<?php
session_start();
include '../conexion.php'; // Agregamos ../ para salir de la carpeta 'usuarios'

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $dni = $_POST['dni'];
    $correo = $_POST['correo'];
    $contrasena = $_POST['contrasena'];
    $telefono = $_POST['telefono'];

    // Insertamos en la tabla 'clientes' (Rol 3 = Cliente)
    $sql = "INSERT INTO clientes (id_rol, dni, nombre, apellido, correo, contrasena, telefono) 
            VALUES (3, '$dni', '$nombre', '$apellido', '$correo', '$contrasena', '$telefono')";

    if ($conn->query($sql) === TRUE) {
        $_SESSION['usuario_nombre'] = $nombre; 
        $_SESSION['usuario_rol'] = 'cliente';
        header("Location: ../index.php"); // Volvemos a la raíz para el index
        exit();
    } else {
        echo "Error al registrar: " . $conn->error;
    }
}
?>