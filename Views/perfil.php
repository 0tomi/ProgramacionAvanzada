<?php
declare(strict_types=1);

$preruta = '../';
$source = 'Perfil';
$require_boostrap = false;

require_once __DIR__ . '/../Controlers/autenticacion.php';
require_once __DIR__ . '/../Controlers/ProfileController.php';
require_once __DIR__ . '/../Controlers/InicioController.php';

require_once __DIR__ . "/header.php";


$controller = new ProfileController();
$controller->processProfileUpdate();
$data = $controller->getProfileData();

$nombre = $u->getNombre() ?? 'Usuario';


$userTag      = $data['userTag']      ?? (method_exists($u,'getUserTag') ? (string)$u->getUserTag() : strtolower(preg_replace('/\s+/', '', (string)$nombre)));
$description  = $data['description']  ?? '';
$profilePhoto = $data['profilePhoto'] ?? 'Resources/profilePictures/defaultProfilePicture.png';
$arroba       = '@' . strtolower($userTag);


$isHttp     = (strpos($profilePhoto, 'http') === 0);
$avatarWeb  = $isHttp ? $profilePhoto : ('../' . ltrim($profilePhoto, '/'));
$avatarFs   = $isHttp ? null : (__DIR__ . '/../' . ltrim($profilePhoto, '/'));
$hasLocal   = $avatarFs ? is_file($avatarFs) : false;


if (!function_exists('e')) {
  function e($v){ return htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8'); }
}
if (!function_exists('perfil_resolve_media_path')) {
  function perfil_resolve_media_path(string $path): string {
    $path = trim($path);
    if ($path === '') return '';
    if (preg_match('#^(?:https?:)?//#i', $path)) return $path;
    if (strpos($path, '../') === 0) return $path;
    return '../' . ltrim($path, '/');
  }
}

$profileOwnerId = (int)$u->getIdUsuario();
$viewerId       = $profileOwnerId;

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

<header class="flex items-center justify-between px-6 py-4 border-b border-[color:var(--line)] bg-[color:var(--panel)]">
  <a href="../logout.php"
     class="ml-auto px-4 py-2 rounded-full font-bold border border-[color:var(--line)] bg-red-600 text-white hover:opacity-90 transition">
    Cerrar sesión
  </a>
</header>

<?php require_once __DIR__ . "/barraLateral/barraLateral.php"; ?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<title>Ritual · Perfil</title>
<meta name="viewport" content="width=device-width, initial-scale=1" />
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700;800&display=swap" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css"/>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick-theme.css"/>
<script src="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js"></script>
<style>
:root{--bg:#0c0f14;--panel:#0f131a;--panel-2:#0b0f15;--ink:#e8edf5;--muted:#8ea0b5;--line:#1b2431;--shadow:0 12px 36px rgb(0 0 0 /.28)}
*{box-sizing:border-box}
html,body{margin:0;height:100%}
body{font-family:'Poppins',system-ui,-apple-system,"Segoe UI",Roboto,Arial,sans-serif;color:var(--ink);background:linear-gradient(360deg,#1b4aaf -40%,#0c0f14 420px) fixed,var(--bg) !important;overflow-x:hidden}
.contenedor{max-width:950px;margin:0 auto;padding:24px 16px 40px}
.tarjeta{background:linear-gradient(180deg,var(--panel) 0%,var(--panel-2) 120%);border:1px solid var(--line);border-radius:18px;box-shadow:var(--shadow);overflow:hidden}
.centro{max-width:680px;margin:0 auto}
.encabezado{padding:26px 0 14px;border-bottom:1px solid var(--line);text-align:center}
.foto{width:100px;height:100px;border-radius:50%;overflow:hidden;margin:0 auto 10px;border:2px solid #1e2a3a;box-shadow:0 6px 18px rgb(0 0 0 /.35);display:grid;place-items:center;background:radial-gradient(circle at 30% 30%,#203047,#111826 70%)}
.foto img{width:100%;height:100%;object-fit:cover}
.inicial{font-weight:800;font-size:48px;color:#bcd2ea}
.nombre h1{margin:0;font-size:24px;font-weight:800}
.nombre .arroba{color:var(--muted);margin-top:4px;font-weight:700}
.pestanias{padding:12px 0;border-top:1px solid var(--line);border-bottom:1px solid var(--line);background:rgba(15,19,26,.88);backdrop-filter:blur(8px)}
.lista-pestanias{display:flex;gap:10px;justify-content:center}
.pestania{padding:10px 18px;border-radius:10px;font-weight:700;color:var(--muted);border:1px solid transparent;cursor:pointer}
.pestania.activa{color:#fff;border-color:#203665;background:rgba(37,99,235,.08)}
.marco{height:460px;padding:18px 0}
.seccion{height:100%;overflow-y:auto;overflow-x:hidden}
.oculta{display:none}
.alert{margin:14px auto 0;max-width:680px;padding:12px 14px;border-radius:12px;border:1px solid}
.alert.ok{background:#072812;border-color:#1b6b3a;color:#c8f3db}
.alert.err{background:#2b0c10;border-color:#7a2531;color:#ffd7dc}
.carrusel-contenedor{max-width:620px;margin:0 auto;position:relative}
.slick-list{overflow:hidden !important}
.slick-track{will-change:transform}
.diapo{width:100%;aspect-ratio:16/9;border-radius:14px;overflow:hidden;border:1px solid #1f2a3c;background:#0e141c;box-shadow:0 8px 22px rgb(0 0 0 /.35)}
.diapo img{width:100%;height:100%;object-fit:cover;display:block}
.flecha{position:absolute;top:50%;transform:translateY(-50%);width:48px;height:48px;border-radius:999px;display:grid;place-items:center;background:rgba(15,19,26,.95);border:1px solid #2a3545;box-shadow:0 8px 22px rgba(0,0,0,.55);z-index:5;cursor:pointer}
.flecha svg{width:22px;height:22px;fill:#e8edf5}
.flecha:hover{background:#2563eb;border-color:#3b82f6}
.flecha.izq{left:10px}.flecha.der{right:10px}
.slick-dots{bottom:-22px}
.slick-dots li button:before{color:#8fb4ff;font-size:10px}
.slick-dots li.slick-active button:before{color:#fff}
.lista-post{display:grid;gap:12px;max-width:620px;margin:0 auto}
.post{background:#0e141c;border:1px solid var(--line);border-radius:14px;padding:14px}
.post h3{margin:0 0 6px}
.post p{margin:0;color:var(--muted)}
.forma{display:grid;gap:14px;max-width:620px;margin:0 auto}
label{font-weight:700}
input[type="text"],textarea{width:100%;border-radius:12px;border:1px solid #22303c;background:#0f1419;color:#fff;padding:12px 14px;font:inherit}
textarea{min-height:120px}
input[type="file"]{color:#cbd5e1}
.boton{width:100%;padding:12px 16px;border-radius:14px;border:1px solid #124225;background:#17a34a;color:#fff;font-weight:800;cursor:pointer}
.boton:hover{filter:brightness(1.05)}
.boton.sec{background:#1a2a41;border-color:#27406b}

/* INFO */
.info-wrap{max-width:720px;margin:0 auto}
.info-grid{display:grid;gap:16px}
@media(min-width:900px){.info-grid{grid-template-columns:1fr 1fr}}
.info-card{background:linear-gradient(180deg,#111827 0%,#0b1220 120%);border:1px solid var(--line);border-radius:16px;padding:18px;box-shadow:0 10px 26px rgba(0,0,0,.38)}
.info-card .title{display:flex;align-items:center;gap:10px;margin:0 0 12px;font-weight:800;font-size:18px}
.info-card .sub{margin:-6px 0 14px;color:var(--muted);font-size:12px}
.info-card .field{display:grid;gap:10px;margin-bottom:10px}
.info-card label{font-weight:800;color:#cfe3ff}
.info-card input[type="text"],.info-card textarea,.info-card input[type="file"]{width:100%;border-radius:12px;border:1px solid #1e2a32;background:#0b1118;color:#fff;padding:12px 14px;font:inherit}
.info-card textarea{min-height:140px}
.info-card .boton{width:100%}
.info-card.wide{grid-column:1/-1}
.info-card input[type="file"]::-webkit-file-upload-button{border:1px solid #27406b;background:#1a2a41;color:#e8edf5;border-radius:10px;padding:8px 12px;font-weight:700;cursor:pointer}
.info-card input[type="file"]::file-selector-button{border:1px solid #27406b;background:#1a2a41;color:#e8edf5;border-radius:10px;padding:8px 12px;font-weight:700;cursor:pointer}
</style>
</head>
<body>
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
            <div class="arroba"><?= e($arroba) ?></div>
          </div>
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
            <p style="color:var(--muted); text-align:center; padding-top:20px;">Aún no tienes publicaciones con fotos.</p>
          <?php endif; ?>
        </div>

        <div id="tab-post" class="seccion oculta">
          <div class="lista-post" id="lista-posts">
            <?php if (count($posts)): ?>
              <?php foreach ($posts as $p): ?>
                <article class="post">
                  <h3><?= e($p['author']['name'] ?? 'Post') ?></h3>
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
            </div>
          </div>
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
  document.querySelector('.flecha.izq').onclick=()=> $car.slick('slickPrev');
  document.querySelector('.flecha.der').onclick=()=> $car.slick('slickNext');
}
const pestanias=document.querySelectorAll('.pestania');
const secciones={fotos:document.getElementById('tab-fotos'),post:document.getElementById('tab-post'),info:document.getElementById('tab-info')};
pestanias.forEach(p=>{
  p.addEventListener('click',()=>{
    pestanias.forEach(x=>x.classList.remove('activa'));
    p.classList.add('activa');
    Object.values(secciones).forEach(s=>s.classList.add('oculta'));
    secciones[p.dataset.tab].classList.remove('oculta');
  });
});
</script>
</body>
</html>
