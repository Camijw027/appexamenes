<?php
$conexion = new mysqli("mysql", "cristian", "1234", "base_datos");

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}
?>