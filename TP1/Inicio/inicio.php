<?php
session_start();

$isAuth = isset($_SESSION['username']) && $_SESSION['username'] !== '';
$guard  = $isAuth ? '' : 'disabled';

$guestAvatar = "../imagenes/profilePictures/defaultProfilePicture.png";

$lockedAttr = $isAuth ? '' : 'data-locked="1"'; // bandera para bloquear botones en modo invitado

// === FEED: leer posts desde /JSON/POST.json ===
$POSTS_JSON = __DIR__ . '/../JSON/POST.json';
$posts = [];
if (is_readable($POSTS_JSON)) {
  $raw = file_get_contents($POSTS_JSON);
  $posts = json_decode($raw ?: '[]', true) ?: [];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <title>Inicio — Feed</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="stylesheet" href="inicio.css">
</head>
<body>
  <?php include __DIR__ . '/headerInicio.php'; ?>
  <?php $preruta ="../"; require('../includes/barraLateral/barraLateral.php'); ?>

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
        <?php if (empty($posts)): ?>
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
                        class="chip like"
                        data-id="<?= $idEsc ?>">
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

  <?php require_once __DIR__ . '/../includes/footer.php'; ?>

  <!-- JS separado -->
  <script src="inicio.js?v=20250901"></script>

</body>
</html>
