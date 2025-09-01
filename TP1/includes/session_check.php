<?php
// Arranque de sesión seguro (llamarlo antes de cualquier salida)
if (session_status() === PHP_SESSION_NONE) {
    // Cookies seguras (ajusta 'secure' si usas HTTPS)
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
        'secure'   => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
    ]);
    session_start();
}
