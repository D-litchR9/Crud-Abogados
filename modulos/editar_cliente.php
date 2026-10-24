<?php

session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'admin') {
    header("Location: panel.php");
    exit();
}

require_once '../config/conexion.php';
require_once '../config/validaciones.php';
$error = '';
$id_cliente = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : null;

// proceso de eliminacion
if ($id_cliente && isset($_POST['accion']) && $_POST['accion'] === 'eliminar') {
    try {
        $stmt = $conexion->prepare("DELETE FROM clientes WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id_cliente]);
        $_SESSION['exito'] = 'Cliente eliminado correctamente.';
        header("Location: editar_cliente.php");
        exit();
    } catch (PDOException $e) {
        $error = "Error al eliminar el cliente.";
    }
}

// proceso de actualizacion
if ($id_cliente && $_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['accion'])) {
    $nombre   = trim($_POST['nombre'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $correo   = trim($_POST['correo'] ?? '');

    $error = validar_campos([
        [$nombre,   'letras',   'Nombre'],
        [$correo,   'email',    'Correo',   false],
        [$telefono, 'telefono', 'Teléfono', false],
    ]);

    if (!$error) {
        try {
            $stmt = $conexion->prepare("UPDATE clientes SET nombre = :nombre, telefono = :telefono, correo = :correo WHERE id = :id");
            $stmt->execute([
                'nombre'   => $nombre,
                'telefono' => $telefono,
                'correo'   => $correo,
                'id'       => $id_cliente
            ]);
            $_SESSION['exito'] = 'Cliente actualizado correctamente.';
            header("Location: editar_cliente.php?id=" . $id_cliente);
            exit();
        } catch (PDOException $e) {
            $error = "Error al actualizar el cliente.";
        }
    } 
}

// Cargar la lista completa de clientes 
$stmt_lista = $conexion->query("SELECT id, nombre, telefono FROM clientes");
$lista_clientes = $stmt_lista->fetchAll();

$cliente = null;
if ($id_cliente) {
    $stmt_sel = $conexion->prepare("SELECT * FROM clientes WHERE id = :id LIMIT 1");
    $stmt_sel->execute(['id' => $id_cliente]);
    $cliente = $stmt_sel->fetch();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modificar Cliente - Malvados y Asociados</title>
    
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

            <!-- Tarjeta Principal -->
            <div class="form-card">
                
                <div class="form-header-card">
                    <div class="icon-box icon-gold-bg d-flex align-items-center justify-content-center" style="background-color: rgba(212, 175, 55, 0.1); color: #D4AF37;">
                        <i class="fa-solid fa-user-pen"></i>
                    </div>
                    <div class="form-title-text">
                        <span class="subtitle" style="color: #D4AF37; font-size: 0.75rem; font-weight: 700; letter-spacing: 0.1em;">GESTIÓN DE CLIENTES</span>
                        <h2 class="mb-1" style="font-family: 'Playfair Display', serif; font-size: 1.75rem;">Modificar o Eliminar Cliente</h2>
                        <p class="mb-0 text-muted" style="font-size: 0.875rem;">Seleccione un perfil para consultar y actualizar su información.</p>
                    </div>
                </div>

                <!-- Formulario de Selección -->
                <form method="GET" action="editar_cliente.php" class="custom-form mb-2">
                    <div class="form-group full-width">
                        <label>Seleccione el Cliente a Modificar</label>
                        <div class="d-flex gap-3">
                            <div class="input-with-icon flex-grow-1">
                                <i class="fa-solid fa-user-tag"></i>
                                <select name="id" onchange="this.form.submit()" required>
                                    <option value="">-- Seleccionar Cliente --</option>
                                    <?php foreach ($lista_clientes as $cli): ?>
                                        <option value="<?php echo $cli['id']; ?>" <?php echo ($id_cliente == $cli['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($cli['nombre']) . ($cli['telefono'] ? " (" . htmlspecialchars($cli['telefono']) . ")" : ""); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <i class="fa-solid fa-chevron-down select-arrow"></i>
                            </div>
                            <button type="submit" class="btn text-white d-flex align-items-center gap-2 px-4" style="background-color: #3b82f6; border: none;">
                                <i class="fa-solid fa-magnifying-glass"></i> Cargar Datos
                            </button>
                        </div>
                    </div>
                </form>

                <?php if ($cliente): ?>
                    <!-- Separador -->
                    <div class="position-relative text-center my-4">
                        <hr class="border-secondary" style="opacity: 0.3;">
                        <span class="position-absolute top-50 start-50 translate-middle px-3 text-muted" style="background-color: var(--bg-panel); font-size: 0.65rem; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase;">Datos del Perfil</span>
                    </div>

                    <!-- Formulario de Edición -->
                    <form method="POST" action="editar_cliente.php?id=<?php echo $id_cliente; ?>" class="custom-form validar-form" novalidate>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>Nombre Completo</label>
                                <div class="input-with-icon">
                                    <i class="fa-regular fa-user"></i>
                                    <input type="text" name="nombre" value="<?php echo htmlspecialchars($cliente['nombre']); ?>" required data-validar="letras" data-label="Nombre">
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Correo Electrónico</label>
                                <div class="input-with-icon">
                                    <i class="fa-regular fa-envelope"></i>
                                    <input type="email" name="correo" value="<?php echo htmlspecialchars($cliente['correo'] ?? ''); ?>" data-validar="email" data-label="Correo">
                                </div>
                            </div>
                        </div>

                        <div class="form-group full-width mt-2">
                            <label>Teléfono de Contacto</label>
                            <div class="input-with-icon">
                                <i class="fa-solid fa-phone"></i>
                                <input type="text" name="telefono" value="<?php echo htmlspecialchars($cliente['telefono'] ?? ''); ?>" data-validar="telefono" data-label="Teléfono">
                            </div>
                        </div>

                        <div class="alert d-flex align-items-center gap-3 mt-4 mb-0" role="alert" style="background-color: rgba(96, 165, 250, 0.05); color: #60a5fa; border: 1px solid rgba(96, 165, 250, 0.2);">
                            <i class="fa-solid fa-lock fs-5"></i>
                            <span style="font-size: 0.875rem;">Los cambios quedarán registrados en el historial de auditoría del sistema.</span>
                        </div>

                        <div class="d-flex justify-content-end align-items-center gap-3 mt-4 pt-4 border-top border-secondary">
                            <a href="panel.php" class="btn btn-outline-secondary text-white px-4 py-2">
                                Cancelar
                            </a>
                            <button type="submit" class="btn btn-success d-flex align-items-center gap-2 px-4 py-2">
                                <i class="fa-solid fa-pen"></i> Guardar Cambios
                            </button>
                        </div>
                    </form>
                    <div class="danger-zone mt-5">
                        <form method="POST" action="editar_cliente.php?id=<?php echo $id_cliente; ?>" onsubmit="return confirm('¿Está seguro de eliminar este cliente?')">
                            <input type="hidden" name="accion" value="eliminar">
                            <button type="submit" class="btn btn-danger d-flex align-items-center gap-2 px-4 py-2" style="background-color: #ef4444; border: none;">
                                <i class="fa-solid fa-trash-can"></i> Eliminar Cliente
                            </button>
                        </form>
                    </div>

                <?php endif; ?>
            </div>
        </div>
    </main>
    <?php
    $exito = $_SESSION['exito'] ?? null;
    unset($_SESSION['exito']);
    notificaciones($error, $exito);
    ?>
</body>
</html>