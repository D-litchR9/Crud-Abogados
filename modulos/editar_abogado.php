<?php
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'admin') {
    header("Location: panel.php");
    exit();
}

require_once '../config/conexion.php';
require_once '../config/seguridad.php';
$error = '';
$id_abogado = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : null;

// proceso de eliminacion
if ($id_abogado && isset($_POST['accion']) && $_POST['accion'] === 'eliminar') {
    try {
        $stmt = $conexion->prepare("DELETE FROM usuarios WHERE id = :id AND rol = 'abogado' LIMIT 1");
        $stmt->execute(['id' => $id_abogado]);
        header("Location: editar_abogado.php");
        exit();
    } catch (PDOException $e) {
        $error = "Error al eliminar el abogado.";
    }
}

// proceso de actualizacion
if ($id_abogado && $_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['accion'])) {
    $nombre    = trim($_POST['nombre']);
    $correo    = trim($_POST['correo']);
    $password  = trim($_POST['password']);
    $pregunta  = $_POST['pregunta'] ?? '';
    $respuesta = normalizar_respuesta($_POST['respuesta'] ?? '');

    if (empty($nombre) || empty($correo)) {
        $error = "Nombre y correo son obligatorios.";
    } elseif (!empty($password) && strlen($password) < 8) {
        $error = "La nueva contraseña debe tener mínimo 8 caracteres.";
    } else {
        try {
            $sql = "UPDATE usuarios SET nombre = :nombre, correo = :correo";
            $params = ['nombre' => $nombre, 'correo' => $correo, 'id' => $id_abogado];

            if (!empty($password)) {
                $sql .= ", password = :password";
                $params['password'] = password_hash($password, PASSWORD_DEFAULT);
            }

            // La pregunta y la respuesta solo se cambian si se escribe una respuesta nueva
            if (!empty($respuesta) && in_array($pregunta, PREGUNTAS_SEGURIDAD, true)) {
                $sql .= ", pregunta_seguridad = :pregunta, respuesta_seguridad = :respuesta";
                $params['pregunta']  = $pregunta;
                $params['respuesta'] = password_hash($respuesta, PASSWORD_DEFAULT);
            }

            $sql .= " WHERE id = :id AND rol = 'abogado'";
            $stmt = $conexion->prepare($sql);
            $stmt->execute($params);

            header("Location: editar_abogado.php?id=" . $id_abogado);
            exit();
        } catch (PDOException $e) {
            $error = "Error al actualizar la informacion.";
        }
    }
}

// lista completa de abogados 
$stmt_lista = $conexion->query("SELECT id, nombre, correo FROM usuarios WHERE rol = 'abogado'");
$lista_abogados = $stmt_lista->fetchAll();

$abogado = null;
if ($id_abogado) {
    $stmt_sel = $conexion->prepare("SELECT * FROM usuarios WHERE id = :id AND rol = 'abogado' LIMIT 1");
    $stmt_sel->execute(['id' => $id_abogado]);
    $abogado = $stmt_sel->fetch();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modificar Abogado - Malvados y Asociados</title>
    
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
            <span class="badge-admin">Admin</span>
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
                        <i class="fa-regular fa-user"></i>
                    </div>
                    <div class="form-title-text">
                        <span class="subtitle" style="color: #D4AF37; font-size: 0.75rem; font-weight: 700; letter-spacing: 0.1em;">GESTIÓN DE PROFESIONALES</span>
                        <h2 class="mb-1" style="font-family: 'Playfair Display', serif; font-size: 1.75rem;">Modificar o Eliminar Abogado</h2>
                        <p class="mb-0 text-muted" style="font-size: 0.875rem;">Seleccione un perfil para consultar y actualizar su información.</p>
                    </div>
                </div>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger d-flex align-items-center gap-2 py-2" role="alert" style="font-size: 0.875rem;">
                        <i class="fa-solid fa-circle-exclamation"></i>
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <!-- Formulario de Seleccion -->
                <form method="GET" action="editar_abogado.php" class="custom-form mb-2">
                    <div class="form-group full-width">
                        <label>Seleccione el Abogado a Modificar</label>
                        <div class="d-flex gap-3">
                            <div class="input-with-icon flex-grow-1">
                                <i class="fa-regular fa-user"></i>
                                <select name="id" onchange="this.form.submit()" required>
                                    <option value="">-- Seleccionar Abogado --</option>
                                    <?php foreach ($lista_abogados as $abg): ?>
                                        <option value="<?php echo $abg['id']; ?>" <?php echo ($id_abogado == $abg['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($abg['nombre']); ?>
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

                <?php if ($abogado): ?>
                    <!-- Separador -->
                    <div class="position-relative text-center my-4">
                        <hr class="border-secondary" style="opacity: 0.3;">
                        <span class="position-absolute top-50 start-50 translate-middle px-3 text-muted" style="background-color: var(--bg-panel); font-size: 0.65rem; font-weight: 700; letter-spacing: 0.1em; text-transform: uppercase;">Datos del Perfil</span>
                    </div>

                    <!-- Formulario de Edicion -->
                    <form method="POST" action="editar_abogado.php?id=<?php echo $id_abogado; ?>" class="custom-form">
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>Nombre Completo</label>
                                <div class="input-with-icon">
                                    <i class="fa-regular fa-user"></i>
                                    <input type="text" name="nombre" value="<?php echo htmlspecialchars($abogado['nombre']); ?>" required>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Correo Electrónico</label>
                                <div class="input-with-icon">
                                    <i class="fa-regular fa-envelope"></i>
                                    <input type="email" name="correo" value="<?php echo htmlspecialchars($abogado['correo']); ?>" required>
                                </div>
                            </div>
                        </div>

                        <!--Campo de contraseña  -->
                        <div class="form-group full-width mt-2">
                            <label>Contraseña (dejar en blanco para mantener la actual)</label>

                                                    <div class="form-row mt-2">
                            <div class="form-group">
                                <label>Pregunta de Seguridad</label>
                                <div class="input-with-icon">
                                    <i class="fa-solid fa-circle-question"></i>
                                    <select name="pregunta">
                                        <?php foreach (PREGUNTAS_SEGURIDAD as $p): ?>
                                            <option value="<?php echo htmlspecialchars($p); ?>" <?php echo ($abogado['pregunta_seguridad'] === $p) ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($p); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <i class="fa-solid fa-chevron-down select-arrow"></i>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Nueva Respuesta (opcional)</label>
                                <div class="input-with-icon">
                                    <i class="fa-solid fa-key"></i>
                                    <input type="text" name="respuesta" placeholder="Dejar en blanco para mantener la actual">
                                </div>
                            </div>
                        </div>

                            <div class="input-with-icon">
                                <i class="fa-solid fa-lock"></i>
                                <input type="password" name="password" placeholder="Nueva contraseña (opcional)">
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

                    <!-- Eliminar -->
                    <div class="danger-zone mt-5">
                        <form method="POST" action="editar_abogado.php?id=<?php echo $id_abogado; ?>" onsubmit="return confirm('¿Está seguro de eliminar este abogado?')">
                            <input type="hidden" name="accion" value="eliminar">
                            <button type="submit" class="btn btn-danger d-flex align-items-center gap-2 px-4 py-2" style="background-color: #ef4444; border: none;">
                                <i class="fa-solid fa-trash-can"></i> Eliminar Abogado
                            </button>
                        </form>
                    </div>

                <?php endif; ?>
            </div>
        </div>
    </main>
</body>
</html>