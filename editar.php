<?php
include 'conexion.php';

// CORRECCIÓN: El Dashboard pasa ?id=X (id numérico), no ?nombre=X
// Se debe usar 'id_producto' como identificador, no el nombre
$id = intval($_GET['id'] ?? 0);

// CORRECCIÓN: Tabla correcta 'productos', campo correcto 'id_producto'
$res = $conn->query("SELECT * FROM productos WHERE id_producto = $id");
$p = $res->fetch_assoc();

if (!$p) {
    die("Producto no encontrado.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $n    = $conn->real_escape_string($_POST['nombre_producto']);
    $pr   = floatval($_POST['precio']);
    $st   = intval($_POST['stock']);
    $cat  = intval($_POST['id_categoria']);
    $desc = $conn->real_escape_string($_POST['descripcion'] ?? '');

    // CORRECCIÓN: Tabla correcta 'productos', campos correctos
    $sql = "UPDATE productos SET nombre_producto='$n', precio=$pr, stock=$st,
            id_categoria=$cat, descripcion='$desc' WHERE id_producto=$id";
    if ($conn->query($sql)) {
        header("Location: dashboard_admin.php");
        exit();
    } else {
        echo "Error al actualizar: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Editar Producto</title>
</head>
<body class="p-4">
    <div class="card mx-auto" style="max-width:500px;">
        <div class="card-body">
            <h4 class="mb-3">Editar: <?php echo htmlspecialchars($p['nombre_producto']); ?></h4>
            <form method="POST" class="row g-3">
                <div class="col-12">
                    <label class="form-label">Nombre</label>
                    <input type="text" name="nombre_producto" class="form-control" value="<?php echo htmlspecialchars($p['nombre_producto']); ?>" required>
                </div>
                <div class="col-6">
                    <label class="form-label">Precio</label>
                    <input type="number" name="precio" step="0.01" class="form-control" value="<?php echo $p['precio']; ?>" required>
                </div>
                <div class="col-6">
                    <label class="form-label">Stock</label>
                    <input type="number" name="stock" class="form-control" value="<?php echo $p['stock']; ?>" required>
                </div>
                <div class="col-12">
                    <label class="form-label">ID Categoría</label>
                    <input type="number" name="id_categoria" class="form-control" value="<?php echo $p['id_categoria']; ?>" required>
                </div>
                <div class="col-12">
                    <label class="form-label">Descripción</label>
                    <textarea name="descripcion" class="form-control"><?php echo htmlspecialchars($p['descripcion'] ?? ''); ?></textarea>
                </div>
                <div class="col-12 d-flex gap-2">
                    <button type="submit" class="btn btn-success">Guardar Cambios</button>
                    <a href="dashboard_admin.php" class="btn btn-outline-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>