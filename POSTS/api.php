<?php
/**
 * API REST ligera para gestionar los posts del módulo.
 *
 * Todas las operaciones delegan en PostService, que es la capa de dominio
 * compartida por la vista en PHP. De esta manera existe un único punto de
 * verdad para las reglas de negocio (likes, comentarios, creación, etc.).
 */
declare(strict_types=1);

use Posts\Lib\PostService;
use Posts\Lib\PostStorage;

require_once __DIR__ . '/../includes/Usuario.php';
require_once __DIR__ . '/lib/PostStorage.php';
require_once __DIR__ . '/lib/PostService.php';

ini_set('display_errors', '0');
ini_set('log_errors', '1');

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

header('Content-Type: application/json; charset=utf-8');

$service = new PostService(new PostStorage());

/**
 * @param array<string, mixed> $payload
 */
function json_out(array $payload, int $status = 200): void
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

try {
    $action = (string) ($_GET['action'] ?? 'list');

    switch ($action) {
        case 'get':
            $id = (string) ($_GET['id'] ?? '');
            if ($id === '') {
                json_out(['ok' => false, 'error' => 'id requerido'], 400);
            }
            $post = $service->getPost($id, $_SESSION);
            if ($post === null) {
                json_out(['ok' => false, 'error' => 'Post no encontrado'], 404);
            }
            json_out(['ok' => true, 'item' => $post]);
            break;

        case 'list':
            $posts = $service->listPosts($_SESSION);
            json_out(['ok' => true, 'items' => $posts]);
            break;

        case 'liked_ids':
            $ids = array_values(array_map('strval', $_SESSION['likes'] ?? []));
            json_out(['ok' => true, 'ids' => $ids]);
            break;

        case 'like':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                json_out(['ok' => false, 'error' => 'Método no permitido'], 405);
            }
            if (!$service->isAuthenticated($_SESSION)) {
                json_out(['ok' => false, 'error' => 'Debes iniciar sesión para likear'], 401);
            }
            $input = json_decode(file_get_contents('php://input') ?: '{}', true) ?? [];
            $postId = (string) ($input['post_id'] ?? '');
            $result = $service->toggleLike($postId, $_SESSION);
            json_out(['ok' => true] + $result);
            break;

        case 'comment':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                json_out(['ok' => false, 'error' => 'Método no permitido'], 405);
            }
            $input = json_decode(file_get_contents('php://input') ?: '{}', true) ?? [];
            $postId = (string) ($input['post_id'] ?? '');
            $parent = isset($input['parent_comment_id']) && $input['parent_comment_id'] !== ''
                ? (string) $input['parent_comment_id']
                : null;
            $author = (string) ($input['author'] ?? '');
            $text = (string) ($input['text'] ?? '');
            $comment = $service->addComment($postId, $parent, $author, $text);
            json_out(['ok' => true, 'comment' => $comment]);
            break;

        case 'create':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                json_out(['ok' => false, 'error' => 'Método no permitido'], 405);
            }
            if (!$service->isAuthenticated($_SESSION)) {
                json_out(['ok' => false, 'error' => 'Debes iniciar sesión para publicar'], 401);
            }
            $post = $service->createPost($_SESSION, (string) ($_POST['text'] ?? ''), $_FILES['image'] ?? []);
            json_out(['ok' => true, 'item' => $post]);
            break;

        default:
            json_out(['ok' => false, 'error' => 'Acción no soportada'], 400);
    }
} catch (InvalidArgumentException $e) {
    json_out(['ok' => false, 'error' => $e->getMessage()], 400);
} catch (RuntimeException $e) {
    $code = str_contains(strtolower($e->getMessage()), 'no encontrado') ? 404 : 500;
    json_out(['ok' => false, 'error' => $e->getMessage()], $code);
} catch (Throwable $e) {
    json_out(['ok' => false, 'error' => $e->getMessage()], 500);
}
