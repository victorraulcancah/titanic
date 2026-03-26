<div class="page-title-box">
    <div class="row align-items-center">
        <!-- <div class="col-md-8">
            <h6 class="page-title">Cobranzas</h6>
            <ol class="breadcrumb m-0">
                <li class="breadcrumb-item"><a href="javascript: void(0);">Facturacion</a></li>
                <li class="breadcrumb-item"><a href="/ventas" class="button-link">Ventas</a></li>
                <li class="breadcrumb-item active" aria-current="page">Productos</li>
            </ol>
        </div> -->
        <div class="clearfix">
            <h6 class="page-title float-end">Total deuda por cliente</h6>
            <ol class="breadcrumb m-0 float-start">
                <li class="breadcrumb-item"><a href="javascript: void(0);">Facturación</a></li>
                <li class="breadcrumb-item"><a href="/deudas" class="button-link">Deudas</a></li>
                <li class="breadcrumb-item active" aria-current="page">Productos</li>
            </ol>
        </div>
        <!-- <div class="col-md-4">
            <div class="float-end d-none d-md-block">

            </div>
        </div> -->
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card" style="border-radius:20px;box-shadow:0 4px 6px -1px rgba(0,0,0,.1),0 2px 4px -1px rgba(0,0,0,.06)">
            <div class="card-header">

                <div class="row">
                    <!-- Sección de totales -->
                    <div class="col-md-8 d-flex align-items-center gap-3">
                        <div class="container">
                            <div class="row">                                
                                <div class="col-md-12 d-flex align-items-center">
                                    <div class="col-md-3 d-grid">
                                        <div class="col-lg-12">
                                            <label for="vendedor" class="form-label form-label-sm fs-7">Cliente</label>
                                            <div class="input-group">
                                                <input type="hidden" id="id_cliente">
                                                <input id="input_datos_cliente" v-model="venta.num_doc" type="text" placeholder="Documento o Nombre" class="form-control form-control-sm" maxlength="11">
                                            </div>
                                        </div>
                                    </div>  
                                    <div class="col-md-3">
                                        <label for="vendedor" class="form-label form-label-sm fs-7">Vendedor</label>
                                        <select id="vendedor" name="vendedor" class="form-select form-select-sm">
                                            <option value="">Seleccionar</option>
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <label for="fecha_inicio" class="form-label form-label-sm fs-7">Fecha inicio</label>
                                        <input type="date" id="fecha_inicio" name="fecha_inicio" class="form-control form-control-sm" value="">
                                    </div>
                                    <div class="col-md-2">
                                        <label for="fecha_fin" class="form-label form-label-sm fs-7">Fecha fin</label>
                                        <input type="date" id="fecha_fin" name="fecha_fin" class="form-control form-control-sm" value="">
                                    </div>                            
                                    <div class="col-md-2">
                                        <label for="camion" class="form-label form-label-sm fs-7">Camion</label>
                                        <select id="camion" name="camion" class="form-select form-select-sm">
                                            <option value="">Todos</option>
                                            <option value="1">Camión 1</option>
                                            <option value="2">Camión 2</option>
                                            <option value="3">Camión 3</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-12 d-flex align-items-center">
                                    <div class="col-md-3">
                                        <label for="input_buscar_proveedor" class="form-label form-label-sm fs-7">Buscar</label>
                                        <input type="text" placeholder="Proveedor" class="form-control ui-autocomplete-input form-control-sm" id="input_buscar_proveedor" autocomplete="off">
                                    </div> 
                                    <div class="col-md-3">
                                        <label for="dias_visita" class="form-label form-label-sm fs-7">Dias de visita</label>
                                        <select class="form-control form-control-sm" id="diasVisita">
                                            <option value="">Selecciona</option>
                                            <option value="lunes">Lunes</option>
                                            <option value="martes">Martes</option>
                                            <option value="miercoles">Miércoles</option>
                                            <option value="jueves">Jueves</option>
                                            <option value="viernes">Viernes</option>
                                            <option value="sabado">Sábado</option>
                                            <option value="domingo">Domingo</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label for="ruta" class="form-label form-label-sm fs-7">Ruta</label>
                                        <select id="ruta" name="ruta" class="form-select form-select-sm">
                                            <option value="">Todos</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label form-label-sm fs-7 invisible">Buscar</label>
                                        <div class="dropdown d-grid">
                                            <button class="btn btn-sm btn-primary dropdown-toggle" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="fa fa-file-pdf"></i> Reporte
                                            </button>
                                            <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                                                <li><a class="dropdown-item" href="#" id="reporteCobros">Reporte Cobros</a></li>
                                                <li><a class="dropdown-item" href="#" id="reporteCobrosVendedor">Cobros por Vendedor</a></li>
                                                <li><a class="dropdown-item" href="#" id="reporteCobrosRuta">Deuda por Ruta</a></li>
                                                <li><a class="dropdown-item" href="#" id="reporteVentas">Ventas por Producto</a></li>
                                                <li><a class="dropdown-item" href="#" id="reporteVentasVendedor">Ventas por Proveedor</a></li>
                                            </ul>
                                        </div>
                                    </div>                        
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Botón de exportar -->
                    <div class="col-md-4 text-end">
                        <a href="/r/cobranzas/reporte/xls" class="btn btn-success">
                            <i class="fa fa-file-excel"></i> Exportar
                        </a>
                    </div>
                </div>

            </div>
            <div class="card-body">

                <h4 class="card-title">Venta de Producto</h4>

                <div class="card-title-desc">

                </div>
                <div class="table-responsive">
                    <table id="datatable" class="table table-bordered text-center table-sm" style="border-collapse: collapse; border-spacing: 0; width: 100%;">

                        <thead>
                            <tr>
                                <th>Cliente</th>
                                <th>Total</th>
                                <th>Pagado</th>
                                <th>Saldo</th>
                                <!-- <th>Detalles</th> -->

                            </tr>
                        </thead>

                    </table>
                </div>

                <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h1 class="modal-title fs-5" id="exampleModalLabel">Modal title</h1>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div id="" class="col-xs-12 col-sm-12 col-md-12 no-padding table-responsive">


                                    <table id="datatableDiasCompras" class="table table-bordered dt-responsive nowrap text-center table-sm" style="border-collapse: collapse; border-spacing: 0; width: 100%;">

                                        <thead>
                                            <tr>
                                                <th style="text-align: center;">Id</th>
                                                <th style="text-align: center;">Monto</th>
                                                <th style="text-align: center;">F. Pago</th>
                                                <th style="text-align: center;">Estado</th>
                                                <th style="text-align: center;">Pago</th>
                                                <th style="text-align: center;">Pagar</th>


                                            </tr>
                                        </thead>

                                    </table>
                                </div>
                            </div>
                            <div class="d-flex">
                                <p>Total: <input type="text" id="total_cuotas" class="border px-2 py-1"></p>
                                <p>Falta pagar: <input type="text" id="restante_total" class="border px-2 py-1"></p>
                                <p>Total Pagado: <input type="text" id="total_pagado" class="border px-2 py-1"></p>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-danger cerrarpagos">Cerrar</button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
    let botonDetalle = null

    function sumarTotal() {
        let total = 0;
        let restante = 0;
        let total_pagado = 0;

        $('#datatableDiasCompras').DataTable().rows().every(function() {
            let row = this.data();
            let estado = row.estado;

            if (estado == '1') {
                const valor = parseFloat($(this.node()).find('.lisopcpavalor').val()) || 0;
                total_pagado += valor;
            }
        });
        $('#total_pagado').val(total_pagado);


    }

    function totalApagar(id) {
        console.log("id_venta:", id); // Verifica si el ID se pasa correctamente

        $.ajax({
            url: _URL + "/ajs/pagar/total/ventas",
            method: "POST",
            data: {
                id_venta: id
            },
            success: function(res) {
                let total = JSON.parse(res).total;
                $('#total_cuotas').val(total);
                totalPagado = parseFloat($('#total_pagado').val());
                totalCuotas = parseFloat($('#total_cuotas').val());;
                restante = totalCuotas - totalPagado;
                $('#restante_total').val(restante);
            }
        })
    }

    $(document).ready(function() {

        //
        $.ajax({
            type: 'GET',
            url: _URL + '/ajs/cuentas/usuarios',
            success: function(resp) {
                $selectVendedores = $('#vendedor');
                $selectVendedores.empty();
                $selectVendedores.append('<option value="">Seleccionar</option>');
                let vendedores = JSON.parse(resp);
                for (let i = 0; i < vendedores.length; i++) {
                    let vendedor = vendedores[i];
                    $selectVendedores.append('<option value="' + vendedor.usuario_id + '">' + vendedor.nombres + '</option>');
                }

            }
        })
        listarRutas();
        function listarRutas() {
            $.ajax({
                url: _URL + '/ajs/admin/cliente/rutas',
                method: 'GET',
                success: function(response) {
                    response = JSON.parse(response);
                    console.log(response);
                    let options = `<option value="">Seleccione</option>`;
                    $.each(response, function(idx, res) {
                        options += `<option value="${res.id_ruta}">${res.id_ruta}</option>`;
                    });
                    $("#ruta").html(options);
                }
            });
        }

        $("#input_datos_cliente").autocomplete({
            source: _URL + "/ajs/buscar/cliente/datos",
            minLength: 2,
            select: function(event, ui) {
                event.preventDefault();
                console.log(ui.item);
                $("#input_datos_cliente").val(ui.item.datos);
                $("#id_cliente").val(ui.item.codigo);
            }
        });
        
        $("#input_buscar_proveedor").autocomplete({
            source: _URL + `/ajs/productos/razon_social`,
            minLength: 2,
            select: function(event, ui) {
                event.preventDefault();
                $("#input_buscar_proveedor").val(ui.item.value);
            }
        });

        obtenerDataFiltroReporte = function (){            
            let id_cliente = $('#id_cliente').val();
            let id_vendedor = $('#vendedor').val();
            let fecha_inicio = $('#fecha_inicio').val();
            let fecha_fin = $('#fecha_fin').val();
            let camion = $('#camion').val();
            let diasVisita = $('#diasVisita').val();
            let ruta = $('#ruta').val();
            let data = {
                id_cliente,
                id_vendedor,
                fecha_inicio,
                fecha_fin,
                camion,
                diasVisita,
                ruta
            }
            return data;
        }

        $(document).on('click','#reporteCobros',function(e){
            e.preventDefault();
            let data = obtenerDataFiltroReporte();
            let filtros = new URLSearchParams(data).toString();
            let url = `${_URL}/reporte/deudas/cobros?${filtros}`;
            window.open(url,'_blank');
        });


        reporteDuedaXVendedor = function (){
            let data = obtenerDataFiltroReporte();
            let filtros = new URLSearchParams(data).toString();
            let url = `${_URL}/reporte/deudas/vendedor?${filtros}`;
            window.open(url,'_blank');
        }

        $(document).on('click','#reporteCobrosVendedor',function(e){
            e.preventDefault();
            let id_cliente = $('#id_cliente').val();
            let id_vendedor = $('#vendedor').val();
            let fecha_inicio = $('#fecha_inicio').val();
            let fecha_fin = $('#fecha_fin').val();
            let camion = $('#camion').val();
            let diasVisita = $('#diasVisita').val();
            let ruta = $('#ruta').val();
            let data = {
                id_cliente,
                id_vendedor,
                fecha_inicio,
                fecha_fin,
                camion,
                diasVisita,
                ruta
            }
            if(id_vendedor==""){
                return Swal.fire({
                    title: 'Seleccione el Vendedor',
                    icon: "error"
                });
            }
            let filtros = new URLSearchParams(data).toString();
            let url = `${_URL}/reporte/deudas/vendedor?${filtros}`;
            window.open(url,'_blank');
        });
        
        $(document).on('click','#reporteCobrosRuta',function(e){
            e.preventDefault();
            let id_cliente = $('#id_cliente').val();
            let id_vendedor = $('#vendedor').val();
            let fecha_inicio = $('#fecha_inicio').val();
            let fecha_fin = $('#fecha_fin').val();
            let camion = $('#camion').val();
            let diasVisita = $('#diasVisita').val();
            let ruta = $('#ruta').val();
            let data = {
                id_cliente,
                id_vendedor,
                fecha_inicio,
                fecha_fin,
                camion,
                diasVisita,
                ruta
            }
            if(ruta==""){
                return Swal.fire({
                    title: 'Seleccione la ruta',
                    icon: "error"
                });
            }
            let filtros = new URLSearchParams(data).toString();
            let url = `${_URL}/reporte/deudas/ruta?${filtros}`;
            window.open(url,'_blank');
        });

        $(document).on('click','#reporteVentas',function(e){
            e.preventDefault();
            let id_vendedor = $('#vendedor').val();
            let fecha_inicio = $('#fecha_inicio').val();
            let fecha_fin = $('#fecha_fin').val();
            let camion = $('#camion').val();
            let diasVisita = $('#diasVisita').val();
            let ruta = $('#ruta').val();
            let data = {
                id_vendedor,
                fecha_inicio,
                fecha_fin,
                ruta,
                camion,
                diasVisita
            }
            if(id_vendedor=="" && ruta==""){
                if(id_vendedor=="" ){
                    return Swal.fire({
                        title: 'Seleccione un vendedor',
                        icon: "error"
                    });
                }else{
                    return Swal.fire({
                        title: 'Seleccione la ruta',
                        icon: "error"
                    });
                }
            }
            let filtros = new URLSearchParams(data).toString();
            let url = `${_URL}/reporte/ventas?${filtros}`;
            window.open(url,'_blank');
        });

        $(document).on('click','#reporteVentasVendedor',function(e){
            e.preventDefault();
            let id_vendedor = $('#vendedor').val();
            let fecha_inicio = $('#fecha_inicio').val();
            let fecha_fin = $('#fecha_fin').val();
            let proveedor = $('#input_buscar_proveedor').val();
            let data = {
                id_vendedor,
                proveedor,
                fecha_inicio,
                fecha_fin,
            }
            if(id_vendedor=="" || proveedor==""){
                if(id_vendedor=="" ){
                    return Swal.fire({
                        title: 'Seleccione un vendedor',
                        icon: "error"
                    });
                }else{
                    return Swal.fire({
                        title: 'Seleccione un proveedor',
                        icon: "error"
                    });
                }
            }
            let filtros = new URLSearchParams(data).toString();
            let url = `${_URL}/reporte/ventas/vendedor?${filtros}`;
            window.open(url,'_blank');
        });

        const datatable = $("#datatable").DataTable({
            order: [
                [0, "desc"]
            ],
            paging: true,
            bFilter: true,
            ordering: true,
            searching: true,
            destroy: true,
            ajax: {
                url: _URL + "/ajs/cuentas/deuda/render",
                method: "POST",
                dataSrc: "",
            },
            language: {
                url: "ServerSide/Spanish.json",
            },
            columns: [{
                    data: "cliente",
                    class: "text-center",
                },
                {
                    data: "total",
                    class: "text-center",
                    render: function(data) {
                        return `<div class="text-center"><div class="btn-group">S/ ${data ?? 0}</div></div>`;
                    },
                },
                {
                    data: "pagado",
                    class: "text-center",
                    render: function(data) {
                        return `<div class="text-center"><div class="btn-group">S/ ${parseFloat(data ?? 0).toFixed(2)}</div></div>`;
                    },
                },
                {
                    data: "saldo",
                    class: "text-center",
                    render: function(data) {
                        return `<div class="text-center"><div class="btn-group">S/ ${parseFloat(data ?? 0).toFixed(2)}</div></div>`;
                    },
                },
            ],
        });



        $("#datatable").on("click", ".btnDetalles ", function(event) {
            botonDetalle = $(event.currentTarget)
            $("#loader-menor").show()
            var table = $("#tablaMaquina").DataTable();
            var trid = $(this).closest("tr").attr("id");
            var id = $(this).data("id");
            var tipo = $(this).data("tipo");
            $("#exampleModal").modal("show");
            $("#exampleModal")
                .find(".modal-title")
                .text("Detalles compra N° " + id);
            $.ajax({
                url: _URL + "/ajas/getAllCuotas/byIdVenta",
                data: {
                    id: id,
                    tipo: tipo
                },
                type: "post",
                success: function(resp) {
                    $("#loader-menor").hide()
                    resp = JSON.parse(resp)
                    console.log(resp);
                    /*    console.log(resp[0]['fecha']); */

                    /*   let vencimiento = resp[0]['fecha']
                      const [year, month, day] = vencimiento.split('-');
                      const vencimientoFecha = [month, day, year].join('/');
                      var today = new Date();
                      var dd = String(today.getDate()).padStart(2, '0');
                      var mm = String(today.getMonth() + 1).padStart(2, '0'); //January is 0!
                      var yyyy = today.getFullYear();
                      today = mm + '/' + dd + '/' + yyyy;
                      const dateToday = new Date(today);
                      const dateVencimiento = new Date(vencimientoFecha);
                      const diffTime = Math.abs(dateToday - dateVencimiento);
                      const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                      console.log(today);
                      console.log('vencimient ' + vencimientoFecha); */
                    datatableDiasCompras = $("#datatableDiasCompras").DataTable({

                        paging: true,
                        bFilter: true,
                        ordering: true,
                        searching: true,
                        destroy: true,
                        data: resp,
                        language: {
                            url: "ServerSide/Spanish.json",
                        },
                        initComplete: function(settings, json) {
                            sumarTotal();
                            totalApagar(id);
                        },
                        columns: [{
                                data: "dias_venta_id",
                                class: "text-center",
                            },
                            {
                                data: "monto",
                                class: "text-center",
                                render: function(data, type, row) {
                                    let IsDisabled = row.estado == 1 ? 'disabled' : '';
                                    return `<input  data-tipo="${row.tipo_doc}" data-cod="${row.dias_venta_id}" class="lisopcpavalor" type="text" value="${data}" ${IsDisabled}>`
                                }
                            },
                            {
                                data: "fecha",
                                class: "text-center",
                                render: function(data, type, row) {
                                    const fecha = data ? data.fecha : '';
                                    return `<input  data-tipo="${row.tipo_doc}" data-cod="${row.dias_venta_id}"  class="lisopcpafecha" type="date" value="${row.fecha}">`
                                }
                            },
                            {
                                data: null,
                                class: "text-center",
                                render: function(data, type, row) {

                                    let vencimiento = row.fecha
                                    const [year, month, day] = vencimiento.split('-');
                                    const vencimientoFecha = [month, day, year].join('/');
                                    var today = new Date();
                                    var dd = String(today.getDate()).padStart(2, '0');
                                    var mm = String(today.getMonth() + 1).padStart(2, '0'); //January is 0!
                                    var yyyy = today.getFullYear();
                                    today = mm + '/' + dd + '/' + yyyy;
                                    if ((today > vencimientoFecha) && row.estado == '0') {
                                        return `<div class="text-center">
              <div class="btn-group"><span class="badge bg-danger">Vencido</span></div></div>`;
                                    } else if ((today < vencimientoFecha || vencimientoFecha == today) && row.estado == '0') {
                                        return `<div class="text-center">
              <div class="btn-group"><span class="badge bg-danger">Vigente</span></div></div>`;
                                    } else if (row.estado == '1') {
                                        return `<div class="text-center">
              <div class="btn-group"><span class="badge bg-success">Pagado</span></div></div>`;
                                    }



                                },
                            },
                            {
                                data: null,
                                class: "text-center",
                                render: function(data, type, row) {

                                    let listaOpc = ["Plin", "Yape", "Efectivo", "Tarjeta", "Transferencia"]
                                        .map(item => {
                                            if (item == row.tipo_pago) return `<option selected>${item}</option>`
                                            else return `<option>${item}</option>`

                                        })

                                    return `
                                    <select data-tipo="${row.tipo_doc}" data-cod="${row.dias_venta_id}" class="lisopcpa">
                                    <option disabled selected>Elija Uno</option>
${listaOpc.join("")}
</select>
                                    `
                                }
                            },
                            {
                                data: null,
                                class: "text-center",
                                render: function(data, type, row) {
                                    
                                    if (row.estado == '0') {
                                        return `<div class="text-center">
                                            <div class="btn-group"><button  data-tipo="${row.tipo_doc}" data-id="${Number(
                                            row.dias_venta_id
                                        )}" class="btn btn-success btnPagar btn-sm"><i class="fas fa-money-bill"></i> </button></div></div>`;
                                    }
                                    if (row.estado == '1') {
                                        return `<div class="text-center">
                                            <div class="btn-group"></div></div>`;
                                    }
                                },
                            },

                        ],
                    });


                },
            })
        });
        // $("#datatableDiasCompras").on("change",".lisopcpavalor",function (event) {
        //     console.log($(event.currentTarget))
        //     const codPag=$(event.currentTarget).data("cod");
        //     const tipoPag=$(event.currentTarget).data("tipo");
        //     let valor=$(event.currentTarget).val();
        //     valor = valor.length==0?'0':valor
        //     console.log(codPag,tipoPag,valor)
        //     _post("/ajs/set/state/pago/cv",{codPag,tipoPag,valor,col:'v'},(data)=>{
        //         console.log(data)
        //     })

        // })
        $("#datatableDiasCompras").on("change", ".lisopcpafecha", function(event) {
            console.log($(event.currentTarget))
            const codPag = $(event.currentTarget).data("cod");
            const tipoPag = $(event.currentTarget).data("tipo");
            const valor = $(event.currentTarget).val();
            console.log(codPag, tipoPag, valor)
            _post("/ajs/set/state/pago/cv", {
                codPag,
                tipoPag,
                valor,
                col: 'f'
            }, (data) => {
                console.log(data)
            })

        })
        $("#datatableDiasCompras").on("change", ".lisopcpa", function(event) {
            const codPag = $(event.currentTarget).data("cod");
            const tipoPag = $(event.currentTarget).data("tipo");
            const valor = $(event.currentTarget).val();
            console.log(codPag, tipoPag)
            _post("/ajs/set/state/pago/cv", {
                codPag,
                tipoPag,
                valor,
                col: 'p'
            }, (data) => {
                console.log(data)
            })

        })
        $("#datatableDiasCompras").on("click", ".btnPagar ", function(event) {

            var table = $("#tablaMaquina").DataTable();
            var trid = $(this).closest("tr").attr("id");
            var id = $(this).data("id");
            var tipo = $(this).data("tipo");
            var fila = $(this).closest("tr");
            var monto = fila.find('.lisopcpavalor').val();
            Swal.fire({
                title: '¿Desea pagar la cuota N° ' + id + ' ? ',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Si'
            }).then((result) => {
                if (result.isConfirmed) {
                    $("#loader-menor").show()
                    $.ajax({
                        type: 'POST',
                        url: _URL + '/ajs/pagar/cuota/ventas',
                        data: {
                            id: id,
                            tipo,
                            monto
                        },
                        success: function(resp) {
                            $("#loader-menor").hide();
                            let data = JSON.parse(resp)
                            console.log(data);
                            botonDetalle.click()
                            /*  */
                        }
                    });
                }
            })
        })        

        $('.cerrarpagos').click(function() {
            $('#exampleModal').modal('hide');
            datatable.ajax.reload();
        })
    })
</script>