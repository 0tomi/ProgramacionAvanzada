<?php require("partials/head.php") ?>
<?php require("partials/nav.php") ?>
<?php require("partials/banner.php") ?>

    <main>
        <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
            <h1 class="text-2xl font-bold mb-6">Foro de Notas</h1>
            <ul>
                <?php if (isset($notas) && is_array($notas)) : ?>
                    <?php foreach ($notas as $nota) : ?>
                        <li class="mb-6 p-4 bg-white rounded shadow">
                            <div class="font-semibold text-indigo-700">@<?= htmlspecialchars($nota['username'] ?? '') ?></div>
                            <div class="text-gray-700">Nombre: <?= htmlspecialchars($nota['first_name'] ?? '') ?> <?= htmlspecialchars($nota['last_name'] ?? '') ?></div>
                            <div class="text-gray-500 italic">Sobre: <?= htmlspecialchars($nota['about'] ?? '') ?></div>
                            <div class="mt-2">Nota: <?= htmlspecialchars($nota['body'] ?? '') ?></div>
                        </li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <li>No hay notas para mostrar.</li>
                <?php endif; ?>
            </ul>
            <div class="mt-8">
                <a href="/notes/create" class="rounded bg-indigo-600 px-4 py-2 text-white hover:bg-indigo-500">Crear nueva nota</a>
            </div>
        </div>
    </main>

<?php require("partials/footer.php") ?>