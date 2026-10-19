<?php
session_start();

// limpiar variables de seccion
$_SESSION = array();

// borrar la cookie  si existen
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(), 
        '', 
        time() - 42000,
        $params["path"], 
        $params["domain"],
        $params["secure"], 
        $params["httponly"]
    );
}

// destruir la sesión
session_destroy();

header("Location: login.php");
exit();
?>