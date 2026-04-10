<?php
include ('../conexion.php');

if (!isset($_GET['id'])) {
    echo "ID del examen no proporcionado.";
    exit;
}

$id=$_GET['id'];

$sql_examen = "SELECT * FROM examenes WHERE id = $id";
$resultado_examen = $conexion->query($sql_examen);
if ($resultado_examen->num_rows > 0) {
    $examen = $resultado_examen->fetch_assoc();
} else {
    echo "Examen no encontrado.";
    exit;
}

$sql_preguntas = "SELECT * FROM preguntas WHERE examen_id = $id";
$resultado_preguntas = $conexion->query($sql_preguntas);    

?>

<h1>Detalles del Examen</h1>
<p><strong>Título:</strong> <?php echo $examen['titulo']; ?></p>
<p><strong>Materia:</strong> <?php echo $examen['materia']; ?></p>
<p><strong>Fecha de Creación:</strong> <?php echo $examen['fecha_creacion']; ?></p>

<hr>
<h2>Preguntas del Examen</h2>
  <form method="POST" action="calificar.php">
    <input  type="hidden" name="examen_id" value="<?php echo $id; ?>">

    <?php while ($pregunta = $resultado_preguntas->fetch_assoc()) { ?>
        <div  style="border: 1px solid #ccc; padding: 10px; margin-bottom: 10px;">
            <p><strong>Pregunta:</strong> <?php echo $pregunta['pregunta']; ?></p>
            <label><input type="radio" name="respuesta_<?php 
            echo $pregunta['id'];?>" value="A"> <?php 
            echo $pregunta['opcion_a']; ?></label><br>
            <label><input type="radio" name="respuesta_<?php 
            echo $pregunta['id'];?>" value="B"> <?php
            echo $pregunta['opcion_b']; ?></label><br>
            <label><input type="radio" name="respuesta_<?php 
            echo $pregunta['id'];?>" value="C"> <?php
            echo $pregunta['opcion_c']; ?></label><br>
            </div>
    <?php } ?>
    <button type="submit">Calificar Examen</button>
  </form>   
