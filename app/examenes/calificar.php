<?php
session_start();
include '../conexion.php';
include '../helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['asignacion_id']) || !isset($_SESSION['alumno_id'])) {
    echo 'Solicitud invalida.';
    exit;
}

$asignacion_id = (int) $_POST['asignacion_id'];
$alumno_id = (int) $_SESSION['alumno_id'];

// Verifica que la asignacion exista y corresponda al alumno autenticado.
$stmtAsignacion = $conexion->prepare(
    "SELECT a.id, a.estado, e.id AS examen_id, e.titulo, e.materia
     FROM asignaciones a
     INNER JOIN examenes e ON e.id = a.examen_id
     WHERE a.id = ? AND a.alumno_id = ?"
);
$stmtAsignacion->bind_param("ii", $asignacion_id, $alumno_id);
$stmtAsignacion->execute();
$resultado_asignacion = $stmtAsignacion->get_result();
$asignacion = $resultado_asignacion->fetch_assoc();
$stmtAsignacion->close();

if (!$asignacion) {
    echo 'Asignacion no encontrada.';
    exit;
}

if ($asignacion['estado'] !== 'pendiente') {
    echo 'Este examen ya fue enviado.';
    exit;
}

$examen_id = (int) $asignacion['examen_id'];
$stmtPreguntas = $conexion->prepare("SELECT * FROM preguntas WHERE examen_id = ? ORDER BY id ASC");
$stmtPreguntas->bind_param("i", $examen_id);
$stmtPreguntas->execute();
$resultadoPreguntas = $stmtPreguntas->get_result();

$total = 0;
$correctas = 0;
$detalle = [];

// Recorre cada pregunta para contar respuestas correctas y guardar un detalle visible.
while ($pregunta = $resultadoPreguntas->fetch_assoc()) {
    $total++;
    $respuesta_usuario = $_POST['respuesta_' . $pregunta['id']] ?? null;
    $es_correcta = $respuesta_usuario === $pregunta['respuesta_correcta'];

    if ($es_correcta) {
        $correctas++;
    }

    $detalle[] = [
        'pregunta' => $pregunta['pregunta'],
        'respuesta_usuario' => $respuesta_usuario,
        'respuesta_correcta' => $pregunta['respuesta_correcta'],
        'es_correcta' => $es_correcta,
    ];
}
$stmtPreguntas->close();

list($calificacion, $descripcion) = obtenerEscala($correctas);

// Guarda el resultado final y marca la asignacion como presentada en una sola transaccion.
$conexion->begin_transaction();

try {
    $fecha_presentacion = date('Y-m-d H:i:s');

    $stmtResultado = $conexion->prepare(
        "INSERT INTO resultados (asignacion_id, respuestas_correctas, calificacion, descripcion, fecha_presentacion)
         VALUES (?, ?, ?, ?, ?)"
    );
    $stmtResultado->bind_param("iiiss", $asignacion_id, $correctas, $calificacion, $descripcion, $fecha_presentacion);
    $stmtResultado->execute();
    $stmtResultado->close();

    $stmtActualizarAsignacion = $conexion->prepare("UPDATE asignaciones SET estado = 'presentado' WHERE id = ?");
    $stmtActualizarAsignacion->bind_param("i", $asignacion_id);
    $stmtActualizarAsignacion->execute();
    $stmtActualizarAsignacion->close();

    $conexion->commit();
} catch (Throwable $e) {
    $conexion->rollback();
    echo 'No fue posible guardar el resultado.';
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resultado del examen</title>
</head>
<body>
    <h1>Resultado del examen</h1>
    <p><strong>Titulo:</strong> <?php echo escaparHtml($asignacion['titulo']); ?></p>
    <p><strong>Materia:</strong> <?php echo escaparHtml($asignacion['materia']); ?></p>
    <p><strong>Respuestas correctas:</strong> <?php echo $correctas; ?> de <?php echo $total; ?></p>
    <p><strong>Calificacion:</strong> <?php echo $calificacion; ?></p>
    <p><strong>Descripcion:</strong> <?php echo escaparHtml($descripcion); ?></p>

    <h2>Detalle</h2>

    <?php foreach ($detalle as $item) { ?>
        <div style="border: 1px solid #ccc; padding: 10px; margin-bottom: 10px;">
            <p><strong>Pregunta:</strong> <?php echo escaparHtml($item['pregunta']); ?></p>
            <p><strong>Tu respuesta:</strong> <?php echo escaparHtml($item['respuesta_usuario'] ?? 'Sin responder'); ?></p>
            <p><strong>Respuesta correcta:</strong> <?php echo escaparHtml($item['respuesta_correcta']); ?></p>
            <p><strong>Estado:</strong> <?php echo $item['es_correcta'] ? 'Correcta' : 'Incorrecta'; ?></p>
        </div>
    <?php } ?>

    <p><a href="../alumno/panel.php">Volver al panel del alumno</a></p>
</body>
</html>
