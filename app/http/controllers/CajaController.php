<?php

class CajaController extends Controller
{
    private $conexion;

    public function __construct()
    {
        $this->conexion = (new Conexion())->getConexion();
    }

    public function cerrarCajaChica()
    {
        $respuesta = ["res" => false];
        $sql = "update caja_empresa set estado ='0',
         entrada='{$_POST['ingreso']}', salida='{$_POST['egreso']}' where caja_id='{$_POST['caja']}'";
        if ($this->conexion->query($sql)) {
            $respuesta["res"] = true;
        }
        return json_encode($respuesta);
    }

    public function agregarMovimiento()
    {
        $respuesta = ["res" => false];
        $sql = '';
        if ($_POST['tipo'] == '1') {
            $sql = "insert into caja_chica set id_caja_empresa='{$_POST['caja']}',
              hora='{$_POST['hora']}',
              detalle='{$_POST['detalle']}',
              salida='{$_POST['monto']}',
              metodo='{$_POST['metodo']}',
              entrada=0";
        } else {
            $sql = "insert into caja_chica set id_caja_empresa='{$_POST['caja']}',
              hora='{$_POST['hora']}',
              detalle='{$_POST['detalle']}',
              salida=0,
              metodo='{$_POST['metodo']}',
              entrada='{$_POST['monto']}'";
        }

        if ($this->conexion->query($sql)) {
            $respuesta["res"] = true;
        }
        return json_encode($respuesta);
    }

    public function listar()
    {
        $listaTotal = [];
        $sql = "select * from caja_chica where id_caja_empresa ='{$_POST['cod']}'";
        $lista = [];
        foreach ($this->conexion->query($sql)  as $row) {
          /*   $lista[] = $row; */
            $listaTotal[] = ['detalle' => $row['detalle'], 'salida' => $row['salida'], 'entrada' => $row['entrada'], 'hora' => $row['hora'], 'metodo' => $row['metodo']];
        }

        $dateHoy = date('Y-m-d');

        $sql = "SELECT v.id_venta, v.fecha_emision, CONCAT( ds.abreviatura , ' | ' , v.serie , ' - ', v.numero) AS detalle, 
        v.total AS entrada FROM ventas AS v
                 LEFT JOIN documentos_sunat ds ON v.id_tido = ds.id_tido
                 LEFT JOIN ventas_sunat vs ON v.id_venta = vs.id_venta
             WHERE v.id_empresa = '{$_SESSION['id_empresa']}' AND v.sucursal='{$_SESSION['sucursal']}'  AND v.medoto_pago_id = '10' AND v.fecha_emision ='$dateHoy'
             ORDER BY v.fecha_emision ASC, v.numero ASC";

       /*  $lista2 = []; */
        foreach ($this->conexion->query($sql)  as $row2) {
            $listaTotal[] = ['detalle' => $row2['detalle'], 'salida' => 0, 'entrada' => $row2['entrada'], 'hora' => '-'];
        }

       /*  $arrayMerge = array_merge($lista, $lista2); */
        return json_encode($listaTotal);
    }

    public function aperturarCaja()
    {
        $respuesta = ["res" => false];
        $sql = "insert into caja_empresa set id_empresa='{$_SESSION['id_empresa']}',
  sucursal='{$_SESSION['sucursal']}',
  detalle='{$_POST['detalle']}',
  fecha=NOW(),
  entrada='',
  salida=''";
        if ($this->conexion->query($sql)) {
            $respuesta["res"] = true;
            $caja_id = $this->conexion->insert_id;
            $sql = "insert into caja_chica set id_caja_empresa='$caja_id',
              hora='{$_POST['hora']}',
              detalle='Apertura de caja',
              tipo='a',
              entrada='{$_POST['monto']}',
              salida=0, metodo = 1";
            $this->conexion->query($sql);
        }

        return json_encode($respuesta);
    }
}
