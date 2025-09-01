<?php
// /POSTS/api.php
declare(strict_types=1);

// no imprimir warnings en HTML
ini_set('display_errors', '0'); ini_set('log_errors', '1');

session_start(); // ← para recordar likes por navegador

header('Content-Type: application/json; charset=utf-8');

const POSTS_JSON_PATH = __DIR__ . '/../JSON/POST.json';

function ensure_posts_file(): void {
  $dir = dirname(POSTS_JSON_PATH);
  if (!is_dir($dir)) { mkdir($dir, 0775, true); }
  if (!file_exists(POSTS_JSON_PATH)) { file_put_contents(POSTS_JSON_PATH, "[]"); }
}
function read_posts(): array {
  ensure_posts_file();
  $raw = @file_get_contents(POSTS_JSON_PATH);
  if ($raw === false) { throw new Exception('No se pudo leer POST.json'); }
  $data = json_decode($raw, true);
  if (!is_array($data)) { throw new Exception('POST.json inválido'); }
  return $data;
}
function write_posts(array $arr): void {
  ensure_posts_file();
  $fp = fopen(POSTS_JSON_PATH, 'c+');
  if (!$fp) throw new Exception('No se pudo abrir POST.json');
  if (!flock($fp, LOCK_EX)) { fclose($fp); throw new Exception('No se pudo bloquear POST.json'); }
  ftruncate($fp, 0); rewind($fp);
  fwrite($fp, json_encode($arr, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
  fflush($fp); flock($fp, LOCK_UN); fclose($fp);
}

if (!isset($_SESSION['likes'])) $_SESSION['likes'] = []; // set de ids likeados

try {
  $action = $_GET['action'] ?? 'list';

  if ($action === 'get') {
    $id = $_GET['id'] ?? '';
    if ($id === '') { http_response_code(400); echo json_encode(['ok'=>false,'error'=>'id requerido']); exit; }
    $items = read_posts();
    $liked = array_flip($_SESSION['likes']);
    foreach ($items as $p) {
      if (($p['id'] ?? '') === $id) {
        $p['counts']  = $p['counts']  ?? ['likes'=>0,'replies'=>0];
        $p['replies'] = $p['replies'] ?? [];
        $p['author']  = $p['author']  ?? ['id'=>'uX','handle'=>'anon','name'=>'Anónimo'];
        // hint para el cliente
        $p['viewer'] = ['liked' => isset($liked[$p['id']])];
        echo json_encode(['ok'=>true, 'item'=>$p], JSON_UNESCAPED_UNICODE); exit;
      }
    }
    http_response_code(404);
    echo json_encode(['ok'=>false,'error'=>'Post no encontrado']); exit;
  }

  // Devolvemos los IDs likeados en esta sesión
  if (($action ?? '') === 'liked_ids') {
    session_start();
    header('Content-Type: application/json; charset=utf-8');
    $ids = array_values($_SESSION['likes'] ?? []);
    echo json_encode(['ok' => true, 'ids' => $ids]);
    exit;
  }


  if ($action === 'list') {
    $items = read_posts();
    foreach ($items as &$p) {
      $p['counts']  = $p['counts']  ?? ['likes'=>0,'replies'=>0];
      $p['replies'] = $p['replies'] ?? [];
      $p['author']  = $p['author']  ?? ['id'=>'uX','handle'=>'anon','name'=>'Anónimo'];
    }
    echo json_encode(['ok'=>true,'items'=>$items], JSON_UNESCAPED_UNICODE); exit;
  }

  // === LIKE (toggle por sesión, sin login real) ===
  if ($action === 'like' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input') ?: '{}', true) ?? [];
    $postId = (string)($input['post_id'] ?? '');
    if ($postId === '') { http_response_code(400); echo json_encode(['ok'=>false,'error'=>'post_id requerido']); exit; }

    $items = read_posts();
    foreach ($items as &$p) {
      if (($p['id'] ?? '') === $postId) {
        $p['counts'] = $p['counts'] ?? ['likes'=>0,'replies'=>0];
        $liked = in_array($postId, $_SESSION['likes'], true);
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
        echo json_encode(['ok'=>true,'liked'=>$liked,'like_count'=>$p['counts']['likes']]); exit;
      }
    }
    http_response_code(404); echo json_encode(['ok'=>false,'error'=>'Post no encontrado']); exit;
  }

  // === COMENTAR (raíz o respuesta) ===
  if ($action === 'comment' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input') ?: '{}', true) ?? [];
    $postId = (string)($input['post_id'] ?? '');
    $parent = $input['parent_comment_id'] ?? null;
    $author = trim((string)($input['author'] ?? ''));
    $text   = trim((string)($input['text'] ?? ''));
    if ($postId === '') { http_response_code(400); echo json_encode(['ok'=>false,'error'=>'post_id requerido']); exit; }
    if ($text === '' || mb_strlen($text,'UTF-8') > 280) { http_response_code(400); echo json_encode(['ok'=>false,'error'=>'Comentario inválido (1..280)']); exit; }

    $items = read_posts();
    foreach ($items as &$p) {
      if (($p['id'] ?? '') === $postId) {
        $cid = (string)(time()) . mt_rand(1000,9999);
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
        echo json_encode(['ok'=>true,'comment'=>$comment]); exit;
      }
    }
    http_response_code(404); echo json_encode(['ok'=>false,'error'=>'Post no encontrado']); exit;
  }

  http_response_code(400);
  echo json_encode(['ok'=>false,'error'=>'Acción no soportada']);
} catch (Throwable $e) {
  http_response_code(500);
  echo json_encode(['ok'=>false,'error'=>$e->getMessage()]);
}
