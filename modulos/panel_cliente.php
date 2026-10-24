<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../auth/login.php");
    exit();
}
if ($_SESSION['usuario_rol'] !== 'cliente') {
    header("Location: panel.php");
    exit();
}

require_once '../config/conexion.php';
require_once '../config/validaciones.php';

$casos = [];
$cliente_id = $_SESSION['cliente_id'] ?? null;

// Solo los casos del cliente enlazado a este usuario
if ($cliente_id) {
    $stmt = $conexion->prepare(
        "SELECT c.id, c.titulo, c.descripcion, c.estado, c.fecha_creacion, u.nombre AS abogado_nombre
         FROM casos c
         LEFT JOIN usuarios u ON c.usuario_id = u.id
         WHERE c.cliente_id = :cid
         ORDER BY c.id DESC"
    );
    $stmt->execute(['cid' => $cliente_id]);
    $casos = $stmt->fetchAll();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Casos - Malvados y Asociados</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../css/styles.css">
</head>

<body class="dark-theme">

    <header class="topbar">
        <div class="topbar-left">
            <div class="logo-icon"><i class="fa-solid fa-scale-balanced"></i></div>
            <div class="brand-text">
                <h1>Malvados y Asociados</h1>
                <p>Gestión de expedientes</p>
            </div>
        </div>
        <div class="topbar-right">
            <div class="user-profile">
                <div class="user-details">
                    <p class="user-name"><?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?></p>
                    <p class="user-role">Cliente</p>
                </div>
            </div>
            <span class="badge-admin">Cliente</span>
            <a href="../auth/logout.php" class="btn-logout">
                <i class="fa-solid fa-arrow-right-from-bracket"></i> Cerrar Sesión
            </a>
        </div>
    </header>

    <main class="dashboard-main">

        <div class="page-header">
            <div class="header-titles">
                <span class="subtitle">Portal del Cliente</span>
                <h2>Estado de mis Casos</h2>
                <p>Consulte el avance de sus expedientes y el abogado a cargo.</p>
            </div>
        </div>

        <div class="panel-container">
            <div class="panel-header">
                <div class="panel-title">
                    <div class="icon-box icon-gold"><i class="fa-solid fa-folder-open"></i></div>
                    <div>
                        <h3>Mis Expedientes</h3>
                        <p>Vista de solo lectura</p>
                    </div>
                </div>
                <div class="panel-tools">
                    <span class="counter-badge"><?php echo count($casos); ?> expedientes</span>
                </div>
            </div>

            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>N° Expediente</th>
                            <th>Título</th>
                            <th>Abogado Asignado</th>
                            <th>Estado</th>
                            <th>Fecha</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($casos)): ?>
                            <tr><td colspan="5" class="empty-state">Todavía no tiene expedientes registrados.</td></tr>
                        <?php endif; ?>

                        <?php foreach ($casos as $caso): ?>
                            <?php
                                $estado = strtolower($caso['estado']);
                                $claseEstado = "status-default";
                                if ($estado == 'abierto') $claseEstado = "status-open";
                                if ($estado == 'en proceso') $claseEstado = "status-process";
                            ?>
                            <tr>
                                <td class="text-gold font-medium">#<?php echo str_pad($caso['id'], 3, '0', STR_PAD_LEFT); ?></td>
                                <td class="text-white font-semibold">
                                    <?php echo htmlspecialchars($caso['titulo']); ?>
                                    <?php if (!empty($caso['descripcion'])): ?>
                                        <span class="case-desc"><?php echo htmlspecialchars(mb_strimwidth($caso['descripcion'], 0, 90, '...')); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="lawyer-cell">
                                        <div class="lawyer-avatar">
                                            <?php echo strtoupper(substr($caso['abogado_nombre'] ?? 'S', 0, 1)); ?>
                                        </div>
                                        <span><?php echo htmlspecialchars($caso['abogado_nombre'] ?? 'Sin asignar'); ?></span>
                                    </div>
                                </td>
                                <td>
                                    <span class="status-badge">
                                        <span class="status-dot <?php echo $claseEstado; ?>"></span>
                                        <?php echo htmlspecialchars(ucfirst($caso['estado'])); ?>
                                    </span>
                                </td>
                                <td><?php echo $caso['fecha_creacion'] ? date('d/m/Y', strtotime($caso['fecha_creacion'])) : '-'; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="panel-footer">
                <p>Mostrando <?php echo count($casos); ?> expedientes</p>
                <div class="pagination-current">1</div>
            </div>
        </div>
    </main>
</body>
</html>