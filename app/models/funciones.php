<?php

// Archivo donde se almacenan los usuarios registrados.  Se utiliza la
// constante __DIR__ para construir la ruta absoluta y evitar problemas de
// directorio de trabajo al incluir este archivo desde distintos scripts.
const USERS_FILE = dirname(__DIR__, 2) . '/storage/data/users.json';

/**
 * Lee la lista de usuarios desde el archivo JSON.
 *
 * @return array Lista de usuarios.
 */
function leerUsuarios() {
    if (!file_exists(USERS_FILE)) {
        file_put_contents(USERS_FILE, json_encode([]));
    }
    return json_decode(file_get_contents(USERS_FILE), true);
}

/**
 * Guarda la lista de usuarios en el archivo JSON.
 *
 * @param array $usuarios Lista de usuarios a guardar.
 */
function guardarUsuarios($usuarios) {
    file_put_contents(USERS_FILE, json_encode($usuarios, JSON_PRETTY_PRINT));
}

?>
