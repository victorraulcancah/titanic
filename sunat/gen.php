<?php

date_default_timezone_set('America/Lima');

require __DIR__ . '/vendor/autoload.php';
require __DIR__."/utils/NumerosaLetras.php";

use Greenter\Model\Client\Client;
use Greenter\Model\Company\Address;
use Greenter\Model\Company\Company;
use Greenter\Model\Sale\Invoice;
use Greenter\Model\Sale\Legend;
use Greenter\Model\Sale\SaleDetail;
use Greenter\Ws\Services\SunatEndpoints;

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

    $tipo_doc = "";
    $documente_num="";
    if (strlen($data['cliente']['doc_num']) == 8) {
        $tipo_doc = "01";
        $documente_num=$data['cliente']['doc_num'];
    } else {
        $tipo_doc = "00";
        $documente_num='00000000';
    }


    $client = new Client();
    $client->setTipoDoc($tipo_doc)
        ->setNumDoc($documente_num)
        ->setRznSocial(utf8_decode($data['cliente']["nom_RS"]));

    $util->setRuc($data['empresa']["ruc"]);
    $util->setClave($data['empresa']["clave_sol"]);
    $util->setUsuario($data['empresa']["usuario_sol"]);

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

    $subtotal = number_format($data['total'] / 1.18, 2, ".", "");
    $igv = number_format($data['total'] / 1.18 * 0.18, 2, ".", "");
    $total = number_format($data['total'], 2, ".", "");

// Venta
    $invoice = new Invoice();
    $invoice
        ->setUblVersion('2.1')
        ->setTipoOperacion('0101')
        ->setTipoDoc('03')
        ->setSerie($data['serie'])
        ->setCorrelativo($data['numero'])
        ->setFechaEmision(\DateTime::createFromFormat('Y-m-d',$data['fecha']))
        ->setTipoMoneda($data['moneda'])
        ->setClient($client)
        ->setMtoOperGravadas(number_format($data['total'] / 1.18, 2, ".", ""))
        ->setMtoIGV(number_format($data['total'] / 1.18 * 0.18, 2, ".", ""))
        ->setTotalImpuestos(number_format($data['total'] / 1.18 * 0.18, 2, ".", ""))
        ->setValorVenta(number_format($data['total'] / 1.18, 2, ".", ""))
        ->setMtoImpVenta(number_format($data['total'], 2, ".", ""))
        ->setCompany($empresa);

    $array_items = array();

    foreach ($data['productos'] as $value) {
        $subtotal_producto = $value['precio'] * $value['cantidad'] / 1.18;
        $igv_producto = $value['precio'] * $value['cantidad'] / 1.18 * 0.18;
        $total_producto = $value['precio'] * $value['cantidad'];
        $item = new SaleDetail();
        $item->setCodProducto($value['cod_pro'])
            ->setCodProdSunat($value['cod_sunat'])
            ->setUnidad('NIU')
            ->setDescripcion($value['descripcion'])
            ->setCantidad($value['cantidad'])
            ->setMtoValorUnitario(number_format($value['precio'] / 1.18, 2, '.', ''))
            ->setMtoValorVenta(number_format($subtotal_producto, 2, '.', ''))
            ->setMtoBaseIgv(number_format($subtotal_producto, 2, '.', ''))
            ->setPorcentajeIgv(18)
            ->setIgv(number_format($igv_producto, 2, '.', ''))
            ->setTipAfeIgv('10')
            ->setTotalImpuestos(number_format($igv_producto, 2, '.', ''))
            ->setMtoPrecioUnitario(number_format($value['precio'], 2, '.', ''));
        $array_items[] = $item;
    }

    $invoice->setDetails($array_items);
    $c_numeros = new NumerosaLetras();
    $numeros = utf8_decode($c_numeros->to_word(number_format($data['total'], 2, ".", ""), "PEN"));
    $invoice->setLegends([
        (new Legend())
            ->setCode('1000')
            ->setValue($numeros)
    ]);

//fijar variables principales
    $nombre_archivo = $invoice->getName();
    $nombre_xml =   $invoice->getName() . ".xml";
    $hash = $util->getHash($invoice);

    $qr = $data['empresa']["ruc"] . "|" . "03" . "|"
        . $data['serie'] . "-" . $data['numero'] . "|"
        . $igv . "|" . $total . "|" . $data['fecha'] . "|"
        . $tipo_doc . "|" . $documente_num;


    $see = $util->getSee2($endpoint);
    $consten_XML= $see->getXmlSigned($invoice);


    echo json_encode([
        "res"=>true,
        "data"=>[
            "qr"=>$qr,
            "hash"=>$hash,
            "nombre_archivo"=>$nombre_archivo,
            "consten_XML"=>$consten_XML
        ]
    ]);

}else{
    echo json_encode(["res"=>false,"msg"=>"Endpoints no establecido"]);
}
