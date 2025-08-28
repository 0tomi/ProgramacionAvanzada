<?php require base_path("views/partials/head.php") ?>
<?php require base_path("views/partials/nav.php") ?>
<?php require base_path("views/partials/banner.php") ?>

    <main>
        <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
            <ul>
                <?php foreach ($notes as $note) : ?>
                
                <li> 
                    <a href="/note?id=<?= $note['id'] ?>" class="text-blue-900 hover:underline">
                        <?= htmlspecialchars($note['body'])?>
                    </a> 
                </li>
            
            
                <?php endforeach; ?>
            </ul>

            <p class="mt-10 bg-white rounded-xl shadow-lg hover:scale-105 transition-transform items-center justify-center duration-5 w-24 h-10 flex">
                <a href="/notes/create" class="text-blue-900">Create Note</a>
            </p>
        </div>
    </main>

<?php require base_path("views/partials/footer.php") ?>