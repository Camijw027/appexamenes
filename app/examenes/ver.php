<?php
include '../conexion.php';
include '../helpers.php';

if (!isset($_GET['id'])) {
    echo 'ID del examen no proporcionado.';
    exit;
}

$examen_id = (int) $_GET['id'];

$stmtExamen = $conexion->prepare("SELECT * FROM examenes WHERE id = ?");
$stmtExamen->bind_param("i", $examen_id);
$stmtExamen->execute();
$resultado_examen = $stmtExamen->get_result();
$examen = $resultado_examen->fetch_assoc();
$stmtExamen->close();

if (!$examen) {
    echo 'Examen no encontrado.';
    exit;
}

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
    <title>Detalles del examen</title>
</head>
<body>
    <h1>Detalles del examen</h1>

    <p><strong>Titulo:</strong> <?php echo escaparHtml($examen['titulo']); ?></p>
    <p><strong>Materia:</strong> <?php echo escaparHtml($examen['materia']); ?></p>
    <p><strong>Fecha de creacion:</strong> <?php echo escaparHtml($examen['fecha_creacion']); ?></p>

    <h2>Preguntas</h2>
    <?php while ($pregunta = $preguntas->fetch_assoc()) { ?>
        <div style="border: 1px solid #ccc; padding: 10px; margin-bottom: 10px;">
            <p><strong>Pregunta:</strong> <?php echo escaparHtml($pregunta['pregunta']); ?></p>
            <p>A. <?php echo escaparHtml($pregunta['opcion_a']); ?></p>
            <p>B. <?php echo escaparHtml($pregunta['opcion_b']); ?></p>
            <p>C. <?php echo escaparHtml($pregunta['opcion_c']); ?></p>
            <p><strong>Respuesta correcta:</strong> <?php echo escaparHtml($pregunta['respuesta_correcta']); ?></p>
        </div>
    <?php } ?>

    <p><a href="listar.php">Volver al listado</a></p>
</body>
</html>
