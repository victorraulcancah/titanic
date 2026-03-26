<?php
$conexion = (new Conexion())->getConexion();

$isAbierta = false;
$cajaid = '';
$id_usuario = isset($_SESSION['usuario_id']) ? $_SESSION['usuario_id'] : (isset($_SESSION['usuario_fac']) ? $_SESSION['usuario_fac'] : 0);

// Verificar si hay una caja abierta del vendedor (solo del día actual)
$fecha_hoy = date('Y-m-d');

// PRIMERO: Cerrar cajas que pasaron más de 12 horas (ANTES de verificar si hay caja abierta)
$sql_cerrar = "UPDATE caja_empresa 
               SET estado='0' 
               WHERE estado='1' 
               AND id_usuario='$id_usuario'
               AND id_empresa='{$_SESSION['id_empresa']}'
               AND sucursal='{$_SESSION['sucursal']}'
               AND TIMESTAMPDIFF(HOUR, fecha, NOW()) > 12";
$conexion->query($sql_cerrar);

// SEGUNDO: Verificar si hay una caja abierta (después del cierre automático)
$sql = "SELECT *, 
        TIMESTAMPDIFF(HOUR, fecha, NOW()) as horas_transcurridas 
        FROM caja_empresa 
        WHERE DATE(fecha)='$fecha_hoy' 
        AND estado='1' 
        AND id_usuario='$id_usuario'
        AND id_empresa='{$_SESSION['id_empresa']}'
        AND sucursal='{$_SESSION['sucursal']}'";

if ($orrr = $conexion->query($sql)->fetch_assoc()) {
    $isAbierta = true;
    $cajaid = $orrr['caja_id'];
    $horas_transcurridas = $orrr['horas_transcurridas'];
}
?>

<div class="page-title-box">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h6 class="page-title">Mi Caja</h6>
            <ol class="breadcrumb m-0">
                <li class="breadcrumb-item"><a href="javascript: void(0);">Vendedor</a></li>
                <li class="breadcrumb-item active" aria-current="page">Mi Caja</li>
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
                <div class="card-title-desc"></div>
                
                <?php if (!$isAbierta) { ?>
                    <div class="text-center">
                        <h3>Abrir Caja hoy</h3>
                        <button data-bs-toggle="modal" data-bs-target="#modal-add-caja" class="btn btn-primary mt-4">Abrir Caja</button>
                    </div>
                    
                    <div class="mt-5">
                        <h5 class="mb-3">Historial de Cajas</h5>
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <label class="form-label">Fecha Inicio</label>
                                <input type="date" v-model="filtroFechaInicio" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Fecha Fin</label>
                                <input type="date" v-model="filtroFechaFin" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-3 d-flex align-items-end">
                                <button @click="filtrarHistorial" class="btn btn-primary btn-sm">
                                    <i class="fas fa-search"></i> Buscar
                                </button>
                                <button @click="limpiarFiltros" class="btn btn-secondary btn-sm ms-2">
                                    <i class="fas fa-times"></i> Limpiar
                                </button>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover table-sm">
                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Detalle</th>
                                        <th>Ingresos</th>
                                        <th>Egresos</th>
                                        <th>Resultado</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="caja in historialFiltrado">
                                        <td>{{caja.fecha}}</td>
                                        <td>{{caja.detalle}}</td>
                                        <td class="text-success">S/ {{parseFloat(caja.entrada || 0).toFixed(2)}}</td>
                                        <td class="text-danger">S/ {{parseFloat(caja.salida || 0).toFixed(2)}}</td>
                                        <td class="fw-bold" :class="(parseFloat(caja.entrada || 0) - parseFloat(caja.salida || 0)) >= 0 ? 'text-success' : 'text-danger'">
                                            S/ {{(parseFloat(caja.entrada || 0) - parseFloat(caja.salida || 0)).toFixed(2)}}
                                        </td>
                                        <td>
                                            <span v-if="caja.estado == '1'" class="badge bg-success">Abierta</span>
                                            <span v-else class="badge bg-secondary">Cerrada</span>
                                        </td>
                                        <td>
                                            <button @click="verDetalleCaja(caja.caja_id)" class="btn btn-info btn-sm">
                                                <i class="fas fa-eye"></i> Ver
                                            </button>
                                        </td>
                                    </tr>
                                    <tr v-if="historialFiltrado.length == 0">
                                        <td colspan="7" class="text-center text-muted">No hay historial de cajas</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
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
                                                <input required v-model="apertura.detalle" type="text" class="form-control" placeholder="Ej: Caja vendedor Juan">
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
                <?php } else { ?>
                    <input type="hidden" value="<?= $cajaid ?>" id="cajacod">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <div class="alert alert-info d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="fas fa-clock"></i> 
                                    <strong>Caja Abierta</strong> - Tiempo transcurrido: <?= $horas_transcurridas ?> hora(s)
                                </div>
                                <div>
                                    <small class="text-muted">La caja se cerrará automáticamente después de 12 horas</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-7">
                            <div class="col-md-12 text-end m-3">
                                <button @click="cerrarCaja" class="btn btn-success">Cerrar Caja</button>
                                <button data-bs-toggle="modal" data-bs-target="#modal-add-gasto" class="btn btn-primary">Agregar</button>
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
                                        <tr v-for="(item,index) in listaMovimientos">
                                            <th>{{index+1}}</th>
                                            <th>{{item.detalle}}</th>
                                            <th>{{item.hora}}</th>
                                            <th>{{item.entrada==0?'-':item.entrada}}</th>
                                            <th>{{item.salida==0?'-':item.salida}}</th>
                                            <th>{{item.metodo==1?'EFECTIVO':item.metodo==2?'TARJETAS':item.metodo==3?'BANCOS':''}}</th>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="row">
                                <div class="col-md-12 mt-3">
                                    <div class="card">
                                        <div class="card-body">
                                            <h5 class="card-title mb-3">Resumen por Método de Pago</h5>
                                            <div class="row mb-3">
                                                <div class="col-md-4 text-center">
                                                    <div class="border rounded p-3 bg-light">
                                                        <i class="fas fa-money-bill-wave text-success font-size-24 mb-2"></i>
                                                        <h6 class="text-muted mb-1">Efectivo</h6>
                                                        <div class="mt-2">
                                                            <small class="text-muted d-block">Ingresos</small>
                                                            <strong class="text-success">S/ {{ingresosEfectivo}}</strong>
                                                        </div>
                                                        <div class="mt-1">
                                                            <small class="text-muted d-block">Egresos</small>
                                                            <strong class="text-danger">S/ {{egresosEfectivo}}</strong>
                                                        </div>
                                                        <hr class="my-2">
                                                        <div>
                                                            <small class="text-muted d-block">Resultado</small>
                                                            <h4 class="mb-0 text-success">S/ {{totalEfectivo}}</h4>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-4 text-center">
                                                    <div class="border rounded p-3 bg-light">
                                                        <i class="fas fa-credit-card text-primary font-size-24 mb-2"></i>
                                                        <h6 class="text-muted mb-1">Tarjetas</h6>
                                                        <div class="mt-2">
                                                            <small class="text-muted d-block">Ingresos</small>
                                                            <strong class="text-success">S/ {{ingresosTarjetas}}</strong>
                                                        </div>
                                                        <div class="mt-1">
                                                            <small class="text-muted d-block">Egresos</small>
                                                            <strong class="text-danger">S/ {{egresosTarjetas}}</strong>
                                                        </div>
                                                        <hr class="my-2">
                                                        <div>
                                                            <small class="text-muted d-block">Resultado</small>
                                                            <h4 class="mb-0 text-primary">S/ {{totalTarjetas}}</h4>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-4 text-center">
                                                    <div class="border rounded p-3 bg-light">
                                                        <i class="fas fa-university text-info font-size-24 mb-2"></i>
                                                        <h6 class="text-muted mb-1">Bancos</h6>
                                                        <div class="mt-2">
                                                            <small class="text-muted d-block">Ingresos</small>
                                                            <strong class="text-success">S/ {{ingresosBancos}}</strong>
                                                        </div>
                                                        <div class="mt-1">
                                                            <small class="text-muted d-block">Egresos</small>
                                                            <strong class="text-danger">S/ {{egresosBancos}}</strong>
                                                        </div>
                                                        <hr class="my-2">
                                                        <div>
                                                            <small class="text-muted d-block">Resultado</small>
                                                            <h4 class="mb-0 text-info">S/ {{totalBancos}}</h4>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="modal fade" id="modal-add-gasto" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="exampleModalLabel">Agregar Movimiento</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <form @submit.prevent="agregarGasto">
                                        <div class="modal-body">
                                            <div class="row">
                                                <div class="col-md-12 mb-3">
                                                    <label class="form-label">Detalle</label>
                                                    <input required v-model="gasto.concepto" type="text" class="form-control" placeholder="Ej: Combustible, Peaje">
                                                </div>
                                                <div class="col-md-12 mb-3">
                                                    <label class="form-label">Tipo</label>
                                                    <select required v-model="gasto.tipo" class="form-control">
                                                        <option value="1">Egreso</option>
                                                        <option value="2">Ingreso</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-12 mb-3">
                                                    <label class="form-label">Monto S/</label>
                                                    <input required @keypress="onlyNumber" v-model="gasto.monto" type="text" class="form-control">
                                                </div>
                                                <div class="col-md-12 mb-3">
                                                    <label class="form-label">Método</label>
                                                    <select required v-model="gasto.metodo" class="form-control">
                                                        <option value="1">EFECTIVO</option>
                                                        <option value="2">TARJETAS</option>
                                                        <option value="3">BANCOS</option>
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
                <?php } ?>
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
            gasto: {
                concepto: "",
                tipo: "1",
                monto: "",
                metodo: "1",
            },
            listaMovimientos: [],
            historial: [],
            filtroFechaInicio: "",
            filtroFechaFin: "",
            egreso: 0,
            ingreso: 0,
        },
        computed: {
            ingresosEfectivo() {
                var total = 0;
                this.listaMovimientos.forEach((el) => {
                    if (el.metodo == 1) {
                        total += parseFloat(el.entrada + "")
                    }
                })
                return parseFloat(total + '').toFixed(2)
            },
            egresosEfectivo() {
                var total = 0;
                this.listaMovimientos.forEach((el) => {
                    if (el.metodo == 1) {
                        total += parseFloat(el.salida + "")
                    }
                })
                return parseFloat(total + '').toFixed(2)
            },
            ingresosTarjetas() {
                var total = 0;
                this.listaMovimientos.forEach((el) => {
                    if (el.metodo == 2) {
                        total += parseFloat(el.entrada + "")
                    }
                })
                return parseFloat(total + '').toFixed(2)
            },
            egresosTarjetas() {
                var total = 0;
                this.listaMovimientos.forEach((el) => {
                    if (el.metodo == 2) {
                        total += parseFloat(el.salida + "")
                    }
                })
                return parseFloat(total + '').toFixed(2)
            },
            ingresosBancos() {
                var total = 0;
                this.listaMovimientos.forEach((el) => {
                    if (el.metodo == 3) {
                        total += parseFloat(el.entrada + "")
                    }
                })
                return parseFloat(total + '').toFixed(2)
            },
            egresosBancos() {
                var total = 0;
                this.listaMovimientos.forEach((el) => {
                    if (el.metodo == 3) {
                        total += parseFloat(el.salida + "")
                    }
                })
                return parseFloat(total + '').toFixed(2)
            },
            egresos() {
                var total = 0;
                this.listaMovimientos.forEach((el) => {
                    total += parseFloat(el.salida + "")
                })
                this.egreso = total;
                return parseFloat(total + '').toFixed(2)
            },
            ingresos() {
                var total = 0;
                this.listaMovimientos.forEach((el) => {
                    total += parseFloat(el.entrada + "")
                })
                this.ingreso = total;
                return parseFloat(total + '').toFixed(2)
            },
            totalEfectivo() {
                var total = 0;
                this.listaMovimientos.forEach((el) => {
                    if (el.metodo == 1) {
                        total += parseFloat(el.entrada + "") - parseFloat(el.salida + "")
                    }
                })
                return parseFloat(total + '').toFixed(2)
            },
            totalTarjetas() {
                var total = 0;
                this.listaMovimientos.forEach((el) => {
                    if (el.metodo == 2) {
                        total += parseFloat(el.entrada + "") - parseFloat(el.salida + "")
                    }
                })
                return parseFloat(total + '').toFixed(2)
            },
            totalBancos() {
                var total = 0;
                this.listaMovimientos.forEach((el) => {
                    if (el.metodo == 3) {
                        total += parseFloat(el.entrada + "") - parseFloat(el.salida + "")
                    }
                })
                return parseFloat(total + '').toFixed(2)
            },
            historialFiltrado() {
                if (!this.filtroFechaInicio && !this.filtroFechaFin) {
                    return this.historial;
                }
                
                return this.historial.filter(caja => {
                    const fechaCaja = caja.fecha;
                    if (this.filtroFechaInicio && this.filtroFechaFin) {
                        return fechaCaja >= this.filtroFechaInicio && fechaCaja <= this.filtroFechaFin;
                    } else if (this.filtroFechaInicio) {
                        return fechaCaja >= this.filtroFechaInicio;
                    } else if (this.filtroFechaFin) {
                        return fechaCaja <= this.filtroFechaFin;
                    }
                    return true;
                });
            },
        },
        methods: {
            cerrarCaja() {
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
                    if (result.isConfirmed) {
                        _post("/ajs/caja/vendedor/cerrar", data, function(resp) {
                            console.log(resp);
                            if (resp.res) {
                                alertExito("Caja Cerrada")
                                    .then(function() {
                                        location.reload()
                                    })
                            } else {
                                alertAdvertencia("No se pudo Cerrar")
                            }
                        })
                    }
                })
            },
            limpiarGasto() {
                this.gasto = {
                    concepto: "",
                    tipo: "1",
                    monto: "",
                    metodo: '1'
                }
            },
            agregarGasto() {
                const data = {
                    detalle: this.gasto.concepto,
                    tipo: this.gasto.tipo,
                    monto: this.gasto.monto,
                    metodo: this.gasto.metodo,
                    caja: $("#cajacod").val(),
                    hora: getTime()
                }
                
                _post("/ajs/caja/vendedor/gasto", data, function(resp) {
                    console.log(resp);
                    if (resp.res) {
                        $("#modal-add-gasto").modal("hide");
                        app.listarMovimientos();
                        app.limpiarGasto()
                    } else {
                        alertAdvertencia("No se pudo agregar")
                    }
                })
            },
            listarMovimientos() {
                _post("/ajs/caja/vendedor/movimientos", {
                    cod: $("#cajacod").val()
                }, function(resp) {
                    app._data.listaMovimientos = resp;
                    console.log(app._data.listaMovimientos);
                })
            },
            cargarHistorial() {
                _post("/ajs/caja/vendedor/historial", {}, function(resp) {
                    app._data.historial = resp;
                    console.log(app._data.historial);
                })
            },
            filtrarHistorial() {
                // El filtrado se hace automáticamente con el computed property
                console.log("Filtrando historial...");
            },
            limpiarFiltros() {
                this.filtroFechaInicio = "";
                this.filtroFechaFin = "";
            },
            verDetalleCaja(cajaId) {
                _post("/ajs/caja/vendedor/movimientos", {
                    cod: cajaId
                }, function(resp) {
                    let movimientos = resp;
                    
                    // Calcular totales por método
                    let totales = {
                        efectivo: {ingresos: 0, egresos: 0, resultado: 0},
                        tarjetas: {ingresos: 0, egresos: 0, resultado: 0},
                        bancos: {ingresos: 0, egresos: 0, resultado: 0}
                    };
                    
                    movimientos.forEach(function(mov) {
                        if (mov.metodo == 1) {
                            totales.efectivo.ingresos += parseFloat(mov.entrada || 0);
                            totales.efectivo.egresos += parseFloat(mov.salida || 0);
                        } else if (mov.metodo == 2) {
                            totales.tarjetas.ingresos += parseFloat(mov.entrada || 0);
                            totales.tarjetas.egresos += parseFloat(mov.salida || 0);
                        } else if (mov.metodo == 3) {
                            totales.bancos.ingresos += parseFloat(mov.entrada || 0);
                            totales.bancos.egresos += parseFloat(mov.salida || 0);
                        }
                    });
                    
                    totales.efectivo.resultado = totales.efectivo.ingresos - totales.efectivo.egresos;
                    totales.tarjetas.resultado = totales.tarjetas.ingresos - totales.tarjetas.egresos;
                    totales.bancos.resultado = totales.bancos.ingresos - totales.bancos.egresos;
                    
                    let html = '<div class="mb-3">';
                    html += '<div class="row">';
                    html += '<div class="col-md-4"><div class="card bg-light"><div class="card-body text-center">';
                    html += '<h6 class="text-muted">EFECTIVO</h6>';
                    html += '<small class="text-success">Ingresos: S/ ' + totales.efectivo.ingresos.toFixed(2) + '</small><br>';
                    html += '<small class="text-danger">Egresos: S/ ' + totales.efectivo.egresos.toFixed(2) + '</small><br>';
                    html += '<hr><strong class="text-success">Resultado: S/ ' + totales.efectivo.resultado.toFixed(2) + '</strong>';
                    html += '</div></div></div>';
                    
                    html += '<div class="col-md-4"><div class="card bg-light"><div class="card-body text-center">';
                    html += '<h6 class="text-muted">TARJETAS</h6>';
                    html += '<small class="text-success">Ingresos: S/ ' + totales.tarjetas.ingresos.toFixed(2) + '</small><br>';
                    html += '<small class="text-danger">Egresos: S/ ' + totales.tarjetas.egresos.toFixed(2) + '</small><br>';
                    html += '<hr><strong class="text-primary">Resultado: S/ ' + totales.tarjetas.resultado.toFixed(2) + '</strong>';
                    html += '</div></div></div>';
                    
                    html += '<div class="col-md-4"><div class="card bg-light"><div class="card-body text-center">';
                    html += '<h6 class="text-muted">BANCOS</h6>';
                    html += '<small class="text-success">Ingresos: S/ ' + totales.bancos.ingresos.toFixed(2) + '</small><br>';
                    html += '<small class="text-danger">Egresos: S/ ' + totales.bancos.egresos.toFixed(2) + '</small><br>';
                    html += '<hr><strong class="text-info">Resultado: S/ ' + totales.bancos.resultado.toFixed(2) + '</strong>';
                    html += '</div></div></div>';
                    html += '</div></div>';
                    
                    html += '<div class="table-responsive"><table class="table table-sm table-bordered">';
                    html += '<thead><tr><th>Detalle</th><th>Hora</th><th>Entrada</th><th>Salida</th><th>Método</th></tr></thead><tbody>';
                    
                    movimientos.forEach(function(mov) {
                        let metodo = mov.metodo == 1 ? 'EFECTIVO' : mov.metodo == 2 ? 'TARJETAS' : 'BANCOS';
                        html += `<tr>
                            <td>${mov.detalle}</td>
                            <td>${mov.hora}</td>
                            <td class="text-success">${mov.entrada == 0 ? '-' : 'S/ ' + parseFloat(mov.entrada).toFixed(2)}</td>
                            <td class="text-danger">${mov.salida == 0 ? '-' : 'S/ ' + parseFloat(mov.salida).toFixed(2)}</td>
                            <td><span class="badge ${mov.metodo == 1 ? 'bg-success' : mov.metodo == 2 ? 'bg-primary' : 'bg-info'}">${metodo}</span></td>
                        </tr>`;
                    });
                    
                    html += '</tbody></table></div>';
                    
                    Swal.fire({
                        title: 'Detalle de Movimientos',
                        html: html,
                        width: '900px',
                        confirmButtonText: 'Cerrar'
                    });
                });
            },
            guardarAperturaCaja() {
                const data = {
                    ...this.apertura,
                    hora: getTime()
                }
                
                _post("/ajs/caja/vendedor/abrir", data, function(resp) {
                    console.log(resp);
                    if (resp.res) {
                        alertExito("Caja Abierta")
                            .then(function() {
                                location.reload();
                            })
                    } else {
                        alertAdvertencia("No se pudo Abrir la Caja")
                    }
                })
            },
            onlyNumber($event) {
                let keyCode = ($event.keyCode ? $event.keyCode : $event.which);
                if ((keyCode < 48 || keyCode > 57) && keyCode !== 46) {
                    $event.preventDefault();
                }
            },
        }
    })

    <?php
    if ($isAbierta) {
        echo "app.listarMovimientos()";
    } else {
        echo "app.cargarHistorial()";
    }
    ?>
})
</script>
