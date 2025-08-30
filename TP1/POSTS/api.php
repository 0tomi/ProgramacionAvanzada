<?php
// POSTS/api.php
declare(strict_types=1);
require __DIR__ . '/auth.php';
header('Content-Type: application/json; charset=utf-8');

const POSTS_JSON_PATH = __DIR__ . '/../JSON/POST.json';

if (!file_exists(POSTS_JSON_PATH)) file_put_contents(POSTS_JSON_PATH, "[]");

function ensure_posts_file(): void {
  $dir = dirname(POSTS_JSON_PATH);
  if (!is_dir($dir)) {
    // si te interesa, creá la carpeta; si no, tirá error
    mkdir($dir, 0775, true);
  }
  if (!file_exists(POSTS_JSON_PATH)) {
    file_put_contents(POSTS_JSON_PATH, "[]");
  }
}

function read_posts(): array {
  ensure_posts_file();
  $raw = file_get_contents(POSTS_JSON_PATH);
  $data = json_decode($raw ?: '[]', true);
  return is_array($data) ? $data : [];
}

function write_posts(array $arr): void {
  ensure_posts_file();
  // bloqueo básico para evitar corrupciones si hay 2 escrituras simultáneas
  $fp = fopen(POSTS_JSON_PATH, 'c+');
  if (!$fp) throw new Exception('No se pudo abrir POST.json');
  if (!flock($fp, LOCK_EX)) { fclose($fp); throw new Exception('No se pudo bloquear POST.json'); }
  ftruncate($fp, 0);
  rewind($fp);
  fwrite($fp, json_encode($arr, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
  fflush($fp);
  flock($fp, LOCK_UN);
  fclose($fp);
}

if (!isset($_SESSION['likes'])) $_SESSION['likes'] = [];

$action = $_GET['action'] ?? 'list';

if ($action === 'get') {
  $id = $_GET['id'] ?? '';
  if ($id === '') {
    http_response_code(400);
    echo json_encode(['ok'=>false,'error'=>'id requerido']); exit;
  }
  $items = read_posts();
  $liked = array_flip($_SESSION['likes'] ?? []);
  $viewer = auth_user();

  foreach ($items as $p) {
    if ($p['id'] === $id) {
      // normalizar y enriquecer igual que en list
      $p['counts']  = $p['counts']  ?? ['likes'=>0,'replies'=>0];
      $p['replies'] = $p['replies'] ?? [];
      $p['author']  = $p['author']  ?? ['id'=>'uX','handle'=>'anon','name'=>'Anónimo'];
      $p['viewer']  = [
        'liked' => isset($liked[$p['id']]),
        'authenticated' => $viewer !== null,
        'handle' => $viewer['handle'] ?? null,
        'name'   => $viewer['name'] ?? null,
      ];
      echo json_encode(['ok'=>true,'item'=>$p], JSON_UNESCAPED_UNICODE); exit;
    }
  }
  http_response_code(404);
  echo json_encode(['ok'=>false,'error'=>'Post no encontrado']); exit;
}

try {
  /* ====== Auth dev helpers ====== */
  if ($action === 'login') {
    $h = $_GET['handle'] ?? '';
    $p = $_GET['password'] ?? '';
    if ($h && $p && auth_login($h,$p)) {
      echo json_encode(['ok'=>true,'user'=>auth_user()]); exit;
    } else { echo json_encode(['ok'=>false,'error'=>'Credenciales inválidas']); exit; }
  }
  if ($action === 'logout') {
    auth_logout(); echo json_encode(['ok'=>true]); exit;
  }
  if ($action === 'me') {
    echo json_encode(['ok'=>true,'user'=>auth_user()]); exit;
  }

  /* ====== List posts ====== */
  if ($action === 'list') {
    $items = read_posts();
    $liked = array_flip($_SESSION['likes']);
    $viewer = auth_user();
    // inyectar viewer y asegurar campos
    $out = array_map(function($p) use ($liked, $viewer) {
      $p['counts'] = $p['counts'] ?? ['likes'=>0,'replies'=>0];
      $p['replies'] = $p['replies'] ?? [];
      $p['author'] = $p['author'] ?? ['id'=>'uX','handle'=>'anon','name'=>'Anónimo'];
      $p['viewer'] = [
        'liked' => isset($liked[$p['id']]),
        'authenticated' => $viewer !== null,
        'handle' => $viewer['handle'] ?? null,
        'name'   => $viewer['name'] ?? null,
      ];
      return $p;
    }, $items);
    echo json_encode(['ok'=>true, 'items'=>$out], JSON_UNESCAPED_UNICODE); exit;
  }

  /* ====== Like (requiere login) ====== */
  if ($action === 'like') {
    auth_require(); // 401 si no está logueado
    $input = json_decode(file_get_contents('php://input') ?: '{}', true) ?? [];
    $postId = (string)($input['post_id'] ?? '');
    if ($postId === '') throw new Exception('post_id requerido');

    $items = read_posts();
    foreach ($items as &$p) {
      if ($p['id'] === $postId) {
        $liked = in_array($postId, $_SESSION['likes'], true);
        if ($liked) {
          $p['counts']['likes'] = max(0, (int)$p['counts']['likes'] - 1);
          $_SESSION['likes'] = array_values(array_filter($_SESSION['likes'], fn($id)=>$id!==$postId));
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
    throw new Exception('Post no encontrado');
  }

  /* ====== Comment (requiere login) ====== */
  if ($action === 'comment') {
    auth_require();
    $input = json_decode(file_get_contents('php://input') ?: '{}', true) ?? [];
    $postId = (string)($input['post_id'] ?? '');
    $parent = $input['parent_comment_id'] ?? null;
    $authorName = auth_user()['name'];
    $authorHandle = auth_user()['handle'];
    $text = trim((string)($input['text'] ?? ''));
    if ($postId==='') throw new Exception('post_id requerido');
    if ($text==='' || mb_strlen($text,'UTF-8')>280) throw new Exception('Comentario inválido (1..280)');

    $items = read_posts();
    foreach ($items as &$p) {
      if ($p['id'] === $postId) {
        $cid = (string)(time()).mt_rand(1000,9999);
        $comment = [
          'id' => $cid,
          'parent_id' => $parent ? (string)$parent : null,
          'author' => $authorName . "(@" . $authorHandle . ")",
          'text' => $text,
          'created_at' => gmdate('c')
        ];
        if (!isset($p['replies']) || !is_array($p['replies'])) $p['replies'] = [];
        array_unshift($p['replies'], $comment);
        if (!isset($p['counts']['replies'])) $p['counts']['replies']=0;
        $p['counts']['replies']++;

        write_posts($items);
        echo json_encode(['ok'=>true,'comment'=>$comment]); exit;
      }
    }
    throw new Exception('Post no encontrado');
  }

  echo json_encode(['ok'=>false,'error'=>'Acción no soportada']); exit;

} catch (Throwable $e) {
  http_response_code(400);
  echo json_encode(['ok'=>false,'error'=>$e->getMessage()], JSON_UNESCAPED_UNICODE);
}
