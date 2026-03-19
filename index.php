<?php
session_start();
include 'conexion.php'; // Asegúrate de que este archivo esté en la misma carpeta
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supermercado Diagnostco</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <style>
        :root {
            --verde-super: #28a745;
            --verde-oscuro: #1e7e34;
            --rojo-precio: #d62828;
            --fondo: #f8f9fa;
        }

        body { 
            background-color: var(--fondo); 
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        /* Navbar personalizada */
        .navbar { 
            background-color: var(--verde-super) !important; 
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .navbar-brand, .nav-link { 
            color: white !important; 
            font-weight: 600;
        }

        /* Hero / Bienvenida */
        .welcome-section {
            background: linear-gradient(135deg, var(--verde-super) 0%, var(--verde-oscuro) 100%);
            color: white;
            padding: 60px 0;
            text-align: center;
            border-bottom-left-radius: 40px;
            border-bottom-right-radius: 40px;
            margin-bottom: 40px;
        }

        /* Tarjetas de Producto */
        .card-producto {
            border: none;
            border-radius: 15px;
            transition: all 0.3s ease;
            background: white;
            overflow: hidden;
        }
        .card-producto:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 25px rgba(0,0,0,0.1) !important;
        }
        
        .card-img-container {
            height: 180px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: #fff;
            padding: 15px;
        }

        .precio-grande {
            color: var(--rojo-precio);
            font-size: 1.6rem;
            font-weight: 800;
        }

        .btn-agregar {
            background-color: var(--verde-super);
            color: white;
            border-radius: 25px;
            font-weight: bold;
            border: none;
            padding: 10px;
            transition: 0.2s;
        }
        .btn-agregar:hover {
            background-color: var(--verde-oscuro);
            color: white;
        }

        /* Estilo para Tablas de Inventario */
        .panel-gestion {
            background: white;
            padding: 25px;
            border-radius: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg sticky-top">
    <div class="container">
        <a class="navbar-brand" href="#"><i class="bi bi-shop-window me-2"></i>DIAGNOSTCO</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-center">
                <li class="nav-item"><a class="nav-link" href="#inicio">Inicio</a></li>
                <?php if(!isset($_SESSION['usuario_rol'])): ?>
                    <li class="nav-item"><a class="nav-link" href="registrar.html">Registrarse</a></li>
                    <li class="nav-item ms-lg-2"><a class="btn btn-light rounded-pill px-4 fw-bold text-success" href="login.html">Login</a></li>
                <?php else: ?>
                    <li class="nav-item px-3 text-white small">Hola, <b><?php echo $_SESSION['usuario_nombre']; ?></b></li>
                    <li class="nav-item"><a class="btn btn-outline-light btn-sm rounded-pill" href="logout.php">Cerrar Sesión</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<header class="welcome-section" id="inicio">
    <div class="container">
        <h1 class="display-4 fw-bold">Gestión de Supermercado</h1>
        <p class="lead">Panel de administración y catálogo de productos</p>
    </div>
</header>

<main class="container mb-5">
    <?php
    $busqueda = isset($_GET['buscar']) ? $_GET['buscar'] : '';

    // --- ROL: CLIENTE O VISITANTE (Ver Catálogo) ---
    if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] === 'cliente') {
        echo '
        <div class="row mb-4 align-items-center">
            <div class="col-md-6">
                <h2 class="fw-bold m-0">Catálogo de Productos</h2>
            </div>
            <div class="col-md-6">
                <form class="d-flex shadow-sm rounded-pill bg-white p-1" method="GET">
                    <input class="form-control border-0 rounded-pill px-3" type="search" name="buscar" placeholder="¿Qué estás buscando?" value="'.$busqueda.'">
                    <button class="btn btn-success rounded-pill px-4" type="submit"><i class="bi bi-search"></i></button>
                </form>
            </div>
        </div>';

        $sql = "SELECT * FROM producto WHERE nombre LIKE '%$busqueda%' OR categoria LIKE '%$busqueda%'";
        $res = $conn->query($sql);

        echo '<div class="row row-cols-1 row-cols-sm-2 row-cols-lg-4 g-4">';
        while ($f = $res->fetch_assoc()) {
            echo '
            <div class="col">
                <div class="card h-100 card-producto shadow-sm">
                    <div class="card-img-container">
                        <i class="bi bi-box2-fill text-success opacity-25" style="font-size: 4rem;"></i>
                    </div>
                    <div class="card-body d-flex flex-column pt-0">
                        <span class="badge bg-light text-success border border-success mb-2 align-self-start">'.htmlspecialchars($f['categoria']).'</span>
                        <h5 class="card-title fw-bold">'.htmlspecialchars($f['nombre']).'</h5>
                        <p class="text-muted small mb-3">Stock: '.htmlspecialchars($f['stock']).' unidades</p>
                        
                        <div class="mt-auto">
                            <p class="precio-grande mb-3">$'.htmlspecialchars($f['precio']).'</p>
                            <form action="comprar.php" method="POST">
                                <input type="hidden" name="producto" value="'.$f['nombre'].'">
                                <input type="hidden" name="precio" value="'.$f['precio'].'">
                                <div class="input-group mb-2">
                                    <span class="input-group-text bg-white border-end-0">Cant.</span>
                                    <input type="number" name="cantidad" value="1" min="1" max="'.$f['stock'].'" class="form-control border-start-0 text-center">
                                </div>
                                <button type="submit" class="btn btn-agregar w-100">🛒 AGREGAR</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>';
        }
        echo '</div>';
    }

    // --- ROL: TRABAJADOR (Gestión de Inventario) ---
    elseif ($_SESSION['usuario_rol'] === 'trabajador') {
        echo '
        <div class="panel-gestion shadow-sm border mt-4">
            <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
                <h2 class="fw-bold text-success m-0"><i class="bi bi-clipboard-data me-2"></i>Inventario</h2>
                <a href="#agregar" class="btn btn-success rounded-pill px-4 fw-bold shadow-sm">+ Nuevo Producto</a>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light text-secondary text-uppercase small">
                        <tr>
                            <th>Producto</th><th>Categoría</th><th>Precio</th><th>Stock</th><th class="text-end">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>';
        
        $sql = "SELECT * FROM producto WHERE nombre LIKE '%$busqueda%'";
        $res = $conn->query($sql);
        while ($f = $res->fetch_assoc()) {
            echo "<tr>
                    <td class='fw-bold'>{$f['nombre']}</td>
                    <td><span class='badge rounded-pill bg-light text-dark border'>{$f['categoria']}</span></td>
                    <td class='fw-bold text-success'>\${$f['precio']}</td>
                    <td><span class='badge bg-success bg-opacity-10 text-success fw-bold p-2'>{$f['stock']} unid.</span></td>
                    <td class='text-end'>
                        <a href='editar.php?nombre=".urlencode($f['nombre'])."' class='btn btn-sm btn-outline-primary rounded-pill px-3 me-1'>Editar</a>
                        <a href='eliminar.php?nombre=".urlencode($f['nombre'])."' class='btn btn-sm btn-outline-danger rounded-pill px-3' onclick='return confirm(\"¿Deseas eliminar este producto?\")'>Borrar</a>
                    </td>
                  </tr>";
        }
        echo '</tbody></table></div></div>';

        // Formulario de Agregar (Solo visible para trabajador)
        echo '
        <div id="agregar" class="panel-gestion mt-5 border border-success border-opacity-25">
            <h3 class="fw-bold mb-4"><i class="bi bi-plus-circle me-2 text-success"></i>Cargar Nuevo Producto</h3>
            <form class="row g-3" action="anadirproducto.php" method="POST">
                <div class="col-md-6"><label class="form-label fw-bold small text-muted text-uppercase">Nombre</label><input type="text" name="nombre" class="form-control rounded-3" required></div>
                <div class="col-md-2"><label class="form-label fw-bold small text-muted text-uppercase">Precio ($)</label><input type="number" name="precio" class="form-control rounded-3" required></div>
                <div class="col-md-2"><label class="form-label fw-bold small text-muted text-uppercase">Stock</label><input type="number" name="stock" class="form-control rounded-3" required></div>
                <div class="col-md-2"><label class="form-label fw-bold small text-muted text-uppercase">Categoría</label><input type="text" name="categoria" class="form-control rounded-3" required></div>
                <div class="col-12 text-end"><button type="submit" class="btn btn-success btn-lg px-5 mt-2 rounded-pill fw-bold">GUARDAR EN BASE DE DATOS</button></div>
            </form>
        </div>';
    }
    ?>
</main>

<footer class="bg-dark text-white py-5 mt-5">
    <div class="container text-center">
        <p class="mb-2"><b>Supermercado Diagnostco</b> - Vicente López</p>
        <p class="text-muted small mb-0">&copy; 2026 Todos los derechos reservados.</p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>