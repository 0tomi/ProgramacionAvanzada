<?php
declare(strict_types=1);

require_once __DIR__ . '/lectorEnv.php';

/**
 * Repositorio responsable de persistir y recuperar información de publicaciones.
 *
 * Esta clase encapsula las consultas sobre las tablas Post, ImagesPost y Likes,
 * devolviendo estructuras listas para exponer por la API sin filtrar lógica de DB
 * hacia las capas superiores.
 * 
 * getPostByUser devuelve un array asociativo con todos los datos necesarios para renderizar un post. 
 * Requiere que se le pase el id de la persona a la cual se le quieren consultar los post y el
 * id de la persona la cual va a mirar esos post, para determinar si likeo o no dicho post.
 * 
 * El array lo devuelve de esta forma:
 *            $posts[] = [
 *              'id' => (int)$id,
 *              'parent_id' => $parentId !== null ? (int)$parentId : null,
 *              'content' => $content,
 *              'date' => $date,
 *              'parent_author' => $parentAuthor,
 *              'likes' => 0,
 *              'liked' => false,
 *             ];
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

public function getFeed(?int $viewerId = null): array {
        $baseSelect = <<<SQL
        SELECT
            p.idPost                                   AS post_id,
            p.content                                  AS post_text,
            p.date                                     AS created_at,
            u.idUser                                   AS author_id,
            u.userTag                                  AS author_tag,
            u.username                                 AS author_username,
            u.profileImageRoute                        AS author_avatar,
            MAX(CASE WHEN ip.`order` = 1 THEN ip.route END) AS image_1,
            MAX(CASE WHEN ip.`order` = 2 THEN ip.route END) AS image_2,
            COALESCE(lc.total_likes, 0)                AS like_count,
            COALESCE(rc.total_replies, 0)              AS reply_count
        SQL;

        $baseFrom = <<<SQL
        FROM Post AS p
        INNER JOIN User AS u ON u.idUser = p.idUserOwner
        LEFT JOIN ImagesPost AS ip ON ip.idPost = p.idPost
        LEFT JOIN (
            SELECT post, COUNT(*) AS total_likes
            FROM Likes
            GROUP BY post
        ) AS lc ON lc.post = p.idPost
        LEFT JOIN (
            SELECT idBelogingPost, COUNT(*) AS total_replies
            FROM Post
            WHERE idBelogingPost IS NOT NULL
            GROUP BY idBelogingPost
        ) AS rc ON rc.idBelogingPost = p.idPost
        SQL;

        $tail = <<<SQL
        WHERE p.idBelogingPost IS NULL
        GROUP BY
            p.idPost,
            p.content,
            p.date,
            u.idUser,
            u.userTag,
            u.username,
            u.profileImageRoute,
            viewer_has_like,
            viewer_can_delete
        ORDER BY p.date DESC
        SQL;

        if ($viewerId !== null) {
                $sql = $baseSelect . ',
            CASE WHEN lcur.post IS NOT NULL THEN TRUE ELSE FALSE END AS viewer_has_like,
            CASE WHEN p.idUserOwner = ? THEN TRUE ELSE FALSE END AS viewer_can_delete
        ' . $baseFrom . '
        LEFT JOIN (
            SELECT post
            FROM Likes
            WHERE idUser = ?
        ) AS lcur ON lcur.post = p.idPost
        ' . $tail;

                $stmt = $this->db->prepare($sql);
                $stmt->bind_param('ii', $viewerId, $viewerId);
            } else {
                $sql = $baseSelect . ',
            FALSE AS viewer_has_like,
            FALSE AS viewer_can_delete
        ' . $baseFrom . '
        ' . $tail;

                $stmt = $this->db->prepare($sql);
            }

            $stmt->execute();
            $result = $stmt->get_result();
            
            $posts = [];
            while ($row = $result->fetch_assoc()) {
                $posts[] = [
                    'id'    => (int)$row['post_id'],
                    'text'  => $row['post_text'],
                    'created_at' => $row['created_at'],
                    'author'     => [
                        'id'       => (int)$row['author_id'],
                        'name'      => $row['author_tag'],
                        'handle' => $row['author_username'],
                        'avatar_url'   => $row['author_avatar'],
                    ],
                    'images'     => [
                        'image_1' => ($row['image_1'] !== null && $row['image_1'] !== '') ? $row['image_1'] : null,
                        'image_2' => ($row['image_2'] !== null && $row['image_2'] !== '') ? $row['image_2'] : null,
                    ],
                    'counts'      => [
                        'likes'   => (int)$row['like_count'],
                        'replies' => (int)$row['reply_count'],
                    ],
                    'viewer'     => [
                        'liked'   => $viewerId !== null ? (bool)$row['viewer_has_like'] : false,
                        'can_delete' => $viewerId !== null ? (bool)$row['viewer_can_delete'] : false,
                    ],
                ];
            }
            $stmt->close();
            return $posts;
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
        $viewer = (int)$viewerId;

        $sql = <<<SQL
            WITH RECURSIVE ancestors AS (
                SELECT idPost, idBelogingPost
                FROM Post
                WHERE idPost = ?
                UNION ALL
                SELECT parent.idPost, parent.idBelogingPost
                FROM Post AS parent
                INNER JOIN ancestors AS child ON child.idBelogingPost = parent.idPost
            ),
            root AS (
                SELECT idPost AS root_id
                FROM ancestors
                WHERE idBelogingPost IS NULL
                LIMIT 1
            ),
            thread AS (
                SELECT p.idPost, p.idBelogingPost
                FROM Post AS p
                INNER JOIN root AS r ON p.idPost = r.root_id
                UNION ALL
                SELECT child.idPost, child.idBelogingPost
                FROM Post AS child
                INNER JOIN thread AS t ON child.idBelogingPost = t.idPost
            ),
            first_image AS (
                SELECT ip.idPost, ip.route
                FROM ImagesPost AS ip
                INNER JOIN (
                    SELECT idPost, MIN(`order`) AS min_order
                    FROM ImagesPost
                    GROUP BY idPost
                ) AS ordered ON ordered.idPost = ip.idPost AND ordered.min_order = ip.`order`
            )
            SELECT
                r.root_id,
                p.idPost AS post_id,
                p.idBelogingPost AS parent_id,
                p.idUserOwner AS owner_id,
                p.content,
                p.date AS created_at,
                u.idUser AS author_id,
                u.userTag AS author_handle,
                u.username AS author_name,
                u.profileImageRoute AS author_avatar,
                fi.route AS image_route,
                COUNT(l.post) AS like_count,
                MAX(CASE WHEN l.idUser = ? THEN 1 ELSE 0 END) AS viewer_liked
            FROM thread AS t
            INNER JOIN Post AS p ON p.idPost = t.idPost
            INNER JOIN root AS r ON TRUE
            INNER JOIN User AS u ON u.idUser = p.idUserOwner
            LEFT JOIN first_image AS fi ON fi.idPost = p.idPost
            LEFT JOIN Likes AS l ON l.post = p.idPost
            GROUP BY
                r.root_id,
                p.idPost,
                p.idBelogingPost,
                p.idUserOwner,
                p.content,
                p.date,
                u.idUser,
                u.userTag,
                u.username,
                u.profileImageRoute,
                fi.route
            ORDER BY
                CASE WHEN p.idPost = r.root_id THEN 0 ELSE 1 END,
                p.date ASC,
                p.idPost ASC
        SQL;

        $stmt = $this->db->prepare($sql);
        $stmt->bind_param('ii', $postId, $viewer);
        $stmt->execute();
        $result = $stmt->get_result();

        $rows = [];
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        $result->free();
        $stmt->close();

        if ($rows === []) {
            return null;
        }

        $rootId = (int)$rows[0]['root_id'];
        $rootRow = null;
        $replies = [];

        foreach ($rows as $row) {
            $isRoot = (int)$row['post_id'] === $rootId;
            $normalizedImage = $this->normalizeAssetPath($row['image_route']);
            $formattedDate = $this->formatDateTime($row['created_at']);
            $viewerData = [
                'liked' => (bool)$row['viewer_liked'],
                'can_delete' => $viewer === (int)$row['owner_id'],
            ];
            $counts = [
                'likes' => (int)$row['like_count'],
                'replies' => 0,
            ];

            if ($isRoot) {
                $rootRow = [
                    'id' => (string)$row['post_id'],
                    'text' => $row['content'],
                    'created_at' => $formattedDate,
                    'author' => [
                        'id' => (string)$row['author_id'],
                        'handle' => $row['author_handle'],
                        'name' => $row['author_name'],
                        'avatar_url' => $this->resolveAvatar($row['author_avatar']),
                    ],
                    'media_url' => $normalizedImage,
                    'counts' => $counts,
                    'viewer' => $viewerData,
                    'replies' => [],
                ];
                continue;
            }

            $parentId = $row['parent_id'] !== null ? (int)$row['parent_id'] : null;
            $replies[] = [
                'id' => (string)$row['post_id'],
                'parent_id' => ($parentId !== null && $parentId === $rootId) ? null : ($parentId !== null ? (string)$parentId : null),
                'author' => $row['author_name'],
                'text' => $row['content'],
                'created_at' => $formattedDate,
                'media_url' => $normalizedImage,
                'counts' => $counts,
                'viewer' => $viewerData,
            ];
        }

        if ($rootRow === null) {
            return null;
        }

        $rootRow['replies'] = $replies;
        $rootRow['counts']['replies'] = count($replies);

        return $rootRow;
    }

    public function getPostsByUser(int $userId, ?int $viewerId = null): array
    {
        $stmt = $this->db->prepare(
            'SELECT
                p.idPost,
                p.idBelogingPost,
                p.content,
                p.date,
                parentUser.username AS parentAuthor
            FROM Post AS p
            LEFT JOIN Post AS parent ON parent.idPost = p.idBelogingPost
            LEFT JOIN User AS parentUser ON parentUser.idUser = parent.idUserOwner
            WHERE p.idUserOwner = ?
            ORDER BY p.date DESC, p.idPost DESC'
        );
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $stmt->bind_result($id, $parentId, $content, $date, $parentAuthor);

        $posts = [];
        while ($stmt->fetch()) {
            $posts[] = [
                'id' => (int)$id,
                'parent_id' => $parentId !== null ? (int)$parentId : null,
                'content' => $content,
                'date' => $date,
                'parent_author' => $parentAuthor,
                'likes' => 0,
                'liked' => false,
            ];
        }

        $stmt->close();

        if (empty($posts)) {
            return $posts;
        }

        $postIds = array_map(static fn(array $post): int => $post['id'], $posts);
        $idList = implode(',', $postIds);

        $likeCounts = [];
        $result = $this->db->query(
            'SELECT post, COUNT(*) AS likeCount FROM Likes WHERE post IN (' . $idList . ') GROUP BY post'
        );
        while ($row = $result->fetch_assoc()) {
            $likeCounts[(int)$row['post']] = (int)$row['likeCount'];
        }
        $result->free();

        $viewerLikes = [];
        if ($viewerId !== null) {
            $result = $this->db->query(
                'SELECT post FROM Likes WHERE idUser = ' . (int)$viewerId . ' AND post IN (' . $idList . ')'
            );
            while ($row = $result->fetch_assoc()) {
                $viewerLikes[(int)$row['post']] = true;
            }
            $result->free();
        }

        foreach ($posts as &$post) {
            $postId = $post['id'];
            $post['likes'] = $likeCounts[$postId] ?? 0;
            $post['liked'] = isset($viewerLikes[$postId]);
        }
        unset($post);

        return $posts;
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
     * @param int         $ownerId
     * @param int         $rootPostId
     * @param string      $content
     * @param int|null    $parentCommentId
     * @param string|null $imageRelativeRoute Ruta relativa (desde la raíz) o null.
     * @return array{
     *   id:string,
     *   parent_id:?string,
     *   author:string,
     *   text:string,
     *   created_at:string,
     *   media_url:?string,
     *   counts:array{likes:int},
     *   viewer:array{liked:bool,can_delete:bool}
     * }
     */
    public function createComment(
        int $ownerId,
        int $rootPostId,
        string $content,
        ?int $parentCommentId = null,
        ?string $imageRelativeRoute = null
    ): array {
        $parentId = $parentCommentId ?? $rootPostId;

        $this->db->begin_transaction();
        try {
            $stmt = $this->db->prepare(
                'INSERT INTO Post (idBelogingPost, idUserOwner, content) VALUES (?, ?, ?)'
            );
            $stmt->bind_param('iis', $parentId, $ownerId, $content);
            $stmt->execute();
            $commentId = (int)$this->db->insert_id;
            $stmt->close();

            if ($imageRelativeRoute !== null) {
                $imageName = basename($imageRelativeRoute);
                $order = 1;
                $stmt = $this->db->prepare(
                    'INSERT INTO ImagesPost (idPost, Name, `order`, route) VALUES (?, ?, ?, ?)'
                );
                $stmt->bind_param('isis', $commentId, $imageName, $order, $imageRelativeRoute);
                $stmt->execute();
                $stmt->close();
            }

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
            'media_url' => $this->normalizeAssetPath($imageRelativeRoute),
            'counts' => [
                'likes' => 0,
            ],
            'viewer' => [
                'liked' => false,
                'can_delete' => true,
            ],
        ];
    }

    /**
     * Elimina un post principal y toda su conversación asociada.
     *
     * @param int $postId
     * @param int $userId
     */
    public function deletePost(int $postId, int $userId): void
    {
        $stmt = $this->db->prepare('SELECT idUserOwner, idBelogingPost FROM Post WHERE idPost = ?');
        $stmt->bind_param('i', $postId);
        $stmt->execute();
        $stmt->bind_result($ownerId, $parentId);
        if (!$stmt->fetch()) {
            $stmt->close();
            throw new RuntimeException('La publicación indicada no existe.');
        }
        $stmt->close();

        if ((int)$ownerId !== $userId) {
            throw new RuntimeException('Solo puedes eliminar publicaciones propias.');
        }
        if ($parentId !== null) {
            throw new RuntimeException('Los comentarios se eliminan desde el detalle del post.');
        }

        $ids = $this->collectDescendantIds($postId);
        $ids = array_values(array_unique(array_map('intval', $ids)));

        if (empty($ids)) {
            return;
        }

        $idList = implode(',', $ids);
        $imageRoutes = $this->collectImageRoutes($ids);

        $this->db->begin_transaction();
        try {
            $this->db->query('DELETE FROM Likes WHERE post IN (' . $idList . ')');
            $this->db->query('DELETE FROM ImagesPost WHERE idPost IN (' . $idList . ')');
            $this->db->query('DELETE FROM Post WHERE idPost IN (' . $idList . ')');
            $this->db->commit();
        } catch (mysqli_sql_exception $e) {
            $this->db->rollback();
            throw new RuntimeException('No se pudo eliminar la publicación.', 0, $e);
        }

        $this->deleteImageFiles($imageRoutes);
    }

    /**
     * Elimina un comentario y toda su rama de respuestas.
     *
     * @param int $commentId
     * @param int $userId
     */
    public function deleteComment(int $commentId, int $userId): void
    {
        $stmt = $this->db->prepare('SELECT idUserOwner, idBelogingPost FROM Post WHERE idPost = ?');
        $stmt->bind_param('i', $commentId);
        $stmt->execute();
        $stmt->bind_result($ownerId, $parentId);
        if (!$stmt->fetch()) {
            $stmt->close();
            throw new RuntimeException('El comentario indicado no existe.');
        }
        $stmt->close();

        if ($parentId === null) {
            throw new RuntimeException('La publicación raíz no puede eliminarse con deleteComment.');
        }
        if ((int)$ownerId !== $userId) {
            throw new RuntimeException('Solo puedes eliminar comentarios propios.');
        }

        $ids = $this->collectDescendantIds($commentId);
        $ids = array_values(array_unique(array_map('intval', $ids)));

        if (empty($ids)) {
            return;
        }

        $idList = implode(',', $ids);
        $imageRoutes = $this->collectImageRoutes($ids);

        $this->db->begin_transaction();
        try {
            $this->db->query('DELETE FROM Likes WHERE post IN (' . $idList . ')');
            $this->db->query('DELETE FROM ImagesPost WHERE idPost IN (' . $idList . ')');
            $this->db->query('DELETE FROM Post WHERE idPost IN (' . $idList . ')');
            $this->db->commit();
        } catch (mysqli_sql_exception $e) {
            $this->db->rollback();
            throw new RuntimeException('No se pudo eliminar el comentario.', 0, $e);
        }

        $this->deleteImageFiles($imageRoutes);
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
     * Obtiene todos los IDs de comentarios pertenecientes a un post raíz.
     *
     * @return int[]
     */
    private function collectDescendantIds(int $rootId): array
    {
        $sql = 'SELECT idPost, idBelogingPost FROM Post';
        $result = $this->db->query($sql);

        $children = [];
        while ($row = $result->fetch_assoc()) {
            if ($row['idBelogingPost'] === null) {
                continue;
            }
            $parent = (int)$row['idBelogingPost'];
            $child = (int)$row['idPost'];
            $children[$parent][] = $child;
        }
        $result->free();

        $ids = [$rootId];
        $queue = [$rootId];

        while (!empty($queue)) {
            $current = array_shift($queue);
            if (empty($children[$current])) {
                continue;
            }
            foreach ($children[$current] as $childId) {
                $ids[] = $childId;
                $queue[] = $childId;
            }
        }

        return $ids;
    }

    /**
     * Recupera las rutas de imágenes asociadas a una lista de posts.
     *
     * @param int[] $postIds
     * @return string[]
     */
    private function collectImageRoutes(array $postIds): array
    {
        if (empty($postIds)) {
            return [];
        }

        $idList = implode(',', array_map('intval', $postIds));
        $sql = 'SELECT route FROM ImagesPost WHERE idPost IN (' . $idList . ')';
        $result = $this->db->query($sql);

        $routes = [];
        while ($row = $result->fetch_assoc()) {
            $routes[] = $row['route'];
        }
        $result->free();

        return $routes;
    }

    /**
     * Elimina físicamente las imágenes asociadas a una publicación.
     *
     * @param string[] $routes
     */
    private function deleteImageFiles(array $routes): void
    {
        foreach ($routes as $route) {
            $relative = trim((string)$route);
            if ($relative === '') {
                continue;
            }

            $normalized = $relative;
            if (str_starts_with($normalized, '../')) {
                $normalized = substr($normalized, 3);
            }

            $absolute = __DIR__ . '/../' . ltrim($normalized, '/');
            if (!is_file($absolute)) {
                continue;
            }

            @unlink($absolute);
        }
    }
}
