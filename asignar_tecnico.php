<?php
// Conexión a la base de datos
$conexion = new mysqli("localhost", "root", "", "mantenimiento_escuela");
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Verificar si se recibieron los datos
if (isset($_POST['id_reporte']) && isset($_POST['correo_tecnico'])) {
    $id_reporte = $_POST['id_reporte'];
    $correo_tecnico = $_POST['correo_tecnico'];

    // Actualizar el reporte con el correo del técnico asignado
    $sql = "UPDATE reportesnuevos SET correo_tecnico = ?, estado='En Proceso' WHERE id = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("si", $correo_tecnico, $id_reporte);

    if ($stmt->execute()) {
        echo "success"; // Respuesta exitosa
    } else {
        echo "Error al asignar técnico: " . $stmt->error;
    }

    $stmt->close();
} else {
    echo "Error: Parámetros faltantes.";
}

$conexion->close();
?>
