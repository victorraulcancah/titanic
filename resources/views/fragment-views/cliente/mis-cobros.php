<div class="page-title-box">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h6 class="page-title">Mis Cobros</h6>
            <ol class="breadcrumb m-0">
                <li class="breadcrumb-item"><a href="javascript: void(0);">Facturacion</a></li>
                <li class="breadcrumb-item active" aria-current="page">Mis Cobros</li>
            </ol>
        </div>
    </div>
</div>

<div id="container-vue" class="row">
    <div class="col-12">
        <div class="card" style="border-radius:20px;box-shadow:0 4px 6px -1px rgba(0,0,0,.1),0 2px 4px -1px rgba(0,0,0,.06)">
            <div class="card-header" style="background-color: #f8f9fa;">
                <h5 class="mb-3" style="font-weight: 600;">Reporte de Cobranzas</h5>
                
                <!-- Filtros -->
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Fecha inicio</label>
                        <input type="date" v-model="filtros.fecha_inicio" class="form-control" :value="fechaHoy">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Fecha fin</label>
                        <input type="date" v-model="filtros.fecha_fin" class="form-control" :value="fechaHoy">
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button @click="buscarCobros" class="btn btn-primary w-100">
                            <i class="fa fa-search"></i> Buscar
                        </button>
                    </div>
                </div>
            </div>

            <div class="card-body">
                <!-- Resumen de totales (solo se muestra despu¨¦s de buscar) -->
                <div v-if="mostrarResultados" class="row mb-4">
                    <div class="col-md-4">
                        <div class="card text-white" style="background: linear-gradient(135deg, #2c5282 0%, #2a4365 100%); border: none; border-radius: 15px;">
                            <div class="card-body text-center py-4">
                                <h6 class="text-white mb-2" style="font-weight: 500; opacity: 0.9;">Efectivo</h6>
                                <h2 class="text-white mb-0" style="font-weight: 700;">S/ {{totalEfectivo}}</h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-white" style="background: linear-gradient(135deg, #38a169 0%, #2f855a 100%); border: none; border-radius: 15px;">
                            <div class="card-body text-center py-4">
                                <h6 class="text-white mb-2" style="font-weight: 500; opacity: 0.9;">Otros Metodos</h6>
                                <h2 class="text-white mb-0" style="font-weight: 700;">S/ {{totalOtros}}</h2>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card text-white" style="background: linear-gradient(135deg, #2d3748 0%, #1a202c 100%); border: none; border-radius: 15px;">
                            <div class="card-body text-center py-4">
                                <h6 class="text-white mb-2" style="font-weight: 500; opacity: 0.9;">Total General</h6>
                                <h2 class="text-white mb-0" style="font-weight: 700;">S/ {{totalGeneral}}</h2>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tabla de cobros -->
                <div v-if="mostrarResultados" class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead style="background-color: #f8f9fa;">
                            <tr>
                                <th class="text-center" style="font-weight: 600;">#</th>
                                <th class="text-center" style="font-weight: 600;">Fecha</th>
                                <th class="text-center" style="font-weight: 600;">Hora</th>
                                <th class="text-center" style="font-weight: 600;">Documento</th>
                                <th style="font-weight: 600;">Cliente</th>
                                <th class="text-center" style="font-weight: 600;">Monto</th>
                                <th class="text-center" style="font-weight: 600;">Metodo de Pago</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="listaCobros.length === 0">
                                <td colspan="7" class="text-center text-muted py-5">
                                    <i class="fa fa-info-circle fa-2x mb-2"></i>
                                    <p class="mb-0">No hay cobros en el rango de fechas seleccionado</p>
                                </td>
                            </tr>
                            <tr v-for="(item, index) in listaCobros" :key="index">
                                <td class="text-center">{{index + 1}}</td>
                                <td class="text-center">{{formatearFecha(item.fecha_pago_real)}}</td>
                                <td class="text-center">{{formatearHora(item.fecha_pago_real)}}</td>
                                <td class="text-center" style="font-weight: 500;">{{item.documento}}</td>
                                <td>{{item.cliente}}</td>
                                <td class="text-end" style="font-weight: 600; color: #28a745;">S/ {{parseFloat(item.monto).toFixed(2)}}</td>
                                <td class="text-center">
                                    <span :class="getBadgeClass(item.tipo_pago)" style="padding: 6px 12px; border-radius: 20px; font-size: 0.85rem;">
                                        {{item.tipo_pago || 'Efectivo'}}
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Mensaje inicial -->
                <div v-if="!mostrarResultados" class="text-center py-5">
                    <i class="fa fa-search fa-3x text-muted mb-3"></i>
                    <p class="text-muted">Selecciona un rango de fechas y presiona "Buscar" para ver tus cobros</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        const app = new Vue({
            el: "#container-vue",
            data: {
                filtros: {
                    fecha_inicio: '<?php echo date("Y-m-d"); ?>',
                    fecha_fin: '<?php echo date("Y-m-d"); ?>'
                },
                listaCobros: [],
                mostrarResultados: false
            },
            computed: {
                fechaHoy() {
                    return '<?php echo date("Y-m-d"); ?>';
                },
                totalEfectivo() {
                    let total = 0;
                    this.listaCobros.forEach((cobro) => {
                        if (!cobro.tipo_pago || cobro.tipo_pago === 'Efectivo') {
                            total += parseFloat(cobro.monto);
                        }
                    });
                    return total.toFixed(2);
                },
                totalOtros() {
                    let total = 0;
                    this.listaCobros.forEach((cobro) => {
                        if (cobro.tipo_pago && cobro.tipo_pago !== 'Efectivo') {
                            total += parseFloat(cobro.monto);
                        }
                    });
                    return total.toFixed(2);
                },
                totalGeneral() {
                    let total = 0;
                    this.listaCobros.forEach((cobro) => {
                        total += parseFloat(cobro.monto);
                    });
                    return total.toFixed(2);
                }
            },
            methods: {
                buscarCobros() {
                    if (!this.filtros.fecha_inicio || !this.filtros.fecha_fin) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Atenci¨®n',
                            text: 'Debes seleccionar ambas fechas'
                        });
                        return;
                    }

                    _post("/ajs/cobros/vendedor/rango", this.filtros,
                        function(resp) {
                            app.listaCobros = resp;
                            app.mostrarResultados = true;
                        }
                    );
                },
                formatearFecha(fechaHora) {
                    if (!fechaHora) return '-';
                    const fecha = new Date(fechaHora);
                    return fecha.toLocaleDateString('es-PE');
                },
                formatearHora(fechaHora) {
                    if (!fechaHora) return '-';
                    const fecha = new Date(fechaHora);
                    return fecha.toLocaleTimeString('es-PE', { 
                        hour: '2-digit', 
                        minute: '2-digit',
                        hour12: true 
                    });
                },
                getBadgeClass(tipoPago) {
                    if (!tipoPago || tipoPago === 'Efectivo') {
                        return 'badge bg-success';
                    }
                    return 'badge bg-info';
                }
            }
        });
    });
</script>
