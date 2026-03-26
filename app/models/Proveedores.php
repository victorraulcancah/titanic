<?php
require_once __DIR__ . '/models.php';
    class Proveedores extends models
    {
        #private $tabla = 'proveedores';

        function __construct() {
            parent::__construct();
            #parent::__construct($this->tabla);
        }

    }
