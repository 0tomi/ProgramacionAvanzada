<?php

if (!($_SESSION['user'] ?? false)) {
    http_response_code(403);
    require base_path('views/403.php');
    exit();
}

$db = new Core\Database(require base_path('public/assets/config.php'));
$notas = $db->Query("SELECT username, first_name, last_name, about, body FROM notes")->get();

view("forum.view.php", [
    "notas" => $notas
]);