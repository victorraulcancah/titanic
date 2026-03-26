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

if(isset($_REQUEST['venta'])&&is_string($_REQUEST['venta'])){
    $_REQUEST['venta']=json_decode($_REQUEST['venta'],true);
}
if(isset($_REQUEST['transporte'])&&is_string($_REQUEST['transporte'])){
    $_REQUEST['transporte']=json_decode($_REQUEST['transporte'],true);
}

date_default_timezone_set('America/Lima');

use Greenter\Model\Client\Client;
use Greenter\Model\Despatch\Despatch;
use Greenter\Model\Despatch\DespatchDetail;
use Greenter\Model\Despatch\Direction;
use Greenter\Model\Despatch\Shipment;
use Greenter\Model\Despatch\Transportist;
use Greenter\Model\Sale\Document;
use Greenter\Model\Company\Company;
use Greenter\Model\Company\Address;
use Greenter\Ws\Services\SunatEndpoints;

require __DIR__ . '/vendor/autoload.php';
    
    $endpoint='';
    $continuar=false;
    if(isset($_REQUEST["endpoints"])){
        if($_REQUEST['endpoints']=='beta'){
            $continuar=true;
            $endpoint=SunatEndpoints::GUIA_BETA;
        }elseif($_REQUEST['endpoints']=='production'){
            $continuar=true;
            $endpoint=SunatEndpoints::GUIA_PRODUCCION;
        }
    }

    if($continuar){
        $util = Util::getInstance();
        $util->setCerGlobal($_REQUEST['certGlobal']);

        $util->setRuc($_REQUEST['empresa']["ruc"]);
        $util->setClave($_REQUEST['empresa']["clave_sol"]);
        $util->setUsuario($_REQUEST['empresa']["usuario_sol"]);


        $documento=$_REQUEST['cliente']['doc_num'];
        $datosCli=$_REQUEST['cliente']['nom_RS'];
        $tipo_doc = '';

        if (strlen($documento) == 8) {
            $tipo_doc = "1";
        } elseif (strlen($documento) == 11) {
            $tipo_doc = "6";
        }else {
            $tipo_doc = "0";
            $documento='00000000';
        }

        $client = new Client();
        $client->setTipoDoc($tipo_doc)
            ->setNumDoc($documento)
            ->setRznSocial($datosCli);

        $empresa = new Company();
        $empresa->setRuc($_REQUEST['empresa']['ruc'])
            ->setNombreComercial($_REQUEST['empresa']['razon_social'])
            ->setRazonSocial($_REQUEST['empresa']['razon_social'])
            ->setAddress((new Address())
                ->setUbigueo($_REQUEST['empresa']["ubigeo"])
                ->setDistrito($_REQUEST['empresa']["distrito"])
                ->setProvincia($_REQUEST['empresa']["provincia"])
                ->setDepartamento($_REQUEST['empresa']["departamento"])
                ->setUrbanizacion('-')
                ->setCodLocal('0000')
                ->setDireccion($_REQUEST['empresa']["direccion"]));


        $array_items = array();
        $sumar_cantidades = 0;

        $items=$_REQUEST['productos'];

        foreach ($items as $value) {
            $detail = new DespatchDetail();
            $detail->setCantidad($value['cantidad'])
                ->setUnidad('NIU')
                ->setDescripcion($value['descripcion'] )
                ->setCodigo($value['cod_pro']);
                //->setCodProdSunat($value['id_producto']);

            $sumar_cantidades += $value['cantidad'];

            $array_items[] = $detail;
        }

        $transp = new Transportist();
        $transp->setTipoDoc('6')
            ->setNumDoc($_REQUEST['transporte']['ruc'])
            ->setRznSocial($_REQUEST['transporte']['razon_social'])
            ->setPlaca($_REQUEST['transporte']['placa'])
            ->setChoferTipoDoc('1')
            ->setChoferDoc($_REQUEST['transporte']['doc_chofer']);

        $envio = new Shipment();
        $envio->setModTraslado('01')
            ->setCodTraslado('01')
            ->setDesTraslado('VENTA')
            ->setFecTraslado(new \DateTime())
            ->setIndTransbordo(false)
            ->setPesoTotal($_REQUEST['peso'])
            ->setUndPesoTotal('KGM')
            ->setNumBultos($sumar_cantidades)
            ->setLlegada(new Direction($_REQUEST['ubigeo'], $_REQUEST['direccion']))
            ->setPartida(new Direction($_REQUEST['empresa']["ubigeo"],$_REQUEST['empresa']["direccion"]))
            ->setTransportista($transp);

        $despatch = new Despatch();
        $despatch->setTipoDoc('09')
            ->setSerie($_REQUEST['serie'])
            ->setCorrelativo($_REQUEST['numero'])
            ->setFechaEmision(new \DateTime())
            ->setCompany($empresa)
            ->setDestinatario($client)
            //->setTercero($client)
            ->setObservacion('FT: ' . $_REQUEST['venta']['serie'] . "-" . $_REQUEST['venta']['numero'])
            ->setEnvio($envio);
        
        $despatch->setDetails($array_items);

        $nombre_archivo = $despatch->getName();
        // Envio a SUNAT.
        $see = $util->getSee2($endpoint);
        $consten_XML= $see->getXmlSigned($despatch);
        $hash = $util->getHash($despatch);

        $qr = $_REQUEST['empresa']["ruc"] . "|" . "09" . "|" . 
        $_REQUEST['serie'] . "-" . $_REQUEST['numero'] .
        "|0.00|0.00|" . $_REQUEST['fecha'] . "|" . $tipo_doc . "|" . 
        $_REQUEST['cliente']['doc_num'];
        
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
    