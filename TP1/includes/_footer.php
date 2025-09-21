<?php
$year = date('Y');
?>
<footer class="site-footer">
  <style>
    .site-footer{
      background:linear-gradient(360deg, #10295eff -40%, #0c0f14 200px);;
      color:#fff;
      border-top:1px solid #22303c;
      width:100%;
      font-family: system-ui, -apple-system, Segoe UI, Roboto, Ubuntu, Cantarell, 'Helvetica Neue', Arial, sans-serif;
    }
    .ft-container{
      max-width: 1100px;
      margin: 0 auto;
      padding: 1.5rem 1rem;
    }
    .site-footer h5{
      margin: 0 0 .5rem 0;
      font-size: 1.125rem;
      color:#ffffff;
    }
    .site-footer p{ margin: 0 0 .5rem 0; }
    .muted{ color:#8899ac; }
    .ft-row{
      display:grid;
      gap: 1rem;
      grid-template-columns: 1fr;
      align-items:start;
    }
    .ft-col{ min-width: 0; }
    @media (min-width: 768px){
      .ft-row{
        grid-template-columns: 0.8fr 0.6fr 1fr;
        gap: 1rem;
      }
    }
    .ft-list{
      list-style: none;
      padding: 0;
      margin: 0;
    }
    .ft-list li{ margin: 0 0 .25rem 0; }
    .ft-divider{
      border: 0;
      border-top:1px solid #22303c;
      margin: 1rem 0;
    }
    .ft-bottom{
      display:flex;
      justify-content: space-between;
      align-items: center;
    }
    @media (min-width: 1024px){
      .ft-container{ padding: 2rem 1rem; }
    }
  </style>

  <div class="ft-container">
    <div class="ft-row">
      <div class="ft-col">
        <h5>Proyecto</h5>
        <p class="muted">Red social inspirada en X (Twitter). Feed, likes y comentarios.</p>
        <p class="muted">Login, validación de acceso, estructura organizada, cabecera y pie de página.</p>
      </div>

      <div class="ft-col">
        <h5>Cátedra</h5>
        <ul class="ft-list muted">
          <li>Programacion Avanzada</li>
          <li>FCyT UADER</li>
        </ul>
      </div>

      <div class="ft-col">
        <h5>Integrantes</h5>
        <p class="muted">
          Grupo del TP: Caminos Mariano, Famea Damián, Grigolato Facundo,
          Schlotahuer Tomás, Roldán Giorgi Tomás, Pettinato Valentino.
        </p>
      </div>
    </div>

    <hr class="ft-divider">
    <div class="ft-bottom">
      <small class="muted">&copy; <?php echo $year; ?> Ritual</small>
    </div>
  </div>
</footer>
