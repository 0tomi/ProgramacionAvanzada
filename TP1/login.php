<?php $source = 'Login'; $require_boostrap = true; require("includes/header.php") ?>

<nav class="bg-[#141e27] drop-shadow-xl w-full h-24">
  <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8 flex items-center relative">
      
      <!-- Texto de saludo -->
      <div class="absolute mt-2 left-1/2 transform -translate-x-1/2">
          <h1 class="text-3xl font-bold tracking-tight text-[#ffffff]">Ritual</h1>
      </div>

      <!-- Botón ir a inicio -->
      <div class="ml-auto mt-2 hover:text-[#15202b] hover:scale-105 transition-transform">
          <a href="index.php" 
             class="rounded hover:bg-[#ffffff] bg-[#8899ac] px-5 py-2 text-[#15202b] font-semibold shadow">
              Volver
          </a>
      </div>
  </div>
</nav>

<main class="flex flex-grow items-center justify-center bg-gradient-to-b from-[#192734] via-[#313e4b] to-[#15202b]">
    <div class="flex flex-col justify-center text-center w-full max-w-md px-6 py-12 lg:px-8 mb-32 mt-20">

        <div class="sm:mx-auto sm:w-full sm:max-w-sm">
            <img class="mx-auto size-16 w-auto" src="imagenes/profilePictures/Ritual.png" alt="Your Company" />
            <h2 class="mt-4 text-center text-2xl/9 font-bold tracking-tight text-[#ffffff]">Inicia Sesión en Ritual</h2>
        </div>

        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-sm bg-[#141e27] p-6 rounded-lg shadow-lg hover:scale-x-95 transition-transform">

            <?php if (isset($_GET['error'])): ?>
                <div class="mb-4 rounded bg-red-500 px-4 py-2 text-white text-center font-semibold">
                    <?php echo htmlspecialchars($_GET['error']); ?>
                </div>
            <?php endif; ?>

            <form class="space-y-6" action="includes/procesoLogin.php" method="POST" id="loginForm">

                <div>
                    <div class="flex items-center justify-between">
                        <label for="username" class="block text-sm/6 font-medium text-[#ffffff]">Usuario</label>
                    </div>
                    <div class="mt-2">
                        <input type="text" name="username" id="username" autocomplete="username" required 
                            class="block w-full rounded-md bg-[#22303c] px-3 py-1.5 text-base text-[#ffffff] 
                                   outline-none placeholder:text-[#8899ac] 
                                   focus:ring-2 focus:ring-[#8899ac] sm:text-sm/6" />
                        <span id="usernameError" class="text-red-500 text-sm"></span>
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between">
                        <label for="password" class="block text-sm/6 font-medium text-[#ffffff]">Contraseña</label>
                    </div>
                    <div class="mt-2">
                        <input type="password" name="password" id="password" autocomplete="current-password" required 
                            class="block w-full rounded-md bg-[#22303c] px-3 py-1.5 text-base text-[#ffffff] 
                                   outline-none placeholder:text-[#8899ac] 
                                   focus:ring-2 focus:ring-[#8899ac] sm:text-sm/6" />
                        <span id="passwordError" class="text-red-500 text-sm"></span>
                    </div>
                </div>

                <div>
                    <button type="submit" 
                        class="flex w-full justify-center rounded-md bg-[#8899ac] px-3 py-1.5 
                               font-semibold text-[#15202b] shadow-xs hover:bg-[#ffffff] 
                               hover:text-[#15202b] hover:scale-105 transition-transform">
                        Iniciar Sesión
                    </button>
                </div>

                <div class="mt-8 flex justify-center">
                    <a href="register.php" 
                       class="rounded bg-[#8899ac] px-5 py-2 text-[#15202b] font-semibold shadow 
                              hover:bg-[#ffffff] hover:text-[#15202b] hover:scale-105 transition-transform">
                        Registrarse
                    </a>
                </div>
            </form>
        </div>
    </div>
</main>

<script>
document.getElementById('loginForm').addEventListener('submit', function(e) {
    let valid = true;
    const username = document.getElementById('username').value.trim();
    const password = document.getElementById('password').value;
    document.getElementById('usernameError').textContent = '';
    document.getElementById('passwordError').textContent = '';
    if (username.length < 3) {
        document.getElementById('usernameError').textContent = 'El nombre de usuario debe tener al menos 3 caracteres.';
        valid = false;
    }
    if (password.length < 6) {
        document.getElementById('passwordError').textContent = 'La contraseña debe tener al menos 6 caracteres.';
        valid = false;
    }
    if (!valid) {
        e.preventDefault();
    }
});
</script>


<?php require("includes/_footer.php") ?>
