<?php
session_start();
$isAuth = isset($_SESSION['username']) && $_SESSION['username'] !== '';
$guard  = $isAuth ? '' : 'disabled';


$guestAvatar = "../imagenes/profilePictures/defaultProfilePicture.png";

// si esta logeado q use su fotito
$avatarUrl = ($isAuth && !empty($_SESSION['profilePicture']))
  ? $_SESSION['profilePicture'] //profile picture no existe creo
  : $guestAvatar;

$lockedAttr = $isAuth ? '' : 'data-locked="1"';//esto es solo una bandera para bloquear botones en modo invitado
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
  <?php require('../includes/barraLateral/barraLateral.php'); ?>
  <div class="shell">
    <section class="feed-col" role="feed" aria-label="Inicio">
      <header class="feed-head">
        <h1>Inicio</h1>
        <span class="sub">Posteos más recientes</span>
      </header>

      <!-- Composer -->
      <div class="composer"<?=$lockedAttr?> aria-label="Publicar">
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
      
      <!-- FEED ESTÁTICO (sin JS): dos ejemplos -->
      <div id="feed">
        <article class="post">
          <a href="#" class="post-overlay" aria-label="Ver post"></a><!--click para entrar al post  -->
          <header class="post-header">
            <div class="avatar">V</div>
            <div class="meta">
              <div class="name">Valentino Pettinato</div>
              <div class="subline">
                <span class="handle">@valen</span>
                <span class="dot">·</span>
                <time datetime="2025-08-28T11:20">28/08/2025 11:20</time>
              </div>
            </div>
          </header>
          <p class="text">Hola, soy Valen y este es mi post 👋</p>
          <div class="actions">
            <button type="button" class="chip" disabled>♥ <span class="count">1</span></button>
          </div>
        </article>

        <article class="post">
          <a href="#" class="post-overlay" aria-label="Ver post"></a><!--click para entrar al post  -->
          <header class="post-header">
            <div class="avatar">T</div>
            <div class="meta">
              <div class="name">Tomás Sch</div>
              <div class="subline">
                <span class="handle">@tomas</span>
                <span class="dot">·</span>
                <time datetime="2025-08-29T13:00">29/08/2025 13:00</time>
              </div>
            </div>
          </header>
          <p class="text">Post de Tomás con imagen</p>
          <figure class="media">
            <img src="https://picsum.photos/1200/650?random=3" alt="Imagen del post">
          </figure>
          <div class="actions">
            <button type="button" class="chip" disabled>♥ <span class="count">0</span></button>
          </div>
        </article>
      </div>

      <div class="load-more">
        <button class="btn" disabled>Cargar más</button>
        <span class="spinner" aria-hidden="true"></span>
      </div>
    </section>
  </div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?> 

</body>
</html>
