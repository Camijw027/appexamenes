<?php
session_start();
include '../conexion.php';
include '../helpers.php';

if (!isset($_SESSION['alumno_id'])) {
    header('Location: login.php');
    exit;
}

$alumno_id = (int) $_SESSION['alumno_id'];

// Consulta solo los examenes que fueron asignados al alumno autenticado.
$stmtAsignaciones = $conexion->prepare(
    "SELECT a.id, a.estado, a.fecha_asignacion, e.titulo, e.materia
     FROM asignaciones a
     INNER JOIN examenes e ON e.id = a.examen_id
     WHERE a.alumno_id = ?
     ORDER BY a.id DESC"
);
$stmtAsignaciones->bind_param("i", $alumno_id);
$stmtAsignaciones->execute();
$asignaciones = $stmtAsignaciones->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel del alumno</title>
</head>
<body>
    <h1>Panel del alumno</h1>
    <p>Bienvenido, <?php echo escaparHtml($_SESSION['alumno_nombre']); ?>.</p>

    <h2>Examenes asignados</h2>

    <table border="1" cellpadding="8">
        <tr>
            <th>Titulo</th>
            <th>Materia</th>
            <th>Fecha de asignacion</th>
            <th>Estado</th>
            <th>Accion</th>
        </tr>
        <?php if ($asignaciones->num_rows > 0) { ?>
            <?php while ($fila = $asignaciones->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo escaparHtml($fila['titulo']); ?></td>
                    <td><?php echo escaparHtml($fila['materia']); ?></td>
                    <td><?php echo escaparHtml($fila['fecha_asignacion']); ?></td>
                    <td><?php echo escaparHtml(ucfirst($fila['estado'])); ?></td>
                    <td>
                        <?php if ($fila['estado'] === 'pendiente') { ?>
                            <a href="presentar.php?asignacion_id=<?php echo (int) $fila['id']; ?>">Presentar examen</a>
                        <?php } else { ?>
                            Examen enviado
                        <?php } ?>
                    </td>
                </tr>
            <?php } ?>
        <?php } else { ?>
            <tr>
                <td colspan="5">No tienes examenes asignados.</td>
            </tr>
        <?php } ?>
    </table>

    <p><a href="logout.php">Cerrar sesion</a></p>
</body>
</html>
<?php
$stmtAsignaciones->close();
?>
