<?php
include ('../conexion.php');

if (isset($_GET['id'])) {
    $id = (int) $_GET['id'];
    $sql = "DELETE FROM alumnos WHERE id = $id";

    if ($conexion->query($sql) === TRUE) {
        header("Location: listar.php"); 
        exit();

    } else {
        echo "Error al eliminar el alumno: " . $conexion->error;
    }
} else {
    echo "ID de alumno no especificado";
}
?>