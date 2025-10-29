<?php
include("conexion.php");

$mensaje = "";
$formulario_mostrado = true; // Controla si se debe seguir mostrando el formulario

if (isset($_GET["token"])) {
    $token = $_GET["token"];

    $consulta = $conexion->prepare("SELECT id_usuario, fecha_expiracion FROM usuarios WHERE token_recuperacion = ?");
    $consulta->bind_param("s", $token);
    $consulta->execute();
    $resultado = $consulta->get_result();

    if ($resultado->num_rows > 0) {
        $usuario = $resultado->fetch_assoc();

        if (strtotime($usuario["fecha_expiracion"]) > time()) {
            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                $nueva_contrasena = $_POST["contrasena"];
                $confirmar_contrasena = $_POST["confirmar_contrasena"];

                if ($nueva_contrasena === $confirmar_contrasena) {
                    $hash_contrasena = password_hash($nueva_contrasena, PASSWORD_DEFAULT);

                    $update = $conexion->prepare("UPDATE usuarios SET contrasena = ?, token_recuperacion = NULL, fecha_expiracion = NULL WHERE id_usuario = ?");
                    $update->bind_param("si", $hash_contrasena, $usuario['id_usuario']);

                    if ($update->execute()) {
                        $mensaje = "<div class='success-message'>✅ Contraseña actualizada exitosamente. <br><a href='index.html' class='link-button'>Iniciar sesión</a></div>";
                        $formulario_mostrado = false; // Ya no mostramos el formulario
                    } else {
                        $mensaje = "<div class='error-message'>❌ Error al actualizar la contraseña. Intenta de nuevo.</div>";
                    }
                } else {
                    $mensaje = "<div class='error-message'>❌ Las contraseñas no coinciden. Inténtalo otra vez.</div>";
                }
            }
        } else {
            $mensaje = "<div class='error-message'>❌ El enlace de recuperación ha expirado. Solicita uno nuevo.</div>";
            $formulario_mostrado = false;
        }
    } else {
        $mensaje = "<div class='error-message'>❌ Token inválido. Solicita recuperación nuevamente.</div>";
        $formulario_mostrado = false;
    }
} else {
    $mensaje = "<div class='error-message'>❌ Acceso no autorizado.</div>";
    $formulario_mostrado = false;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Restablecer Contraseña</title>
  <link rel="stylesheet" href="css/inicioSesion.css">
  <link rel="icon" href="imagenes/logo-primary.png">
</head>
<body>
  <div class="phone-container">
    <div class="login-container">
      <h1>Restablecer Contraseña</h1>

      <?php
      if ($formulario_mostrado) {
      ?>
      <form method="POST">
        <label for="contrasena">Nueva Contraseña:</label>
        <input type="password" id="contrasena" name="contrasena" required>
        
        <label for="confirmar_contrasena">Confirmar Contraseña:</label>
        <input type="password" id="confirmar_contrasena" name="confirmar_contrasena" required>
        
        <button type="submit">Restablecer Contraseña</button>
      </form>
      <?php
      }
      ?>

      <?php
        if (!empty($mensaje)) {
            echo $mensaje;
        }
      ?>
    </div>
  </div>
</body>
</html>
