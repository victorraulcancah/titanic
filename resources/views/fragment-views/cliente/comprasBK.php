<div class="page-title-box">
    <div class="row align-items-center">
        <!-- <div class="col-md-8">
            <h6 class="page-title">Compras</h6>
            <ol class="breadcrumb m-0">
                <li class="breadcrumb-item"><a href="javascript: void(0);">Facturacion</a></li>
                <li class="breadcrumb-item"><a href="/ventas" class="button-link">Ventas</a></li>
                <li class="breadcrumb-item active" aria-current="page">Productos</li>
            </ol>
        </div> -->
        <div class="clearfix">
            <h6 class="page-title float-end">Compras</h6>
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
        <div class="card"  style="border-radius:20px;box-shadow:0 4px 6px -1px rgba(0,0,0,.1),0 2px 4px -1px rgba(0,0,0,.06)">
            <div class="card-body">



                <div class="card-title-desc text-end">
                    <a href="/compras/add" class="btn btn-primary button-link">
                        <i class="fa fa-plus "></i> Agregar Compra
                    </a>
                    <a target="_blank" class="btn btn-info" href="https://lencika.com/reporte/compras"><i class="fa fa-file"></i> Exportar Reporte</a>
                   
                </div>


                <div class="table-responsive">
                    <table id="datatable" class="table table-bordered dt-responsive nowrap text-center table-sm" style="border-collapse: collapse; border-spacing: 0; width: 100%;">

                        <thead>
                        <tr>
                            <th style="text-align: center;">Id</th>
                            <th style="text-align: center;">F. Emision</th>
                            <th style="text-align: center;">F. Vencimiento</th>
                            <th style="text-align: center;">Serie</th>
                            <th style="text-align: center;">Numero</th>
                            <th style="text-align: center;" width="50%">Razon Social</th>
                            <th style="text-align: center;">Detalles</th>
                            <th style="text-align: center;">Reporte</th>
                        </tr>
                        </thead>

                    </table>
                </div>

                <div class="modal fade" id="modalDetalle" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered" role="document">
                        <div class="modal-content modal-xl">
                            <div class="modal-header">
                                <h5 class="modal-title" id="exampleModalLabel">Agregar</h5>
                            </div>
                            <div class="modal-body">
                                <table id="datatableProductoDetalle" class="table table-bordered dt-responsive  text-center table-sm" style="border-collapse: collapse; border-spacing: 0; width: 100%;">

                                    <thead>
                                    <tr>
                                        <th style="text-align: center;">Id</th>
                                        <th style="text-align: center;" >Producto</th>
                                        <th style="text-align: center;">Cantidad</th>
                                        <th style="text-align: center;">Precio</th>

                                    </tr>
                                    </thead>

                                </table>
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

        



        datatable = $("#datatable").DataTable({
            order: [[ 0, "desc" ]],
            paging: true,
            bFilter: true,
            ordering: true,
            searching: true,
            destroy: true,
            ajax: {
                url: _URL + "/ajs/prodcutos/compras/render",
                method: "POST",
                dataSrc: "",
            },
            language: {
                url: "ServerSide/Spanish.json",
            },
            columns: [{
                data: "id_compra",
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
                    data: "serie",
                    class: "text-center",
                },
                {
                    data: "numero",
                    class: "text-center",
                },
                {
                    data: "razon_social",
                    class: "text-center",
                },

                {
                    data: null,
                    class: "text-center",
                    render: function(data, type, row) {
                        return `<div class="text-center">
              <div class="btn-group"><button  data-id="${Number(
                            row.id_compra
                        )}" class="btn  btn-sm btn-success btnDetalle"><i class="fa fa-eye"></i> </button></div></div>`;
                    },
                },
                {
                    data: null,
                    class: "text-center",
                    render: function(data, type, row) {
                        return `<div class="text-center">
              <div class="btn-group"><a target="_blank" class="btn btn-sm btn-info" href="${_URL}/reporte/compras/pdf/${row.id_compra}" ><i class="fa fa-file"></i> </a></div></div>`;
                    },
                },
            ],
        });


        
        $("#datatable").on("click", ".btnDetalle ", function(event) {
            $("#loader-menor").show()
            var table = $("#tabla_clientes").DataTable();
            var trid = $(this).closest("tr").attr("id");
            var id = $(this).data("id");
            $("#modalDetalle").modal("show");
            $("#modalDetalle")
                .find(".modal-title")
                .text("Detelle compra N°" + id);
            $.ajax({
                type: 'POST',
                url: _URL + '/ajas/compra/detalle',
                data: {
                    id: id
                },
                success: function(resp) {
                    $("#loader-menor").hide()
                    let data = JSON.parse(resp)
                    datatableProductoDetalle = $("#datatableProductoDetalle").DataTable({

                        paging: true,
                        bFilter: true,
                        ordering: true,
                        searching: true,
                        destroy: true,

                        language: {
                            url: "ServerSide/Spanish.json",
                        },
                        data: data,
                        columns: [{
                            data: "id_producto_venta",
                            class: "text-center",
                        },
                            {
                                data: "descripcion",
                                class: "text-center",
                            },
                            {
                                data: "cantidad",
                                class: "text-center",
                            },
                            {
                                data: "precio",
                                class: "text-center",
                            },
                        ],
                    });
                }
            });

        });
    })
</script>