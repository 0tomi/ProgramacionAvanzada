<?php // /Views/POSTS/index.php ?>
<?php
$source = 'Post';
$require_boostrap = false;
$preruta = '../../';
require_once __DIR__ . '/../header.php';
?>

<body>
  
  <?php
    $preruta = '../../';
    require_once __DIR__ . '/../../Controlers/autenticacion.php';
    require_once __DIR__ . '/../barraLateral/barraLateral.php';
    ?>
  <main class="container">
    <section id="feed"></section> <!-- aquí se inyecta el post individual -->
  </main>

  <a href="../../Inicio/inicio.php" class="back-button-fixed">← Volver</a>
  <script src="app.js"></script>

</body>
</html>
