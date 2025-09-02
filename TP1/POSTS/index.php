<?php // /POSTS/index.php ?>
<?php $source = 'Post'; $require_boostrap = false; $preruta = '../'; 
  require_once __DIR__.'/../includes/header.php'; 
?>

<body>
  <?php $preruta = '../'; 
    require_once __DIR__ . '/../includes/autentificacion.php'; 
    require_once __DIR__ . '/../includes/barraLateral/barraLateral.php';
    ?>
  <main class="container">
    <section id="feed"></section> <!-- aquí se inyecta el post individual -->
  </main>
  <script src="app.js"></script>
</body>
</html>