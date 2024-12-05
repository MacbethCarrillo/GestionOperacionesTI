<?php
// Inicia sesión
session_start();

// Verifica que el usuario esté logeado
if (!isset($_SESSION['correo'])) {
    die("Acceso denegado. Por favor, inicia sesión.");
}

// Conexión a la base de datos
$conexion = new mysqli("localhost", "root", "", "mantenimiento_escuela");

// Verifica la conexión
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Obtiene los datos del formulario
$hardware = $_POST['hardware']; // Nombre del hardware o componente
$edificio = $_POST['edificio']; // Edificio o aula
$descripcion = $_POST['descripcion']; // Descripción del problema
$estado = "Pendiente"; // Estado inicial del reporte
$fecha_reporte = date("Y-m-d"); // Fecha actual
$correo = $_SESSION['correo']; // Correo del usuario logeado

// Obtiene el área del usuario desde la tabla `usuarios`
$sql_area = "SELECT area FROM usuarios WHERE correo = ?";
$stmt_area = $conexion->prepare($sql_area);
$stmt_area->bind_param("s", $correo);
$stmt_area->execute();
$stmt_area->bind_result($area);
$stmt_area->fetch();
$stmt_area->close();

// Valida que el área fue recuperada
if (!$area) {
    die("Error: No se pudo obtener el área del usuario logeado.");
}

// Inserta los datos en la tabla `reportesnuevos`
$sql = "INSERT INTO reportesnuevos (nombre, edificio, descripcion, estado, fecha_reporte, correo, area) 
        VALUES (?, ?, ?, ?, ?, ?, ?)";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("sssssss", $hardware, $edificio, $descripcion, $estado, $fecha_reporte, $correo, $area);

if ($stmt->execute()) {
    echo "Reporte enviado exitosamente.";
} else {
    echo "Error al enviar el reporte: " . $stmt->error;
}

// Cierra la conexión
$stmt->close();
$conexion->close();
?>
