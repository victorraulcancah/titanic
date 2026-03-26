<?php

    require_once "app/models/Cliente.php";

    $c_cliente = new Cliente();
    $c_cliente->setIdEmpresa($_SESSION['id_empresa']);
?>
<style>
    .text-left {
        text-align: left;
    }

    #tabla_clientes {
        width: 100% !important;
    }
</style>
<div class="page-title-box" style="padding: 12px 0;">
    <div class="row align-items-center">
        <div class="col-md-12">
            <h6 class="page-title text-center">DATOS DE PROVEEDORES</h6>

        </div>

    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <div class="row">
                    <div class="col-md-6">
                        <button type="button" data-bs-toggle="modal" data-bs-target="#agregarModal"
                                class="btn btn-primary"><i class="fa fa-plus"></i> Agregar
                        </button>
                        <!--   <button type="button" data-bs-toggle="modal" data-bs-target="#editarModal" class="btn btn-warning">Editar</button> -->
                    </div>

                    <div class="col-md-6 text-end">
                        <button type="button" data-bs-toggle="modal" data-bs-target="#importarModal"
                                class="btn btn-success"><i class="fa fa-file-excel"></i> Importar
                        </button>
                        <!-- <button class="btn btn-success"><i class="fa fa-file-excel"></i> Importar</button> -->
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
                                    <h5 class="modal-title" id="staticBackdropLabel">Lista de Proveedores</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                            aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <table class="table table-sm table-bordered text-center" id="tablaImportarCliente">
                                        <thead>
                                        <tr>
                                            <th>RUC</th>
                                            <th>Razon Social</th>
                                            <th>Direccion</th>
                                            <th>Direccion2</th>
                                            <th>Telefono</th>
                                            <th>Telefono2</th>
                                            <th>Email</th>
                                        </tr>
                                        </thead>
                                        <tbody id="tbodyImportar">
                                        <!--  <tr id="trImportar"></tr> -->
                                        <tr id="trImportar" v-for="(item,index) in listaClientes">
                                            <!--  -->
                                            <td>{{item.ruc}}</td>
                                            <td> {{item.razon_social}}</td>
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
                                    <button @click="agregarListaImport" type="button" class="btn btn-primary">Guardar
                                    </button>
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar
                                    </button>

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
                                    <h5 class="modal-title" id="exampleModalLabel">Importar Proveedores con EXCEL</h5>
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
                                                        href="<?=URL::to("public/plantillaproveedores.xlsx")?>">template.xlsx</a>
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
                                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cerrar</button>
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
                                                           id="documentoAgregar" name="ruc">
                                                    <div class="input-group-prepend">
                                                        <button id="btnBuscarInfo" class="btn btn-primary"><i
                                                                    class="fa fa-search"></i></button>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-8 form-group">
                                                <label for="datosAgregar">Nombre/Razon Social <span style="color: red;"> (*)</span></label>
                                                <input type="text" class="form-control" id="datosAgregar"
                                                       name="razon_social">
                                            </div>


                                            <div class="col-md-6 mt-3">
                                                <label for="direccionAgregar2">Dirección de Llegada</label>
                                                <input type="text" class="form-control" id="direccionAgregar2"
                                                       name="direccionAgregar2">
                                            </div>
                                            <div class="col-md-8 mt-3">
                                                <label for="direccion">Email</label>
                                                <input required type="text" class="form-control" id="email"
                                                       name="email">
                                            </div>
                                            <div class="col-md-6 mt-3">
                                                <label for="telefonoAgregar">Teléfono</label>
                                                <input type="number" class="form-control" id="telefonoAgregar"
                                                       name="telefono">
                                            </div>
                                            <div class="col-md-6 mt-3">
                                                <label for="telefonoAgregar2">Teléfono 2</label>
                                                <input type="number" class="form-control" id="telefonoAgregar2"
                                                       name="telefonoAgregar2">
                                            </div>

                                            <div class="col-md-6 mt-3">
                                                <label for="direccionAgregar">Dirección</label>
                                                <input type="text" class="form-control" id="direccionAgregar"
                                                       name="direccion">
                                            </div>


                                        </div>

                                    </form>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cerrar</button>
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
                                                    <input type="hidden" name="proveedor_id" id="idCliente" value="">
                                                    <input type="hidden" name="trid" id="trid" value="">
                                                    <input type="text" class="form-control" id="documentoEditar"
                                                           name="ruc">
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
                                                <label for="datosAgregar">Nombre/Razon Social <span style="color: red;"> (*)</span></label>
                                                <input type="text" class="form-control" id="datosEditar"
                                                       name="razon_social">
                                            </div>


                                            <div class="col-md-12 mt-3">
                                                <label for="direccionEditar" class="col-form-label">Direccion</label>
                                                <input type="text" class="form-control" id="direccionEditar"
                                                       name="direccion">
                                            </div>
                                            <div class="col-md-6 mt-3">
                                                <label for="direccionEditar2" class="col-form-label">Direccion 2</label>
                                                <input type="text" class="form-control" id="direccionEditar2"
                                                       name="direccion2">
                                            </div>
                                            <div class="col-md-6 mt-3">
                                                <label for="emailEditar" class="col-form-label">Email</label>
                                                <input required type="text" class="form-control" id="emailEditar"
                                                       name="email">
                                            </div>
                                            <div class="col-md-6 mt-3">
                                                <label for="telefonoEditar" class="col-form-label">Telefono</label>
                                                <input type="number" class="form-control" id="telefonoEditar"
                                                       name="telefono">
                                            </div>
                                            <div class="col-md-6 mt-3">
                                                <label for="telefonoEditar2" class="col-form-label">Telefono 2</label>
                                                <input type="number" class="form-control" id="telefonoEditar2"
                                                       name="telefono2">
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
                    <div class="card-title-desc">
                        <div class="table-responsive">
                            <table id="tabla_clientes"
                                   class="table table-bordered dt-responsive nowrap text-center table-sm dataTable no-footer">
                                <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>RUC</th>
                                    <th>Razon Social</th>
                                    <th>Email</th>
                                    <th>Télefono</th>
                                    <th>Télefono2</th>
                                    <th>Direccion</th>
                                    <th>Direccion2</th>
                                    <th>Creado</th>
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


<script>
	$(document).ready(function () {

		const app = new Vue({
			el: "#conte-vue-modals",
			data: {
				listaClientes: []
			},
			methods: {
				agregarListaImport() {

					if (this.listaClientes.length > 0) {

						_ajax("/ajs/proveedores/set/group", "POST", {
								lista: JSON.stringify(this.listaClientes)
							},
							function (resp) {
								console.log(resp);
								/* return */
								if (resp.res) {
									alertExito("Agregado")
										.then(function () {
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
			ordering: false,
			searching: true,
			destroy: true,
			ajax: {
				url: _URL + "/ajs/proveedores/render",
				method: "POST", //usamos el metodo POST
				dataSrc: "",
			},
			language: {
				url: "ServerSide/Spanish.json",
			},
			columns: [{
				data: "proveedor_id",
				class: "text-center",
			},
				{
					data: "ruc",
					class: "text-left",
				},
				{
					data: "razon_social",
					class: "text-left",
				},
				{
					data: "email",
					class: "text-center",
				},
				{
					data: "telefono",
					class: "text-center",
				},
				{
					data: "telefono2",
					class: "text-center",
				},
				{
					data: "direccion",
					class: "text-center",
				},
				{
					data: "direccion2",
					class: "text-center",
				},
				{
					data: "fecha_create",
					class: "text-center",
				},
				{
					data: null,
					class: "text-center",
					render: function (data, type, row) {
						return `<div class="text-center">
                                    <div class="btn-group btn-sm">
                                        <button  data-id="${Number(row.proveedor_id)}" class="btn btn-sm btn-warning btnEditar"><i class="fa fa-edit"></i> </button>
                                        <button btn-sm  data-id="${Number(row.proveedor_id)}" class="btn btn-sm  btn-danger btnBorrar"><i class="fa fa-trash"></i> </button>
                                    </div>
                                </div>`;
					},
				},
			],
		});
		$("#nuevoCliente").click(function () {
			$("#loader-menor").show();
			let data = $("#frmClientesAgregar").serializeArray();
			$.ajax({
				type: "POST",
				url: _URL + "/ajs/proveedores/set",
				data: data,
				success: function (resp) {
					$("#loader-menor").hide();
					
					
						tabla_clientes.ajax.reload(null, false);
						Swal.fire("¡Buen trabajo!", "Registro Exitoso", "success");
						$("#agregarModal").modal("hide");
						$("body").removeClass("modal-open");
						$("#frmClientesAgregar").trigger("reset");
					
				},
			});
		});

		$("#tabla_clientes").on("click", ".btnEditar ", function (event) {
			$("#loader-menor").show();
			var table = $("#tabla_clientes").DataTable();
			var trid = $(this).closest("tr").attr("id");
			var id = $(this).data("id");
			$("#editarModal").modal("show");
			$("#editarModal")
				.find(".modal-title")
				.text("Editar Proveedor N°" + id);
			$.ajax({
				url: _URL + "/ajs/proveedores/get",
				data: {
					id: id,
				},
				type: "post",
				success: function (data) {

					$("#loader-menor").hide();
					let datos = JSON.parse(data);
					console.log(datos);
					$("#documentoEditar").val(datos.ruc);
					$("#datosEditar").val(datos.razon_social);
					$("#direccionEditar").val(datos.direccion);
					$("#direccionEditar2").val(datos.direccion2);
					$("#telefonoEditar").val(datos.telefono);
					$("#telefonoEditar2").val(datos.telefono2);
					$("#emailEditar").val(datos.email);
					$("#idCliente").val(id);
					$("#trid").val(trid);
				},
			});
		});

		$("#updateCliente").click(function () {
			$("#loader-menor").show();
			let data = $("#clientesEditar").serializeArray();
			let id = $("#idCliente").val();
			let idData = {
				name: "idPre",
				value: id,
			};
			$.ajax({
				url: _URL + "/ajs/proveedores/update",
				type: "POST",
				data: data,
				success: function (resp) {
					$("#loader-menor").hide();
					console.log(resp);
					let data = JSON.parse(resp);
					console.log(data);
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

		$("#tabla_clientes").on("click", ".btnBorrar", function () {
			var id = $(this).data("id");
			let idData = {
				id: id,
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
						url: _URL + "/ajs/proveedores/delete",
						type: "post",
						data: idData,
						success: function (resp) {
							/* console.log(resp); */
							tabla_clientes.ajax.reload(null, false);
							Swal.fire(
								"¡Buen trabajo!",
								"Registro Borrado Exitosamente",
								"success"
							);
						},
					});
				} else {
				}
			});
		});
		$("#btnBuscarInfo").click(function (e) {
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
						success: function (resp) {
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
		$("#btnBuscarInfoEditar").click(function (e) {
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
						success: function (resp) {
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
							$("#direccionEditar").val(datos.data.direccion || '');
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


		$("#nuevoExcel").change(function () {
			console.log("aaaaaaaa")
			if ($("#nuevoExcel").val().length > 0) {
				var fd = new FormData();
				fd.append('file', $("#nuevoExcel")[0].files[0]);
				$.ajax({
					type: 'POST',
					url: _URL + "/ajs/proveedores/set/exel",
					data: fd,
					contentType: false,
					cache: false,
					processData: false,
					beforeSend: function () {
						console.log('inicio');
						$("#loader-menor").show();
					},
					error: function (err) {
						$("#loader-menor").hide();
						console.log(err);
					},
					success: function (resp) {
						$("#loader-menor").hide();
						console.log(resp);
						/* return */
						resp = JSON.parse(resp)
						if (resp.res) {
							var bloc = true;
							var listaTemp = [];
							resp.data.forEach(function (el) {
								if (!bloc) {
									listaTemp.push({
										ruc: el[0],
										razon_social: el[1],
										direccion: el[2],
										direccion2: el[3],
										telefono: el[4],
										telefono2: el[5],
										email: el[6],
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

	});
</script>
