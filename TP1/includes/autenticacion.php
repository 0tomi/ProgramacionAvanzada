<?php
session_start();
// Estado
$isLoggedIn = !empty($_SESSION['user_id']);

// Datos del perfil que se van a mostrar
$userName = $isLoggedIn
    ? $_SESSION['username']
    : 'Invitado'; 

if ($isLoggedIn)
    $profilePicture = !empty($_SESSION['user_profile_picture']) ?
        $_SESSION['user_profile_picture']
        : 'imagenes/profilePictures/user.png';
else $profilePicture = 'imagenes/profilePictures/defaultProfilePicture.png';

// Asegurar que la ruta funcione desde subdirectorios
if (!preg_match('#^https?://#', $profilePicture) && $profilePicture[0] !== '/') {
    if (!empty($preruta))
        $profilePicture = $preruta . ltrim($profilePicture, '/');
    else $profilePicture = ltrim($profilePicture, '/');
}