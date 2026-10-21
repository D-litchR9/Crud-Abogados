<?php
session_start();
require_once '../config/conexion.php';
require_once '../config/seguridad.php';

if (isset($_SESSION['usuario_id'])) {
    header("Location: ../modulos/panel.php");
    exit();
}

const MAX_INTENTOS = 5;

if (!isset($_SESSION['reset_intentos'])) {
    $_SESSION['reset_intentos'] = 0;
}

$error    = '';
$paso     = 1;
$correo   = '';
$pregunta = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    $correo = trim($_POST['correo'] ?? '');

    $stmt = $conexion->prepare(
        "SELECT id, pregunta_seguridad, respuesta_seguridad FROM usuarios WHERE correo = :correo LIMIT 1"
    );
    $stmt->execute(['correo' => $correo]);
    $usuario = $stmt->fetch();

    $tienePregunta = $usuario && !empty($usuario['pregunta_seguridad']) && !empty($usuario['respuesta_seguridad']);

    // ---------- PASO 1: buscar el correo y mostrar la pregunta ----------
    if ($accion === 'buscar') {
        if ($tienePregunta) {
            $paso     = 2;
            $pregunta = $usuario['pregunta_seguridad'];
        } else {
            $error = "No encontramos una cuenta con recuperación disponible para ese correo.";
        }
    }

    // ---------- PASO 2: validar la respuesta y cambiar la contraseña ----------
    if ($accion === 'restablecer') {
        $respuesta = normalizar_respuesta($_POST['respuesta'] ?? '');
        $nueva     = trim($_POST['password'] ?? '');
        $confirmar = trim($_POST['confirmar'] ?? '');

        if (!$tienePregunta) {
            $error = "No fue posible validar la cuenta.";
        } elseif ($_SESSION['reset_intentos'] >= MAX_INTENTOS) {
            $error = "Demasiados intentos fallidos. Cierre el navegador e intente más tarde.";
        } else {
            $paso     = 2;
            $pregunta = $usuario['pregunta_seguridad'];

            if (empty($respuesta) || empty($nueva) || empty($confirmar)) {
                $error = "Por favor, llene todos los campos.";
            } elseif (strlen($nueva) < 8) {
                $error = "La nueva contraseña debe tener mínimo 8 caracteres.";
            } elseif ($nueva !== $confirmar) {
                $error = "Las contraseñas no coinciden.";
            } elseif (!password_verify($respuesta, $usuario['respuesta_seguridad'])) {
                $_SESSION['reset_intentos']++;
                $restantes = MAX_INTENTOS - $_SESSION['reset_intentos'];
                $error = "La respuesta es incorrecta. Intentos restantes: $restantes.";
            } else {
                $upd = $conexion->prepare("UPDATE usuarios SET password = :p WHERE id = :id");
                $upd->execute([
                    'p'  => password_hash($nueva, PASSWORD_DEFAULT),
                    'id' => $usuario['id']
                ]);

                $_SESSION['reset_intentos'] = 0;
                header("Location: login.php?reset=ok");
                exit();
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
    <title>Recuperar Contraseña - Malvados y Asociados</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body class="dark-theme">

    <main class="dashboard-main form-layout auth-layout">
        <div class="form-wrapper auth-wrapper">

            <div class="auth-brand">
                <div class="logo-icon"><i class="fa-solid fa-key"></i></div>
                <h2>Recuperar Contraseña</h2>
                <p>
                    <?php echo ($paso === 1)
                        ? 'Escriba el correo de su cuenta'
                        : 'Responda su pregunta de seguridad'; ?>
                </p>
            </div>

            <div class="form-card">

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger d-flex align-items-center gap-2 py-2" role="alert" style="font-size: 0.875rem;">
                        <i class="fa-solid fa-circle-exclamation"></i>
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <?php if ($paso === 1): ?>

                    <!-- PASO 1 -->
                    <form action="olvide_password.php" method="POST" class="custom-form">
                        <input type="hidden" name="accion" value="buscar">

                        <div class="form-group">
                            <label>Correo Electrónico</label>
                            <div class="input-with-icon">
                                <i class="fa-regular fa-envelope"></i>
                                <input type="email" name="correo" required value="<?php echo htmlspecialchars($correo); ?>" placeholder="usuario@correo.com">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-success w-100 py-2">
                            <i class="fa-solid fa-magnifying-glass"></i> Continuar
                        </button>
                    </form>

                <?php else: ?>

                    <!-- PASO 2 -->
                    <form action="olvide_password.php" method="POST" class="custom-form">
                        <input type="hidden" name="accion" value="restablecer">
                        <input type="hidden" name="correo" value="<?php echo htmlspecialchars($correo); ?>">

                        <div class="form-group">
                            <label>Cuenta</label>
                            <div class="input-with-icon">
                                <i class="fa-solid fa-lock" style="color: #6b7280;"></i>
                                <input type="text" value="<?php echo htmlspecialchars($correo); ?>" readonly
                                       style="background-color: rgba(255,255,255,0.02); color: #9ca3af; cursor: not-allowed;">
                            </div>
                        </div>

                        <div class="form-group">
                            <label><?php echo htmlspecialchars($pregunta); ?></label>
                            <div class="input-with-icon">
                                <i class="fa-solid fa-key"></i>
                                <input type="text" name="respuesta" required placeholder="Su respuesta" autocomplete="off">
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Nueva Contraseña</label>
                            <div class="input-with-icon">
                                <i class="fa-solid fa-lock"></i>
                                <input type="password" name="password" required placeholder="Mínimo 8 caracteres">
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Confirmar Nueva Contraseña</label>
                            <div class="input-with-icon">
                                <i class="fa-solid fa-lock"></i>
                                <input type="password" name="confirmar" required placeholder="Repita la contraseña">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-success w-100 py-2">
                            <i class="fa-solid fa-floppy-disk"></i> Cambiar Contraseña
                        </button>
                    </form>

                <?php endif; ?>

                <div class="auth-footer">
                    <a href="login.php" class="auth-link"><i class="fa-solid fa-chevron-left"></i> Volver al inicio de sesión</a>
                </div>
            </div>
        </div>
    </main>

</body>
</html>