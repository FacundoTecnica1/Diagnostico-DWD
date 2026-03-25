<?php
session_start();
include 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Los nombres de campos coinciden con el formulario del Dashboard
    // y con la estructura real de la tabla 'productos' en la base de datos.
    $nombre    = $conn->real_escape_string($_POST['nombre_producto'] ?? $_POST['nombre'] ?? '');
    $precio    = floatval($_POST['precio']);
    $stock     = intval($_POST['stock']);
    // CORRECCIÓN: Campo correcto es 'id_categoria', no 'categoria'
    $id_cat    = intval($_POST['id_categoria'] ?? $_POST['categoria'] ?? 0);
    $desc      = $conn->real_escape_string($_POST['descripcion'] ?? '');

    // CORRECCIÓN: Tabla correcta es 'productos' (plural), no 'producto' (singular)
    $sql = "INSERT INTO productos (nombre_producto, precio, stock, id_categoria, descripcion)
            VALUES ('$nombre', $precio, $stock, $id_cat, '$desc')";
    
    if ($conn->query($sql) === TRUE) {
        header("Location: dashboard_admin.php?msg=producto_creado");
        exit();
    } else {
        echo "Error al agregar producto: " . $conn->error;
    }
}
?>