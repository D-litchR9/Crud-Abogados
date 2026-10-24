<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../auth/login.php");
    exit();
}
if ($_SESSION['usuario_rol'] !== 'abogado') {
    header("Location: panel.php");
    exit();
}

require_once '../config/conexion.php';
require_once '../config/validaciones.php';

// Solo los casos asignados a este abogado
$stmt = $conexion->prepare(
    "SELECT c.id, c.titulo, c.descripcion, c.estado, c.fecha_creacion, cl.nombre AS cliente_nombre
     FROM casos c
     INNER JOIN clientes cl ON c.cliente_id = cl.id
     WHERE c.usuario_id = :uid
     ORDER BY c.id DESC"
);
$stmt->execute(['uid' => $_SESSION['usuario_id']]);
$casos = $stmt->fetchAll();

// Contadores por estado
$abiertos = 0; $proceso = 0; $cerrados = 0;
foreach ($casos as $c) {
    $e = strtolower($c['estado']);
    if ($e === 'abierto') $abiertos++;
    elseif ($e === 'en proceso') $proceso++;
    elseif ($e === 'cerrado') $cerrados++;
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
                    <p class="user-role">Abogado</p>
                </div>
            </div>
            <span class="badge-admin">Abogado</span>
            <a href="../auth/logout.php" class="btn-logout">
                <i class="fa-solid fa-arrow-right-from-bracket"></i> Cerrar Sesión
            </a>
        </div>
    </header>

    <main class="dashboard-main">

        <div class="page-header">
            <div class="header-titles">
                <span class="subtitle">Área del Abogado</span>
                <h2>Mis Expedientes</h2>
                <p>Casos que tiene asignados actualmente.</p>
            </div>
        </div>

        <!-- Resumen -->
        <div class="actions-grid">
            <div class="action-card static">
                <div class="card-left">
                    <div class="icon-box icon-gold"><i class="fa-solid fa-folder-open"></i></div>
                    <div class="card-text"><h3><?php echo count($casos); ?> casos</h3><p>Total asignados</p></div>
                </div>
            </div>
            <div class="action-card static">
                <div class="card-left">
                    <div class="icon-box icon-blue"><i class="fa-solid fa-circle-play"></i></div>
                    <div class="card-text"><h3><?php echo $abiertos; ?> abiertos</h3><p>Pendientes de iniciar</p></div>
                </div>
            </div>
            <div class="action-card static">
                <div class="card-left">
                    <div class="icon-box icon-indigo"><i class="fa-solid fa-gavel"></i></div>
                    <div class="card-text"><h3><?php echo $proceso; ?> en proceso</h3><p>En trámite</p></div>
                </div>
            </div>
            <div class="action-card static">
                <div class="card-left">
                    <div class="icon-box icon-gold"><i class="fa-solid fa-circle-check"></i></div>
                    <div class="card-text"><h3><?php echo $cerrados; ?> cerrados</h3><p>Finalizados</p></div>
                </div>
            </div>
        </div>

        <!-- Tabla -->
        <div class="panel-container">
            <div class="panel-header">
                <div class="panel-title">
                    <div class="icon-box icon-gold"><i class="fa-solid fa-briefcase"></i></div>
                    <div>
                        <h3>Casos Asignados</h3>
                        <p>Solo se muestran los expedientes a su cargo</p>
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
                            <th>Cliente</th>
                            <th>Estado</th>
                            <th>Fecha</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($casos)): ?>
                            <tr><td colspan="5" class="empty-state">Aún no tiene casos asignados.</td></tr>
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
                                <td><?php echo htmlspecialchars($caso['cliente_nombre']); ?></td>
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