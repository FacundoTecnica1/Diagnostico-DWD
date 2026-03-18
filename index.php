<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diagnostico</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="cartel-bienvenida">
    <h1>Gestion de supermercado </h1>
    
    <?php if (isset($_SESSION['usuario_nombre'])): ?>
        <h2 style="color: #000000; margin-top: 10px;">
            Hola, <?php echo $_SESSION['usuario_nombre']; ?>!
        </h2>
    <?php endif; ?>

    <a href="#inicio" class="btn-ver-pagina">Ver página</a>
</div>
    <header>
        <div class="logo-menu">
            <img src="logo.png" alt="Logo" class="logo" style="height: 120px; width: auto;">
            <nav>
                <ul>
                    <li><a href="#inicio">Inicio</a></li>
                    <li><a href="#agregar">Agregar Producto</a></li>
                    <li><a href="registrar.html">Registrarse</a></li>
                    <li><a href="login.html">Login</a></li>
                    
                    
                </ul>
            </nav>
        </div>
    </header>
            <?php
            // Panel según rol
            if (isset($_SESSION['usuario_rol'])) {
                if ($_SESSION['usuario_rol'] === 'trabajador') {
                    echo '<div class="panel-trabajador" style="background:#e0e0e0;padding:10px;margin:10px 0;">';
                    echo '<h3>Panel Trabajador</h3>';
                    echo '<ul>';
                    echo '<li><a href="anadirproducto.php">Agregar Producto</a></li>';
                    echo '<li><a href="#inventario">Ver Inventario</a></li>';
                    echo '</ul>';
                    echo '</div>';
                } elseif ($_SESSION['usuario_rol'] === 'vendedor') {
                    echo '<div class="panel-vendedor" style="background:#f0f0f0;padding:10px;margin:10px 0;">';
                    echo '<h3>Panel Vendedor</h3>';
                    echo '<ul>';
                    echo '<li><a href="ventas.html">Registrar Venta</a></li>';
                    echo '<li><a href="ventas.php">Ver Ventas</a></li>';
                    echo '</ul>';
                    echo '</div>';
                }
            }
                if (isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] === 'cliente') {
                    // Mostrar productos en tarjetas con opción de compra
                    include 'conexion.php';
                    $busqueda = isset($_GET['buscar']) ? $_GET['buscar'] : '';
                    $sql = "SELECT * FROM producto WHERE nombre LIKE '%$busqueda%' OR categoria LIKE '%$busqueda%'";
                    $res = $conn->query($sql);
                    echo '<div class="tarjetas-productos" style="display:flex;flex-wrap:wrap;gap:20px;margin-top:20px;">';
                    while ($f = $res->fetch_assoc()) {
                        echo '<div class="tarjeta" style="border:1px solid #ccc;padding:16px;width:220px;background:#fff;box-shadow:2px 2px 8px #eee;">';
                        echo '<h3 style="margin:0 0 10px 0;">' . htmlspecialchars($f['nombre']) . '</h3>';
                        echo '<p><strong>Precio:</strong> $' . htmlspecialchars($f['precio']) . '</p>';
                        echo '<p><strong>Stock:</strong> ' . htmlspecialchars($f['stock']) . '</p>';
                        echo '<p><strong>Categoría:</strong> ' . htmlspecialchars($f['categoria']) . '</p>';
                        echo '<form action="comprar.php" method="post" style="margin-top:10px;">';
                        echo '<input type="hidden" name="producto" value="' . htmlspecialchars($f['nombre']) . '">';
                        echo '<input type="hidden" name="precio" value="' . htmlspecialchars($f['precio']) . '">';
                        echo '<label for="cantidad_' . htmlspecialchars($f['nombre']) . '">Cantidad:</label> ';
                        echo '<input type="number" id="cantidad_' . htmlspecialchars($f['nombre']) . '" name="cantidad" min="1" max="' . htmlspecialchars($f['stock']) . '" value="1" required style="width:60px;"> ';
                        echo '<button type="submit">Comprar</button>';
                        echo '</form>';
                        echo '</div>';
                    }
                    echo '</div>';
                } elseif (isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] === 'trabajador') {
                    // Panel de gestión para trabajador
                    echo '<a href="#inventario" class="btn">Ir a Productos</a>';
                    echo '<section id="agregar">';
                    echo '<h2>Agregar Producto</h2>';
                    echo '<form id="form-agregar" class="producto-form" action="anadirproducto.php" method="POST">';
                    echo '<label for="nombre">Nombre:</label>';
                    echo '<input type="text" id="nombre" name="nombre" required>';
                    echo '<label for="precio">Precio:</label>';
                    echo '<input type="number" id="precio" name="precio" required>';
                    echo '<label for="stock">Stock:</label>';
                    echo '<input type="number" id="stock" name="stock" required>';
                    echo '<label for="categoria">Categoría:</label>';
                    echo '<input type="text" id="categoria" name="categoria" required>';
                    echo '<button type="submit">Agregar</button>';
                    echo '</form>';
                    echo '</section>';
                    echo '<section id="inventario" style="padding: 20px;">';
                    echo '<h2>Inventario de Productos</h2>';
                    echo '<form method="GET" style="margin-bottom: 20px;">';
                    echo '<input type="text" name="buscar" placeholder="Buscar por nombre o categoría..." style="padding: 8px; width: 300px;">';
                    echo '<button type="submit" style="padding: 8px;">🔍 Buscar</button>';
                    echo '</form>';
                    echo '<table border="1" style="width:100%; border-collapse: collapse; text-align: center;">';
                    echo '<tr style="background: #333; color: white;">';
                    echo '<th>Nombre</th><th>Precio</th><th>Stock</th><th>Categoría</th><th>Acciones</th>';
                    echo '</tr>';
                    include 'conexion.php';
                    $busqueda = isset($_GET['buscar']) ? $_GET['buscar'] : '';
                    $sql = "SELECT * FROM producto WHERE nombre LIKE '%$busqueda%' OR categoria LIKE '%$busqueda%'";
                    $res = $conn->query($sql);
                    while ($f = $res->fetch_assoc()) {
                        echo '<tr>';
                        echo '<td>' . htmlspecialchars($f['nombre']) . '</td>';
                        echo '<td>$' . htmlspecialchars($f['precio']) . '</td>';
                        echo '<td>' . htmlspecialchars($f['stock']) . '</td>';
                        echo '<td>' . htmlspecialchars($f['categoria']) . '</td>';
                        echo '<td>';
                        echo '<a href="editar.php?nombre=' . urlencode($f['nombre']) . '" style="color: blue; margin-right: 10px;">Editar</a>';
                        echo '<a href="eliminar.php?nombre=' . urlencode($f['nombre']) . '" style="color: red;" onclick="return confirm(\'¿Eliminar este producto?\')">Eliminar</a>';
                        echo '</td>';
                        echo '</tr>';
                    }
                    echo '</table>';
                    echo '</section>';
                }
                ?>
            ?>
    <section id="inicio"> 
    <h1>Bienvenido Supermercado Diagnostco</h1> 
    <a href="#inventario" class="btn">Ir a Productos</a>
</section>


    <section id="agregar">
        <h2>Agregar Producto</h2>
        <form id="form-agregar" class="producto-form" action="anadirproducto.php" method="POST">
            <label for="nombre">Nombre:</label>
            <input type="text" id="nombre" name="nombre" required>
            <label for="precio">Precio:</label>
            <input type="number" id="precio" name="precio" required>
            <label for="stock">Stock:</label>
            <input type="number" id="stock" name="stock" required>
            <label for="categoria">Categoría:</label>
            <input type="text" id="categoria" name="categoria" required>
            <button type="submit">Agregar</button>
        </form>
    </section>
<section id="inventario" style="padding: 20px;">
    <h2>Inventario de Productos</h2>

    <form method="GET" style="margin-bottom: 20px;">
        <input type="text" name="buscar" placeholder="Buscar por nombre o categoría..." style="padding: 8px; width: 300px;">
        <button type="submit" style="padding: 8px;">🔍 Buscar</button>
    </form>

    <table border="1" style="width:100%; border-collapse: collapse; text-align: center;">
        <tr style="background: #333; color: white;">
            <th>Nombre</th>
            <th>Precio</th>
            <th>Stock</th>
            <th>Categoría</th>
            <th>Acciones</th>
        </tr>

        <?php
        include 'conexion.php';


        $busqueda = isset($_GET['buscar']) ? $_GET['buscar'] : '';
        $sql = "SELECT * FROM producto WHERE nombre LIKE '%$busqueda%' OR categoria LIKE '%$busqueda%'";
        
        $res = $conn->query($sql);

        while ($f = $res->fetch_assoc()) {
            echo "<tr>
                    <td>{$f['nombre']}</td>
                    <td>\${$f['precio']}</td>
                    <td>{$f['stock']}</td>
                    <td>{$f['categoria']}</td>
                    <td>
                        <a href='editar.php?nombre={$f['nombre']}' style='color: blue; margin-right: 10px;'>Editar</a>
                        <a href='eliminar.php?nombre={$f['nombre']}' style='color: red;' onclick='return confirm(\"¿Eliminar este producto?\")'>Eliminar</a>
                    </td>
                  </tr>";
        }
        ?>
    </table>
</section>
</section>

    <footer>
        <div class="footer-content">
            <p>Contacto: contacto@supermercado.com</p>
            <p>&copy; 2026 Supermercado. Todos los derechos reservados.</p>
        </div>
    </footer>
    
</body>
</html>