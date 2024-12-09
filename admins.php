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

// Obtener reportes "Pendiente"
$sql_reportes = "SELECT * FROM reportesnuevos WHERE estado = 'Pendiente'";
$result_reportes = $conexion->query($sql_reportes);

// Obtener reportes "En Proceso" del área del usuario logueado
$area_usuario = $_SESSION['area'];
$sql_reportes_proceso = "SELECT * FROM reportesnuevos WHERE estado = 'En Proceso' AND area = '$area_usuario'";
$result_reportes_proceso = $conexion->query($sql_reportes_proceso);

// Verificar si hay RFC en estado 'Pendiente'
$sqlPendiente = "SELECT COUNT(*) AS total FROM rfc WHERE aprobado_rechazado = 'Pendiente'";
$resultPendiente = $conexion->query($sqlPendiente);
$totalPendientes = $resultPendiente->fetch_assoc()['total'];

// Obtener reportes "En Proceso" del área del usuario logueado
$area_usuario = $_SESSION['area'];
$sql_reportes_resuelto = "SELECT * FROM reportesnuevos WHERE estado = 'Resuelto' AND area = '$area_usuario'";
$result_reportes_resuelto = $conexion->query($sql_reportes_resuelto);

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
body {
    font-family: Arial, sans-serif;
    background-color: #f4f4f4;
    margin: 0;
    padding: 0;
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
        #tabla-proceso {
            display: none; /* Ocultar inicialmente */
        }

    </style>
</head>
<body>
    <h1>Bienvenido Administrador, <?php echo $_SESSION['nombre']; ?>!</h1>
    <h1>Este es el portal para los administradores.</h1>
    <p><strong>Área:</strong> <?php echo $_SESSION['area']; ?></p>
    <p><strong>Correo:</strong> <?php echo $_SESSION['correo']; ?></p> 
    <a href="logout.php" class="btn-rojo">Cerrar sesión</a>

     <!-- Botón para abrir el modal -->
<!-- Botón para abrir el modal -->
<button id="openModal" class="btn btn-primary">Gestionar Problema</button>


<style>
    .modal1 {
        display: none;
        position: fixed;
        z-index: 1;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgba(0, 0, 0, 0.4);
    }

    .modal1-content {
        background-color: #fff;
        margin: 15% auto;
        padding: 20px;
        border: 1px solid #888;
        width: 80%;
        max-width: 900px;
        overflow-x: auto;
    }

    .close {
        color: #aaa;
        float: right;
        font-size: 28px;
        font-weight: bold;
    }

    .close:hover,
    .close:focus {
        color: black;
        text-decoration: none;
        cursor: pointer;
    }
</style>

<!-- Botón para abrir el modal de problemas finalizados -->
<button id="openModalFinalizados">Ver Problemas Finalizados</button>

<!-- Modal para problemas finalizados -->
<div id="modalFinalizados" class="modal1" style="display: none;">
    <div class="modal1-content">
        <span class="close" id="closeModalFinalizados">&times;</span>
        <h2>Problemas Finalizados</h2>
        <table border="1">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre del Hardware</th>
                    <th>Descripción</th>
                    <th>Edificio</th>
                    <th>Prioridad</th>
                    <th>Correo Técnico</th>
                    <th>Causa Raíz</th>
                    <th>Estado</th>
                    <th>Fecha de Creación</th>
                    <th>Solución</th>
                    <th>Horas</th>
                    <th>Fecha Resuelto</th>
                </tr>
            </thead>
            <tbody id="problemasFinalizadosBody">
                <!-- Los datos de la tabla se cargarán aquí mediante JavaScript -->
            </tbody>
        </table>
    </div>
</div>
<script>

// Abrir el modal de problemas finalizados
const modalFinalizados = document.getElementById("modalFinalizados");
const openModalFinalizados = document.getElementById("openModalFinalizados");
const closeModalFinalizados = document.getElementById("closeModalFinalizados");

openModalFinalizados.onclick = () => {
    modalFinalizados.style.display = "block";
    cargarProblemasFinalizados(); // Llamar a la función para cargar los datos
};

closeModalFinalizados.onclick = () => {
    modalFinalizados.style.display = "none";
};

window.onclick = (event) => {
    if (event.target == modalFinalizados) {
        modalFinalizados.style.display = "none";
    }
};
function cargarProblemasFinalizados() {
    fetch('obtener_problemas_finalizados.php')
        .then(response => response.json())
        .then(data => {
            const tbody = document.getElementById("problemasFinalizadosBody");
            tbody.innerHTML = ''; // Limpiar cualquier contenido previo

            data.forEach(problema => {
                const row = document.createElement("tr");
                row.innerHTML = `
                    <td>${problema.id}</td>
                    <td>${problema.nombre_hardware}</td>
                    <td>${problema.descripcion}</td>
                    <td>${problema.edificio}</td>
                    <td>${problema.prioridad}</td>
                    <td>${problema.correo_tecnico}</td>
                    <td>${problema.causa_raiz}</td>
                    <td>${problema.estado}</td>
                    <td>${problema.fecha_creacion}</td>
                    <td>${problema.solucion}</td>
                    <td>${problema.horas}</td>
                    <td>${problema.fecha_resuelto}</td>
                `;
                tbody.appendChild(row);
            });
        })
        .catch(error => {
            console.error("Error al cargar los problemas finalizados:", error);
        });
}

</script>

<!-- Modal -->
<div id="encuestaModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h3>Gestión de Problemas</h3>
        <form id="encuestaForm" method="POST" action="insertar_problemas.php">
            <!-- Campo: Nombre del Hardware -->
            <label for="nombre_hardware">Nombre del Equipo:</label>
            <textarea id="nombre_hardware" name="nombre_hardware" placeholder="" required></textarea>
            
            <!-- Campo: Descripción -->
            <label for="descripcion">Problema:</label>
            <textarea id="descripcion" name="descripcion" placeholder="" required></textarea>
            
            <!-- Campo: Edificio -->
            <label for="edificio">Edificio:</label>
            <textarea id="edificio" name="edificio" placeholder="" required></textarea>
            
            <!-- Campo: Técnico -->
            <label for="tecnico">Técnico:</label>
            <select id="tecnico" name="correo_tecnico" required>
                <option value="">Seleccionar Técnico</option>
                <?php
                // Obtener el área del usuario logueado
                $correo_usuario = $_SESSION['correo'];
                $sql_usuario = "SELECT area FROM usuarios WHERE correo = ?";
                $stmt_usuario = $conexion->prepare($sql_usuario);
                $stmt_usuario->bind_param("s", $correo_usuario);
                $stmt_usuario->execute();
                $resultado_usuario = $stmt_usuario->get_result();

                if ($resultado_usuario->num_rows > 0) {
                    $usuario = $resultado_usuario->fetch_assoc();
                    $area_usuario = $usuario['area'];

                    // Obtener técnicos del mismo área
                    $sql_tecnicos = "SELECT correo, nombre FROM usuarios WHERE rol = 'Tecnico' AND area = ?";
                    $stmt_tecnicos = $conexion->prepare($sql_tecnicos);
                    $stmt_tecnicos->bind_param("s", $area_usuario);
                    $stmt_tecnicos->execute();
                    $resultado_tecnicos = $stmt_tecnicos->get_result();

                    while ($tecnico = $resultado_tecnicos->fetch_assoc()) {
                        // Consulta para contar los reportes "En Proceso" asignados al técnico en la tabla reportesnuevos
                        $correo_tecnico = $tecnico['correo'];
                        $query_reportes = "SELECT COUNT(*) as total FROM reportesnuevos WHERE estado = 'En Proceso' AND correo_tecnico = ?";
                        $stmt_reportes = $conexion->prepare($query_reportes);
                        $stmt_reportes->bind_param("s", $correo_tecnico);
                        $stmt_reportes->execute();
                        $resultado_reportes = $stmt_reportes->get_result();
                        $reportes = $resultado_reportes->fetch_assoc();
                        $reportesEnProceso = $reportes['total'];

                        // Mostrar la opción con la cantidad de reportes en proceso
                        echo "<option value='{$tecnico['correo']}'>{$tecnico['nombre']} ({$tecnico['correo']}) - {$reportesEnProceso} en proceso</option>";

                        $stmt_reportes->close();
                    }

                    $stmt_tecnicos->close();
                } else {
                    echo "<option disabled>No hay técnicos disponibles en esta área</option>";
                }

                $stmt_usuario->close();
                ?>
            </select>
            
            <!-- Botón de envío -->
            <button type="submit" class="btn btn-success">Enviar Problema</button>
        </form>
    </div>
</div>





<!-- CSS del Modal -->
<style>
    /* Estilo del modal */
    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        justify-content: center;
        align-items: center;
    }

    .modal-content {
        background-color: #fff;
        padding: 20px;
        border-radius: 10px;
        width: 90%;
        max-width: 600px;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        position: relative;
    }

    .close {
        position: absolute;
        top: 10px;
        right: 15px;
        font-size: 24px;
        cursor: pointer;
    }

    form label {
        display: block;
        margin: 10px 0 5px;
        font-weight: bold;
    }

    form textarea, form select {
        width: 100%;
        padding: 10px;
        margin-bottom: 15px;
        border: 1px solid #ccc;
        border-radius: 5px;
    }

    form button {
        display: block;
        width: 100%;
        padding: 10px;
        background-color: #28a745;
        color: #fff;
        border: none;
        border-radius: 5px;
        cursor: pointer;
    }

    form button:hover {
        background-color: #218838;
    }
</style>
<script>

    // Obtener elementos del DOM
const modal = document.getElementById("encuestaModal");
const openModalButton = document.getElementById("openModal");
const closeModalButton = document.querySelector(".close");

// Abrir el modal al hacer clic en el botón
openModalButton.addEventListener("click", () => {
    modal.style.display = "flex";
});

// Cerrar el modal al hacer clic en la 'X'
closeModalButton.addEventListener("click", () => {
    modal.style.display = "none";
});

// Cerrar el modal al hacer clic fuera de la ventana del modal
window.addEventListener("click", (e) => {
    if (e.target === modal) {
        modal.style.display = "none";
    }
});

    </script>


    <h2>Reportes Nuevos</h2>
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
                    // Consulta para contar los reportes "En Proceso" asignados al técnico
                    $correo = $tecnico['correo'];
                    $query = "SELECT COUNT(*) as total FROM reportesnuevos WHERE estado = 'En Proceso' AND correo_tecnico = '$correo'";
                    $resultado = mysqli_query($conexion, $query);
                    $fila = mysqli_fetch_assoc($resultado);
                    $reportesEnProceso = $fila['total'];

                    // Mostrar la opción con la cantidad de reportes en proceso
                    echo "<option value='{$tecnico['correo']}'>{$tecnico['nombre']} ({$tecnico['correo']}) - {$reportesEnProceso} en proceso</option>";
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

<!-- Texto verde si hay RFC en 'Pendiente' -->
<?php if ($totalPendientes > 0): ?>
    <p style="color: green; cursor: pointer;" id="verRFC">
        Tienes RFC en estado 'Pendiente'. ¡Haz clic aquí para revisarlos!
    </p>
<?php else: ?>
    <p>No tienes RFC pendientes por revisar.</p>
<?php endif; ?>

<!-- Modal para mostrar la tabla de RFC -->
<div id="modalRFC" class="modal" style="display: none;">
    <div class="modal-content" style="width: 90%; max-height: 90%; overflow-y: auto;">
        <span class="close">&times;</span>
        <h2>RFC en estado 'Pendiente'</h2>
        <table border="1" style="width: 100%; text-align: left;">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Correo Técnico</th>
                    <th>Hardware Nombre</th>
                    <th>Descripción</th>
                    <th>Componente</th>
                    <th>Precio</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sqlRFC = "SELECT * FROM rfc WHERE aprobado_rechazado = 'Pendiente'";
                $resultRFC = $conexion->query($sqlRFC);

                while ($row = $resultRFC->fetch_assoc()):
                ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo $row['correo_tecnico']; ?></td>
                        <td><?php echo $row['hardware_nombre']; ?></td>
                        <td><?php echo $row['descripcion']; ?></td>
                        <td><?php echo $row['componente']; ?></td>
                        <td><?php echo $row['precio']; ?></td>
                        <td>
                            <button onclick="actualizarEstado(<?php echo $row['id']; ?>, 'Aprobado')">Aprobado</button>
                            <button onclick="actualizarEstado(<?php echo $row['id']; ?>, 'Rechazado')">Rechazado</button>
                        </td>
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

    // Abrir el modal al hacer clic en el texto verde
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

    // Función para actualizar el estado de aprobado_rechazado
    function actualizarEstado(id, estado) {
        fetch('actualizar_rfc.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `id=${id}&estado=${estado}`,
        })
        .then(response => response.text())
        .then(data => {
            if (data === 'success') {
                alert('Estado actualizado con éxito.');
                location.reload(); // Recargar la página para reflejar los cambios
            } else {
                alert('Error al actualizar el estado: ' + data);
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    }
</script>

<script>
        function toggleTablaProceso() {
            const tabla = document.getElementById('tabla-proceso');
            tabla.style.display = tabla.style.display === 'none' ? 'table' : 'none';
        }
    </script>
<BR><BR>
<button onclick="toggleTablaProceso()">Ver Reportes en Proceso</button>
    <table id="tabla-proceso">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Descripción</th>
                <th>Estado</th>
                <th>Fecha</th>
                <th>Correo Reportante</th>
                <th>Área</th>
                <th>Edificio</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result_reportes_proceso->num_rows > 0): ?>
                <?php while ($reporte_proceso = $result_reportes_proceso->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $reporte_proceso['id']; ?></td>
                        <td><?php echo $reporte_proceso['nombre']; ?></td>
                        <td><?php echo $reporte_proceso['descripcion']; ?></td>
                        <td><?php echo $reporte_proceso['estado']; ?></td>
                        <td><?php echo $reporte_proceso['fecha_reporte']; ?></td>
                        <td><?php echo $reporte_proceso['correo']; ?></td>
                        <td><?php echo $reporte_proceso['area']; ?></td>
                        <td><?php echo $reporte_proceso['edificio']; ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8">No hay reportes en proceso en tu área</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
  


    <script>
        function toggleTablaResuelto() {
            const tabla = document.getElementById('tabla-resuelto');
            tabla.style.display = tabla.style.display === 'none' ? 'table' : 'none';
        }
    </script>
<BR><BR>
<button onclick="toggleTablaResuelto()">Ver Reportes Resueltos</button>
    <table id="tabla-resuelto">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Descripción</th>
                <th>Estado</th>
                <th>Fecha</th>
                <th>Correo Reportante</th>
                <th>Área</th>
                <th>Edificio</th>
                <th>Fecha Resolucion</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result_reportes_resuelto->num_rows > 0): ?>
                <?php while ($reporte_resuelto = $result_reportes_resuelto->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $reporte_resuelto['id']; ?></td>
                        <td><?php echo $reporte_resuelto['nombre']; ?></td>
                        <td><?php echo $reporte_resuelto['descripcion']; ?></td>
                        <td><?php echo $reporte_resuelto['estado']; ?></td>
                        <td><?php echo $reporte_resuelto['fecha_reporte']; ?></td>
                        <td><?php echo $reporte_resuelto['correo']; ?></td>
                        <td><?php echo $reporte_resuelto['area']; ?></td>
                        <td><?php echo $reporte_resuelto['edificio']; ?></td>
                        <td><?php echo $reporte_resuelto['fecha_resuelto']; ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8">No hay reportes en proceso en tu área</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>


    
</body>
</html>
<?php $conexion->close(); ?>
