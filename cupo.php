<?php
session_start();

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['id_usuario'])) {
  header("Location: index.html");
  exit();
}

$id_usuario = $_SESSION['id_usuario'];

// Conexión a la base de datos
$host = "localhost";
$user = "root";
$password = "";
$database = "dbparking";

$conn = new mysqli($host, $user, $password, $database);
$ocupados = [];

if ($conn->connect_error) {
  die("Error de conexión: " . $conn->connect_error);
}

// 🔄 1. Actualizar automáticamente reservas vencidas
$conn->query("UPDATE reservas SET estado='vencido' WHERE fecha_vencimiento < NOW() AND estado='activo'");

// 🧠 2. Verificar si el usuario ya tiene una reserva activa
$sqlActiva = "SELECT * FROM reservas WHERE id_usuario='$id_usuario' AND estado='activo' AND fecha_vencimiento > NOW()";
$resActiva = $conn->query($sqlActiva);
$tieneReserva = $resActiva->num_rows > 0;

// 🚗 3. Obtener cupos ocupados actualmente (activos y vigentes)
$sql = "SELECT numero_cupo FROM reservas WHERE estado='activo' AND fecha_vencimiento > NOW()";
$result = $conn->query($sql);

if ($result) {
  while ($row = $result->fetch_assoc()) {
    $ocupados[] = (int)$row['numero_cupo'];
  }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Agenda tu cupo</title>
  <link rel="stylesheet" href="css/cupo.css" />
  <link rel="icon" href="imagenes/logo-primary.png">
</head>
<body>
  <div class="phone-container">
    <div class="header">
      <h1>Agenda tu cupo</h1>
      <button class="logout-btn">Cerrar sesión</button>
      <a href="tarifa.html" class="tarifas-btn">Tarifas</a>
      <a href="admin.php" class="admin-btn" id="admin-btn" style="display: none;">Admin Panel</a>
    </div>

    <div class="container">
      <?php if ($tieneReserva): ?>
        <div style="background:#fee; padding:10px; border:1px solid red; color:red; font-weight:bold; text-align:center; border-radius:10px; margin-bottom:10px;">
          Ya tienes una reserva activa. Debes esperar a que finalice para agendar otra.
        </div>
      <?php endif; ?>

      <div class="zonas">
        <!-- 🅿 Zona A -->
        <div class="zona">
          <h3>Zona A</h3>
          <div class="parking-lot" id="zonaA">
            <?php
            for ($i = 1; $i <= 250; $i++) {
              $ocupado = in_array($i, $ocupados);
              $clase = $ocupado ? "parking-spot ocupado" : "parking-spot";
              echo '<div class="' . $clase . '" data-num="' . $i . '">' . $i . '</div>';
              if ($i % 20 == 0) echo '<div class="internal-road"></div>';
            }
            ?>
          </div>
        </div>

        <!-- 🔄 Conexión -->
        <div class="conexion"></div>
        <div class="calle"></div>
        <div class="conexion"></div>

        <!-- 🅿 Zona B -->
        <div class="zona">
          <h3>Zona B</h3>
          <div class="parking-lot" id="zonaB">
            <?php
            for ($i = 501; $i <= 1000; $i++) {
              $ocupado = in_array($i, $ocupados);
              $clase = $ocupado ? "parking-spot ocupado" : "parking-spot";
              echo '<div class="' . $clase . '" data-num="' . $i . '">' . $i . '</div>';
              if ($i % 20 == 0) echo '<div class="internal-road"></div>';
            }
            ?>
          </div>
        </div>
      </div>

      <div class="button-container">
        <?php if (!$tieneReserva): ?>
          <a href="#" id="agendar-btn">Agendar</a>
        <?php else: ?>
          <a href="#" id="agendar-btn" style="background:gray; pointer-events:none;">Agendar</a>
        <?php endif; ?>

        <a href="editar_perfil.php" class="action-button">Editar Perfil</a>
      </div>

      <div id="error"></div>
    </div>
  </div>

  <script>
    const ocupados = <?php echo json_encode($ocupados); ?>;
    const tieneReserva = <?php echo $tieneReserva ? 'true' : 'false'; ?>;
    let cupoSeleccionado = null;

    document.addEventListener("DOMContentLoaded", () => {
      const spots = document.querySelectorAll(".parking-spot");

      // Marcar cupos ocupados o disponibles
      spots.forEach((spot) => {
        const numero = parseInt(spot.dataset.num);
        if (ocupados.includes(numero)) {
          spot.classList.add("ocupado");
        } else if (!tieneReserva) {
          spot.addEventListener("click", () => {
            spots.forEach(s => s.classList.remove("seleccionado"));
            spot.classList.add("seleccionado");
            cupoSeleccionado = numero;
            document.getElementById("error").innerText = "";
          });
        }
      });

      // Botón Agendar
      const agendarBtn = document.getElementById("agendar-btn");
      agendarBtn.addEventListener("click", (e) => {
        e.preventDefault();
        if (tieneReserva) {
          document.getElementById("error").innerText = "Ya tienes una reserva activa.";
          return;
        }
        if (cupoSeleccionado) {
          localStorage.setItem("cupoTemporal", cupoSeleccionado);
          window.location.href = "confirmacion.html";
        } else {
          document.getElementById("error").innerText = "Por favor selecciona un cupo disponible.";
        }
      });

      // Botón Cerrar sesión
      document.querySelector(".logout-btn").addEventListener("click", () => {
        localStorage.clear();
        window.location.href = "index.html";
      });

      // Mostrar panel admin si el rol es administrador
      const rol = localStorage.getItem("rol");
      if (rol === "administrador") {
        document.getElementById("admin-btn").style.display = "inline-block";
      }
    });
  </script>
</body>
</html>
