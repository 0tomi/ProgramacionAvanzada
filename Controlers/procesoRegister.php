<?php
include "../Model/UserFactory.php";

$username = trim($_POST['username']);
$password = trim($_POST['password']);

$userFactory = new UserFactory();

// Verificar si el usuario ya existe, y crearlo si no.
if (!$userFactory->registerUser($username, $password)){
    header("Location: ../LOGIN/_register.php?error=$userFactory->error");
    exit;
}

// Exito
header("Location: ../LOGIN/_login.php?success=Registro+exitoso");
