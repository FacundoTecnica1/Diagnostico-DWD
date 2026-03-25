<?php
session_start();
include '../conexion.php'; // Agregamos ../ para salir de la carpeta 'usuarios'

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = $_POST['correo'];
    $contrasena = $_POST['contrasena'];

    $sql = "SELECT c.*, r.nombre AS rol_nombre 
            FROM clientes c 
            JOIN roles r ON c.id_rol = r.id_rol 
            WHERE c.correo='$correo' AND c.contrasena='$contrasena'";

    $resultado = $conn->query($sql);

    if ($resultado && $resultado->num_rows > 0) {
        $usuario = $resultado->fetch_assoc();
        
        $_SESSION['usuario_nombre'] = $usuario['nombre'];
        $_SESSION['usuario_rol'] = strtolower($usuario['rol_nombre']); 
        $_SESSION['id_cliente'] = $usuario['id_cliente'];

        // Redirigir según rol
        if ($_SESSION['usuario_rol'] === 'admin') {
            header("Location: ../dashboardadmin.php");
        } else {
            header("Location: ../index.php");
        }
        exit(); 
    } else {
        echo "<script>alert('Datos incorrectos'); window.location='login.html';</script>";
    }
}
?>