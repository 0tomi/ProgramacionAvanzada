<!DOCTYPE html>
<html lang="es">
<?php require_once 'includes/head.php';?>
<?php require_once 'includes/menu.php'; ?>
<br>

    <h4 class="text-center">Sistema Recursos Humanos</h4>
    <div class="d-flex justify-content-center">
      <form method="GET" action="/php/procesoFormulario.php" style="min-width:350px;max-width:600px;width:100%">
      <div class="form-row">
        <div class="col-md-6 mb-3">
          <label for="nombre">Nombre</label>
          <input type="text" class="form-control" id="nombre" name="nombre" value="" required>
          <div class="valid-feedback">Bien!</div>
          <div class="invalid-feedback">El campo Nombre no puede estar vacío.</div>
        </div>
        <div class="col-md-6 mb-3">
          <label for="apellido">Apellido</label>
          <input type="text" class="form-control" id="apellido" name="apellido" value="" required>
          <div class="valid-feedback">Bien!</div>
          <div class="invalid-feedback">El campo Apellido no puede estar vacío.</div>
        </div>
      </div>
      <div class="form-row">
        <div class="col-md-6 mb-3">
          <label for="validationCustom03">Fecha de Nacimiento</label>
          <input type="date" class="form-control" id="fechaNacimiento" name="fechaNacimiento" required>
          <div class="valid-feedback">Bien!</div>
          <div class="invalid-feedback">El campo  Nacimiento no puede estar vacío.</div>
        </div>
        <div class="col-md-6 mb-3">
          <label for="validationCustom04">Ciudad</label>
            <select class="custom-select" id="ciudad" name="ciudad" required>
              <option selected disabled value="">Seleccione...</option>
              <option value="Paraná">Paraná</option>
              <option value="Paraná">Oro Verde</option>
              <option value="Paraná">Diamante</option>
            </select>
          <div class="valid-feedback">Bien!</div>
          <div class="invalid-feedback">El campo Ciudad no puede estar vacío.</div>
        </div>
      </div>
      <button class="btn btn-primary" type="submit">Enviar datos</button>
      </form>
    </div>

<?php require_once 'includes/footer.php'; ?>
