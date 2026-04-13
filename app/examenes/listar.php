<?php
include '../conexion.php';
include '../helpers.php';

$sql = "SELECT * FROM examenes ORDER BY id ASC";
$resultado = $conexion->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de examenes</title>
</head>
<body>
    <h1>Lista de examenes</h1>

    <p>
        <a href="crear.php">Crear nuevo examen</a> |
        <a href="resultados.php">Ver resultados de examenes</a> |
        <a href="../profesor/panel.php">Volver al panel del profesor</a>
    </p>

    <table border="1" cellpadding="8">
        <tr>
            <th>ID</th>
            <th>Titulo</th>
            <th>Materia</th>
            <th>Fecha de creacion</th>
            <th>Accion</th>
        </tr>
        <?php if ($resultado->num_rows > 0) { ?>
            <?php while ($fila = $resultado->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo (int) $fila['id']; ?></td>
                    <td><?php echo escaparHtml($fila['titulo']); ?></td>
                    <td><?php echo escaparHtml($fila['materia']); ?></td>
                    <td><?php echo escaparHtml($fila['fecha_creacion']); ?></td>
                    <td><a href="ver.php?id=<?php echo (int) $fila['id']; ?>">Ver detalles</a></td>
                </tr>
            <?php } ?>
        <?php } else { ?>
            <tr>
                <td colspan="5">No hay examenes disponibles.</td>
            </tr>
        <?php } ?>
    </table>
</body>
</html>
