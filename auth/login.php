<?php
session_start();
require_once '../config/conexion.php';

// Si ya está logueado, redirigir al panel principal
if (isset($_SESSION['usuario_id'])) {
    header("Location: ../modulos/panel.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = trim($_POST['correo']);
    $password = trim($_POST['password']);

    if (!empty($correo) && !empty($password)) {
        // Buscar al usuario por correo
        $stmt = $conexion->prepare("SELECT * FROM usuarios WHERE correo = :correo LIMIT 1");
        $stmt->execute(['correo' => $correo]);
        $usuario = $stmt->fetch();

       
        if ($usuario && $password === $usuario['password']) {
            // Guardar datos en la sesión
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_nombre'] = $usuario['nombre'];
            $_SESSION['usuario_rol'] = $usuario['rol'];

            header("Location: ../modulos/panel.php");
            exit();
        } else {
            $error = "Credenciales incorrectas.";
        }
    } else {
        $error = "Por favor, llenar todos los campos.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Iniciar Sesión</title>
</head>
<body>
    <h2>Iniciar Sesión</h2>
    <?php if (!empty($error)): ?>
        <p style="color: red;"><?php echo $error; ?></p>
    <?php endif; ?>
    
    <form action="login.php" method="POST">
        <label>Correo:</label><br>
        <input type="email" name="correo" required><br><br>
        
        <label>Contraseña:</label><br>
        <input type="password" name="password" required><br><br>
        
        <button type="submit">Ingresar</button>
    </form>
</body>
</html>
