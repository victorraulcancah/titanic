<?php

class CajaVendedorController extends Controller
{
    private $conexion;

    public function __construct()
    {
        $this->conexion = (new Conexion())->getConexion();
    }

    // Abrir caja del vendedor
    public function abrirCaja()
    {
        $respuesta = ["res" => false];
        
        $id_usuario = isset($_SESSION['usuario_id']) ? $_SESSION['usuario_id'] : $_SESSION['usuario_fac'];
        $detalle = $_POST['detalle'];
        $monto = $_POST['monto'];
        $hora = $_POST['hora'];
        $fecha_hora_actual = date('Y-m-d H:i:s');
        $fecha = date('Y-m-d');
        
        // Verificar si ya existe una caja abierta hoy para este vendedor
        $sql = "SELECT * FROM caja_empresa 
                WHERE DATE(fecha)='$fecha' 
                AND estado='1' 
                AND id_usuario='$id_usuario'
                AND id_empresa='{$_SESSION['id_empresa']}'
                AND sucursal='{$_SESSION['sucursal']}'";
        $result = $this->conexion->query($sql);
        
        if ($result->num_rows > 0) {
            $respuesta["mensaje"] = "Ya tienes una caja abierta hoy";
            return json_encode($respuesta);
        }
        
        // Insertar la caja con fecha y hora actual
        $sql = "INSERT INTO caja_empresa SET 
                id_empresa='{$_SESSION['id_empresa']}',
                sucursal='{$_SESSION['sucursal']}',
                id_usuario='$id_usuario',
                detalle='$detalle',
                fecha='$fecha_hora_actual',
                entrada='0',
                salida='0',
                estado='1'";
        
        if ($this->conexion->query($sql)) {
            $respuesta["res"] = true;
            $caja_id = $this->conexion->insert_id;
            
            $sql = "INSERT INTO caja_chica SET 
                    id_caja_empresa='$caja_id',
                    hora='$hora',
                    detalle='Apertura de caja',
                    tipo='a',
                    entrada='$monto',
                    salida=0,
                    metodo=1";
            $this->conexion->query($sql);
        }
        
        return json_encode($respuesta);
    }

    // Registrar gasto
    public function registrarGasto()
    {
        $respuesta = ["res" => false];
        $sql = '';
        
        if ($_POST['tipo'] == '1') {
            $sql = "INSERT INTO caja_chica SET id_caja_empresa='{$_POST['caja']}',
                    hora='{$_POST['hora']}',
                    detalle='{$_POST['detalle']}',
                    salida='{$_POST['monto']}',
                    metodo='{$_POST['metodo']}',
                    entrada=0";
        } else {
            $sql = "INSERT INTO caja_chica SET id_caja_empresa='{$_POST['caja']}',
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

    // Obtener movimientos de la caja
    public function obtenerMovimientos()
    {
        $listaTotal = [];
        $sql = "SELECT * FROM caja_chica WHERE id_caja_empresa='{$_POST['cod']}' ORDER BY caja_chica_id ASC";
        
        foreach ($this->conexion->query($sql) as $row) {
            $listaTotal[] = [
                'detalle' => $row['detalle'],
                'salida' => $row['salida'],
                'entrada' => $row['entrada'],
                'hora' => $row['hora'],
                'metodo' => $row['metodo']
            ];
        }
        
        return json_encode($listaTotal);
    }

    // Cerrar caja
    public function cerrarCaja()
    {
        $respuesta = ["res" => false];
        
        $sql = "UPDATE caja_empresa SET estado='0',
                entrada='{$_POST['ingreso']}', 
                salida='{$_POST['egreso']}' 
                WHERE caja_id='{$_POST['caja']}'";
        
        if ($this->conexion->query($sql)) {
            $respuesta["res"] = true;
        }
        
        return json_encode($respuesta);
    }
    
    // Obtener historial de cajas del vendedor
    public function obtenerHistorial()
    {
        $id_usuario = isset($_SESSION['usuario_id']) ? $_SESSION['usuario_id'] : $_SESSION['usuario_fac'];
        
        $sql = "SELECT 
                ce.caja_id,
                ce.detalle,
                ce.fecha,
                ce.estado,
                COALESCE(SUM(cc.entrada), 0) as entrada,
                COALESCE(SUM(cc.salida), 0) as salida
                FROM caja_empresa ce
                LEFT JOIN caja_chica cc ON ce.caja_id = cc.id_caja_empresa
                WHERE ce.id_usuario='$id_usuario'
                AND ce.id_empresa='{$_SESSION['id_empresa']}'
                AND ce.sucursal='{$_SESSION['sucursal']}'
                GROUP BY ce.caja_id
                ORDER BY ce.fecha DESC
                LIMIT 30";
        
        $result = $this->conexion->query($sql);
        $historial = mysqli_fetch_all($result, MYSQLI_ASSOC);
        
        return json_encode($historial);
    }
}
