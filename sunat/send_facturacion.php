<?php

date_default_timezone_set('America/Lima');

if(isset($_REQUEST['cliente'])&&is_string($_REQUEST['cliente'])){
    $_REQUEST['cliente']=json_decode($_REQUEST['cliente'],true);
}
if(isset($_REQUEST['empresa'])&&is_string($_REQUEST['empresa'])){
    $_REQUEST['empresa']=json_decode($_REQUEST['empresa'],true);
}
if(isset($_REQUEST['productos'])&&is_string($_REQUEST['productos'])){
    $_REQUEST['productos']=json_decode($_REQUEST['productos'],true);
}
use Greenter\Model\Sale\Invoice;
use Greenter\Ws\Services\SunatEndpoints;

require __DIR__ . '/vendor/autoload.php';
require __DIR__."/utils/NumerosaLetras.php";

$data = $_REQUEST;

$endpoint='';
$continuar=false;
$respuesta =["res"=>false,"msg"=>''];
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

    $ruc_empr="20123456789";
    $dominio=$data['certGlobal'];

    $util->setCerGlobal($data['certGlobal']);
    $util->setRuc($data["ruc"]);
    $util->setClave($data["clave_sol"]);
    $util->setUsuario($data["usuario_sol"]);


    $see = $util->getSee2($endpoint);


    if ($data["lotes"]){

        if (isset($data["lote"])){
            $arrayLote=[];
            $continuarLote=false;
            if (is_array($data["lote"])){
                $continuarLote=true;
                $arrayLote=$data["lote"];
            }elseif (is_string($data["lote"])){
                try{
                    $arrayLote=json_decode($data["lote"],true);
                    $continuarLote=true;
                }catch(Exception $e){
                    $respuesta["msg"]=$e->getMessage();
                }
            }
            if ($continuarLote){
                $respuesta['res'] =true;
                $respuesta["resultados"]=[];
                foreach ($arrayLote as $doc){
                    $tempRes=[
                        "res"=>false,
                        "msg"=>'',
                        "nombre"=>'',
                        "datos"=>'',
                        "metas"=>$doc['metas']
                    ];
                    $nombre_archivo = $doc['nombre_XML'];
                    $nombre_xml = $data['ruta_xml'] . $nombre_archivo . ".xml";
                    $res = $see->sendXml(Invoice::class, $nombre_archivo,$doc['contenido_XML'] );
                    if ($res->isSuccess()) {
                        $tempRes["res"]=true;
                        $tempRes['nombre']='R-'.$nombre_archivo.'.zip';
                        //obtener cdr y guardar en json
                        $cdr = $res->getCdrResponse();
                        $tempRes['cdr']= utf8_encode($res->getCdrZip());

                    }else{
                        $tempRes["msg"] = $util->getErrorResponse2($res->getError());
                    }
                    $respuesta["resultados"][]=$tempRes;
                }

            }else{
                $respuesta["msg"]="El Lote deve ser un Array o String en formato JSON";
            }

        }else{
            $respuesta["msg"]="Lote No definido";
        }
    }else{
        if (isset($data["documento"])){
            if(isset($data['documento'])){

                $nombre_archivo = $data['documento']['nombre_XML'];
                $nombre_xml = $data['ruta_xml'] . $nombre_archivo . ".xml";
                $res = $see->sendXml(Invoice::class, $nombre_archivo,$data['documento']['contenido_XML'] );
                if ($res->isSuccess()) {
                    $respuesta["msg"]=true;
                    $respuesta['nombre']='R-'.$nombre_archivo.'.zip';
                    //obtener cdr y guardar en json
                    $cdr = $res->getCdrResponse();
                    //$respuesta['cdr']= $res->getCdrZip();

                }else{
                    $respuesta["msg"] = $util->getErrorResponse2($res->getError());
                }
            }else{
                $respuesta["msg"]="documento No definido";
            }
        }else{
            $respuesta["msg"]="Documento No definido";
        }
    }


}else{
    $respuesta["msg"]="Endpoints no establecido";
}
//var_dump($respuesta);
echo json_encode($respuesta);
//echo json_last_error_msg();