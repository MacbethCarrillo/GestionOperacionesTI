<?php
include('conexion.php'); // Asegúrate de tener la conexión a la base de datos

// Consulta para obtener las impresoras
$query = "SELECT nombre, marca FROM hardware WHERE tipo = 'impresora'";
$result = mysqli_query($conexion, $query);
$impresoras = [];

while ($row = mysqli_fetch_assoc($result)) {
    $impresoras[] = $row;
}

// Devolver los datos en formato JSON
echo json_encode($impresoras);
?>
