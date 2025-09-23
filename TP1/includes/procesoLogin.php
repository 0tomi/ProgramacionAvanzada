<?php
include "funciones.php";
include "Usuario.php";

$username = trim($_POST['username']);
$password = trim($_POST['password']);

$usuarios = leerUsuarios();

foreach ($usuarios as $user) {
    if ($user['username'] === $username && password_verify($password, $user['password'])) {
        session_start();
        $usuario = new User($user['id'], $username, $user['description'], $user['user_profile_picture']);

        $_SESSION['user'] = $usuario;
        header("Location: ../Inicio/inicio.php");
        exit;   
    }
}

// Si no encontró coincidencias
header("Location: ../login.php?error=Usuario+o+contraseña+incorrectos");
exit;
