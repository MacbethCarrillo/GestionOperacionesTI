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

// Obtener los reportes asignados al técnico logueado
$correo_tecnico = $_SESSION['correo'];
$sql_reportes = "SELECT * FROM reportesnuevos WHERE correo_tecnico = ?";
$stmt = $conexion->prepare($sql_reportes);
$stmt->bind_param("s", $correo_tecnico);
$stmt->execute();
$result_reportes = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes Asignados</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid black;
        }
        th, td {
            padding: 10px;
            text-align: center;
        }
        .mensaje {
            padding: 10px;
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <h1>Bienvenido Técnico, <?php echo $_SESSION['nombre']; ?>!</h1>
    <p><strong>Área:</strong> <?php echo $_SESSION['area']; ?></p>
    <p><strong>Correo:</strong> <?php echo $_SESSION['correo']; ?></p>
    <p>Este es el portal para los Técnicos.</p>
    <a href="logout.php">Cerrar sesión</a>

    <h2>Reportes Asignados</h2>

    <!-- Mostrar mensaje de éxito o error -->
    <?php
    if (isset($_SESSION['mensaje'])) {
        $mensaje_clase = (strpos($_SESSION['mensaje'], 'Error') === false) ? 'mensaje' : 'mensaje error';
        echo "<div class=\"$mensaje_clase\">" . $_SESSION['mensaje'] . "</div>";
        unset($_SESSION['mensaje']);  // Limpiar el mensaje después de mostrarlo
    }
    ?>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Descripción</th>
                <th>Aula</th>
                <th>Estado</th>
                <th>Fecha</th>
                <th>Comentario</th>
                <th>Prioridad</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result_reportes->num_rows > 0): ?>
                <?php while ($reporte = $result_reportes->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $reporte['id']; ?></td>
                        <td><?php echo $reporte['nombre']; ?></td>
                        <td><?php echo $reporte['descripcion']; ?></td>
                        <td><?php echo $reporte['edificio']; ?></td>
                        <td><?php echo $reporte['estado']; ?></td>
                        <td><?php echo $reporte['fecha_reporte']; ?></td>
                        <td>
                            <?php if ($reporte['estado'] == 'Resuelto'): ?>
                                <!-- Mostrar comentario deshabilitado si está resuelto -->
                                <textarea name="comentarios" disabled><?php echo $reporte['comentarios']; ?></textarea>
                            <?php else: ?>
                                <!-- Formulario para agregar comentario cuando está pendiente -->
                                <form action="procesar_reporte_tecnico.php" method="POST">
                                    <input type="hidden" name="id_reporte" value="<?php echo $reporte['id']; ?>">
                                    <textarea name="comentario" placeholder="Agregar comentario..." required></textarea>
                                    <button type="submit">Procesar</button>
                                </form>
                            <?php endif; ?>
                        </td>
                        <td><?php echo $reporte['prioridad']; ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8">No tienes reportes asignados.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>

<?php $conexion->close(); ?>
