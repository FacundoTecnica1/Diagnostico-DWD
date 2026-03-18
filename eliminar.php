<?php
include 'conexion.php';

if (isset($_GET['nombre'])) {
    $nombre = $_GET['nombre'];
    
    // Eliminamos por nombre (asegúrate de que el nombre sea único o usa un ID si lo tienes)
    $sql = "DELETE FROM producto WHERE nombre = '$nombre'";
    
    if ($conn->query($sql)) {
        header("Location: index.php#inventario");
    } else {
        echo "Error al eliminar: " . $conn->error;
    }
}
?>