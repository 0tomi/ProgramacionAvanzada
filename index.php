<?php
session_start();
if (!empty($_SESSION['user'])) {
  header("Location: Inicio/inicio.php");
  exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>RITUAL</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700;800;900&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="./Views/CSS/index.css">
</head>
<body>
  <main>
    <h1 class="brand">RITUAL</h1>
    <p class="tag">La red social de sistemas</p>
    <p class="sub">Encontrá tu mundo, viví tu ritual. El momento es tuyo.</p>

    <div class="buttons">
      <a href="Views/LOGIN/_login.php" class="btn btn-primary">Iniciar sesión</a>
      <a href="Views/LOGIN/_register.php" class="btn btn-light">Registrarse</a>
      <!-- <a href="Inicio/inicio.php" class="btn btn-dark">Continuar como invitado</a> --> <!-- Para dsps cuando tengamos el invitado -->
    </div>
  </main>

  <?php require("Views/_footer.php"); ?>

</body>
</html>
