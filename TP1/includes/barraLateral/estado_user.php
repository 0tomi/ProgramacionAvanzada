<?php
// Estado
$isLoggedIn = !empty($_SESSION['user_id']);

// Datos del perfil que se van a mostrar
$userName = $isLoggedIn
    ? $_SESSION['username']
    : 'Invitado'; 

// debug xq no se veian las fotos de perfil
/*echo "<pre>";
var_dump($_SESSION['user_profile_picture']);
echo "<pre>";*/

if ($isLoggedIn)
    $profilePicture = !empty($_SESSION['user_profile_picture']) ?
        $_SESSION['user_profile_picture']
        : '../imagenes/profilePictures/user.png';
else $profilePicture = '../imagenes/profilePictures/defaultProfilePicture.png';

// Asegurar que la ruta funcione desde subdirectorios
if (!preg_match('#^https?://#', $profilePicture) && $profilePicture[0] !== '/') {
    $profilePicture = $preruta . ltrim($profilePicture, '/');
}

// Referencias de los otros botones
$boton_perfil = $preruta.'perfil.php';
$boton_inicio = $preruta.'index.php';

// CTA (botón inferior)
if ($isLoggedIn) {
    $ctaHref = $preruta.'logout.php';
    $ctaText = 'Cerrar sesión';
} else {
    $ctaHref = $preruta.'login.php';
    $ctaText = 'Iniciar sesión';
}

// Sanitizar salidas ????????????????
$userNameSafe      = htmlspecialchars($userName ?? '', ENT_QUOTES, 'UTF-8');
$profilePictureSafe = htmlspecialchars($profilePicture ?? '', ENT_QUOTES, 'UTF-8');
$ctaHrefSafe       = htmlspecialchars($ctaHref ?? '#', ENT_QUOTES, 'UTF-8');
$ctaTextSafe       = htmlspecialchars($ctaText ?? '', ENT_QUOTES, 'UTF-8');
