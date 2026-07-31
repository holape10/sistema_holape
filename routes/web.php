<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


//RUTAS PARA EL INGRESO AL SISTEMA


Route::group(['middleware' => 'validateAuthenticated'], function () {

Route::get('/','Auth\LoginController@showFormLogin');
Route::get('/login','Auth\LoginController@showFormLogin');
Route::view('/ingreso','auth/loginmesas');
Route::post('/ingresomozo','PuntoVentaController@ingresomozo');
Route::post('/login','Auth\LoginController@login');

Route::get('/logmovil', 'Auth\LoginController@showFormLoginMovil')->name('logmovil');
Route::post('/logmovil', 'Auth\LoginController@loginMovil');

Route::get('/inicio', 'HomeController@index')->name('inicio');

//EMPLEADOS
Route::resource('/empleado','EmpleadoController');
Route::post('/actualizarempleado','EmpleadoController@update');
Route::get('/exportarempleados','EmpleadoController@exportar_empleados');

Route::get('/consultar-documento', 'EmpleadoController@consultarDocumento');

// Rutas para Precios Dinámicos
Route::get('/productos/get-precios-dinamicos-modal/{productId}', 'ProductosController@getPreciosDinamicosModal');
Route::post('/productos/guardar-precios-dinamicos/{productId}', 'ProductosController@guardarPreciosDinamicos');
Route::post('/productos/toggleDescuento', 'ProductosController@toggleDescuento')->name('productos.toggleDescuento');

Route::get('/buscar-catalogo-sunat', 'ProductosController@buscarCatalogoSunat');

//RUTA DE ELIMINAR PEDIDOS CON CONTRASEÑA

Route::post('/validar-admin-password', 'RestauranteController@validarAdminPassword');



Route::get('/migrarxml','UtilitariosController@migrar_xml');

Route::get('/soporte', 'SoporteController@index')->name('soporte.index');
Route::post('soporte/contacto', 'SoporteController@store')->name('soporte.store');

//AUTORIZACIONES
Route::get('/autorizarmodificarprecio/{codigo?}/{usuario?}','PuntoVentaController@autorizarmodificarprecio');
//RUTAS PARA VISUALIZAR MESAS
Route::get('/mesas/{usuario}/{sucursal}','PuntoVentaController@mesas');
Route::get('/actualizar','UtilitariosController@actualizar');
Route::get('/actualizarproductostock','UtilitariosController@actualizar_producto_stock');
Route::get('/editarproducto/{id}/{sucursal}','ProductosController@edit');

Route::get('/precios','ProductosController@precios');

//VALETPARKING

// --- MÓDULO ESTACIONAMIENTO ---
Route::get('/estacionamiento/ingreso', 'EstacionamientoController@index');
Route::post('/estacionamiento/ingreso', 'EstacionamientoController@store');
Route::post('/estacionamiento/salida/{codigo}', 'EstacionamientoController@registrarSalida');
Route::get('/estacionamiento/activos', 'EstacionamientoController@obtenerActivos');
Route::get('/estacionamiento/cobrar/{codigo}', 'EstacionamientoController@cobrarTicket');
Route::post('/estacionamiento/registrarcobro', 'EstacionamientoController@registrar_cobro');
Route::get('/estacionamiento/tarifas', 'EstacionamientoController@indexTarifas');
Route::post('/estacionamiento/tarifas/guardar', 'EstacionamientoController@guardarTarifa');
Route::get('/estacionamiento/reportes', 'EstacionamientoController@reportePorPunto');
// API Consulta Documento
Route::post('/consultardocumento', 'EstacionamientoController@consultarDocumento');

Route::get('/ingresovehiculo','ValetParkingController@ingresovehiculo');
Route::get('/historial/{id}','ProductosController@historialproducto');

Route::get('/ventasimportardbf','IntegracionSistemaController@extraer_ventas_dbf');

Route::get('/stockinicial','AlmacenController@registrar_stock_inicial');
Route::get('/stockinicialproductos','AlmacenController@stock_inicial_productos');
Route::get('/registrarmovimientos','AlmacenController@calcular_stock');
Route::get('/registrarcompras','AlmacenController@registrar_compras');
Route::get('/registrar_ventas','AlmacenController@registrar_ventas');
Route::get('/registrartransferenciassalidas','AlmacenController@registrar_transferencias_salidas');
Route::get('/registrartransferenciassalidasguia','AlmacenController@registrar_transferencias_salidas_guias');
Route::get('/registrartransferenciasingresos','AlmacenController@registrar_transferencias_ingresos');
Route::get('/calcular_stock','AlmacenController@calcularstock');
Route::get('/calcularmovimiento/{id}','AlmacenController@calcular_movimientos');

//TALLER
Route::resource('/tiposvehiculos','TiposVehiculosController');
Route::get('/generarordenes/{venta}','POSGaleriaController@generarordenes');

Route::get('/indexcotizaciones/{id?}','POSGaleriaController@indexcotizaciones');
Route::get('/indexcotizacion','ComprobantesController@cotizaciones');
Route::get('/indexordenestrabajo','ComprobantesController@indexordenestrabajo');
Route::get('/indexordenespedido','POSGaleriaController@indexordenespedido');

Route::get('/ordenpedido/{tdocod?}/{cpe?}','POSGaleriaController@ordenpedido');
Route::get('/cotizaciones/{placa?}','POSGaleriaController@cotizaciones');
Route::get('/ordentrabajo/{placa?}','POSGaleriaController@ordentrabajo');
Route::get('/ordenpedido/{placa?}','POSGaleriaController@ordenpedido');


Route::get('/generarot/{id}','POSGaleriaController@generarot');
Route::post('/registrarop','POSGaleriaController@registrarordenpedido');
Route::post('/registrarcotizacion','POSGaleriaController@registrarcotizacion');
Route::post('/registrarot','POSGaleriaController@registrarordentrabajo');

Route::get('/editarop/{id}','POSGaleriaController@editarordenpedido');
Route::get('/editarcotizacion/{id}','POSGaleriaController@editarcotizacion');
Route::get('/editarot/{id}','POSGaleriaController@editarordentrabajo');
Route::get('/cobrar/{id}','POSGaleriaController@cobrar')->middleware('ValidarAperturaTurno');

Route::post('/actualizarop','POSGaleriaController@actualizarordenpedido');
Route::post('/actualizarcotizacion','POSGaleriaController@actualizarcotizacion');
Route::post('/actualizarot','POSGaleriaController@actualizarordentrabajo');

Route::post('/registrarcobropunto','POSGaleriaController@registrarcobropunto');

Route::get('/eliminar/{id}','POSGaleriaController@eliminar');
Route::get('/generarcomprobante/{comprobante}','POSGaleriaController@generarcomprobante');
Route::get('/generarcomunicacionbaja/{comprobantes}','ComprobantesController@generarcomunicacionbaja');
Route::get('/generarresumendiario/{fecha}/{tipo}','POSGaleriaController@generarresumendiario');
Route::get('/consultarticket/{ticket}/{tipo}','POSGaleriaController@consultarticket');
Route::get('/consultarticketbaja/{ticket}','ComprobantesController@consultarticketbaja');
Route::get('/descargar/{venta}/{tipo}','POSGaleriaController@descargar');
Route::get('/descargarcompra/{id}/','ComprasController@descargar');
Route::get('/descargarcompraexcel/{id}/','ComprasController@descargar_excel');

Route::get('/generarpdf/{venta}','POSGaleriaController@generarpdf');
Route::get('/generarpdf1/{venta}','POSGaleriaController@generarpdf1');
Route::get('/generarordenes/{venta}','POSGaleriaController@generarordenes');

//CUENTAS POR COBRAR
Route::post('/registrarcuentacobrar','CuentasCobrarController@registrar');
Route::post('/actualizarabono','CuentasCobrarController@actualizar_abono');
Route::get('/cuotas/{venta}','CuentasCobrarController@detallecuotas');
Route::get('/editarcobro/{id}','CuentasCobrarController@editar_cobro');
Route::get('/cuentascobrar','CuentasCobrarController@index');
Route::get('/cuentascobrar/ingresar/{cuenta}','CuentasCobrarController@ingresar');
Route::get('/cuentascobrar/ingresar','CuentasCobrarController@ingresarcuenta');
Route::get('/cuentascobrar/detalle/{cuenta}','CuentasCobrarController@detalle');
Route::get('/cuentascobrar/eliminar','CuentasCobrarController@eliminar');
Route::post('/cuentascobrar/registrarcuenta','CuentasCobrarController@registrarcuenta');
Route::post('/cuentascobrar/registrartotal','CuentasCobrarController@registrartotal');
Route::post('/cuentascobrar','CuentasCobrarController@index');
Route::post('/cuentascobrar/cuentas','CuentasCobrarController@cuentascobrar');
Route::post('/eliminarcuentacobrar','CuentasCobrarController@eliminar_cuenta_cobrar');


//CUENTAS POR PAGAR
Route::get('/cuentaspagar','CuentasPagarController@index');
Route::get('/cuentaspagar/ingresar/{cuenta}','CuentasPagarController@ingresar');
Route::get('/cuentaspagar/ingresar','CuentasPagarController@ingresarcuenta');
Route::post('/actualizarpago','CuentasPagarController@actualizar_pago');
Route::get('/editarpago/{id}','CuentasPagarController@editar_pago');
Route::get('/cuentaspagar/detalle/{cuenta}','CuentasPagarController@detalle');
Route::get('/cuentaspagar/eliminar','CuentasPagarController@eliminar');
Route::post('/cuentaspagar/registrar','CuentasPagarController@registrar');
Route::post('/cuentaspagar/registrarcuenta','CuentasPagarController@registrarcuenta');
Route::post('/cuentaspagar/registrartotal','CuentasPagarController@registrartotal');
Route::post('/cuentaspagar','CuentasPagarController@index');
Route::post('/cuentaspagar/cuentas','CuentasPagarController@cuentaspagar');
Route::post('/eliminarcuentapagar','CuentasCobrarController@eliminar_cuenta_pagar');

//REPORTES
Route::get('/reportepedido','ReportesPedidosController@buscar_reporte_pedido');
Route::post('/reportepedidoexcel','ReportesPedidosController@buscar_reporte_pedido_excel');
Route::get('/generarreporte/{tipo}','ReportesController@generarreporte');
Route::get('/rptpagospersonal','ReportesController@reporte_pagos_personal');
Route::post('/rptpagospersonal','ReportesController@reporte_pagos_personal');
Route::post('/generarrptpagospersonal','ReportesController@pdf_reporte_pagos_personal');
Route::post('/generarreportepdfproductos','ReportesController@pdf_reporte_resumen_ventas_productos');
Route::post('/generarreporteventasvendedor','ReportesController@pdf_reporte_ventas_vendedor');
Route::get('/generarreporteinventario/{valor1}/{valor2}','ReportesController@generar_reporte_inventario');
Route::post('/generarreportepdf','ReportesController@buscarreportepdf');

//REPORTE EN FORMATO TICKET
Route::post('/generar_reporte_ticket','ReportesTicketController@generar_reporte_ticket');
Route::post('/reporte_ticket_ranking_productos','ReportesTicketController@reporte_ranking_productos');

Route::get('/ventasturno/{turno}','TurnosController@ventasturno');
Route::get('/ventasturnoexcel/{turno}','TurnosController@ventasturnoexcel');
Route::get('/arqueoresumen/{turno}/{tipo?}','TurnosController@arqueoresumen');
Route::get('/arqueodetallado/{turno}/{tipo?}','TurnosController@arqueodetallado');

Route::get('/arqueodiario','ReportesController@arqueodiario');
Route::post('/generararqueodiario','ReportesController@generararqueodiario');

Route::get('/ventasdia/{turno}','TurnosController@reporteVentasDia');

Route::post('/ventasturno','TurnosController@ventasturno');
Route::get('/nuevaorden','POSGaleriaController@nuevaorden');

Route::post('/imprimirreporteventas','ReportesController@imprimirreporteventas');

//PROGRAMAS
Route::resource('/programas','ProgramasController');
Route::get('/asignarplatos/{id}','ProgramasController@asignarplatos');
Route::post('/registrarplato','ProgramasController@registrarplato');


//DELIVERY
Route::get('/delivery','RestauranteController@listar_pedidos_delivery');
Route::post('/programarenvio','PuntoVentaController@programarenvio');
Route::get('/entregarpedido/{pedido}','RestauranteController@entregarpedido');
Route::get('/preparadopedido/{pedido}','PuntoVentaController@preparadopedido');
Route::get('/reprogramarenvio/{pedido}','PuntoVentaController@reprogramarenvio');

//AREAS
Route::resource('/areas','AreasController');

//PROCESOS
Route::resource('/procesos','ProcesosController');
Route::get('/operaciones','ProcesosController@operaciones');
Route::get('/iniciarprocesos/{id}','ProcesosController@iniciarprocesos');
Route::get('/mostrarprocesos/{id}','ProcesosController@mostrarprocesos');
Route::post('/iniciarproceso','ProcesosController@iniciarproceso');
Route::post('/finalizarproceso','ProcesosController@finalizarproceso');
Route::post('/observacionproceso','ProcesosController@observacionproceso');
Route::get('/finalizarproceso/{id}','ProcesosController@finalizarproceso');
Route::get('/detalleorden/{venta}','ProcesosController@detalleorden');

//MAQUINAS
Route::resource('/maquinas','MaquinasController');

//MARCAS
Route::resource('/marcas','MarcasController');
Route::resource('/modelos','ModelosController');


//COCINA
Route::view('/consultas','empresas/comprobantes');
Route::view('/info','empresas/info');
Route::view('/consolidado','/empresas/consolidadoproductos/index');
Route::view('/prueba','empresas/prueba');
Route::view('/prueba2','empresas/prueba2');
Route::view('/graficos','empresas/reportes/graficos');

// Ruta principal para la consola del restaurante
Route::get('/consola', 'RestauranteController@consola')->name('restaurante.consola');

// Rutas de API para la gestión de pedidos desde el frontend (AJAX/Fetch)
// Usamos POST para guardar/actualizar y eliminar, ya que modifican datos.
Route::post('/api/pedidos/guardar', 'RestauranteController@registrar_o_actualizar_pedido')->name('api.pedidos.guardar');
Route::post('/api/pedidos/eliminar-item', 'RestauranteController@eliminar_item_pedido')->name('api.pedidos.eliminar_item');
// Para la eliminación completa del pedido (si se usa un botón específico para eso)
Route::post('/api/pedidos/eliminar-completo/{ped_id}', 'RestauranteController@eliminar_pedido_completo')->name('api.pedidos.eliminar_completo');

// Ruta para obtener los detalles de un pedido existente (para precargar el modal)
Route::get('/api/pedidos/{ped_id}/detalles', 'RestauranteController@buscar_detalles_pedido')->name('api.pedidos.detalles');

Route::get('/categoriapredeterminada/{cat}','CategoriasController@categoria_predeterminada');
Route::get('/buscarcategorias/{producto}','ProductosController@buscarcategorias');
Route::get('/buscaralmacen/{sucursal}','AlmacenController@buscaralmacen');
Route::get('/buscaralmaceninventario/{sucursal}','AlmacenController@buscaralmaceninventario');
Route::get('/buscaralmacendestino/{sucursal}','AlmacenController@buscaralmacendestino');
Route::get('/buscarsubcategorias/{producto}','ProductosController@buscarsubcategorias');
Route::get('/buscartipos/{producto}','ProductosController@buscartipos');

Route::get('/buscarfamilias/{id}', 'ProductosController@buscarfamilias');

Route::get('/buscarsucursales/{empresa}','EmpresaNegociosController@buscarsucursales');
Auth::routes();

//REPORTES
Route::get('/reportes/{tipo}','ReportesController@generarreporte');
Route::post('/buscarreporte','ReportesController@buscarreporte');
Route::post('/buscarreportepdf','ReportesController@buscarreportepdf');

//CONTROL DE CAJA
Route::get('/facturacionelectronica','PuntoVentaController@buscarcomprobantes');
Route::post('/facturacionelectronica','PuntoVentaController@buscarcomprobantes');
Route::get('/buscarinconsistenciassunat','PuntoVentaController@buscar_inconsistencias_sunat');
Route::get('/listarresumenes','PuntoVentaController@listarresumenes');

Route::post('/cerrarturno','TurnosController@CerrarTurno');
Route::get('/movimientosturno/{turno}','TurnosController@movimientosturno');
Route::post('/aperturarturno','TurnosController@AperturarTurno');
Route::get('/ReporteCaja','ReportesController@ReporteCaja');
Route::get('/caja','TurnosController@index');
Route::get('/imprimircaja/{turno}','TurnosController@imprimirturno');
Route::get('/imprimircajaproductos/{turno}','TurnosController@imprimirturnoproductos');
Route::get('/imprimircajadenominaciones/{turno}','TurnosController@imprimirDenominaciones')->name('reporte.denominaciones.turno');
Route::resource('/turnos','TurnosController');

Route::get('/imprimircajacategoriasproductos/{id}', 'TurnosController@imprimirCajaCategoriasProductos')->name('reporte.categorias.productos.turno');



Route::get('/listos','PuntoVentaController@pedidosgeneral');

Route::post('/enviarsunat','PuntoVentaController@enviarsunat');
Route::post('/enviarresumen','PuntoVentaController@generar_xml_resumen_diario');
Route::post('/listarresumenes','PuntoVentaController@listarresumenes');
Route::post('/buscarresumenes','PuntoVentaController@buscarresumenes');

Route::post('/comprobantes/revisar1033', 'ComprobantesController@revisarComprobantes1033')->name('comprobantes.revisar1033');

Route::get('/modificarpedido/{mesa}','PuntoVentaController@modificarpedido');
Route::get('/mostrarpiso/{piso}','PuntoVentaController@mostrarpiso');
Route::resource('sales', 'SaleController', ['only' => ['create', 'store']]);

Route::get('/presentaciones/{producto}','ProductosController@presentaciones');
Route::post('/actualizarpresentaciones','ProductosController@actualizarpresentaciones');
Route::post('/actualizarstock','ProductosController@actualizarstock');


Route::get('/movimientoproducto','AlmacenController@movimiento_producto');
Route::post('/movimientoproducto','AlmacenController@movimiento_producto');



Route::get('/administrador/contrasena/{id}','UsuarioController@editarContrasena');
Route::post('/administrador/cambiar/contrasena','UsuarioController@cambiarContrasena');
Route::resource('administrador/usuarios','UsuarioController');

// Rutas Personalizadas dashboard
Route::get('dashboard/documentos/{tdocod_filter}', 'DashboardController@Documentos')->name('dashboard.documentos.lista');
Route::resource('dashboard','DashboardController');

Route::resource('/series','SeriesController');
Route::resource('/tipocambio','TipoCambioController');
Route::resource('/consultar','ConsultarComprobantesController');
//Route::get('/imprimir/{cpe}/{tipdoc}','PuntoVentaController@imprimir');
Route::get('/imprimir/{cpe}/{tipdoc}','RestauranteController@imprimir');
Route::get('/imprimirgasto/{codgast?}','ImprimirTickeController@imprimirgasto');
Route::get('/imprimirpedido/{pedido}/{tipo?}','ImprimirTickeController@imprimirpedido');
Route::get('/imprimircuenta/{pedido}','ImprimirTickeController@imprimircuenta');
Route::get('/imprimircuentaweb/{pedido}','ImprimirTickeController@imprimircuentaweb');
Route::get('/imprimirpedidollevar/{pedido}/{tipo?}','ImprimirTickeController@imprimirpedidollevar');

Route::get('/imprimircierre/{fecfin}/{fecin}','ImprimirTickeController@imprimircierre');

Route::get('/consultarcdr/{id}','PuntoVentaController@consultar_cdr');
Route::get('/enviarservidor/{id}','PuntoVentaController@enviar_servidor');
Route::get('usuarios/create','UsuarioController@create');
Route::get('/home', 'HomeController@index')->name('home');
Route::get('/','Auth\LoginController@showFormLogin');
Route::get('/logout', 'Auth\LoginController@logout');
Route::get('/administrador/registrar','Auth\RegisterController@showEmpresa');
Route::view('/nuevanotacredito','empresas\comprobantes\nuevanotacredito');
Route::view('/nuevanotadebito','empresas\comprobantes\nuevanotadebito');

Route::post('/registrarguia','PuntoVentaController@registrarguia');
//COTIZACIONES
Route::get('/cotizaciones','ComprobantesController@cotizaciones');
Route::get('/cobrarcotizacion/{cotizacion}','PuntoVentaController@cobrarcotizacion');
Route::get('/modificarcotizacion/{cotizacion}','PuntoVentaController@modificarcotizacion');

//Route::post('/actualizarcotizacion','PuntoVentaController@actualizarcotizacion');

//ordenes
Route::get('/ordenes','ComprobantesController@indexordenes');
Route::get('/historialordenes','ComprobantesController@historialordenes');
Route::get('/equiposreparacion','ComprobantesController@equiposreparacion');
Route::get('/ordenesclientes','ComprobantesController@ordenesclientes');
Route::get('/cobrarorden/{orden}','PuntoVentaController@cobrarorden');
Route::get('/modificarorden/{orden}','PuntoVentaController@modificarorden');
Route::post('/actualizarorden','PuntoVentaController@actualizarorden');
Route::post('/actualizarestado','PuntoVentaController@actualizarestado');
Route::post('/registrarpagoorden','PuntoVentaController@registrarpagoorden');


//TIPO GASTOS
Route::resource('/tipogastos','TipoGastosController');


//VENTAS

Route::get('/notacredito','ComprobantesController@lista_nota_credito');

Route::resource('/SisFact','ComprobantesController');
Route::resource('/Reportes','ReportesController');
Route::get('/ingreso/almacen','AlmacenController@ingresoproductos');

Route::get('/transferir','AlmacenController@transferirproductos');
Route::post('/transferir','AlmacenController@transferir');
Route::get('/transferiralmacenes','AlmacenController@transferirproductosalmacenes');
Route::post('/transferiralmacenes','AlmacenController@transferiralmacenes');

Route::get('/generarguia/{id}','AlmacenController@generarguiaalbergue');

Route::get('/recepcionar/{transferencia}','AlmacenController@recepcionartransferencia');
Route::get('/transferencias','AlmacenController@documentos_transferencias');
Route::post('/registrartransferencia','AlmacenController@registrar_recepcion_transferencia');


Route::get('/pedidos','PuntoVentaController@pedidos');
Route::post('/registrarpedido','PuntoVentaController@registrarpedido');
Route::post('/actualizarpedido','PuntoVentaController@actualizarpedido');

Route::get('/indexpedidos','ComprobantesController@indexpedidos');
Route::get('/buscarpedido/{pedido}/{tipo}','PuntoVentaController@buscarpedido');
Route::get('/buscar_comprobante/{comprobante}','PuntoVentaController@buscar_comprobante');

Route::get('/modificarpedidos/{pedido}','PuntoVentaController@modificarpedidos');

//CUENTAS POR COBRAR
Route::post('/reportecobranzavendedor','ReportesController@pdf_reporte_cuentas_cobrar_vendedor');
Route::post('/reportecobranzasvendedor','ReportesController@pdf_reporte_cobranzas_vendedor');
Route::post('/reportecobranzasclientes','ReportesController@pdf_reporte_cobranzas_clientes');
Route::post('/reportecobranzacliente','ReportesController@pdf_reporte_cuentas_cobrar_cliente');
Route::post('/reportecuentas','ReportesController@cuentascobrar');
Route::get('/reportecuentas','ReportesController@cuentascobrar');

Route::post('/Reportes', 'ReportesController@ReporteComprobantes')->name('Reportes.ReporteComprobantes');
Route::resource('/documentos/bajas','BajasController');
Route::resource('/negocios','EmpresaNegociosController');
Route::get('/bajas/estado/{ticket}/{baja}/{nomfilexml?}','ComprobantesController@ConsultarTicket');
Route::get('/listarbajas/{idcabecera}/{tdocod}','BajasController@listarbajas');
Route::get('/PanelClientes/{idempresa}/{idnegocio}','ComprobantesController@ingresarpanel');
Route::get('/boletadetalle/{tdocod}/{cpe?}','ComprobantesController@crearboleta');
Route::get('/consultarempresas','FuncionesController@consultarempresas');
Route::get('/Boleta/create/{tdocod}','ComprobantesController@create');
Route::get('/SisFact/create/{tdocod}/{cpe?}','ComprobantesController@create');

//SALIDAS
Route::get('/nuevasalida','ComprobantesController@salidasproductos');
Route::get('/salidas','ComprobantesController@indexsalidas');
Route::get('/detallesalidas/{id}','ComprobantesController@detalle_salidas');
Route::get('/editarsalidasproductos/{id}','ComprobantesController@editar_salidas_productos');

Route::post('/registrarsalida','PuntoVentaController@registrarsalida');
Route::post('/actualizarsalida','PuntoVentaController@actualizar_salida');

Route::get('/reporte/salidas','ReportesController@reportes_salidas');
Route::get('/reporte/salidas/pdf','ReportesController@pdf_reportes_salidas');

//PRESAMOS
Route::get('/prestamos/{id?}','PrestamosController@prestamos');
Route::post('/registrarprestamo','PrestamosController@registrarprestamo');

Route::get('/albergues','PuntoVentaController@albergues');
Route::get('/listarpedidos','PuntoVentaController@listarpedidosalbergues');
Route::post('/buscarpedidosalbergues','PuntoVentaController@buscarpedidosalbergues');
Route::post('/modificarpedidoalbergue','PuntoVentaController@modificarpedidoalbergue');
Route::get('/modificarpedidoalber/{pedido}','PuntoVentaController@modificarpedidoalber');

Route::get('/modificarpedidosalbergue/{pedido}','PuntoVentaController@modificarpedidosalbergue');

Route::post('/buscarpedidoalbergue','PuntoVentaController@buscarpedidoalbergue');

Route::get('/pedidoalbergue','PuntoVentaController@pedidoalbergue');
Route::get('/calcularpedidoalbergue/{prog}/{cantidad}','PuntoVentaController@calcularpedidoalbergue');
Route::post('/registrarpedidoalbergue','PuntoVentaController@registrarpedidoalbergue');
Route::get('/listarpedidoalbergue','PuntoVentaController@listarpedidoalbergue');
Route::get('/detallepedidoalbergue/{id}','PuntoVentaController@detallepedidoalbergue');
Route::get('/eliminarpedidoalbergue/{id}','PuntoVentaController@eliminarpedidoalbergue');
Route::post('/actualizarpedidoalbergue','PuntoVentaController@actualizarpedidoalbergue');

Route::post('/actualizargasto','GastosController@actualizar_gasto');
Route::post('/actualizaringreso','IngresosController@actualizar_ingreso');
Route::get('/gastos/{codgast?}','GastosController@index');
Route::get('/ingresos/{codgast?}','IngresosController@index');

Route::get('/SisFact/comprobante/{tipo}/{tdocod}/{serdoc}/{numdoc}','ComprobantesController@webservicepdf');
Route::get('/SisFact/notacredito/{tdocod}','ComprobantesController@crearnotacredito');
Route::get('/reportecomprobante','ReportesController@ReporteComprobantes');
Route::post('/restaurantpunto','PuntoVentaController@restaurantpunto');
Route::post('/cambiarcomprobante','PuntoVentaController@cambiar_comprobante');
Route::get('/buscar_pedido/{id}','PuntoVentaController@buscar_pedido');
Route::get('/voucher/{cpe}','PuntoVentaController@voucher');
Route::post('/registrarcliente','ClientesController@store');
Route::post('/registrarclientetaller','ClientesController@registrar_cliente_taller');
Route::get('/pvgrifo/{id?}','POSGaleriaController@puntoventagrifos')->middleware(['ValidarAperturaTurno','validateAuthenticated']);
Route::resource('/clientes','ClientesController');
Route::resource('/tipocambio','TipoCambioController');

Route::get('/actualizarpro/{tipo}','ProductosController@actualizarpro');
Route::resource('/mesas','POSMesasController');
Route::post('/productos/subirimagen/{id}','ProductosController@subir_imagen');
Route::resource('/productos','ProductosController');
Route::get('/posmv/{tdocod?}','PuntoVentaController@pos_movil')->middleware(['ValidarAperturaTurno','ValidarTipoCambio','validateAuthenticated']);


Route::resource('/restaurant','PuntoVentaController');
Route::resource('/categorias','CategoriasController');
Route::resource('/subcategorias','SubcategoriasController');
Route::resource('/tipoproducto','TipoProductoController');
Route::resource('/colores','ColoresController');
Route::resource('/menus','MenusController');
Route::resource('/compras','ComprasController');
Route::resource('/ordenescompra','OrdenesCompraController');



Route::resource('/promociones','PromocionesController');
Route::resource('/tragos','TragosController');
ROute::resource('/distribuidor','DistribuidorController');
Route::resource('/mesa','MesasController');
Route::resource('/proveedor','ProveedorController');
Route::resource('/cierre','ImprimirTickeController');

Route::post('/consultarprod','ProductosController@consultarproductos')->name('Productos.consultarproductos');
Route::post('/consultarserv','ProductosController@consultarservicios')->name('Productos.consultarservicios');
Route::post('/consultarrepu','ProductosController@consultarrepuestos')->name('Productos.consultarrepuestos');

Route::get('/consultarprod','ProductosController@consultarproductos');

Route::get('/consultarproductosbarra','ProductosController@consultarproductosbarra');
Route::post('/consultarcatalogo','ProductosController@consultar_catalogo');

Route::post('/consultarprodcompra','ProductosController@consultarproductoscompra')->name('Productos.consultarproductoscompra');


Route::get('/consultarproductocompra',array('as'=>'consultarproductocompra','uses'=>'ProductosController@consultarproductocompra'));

Route::get('/consultarproducto',array('as'=>'consultarproducto','uses'=>'ProductosController@consultarproducto'));
Route::get('/consultarproductonomcompra',array('as'=>'consultarproductonomcompra','uses'=>'ProductosController@consultarproductonomcompra'));

Route::get('/consultarprodalm/{sucursal}/{almacen}','ProductosController@consultarproductosalm');
Route::get('/busquedaproductoalm/{valor}/{almacen}/{sucursal}','ProductosController@busquedaproductoalm');
Route::get('/consultarmenualm/{cat_cod}/{almacen}/{sucursal}','ProductosController@consultarmenualm');

Route::get('/consultarmenuinventario/{cat_cod}/{sucursal}/{almacen}','ProductosController@consultarmenuinventario');
Route::get('/consultarprodinventario/{sucursal}/{almacen}','ProductosController@consultarproductosinventario');
Route::get('/busquedaproductoinventario/{valor}/{sucursal}/{almacen}','ProductosController@busquedaproductoinventario');

Route::get('/costeoproductos/{producto}/{sucursal}','ProductosController@costeo');
Route::post('/actualizarprecios','ProductosController@actualizarprecios');
Route::get('/presentacionesproductocompra/{producto}/{sucursal}/{almacen}','ProductosController@presentacionesproductocompra');
Route::get('/presentacionesproducto/{producto}','ProductosController@presentacionesproducto');
Route::get('/presentacionesproductoinventario/{producto}/{sucursal}/{almacen}','ProductosController@presentacionesproductoinventario');


// VISTA SEARCHSTOCK STOCK DE PRODUCTOS
Route::get('/stockproductos','ProductosController@stockproductos');
Route::get('/consultastock','ProductosController@consultastockproductos');
Route::post('/consultastock','ReportesController@consulta_stock_productos');
Route::post('/exportarstockproductos','ProductosController@exportarstockproductos');
Route::post('/exportarstockexcel','ReportesController@ExportarStockProductos');
Route::post('/exportarstockpdf', 'ReportesController@ExportarStockPdf');
Route::post('/exportarstockticket', 'ReportesController@ImprimirStockTicketVista');
Route::post('/exportarproductosexcel','ReportesController@ExportarProductosExcel');
//Route::get('/inventarios/{suc?}/{alm?}/{tipo?}','ProductosController@inventarios');
Route::get('/inventariosexcel/{suc}/{alm}','ProductosController@exportar_productos_inventario');
Route::post('/importarinventario','ProductosController@importar_inventario');
Route::post('/actualizarinventario','ProductosController@actualizarinventario');

//inventarios
Route::get('/inventarios','InventariosController@inventarios');
Route::post('/nuevoinventario','InventariosController@nuevoinventario');
Route::get('/inventarioregistrarproducto/{inv_cab_id}/{id_producto}/{inv_can}','InventariosController@inventario_registrar_producto');
Route::get('/inventarioeliminarproducto/{inv_cab_id}/{id_producto}/{inv_can}','InventariosController@inventario_eliminar_producto');
Route::get('/ingresarinventario/{id}','InventariosController@ingresar_inventario');


//CLIENTES
Route::get('/exportarclientes','ReportesController@exportarclientes');
Route::get('/seleccionardireccion/{id}','ClientesController@seleccionardireccion');
Route::get('/buscarclientenombre/{id}','ClientesController@buscarclientenombre');


//PRODUCCION
Route::get('/indexsalidas','ProduccionController@indexvalessalidas');
Route::get('/indexingresos','ProduccionController@indexvalesingresos');
Route::get('/ingresosproduccion/{tdocod?}','ProduccionController@ingresosproduccion');
Route::get('/salidasproduccion/{tdocod?}','ProduccionController@salidasproduccion');
Route::post('/registrarvalesalida/{tdocod?}','ProduccionController@registrar');
Route::post('/registrarvaleingreso/{tdocod?}','ProduccionController@registraringreso');
Route::get('/editarvalesalidaproduccion/{id}','ProduccionController@editarvalesalidaproduccion');
Route::get('/editarvaleingresoproduccion/{id}','ProduccionController@editarvaleingresoproduccion');
Route::post('/actualizarvalesalidaproduccion','ProduccionController@actualizarvalesalidaproduccion');
Route::post('/actualizarvaleingresoproduccion','ProduccionController@actualizarvaleingresoproduccion');

//INGRESOS Y SALIDAS DE PRODUCTOS
Route::get('/salidasproductos','IngresosSalidasProductosController@indexvalessalidas');
Route::get('/ingresosproductos','IngresosSalidasProductosController@indexvalesingresos');
Route::get('/ingresarproductos/{tdocod?}','IngresosSalidasProductosController@ingresarproductos');
Route::get('/egresarproductos/{tdocod?}','IngresosSalidasProductosController@egresarproductos');
Route::post('/registrarsalidaproducto/{tdocod?}','IngresosSalidasProductosController@registrarsalidaproducto');
Route::post('/registraringresoproducto/{tdocod?}','IngresosSalidasProductosController@registraringresoproducto');
Route::get('/editarvalesalida/{id}','IngresosSalidasProductosController@editarvalesalida');
Route::get('/editarvaleingreso/{id}','IngresosSalidasProductosController@editarvaleingreso');
Route::post('/actualizarvalesalida','IngresosSalidasProductosController@actualizarvalesalida');
Route::post('/actualizarvaleingreso','IngresosSalidasProductosController@actualizarvaleingreso');


Route::get('/editarinventario/{inventario}','ProductosController@editarinventario');

Route::post('/regcobrollevar','PuntoVentaController@reg_cobro_llevar');

Route::get('/autocomplete/{cliente}','ComprobantesController@autocomplete');
Route::get('/autocomplete1/{cliente}','ComprobantesController@autocomplete1');


Route::get('/autocompleteprov/{cliente}','ComprobantesController@autocompleteprov');
Route::get('/autocompletenom',array('as'=>'autocompletenom','uses'=>'ComprobantesController@autocompletenom'));
Route::get('/autocompletenomprov',array('as'=>'autocompletenomprov','uses'=>'ComprobantesController@autocompletenomprov'));
Route::get('/buscarcomprobante',array('as'=>'buscarcomprobante','uses'=>'ComprobantesController@buscarcomprobante'));
Route::get('/buscarcomprobantebaja',array('as'=>'buscarcomprobantebaja','uses'=>'ComprobantesController@buscarcomprobantebaja'));
Route::get('/buscarcomprobantelista',array('as'=>'buscarcomprobantelista','uses'=>'ComprobantesController@buscarcomprobantelista'));
Route::get('/buscardocumentosbajas',array('as'=>'buscardocumentosbajas','uses'=>'BajasController@buscardocumentosbajas'));
Route::get('/consultarcambio',array('as'=>'consultarcambio','uses'=>'ComprobantesController@consultarcambio'));
Route::get('/consultartdi',array('as'=>'consultartdi','uses'=>'ComprobantesController@consultartdi'));

Route::get('/consultarticketbaja/{comprobante}','ComprobantesController@consultarticketbaja');
Route::get('/consultarticket/{comprobante}','PuntoVentaController@consultarticket');
Route::get('/clientes/contrasena/{id}','ClientesController@editarContrasena');
Route::get('/formbajacomprobante/{IdCpe_cabecera}','ComprobantesController@formbajacomprobante');
Route::get('/bajacomprobante','ComprobantesController@bajacomprobante');
Route::get('/registraranulacion/{id}/{motivo}','PuntoVentaController@registraranulacion');
Route::post('/clientes/cambiar/contrasena','ClientesController@cambiarContrasena');
Route::get('/verificarcomprobante','ComprobantesController@verificarcomprobante');
Route::resource('/pisos','PisosController');
Route::get('/consultartipcambio/','ComprobantesController@consultartipcambio');
Route::get('/consultartipocomp/','TipoDocumentoController@consultartipodocumento');
Route::get('/comprobantes/send/{cabfile}/{puerto}/{idcabecera}/{tdocod}','ComprobantesController@webservicesend');
Route::get('/comprobantes/generarxml/{cabfile}/{puerto}/{idcabecera}/{tdocod}','ComprobantesController@webservice');
Route::post('/SisFact/registrarnota','PuntoVentaController@registrarnota');
Route::get('/tiponota/{tdocod?}/{idcabecera?}/{ncdcod}','ComprobantesController@tiponotacd');
Route::get('/emitirnota','ComprobantesController@emitirnota');
Route::get('/listarnotas/{idcabecera}','ComprobantesController@listarnotas');
Route::get('/SisFact/factpdf/{tdocod}/{doccod}/{idcabecera}','ComprobantesController@facturapdf');
Route::get('/consultarruc','ComprobantesController@consultaruc');

Route::get('/consultarmenu/{cat_cod}','ProductosController@consultarmenu');
Route::get('/consultarproductosservicio/{tipo?}/{prog}','ProductosController@consultarproductosservicio');
Route::get('/consultarservicio/{tipo}','ProductosController@consultarservicio');
Route::get('/busquedaproducto/{valor}','ProductosController@busquedaproducto');

Route::post('/registrarpedidos','PuntoVentaController@registrarpedidos');

Route::get('/consultarmenucompra/{cat_cod}/{sucursal}/{almacen}','ProductosController@consultarmenucompra');
Route::get('/consultarprodcompra/{sucursal}/{almacen}','ProductosController@consultarproductoscompra');
Route::get('/busquedaproductocompra/{valor}/{sucursal}/{almacen}','ProductosController@busquedaproductocompra');

Route::get('/consultarmenucomanda/{cat_cod}','ProductosController@consultarmenucomanda');
Route::get('/busquedaproductocomanda/{valor}','ProductosController@busquedaproductocomanda');
Route::get('/consultarmenucobrar/{cat_cod}','ProductosController@consultarmenucobrar');
Route::get('/consultarcategorias','ProductosController@consultarcategorias');
Route::get('/pedido/{mesa}','PuntoVentaController@tomarpedido');
Route::get('/pedidoadicionar/{mesa}','PuntoVentaController@editarpedido');
Route::get('/editarpedidollevar/{pedido}','PuntoVentaController@editarpedidollevar');
Route::get('/mostrarpedido/{pedido}','PuntoVentaController@mostrarpedidollevar');

Route::get('/generarcompra/{compra}','ComprasController@editarcompra');
Route::get('/editarcompra/{id}','ComprasController@editarcompra');
Route::get('/editarcomp/{id}','ComprasController@editar_compra');

Route::get('/notascreditoscompras','ComprasController@indexnotascreditos');
Route::post('/registrarnotacompra','ComprasController@registrar_nota_credito_compra');
Route::post('/actualizarnotacompra','ComprasController@actualizar_nota_credito_compra');
Route::get('/notacreditocompra/crear','ComprasController@nota_credito_compra');
Route::get('/editarnotacompra/{id}','ComprasController@editar_nota_credito_compra');

Route::post('/actualizarcompra','ComprasController@actualizarcompra');
Route::post('/actualizarcomp','ComprasController@actualizar_compra');
Route::get('/detallecompras/{compra}/{tipo}','ComprasController@detallecompras');
Route::get('/detalleorden/{compra}/{tipo}','OrdenesCompraController@detallecompras');

Route::get('/detallegastos/{gasto}','GastosController@detallegastos');
Route::get('/compra/crear','ComprasController@compraproductos');
Route::get('/ordenes/crear','OrdenesCompraController@crear_orden_compra');
Route::get('/gasto/crear','GastosController@gastoproductos')->middleware('ValidarAperturaTurno');
Route::get('/ingreso/crear','IngresosController@gastoproductos')->middleware('ValidarAperturaTurno');
Route::resource('/gastos','GastosController');
Route::resource('/ingresos','IngresosController');
Route::get('/factmesa','PuntoVentaController@facturacionmesa')->middleware('ValidarAperturaTurno');
Route::get('/cobrarmesa/{mesa}','PuntoVentaController@cobrarmesa')->middleware('ValidarAperturaTurno');
Route::get('/cobrarllevar/{pedido}','PuntoVentaController@cobrar_llevar')->middleware('ValidarAperturaTurno');
Route::get('/listallevar/{id_ped?}/{tipo?}','PuntoVentaController@listar_pedido_llevar');
Route::get('/pedidollevar','PuntoVentaController@pedido_llevar');
Route::post('/mesaadicionar','POSMesasController@adicionarpedido');
Route::post('/pedidollevaradicionar','POSMesasController@adicionarpedidollevar');
Route::post('/regpedidollevar','PuntoVentaController@registrar_pedido_llevar');

Route::get('/asignarreceta/{producto}','ProductosController@asignarreceta');
Route::post('/registrarreceta','ProductosController@registrarreceta');

Route::get('/asignarcombo/{producto}','ProductosController@asignarcombo');
Route::post('/registrarcombo','ProductosController@registrarcombo');


Route::get('/mostrarmesas/{id_ped}/{tipo?}','POSMesasController@mostrar_mesas');
//Route::get('/aperturarcaja','PuntoVentaController@aperturacaja');
Route::get('/movimientos','AlmacenController@movimientos');
//Route::get('/cerrarcaja','PuntoVentaController@cerrarcaja');
Route::get('/imprimirconsolidado/{fecfin}/{fecin}','ImprimirTickeController@imprimirdetalleventas');
Route::get('/SisFact/factpdf/{tdocod}/{doccod}/{idcabecera}/{archivo}','ComprobantesController@facturapdf');
Route::post('/consolidadoproductos','ImprimirTickeController@consolidadoproductos');


Route::post('/import-productos','ProductosController@ImportarProductos');
Route::get('/exportar-productos-excel','ProductosController@exportar_productos_excel');
Route::get('/generarcodigo','ProductosController@generarcodigo');
Route::get('/reporteventa','ReportesController@reportepantalla');
Route::get('/reportecompra','ReportesController@reportecompra');

Route::get('/reportealbergues','ReportesController@reportealbergues');

Route::get('/reportestock', 'ReportesController@reportestock');
Route::get('/exportarstockproductos','ReportesController@ExportarStockProductos');
Route::get('/exportarstockvalorizado','ReportesController@exportarstockvalorizado');
Route::post('/mostrarventas','ReportesController@MostrarVentas');
Route::post('/buscarstock','ProductosController@BuscarStock');
Route::get('/buscarproducto','ProductosController@BuscarProducto');
Route::post('/reportecomprobantes','ReportesController@ReporteComprobantes');

Route::get('/ReporteCaja','ReportesController@ReporteCaja');

Route::get('/impresoras/listarimpresoras/{id_empresa_negocio}','ConfiguracionSistemaController@listarimpresoras');
Route::get('/impresoras/crear/{id_empresa_negocio}','ConfiguracionSistemaController@crearimpresoras');
Route::get('/impresoras/editar/{Id}/{id_empresa_negocio}','ConfiguracionSistemaController@editarimpresoras');
Route::post('/impresoras/registrarimpresora','ConfiguracionSistemaController@registrarimpresora');
Route::post('/impresoras/eliminarimpresora','ConfiguracionSistemaController@eliminarimpresora');
Route::post('/impresoras/actualizarimpresora','ConfiguracionSistemaController@actualizarimpresora');

Route::get('/impresorapredeterminada/{impresora}','ConfiguracionSistemaController@impresorapredeterminada');
Route::get('/mediopredeterminado/{impresora}','MediosPagosController@mediopredeterminado');

Route::get('/rutas/listarrutas','ConfiguracionSistemaController@listarrutas');
Route::get('/rutas/crear','ConfiguracionSistemaController@crearrutas');
Route::get('/rutas/editar/{Id}','ConfiguracionSistemaController@editarrutas');
Route::post('/rutas/registrarruta','ConfiguracionSistemaController@registrarruta');
Route::post('/rutas/eliminarruta','ConfiguracionSistemaController@eliminarruta');
Route::post('/rutas/actualizarruta','ConfiguracionSistemaController@actualizarruta');

Route::get('/almacen/listaralmacenes/{sucursal?}','AlmacenController@listaralmacenes');
Route::get('/almacenpredeterminada/{almacen}/{sucursal}','AlmacenController@almacenpredeterminada');

Route::get('/crearalmacenes','AlmacenController@crearalmacenes');
Route::get('/editaralmacenes/{id}','AlmacenController@editaralmacenes');
Route::post('/registraralmacenes','AlmacenController@registraralmacenes');
Route::post('/actualizaralmacenes','AlmacenController@actualizaralmacenes');
Route::post('/eliminaralmacenes','AlmacenController@eliminaralmacenes');
Route::resource('/almacen','AlmacenController');

Route::get('/consignacion','ConsignacionController@listarconsignaciones');
Route::get('/enviarjson/{comprobante}','ComprobantesController@GenerarJSON');


//impresion A4

Route::get('/generarpdfgeneral/{venta}','PuntoVentaController@generarpdfgeneral');
Route::get('/descargar/{venta}/{tipo}','PuntoVentaController@descargar');
Route::get('/descargarorden/{file}','OrdenesCompraController@descargarorden');

//ordenes de compra
Route::get('/editarorden/{id}','OrdenesCompraController@editar_orden_compra');
Route::post('/actualizarordencompra','OrdenesCompraController@actualizar_orden_compra');

//REPORTES
Route::get('/imprimirreportes','ReportesController@imprimirreportes');
Route::post('/imprimirreporte','ReportesController@imprimirreporte');

Route::get('/movimientoscaja','CajaController@movimientoscaja');

Route::post('/movimientosbancarios','CajaController@movimientosbancarios');
Route::get('/movimientosbancarios','CajaController@movimientosbancarios');
Route::get('/movimientosbancarios/crear','CajaController@ingresarmovimientosbancarios');
Route::get('/movimientosbancarios/editar/{movimiento}','CajaController@editarmovimientosbancarios');
Route::post('/movimientosbancarios/registrar','CajaController@registrarmovimientosbancarios');
Route::post('/movimientosbancarios/actualizar','CajaController@actualizarmovimientosbancarios');
Route::post('/movimientosbancarios/eliminar','CajaController@eliminarmovimientosbancarios');

Route::post('/movimientoscaja','CajaController@movimientoscaja');
Route::get('/movimientoscaja','CajaController@movimientoscaja');
Route::get('/movimientoscaja/crear','CajaController@ingresarmovimientoscaja');
Route::get('/movimientoscaja/editar/{movimiento}','CajaController@editarmovimientoscaja');
Route::post('/movimientoscaja/registrar','CajaController@registrarmovimientoscaja');
Route::post('/movimientoscaja/actualizar','CajaController@actualizarmovimientoscaja');
Route::post('/movimientoscaja/eliminar','CajaController@eliminarmovimientoscaja');


Route::resource('/cuentasbancarias','CuentasBancariasController');

Route::resource('/conceptosbancarios','ConceptosBancariosController');
Route::resource('/tiposdocumentos','TiposDocumentosController');

Route::get('/editarmovimiento/{id}','AlmacenController@editarmovimiento');
Route::get('/editarmovimientoalmacenes/{id}','AlmacenController@editarmovimientoalmacenes');

Route::post('/actualizarmovimiento','AlmacenController@actualizarmovimiento');

Route::resource('/bancos','BancosController');
Route::resource('/tiposcaja','TiposCajaController');
Route::resource('/mediospagos','MediosPagosController');

Route::post('/enviarcorreo','ComprobantesController@enviar_comprobante');

Route::post('/pagoservicio','PuntoVentaController@pagoservicio');

Route::get('/puntoventa','PuntoVentaController@puntoventa');

Route::get('/detalleventa/{venta}','PuntoVentaController@detalleventa');

//RUTAS PARA MIGRAR INFORMACION A SISTEMA WILCAT
Route::get('/ventasdbf/{fecin}/{fecfin}','IntegracionSistemaController@registrar_ventas_dbf');
Route::get('/comprasdbf/{fecin?}/{fecfin?}','IntegracionSistemaController@registrar_compras_dbf');
Route::get('/clientesdbf','IntegracionSistemaController@registrar_clientes_proveedores_dbf');

//RUTAS FACTURACION ELECTRONICA OSE
Route::get('/enviarose/{archivo}','PuntoVentaController@enviar_ose');


Route::get('/venta/crearguia/{id}','GuiasRemisionController@crear_guia');
Route::post('/venta/generarguia','GuiasRemisionController@generar_guia_venta');
Route::get('/anularguiaremision/{id}/{motivo}','GuiasRemisionController@anular_guia_remision');


//GUIAS DE REMISION
Route::resource('/guiasremision','GuiasRemisionController');
Route::get('/guiaremision/create/{comprobante?}','GuiasRemisionController@create');
Route::get('/buscarubigeo',array('as'=>'buscarubigeo','uses'=>'GuiasRemisionController@buscarubigeo'));
Route::get('/autocomplete',array('as'=>'autocomplete','uses'=>'ComprobantesController@autocomplete'));
Route::get('/descargarguia/{guia}/{tipo}','GuiasRemisionController@descargar');

Route::get('/enviar_guia_sunat/{id}','GuiasRemisionController@enviar_guia_sunat');
Route::get('/consultarticketgre/{id}/{ticket?}','GuiasRemisionController@consultar_ticket_gre');
Route::get('/descargarcdrguia/{id}','GuiasRemisionController@guardar_cdr_guia');


//WEBSERVICE

Route::post('/api/invoice','PuntoVentaController@recibir_xml');
Route::get('/enviar_xml/{archivo}','PuntoVentaController@enviar_xml');

});

//CONFIGURACION INICIAL DE CREAR EMPRESA
Route::get('/config','EmpresaController@crearempresa');
Route::get('empresas/create','EmpresaController@create');
Route::resource('administrador/empresas','EmpresaController');

//------------------------RECURSOS HUMANOS------------------------------------------------//
Route::get('/gastospersonal/{codgast?}','RecursosHumanosController@index');
Route::get('/gastospersonal/{gasto}','RecursosHumanosController@detallegastos');
Route::get('/gastopersonal/crear','RecursosHumanosController@gastospersonal');
Route::resource('/gastospersonal','RecursosHumanosController');


//----------------RUTAS VALETA PARKING----------------------------------------------------//

//INGRESO DE LOS VEHICULOS
Route::get('/ingresovehiculo','ValetParking\PuntoVentaController@ingresovehiculo');
Route::post('/registraringreso','ValetParking\PuntoVentaController@registraringreso')->middleware('ValidarAperturaTurno');
Route::post('/registrarcobroplaca','ValetParking\PuntoVentaController@registrarcobroplaca');
Route::post('/eliminaringreso','ValetParking\PuntoVentaController@eliminaringreso');

//TARIFAS	
Route::get('/editartarifa/{id}','ValetParking\TarifasController@edit');
Route::post('/eliminartarifa','ValetParking\TarifasController@eliminartarifa');
Route::resource('/tarifas','ValetParking\TarifasController');
Route::get('/buscartarifas/{tipo}','ValetParking\TarifasController@BuscarTarifas');


//PLACAS

Route::resource('/placas','ValetParking\PlacasController');
Route::get('/buscarplaca/{placa}','ValetParking\PuntoVentaController@buscarplaca')->middleware('ValidarAperturaTurno');
Route::get('/cobrarplaca/{placa}','ValetParking\PuntoVentaController@cobrarplaca')->middleware('ValidarAperturaTurno');

//ORDENES DE SERVICIO
Route::get('/ordeneservicio/crear','OrdenesServiciosController@ordenservicio');
Route::resource('/ordenesservicios','OrdenesServiciosController');
Route::get('/detalleordenservicio/{id}','OrdenesServiciosController@detalleordenservicio');

//FARMACIAS
Route::get('/laboratorio/create','LaboratorioController@create');
Route::resource('/laboratorio','LaboratorioController');
Route::resource('/tiposmedicamentos','TipoMedicamentoController');
Route::resource('/principioactivo','PrincipioActivoController');

//RESTAURANTE
Route::get('/consola','RestauranteController@consola');
Route::get('/consolacaja/{id?}','RestauranteController@indexcaja');

Route::get('/reporte/autoconsumo/{id}', 'TurnosController@imprimirAutoconsumoTurno')->name('reporte.autoconsumo.turno');

Route::get('/ventacaja/{tdocod?}','PuntoVentaController@caja')->middleware('ValidarAperturaTurno');
Route::get('/ventacaja1/{tdocod?}','PuntoVentaController@caja_tactil_1')->middleware('ValidarAperturaTurno');
Route::get('/ventacaja2/{tdocod?}','PuntoVentaController@caja_tactil_2')->middleware('ValidarAperturaTurno');
Route::get('/ventacaja3/{tdocod?}','PuntoVentaController@caja_tactil_3')->middleware('ValidarAperturaTurno');
Route::get('/ventacaja4/{tdocod?}','PuntoVentaController@caja_tactil_4')->middleware('ValidarAperturaTurno');
Route::get('/ventacaja5/{tdocod?}','PuntoVentaController@caja_tactil_5')->middleware('ValidarAperturaTurno');
Route::get('/ventacaja6/{tdocod?}','PuntoVentaController@caja_tactil_6')->middleware('ValidarAperturaTurno');



Route::get('/buscarcarta/{id?}/{cat?}','ProductosController@buscarcarta');
Route::get('/buscarcartaimg/{id?}/{cat?}','ProductosController@buscarcartaimg');
Route::get('/buscarcartallevar/{id?}/{cat?}','ProductosController@buscarcartallevar');
Route::get('/buscarmesas/{id}','MesasController@buscar_mesas');
Route::get('/buscarmesasmobil/{id}','MesasController@buscar_mesas_mobil');
Route::get('/buscarmesasdesocupadas','MesasController@buscar_mesas_desocupadas');
Route::get('/buscarmesasdesunir/{id}','MesasController@buscar_mesas_desunir');
Route::get('/buscarmesasdesocupadasunir/{id?}','MesasController@buscar_mesas_desocupadas_unir');
Route::get('/buscarmesascaja/{id}','MesasController@buscar_mesas_caja');
Route::get('/buscarpedidomesa/{id}','RestauranteController@buscar_pedido_mesa');
Route::get('/buscarpedidollevardelivery/{id}','RestauranteController@buscar_pedido_llevar_delivery');
Route::post('/registrarcomanda','RestauranteController@registrar_comanda');
Route::post('/actualizarcomanda','RestauranteController@actualizar_comanda');
Route::get('/eliminaritem/{item}/{pedido}','RestauranteController@eliminar_item');
Route::get('/eliminarpedido/{pedido}','RestauranteController@eliminar_pedido');
Route::get('/panelsalon','RestauranteController@panel_salon');
Route::get('/panelllevar','RestauranteController@panel_llevar');
Route::get('/paneldelivery','RestauranteController@panel_delivery');
Route::post('/cambiarmesa','RestauranteController@cambiar_mesa');
Route::get('/buscarpedidos/{tipo}','RestauranteController@buscar_pedidos');
Route::get('/buscarpedidoscaja/{tipo}','RestauranteController@buscar_pedidos_caja');
Route::get('/cobrarmesa/{mesa}','RestauranteController@cobrar_mesa')->middleware('ValidarAperturaTurno');
//REGISTRARCOBRO REGISTRAR COBRO ORIGINAL
Route::post('/registrarcobro','RestauranteController@registrar_cobro');


Route::post('/registrarcobrokiosko', 'RestauranteController@registrar_cobro_kiosko')->name('restaurante.registrar_cobro_kiosko');


Route::post('/registrarcobrocs','RestauranteController@registrar_cobro_cs');
Route::post('/registrarcobroot','POSGaleriaController@registrarcobro');
Route::get('/indexcomandas','RestauranteController@indexcomandas');

Route::get('/exportarcomandasexcel', 'RestauranteController@exportarComandasExcel')->name('comandas.excel');

Route::get('/imprimircomanda/{id}','RestauranteController@imprimir_comanda');
Route::get('/imprimircomandatotal/{id}','RestauranteController@imprimir_comanda_total');
Route::get('/cseparadas/{id}','RestauranteController@cuentas_separadas');
Route::get('/validaritemsfacturados/{id1}/{id2}/{id3}','RestauranteController@validar_items_facturados');
Route::post('/unirmesas','MesasController@unir_mesas');
Route::post('/desunirmesas','MesasController@desunir_mesas');

//PUNTO DE VENTA
Route::post('/venta/registrar','PuntoVentaController@registrar_venta');
//Route::get('/pos/{tdocod?}','PuntoVentaController@punto_venta')->middleware(['ValidarAperturaTurno','ValidarTipoCambio','validateAuthenticated']);
Route::get('/pos/{tdocod?}','POSGaleriaController@index')->middleware(['ValidarAperturaTurno','ValidarTipoCambio','validateAuthenticated']);
Route::post('/registrarventa','PuntoVentaController@registrar_venta_directa')->middleware(['ValidarAperturaTurno','ValidarTipoCambio','validateAuthenticated']);

//PUNTO DE VENTA NUEVO  POS MODERNO

Route::get('/pos-nuevo/{codfact?}', 'PuntoVentaController@vistaPvnuevo')->name('vistaPvnuevo');
Route::post('/pos-nuevo', 'PuntoVentaController@pvnuevo')->name('pvnuevo');

//Route::get('/pos-nuevo', 'PuntoVentaController@vistaPvnuevo')->name('pos.nuevo');
//Route::post('/pos-nuevo/procesar', 'PuntoVentaController@pvnuevo')->name('pos.procesar');

Route::get('/editarventa/{id}','PuntoVentaController@punto_venta_editar');
Route::post('/actualizarventa','PuntoVentaController@actualizar_venta');
Route::get('/editarmp/{id}','PuntoVentaController@editar_medio_pago');
Route::post('/actualizarmp','PuntoVentaController@actualizar_venta_mp');


//Route::get('/editarventa/{id}','PuntoVentaController@editarventa');
//Route::get('/editarventa/{id}','PuntoVentaController@editarventa');

//Route::get('/editarventapos/{id}','PuntoVentaController@editarventa');
//Route::get('/editarmppos/{id}','PuntoVentaController@editar_medio_pago');

//Route::post('/actualizarventa','PuntoVentaController@actualizarventa');
//Route::post('/actualizarventa','PuntoVentaController@actualizarventa');


//IMPRESIONES PANTALLA
Route::get('/voucher/{cpe}','PuntoVentaController@voucher');
Route::get('/precuenta/{cpe}','ImprimirTicketController@imprimir_cuenta_pantalla');
Route::get('/imprimirguia/{cpe}','GuiasRemisionController@imprimirguia');

/*no tocar eliminará todo*/
//Route::get('/limpiardata','UtilitariosController@limpiardata');

//STOCK PRODUCTOS
Route::get('/ajustar_stock/{almacen}/{producto}','ProductosController@ajustar_stock');
Route::post('/registrarajustarstock','ProductosController@registrar_ajustar_stock');



//UTILITARIOS
Route::get('/utilitarios/buscarcomprobantes','UtilitariosController@buscarcomprobantes');
Route::post('/utilitarios/buscarcomprobantes','UtilitariosController@buscarcomprobantes');
Route::resource('/utilitarios','UtilitariosController');
Route::post('/generarxmlmasivo','UtilitariosController@generarxmlmasivo');
Route::post('/generarpdfmasivo','UtilitariosController@generarpdfmasivo');
Route::post('/cambiarestadosunat','UtilitariosController@CambiarEstadoSunat');
Route::post('/importarproductos','UtilitariosController@importar_productos');
Route::post('/importarpresentaciones','UtilitariosController@importar_presentaciones');
Route::post('/importarclientes','UtilitariosController@importar_clientes');
Route::post('/importarproveedores','UtilitariosController@importar_proveedores');
Route::get('/descargarformato/{tipo}','UtilitariosController@descargar_formatos');
Route::get('/actualizarcabecera','UtilitariosController@actualizar_cabecera');
Route::get('/generarcodigoscabecera','UtilitariosController@generar_codigo_movimiento');
Route::get('/importarventasexcel','UtilitariosController@importar_ventas_excel');
Route::post('/registrarventasexcel','UtilitariosController@registrar_ventas_excel');
Route::get('/backupbd','UtilitariosController@backup_bd');

//REPORTE
Route::get('/reporteajustes','ReportesController@listar_ajuste');
Route::post('/reporteajustes','ReportesController@listar_ajuste');
Route::post('/reporteventasexcel','ReportesController@reporte_ventas');
Route::get('/reporteinventario','ReportesController@reporte_inventario');
Route::post('/reporteinventarioexcel','ReportesController@reporte_inventario_excel');
Route::post('/reporteinventario','ReportesController@reporte_inventario');
Route::post('/reporteinventariopdf','ReportesController@reporte_inventario_pdf');
Route::post('/reportecompras','ReportesController@reporte_compras');
Route::post('/reportecompraspdf','ReportesController@reporte_compras_pdf');
Route::post('/reportecomprasexcel','ReportesController@reporte_compras_excel');

//CONTINGENCIA
Route::get('/contingencia','POSGaleriaController@indexcontingencia');
Route::post('/registrarcontingencia','PuntoVentaController@registrarcontingencia');
Route::post('/actualizarcontingencia','PuntoVentaController@actualizarcontingencia');


//AUTOMOTRIZ
Route::resource('/combustible','CombustibleController');
Route::resource('/tecnicos','TecnicosController');



//TALLER ELECTRODOMESTICOS
Route::get('/ordeneselectro/{id?}','TallerElectrodomesticosController@ordenes');
Route::get('/ordenessuper','TallerElectrodomesticosController@ordenes_supervisor');
Route::get('/ordenescoor','TallerElectrodomesticosController@ordenes_coordinador');
Route::get('/ordenestec','TallerElectrodomesticosController@ordenes_tecnico');
Route::get('/ordenesratec','TallerElectrodomesticosController@ordenes_reasignar_tecnico');
Route::get('/nuevaordenelectro','TallerElectrodomesticosController@nueva_orden_trabajo');
Route::post('/registrarordenelectro','TallerElectrodomesticosController@registrar_orden_trabajo');
Route::get('/seleccionarequipos','TallerElectrodomesticosController@seleccionarequipos');
Route::post('/asignartecnico','TallerElectrodomesticosController@asignar_tecnico');
Route::post('/reasignartecnico','TallerElectrodomesticosController@reasignar_tecnico');
Route::post('/asignarcoordinador','TallerElectrodomesticosController@asignar_coordinador');
Route::post('/recepcionarorden','TallerElectrodomesticosController@recepcionar_supervisor');
Route::get('/atencionot','TallerElectrodomesticosController@ordenes_atencion');
Route::get('/atencioncc','TallerElectrodomesticosController@ordenes_control_calidad');
Route::get('/ordenesaprobadas','TallerElectrodomesticosController@ordenes_aprobadas');
Route::get('/ordenesobservadas','TallerElectrodomesticosController@ordenes_observadas');
Route::get('/editarordent/{id}','TallerElectrodomesticosController@editar_orden_trabajo');
Route::get('/editarentregar/{id}','TallerElectrodomesticosController@editar_orden_entregar');
Route::resource('/fallas','FallasController');
Route::resource('/evaluaciones','EvaluacionesController');
Route::post('/actualizarot','TallerElectrodomesticosController@actualizar_orden_trabajo');
Route::post('/actualizarottec','TallerElectrodomesticosController@actualizar_orden_trabajo_tecnico');
Route::post('/actualizarotalm','TallerElectrodomesticosController@actualizar_orden_trabajo_almacen');
Route::post('/actualizarottecfin','TallerElectrodomesticosController@finalizar_orden_trabajo_tecnico');
Route::post('/observarcc','TallerElectrodomesticosController@observar_control_calidad');
Route::post('/aceptarcc','TallerElectrodomesticosController@aceptar_control_calidad');
Route::get('/equiposentregar','TallerElectrodomesticosController@equipos_entregar');
Route::get('/equiposentregados','TallerElectrodomesticosController@equipos_entregados');
Route::post('/entregarequipo','TallerElectrodomesticosController@registrar_entregar_equipo');
Route::resource('/servicios','ServiciosController');
Route::resource('/equipos','EquiposController');
Route::resource('/repuestos','RepuestosController');
Route::get('/editarservicio/{id}/{sucursal}','ServiciosController@edit');
Route::get('/editarrepuesto/{id}/{sucursal}','RepuestosController@edit');
Route::get('/editarequipo/{id}/{sucursal}','EquiposController@edit');
Route::get('/reportestaller','ReportesController@reportes_taller');
Route::post('/generarreporteordenes','ReportesController@generar_reportes_taller');
Route::post('/generarreporteordenesexcel','ReportesController@generar_reportes_taller_excel');
Route::post('/generarreporteordenespdf','ReportesController@generar_reportes_taller_pdf');
Route::get('/consultarordenes','TallerElectrodomesticosController@consultar_ordenes');
Route::get('/visualizarorden/{id}','TallerElectrodomesticosController@visualizar_orden');
Route::get('/solicitudesot','TallerElectrodomesticosController@pedidos_repuestos_ordenes_trabajo');



//TALLER COMPUTADORAS
Route::get('/ordenescompu/{id?}','TallerComputadorasController@ordenes');
Route::get('/nuevaordencompu','TallerComputadorasController@nueva_orden_trabajo');
Route::get('/atencionotcompu','TallerComputadorasController@ordenes_atencion');
Route::post('/registrarordencompu','TallerComputadorasController@registrar_orden_trabajo');
Route::get('/editarordencompu/{id}','TallerComputadorasController@editar_orden_trabajo');
Route::get('/editarentregarcompu/{id}','TallerComputadorasController@editar_orden_entregar');
Route::post('/actualizarotcompu','TallerComputadorasController@actualizar_orden_trabajo');
Route::post('/actualizarotcomputec','TallerComputadorasController@actualizar_orden_trabajo_tecnico');
Route::post('/actualizarotcompualm','TallerComputadorasController@actualizar_orden_trabajo_almacen');
Route::post('/actualizarotcomputecfin','TallerComputadorasController@finalizar_orden_trabajo_tecnico');
Route::get('/equiposentregarcompu','TallerComputadorasController@equipos_entregar');
Route::get('/equiposentregadoscompu','TallerComputadorasController@equipos_entregados');
Route::post('/entregarequipocompu','TallerComputadorasController@registrar_entregar_equipo');
Route::get('/consultarordenescompu','TallerComputadorasController@consultar_ordenes');
Route::get('/visualizarordencompu/{id}','TallerComputadorasController@visualizar_orden');
Route::get('/solicitudescompuot','TallerComputadorasController@pedidos_repuestos_ordenes_trabajo');
Route::resource('/tipoequipo','TipoEquipoController');
Route::post('/asignartecnicocompu','TallerComputadorasController@asignar_tecnico');
Route::post('/reasignartecnicocompu','TallerComputadorasController@reasignar_tecnico');
Route::get('/ordenesteccompu','TallerComputadorasController@ordenes_tecnico');


Route::get('/vrapida/{id?}','PuntoVentaController@venta_rapida');


//MIGRAR A CONCAR
Route::post('/ventasconcardbf','IntegracionSistemaController@ventas_concar_dbf');
Route::get('/confconcar','ConfiguracionContableController@editar');
Route::post('/regconfconcar','ConfiguracionContableController@actualizar_configuracion_concar');

Route::get('/cantidad_monedas/{cantidad}','ComprobantesController@obtener_monedas');

Route::get('/historiaclinica','HistoriasClinicasController@index');
Route::post('/registraratencion','HistoriasClinicasController@registrar_atencion');
Route::get('/editarcita/{id}','HistoriasClinicasController@editar_cita');
Route::post('/actualizarcita','HistoriasClinicasController@actualizar_cita');
Route::get('/historia/{id}','HistoriasClinicasController@historia_paciente');
Route::get('/historiasasignadas','HistoriasClinicasController@historia_especialista');
Route::get('/atender/{id}','HistoriasClinicasController@atender_paciente');
Route::get('/editaratencion/{id}','HistoriasClinicasController@editar_atencion');
Route::post('/registrardiagnostico','HistoriasClinicasController@registrar_diagnostico');
Route::post('/eliminarhistoria','HistoriasClinicasController@eliminar_historia_clinica');
Route::post('/eliminarcita','HistoriasClinicasController@eliminar_cita');

Route::get('/descargar_comprobante/{nombre}','PuntoVentaController@descargar_comprobante');
Route::post('/enviar_whastapp','PuntoVentaController@enviar_whastapp');

//SIRE
Route::get('/sire/buscar','SireController@buscarRegistrosVentasCompras');
Route::get('/sire/sunat','SireController@consultarSireSunat');
Route::post('/generarsire','SireController@generarSire');

Route::get('/sire/obtenertoken','SireController@obtenerToken');
Route::post('/sire/descargarpropuesta','SireController@descargarPropuesta');
Route::get('/sire/consultarticket/{solicitud}','SireController@ConsultarTicket');
Route::get('/sire/descargarzip/{solicitud}','SireController@descargarZip');
Route::get('/sire/descargarcsv/{solicitud}','SireController@descargarCsv');
Route::get('/sire/ventasexcel/{solicitud}','SireController@generar_ventas_excel');
Route::get('/sire/comprasexcel/{solicitud}','SireController@generar_compras_excel');

Route::get('/sire/ventasconcar/{solicitud}','SireController@generar_ventas_concar');
Route::get('/sire/comprasconcar/{solicitud}','SireController@generar_compras_concar');

//REPORTE VENTAS

Route::get('/reportesunat','ReporteSunatController@index');
Route::post('/reportesunat/venta/generar','ReporteSunatController@generar_venta_txt');
Route::post('/reportesunat/compra/generar','ReporteSunatController@generar_compra_txt');



//REPORTE VENTAS
Route::get('/reporteventas/{id}','ReportesVentasController@reporteVentas');
Route::post('/generarreporteventas','ReportesVentasController@generarReporteVentas');
Route::post('/generarexcelventas','ReportesVentasController@generarExcelVentas');
Route::post('/generarpdfventas','ReportesVentasController@generarPDFVentas');

Route::get('/autoconsumos', 'ComprobantesController@indexAutoconsumos')->name('autoconsumos.index');


//REPORTE GASTOS
Route::get('/reportegastos','ReporteGastosController@reporteGastos');
Route::post('/generarreportegastos','ReporteGastosController@generarReporteGastos');
Route::post('/generarexcelgastos','ReporteGastosController@generarExcelGastos');
Route::post('/generarpdfgastos','ReporteGastosController@generarPDFGastos');

//REPORTES KARDEX
Route::get('/kardex','ReporteKardexController@kardex');
Route::post('/kardex','ReporteKardexController@kardex');
Route::post('/buscarkardex','ReporteKardexController@buscarkardex');
Route::post('/generarkardexpdf','ReporteKardexController@generarkardexpdf');
Route::post('/generarkardexexcel','ReporteKardexController@generarkardexexcel');

//REGISTRO DE COMBOS
Route::get('/combos','CombosController@index');
Route::get('/combos/crear','CombosController@crear_combo');
Route::get('/combos/editar/{id}/{suc}','CombosController@editar_combo');
Route::post('/combos/eliminar/{id}','CombosController@eliminar_combo');
Route::post('/combos/registrar','CombosController@registrar_combo');
Route::post('/combos/actualizar','CombosController@actualizar_combo');
Route::resource('/combos','CombosController');

//REPORTE VENTAS
Route::get('/reportecompras/{id}','ReportesComprasController@reporteCompras');
Route::post('/generarreportecompras','ReportesComprasController@generarReporteCompras');
Route::post('/generarexcelcompras','ReportesComprasController@generarExcelCompras');
Route::post('/generarpdfcompras','ReportesComprasController@generarPDFCompras');

/*
|--------------------------------------------------------------------------
| Kiosko de Autoconsumo (Punto de Venta Táctil para Cliente)
|--------------------------------------------------------------------------
*/

// Envolvemos todo el Kiosko para asegurar que haya sesión y llene el user_id
Route::group(['middleware' => 'validateAuthenticated'], function () {
    
    // 1. Pantalla de Bienvenida
    Route::get('/bienvenidos', 'KioskoController@bienvenida')->name('kiosko.bienvenida');

    // 2. Pantalla de Selección de Mesa / Servicio
    Route::get('/seleccion', 'KioskoController@seleccionServicio')->name('kiosko.seleccion_servicio');
    Route::post('/kiosko/set-service-data', 'KioskoController@setServiceData')->name('kiosko.set_service_data');
    Route::get('/kiosko/mesas/{piso_id}', 'KioskoController@getMesasPorPiso')->name('kiosko.get_mesas_por_piso');
    Route::get('/buscar-cliente', 'KioskoController@buscarCliente')->name('buscar.cliente');
    Route::get('/kiosko/autocomplete-client', 'KioskoController@autocompleteClient')->name('kiosko.autocomplete_client');
    Route::post('/kiosko/autocomplete-client', 'KioskoController@autocompleteClient');

    // 3. Pantalla de Menú de Pedido y Carrito (RUTAS ÚNICAS, SIN DUPLICADOS)
    Route::get('/menu', 'KioskoController@menuPedido')->name('kiosko.menu_pedido');
    Route::get('/kiosko/productos/{categoria_id}', 'KioskoController@getProductosPorCategoria')->name('kiosko.get_productos_por_categoria');
    Route::get('/kiosko/search-products-kiosko', 'KioskoController@searchProductsKiosko')->name('kiosko.search_products_kiosko');
    Route::get('/kiosko/get-entradas', 'KioskoController@getEntradasKiosko')->name('kiosko.get_entradas');
    Route::get('/kiosko/get-combos', 'KioskoController@getCombosKiosko')->name('kiosko.get_combos');
    
    // Gestión del carrito (AJAX)
    Route::get('/kiosko/get-cart-details', 'KioskoController@getCartDetails')->name('kiosko.get_cart_details');
    Route::post('/kiosko/carrito/add', 'KioskoController@addToCart')->name('kiosko.add_to_cart');
    Route::post('/kiosko/carrito/update', 'KioskoController@updateCartItem')->name('kiosko.update_cart_item');
    Route::post('/kiosko/carrito/remove', 'KioskoController@removeCartItem')->name('kiosko.remove_cart_item');
    Route::post('/kiosko/carrito/clear', 'KioskoController@clearCart')->name('kiosko.clear_cart');
    Route::post('/kiosko/remove-old-cart-item', 'KioskoController@removeOldCartItem')->name('kiosko.remove_old_cart_item');

    // 4. Confirmación, Envío y Pagos
    Route::get('/confirmacion', 'KioskoController@confirmacionPedido')->name('kiosko.confirmacion_pedido');
    Route::post('/kiosko/enviar-comanda', 'KioskoController@enviarComanda')->name('kiosko.enviar_comanda');
    Route::get('/kiosko/pedido_details/{pedido_id}', 'KioskoController@getPedidoDetails')->name('kiosko.get_pedido_details');
    Route::get('/kiosko/exito', 'KioskoController@pedidoExito')->name('kiosko.pedido_exito');
    Route::post('/kiosko/precuenta', 'KioskoController@generarPrecuenta')->name('kiosko.generar_precuenta');
    Route::get('/kiosko/cobrar/{ped_id}', 'RestauranteController@showKioskoCobranza')->name('kiosko.cobrar');
    Route::post('/kiosko/solicitar_cs', 'KioskoController@solicitar_cs')->name('kiosko.solicitar_cs');

    Route::post('/kiosko/reimprimir-item', 'KioskoController@reimprimirItemComanda')->name('kiosko.reimprimir_item');

    // 5. Gestión de Mesas (Unir, desunir, cambiar)
    Route::post('/kiosko/cambiar_mesa', 'KioskoController@cambiarMesa')->name('kiosko.cambiar_mesa');
    Route::get('/kiosko/buscar_mesas_desocupadas', 'KioskoController@buscarMesasDesocupadas')->name('kiosko.buscar_mesas_desocupadas');
    Route::post('/kiosko/unir_mesas', 'KioskoController@unirMesas')->name('kiosko.unir_mesas');
    Route::get('/kiosko/buscar_mesas_desocupadas_unir/{mesa_actual_id}', 'KioskoController@buscarMesasDesocupadasUnir')->name('kiosko.buscar_mesas_desocupadas_unir');
    Route::post('/kiosko/desunir_mesas', 'KioskoController@desunirMesas')->name('kiosko.desunir_mesas');
    Route::get('/kiosko/buscar-mesas-libres-para-unir', 'KioskoController@buscarMesasLibresParaUnir')->name('kiosko.buscar_mesas_libres_para_unir');

    // 6. Privilegios de Admin (Eliminaciones con password)
    Route::post('/kiosko/eliminar-pedido-completo', 'KioskoController@eliminarPedidoCompleto')->name('kiosko.eliminar_pedido_completo');
    Route::post('/kiosko/eliminar-item-pedido-con-password', 'KioskoController@eliminarItemPedidoConPassword')->name('kiosko.eliminar_item_pedido_con_password');
    Route::post('/kiosko/modificar-precio-item-pedido-con-password', 'KioskoController@modificarPrecioItemPedidoConPassword')->name('kiosko.modificar_precio_item_pedido_con_password');

    // 7. Venta Directa / Llevar / Cocina
    Route::get('/puntoventadirecta', 'KioskoController@puntoVentaDirecta')->name('kiosko.punto_venta_directa');
    Route::get('/kiosko/search-products-directa', 'KioskoController@searchProducts')->name('kiosko.search_products_directa');
    Route::post('/kiosko/registrar-venta-directa', 'KioskoController@registrarVentaDirecta')->name('kiosko.registrar_venta_directa');
    Route::get('/cocina', 'KioskoController@mostrarComandasCocina')->name('kiosko.comandas_cocina');
    Route::get('/kiosko/get-comandas-json', 'KioskoController@getComandasCocinaJson')->name('kiosko.get_comandas_cocina_json');
    Route::post('/kiosko/despachar-item', 'KioskoController@despacharItemComanda')->name('kiosko.despachar_item_comanda');
    Route::get('/kiosko/get-takeaway-delivery-orders', 'KioskoController@getTakeAwayAndDeliveryOrdersForDisplay')->name('kiosko.get_take_away_and_delivery_orders_for_display');
    Route::get('/kiosko/check_new_take_away_orders', 'KioskoController@checkNewTakeAwayOrders')->name('kiosko.check_new_take_away_orders');

    // Rutas para el manejo de reservas en el Kiosko
	Route::get('/api/kiosko/reservas-hoy', 'KioskoController@getReservasHoy')->name('kiosko.reservas_hoy');
	Route::post('/api/kiosko/procesar-reserva', 'KioskoController@procesarReserva')->name('kiosko.procesar_reserva');
    
    // Mall y visualización de tickets
    Route::get('mall', 'KioskoController@indexKiosko')->name('kiosko.index');
    Route::post('empresas/kiosko/enviar', 'KioskoController@enviarComandaKiosko')->name('kiosko.enviar');
    Route::get('/comprobantes/ver-ticket/{id_cpe_cabecera}', 'KioskoController@verComprobanteTicket')->name('comprobantes.ver_ticket');

    // Stock Kiosko
    Route::get('kiosko/stock-preparados', 'KioskoController@indexStockPreparados')->name('kiosko.stock_preparados.index');
    Route::get('kiosko/stock-preparados/crear', 'KioskoController@vistaStockPreparados')->name('kiosko.stock_preparados.crear');
    Route::post('kiosko/stock-preparados/guardar', 'KioskoController@guardarStockPreparados')->name('kiosko.stock_preparados.guardar');

    //VACIAR BASE DE DATOS BORRAR BASE DE DATOS BORRAR INFORMACION
	Route::get('/administrador/vaciartablas', 'EmpresaController@vistaVaciarTablas')->name('vaciartablas.index');
	Route::post('/administrador/ejecutar-vaciado', 'EmpresaController@ejecutarVaciado')->name('vaciartablas.store');

});
// Verificación del sistema
Route::post('/verificar-sistema', 'SystemVerificationController@verify')->name('system.verify');

//Route::post('/exportar-concar', 'ConcarController@exportarVentas')->name('concar.export');
// Ruta para mostrar la vista que creaste
Route::get('/concar/exportar', 'ConcarController@index')->name('concar.index');

// Ruta para procesar la exportación (el botón Aceptar)
Route::post('/concar/procesar', 'ConcarController@exportarVentas')->name('concar.export');

Route::post('/concar/exportar-excel', 'ConcarController@exportarExcel')->name('concar.excel');

Route::post('concar/exportar-cobranzas', 'ConcarController@exportarCobranzasExcel')->name('concar.cobranzas');

//FIDELIZACION DE CLIENTES PUNTOS
// Ver el panel de configuración
Route::get('/configuracion/puntos', 'FidelizacionController@index')->name('puntos.index');
// Guardar los cambios
Route::post('/configuracion/puntos/actualizar', 'FidelizacionController@update')->name('puntos.update');

Route::resource('fidelizacion', 'FidelizacionController');
// Ruta extra para activar/desactivar rápido
Route::get('fidelizacion/{id}/estado', 'FidelizacionController@toggleEstado')->name('puntos.estado');

// Ruta para cambiar el estado (Activar/Desactivar)
Route::get('fidelizacion/estado/{id}', 'FidelizacionController@toggleEstado')->name('fidelizacion.estado');

// Ruta para eliminar
Route::get('fidelizacion/eliminar/{id}', 'FidelizacionController@destroy')->name('fidelizacion.destroy');

// Ruta para editar (puedes usar un modal o una vista aparte, por ahora hagamos la lógica)
Route::post('fidelizacion/update/{id}', 'FidelizacionController@update')->name('fidelizacion.update');

Route::post('/fidelizacion/actualizar/{id}', 'FidelizacionController@actualizarRegla');

Route::get('/cliente/{id}/puntos', 'FidelizacionController@consultarPuntos');

Route::post('/canjear-premio', 'FidelizacionController@canjearPremio');

Route::get('/ventas/masiva', 'PuntoVentaController@vistaVentaMasiva')->name('puntoventa.masiva');
Route::post('/ventas/masiva/procesar', 'PuntoVentaController@procesarVentaMasiva')->name('puntoventa.procesarmasiva');

//CONFIGURACION INICIAL
Route::get('borrar_cola', 'EmpresaController@borrarColaImpresion');
Route::post('/eliminar-tickets', 'ComprobantesController@eliminarVarios')->name('tickets.eliminar');
//IMPORTAR BASE DE DATOS
Route::get('importar_informacion', 'ImportarDatosController@index')->name('importar.index');
Route::post('importar-tabla', 'ImportarDatosController@importarTabla')->name('importar.tabla');

Route::get('/asistencia', 'AttendanceController@index')->name('asistencia.index');
Route::get('/asistencia/generar-url/{dni}', 'AttendanceController@generarUrlSegura');
Route::get('/asistencia/marcar/{dni}', 'AttendanceController@registrarCelular')->name('asistencia.registrar');
Route::post('/asistencia/api/registrar', 'AttendanceController@register');
Route::get('/asistencia/verificar-estado/{dni}', 'AttendanceController@verificarEstado');
Route::get('/asistencia/lector-fisico/{dni}', 'AttendanceController@registrarLectorFisico')->name('asistencia.lector_fisico');

Route::get('/autocompleteruc/{ruc}', 'EmpresaController@consultaRucSunat');

//CONSULTAS CPE
Route::get('consulta-cpe', 'CpeController@index');
Route::post('consultar-cpe', 'CpeController@consultar');
Route::get('consulta-multiple-v2', 'CpeController@consultaMultipleV2');
Route::get('revalidar-compra/{id}', 'ComprasController@revalidarCompra');
Route::get('consultar-cpe-venta/{id}', 'CpeController@consultarVenta');

// Ruta para mostrar la vista
Route::get('/empresas/mantenimiento/backup', 'ImportarDatosController@vistaBackup')->name('backup.vista');

// Ruta para ejecutar y descargar el backup
Route::post('/empresas/mantenimiento/backup/descargar', 'ImportarDatosController@descargarBackup')->name('backup.descargar');

Route::get('/movimientos_preparados', 'MovimientosPreparadosController@index');
Route::post('/movimientos_preparados/ingreso-diario', 'MovimientosPreparadosController@storeIngreso');
Route::get('/movimientos_preparados/exportar', 'MovimientosPreparadosController@exportarHistorial');
Route::get('movimientos_preparados/reporte-stock', 'MovimientosPreparadosController@reporteStock');
Route::get('movimientos_preparados/exportar-stock', 'MovimientosPreparadosController@exportarStock');

//RESERVAS 
// Vista de creación de reserva
Route::get('reservas', 'ReservasController@index')->name('reservas.index');
Route::get('reservas/crear', 'ReservasController@create')->name('reservas.create');
// Rutas para AJAX
Route::get('api/mesas-por-piso/{pis_id}', 'ReservasController@getMesasPorPiso');
//Route::get('api/buscar-cliente/{documento}', 'ReservasController@buscarCliente');
Route::get('api/buscar-cliente', 'ReservasController@buscarCliente');
Route::get('api/buscar-externa/{documento}', 'ReservasController@buscarApiExterna');
Route::post('api/guardar-cliente-ajax', 'ReservasController@storeClienteAjax');
Route::post('reservas/store', 'ReservasController@store')->name('reservas.store');
Route::get('reservas/ticket/{id}', 'ReservasController@imprimirTicket')->name('reservas.ticket');


// RUTAS PARA EL MÓDULO DE SAUNA
Route::get('sauna/recepcion', 'SaunaController@index')->name('sauna.recepcion');
Route::post('sauna/procesar-rfid', 'SaunaController@procesarEscaneoRFID')->name('sauna.procesar-rfid');
Route::post('sauna/guardar-checkin', 'SaunaController@guardarCheckIn')->name('sauna.guardar-checkin');
Route::get('sauna/cuenta/{id_brazalete}', 'SaunaController@verCuentaActiva')->name('sauna.cuenta_activa');

// Rutas AJAX para la búsqueda y creación rápida de clientes en el Sauna
Route::get('sauna/autocomplete-cliente/{query}', 'SaunaController@autocompleteCliente');
Route::post('sauna/registrar-cliente-rapido', 'SaunaController@registrarClienteRapido')->name('sauna.registrar-cliente-rapido');

// Rutas para crear y listar los Brazaletes/Casilleros
Route::get('sauna/brazaletes', 'SaunaController@listarBrazaletes')->name('sauna.brazaletes.index');
Route::get('sauna/brazaletes/crear', 'SaunaController@crearBrazalete')->name('sauna.brazaletes.create');
Route::post('sauna/brazaletes/guardar', 'SaunaController@guardarBrazalete')->name('sauna.brazaletes.store');

Route::get('sauna/brazaletes/editar/{id}', 'SaunaController@editarBrazalete')->name('sauna.brazaletes.edit');
Route::post('sauna/brazaletes/actualizar/{id}', 'SaunaController@actualizarBrazalete')->name('sauna.brazaletes.update');

Route::post('sauna/agregar-consumo', 'SaunaController@agregarConsumo')->name('sauna.agregar-consumo');

Route::get('reportes_stock_general', 'MovimientosPreparadosController@reporteGeneralStock');
Route::get('movimientos_preparados/exportar-general-stock', 'MovimientosPreparadosController@exportarGeneralStock');

// Ruta para ver la pantalla de monitoreo
Route::get('/monitoreo_impresiones', 'ComprobantesController@monitoreo')->name('impresiones.monitoreo');

// Ruta para el botón de reimprimir
Route::post('/reimprimir-ticket/{id}', 'ComprobantesController@reimprimir')->name('impresiones.reimprimir');

Route::post('/reportes/ventas/detalle-hora', 'ReportesVentasController@detalleHora');



// Vista principal del formulario público
Route::get('cpe', 'CpeQueryController@index')->name('cpe.index');

// Procesar el formulario de consulta y mostrar los resultados
Route::post('cpe', 'CpeQueryController@search')->name('cpe.search');

// Descarga o renderizado del PDF
Route::get('cpe/download-pdf/{id}', 'CpeQueryController@downloadPdf')->name('cpe.download.pdf');

// Ruta para el autocompletado (Apunta al controlador donde tengas la función que me pasaste)
Route::get('consultorio/buscar-cliente', 'ConsultorioGinecologicoController@autocompleteClient')->name('cliente.autocomplete');
Route::get('consultorio/{id}/reporte', 'ConsultorioGinecologicoController@imprimirReporte')->name('consultorio.reporte');
Route::resource('consultorio', 'ConsultorioGinecologicoController');

Route::get('insumos', 'ProductosController@indexInsumos');

// RUTAS PARA GESTIÓN DE MERMAS
Route::get('/mermas', 'MermaController@index');
Route::get('/mermas/crear', 'MermaController@create');
Route::post('/mermas/guardar', 'MermaController@store');

// Modificar y Eliminar
Route::get('/mermas/editar/{id}', 'MermaController@edit');
Route::post('/mermas/actualizar', 'MermaController@update');
Route::get('/mermas/eliminar/{id}', 'MermaController@destroy');

// Reportes
Route::get('/mermas/ticket/{id}', 'MermaController@ticket');
Route::get('/mermas/reporte-diario/pdf', 'MermaController@reporteDiarioPdf');
Route::get('/mermas/reporte-diario/excel', 'MermaController@reporteDiarioExcel');

// RUTAS PARA MOTIVOS DE MERMA
Route::get('/motivos-merma', 'MotivoMermaController@index');
Route::post('/motivos-merma/guardar', 'MotivoMermaController@store');
Route::post('/motivos-merma/actualizar', 'MotivoMermaController@update');
Route::get('/motivos-merma/eliminar/{id}', 'MotivoMermaController@destroy');

// RUTAS DE MANTENIMIENTO / KARDEX
Route::get('/mantenimiento/stock', 'MantenimientoController@index');
Route::post('/mantenimiento/sincronizar-stock', 'MantenimientoController@sincronizarStock');

// MODULO ASISTENCIA TIPO BIOMETRICO
// Pantalla principal y reportes ASISTENCIAS

Route::get('/asistencia/reporte', 'AttendanceController@reporte')->name('asistencia.reporte');

Route::get('/asistencia/horarios', 'AttendanceController@asignarHorarios')->name('asistencia.horarios');
Route::post('/asistencia/horarios/guardar', 'AttendanceController@guardarHorarios')->name('asistencia.horarios.guardar');

// Gestión de Turnos (Crear/Eliminar)
Route::get('/asistencia/turnos', 'AttendanceController@turnosIndex')->name('asistencia.turnos');
Route::post('/asistencia/turnos', 'AttendanceController@turnosStore')->name('asistencia.turnos.store');
Route::get('/asistencia/turnos/eliminar/{id}', 'AttendanceController@turnosDestroy')->name('asistencia.turnos.destroy');

// Rutas de Edición de Turnos
Route::get('/asistencia/turnos/editar/{id}', 'AttendanceController@turnosEdit')->name('asistencia.turnos.edit');
Route::post('/asistencia/turnos/actualizar/{id}', 'AttendanceController@turnosUpdate')->name('asistencia.turnos.update');

// Rutas para configurar la IP de asistencia del local
Route::get('/asistencia/configurar-ip', 'AttendanceController@configurarIpIndex')->name('asistencia.configurar_ip');
Route::post('/asistencia/configurar-ip/guardar', 'AttendanceController@configurarIpUpdate')->name('asistencia.configurar_ip.update');

Route::get('/asistencia/reporte-detallado', 'AttendanceController@reporteDetallado')->name('asistencia.reporte_detallado');

Route::post('/asistencia/autorizar-tardanza', 'AttendanceController@autorizarTardanza')->name('asistencia.autorizar_tardanza');

Route::get('/asistencia/motivos', 'AttendanceController@motivosIndex')->name('asistencia.motivos');
Route::post('/asistencia/motivos', 'AttendanceController@motivosStore')->name('asistencia.motivos.store');
Route::delete('/asistencia/motivos/{id}', 'AttendanceController@motivosDestroy')->name('asistencia.motivos.destroy');
Route::put('/asistencia/motivos/{id}', 'AttendanceController@motivosUpdate')->name('asistencia.motivos.update');

Route::get('/asistencia/tareo', 'AttendanceController@reporteTareo')->name('asistencia.tareo');

// GESTIÓN DEL PLAN CONTABLE
// ==============================================================================
Route::get('/plan-contable', 'PlanContableController@index')->name('plan-contable.index');
Route::get('/plan-contable/crear', 'PlanContableController@create')->name('plan-contable.create');
Route::post('/plan-contable/guardar', 'PlanContableController@store')->name('plan-contable.store');


// ==============================================================================
// GESTIÓN DE ASIENTOS CONTABLES
// ==============================================================================
Route::get('/asientos', 'AsientoController@index')->name('asientos.index');
Route::get('/asientos/crear', 'AsientoController@create')->name('asientos.create');
Route::post('/asientos/guardar', 'AsientoController@store')->name('asientos.store');

Route::get('/asientos/reporte-excel', 'AsientoController@reporteExcel')->name('asientos.excel');
Route::get('/asientos/reporte-pdf', 'AsientoController@reportePdf')->name('asientos.pdf');

Route::get('/ventas/generar-txt-sunat', 'VentaContableController@generarTxtSunat')->name('ventas.generarTxtSunat');

Route::get('/plan-contable/exportar', 'PlanContableController@exportarExcel')->name('plan-contable.exportar');
Route::post('/plan-contable/importar', 'PlanContableController@importarExcel')->name('plan-contable.importar');

// GESTIÓN Y CENTRALIZACIÓN DE VENTAS (CPE)
// ==============================================================================
Route::get('/ventas', 'VentaContableController@index')->name('ventas.index');
Route::post('/ventas/centralizar/{id}', 'VentaContableController@generarAsientoVenta')->name('ventas.centralizar');
Route::post('/ventas/centralizar-masivo', 'VentaContableController@centralizarMasivo')->name('ventas.centralizarMasivo');

// ==============================================================================
// GESTIÓN Y CENTRALIZACIÓN DE COMPRAS
// ==============================================================================
//Route::get('/compras', 'CompraContableController@index')->name('compras.index');
//Route::post('/compras/centralizar/{id}', 'CompraContableController@generarAsientoCompra')->name('compras.centralizar');

Route::get('/exportar-recetas-excel', 'ProductosController@exportarRecetasExcel');

    // Rutas de Vehículos (Flota)
Route::resource('vehiculos', 'VehiculoController');
Route::resource('viajes', 'ViajeController');

Route::get('mantenimientos/crear/{vehiculo_id}', 'MantenimientoController@create')->name('mantenimientos.create');
Route::post('mantenimientos', 'MantenimientoController@store')->name('mantenimientos.store');

Route::resource('guias', 'GuiaRemisionController');
Route::post('/guias/consulta-ruc', 'GuiaRemisionController@consultaRucSunat')->name('guias.consultaRuc');
Route::get('/guias/buscar-ubigeo', 'GuiaRemisionController@buscarUbigeo')->name('guias.buscarUbigeo');

// Consulta unificada de DNI y RUC
Route::post('/guias/consulta-doc', 'GuiaRemisionController@consultarDocumento')->name('guias.consultaDoc');

// Buscador de Ubigeo
Route::get('/guias/buscar-ubigeo', 'GuiaRemisionController@buscarUbigeo')->name('guias.buscarUbigeo');

// Descargar PDF y XML
Route::get('/guias/{id}/pdf', 'GuiaRemisionController@descargarPdf')->name('guias.pdf');

// Reenviar a SUNAT
Route::post('/guias/{id}/reenviar', 'GuiaRemisionController@reenviarSunat')->name('guias.reenviar');

Route::post('/consultarDocumento', 'GuiasRemisionController@consultarDocumento')->name('consultar.documento');

Route::get('transportes', 'TransporteController@index')->name('transportes.index');
Route::get('transportes/create', 'TransporteController@create')->name('transportes.create');
Route::post('transportes', 'TransporteController@store')->name('transportes.store');
Route::get('transportes/unidades', 'UnidadController@index')->name('unidades.index');
Route::post('transportes/vehiculos', 'UnidadController@storeVehiculo')->name('vehiculos.store');
Route::post('transportes/choferes', 'UnidadController@storeChofer')->name('choferes.store');

Route::get('transportes/consultar-doc', 'TransporteController@consultarDocumento')->name('transportes.consultardoc');

Route::prefix('ocr')->group(function () {
    Route::get('/', 'OcrController@index')->name('ocr.index');
    Route::post('/process', 'OcrController@process')->name('ocr.process');
});

Route::get('/api/v1/ruc/{ruc}', 'SunatController@consultar');