<?php
$conexion = (new Conexion())->getConexion();

$isAbierta = false;
$cajaid = '';

$sql = "SELECT * FROM caja_empresa 
where id_empresa='{$_SESSION['id_empresa']}' and sucursal='{$_SESSION['sucursal']}' and fecha='" . date("Y-m-d") . "' and estado='1'";


if ($orrr = $conexion->query($sql)->fetch_assoc()) {
    $isAbierta = true;
    $cajaid = $orrr['caja_id'];
}

?>
<div class="page-title-box">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h6 class="page-title">Flujo de caja</h6>
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

<div id="container-vue" class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-body">

                <h4 class="card-title"></h4>

                <div class="card-title-desc">

                </div>
                <?php

                if (!$isAbierta) {

                ?>

                    <div class="text-center">
                        <h3>Abrir Caja hoy</h3>
                        <button data-bs-toggle="modal" data-bs-target="#modal-add-caja" class="btn btn-primary mt-4">Abrir Caja</button>
                    </div>
                    <div class="modal fade" id="modal-add-caja" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="exampleModalLabel">Apertura de caja</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <form @submit.prevent="guardarAperturaCaja">
                                    <div class="modal-body">
                                        <div class="row">

                                            <div class="col-md-12 mb-3">
                                                <label class="form-label">Detalle de caja</label>
                                                <input required v-model="apertura.detalle" type="text" class="form-control">
                                            </div>
                                            <div class="col-md-12 mb-3">
                                                <label class="form-label">Monto de apertura</label>
                                                <input required @keypress="onlyNumber" v-model="apertura.monto" type="text" class="form-control">
                                            </div>

                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="submit" class="btn btn-primary">Guardar</button>
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>

                                    </div>
                                </form>

                            </div>
                        </div>
                    </div>
                <?php  } else {  ?>
                    <input type="hidden" value="<?= $cajaid ?>" id="cajacod">
                    <div class="row">
                        <div class="col-md-7">
                            <div class="col-md-12 text-end m-3">
                                <button @click="cerrarCajaChica" class="btn btn-success">Cerrar Caja Chica</button>
                                <a href="<?=URL::to("/reporte/caja/excel/$cajaid")?>" class="btn btn-info">Exportar Caja Chica</a>
                                <button data-bs-toggle="modal" data-bs-target="#modal-add-caja-chica" class="btn btn-primary">Agregar</button>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover table-sm text-center">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Detalle</th>
                                            <th>Hora</th>
                                            <th>Entrada</th>
                                            <th>Salida</th>
                                            <th>Metodo</th>

                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(item,index) in listaCajaChic">
                                            <th>{{index+1}}</th>
                                            <th>{{item.detalle}}</th>
                                            <th>{{item.hora}}</th>
                                            <th>{{item.entrada==0?'-':item.entrada}}</th>
                                            <th>{{item.salida==0?'-':item.salida}}</th>
                                            <th>{{item.metodo==1?'EFECTIVO':item.metodo==2? 'TARJETAS':item.metodo==3?'TRANSFERENCIAS' : ''}}</th>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card bg-primary">
                                        <div class="card-body">
                                            <div class="text-center text-white py-4">
                                                <h3 class="mb-4 text-white-50 font-size-16">Ingreso</h3>
                                                <h1>S/ {{ingresos}}</h1>
                                                <p class="font-size-14 pt-1">Total</p>
                                                <p class="text-white-50 mb-0"> </p>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                                <div class="col-md-6">
                                    <div class="card bg-danger">
                                        <div class="card-body">
                                            <div class="text-center text-white py-4">
                                                <h3 class="mb-4 text-white-50 font-size-16">Egreso</h3>
                                                <h1>S/ {{egresos}}</h1>
                                                <p class="font-size-14 pt-1">Total</p>
                                                <p class="text-white-50 mb-0"></p>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>

                        <div class="modal fade" id="modal-add-caja-chica" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="exampleModalLabel">Apertura de caja</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <form @submit.prevent="agregarCajaChica">
                                        <div class="modal-body">
                                            <div class="row">

                                                <div class="col-md-12 mb-3">
                                                    <label class="form-label">Detalle </label>
                                                    <input required v-model="movimiento.detalle" type="text" class="form-control">
                                                </div>
                                                <div class="col-md-12 mb-3">
                                                    <label class="form-label">Tipo</label>
                                                    <select required v-model="movimiento.tipo" class="form-control">
                                                        <option value="1">Egreso</option>
                                                        <option value="2">Ingreso</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-12 mb-3">
                                                    <label class="form-label">Monto S/</label>
                                                    <input required @keypress="onlyNumber" v-model="movimiento.monto" type="text" class="form-control">
                                                </div>
                                                <div class="col-md-12 mb-3">
                                                    <label class="form-label">Metodo</label>
                                                    <select required v-model="movimiento.metodo" class="form-control">
                                                        <option value="1">EFECTIVO</option>
                                                        <option value="2">TARJETAS</option>
                                                        <option value="3">TRANSFERENCIAS</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="submit" class="btn btn-primary">Guardar</button>
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>

                                        </div>
                                    </form>

                                </div>
                            </div>
                        </div>
                    </div>

                <?php
                }

                ?>



            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {

        const app = new Vue({
            el: "#container-vue",
            data: {
                apertura: {
                    detalle: "",
                    monto: "",
                },
                movimiento: {
                    detalle: "",
                    tipo: "1",
                    monto: "",
                    metodo: "1",
                },
                listaCajaChic: [],
                egreso: 0,
                ingreso: 0,
            },
            computed: {
                egresos() {
                    var total = 0;
                    this.listaCajaChic.forEach((el) => {
                        total += parseFloat(el.salida + "")
                    })
                    this.egreso = total;
                    return parseFloat(total + '').toFixed(2)
                },
                ingresos() {
                    var total = 0;
                    this.listaCajaChic.forEach((el) => {
                        total += parseFloat(el.entrada + "")
                    })
                    this.ingreso = total;
                    return parseFloat(total + '').toFixed(2)
                },
            },
            methods: {

                cerrarCajaChica() {
                    const data = {}
                    data.egreso = this.egreso
                    data.ingreso = this.ingreso
                    data.caja = $("#cajacod").val();
                    data.hora = getTime();

                    Swal.fire({
                        title: '¿Desea cerrar la caja?',
                        showDenyButton: false,
                        showCancelButton: true,
                        confirmButtonText: 'Cerrar Caja',
                        denyButtonText: `cancelar`,
                    }).then((result) => {
                        /* Read more about isConfirmed, isDenied below */
                        if (result.isConfirmed) {
                            _post("/ajs/caja/chica/cerrar", data,
                                function(resp) {
                                    console.log(resp);
                                    if (resp.res) {
                                        alertExito("Caja Cerrada")
                                            .then(function() {
                                                location.reload()
                                            })
                                    } else {
                                        alertAdvertencia("No se pudo Cerrar")
                                    }
                                }
                            )
                        }
                    })
                },
                lismpiarMovimiento() {
                    this.movimiento = {
                        detalle: "",
                        tipo: "1",
                        monto: "",
                        metodo: '1'
                    }
                },
                agregarCajaChica() {
                    const data = {
                        ...this.movimiento
                    }
                    data.caja = $("#cajacod").val();
                    data.hora = getTime();
                    _post("/ajs/caja/chica/add", data,
                        function(resp) {
                            console.log(resp);
                            if (resp.res) {
                                $("#modal-add-caja-chica").modal("hide");
                                app.listarCajaChica();
                                app.lismpiarMovimiento()

                            } else {
                                alertAdvertencia("No se pudo agregar")
                            }
                        }
                    )
                },
                listarCajaChica() {
                    _post("/ajs/caja/apertura/listar", {
                            cod: $("#cajacod").val()
                        },
                        function(resp) {
                           
                           /*  resp.forEach(element => {
                                if (Object.keys(element).some(key => key === 'salida') == false) {
                                    let salidaAdd = element.salida = 0
                                }
                            });
 */
                            app._data.listaCajaChic = resp;
                            console.log(app._data.listaCajaChic);
                        }
                    )
                },
                guardarAperturaCaja() {
                    const data = {
                        ...this.apertura
                    }
                    data.hora = getTime();
                    _post("/ajs/caja/apertura", data,
                        function(resp) {
                            console.log(resp);
                            if (resp.res) {
                                alertExito("Caja Abierta")
                                    .then(function() {
                                        location.reload();
                                    })
                            } else {
                                alertAdvertencia("No se pudo Abrir la Caja")
                            }
                        }
                    )
                },
                onlyNumber($event) {
                    //console.log($event.keyCode); //keyCodes value
                    let keyCode = ($event.keyCode ? $event.keyCode : $event.which);
                    if ((keyCode < 48 || keyCode > 57) && keyCode !== 46) { // 46 is dot
                        $event.preventDefault();
                    }
                },
            }
        })

        <?php

        if ($isAbierta) {
            echo "app.listarCajaChica()";
        }
        ?>
    })
</script>