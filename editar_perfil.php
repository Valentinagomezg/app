<?php
session_start();
include("conexion.php");

if (!isset($_SESSION['id_usuario'])) {
    header("Location: index.html");
    exit();
}

$id_usuario = $_SESSION['id_usuario'];
$mensaje = "";
$mensaje_tipo = "";

// Zona horaria
date_default_timezone_set('America/Bogota');

// Obtener fecha actual de MySQL para sincronizar
$fecha_mysql_res = $conexion->query("SELECT NOW() AS fecha_actual");
if (!$fecha_mysql_res) {
    die("Error obteniendo fecha de MySQL: " . $conexion->error);
}
$fecha_actual = $fecha_mysql_res->fetch_assoc()['fecha_actual'];

// Cancelar reservas vencidas
$liberar = $conexion->prepare("
    UPDATE reservas 
    SET estado = 'cancelado', numero_cupo = NULL 
    WHERE fecha_vencimiento IS NOT NULL AND fecha_vencimiento < ? AND estado = 'activo'
");
$liberar->bind_param("s", $fecha_actual);
$liberar->execute();

// Actualizar perfil
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["actualizar"])) {
    $nombre = trim($_POST["nombre"]);
    $telefono = trim($_POST["telefono"]);
    $email = trim($_POST["email"]);

    // Verificar email duplicado
    $verificar = $conexion->prepare("SELECT id_usuario FROM usuarios WHERE email = ? AND id_usuario != ?");
    $verificar->bind_param("si", $email, $id_usuario);
    $verificar->execute();
    $resultado = $verificar->get_result();

    if ($resultado->num_rows > 0) {
        $mensaje = "Este correo ya está registrado.";
        $mensaje_tipo = "error";
    } else {
        $update = $conexion->prepare("UPDATE usuarios SET nombre = ?, telefono = ?, email = ? WHERE id_usuario = ?");
        $update->bind_param("sssi", $nombre, $telefono, $email, $id_usuario);
        if ($update->execute()) {
            $mensaje = "Perfil actualizado correctamente.";
            $mensaje_tipo = "exito";
        } else {
            $mensaje = "Error al actualizar el perfil.";
            $mensaje_tipo = "error";
        }
    }
}

// Cancelar reserva
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["cancelar_reserva"])) {
    $eliminar_reserva = $conexion->prepare("UPDATE reservas SET estado = 'cancelado', numero_cupo = NULL WHERE id_usuario = ? AND estado = 'activo'");
    $eliminar_reserva->bind_param("i", $id_usuario);

    if ($eliminar_reserva->execute()) {
        $mensaje = "Reserva cancelada correctamente. El cupo ha sido liberado. NO SE hará devolución de dinero.";
        $mensaje_tipo = "exito";
    } else {
        $mensaje = "Error al cancelar la reserva.";
        $mensaje_tipo = "error";
    }
}

// Obtener usuario y reserva activa
$consulta = $conexion->prepare("
    SELECT u.nombre, u.telefono, u.email, r.numero_cupo, r.estado, r.fecha_reserva, r.fecha_vencimiento
    FROM usuarios u
    LEFT JOIN reservas r ON u.id_usuario = r.id_usuario AND r.estado = 'activo'
    WHERE u.id_usuario = ?
");
$consulta->bind_param("i", $id_usuario);
$consulta->execute();
$resultado = $consulta->get_result();
$usuario = $resultado->fetch_assoc();

if (!$usuario) {
    die("Usuario no encontrado.");
}

// Ajustar clase mensaje para CSS
$mensaje_tipo_css = ($mensaje_tipo === "exito") ? "success-message" : "error-message";
?>


<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Editar Perfil</title>
  <link rel="stylesheet" href="css/inicioSesion.css">
  <link rel="icon" href="imagenes/logo-primary.png">

</head>
<body>
  <div class="phone-container">
    <div class="login-container">
      <h1>Editar Perfil</h1>

      <?php if (!empty($mensaje)): ?>
        <div class="<?php echo $mensaje_tipo_css; ?>">
          <?php echo htmlspecialchars($mensaje); ?>
        </div>
      <?php endif; ?>

      <form method="POST" autocomplete="off">
        <label for="nombre">Nombre:</label>
        <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($usuario['nombre']); ?>" required>

        <label for="telefono">Teléfono:</label>
        <input type="text" id="telefono" name="telefono" value="<?php echo htmlspecialchars($usuario['telefono']); ?>" required>

        <label for="email">Correo electrónico:</label>
        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($usuario['email']); ?>" required>

        <button type="submit" name="actualizar">Guardar cambios</button>
      </form>

      <?php if (!empty($usuario['numero_cupo']) && $usuario['estado'] === 'activo'): ?>
        <div class="info-cupo" style="margin-top: 20px;">
          <p><strong>Cupo reservado:</strong> <?php echo htmlspecialchars($usuario['numero_cupo']); ?></p>
          <p><strong>Desde:</strong> <?php echo date("d/m/Y H:i", strtotime($usuario['fecha_reserva'])); ?></p>
          <p><strong>Hasta:</strong> <?php echo date("d/m/Y H:i", strtotime($usuario['fecha_vencimiento'])); ?></p>
          <form method="POST" onsubmit="return confirmarCancelacion();">
            <button type="submit" name="cancelar_reserva" class="cancelar-btn">Cancelar Reserva</button>
          </form>
        </div>
      <?php else: ?>
        <div class="info-cupo" style="margin-top: 20px;">
          <p><strong>No tienes cupo reservado actualmente o ya ha vencido.</strong></p>
        </div>
      <?php endif; ?>

      <a href="cupo.php" class="link-button" style="margin-top: 20px;">Volver al menú</a>
    </div>
  </div>

  <script>
    function confirmarCancelacion() {
      return confirm('¿Estás seguro de que quieres cancelar tu reserva?');
    }
  </script>
</body>
</html>
