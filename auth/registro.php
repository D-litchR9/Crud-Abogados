<?php
session_start();
require_once '../config/conexion.php';
require_once '../config/seguridad.php';

if (isset($_SESSION['usuario_id'])) {
    header("Location: ../modulos/panel.php");
    exit();
}

$error = '';
$nombre = '';
$correo = '';
$pregunta = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre    = trim($_POST['nombre'] ?? '');
    $correo    = trim($_POST['correo'] ?? '');
    $password  = trim($_POST['password'] ?? '');
    $confirmar = trim($_POST['confirmar'] ?? '');
    $pregunta  = $_POST['pregunta'] ?? '';
    $respuesta = normalizar_respuesta($_POST['respuesta'] ?? '');

    if (empty($nombre) || empty($correo) || empty($password) || empty($confirmar)) {
        $error = "Por favor, llene todos los campos.";
    } elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $error = "El correo electrónico no es válido.";
    } elseif (strlen($password) < 8) {
        $error = "La contraseña debe tener mínimo 8 caracteres.";
    } elseif ($password !== $confirmar) {
        $error = "Las contraseñas no coinciden.";
    } elseif (!in_array($pregunta, PREGUNTAS_SEGURIDAD, true)) {
        $error = "Seleccione una pregunta de seguridad.";
    } elseif (empty($respuesta)) {
        $error = "Escriba la respuesta a su pregunta de seguridad.";
    } else {
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

                <form action="registro.php" method="POST" class="custom-form">

                    <div class="form-group">
                        <label>Nombre Completo</label>
                        <div class="input-with-icon">
                            <i class="fa-regular fa-user"></i>
                            <input type="text" name="nombre" required value="<?php echo htmlspecialchars($nombre); ?>" placeholder="Ej. Santiago Mendoza Ruiz">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Correo Electrónico</label>
                        <div class="input-with-icon">
                            <i class="fa-regular fa-envelope"></i>
                            <input type="email" name="correo" required value="<?php echo htmlspecialchars($correo); ?>" placeholder="cliente@correo.com">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Contraseña</label>
                            <div class="input-with-icon">
                                <i class="fa-solid fa-lock"></i>
                                <input type="password" name="password" required placeholder="Mínimo 8 caracteres">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Confirmar Contraseña</label>
                            <div class="input-with-icon">
                                <i class="fa-solid fa-lock"></i>
                                <input type="password" name="confirmar" required placeholder="Repita la contraseña">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Pregunta de Seguridad</label>
                        <div class="input-with-icon">
                            <i class="fa-solid fa-circle-question"></i>
                            <select name="pregunta" required>
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
                            <input type="text" name="respuesta" required placeholder="Le servirá para recuperar su contraseña">
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

</body>
</html>