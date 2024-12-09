<?php
// Conexión a la base de datos
$conexion = new mysqli("localhost", "root", "", "mantenimiento_escuela");
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Recibir los datos del formulario
$id = $_POST['id'];
$causa_raiz = $_POST['causa_raiz'];
$solucion = $_POST['solucion'];
$horas = $_POST['horas'];

// Obtener la fecha y hora actuales
$fecha_resuelto = date('Y-m-d H:i:s'); // Formato de fecha y hora: Año-Mes-Día Hora:Minuto:Segundo

// Actualizar los datos en la base de datos
$sql = "UPDATE problemas SET estado ='Resuelto', causa_raiz=?, solucion=?, horas=?, fecha_resuelto=? WHERE id=?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("ssisi", $causa_raiz, $solucion, $horas, $fecha_resuelto, $id);

if ($stmt->execute()) {
    echo 'success';
} else {
    echo 'error';
}

$stmt->close();
$conexion->close();
?>
