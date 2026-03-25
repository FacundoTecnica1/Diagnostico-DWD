<?php
session_start();
include 'conexion.php';

// --- MANEJO DE ACCIONES ---
$mensaje = '';
$tipo_mensaje = '';

// AGREGAR PRODUCTO
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'agregar') {
    $nombre   = $conn->real_escape_string($_POST['nombre_producto']);
    $precio   = floatval($_POST['precio']);
    $stock    = intval($_POST['stock']);
    $id_cat   = intval($_POST['id_categoria']);
    $desc     = $conn->real_escape_string($_POST['descripcion'] ?? '');

    $sql = "INSERT INTO productos (nombre_producto, precio, stock, id_categoria, descripcion)
            VALUES ('$nombre', $precio, $stock, $id_cat, '$desc')";
    if ($conn->query($sql)) {
        $mensaje = "✓ Producto <b>$nombre</b> agregado correctamente.";
        $tipo_mensaje = 'exito';
    } else {
        $mensaje = "✗ Error al agregar: " . $conn->error;
        $tipo_mensaje = 'error';
    }
}

// EDITAR PRODUCTO
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'editar') {
    $id     = intval($_POST['id_producto']);
    $nombre = $conn->real_escape_string($_POST['nombre_producto']);
    $precio = floatval($_POST['precio']);
    $stock  = intval($_POST['stock']);
    $id_cat = intval($_POST['id_categoria']);
    $desc   = $conn->real_escape_string($_POST['descripcion'] ?? '');

    $sql = "UPDATE productos SET nombre_producto='$nombre', precio=$precio, stock=$stock,
            id_categoria=$id_cat, descripcion='$desc' WHERE id_producto=$id";
    if ($conn->query($sql)) {
        $mensaje = "✓ Producto actualizado correctamente.";
        $tipo_mensaje = 'exito';
    } else {
        $mensaje = "✗ Error al editar: " . $conn->error;
        $tipo_mensaje = 'error';
    }
}

// ELIMINAR PRODUCTO
if (isset($_GET['eliminar'])) {
    $id = intval($_GET['eliminar']);
    if ($conn->query("DELETE FROM productos WHERE id_producto=$id")) {
        $mensaje = "✓ Producto eliminado.";
        $tipo_mensaje = 'exito';
    } else {
        $mensaje = "✗ Error: " . $conn->error;
        $tipo_mensaje = 'error';
    }
}

// Obtener producto para editar
$producto_editar = null;
if (isset($_GET['editar'])) {
    $id = intval($_GET['editar']);
    $res = $conn->query("SELECT * FROM productos WHERE id_producto=$id");
    if ($res && $res->num_rows > 0) $producto_editar = $res->fetch_assoc();
}

// Obtener todos los productos con categoría
$productos = $conn->query("
    SELECT p.*, c.nombre as categoria_nombre
    FROM productos p
    LEFT JOIN categorias c ON p.id_categoria = c.id_categoria
    ORDER BY p.id_producto DESC
");

// Obtener categorías para el select
$categorias = $conn->query("SELECT * FROM categorias ORDER BY nombre");

// Stats
$total_prods  = $conn->query("SELECT COUNT(*) as t FROM productos")->fetch_assoc()['t'];
$total_stock  = $conn->query("SELECT SUM(stock) as t FROM productos")->fetch_assoc()['t'] ?? 0;
$stock_bajo   = $conn->query("SELECT COUNT(*) as t FROM productos WHERE stock < 10")->fetch_assoc()['t'];
$valor_inv    = $conn->query("SELECT SUM(precio*stock) as t FROM productos")->fetch_assoc()['t'] ?? 0;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Admin — Supermercado Diagnostco</title>
    <link href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg:       #0d0f14;
            --surface:  #161922;
            --border:   #23283a;
            --accent:   #00e676;
            --accent2:  #ff6b35;
            --accent3:  #7c3aed;
            --text:     #eaeef8;
            --muted:    #636b82;
            --danger:   #ff4757;
            --card-r:   16px;
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            background: var(--bg);
            color: var(--text);
            font-family: 'DM Sans', sans-serif;
            min-height: 100vh;
            display: flex;
        }

        /* ── SIDEBAR ── */
        .sidebar {
            width: 260px;
            min-height: 100vh;
            background: var(--surface);
            border-right: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            padding: 32px 20px;
            position: fixed;
            top: 0; left: 0;
            z-index: 100;
        }

        .sidebar-logo {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 40px;
        }

        .logo-icon {
            width: 44px; height: 44px;
            background: var(--accent);
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.3rem;
        }

        .logo-text {
            font-family: 'Syne', sans-serif;
            font-weight: 800;
            font-size: 1.1rem;
            letter-spacing: -0.5px;
            color: var(--text);
        }

        .logo-text span { color: var(--accent); }

        .nav-section-label {
            font-size: 0.65rem;
            font-weight: 600;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: var(--muted);
            margin: 20px 0 8px 8px;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 11px 14px;
            border-radius: 10px;
            color: var(--muted);
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
            transition: all 0.2s;
            cursor: pointer;
            border: none;
            background: none;
            width: 100%;
            text-align: left;
        }

        .nav-item:hover { background: rgba(255,255,255,0.04); color: var(--text); }
        .nav-item.active { background: rgba(0,230,118,0.1); color: var(--accent); }
        .nav-item .icon { font-size: 1.1rem; width: 20px; text-align: center; }

        .sidebar-footer {
            margin-top: auto;
            padding-top: 20px;
            border-top: 1px solid var(--border);
        }

        .user-chip {
            display: flex; align-items: center; gap: 10px;
            padding: 10px;
        }

        .user-avatar {
            width: 36px; height: 36px;
            background: linear-gradient(135deg, var(--accent3), var(--accent));
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: 0.9rem;
            color: white;
        }

        .user-info { flex: 1; }
        .user-name  { font-size: 0.85rem; font-weight: 600; color: var(--text); }
        .user-role  { font-size: 0.72rem; color: var(--muted); }

        /* ── MAIN ── */
        .main {
            margin-left: 260px;
            flex: 1;
            padding: 40px;
        }

        /* ── TOPBAR ── */
        .topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 36px;
        }

        .page-title {
            font-family: 'Syne', sans-serif;
            font-size: 1.9rem;
            font-weight: 800;
            letter-spacing: -1px;
        }

        .page-title span { color: var(--accent); }

        .search-bar {
            display: flex;
            align-items: center;
            gap: 8px;
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 50px;
            padding: 8px 18px;
        }

        .search-bar input {
            background: none; border: none; outline: none;
            color: var(--text); font-family: 'DM Sans', sans-serif;
            font-size: 0.88rem; width: 200px;
        }

        .search-bar input::placeholder { color: var(--muted); }

        /* ── STAT CARDS ── */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 36px;
        }

        .stat-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--card-r);
            padding: 24px;
            position: relative;
            overflow: hidden;
            transition: transform 0.2s;
        }

        .stat-card:hover { transform: translateY(-3px); }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 3px;
        }

        .stat-card:nth-child(1)::before { background: var(--accent); }
        .stat-card:nth-child(2)::before { background: var(--accent2); }
        .stat-card:nth-child(3)::before { background: var(--danger); }
        .stat-card:nth-child(4)::before { background: var(--accent3); }

        .stat-icon {
            font-size: 1.6rem;
            margin-bottom: 12px;
        }

        .stat-value {
            font-family: 'Syne', sans-serif;
            font-size: 2rem;
            font-weight: 800;
            line-height: 1;
            margin-bottom: 4px;
        }

        .stat-label { font-size: 0.78rem; color: var(--muted); font-weight: 500; text-transform: uppercase; letter-spacing: 0.5px; }

        /* ── PANELS ── */
        .panels-grid {
            display: grid;
            grid-template-columns: 1fr 380px;
            gap: 24px;
        }

        .panel {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: var(--card-r);
            overflow: hidden;
        }

        .panel-header {
            padding: 20px 24px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .panel-title {
            font-family: 'Syne', sans-serif;
            font-size: 1rem;
            font-weight: 700;
        }

        .badge {
            font-size: 0.72rem;
            padding: 3px 10px;
            border-radius: 20px;
            font-weight: 600;
        }

        .badge-green { background: rgba(0,230,118,0.1); color: var(--accent); }
        .badge-orange { background: rgba(255,107,53,0.1); color: var(--accent2); }
        .badge-purple { background: rgba(124,58,237,0.1); color: #a78bfa; }

        /* ── TABLE ── */
        .table-wrap { overflow-x: auto; }

        table { width: 100%; border-collapse: collapse; }

        thead tr { border-bottom: 1px solid var(--border); }

        th {
            font-size: 0.68rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: var(--muted);
            padding: 14px 24px;
            text-align: left;
            white-space: nowrap;
        }

        td {
            padding: 14px 24px;
            font-size: 0.88rem;
            border-bottom: 1px solid rgba(255,255,255,0.03);
            vertical-align: middle;
        }

        tr:last-child td { border-bottom: none; }
        tr:hover td { background: rgba(255,255,255,0.02); }

        .prod-name { font-weight: 600; color: var(--text); }
        .prod-cat  {
            font-size: 0.75rem;
            padding: 3px 8px;
            border-radius: 6px;
            background: rgba(255,255,255,0.05);
            color: var(--muted);
        }

        .price-cell { font-family: 'Syne', sans-serif; font-weight: 700; color: var(--accent2); }

        .stock-pill {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.78rem;
            font-weight: 600;
        }

        .stock-ok   { background: rgba(0,230,118,0.08);  color: var(--accent); }
        .stock-low  { background: rgba(255,71,87,0.08);  color: var(--danger); }
        .stock-mid  { background: rgba(255,107,53,0.08); color: var(--accent2); }

        .action-btns { display: flex; gap: 8px; }

        .btn-edit, .btn-del {
            border: none; cursor: pointer;
            font-size: 0.78rem; font-weight: 600;
            padding: 6px 14px; border-radius: 8px;
            transition: all 0.2s; font-family: 'DM Sans', sans-serif;
        }

        .btn-edit { background: rgba(124,58,237,0.12); color: #a78bfa; }
        .btn-edit:hover { background: rgba(124,58,237,0.25); }

        .btn-del { background: rgba(255,71,87,0.1); color: var(--danger); }
        .btn-del:hover { background: rgba(255,71,87,0.2); }

        /* ── FORM PANEL ── */
        .form-panel { padding: 24px; }

        .form-section-title {
            font-family: 'Syne', sans-serif;
            font-size: 0.85rem;
            font-weight: 700;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 16px;
        }

        .form-group { margin-bottom: 16px; }

        label {
            display: block;
            font-size: 0.75rem;
            font-weight: 600;
            color: var(--muted);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 6px;
        }

        input[type="text"], input[type="number"], select, textarea {
            width: 100%;
            background: rgba(255,255,255,0.04);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 11px 14px;
            color: var(--text);
            font-family: 'DM Sans', sans-serif;
            font-size: 0.88rem;
            outline: none;
            transition: border-color 0.2s;
        }

        input:focus, select:focus, textarea:focus {
            border-color: var(--accent);
            background: rgba(0,230,118,0.03);
        }

        select option { background: #1a1f2e; }
        textarea { resize: vertical; min-height: 70px; }

        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }

        .btn-submit {
            width: 100%;
            padding: 13px;
            background: var(--accent);
            color: #0d0f14;
            border: none;
            border-radius: 10px;
            font-family: 'Syne', sans-serif;
            font-size: 0.9rem;
            font-weight: 800;
            cursor: pointer;
            letter-spacing: 0.5px;
            transition: all 0.2s;
            margin-top: 4px;
        }

        .btn-submit:hover { background: #00ff84; transform: translateY(-1px); }
        .btn-submit.editing { background: var(--accent3); color: white; }
        .btn-submit.editing:hover { background: #9461f5; }

        .btn-cancel {
            width: 100%;
            padding: 11px;
            background: transparent;
            color: var(--muted);
            border: 1px solid var(--border);
            border-radius: 10px;
            font-family: 'DM Sans', sans-serif;
            font-size: 0.85rem;
            cursor: pointer;
            margin-top: 8px;
            transition: all 0.2s;
        }

        .btn-cancel:hover { border-color: var(--muted); color: var(--text); }

        /* ── TOAST ── */
        .toast {
            position: fixed;
            top: 24px; right: 24px;
            padding: 14px 20px;
            border-radius: 12px;
            font-size: 0.88rem;
            font-weight: 500;
            z-index: 9999;
            animation: slideIn 0.3s ease, fadeOut 0.5s ease 3s forwards;
            max-width: 380px;
            border-left: 4px solid;
        }

        .toast-exito {
            background: rgba(0,230,118,0.1);
            border-color: var(--accent);
            color: var(--accent);
        }

        .toast-error {
            background: rgba(255,71,87,0.1);
            border-color: var(--danger);
            color: var(--danger);
        }

        @keyframes slideIn {
            from { transform: translateX(120%); opacity: 0; }
            to   { transform: translateX(0); opacity: 1; }
        }
        @keyframes fadeOut {
            to { opacity: 0; pointer-events: none; }
        }

        /* ── EMPTY STATE ── */
        .empty-state {
            padding: 60px 24px;
            text-align: center;
        }

        .empty-icon { font-size: 3rem; margin-bottom: 12px; opacity: 0.3; }
        .empty-text { color: var(--muted); font-size: 0.9rem; }

        /* ── SCROLLBAR ── */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: var(--border); border-radius: 3px; }

        /* ── DIVIDER ── */
        .divider { height: 1px; background: var(--border); margin: 20px 0; }

        /* ── EDIT MODE INDICATOR ── */
        .edit-banner {
            background: rgba(124,58,237,0.1);
            border: 1px solid rgba(124,58,237,0.3);
            border-radius: 10px;
            padding: 10px 14px;
            font-size: 0.82rem;
            color: #a78bfa;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        @media (max-width: 1200px) {
            .stats-grid { grid-template-columns: repeat(2,1fr); }
            .panels-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>

<!-- TOAST -->
<?php if ($mensaje): ?>
<div class="toast toast-<?= $tipo_mensaje ?>">
    <?= $mensaje ?>
</div>
<?php endif; ?>

<!-- SIDEBAR -->
<aside class="sidebar">
    <div class="sidebar-logo">
        <div class="logo-icon">🛒</div>
        <div class="logo-text">DIAGNOSTCO<span>.</span></div>
    </div>

    <span class="nav-section-label">Gestión</span>
    <button class="nav-item active" onclick="showSection('productos')">
        <span class="icon">📦</span> Productos
    </button>
    <a class="nav-item" href="index.php">
        <span class="icon">🏠</span> Ver Tienda
    </a>

    <span class="nav-section-label">Sistema</span>
    <a class="nav-item" href="usuarios/login.html">
        <span class="icon">👤</span> Login
    </a>
    <a class="nav-item" href="usuarios/logout.php">
        <span class="icon">🚪</span> Cerrar Sesión
    </a>

    <div class="sidebar-footer">
        <div class="user-chip">
            <div class="user-avatar">
                <?= isset($_SESSION['usuario_nombre']) ? strtoupper(substr($_SESSION['usuario_nombre'],0,1)) : 'A' ?>
            </div>
            <div class="user-info">
                <div class="user-name"><?= isset($_SESSION['usuario_nombre']) ? htmlspecialchars($_SESSION['usuario_nombre']) : 'Admin' ?></div>
                <div class="user-role"><?= isset($_SESSION['usuario_rol']) ? ucfirst($_SESSION['usuario_rol']) : 'Administrador' ?></div>
            </div>
        </div>
    </div>
</aside>

<!-- MAIN -->
<main class="main">

    <!-- TOPBAR -->
    <div class="topbar">
        <div>
            <div class="page-title">Panel <span>Admin</span></div>
            <div style="font-size:0.82rem; color:var(--muted); margin-top:2px;">Gestión de inventario y productos</div>
        </div>
        <form method="GET" class="search-bar">
            <span>🔍</span>
            <input type="text" name="buscar" placeholder="Buscar producto..." 
                   value="<?= htmlspecialchars($_GET['buscar'] ?? '') ?>">
        </form>
    </div>

    <!-- STATS -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">📦</div>
            <div class="stat-value"><?= $total_prods ?></div>
            <div class="stat-label">Productos Totales</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">📊</div>
            <div class="stat-value"><?= number_format($total_stock) ?></div>
            <div class="stat-label">Unidades en Stock</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">⚠️</div>
            <div class="stat-value"><?= $stock_bajo ?></div>
            <div class="stat-label">Stock Bajo (&lt;10)</div>
        </div>
        <div class="stat-card">
            <div class="stat-icon">💰</div>
            <div class="stat-value">$<?= number_format($valor_inv, 0, ',', '.') ?></div>
            <div class="stat-label">Valor del Inventario</div>
        </div>
    </div>

    <!-- PANELS -->
    <div class="panels-grid">

        <!-- TABLA DE PRODUCTOS -->
        <div class="panel">
            <div class="panel-header">
                <span class="panel-title">📋 Inventario</span>
                <?php
                $busqueda = $_GET['buscar'] ?? '';
                $sql_prod = "SELECT p.*, c.nombre as categoria_nombre FROM productos p LEFT JOIN categorias c ON p.id_categoria = c.id_categoria WHERE p.nombre_producto LIKE '%$busqueda%' ORDER BY p.id_producto DESC";
                $res_prod = $conn->query($sql_prod);
                $count = $res_prod ? $res_prod->num_rows : 0;
                ?>
                <span class="badge badge-green"><?= $count ?> productos</span>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Producto</th>
                            <th>Categoría</th>
                            <th>Precio</th>
                            <th>Stock</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if ($res_prod && $res_prod->num_rows > 0):
                        while ($f = $res_prod->fetch_assoc()):
                            $stock_class = $f['stock'] >= 20 ? 'stock-ok' : ($f['stock'] >= 10 ? 'stock-mid' : 'stock-low');
                            $stock_dot = $f['stock'] >= 20 ? '●' : ($f['stock'] >= 10 ? '●' : '●');
                    ?>
                    <tr>
                        <td style="color:var(--muted); font-size:0.78rem;">#<?= $f['id_producto'] ?></td>
                        <td>
                            <div class="prod-name"><?= htmlspecialchars($f['nombre_producto']) ?></div>
                            <?php if ($f['descripcion']): ?>
                            <div style="font-size:0.75rem; color:var(--muted); margin-top:2px;"><?= htmlspecialchars(substr($f['descripcion'],0,40)) ?>...</div>
                            <?php endif; ?>
                        </td>
                        <td><span class="prod-cat"><?= htmlspecialchars($f['categoria_nombre'] ?? '—') ?></span></td>
                        <td class="price-cell">$<?= number_format($f['precio'],2,',','.') ?></td>
                        <td><span class="stock-pill <?= $stock_class ?>"><?= $stock_dot ?> <?= $f['stock'] ?> u.</span></td>
                        <td>
                            <div class="action-btns">
                                <a href="?editar=<?= $f['id_producto'] ?>#form-panel" class="btn-edit">✏️ Editar</a>
                                <a href="?eliminar=<?= $f['id_producto'] ?>" 
                                   class="btn-del" 
                                   onclick="return confirm('¿Eliminar <?= htmlspecialchars($f['nombre_producto']) ?>?')">🗑 Borrar</a>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; else: ?>
                    <tr>
                        <td colspan="6">
                            <div class="empty-state">
                                <div class="empty-icon">📭</div>
                                <div class="empty-text">No hay productos cargados todavía.</div>
                            </div>
                        </td>
                    </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- FORMULARIO -->
        <div class="panel" id="form-panel">
            <div class="panel-header">
                <span class="panel-title">
                    <?= $producto_editar ? '✏️ Editar Producto' : '➕ Nuevo Producto' ?>
                </span>
                <?php if ($producto_editar): ?>
                    <span class="badge badge-purple">Modo edición</span>
                <?php else: ?>
                    <span class="badge badge-green">Nuevo</span>
                <?php endif; ?>
            </div>

            <div class="form-panel">
                <?php if ($producto_editar): ?>
                <div class="edit-banner">
                    <span>🔵</span>
                    Editando: <b><?= htmlspecialchars($producto_editar['nombre_producto']) ?></b>
                </div>
                <?php endif; ?>

                <form method="POST" action="<?= $_SERVER['PHP_SELF'] ?>#form-panel">
                    <input type="hidden" name="accion" value="<?= $producto_editar ? 'editar' : 'agregar' ?>">
                    <?php if ($producto_editar): ?>
                    <input type="hidden" name="id_producto" value="<?= $producto_editar['id_producto'] ?>">
                    <?php endif; ?>

                    <div class="form-group">
                        <label>Nombre del Producto</label>
                        <input type="text" name="nombre_producto" 
                               placeholder="Ej: Leche entera 1L"
                               value="<?= htmlspecialchars($producto_editar['nombre_producto'] ?? '') ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Descripción</label>
                        <textarea name="descripcion" placeholder="Descripción breve..."><?= htmlspecialchars($producto_editar['descripcion'] ?? '') ?></textarea>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label>Precio ($)</label>
                            <input type="number" name="precio" step="0.01" min="0"
                                   placeholder="0.00"
                                   value="<?= $producto_editar['precio'] ?? '' ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Stock (unidades)</label>
                            <input type="number" name="stock" min="0"
                                   placeholder="0"
                                   value="<?= $producto_editar['stock'] ?? '' ?>" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Categoría</label>
                        <select name="id_categoria" required>
                            <option value="">— Seleccionar —</option>
                            <?php
                            $categorias->data_seek(0);
                            while ($cat = $categorias->fetch_assoc()):
                                $sel = (isset($producto_editar) && $producto_editar['id_categoria'] == $cat['id_categoria']) ? 'selected' : '';
                            ?>
                            <option value="<?= $cat['id_categoria'] ?>" <?= $sel ?>>
                                <?= htmlspecialchars($cat['nombre']) ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="divider"></div>

                    <button type="submit" class="btn-submit <?= $producto_editar ? 'editing' : '' ?>">
                        <?= $producto_editar ? '💾 GUARDAR CAMBIOS' : '+ AGREGAR PRODUCTO' ?>
                    </button>

                    <?php if ($producto_editar): ?>
                    <a href="dashboard_admin.php"><button type="button" class="btn-cancel">✕ Cancelar edición</button></a>
                    <?php endif; ?>
                </form>
            </div>
        </div>

    </div><!-- /panels-grid -->
</main>

<script>
// Auto-hide toast after 3.5s
setTimeout(() => {
    const t = document.querySelector('.toast');
    if (t) t.style.display = 'none';
}, 3500);

// Search filter (client-side highlight)
const searchInput = document.querySelector('.search-bar input');
if (searchInput) {
    searchInput.addEventListener('input', function() {
        const q = this.value.toLowerCase();
        document.querySelectorAll('tbody tr').forEach(row => {
            const name = row.querySelector('.prod-name');
            if (name) {
                row.style.display = name.textContent.toLowerCase().includes(q) ? '' : 'none';
            }
        });
    });
}
</script>
</body>
</html>