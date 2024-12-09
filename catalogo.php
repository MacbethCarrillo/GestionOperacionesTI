<?php
// Conexión a la base de datos
$conexion = new mysqli("localhost", "root", "", "mantenimiento_escuela");
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Obtener los datos del catálogo
$sql_catalogo = "SELECT * FROM catalogo_servicios";
$result_catalogo = $conexion->query($sql_catalogo);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catálogo de Servicios</title>
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
    </style>
</head>
<body>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Descripción</th>
                <th>Fecha Reporte</th>
                <th>Nombre del Hardware</th>
                <th>Prioridad</th>
                <th>Resolución</th>
                <th>Horas</th>
                <th>Fecha Resuelto</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result_catalogo->num_rows > 0): ?>
                <?php while ($fila = $result_catalogo->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $fila['id']; ?></td>
                        <td><?php echo $fila['descripcion']; ?></td>
                        <td><?php echo $fila['fecha_reporte']; ?></td>
                        <td><?php echo $fila['nombre_hardware']; ?></td>
                        <td><?php echo $fila['prioridad']; ?></td>
                        <td><?php echo $fila['resolucion']; ?></td>
                        <td><?php echo $fila['horas']; ?></td>
                        <td><?php echo $fila['fecha_resuelto']; ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8">No hay datos en el catálogo.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>

<?php $conexion->close(); ?>
