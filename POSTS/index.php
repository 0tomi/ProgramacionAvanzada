<?php
declare(strict_types=1);

use Posts\App\PostPageController;

$source = 'Post';
$require_boostrap = false;
$preruta = '../';
require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/app.php';
?>

<body>
  <?php
    $preruta = '../';
    require_once __DIR__ . '/../includes/autenticacion.php';

    $controller = new PostPageController();
    $result = $controller->handle($_SESSION);
    $post = $result['post'];
    $errors = $result['errors'];
    $flashMessage = $result['flash'];

    require_once __DIR__ . '/../includes/barraLateral/barraLateral.php';
  ?>

  <main class="container">
    <?php if (!empty($flashMessage)): ?>
      <div class="notice"><?= htmlspecialchars($flashMessage, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <?php foreach ($errors as $error): ?>
      <div class="error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endforeach; ?>

    <?php if ($post !== null): ?>
      <section id="feed">
        <?= $controller->render($post) ?>
      </section>
    <?php endif; ?>
  </main>

  <a href="../Inicio/inicio.php"
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

  <script>
    document.addEventListener('click', (event) => {
      const toggle = event.target.closest('[data-reply-toggle]');
      if (!toggle) {
        return;
      }
      const bubble = toggle.closest('.c-bubble');
      if (!bubble) {
        return;
      }
      const form = bubble.querySelector('.c-reply-form');
      if (form) {
        form.classList.toggle('hidden');
      }
    });
  </script>
</body>
</html>
