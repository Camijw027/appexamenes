<?php
include ('../conexion.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $cedula = $_POST['cedula'];
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $direccion = $_POST['direccion'];
    $genero = $_POST['genero'];
    $fecha_nacimiento = $_POST['fecha_nacimiento'];

    $sql = "INSERT INTO alumnos (cedula, nombre, apellido, direccion, genero, fecha_nacimiento)
     VALUES ('$cedula', '$nombre', '$apellido', '$direccion', '$genero', '$fecha_nacimiento')";

    if ($conexion->query($sql) === TRUE) {
        echo "Alumno creado exitosamente";
    } else {
        echo "Error: " . $sql . "<br>" . $conexion->error;
    }
}
?>

<h1>Crear Alumno</h1>
<form method="POST" action="crear.php">

    cedula:  <input type="text" name="cedula" required><br>

    nombre: <input type="text" name="nombre" required><br> 

    apellido: <input type="text" name="apellido" required><br>

    direccion: <input type="text" name="direccion"><br>

    genero: <select name="genero">
        <option value="">Seleccione</option>
        <option value="M">Masculino</option>
        <option value="F">Femenino</option>
    </select><br>

    fecha_nacimiento: <input type="date" name="fecha_nacimiento"><br>

    <button type="submit">Guardar</button>
    
</form>