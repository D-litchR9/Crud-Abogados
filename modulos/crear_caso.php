<?php
session_start();

// Control estricto: Solo el Administrador crea expedientes
if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] !== 'admin') {
    header("Location: panel.php?error=no_autorizado");
    exit();
}

require_once '../config/conexion.php';

// 1. Cargar abogados disponibles para el select
$abogados = $conexion->query("SELECT id, nombre FROM usuarios WHERE rol = 'abogado'")->fetchAll();

// 2. Cargar clientes disponibles para el select
$clientes = $conexion->query("SELECT id, nombre FROM clientes")->fetchAll();

// 3. Procesar el formulario al enviar
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = trim($_POST['titulo']);
    $descripcion = trim($_POST['descripcion']);
    $abogado_id = $_POST['usuario_id'];
    $cliente_id = $_POST['cliente_id'];

    if (!empty($titulo) && !empty($cliente_id)) {
        $stmt = $conexion->prepare("INSERT INTO casos (titulo, descripcion, usuario_id, cliente_id) VALUES (:titulo, :descripcion, :usuario_id, :cliente_id)");
        $stmt->execute([
            'titulo' => $titulo,
            'descripcion' => $descripcion,
            'usuario_id' => !empty($abogado_id) ? $abogado_id : null,
            'cliente_id' => $cliente_id
        ]);

        header("Location: panel.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Abrir Expediente - Malvados y Asociados</title>
    
    <!-- Fuentes e Iconos -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- BOOTSTRAP 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- TU CSS PERSONALIZADO -->
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
            <!-- Tarjeta del Formulario -->
            <div class="form-card">
                
                <!-- Encabezado de la tarjeta -->
                <div class="form-header-card">
                    <div class="icon-box icon-gold-bg d-flex align-items-center justify-content-center">
                        <i class="fa-solid fa-briefcase"></i>
                    </div>
                    <div class="form-title-text">
                        <span class="subtitle">NUEVO EXPEDIENTE</span>
                        <h2 class="mb-1">Abrir Nuevo Caso Jurídico</h2>
                        <p class="mb-0">Registre la información inicial para generar un nuevo número de expediente.</p>
                    </div>
                </div>

                <form action="crear_caso.php" method="POST" class="custom-form">
                    
                    <div class="form-group full-width">
                        <label>Titulo del Caso</label>
                        <div class="input-with-icon">
                            <i class="fa-regular fa-file-lines"></i>
                            <input type="text" name="titulo" required placeholder="Ej. Reclamación por incumplimiento contractual">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Cliente</label>
                            <div class="input-with-icon">
                                <i class="fa-solid fa-user-plus"></i>
                                <select name="cliente_id" required>
                                    <option value="">Seleccione un cliente</option>
                                    <?php foreach ($clientes as $cli): ?>
                                        <option value="<?php echo $cli['id']; ?>"><?php echo htmlspecialchars($cli['nombre']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <i class="fa-solid fa-chevron-down select-arrow"></i>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Abogado Asignado </label>
                            <div class="input-with-icon">
                                <i class="fa-regular fa-user"></i>
                                <select name="usuario_id">
                                    <option value="">Sin asignar por el momento</option>
                                    <?php foreach ($abogados as $abg): ?>
                                        <option value="<?php echo $abg['id']; ?>"><?php echo htmlspecialchars($abg['nombre']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <i class="fa-solid fa-chevron-down select-arrow"></i>
                            </div>
                        </div>
                    </div>

                    <div class="form-group full-width">
                        <label>Descripcion del Caso</label>
                        <textarea name="descripcion" rows="5" placeholder="Describa los antecedentes, hechos relevantes y alcance del caso..."></textarea>
                    </div>

                    <!-- Banner de Informacion usando Bootstrap -->
                    <div class="alert d-flex align-items-center gap-3 mt-4 mb-0" role="alert" style="background-color: rgba(96, 165, 250, 0.05); color: #60a5fa; border: 1px solid rgba(96, 165, 250, 0.2);">
                        <i class="fa-regular fa-file-lines fs-5"></i>
                        <span style="font-size: 0.875rem;">Al registrar el caso se generará automáticamente un número de expediente único.</span>
                    </div>

                    <!-- Botones de Accion usando Bootstrap -->
                    <div class="d-flex justify-content-between align-items-center mt-4 pt-4 border-top border-secondary">
                        <a href="panel.php" class="btn btn-outline-secondary text-white d-flex align-items-center gap-2">
                            <i class="fa-solid fa-chevron-left"></i> Volver al Panel
                        </a>
                        <button type="submit" class="btn btn-success d-flex align-items-center gap-2 px-4 py-2">
                            <i class="fa-solid fa-briefcase"></i> Registrar Caso
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>
</body>
</html>