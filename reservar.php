<?php
session_start();

$host = "localhost";
$user = "root";
$password = "";
$database = "dbparking";

$conn = new mysqli($host, $user, $password, $database);
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

if (!isset($_SESSION['id_usuario'])) {
    die("Usuario no autenticado. Por favor inicia sesión.");
}

$id_usuario = $_SESSION['id_usuario'];

if (
    isset($_POST['numero_cupo']) &&
    isset($_POST['hora_inicio']) &&
    isset($_POST['hora_fin']) &&
    isset($_POST['monto']) &&
    isset($_POST['metodo_pago']) &&
    isset($_POST['tipo_automovil'])
) {
    $numero_cupo = $_POST['numero_cupo'];
    $hora_inicio = $_POST['hora_inicio'];
    $hora_fin = $_POST['hora_fin'];
    $monto = $_POST['monto'];
    $metodo_pago = $_POST['metodo_pago'];
    $tipo_automovil = $_POST['tipo_automovil'];

    $fecha_actual = date("Y-m-d");
    $fecha_inicio = "$fecha_actual $hora_inicio:00";
    $fecha_fin = "$fecha_actual $hora_fin:00";

    $sql = "INSERT INTO reservas (id_usuario, numero_cupo, fecha_reserva, fecha_vencimiento, monto, metodo_pago, tipo_automovil) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iissdss", $id_usuario, $numero_cupo, $fecha_inicio, $fecha_fin, $monto, $metodo_pago, $tipo_automovil);

    if ($stmt->execute()) {
        // Guardar el id_reserva generado para asociarlo con el vehículo
        $id_reserva = $stmt->insert_id;
        $_SESSION['id_reserva'] = $id_reserva;

        header("Location: vehiculo.html");
        exit();
    } else {
        echo "Error al registrar la reserva: " . $stmt->error;
    }

    $stmt->close();
} else {
    echo "Todos los campos son requeridos.";
}
?>
