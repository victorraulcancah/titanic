<?php
require_once "app/models/GuiaRemision.php";
require_once "app/models/Varios.php";

$c_guia = new GuiaRemision();
$c_varios = new Varios();

$c_guia->setIdEmpresa($_SESSION['id_empresa']);
?>
<!-- start page title -->
<div class="page-title-box">
    <div class="row align-items-center">
        <div class="col-md-8">
            
            <!-- <h6 class="page-title">Guía Remisión</h6>
            <ol class="breadcrumb m-0">
                <li class="breadcrumb-item"><a href="javascript: void(0);">Facturación</a></li>
                <li class="breadcrumb-item active" aria-current="page">Guía Remisión</li>
            </ol> -->
        </div>
        <div class="clearfix">
                <h6 class="page-title float-end">Guía Remisión</h6>
                <ol class="breadcrumb m-0 float-start">
                    <li class="breadcrumb-item"><a href="javascript: void(0);">Facturación</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Guía Remisión</li>
                </ol>
            </div>
        <div class="col-md-4">
            <div class="float-end d-none d-md-block">
                <div hidden class="dropdown">
                    <button class="btn btn-primary  dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="mdi mdi-cog me-2"></i> Settings
                    </button>
                    <div class="dropdown-menu dropdown-menu-end">
                        <a class="dropdown-item" href="#">Action</a>
                        <a class="dropdown-item" href="#">Another action</a>
                        <a class="dropdown-item" href="#">Something else here</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="#">Separated link</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- end page title -->



<div class="row">
    <div class="col-12">
        <div class="card" style="border-radius:20px;box-shadow:0 4px 6px -1px rgba(0,0,0,.1),0 2px 4px -1px rgba(0,0,0,.06)">
            <div class="card-body">

                <!-- <h4 class="card-title"></h4> -->

                <div class="card-title-desc text-end" >
                    <a href="/guia/remision/registrar" class="btn btn-primary button-link "><i class="fa fa-plus"></i> Crear Guía de Remisión</a>

                </div>
               <div class="table-responsive">
                     <table id="datatable" class="table table-bordered text-center table-sm" style="border-spacing: 0; width: 100%;">

                        <thead>
                        <tr>
                            <th>Item</th>
                            <th>Fecha</th>
                            <th>Documento</th>
                            <th>Cliente</th>
                            <th>Factura</th>
                            <th>Sunat</th>
                            <th>Acciones</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                            $item = 0;
                            foreach ($c_guia->verFilas() as $fila) {
                                $doc_guia = "GR | " . $fila['serie'] . "-". $c_varios->zerofill($fila['numero'], 4);
                                $doc_venta = $fila['doc_venta']." | " . $fila['serie_venta'] . "-". $c_varios->zerofill($fila['numero_venta'], 4);
                                $item ++;
                                ?>
                                <tr>
                                    <td class="text-center"><?php echo $item ?></td>
                                    <td class="text-center"><?php echo $c_varios->fecha_mysql_web($fila['fecha_emision'])?></td>
                                    <td><a target="_blank" href="<?= URL::to('/guia/remision/pdf/'.$fila['id_guia_remision'].'/'.$fila['nom_guia_xml']) ?>"><?php echo $doc_guia ?></a></td>
                                    <td><?php echo $fila['datos'] ?></td>
                                    <td class="text-center"><?php echo $doc_venta ?></td>
                                    <td class="text-center"><?=$fila['enviado_sunat']?>-<?=$fila['id_guia_remision']?></td>
                                    <td class="text-center">
                                        <a href="<?=URL::to('files/facturacion/xml/'.$fila['ruc_empresa'].'/'.$fila['nom_guia_xml'].'.xml')?>" target="_blank" class="btn btn-sm btn-success"> <i class="fa fa-file"></i></a>
                                    </td>
                                </tr>
                                    <?php
                            }
                        ?>
                        </tbody>
                    </table>
               </div>

            </div>
        </div>
    </div> <!-- end col -->
</div>


<script>
    function tes(){
        _ajax("/ajs/ventas","POST"
            ,{}
            ,function (resp) {
                //console.log(resp);
                tabla.rows().remove();
                resp.forEach(function(item){
                    tabla.row.add([
                      item.abreviatura+" | "+item.serie+" - "+item.numero,
                        item.fecha,
                        item.documento+" | "+item.datos,
                        (parseFloat(item.total) / 1.18).toFixed(2),
                        (parseFloat(item.total) / 1.18 * 0.18).toFixed(2),
                        (parseFloat(item.total) ).toFixed(2),
                        item.estado,
                        item.id_venta
                    ]).draw(false);
                })
            }
        )
    }
    var tabla;
    $(document).ready(function () {
       tabla = $("#datatable").DataTable({
        "responsive": true,
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

        $("#datatable").on("click",".btn-send-sunat",function (evt) {
            const cod =($(evt.currentTarget).attr('data-item'));
            $("#loader-menor").show()
            _ajax("/ajs/send/sunat/guiaremision","POST",{cod},
                function (resp) {
                    console.log(resp);
                    if(resp.res){
                        alertExito("Enviado a la sunat")
                            .then(function () {
                                location.reload();
                            })

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

