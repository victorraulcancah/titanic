<?php
$empresa = $_SESSION['id_empresa'];

$anio1 = date("Y");
$mes1 = date("m");
$anio2 = '';
$mes2 = '';
if ($mes1 == 1) {
    $mes2 = '12';
    $anio2 = $anio1 - 1;
} else {
    $anio2 = $anio1;
    $mes2 = $mes1 - 1;
}

$conexion = (new Conexion())->getConexion();
$sql = "SELECT (SELECT SUM(total) FROM ventas WHERE id_empresa='$empresa' AND estado = '1' and sucursal='{$_SESSION['sucursal']}' AND YEAR(fecha_emision)='$anio1' AND MONTH(fecha_emision)='$mes1') totalv ,
(SELECT COUNT(*)  FROM  clientes WHERE id_empresa = '$empresa') cnt_cli,
(SELECT SUM(total) FROM ventas WHERE id_empresa='$empresa'  and sucursal='{$_SESSION['sucursal']}' and id_tido =2 AND estado = '1' AND YEAR(fecha_emision)='$anio1' AND MONTH(fecha_emision)='$mes1') totalvF ,
(SELECT SUM(total) FROM ventas WHERE id_empresa='$empresa' and sucursal='{$_SESSION['sucursal']}' and id_tido =1 AND estado = '1' AND YEAR(fecha_emision)='$anio1' AND MONTH(fecha_emision)='$mes1') totalvB,
 (SELECT SUM(total) FROM ventas WHERE id_empresa='$empresa' and sucursal='{$_SESSION['sucursal']}' AND estado = '1' AND YEAR(fecha_emision)='$anio2' AND MONTH(fecha_emision)='$mes2') totalvMA,
 (SELECT productos.descripcion FROM `productos_compras` inner join productos on productos_compras.id_producto = productos.id_producto GROUP BY productos.id_producto ORDER BY SUM(productos_compras.cantidad) DESC limit 1) prodVen,
(SELECT  SUM(cantidad) FROM productos_compras GROUP BY id_producto ORDER BY SUM(cantidad) DESC limit 1) prodVenCan";

//echo $sql;

$data = $conexion->query($sql)->fetch_assoc();

$dataListVen = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];

$sql = "SELECT 
    MONTH(fecha_emision) mes,
    SUM(total ) total
  FROM
    ventas 
  WHERE id_empresa = '$empresa' 
    AND estado = '1' 
    and sucursal='{$_SESSION['sucursal']}'
    AND YEAR(fecha_emision) = '$anio1'
    GROUP BY mes";
$resultList = $conexion->query($sql);

foreach ($resultList as $dtTemp) {
    $tempValue = 0;
    if (doubleval($dtTemp['total']) > 0) {
        $tempValue = doubleval($dtTemp['total']);
    }
    $dataListVen[intval($dtTemp['mes'])] = $tempValue;
}


?>
<!-- start page title -->
<div class="page-title-box">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h6 class="page-title">Dashboard</h6>
            <ol class="breadcrumb m-10">
                <li class="breadcrumb-item active">Bienvenido <strong>TITANIC</strong> al Sistema de Facturaci&oacute;n Electr&oacute;nica <strong>HATUNA</strong></li>
            </ol>
        </div>
        <div class="col-md-4">

        </div>
    </div>
</div>
<!-- end page title -->

<div class="row">
    <div class="col-xl-3 col-md-6">
        <div class="card mini-stat bg-white text-dark" style="border-radius:20px;box-shadow:0 4px 6px -1px rgba(0,0,0,.1),0 2px 4px -1px rgba(0,0,0,.06)">
            <div class="card-body">
                <div class="mb-4">
                    <div class="position-absolute top-0 start-15 translate-middle border-radius-xl mini-stat-img mt-3 w-25 h-50" style="border-radius: 20px;background-color: #ffad9e;">
                        <img class="mt-3 mr-5" src="<?= URL::to('public/assets/images/services-icon/01.png') ?>">
                    </div>
                    <h5 class="text-uppercase fw-light text-dark text-end">Monto Vendido</h5>
                    <h1 class="fw-bolder text-end">S/ <?= number_format($data["totalv"] ?? 0.00, 2, ".", ",") ?></h1>
                    <!-- <div class="mini-stat-label bg-success">
                        <p class="mb-0">Mes</p>
                    </div> -->
                </div>
                <div class="pt-2">
                    <div class="float-end" hidden>
                        <a href="javascript:void(0)" class="text-black-50"><i class="mdi mdi-arrow-right h5"></i></a>
                    </div>

                    <p class="text-dark-50 mb-0 mt-1 text-end">Facturas y Boletas</p>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="card mini-stat bg-white text-dark" style="border-radius:20px;box-shadow:0 4px 6px -1px rgba(0,0,0,.1),0 2px 4px -1px rgba(0,0,0,.06)">
            <div class="card-body">
                <div class="mb-4">
                    <div class="position-absolute top-0 start-15 translate-middle border-radius-xl mini-stat-img mt-3 w-25 h-50" style="border-radius: 20px; background-color: #ffad9e;">
                        <img class="mt-3 mr-5" src="<?= URL::to('public/assets/images/services-icon/02.png') ?>" alt="">
                    </div>
                    <h6 class="fw-light text-uppercase text-black text-end" style="margin-bottom: 20px;">Producto mas Vendido</h6>
                    <p class="fw-bolder text-end" style="font-size: 17px;"><?= $data["prodVen"] ?></p>
                    <div hidden class="mini-stat-label bg-danger">
                        <p class="mb-0">Total</p>
                    </div>
                </div>
                <div class="pt-2">
                    <div hidden class="float-end">
                        <a href="javascript:void(0)" class="text-white-50"><i class="mdi mdi-arrow-right h5"></i></a>
                    </div>

                    <p class="text-dark-50 mb-0 mt-1 text-end">Cantidad Vendidas ( <?= $data["prodVenCan"] ?> )</p>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card mini-stat bg-white text-dark" style="border-radius:20px;box-shadow:0 5px 6px -1px rgba(0,0,0,.1),0 2px 4px -1px rgba(0,0,0,.06)">
            <div class="card-body">
                <div class="mb-4">
                    <div class="position-absolute top-0 start-15 translate-middle border-radius-xl mini-stat-img mt-3 w-25 h-50" style="border-radius: 20px; background-color: #ffad9e;">
                        <img class="mt-3 mr-5" src="<?= URL::to('public/assets/images/services-icon/03.png') ?>" alt="">
                    </div>
                    <h5 class="fw-light text-uppercase text-black text-end">Total en Facturas</h5>
                    <h1 class="fw-bolder text-end">S/ <?= number_format($data["totalvF"] ?? 0.00, 2, ".", ",") ?> </h1>
                    <!-- <div class="mini-stat-label bg-info">
                        <p class="mb-0"> Mes</p>
                    </div> -->
                </div>
                <div class="pt-2">
                    <div class="float-end">
                        <a href="javascript:void(0)" class="text-white-50"><i class="mdi mdi-arrow-right h5"></i></a>
                    </div>

                    <p class="text-white-50 mb-0 mt-1"> </p>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="card mini-stat bg-white text-dark" style="border-radius:20px;box-shadow:0 4px 6px -1px rgba(0,0,0,.1),0 2px 4px -1px rgba(0,0,0,.06)">
            <div class="card-body">
                <div class="mb-4">
                    <div class="position-absolute top-0 start-15 translate-middle border-radius-xl mini-stat-img mt-3 w-25 h-50" style="border-radius: 20px; background-color: #ffad9e;">
                        <img class="mt-3 mr-5" src="<?= URL::to('public/assets/images/services-icon/04.png') ?>" alt="">
                    </div>
                    <h5 class="fw-light text-uppercase text-black text-end">Total en Boletas</h5>
                    <h1 class="fw-bolder text-end">S/ <?= number_format($data["totalvB"] ?? 0.00, 2, ".", ",") ?> </h1>
                    <!-- <div class="mini-stat-label bg-warning">
                        <p class="mb-0">Mes</p>
                    </div> -->
                </div>
                <div class="pt-2">
                    <div class="float-end">
                        <a href="javascript:void(0)" class="text-white-50"><i class="mdi mdi-arrow-right h5"></i></a>
                    </div>

                    <p class="text-white-50 mb-0 mt-1"> </p>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- end row -->

<div class="row">
    <div class="col-xl-12">
        <div class="card">
            <div class="card-body h-50">
                <h4 class="card-title mb-4">Venta Anual</h4>
                <div class="row">
                    <div class="col-lg-7">
                        <div>
                            <canvas id="chart-with-area">
                            </canvas>
                        </div>
                    </div>
                    <div class="col-lg-5">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="text-center">
                                    <p class="text-muted mb-4">Este Mes</p>
                                    <h3>S/ <?= number_format($data["totalv"] ?? 0.00, 2, ".", ",") ?></h3>
                                    <p class="text-muted mb-5">Ganancias Totales.</p>
                                    <span class="peity-donut"
                                        data-peity='{ "fill": ["#02a499", "#f2f2f2"], "innerRadius": 28, "radius": 32 }'
                                        data-width="72" data-height="72"></span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="text-center">
                                    <p class="text-muted mb-4">Mes Anterior</p>
                                    <h3>S/ <?= number_format($data["totalvMA"] ?? 0.00, 2, ".", ",") ?></h3>
                                    <p class="text-muted mb-5">Comparativa Ganancias Totales.</p>
                                    <span class="peity-donut"
                                        data-peity='{ "fill": ["#02a499", "#f2f2f2"], "innerRadius": 28, "radius": 32 }'
                                        data-width="72" data-height="72"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- end row -->
            </div>
        </div>
        <!-- end card -->
    </div>

</div>
<!-- end row -->

<textarea style="display: none" id="listatempdata"><?= json_encode($dataListVen) ?></textarea>

<script>
    $(document).ready(function() {
        new Chart("chart-with-area", {
            type: "line",
            data: {
                labels: getMesAbreLinst("es"),
                datasets: [{
                    label: 'Ventas',
                    data: JSON.parse($("#listatempdata").val()),
                    borderColor: "#626ed4",
                    backgroundColor: "rgba(98,110,212,0.36)",
                    fill: true

                }]
            },

        });
    })
</script>