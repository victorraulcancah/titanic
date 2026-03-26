<?php
$conexion = (new Conexion())->getConexion();

$sql = "SELECT nes.nombre_xml,ne.*,e.ruc as ruc_empresa,ds.nombre as 'nota_nombre', c.datos as 'cliente_ne' FROM notas_electronicas ne
join documentos_sunat ds on ne.tido = ds.id_tido
    join empresas e on ne.id_empresa = e.id_empresa
    join notas_electronicas_sunat nes on ne.nota_id = nes.id_notas_electronicas
join ventas v on ne.id_venta = v.id_venta
join clientes c on v.id_cliente = c.id_cliente
where ne.id_empresa={$_SESSION['id_empresa']} and ne.sucursal='{$_SESSION['sucursal']}'";

$listaNE = $conexion->query($sql);

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
        <div class="col-md-4">
            <div class="float-end d-none d-md-block">

            </div>
        </div>
    </div>
    <div class="clearfix">
        <h6 class="page-title float-end">Ventas</h6>
        <ol class="breadcrumb m-0 float-start">
            <li class="breadcrumb-item"><a href="javascript: void(0);">Facturación</a></li>
            <li class="breadcrumb-item"><a href="/ventas" class="button-link">Ventas</a></li>
            <li class="breadcrumb-item active" aria-current="page">Productos</li>
        </ol>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card" style="border-radius:20px;box-shadow:0 4px 6px -1px rgba(0,0,0,.1),0 2px 4px -1px rgba(0,0,0,.06)" >
            <div class="card-body">
                <div class="clearfix">
                    <h4 class="card-title float-start">Venta de Producto</h4>
            
                    <div class="card-title-desc text-end">
                        <a href="/nota/electronica" class="btn btn-success button-link"><i class="fa fa-plus"></i> Agregar Nota Electronica</a>
                    </div>
            
                </div>
                <!-- <h4 class="card-title">Venta de Producto</h4>

                <div class="card-title-desc text-end">
                    <a href="/nota/electronica" class="btn btn-success button-link"><i class="fa fa-plus"></i> Agregar Nota Electronica</a>
                </div> -->

                <table id="tabla-notaselect" class="table table-bordered table-sm text-center">
                    <thead>
                    <tr>
                        <th></th>
                        <th>Documento</th>
                        <th>Fecha</th>
                        <th>Cliente</th>
                        <th>Total</th>
                        <th>Sunat</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    foreach ($listaNE as $ne){
                        ?>
                        <tr>
                            <td><?=$ne['nota_id']?></td>
                            <td><a target="_blank" href="<?=URL::to('/nota/electronica/pdf/'.$ne['nota_id'].'/'.$ne['nombre_xml'])?>"><?=$ne['nota_nombre']?> | <?=$ne['serie']?>-<?=$ne['numero']?></a></td>
                            <td><?=$ne['fecha']?></td>
                            <td><?=$ne['cliente_ne']?></td>
                            <td><?=$ne['monto']?></td>
                            <td><?=$ne['estado_sunat']?>-<?=$ne['nota_id']?></td>
                            <td><a href="<?=URL::to('files/facturacion/xml/'.$ne['ruc_empresa'].'/'.$ne['nombre_xml'].'.xml')?>" target="_blank" class="btn btn-info btn-sm"><i class="far fa-file"></i></a></td>
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

<script>
    function enviarSunat() {
        alertAdvertencia("Aun se debe configurar para enviarlo a la Sunat")
    }
    $(document).ready(function(){
        $("#tabla-notaselect").DataTable({
            order: [[ 0, "desc" ]],
            columnDefs:[
                {
                    targets: 5,
                    render(data, type, row) {
                        const desData = data.split('-')
                        if (desData[0] =='1'){
                            return '<span class=" badge bg-success">Enviado</span>';
                        }else{
                            var bntSend='<i  data-item="' + desData[1] + '" class="btn-send-sunat btn-sm btn btn-info fas fa-location-arrow"></i>'

                            return '<span class="badge bg-warning">Pendiente</span> '+bntSend;
                        }

                    }
                },
            ]
        })
        $("#tabla-notaselect").on("click",".btn-send-sunat",function (evt) {
            const cod =($(evt.currentTarget).attr('data-item'));
            $("#loader-menor").show()
            _ajax("/ajs/send/sunat/notaelectronica","POST",{cod},
                function (resp) {
                    console.log(resp);
                    if(resp.res){
                        alertExito("Enviado a la sunat")
                        tes();
                    }else{
                        Swal.fire({
                            icon: 'warning',
                            title: "Alerta",
                            html: resp.msg,
                        })
                    }
                }
            )
        })




    })
</script>