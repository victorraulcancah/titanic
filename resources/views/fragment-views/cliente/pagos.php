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
                <li class="breadcrumb-item"><a href="javascript: void(0);">Facturación</a></li>
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
            <div class="card-header">
                <div class="row align-items-end g-3">
                    <div class="col-md-12">
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
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body">

                <div class="table-responsive">


                    <table id="datatable" class="table table-bordered dt-responsive nowrap text-center table-sm" style="border-collapse: collapse; border-spacing: 0; width: 100%;">

                        <thead>
                        <tr>
                            <th style="text-align: center;">Id</th>
                            <th style="text-align: center;">Codigo</th>
                            <th style="text-align: center;">F. Emision</th>
                            <th style="text-align: center;">F. Vencimiento</th>
                            <th style="text-align: center;">Empresa</th>
                            <!--   <th style="text-align: center;">Moneda</th> -->
                            <th style="text-align: center;">Total</th>
                            <th style="text-align: center;">Pagado</th>
                            <th style="text-align: center;">Saldo</th>
                            <th style="text-align: center;">Situacion</th>
                            <th style="text-align: center;">Dias Vencidos</th>
                            <th style="text-align: center;">Detalles</th>
                            <th style="text-align: center;">Productos</th>

                        </tr>
                        </thead>

                    </table>
                </div>

                <!-- Modal Productos -->
                <div class="modal fade" id="modalProductos" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h1 class="modal-title fs-5" id="exampleModalLabel">Modal title</h1>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="col-xs-12 col-sm-12 col-md-12 no-padding table-responsive">
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
                            <div class="modal-footer">
                                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cerrar</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Modal Cuotas -->
                <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h1 class="modal-title fs-5" id="exampleModalLabel">Modal title</h1>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="col-xs-12 col-sm-12 col-md-12 no-padding table-responsive">
                                    <h4 id="title-cliente-cuotas"></h4>
                                    <table id="datatableDiasCompras" class="table table-bordered dt-responsive nowrap text-center table-sm" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                                        <thead>
                                            <tr>
                                                <th style="text-align: center;">Id</th>
                                                <th style="text-align: center;">Monto</th>
                                                <th style="text-align: center;">F. Pago</th>
                                                <th style="text-align: center;">Estado</th>
                                                <th style="text-align: center;">Pago</th>
                                                <th style="text-align: center;">Accion</th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                                <div class="d-flex gap-3 mt-2">
                                    <p>Total: <input type="text" id="total_cuotas" class="border px-2 py-1" readonly></p>
                                    <p>Falta pagar: <input type="text" id="restante_total" class="border px-2 py-1" readonly></p>
                                    <p>Total Pagado: <input type="text" id="total_pagado" class="border px-2 py-1" readonly></p>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-primary" id="btnAgregarPagoCompra"><i class="fas fa-plus"></i> Agregar Pago</button>
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
    $(document).ready(function() {
        var days = daysdifference('03/25/2021', '03/22/2021');
        // Add two dates to two variables
        console.log(days);

        function daysdifference(firstDate, secondDate) {
            var startDay = new Date(firstDate);
            var endDay = new Date(secondDate);

            // Determine the time difference between two dates
            var millisBetween = startDay.getTime() - endDay.getTime();

            // Determine the number of days between two dates
            var days = millisBetween / (1000 * 3600 * 24);

            // Show the final number of days between dates
            return Math.round(Math.abs(days));
        }
        $.ajax({
            type: 'POST',
            url: _URL + '/ajas/cuentas/ventas/render',
            success: function(resp) {
                let data = JSON.parse(resp)
                let iguaalcion = data[5]
                console.log();
                if (iguaalcion.pagado > parseFloat(iguaalcion.total).toFixed(3)) {
                    console.log('nice');
                } else {
                    console.log('bad');
                }
            }
        });
        function sumarTotales() {
            let total = 0;
            let pagado = 0;
            let saldo = 0;

            const data = datatable.rows({ search: 'applied' }).data();

            for (let i = 0; i < data.length; i++) {
                total += parseFloat(data[i].total ?? 0);
                pagado += parseFloat(data[i].pagado ?? 0);
                saldo += parseFloat(data[i].saldo ?? 0);
            }

            $("#total").text('S/ ' + total.toFixed(2));
            $("#pagado").text('S/ ' + pagado.toFixed(2));
            $("#saldo").text('S/ ' + saldo.toFixed(2));
        }

        datatable = $("#datatable").DataTable({
            order: [[8, 'asc']],
            paging: true,
            bFilter: true,
            ordering: true,
            searching: true,
            destroy: true,
            ajax: {
                url: _URL + "/ajas/cuentas/ventas/render",
                method: "POST",
                dataSrc: "",
            },
            drawCallback: function() {
                sumarTotales();
            },
            language: {
                url: "ServerSide/Spanish.json",
            },
            columns: [{
                data: "id_compra",
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
                    data: "fecha_vencimiento",
                    class: "text-center",
                },
                {
                    data: "cliente",
                    class: "text-center",
                },


                {
                    data: null,
                    class: "text-center",
                    render: function(data, type, row) {
                        if (row.total !== null) {
                            return `<div class="text-center">
                                            <div class="btn-group">S/ ${row.total}</div></div>`;
                        }  else {
                            return `<div class="text-center">
                                            <div class="btn-group"></div></div>`;
                        }

                    },
                },
                {
                    data: null,
                    class: "text-center",
                    render: function(data, type, row) {
                        if ( row.pagado !== null) {
                            return `<div class="text-center">
                                            <div class="btn-group">S/ ${row.pagado}</div></div>`;
                        }  else {
                            return `<div class="text-center">
                                            <div class="btn-group"></div></div>`;
                        }

                    },
                },
                {
                    data: null,
                    class: "text-center",
                    render: function(data, type, row) {
                        if (row.saldo !== null) {
                            return `<div class="text-center">
                                            <div class="btn-group">S/ ${row.saldo}</div></div>`;
                        } else {
                            return `<div class="text-center">
                                            <div class="btn-group"></div></div>`;
                        }

                    },
                },

                {
                    data: null,
                    class: "text-center",
                    render: function(data, type, row) {

                        let vencimiento = row.fecha_vencimiento
                        const [year, month, day] = vencimiento.split('-');
                        const vencimientoFecha = [month, day, year].join('/');
                        var today = new Date();
                        var dd = String(today.getDate()).padStart(2, '0');
                        var mm = String(today.getMonth() + 1).padStart(2, '0'); //January is 0!
                        var yyyy = today.getFullYear();
                        today = mm + '/' + dd + '/' + yyyy;
                        if ((parseFloat(row.total).toFixed(3) == parseFloat(row.pagado).toFixed(3))) {
                            if (type === 'sort') return 2;
                            return `<div class="text-center">
              <div class="btn-group"><span class="badge bg-success">Pagado</span></div></div>`;
                        } else if ((parseFloat(row.total).toFixed(3) > parseFloat(row.pagado).toFixed(3)) && today > vencimientoFecha) {
                            if (type === 'sort') return 0;
                            return `<div class="text-center">
              <div class="btn-group"><span class="badge bg-danger">Vencido</span></div></div>`;
                        } else if ((parseFloat(row.total).toFixed(3) > parseFloat(row.pagado).toFixed(3)) && today < vencimientoFecha) {
                            if (type === 'sort') return 1;
                            return `<div class="text-center">
              <div class="btn-group"><span class="badge bg-info">Vigente</span></div></div>`;
                        }
                        if (type === 'sort') return 3;
                        return '';


                    },
                },

                {
                    data: null,
                    class: "text-center",
                    render: function(data, type, row) {
                        let vencimiento = row.fecha_vencimiento
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

                    },
                },
                {
                    data: null,
                    class: "text-center",
                    render: function(data, type, row) {
                        return `<div class="text-center">
                                            <div class="btn-group"><button  data-id="${Number(
                            row.id_compra
                        )}" class="btn btn-success btnDetalles btn-sm"><i class="fa fa-eye"></i> </button></div></div>`;
                    },
                },
                {
                    data: null,
                    class: "text-center",
                    render: function(data, type, row) {
                        return `<div class="text-center">
                                            <div class="btn-group"><button data-id="${Number(
                            row.id_compra
                        )}" class="btn btn-info btnProductos btn-sm"><i class="fa fa-box"></i> </button></div></div>`;
                    },
                },
            ],
        });

        $("#datatable").on("click", ".btnDetalles ", function(event) {
            $("#loader-menor").show()
            var table = $("#tablaMaquina").DataTable();
            var trid = $(this).closest("tr").attr("id");
            var id = $(this).data("id");
            $("#exampleModal").modal("show");
            var id_rol = <?= $_SESSION['rol'] ?>;
            var tr = $(this).closest("tr")[0];
            var td = tr.querySelectorAll('td')[4];
            var td_texto = td.innerHTML;
            var cliente = td_texto.split('|')[1];
            $("#title-cliente-cuotas").html("Cliente: " + (cliente || ""));
            $("#exampleModal")
                .find(".modal-title")
                .text("Detalles compra N° " + id);
            $("#exampleModal").data("id_compra", id);
            $.ajax({
                url: _URL + "/ajas/getAllCuotas/byIdCompra",
                data: { id: id },
                type: "post",
                success: function(resp) {
                    $("#loader-menor").hide()
                    resp = JSON.parse(resp)

                    if ($.fn.DataTable.isDataTable("#datatableDiasCompras")) {
                        $("#datatableDiasCompras").DataTable().clear().destroy();
                    }

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
                        initComplete: function() {
                            totalApagar(id);
                        },
                        columns: [{
                            data: "dias_compra_id",
                            class: "text-center",
                            render: function(data) {
                                if (String(data).startsWith('nuevo_')) {
                                    let num = String(data).replace('nuevo_', '');
                                    return `<span class="badge bg-info">${num}</span>`;
                                }
                                return data;
                            }
                        },
                        {
                            data: "monto",
                            class: "text-center",
                            render: function(data, type, row) {
                                let isDisabled = (row.estado == 1 && id_rol != 1) ? 'disabled' : '';
                                return `<input data-cod="${row.dias_compra_id}" class="lisopcpavalor" type="number" step="0.01" min="0" value="${data}" ${isDisabled}>`;
                            }
                        },
                        {
                            data: "fecha",
                            class: "text-center",
                            render: function(data, type, row) {
                                let fecha;
                                if (row.estado == '0') {
                                    const hoy = new Date();
                                    const year = hoy.getFullYear();
                                    const month = String(hoy.getMonth() + 1).padStart(2, '0');
                                    const day = String(hoy.getDate()).padStart(2, '0');
                                    fecha = year + '-' + month + '-' + day;
                                } else {
                                    fecha = (row.fecha && row.fecha != '0000-00-00') ? row.fecha : '';
                                }
                                let isDisabled = (row.estado == 1 && id_rol != 1) ? 'disabled' : '';
                                return `<input data-cod="${row.dias_compra_id}" class="lisopcpafecha" type="date" value="${fecha}" ${isDisabled}>`;
                            }
                        },
                        {
                            data: null,
                            class: "text-center",
                            render: function(data, type, row) {
                                let vencimiento = row.fecha;
                                if (vencimiento) {
                                    const [year, month, day] = vencimiento.split('-');
                                    const vencimientoFecha = month + '/' + day + '/' + year;
                                    var today = new Date();
                                    var dd = String(today.getDate()).padStart(2, '0');
                                    var mm = String(today.getMonth() + 1).padStart(2, '0');
                                    var yyyy = today.getFullYear();
                                    today = mm + '/' + dd + '/' + yyyy;
                                    if ((today > vencimientoFecha) && row.estado == '0') {
                                        return '<div class="text-center"><div class="btn-group"><span class="badge bg-danger">Vencido</span></div></div>';
                                    } else if ((today <= vencimientoFecha) && row.estado == '0') {
                                        return '<div class="text-center"><div class="btn-group"><span class="badge bg-success">Vigente</span></div></div>';
                                    } else if (row.estado == '1') {
                                        return '<div class="text-center"><div class="btn-group"><span class="badge bg-info">Pagado</span></div></div>';
                                    }
                                }
                                return '';
                            }
                        },
                        {
                            data: null,
                            class: "text-center",
                            render: function(data, type, row) {
                                let dbPago = (row.tipo_pago || (row.estado == 1 ? 'EFECTIVO' : '')).toUpperCase();
                                let opciones = ["Efectivo", "Plin", "Yape", "BCP", "BBVA"].map(function(item) {
                                    let uiItem = item.toUpperCase();
                                    let selected = (uiItem === dbPago || dbPago.includes(uiItem)) ? 'selected' : '';
                                    return '<option ' + selected + ' value="' + item + '">' + item + '</option>';
                                });
                                let isDisabled = (row.estado == 1 && id_rol != 1) ? 'disabled' : '';
                                return '<select data-cod="' + row.dias_compra_id + '" class="lisopcpa" ' + isDisabled + '><option disabled selected value="">Elija Uno</option>' + opciones.join("") + '</select>';
                            }
                        },
                        {
                            data: null,
                            class: "text-center",
                            render: function(data, type, row) {
                                let content = '<div class="text-center">';
                                if (row.estado == '0') {
                                    content += '<div class="btn-group"><button data-id="' + row.dias_compra_id + '" class="btn btn-success btnPagar btn-sm"><i class="fas fa-money-bill"></i></button></div>';
                                }
                                if (row.estado == '1' && id_rol == 1) {
                                    content += '<div class="btn-group"><button data-id="' + row.dias_compra_id + '" class="btn btn-warning btnEditarPagoCompra btn-sm" title="Guardar cambios"><i class="fas fa-save"></i></button></div>';
                                    content += '<div class="btn-group"><button data-id="' + row.dias_compra_id + '" class="btn btn-danger btnEliminarPagoCompra btn-sm"><i class="fas fa-trash"></i></button></div>';
                                }
                                content += '</div>';
                                return content;
                            }
                        }
                        ],
                    });
                },
            })
        });
        $("#datatable").on("click", ".btnProductos", function(event) {
            $("#loader-menor").show();
            var id = $(this).data("id");

            $("#modalProductos").modal("show");
            $("#modalProductos")
                .find(".modal-title")
                .text("Detalles compra N° " + id);

            $.ajax({
                url: _URL + "/ajas/getAllProductos/byIdCompra",
                type: "POST",
                data: { id: id },
                success: function(resp) {
                    $("#loader-menor").hide();
                    let data = JSON.parse(resp);

                    if ($.fn.DataTable.isDataTable("#datatableProductos")) {
                        $("#datatableProductos").DataTable().clear().destroy();
                    }

                    $("#datatableProductos").DataTable({
                        data: data,
                        columns: [
                            { data: "id_producto", className: "text-center" },
                            { data: "descripcion", className: "text-center" },
                            { data: "cantidad", className: "text-center" },
                            { data: "precio", className: "text-center" },
                            {
                                data: "total",
                                className: "text-center",
                                render: function(data, type, row) {
                                    return `<div class="text-center"><div class="btn-group"><span class="badge bg-success">${parseFloat(data).toFixed(2)}</span></div></div>`;
                                }
                            }
                        ]
                    });
                },
                error: function(xhr, status, error) {
                    $("#loader-menor").hide();
                    console.error("Error en AJAX:", error);
                }
            });
        });

        function totalApagar(id) {
            var total = 0;
            var pagado = 0;

            if ($.fn.DataTable.isDataTable("#datatableDiasCompras")) {
                var table = $("#datatableDiasCompras").DataTable();
                table.rows().every(function() {
                    var row = this.data();
                    var monto = parseFloat(row.monto) || 0;
                    total += monto;
                    if (row.estado == '1') {
                        pagado += monto;
                    }
                });
            }

            var restante = total - pagado;
            $("#total_cuotas").val(total.toFixed(2));
            $("#total_pagado").val(pagado.toFixed(2));
            $("#restante_total").val(restante.toFixed(2));
        }

        $("#datatableDiasCompras").on("change", ".lisopcpavalor", function() {
            var totalVenta = parseFloat($("#total_cuotas").val()) || 0;
            var montos = $(".lisopcpavalor");
            var pagado = 0;

            montos.each(function() {
                pagado += parseFloat($(this).val()) || 0;
            });

            pagado = Math.round(pagado * 100) / 100;
            totalVenta = Math.round(totalVenta * 100) / 100;
            var restante = Math.round((totalVenta - pagado) * 100) / 100;

            if (pagado > totalVenta + 0.01) {
                Swal.fire({
                    title: 'Error',
                    text: 'No puedes cobrar mas del total. Total: S/ ' + totalVenta.toFixed(2),
                    icon: 'error'
                });
                $(this).val('0');
                pagado = 0;
                montos.each(function() {
                    pagado += parseFloat($(this).val()) || 0;
                });
                pagado = Math.round(pagado * 100) / 100;
                restante = Math.round((totalVenta - pagado) * 100) / 100;
            }

            $("#total_pagado").val(pagado.toFixed(2));
            $("#restante_total").val(restante.toFixed(2));
        });

        $("#datatableDiasCompras").on("click", ".btnPagar", function() {
            var fila = $(this).closest("tr");
            var id = $(this).data("id");
            var monto = fila.find('.lisopcpavalor').val();
            var fecha = fila.find('.lisopcpafecha').val();
            var tipo_pago = fila.find('.lisopcpa').val();

            if (isNaN(monto) || parseFloat(monto) <= 0) {
                Swal.fire({ title: 'Primero debe ingresar un monto', icon: "error" });
                return;
            }
            if (!tipo_pago) {
                Swal.fire({ title: 'Elija el tipo de pago', icon: "error" });
                return;
            }
            if (!fecha) {
                Swal.fire({ title: 'Debe elegir la fecha', icon: "error" });
                return;
            }

            var esNuevo = String(id).startsWith('nuevo_');
            var idCompra = $("#exampleModal").data("id_compra");

            if (esNuevo && !idCompra) {
                Swal.fire({ title: 'Error', text: 'No se encontro la compra asociada', icon: 'error' });
                return;
            }

            Swal.fire({
                title: 'Pagar cuota N° ' + id,
                text: 'Monto: S/ ' + parseFloat(monto).toFixed(2),
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Si'
            }).then(function(result) {
                if (result.isConfirmed) {
                    $("#loader-menor").show();
                    var data = { id: id, monto_pagado: monto, tipo_pago: tipo_pago, fecha_pago: fecha };
                    if (esNuevo) {
                        data.es_nuevo = 1;
                        data.id_compra = idCompra;
                    }
                    $.ajax({
                        type: 'POST',
                        url: _URL + '/ajs/pagar/cuota/pago',
                        data: data,
                        success: function(resp) {
                            $("#loader-menor").hide();
                            try {
                                var r = JSON.parse(resp);
                                if (r.res) {
                                    location.reload();
                                } else {
                                    Swal.fire({ title: 'Error', text: r.msg || r.error || 'No se pudo registrar el pago', icon: 'error' });
                                }
                            } catch(e) {
                                location.reload();
                            }
                        },
                        error: function() {
                            $("#loader-menor").hide();
                            Swal.fire({ title: 'Error', text: 'Error de conexion al registrar el pago', icon: 'error' });
                        }
                    });
                }
            });
        });

        $("#datatableDiasCompras").on("click", ".btnEditarPagoCompra", function() {
            var id = $(this).data("id");
            var fila = $(this).closest("tr");
            var monto = fila.find('.lisopcpavalor').val();
            var fecha = fila.find('.lisopcpafecha').val();
            var tipo_pago = fila.find('.lisopcpa').val();

            if (isNaN(monto) || parseFloat(monto) <= 0) {
                Swal.fire({ title: 'El monto debe ser mayor a 0', icon: "error" });
                return;
            }

            Swal.fire({
                title: 'Guardar cambios en cuota N° ' + id,
                text: 'Nuevo monto: S/ ' + parseFloat(monto).toFixed(2),
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Si, guardar'
            }).then(function(result) {
                if (result.isConfirmed) {
                    $("#loader-menor").show();
                    $.ajax({
                        type: 'POST',
                        url: _URL + '/ajs/editar/cuota/compras',
                        data: { id: id, monto: monto, fecha: fecha, tipo_pago: tipo_pago },
                        success: function(resp) {
                            $("#loader-menor").hide();
                            var data = JSON.parse(resp);
                            if (data.res) {
                                Swal.fire({ title: 'Exito', text: 'Cuota actualizada', icon: 'success', timer: 1500, showConfirmButton: false }).then(function() {
                                    location.reload();
                                });
                            } else {
                                Swal.fire({ title: 'Error', text: data.msg || 'No se pudo actualizar', icon: 'error' });
                            }
                        }
                    });
                }
            });
        });

        $("#datatableDiasCompras").on("click", ".btnEliminarPagoCompra", function() {
            var id = $(this).data("id");
            Swal.fire({
                title: 'Eliminar pago cuota N° ' + id + '?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Si'
            }).then(function(result) {
                if (result.isConfirmed) {
                    $("#loader-menor").show();
                    $.ajax({
                        type: 'POST',
                        url: _URL + '/ajs/pagar/cuota/eliminar/compra',
                        data: { id: id },
                        success: function(resp) {
                            $("#loader-menor").hide();
                            try {
                                var r = JSON.parse(resp);
                                if (r.res) {
                                    location.reload();
                                } else {
                                    Swal.fire({ title: 'Error', text: r.msg || 'No se pudo eliminar el pago', icon: 'error' });
                                }
                            } catch(e) {
                                location.reload();
                            }
                        },
                        error: function() {
                            $("#loader-menor").hide();
                            Swal.fire({ title: 'Error', text: 'Error de conexion al eliminar el pago', icon: 'error' });
                        }
                    });
                }
            });
        });

        $('.cerrarpagos').click(function() {
            $('#exampleModal').modal('hide');
        });

        $('#btnAgregarPagoCompra').click(function() {
            var restante = parseFloat($('#restante_total').val());
            if (isNaN(restante) || restante <= 0) {
                Swal.fire({ title: 'No hay saldo pendiente', text: 'La deuda ya esta completamente pagada', icon: 'info' });
                return;
            }

            var table = $('#datatableDiasCompras').DataTable();
            var maxId = 0;
            table.rows().every(function() {
                var row = this.data();
                var currentId = parseInt(row.dias_compra_id);
                if (!isNaN(currentId) && currentId > maxId) {
                    maxId = currentId;
                }
            });

            var nuevoId = maxId + 1;
            var hoy = new Date();
            var year = hoy.getFullYear();
            var month = String(hoy.getMonth() + 1).padStart(2, '0');
            var day = String(hoy.getDate()).padStart(2, '0');
            var fechaHoy = year + '-' + month + '-' + day;

            var nuevaFila = {
                dias_compra_id: 'nuevo_' + nuevoId,
                monto: '0.00',
                fecha: fechaHoy,
                estado: '0',
                tipo_pago: '',
                tipo_doc: 'v',
                es_nuevo: true
            };

            table.row.add(nuevaFila).draw();
            totalApagar();
        });
    });
</script>