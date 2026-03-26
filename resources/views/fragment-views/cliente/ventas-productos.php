<?php
$conexion = (new Conexion())->getConexion();

$datoEmpresa = $conexion->query("select * from empresas where id_empresa='{$_SESSION['id_empresa']}'")->fetch_assoc();

$igv_empresa = $datoEmpresa['igv'];
?>
<script src="<?= URL::to('public/js/qrCode.min.js') ?>"></script>
<div class="page-title-box">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h6 class="page-title">Ventas</h6>
            <ol class="breadcrumb m-0">
                <li class="breadcrumb-item"><a href="javascript: void(0);">Facturación</a></li>
                <li class="breadcrumb-item"><a href="/ventas" class="button-link">Ventas</a></li>
                <li class="breadcrumb-item active" aria-current="page">Productos</li>
            </ol>
        </div>
        <div class="col-md-4">
            <div class="float-end d-none d-md-block">
                <button id="backbuttonvp" href="/ventas" type="button" class="btn btn-warning button-link"><i class="fa fa-arrow-left"></i> Regresar</button>
            </div>
        </div>
    </div>
</div>
<input type="hidden" id="fecha-app" value="<?= date("Y-m-d") ?>">
<?php
if (isset($_GET["coti"])) {
    echo "<input type='hidden' id='cotizacion' value='{$_GET["coti"]}'>";
}
?>

<div class="row" id="container-vue">
    <div class="col-12 row">
        <div class="col-md-8">
            <div class="card ">
                <div class="card-body">

                    <h4 class="card-title">Venta de Productos</h4>

                    <div class="card-title-desc">

                    </div>

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
                                <canvas hidden="" id="qr-canvas" v-show="toggleCamara" style="width: 300px; padding: 10px;"></canvas>

                                <div class="row">
                                    <label class="col-lg-2 control-label">Cliente</label>

                                    <div class="form-group mb-3">
                                        <div class="col-lg-12">
                                            <div class="input-group">

                                                <input id="input_datos_cliente" v-model="venta.num_doc" type="text" placeholder="Ingrese Documento" class="form-control" maxlength="11">
                                                <div class="input-group-prepend">
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
                                            <div class="input-group">
                                                <input v-model="venta.dir_cli" type="text" placeholder="Dirección 1" class="form-control ui-autocomplete-input" autocomplete="off">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text" id="basic-addon1">
                                                        <input v-model="venta.dir_pos" name="dirserl" value="1" type="radio" class="form-check-input">
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div hidden class="form-group  mb-3">
                                        <div class="col-lg-12">
                                            <div class="input-group">
                                                <input v-model="venta.dir2_cli" type="text" placeholder="Dirección 2" class="form-control ui-autocomplete-input" autocomplete="off">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text" id="basic-addon1">
                                                        <input :disabled="!isDirreccionCont" v-model="venta.dir_pos" name="dirserl" value="2" type="radio" class="form-check-input">
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group row mb-3">

                                    <label class="col-lg-2 control-label">Buscar</label>

                                    <div class="col-lg-10">

                                        <div class="input-group">
                                            <input @input="chambioInputSearchProd" type="text" placeholder="Consultar Productos" class="form-control ui-autocomplete-input" id="input_buscar_productos" autocomplete="off">
                                            <div class="input-group-btn p-1">
                                                <!-- <button id="btn-scan-qr" @click="toggleCamara" class="btn btn-primary">
                                                                        Escanear QR
                                                                        </button> -->
                                                <!-- Canvas para mostrar la vista de la cámara -->

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
                                <div class="form-group ">
                                    <div class="row" style="margin-right: 0;">
                                        <div class="row  col-lg-3">
                                            <label for="example-text-input" class="col-form-label">Stock Actual</label>

                                            <div class="input-group">
                                                <input disabled v-model="producto.stock" class="form-control text-center" type="text" placeholder="0">
                                                <span class="input-group-text" id="basic-addon1">{{producto.medida}}</span>
                                            </div>
                                        </div>
                                        <div class="row  col-lg-5">
                                            <label for="example-text-input" class=" col-form-label">Cantidad</label>

                                            <div class="input-group">
                                                <input @keypress="onlyNumber" required v-model="producto.cantidad" class="form-control text-center" type="text" placeholder="0" id="example-text-input">
                                                <select v-model="producto.presentacion" class="form-select">
                                                    <option v-for="(item ) in listaOpcionesPResen" :value="item.cod">{{item.nom}}</option>
                                                </select>
                                                <span class="input-group-text">De</span>
                                                <template v-if="listaMedidasCnt.length>0">
                                                    <select required v-model="producto.presentacionCnt" class="form-select">
                                                        <option v-for="itm in listaMedidasCnt">{{itm}}</option>
                                                    </select>
                                                </template>
                                                <template v-else>
                                                    <input v-model="producto.presentacionCnt" required @keypress="onlyNumber" class="form-control" />
                                                </template>
                                                <span class="input-group-text">{{producto.medida}}</span>
                                            </div>


                                        </div>
                                        <div class="row  col-lg-3">
                                            <label for="example-text-input" class=" col-form-label">Precio</label>
                                            <div class="input-group">
                                                <select name="" id="" class="form-control" v-model="producto.precio_unidad">
                                                    <option v-for="(value, key) in precioProductos" :value="value.precio" :key="key">{{ value.precio }}</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-lg-2">
                                            <button id="submit-a-product" type="submit" class="btn btn-success"><i class="fa fa-check"></i> Agregar</button>
                                        </div>
                                    </div>

                                </div>


                            </form>
                        </div>

                        <div class="col-md-12 mt-5">
                            <div class="row">
                                <div class="text-left col-md-9">
                                    <h4>Detalle Venta</h4>
                                </div>
                                <div class="col-md-3" v-if="productos.length > 0">
                                    <label for="">Usar</label>
                                    <select name="" id="" class="form-control text-right" v-model="usar_precio" @change="cambiarPrecio($event)">
                                        <option value="1">Precio</option>
                                        <option value="2">Credito 1</option>
                                        <option value="3">Credito 2</option>
                                        <option value="4">Precio x Saco</option>
                                        <option value="5">Precio x Mayor</option>
                                    </select>
                                </div>
                            </div>
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Item</th>
                                        <th>Producto</th>
                                        <th>Cantidad</th>
                                        <th>Medida</th>
                                        <th>P. Unit.</th>
                                        <th>Parcial</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(item,index) in productos">
                                        <td>{{index+1}}</td>
                                        <td>{{item.descripcion}}</td>
                                        <td><span v-if="!item.edicion">{{item.cantidad}} {{nombreMedida(item.presentacion)}} / {{item.presentacionCnt}}{{item.medida}}</span><input v-if="item.edicion" v-model="item.cantidad"></td>
                                        <td> </td>
                                        <td><span v-if="!item.edicion">{{formatoDecimal(item.precioVenta)}}</span><input v-if="item.edicion" v-model="item.precioVenta"></td>

                                        <td>{{formatoDecimal(item.precioVenta*item.cantidad)}}</td>
                                        <td><button @click="eliminarItemPro(index)" type="button" class="btn btn-danger btn-sm">
                                                <i class="fa fa-times"></i>
                                            </button>
                                            <button v-if="!item.edicion" @click="item.edicion=true" class="btn btn-info btn-sm"><i class="fa fa-edit"></i></button>
                                            <button v-if="item.edicion" @click="item.edicion=false" class="btn btn-warning btn-sm"><i class="fa fa-save"></i></button>
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
                                        <div class="col-md-12 form-group">
                                            <label class="control-label">Aplicar IGV Venta</label>
                                            <select :disabled="!apli_igv_is" v-model="venta.apli_igv" class="form-control">
                                                <option value="1">SI</option>
                                                <option value="0">NO</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6 form-group">
                                            <label class="control-label">Documento</label>
                                            <div class="col-md-12">
                                                <select :disabled="isCoti" @change="onChangeTiDoc($event)" v-model="venta.tipo_doc" class="form-control">
                                                    <option value="1">BOLETA DE VENTA</option>
                                                    <option value="2">FACTURA</option>
                                                    <option value="6">NOTA DE VENTA</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-6 form-group">
                                            <label class="control-label">Tipo Pago</label>
                                            <select :disabled="isCoti" v-model="venta.tipo_pago" @change="changeTipoPago" class="form-control">
                                                <option value="1">Contado</option>
                                                <option value="2">Crédito</option>
                                            </select>
                                        </div>
                                        <div class="col-md-12 form-group">
                                            <label class="control-label">Método Pago</label>
                                            <select class="form-control" v-model='venta.metodo'>
                                                <option v-for="(value, key) in metodosPago" :value="value.id_metodo_pago" :key="key">{{ value.nombre }}</option>

                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-lg-4 control-label">Ser | Num</label>
                                        <div class="col-lg-12 row">
                                            <div class="col-lg-6">
                                                <input v-model="venta.serie" type="text" class="form-control text-center" readonly="">
                                            </div>
                                            <div class="col-lg-6">
                                                <input v-model="venta.numero" type="text" class="form-control text-center" readonly="">
                                            </div>
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
                                    <div class="form-group  mb-3">
                                        <label class="col-lg-4 control-label">Fecha</label>
                                        <div class="col-lg-12">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group ">
                                                        <label class="control-label">Emisión</label>
                                                        <div class="col-lg-12">
                                                            <input v-model="venta.fecha" type="date" placeholder="dd/mm/aaaa" name="input_fecha" class="form-control text-center" value="2021-10-16">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
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
                                    <div v-if="venta.tipo_pago=='2'" class="form-group ">
                                        <label class="control-label">Días de pago</label>
                                        <div class="col-lg-12">
                                            <input @focus="focusDiasPagos" v-model="venta.dias_pago" type="text" class="form-control text-center">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-lg-4 control-label">Cliente</label>
                                    </div>



                                    <div class="form-group  mb-3">
                                        <div class="col-lg-12">
                                            <label>Observaciones</label>
                                            <div class="input-group">

                                                <input v-model="venta.observ" type="text" placeholder="" class="form-control ui-autocomplete-input" autocomplete="off">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group  mb-3">

                                        <div class="col-lg-12">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="form-group ">
                                                        <label class="control-label">Paga con</label>
                                                        <div class="col-lg-12">
                                                            <input v-model="venta.pagacon" @keypress="onlyNumber" type="text" placeholder="" class="form-control text-center">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-group ">
                                                        <label class="control-label">Vuelto</label>
                                                        <div class="col-lg-12">
                                                            <input :value="vuelDelPago" disabled type="text" class="form-control text-center">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group  mb-3">
                                        <label>Cantidad de Pagos</label>
                                        <select class="form-control" v-model="venta.cantidadPagos">
                                            <option value="1">1 Pago</option>
                                            <option value="2">2 Pagos</option>
                                            <option value="3">3 Pagos</option>
                                            <option value="4">4 Pagos</option>
                                            <option value="5">5 Pagos</option>
                                        </select>
                                    </div>

                                    <div v-for="(index, pagoIndex) in parseInt(venta.cantidadPagos)" :key="pagoIndex">
                                        <div class="col-md-12 form-group">
                                            <label class="control-label">Método de Pago {{ pagoIndex + 1 }}</label>
                                            <select class="form-control" v-model="venta.pagos[pagoIndex].metodoPago">
                                                <option v-for="(value, key) in metodosPago" :value="value.id_metodo_pago" :key="key">{{ value.nombre }}</option>
                                            </select>
                                        </div>

                                        <div class="form-group mb-3">
                                            <div class="col-lg-12">
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <div class="form-group">
                                                            <label class="control-label">Monto de Pago {{ pagoIndex + 1 }}</label>
                                                            <div class="col-lg-12">
                                                                <input v-model="venta.pagos[pagoIndex].montoPago" @keypress="onlyNumber" type="text" placeholder="" class="form-control text-center">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group  mb-3">
                                        <div class="col-lg-12">
                                            <button @click="guardarVenta" type="button" class="btn btn-lg btn-primary" id="btn_finalizar_pedido">
                                                <i class="fa fa-save"></i> Guardar
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <div class="bg-primary pv-15 text-center  p-3" style="height: 90px; color: white">
                                <h1 class="mv-0 font-400" id="lbl_suma_pedido">{{monedaSibol}} {{formatoDecimal(totalProdustos)}}</h1>
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
                        <div class="d-grid gap-2">
                            <button @click="sumardiaspago" class="btn btn btn-success" type="button">+</button>
                        </div>
                        <!-- <input placeholder="10,20,30,........" v-model="venta.dias_pago" @keypress="onlyNumberComas" type="text" class="form-control">
                        <div class="form-text">Separar por comas los días de pagos</div> -->
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <table class="text-center table-sm table table-bordered">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Fecha</th>
                                        <th>Monto</th>
                                        <th>Método</th>
                                        <th>Acción</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(item,index) in venta.dias_lista">
                                        <td></td>
                                        <td><input type="date" v-model="item.fecha"></td>
                                        <td><input type="number" step="0.01" v-model="item.monto" /></td>
                                        <td>
                                            <select v-model="item.metodo" class="form-control">
                                                <option v-for="(valuem, keym) in metodosPago" :value="valuem.id_metodo_pago" :key="keym">{{ valuem.nombre }}</option>
                                            </select>
                                        </td>
                                        <td><button type="button" class="btn btn-danger btn-sm" @click="quitardiaspago(index)"><i class="fa fa-times"></i></button></td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="2">Total</th>
                                        <th>{{totalValorListaDias}}</th>
                                        <th></th>
                                    </tr>
                                    <tr>
                                        <th colspan="2">Restante</th>
                                        <th>{{restanteValorListaDias}}</th>
                                        <th></th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Guardar</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalImprimirComprobante" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Imprimir Comprobante</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <button id="ce-t-a4" class="print-pfd-sld mt-2 btn btn-primary"><i class="fa fa-file-pdf"></i> Hoja A4</button>
                    <button id="ce-t-a4-m" class="print-pfd-sld mt-2 btn btn-primary"><i class="fa fa-file-pdf"></i> Media Hoja A4</button>
                    <button id="ce-t-8cm" class="print-pfd-sld mt-2 btn btn-info"><i class="fas fa-file-invoice"></i> Voucher 8cm</button>
                    <button id="ce-t-5_6cm" class="print-pfd-sld mt-2 btn btn-info"><i class="fas fa-file-invoice"></i> Voucher 5.8cm</button>

                </div>
                <div class="modal-footer">
                    <a href="<?= URL::to('/ventas') ?>" class="btn btn-secondary">Cerrar</a>
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
                                    <td>Medida</td>
                                    <td>Precio</td>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="item in itemsLista">
                                    <th>{{item.codigo_pp}} | {{item.descripcion}}</th>
                                    <th>{{item.cnt}}</th>
                                    <th><input style="width: 80px;" v-model="item.cantidad" /></th>
                                    <th>{{item.medida}}</th>
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



<script>
    /*   function modalFunsns(link,linkd,nameFile,num,email) {
        const html = `
        <div class="row text-start">
            <div class="col-md-12">
                <form id="from-sen-email" >
                <div class="form-group">
                    <label>Enviar Por Email</label>
                    <div class="input-group mb-3">
                        <input type="hidden" name="nombrefile" value="${nameFile}">
                        <input type="hidden" name="link" value="${linkd}">
                      <input value="${email}" required name="email" type="email" class="form-control" placeholder="ejemplo@gmail.com" >
                      <div class="input-group-prepend">
                        <button type="submit" class="btn btn-primary"><i class="fa fa-send"></i> Enviar</button>
                      </div>
                    </div>
                </div>
                </form>

                <form id="from-sen-whatsapp" >

                <div class="form-group">
                    <label>Enviar a Whatsapp</label>
                    <div class="input-group mb-3">
                        <div class="input-group-prepend">
                            <span class="input-group-text">+51 </span>
                         </div>
                      <input require name="num" value="${num}" type="text" class="form-control" placeholder="00000" >
                        <input type="hidden" name="link" value="${link}">
                      <div class="input-group-prepend">
                        <button class="btn btn-primary"><i class="fa fa-send"></i> Enviar</button>
                      </div>
                    </div>
                </div>
             </form>
            </div>
        </div>`;
        Swal.fire({
            title:"Enviar Factura",
            html,
            didOpen: () => {
                //Swal.showLoading()
                const formSendEmail = Swal.getHtmlContainer().querySelector('#from-sen-email');
                formSendEmail.addEventListener("submit",function (evt) {
                    evt.preventDefault();
                    $("#loader-menor").show();
                    _post("/ajs/send/comprobante/email",$(this).serialize(),
                        function (resp) {
                            console.log(resp);
                            if (resp.res){
                                alertExito("Enviado")
                            }else{
                                alertAdvertencia("No se pudo Enviar")
                            }
                        });
                });
                const formSendWatsapp = Swal.getHtmlContainer().querySelector('#from-sen-whatsapp');
                formSendWatsapp.addEventListener("submit",function (evt) {
                    evt.preventDefault();
                    const numero = $(this).find("input[name='num']").val();
                    const linkVen = $(this).find("input[name='link']").val();

                    var link = "https://api.whatsapp.com/send?phone=";
                    const cod_ = 51;
                    const number_ = numero;
                    const mensaje = linkVen;
                    if (number_.length > 0) {
                        link += cod_ + number_
                        if (mensaje.length > 0) {
                            link += "&text=" + encodeURIComponent(mensaje)
                        }
                    }
                    window.open(link);
                  
                });
                console.log(formSendEmail);
            },
        })
        setTimeout(function (){},100)
    } */
    $(document).ready(function() {

        console.log($('.idAlmacen').val());

        const app = new Vue({
            el: "#container-vue",
            data: {
                listaMedida: [{
                    cod: 1,
                    nom: 'Unidad'
                }, {
                    cod: 2,
                    nom: 'Caja'
                }, {
                    cod: 3,
                    nom: 'Bolsa'
                }, {
                    cod: 4,
                    nom: 'Saco'
                }, ],
                enProceso: true,
                usar_scaner: false,
                apli_igv_is: true,
                listaMedidasCnt: [],
                producto: {
                    presentacionTmepPO: [],
                    edicion: false,
                    productoid: "",
                    descripcion: "",
                    nom_prod: "",
                    cantidad: "1",
                    medida: "",
                    stock: "",
                    codigo: "",
                    costo: "",
                    codsunat: "",
                    precio: '',
                    almacen: '<?php echo $_SESSION["sucursal"] ?>',
                    precio2: '',
                    precio3: '',
                    precio4: '',
                    precio_unidad: '',
                    precioVenta: '',
                    precio_usado: 1,
                    presentacion: '1',
                    presentacionCnt: '1',
                },
                usar_precio: '5',
                productos: [],
                metodosPago: [],
                precioProductos: [],
                venta: {
                    cantidadPagos: 0,
                    pagos: Array.from({
                        length: 5
                    }, () => ({
                        metodoPago: null,
                        montoPago: null
                    })),
                    segundoPago: false,
                    pagacon2: '',
                    pagacon: '',
                    observ: '',
                    apli_igv: 1,
                    dir_pos: 1,
                    tipo_doc: '6',
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
                    dias_lista: [],
                    metodo: 12,
                    metodo2: 12,
                    moneda: 1,
                    tc: '',
                },
                dataKey: '',
                listaTempProd: [],
                itemsLista: [],
                pointSel: 1,
                isCoti: <?= isset($_GET['coti']) ? 'true' : 'false' ?>
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
                nombreMedida(cod) {
                    return this.listaMedida.find(item => item.cod == cod)?.nom
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
                chambioInputSearchProd() {
                    const codInput = $("#input_buscar_productos").val().trim();
                    if (this.usar_scaner) {
                        if (codInput.length > 3) {
                            _post("/ajs/data/producto/info/code", {
                                    code: codInput,
                                    almacen: this.producto.almacen
                                },
                                function(resp) {
                                    console.log(resp.data);
                                    if (resp.res) {
                                        const ui = {
                                            item: resp.data
                                        }
                                        app.producto.productoid = ui.item.codigo
                                        app.producto.descripcion = ui.item.codigo + " | " + ui.item.descripcion
                                        app.producto.nom_prod = ui.item.descripcion
                                        app.producto.cantidad = ''
                                        app.producto.stock = ui.item.cantidad
                                        app.producto.precio = ui.item.precio == null ? parseFloat(0 + "").toFixed(4) : parseFloat(ui.item.precio + "").toFixed(4)
                                        app.producto.precio2 = ui.item.precio2 == null ? parseFloat(0 + "").toFixed(4) : parseFloat(ui.item.precio2 + "").toFixed(4)
                                        app.producto.precio3 = ui.item.precio3 == null ? parseFloat(0 + "").toFixed(4) : parseFloat(ui.item.precio3 + "").toFixed(4)
                                        app.producto.precio4 = ui.item.precio4 == null ? parseFloat(0 + "").toFixed(4) : parseFloat(ui.item.precio4 + "").toFixed(4)
                                        app.producto.precio_unidad = ui.item.precio_unidad == null ? parseFloat(0 + "").toFixed(4) : parseFloat(ui.item.precio_unidad + "").toFixed(4)
                                        app.producto.precioVenta = parseFloat(ui.item.precio_unidad + "").toFixed(4)
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
                                        $("#input_buscar_productos").val('')
                                    } else {
                                        //alertAdvertencia("No se encontro algun producto con el codigo: "+$("#input_buscar_productos").val())
                                        $("#input_buscar_productos").val('')
                                    }
                                }
                            )
                        }
                    }
                },
                cambiarPrecio(event) {
                    console.log(event.target.value)

                    var self = this

                    this.productos.forEach(element => {
                        if (event.target.value == 1) {
                            element.precioVenta = element.precio
                            /*  ui.item.precio == null ? parseFloat(0 + "").toFixed(2) : parseFloat(ui.item.precio + "").toFixed(2) */
                            element.precio_usado = '1'
                        } else if (event.target.value == 2) {
                            element.precioVenta = element.precio2
                            element.precio_usado = '2'
                        } else if (event.target.value == 3) {
                            element.precioVenta = element.precio3
                            element.precio_usado = '3'

                        } else if (event.target.value == 4) {
                            element.precioVenta = element.precio4
                            element.precio_usado = '4'
                        } else {
                            element.precioVenta = element.precio_unidad
                            element.precio_usado = '5'
                        }

                    });
                },
                buscarPorCodigoBarra() {

                },
                cargarCotizacion() {
                    const vue = this;
                    _post("/ajs/cotizaciones/info", {
                            coti: $("#cotizacion").val()
                        },
                        function(resp) {
                            console.log("aaaaaaaaa", resp);
                            vue.productos = resp.productos.map(ert => {
                                ert.descripcion = ert.codigo.toString().trim() + ' | ' + ert.descripcion
                                ert.edicion = false
                                return ert
                            })
                            //vue.venta.fecha = resp.fecha
                            vue.venta.cotiId = resp.cotizacion_id
                            vue.venta.moneda = resp.moneda
                            vue.venta.tc = resp.cm_tc
                            vue.venta.tipo_doc = resp.id_tido
                            vue.venta.tipo_pago = resp.id_tipo_pago
                            vue.venta.dias_pago = resp.dias_pagos
                            vue.venta.dir_pos = parseInt(resp.direccion + "")
                            vue.venta.num_doc = resp.cliente_doc
                            vue.venta.nom_cli = resp.cliente_nom
                            vue.venta.dir_cli = resp.cliente_dir1
                            vue.venta.dir2_cli = resp.cliente_dir2
                            if (vue.isCoti) {
                                vue.venta.tipo_doc = '6'
                                vue.venta.tipo_pago = '2'
                            }
                            /*   vue.venta.cotizacion = $('#cotizacion').val() */
                            vue.usar_precio = resp.usar_precio
                            setTimeout(function() {
                                vue.venta.dias_lista = resp.cuotas.map(c => {
                                    let foundMethod = null;
                                    if (c.tipo_pago) {
                                        let uiItem = c.tipo_pago.toUpperCase();
                                        foundMethod = vue.metodosPago.find(m => m.nombre.toUpperCase() === uiItem || m.nombre.toUpperCase().includes(uiItem));
                                    }
                                    c.metodo = foundMethod ? foundMethod.id_metodo_pago : (c.id_metodo_pago || 12);
                                    c.estado = '1';
                                    return c
                                })
                            }, 1000)
                            vue.buscarSNdoc();

                        }
                    )
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
                onChangeAlmacen(event) {
                    /*    window.localStorage.removeItem('idChecks'); */
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
                            app.listaMedidasCnt = []
                            if (ui.item.cnt_presenta != null && ui.item.cnt_presenta != '') {
                                app.listaMedidasCnt = ui.item.cnt_presenta.split(',')
                            }
                            app.producto.productoid = ui.item.codigo
                            app.producto.descripcion = ui.item.codigo + " | " + ui.item.descripcion
                            app.producto.nom_prod = ui.item.descripcion
                            app.producto.medida = ui.item.medida
                            app.producto.cantidad = ''
                            app.producto.stock = ui.item.cnt
                            app.producto.presentacionTmepPO = ui.item.presentaciones ? ui.item.presentaciones.split(",") : [];
                            app.producto.precio = ui.item.precio == null ? parseFloat(0 + "").toFixed(4) : ui.item.precio
                            app.producto.precio2 = ui.item.precio2 == null ? parseFloat(0 + "").toFixed(4) : parseFloat(ui.item.precio2 + "").toFixed(4)
                            app.producto.precio3 = ui.item.precio3 == null ? parseFloat(0 + "").toFixed(4) : parseFloat(ui.item.precio3 + "").toFixed(4)
                            app.producto.precio4 = ui.item.precio4 == null ? parseFloat(0 + "").toFixed(4) : parseFloat(ui.item.precio4 + "").toFixed(4)
                            app.producto.precio_unidad = ui.item.precio_unidad == null ? parseFloat(0 + "").toFixed(4) : ui.item.precio_unidad
                            app.producto.codigo = ui.item.codigo
                            app.producto.costo = ui.item.costo
                            app.producto.precioVenta = ui.item.precio_unidad == null ? parseFloat(0 + "").toFixed(4) : ui.item.precio_unidad
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
                            console.log(array);
                            $('#input_buscar_productos').val("");
                            $("#example-text-input").focus()
                        }
                    });
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
                    /*  this.producto.almacen = 1 */
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
                                    if (typeof resp.data.direccion !== 'undefined') {
                                        app._data.venta.dir_cli = resp.data.direccion.trim().length > 0 ? resp.data.direccion : '-'
                                    }

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
                    const vuee = this
                    if (this.enProceso) {
                        this.enProceso = false
                        if (this.productos.length > 0) {

                            var continuar = true;
                            var mensaje = '';
                            if (this.isCoti) {
                                let totalCalculado = 0;
                                this.venta.dias_lista.forEach(el => {
                                    totalCalculado += parseFloat(el.monto || 0);
                                });
                                if (Math.abs(totalCalculado - this.venta.total) > 0.01) {
                                    continuar = false;
                                    mensaje = 'El total de las cuotas (' + totalCalculado.toFixed(2) + ') debe ser igual al total de la venta (' + parseFloat(this.venta.total).toFixed(2) + ')';
                                }
                            }



                            // Validación unificada para todos los tipos de documentos
                            if (this.venta.tipo_doc == '1') {
                                if (this.venta.num_doc.length == 11) {
                                    continuar = false;
                                    mensaje = 'No puede emitir Boleta usando RUC';
                                }
                            } else if (this.venta.tipo_doc == '2') {
                                if (this.venta.nom_cli.length < 5) {
                                    mensaje = 'Debe escribir la Razón Social o dar al botón para buscar el ruc';
                                    continuar = false;
                                }
                                if (this.venta.num_doc.length != 11) {
                                    mensaje = 'Solo se puede emitir Factura usando RUC';
                                    continuar = false;
                                }
                            }

                            // Auto-generar cuotas para Crédito si no existen (aplica a Boleta, Factura y Nota de Venta)
                            if (this.venta.tipo_pago == 2 && continuar) {
                                if (this.venta.dias_lista.length == 0) {
                                    console.log("Generando cuotas automáticas antes de guardar...");
                                    // Generar las 5 cuotas automáticamente aquí si faltan
                                    let fechaBase = new Date();
                                    if (this.venta.fecha) {
                                        let parts = this.venta.fecha.split('-');
                                        fechaBase = new Date(parts[0], parts[1] - 1, parts[2]); // Constructor seguro (YYYY, MM-1, DD)
                                    }

                                    // Cuota 1: Total
                                    let fecha1 = new Date(fechaBase);
                                    let yyyy1 = fecha1.getFullYear();
                                    let mm1 = String(fecha1.getMonth() + 1).padStart(2, '0');
                                    let dd1 = String(fecha1.getDate()).padStart(2, '0');

                                    this.venta.dias_lista.push({
                                        fecha: `${yyyy1}-${mm1}-${dd1}`,
                                        monto: (this.venta.total > 0 ? this.venta.total : 0).toFixed(2)
                                    });

                                    // Cuotas 2-5
                                    for (let i = 1; i < 5; i++) {
                                        fechaBase.setDate(fechaBase.getDate() + 1);
                                        let yyyy = fechaBase.getFullYear();
                                        let mm = String(fechaBase.getMonth() + 1).padStart(2, '0');
                                        let dd = String(fechaBase.getDate()).padStart(2, '0');
                                        this.venta.dias_lista.push({
                                            fecha: `${yyyy}-${mm}-${dd}`,
                                            monto: (0).toFixed(2),
                                            metodo: 12,
                                            estado: '1'
                                        });
                                    }
                                }
                            }

                            /*                    console.log(continuar);  */
                            if (continuar) {
                                if (this.venta.total > 0) {
                                    let idCoti = JSON.parse('<?php echo addslashes(json_encode(isset($_GET["coti"]) ? $_GET["coti"] : null)); ?>');
                                    const data = {
                                        ...this.venta,
                                        listaPro: JSON.stringify(this.productos),
                                        datosGuiaRemosion: localStorage.getItem('datosGuiaRemosion'),
                                        datosTransporteGuiaRemosion: localStorage.getItem('datosTransporteGuiaRemosion'),
                                        productosGuiaRemosion: localStorage.getItem('productosGuiaRemosion'),
                                        datosUbigeoGuiaRemosion: localStorage.getItem('datosUbigeoGuiaRemosion'),
                                        idCoti: idCoti
                                    }
                                    data.dias_lista = JSON.stringify(this.venta.dias_lista.map(dd => {
                                        let met = this.metodosPago.find(m => m.id_metodo_pago == dd.metodo);
                                        dd.metodo_nombre = met ? met.nombre : 'Efectivo';
                                        return dd;
                                    }))
                                    /*console.log(data);
                                    return*/
                                    /*  console.log(data); */
                                    /*  return */
                                    /*  return */
                                    /* console.log('linea 775'); */
                                    /*  $("#loader-menor").show(); */
                                    _ajax("/ajs/ventas/add", "POST",
                                        data,
                                        function(resp) {
                                            console.log(resp);

                                            let desde = localStorage.getItem('desde')
                                            /*  let dataGuia = JSON.parse(localStorage.getItem('datosGuiaRemosion'))
                                             dataGuia = JSON.parse(dataGuia) */
                                            /*   return */
                                            if (resp.res) {
                                                // No resetear enProceso a true si tiene éxito para evitar re-envíos
                                                vuee.enProceso = false;

                                                /*   console.log(resp);
                                                  return */
                                                alertExito("Exito", "Venta Guardada").then(function() {
                                                        $("#ce-t-a4").attr("href", _URL + "/venta/comprobante/pdf/" + resp.venta + "/" + resp.nomxml);
                                                        $("#ce-t-a4-m").attr("href", _URL + "/venta/comprobante/pdf/ma4/" + resp.venta + "/" + resp.nomxml);
                                                        $("#ce-t-8cm").attr("href", _URL + "/venta/pdf/voucher/8cm/" + resp.venta + "/" + resp.nomxml);
                                                        $("#ce-t-5_6cm").attr("href", _URL + "/venta/pdf/voucher/5.6cm/" + resp.venta + "/" + resp.nomxml);
                                                        $("#modalImprimirComprobante").modal("show");

                                                    })
                                                    .then(function() {
                                                        //location.reload();
                                                    })

                                                if (desde == 'coti_guia') {
                                                    let idVenta = {
                                                        idVenta: resp.venta
                                                    }
                                                    data.idVenta = resp.venta
                                                    _ajax("/ajs/guia/remision/add2", "POST", {
                                                            data
                                                        },
                                                        function(resp) {
                                                            console.log(resp);
                                                            localStorage.removeItem("desde");
                                                            localStorage.removeItem("datosGuiaRemosion");
                                                            localStorage.removeItem("datosTransporteGuiaRemosion");
                                                            localStorage.removeItem("productosGuiaRemosion");
                                                            localStorage.removeItem("datosUbigeoGuiaRemosion");
                                                            $("#backbuttonvp").click();
                                                        }
                                                    )
                                                }
                                            } else {
                                                // Si hubo error, permitimos volver a intentarlo
                                                vuee.enProceso = true;
                                                alertAdvertencia("Alerta", resp.msj)
                                            }
                                        }
                                    )

                                } else {
                                    alertAdvertencia('El monto debe ser mayor a 0')
                                }


                            } else {
                                this.enProceso = true
                                alertAdvertencia(mensaje)
                            }
                        } else {
                            this.enProceso = true
                            alertAdvertencia("No hay productos agregados a la lista ")
                        }
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
                    if (this.venta.tipo_doc == 6) {
                        this.apli_igv_is = false
                        this.venta.apli_igv = 1
                    } else {
                        this.apli_igv_is = true;
                    }
                },
                limpiasDatos() {
                    this.listaMedidasCnt = []
                    this.producto = {
                        presentacionTmepPO: [],
                        edicion: false,
                        productoid: "",
                        descripcion: "",
                        nom_prod: "",
                        cantidad: "1",
                        medida: "",
                        stock: "",
                        codigo: "",
                        costo: "",
                        codsunat: "",
                        precio: '',
                        almacen: '<?php echo $_SESSION["sucursal"] ?>',
                        precio2: '',
                        precio3: '',
                        precio4: '',
                        precio_unidad: '',
                        precioVenta: '',
                        precio_usado: 1,
                        presentacion: '1',
                        presentacionCnt: '1',
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
                        prod.precioVenta = prod.precioVenta * (prod.cantidad * prod.presentacionCnt)

                        this.productos.push(prod)
                        this.limpiasDatos();
                        this.usar_precio = 5
                    } else {
                        alertAdvertencia("Busque un producto primero")
                            .then(function() {
                                setTimeout(function() {
                                    $("#input_buscar_productos").focus();
                                }, 500)
                            })
                    }

                },
                changeTipoPago() {
                    console.log("Cambio tipo pago");
                    this.venta.dias_lista = [];
                    if (this.venta.tipo_pago == '2') {
                        // Fecha base: Usar la fecha seleccionada o la actual si falla
                        let fechaBase;
                        if (this.venta.fecha) {
                            fechaBase = new Date(this.venta.fecha + 'T00:00:00');
                            if (isNaN(fechaBase.getTime())) { // Si es inválida
                                fechaBase = new Date(); // Usar hoy
                            }
                        } else {
                            fechaBase = new Date();
                        }

                        // Cuota 1: Total
                        this.venta.dias_lista.push({
                            fecha: this.venta.fecha || fechaBase.toISOString().split('T')[0],
                            monto: (this.venta.total > 0 ? this.venta.total : 0).toFixed(2),
                            metodo: 12,
                            estado: '1'
                        });

                        // Cuotas 2-5: 0 y +1 día cada una
                        for (let i = 1; i < 5; i++) {
                            fechaBase.setDate(fechaBase.getDate() + 1);

                            // Formato YYYY-MM-DD manual para evitar problemas de zona horaria
                            let yyyy = fechaBase.getFullYear();
                            let mm = String(fechaBase.getMonth() + 1).padStart(2, '0');
                            let dd = String(fechaBase.getDate()).padStart(2, '0');

                            this.venta.dias_lista.push({
                                fecha: `${yyyy}-${mm}-${dd}`,
                                monto: (0).toFixed(2),
                                metodo: 12,
                                estado: '1'
                            });
                        }
                    }
                },
                sumardiaspago() {
                    let fecha_ = new Date()
                    let data = {
                        fecha: this.formatDate(fecha_),
                        monto: 0,
                        metodo: 12,
                        estado: '1'
                    };
                    this.venta.dias_lista.push(data);
                    /* if (listD.length > 0) {

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
                    } */
                },
                quitardiaspago(index) {
                    this.venta.dias_lista.splice(index, 1);
                }
            },
            computed: {
                listaOpcionesPResen() {
                    const vue = this
                    if (this.producto.presentacionTmepPO.length > 0) {
                        return this.listaMedida.filter(item => {
                            return vue.producto.presentacionTmepPO.find(item2 => item2 == item.cod)
                        })
                    } else {
                        return this.listaMedida
                    }
                },
                monedaSibol() {
                    return (this.venta.moneda == 1 ? 'S/' : '$')
                },
                vuelDelPago() {
                    if (this.venta.pagacon.length > 0) {
                        let pagacon = parseFloat(this.venta.pagacon)
                        if (this.venta.segundoPago) {
                            pagacon = pagacon + (isNaN(parseFloat(this.venta.pagacon2)) ? 0 : parseFloat(this.venta.pagacon2))
                        }
                        return pagacon - parseFloat(this.totalProdustos)
                    } else {
                        return ''
                    }
                },
                totalValorListaDias() {
                    var total_ = 0;
                    this.venta.dias_lista.forEach((el) => {
                        total_ += parseFloat(el.monto + "")
                    })
                    return "S/ " + total_.toFixed(4);
                },
                restanteValorListaDias() {
                    var total_ = 0;
                    this.venta.dias_lista.forEach((el) => {
                        total_ += parseFloat(el.monto + "")
                    })
                    var restante = this.venta.total - total_;
                    return "S/ " + restante.toFixed(2);
                },
                isDirreccionCont() {
                    return this.venta.dir2_cli.length > 0;
                },
                totalProdustos() {
                    const vue = this
                    var total = 0;
                    this.productos.forEach(function(prod) {
                        if (vue.venta.moneda == 2) {
                            total += (prod.precioVenta / parseFloat(vue.venta.tc || '1')) * prod.cantidad
                        } else {
                            total += prod.precioVenta * prod.cantidad
                        }

                    })

                    this.venta.total = total;
                    return total.toFixed(4)
                }
            }
        });
        app.buscarSNdoc();

        _ajax("/ajs/consulta/metodo/pago", "POST", {

            },
            function(resp) {
                console.log(resp);
                app._data.metodosPago = resp
                /*     app.venta.serie = resp.serie
                    app.venta.numero = resp.numero */
            }
        )
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

        /*      $("#input_buscar_productos").autocomplete({
                     source: function(request, response) {
                         $.ajax({
                                     url: _URL + "/ajs/cargar/productos",
                                     dataType: "json",
                                     data: {
                                         term: request.term,
                                         country_id: $("#country_id").val()
                                     },
                                     success: function(data) {

                                         select: function(event, ui) {
                                             response(data);
                                             console.log(data);
                                             return

                                             app.producto.productoid = data.item.codigo
                                             app.producto.descripcion = data.item.codigo + " | " + data.item.descripcion
                                             app.producto.nom_prod = data.item.descripcion
                                             app.producto.cantidad = ''
                                             app.producto.stock = data.item.cnt
                                             app.producto.precio = data.item.precio
                                             app.producto.codigo = data.item.codigo
                                             app.producto.costo = data.item.costo
                                             let array = [{
                                                     precio: data.item.precio
                                                 },
                                                 {
                                                     precio: data.item.precio2
                                                 },
                                                 {
                                                     precio: data.item.precio3
                                                 },
                                             ]

                                             app.precioProductos = array
                                             console.log(array);
                                             $('#input_buscar_productos').val("");

                                         }
                                     });
                             },
                             min_length: 3,
                             delay: 300
                     }); */
        $("#input_buscar_productos").autocomplete({

            source: _URL + `/ajs/cargar/productos/${app.producto.almacen}`,
            minLength: 1,
            select: function(event, ui) {
                event.preventDefault();
                /*    console.log(item);
                   console.log(ui); */
                console.log(ui.item);
                /*  return */
                app.listaMedidasCnt = []
                if (ui.item.cnt_presenta != null && ui.item.cnt_presenta != '') {
                    app.listaMedidasCnt = ui.item.cnt_presenta.split(',')
                }
                app.producto.productoid = ui.item.codigo
                app.producto.descripcion = ui.item.codigo_pp + " | " + ui.item.descripcion
                app.producto.nom_prod = ui.item.descripcion
                app.producto.medida = ui.item.medida
                app.producto.cantidad = ''
                app.producto.stock = ui.item.cnt
                app.producto.presentacionTmepPO = ui.item.presentaciones ? ui.item.presentaciones.split(",") : [];
                app.producto.precio = ui.item.precio == null ? parseFloat(0 + "").toFixed(4) : ui.item.precio
                app.producto.precio2 = ui.item.precio2 == null ? parseFloat(0 + "").toFixed(4) : parseFloat(ui.item.precio2 + "").toFixed(4)
                app.producto.precio3 = ui.item.precio3 == null ? parseFloat(0 + "").toFixed(4) : parseFloat(ui.item.precio3 + "").toFixed(4)
                app.producto.precio4 = ui.item.precio4 == null ? parseFloat(0 + "").toFixed(4) : parseFloat(ui.item.precio4 + "").toFixed(4)
                app.producto.precio_unidad = ui.item.precio_unidad == null ? parseFloat(0 + "").toFixed(4) : ui.item.precio_unidad
                app.producto.precioVenta = ui.item.precio_unidad
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
                $("#example-text-input").focus()
            }
        });

        <?php
        if (isset($_GET["coti"])) {
            echo "app.cargarCotizacion();";
        }
        ?>
        $("#example-text-input").on('keypress', function(e) {
            if (e.which == 13) {
                $("#submit-a-product").click()
                $("#input_buscar_productos").focus()
            }
        });
        $("#container-vue").on("click", ".print-pfd-sld", function() {
            console.log("ssssssssssssssssssss")

            let printA4 = $(this).attr('href')
            if ($("#device-app").val() == 'desktop') {
                var iframe = document.createElement('iframe');
                iframe.style.display = "none";
                iframe.src = printA4;
                document.body.appendChild(iframe);
                iframe.contentWindow.focus();
                iframe.contentWindow.print();
                console.log(printA4);
            } else {
                window.open(printA4)
            }
        })

        $('#container-vue .modalImprimirComprobante').on('hidden.bs.modal', function(e) {
            location.reload();
        })

        $('#modalImprimirComprobante').on('hidden.bs.modal', function(e) {
            location.reload();
        })

        $(document).on('click', '#btndiaspago', function() {
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

        });
    })
</script>