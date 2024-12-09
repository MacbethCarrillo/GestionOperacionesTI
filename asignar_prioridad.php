<?php
// Conexión a la base de datos
$conexion = new mysqli("localhost", "root", "", "mantenimiento_escuela");
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Verificar si se recibieron los datos
if (isset($_POST['id_reporte']) && isset($_POST['prioridad'])) {
    $id_reporte = $_POST['id_reporte'];
    $prioridad = $_POST['prioridad'];

    // Actualizar la prioridad en la base de datos
    $sql = "UPDATE reportesnuevos SET prioridad = ? WHERE id = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("si", $prioridad, $id_reporte);

    if ($stmt->execute()) {
        echo "success"; // Respuesta exitosa
    } else {
        echo "error"; // Error al actualizar
    }

    $stmt->close();
} else {
    echo "error"; // Error por parámetros faltantes
}

$conexion->close();
?>
