<?php
include 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $producto = $_POST['producto'];
    $cantidad = $_POST['cantidad'];
    $precio = $_POST['precio'];

    // Aquí puedes agregar la lógica para guardar la venta en la base de datos
    // Ejemplo:
    // $sql = "INSERT INTO ventas (producto, cantidad, precio) VALUES ('$producto', $cantidad, $precio)";
    // mysqli_query($conn, $sql);

    echo "<h2>Venta registrada correctamente</h2>";
    echo "<a href='ventas.html'>Registrar otra venta</a>";
} else {
    echo "<h2>Acceso no permitido</h2>";
}
?>