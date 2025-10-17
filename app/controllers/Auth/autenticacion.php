<?php
require_once dirname(__DIR__, 2) . '/models/Usuario.php';
session_start();
// Estado
$isLoggedIn = !empty($_SESSION['user']);

if ($isLoggedIn) {
  header("Location: /Inicio/inicio.php");
  exit;
}

// Datos del perfil que se van a mostrar
$userName = $isLoggedIn
    ? $_SESSION['user']->getNombre()
    : 'Invitado'; 

if ($isLoggedIn)
    $profilePicture = !empty($_SESSION['user']->getProfilePhoto()) ?
        $_SESSION['user']->getProfilePhoto()
        : '/public/assets/images/profilePictures/user.png';
else $profilePicture = '/public/assets/images/profilePictures/defaultProfilePicture.png';

// Asegurar que la ruta funcione desde subdirectorios
if (!preg_match('#^https?://#', $profilePicture) && $profilePicture[0] !== '/') {
    if (!empty($preruta))
        $profilePicture = $preruta . ltrim($profilePicture, '/');
    else $profilePicture = ltrim($profilePicture, '/');
}