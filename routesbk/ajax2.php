<?php



Route::post("/ajs/admin/cliente/add","AdminDataController@agregarCliente")->Middleware([ValidarTokenMiddleware::class]);
Route::post("/ajs/admin/cliente/edt","AdminDataController@actualizarCliente")->Middleware([ValidarTokenMiddleware::class]);
Route::post("/ajs/admin/cliente/info","AdminDataController@infoCliemt")->Middleware([ValidarTokenMiddleware::class]);
Route::post("/ajs/admin/cliente/estado/edt","AdminDataController@guardarEstado")->Middleware([ValidarTokenMiddleware::class]);


Route::post("/ajs/data/producto/add","ProductosController@agregar")->Middleware([ValidarTokenMiddleware::class]);
Route::post("/ajs/data/producto/add/lista","ProductosController@agregarPorLista")->Middleware([ValidarTokenMiddleware::class]);
Route::post("/ajs/data/producto/edt","ProductosController@actualizar")->Middleware([ValidarTokenMiddleware::class]);
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
Route::post("/ajs/consulta/sucursales/empresa/edt","ConsultasController@actualizarSucursal")->Middleware([ValidarTokenMiddleware::class]);

Route::post("/ajs/send/comprobante/email","ConsultasController@enviarcomprobanteEmail");

Route::post("/ajs/informacion/venta/fb","ConsultasController@informacionVentaFb");

Route::post("/ajs/verificador/token","ConsultasController@verificadorToken");


Route::post("/ajs/cotizaciones","CotizacionesController@listar");
Route::post("/ajs/cotizaciones/add","CotizacionesController@agregar");
Route::post("/ajs/cotizaciones/edt","CotizacionesController@actualizar");
Route::post("/ajs/cotizaciones/info","CotizacionesController@getInformacion");


Route::post('/ajs/cuentas/cobrar/render',"CobranzaController@render");
Route::post('/ajas/getAllCuotas/byIdVenta',"CobranzaController@getAllByIdVenta");
Route::post('/ajs/pagar/cuota/cobranza',"CobranzaController@pagarCuota");


Route::post('/ajs/caja/apertura',"CajaController@aperturarCaja");
Route::post('/ajs/caja/apertura/listar',"CajaController@listar");
Route::post('/ajs/caja/chica/add',"CajaController@agregarMovimiento");
Route::post('/ajs/caja/chica/cerrar',"CajaController@cerrarCajaChica");


Route::post('/ajs/prodcutos/compras/render',"ComprasController@getAll");
Route::post('/ajas/compra/detalle',"ComprasController@getDetalle");
Route::post('/ajas/compra/buscar/producto',"ComprasController@buscarProducto");
Route::post('/ajas/compras/add',"ComprasController@guardarCompras");



Route::post('/ajas/cuentas/ventas/render',"PagosController@render");
Route::post('/ajas/getAllCuotas/byIdCompra',"PagosController@getAllByIdCompra");
Route::post('/ajs/pagar/cuota/pago',"PagosController@pagarCuota");


Route::post("/ajas/ventas/porempresa","VentasController@listaVentasPorEmpresa");
Route::post("/ajas/ventas/porempresa/regenxml","VentasController@regenerarXML");
Route::post("/ajas/ventas/porempresa/sendsunat","VentasController@enviarDocumentoSunatPorEmpresa");
Route::post("/ajas/ventas/porempresa/sendsunatresumen","VentasController@envioResumenDiarioPorEmpresa");
Route::post("/ajas/ventas/porempresa/sendsunatcomubaja","VentasController@envioComunicacionBajaPorEmpresa");








