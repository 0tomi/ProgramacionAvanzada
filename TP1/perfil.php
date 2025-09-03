<?php
if (!isset($_SESSION["usuario"])) {
    $_SESSION["usuario"] = [
        "nombre"      => "Facundo",
        "usuario"     => "facuperez",
        "contrasena"  => "123456",
        "descripcion" => "¡Hola! Soy nuevo en la red social.",
        "imagen"      => "imagenes/profilePictures/defaultProfilePicture.png"
    ];
}

$preruta = "";

$usuario = $_SESSION["usuario"];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST["descripcion"])) {
        $usuario["descripcion"] = htmlspecialchars($_POST["descripcion"], ENT_QUOTES, 'UTF-8');
    }
    if (!empty($_FILES["imagen"]["name"])) {
        $targetDir = __DIR__ . "/uploads/";
        if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
        $safeName   = preg_replace('/[^a-zA-Z0-9._-]/', '_', $_FILES["imagen"]["name"]);
        $targetFile = $targetDir . $safeName;
        if (move_uploaded_file($_FILES["imagen"]["tmp_name"], $targetFile)) {
            $usuario["imagen"] = "uploads/" . $safeName;
        }
    }
    $_SESSION["usuario"] = $usuario;
    header("Location: perfil.php?saved=1");
    exit;
}

$source = 'Perfil'; $require_boostrap = true;
require_once __DIR__ . "/includes/header.php";
require_once __DIR__ . "/includes/autentificacion.php";
$preruta = '';
require_once __DIR__ . "/includes/barraLateral/barraLateral.php";
require_once __DIR__ . "/Inicio/headerInicio.php";
?>

<main class="min-vh-100 d-flex align-items-center justify-content-center px-4 py-5"
      style="background:#0f1419;">
  <div class="w-100" style="max-width: 900px; background:#15202b; border:1px solid #22303c; border-radius:20px; padding:50px; box-shadow:0 8px 24px rgba(0,0,0,0.4);">

    <h1 class="text-center mb-5" style="color:#ffffff; font-size:2.2rem;">Perfil del Usuario</h1>

    <!-- Imagen -->
    <div class="text-center mb-4">
      <img src="<?= htmlspecialchars($usuario["imagen"]) ?>" alt="Imagen de perfil"
           style="width:200px;height:200px;object-fit:cover;border-radius:50%;
                  border:3px solid #1da1f2;">
    </div>

    <!-- Datos -->
    <div class="mb-5 text-center" style="font-size:1.2rem;">
      <p style="color:#8899ac;"><strong style="color:#ffffff;">Nombre:</strong> <?= htmlspecialchars($usuario["nombre"]) ?></p>
      <p style="color:#8899ac;"><strong style="color:#ffffff;">Usuario:</strong> <?= htmlspecialchars($usuario["usuario"]) ?></p>
      <p style="color:#8899ac;">
        <strong style="color:#ffffff;">Contraseña:</strong>
        <span id="contrasena" style="color:#ffffff;">******</span>
        <button type="button" class="btn btn-lg btn-primary ms-3" onclick="togglePassword()">Mostrar</button>
      </p>
    </div>

    <!-- Formulario -->
    <form method="POST" enctype="multipart/form-data" style="font-size:1.1rem;">
      <div class="mb-4">
        <label class="form-label" style="color:#ffffff; font-size:1.1rem;"><strong>Cambiar imagen</strong></label>
        <input type="file" name="imagen" accept="image/*" class="form-control form-control-lg">
      </div>

      <div class="mb-4">
        <label class="form-label" style="color:#ffffff; font-size:1.1rem;"><strong>Descripción</strong></label>
        <textarea name="descripcion" rows="4" class="form-control form-control-lg"
                  style="background:#0f1419; border:1px solid #22303c; color:#ffffff;"><?= htmlspecialchars($usuario["descripcion"]) ?></textarea>
      </div>

      <button type="submit" class="btn btn-lg w-100"
              style="background:#17a34a; color:#fff; font-weight:bold; font-size:1.2rem;">Guardar cambios</button>
    </form>

    <?php if (isset($_GET['saved'])): ?>
      <p class="text-center mt-4" style="color:#1da1f2; font-size:1.1rem;">Cambios guardados correctamente ✅</p>
    <?php endif; ?>
  </div>
</main>

<script>
  let visible = false;
  const contrasena = "<?= htmlspecialchars($usuario['contrasena'], ENT_QUOTES, 'UTF-8'); ?>";
  function togglePassword() {
    const span = document.getElementById("contrasena");
    if (visible) {
      span.textContent = "******";
      event.target.textContent = "Mostrar";
    } else {
      span.textContent = contrasena;
      event.target.textContent = "Ocultar";
    }
    visible = !visible;
  }
</script>

<?php require_once __DIR__ . "/includes/footer.php"; ?>
