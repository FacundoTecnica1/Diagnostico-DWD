<?php
// 1. Iniciamos la sesión para poder acceder a ella
session_start();

// 2. Limpiamos todas las variables de sesión
$_SESSION = array();

// 3. Destruimos la sesión en el servidor
session_destroy();

// 4. Opcional: Borrar la cookie de sesión del navegador
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 5. Redirigimos al index principal
// Usamos ../ porque el archivo está en la carpeta 'usuarios' y el index afuera
header("Location: ../index.php");
exit();
?>