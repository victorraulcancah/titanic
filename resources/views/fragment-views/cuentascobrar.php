<!-- start page title -->

<div class="page-title-box">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h6 class="page-title">Cuentas por Cobrar</h6>
            <ol class="breadcrumb m-0">
                <li class="breadcrumb-item"><a href="javascript: void(0);">Facturacion</a></li>
                <li class="breadcrumb-item active" aria-current="page">Cuentas por Cobrar</li>
            </ol>
        </div>

    </div>
</div>
<!-- end page title -->



<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body ">
                <div class="table-responsive">
                    <h4 class="card-title">Lista de Cuentas Por cobrar</h4>
                    <table id="datatable" class="table table-bordered dt-responsive nowrap text-center table-sm table-hover" style="border-collapse: collapse; border-spacing: 0; width: 100%;">

                        <thead>
                            <tr>
                                <th>ID Cuota</th>
                                <th>ID Venta</th>
                                <th>Emision</th>
                                <th>Vencimiento</th>
                                <th>Cliente</th>
                                <th>Estado</th>
                                <th>Pagado</th>

                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>

                </div>


            </div>
        </div>
    </div> <!-- end col -->
</div>
<div id="modal_ver_detalle" class="modal fade" tabindex="-1" aria-labelledby="myModalLabel" style="display: none;" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <input type="hidden" name="idUsaVenta" id="idPresu" value="">
            <input type="hidden" name="trid" id="trid" value="">
            <div class="modal-header">
                <h5 class="modal-title" id="myModalLabel">Detalle Productos en venta
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="modal_detalle">

            </div>

            |
            <div class="modal-footer">

                <button type="button" class="btn btn-danger waves-effect" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
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
        _ajax("/ajs/cuentas/cobrar", "POST", {}, function(resp) {
            //console.log(resp);
            console.log(resp);
        })
    }
    var tabla;
    $(document).ready(function() {

        datatable = $("#datatable").DataTable({

            paging: true,
            bFilter: true,
            ordering: true,
            searching: true,
            destroy: true,
            ajax: {
                url: _URL + "/ajs/cuentas/cobrar",
                method: "POST", //usamos el metodo POST
                dataSrc: "",
            },
            columns: [{
                    data: "dias_venta_id",
                    class: "text-center"
                },
                {
                    data: null,
                    class: "text-center",
                    render: function(data, type, row) {
                        return `<div class="text-center">
                <div class="btn-group"><p id="idVenta" value="${Number(
                  row.id_venta
                )}" id="${Number(
                  row.id_venta
                )}"</p></div>${Number(
                  row.id_venta)}</div>`;
                    },
                },
               
                {
                    data: "fecha_emision",
                    class: "text-center"
                },
                {
                    data: "fecha_vencimiento",
                    class: "text-center"
                },
                {
                    data: "datos",
                    class: "text-center"
                },

                {
                    class: "text-center",
                    render: function(data, type, row) {
                        if (row.estado == 1) {
                            return `<div class="text-center">
                <div class="btn-group"><span class="badge bg-warning">Pendiente </span></div></div> `;
                        } else if (row.estado == 0) {
                            return `<div class="text-center">
                <div class="btn-group"><span class="badge bg-success">Pagado</span></div></div> `;

                        } else {
                            return `<div class="text-center">
                <div class="btn-group"></div></div> `;

                        }
                    },
                },
                {
                    data: null,
                    class: "text-center",
                    render: function(data, type, row) {
                        return `<div class="text-center">
                <div class="btn-group"><button  data-id="${Number(
                  row.dias_venta_id
                )}" class="btn btn-sm btn-success btnPagar"><i class="fas fa-money-bill"></i> </button></div></div>`;
                    },
                },
            ],
        });


        $("#datatable").on("click", ".btnPagar ", function(event) {
            var table = $("#datatable").DataTable();
            var trid = $(this).closest("tr").attr("id");
            var id = $(this).data("id");
            var idVeta = $(this).attr("idVenta");
         /*    var idVeta = table.row( this ).data().id_venta; */
            console.log(idVeta);
            const swalWithBootstrapButtons = Swal.mixin({
                customClass: {
                    confirmButton: 'btn btn-success',
                    cancelButton: 'btn btn-danger'
                },
                buttonsStyling: false
            })

            swalWithBootstrapButtons.fire({
                title: "¿Se canceló la cuota del esta venta",

                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Si',
                cancelButtonText: 'No',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {

                    _ajax("/ajs/cuentas/cobrar/estado", "POST", {
                        id: id
                    }, function(resp) {
                        //console.log(resp);
                        console.log(resp);
                        datatable.ajax.reload(null, false);
                    })
                    swalWithBootstrapButtons.fire(
                        'Bien',
                        'La cuota se canceló',
                        'success'
                    )
                } else if (
                    /* Read more about handling dismissals below */
                    result.dismiss === Swal.DismissReason.cancel
                ) {

                }
            })


        })
    })
</script>
