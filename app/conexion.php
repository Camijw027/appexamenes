<?php
$conexion = new mysqli("mysql", "cristian", "1234", "base_datos");

if ($conexion->connect_error) {
    die("Error de conexion: " . $conexion->connect_error);
}

$conexion->set_charset("utf8mb4");
?>
