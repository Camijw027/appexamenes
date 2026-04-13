<?php
include '../conexion.php';
include '../helpers.php';

$filtroCalificacion = $_GET['calificacion'] ?? '';
$sql = "SELECT r.calificacion, r.descripcion, r.respuestas_correctas, r.fecha_presentacion,
               al.nombre, al.apellido, al.cedula, e.titulo, e.materia
        FROM resultados r
        INNER JOIN asignaciones a ON a.id = r.asignacion_id
        INNER JOIN alumnos al ON al.id = a.alumno_id
        INNER JOIN examenes e ON e.id = a.examen_id";

// Permite al profesor filtrar los resultados por la nota final del examen.
if ($filtroCalificacion !== '') {
    $sql .= " WHERE r.calificacion = " . (int) $filtroCalificacion;
}

$sql .= " ORDER BY r.fecha_presentacion DESC";
$resultado = $conexion->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resultados de examenes</title>
</head>
<body>
    <h1>Resultados de examenes</h1>

    <form method="GET" action="resultados.php">
        <label for="calificacion">Filtrar por nota:</label>
        <select id="calificacion" name="calificacion">
            <option value="">Todas</option>
            <option value="1" <?php echo $filtroCalificacion === '1' ? 'selected' : ''; ?>>1 - Deficiente</option>
            <option value="2" <?php echo $filtroCalificacion === '2' ? 'selected' : ''; ?>>2 - Aceptable</option>
            <option value="3" <?php echo $filtroCalificacion === '3' ? 'selected' : ''; ?>>3 - Excelente</option>
        </select>
        <button type="submit">Filtrar</button>
    </form>

    <p><a href="../profesor/panel.php">Volver al panel del profesor</a></p>

    <table border="1" cellpadding="8">
        <tr>
            <th>Estudiante</th>
            <th>Cedula</th>
            <th>Examen</th>
            <th>Materia</th>
            <th>Correctas</th>
            <th>Nota</th>
            <th>Descripcion</th>
            <th>Fecha</th>
        </tr>
        <?php if ($resultado->num_rows > 0) { ?>
            <?php while ($fila = $resultado->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo escaparHtml($fila['nombre'] . ' ' . $fila['apellido']); ?></td>
                    <td><?php echo escaparHtml($fila['cedula']); ?></td>
                    <td><?php echo escaparHtml($fila['titulo']); ?></td>
                    <td><?php echo escaparHtml($fila['materia']); ?></td>
                    <td><?php echo (int) $fila['respuestas_correctas']; ?></td>
                    <td><?php echo (int) $fila['calificacion']; ?></td>
                    <td><?php echo escaparHtml($fila['descripcion']); ?></td>
                    <td><?php echo escaparHtml($fila['fecha_presentacion']); ?></td>
                </tr>
            <?php } ?>
        <?php } else { ?>
            <tr>
                <td colspan="8">No hay resultados registrados.</td>
            </tr>
        <?php } ?>
    </table>
</body>
</html>
