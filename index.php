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

//CIERRE DE SESION
$cerrarSesion = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cerrar_sesion'])) {
  session_unset();
  session_destroy();
  $cerrarSesion = true;
  header('Location: landing.html');
  exit();
}

// NUEVA VENTA Y ELIMINAR VENTA
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Alta de venta
    if (isset($_POST['producto'], $_POST['year'], $_POST['precio_unidad'], $_POST['ingresos'], $_POST['region'])) {
        $producto = $conexion->real_escape_string($_POST['producto']);
        $year = (int)$_POST['year'];
        $precio_unidad = (float)$_POST['precio_unidad'];
        $ingresos = (float)$_POST['ingresos'];
        $region = $conexion->real_escape_string($_POST['region']);
        $vendedor = isset($_SESSION['usuario_logeado']) ? $conexion->real_escape_string($_SESSION['usuario_logeado']) : '';

        // Buscar el primer ID disponible (rellenar huecos). Si la tabla está vacía, usar 1.
        $gapRes = $conexion->query("SELECT MIN(t1.id_venta + 1) AS gap FROM ventas t1 LEFT JOIN ventas t2 ON t1.id_venta + 1 = t2.id_venta WHERE t2.id_venta IS NULL");
        $id_to_use = 1;
        if ($gapRes) {
            $gapRow = $gapRes->fetch_assoc();
            if (!empty($gapRow['gap'])) {
                $id_to_use = (int)$gapRow['gap'];
            } else {
                // Si no hay filas, o gap es null, tomar 1 o max+1
                $maxRes = $conexion->query("SELECT MAX(id_venta) AS maxid FROM ventas");
                $maxRow = $maxRes ? $maxRes->fetch_assoc() : null;
                $id_to_use = $maxRow && $maxRow['maxid'] ? ((int)$maxRow['maxid'] + 1) : 1;
            }
        }

        // Insertar especificando id_venta para rellenar huecos
        $sql_insert = "INSERT INTO ventas (id_venta, producto, year, vendedor, precio_unidad, ingresos, region) VALUES ($id_to_use, '$producto', $year, '$vendedor', $precio_unidad, $ingresos, '$region')";
        $ok = $conexion->query($sql_insert);

        // Asegurar que el AUTO_INCREMENT queda en max(id)+1 para futuras inserciones automáticas
        $maxRes2 = $conexion->query("SELECT MAX(id_venta) AS maxid FROM ventas");
        if ($maxRes2) {
            $maxRow2 = $maxRes2->fetch_assoc();
            $nextAi = ($maxRow2 && $maxRow2['maxid']) ? ((int)$maxRow2['maxid'] + 1) : 1;
            // Ajustar AUTO_INCREMENT — si el motor permite, esto asegura consistencia
            $conexion->query("ALTER TABLE ventas AUTO_INCREMENT = $nextAi");
        }

        // Redirigir para evitar reenvío del formulario
        header("Location: index.php");
        exit();
    }
  // Eliminar venta
  if (isset($_POST['id_venta_eliminar'])) {
    $id_venta_eliminar = (int)$_POST['id_venta_eliminar'];
    $sql_delete = "DELETE FROM ventas WHERE id_venta = $id_venta_eliminar";
    $resultado = $conexion->query($sql_delete);
    if ($resultado) {
      echo "<script>alert('La venta ha sido eliminada correctamente');window.location='index.php';</script>";
    } else {
      echo "<script>alert('Error al eliminar la venta');window.location='index.php';</script>";
    }
    exit();
  }
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


// CONSULTA PARA EDITAR VENTA (obtener datos para el modal)
if (isset($_GET['editar_venta'])) {
  $id_editar = (int)$_GET['editar_venta'];
  $sql_editar = "SELECT * FROM ventas WHERE id_venta = $id_editar";
  $res_editar = $conexion->query($sql_editar);
  $venta_editar = $res_editar ? $res_editar->fetch_assoc() : null;
}

// ACTUALIZAR VENTA (guardar cambios desde el modal)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_venta_editar'])) {
  $id_venta_editar = (int)$_POST['id_venta_editar'];
  $producto = $conexion->real_escape_string($_POST['producto_editar']);
  $year = (int)$_POST['year_editar'];
  $precio_unidad = (float)$_POST['precio_unidad_editar'];
  $ingresos = (float)$_POST['ingresos_editar'];
  $region = $conexion->real_escape_string($_POST['region_editar']);
  $sql_update = "UPDATE ventas SET producto='$producto', year=$year, precio_unidad=$precio_unidad, ingresos=$ingresos, region='$region' WHERE id_venta=$id_venta_editar";
  $conexion->query($sql_update);
  echo "<script>alert('Venta actualizada correctamente');window.location='index.php';</script>";
  exit();
}

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
      <!-- Navbar de la pagina -->
        <nav class="navbar navbar-expand-lg" style="background-color: #9ccbecff;">
            <div class="container-fluid">
                <span class="navbar-brand mb-0 h1">Inicio</span>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                        <a href="graficos.php" class="btn btn-primary" style="margin-right:10px;">Gráficos</a>
                        <button type="button" class="btn btn-primary" style="margin-right:10px;" data-bs-toggle="modal" data-bs-target="#añadirventa">Añadir venta</button>

                    <a href="ventas.php" class="btn btn-secondary" style="margin-right:10px;">Ventas</a> 
                    <form class="d-flex" role="search">
                        <input class="form-control me-2" type="search" style="margin-right:10px;" name="busqueda" placeholder="Buscar" aria-label="Busqueda" value="<?php echo isset($_GET['busqueda']) ? htmlspecialchars($_GET['busqueda']) : ''; ?>">
                        <button class=" btn btn-success" type="submit" style="margin-right:10px;">Buscar</button>
                    </form>
                    <div>
                      <?php if (isset($_SESSION['usuario_logeado'])): ?>
                        <span class="navbar-text font-weight-bold text-primary me-3" style="margin-right:10px;"><?php echo htmlspecialchars($_SESSION['usuario_logeado']); ?></span>
                      <?php endif; ?>                                            
                    </div>
                    <form method="post" class="mb-0">
                        <button class="btn btn-danger" type="submit" name="cerrar_sesion" style="margin-right:10px;">Cerrar Sesion</button>
                    </form>       
                </div>
            </div>
        </nav>
      <!-- Navbar de la pagina -->
        <p></p>
        <p></p>
    <!-- Tarjeta para la tabla -->
        <section class="py-3 py-md-5">
            <div class="container-fluid">
                <div class="row justify-content-center">
                    <div class="col-12 col-lg-9 col-xl-8">
                        <div class="card widget-card border-light shadow-sm">
                            <div class="card-body p-4">
                                <h5 class="card-title widget-card-title mb-4">Ventas</h5>
                                <div class="table-responsive">
                                    <table class="table table-bordered bsb-table-xl text-nowrap align-middle m-0" >
                                        <thead>
                                            <tr>
                                                <th scope="col">ID</th>
                                                <th scope="col">Producto</th>
                                                <th scope="col">Año</th>
                                                <th scope="col">Vendedor</th>
                                                <th scope="col">Precio Unidad</th>
                                                <th scope="col">Ingresos</th>
                                                <th scope="col">Región</th>
                                                <th scope="col">Acciones</th>                                                
                                            </tr>                                            
                                        </thead>
                                        <tbody>
                                            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                            <tr>
                                                <td><?php echo $row ['id_venta']; ?></td>
                                                <td><?php echo $row ['producto']; ?></td>
                                                <td><?php echo $row ['year']; ?></td>
                                                <td><?php echo $row ['vendedor']; ?></td>
                                                <td><?php echo $row ['precio_unidad']; ?></td>
                                                <td><?php echo $row ['ingresos']; ?></td>
                                                <td><?php echo $row ['region']; ?></td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <button type="button" class="btn btn-info" style="margin-right:10px;" data-bs-toggle="modal" data-bs-target="#editarventa" data-id="<?php echo $row['id_venta']; ?>" data-producto="<?php echo $row['producto']; ?>" data-year="<?php echo $row['year']; ?>" data-precio_unidad="<?php echo $row['precio_unidad']; ?>" data-ingresos="<?php echo $row['ingresos']; ?>" data-region="<?php echo $row['region']; ?>">
                                                            Editar Venta
                                                        </button>
                                                        <button type="button" class="btn btn-danger" style="margin-left:10px;" data-bs-toggle="modal" data-bs-target="#eliminarventa" data-id="<?php echo $row['id_venta']; ?>">
                                                            Eliminar Venta
                                                        </button>
                                                    </div>         
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
    <!-- Tarjeta para la tabla -->

    <!-- Modal Añadir Venta-->
        <div class="modal fade" id="añadirventa" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Añadir Venta</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
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
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-primary" form="formVenta">Guardar cambios</button>
                    </div>
                </div>
            </div>
        </div>
    <!-- Modal Añadir Venta-->
    

    <!-- Modal Editar Venta -->
        <div class="modal fade" id="editarventa" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Título del modal</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="formEditarVenta" method="post" action="">
                            <input type="hidden" id="id_venta_editar" name="id_venta_editar">
                            <div class="mb-3">
                                <label for="producto_editar" class="form-label">Producto</label>
                                <input type="text" class="form-control" id="producto_editar" name="producto_editar" required>
                            </div>
                            <div class="mb-3">
                                <label for="year_editar" class="form-label">Año</label>
                                <input type="number" class="form-control" id="year_editar" name="year_editar" min="1900" max="2100" required>
                            </div>
                            <div class="mb-3">
                                <label for="precio_unidad_editar" class="form-label">Precio Unidad</label>
                                <input type="number" step="0.01" class="form-control" id="precio_unidad_editar" name="precio_unidad_editar" required>
                            </div>
                            <div class="mb-3">
                                <label for="ingresos_editar" class="form-label">Ingresos</label>
                                <input type="number" step="0.01" class="form-control" id="ingresos_editar" name="ingresos_editar" required>
                            </div>
                            <div class="mb-3">
                                <label for="region_editar" class="form-label">Región</label>
                                <input type="text" class="form-control" id="region_editar" name="region_editar" required>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-primary" form="formEditarVenta">Guardar cambios</button>
                    </div>
                </div>
            </div>
        </div>
    <!-- Modal Editar Venta-->


    <!-- Modal Eliminar Venta-->
        <div class="modal fade" id="eliminarventa" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Eliminar Venta</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="formEliminarVenta" method="post" action="">
                            <input type="hidden" id="id_venta_eliminar" name="id_venta_eliminar">
                            <p>¿Estás seguro de que deseas eliminar esta venta?</p>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-primary" form="formEliminarVenta">Eliminar</button>
                    </div>
                </div>
            </div>
        </div>
    <!-- Modal Eliminar Venta-->
    <script>
        // Modal editar: el id del modal en el HTML es 'editarventa'
        var modalEditar = document.getElementById('editarventa');
        if (modalEditar) {
            modalEditar.addEventListener('show.bs.modal', function (event) {
                var button = event.relatedTarget;
                document.getElementById('id_venta_editar').value = button.getAttribute('data-id');
                document.getElementById('producto_editar').value = button.getAttribute('data-producto');
                document.getElementById('year_editar').value = button.getAttribute('data-year');
                document.getElementById('precio_unidad_editar').value = button.getAttribute('data-precio_unidad');
                document.getElementById('ingresos_editar').value = button.getAttribute('data-ingresos');
                document.getElementById('region_editar').value = button.getAttribute('data-region');
            });
        }

        // Modal eliminar: el id del modal en el HTML es 'eliminarventa'
        var modalEliminar = document.getElementById('eliminarventa');
        if (modalEliminar) {
            modalEliminar.addEventListener('show.bs.modal', function (event) {
                var button = event.relatedTarget;
                document.getElementById('id_venta_eliminar').value = button.getAttribute('data-id');
            });
        }
    </script>
    </body>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</html>