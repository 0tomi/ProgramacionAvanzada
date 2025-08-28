<?php

use Core\App;
use Core\Database;
use Core\Validator;

$db = App::resolve(Database::class);

$currentUserID = 1;

// find the corresponding note

$query = "SELECT * FROM notes WHERE id = :id";

$note = $db->Query($query, [
    'id' => $_POST['id']
])->FindOrFail();

// authorize that the current user can edit the note

authorize($note['user_id_fkey'] === $currentUserID);

// validate the form

$errors = [];

if (!Validator::string($_POST['body']))
{
    $errors['body'] = "A body of no more than 1000 characters is required.";
}

// if no validation errors, update the record in the notes database table

if(count($errors))
{
    return view("notes/edit.view.php", [
    "heading" => "Edit Note",
    "errors" => $errors,
    "note" => $note
    ]);
}

$db->query("UPDATE notes SET body = :body WHERE id = :id", [
    "id" => $_POST['id'],
    "body" => $_POST['body']
]);

// redirect the user
header("location: /notes");
die();