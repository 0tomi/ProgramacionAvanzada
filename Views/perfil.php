<?php
declare(strict_types=1);

$preruta = '../';
$source = 'Perfil';
$require_boostrap = true;

require_once __DIR__ . '/../Controlers/autenticacion.php';
require_once __DIR__ . '/../Controlers/ProfileController.php';
require_once __DIR__ . '/../Controlers/InicioController.php';

require_once __DIR__ . "/header.php";

$u = $_SESSION['user'];
$viewerId = 0;
if (is_object($u) && method_exists($u, 'getIdUsuario')) {
  $viewerId = (int)$u->getIdUsuario();
}

$requestedProfileId = null;
$requestedProfileUsername = null;
if (isset($_GET['id'])) {
  $requestedProfileId = (int)$_GET['id'];
  if ($requestedProfileId <= 0) {
    $requestedProfileId = null;
  }
}
if (isset($_GET['username'])) {
  $requestedProfileUsername = trim((string)$_GET['username']);
  if ($requestedProfileUsername === '') {
    $requestedProfileUsername = null;
  }
}

$controller = new ProfileController($requestedProfileId, $requestedProfileUsername);
$controller->processProfileUpdate();
$data = $controller->getProfileData();

$isOwner        = (bool)($data['isOwner'] ?? false);
$profileOwnerId = $controller->getProfileOwnerId();

$nombre = trim((string)($data['displayName'] ?? ''));
if ($nombre === '') {
  if ($isOwner && is_object($u) && method_exists($u, 'getNombre')) {
    $nombre = (string)($u->getNombre() ?? 'Usuario');
  } else {
    $nombre = 'Usuario';
  }
}

$userTag = trim((string)($data['userTag'] ?? ''));
if ($userTag === '' && $isOwner && is_object($u) && method_exists($u, 'getUserTag')) {
  $userTag = (string)$u->getUserTag();
}
$fallbackTag = strtolower(preg_replace('/\s+/', '', (string)$nombre));
$userTag = $userTag !== '' ? $userTag : $fallbackTag;

$description  = (string)($data['description'] ?? '');
$profilePhoto = (string)($data['profilePhoto'] ?? 'Resources/profilePictures/defaultProfilePicture.png');
$arroba       = $userTag !== '' ? '@' . strtolower($userTag) : '';


$isHttp     = (strpos($profilePhoto, 'http') === 0);
$avatarWeb  = $isHttp ? $profilePhoto : ('../' . ltrim($profilePhoto, '/'));
$avatarFs   = $isHttp ? null : (__DIR__ . '/../' . ltrim($profilePhoto, '/'));
$hasLocal   = $avatarFs ? is_file($avatarFs) : false;



function e($v){ return htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8'); }

function perfil_resolve_media_path(string $path): string {
  $path = trim($path);
  if ($path === '') return '';
  if (preg_match('#^(?:https?:)?//#i', $path)) return $path;
  if (strpos($path, '../') === 0) return $path;
  return '../' . ltrim($path, '/');
}

$posts = [];
$fotos = [];

try {
  $inicio = new InicioController();
  $userPosts = $inicio->getPostsByUserId($profileOwnerId, $viewerId);
  $posts = is_array($userPosts) ? $userPosts : [];
  foreach ($posts as $p) {
    $media = trim((string)($p['media_url'] ?? ''));
    if ($media !== '') {
      $fotos[] = [
        'route' => perfil_resolve_media_path($media),
        'name'  => (string)($p['text'] ?? 'Foto del post')
      ];
    }
  }
} catch (Throwable $e) {
  error_log('Perfil:getPostsByUserId -> ' . $e->getMessage());
  $posts = [];
  $fotos = [];
}

$flash = $_SESSION['flash'] ?? null;
if ($flash) unset($_SESSION['flash']);
?>

<body>
  <?php require_once __DIR__ . '/../Inicio/headerInicio.php'; ?>
  <?php require_once __DIR__ . "/barraLateral/barraLateral.php"; ?>

  <!-- Dependencias para carrusel de fotos del perfil -->
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>
  <main class="contenedor">
    <?php if ($flash): ?>
      <div class="alert <?= $flash['type']==='error'?'err':'ok' ?>"><?= e($flash['message'] ?? '') ?></div>
    <?php endif; ?>
    <?php if (!empty($data['errors'])): ?>
      <div class="alert err">
        <?php foreach (($data['errors'] ?? []) as $err): ?>
          <div>• <?= e($err) ?></div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <section class="tarjeta">
      <div class="encabezado">
        <div class="centro">
          <div class="foto">
            <?php if ($isHttp || $hasLocal): ?>
              <img src="<?= e($avatarWeb) ?>" alt="perfil">
            <?php else: ?>
              <div class="inicial"><?= strtoupper(substr(e($nombre),0,1)) ?></div>
            <?php endif; ?>
          </div>
          <div class="nombre">
            <h1><?= e($nombre) ?></h1>
            <?php if ($arroba !== ''): ?>
              <div class="arroba"><?= e($arroba) ?></div>
            <?php endif; ?>
          </div>
          <?php if ($description !== ''): ?>
            <p class="bio"><?= e($description) ?></p>
          <?php elseif ($isOwner): ?>
            <p class="bio">Todavía no agregaste una descripción.</p>
          <?php endif; ?>
        </div>
      </div>

      <div class="pestanias">
          <div class="centro lista-pestanias">
            <div class="pestania activa" data-tab="fotos">Fotos</div>
            <div class="pestania" data-tab="post">Post</div>
            <div class="pestania" data-tab="info">Info</div>
          </div>
        </div>

      <div class="centro marco">
        <div id="tab-fotos" class="seccion">
          <?php if (count($fotos)): ?>
            <div class="carrusel-contenedor">
              <button type="button" class="flecha izq" aria-label="Anterior">
                <svg viewBox="0 0 24 24"><path d="M15.41 7.41 14 6l-6 6 6 6 1.41-1.41L10.83 12z"/></svg>
              </button>
              <button type="button" class="flecha der" aria-label="Siguiente">
                <svg viewBox="0 0 24 24"><path d="M8.59 16.59 13.17 12 8.59 7.41 10 6l6 6-6 6z"/></svg>
              </button>
              <div class="carrusel" id="carrusel-fotos">
                <?php foreach ($fotos as $f): ?>
                  <div class="diapo"><img src="<?= e($f['route']) ?>" alt="<?= e($f['name'] ?? '') ?>"></div>
                <?php endforeach; ?>
              </div>
            </div>
          <?php else: ?>
            <p style="color:var(--muted); text-align:center; padding-top:20px;">
              <?= $isOwner ? 'Todavía no tenés publicaciones con fotos.' : 'Este perfil aún no tiene publicaciones con fotos.' ?>
            </p>
          <?php endif; ?>
        </div>

        <div id="tab-post" class="seccion oculta">
          <div class="lista-post" id="lista-posts">
            <?php if (count($posts)): ?>
              <?php foreach ($posts as $p): ?>
                <?php
                  $postAuthor = is_array($p['author'] ?? null) ? $p['author'] : [];
                  $postAuthorName = (string)($postAuthor['name'] ?? 'Post');
                  $postAuthorHandle = trim((string)($postAuthor['handle'] ?? ''));
                  $postAuthorId = null;
                  if (isset($postAuthor['id']) && $postAuthor['id'] !== '') {
                    $postAuthorId = (int)$postAuthor['id'];
                  }
                  $postProfileHref = 'perfil.php';
                  if ($postAuthorId !== null && $postAuthorId !== $viewerId) {
                    $postProfileHref .= '?id=' . urlencode((string)$postAuthorId);
                  }
                ?>
                <article class="post">
                  <h3>
                    <a class="post-author-link" href="<?= e($postProfileHref) ?>">
                      <?= e($postAuthorName) ?>
                    </a>
                    <?php if ($postAuthorHandle !== ''): ?>
                      <span class="handle"><?= e('@' . $postAuthorHandle) ?></span>
                    <?php endif; ?>
                    <?php if (!empty($p['parent_id'] ?? null)): ?>
                      <span class="reply-badge">Respondiendo a otro post</span>
                    <?php endif; ?>
                  </h3>
                  <p><?= e($p['text'] ?? '') ?></p>
                  <?php if (!empty($p['media_url'])): ?>
                    <figure class="diapo" style="aspect-ratio:16/9;margin:8px 0">
                      <img src="<?= e(perfil_resolve_media_path((string)$p['media_url'])) ?>" alt="">
                    </figure>
                  <?php endif; ?>
                  <?php if (!empty($p['created_at'])): ?>
                    <small style="color:#9fb0c3"><?= e(date('d/m/Y H:i', strtotime($p['created_at']))) ?></small>
                  <?php endif; ?>
                </article>
              <?php endforeach; ?>
            <?php else: ?>
              <p style="color:var(--muted);text-align:center">Sin publicaciones.</p>
            <?php endif; ?>
          </div>
        </div>
          <div id="tab-info" class="seccion oculta">
            <?php if ($isOwner): ?>
            <div class="info-wrap">
              <div class="info-grid">
               
                <form class="info-card" method="POST" action="">
                  <input type="hidden" name="action" value="updateUserTag">
                  <h3 class="title">Usuario</h3>
                  <p class="sub">Definí tu @usuario para que puedan encontrarte.</p>
                  <div class="field">
                    <label for="userTag">Usuario (@user)</label>
                    <input id="userTag" name="userTag" type="text" value="<?= e($userTag) ?>" placeholder="tu_usuario">
                  </div>
                  <button class="boton sec" type="submit">Actualizar usuario</button>
                </form>

                <form class="info-card" method="POST" action="">
                  <input type="hidden" name="action" value="updateDescripcion">
                  <h3 class="title">Descripción</h3>
                  <p class="sub">Contá algo breve sobre vos.</p>
                  <div class="field">
                    <label for="descripcion">Descripción</label>
                    <textarea id="descripcion" name="description" placeholder="Contá algo de vos..."><?= e($description) ?></textarea>
                  </div>
                  <button class="boton sec" type="submit">Actualizar descripción</button>
                </form>

                <form class="info-card wide" method="POST" action="" enctype="multipart/form-data">
                  <input type="hidden" name="action" value="updatePhoto">
                  <h3 class="title">Imagen de perfil</h3>
                  <p class="sub">Subí una foto cuadrada para que se vea mejor.</p>
                  <div class="field">
                    <label for="avatar">Cambiar imagen</label>
                    <input id="avatar" name="imagen" type="file" accept="image/*">
                  </div>
                  <button class="boton sec" type="submit">Subir nueva foto</button>
                </form>
                <?php else: ?>
                  <div class="info-card">
                    <h3 class="title">Descripción de <?= e($nombre) ?></h3>
                    <?php if ($description !== ''): ?>
                      <p><?= e($description) ?></p>
                    <?php else: ?>
                      <p class="sub">Este usuario todavía no agregó una descripción.</p>
                    <?php endif; ?>
                  </div>
              </div>
            </div>
            <?php endif ?>  
          </div>
      </div>
    </section>
  </main>

  <?php require_once __DIR__ . "/_footer.php"; ?>

<script>
if ($('#carrusel-fotos').length > 0) {
  const $car = $('#carrusel-fotos').slick({
    centerMode:true, centerPadding:'60px', slidesToShow:1,
    adaptiveHeight:false, infinite:true, arrows:false, dots:true,
    autoplay:true, autoplaySpeed:2800, pauseOnHover:true, cssEase:'ease',
    responsive:[{breakpoint:860,settings:{centerPadding:'30px'}}]
  });
  const prev = document.querySelector('.flecha.izq');
  const next = document.querySelector('.flecha.der');
  if (prev) prev.onclick = () => $car.slick('slickPrev');
  if (next) next.onclick = () => $car.slick('slickNext');
}
const pestanias = document.querySelectorAll('.pestania');
const secciones = {};
pestanias.forEach(p => {
  const tab = p.dataset.tab;
  if (!tab) return;
  const section = document.getElementById(`tab-${tab}`);
  if (section) {
    secciones[tab] = section;
  }
});
pestanias.forEach(p => {
  p.addEventListener('click', () => {
    pestanias.forEach(x => x.classList.remove('activa'));
    p.classList.add('activa');
    Object.values(secciones).forEach(s => s.classList.add('oculta'));
    const target = secciones[p.dataset.tab];
    if (target) target.classList.remove('oculta');
  });
});
</script>
</body>
</html>
