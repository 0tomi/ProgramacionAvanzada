<?php
require_once 'includes/header.php';
require_once 'includes/barraLateral.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Datos simulados de usuario (mockup para front)
if (!isset($_SESSION["usuario"])) {
    $_SESSION["usuario"] = [
        "nombre" => "NombreEjemplo",
        "usuario" => "UsuarioEjemplo",
        "contrasena" => "123456", // visible/oculto con botón
        "descripcion" => "Introduce tu descripcion aca...",
        "imagen" => "uploads/default.png"
    ];
}

$usuario = $_SESSION["usuario"];

// Procesar cambios si se envía formulario
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

<main class="flex-grow-1 p-4" style="background:#0f1419; min-height:100vh;">
  <div class="perfil-container text-center p-4" style="background:#ffffff; border-radius:12px; max-width:500px; margin:auto; border:1px solid #22303c;">
      <h2 class="mb-3">Perfil del Usuario</h2>

      <!-- Imagen de perfil -->
      <img src="<?= $usuario["imagen"]; ?>" alt="Imagen de perfil" 
           style="width:120px; height:120px; border-radius:50%; object-fit:cover; border:2px solid #0b0c0eff;">

      <div class="mt-3"><strong>Nombre:</strong> <span><?= $usuario["nombre"]; ?></span></div>
      <div class="mt-2"><strong>Usuario:</strong> <span><?= $usuario["usuario"]; ?></span></div>

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

<script>
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
