<?php
include ('../conexion.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $titulo = $_POST['titulo'];
    $materia = $_POST['materia'];
    $fecha_creacion = $_POST['fecha_creacion'];

    if (empty($titulo) || empty($materia) || empty($fecha_creacion)) {
        echo "Todos los campos son obligatorios.";
        exit;
    }

    $sql = "INSERT INTO examenes (titulo, materia, fecha_creacion) VALUES ('$titulo', '$materia', '$fecha_creacion')";
    
    if ($conexion->query($sql) === TRUE) {
        echo "Examen creado exitosamente.";
    } else {
        echo "Error: " . $sql . "<br>" . $conexion->error;
    }
}
?>

<h1>Crear Examen</h1>
<form method="POST" action="crear.php">
    <label for="titulo">Título:</label><br>
    <input type="text" id="titulo" name="titulo"><br><br>

    <label for="materia">Materia:</label><br>
    <input type="text" id="materia" name="materia"><br><br>

    <label for="fecha_creacion">Fecha de Creación:</label><br>
    <input type="date" id="fecha_creacion" name="fecha_creacion"><br><br>

    <input type="submit" value="Crear Examen">
</form>
