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

// Validar datos enviados desde el formulario
if (
    isset($_POST['nombre_hardware']) &&
    isset($_POST['descripcion']) &&
    isset($_POST['edificio'])
) {
    // Capturar los datos del formulario
    $nombre_hardware = $conexion->real_escape_string($_POST['nombre_hardware']);
    $descripcion = $conexion->real_escape_string($_POST['descripcion']);
    $edificio = $conexion->real_escape_string($_POST['edificio']);
    $correo_usuario = $_SESSION['correo'];

    // Forzar el correo del técnico
    $correo_tecnico = "especialistaSistemasOperativos@mail.com";

    $prioridad = 'Alta'; // Prioridad siempre alta
    $estado = 'En Revision'; // Estado inicial
    $fecha_creacion = date('Y-m-d');

    // Verificar el área del usuario logueado
    $query_usuario = "SELECT area FROM usuarios WHERE correo = ?";
    $stmt_usuario = $conexion->prepare($query_usuario);
    $stmt_usuario->bind_param("s", $correo_usuario);
    $stmt_usuario->execute();
    $resultado_usuario = $stmt_usuario->get_result();
    if ($resultado_usuario->num_rows === 0) {
        echo "Error: Usuario no encontrado.";
        exit();
    }
    $usuario = $resultado_usuario->fetch_assoc();
    $area_usuario = $usuario['area'];
    $stmt_usuario->close();

    // Insertar el problema en la tabla
    $query_insert = "INSERT INTO problemas (nombre_hardware, descripcion, edificio, prioridad, correo_tecnico, estado, fecha_creacion) 
                     VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt_insert = $conexion->prepare($query_insert);
    $stmt_insert->bind_param(
        "sssssss",
        $nombre_hardware,
        $descripcion,
        $edificio,
        $prioridad,
        $correo_tecnico,
        $estado,
        $fecha_creacion
    );

    if ($stmt_insert->execute()) {
        echo "Problema registrado con éxito.";
    } else {
        echo "Error al registrar el problema: " . $stmt_insert->error;
    }

    $stmt_insert->close();
} else {
    echo "Error: Datos incompletos en el formulario.";
}

$conexion->close();
?>
