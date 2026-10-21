<?php
session_start();

// Verificar sesión y obtener información del usuario
$usuario_logueado = isset($_SESSION['usuario_id']);
$nombre_usuario   = $usuario_logueado ? $_SESSION['usuario_nombre'] : '';
$rol_usuario      = $usuario_logueado ? $_SESSION['usuario_rol'] : '';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Malvados y Asociados</title>

    <!-- Fuentes e Iconos -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- BOOTSTRAP 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- CSS PERSONALIZADO -->
    <link rel="stylesheet" href="css/styles.css">
</head>
<body class="dark-theme">

    <main class="dashboard-main form-layout auth-layout">
        <div class="form-wrapper auth-wrapper wide">

            <div class="auth-brand">
                <div class="logo-icon"><i class="fa-solid fa-scale-balanced"></i></div>
                <h1>Malvados y Asociados</h1>
                <p>Sistema de Gestión de Expedientes</p>
            </div>

            <div class="form-card text-center">

                <?php if ($usuario_logueado): ?>
                    <div class="session-box">
                        Sesión activa: <strong class="text-white"><?php echo htmlspecialchars($nombre_usuario); ?></strong><br>
                        Rol:
                        <span class="text-gold" style="text-transform: uppercase; font-weight: 700; font-size: 0.75rem; letter-spacing: 0.1em;">
                            <?php echo htmlspecialchars($rol_usuario); ?>
                        </span>
                    </div>

                    <a href="modulos/panel.php" class="btn btn-success w-100 mb-2 py-2">
                        <i class="fa-solid fa-folder-open"></i> Ir al Panel de Control
                    </a>
                    <a href="auth/logout.php" class="btn btn-danger w-100 py-2">
                        <i class="fa-solid fa-arrow-right-from-bracket"></i> Cerrar Sesión
                    </a>

                <?php else: ?>
                    <p class="teuixt-white mb-4" style="font-size: 0.9rem;">
                        Bienvenido. Inicie sesión para consultar expedientes, reasignar litigios y gestionar la base de clientes.
                    </p>

                    <a href="auth/login.php" class="btn btn-success w-100 py-2">
                        <i class="fa-solid fa-right-to-bracket"></i> Iniciar Sesión
                    </a>

                                        <a href="auth/registro.php" class="btn btn-outline-secondary text-white w-100 py-2 mt-2">
                        <i class="fa-solid fa-user-plus"></i> Crear Cuenta
                    </a>

                <?php endif; ?>

            </div>
        </div>
    </main>

</body>
</html>