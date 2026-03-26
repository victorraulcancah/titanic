<?php

require_once "utils/lib/exel/vendor/autoload.php";

class ProductosController extends Controller
{
    private $conexion;

    public function __construct()
    {
        $this->conexion= (new Conexion())->getConexion();
    }

    public function agregarPorLista(){
        $lista = json_decode($_POST['lista'],true);
        //var_dump($lista);
        $respuesta = ["res"=>true];
        foreach ($lista as $item){
            $afect = $item['afecto']?'1':'0';
            $sql="insert into productos set descripcion='{$item['descripcicon']}',
  precio='{$item['precio']}',
  costo='{$item['costo']}',
  cantidad='{$item['cantidad']}',
  iscbp='$afect',
  id_empresa='{$_SESSION['id_empresa']}',
  ultima_salida='1000-01-01',
  codsunat='{$item['codSunat']}'";

            if (!$this->conexion->query($sql)){
                $respuesta["res"]=false;
            }
        }
        return json_encode($respuesta);
    }

    public function importarExel(){
        $respuesta = ["res"=>false];
        $filename = $_FILES['file']['name'];

        $path_parts = pathinfo($filename, PATHINFO_EXTENSION);
        $newName =Tools::getToken(80);
        /* Location */
        $loc_ruta="files/temp";
        if (!file_exists($loc_ruta)) {
            mkdir($loc_ruta, 0777, true);
        }
        $location = $loc_ruta."/" . $newName .'.'. $path_parts;
        if (move_uploaded_file($_FILES['file']['tmp_name'], $location)){
            $nombre_logo= $newName.".".$path_parts;

            $respuesta["res"]=true;
            $type = $path_parts;

            if ($type=="xlsx"){
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
            }elseif ($type=="xls"){
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
            }elseif ($type=="csv"){
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Csv();
            }

            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load("files/temp/".$nombre_logo);

            $schdeules = $spreadsheet->getActiveSheet()->toArray();
           // array_shift($schdeules);
            $respuesta["data"]=$schdeules;

            unlink($location);
            //return $schdeules;
        }

        return json_encode($respuesta);
    }

    public function restock(){
        $respuesta = ["res"=>false];
        $sql = "update productos set cantidad=cantidad+{$_POST['cantidad']} where id_producto='{$_POST['cod']}'";
        //echo $sql;
        if ($this->conexion->query($sql)){
            $respuesta["res"]=true;
        }
        return json_encode($respuesta);
    }

    public function informacion(){
        $respuesta = ["res"=>false];
        $sql = "SELECT * FROM productos where id_producto='{$_POST['cod']}'";
        if ($row = $this->conexion->query($sql)->fetch_assoc()){
            $respuesta["res"]=true;
            $respuesta["data"]=$row;
        }
        return json_encode($respuesta);
    }
    public function actualizar(){
        $respuesta = ["res"=>false];
        $sql="update productos set descripcion='{$_POST['descripcicon']}',
                     cod_barra='{$_POST['cod_prod']}',
                     usar_barra='{$_POST['usar_barra']}',
  precio='{$_POST['precio']}',
  costo='{$_POST['costo']}',
  iscbp='{$_POST['afecto']}',
  codsunat='{$_POST['codSunat']}' where id_producto='{$_POST['cod']}'";

        if ($this->conexion->query($sql)){
            $respuesta["res"]=true;
        }
        return json_encode($respuesta);
    }

    public function agregar(){
        $respuesta = ["res"=>false];
        $sql="insert into productos set descripcion='{$_POST['descripcicon']}',
  precio='{$_POST['precio']}',
  costo='{$_POST['costo']}',
  cantidad='{$_POST['cantidad']}',
  iscbp='{$_POST['afecto']}',
    sucursal='{$_SESSION['sucursal']}',
  id_empresa='{$_SESSION['id_empresa']}',
  ultima_salida='1000-01-01',
  codsunat='{$_POST['codSunat']}'";

        if ($this->conexion->query($sql)){
            $respuesta["res"]=true;
        }
        return json_encode($respuesta);
    }

}