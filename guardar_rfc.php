<?php
// Conexión a la base de datos
$conexion = new mysqli("localhost", "root", "", "mantenimiento_escuela");
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Recibir los datos enviados desde el formulario
$hardware_nombre = $_POST['hardware_nombre'];
$descripcion = $_POST['descripcion'];
$componente = $_POST['componente'];
$precio = $_POST['precio'];

// Insertar los datos en la tabla `rfc`
$sql = "INSERT INTO rfc (hardware_nombre, descripcion, componente, precio, aprobado_rechazado, correo_tecnico) 
        VALUES (?, ?, ?, ?, 'Pendiente', 'jorge@mail.com')";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("sssd", $hardware_nombre, $descripcion, $componente, $precio);

if ($stmt->execute()) {
    echo "success";
} else {
    echo "Error al guardar los datos: " . $stmt->error;
}

$stmt->close();
$conexion->close();
?>
