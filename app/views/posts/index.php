<?php // /POSTS/index.php ?>
<?php $source = 'Post'; $require_boostrap = false; $preruta = '../';
  require_once dirname(__DIR__) . '/layout/header.php';
?>

<body>
  
  <?php $preruta = '../';
    require_once dirname(__DIR__, 2) . '/controllers/Auth/autenticacion.php';
    require_once dirname(__DIR__) . '/layout/barraLateral/barraLateral.php';
    ?>
  <main class="container">
    <section id="feed"></section> <!-- aquí se inyecta el post individual -->
  </main>

  <a href="/Inicio/inicio.php"
   style="
     position:fixed;
     top:12px;
     left:max(12px, calc(35% - 380px));
     z-index:2147483647;

     padding:8px 12px;
     border:1px solid #1b2431;
     border-radius:999px;
     background:#0f131a;
     color:#e8edf5;
     font-weight:700;
     text-decoration:none;
     box-shadow:0 8px 24px rgba(0,0,0,.25);
   ">
  ← Volver
</a>
  <script src="/public/assets/js/posts/app.js"></script>

</body>
</html>