<?php
include "includes/funciones.php";

$username = trim($_POST['username']);
$password = trim($_POST['password']);

$usuarios = leerUsuarios();

foreach ($usuarios as $user) {
    if ($user['username'] === $username && password_verify($password, $user['password'])) {
        session_start();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $username;
        header("Location: Inicio/inicio.php");
        exit;
    }
}

// Si no encontró coincidencias
header("Location: login.php?error=Usuario+o+contraseña+incorrectos");
exit;
