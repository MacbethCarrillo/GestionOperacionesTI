<?php
$servidor = "localhost";
$usuario = "root";
$contrasena = "";
$base_datos = "mantenimiento_escuela";

$conexion = new mysqli($servidor, $usuario, $contrasena, $base_datos);

if ($conexion->connect_error) {
    die("Error en la conexión: " . $conexion->connect_error);
}
?>
