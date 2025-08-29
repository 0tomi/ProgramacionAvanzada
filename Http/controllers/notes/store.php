<?php

use \Core\Validator;
use Core\App;
use Core\Database;

$db = App::resolve(Database::class);

$errors = [];

//adding validation to the form
if (!Validator::string($_POST['body']))
{
    $errors['body'] = "A body of no more than 1000 characters is required.";
}

if (!empty($errors))
{
    return view("notes/create.view.php", [
    "heading" => "Create Note",
    "errors" => $errors
    ]);
}


$db->Query("INSERT INTO notes(body, username, first_name, last_name, about, user_id_fkey) VALUES (:body, :username, :first_name, :last_name, :about, :user_id_fkey)", [
    'body' => $_POST['body'],
    'username' => $_POST['username'],
    'first_name' => $_POST['first-name'],
    'last_name' => $_POST['last-name'],
    'about' => $_POST['body'],
    'user_id_fkey' => 1
]);

header("location: /notes");


