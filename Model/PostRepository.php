<?php
declare(strict_types=1);

require_once __DIR__ . '/lectorEnv.php';

/**
 * Repositorio responsable de persistir y recuperar información de publicaciones.
 *
 * Esta clase encapsula las consultas sobre las tablas Post, ImagesPost y Likes,
 * devolviendo estructuras listas para exponer por la API sin filtrar lógica de DB
 * hacia las capas superiores.
 */
final class PostRepository
{
    private mysqli $db;

    public function __construct()
    {
        try {
            mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
            $this->db = new mysqli(
                $_ENV['DB_HOST'],
                $_ENV['DB_USER'],
                $_ENV['DB_PASS'],
                $_ENV['DB_NAME'],
                (int)$_ENV['DB_PORT']
            );
            $this->db->set_charset('utf8mb4');
        } catch (mysqli_sql_exception $e) {
            throw new RuntimeException('No se pudo conectar a la base de datos.', 0, $e);
        }
    }

    public function __destruct()
    {
        if (isset($this->db)) {
            $this->db->close();
        }
    }

    /**
     * Devuelve todas las publicaciones principales con sus métricas y comentarios listos
     * para serializar en la API.
     *
     * @param int|null $viewerId ID del usuario autenticado (si existe) para marcar likes propios.
     * @return array<int, array<string,mixed>>
     */
    public function getFeed(?int $viewerId): array
    {
        $graph = $this->loadGraph($viewerId);
        $posts = $graph['posts'];
        $commentsByRoot = $graph['commentsByRoot'];

        $feed = [];
        foreach ($posts as $id => $data) {
            if ($data['parent_id'] !== null) {
                continue;
            }
            $post = $data;
            unset($post['parent_id']);
            $post['replies'] = array_values($commentsByRoot[$id] ?? []);
            $post['counts']['replies'] = count($post['replies']);
            $feed[] = $post;
        }

        usort(
            $feed,
            static fn(array $a, array $b): int => $b['created_at'] <=> $a['created_at']
        );

        return $feed;
    }

    /**
     * Obtiene un post principal por ID. Si se solicita un comentario, retorna su raíz.
     *
     * @param int      $postId
     * @param int|null $viewerId
     * @return array<string,mixed>|null
     */
    public function getPost(int $postId, ?int $viewerId): ?array
    {
        $graph = $this->loadGraph($viewerId);
        $posts = $graph['posts'];
        $commentsByRoot = $graph['commentsByRoot'];
        $rootAssignments = $graph['rootAssignments'];

        if (!isset($posts[$postId])) {
            return null;
        }

        $rootId = $posts[$postId]['parent_id'] === null
            ? $postId
            : ($rootAssignments[$postId] ?? null);

        if ($rootId === null || !isset($posts[$rootId])) {
            return null;
        }

        $post = $posts[$rootId];
        unset($post['parent_id']);
        $post['replies'] = array_values($commentsByRoot[$rootId] ?? []);
        $post['counts']['replies'] = count($post['replies']);

        return $post;
    }

    /**
     * Lista los IDs de publicaciones likeadas por el usuario autenticado.
     *
     * @param int $viewerId
     * @return string[]
     */
    public function listLikedPostIds(int $viewerId): array
    {
        $stmt = $this->db->prepare('SELECT post FROM Likes WHERE idUser = ?');
        $stmt->bind_param('i', $viewerId);
        $stmt->execute();
        $stmt->bind_result($postId);

        $ids = [];
        while ($stmt->fetch()) {
            $ids[] = (string)$postId;
        }
        $stmt->close();

        return $ids;
    }

    /**
     * Alterna el like de una publicación para el usuario indicado.
     *
     * @param int $postId
     * @param int $userId
     * @return array{liked:bool, like_count:int}
     */
    public function toggleLike(int $postId, int $userId): array
    {
        $this->assertPostExists($postId);

        $this->db->begin_transaction();
        try {
            $stmt = $this->db->prepare('SELECT 1 FROM Likes WHERE idUser = ? AND post = ?');
            $stmt->bind_param('ii', $userId, $postId);
            $stmt->execute();
            $stmt->store_result();
            $exists = $stmt->num_rows > 0;
            $stmt->close();

            if ($exists) {
                $stmt = $this->db->prepare('DELETE FROM Likes WHERE idUser = ? AND post = ?');
                $stmt->bind_param('ii', $userId, $postId);
                $stmt->execute();
                $stmt->close();
                $liked = false;
            } else {
                $stmt = $this->db->prepare(
                    'INSERT INTO Likes (idUser, post, date, time) VALUES (?, ?, CURDATE(), CURTIME())'
                );
                $stmt->bind_param('ii', $userId, $postId);
                $stmt->execute();
                $stmt->close();
                $liked = true;
            }

            $stmt = $this->db->prepare('SELECT COUNT(*) FROM Likes WHERE post = ?');
            $stmt->bind_param('i', $postId);
            $stmt->execute();
            $stmt->bind_result($count);
            $stmt->fetch();
            $stmt->close();

            $this->db->commit();

            return [
                'liked' => $liked,
                'like_count' => (int)$count,
            ];
        } catch (mysqli_sql_exception $e) {
            $this->db->rollback();
            throw new RuntimeException('No se pudo actualizar el like.', 0, $e);
        }
    }

    /**
     * Crea una publicación principal.
     *
     * @param int         $ownerId
     * @param string      $content
     * @param string|null $imageRelativeRoute Ruta relativa (desde la raíz del proyecto) o null.
     *
     * @return array<string,mixed>
     */
    public function createPost(int $ownerId, string $content, ?string $imageRelativeRoute = null): array
    {
        $this->db->begin_transaction();
        try {
            $stmt = $this->db->prepare(
                'INSERT INTO Post (idBelogingPost, idUserOwner, content) VALUES (NULL, ?, ?)'
            );
            $stmt->bind_param('is', $ownerId, $content);
            $stmt->execute();
            $postId = (int)$this->db->insert_id;
            $stmt->close();

            if ($imageRelativeRoute !== null) {
                $imageName = basename($imageRelativeRoute);
                $order = 1;
                $stmt = $this->db->prepare(
                    'INSERT INTO ImagesPost (idPost, Name, `order`, route) VALUES (?, ?, ?, ?)'
                );
                $stmt->bind_param('isis', $postId, $imageName, $order, $imageRelativeRoute);
                $stmt->execute();
                $stmt->close();
            }

            $this->db->commit();
        } catch (mysqli_sql_exception $e) {
            $this->db->rollback();
            throw new RuntimeException('No se pudo crear la publicación.', 0, $e);
        }

        $post = $this->getPost($postId, $ownerId);
        if ($post === null) {
            throw new RuntimeException('La publicación recién creada no pudo recuperarse.');
        }
        return $post;
    }

    /**
     * Crea un comentario (o respuesta) dentro de un hilo de publicaciones.
     *
     * @param int      $ownerId
     * @param int      $rootPostId
     * @param string   $content
     * @param int|null $parentCommentId
     * @return array{id:string,parent_id:?string,author:string,text:string,created_at:string}
     */
    public function createComment(
        int $ownerId,
        int $rootPostId,
        string $content,
        ?int $parentCommentId = null
    ): array {
        $this->assertRootPostExists($rootPostId);

        $parentId = $parentCommentId ?? $rootPostId;
        $this->assertPostExists($parentId);

        $this->db->begin_transaction();
        try {
            $stmt = $this->db->prepare(
                'INSERT INTO Post (idBelogingPost, idUserOwner, content) VALUES (?, ?, ?)'
            );
            $stmt->bind_param('iis', $parentId, $ownerId, $content);
            $stmt->execute();
            $commentId = (int)$this->db->insert_id;
            $stmt->close();

            $stmt = $this->db->prepare(
                'SELECT p.idBelogingPost, p.date, u.username
                 FROM Post AS p
                 INNER JOIN User AS u ON u.idUser = p.idUserOwner
                 WHERE p.idPost = ?'
            );
            $stmt->bind_param('i', $commentId);
            $stmt->execute();
            $stmt->bind_result($storedParent, $date, $username);
            if (!$stmt->fetch()) {
                $stmt->close();
                throw new RuntimeException('No se pudo recuperar el comentario insertado.');
            }
            $stmt->close();

            $this->db->commit();
        } catch (mysqli_sql_exception $e) {
            $this->db->rollback();
            throw new RuntimeException('No se pudo crear el comentario.', 0, $e);
        }

        $createdAt = $this->formatDateTime($date);
        $parentForResponse = ((int)$storedParent === $rootPostId) ? null : (string)$storedParent;

        return [
            'id' => (string)$commentId,
            'parent_id' => $parentForResponse,
            'author' => $username,
            'text' => $content,
            'created_at' => $createdAt,
        ];
    }

    /**
     * Verifica que exista una publicación (post o comentario).
     *
     * @throws RuntimeException si no existe
     */
    private function assertPostExists(int $postId): void
    {
        $stmt = $this->db->prepare('SELECT 1 FROM Post WHERE idPost = ?');
        $stmt->bind_param('i', $postId);
        $stmt->execute();
        $stmt->store_result();
        $exists = $stmt->num_rows > 0;
        $stmt->close();

        if (!$exists) {
            throw new RuntimeException('La publicación indicada no existe.');
        }
    }

    /**
     * Verifica que el ID corresponda a una publicación principal (idBelogingPost = NULL).
     *
     * @throws RuntimeException si no existe o no es raíz.
     */
    private function assertRootPostExists(int $postId): void
    {
        $stmt = $this->db->prepare('SELECT idBelogingPost FROM Post WHERE idPost = ?');
        $stmt->bind_param('i', $postId);
        $stmt->execute();
        $stmt->bind_result($parent);
        if (!$stmt->fetch()) {
            $stmt->close();
            throw new RuntimeException('La publicación indicada no existe.');
        }
        $stmt->close();

        if ($parent !== null) {
            throw new RuntimeException('El identificador corresponde a un comentario, no a un post principal.');
        }
    }

    /**
     * Construye la estructura de posts y comentarios para la API.
     *
     * @param int|null $viewerId
     * @return array{
     *   posts: array<int,array<string,mixed>>,
     *   commentsByRoot: array<int,array<int,array<string,mixed>>>,
     *   rootAssignments: array<int,int>,
     *   parentMap: array<int,int>
     * }
     */
    private function loadGraph(?int $viewerId): array
    {
        $rows = $this->fetchAllPosts();
        $likeCounts = $this->fetchLikeCounts();
        $viewerLikes = $this->fetchViewerLikes($viewerId);
        $imagesByPost = $this->fetchImages();

        $viewerLikesSet = array_flip($viewerLikes);

        $posts = [];
        $parentMap = [];

        foreach ($rows as $row) {
            $id = (int)$row['idPost'];
            $parentId = $row['idBelogingPost'] !== null ? (int)$row['idBelogingPost'] : null;

            $posts[$id] = [
                'id' => (string)$id,
                'parent_id' => $parentId !== null ? (string)$parentId : null,
                'text' => $row['content'],
                'created_at' => $this->formatDateTime($row['date']),
                'author' => [
                    'id' => (string)$row['idUserOwner'],
                    'handle' => $row['userTag'],
                    'name' => $row['username'],
                    'avatar_url' => $this->resolveAvatar($row['profileImageRoute']),
                ],
                'media_url' => $this->normalizeAssetPath($imagesByPost[$id][0] ?? null),
                'counts' => [
                    'likes' => (int)($likeCounts[$id] ?? 0),
                    'replies' => 0,
                ],
                'viewer' => [
                    'authenticated' => $viewerId !== null,
                    'liked' => $viewerId !== null && isset($viewerLikesSet[$id]),
                ],
            ];

            if ($parentId !== null) {
                $parentMap[$id] = $parentId;
            }
        }

        $memo = [];
        $rootAssignments = [];
        foreach (array_keys($parentMap) as $childId) {
            $rootAssignments[$childId] = $this->findRootId($childId, $parentMap, $memo);
        }

        $commentsByRoot = [];
        foreach ($rootAssignments as $childId => $rootId) {
            if (!isset($posts[$rootId])) {
                continue;
            }
            $commentsByRoot[$rootId][] = $this->toComment($posts[$childId], $rootId);
        }

        return [
            'posts' => $posts,
            'commentsByRoot' => $commentsByRoot,
            'rootAssignments' => $rootAssignments,
            'parentMap' => $parentMap,
        ];
    }

    /**
     * Devuelve los datos crudos de la tabla Post enriquecidos con datos de usuario.
     *
     * @return array<int, array<string,mixed>>
     */
    private function fetchAllPosts(): array
    {
        $sql = <<<'SQL'
            SELECT
                p.idPost,
                p.idBelogingPost,
                p.idUserOwner,
                p.content,
                p.date,
                u.username,
                u.userTag,
                u.profileImageRoute
            FROM Post AS p
            INNER JOIN User AS u ON u.idUser = p.idUserOwner
        SQL;

        $result = $this->db->query($sql);
        $rows = [];
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        $result->free();

        return $rows;
    }

    /**
     * Conteo total de likes por post.
     *
     * @return array<int,int>
     */
    private function fetchLikeCounts(): array
    {
        $sql = 'SELECT post, COUNT(*) AS likeCount FROM Likes GROUP BY post';
        $result = $this->db->query($sql);

        $map = [];
        while ($row = $result->fetch_assoc()) {
            $map[(int)$row['post']] = (int)$row['likeCount'];
        }
        $result->free();

        return $map;
    }

    /**
     * IDs de posts likeados por el usuario actual.
     *
     * @param int|null $viewerId
     * @return int[]
     */
    private function fetchViewerLikes(?int $viewerId): array
    {
        if ($viewerId === null) {
            return [];
        }

        $stmt = $this->db->prepare('SELECT post FROM Likes WHERE idUser = ?');
        $stmt->bind_param('i', $viewerId);
        $stmt->execute();
        $stmt->bind_result($postId);

        $ids = [];
        while ($stmt->fetch()) {
            $ids[] = (int)$postId;
        }
        $stmt->close();

        return $ids;
    }

    /**
     * Lista de rutas de imágenes asociadas a cada post.
     *
     * @return array<int,array<int,string>>
     */
    private function fetchImages(): array
    {
        $sql = 'SELECT idPost, route FROM ImagesPost ORDER BY `order` ASC';
        $result = $this->db->query($sql);

        $images = [];
        while ($row = $result->fetch_assoc()) {
            $postId = (int)$row['idPost'];
            $images[$postId][] = $row['route'];
        }
        $result->free();

        return $images;
    }

    /**
     * Convierte un registro de comentario en el formato esperado por la API.
     *
     * @param array<string,mixed> $postData
     * @param int                 $rootId
     * @return array{id:string,parent_id:?string,author:string,text:string,created_at:string}
     */
    private function toComment(array $postData, int $rootId): array
    {
        $parentId = $postData['parent_id'];
        $parentForResponse = ($parentId !== null && (int)$parentId === $rootId)
            ? null
            : $parentId;

        return [
            'id' => $postData['id'],
            'parent_id' => $parentForResponse,
            'author' => $postData['author']['name'],
            'text' => $postData['text'],
            'created_at' => $postData['created_at'],
        ];
    }

    /**
     * Normaliza rutas relativas para distintos contextos de front-end.
     */
    private function normalizeAssetPath(?string $path): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }
        if (preg_match('#^https?://#i', $path) === 1) {
            return $path;
        }
        if (str_starts_with($path, '../')) {
            return $path;
        }
        return '../' . ltrim($path, '/');
    }

    /**
     * Normaliza la ruta del avatar del usuario o devuelve la imagen por defecto.
     */
    private function resolveAvatar(?string $path): string
    {
        $normalized = $this->normalizeAssetPath($path);
        if ($normalized !== null) {
            return $normalized;
        }
        return '../Resources/profilePictures/defaultProfilePicture.png';
    }

    /**
     * Convierte timestamps de la DB a ISO 8601.
     */
    private function formatDateTime(string $dateTime): string
    {
        try {
            $dt = new DateTimeImmutable($dateTime);
        } catch (\Throwable $e) {
            $dt = new DateTimeImmutable('now');
        }
        return $dt->format(DATE_ATOM);
    }

    /**
     * Determina la raíz de un comentario usando memoización para evitar recomputar.
     *
     * @param array<int,int> $parentMap
     * @param array<int,int> $memo
     */
    private function findRootId(int $postId, array $parentMap, array &$memo): int
    {
        if (isset($memo[$postId])) {
            return $memo[$postId];
        }
        if (!isset($parentMap[$postId])) {
            $memo[$postId] = $postId;
            return $postId;
        }
        $memo[$postId] = $this->findRootId($parentMap[$postId], $parentMap, $memo);
        return $memo[$postId];
    }
}
