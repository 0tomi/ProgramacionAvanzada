<?php
include "../Model/UserFactory.php";
/*
$secret = '6LdELdMrAAAAACqktniyEYfKBsP9hGg9Wvs5Anua'; // No exponer en producción
$token  = $_POST['g-recaptcha-response'] ?? '';
$ip     = $_SERVER['REMOTE_ADDR'] ?? '';

if ($token === '') {
  header('Location: ../Views/LOGIN/_login.php?error=Completa+el+Catpcha');
  exit;
}

$verify = file_get_contents(
  'https://www.google.com/recaptcha/api/siteverify?secret='
  . urlencode($secret) . '&response=' . urlencode($token) . '&remoteip=' . urlencode($ip)
);

$res = json_decode($verify, true);
if (!is_array($res) || !($res['success'] ?? false)) {
  header('Location: ../Views/LOGIN/_login.php?error=Captcha+invalido');
  exit;
}
*/
$username = trim($_POST['username']);
$password = trim($_POST['password']);

$userFactory = new UserFactory($username, $password);
$usuario = $userFactory->getUser();

if ($usuario === null){
  header("Location: ../Views/LOGIN/_login.php?error=$userFactory->error");
  exit;
}

session_start();
$_SESSION['user'] = $usuario;
header("Location: ../Inicio/inicio.php");

exit;
