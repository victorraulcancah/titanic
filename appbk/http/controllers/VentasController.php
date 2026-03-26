<?php

require_once "app/models/Venta.php";
require_once "app/models/Cliente.php";
require_once "app/models/DocumentoEmpresa.php";
require_once "app/models/ProductoVenta.php";
require_once "app/models/VentaServicio.php";
require_once "app/models/Varios.php";
require_once "app/models/VentaSunat.php";
require_once "app/models/VentaAnulada.php";
require_once "app/clases/SendURL.php";
require_once "app/clases/SunatApi.php";


class VentasController extends Controller
{
    private $venta;
    private $sunatApi;

    public function __construct()
    {
        $this->venta = new Venta();
        $this->sunatApi=new SunatApi();
    }

    public function envioComunicacionBajaPorEmpresa(){
        $listaBoletas = [];
        foreach (json_decode($_POST['boletas'],true) as  $bol){
            $listaBoletas[]="v.id_venta='$bol'";
        }

        $sql="select v.id_venta, v.enviado_sunat,vs.nombre_xml from ventas v
        join ventas_sunat vs on v.id_venta = vs.id_venta
        where ".implode(" OR ",$listaBoletas);

        $listaPorEnviar = $this->venta->exeSQL($sql);

        foreach ($listaPorEnviar as $vpr){
            if ($vpr['enviado_sunat']=='0'){
                if ($this->sunatApi->envioIndividualDocumentoVPorEmpresa($vpr['nombre_xml'],$_POST['empresa'])){
                    $sql="update ventas set enviado_sunat='1' where id_venta='{$vpr['id_venta']}'";
                    $this->venta->exeSQL($sql);

                }
                sleep(2);
            }

        }
        $respuesta=[];
        $respuesta['msg_resumen']=$this->sunatApi->comunicacionBajaPorEmpresa($listaBoletas,$_POST['empresa'],
            $_POST['fecharesumen'],$_POST["fechagen"],$_POST['correlativo1']);

        return json_encode($respuesta);
    }

    public function envioResumenDiarioPorEmpresa(){
        $listaBoletas = [];
        foreach (json_decode($_POST['boletas'],true) as  $bol){
            $listaBoletas[]="v.id_venta='$bol'";
        }
        return json_encode([
            $this->sunatApi->resumenDiarioPorEmpresa($listaBoletas,$_POST['empresa'],
                $_POST['fechagen'],$_POST['fecharesumen'],$_POST['correlativo1']),
        $this->sunatApi->resumenDiarioBajaPorEmpresa($listaBoletas,$_POST['empresa'],
            $_POST['fechagen'],$_POST['fecharesumen'],$_POST['correlativo2'])
        ]);



    }

    public function enviarDocumentoSunatPorEmpresa(){
        $sql = "select vs.*,v.id_empresa from ventas_sunat vs
        join ventas v on v.id_venta = vs.id_venta
        where vs.id_venta = '{$_POST["cod"]}'";
        $resultado=["res"=>false];
        if ($row =$this->venta->exeSQL($sql)->fetch_assoc()){
            if ($this->sunatApi->envioIndividualDocumentoVPorEmpresa($row["nombre_xml"],$row['id_empresa'])){
                $sql="update ventas set  enviado_sunat='1'
                where id_venta = '{$_POST["cod"]}'";
                $this->venta->exeSQL($sql);
                $resultado['res']=true;
            }else{
                $resultado['msg']=$this->sunatApi->getMensaje();
            }
        }
        return json_encode($resultado);
    }

    public function regenerarXML(){
        $venta= $_POST["venta"];

        $sql = "SELECT * from ventas where id_venta='$venta'";
        $ventaData = $this->venta->exeSQL($sql)->fetch_assoc();
        $empresa = $this->venta->exeSQL("select * from empresas where id_empresa='{$ventaData['id_empresa']}'")->fetch_assoc();
        $cliente = $this->venta->exeSQL("select * from clientes where id_cliente='{$ventaData['id_cliente']}'")->fetch_assoc();


        $dataSend=[];
        $dataSend["certGlobal"]=false;

        $direccionselk =$cliente["direccion"];



        if (strlen(trim($direccionselk)) == "") {
            $direccionselk ='-';
        }
        if (trim($cliente["datos"]) == "") {
            $cliente["datos"]='-';
        }

        $dataSend['cliente']=json_encode([
            'doc_num'=>$cliente["documento"],
            'nom_RS'=>$cliente["datos"],
            'direccion'=>$direccionselk
        ]);
        $dataSend['productos']=[];

        $dataSend['total']=$ventaData["total"];
        $dataSend['serie']=$ventaData["serie"];
        $dataSend['numero']=$ventaData["numero"];
        $dataSend['fechaE']=$ventaData["fecha_emision"];
        $dataSend['fechaV']=$ventaData["fecha_vencimiento"];
        $dataSend['tipo_pago']=$ventaData["id_tipo_pago"];
        $dataSend['dias_pagos']=[];
        $dataSend['moneda']="PEN";

        $sql="select * from dias_ventas where id_venta='$venta'";
        $cuotasVentas = $this->venta->exeSQL($sql);

        foreach($cuotasVentas as $cuotas){
            $dataSend['dias_pagos'][]=[
                "monto"=>$cuotas['monto'],
                "fecha"=>$cuotas['fecha']
            ];
        }

        $sql="select pv.*,p.descripcion from productos_ventas pv
        join productos p on p.id_producto = pv.id_producto
        where pv.id_venta='$venta'";
        $listaProductos = $this->venta->exeSQL($sql);
        foreach($listaProductos as $prod){
            $dataSend['productos'][]=[
                "precio"=>number_format($prod['precio'],2, ".", ""),
                "cantidad"=>number_format($prod['cantidad'],0),
                "cod_pro"=>$prod['id_producto'],
                "cod_sunat"=>"",
                "descripcion"=>$prod['descripcion']
            ];
        }

        $sql="select * from ventas_servicios where  id_venta='$venta'";
        $listaProductos = $this->venta->exeSQL($sql);
        foreach($listaProductos as $prod){
            $dataSend['productos'][]=[
                "precio"=>number_format($prod['monto'],2, ".", ""),
                "cantidad"=>number_format($prod['cantidad'],0),
                "cod_pro"=>$prod['id_item'],
                "cod_sunat"=>$prod['codsunat'],
                "descripcion"=>$prod['descripcion']
            ];
        }

        $dataSend["endpoints"]=$empresa['modo'];

        $dataSend['empresa']=json_encode([
            'ruc'=>$empresa['ruc'],
            'razon_social'=>$empresa['razon_social'],
            'direccion'=>$empresa['direccion'],
            'ubigeo'=>$empresa['ubigeo'],
            'distrito'=>$empresa['distrito'],
            'provincia'=>$empresa['provincia'],
            'departamento'=>$empresa['departamento'],
            'clave_sol'=>$empresa['clave_sol'],
            'usuario_sol'=>$empresa['user_sol']
        ]);
        $respuesta = ["res"=>false];

        if ($ventaData['id_tido'] == 1 || $ventaData['id_tido'] == 2) {
            $dataSend['dias_pagos'] =json_encode( $dataSend['dias_pagos']);

            $dataSend['productos']=json_encode($dataSend['productos']);
            if ($ventaData['id_tido'] == 1){
                $dataResp =$this->sunatApi->genBoletaXML($dataSend);
            }else{
                $dataResp =$this->sunatApi->genFacturaXML($dataSend);
            }
            if ($dataResp["res"]){
                $respuesta["res"]=true;
                $sql = "select * from ventas_sunat where id_venta = '$venta'";
                if ($rrroooo = $this->venta->exeSQL($sql)->fetch_assoc()){
                    $sql="update ventas_sunat set hash='{$dataResp['data']['hash']}',
                      nombre_xml='{$dataResp['data']['nombre_archivo']}',
                      qr_data='{$dataResp['data']['qr']}' where id_venta = '$venta' ";
                    $this->venta->exeSQL($sql);
                }else{
                    $sql="insert into ventas_sunat set hash='{$dataResp['data']['hash']}',
                      nombre_xml='{$dataResp['data']['nombre_archivo']}',
                      qr_data='{$dataResp['data']['qr']}',  id_venta = '$venta' ";
                    $this->venta->exeSQL($sql);
                }
            }
        }

        return json_encode($respuesta);

    }

    public function listaVentasPorEmpresa(){
        return json_encode($this->venta->verFilasPorEmpresas($_POST["empresa"],$_POST["sucursal"]));
    }


    public function enviarDocumentoSunat(){
        $sql = "select * from ventas_sunat where id_venta = '{$_POST["cod"]}'";
        $resultado=["res"=>false];
        if ($row =$this->venta->exeSQL($sql)->fetch_assoc()){
            if ($this->sunatApi->envioIndividualDocumentoV($row["nombre_xml"])){
                $sql="update ventas set  enviado_sunat='1' where id_venta = '{$_POST["cod"]}'";
                $this->venta->exeSQL($sql);
                $resultado['res']=true;
            }else{
                $resultado['msg']=$this->sunatApi->getMensaje();
            }
        }
        return json_encode($resultado);
    }

    public function anularVenta(){
        $this->venta->setIdVenta($_POST['iventa']);
        $c_anulada = new VentaAnulada();
        $c_producto = new ProductoVenta();

        $c_producto->setIdVenta($this->venta->getIdVenta());
        $c_producto->eliminar();

        $c_anulada->setIdVenta($this->venta->getIdVenta());
        $c_anulada->setFecha(date("Y-m-d"));
        $c_anulada->setMotivo("-");
        $resultado=["res"=>false];
        if ($this->venta->anular()) {
            $resultado['res']=true;
            $c_anulada->insertar();
        }
        return json_encode($resultado);
    }

    public function listarVentas(){
        $this->venta->setIdEmpresa($_SESSION['id_empresa']);
        $lista = $this->venta->verFilas("202202");
        return json_encode($lista);
    }
    public function detalleVenta(){
        //echo $_POST['iventa'];
        $this->venta->setIdVenta($_POST['iventa']);
        return $this->venta->verDetalle();
    }
    public function detalleVenta2(){
        //echo $_POST['iventa'];
        $this->venta->setIdVenta($_POST['iventa']);
        return $this->venta->verDetalle2();
    }
    public function guardarVentas(){


        $resultado=["res"=>false];


        $dataSend=[];
        $dataSend["certGlobal"]=false;


        $c_cliente = new Cliente();
        $c_venta = new Venta();
        $c_tido = new DocumentoEmpresa();
        $c_detalle = new ProductoVenta();
        $c_servicio = new VentaServicio();
       // $c_curl = new SendCurlVenta();
        $c_sunat = new VentaSunat();
        $c_varios = new Varios();




        $id_empresa = $_SESSION['id_empresa'];
        $c_cliente->setIdEmpresa($id_empresa);
        $c_cliente->setDocumento(filter_input(INPUT_POST, 'num_doc'));
        $c_cliente->setDatos(filter_input(INPUT_POST, 'nom_cli'));
        $c_cliente->setDireccion(filter_input(INPUT_POST, 'dir_cli'));
        $c_cliente->setDireccion2(filter_input(INPUT_POST, 'dir2_cli'));


        if ($c_cliente->getDocumento() == "") {
            $c_cliente->setDocumento("SD" . $c_varios->generarCodigo(5));
            $c_cliente->insertar();
        } else {
            if (!$c_cliente->verificarDocumento()) {
                $c_cliente->insertar();
            }
        }

        $resultado["email"]=$c_cliente->getEmail()?$c_cliente->getEmail():'';
        $resultado["cel"]=$c_cliente->getTelefono()?$c_cliente->getTelefono():'';

        $direccionselk ='';
        if ($_POST['dir_pos']==1){
            $direccionselk =$_POST['dir_cli'];
        }elseif($_POST['dir_pos']==2){
            $direccionselk =$_POST['dir2_cli'];
        }

        if (trim($c_cliente->getDocumento()) == "") {
            $c_cliente->setDocumento('');
        }
        if (strlen(trim($direccionselk)) == "") {
            $direccionselk ='-';
        }
        if (trim($c_cliente->getDatos()) == "") {
            $c_cliente->setDatos('-');
        }

        $dataSend['cliente']=json_encode([
            'doc_num'=>$c_cliente->getDocumento(),
            'nom_RS'=>$c_cliente->getDatos(),
            'direccion'=>$direccionselk
        ]);
        $c_venta->setDireccion($direccionselk);
        $dataSend['productos']=[];
        $c_tido->setIdEmpresa($id_empresa);
        $c_tido->setIdTido(filter_input(INPUT_POST, 'tipo_doc'));
        $c_tido->obtenerDatos();
        $c_venta->setIdEmpresa($id_empresa);
        $c_venta->setFecha($_POST['fecha']);
        $c_venta->setFechaVenc($_POST['tipo_pago']=='1'?$_POST['fecha']:$_POST['fechaVen']);
        $c_venta->setDiasPagos($_POST['dias_pago']);
        $c_venta->setIdTipoPago($_POST['tipo_pago']);
        $c_venta->setIdTido($c_tido->getIdTido());
        $c_venta->setSerie($c_tido->getSerie());
        $c_venta->setNumero($c_tido->getNumero());
        $c_venta->setObservaciones($_POST['observacion']);

        $c_venta->setIdCliente($c_cliente->getIdCliente());
        $c_venta->setTotal(filter_input(INPUT_POST, 'total'));
        $tipoventa = filter_input(INPUT_POST, 'tipoventa');


        $dataSend['total']=$c_venta->getTotal();
        $dataSend['serie']=$c_tido->getSerie();
        $dataSend['numero']=$c_tido->getNumero();
        $dataSend['fechaE']=$c_venta->getFecha();
        $dataSend['fechaV']=$c_venta->getFechaVenc();
        $dataSend['tipo_pago']=$c_venta->getIdTipoPago();
        $dataSend['dias_pagos']=[];
        $dataSend['moneda']="PEN";

        $listaPagos = json_decode($_POST['dias_lista'],true);

        if ($c_venta->insertar()) {

            $resultado["res"]=true;
            $array_detalle = json_decode($_POST['listaPro'], true);
            foreach ($listaPagos as $diaP){
                $sql="insert into dias_ventas set id_venta='{$c_venta->getIdVenta()}',
                    monto='{$diaP['monto']}',fecha='{$diaP['fecha']}',estado='0'";
                $c_venta->exeSQL($sql);
                $dataSend['dias_pagos'][]=[
                    "monto"=>$diaP['monto'],
                    "fecha"=>$diaP['fecha']
                ];
            }
            $dataSend['dias_pagos'] =json_encode( $dataSend['dias_pagos']);

            if ($tipoventa == 1) {
                $c_detalle->setIdVenta($c_venta->getIdVenta());
                foreach ($array_detalle as $fila) {
                    $c_detalle->setIdProducto($fila['productoid']);
                    $c_detalle->setCantidad($fila['cantidad']);
                    $c_detalle->setCosto($fila['costo']);
                    $c_detalle->setPrecio($fila['precio']);
                    $c_detalle->insertar();
                    $dataSend['productos'][]=[
                        "precio"=>$fila['precio'],
                        "cantidad"=>$fila['cantidad'],
                        "cod_pro"=>$fila['productoid'],
                        "cod_sunat"=>"",
                        "descripcion"=>$fila['nom_prod']
                    ];
                }
            }

            if ($tipoventa == 2) {
                $nroitem = 1;
                $c_servicio->setIdventa($c_venta->getIdVenta());
                foreach ($array_detalle as $fila) {
                    $c_servicio->setDescripcion($fila['descripcion']);
                    $c_servicio->setCantidad($fila['cantidad']);
                    $c_servicio->setMonto($fila['precio']);
                    $c_servicio->setCodsunat(isset($fila['codsunat'])?$fila['codsunat']:'');
                    $c_servicio->setIditem($nroitem);
                    $c_servicio->insertar();
                    $nroitem++;
                    $dataSend['productos'][]=[
                        "precio"=>$fila['precio'],
                        "cantidad"=>$fila['cantidad'],
                        "cod_pro"=>$nroitem,
                        "cod_sunat"=>isset($fila['codsunat'])?$fila['codsunat']:'',
                        "descripcion"=>$fila['descripcion']
                    ];
                }
            }


            //definir url segun el tipo de documento sunat
            if ($c_venta->getIdTido() == 1) {
                $archivo = "boleta";
            }
            if ($c_venta->getIdTido() == 2) {
                $archivo = "factura";
            }

            if ($c_venta->getIdTido() == 1 || $c_venta->getIdTido() == 2) {
                $sql = "SELECT * from empresas where id_empresa = ".$id_empresa;

                $respEmpre=$c_venta->exeSQL($sql)->fetch_assoc();

                $dataSend["endpoints"]=$respEmpre['modo'];

                $dataSend['empresa']=json_encode([
                    'ruc'=>$respEmpre['ruc'],
                    'razon_social'=>$respEmpre['razon_social'],
                    'direccion'=>$respEmpre['direccion'],
                    'ubigeo'=>$respEmpre['ubigeo'],
                    'distrito'=>$respEmpre['distrito'],
                    'provincia'=>$respEmpre['provincia'],
                    'departamento'=>$respEmpre['departamento'],
                    'clave_sol'=>$respEmpre['clave_sol'],
                    'usuario_sol'=>$respEmpre['user_sol']
                ]);

                /*$file = fopen("archivo.txt", "w");

                fwrite($file, json_encode($dataSend) );


                fclose($file);*/

                $dataSend['productos']=json_encode($dataSend['productos']);

                /*$resultado["data"] ="curl";
                $respCURL =SendURL::SendComprobante($dataSend);
                 json_decode($respCURL,true);
                = $respCURL["data"];

                $rutaFileXML="file/xml/".$respEmpre['ruc'];
                if (!file_exists($rutaFileXML)){
                    mkdir($rutaFileXML, 0777, true);
                }

                $myfile = fopen($rutaFileXML.'/'.$dataResp['nombre_archivo'].".xml", "w");
                fwrite($myfile,$dataResp['consten_XML']);
                fclose($myfile);

*/
                if ($c_venta->getIdTido() == 1){
                    $dataResp =$this->sunatApi->genBoletaXML($dataSend);
                }else{
                    $dataResp =$this->sunatApi->genFacturaXML($dataSend);
                }



                if ($dataResp["res"]){
                    $c_sunat->setIdVenta($c_venta->getIdVenta());
                    $c_sunat->setHash($dataResp['data']['hash']);
                    $c_sunat->setNombreXml($dataResp['data']['nombre_archivo']);
                    $c_sunat->setQrData($dataResp['data']['qr']);
                    $c_sunat->insertar();
                }else{

                }

            }
            else {
                $c_sunat->setIdVenta($c_venta->getIdVenta());
                $c_sunat->setHash("-");
                $c_sunat->setNombreXml("-");
                $c_sunat->setQrData('-');
                $c_sunat->insertar();

                $resultado["valor"] = $c_venta->getIdVenta();

            }
            $resultado["nomFact"] = $c_sunat->getNombreXml().".pdf";
            $resultado["urlFact"]=URL::to('/venta/comprobante/pdf/'.$c_sunat->getIdVenta().'/'.$c_sunat->getNombreXml());
            $resultado["urlFactd"]=URL::to('/venta/comprobante/pdfd/'.$c_sunat->getIdVenta().'/'.$c_sunat->getNombreXml());
        }
        return json_encode($resultado);

    }

}