<?php

use Core\App;
use Core\Authenticator;
use Core\Database;
use Core\Validator;

$email = $_POST['email'];
$password = $_POST['password'];

// validate the user

if(!Validator::email($email))
{
    $errors['email'] = "Please enter a valid email address.";
}

if(!Validator::string($password, 7, 255))
{
    $errors['password'] = "Please enter a password between 7 and 255 characters.";
}

if(!empty($errors))
{
    return view("registration/create.view.php", [
        "errors" => $errors
    ]);
}

$db = App::resolve(Database::class);

// Check if the account already exists

$user = $db->Query("SELECT * FROM users WHERE email = :email", [
    'email' => $email
])->find();

if($user)
{
    // if someone with that email already exists and has an account redirect him to a login page.
    $_SESSION["user"] = [
        "email" => $email
    ];

    header("location: /");
    exit();
}
else {

    // if not, save one to the database and then log the user in, and redirect.
    $db->Query("INSERT INTO users(email, password) 
    VALUES (:email, :password)"
    , [
        "email" => $email,
        "password" => password_hash($password, PASSWORD_BCRYPT) // hashing the password for secure
    ]);

    (new Authenticator)->login($user);


    // mark that the user has logged in.
    $_SESSION["user"] = [
        "email" => $email
    ];

    header("location: /");
    exit();
}