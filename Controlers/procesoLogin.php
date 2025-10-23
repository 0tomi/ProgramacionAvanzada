<?php
include "../Model/UserFactory.php";

$secret  = '6LdELdMrAAAAACqktniyEYfKBsP9hGg9Wvs5Anua'; // NO PONERLA EN LA ENTREGA FINAL PORFAVOR SE LOS PIDO
$token   = $_POST['g-recaptcha-response'] ?? '';
$ip      = $_SERVER['REMOTE_ADDR'] ?? '';

/* 
if (!$token) { return // error si falla x alguna razon de key
  header('Location: ../LOGIN/_login.php?error=captcha');
  exit;
}
$verify = file_get_contents(// envia la peticion a Google reCAPTCHA para validar el token del usuario
  'https://www.google.com/recaptcha/api/siteverify?secret='
  . urlencode($secret) . '&response=' . urlencode($token) . '&remoteip=' . urlencode($ip)
);
$res = json_decode($verify, true);
//la respuesta devuelta por gogle es un JSON con success
if (!($res['success'] ?? false)) {//aca chequea ese JSON
  header('Location: ../LOGIN/_login.php?error=captcha');
  exit;
}
*/

/*TODO EL PROCESO ANTERIOR FUE PARA CARGAR EL CAPTCHA Y QUE VALIDE SI EL USUARIO LO COMPLETO, SI ES ASI SIGUE CON EL RESTO DE LA LOGICA DE PROCESO*/



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
