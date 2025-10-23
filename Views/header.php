<!DOCTYPE html>

<?php
  $htmlClasses = ['h-full'];
  if ($require_boostrap) {
    $htmlClasses[] = 'bg-gray-100';
  } elseif (($source ?? '') !== 'Inicio') {
    $htmlClasses[] = 'bg-[#15202b]';
  }
?>

<html lang="es" class="<?= implode(' ', $htmlClasses) ?>">

<head>
  <meta charset="UTF-8">
  <script src="https://cdn.tailwindcss.com"></script>

  <?php if ($require_boostrap): ?>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <?php endif; ?>

  <meta name="viewport" content="width=device-width, initial-scale=1" />

  <title><?= $source ?></title>

  <?php if ($source === 'Inicio'): ?>
    <link rel="stylesheet" href="<?= $preruta ?>Inicio/inicio.css">
  <?php elseif ($source === 'Post'): ?>
    <link rel="stylesheet" href="<?= $preruta ?>Views/POSTS/styles.css">
  <?php endif; ?>

</head>

<?php if ($require_boostrap): ?>
  
<body class="min-h-screen bg-[#15202b]">
    <div class="min-h-full">

<?php endif; ?>
