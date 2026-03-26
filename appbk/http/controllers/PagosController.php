<?php

class PagosController extends Controller
{
    private $conectar;

    public function __construct()
    {
        $this->conectar=(new Conexion())->getConexion();
    }

    public function render(){
        try {
            $sql = "SELECT com.id_compra,CONCAT(com.serie, ' | ' , com.numero) AS factura,com.moneda ,com.fecha_emision,com.fecha_vencimiento,CONCAT(pro.ruc,' | ' ,pro.razon_social) AS cliente,
            com.total,            
                CASE 
                WHEN dc.estado = '1'  AND dc.id_compra = dc.id_compra THEN SUM(dc.monto)
                WHEN dc.estadO= '0' THEN '0'
                END AS pagado,
               (com.total - SUM(dc.monto) ) AS saldo
                FROM compras AS com
                INNER JOIN dias_compras AS dc ON  com.id_compra=dc.id_compra 
                INNER JOIN proveedores AS pro ON com.id_proveedor=pro.proveedor_id
                WHERE com.id_tipo_pago = 2  and com.id_empresa='{$_SESSION['id_empresa']}'
                and com.sucursal='{$_SESSION['sucursal']}'
                GROUP BY dc.id_compra,dc.estado  
            ";
            $fila = mysqli_query($this->conectar, $sql);
            return json_encode(mysqli_fetch_all($fila, MYSQLI_ASSOC));
        } catch (Exception $e) {
            return json_encode([]);
        }
    }
    public function getAllByIdCompra(){


        try {
            $sql = "SELECT * FROM dias_compras WHERE id_compra = '{$_POST['id']}'";
            $fila = mysqli_query($this->conectar, $sql);
            return json_encode(mysqli_fetch_all($fila, MYSQLI_ASSOC));
        } catch (Exception $e) {
            echo $e->getTraceAsString();
        }
    }
    public function validarLista()
    {
        $listaPagos = json_decode($_POST['dias_lista'], true);
        echo json_encode($listaPagos);
    }
    public function pagarCuota()
    {
        $sql = "UPDATE dias_compras set estado = '1' where dias_compra_id='{$_POST['id']}'";
        $result = $this->conectar->query($sql);

        echo json_encode($result);
    }

}