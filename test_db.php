<?php
require 'utils/config.php';
require 'config/Conexion.php';

$con = new Conexion();
$db = $con->getConexion();

if ($db->connect_error) {
    echo "Connection failed: " . $db->connect_error;
} else {
    echo "Connected successfully";
}
