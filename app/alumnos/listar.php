<?php
include '../conexion.php';
include '../helpers.php';

$sql = "SELECT * FROM alumnos ORDER BY id ASC";
$resultado = $conexion->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de alumnos</title>
</head>
<body>
    <h1>Lista de alumnos</h1>

    <p>
        <a href="crear.php">Crear nuevo alumno</a> |
        <a href="../profesor/panel.php">Volver al panel del profesor</a>
    </p>

    <table border="1" cellpadding="8">
        <tr>
            <th>ID</th>
            <th>Cedula</th>
            <th>Nombre</th>
            <th>Apellido</th>
            <th>Direccion</th>
            <th>Genero</th>
            <th>Fecha de nacimiento</th>
            <th>Acciones</th>
        </tr>

        <?php if ($resultado->num_rows > 0) { ?>
            <?php while ($fila = $resultado->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo (int) $fila['id']; ?></td>
                    <td><?php echo escaparHtml($fila['cedula']); ?></td>
                    <td><?php echo escaparHtml($fila['nombre']); ?></td>
                    <td><?php echo escaparHtml($fila['apellido']); ?></td>
                    <td><?php echo escaparHtml($fila['direccion']); ?></td>
                    <td><?php echo escaparHtml($fila['genero']); ?></td>
                    <td><?php echo escaparHtml($fila['fecha_nacimiento']); ?></td>
                    <td>
                        <a href="editar.php?id=<?php echo (int) $fila['id']; ?>">Editar</a> |
                        <a href="asignar.php?id=<?php echo (int) $fila['id']; ?>">Asignar examen</a> |
                        <a href="eliminar.php?id=<?php echo (int) $fila['id']; ?>" onclick="return confirm('Estas seguro de eliminar este alumno?');">Eliminar</a>
                    </td>
                </tr>
            <?php } ?>
        <?php } else { ?>
            <tr>
                <td colspan="8">No se encontraron alumnos.</td>
            </tr>
        <?php } ?>
    </table>
</body>
</html>
