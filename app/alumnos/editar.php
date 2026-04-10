<?php
include ('../conexion.php');

if (isset($_GET['id'])) {
    $id = (int) $_GET['id'];
} else {
    echo "ID de alumno no especificado";
    exit();
}
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $cedula = $_POST['cedula'];
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $direccion = $_POST['direccion'];
    $genero = $_POST['genero'];
    $fecha_nacimiento = $_POST['fecha_nacimiento'];

    $sql_update = "UPDATE alumnos SET 

    cedula='$cedula', 
    nombre='$nombre', 
    apellido='$apellido', 
    direccion='$direccion', 
    genero='$genero', 
    fecha_nacimiento='$fecha_nacimiento' 
    WHERE id=$id";

    if ($conexion->query($sql_update) === TRUE) {
        header("Location: listar.php"); 
        exit(); 
    } else {
        echo "Error: " . $sql_update . "<br>" . $conexion->error;
    }
}
$sql = "SELECT * FROM alumnos WHERE id = $id";
$resultado = $conexion->query($sql);
$alumno = $resultado->fetch_assoc();

?>

<h1>Editar Alumno</h1>

<form method="POST" action="editar.php?id=<?php echo $alumno['id']; ?>">

    cedula:  <input type="text" name="cedula" value="<?php echo $alumno['cedula']; ?>" required><br><br>

    nombre: <input type="text" name="nombre" value="<?php echo $alumno['nombre']; ?>" required><br><br>

    apellido: <input type="text" name="apellido" value="<?php echo $alumno['apellido']; ?>" required><br><br>

    direccion: <input type="text" name="direccion" value="<?php echo $alumno['direccion']; ?>"><br><br>

    genero: <select name="genero">
        <option value="">Seleccione</option>
        <option value="M" <?php if ($alumno['genero'] == 'M') echo 'selected'; ?>>Masculino</option>
        <option value="F" <?php if ($alumno['genero'] == 'F') echo 'selected'; ?>>Femenino</option>
    </select><br><br>

    fecha_nacimiento: <input type="date" name="fecha_nacimiento" value="<?php echo $alumno['fecha_nacimiento']; ?>"><br>

    <button type="submit">Actualizar</button>
</form>