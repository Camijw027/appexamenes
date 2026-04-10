<?php
include('../conexion.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['examen_id'])) {
    echo "Solicitud invalida.";
    exit;
}

$examen_id = (int) $_POST['examen_id'];

$sql_examen = "SELECT * FROM examenes WHERE id = $examen_id";
$resultado_examen = $conexion->query($sql_examen);

if (!$resultado_examen || $resultado_examen->num_rows === 0) {
    echo "Examen no encontrado.";
    exit;
}

$examen = $resultado_examen->fetch_assoc();
$sql_preguntas = "SELECT * FROM preguntas WHERE examen_id = $examen_id";
$resultado_preguntas = $conexion->query($sql_preguntas);

$total = 0;
$correctas = 0;
$detalle = [];

while ($pregunta = $resultado_preguntas->fetch_assoc()) {
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

$porcentaje = $total > 0 ? round(($correctas / $total) * 100, 2) : 0;
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
    <p><strong>Titulo:</strong> <?php echo htmlspecialchars($examen['titulo']); ?></p>
    <p><strong>Materia:</strong> <?php echo htmlspecialchars($examen['materia']); ?></p>
    <p><strong>Respuestas correctas:</strong> <?php echo $correctas; ?> de <?php echo $total; ?></p>
    <p><strong>Porcentaje:</strong> <?php echo $porcentaje; ?>%</p>

    <h2>Detalle</h2>

    <?php if ($total === 0) { ?>
        <p>Este examen no tiene preguntas registradas.</p>
    <?php } else { ?>
        <?php foreach ($detalle as $item) { ?>
            <div style="border: 1px solid #ccc; padding: 10px; margin-bottom: 10px;">
                <p><strong>Pregunta:</strong> <?php echo htmlspecialchars($item['pregunta']); ?></p>
                <p><strong>Tu respuesta:</strong> <?php echo htmlspecialchars($item['respuesta_usuario'] ?? 'Sin responder'); ?></p>
                <p><strong>Respuesta correcta:</strong> <?php echo htmlspecialchars($item['respuesta_correcta']); ?></p>
                <p><strong>Estado:</strong> <?php echo $item['es_correcta'] ? 'Correcta' : 'Incorrecta'; ?></p>
            </div>
        <?php } ?>
    <?php } ?>

    <p><a href="ver.php?id=<?php echo $examen_id; ?>">Volver al examen</a></p>
    <p><a href="listar.php">Volver al listado</a></p>
</body>
</html>