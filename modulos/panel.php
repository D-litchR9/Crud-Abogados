<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

require_once '../config/conexion.php';

$sql = "SELECT c.id, c.titulo, c.estado, c.fecha_creacion, 
               u.nombre AS abogado_nombre, cl.nombre AS cliente_nombre 
        FROM casos c
        LEFT JOIN usuarios u ON c.usuario_id = u.id
        INNER JOIN clientes cl ON c.cliente_id = cl.id";


$stmt = $conexion->query($sql);
$casos = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel del Bufete - Malvados y Asociados</title>
    
    <!-- Fuentes e Iconos -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Referencia al archivo CSS (Ajusta la ruta si es necesario) -->
    <link rel="stylesheet" href="../css/styles.css">
</head>

<body class="dark-theme">

    <!-- HEADER / TOPBAR -->
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
                    <p class="user-role">
                        <?php echo $_SESSION['usuario_rol'] === 'admin' ? 'Administrador del sistema' : 'Usuario estándar'; ?>
                    </p>
                </div>
            </div>
            <?php if ($_SESSION['usuario_rol'] === 'admin'): ?>
                <span class="badge-admin">Admin</span>
            <?php endif; ?>
            <a href="../auth/logout.php" class="btn-logout">
                <i class="fa-solid fa-arrow-right-from-bracket"></i> Cerrar Sesión
            </a>
        </div>
    </header>

    <!-- MAIN CONTENT -->
    <main class="dashboard-main">
        
        <!-- Título y Fecha -->
        <div class="page-header">
            <div class="header-titles">
                <span class="subtitle">Panel de Control</span>
                <h2>Sistema de Gestión Jurídica</h2>
                <p>Administre expedientes, profesionales y clientes desde un solo lugar.</p>
            </div>
            <div class="header-date">
                <div class="date-info">
                    <span class="date-label">Hoy</span>
                    <p class="date-value">
                        <?php 
                            setlocale(LC_TIME, 'es_CO.UTF-8', 'es_CO', 'Spanish_Colombia', 'es_ES.UTF-8', 'esp');
                            echo strftime("%d de %B, %Y"); 
                        ?>
                    </p>
                </div>
                <div class="date-divider"></div>
            </div>
        </div>

        <!-- Tarjetas de Acciones (Solo Admin) -->
        <?php if ($_SESSION['usuario_rol'] === 'admin'): ?>
        <div class="actions-grid">
            
            <a href="crear_caso.php" class="action-card card-primary">
                <div class="card-left">
                    <div class="icon-box icon-gold"><i class="fa-solid fa-plus"></i></div>
                    <div class="card-text">
                        <h3>Registrar Nuevo Caso</h3>
                        <p>Crear un expediente jurídico</p>
                    </div>
                </div>
                <i class="fa-solid fa-chevron-right arrow"></i>
            </a>

            <a href="agregar_abogado.php" class="action-card">
                <div class="card-left">
                    <div class="icon-box icon-blue"><i class="fa-solid fa-user-plus"></i></div>
                    <div class="card-text">
                        <h3>Registrar Abogado</h3>
                        <p>Agregar profesional</p>
                    </div>
                </div>
                <i class="fa-solid fa-chevron-right arrow"></i>
            </a>

            <a href="editar_abogado.php" class="action-card">
                <div class="card-left">
                    <div class="icon-box icon-blue"><i class="fa-solid fa-pen"></i></div>
                    <div class="card-text">
                        <h3>Editar Abogado</h3>
                        <p>Gestionar profesionales</p>
                    </div>
                </div>
                <i class="fa-solid fa-chevron-right arrow"></i>
            </a>

            <a href="agregar_cliente.php" class="action-card">
                <div class="card-left">
                    <div class="icon-box icon-indigo"><i class="fa-solid fa-user-tie"></i></div>
                    <div class="card-text">
                        <h3>Registrar Cliente</h3>
                        <p>Crear nuevo perfil</p>
                    </div>
                </div>
                <i class="fa-solid fa-chevron-right arrow"></i>
            </a>

            <a href="editar_cliente.php" class="action-card">
                <div class="card-left">
                    <div class="icon-box icon-indigo"><i class="fa-solid fa-user-pen"></i></div>
                    <div class="card-text">
                        <h3>Editar Cliente</h3>
                        <p>Actualizar información</p>
                    </div>
                </div>
                <i class="fa-solid fa-chevron-right arrow"></i>
            </a>

        </div>
        <!--Aca se pueden agregar nuevas opciones para los abogados siguiendo el if que se creo con la session-->
        <?php endif; ?>

        <!-- Contenedor Principal de la Tabla -->
        <div class="panel-container">
            <div class="panel-header">
                <div class="panel-title">
                    <div class="icon-box icon-gold"><i class="fa-solid fa-folder-open"></i></div>
                    <div>
                        <h3>Expedientes y Casos Activos</h3>
                        <p>Vista general de todos los expedientes registrados</p>
                    </div>
                </div>
                <div class="panel-tools">
                    <div class="search-box">
                        <i class="fa-solid fa-search"></i>
                        <input type="text" placeholder="Buscar expediente...">
                    </div>
                    <span class="counter-badge"><?php echo count($casos); ?> expedientes</span>
                </div>
            </div>

            <!-- Tabla de Casos -->
            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>N° Expediente</th>
                            <th>Título</th>
                            <th>Cliente</th>
                            <th>Abogado Asignado</th>
                            <th>Estado</th>
                            <?php if ($_SESSION['usuario_rol'] === 'admin'): ?>
                            <th class="text-right">Acciones</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($casos as $caso): ?>
                            <tr>
                                <td class="text-gold font-medium">#<?php echo str_pad($caso['id'], 3, '0', STR_PAD_LEFT); ?></td>
                                <td class="text-white font-semibold"><?php echo htmlspecialchars($caso['titulo']); ?></td>
                                <td><?php echo htmlspecialchars($caso['cliente_nombre']); ?></td>
                                <td>
                                    <div class="lawyer-cell">
                                        <div class="lawyer-avatar">
                                            <?php echo strtoupper(substr($caso['abogado_nombre'] ?? 'S', 0, 1)); ?>
                                        </div>
                                        <span><?php echo htmlspecialchars($caso['abogado_nombre'] ?? 'Sin asignar'); ?></span>
                                    </div>
                                </td>
                                <td>
                                    <?php 
                                        $estado = strtolower($caso['estado']);
                                        $claseEstado = "status-default";
                                        if ($estado == 'abierto') $claseEstado = "status-open";
                                        if ($estado == 'en proceso') $claseEstado = "status-process";
                                    ?>
                                    <span class="status-badge">
                                        <span class="status-dot <?php echo $claseEstado; ?>"></span>
                                        <?php echo ucfirst($caso['estado']); ?>
                                    </span>
                                </td>
                                <?php if ($_SESSION['usuario_rol'] === 'admin'): ?>
                                <td class="actions-cell text-right">
                                    <a href="editar_caso.php?id=<?php echo $caso['id']; ?>" class="btn-action edit">
                                        <i class="fa-solid fa-pen"></i> Reasignar/Editar
                                    </a>
                                    <a href="eliminar_caso.php?id=<?php echo $caso['id']; ?>" onclick="return confirm('¿Archivar/Eliminar caso?')" class="btn-action delete">
                                        <i class="fa-solid fa-trash"></i> Eliminar
                                    </a>
                                </td>
                                <?php endif; ?>
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