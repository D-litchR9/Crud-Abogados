<?php
session_start();
require_once '../config/conexion.php';

// Si ya está logueado, redirigir al despachador de paneles
if (isset($_SESSION['usuario_id'])) {
    header("Location: ../modulos/panel.php");
    exit();
}

$error = '';
$mensaje = '';

if (isset($_GET['registro']) && $_GET['registro'] === 'ok') {
    $mensaje = "Cuenta creada correctamente. Ya puede iniciar sesión.";
}
if (isset($_GET['reset']) && $_GET['reset'] === 'ok') {
    $mensaje = "Contraseña actualizada. Inicie sesión con la nueva contraseña.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo   = trim($_POST['correo']);
    $password = trim($_POST['password']);

    if (!empty($correo) && !empty($password)) {
        $stmt = $conexion->prepare("SELECT * FROM usuarios WHERE correo = :correo LIMIT 1");
        $stmt->execute(['correo' => $correo]);
        $usuario = $stmt->fetch();

        if ($usuario && password_verify($password, $usuario['password'])) {
            session_regenerate_id(true);

            $_SESSION['usuario_id']     = $usuario['id'];
            $_SESSION['usuario_nombre'] = $usuario['nombre'];
            $_SESSION['usuario_rol']    = $usuario['rol'];
            $_SESSION['cliente_id']     = $usuario['cliente_id']; // solo aplica a clientes

            header("Location: ../modulos/panel.php");
            exit();
        } else {
            $error = "Credenciales incorrectas.";
        }
    } else {
        $error = "Por favor, llenar todos los campos.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Malvados y Asociados</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body class="dark-theme">

    <main class="dashboard-main form-layout auth-layout">
        <div class="form-wrapper auth-wrapper">

            <div class="auth-brand">
                <div class="logo-icon"><i class="fa-solid fa-scale-balanced"></i></div>
                <h2>Malvados y Asociados</h2>
                <p>Inicie sesión para continuar</p>
            </div>

            <div class="form-card">

                <?php if (!empty($mensaje)): ?>
                    <div class="alert alert-success d-flex align-items-center gap-2 py-2" role="alert" style="font-size: 0.875rem;">
                        <i class="fa-solid fa-circle-check"></i>
                        <?php echo htmlspecialchars($mensaje); ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger d-flex align-items-center gap-2 py-2" role="alert" style="font-size: 0.875rem;">
                        <i class="fa-solid fa-circle-exclamation"></i>
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <form action="login.php" method="POST" class="custom-form">

                    <div class="form-group">
                        <label>Correo Electrónico</label>
                        <div class="input-with-icon">
                            <i class="fa-regular fa-envelope"></i>
                            <input type="email" name="correo" required placeholder="usuario@correo.com">
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Contraseña</label>
                        <div class="input-with-icon">
                            <i class="fa-solid fa-lock"></i>
                            <input type="password" name="password" required placeholder="Su contraseña">
                        </div>
                        <div class="text-end mt-2">
                            <a href="olvide_password.php" class="auth-link">¿Olvidó su contraseña?</a>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-success w-100 py-2">
                        <i class="fa-solid fa-right-to-bracket"></i> Ingresar
                    </button>
                </form>

                <div class="auth-footer">
                    ¿No tiene cuenta? <a href="registro.php" class="auth-link">Regístrese aquí</a><br>
                    <a href="../index.php" class="auth-link d-inline-block mt-2"><i class="fa-solid fa-chevron-left"></i> Volver al inicio</a>
                </div>
            </div>
        </div>
    </main>

</body>
</html>