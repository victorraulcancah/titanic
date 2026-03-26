
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
                $sql = "SELECT v.id_venta,CONCAT(v.serie, ' | ' , v.numero) AS factura, v.fecha_emision,v.fecha_vencimiento,CONCAT(c.documento ,' | ' ,c.datos) AS cliente,
                v.total,
                CASE 
                WHEN dv.estado = '1'  AND dv.id_venta = dv.id_venta THEN SUM(dv.monto)
                WHEN dv.estado= '0' THEN '0'
                END AS pagado,
                (v.total - SUM(dv.monto) ) AS saldo
                FROM ventas AS v
                INNER JOIN dias_ventas AS dv ON  v.id_venta=dv.id_venta 
                INNER JOIN clientes AS c ON v.id_cliente=c.id_cliente
                WHERE v.estado=1 AND v.id_tipo_pago = 2 and v.sucursal ='{$_SESSION['sucursal']}' and v.id_empresa='{$_SESSION['id_empresa']}'
                GROUP BY dv.id_venta,dv.estado 
            ";
            $fila = mysqli_query($this->conectar, $sql);
            return mysqli_fetch_all($fila, MYSQLI_ASSOC);
        } catch (Exception $e) {
            echo $e->getTraceAsString();
        }
    }

    public function getAllByIdVenta($id)
    {
        try {
            $sql = "SELECT * FROM dias_ventas WHERE id_venta = '$id'";
            $fila = mysqli_query($this->conectar, $sql);
            return mysqli_fetch_all($fila, MYSQLI_ASSOC);
        } catch (Exception $e) {
            echo $e->getTraceAsString();
        }
    }
    public function pagarCuota($id)
    {
        $sql = "UPDATE dias_ventas set estado = '1' where dias_venta_id=$id";
        $result = $this->conectar->query($sql);
        if ($result) {
            return $result;
        }
    }
}
