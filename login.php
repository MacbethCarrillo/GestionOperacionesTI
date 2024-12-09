<?php
session_start(); // Iniciar sesión
require_once 'conexion.php'; // Incluir la conexión

// Procesar formulario de inicio de sesión
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $correo = $_POST['correo'] ?? '';
    $contrasena = $_POST['contrasena'] ?? '';

    // Validar que los campos no estén vacíos
    if (empty($correo) || empty($contrasena)) {
        $error = "Por favor, llena todos los campos.";
    } else {
        // Encriptar la contraseña con md5
        $hashed_password = md5($contrasena);

        // Consultar si existe un usuario con ese correo
        $sql = "SELECT * FROM usuarios WHERE correo = ?";
        $stmt = $conexion->prepare($sql); // Preparar la consulta
        $stmt->bind_param("s", $correo); // "s" para el correo
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();

            // Comparar la contraseña encriptada con la almacenada
            if ($user['contrasena'] === $hashed_password) {
                // Usuario autenticado
                $_SESSION['id_usuario'] = $user['id']; // Almacenar el id_usuario en la sesión
                $_SESSION['correo'] = $user['correo']; // Almacenar el id_usuario en la sesión
                $_SESSION['nombre'] = $user['nombre'];
                $_SESSION['rol'] = $user['rol'];
                $_SESSION['area'] = $user['area'];

                // Redirigir según el rol
                switch ($user['rol']) {
                    case 'Maestro':
                        header("Location: maestros.php");
                        break;
                    case 'Tecnico':
                        header("Location: tecnicos.php");
                        break;
                    case 'Administrador':
                        header("Location: admins.php");
                        break;
                    default:
                        $error = "Rol de usuario desconocido.";
                }
                exit();
            } else {
                $error = "Contraseña incorrecta.";
            }
        } else {
            $error = "Correo no registrado.";
        }

        $stmt->close();
    }
}

$conexion->close(); // Cerrar la conexión a la base de datos
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        form {
            background: #ffffff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }
        h1 {
            margin-top: 20px;
            font-size: 24px;
            color: #333;
        }
        .logo {
            width: 120px; /* Ajusta el tamaño del logo */
            margin-bottom: 20px; /* Espaciado debajo del logo */
        }
        label {
            display: block;
            margin-bottom: 8px;
        }
        input {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        button {
            width: 100%;
            padding: 10px;
            background-color: #10216f;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background-color: #0a1648;
        }
        .error {
            color: red;
            text-align: center;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <form action="login.php" method="POST">
        <!-- Logo -->
        <img src="logo.png" alt="Logo" class="logo">
        <h1>Iniciar Sesión</h1>
        
        <!-- Mensaje de error -->
        <?php if (isset($error)): ?>
            <div class="error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <label for="correo">Correo:</label>
        <input type="email" name="correo" id="correo" required>
        
        <label for="contrasena">Contraseña:</label>
        <input type="password" name="contrasena" id="contrasena" required>
        
        <button type="submit">Iniciar Sesión</button>
    </form>
</body>
</html>
