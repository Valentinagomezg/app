<?php
$host = "localhost";
$user = "root";
$password = "";
$database = "dbparking";
$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    echo json_encode(["success" => false]);
    exit();
}

if (isset($_GET['cupo'])) {
    $cupo = (int)$_GET['cupo'];
    $sql = "SELECT u.nombre, r.metodo_pago, r.monto, r.fecha_reserva, r.fecha_vencimiento, r.tipo_automovil
            FROM reservas r
            JOIN usuarios u ON r.id_usuario = u.id_usuario
            WHERE r.numero_cupo = $cupo
            LIMIT 1";

    $result = $conn->query($sql);

    if ($result && $row = $result->fetch_assoc()) {
        echo json_encode([
            "success" => true,
            "nombre" => $row['nombre'],
            "metodo_pago" => $row['metodo_pago'],
            "monto" => $row['monto'],
            "fecha_reserva" => $row['fecha_reserva'],
            "fecha_vencimiento" => $row['fecha_vencimiento'],
            "tipo_automovil" => $row['tipo_automovil']
        ]);
    } else {
        echo json_encode(["success" => false]);
    }
} else {
    echo json_encode(["success" => false]);
}

$conn->close();
?>
