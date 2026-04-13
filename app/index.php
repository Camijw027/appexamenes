<?php
include 'conexion.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>App Examenes</title>
</head>
<body>
    <h1>App Examenes</h1>
    <p>Selecciona como deseas ingresar al sistema.</p>

    <ul>
        <li><a href="alumno/login.php">Ingresar como alumno</a></li>
        <li><a href="profesor/panel.php">Ingresar como profesor</a></li>
    </ul>

    <p>Conexion activa con la base de datos.</p>
</body>
</html>
