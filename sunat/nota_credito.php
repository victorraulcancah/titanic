<?php

use Greenter\Model\Company\Address;
use Greenter\Model\Company\Company;
use Greenter\Model\Client\Client;
use Greenter\Model\Sale\Legend;
use Greenter\Model\Sale\Note;
use Greenter\Model\Sale\SaleDetail;
use Greenter\Model\Sale\FormaPagos\FormaPagoContado;
use Greenter\Ws\Services\SunatEndpoints;

if(isset($_REQUEST['cliente'])&&is_string($_REQUEST['cliente'])){
    $_REQUEST['cliente']=json_decode($_REQUEST['cliente'],true);
}
if(isset($_REQUEST['empresa'])&&is_string($_REQUEST['empresa'])){
    $_REQUEST['empresa']=json_decode($_REQUEST['empresa'],true);
}
if(isset($_REQUEST['productos'])&&is_string($_REQUEST['productos'])){
    $_REQUEST['productos']=json_decode($_REQUEST['productos'],true);
}

date_default_timezone_set('America/Lima');

require __DIR__ . '/vendor/autoload.php';
require __DIR__."/utils/NumerosaLetras.php";


$data = $_REQUEST;

$endpoint='';
$continuar=false;
if(isset($data["endpoints"])){
    if($data['endpoints']=='beta'){
        $continuar=true;
        $endpoint=SunatEndpoints::FE_BETA;
    }elseif($data['endpoints']=='production'){
        $continuar=true;
        $endpoint=SunatEndpoints::FE_PRODUCCION;
    }
}


if($continuar){
    $util = Util::getInstance();

    $util->setCerGlobal($data['certGlobal']);
    $util->setRuc($data['empresa']["ruc"]);
    $util->setClave($data['empresa']["clave_sol"]);
    $util->setUsuario($data['empresa']["usuario_sol"]);

    $tipo_doc = "";
    $documente_num="";
    if (strlen($data['cliente']['doc_num']) == 8) {
        $tipo_doc = "1";
        $documente_num=$data['cliente']['doc_num'];
    } elseif (strlen($data['cliente']['doc_num']) == 11) {
        $tipo_doc = "6";
        $documente_num=$data['cliente']['doc_num'];
    }else {
        $tipo_doc = "0";
        $documente_num='00000000';
    }

    $nom_rs_c=$data['cliente']["nom_RS"];

    $client = new Client();
    $client->setTipoDoc($tipo_doc)
        ->setNumDoc($documente_num)
        ->setRznSocial($nom_rs_c=='-'?'cliente':utf8_decode($nom_rs_c))
        ->setAddress((new Address())
            ->setDireccion($data['cliente']["direccion"]));

    $empresa = new Company();
    $empresa->setRuc($data['empresa']["ruc"])
        ->setNombreComercial(utf8_decode($data['empresa']["razon_social"]))
        ->setRazonSocial(utf8_decode($data['empresa']["razon_social"]))
        ->setAddress((new Address())
            ->setUbigueo($data['empresa']["ubigeo"])
            ->setDistrito($data['empresa']["distrito"])
            ->setProvincia($data['empresa']["provincia"])
            ->setDepartamento($data['empresa']["departamento"])
            ->setUrbanizacion('-')
            ->setCodLocal('0000')
            ->setDireccion($data['empresa']["direccion"]));


    $montoGravada=number_format($data['total'] / 1.18, 2, ".", "");
    $igv_v=number_format($data['total'] / 1.18 * 0.18, 2, ".", "");
    $total_imp=number_format($data['total'] / 1.18 * 0.18, 2, ".", "");
    $valor_venta=number_format($data['total'] / 1.18, 2, ".", "");
    $impVenta=number_format($data['total'], 2, ".", "");

    $note = new Note();
    $note
        ->setUblVersion('2.1')
        ->setTipoDoc('07') // Tipo Doc: Nota de Credito
        ->setSerie($data['serie']) // Serie NCR
        ->setCorrelativo($data['numero']) // Correlativo NCR
        ->setFechaEmision(new DateTime())
        ->setTipDocAfectado($data['tip_doc_afectado']) // Tipo Doc: Boleta
        ->setNumDocfectado($data['sn_afectado']) // Boleta: Serie-Correlativo
        ->setCodMotivo($data['cod_motivo']) // Catalogo. 09
        ->setDesMotivo($data['des_motivo'])
        ->setTipoMoneda($data['moneda'])
        ->setCompany($empresa)
        ->setClient($client)
        ->setMtoOperGravadas($montoGravada)
        ->setMtoIGV($igv_v)
        ->setTotalImpuestos($total_imp)
        ->setMtoImpVenta($impVenta);

    $array_items = array();


    foreach ($data['productos'] as $value) {

        $preciounitario = $value['precio'];
        $cantidad = $value['cantidad'];
        $preciounitariosinigv = round($preciounitario/ 1.18,2);
        $valorventasinigv = round(($preciounitario * $cantidad) / 1.18,2);
        $igvtotal =  round(($preciounitario * $cantidad) / 1.18 * 0.18,2);

        $item = new SaleDetail();
        $item->setCodProducto($value['cod_pro'])
            ->setCodProdSunat($value['cod_sunat'])
            ->setUnidad('NIU')
            ->setDescripcion($value['descripcion'])
            ->setCantidad($value['cantidad'])
            ->setMtoValorUnitario($preciounitariosinigv)
            ->setMtoValorVenta($valorventasinigv)
            ->setMtoBaseIgv($valorventasinigv)
            ->setPorcentajeIgv(18)
            ->setIgv($igvtotal)
            ->setTipAfeIgv('10')
            ->setTotalImpuestos($igvtotal)
            ->setMtoPrecioUnitario(round($preciounitario,3));

        $array_items []=$item;
    }

    $legend = new Legend();
    $legend->setCode('1000')
        ->setValue('SON DOSCIENTOS TREINTA Y SEIS CON 00/100 SOLES');

    $note->setDetails($array_items)
        ->setLegends([$legend]);


    //fijar variables principales
    $nombre_archivo = $note->getName();
    $nombre_xml =   $note->getName() . ".xml";
    $hash = $util->getHash($note);
    $qr ='';
    /*$qr = $data['empresa']["ruc"] . "|" . "07" . "|"
        . $data['serie'] . "-" . $data['numero'] . "|"
        . $igv . "|" . $total . "|" . $data['fecha'] . "|"
        . $tipo_doc . "|" . $documente_num;*/

    $see = $util->getSee2($endpoint);
    $consten_XML= $see->getXmlSigned($note);
    $res = $see->send($note);
    $error_mensaje='';
    if (!$res->isSuccess()) {
        $error_mensaje= "Error: ".$util->getErrorResponse($res->getError());

    }

    echo json_encode([
        "res"=>true,
        "data"=>[
            "qr"=>$qr,
            "hash"=>$hash,
            "nombre_archivo"=>$nombre_archivo,
            "consten_XML"=>$error_mensaje
        ]
    ]);
}else{
    echo json_encode(["res"=>false,"msg"=>"Endpoints no establecido"]);
}