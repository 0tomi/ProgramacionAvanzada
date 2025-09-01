<?php
// arrancar la "sesión" (solo si no está iniciada)
$isAuth = (isset($_SESSION['username']) && $_SESSION['username'] !== '');//si hay nombre es pq hay login!!
?>
<header class="flex items-center justify-between px-6 py-4 border-b border-[color:var(--line)] bg-[color:var(--panel)]">
  <?php if ($isAuth): ?>
    <!-- mostrar logout papaa -->
    <a href="../logout.php"
       class="ml-auto px-4 py-2 rounded-full font-bold border border-[color:var(--line)] bg-red-600 text-white hover:opacity-90 transition">
      Cerrar sesión
    </a>
  <?php else: ?>
    <!-- inicia sesion pibe -->
    <a href="../index.php"
       class="ml-auto px-4 py-2 rounded-full font-bold border border-[color:var(--line)] bg-[color:var(--accent)] text-white hover:opacity-90 transition">
      Iniciar sesión
    </a>
  <?php endif; ?>
</header>
