<?php
session_start();

// validar seccion activa

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../auth/login.php");
    exit();
}

// validar que sea admin
if ($_SESSION['usuario_rol'] !== 'admin') {
    header("Location: panel.php");
    exit();
}

require_once '../config/conexion.php';

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id_caso = (int)$_GET['id'];

    try {

        $stmt = $conexion->prepare("DELETE FROM casos WHERE id = :id LIMIT 1");
        $stmt->execute(['id' => $id_caso]);
        
    } catch (PDOException $e) {
        die("Error al eliminar el caso: " . $e->getMessage());
    }
}

header("Location: panel.php");
exit();
?>