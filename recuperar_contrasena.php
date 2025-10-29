<?php
include("conexion.php");

$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["email"])) {
    $email = $_POST["email"];

    // Verificar si el correo existe en la base de datos
    $consulta = $conexion->prepare("SELECT id_usuario FROM usuarios WHERE email = ?");
    $consulta->bind_param("s", $email);
    $consulta->execute();
    $resultado = $consulta->get_result();

    if ($resultado->num_rows > 0) {
        // Si el correo existe, generamos el token
        $usuario = $resultado->fetch_assoc();
        $token = bin2hex(random_bytes(16)); // Token aleatorio
        $fecha_expiracion = date("Y-m-d H:i:s", strtotime("+1 hour"));

        // Guardamos el token y la fecha de expiración en la base de datos
        $update = $conexion->prepare("UPDATE usuarios SET token_recuperacion = ?, fecha_expiracion = ? WHERE id_usuario = ?");
        $update->bind_param("ssi", $token, $fecha_expiracion, $usuario['id_usuario']);
        $update->execute();

        // Enlace de recuperación de contraseña
        $enlace = "http://localhost/app/restablecer_contrasena.php?token=" . $token;

        // Mensaje para mostrar el enlace
        $mensaje = "<a href='$enlace' class='link-button'>Restablecer tu contraseña</a>";
    } else {
        // Si el correo no está registrado
        $mensaje = "<p style='color: red;'>Correo no encontrado en nuestros registros.</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Recuperar Contraseña</title>
  <link rel="stylesheet" href="css/inicioSesion.css">
  <link rel="icon" href="imagenes/logo-primary.png">
</head>
<body>
  <div class="phone-container">
    <div class="login-container">
      <h1>Recuperar Contraseña</h1>
      <form method="POST">
        <label for="email">Ingresa tu correo electrónico:</label>
        <input type="email" id="email" name="email" required>
        <button type="submit">Enviar enlace</button>
      </form>

      <!-- Aquí se mostrará el mensaje dentro -->
      <?php
        if (!empty($mensaje)) {
            echo $mensaje; // Muestra el mensaje si hay
        }
      ?>
    </div>
  </div>
</body>
</html>
<!--hola-->