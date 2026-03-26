<?php

use Greenter\Model\Sale\Invoice;


$util = Util::getInstance();
$util->setRuc($emp['ruc']);
$util->setClave($emp['clave_sol']);
$util->setUsuario($emp['user_sol']);

$sql = "SELECT gr.*,gs.nombre_xml FROM guia_remision gr 
    join guia_sunat gs on gr.id_guia_remision = gs.id_guia 
    where gr.enviado_sunat='0' ";

$see = $util->getSee2($endpointguia);

$listaGuia = $conexion->query($sql);

foreach ($listaGuia as $guia){

    $nombre_archivo = $guia['nombre_xml'];
    $xml_ruta = __DIR__ . "/../files/facturacion/xml/" . $emp["ruc"] . '/' . $nombre_archivo . ".xml";
    $contenido = file_get_contents($xml_ruta);

    $res = $see->sendXml(Invoice::class, $nombre_archivo,$contenido );

    if ($res->isSuccess()) {
        echo "Enviado : $nombre_archivo \n";
        $nombreCDR='R-'.$nombre_archivo.'.zip';
        $cdr = $res->getCdrZip();
        $fileDir =  __DIR__ . '/../files/facturacion/cdr/'.$emp['ruc'];

        if (!file_exists($fileDir)) {
            mkdir($fileDir, 0777, true);
        }

        file_put_contents($fileDir.DIRECTORY_SEPARATOR.$nombreCDR,$cdr);

        $sql="update guia_remision set  enviado_sunat='1' where id_guia_remision='{$guia['id_guia_remision']}' ";
        $conexion->query($sql);

    }else{
        $mensaje = $util->getErrorResponse2($res->getError());
        echo  $mensaje."\n";

    }

}



