<?php
session_start(); // Siempre al principio
include 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $dni = $_POST['dni'];
    $edad = $_POST['edad'];

    // Insertamos al usuario (Por defecto el estado será 'activo' según tu tabla)
    $sql = "INSERT INTO usuario (nombre, apellido, dni, edad) 
            VALUES ('$nombre', '$apellido', '$dni', '$edad')";

    if ($conn->query($sql) === TRUE) {
        // GUARDAMOS EL NOMBRE EN LA SESIÓN AQUÍ
        $_SESSION['usuario_nombre'] = $nombre; 
        
        // Redirigimos
        header("Location: index.php#inicio");
        exit();
    } else {
        echo "Error: " . $conn->error;
    }
}
?>