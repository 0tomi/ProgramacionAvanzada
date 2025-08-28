<?php require base_path("views/partials/head.php") ?>
<?php require base_path("views/partials/nav.php") ?>
<?php require base_path("views/partials/banner.php") ?>

    <main>
        <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
            <div class="bg-white rounded-xl shadow-lg p-8 flex flex-col items-center justify-center">
                <h2 class="text-xl font-bold text-indigo-700 mb-4">Sección de creación de notas</h2>
                <p class="mb-6 text-gray-600">Aquí podrás crear nuevas notas. Serás redirigido a un formulario para ingresar la información.</p>
                <a href="/notes/create" class="rounded bg-indigo-600 px-5 py-2 text-white font-semibold shadow hover:bg-indigo-500 transition">Crear Nota</a>
            </div>
        </div>
    </main>

<?php require base_path("views/partials/footer.php") ?>