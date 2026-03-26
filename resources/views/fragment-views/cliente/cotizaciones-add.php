<script src="<?= URL::to('public/js/qrCode.min.js') ?>"></script>
<div class="page-title-box">
	<div class="row align-items-center">
		<div class="col-md-8">
			<h6 class="page-title">Pedido</h6>
			<ol class="breadcrumb m-0">
				<li class="breadcrumb-item"><a href="javascript: void(0);">Facturación</a></li>
				<li class="breadcrumb-item"><a href="/ventas" class="button-link">Pedido</a></li>
				<li class="breadcrumb-item active" aria-current="page">Productos</li>
			</ol>
		</div>
		<div class="col-md-4">
			<div class="float-end d-none d-md-block">

			</div>
		</div>
	</div>
</div>
<input type="hidden" value="<?= date("Y-m-d") ?>" id="fecha-app">
<div class="row">
	<div class="col-12">
		<div class="card">
			<div class="card-body">

				<h4 class="card-title"></h4>

				<div class="card-title-desc">
					<div class="col-lg-12 text-end">
						<button type="button" onclick="$('#btn_finalizar_pedido').click()" class="btn btn-primary">
							<i class="fa fa-plus "></i> Guardar Pedido
						</button>

						<a id="backbuttonvp" style="margin-left:25px;" href="/cotizaciones" class="btn btn-warning button-link"><i class="fa fa-arrow-left"></i> Regresar</a>
					</div>
				</div>
				<div class="row" id="container-vue">
					<div class="col-12 row">
						<div class="col-md-8">
							<div class="panel">
								<div class="panel-body">

									<div class="row">
										<div class="col-md-12">

											<form v-on:submit.prevent="addProduct" class="form-horizontal">
												<div hidden class="form-group row mb-3">
													<label class="col-lg-2 control-label">Almacén</label>
													<div class="col-lg-3">
														<select class="form-control idAlmacen" v-model='producto.almacen' @change="onChangeAlmacen($event)">
															<option value="1">Almacén 1</option>
															<option value="2">Tienda 1</option>
														</select>
													</div>
												</div>

                                                <div class="row">
                                                    <label class="col-lg-2 control-label">Cliente</label>
                                                    <div class="form-group mb-3">
                                                        <div class="col-lg-12">
                                                            <div class="input-group">
                                                                <input id="input_datos_cliente" v-model="venta.num_doc" type="text" placeholder="Ingrese Documento" class="form-control" maxlength="11">
                                                                <div class="input-group-addon btn btn-primary" @click="buscarDocumentSS" style="color: #fff;background-color: #337ab7;border-color: #2e6da4;">
                                                                    <i class="fa fa-search"></i>
                                                                </div>
                                                            </div>

                                                        </div>
                                                    </div>

                                                    <div class="form-group  mb-3">
                                                        <div class="col-lg-12">
                                                            <input v-model="venta.nom_cli" type="text" placeholder="Nombre del cliente" class="form-control ui-autocomplete-input" autocomplete="off">
                                                        </div>
                                                    </div>
                                                    <div class="form-group  mb-3">
                                                        <div class="col-lg-12">
                                                            <div class="input-group">
                                                                <input v-model="venta.dir_cli" type="text" placeholder="Direccion 1" class="form-control ui-autocomplete-input" autocomplete="off">
                                                                <div class="input-group-addon"><input v-model="venta.dir_pos" name="dirserl" value="1" type="radio" class="form-check-input"></div>
                                                            </div>

                                                        </div>
                                                    </div>
                                                    <div class="form-group  mb-3">
                                                        <label class="col-lg-2 control-label">Observacion</label>
                                                        <div class="col-lg-12">
                                                            <div class="input-group">
                                                                <input v-model="venta.observacion" type="text" placeholder="Observacion" class="form-control ui-autocomplete-input" autocomplete="off">
                                                                <div class="input-group-addon"> </div>
                                                            </div>

                                                        </div>
                                                    </div>

                                                </div>

                                                <canvas hidden="" id="qr-canvas" v-show="toggleCamara" style="width: 300px; padding: 10px;"></canvas>

                                                <div class="form-group row mb-3">
													<label class="col-lg-2 control-label" style="color:#ec4561;font-weight: bold;">Buscar</label>
													<div class="col-lg-10">
														<div class="input-group">
															<input type="text" placeholder="Consultar Productos" class="form-control ui-autocomplete-input" id="input_buscar_productos" autocomplete="off" style="background-color: #f8b425b8;">
															<div class="input-group-btn p-1">
																<label class=""> <input id="btn-scan-qr" v-model="usar_scaner" @click="toggleCamara" type="checkbox"> Usar Scanner</label><br />
																<label @click="abrirMultipleBusaque" style="color: blue;cursor: pointer">Busqueda Multiple</label>
															</div>
														</div>
													</div>
												</div>
												<div class="form-group row mb-3">
													<label class="col-lg-2 control-label">Descripción</label>
													<div class="col-lg-10">
														<input required v-model="producto.descripcion" type="text" placeholder="Descripción" class="form-control" readonly="true">
													</div>
												</div>
												<div class="form-group row  mb-3">


													<div class="col-lg-12">
														<div class="row">
															<div class="row  col-lg-3">
                                                                <label class=" control-label">Stock Actual</label>
																<div class="input-group">
																	<input disabled v-model="producto.stock" class="form-control text-center" type="text" placeholder="0">
                                                                    <span class="input-group-text" >{{producto.medida}}</span>
                                                                </div>
															</div>
															<div class="row  col-lg-6">
																<label for="example-text-input" class="  control-label">Cantidad</label>
																<div class="input-group">
																	<input @keypress="onlyNumber" required v-model="producto.cantidad" class="form-control text-center" type="text" placeholder="0" id="example-text-input">
                                                                    <select v-model="producto.presentacion" class="form-select">
                                                                        <option v-for="(item ) in listaOpcionesPResen" :value="item.cod">{{item.nom}}</option>
                                                                    </select>
                                                                    <span class="input-group-text" >De</span>
                                                                    <template  v-if="listaMedidasCnt.length>0">
                                                                        <select v-model="producto.presentacionCnt" required v-model="producto.presentacionCnt" class="form-select">
                                                                            <option v-for="itm in listaMedidasCnt">{{itm}}</option>
                                                                        </select>
                                                                    </template>
                                                                    <template v-else>
                                                                        <input  v-model="producto.presentacionCnt" required @keypress="onlyNumber" class="form-control" />
                                                                    </template>

                                                                    <span class="input-group-text" >{{producto.medida}}</span>
                                                                </div>
															</div>
															<!--<div class="row  col-lg-2">-->
															<!--	<label for="example-text-input" class=" col-form-label">Precio</label>-->
															<!--	<div class="input-group">-->
															<!--		<select name="" id="" class="form-control" v-model="producto.precio">-->
															<!--			<option v-for="(value, key) in precioProductos" :value="value.precio" :key="key">{{ value.precio }}</option>-->
															<!--		</select>-->
															<!--	</div>-->
															<!--</div>-->
															<div class="col">
                                                                <label  class="  control-label" style="color: white">.</label><br>
																<button id="submit-a-product" type="submit" class="btn btn-success"><i class="fa fa-check"></i> Agregar
															</div>
														</div>
													</div>


												</div>


											</form>
										</div>

										<div class="col-md-12 mt-5" style="overflow-x: auto;">
											<div class="row">
												<div class="text-left col-md-9">
													<h4>Producto</h4>
												</div>
												<!--<div class="col-md-3" v-if="productos.length > 0">-->
												<!--	<label for="">Usar</label>-->
												<!--	<select name="" id="" class="form-control text-right" v-model="usar_precio" @change="cambiarPrecio($event)">-->
												<!--		<option value="1">Precio</option>-->
												<!--		<option value="2">Credito 1</option>-->
												<!--		<option value="3">Credito 2</option>-->
												<!--		<option value="4">Precio x Saco</option>-->
												<!--		<option value="5">Precio x Mayor</option>-->
												<!--	</select>-->
												<!--</div>-->
											</div>
											<table class="table" style="width: 100%; ">
												<thead>
													<tr>
														<th>Item</th>
														<th> </th>
														<th>Producto</th>
														<th>Cantidad</th>
														<th>P. Unit.</th>
														<th>T. precio.</th>
														<th>Parcial</th>
														<th></th>
													</tr>
												</thead>
												<tbody>
													<tr v-for="(item,index) in productos">
														<td>{{index+1}}</td>
														<td colspan="2">{{item.descripcion}}</td>
														<td> <span v-if="!item.editable">{{item.cantidad}} {{nombreMedida(item.presentacion)}} / {{item.presentacionCnt}} {{item.medida}}</span>
                                                            <template  v-if="item.editable">
                                                                <div class="input-group">
                                                                    <input class="form-control" v-model="item.cantidad">
                                                                    <span class="input-group-text" id="basic-addon1">{{nombreMedida(item.presentacion)}} / {{item.presentacionCnt}}{{item.medida}}</span>

                                                                </div>
                                                            </template>
                                                        </td>
														<td>
                                                            <span  v-if="!item.editable">{{item.precioSelPre}}</span>
                                                            <input @keypress="onlyNumber"   v-model="item.precioSelPre" v-if="item.editable"  @change="cambioPrecioProd(item)" />
                                                            <!--<select v-model="item.precioSelPre" v-if="item.editable"  @change="cambioPrecioProd(item)">
                                                                <option v-for="prs in item.precioProductos" :value="prs.precio">{{prs.precio}}</option>
                                                            </select>-->
                                                        </td>
              <!--                                          <td>-->
														<!--	<select-->
														<!--		name="precio"-->
														<!--		class="form-control"-->
														<!--		v-model="item.precio_usado"-->
														<!--		@change="cambiarPrecioFila(item)">-->
														<!--		<option value="1">Precio 1</option>-->
														<!--		<option value="2">Precio 2</option>-->
														<!--		<option value="3">Precio 3</option>-->
														<!--		<option value="4">Precio Club</option>-->
														<!--		<option value="5">Precio Unidad</option>-->
														<!--	</select>-->
														<!--</td>-->
														<td>
															<select
																name="precio"
																class="form-control"
																v-model.number="item.precio_usado"
																@change="cambiarPrecioFila(item)">
																<option v-for="(value, key) in item.precioProductos" :value="key + 1" :key="key">
																	{{ value.precio }}
																</option>
															</select>
														</td>

														<td>{{formatoDecimal(item.precioVenta*item.cantidad)}}</td>
														<td>{{item.serie}}</td>
														<td><button @click="eliminarItemPro(index)" type="button" class="btn btn-danger btn-sm">
																<i class="fa fa-times"></i>
															</button>
															<button @click="cambiarEdiProd(item)" v-if="!item.editable"  class="btn btn-info btn-sm"><i class="fa fa-edit"></i></button>
															<button @click="cambiarEdiProd(item)" v-if="item.editable"   class="btn btn-warning btn-sm"><i class="fa fa-save"></i></button>
														</td>
													</tr>
												</tbody>
											</table>
										</div>

									</div>

								</div>
							</div>

						</div>
						<div class="col-md-4">
							<div class="card ">
								<div class="card-body">
									<div class="col-md-12">
										<div class="widget padding-0 white-bg">
											<div class="padding-20 text-center">
												<form v-on:submit.prevent role="form" class="form-horizontal">
													<div class="row">
														<div class="col-md-6 form-group">
															<label class="control-label">Documento</label>
															<div class="col-md-12">
																<select @change="onChangeTiDoc($event)" v-model="venta.tipo_doc" class="form-control">
																	<option value="1">BOLETA DE VENTA</option>
																	<option value="2">FACTURA</option>
                                                                    <option value="6">NOTA DE VENTA</option>
																</select>
															</div>
														</div>
														<div class="col-md-6 form-group">
															<label class="control-label">Tipo Pago</label>
															<select disabled v-model="venta.tipo_pago" @change="changeTipoPago" class="form-control">
																<option value="1">Contado</option>
																<option value="2">Crédito</option>

															</select>
														</div>
													</div>
													<div style="display: none" class="form-group">
														<div class="col-lg-12 row">
															<div class="col-lg-6">
																<label class="text-center col-md-12">Serie</label>
																<input v-model="venta.serie" type="text" class="form-control text-center" readonly="">
															</div>
															<div class="col-lg-6">
																<label class="text-center col-md-12">Número</label>
																<input v-model="venta.numero" type="text" class="form-control text-center" readonly="">
															</div>
														</div>
													</div>
													<div class="form-group  mb-3">
														<label style="display: none" class="col-lg-12 text-center">Fecha</label>
														<div class="col-lg-12">
															<div class="row">
																<div class="col-md-6">
																	<div class="form-group ">
																		<label class="control-label">Fecha</label>
																		<div class="col-lg-12">
																			<input v-model="venta.fecha" type="date" placeholder="dd/mm/aaaa" name="input_fecha" class="form-control text-center" value="2021-10-16">
																		</div>
																	</div>
																</div>
																<div  style="display: none" class="col-md-6">
																	<div class="form-group ">
																		<label class="control-label">Vencimiento</label>
																		<div class="col-lg-12">
																			<input disabled v-model="venta.fechaVen" type="date" placeholder="dd/mm/aaaa" name="input_fecha" class="form-control text-center" value="2021-10-16">
																		</div>
																	</div>
																</div>
															</div>
														</div>
													</div>
													<div hidden  v-if="venta.tipo_pago=='2'" class="form-group ">
														<label class="control-label">Días de pago</label>
														<div class="col-lg-12">
															<input @focus="focusDiasPagos" v-model="venta.dias_pago" type="text" class="form-control text-center">
														</div>
													</div>

													<div class="form-group  mb-3">
														<label class="col-lg-4 control-label"> </label>
														<div class="col-lg-12">
															<div class="row">
																<div class="col-md-6">
																	<div class="form-group ">
																		<label class="control-label">Moneda</label>
																		<div class="col-lg-12">
																			<select disabled v-model="venta.moneda" class="form-control">
																				<option value="1">SOLES</option>
																				<option value="2">DOLARES</option>
																			</select>
																		</div>
																	</div>
																</div>
																<div hidden class="col-md-6">
																	<div class="form-group ">
																		<label class="control-label">Tasa de cambio</label>
																		<div class="col-lg-12">
																			<input v-model="venta.tc" type="text">
																		</div>
																	</div>
																</div>
															</div>
														</div>
													</div>

													<div class="form-group">
														<label class="col-lg-12 text-center"></label>
													</div>



													<div hidden class="form-group  mb-3">
														<div class="col-lg-12">

															<div class="input-group">
																<input v-model="venta.dir2_cli" type="text" placeholder="Direccion 2" class="form-control ui-autocomplete-input" autocomplete="off">
																<div class="input-group-addon">
																	<input :disabled="!isDirreccionCont" v-model="venta.dir_pos" name="dirserl" value="2" type="radio" class="form-check-input">
																</div>
															</div>

														</div>
													</div>
													<div class="form-group  mb-3">
														<div class="col-lg-12">
															<button style="display: none" @click="guardarVenta" type="button" class="btn btn-lg btn-primary" id="btn_finalizar_pedido">
																<i class="fa fa-save"></i> Guardar
															</button>
														</div>
													</div>
												</form>
											</div>
											<div class="bg-primary pv-15 text-center  p-3" style="height: 90px; color: white">

												<h1 class="mv-0 font-400" id="lbl_suma_pedido">{{monedaSibol}} {{(totalProdustos/(venta.tc||1)).toFixed(2)}}</h1>
												<div class="text-uppercase">Suma Pedido</div>
											</div>
										</div>
									</div>
								</div>
							</div>


						</div>
					</div>


					<div class="modal fade" id="modal-dias-pagos" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
						<div class="modal-dialog modal-dialog-centered">
							<div class="modal-content">
								<div class="modal-header">
									<h3 class="modal-title" id="exampleModalLabel">Días de Pagos</h3>
								</div>
								<div class="modal-body">
									<div class="row mb-3">
										<div class="col-md-6">
											<div class="">
												<label class="form-label">Fecha Emisión</label>
												<input v-model="venta.fecha" disabled type="date" class="form-control">
											</div>
										</div>
										<div class="col-md-6">
											<div class="">
												<label class="form-label">Monto Total Venta</label>
												<input :value="'S/ '+venta.total" disabled type="text" class="form-control">
											</div>
										</div>
									</div>
									<div class="mb-3">
										<label class="form-label">Días de pagos</label>
										<input placeholder="10,20,30,........" v-model="venta.dias_pago" @keypress="onlyNumberComas" type="text" class="form-control">
										<div class="form-text">Separar por comas los días de pagos</div>
									</div>
									<div class="row">
										<div class="col-md-12">
											<table class="text-center table-sm table table-bordered">
												<thead>
													<tr>
														<th>#</th>
														<th>Fecha</th>
														<th>Monto</th>
													</tr>
												</thead>
												<tbody>
													<tr v-for="(item,index) in venta.dias_lista">
														<td></td>
														<td>{{visualFechaSee(item.fecha)}}</td>
														<td>S/ {{formatoDecimal(item.monto)}}</td>
													</tr>
												</tbody>
												<tfoot>
													<tr>
														<th colspan="2">Total</th>
														<th>{{totalValorListaDias}}</th>
													</tr>
												</tfoot>
											</table>
										</div>
									</div>
								</div>
								<div class="modal-footer">
									<button type="button" class="btn btn-danger" data-dismiss="modal">Cerrar</button>
								</div>
							</div>
						</div>
					</div>


					<div class="modal fade" id="modalSelMultiProd" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
						<div class="modal-dialog modal-dialog-centered  modal-lg">
							<div class="modal-content">
								<div class="modal-header">
									<h5 class="modal-title" id="exampleModalLabel">Busqueda Multiple</h5>
									<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
								</div>
								<div class="modal-body">
									<div v-if="pointSel==1">
										<div class="mb-3">
											<label class="form-label">Buscar Producto</label>
											<input v-model="dataKey" @keyup="busquedaKeyPess" type="text" class="form-control">
										</div>

										<div class="list-group" style=" height: 300px; overflow-y: scroll;">
											<label v-for="item in listaTempProd" class="list-group-item list-group-item-action"><input v-model="itemsLista" :value="item" type="checkbox"> {{item.value}}</label>
										</div>
										<div v-if="itemsLista.length>0" style="width: 100%" class="text-end">
											<button @click="pasar2Poiter" class="btn btn-primary">Continuar</button>
										</div>
									</div>
									<div v-if="pointSel==2">
										<table class="table table-sm table-bordered">
											<thead>
												<tr>
													<td>Producto</td>
													<td>Stock</td>
													<td>Cantidad</td>
													<td>Precio</td>
												</tr>
											</thead>
											<tbody>
												<tr v-for="item in itemsLista">
													<th>{{item.codigo_pp}} | {{item.descripcion}}</th>
													<th>{{item.cnt}}</th>
													<th><input style="width: 80px;" v-model="item.cantidad" /></th>
													<th>
														<select style="width: 80px;" class="form-control" v-model="item.precio_unidad">
															<option v-for="(value, key) in item.precioProductos" :value="value.precio" :key="key">{{ value.precio }}</option>
														</select>
													</th>
												</tr>
											</tbody>
										</table>
										<div v-if="itemsLista.length>0" style="width: 100%" class="text-end">
											<button @click="pointSel=1" class="btn btn-warning">Regresar</button>
											<button @click="agregarProducto2Ps" class="btn btn-primary">Agregar</button>
										</div>
									</div>
								</div>
								<div class="modal-footer">
									<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
								</div>
							</div>
						</div>
					</div>

				</div>

			</div>
		</div>
	</div>
</div>


<script>
function verificarEstadoSesion(callback) {
		return fetch(_URL + "/ajs/usuarios/verificarEstadoSesion", {
				method: "POST",
			})
			.then(response => response.json())
			.then(data => {
				console.log(data)
				// Si la respuesta es false, significa que el usuario está desactivado
				if (!data.success) {
					Swal.fire({
						icon: 'error',
						title: 'Error',
						text: data.message,
						confirmButtonText: 'OK'
					}).then(() => {
						document.getElementById('logout').click();
					});
					// Mostramos el mensaje de error

				} else {
					callback();
				}
			})
	}
	$(document).ready(function() {
	    verificarEstadoSesion(function() {
			console.log("usuario activo")
		});
		const app = new Vue({
			el: "#container-vue",
			data: {
                listaMedida:[{cod:1,nom:'Unidad'},{cod:2,nom:'Caja'},{cod:3,nom:'Bolsa'},{cod:4,nom:'Saco'},],
				usar_scaner: false,
                listaMedidasCnt:[],
				producto: {
                    precioSelOption:'',
                    presentacionTmepPO:[],
					editable: false,
					productoid: "",
					descripcion: "",
                    precioProductos: [],
					nom_prod: "",
					cantidad: "1",
					precioSelPre: 0,
					stock: "",
                    medida: "",
					codigo: "",
					codigo_pp: "",
					costo: "",
					codsunat: "",
					precio: '1',
					almacen: '<?php echo $_SESSION["sucursal"] ?>',
					precio2: '',
					precio3: '',
					precio4: '',
					precio_unidad: '',
					precioVenta: '',
					precio_usado: 1,
                    presentacion:'1',
                    presentacionCnt:'1',
				},
				productos: [],
				precioProductos: [],
				usar_precio: '5',
				venta: {
					dir_pos: 1,
					tipo_doc: '6',
					serie: '',
					numero: '',
					tipo_pago: '2',
					dias_pago: '',
					fecha: $("#fecha-app").val(),
					fechaVen: $("#fecha-app").val(),
					sendwp: false,
					numwp: "",
					num_doc: "",
					nom_cli: "",
					dir_cli: "",
					dir2_cli: "",
					tipoventa: 1,
					total: 0,
					dias_lista: [],
					moneda: '1',
                    observacion: '',


				},
				adicional:{
					cajero: '',
					vendedor: '',
					ruta: ''
				},
				dataKey: '',
				listaTempProd: [],
				itemsLista: [],
				pointSel: 1
			},
			watch: {
				'venta.dias_pago'(newValue) {
					const listD = (newValue + "").split(",");
					this.dias_lista = [];
					if (listD.length > 0) {

						var listaTemp = listD.filter(ite => ite.length > 0)
						const palorInicial = (parseFloat(this.venta.total + "") / listaTemp.length).toFixed(0)
						var totalValos = parseFloat(this.venta.total + "");
						listaTemp = listaTemp.map((num, index) => {
							var fecha_ = new Date(this.venta.fecha)
							const dias_ = parseInt(num + "")
							fecha_.setDate(fecha_.getDate() + dias_);
							var value = 0;
							if (index + 1 == listaTemp.length) {
								value = totalValos;
								this.venta.fechaVen = this.formatDate(fecha_)
							} else {
								value = palorInicial;
								totalValos -= palorInicial;
							}
							return {
								fecha: this.formatDate(fecha_),
								monto: value
							}
						});
						//console.log(palorInicial+"<<<<<<<<<<<<<")
						this.venta.dias_lista = listaTemp
						//console.log(listaTemp);
					}

				}
			},
			methods: {
                cambioPrecioProd(prod){
                    console.log(prod);
                    prod.precioVenta = prod.precioSelPre *( prod.presentacionCnt)
                },
                cambiarEdiProd(prod){
                    if (prod.editable){
                        console.log("aaaaaaaaaaaaaaaaaaaa")
                        //prod.precioVenta = prod.precioVenta * prod.presentacionCnt
                    }else{
                        //prod.precioVenta = prod.precioVenta /prod.presentacionCnt
                    }
                    prod.editable=!prod.editable
                },

                nombreMedida(cod){
                    return this.listaMedida.find(item => item.cod==cod)?.nom
                },
				toggleCamara() {
					if (!app.usar_scaner) {
						app.encenderCamara();
					} else {
						app.cerrarCamara();
					}
				},
				encenderCamara() {
					navigator.mediaDevices
						.getUserMedia({
							video: {
								facingMode: "environment"
							}
						})
						.then(function(stream) {
							app.scanning = true; // Actualiza el estado de escaneo
							// Configuración de la cámara y la lógica de escaneo



							const video = document.createElement("video");
							const canvasElement = document.getElementById("qr-canvas");
							const canvas = canvasElement.getContext("2d");
							const btnScanQR = document.getElementById("btn-scan-qr");
							btnScanQR.checked = true;
							video.setAttribute("playsinline", true); // required to tell iOS safari we don't want fullscreen
							video.srcObject = stream;
							video.play();

							function tick() {
								canvasElement.height = video.videoHeight;
								canvasElement.width = video.videoWidth;
								canvas.drawImage(video, 0, 0, canvasElement.width, canvasElement.height);

								app.scanning && requestAnimationFrame(tick);
							}

							function scan() {
								try {
									qrcode.decode();
								} catch (e) {
									setTimeout(scan, 500);
								}
							}

							video.addEventListener("loadeddata", function() {
								canvasElement.hidden = false;

								tick();
								scan();
							});

							qrcode.callback = (respuesta) => {
								$("#input_buscar_productos").val(respuesta);
								if (respuesta) {
									$.ajax({
										type: "post",
										url: _URL + '/ajas/compra/buscar/producto',
										data: {
											producto: respuesta // Código escaneado
										},
										success: function(response) {
											//console.log(response);
											let data = JSON.parse(response);
											console.log(data);
											// // Manejar la respuesta del servidor
											if (data.res == true) {
												//alert("es verdadero el producto");

												let id = data.data[0].id_producto;
												let codigo_app = data.data[0].codigo;
												let codsunat = data.data[0].codsunat;
												let costo = data.data[0].costo;
												// let descripcion = data.data[0].descripcion;
												let nom_prod = data.data[0].descripcion;

												// let idempresa = data.data[0].empresa;
												let precio = data.data[0].precio;
												let precio2 = data.data[0].precio2;
												let precio3 = data.data[0].precio3;
												let precio4 = data.data[0].precio4;
												let precio_unidad = data.data[0].precio_unidad;

												Swal.fire({
													title: 'Se agrego correctamente',
													text: respuesta,
													icon: 'success',
													confirmButtonText: 'Cerrar'
												});
												app.addProductQR(id, codigo_app, codsunat, costo, nom_prod, precio, precio2, precio3, precio4, precio_unidad);
												$("#input_buscar_productos").val('');
												app.usar_scaner = false;
												app.cerrarCamara();
											} else {
												// alert("el producto no existe");
												$("#input_buscar_productos").val('');
												// Producto no encontrado
												Swal.fire({
													icon: 'warning',
													title: 'Advertencia',
													text: 'No se encontró ningun producto',
													confirmButtonText: 'Cerrar'
												});
												app.usar_scaner = false;
												app.cerrarCamara();
											}
										},
										error: function() {
											// Manejar errores de AJAX
											alert('Error al buscar el producto.');
										}
									});


									// // Swal.fire({
									// //     title: 'Se agrego correctamente',
									// //     text: respuesta,
									// //     icon: 'success',
									// //     confirmButtonText: 'Cerrar'
									// }).then(() => {
									//     app.encenderCamara(); // Detiene la cámara después de escanear
								}

							};
						});
				},
				cerrarCamara() {
					// Lógica para apagar la cámara
					//this.camaraEncendida = false;
					app.usar_scaner = false; // Actualiza el estado de escaneo
					const video = document.querySelector("video");
					const canvasElement = document.getElementById("qr-canvas");
					const canvas = canvasElement.getContext("2d");


					if (video && video.srcObject) {
						video.srcObject.getTracks().forEach((track) => {
							track.stop();
						});
					}
					document.getElementById("btn-scan-qr").checked = false;
					canvasElement.hidden = true;
				},
				agregarProducto2Ps() {
					this.pointSel = 1
					this.productos = this.productos.concat(this.itemsLista.map(e => {
						e.precioVenta = e.precio_unidad
						e.edicion = false
						return {
							...e,
							precioVenta: e.precio_unidad,
							edicion: false,
							productoid: e.codigo
						}
					}))
					this.itemsLista = []
					this.listaTempProd = []
					this.dataKey = ''
					$("#modalSelMultiProd").modal('hide')
				},
				pasar2Poiter() {
					this.itemsLista = this.itemsLista.map(e => {
						e.cantidad = '1'
						let array = [{
								precio: e.precio
							},
							{
								precio: e.precio2
							},
							{
								precio: e.precio3
							},
							{
								precio: e.precio4
							},
							{
								precio: e.precio_unidad
							}
						]
						e.precio_unidad = array[array.length - 1].precio || 0
						e.precioProductos = array
						return e
					})
					this.pointSel = 2
				},
				busquedaKeyPess(evt) {
					const vue = this
					vue.listaTempProd = []
					if (this.dataKey.length > 0) {
						_get("/ajs/cargar/productos/<?php echo $_SESSION["sucursal"] ?>?term=" + this.dataKey, (result) => {
							console.log(result)
							vue.listaTempProd = result
						})
					}

				},
				abrirMultipleBusaque() {
					$("#modalSelMultiProd").modal('show')
				},
				// cambiarPrecio(event) {
				// 	console.log(event.target.value)

				// 	var self = this

    //                 this.productos.forEach(element => {
    //                     if (event.target.value == 1) {
    //                         element.precioSelPre =element.precio
    //                         element.precioSelPre= parseFloat(element.precioSelPre)
    //                         element.precioVenta = parseFloat((element.precio*(element.presentacionCnt)) + "").toFixed(2)
    //                         /*  ui.item.precio == null ? parseFloat(0 + "").toFixed(2) : parseFloat(ui.item.precio + "").toFixed(2) */
    //                         element.precio_usado = '1'
    //                     } else if (event.target.value == 2) {
    //                         element.precioSelPre =element.precio2
    //                         element.precioSelPre= parseFloat(element.precioSelPre)
    //                         element.precioVenta = parseFloat((element.precio2*(element.presentacionCnt)) + "").toFixed(2)
    //                         element.precio_usado = '2'
    //                     } else if (event.target.value == 3) {
    //                         element.precioSelPre =element.precio3
    //                         element.precioSelPre= parseFloat(element.precioSelPre)
    //                         element.precioVenta = parseFloat((element.precio3*(element.presentacionCnt)) + "").toFixed(2)
    //                         element.precio_usado = '3'

    //                     } else if (event.target.value == 4) {
    //                         element.precioSelPre =element.precio4
    //                         element.precioSelPre= parseFloat(element.precioSelPre)
    //                         element.precioVenta = parseFloat((element.precio4*(element.presentacionCnt)) + "").toFixed(2)
    //                         element.precio_usado = '4'
    //                     } else {
    //                         element.precioSelPre =element.precio_unidad
    //                         element.precioSelPre= parseFloat(element.precioSelPre)
    //                         element.precioVenta = parseFloat((element.precio_unidad*(element.presentacionCnt)) + "").toFixed(2)
    //                         element.precio_usado = '5'
    //                     }

    //                 });
				// },
				// cambiarPrecioFila(item) {
				// 	if (item.precio_usado === "1") {
				// 		item.precioSelPre = parseFloat(item.precio);
				// 		item.precioVenta = parseFloat(item.precio * item.presentacionCnt).toFixed(2);
				// 	} else if (item.precio_usado === "2") {
				// 		item.precioSelPre = parseFloat(item.precio2);
				// 		item.precioVenta = parseFloat(item.precio2 * item.presentacionCnt).toFixed(2);
				// 	} else if (item.precio_usado === "3") {
				// 		item.precioSelPre = parseFloat(item.precio3);
				// 		item.precioVenta = parseFloat(item.precio3 * item.presentacionCnt).toFixed(2);
				// 	} else if (item.precio_usado === "4") {
				// 		item.precioSelPre = parseFloat(item.precio4);
				// 		item.precioVenta = parseFloat(item.precio4 * item.presentacionCnt).toFixed(2);
				// 	} else if (item.precio_usado === "5") {
				// 		item.precioSelPre = parseFloat(item.precio_unidad);
				// 		item.precioVenta = parseFloat(item.precio_unidad * item.presentacionCnt).toFixed(2);
				// 	}
				// },
				cambiarPrecioFila(item) {
					// Busca el precio correspondiente en la lista
					const precioSeleccionado = item.precioProductos[item.precio_usado - 1]?.precio || 0;

					item.precioSelPre = parseFloat(precioSeleccionado);
					item.precioVenta = (precioSeleccionado * (item.presentacionCnt || 1)).toFixed(2);
				},
				onChangeAlmacen(event) {
					console.log(event.target.value)
					this.producto.almacen = event.target.value
					var self = this
					$("#input_buscar_productos").autocomplete({

						source: _URL + `/ajs/cargar/productos/${self.producto.almacen}`,
						minLength: 1,
						select: function(event, ui) {
							event.preventDefault();
							/*    console.log(item);
							   console.log(ui); */
							console.log(ui.item);
							/*   return */
							console.log('aqui');
                            app.listaMedidasCnt=[]
                            if (ui.item.cnt_presenta!=null&&ui.item.cnt_presenta!=''){
                                app.listaMedidasCnt =ui.item.cnt_presenta.split(',')
                            }

                            app.producto.productoid = ui.item.codigo
							app.producto.codigo_pp = ui.item.codigo_pp
							app.producto.descripcion = ui.item.codigo + " | " + ui.item.descripcion
							app.producto.nom_prod = ui.item.descripcion
							app.producto.medida = ui.item.medida
							app.producto.precioSelOption = 0
							app.producto.precioSelPre = 0
							app.producto.cantidad = '1'
							app.producto.stock = ui.item.cnt
                            app.producto.presentacionTmepPO = ui.item.presentaciones?ui.item.presentaciones.split(","):[];
							app.producto.precio = ui.item.precio == null ? parseFloat(0 + "").toFixed(4) : parseFloat(ui.item.precio + "").toFixed(4)
							app.producto.precio2 = ui.item.precio2 == null ? parseFloat(0 + "").toFixed(4) : parseFloat(ui.item.precio2 + "").toFixed(4)
							app.producto.precio3 = ui.item.precio3 == null ? parseFloat(0 + "").toFixed(4) : parseFloat(ui.item.precio3 + "").toFixed(4)
							app.producto.precio4 = ui.item.precio4 == null ? parseFloat(0 + "").toFixed(4) : parseFloat(ui.item.precio4 + "").toFixed(4)
							app.producto.precio_unidad = ui.item.precio_unidad == null ? parseFloat(0 + "").toFixed(4) : parseFloat(ui.item.precio_unidad + "").toFixed(4)
							app.producto.codigo = ui.item.codigo
							app.producto.codigo_prod = ui.item.codigo_pp
							app.producto.costo = ui.item.costo
							app.producto.precioVenta = parseFloat(ui.item.precio_unidad == null ? parseFloat(0 + "").toFixed(4) : parseFloat(ui.item.precio_unidad + "").toFixed(4))
                            let array = [{
                                precio: parseFloat(app.producto.precio)
                            },
                                {
                                    precio: parseFloat(app.producto.precio2)
                                },
                                {
                                    precio: parseFloat(app.producto.precio2)
                                },
                                {
                                    precio: parseFloat(app.producto.precio4)
                                },
                                {
                                    precio: parseFloat(app.producto.precio_unidad)
                                }
                            ]
                            app.producto.precioProductos =array
                             app.precioProductos = array
							console.log(array);
							$('#input_buscar_productos').val("");
							$("#example-text-input").focus()
						}
					});
				},
				formatoDecimal(num, desc = 2) {
					return parseFloat(num + "").toFixed(desc);
				},
				visualFechaSee(fecha) {
					return formatFechaVisual(fecha);
				},
				formatDate(date) {
					console.log(date);
					var d = date,
						month = '' + (d.getMonth() + 1),
						day = '' + (d.getDate() + 1),
						year = d.getFullYear();

					if (month.length < 2)
						month = '0' + month;
					if (day.length < 2)
						day = '0' + day;

					return [year, month, day].join('-');
				},
				onlyNumberComas($event) {
					//console.log($event.keyCode); //keyCodes value
					let keyCode = ($event.keyCode ? $event.keyCode : $event.which);
					if ((keyCode < 48 || keyCode > 57) && keyCode !== 44) { // 46 is dot
						$event.preventDefault();
					}
				},
				focusDiasPagos() {
					//console.log("1000000000000000000")
					$("#modal-dias-pagos").modal("show")
				},
				changeTipoPago(event) {
					console.log(event.target.value)
					this.venta.fechaVen = this.venta.fecha;
					this.venta.dias_lista = []
					this.venta.dias_pago = ''
				},
				onlyNumber($event) {
					//console.log($event.keyCode); //keyCodes value
					let keyCode = ($event.keyCode ? $event.keyCode : $event.which);
					if ((keyCode < 48 || keyCode > 57) && keyCode !== 46) { // 46 is dot
						$event.preventDefault();
					}
				},
				eliminarItemPro(index) {
					this.productos.splice(index, 1)
				},
				buscarDocumentSS() {
					if (this.venta.num_doc.length == 8 || this.venta.num_doc.length == 11) {
						$("#loader-menor").show()
						this.venta.dir_pos = 1
						_ajax("/ajs/consulta/doc/cliente", "POST", {
								doc: this.venta.num_doc
							},
							function(resp) {
								$("#loader-menor").hide()
								console.log(resp);
								if (resp.res) {
									app._data.venta.nom_cli = (resp.data.nombre ? resp.data.nombre : '') + (resp.data.razon_social ? resp.data.razon_social : '')
									app._data.venta.dir_cli = resp.data.direccion
								} else {
									alertAdvertencia("Documento no enocntrado")
								}
							}
						)
					} else {
						alertAdvertencia("Documento, DNI es 8 digitos y RUC 11 digitos")
					}
				},
				guardarVenta() {
				        if (this.productos.length > 0) {

							var continuar = true;
							var mensaje = '';



							if (this.venta.tipo_doc == '1') {
								if (this.venta.num_doc.length == 11) {
									continuar = false;
									mensaje = 'No puede emitir Boleta usando RUC';
								}
								if (this.venta.tipo_pago == 2) {
									if (this.venta.dias_lista.length == 0) {
										this.venta.dias_lista.push({
											fecha: this.venta.fecha,
											monto: this.venta.total
										})
									}
								}
							} else if (this.venta.tipo_doc == '2') {
								mensaje = 'Solo se puede emitir Factura usando RUC';
								if (this.venta.num_doc.length != 11) {
									continuar = false;
								}
								if (this.venta.tipo_pago == 2) {
									if (this.venta.dias_lista.length == 0) {
										this.venta.dias_lista.push({
											fecha: this.venta.fecha,
											monto: this.venta.total
										})


										//continuar = false;
										//mensaje = 'Debe especificar los días de pagos para un cotizacion a crédito';
									}
								}


							}

							if (continuar) {
								//alertInfo("Proceso en construcción")
								const data = {
									...this.venta,
									usar_precio: this.usar_precio,
									listaPro: JSON.stringify(this.productos)
								}
								data.dias_lista = JSON.stringify(data.dias_lista)
								console.log(data);
								$("#loader-menor").show();
								_ajax("/ajs/cotizaciones/add", "POST",
									data,
									function(resp) {
										console.log(resp);
										if (resp.res) {
											alertExito("Exito", "Cotizacion Guardada")
												.then(function() {
													$("#backbuttonvp").click();
												})
										} else {
											alertAdvertencia("No se pudo Guardar la Cotizacion")
										}
									}
								)
							} else {
								alertAdvertencia(mensaje)
							}
						} else {
							alertAdvertencia("No hay productos agregados a la lista ")
						}

				},
				buscarSNdoc() {
					_ajax("/ajs/consulta/sn", "POST", {
							doc: this.venta.tipo_doc
						},
						function(resp) {
							app.venta.serie = resp.serie
							app.venta.numero = resp.numero
						}
					)
				},
				onChangeTiDoc(event) {
					this.buscarSNdoc();
				},
				limpiasDatos() {
                    this.listaMedidasCnt=[]
					this.producto = {
                        presentacionTmepPO:[],
                        editable: false,
                        productoid: "",
                        precioSelOption: 0,
                        precioSelPre: 0,
                        descripcion: "",
                        nom_prod: "",
                        cantidad: "1",
                        stock: "",
                        medida: "",
                        codigo: "",
                        codigo_pp: "",
                        costo: "",
                        codsunat: "",
                        precio: '1',
                        almacen: '<?php echo $_SESSION["sucursal"] ?>',
                        precio2: '',
                        precio3: '',
                        precio4: '',
                        precio_unidad: '',
                        precioVenta: '',
                        precio_usado: 1,
                        presentacion:'1',
                        presentacionCnt:'1',
					}
				},
				addProductQR(id, codigo_app, codsunat, costo, nom_prod, precio, precio2, precio3, precio4, precio_unidad) {
					//if (this.producto.stock)
					let cantidad = 1;

					if (codigo_app.length > 0) {
						const exisProduct = this.productos.findIndex(prod => prod.codigo === codigo_app);
						if (exisProduct !== -1) {
							this.productos[exisProduct].cantidad += cantidad;
							this.productos[exisProduct].precio = parseFloat(precio).toFixed(2);
						} else {
							const prod = {
								...this.producto
							}
							prod.productoid = id;
							prod.descripcion = codigo_app + "|" + nom_prod;
							prod.nom_prod = nom_prod;
							prod.cantidad = cantidad;
							prod.codigo = codigo_app;
							prod.costo = costo;
							prod.codsunat = codsunat;
							prod.precio = parseFloat(precio).toFixed(2);
							prod.precio2 = parseFloat(precio2).toFixed(2);
							prod.precio3 = parseFloat(precio3).toFixed(2);
							prod.precio4 = parseFloat(precio4).toFixed(2);
							prod.precio_unidad = parseFloat(precio_unidad).toFixed(2);
							prod.precioVenta = parseFloat(precio).toFixed(2);
							this.productos.push(prod);
							//this.limpiasDatos();
							console.log("QR", prod);
						}
					} else {
						alert("No se pudo guardar los datos");
					}
				},
				addProduct() {
					//if (this.producto.stock)

					if (this.producto.descripcion.length > 0) {
						const prod = {
							...this.producto
						}
                        prod.precioSelPre = parseFloat(prod?.precioVenta)
                        prod.precioVenta = prod.precioVenta *( prod.presentacionCnt)
                        console.log(">>>_",prod)
                        // prod.medida = this.nombreMedida(prod.presentacion)
						this.productos.push(prod)
						this.limpiasDatos();
					} else {
						alertAdvertencia("Busque un producto primero")
							.then(function() {
								setTimeout(function() {
									$("#input_buscar_productos").focus();
								}, 500)
							})
					}

				}
			},
			computed: {
                listaOpcionesPResen(){
                    const vue = this
                    if (this.producto.presentacionTmepPO.length > 0) {
                       return this.listaMedida.filter(item=>{
                           return vue.producto.presentacionTmepPO.find(item2=>item2 == item.cod)
                       })
                    }else{
                        return this.listaMedida
                    }
                },
				monedaSibol() {
					return (this.venta.moneda == 1 ? 'S/' : '$')
				},

				totalValorListaDias() {
					var total_ = 0;
					this.venta.dias_lista.forEach((el) => {
						total_ += parseFloat(el.monto + "")
					})
					return "S/ " + total_.toFixed(4);
				},
				isDirreccionCont() {
					return this.venta.dir2_cli.length > 0;
				},
				totalProdustos() {
					var total = 0;
					this.productos.forEach(function(prod) {
						total += prod.precioVenta * prod.cantidad
					})
					this.venta.total = total;
					return total.toFixed(4);
				}
			}
		});
		app.buscarSNdoc();
		$("#input_datos_cliente").autocomplete({
			source: _URL + "/ajs/buscar/cliente/datos",
			minLength: 2,
			select: function(event, ui) {
				event.preventDefault();
				console.log(ui.item);
				app._data.venta.dir_pos = 1
				app._data.venta.nom_cli = ui.item.datos
				app._data.venta.num_doc = ui.item.documento
				app._data.venta.dir_cli = ui.item.direccion
				/*$('#input_datos_cliente').val(ui.item.datos);
				$('#input_documento_cliente').val(ui.item.documento);
				$('#input_datos_cliente').focus();*/
			}
		});
		$("#input_buscar_productos").autocomplete({

			source: _URL + `/ajs/cargar/productos/${app.producto.almacen}`,
			minLength: 1,
			select: function(event, ui) {
				event.preventDefault();
				/*    console.log(item);
				   console.log(ui); */
				console.log(ui.item);
				/*  return */
				console.log('entra aca');
                app.listaMedidasCnt=[]
                if (ui.item.cnt_presenta!=null&&ui.item.cnt_presenta!=''){
                    app.listaMedidasCnt =ui.item.cnt_presenta.split(',')
                }
				app.producto.productoid = ui.item.codigo
				app.producto.codigo_pp = ui.item.codigo_pp
				app.producto.descripcion = ui.item.codigo + " | " + ui.item.descripcion
				app.producto.nom_prod = ui.item.descripcion
				app.producto.medida = ui.item.medida
				app.producto.cantidad = '1'
				app.producto.precioSelOption = 0
				app.producto.precioSelPre = 0
				app.producto.stock = ui.item.cnt
				app.producto.presentacionTmepPO = ui.item.presentaciones?ui.item.presentaciones.split(","):[];
				app.producto.precio = ui.item.precio == null ? parseFloat(0 + "").toFixed(4) : parseFloat(ui.item.precio + "").toFixed(4)
				app.producto.precio2 = ui.item.precio2 == null ? parseFloat(0 + "").toFixed(4) : parseFloat(ui.item.precio2 + "").toFixed(4)
				app.producto.precio3 = ui.item.precio3 == null ? parseFloat(0 + "").toFixed(4) : parseFloat(ui.item.precio3 + "").toFixed(4)
				app.producto.precio4 = ui.item.precio4 == null ? parseFloat(0 + "").toFixed(4) : parseFloat(ui.item.precio4 + "").toFixed(4)
				app.producto.precio_unidad = ui.item.precio_unidad == null ? parseFloat(0 + "").toFixed(4) : parseFloat(ui.item.precio_unidad + "").toFixed(4)

				/*   app.producto.precio = parseFloat(ui.item.precio + "").toFixed(2) */
				/*  app.producto.precio2 = parseFloat(ui.item.precio2 + "").toFixed(2)
				 app.producto.precio3 = parseFloat(ui.item.precio3 + "").toFixed(2)
				 app.producto.precio4 = parseFloat(ui.item.precio4 + "").toFixed(2)
				 app.producto.precio_unidad = parseFloat(ui.item.precio_unidad + "").toFixed(2) */
				app.producto.precioVenta = parseFloat(ui.item.precio + "").toFixed(4)
				app.producto.codigo = ui.item.codigo
				app.producto.codigo_prod = ui.item.codigo_pp
				app.producto.costo = ui.item.costo
				let array = [{
						precio: parseFloat(app.producto.precio_unidad)
					},
					{
						precio: parseFloat(app.producto.precio4)
					},
					{
						precio: parseFloat(app.producto.precio)
					},
					{
						precio: parseFloat(app.producto.precio2)
					},
					{
						precio: parseFloat(app.producto.precio3)
					},
				]
                app.producto.precioProductos =array

				app.precioProductos = array
				console.log(array);
				$('#input_buscar_productos').val("");
				//$("#example-text-input-cnt").focus()
				$("#example-text-input").focus()
			}
		});

		$("#example-text-input").on('keypress', function(e) {
			if (e.which == 13) {
				$("#submit-a-product").click()
				$("#input_buscar_productos").focus()
			}
		});
		$(document).ready(function() {
			// Utilizamos eventos delegados para manejar los clics en los botones de "Editar" y "Guardar"
			$(document).on("click", ".btnedit", function() {
				// Encuentra el padre <tr> del botón
				var tr = $(this).closest("tr");
				// Muestra los inputs y oculta los span con clase "save"
				tr.find(".edit").show();
				tr.find(".save").hide();
				tr.find(".btnedit").hide();
				tr.find(".btnsave").show();
			});

			$(document).on("click", ".btnsave", function() {
				// Encuentra el padre <tr> del botón
				var tr = $(this).closest("tr");
				// Oculta los inputs y muestra los span con clase "save"
				tr.find(".edit").hide();
				tr.find(".save").show();
				tr.find(".btnedit").show();
				tr.find(".btnsave").hide();
			});
		});


	})
</script>