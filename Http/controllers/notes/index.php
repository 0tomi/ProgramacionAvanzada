<?php

if (!($_SESSION['user'] ?? false)) {
    http_response_code(403);
    require base_path('views/403.php');
    exit();
}

use Core\App;
use Core\Database;

$db = App::resolve(Database::class);

$query = "SELECT id, body, username, first_name, last_name, about FROM notes";
$notes = $db->Query($query)->get();

view("notes/index.view.php", [
    "heading" => "My Notes",
    "notes" => $notes
]);