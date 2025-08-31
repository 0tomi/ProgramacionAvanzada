<?php
require_once 'includes/header.php';
require_once 'includes/barraLateral.php';
session_start();

if (!isset($_SESSION["usuario"])) {
    $_SESSION["usuario"] = [
          ];
}

$usuario = $_SESSION["usuario"];
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!empty($_POST["descripcion"])) {
        $usuario["descripcion"] = htmlspecialchars($_POST["descripcion"]);
    }
    if (!empty($_FILES["imagen"]["name"])) {
         $targetDir = "uploads/";
          if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
            }

    $targetFile = $targetDir . basename($_FILES["imagen"]["name"]);
    if (move_uploaded_file($_FILES["imagen"]["tmp_name"], $targetFile)) {
    $usuario["imagen"] = $targetFile;
      }
    }
 $_SESSION["usuario"] = $usuario;
}
?>

<main class="flex-grow-1 p-4">
  <div class="perfil-container text-center p-4" style="background:#192734; border-radius:12px; max-width:500px; margin:auto; border:1px solid #22303c;">
      <h2 class="mb-3">Perfil del Usuario</h2>

      <!-- Imagen de perfil -->
      <img src="<?= $usuario["imagen"]; ?>" alt="Imagen de perfil" style="width:120px; height:120px; border-radius:50%; object-fit:cover; border:2px solid #22303c;">

      <div class="mt-3"><strong>Nombre:</strong> <?= $usuario["nombre"]; ?></div>
      <div class="mt-2"><strong>Usuario:</strong> <?= $usuario["usuario"]; ?></div>

      <!-- Contraseña -->
      <div class="mt-2">
          <strong>Contraseña:</strong>
          <span id="contrasena">******</span>
          <button type="button" class="btn btn-sm btn-primary ms-2" onclick="togglePassword()">Mostrar</button>
      </div>

      <!-- Formulario -->
      <form method="POST" enctype="multipart/form-data" class="mt-4 text-start">
          <label class="form-label"><strong>Cambiar imagen:</strong></label>
          <input type="file" name="imagen" accept="image/*" class="form-control mb-3">

          <label class="form-label"><strong>Descripción:</strong></label>
          <textarea name="descripcion" class="form-control mb-3" rows="4"><?= $usuario["descripcion"]; ?></textarea>

          <button type="submit" class="btn btn-success w-100">Guardar cambios</button>
      </form>
  </div>
</main>

<script?
let visible = false;
const contrasena = "<?= $usuario['contrasena']; ?>";
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

<?php require_once 'includes/footer.php'; ?>