<?php


use Core\Authenticator;
use Http\Forms\LoginForm;


$email = $_POST['email'];
$password = $_POST['password'];


// match the credentials


$form = new LoginForm();

if($form->validate($email, $password) )
{
    $auth = new Authenticator();

    if( $auth->attempt($email, $password))
    {
        redirect('/');
    } 

    $form->addError('email', 'No matching account found for that email and password.');

}

return view("session/create.view.php", [
    'errors' => $form->getErrors()
]);
