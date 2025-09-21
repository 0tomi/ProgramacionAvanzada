<?php
// podés dejar vacío el bloque PHP si no usás nada todavía
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Registro</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="./_register.css?v=1">
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
                        <h1>Crear cuenta.</h1>
                        <form id="formRegister"> 
                            <div class="nameUser" method="POST" action="../includes/procesoRegister.php">
                                <label for="userName" >Usuario</label>
                                <input type="text" id="userName" name="userName" required autocomplete="username" placeholder="Tu usuario acá">
                            </div>
                            <div class="passWord">
                                <label for="password">Contraseña</label>
                                <input type="password" id="password" name="password" required autocomplete="current-password" placeholder="••••••••">
                                <label for="password"> Repetir contraseña</label>
                                <input type="password" id="pass2" placeholder="••••••••">
                                
                            </div>
                            
                            
                            <div class="botonLogin">
                                <button class="btn" type="submit">Registrarse</button>
                            </div>
                            <div class="dividir">¿Ya tenés cuenta?</div>
                            <div class="botonRegistrarse">
                                <a href="./_login.php" class="btn">Iniciar sesión</a>
                            </div>
                        </form>

                     </div>

                    </div>
            
            </div>
</body>
</html>
<?php require("../includes/_footer.php"); ?>