<?php
session_start();
require_once '../config/conexion.php';
require_once '../config/seguridad.php';
require_once '../config/validaciones.php';

if (isset($_SESSION['usuario_id'])) {
    header("Location: ../modulos/panel.php");
    exit();
}

$error = '';
$nombre = '';
$correo = '';
$pregunta = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre        = trim($_POST['nombre'] ?? '');
    $correo        = trim($_POST['correo'] ?? '');
    $password      = trim($_POST['password'] ?? '');
    $confirmar     = trim($_POST['confirmar'] ?? '');
    $pregunta      = $_POST['pregunta'] ?? '';
    $respuesta_raw = trim($_POST['respuesta'] ?? '');
    $respuesta     = normalizar_respuesta($respuesta_raw);

    $error = validar_campos([
        [$nombre,        'letras',       'Nombre'],
        [$correo,        'email',        'Correo'],
        [$password,      'password',     'Contraseña'],
        [$confirmar,     'requerido',    'Confirmar contraseña'],
        [$respuesta_raw, 'alfanumerico', 'Respuesta de seguridad'],
    ]);

    if (!$error && $password !== $confirmar) {
        $error = "Las contraseñas no coinciden.";
    }
    if (!$error && !in_array($pregunta, PREGUNTAS_SEGURIDAD, true)) {
        $error = "Seleccione una pregunta de seguridad.";
    }

    if (!$error) {
        // Verificar que el correo no exista
        $stmt = $conexion->prepare("SELECT id FROM usuarios WHERE correo = :correo LIMIT 1");
        $stmt->execute(['correo' => $correo]);

        if ($stmt->fetch()) {
            $error = "Ya existe una cuenta con ese correo.";
        } else {
            try {
                $conexion->beginTransaction();

                // 1. Perfil en la tabla clientes
                $stmtCli = $conexion->prepare("INSERT INTO clientes (nombre, correo) VALUES (:nombre, :correo)");
                $stmtCli->execute(['nombre' => $nombre, 'correo' => $correo]);
                $cliente_id = $conexion->lastInsertId();

                // 2. Usuario con rol cliente, enlazado al perfil
                $stmtUsr = $conexion->prepare(
                    "INSERT INTO usuarios (nombre, correo, password, rol, cliente_id, pregunta_seguridad, respuesta_seguridad)
                     VALUES (:nombre, :correo, :password, 'cliente', :cliente_id, :pregunta, :respuesta)"
                );
                $stmtUsr->execute([
                    'nombre'     => $nombre,
                    'correo'     => $correo,
                    'password'   => password_hash($password, PASSWORD_DEFAULT),
                    'cliente_id' => $cliente_id,
                    'pregunta'   => $pregunta,
                    'respuesta'  => password_hash($respuesta, PASSWORD_DEFAULT)
                ]);

                $conexion->commit();

                header("Location: login.php?registro=ok");
                exit();
            } catch (PDOException $e) {
                if ($conexion->inTransaction()) {
                    $conexion->rollBack();
                }
                $error = "No se pudo completar el registro. Intente de nuevo.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Cuenta - Malvados y Asociados</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body class="dark-theme">

    <main class="dashboard-main form-layout auth-layout">
        <div class="form-wrapper auth-wrapper wide">

            <div class="auth-brand">
                <div class="logo-icon"><i class="fa-solid fa-scale-balanced"></i></div>
                <h2>Crear Cuenta</h2>
                <p>Regístrese para consultar el estado de sus casos</p>
            </div>

            <div class="form-card">

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger d-flex align-items-center gap-2 py-2" role="alert" style="font-size: 0.875rem;">
                        <i class="fa-solid fa-circle-exclamation"></i>
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <form action="registro.php" method="POST" class="custom-form validar-form" novalidate>

                    <div class="form-group">
                        <label>Nombre Completo</label>
                        <div class="input-with-icon">
                            <i class="fa-regular fa-user"></i>
                            <input type="text" name="nombre" required data-validar="letras" data-label="Nombre" value="<?php echo htmlspecialchars($nombre); ?>" placeholder="Ej. Santiago Mendoza Ruiz">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Correo Electrónico</label>
                        <div class="input-with-icon">
                            <i class="fa-regular fa-envelope"></i>
                            <input type="email" name="correo" required data-validar="email" data-label="Correo" value="<?php echo htmlspecialchars($correo); ?>" placeholder="cliente@correo.com">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Contraseña</label>
                            <div class="input-with-icon">
                                <i class="fa-solid fa-lock"></i>
                                <input type="password" name="password" required data-validar="password" data-label="Contraseña" placeholder="Mínimo 8 caracteres">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Confirmar Contraseña</label>
                            <div class="input-with-icon">
                                <i class="fa-solid fa-lock"></i>
                                <input type="password" name="confirmar" required data-validar="requerido" data-label="Confirmar contraseña" placeholder="Repita la contraseña">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Pregunta de Seguridad</label>
                        <div class="input-with-icon">
                            <i class="fa-solid fa-circle-question"></i>
                            <select name="pregunta" required data-validar="requerido" data-label="Pregunta de seguridad">
                                <option value="">-- Seleccione una pregunta --</option>
                                <?php foreach (PREGUNTAS_SEGURIDAD as $p): ?>
                                    <option value="<?php echo htmlspecialchars($p); ?>" <?php echo ($pregunta === $p) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($p); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <i class="fa-solid fa-chevron-down select-arrow"></i>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Respuesta</label>
                        <div class="input-with-icon">
                            <i class="fa-solid fa-key"></i>
                            <input type="text" name="respuesta" required data-validar="alfanumerico" data-label="Respuesta" placeholder="Le servirá para recuperar su contraseña">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-success w-100 py-2">
                        <i class="fa-solid fa-user-plus"></i> Crear Cuenta
                    </button>
                </form>

                <div class="auth-footer">
                    ¿Ya tiene cuenta? <a href="login.php" class="auth-link">Inicie sesión</a>
                </div>
            </div>
        </div>
    </main>
    <?php notificaciones($error); ?>
    <script>
    document.querySelector('.validar-form').addEventListener('submit', function (e) {
        if (this.password.value !== this.confirmar.value) {
            e.preventDefault();
            showToast('Las contraseñas no coinciden.');
        }
    });
    </script>
</body>
</html>