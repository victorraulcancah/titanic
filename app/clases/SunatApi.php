<?php

date_default_timezone_set('America/Lima');

require_once "sunat/vendor/autoload.php";
require_once "sunat/utils/NumerosaLetras.php";



use Greenter\Model\Client\Client;
use Greenter\Model\Sale\FormaPagos\FormaPagoContado;
use Greenter\Model\Company\Address;
use Greenter\Model\Company\Company;
use Greenter\Model\Sale\Invoice;
use Greenter\Model\Sale\Legend;
use Greenter\Model\Sale\Cuota;
use Greenter\Model\Sale\SaleDetail;
use Greenter\Ws\Services\SunatEndpoints;
use Greenter\Model\Despatch\Despatch;
use Greenter\Model\Despatch\DespatchDetail;
use Greenter\Model\Despatch\Direction;
use Greenter\Model\Despatch\Shipment;
use Greenter\Model\Despatch\Transportist;
use Greenter\Model\Sale\Document;
use Greenter\Model\Sale\Note;
use Greenter\Model\Sale\FormaPagos\FormaPagoCredito;
use Greenter\Model\Summary\SummaryDetail;
use Greenter\Model\Summary\Summary;
use Greenter\Model\Voided\Voided;
use Greenter\Model\Voided\VoidedDetail;
use Greenter\Zip\ZipFly;


class SunatApi
{

    private $mensaje;

    public function __construct(){

    }

    public function comunicacionBajaPorEmpresa($listaFac,$empresa,$fechaComuni,$fechaGene,$correlativo){
        $conexion=(new Conexion())->getConexion();

        $emp = $conexion->query("select * from  empresas where id_empresa='$empresa'")->fetch_assoc();

        $endpoint='';

        if($emp['modo']=='beta'){
            $endpoint=SunatEndpoints::FE_BETA;
        }elseif($emp['modo']=='production'){
            $endpoint=SunatEndpoints::FE_PRODUCCION;
        }


        $sql="select v.id_venta, v.fecha_emision, va.fecha as fecha_anulado, ds.cod_sunat,
       ds.abreviatura, v.serie, v.numero, c.documento, c.datos, v.total, v.estado, v.id_tido, v.enviado_sunat, v.estado
        from ventas_anuladas as va 
            inner join ventas as v on v.id_venta = va.id_venta 
            inner join documentos_sunat ds on v.id_tido = ds.id_tido
            inner join clientes c on v.id_cliente = c.id_cliente 
        where  ".implode(" OR ",$listaFac);

        $a_anulados =  $conexion->query($sql);

        $util = Util::getInstance();
        $util->setRuc($emp['ruc']);
        $util->setClave($emp['clave_sol']);
        $util->setUsuario($emp['user_sol']);

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

        $respuesta = "Sin item";

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
            $voided->setCorrelativo($emp['id_empresa']. $correlativo)
                ->setFecComunicacion(\DateTime::createFromFormat('Y-m-d', $fechaComuni))
                ->setFecGeneracion(\DateTime::createFromFormat('Y-m-d', $fechaGene))
                ->setCompany($empresa)
                ->setDetails($array_items);

            $nombre_archivo = $voided->getName();
            $nombre_xml =   $nombre_archivo.'.xml' ;



            $see = $util->getSee2($endpoint);

            $res = $see->send($voided);
            $fileDir =  'files/facturacion/xml/'.$emp['ruc'];
            if (!file_exists($fileDir)) {
                mkdir($fileDir, 0777, true);
            }
            file_put_contents($fileDir.DIRECTORY_SEPARATOR.$nombre_xml,$see->getFactory()->getLastXml());

            $respuesta = "";

            if ($res->isSuccess()) {
                $ticket = $res->getTicket();
                //echo 'Ticket :<strong>' . $ticket . '</strong>';
                $result = $see->getStatus($ticket);

                if ($result->isSuccess()) {
                    $cdr = $result->getCdrResponse();

                    $fileDir =  'files/facturacion/cdr/'.$emp['ruc'];
                    if (!file_exists($fileDir)) {
                        mkdir($fileDir, 0777, true);
                    }
                    file_put_contents($fileDir.DIRECTORY_SEPARATOR.'R-'.$nombre_archivo.'.zip',$result->getCdrZip());

                    $respuesta = $util->showResponse($voided, $cdr);
                } else {
                    $respuesta = $util->getErrorResponse2($result->getError());
                }

            } else {
                $respuesta = $util->getErrorResponse2($res->getError());
            }

        }
        return  $respuesta;
    }

    public function resumenDiarioPorEmpresa($ventas,$empresa,$fechaGene,$fechaResu,$correlativo){

        $resultado =["res"=>false,"msg"=>''];

        $conexion=(new Conexion())->getConexion();

        $emp = $conexion->query("select * from empresas where id_empresa='$empresa'")->fetch_assoc();

        $sql = "select v.id_venta, v.fecha_emision, ds.cod_sunat, ds.abreviatura,
       v.serie, v.numero, c.documento, c.datos, v.total, v.estado, v.id_tido, 
       v.enviado_sunat, v.estado
        from ventas as v 
            inner join documentos_sunat ds on v.id_tido = ds.id_tido
            inner join clientes c on v.id_cliente = c.id_cliente 
        where   ".implode(" OR ",$ventas);

        $resultResu = $conexion->query($sql);

        $contar_items = 0;
        $array_items = array();

        $endpoint='';

        if($emp['modo']=='beta'){
            $endpoint=SunatEndpoints::FE_BETA;
        }elseif($emp['modo']=='production'){
            $endpoint=SunatEndpoints::FE_PRODUCCION;
        }

        $util = Util::getInstance();
        $util->setRuc($emp['ruc']);
        $util->setClave($emp['clave_sol']);
        $util->setUsuario($emp['user_sol']);

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

            $total = $fila['total'];
            $subtotal = $total / 1.18;
            $igv = $total / 1.18 * 0.18;

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

            $array_items[] = $item;
            $sql="update ventas set enviado_sunat='1' where id_venta='{$fila['id_venta']}'";
            $conexion->query($sql);
        }
        $ompany = new Company();
        $ompany->setRuc($emp['ruc'])
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
        $sum->setFecGeneracion(\DateTime::createFromFormat('Y-m-d', $fechaGene))
            ->setFecResumen(\DateTime::createFromFormat('Y-m-d', $fechaResu))
            ->setCorrelativo($correlativo)
            ->setCompany($ompany)
            ->setDetails($array_items);

        $nombre_archivo = $sum->getName();
        $nombre_xml =   $nombre_archivo.'.xml' ;

        if ($contar_items > 0) {

            $see = $util->getSee2($endpoint);
            $res = $see->send($sum);

            $fileDir = 'files/facturacion/xml/'.$emp['ruc'];
            if (!file_exists($fileDir)) {
                mkdir($fileDir, 0777, true);
            }
            file_put_contents($fileDir.DIRECTORY_SEPARATOR.$nombre_xml,$see->getFactory()->getLastXml());

            if ($res->isSuccess()) {
                $ticket = $res->getTicket();
                $descripcion = "";
                $result = $see->getStatus($ticket);

                if ($result->isSuccess()) {
                    $resultado["res"]=true;
                    $cdr = $result->getCdrResponse();

                    $fileDir =  'files/facturacion/cdr/'.$emp['ruc'];
                    if (!file_exists($fileDir)) {
                        mkdir($fileDir, 0777, true);
                    }
                    file_put_contents($fileDir.DIRECTORY_SEPARATOR.'R-'.$nombre_archivo.'.zip',$result->getCdrZip());

                    $descripcion = $cdr->getDescription();

                    $fecha = date("Y-m-d");
                    $sql ="insert into resumen_diario set 
                      id_empresa='{$emp['id_empresa']}',
                      fecha='$fecha',
                      ticket='$ticket',
                      cantidad_items='$contar_items',
                      tipo='1'";
                    $conexion->query($sql);

                }else{
                    $resultado["msg"]="error: ". $util->getErrorResponse($result->getError());
                }

            }

        }
        return $resultado;
    }
    public function resumenDiarioBajaPorEmpresa($ventas,$empresa,$fechaGene,$fechaResu,$correlativo){

        $resultado =["res"=>false,"msg"=>''];

        $conexion=(new Conexion())->getConexion();

        $emp = $conexion->query("select * from empresas where id_empresa='$empresa'")->fetch_assoc();

        $sql = "select v.id_venta, v.fecha_emision, va.fecha as fecha_anulado, ds.cod_sunat,
       ds.abreviatura, v.serie, v.numero, c.documento, c.datos, v.total,
       v.estado, v.id_tido, v.enviado_sunat, v.estado
        from ventas_anuladas as va 
            inner join ventas as v on v.id_venta = va.id_venta 
            inner join documentos_sunat ds on v.id_tido = ds.id_tido
            inner join clientes c on v.id_cliente = c.id_cliente 
        where  ".implode(" OR ",$ventas);

        $resultResu = $conexion->query($sql);

        $contar_items = 0;
        $array_items = array();

        $endpoint='';

        if($emp['modo']=='beta'){
            $endpoint=SunatEndpoints::FE_BETA;
        }elseif($emp['modo']=='production'){
            $endpoint=SunatEndpoints::FE_PRODUCCION;
        }

        $util = Util::getInstance();
        $util->setRuc($emp['ruc']);
        $util->setClave($emp['clave_sol']);
        $util->setUsuario($emp['user_sol']);

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

            $total = $fila['total'];
            $subtotal = $total / 1.18;
            $igv = $total / 1.18 * 0.18;

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
            $sql="update ventas set enviado_sunat='1' where id_venta='{$fila['id_venta']}'";
            $conexion->query($sql);
        }
        $company = new Company();
        $company->setRuc($emp['ruc'])
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
        $sum->setFecGeneracion(\DateTime::createFromFormat('Y-m-d', $fechaGene))
            ->setFecResumen(\DateTime::createFromFormat('Y-m-d', $fechaResu))
            ->setCorrelativo($correlativo)
            ->setCompany($company)
            ->setDetails($array_items);

        $nombre_archivo = $sum->getName();
        $nombre_xml =   $nombre_archivo.'.xml' ;

        if ($contar_items > 0) {

            $see = $util->getSee2($endpoint);
            $res = $see->send($sum);

            $fileDir = 'files/facturacion/xml/'.$emp['ruc'];
            if (!file_exists($fileDir)) {
                mkdir($fileDir, 0777, true);
            }
            file_put_contents($fileDir.DIRECTORY_SEPARATOR.$nombre_xml,$see->getFactory()->getLastXml());

            if ($res->isSuccess()) {
                $ticket = $res->getTicket();
                $descripcion = "";
                $result = $see->getStatus($ticket);

                if ($result->isSuccess()) {
                    $resultado["res"]=true;
                    $cdr = $result->getCdrResponse();

                    $fileDir = 'files/facturacion/cdr/'.$emp['ruc'];
                    if (!file_exists($fileDir)) {
                        mkdir($fileDir, 0777, true);
                    }
                    file_put_contents($fileDir.DIRECTORY_SEPARATOR.'R-'.$nombre_archivo.'.zip',$result->getCdrZip());

                    $descripcion = $cdr->getDescription();

                    $fecha = date("Y-m-d");
                    $sql ="insert into resumen_diario set 
                      id_empresa='{$emp['id_empresa']}',
                      fecha='$fecha',
                      ticket='$ticket',
                      cantidad_items='$contar_items',
                      tipo='1'";
                    $conexion->query($sql);

                }else{
                    $resultado["msg"]="error: ". $util->getErrorResponse2($result->getError());
                }

            }

        }
        return $resultado;
    }

    public function envioIndividualGuiaRemi($nom_XML){


        $conexion=(new Conexion())->getConexion();

        $sql = "SELECT * FROM empresas where id_empresa = '{$_SESSION['id_empresa']}'";
        $empresa =  $conexion->query($sql)->fetch_assoc();


        $util = Util::getInstance();
        $util->setRuc($empresa['ruc']);
        $util->setClave($empresa['clave_sol']);
        $util->setUsuario($empresa['user_sol']);

        $fecha = date("Y-m-d");

        $endpoint='';

        /*if($empresa['modo']=='beta'){
            $endpoint=SunatEndpoints::GUIA_BETA;
        }elseif($empresa['modo']=='production'){
            $endpoint=SunatEndpoints::GUIA_PRODUCCION;
        }*/

        //$api = $util->getSeeApi2();

        //$see = $util->getSee2($endpoint);

        $nombre_archivo = $nom_XML;
        $xml_ruta = "files/facturacion/xml/".$empresa["ruc"].'/'.$nombre_archivo.".xml";

        $contenido =  file_get_contents($xml_ruta);

        $zipFly= new ZipFly();

        $zipContent = $zipFly->compress($nombre_archivo.".xml", $contenido);

        //var_dump($zipContent);
        die();

        $res = $api->sendXml( $nombre_archivo,$contenido );
        if ($res->isSuccess()) {
            $nombreCDR='R-'.$nombre_archivo.'.zip';
            $cdr = $res->getCdrZip();
            $fileDir =  'files/facturacion/cdr/'.$empresa['ruc'];
            if (!file_exists($fileDir)) {
                mkdir($fileDir, 0777, true);
            }
            file_put_contents($fileDir.DIRECTORY_SEPARATOR.$nombreCDR,$cdr);
            return true;
        }else{
            $mensaje = $util->getErrorResponse2($res->getError());
            $this->mensaje = $mensaje;
            return false;
        }

    }

    public function envioIndividualDocumentoVPorEmpresa($nom_XML,$id_empresa){
        $conexion=(new Conexion())->getConexion();

        $sql = "SELECT * FROM empresas where id_empresa = '$id_empresa'";
        $empresa =  $conexion->query($sql)->fetch_assoc();

        $util = Util::getInstance();
        $util->setRuc($empresa['ruc']);
        $util->setClave($empresa['clave_sol']);
        $util->setUsuario($empresa['user_sol']);

        $fecha = date("Y-m-d");

        $endpoint='';

        if($empresa['modo']=='beta'){
            $endpoint=SunatEndpoints::FE_BETA;
        }elseif($empresa['modo']=='production'){
            $endpoint=SunatEndpoints::FE_PRODUCCION;
        }

        $see = $util->getSee2($endpoint);

        $nombre_archivo = $nom_XML;
        $xml_ruta = "files/facturacion/xml/".$empresa["ruc"].'/'.$nombre_archivo.".xml";
        $contenido =  file_get_contents($xml_ruta);
        $res = $see->sendXml(Invoice::class, $nombre_archivo,$contenido );
        if ($res->isSuccess()) {
            $nombreCDR='R-'.$nombre_archivo.'.zip';
            $cdr = $res->getCdrZip();
            $fileDir =  'files/facturacion/cdr/'.$empresa['ruc'];
            if (!file_exists($fileDir)) {
                mkdir($fileDir, 0777, true);
            }
            file_put_contents($fileDir.DIRECTORY_SEPARATOR.$nombreCDR,$cdr);
            return true;
        }else{
            $mensaje = $util->getErrorResponse2($res->getError());
            $this->mensaje = $mensaje;
            return false;
        }

    }
    public function envioIndividualDocumentoV($nom_XML){
        $conexion=(new Conexion())->getConexion();

        $sql = "SELECT * FROM empresas where id_empresa = '{$_SESSION['id_empresa']}'";
        $empresa =  $conexion->query($sql)->fetch_assoc();

        $util = Util::getInstance();
        $util->setRuc($empresa['ruc']);
        $util->setClave($empresa['clave_sol']);
        $util->setUsuario($empresa['user_sol']);

        $fecha = date("Y-m-d");

        $endpoint='';

        if($empresa['modo']=='beta'){
            $endpoint=SunatEndpoints::FE_BETA;
        }elseif($empresa['modo']=='production'){
            $endpoint=SunatEndpoints::FE_PRODUCCION;
        }

        $see = $util->getSee2($endpoint);

        $nombre_archivo = $nom_XML;
        $xml_ruta = "files/facturacion/xml/".$empresa["ruc"].'/'.$nombre_archivo.".xml";
        $contenido =  file_get_contents($xml_ruta);
        $res = $see->sendXml(Invoice::class, $nombre_archivo,$contenido );
        if ($res->isSuccess()) {
            $nombreCDR='R-'.$nombre_archivo.'.zip';
            $cdr = $res->getCdrZip();
            $fileDir =  'files/facturacion/cdr/'.$empresa['ruc'];
            if (!file_exists($fileDir)) {
                mkdir($fileDir, 0777, true);
            }
            file_put_contents($fileDir.DIRECTORY_SEPARATOR.$nombreCDR,$cdr);
           return true;
        }else{
            $mensaje = $util->getErrorResponse2($res->getError());
            $this->mensaje = $mensaje;
            return false;
        }

    }


    public function genFacturaXML($dataE){
        if(isset($dataE['cliente'])&&is_string($dataE['cliente'])){
            $dataE['cliente']=json_decode($dataE['cliente'],true);
        }
        if(isset($dataE['empresa'])&&is_string($dataE['empresa'])){
            $dataE['empresa']=json_decode($dataE['empresa'],true);
        }
        if(isset($dataE['productos'])&&is_string($dataE['productos'])){
            $dataE['productos']=json_decode($dataE['productos'],true);
        }
        if(isset($dataE['dias_pagos'])&&is_string($dataE['dias_pagos'])){
            $dataE['dias_pagos']=json_decode($dataE['dias_pagos'],true);
        }

        $data = $dataE;

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

            $util->setCerGlobal(false);
            $igv_venta_sel=$data['igv_venta'];
            $client = new Client();
            $client->setTipoDoc('6')
                ->setNumDoc($data['cliente']['doc_num'])
                ->setRznSocial(utf8_encode($data['cliente']["nom_RS"]))
                ->setAddress((new Address())
                    ->setDireccion(utf8_encode($data['cliente']["direccion"])));

            $util->setRuc($data['empresa']["ruc"]);
            $util->setClave($data['empresa']["clave_sol"]);
            $util->setUsuario($data['empresa']["usuario_sol"]);

            $empresa = new Company();
            $empresa->setRuc($data['empresa']["ruc"])
                ->setNombreComercial(utf8_encode($data['empresa']["razon_social"]))
                ->setRazonSocial(utf8_encode($data['empresa']["razon_social"]))
                ->setAddress((new Address())
                    ->setUbigueo($data['empresa']["ubigeo"])
                    ->setDistrito($data['empresa']["distrito"])
                    ->setProvincia($data['empresa']["provincia"])
                    ->setDepartamento($data['empresa']["departamento"])
                    ->setUrbanizacion('-')
                    ->setCodLocal('0000')
                    ->setDireccion(utf8_encode($data['empresa']["direccion"])));

            $subtotal = number_format($data['total'] / ($igv_venta_sel+1), 2, ".", "");
            $igv = number_format($data['total'] / ($igv_venta_sel+1) * $igv_venta_sel, 2, ".", "");
            $total = number_format($data['total'], 2, ".", "");

// Venta
            $invoice = new Invoice();

            $montoGravada=number_format($data['total'] / ($igv_venta_sel+1), 2, ".", "");
            $igv_v=number_format($data['total'] / ($igv_venta_sel+1) * $igv_venta_sel, 2, ".", "");
            $total_imp=number_format($data['total'] / ($igv_venta_sel+1) * $igv_venta_sel, 2, ".", "");
            $valor_venta=number_format($data['total'] / ($igv_venta_sel+1), 2, ".", "");
            $impVenta=number_format($data['total'], 2, ".", "");

            if ($data['apli_igv']){
                $invoice
                    ->setUblVersion('2.1')

                    ->setTipoOperacion('0101')
                    ->setTipoDoc('01')
                    ->setSerie($data['serie'])
                    ->setFormaPago($data['tipo_pago']=='1'?new FormaPagoContado():new FormaPagoCredito($impVenta))
                    ->setCorrelativo($data['numero'])
                    ->setFechaEmision(\DateTime::createFromFormat('Y-m-d',$data['fechaE']))
                    ->setFecVencimiento(\DateTime::createFromFormat('Y-m-d',$data['fechaV']))
                    ->setTipoMoneda($data['moneda'])
                    ->setClient($client)
                    ->setMtoOperGravadas($montoGravada)
                    ->setMtoIGV($igv_v)
                    ->setTotalImpuestos($total_imp)
                    ->setValorVenta($valor_venta)
                    ->setMtoImpVenta($impVenta)
                    ->setSubTotal($impVenta)
                    ->setCompany($empresa);
            }else{
                $invoice
                    ->setUblVersion('2.1')
                    ->setTipoOperacion('0101')
                    ->setTipoDoc('01')
                    ->setSerie($data['serie'])
                    ->setFormaPago($data['tipo_pago']=='1'?new FormaPagoContado():new FormaPagoCredito($impVenta))
                    ->setCorrelativo($data['numero'])
                    ->setFechaEmision(\DateTime::createFromFormat('Y-m-d',$data['fechaE']))
                    ->setFecVencimiento(\DateTime::createFromFormat('Y-m-d',$data['fechaV']))
                    ->setTipoMoneda($data['moneda'])
                    ->setClient($client)
                    ->setMtoOperExoneradas($impVenta)
                    ->setMtoIGV(0)
                    ->setTotalImpuestos(0)
                    ->setValorVenta($impVenta)
                    ->setMtoImpVenta($impVenta)
                    ->setSubTotal($impVenta)
                    ->setCompany($empresa);
            }


            if (count($data['dias_pagos'])>0){
                $tempDiasPagos=[];
                foreach ($data['dias_pagos'] as $diasP){
                    $tempDiasPagos[]=(new Cuota())
                        ->setMonto($diasP['monto'])
                        ->setFechaPago(\DateTime::createFromFormat('Y-m-d',$diasP['fecha']));
                }
                $invoice->setCuotas($tempDiasPagos);
            }

            $array_items = array();

            foreach ($data['productos'] as $value) {
                $subtotal_producto = $value['precio'] * $value['cantidad'] / ($igv_venta_sel+1);
                $igv_producto = $value['precio'] * $value['cantidad'] / ($igv_venta_sel+1) * $igv_venta_sel;
                $total_producto = $value['precio'] * $value['cantidad'];
                $item = new SaleDetail();
                $item->setCodProducto($value['cod_pro'])
                    ->setCodProdSunat($value['cod_sunat'])
                    ->setUnidad('NIU')
                    ->setDescripcion(utf8_encode($value['descripcion']))
                    ->setCantidad($value['cantidad']);

                if ($data['apli_igv']){
                    $item->setMtoValorUnitario(number_format($value['precio'] / ($igv_venta_sel+1), 2, '.', ''))
                        ->setMtoValorVenta(number_format($subtotal_producto, 2, '.', ''))
                        ->setMtoBaseIgv(number_format($subtotal_producto, 2, '.', ''))
                        ->setPorcentajeIgv($igv_venta_sel*100)
                        ->setIgv(number_format($igv_producto, 2, '.', ''))
                        ->setTipAfeIgv('10')
                        ->setTotalImpuestos(number_format($igv_producto, 2, '.', ''))
                        ->setMtoPrecioUnitario(number_format($value['precio'], 2, '.', ''));
                }else{
                    $item->setMtoValorUnitario(number_format($value['precio'], 2, '.', ''))
                        ->setMtoValorVenta(number_format($value['precio']*$value['cantidad'], 2, '.', ''))
                        ->setMtoBaseIgv(number_format($value['precio']*$value['cantidad'], 2, '.', ''))
                        ->setPorcentajeIgv(0)
                        ->setIgv(0)
                        ->setTipAfeIgv('20')
                        ->setTotalImpuestos(0)
                        ->setMtoPrecioUnitario(number_format($value['precio'], 2, '.', ''));
                }
                $array_items[] = $item;
            }

            $invoice->setDetails($array_items);
            $c_numeros = new NumerosaLetras();
            $numeros = utf8_decode($c_numeros->to_word(number_format($data['total'], 2, ".", ""), $data['moneda']));
            //echo $numeros;
            $invoice->setLegends([
                (new Legend())
                    ->setCode('1000')
                    ->setValue($numeros)
            ]);

//fijar variables principales
            $nombre_archivo = $invoice->getName();
            $nombre_xml =   $invoice->getName() . ".xml";
            $hash = $util->getHash2($invoice);
            $qr ='';
            if ($data['apli_igv']){
                $qr = $data['empresa']["ruc"] . "|" . "03" . "|"
                    . $data['serie'] . "-" . $data['numero'] . "|"
                    . $igv . "|" . $total . "|" . $data['fechaE'] . "|"
                    . '06' . "|" . $data['cliente']['doc_num'];
            }else{
                $qr = $data['empresa']["ruc"] . "|" . "03" . "|"
                    . $data['serie'] . "-" . $data['numero'] . "|"
                    . 0 . "|" . $data['total'] . "|" . $data['fechaE'] . "|"
                    . '06' . "|" . $data['cliente']['doc_num'];
            }



            $see = $util->getSee2($endpoint);
            $consten_XML= $see->getXmlSigned($invoice);

            $loc_ruta="files/facturacion/xml/".$data['empresa']["ruc"];
            if (!file_exists($loc_ruta)) {
                mkdir($loc_ruta, 0777, true);
            }

            file_put_contents($loc_ruta."/".$nombre_archivo.".xml", $consten_XML);


            return[
                "res"=>true,
                "data"=>[
                    "qr"=>$qr,
                    "hash"=>$hash,
                    "nombre_archivo"=>$nombre_archivo
                ]
            ];

        }else{
            return ["res"=>false,"msg"=>"Endpoints no establecido"];
        }
    }
    public function genBoletaXML($dataE){
        if(isset($dataE['cliente'])&&is_string($dataE['cliente'])){
            $dataE['cliente']=json_decode($dataE['cliente'],true);
        }
        if(isset($dataE['empresa'])&&is_string($dataE['empresa'])){
            $dataE['empresa']=json_decode($dataE['empresa'],true);
        }
        if(isset($dataE['productos'])&&is_string($dataE['productos'])){
            $dataE['productos']=json_decode($dataE['productos'],true);
        }
        if(isset($dataE['dias_pagos'])&&is_string($dataE['dias_pagos'])){
            $dataE['dias_pagos']=json_decode($dataE['dias_pagos'],true);
        }

        $data = $dataE;

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

            $util->setCerGlobal(false);

            $igv_venta_sel=$data['igv_venta'];

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
                ->setRznSocial($nom_rs_c=='-'?'cliente':utf8_encode($nom_rs_c));

            $util->setRuc($data['empresa']["ruc"]);
            $util->setClave($data['empresa']["clave_sol"]);
            $util->setUsuario($data['empresa']["usuario_sol"]);

            $empresa = new Company();
            $empresa->setRuc($data['empresa']["ruc"])
                ->setNombreComercial(utf8_encode($data['empresa']["razon_social"]))
                ->setRazonSocial(utf8_encode($data['empresa']["razon_social"]))
                ->setAddress((new Address())
                    ->setUbigueo($data['empresa']["ubigeo"])
                    ->setDistrito($data['empresa']["distrito"])
                    ->setProvincia($data['empresa']["provincia"])
                    ->setDepartamento($data['empresa']["departamento"])
                    ->setUrbanizacion('-')
                    ->setCodLocal('0000')
                    ->setDireccion(utf8_encode($data['empresa']["direccion"])));

            $subtotal = number_format($data['total'] / ($igv_venta_sel+1), 2, ".", "");
            $igv = number_format($data['total'] / ($igv_venta_sel+1) * $igv_venta_sel, 2, ".", "");
            $total = number_format($data['total'], 2, ".", "");

// Venta
            $invoice = new Invoice();

            $montoGravada=number_format($data['total'] / ($igv_venta_sel+1), 2, ".", "");
            $igv_v=number_format($data['total'] / ($igv_venta_sel+1) * $igv_venta_sel, 2, ".", "");
            $total_imp=number_format($data['total'] / ($igv_venta_sel+1) * $igv_venta_sel, 2, ".", "");
            $valor_venta=number_format($data['total'] / ($igv_venta_sel+1), 2, ".", "");
            $impVenta=number_format($data['total'], 2, ".", "");

            if ($data['apli_igv']){
                $invoice
                    ->setUblVersion('2.1')
                    ->setTipoOperacion('0101')
                    ->setTipoDoc('03')
                    ->setSerie($data['serie'])
                    ->setFormaPago($data['tipo_pago']=='1'?new FormaPagoContado():new FormaPagoCredito($impVenta))
                    ->setCorrelativo($data['numero'])
                    ->setFechaEmision(\DateTime::createFromFormat('Y-m-d',$data['fechaE']))
                    ->setFecVencimiento(\DateTime::createFromFormat('Y-m-d',$data['fechaV']))
                    ->setTipoMoneda($data['moneda'])
                    ->setClient($client)
                    ->setMtoOperGravadas($montoGravada)
                    ->setMtoIGV($igv_v)
                    ->setTotalImpuestos($total_imp)
                    ->setValorVenta($valor_venta)
                    ->setMtoImpVenta($impVenta)
                    ->setSubTotal($impVenta)
                    ->setCompany($empresa);
            }else{
                $invoice
                    ->setUblVersion('2.1')
                    ->setTipoOperacion('0101')
                    ->setTipoDoc('03')
                    ->setSerie($data['serie'])
                    ->setFormaPago($data['tipo_pago']=='1'?new FormaPagoContado():new FormaPagoCredito($impVenta))
                    ->setCorrelativo($data['numero'])
                    ->setFechaEmision(\DateTime::createFromFormat('Y-m-d',$data['fechaE']))
                    ->setFecVencimiento(\DateTime::createFromFormat('Y-m-d',$data['fechaV']))
                    ->setTipoMoneda($data['moneda'])
                    ->setClient($client)
                    ->setMtoOperExoneradas($impVenta)
                    ->setMtoIGV(0)
                    ->setTotalImpuestos(0)
                    ->setValorVenta($impVenta)
                    ->setMtoImpVenta($impVenta)
                    ->setSubTotal($impVenta)
                    ->setCompany($empresa);
            }


            if (count($data['dias_pagos'])>0){
                $tempDiasPagos=[];
                foreach ($data['dias_pagos'] as $diasP){
                    $tempDiasPagos[]=(new Cuota())
                        ->setMonto($diasP['monto'])
                        ->setFechaPago(\DateTime::createFromFormat('Y-m-d',$diasP['fecha']));
                }
                $invoice->setCuotas($tempDiasPagos);
            }
            $array_items = array();

            foreach ($data['productos'] as $value) {
                $subtotal_producto = $value['precio'] * $value['cantidad'] / ($igv_venta_sel+1);
                $igv_producto = $value['precio'] * $value['cantidad'] / ($igv_venta_sel+1) * $igv_venta_sel;
                $total_producto = $value['precio'] * $value['cantidad'];
                $item = new SaleDetail();
                $item->setCodProducto($value['cod_pro'])
                    ->setCodProdSunat($value['cod_sunat'])
                    ->setUnidad('NIU')
                    ->setDescripcion(utf8_encode($value['descripcion']))
                    ->setCantidad($value['cantidad']);


                if ($data['apli_igv']){
                    $item->setMtoValorUnitario(number_format($value['precio'] / ($igv_venta_sel+1), 2, '.', ''))
                        ->setMtoValorVenta(number_format($subtotal_producto, 2, '.', ''))
                        ->setMtoBaseIgv(number_format($subtotal_producto, 2, '.', ''))
                        ->setPorcentajeIgv($igv_venta_sel*100)
                        ->setIgv(number_format($igv_producto, 2, '.', ''))
                        ->setTipAfeIgv('10')
                        ->setTotalImpuestos(number_format($igv_producto, 2, '.', ''))
                        ->setMtoPrecioUnitario(number_format($value['precio'], 2, '.', ''));
                }else{
                    $item->setMtoValorUnitario(number_format($value['precio'], 2, '.', ''))
                        ->setMtoValorVenta(number_format($value['precio']*$value['cantidad'], 2, '.', ''))
                        ->setMtoBaseIgv(number_format($value['precio']*$value['cantidad'], 2, '.', ''))
                        ->setPorcentajeIgv(0)
                        ->setIgv(0)
                        ->setTipAfeIgv('20')
                        ->setTotalImpuestos(0)
                        ->setMtoPrecioUnitario(number_format($value['precio'], 2, '.', ''));
                }

                $array_items[] = $item;
            }

            $invoice->setDetails($array_items);
            $c_numeros = new NumerosaLetras();
            $numeros = utf8_decode($c_numeros->to_word(number_format($data['total'], 2, ".", ""), $data['moneda']));
            $invoice->setLegends([
                (new Legend())
                    ->setCode('1000')
                    ->setValue($numeros)
            ]);

//fijar variables principales


            $nombre_archivo = $invoice->getName();
            $nombre_xml =   $invoice->getName() . ".xml";
            $hash = $util->getHash2($invoice);
            $qr ='';
            if ($data['apli_igv']){
                $qr = $data['empresa']["ruc"] . "|" . "03" . "|"
                    . $data['serie'] . "-" . $data['numero'] . "|"
                    . $igv . "|" . $total . "|" . $data['fechaE'] . "|"
                    . $tipo_doc . "|" . $documente_num;
            }else{
                $qr = $data['empresa']["ruc"] . "|" . "03" . "|"
                    . $data['serie'] . "-" . $data['numero'] . "|"
                    . 0 . "|" . $data['total'] . "|" . $data['fechaE'] . "|"
                    . $tipo_doc . "|" . $documente_num;
            }



            $see = $util->getSee2($endpoint);

            $consten_XML= $see->getXmlSigned($invoice);

            $loc_ruta="files/facturacion/xml/".$data['empresa']["ruc"];
            if (!file_exists($loc_ruta)) {
                mkdir($loc_ruta, 0777, true);
            }

            file_put_contents($loc_ruta."/".$nombre_archivo.".xml", $consten_XML);


            return [
                "res"=>true,
                "data"=>[
                    "qr"=>$qr,
                    "hash"=>$hash,
                    "nombre_archivo"=>$nombre_archivo,
                ]
            ];

        }else{
            return ["res"=>false,"msg"=>"Endpoints no establecido"];
        }

    }

    public function genGuiaRemision($dataE){

       if(isset($dataE['cliente'])&&is_string($dataE['cliente'])){
            $dataE['cliente']=json_decode($dataE['cliente'],true);
        }
        if(isset($dataE['empresa'])&&is_string($dataE['empresa'])){
            $dataE['empresa']=json_decode($dataE['empresa'],true);
        }
        if(isset($dataE['productos'])&&is_string($dataE['productos'])){
            $dataE['productos']=json_decode($dataE['productos'],true);
        }

        if(isset($dataE['venta'])&&is_string($dataE['venta'])){
            $dataE['venta']=json_decode($dataE['venta'],true);
        }
        if(isset($dataE['transporte'])&&is_string($dataE['transporte'])){
            $dataE['transporte']=json_decode($dataE['transporte'],true);
        }

        $endpoint='';
        $continuar=false;
        if(isset($dataE["endpoints"])){
            if($dataE['endpoints']=='beta'){
                $continuar=true;
                $endpoint=SunatEndpoints::GUIA_BETA;
            }elseif($dataE['endpoints']=='production'){
                $continuar=true;
                $endpoint=SunatEndpoints::GUIA_PRODUCCION;
            }
        }

        if($continuar){
            $util = Util::getInstance();
            $util->setCerGlobal($dataE['certGlobal']);

            $util->setRuc($dataE['empresa']["ruc"]);
            $util->setClave($dataE['empresa']["clave_sol"]);
            $util->setUsuario($dataE['empresa']["usuario_sol"]);


            $documento=$dataE['cliente']['doc_num'];
            $datosCli=$dataE['cliente']['nom_RS'];
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
            $empresa->setRuc($dataE['empresa']['ruc'])
                ->setNombreComercial(utf8_encode($dataE['empresa']['razon_social']))
                ->setRazonSocial(utf8_encode($dataE['empresa']['razon_social']))
                ->setAddress((new Address())
                    ->setUbigueo($dataE['empresa']["ubigeo"])
                    ->setDistrito($dataE['empresa']["distrito"])
                    ->setProvincia($dataE['empresa']["provincia"])
                    ->setDepartamento($dataE['empresa']["departamento"])
                    ->setUrbanizacion('-')
                    ->setCodLocal('0000')
                    ->setDireccion(utf8_encode($dataE['empresa']["direccion"])));


            $array_items = array();
            $sumar_cantidades = 0;

            $items=$dataE['productos'];

            foreach ($items as $value) {
                $detail = new DespatchDetail();
                $detail->setCantidad($value['cantidad'])
                    ->setUnidad('NIU')
                    ->setDescripcion(utf8_encode($value['descripcion']) )
                    ->setCodigo($value['cod_pro']);
                //->setCodProdSunat($value['id_producto']);

                $sumar_cantidades += $value['cantidad'];

                $array_items[] = $detail;
            }

            $transp = new Transportist();
            $transp->setTipoDoc('6')
                ->setNumDoc(utf8_encode($dataE['transporte']['ruc']))
                ->setRznSocial(utf8_encode($dataE['transporte']['razon_social']))
                ->setPlaca($dataE['transporte']['placa'])
                ->setChoferTipoDoc('1')
                ->setChoferDoc($dataE['transporte']['doc_chofer']);

            $envio = new Shipment();
            $envio->setModTraslado('01')
                ->setCodTraslado('01')
                ->setDesTraslado('VENTA')
                ->setFecTraslado(new \DateTime())
                ->setIndTransbordo(false)
                ->setPesoTotal($dataE['peso'])
                ->setUndPesoTotal('KGM')
                //->setNumBultos($sumar_cantidades)
                ->setLlegada(new Direction($dataE['ubigeo'],utf8_encode( $dataE['direccion'])))
                ->setPartida(new Direction($dataE['empresa']["ubigeo"],utf8_encode($dataE['empresa']["direccion"])))
                ->setTransportista($transp);

            $despatch = new Despatch();
            $despatch->setTipoDoc('09')
                ->setSerie($dataE['serie'])
                ->setCorrelativo($dataE['numero'])
                ->setFechaEmision(new \DateTime())
                ->setCompany($empresa)
                ->setDestinatario($client)
                //->setTercero($client)
                ->setObservacion('FT: ' . $dataE['venta']['serie'] . "-" . $dataE['venta']['numero'])
                ->setEnvio($envio);

            $despatch->setDetails($array_items);


            $nombre_archivo = $despatch->getName();

            // Envio a SUNAT.

            /*$api = $util->getSeeApi2();
            $res = $api->send($despatch);*/
            //$api = $util->getSeeApi();
            //$res = $api->send($despatch);
            //$util->writeXml($despatch, $api->getLastXml());

//            var_dump($res);

            //$consten_XML= $api->getLastXml();
            $see = $util->getSee2($endpoint);
            $consten_XML=  $see->getXmlSigned($despatch);
            //var_dump($consten_XML);
            $hash = "";//$util->getHash($despatch);



            $loc_ruta="files/facturacion/xml/".$dataE['empresa']["ruc"];
            if (!file_exists($loc_ruta)) {
                mkdir($loc_ruta, 0777, true);
            }

            file_put_contents($loc_ruta."/".$nombre_archivo.".xml", $consten_XML);


            $qr = $dataE['empresa']["ruc"] . "|" . "09" . "|" .
                $dataE['serie'] . "-" . $dataE['numero'] .
                "|0.00|0.00|" . $dataE['fecha'] . "|" . $tipo_doc . "|" .
                $dataE['cliente']['doc_num'];

            return[
                "res"=>true,
                "data"=>[
                    "qr"=>$qr,
                    "hash"=>$hash,
                    "nombre_archivo"=>$nombre_archivo
                ]
            ];

        }else{
            return ["res"=>false,"msg"=>"Endpoints no establecido"];
        }


    }

    public function genNotaElectronicaXML($dataE){


        if(isset($dataE['cliente'])&&is_string($dataE['cliente'])){
            $dataE['cliente']=json_decode($dataE['cliente'],true);
        }
        if(isset($dataE['empresa'])&&is_string($dataE['empresa'])){
            $dataE['empresa']=json_decode($dataE['empresa'],true);
        }
        if(isset($dataE['productos'])&&is_string($dataE['productos'])){
            $dataE['productos']=json_decode($dataE['productos'],true);
        }

        $data = $dataE;

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
                ->setRznSocial($nom_rs_c=='-'?'cliente':utf8_encode($nom_rs_c))
                ->setAddress((new Address())
                    ->setDireccion($data['cliente']["direccion"]));

            $empresa = new Company();
            $empresa->setRuc($data['empresa']["ruc"])
                ->setNombreComercial(utf8_encode($data['empresa']["razon_social"]))
                ->setRazonSocial(utf8_encode($data['empresa']["razon_social"]))
                ->setAddress((new Address())
                    ->setUbigueo($data['empresa']["ubigeo"])
                    ->setDistrito($data['empresa']["distrito"])
                    ->setProvincia($data['empresa']["provincia"])
                    ->setDepartamento($data['empresa']["departamento"])
                    ->setUrbanizacion('-')
                    ->setCodLocal('0000')
                    ->setDireccion(utf8_encode($data['empresa']["direccion"])));


            $montoGravada=number_format($data['total'] / 1.18, 2, ".", "");
            $igv_v=number_format($data['total'] / 1.18 * 0.18, 2, ".", "");
            $total_imp=number_format($data['total'] / 1.18 * 0.18, 2, ".", "");
            $valor_venta=number_format($data['total'] / 1.18, 2, ".", "");
            $impVenta=number_format($data['total'], 2, ".", "");

            $note = new Note();
            $note
                ->setUblVersion('2.1')
                ->setTipoDoc($data['cod_notaE']) // Tipo Doc: Nota de Credito y devito
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
                    ->setDescripcion(utf8_encode($value['descripcion']))
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
            $note->setDetails($array_items);
            $c_numeros = new NumerosaLetras();
            $numeros = utf8_decode($c_numeros->to_word(number_format($data['total'], 2, ".", ""), "PEN"));
            $note->setLegends([
                (new Legend())
                    ->setCode('1000')
                    ->setValue($numeros)
            ]);
            //fijar variables principales
            $nombre_archivo = $note->getName();
            $nombre_xml =   $note->getName() . ".xml";
            $hash = $util->getHash($note);

            $qr = $data['empresa']["ruc"] . "|" . "07" . "|"
                . $data['serie'] . "-" . $data['numero'] . "|"
                . $igv_v . "|" . $total_imp . "|" . $data['fecha'] . "|"
                . $tipo_doc . "|" . $documente_num;

            $see = $util->getSee2($endpoint);
            $consten_XML= $see->getXmlSigned($note);


            $loc_ruta="files/facturacion/xml/".$dataE['empresa']["ruc"];
            if (!file_exists($loc_ruta)) {
                mkdir($loc_ruta, 0777, true);
            }

            file_put_contents($loc_ruta."/".$nombre_archivo.".xml", $consten_XML);

            /*$res = $see->sendXml(Invoice::class, $nombre_archivo,$consten_XML );

            $error_mensaje='';
            if (!$res->isSuccess()) {
                $error_mensaje= "Error: ".$util->getErrorResponse($res->getError());
            }*/

            return[
                "res"=>true,
                "data"=>[
                    "qr"=>$qr,
                    "hash"=>$hash,
                    "nombre_archivo"=>$nombre_archivo,
                    //"error"=>$error_mensaje
                ]
            ];
        }else{
            return ["res"=>false,"msg"=>"Endpoints no establecido"] ;
        }

    }


    /**
     * @return mixed
     */
    public function getMensaje()
    {
        return $this->mensaje;
    }

    /**
     * @param mixed $mensaje
     */
    public function setMensaje($mensaje): void
    {
        $this->mensaje = $mensaje;
    }




}