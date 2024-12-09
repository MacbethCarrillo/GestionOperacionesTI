<?php
// Conexión a la base de datos
$conexion = new mysqli("localhost", "root", "", "mantenimiento_escuela");
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Consulta para obtener los problemas finalizados
$sql = "SELECT id, nombre_hardware, descripcion, edificio, prioridad, correo_tecnico, causa_raiz, estado, fecha_creacion, solucion, horas, fecha_resuelto FROM problemas WHERE estado = 'Resuelto'";
$result = $conexion->query($sql);

$problemas = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $problemas[] = $row;
    }
}

echo json_encode($problemas);

$conexion->close();
?>
