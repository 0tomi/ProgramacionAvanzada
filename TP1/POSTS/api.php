<?php
// /POSTS/api.php
declare(strict_types=1);

// ---- Ajustes de errores y cabeceras ----
ini_set('display_errors', '0'); // evita HTML en respuestas
ini_set('log_errors', '1');

session_start(); // para likes por sesión, autor fake, etc.
header('Content-Type: application/json; charset=utf-8');

// ---- Rutas/Constantes ----
const POSTS_JSON_PATH = __DIR__ . '/../JSON/POST.json';
const UPLOADS_DIR     = __DIR__ . '/uploads';       // carpeta donde guardar imágenes
const MAX_IMG_BYTES   = 5 * 1024 * 1024;            // 5MB

// ---- Helpers generales ----
function json_out(array $payload, int $code = 200): void {
  http_response_code($code);
  echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
  exit;
}
function ensure_posts_file(): void {
  $dir = dirname(POSTS_JSON_PATH);
  if (!is_dir($dir)) { @mkdir($dir, 0775, true); }
  if (!file_exists(POSTS_JSON_PATH)) { file_put_contents(POSTS_JSON_PATH, "[]"); }
}
function read_posts(): array {
  ensure_posts_file();
  $raw = @file_get_contents(POSTS_JSON_PATH);
  if ($raw === false) throw new RuntimeException('No se pudo leer POST.json');
  $data = json_decode($raw, true);
  if (!is_array($data)) throw new RuntimeException('POST.json inválido');
  return $data;
}
function write_posts(array $arr): void {
  ensure_posts_file();
  $fp = fopen(POSTS_JSON_PATH, 'c+');
  if (!$fp) throw new RuntimeException('No se pudo abrir POST.json');
  if (!flock($fp, LOCK_EX)) { fclose($fp); throw new RuntimeException('No se pudo bloquear POST.json'); }
  ftruncate($fp, 0);
  rewind($fp);
  fwrite($fp, json_encode($arr, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
  fflush($fp);
  flock($fp, LOCK_UN);
  fclose($fp);
}
function ensure_uploads_dir(): void {
  if (!is_dir(UPLOADS_DIR)) { @mkdir(UPLOADS_DIR, 0775, true); }
}
function gen_id(): string {
  // ID simple tipo "timestamp + random"
  return (string)(time()) . str_pad((string)mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
}
function enrich_post(array $p): array {
  $p['counts']  = $p['counts']  ?? ['likes'=>0, 'replies'=>0];
  $p['replies'] = $p['replies'] ?? [];
  $p['author']  = $p['author']  ?? ['id'=>'uX','handle'=>'anon','name'=>'Anónimo'];
  $likedSet     = array_flip($_SESSION['likes'] ?? []);
  $p['viewer']  = ['liked' => isset($likedSet[$p['id'] ?? ''])];
  return $p;
}

// ---- Router de acciones ----
try {
  $action = $_GET['action'] ?? 'list';

  // GET: un post por id
  if ($action === 'get') {
    $id = (string)($_GET['id'] ?? '');
    if ($id === '') json_out(['ok'=>false,'error'=>'id requerido'], 400);

    $items = read_posts();
    foreach ($items as $p) {
      if (($p['id'] ?? '') === $id) {
        json_out(['ok'=>true, 'item'=> enrich_post($p)]);
      }
    }
    json_out(['ok'=>false,'error'=>'Post no encontrado'], 404);
  }

  // GET: todos los posts
  if ($action === 'list') {
    $items = read_posts();
    $items = array_map('enrich_post', $items);
    json_out(['ok'=>true, 'items'=>$items]);
  }

  // GET: ids likeados en esta sesión (para pintar ♥ en inicio)
  if ($action === 'liked_ids') {
    $ids = array_values($_SESSION['likes'] ?? []);
    json_out(['ok'=>true, 'ids'=>$ids]);
  }

  // POST: toggle like
  if ($action === 'like' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $input  = json_decode(file_get_contents('php://input') ?: '{}', true) ?? [];
    $postId = (string)($input['post_id'] ?? '');
    if ($postId === '') json_out(['ok'=>false,'error'=>'post_id requerido'], 400);

    $items = read_posts();
    $found = false;
    foreach ($items as &$p) {
      if (($p['id'] ?? '') === $postId) {
        $found = true;
        $p['counts'] = $p['counts'] ?? ['likes'=>0,'replies'=>0];

        $liked = in_array($postId, $_SESSION['likes'] ?? [], true);
        if ($liked) {
          $p['counts']['likes'] = max(0, (int)$p['counts']['likes'] - 1);
          $_SESSION['likes'] = array_values(array_filter($_SESSION['likes'], fn($x) => $x !== $postId));
          $liked = false;
        } else {
          $p['counts']['likes'] = (int)$p['counts']['likes'] + 1;
          $_SESSION['likes'][] = $postId;
          $liked = true;
        }
        write_posts($items);
        json_out(['ok'=>true, 'liked'=>$liked, 'like_count'=>$p['counts']['likes']]);
      }
    }
    if (!$found) json_out(['ok'=>false,'error'=>'Post no encontrado'], 404);
  }

  // POST: comentar (raíz o respuesta)
  if ($action === 'comment' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $input   = json_decode(file_get_contents('php://input') ?: '{}', true) ?? [];
    $postId  = (string)($input['post_id'] ?? '');
    $parent  = $input['parent_comment_id'] ?? null;
    $author  = trim((string)($input['author'] ?? ''));
    $text    = trim((string)($input['text'] ?? ''));

    if ($postId === '')                        json_out(['ok'=>false,'error'=>'post_id requerido'], 400);
    if ($text === '' || mb_strlen($text) > 280) json_out(['ok'=>false,'error'=>'Comentario inválido (1..280)'], 400);

    $items = read_posts();
    foreach ($items as &$p) {
      if (($p['id'] ?? '') === $postId) {
        $cid = gen_id();
        $comment = [
          'id'         => $cid,
          'parent_id'  => $parent ? (string)$parent : null,
          'author'     => $author !== '' ? $author : 'Anónimo',
          'text'       => $text,
          'created_at' => gmdate('c'),
        ];
        if (!isset($p['replies']) || !is_array($p['replies'])) $p['replies'] = [];
        array_unshift($p['replies'], $comment);
        $p['counts']['replies'] = (int)($p['counts']['replies'] ?? 0) + 1;

        write_posts($items);
        json_out(['ok'=>true, 'comment'=>$comment]);
      }
    }
    json_out(['ok'=>false,'error'=>'Post no encontrado'], 404);
  }

  // POST (multipart/form-data): crear post (texto + imagen opcional)
  if ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    // Texto
    $text = trim((string)($_POST['text'] ?? ''));
    if ($text === '' || mb_strlen($text, 'UTF-8') > 280) {
      json_out(['ok'=>false,'error'=>'Texto requerido (1..280)'], 400);
    }

    // Imagen (opcional)
    $mediaUrl = '';
    if (!empty($_FILES['image']) && is_uploaded_file($_FILES['image']['tmp_name'])) {
      $file = $_FILES['image'];
      $mime = mime_content_type($file['tmp_name']) ?: '';
      $allowed = ['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp','image/gif'=>'gif'];

      if (!isset($allowed[$mime]))  json_out(['ok'=>false,'error'=>'Formato de imagen no permitido'], 400);
      if ($file['size'] > MAX_IMG_BYTES) json_out(['ok'=>false,'error'=>'La imagen supera 5MB'], 400);

      ensure_uploads_dir();
      $ext   = $allowed[$mime];
      $fname = gen_id().'.'.$ext;
      $dest  = UPLOADS_DIR.'/'.$fname;

      if (!@move_uploaded_file($file['tmp_name'], $dest)) {
        json_out(['ok'=>false,'error'=>'No se pudo guardar la imagen'], 500);
      }

      // URL relativa servible desde /POSTS/
      $mediaUrl = 'uploads/'.$fname;
    }

    // Autor "fake" (hasta tener login real)
    $author = [
      'id'     => $_SESSION['user_id']      ?? 'u1',
      'handle' => $_SESSION['username']     ?? 'anon',
      'name'   => $_SESSION['display_name'] ?? 'Anónimo',
    ];

    // Crear post
    $post = [
      'id'         => gen_id(),
      'author'     => $author,
      'text'       => $text,
      'media_url'  => $mediaUrl,                  // '' si no hay imagen
      'counts'     => ['likes'=>0, 'replies'=>0],
      'replies'    => [],
      'created_at' => gmdate('c'),
    ];

    $items = read_posts();
    array_unshift($items, $post);
    write_posts($items);

    // Enriquecido con viewer.liked = false por defecto
    json_out(['ok'=>true, 'item'=> enrich_post($post)], 200);
  }

  // Acción no soportada
  json_out(['ok'=>false,'error'=>'Acción no soportada'], 400);

} catch (Throwable $e) {
  json_out(['ok'=>false,'error'=>$e->getMessage()], 500);
}
