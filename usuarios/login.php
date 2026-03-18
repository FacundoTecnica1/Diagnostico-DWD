<?php
session_start();
include 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $dni = $_POST['dni'];

    $sql = "SELECT * FROM usuario 
            WHERE nombre='$nombre' 
            AND apellido='$apellido' 
            AND dni='$dni'";

    $resultado = $conn->query($sql);

    if ($resultado->num_rows > 0) {

        // Guardamos en sesión
        $_SESSION['nombre'] = $nombre;
        $_SESSION['apellido'] = $apellido;
        $_SESSION['dni'] = $dni;

        header("Location: index.php");
        exit(); 

    } else {
        echo "Datos incorrectos.";
    }
}
?>