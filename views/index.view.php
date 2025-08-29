<?php require("partials/head.php") ?>
<?php require("partials/nav.php") ?>
<?php require("partials/banner.php") ?>

    <main>
        <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
            <?php if($_SESSION['user'] ?? false) : ?>

                <p class="text-slate-300">Hello <?= $_SESSION['user']['email'] ?>. Welcome to the home page</p>

            <?php else: ?>

                <p class="text-slate-300">Hello Guest. Welcome to the home page</p>
                
            <?php endif; ?>
        </div>
    </main>

<?php require("partials/footer.php") ?>