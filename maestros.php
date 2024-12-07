<?php
session_start();

// Validar si el usuario está logueado
if (!isset($_SESSION['correo'])) {
    header("Location: login.php");
    exit();
}

// Obtener el correo del usuario logueado desde la sesión
$correo_usuario = $_SESSION['correo'];

// Conexión a la base de datos
$conexion = new mysqli("localhost", "root", "", "mantenimiento_escuela");
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Consulta para obtener los reportes con estado 'Resuelto' enviados por el usuario logueado
$sql_resueltos = "SELECT id, nombre, descripcion, fecha_reporte, correo_tecnico, prioridad, comentarios,solucion, fecha_resuelto, calificacion 
                  FROM reportesnuevos 
                  WHERE estado = 'Resuelto' AND correo = ?";
$stmt = $conexion->prepare($sql_resueltos);
$stmt->bind_param("s", $correo_usuario);
$stmt->execute();
$result_resueltos = $stmt->get_result();

// Consultas para reportes 'Pendientes'
$sql_pendientes = "SELECT id, nombre, descripcion, fecha_reporte, correo_tecnico, prioridad
                   FROM reportesnuevos 
                   WHERE (estado = 'Pendiente' OR estado = 'En Proceso') AND correo = ?";

$stmt_pendientes = $conexion->prepare($sql_pendientes);
$stmt_pendientes->bind_param("s", $correo_usuario);
$stmt_pendientes->execute();
$result_pendientes = $stmt_pendientes->get_result();
?>

    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Portal Maestro</title>
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
        <h1>Bienvenido Maestro, <?php echo $_SESSION['nombre']; ?>!</h1>
        <h1>Este es el portal para los maestros.</h1>
        <p><strong>Area:</strong> <?php echo $_SESSION['area']; ?></p>
        <p><strong>id:</strong> <?php echo $_SESSION['correo']; ?></p>
        <a href="logout.php" class="btn-rojo">Cerrar sesión</a>
        
        

        <form action="procesar_reporte.php" method="POST">
    <label for="hardware">Seleccione el hardware:</label>
    <select name="hardware" id="hardware" onchange="mostrarDetalles()">
        <option value="">Seleccione...</option> <!-- Opción vacía por defecto -->
        <option value="computadora">Computadora</option>
        <option value="impresora">Impresora</option>
        <option value="proyector">Proyector</option>
    </select><br><br>

    <!-- Detalles de computadoras -->
    <div id="detalles_computadora" style="display:none;">
        <label for="nombre_computadora">Nombre de la Computadora:</label>
        <select name="nombre_computadora" id="nombre_computadora" onchange="cargarEspecificacionesComputadora()">
            <option value="">Seleccione...</option> <!-- Opción vacía por defecto -->
        </select><br><br>

        <label for="marca_computadora">Marca:</label>
        <span id="marca_computadora"></span><br><br>

        <label for="procesador">Procesador:</label>
        <span id="procesador"></span><br><br>

        <label for="ram">RAM:</label>
        <span id="ram"></span><br><br>

        <label for="sistema_operativo">Sistema Operativo:</label>
        <span id="sistema_operativo"></span><br><br>

        <label for="disco">Disco:</label>
        <span id="disco"></span><br><br>
    </div>

    <!-- Detalles de impresoras -->
    <div id="detalles_impresora" style="display:none;">
        <label for="nombre_impresora">Nombre de la Impresora:</label>
        <select name="nombre_impresora" id="nombre_impresora" onchange="cargarEspecificacionesImpresora()">
            <option value="">Seleccione...</option> <!-- Opción vacía por defecto -->
        </select><br><br>

        <label for="marca_impresora">Marca:</label>
        <span id="marca_impresora"></span><br><br>
    </div>

    <!-- Detalles de proyectores -->
    <div id="detalles_proyector" style="display:none;">
        <label for="nombre_proyector">Nombre del Proyector:</label>
        <select name="nombre_proyector" id="nombre_proyector" onchange="cargarEspecificacionesProyector()">
            <option value="">Seleccione...</option> <!-- Opción vacía por defecto -->
        </select><br><br>

        <label for="marca_proyector">Marca:</label>
        <span id="marca_proyector"></span><br><br>
    </div>

   <!-- Edificio -->
<label for="edificio">Aula o Edificio:</label><br>
<textarea name="edificio" id="edificio" rows="1" cols="12" required></textarea><br><br>

<!-- Descripción -->
<label for="descripcion">Descripción:</label><br>
<textarea name="descripcion" id="descripcion" rows="4" cols="50" required></textarea><br><br>

<!-- Campo Oculto para el correo -->
<input type="hidden" name="correo" id="correo">

<button type="submit">Enviar Reporte</button>
</form>
<style>
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid black;
            padding: 10px;
            text-align: center;
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
        }
        button:hover {
            background-color: #45a049;
        }
        .hidden {
            display: none;
        }
        </style>
<script>
    // Mostrar/ocultar tabla de reportes resueltos
    function toggleTableResueltos() {
        const table = document.getElementById("tabla-resueltos");
        table.classList.toggle("hidden");
    }

    // Mostrar/ocultar tabla de reportes pendientes
    function toggleTablePendientes() {
        const table = document.getElementById("tabla-pendientes");
        table.classList.toggle("hidden");
    }
</script>
<!-- Botón para reportes resueltos -->
<BR><BR>
<button onclick="toggleTableResueltos()">Reportes Resueltos</button>
<table id="tabla-resueltos" class="hidden">
    <thead>
        <tr>
            <th>Nombre</th>
            <th>Descripción</th>
            <th>Fecha Reporte</th>
            <th>Correo Técnico</th>
            <th>Prioridad</th>
            <th>Diagnostico</th>
            <th>Solucion</th>
            <th>Fecha Resuelto</th>
            <th>Calificación</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($result_resueltos->num_rows > 0): ?>
            <?php while ($reporte = $result_resueltos->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $reporte['nombre']; ?></td>
                    <td><?php echo $reporte['descripcion']; ?></td>
                    <td><?php echo $reporte['fecha_reporte']; ?></td>
                    <td><?php echo $reporte['correo_tecnico']; ?></td>
                    <td><?php echo $reporte['prioridad']; ?></td>
                    <td><?php echo $reporte['comentarios']; ?></td>
                    <td><?php echo $reporte['solucion']; ?></td>
                    <td><?php echo $reporte['fecha_resuelto']; ?></td>
                    <td>
                        <form action="calificar.php" method="POST">
                            <input type="hidden" name="id_reporte" value="<?php echo $reporte['id']; ?>">
                            <?php if ($reporte['calificacion']): ?>
                                <textarea name="calificacion" rows="1" cols="10" placeholder="1 a 5" disabled><?php echo $reporte['calificacion']; ?></textarea>
                            <?php else: ?>
                                <textarea name="calificacion" rows="1" cols="10" placeholder="1 a 5" required></textarea>
                            <?php endif; ?>
                            <button type="submit" <?php echo ($reporte['calificacion']) ? 'disabled' : ''; ?>>Calificar</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="8">No hay reportes resueltos.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<!-- Botón para reportes pendientes -->
<BR><BR>
<button onclick="toggleTablePendientes()">Reportes Pendientes</button>
<table id="tabla-pendientes" class="hidden">
    <thead>
        <tr>
            <th>Nombre</th>
            <th>Descripción</th>
            <th>Fecha Reporte</th>
            <th>Correo Técnico</th>
            <th>Prioridad</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($result_pendientes->num_rows > 0): ?>
            <?php while ($reporte = $result_pendientes->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $reporte['nombre']; ?></td>
                    <td><?php echo $reporte['descripcion']; ?></td>
                    <td><?php echo $reporte['fecha_reporte']; ?></td>
                    <td><?php echo $reporte['correo_tecnico']; ?></td>
                    <td><?php echo $reporte['prioridad']; ?></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="5">No hay reportes pendientes.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<style>
    .hidden {
        display: none;
    }
</style>

    <script>

function mostrarDetalles() {
    var hardware = document.getElementById("hardware").value;

    // Oculta todos los detalles
    document.getElementById("detalles_computadora").style.display = "none";
    document.getElementById("detalles_impresora").style.display = "none";
    document.getElementById("detalles_proyector").style.display = "none";

    // Muestra los detalles de acuerdo al hardware seleccionado
    if (hardware === "computadora") {
        document.getElementById("detalles_computadora").style.display = "block";
        cargarNombresComputadoras();
    } else if (hardware === "impresora") {
        document.getElementById("detalles_impresora").style.display = "block";
        cargarNombresImpresoras();
    } else if (hardware === "proyector") {
        document.getElementById("detalles_proyector").style.display = "block";
        cargarNombresProyectores();
    }
}

function cargarNombresComputadoras() {
    fetch('cargar_computadoras.php')
        .then(response => response.json())
        .then(data => {
            var nombreSelect = document.getElementById("nombre_computadora");
            nombreSelect.innerHTML = ""; // Limpia opciones anteriores

            // Agrega la opción vacía
            var optionEmpty = document.createElement("option");
            optionEmpty.value = "";
            optionEmpty.textContent = "Seleccione...";
            nombreSelect.appendChild(optionEmpty);

            // Agrega opciones dinámicamente
            data.forEach(computadora => {
                var option = document.createElement("option");
                option.value = computadora.nombre;
                option.textContent = computadora.nombre;
                nombreSelect.appendChild(option);
            });

            // Guarda los datos de todas las computadoras en el navegador
            window.computadoras = data;
        })
        .catch(error => console.error("Error al cargar nombres:", error));
}

function cargarEspecificacionesComputadora() {
    var nombre = document.getElementById("nombre_computadora").value;

    // Busca las especificaciones de la computadora seleccionada
    var computadora = window.computadoras.find(c => c.nombre === nombre);

    if (computadora) {
        // Se asegura de mostrar la marca de la computadora
        document.getElementById("marca_computadora").textContent = computadora.marca || "Desconocido";
        document.getElementById("procesador").textContent = computadora.procesador || "Desconocido";
        document.getElementById("ram").textContent = computadora.ram || "Desconocido";
        document.getElementById("sistema_operativo").textContent = computadora.sistema_operativo || "Desconocido";
        document.getElementById("disco").textContent = computadora.disco || "Desconocido";
    }
}

function cargarNombresImpresoras() {
    fetch('cargar_impresoras.php')
        .then(response => response.json())
        .then(data => {
            var nombreSelect = document.getElementById("nombre_impresora");
            nombreSelect.innerHTML = ""; // Limpia opciones anteriores

            // Agrega la opción vacía
            var optionEmpty = document.createElement("option");
            optionEmpty.value = "";
            optionEmpty.textContent = "Seleccione...";
            nombreSelect.appendChild(optionEmpty);

            // Agrega opciones dinámicamente
            data.forEach(impresora => {
                var option = document.createElement("option");
                option.value = impresora.nombre;
                option.textContent = impresora.nombre;
                nombreSelect.appendChild(option);
            });

            // Guarda los datos de todas las impresoras en el navegador
            window.impresoras = data;
        })
        .catch(error => console.error("Error al cargar impresoras:", error));
}

function cargarEspecificacionesImpresora() {
    var nombre = document.getElementById("nombre_impresora").value;

    // Busca las especificaciones de la impresora seleccionada
    var impresora = window.impresoras.find(i => i.nombre === nombre);

    if (impresora) {
        // Se asegura de mostrar la marca de la impresora
        document.getElementById("marca_impresora").textContent = impresora.marca || "Desconocido";
    }
}

function cargarNombresProyectores() {
    fetch('cargar_proyectores.php')
        .then(response => response.json())
        .then(data => {
            var nombreSelect = document.getElementById("nombre_proyector");
            nombreSelect.innerHTML = ""; // Limpia opciones anteriores

            // Agrega la opción vacía
            var optionEmpty = document.createElement("option");
            optionEmpty.value = "";
            optionEmpty.textContent = "Seleccione...";
            nombreSelect.appendChild(optionEmpty);

            // Agrega opciones dinámicamente
            data.forEach(proyector => {
                var option = document.createElement("option");
                option.value = proyector.nombre;
                option.textContent = proyector.nombre;
                nombreSelect.appendChild(option);
            });

            // Guarda los datos de todos los proyectores en el navegador
            window.proyectores = data;
        })
        .catch(error => console.error("Error al cargar proyectores:", error));
}

function cargarEspecificacionesProyector() {
    var nombre = document.getElementById("nombre_proyector").value;

    // Busca las especificaciones del proyector seleccionado
    var proyector = window.proyectores.find(p => p.nombre === nombre);

    if (proyector) {
        // Se asegura de mostrar la marca del proyector
        document.getElementById("marca_proyector").textContent = proyector.marca || "Desconocido";
    }
}
    </script>

    <div id="modal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <p>Reporte enviado exitosamente.</p>
    </div>
    </div>

    <script>
document.addEventListener("DOMContentLoaded", function () {
    // Referencia al modal y al botón de cerrar
    const modal = document.getElementById("modal");
    const closeBtn = document.querySelector(".close");

    // Función para mostrar el modal
    function mostrarModal() {
        modal.style.display = "block";
    }

    // Función para limpiar el formulario
    function limpiarFormulario() {
        document.querySelector("form").reset(); // Reinicia los valores del formulario
    }

    // Cierra el modal cuando se hace clic en la 'X'
    closeBtn.addEventListener("click", function () {
        modal.style.display = "none";
    });

    // Cierra el modal si el usuario hace clic fuera del contenido
    window.addEventListener("click", function (event) {
        if (event.target === modal) {
            modal.style.display = "none";
        }
    });

    // Enviar el formulario por AJAX
    const form = document.querySelector("form");
    form.addEventListener("submit", function (e) {
        e.preventDefault(); // Evita la recarga de la página

        const formData = new FormData(form);

        // Enviar los datos del formulario por AJAX
        fetch("procesar_reporte.php", {
            method: "POST",
            body: formData,
        })
            .then((response) => response.text())
            .then((data) => {
                console.log(data); // Muestra la respuesta del servidor en la consola
                mostrarModal(); // Muestra el modal al enviar el reporte
                limpiarFormulario(); // Limpia el formulario después del envío exitoso
            })
            .catch((error) => {
                console.error("Error:", error);
                alert("Ocurrió un error al enviar el reporte.");
            });
    });
});
</script>


    </body>
    <style>
    /* Estilos para el modal */
.modal {
    display: none; /* Oculto por defecto */
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0, 0, 0, 0.5); /* Fondo semi-transparente */
}

/* Contenido del modal */
.modal-content {
    background-color: #fff;
    margin: 15% auto;
    padding: 20px;
    border: 1px solid #888;
    width: 80%;
    text-align: center;
    border-radius: 8px;
}

/* Botón de cerrar */
.close {
    color: #aaa;
    float: right;
    font-size: 28px;
    font-weight: bold;
    cursor: pointer;
}

.close:hover,
.close:focus {
    color: #000;
    text-decoration: none;
}
</style>
    </html>