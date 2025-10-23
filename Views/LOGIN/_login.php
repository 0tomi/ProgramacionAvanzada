<?php
$errorMsg = '';
if (isset($_GET['error']) && $_GET['error'] !== '') {
    $errorMsg = htmlspecialchars($_GET['error'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
$successMsg = '';
if (isset($_GET['success']) && $_GET['success'] !== '') {
    $successMsg = htmlspecialchars($_GET['success'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
?>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../CSS/login.css">

<body>
<div class="grid"> 
    <div class="leftRitual">
        <div class="RITUAL">
            <h1>RITUAL</h1>
            <h3>La red social de sistemas</h3>
            <p>Encontrá tu mundo, viví tu ritual</p>
            <p>El momento es tuyo.</p>
        </div>
    </div>

    <div class="rightAuth">
        <div class="Login">
            <h1>Iniciar Sesión</h1>
            <form id="formLogin" method="POST" action="../../Controlers/procesoLogin.php"> 
                <?php if ($errorMsg !== ''): ?>
                    <div class="login-banner login-error" data-autohide="5000"><?= $errorMsg; ?></div>
                <?php endif; ?>
                <?php if ($successMsg !== ''): ?>
                    <div class="login-banner login-success" data-autohide="3000"><?= $successMsg; ?></div>
                <?php endif; ?>

                <div class="nameUser">
                    <label for="username">Usuario</label>
                    <input type="text" id="userName" name="username" required placeholder="Tu usuario acá">
                </div>

                <div class="password">
                    <label for="password">Contraseña</label>
                    <input type="password" id="password" name="password" required placeholder="••••••••">
                </div>
                <div class="g-recaptcha" data-sitekey="6LdELdMrAAAAADXu2Q9TNUIdKA-9U6I9NV4wuJDm"></div>
                <div class="remember">
                    <!-- A futuro: <label><input type="checkbox" id="checkbox"/> Recordarme</label> -->
                    <!-- A futuro <a href="#" id="recovery">Olvidaste tu contraseña?</a> -->
                </div>

                <div class="botonLogin">
                    <button class="btn" type="submit">Iniciar sesión</button>
                </div>

                <div class="dividir">O crear cuenta</div>

                <div class="botonRegistrarse">
                    <a href="./_register.php" class="btn">Registrarse</a>
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
<?php require("../Views/_footer.php"); ?>
