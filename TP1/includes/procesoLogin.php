<?php
include "funciones.php";

$username = trim($_POST['username']);
$password = trim($_POST['password']);

$usuarios = leerUsuarios();

foreach ($usuarios as $user) {
    if ($user['username'] === $username && password_verify($password, $user['password'])) {
        session_start();
        // Proximamente esto ira dentro de una clase, no llegamos
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $username;
        $_SESSION['user_profile_picture'] = $user['user_profile_picture'];
        $_SESSION['description'] = $user['description'];
        $_SESSION['flash'] = ['type' => 'success', 'msg' => 'Inicio de sesión correcto'];
        header("Location: ../Inicio/inicio.php");
        exit;
    }
}

// Si no encontró coincidencias
header("Location: ../login.php?error=Usuario+o+contraseña+incorrectos");
exit;
