<?php
require_once "app/models/Consultas.php";
require_once "app/models/Ubigeo.php";
require_once "app/models/Venta.php";
require_once "app/models/Cliente.php";
require_once "app/models/ProductoVenta.php";
require_once "app/models/DocumentoEmpresa.php";
require_once "app/clases/SunatApi.php";

require_once "app/clases/EnvioEmail.php";

class ConsultasController extends Controller
{
    private $consulta;
    private $sunatApi;

    public function __construct()
    {
        $this->consulta=new Consultas();
        $this->sunatApi=new SunatApi();
    }
    public function informacionVentaFb(){
        $venta = $_POST["venta"];


        $sql="select c.*,vs.nombre_xml from ventas v join clientes c on v.id_cliente = c.id_cliente join ventas_sunat vs on v.id_venta = vs.id_venta where v.id_venta = $venta";

        $datos = $this->consulta->exeSQL($sql)->fetch_assoc();

        return json_encode([
            "link"=>URL::to("/venta/comprobante/pdf/$venta/".$datos['nombre_xml']),
            "linkd"=>URL::to("/venta/comprobante/pdfd/$venta"),
            "file_name"=>$datos['nombre_xml'].'.pdf',
            "numero"=>$datos['telefono']?$datos['telefono']:'',
            "mail"=>$datos['email']?$datos['email']:'',
        ]);

    }

    public function enviarcomprobanteEmail(){
        $respuesta=["res"=>false];
        $empresa = $this->consulta->exeSQL("select * from empresas where id_empresa='{$_SESSION['id_empresa']}'")->fetch_assoc();

        $tock_temp = Tools::getToken(10);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $_POST['link']."/"
            .base64_encode("files/temp/".$tock_temp.".pdf"));
        $data = curl_exec($ch);
        curl_close($ch);

        ob_start();
        $sendEmail = (new EnvioEmail());
        $sendEmail->de(USER_SMTP,$empresa['razon_social'])
            ->addEmail($_POST['email'],'Cliente')
            ->setasunto("Comprobante Electronico")
            ->cuerpo("<h1>Comproante: {$_POST['nombrefile']}</h1>")
            ->addArchivo("files/temp/".$tock_temp.".pdf",$_POST['nombrefile']);

        if (file_exists("files/facturacion/xml/".$empresa['ruc'].'/'.basename($_POST['nombrefile'], ".pdf").".xml")) {
            $sendEmail->addArchivo("files/facturacion/xml/".$empresa['ruc'].'/'.basename($_POST['nombrefile'], ".pdf").".xml",basename($_POST['nombrefile'], ".pdf").".xml");
        }

        $resul = $sendEmail->enviar();

         ob_end_clean();

        if ($resul){
            unlink("files/temp/".$tock_temp.".pdf");
            $respuesta["res"]=true;
        }
        return json_encode($respuesta);

    }
    public function actualizarSucursal(){
        $respuesta=["res"=>false];
        $data = $_POST;
        if (strlen($data['clave'])>0){
            $sql = "update usuarios set 
  num_doc='{$data['documento']}',
  usuario='{$data['usuario']}',
  clave=sha1('{$data['clave']}'),
  email='{$data['email']}',
  nombres='{$data['nombres']}', 
  telefono='{$data['telefono']}' where usuario_id='{$data['usuarioid']}'";
            if ($this->consulta->exeSQL($sql)){
                $respuesta["res"]=true;
            }
        }else{
            $sql = "update usuarios set 
  num_doc='{$data['documento']}',
  usuario='{$data['usuario']}', 
  email='{$data['email']}',
  nombres='{$data['nombres']}', 
  telefono='{$data['telefono']}' where usuario_id='{$data['usuarioid']}' ";
            if ($this->consulta->exeSQL($sql)){
                $respuesta["res"]=true;
            }
        }

        $sql=" update documentos_empresas set serie='{$data['serieF']}',numero='{$data['numeroF']}' where id_empresa='{$data['empr']}' and id_tido=2 and sucursal='{$data['sucursal']}'";
        $this->consulta->exeSQL($sql);
        $sql=" update documentos_empresas set serie='{$data['serieB']}',numero='{$data['numeroB']}' where id_empresa='{$data['empr']}' and id_tido=1  and sucursal='{$data['sucursal']}'";
        $this->consulta->exeSQL($sql);
        $sql=" update documentos_empresas set serie='{$data['serieNV']}',numero='{$data['numeroNV']}' where  id_empresa='{$data['empr']}' and id_tido=6 and sucursal='{$data['sucursal']}'";
        $this->consulta->exeSQL($sql);
        $sql=" update documentos_empresas set serie='{$data['serieNC']}',numero='{$data['numeroNC']}' where id_empresa='{$data['empr']}' and id_tido=3 and sucursal='{$data['sucursal']}'";
        $this->consulta->exeSQL($sql);
        $sql=" update documentos_empresas set serie='{$data['serieND']}',numero='{$data['numeroND']}' where id_empresa='{$data['empr']}' and id_tido=4 and sucursal='{$data['sucursal']}'";
        $this->consulta->exeSQL($sql);
        $sql=" update documentos_empresas set serie='{$data['serieGR']}',numero='{$data['numeroGR']}' where id_empresa='{$data['empr']}' and id_tido=11 and sucursal='{$data['sucursal']}'";
        $this->consulta->exeSQL($sql);

        return json_encode($respuesta);
    }

    public function getInfoSucursal(){
        $dataR =[];
        $sql = "SELECT * from usuarios where usuario_id='{$_POST['user']}'";
        $user = $this->consulta->exeSQL($sql)->fetch_assoc();

        $sql="select * from  documentos_empresas where  id_empresa='{$user['id_empresa']}' and sucursal='{$user['sucursal']}'";
        $temResp = $this->consulta->exeSQL($sql);
        $user["docEmp"]=[];
        foreach ($temResp as $rowT){
            $user["docEmp"][]=$rowT;
        }
        return json_encode($user);
    }

    public function agregarSusucursal(){
        $respuesta =["res"=>false];

        $sql="select * from usuarios where id_empresa = '{$_POST['empr']}' order by  sucursal desc limit 1";
        $ultimoSuculsal = $this->consulta->exeSQL($sql)->fetch_assoc();

        $sigienteSucursal = $ultimoSuculsal['sucursal']+1;

        $sql = "insert into usuarios set id_empresa='{$_POST['empr']}',
  id_rol='2',
  num_doc='{$_POST['documento']}',
  usuario='{$_POST['usuario']}',
  clave=SHA1('{$_POST['clave']}'),
  email='{$_POST['email']}',
  nombres='{$_POST['nombres']}',
  apellidos='',
  rubro='{$ultimoSuculsal['rubro']}',
  sucursal='$sigienteSucursal',
  telefono='{$_POST['telefono']}',
  estado='1'";
        if ($this->consulta->exeSQLInsert($sql)) {
            $idUsuaio = $this->consulta->getUltimoId();
            $data =$_POST;
            $idEmpresa = $_POST['empr'];
            $sql=" insert into documentos_empresas set sucursal='$sigienteSucursal', id_empresa='$idEmpresa',id_tido=2,serie='{$data['serieF']}',numero='{$data['numeroF']}'";
            //echo $sql;
            $this->consulta->exeSQL($sql);
            $sql=" insert into documentos_empresas set sucursal='$sigienteSucursal',id_empresa='$idEmpresa',id_tido=1,serie='{$data['serieB']}',numero='{$data['numeroB']}'";
            $this->consulta->exeSQL($sql);
            $sql=" insert into documentos_empresas set sucursal='$sigienteSucursal',id_empresa='$idEmpresa',id_tido=6,serie='{$data['serieNV']}',numero='{$data['numeroNV']}'";
            $this->consulta->exeSQL($sql);
            $sql=" insert into documentos_empresas set sucursal='$sigienteSucursal',id_empresa='$idEmpresa',id_tido=3,serie='{$data['serieNC']}',numero='{$data['numeroNC']}'";
            $this->consulta->exeSQL($sql);
            $sql=" insert into documentos_empresas set sucursal='$sigienteSucursal',id_empresa='$idEmpresa',id_tido=4,serie='{$data['serieND']}',numero='{$data['numeroND']}'";
            $this->consulta->exeSQL($sql);

            $sql=" insert into documentos_empresas set sucursal='$sigienteSucursal',id_empresa='$idEmpresa',id_tido=11,serie='{$data['serieGR']}',numero='{$data['numeroGR']}'";
            $this->consulta->exeSQL($sql);
            $respuesta["res"]=true;
        }
        return json_encode($respuesta);
    }

    public function listasucursaleEmpresa(){
        $lista = [];
        $sql = "SELECT * from usuarios where id_empresa='{$_POST['cod']}'";
        $result = $this->consulta->exeSQL($sql);
        foreach ($result as $R){
            $lista[] = $R;
        }
        return json_encode($lista);
    }

    public function verificadorToken(){
        $respuesta =["res"=>false];
        $save = $_POST['s'];
        $token = json_decode(Tools::decryptText($_POST['token']),true);
        if ($token){
            $respuesta["res"]=true;
            if ($save){
                $_SESSION = $token;
            }
        }
        return json_encode($respuesta);
    }

    public function enviarDocumentoSunatNE(){
        $sql = "select * from notas_electronicas_sunat where id_notas_electronicas = '{$_POST["cod"]}'";
        $resultado=["res"=>false];
        if ($row =$this->consulta->exeSQL($sql)->fetch_assoc()){
            if ($this->sunatApi->envioIndividualDocumentoV($row["nombre_xml"])){
                $sql="update notas_electronicas set  estado_sunat='1' where nota_id = '{$_POST["cod"]}'";
                $this->consulta->exeSQL($sql);
                $resultado['res']=true;
            }else{
                $resultado['msg']=$this->sunatApi->getMensaje();
            }
        }
        return json_encode($resultado);
    }

    public function guardarNotaElectronica(){
        $c_tido = new DocumentoEmpresa();

        $c_tido->setIdEmpresa($_SESSION['id_empresa']);
        $c_tido->setIdTido($_POST['tipo_docNE']);
        $c_tido->obtenerDatos();
        $serieE = $c_tido->getSerie();
        $numeroE = $c_tido->getNumero();

        $sql="insert into notas_electronicas set id_venta='{$_POST['ventacod']}',
  tido='{$_POST['tipo_docNE']}',
  fecha='{$_POST['fecha']}',
    id_empresa='{$_SESSION['id_empresa']}',
    sucursal='{$_SESSION['sucursal']}',
  serie='$serieE',
  numero='$numeroE',
  motivo='{$_POST['motivoNE']}',
  monto='{$_POST['total_NE']}',
  productos=?";
        $productos = $_POST['listaPro'];
        $stmt = $this->consulta->getConectar()->prepare($sql);
        $stmt->bind_param("s", $productos);
        $respuesta=["res"=>false];
        if ($stmt->execute()){

            $idNotaElectronica = $stmt->insert_id;

            $respuesta["res"]=true;

            $empresa = $this->consulta->exeSQL("select * from empresas where id_empresa='{$_SESSION['id_empresa']}'")->fetch_assoc();
            $dataSend=[];
            if($_POST['tipo_doc']=='1'){
                $dataSend['tip_doc_afectado']='03';
            }elseif($_POST['tipo_doc']=='2'){
                $dataSend['tip_doc_afectado']='01';
            }

            if ($_POST['tipo_docNE']=='3'){
                $dataSend['cod_notaE']='07';
            }else{
                $dataSend['cod_notaE']='08';
            }

            $sql = "SELECT * FROM motivo_documento where id_motivo = {$_POST['motivoNE']}";

            $motivoNEData = $this->consulta->exeSQL($sql)->fetch_assoc();


            $dataSend['productos']=[];
            $dataSend["certGlobal"]=false;
            $dataSend["endpoints"]=$empresa['modo'];

            $listaProd = json_decode($productos,true);

            foreach($listaProd as $prodd){
                $dataSend['productos'][]=[
                    "precio"=>$prodd['precio'],
                    "cantidad"=>$prodd['cantidad'],
                    "cod_pro"=>$prodd['productoid'],
                    "cod_sunat"=>"",
                    "descripcion"=>$prodd['descripcion']
                ];
            }

            $dataSend['cliente']=json_encode([
                'doc_num'=>$_POST['num_doc'],
                'nom_RS'=>$_POST['nom_cli'],
                'direccion'=>$_POST['dir_cli'],
            ]);

            $dataSend['total']=$_POST['total_NE'];
            $dataSend['serie']=$serieE;

            $dataSend['sn_afectado']=$_POST['serie'].'-'.$_POST['numero'];
            $dataSend['cod_motivo']=$motivoNEData['codigo'];
            $dataSend['des_motivo']=$motivoNEData['nombre'];//$_POST['motivodes'];
            $dataSend['numero']=$numeroE;
            $dataSend['fecha']=$_POST['fecha'];
            $dataSend['moneda']="PEN";
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

            /*$file = fopen("archivo.txt", "w");
            fwrite($file, json_encode($dataSend) );
            fclose($file);*/

            $dataSend['productos']=json_encode($dataSend['productos']);
            $dataResp = $this->sunatApi->genNotaElectronicaXML($dataSend);
            if ($dataResp["res"]){
                $sql="insert into notas_electronicas_sunat set 
id_notas_electronicas='$idNotaElectronica',
  hash='{$dataResp['data']['hash']}',
  nombre_xml='{$dataResp['data']['nombre_archivo']}',
  qr_data='{$dataResp['data']['qr']}'
";
                $this->consulta->exeSQL($sql);

                //$respuesta["err"]=$dataResp['data']["error"];
            }
        }
//echo" ccc";
        return json_encode($respuesta);
    }

    public function functionbuscarDocumentoVentasSN(){
        $respuesta=["res"=>false];
        $sql="select v.*,c.documento,c.datos from ventas v
                join clientes c on c.id_cliente = v.id_cliente
                        where v.serie='{$_POST['serie']}' 
                       and v.numero='{$_POST['numero']}' 
                       and v.id_tido='{$_POST['tidoc']}' and v.id_empresa='{$_SESSION['id_empresa']}' ";
        //echo $sql;
        $resul = $this->consulta->exeSQL($sql);
        if ($row = $resul->fetch_assoc()){
            $respuesta["res"]=true;
            $respuesta["data"]=$row;
        }

        return json_encode($respuesta);
    }

    public function buscarDataCliente(){

        $searchTerm = filter_input(INPUT_GET, 'term');

        $resultados = $this->consulta->buscarClientes($searchTerm,$_SESSION['id_empresa']);

        $array_resultado = array();
        foreach ($resultados as $value) {
            $fila = array();
            $fila['value'] = $value['documento'] . " | " . $value['datos'];
            $fila['codigo'] = $value['id_cliente'];
            $fila['documento'] = $value['documento'];
            $fila['direccion'] = $value['direccion'];
            $fila['datos'] = $value['datos'];
            array_push($array_resultado, $fila);
        }

        return json_encode($array_resultado);
    }

    public function buscarDocInfo(){
        //var_dump($_POST);
        if (strlen($_POST['doc'])==8){
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'http://magustechnologies.com:9091/consulta/dni/'.$_POST['doc']);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            $data = curl_exec($ch);
            curl_close($ch);
            echo $data;
        }else{
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'http://magustechnologies.com:9091/consulta/ruc2/'.$_POST['doc']);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            $data = curl_exec($ch);
            curl_close($ch);
            echo $data;
        }
    }

    public function  consultaRuc(){
        $ruc = $_POST['ruc'];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://magustechnologies.com/api/consulta/ruc/".$ruc);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'token: VK2BvcODHQtezAU3jZXkYLEifNVKpH8KDlbRn3VGzWqvP0YWfJtMQftu9QFKqcKPDB58WFMFNJT7NdN0UrB5NKTZU84TYKmsWHO1x0h4qZCQwlG53WS4lLrAnSn7I3NBPSfShjNXDfG8jFyY8fCU2kxj7jy4F31xrTboGAVZoWSskUphhKIA1oj8XsmetS7s5EkFo328'
        ));
        $datos = curl_exec($ch);
        curl_close($ch);
        var_dump($datos);
        return  '1111';
    }

    public function consultvfb(){
        $_SESSION['ventaproductos'] = array();

//obtener las variables
        $tido = filter_input(INPUT_POST, 'idtido');
        $serie = filter_input(INPUT_POST, 'serie');
        $numero = filter_input(INPUT_POST, 'numero');

//iniciar clases
        $c_venta = new Venta();
        $c_cliente = new Cliente();
        $c_detalle = new ProductoVenta();

//enviar datos para consultar detalle
        $c_venta->setIdTido($tido);
        $c_venta->setSerie($serie);
        $c_venta->setNumero($numero);
        $c_venta->validarVenta();

//iniciar array resultado
        $resultado = [];

//validar si existe venta
        if ($c_venta->getIdVenta() == null || $c_venta->getIdVenta() == "") {
            $resultado['res'] = false;
            $resultado['msg'] = "Documento no encontrado";
        } else {
            $c_venta->obtenerDatos();
            if ($c_venta->getSucursal()==$_SESSION["sucursal"]){
                $c_cliente->setIdCliente($c_venta->getIdCliente());
                $c_cliente->obtenerDatos();

                $c_detalle->setIdVenta($c_venta->getIdVenta());
                $a_detalle = $c_detalle->verFilas();

                $resultado["productos"]=[];
                foreach ($a_detalle as $row) {
                    $fila = array();
                    $fila['idproducto'] = $row['id_producto'];
                    $fila['descripcion'] = $row['descripcion'];
                    $fila['cantidad'] = $row['cantidad'];
                    $fila['precio'] = $row['precio'];
                    $fila['costo'] = $row['costo'];
                    $resultado["productos"][]=$fila;
                }

                //iniciar array resultado con valores reales
                $resultado['res'] = true;
                $resultado['idventa'] = $c_venta->getIdVenta();
                $resultado['total'] = $c_venta->getTotal();
                $resultado['doc_cliente'] = $c_cliente->getDocumento();
                $resultado['nom_cliente'] = $c_cliente->getDatos();
                $resultado['dir_cliente'] = $c_cliente->getDireccion();
            }else{
                $resultado['res'] = false;
                $resultado['msg'] = "El documento Ingresado Pertenece a otra sucursal";
            }

        }

        echo json_encode($resultado);
    }

    public function listarDistri(){
        $c_ubigeo = new Ubigeo();

        $c_ubigeo->setDepartamento(filter_input(INPUT_POST, 'departamento'));
        $c_ubigeo->setProvincia(filter_input(INPUT_POST, 'provincia'));

        echo $c_ubigeo->verDistritos();
    }
    public function listarProvincias(){
        $c_ubigeo = new Ubigeo();

        $c_ubigeo->setDepartamento(filter_input(INPUT_POST, 'departamento'));
        echo $c_ubigeo->verProvincias();
    }

    function buscarSNdoc(){
        //return json_encode($_REQUEST);
        return json_encode($this->consulta->buscarSNdoc($_SESSION['id_empresa'],$_REQUEST['doc']));
    }

    function buscarProducto(){
        $searchTerm = filter_input(INPUT_GET, 'term');
        $resultados =$this->consulta->buscarProducto($_SESSION['id_empresa'],$searchTerm);
        $array_resultado = array();
        foreach ($resultados as $value) {
            $fila = array();
            $fila['value'] = $value['descripcion'] . " | P.Venta S/ : " . $value['precio'] . " | Stock: " . $value['cantidad'] ;
            $fila['codigo'] = $value['id_producto'];
            $fila['descripcion'] = $value['descripcion'];
            $fila['precio'] = $value['precio'];
            $fila['cnt'] = $value['cantidad'];
            $fila['costo'] = $value['costo'];
            array_push($array_resultado, $fila);
        }
        return json_encode($array_resultado);
    }

}