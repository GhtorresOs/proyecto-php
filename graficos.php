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
?>
<!DOCTYPE html>
<html>
<head>
  <title>Gráficos de Ventas</title>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</head>
<body>

  <nav class="navbar navbar-expand-lg bg-body-tertiary" style="background-color: #9ccbecff;">
    <div class="container-fluid">
  <span class="navbar-brand mb-0 h1">Gráficos</span>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <ul class="navbar-nav me-auto mb-2 mb-lg-0">
          <li class="nav-item">
            <a href="index.php" class="btn btn-secondary" style="margin-right:10px;">Inicio</a>
          </li>
          <?php if (isset($_SESSION['usuario_logeado']) && strtolower($_SESSION['usuario_logeado']) === 'admin'): ?>
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
              Ver ventas por vendedor
            </a>
            <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
              <li><a class="dropdown-item" href="graficos.php?vendedor=medina">Ventas de Medina</a></li>
              <li><a class="dropdown-item" href="graficos.php?vendedor=vega">Ventas de Vega</a></li>
              <li><a class="dropdown-item" href="graficos.php?vendedor=araujo">Ventas de Araujo</a></li>
            </ul>
          </li>
          <?php endif; ?>
        </ul>
      </div>
    </div>
  </nav>



  <div class="container mt-5">
  <?php
    $vendedores = ['medina', 'vega', 'araujo'];
    $vendedor_seleccionado = null;
    if (isset($_SESSION['usuario_logeado']) && strtolower($_SESSION['usuario_logeado']) === 'admin') {
      if (isset($_GET['vendedor']) && in_array($_GET['vendedor'], $vendedores)) {
        $vendedor_seleccionado = $_GET['vendedor'];
      }
    } else if (isset($_SESSION['usuario_logeado'])) {
      $vendedor_seleccionado = $_SESSION['usuario_logeado'];
    }

    // Mostrar gráfico general por defecto si es admin y no hay vendedor seleccionado
    if (isset($_SESSION['usuario_logeado']) && strtolower($_SESSION['usuario_logeado']) === 'admin' && !$vendedor_seleccionado) {
      $query = "SELECT producto, SUM(ingresos) as total_ingresos FROM ventas GROUP BY producto";
      $titulo = 'Ingresos por Producto - Total (General)';
      $result = $conexion->query($query);
      $productos = [];
      $ingresos = [];
      while ($row = $result->fetch_assoc()) {
        $productos[] = $row['producto'];
        $ingresos[] = $row['total_ingresos'];
      }
      ?>
      <h2 class="mb-4 text-center"><?php echo $titulo; ?></h2>
      <div class="row justify-content-center">
        <div class="col-md-6">
          <canvas id="graficoVentas"></canvas>
        </div>
        <div class="col-md-6">
          <canvas id="graficoPie"></canvas>
        </div>
      </div>
      <?php
    } elseif ($vendedor_seleccionado) {
      $query = "SELECT producto, SUM(ingresos) as total_ingresos FROM ventas WHERE vendedor = '" . $conexion->real_escape_string($vendedor_seleccionado) . "' GROUP BY producto";
      $titulo = 'Ingresos por Producto - ' . ucfirst($vendedor_seleccionado);
      $result = $conexion->query($query);
      $productos = [];
      $ingresos = [];
      while ($row = $result->fetch_assoc()) {
        $productos[] = $row['producto'];
        $ingresos[] = $row['total_ingresos'];
      }
      ?>
      <h2 class="mb-4 text-center"><?php echo $titulo; ?></h2>
      <div class="row justify-content-center">
        <div class="col-md-6">
          <canvas id="graficoVentas"></canvas>
        </div>
        <div class="col-md-6">
          <canvas id="graficoPie"></canvas>
        </div>
      </div>
      <?php
    } else {
      ?>
      <h2 class="mb-4 text-center">Seleccione un vendedor en el menú para ver sus ventas</h2>
      <?php
    }
    // --- Línea de tiempo: ventas por producto y año ---
    // Obtener todos los años y productos
    $query_timeline = "SELECT year, producto, COUNT(*) as cantidad FROM ventas ";
    if ($vendedor_seleccionado) {
      $query_timeline .= "WHERE vendedor = '" . $conexion->real_escape_string($vendedor_seleccionado) . "' ";
    }
    $query_timeline .= "GROUP BY year, producto ORDER BY year ASC, producto ASC";
    $result_timeline = $conexion->query($query_timeline);
    $timeline_data = [];
    $productos_timeline = [];
    $years_timeline = [];
    while ($row = $result_timeline->fetch_assoc()) {
      $year = $row['year'];
      $producto = $row['producto'];
      $cantidad = (int)$row['cantidad'];
      if (!in_array($year, $years_timeline)) $years_timeline[] = $year;
      if (!in_array($producto, $productos_timeline)) $productos_timeline[] = $producto;
      $timeline_data[$producto][$year] = $cantidad;
    }
    // Asegurar que todos los productos tengan datos para todos los años (rellenar con 0)
    sort($years_timeline);
    foreach ($productos_timeline as $prod) {
      foreach ($years_timeline as $yr) {
        if (!isset($timeline_data[$prod][$yr])) $timeline_data[$prod][$yr] = 0;
      }
      ksort($timeline_data[$prod]);
    }
    ?>

  <div class="row justify-content-center mt-5">
    <div class="col-md-10">
      <h4 class="text-center">Línea de tiempo: Ventas de productos por año</h4>
      <canvas id="timelineChart"></canvas>
    </div>
  </div>

  <script>
    // Gráfico de barras
  <?php if ((isset($_SESSION['usuario_logeado']) && strtolower($_SESSION['usuario_logeado']) === 'admin' && !$vendedor_seleccionado) || $vendedor_seleccionado): ?>
    const ctx = document.getElementById('graficoVentas').getContext('2d');
    const graficoVentas = new Chart(ctx, {
      type: 'bar',
      data: {
        labels: <?php echo json_encode($productos); ?>,
        datasets: [{
          label: 'Ingresos',
          data: <?php echo json_encode($ingresos); ?>,
          backgroundColor: 'rgba(54, 162, 235, 0.5)',
          borderColor: 'rgba(54, 162, 235, 1)',
          borderWidth: 1
        }]
      },
      options: {
        responsive: true,
        scales: { y: { beginAtZero: true } }
      }
    });
    // Gráfico circular (pie)
    const ctxPie = document.getElementById('graficoPie').getContext('2d');
    const graficoPie = new Chart(ctxPie, {
      type: 'pie',
      data: {
        labels: <?php echo json_encode($productos); ?>,
        datasets: [{
          label: 'Ingresos',
          data: <?php echo json_encode($ingresos); ?>,
          backgroundColor: [
            'rgba(54, 162, 235, 0.5)',
            'rgba(255, 99, 132, 0.5)',
            'rgba(255, 206, 86, 0.5)',
            'rgba(75, 192, 192, 0.5)',
            'rgba(153, 102, 255, 0.5)',
            'rgba(255, 159, 64, 0.5)',
            'rgba(201, 203, 207, 0.5)'
          ],
          borderColor: [
            'rgba(54, 162, 235, 1)',
            'rgba(255, 99, 132, 1)',
            'rgba(255, 206, 86, 1)',
            'rgba(75, 192, 192, 1)',
            'rgba(153, 102, 255, 1)',
            'rgba(255, 159, 64, 1)',
            'rgba(201, 203, 207, 1)'
          ],
          borderWidth: 1
        }]
      },
      options: { responsive: true }
    });
    <?php endif; ?>
    // --- Línea de tiempo ---
    const timelineCtx = document.getElementById('timelineChart').getContext('2d');
    const timelineLabels = <?php echo json_encode($years_timeline); ?>;
    const timelineDatasets = [
      <?php foreach ($productos_timeline as $prod): ?>
      {
        label: <?php echo json_encode($prod); ?>,
        data: <?php echo json_encode(array_values($timeline_data[$prod])); ?>,
        fill: false,
        borderColor: '#' + Math.floor(Math.random()*16777215).toString(16),
        tension: 0.1
      },
      <?php endforeach; ?>
    ];
    new Chart(timelineCtx, {
      type: 'line',
      data: {
        labels: timelineLabels,
        datasets: timelineDatasets
      },
      options: {
        responsive: true,
        plugins: {
          title: {
            display: false
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            title: { display: true, text: 'Cantidad de ventas' }
          },
          x: {
            title: { display: true, text: 'Año' }
          }
        }
      }
    });
  </script>
</body>
</html>