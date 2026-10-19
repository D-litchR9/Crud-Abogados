<?php
$host     = 'localhost';
$db_name  = 'bd_Abogados'; 
$username = 'root';            
$password = 'nilominino';                 

try {
    $conexion = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8mb4", $username, $password);
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conexion->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    echo "Conexión exitosa con la base de datos: $db_name"; 

    } catch (PDOException $e) {
    die("Error crítico de conexión: " . $e->getMessage());
}
?>
