<?php
/**
 * Componente reutilizable para el formulario de creación de posts.
 * Permite configurar el id del formulario, el contexto (data-context) y la
 * ruta relativa del avatar del usuario.
 *
 * Variables esperadas (todas opcionales):
 *  - string $composerAvatarSrc     Ruta hacia la imagen del avatar.
 *  - string $composerFormId        ID único del formulario (por defecto createPostForm).
 *  - string $composerDataContext   Identificador del contexto (ej: inicio, posts).
 *  - string $composerTextareaName  Nombre del campo textarea (por defecto text).
 *  - string $composerTextareaPlaceholder Texto del placeholder.
 *  - string $composerFileInputName Nombre del campo file (por defecto image).
 *  - bool   $composerAllowMultiple Habilita selección múltiple de imágenes.
 *  - string $composerAccept        Filtro de tipos permitidos (por defecto image/*).
 */

$avatarSrc = isset($composerAvatarSrc) ? (string)$composerAvatarSrc : '';
$formId = isset($composerFormId) && $composerFormId !== '' ? (string)$composerFormId : 'createPostForm';
$dataContext = isset($composerDataContext) ? (string)$composerDataContext : '';
$textareaName = isset($composerTextareaName) && $composerTextareaName !== '' ? (string)$composerTextareaName : 'text';
$textareaPlaceholder = isset($composerTextareaPlaceholder) && $composerTextareaPlaceholder !== ''
    ? (string)$composerTextareaPlaceholder
    : '¿Qué está pasando?';
$fileInputName = isset($composerFileInputName) && $composerFileInputName !== '' ? (string)$composerFileInputName : 'image';
$accept = isset($composerAccept) && $composerAccept !== '' ? (string)$composerAccept : 'image/*';
$allowMultiple = !empty($composerAllowMultiple);
$fileInputId = $formId . '_image';

unset(
    $composerAvatarSrc,
    $composerFormId,
    $composerDataContext,
    $composerTextareaName,
    $composerTextareaPlaceholder,
    $composerFileInputName,
    $composerAccept,
    $composerAllowMultiple
);
?>

<div class="composer" aria-label="Publicar">
  <?php if ($avatarSrc !== ''): ?>
    <img class="avatar" src="<?= htmlspecialchars($avatarSrc, ENT_QUOTES, 'UTF-8') ?>" alt="Tu avatar">
  <?php else: ?>
    <div class="avatar">U</div>
  <?php endif; ?>

  <form
    id="<?= htmlspecialchars($formId, ENT_QUOTES, 'UTF-8') ?>"
    class="compose js-create-post-form"
    action="javascript:void(0)"
    method="post"
    enctype="multipart/form-data"
    novalidate
    data-context="<?= htmlspecialchars($dataContext, ENT_QUOTES, 'UTF-8') ?>"
  >
    <div class="compose__input">
      <textarea
        name="<?= htmlspecialchars($textareaName, ENT_QUOTES, 'UTF-8') ?>"
        placeholder="<?= htmlspecialchars($textareaPlaceholder, ENT_QUOTES, 'UTF-8') ?>"
        maxlength="280"
        required
        aria-label="Contenido del post"
      ></textarea>

      <div class="compose__previews" hidden aria-live="polite"></div>
    </div>

    <div class="row">
      <input
        type="file"
        id="<?= htmlspecialchars($fileInputId, ENT_QUOTES, 'UTF-8') ?>"
        name="<?= htmlspecialchars($fileInputName, ENT_QUOTES, 'UTF-8') ?>"
        accept="<?= htmlspecialchars($accept, ENT_QUOTES, 'UTF-8') ?>"
        <?= $allowMultiple ? 'multiple' : '' ?>
        hidden
      >
      <label for="<?= htmlspecialchars($fileInputId, ENT_QUOTES, 'UTF-8') ?>" class="btn ghost">Agrega una imagen</label>
      <button class="btn primary" type="submit">Publicar</button>
    </div>
  </form>
</div>
