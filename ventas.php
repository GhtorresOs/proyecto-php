<?php
session_start();
// Conexión a la base de datos 'prueba'
$host = "localhost";
$user = "root";
$password = "";
$dbname = "prueba";
$conexion = new mysqli($host, $user, $password, $dbname);
if ($conexion->connect_error) {
  die("Error de conexión a la base de datos: " . $conexion->connect_error);
}

// Determinar permiso/alcance por vendedor: si el usuario no es admin, solo verá sus datos
$vendedor_logeado = isset($_SESSION['usuario_logeado']) ? $_SESSION['usuario_logeado'] : null;
$is_admin = $vendedor_logeado && strtolower($vendedor_logeado) === 'admin';
$where_vendedor = '';
if (!$is_admin && $vendedor_logeado) {
        $where_vendedor = "WHERE vendedor = '" . $conexion->real_escape_string($vendedor_logeado) . "'";
}


$sql = "SELECT producto, COUNT(producto) AS cantidad_productos FROM ventas " . $where_vendedor . " GROUP BY producto";
$res = $conexion->query($sql);
$productos = [];
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $productos[] = $row;
    }
}

// Consulta de qué producto genera más ganancias (suma de precio_neto)
$sql_total = "SELECT producto, SUM(precio_neto) AS total_ventas FROM ventas " . $where_vendedor . " GROUP BY producto";
$res_total = $conexion->query($sql_total);
$ganancias = [];
if ($res_total) {
    while ($row = $res_total->fetch_assoc()) {
        $ganancias[] = $row;
    }
}

$sql_vendedores = "SELECT vendedor, COUNT(*) AS cantidad_ventas FROM ventas " . ($is_admin ? "" : $where_vendedor) . " GROUP BY vendedor ORDER BY cantidad_ventas DESC";
$res_vendedores = $conexion->query($sql_vendedores);
$vendedores = [];
if ($res_vendedores) {
    while ($row = $res_vendedores->fetch_assoc()) {
        $vendedores[] = $row;
    }
}
$sql_years = "SELECT year, COUNT(*) AS producto_year FROM ventas " . $where_vendedor . " GROUP BY year ORDER BY year ASC";
$res_years = $conexion->query($sql_years);
$years = [];
if ($res_years) {
    while ($row = $res_years->fetch_assoc()) {
        $years[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8">
        <title>Proyecto PHP</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    </head>
    <body>
    <style>
        body{
            background-color: #9ccbecff;
        }
    </style>
    <!-- Navbar de la pagina -->
        <nav class="navbar navbar-expand-lg bg-body-tertiary bg-dark border-bottom border-body" data-bs-theme="dark">
            <div class="container-fluid">
                <span class="navbar-brand mb-0 h1">Ventas</span>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarTogglerDemo03" aria-controls="navbarTogglerDemo03" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarTogglerDemo03">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                    <a class="btn btn-secondary" style="margin-right:10px;" href="index.php" role="button">Inicio</a>
                    </li>
                    <li class="nav-item">
                        <a href="graficos.php" class="btn btn-primary" style="margin-right:10px;">Gráficos</a>
                    </li>
                    <li class="nav-item">
                        <?php if (isset($_SESSION['usuario_logeado'])): ?>
                            <button type="button" class="btn btn-warning me-3" disabled><?php echo htmlspecialchars($_SESSION['usuario_logeado']); ?></button>
                        <?php endif; ?> 
                    </li>
                </ul>
                <form class="d-flex" role="search">
                    <input class="form-control me-2" type="search" placeholder="Buscar" aria-label="Search">
                    <button class="btn btn-success" type="submit">Buscar</button>
                </form>
                </div>
            </div>
        </nav>
    <!-- Navbar de la pagina -->
    <!-- Tarjetas de la pagina -->

    <div class="container my-5" style="max-width: 1100px;">
        <div class="row">
            <div class="col-md-4 mb-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Resumen de ventas</h5>
                        <p class="card-text">Cantidad de ventas registradas por producto</p>
                        <?php if (!empty($productos)): ?>
                            <ul class="list-group list-group-flush">
                                <?php foreach ($productos as $p): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <?php echo htmlspecialchars($p['producto']); ?>
                                        <span class="badge bg-primary rounded-pill"><?php echo (int)$p['cantidad_productos']; ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <div class="alert alert-secondary" role="alert">No se encontraron ventas para mostrar.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-md-4 mb-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Ganancias por producto</h5>
                        <p class="card-text">Precio neto (con IVA) generado por producto</p>
                        <?php if (!empty($ganancias)): ?>
                            <ul class="list-group list-group-flush">
                                <?php foreach ($ganancias as $g): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <?php echo htmlspecialchars($g['producto']); ?>
                                        <span class="badge bg-success rounded-pill">$<?php echo number_format((float)$g['total_ventas'], 2, ',', '.'); ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <div class="alert alert-secondary" role="alert">No se encontraron datos.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-md-4 mb-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Ventas por vendedor</h5>
                        <?php if (!empty($vendedores)): ?>
                            <ul class="list-group list-group-flush">
                                <?php foreach ($vendedores as $v): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <?php echo htmlspecialchars($v['vendedor']); ?>
                                        <span class="badge bg-info rounded-pill"><?php echo (int)$v['cantidad_ventas']; ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <div class="alert alert-secondary" role="alert">No se encontraron datos.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="container my-4" style="max-width: 1100px;">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Ventas por año</h5>
                        <p class="card-text">Número de ventas registrado en cada año</p>
                        <?php if (!empty($years)): ?>
                            <ul class="list-group list-group-flush">
                                <?php foreach ($years as $y): ?>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        Año <?php echo htmlspecialchars($y['year']); ?>
                                        <span class="badge bg-secondary rounded-pill"><?php echo (int)$y['producto_year']; ?></span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <div class="alert alert-secondary" role="alert">No se encontraron datos.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Tarjetas de la pagina -->


    </body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</html>