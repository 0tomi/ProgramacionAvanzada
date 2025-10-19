<?php
/**
 * /POSTS/api.php
 *
 * Punto de entrada de la API de publicaciones.
 * Expone un router muy simple (parámetro ?action=) y delega la interacción con la
 * base de datos al PostRepository. Devuelve siempre JSON y controla los errores
 * más comunes para que el front pueda reaccionar con mensajes claros.
 */
declare(strict_types=1);

require_once __DIR__ . '/../Model/Usuario.php';
require_once __DIR__ . '/../Model/PostRepository.php';

ini_set('display_errors', '0');
ini_set('log_errors', '1');

session_start();
header('Content-Type: application/json; charset=utf-8');

const POST_IMAGE_DIR = __DIR__ . '/../Resources/PostImages';
const POST_IMAGE_ROUTE_PREFIX = 'Resources/PostImages';
const MAX_IMAGE_SIZE_BYTES = 5 * 1024 * 1024; // 5MB

/**
 * Envía una respuesta JSON y detiene la ejecución.
 */
function jsonResponse(array $payload, int $status = 200): void
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

/**
 * Devuelve el usuario autenticado (objeto User) o null.
 */
function currentUser(): ?User
{
    $user = $_SESSION['user'] ?? null;
    return $user instanceof User ? $user : null;
}

/**
 * Obtiene el ID del usuario logueado o null.
 */
function currentUserId(): ?int
{
    $user = currentUser();
    return $user ? (int)$user->getIdUsuario() : null;
}

/**
 * Garantiza autenticación y devuelve el usuario activo.
 */
function requireAuth(): User
{
    $user = currentUser();
    if ($user === null) {
        jsonResponse(['ok' => false, 'error' => 'Debes iniciar sesión para realizar esta acción'], 401);
    }
    return $user;
}

/**
 * Crea la carpeta de imágenes si no existe.
 */
function ensureImageDirectory(): void
{
    if (!is_dir(POST_IMAGE_DIR) && !mkdir(POST_IMAGE_DIR, 0775, true) && !is_dir(POST_IMAGE_DIR)) {
        throw new RuntimeException('No se pudo crear la carpeta de imágenes de publicaciones.');
    }
}

/**
 * Guarda la imagen subida y devuelve la ruta relativa que se almacenará en la BD.
 */
function storeUploadedImage(array $file): string
{
    if (!isset($file['error']) || $file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('No se recibió una imagen válida.');
    }
    if (($file['size'] ?? 0) > MAX_IMAGE_SIZE_BYTES) {
        throw new RuntimeException('La imagen supera el tamaño máximo de 5MB.');
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = $finfo ? finfo_file($finfo, $file['tmp_name']) : '';
    if ($finfo) {
        finfo_close($finfo);
    }

    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp',
        'image/gif'  => 'gif',
    ];
    if (!isset($allowed[$mime])) {
        throw new RuntimeException('Formato de imagen no permitido.');
    }

    ensureImageDirectory();

    $filename = sprintf('%s.%s', bin2hex(random_bytes(12)), $allowed[$mime]);
    $destination = POST_IMAGE_DIR . '/' . $filename;
    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        throw new RuntimeException('No se pudo guardar la imagen en el servidor.');
    }

    return POST_IMAGE_ROUTE_PREFIX . '/' . $filename;
}

/**
 * Decodifica el cuerpo JSON de la petición.
 *
 * @throws RuntimeException si el cuerpo no es JSON válido.
 */
function readJsonInput(): array
{
    $raw = file_get_contents('php://input');
    if ($raw === false || trim($raw) === '') {
        return [];
    }
    $decoded = json_decode($raw, true);
    if (!is_array($decoded)) {
        throw new RuntimeException('El cuerpo de la petición no es JSON válido.');
    }
    return $decoded;
}

try {
    $repository = new PostRepository();
    $action = $_GET['action'] ?? 'list';
    $viewerId = currentUserId();

    switch ($action) {
        case 'list':
            $items = $repository->getFeed($viewerId);
            jsonResponse(['ok' => true, 'items' => $items]);

        case 'get':
            $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
            if (!$id) {
                jsonResponse(['ok' => false, 'error' => 'Parámetro id requerido'], 400);
            }
            $post = $repository->getPost((int)$id, $viewerId);
            if ($post === null) {
                jsonResponse(['ok' => false, 'error' => 'Post no encontrado'], 404);
            }
            jsonResponse(['ok' => true, 'item' => $post]);

        case 'liked_ids':
            if ($viewerId === null) {
                jsonResponse(['ok' => true, 'ids' => []]);
            }
            $ids = $repository->listLikedPostIds($viewerId);
            jsonResponse(['ok' => true, 'ids' => $ids]);

        case 'like':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                jsonResponse(['ok' => false, 'error' => 'Método no permitido'], 405);
            }
            $user = requireAuth();
            $body = readJsonInput();
            $postId = isset($body['post_id']) ? filter_var($body['post_id'], FILTER_VALIDATE_INT) : false;
            if (!$postId) {
                jsonResponse(['ok' => false, 'error' => 'post_id requerido'], 400);
            }
            $toggle = $repository->toggleLike((int)$postId, (int)$user->getIdUsuario());
            jsonResponse(['ok' => true] + $toggle);

        case 'comment':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                jsonResponse(['ok' => false, 'error' => 'Método no permitido'], 405);
            }
            $user = requireAuth();
            $body = readJsonInput();

            $postId = isset($body['post_id']) ? filter_var($body['post_id'], FILTER_VALIDATE_INT) : false;
            if (!$postId) {
                jsonResponse(['ok' => false, 'error' => 'post_id requerido'], 400);
            }

            $text = trim((string)($body['text'] ?? ''));
            if ($text === '' || mb_strlen($text, 'UTF-8') > 280) {
                jsonResponse(['ok' => false, 'error' => 'Comentario inválido (1..280 caracteres)'], 400);
            }

            $parentCommentId = null;
            if (isset($body['parent_comment_id']) && $body['parent_comment_id'] !== null) {
                $parentCommentId = filter_var($body['parent_comment_id'], FILTER_VALIDATE_INT);
                if (!$parentCommentId) {
                    jsonResponse(['ok' => false, 'error' => 'parent_comment_id inválido'], 400);
                }
            }

            $comment = $repository->createComment(
                (int)$user->getIdUsuario(),
                (int)$postId,
                $text,
                $parentCommentId !== false ? $parentCommentId : null
            );
            jsonResponse(['ok' => true, 'comment' => $comment]);

        case 'create':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                jsonResponse(['ok' => false, 'error' => 'Método no permitido'], 405);
            }
            $user = requireAuth();

            $text = trim((string)($_POST['text'] ?? ''));
            if ($text === '' || mb_strlen($text, 'UTF-8') > 280) {
                jsonResponse(['ok' => false, 'error' => 'El post debe tener entre 1 y 280 caracteres'], 400);
            }

            $imageRoute = null;
            if (isset($_FILES['image']) && is_array($_FILES['image']) && $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE) {
                $imageRoute = storeUploadedImage($_FILES['image']);
            }

            $post = $repository->createPost((int)$user->getIdUsuario(), $text, $imageRoute);
            jsonResponse(['ok' => true, 'item' => $post]);

        case 'delete':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                jsonResponse(['ok' => false, 'error' => 'Método no permitido'], 405);
            }
            $user = requireAuth();
            $body = readJsonInput();
            $postId = isset($body['post_id']) ? filter_var($body['post_id'], FILTER_VALIDATE_INT) : false;
            if (!$postId) {
                jsonResponse(['ok' => false, 'error' => 'post_id requerido'], 400);
            }
            $repository->deletePost((int)$postId, (int)$user->getIdUsuario());
            jsonResponse(['ok' => true]);

        default:
            jsonResponse(['ok' => false, 'error' => 'Acción no soportada'], 400);
    }
} catch (RuntimeException $e) {
    jsonResponse(['ok' => false, 'error' => $e->getMessage()], 400);
} catch (Throwable $e) {
    error_log($e->getMessage());
    jsonResponse(['ok' => false, 'error' => 'Error inesperado en el servidor'], 500);
}
