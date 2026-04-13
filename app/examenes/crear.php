<?php
include '../conexion.php';
include '../helpers.php';

$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = trim($_POST['titulo'] ?? '');
    $materia = trim($_POST['materia'] ?? '');
    $fecha_creacion = $_POST['fecha_creacion'] ?? '';

    $preguntas = [];
    // Recolecta las tres preguntas junto con sus opciones y respuesta correcta.
    for ($i = 1; $i <= 3; $i++) {
        $preguntas[] = [
            'pregunta' => trim($_POST['pregunta_' . $i] ?? ''),
            'opcion_a' => trim($_POST['opcion_a_' . $i] ?? ''),
            'opcion_b' => trim($_POST['opcion_b_' . $i] ?? ''),
            'opcion_c' => trim($_POST['opcion_c_' . $i] ?? ''),
            'respuesta_correcta' => $_POST['respuesta_correcta_' . $i] ?? '',
        ];
    }

    $datosValidos = $titulo !== '' && $materia !== '' && $fecha_creacion !== '';
    foreach ($preguntas as $pregunta) {
        if (
            $pregunta['pregunta'] === '' ||
            $pregunta['opcion_a'] === '' ||
            $pregunta['opcion_b'] === '' ||
            $pregunta['opcion_c'] === '' ||
            $pregunta['respuesta_correcta'] === ''
        ) {
            $datosValidos = false;
            break;
        }
    }

    if (!$datosValidos) {
        $mensaje = 'Debes completar los datos del examen y sus 3 preguntas.';
    } else {
        // Guarda el examen y sus preguntas como una sola operacion para evitar datos incompletos.
        $conexion->begin_transaction();

        try {
            $stmtCrearExamen = $conexion->prepare("INSERT INTO examenes (titulo, materia, fecha_creacion) VALUES (?, ?, ?)");
            $stmtCrearExamen->bind_param("sss", $titulo, $materia, $fecha_creacion);
            $stmtCrearExamen->execute();
            $examen_id = $conexion->insert_id;
            $stmtCrearExamen->close();

            $stmtCrearPregunta = $conexion->prepare(
                "INSERT INTO preguntas (examen_id, pregunta, opcion_a, opcion_b, opcion_c, respuesta_correcta)
                 VALUES (?, ?, ?, ?, ?, ?)"
            );

            foreach ($preguntas as $pregunta) {
                $stmtCrearPregunta->bind_param(
                    "isssss",
                    $examen_id,
                    $pregunta['pregunta'],
                    $pregunta['opcion_a'],
                    $pregunta['opcion_b'],
                    $pregunta['opcion_c'],
                    $pregunta['respuesta_correcta']
                );
                $stmtCrearPregunta->execute();
            }

            $stmtCrearPregunta->close();
            $conexion->commit();
            header('Location: listar.php');
            exit;
        } catch (Throwable $e) {
            $conexion->rollback();
            $mensaje = 'No fue posible crear el examen.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear examen</title>
</head>
<body>
    <h1>Crear examen</h1>

    <?php if ($mensaje !== '') { ?>
        <p><?php echo escaparHtml($mensaje); ?></p>
    <?php } ?>

    <form method="POST" action="crear.php">
        <label for="titulo">Titulo:</label><br>
        <input type="text" id="titulo" name="titulo" required><br><br>

        <label for="materia">Materia:</label><br>
        <input type="text" id="materia" name="materia" required><br><br>

        <label for="fecha_creacion">Fecha de creacion:</label><br>
        <input type="date" id="fecha_creacion" name="fecha_creacion" required><br><br>

        <?php for ($i = 1; $i <= 3; $i++) { ?>
            <fieldset style="margin-bottom: 20px;">
                <legend>Pregunta <?php echo $i; ?></legend>

                <label for="pregunta_<?php echo $i; ?>">Pregunta:</label><br>
                <input type="text" id="pregunta_<?php echo $i; ?>" name="pregunta_<?php echo $i; ?>" required><br><br>

                <label for="opcion_a_<?php echo $i; ?>">Opcion A:</label><br>
                <input type="text" id="opcion_a_<?php echo $i; ?>" name="opcion_a_<?php echo $i; ?>" required><br><br>

                <label for="opcion_b_<?php echo $i; ?>">Opcion B:</label><br>
                <input type="text" id="opcion_b_<?php echo $i; ?>" name="opcion_b_<?php echo $i; ?>" required><br><br>

                <label for="opcion_c_<?php echo $i; ?>">Opcion C:</label><br>
                <input type="text" id="opcion_c_<?php echo $i; ?>" name="opcion_c_<?php echo $i; ?>" required><br><br>

                <label for="respuesta_correcta_<?php echo $i; ?>">Respuesta correcta:</label><br>
                <select id="respuesta_correcta_<?php echo $i; ?>" name="respuesta_correcta_<?php echo $i; ?>" required>
                    <option value="">Seleccione</option>
                    <option value="A">A</option>
                    <option value="B">B</option>
                    <option value="C">C</option>
                </select>
            </fieldset>
        <?php } ?>

        <button type="submit">Guardar examen</button>
    </form>

    <p><a href="listar.php">Volver al listado</a></p>
</body>
</html>
