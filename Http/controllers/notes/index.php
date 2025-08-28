<?php

use Core\App;
use Core\Database;

$db = App::resolve(Database::class);

$query = "SELECT * FROM notes WHERE user_id_fkey = 1";

$notes = $db->Query($query)->get();

view("notes/index.view.php", [
    "heading" => "My Notes",
    "notes" => $notes
]);