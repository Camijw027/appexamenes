<?php
include '../conexion.php';
include '../helpers.php';

if (!isset($_GET['id'])) {
    echo 'ID de alumno no especificado.';
    exit;
}

$alumno_id = (int) $_GET['id'];
$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cedula = trim($_POST['cedula'] ?? '');
    $nombre = trim($_POST['nombre'] ?? '');
    $apellido = trim($_POST['apellido'] ?? '');
    $direccion = trim($_POST['direccion'] ?? '');
    $genero = trim($_POST['genero'] ?? '');
    $fecha_nacimiento = $_POST['fecha_nacimiento'] ?? '';

    $stmtActualizarAlumno = $conexion->prepare(
        "UPDATE alumnos
         SET cedula = ?, nombre = ?, apellido = ?, direccion = ?, genero = ?, fecha_nacimiento = ?
         WHERE id = ?"
    );
    $stmtActualizarAlumno->bind_param("ssssssi", $cedula, $nombre, $apellido, $direccion, $genero, $fecha_nacimiento, $alumno_id);

    if ($stmtActualizarAlumno->execute()) {
        header('Location: listar.php');
        exit;
    }

    $mensaje = 'No fue posible actualizar el alumno.';
    $stmtActualizarAlumno->close();
}

$stmtAlumno = $conexion->prepare("SELECT * FROM alumnos WHERE id = ?");
$stmtAlumno->bind_param("i", $alumno_id);
$stmtAlumno->execute();
$resultadoAlumno = $stmtAlumno->get_result();
$alumno = $resultadoAlumno->fetch_assoc();
$stmtAlumno->close();

if (!$alumno) {
    echo 'Alumno no encontrado.';
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar alumno</title>
</head>
<body>
    <h1>Editar alumno</h1>

    <?php if ($mensaje !== '') { ?>
        <p><?php echo escaparHtml($mensaje); ?></p>
    <?php } ?>

    <form method="POST" action="editar.php?id=<?php echo $alumno_id; ?>">
        <label for="cedula">Cedula:</label><br>
        <input type="text" id="cedula" name="cedula" value="<?php echo escaparHtml($alumno['cedula']); ?>" required><br><br>

        <label for="nombre">Nombre:</label><br>
        <input type="text" id="nombre" name="nombre" value="<?php echo escaparHtml($alumno['nombre']); ?>" required><br><br>

        <label for="apellido">Apellido:</label><br>
        <input type="text" id="apellido" name="apellido" value="<?php echo escaparHtml($alumno['apellido']); ?>" required><br><br>

        <label for="direccion">Direccion:</label><br>
        <input type="text" id="direccion" name="direccion" value="<?php echo escaparHtml($alumno['direccion']); ?>"><br><br>

        <label for="genero">Genero:</label><br>
        <select id="genero" name="genero">
            <option value="">Seleccione</option>
            <option value="M" <?php echo $alumno['genero'] === 'M' ? 'selected' : ''; ?>>Masculino</option>
            <option value="F" <?php echo $alumno['genero'] === 'F' ? 'selected' : ''; ?>>Femenino</option>
        </select><br><br>

        <label for="fecha_nacimiento">Fecha de nacimiento:</label><br>
        <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" value="<?php echo escaparHtml($alumno['fecha_nacimiento']); ?>" required><br><br>

        <button type="submit">Actualizar</button>
    </form>

    <p><a href="listar.php">Volver al listado</a></p>
</body>
</html>
