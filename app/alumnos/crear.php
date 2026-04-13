<?php
include '../conexion.php';
include '../helpers.php';

$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cedula = trim($_POST['cedula'] ?? '');
    $nombre = trim($_POST['nombre'] ?? '');
    $apellido = trim($_POST['apellido'] ?? '');
    $direccion = trim($_POST['direccion'] ?? '');
    $genero = trim($_POST['genero'] ?? '');
    $fecha_nacimiento = $_POST['fecha_nacimiento'] ?? '';

    $stmtCrearAlumno = $conexion->prepare(
        "INSERT INTO alumnos (cedula, nombre, apellido, direccion, genero, fecha_nacimiento)
         VALUES (?, ?, ?, ?, ?, ?)"
    );
    $stmtCrearAlumno->bind_param("ssssss", $cedula, $nombre, $apellido, $direccion, $genero, $fecha_nacimiento);

    if ($stmtCrearAlumno->execute()) {
        header('Location: listar.php');
        exit;
    }

    $mensaje = 'No fue posible crear el alumno. Verifica si la cedula ya existe.';
    $stmtCrearAlumno->close();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear alumno</title>
</head>
<body>
    <h1>Crear alumno</h1>

    <?php if ($mensaje !== '') { ?>
        <p><?php echo escaparHtml($mensaje); ?></p>
    <?php } ?>

    <form method="POST" action="crear.php">
        <label for="cedula">Cedula:</label><br>
        <input type="text" id="cedula" name="cedula" required><br><br>

        <label for="nombre">Nombre:</label><br>
        <input type="text" id="nombre" name="nombre" required><br><br>

        <label for="apellido">Apellido:</label><br>
        <input type="text" id="apellido" name="apellido" required><br><br>

        <label for="direccion">Direccion:</label><br>
        <input type="text" id="direccion" name="direccion"><br><br>

        <label for="genero">Genero:</label><br>
        <select id="genero" name="genero">
            <option value="">Seleccione</option>
            <option value="M">Masculino</option>
            <option value="F">Femenino</option>
        </select><br><br>

        <label for="fecha_nacimiento">Fecha de nacimiento:</label><br>
        <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" required><br><br>

        <button type="submit">Guardar</button>
    </form>

    <p><a href="listar.php">Volver al listado</a></p>
</body>
</html>
