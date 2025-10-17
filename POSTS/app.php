<?php
declare(strict_types=1);

namespace Posts\App;

use Posts\Lib\PostRenderer;
use Posts\Lib\PostService;

require_once __DIR__ . '/../includes/Usuario.php';
require_once __DIR__ . '/lib/PostStorage.php';
require_once __DIR__ . '/lib/PostService.php';
require_once __DIR__ . '/lib/PostRenderer.php';

/**
 * Punto de entrada para la vista de detalle de un post.
 *
 * El objetivo de este módulo es reemplazar al antiguo app.js que renderizaba
 * y gestionaba los posts desde el navegador. Toda la lógica vive ahora en PHP
 * y se expone mediante un pequeño controlador orientado a acciones.
 */
final class PostPageController
{
    private PostService $service;
    private PostRenderer $renderer;

    public function __construct(?PostService $service = null, ?PostRenderer $renderer = null)
    {
        $this->service = $service ?? new PostService();
        $this->renderer = $renderer ?? new PostRenderer();
    }

    /**
     * Procesa la request actual (GET/POST) y devuelve los datos necesarios para
     * armar la página.
     *
     * @param array<string, mixed> $session Referencia a $_SESSION.
     * @return array{post: ?array, errors: string[], flash: ?string}
     */
    public function handle(array &$session): array
    {
        $postId = (string) ($_GET['id'] ?? '');
        if ($postId === '') {
            return [
                'post' => null,
                'errors' => ['Falta el parámetro ?id=...'],
                'flash' => null,
            ];
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleAction($postId, $session);
        }

        $post = $this->service->getPost($postId, $session);
        if ($post === null) {
            return [
                'post' => null,
                'errors' => ['Post no encontrado.'],
                'flash' => $this->consumeFlash($session),
            ];
        }

        return [
            'post' => $post,
            'errors' => [],
            'flash' => $this->consumeFlash($session),
        ];
    }

    /**
     * Convierte el post en HTML listo para insertar en la vista.
     *
     * @param array<string, mixed> $post
     */
    public function render(array $post): string
    {
        return $this->renderer->renderFullPost($post);
    }

    /**
     * Procesa la acción enviada por POST y hace un redirect para evitar el
     * reposteo del formulario.
     */
    private function handleAction(string $postId, array &$session): void
    {
        $action = (string) ($_POST['action'] ?? '');

        try {
            if ($action === 'toggle_like') {
                if (!$this->service->isAuthenticated($session)) {
                    throw new \RuntimeException('Debes iniciar sesión para likear.');
                }
                $this->service->toggleLike($postId, $session);
                $this->setFlash($session, '¡Guardamos tu like!');
            } elseif ($action === 'comment') {
                $author = (string) ($_POST['author'] ?? '');
                $text = (string) ($_POST['text'] ?? '');
                $parentId = isset($_POST['parent_comment_id']) && $_POST['parent_comment_id'] !== ''
                    ? (string) $_POST['parent_comment_id']
                    : null;
                $this->service->addComment($postId, $parentId, $author, $text);
                $this->setFlash($session, 'Comentario publicado.');
            } else {
                $this->setFlash($session, 'Acción desconocida.');
            }
        } catch (\Throwable $e) {
            $this->setFlash($session, $e->getMessage());
        }

        $url = $this->currentUrl($postId);
        header('Location: ' . $url);
        exit;
    }

    /**
     * Construye la URL actual asegurando que se preserve el id.
     */
    private function currentUrl(string $postId): string
    {
        $base = strtok($_SERVER['REQUEST_URI'] ?? ('/POSTS/index.php?id=' . $postId), '?') ?: '/POSTS/index.php';
        return $base . '?id=' . urlencode($postId);
    }

    /**
     * Guarda un mensaje flash en sesión para mostrar tras el redirect.
     */
    private function setFlash(array &$session, string $message): void
    {
        $session['post_flash'] = $message;
    }

    /**
     * Obtiene y limpia el mensaje flash de la sesión.
     */
    private function consumeFlash(array &$session): ?string
    {
        $message = $session['post_flash'] ?? null;
        if ($message !== null) {
            unset($session['post_flash']);
        }
        return $message;
    }
}
