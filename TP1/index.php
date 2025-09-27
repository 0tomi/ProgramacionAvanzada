<?php
session_start();
if (!empty($_SESSION['user'])) {
  header("Location: Inicio/inicio.php");
  exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>RITUAL</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700;800;900&display=swap" rel="stylesheet">
  <style>
    :root{
      --bg:#0c0f14; --grad1:#1b4aaf;
      --ink:#e8edf5; --muted:#8ea0b5;
      --cta:#2563eb; --cta2:#d7d9de; --dark:#000;
    }
    *{box-sizing:border-box}
    html,body{height:100%;margin:0;padding:0;}
    body{
      font-family:'Poppins',system-ui,Segoe UI,Roboto,Arial,sans-serif;
      color:var(--ink);
      background: linear-gradient(360deg, var(--grad1) -40%, var(--bg) 400px);
      display:flex;
      flex-direction:column;
      justify-content:space-between;
    }

    main{
      flex:1;
      display:flex;
      flex-direction:column;
      align-items:center;
      justify-content:center;
      text-align:center;
      padding:7rem;
    }

    h1.brand{
      font-weight:800;
      font-size:clamp(64px, 12vw, 180px);
      line-height:0.8;
      margin:0;
      letter-spacing:1px;
      padding-bottom:20px;
    }

    p.tag{
      font-size:clamp(18px, 2vw, 26px);
      margin:.5rem 0 0;
      font-weight:600;
      color:var(--ink);
    }

    p.sub{
      margin-top:.5rem;
      font-size:clamp(14px, 1.6vw, 15px);
      color:var(--ink);
      opacity:.9;
      font-weight:500;
      max-width: 250px;
    }

    .buttons{
      margin-top:1rem;
      width:100%;
      max-width:400px;
      display:grid;
      gap:14px;
    }

    a.btn{
      display:flex;
      align-items:center;
      justify-content:center;
      height:55px;
      border-radius:14px;
      font-weight:600;
      font-size:15px;
      text-decoration:none;
      transition:0.2s;
    }

    .btn-primary{
      background:linear-gradient(180deg,var(--cta) 0%,#000 350%);
      color:#fff;
    }
    .btn-primary:hover{filter:brightness(1.08);}

    .btn-light{
      background:var(--cta2);
      color:#15202b;
    }
    .btn-light:hover{filter:brightness(1.05);}

    .btn-dark{
      background:var(--dark);
      color:#fff;
    }
    .btn-dark:hover{filter:brightness(1.1);}

    
  </style>
</head>
<body>
  <main>
    <h1 class="brand">RITUAL</h1>
    <p class="tag">La red social de sistemas</p>
    <p class="sub">Encontrá tu mundo, viví tu ritual. El momento es tuyo.</p>

    <div class="buttons">
      <a href="LOGIN/_login.php" class="btn btn-primary">Iniciar sesión</a>
      <a href="LOGIN/_register.php" class="btn btn-light">Registrarse</a>
      <a href="Inicio/inicio.php" class="btn btn-dark">Continuar como invitado</a>
    </div>
  </main>

    <?php require("includes/_footer.php"); ?>

</body>
</html>
