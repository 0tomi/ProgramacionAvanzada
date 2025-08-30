    <!-- boton para la barrita de la izquierda -->
<header> 
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
      rel="stylesheet"
      integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB"
      crossorigin="anonymous">

    <!-- esto trae iconos -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css"
          rel="stylesheet">
    <button class="btn btn-primary m-3" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarMenu"  style="background-color:#8899ac;">
    <i class="bi bi-list"></i> 
    </button>
</header>

<!-- OFFCANVAS (sidebar) -->
<div class="offcanvas offcanvas-start" tabindex="-1" id="sidebarMenu"
     style="--bs-offcanvas-width: 280px; background-color:#192734; color:#fff;">
  <div class="offcanvas-header d-flex align-items-center"
       style="background-color:#192734; color:#fff; border-bottom:1px solid #223142;">
    <h5 class="offcanvas-title m-0">Menú</h5>
    <button type="button" class="border-0 bg-transparent text-white fs-4 ms-auto"
            data-bs-dismiss="offcanvas" aria-label="Cerrar">
      <i class="bi bi-x-lg"></i>
    </button>
  </div>

  <div class="offcanvas-body d-flex flex-column p-0 h-100" style="background-color:#192734; color:#fff;">
    <!-- Perfil arriba -->
    <div class="p-3 text-center" style="border-bottom:1px solid #223142;">
      <img src="<?= $profilePictureSafe ?>" alt="Usuario" width="72" height="72" class="rounded-circle mb-2">
      <div class="fw-semibold"><?= $userNameSafe ?></div>
    </div>

    <!-- Menú medio -->
    <div class="px-3 py-3">
      <ul class="nav flex-column gap-2 mb-0">
        <li class="nav-item">
          <a href="#" class="nav-link d-flex align-items-center"
             style="color:#fff; background-color:#8899ac; border-radius:.5rem;">
            <i class="bi bi-house-door me-2"></i> Inicio
          </a>
        </li>
        <li>
          <a href="#" class="nav-link d-flex align-items-center" style="color:#fff;">
            <i class="bi bi-person-circle me-2"></i> Perfil
          </a>
        </li>
        <li>
          <a href="#" class="nav-link d-flex align-items-center" style="color:#fff;">
            <i class="bi bi-palette me-2"></i> Tema
          </a>
        </li>
      </ul>
    </div>

    <!-- el espaciador del final -->
    <div class="mt-auto"></div>

    <!-- boton inferior, cambia si estas logeado o no -->
    <div class="p-3" style="border-top:1px solid #223142;">
      <a href="<?= $ctaHrefSafe ?>" class="<?= $ctaStyle ?>"
         <?php if (!empty($ctaInline)): ?> style="<?= $ctaInline ?>" <?php endif; ?>>
        <i class="bi <?= $ctaIconSafe ?> me-2"></i> <?= $ctaTextSafe ?>
      </a>
    </div>
  </div>
</div>

