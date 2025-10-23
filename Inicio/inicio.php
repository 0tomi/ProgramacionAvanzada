<?php
$preruta ="../";
require_once __DIR__ . '/../Controlers/autenticacion.php';
require_once __DIR__ . '/../Controlers/InicioController.php';

$u = $_SESSION['user'];
$viewerId = (int)$u->getIdUsuario();
$flash = $u->getFlash();
$_SESSION['user']->setFlash('');

$inicioController = new InicioController();
$posts = $inicioController->getFeed($viewerId);
if (!is_array($posts)) {
    $posts = [];
}

if (!function_exists('inicio_resolve_media_path')) {
  /**
   * Normaliza rutas relativas de archivos multimedia para que sean accesibles
   * desde la vista de inicio.
   */
  function inicio_resolve_media_path(string $path): string
  {
      $path = trim($path);
      if ($path === '') {
          return '';
      }

      if (preg_match('#^(?:https?:)?//#i', $path)) {
          return $path;
      }

      if (strpos($path, '../') === 0) {
          return $path;
      }

      $normalized = ltrim($path, '/');

      return '../' . $normalized;
  }
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
      <?php
        $composerAvatarSrc = '../' . (string)$_SESSION['user']->getProfilePhoto();
        $composerFormId = 'createPostForm';
        $composerDataContext = 'inicio';
        require __DIR__ . '/../Views/components/createPostForm.php';
      ?>

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
            $avatarUrl = '../Resources/profilePictures/defaultProfilePicture.png';
            if (isset($author['avatar_url'])) {
              $rawAvatar = trim((string)$author['avatar_url']);
              if ($rawAvatar !== '') {
                $resolvedAvatar = inicio_resolve_media_path($rawAvatar);
                if ($resolvedAvatar !== '') {
                  $avatarUrl = $resolvedAvatar;
                }
              }
            }
            $handleLabel = '@'.$handle;

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
            $handleLabelEsc = htmlspecialchars($handleLabel, ENT_QUOTES, 'UTF-8');

            $text = htmlspecialchars((string)($p['text'] ?? ''), ENT_QUOTES, 'UTF-8');

            $likes = (int)($p['counts']['likes'] ?? 0);
            $likeClasses = 'chip like';
            $likedByViewer = !empty($p['viewer']['liked']);
            if ($likedByViewer) {
                $likeClasses .= ' liked';
            }

            $images = [];
            $rawImages = $p['images'] ?? [];
            if (is_array($rawImages)) {
              foreach ($rawImages as $imgValue) {
                $imagePath = trim((string)$imgValue);
                if ($imagePath === '') {
                  continue;
                }

                $resolvedImage = inicio_resolve_media_path($imagePath);
                if ($resolvedImage !== '') {
                  $images[] = $resolvedImage;
                }
              }
            }

            $canDelete = !empty($p['viewer']['can_delete']);
          ?>
            <article class="post" data-id="<?= $idEsc ?>">
              <a class="post-overlay"
                 href="../Views/POSTS/index.php?id=<?= urlencode($id) ?>"
                 aria-label="Ver post"></a>
              <?php if ($canDelete): ?>
                <div class="post-menu">
                  <button type="button"
                          class="post-menu__toggle"
                          aria-haspopup="true"
                          aria-expanded="false">
                    ⋮
                  </button>
                  <div class="post-menu__dropdown"
                       role="menu">
                    <button type="button"
                            class="post-menu__item post-menu__item--danger"
                            role="menuitem"
                            data-action="delete-post"
                            data-id="<?= $idEsc ?>">
                      Eliminar post
                    </button>
                  </div>
                </div>
              <?php endif; ?>

              <header class="post-header">
                <img class="avatar" src="<?= htmlspecialchars($avatarUrl, ENT_QUOTES, 'UTF-8') ?>" alt="Avatar de <?= $name ?>">
                <div class="meta">
                  <div class="name"><?= $name ?></div>
                  <div class="subline">
                    <span class="handle"><?= $handleLabelEsc ?></span><?= $handleLabelEsc ?>
                    <time datetime="<?= $createdAtIso ?>"><?= $createdAtHumanEsc ?></time>
                  </div>
                </div>
              </header>

              <p class="text"><?= $text ?></p>

              <?php if (!empty($images)): ?>
                <div class="media-gallery">
                  <?php foreach ($images as $imgSrc): ?>
                    <?php $imgEsc = htmlspecialchars($imgSrc, ENT_QUOTES, 'UTF-8'); ?>
                    <figure class="media" data-action="open-media" data-media="<?= $imgEsc ?>" tabindex="0" role="button">
                      <img src="<?= $imgEsc ?>" alt="Imagen del post">
                    </figure>
                  <?php endforeach; ?>
                </div>
              <?php endif; ?>

              <div class="actions">
                <button type="button"
                  class="<?= htmlspecialchars($likeClasses, ENT_QUOTES, 'UTF-8') ?>"
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
  <!-- js -->
  <script src="../Resources/js/createPostComposer.js?v=1"></script>
  <script src="inicio.js?v=4"></script>
  <?php require_once __DIR__ . '/../Views/_footer.php'; ?>

</body>
</html>
