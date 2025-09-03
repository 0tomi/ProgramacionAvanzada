<?php
include "funciones.php";

$username    = trim($_POST['username']);
$password    = trim($_POST['password']);
$description = trim($_POST['description'] ?? '');

$profilePicturePath = '';
if (!empty($_FILES['profile_picture']['name'])) {
    $targetDir = __DIR__ . '/../imagenes/profilePictures/';
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0777, true);
    }
    $safeName   = preg_replace('/[^a-zA-Z0-9._-]/', '_', $_FILES['profile_picture']['name']);
    $targetFile = $targetDir . $safeName;
    if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $targetFile)) {
        $profilePicturePath = 'imagenes/profilePictures/' . $safeName;
    }
}

$usuarios = leerUsuarios();

// Verificar si el usuario ya existe
foreach ($usuarios as $user) {
    if ($user['username'] === $username) {
        header("Location: ../register.php?error=Usuario+ya+existe");
        exit;
    }
}

// Guardar con contraseña encriptada
$usuarios[] = [
    "id" => "u" . (count($usuarios) + 1),
    "username" => $username,
    "password" => password_hash($password, PASSWORD_DEFAULT),
    "user_profile_picture" => $profilePicturePath,
    "description" => $description
];

guardarUsuarios($usuarios);

header("Location: ../Inicio/inicio.php?success=Registro+exitoso");
