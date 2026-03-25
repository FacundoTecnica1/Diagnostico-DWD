<?php
include 'conexion.php';

// CORRECCIÓN: El Dashboard e index.php pasan ?id=X (id numérico), no ?nombre=X
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    // CORRECCIÓN: Tabla correcta 'productos', campo correcto 'id_producto'
    $sql = "DELETE FROM productos WHERE id_producto = $id";
    
    if ($conn->query($sql)) {
        header("Location: dashboard_admin.php");
        exit();
    } else {
        echo "Error al eliminar: " . $conn->error;
    }
} else {
    header("Location: dashboard_admin.php");
    exit();
}
?>