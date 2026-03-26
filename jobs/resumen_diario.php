<?php

//use Greenter\Model\Summary\SummaryDetail;
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



/*$sql = "select v.id_venta, v.fecha_emision, ds.cod_sunat, ds.abreviatura, v.serie, v.numero, c.documento, c.datos, v.total, v.estado, v.id_tido, v.enviado_sunat, v.estado
        from ventas as v 
            inner join documentos_sunat ds on v.id_tido = ds.id_tido
            inner join clientes c on v.id_cliente = c.id_cliente 
        where v.id_empresa = '{$emp['id_empresa']}' and v.enviado_sunat=0 and v.id_tido = 1";*/
$sql = "select v.id_venta, v.fecha_emision, ds.cod_sunat, ds.abreviatura, v.serie, v.numero, c.documento, c.datos, v.total, v.estado, v.id_tido, v.enviado_sunat, v.estado
        from ventas as v 
            inner join documentos_sunat ds on v.id_tido = ds.id_tido
            inner join clientes c on v.id_cliente = c.id_cliente 
        where v.id_empresa = '{$emp['id_empresa']}' and v.fecha_emision >= '$fecha' and  v.id_tido = 1   and v.enviado_sunat = '0' ";


$resultResu = $conexion->query($sql);

$contar_items = 0;
$array_items = array();

foreach ($resultResu as $fila) {

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

    //estado
    //$estado = $fila['estado'];
    /*if ($fila['estado'] == 1) {
        $estado = "1";
    }
    if ($fila['estado'] == 2) {
        $estado = "3";
    }*/

    $total = $fila['total'];
    $subtotal = $total / 1.18;
    $igv = $total / 1.18 * 0.18;

    echo $fila['cod_sunat'].' / '.$fila['serie'] . "-" . $fila['numero']."\n";

    $item = new SummaryDetail();
    $item->setTipoDoc($fila['cod_sunat'])
        ->setSerieNro($fila['serie'] . "-" . $fila['numero'])
        ->setEstado(1)
        ->setClienteTipo($tipo_doc)
        ->setClienteNro($doc_cliente)
        ->setTotal($total)
        ->setMtoOperGravadas($subtotal)
        ->setMtoOperInafectas(0)
        ->setMtoOperExoneradas(0)
        ->setMtoOtrosCargos(0)
        ->setMtoIGV($igv);

    /*  if ($fila['id_tido'] == 3) {
        //si es nota de credito

        $sql = "select * 
        from ventas_referencias 
        where id_venta = '{$fila['id_venta']}'";
        $referen = $conexion->query($sql)->fetch_assoc();

        $sql ="select * from ventas  where id_venta = '{$referen['id_referencia']}'";

        $c_venta_afecta = $conexion->query($sql)->fetch_assoc();

        //obtener laa serie y el numero y mostrar
        $item->setDocReferencia($c_venta_afecta['serie'] . "-" . $c_venta_afecta['numero']);
    }*/
    $array_items[] = $item;
    $sql="update ventas set enviado_sunat='1' where id_venta='{$fila['id_venta']}'";
    $conexion->query($sql);
}

$sql="select ne.*,c.documento,ds.cod_sunat, concat(v.serie,'-',v.numero) referencia from notas_electronicas ne 
    join ventas v on ne.id_venta = v.id_venta 
    join documentos_sunat ds on ne.tido = ds.id_tido
    join clientes c on v.id_cliente = c.id_cliente
     where v.id_empresa = '{$emp['id_empresa']}' 
       and v.fecha_emision >= '$fecha' 
       and v.id_tido = 1  and ne.estado_sunat='0'";

$notas_electronicas_b = $conexion->query($sql);

foreach ($notas_electronicas_b as $fila){
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

    $total = $fila['monto'];
    $subtotal = $total / 1.18;
    $igv = $total / 1.18 * 0.18;

    echo $fila['cod_sunat'].' / '.$fila['serie'] . "-" . $fila['numero']."\n";

    $item = new SummaryDetail();
    $item->setTipoDoc($fila['cod_sunat'])
        ->setSerieNro($fila['serie'] . "-" . $fila['numero'])
        ->setEstado(1)
        ->setClienteTipo($tipo_doc)
        ->setClienteNro($doc_cliente)
        ->setTotal($total)
        ->setMtoOperGravadas($subtotal)
        ->setMtoOperInafectas(0)
        ->setMtoOperExoneradas(0)
        ->setMtoOtrosCargos(0)
        ->setMtoIGV($igv);

    $item->setDocReferencia((new Document())
        ->setTipoDoc('03')
        ->setNroDoc($fila['referencia']));
    $array_items[] = $item;

    $sql="update notas_electronicas set estado_sunat='1' where nota_id='{$fila['nota_id']}'";
    $conexion->query($sql);
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

//$util->setRucEmpresa($c_empresa->getRuc());

$sum = new Summary();
$sum->setFecGeneracion(\DateTime::createFromFormat('Y-m-d', $fecha))
    ->setFecResumen(\DateTime::createFromFormat('Y-m-d', $fecha))
    ->setCorrelativo('001')
    ->setCompany($empresa)
    ->setDetails($array_items);


$nombre_archivo = $sum->getName();
$nombre_xml =   $nombre_archivo.'.xml' ;

echo "\n".$nombre_archivo."\n";



if ($contar_items > 0) {
    $see = $util->getSee2($endpoint);
    //$see = $util->getSee(SunatEndpoints::FE_PRODUCCION);

    $res = $see->send($sum);
    //$util->writeXml($sum, $see->getFactory()->getLastXml());
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

            //$util->writeCdr($sum, $result->getCdrZip());

            $fileDir =  __DIR__ .'/../files/facturacion/cdr/'.$emp['ruc'];
            if (!file_exists($fileDir)) {
                mkdir($fileDir, 0777, true);
            }

            file_put_contents($fileDir.DIRECTORY_SEPARATOR.'R-'.$nombre_archivo.'.zip',$result->getCdrZip());

            $descripcion = $cdr->getDescription();

            //$util->showResponse($sum, $cdr);
            //   $respuesta = $util->showResponse($sum, $cdr);
            $sql ="insert into resumen_diario set 
              id_empresa='{$emp['id_empresa']}',
              fecha='$fecha',
              ticket='$ticket',
              cantidad_items='$contar_items',
              tipo='1'";
            $conexion->query($sql);

        } else {
            echo "error: ". $util->getErrorResponse($result->getError());
            //  $respuesta = $util->getErrorResponse($result->getError());
            /*$respuesta = array(
                "success" => false,
                "resultado" => "ERROR EN EL TICKET"
            );*/
        }



    }

}
else{
    echo "\n resumen diario '$fecha' empresa {$emp['ruc']} sin item \n <br>";
}

