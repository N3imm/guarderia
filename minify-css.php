<?php
require_once 'vendor/autoload.php';

use MatthiasMullie\Minify;

// Ruta del archivo CSS original
$sourcePath = __DIR__ . '/assets/css/style.css';

// Ruta del archivo CSS minificado de destino
$destinationPath = __DIR__ . '/assets/css/style.min.css';

// Crea una instancia del minificador de CSS
$minifier = new Minify\CSS($sourcePath);

// Guarda el archivo minificado
$minifier->minify($destinationPath);

echo "El archivo style.css ha sido minificado a style.min.css exitosamente.";
?>
