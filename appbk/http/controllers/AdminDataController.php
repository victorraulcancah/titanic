<?php

class AdminDataController extends Controller
{
    private $conexion;

    public function __construct()
    {
        $this->conexion=(new Conexion())->getConexion();
    }

    public function guardarEstado(){
        $resultado=["res"=>false];
        $sql = "update usuarios set estado='{$_POST['estado']}', mensaje='{$_POST['mensaje']}' where usuario_id='{$_POST['cod']}'";
        if ($this->conexion->query($sql)){
            $resultado['res']=true;
        }
        return json_encode($resultado);
    }

    public function infoCliemt(){
        $resultado=["res"=>false];
        $sql="select * from usuarios u
join empresas e on e.id_empresa = u.id_empresa
where u.usuario_id = '{$_POST['usr']}'";
        if ($row = $this->conexion->query($sql)->fetch_assoc()){

            $sql="select * from  documentos_empresas where  id_empresa='{$row['id_empresa']}' and sucursal='{$_SESSION['sucursal']}'";
            $temResp = $this->conexion->query($sql);
            $row["docEmp"]=[];
            foreach ($temResp as $rowT){
                $row["docEmp"][]=$rowT;
            }

            $resultado['res']=true;
            $resultado['data']=$row;
        }
        return json_encode($resultado);
    }

    public function actualizarCliente(){

        $data = json_decode($_POST['data'],true);

        $nombre_logo=$data['logo'];
        $nombre_certi="";

        if (isset($_FILES['file1'])){
            $filename = $_FILES['file1']['name'];

            $path_parts = pathinfo($filename, PATHINFO_EXTENSION);
            $newName =Tools::getToken(80);
            /* Location */
            $loc_ruta="files/logos";
            if (!file_exists($loc_ruta)) {
                mkdir($loc_ruta, 0777, true);
            }
            $location = $loc_ruta."/" . $newName .'.'. $path_parts;
            if (move_uploaded_file($_FILES['file1']['tmp_name'], $location)){
                $nombre_logo= $newName.".".$path_parts;
            }
        }
        if (isset($_FILES['file2'])){
            $filename = $_FILES['file2']['name'];

            $path_parts = pathinfo($filename, PATHINFO_EXTENSION);
            $newName =$data['ruc'].'-cert';
            /* Location */
            $loc_ruta="files/facturacion/certificados";
            if (!file_exists($loc_ruta)) {
                mkdir($loc_ruta, 0777, true);
            }
            $location = $loc_ruta."/" . $newName .'.'. $path_parts;
            if (move_uploaded_file($_FILES['file2']['tmp_name'], $location)){
                $nombre_certi= $newName.".".$path_parts;
            }
        }

        $respuesta=["res"=>false];

        $sql = "update empresas set 
  ruc='{$data['ruc']}',
  razon_social='{$data['razon']}',
  comercial='{$data['razon']}',
  direccion='{$data['direccion']}',
  email='{$data['email']}',
  telefono='{$data['telefono']}',
  user_sol='{$data['usuario_sol']}',
  clave_sol='{$data['clave_sol']}',
  modo='{$data['modo']}',
  logo='$nombre_logo',
  ubigeo='{$data['ubigeo']}',
  distrito='{$data['distrito']}',
  provincia='{$data['provincia']}',
  departamento='{$data['departamento']}' where id_empresa='{$data['codemp']}'";

        //echo  $sql;
        if ($this->conexion->query($sql)){

            if (strlen($data['clave'])>0){
                $sql = "update usuarios set 
  num_doc='{$data['documento']}',
  usuario='{$data['usuario']}',
  clave=sha1('{$data['clave']}'),
  email='{$data['email']}',
  nombres='{$data['nombres']}', 
  rubro='{$data['rubro']}', 
  telefono='{$data['telefono']}' where usuario_id='{$data['codus']}'";
                if ($this->conexion->query($sql)){
                    $respuesta["res"]=true;
                }
            }else{
                $sql = "update usuarios set 
  num_doc='{$data['documento']}',
  usuario='{$data['usuario']}', 
  email='{$data['email']}',
  nombres='{$data['nombres']}', 
  rubro='{$data['rubro']}', 
  telefono='{$data['telefono']}' where usuario_id='{$data['codus']}' ";
                if ($this->conexion->query($sql)){
                    $respuesta["res"]=true;
                }
            }

            $sql=" update documentos_empresas set serie='{$data['serieF']}',numero='{$data['numeroF']}' where id_empresa='{$data['codemp']}' and id_tido=2 and sucursal='1'";
            $this->conexion->query($sql);
            $sql=" update documentos_empresas set serie='{$data['serieB']}',numero='{$data['numeroB']}' where id_empresa='{$data['codemp']}' and id_tido=1  and sucursal='1'";
            $this->conexion->query($sql);
            $sql=" update documentos_empresas set serie='{$data['serieNV']}',numero='{$data['numeroNV']}' where  id_empresa='{$data['codemp']}' and id_tido=6 and sucursal='1'";
            $this->conexion->query($sql);
            $sql=" update documentos_empresas set serie='{$data['serieNC']}',numero='{$data['numeroNC']}' where id_empresa='{$data['codemp']}' and id_tido=3 and sucursal='1'";
            $this->conexion->query($sql);
            $sql=" update documentos_empresas set serie='{$data['serieND']}',numero='{$data['numeroND']}' where id_empresa='{$data['codemp']}' and id_tido=4 and sucursal='1'";
            $this->conexion->query($sql);


        }

        return json_encode($respuesta);

    }
    public function agregarCliente(){

        $data = json_decode($_POST['data'],true);

        $nombre_logo="";
        $nombre_certi="";

        $sql="select * from empresas where ruc = '{$data['ruc']}'";
        $respuesta=["res"=>false,"msg"=>''];
        if($roww = $this->conexion->query($sql)->fetch_assoc() ){
            $respuesta["msg"]="Esta empresa ya fue registrada";
        }else{
            if (isset($_FILES['file1'])){
                $filename = $_FILES['file1']['name'];

                $path_parts = pathinfo($filename, PATHINFO_EXTENSION);
                $newName =Tools::getToken(80);
                /* Location */
                $loc_ruta="files/logos";
                if (!file_exists($loc_ruta)) {
                    mkdir($loc_ruta, 0777, true);
                }
                $location = $loc_ruta."/" . $newName .'.'. $path_parts;
                if (move_uploaded_file($_FILES['file1']['tmp_name'], $location)){
                    $nombre_logo= $newName.".".$path_parts;
                }
            }
            if (isset($_FILES['file2'])){
                $filename = $_FILES['file2']['name'];

                $path_parts = pathinfo($filename, PATHINFO_EXTENSION);
                $newName =$data['ruc'].'-cert';
                /* Location */
                $loc_ruta="files/facturacion/certificados";
                if (!file_exists($loc_ruta)) {
                    mkdir($loc_ruta, 0777, true);
                }
                $location = $loc_ruta."/" . $newName .'.'. $path_parts;
                if (move_uploaded_file($_FILES['file2']['tmp_name'], $location)){
                    $nombre_certi= $newName.".".$path_parts;
                }
            }



            $sql = "insert into empresas set 
  ruc='{$data['ruc']}',
  razon_social='{$data['razon']}',
  comercial='{$data['razon']}',
  direccion='{$data['direccion']}',
  email='{$data['email']}',
  telefono='{$data['telefono']}',
  estado='1',
  user_sol='{$data['usuario_sol']}',
  clave_sol='{$data['clave_sol']}',
  logo='$nombre_logo',
  ubigeo='{$data['ubigeo']}',
  distrito='{$data['distrito']}',
  modo='{$data['modo']}',
  provincia='{$data['provincia']}',
  departamento='{$data['departamento']}'";

            //echo  $sql;
            if ($this->conexion->query($sql)){
                $respuesta["msg"]="No se pudo agregar";
                $idEmpresa = $this->conexion->insert_id;

                $sql = "insert into usuarios set 
  id_empresa='$idEmpresa',
  id_rol='2',
  num_doc='{$data['documento']}',
  usuario='{$data['usuario']}',
  clave=sha1('{$data['clave']}'),
  email='{$data['email']}',
  nombres='{$data['nombres']}', 
  rubro='{$data['rubro']}',
    estado='1',
  telefono='{$data['telefono']}'";





                if ($this->conexion->query($sql)){

                    $sql=" insert into documentos_empresas set id_empresa='$idEmpresa',id_tido=2,serie='{$data['serieF']}',numero='{$data['numeroF']}', sucursal='1'";
                    //echo $sql;
                    $this->conexion->query($sql);
                    $sql=" insert into documentos_empresas set id_empresa='$idEmpresa',id_tido=1,serie='{$data['serieB']}',numero='{$data['numeroB']}', sucursal='1'";
                    $this->conexion->query($sql);
                    $sql=" insert into documentos_empresas set id_empresa='$idEmpresa',id_tido=6,serie='{$data['serieNV']}',numero='{$data['numeroNV']}', sucursal='1'";
                    $this->conexion->query($sql);
                    $sql=" insert into documentos_empresas set id_empresa='$idEmpresa',id_tido=3,serie='{$data['serieNC']}',numero='{$data['numeroNC']}', sucursal='1'";
                    $this->conexion->query($sql);
                    $sql=" insert into documentos_empresas set id_empresa='$idEmpresa',id_tido=4,serie='{$data['serieND']}',numero='{$data['numeroND']}', sucursal='1'";
                    $this->conexion->query($sql);
                    $sql=" insert into documentos_empresas set id_empresa='$idEmpresa',id_tido=11,serie='{$data['serieGR']}',numero='{$data['numeroGR']}', sucursal='1'";
                    $this->conexion->query($sql);

                    $respuesta["msg"]="";
                    $respuesta["res"]=true;
                }
            }
        }



        return json_encode($respuesta);

    }

}