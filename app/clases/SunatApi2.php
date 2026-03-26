<?php
require_once "sunat2/vendor/autoload.php";
require_once "utils/lib/gre_suant/vendor/autoload.php";

use Greenter\Sunat\GRE\ApiException;
use Greenter\Zip\ZipFly;

class SunatApi2
{
    private $conexion;
    private $mensaje;
    public function __construct()
    {
        $this->conexion = (new Conexion())->getConexion();
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
    public function envioIndividualGuiaRemi2($nom_XML){
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

        $api = $util->getSeeApi2();

        //$see = $util->getSee2($endpoint);

        $nombre_archivo = $nom_XML;
        $xml_ruta = "files/facturacion/xml/".$empresa["ruc"].'/'.$nombre_archivo.".xml";

        $contenido =  file_get_contents($xml_ruta);




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
    public function envioIndividualGuiaRemi($nom_XML){
        $conexion=(new Conexion())->getConexion();

        $sql = "SELECT * FROM empresas where id_empresa = '{$_SESSION['id_empresa']}'";
        $empresa =  $conexion->query($sql)->fetch_assoc();

        $apiInstance = new Greenter\Sunat\GRE\Api\AuthApi(
            new GuzzleHttp\Client()
        );
        $client_id = '4c4fd4c3-c380-4447-9223-0e60a8a09a14'; // El client_id generado en menú sol
        $grant_type = 'password';
        $scope = 'https://api-cpe.sunat.gob.pe';
        $client_secret = 'WLX0zMBp8jE2Jhr5UHomjg=='; // client_secret generado en menú sol
        $username =$empresa['ruc'].$empresa['user_sol']; // <Numero de RUC> + <Usuario SOL>
        $password = $empresa['clave_sol']; // Contraseña SOL
        $token='';
        try {
            $result = $apiInstance->getToken($grant_type, $scope, $client_id, $client_secret, $username, $password);

            $token=$result->getAccessToken();
        } catch (Exception $e) {
            echo 'Excepcion cuando invocaba AuthApi->getToken: ', $e->getMessage(), PHP_EOL;
        }

        $xml_ruta = "files/facturacion/xml/".$empresa["ruc"].'/'.$nom_XML.".xml";

        $contenido =  file_get_contents($xml_ruta);

        $zipFly= new ZipFly();

        $zipContent = $zipFly->compress($nom_XML.".xml", $contenido);

        try{
            $url_cep="https://api-cpe.sunat.gob.pe/v1/contribuyente/gem/comprobantes/".$nom_XML;

            $fields = array('archivo' => [
                "nomArchivo"=>$nom_XML.".zip",
                "arcGreZip"=>base64_encode($zipContent),
                "hashZip"=>hash('sha256', $zipContent)
            ]);

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url_cep);
            curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json',"Authorization:Bearer ".$token));
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields) );
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $data = curl_exec($ch);
            curl_close($ch);
            $objeto = json_decode($data,true);
            if ($objeto){
                if (isset($objeto['numTicket'])){
                    return true;
                }else{
                    $this->mensaje="No se Pudo enviar a la Sunat";
                }
            }else{
                $this->mensaje="Error en la sunat";
            }

        }catch (Exception $e){
            $this->mensaje=$e->getMessage();
        }
        return false;
    }
}