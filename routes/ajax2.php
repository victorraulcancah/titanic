<?php



Route::get("/data/cotizaciones/lista/ss","ConsultaDelcontroller@getDataCotizacionSS")->Middleware([ValidarTokenMiddleware::class]);
Route::get("/ajs/asearch/provedor/data","ConsultasController@buscarDataProveedor")->Middleware([ValidarTokenMiddleware::class]);

Route::post("/ajs/admin/cliente/add","AdminDataController@agregarCliente")->Middleware([ValidarTokenMiddleware::class]);
Route::post("/ajs/admin/cliente/edt","AdminDataController@actualizarCliente")->Middleware([ValidarTokenMiddleware::class]);
Route::post("/ajs/admin/cliente/info","AdminDataController@infoCliemt")->Middleware([ValidarTokenMiddleware::class]);
# datos cliente
Route::get("/ajs/admin/cliente/rutas","AdminDataController@obtenerRutasClientes")->Middleware([ValidarTokenMiddleware::class]);
Route::get("/ajs/admin/cliente/mercados","AdminDataController@obtenerMercadosClientes")->Middleware([ValidarTokenMiddleware::class]);
Route::get("/ajs/admin/cliente/medidas", "AdminDataController@obtenerMedidas")->Middleware([ValidarTokenMiddleware::class]);


Route::post('/ajs/cargar/productos/precios',"ConsultasController@cargarPreciosProd")->Middleware([ValidarTokenMiddleware::class]);


Route::post("/ajs/admin/cliente/estado/edt","AdminDataController@guardarEstado")->Middleware([ValidarTokenMiddleware::class]);


Route::post("/ajs/data/producto/add","ProductosController@agregar")->Middleware([ValidarTokenMiddleware::class]);
Route::post("/ajs/data/producto/delete","ProductosController@delete")->Middleware([ValidarTokenMiddleware::class]);
Route::post("/ajs/data/producto/activar","ProductosController@activar")->Middleware([ValidarTokenMiddleware::class]);
Route::post("/ajs/confirmar/traslado","ProductosController@confirmarTraslado")->Middleware([ValidarTokenMiddleware::class]);
Route::post("/ajs/data/producto/add/lista","ProductosController@agregarPorLista")->Middleware([ValidarTokenMiddleware::class]);
Route::post("/ajs/producto/lista","ProductosController@listaProducto")->Middleware([ValidarTokenMiddleware::class]);
Route::post("/ajs/data/producto/edt","ProductosController@actualizar")->Middleware([ValidarTokenMiddleware::class]);
Route::post("/ajs/data/producto/edt/precios","ProductosController@actualizarPrecios")->Middleware([ValidarTokenMiddleware::class]);
Route::post("/ajs/data/producto/info/code","ProductosController@informacionPorCodigo")->Middleware([ValidarTokenMiddleware::class]);
Route::post("/ajs/data/producto/info","ProductosController@informacion")->Middleware([ValidarTokenMiddleware::class]);
Route::post("/ajs/data/producto/restock","ProductosController@restock")->Middleware([ValidarTokenMiddleware::class]);
Route::post("/ajs/data/producto/add/exel","ProductosController@importarExel")->Middleware([ValidarTokenMiddleware::class]);
Route::post("/ajs/consulta/doc/venta/info","ConsultasController@functionbuscarDocumentoVentasSN")->Middleware([ValidarTokenMiddleware::class]);
Route::post("/ajs/nota/electronica/add","ConsultasController@guardarNotaElectronica")->Middleware([ValidarTokenMiddleware::class]);


Route::post("/ajs/send/sunat/venta","VentasController@enviarDocumentoSunat")->Middleware([ValidarTokenMiddleware::class]);
Route::post("/ajs/send/sunat/notaelectronica","ConsultasController@enviarDocumentoSunatNE")->Middleware([ValidarTokenMiddleware::class]);
Route::post("/ajs/send/sunat/guiaremision","GuiaRemisionController@enviarDocumentoSunat")->Middleware([ValidarTokenMiddleware::class]);
Route::post("/ajs/consulta/sucursales/empresa","ConsultasController@listasucursaleEmpresa")->Middleware([ValidarTokenMiddleware::class]);
Route::post("/ajs/consulta/sucursales/empresa/add","ConsultasController@agregarSusucursal")->Middleware([ValidarTokenMiddleware::class]);
Route::post("/ajs/consulta/sucursales/empresa/info","ConsultasController@getInfoSucursal")->Middleware([ValidarTokenMiddleware::class]);
Route::post("/ajs/consulta/sucursales/empresa/info/detalle","ConsultasController@getInfoSucursalDetalle")->Middleware([ValidarTokenMiddleware::class]);
Route::post("/ajs/consulta/sucursales/empresa/edt","ConsultasController@actualizarSucursal")->Middleware([ValidarTokenMiddleware::class]);

Route::post("/ajs/consulta/metodo/pago","ConsultasController@getMetodoPago")->Middleware([ValidarTokenMiddleware::class]);


Route::post("/ajs/consulta/stock/almacen","ConsultasController@consultaStockAlmacen")->Middleware([ValidarTokenMiddleware::class]);
Route::post("/ajs/consulta/stock/almacen","ConsultasController@consultaStockAlmacen")->Middleware([ValidarTokenMiddleware::class]);

Route::post("/ajs/send/comprobante/email","ConsultasController@enviarcomprobanteEmail");

Route::post("/ajs/informacion/venta/fb","ConsultasController@informacionVentaFb");

Route::post("/ajs/verificador/token","ConsultasController@verificadorToken");


Route::post("/ajs/cotizaciones","CotizacionesController@listar");
Route::post("/ajs/cotizaciones/add","CotizacionesController@agregar");
Route::post("/ajs/cotizaciones/edt","CotizacionesController@actualizar");
Route::post("/ajs/cotizaciones/info","CotizacionesController@getInformacion");
Route::post("/ajs/cotizaciones/del","CotizacionesController@eliminarCotizacion");
Route::post("/ajs/cotizaciones/getvendedores","CotizacionesController@getVendedores");
Route::post('/ajs/pagar/total/ventas', "PagosController@totalCuotaVentas");


Route::post('/ajs/cuentas/cobrar/render',"CobranzaController@render");
Route::post('/ajs/cuentas/deuda/render', "CobranzaController@renderDeudas");

Route::post('/ajas/getAllCuotas/byIdVenta',"CobranzaController@getAllByIdVenta");
Route::post('/ajas/getAllProductos/byIdVenta', "VentasController@getAllByProductosIdVenta"); // agregando 10/04/2025
Route::post('/ajs/pagar/cuota/cobranza',"CobranzaController@pagarCuota");
Route::post('/ajs/pagar/cuota/ventas',"PagosController@pagarCuotaVentas");
Route::post('/ajs/editar/cuota/ventas',"PagosController@editarCuotaVentas");
Route::post('/ajs/pagar/cuota/eliminar',"PagosController@eliminarPagoCuotaVentas");

Route::post('/ajs/caja/apertura',"CajaController@aperturarCaja");
Route::post('/ajs/caja/apertura/listar',"CajaController@listar");
Route::post('/ajs/caja/chica/add',"CajaController@agregarMovimiento");
Route::post('/ajs/caja/chica/cerrar',"CajaController@cerrarCajaChica");


Route::post('/ajs/prodcutos/compras/render',"ComprasController@getAll");
Route::post('/ajas/compra/detalle',"ComprasController@getDetalle");
Route::post('/ajas/compra/buscar/producto',"ComprasController@buscarProducto");
Route::post('/ajas/compras/add',"ComprasController@guardarCompras");
Route::post('/ajas/compras/update',"ComprasController@actualizarCompra");
Route::post('/ajs/compras/obtener',"ComprasController@obtenerCompra");


Route::post('/ajas/cuentas/ventas/render',"PagosController@render");
Route::post('/ajas/getAllCuotas/byIdCompra',"PagosController@getAllByIdCompra");
Route::post('/ajs/pagar/cuota/pago',"PagosController@pagarCuota");
Route::post('/ajas/getAllProductos/byIdCompra',"PagosController@getAllProductosByIdCompra");
Route::post('/ajs/editar/cuota/compras',"PagosController@editarCuotaCompras");
Route::post('/ajs/pagar/cuota/eliminar/compra',"PagosController@eliminarPagoCuotaCompras");


Route::post("/ajas/ventas/porempresa","VentasController@listaVentasPorEmpresa");
Route::post("/ajas/ventas/porempresa/regenxml","VentasController@regenerarXML");
Route::post("/ajas/ventas/porempresa/sendsunat","VentasController@enviarDocumentoSunatPorEmpresa");
Route::post("/ajas/ventas/porempresa/sendsunatresumen","VentasController@envioResumenDiarioPorEmpresa");
Route::post("/ajas/ventas/porempresa/sendsunatcomubaja","VentasController@envioComunicacionBajaPorEmpresa");

Route::post("/ajs/getroles","ConsultasController@getRoles");
Route::post("/ajs/add/users","ConsultasController@saveUser");

Route::get("/ajs/get/detalles","ConsultasController@getDetalle");

Route::post("/ajs/set/state/pago/cv","ConsultasController@cambiarEstadoPAgoCv");

#
Route::post('/ajas/devolucones/render',"DevolucionesController@render");






// Rutas de Arqueo Diario
Route::post("/ajs/arqueo/cobros/dia","ArqueoDiarioController@obtenerCobrosDia")->Middleware([ValidarTokenMiddleware::class]);
Route::post("/ajs/arqueo/guardar","ArqueoDiarioController@guardarArqueo")->Middleware([ValidarTokenMiddleware::class]);
Route::post("/ajs/arqueo/listar","ArqueoDiarioController@obtenerArqueosGuardados")->Middleware([ValidarTokenMiddleware::class]);
Route::post("/ajs/arqueo/obtener","ArqueoDiarioController@obtenerArqueoPorId")->Middleware([ValidarTokenMiddleware::class]);
Route::post("/ajs/arqueo/actualizar","ArqueoDiarioController@actualizarArqueo")->Middleware([ValidarTokenMiddleware::class]);
Route::post("/ajs/arqueo/buscar-clientes","ArqueoDiarioController@buscarClientes")->Middleware([ValidarTokenMiddleware::class]);

// Rutas de Caja Vendedor
Route::post("/ajs/caja/vendedor/abrir","CajaVendedorController@abrirCaja")->Middleware([ValidarTokenMiddleware::class]);
Route::post("/ajs/caja/vendedor/gasto","CajaVendedorController@registrarGasto")->Middleware([ValidarTokenMiddleware::class]);
Route::post("/ajs/caja/vendedor/obtener","CajaVendedorController@obtenerCajaAbierta")->Middleware([ValidarTokenMiddleware::class]);
Route::post("/ajs/caja/vendedor/movimientos","CajaVendedorController@obtenerMovimientos")->Middleware([ValidarTokenMiddleware::class]);
Route::post("/ajs/caja/vendedor/cerrar","CajaVendedorController@cerrarCaja")->Middleware([ValidarTokenMiddleware::class]);
Route::post("/ajs/caja/vendedor/historial","CajaVendedorController@obtenerHistorial")->Middleware([ValidarTokenMiddleware::class]);

// Rutas de Cobros Vendedor
Route::post("/ajs/cobros/vendedor/rango","CobrosVendedorController@obtenerCobrosPorRango")->Middleware([ValidarTokenMiddleware::class]);

// Ruta para obtener detalle completo de caja (Admin)
Route::post("/ajs/caja/detalle/completo","CajaVendedorController@obtenerDetalleCompleto")->Middleware([ValidarTokenMiddleware::class]);

// Rutas nuevas para edici��n de caja
Route::post("/ajs/caja/cobros/vendedor","CajaVendedorController@obtenerCobrosVendedor")->Middleware([ValidarTokenMiddleware::class]);
Route::post("/ajs/caja/guardar","CajaVendedorController@guardarRegistroCaja")->Middleware([ValidarTokenMiddleware::class]);
Route::post("/ajs/caja/registros/historicos","CajaVendedorController@obtenerRegistrosHistoricos")->Middleware([ValidarTokenMiddleware::class]);
