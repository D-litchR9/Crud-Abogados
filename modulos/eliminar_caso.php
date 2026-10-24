<?php
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../auth/login.php");
    exit();
}
if ($_SESSION['usuario_rol'] !== 'admin') {
    header("Location: panel.php");
    exit();
}

require_once '../config/conexion.php';

if (isset($_GET['id']) && ctype_digit($_GET['id'])) {
    try {
        $stmt = $conexion->prepare("DELETE FROM casos WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => (int)$_GET['id']]);
        if ($stmt->rowCount() > 0) {
            $_SESSION['exito'] = 'Expediente eliminado correctamente.';
        } else {
            $_SESSION['error'] = 'El expediente no existe.';
        }
    } catch (PDOException $e) {
        $_SESSION['error'] = 'No se pudo eliminar el expediente.';
    }
} else {
    $_SESSION['error'] = 'Identificador de expediente no válido.';
}

header("Location: panel.php");
exit();