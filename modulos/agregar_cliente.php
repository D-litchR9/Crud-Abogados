<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

require_once '../config/conexion.php';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']);
    $telefono = trim($_POST['telefono']);
    $correo = trim($_POST['correo']);

    if (!empty($nombre)) {
        try {
            $stmt = $conexion->prepare("INSERT INTO clientes (nombre, telefono, correo) VALUES (:nombre, :telefono, :correo)");
            $stmt->execute([
                'nombre'   => $nombre,
                'telefono' => $telefono,
                'correo'   => $correo
            ]);
            header("Location: panel.php");
            exit();
        } catch (PDOException $e) {
            $error = "Error al guardar el cliente.";
        }
    } else {
        $error = "El nombre del cliente es obligatorio.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Cliente - Malvados y Asociados</title>
    
    <!-- Fuentes e Iconos -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- BOOTSTRAP 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- CSS PERSONALIZADO -->
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body class="dark-theme">

    <!-- HEADER / TOPBAR -->
    <header class="topbar">
        <div class="topbar-left">
            <div class="logo-icon"><i class="fa-solid fa-scale-balanced"></i></div>
            <div class="brand-text">
                <h1 class="m-0" style="font-size: 1.25rem;">Malvados y Asociados</h1>
                <p class="m-0 text-muted" style="font-size: 0.875rem;">Gestión de expedientes</p>
            </div>
        </div>
        <div class="topbar-right">
            <div class="user-profile">
                <div class="user-details">
                    <p class="user-name m-0"><?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?></p>
                    <p class="user-role m-0">Administrador del sistema</p>
                </div>
            </div>
            <?php if (isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] === 'admin'): ?>
                <span class="badge-admin">Admin</span>
            <?php endif; ?>
            <a href="../auth/logout.php" class="btn-logout text-decoration-none">
                <i class="fa-solid fa-arrow-right-from-bracket"></i> Cerrar Sesión
            </a>
        </div>
    </header>

    <!-- MAIN CONTENT -->
    <main class="dashboard-main form-layout">
        
        <div class="form-wrapper">
            <!-- Enlace de regreso -->
            <a href="panel.php" class="back-link">
                <i class="fa-solid fa-chevron-left"></i> Volver al Panel
            </a>

            <!-- Tarjeta del Formulario -->
            <div class="form-card">
                <div class="form-header-card">
                    <div class="icon-box icon-gold-bg d-flex align-items-center justify-content-center" style="background-color: rgba(212, 175, 55, 0.1); color: #D4AF37;">
                        <i class="fa-solid fa-user-plus"></i>
                    </div>
                    <div class="form-title-text">
                        <span class="subtitle" style="color: #D4AF37; font-size: 0.75rem; font-weight: 700; letter-spacing: 0.1em;">GESTIÓN DE CLIENTES</span>
                        <h2 class="mb-1" style="font-family: 'Playfair Display', serif; font-size: 1.75rem;">Registrar Nuevo Cliente</h2>
                        <p class="mb-0 text-muted" style="font-size: 0.875rem;">Complete la información para crear un nuevo perfil en el sistema.</p>
                    </div>
                </div>
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger d-flex align-items-center gap-2 py-2" role="alert" style="font-size: 0.875rem;">
                        <i class="fa-solid fa-circle-exclamation"></i>
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="agregar_cliente.php" class="custom-form">
                    
                    <div class="form-group full-width">
                        <label>Nombre Completo</label>
                        <div class="input-with-icon">
                            <i class="fa-regular fa-user"></i>
                            <input type="text" name="nombre" required placeholder="Ej. Santiago Mendoza Ruiz">
                        </div>
                    </div>

                    <div class="form-group full-width">
                        <label>Correo Electrónico</label>
                        <div class="input-with-icon">
                            <i class="fa-regular fa-envelope"></i>
                            <input type="email" name="correo" placeholder="cliente@correo.com">
                        </div>
                    </div>

                    <div class="form-group full-width">
                        <label>Teléfono de Contacto</label>
                        <div class="input-with-icon">
                            <i class="fa-solid fa-phone"></i>
                            <input type="text" name="telefono" placeholder="Ej. +57 300 123 4567">
                        </div>
                    </div>

                    <!-- Banner de Seguridad -->
                    <div class="alert d-flex align-items-center gap-3 mt-4 mb-0" role="alert" style="background-color: rgba(96, 165, 250, 0.05); color: #60a5fa; border: 1px solid rgba(96, 165, 250, 0.2);">
                        <i class="fa-solid fa-lock fs-5"></i>
                        <span style="font-size: 0.875rem;">La información será almacenada de forma segura y solo podrá ser consultada por administradores autorizados.</span>
                    </div>

                    <!-- Botones de Acción -->
                    <div class="d-flex justify-content-end align-items-center gap-3 mt-4 pt-4 border-top border-secondary">
                        <a href="panel.php" class="btn btn-outline-secondary text-white px-4 py-2">
                            Cancelar
                        </a>
                        <button type="submit" class="btn btn-success d-flex align-items-center gap-2 px-4 py-2">
                            <i class="fa-solid fa-user-plus"></i> Guardar Cliente
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>
</body>
</html>