<?php
//echo('holaaa');
require_once __DIR__ . '/../Model/Usuario.php';
session_start();
// Estado
$isLoggedIn = !empty($_SESSION['user']);

$goBackRute = "Location: ".$preruta."Views/LOGIN/_login.php?error= Debes estar autenticado para acceder a esta seccion.";

if (!$isLoggedIn) {
  header($goBackRute);
  exit;
}

// Datos del perfil que se van a mostrar
$userName = $isLoggedIn
    ? $_SESSION['user']->getNombre()
    : 'Invitado'; 

if ($isLoggedIn)
    $profilePicture = !empty($_SESSION['user']->getProfilePhoto()) ?
        $_SESSION['user']->getProfilePhoto()
        : 'imagenes/profilePictures/user.png';
else $profilePicture = 'imagenes/profilePictures/defaultProfilePicture.png';

// Asegurar que la ruta funcione desde subdirectorios
if (!preg_match('#^https?://#', $profilePicture) && $profilePicture[0] !== '/') {
    if (!empty($preruta))
        $profilePicture = $preruta . ltrim($profilePicture, '/');
    else $profilePicture = ltrim($profilePicture, '/');
}