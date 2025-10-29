<?php
session_start();


if (!isset($_SESSION['id_usuario']) || !isset($_SESSION['id_reserva'])) {
    die("Debes iniciar sesión y realizar una reserva antes de registrar un vehículo.");
}

// Conexión a la base de datos
$host = "localhost";
$user = "root";
$password = "";
$database = "dbparking";
$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Obtener datos del formulario
$placa = $_POST['placa'];
$marca = $_POST['marca']; 
$color = $_POST['color'];
$id_reserva = $_SESSION['id_reserva'];

// Insertar en la tabla 'vehiculos'
$sql = "INSERT INTO vehiculos (id_reserva, placa, marca, color) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    die("Error al preparar la consulta: " . $conn->error);
}

$stmt->bind_param("isss", $id_reserva, $placa, $marca, $color);

if ($stmt->execute()) {
    echo "Vehículo registrado exitosamente.";
    unset($_SESSION['id_reserva']); // Limpiar después de usarlo
} else {
    echo "Error al registrar el vehículo: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>

