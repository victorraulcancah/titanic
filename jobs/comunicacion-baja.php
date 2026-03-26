<?php


use Greenter\Model\Company\Address;
use Greenter\Model\Company\Company;
use Greenter\Model\Voided\Voided;
use Greenter\Model\Voided\VoidedDetail;
use Greenter\Ws\Services\SunatEndpoints;

$util = Util::getInstance();
$util->setRuc($emp['ruc']);
$util->setClave($emp['clave_sol']);
$util->setUsuario($emp['user_sol']);


$sql="select v.id_venta, v.fecha_emision, va.fecha as fecha_anulado, ds.cod_sunat, ds.abreviatura, v.serie, v.numero, c.documento, c.datos, v.total, v.estado, v.id_tido, v.enviado_sunat, v.estado
        from ventas_anuladas as va 
            inner join ventas as v on v.id_venta = va.id_venta 
            inner join documentos_sunat ds on v.id_tido = ds.id_tido
            inner join clientes c on v.id_cliente = c.id_cliente 
        where v.id_empresa = '{$emp['id_empresa']}' and v.fecha_emision = '$fecha' and v.id_tido='2' ";

$a_anulados =  $conexion->query($sql);

$contar_items = 0;

$array_items = array();
foreach ($a_anulados as $value) {
    $detail = new VoidedDetail();
    $detail->setTipoDoc('01')
        ->setSerie($value['serie'])
        ->setCorrelativo($value['numero'])
        ->setDesMotivoBaja("ERROR AL BUSCAR PRODUCTOS");
    $array_items[] = $detail;
    $contar_items++;
}

if ($contar_items > 0) {
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

    $voided = new Voided();
    $voided->setCorrelativo($emp['id_empresa']. "1")
        ->setFecComunicacion(\DateTime::createFromFormat('Y-m-d', $fecha))
        ->setFecGeneracion(\DateTime::createFromFormat('Y-m-d', $fecha))
        ->setCompany($empresa)
        ->setDetails($array_items);

    $nombre_archivo = $voided->getName();
    $nombre_xml =   $nombre_archivo.'.xml' ;

    echo "\n".$nombre_archivo."\n";

    $see = $util->getSee2($endpoint);

    $res = $see->send($voided);
    $fileDir =  __DIR__ .'/../files/facturacion/xml/'.$emp['ruc'];
    if (!file_exists($fileDir)) {
        mkdir($fileDir, 0777, true);
    }
    file_put_contents($fileDir.DIRECTORY_SEPARATOR.$nombre_xml,$see->getFactory()->getLastXml());

    $respuesta = "";

    if ($res->isSuccess()) {
        $ticket = $res->getTicket();
        echo 'Ticket :<strong>' . $ticket . '</strong>';
        $result = $see->getStatus($ticket);

        if ($result->isSuccess()) {
            $cdr = $result->getCdrResponse();

            $fileDir =  __DIR__ .'/../files/facturacion/cdr/'.$emp['ruc'];
            if (!file_exists($fileDir)) {
                mkdir($fileDir, 0777, true);
            }
            file_put_contents($fileDir.DIRECTORY_SEPARATOR.'R-'.$nombre_xml.'.zip',$result->getCdrZip());

            $respuesta = $util->showResponse($voided, $cdr);
        } else {
            $respuesta = $util->getErrorResponse($result->getError());
        }

    } else {
        $respuesta = $util->getErrorResponse($res->getError());
    }
} else {
    $respuesta = "no hay registros";
}

echo $respuesta;












