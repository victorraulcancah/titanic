<?php
//header("Content-Type: text/plain");
date_default_timezone_set('America/Lima');

//echo __DIR__ ;
require_once __DIR__ ."/../sunat/vendor/autoload.php";

require_once  __DIR__ ."/../utils/config.php";
require_once  __DIR__ ."/../config/Conexion.php";

use Greenter\Ws\Services\SunatEndpoints;

$conexion=(new Conexion())->getConexion();

$sql = "SELECT e.* FROM usuarios u join empresas e on e.id_empresa = u.id_empresa group by e.id_empresa";

$result = $conexion->query($sql);
$fecha = date("Y-m-d");

$endpoint='';
$endpointguia='';


foreach ($result as $emp){
    ob_start();
    if($emp['modo']=='beta'){
        $endpoint=SunatEndpoints::FE_BETA;
    }elseif($emp['modo']=='production'){
        $endpoint=SunatEndpoints::FE_PRODUCCION;
    }

    if($emp['modo']=='beta'){
        $endpointguia=SunatEndpoints::GUIA_BETA;
    }elseif($emp['modo']=='production'){
        $endpointguia=SunatEndpoints::GUIA_PRODUCCION;
    }

    if (file_exists(__DIR__ ."/../files/facturacion/certificados/".$emp['ruc']."-cert.pem")) {


        $id_empresa = $emp['id_empresa'];
        //envio facturas


        if ($emp["ruc"]!=''){

            echo "Enviando facturas......\n<br>";
            require_once __DIR__ . DIRECTORY_SEPARATOR .'facturas.php';

            echo "Enviando resumen diario......<br>\n";

            sleep(2);

            require_once __DIR__ .'/resumen_diario.php';


            echo "Enviando resumen diario anulados......<br>\n";
//
            sleep(2);

            require_once __DIR__ .'/resumen_diario_baja.php';


            echo "Enviando comunicacion baja......<br>\n";

            sleep(2);

            require_once __DIR__ .'/comunicacion-baja.php';

            echo "Enviando Guias Remision......<br>\n";

            sleep(2);

            require_once __DIR__ .'/guias_remision.php';


        }

        sleep(2);

    }else{
        echo "\n<br>Alerta el ".$emp['ruc']." no cuenta con su sertificado digital";
    }
    $output= ob_get_contents();
    ob_end_clean();

    $dirSaveFile=__DIR__ ."/../files/log/sunat/".date("Y_m_d");
    if (!file_exists($dirSaveFile)) {
        mkdir($dirSaveFile, 0777, true);
    }
    file_put_contents($dirSaveFile.DIRECTORY_SEPARATOR.$emp["ruc"].".log",$output);
}
echo "\n<br> termino.....";


