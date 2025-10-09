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

$cerrarSesion = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cerrar_sesion'])) {
  session_unset();
  session_destroy();
  $cerrarSesion = true;
  header('Location: landing.html');
  exit();
}

// Procesar formulario de nueva venta
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['producto'], $_POST['year'], $_POST['precio_unidad'], $_POST['ingresos'], $_POST['region'])) {
  $producto = $conexion->real_escape_string($_POST['producto']);
  $year = (int)$_POST['year'];
  $precio_unidad = (float)$_POST['precio_unidad'];
  $ingresos = (float)$_POST['ingresos'];
  $region = $conexion->real_escape_string($_POST['region']);
  $vendedor = isset($_SESSION['usuario_logeado']) ? $conexion->real_escape_string($_SESSION['usuario_logeado']) : '';
  $sql_insert = "INSERT INTO ventas (producto, year, vendedor, precio_unidad, ingresos, region) VALUES ('$producto', $year, '$vendedor', $precio_unidad, $ingresos, '$region')";
  $conexion->query($sql_insert);
  // Redirigir para evitar reenvío del formulario
  header("Location: index.php");
  exit();
}

//Consulta para las ventas en el buscador
$sql = "SELECT * FROM ventas";
if (isset($_SESSION['usuario_logeado'])) {
  $usuario = $_SESSION['usuario_logeado'];
  $busqueda = isset($_GET['busqueda']) ? $conexion->real_escape_string($_GET['busqueda']) : '';
  if (strtolower($usuario) === 'admin') {
    if ($busqueda !== '') {
      $sql = "SELECT * FROM ventas WHERE id_venta LIKE '%$busqueda%' OR producto LIKE '%$busqueda%' OR year LIKE '%$busqueda%' OR vendedor LIKE '%$busqueda%' OR precio_unidad LIKE '%$busqueda%' OR ingresos LIKE '%$busqueda%' OR region LIKE '%$busqueda%'";
    }
  } else {
    if ($busqueda !== '') {
      $sql = "SELECT * FROM ventas WHERE vendedor = '" . $conexion->real_escape_string($usuario) . "' AND (id_venta LIKE '%$busqueda%' OR producto LIKE '%$busqueda%' OR year LIKE '%$busqueda%' OR precio_unidad LIKE '%$busqueda%' OR ingresos LIKE '%$busqueda%' OR region LIKE '%$busqueda%')";
    } else {
      $sql = "SELECT * FROM ventas WHERE vendedor = '" . $conexion->real_escape_string($usuario) . "'";
    }
  }
} else if (isset($_GET['busqueda']) && $_GET['busqueda'] !== '') {
  $busqueda = $conexion->real_escape_string($_GET['busqueda']);
  $sql = "SELECT * FROM ventas WHERE id_venta LIKE '%$busqueda%' OR producto LIKE '%$busqueda%' OR year LIKE '%$busqueda%' OR vendedor LIKE '%$busqueda%' OR precio_unidad LIKE '%$busqueda%' OR ingresos LIKE '%$busqueda%' OR region LIKE '%$busqueda%'";
}
$result = mysqli_query($conexion, $sql);
?>

<!DOCTYPE html>
<html lang="es">


<head>
    <meta charset="UTF-8">
    <title>Proyecto PHP</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.4.1/dist/css/bootstrap.min.css" integrity="sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh" crossorigin="anonymous">
</head>

<body>

  <!-- INICIO DE NAVBAR -->
<nav class="navbar navbar-expand-lg navbar-light bg-light">

  <div class="container-fluid">
    <a class="navbar-brand" href="index.php">Inicio</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>


    <a href="graficos.php" class="btn btn-secondary" style="margin-right:5px;">Gráficos</a>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalVenta">Añadir venta</button>
<!-- Modal para añadir venta -->
    <div class="modal fade" id="modalVenta" tabindex="-1" aria-labelledby="modalVentaLabel" aria-hidden="true">
    <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalVentaLabel">Añadir venta</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>

      <div class="modal-body">
        <form id="formVenta" method="post" action="">
          <div class="mb-3">
            <label for="producto" class="form-label">Producto</label>
            <input type="text" class="form-control" id="producto" name="producto" required>
          </div>

          <div class="mb-3">
            <label for="year" class="form-label">Año</label>
            <input type="number" class="form-control" id="year" name="year" min="1900" max="2100" required>
          </div>

          <div class="mb-3">
            <label for="precio_unidad" class="form-label">Precio Unidad</label>
            <input type="number" step="0.01" class="form-control" id="precio_unidad" name="precio_unidad" required>
          </div>

          <div class="mb-3">
            <label for="ingresos" class="form-label">Ingresos</label>
            <input type="number" step="0.01" class="form-control" id="ingresos" name="ingresos" required>
          </div>

          <div class="mb-3">
            <label for="region" class="form-label">Región</label>
            <input type="text" class="form-control" id="region" name="region" required>
          </div>
          <!-- El vendedor se toma de la sesión -->
        </form>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
        <button type="submit" class="btn btn-primary" form="formVenta">Guardar cambios</button>
      </div>
      
    </div>
  </div>
    </div>


   <!-- LINEA QUE MUESTRA EL USUARIO LOGUEADO -->
  <?php if (isset($_SESSION['usuario_logeado'])): ?>
    <span class="navbar-text ml-3 font-weight-bold text-primary" style="margin-right:1000px;"><?php echo htmlspecialchars($_SESSION['usuario_logeado']); ?></span>
  <?php endif; ?>
  <!-- LINEA QUE MUESTRA EL USUARIO LOGUEADO -->


    <div class="collapse navbar-collapse" id="navbarSupportedContent">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0"></ul>

      <form class="d-flex mx-auto" style="max-width: 400px;">
        <input class="form-control me-2" type="search" name="busqueda" placeholder="Buscar" aria-label="Busqueda" value="<?php echo isset($_GET['busqueda']) ? htmlspecialchars($_GET['busqueda']) : ''; ?>">
        <button class="btn btn-outline-success" type="submit">Buscar</button>
      </form>

      <form method="post" class="mx-auto">
        <button class="btn btn-danger mx-auto d-block" type="submit" name="cerrar_sesion" style="margin-right:50px;">Cerrar sesión</button>
      </form>
    </div>
  
  </div>
</nav>
<!-- FIN DE NAVBAR -->
 <p></p>
 <p></p>

<!-- Table 1 - Bootstrap Brain Component -->
<section class="py-3 py-md-5">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-12 col-lg-9 col-xl-8">
        <div class="card widget-card border-light shadow-sm">
          <div class="card-body p-4">
            <h5 class="card-title widget-card-title mb-4">Ventas</h5>
            <div class="table-responsive">
              <table class="table table-borderless bsb-table-xl text-nowrap align-middle m-0">
                <thead>
                  <tr>
                    <th scope="col">ID</th>
                    <th scope="col">Producto</th>
                    <th scope="col">Año</th>
                    <th scope="col">Vendedor</th>
                    <th scope="col">Precio Unidad</th>
                    <th scope="col">Ingresos</th>
                    <th scope="col">Región</th>
                  </tr>
                </thead>
                <tbody>
                  <?php while ($row = mysqli_fetch_assoc($result)): ?>
                  <tr>
                    <td>
                      <?php echo $row ['id_venta']; ?>
                    </td>
                    <td>
                      <?php echo $row ['producto']; ?>
                    </td>
                    <td>
                      <?php echo $row ['year']; ?>  
                    </td>
                    <td>
                      <?php echo $row ['vendedor']; ?>
                    </td>
                    <td>
                      <?php echo $row ['precio_unidad']; ?>
                    </td>
                    <td>
                      <?php echo $row ['ingresos']; ?>
                    </td>
                    <td>
                      <?php echo $row ['region']; ?>
                    </td>
                  </tr>
                  <?php endwhile; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>



</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>