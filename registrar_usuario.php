<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Registro de Usuario</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
</head>
<body>

  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-6">
        <div class="card shadow-lg border-0 rounded-4">
          <div class="card-body p-5">
            <h3 class="text-center mb-4">Registrar Usuario</h3>
            <form method="POST" action="">
              <div class="mb-3">
                <label for="nombre" class="form-label">Nombre completo</label>
                <input type="text" class="form-control" id="nombre" name="nombre" required>
              </div>
              <div class="mb-3">
                <label for="contraseña" class="form-label">Contraseña</label>
                <input type="text" class="form-control" id="contraseña" name="contraseña" required>
              </div>
              <div class="mb-3">
                <label for="email" class="form-label">Correo electrónico</label>
                <input type="email" class="form-control" id="email" name="email" required>
              </div>
              <button type="submit" name="registrar" class="btn btn-primary w-100">Registrar</button>
            </form>

    <?php
    if (isset($_POST['registrar'])) {
        
        $host = "localhost";
        $user = "root";     
        $password = "";     
        $dbname = "prueba";

        // Conexión con MySQLi
        $conexion = new mysqli($host, $user, $password, $dbname);

        if ($conexion->connect_error) {
            die("<p class='mensaje' style='color:red;'>❌ Error de conexión: " . $conexion->connect_error . "</p>");
        }

        // Recibir datos
        $nombre = $_POST['nombre'];
        $password = $_POST['contraseña'];
        $email = $_POST['email'];

        // Insertar en la base
  $sql = "INSERT INTO usuarios (nombre, email, password) VALUES ('$nombre', '$email', '$password')";

    if ($conexion->query($sql) === TRUE) {
      echo "<p class='mensaje' style='color:green;'>✅ Usuario registrado con éxito. Serás redirigido al login...</p>";
      echo "<script>setTimeout(function(){ window.location.href = 'login.php'; }, 2000);</script>";
    } else {
      echo "<p class='mensaje' style='color:red;'>❌ Error: " . $conexion->error . "</p>";
    }

        $conexion->close();
    }
    ?>
  </form>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js" integrity="sha384-FKyoEForCGlyvwx9Hj09JcYn3nv7wiPVlz7YYwJrWVcXK/BmnVDxM+D2scQbITxI" crossorigin="anonymous"></script>
</body>
</html>