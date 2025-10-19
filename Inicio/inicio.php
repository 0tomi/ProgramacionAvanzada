<?php
$preruta ="../";
require_once __DIR__ . '/../Controlers/autenticacion.php';
require_once __DIR__ . '/../Controlers/InicioController.php';

$isAuth = $isLoggedIn;
$guard  = $isAuth ? '' : 'disabled';

$likeDisabledAttr = $isAuth ? '' : 'disabled title="Inicia sesión para likear"';

$lockedAttr = $isAuth ? '' : 'data-locked="1"'; // bandera para bloquear botones en modo invitado

$flash = null;
$viewerId = null;
if (isset($_SESSION['user']) && $_SESSION['user'] instanceof User) {
    $u = $_SESSION['user'];
    $viewerId = (int)$u->getIdUsuario();
    $flash = $u->getFlash();
    $u->setFlash(null);
}

$inicioController = new InicioController();
$posts = $inicioController->getFeed($viewerId);
if (!is_array($posts)) {
    $posts = [];
}
?>

<?php $require_boostrap = false; $source = 'Inicio'; require_once __DIR__ . '/../Views/header.php'; ?>

<body>
  <?php include __DIR__ . '/headerInicio.php'; ?>
  <?php require('../Views/barraLateral/barraLateral.php'); ?>
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

      <!-- FEED desde la API -->
      <div id="feed">
        <?php if (empty($posts)): ?>
          <p class="muted">No hay posts todavía.</p>
        <?php else: ?>
          <?php foreach ($posts as $p):
            $id = (string)($p['id'] ?? '');
            if ($id === '') {
              continue;
            }

            $idEsc = htmlspecialchars($id, ENT_QUOTES, 'UTF-8');
            $author = is_array($p['author'] ?? null) ? $p['author'] : [];
            $name = htmlspecialchars((string)($author['name'] ?? 'Anónimo'), ENT_QUOTES, 'UTF-8');
            $handle = (string)($author['handle'] ?? '');
            $avatarLetter = strtoupper(substr($handle !== '' ? $handle : ($author['name'] ?? 'U'), 0, 1));
            $avatarUrl = isset($author['avatar_url']) ? trim((string)$author['avatar_url']) : '';

            $createdAt = (string)($p['created_at'] ?? '');
            $createdAtIso = htmlspecialchars($createdAt, ENT_QUOTES, 'UTF-8');
            $createdAtHuman = '';
            if ($createdAt !== '') {
              try {
                $dt = new DateTimeImmutable($createdAt);
                $createdAtHuman = $dt->format('d/m/Y H:i');
              } catch (\Throwable $e) {
                $createdAtHuman = '';
              }
            }
            $createdAtHumanEsc = htmlspecialchars($createdAtHuman, ENT_QUOTES, 'UTF-8');

            $text = htmlspecialchars((string)($p['text'] ?? ''), ENT_QUOTES, 'UTF-8');

            $likes = (int)($p['counts']['likes'] ?? 0);
            $likeClasses = 'chip like';
            $likedByViewer = !empty($p['viewer']['liked']);
            if ($likedByViewer) {
              $likeClasses .= ' liked';
            }

            $media = isset($p['media_url']) ? trim((string)$p['media_url']) : '';
            $mediaEsc = htmlspecialchars($media, ENT_QUOTES, 'UTF-8');

            $canDelete = !empty($p['viewer']['can_delete']);
          ?>
            <article class="post" data-id="<?= $idEsc ?>">
              <a class="post-overlay"
                 href="../POSTS/?id=<?= urlencode($id) ?>"
                 aria-label="Ver post"></a>

              <header class="post-header">
                <?php if ($avatarUrl !== ''): ?>
                  <img class="avatar" src="<?= htmlspecialchars($avatarUrl, ENT_QUOTES, 'UTF-8') ?>" alt="Avatar de <?= $name ?>">
                <?php else: ?>
                  <div class="avatar"><?= htmlspecialchars($avatarLetter, ENT_QUOTES, 'UTF-8') ?></div>
                <?php endif; ?>
                <div class="meta">
                  <div class="name"><?= $name ?></div>
                  <div class="subline">
                    <time datetime="<?= $createdAtIso ?>"><?= $createdAtHumanEsc ?></time>
                  </div>
                </div>
              </header>

              <p class="text"><?= $text ?></p>

              <?php if ($mediaEsc !== ''): ?>
                <figure class="media">
                  <img src="<?= $mediaEsc ?>" alt="Imagen del post">
                </figure>
              <?php endif; ?>

              <div class="actions">
                <button type="button"
                  class="<?= htmlspecialchars($likeClasses, ENT_QUOTES, 'UTF-8') ?>"
                  data-id="<?= $idEsc ?>"
                  <?= $likeDisabledAttr ?>>
                  ♥ <span class="count"><?= $likes ?></span>
                </button>
                <?php if ($canDelete): ?>
                  <button type="button"
                    class="chip delete"
                    data-action="delete-post"
                    data-id="<?= $idEsc ?>">
                    Eliminar
                  </button>
                <?php endif; ?>
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
  <?php require_once __DIR__ . '/../Views/_footer.php'; ?>

</body>
</html>
