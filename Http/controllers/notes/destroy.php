<?php

use Core\App;
use Core\Database;

$db = App::resolve(Database::class);

$currentUserID = 1;


$query = "SELECT * FROM notes WHERE id = :id";

$note = $db->Query($query, [
    'id' => $_GET['id']
])->FindOrFail();

authorize($note['user_id_fkey'] === $currentUserID);

$db->query("DELETE FROM notes WHERE id = :id", [
    "id" => $_GET['id']
]);

header("location: /notes");
exit();
