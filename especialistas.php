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
// Obtener los reportes asignados al técnico logueado y ordenarlos por la fecha más reciente primero
$sql_reportes = "SELECT * FROM reportesnuevos WHERE correo_tecnico = ? ORDER BY fecha_reporte DESC";
$stmt = $conexion->prepare($sql_reportes);
$stmt->bind_param("s", $correo_tecnico);
$stmt->execute();
$result_reportes = $stmt->get_result();

// Verificar si hay RFC con estado 'Aprobado' o 'Rechazado'
$sqlValidarRFC = "SELECT COUNT(*) AS total FROM rfc WHERE aprobado_rechazado IN ('Aprobado', 'Rechazado')";
$resultValidarRFC = $conexion->query($sqlValidarRFC);
$totalRFC = $resultValidarRFC->fetch_assoc()['total'];

$correo_tecnico = $_SESSION['correo'];
$query = "SELECT * FROM problemas WHERE correo_tecnico = ? AND estado = 'En Revision'";
$stmt = $conexion->prepare($query);
$stmt->bind_param("s", $correo_tecnico);
$stmt->execute();
$resultado = $stmt->get_result();
$problemas = $resultado->fetch_all(MYSQLI_ASSOC);
$stmt->close();
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
        body {
    font-family: Arial, sans-serif;
    background-color: #f4f4f4;
    margin: 0;
    padding: 0;
}
.btn-rojo {
    display: inline-block;
    padding: 10px 20px;
    background-color: red; /* Color rojo */
    color: white; /* Texto blanco */
    text-align: center;
    text-decoration: none;
    font-weight: bold;
    border-radius: 5px; /* Bordes redondeados */
    transition: background-color 0.3s ease;
}

.btn-rojo:hover {
    background-color: darkred; /* Cambia a un tono más oscuro de rojo cuando se pasa el ratón */
}


h1 {
    text-align: center;
    color: #333;
    margin-top: 20px;
}

p {
    font-size: 16px;
    color: #555;
}

a {
    color: #1e90ff;
    text-decoration: none;
    font-size: 16px;
}

a:hover {
    text-decoration: underline;
}

form {
    max-width: 600px;
    margin: 20px auto;
    background-color: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

form label {
    font-weight: bold;
    margin-bottom: 5px;
    display: block;
}

form select, form textarea {
    width: 100%;
    padding: 10px;
    margin-bottom: 15px;
    border: 1px solid #ddd;
    border-radius: 5px;
    box-sizing: border-box;
}

form button {
    padding: 10px 20px;
    background-color: #4CAF50;
    color: white;
    border: none;
    cursor: pointer;
    border-radius: 5px;
    font-size: 16px;
}

form button:hover {
    background-color: #45a049;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
    background-color: #fff;
    border-radius: 8px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

th, td {
    padding: 10px;
    text-align: center;
    border: 1px solid #ddd;
}

th {
    background-color: #f2f2f2;
}

button {
    padding: 10px 20px;
    background-color: #4CAF50;
    color: white;
    border: none;
    cursor: pointer;
    border-radius: 5px;
    font-size: 16px;
}

button:hover {
    background-color: #45a049;
}

.hidden {
    display: none;
}

textarea {
    resize: none;
}

@media (max-width: 768px) {
    h1 {
        font-size: 24px;
    }

    form {
        width: 90%;
        padding: 15px;
    }

    table {
        font-size: 14px;
    }
}

@media (max-width: 480px) {
    h1 {
        font-size: 20px;
    }

    form {
        padding: 10px;
    }

    table {
        font-size: 12px;
    }

    button {
        font-size: 14px;
    }
}

    </style>
</head>
<body>
    <h1>Bienvenido Especialista en Sistemas Operativos</h1>
    <h1>Este es el portal para los Especialistas.</h1>
    <p><strong>Área:</strong> <?php echo $_SESSION['area']; ?></p>
    <p><strong>Correo:</strong> <?php echo $_SESSION['correo']; ?></p>
    <a href="logout.php" class="btn-rojo">Cerrar sesión</a>
    
    <?php if (count($problemas) > 0): ?>
    <p style="color: green; cursor: pointer;" id="openModalProblemas">
        Tienes un Reporte de Problema por revisar
    </p>
<?php else: ?>
    <p>No tienes reportes pendientes por revisar.</p>
<?php endif; ?>


        <h2>Gestion de Problemas</h2>
        <table border="1">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre del Hardware</th>
                    <th>Descripción</th>
                    <th>Edificio</th>
                    <th>Prioridad</th>
                    <th>Causa Raíz</th>
                    <th>Solución</th>
                    <th>Horas</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($problemas as $problema): ?>
                    <tr>
                        <td><?php echo $problema['id']; ?></td>
                        <td><?php echo $problema['nombre_hardware']; ?></td>
                        <td><?php echo $problema['descripcion']; ?></td>
                        <td><?php echo $problema['edificio']; ?></td>
                        <td><?php echo $problema['prioridad']; ?></td>
                        <td>
                            <textarea id="causa-<?php echo $problema['id']; ?>"><?php echo $problema['causa_raiz']; ?></textarea>
                        </td>
                        <td>
                            <textarea id="solucion-<?php echo $problema['id']; ?>"><?php echo $problema['solucion']; ?></textarea>
                        </td>
                        <td>
    <textarea id="horas-<?php echo $problema['id']; ?>" placeholder="Ingrese las horas"></textarea>
</td>

                        <td>
                            <button onclick="guardarDatos(<?php echo $problema['id']; ?>)">Guardar</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>

    // Función para guardar causa raíz, solución y horas
function guardarDatos(id) {
    const causaRaiz = document.getElementById(`causa-${id}`).value;
    const solucion = document.getElementById(`solucion-${id}`).value;
    const horas = document.getElementById(`horas-${id}`).value; // Obtener el valor de horas

    fetch('guardar_datos.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `id=${id}&causa_raiz=${encodeURIComponent(causaRaiz)}&solucion=${encodeURIComponent(solucion)}&horas=${encodeURIComponent(horas)}`, // Asegúrate de incluir las horas en el cuerpo de la solicitud
    })
    .then(response => response.text())
    .then(data => {
        if (data === 'success') {
            alert('Datos guardados con éxito.');
        } else {
            alert('Error al guardar los datos: ' + data);
        }
    })
    .catch(error => {
        console.error('Error:', error);
    });
}

</script>


    
</body>
</html>

<?php $conexion->close(); ?>
