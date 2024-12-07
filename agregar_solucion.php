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
$solucion = $_POST['solucion'];

// Validar que los datos no estén vacíos
if (empty($id_reporte) || empty($solucion)) {
    echo "Por favor, complete todos los campos.";
    exit();
}

// Actualizar la columna `solucion` en la tabla `reportesnuevos`
$sql_update = "UPDATE reportesnuevos SET solucion = ? WHERE id = ?";
$stmt = $conexion->prepare($sql_update);
$stmt->bind_param("si", $solucion, $id_reporte);
$stmt->execute();

if ($stmt->affected_rows > 0) {
    // Si la actualización fue exitosa, redirigir a la página principal de reportes
    header("Location: tecnicos.php");
} else {
    // Si no se actualizó, mostrar un mensaje de error
    echo "Error al agregar la solución. Por favor, intente nuevamente.";
}

// Cerrar conexión
$stmt->close();
$conexion->close();
?>
