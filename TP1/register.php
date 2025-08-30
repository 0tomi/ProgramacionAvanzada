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
                Go to home
            </a>
        </div>
    </div>

    
</nav>

<script src="js/validations.js"></script>
<main>
    <div class="flex min-h-full px-6 py-12 lg:px-8">
        <!-- Formulario lado izquierdo -->
        <div class="flex flex-col justify-center sm:mx-auto sm:w-full sm:max-w-sm w-full lg:w-1/2">
            <img class="mx-auto h-10 w-auto" src="https://tailwindcss.com/plus-assets/img/logos/mark.svg?color=indigo&shade=600" alt="Your Company" />
            <h2 class="mt-10 text-left text-2xl/9 font-bold tracking-tight text-gray-100">Register!!</h2>

            <div class="mt-10">
                <form class="space-y-6" action="procesoRegister.php" id="registerForm" method="POST">
                    <div>
                        <label for="username" class="block text-sm/6 font-medium text-gray-100">Username</label>
                        <div class="mt-2">
                            <input type="text" name="username" id="username" autocomplete="username" required class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6" />
                            <?php if (isset($errors['username'])) : ?>
                                <p class="mt-4 ml-5 font-semibold text-sm text-red-500"> <?= $errors['username'] ?> </p>
                            <?php endif ?>
                        </div>
                    </div>
                    <div>
                        <div class="flex items-center justify-between">
                            <label for="password" class="block text-sm/6 font-medium text-gray-100">Password</label>
                        </div>
                        <div class="mt-2">
                            <input type="password" name="password" id="password" autocomplete="current-password" required class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6" />
                        </div>
                        <?php if (isset($errors['password'])) : ?>
                            <p class="mt-4 ml-5 font-semibold text-sm text-red-500"> <?= $errors['password'] ?> </p>
                        <?php endif ?>
                    </div>
                    <div>
                        <button type="submit" class="flex w-full justify-center rounded-md bg-indigo-600 px-3 py-1.5 font-semibold text-white shadow-lg hover:bg-indigo-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 hover:scale-105 transition-transform">Register</button>
                    </div>
                    <div class="mt-8 flex justify-center">
                        <a href="login.php" class="rounded bg-indigo-600 px-5 py-2 text-white font-semibold shadow hover:bg-indigo-500  hover:scale-105 transition-transform">Go to Log In</a>
                    </div>
                </form>
            </div>
        </div>
        <div class="hidden lg:flex items-center justify-center w-1/2">
            <div id="carouselExampleAutoplaying" class="carousel slide w-full max-w-md" data-bs-ride="carousel">
                <div class="carousel-inner rounded-lg shadow-lg">
                    <div class="carousel-item active">
                        <img src="imagenes/images/1.png" class="d-block w-100 shadow-lg" alt="Imagen 1" style="height:300px; object-fit:cover;">
                    </div>
                    <div class="carousel-item">
                        <img src="imagenes/images/2.png" class="d-block w-100 shadow-lg" alt="Imagen 2" style="height:300px; object-fit:cover;">
                    </div>
                    <div class="carousel-item">
                        <img src="imagenes/images/3.png" class="d-block w-100 shadow-lg" alt="Imagen 3" style="height:300px; object-fit:cover;">
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<?php require("includes/footer.php") ?>