<?php
if (isset($_POST['ingresar'])) {
    //CONEXION A LA BASE DE DATOS
    $host = "localhost";
    $user = "root"; 
    $password = "";
    $dbname = "prueba";

    // CONEXION CON MYSQL
    $conexion = new mysqli($host, $user, $password, $dbname);

    if ($conexion->connect_error) {
        die("Error de conexión: " . $conexion->connect_error);
    }

    $email = $_POST['email'];
    $clave = $_POST['password'];

    // Consulta con seguridad (evita SQL Injection)
    $sql = "SELECT * FROM usuarios WHERE email = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        $usuario = $resultado->fetch_assoc();

    //VERIFICACION A LA BD DE LA CONTRASEÑA
    if ($clave === $usuario['password']) {
      $mensaje = "<div class='alert alert-success mt-3'> Bienvenido <b>" . $usuario['nombre'] . "</b></div>";
    } else {
      $mensaje = "<div class='alert alert-danger mt-3'> Contraseña incorrecta</div>";
    }
    } else {
        $mensaje = "<div class='alert alert-danger mt-3'> Usuario no encontrado</div>";
    }

    $stmt->close();
    $conexion->close();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Iniciar Sesión</title>
  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center justify-content-center vh-100">
  <div class="col-md-4">
    <div class="card shadow-lg border-0 rounded-4">
      <div class="card-body p-4">
        <!-- TABLA PARA INGRESAR LOS DATOS -->
        <h3 class="text-center mb-4"> Iniciar Sesión</h3>
        <form method="POST" action="">
          <div class="mb-3">
            <label class="form-label">Correo electrónico</label>
            <input type="email" class="form-control" name="email" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Contraseña</label>
            <input type="password" class="form-control" name="password" required>
          </div>
            <!-- BOTONES DE INGRESAR, REGISTRARSE Y VOLVER AL INICIO -->
          <button type="submit" name="ingresar" class="btn btn-primary w-100 mb-2">Ingresar</button>
          <!--BOTON DE REGISTRARSE Y REFERENCIA A REGISTRAR_USUARIO.PHP-->
          <div class="text-center mb-2">
            <a href="registrar_usuario.php" class="btn btn-link">¿No tienes cuenta? Regístrate aquí</a>
          </div>
          <!-- BOTON DE VOLVER A LA PAGINA PRINCIPAL -->
          <div class="text-center">
            <a href="index.html" class="btn btn-secondary">Volver al inicio</a>
          </div>
        </form>

        <!-- Mostrar mensajes -->
        <?php if (isset($mensaje)) echo $mensaje; ?>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
