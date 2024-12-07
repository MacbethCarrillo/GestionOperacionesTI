<?php
// Conexión a la base de datos
$conexion = new mysqli("localhost", "root", "", "mantenimiento_escuela");
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Recibir los datos enviados por el fetch
$id = $_POST['id'];
$estado = $_POST['estado'];

// Actualizar el estado en la tabla `rfc`
$sql = "UPDATE rfc SET aprobado_rechazado = ? WHERE id = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("si", $estado, $id);

if ($stmt->execute()) {
    echo "success";
} else {
    echo "Error al actualizar el estado: " . $stmt->error;
}

$stmt->close();
$conexion->close();
?>
