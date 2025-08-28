<!DOCTYPE html>
<html lang="es">
<?php require_once 'includes/head.php';?>
<?php require_once 'includes/menu.php'; ?>
<?php require_once 'php/datosTabla.php'; ?>

<br>

    <h4 class="text-center">Listado de Personas</h4>
    <div class="d-flex justify-content-center">
    
    <table class="table">
        <thead>
            <tr>
            <th scope="col">#</th>
            <th scope="col">Documento</th>
            <th scope="col">Nombre</th>
            <th scope="col">Apellido</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <th scope="row">1</th>
                <td>22333444</td>
                <td>Exeuiel</td>
                <td>Aramburu</td>
            </tr>
            <tr>
            <th scope="row">2</th>
            <td>23232332</td>
            <td>Federico</td>
            <td>Bonnet</td>
            </tr>
            <?php echo $filasTabla; ?>
        </tbody>
    </table>

    <?php require_once 'includes/footer.php'; ?>
