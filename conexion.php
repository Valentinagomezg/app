<?php
$host = "localhost";
$usuario = "root";  
$clave = "";        
$base_de_datos = "dbparking";  

$conexion = new mysqli($host, $usuario, $clave, $base_de_datos);

// Verificar si hay error en la conexión
if ($conexion->connect_error) {
    die("Conexión fallida: " . $conexion->connect_error);
}
?>
