<?php


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

use Greenter\Model\Client\Client;
use Greenter\Model\Sale\FormaPagos\FormaPagoContado;
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

/*echo "ssssss";
die();*/

if($continuar){

    $util = Util::getInstance();

    $util->setCerGlobal($data['certGlobal']);

    $tipo_doc = "";
    $documente_num="";
    if (strlen($data['cliente']['doc_num']) == 8) {
        $tipo_doc = "1";
        $documente_num=$data['cliente']['doc_num'];
    } else {
        $tipo_doc = "0";
        $documente_num='00000000';
    }

    $nom_rs_c=$data['cliente']["nom_RS"];

    $client = new Client();
    $client->setTipoDoc($tipo_doc)
        ->setNumDoc($documente_num)
        ->setRznSocial($nom_rs_c=='-'?'cliente':utf8_decode($nom_rs_c));

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

    $montoGravada=number_format($data['total'] / 1.18, 2, ".", "");
    $igv_v=number_format($data['total'] / 1.18 * 0.18, 2, ".", "");
    $total_imp=number_format($data['total'] / 1.18 * 0.18, 2, ".", "");
    $valor_venta=number_format($data['total'] / 1.18, 2, ".", "");
    $impVenta=number_format($data['total'], 2, ".", "");


    $invoice
        ->setUblVersion('2.1')
        ->setFecVencimiento(new DateTime())
        ->setTipoOperacion('0101')
        ->setTipoDoc('03')
        ->setSerie($data['serie'])
        ->setFormaPago(new FormaPagoContado())
        ->setCorrelativo($data['numero'])
        ->setFechaEmision(\DateTime::createFromFormat('Y-m-d',$data['fecha']))
        ->setTipoMoneda($data['moneda'])
        ->setClient($client)
        ->setMtoOperGravadas($montoGravada)
        ->setMtoIGV($igv_v)
        ->setTotalImpuestos($total_imp)
        ->setValorVenta($valor_venta)
        ->setMtoImpVenta($impVenta)
        ->setSubTotal($impVenta)
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