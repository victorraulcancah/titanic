<script type="text/javascript" src="https://rawgit.com/schmich/instascan-builds/master/instascan.min.js"></script>
    <!-- <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/vue/2.1.10/vue.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/webrtc-adapter/3.3.3/adapter.min.js"></script> -->
    <!-- <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css"> -->
<div class="page-title-box">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h6 class="page-title">Compra</h6>
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
    <div class="col-12">
        <div class="card">
            <div class="card-body">


                <div class="card-title-desc">

                </div>
                <div class="panel panel-default">
                    <div class="panel-body">

                        <div class="col-lg-12">
                            <hr class="fg-black line-body" />
                        </div>

                        <div id="" class="col-xs-12 col-sm-12 col-md-12 no-padding">

                            <div class="col-xs-12 col-sm-12 col-md-12 no-padding">

                                <div class="row" id="container-vue">
                                    <div class="col-12 row">
                                        <div class="col-md-8">
                                            <div class="panel">
                                                <div class="panel-body">

                                                    <div class="row">
                                                        <div class="col-md-12">
                                                        <div>
                                                            <!-- <button class="btn btn-primary" onclick="encender()" @click="addProduct" id="escaner">Escanear</button> -->
                                                            <button class="btn btn-primary" @click="encender" id="escaner">Escanear</button>
                                                        </div>
                                                        <video id="preview" width="50%" style="display: none; margin-bottom: 50px;" ></video>
                                                            <form id="frmCompraProducto" class="form-horizontal">
                                                                <div class="form-group row mb-3">
                                                                    <label class="col-lg-2 control-label">Buscar</label>
                                                                    <div class="col-lg-8">
                                                                        <div style="display: flex;">
                                                                            <div class="col-lg-10" style="padding-left: 0;">
                                                                                <input type="text" class="form-control" id="descripcionBuscar" v-model="producto.productoBusca">
                                                                                <!-- inicio de scaner -->
                                                                                
                                                                            </div>
                                                                            <div class=" col-lg-2">
                                                                                <select class="form-control" v-model="producto.almacen">
                                                                                    <option value="1">Almacen 1</option>
                                                                                    <option value="2">Tienda 1</option>
                                                                                </select>
                                                                            </div>
                                                                            <!-- boton para escanear -->
                                                                            
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row mb-3">
                                                                    <label class="col-lg-2 control-label">Descripcion</label>
                                                                    <div class="col-lg-10">
                                                                        <input type="text" class="form-control" placeholder="Producto" id="descripcionBuscar" v-model="producto.descripcion" readonly="">
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row  mb-3">

                                                                    <label class="col-lg-2 control-label">Stock Actual</label>
                                                                    <div class="col-lg-10">
                                                                        <div class="row">
                                                                            <div class="row  col-lg-3">
                                                                                <div class="col-sm-12">
                                                                                    <input disabled class="form-control text-center" type="text" placeholder="0" id="stockActual" name="stockActual" v-model="producto.stock">
                                                                                </div>
                                                                            </div>
                                                                            <div class="row  col-lg-4">
                                                                                <label for="example-text-input" class="col-sm-4  control-label">Cantidad</label>
                                                                                <div class="col-sm-8">
                                                                                    <input class="form-control text-center only-number" type="text" placeholder="0" id="cantidad" name="cantidad" autocomplete="off" v-model="producto.cantidad">
                                                                                </div>
                                                                            </div>
                                                                            <div class="row  col-lg-4">
                                                                                <label for="example-text-input" class="col-sm-4 control-label">Precio</label>
                                                                                <div class="col-sm-8">
                                                                                    <input @keypress="onlyNumber" class="form-control text-end" type="text" placeholder="0.00" id="example-text-input" v-model="producto.precio">
                                                                                </div>
                                                                            </div>
                                                                            <div>
                                                                                <button @click="addProduct" type="button" class="btn btn-success"><i class="fa fa-check"></i> Agregar
                                                                            </div>
                                                                        </div>
                                                                    </div>


                                                                </div>
                                                                <div id="modal_ver_detalle" class="modal fade" tabindex="-1" aria-labelledby="myModalLabel" style="display: none;" aria-hidden="true">
                                                                    <div class="modal-dialog">
                                                                        <div class="modal-content">
                                                                            <input type="hidden" name="idProducto" id="idProducto" value="">

                                                                            <div class="modal-header">
                                                                                <h5 class="modal-title" id="myModalLabel">Productos
                                                                                </h5>

                                                                            </div>
                                                                            <div class="modal-body" id="modal_detalle">
                                                                                <table class="table">
                                                                                    <thead>
                                                                                    <tr>
                                                                                        <th style="width: 10%;text-align: center;">Item</th>
                                                                                        <th style="width: 70%;text-align: center;">Producto</th>
                                                                                        <th style="width: 10%;text-align: center;">Stock</th>
                                                                                        <th style="width: 10%;text-align: center;">Agregar</th>

                                                                                    </tr>
                                                                                    </thead>
                                                                                    <tbody>
                                                                                    <tr v-for="product in productoInfo " :key="product.id_producto">
                                                                                        <td style="text-align: center;">{{product.id_producto}}</td>
                                                                                        <td style="text-align: center;"> {{product.descripcion}}</td>
                                                                                        <td style="text-align: center;">{{product.cantidad}}</td>
                                                                                        <td style="text-align: center;"><button @click="agregarLista(product.id_producto,product.descripcion,product.cantidad, $event)" data-id="product.id_producto" class="btn btn-success"><i class="fa fa-plus"></i></button></td>
                                                                                    </tr>
                                                                                    </tbody>

                                                                                </table>
                                                                            </div>


                                                                            <div class="modal-footer">

                                                                                <button class="btn btn-info" data-dismiss="modal" aria-label="Close">Guardar</button>
                                                                            </div>
                                                                        </div>
                                                                        <!-- /.modal-content -->
                                                                    </div>
                                                                    <!-- /.modal-dialog -->
                                                                </div>


                                                            </form>
                                                        </div>

                                                        <div class="col-md-12 mt-5" style="margin-top: 25px">
                                                            <div class="form-group ">
                                                                <div style="width: 100%; height: 20px; border-bottom: 2px solid #0866c6; text-align: left">
                                                                            <span style="font-size: 16px; font-weight: bold ; background-color: #ffffff; padding: 1px 4px;">
                                                                                Productos
                                                                                <!--Padding is optional-->
                                                                            </span>

                                                                </div>
                                                            </div>
                                                            <table class="table">
                                                                <thead>
                                                                <tr>
                                                                    <th>Item</th>
                                                                    <th>Producto</th>
                                                                    <th>Cantidad</th>
                                                                    <th>P. Unit.</th>
                                                                    <th>Parcial</th>
                                                                    <th></th>
                                                                </tr>
                                                                </thead>
                                                                <tbody>
                                                                <tr v-for="(item,index) in productos">
                                                                    <td>{{index+1}}</td>
                                                                    <td>{{item.codigo_app}} |{{item.descripcion}}</td>
                                                                    <td>{{item.cantidad}}</td>
                                                                    <td>{{item.precio}}</td>
                                                                    <td>{{item.precio*item.cantidad}}</td>
                                                                    <td><button @click="eliminarItemPro(index)" type="button" class="btn btn-danger btn-xs">
                                                                            <i class="fa fa-times"></i>
                                                                        </button></td>
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
                                                                    <div class="row form-group">
                                                                        <div class="col-md-6 form-group">
                                                                            <label class="control-label">Documento</label>
                                                                            <div class="col-md-12">
                                                                                <select @change="onChangeTiDoc($event)" v-model="venta.tipo_doc" class="form-control">

                                                                                    <option value="2">FACTURA</option>
                                                                                    <option value="12">NOTA DE COMPRA</option>
                                                                                </select>
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-md-6">
                                                                            <label class="control-label">Tipo Pago</label>
                                                                            <select v-model="venta.tipo_pago" @change="changeTipoPago" class="form-control">
                                                                                <option value="1">Contado</option>
                                                                                <option value="2">Credito</option>
                                                                            </select>
                                                                        </div>
                                                                    </div>
                                                                    <div class="form-group">
                                                                        <div class="col-lg-12 row">
                                                                            <div class="col-lg-6">
                                                                                <label class="text-center col-md-12">Serie</label>
                                                                                <input v-model="venta.serie" type="text" class="form-control text-center">
                                                                            </div>
                                                                            <div class="col-lg-6">
                                                                                <label class="text-center col-md-12">Numero</label>
                                                                                <input v-model="venta.numero" type="text" class="form-control text-center">
                                                                            </div>
                                                                        </div>
                                                                        <div class="col-lg-12 row" style="margin-top: 15px;">
                                                                            <div class="col-lg-6">
                                                                                <label class="text-center col-md-12">Moneda</label>
                                                                                <select v-model="venta.moneda" @change="chageMoneda" class="form-control">
                                                                                    <option value="1">PEN</option>
                                                                                    <option value="2">USD</option>
                                                                                </select>
                                                                            </div>

                                                                        </div>
                                                                    </div>
                                                                    <div class="form-group">
                                                                        <label class="col-lg-12 text-center">Fecha</label>
                                                                        <div class="col-lg-12">
                                                                            <div class="row">
                                                                                <div class="col-md-6">
                                                                                    <div class="form-group ">
                                                                                        <label class="control-label">Emision</label>
                                                                                        <div class="col-lg-12">
                                                                                            <input v-model="venta.fecha" type="date" placeholder="dd/mm/aaaa" name="input_fecha" class="form-control text-center" value="2021-10-16">
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="col-md-6">
                                                                                    <div class="form-group ">
                                                                                        <label class="control-label">Vencimiento</label>
                                                                                        <div class="col-lg-12">
                                                                                            <input v-model="venta.fechaVen" type="date" placeholder="dd/mm/aaaa" name="input_fecha" class="form-control text-center" value="2021-10-16">
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div v-if="venta.tipo_pago=='2'" class="form-group ">
                                                                        <label class="control-label">Dias de pago</label>
                                                                        <div class="col-lg-12">
                                                                            <input @focus="focusDiasPagos" v-model="venta.dias_pago" type="text" class="form-control text-center">
                                                                        </div>
                                                                    </div>
                                                                    <div class="form-group">
                                                                        <label class="col-lg-4 control-label" style="text-align:center;">Proveedor</label>
                                                                    </div>

                                                                    <div class="form-group mb-3">
                                                                        <div class="col-lg-12">
                                                                            <div class="input-group">
                                                                                <input id="input_datos_cliente" v-model="venta.num_doc" type="text" placeholder="Ingrese Documento" class="form-control" maxlength="11">
                                                                                <div class="input-group-addon">
                                                                                    <button @click="buscarDocumentSS" class="btn btn-primary" type="button"><i class="fa fa-search"></i></button>
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
                                                                            <div class="">
                                                                                <input v-model="venta.dir_cli" type="text" placeholder="Direccion 1" class="form-control" autocomplete="off">

                                                                            </div>
                                                                        </div>
                                                                    </div>

                                                                    <div class="form-group  mb-3">
                                                                        <div class="col-lg-12">
                                                                            <button @click="guardarCompra" type="button" class="btn btn-lg btn-primary" id="btn_finalizar_pedido">
                                                                                <i class="fa fa-save"></i> Guardar
                                                                            </button>
                                                                        </div>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                            <div class="bg-primary pv-15 text-center  p-3" style="height: 90px; color: white">
                                                                <h1 class="mv-0 font-400" id="lbl_suma_pedido">S/ {{totalProdustos}}</h1>
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
                                                    <h5 class="modal-title" id="exampleModalLabel">Dias de Pagos</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="row mb-3">
                                                        <div class="col-md-6">
                                                            <div class="">
                                                                <label class="form-label">Fecha Emision</label>
                                                                <input v-model="venta.fecha" disabled type="date" class="form-control">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <div class="">
                                                                <label class="form-label">Monto TotalVenta</label>
                                                                <input :value="'S/ '+venta.total" disabled type="text" class="form-control">
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label">Dias de pagos</label>
                                                        <input placeholder="10,20,30,........" v-model="venta.dias_pago" @keypress="onlyNumberComas" type="text" class="form-control">
                                                        <div class="form-text">Separe por comas los días de pagos</div>
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
                                                                    <td><input type="text" v-model="item.monto"></td>
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
                                                    <button  type="button" class="btn btn-info" data-bs-dismiss="modal">Guardar</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>



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


    $(".only-number").keypress((evt) => {
        let keyCode = evt.keyCode ? evt.keyCode : evt.which;
        if ((keyCode < 48 || keyCode > 57) && keyCode !== 46) {
            // 46 is dot
            evt.preventDefault();
        }
    });


    $(document).ready(function() {
        $('#btnCerrar').click(function() {
            $("#modal_ver_detalle").modal('toggle');
        })
        var app = new Vue({
            el: "#container-vue",
            data: {
                producto: {
                    almacen:"1",
                    productoid: "",
                    descripcion: "",
                    codigo_app:'',
                    nom_prod: "",
                    cantidad: "",
                    stock: "",
                    precio: "",
                    codigo: "",
                    costo: "",
                    codsunat: "",
                    productoBuscar: "",
                },
                productos: [],
                productoDesc: [],
                productoInfo: [],
                venta: {
                    dir_pos: 1,
                    tipo_doc: '2',
                    serie: '',
                    numero: '',
                    tipo_pago: '1',
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
                    moneda: "1",
                    dias_lista: [],

                }
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
                   
                    encender(){
                    //let scanner; 
                    let preview = document.getElementById('preview'); // Asegúrate de tener el elemento 'preview' en tu HTML
                    let escaner = document.getElementById('escaner'); // Asegúrate de tener el elemento 'escaner' en tu HTML
                    preview.style.display = 'block';
                    
                    if (escaner.textContent === 'Cancelar') {
                    
                        escaner.textContent = 'Escanear QR';
                        // Ocultar la vista previa
                        preview.style.display = 'none';

                    }else{
                        escaner.textContent = 'Cancelar';
                        // Mostrar la vista previa
                        preview.style.display = 'block';
                        //scanner.stop();
                    }
                    
                    // Inicializar el objeto scanner cuando se presiona el botón
                    scanner = new Instascan.Scanner({ video: preview });
                    
                    // Obtener cámaras y empezar a escanear
                    Instascan.Camera.getCameras().then(function(cameras) {
                        if (cameras.length > 0) {
                            scanner.start(cameras[0]);
                        } else {
                            alert('No se encontraron cámaras');
                        }
                    }).catch(function(e) {
                    console.log(e)
                    });
            

                    scanner.addListener('scan',function(c) {
                        // scanner.stop();
                        //console.log(c);
                        // $("#descripcionBuscar").val(c).trigger('input');
                        $("#descripcionBuscar").val(c);
                        
                        $.ajax({
                            type: "post",
                            url: _URL + '/ajas/compra/buscar/producto',
                            data: {
                                producto: c // Código escaneado
                            },
                            success: function(response) {
                                let data = JSON.parse(response);
                                //console.log("data:",data);
                                
                                //console.log(descripcion);
                                //console.log(precio);

                                // Manejar la respuesta del servidor
                                if (data.res == true) {
                                    // if (descripcion.length>0 && precio.length>0) {
                                        let descripcion = data.data[0].descripcion;
                                        let precio = data.data[0].precio;
                                        let id = data.data[0].id_producto;
                                        let codigo = data.data[0].codigo;
                                       app.addProductQR(id,descripcion,precio,codigo);
                                    // }else{
                                    //     alert("No paso");
                                    // }
                                   //console.log()
                                   
                                //     $('#cantidad').val("1");
                                //alert("producto encontrado");
                                //     // Agregar el producto a la lista
                                //     this.productos.cantidad = 1
                                //     this.precio
                               
                                
                                } else {
                                    $("#descripcionBuscar").val('');
                                    //debugger
                                    // Producto no encontrado
                                    Swal.fire({
                                        icon: 'warning',
                                        title: 'Advertencia',
                                        text: 'No se encontró ningun producto',
                                    })
                                }
                                   // productos.push(data);
                                   
                            },
                            error: function() {
                                // Manejar errores de AJAX
                                alert('Error al buscar el producto.');
                            }
                        });
                        // this.buscarProducto(c);
                    });   

                },
                /*       guardarDiasFechas() {
                          let totalVentaLista = parseFloat(this.totalValorListaDias + "")
                          let ventaTotal = parseFloat(this.venta.total + "")
                          let listaDiasVacia = []
                          console.log(this.venta.dias_lista);
                          $.ajax({
                              url: _URL + "/ajs/validarLista",
                              type: "POST",
                              data: {
                                  dias_lista: this.venta.dias_lista
                              }

                          }).done(function(resp) {
                              console.log(JSON.parse(resp));
                          });
                          if (totalVentaLista == ventaTotal) {
                              console.log(this.venta.dias_lista);
                          } else {
                              this.venta.dias_lista = []
                              console.log(this.venta.dias_lista);
                          }
                      }, */
                agregarLista(id, producto, stock, $event) {

                    $event.preventDefault()
                    Swal.fire({
                        title: "¿Deseas agregar este producto?",
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#3085d6",
                        cancelButtonColor: "#d33",
                        confirmButtonText: "Si",
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $("#modal_ver_detalle").modal("hide")
                            this.producto.productoid = id;
                            this.producto.descripcion = producto;
                            this.producto.stock = stock;
                            this.limpiasDatos;
                            $("#descripcionBuscar").val('')
                            this.productoInfo = []
                            this.venta.dias_lista = []
                            this.venta.dias_pago = ''
                            /* $.ajax({
                                url: _URL + "/ajs/usuarios/logout",
                                type: "POST",
                            }).done(function() {
                                window.location.href = _URL + "/login";
                            }); */
                        } else {}
                    });
                },
                buscarProducto() {
                    /* this.limpiasDatos(); */
                    this.limpiasDatos;
                    /* $("#descripcionBuscar").val('') */
                    this.productoInfo = []
                    $('#stockActual').val('')
                    $('#producto').empty()
                    var self = this;
                    self.productos.stock = ''
                    var self = this
                    if ($("#descripcionBuscar").val().length > 2) {
                        $("#loader-menor").show()
                        $.ajax({
                            type: "post",
                            url: _URL + '/ajas/compra/buscar/producto',
                            data: {
                                producto: $("#descripcionBuscar").val()
                            },
                            success: function(resp) {
                                $('#stockActual').val('')
                                self.productos.stock = ''
                                $("#loader-menor").hide()
                                let data = JSON.parse(resp);
                                if (data.res) {
                                    $("#modal_ver_detalle").modal("show")
                                    data.data.map(function(item) {
                                        self.productoInfo.push(item)
                                        /*  console.log(item); */
                                    })

                                } else {
                                    Swal.fire({
                                        icon: 'warning',
                                        title: 'Advertencia',
                                        text: 'No se encontró ningun producto',
                                    })
                                }

                            }
                        });
                    } else {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Advertencia',
                            text: 'Digite al menos 3 caracter',
                        })
                    }
                },
                formatoDecimal(num, desc = 2) {
                    return parseFloat(num + "").toFixed(desc);
                },
                visualFechaSee(fecha) {
                    return formatFechaVisual(fecha);
                },
                formatDate(date) {
                    /*  console.log(date); */
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
                chageMoneda(event) {
                    console.log(event.target.value)
                    this.venta.moneda = event.target.value;

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
                    if (this.venta.num_doc.length == 11) {
                        $("#loader-menor").show()
                        this.venta.dir_pos = 1
                        _ajax("/ajs/consulta/doc/cliente", "POST", {
                                doc: this.venta.num_doc
                            },
                            function(resp) {
                                $("#loader-menor").hide()
                                /* console.log(resp); */
                                if (resp.res) {
                                    app._data.venta.nom_cli = (resp.data.nombre ? resp.data.nombre : '') + (resp.data.razon_social ? resp.data.razon_social : '')
                                    app._data.venta.dir_cli = resp.data.direccion
                                } else {
                                    alertAdvertencia("Documento no enocntrado")
                                }
                            }
                        )
                    } else {
                        alertAdvertencia("El RUC debe tener 11 digitos")
                    }
                },
                guardarCompra() {
                    if (this.productos.length > 0) {
                        let data = JSON.stringify(this.productos);
                        let datos =
                            console.log(data);

                        var continuar = true;
                        var mensaje = '';



                        if (this.venta.tipo_doc == '1') {
                            if (this.venta.num_doc.length == 11) {
                                continuar = false;
                                mensaje = 'No puede emitir Boleta usando RUC';
                            }
                            if (this.venta.tipo_pago == 2) {
                                if (this.venta.dias_lista.length == 0) {
                                    continuar = false;
                                    mensaje = 'Debe especificar los días de pagos para un venta a crédito';
                                }
                            }
                        } else if (this.venta.tipo_doc == '2') {
                            mensaje = 'Solo se puede emitir Factura usando RUC';
                            if (this.venta.num_doc.length != 11) {
                                continuar = false;
                            }
                            if (this.venta.tipo_pago == 2) {
                                if (this.venta.dias_lista.length == 0) {
                                    continuar = false;
                                    mensaje = 'Debe especificar los días de pagos para un venta a crédito';
                                }
                            }


                        }

                        if (continuar) {
                            const data = {
                                ...this.venta,
                                listaPro: JSON.stringify(this.productos)
                            }
                            data.dias_lista = JSON.stringify(data.dias_lista)
                            console.log(data);
                            /*   $("#loader-menor").show(); */
                            /*  console.log(this.venta.dir_cli); */
                            if (this.venta.fecha !== undefined && this.venta.nom_cli !== '' && this.venta.serie !== '') {
                                if (this.venta.dir_cli !== undefined) {
                                    $("#loader-menor").hide();
                                    _ajax("/ajas/compras/add", "POST",
                                        data,
                                        function(resp) {
                                            /*  console.log(JSON.parse(resp)); */
                                            /*    console.log(resp); */
                                            if (resp.resp) {
                                                Swal.fire({
                                                    icon: 'success',
                                                    title: 'Bien',
                                                    text: 'Registro Exitoso',
                                                }).then(function() {
                                                    console.log(resp);
                                                    window.location = _URL + '/compras';
                                                });
                                            } else {
                                                alertAdvertencia('Alerta', 'No se pudo insertar el registro')
                                            }
                                        }
                                    )
                                } else {
                                    $("#loader-menor").hide();
                                    alertAdvertencia('Alerta', 'Llene el formulario Correctamente')
                                }
                            } else {
                                $("#loader-menor").hide();
                                alertAdvertencia('Alerta', 'Llene el formulario Correctamente')
                            }
                        } else {
                            alertAdvertencia(mensaje)
                        }
                    } else {
                        alertAdvertencia("No hay productos agregados a la lista ")
                    }

                },
                buscarSNdoc() {

                    /*     $.ajax({
                            type: 'post',
                            url: _URL + '/ajs/consulta/sn',
                            data: {
                                doc: this.venta.tipo_doc
                            },
                            success: function(resp) {
                                $("#loader-menor").hide()
                                if (isJson(resp)) {
                                    func(JSON.parse(resp));
                                } else {
                                    console.log(resp)
                                    alertError('ERR', 'Error en el servidor')
                                }

                            }
                        }); */
                    /*_ajax("/ajs/consulta/sn", "POST", {
                            doc: this.venta.tipo_doc
                        },
                        function(resp) {
                            app.venta.serie = resp.serie
                        }
                    )*/
                },
                onChangeTiDoc(event) {
                    this.buscarSNdoc();
                },
                limpiasDatos() {
                    this.producto = {
                        productoid: "",
                        descripcion: "",
                        cantidad: "",
                        precio: "",
                        codigo: "",
                        costo: "",
                        productoBusca: "",
                        stock: "",
                        productoInfo: []
                    }
                },
                addProductQR(id,descripcion,precio,codigo) {
                    //if (this.producto.stock)
                   let cantidad = 1;
                   if (descripcion.length > 0 && precio.length > 0) {
                    const prod = {
                        value:codigo + "|" + descripcion +"| P.Venta S/:" + precio +"|Stock:" + cantidad ,
                            ...this.productos
                        }
                        prod.cantidad = cantidad;
                        prod.codigo = codigo;
                        prod.productoid = id;
                        prod.descripcion = descripcion;
                        prod.precio = precio.toString(); 
                        this.productos.push(prod);
                        //this.limpiasDatos();
                        console.log("QR",prod);
                   }else{
                    alert("No se pudo guardar los datos");
                    }
                },
                addProduct() {
                    //if (this.producto.stock)
                    if (this.producto.descripcion.length > 0 && this.producto.cantidad.length > 0 && this.producto.precio.length > 0) {
                        const prod = {
                            ...this.producto
                        }
                        /*  console.log(this.producto.descripcion); */
                        // para agregar el arreglo
                        this.productos.push(prod)
                        console.log("addproduct",prod);
                        this.limpiasDatos();
                        $("#producto").empty()
                        $('#producto')
                            .find('option')
                            .remove()
                            .end()
                            .append('<option value=""></option>')
                    } else {
                        alertAdvertencia("Llene todos los campos")
                            .then(function() {
                                setTimeout(function() {
                                    $("#input_buscar_productos").focus();
                                }, 500)
                            })
                    }

                },
                onChangeSelect(event) {

                    var self = this;
                    $.ajax({
                        type: "post",
                        url: _URL + '/buscar/buscarStock',
                        data: {
                            id: event.target.value
                        },
                        success: function(resp) {
                            $("#loader-menor").hide()
                            let data = JSON.parse(resp)
                            /*  console.log(data); */
                            /* const dataStock = this.producto = {
                                stock: element[4]
                            } */
                            if (data.res) {
                                /* console.log(data.data[0][2]); */
                                self.producto.stock = JSON.parse(data.data[0][0]);
                                self.producto.descripcion = (data.data[0][1]);
                                self.producto.productoid = JSON.parse(data.data[0][2]);

                                /*    console.log(data.data); */
                                /*  self.producto.descripcion = JSON.parse(data.data[0][0]); */
                                /*  this.producto = {
                                     stock: data.data[0][0]
                                 }
                                 console.log(this.producto); */
                            }
                        }
                    });
                },

            },
            computed: {
                totalValorListaDias() {
                    var total_ = 0;
                    var totales_ = 0;
                    const arrayMontoVacio = []
                    /*  console.log(this.venta.dias_lista); */
                    var arrayMontosInvalidos = []
                    this.venta.dias_lista.forEach((el) => {
                        arrayMontosInvalidos.push(el.monto)
                        /*  const words = ['spray', 'limit', 'elite', 'exuberant', 'destruction', 'present'];

                         const result = words.filter(word => word.length > 6); */

                        const result = arrayMontosInvalidos.filter(monto => monto > 0);

                        total_ = result.reduce((a, b) => parseFloat(a + "") + parseFloat(b + ""), 0);
                        /*   result.map(element => {
                              total_ += parseFloat(element + "");


                          }); */
                        /*  console.log(data); */
                        /*    if(isNaN(parseFloat(el.monto + ""))){
                               console.log('ins nan un campo');
                           }
                        else{
                               console.log(parseFloat(el.monto + ""));
                        } */
                        /*        arrayMontoVacio = el.monto.filter(montoInvalido => montoInvalido < 0);
                               if (el.monto !== '') {
                                   console.log('hay monto vacio' + el.monto);

                               } else {
                                   total_ = 0
                               }
                               console.log(arrayMontoVacio); */

                    })
                    /*  total_ = data */
                    return total_.toFixed(2);
                },
                isDirreccionCont() {
                    return this.venta.dir2_cli.length > 0;
                },
                totalProdustos() {
                    var total = 0;
                    this.productos.forEach(function(prod) {
                        total += prod.precio * prod.cantidad
                    })
                    this.venta.total = total;
                    return total.toFixed(2);
                }
            }
        })
        /*    $("#btnBuscar").click(function(e) {
               e.preventDefault()
               $('#stockActual').val('')
               if ($("#descripcionBuscar").val().length > 2) {
                   $("#loader-menor").show()
                   $.ajax({
                       type: "post",
                       url: _URL + '/buscar/producto',
                       data: {
                           producto: $("#descripcionBuscar").val()
                       },
                       success: function(resp) {
                           $("#loader-menor").hide()
                           let data = JSON.parse(resp);
                           if (data.res) {
                               $('#producto')
                                   .find('option')
                                   .remove()
                                   .end()
                                   .append('<option value="">SELECCIONE</option>')



                               data.data.forEach(element => {
                                   $('#producto').append($('<option>', {
                                       value: element[0],
                                       text: element[1]
                                   }));


                               });

                           } else {
                               Swal.fire({
                                   icon: 'warning',
                                   title: 'Advertencia',
                                   text: 'No se encontró ningun producto',
                               })
                           }

                       }
                   });
               } else {
                   Swal.fire({
                       icon: 'warning',
                       title: 'Advertencia',
                       text: 'Digite al menos 3 caracter',
                   })
               }
           }) */

        $("#descripcionBuscar").autocomplete({

            source: _URL + `/ajs/cargar/productos/${app.producto.almacen}`,
            minLength: 1,
            select: function(event, ui) {
                event.preventDefault();
                /*    console.log(item);
                   console.log(ui); */
                console.log(ui.item);
                /*  return */
                app.producto.productoid = ui.item.codigo
                app.producto.descripcion = ui.item.codigo + " | " + ui.item.descripcion
                app.producto.nom_prod = ui.item.descripcion
                app.producto.codigo_app = ui.item.codigo_pp
                app.producto.cantidad = ''
                app.producto.stock = ui.item.cnt
                app.producto.precio = ui.item.precio == null ? parseFloat(0 + "").toFixed(2) : parseFloat(ui.item.precio + "").toFixed(2)
                app.producto.precio2 = ui.item.precio2 == null ? parseFloat(0 + "").toFixed(2) : parseFloat(ui.item.precio2 + "").toFixed(2)
                app.producto.precio3 = ui.item.precio3 == null ? parseFloat(0 + "").toFixed(2) : parseFloat(ui.item.precio3 + "").toFixed(2)
                app.producto.precio4 = ui.item.precio4 == null ? parseFloat(0 + "").toFixed(2) : parseFloat(ui.item.precio4 + "").toFixed(2)
                app.producto.precio_unidad = ui.item.precio_unidad == null ? parseFloat(0 + "").toFixed(2) : parseFloat(ui.item.precio_unidad + "").toFixed(2)
                app.producto.precioVenta = parseFloat(ui.item.precio_unidad + "").toFixed(2)
                app.producto.codigo = ui.item.codigo
                app.producto.costo = ui.item.costo
                let array = [{
                    precio: app.producto.precio
                },
                    {
                        precio: app.producto.precio2
                    },
                    {
                        precio: app.producto.precio3
                    },
                    {
                        precio: app.producto.precio4
                    },
                    {
                        precio: app.producto.precio_unidad
                    }
                ]

                app.precioProductos = array
                /*  app.precioProductos = array */
                console.log(array);
                $('#input_buscar_productos').val("");
            }
        });

        $("#input_datos_cliente").autocomplete({
            source: _URL + `/ajs/asearch/provedor/data`,
            minLength: 1,
            select: function(event, ui) {
                event.preventDefault();
                //console.log(ui)
                app._data.venta.num_doc =ui.item.documento
                app._data.venta.nom_cli =ui.item.datos
            }
        })

    })
</script>
