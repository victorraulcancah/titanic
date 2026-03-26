<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
date_default_timezone_set('America/Lima');
require './utils/config.php';
require './src/Roots.php';
require './utils/Tools.php';
require_once PATH_APP."http/controllers/Controller.php";
require_once PATH_APP."http/middleware/Middleware.php";
require PATH_SRC . 'autoloader/Autoloader.php';
require_once 'config/Conexion.php';

Autoloader::registrar();

$rutas = scandir(PATH_ROUTES);

foreach ($rutas as $archivo) {
    $rutaArchivo = realpath(PATH_ROUTES . $archivo);
    if (is_file($rutaArchivo)) {
        require $rutaArchivo;
    }
}

Route::submit();
