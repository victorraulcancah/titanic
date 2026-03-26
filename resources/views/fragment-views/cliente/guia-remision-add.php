<?php
require_once "app/models/Ubigeo.php";
$c_ubigeo = new Ubigeo();
?>
<div class="page-title-box">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h6 class="page-title">Guía Remisión</h6>
            <ol class="breadcrumb m-0">
                <li class="breadcrumb-item"><a href="javascript: void(0);">Ventas</a></li>
                <li class="breadcrumb-item"><a href="/ventas" class="button-link">Guía Remisión</a></li>
                <li class="breadcrumb-item active" aria-current="page">Productos</li>
            </ol>
        </div>
        <div class="col-md-4">
            <div class="float-end d-none d-md-block">
                <button id="backbuttonvp" href="/guias/remision" type="button" class="btn btn-warning button-link"><i class="fa fa-arrow-left"></i> Regresar</button>
            </div>
        </div>
    </div>
</div>

<div class="row" id="container-vue">
    <?php if (!isset($_GET["coti"])) : ?>

        <div>

            <input type="hidden" id="fecha-now-app" value="<?php echo date("Y-m-d"); ?>">
            <div class="row">
                <div class="col-md-4">
                    <div class="card ">
                        <div class="card-body">
                            <div class="col-md-12">

                                <form role="form" class="form-horizontal">
                                    <h5>Datos de la Fac - Bol </h5>
                                    <div class="form-group row mb-3">
                                        <label class="col-md-4 control-label text-end">Doc.</label>
                                        <div class="col-md-8">
                                            <select v-model="guia.tipo_doc" class="form-control" name="select_documento_venta" id="select_documento_venta">
                                                <option value="1">BOLETA DE VENTA</option>
                                                <option value="2">FACTURA</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group row mb-3">
                                        <div class="col-lg-12 text-center">
                                            <button type="button" class="btn btn-info" @click="comprobarVenta()"><i class="fa fa-search"></i> Comprobar Documento Venta
                                            </button>
                                            <input type="hidden" name="input_id_venta_referencia" id="input_id_venta_referencia">
                                        </div>
                                    </div>
                                    <div class="form-group row mb-3">
                                        <label class="col-lg-4 control-label text-end">Ser | Num</label>
                                        <div class="col-lg-4">
                                            <input v-model="guia.serie" type="text" name="input_serie_venta" id="input_serie_venta" class="form-control text-center">
                                        </div>
                                        <div class="col-lg-4">
                                            <input v-model="guia.numero" type="text" name="input_numero_venta" id="input_numero_venta" class="form-control text-center">
                                        </div>
                                    </div>

                                    <div class="form-group row mb-3">
                                        <label class="col-lg-4 control-label text-end">Total</label>
                                        <div class="col-lg-6">
                                            <input type="text" class="form-control text-right" name="input_total_venta" id="input_total_venta" v-model="guia.total" disabled>
                                        </div>
                                    </div>
                                    <hr>
                                    <h5>Datos de la Guía</h5>
                                    <div class="form-group row mb-3">
                                        <label class="col-md-4 control-label text-end">Doc.</label>
                                        <div class="col-md-8">
                                            <input type="text" class="form-control text-center" value="GUIA DE REMISION" readonly name="input_doc_envio">
                                        </div>
                                    </div>
                                    <div class="form-group row mb-3">
                                        <label class="col-lg-4 control-label text-end">Ser | Num</label>
                                        <div class="col-lg-4">
                                            <input v-model="guia.serie_g" type="text" name="input_serie_guia" id="input_serie_guia" class="form-control text-center" readonly>
                                        </div>
                                        <div class="col-lg-4">
                                            <input v-model="guia.numero_g" type="text" name="input_numero_guia" id="input_numero_guia" class="form-control text-center" readonly>
                                        </div>
                                    </div>
                                    <div class="form-group row mb-3">
                                        <label class="col-lg-4 control-label text-end">Fecha</label>
                                        <div class="col-lg-6">
                                            <input type="date" name="input_fecha" id="input_fecha" class="form-control text-center" value="<?php echo date("Y-m-d"); ?>" disabled>
                                        </div>
                                    </div>
                                    <div class="form-group row mb-3">
                                        <label class="col-md-4 control-label text-end">Motivo.</label>
                                        <div class="col-md-8">
                                            <select class="form-control" name="select_motivo" id="select_motivo">
                                                <option value="1">VENTA UX</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group row mb-3">
                                        <label class="col-lg-4 control-label text-end">Peso total</label>
                                        <div class="col-lg-6">
                                            <input v-model="guia.peso" type="text" id="input_peso_total" class="form-control text-center" value="0">
                                        </div>
                                    </div>
                                    <div class="form-group row mb-3">
                                        <label class="col-lg-4 control-label text-end">Nro Bultos</label>
                                        <div class="col-lg-6">
                                            <input v-model="guia.num_bultos" type="text" id="input_nro_bultos" class="form-control text-center" value="0">
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="card ">
                        <div class="card-header">
                            <h5>Datos del Destino</h5>
                        </div>
                        <div class="card-body">
                            <div class="col-md-12">
                                <form class="form-horizontal">
                                    <div class="form-group row mb-3">
                                        <label class="col-lg-2 control-label">Destinatario</label>
                                        <div class="col-lg-10">
                                            <input v-model="guia.nom_cli" type="text" class="form-control" id="input_datos_destinatario" readonly>
                                        </div>
                                    </div>
                                    <div class="form-group row mb-3">
                                        <label class="col-lg-2 control-label">Dir. Llegada</label>
                                        <div class="col-lg-10">
                                            <input type="text" class="form-control" v-model="guia.dir_cli" id="input_dir_llegada" name="input_dir_llegada">
                                        </div>
                                    </div>
                                    <div class="form-group row mb-3">
                                        <label class="col-lg-2 control-label">Ubigeo</label>
                                        <div class="col-lg-3">
                                            <select class="form-control" name="select_departamento" id="select_departamento" onchange="obtenerProvincias()">
                                                <?php
                                                foreach ($c_ubigeo->verDepartamentos() as $fila) {
                                                    echo "<option value='{$fila["departamento"]}'>{$fila['nombre']}</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <div class="col-lg-3">
                                            <select class="form-control" name="select_provincia" id="select_provincia" onchange="obtenerDistritos()">
                                            </select>
                                        </div>
                                        <div class="col-lg-3">
                                            <select class="form-control" name="select_distrito" id="select_distrito">
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group row mb-3">
                                        <label class="col-lg-2 control-label">Transportista</label>
                                        <div class="col-lg-3">
                                            <select v-model="transporte.tipo_trans" class="form-control" name="select_tipo_transporte" id="select_tipo_transporte">
                                                <option value="1">Propio</option>
                                                <option value="2">Externo</option>
                                            </select>
                                        </div>
                                        <div class="col-lg-3">
                                            <input placeholder="RUC" type="text" id="input_documento_clientetr" v-model="transporte.ruc" name="input_ruc_transporte" class="form-control text-center">
                                            <input type="hidden" id="hidden_documento_cliente">
                                        </div>
                                        <div class="col-lg-3">
                                            <button type="button" class="btn btn-info" @click="comprobarRucTransporte()"> Comprobar DOC</button>
                                        </div>
                                    </div>
                                    <div class="form-group row mb-3">
                                        <label class="col-lg-2 control-label">Razon Social Trans.</label>
                                        <div class="col-lg-10">
                                            <input type="text" id="input_datos_cliente" v-model="transporte.razon_social" name="input_datos_cliente" class="form-control" readonly="true">
                                            <input type="hidden" id="hidden_datos_cliente">
                                        </div>
                                    </div>
                                    <div class="form-group row mb-3">
                                        <label class="col-lg-2 control-label">Vehículo</label>
                                        <div class="col-lg-3">
                                            <input placeholder="Placa Vehículo" type="text" class="form-control" maxlength="30" v-model="transporte.veiculo" id="input_vehiculo">
                                        </div>
                                    </div>
                                    <div class="form-group  row mb-3">
                                        <label class="col-lg-2 control-label">Chofer</label>
                                        <div class="col-lg-2">
                                            <input placeholder="Licencia" type="text" class="form-control" v-model="transporte.chofer_dni" id="input_dni_chofer"  maxlength="15">
                                        </div>
                                        <div class="col-lg-8">
                                            <input placeholder="Nombre" type="text" class="form-control" v-model="transporte.chofer_datos" id="input_datos_chofer">
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button @click="registerGuia" type="button" class="btn btn-success" id="btn_graba_guia"><i class="fa fa-save"></i> Generar Guía</button>
                        </div>
                    </div>
                </div>

            </div>
            <div class="col-md-12">
                <div class="card  ">
                    <div class="card-header">
                        <h5>Detalle Venta</h5>
                    </div>
                    <div class="card-body table-responsive">
                        <table class="table text-center table-sm">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>Producto</th>
                                    <th>Cantidad</th>
                                    <th>P. Unit.</th>
                                    <th>Parcial</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(item,index) in productos">
                                    <td>{{index+1}}</td>
                                    <td>{{item.descripcion}}</td>
                                    <td>{{item.cantidad}}</td>
                                    <td>{{item.precio}}</td>
                                    <td>{{subTotalPro(item.cantidad,item.precio)}}</td>
                                    <td><button @click="eliminarProducto(index)" class="btn btn-danger btn-sm"><i class="fa fa-times"></i></button></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>

    <?php else : ?>
        <div>
            <input type='hidden' id='cotizacion' value='<?php echo $_GET["coti"] ?>'>
            <input type="hidden" id="fecha-now-app" value="<?php echo date("Y-m-d"); ?>">
            <div class="row">
                <div class="col-md-4">
                    <div class="card ">
                        <div class="card-body">
                            <div class="col-md-12">

                                <form role="form" class="form-horizontal">

                                    <!--  <div class="form-group row mb-3">
                                    <label class="col-md-4 control-label text-end">Doc.</label>
                                    <div class="col-md-8">
                                        <select v-model="guia.tipo_doc" class="form-control" name="select_documento_venta" id="select_documento_venta">
                                            <option value="1">BOLETA DE VENTA</option>
                                            <option value="2">FACTURA</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group row mb-3">
                                    <div class="col-lg-12 text-center">
                                        <button type="button" class="btn btn-info" @click="comprobarVenta()"><i class="fa fa-search"></i> Comprobar Documento Venta
                                        </button>
                                        <input type="hidden" name="input_id_venta_referencia" id="input_id_venta_referencia">
                                    </div>
                                </div>
                                <div class="form-group row mb-3">
                                    <label class="col-lg-4 control-label text-end">Ser | Num</label>
                                    <div class="col-lg-4">
                                        <input v-model="guia.serie" type="text" name="input_serie_venta" id="input_serie_venta" class="form-control text-center">
                                    </div>
                                    <div class="col-lg-4">
                                        <input v-model="guia.numero" type="text" name="input_numero_venta" id="input_numero_venta" class="form-control text-center">
                                    </div>
                                </div>

                                <div class="form-group row mb-3">
                                    <label class="col-lg-4 control-label text-end">Total</label>
                                    <div class="col-lg-6">
                                        <input type="text" class="form-control text-right" name="input_total_venta" id="input_total_venta" v-model="guia.total" disabled>
                                    </div>
                                </div> -->

                                    <h5>Datos de la Guía de la Cotizacion N° <?php echo $_GET['coti'] ?></h5>
                                    <br>
                                    <div class="form-group row mb-3">
                                        <label class="col-md-4 control-label text-end">Doc.</label>
                                        <div class="col-md-8">
                                            <input type="text" class="form-control text-center" value="GUÍA DE REMISIÓN" readonly name="input_doc_envio">
                                        </div>
                                    </div>
                                    <div class="form-group row mb-3">
                                        <label class="col-lg-4 control-label text-end">Ser | Num</label>
                                        <div class="col-lg-4">
                                            <input v-model="guia.serie_g" type="text" name="input_serie_guia" id="input_serie_guia" class="form-control text-center" readonly>
                                        </div>
                                        <div class="col-lg-4">
                                            <input v-model="guia.numero_g" type="text" name="input_numero_guia" id="input_numero_guia" class="form-control text-center" readonly>
                                        </div>
                                    </div>
                                    <div class="form-group row mb-3">
                                        <label class="col-lg-4 control-label text-end">Fecha</label>
                                        <div class="col-lg-6">
                                            <input type="date" name="input_fecha" id="input_fecha" class="form-control text-center" value="<?php echo date("Y-m-d"); ?>" disabled>
                                        </div>
                                    </div>
                                    <div class="form-group row mb-3">
                                        <label class="col-md-4 control-label text-end">Motivo.</label>
                                        <div class="col-md-8">
                                            <select class="form-control" name="select_motivo" id="select_motivo">
                                                <option value="1">VENTA UX</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group row mb-3">
                                        <label class="col-lg-4 control-label text-end">Peso total</label>
                                        <div class="col-lg-6">
                                            <input v-model="guia.peso" type="text" id="input_peso_total" class="form-control text-center" value="0">
                                        </div>
                                    </div>
                                    <div class="form-group row mb-3">
                                        <label class="col-lg-4 control-label text-end">Nro Bultos</label>
                                        <div class="col-lg-6">
                                            <input v-model="guia.num_bultos" type="text" id="input_nro_bultos" class="form-control text-center" value="0">
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="card ">
                        <div class="card-header">
                            <h5>Datos del Destino</h5>
                        </div>
                        <div class="card-body">
                            <div class="col-md-12">
                                <form class="form-horizontal">
                                    <div class="form-group row mb-3">
                                        <label class="col-lg-2 control-label">Destinatario</label>
                                        <div class="col-lg-10">
                                            <input v-model="guia.nom_cli" type="text" class="form-control" id="input_datos_destinatario" readonly>
                                        </div>
                                    </div>
                                    <div class="form-group row mb-3">
                                        <label class="col-lg-2 control-label">Dir. Llegada</label>
                                        <div class="col-lg-10">
                                            <input type="text" class="form-control" v-model="guia.dir_cli" id="input_dir_llegada" name="input_dir_llegada">
                                        </div>
                                    </div>
                                    <div class="form-group row mb-3">
                                        <label class="col-lg-2 control-label">Ubigeo</label>
                                        <div class="col-lg-3">
                                            <select class="form-control" name="select_departamento" id="select_departamento" onchange="obtenerProvincias()">
                                                <?php
                                                foreach ($c_ubigeo->verDepartamentos() as $fila) {
                                                    echo "<option value='{$fila["departamento"]}'>{$fila['nombre']}</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <div class="col-lg-3">
                                            <select class="form-control" name="select_provincia" id="select_provincia" onchange="obtenerDistritos()">
                                            </select>
                                        </div>
                                        <div class="col-lg-3">
                                            <select class="form-control" name="select_distrito" id="select_distrito">
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group row mb-3">
                                        <label class="col-lg-2 control-label">Transportista</label>
                                        <div class="col-lg-3">
                                            <select v-model="transporte.tipo_trans" class="form-control" name="select_tipo_transporte" id="select_tipo_transporte">
                                                <option value="1">Propio</option>
                                                <option value="2">Externo</option>
                                            </select>
                                        </div>
                                        <div class="col-lg-3">
                                            <input placeholder="RUC" type="text" id="input_documento_cliente" v-model="transporte.ruc" name="input_ruc_transporte" class="form-control text-center">
                                            <input type="hidden" id="hidden_documento_cliente">
                                        </div>
                                        <div class="col-lg-3">
                                            <button type="button" class="btn btn-info" @click="comprobarRucTransporte()"> Comprobar DOC</button>
                                        </div>
                                    </div>
                                    <div class="form-group row mb-3">
                                        <label class="col-lg-2 control-label">Razon Social Trans.</label>
                                        <div class="col-lg-10">
                                            <input type="text" id="input_datos_cliente" v-model="transporte.razon_social" name="input_datos_cliente" class="form-control" readonly="true">
                                            <input type="hidden" id="hidden_datos_cliente">
                                        </div>
                                    </div>
                                    <div class="form-group row mb-3">
                                        <label class="col-lg-2 control-label">Vehículo</label>
                                        <div class="col-lg-3">
                                            <input placeholder="Placa Vehículo" type="text" class="form-control" maxlength="30" v-model="transporte.veiculo" id="input_vehiculo">
                                        </div>
                                    </div>
                                    <div class="form-group  row mb-3">
                                        <label class="col-lg-2 control-label">Chofer</label>
                                        <div class="col-lg-2">
                                            <input placeholder="Licencia" type="text" class="form-control" v-model="transporte.chofer_dni" id="input_dni_chofer" " maxlength="15">
                                        </div>
                                        <div class="col-lg-8">
                                            <input placeholder="Nombre" type="text" class="form-control" v-model="transporte.chofer_datos" id="input_datos_chofer">
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="card-footer">
                            <button @click="registerGuiaCoti" type="button" class="btn btn-success" id="btn_graba_guia"><i class="fa fa-save"></i> Generar Guía</button>
                        </div>
                    </div>
                </div>

            </div>
            <div class="col-md-12">
                <div class="card  ">
                    <div class="card-header">
                        <h5>Detalle Venta</h5>
                    </div>
                    <div class="card-body table-responsive">
                        <table class="table text-center table-sm">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>Producto</th>
                                    <th>Cantidad</th>
                                    <th>P. Unit.</th>
                                    <th>Parcial</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(item,index) in productos">
                                    <td>{{index+1}}</td>
                                    <td>{{item.descripcion}}</td>
                                    <td>{{item.cantidad}}</td>
                                    <td>{{item.precio}}</td>
                                    <td>{{subTotalPro(item.cantidad,item.precio)}}</td>
                                    <td><button @click="eliminarProducto(index)" class="btn btn-danger btn-sm"><i class="fa fa-times"></i></button></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>
    <?php endif; ?>


</div>



<script>
    function obtenerProvincias() {
        var select_provincia = $("#select_provincia");
        var parametros = {
            "departamento": $("#select_departamento").val()
        };

        $.ajax({
            data: parametros,
            url: _URL + '/ajs/consulta/lista/provincias',
            type: 'post',
            beforeSend: function() {
                select_provincia.find('option').remove();
            },
            success: function(response) {
                var json_response = JSON.parse(response);
                select_provincia.find('option').remove();
                $(json_response).each(function(i, v) { // indice, valor
                    select_provincia.append('<option value="' + v.provincia + '">' + v.nombre + '</option>');
                });
                select_provincia.prop('disabled', false);
                obtenerDistritos();
            },
            error: function() {
                alert("error al procesar");
            }
        });
    }

    function obtenerDistritos() {
        var select_distrito = $("#select_distrito");
        var parametros = {
            "departamento": $("#select_departamento").val(),
            "provincia": $("#select_provincia").val()
        };

        $.ajax({
            data: parametros,
            url: _URL + '/ajs/consulta/lista/distrito',
            type: 'post',
            beforeSend: function() {
                select_distrito.find('option').remove();
            },
            success: function(response) {
                var json_response = JSON.parse(response);
                select_distrito.find('option').remove();
                $(json_response).each(function(i, v) { // indice, valor
                    select_distrito.append('<option value="' + v.ubigeo + '">' + v.nombre + '</option>');
                });
                select_distrito.prop('disabled', false);
            },
            error: function() {
                alert("error al procesar");
            }
        });
    }

    $(document).ready(function() {
        var appguia = new Vue({
            el: "#container-vue",
            data: {
                guia: {
                    fecha_emision: $("#fecha-now-app").val(),
                    tipo_doc: '1',
                    serie: '',
                    numero: '',
                    total: '',
                    serie_g: '',
                    numero_g: '',
                    venta: '',
                    doc_cli: '',
                    nom_cli: '',
                    dir_cli: '',
                    peso: '1',
                    num_bultos: '1',
                },
                transporte: {
                    ruc: '',
                    tipo_trans: '1',
                    razon_social: '',
                    veiculo: '',
                    chofer_dni: '',
                    chofer_datos: '',

                },
                productos: []
            },
            methods: {
                onlyNumber($event) {
                    //console.log($event.keyCode); //keyCodes value
                    let keyCode = ($event.keyCode ? $event.keyCode : $event.which);
                    if ((keyCode < 48 || keyCode > 57) && keyCode !== 46) { // 46 is dot
                        $event.preventDefault();
                    }
                },
                registerGuia() {
                    var data = {
                        ...this.guia,
                        ...this.transporte,
                        productos: JSON.stringify(this.productos),
                        ubigeo: $("#select_distrito").val(),
                        /* tipo: 'coti_guia' */
                    }
                    $("#loader-menor").show();
                    _ajax("/ajs/guia/remision/add", "POST",
                        data,
                        function(resp) {
                            if (resp.res) {
                                alertExito("Guía Remisión Registrada")
                                    .then(function() {
                                        $("#backbuttonvp").click();
                                    })
                            } else {
                                alertAdvertencia("No se pudo completar el registro de la GUIA")
                            }
                        }
                    )
                },
                registerGuiaCoti() {
                    var data = {

                        productos: JSON.stringify(this.productos),
                        guia: JSON.stringify(this.guia),
                        transporte: JSON.stringify(this.transporte),
                        ubigeo: $("#select_distrito").val(),
                        tipo: 'coti_guia'
                       /*  ubigeo: $("#select_distrito").val(), */
                        /*   ubigeo: $("#select_distrito").val() */
                    }
                    /*   $("#loader-menor").show(); */
                    console.log(data.productos);
                    localStorage.setItem('productosGuiaRemosion', data.productos);
                    localStorage.setItem('datosGuiaRemosion', data.guia);
                    localStorage.setItem('datosTransporteGuiaRemosion', data.transporte);
                    localStorage.setItem('datosUbigeoGuiaRemosion', data.ubigeo);
                    localStorage.setItem('desde', data.tipo);
                    /*   <a href="/ventas/productos?coti=${data}" */
                    let coti = $('#cotizacion').val()
                    window.location.href = `${_URL}/ventas/productos?coti=${coti}`
                    /*      _ajax("/ajs/guia/remision/add", "POST",
                             data,
                             function(resp) {
                                 if (resp.res) {
                                     alertExito("Guia Remision Registrada")
                                         .then(function() {
                                             $("#backbuttonvp").click();
                                         })
                                 } else {
                                     alertAdvertencia("No se pudo completar el registro de la GUIA")
                                 }
                             }
                         ) */
                },
                eliminarProducto(index) {
                    this.productos.splice(index, 1);
                },
                subTotalPro(cnt, precio) {
                    return (parseFloat(cnt + "") * parseFloat(precio + "")).toFixed(2)
                },
                comprobarRucTransporte() {
                    var vue = this
                    $("#loader-menor").show();
                    _ajax("/ajs/consulta/doc/cliente", "POST", {
                            doc: this.transporte.ruc
                        },
                        function(resp) {
                            $("#loader-menor").hide()
                            console.log(resp);
                            if (resp.res) {
                                vue.transporte.razon_social = (resp.data.razon_social ? resp.data.razon_social : '')
                                _ajax("/ajs/consulta/add/dtatranspor", "POST",{
                                    ruc:resp.data.ruc,
                                    razon:(resp.data.razon_social ? resp.data.razon_social : ''),
                                    direccion:resp.data.direccion
                                })
                            } else {
                                alertAdvertencia("RUC no enocntrado")
                            }
                        }
                    )
                    /* $.ajax({
                         type: "GET",
                         url: "https://consulta.api-peru.com/api/ruc/"+this.transporte.ruc,
                         success: function (resp) {
                             $("#loader-menor").hide();
                             console.log(resp)
                             if(resp.success){
                                 var data = resp.data;
                                 vue.transporte.razon_social= data.nombre_o_razon_social
                             }else{
                                 alertAdvertencia("RUc no encontrado")
                             }
                         }
                     });*/

                    /*_ajax("/ajs/consulta/ruc","POST",
                        {ruc:this.transporte.ruc},
                        function (resp){
                            console.log(resp);
                        }
                    )*/
                },
                getDocumentoGuia() {
                    var vue = this;
                    _ajax("/ajs/consulta/sn", "POST", {
                            doc: '11'
                        },
                        function(resp) {
                            vue.guia.numero_g = resp.numero
                            vue.guia.serie_g = resp.serie
                        }
                    )
                },
                comprobarVenta() {
                    var vue = this;
                    var data = {
                        idtido: this.guia.tipo_doc,
                        serie: this.guia.serie,
                        numero: this.guia.numero,
                    }
                    $("#loader-menor").show();
                    _ajax("/ajs/consulta/guia/documentofb", "POST",
                        data,
                        function(resp) {
                            console.log(resp);
                            if (resp.res) {
                                alertExito("Documento encontrado")
                                vue.productos = resp.productos
                                vue.guia.venta = resp.idventa
                                vue.guia.doc_cli = resp.doc_cliente
                                vue.guia.nom_cli = resp.nom_cliente
                                vue.guia.dir_cli = resp.dir_cliente
                                vue.guia.total = resp.total
                            } else {
                                alertAdvertencia(resp.msg)
                            }
                        }
                    )
                }
            }
        });

        if ($('#cotizacion').val() !== undefined) {

            /* 
                        window.localStorage.removeItem('productosGuiaRemosion');
                    window.localStorage.removeItem('datosGuiaRemosion');
                    window.localStorage.removeItem('datosTransporteGuiaRemosion');
                    window.localStorage.removeItem('datosUbigeoGuiaRemosion'); */
            console.log($('#cotizacion').val());
            let cod = $('#cotizacion').val()
            _ajax("/ajs/guia/remision/coti/" + cod, "POST", {
                    cod
                },
                function(resp) {
                    console.log(resp);
                    appguia._data.productos = resp
                }
            )
            _ajax("/ajs/guia/remision/coti/cliente/" + cod, "POST", {
                    cod
                },
                function(resp) {
                    console.log(resp);
                    /*   appguia._data.productos = resp */
                    appguia._data.guia.nom_cli = resp.datos
                    appguia._data.guia.dir_cli = resp.direccion
                }
            )
        }
        $("#input_documento_clientetr").autocomplete({

            source: _URL + `/ajs/consulta/buscar/dtatranspor`,
            minLength: 1,
            select: function(event, ui) {
                event.preventDefault();
                /*    console.log(item);
                   console.log(ui); */
                console.log(ui.item);
                appguia.transporte.ruc= ui.item.ruc
                appguia.transporte.razon_social= ui.item.razon
            }
        });
        appguia.getDocumentoGuia();
        obtenerProvincias()
    })
</script>
