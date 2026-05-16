<?php
$conexion = (new Conexion())->getConexion();
?>

<div class="page-title-box">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h6 class="page-title">Arqueo Diario de Caja</h6>
        </div>
    </div>
</div>

<div id="app-arqueo">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <!-- Selector de fecha y botón cargar -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <label class="form-label"><strong>Fecha de Arqueo</strong></label>
                            <input type="date" class="form-control" v-model="fecha" :value="fechaHoy">
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button @click="cargarCobros" class="btn btn-primary w-100">
                                <i class="fa fa-sync"></i> Cargar Cobros del Día
                            </button>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button @click="verRegistrosGuardados" class="btn btn-info w-100">
                                <i class="fa fa-history"></i> Ver Registros Guardados
                            </button>
                        </div>
                    </div>

                    <!-- Tabla de cobros por vendedor -->
                    <div v-if="cobrosLoaded" class="mb-4">
                        <h5 class="mb-3">Cobros del Día</h5>
                        <table class="table table-bordered table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>Usuario</th>
                                    <th class="text-end">Efectivo</th>
                                    <th class="text-end">Bancos</th>
                                    <th class="text-end">Total</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="vendedor in resumenVendedores" :key="vendedor.usuario">
                                    <td><strong>{{ vendedor.usuario }}</strong></td>
                                    <td class="text-end">{{ formatMoney(vendedor.efectivo) }}</td>
                                    <td class="text-end">{{ formatMoney(vendedor.bancos) }}</td>
                                    <td class="text-end"><strong>{{ formatMoney(vendedor.total) }}</strong></td>
                                    <td class="text-center">
                                        <!-- Si no tiene arqueo guardado, mostrar botón Cuadrar -->
                                        <button v-if="!vendedor.arqueo" @click="abrirModalCuadre(vendedor)" class="btn btn-success btn-sm">
                                            <i class="fa fa-calculator"></i> Cuadrar
                                        </button>
                                        <!-- Si ya tiene arqueo guardado, mostrar botones Ver y Editar -->
                                        <div v-else class="btn-group btn-group-sm">
                                            <button @click="verArqueoVendedor(vendedor)" 
                                                    :class="vendedor.arqueo.cuadra_efectivo == 1 && vendedor.arqueo.cuadra_bancos == 1 ? 'btn btn-info btn-sm' : 'btn btn-danger btn-sm'" 
                                                    title="Ver">
                                                <i class="fa fa-eye"></i>
                                            </button>
                                            <button @click="editarArqueoVendedor(vendedor)" class="btn btn-warning btn-sm" title="Editar">
                                                <i class="fa fa-edit"></i>
                                            </button>
                                            <button @click="eliminarArqueoVendedor(vendedor.arqueo.arqueo_id)" class="btn btn-danger btn-sm" title="Eliminar">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                            <span class="badge ms-1" :class="vendedor.arqueo.cuadra_efectivo == 1 && vendedor.arqueo.cuadra_bancos == 1 ? 'bg-success' : 'bg-danger'">
                                                <i :class="vendedor.arqueo.cuadra_efectivo == 1 && vendedor.arqueo.cuadra_bancos == 1 ? 'fa fa-check' : 'fa fa-times'"></i>
                                            </span>
                                        </div>
                                    </td>
                                </tr>
                                <tr class="table-secondary">
                                    <td><strong>TOTAL GENERAL</strong></td>
                                    <td class="text-end"><strong>{{ formatMoney(totalEfectivo) }}</strong></td>
                                    <td class="text-end"><strong>{{ formatMoney(totalBancos) }}</strong></td>
                                    <td class="text-end"><strong>{{ formatMoney(totalGeneral) }}</strong></td>
                                    <td></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Cuadre de Caja -->
    <div class="modal fade" id="modalCuadreCaja" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="fa fa-calculator"></i> 
                        <span v-if="!modoEdicion && arqueoSeleccionado">Ver Arqueo</span>
                        <span v-else-if="modoEdicion && arqueoSeleccionado">Editar Arqueo</span>
                        <span v-else>Cuadrar Caja</span>
                        - {{ vendedorSeleccionado ? vendedorSeleccionado.usuario : '' }} - {{ fecha }}
                    </h5>
                    <button type="button" class="close text-white" @click="cerrarModal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- Nueva estructura con dos columnas -->
                    <div class="row">
                        <!-- COLUMNA IZQUIERDA: CONTROL DE EFECTIVO -->
                        <div class="col-md-6">
                            <div class="card border-primary">
                                <div class="card-header bg-primary text-white">
                                    <h6 class="mb-0"><i class="fa fa-money-bill"></i> CONTROL DE EFECTIVO</h6>
                                </div>
                                <div class="card-body">
                                    <!-- Ingresos en efectivo -->
                                    <h6 class="text-success"><i class="fa fa-arrow-down"></i> Efectivo que trae:</h6>
                                    <div class="row mb-2">
                                        <div class="col-6">
                                            <label class="form-label small">Billetes:</label>
                                            <input type="number" step="0.01" class="form-control form-control-sm" 
                                                   v-model.number="detalleEfectivo.billetes" 
                                                   :disabled="!modoEdicion && arqueoSeleccionado">
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label small">Monedas:</label>
                                            <input type="number" step="0.01" class="form-control form-control-sm" 
                                                   v-model.number="detalleEfectivo.monedas" 
                                                   :disabled="!modoEdicion && arqueoSeleccionado">
                                        </div>
                                    </div>
                                    
                                    <hr>
                                    
                                    <!-- Gastos en efectivo -->
                                    <h6 class="text-danger"><i class="fa fa-arrow-up"></i> Gastos realizados:</h6>
                                    <div class="row mb-2">
                                        <div class="col-6">
                                            <label class="form-label small">Pasaje:</label>
                                            <input type="number" step="0.01" class="form-control form-control-sm" 
                                                   v-model.number="detalleEfectivo.pasaje" 
                                                   :disabled="!modoEdicion && arqueoSeleccionado">
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label small">Combustible:</label>
                                            <input type="number" step="0.01" class="form-control form-control-sm" 
                                                   v-model.number="detalleEfectivo.combustible" 
                                                   :disabled="!modoEdicion && arqueoSeleccionado">
                                        </div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-6">
                                            <label class="form-label small">Gastos:</label>
                                            <input type="number" step="0.01" class="form-control form-control-sm" 
                                                   v-model.number="detalleEfectivo.gastos" 
                                                   :disabled="!modoEdicion && arqueoSeleccionado">
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label small">Menú:</label>
                                            <input type="number" step="0.01" class="form-control form-control-sm" 
                                                   v-model.number="detalleEfectivo.menu" 
                                                   :disabled="!modoEdicion && arqueoSeleccionado">
                                        </div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-12">
                                            <label class="form-label small">Otro:</label>
                                            <div class="input-group input-group-sm">
                                                <input type="number" step="0.01" class="form-control" 
                                                       v-model.number="detalleEfectivo.otro" 
                                                       :disabled="!modoEdicion && arqueoSeleccionado">
                                                <input type="text" class="form-control" placeholder="Descripción" 
                                                       v-model="detalleEfectivo.otro_descripcion" 
                                                       :disabled="!modoEdicion && arqueoSeleccionado">
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <hr>
                                    
                                    <!-- Resumen de efectivo -->
                                    <table class="table table-sm table-bordered">
                                        <tr class="table-light">
                                            <td><strong>TOTAL EFECTIVO REAL</strong></td>
                                            <td class="text-end"><strong>{{ formatMoney(totalEfectivoReal) }}</strong></td>
                                        </tr>
                                        <tr class="table-info">
                                            <td><strong>SISTEMA (Debe traer)</strong></td>
                                            <td class="text-end"><strong>{{ formatMoney(debeTraerEfectivo) }}</strong></td>
                                        </tr>
                                        <tr :class="{'table-success': cuadraEfectivo, 'table-danger': !cuadraEfectivo}">
                                            <td><strong>DIFERENCIA EFECTIVO</strong></td>
                                            <td class="text-end"><strong>{{ formatMoney(Math.abs(diferenciaEfectivo)) }}</strong></td>
                                        </tr>
                                    </table>
                                    
                                    <div class="alert alert-sm" :class="{'alert-success': cuadraEfectivo, 'alert-danger': !cuadraEfectivo}">
                                        <strong>{{ mensajeEfectivo }}</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- COLUMNA DERECHA: PAGOS DIGITALES -->
                        <div class="col-md-6">
                            <div class="card border-info">
                                <div class="card-header bg-info text-white">
                                    <h6 class="mb-0"><i class="fa fa-mobile-alt"></i> PAGOS DIGITALES (Yape/Plin/Transf)</h6>
                                </div>
                                <div class="card-body">
                                    <!-- Formulario para agregar pago digital -->
                                    <div v-if="modoEdicion || !arqueoSeleccionado" class="border p-2 mb-3 bg-light">
                                        <h6 class="small mb-2">Agregar Pago Digital:</h6>
                                        <div class="row mb-2">
                                            <div class="col-12">
                                                <input type="text" 
                                                       class="form-control form-control-sm" 
                                                       placeholder="Buscar cliente..." 
                                                       v-model="nuevoPago.cliente_nombre"
                                                       @input="buscarClientesDebounce"
                                                       list="listaClientes">
                                                <datalist id="listaClientes">
                                                    <option v-for="cliente in clientesEncontrados" :key="cliente.id" :value="cliente.nombre">
                                                        {{ cliente.documento }} - {{ cliente.nombre }}
                                                    </option>
                                                </datalist>
                                            </div>
                                        </div>
                                        <div class="row mb-2">
                                            <div class="col-6">
                                                <select class="form-control form-control-sm" v-model="nuevoPago.tipo_pago">
                                                    <option value="Yape">Yape</option>
                                                    <option value="Plin">Plin</option>
                                                    <option value="Transferencia">Transferencia</option>
                                                </select>
                                            </div>
                                            <div class="col-6">
                                                <input type="text" class="form-control form-control-sm" 
                                                       placeholder="N° Operación" 
                                                       v-model="nuevoPago.numero_operacion">
                                            </div>
                                        </div>
                                        <div class="row mb-2">
                                            <div class="col-8">
                                                <input type="number" step="0.01" class="form-control form-control-sm" 
                                                       placeholder="Monto" 
                                                       v-model.number="nuevoPago.monto">
                                            </div>
                                            <div class="col-4">
                                                <button @click="agregarPagoDigital" class="btn btn-success btn-sm w-100">
                                                    <i class="fa fa-plus"></i> Agregar
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Lista de pagos digitales -->
                                    <div style="max-height: 300px; overflow-y: auto;">
                                        <table class="table table-sm table-bordered">
                                            <thead class="table-secondary">
                                                <tr>
                                                    <th>Cliente / Operación</th>
                                                    <th class="text-end">Monto</th>
                                                    <th v-if="modoEdicion || !arqueoSeleccionado" class="text-center" style="width: 50px;">Acc</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr v-for="(pago, index) in pagosDigitales" :key="index">
                                                    <td class="small">
                                                        <strong>{{ pago.cliente_nombre }}</strong><br>
                                                        <span class="badge bg-light text-dark border">{{ pago.tipo_pago }}</span> 
                                                        <input v-if="modoEdicion || !arqueoSeleccionado" type="text" 
                                                               class="form-control form-control-sm d-inline-block" 
                                                               style="width: 120px; height: 22px; font-size: 10px;"
                                                               placeholder="N° Operación" 
                                                               v-model="pago.numero_operacion">
                                                        <span v-else>Op: {{ pago.numero_operacion }}</span>
                                                    </td>
                                                    <td class="text-end">{{ formatMoney(pago.monto) }}</td>
                                                    <td v-if="modoEdicion || !arqueoSeleccionado" class="text-center">
                                                        <button @click="eliminarPagoDigital(index)" class="btn btn-danger btn-sm">
                                                            <i class="fa fa-trash"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                                <tr v-if="pagosDigitales.length === 0">
                                                    <td colspan="3" class="text-center text-muted small">No hay pagos digitales registrados</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                    
                                    <hr>
                                    
                                    <!-- Resumen de pagos digitales -->
                                    <table class="table table-sm table-bordered">
                                        <tr class="table-light">
                                            <td><strong>TOTAL DIGITAL REAL</strong></td>
                                            <td class="text-end"><strong>{{ formatMoney(totalPagosDigitales) }}</strong></td>
                                        </tr>
                                        <tr class="table-info">
                                            <td><strong>SISTEMA (Bancos)</strong></td>
                                            <td class="text-end"><strong>{{ formatMoney(debeTraerBancos) }}</strong></td>
                                        </tr>
                                        <tr :class="{'table-success': cuadraBancos, 'table-danger': !cuadraBancos}">
                                            <td><strong>DIFERENCIA BANCOS</strong></td>
                                            <td class="text-end"><strong>{{ formatMoney(Math.abs(diferenciaBancos)) }}</strong></td>
                                        </tr>
                                    </table>
                                    
                                    <div class="alert alert-sm" :class="{'alert-success': cuadraBancos, 'alert-danger': !cuadraBancos}">
                                        <strong>{{ mensajeBancos }}</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button v-if="!arqueoSeleccionado" @click="guardarArqueo" class="btn btn-success btn-lg">
                        <i class="fa fa-save"></i> Guardar Arqueo del Día
                    </button>
                    <button v-if="modoEdicion && arqueoSeleccionado" @click="actualizarArqueo" class="btn btn-warning btn-lg">
                        <i class="fa fa-edit"></i> Actualizar Arqueo
                    </button>
                    <button type="button" class="btn btn-secondary" @click="cerrarModal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Arqueos Guardados -->
    <div class="modal fade" id="modalArqueosGuardados" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title">
                        <i class="fa fa-history"></i> Arqueos Guardados
                    </h5>
                    <button type="button" class="close text-white" @click="cerrarModalGuardados" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <table class="table table-bordered table-hover table-sm">
                        <thead class="table-dark">
                            <tr>
                                <th>#</th>
                                <th>Fecha</th>
                                <th>Vendedor</th>
                                <th>Cobró Efectivo</th>
                                <th>Cobró Bancos</th>
                                <th>Trajo Efectivo</th>
                                <th>Gastó Efectivo</th>
                                <th>Diferencia</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(arqueo, index) in arqueosGuardados" :key="arqueo.arqueo_id">
                                <td>{{ index + 1 }}</td>
                                <td>{{ arqueo.fecha_arqueo }}</td>
                                <td><strong>{{ arqueo.vendedor }}</strong></td>
                                <td class="text-end">{{ formatMoney(arqueo.cobros_efectivo) }}</td>
                                <td class="text-end">{{ formatMoney(arqueo.cobros_bancos) }}</td>
                                <td class="text-end">{{ formatMoney(arqueo.ingresos_efectivo) }}</td>
                                <td class="text-end">{{ formatMoney(arqueo.egresos_efectivo) }}</td>
                                <td class="text-end" :class="{'text-success': Math.abs(arqueo.diferencia_efectivo) < 0.01, 'text-danger': Math.abs(arqueo.diferencia_efectivo) >= 0.01}">
                                    {{ formatMoney(Math.abs(arqueo.diferencia_efectivo)) }}
                                </td>
                                <td class="text-center">
                                    <span v-if="arqueo.cuadra_efectivo == 1" class="badge bg-success">
                                        <i class="fa fa-check"></i> Cuadra
                                    </span>
                                    <span v-else class="badge bg-danger">
                                        <i class="fa fa-times"></i> No Cuadra
                                    </span>
                                </td>
                                <td class="text-center">
                                    <button @click="verArqueo(arqueo)" class="btn btn-info btn-sm" title="Ver">
                                        <i class="fa fa-eye"></i>
                                    </button>
                                    <button @click="editarArqueo(arqueo)" class="btn btn-warning btn-sm" title="Editar">
                                        <i class="fa fa-edit"></i>
                                    </button>
                                    <button @click="eliminarArqueoVendedor(arqueo.arqueo_id)" class="btn btn-danger btn-sm" title="Eliminar">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                            <tr v-if="arqueosGuardados.length === 0">
                                <td colspan="10" class="text-center text-muted">No hay arqueos guardados</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" @click="cerrarModalGuardados">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    new Vue({
        el: '#app-arqueo',
        data: {
            fecha: '<?php echo date("Y-m-d"); ?>',
            cobrosLoaded: false,
            resumenVendedores: [],
            vendedorSeleccionado: null,
            arqueosGuardados: [],
            arqueoSeleccionado: null,
            modoEdicion: false,
            ingresos: {
                efectivo: 0,
                bancos: 0
            },
            egresos: {
                efectivo: 0,
                bancos: 0
            },
            // Nuevos datos para detalle de efectivo
            detalleEfectivo: {
                billetes: '',
                monedas: '',
                pasaje: '',
                combustible: '',
                gastos: '',
                menu: '',
                otro: '',
                otro_descripcion: ''
            },
            // Nuevos datos para pagos digitales
            pagosDigitales: [],
            nuevoPago: {
                cliente_nombre: '',
                tipo_pago: 'Yape',
                numero_operacion: '',
                monto: ''
            },
            clientesEncontrados: [],
            buscarClientesTimeout: null
        },
        computed: {
            fechaHoy() {
                return '<?php echo date("Y-m-d"); ?>';
            },
            totalEfectivo() {
                return this.resumenVendedores.reduce((sum, v) => sum + v.efectivo, 0);
            },
            totalBancos() {
                return this.resumenVendedores.reduce((sum, v) => sum + v.bancos, 0);
            },
            totalGeneral() {
                return this.totalEfectivo + this.totalBancos;
            },
            // Valores del vendedor seleccionado
            efectivoVendedor() {
                return this.vendedorSeleccionado ? this.vendedorSeleccionado.efectivo : 0;
            },
            bancosVendedor() {
                return this.vendedorSeleccionado ? this.vendedorSeleccionado.bancos : 0;
            },
            // Cálculos de efectivo con detalle
            totalIngresosEfectivo() {
                return parseFloat(this.detalleEfectivo.billetes || 0) + parseFloat(this.detalleEfectivo.monedas || 0);
            },
            totalGastosEfectivo() {
                return parseFloat(this.detalleEfectivo.pasaje || 0) + 
                       parseFloat(this.detalleEfectivo.combustible || 0) + 
                       parseFloat(this.detalleEfectivo.gastos || 0) + 
                       parseFloat(this.detalleEfectivo.menu || 0) + 
                       parseFloat(this.detalleEfectivo.otro || 0);
            },
            totalEfectivoReal() {
                return this.totalIngresosEfectivo + this.totalGastosEfectivo;
            },
            debeTraerEfectivo() {
                return this.efectivoVendedor;
            },
            debeTraerBancos() {
                return this.bancosVendedor;
            },
            diferenciaEfectivo() {
                return this.totalEfectivoReal - this.debeTraerEfectivo;
            },
            // Cálculos de pagos digitales
            totalPagosDigitales() {
                return this.pagosDigitales.reduce((sum, pago) => sum + parseFloat(pago.monto || 0), 0);
            },
            diferenciaBancos() {
                return this.totalPagosDigitales - this.debeTraerBancos;
            },
            cuadraEfectivo() {
                return Math.abs(this.diferenciaEfectivo) < 0.01;
            },
            cuadraBancos() {
                return Math.abs(this.diferenciaBancos) < 0.01;
            },
            mensajeEfectivo() {
                if (this.cuadraEfectivo) return '✓ CUADRA PERFECTO';
                if (this.diferenciaEfectivo > 0) return '⚠ SOBRA S/ ' + Math.abs(this.diferenciaEfectivo).toFixed(2);
                return '⚠ FALTA S/ ' + Math.abs(this.diferenciaEfectivo).toFixed(2);
            },
            mensajeBancos() {
                if (this.cuadraBancos) return '✓ CUADRA PERFECTO';
                if (this.diferenciaBancos > 0) return '⚠ SOBRA S/ ' + Math.abs(this.diferenciaBancos).toFixed(2);
                return '⚠ FALTA S/ ' + Math.abs(this.diferenciaBancos).toFixed(2);
            }
        },
        methods: {
            formatMoney(value) {
                return 'S/ ' + parseFloat(value || 0).toFixed(2);
            },
            cerrarModal() {
                $('#modalCuadreCaja').modal('hide');
                // Limpiar datos
                this.vendedorSeleccionado = null;
                this.arqueoSeleccionado = null;
                this.modoEdicion = false;
                this.ingresos = { efectivo: 0, bancos: 0 };
                this.egresos = { efectivo: 0, bancos: 0 };
                this.detalleEfectivo = {
                    billetes: '',
                    monedas: '',
                    pasaje: '',
                    combustible: '',
                    gastos: '',
                    menu: '',
                    otro: '',
                    otro_descripcion: ''
                };
                this.pagosDigitales = [];
                this.nuevoPago = {
                    cliente_nombre: '',
                    tipo_pago: 'Yape',
                    numero_operacion: '',
                    monto: ''
                };
            },
            cerrarModalGuardados() {
                $('#modalArqueosGuardados').modal('hide');
            },
            cargarCobros() {
                if (!this.fecha) {
                    Swal.fire('Error', 'Selecciona una fecha', 'warning');
                    return;
                }
                
                _post('/ajs/arqueo/cobros/dia', { fecha: this.fecha }, (response) => {
                    this.resumenVendedores = response;
                    this.cobrosLoaded = true;
                    this.ingresos = { efectivo: 0, bancos: 0 };
                    this.egresos = { efectivo: 0, bancos: 0 };
                    
                    // Cargar arqueos guardados para verificar si ya hay cuadres
                    this.cargarArqueosDelDia();
                });
            },
            cargarArqueosDelDia() {
                _post('/ajs/arqueo/listar', {}, (response) => {
                    // Filtrar solo los arqueos de la fecha seleccionada
                    const arqueosFecha = response.filter(a => a.fecha_arqueo === this.fecha);
                    
                    // Asociar arqueos con vendedores usando usuario_id
                    this.resumenVendedores.forEach(vendedor => {
                        const arqueo = arqueosFecha.find(a => 
                            a.vendedor_id == vendedor.usuario_id || 
                            a.vendedor.trim() === vendedor.usuario.trim()
                        );
                        // Usar Vue.set para asegurar reactividad
                        this.$set(vendedor, 'arqueo', arqueo || null);
                        
                        // Debug: ver los valores
                        if (arqueo) {
                            console.log('Arqueo de ' + vendedor.usuario + ':', {
                                cuadra_efectivo: arqueo.cuadra_efectivo,
                                cuadra_bancos: arqueo.cuadra_bancos
                            });
                        }
                    });
                });
            },
            abrirModalCuadre(vendedor) {
                this.vendedorSeleccionado = vendedor;
                this.modoEdicion = false;
                this.arqueoSeleccionado = null;
                this.ingresos = { efectivo: 0, bancos: 0 };
                this.egresos = { efectivo: 0, bancos: 0 };
                this.detalleEfectivo = {
                    billetes: '',
                    monedas: '',
                    pasaje: '',
                    combustible: '',
                    gastos: '',
                    menu: '',
                    otro: '',
                    otro_descripcion: ''
                };
                // Pre-cargar pagos digitales del sistema
                this.pagosDigitales = (vendedor.pagos_digitales_sistema || []).map(p => ({
                    cliente_nombre: p.cliente_nombre,
                    tipo_pago: p.tipo_pago,
                    numero_operacion: '', // El usuario completará esto manualmente
                    monto: p.monto
                }));

                this.nuevoPago = {
                    cliente_nombre: '',
                    tipo_pago: 'Yape',
                    numero_operacion: '',
                    monto: ''
                };
                $('#modalCuadreCaja').modal('show');
            },
            agregarPagoDigital() {
                if (!this.nuevoPago.cliente_nombre || !this.nuevoPago.numero_operacion || !this.nuevoPago.monto) {
                    Swal.fire('Error', 'Complete todos los campos del pago digital', 'warning');
                    return;
                }
                
                this.pagosDigitales.push({
                    cliente_nombre: this.nuevoPago.cliente_nombre,
                    tipo_pago: this.nuevoPago.tipo_pago,
                    numero_operacion: this.nuevoPago.numero_operacion,
                    monto: parseFloat(this.nuevoPago.monto)
                });
                
                // Limpiar formulario
                this.nuevoPago = {
                    cliente_nombre: '',
                    tipo_pago: 'Yape',
                    numero_operacion: '',
                    monto: ''
                };
            },
            eliminarPagoDigital(index) {
                this.pagosDigitales.splice(index, 1);
            },
            buscarClientesDebounce() {
                clearTimeout(this.buscarClientesTimeout);
                this.buscarClientesTimeout = setTimeout(() => {
                    this.buscarClientes();
                }, 300);
            },
            buscarClientes() {
                const termino = this.nuevoPago.cliente_nombre;
                if (termino.length < 2) {
                    this.clientesEncontrados = [];
                    return;
                }
                
                _post('/ajs/arqueo/buscar-clientes', { termino: termino }, (response) => {
                    this.clientesEncontrados = response;
                });
            },
            calcular() {
                // Trigger computed properties
            },
            guardarArqueo() {
                if (!this.vendedorSeleccionado) {
                    Swal.fire('Error', 'No hay vendedor seleccionado', 'error');
                    return;
                }
                
                // Si estamos en modo edición, actualizar en lugar de guardar
                if (this.modoEdicion && this.arqueoSeleccionado) {
                    this.actualizarArqueo();
                    return;
                }
                
                const data = {
                    fecha: this.fecha,
                    vendedor: this.vendedorSeleccionado.usuario,
                    vendedor_id: this.vendedorSeleccionado.usuario_id,
                    cobros_efectivo: this.efectivoVendedor,
                    cobros_bancos: this.bancosVendedor,
                    ingresos_efectivo: this.totalEfectivoReal,
                    ingresos_bancos: this.totalPagosDigitales,
                    egresos_efectivo: this.totalGastosEfectivo,
                    egresos_bancos: 0,
                    diferencia_efectivo: this.diferenciaEfectivo,
                    diferencia_bancos: this.diferenciaBancos,
                    cuadra_efectivo: this.cuadraEfectivo,
                    cuadra_bancos: this.cuadraBancos,
                    detalle_efectivo: JSON.stringify(this.detalleEfectivo),
                    pagos_digitales: JSON.stringify(this.pagosDigitales)
                };
                
                _post('/ajs/arqueo/guardar', data, (response) => {
                    if (response.res) {
                        Swal.fire('Éxito', 'Cuadre de ' + this.vendedorSeleccionado.usuario + ' guardado', 'success')
                            .then(() => {
                                $('#modalCuadreCaja').modal('hide');
                                this.cargarCobros(); // Recargar para actualizar los botones
                            });
                    } else {
                        Swal.fire('Error', response.mensaje, 'error');
                    }
                });
            },
            verRegistrosGuardados() {
                // Cargar arqueos guardados
                _post('/ajs/arqueo/listar', {}, (response) => {
                    this.arqueosGuardados = response;
                    $('#modalArqueosGuardados').modal('show');
                });
            },
            verArqueoVendedor(vendedor) {
                // Ver arqueo desde la tabla principal
                if (vendedor.arqueo) {
                    // CORREGIDO: Pasar también los datos actuales del vendedor
                    // para que el modal use los valores correctos de la tabla
                    const arqueo = vendedor.arqueo;
                    
                    this.modoEdicion = false;
                    this.arqueoSeleccionado = arqueo;
                    
                    // Usar los valores actuales de la tabla, no los guardados
                    this.vendedorSeleccionado = {
                        usuario: vendedor.usuario,
                        usuario_id: vendedor.usuario_id,
                        efectivo: vendedor.efectivo,  // Valor actual de la tabla
                        bancos: vendedor.bancos        // Valor actual de la tabla
                    };
                    
                    this.ingresos = {
                        efectivo: parseFloat(arqueo.ingresos_efectivo),
                        bancos: parseFloat(arqueo.ingresos_bancos)
                    };
                    this.egresos = {
                        efectivo: parseFloat(arqueo.egresos_efectivo),
                        bancos: parseFloat(arqueo.egresos_bancos)
                    };
                    this.fecha = arqueo.fecha_arqueo;
                    
                    // Cargar detalle completo del arqueo
                    _post('/ajs/arqueo/obtener', { arqueo_id: arqueo.arqueo_id }, (response) => {
                        if (response.detalle_efectivo) {
                            this.detalleEfectivo = {
                                billetes: parseFloat(response.detalle_efectivo.billetes || 0),
                                monedas: parseFloat(response.detalle_efectivo.monedas || 0),
                                pasaje: parseFloat(response.detalle_efectivo.pasaje || 0),
                                combustible: parseFloat(response.detalle_efectivo.combustible || 0),
                                gastos: parseFloat(response.detalle_efectivo.gastos || 0),
                                menu: parseFloat(response.detalle_efectivo.menu || 0),
                                otro: parseFloat(response.detalle_efectivo.otro || 0),
                                otro_descripcion: response.detalle_efectivo.otro_descripcion || ''
                            };
                        } else {
                            this.detalleEfectivo = {
                                billetes: parseFloat(arqueo.ingresos_efectivo || 0),
                                monedas: 0,
                                pasaje: 0,
                                combustible: 0,
                                gastos: parseFloat(arqueo.egresos_efectivo || 0),
                                menu: 0,
                                otro: 0,
                                otro_descripcion: 'Arqueo antiguo sin detalle'
                            };
                        }
                        
                        if (response.pagos_digitales && response.pagos_digitales.length > 0) {
                            this.pagosDigitales = response.pagos_digitales.map(p => ({
                                cliente_nombre: p.cliente_nombre,
                                tipo_pago: p.tipo_pago,
                                numero_operacion: p.numero_operacion,
                                monto: parseFloat(p.monto)
                            }));
                        } else {
                            if (parseFloat(arqueo.ingresos_bancos || 0) > 0) {
                                this.pagosDigitales = [{
                                    cliente_nombre: 'Pagos digitales (sin detalle)',
                                    tipo_pago: 'Transferencia',
                                    numero_operacion: 'N/A',
                                    monto: parseFloat(arqueo.ingresos_bancos)
                                }];
                            } else {
                                this.pagosDigitales = [];
                            }
                        }
                    });
                    
                    $('#modalCuadreCaja').modal('show');
                }
            },
            editarArqueoVendedor(vendedor) {
                // Editar arqueo desde la tabla principal
                if (vendedor.arqueo) {
                    // CORREGIDO: Pasar también los datos actuales del vendedor
                    // para que el modal use los valores correctos de la tabla
                    const arqueo = vendedor.arqueo;
                    
                    this.modoEdicion = true;
                    this.arqueoSeleccionado = arqueo;
                    
                    // Usar los valores actuales de la tabla, no los guardados
                    this.vendedorSeleccionado = {
                        usuario: vendedor.usuario,
                        usuario_id: vendedor.usuario_id,
                        efectivo: vendedor.efectivo,  // Valor actual de la tabla
                        bancos: vendedor.bancos        // Valor actual de la tabla
                    };
                    
                    this.ingresos = {
                        efectivo: parseFloat(arqueo.ingresos_efectivo),
                        bancos: parseFloat(arqueo.ingresos_bancos)
                    };
                    this.egresos = {
                        efectivo: parseFloat(arqueo.egresos_efectivo),
                        bancos: parseFloat(arqueo.egresos_bancos)
                    };
                    this.fecha = arqueo.fecha_arqueo;
                    
                    // Cargar detalle completo del arqueo
                    _post('/ajs/arqueo/obtener', { arqueo_id: arqueo.arqueo_id }, (response) => {
                        if (response.detalle_efectivo) {
                            this.detalleEfectivo = {
                                billetes: parseFloat(response.detalle_efectivo.billetes || 0),
                                monedas: parseFloat(response.detalle_efectivo.monedas || 0),
                                pasaje: parseFloat(response.detalle_efectivo.pasaje || 0),
                                combustible: parseFloat(response.detalle_efectivo.combustible || 0),
                                gastos: parseFloat(response.detalle_efectivo.gastos || 0),
                                menu: parseFloat(response.detalle_efectivo.menu || 0),
                                otro: parseFloat(response.detalle_efectivo.otro || 0),
                                otro_descripcion: response.detalle_efectivo.otro_descripcion || ''
                            };
                        }
                        
                        if (response.pagos_digitales) {
                            this.pagosDigitales = response.pagos_digitales.map(p => ({
                                cliente_nombre: p.cliente_nombre,
                                tipo_pago: p.tipo_pago,
                                numero_operacion: p.numero_operacion,
                                monto: parseFloat(p.monto)
                            }));
                        }
                    });
                    
                    $('#modalCuadreCaja').modal('show');
                }
            },
            eliminarArqueoVendedor(arqueo_id) {
                Swal.fire({
                    title: '¿Estás seguro?',
                    text: "Se eliminará el cuadre de caja de este vendedor.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.value) {
                        _post('/ajs/arqueo/eliminar', { arqueo_id: arqueo_id }, (response) => {
                            if (response.res) {
                                Swal.fire('Eliminado', response.mensaje, 'success').then(() => {
                                    this.cargarCobros();
                                    if ($('#modalArqueosGuardados').hasClass('show')) {
                                        this.verRegistrosGuardados();
                                    }
                                });
                            } else {
                                Swal.fire('Error', response.mensaje, 'error');
                            }
                        });
                    }
                });
            },
            verArqueo(arqueo) {
                // Cargar datos del arqueo en modo solo lectura
                this.modoEdicion = false;
                this.arqueoSeleccionado = arqueo;
                
                // CORREGIDO: Buscar los valores actuales del vendedor en resumenVendedores
                // en lugar de usar los valores guardados del arqueo
                const vendedorActual = this.resumenVendedores.find(v => 
                    v.usuario_id == arqueo.vendedor_id || 
                    v.usuario.trim() === arqueo.vendedor.trim()
                );
                
                // Si encontramos al vendedor en la tabla actual, usar esos valores
                // Si no, usar los valores guardados del arqueo (fallback)
                this.vendedorSeleccionado = {
                    usuario: arqueo.vendedor,
                    usuario_id: arqueo.vendedor_id,
                    efectivo: vendedorActual ? vendedorActual.efectivo : parseFloat(arqueo.cobros_efectivo),
                    bancos: vendedorActual ? vendedorActual.bancos : parseFloat(arqueo.cobros_bancos)
                };
                
                this.ingresos = {
                    efectivo: parseFloat(arqueo.ingresos_efectivo),
                    bancos: parseFloat(arqueo.ingresos_bancos)
                };
                this.egresos = {
                    efectivo: parseFloat(arqueo.egresos_efectivo),
                    bancos: parseFloat(arqueo.egresos_bancos)
                };
                this.fecha = arqueo.fecha_arqueo;
                
                // Cargar detalle completo del arqueo
                _post('/ajs/arqueo/obtener', { arqueo_id: arqueo.arqueo_id }, (response) => {
                    if (response.detalle_efectivo) {
                        this.detalleEfectivo = {
                            billetes: parseFloat(response.detalle_efectivo.billetes || 0),
                            monedas: parseFloat(response.detalle_efectivo.monedas || 0),
                            pasaje: parseFloat(response.detalle_efectivo.pasaje || 0),
                            combustible: parseFloat(response.detalle_efectivo.combustible || 0),
                            gastos: parseFloat(response.detalle_efectivo.gastos || 0),
                            menu: parseFloat(response.detalle_efectivo.menu || 0),
                            otro: parseFloat(response.detalle_efectivo.otro || 0),
                            otro_descripcion: response.detalle_efectivo.otro_descripcion || ''
                        };
                    } else {
                        // Si no hay detalle (arqueos antiguos), mostrar el total en billetes
                        this.detalleEfectivo = {
                            billetes: parseFloat(arqueo.ingresos_efectivo || 0),
                            monedas: 0,
                            pasaje: 0,
                            combustible: 0,
                            gastos: parseFloat(arqueo.egresos_efectivo || 0),
                            menu: 0,
                            otro: 0,
                            otro_descripcion: 'Arqueo antiguo sin detalle'
                        };
                    }
                    
                    if (response.pagos_digitales && response.pagos_digitales.length > 0) {
                        this.pagosDigitales = response.pagos_digitales.map(p => ({
                            cliente_nombre: p.cliente_nombre,
                            tipo_pago: p.tipo_pago,
                            numero_operacion: p.numero_operacion,
                            monto: parseFloat(p.monto)
                        }));
                    } else {
                        // Si no hay pagos digitales pero hay ingresos en bancos, crear uno genérico
                        if (parseFloat(arqueo.ingresos_bancos || 0) > 0) {
                            this.pagosDigitales = [{
                                cliente_nombre: 'Pagos digitales (sin detalle)',
                                tipo_pago: 'Transferencia',
                                numero_operacion: 'N/A',
                                monto: parseFloat(arqueo.ingresos_bancos)
                            }];
                        } else {
                            this.pagosDigitales = [];
                        }
                    }
                });
                
                $('#modalArqueosGuardados').modal('hide');
                $('#modalCuadreCaja').modal('show');
            },
            editarArqueo(arqueo) {
                // Cargar datos del arqueo en modo edición
                this.modoEdicion = true;
                this.arqueoSeleccionado = arqueo;
                
                // CORREGIDO: Buscar los valores actuales del vendedor en resumenVendedores
                // en lugar de usar los valores guardados del arqueo
                const vendedorActual = this.resumenVendedores.find(v => 
                    v.usuario_id == arqueo.vendedor_id || 
                    v.usuario.trim() === arqueo.vendedor.trim()
                );
                
                // Si encontramos al vendedor en la tabla actual, usar esos valores
                // Si no, usar los valores guardados del arqueo (fallback)
                this.vendedorSeleccionado = {
                    usuario: arqueo.vendedor,
                    usuario_id: arqueo.vendedor_id,
                    efectivo: vendedorActual ? vendedorActual.efectivo : parseFloat(arqueo.cobros_efectivo),
                    bancos: vendedorActual ? vendedorActual.bancos : parseFloat(arqueo.cobros_bancos)
                };
                
                this.ingresos = {
                    efectivo: parseFloat(arqueo.ingresos_efectivo),
                    bancos: parseFloat(arqueo.ingresos_bancos)
                };
                this.egresos = {
                    efectivo: parseFloat(arqueo.egresos_efectivo),
                    bancos: parseFloat(arqueo.egresos_bancos)
                };
                this.fecha = arqueo.fecha_arqueo;
                
                // Cargar detalle completo del arqueo
                _post('/ajs/arqueo/obtener', { arqueo_id: arqueo.arqueo_id }, (response) => {
                    if (response.detalle_efectivo) {
                        this.detalleEfectivo = {
                            billetes: parseFloat(response.detalle_efectivo.billetes || 0),
                            monedas: parseFloat(response.detalle_efectivo.monedas || 0),
                            pasaje: parseFloat(response.detalle_efectivo.pasaje || 0),
                            combustible: parseFloat(response.detalle_efectivo.combustible || 0),
                            gastos: parseFloat(response.detalle_efectivo.gastos || 0),
                            menu: parseFloat(response.detalle_efectivo.menu || 0),
                            otro: parseFloat(response.detalle_efectivo.otro || 0),
                            otro_descripcion: response.detalle_efectivo.otro_descripcion || ''
                        };
                    }
                    
                    if (response.pagos_digitales) {
                        this.pagosDigitales = response.pagos_digitales.map(p => ({
                            cliente_nombre: p.cliente_nombre,
                            tipo_pago: p.tipo_pago,
                            numero_operacion: p.numero_operacion,
                            monto: parseFloat(p.monto)
                        }));
                    }
                });
                
                $('#modalArqueosGuardados').modal('hide');
                $('#modalCuadreCaja').modal('show');
            },
            actualizarArqueo() {
                if (!this.arqueoSeleccionado) {
                    Swal.fire('Error', 'No hay arqueo seleccionado', 'error');
                    return;
                }
                
                const data = {
                    arqueo_id: this.arqueoSeleccionado.arqueo_id,
                    ingresos_efectivo: this.totalEfectivoReal,
                    ingresos_bancos: this.totalPagosDigitales,
                    egresos_efectivo: this.totalGastosEfectivo,
                    egresos_bancos: 0,
                    diferencia_efectivo: this.diferenciaEfectivo,
                    diferencia_bancos: this.diferenciaBancos,
                    cuadra_efectivo: this.cuadraEfectivo,
                    cuadra_bancos: this.cuadraBancos,
                    detalle_efectivo: JSON.stringify(this.detalleEfectivo),
                    pagos_digitales: JSON.stringify(this.pagosDigitales)
                };
                
                _post('/ajs/arqueo/actualizar', data, (response) => {
                    if (response.res) {
                        Swal.fire('Éxito', 'Arqueo actualizado correctamente', 'success')
                            .then(() => {
                                $('#modalCuadreCaja').modal('hide');
                                this.modoEdicion = false;
                                this.arqueoSeleccionado = null;
                                this.cargarCobros(); // Recargar para actualizar los botones
                            });
                    } else {
                        Swal.fire('Error', response.mensaje, 'error');
                    }
                });
            }
        }
    });
});
</script>
