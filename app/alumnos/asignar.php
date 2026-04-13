<?php
include '../conexion.php';
include '../helpers.php';

if (!isset($_GET['id'])) {
    echo 'ID de alumno no especificado.';
    exit;
}

$alumno_id = (int) $_GET['id'];
$mensaje = '';

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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $examen_id = (int) ($_POST['examen_id'] ?? 0);

    // Evita asignar el mismo examen dos veces al mismo alumno.
    $stmtAsignacionExistente = $conexion->prepare("SELECT id FROM asignaciones WHERE alumno_id = ? AND examen_id = ?");
    $stmtAsignacionExistente->bind_param("ii", $alumno_id, $examen_id);
    $stmtAsignacionExistente->execute();
    $asignacionExistente = $stmtAsignacionExistente->get_result()->fetch_assoc();
    $stmtAsignacionExistente->close();

    if ($asignacionExistente) {
        $mensaje = 'Ese examen ya esta asignado a este alumno.';
    } else {
        $fecha = date('Y-m-d');
        $stmtNuevaAsignacion = $conexion->prepare(
            "INSERT INTO asignaciones (alumno_id, examen_id, fecha_asignacion, estado)
             VALUES (?, ?, ?, 'pendiente')"
        );
        $stmtNuevaAsignacion->bind_param("iis", $alumno_id, $examen_id, $fecha);

        if ($stmtNuevaAsignacion->execute()) {
            $mensaje = 'Examen asignado correctamente.';
        } else {
            $mensaje = 'No fue posible asignar el examen.';
        }

        $stmtNuevaAsignacion->close();
    }
}

$examenes = $conexion->query("SELECT id, titulo, materia FROM examenes ORDER BY id ASC");

// Muestra al profesor los examenes ya asignados a este alumno.
$stmtAsignacionesAlumno = $conexion->prepare(
    "SELECT a.id, a.estado, a.fecha_asignacion, e.titulo, e.materia
     FROM asignaciones a
     INNER JOIN examenes e ON e.id = a.examen_id
     WHERE a.alumno_id = ?
     ORDER BY a.id DESC"
);
$stmtAsignacionesAlumno->bind_param("i", $alumno_id);
$stmtAsignacionesAlumno->execute();
$asignacionesAlumno = $stmtAsignacionesAlumno->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asignar examen</title>
</head>
<body>
    <h1>Asignar examen</h1>
    <p><strong>Alumno:</strong> <?php echo escaparHtml($alumno['nombre'] . ' ' . $alumno['apellido']); ?></p>

    <?php if ($mensaje !== '') { ?>
        <p><?php echo escaparHtml($mensaje); ?></p>
    <?php } ?>

    <form method="POST" action="asignar.php?id=<?php echo $alumno_id; ?>">
        <label for="examen_id">Examen:</label><br>
        <select id="examen_id" name="examen_id" required>
            <option value="">Seleccione</option>
            <?php while ($examen = $examenes->fetch_assoc()) { ?>
                <option value="<?php echo (int) $examen['id']; ?>">
                    <?php echo escaparHtml($examen['titulo'] . ' - ' . $examen['materia']); ?>
                </option>
            <?php } ?>
        </select><br><br>

        <button type="submit">Asignar examen</button>
    </form>

    <h2>Examenes asignados</h2>
    <table border="1" cellpadding="8">
        <tr>
            <th>Titulo</th>
            <th>Materia</th>
            <th>Fecha de asignacion</th>
            <th>Estado</th>
        </tr>
        <?php if ($asignacionesAlumno->num_rows > 0) { ?>
            <?php while ($fila = $asignacionesAlumno->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo escaparHtml($fila['titulo']); ?></td>
                    <td><?php echo escaparHtml($fila['materia']); ?></td>
                    <td><?php echo escaparHtml($fila['fecha_asignacion']); ?></td>
                    <td><?php echo escaparHtml(ucfirst($fila['estado'])); ?></td>
                </tr>
            <?php } ?>
        <?php } else { ?>
            <tr>
                <td colspan="4">Este alumno no tiene examenes asignados.</td>
            </tr>
        <?php } ?>
    </table>

    <p><a href="listar.php">Volver al listado de alumnos</a></p>
</body>
</html>
<?php
$stmtAsignacionesAlumno->close();
?>
