<?php
include 'conexion.php';
$nombre_ref = $_GET['nombre'];
$res = $conn->query("SELECT * FROM producto WHERE nombre = '$nombre_ref'");
$p = $res->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $n = $_POST['nombre'];
    $pr = $_POST['precio'];
    $st = $_POST['stock'];
    $cat = $_POST['categoria'];

    $sql = "UPDATE producto SET nombre='$n', precio='$pr', stock='$st', categoria='$cat' WHERE nombre='$nombre_ref'";
    if ($conn->query($sql)) header("Location: index.php#inventario");
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <link rel="stylesheet" href="style.css">
    <title>Editar Producto</title>
</head>
<body>
    <section id="login">
        <h2>Editar: <?php echo $p['nombre']; ?></h2>
        <form method="POST" class="producto-form">
            <input type="text" name="nombre" value="<?php echo $p['nombre']; ?>" required>
            <input type="number" name="precio" value="<?php echo $p['precio']; ?>" required>
            <input type="number" name="stock" value="<?php echo $p['stock']; ?>" required>
            <input type="text" name="categoria" value="<?php echo $p['categoria']; ?>" required>
            <button type="submit">Guardar Cambios</button>
            <a href="index.php">Cancelar</a>
        </form>
    </section>
</body>
</html>