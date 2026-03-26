<?php

class CobrosVendedorController extends Controller
{
    private $conexion;

    public function __construct()
    {
        $this->conexion = (new Conexion())->getConexion();
    }

    // Obtener cobros por rango de fechas del vendedor
    public function obtenerCobrosPorRango()
    {
        $id_usuario = isset($_SESSION['usuario_id']) ? $_SESSION['usuario_id'] : $_SESSION['usuario_fac'];
        $fecha_inicio = $_POST['fecha_inicio'];
        $fecha_fin = $_POST['fecha_fin'];
        
        $cobros = [];
        
        // Cobros de ventas
        $sql_ventas = "SELECT 
                        dv.dias_venta_id,
                        dv.monto,
                        dv.tipo_pago,
                        dv.fecha_pago_real,
                        CONCAT(v.serie, '-', v.numero) as documento,
                        c.datos as cliente,
                        'Venta' as tipo_documento
                    FROM dias_ventas dv
                    INNER JOIN ventas v ON dv.id_venta = v.id_venta
                    INNER JOIN clientes c ON v.id_cliente = c.id_cliente
                    WHERE dv.id_usuario = '$id_usuario'
                    AND dv.estado = '1'
                    AND DATE(dv.fecha_pago_real) BETWEEN '$fecha_inicio' AND '$fecha_fin'
                    AND v.id_empresa = '{$_SESSION['id_empresa']}'
                    AND v.sucursal = '{$_SESSION['sucursal']}'
                    ORDER BY dv.fecha_pago_real DESC";
        
        $result_ventas = $this->conexion->query($sql_ventas);
        while ($row = $result_ventas->fetch_assoc()) {
            $cobros[] = $row;
        }
        
        // Cobros de cotizaciones
        $sql_cotizaciones = "SELECT 
                                cc.cuota_coti_id as dias_venta_id,
                                cc.monto,
                                cc.tipo_pago,
                                cc.fecha_pago_real,
                                CONCAT('COT-', cot.numero) as documento,
                                c.datos as cliente,
                                'Cotización' as tipo_documento
                            FROM cuotas_cotizacion cc
                            INNER JOIN cotizaciones cot ON cc.id_coti = cot.cotizacion_id
                            INNER JOIN clientes c ON cot.id_cliente = c.id_cliente
                            WHERE cc.id_usuario = '$id_usuario'
                            AND cc.estado = '1'
                            AND DATE(cc.fecha_pago_real) BETWEEN '$fecha_inicio' AND '$fecha_fin'
                            AND cot.id_empresa = '{$_SESSION['id_empresa']}'
                            AND cot.sucursal = '{$_SESSION['sucursal']}'
                            ORDER BY cc.fecha_pago_real DESC";
        
        $result_cotizaciones = $this->conexion->query($sql_cotizaciones);
        while ($row = $result_cotizaciones->fetch_assoc()) {
            $cobros[] = $row;
        }
        
        // Ordenar todos los cobros por fecha
        usort($cobros, function($a, $b) {
            return strtotime($b['fecha_pago_real']) - strtotime($a['fecha_pago_real']);
        });
        
        return json_encode($cobros);
    }
}
