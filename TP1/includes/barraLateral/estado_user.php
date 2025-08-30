<?php
require_once __DIR__ . '/session_check.php';

// -----------------------
// LÓGICA DE ESTADO DE USUARIO
// -----------------------
// Supongamos que, al loguearse, guardás:
/// $_SESSION['user_id']
/// $_SESSION['user_name']
/// $_SESSION['user_profile_picture'] (ruta relativa, opcional)

// Estado
$isLoggedIn = !empty($_SESSION['user_id']);

// Datos del perfil que se van a mostrar
$userName = $isLoggedIn
    ? $_SESSION['user_name']
    : 'Invitado'; 

$profilePicture = $isLoggedIn && !empty($_SESSION['user_profile_picture'])
    ? $_SESSION['user_profile_picture']
    : 'imagenes/profilePictures/defaultProfilePicture.png';

// CTA (botón inferior)
if ($isLoggedIn) {
    $ctaHref   = 'logout.php';
    $ctaText   = 'Cerrar sesión';
    $ctaStyle  = 'btn btn-danger w-100 d-flex align-items-center justify-content-center';
    $ctaIcon   = 'bi-box-arrow-right';
} else {
    $ctaHref   = 'login.php';
    $ctaText   = 'Iniciar sesión';
    $ctaStyle  = 'btn w-100 d-flex align-items-center justify-content-center';
    $ctaIcon   = 'bi-box-arrow-in-right';
    $ctaInline = 'background-color:#8899ac; color:#fff;';
}

// Sanitizar salidas ???
$userNameSafe = htmlspecialchars($userName ?? '', ENT_QUOTES, 'UTF-8');
$profilePictureSafe = htmlspecialchars($profilePicture ?? '', ENT_QUOTES, 'UTF-8');
$ctaHrefSafe = htmlspecialchars($ctaHref ?? '#', ENT_QUOTES, 'UTF-8');
$ctaTextSafe = htmlspecialchars($ctaText ?? '', ENT_QUOTES, 'UTF-8');
$ctaIconSafe = htmlspecialchars($ctaIcon ?? 'bi-box-arrow-right', ENT_QUOTES, 'UTF-8');