<?php 
// debug xq no se veian las fotos de perfil
/*echo "<pre>";
var_dump($_SESSION['user_profile_picture']);
echo "<pre>";*/

// Referencias de los otros botones
$boton_perfil = $preruta.'Views/perfil.php';
$boton_inicio = $preruta.'Inicio/inicio.php';

// CTA (botón inferior)
$ctaHref = $preruta.'logout.php';
$ctaText = 'Cerrar sesión';


// Sanitizar salidas ????????????????
$ctaHrefSafe       = htmlspecialchars($ctaHref ?? '#', ENT_QUOTES, 'UTF-8');
$ctaTextSafe       = htmlspecialchars($ctaText ?? '', ENT_QUOTES, 'UTF-8');

// Determinar sección activa para resaltar en la barra
// Prioriza $activeSidebar si viene seteado; si no, usa $source (p.ej. 'Inicio', 'Perfil')
$__active = strtolower((string)($activeSidebar ?? ($source ?? '')));
$inicioActiveClass = ($__active === 'inicio') ? ' active' : '';
$perfilActiveClass = ($__active === 'perfil') ? ' active' : '';
?>

<!-- Botón de apertura -->
<button id="sidebarToggle"
        type="button"
        class="btn btn-dark sidebar-toggle"
        aria-controls="sidebar"
        aria-expanded="false">
  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
       stroke="currentColor" class="sidebar-toggle__icon">
    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 5.25h16.5m-16.5 6h16.5m-16.5 6h16.5" />
  </svg>
  <span class="visually-hidden">Abrir menú</span>
</button>

<!-- Overlay -->
<div id="sidebarOverlay" class="sidebar-overlay" hidden></div>

<!-- Sidebar -->
<aside id="sidebar" class="sidebar-panel" aria-hidden="true">
  <div class="sidebar-panel__header d-flex align-items-center justify-content-between">
    <h2 class="sidebar-panel__title mb-0">Menú</h2>
    <button id="sidebarClose" type="button" class="btn btn-link p-0 sidebar-close">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="sidebar-close__icon">
        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
      </svg>
      <span class="visually-hidden">Cerrar menú</span>
    </button>
  </div>

  <!-- Vista al perfil -->
  <div class="sidebar-profile text-center">
    <img src="<?= $profilePicture ?>" alt="Usuario" class="sidebar-profile__avatar mb-2">
    <div class="sidebar-profile__name fw-semibold"><?= $userName ?></div>
  </div>

  <nav class="sidebar-nav">
    <a href="<?= $boton_inicio ?>" class="sidebar-link<?= $inicioActiveClass ?>">
      <span class="sidebar-icon me-2">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
        </svg>
      </span>
      Inicio
    </a>
    <?php if ($isLoggedIn): ?>
      <a href=" <?= $boton_perfil; ?> " class="sidebar-link<?= $perfilActiveClass ?>">
        <span class="sidebar-icon me-2">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
          </svg>
        </span>
        Perfil
      </a>
    <?php endif;?>
    <a href="#" class="sidebar-link">
      <span class="sidebar-icon me-2">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" d="M6.633 10.5c-.806 0-1.533.446-1.902 1.166l-1.045 2.09A2.166 2.166 0 0 0 5.595 16.5h12.81a2.166 2.166 0 0 0 1.909-2.744l-1.045-2.09a2.166 2.166 0 0 0-1.903-1.166H6.633Z" />
          <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75a3.75 3.75 0 1 1 7.5 0 3.75 3.75 0 0 1-7.5 0Z" />
        </svg>
      </span>
      Tema
    </a>
  </nav>

  <div class="sidebar-footer">
    <a href="<?= $ctaHrefSafe ?>"
       class="sidebar-cta btn w-100 d-flex align-items-center justify-content-center gap-2 <?php echo $isLoggedIn ? 'btn-danger' : 'btn-secondary'; ?>">
      <span class="sidebar-icon">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
          <?php if ($isLoggedIn): ?>
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-9A2.25 2.25 0 0 0 2.25 5.25v13.5A2.25 2.25 0 0 0 4.5 21h9a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
          <?php else: ?>
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-9A2.25 2.25 0 0 0 2.25 5.25v13.5A2.25 2.25 0 0 0 4.5 21h9a2.25 2.25 0 0 0 2.25-2.25V15m-6-3 3-3m0 0-3-3m3 3H9" />
          <?php endif; ?>
        </svg>
      </span>
      <?= $ctaTextSafe ?>
    </a>
  </div>
</aside>

<script>
  (function() {
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebarOverlay');
    const openBtn = document.getElementById('sidebarToggle');
    const closeBtn = document.getElementById('sidebarClose');

    function openSidebar() {
      sidebar.classList.add('sidebar-panel--open');
      sidebar.setAttribute('aria-hidden', 'false');
      overlay.classList.add('sidebar-overlay--visible');
      overlay.removeAttribute('hidden');
      openBtn.setAttribute('aria-expanded', 'true');
    }

    function closeSidebar() {
      sidebar.classList.remove('sidebar-panel--open');
      sidebar.setAttribute('aria-hidden', 'true');
      overlay.classList.remove('sidebar-overlay--visible');
      overlay.setAttribute('hidden', 'hidden');
      openBtn.setAttribute('aria-expanded', 'false');
    }

    openBtn.addEventListener('click', openSidebar);
    closeBtn.addEventListener('click', closeSidebar);
    overlay.addEventListener('click', closeSidebar);
  })();
</script>
