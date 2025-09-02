<?php
session_start();

// Si recibe método DELETE (logout)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['_method'] ?? '') === 'DELETE') {
    session_destroy();
    header("Location: index.php");
    exit;
}
if (!empty($_SESSION['username'])) { //si esta logeado que mande al inicio!!!!!!
    header("Location: Inicio/inicio.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en" class="h-full bg-[#15202b]">
<head>
  <meta charset="UTF-8">
  <script src="https://cdn.tailwindcss.com"></script>
  <title>Inicio</title>
</head>
<body class="min-h-screen bg-[#15202b] flex flex-col">

  <div class="min-h-full">
      <!-- NAV -->
    <nav class="bg-[#192734] drop-shadow-xl shadow-lg">
      <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex h-18 items-center justify-between">
          
          <!-- Texto usuario -->
          <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8 flex justify-between items-center">
            <img src="imagenes/profilePictures/Ritual.png" class="flex absolute size-12 w-12 left-24">
            <h1 class="text-3xl mb-2 font-bold tracking-tight text-[#ffffff]"> Ritual</h1>
          </div>
        </div>
      </div>
    </nav>

    <?php $preruta =""; include 'includes/barraLateral/barraLateral.php'; ?>

    <!-- CONTENIDO CENTRADO -->
    <main class="flex-grow flex items-center justify-center bg-gradient-to-b from-[#192734] via-[#22303c] to-[#15202b] ">
      <div class="bg-[#192734] rounded-2xl shadow-2xl p-10 text-center w-full max-w-md hover:scale-x-95 transition-transform mb-48 mt-48">
        
        <h1 class="text-2xl font-bold text-[#ffffff] mb-4">
          Bienvenido a Ritual
        </h1>
        <p class="text-[#ffffff] mb-8 text-">
          La red social de Sistemas.
        </p>
        
        <?php if ($_SESSION['username'] ?? false): ?>
          <!-- Botón logout -->
          <form method="POST" action="index.php">
            <input type="hidden" name="_method" value="DELETE">
            <button class="px-6 py-3 w-full rounded-xl bg-[#22303c] text-[#ffffff] font-semibold shadow-md hover:bg-[#8899ac] hover:text-[#15202b] hover:scale-105 transition">
              Cerrar Sesión
            </button>
          </form>
        <?php else: ?>
          <!-- Botones login/register -->
          <div class="space-y-4">
            <a href="login.php" class="block px-6 py-3 w-full rounded-xl bg-[#22303c] text-[#ffffff] font-semibold shadow-md hover:bg-[#8899ac] hover:text-[#15202b] hover:scale-105 transition">
              Iniciar Sesión
            </a>
            <a href="register.php" class="block px-6 py-3 w-full rounded-xl bg-[#8899ac] text-[#15202b] font-semibold shadow-md hover:bg-[#ffffff] hover:text-[#15202b] hover:scale-105 transition">
              Registrarse
            </a>
            <a href="Inicio/inicio.php" class="block px-6 py-3 w-full rounded-xl bg-[#22303c] text-[#ffffff] font-semibold shadow-md hover:bg-[#8899ac] hover:text-[#15202b] hover:scale-105 transition">
              Continuar como Invitado
            </a>
          </div>
        <?php endif; ?>

      </div>
    </main>
  </div>
</body>

<?php include('includes/footer.php'); ?>
</html>
