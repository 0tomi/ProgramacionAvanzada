<?php
session_start();

$isAuth = isset($_SESSION['username']) && $_SESSION['username'] !== '';
$guard  = $isAuth ? '' : 'disabled';

$guestAvatar = "../imagenes/profilePictures/defaultProfilePicture.png";

// si está logueado usa su foto; si no, avatar por defecto
$avatarUrl = ($isAuth && !empty($_SESSION['profilePicture']))
  ? $_SESSION['profilePicture']      // (si existe esa key en tu sesión)
  : $guestAvatar;

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
  <title>Inicio — Demo sin JS</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="stylesheet" href="inicio.css">
</head>
<body>
  <?php include __DIR__ . '/headerInicio.php'; ?>
  <?php require('../includes/barraLateral/barraLateral.php'); ?>

  <div class="shell">
    <section class="feed-col" role="feed" aria-label="Inicio">
      <header class="feed-head">
        <h1>Inicio</h1>
        <span class="sub">Posteos más recientes</span>
      </header>

      <!-- Composer -->
      <div class="composer" <?=$lockedAttr?> aria-label="Publicar">
        <img class="avatar" src="<?= htmlspecialchars($avatarUrl) ?>" alt="Tu avatar">
        <form class="compose" action="#" method="post" enctype="multipart/form-data" novalidate>
          <textarea placeholder="<?= $isAuth ? '¿Qué está pasando?' : 'Inicia sesión para postear' ?>" maxlength="280" <?= $guard ?>></textarea>
          <div class="row">
            <input type="file" id="imgUp" name="images[]" accept="image/*" style="display:none" <?= $guard ?>>
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
            $id      = htmlspecialchars($p['id'] ?? '');
            $name    = htmlspecialchars($p['author']['name'] ?? 'Anónimo');
            $handle  = htmlspecialchars($p['author']['handle'] ?? 'anon');
            $avatarL = strtoupper(substr($p['author']['handle'] ?? 'U', 0, 1));
            $tsRaw   = $p['created_at'] ?? '';
            $tsHuman = $tsRaw ? date('d/m/Y H:i', strtotime($tsRaw)) : '';
            $text    = htmlspecialchars($p['text'] ?? '');
            $likes   = (int)($p['counts']['likes'] ?? 0);
            $media   = trim((string)($p['media_url'] ?? ''));
          ?>
            <article class="post" data-id="<?= htmlspecialchars($id) ?>">
              <!-- Capa clickeable que abre el detalle del post -->
              <a class="post-overlay" 
                href="../POSTS/?id=<?= urlencode($id) ?>" 
                aria-label="Ver post"></a>

              <header class="post-header">
                <div class="avatar"><?= htmlspecialchars($avatarL) ?></div>
                <div class="meta">
                  <div class="name"><?= $name ?></div>
                  <div class="subline">
                    <span class="handle">@<?= $handle ?></span>
                    <span class="dot">·</span>
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
                        data-id="<?= htmlspecialchars($id) ?>">
                  ♥ <span class="count"><?= $likes ?></span>
                </button>
              </div>

            </article>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>

      <script>
        (async function markLikedOnLoad(){
          try{
            const res = await fetch('../POSTS/api.php?action=liked_ids', { credentials:'same-origin' });
            const data = await res.json();
            if (!data.ok || !Array.isArray(data.ids)) return;
            const liked = new Set(data.ids.map(String));
            document.querySelectorAll('.chip.like[data-id]').forEach(btn=>{
              if (liked.has(btn.getAttribute('data-id'))) btn.classList.add('liked');
            });
          }catch(e){ /* silencioso */ }
        })();
      </script>


      <div class="load-more">
        <button class="btn" disabled>Cargar más</button>
        <span class="spinner" aria-hidden="true"></span>
      </div>
    </section>
  </div>

  <?php require_once __DIR__ . '/../includes/footer.php'; ?>

  <!-- CSS mínimo para overlay clickeable (si no está en tu inicio.css) -->
  <style>
    .post{ position:relative; }
    .post-overlay{ position:absolute; inset:0; z-index:1; text-indent:-9999px; }
    .post *{ position:relative; z-index:2; }
    .post .chip{ pointer-events:none; } /* en inicio el botón es decorativo */
  </style>

  <script>
    // Navegación por overlay + Like en Inicio
    document.addEventListener('click', async function(e){
      // 1) LIKE en el feed
      const likeBtn = e.target.closest('.chip.like');
      if (likeBtn) {
        e.preventDefault();
        e.stopPropagation();

        const postId = likeBtn.getAttribute('data-id');
        if (!postId) return;

        // elementos y estado actual
        const countEl = likeBtn.querySelector('.count');
        const prev = parseInt(countEl.textContent, 10) || 0;

        // Optimistic UI
        likeBtn.classList.toggle('liked');
        const optimistic = likeBtn.classList.contains('liked') ? prev + 1 : prev - 1;
        countEl.textContent = optimistic;

        try {
          // IMPORTANTE: ruta relativa desde /Inicio a /POSTS
          const res = await fetch('../POSTS/api.php?action=like', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'same-origin',
            body: JSON.stringify({ post_id: postId })
          });
          const data = await res.json();
          if (!data.ok) throw new Error(data.error || 'Error al likear');

          // sincronizar con el valor real que devolvió la API
          likeBtn.classList.toggle('liked', !!data.liked);
          countEl.textContent = data.like_count;
        } catch (err) {
          // rollback si hubo error
          likeBtn.classList.toggle('liked');
          countEl.textContent = prev;
          alert(String(err.message || err));
        }
        return;
      }

      // 2) Overlay: si clickeás en la tarjeta, navegás al detalle
      const card = e.target.closest('.post');
      if(!card) return;
      const overlay = card.querySelector('.post-overlay');
      if(!overlay || !overlay.getAttribute('href')) return;

      if (!e.target.closest('.post-overlay')) {
        window.location.href = overlay.href;
      }
    }, { passive: true });
  </script>

</body>
</html>


