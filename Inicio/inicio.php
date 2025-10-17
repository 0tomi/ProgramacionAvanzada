<?php
declare(strict_types=1);

use Posts\Lib\PostService;

$preruta ="../";
require_once __DIR__ . '/../includes/autenticacion.php';
require_once __DIR__ . '/../includes/Usuario.php';
require_once __DIR__ . '/../POSTS/lib/PostStorage.php';
require_once __DIR__ . '/../POSTS/lib/PostService.php';

$isAuth = $isLoggedIn;

// Redirigir si no esta logueado el usuario o no existe la sesion
if (!$isAuth) {
    header('Location: ../LOGIN/_login.php');
    exit;
}

$guard  = $isAuth ? '' : 'disabled';

$lockedAttr = $isAuth ? '' : 'data-locked="1"'; // bandera para bloquear botones en modo invitado

$flash = null;
if (isset($_SESSION['user']) && $_SESSION['user'] instanceof User) { 
    $u = $_SESSION['user'];
    $flash = $u->getFlash(); 
    $u->setFlash(null); 
}
// === FEED: obtener posts usando el servicio compartido ===
$posts = [];
$feedError = null;
try {
  $service = new PostService();
  $posts = $service->listPosts($_SESSION);
} catch (\Throwable $e) {
  $feedError = $e->getMessage();
}
?>

<?php $require_boostrap = false; $source = 'Inicio'; require_once __DIR__ . '/../includes/header.php'; ?>

<body>
  <?php include __DIR__ . '/headerInicio.php'; ?>
  <?php require('../includes/barraLateral/barraLateral.php'); ?>
  <?php if (!empty($flash)): ?>
  <div id="flash"
       data-type="<?= htmlspecialchars($flash['type'], ENT_QUOTES) ?>"
       data-msg="<?= htmlspecialchars($flash['msg'], ENT_QUOTES) ?>">
  </div>

  <div id="toast" class="toast" data-msg="<?= htmlspecialchars($flash['msg'], ENT_QUOTES, 'UTF-8') ?>">
    <span class="toast__icon"></span>
    <span class="toast__msg"></span>
  </div>
<?php endif; ?>


  <div class="shell">
    <section class="feed-col" role="feed" aria-label="Inicio">
      <header class="feed-head">
        <h1>Inicio</h1>
        <span class="sub">Posteos más recientes</span>
      </header>

      <!-- Composer -->
      <div class="composer" <?=$lockedAttr?> aria-label="Publicar">
        <img class="avatar" src="<?= htmlspecialchars($profilePicture) ?>" alt="Tu avatar">

        <!-- IMPORTANTE: id, name="text", name="image" -->
        <form id="createPostForm" class="compose" action="javascript:void(0)" method="post" enctype="multipart/form-data" novalidate>
          <textarea name="text" placeholder="<?= $isAuth ? '¿Qué está pasando?' : 'Inicia sesión para postear' ?>" maxlength="280" required <?= $guard ?>></textarea>
          <div class="row">
            <input type="file" id="imgUp" name="image" accept="image/*" style="display:none" <?= $guard ?>>
            <label for="imgUp" class="btn ghost" aria-disabled="<?= $isAuth ? 'false' : 'true' ?>" <?= $guard ? 'tabindex="-1"' : '' ?>>Imagen</label>
            <button class="btn primary" type="submit" <?= $guard ?>>Publicar</button>
          </div>
        </form>
      </div>

      <!-- FEED desde /JSON/POST.json -->
      <div id="feed">
        <?php if ($feedError !== null): ?>
          <p class="error"><?= htmlspecialchars($feedError, ENT_QUOTES, 'UTF-8') ?></p>
        <?php elseif (empty($posts)): ?>
          <p class="muted">No hay posts todavía.</p>
        <?php else: ?>
          <?php foreach ($posts as $p):
            // defensivo + formato de campos
            $id      = (string)($p['id'] ?? '');
            $idEsc   = htmlspecialchars($id);
            $name    = htmlspecialchars($p['author']['name'] ?? 'Anónimo');
            $handle  = (string)($p['author']['handle'] ?? 'anon'); // solo para la inicial del avatar
            $avatarL = strtoupper(substr($handle ?: 'U', 0, 1));

            $tsRaw   = $p['created_at'] ?? '';
            $tsHuman = $tsRaw ? date('d/m/Y H:i', strtotime($tsRaw)) : '';
            $text    = htmlspecialchars($p['text'] ?? '');
            $likes   = (int)($p['counts']['likes'] ?? 0);
            $media   = trim((string)($p['media_url'] ?? ''));
            $liked   = !empty($p['viewer']['liked']);
            $likeClass = 'chip like' . ($liked ? ' liked' : '');
            $buttonDisabled = !empty($p['viewer']['authenticated']) ? '' : 'disabled title="Inicia sesión para likear"';
          ?>
            <article class="post" data-id="<?= $idEsc ?>">
              <!-- Capa clickeable que abre el detalle del post -->
              <a class="post-overlay"
                 href="../POSTS/?id=<?= urlencode($id) ?>"
                 aria-label="Ver post"></a>

              <header class="post-header">
                <div class="avatar"><?= htmlspecialchars($avatarL) ?></div>
                <div class="meta">
                  <div class="name"><?= $name ?></div>
                  <div class="subline">
                    <time datetime="<?= htmlspecialchars($tsRaw) ?>"><?= $tsHuman ?></time>
                  </div>
                </div>
              </header>

              <p class="text"><?= $text ?></p>

              <?php if ($media !== ''): ?>
                <figure class="media">
                  <img src="<?= htmlspecialchars($media) ?>" alt="Imagen del post">
                </figure>
              <?php endif; ?>

              <div class="actions">
                <button type="button"
                  class="<?= $likeClass ?>"
                  data-id="<?= $idEsc ?>"
                  <?= $buttonDisabled ?>>
                  ♥ <span class="count"><?= $likes ?></span>
                </button>
              </div>
            </article>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>

      <div class="load-more">
        <button class="btn" disabled>Cargar más</button>
        <span class="spinner" aria-hidden="true"></span>
      </div>
    </section>
  </div>
  <!-- js -->
  <script src="inicio.js?v=3"></script>
  <?php require_once __DIR__ . '/../includes/_footer.php'; ?>

</body>
</html>
