<?php

require_once "utils/lib/exel/vendor/autoload.php";

require_once "app/models/Producto.php";
class ProductosController extends Controller
{
    private $conexion;
    private $c_producto;

    public function __construct()
    {
        $this->conexion = (new Conexion())->getConexion();

        /*   $c_producto->setIdEmpresa($_SESSION['id_empresa']); */
    }
    public function listaProductoServerSide(){
        require_once "app/clases/serverside.php";
        header('Pragma: no-cache');
        header('Cache-Control: no-store, no-cache, must-revalidate');
        $almacen = $_GET['almacenId'];
        $table_data = new TableData();
        if ($almacen=='1'){
            $table_data->get2("view_productos_1","id_producto",[
                "id_producto",
                "codigo",
                "descripcion",
                "codigo",
                "cantidad",
                "costo",
                "precio2",
                "ultima_salida",
                "razon_social",
                "id_producto",
                "id_producto",
                "id_producto",
                "id_producto",
                "activo",
                "id_producto",
            ]);
        }else{
            $table_data->get("view_productos_2","id_producto",[
                "id_producto",
                "codigo",
                "descripcion",
                "codigo",
                "cantidad",
                "costo",
                "precio2",
                "ultima_salida",
                "razon_social",
                "id_producto",
                "id_producto",
                "id_producto",
                "id_producto",
                "activo",
            ]);
        }

    }

    public function listaProducto()
    {
        $c_producto = new Producto();
        $c_producto->setIdEmpresa($_SESSION['id_empresa']);
        $a_productos = $c_producto->verFilas($_POST['almacenId']);
        /*    $metodosPago = $this->consulta->exeSQL($a_productos); */
        $lista = [];
        foreach ($a_productos as $rowT) {
            $lista[] = $rowT;
        }
        return json_encode($lista);
        /*   echo json_encode($data); */

        /*     echo json_encode($_POST); */
    }
    public function agregarPorLista()
    {
        $lista = json_decode($_POST['lista'], true);
        $respuesta = ["res" => false];
        foreach ($lista as $item) {
            $afect = $item['afecto'] ? '1' : '0';

            $descripcion = $item['descripcicon'];
            $codigoProd = $item['codigoProd'];

            $sqlProducto = "SELECT * FROM productos where codigo = '$codigoProd' ";
            $producto =  $this->conexion->query($sqlProducto)->fetch_assoc();
            if ($producto) {
                $updateProducto = "UPDATE productos set descripcion= '$descripcion',
                                            precio='{$item['precio']}',
                                            precio2='{$item['precio2']}',
                                            precio3='{$item['precio3']}',
                                            precio4='{$item['precio4']}',
                                            almacen='{$item['almacen']}',
                                            precio_unidad='{$item['precio_unidad']}',
                                            costo='{$item['costo']}',
                                            cantidad='{$item['cantidad']}',
                                            codsunat='{$item['codSunat']}'
                                    where 
                                    codigo='$codigoProd' ";
                $this->conexion->query($updateProducto);
                $respuesta["res"] = true;
            } else {
                $sql = "insert into productos set descripcion=?,
                precio='{$item['precio']}',
                precio2='{$item['precio2']}',
                precio3='{$item['precio3']}',
                precio4='{$item['precio4']}',
                almacen='{$item['almacen']}',
                precio_unidad='{$item['precio_unidad']}',
                costo='{$item['costo']}',
                cantidad='{$item['cantidad']}',
                iscbp='$afect',
                id_empresa='{$_SESSION['id_empresa']}',
                ultima_salida='1000-01-01',
                sucursal='{$_SESSION['sucursal']}',
                codsunat='{$item['codSunat']}',
                codigo=?";

                $stmt = $this->conexion->prepare($sql);
                $stmt->bind_param('ss', $descripcion, $codigoProd);
                /*   $stmt->bind_param('s', $codigoProd); */

                if ($stmt->execute()) {
                    $respuesta["res"] = true;
                }
            }
        }
        return json_encode($respuesta);
    }

    public function importarExel()
    {
        $respuesta = ["res" => false];
        $filename = $_FILES['file']['name'];

        $path_parts = pathinfo($filename, PATHINFO_EXTENSION);
        $newName = Tools::getToken(80);
        /* Location */
        $loc_ruta = "files/temp";
        if (!file_exists($loc_ruta)) {
            mkdir($loc_ruta, 0777, true);
        }
        $location = $loc_ruta . "/" . $newName . '.' . $path_parts;
        if (move_uploaded_file($_FILES['file']['tmp_name'], $location)) {
            $nombre_logo = $newName . "." . $path_parts;

            $respuesta["res"] = true;
            $type = $path_parts;

            if ($type == "xlsx") {
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
            } elseif ($type == "xls") {
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xls();
            } elseif ($type == "csv") {
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Csv();
            }

            $reader->setReadDataOnly(true);
            $spreadsheet = $reader->load("files/temp/" . $nombre_logo);

            $schdeules = $spreadsheet->getActiveSheet()->toArray();
            // array_shift($schdeules);
            $respuesta["data"] = $schdeules;

            unlink($location);
            //return $schdeules;
        }

        return json_encode($respuesta);
    }

    public function restock()
    {
        $respuesta = ["res" => false];
        $sql = "update productos set cantidad=cantidad+{$_POST['cantidad']} where id_producto='{$_POST['cod']}'";
        //echo $sql;
        if ($this->conexion->query($sql)) {
            $respuesta["res"] = true;
        }
        return json_encode($respuesta);
    }
    public function informacionPorCodigo()
    {
        $respuesta = ["res" => false];
        $sql = "SELECT * FROM productos where trim(codigo)='{$_POST['code']}' AND almacen = '{$_POST['almacen']}' and sucursal='{$_SESSION['sucursal']}'";

        if ($row = $this->conexion->query($sql)->fetch_assoc()) {
            $respuesta["res"] = true;
            $respuesta["data"] = $row;
        }
        return json_encode($respuesta);
    }
    public function informacion()
    {
        $respuesta = ["res" => false];
        $sql = "SELECT * FROM productos where id_producto='{$_POST['cod']}'";
        if ($row = $this->conexion->query($sql)->fetch_assoc()) {
            $respuesta["res"] = true;
            $respuesta["data"] = $row;
        }
        return json_encode($respuesta);
    }
    public function agregar()
    {
        $respuesta = ["res" => false];
        $descripcion = $_POST['descripcicon'];
        $codigoProd = $_POST['codigo'];
        for ($i=1; $i < 3; $i++) { 
            $sql = "insert into productos set descripcion=?,
            precio='{$_POST['precio']}',
            costo='{$_POST['costo']}',
            medida='{$_POST['medida']}',
            almacen='{$i}',
            cantidad='{$_POST['cantidad']}',
            cnt_presenta='{$_POST['cnt_medida']}',
            iscbp='{$_POST['afecto']}',
              sucursal='{$_SESSION['sucursal']}',
            id_empresa='{$_SESSION['id_empresa']}',
            ultima_salida='1000-01-01',
            codsunat='{$_POST['codSunat']}',
            presentaciones='{$_POST['presentaciones']}',
            precio_mayor={$_POST['precioMayor']},precio_menor={$_POST['precioMenor']},razon_social='{$_POST['razon']}',ruc='{$_POST['ruc']}',codigo=?
            ";
          
                  $stmt = $this->conexion->prepare($sql);
                  $stmt->bind_param('ss', $descripcion, $codigoProd);
                  /*   $stmt->bind_param('s', $codigoProd); */
          
                  if ($stmt->execute()) {
                      $respuesta["res"] = true;
                  }
        }
      
        return json_encode($respuesta);
    }
    public function actualizar()
    {
        $respuesta = ["res" => false];
        $descripcion = $_POST['descripcicon'];
        $codigoProd = $_POST['codigo'];

        $sql="select * from productos where id_producto='{$_POST['cod']}'";
        $result = $this->conexion->query($sql);
        if ($row= $result->fetch_assoc()){
            //$almacenTemp = $row["almacen"]=="1"?2:1;
            $almacenTemp = $row["almacen"]=="1";
            $sql = "update productos set descripcion=?,
                     cod_barra='',
                     usar_barra='{$_POST['usar_barra']}',
                  precio='{$_POST['precio']}',
                  costo='{$_POST['costo']}',
                  medida='{$_POST['medida']}',
                  iscbp='{$_POST['afecto']}',
                  presentaciones='{$_POST['presentaciones']}',
                  cnt_presenta='{$_POST['cnt_medida']}',
                  codsunat='{$_POST['codSunat']}',precio_mayor={$_POST['precioMayor']},precio_menor={$_POST['precioMenor']},razon_social='{$_POST['razon']}',ruc='{$_POST['ruc']}',
                  codigo=?
                  where descripcion=? and almacen='$almacenTemp'";
           // var_dump($row);
            $stmt = $this->conexion->prepare($sql);
            $stmt->bind_param('sss', $descripcion, $codigoProd,$row['descripcion']);
            /*   $stmt->bind_param('s', $codigoProd); */

            $result= $stmt->execute();
            if(!$result){
                var_dump($stmt->error);
            }

        }

        /*   $sql = "insert into productos set descripcion=?, */
        $sql = "update productos set descripcion=?,
                     cod_barra='',
                     usar_barra='{$_POST['usar_barra']}',
  precio='{$_POST['precio']}',
  costo='{$_POST['costo']}',
  iscbp='{$_POST['afecto']}',
  cantidad='{$_POST['cantidad']}',
  codsunat='{$_POST['codSunat']}',precio_mayor={$_POST['precioMayor']},precio_menor={$_POST['precioMenor']},razon_social='{$_POST['razon']}',ruc='{$_POST['ruc']}',
  codigo=?
  where id_producto='{$_POST['cod']}'";

        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param('ss', $descripcion, $codigoProd);
        /*   $stmt->bind_param('s', $codigoProd); */

        if ($stmt->execute()) {
            $respuesta["res"] = true;


        }
        return json_encode($respuesta);
    }

    public function actualizarPrecios()
    {
        $respuesta = ["res" => false];
        $sql = "update productos set precio='{$_POST['precio']}',precio_unidad='{$_POST['precio_unidad']}', precio2='{$_POST['precio2']}', precio3='{$_POST['precio3']}', precio4='{$_POST['precio4']}' where id_producto='{$_POST['cod_prod']}'";
        if ($this->conexion->query($sql)) {
            $respuesta["res"] = true;
            $sql="select * from productos where id_producto='{$_POST['cod_prod']}'";
            $result = $this->conexion->query($sql);
            if ($row= $result->fetch_assoc()){
                $almacenTemp = $row["almacen"]=="1"?2:1;
                $sql = "update productos set 
                     precio='{$_POST['precio']}',precio_unidad='{$_POST['precio_unidad']}', 
                     precio2='{$_POST['precio2']}', precio3='{$_POST['precio3']}', 
                     precio4='{$_POST['precio4']}'
                  where descripcion=? and almacen='$almacenTemp'";
                $stmt = $this->conexion->prepare($sql);
                $stmt->bind_param('s', $row['descripcion']);
                /*   $stmt->bind_param('s', $codigoProd); */

                if(!$stmt->execute()){
                }


            }
        }
        return json_encode($respuesta);
    }
    public function confirmarTraslado()
    {
        $respuesta['res'] = false;
        $sql = "SELECT id_producto,almacen_ingreso,almacen_egreso,cantidad FROM ingreso_egreso WHERE intercambio_id ='{$_POST['cod']}'";
        $result = $this->conexion->query($sql)->fetch_assoc();

        $almacen = $result['almacen_ingreso'];
        $id_producto = $result['id_producto'];
        $cantidad = $result['cantidad'];

        $sql = "SELECT * FROM productos WHERE id_producto = '{$result['id_producto']}'";
        $result = $this->conexion->query($sql)->fetch_assoc();


        $sql = "SELECT * FROM productos WHERE descripcion = '{$result['descripcion']}' AND almacen = '$almacen'";
        $result2 = $this->conexion->query($sql)->fetch_assoc();


        if (is_null($result2)) {
            $sql = "INSERT INTO productos 
            (cod_barra, descripcion, precio, costo,cantidad,iscbp,id_empresa,sucursal,ultima_salida,codsunat,usar_barra,precio_mayor,precio_menor,razon_social,ruc,estado,almacen,precio2,precio3)
            SELECT cod_barra, descripcion, precio, costo,$cantidad,iscbp,id_empresa,sucursal,ultima_salida,codsunat,usar_barra,precio_mayor,precio_menor,razon_social,ruc,estado, $almacen,precio2,precio3
            FROM productos
            WHERE id_producto = $id_producto";
            if ($this->conexion->query($sql)) {
                $sql = "UPDATE productos set cantidad = cantidad - $cantidad   WHERE id_producto = $id_producto";
                if ($this->conexion->query($sql)) {
                    $respuesta['res'] = true;
                }
            }
        } else {
            $idExistente = $result2['id_producto'];
            $sql2 = "UPDATE  productos set cantidad =  cantidad - $cantidad  WHERE id_producto = $id_producto";
            if ($this->conexion->query($sql2)) {
                $sql = "UPDATE  productos set cantidad = cantidad + $cantidad   WHERE id_producto = $idExistente";
                if ($this->conexion->query($sql)) {
                    $respuesta['res'] = true;
                }
            }
        }
        if ($respuesta['res']) {
            $sql = "UPDATE  ingreso_egreso set estado = 1   WHERE intercambio_id = '{$_POST['cod']}'";
            if ($this->conexion->query($sql)) {
                $respuesta['res'] = true;
            }
        }
        echo json_encode($respuesta);
    }

    public function delete()
    {
        $respuesta["res"] = true;
        $respuesta["data"] = $_POST;
        $sql = '';
        foreach ($respuesta["data"]['arrayId'] as $ids) {
            /*   $sql .= $ids; */

            $sql = "UPDATE   productos set estado=0 where id_producto = '{$ids['id']}'";
            if ($this->conexion->query($sql)) {
                $respuesta["res"] = true;
            }
        }
        return json_encode($respuesta);
    }
    
    public function activar()
    {
        $idProducto = $_POST["idProducto"];
        $valorChecked = $_POST["valorChecked"]; # si es true, entonces se desactiva, false se activa
        $respuesta["res"] = false;
        $activo = ($valorChecked==='true') ? 0 : 1;
        $sql = '';
        $sql = "UPDATE productos set activo='{$activo}' where id_producto = '{$idProducto}'";
        if ($this->conexion->query($sql)) {
            $respuesta["res"] = true;
        }
        return json_encode($respuesta);
    }
    
}
