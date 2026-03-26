<?php

class CajaController extends Controller
{
    private $conexion;

    public function __construct()
    {
        $this->conexion = (new Conexion())->getConexion();
    }

    public function cerrarCajaChica(){
        $respuesta=["res"=>false];
        $sql = "update caja_empresa set estado ='0',
         entrada='{$_POST['ingreso']}', salida='{$_POST['egreso']}' where caja_id='{$_POST['caja']}'";
        if ($this->conexion->query($sql)){
            $respuesta["res"]=true;
        }
        return json_encode($respuesta);
    }

    public function agregarMovimiento(){
        $respuesta=["res"=>false];
        $sql='';
        if ($_POST['tipo']=='1'){
            $sql="insert into caja_chica set id_caja_empresa='{$_POST['caja']}',
              hora='{$_POST['hora']}',
              detalle='{$_POST['detalle']}',
              salida='{$_POST['monto']}',
              entrada=0";
        }else{
            $sql="insert into caja_chica set id_caja_empresa='{$_POST['caja']}',
              hora='{$_POST['hora']}',
              detalle='{$_POST['detalle']}',
              salida=0,
              entrada='{$_POST['monto']}'";
        }

        if ($this->conexion->query($sql)){
            $respuesta["res"]=true;
        }
        return json_encode($respuesta);
    }

    public function listar(){
        $sql="select * from caja_chica where id_caja_empresa ='{$_POST['cod']}'";
        $lista=[];
        foreach ($this->conexion->query($sql)  as $row){
            $lista[] = $row;
        }
        return json_encode($lista);
    }

    public function aperturarCaja(){
        $respuesta=["res"=>false];
        $sql = "insert into caja_empresa set id_empresa='{$_SESSION['id_empresa']}',
  sucursal='{$_SESSION['sucursal']}',
  detalle='{$_POST['detalle']}',
  fecha=NOW(),
  entrada='',
  salida=''";
        if ($this->conexion->query($sql)){
            $respuesta["res"]=true;
            $caja_id = $this->conexion->insert_id;
            $sql="insert into caja_chica set id_caja_empresa='$caja_id',
              hora='{$_POST['hora']}',
              detalle='Apertura de caja',
              tipo='a',
              entrada='{$_POST['monto']}',
              salida=0";
            $this->conexion->query($sql);

        }

        return json_encode($respuesta);

    }

}