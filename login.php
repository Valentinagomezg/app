<?php
// Incluye el archivo con la conexión a la base de datos
include 'conexion.php';

// Inicia la sesión
session_start();

// ------------------- RECOGER DATOS DEL FORMULARIO -------------------
$email = trim($_POST['email']);
$contrasena = trim($_POST['password']);

// ------------------- CONSULTAR USUARIO -------------------
$sql = "SELECT * FROM usuarios WHERE email = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows > 0) {
    $usuario = $resultado->fetch_assoc();

    if (password_verify($contrasena, $usuario['contrasena'])) {
        // Guardar datos en la sesión
        $_SESSION['id_usuario'] = $usuario['id_usuario'];
        $_SESSION['rol'] = $usuario['rol'];

        // Guardar también en localStorage y redirigir según el rol
        echo "<script>
            localStorage.setItem('id_usuario', '{$usuario['id_usuario']}');
            localStorage.setItem('rol', '{$usuario['rol']}');

            if ('{$usuario['rol']}' === 'administrador') {
                window.location.href = 'admin.php';
            } else {
                window.location.href = 'cupo.php';
            }
        </script>";
        exit();
    } else {
        echo "<script>
            alert('Correo o contraseña incorrectos.');
            window.history.back();
        </script>";
    }
} else {
    echo "<script>
        alert('Correo o contraseña incorrectos.');
        window.history.back();
    </script>";
}

$stmt->close();
$conexion->close();
?>

