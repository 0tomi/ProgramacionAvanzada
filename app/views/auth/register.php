<?php $source = 'Registro'; $require_boostrap = true; require dirname(__DIR__) . "/layout/header.php" ?>

<?php
// Capturar mensajes de error o éxito
$error   = isset($_GET['error']) ? $_GET['error'] : null;
$success = isset($_GET['success']) ? $_GET['success'] : null;
?>

<nav class="bg-[#141e27] drop-shadow-xl w-full h-24">
  <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8 flex items-center relative">
      
      <!-- Texto de saludo -->
      <div class="absolute mt-2 left-1/2 transform -translate-x-1/2">
          <h1 class="text-3xl font-bold tracking-tight text-[#ffffff]">Ritual</h1>
      </div>

      <!-- Botón ir a inicio -->
      <div class="ml-auto mt-2 hover:text-[#15202b] hover:scale-105 transition-transform">
          <a href="/index.php"
             class="rounded bg-[#8899ac] px-5 py-2 text-[#15202b] font-semibold shadow 
                    hover:bg-[#ffffff]">
              Volver
          </a>
      </div>
  </div>
</nav>


<!-- Grid padre -->
<main class="grid grid-cols-1 lg:grid-cols-2 bg-gradient-to-b from-[#192734] via-[#313e4b] to-[#15202b]">
    
    <!-- Columna izquierda: formulario -->
    <div class="flex flex-col justify-center items-center py-12 px-6 lg:px-8 mt-20 mb-24">
        <img class="mx-auto size-16 w-auto" src="/public/assets/images/profilePictures/Ritual.png" alt="Your Company" />
        <h2 class="mt-4 text-left text-2xl/9 font-bold tracking-tight text-[#ffffff]">Registrate en Ritual</h2>

        <!-- Mostrar mensajes -->
        <?php if ($error): ?>
            <p class="mt-4 p-3 rounded bg-red-500 text-white font-semibold text-center shadow-lg">
                <?= htmlspecialchars($error) ?>
            </p>
        <?php endif; ?>

        <?php if ($success): ?>
            <p class="mt-4 p-3 rounded bg-green-500 text-white font-semibold text-center shadow-lg">
                <?= htmlspecialchars($success) ?>
            </p>
        <?php endif; ?>

        <div class="w-full max-w-md bg-[#141e27] p-6 rounded-lg shadow-lg mt-8 hover:scale-x-95 transition-transform">
            <form class="space-y-6" action="/app/controllers/Auth/procesoRegister.php" id="registerForm" method="POST">
                <div>
                    <label for="username" class="block text-sm/6 font-medium text-[#ffffff]">Usuario</label>
                    <div class="mt-2">
                        <input type="text" name="username" id="username" autocomplete="username" required 
                            class="block w-full rounded-md bg-[#22303c] px-3 py-1.5 text-base text-[#ffffff] 
                                   outline-none placeholder:text-[#8899ac] 
                                   focus:ring-2 focus:ring-[#8899ac] sm:text-sm/6" />
                    </div>
                </div>

                <div>
                    <label for="password" class="block text-sm/6 font-medium text-[#ffffff]">Contraseña</label>
                    <div class="mt-2">
                        <input type="password" name="password" id="password" autocomplete="current-password" required 
                            class="block w-full rounded-md bg-[#22303c] px-3 py-1.5 text-base text-[#ffffff] 
                                   outline-none placeholder:text-[#8899ac] 
                                   focus:ring-2 focus:ring-[#8899ac] sm:text-sm/6" />
                    </div>
                </div>

                <div>
                    <button type="submit" 
                        class="flex w-full justify-center rounded-md bg-[#8899ac] px-3 py-1.5 
                               font-semibold text-[#15202b] shadow-lg hover:bg-[#ffffff] 
                               hover:text-[#15202b] hover:scale-105 transition-transform">
                        Registrarse
                    </button>
                </div>

                <div class="mt-8 flex justify-center">
                    <a href="/LOGIN/_login.php"
                       class="rounded bg-[#8899ac] px-5 py-2 text-[#15202b] font-semibold shadow 
                              hover:bg-[#ffffff] hover:text-[#15202b] hover:scale-105 transition-transform">
                        Iniciar Sesión
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Columna derecha: carrusel -->
    <div class="hidden lg:flex items-center justify-center mt-12">
        <div id="carouselExampleAutoplaying" class="carousel slide w-full max-w-md" data-bs-ride="carousel">
            <div class="carousel-inner rounded-lg shadow-lg">
                <div class="carousel-item active">
                    <img src="/public/assets/images/images/1.png" class="d-block w-100 shadow-lg" alt="Imagen 1" style="height:300px; object-fit:cover;">
                </div>
                <div class="carousel-item">
                    <img src="/public/assets/images/images/2.png" class="d-block w-100 shadow-lg" alt="Imagen 2" style="height:300px; object-fit:cover;">
                </div>
                <div class="carousel-item">
                    <img src="/public/assets/images/images/3.png" class="d-block w-100 shadow-lg" alt="Imagen 3" style="height:300px; object-fit:cover;">
                </div>
            </div>
        </div>
    </div>
</main>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<?php require dirname(__DIR__) . "/layout/_footer.php" ?>
