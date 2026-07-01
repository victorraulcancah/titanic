<?php

require_once 'app/models/Cliente.php';

$c_cliente = new Cliente();
$c_cliente->setIdEmpresa($_SESSION['id_empresa']);
if(isset($_SESSION['rutas']) && sizeof($_SESSION['rutas'])>0){
    $rutas = $_SESSION['rutas'];
}else{
    require_once 'app/models/RutaVendedor.php';
    $rutaVendedor = new RutaVendedor();
    $rutas = $rutaVendedor->obtenerDatos();
}
?>
<div class="page-title-box" style="padding: 12px 0;">
    <div class="row align-items-center">
        <div class="col-md-12">
            <h6 class="page-title text-center">DATOS DE CLIENTES</h6>

        </div>

    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card"
            style="border-radius:20px;box-shadow:0 4px 6px -1px rgba(0,0,0,.1),0 2px 4px -1px rgba(0,0,0,.06)">
            <div class="card-header">
                <div class="row">
                    <div class="col-md-6">
                        <button type="button" data-bs-toggle="modal" data-bs-target="#agregarModal"
                            class="btn btn-primary"><i class="fa fa-plus"></i> Agregar</button>
                        <!--   <button type="button" data-bs-toggle="modal" data-bs-target="#editarModal" class="btn btn-warning">Editar</button> -->
                    </div>

                    <div class="col-md-6 text-end">
                        <button type="button" data-bs-toggle="modal" data-bs-target="#importarModal"
                            class="btn btn-success"><i class="fa fa-file-excel"></i> Importar</button>
                        <a href="/r/clientes/reporte/xls" class="btn btn-success"><i class="fa fa-file-excel"></i> Exportar</a>
                        <!-- <button class="btn btn-success"><i class="fa fa-file-excel"></i> Importar</button> -->
                        <button id="btnModalImprimirClientes" class="btn btn-success"><i class="fa fa-print"></i> Imprimir</button>
                    </div>
                </div>
            </div>
            <div id="conte-vue-modals">
                <div class="card-body">
                    <!-- MODAL CONFIRMAR DATOS -->
                    <div class="modal fade" id="modal-lista-clientes" data-bs-backdrop="static" data-bs-keyboard="false"
                        tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                        <div class="modal-dialog  modal-dialog-scrollable modal-lg modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="staticBackdropLabel">Lista de clientes</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <table class="table table-sm table-bordered text-center" id="tablaImportarCliente">
                                        <thead>
                                            <tr>
                                                <th>Documento</th>
                                                <th>Datos</th>
                                                <th>Direccion</th>
                                                <th>Distrito</th>
                                                <th>Telefono</th>
                                                <th>Dias Visita</th>
                                                <th>Ruta</th>
                                                <th>Mercado</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tbodyImportar">
                                            <!--  <tr id="trImportar"></tr> -->
                                            <tr id="trImportar" v-for="(item,index) in listaClientes">
                                                <!--  -->
                                                <td>{{ item . documento }}</td>
                                                <td> {{ item . datos }}</td>
                                                <td>{{ item . direccion }}</td>
                                                <td>{{ item . distrito }}</td>
                                                <td>{{ item . telefono }}</td>
                                                <td>{{ item . visita }}</td>
                                                <td>{{ item . ruta }}</td>
                                                <td>{{ item . mercado }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="modal-footer">
                                    <!--  <button id="agregarClientesImport" type="button" class="btn btn-primary">Guardar</button> -->
                                    <button @click="agregarListaImport" type="button"
                                        class="btn btn-primary">Guardar</button>
                                    <button type="button" class="btn btn-secondary"
                                        data-bs-dismiss="modal">Cancelar</button>

                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- MODAL DE IMPORTAR XLS -->
                    <div class="modal fade" id="importarModal" tabindex="-1" aria-labelledby="exampleModalLabel"
                        aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="exampleModalLabel">Importar Cliente con EXCEL</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <form enctype='multipart/form-data'>
                                        <div class="mb-3">
                                            <p>Descargue el modelo en <span class="fw-bold">EXCEL</span> para importar,
                                                no
                                                modifique los campos en el archivo, <span class="fw-bold">click para
                                                    descargar</span> <a
                                                    href="<?= URL::to('public/templateExcelClientes.xlsx') ?>">template.xlsx</a>
                                            </p>
                                        </div>
                                        <div class="mb-3">
                                            <label class="col-form-label">Importar Excel:</label>

                                        </div>
                                        <input type="file" id="nuevoExcel" name="nuevoExcel"
                                            accept=".csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel">
                                    </form>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-danger"
                                        data-bs-dismiss="modal">Cerrar</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- MODAL DE AGREGAR CLIENTE -->
                    <div class="modal fade" id="agregarModal" tabindex="-1" role="dialog"
                        aria-labelledby="exampleModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="exampleModalLabel">Agregar</h5>
                                </div>
                                <div class="modal-body">
                                    <form id="frmClientesAgregar">
                                        <div class="row">
                                            <div class="col-md-4 form-group">
                                                <label>DNI<span style="color: red;"> (*)</span> </label>
                                                <div class="input-group">
                                                    <input type="text" class="form-control" required
                                                        id="documentoAgregar" name="documentoAgregar">
                                                    <div class="input-group-prepend">
                                                        <button id="btnBuscarInfo" class="btn btn-primary"><i
                                                                class="fa fa-search"></i></button>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-8 form-group">
                                                <label for="datosAgregar">Datos<span style="color: red;">
                                                        (*)</span></label>
                                                <input type="text" class="form-control" id="datosAgregar"
                                                    name="datosAgregar">
                                            </div>


                                            <div class="col-md-4 mt-3">
                                                <label for="direccionAgregar">Dirección</label>
                                                <input type="text" class="form-control" id="direccionAgregar"
                                                    name="direccionAgregar">
                                            </div>
                                            <div class="col-md-4 mt-3">
                                                <label for="distrito">Distrito</label>
                                                <input type="text" class="form-control" id="distrito"
                                                    name="distrito">
                                            </div>
                                            <div class="col-md-4 mt-3">
                                                <label for="telefonoAgregar">Teléfono</label>
                                                <input type="number" class="form-control" id="telefonoAgregar"
                                                    name="telefonoAgregar">
                                            </div>
                                            <div class="col-md-4 mt-3">
                                                <label for="diasvisita">Días de visita</label>
                                                <input type="text" class="form-control" id="visita"
                                                    name="visita">
                                            </div>
                                            <div class="col-md-4 mt-3">
                                                <label for="ruta">Ruta</label>
                                                <select name="ruta" id="ruta" class="form-control">
                                                <?php
                                                foreach ($rutas as $key => $ruta) {
                                                ?>
                                                    <option value="<?php echo $ruta ?>"><?php echo $ruta ?></option>
                                                <?php
                                                }
                                                ?>
                                                </select>
                                            </div>
                                            <div class="col-md-4 mt-3">
                                                <label for="diasvisita">Mercado</label>
                                                <input type="text" class="form-control" id="mercado"
                                                    name="mercado">
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-danger"
                                        data-bs-dismiss="modal">Cerrar</button>
                                    <button id="nuevoCliente" type="button" class="btn btn-primary">Guardar</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- EDITAR MODAL -->
                    <div class="modal fade" id="editarModal" tabindex="-1" role="dialog"
                        aria-labelledby="exampleModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="exampleModalLabel">Editar</h5>

                                </div>
                                <div class="modal-body">
                                    <form id="clientesEditar">
                                        <div class="row">
                                            <div class="col-md-4 form-group">
                                                <label>DNI<span style="color: red;"> (*)</span> </label>
                                                <div class="input-group">
                                                    <input type="hidden" name="idCliente" id="idCliente"
                                                        value="">
                                                    <input type="hidden" name="trid" id="trid"
                                                        value="">
                                                    <input type="text" class="form-control" id="documentoEditar"
                                                        name="documentoEditar">
                                                    <div class="input-group-prepend">
                                                        <button id="btnBuscarInfoEditar" class="btn btn-primary"><i
                                                                class="fa fa-search"></i></button>
                                                    </div>
                                                </div>
                                            </div>
                                            <!--    <div class="col-md-4 mt-3">
                                            <input type="hidden" name="idCliente" id="idCliente" value="">
                                            <input type="hidden" name="trid" id="trid" value="">
                                            <label for="documentoEditar" class="col-form-label">Documento <span style="color: red;">(*)</span></label>
                                            <input type="text" class="form-control" id="documentoEditar" name="documentoEditar">
                                        </div> -->
                                            <div class="col-md-8 form-group">
                                                <label for="datosAgregar">Nombre/Razon Social <span
                                                        style="color: red;"> (*)</span></label>
                                                <input type="text" class="form-control" id="datosEditar"
                                                    name="datosEditar">
                                            </div>


                                            <div class="col-md-4 mt-3">
                                                <label for="direccionEditar" class="col-form-label">Direccion</label>
                                                <input type="text" class="form-control" id="direccionEditar"
                                                    name="direccionEditar">
                                            </div>
                                            <div class="col-md-4 mt-3">
                                                <label for="distrito" class="col-form-label">Distrito</label>
                                                <input type="text" class="form-control" id="distritoEditar"
                                                    name="distritoEditar">
                                            </div>
                                            <div class="col-md-4 mt-3">
                                                <label for="telefonoEditar" class="col-form-label">Telefono</label>
                                                <input type="number" class="form-control" id="telefonoEditar"
                                                    name="telefonoEditar">
                                            </div>
                                            <div class="col-md-4 mt-3">
                                                <label for="visita" class="col-form-label">Días de visita</label>
                                                <input type="text" class="form-control" id="visitasEditar"
                                                    name="visitasEditar">
                                            </div>
                                            <div class="col-md-4 mt-3">
                                                <label for="rutaEditar" class="col-form-label">Ruta</label>
                                                <select name="rutaEditar" id="rutaEditar" class="form-control">
                                                <?php
                                                foreach ($rutas as $key => $ruta) {
                                                ?>
                                                    <option value="<?php echo $ruta ?>"><?php echo $ruta ?></option>
                                                <?php
                                                }
                                                ?>
                                                </select>
                                            </div>
                                            <div class="col-md-4 mt-3">
                                                <label for="diasvisita" class="col-form-label">Mercado</label>
                                                <input type="text" class="form-control" id="mercadoEditar"
                                                    name="mercadoEditar">
                                            </div>

                                            <!-- <div class="col-md-8 mt-3">
                                                <label for="emailEditar" class="col-form-label">Email</label>
                                                <input required type="text" class="form-control" id="emailEditar"
                                                    name="emailEditar">
                                            </div> -->

                                        </div>

                                    </form>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-danger"
                                        data-bs-dismiss="modal">Cerrar</button>
                                    <button id="updateCliente" type="button"
                                        class="btn btn-primary">Guardar</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-title-desc">
                        <div class="table-responsive">
                            <table id="tabla_clientes"
                                class="table table-bordered dt-responsive nowrap text-center table-sm dataTable no-footer">
                                <thead>
                                    <tr>
                                        <th>Item</th>
                                        <th>Documento</th>
                                        <th>Nombre/Razon Social</th>
                                        <th>Dirección</th>
                                        <th>Distrito</th>
                                        <th>Télefono</th>
                                        <th>Días Visita</th>
                                        <th>Rutas</th>
                                        <th>Mercado</th>
                                        <th>Acciones</th>
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
<!-- MODAL IMPRIMIR -->
<div class="modal" id="modalImprimirClientes">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Imprimir Reportes</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div>
                        <div class="form-group">
                            <label for="">Día Visita:</label>
                            <select class="form-control" id="diasVisita">
                                <option value="lunes">Lunes</option>
                                <option value="martes">Martes</option>
                                <option value="miercoles">Miércoles</option>
                                <option value="jueves">Jueves</option>
                                <option value="viernes">Viernes</option>
                                <option value="sabado">Sábado</option>
                                <option value="domingo">Domingo</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="">Ruta:</label>
                            <select class="form-control" id="rutaModalImprimirClientes">
                                <option value="0">Todos</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="btnImprimirUsuarioClientes">Imprimir</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {

        const app = new Vue({
            el: "#conte-vue-modals",
            data: {
                listaClientes: []
            },
            methods: {
                agregarListaImport() {

                    if (this.listaClientes.length > 0) {

                        _ajax("/ajs/clientes/add/por/lista", "POST", {
                                lista: JSON.stringify(this.listaClientes)
                            },
                            function(resp) {
                                console.log(resp);
                                /* return */
                                if (resp.res) {
                                    alertExito("Agregado")
                                        .then(function() {
                                            location.reload()
                                        })
                                } else {
                                    alertAdvertencia("No se pudo Agregar")
                                }
                            }
                        )
                    } else {
                        alertAdvertencia("La lista esta vacia")
                    }
                },


            }
        })

        tabla_clientes = $("#tabla_clientes").DataTable({
            paging: true,
            bFilter: true,
            ordering: true,
            searching: true,
            destroy: true,
            ajax: {
                url: _URL + "/ajs/clientes/render",
                method: "POST", //usamos el metodo POST
                dataSrc: "",
            },
            language: {
                url: "ServerSide/Spanish.json",
            },
            columns: [{
                    data: "id_cliente",
                    class: "text-center",
                },
                {
                    data: "documento",
                    class: "text-center",
                },
                {
                    data: "datos",
                    class: "text-center",
                },
                {
                    data: "direccion",
                    class: "text-center",
                },
                {
                    data: "distrito",
                    class: "text-center",
                },
                {
                    data: "telefono",
                    class: "text-center",
                },
                {
                    data: "dias_visitas",
                    class: "text-center",
                },
                {
                    data: null,
                    class: "text-center",
                    render: function(data, type, row) {
                        return `<div class="text-center">
                            ${row.id_ruta || '-'}
                        </div>`;
                    },
                },
                {
                    data: "mercado",
                    class: "text-center",
                },
                {

                    /* href="' + _URL + '/files/facturacion/xml/ */
                    data: null,
                    class: "text-center",
                    render: function(data, type, row) {
                        <?php if ($_SESSION["rol"] == 3): ?>
                            // Vendedores: solo pueden ver reporte, no editar ni eliminar
                            return `<div class="text-center">
                <div class="btn-group btn-sm">
                <button class="btn btn-sm btn-secondary" disabled title="No tienes permiso para editar"><i class="fa fa-edit"></i></button>
                <button class="btn btn-sm btn-secondary" disabled title="No tienes permiso para eliminar"><i class="fa fa-trash"></i></button>
                <a href="${_URL}/reporte/cliente/${Number(row.id_cliente)}" target="_blank" class="btn btn-sm btn-info"><i class="fa fa-file"></i></a>
                </div></div>`;
                        <?php else: ?>
                            // Administradores: pueden hacer todo
                            return `<div class="text-center">
                <div class="btn-group btn-sm"><button  data-id="${Number(row.id_cliente)}" class="btn btn-sm btn-warning btnEditar"
                ><i class="fa fa-edit"></i> </button>
                <button btn-sm  data-id="${Number(row.id_cliente)}" class="btn btn-sm  btn-danger btnBorrar"><i class="fa fa-trash"></i> </button>
                <a href="${_URL}/reporte/cliente/${Number(row.id_cliente)}" target="_blank" class="btn btn-sm btn-info"><i class="fa fa-file"></i></a>
                </div></div>`;
                        <?php endif; ?>
                    },
                },
            ],
        });
        $("#nuevoCliente").click(function() {
            let $btn = $(this);
            if ($btn.data('submitting')) return;
            $btn.data('submitting', true).prop('disabled', true);
            $("#loader-menor").show();
            let data = $("#frmClientesAgregar").serializeArray();
            $.ajax({
                type: "POST",
                url: _URL + "/ajs/clientes/add",
                data: data,
                success: function(resp) {
                    $("#loader-menor").hide();
                    let data = JSON.parse(resp);
                    if (typeof data === "object") {
                        tabla_clientes.ajax.reload(null, false);
                        Swal.fire("¡Buen trabajo!", "Registro Exitoso", "success");
                        $("#agregarModal").modal("hide");
                        $("body").removeClass("modal-open");
                        $("#frmClientesAgregar").trigger("reset");
                    } else {
                        Swal.fire({
                            icon: "error",
                            title: "Error",
                            text: JSON.parse(resp),
                        });
                    }
                },
                complete: function() {
                    $btn.data('submitting', false).prop('disabled', false);
                },
            });
        });
        
        $("#tabla_clientes").on("click", ".btnEditar ", function(event) {
            $("#loader-menor").show();
            var table = $("#tabla_clientes").DataTable();
            var trid = $(this).closest("tr").attr("id");
            var id = $(this).data("id");
            $("#editarModal").modal("show");
            $("#editarModal")
                .find(".modal-title")
                .text("Editar cliente N°" + id);
            $.ajax({
                url: _URL + "/ajs/clientes/getOne",
                data: {
                    id: id,
                },
                type: "post",
                success: function(data) {
                    $("#loader-menor").hide();
                    let json = JSON.parse(data);
                    let datos = json[0];
                    console.log(datos);
                    $("#documentoEditar").val(datos.documento);
                    $("#datosEditar").val(datos.datos);
                    $("#direccionEditar").val(datos.direccion);
                    $("#distritoEditar").val(datos.distrito);
                    $("#telefonoEditar").val(datos.telefono);
                    $("#visitasEditar").val(datos.dias_visitas);
                    $("#emailEditar").val(datos.email);
                    if(datos.id_ruta) $("#rutaEditar").val(datos.id_ruta);
                    if(datos.mercado) $("#mercadoEditar").val(datos.mercado);
                    $("#idCliente").val(id);
                    $("#trid").val(trid);
                },
            });
        });
        $("#updateCliente").click(function() {
            $("#loader-menor").show();
            let data = $("#clientesEditar").serializeArray();
            let id = $("#idCliente").val();
            let idData = {
                name: "idPre",
                value: id,
            };
            $.ajax({
                url: _URL + "/ajs/clientes/editar",
                type: "POST",
                data: data,
                success: function(resp) {
                    $("#loader-menor").hide();
                    let data = JSON.parse(resp);
                    console.log(resp);
                    if (Array.isArray(data)) {
                        tabla_clientes.ajax.reload(null, false);
                        Swal.fire("¡Buen trabajo!", "Actualización exitosa", "success");
                        $("#editarModal").modal("hide");
                        $("body").removeClass("modal-open");
                    } else {
                        Swal.fire({
                            icon: "error",
                            title: "Error",
                            text: JSON.parse(resp),
                        });
                    }
                },
            });
        });
        $("#tabla_clientes").on("click", ".btnBorrar", function() {
            var id = $(this).data("id");
            let idData = {
                name: "idDelete",
                value: id,
            };
            Swal.fire({
                title: "¿Deseas borrar el registro?",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Si",
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: _URL + "/ajs/clientes/borrar",
                        type: "post",
                        data: idData,
                        success: function(resp) {
                            /* console.log(resp); */
                            tabla_clientes.ajax.reload(null, false);
                            Swal.fire(
                                "¡Buen trabajo!",
                                "Registro Borrado Exitosamente",
                                "success"
                            );
                        },
                    });
                } else {}
            });
        });
        $("#btnBuscarInfo").click(function(e) {
            e.preventDefault();
            if (!$("#documentoAgregar").val()) {
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: "Debe ingresar un DNI o RUC",
                });
            } else {
                if (
                    $("#documentoAgregar").val().length === 8 ||
                    $("#documentoAgregar").val().length === 11
                ) {
                    let docu = $("#documentoAgregar").val();
                    $("#loader-menor").show();
                    $.ajax({
                        url: _URL + "/ajs/consulta/doc/cliente",
                        type: "post",
                        data: {
                            doc: docu
                        },
                        success: function(resp) {
                            $("#loader-menor").hide();
                            let datos = JSON.parse(resp);
                            console.log(datos.data);
                            /*  console.log(resp); */
                            if (datos.data.nombre) {
                                $("#datosAgregar").val(datos.data.nombre);
                            } else if (datos.data.razon_social) {
                                $("#datosAgregar").val(datos.data.razon_social);
                            } else {
                                alertAdvertencia("Documento no encontrado");
                            }
                            console.log(datos.data.direccion)
                            $("#direccionAgregar").val(datos.data.direccion || '');
                            /* $("#datosAgregar").val(datos.data.dni);   */
                            //PRUEBA RUC 10427993120
                        },
                    });
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: "Debe ingresar un DNI o RUC",
                    });
                }
            }
        });
        $("#btnBuscarInfoEditar").click(function(e) {
            e.preventDefault();
            if (!$("#documentoEditar").val()) {
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: "Debe ingresar un DNI o RUC",
                });
            } else {
                if (
                    $("#documentoEditar").val().length === 8 ||
                    $("#documentoEditar").val().length === 11
                ) {
                    let docu = $("#documentoEditar").val();
                    $("#loader-menor").show();
                    $.ajax({
                        url: _URL + "/ajs/consulta/doc/cliente",
                        type: "post",
                        data: {
                            doc: docu
                        },
                        success: function(resp) {
                            $("#loader-menor").hide();
                            let datos = JSON.parse(resp);
                            console.log(datos.data);
                            console.log(resp);
                            if (datos.data.nombre) {
                                $("#datosEditar").val(datos.data.nombre);
                            } else if (datos.data.razon_social) {
                                $("#datosEditar").val(datos.data.razon_social);
                            } else {
                                alertAdvertencia("Documento no encontrado");
                            }
                            if(datos.data.direccion && datos.data.direccion!="" && datos.data.direccion!="-"){
                                $("#direccionEditar").val(datos.data.direccion);
                            }
                            /* $("#datosAgregar").val(datos.data.dni);   */
                            //PRUEBA RUC 10427993120
                        },
                    });
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text: "Debe ingresar un DNI o RUC",
                    });
                }
            }
        });



        $("#nuevoExcel").change(function() {
            console.log("aaaaaaaa")
            if ($("#nuevoExcel").val().length > 0) {
                var fd = new FormData();
                fd.append('file', $("#nuevoExcel")[0].files[0]);
                $.ajax({
                    type: 'POST',
                    url: _URL + "/ajs/clientes/add/exel",
                    data: fd,
                    contentType: false,
                    cache: false,
                    processData: false,
                    beforeSend: function() {
                        console.log('inicio');
                        $("#loader-menor").show();
                    },
                    error: function(err) {
                        $("#loader-menor").hide();
                        console.log(err);
                    },
                    success: function(resp) {
                        $("#loader-menor").hide();
                        console.log(resp);
                        /* return */
                        resp = JSON.parse(resp)
                        if (resp.res) {
                            var bloc = true;
                            var listaTemp = [];
                            resp.data.forEach(function(el) {
                                if (!bloc) {
                                    listaTemp.push({
                                        documento: el[0],
                                        datos: el[1],
                                        direccion: el[2],
                                        distrito: el[3],
                                        telefono: el[4],
                                        visita: el[5],
                                        ruta: el[6],
                                        mercado: el[7],
                                    })
                                }
                                bloc = false
                            })
                            app._data.listaClientes = listaTemp
                            $("#importarModal").modal("hide")
                            $("#modal-lista-clientes").modal("show")
                        } else {
                            alertAdvertencia("No se pudo subir el Archivo")
                        }
                        $("#nuevoExcel").val("")

                    }
                })
            }
        })

        // IMPRIMIR USUARIO CLIENTES
        cargarRutas();
        function cargarRutas(){            
            $.ajax({
                type: "GET",
                url: _URL + "/ajs/admin/cliente/rutas",
                success: function(response) {
                    let datos = JSON.parse(response);
                    let options = '';
                    $.each(datos, function(i, data) {
                        options += `<option value="${data.id_ruta}">${data.id_ruta}</option>`;
                    });
                    $('#rutaModalImprimirClientes').html(options);
                },
                error: function(response) {
                    console.log(response);
                }
            });
        }

        $(document).on('click','#btnModalImprimirClientes',function(){
            $("#modalImprimirClientes").modal('show');
        });

        $(document).on('click','#btnImprimirUsuarioClientes',function(e){

            e.preventDefault();
            let data = new FormData();
            data.append('id_ruta',$("#rutaModalImprimirClientes").val());
            data.append('dia_visita',$("#diasVisita").val());
            let params = new URLSearchParams(data);
            let ruta = `r/clientes/diavisita/pdf?${params}`;
            console.log(ruta);
            // let win = window.open(ruta,'_self');
            // win.onload = function(){
            //     win.print();
            // }
            //creamos un iframe oculto para cargar el pdf
            let iframe = document.createElement('iframe');
            iframe.style.display = 'none';
            iframe.src = ruta; 
            document.body.appendChild(iframe);

            // Esperar a que el iframe se cargue y luego abrir el cuadro de diálogo de impresión
            iframe.onload = function() {
                iframe.contentWindow.print();
            };
        });
        
    });
</script>
