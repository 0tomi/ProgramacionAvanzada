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

      <!-- Composer (solo visual) -->
      <div class="composer" aria-hidden="true">
        <img class="avatar" src="https://i.pravatar.cc/88?img=15" alt="Avatar demo">
        <form>
          <textarea placeholder="¿Qué está pasando?" disabled></textarea>
          <div class="row">
            <button class="btn" type="button" disabled>Imagen</button>
            <button class="btn primary" type="button" disabled>Publicar</button>
          </div>
        </form>
      </div>
      
      <!-- FEED ESTÁTICO (sin JS): dos ejemplos -->
      <div id="feed">
        <!-- Post 1 (sin imagen) -->
        <a class="post-link-card" href="/POSTS/?id=197041291234567000">
          <article class="post">
            <header class="post-header">
              <div class="avatar">V</div>
              <div class="meta">
                <strong>Valentino Pettinato</strong>
                <span class="handle">@valen</span>
                <span class="time"> · 28/08/2025 11:20</span>
              </div>
            </header>

            <p>Hola, soy Valen y este es mi post 👋</p>

            <div class="actions">
              <button type="button" class="like" title="Demo: deshabilitado" disabled>
                ♥ <span class="like-count">1</span>
              </button>
            </div>

            <details open>
              <summary>Comentarios (1)</summary>
              <div class="comentarios">
                <ul class="c-tree">
                  <li class="c-node">
                    <div class="c-bubble">
                      <div class="c-meta"><b>Tomás(@tomas)</b> · <span>29/08/2025 07:10</span></div>
                      <div class="c-text">¡bien ahí!</div>
                    </div>
                  </li>
                </ul>
              </div>
              <form class="comment-form">
                <input placeholder="Inicia sesión para comentar" disabled>
                <button disabled>Comentar</button>
              </form>
            </details>
          </article>
        </a>


        <!-- Post 2 (con imagen) -->
        <article class="post">
          <header class="post-header">
            <img class="avatar" src="https://i.pravatar.cc/88?img=32" alt="Avatar Tomás">
            <div class="meta">
              <strong>Tomás Sch</strong>
              <span class="handle">@tomas</span>
              <span class="time"> · 29/08/2025 13:00</span>
            </div>
          </header>
          <p>Post de Tomás con imagen</p>
          <img class="post-image" src="https://picsum.photos/800/450?random=3" alt="Imagen del post">

          <div class="actions">
            <button type="button" class="like" title="Demo: deshabilitado" disabled>
              ♥ <span class="like-count">0</span>
            </button>
          </div>

          <details>
            <summary>Comentarios (0)</summary>
            <div class="comentarios">
              <div class="muted">Sé el primero en comentar</div>
            </div>
            <form class="comment-form">
              <input placeholder="Inicia sesión para comentar" disabled>
              <button disabled>Comentar</button>
            </form>
          </details>
        </article>
      </div>

      <!-- Pie de la columna central -->
      <div class="load-more">
        <button class="btn" disabled>Cargar más</button>
        <span class="spinner" aria-hidden="true"></span>
      </div>
    </section>
  </div>
</body>
</html>
