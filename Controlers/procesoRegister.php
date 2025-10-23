<?php
include "../Model/UserFactory.php";

$secret = '6LdELdMrAAAAACqktniyEYfKBsP9hGg9Wvs5Anua'; // No exponer en producción
$token  = $_POST['g-recaptcha-response'] ?? '';
$ip     = $_SERVER['REMOTE_ADDR'] ?? '';

if ($token === '') {
    header('Location: ../Views/LOGIN/_register.php?error=Completa+el+captcha');
    exit;
}

$verifyResponse = file_get_contents(
    'https://www.google.com/recaptcha/api/siteverify?secret='
    . urlencode($secret) . '&response=' . urlencode($token) . '&remoteip=' . urlencode($ip)
);

$decoded = json_decode($verifyResponse, true);
if (!is_array($decoded) || !($decoded['success'] ?? false)) {
    header('Location: ../Views/LOGIN/_register.php?error=Captcha+invalido');
    exit;
}

$username = trim($_POST['username']);
$password = trim($_POST['password']);

$userFactory = new UserFactory();

// Verificar si el usuario ya existe, y crearlo si no.
if (!$userFactory->registerUser($username, $password)){
    header("Location: ../Views/LOGIN/_register.php?error=$userFactory->error");
    exit;
}

// Exito
header("Location: ../Views/LOGIN/_login.php?success=Registro+exitoso");
exit;
