<?php
// Configuración de errores para desarrollo
// En producción estos deben estar desactivados
error_reporting(E_ALL);
ini_set('display_errors', '0'); // Cambiado a 0 para evitar output antes de JSON
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/error_log.log');
require './src/launcher.php';
