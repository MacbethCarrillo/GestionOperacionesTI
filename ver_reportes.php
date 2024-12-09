<?php
// Inicia sesión
session_start();

// Verifica que el usuario esté logeado
if (!isset($_SESSION['correo'])) {
    die("Acceso denegado. Por favor, inicia sesión.");
}

// Conexión a la base de datos
$conexion = new mysqli("localhost", "root", "", "mantenimiento_escuela");

// Verifica la conexión
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Obtén el correo del maestro desde la sesión
$correo_maestro = $_SESSION['correo'];

// Consulta SQL para obtener los reportes del maestro por su correo
$query = "SELECT nombre, descripcion, fecha_reporte, correo, prioridad, comentarios, fecha_resuelto, calificacion FROM reportesnuevos WHERE correo = ? AND rol = 'Maestro'";
$stmt = $conexion->prepare($query);
$stmt->bind_param("s", $correo_maestro); // "s" indica que es un parámetro de tipo string
$stmt->execute();
$resultados = $stmt->get_result();

// Verifica si hay reportes
if ($resultados->num_rows > 0) {
    // Muestra los resultados en la tabla HTML
    echo '<h2>Reportes</h2>';
    echo '<table border="1">';
    echo '<thead>
            <tr>
                <th>Nombre</th>
                <th>Descripción</th>
                <th>Fecha Reporte</th>
                <th>Correo Técnico</th>
                <th>Prioridad</th>
                <th>Comentarios</th>
                <th>Fecha Resuelto</th>
                <th>Calificación</th>
            </tr>
          </thead>';
    echo '<tbody>';
    
    // Itera sobre los resultados y los muestra en la tabla
    while ($row = $resultados->fetch_assoc()) {
        echo '<tr>';
        echo '<td>' . htmlspecialchars($row['nombre']) . '</td>';
        echo '<td>' . htmlspecialchars($row['descripcion']) . '</td>';
        echo '<td>' . htmlspecialchars($row['fecha_reporte']) . '</td>';
        echo '<td>' . htmlspecialchars($row['correo']) . '</td>';
        echo '<td>' . htmlspecialchars($row['prioridad']) . '</td>';
        echo '<td>' . htmlspecialchars($row['comentarios']) . '</td>';
        echo '<td>' . htmlspecialchars($row['fecha_resuelto']) . '</td>';
        echo '<td>' . htmlspecialchars($row['calificacion']) . '</td>';
        echo '</tr>';
    }
    
    echo '</tbody>';
    echo '</table>';
} else {
    echo '<p>No hay reportes disponibles.</p>';
}

// Cierra la conexión
$stmt->close();
$conexion->close();
?>
