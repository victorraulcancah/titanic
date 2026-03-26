<?php
        define('DB_HOST','localhost');
	define('DB_USER','root');
	define('DB_PASS','c4p1cu4%');
	define('DB_NAME','factura_acosta');
	# conectare la base de datos
    $con=@mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if(!$con){
        die("imposible conectarse: ".mysqli_error($con));
    }
    if (@mysqli_connect_errno()) {
        die("Conexin fall: ".mysqli_connect_errno()." : ". mysqli_connect_error());
    }

   mysqli_query($con,"SET CHARACTER SET 'utf8'");
mysqli_query($con,"SET SESSION collation_connection ='utf8_unicode_ci'");

?>
