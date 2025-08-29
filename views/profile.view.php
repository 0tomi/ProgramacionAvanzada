<?php require base_path('views/partials/head.php'); ?>
<?php require base_path('views/partials/nav.php'); ?>
<?php require base_path('views/partials/banner.php'); ?>
<?php require base_path('views/partials/dashboard.php'); ?>

<main class="flex flex-col items-center justify-center min-h-screen bg-slate-50">
    <div class="w-full max-w-xl bg-white rounded-xl shadow-lg p-8 mt-8">
        <h2 class="text-2xl font-bold mb-6 text-indigo-700">Editar Perfil</h2>
    <form method="POST" action="/profile" enctype="multipart/form-data" class="space-y-6">
            <div class="flex flex-col items-center">
                <img src="<?= $profile['image'] ? '/assets/profile/' . htmlspecialchars($profile['image']) : '/assets/profile.png' ?>" alt="Profile" class="w-32 h-32 rounded-full object-cover mb-4 border border-indigo-200">
                <input type="file" name="image" accept="image/*" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
            </div>
            <div>
                <label for="description" class="block text-base font-medium text-gray-700 mb-2">Descripción</label>
                <textarea id="description" name="description" rows="3" class="block w-full rounded-md border border-gray-300 px-3 py-2 text-base text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-600" placeholder="Agrega una descripción personal..."><?= htmlspecialchars($profile['description'] ?? '') ?></textarea>
            </div>
            <div class="flex justify-end gap-4">
                <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-lg font-semibold shadow hover:bg-indigo-500 transition">Guardar Cambios</button>
            </div>
        </form>
    </div>
</main>

<?php require base_path('views/partials/footer.php'); ?>
