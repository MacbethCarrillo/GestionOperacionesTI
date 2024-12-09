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
$id_reporte = $_POST['id_reporte']; // ID del reporte a calificar
$calificacion = $_POST['calificacion']; // Calificación proporcionada por el usuario

// Valida que la calificación esté en el rango correcto (1 a 5)
if (!is_numeric($calificacion) || $calificacion < 1 || $calificacion > 5) {
    die("La calificación debe ser un número entre 1 y 5.");
}

// Actualiza la tabla `reportesnuevos` con la calificación
$sql = "UPDATE reportesnuevos SET calificacion = ? WHERE id = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("di", $calificacion, $id_reporte); // "d" para calificación (double) y "i" para id_reporte (integer)

if ($stmt->execute()) {
    echo "Calificación guardada exitosamente.";
} else {
    echo "Error al guardar la calificación: " . $stmt->error;
}

// Cierra la conexión
$stmt->close();
$conexion->close();
?>
