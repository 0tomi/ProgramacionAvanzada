<header class="inicio-topbar d-flex align-items-center justify-content-between">
  <?php if ($isLoggedIn): ?>
    <!-- mostrar logout papaa -->
    <a href="../logout.php"
       class="btn inicio-topbar__cta inicio-topbar__cta--logout">
      Cerrar sesión
    </a>
  <?php else: ?>
    <!-- inicia sesion pibe -->
    <a href="../index.php"
       class="btn btn-primary fw-bold rounded-pill border inicio-topbar__cta">
      Iniciar sesión
    </a>
  <?php endif; ?>
</header>
