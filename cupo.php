<?php
// Obtener cupos ocupados desde la base de datos
$host = "localhost";
$user = "root";
$password = "";
$database = "dbparking";

$conn = new mysqli($host, $user, $password, $database);
$ocupados = [];

if (!$conn->connect_error) {
  $sql = "SELECT numero_cupo FROM reservas";
  $result = $conn->query($sql);
  if ($result) {
    while ($row = $result->fetch_assoc()) {
      $ocupados[] = (int)$row['numero_cupo'];
    }
  }
  $conn->close();
}
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
      <div class="zonas">
        <!-- 🅿 Zona A -->
        <div class="zona">
          <h3>Zona A</h3>
          <div class="parking-lot" id="zonaA">
            <?php
            for ($i = 1; $i <= 250; $i++) {
              echo '<div class="parking-spot" data-num="' . $i . '">' . $i . '</div>';
              if ($i % 50 == 0) echo '<div class="internal-road"></div>';
            }
            ?>
          </div>
        </div>

        <!-- 🔄 Conexión a la carretera -->
        <div class="conexion"></div>

        <!-- 🛣 Carretera principal -->
        <div class="calle"></div>

        <!-- 🔄 Conexión a Zona B -->
        <div class="conexion"></div>

        <!-- 🅿 Zona B -->
        <div class="zona">
          <h3>Zona B</h3>
          <div class="parking-lot" id="zonaB">
            <?php
            for ($i = 501; $i <= 1000; $i++) {
              echo '<div class="parking-spot" data-num="' . $i . '">' . $i . '</div>';
              if ($i % 50 == 0) echo '<div class="internal-road"></div>';
            }
            ?>
          </div>
        </div>
      </div>

      <div class="button-container">
        <a href="#" id="agendar-btn">Agendar</a>
        <a href="editar_perfil.php" class="action-button">Editar Perfil</a>
      </div>

      <div id="error"></div>
    </div>
  </div>

  <script>
    const ocupados = <?php echo json_encode($ocupados); ?>;
    let cupoSeleccionado = null;

    document.addEventListener("DOMContentLoaded", () => {
      const spots = document.querySelectorAll(".parking-spot");

      // Marcar cupos ocupados o disponibles
      spots.forEach((spot) => {
        const numero = parseInt(spot.dataset.num);
        if (ocupados.includes(numero)) {
          spot.classList.add("ocupado");
        } else {
          spot.addEventListener("click", () => {
            spots.forEach(s => s.classList.remove("seleccionado"));
            spot.classList.add("seleccionado");
            cupoSeleccionado = numero;
            document.getElementById("error").innerText = "";
          });
        }
      });

      // Botón Agendar
      document.getElementById("agendar-btn").addEventListener("click", (e) => {
        e.preventDefault();
        if (cupoSeleccionado) {
          localStorage.setItem("cupoTemporal", cupoSeleccionado);
          window.location.href = "confirmacion.html";
        } else {
          document.getElementById("error").innerText = "Por favor selecciona un cupo.";
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