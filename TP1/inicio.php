<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}
?>

<?php require("includes/header.php") ?>
<?php require("includes/nav.php") ?>
<?php require("includes/dashboard.php") ?>

    <main>
        <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
            <p class="text-slate-300">
            Hello <?= htmlspecialchars($_SESSION['username']) ?>. Welcome to the home page
        </p>
        </div>
    </main>

<?php require("includes/footer.php") ?>