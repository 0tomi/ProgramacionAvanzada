<?php
$preruta = '../';
require_once "../Controlers/autenticacion.php";

// 1. INCLUYE EL CONTROLADOR
require_once "../Controlers/InicioController.php"; 

$source = 'Perfil'; $require_boostrap = false;
require_once __DIR__ . "/header.php";

// 2. OBTÉN EL USUARIO Y SUS IDs
$u = $_SESSION['user'] ?? null;
if (!$u) {
    // Si no hay usuario, no podemos mostrar perfil
    header("Location: ../LOGIN/_login.php");
    exit;
}

// Obtenemos los datos del usuario en sesión
$profileOwnerId = (int)$u->getIdUsuario();   // El ID del dueño de este perfil
$viewerId = $profileOwnerId;               // El que está viendo es el mismo dueño
$nombre = $u->getNombre() ?? 'Usuario';

// Asumimos que tu objeto User tiene un método getUserTag()
$userTag = method_exists($u, 'getUserTag') ? $u->getUserTag() : strtolower(preg_replace('/\s+/', '', $nombre));
$arroba = '@' . $userTag;

// Ajustamos la ruta del avatar (basado en tu código de inicio.php)
$avatar = '../' . ($u->getProfilePhoto() ?? 'Resources/profilePictures/defaultProfilePicture.png');

// 3. INICIALIZA LOS ARRAYS
$posts = []; // Para la pestaña "Post"
$fotos = []; // Para el carrusel "Fotos"

// 4. BUSCA LOS POSTS USANDO EL CONTROLADOR
try {
    $controller = new InicioController();
    
    // ¡Aquí usamos el método del controlador!
    $userPosts = $controller->getPostsByUserId($profileOwnerId, $viewerId); 

} catch (Exception $e) {
    // Manejar error si el controlador falla (ej. conexión a BD)
    error_log("Error al cargar perfil: " . $e->getMessage());
    $userPosts = []; // Dejar los posts vacíos
}

// 5. PROCESA Y SEPARA LOS RESULTADOS
if (is_array($userPosts)) {
    
    // El array $userPosts ya viene formateado por el controlador.
    // Lo asignamos directamente a la variable $posts para la pestaña "Post"
    $posts = $userPosts; 

    // Ahora, filtramos ese array para obtener solo los que tienen foto
    // para el carrusel "Fotos"
    foreach ($userPosts as $p) {
        $media_url = $p['media_url'] ?? null;
        
        if (!empty($media_url)) {
            // La ruta ya debe venir lista (con ../) desde el controlador
            $fotos[] = [
                'route' => $media_url,
                'name'  => $p['text'] ?? 'Foto del perfil' // Usamos el texto del post como 'alt'
            ];
        }
    }
}

// Función de ayuda para escapar HTML
function e($v){ return htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8'); }
?>
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
body{font-family:'Poppins',system-ui,-apple-system,"Segoe UI",Roboto,Arial,sans-serif;color:var(--ink);background:linear-gradient(360deg,#1b4aaf -40%,#0c0f14 420px) fixed,var(--bg);overflow-x:hidden}
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

.carrusel-contenedor{max-width:620px;margin:0 auto;position:relative}
.slick-list{overflow:hidden !important}
.slick-track{will-change:transform}
.diapo{width:100%;aspect-ratio:16/9;border-radius:14px;overflow:hidden;border:1px solid #1f2a3c;background:#0e141c;box-shadow:0 8px 22px rgb(0 0 0 /.35)}
.diapo img{width:100%;height:100%;object-fit:cover;display:block}
.flecha{position:absolute;top:50%;transform:translateY(-50%);width:48px;height:48px;border-radius:999px;display:grid;place-items:center;background:rgba(15,19,26,.95);border:1px solid #2a3545;box-shadow:0 8px 22px rgba(0,0,0,.55);z-index:5;cursor:pointer}
.flecha svg{width:22px;height:22px;fill:#e8edf5}
.flecha:hover{background:#2563eb;border-color:#3b82f6}
.flecha.izq{left:10px}
.flecha.der{right:10px}
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
.boton{width:100%;padding:12px 16px;border-radius:14px;border:1px solid #124225;background:#17a3a;color:#fff;font-weight:800;cursor:pointer}
.boton:hover{filter:brightness(1.05)}
</style>
</head>
<body>
  <main class="contenedor">
    <section class="tarjeta">
      <div class="encabezado">
        <div class="centro">
          <div class="foto">
            <?php if ($avatar && is_file(str_replace('../', '', $avatar))): /* Corregido para chequear la ruta real */ ?>
              <img src="<?= e($avatar) ?>" alt="perfil">
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
            <p style="color:var(--muted); text-align:center; padding-top:20px;">
              Aún no tienes publicaciones con fotos.
            </p>
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
                      <img src="<?= e($p['media_url']) ?>" alt="">
                    </figure>
                  <?php endif; ?>
                  
                  <?php if (!empty($p['created_at'])): ?>
                    <small style="color:#9fb0c3">
                      <?= e(date('d/m/Y H:i', strtotime($p['created_at']))) ?>
                    </small>
                  <?php endif; ?>
                </article>
              <?php endforeach; ?>
            <?php else: ?>
              <p style="color:var(--muted);text-align:center">Sin publicaciones.</p>
            <?php endif; ?>
          </div>
        </div>
        <div id="tab-info" class="seccion oculta">
          <form class="forma" method="POST" action="../Controlers/update_profile.php" enctype="multipart/form-data">
            <div>
              <label for="nombre">Nombre</label>
              <input id="nombre" name="nombre" type="text" value="<?= e($nombre) ?>" />
            </div>
            <div>
              <label for="descripcion">Descripción</label>
              <textarea id="descripcion" name="descripcion"><?= $u ? e($u->getDescripcion()) : '' ?></textarea>
            </div>
            <div>
              <label for="avatar">Cambiar imagen</label>
              <input id="avatar" name="avatar" type="file" accept="image/*" />
            </div>
            <button class="boton" type="submit">Guardar cambios</button>
          </form>
        </div>
      </div>
    </section>
  </main>

  <?php require_once __DIR__ . "/_footer.php"; ?>

<script>
// Verifica si el elemento del carrusel existe en la página
if ($('#carrusel-fotos').length > 0) {
  
  // Si existe, inicializa Slick
  const $car = $('#carrusel-fotos').slick({
    centerMode:true,
    centerPadding:'60px',
    slidesToShow:1,
    adaptiveHeight:false,
    infinite:true,
    arrows:false,
    dots:true,
    autoplay:true,
    autoplaySpeed:2800,
    pauseOnHover:true,
    cssEase:'ease',
    responsive:[{breakpoint:860,settings:{centerPadding:'30px'}}]
  });
  
  // Y asigna los eventos a las flechas
  document.querySelector('.flecha.izq').onclick=()=> $car.slick('slickPrev');
  document.querySelector('.flecha.der').onclick=()=> $car.slick('slickNext');
}

// El código de las pestañas (tabs) no cambia y funciona igual
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