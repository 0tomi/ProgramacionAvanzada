<?php
include "includes/funciones.php";

$username = trim($_POST['username']);
$password = trim($_POST['password']);

$usuarios = leerUsuarios();

foreach ($usuarios as $user) {
    if ($user['username'] === $username && password_verify($password, $user['password'])) {
        session_start();
        $_SESSION['username'] = $username;
        header("Location: Inicio/inicio.php");
        exit;
    }
}

header("Location: login.php?error=Credenciales+inválidas");
