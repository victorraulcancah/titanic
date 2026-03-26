<?php
require_once "app/http/controllers/VentasController.php";

$c_venta = new VentasController();
$getAll = $c_venta->ingresosEgresosRender();
?>
<style>
    .ui-autocomplete {
        z-index: 1065;
    }
</style>
<script src="<?= URL::to('public/js/qrCode.min.js') ?>"></script>
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
        <!-- <div class="col-md-4">
            <div class="float-end d-none d-md-block">

            </div>
        </div> -->
    </div>
</div>
<div class="row" id="container-vue">
    <div class="row">
        <div class="col-12">
            <div class="card" style="border-radius:20px;box-shadow:0 4px 6px -1px rgba(0,0,0,.1),0 2px 4px -1px rgba(0,0,0,.06)">
                <div class="card-header">
                    <div class="clearfix">
                        <h4 class="card-title float-start">Intercambio de Productos</h4>
                        <button data-bs-toggle="modal" data-bs-target="#nuevoIngreso" class="btn btn-success float-end" @click="btnCerrar"><i class="fa fa-plus"></i> Nuevo Ingreso</button>
                        <button data-bs-toggle="modal" data-bs-target="#nuevaSalida" class="btn btn-primary float-end" @click="btnCerrar"><i class="fa fa-plus"></i> Nueva Salida</button>
                    </div>
                    <!-- <div class="row">
                        <div class="col-md-6">
                            <h4 class="card-title">Intercambio de Productos</h4>
                        </div>
                        <div class="col-md-6 text-end">
                            <button data-bs-toggle="modal" data-bs-target="#nuevoIngreso" class="btn btn-success" @click="btnCerrar"><i class="fa fa-plus"></i> Nuevo Ingreso</button>
                            <button data-bs-toggle="modal" data-bs-target="#nuevaSalida" class="btn btn-primary" @click="btnCerrar"><i class="fa fa-plus"></i> Nueva Salida</button>
                        </div>
                    </div> -->
                </div>
                <div class="card-body">
                    <table id="datatable" class="table table-bordered dt-responsive nowrap text-center table-sm table-hover" style="border-collapse: collapse; border-spacing: 0; width: 100%;">
                        <thead>
                            <tr>
                                <th></th>
                                <th>Producto</th>
                                <th>Cantidad</th>
                                <th>Tipo</th>
                                <th>Usuario</th>
                                <th>Egreso</th>
                                <th>Ingreso</th>
                                <th>Confirmar Taslado</th>
                                <th>Reporte</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            foreach ($getAll  as $row) :
                            ?>
                                <tr>
                                    <td><?php echo $row['intercambio_id'] ?></td>
                                    <td><?php echo $row['codigo'] ?> | <?php echo $row['descripcion'] ?></td>
                                    <td><?php echo $row['cantidad'] ?></td>
                                    <?php if ($row['tipo'] == 'i') {
                                        $tipo = 'Ingreso';
                                    } else {
                                        $tipo = 'Salida';
                                    } ?>
                                    <td><?php echo $tipo ?></td>
                                    <td><?php echo $row["usuario"] ?></td>
                                    <td> <?php echo $row['almacen_egreso'] == 2 ? "Tienda 1" : "Almacén 1"  ?></td>
                                    <td> <?php echo $row['almacen_ingreso'] == 2 ? "Tienda 1" : "Almacén 1" ?></td>
                                    <td class="text-center" width="auto">
                                        <?php
                                        if ($row['tipo'] == 'e' && $row['estado'] == '0') : ?>
                                            <button data-item="<?= $row['intercambio_id'] ?>" class="btn-confirmar btn btn-sm btn-success"> <i class="fa fa-check"></i></button>
                                        <?php endif ?>
                                    </td>
                                    <td class="text-center" width="auto">



                                        <a target="_blank" href="<?= URL::to('reporte/ingresos/egresos/' . $row['intercambio_id'] . '') ?>" class="btn-reporte btn btn-sm btn-primary"> <i class="fa fa-file"></i></a>

                                    </td>

                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <!-- <div class="form-group row mb-3">
    <label class="col-lg-2 control-label">Buscar</label>
    <div class="col-lg-10">
        <input type="text" placeholder="Consultar Productos" class="form-control ui-autocomplete-input" id="input_buscar_productos" autocomplete="off">
    </div>
</div>

 -->

    <div class="modal fade " id="nuevoIngreso" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Nuevo Ingreso</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form v-on:submit.prevent="addIngreso" class="form-horizontal">
                    <div class="modal-body">
                        <div class="row">
                            <canvas hidden="" id="qr-canvas2" v-show="toggleCamara2" style="width: 300px; padding: 10px;"></canvas>
                            <div class="mb-3 col-md-12">
                                <label class=""> <input id="btn-scan-qr2" v-model="usar_scaner2" @click="toggleCamara2" type="checkbox"> Usar Scanner</label><br />
                            </div>
                            <div class="mb-3 col-md-12">
                                <label class="control-label">Producto</label>
                                <input type="text" placeholder="Consultar Productos" class="form-control ui-autocomplete-input" id="input_buscar_productos" autocomplete="off">

                            </div>
                            <div class="mb-3">
                                <label class="control-label">Descripción</label>
                                <input required v-model="producto.descripcion" type="text" placeholder="Descripcion" class="form-control" readonly="true">
                            </div>
                            <div class="mb-3 col-md-3">
                                <label class="control-label">Cantidad</label>
                                <input required v-model="producto.cantidad" type="text" class="form-control" @keypress="onlyNumber">
                            </div>
                            <div class="mb-3 col-md-6">
                                <label class="control-label">Ingreso Almacén</label>
                                <select name="almacen" id="almacen" v-model="producto.almacen" class="form-control" @change="onChangeAlmacen($event)">
                                    <option value="1">Almacen 1</option>
                                    <option value="2">Tienda 1</option>
                                </select>
                            </div>
                            <div class="mb-3 col-md-3">
                                <label class="control-label">Stock Act.</label>
                                <input v-model="producto.stock" type="text" class="form-control" readonly="true">
                            </div>

                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Guardar</button>
                        <button type="button" class="btn btn-secondary" @click="btnCerrar" data-bs-dismiss="modal">Cerrar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="modal fade " id="nuevaSalida" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Nueva Salida</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <form v-on:submit.prevent="addSalida" class="form-horizontal">
                    <div class="modal-body">
                        <div class="row">
                            <canvas hidden="" id="qr-canvas" v-show="toggleCamara" style="width: 300px; padding: 10px;"></canvas>
                            <div class="mb-3 col-md-12">
                                <label class=""> <input id="btn-scan-qr" v-model="usar_scaner" @click="toggleCamara" type="checkbox"> Usar Scanner</label><br />
                            </div>
                            <div class="mb-3 col-md-12">
                                <label class="control-label">Producto</label>
                                <input type="text" placeholder="Consultar Productos" class="form-control ui-autocomplete-input" id="input_buscar_productos_salida" autocomplete="off">
                            </div>
                            <div class="mb-3 col-md-6">
                                <label class="control-label">Del Almacén</label>
                                <select name="delAlmacen" id="delAlmacen" v-model="producto.almacen" class="form-control" @change="onChangeAlmacen($event)">
                                    <option v-if="producto.almacen==1" value="1">Almacén 1</option>
                                    <option v-else value="2">Tienda 1</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="control-label">Descripcion</label>
                                <input required v-model="producto.descripcion" type="text" placeholder="Descripcion" class="form-control" readonly="true">
                            </div>
                            <div class="mb-3 col-md-3">
                                <label class="control-label">Cantidad</label>
                                <input required v-model="producto.cantidad" type="text" class="form-control" @keypress="onlyNumber">
                            </div>
                            <div class="mb-3 col-md-6">
                                <label class="control-label">Al Almacén</label>
                                <select name="" id="" v-model="producto.alAlmacen" class="form-control">
                                    <option v-if="producto.almacen==1" value="2" selected>Tienda 1</option>
                                    <option v-else value="1">Almacén 1</option>


                                </select>
                            </div>
                            <div class="mb-3 col-md-3">
                                <label class="control-label">Stock Act.</label>
                                <input v-model="producto.stock" type="text" class="form-control" readonly="true">
                            </div>

                        </div>
                    </div>
                    <div class="modal-footer">
                        <button id="btnguardarSalida" type="submit" class="btn btn-primary">Guardar</button>
                        <button type="button" class="btn btn-secondary" @click="btnCerrar" data-bs-dismiss="modal">Cerrar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>

<script>
    $(document).ready(function() {

        const app = new Vue({
            el: "#container-vue",
            data: {
                usar_scaner: false,
                usar_scaner2: false,
                producto: {
                    productoid: "",
                    cantidad: "",
                    stock: "0",
                    codigo: "",
                    almacen: "1",
                    alAlmacen: "2",
                    tipo: '',
                    descripcion: ''
                },
            },
            methods: {
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

                                                // Llenar el campo y luego activar el autocompletado
                                                app.producto.productoid = data.data[0].id_producto
                                                app.producto.descripcion = data.data[0].id_producto + " | " + data.data[0].descripcion
                                                app.producto.cantidad = 0
                                                app.producto.stock = data.data[0].cantidad

                                                app.producto.codigo = data.data[0].codigo
                                                app.producto.almacen = data.data[0].almacen

                                                $('#input_buscar_productos').val("");
                                                $('#almacen').prop("disabled", false);
                                                $('#delAlmacen').prop("disabled", false);
                                                _ajax("/ajs/consulta/stock/almacen", "POST", {
                                                        almacen: app.producto.almacen,
                                                        producto: app.producto.productoid
                                                    },
                                                    function(resp) {
                                                        if (resp == null) {
                                                            app.producto.stock = 0
                                                        } else {
                                                            app.producto.stock = data.data[0].cantidad
                                                        }
                                                    }
                                                )
                                                $("#input_buscar_productos_salida").val("");
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
                toggleCamara2() {
                    if (!app.usar_scaner) {
                        app.encenderCamara2();
                    } else {
                        app.cerrarCamara2();
                    }
                },
                encenderCamara2() {
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
                            const canvasElement = document.getElementById("qr-canvas2");
                            const canvas = canvasElement.getContext("2d");
                            const btnScanQR = document.getElementById("btn-scan-qr2");
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

        
                                                
                                                app.producto.productoid = data.data[0].id_producto
                                                app.producto.descripcion = data.data[0].id_producto + " | " + data.data[0].descripcion
                                                app.producto.cantidad = 0
                                                app.producto.stock = data.data[0].cantidad

                                                app.producto.codigo = data.data[0].codigo
                                                app.producto.almacen = data.data[0].almacen

                                                $('#input_buscar_productos').val("");
                                                $('#almacen').prop("disabled", false);
                                                $('#delAlmacen').prop("disabled", false);
                                                _ajax("/ajs/consulta/stock/almacen", "POST", {
                                                        almacen: app.producto.almacen,
                                                        producto: app.producto.productoid
                                                    },
                                                    function(resp) {
                                                        if (resp == null) {
                                                            app.producto.stock = 0
                                                        } else {
                                                            app.producto.stock = ui.item.cnt
                                                        }
                                                    }
                                                )
                                                $("#input_buscar_productos_salida").val("");
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
                                }

                            };
                        });
                },
                cerrarCamara2() {
                    // Lógica para apagar la cámara
                    //this.camaraEncendida = false;
                    app.usar_scaner = false; // Actualiza el estado de escaneo
                    const video = document.querySelector("video");
                    const canvasElement = document.getElementById("qr-canvas2");
                    const canvas = canvasElement.getContext("2d");


                    if (video && video.srcObject) {
                        video.srcObject.getTracks().forEach((track) => {
                            track.stop();
                        });
                    }
                    document.getElementById("btn-scan-qr2").checked = false;
                    canvasElement.hidden = true;
                },
                btnCerrar() {
                    this.producto = {
                        productoid: "",
                        descripcion: "",
                        cantidad: "",
                        stock: "0",
                        codigo: "",
                        almacen: "1",
                        alAlmacen: "1",
                    }
                },
                limpiasDatos() {
                    this.producto = {
                        productoid: "",
                        descripcion: "",
                        cantidad: "",
                        stock: "0",
                        codigo: "",

                    }
                },
                addIngreso() {
                    //if (this.producto.stock)
                    if (this.producto.descripcion.length > 0) {
                        const data = {
                            ...this.producto
                        }
                        data.tipo = 'i'
                        _ajax("/ajs/ingreso/almacen/add", "POST",
                            data,
                            function(resp) {
                                console.log(resp);
                                if (resp.res) {
                                    alertExito('Bien', "Registro Correcto").then(function() {
                                        location.reload()
                                    });
                                } else {
                                    alertAdvertencia("No se pudo Guardar la Venta")
                                }
                            }
                        )
                    } else {
                        alertAdvertencia("Busque un producto primero")
                            .then(function() {
                                setTimeout(function() {
                                    $("#input_buscar_productos").focus();
                                }, 500)
                            })
                    }

                },
                addSalida() {
                    $("#btnguardarSalida").prop('disabled', true)
                    //if (this.producto.stock)
                    if (this.producto.descripcion.length > 0 && this.producto.stock > 0) {
                        const data = {
                            ...this.producto
                        }
                        data.tipo = 'e'

                        console.log(data);
                        /*  return */
                        _ajax("/ajs/egreso/almacen/add", "POST",
                            data,
                            function(resp) {
                                console.log(resp);
                                if (resp.res) {
                                    alertExito('Bien', "Registro Correcto").then(function() {
                                        location.reload()
                                    });
                                } else {
                                    alertAdvertencia("No se pudo Guardar la Venta")
                                }
                            }
                        )
                    } else {
                        alertAdvertencia("Busque un producto primero o verifique stock")
                            .then(function() {
                                setTimeout(function() {
                                    $("#input_buscar_productos").focus();
                                }, 500)
                            })
                    }

                },
                onChangeAlmacen(event) {
                    console.log(event.target.value)
                    if (this.producto.almacen == 1) {
                        this.producto.alAlmacen = 2
                    } else {
                        this.producto.alAlmacen = 1
                    }

                    if (this.producto.descripcion !== '' && this.producto.descripcion != undefined) {
                        console.log(this.producto.descripcion);
                        /*   return */
                        this.producto.almacen = event.target.value
                        var self = this
                        _ajax("/ajs/consulta/stock/almacen", "POST", {
                                almacen: event.target.value,
                                producto: app.producto.productoid
                            },
                            function(resp) {

                                console.log(resp);
                                /* return */
                                if (resp == null) {
                                    app.producto.stock = 0
                                } else {
                                    app.producto.stock = resp.cantidad
                                }
                            }
                        )
                    }

                },
                onlyNumber($event) {

                    let keyCode = ($event.keyCode ? $event.keyCode : $event.which);
                    if ((keyCode < 48 || keyCode > 57) && keyCode !== 46) { // 46 is dot
                        $event.preventDefault();
                    }
                },
            },
        })
        $("#input_buscar_productos").autocomplete({
            source: _URL + "/ajs/cargar/productos",
            minLength: 1,
            appendTo: $("#nuevoIngreso"),
            select: function(event, ui) {
                event.preventDefault();
                console.log(ui.item);
                app.producto.productoid = ui.item.codigo
                app.producto.descripcion = ui.item.codigo + " | " + ui.item.descripcion

                app.producto.cantidad = 0
                app.producto.stock = ui.item.cnt

                app.producto.codigo = ui.item.codigo

                $('#input_buscar_productos').val("");
                $('#almacen').prop("disabled", false);
                $('#delAlmacen').prop("disabled", false);
                _ajax("/ajs/consulta/stock/almacen", "POST", {
                        almacen: app.producto.almacen,
                        producto: app.producto.productoid
                    },
                    function(resp) {
                        if (resp == null) {
                            app.producto.stock = 0
                        } else {
                            app.producto.stock = ui.item.cnt
                        }
                    }
                )
            }
        });
        $("#input_buscar_productos_salida").autocomplete({
            source: _URL + "/ajs/cargar/productos",
            minLength: 1,
            appendTo: $("#nuevaSalida"),
            select: function(event, ui) {
                event.preventDefault();
                console.log(ui.item);
                app.producto.productoid = ui.item.codigo
                app.producto.descripcion = ui.item.codigo + " | " + ui.item.descripcion
                app.producto.cantidad = 0
                app.producto.stock = ui.item.cnt

                app.producto.codigo = ui.item.codigo
                app.producto.almacen = ui.item.almacen

                $('#input_buscar_productos').val("");
                $('#almacen').prop("disabled", false);
                $('#delAlmacen').prop("disabled", false);
                _ajax("/ajs/consulta/stock/almacen", "POST", {
                        almacen: app.producto.almacen,
                        producto: app.producto.productoid
                    },
                    function(resp) {
                        if (resp == null) {
                            app.producto.stock = 0
                        } else {
                            app.producto.stock = ui.item.cnt
                        }
                    }
                )
            }
        });
        $("#datatable").DataTable({
            order: [
                [0, "ASC"]
            ],
            scrollX: true,

        })

        $("#datatable").on("click", ".btn-confirmar", function(evt) {
            const cod = $(evt.currentTarget).attr("data-item");
            /*   console.log(cod); */
            Swal.fire({
                title: 'Desea confirmar el traslado?',
                showDenyButton: true,
                confirmButtonText: 'Si',
                denyButtonText: `No`,
            }).then((result) => {
                /* Read more about isConfirmed, isDenied below */
                if (result.isConfirmed) {
                    _ajax("/ajs/confirmar/traslado", "POST", {
                            cod
                        },
                        function(resp) {
                            console.log(resp);
                            if (resp.res) {
                                /*   localStorage.removeItem('idChecks'); */
                                Swal.fire('Buen trabajo',
                                    'Traslado Exitoso',
                                    'success', {}).then((result) => {

                                    location.reload();
                                });
                            } else {
                                alertAdvertencia("Ocurrio un error")
                            }
                        }
                    )
                    /*   */
                }
            })
        })
    })
</script>