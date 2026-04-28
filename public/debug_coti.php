<?php
$conexion = new mysqli('localhost', 'root', '', 'magusqao_titanic');
$coti = 46822;
$sql = "SELECT DATE(co.fecha) as fecha, co.id_empresa, co.sucursal, c.dias_visitas, c.id_ruta, c.mercado 
        FROM cotizaciones co 
        INNER JOIN clientes c ON co.id_cliente = c.id_cliente 
        WHERE co.cotizacion_id = '$coti'";
$res = $conexion->query($sql);
if ($res) {
    print_r($res->fetch_assoc());
} else {
    echo "Query failed: " . $conexion->error;
}
