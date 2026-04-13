<?php
function escaparHtml($valor)
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}

function obtenerEscala($correctas)
{
    // Convierte la cantidad de respuestas correctas a la escala solicitada.
    if ($correctas <= 1) {
        return [1, 'Deficiente'];
    }

    if ($correctas === 2) {
        return [2, 'Aceptable'];
    }

    return [3, 'Excelente'];
}
?>
