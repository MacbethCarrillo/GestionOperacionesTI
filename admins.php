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

// Obtener reportes
$sql_reportes = "SELECT * FROM reportesnuevos";
$result_reportes = $conexion->query($sql_reportes);

// Obtener técnicos
$sql_tecnicos = "SELECT correo, nombre, area FROM usuarios WHERE rol = 'tecnico'";
$result_tecnicos = $conexion->query($sql_tecnicos);

// Crear un arreglo para los técnicos agrupados por área
$tecnicos_por_area = [];
if ($result_tecnicos->num_rows > 0) {
    while ($tecnico = $result_tecnicos->fetch_assoc()) {
        $tecnicos_por_area[$tecnico['area']][] = $tecnico;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Administrador</title>
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
    <h1>Bienvenido Administrador, <?php echo $_SESSION['nombre']; ?>!</h1>
    <p><strong>Área:</strong> <?php echo $_SESSION['area']; ?></p>
    <p><strong>Correo:</strong> <?php echo $_SESSION['correo']; ?></p>
    <p>Este es el portal para los administradores.</p>
    <a href="logout.php">Cerrar sesión</a>

    <h2>Reportes</h2>
<table>
    <thead>
        <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Descripción</th>
            <th>Estado</th>
            <th>Fecha</th>
            <th>Correo Reportante</th>
            <th>Área</th>
            <th>Edificio</th> <!-- Nueva columna -->
            <th>Prioridad</th>
            <th>Técnico</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($result_reportes->num_rows > 0): ?>
            <?php while ($reporte = $result_reportes->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $reporte['id']; ?></td>
                    <td><?php echo $reporte['nombre']; ?></td>
                    <td><?php echo $reporte['descripcion']; ?></td>
                    <td><?php echo $reporte['estado']; ?></td>
                    <td><?php echo $reporte['fecha_reporte']; ?></td>
                    <td><?php echo $reporte['correo']; ?></td>
                    <td><?php echo $reporte['area']; ?></td>
                    <td><?php echo $reporte['edificio']; ?></td> <!-- Mostrar el campo edificio -->
                    <td>
                        <form onsubmit="asignarPrioridad(event, <?php echo $reporte['id']; ?>)">
                            <select id="prioridad-<?php echo $reporte['id']; ?>" name="prioridad" required>
                                <option value="Baja" <?php echo $reporte['prioridad'] == 'Baja' ? 'selected' : ''; ?>>Baja</option>
                                <option value="Media" <?php echo $reporte['prioridad'] == 'Media' ? 'selected' : ''; ?>>Media</option>
                                <option value="Alta" <?php echo $reporte['prioridad'] == 'Alta' ? 'selected' : ''; ?>>Alta</option>
                            </select>
                            <button type="submit">Asignar</button>
                        </form>
                    </td>
                    <td>
                        <form onsubmit="asignarTecnico(event, <?php echo $reporte['id']; ?>)">
                            <select id="tecnico-<?php echo $reporte['id']; ?>" name="correo_tecnico" required>
                                <option value="">Seleccionar Técnico</option>
                                <?php
                                if (isset($tecnicos_por_area[$reporte['area']])) {
                                    foreach ($tecnicos_por_area[$reporte['area']] as $tecnico) {
                                        echo "<option value='{$tecnico['correo']}'>{$tecnico['nombre']} ({$tecnico['correo']})</option>";
                                    }
                                } else {
                                    echo "<option disabled>No hay técnicos en esta área</option>";
                                }
                                ?>
                            </select>
                            <button type="submit">Asignar</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="10">No hay reportes disponibles</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>
    <script>
    function asignarPrioridad(event, idReporte) {
        event.preventDefault(); // Prevenir que el formulario recargue la página

        // Obtener el valor de prioridad seleccionada
        const selectPrioridad = document.querySelector(`#prioridad-${idReporte}`);
        const prioridad = selectPrioridad.value;

        if (!prioridad) {
            alert("Por favor, selecciona una prioridad.");
            return;
        }

        // Crear la solicitud AJAX
        const xhr = new XMLHttpRequest();
        xhr.open("POST", "asignar_prioridad.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

        xhr.onload = function () {
            if (xhr.status === 200) {
                if (xhr.responseText === "success") {
                    // Actualizar la celda de prioridad en la tabla
                    alert("Prioridad asignada con éxito.");
                    location.reload(); // Refrescar la página
                } else {
                    alert("Error al asignar la prioridad: " + xhr.responseText);
                }
            }
        };

        // Enviar los datos al servidor
        xhr.send(`id_reporte=${idReporte}&prioridad=${prioridad}`);
    }
</script>
<script>
    function asignarTecnico(event, idReporte) {
        event.preventDefault(); // Prevenir que el formulario recargue la página

        // Obtener el valor del técnico seleccionado
        const selectTecnico = document.querySelector(`#tecnico-${idReporte}`);
        const correoTecnico = selectTecnico.value;

        if (!correoTecnico) {
            alert("Por favor, selecciona un técnico.");
            return;
        }

        // Crear la solicitud AJAX
        const xhr = new XMLHttpRequest();
        xhr.open("POST", "asignar_tecnico.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

        xhr.onload = function () {
            if (xhr.status === 200) {
                if (xhr.responseText.trim() === "success") {
                    // Actualizar la tabla o mostrar mensaje de éxito
                    alert("Técnico asignado con éxito.");
                    location.reload(); // Refrescar la página
                } else {
                    alert("Error al asignar técnico: " + xhr.responseText);
                }
            }
        };

        // Enviar los datos al servidor
        xhr.send(`id_reporte=${idReporte}&correo_tecnico=${correoTecnico}`);
    }
</script>

    
</body>
</html>
<?php $conexion->close(); ?>
