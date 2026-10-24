<?php
session_start();
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'admin') {
    header("Location: panel.php");
    exit();
}

require_once '../config/conexion.php';
require_once '../config/seguridad.php';
require_once '../config/validaciones.php';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre        = trim($_POST['nombre'] ?? '');
    $correo        = trim($_POST['correo'] ?? '');
    $password      = trim($_POST['password'] ?? '');
    $pregunta      = $_POST['pregunta'] ?? '';
    $respuesta_raw = trim($_POST['respuesta'] ?? '');
    $respuesta     = normalizar_respuesta($respuesta_raw);
    $rol           = 'abogado';

    $error = validar_campos([
        [$nombre,        'letras',       'Nombre'],
        [$correo,        'email',        'Correo'],
        [$password,      'password',     'Contraseña'],
        [$respuesta_raw, 'alfanumerico', 'Respuesta de seguridad'],
    ]);

    if (!$error && !in_array($pregunta, PREGUNTAS_SEGURIDAD, true)) {
        $error = "Seleccione una pregunta de seguridad válida.";
    }

    if (!$error) {
        try {
            $stmt = $conexion->prepare(
                "INSERT INTO usuarios (nombre, correo, password, rol, pregunta_seguridad, respuesta_seguridad)
                 VALUES (:nombre, :correo, :password, :rol, :pregunta, :respuesta)"
            );
            $stmt->execute([
                'nombre'    => $nombre,
                'correo'    => $correo,
                'password'  => password_hash($password, PASSWORD_DEFAULT),
                'rol'       => $rol,
                'pregunta'  => $pregunta,
                'respuesta' => password_hash($respuesta, PASSWORD_DEFAULT)
            ]);
        $_SESSION['exito'] = 'Abogado registrado correctamente.';
            header("Location: panel.php");
            exit();
        } catch (PDOException $e) {
            $error = "Error al registrar el abogado. Es posible que el correo ya exista.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Abogado - Malvados y Asociados</title>
    
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

            <!-- Tarjeta del Formulario -->
            <div class="form-card">
                
                <!-- Encabezado de la tarjeta -->
                <div class="form-header-card">
                    <div class="icon-box icon-gold-bg d-flex align-items-center justify-content-center">
                        <i class="fa-regular fa-user"></i>
                    </div>
                    <div class="form-title-text">
                        <span class="subtitle">GESTIÓN DE PROFESIONALES</span>
                        <h2 class="mb-1">Registrar Nuevo Abogado</h2>
                        <p class="mb-0">Complete la información para crear un nuevo perfil en el sistema.</p>
                    </div>
                </div>


                <form method="POST" action="agregar_abogado.php" class="custom-form validar-form" novalidate>
                    <div class="form-group full-width">
                        <label>Nombre Completo</label>
                        <div class="input-with-icon">
                            <i class="fa-regular fa-user"></i>
                            <input type="text" name="nombre" required data-validar="letras" data-label="Nombre" placeholder="Ej. Andrea Fernandez Lopez">
                        </div>
                    </div>

                    <div class="form-group full-width">
                        <label>Correo Electrónico</label>
                        <div class="input-with-icon">
                            <i class="fa-regular fa-envelope"></i>
                            <input type="email" name="correo" required data-validar="email" data-label="Correo" placeholder="abogado@malvadosyasociados.com">
                        </div>
                    </div>

                    <div class="form-group full-width">
                        <label>Contraseña</label>
                        <div class="input-with-icon">
                            <i class="fa-solid fa-lock"></i>
                            <input type="password" name="password" required data-validar="password" data-label="Contraseña" placeholder="Mínimo 8 caracteres">
                            <i class="fa-regular fa-eye position-absolute" style="right: 1rem; color: #9ca3af; pointer-events: none;"></i>
                        </div>
                    </div>

                                        <div class="form-group full-width">
                        <label>Pregunta de Seguridad</label>
                        <div class="input-with-icon">
                            <i class="fa-solid fa-circle-question"></i>
                            <select name="pregunta" required data-validar="requerido" data-label="Pregunta de seguridad">
                                <option value="">-- Seleccione una pregunta --</option>
                                <?php foreach (PREGUNTAS_SEGURIDAD as $p): ?>
                                    <option value="<?php echo htmlspecialchars($p); ?>"><?php echo htmlspecialchars($p); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <i class="fa-solid fa-chevron-down select-arrow"></i>
                        </div>
                    </div>

                    <div class="form-group full-width">
                        <label>Respuesta de Seguridad</label>
                        <div class="input-with-icon">
                            <i class="fa-solid fa-key"></i>
                            <input type="text" name="respuesta" required data-validar="alfanumerico" data-label="Respuesta de seguridad" placeholder="Para recuperar su contraseña">
                        </div>
                    </div>

                    <!-- Banner de Seguridad -->
                    <div class="alert d-flex align-items-center gap-3 mt-4 mb-0" role="alert" style="background-color: rgba(96, 165, 250, 0.05); color: #60a5fa; border: 1px solid rgba(96, 165, 250, 0.2);">
                        <i class="fa-solid fa-lock fs-5"></i>
                        <span style="font-size: 0.875rem;">La información será almacenada de forma segura y solo podrá ser consultada por administradores autorizados.</span>
                    </div>

                    <!-- Botones de Accion (Alineados a la derecha segun el mockup) -->
                    <div class="d-flex justify-content-end align-items-center gap-3 mt-4 pt-4 border-top border-secondary">
                        <a href="panel.php" class="btn btn-outline-secondary text-white px-4 py-2">
                            Cancelar
                        </a>
                        <button type="submit" class="btn btn-success d-flex align-items-center gap-2 px-4 py-2">
                            <i class="fa-regular fa-user"></i> Guardar Abogado
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>
    <?php notificaciones($error); ?>
</body>
</html>