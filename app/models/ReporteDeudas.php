
<?php


class ReporteDeudas
{
    public function __construct()
    {
        $this->conectar = (new Conexion())->getConexion();
    }

    public function getAllCobros($whereCliente, $whereVendedor, $whereFecha, $whereClientes, $whereDiasVisita, $whereRuta)
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
                IFNULL(c.dias_visitas,'') AS dias_visitas,
                IFNULL(c.id_ruta,'') AS id_ruta,
                cu.fecha_pago_real

                FROM cotizaciones co

                INNER JOIN clientes AS c ON c.id_cliente=co.id_cliente
                INNER JOIN cuotas_cotizacion cu ON cu.id_coti = co.cotizacion_id and cu.estado=1
                LEFT JOIN usuarios us ON us.usuario_id = co.id_usuario
                WHERE co.id_tipo_pago=2 AND co.estado!=2
                AND co.id_empresa='{$_SESSION['id_empresa']}'
                AND co.sucursal='{$_SESSION['sucursal']}'
                $whereFecha
                $whereCliente
                $whereVendedor
                $whereClientes
                $whereDiasVisita
                $whereRuta
                ORDER BY cu.fecha_pago_real DESC,FIELD(c.dias_visitas,'LUNES','MARTES','MIERCOLES','JUEVES','VIERNES','SABADO','DOMINGO'),c.id_ruta ASC
            ";
            $fila = mysqli_query($this->conectar, $sql);
            $lista2 =  mysqli_fetch_all($fila, MYSQLI_ASSOC);

            return array_merge($lista2, array());
        } catch (Exception $e) {
            echo $e->getTraceAsString();
        }
    }
    #
    public function getAllCobrosByVendedor($whereCliente, $whereVendedor, $whereFecha, $whereClientes, $whereDiasVisita, $whereRuta)
    {
        try {
            // CORREGIDO: Usar fecha_pago_real en lugar de fecha para ambas tablas
            $whereFechaVentas = str_replace('cu.fecha_pago_real', 'dv.fecha_pago_real', $whereFecha);
            $whereVendedorVentas = str_replace('cu.', 'dv.', $whereVendedor);
            
            $sql = "
                (
                SELECT 
                'v' AS tipo_co,
                v.id_venta,
                CONCAT(v.serie,'-',v.numero) AS factura,
                us.usuario vendedor, 
                v.fecha_emision AS fecha_emision,
                dv.fecha_pago_real AS fecha_pago,
                c.id_cliente,
                c.documento,
                c.datos AS nombre_cliente,
                CONCAT(c.documento ,' | ' ,c.datos) AS cliente,
                v.total,
                dv.monto as pagado,
                (
                select sum(dv1.monto) from dias_ventas dv1 where dv1.fecha_pago_real<=dv.fecha_pago_real and dv1.id_venta=dv.id_venta and dv1.estado=1
                ) AS 'total_pagado',
                IFNULL(dv.tipo_pago,'Efectivo') AS 'metodo_pago',
                IFNULL(c.dias_visitas,'') AS dias_visitas,
                IFNULL(c.id_ruta,'') AS id_ruta

                FROM ventas v

                INNER JOIN clientes AS c ON c.id_cliente=v.id_cliente
                INNER JOIN dias_ventas dv ON dv.id_venta = v.id_venta and dv.estado=1
                LEFT JOIN usuarios us ON us.usuario_id = dv.id_usuario
                WHERE v.id_tipo_pago=2 AND v.estado=1
                AND v.id_empresa='{$_SESSION['id_empresa']}'
                AND v.sucursal='{$_SESSION['sucursal']}'
                $whereFechaVentas
                $whereCliente
                $whereVendedorVentas
                $whereClientes
                $whereDiasVisita
                $whereRuta
                )
                UNION
                (
                SELECT 
                'c' AS tipo_co,
                co.cotizacion_id AS id_venta,
                CONCAT('#',co.numero) AS factura,
                us.usuario vendedor, 
                co.fecha AS fecha_emision,
                cu.fecha_pago_real AS fecha_pago,
                c.id_cliente,
                c.documento,
                c.datos AS nombre_cliente,
                CONCAT(c.documento ,' | ' ,c.datos) AS cliente,
                co.total,
                cu.monto as pagado,
                (
                select sum(cu1.monto) from cuotas_cotizacion cu1 where cu1.fecha_pago_real<=cu.fecha_pago_real and cu1.id_coti=cu.id_coti and cu1.estado=1
                ) AS 'total_pagado',
                IFNULL(cu.tipo_pago,'Efectivo') AS 'metodo_pago',
                IFNULL(c.dias_visitas,'') AS dias_visitas,
                IFNULL(c.id_ruta,'') AS id_ruta

                FROM cotizaciones co

                INNER JOIN clientes AS c ON c.id_cliente=co.id_cliente
                INNER JOIN cuotas_cotizacion cu ON cu.id_coti = co.cotizacion_id and cu.estado=1
                LEFT JOIN usuarios us ON us.usuario_id = cu.id_usuario
                WHERE co.id_tipo_pago=2 AND co.estado!=2
                AND co.id_empresa='{$_SESSION['id_empresa']}'
                AND co.sucursal='{$_SESSION['sucursal']}'
                $whereFecha
                $whereCliente
                $whereVendedor
                $whereClientes
                $whereDiasVisita
                $whereRuta
                )
                ORDER BY FIELD(metodo_pago,'Efectivo','Yape','Plin','Transferencia'),fecha_pago DESC
            ";
            $fila = mysqli_query($this->conectar, $sql);
            $lista =  mysqli_fetch_all($fila, MYSQLI_ASSOC);

            return $lista;
        } catch (Exception $e) {
            echo $e->getTraceAsString();
        }
    }

    public function getAllDeudaByRuta($whereCliente,$id_vendedor, $whereVendedor, $whereFechaVenta, $whereFechaCoti, $whereClientes, $whereDiasVisita, $whereRuta)
    {
        $filtroUsuarioVenta = ($whereVendedor!="") ? " AND v.id_vendedor={$id_vendedor} " : "";
        try {
            /* $sql = "SELECT 
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
            $whereFechaVenta
            $whereCliente
            $filtroUsuarioVenta
            $whereClientes
            $whereDiasVisita
            $whereRuta
            GROUP BY v.id_venta , c.datos, c.mercado
            ORDER BY c.mercado ASC, c.datos ASC";
    
            $fila = mysqli_query($this->conectar, $sql);
            $lista = mysqli_fetch_all($fila, MYSQLI_ASSOC); */
            $lista = [];

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
            AND co.id_empresa='{$_SESSION['id_empresa']}'
            AND co.sucursal='{$_SESSION['sucursal']}'
            $whereFechaCoti
            $whereCliente
            $whereVendedor
            $whereClientes
            $whereDiasVisita
            $whereRuta
            ORDER BY c.mercado ASC, c.datos ASC";
    
            $fila = mysqli_query($this->conectar, $sql);
            $lista2 = mysqli_fetch_all($fila, MYSQLI_ASSOC);

            return array_merge($lista2, $lista);
        } catch (Exception $e) {
            echo $e->getTraceAsString();
        }
    }

    // SUMATORIA DE PRODUCTOS
    public function getAllVentasProducto1($whereVendedor,$whereClientes,$whereDiasVisita,$whereFechaVenta,$whereFechaCoti, $whereRuta){
        try {
            $sql = "
                SELECT
                sub.id_venta,
                sub.factura,
                sub.fecha_emision,
                sub.total,
                sub.id_producto,
                sub.codigo,
                sub.descripcion,
                sub.proveedor,
                SUM(sub.cantidad) AS cantidad,
                SUM(sub.precio) AS precio,
                SUM(sub.costo) AS costo,
                SUM(sub.productos) AS productos
                FROM
                (

                (
                SELECT 
                v.id_venta,
                CONCAT(v.serie, ' | ', v.numero) AS factura,
                v.fecha_emision AS fecha_emision,
                v.total AS total,
                p.id_producto,
                p.codigo,
                p.descripcion,
                p.razon_social AS 'proveedor',
                SUM((pv.cantidad*IF(pv.presenta_cnt<=0,1,pv.presenta_cnt))) AS cantidad,
                SUM(pv.precio/IF(pv.presenta_cnt<=0,1,pv.presenta_cnt)) AS 'precio',
                SUM(pv.costo) AS costo,
                COUNT(p.id_producto) AS 'productos'
                FROM ventas AS v
                INNER JOIN productos_ventas pv ON pv.id_venta = v.id_venta
                INNER JOIN productos p ON p.id_producto = pv.id_producto
                LEFT JOIN clientes AS c ON v.id_cliente = c.id_cliente
                LEFT JOIN usuarios AS u ON u.usuario_id = v.id_vendedor
                WHERE v.estado = 1 
                AND v.id_empresa='{$_SESSION['id_empresa']}'
                AND v.sucursal='{$_SESSION['sucursal']}'
                $whereFechaVenta
                $whereClientes
                $whereVendedor
                $whereRuta
                GROUP BY p.id_producto
                )
                UNION 
                (
                SELECT 
                co.cotizacion_id AS id_venta,
                CONCAT('#', co.numero) AS factura,
                co.fecha AS fecha_emision,
                co.total,
                p.id_producto,
                p.codigo,
                p.descripcion,
                p.razon_social AS 'proveedor',
                SUM((pc.cantidad*IF(pc.presenta_cnt<=0,1,pc.presenta_cnt))) AS cantidad,
                SUM(pc.precio/IF(pc.presenta_cnt<=0,1,pc.presenta_cnt)) AS 'precio',
                SUM(pc.costo) AS costo,
                COUNT(p.id_producto) AS 'productos'
                FROM cotizaciones co
                INNER JOIN productos_cotis pc ON pc.id_coti=co.cotizacion_id
                INNER JOIN productos p ON p.id_producto = pc.id_producto
                INNER JOIN clientes AS c ON c.id_cliente = co.id_cliente
                LEFT JOIN usuarios u ON u.usuario_id = co.id_usuario
                WHERE co.id_tipo_pago = 2 AND co.estado!=2
                AND co.id_empresa='{$_SESSION['id_empresa']}'
                AND co.sucursal='{$_SESSION['sucursal']}'
                $whereFechaCoti
                $whereClientes
                $whereVendedor
                $whereRuta
                GROUP BY p.id_producto
                )

                ) AS sub
                GROUP BY sub.id_producto
                ORDER BY sub.descripcion ASC

            ";
            $fila = mysqli_query($this->conectar, $sql);
            $lista2 =  mysqli_fetch_all($fila, MYSQLI_ASSOC);

            return $lista2;
        } catch (Exception $e) {
            echo $e->getTraceAsString();
        }
    }
    public function getAllVentasProducto($whereVendedor,$whereClientes,$whereDiasVisita, $whereFechaVenta,$whereFechaCoti, $whereRuta){
        try {
            $sql = "
                SELECT
                sub.id_venta,
                sub.factura,
                sub.fecha_emision,
                sub.total,
                sub.id_producto,
                sub.codigo,
                sub.descripcion,
                sub.proveedor,
                SUM(sub.cantidad) AS cantidad,
                SUM(sub.precio) AS precio,
                SUM(sub.costo) AS costo,
                SUM(sub.productos) AS productos,
                sub.peso_bruto,
                sub.presenta_cnt
                FROM
                (

                (
                SELECT 
                v.id_venta,
                CONCAT(v.serie, ' | ', v.numero) AS factura,
                v.fecha_emision AS fecha_emision,
                v.total AS total,
                p.id_producto,
                p.codigo,
                p.descripcion,
                p.razon_social AS 'proveedor',
                SUM((pv.cantidad*IF(pv.presenta_cnt<=0,1,pv.presenta_cnt))) AS cantidad,
                SUM(pv.precio/IF(pv.presenta_cnt<=0,1,pv.presenta_cnt)) AS 'precio',
                SUM(pv.costo) AS costo,
                COUNT(p.id_producto) AS 'productos',
                p.peso_bruto,
                pv.presenta_cnt
                FROM ventas AS v
                INNER JOIN productos_ventas pv ON pv.id_venta = v.id_venta
                INNER JOIN productos p ON p.id_producto = pv.id_producto
                LEFT JOIN clientes AS c ON v.id_cliente = c.id_cliente
                LEFT JOIN usuarios AS us ON us.usuario_id = v.id_vendedor
                WHERE v.estado = 1 
                AND v.id_empresa='{$_SESSION['id_empresa']}'
                AND v.sucursal='{$_SESSION['sucursal']}'
                $whereFechaVenta
                $whereClientes
                $whereVendedor
                $whereRuta
                GROUP BY p.id_producto
                )
                UNION 
                (
                SELECT 
                co.cotizacion_id AS id_venta,
                CONCAT('#', co.numero) AS factura,
                co.fecha AS fecha_emision,
                co.total,
                p.id_producto,
                p.codigo,
                p.descripcion,
                p.razon_social AS 'proveedor',
                SUM((pc.cantidad*IF(pc.presenta_cnt<=0,1,pc.presenta_cnt))) AS cantidad,
                SUM(pc.precio/IF(pc.presenta_cnt<=0,1,pc.presenta_cnt)) AS 'precio',
                SUM(pc.costo) AS costo,
                COUNT(p.id_producto) AS 'productos',
                p.peso_bruto,
                pc.presenta_cnt
                FROM cotizaciones co
                INNER JOIN productos_cotis pc ON pc.id_coti=co.cotizacion_id
                INNER JOIN productos p ON p.id_producto = pc.id_producto
                INNER JOIN clientes AS c ON c.id_cliente = co.id_cliente
                LEFT JOIN usuarios us ON us.usuario_id = co.id_usuario
                WHERE co.id_tipo_pago = 2 AND co.estado!=2
                AND co.id_empresa='{$_SESSION['id_empresa']}'
                AND co.sucursal='{$_SESSION['sucursal']}'
                $whereFechaCoti
                $whereClientes
                $whereVendedor
                $whereRuta
                GROUP BY p.id_producto
                )

                ) AS sub
                GROUP BY sub.id_producto
                ORDER BY sub.descripcion ASC

            ";
            $fila = mysqli_query($this->conectar, $sql);
            $lista2 =  mysqli_fetch_all($fila, MYSQLI_ASSOC);

            return $lista2;
        } catch (Exception $e) {
            echo $e->getTraceAsString();
        }
    }
    public function getAllVentasProducto2($whereVendedor, $whereFechaVenta, $whereRuta){
        try {
            $sql = "
                SELECT 
                v.id_venta,
                CONCAT(v.serie, ' | ', v.numero) AS factura,
                v.fecha_emision AS fecha_emision,
                v.fecha_vencimiento AS fecha_vencimiento,
                c.documento,
                c.datos AS cliente,
                c.mercado AS mercado,
                v.total AS total,
                p.codigo,
                p.descripcion,
                p.razon_social AS 'proveedor',
                SUM((pv.cantidad*IF(pv.presenta_cnt<=0,1,pv.presenta_cnt))) AS cantidad,
                SUM(pv.precio/IF(pv.presenta_cnt<=0,1,pv.presenta_cnt)) AS 'precio',
                SUM(pv.costo) AS costo,
                COUNT(p.id_producto) as 'productos'
                FROM ventas AS v
                INNER JOIN productos_ventas pv ON pv.id_venta = v.id_venta
                INNER JOIN productos p ON p.id_producto = pv.id_producto
                LEFT JOIN clientes AS c ON v.id_cliente = c.id_cliente
                LEFT JOIN usuarios AS u ON u.usuario_id = v.id_vendedor
                WHERE v.estado = 1 
                $whereVendedor
                $whereRuta
                $whereFechaVenta
                GROUP BY p.id_producto
                ORDER BY p.descripcion ASC
            ";
            
            $fila = mysqli_query($this->conectar, $sql);
            $lista2 =  mysqli_fetch_all($fila, MYSQLI_ASSOC);

            return $lista2;
        } catch (Exception $e) {
            echo $e->getTraceAsString();
        }
    }

    public function getAllVentasVendedorProveedor($id_vendedor, $whereFechaVenta,$whereFechaCoti,$proveedor){
        try {
            $sql = "
                SELECT
                sub.id_venta,
                sub.factura,
                sub.fecha_emision,
                sub.total,
                sub.id_producto,
                sub.codigo,
                sub.descripcion,
                sub.proveedor,
                SUM(sub.cantidad) AS cantidad,
                SUM(sub.precio) AS precio,
                SUM(sub.presenta_cnt) AS presenta_cnt,
                SUM(sub.costo) AS costo,
                SUM(sub.productos) AS productos
                FROM
                (

                (
                SELECT 
                v.id_venta,
                CONCAT(v.serie, ' | ', v.numero) AS factura,
                v.fecha_emision AS fecha_emision,
                v.total AS total,
                p.id_producto,
                p.codigo,
                p.descripcion,
                p.razon_social AS 'proveedor',
                SUM((pv.cantidad*IF(pv.presenta_cnt<=0,1,pv.presenta_cnt))) AS cantidad,
                SUM(pv.precio/IF(pv.presenta_cnt<=0,1,pv.presenta_cnt)) AS 'precio',
                SUM(IF(pv.presenta_cnt<=0,1,pv.presenta_cnt)) AS presenta_cnt,
                SUM(pv.costo) AS costo,
                COUNT(p.id_producto) AS 'productos'
                FROM ventas AS v
                INNER JOIN productos_ventas pv ON pv.id_venta = v.id_venta
                INNER JOIN productos p ON p.id_producto = pv.id_producto
                LEFT JOIN clientes AS c ON v.id_cliente = c.id_cliente
                LEFT JOIN usuarios AS u ON u.usuario_id = v.id_vendedor
                WHERE v.estado = 1 
                AND v.id_empresa='{$_SESSION['id_empresa']}'
                AND v.sucursal='{$_SESSION['sucursal']}'
                AND v.id_vendedor=$id_vendedor
                AND trim(p.razon_social)='$proveedor'
                $whereFechaVenta
                GROUP BY p.id_producto
                )
                UNION 
                (
                SELECT 
                co.cotizacion_id AS id_venta,
                CONCAT('#', co.numero) AS factura,
                co.fecha AS fecha_emision,
                co.total,
                p.id_producto,
                p.codigo,
                p.descripcion,
                p.razon_social AS 'proveedor',
                SUM((pc.cantidad*IF(pc.presenta_cnt<=0,1,pc.presenta_cnt))) AS cantidad,
                SUM(pc.precio/IF(pc.presenta_cnt<=0,1,pc.presenta_cnt)) AS 'precio',
                SUM(IF(pc.presenta_cnt<=0,1,pc.presenta_cnt)) AS presenta_cnt,
                SUM(pc.costo) AS costo,
                COUNT(p.id_producto) AS 'productos'
                FROM cotizaciones co
                INNER JOIN productos_cotis pc ON pc.id_coti=co.cotizacion_id
                INNER JOIN productos p ON p.id_producto = pc.id_producto
                INNER JOIN clientes AS c ON c.id_cliente = co.id_cliente
                LEFT JOIN usuarios u ON u.usuario_id = co.id_usuario
                WHERE co.id_tipo_pago = 2 AND co.estado!=2
                AND co.id_empresa='{$_SESSION['id_empresa']}'
                AND co.sucursal='{$_SESSION['sucursal']}'
                AND co.id_usuario=$id_vendedor
                AND trim(p.razon_social)='$proveedor'
                $whereFechaCoti
                GROUP BY p.id_producto
                )

                ) AS sub
                GROUP BY sub.id_producto
                ORDER BY sub.descripcion ASC

            ";
            
            $fila = mysqli_query($this->conectar, $sql);
            $lista2 =  mysqli_fetch_all($fila, MYSQLI_ASSOC);

            return $lista2;
        } catch (Exception $e) {
            echo $e->getTraceAsString();
        }
    }

    public function getAllVentasVendedorProveedor2($id_vendedor,$proveedor, $whereFechaVenta){
        try {
            $sql = "
                SELECT 
                v.id_venta,
                CONCAT(v.serie, ' | ', v.numero) AS factura,
                v.fecha_emision AS fecha_emision,
                v.fecha_vencimiento AS fecha_vencimiento,
                c.documento,
                c.datos AS cliente,
                c.mercado AS mercado,
                v.total AS total,
                p.codigo,
                p.descripcion,
                p.razon_social AS 'proveedor',
                pv.cantidad,
                pv.precio/if(pv.presenta_cnt<=0,1,pv.presenta_cnt) AS 'precio',
                if(pv.presenta_cnt<=0,1,pv.presenta_cnt) AS presenta_cnt
                FROM ventas AS v
                INNER JOIN productos_ventas pv ON pv.id_venta = v.id_venta
                INNER JOIN productos p ON p.id_producto = pv.id_producto
                INNER JOIN clientes AS c ON v.id_cliente = c.id_cliente
                INNER JOIN usuarios AS u ON u.usuario_id = v.id_vendedor
                WHERE v.estado = 1 
                AND v.id_vendedor=$id_vendedor
                and trim(p.razon_social)='$proveedor'
                $whereFechaVenta
                ORDER BY p.descripcion ASC
            ";
            $fila = mysqli_query($this->conectar, $sql);
            $lista2 =  mysqli_fetch_all($fila, MYSQLI_ASSOC);

            return $lista2;
        } catch (Exception $e) {
            echo $e->getTraceAsString();
        }
    }

}
