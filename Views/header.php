<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">


  <?php if (!empty($require_boostrap)): ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <?php endif; ?>

  <meta name="viewport" content="width=device-width, initial-scale=1" />

  <title><?= $source ?></title>

  <?php if ($source === 'Inicio'): ?>
    <link rel="stylesheet" href="<?= $preruta ?>Inicio/inicio.css">
  <?php elseif ($source === 'Perfil'): ?>
    <!-- Reusar estilos de layout (topbar, sidebar, etc.) en Perfil -->
    <link rel="stylesheet" href="<?= $preruta ?>Inicio/inicio.css">
    <!-- Estilos específicos de la vista de Perfil -->
    <link rel="stylesheet" href="<?= $preruta ?>Views/CSS/perfil.css">
    <!-- Slick CSS para carrusel de fotos -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css"/>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick-theme.css"/>
  <?php elseif ($source === 'Post'): ?>
    <link rel="stylesheet" href="<?= $preruta ?>Views/POSTS/styles.css">
  <?php endif; ?>

</head>
