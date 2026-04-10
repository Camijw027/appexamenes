<?php
include ('../conexion.php');

$sql = "SELECT * FROM examenes";
$resultado = $conexion->query($sql);
?>

<h1>Lista de Exámenes</h1>

<a href="crear.php">Crear Nuevo Examen</a><br><br>

<table border="1">
    <tr>
        <th>ID</th>
        <th>Título</th>
        <th>Materia</th>
        <th>Fecha de Creación</th>
        <th>ver</th>
    </tr>
    <?php
    if ($resultado->num_rows > 0) {
        while($fila = $resultado->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $fila['id'] . "</td>";
            echo "<td>" . $fila['titulo'] . "</td>";
            echo "<td>" . $fila['materia'] . "</td>";
            echo "<td>" . $fila['fecha_creacion'] . "</td>";
            echo "<td><a href='ver.php?id=" . $fila['id'] . "'>Ver Detalles</a></td>";
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='5'>No hay exámenes disponibles.</td></tr>";
    }
    ?>