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
            <h6 class="page-title float-end">Cobranzas</h6>
            <ol class="breadcrumb m-0 float-start">
                <li class="breadcrumb-item"><a href="javascript: void(0);">Facturación</a></li>
                <li class="breadcrumb-item"><a href="/ventas" class="button-link">Ventas</a></li>
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
                <!-- agregando 10/04/2025 -->
                <div class="row align-items-end g-3">
                    <!-- Filtros -->
                    <div class="col-md-6">
                        <div class="row g-2">
                            <div class="col-md-3">
                                <label for="vendedor" class="form-label form-label-sm fs-7">Vendedor</label>
                                <select id="vendedor" name="vendedor" class="form-select form-select-sm">
                                    <option value="">Todos</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="fecha_inicio" class="form-label form-label-sm fs-7">Fecha inicio</label>
                                <input type="date" id="fecha_inicio" name="fecha_inicio" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-3">
                                <label for="fecha_fin" class="form-label form-label-sm fs-7">Fecha fin</label>
                                <input type="date" id="fecha_fin" name="fecha_fin" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-3 d-grid">
                                <label class="form-label form-label-sm fs-7 invisible">Buscar</label>
                                <button type="button" class="btn btn-sm btn-primary" onclick="buscarCobranzas()">
                                    <i class="fa fa-search"></i> Buscar
                                </button>
                            </div>
                            <div class="col-md-3">
                                <label for="camion" class="form-label form-label-sm fs-7">Camion</label>
                                <select id="camion" name="camion" class="form-select form-select-sm">
                                    <option value="">Todos</option>
                                    <option value="1">Camión 1</option>
                                    <option value="2">Camión 2</option>
                                    <option value="3">Camión 3</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="dias_visita" class="form-label form-label-sm fs-7">Dias de visita</label>
                                <select class="form-control" id="diasVisita">
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
                        </div>
                    </div>

                    <!-- Resultados y Exportar -->
                    <div class="col-md-6">
                        <div class="d-flex justify-content-end align-items-center gap-4 flex-wrap">
                            <div class="text-end">
                                <label class="form-label form-label-sm fs-7 mb-0">Total</label>
                                <div><span id="total" class="fw-bold text-primary fs-6">S/ 0.00</span></div>
                            </div>
                            <div class="text-end">
                                <label class="form-label form-label-sm fs-7 mb-0">Pagado</label>
                                <div><span id="pagado" class="fw-bold text-success fs-6">S/ 0.00</span></div>
                            </div>
                            <div class="text-end">
                                <label class="form-label form-label-sm fs-7 mb-0">Saldo</label>
                                <div><span id="saldo" class="fw-bold text-danger fs-6">S/ 0.00</span></div>
                            </div>
                            <div>
                                <button href="#" class="btn btn-success btn-sm" onclick="pdf()">
                                    <i class="fa fa-file-excel"></i> Exportar
                                </button>
                            </div>
                        </div>
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
                                <th>Id</th>
                                <th>Codigo</th>
                                <th>F. Emision</th>
                                <th>F. Vencimiento</th>
                                <th>Vendedor</th>
                                <th>Cliente</th>
                                <th>Mercado</th>
                                <th>Total</th>
                                <th>Pagado</th>
                                <th>Saldo</th>
                                <th>Situacion</th>
                                <th>Dias V.</th>
                                <th>Cuotas</th>
                                <th>Productos</th>
                                <!-- <th>Editar</th> -->
                            </tr>
                        </thead>

                    </table>
                </div>
                <div class="modal fade" id="modalProductos" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h1 class="modal-title fs-5" id="exampleModalLabel">Modal title</h1>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div id="" class="col-xs-12 col-sm-12 col-md-12 no-padding table-responsive">


                                    <table id="datatableProductos" class="table table-bordered dt-responsive nowrap text-center table-sm" style="border-collapse: collapse; border-spacing: 0; width: 100%;">

                                        <thead>
                                            <tr>
                                                <th style="text-align: center;">Id</th>
                                                <th style="text-align: center;">Producto</th>
                                                <th style="text-align: center;">Cantidad</th>
                                                <th style="text-align: center;">Precio</th>
                                                <th style="text-align: center;">Total</th>
                                            </tr>
                                        </thead>

                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
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
                                    <h4 id="title-ciente-cuotas"></h4>

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
                                <button type="button" class="btn btn-primary" id="btnAgregarPago"><i class="fas fa-plus"></i> Agregar Pago</button>
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
    let id_usuario = <?= $_SESSION['usuario_fac'] ?>;
    let id_rol = <?= $_SESSION['rol'] ?>;
    let botonDetalle = null

    function pdf() {
        const data = {
            id_usuario: $('#vendedor').val(),
            fecha_inicio: $('#fecha_inicio').val(),
            fecha_fin: $('#fecha_fin').val(),
            camion: $('#camion').val(),
            diasVisita: $('#diasVisita').val(),
            ruta: $('#ruta').val()
        };

        fetch(_URL + "/ajs/cobranzas/pdf", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    id_usuario: $('#vendedor').val(),
                    fecha_inicio: $('#fecha_inicio').val(),
                    fecha_fin: $('#fecha_fin').val(),
                    camion: $('#camion').val(),
                    diasVisita: $('#diasVisita').val(),
                    ruta: $('#ruta').val()
                })
            })
            .then(response => response.blob()) // PDF será un blob
            .then(blob => {
                const url = window.URL.createObjectURL(blob);
                window.open(url, '_blank'); // Abre el PDF en una nueva ventana
            })
            .catch(error => console.error('Error:', error));
    }

    function sumarTotal() {
        let total = 0;
        let restante = 0;
        let total_pagado = 0;
        // agregando 10/04/2025
        let datatable = $('#datatable').DataTable();
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

    function totalApagar(id, tipo) {
        console.log("id_venta:", id); // Verifica si el ID se pasa correctamente

        $.ajax({
            url: _URL + "/ajs/pagar/total/ventas",
            method: "POST",
            data: {
                id_venta: id,
                tipo
            },
            success: function(res) {
                let total = JSON.parse(res).total;
                $('#total_cuotas').val(total);
                totalPagado = parseFloat($('#total_pagado').val());
                totalCuotas = parseFloat($('#total_cuotas').val());
                restante = (totalCuotas - totalPagado).toFixed(2);
                $('#restante_total').val(restante);
            }
        })
    }
    // agregando 10/04/2025
    function buscarCobranzas() {
        let id_usuario = $('#vendedor').val();
        let fecha_inicio = $('#fecha_inicio').val();
        let fecha_fin = $('#fecha_fin').val();
        let camion = $('#camion').val();
        let diasVisita = $('#diasVisita').val();
        let ruta = $('#ruta').val();

        $.ajax({
            url: _URL + "/ajs/cobranzas/buscar",
            method: "POST",
            data: {
                id_usuario: id_usuario,
                fecha_inicio: fecha_inicio,
                fecha_fin: fecha_fin,
                camion: camion,
                diasVisita: diasVisita,
                ruta: ruta
            },
            success: function(res) {
                try {
                    let data = res;

                    if (Array.isArray(data)) {
                        // Aplicar el filtro aquí también
                        const datosFiltrados = data.filter(row => {
                            const total = parseFloat(row.total);
                            const pagado = parseFloat(row.pagado);
                            const diferencia = Math.abs(total - pagado);
                            return diferencia > 0.0000001;
                        });

                        const datatable = $('#datatable').DataTable();
                        datatable.clear();
                        datatable.rows.add(datosFiltrados).draw(); // Usar datosFiltrados
                        sumarTotales();

                    } else {
                        console.warn("La respuesta no es un arreglo:", data);
                    }
                } catch (e) {
                    console.error("Error al parsear JSON:", e);
                    console.log("Respuesta recibida:", res);
                }
            },
            error: function(err) {
                console.error("Error en la petición AJAX:", err);
            }
        });
    }
    // agregando 10/04/2025
    function sumarTotales() {
        let total = 0;
        let pagado = 0;
        let saldo = 0;

        const data = datatable.rows({
            search: "applied"
        }).data(); // incluye filtrado si está activo

        for (let i = 0; i < data.length; i++) {
            total += parseFloat(data[i].total ?? 0);
            pagado += parseFloat(data[i].pagado ?? 0);
            saldo += parseFloat(data[i].saldo ?? 0);
        }

        // Mostrar resultados en los spans
        $("#total").text(`S/ ${total.toFixed(2)}`);
        $("#pagado").text(`S/ ${pagado.toFixed(2)}`);
        $("#saldo").text(`S/ ${saldo.toFixed(2)}`);
    }
    $(document).ready(function() {
        listarMercados();
        listarRutas();

        function listarMercados() {
            $.ajax({
                url: '/ajs/admin/cliente/mercados',
                method: 'GET',
                success: function(response) {
                    response = JSON.parse(response);
                    console.log(response);
                    let options = `<option value="">Selecciona</option>`;
                    $.each(response, function(idx, res) {
                        options += `<option value="${res.mercado}">${res.mercado}</option>`;
                    });
                    $("#mercado").html(options);
                }
            });
        }

        function listarRutas() {
            $.ajax({
                url: 'ajs/admin/cliente/rutas',
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


        /* $.ajax({
             type: 'POST',
             url: _URL + '/ajs/cuentas/cobrar/render',
             success: function(resp) {

                 console.log(JSON.parse(resp));
             }
         });*/
        // agregando 10/04/2025
        $.ajax({
            type: 'GET',
            url: _URL + '/ajs/cuentas/usuarios',
            success: function(resp) {
                $selectVendedores = $('#vendedor');
                $selectVendedores.empty();
                $selectVendedores.append('<option value="">Todos</option>');
                let vendedores = JSON.parse(resp);
                for (let i = 0; i < vendedores.length; i++) {
                    let vendedor = vendedores[i];
                    $selectVendedores.append('<option value="' + vendedor.usuario_id + '">' + vendedor.nombres + '</option>');
                }

            }
        })
        // agregando 10/04/2025
        $.ajax({
            url: _URL + "/ajs/cuentas/cobrar/render",
            method: "POST",
            dataType: "json", // Asegúrate de que la respuesta sea JSON
            success: function(data) {
                console.log("Datos recibidos:", data);
                
                // Filtrar los datos para incluir solo los que no están totalmente pagados
                const datosFiltrados = data.filter(row => {
                    const total = parseFloat(row.total);
                    const pagado = parseFloat(row.pagado);
                    const diferencia = Math.abs(total - pagado);
                    return diferencia > 0.0000001;
                });

                // Inicializar DataTables con los datos filtrados
                datatable = $("#datatable").DataTable({
                    order: [], // Sin ordenamiento inicial, usar el del backend
                    columnDefs: [{
                        targets: 2, // Columna fecha
                        type: "date"
                    }],
                    paging: true,
                    bFilter: true,
                    ordering: true,
                    searching: true,
                    destroy: true,
                    data: datosFiltrados, // Usar los datos filtrados aquí
                    language: {
                        url: "ServerSide/Spanish.json",
                    },
                    columns: [{
                            data: "id_venta",
                            class: "text-center",
                        },
                        {
                            data: "factura",
                            class: "text-center",
                        },
                        {
                            data: "fecha_emision",
                            class: "text-center",
                        },
                        {
                            data: "fecha_emision",
                            class: "text-center",
                        },
                        {
                            data: "vendedor",
                            class: "text-center",
                        },
                        {
                            data: "cliente",
                            class: "text-center",
                        },
                        {
                            data: "mercado",
                            class: "text-center",
                            // visible: false
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
                        {
                            data: null,
                            class: "text-center",
                            render: function(data, type, row) {
                                let vencimiento = row.fecha_vencimiento ?? row.fecha_emision
                                if (vencimiento) {
                                    const [year, month, day] = vencimiento.split('-');
                                    const vencimientoFecha = [month, day, year].join('/');
                                    var today = new Date();
                                    var dd = String(today.getDate()).padStart(2, '0');
                                    var mm = String(today.getMonth() + 1).padStart(2, '0'); //January is 0!
                                    var yyyy = today.getFullYear();
                                    today = mm + '/' + dd + '/' + yyyy;

                                    if ((parseFloat(row.total).toFixed(3) == parseFloat(row.pagado).toFixed(3))) {
                                        return `<div class="text-center">
                                    <div class="btn-group"><span class="badge bg-success">Pagado</span></div></div>`;
                                    } else if ((parseFloat(row.total).toFixed(3) > parseFloat(row.pagado).toFixed(3)) && today > vencimientoFecha) {
                                        return `<div class="text-center">
                                    <div class="btn-group"><span class="badge bg-danger">Vencido</span></div></div>`;
                                    } else if ((parseFloat(row.total).toFixed(3) > parseFloat(row.pagado).toFixed(3)) && today < vencimientoFecha) {
                                        return `<div class="text-center">
                                    <div class="btn-group"><span class="badge bg-warning">Pendiente</span></div></div>`;
                                    } else {
                                        return `<div class="text-center">
                                    <div class="btn-group"><span class="badge bg-warning">Pendiente</span></div></div>`;
                                    }
                                }
                            },
                        },
                        {
                            data: null,
                            class: "text-center",
                            render: function(data, type, row) {
                                let vencimiento = row.fecha_vencimiento ?? row.fecha_emision
                                if (vencimiento) {
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
                                    /* console.log(diffDays); */
                                    if (today > vencimientoFecha) {
                                        return `<div class="text-center">
                                    <div class="btn-group"><span class="badge bg-danger">${diffDays}</span></div></div>`;
                                    } else {
                                        return `<div class="text-center">
                                    <div class="btn-group"><span class="badge bg-success">0</span></div></div>`;
                                    }
                                }
                            },
                        },
                        {
                            data: null,
                            class: "text-center",
                            render: function(data, type, row) {
                                return `<div class="text-center">
                                        <div class="btn-group"><button data-tipo="${row.tipo_co}"  data-id="${Number(
                                            row.id_venta
                                        )}" class="btn btn-success btnDetalles btn-sm"><i class="fa fa-eye"></i> </button></div></div>`;
                            },
                        },
                        // agregando 10/04/2025
                        {
                            data: null,
                            class: "text-center",
                            render: function(data, type, row) {
                                return `<div class="text-center">
                                        <div class="btn-group"><button data-id="${Number(
                                            row.id_venta
                                        )}" class="btn btn-success btnDetallesProductos btn-sm"><i class="fa fa-eye"></i> </button></div></div>`;
                            },
                        },
                        /* {
                            data: null,
                            class: "text-center",
                            render: function(data, type, row) {
                                // Solo mostrar botón de editar para cotizaciones (tipo_co = 'c')
                                if (row.tipo_co === 'c') {
                                    return `<div class="text-center">
                                            <div class="btn-group">
                                                <a href="/cotizaciones/edt/${row.id_venta}" class="btn btn-primary btn-sm button-link">
                                                    <i class="fa fa-edit"></i>
                                                </a>
                                            </div>
                                        </div>`;
                                } else {
                                    return '<div class="text-center">-</div>';
                                }
                            },
                        }, */
                    ],
                });
            },
            error: function(xhr, status, error) {
                console.error("Error al cargar los datos:", error);
                console.error("Status:", status);
                console.error("Response Status:", xhr.status);
                console.error("Response Text:", xhr.responseText);
                
                // Mostrar mensaje al usuario
                alert("Error al cargar los datos de cobranzas. Revisa la consola para más detalles.");
            }
        });
        // agregando 10/04/2025
        $("#datatable").on("click", ".btnDetallesProductos", function(event) {
            var botonDetalle = $(event.currentTarget);
            $("#loader-menor").show();
            var table = $("#tablaMaquina").DataTable();
            var trid = $(this).closest("tr").attr("id");
            var id = $(this).data("id");

            console.log("ID Venta:", id);

            $("#modalProductos").modal("show");
            $("#modalProductos")
                .find(".modal-title")
                .text("Detalles compra N° " + id);

            $.ajax({
                headers: {
                    'token-app': localStorage.getItem("_token")
                },
                url: _URL + "/ajas/getAllProductos/byIdVenta",
                type: "POST",
                data: {
                    id: id
                },
                success: function(resp) {
                    $("#loader-menor").hide();

                    let data = JSON.parse(resp);

                    // Destruye la tabla si ya está inicializada
                    if ($.fn.DataTable.isDataTable("#datatableProductos")) {
                        $("#datatableProductos").DataTable().clear().destroy();
                    }

                    $("#datatableProductos").DataTable({
                        data: data,
                        columns: [{
                                data: "id_producto",
                                className: "text-center"
                            },
                            {
                                data: "descripcion",
                                className: "text-center"
                            },
                            {
                                data: "cantidad",
                                className: "text-center",
                                render: function(data, type, row) {
                                    return `
                                <div class="text-center">
                                    ${(row.cantidad*row.presenta_cnt).toFixed(2)}
                                </div>`;
                                }
                            },
                            {
                                data: "precio",
                                className: "text-center"
                            },
                            {
                                data: "total",
                                className: "text-center",
                                render: function(data, type, row) {
                                    return `
                                <div class="text-center">
                                    <div class="btn-group">
                                        <span class="badge bg-success">${data}</span>
                                    </div>
                                </div>`;
                                }
                            }
                        ]
                    });
                },
                error: function(xhr, status, error) {
                    console.error("Error en AJAX:", error);
                    $("#loader-menor").hide();
                }
            });
        });


        $("#datatable").on("click", ".btnDetalles ", function(event) {
            botonDetalle = $(event.currentTarget)
            $("#loader-menor").show()
            var table = $("#tablaMaquina").DataTable();
            var tr = $(this).closest("tr")[0];
            var td = tr.querySelectorAll('td')[5];
            var td_texto = td.innerHTML;
            var cliente = td_texto.split('|')[1];
            var trid = $(this).closest("tr").attr("id");
            var id = $(this).data("id");
            var tipo = $(this).data("tipo");
            $("#title-ciente-cuotas").html(`Cliente: ${cliente}`);
            $("#exampleModal").modal("show");
            $("#exampleModal")
                .find(".modal-title")
                .text("Detalles compra N° " + id);
            $.ajax({
                headers: {
                    'token-app': localStorage.getItem("_token")
                },
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
                            totalApagar(id, tipo);
                        },
                        columns: [{
                                data: "dias_venta_id",
                                class: "text-center",
                                render: function(data, type, row) {
                                    // Si es un ID temporal (nuevo_X), mostrar solo el número
                                    if (String(data).startsWith('nuevo_')) {
                                        let numero = String(data).replace('nuevo_', '');
                                        return `<span class="badge bg-info">${numero}</span>`;
                                    }
                                    return data;
                                }
                            },
                            {
                                data: "monto",
                                class: "text-center",
                                render: function(data, type, row) {
                                    // Permitir editar si no está pagado, o si está pagado pero es admin (rol 1)
                                    let IsDisabled = (row.estado == 1 && id_rol != 1) ? 'disabled' : '';
                                    return `<input  data-tipo="${row.tipo_doc}" data-cod="${row.dias_venta_id}" class="lisopcpavalor" type="number" step="0.01" min="0" value="${data}" ${IsDisabled}>`
                                }
                            },
                            {
                                data: "fecha",
                                class: "text-center",
                                render: function(data, type, row) {
                                    // Usar fecha actual de JavaScript si no hay fecha o es inválida
                                    let fecha;
                                    if (row.fecha && row.fecha != '0000-00-00') {
                                        fecha = row.fecha;
                                    } else {
                                        // Obtener fecha actual en formato YYYY-MM-DD
                                        const hoy = new Date();
                                        const year = hoy.getFullYear();
                                        const month = String(hoy.getMonth() + 1).padStart(2, '0');
                                        const day = String(hoy.getDate()).padStart(2, '0');
                                        fecha = `${year}-${month}-${day}`;
                                    }
                                    return `<input  data-tipo="${row.tipo_doc}" data-cod="${row.dias_venta_id}"  class="lisopcpafecha" type="date" value="${fecha}">`;
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

                                    let dbPago = (row.tipo_pago || '').toUpperCase();
                                    let listaOpc = ["Efectivo", "Plin", "Yape", "BCP", "BBVA"]
                                        .map(item => {
                                            let uiItem = item.toUpperCase();
                                            // Match exact uppercase, or if the DB string includes the option (e.g. "TRANSFERENCIA BANCO BCP" includes "BCP")
                                            if (uiItem === dbPago || dbPago.includes(uiItem)) {
                                                return `<option selected value="${item}">${item}</option>`;
                                            } else {
                                                return `<option value="${item}">${item}</option>`;
                                            }
                                        })

                                    // Permitir editar si no está pagado, o si está pagado pero es admin (rol 1)
                                    let isDisabled = (row.estado == 1 && id_rol != 1) ? 'disabled' : '';

                                    return `
                                    <select data-tipo="${row.tipo_doc}" data-cod="${row.dias_venta_id}" class="lisopcpa" ${isDisabled}>
                                    <option disabled selected value="">Elija Uno</option>
${listaOpc.join("")}
</select>
                                    `
                                }
                            },
                            {
                                data: null,
                                class: "text-center",
                                render: function(data, type, row) {
                                    let content = '<div class="text-center">';
                                    if (row.estado == '0') {
                                        content += `
                                            <div class="btn-group"><button  data-tipo="${row.tipo_doc}" data-id="${row.dias_venta_id}" class="btn btn-success btnPagar btn-sm"><i class="fas fa-money-bill"></i> </button></div>`;
                                    }
                                    if (row.estado == '1' && id_rol == 1) {
                                        content += `
                                                <div class="btn-group"><button  data-tipo="${row.tipo_doc}" data-id="${row.dias_venta_id}" class="btn btn-warning btnEditarPago btn-sm" title="Guardar cambios"><i class="fas fa-save"></i> </button></div>
                                                <div class="btn-group"><button  data-tipo="${row.tipo_doc}" data-id="${row.dias_venta_id}" class="btn btn-danger btnEliminarPago btn-sm"><i class="fas fa-trash"></i> </button></div>`;
                                    }
                                    content += `</div>`;
                                    return content;
                                },
                            },

                        ],
                    });


                },
            })
        });
        $("#datatableDiasCompras").on("change", ".lisopcpavalor", function(event) {
            console.log($(event.currentTarget))
            const codPag = $(event.currentTarget).data("cod");
            const tipoPag = $(event.currentTarget).data("tipo");
            let valor = $(event.currentTarget).val();
            valor = valor.length == 0 ? '0' : valor

            // VALIDACIÓN: No permitir cobrar más del total
            let totalVenta = parseFloat($("#total_cuotas").val());
            let montos = $(".lisopcpavalor");
            let pagado = 0;

            montos.each((idx, element) => {
                let pago = parseFloat(element.value) || 0;
                pagado += pago;
            });

            // Redondear correctamente para evitar problemas de precisión
            pagado = Math.round(pagado * 100) / 100;
            totalVenta = Math.round(totalVenta * 100) / 100;
            let restante = Math.round((totalVenta - pagado) * 100) / 100;

            // Si el total pagado excede el total de la venta (con tolerancia de 0.01)
            if (pagado > totalVenta + 0.01) {
                Swal.fire({
                    title: 'Error',
                    text: `No puedes cobrar más del total. Total: S/ ${totalVenta.toFixed(2)}, Intentas cobrar: S/ ${pagado.toFixed(2)}`,
                    icon: 'error'
                });
                // Revertir el valor al anterior
                $(event.currentTarget).val('0');
                // Recalcular
                pagado = 0;
                montos.each((idx, element) => {
                    let pago = parseFloat(element.value) || 0;
                    pagado += pago;
                });
                pagado = Math.round(pagado * 100) / 100;
                restante = Math.round((totalVenta - pagado) * 100) / 100;
            }

            $("#total_pagado").val(pagado.toFixed(2));
            $("#restante_total").val(restante.toFixed(2));
            /* console.log(codPag,tipoPag,valor)
            _post("/ajs/set/state/pago/cv",{codPag,tipoPag,valor,col:'v'},(data)=>{
                console.log(data)
            }) */

        })
        /* $("#datatableDiasCompras").on("change",".lisopcpafecha",function (event) {
            console.log($(event.currentTarget))
            const codPag=$(event.currentTarget).data("cod");
            const tipoPag=$(event.currentTarget).data("tipo");
            const valor=$(event.currentTarget).val();
            console.log(codPag,tipoPag,valor)
            _post("/ajs/set/state/pago/cv",{codPag,tipoPag,valor,col:'f'},(data)=>{
                console.log(data)
            })

        }) */
        $("#datatableDiasCompras").on("change", ".lisopcpa", function(event) {
            const codPag = $(event.currentTarget).data("cod");
            const tipoPag = $(event.currentTarget).data("tipo");
            const valor = $(event.currentTarget).val();
            let fila = $(this).closest("tr");
            let fecha = fila.find('.lisopcpafecha').val();
            const hoy = new Date();
            const year = hoy.getFullYear();
            const month = String(hoy.getMonth() + 1).padStart(2, '0');
            const day = String(hoy.getDate()).padStart(2, '0');

            const fechaFormateada = `${year}-${month}-${day}`;
            if (valor == 'Efectivo') {
                // Solo cambiar a fecha de hoy si el campo está vacío o tiene fecha inválida
                if (!fecha || fecha === '' || fecha === '0000-00-00' || fecha === fechaFormateada) {
                    fila.find('.lisopcpafecha').val(fechaFormateada);
                }
                // No deshabilitar el campo para permitir edición manual
                // fila.find('.lisopcpafecha').attr('disabled', 'disabled');
            } else {
                fila.find('.lisopcpafecha').removeAttr('disabled');
            }

            console.log(codPag, tipoPag)
            /* _post("/ajs/set/state/pago/cv",{codPag,tipoPag,valor,col:'p'},(data)=>{
                console.log(data)
            }) */

        })
        $("#datatableDiasCompras").on("click", ".btnPagar ", function(event) {

            var table = $("#datatableDiasCompras").DataTable();
            var trid = $(this).closest("tr").attr("id");
            var id = $(this).data("id");
            var tipo = $(this).data("tipo");
            var fila = $(this).closest("tr");
            var monto = fila.find('.lisopcpavalor').val();
            var fecha = fila.find('.lisopcpafecha').val();
            var tipo_pago = fila.find('.lisopcpa').val();

            // Verificar si es una fila nueva (ID temporal)
            var esNuevo = String(id).startsWith('nuevo_');

            console.log(tipo_pago)
            if (isNaN(monto) || isNaN(parseFloat(monto)) || parseFloat(monto) <= 0) {
                fila.find('.lisopcpavalor').val('');
                fila.find('.lisopcpavalor').focus();
                Swal.fire({
                    title: 'Primero debe ingresar un monto',
                    icon: "error"
                });
            } else if (!tipo_pago || tipo_pago == "") {
                Swal.fire({
                    title: 'Elija el tipo de Pago',
                    icon: "error"
                });
            } else if (fecha == "") {
                Swal.fire({
                    title: 'Debe elegir la fecha',
                    icon: "error"
                });
            } else {
                let mensaje = esNuevo ? '¿Desea registrar este pago adicional?' : '¿Desea pagar la cuota N° ' + id + ' ?';
                Swal.fire({
                    title: mensaje,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Si'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $("#loader-menor").show()

                        // Si es nuevo, necesitamos obtener el id_venta del modal
                        let id_venta = $("#exampleModal").find(".modal-title").text().match(/\d+/)[0];

                        $.ajax({
                            headers: {
                                'token-app': localStorage.getItem("_token")
                            },
                            type: 'POST',
                            url: _URL + '/ajs/pagar/cuota/ventas',
                            data: {
                                id: esNuevo ? id_venta : id,
                                tipo,
                                monto,
                                fecha,
                                tipo_pago,
                                es_nuevo: esNuevo ? 1 : 0
                            },
                            success: function(resp) {
                                $("#loader-menor").hide();
                                console.log("Respuesta cruda:", resp);

                                // Verificar si la respuesta es JSON válido
                                try {
                                    let data = JSON.parse(resp);
                                    console.log("Respuesta del servidor:", data);

                                    // Verificar si hay error por falta de caja
                                    if (data.error === 'no_caja') {
                                        Swal.fire({
                                            title: 'Caja no abierta',
                                            html: data.mensaje + '<br><br><a href="/mi-caja" class="btn btn-primary mt-2">Ir a Mi Caja</a>',
                                            icon: 'warning',
                                            confirmButtonText: 'Entendido'
                                        });
                                    } else if (data.res === true) {
                                        Swal.fire({
                                            title: 'Éxito',
                                            text: esNuevo ? 'Pago adicional registrado correctamente' : 'Cuota pagada correctamente',
                                            icon: 'success',
                                            timer: 1500,
                                            showConfirmButton: false
                                        }).then(() => {
                                            botonDetalle.click()
                                        });
                                    } else {
                                        Swal.fire({
                                            title: 'Error',
                                            html: 'No se pudo registrar el pago<br>' + (data.error || ''),
                                            icon: 'error'
                                        });
                                        console.error("SQL:", data.sql);
                                    }
                                } catch (e) {
                                    // Si no es JSON, mostrar el error crudo
                                    console.error("Error al parsear respuesta:", e);
                                    console.error("Respuesta del servidor:", resp);
                                    Swal.fire({
                                        title: 'Error en el servidor',
                                        html: '<pre style="text-align:left; max-height:300px; overflow:auto;">' + resp + '</pre>',
                                        icon: 'error',
                                        width: '600px'
                                    });
                                }
                            },
                            error: function(xhr, status, error) {
                                $("#loader-menor").hide();
                                Swal.fire({
                                    title: 'Error',
                                    text: 'Ocurrió un error al procesar el pago',
                                    icon: 'error'
                                });
                            }
                        });
                    }
                })
            }

        })

        // Botón para editar/guardar cambios en pagos ya realizados (solo admin)
        $("#datatableDiasCompras").on("click", ".btnEditarPago", function(event) {
            var id = $(this).data("id");
            var tipo = $(this).data("tipo");
            var fila = $(this).closest("tr");
            var monto = fila.find('.lisopcpavalor').val();
            var fecha = fila.find('.lisopcpafecha').val();
            var tipo_pago = fila.find('.lisopcpa').val();

            // Validar monto
            if (isNaN(monto) || isNaN(parseFloat(monto)) || parseFloat(monto) <= 0) {
                Swal.fire({
                    title: 'El monto debe ser mayor a 0',
                    icon: "error"
                });
                return;
            }

            // Validar que no exceda el total
            let totalVenta = parseFloat($("#total_cuotas").val());
            let montos = $(".lisopcpavalor");
            let pagado = 0;

            montos.each((idx, element) => {
                let pago = parseFloat(element.value) || 0;
                pagado += pago;
            });

            // Redondear para evitar problemas de precisión
            pagado = Math.round(pagado * 100) / 100;
            totalVenta = Math.round(totalVenta * 100) / 100;

            if (pagado > totalVenta + 0.01) {
                Swal.fire({
                    title: 'Error',
                    text: `No puedes cobrar más del total. Total: S/ ${totalVenta.toFixed(2)}`,
                    icon: 'error'
                });
                return;
            }

            Swal.fire({
                title: '¿Desea guardar los cambios en la cuota N° ' + id + '?',
                text: 'Nuevo monto: S/ ' + parseFloat(monto).toFixed(2),
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, guardar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $("#loader-menor").show();

                    $.ajax({
                        headers: {
                            'token-app': localStorage.getItem("_token")
                        },
                        type: 'POST',
                        url: _URL + '/ajs/editar/cuota/ventas',
                        data: {
                            id: id,
                            tipo: tipo,
                            monto: monto,
                            fecha: fecha,
                            tipo_pago: tipo_pago
                        },
                        success: function(resp) {
                            $("#loader-menor").hide();
                            try {
                                let data = JSON.parse(resp);
                                if (data.res === true) {
                                    Swal.fire({
                                        title: 'Éxito',
                                        text: 'Cuota actualizada correctamente',
                                        icon: 'success',
                                        timer: 1500,
                                        showConfirmButton: false
                                    }).then(() => {
                                        botonDetalle.click();
                                    });
                                } else {
                                    Swal.fire({
                                        title: 'Error',
                                        text: data.msg || 'No se pudo actualizar la cuota',
                                        icon: 'error'
                                    });
                                }
                            } catch (e) {
                                console.error("Error al parsear respuesta:", e);
                                Swal.fire({
                                    title: 'Error',
                                    text: 'Error al procesar la respuesta del servidor',
                                    icon: 'error'
                                });
                            }
                        },
                        error: function(xhr, status, error) {
                            $("#loader-menor").hide();
                            Swal.fire({
                                title: 'Error',
                                text: 'Ocurrió un error al actualizar la cuota',
                                icon: 'error'
                            });
                        }
                    });
                }
            });
        });

        $("#datatableDiasCompras").on("click", ".btnEliminarPago ", function(event) {
            var table = $("#tablaMaquina").DataTable();
            var trid = $(this).closest("tr").attr("id");
            var id = $(this).data("id");
            var tipo = $(this).data("tipo");
            var fila = $(this).closest("tr");
            var monto = fila.find('.lisopcpavalor').val();
            Swal.fire({
                title: '¿Desea eliminar el pago la cuota N° ' + id + ' ? ',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Si'
            }).then((result) => {
                if (result.isConfirmed) {
                    $("#loader-menor").show()
                    $.ajax({
                        headers: {
                            'token-app': localStorage.getItem("_token")
                        },
                        type: 'POST',
                        url: _URL + '/ajs/pagar/cuota/eliminar',
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
                        }
                    });
                }
            })
        })

        $('.cerrarpagos').click(function() {
            $('#exampleModal').modal('hide');
            //datatable.ajax.reload();
        })

        // Agregar nueva fila de pago
        $('#btnAgregarPago').click(function() {
            let restante = parseFloat($('#restante_total').val());

            if (isNaN(restante) || restante <= 0) {
                Swal.fire({
                    title: 'No hay saldo pendiente',
                    text: 'La deuda ya está completamente pagada',
                    icon: 'info'
                });
                return;
            }

            // Obtener el último ID de la tabla para generar el siguiente
            let datatable = $('#datatableDiasCompras').DataTable();
            let maxId = 0;

            datatable.rows().every(function() {
                let row = this.data();
                let currentId = parseInt(row.dias_venta_id);
                if (!isNaN(currentId) && currentId > maxId) {
                    maxId = currentId;
                }
            });

            let nuevoId = maxId + 1;

            // Obtener el tipo de documento del primer registro
            let primerFila = datatable.row(0).data();
            let tipoDoc = primerFila ? primerFila.tipo_doc : '';

            // Crear nueva fila con datos temporales
            let nuevaFila = {
                dias_venta_id: 'nuevo_' + nuevoId, // ID temporal con el siguiente número
                monto: restante.toFixed(2),
                fecha: '<?php echo date('Y-m-d') ?>',
                estado: '0',
                tipo_pago: '',
                tipo_doc: tipoDoc,
                es_nuevo: true // Marcador para identificar filas nuevas
            };

            // Agregar la fila a la tabla
            datatable.row.add(nuevaFila).draw();

            // Recalcular totales
            sumarTotal();
        });
    })
</script>