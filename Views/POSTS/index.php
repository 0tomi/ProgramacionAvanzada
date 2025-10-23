<?php
// Views/POSTS/index.php

$source = 'Post';
$require_boostrap = true;
$preruta = '../../';

require_once __DIR__ . '/../../Controlers/autenticacion.php';
require_once __DIR__ . '/../header.php';
?>


<link rel="stylesheet" href="<?= $preruta ?>Inicio/inicio.css">

<main class="shell">
  <div class="feed-col">
    <header class="feed-head">
      <h1>Publicación</h1>
      <span class="sub">Vista detallada</span>
    </header>

    <section id="feed"></section> 
  </div>
</main>

<a href="<?= $preruta ?>Inicio/inicio.php" class="back-button-fixed btn btn-primary mt-4 ms-4">
  ← Volver al inicio
</a>

<script src="app.js"></script>

<?php require_once __DIR__ . '/../_footer.php'; ?>
