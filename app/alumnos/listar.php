<?php
include ('../conexion.php');

$sql = "SELECT * FROM alumnos";
$resultado = $conexion->query($sql);       
?>

<h1>Lista de Alumnos</h1>  

<a href="crear.php">Crear Nuevo Alumno</a><br><br>

<table border="1" cellpadding="8">
    <tr>
        <th>ID</th>
        <th>Cédula</th>
        <th>Nombre</th>
        <th>Apellido</th>
        <th>Dirección</th>
        <th>Género</th>
        <th>Fecha de Nacimiento</th>
        <th>Acciones</th>
    </tr>

    <?php
    if ($resultado->num_rows > 0) {
        while($fila = $resultado->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $fila["id"] . "</td>";
            echo "<td>" . $fila["cedula"] . "</td>";
            echo "<td>" . $fila["nombre"] . "</td>";
            echo "<td>" . $fila["apellido"] . "</td>";
            echo "<td>" . $fila["direccion"] . "</td>";
            echo "<td>" . $fila["genero"] . "</td>";
            echo "<td>" . $fila["fecha_nacimiento"] . "</td>";
            echo "<td>
                    <a href='editar.php?id=" . $fila["id"] . "'>Editar</a> | 
                    <a href='eliminar.php?id=" . $fila["id"] . "' onclick=\"return confirm('¿Estás seguro de eliminar este alumno?');\">Eliminar</a>
                  </td>";
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='7'>No se encontraron alumnos</td></tr>";
    }
    ?>  
</table>