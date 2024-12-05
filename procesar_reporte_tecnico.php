<?php
session_start();
if (!isset($_SESSION['correo'])) {
    header("Location: login.php");
    exit();
}

// Conexión a la base de datos
$conexion = new mysqli("localhost", "root", "", "mantenimiento_escuela");
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Obtener los datos del formulario
$id_reporte = $_POST['id_reporte'];
$comentario = $_POST['comentario'];

// Obtener la fecha actual en formato 'Y-m-d' (año-mes-día)
$fecha_resuelto = date('Y-m-d');

// Actualizar el reporte en la base de datos (cambiar estado a "Resuelto", agregar comentario y fecha de resolución)
$sql_update = "UPDATE reportesnuevos SET estado = 'Resuelto', comentarios = ?, fecha_resuelto = ? WHERE id = ?";
$stmt = $conexion->prepare($sql_update);
$stmt->bind_param("ssi", $comentario, $fecha_resuelto, $id_reporte);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    // Si la actualización fue exitosa, redirigir a la página principal de reportes
    header("Location: tecnicos.php");
} else {
    // Si hubo algún error, mostrar mensaje de error
    echo "Error al procesar el reporte.";
}

$conexion->close();
?>
