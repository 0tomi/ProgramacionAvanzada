<?php require("includes/headertw.php") ?>


<nav class="bg-gray-800 drop-shadow-xl w-full h-24">
    <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8 flex justify-between items-center">
        <!-- Texto de saludo -->
        <h1 class="text-3xl font-bold tracking-tight text-gray-200"> Hello, Guest</h1>

        <!-- Botón Ir a Inicio -->
        <div class="flex justify-end">
            <a href="index.php" 
               class="rounded bg-indigo-600 px-5 py-2 text-white font-semibold shadow 
                      hover:bg-indigo-500 hover:scale-105 transition-transform">
                Go to Home
            </a>
        </div>
    </div>
</nav>

<script src="js/validations.js"></script>
<main>
    <div class="flex min-h-full flex-col justify-center px-6 py-12 lg:px-8">

        <div class="sm:mx-auto sm:w-full sm:max-w-sm">
            <img class="mx-auto h-10 w-auto" src="https://tailwindcss.com/plus-assets/img/logos/mark.svg?color=indigo&shade=600" alt="Your Company" />
            <h2 class="mt-10 text-center text-2xl/9 font-bold tracking-tight text-gray-100">Log In!!</h2>
        </div>

        <div class="mt-10 sm:mx-auto sm:w-full sm:max-w-sm">
            <form class="space-y-6" action="procesoLogin.php" method="POST" id="loginForm">

                <div>
                    <label for="username" class="block text-sm/6 font-medium text-gray-100">Username</label>
                    <div class="mt-2">
                        <input type="text" name="username" id="username" autocomplete="username" required 
                            class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 
                                   outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 
                                   focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6" />
                        <span id="usernameError" class="text-red-500 text-sm"></span>
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between">
                        <label for="password" class="block text-sm/6 font-medium text-gray-100">Password</label>
                    </div>
                    <div class="mt-2">
                        <input type="password" name="password" id="password" autocomplete="current-password" required 
                            class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 
                                   outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 
                                   focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6" />
                        <span id="passwordError" class="text-red-500 text-sm"></span>
                    </div>
                </div>

                <div>
                    <button type="submit" 
                        class="flex w-full justify-center rounded-md bg-indigo-600 px-3 py-1.5 
                               font-semibold text-white shadow-xs hover:bg-indigo-500 
                               focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 
                               hover:scale-105 transition-transform">
                        Log In
                    </button>
                </div>

                <div class="mt-8 flex justify-center">
                    <a href="register.php" 
                       class="rounded bg-indigo-600 px-5 py-2 text-white font-semibold shadow 
                              hover:bg-indigo-500 hover:scale-105 transition-transform">
                        Go to Register
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

<?php require("includes/footer.php") ?>
