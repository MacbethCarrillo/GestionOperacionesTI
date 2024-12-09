<?php
include('conexion.php'); // Asegúrate de tener la conexión a la base de datos

// Consulta para obtener los proyectores
$query = "SELECT nombre, marca FROM hardware WHERE tipo = 'proyector'";
$result = mysqli_query($conexion, $query);
$proyectores = [];

while ($row = mysqli_fetch_assoc($result)) {
    $proyectores[] = $row;
}

// Devolver los datos en formato JSON
echo json_encode($proyectores);
?>
