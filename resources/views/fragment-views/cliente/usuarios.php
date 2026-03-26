<?php

require_once "app/models/Cliente.php";

$c_cliente = new Cliente();
$c_cliente->setIdEmpresa($_SESSION['id_empresa']);

?>
<div class="page-title-box" style="padding: 12px 0;">
    <div class="row align-items-center">
        <div class="col-md-12">
            <h6 class="page-title text-center">DATOS DE USUARIOS</h6>

        </div>

    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card" style="border-radius:20px;box-shadow:0 4px 6px -1px rgba(0,0,0,.1),0 2px 4px -1px rgba(0,0,0,.06)">
            <div class="card-header">
                                <div class="row">
                    <div class="col-md-4">
                        <button type="button" id="add-user" class="btn btn-primary"><i class="fa fa-plus"></i> Agregar</button>
                    </div>
                    <div class="col-md-4 text-end">
                        <button id="btnDesactivar" class="btn btn-success"></i> Desactivar Usuarios</button>
                    </div>
                    <div class="col-md-4 text-end">
                        <button id="btnModalImprimirUsuario" class="btn btn-success"><i class="fa fa-print"></i> Imprimir</button>
                    </div>
                </div>
            </div>
            <div id="conte-vue-modals">
                <div class="card-body">
                    <!-- MODAL CONFIRMAR DATOS -->
                    <div class="modal fade" id="modal-lista-clientes" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
                        <div class="modal-dialog  modal-dialog-scrollable modal-lg modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="staticBackdropLabel">Lista de clientes</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <table class="table table-sm table-bordered text-center" id="tablaImportarCliente">
                                        <thead>
                                            <tr>
                                                <th>Documento</th>
                                                <th>Datos</th>
                                                <th>Direccion</th>
                                                <th>Direccion 2</th>
                                                <th>Telefono</th>
                                                <th>Telefon 2</th>
                                                <th>Email</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tbodyImportar">
                                            <!--  <tr id="trImportar"></tr> -->
                                            <tr id="trImportar" v-for="(item,index) in listaClientes">
                                                <!--  -->
                                                <td>{{item.documento}}</td>
                                                <td> {{item.datos}}</td>
                                                <td>{{item.direccion}}</td>
                                                <td>{{item.direccion2}}</td>
                                                <td>{{item.telefono}}</td>
                                                <td>{{item.telefono2}}</td>
                                                <td>{{item.email}}</td>

                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="modal-footer">
                                    <!--  <button id="agregarClientesImport" type="button" class="btn btn-primary">Guardar</button> -->
                                    <button @click="agregarListaImport" type="button" class="btn btn-primary">Guardar</button>
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>

                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- MODAL DE IMPORTAR XLS -->
                    <div class="modal fade" id="importarModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="exampleModalLabel">Importar Cliente con EXCEL</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <form enctype='multipart/form-data'>
                                        <div class="mb-3">
                                            <p>Descargue el modelo en <span class="fw-bold">EXCEL</span> para importar, no
                                                modifique los campos en el archivo, <span class="fw-bold">click para
                                                    descargar</span> <a href="<?= URL::to("public/templateExcelClientes.xlsx") ?>">template.xlsx</a></p>
                                        </div>
                                        <div class="mb-3">
                                            <label class="col-form-label">Importar Excel:</label>

                                        </div>
                                        <input type="file" id="nuevoExcel" name="nuevoExcel" accept=".csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel">
                                    </form>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cerrar</button>
                                </div>
                            </div>
                        </div>
                    </div>


                    <div class="card-title-desc">
                        <div class="table-responsive">
                            <table id="tabla_clientes" class="table table-bordered dt-responsive nowrap text-center table-sm dataTable no-footer">
                                <thead>
                                    <tr>
                                        <th>Item</th>
                                        <th>Rol</th>
                                        <th>Usuario</th>
                                        <th>Email</th>
                                        <th>Nombres</th>
                                        <th>Tienda</th>
                                        <th>Fecha inicio</th>
                                        <th>Fecha Fin</th>
                                        <th>Funciones</th>
                                        <th>Rotativo</th>
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
<div class="modal fade" id="usuario-add-bs" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Crear Usuario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="myForm">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Rol</label>
                            <select name="rol" id="rol" class="form-control">
                            </select>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Número de documento</label>
                            <input type="text" name="ndoc" id="ndoc" class="form-control" required>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Usuario</label>
                            <input type="text" name="usuario" id="usuario" class="form-control" required>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Clave</label>
                            <input type="text" name="clave" id="clave" class="form-control" required>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Correo</label>
                            <input type="email" name="email" id="email" class="form-control" required>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Nombres</label>
                            <input type="text" name="nombres" id="nombres" class="form-control" required>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">fecha de inicio</label>
                            <input type="date" name="fechaInicio" id="fechaInicio" class="form-control" required>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Fecha de Fin</label>
                            <input type="date" name="fechaFin" id="fechaFin" class="form-control" required>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Funciones</label>
                            <input type="text" name="funciones" id="funciones" class="form-control" required>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label">Tienda</label>
                            <select name="tienda" id="tiendau" class="form-control">
                                <option value="1">Tienda 435</option>
                                <option value="2">Tienda 426</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Rotativo</label>
                            <select name="rotativou" id="rotativou" class="form-control">
                                <option value="0">No</option>
                                <option value="1">Si</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Rutas</label>
                            <select name="rutas" id="rutas" class="form-control">
                                <option value="0">No Aplica</option>
                                <option value="1">1</option>
                                <option value="2">2</option>
                                <option value="3">3</option>
                                <option value="4">4</option>
                                <option value="5">5</option>
                                <option value="6">6</option>
                                <option value="7">7</option>
                            </select>
                        </div>
                        <div class="col-md-12 mb-3 text-center">
                            <button type="button" id="submitButton" class="btn btn-primary">Crear</button>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
<!-- EDITAR MODAL -->
<div class="modal fade" id="editarModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Editar</h5>
            </div>
            <div class="modal-body">
                <form id="clientesEditar">
                    <div class="row">
                        <input type="text" name="idCliente" id="idCliente" value="" hidden>
                        <div class="col-md-4 form-group">
                            <label>Rol</label>
                            <select name="rol" id="rol2" class="form-control">
                            </select>
                        </div>
                        <div class="col-md-8 form-group">
                            <label for="datosAgregar">Nombre</label>
                            <input type="text" class="form-control" id="datosEditar" name="datosEditar">
                        </div>
                        <div class="col-md-6">
                            <label for="doc" class="col-form-label">Número de documento</label>
                            <input type="text" class="form-control" id="doc" name="doc">
                        </div>
                        <div class="col-md-6">
                            <label for="usuariou" class="col-form-label">Usuario</label>
                            <input type="text" class="form-control" id="usuariou" name="usuariou">
                        </div>
                        <!--<div class="col-md-6 form-group">-->
                        <!--    <label for="claveu" class="col-form-label">Clave</label>-->
                        <!--    <input type="text" class="form-control" id="claveu" name="claveu">-->
                        <!--</div>-->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">fecha de inicio</label>
                            <input type="date" name="fechaInicio" id="fechaIniciou" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Fecha de Fin</label>
                            <input type="date" name="fechaFin" id="fechaFinu" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Funciones</label>
                            <input type="text" name="funciones" id="funcionesu" class="form-control" required>
                        </div>
                        <div class="col-md-6 ">
                            <label>Tienda</label>
                            <select name="tiendau" id="tiendau" class="form-control">
                                <option value="1">Tienda 435</option>
                                <option value="2">Tienda 426</option>
                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Rotativo</label>
                            <select name="rotativou" id="rotativou" class="form-control">
                                <option value="0">No</option>
                                <option value="1">Si</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Rutas</label>
                            <select name="rutasu" id="rutasu" class="form-control">
                                <option value="0">No Aplica</option>
                                <option value="1">1</option>
                                <option value="2">2</option>
                                <option value="3">3</option>
                                <option value="4">4</option>
                                <option value="5">5</option>
                                <option value="6">6</option>
                                <option value="7">7</option>
                            </select>
                        </div>
                        <div class="col-md-6 ">
                            <label for="emailEditar" class="col-form-label">Email</label>
                            <input required type="text" class="form-control" id="emailEditar" name="emailEditar">
                        </div>

                    </div>

                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cerrar</button>
                <button id="updateCliente" type="button" class="btn btn-primary">Guardar</button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL IMPRIMIR -->
<div class="modal" id="modalImprimirUsuario">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Imprimir Reportes</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div>
                        <h5>Seleccione el vendedor:</h5>
                        <select class="form-control" id="vendedorModalImprimirUsuario">
                            <option value="0">Todos</option>
                        </select>
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

        tabla_clientes = $("#tabla_clientes").DataTable({
            paging: true,
            bFilter: true,
            ordering: true,
            searching: true,
            destroy: true,
            ajax: {
                url: _URL + "/ajs/usuarios/render",
                method: "POST", //usamos el metodo POST
                dataSrc: "",
            },
            language: {
                url: "ServerSide/Spanish.json",
            },
            columns: [{
                    data: "usuario_id",
                    class: "text-center",
                },
                {
                    data: "nombre",
                    class: "text-center",
                },
                {
                    data: "usuario",
                    class: "text-center",
                },
                {
                    data: "email",
                    class: "text-center",
                },
                {
                    data: "nombres",
                    class: "text-center",
                },
                {
                    data: "tienda",
                    class: "text-center",
                },
                {
                    data: "fecha_inicio",
                    class: "text-center",
                },
                {
                    data: "fecha_salida",
                    class: "text-center",
                },
                {
                    data: "funciones",
                    class: "text-center",
                },
                {
                    data: "rotativo",
                    class: "text-center",
                },
                {

                    /* href="' + _URL + '/files/facturacion/xml/ */
                    data: null,
                    class: "text-center",
                    render: function(data, type, row) {
                        return `<div class="text-center">
            <div class="btn-group btn-sm"><button  data-id="${Number(row.usuario_id)}" class="btn btn-sm btn-warning btnEditar"
            ><i class="fa fa-edit"></i> </button>
            <button btn-sm  data-id="${Number(row.usuario_id)}" class="btn btn-sm  btn-danger btnBorrar"><i class="fa fa-trash"></i> </button>
            </div></div>`;
                    },
                },
            ],
        });

        $("#tabla_clientes").on("click", ".btnEditar ", function(event) {
            $("#loader-menor").show();
            var table = $("#tabla_clientes").DataTable();
            var trid = $(this).closest("tr").attr("id");
            var id = $(this).data("id");
            $("#editarModal").modal("show");
            $("#editarModal")
                .find(".modal-title")
                .text("Editar Usuario N°" + id);
            $.ajax({
                url: _URL + "/ajs/usuarios/getOne",
                data: {
                    id: id,
                },
                type: "post",
                success: function(datos) {
                    $.ajax({
                        type: "POST",
                        url: _URL + "/ajs/getroles",
                        success: function(response) {
                            let data = JSON.parse(response);
                            let options = '';
                            $.each(data, function(i, d) {
                                options += `<option value="${d.rol_id}">${d.nombre}</option>`;
                            });
                            $('#rol2').html(options);
                            $("#loader-menor").hide();
                            let json = JSON.parse(datos)[0];
                            
                            $("#rol2").val(json.id_rol);
                            $("#doc").val(json.num_doc);
                            $("#datosEditar").val(json.nombres);
                            $("#usuariou").val(json.usuario);
                            $("#emailEditar").val(json.email);
                            $("#nombresu").val(json.usuario);
                            // $("#tiendau").val(json.sucursal);
                             $("#tiendau option").each(function() {
                                if ($(this).val() == json.sucursal) {
                                    $(this).prop("selected", true);
                                }
                            });
                            $('#rutasu').val(json.id_ruta);
                            // $("#rotativou").val(json.rotativo);
                            let valor = json.rotativo;
                            $("#rotativou option").each(function() {
                                if ($(this).val() == valor) {
                                    $(this).prop("selected", true);
                                }
                            });
                            $("#idCliente").val(id);
                            $("#trid").val(trid);
                            $("#fechaIniciou").val(json.fecha_inicio);
                            $("#fechaFinu").val(json.fecha_salida);
                            $("#funcionesu").val(json.funciones);
                            
                        },
                        error: function(response) {
                            console.log(response);
                        }
                    });

                },
            });
        });
        $("#updateCliente").click(function() {
            $("#loader-menor").show();
            let data = $("#clientesEditar").serializeArray();
            let id = $("#idCliente").val();
           
            $.ajax({
                url: _URL + "/ajs/usuarios/editar",
                type: "POST",
                data: data,
                success: function(resp) {
                    $("#loader-menor").hide();
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
                        url: _URL + "/ajs/usuarios/borrar",
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

        $('#add-user').on('click', function() {
            $.ajax({
                type: "POST",
                url: _URL + "/ajs/getroles",
                success: function(response) {
                    let data = JSON.parse(response);
                    let options = '';
                    $.each(data, function(i, d) {
                        options += `<option value="${d.rol_id}">${d.nombre}</option>`;
                    });
                    $('#rol').html(options);
                    $('#rol2').html(options);
                    $('#usuario-add-bs').modal('show');
                },
                error: function(response) {
                    console.log(response);
                }
            });
        });
        $('#submitButton').click(function() {
            // Verifica si todos los campos obligatorios están llenos
            if ($('#rol').val() && $('#ndoc').val() && $('#usuario').val() && $('#clave').val() && $('#email').val() && $('#nombres').val()) {
                // Recolecta los datos del formulario
                var formData = $('#myForm').serialize();

                // Envía los datos mediante AJAX al endpoint adecuado
                $.ajax({
                    url: _URL + "/ajs/add/users",
                    type: 'POST',
                    data: formData,
                    success: function(response) {
                        Swal.fire({
                            title: "Exito",
                            text: "Usuario creado correctamente.",
                            icon: "info"
                        });
                        $('#usuario-add-bs').modal('hide');
                        tabla_clientes.ajax.reload(null, false);
                    },
                    error: function(xhr, status, error) {
                        // Maneja el error en caso de que la solicitud AJAX falle
                        console.error(xhr.responseText);
                    }
                });
            } else {
                // Si algún campo está vacío, muestra un mensaje de error
                Swal.fire({
                    title: "Error",
                    text: "Por favor, completa todos los campos obligatorios.",
                    icon: "error"
                });
            }
        });
         //DESACTIVAR CLIENTES 
        function actualizarBoton() {
            $.ajax({
                url: _URL + "/ajs/usuarios/render",
                method: "POST",
                dataType: "json",
                success: function(data) {
                    console.log("Usuarios recibidos:", data);

                    // Filtrar solo usuarios que no tienen id_rol == 1
                    let usuariosNoRol1 = data.filter(user => Number(user.id_rol) !== 1);

                    // Revisar si TODOS los usuarios que no son del rol 1 tienen available_status = "0"
                    let todosDesactivados = usuariosNoRol1.every(user => user.available_status === "0");

                    // Cambiar el color y el texto del botón
                    $("#btnDesactivar")
                        .toggleClass("btn-danger", !todosDesactivados)
                        .toggleClass("btn-success", todosDesactivados)
                        .html(todosDesactivados ? "Activar Usuarios" : "Desactivar Usuarios");
                },
                error: function() {
                    console.error("Error al obtener los datos de los usuarios.");
                }
            });
        }

        // Llamar a la función cuando cargue la página
        $(document).ready(function() {
            actualizarBoton();
        });


        $("#btnDesactivar").on("click", function() {
            let nuevoEstado = $("#btnDesactivar").text().includes("Desactivar") ? 0 : 1;

            $.ajax({
                url: _URL + "/ajs/usuarios/toggleDisponibilidad", // Asegúrate de que el backend acepte activar también
                type: "POST",
                data: {
                    estado: nuevoEstado
                }, // Envía el nuevo estado
                dataType: "json",
                success: function(resp) {
                    if (resp.success) {
                        Swal.fire("¡Buen trabajo!", resp.message, "success");
                        actualizarBoton(); // Actualiza el botón después del cambio
                        tabla_clientes.ajax.reload(); // Recargar la DataTable
                    } else {
                        Swal.fire("¡Error!", resp.message, "error");
                    }
                },
                error: function() {
                    Swal.fire("¡Error!", "Hubo un problema con la solicitud.", "error");
                },
            });
        });

        // IMPRIMIR USUARIO CLIENTES
        $(document).on('click','#btnModalImprimirUsuario',function(){
            
            $.ajax({
                type: "POST",
                url: _URL + "/ajs/usuarios/vendedores",
                success: function(response) {
                    let data = JSON.parse(response);
                    let options = '';
                    $.each(data, function(i, d) {
                        options += `<option value="${d.usuario_id}">${d.nombres}</option>`;
                    });
                    $('#vendedorModalImprimirUsuario').html(options);
                    $("#modalImprimirUsuario").modal('show');
                },
                error: function(response) {
                    console.log(response);
                }
            });
        });

        $(document).on('click','#btnImprimirUsuarioClientes',function(e){

            e.preventDefault();
            let data = new FormData();
            data.append('id_usuario',$("#vendedorModalImprimirUsuario").val());
            let params = new URLSearchParams(data);
            let ruta = `/r/usuario/clientes/pdf?${params}`;
            console.log(ruta);
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