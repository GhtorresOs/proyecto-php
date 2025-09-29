<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Registro de Usuario</title>
  <href="assets/css/styles.css" rel="stylesheet">
</head>
<body>
  <form method="POST" action="">
    <h2>Registrar Usuario</h2>
    <input type="text" name="nombre" placeholder="Nombre completo" required>
    <input type="email" name="email" placeholder="Correo electrónico" required>
    <button type="submit" name="registrar">Registrar</button>

    <?php
    if (isset($_POST['registrar'])) {
        // Datos de conexión a MySQL en XAMPP
        $host = "localhost";
        $user = "root";     // usuario por defecto en XAMPP
        $password = "";     // contraseña por defecto vacía
        $dbname = "prueba";

        // Conexión con MySQLi
        $conexion = new mysqli($host, $user, $password, $dbname);

        if ($conexion->connect_error) {
            die("<p class='mensaje' style='color:red;'>❌ Error de conexión: " . $conexion->connect_error . "</p>");
        }

        // Recibir datos
        $nombre = $_POST['nombre'];
        $email = $_POST['email'];

        // Insertar en la base
        $sql = "INSERT INTO usuarios (nombre, email) VALUES ('$nombre', '$email')";

        if ($conexion->query($sql) === TRUE) {
            echo "<p class='mensaje' style='color:green;'>✅ Usuario registrado con éxito</p>";
        } else {
            echo "<p class='mensaje' style='color:red;'>❌ Error: " . $conexion->error . "</p>";
        }

        $conexion->close();
    }
    ?>
  </form>
</body>
</html>