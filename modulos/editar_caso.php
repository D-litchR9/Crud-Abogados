<?php
session_start();

// CONTROL DE SEGURIDAD: Solo el Administrador puede editar o borrar casos
if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] !== 'admin') {
    header("Location: panel.php?error=no_autorizado");
    exit();
}

require_once '../config/conexion.php';
require_once '../config/validaciones.php';

// Verificar que se haya enviado un ID valido por la URL
if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
    header("Location: panel.php");
    exit();
}
$id_caso = (int)$_GET['id'];
$error = '';

/* ==========================================================================
   1. ACCION: ELIMINAR CASO (Si se presiona el boton de borrar)
   ========================================================================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion_eliminar'])) {
    $stmt_del = $conexion->prepare("DELETE FROM casos WHERE id = :id");
    $stmt_del->execute(['id' => $id_caso]);
    
    $_SESSION['exito'] = 'Expediente eliminado correctamente.';
    header("Location: panel.php");
    exit();
}

/* ==========================================================================
   2. ACCION: ACTUALIZAR CASO (Si se presiona el boton de guardar cambios)
   ========================================================================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion_guardar'])) {
    $titulo      = trim($_POST['titulo'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $estado      = $_POST['estado'] ?? '';
    $abogado_id  = $_POST['usuario_id'] ?? '';

    $error = validar_campos([
        [$titulo,     'texto',  'Título del caso'],
        [$abogado_id, 'numero', 'Abogado asignado', false],
    ]);
    if (!$error && !in_array($estado, ['Abierto', 'En Proceso', 'Cerrado'], true)) {
        $error = "Seleccione un estado válido.";
    }

    if (!$error) {
        $stmt_upd = $conexion->prepare("UPDATE casos 
                                        SET titulo = :titulo, 
                                            descripcion = :descripcion, 
                                            estado = :estado, 
                                            usuario_id = :usuario_id 
                                        WHERE id = :id");
        $stmt_upd->execute([
            'titulo'      => $titulo,
            'descripcion' => $descripcion,
            'estado'      => $estado,
            'usuario_id'  => !empty($abogado_id) ? $abogado_id : null,
            'id'          => $id_caso
        ]);

        $_SESSION['exito'] = 'Expediente actualizado correctamente.';
        header("Location: panel.php");
        exit();
    }
}

/* ==========================================================================
   3. CARGAR DATOS EN EL FORMULARIO (Se ejecuta al abrir la pagina)
   ========================================================================== */
// Obtener los datos actuales del caso e incluir el nombre del cliente (Solo lectura para el cliente)
$stmt_caso = $conexion->prepare("SELECT c.*, cl.nombre AS cliente_nombre 
                                 FROM casos c 
                                 INNER JOIN clientes cl ON c.cliente_id = cl.id 
                                 WHERE c.id = :id LIMIT 1");
$stmt_caso->execute(['id' => $id_caso]);
$caso = $stmt_caso->fetch();

// Si el caso no existe en la BD, regresar al panel
if (!$caso) {
    header("Location: panel.php");
    exit();
}

// Cargar la lista de abogados disponibles para el menu desplegable
$abogados = $conexion->query("SELECT id, nombre FROM usuarios WHERE rol = 'abogado'")->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Expediente N° <?php echo str_pad($caso['id'], 3, '0', STR_PAD_LEFT); ?> - Malvados y Asociados</title>
    
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
                
                <div class="d-flex justify-content-between align-items-start mb-4 pb-2">
                    <div class="d-flex gap-3">
                        <div class="icon-box icon-gold-bg d-flex align-items-center justify-content-center" style="background-color: rgba(212, 175, 55, 0.1); color: #D4AF37;">
                            <i class="fa-regular fa-file-lines"></i>
                        </div>
                        <div class="form-title-text">
                            <span class="subtitle" style="color: #D4AF37; font-size: 0.75rem; font-weight: 700; letter-spacing: 0.1em;">EDICIÓN DE EXPEDIENTE</span>
                            <h2 class="mb-1" style="font-family: 'Playfair Display', serif; font-size: 1.75rem;">
                                Modificar Expediente N° <?php echo str_pad($caso['id'], 3, '0', STR_PAD_LEFT); ?>
                            </h2>
                            <p class="mb-0 text-muted" style="font-size: 0.875rem;">Actualice los datos operativos y el estado actual del caso.</p>
                        </div>
                    </div>
                    
                    <!-- Etiqueta del Estado Actual -->
                    <div>
                        <?php 
                            $estadoActual = strtolower($caso['estado']);
                            $clasePunto = "status-default";
                            if ($estadoActual == 'abierto') $clasePunto = "status-open";
                            if ($estadoActual == 'en proceso') $clasePunto = "status-process";
                        ?>
                        <span class="status-badge" style="background-color: #111A24; border-color: #1f2937;">
                            <span class="status-dot <?php echo $clasePunto; ?>"></span>
                            <?php echo htmlspecialchars($caso['estado']); ?>
                        </span>
                    </div>
                </div>

                <form action="editar_caso.php?id=<?php echo $caso['id']; ?>" method="POST" class="custom-form validar-form" novalidate>
                    
                    <!-- Cliente Asociado (Solo lectura) -->
                    <div class="form-group full-width">
                        <label>Cliente Asociado</label>
                        <div class="input-with-icon">
                            <i class="fa-solid fa-lock" style="color: #6b7280;"></i>
                            <input type="text" value="<?php echo htmlspecialchars($caso['cliente_nombre']); ?>" readonly 
                                   style="background-color: rgba(255, 255, 255, 0.02); color: #9ca3af; cursor: not-allowed; border-color: #1f2937;">
                        </div>
                    </div>

                    <div class="form-group full-width">
                        <label>Título del Caso</label>
                        <div class="input-with-icon">
                            <i class="fa-regular fa-file-lines"></i>
                            <input type="text" name="titulo" value="<?php echo htmlspecialchars($caso['titulo']); ?>" required data-validar="texto" data-label="Título del caso">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Abogado Asignado</label>
                            <div class="input-with-icon">
                                <i class="fa-regular fa-user"></i>
                                <select name="usuario_id" data-validar="numero" data-label="Abogado asignado">
                                    <option value="">-- Sin asignar --</option>
                                    <?php foreach ($abogados as $abg): ?>
                                        <option value="<?php echo $abg['id']; ?>" <?php echo ($caso['usuario_id'] == $abg['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($abg['nombre']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <i class="fa-solid fa-chevron-down select-arrow"></i>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Estado del Expediente</label>
                            <div class="input-with-icon">
                                <i class="fa-solid fa-briefcase"></i>
                                <select name="estado" required data-validar="requerido" data-label="Estado">
                                    <option value="Abierto" <?php echo ($caso['estado'] === 'Abierto') ? 'selected' : ''; ?>>Abierto</option>
                                    <option value="En Proceso" <?php echo ($caso['estado'] === 'En Proceso') ? 'selected' : ''; ?>>En Proceso</option>
                                    <option value="Cerrado" <?php echo ($caso['estado'] === 'Cerrado') ? 'selected' : ''; ?>>Cerrado</option>
                                </select>
                                <i class="fa-solid fa-chevron-down select-arrow"></i>
                            </div>
                        </div>
                    </div>

                    <div class="form-group full-width">
                        <label>Descripción</label>
                        <textarea name="descripcion" rows="5"><?php echo htmlspecialchars($caso['descripcion']); ?></textarea>
                    </div>

                    <div class="d-flex justify-content-end align-items-center gap-3 mt-4 pt-4 border-top border-secondary">
                        <a href="panel.php" class="btn btn-outline-secondary text-white px-4 py-2">
                            Cancelar
                        </a>    
                        <button type="submit" name="accion_guardar" class="btn btn-success d-flex align-items-center gap-2 px-4 py-2">
                            <i class="fa-solid fa-pen"></i> Guardar Cambios
                        </button>
                    </div>
                </form>
                <div class="danger-zone mt-5">
                    <form action="editar_caso.php?id=<?php echo $caso['id']; ?>" method="POST" onsubmit="return confirm('¿Está seguro de eliminar definitivamente este caso?');">
                        <button type="submit" name="accion_eliminar" class="btn btn-danger d-flex align-items-center gap-2 px-4 py-2" style="background-color: #ef4444; border: none;">
                            <i class="fa-solid fa-trash-can"></i> Eliminar Caso Definitivamente
                        </button>
                    </form>
                </div>

            </div>
        </div>
    </main>
    <?php notificaciones($error); ?>
</body>
</html>