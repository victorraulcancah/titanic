<?php
require_once "app/models/GuiaRemision.php";
require_once "app/models/Varios.php";

$c_guia = new GuiaRemision();
$c_varios = new Varios();

$c_guia->setIdEmpresa($_SESSION['id_empresa']);
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
        <div class="col-md-8">
            <h6 class="page-title">Ventas</h6>
            <ol class="breadcrumb m-0">
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
    <input type="hidden" value="<?=$emprCod?>" id="empresacod">
    <div class="col-12">
        <div class="card">
            <div class="card-body">
                <div class="col-md-12 text-end m-3">

                    <button id="send-personal" type="button" class="btn btn-primary">Envio Sunat</button>
                    <a href="/administrarempresas" class="btn btn-warning">Regresar</a>
                </div>

                <h4 class="card-title">Ventas <i class="fas fa-sync"></i></h4>
                <table id="table-ventas" class="table table-bordered dt-responsive nowrap text-center table-sm table-hover">
                    <thead>
                    <tr>
                        <th></th>
                        <th>Documento</th>
                        <th>Fecha V.</th>
                        <th>Cliente</th>
                        <th>Sub. Total</th>
                        <th>IGV</th>
                        <th>Total</th>
                        <th>Sunat</th>
                        <th>Estado</th>
                        <th></th>
                    </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card">
            <div class="card-body">

                <h4 class="card-title">Guías Remisión <i class="fas fa-sync"></i></h4>
                <table id="table-guias" class="table table-bordered dt-responsive nowrap text-center table-sm table-hover">
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

    <div class="col-12">
        <div class="card">
            <div class="card-body">

                <h4 class="card-title">Notas Electronicas <i class="fas fa-sync"></i></h4>
                <table id="table-notas" class="table table-bordered dt-responsive nowrap text-center table-sm table-hover">
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
<div class="modal fade" id="modal-send-resumen" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Resumen Diario Personalizado</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form>
                <div class="modal-body text-center">
                    <div class="col-md-12 row">
                        <div class="mb-3 col-md-6">
                            <label class="form-label">Fecha generacion</label>
                            <input id="fechageneracionresumen" type="date" value="<?=date('Y-m-d')?>" class="form-control" >
                        </div>
                        <div class="mb-3 col-md-6">
                            <label class="form-label">Fecha Resumen</label>
                            <input id="fecharesumen" type="date" value="<?=date('Y-m-d')?>" class="form-control" >
                        </div>
                        <div class="mb-3 col-md-6">
                            <label class="form-label">Correlativo 1</label>
                            <input id="correlativo1" type="text" class="form-control" value="001">
                        </div>
                        <div class="mb-3 col-md-6">
                            <label class="form-label">Correlativo 2</label>
                            <input id="correlativo2" type="text" class="form-control" value="001">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button  id="form-send-comu-baja" type="button" class="btn btn-primary" >Enviar Cominicacion Baja</button>
                    <button  id="form-send-resumen" type="button" class="btn btn-primary" >Enviar Resumen</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </form>

        </div>
    </div>
</div>

<div class="modal fade" id="modalImprimirComprobante" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Imprimir Comprobante</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <a id="ce-t-a4" href="#" target="_blank" class="btn btn-primary"><i class="fa fa-file-pdf"></i> Hoja A4</a>
                <a id="ce-t-8cm" href="#" target="_blank" class="btn btn-info"><i class="fas fa-file-invoice"></i> Voucher 8cm</a>
                <a id="ce-t-5_6cm" href="#" target="_blank" class="btn btn-info"><i class="fas fa-file-invoice"></i> Voucher 6.5cm</a>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>


<script>
    function tes() {
        $("#loader-menor").show()
        _ajax("/ajas/ventas/porempresa", "POST", {
            empresa:$("#empresacod").val(),
            sucursal:'1'
        }, function(resp) {
            //console.log(resp);
            tabla.rows().remove();
            resp.forEach(function(item) {
                tabla.row.add([
                    item.cod_v,
                    item.abreviatura + " | " + item.serie + " - " + item.numero,
                    item.fecha_emision,
                    item.documento + " | " + item.datos,
                    (parseFloat(item.total) / 1.18).toFixed(2),
                    (parseFloat(item.total) / 1.18 * 0.18).toFixed(2),
                    (parseFloat(item.total)).toFixed(2),
                    item.enviado_sunat+"-"+item.id_tido,
                    item.estado,
                    item.id_venta
                ]).draw(false);
            })
        })
    }
    var tabla;
    var listaIdesVentas=[];
    $(document).ready(function() {
        $("#table-notas").DataTable({
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

        $("#table-guias").DataTable({
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
        $("#form-send-comu-baja").click(function(evt){
            if(listaIdesVentas.length>0){
                $("#loader-menor").show();
                _post("/ajas/ventas/porempresa/sendsunatcomubaja",{
                        fechagen:$("#fechageneracionresumen").val(),
                        fecharesumen:$("#fecharesumen").val(),
                        correlativo1:$("#correlativo1").val(),
                        correlativo2:$("#correlativo2").val(),
                        empresa:$("#empresacod").val(),
                        boletas:JSON.stringify(listaIdesVentas)
                    },
                    function (resp) {
                        console.log(resp);
                    }
                )
            }else{
                alertAdvertencia("No hay boletas seleccionadas")
            }
        })
        $("#form-send-resumen").click(function(evt){
            if(listaIdesVentas.length>0){
                $("#loader-menor").show();
                _post("/ajas/ventas/porempresa/sendsunatresumen",{
                        fechagen:$("#fechageneracionresumen").val(),
                        fecharesumen:$("#fecharesumen").val(),
                        correlativo1:$("#correlativo1").val(),
                        correlativo2:$("#correlativo2").val(),
                        empresa:$("#empresacod").val(),
                        boletas:JSON.stringify(listaIdesVentas)
                    },
                    function (resp) {
                        console.log(resp);
                    }
                )
            }else{
                alertAdvertencia("No hay boletas seleccionadas")
            }
        })

        $("#send-personal").click(function () {
            $("#modal-send-resumen").modal("show");
        })
        
        tabla =  $("#table-ventas").DataTable({
            order: [[ 0, "desc" ]],
            columnDefs: [
                {
                    targets: 0,
                    render(data, type, row) {
                        var dataR = row[7].split("-")
                        //console.log(row);
                        if ((dataR[1] == '1' || dataR[1] == '2') && dataR[0] == '0'){
                            return `<label><input value="${data}" type="checkbox" class="chek-bolt"> ${data}</label>`;
                        }else{return  data}

                    }
                },
                {
                    targets: 1,
                    render(data, type, row) {
                        return '<a class="btn-info-vent" target="_blank" href="' + row[0] + '">' + data + '</a>';
                    }
                },
                {
                    targets: 7,
                    render(data, type, row) {
                        data = data.split("-")
                        var desData = row[9].split('--');
                        if (!(desData[1]=='-')){
                            if (data[0] =='1'){
                                return '<span class=" badge bg-success">Enviado</span>';
                            }else{
                                var bntSend=''
                                if (row[8] == 1 && data[1] =='2') {
                                    bntSend='<i  data-venta="' + desData[0] + '" class="btn-send-sunat btn-sm btn btn-info fas fa-location-arrow"></i>'
                                }
                                return '<span class="badge bg-warning">Pendiente</span> '+bntSend;
                            }
                        }
                        return '';

                    }
                },
                {
                    "targets": 8,
                    "render": function(data, type, row, meta) {
                        //console.log(data, type, row, meta)
                        if (data == 1) {
                            return '<span class="badge bg-success">Normal</span>'
                        } else if (data == 2) {
                            return '<span class="badge bg-danger">Anulado</span>'
                        }
                        return data;
                    }
                },
                {
                    targets: 9,
                    render: function(data, type, row, meta) {
                        //console.log(data)
                        if (row[8] == 1) {
                            var desData = data.split('--');
                            var stpan = '<span id="'+row[0]+'-nom-xml" style="display: none">'+desData[1]+'</span>'
                            return stpan+
                                '<a '+(desData[1]=='-'?'hidden':'')+' href="' + _URL + '/files/facturacion/xml/<?= $_SESSION['ruc_empr'] ?>/' + desData[1] + '.xml" target="_blank" class="btn btn-sm btn-info" alt="ver archivo XML" title="ver archivo XML"> <i class="fa fa-file"></i></a> ' +
                                '<button type="button" data-venta="' + desData[0] + '" class="btn btn-sm btn-primary btn-detalle-vent" id="detalleVentaA" alt="Ver Detalle" title="Ver Detalle"><i class="fa fa-bars"></i></button> ' +
                                '<button type="button" data-venta="' + desData[0] + '" class="btn-regenxml-vent btn btn-sm btn-info" alt="Anular Venta" title="Anular Venta"><i class="fas fa-sync"></i></button>';
                        } else {
                            return '';
                        }

                    }
                }
            ]
        })
        tes()

        $("#table-ventas").on("click", ".chek-bolt", function(evt) {
            const input = $(evt.currentTarget)
            if(input.is(':checked')){
                listaIdesVentas.push(input.val());
            }else{
                var indx = listaIdesVentas.indexOf(input.val())
                if (indx > -1) {
                    listaIdesVentas.splice(indx,1)
                }
            }
        })
        $("#table-ventas").on("click", ".btn-regenxml-vent", function(evt) {
            evt.preventDefault();
            const venta= $(evt.currentTarget).data("venta")
            console.log(venta);
            Swal.fire({
                title: '¿Quieres Generar de nuevo el XML?',
                showDenyButton: false,
                showCancelButton: true,
                confirmButtonText: 'Continuar',
                denyButtonText: `Cancelar`,
            }).then((result) => {
                /* Read more about isConfirmed, isDenied below */
                if (result.isConfirmed) {
                    $("#loader-menor").show();
                    _post("/ajas/ventas/porempresa/regenxml",{venta},
                        function (resp) {
                            console.log(resp);
                            if(resp.res){
                                alertExito("Generado y acualizado")
                            }else{
                                alertAdvertencia("No se logro generar ni actualizar")
                            }
                        }
                    )
                }
            })

        })
        $("#table-ventas").on("click", ".btn-info-vent", function(evt) {
            evt.preventDefault();
            const iventa = $(evt.currentTarget).attr('href');
            $("#modalImprimirComprobante").modal("show");
            $("#ce-t-a4").attr("href",_URL+"/venta/comprobante/pdf/"+iventa+'/'+$("#"+iventa+"-nom-xml").text())
            $("#ce-t-8cm").attr("href",_URL+"/venta/pdf/voucher/8cm/"+iventa+'/'+$("#"+iventa+"-nom-xml").text())
            $("#ce-t-5_6cm").attr("href",_URL+"/venta/pdf/voucher/5.6cm/"+iventa+'/'+$("#"+iventa+"-nom-xml").text())

            //console.log($("#"+iventa+"-nom-xml").text());
        })

        $("#table-ventas").on("click",".btn-send-sunat",function (evt) {
            const cod =($(evt.currentTarget).attr('data-venta'));
            $("#loader-menor").show()
            _ajax("/ajas/ventas/porempresa/sendsunat","POST",{cod},
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
