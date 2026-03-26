<?php

use Greenter\Ws\Services\SunatEndpoints;
use Greenter\Model\Company\Address;
use Greenter\Model\Company\Company;
use Greenter\Model\Summary\Summary;
use Greenter\Model\Sale\Document;
use Greenter\Model\Summary\SummaryDetail;


$util = Util::getInstance();
$util->setRuc($emp['ruc']);
$util->setClave($emp['clave_sol']);
$util->setUsuario($emp['user_sol']);

$sql="select v.id_venta, v.fecha_emision, va.fecha as fecha_anulado, ds.cod_sunat,
       ds.abreviatura, v.serie, v.numero, c.documento, c.datos, v.total,
       v.estado, v.id_tido, v.enviado_sunat, v.estado
        from ventas_anuladas as va 
            inner join ventas as v on v.id_venta = va.id_venta 
            inner join documentos_sunat ds on v.id_tido = ds.id_tido
            inner join clientes c on v.id_cliente = c.id_cliente 
        where v.id_empresa = '{$emp['id_empresa']}'
          and v.fecha_emision = '$fecha' 
          and v.id_tido=1 ";

$resultado_empresa = $conexion->query($sql);

$contar_items = 0;
$array_items = array();

foreach ($resultado_empresa as $fila) {

    $contar_items++;
    //tipo cliente
    $doc_cliente = "00000000";
    if (strlen($fila['documento']) == 8) {
        $tipo_doc = 1;
        $doc_cliente = $fila['documento'];
    } else if (strlen($fila['documento']) == 11) {
        $tipo_doc = 6;
        $doc_cliente = $fila['documento'];
    } else {
        $tipo_doc = 0;
    }

    //totales
    $total = $fila['total'];
    $subtotal = $total / 1.18;
    $igv = $total / 1.18 * 0.18;

    echo $fila['cod_sunat'] . " | " . $fila['serie'] . "-" . $fila['numero'] . PHP_EOL;

    $item = new SummaryDetail();
    $item->setTipoDoc($fila['cod_sunat'])
        ->setSerieNro($fila['serie'] . "-" . $fila['numero'])
        ->setEstado(3)
        ->setClienteTipo($tipo_doc)
        ->setClienteNro($doc_cliente)
        ->setTotal($total)
        ->setMtoOperGravadas($subtotal)
        ->setMtoOperInafectas(0)
        ->setMtoOperExoneradas(0)
        ->setMtoOtrosCargos(0)
        ->setMtoIGV($igv);

    $array_items[] = $item;
}

$empresa = new Company();
$empresa->setRuc($emp['ruc'])
    ->setNombreComercial($emp['razon_social'])
    ->setRazonSocial($emp['razon_social'])
    ->setAddress((new Address())
        ->setUbigueo($emp['ubigeo'])
        ->setDistrito($emp['distrito'])
        ->setProvincia($emp['provincia'])
        ->setDepartamento($emp['departamento'])
        ->setUrbanizacion('-')
        ->setCodLocal('0000')
        ->setDireccion($emp['direccion']));

$sum = new Summary();
$sum->setFecGeneracion(\DateTime::createFromFormat('Y-m-d', $fecha))
    ->setFecResumen(\DateTime::createFromFormat('Y-m-d', $fecha))
    ->setCorrelativo('002')
    ->setCompany($empresa)
    ->setDetails($array_items);

$nombre_archivo = $sum->getName();
$nombre_xml =   $nombre_archivo.'.xml' ;

echo "\n".$nombre_archivo."\n";

if ($contar_items > 0) {
    $see = $util->getSee2($endpoint);

    $res = $see->send($sum);

    $fileDir =  __DIR__ .'/../files/facturacion/xml/'.$emp['ruc'];

    if (!file_exists($fileDir)) {
        mkdir($fileDir, 0777, true);
    }
    file_put_contents($fileDir.DIRECTORY_SEPARATOR.$nombre_xml,$see->getFactory()->getLastXml());

    if ($res->isSuccess()) {
        $ticket = $res->getTicket();
        $descripcion = "";
        $result = $see->getStatus($ticket);

        if ($result->isSuccess()) {
            $cdr = $result->getCdrResponse();

            $fileDir =  __DIR__ .'/../files/facturacion/cdr/'.$emp['ruc'];
            if (!file_exists($fileDir)) {
                mkdir($fileDir, 0777, true);
            }

            file_put_contents($fileDir.DIRECTORY_SEPARATOR.'R-'.$nombre_archivo.'.zip',$result->getCdrZip());

            $descripcion = $cdr->getDescription();
            $sql ="insert into resumen_diario set 
  id_empresa='{$emp['id_empresa']}',
  fecha='$fecha',
  ticket='$ticket',
  cantidad_items='$contar_items',
  tipo='1'";
            $conexion->query($sql);

        }else {
            echo $util->getErrorResponse2($result->getError());
        }

    } else {
        echo $util->getErrorResponse2($res->getError());
    }

}