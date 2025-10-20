<?php // Para visualizar un post concreto, invocar a esta pagina con ?id = idPost ?>
<?php
$source = 'Post';
$require_boostrap = true;
$preruta = '../../';
require_once __DIR__ . '/../header.php';
?>

<body>
  
  <?php
    $preruta = '../../';
    require_once __DIR__ . '/../../Controlers/autenticacion.php';
    ?>
  <main class="container">
    <div class="row justify-content-center">
      <div class="col-12 col-md-8">
        <section id="feed"></section> <!-- aquí se inyecta el post individual -->
      </div>
    </div>
  </main>

  <a href="../../Inicio/inicio.php" class="back-button-fixed">← Volver al inicio</a>
  <script src="app.js"></script>

</body>
</html>
