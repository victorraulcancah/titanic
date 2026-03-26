<?php
require_once "app/models/GuiaRemision.php";
require_once "app/models/GuiaDetalle.php";
require_once "app/models/DocumentoEmpresa.php";
require_once "app/models/GuiaSunat.php";
require_once "app/clases/SendURL.php";
require_once "app/clases/SunatApi.php";
require_once "app/clases/SunatApi2.php";

class GuiaRemisionController extends Controller
{
    private $sunatApi;
    private $sunatApi2;
    private $conexion;
    public function __construct()
    {
        $this->sunatApi2 = new SunatApi2();
        $this->sunatApi = new SunatApi();
        $this->conexion = (new Conexion())->getConexion();
    }

    public function enviarDocumentoSunat()
    {
        $conexion = (new Conexion())->getConexion();
        $sql = "select * from guia_sunat where id_guia = '{$_POST['cod']}'";
        $dataGuia = $conexion->query($sql)->fetch_assoc();
        $resultado = ["res" => false];
        if ($this->sunatApi2->envioIndividualGuiaRemi($dataGuia['nombre_xml'])) {
            $sql = "update guia_remision set  enviado_sunat='1' where id_guia_remision= '{$_POST["cod"]}'";
            $conexion->query($sql);
            $resultado['res'] = true;
        } else {
            //echo "Error1";
            $resultado['msg'] = $this->sunatApi2->getMensaje();
        }
        return json_encode($resultado);
    }

    public function insertar()
    {
        $c_guia = new GuiaRemision();
        $c_documentos = new DocumentoEmpresa();
        $guiaDetalle = new GuiaDetalle();
        $guiaSunat = new GuiaSunat();
        $sendURL = new SendURL();

        $dataSend = [];
        $dataSend["certGlobal"] = false;


        //$sendGuia = new SendCurlGuia();
        /*   $data = $_POST['data'];
        $datosGuiaRemosion = json_decode($data['datosGuiaRemosion'], true);
        return json_encode($datosGuiaRemosion);

        return; */

        $c_guia->setFecha(filter_input(INPUT_POST, 'fecha_emision'));
        $c_guia->setIdVenta(filter_input(INPUT_POST, 'venta'));
        $c_guia->setDirLlegada(filter_input(INPUT_POST, 'dir_cli'));
        $c_guia->setUbigeo(filter_input(INPUT_POST, 'ubigeo'));
        $c_guia->setTipoTransporte(filter_input(INPUT_POST, 'tipo_trans'));
        $c_guia->setRucTransporte(filter_input(INPUT_POST, 'ruc'));
        $c_guia->setRazTransporte(filter_input(INPUT_POST, 'razon_social'));
        $c_guia->setVehiculo(filter_input(INPUT_POST, 'veiculo'));
        $c_guia->setChofer(filter_input(INPUT_POST, 'chofer_dni'));


        $c_guia->setPeso(filter_input(INPUT_POST, 'peso'));
        $c_guia->setNroBultos(filter_input(INPUT_POST, 'num_bultos'));

        $c_guia->setIdEmpresa($_SESSION['id_empresa']);

        $c_documentos->setIdTido(11);
        $c_documentos->setIdEmpresa($c_guia->getIdEmpresa());
        $c_documentos->obtenerDatos();

        $c_guia->setSerie($c_documentos->getSerie());
        $c_guia->setNumero($c_documentos->getNumero());

        $dataSend['peso'] = $c_guia->getPeso();
        $dataSend['ubigeo'] = $c_guia->getUbigeo();
        $dataSend['direccion'] = $c_guia->getDirLlegada();
        $dataSend['serie'] = $c_guia->getSerie();
        $dataSend['numero'] = $c_guia->getNumero();
        $dataSend['fecha'] = $c_guia->getFecha();

        // $c_guia->obtenerId();
        $resultado = ["res" => false];
        if ($c_guia->insertar()) {
            //echo "xsssss";

            $resultado["res"] = true;
            $resultado["guia"] = $c_guia->getIdGuia();
            $listaProd = json_decode($_POST['productos'], true);
            $guiaDetalle->setIdGuia($c_guia->getIdGuia());

            $dataSend['productos'] = [];
            foreach ($listaProd as $prodG) {
                $guiaDetalle->setCantidad($prodG['cantidad']);
                $guiaDetalle->setDetalles($prodG['descripcion']);
                $guiaDetalle->setIdProducto($prodG['idproducto']);
                $guiaDetalle->setPrecio($prodG['precio']);
                $guiaDetalle->setUnidad("NIU");
                $guiaDetalle->insertar();
                $dataSend['productos'][] = [
                    'cantidad' => $prodG['cantidad'],
                    'cod_pro' => $prodG['idproducto'],
                    'cod_sunat' => "000",
                    'descripcion' => $prodG['descripcion']
                ];
            }

            $dataSend['productos'] = json_encode($dataSend['productos']);

            $sql = "SELECT * from empresas where id_empresa = " . $_SESSION['id_empresa'];
            $respEmpre = $c_guia->exeSQL($sql)->fetch_assoc();

            $dataSend["endpoints"] = $respEmpre['modo'];

            $dataSend['empresa'] = json_encode([
                'ruc' => $respEmpre['ruc'],
                'razon_social' => $respEmpre['razon_social'],
                'direccion' => $respEmpre['direccion'],
                'ubigeo' => $respEmpre['ubigeo'],
                'distrito' => $respEmpre['distrito'],
                'provincia' => $respEmpre['provincia'],
                'departamento' => $respEmpre['departamento'],
                'clave_sol' => $respEmpre['clave_sol'],
                'usuario_sol' => $respEmpre['user_sol']
            ]);

            $dataSend['venta'] = json_encode([
                'serie' => filter_input(INPUT_POST, 'serie'),
                'numero' => filter_input(INPUT_POST, 'numero')
            ]);
            $dataSend['cliente'] = json_encode([
                'doc_num' => filter_input(INPUT_POST, 'doc_cli'),
                'nom_RS' => filter_input(INPUT_POST, 'nom_cli')
            ]);
            $dataSend['transporte'] = json_encode([
                'ruc' => filter_input(INPUT_POST, 'ruc'),
                'razon_social' => filter_input(INPUT_POST, 'razon_social'),
                'placa' => filter_input(INPUT_POST, 'veiculo'),
                'doc_chofer' => filter_input(INPUT_POST, 'chofer_dni')
            ]);

            $dataResp = $this->sunatApi->genGuiaRemision($dataSend);

            /*$respCURL =SendURL::SendGuiaRemision($dataSend);
            $respCURL = json_decode($respCURL,true);
            $dataResp= $respCURL["data"];

            $rutaFileXML="file/xml/".$respEmpre['ruc'];
            if (!file_exists($rutaFileXML)){
                mkdir($rutaFileXML, 0777, true);
            }

            $myfile = fopen($rutaFileXML.'/'.$dataResp['nombre_archivo'].".xml", "w");
            fwrite($myfile,$dataResp['consten_XML']);
            fclose($myfile);*/

            if ($dataResp["res"]) {
                $guiaSunat->setIdGuia($c_guia->getIdGuia());
                $guiaSunat->setHash($dataResp["data"]['hash']);
                $guiaSunat->setNombreXml($dataResp["data"]['nombre_archivo']);
                $guiaSunat->setQrData($dataResp["data"]['qr']);
                $guiaSunat->insertar();
            }
        }
        return json_encode($resultado);
    }
    public function insertar2()
    {
        $c_guia = new GuiaRemision();
        $c_documentos = new DocumentoEmpresa();
        $guiaDetalle = new GuiaDetalle();
        $guiaSunat = new GuiaSunat();
        $sendURL = new SendURL();

        $dataSend = [];
        $dataSend["certGlobal"] = false;


        //$sendGuia = new SendCurlGuia();
        /* return json_encode($_POST['idVenta']);
        return; */
        $data = $_POST['data'];
        $datosGuiaRemosion = json_decode($data['datosGuiaRemosion'], true);
        $datosTransporteGuiaRemosion = json_decode($data['datosTransporteGuiaRemosion'], true);
        $sql = "SELECT * FROM ventas WHERE id_venta = '{$_POST['data']['idVenta']}'";
        $result = $this->conexion->query($sql)->fetch_assoc();

        /*  return json_encode($result['id_venta']);
        return; */
        /*      return json_encode($data);
        return; */
        /*   $c_guia->setFecha(filter_input(INPUT_POST, 'fecha_emision')); 
        $c_guia->setIdVenta(filter_input(INPUT_POST, 'venta'));
           $c_guia->setDirLlegada(filter_input(INPUT_POST, 'dir_cli'));
             $c_guia->setUbigeo(filter_input(INPUT_POST, 'ubigeo'));
              $c_guia->setTipoTransporte(filter_input(INPUT_POST, 'tipo_trans'));
               $c_guia->setRucTransporte(filter_input(INPUT_POST, 'ruc'));
      */
        $c_guia->setFecha($datosGuiaRemosion['fecha_emision']);
        $c_guia->setIdVenta($result['id_venta']);
        $c_guia->setDirLlegada($datosGuiaRemosion['dir_cli']);
        $c_guia->setUbigeo($data['datosUbigeoGuiaRemosion']);
        $c_guia->setTipoTransporte($datosTransporteGuiaRemosion['tipo_trans']);
        $c_guia->setRucTransporte($datosTransporteGuiaRemosion['ruc']);
        $c_guia->setRazTransporte($datosTransporteGuiaRemosion['razon_social']);
        $c_guia->setVehiculo($datosTransporteGuiaRemosion['veiculo']);
        $c_guia->setChofer($datosTransporteGuiaRemosion['chofer_dni']);


        $c_guia->setPeso($datosGuiaRemosion['peso']);
        $c_guia->setNroBultos($datosGuiaRemosion['num_bultos']);

        $c_guia->setIdEmpresa($_SESSION['id_empresa']);

        $c_documentos->setIdTido(11);
        $c_documentos->setIdEmpresa($c_guia->getIdEmpresa());
        $c_documentos->obtenerDatos();

        $c_guia->setSerie($c_documentos->getSerie());
        $c_guia->setNumero($c_documentos->getNumero());

        $dataSend['peso'] = $c_guia->getPeso();
        $dataSend['ubigeo'] = $c_guia->getUbigeo();
        $dataSend['direccion'] = $c_guia->getDirLlegada();
        $dataSend['serie'] = $c_guia->getSerie();
        $dataSend['numero'] = $c_guia->getNumero();
        $dataSend['fecha'] = $c_guia->getFecha();

        // $c_guia->obtenerId();
        $resultado = ["res" => false];
        if ($c_guia->insertar()) {
            //echo "xsssss";

            $resultado["res"] = true;
            $resultado["guia"] = $c_guia->getIdGuia();
            $listaProd = json_decode($data['listaPro'], true);
            $guiaDetalle->setIdGuia($c_guia->getIdGuia());

            $dataSend['productos'] = [];
            foreach ($listaProd as $prodG) {
                $guiaDetalle->setCantidad($prodG['cantidad']);
                $guiaDetalle->setDetalles($prodG['descripcion']);
                $guiaDetalle->setIdProducto($prodG['productoid']);
                $guiaDetalle->setPrecio($prodG['precio']);
                $guiaDetalle->setUnidad("NIU");
                $guiaDetalle->insertar();
                $dataSend['productos'][] = [
                    'cantidad' => $prodG['cantidad'],
                    'cod_pro' => $prodG['productoid'],
                    'cod_sunat' => "000",
                    'descripcion' => $prodG['descripcion']
                ];
            }

            $dataSend['productos'] = json_encode($dataSend['productos']);

            $sql = "SELECT * from empresas where id_empresa = " . $_SESSION['id_empresa'];
            $respEmpre = $c_guia->exeSQL($sql)->fetch_assoc();

            $dataSend["endpoints"] = $respEmpre['modo'];

            $dataSend['empresa'] = json_encode([
                'ruc' => $respEmpre['ruc'],
                'razon_social' => $respEmpre['razon_social'],
                'direccion' => $respEmpre['direccion'],
                'ubigeo' => $respEmpre['ubigeo'],
                'distrito' => $respEmpre['distrito'],
                'provincia' => $respEmpre['provincia'],
                'departamento' => $respEmpre['departamento'],
                'clave_sol' => $respEmpre['clave_sol'],
                'usuario_sol' => $respEmpre['user_sol']
            ]);

            $dataSend['venta'] = json_encode([
                'serie' => $result['serie'],
                'numero' => $result['numero']
            ]);
            $dataSend['cliente'] = json_encode([
                'doc_num' => $data['num_doc'],
                'nom_RS' => $data['nom_cli']
            ]);
            $dataSend['transporte'] = json_encode([
                'ruc' => $datosTransporteGuiaRemosion['ruc'],
                'razon_social' => $datosTransporteGuiaRemosion['razon_social'],
                'placa' => $datosTransporteGuiaRemosion['veiculo'],
                'doc_chofer' => $datosTransporteGuiaRemosion['chofer_dni']
            ]);

            $dataResp = $this->sunatApi->genGuiaRemision($dataSend);

            /*$respCURL =SendURL::SendGuiaRemision($dataSend);
            $respCURL = json_decode($respCURL,true);
            $dataResp= $respCURL["data"];

            $rutaFileXML="file/xml/".$respEmpre['ruc'];
            if (!file_exists($rutaFileXML)){
                mkdir($rutaFileXML, 0777, true);
            }

            $myfile = fopen($rutaFileXML.'/'.$dataResp['nombre_archivo'].".xml", "w");
            fwrite($myfile,$dataResp['consten_XML']);
            fclose($myfile);*/

            if ($dataResp["res"]) {
                $guiaSunat->setIdGuia($c_guia->getIdGuia());
                $guiaSunat->setHash($dataResp["data"]['hash']);
                $guiaSunat->setNombreXml($dataResp["data"]['nombre_archivo']);
                $guiaSunat->setQrData($dataResp["data"]['qr']);
                $guiaSunat->insertar();
            }
        }
        return json_encode($resultado);
    }
}
