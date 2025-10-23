<?php
    $errorMsg = '';
    if (isset($_GET['error']) && $_GET['error'] !== '') {
        $errorMsg = htmlspecialchars($_GET['error'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Registro</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
  <link href="../CSS/login.css" rel="stylesheet">
</head>
<body>
   <div class="grid"> 
                <div class="leftRitual">
                    <div class="RITUAL"><h1>RITUAL</h1>
                    <h3>La red social de sistemas</h3>
                    <p>Encontrá tu mundo, viví tu ritual</p>
                    <p>El momento es tuyo.</p>
                </div>
                </div>
                <div class="rightAuth">
                    <div class="Login">
                        <h1>Crear cuenta</h1>
                        <form id="formRegister"action="../../Controlers/procesoRegister.php" id="registerForm" method="POST"> 
                            <?php if ($errorMsg !== ''): ?>
                                <div class="login-banner login-error" data-autohide="5000"><?= $errorMsg; ?></div>
                            <?php endif; ?>
                            <div class="nameuser">
                                <label for="username" >Usuario</label>
                                <input type="text" id="username" name="username" required autocomplete="username" placeholder="Tu usuario acá">
                            </div>
                            <div class="password">
                                <label for="password">Contraseña</label>
                                <input type="password" id="password" name="password" required autocomplete="current-password" placeholder="••••••••">
                            </div>
                            <div class="captcha">
                                <div class="g-recaptcha" data-sitekey="6LdELdMrAAAAADXu2Q9TNUIdKA-9U6I9NV4wuJDm"></div>
                            </div>
                            <div class="botonLogin">
                                <button class="btn" type="submit" >Registrarse</button>
                            </div>
                            <div class="dividir">¿Ya tenés cuenta?</div>
                            <div class="botonRegistrarse">
                                <a href="./_login.php" class="btn">Iniciar sesión</a>
                            </div>
                        </form>

                     </div>

                    </div>
            
            </div>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.login-banner').forEach(banner => {
                const delay = Number(banner.dataset.autohide || 3000);
                setTimeout(() => banner.classList.add('fade-out'), delay);
                banner.addEventListener('transitionend', () => banner.remove());
            });
        });
    </script>
</body>
</html>
<?php require("../Views/_footer.php"); ?>
