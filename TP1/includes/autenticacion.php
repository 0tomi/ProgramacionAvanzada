<?php
require_once __DIR__ . '/Usuario.php';
session_start();
// Estado
$isLoggedIn = !empty($_SESSION['user']);

// Datos del perfil que se van a mostrar
$userName = $isLoggedIn
    ? $_SESSION['user']->getNombre()
    : 'Invitado'; 

if ($isLoggedIn)
    $profilePicture = !empty($_SESSION['user']->getProfilePhoto()) ?
        $_SESSION['user_profile_picture']
        : 'imagenes/profilePictures/user.png';
else $profilePicture = 'imagenes/profilePictures/defaultProfilePicture.png';

// Asegurar que la ruta funcione desde subdirectorios
if (!preg_match('#^https?://#', $profilePicture) && $profilePicture[0] !== '/') {
    if (!empty($preruta))
        $profilePicture = $preruta . ltrim($profilePicture, '/');
    else $profilePicture = ltrim($profilePicture, '/');
}