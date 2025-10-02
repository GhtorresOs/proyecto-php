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
$sql = "SELECT * FROM ventas";
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
   <!-- LINEA QUE MUESTRA EL USUARIO LOGUEADO -->
  <?php if (isset($_SESSION['usuario_logeado'])): ?>
    <span class="navbar-text ml-3 font-weight-bold text-primary"><?php echo htmlspecialchars($_SESSION['usuario_logeado']); ?></span>
  <?php endif; ?>
  <!-- LINEA QUE MUESTRA EL USUARIO LOGUEADO -->


    <div class="collapse navbar-collapse" id="navbarSupportedContent">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0"></ul>
      <form class="d-flex mx-auto" style="max-width: 400px;">
        <input class="form-control me-2" type="search" placeholder="Buscar" aria-label="Busqueda">
        <button class="btn btn-outline-success" type="submit">Buscar</button>
      </form>
      <form method="post" class="mx-auto">
        <button class="btn btn-danger mx-auto d-block" type="submit" name="cerrar_sesion">Cerrar sesión</button>
      </form>
    </div>
  </div>
</nav>
<!-- FIN DE NAVBAR -->
 <p></p>
 <p></p>
<div>
  <table class="table caption-top">
    <caption>Ventas</caption>
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


  <script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous"></script>
</body>
</html>