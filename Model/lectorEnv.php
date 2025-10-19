<?php
/*
    Este lector lee automaticamente el .env
    Luego para usar las credenciales para autentificarse en la BD, solo hace falta invocar al arraysuperglobal
    $_ENV['lo que quieran sacar'].
    Los indices de env condicen con lo que hay antes del igual en credenciales.env, por ejemplo:
    $_ENV['DB_USER'] => admin
*/
$envPath = __DIR__ . '/credenciales.env';

if (!is_readable($envPath)) {
    throw new RuntimeException("No puedo leer $envPath");
}

$vars = parse_ini_file($envPath, false, INI_SCANNER_RAW);
if ($vars === false) {
    throw new RuntimeException("No pude parsear $envPath");
}

foreach ($vars as $k => $v) {
    $_ENV[$k] = $v;
    putenv("$k=$v");
}

