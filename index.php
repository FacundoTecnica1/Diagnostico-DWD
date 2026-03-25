<?php
session_start();
include 'conexion.php'; // Asegúrate de que este archivo tenga la conexión a la base de datos 'pruebaa'
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

        .navbar { 
            background-color: var(--verde-super) !important; 
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .navbar-brand, .nav-link { 
            color: white !important; 
            font-weight: 600;
        }

        .welcome-section {
            background: linear-gradient(135deg, var(--verde-super) 0%, var(--verde-oscuro) 100%);
            color: white;
            padding: 60px 0;
            text-align: center;
            border-bottom-left-radius: 40px;
            border-bottom-right-radius: 40px;
            margin-bottom: 40px;
        }

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
                    <li class="nav-item"><a class="nav-link" href="usuarios/registrar.html">Registrarse</a></li>
                    <li class="nav-item ms-lg-2"><a class="btn btn-light rounded-pill px-4 fw-bold text-success" href="usuarios/login.html">Login</a></li>
                <?php else: ?>
                    <li class="nav-item px-3 text-white small">Hola, <b><?php echo $_SESSION['usuario_nombre']; ?></b></li>
                    <?php if($_SESSION['usuario_rol'] === 'admin'): ?>
                    <li class="nav-item ms-lg-2">
                        <a class="btn btn-warning rounded-pill px-4 fw-bold text-dark me-2" href="dashboardadmin.php">
                            <i class="bi bi-speedometer2 me-1"></i>Dashboard
                        </a>
                    </li>
                    <?php endif; ?>
                    <li class="nav-item"><a class="btn btn-outline-light btn-sm rounded-pill" href="usuarios/logout.php">Cerrar Sesión</a></li>
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

    // --- ROL: CLIENTE O VISITANTE ---
    echo '
    <div class="row mb-4 align-items-center">
        <div class="col-md-6">
            <h2 class="fw-bold m-0">Catálogo de Productos</h2>
        </div>
        <div class="col-md-6">
            <form class="d-flex shadow-sm rounded-pill bg-white p-1" method="GET">
                <input class="form-control border-0 rounded-pill px-3" type="search" name="buscar" placeholder="¿Qué buscas?" value="'.$busqueda.'">
                <button class="btn btn-success rounded-pill px-4" type="submit"><i class="bi bi-search"></i></button>
            </form>
        </div>
    </div>';

    $sql = "SELECT p.*, c.nombre as categoria_nombre 
            FROM productos p 
            LEFT JOIN categorias c ON p.id_categoria = c.id_categoria 
            WHERE p.nombre_producto LIKE '%$busqueda%'";
    $res = $conn->query($sql);

    echo '<div class="row row-cols-1 row-cols-sm-2 row-cols-lg-4 g-4">';
    if ($res && $res->num_rows > 0) {
        while ($f = $res->fetch_assoc()) {
            echo '
            <div class="col">
                <div class="card h-100 card-producto shadow-sm">
                    <div class="card-img-container">
                        <i class="bi bi-box2-fill text-success opacity-25" style="font-size: 4rem;"></i>
                    </div>
                    <div class="card-body d-flex flex-column pt-0">
                        <span class="badge bg-light text-success border border-success mb-2 align-self-start">'.htmlspecialchars($f['categoria_nombre']).'</span>
                        <h5 class="card-title fw-bold">'.htmlspecialchars($f['nombre_producto']).'</h5>
                        <p class="text-muted small mb-3">Stock: '.htmlspecialchars($f['stock']).' unidades</p>
                        
                        <div class="mt-auto">
                            <p class="precio-grande mb-3">$'.htmlspecialchars($f['precio']).'</p>
                            <form action="comprar.php" method="POST">
                                <input type="hidden" name="id_producto" value="'.$f['id_producto'].'">
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
    } else {
        echo '<p class="text-center">No se encontraron productos.</p>';
    }
    echo '</div>';
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