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