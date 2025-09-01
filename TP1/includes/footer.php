<?php
// includes/footer.php
$year = date('Y');
?>
<footer class="mt-auto" style="background:#192734; color:#fff; border-top:1px solid #22303c;">
  <div class="container py-4">
    <div class="row g-4">
      <div class="col-12 col-md-4">
        <h5 class="mb-2" style="color:#ffffff;">Proyecto</h5>
        <p class="mb-2" style="color:#8899ac;">
          Red social inspirada en X (Twitter). Feed, likes y comentarios.
        </p>
        <a href="/index.php" class="text-decoration-none" style="color:#1da1f2;">Inicio</a>
        <span class="mx-2" style="color:#8899ac;">·</span>
        <a href="/POSTS/index.php" class="text-decoration-none" style="color:#1da1f2;">Posts</a>
        <span class="mx-2" style="color:#8899ac;">·</span>
        <a href="/perfil.php" class="text-decoration-none" style="color:#1da1f2;">Perfil</a>
      </div>

      <div class="col-12 col-md-4">
        <h5 class="mb-2" style="color:#ffffff;">Contacto</h5>
        <ul class="list-unstyled mb-0" style="color:#8899ac;">
          <li><a href="mailto:grupo@example.com" class="text-decoration-none" style="color:#8899ac;">grupo@example.com</a></li>
          <li>FCyT UADER — Programacion Avanzada</li>
        </ul>
      </div>

      <div class="col-12 col-md-4">
        <h5 class="mb-2" style="color:#ffffff;">Quienes somos</h5>
        <p class="mb-2" style="color:#8899ac;">Equipo del TP: Caminos Mariano, Famea damian, Grigolato Facundo, Schlotahuer Tomas, giorgi tomas, valentin pettinato .</p>
        <p class="mb-0" style="color:#8899ac;">Este proyecto explora autenticacion, sesiones, y un mini feed con likes y comentarios.</p>
      </div>
    </div>

    <hr style="border-color:#22303c;">
    <div class="d-flex justify-content-between align-items-center">
      <small style="color:#8899ac;">&copy; <?= $year ?> X</small>
    </div>
  </div>
</footer>
