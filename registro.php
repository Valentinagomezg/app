<?php
// Incluye el archivo de conexión a la base de datos
include 'conexion.php';

// Inicia la sesión para almacenar variables de sesión
session_start();

// ------------------- RECOGER DATOS DEL FORMULARIO -------------------
$nombre = trim($_POST['nombre']);
$telefono = trim($_POST['telefono']);
$email = trim($_POST['email']);
$contrasena = $_POST['contrasena'];
$confirmar_contrasena = $_POST['confirmar_contrasena'];
$fecha_nacimiento = $_POST['fecha_nacimiento'];
$rol = $_POST['rol'];
$codigo_admin = $_POST['codigo_admin'] ?? '';

// ------------------- VALIDACIONES -------------------
if (empty($nombre) || empty($telefono) || empty($email) || empty($contrasena) || empty($confirmar_contrasena) || empty($fecha_nacimiento) || empty($rol)) {
    echo "<script>
            alert('Todos los campos son obligatorios.');
            window.history.back();
          </script>";
    exit();
}

if ($contrasena !== $confirmar_contrasena) {
    echo "<script>
            alert('Las contraseñas no coinciden.');
            window.history.back();
          </script>";
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo "<script>
            alert('Correo electrónico no válido.');
            window.history.back();
          </script>";
    exit();
}

// ------------------- VERIFICACIÓN DE CORREO REPETIDO -------------------
$sql_check = "SELECT id_usuario FROM usuarios WHERE email = ?";
$stmt_check = $conexion->prepare($sql_check);
$stmt_check->bind_param("s", $email);
$stmt_check->execute();
$stmt_check->store_result();

if ($stmt_check->num_rows > 0) {
    echo "<script>
            alert('El correo electrónico ya está registrado.');
            window.history.back();
          </script>";
    exit();
}
$stmt_check->close();

// ------------------- VERIFICAR CÓDIGO ADMINISTRADOR -------------------
if ($rol === 'administrador' && $codigo_admin !== '1075652100') {
    echo "<script>
        alert('Código de administrador incorrecto.');
        window.history.back();
    </script>";
    exit();
}

// ------------------- REGISTRAR USUARIO -------------------
$hash_contrasena = password_hash($contrasena, PASSWORD_DEFAULT);

$sql = "INSERT INTO usuarios (nombre, telefono, email, contrasena, fecha_nacimiento, rol)
        VALUES (?, ?, ?, ?, ?, ?)";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("ssssss", $nombre, $telefono, $email, $hash_contrasena, $fecha_nacimiento, $rol);

if ($stmt->execute()) {
    $_SESSION['id_usuario'] = $conexion->insert_id;
    $_SESSION['rol'] = $rol;

    // Redirigir según el rol
    if ($rol === 'administrador') {
        header("Location: admin.php");
    } else {
        header("Location: cupo.php");
    }
    exit();
} else {
    echo "<script>
        alert('Error al registrar: " . addslashes($stmt->error) . "');
        window.history.back();
    </script>";
}

$stmt->close();
$conexion->close();
?>
