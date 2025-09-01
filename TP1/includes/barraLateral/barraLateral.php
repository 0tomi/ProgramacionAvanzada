<?php require_once __DIR__ . '/estado_user.php'; ?>

<!-- Botón de apertura -->
<button id="sidebarToggle"
        class="fixed top-4 left-4 z-50 p-2 rounded-md bg-gray-700 text-white hover:bg-gray-600">
  <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
       stroke="currentColor" class="w-6 h-6">
    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 5.25h16.5m-16.5 6h16.5m-16.5 6h16.5" />
  </svg>
  <span class="sr-only">Abrir menú</span>
</button>

<!-- Overlay -->
<div id="sidebarOverlay" class="fixed inset-0 bg-black/50 hidden z-40"></div>

<!-- Sidebar -->
<aside id="sidebar"
       class="fixed inset-y-0 left-0 w-72 bg-slate-800 text-white transform -translate-x-full transition-transform z-50 flex flex-col">
  <div class="p-4 flex items-center justify-between border-b border-slate-700">
    <h2 class="text-lg font-semibold">Menú</h2>
    <button id="sidebarClose" class="text-white">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
      </svg>
      <span class="sr-only">Cerrar menú</span>
    </button>
  </div>

  <!-- Vista al perfil -->
  <div class="p-4 text-center border-b border-slate-700">
    <img src="<?= $profilePicture ?>" alt="Usuario" class="w-20 h-20 rounded-full mx-auto mb-2">
    <div class="font-semibold"><?= $userName ?></div>
  </div>

  <nav class="flex-1 p-4 space-y-2">
    <a href=<?= $boton_perfil ?> class="flex items-center px-3 py-2 rounded-lg bg-slate-700">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
      </svg>
      Inicio
    </a>
    <a href="perfil.php" class="flex items-center px-3 py-2 rounded-lg hover:bg-slate-700">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
      </svg>
      Perfil
    </a>
    <a href="#" class="flex items-center px-3 py-2 rounded-lg hover:bg-slate-700">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M6.633 10.5c-.806 0-1.533.446-1.902 1.166l-1.045 2.09A2.166 2.166 0 0 0 5.595 16.5h12.81a2.166 2.166 0 0 0 1.909-2.744l-1.045-2.09a2.166 2.166 0 0 0-1.903-1.166H6.633Z" />
        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75a3.75 3.75 0 1 1 7.5 0 3.75 3.75 0 0 1-7.5 0Z" />
      </svg>
      Tema
    </a>
  </nav>

  <div class="p-4 border-t border-slate-700">
    <a href="<?= $ctaHrefSafe ?>"
       class="w-full inline-flex items-center justify-center px-3 py-2 rounded-lg text-white <?php echo $isLoggedIn ? 'bg-red-600 hover:bg-red-500' : 'bg-gray-600 hover:bg-gray-500'; ?>">
      <?php if ($isLoggedIn): ?>
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-9A2.25 2.25 0 0 0 2.25 5.25v13.5A2.25 2.25 0 0 0 4.5 21h9a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
      </svg>
      <?php else: ?>
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 mr-2">
        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-9A2.25 2.25 0 0 0 2.25 5.25v13.5A2.25 2.25 0 0 0 4.5 21h9a2.25 2.25 0 0 0 2.25-2.25V15m-6-3 3-3m0 0-3-3m3 3H9" />
      </svg>
      <?php endif; ?>
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
      sidebar.classList.remove('-translate-x-full');
      overlay.classList.remove('hidden');
    }

    function closeSidebar() {
      sidebar.classList.add('-translate-x-full');
      overlay.classList.add('hidden');
    }

    openBtn.addEventListener('click', openSidebar);
    closeBtn.addEventListener('click', closeSidebar);
    overlay.addEventListener('click', closeSidebar);
  })();
</script>

