<?php

require_once '../Model/Profile.php';

function getProfileInfo() {
    $userId = $_SESSION['user']->getIdUsuario(); // Obtener ID de sesión
    $profile = new Profile($userId);              // Pasar ID al constructor
    return [
        'userTag' => $profile->getUserTag(),
        'descripcion' => $profile->getDescripcion()
    ];
}

?>