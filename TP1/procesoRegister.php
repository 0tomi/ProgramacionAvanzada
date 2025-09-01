<?php
include "includes/funciones.php";

$username = trim($_POST['username']);
$password = trim($_POST['password']);

$usuarios = leerUsuarios();

// Verificar si el usuario ya existe
foreach ($usuarios as $user) {
    if ($user['username'] === $username) {
        header("Location: register.php?error=Usuario+ya+existe");
        exit;
    }
}

// Guardar con contraseña encriptada
$usuarios[] = [
    "id" => "u" . (count($usuarios) + 1),
    "username" => $username,
    "password" => password_hash($password, PASSWORD_DEFAULT),
    "user_profile_picture" => ""    // Sin foto de perfil x default
];

guardarUsuarios($usuarios);

header("Location: Inicio/inicio.php?success=Registro+exitoso");
