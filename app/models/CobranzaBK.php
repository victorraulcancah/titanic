
<?php


class Cobranza
{
    public function __construct()
    {
        $this->conectar = (new Conexion())->getConexion();
    }
    public function getAllCobranzas()
{
    try {
        if ($_SESSION["rol"] == 4) {
            $sql = "SELECT 
                'v' AS tipo_co,
                v.id_venta,
                CONCAT(v.serie, ' | ', v.numero) AS factura,
                MAX(v.fecha_emision) AS fecha_emision,
                MAX(v.fecha_vencimiento) AS fecha_vencimiento,
                CONCAT(c.documento, ' | ', c.datos) AS cliente,
                c.mercado AS mercado,
                MAX(v.total) AS total,
                '' AS vendedor,
                SUM(CASE WHEN dv.estado = '1' THEN dv.monto ELSE 0 END) AS pagado,
                (MAX(v.total) - SUM(CASE WHEN dv.estado = '1' THEN dv.monto ELSE 0 END)) AS saldo
            FROM ventas AS v
            INNER JOIN dias_ventas AS dv ON v.id_venta = dv.id_venta 
            INNER JOIN clientes AS c ON v.id_cliente = c.id_cliente
            WHERE v.estado = 1 
              AND v.id_tipo_pago = 2  
              AND v.id_empresa = '{$_SESSION['id_empresa']}'
            GROUP BY v.id_venta, c.datos, c.mercado
            ORDER BY c.mercado ASC, c.datos ASC";
        } else {
            $sql = "SELECT 
                'v' AS tipo_co,
                v.id_venta,
                CONCAT(v.serie, ' | ', v.numero) AS factura,
                MAX(v.fecha_emision) AS fecha_emision,
                MAX(v.fecha_vencimiento) AS fecha_vencimiento,
                CONCAT(c.documento, ' | ', c.datos) AS cliente,
                c.mercado AS mercado,
                MAX(v.total) AS total,
                '' AS vendedor,
                SUM(CASE WHEN dv.estado = '1' THEN dv.monto ELSE 0 END) AS pagado,
                (MAX(v.total) - SUM(CASE WHEN dv.estado = '1' THEN dv.monto ELSE 0 END)) AS saldo
            FROM ventas AS v
            INNER JOIN dias_ventas AS dv ON v.id_venta = dv.id_venta 
            INNER JOIN clientes AS c ON v.id_cliente = c.id_cliente
            WHERE v.estado = 1 
              AND v.id_tipo_pago = 2 
              AND v.sucursal = '{$_SESSION['sucursal']}'
              AND v.id_empresa = '{$_SESSION['id_empresa']}'
            GROUP BY v.id_venta, c.datos, c.mercado
            ORDER BY c.mercado ASC, c.datos ASC";
        }

        $fila = mysqli_query($this->conectar, $sql);
        $lista = mysqli_fetch_all($fila, MYSQLI_ASSOC);

        // Consulta de cotizaciones
        $sql = "SELECT 
            'c' AS tipo_co,
            co.cotizacion_id AS id_venta,
            CONCAT('#', co.numero) AS factura,
            if(us.usuario_id is null,'Usuario Eliminado',us.usuario) AS vendedor,
            co.fecha AS fecha_emision,
            (
                SELECT fecha 
                FROM cuotas_cotizacion cc 
                WHERE cc.id_coti = co.cotizacion_id  
                ORDER BY fecha DESC 
                LIMIT 1
            ) AS fecha_vencimiento,
            CONCAT(c.documento, ' | ', c.datos) AS cliente,
            c.mercado AS mercado,
            co.total,
            (
                SELECT IFNULL(SUM(cc.monto), 0) 
                FROM cuotas_cotizacion cc 
                WHERE cc.id_coti = co.cotizacion_id AND cc.estado = 1
            ) AS pagado,
            (co.total - (
                SELECT IFNULL(SUM(cc.monto), 0) 
                FROM cuotas_cotizacion cc 
                WHERE cc.id_coti = co.cotizacion_id AND cc.estado = 1
            )) AS saldo
        FROM cotizaciones co
        INNER JOIN clientes AS c ON c.id_cliente = co.id_cliente
        LEFT JOIN usuarios us ON us.usuario_id = co.id_usuario
        WHERE co.id_tipo_pago = 2 AND co.estado!=2
        ORDER BY c.mercado ASC, c.datos ASC";

        $fila = mysqli_query($this->conectar, $sql);
        $lista2 = mysqli_fetch_all($fila, MYSQLI_ASSOC);

        return array_merge($lista2, $lista);
    } catch (Exception $e) {
        echo $e->getTraceAsString();
    }
}
 public function getAllDeudas()
    {
        try {
            $sql = "SELECT 
                        tb.cliente as cliente,
                        SUM(tb.total) AS total,
                        SUM(tb.pagado) AS pagado,
                        SUM(tb.total - tb.pagado) AS saldo
            FROM (SELECT 
                'c' AS tipo_co,
                co.cotizacion_id AS id_venta,
                CONCAT('#',co.numero) AS factura,
                us.usuario vendedor, 
                co.fecha AS fecha_emision,
                (SELECT  fecha FROM cuotas_cotizacion cc WHERE cc.id_coti = co.cotizacion_id  ORDER BY fecha DESC LIMIT 1)   AS fecha_vencimiento,
                CONCAT(c.documento ,' | ' ,c.datos) AS cliente,
                co.total,
                (SELECT IFNULL( SUM(cc.monto),0) FROM cuotas_cotizacion cc WHERE cc.id_coti = co.cotizacion_id AND cc.estado=1) pagado

                FROM cotizaciones co

                INNER JOIN clientes AS c ON c.id_cliente=co.id_cliente
                JOIN usuarios us ON us.usuario_id = co.id_usuario
                WHERE co.id_tipo_pago=2 AND co.estado!=2) tb GROUP BY tb.cliente;";

            $fila = mysqli_query($this->conectar, $sql);
            $lista2 =  mysqli_fetch_all($fila, MYSQLI_ASSOC);

            return array_merge($lista2, array());
        } catch (Exception $e) {
            echo $e->getTraceAsString();
        }
    }
    
    public function getAllCobros($whereCliente,$whereVendedor,$fecha_inicio,$fecha_fin,$whereClientes,$whereDiasVisita,$whereRuta)
    {
        try {
            $sql = "
                SELECT 
                'c' AS tipo_co,
                co.cotizacion_id AS id_venta,
                CONCAT('#',co.numero) AS factura,
                us.usuario vendedor, 
                co.fecha AS fecha_emision,
                c.id_cliente,
                CONCAT(c.documento ,' | ' ,c.datos) AS cliente,
                co.total,
                cu.monto as pagado,
                IFNULL(cu.tipo_pago,'-') AS 'metodo_pago',
                IFNULL(c.dias_visitas,'') AS dias_visitas,IFNULL(c.id_ruta,'') AS id_ruta

                FROM cotizaciones co

                INNER JOIN clientes AS c ON c.id_cliente=co.id_cliente
                INNER JOIN cuotas_cotizacion cu ON cu.id_coti = co.cotizacion_id and cu.estado=1
                LEFT JOIN usuarios us ON us.usuario_id = cu.id_usuario
                WHERE co.id_tipo_pago=2 AND co.estado!=2
                AND cu.fecha BETWEEN '$fecha_inicio' AND '$fecha_fin' 
                $whereCliente
                $whereVendedor
                $whereClientes
                $whereDiasVisita
                $whereRuta
                ORDER BY CONCAT(c.documento ,' | ' ,c.datos) ASC,co.cotizacion_id DESC
            ";
            $fila = mysqli_query($this->conectar, $sql);
            $lista2 =  mysqli_fetch_all($fila, MYSQLI_ASSOC);

            return array_merge($lista2, array());
        } catch (Exception $e) {
            echo $e->getTraceAsString();
        }
    }
    #
    public function getAllCobrosByVendedor($whereCliente,$whereVendedor,$fecha_inicio,$fecha_fin,$whereClientes,$whereDiasVisita,$whereRuta)
    {
        try {
            $sql = "
                SELECT 
                'c' AS tipo_co,
                co.cotizacion_id AS id_venta,
                CONCAT('#',co.numero) AS factura,
                us.usuario vendedor, 
                co.fecha AS fecha_emision,
                cu.fecha AS fecha_pago,
                c.id_cliente,
                c.documento,
                c.datos AS nombre_cliente,
                CONCAT(c.documento ,' | ' ,c.datos) AS cliente,
                co.total,
                cu.monto as pagado,
                (
                select sum(cu1.monto) from cuotas_cotizacion cu1 where cu1.fecha<=cu.fecha and cu1.id_coti=cu.id_coti
                ) AS 'total_pagado',
                IFNULL(cu.tipo_pago,'-') AS 'metodo_pago',
                IFNULL(c.dias_visitas,'') AS dias_visitas,IFNULL(c.id_ruta,'') AS id_ruta

                FROM cotizaciones co

                INNER JOIN clientes AS c ON c.id_cliente=co.id_cliente
                INNER JOIN cuotas_cotizacion cu ON cu.id_coti = co.cotizacion_id and cu.estado=1
                LEFT JOIN usuarios us ON us.usuario_id = cu.id_usuario
                WHERE co.id_tipo_pago=2 AND co.estado!=2
                AND cu.fecha BETWEEN '$fecha_inicio' AND '$fecha_fin' 
                $whereCliente
                $whereVendedor
                $whereClientes
                $whereDiasVisita
                $whereRuta
                ORDER BY CONCAT(c.documento ,' | ' ,c.datos) ASC,co.cotizacion_id DESC
            ";
            $fila = mysqli_query($this->conectar, $sql);
            $lista2 =  mysqli_fetch_all($fila, MYSQLI_ASSOC);

            return array_merge($lista2, array());
        } catch (Exception $e) {
            echo $e->getTraceAsString();
        }
    }
    
    public function getAllDeudaByRuta($whereCliente,$id_vendedor, $whereVendedor, $fecha_inicio, $fecha_fin, $whereClientes, $whereDiasVisita, $whereRuta)
    {
        $filtroUsuarioVenta = ($whereVendedor!="") ? " AND v.id_vendedor={$id_vendedor} " : "";
        try {
            $sql = "SELECT 
                'v' AS tipo_co,
                v.id_venta,
                CONCAT(v.serie, ' | ', v.numero) AS factura,
                MAX(v.fecha_emision) AS fecha_emision,
                MAX(v.fecha_vencimiento) AS fecha_vencimiento,
                c.documento,
                c.datos AS cliente,
                c.mercado AS mercado,
                MAX(v.total) AS total,
                '' AS vendedor,
                SUM(CASE WHEN dv.estado = '1' THEN dv.monto ELSE 0 END) AS pagado,
                (MAX(v.total) - SUM(CASE WHEN dv.estado = '1' THEN dv.monto ELSE 0 END)) AS saldo
            FROM ventas AS v
            INNER JOIN dias_ventas AS dv ON v.id_venta = dv.id_venta 
            INNER JOIN clientes AS c ON v.id_cliente = c.id_cliente
            WHERE v.estado = 1 
            AND v.id_tipo_pago = 2 
            AND v.sucursal = '{$_SESSION['sucursal']}'
            AND v.id_empresa = '{$_SESSION['id_empresa']}'
            AND v.fecha_emision BETWEEN '$fecha_inicio' AND '$fecha_fin'
            $whereCliente
            $filtroUsuarioVenta
            $whereClientes
            $whereDiasVisita
            $whereRuta
            GROUP BY v.id_venta , c.datos, c.mercado
            ORDER BY v.id_venta ASC,c.mercado ASC, c.datos ASC";

            $fila = mysqli_query($this->conectar, $sql);
            $lista = mysqli_fetch_all($fila, MYSQLI_ASSOC);

            // Consulta de cotizaciones
            $sql = "SELECT 
            'c' AS tipo_co,
            co.cotizacion_id AS id_venta,
            CONCAT('#', co.numero) AS factura,
            if(us.usuario_id is null,'Usuario Eliminado',us.usuario) AS vendedor,
            co.fecha AS fecha_emision,
            (
                SELECT fecha 
                FROM cuotas_cotizacion cc 
                WHERE cc.id_coti = co.cotizacion_id  
                ORDER BY fecha DESC 
                LIMIT 1
            ) AS fecha_vencimiento,
            c.documento,
            c.datos AS cliente,
            c.mercado AS mercado,
            co.total,
            (
                SELECT IFNULL(SUM(cc.monto), 0) 
                FROM cuotas_cotizacion cc 
                WHERE cc.id_coti = co.cotizacion_id AND cc.estado = 1
            ) AS pagado,
            (co.total - (
                SELECT IFNULL(SUM(cc.monto), 0) 
                FROM cuotas_cotizacion cc 
                WHERE cc.id_coti = co.cotizacion_id AND cc.estado = 1
            )) AS saldo
            FROM cotizaciones co
            INNER JOIN clientes AS c ON c.id_cliente = co.id_cliente
            LEFT JOIN usuarios us ON us.usuario_id = co.id_usuario
            WHERE co.id_tipo_pago = 2 AND co.estado!=2
            $whereCliente
            $whereVendedor
            $whereClientes
            $whereDiasVisita
            $whereRuta
            ORDER BY co.cotizacion_id DESC, c.mercado ASC, c.datos ASC";

            $fila = mysqli_query($this->conectar, $sql);
            $lista2 = mysqli_fetch_all($fila, MYSQLI_ASSOC);

            return array_merge($lista2, $lista);
        } catch (Exception $e) {
            echo $e->getTraceAsString();
        }
    }
    
    public function getAllByIdVenta($id,$tip)
    {
        try {
            $sql ="";
            if ($tip=='c'){
                $sql = "SELECT *,cuota_coti_id as dias_venta_id,'c' as tipo_doc FROM cuotas_cotizacion WHERE id_coti = '$id'";
            }else{
                $sql = "SELECT  *,'v' as tipo_doc FROM dias_ventas WHERE id_venta = '$id'";
            }

            $fila = mysqli_query($this->conectar, $sql);
            return mysqli_fetch_all($fila, MYSQLI_ASSOC);
        } catch (Exception $e) {
            echo $e->getTraceAsString();
        }
    }
    public function pagarCuota($id)
    {
        // Obtener la fecha y hora actual del sistema
        date_default_timezone_set('America/Lima');
        $fecha_pago_real = date('Y-m-d H:i:s');
        
        // Actualizar el estado y guardar la fecha real del pago
        $sql = "UPDATE dias_ventas SET 
                estado = '1',
                fecha_pago_real = '$fecha_pago_real'
                WHERE dias_venta_id = $id";
        
        $result = $this->conectar->query($sql);
        if ($result) {
            return $result;
        }
    }
}
