<?php
declare(strict_types=1);

namespace Posts\Lib;

use User;

/**
 * Capa de dominio para todas las operaciones sobre posts.
 *
 * La idea es concentrar aquí la lógica de negocio (likes, comentarios,
 * creación de posts, etc.) para que tanto la API como las vistas en PHP
 * compartan un mismo contrato.
 */
final class PostService
{
    public const MAX_IMAGE_BYTES = 5 * 1024 * 1024; // 5MB

    private PostStorage $storage;

    public function __construct(?PostStorage $storage = null)
    {
        $this->storage = $storage ?? new PostStorage();
    }

    /**
     * Devuelve todos los posts enriquecidos con la información del viewer.
     *
     * @return array<int, array<string, mixed>>
     */
    public function listPosts(array $session): array
    {
        $items = $this->storage->readAll();
        return array_map(fn ($post) => $this->enrichPost($post, $session), $items);
    }

    /**
     * Obtiene un post por id o null si no existe.
     */
    public function getPost(string $id, array $session): ?array
    {
        if ($id === '') {
            return null;
        }
        $items = $this->storage->readAll();
        foreach ($items as $post) {
            if (($post['id'] ?? '') === $id) {
                return $this->enrichPost($post, $session);
            }
        }
        return null;
    }

    /**
     * Alterna el like del usuario actual y devuelve el estado final.
     *
     * @return array{liked: bool, like_count: int}
     */
    public function toggleLike(string $postId, array &$session): array
    {
        if ($postId === '') {
            throw new \InvalidArgumentException('El post id es requerido.');
        }

        $items = $this->storage->readAll();
        foreach ($items as &$post) {
            if (($post['id'] ?? '') !== $postId) {
                continue;
            }

            $post['counts'] = $post['counts'] ?? ['likes' => 0, 'replies' => 0];
            $likes = $session['likes'] ?? [];
            if (!is_array($likes)) {
                $likes = [];
            }

            $liked = in_array($postId, $likes, true);
            if ($liked) {
                $post['counts']['likes'] = max(0, (int) $post['counts']['likes'] - 1);
                $likes = array_values(array_filter($likes, fn ($value) => $value !== $postId));
            } else {
                $post['counts']['likes'] = (int) $post['counts']['likes'] + 1;
                $likes[] = $postId;
            }

            $session['likes'] = $likes;
            $this->storage->writeAll($items);

            return [
                'liked' => !$liked,
                'like_count' => (int) $post['counts']['likes'],
            ];
        }

        throw new \RuntimeException('Post no encontrado.');
    }

    /**
     * Agrega un comentario al post indicado.
     *
     * @return array<string, mixed> Datos del comentario creado.
     */
    public function addComment(string $postId, ?string $parentId, string $author, string $text): array
    {
        if ($postId === '') {
            throw new \InvalidArgumentException('post_id requerido.');
        }
        $text = trim($text);
        if ($text === '' || mb_strlen($text, 'UTF-8') > 280) {
            throw new \InvalidArgumentException('El comentario debe tener entre 1 y 280 caracteres.');
        }

        $items = $this->storage->readAll();
        foreach ($items as &$post) {
            if (($post['id'] ?? '') !== $postId) {
                continue;
            }

            $comment = [
                'id' => $this->generateId(),
                'parent_id' => $parentId ?: null,
                'author' => $author !== '' ? $author : 'Anónimo',
                'text' => $text,
                'created_at' => gmdate('c'),
            ];

            if (!isset($post['replies']) || !is_array($post['replies'])) {
                $post['replies'] = [];
            }

            array_unshift($post['replies'], $comment);
            $post['counts']['replies'] = (int) (($post['counts']['replies'] ?? 0) + 1);

            $this->storage->writeAll($items);
            return $comment;
        }

        throw new \RuntimeException('Post no encontrado.');
    }

    /**
     * Crea un nuevo post.
     *
     * @param array<string, mixed> $files Datos del array $_FILES['image'] si corresponde.
     * @return array<string, mixed> Post creado.
     */
    public function createPost(array $session, string $text, array $files = []): array
    {
        $user = $session['user'] ?? null;
        if (!$user instanceof User) {
            throw new \RuntimeException('Debes iniciar sesión para publicar.');
        }

        $text = trim($text);
        if ($text === '' || mb_strlen($text, 'UTF-8') > 280) {
            throw new \InvalidArgumentException('El texto del post debe tener entre 1 y 280 caracteres.');
        }

        $mediaUrl = '';
        if (!empty($files) && isset($files['tmp_name']) && is_uploaded_file($files['tmp_name'])) {
            $mediaUrl = $this->handleImageUpload($files);
        }

        $username = $user->getNombre();
        $display = $session['display_name'] ?? $username;
        $userId = $session['user_id'] ?? ('u_' . preg_replace('/\W+/', '', strtolower($username)));

        $post = [
            'id' => $this->generateId(),
            'author' => [
                'id' => $userId,
                'handle' => '',
                'name' => $display,
            ],
            'text' => $text,
            'media_url' => $mediaUrl,
            'counts' => ['likes' => 0, 'replies' => 0],
            'replies' => [],
            'created_at' => gmdate('c'),
        ];

        $items = $this->storage->readAll();
        array_unshift($items, $post);
        $this->storage->writeAll($items);

        return $this->enrichPost($post, $session);
    }

    /**
     * Enriquecer post con datos del viewer.
     *
     * @param array<string, mixed> $post
     * @return array<string, mixed>
     */
    public function enrichPost(array $post, array $session): array
    {
        $post['counts'] = $post['counts'] ?? ['likes' => 0, 'replies' => 0];
        $post['replies'] = $post['replies'] ?? [];

        if (!isset($post['author']) || !is_array($post['author'])) {
            $post['author'] = [
                'id' => 'uX',
                'handle' => 'anon',
                'name' => 'Anónimo',
            ];
        }

        $likes = $session['likes'] ?? [];
        if (!is_array($likes)) {
            $likes = [];
        }

        $likedSet = array_flip(array_map('strval', $likes));
        $postId = (string) ($post['id'] ?? '');

        $viewer = [
            'liked' => isset($likedSet[$postId]),
            'authenticated' => $this->isAuthenticated($session),
        ];

        $post['viewer'] = $viewer;

        return $post;
    }

    public function isAuthenticated(array $session): bool
    {
        return ($session['user'] ?? null) instanceof User
            && $session['user']->getNombre() !== '';
    }

    /**
     * Genera ids pseudo únicos basados en timestamp.
     */
    public function generateId(): string
    {
        return (string) (time()) . str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Maneja la subida de una imagen y devuelve la URL relativa.
     *
     * @param array<string, mixed> $file
     */
    private function handleImageUpload(array $file): string
    {
        $mime = mime_content_type($file['tmp_name']) ?: '';
        $allowed = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            'image/gif' => 'gif',
        ];

        if (!isset($allowed[$mime])) {
            throw new \InvalidArgumentException('Formato de imagen no permitido.');
        }

        $size = isset($file['size']) ? (int) $file['size'] : 0;
        if ($size > self::MAX_IMAGE_BYTES) {
            throw new \InvalidArgumentException('La imagen supera el límite permitido (5MB).');
        }

        $this->storage->ensureUploadsDir();
        $filename = $this->generateId() . '.' . $allowed[$mime];
        $destination = $this->storage->getUploadsDir() . '/' . $filename;

        if (!@move_uploaded_file($file['tmp_name'], $destination)) {
            throw new \RuntimeException('No se pudo guardar la imagen subida.');
        }

        return 'uploads/' . $filename;
    }
}
