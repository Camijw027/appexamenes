<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>App Examenes</title>
</head>
<body>
    <header>
        <h1>App Examenes</h1>
    </header>
    <main>
        <p>Bienvenido a la aplicacion de examenes.</p>
        <p>Desde aqui puedes administrar alumnos y examenes.</p>

        <ul>
            <li><a href="alumnos/listar.php">Gestionar alumnos</a></li>
            <li><a href="examenes/listar.php">Gestionar examenes</a></li>
        </ul>

        <?php
        include 'conexion.php';
        echo "<p>Conexion exitosa a la base de datos.</p>";
        ?>
    </main>

    <footer>
        <p>&copy; 2026 App Examenes. Todos los derechos reservados.</p>
    </footer>
</body>
</html>
