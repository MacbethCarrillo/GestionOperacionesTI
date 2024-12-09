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
    <h1>Bienvenido Técnico, <?php echo $_SESSION['nombre']; ?>!</h1>
    <h1>Este es el portal para los Técnicos.</h1>
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

<!-- Modal (nuevo nombre modalReportes) -->
<div id="modalReportes" class="modal" style="display: none;">
    <div class="modal-content">
        <span class="close">&times;</span>
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

<!-- Modal para el catálogo (añadido nuevo modal) -->
<div id="modalCatalogo" class="modal" style="display: none;">
    <div class="modal-content">
        <span class="closeCatalogo">&times;</span>
        <h2>Catálogo de Servicios</h2>
        <iframe src="catalogo.php" width="100%" height="500px" frameborder="0"></iframe>
    </div>
</div>

<!-- Botón para abrir el modal de Catálogo -->
<button id="openModalCatalogo">Ver Catálogo de Servicios</button>

<script>
    // Modal de Problemas
    const modalReportes = document.getElementById("modalReportes");
    const openModalProblemas = document.getElementById("openModalProblemas");
    const closeModalReportes = document.querySelector(".close");

    openModalProblemas.onclick = () => modalReportes.style.display = "block";
    closeModalReportes.onclick = () => modalReportes.style.display = "none";

    // Modal de Catálogo
    const modalCatalogo = document.getElementById("modalCatalogo");
    const openModalCatalogo = document.getElementById("openModalCatalogo");
    const closeModalCatalogo = document.querySelector(".closeCatalogo");

    openModalCatalogo.onclick = () => modalCatalogo.style.display = "block";
    closeModalCatalogo.onclick = () => modalCatalogo.style.display = "none";

    window.onclick = (event) => {
        // Cerrar el modal de Reportes de Problema si se hace clic fuera
        if (event.target == modalReportes) {
            modalReportes.style.display = "none";
        }
        // Cerrar el modal de Catálogo si se hace clic fuera
        if (event.target == modalCatalogo) {
            modalCatalogo.style.display = "none";
        }
    };

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
        /* Estilo general para los modales */
.modal {
    display: none;  /* Inicialmente oculto */
    position: fixed; /* Posiciona el modal respecto a la ventana del navegador */
    top: 50%; /* Lo coloca verticalmente en el centro de la pantalla */
    left: 50%; /* Lo coloca horizontalmente en el centro de la pantalla */
    transform: translate(-50%, -50%); /* Ajusta el modal para que esté completamente centrado */
    width: 80%; /* Ajusta el tamaño del modal (puedes cambiarlo según el tamaño que desees) */
    max-width: 900px; /* Tamaño máximo del modal */
    background-color: white;
    padding: 20px;
    z-index: 1000; /* Asegura que el modal esté encima de otros elementos */
    border-radius: 10px; /* Bordes redondeados */
    box-shadow: 0 0 15px rgba(0, 0, 0, 0.3); /* Sombra alrededor del modal */
}

/* Estilo para el contenido del modal */
.modal-content {
    overflow-y: auto; /* Permite desplazarse por el contenido si es muy largo */
}

/* Estilo para el botón de cerrar */
.close {
    color: #aaa;
    font-size: 28px;
    font-weight: bold;
    position: absolute;
    top: 10px;
    right: 15px;
    cursor: pointer;
}

/* Estilo para el botón de cerrar del modal del catálogo */
.closeCatalogo {
    color: #aaa;
    font-size: 28px;
    font-weight: bold;
    position: absolute;
    top: 10px;
    right: 15px;
    cursor: pointer;
}

        
    </style>

      

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
                <th>Diagnostico</th>
                <th>Solucion</th>
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
                            <td>
    <?php if (!empty($reporte['solucion'])): ?>
        <!-- Mostrar solución existente en un textarea deshabilitado -->
        <textarea disabled><?php echo htmlspecialchars($reporte['solucion']); ?></textarea>
    <?php else: ?>
        <!-- Formulario para agregar solución -->
        <form action="agregar_solucion.php" method="POST">
            <input type="hidden" name="id_reporte" value="<?php echo $reporte['id']; ?>">
            <textarea name="solucion" placeholder="Agregar solución..." required></textarea>
            <button type="submit">Guardar Solución</button>
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


    <style>
        /* Estilos básicos */
        .rfc-table {
            margin-top: 20px;
            border-collapse: collapse;
            width: 100%;
        }
        .rfc-table th, .rfc-table td {
            border: 1px solid #ddd;
            padding: 8px;
        }
        .rfc-table th {
            background-color: #f4f4f4;
            text-align: left;
        }
        .hidden {
            display: none;
        }
    </style>

<button id="rfcButton">RFC</button>

<!-- Tabla oculta inicialmente -->
<div id="rfcContainer" class="hidden">
    <table class="rfc-table">
        <thead>
            <tr>
                <th>Nombre del Hardware</th>
                <th>Descripción</th>
                <th>Componente</th>
                <th>Precio</th>
                <th>Acción</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><textarea id="hardware_nombre" rows="2" cols="30"></textarea></td>
                <td><textarea id="descripcion" rows="2" cols="30"></textarea></td>
                <td><textarea id="componente" rows="2" cols="20"></textarea></td>
                <td><input type="number" id="precio" step="0.01" placeholder="0.00"></td>
                <td><button onclick="enviarRFC()">Enviar</button></td>
            </tr>
        </tbody>
    </table>
</div>

<script>
    // Mostrar/ocultar la tabla RFC al hacer clic en el botón
    const rfcButton = document.getElementById("rfcButton");
    const rfcContainer = document.getElementById("rfcContainer");

    rfcButton.onclick = () => {
        if (rfcContainer.classList.contains("hidden")) {
            rfcContainer.classList.remove("hidden");
        } else {
            rfcContainer.classList.add("hidden");
        }
    };

    // Función para enviar los datos al servidor
    // Función para enviar los datos al servidor
function enviarRFC() {
    const hardwareNombre = document.getElementById("hardware_nombre").value;
    const descripcion = document.getElementById("descripcion").value;
    const componente = document.getElementById("componente").value;
    const precio = document.getElementById("precio").value;

    if (!hardwareNombre || !descripcion || !componente || !precio) {
        alert("Por favor, completa todos los campos.");
        return;
    }

    fetch('guardar_rfc.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `hardware_nombre=${encodeURIComponent(hardwareNombre)}&descripcion=${encodeURIComponent(descripcion)}&componente=${encodeURIComponent(componente)}&precio=${encodeURIComponent(precio)}`,
    })
        .then(response => response.text())
        .then(data => {
            if (data === 'success') {
                alert('Datos guardados con éxito. Se ha notificado al administrador.');
                document.getElementById("hardware_nombre").value = "";
                document.getElementById("descripcion").value = "";
                document.getElementById("componente").value = "";
                document.getElementById("precio").value = "";
            } else {
                alert('Error: ' + data);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Hubo un problema con el envío.');
        });
}
</script>

<!-- Botón para ver RFC aprobados/rechazados -->
<?php if ($totalRFC > 0): ?>
    <button id="verRFC" style="margin-bottom: 20px;">Ver RFC Aprobados/Rechazados</button>
<?php else: ?>
    <p>No hay RFC aprobados o rechazados en este momento.</p>
<?php endif; ?>

<!-- Modal para mostrar la tabla de RFC -->
<div id="modalRFC" class="modal" style="display: none;">
    <div class="modal-content" style="width: 90%; max-height: 90%; overflow-y: auto;">
        <span class="close">&times;</span>
        <h2>RFC Aprobados/Rechazados</h2>
        <table border="1" style="width: 100%; text-align: left;">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Hardware Nombre</th>
                    <th>Descripción</th>
                    <th>Componente</th>
                    <th>Precio</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sqlRFC = "SELECT * FROM rfc WHERE aprobado_rechazado IN ('Aprobado', 'Rechazado')";
                $resultRFC = $conexion->query($sqlRFC);

                while ($row = $resultRFC->fetch_assoc()):
                ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo $row['hardware_nombre']; ?></td>
                        <td><?php echo $row['descripcion']; ?></td>
                        <td><?php echo $row['componente']; ?></td>
                        <td><?php echo $row['precio']; ?></td>
                        <td><?php echo $row['aprobado_rechazado']; ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    // Modal RFC
    const modalRFC = document.getElementById("modalRFC");
    const verRFC = document.getElementById("verRFC");
    const closeModalRFC = document.querySelector(".close");

    // Abrir el modal al hacer clic en el botón
    if (verRFC) {
        verRFC.onclick = () => modalRFC.style.display = "block";
    }

    // Cerrar el modal al hacer clic en la 'X'
    closeModalRFC.onclick = () => modalRFC.style.display = "none";

    // Cerrar el modal si se hace clic fuera del contenido
    window.onclick = (event) => {
        if (event.target == modalRFC) {
            modalRFC.style.display = "none";
        }
    };
</script>
    
</body>
</html>

<?php $conexion->close(); ?>
