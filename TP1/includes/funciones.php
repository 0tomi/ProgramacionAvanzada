<?php
function leerUsuarios() {
    $file = "POSTS/users.json";
    if (!file_exists($file)) {
        file_put_contents($file, json_encode([]));
    }
    return json_decode(file_get_contents($file), true);
}

function guardarUsuarios($usuarios) {
    file_put_contents("POSTS/users.json", json_encode($usuarios, JSON_PRETTY_PRINT));
}
?>
