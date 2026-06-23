<?php
/**
 * Carga manualmente las variables del archivo .env a $_ENV y getenv().
 * No usamos una librería externa para mantenerlo simple y educativo.
 */
function cargarEnv(string $rutaArchivo): void
{
    if (!file_exists($rutaArchivo)) {
        throw new Exception(".env no encontrado en: " . $rutaArchivo);
    }

    $lineas = file($rutaArchivo, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lineas as $linea) {
        $linea = trim($linea);

        // Ignorar comentarios
        if (strpos($linea, '#') === 0) {
            continue;
        }

        // Separar CLAVE=VALOR
        if (strpos($linea, '=') !== false) {
            [$clave, $valor] = explode('=', $linea, 2);
            $clave = trim($clave);
            $valor = trim($valor);

            $_ENV[$clave] = $valor;
            putenv("$clave=$valor");
        }
    }
}