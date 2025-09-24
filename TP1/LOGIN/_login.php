<?php
require_once __DIR__ . '/../config.php';
?>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">


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
            <h1>Iniciar Sesión.</h1>
            <form id="formLogin" method="POST" action="../includes/procesoLogin.php"> 
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
                    <label><input type="checkbox" id="checkbox"/> Recordarme</label>
                    <a href="#" id="recovery">Olvidaste tu contraseña?</a>
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
</body>
<style>
:root{
  --bg:#0c0f14; --panel:#0f131a; --panel-2:#0b0f15;
  --ink:#e8edf5; --muted:#8ea0b5; --line:#1b2431;
  --accent:#4aa3ff; --like:#ff4d7a; --shadow:0 12px 36px rgb(0 0 0 / .28);  
}

html, body{
    background: rgb(255, 255, 255);
    height: 100%;
    margin:0;
    padding:0;
}
.grid{
    display:grid;
    grid-template-columns: minmax(0,1.5fr) minmax(0,1fr);
    min-height:100vh;
    font-family:'Poppins', sans-serif;
}
.grid > div{min-width:0}

.grid .leftRitual{
    display: flex;
    background: linear-gradient(360deg, #1b4aaf -40%, #0c0f14 400px);
    color:var(--ink);
}
.RITUAL{
    
    display: flex;
    flex-direction: column;
    text-align: left;
    justify-content: center;
    padding-left: 13%;
    width:min(720px, 90%);
    margin-left: 0%;
}
.RITUAL h1{
    font-size: clamp(70px, 12vw, 200px);
    margin:0 0 16px 0;
    line-height: 0.7;
    font-weight: 800;
}
.RITUAL h3, .RITUAL p{
    padding-left: 13px;
}
.RITUAL h3{
    font-size: 25px;
    line-height: 1;
}
.RITUAL p{
    font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
    font-size: 18px;
    margin:0;
    font-weight: 500;
}
.grid .rightAuth{
    display: flex;
    color:#4b5563;
    font-weight: 400;
    justify-content: center;
    align-items: center;
    margin:0;
}
.Login{
    position: relative;
    display: flex;
    flex-direction: column;
    width: 100%;
    max-width: 420px;
    margin:0;
    overflow: hidden;
    bottom:2%;
}
.Login h1{
    text-align: left;
    font-weight:bolder;
    padding-top: 15px;
}
.Login #formLogin{
    display: flex;
    flex-direction: column;
}
    input{
    background: #f9fafb;
    height: 45px;
    width: 100%;
    border: none;
    border-radius: 10px;
    padding-left: 20px;
    box-sizing: border-box;
}

    label{
    font-size: 14px;
    padding-bottom: 5px;
}
    label{
        font-family:system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif ;
        font-weight: 600;
    }
    input{
        font-family:system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif ;
        font-weight: 600;
    }
    input::placeholder{
    font-family:system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif ;
    color: #dbdee2;
    font-weight: 500;
}

.nameuser ,
.password{
    padding:10px 0px;
}
button {
  font: inherit;     
  -webkit-appearance: none;
  appearance: none;   
  border: none;
  cursor: pointer;
}
.btn{
    box-sizing: border-box;
    justify-content: center;
    background: linear-gradient(180deg, #2563eb 0%, #000000 350%);
    color:var(--ink);
    width: 100%;
    height: 55px;
    font-size: 14px;
    font-weight: 500;
    border-radius: 15px;
    margin-top:10px;
    text-decoration: none;
}
.btn:hover{
    color: #FFFFFF;
    background: linear-gradient(180deg, #1b4aaf 0%, #000000 300%);
}

.remember{
    display: flex;
    flex-direction: row;
    align-items: center;
    justify-content: space-between;
    
}
.remember label *{
    width: 18px;
    height: 18px;
}
.remember #recovery{
    font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
    color:#4fadf6;
    text-decoration: none;
    margin-left: 130px;
}
.remember #recovery:hover{
    color: #8acdff;
}
.g-recaptcha{
    margin: 5px 0px;
    margin-bottom: 10px;
}
.dividir {
  display: flex;
  align-items: center;
  text-align: center;
  color: #6b7280; 
  font-size: 14px;
  margin: 20px 0;
}
.dividir::before,
.dividir::after {
  content: "";
  flex: 1; 
  border-bottom: 1px solid #d1d5db; 
  margin: 0 10px; 
}

.botonRegistrarse a{
    background: black;
    color: #ffffff;
    margin-top: -5px;
    display: flex;
    justify-content: center;
    align-items: center;
}
.botonRegistrarse a:hover{
 background: #34455e;
}
.Login{
    transform: scale(0.97);
}

/* resoluciones angostas o sea chile afafafafa */
@media (max-width: 768px) {
  .grid {
    grid-template-columns: 1fr;
  }
  .leftRitual {
    justify-content: center;
    padding: 40px 20px;
    text-align: center;
  }
  .RITUAL {
    padding-left: 0;
    margin: 0 auto;
    width: 100%;
    align-items: center;
  }
  .RITUAL h1 {
    font-size: clamp(40px, 15vw, 90px);
    line-height: 1;
    text-align: center;
  }
  .RITUAL h3,
  .RITUAL p {
    padding-left: 0;
    text-align: center;
  }
  .rightAuth {
    margin: 0;
    padding: 20px;
    align-items: flex-start;
  }
  .Login {
    width: 100%;
    max-width: 100%;
    margin: 0;
    transform: none;
  }
  .btn {
    font-size: 16px;
    height: 50px;
  }
  .remember {
    flex-direction: column;
    align-items: flex-start;
    gap: 10px; 
  }
  .remember #recovery {
    margin-left: 0;
  }
  .dividir {
    font-size: 13px;
    margin: 15px 0;
  }
}
    </style>
<?php require("../includes/_footer.php"); ?>
