<?php
session_start();
include '../conexion.php';
include '../helpers.php';

if (!isset($_SESSION['alumno_id'])) {
    header('Location: login.php');
    exit;
}

if (!isset($_GET['asignacion_id'])) {
    echo 'Asignacion no especificada.';
    exit;
}

$asignacion_id = (int) $_GET['asignacion_id'];
$alumno_id = (int) $_SESSION['alumno_id'];

// Asegura que la asignacion solicitada realmente pertenece al alumno autenticado.
$stmtAsignacion = $conexion->prepare(
    "SELECT a.id, a.estado, e.id AS examen_id, e.titulo, e.materia, e.fecha_creacion
     FROM asignaciones a
     INNER JOIN examenes e ON e.id = a.examen_id
     WHERE a.id = ? AND a.alumno_id = ?"
);
$stmtAsignacion->bind_param("ii", $asignacion_id, $alumno_id);
$stmtAsignacion->execute();
$resultadoAsignacion = $stmtAsignacion->get_result();
$asignacion = $resultadoAsignacion->fetch_assoc();
$stmtAsignacion->close();

if (!$asignacion) {
    echo 'Asignacion no encontrada.';
    exit;
}

if ($asignacion['estado'] !== 'pendiente') {
    echo 'Este examen ya fue presentado.';
    exit;
}

$examen_id = (int) $asignacion['examen_id'];
$stmtPreguntas = $conexion->prepare("SELECT * FROM preguntas WHERE examen_id = ? ORDER BY id ASC");
$stmtPreguntas->bind_param("i", $examen_id);
$stmtPreguntas->execute();
$preguntas = $stmtPreguntas->get_result();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Presentar examen</title>
</head>
<body>
    <h1>Presentar examen</h1>
    <p><strong>Titulo:</strong> <?php echo escaparHtml($asignacion['titulo']); ?></p>
    <p><strong>Materia:</strong> <?php echo escaparHtml($asignacion['materia']); ?></p>
    <p><strong>Fecha de creacion:</strong> <?php echo escaparHtml($asignacion['fecha_creacion']); ?></p>

    <form method="POST" action="../examenes/calificar.php">
        <input type="hidden" name="asignacion_id" value="<?php echo $asignacion_id; ?>">

        <?php while ($pregunta = $preguntas->fetch_assoc()) { ?>
            <div style="border: 1px solid #ccc; padding: 10px; margin-bottom: 10px;">
                <p><strong>Pregunta:</strong> <?php echo escaparHtml($pregunta['pregunta']); ?></p>
                <label>
                    <input type="radio" name="respuesta_<?php echo (int) $pregunta['id']; ?>" value="A" required>
                    <?php echo escaparHtml($pregunta['opcion_a']); ?>
                </label><br>
                <label>
                    <input type="radio" name="respuesta_<?php echo (int) $pregunta['id']; ?>" value="B" required>
                    <?php echo escaparHtml($pregunta['opcion_b']); ?>
                </label><br>
                <label>
                    <input type="radio" name="respuesta_<?php echo (int) $pregunta['id']; ?>" value="C" required>
                    <?php echo escaparHtml($pregunta['opcion_c']); ?>
                </label><br>
            </div>
        <?php } ?>

        <button type="submit">Enviar respuestas</button>
    </form>

    <p><a href="panel.php">Volver al panel</a></p>
</body>
</html>
<?php
$stmtPreguntas->close();
?>
