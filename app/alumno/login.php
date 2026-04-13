<?php
session_start();
include '../conexion.php';
include '../helpers.php';

$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cedula = trim($_POST['cedula'] ?? '');
    $apellido = trim($_POST['apellido'] ?? '');

    // Valida el acceso del alumno usando cedula y apellido.
    $stmtAlumno = $conexion->prepare("SELECT id, nombre, apellido, cedula FROM alumnos WHERE cedula = ? AND apellido = ?");
    $stmtAlumno->bind_param("ss", $cedula, $apellido);
    $stmtAlumno->execute();
    $resultadoAlumno = $stmtAlumno->get_result();
    $alumno = $resultadoAlumno->fetch_assoc();
    $stmtAlumno->close();

    if ($alumno) {
        $_SESSION['alumno_id'] = $alumno['id'];
        $_SESSION['alumno_nombre'] = $alumno['nombre'] . ' ' . $alumno['apellido'];
        header('Location: panel.php');
        exit;
    }

    $mensaje = 'Datos no validos. Verifica la cedula y el apellido.';
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ingreso de alumno</title>
</head>
<body>
    <h1>Ingreso de alumno</h1>

    <?php if ($mensaje !== '') { ?>
        <p><?php echo escaparHtml($mensaje); ?></p>
    <?php } ?>

    <form method="POST" action="login.php">
        <label for="cedula">Cedula:</label><br>
        <input type="text" id="cedula" name="cedula" required><br><br>

        <label for="apellido">Apellido:</label><br>
        <input type="text" id="apellido" name="apellido" required><br><br>

        <button type="submit">Ingresar</button>
    </form>

    <p><a href="../index.php">Volver al inicio</a></p>
</body>
</html>
