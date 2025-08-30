<?php
session_start();

// Si recibe método DELETE (logout)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['_method'] ?? '') === 'DELETE') {
    session_destroy();
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en" class="h-full bg-gray-100">
<head>
  <meta charset="UTF-8">
  <script src="https://cdn.tailwindcss.com"></script>
  <title>Inicio</title>
</head>
<body class="min-h-screen bg-gradient-to-b from-slate-800 via-slate-700 to-slate-600 flex flex-col">

  <!-- NAV -->
  <nav class="bg-gray-800 shadow-lg">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
      <div class="flex h-18 items-center justify-between">
        
        <!-- Logo -->
        <div class="flex items-center">
          <img class="h-8 w-auto" src="https://tailwindcss.com/plus-assets/img/logos/mark.svg?color=indigo&shade=500" alt="Logo">
        </div>

        <!-- Texto usuario -->
        <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8 flex justify-between items-center">
          <!-- Texto de saludo -->
          <h1 class="text-3xl font-bold tracking-tight text-gray-200"> Hello, Guest</h1>

        </div>
      </div>
    </div>
  </nav>

  <?php include 'includes/barraLateral/barraLateral.php'; ?>

  <!-- CONTENIDO CENTRADO -->
  <main class="flex-grow flex items-center justify-center">
    <div class="bg-gray-900 rounded-2xl shadow-2xl p-10 text-center w-full max-w-md hover:scale-x-95 transition-transform mb-24">
      
      <h1 class="text-2xl font-bold text-white mb-6">
        Bienvenido.
      </h1>
      
      <?php if ($_SESSION['username'] ?? false): ?>
        <!-- Botón logout -->
        <form method="POST" action="index.php">
          <input type="hidden" name="_method" value="DELETE">
          <button class="px-6 py-3 w-full rounded-xl bg-red-500 text-white font-semibold shadow-md hover:bg-red-600 hover:scale-105 transition">
            Cerrar Sesión
          </button>
        </form>
      <?php else: ?>
        <!-- Botones login/register -->
        <div class="space-y-4">
          <a href="login.php" class="block px-6 py-3 w-full rounded-xl bg-slate-300 text-gray-900 font-semibold shadow-md hover:bg-slate-400 hover:scale-105 transition">
            Iniciar Sesión
          </a>
          <a href="register.php" class="block px-6 py-3 w-full rounded-xl bg-indigo-500 text-white font-semibold shadow-md hover:bg-indigo-600 hover:scale-105 transition">
            Registrarse
          </a>
          <a href="Inicio/inicio.php" class="block px-6 py-3 w-full rounded-xl bg-slate-300 text-gray-900 font-semibold shadow-md hover:bg-slate-400 hover:scale-105 transition">
            Continuar como Invitado
          </a>
        </div>
      <?php endif; ?>

    </div>
  </main>
</body>

<?php include('includes/footer.php'); ?>
</html>
