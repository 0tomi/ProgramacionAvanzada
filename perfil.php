<?php
$preruta = '';
require_once __DIR__ . "/includes/autenticacion.php";
$source = 'Perfil'; $require_boostrap = true;
require_once __DIR__ . "/includes/header.php";
require_once __DIR__ . "/includes/Usuario.php";
?>

<header class="flex items-center justify-between px-6 py-4 border-b border-[color:var(--line)] bg-[color:var(--panel)]">
    <!-- mostrar logout papaa -->
    <a href="logout.php"
       class="ml-auto px-4 py-2 rounded-full font-bold border border-[color:var(--line)] bg-red-600 text-white hover:opacity-90 transition">
      Cerrar sesión
    </a>
</header>

<?php 
/*
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['description'] = $user['description'];
*/
?>

<main class="min-vh-100 d-flex align-items-center justify-content-center px-4 py-5"
      style="background:#0f1419;">
  <?php require_once __DIR__ . "/includes/barraLateral/barraLateral.php"; ?>
  <div class="w-100" style="max-width: 900px; background:#15202b; border:1px solid #22303c; border-radius:20px; padding:50px; box-shadow:0 8px 24px rgba(0,0,0,0.4);">

    <h1 class="text-center mb-5" style="color:#ffffff; font-size:2.4rem;">Perfil del Usuario</h1>

    <!-- Imagen -->
    <div class="text-center mb-4">
      <img src="<?= htmlspecialchars($_SESSION['user']->getProfilePhoto()) ?>" alt="Imagen de perfil"
           style="width:200px;height:200px;object-fit:cover;border-radius:50%;
                  border:3px solid #1da1f2; margin:auto;">
    </div>

    <!-- Datos -->
    <div class="mb-5 text-center" style="font-size:1.5rem;">
      <p style="color:#8899ac;"><strong style="color:#ffffff;">Nombre:</strong> <?= htmlspecialchars($_SESSION['user']->getNombre()) ?></p>
    </div>

    <!-- Formulario -->
    <form method="POST" enctype="multipart/form-data" style="font-size:1.1rem;">
      <div class="mb-4">
        <label class="form-label" style="color:#ffffff; font-size:1.1rem;"><strong>Cambiar imagen</strong></label>
        <input type="file" name="imagen" accept="image/*" class="form-control form-control-lg">
      </div>

      <div class="mb-4">
        <label class="form-label" style="color:#ffffff; font-size:1.1rem;"><strong>Descripción</strong></label>

        <textarea rows="4" 
        class="form-control form-control-lg"
        style="background:#0f1419; border:1px solid #22303c; color:#ffffff;"
        name="descripcion"><?= htmlspecialchars($_SESSION['description']); ?> </textarea>
      </div>

      <button type="submit" class="btn btn-lg w-100"
              style="background:#17a34a; color:#fff; font-weight:bold; font-size:1.2rem;">Guardar cambios</button>
    </form>

    <?php if (isset($_GET['saved'])): ?>
      <p class="text-center mt-4" style="color:#1da1f2; font-size:1.1rem;">Cambios guardados correctamente ✅</p>
    <?php endif; ?>
  </div>
</main>

<?php require_once __DIR__ . "/includes/footer.php"; ?>
