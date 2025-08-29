<?php require base_path("views/partials/head.php") ?>
<?php require base_path("views/partials/nav.php") ?>
<?php require base_path("views/partials/banner.php") ?>
<?php require base_path("views/partials/dashboard.php") ?>

    <main>
        <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8 flex">
         
            <p><?= htmlspecialchars($note['body'])?></p>

        </div>
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8 flex">
            <button class="bg-white rounded-xl shadow-lg hover:scale-105 transition-transform items-center justify-center duration-5 w-20 h-10 flex mr-6 mt-6">
                <p>
                    <a href="/notes" class="text-blue-800 ">Go back</a>
                </p>  
            </button>

            <button class="bg-white rounded-xl shadow-lg hover:scale-105 transition-transform items-center justify-center duration-5 w-20 h-10 flex mr-6 mt-6">
                <a href="/note/edit?id=<?= $note['id'] ?>">
                    Edit
                </a>
            </button>
        </div>
    </main>

<?php require base_path("views/partials/footer.php") ?>