<?php
include('conexion.php');

// Consulta para obtener las computadoras
$query = "SELECT nombre, marca,procesador, ram, sistema_operativo, disco FROM computadoras";
$result = mysqli_query($conexion, $query);

$computadoras = [];
while ($row = mysqli_fetch_assoc($result)) {
    $computadoras[] = $row;
}

// Devolver los datos en formato JSON
echo json_encode($computadoras);
?>
