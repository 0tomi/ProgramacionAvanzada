<?php
$envPath = dirname(__DIR__) . '/credenciales.env';
if (!is_readable($envPath)) { return; }

$vars = parse_ini_file($envPath, false, INI_SCANNER_RAW); // respeta lo escrito
if ($vars === false) { return; }

foreach ($vars as $k => $v) {
    $_ENV[$k] = $v;
    putenv("$k=$v"); 
}
