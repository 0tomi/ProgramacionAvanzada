<?php require base_path("views/partials/head.php") ?>

    <nav class="bg-gray-800 drop-shadow-xl w-full h-24">
        <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-bold tracking-tight text-gray-200"> Hello, <?= $_SESSION['name'] ?? 'Guest' ?></h1>
        </div>
    </nav>

    <main>

        <div class="flex min-h-full flex-col justify-center px-6 py-12 lg:px-8">

        <div class="sm:mx-auto sm:w-full sm:max-w-sm">
            <img class="mx-auto h-10 w-auto" src="https://tailwindcss.com/plus-assets/img/logos/mark.svg?color=indigo&shade=600" alt="Your Company" />
            <h2 class="mt-10 text-center text-2xl/9 font-bold tracking-tight text-gray-100">Log In!!</h2>
        </div>

        <div class="mt-10 sm:mx-auto sm:w-full sm:max-w-sm">

            <form class="space-y-6" action="/session" method="POST">

                <div>
                    <label for="email" class="block text-sm/6 font-medium text-gray-100">Email address</label>

                    <div class="mt-2">

                    <input type="email" name="email" id="email" autocomplete="email" required class="block w-full rounded-md bg-white px-3 py-1.5 text-base text-gray-900 outline-1 -outline-offset-1 outline-gray-300 placeholder:text-gray-400 focus:outline-2 focus:-outline-offset-2 focus:outline-indigo-600 sm:text-sm/6" />
                    
                    <?php if (isset($errors['email'])) : ?>
                        <p class="mt-4 ml-5 font-semibold text-sm text-red-500"> <?= $errors['email'] ?> </p>
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
                    <button type="submit" class="flex w-full justify-center rounded-md bg-indigo-600 px-3 py-1.5 text-sm/6 font-semibold text-white shadow-xs hover:bg-indigo-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 hover:scale-105 transition-transform">Log In</button>
                </div>


                <div class="mt-8 flex justify-center"><a href="/register" class="rounded bg-indigo-600 px-5 py-2 text-white font-semibold shadow hover:bg-indigo-500  hover:scale-105 transition-transform">Ir a Registrarse</a></div>

            </form>

        </div>

        </div>

    </main>

<script src="/js/validations.js"></script>
<?php require base_path("views/partials/footer.php") ?>