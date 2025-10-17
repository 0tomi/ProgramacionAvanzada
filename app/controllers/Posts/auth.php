<?php
// POSTS/auth.php
declare(strict_types=1);
session_start();

const USERS_FILE = dirname(__DIR__, 3) . '/storage/data/posts/users.json';

function auth_load_users(): array {
  if (!file_exists(USERS_FILE)) return [];
  return json_decode(file_get_contents(USERS_FILE) ?: '[]', true) ?: [];
}

function auth_login(string $handle, string $password): bool {
  $users = auth_load_users();
  foreach ($users as $u) {
    if (strcasecmp($u['handle'], $handle) === 0 && $u['password'] === $password) {
      $_SESSION['user'] = ['id'=>$u['id'], 'handle'=>$u['handle'], 'name'=>$u['name']];
      return true;
    }
  }
  return false;
}

function auth_logout(): void { unset($_SESSION['user']); }

function auth_user(): ?array { return $_SESSION['user'] ?? null; }

function auth_require(): void {
  if (!auth_user()) {
    http_response_code(401);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['ok'=>false,'error'=>'No autenticado']);
    exit;
  }
}
