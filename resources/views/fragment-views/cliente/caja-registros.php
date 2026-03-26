<?php
$conexion = (new Conexion())->getConexion();

// Obtener registros de caja con los cobros separados por m¨¦todo de pago
$sql = "SELECT 
    ce.caja_id,
    ce.detalle,
    ce.fecha,
    ce.estado,
    -- Sumar solo cobros en EFECTIVO para Entrada
    COALESCE((
        SELECT SUM(dv.monto) 
        FROM dias_ventas dv 
        WHERE dv.id_caja_empresa = ce.caja_id 
        AND dv.estado = '1' 
        AND (dv.tipo_pago = 'Efectivo' OR dv.tipo_pago IS NULL)
    ), 0) + 
    COALESCE((
        SELECT SUM(cc.monto) 
        FROM cuotas_cotizacion cc 
        WHERE cc.id_caja_empresa = ce.caja_id 
        AND cc.estado = '1' 
        AND (cc.tipo_pago = 'Efectivo' OR cc.tipo_pago IS NULL)
    ), 0) as entrada_efectivo,
    -- Sumar solo cobros por TRANSFERENCIAS/BANCOS para Salida
    COALESCE((
        SELECT SUM(dv.monto) 
        FROM dias_ventas dv 
        WHERE dv.id_caja_empresa = ce.caja_id 
        AND dv.estado = '1' 
        AND dv.tipo_pago IN ('Yape', 'Plin', 'BCP', 'BBVA')
    ), 0) + 
    COALESCE((
        SELECT SUM(cc.monto) 
        FROM cuotas_cotizacion cc 
        WHERE cc.id_caja_empresa = ce.caja_id 
        AND cc.estado = '1' 
        AND cc.tipo_pago IN ('Yape', 'Plin', 'BCP', 'BBVA')
    ), 0) as salida_bancos
FROM caja_empresa ce
WHERE ce.sucursal = '{$_SESSION['sucursal']}' 
AND ce.id_empresa = '{$_SESSION['id_empresa']}'
ORDER BY ce.fecha DESC";

$listaC = $conexion->query($sql);

?>
<div class="page-title-box">
    <div class="row align-items-center">
            <!-- <div class="col-md-8">
                <h6 class="page-title">Ventas</h6>
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="javascript: void(0);">Facturacion</a></li>
                    <li class="breadcrumb-item"><a href="/ventas" class="button-link">Ventas</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Productos</li>
                </ol>
            </div> -->
        <div class="clearfix">
            <h6 class="page-title float-end">Ventas</h6>
            <ol class="breadcrumb m-0 float-start">
                <li class="breadcrumb-item"><a href="javascript: void(0);">Facturacion</a></li>
                <li class="breadcrumb-item"><a href="/ventas" class="button-link">Ventas</a></li>
                <li class="breadcrumb-item active" aria-current="page">Productos</li>
            </ol>
        </div>
        <div class="col-md-4">
            <div class="float-end d-none d-md-block">

            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card" style="border-radius:20px;box-shadow:0 4px 6px -1px rgba(0,0,0,.1),0 2px 4px -1px rgba(0,0,0,.06)">
            <div class="card-body">

                <h4 class="card-title">Registros de Caja</h4>

                <div class="card-title-desc">

                </div>
                <div class="">
                    <table id="tabla-registros" class="table table-sm table-bordered text-center">
                        <thead>
                        <tr>
                            <th></th>
                            <th>Detalle</th>
                            <th>Fecha</th>
                            <th>Efectivo</th>
                            <th>Banco</th>
                            <th>Total</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        $contador=0;
                        foreach ($listaC as $row){
                            $contador++;
                            $entrada = floatval($row['entrada_efectivo']);
                            $salida = floatval($row['salida_bancos']);
                            $total = $entrada + $salida; // SUMA, no resta
                            ?>
                            <tr>
                                <td><?=$contador?></td>
                                <td><?=$row['detalle']?></td>
                                <td><?=Tools::formatoFechaVisual($row['fecha'])?></td>
                                <td>S/ <?=number_format($entrada, 2)?></td>
                                <td>S/ <?=number_format($salida, 2)?></td>
                                <td>S/ <?=number_format($total, 2)?></td>
                            </tr>
                        <?php
                        }
                        ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    $(document).ready(function(){
        $("#tabla-registros").DataTable({})
    })
</script>