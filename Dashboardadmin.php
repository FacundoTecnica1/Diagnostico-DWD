<?php
session_start();
include 'conexion.php';

// --- SEGURIDAD: Solo admin ---
// Para pruebas, comentar estas líneas si no hay sesión activa
// if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] !== 'admin') {
//     header("Location: login.html");
//     exit();
// }

// ============================================================
// ACCIONES POST
// ============================================================

$mensaje = '';
$tipo_mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    // --- PRODUCTOS ---
    if ($accion === 'crear_producto') {
        $nombre   = $conn->real_escape_string($_POST['nombre_producto']);
        $precio   = floatval($_POST['precio']);
        $stock    = intval($_POST['stock']);
        $id_cat   = intval($_POST['id_categoria']);
        $desc     = $conn->real_escape_string($_POST['descripcion'] ?? '');

        // Validar que se seleccionó una categoría válida
        if ($id_cat <= 0) {
            $mensaje = '❌ Error: Debes seleccionar una categoría válida.';
            $tipo_mensaje = 'danger';
        } else {
        $sql = "INSERT INTO productos (nombre_producto, precio, stock, id_categoria, descripcion)
                VALUES ('$nombre', $precio, $stock, $id_cat, '$desc')";
        if ($conn->query($sql)) {
            $mensaje = '✅ Producto creado correctamente.';
            $tipo_mensaje = 'success';
        } else {
            $mensaje = '❌ Error: ' . $conn->error;
            $tipo_mensaje = 'danger';
        }
        } // fin validación id_categoria
    }

    if ($accion === 'editar_producto') {
        $id     = intval($_POST['id_producto']);
        $nombre = $conn->real_escape_string($_POST['nombre_producto']);
        $precio = floatval($_POST['precio']);
        $stock  = intval($_POST['stock']);
        $id_cat = intval($_POST['id_categoria']);
        $desc   = $conn->real_escape_string($_POST['descripcion'] ?? '');
        $sql = "UPDATE productos SET nombre_producto='$nombre', precio=$precio, stock=$stock,
                id_categoria=$id_cat, descripcion='$desc' WHERE id_producto=$id";
        if ($conn->query($sql)) {
            $mensaje = '✅ Producto actualizado.';
            $tipo_mensaje = 'success';
        } else {
            $mensaje = '❌ Error: ' . $conn->error;
            $tipo_mensaje = 'danger';
        }
    }

    if ($accion === 'eliminar_producto') {
        $id = intval($_POST['id_producto']);
        if ($conn->query("DELETE FROM productos WHERE id_producto=$id")) {
            $mensaje = '🗑️ Producto eliminado.';
            $tipo_mensaje = 'warning';
        } else {
            $mensaje = '❌ Error: ' . $conn->error;
            $tipo_mensaje = 'danger';
        }
    }

    // --- EMPLEADOS ---
    if ($accion === 'editar_empleado') {
        $id     = intval($_POST['id_empleado']);
        $cargo  = $conn->real_escape_string($_POST['cargo']);
        $sueldo = floatval($_POST['sueldo_base']);
        $id_rol = intval($_POST['id_rol']);
        $sql = "UPDATE empleados SET cargo='$cargo', sueldo_base=$sueldo, id_rol=$id_rol WHERE id_empleado=$id";
        if ($conn->query($sql)) {
            $mensaje = '✅ Empleado actualizado.';
            $tipo_mensaje = 'success';
        } else {
            $mensaje = '❌ Error: ' . $conn->error;
            $tipo_mensaje = 'danger';
        }
    }

    if ($accion === 'crear_empleado') {
        $nombre  = $conn->real_escape_string($_POST['nombre']);
        $apellido= $conn->real_escape_string($_POST['apellido']);
        $dni     = $conn->real_escape_string($_POST['dni']);
        $correo  = $conn->real_escape_string($_POST['correo']);
        $cargo   = $conn->real_escape_string($_POST['cargo']);
        $sueldo  = floatval($_POST['sueldo_base']);
        $id_rol  = intval($_POST['id_rol']);
        $fecha   = date('Y-m-d');
        $sql = "INSERT INTO empleados (id_rol, dni, nombre, apellido, correo, cargo, sueldo_base, fecha_contratacion)
                VALUES ($id_rol, '$dni', '$nombre', '$apellido', '$correo', '$cargo', $sueldo, '$fecha')";
        if ($conn->query($sql)) {
            $mensaje = '✅ Empleado creado correctamente.';
            $tipo_mensaje = 'success';
        } else {
            $mensaje = '❌ Error: ' . $conn->error;
            $tipo_mensaje = 'danger';
        }
    }

    if ($accion === 'eliminar_empleado') {
        $id = intval($_POST['id_empleado']);
        if ($conn->query("DELETE FROM empleados WHERE id_empleado=$id")) {
            $mensaje = '🗑️ Empleado eliminado.';
            $tipo_mensaje = 'warning';
        } else {
            $mensaje = '❌ Error: ' . $conn->error;
            $tipo_mensaje = 'danger';
        }
    }
}

// ============================================================
// DATOS
// ============================================================
$productos  = $conn->query("SELECT p.*, c.nombre as cat_nombre FROM productos p LEFT JOIN categorias c ON p.id_categoria=c.id_categoria ORDER BY p.id_producto DESC");
$empleados  = $conn->query("SELECT e.*, r.nombre as rol_nombre FROM empleados e LEFT JOIN roles r ON e.id_rol=r.id_rol ORDER BY e.id_empleado ASC");
$categorias = $conn->query("SELECT * FROM categorias ORDER BY nombre ASC");
$roles      = $conn->query("SELECT * FROM roles ORDER BY nombre ASC");

// Stats
$total_productos = $conn->query("SELECT COUNT(*) as c FROM productos")->fetch_assoc()['c'];
$total_empleados = $conn->query("SELECT COUNT(*) as c FROM empleados")->fetch_assoc()['c'];
$total_clientes  = $conn->query("SELECT COUNT(*) as c FROM clientes")->fetch_assoc()['c'];
$valor_inventario= $conn->query("SELECT SUM(precio*stock) as v FROM productos")->fetch_assoc()['v'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Panel Admin — Supermercado </title>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:ital,wght@0,300;0,400;0,500;1,300&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
<style>
/* ============================================================
   VARIABLES & BASE
   ============================================================ */
:root {
    --ink:       #0d1117;
    --ink-2:     #1c2431;
    --ink-3:     #2d3748;
    --surface:   #f7f8fa;
    --surface-2: #ffffff;
    --border:    #e2e8f0;
    --green:     #16a34a;
    --green-2:   #22c55e;
    --green-pale:#dcfce7;
    --red:       #dc2626;
    --red-pale:  #fee2e2;
    --amber:     #d97706;
    --amber-pale:#fef3c7;
    --blue:      #2563eb;
    --blue-pale: #dbeafe;
    --sidebar-w: 260px;
}

*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

body {
    font-family: 'DM Sans', sans-serif;
    background: var(--surface);
    color: var(--ink);
    min-height: 100vh;
    display: flex;
}

h1,h2,h3,h4,h5,h6 { font-family: 'Syne', sans-serif; }

/* ============================================================
   SIDEBAR
   ============================================================ */
.sidebar {
    width: var(--sidebar-w);
    min-height: 100vh;
    background: var(--ink);
    display: flex;
    flex-direction: column;
    position: fixed;
    top: 0; left: 0;
    z-index: 100;
    padding: 0;
    border-right: 1px solid var(--ink-3);
}

.sidebar-brand {
    padding: 28px 24px 20px;
    border-bottom: 1px solid var(--ink-3);
    display: flex;
    align-items: center;
    gap: 12px;
}

.brand-icon {
    width: 38px; height: 38px;
    background: var(--green);
    border-radius: 10px;
    display: grid;
    place-items: center;
    font-size: 1.1rem;
    color: white;
    flex-shrink: 0;
}

.brand-text {
    color: white;
    font-family: 'Syne', sans-serif;
    font-weight: 800;
    font-size: 1rem;
    line-height: 1.2;
}
.brand-sub {
    color: #6b7280;
    font-size: 0.7rem;
    font-weight: 400;
    letter-spacing: 0.08em;
    text-transform: uppercase;
}

.sidebar-nav {
    padding: 20px 12px;
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.nav-label {
    color: #4b5563;
    font-size: 0.65rem;
    font-weight: 700;
    letter-spacing: 0.12em;
    text-transform: uppercase;
    padding: 12px 12px 6px;
}

.nav-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 14px;
    border-radius: 10px;
    color: #9ca3af;
    text-decoration: none;
    font-size: 0.875rem;
    font-weight: 500;
    transition: all 0.15s;
    cursor: pointer;
    border: none;
    background: none;
    width: 100%;
    text-align: left;
}

.nav-item:hover, .nav-item.active {
    background: var(--ink-2);
    color: white;
}

.nav-item.active {
    color: var(--green-2);
}

.nav-item i { font-size: 1.1rem; width: 20px; text-align: center; }

.sidebar-footer {
    padding: 16px 12px;
    border-top: 1px solid var(--ink-3);
}

.user-chip {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 12px;
    border-radius: 10px;
    background: var(--ink-2);
}

.avatar {
    width: 34px; height: 34px;
    background: var(--green);
    border-radius: 50%;
    display: grid;
    place-items: center;
    color: white;
    font-size: 0.8rem;
    font-weight: 700;
    flex-shrink: 0;
}

.user-info { flex: 1; min-width: 0; }
.user-name { color: white; font-size: 0.82rem; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.user-role { color: var(--green-2); font-size: 0.68rem; text-transform: uppercase; letter-spacing: 0.06em; }

/* ============================================================
   MAIN CONTENT
   ============================================================ */
.main {
    margin-left: var(--sidebar-w);
    flex: 1;
    display: flex;
    flex-direction: column;
    min-height: 100vh;
}

.topbar {
    background: white;
    border-bottom: 1px solid var(--border);
    padding: 16px 32px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    position: sticky;
    top: 0;
    z-index: 50;
}

.page-title {
    font-family: 'Syne', sans-serif;
    font-weight: 800;
    font-size: 1.4rem;
    color: var(--ink);
}

.topbar-right {
    display: flex;
    align-items: center;
    gap: 12px;
}

.badge-pill {
    background: var(--green-pale);
    color: var(--green);
    font-size: 0.72rem;
    font-weight: 700;
    padding: 4px 10px;
    border-radius: 20px;
    letter-spacing: 0.04em;
}

.content {
    padding: 28px 32px;
    flex: 1;
}

/* ============================================================
   PANELS (tabs)
   ============================================================ */
.panel { display: none; }
.panel.active { display: block; }

/* ============================================================
   STATS CARDS
   ============================================================ */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
    margin-bottom: 28px;
}

.stat-card {
    background: white;
    border: 1px solid var(--border);
    border-radius: 14px;
    padding: 20px 22px;
    display: flex;
    align-items: flex-start;
    gap: 14px;
    transition: box-shadow 0.2s;
}

.stat-card:hover { box-shadow: 0 4px 20px rgba(0,0,0,0.06); }

.stat-icon {
    width: 44px; height: 44px;
    border-radius: 12px;
    display: grid;
    place-items: center;
    font-size: 1.2rem;
    flex-shrink: 0;
}

.stat-icon.green  { background: var(--green-pale); color: var(--green); }
.stat-icon.blue   { background: var(--blue-pale);  color: var(--blue);  }
.stat-icon.amber  { background: var(--amber-pale); color: var(--amber); }
.stat-icon.red    { background: var(--red-pale);   color: var(--red);   }

.stat-val {
    font-family: 'Syne', sans-serif;
    font-size: 1.7rem;
    font-weight: 800;
    line-height: 1;
    margin-bottom: 3px;
    color: var(--ink);
}

.stat-lbl {
    font-size: 0.78rem;
    color: #64748b;
    font-weight: 500;
}

/* ============================================================
   DATA TABLES
   ============================================================ */
.card-section {
    background: white;
    border: 1px solid var(--border);
    border-radius: 16px;
    overflow: hidden;
    margin-bottom: 24px;
}

.card-header-custom {
    padding: 18px 22px;
    border-bottom: 1px solid var(--border);
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: white;
}

.card-header-title {
    font-family: 'Syne', sans-serif;
    font-size: 0.95rem;
    font-weight: 700;
    color: var(--ink);
    display: flex;
    align-items: center;
    gap: 8px;
}

.table { margin: 0; font-size: 0.865rem; }
.table thead th {
    background: #f8fafc;
    font-size: 0.72rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: #64748b;
    border-bottom: 1px solid var(--border);
    padding: 10px 16px;
}

.table tbody td {
    padding: 12px 16px;
    vertical-align: middle;
    border-bottom: 1px solid #f1f5f9;
    color: var(--ink-3);
}

.table tbody tr:last-child td { border-bottom: none; }
.table tbody tr:hover td { background: #fafbfc; }

/* ============================================================
   BADGES
   ============================================================ */
.tag {
    display: inline-block;
    padding: 3px 9px;
    border-radius: 6px;
    font-size: 0.72rem;
    font-weight: 600;
}

.tag-green  { background: var(--green-pale); color: var(--green); }
.tag-blue   { background: var(--blue-pale);  color: var(--blue);  }
.tag-amber  { background: var(--amber-pale); color: var(--amber); }
.tag-red    { background: var(--red-pale);   color: var(--red);   }
.tag-gray   { background: #f1f5f9; color: #64748b; }

/* ============================================================
   BUTTONS
   ============================================================ */
.btn-primary-custom {
    background: var(--green);
    color: white;
    border: none;
    border-radius: 9px;
    padding: 8px 16px;
    font-size: 0.82rem;
    font-weight: 600;
    font-family: 'DM Sans', sans-serif;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: background 0.15s, transform 0.1s;
    text-decoration: none;
}
.btn-primary-custom:hover { background: #15803d; color: white; }

.btn-ghost {
    background: transparent;
    border: 1px solid var(--border);
    color: var(--ink-3);
    border-radius: 8px;
    padding: 5px 10px;
    font-size: 0.78rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.15s;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}
.btn-ghost:hover { border-color: var(--ink); background: var(--surface); }

.btn-danger-ghost {
    background: transparent;
    border: 1px solid #fca5a5;
    color: var(--red);
    border-radius: 8px;
    padding: 5px 10px;
    font-size: 0.78rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.15s;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}
.btn-danger-ghost:hover { background: var(--red-pale); border-color: var(--red); }

/* ============================================================
   MODAL / DRAWER
   ============================================================ */
.overlay {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.45);
    z-index: 200;
    display: none;
    align-items: center;
    justify-content: center;
    backdrop-filter: blur(2px);
}
.overlay.open { display: flex; }

.modal-box {
    background: white;
    border-radius: 18px;
    padding: 30px 32px;
    width: 100%;
    max-width: 520px;
    max-height: 92vh;
    overflow-y: auto;
    position: relative;
    box-shadow: 0 25px 60px rgba(0,0,0,0.2);
    animation: slideIn 0.2s ease;
}

@keyframes slideIn {
    from { transform: translateY(20px); opacity: 0; }
    to   { transform: translateY(0);    opacity: 1; }
}

.modal-title {
    font-family: 'Syne', sans-serif;
    font-size: 1.15rem;
    font-weight: 800;
    margin-bottom: 20px;
    padding-bottom: 16px;
    border-bottom: 1px solid var(--border);
}

.modal-close {
    position: absolute;
    top: 18px; right: 18px;
    background: var(--surface);
    border: 1px solid var(--border);
    width: 32px; height: 32px;
    border-radius: 8px;
    display: grid;
    place-items: center;
    cursor: pointer;
    font-size: 1rem;
    color: #64748b;
    transition: all 0.15s;
}
.modal-close:hover { background: var(--red-pale); color: var(--red); border-color: #fca5a5; }

/* ============================================================
   FORM CONTROLS
   ============================================================ */
.form-group { margin-bottom: 14px; }

.form-label-custom {
    display: block;
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: #64748b;
    margin-bottom: 5px;
}

.form-input {
    width: 100%;
    padding: 9px 12px;
    border: 1px solid var(--border);
    border-radius: 9px;
    font-family: 'DM Sans', sans-serif;
    font-size: 0.875rem;
    color: var(--ink);
    background: white;
    transition: border-color 0.15s, box-shadow 0.15s;
    outline: none;
}

.form-input:focus {
    border-color: var(--green);
    box-shadow: 0 0 0 3px rgba(22,163,74,0.12);
}

.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }

.form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 22px;
    padding-top: 18px;
    border-top: 1px solid var(--border);
}

/* ============================================================
   ALERT
   ============================================================ */
.alert-custom {
    padding: 12px 16px;
    border-radius: 10px;
    font-size: 0.875rem;
    font-weight: 500;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}
.alert-success { background: var(--green-pale); color: #166534; border: 1px solid #bbf7d0; }
.alert-danger  { background: var(--red-pale);   color: #991b1b; border: 1px solid #fca5a5; }
.alert-warning { background: var(--amber-pale); color: #92400e; border: 1px solid #fde68a; }

/* ============================================================
   SEARCH BAR
   ============================================================ */
.search-wrap {
    position: relative;
    max-width: 260px;
}
.search-wrap i {
    position: absolute;
    left: 10px;
    top: 50%;
    transform: translateY(-50%);
    color: #94a3b8;
    font-size: 0.9rem;
}
.search-wrap input {
    width: 100%;
    padding: 7px 12px 7px 30px;
    border: 1px solid var(--border);
    border-radius: 9px;
    font-size: 0.82rem;
    outline: none;
    font-family: 'DM Sans';
    transition: border-color 0.15s;
}
.search-wrap input:focus { border-color: var(--green); }

/* ============================================================
   STOCK INDICATOR
   ============================================================ */
.stock-bar-wrap { display: flex; align-items: center; gap: 8px; }
.stock-bar { height: 6px; border-radius: 3px; flex: 1; max-width: 60px; background: #e2e8f0; overflow: hidden; }
.stock-fill { height: 100%; border-radius: 3px; transition: width 0.3s; }

/* ============================================================
   RESPONSIVE
   ============================================================ */
@media (max-width: 992px) {
    .stats-grid { grid-template-columns: repeat(2, 1fr); }
    .sidebar { transform: translateX(-100%); }
    .main { margin-left: 0; }
}
</style>
</head>
<body>

<!-- ============================================================
     SIDEBAR
     ============================================================ -->
<aside class="sidebar">
    <div class="sidebar-brand">
        <div class="brand-icon"><i class="bi bi-shop-window"></i></div>
        <div>
            <div class="brand-text">DIAGNOSTCO</div>
            <div class="brand-sub">Panel Admin</div>
        </div>
    </div>

    <nav class="sidebar-nav">
        <div class="nav-label">Principal</div>
        <button class="nav-item active" onclick="switchPanel('resumen', this)">
            <i class="bi bi-grid-1x2-fill"></i> Resumen
        </button>

        <div class="nav-label">Gestión</div>
        <button class="nav-item" onclick="switchPanel('productos', this)">
            <i class="bi bi-box-seam-fill"></i> Productos
        </button>
        <button class="nav-item" onclick="switchPanel('empleados', this)">
            <i class="bi bi-people-fill"></i> Empleados
        </button>

        <div class="nav-label">Sistema</div>
        <a class="nav-item" href="index.php">
            <i class="bi bi-arrow-left-circle"></i> Volver al sitio
        </a>
        <a class="nav-item" href="usuarios/logout.php">
            <i class="bi bi-box-arrow-right"></i> Cerrar Sesión
        </a>
    </nav>

    <div class="sidebar-footer">
        <div class="user-chip">
            <div class="avatar">A</div>
            <div class="user-info">
                <div class="user-name"><?php echo isset($_SESSION['usuario_nombre']) ? htmlspecialchars($_SESSION['usuario_nombre']) : 'Administrador'; ?></div>
                <div class="user-role">Admin</div>
            </div>
        </div>
    </div>
</aside>

<!-- ============================================================
     MAIN
     ============================================================ -->
<div class="main">

    <!-- TOPBAR -->
    <header class="topbar">
        <div class="page-title" id="page-title">Resumen General</div>
        <div class="topbar-right">
            <span class="badge-pill"><i class="bi bi-circle-fill me-1" style="font-size:0.5rem;"></i>En línea</span>
            <span style="font-size:0.8rem; color:#64748b;"><?php echo date('d M Y'); ?></span>
        </div>
    </header>

    <div class="content">

        <?php if ($mensaje): ?>
        <div class="alert-custom alert-<?php echo $tipo_mensaje; ?>">
            <span><?php echo $mensaje; ?></span>
        </div>
        <?php endif; ?>

        <!-- ====================================================
             PANEL: RESUMEN
             ==================================================== -->
        <div class="panel active" id="panel-resumen">

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon green"><i class="bi bi-box-seam-fill"></i></div>
                    <div>
                        <div class="stat-val"><?php echo $total_productos; ?></div>
                        <div class="stat-lbl">Productos en catálogo</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon blue"><i class="bi bi-people-fill"></i></div>
                    <div>
                        <div class="stat-val"><?php echo $total_empleados; ?></div>
                        <div class="stat-lbl">Empleados activos</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon amber"><i class="bi bi-person-heart"></i></div>
                    <div>
                        <div class="stat-val"><?php echo $total_clientes; ?></div>
                        <div class="stat-lbl">Clientes registrados</div>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon red"><i class="bi bi-currency-dollar"></i></div>
                    <div>
                        <div class="stat-val">$<?php echo number_format($valor_inventario ?? 0, 0, ',', '.'); ?></div>
                        <div class="stat-lbl">Valor del inventario</div>
                    </div>
                </div>
            </div>

            <!-- Quick table productos -->
            <div class="card-section">
                <div class="card-header-custom">
                    <div class="card-header-title"><i class="bi bi-clock-history text-success"></i> Últimos productos cargados</div>
                    <button class="btn-primary-custom" onclick="switchPanel('productos', document.querySelector('[onclick*=productos]'))">
                        <i class="bi bi-arrow-right"></i> Ver todos
                    </button>
                </div>
                <table class="table">
                    <thead><tr><th>Producto</th><th>Categoría</th><th>Precio</th><th>Stock</th></tr></thead>
                    <tbody>
                    <?php
                    $q = $conn->query("SELECT p.*, c.nombre as cat_nombre FROM productos p LEFT JOIN categorias c ON p.id_categoria=c.id_categoria ORDER BY p.id_producto DESC LIMIT 5");
                    while ($r = $q->fetch_assoc()):
                        $st = $r['stock'];
                        $pct = min(100, ($st / 50) * 100);
                        $color = $st > 20 ? '#22c55e' : ($st > 5 ? '#f59e0b' : '#ef4444');
                    ?>
                    <tr>
                        <td class="fw-bold"><?php echo htmlspecialchars($r['nombre_producto']); ?></td>
                        <td><span class="tag tag-green"><?php echo htmlspecialchars($r['cat_nombre'] ?? '—'); ?></span></td>
                        <td class="fw-bold text-danger">$<?php echo number_format($r['precio'],2,',','.'); ?></td>
                        <td>
                            <div class="stock-bar-wrap">
                                <div class="stock-bar"><div class="stock-fill" style="width:<?php echo $pct; ?>%;background:<?php echo $color; ?>"></div></div>
                                <span style="font-size:0.78rem;color:#64748b;"><?php echo $st; ?></span>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <!-- Quick table empleados -->
            <div class="card-section">
                <div class="card-header-custom">
                    <div class="card-header-title"><i class="bi bi-people text-primary"></i> Nómina de empleados</div>
                    <button class="btn-primary-custom" onclick="switchPanel('empleados', document.querySelector('[onclick*=empleados]'))">
                        <i class="bi bi-arrow-right"></i> Gestionar
                    </button>
                </div>
                <table class="table">
                    <thead><tr><th>Nombre</th><th>Cargo</th><th>Rol</th><th>Sueldo base</th></tr></thead>
                    <tbody>
                    <?php
                    $qe = $conn->query("SELECT e.*, r.nombre as rol_nombre FROM empleados e LEFT JOIN roles r ON e.id_rol=r.id_rol LIMIT 5");
                    while ($e = $qe->fetch_assoc()):
                    ?>
                    <tr>
                        <td class="fw-bold"><?php echo htmlspecialchars($e['nombre'].' '.$e['apellido']); ?></td>
                        <td><?php echo htmlspecialchars($e['cargo'] ?? '—'); ?></td>
                        <td><span class="tag tag-blue"><?php echo htmlspecialchars($e['rol_nombre']); ?></span></td>
                        <td class="fw-bold">$<?php echo number_format($e['sueldo_base'],2,',','.'); ?></td>
                    </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ====================================================
             PANEL: PRODUCTOS
             ==================================================== -->
        <div class="panel" id="panel-productos">
            <div class="card-section">
                <div class="card-header-custom">
                    <div class="card-header-title"><i class="bi bi-box-seam-fill text-success"></i> Gestión de Productos</div>
                    <div style="display:flex;gap:10px;align-items:center;">
                        <div class="search-wrap">
                            <i class="bi bi-search"></i>
                            <input type="text" id="search-productos" placeholder="Buscar producto..." oninput="filtrarTabla('search-productos','tabla-productos')">
                        </div>
                        <button class="btn-primary-custom" onclick="abrirModal('modal-nuevo-producto')">
                            <i class="bi bi-plus-lg"></i> Nuevo Producto
                        </button>
                    </div>
                </div>
                <table class="table" id="tabla-productos">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nombre</th>
                            <th>Categoría</th>
                            <th>Precio</th>
                            <th>Stock</th>
                            <th>Descripción</th>
                            <th style="text-align:right;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    $productos->data_seek(0);
                    while ($p = $productos->fetch_assoc()):
                        $st = $p['stock'];
                        $pct = min(100, ($st / 50) * 100);
                        $color = $st > 20 ? '#22c55e' : ($st > 5 ? '#f59e0b' : '#ef4444');
                        $tag = $st > 20 ? 'tag-green' : ($st > 5 ? 'tag-amber' : 'tag-red');
                    ?>
                    <tr>
                        <td style="color:#94a3b8; font-size:0.78rem;"><?php echo $p['id_producto']; ?></td>
                        <td class="fw-bold"><?php echo htmlspecialchars($p['nombre_producto']); ?></td>
                        <td><span class="tag tag-green"><?php echo htmlspecialchars($p['cat_nombre'] ?? '—'); ?></span></td>
                        <td style="font-weight:700; color:var(--red);">$<?php echo number_format($p['precio'],2,',','.'); ?></td>
                        <td>
                            <div class="stock-bar-wrap">
                                <div class="stock-bar"><div class="stock-fill" style="width:<?php echo $pct; ?>%;background:<?php echo $color; ?>"></div></div>
                                <span class="tag <?php echo $tag; ?>"><?php echo $st; ?></span>
                            </div>
                        </td>
                        <td style="color:#64748b; font-size:0.8rem; max-width:160px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                            <?php echo htmlspecialchars($p['descripcion'] ?? '—'); ?>
                        </td>
                        <td style="text-align:right;">
                            <button class="btn-ghost" onclick='abrirEditar(<?php echo json_encode($p); ?>)'>
                                <i class="bi bi-pencil-fill"></i> Editar
                            </button>
                            <form style="display:inline;" method="POST" onsubmit="return confirm('¿Eliminar producto?')">
                                <input type="hidden" name="accion" value="eliminar_producto">
                                <input type="hidden" name="id_producto" value="<?php echo $p['id_producto']; ?>">
                                <button type="submit" class="btn-danger-ghost"><i class="bi bi-trash3-fill"></i> Borrar</button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ====================================================
             PANEL: EMPLEADOS
             ==================================================== -->
        <div class="panel" id="panel-empleados">
            <div class="card-section">
                <div class="card-header-custom">
                    <div class="card-header-title"><i class="bi bi-people-fill text-primary"></i> Gestión de Empleados</div>
                    <div style="display:flex;gap:10px;align-items:center;">
                        <div class="search-wrap">
                            <i class="bi bi-search"></i>
                            <input type="text" id="search-empleados" placeholder="Buscar empleado..." oninput="filtrarTabla('search-empleados','tabla-empleados')">
                        </div>
                        <button class="btn-primary-custom" onclick="abrirModal('modal-nuevo-empleado')">
                            <i class="bi bi-person-plus-fill"></i> Nuevo Empleado
                        </button>
                    </div>
                </div>
                <table class="table" id="tabla-empleados">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nombre completo</th>
                            <th>DNI</th>
                            <th>Correo</th>
                            <th>Cargo</th>
                            <th>Rol</th>
                            <th>Sueldo Base</th>
                            <th style="text-align:right;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    $empleados->data_seek(0);
                    while ($e = $empleados->fetch_assoc()):
                        $rol_tag = $e['id_rol'] == 1 ? 'tag-red' : ($e['id_rol'] == 2 ? 'tag-blue' : 'tag-gray');
                    ?>
                    <tr>
                        <td style="color:#94a3b8; font-size:0.78rem;"><?php echo $e['id_empleado']; ?></td>
                        <td>
                            <div style="display:flex;align-items:center;gap:9px;">
                                <div class="avatar" style="width:30px;height:30px;font-size:0.72rem;background:#e2e8f0;color:#475569;">
                                    <?php echo strtoupper(substr($e['nombre'],0,1).substr($e['apellido'],0,1)); ?>
                                </div>
                                <span class="fw-bold"><?php echo htmlspecialchars($e['nombre'].' '.$e['apellido']); ?></span>
                            </div>
                        </td>
                        <td style="color:#64748b;"><?php echo htmlspecialchars($e['dni'] ?? '—'); ?></td>
                        <td style="color:#64748b; font-size:0.82rem;"><?php echo htmlspecialchars($e['correo'] ?? '—'); ?></td>
                        <td><?php echo htmlspecialchars($e['cargo'] ?? '—'); ?></td>
                        <td><span class="tag <?php echo $rol_tag; ?>"><?php echo htmlspecialchars($e['rol_nombre']); ?></span></td>
                        <td style="font-weight:700; color:var(--green);">$<?php echo number_format($e['sueldo_base'],2,',','.'); ?></td>
                        <td style="text-align:right;">
                            <button class="btn-ghost" onclick='abrirEditarEmpleado(<?php echo json_encode($e); ?>)'>
                                <i class="bi bi-pencil-fill"></i> Editar
                            </button>
                            <form style="display:inline;" method="POST" onsubmit="return confirm('¿Eliminar empleado?')">
                                <input type="hidden" name="accion" value="eliminar_empleado">
                                <input type="hidden" name="id_empleado" value="<?php echo $e['id_empleado']; ?>">
                                <button type="submit" class="btn-danger-ghost"><i class="bi bi-trash3-fill"></i> Borrar</button>
                            </form>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div><!-- /content -->
</div><!-- /main -->


<!-- ============================================================
     MODALES
     ============================================================ -->

<!-- Modal: Nuevo Producto -->
<div class="overlay" id="modal-nuevo-producto">
    <div class="modal-box">
        <div class="modal-title">➕ Nuevo Producto</div>
        <button class="modal-close" onclick="cerrarModal('modal-nuevo-producto')">✕</button>
        <form method="POST">
            <input type="hidden" name="accion" value="crear_producto">
            <div class="form-group">
                <label class="form-label-custom">Nombre del Producto</label>
                <input type="text" name="nombre_producto" class="form-input" placeholder="Ej: Leche entera 1L" required>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label-custom">Precio ($)</label>
                    <input type="number" name="precio" step="0.01" class="form-input" placeholder="0.00" required>
                </div>
                <div class="form-group">
                    <label class="form-label-custom">Stock</label>
                    <input type="number" name="stock" class="form-input" placeholder="0" required>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label-custom">Categoría</label>
                <select name="id_categoria" class="form-input" required>
                    <option value="">— Seleccionar —</option>
                    <?php $categorias->data_seek(0); while ($c = $categorias->fetch_assoc()): ?>
                    <option value="<?php echo $c['id_categoria']; ?>"><?php echo htmlspecialchars($c['nombre']); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label-custom">Descripción</label>
                <textarea name="descripcion" class="form-input" rows="2" placeholder="Descripción opcional..."></textarea>
            </div>
            <div class="form-actions">
                <button type="button" class="btn-ghost" onclick="cerrarModal('modal-nuevo-producto')">Cancelar</button>
                <button type="submit" class="btn-primary-custom"><i class="bi bi-check-lg"></i> Guardar Producto</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Editar Producto -->
<div class="overlay" id="modal-editar-producto">
    <div class="modal-box">
        <div class="modal-title">✏️ Editar Producto</div>
        <button class="modal-close" onclick="cerrarModal('modal-editar-producto')">✕</button>
        <form method="POST" id="form-editar-producto">
            <input type="hidden" name="accion" value="editar_producto">
            <input type="hidden" name="id_producto" id="edit-id-producto">
            <div class="form-group">
                <label class="form-label-custom">Nombre del Producto</label>
                <input type="text" name="nombre_producto" id="edit-nombre" class="form-input" required>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label-custom">Precio ($)</label>
                    <input type="number" name="precio" id="edit-precio" step="0.01" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label-custom">Stock</label>
                    <input type="number" name="stock" id="edit-stock" class="form-input" required>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label-custom">Categoría</label>
                <select name="id_categoria" id="edit-categoria" class="form-input" required>
                    <option value="">— Seleccionar —</option>
                    <?php $categorias->data_seek(0); while ($c = $categorias->fetch_assoc()): ?>
                    <option value="<?php echo $c['id_categoria']; ?>"><?php echo htmlspecialchars($c['nombre']); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label-custom">Descripción</label>
                <textarea name="descripcion" id="edit-descripcion" class="form-input" rows="2"></textarea>
            </div>
            <div class="form-actions">
                <button type="button" class="btn-ghost" onclick="cerrarModal('modal-editar-producto')">Cancelar</button>
                <button type="submit" class="btn-primary-custom"><i class="bi bi-check-lg"></i> Actualizar</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Nuevo Empleado -->
<div class="overlay" id="modal-nuevo-empleado">
    <div class="modal-box">
        <div class="modal-title">👤 Nuevo Empleado</div>
        <button class="modal-close" onclick="cerrarModal('modal-nuevo-empleado')">✕</button>
        <form method="POST">
            <input type="hidden" name="accion" value="crear_empleado">
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label-custom">Nombre</label>
                    <input type="text" name="nombre" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label-custom">Apellido</label>
                    <input type="text" name="apellido" class="form-input" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label-custom">DNI</label>
                    <input type="text" name="dni" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label-custom">Correo</label>
                    <input type="email" name="correo" class="form-input" required>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label-custom">Cargo</label>
                    <input type="text" name="cargo" class="form-input" placeholder="Ej: Cajero" required>
                </div>
                <div class="form-group">
                    <label class="form-label-custom">Sueldo Base ($)</label>
                    <input type="number" name="sueldo_base" step="0.01" class="form-input" placeholder="0.00" required>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label-custom">Rol del Sistema</label>
                <select name="id_rol" class="form-input" required>
                    <?php $roles->data_seek(0); while ($r = $roles->fetch_assoc()): ?>
                    <option value="<?php echo $r['id_rol']; ?>"><?php echo htmlspecialchars($r['nombre']); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-actions">
                <button type="button" class="btn-ghost" onclick="cerrarModal('modal-nuevo-empleado')">Cancelar</button>
                <button type="submit" class="btn-primary-custom"><i class="bi bi-check-lg"></i> Crear Empleado</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Editar Empleado -->
<div class="overlay" id="modal-editar-empleado">
    <div class="modal-box">
        <div class="modal-title">✏️ Editar Empleado</div>
        <button class="modal-close" onclick="cerrarModal('modal-editar-empleado')">✕</button>
        <form method="POST" id="form-editar-empleado">
            <input type="hidden" name="accion" value="editar_empleado">
            <input type="hidden" name="id_empleado" id="eedit-id">
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label-custom">Cargo</label>
                    <input type="text" name="cargo" id="eedit-cargo" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label-custom">Sueldo Base ($)</label>
                    <input type="number" name="sueldo_base" id="eedit-sueldo" step="0.01" class="form-input" required>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label-custom">Rol del Sistema</label>
                <select name="id_rol" id="eedit-rol" class="form-input" required>
                    <?php $roles->data_seek(0); while ($r = $roles->fetch_assoc()): ?>
                    <option value="<?php echo $r['id_rol']; ?>"><?php echo htmlspecialchars($r['nombre']); ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <p style="font-size:0.78rem;color:#94a3b8;margin-top:4px;">
                <i class="bi bi-info-circle"></i> Para cambiar nombre, DNI o correo editar directamente en la base de datos.
            </p>
            <div class="form-actions">
                <button type="button" class="btn-ghost" onclick="cerrarModal('modal-editar-empleado')">Cancelar</button>
                <button type="submit" class="btn-primary-custom"><i class="bi bi-check-lg"></i> Guardar Cambios</button>
            </div>
        </form>
    </div>
</div>


<script>
// ============================================================
// PANEL SWITCHING
// ============================================================
const titles = {
    resumen:   'Resumen General',
    productos: 'Gestión de Productos',
    empleados: 'Gestión de Empleados'
};

function switchPanel(name, btn) {
    document.querySelectorAll('.panel').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.nav-item').forEach(b => b.classList.remove('active'));
    document.getElementById('panel-' + name).classList.add('active');
    if (btn) btn.classList.add('active');
    document.getElementById('page-title').textContent = titles[name] || name;
}

// ============================================================
// MODALES
// ============================================================
function abrirModal(id) {
    document.getElementById(id).classList.add('open');
    document.body.style.overflow = 'hidden';
}

function cerrarModal(id) {
    document.getElementById(id).classList.remove('open');
    document.body.style.overflow = '';
}

// Cerrar al clickear overlay
document.querySelectorAll('.overlay').forEach(el => {
    el.addEventListener('click', function(e) {
        if (e.target === el) cerrarModal(el.id);
    });
});

// ============================================================
// EDITAR PRODUCTO (rellena modal)
// ============================================================
function abrirEditar(p) {
    document.getElementById('edit-id-producto').value = p.id_producto;
    document.getElementById('edit-nombre').value      = p.nombre_producto;
    document.getElementById('edit-precio').value      = p.precio;
    document.getElementById('edit-stock').value       = p.stock;
    document.getElementById('edit-categoria').value   = p.id_categoria;
    document.getElementById('edit-descripcion').value = p.descripcion || '';
    abrirModal('modal-editar-producto');
}

// ============================================================
// EDITAR EMPLEADO (rellena modal)
// ============================================================
function abrirEditarEmpleado(e) {
    document.getElementById('eedit-id').value     = e.id_empleado;
    document.getElementById('eedit-cargo').value  = e.cargo || '';
    document.getElementById('eedit-sueldo').value = e.sueldo_base || 0;
    document.getElementById('eedit-rol').value    = e.id_rol;
    abrirModal('modal-editar-empleado');
}

// ============================================================
// FILTRO DE TABLA
// ============================================================
function filtrarTabla(inputId, tablaId) {
    const q     = document.getElementById(inputId).value.toLowerCase();
    const rows  = document.querySelectorAll('#' + tablaId + ' tbody tr');
    rows.forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
}

// ============================================================
// ESC para cerrar modales
// ============================================================
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
        document.querySelectorAll('.overlay.open').forEach(m => cerrarModal(m.id));
    }
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>