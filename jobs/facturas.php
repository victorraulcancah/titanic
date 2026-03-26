<?php

use Greenter\Model\Sale\Invoice;
use Greenter\Ws\Services\SunatEndpoints;
use Greenter\Model\Company\Address;
use Greenter\Model\Company\Company;
use Greenter\Model\Summary\Summary;
use Greenter\Model\Summary\SummaryDetail;

$util = Util::getInstance();
$util->setRuc($emp['ruc']);
$util->setClave($emp['clave_sol']);
$util->setUsuario($emp['user_sol']);

$sql = "SELECT vs.* FROM ventas v join ventas_sunat vs on v.id_venta = vs.id_venta where v.fecha_emision='$fecha' and v.id_empresa = '{$emp['id_empresa']}' and v.enviado_sunat = '0' and v.id_tido=2";
$tempListaFac = $conexion->query($sql);
$see = $util->getSee2($endpoint);
foreach ($tempListaFac as $fac) {
    $nombre_archivo = $fac['nombre_xml'];
    $xml_ruta = __DIR__ . "/../files/facturacion/xml/" . $emp["ruc"] . '/' . $nombre_archivo . ".xml";
    $contenido = file_get_contents($xml_ruta);

    $res = $see->sendXml(Invoice::class, $nombre_archivo, file_get_contents($xml_ruta));
    if ($res->isSuccess()) {
        $nombreCDR = 'R-' . $nombre_archivo . '.zip';
        $cdr = $res->getCdrZip();

        $fileDir = __DIR__ . '/../files/facturacion/cdr/' . $emp['ruc'];
        if (!file_exists($fileDir)) {
            mkdir($fileDir, 0777, true);
        }
        file_put_contents($fileDir . DIRECTORY_SEPARATOR . $nombreCDR, $cdr);
        echo "Exito <br> {$fac['nombre_xml']}\n";
        $sql = "update ventas set enviado_sunat='1' where id_venta='{$fac['id_venta']}'";
        $conexion->query($sql);

    } else {
        $mensaje = $util->getErrorResponse2($res->getError());
        echo "error {$fac['nombre_xml']}: " . $mensaje.'\n <br>';
    }

    sleep(2);

}