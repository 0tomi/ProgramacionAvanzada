<?php



if ( $_SERVER["REQUEST_METHOD"] == "POST")
{
    $correo             = $_POST['correo'];
    $fecha_nacimiento   = $_POST['fecha_nacimiento'];
    $opciones           = $_POST['opciones'];
    $comentarios        = $_POST['comentarios'];

    echo "<h2>Datos recibidos:</h2>";
    echo "Correo: " . htmlspecialchars($correo) . "<br>";
    echo "Fecha de Nacimiento: " . htmlspecialchars($fecha_nacimiento) . "<br>";
    echo "Opción seleccionada: " . htmlspecialchars($opciones) . "<br>";
    echo "Comentarios: " . htmlspecialchars($comentarios) . "<br>";
}

/*
// METODO GET
print_r($_SERVER["REQUEST_METHOD"]);
echo "<hr>";

if ( $_SERVER["REQUEST_METHOD"] == "GET")
{
    $nombre = $_GET['nombre'];
    $apellido = $_GET['apellido'];
    $fechaNacimiento = $_GET['fechaNacimiento'];
    $ciudad = $_GET['ciudad'];

    echo "<h2>Datos recibidos:</h2>";
    echo "Nombre: " . htmlspecialchars($nombre) . "<br>";
    echo "Apellido: " . htmlspecialchars($apellido) . "<br>";
    echo "Fecha de Nacimiento: " . htmlspecialchars($fechaNacimiento) . "<br>";
    echo "Ciudad: " . htmlspecialchars($ciudad) . "<br>";
}
*/

/*
// METODO POST
echo "<hr>";
print_r($_SERVER["REQUEST_METHOD"]);
echo "<hr>";

if ( $_SERVER["REQUEST_METHOD"] == "POST")
{
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $fechaNacimiento = $_POST['fechaNacimiento'];
    $ciudad = $_POST['ciudad'];

    echo "<h2>Datos recibidos:</h2>";
    echo "Nombre: " . htmlspecialchars($nombre) . "<br>";
    echo "Apellido: " . htmlspecialchars($apellido) . "<br>";
    echo "Fecha de Nacimiento: " . htmlspecialchars($fechaNacimiento) . "<br>";
    echo "Ciudad: " . htmlspecialchars($ciudad) . "<br>";
}
*/

?>
