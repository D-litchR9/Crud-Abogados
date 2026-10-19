<?php
session_start();

// Verificar sesión y obtener información del usuario
$usuario_logueado = isset($_SESSION['usuario_id']);
$nombre_usuario = $usuario_logueado ? $_SESSION['usuario_nombre'] : '';
$rol_usuario = $usuario_logueado ? $_SESSION['usuario_rol'] : '';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Malvados y Asociados</title>
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>

    <div class="container">
        <div class="logo"></div>
        <h1>Malvados y Asociados</h1>
        <h3>Sistema de Gestión de Expedientes</h3>
        
        <?php if ($usuario_logueado): ?>
            <!-- Mostrar información de sesión y botones de navegación -->
            <div class="session-info">
                Sesión activa: <strong><?php echo htmlspecialchars($nombre_usuario); ?></strong><br>
                Rol asignado: <span style="text-transform: uppercase; font-size: 12px; font-weight: bold; color: #2980b9;"><?php echo $rol_usuario; ?></span>
            </div>
            
            <a href="modulos/panel.php" class="btn btn-success"> Ir al Panel de Control</a>
            <br>
            <a href="auth/logout.php" class="btn btn-danger">Cerrar Sesión</a>

        <?php else: ?>
            <p>Bienvenido, inicie sesión para consultar expedientes, reasignar litigios judiciales y gestionar la base de datos de clientes.</p>
            
            <a href="auth/login.php" class="btn btn-primary"> Iniciar Sesión</a>
        <?php endif; ?>
    </div>

</body>
</html>
