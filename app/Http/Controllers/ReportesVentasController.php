<?php

namespace MasterSoft\Http\Controllers;

use Illuminate\Http\Request;
use MasterSoft\Http\Requests;
use MasterSoft\Modelos\ReportesVentas;
use MasterSoft\TipoIGV;
use MasterSoft\Empresa;
use MasterSoft\EmpresaNegocios;
use MasterSoft\Cliente;
use MasterSoft\cpe_cabecera;
use MasterSoft\documento_relacionado;
use MasterSoft\unidad_medida;
use MasterSoft\cpe_detalle;
use MasterSoft\cpe_baja;
use MasterSoft\Comprobante;
use MasterSoft\compras_cabecera;
use MasterSoft\cpe_nota_detalle;
use MasterSoft\gastos_cabecera;
use MasterSoft\cpe_nota;
use MasterSoft\User;
use MasterSoft\tipo_documento_identidad;
use MasterSoft\tipo_documento;
use MasterSoft\productos;
use MasterSoft\tipocambio;
use MasterSoft\MontoLetras;
use Illuminate\Support\Facades\Auth;
use MasterSoft\Http\Requests\FacturaCreateFormRequest;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Input;
use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\FilePrintConnector;
use Mike42\Escpos\EscposImage;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use DB;
use Carbon;
use Excel;
use PDF;

class ReportesVentasController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
     
    public function __construct()
    {
        
        $this->middleware('auth');
    }


    public function reporteVentas(Request $request,$tipo){

    	$vendedor = DB::tABLE('users')->get();
    	$cajero = DB::tABLE('users')->get();
    	$cliente = DB::tABLE('cliente')->get();
    	$sucursal = DB::tABLE('empresa_negocios')->get();
    	$fec_ini = now()->modify('first day of this month')->format('Y-m-d');
        $fec_fin = now()->modify('last day of this month')->format('Y-m-d');

        // Obtener productos y negocios para la vista buscar_ventas_productos
        $productoslista = DB::table('productos')->where('tipo','=','1')->where('promocion','!=','2')->get(); // Asumo que esto viene de otro lado, pero lo añado para que la vista funcione
        $negocios = DB::table('empresa_negocios')->get(); // Ya lo tienes como $sucursal arriba, reutilizo
        $prod = null; // Placeholder para $prod si no viene en request para esa vista
        $IdProducto = null; // Placeholder

        if($tipo=='1'){
        	 return view('empresas.reportes.ventas.buscadores.buscar_ventas',compact('vendedor','cajero','cliente','sucursal','fec_ini','fec_fin'));
        }elseif($tipo=='2'){
        	 return view('empresas.reportes.ventas.buscadores.buscar_ventas_detallado',compact('vendedor','cajero','cliente','sucursal','fec_ini','fec_fin'));
        }elseif($tipo=='3'){
        	 return view('empresas.reportes.ventas.buscadores.buscar_ventas_vendedor',compact('vendedor','cajero','cliente','sucursal','fec_ini','fec_fin'));
        }elseif($tipo=='4'){
        	  return view('empresas.reportes.ventas.buscadores.buscar_ventas_cliente',compact('vendedor','cajero','cliente','sucursal','fec_ini','fec_fin'));
        }elseif($tipo=='5'){
        	 return view('empresas.reportes.ventas.buscadores.buscar_ventas_anuladas',compact('vendedor','cajero','cliente','sucursal','fec_ini','fec_fin'));
        }elseif($tipo=='6'){
        	 return view('empresas.reportes.ventas.buscadores.buscar_ventas_formato_sunat',compact('vendedor','cajero','cliente','sucursal','fec_ini','fec_fin'));
        }elseif($tipo=='7'){
        	 return view('empresas.reportes.ventas.buscadores.buscar_ventas_cpe',compact('vendedor','cajero','cliente','sucursal','fec_ini','fec_fin'));	
        }elseif($tipo=='8'){
             return view('empresas.reportes.ventas.buscadores.buscar_ventas_productos',compact('negocios', 'fec_ini','fec_fin','productoslista','prod','IdProducto'));  
        }
       
    }

    public function detalleHora(Request $request)
    {
        // Buscamos las cabeceras cruzando con usuarios (mozo/cajero), mesas y pisos
        $query = DB::table('cpe_cabecera')
            // Cruce para obtener el nombre del Mozo
            ->leftJoin('users as mozos', 'cpe_cabecera.mozo', '=', 'mozos.IdUsuario')
            // Cruce para obtener el nombre del Cajero
            ->leftJoin('users as cajeros', 'cpe_cabecera.IdUsuario', '=', 'cajeros.IdUsuario')
            // Cruce para obtener la Mesa
            ->leftJoin('mesas', 'cpe_cabecera.mes_id', '=', 'mesas.mes_id')
            // Cruce para obtener la Zona
            ->leftJoin('pisos', 'cpe_cabecera.pis_id', '=', 'pisos.pis_id')
            ->select(
                'cpe_cabecera.IdCpe_cabecera',
                'cpe_cabecera.tdocod',
                'cpe_cabecera.serdoc',
                'cpe_cabecera.numdoc',
                'cpe_cabecera.ccandi',
                'cpe_cabecera.ccanom',
                'cpe_cabecera.ccaitv as total', // Tu campo de monto total
                'cpe_cabecera.mes_id',
                'mesas.mes_nom', // Nombre de la mesa
                'pisos.pis_nom',  // Nombre de la zona
                // Concatenamos name + apeusu para mozo y cajero
                DB::raw("CONCAT(COALESCE(mozos.name,''), ' ', COALESCE(mozos.apeusu,'')) as mozo_nombre"),
                DB::raw("CONCAT(COALESCE(cajeros.name,''), ' ', COALESCE(cajeros.apeusu,'')) as cajero_nombre")
            )
            ->whereBetween('cpe_cabecera.ccafem', [$request->fec_ini, $request->fec_fin])
            ->whereNull('cpe_cabecera.ccabaj')
            ->whereIn('cpe_cabecera.tdocod', ['01', '03', '13'])
            ->where(DB::raw('HOUR(cpe_cabecera.fecha_hora)'), $request->hora);

        if (!empty($request->suc_id)) {
            $query->where('cpe_cabecera.id_empresa_negocio', $request->suc_id);
        }

        $comprobantes = $query->get();

        // Jalamos los detalles de cada comprobante usando IdCpe_cabecera
        foreach ($comprobantes as $comp) {
            $comp->detalles = DB::table('cpe_detalle')
                ->where('IdCpe_cabecera', $comp->IdCpe_cabecera)
                ->select('IdProducto', 'cdedes', 'cdecan', 'cdepuni', 'cdevve')
                ->get();
                
            // Validamos si fue Delivery/Llevar o Mesa
            $comp->tipo_consumo = empty($comp->mes_id) ? 'Para Llevar / Delivery' : trim($comp->mes_nom);
        }

        return view('empresas.reportes.ventas.formatos.tabla_detalle_modal', compact('comprobantes'));
    }

 	public function generarReporteVentas(Request $request){

    // --- 1. CAPTURA DE PARÁMETROS ---
    $fec_ini = $request->get('fec_ini');
    $fec_fin = $request->get('fec_fin');
    $ven_id = $request->get('ven_id');
    $cli_id = $request->get('cli_id');
    $suc_id = $request->get('suc_id');
    $caj_id = $request->get('caj_id');
    $tip_rep = $request->get('tip_rep');
    $IdProducto = $request->get('IdProducto');
    
    // --- 2. CONSULTAS SIMPLES DE APOYO (Consultas de contexto rápido) ---
    $vendedor = DB::tABLE('users')->get();
    $cajero = DB::tABLE('users')->get();
    $cliente = DB::tABLE('cliente')->get();
    $almacen = $request->get('almacen', 'Todos');
    
    $dato_vendedor = null;
    if ($request->filled('ven_id') && $ven_id != 'Todos') {
        $dato_vendedor = DB::table('users')->where('IdUsuario',$ven_id)->first();
    }
    $dato_cliente = null;
    if ($request->filled('cli_id') && $cli_id != 'Todos') {
        $dato_cliente = DB::table('cliente')->where('clicod',$cli_id)->first();
    }

    //$cat_id = $request->get('cat_id');

    $sucursal = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',$suc_id)->first();
    $empresa = Empresa::findOrFail(Auth::user()->IdEmpresa);

    $vista = '';
    $cons_ventas = new ReportesVentas();
    
    // ######################################################################
    // IMPORTANTE: NO HAY CONSULTAS PESADAS AQUÍ. SE EJECUTAN DENTRO DEL IF.
    // ######################################################################
    
    // --- 3. LÓGICA POR TIPO DE REPORTE (Cada bloque es independiente y rápido) ---

    if($tip_rep=='1'){
        // REPORTE 1: REPORTE DE VENTAS (Solo ejecuta las 8 consultas necesarias)
        
        $factura = $cons_ventas->obtenerFactura($suc_id,$fec_ini,$fec_fin,$cli_id,$caj_id,$ven_id);
        $boleta = $cons_ventas->obtenerBoleta($suc_id,$fec_ini,$fec_fin,$cli_id,$caj_id,$ven_id);
        $nota_venta = $cons_ventas->obtenerNotaVenta($suc_id,$fec_ini,$fec_fin,$cli_id,$caj_id,$ven_id);
        $nota_credito = $cons_ventas->obtenerNotaCredito($suc_id,$fec_ini,$fec_fin,$cli_id,$caj_id,$ven_id);
        $total_contado = $cons_ventas->obtenerTotalContado($suc_id,$fec_ini,$fec_fin,$cli_id,$caj_id,$ven_id);
        $total_credito = $cons_ventas->obtenerTotalCredito($suc_id,$fec_ini,$fec_fin,$cli_id,$caj_id,$ven_id);
        $total_ventas = $cons_ventas->obtenerTotalVentas($suc_id,$fec_ini,$fec_fin,$cli_id,$caj_id,$ven_id);
        $venta = $cons_ventas->obtenerVenta($suc_id,$fec_ini,$fec_fin,$cli_id,$caj_id,$ven_id);


        $vista = view('empresas.reportes.ventas.formatos.reporte_ventas',compact('fec_ini','fec_fin','factura','boleta','nota_venta','nota_credito','venta','total_contado','total_credito','total_ventas'))->render();


    }elseif($tip_rep=='2'){
        // REPORTE 2: REPORTE DE VENTAS DETALLADAS (Solo ejecuta las 17 consultas necesarias)
        
        $factura = $cons_ventas->obtenerFactura($suc_id,$fec_ini,$fec_fin,$cli_id,$caj_id,$ven_id);
        $boleta = $cons_ventas->obtenerBoleta($suc_id,$fec_ini,$fec_fin,$cli_id,$caj_id,$ven_id);
        $nota_venta = $cons_ventas->obtenerNotaVenta($suc_id,$fec_ini,$fec_fin,$cli_id,$caj_id,$ven_id);
        $nota_credito = $cons_ventas->obtenerNotaCredito($suc_id,$fec_ini,$fec_fin,$cli_id,$caj_id,$ven_id);
        $venta = $cons_ventas->obtenerVenta($suc_id,$fec_ini,$fec_fin,$cli_id,$caj_id,$ven_id);
        $total_contado = $cons_ventas->obtenerTotalContado($suc_id,$fec_ini,$fec_fin,$cli_id,$caj_id,$ven_id);
        $total_credito = $cons_ventas->obtenerTotalCredito($suc_id,$fec_ini,$fec_fin,$cli_id,$caj_id,$ven_id);
        $total_ventas = $cons_ventas->obtenerTotalVentas($suc_id,$fec_ini,$fec_fin,$cli_id,$caj_id,$ven_id);
        $detalle = $cons_ventas->obtenerVentaDetalle($suc_id,$fec_ini,$fec_fin,$cli_id,$caj_id,$ven_id);
        $vent_res_prod = $cons_ventas->obtenerResumenVentasProductos($suc_id,$fec_ini,$fec_fin,$cli_id,$caj_id,$ven_id,$IdProducto);
        $not_cre_res_prod = $cons_ventas->obtenerResumenNotasCreditosProductos($suc_id,$fec_ini,$fec_fin,$cli_id,$caj_id,$ven_id);
        $total_notas_creditos = $cons_ventas->obtenerTotalNotasCreditos($suc_id,$fec_ini,$fec_fin,$cli_id,$caj_id,$ven_id);
        $total_notas_ventas = $cons_ventas->obtenerTotalNotasVentas($suc_id,$fec_ini,$fec_fin,$cli_id,$caj_id,$ven_id);
        $not_ven_res_prod = $cons_ventas->obtenerResumenNotasVentasProductos($suc_id,$fec_ini,$fec_fin,$cli_id,$caj_id,$ven_id);
        $total_vales_cons = $cons_ventas->obtenerTotalValesConsumo($suc_id,$fec_ini,$fec_fin,$cli_id,$caj_id,$ven_id);
        $val_con_res_prod = $cons_ventas->obtenerResumenValesConsumoProductos($suc_id,$fec_ini,$fec_fin,$cli_id,$caj_id,$ven_id);
        $vent_bolfac_res_prod = $cons_ventas->obtenerResumenVentasBoletasFacturasProductos($suc_id,$fec_ini,$fec_fin,$cli_id,$caj_id,$ven_id);
        $total_ventas_bolfac = $cons_ventas->obtenerTotalVentasBoletasFacturas($suc_id,$fec_ini,$fec_fin,$cli_id,$caj_id,$ven_id);


        $vista = view('empresas.reportes.ventas.formatos.reporte_ventas_detallada',compact('fec_ini','fec_fin','factura','boleta','nota_venta','nota_credito','venta','total_contado','total_credito','total_ventas','detalle','vent_res_prod','not_cre_res_prod','total_notas_creditos','total_notas_ventas','not_ven_res_prod','total_vales_cons','val_con_res_prod','vent_bolfac_res_prod','total_ventas_bolfac'))->render();

    }elseif($tip_rep=='3'){ 
        // 🚀 REPORTE 3: VENTAS DECLARACION SUNAT - ¡OPTIMIZADO Y RÁPIDO! 🚀

        // 1. LLAMADA CONSOLIDADA (Solo una consulta a la base de datos)
        $registros = $cons_ventas->obtenerDatosReporteSunat($suc_id, $fec_ini, $fec_fin, $cli_id, $caj_id, $ven_id);

        // 2. PROCESAMIENTO CON COLECCIONES DE LARAVEL (MUCHO MÁS RÁPIDO)
        $factura = $registros->where('tdocod', '01')->all();
        $boleta = $registros->where('tdocod', '03')->all();
        $nota_credito = $registros->where('tdocod', '07')->all();
        $total_ventas_sunat = $registros->whereIn('tdocod', ['01', '03'])->sum('total'); 
        $total_notas_creditos = $registros->where('tdocod', '07')->sum('total');
        $igv_ventas = $registros->whereIn('tdocod', ['01', '03'])->sum('igv');
        $igv_notas_creditos = $registros->where('tdocod', '07')->sum('igv');
        

        $vista = view('empresas.reportes.ventas.formatos.reporte_ventas_sunat',
            compact('fec_ini','fec_fin','factura','boleta','nota_credito','total_notas_creditos','total_ventas_sunat','igv_notas_creditos','igv_ventas')
        )->render();
        
        //return $vista; // Devolver directamente la vista generada

    }elseif($tip_rep=='4'){
        // REPORTE 4: REPORTE VENTAS MIGRAR (Solo ejecuta las 9 consultas necesarias)
        
        $factura = $cons_ventas->obtenerFactura($suc_id,$fec_ini,$fec_fin,$cli_id,$caj_id,$ven_id);
        $boleta = $cons_ventas->obtenerBoleta($suc_id,$fec_ini,$fec_fin,$cli_id,$caj_id,$ven_id);
        $nota_venta = $cons_ventas->obtenerNotaVenta($suc_id,$fec_ini,$fec_fin,$cli_id,$caj_id,$ven_id);
        $nota_credito = $cons_ventas->obtenerNotaCredito($suc_id,$fec_ini,$fec_fin,$cli_id,$caj_id,$ven_id);
        $venta = $cons_ventas->obtenerVenta($suc_id,$fec_ini,$fec_fin,$cli_id,$caj_id,$ven_id);
        $total_contado = $cons_ventas->obtenerTotalContado($suc_id,$fec_ini,$fec_fin,$cli_id,$caj_id,$ven_id);
        $total_credito = $cons_ventas->obtenerTotalCredito($suc_id,$fec_ini,$fec_fin,$cli_id,$caj_id,$ven_id);
        $total_ventas = $cons_ventas->obtenerTotalVentas($suc_id,$fec_ini,$fec_fin,$cli_id,$caj_id,$ven_id);
        $ventas_migrar = $cons_ventas->obtenerRegistroVentasMigrar($suc_id,$fec_ini,$fec_fin,$cli_id,$caj_id,$ven_id);

        $vista = view('empresas.reportes.ventas.formatos.reporte_ventas_migrar',compact('fec_ini','fec_fin','factura','boleta','nota_venta','nota_credito','venta','total_contado','total_credito','total_ventas','ventas_migrar','sucursal'))->render();


    }elseif($tip_rep=='5'){
        // REPORTE 5: RESUMEN VENDEDOR
        // Corregido: La consulta se añade aquí para definir $ventas_res_vend
        $ventas_res_vend = $cons_ventas->obtenerResumenVentasVendedor($suc_id,$fec_ini,$fec_fin,$cli_id,$caj_id,$ven_id);
        $total = $ventas_res_vend->sum('total');
        $dat_vend = DB::tABLE('users')->where('IdUsuario',$ven_id)->first();
        $vista = view('empresas.reportes.ventas.formatos.reporte_resumen_ventas_vendedor', compact('fec_ini','fec_fin','sucursal','dat_vend','ventas_res_vend','total'))->render();

    }elseif($tip_rep=='6'){
        // REPORTE 6: RESUMEN CLIENTE
        // Corregido: La consulta se añade aquí para definir $ventas_res_cli
        $ventas_res_cli = $cons_ventas->obtenerResumenVentasCliente($suc_id,$fec_ini,$fec_fin,$cli_id,$caj_id,$ven_id);
        $total = $ventas_res_cli->sum('total');
        $dat_cli = DB::tABLE('cliente')->where('clicod',$cli_id)->first();
        $vista = view('empresas.reportes.ventas.formatos.reporte_resumen_ventas_cliente', compact('fec_ini','fec_fin','sucursal','dat_cli','ventas_res_cli','total'))->render();

    }elseif($tip_rep=='7'){
        // REPORTE 7: RESUMEN PRODUCTOS
        // Corregido: La consulta se añade aquí para definir $vent_res_prod
        $vent_res_prod = $cons_ventas->obtenerResumenVentasProductos($suc_id,$fec_ini,$fec_fin,$cli_id,$caj_id,$ven_id,$IdProducto);
        $total = $vent_res_prod->sum('total');
        $vista = view('empresas.reportes.ventas.formatos.reporte_resumen_ventas_productos',compact('fec_ini','fec_fin','sucursal','vent_res_prod','total','IdProducto'))->render();
    
    }elseif($tip_rep=='8'){
        $ventas_det_prod = $cons_ventas->obtenerVentasDetallada($suc_id,$fec_ini,$fec_fin,$cli_id,$caj_id,$ven_id,$IdProducto);
        $notas_cre_det_prod = $cons_ventas->obtenerNotasCreditoDetallada($suc_id,$fec_ini,$fec_fin,$cli_id,$caj_id,$ven_id,$IdProducto);
        $anulaciones_det_prod = $cons_ventas->obtenerAnulacionesDetallada($suc_id,$fec_ini,$fec_fin,$cli_id,$caj_id,$ven_id,$IdProducto);

        // Sumamos el total de ventas y restamos notas de crédito
        $total_v = $ventas_det_prod->sum('cdevve');
        $total_nc = $notas_cre_det_prod->sum('cdevve');
        $total_final = $total_v - $total_nc; 

        $vista = view('empresas.reportes.ventas.formatos.reporte_ventas_detallada_productos', 
            compact('fec_ini','fec_fin','sucursal','ventas_det_prod','notas_cre_det_prod','anulaciones_det_prod','total_final','IdProducto'))->render();
    }elseif($tip_rep=='9'){
        // REPORTE 9: VENTAS ANULADAS
        // Corregido: Se añaden las consultas necesarias
        $ventas_anuladas = $cons_ventas->obtenerVentasAnuladas($suc_id,$fec_ini,$fec_fin,$cli_id,$caj_id,$ven_id);
        $total_contado = $cons_ventas->obtenerTotalContado($suc_id,$fec_ini,$fec_fin,$cli_id,$caj_id,$ven_id);
        $total_credito = $cons_ventas->obtenerTotalCredito($suc_id,$fec_ini,$fec_fin,$cli_id,$caj_id,$ven_id);
        $total_ventas = $cons_ventas->obtenerTotalVentas($suc_id,$fec_ini,$fec_fin,$cli_id,$caj_id,$ven_id);

        // La variable $vent_res_prod se usaba para el total, la redefinimos o usamos la de anuladas.
        // Asumiendo que el total es del resumen de productos, la definimos.
        $vent_res_prod = $cons_ventas->obtenerResumenVentasProductos($suc_id,$fec_ini,$fec_fin,$cli_id,$caj_id,$ven_id,$IdProducto);
        $total = $vent_res_prod->sum('precio');

        $vista = view('empresas.reportes.ventas.formatos.reporte_ventas_anuladas',compact('fec_ini','fec_fin','sucursal','ventas_anuladas','IdProducto','total_contado','total_credito','total_ventas','total'))->render();

    }elseif($tip_rep=='10'){
        // REPORTE 10: VENTAS ANULADAS DETALLADAS
        // Corregido: Se añaden las consultas necesarias
        $anulaciones_det_prod = $cons_ventas->obtenerAnulacionesDetallada($suc_id,$fec_ini,$fec_fin,$cli_id,$caj_id,$ven_id,$IdProducto);
        $total_contado = $cons_ventas->obtenerTotalContado($suc_id,$fec_ini,$fec_fin,$cli_id,$caj_id,$ven_id);
        $total_credito = $cons_ventas->obtenerTotalCredito($suc_id,$fec_ini,$fec_fin,$cli_id,$caj_id,$ven_id);
        $total_ventas = $cons_ventas->obtenerTotalVentas($suc_id,$fec_ini,$fec_fin,$cli_id,$caj_id,$ven_id);
        
        // Se necesita una consulta para obtener el total. Asumo que es $vent_res_prod.
        $vent_res_prod = $cons_ventas->obtenerResumenVentasProductos($suc_id,$fec_ini,$fec_fin,$cli_id,$caj_id,$ven_id,$IdProducto);
        $total = $vent_res_prod->sum('precio');

        $vista = view('empresas.reportes.ventas.formatos.reporte_ventas_anuladas_detalladas',compact('fec_ini','fec_fin','sucursal','anulaciones_det_prod','total','IdProducto','total_contado','total_credito','total_ventas'))->render();

    }elseif($tip_rep=='11'){
        // REPORTE 11: RENTABILIDAD
        // Corregido: Se añaden las consultas necesarias (basado en las variables que usa el compact)
        $anulaciones_det_prod = $cons_ventas->obtenerAnulacionesDetallada($suc_id,$fec_ini,$fec_fin,$cli_id,$caj_id,$ven_id,$IdProducto);
        $total_contado = $cons_ventas->obtenerTotalContado($suc_id,$fec_ini,$fec_fin,$cli_id,$caj_id,$ven_id);
        $total_credito = $cons_ventas->obtenerTotalCredito($suc_id,$fec_ini,$fec_fin,$cli_id,$caj_id,$ven_id);
        $total_ventas = $cons_ventas->obtenerTotalVentas($suc_id,$fec_ini,$fec_fin,$cli_id,$caj_id,$ven_id);
        
        // Se necesita una consulta para obtener el total. Asumo que es $vent_res_prod.
        $vent_res_prod = $cons_ventas->obtenerResumenVentasProductos($suc_id,$fec_ini,$fec_fin,$cli_id,$caj_id,$ven_id,$IdProducto);
        $total = $vent_res_prod->sum('precio');

        $vista = view('empresas.reportes.ventas.formatos.reporte_rentabilidad',compact('fec_ini','fec_fin','sucursal','anulaciones_det_prod','total','IdProducto','total_contado','total_credito','total_ventas'))->render();

    }elseif($tip_rep=='12'){
        // REPORTE 12: RENTABILIDAD COSTO FIJO
        // Corregido: Se añaden las consultas necesarias (basado en las variables que usa el compact)
        $anulaciones_det_prod = $cons_ventas->obtenerAnulacionesDetallada($suc_id,$fec_ini,$fec_fin,$cli_id,$caj_id,$ven_id,$IdProducto);
        $total_contado = $cons_ventas->obtenerTotalContado($suc_id,$fec_ini,$fec_fin,$cli_id,$caj_id,$ven_id);
        $total_credito = $cons_ventas->obtenerTotalCredito($suc_id,$fec_ini,$fec_fin,$cli_id,$caj_id,$ven_id);
        $total_ventas = $cons_ventas->obtenerTotalVentas($suc_id,$fec_ini,$fec_fin,$cli_id,$caj_id,$ven_id);
        
        // Se necesita una consulta para obtener el total. Asumo que es $vent_res_prod.
        $vent_res_prod = $cons_ventas->obtenerResumenVentasProductos($suc_id,$fec_ini,$fec_fin,$cli_id,$caj_id,$ven_id,$IdProducto);
        $total = $vent_res_prod->sum('precio');

        $vista = view('empresas.reportes.ventas.formatos.reporte_rentabilidad_costo_fijo',compact('fec_ini','fec_fin','sucursal','anulaciones_det_prod','total','IdProducto','total_contado','total_credito','total_ventas'))->render();

    }elseif($tip_rep == '14'){
    $query = DB::table('cpe_cabecera as cab')
        ->join('cpe_detalle as det', 'cab.IdCpe_cabecera', '=', 'det.IdCpe_cabecera')
        // Cambiamos join por leftJoin para no perder ventas de productos borrados
        ->leftJoin('productos as p', 'det.IdProducto', '=', 'p.IdProducto')
        ->leftJoin('categorias as cat', 'p.cat_id', '=', 'cat.cat_id')
        ->select(
            // Si la categoría es nula (producto borrado), ponemos "SIN CATEGORÍA"
            DB::raw('IFNULL(cat.cat_nom, "SIN CATEGORÍA / BORRADOS") as categoria_nombre'),
            DB::raw('SUM(det.cdevve) as total_ventas_categoria'),
            DB::raw('SUM(det.cdecan) as total_items_vendidos_categoria')
        )
        ->whereBetween('cab.ccafem', [$fec_ini, $fec_fin])
        ->whereNull('cab.ccabaj') 
        ->whereIn('cab.tdocod', ['01', '03', '13']);

    if (!empty($suc_id)) { $query->where('cab.id_empresa_negocio', $suc_id); }

    $salesByCategory = $query->groupBy('categoria_nombre') // Agrupamos por el nombre calculado
                             ->orderByDesc('total_ventas_categoria')
                             ->get();
    
    $vista = view('empresas.reportes.ventas.formatos.reporte_ventas_por_categoria', compact('salesByCategory', 'fec_ini', 'fec_fin', 'sucursal'))->render();


    }elseif ($tip_rep == '15') {
    // 1. TOTALES POR CATEGORÍA
    $categorias_totales = DB::table('cpe_detalle as det')
        ->join('cpe_cabecera as cab', 'det.IdCpe_cabecera', '=', 'cab.IdCpe_cabecera')
        ->leftJoin('productos as p', 'det.IdProducto', '=', 'p.IdProducto')
        ->leftJoin('categorias as cat', 'p.cat_id', '=', 'cat.cat_id')
        ->select(
            DB::raw('IFNULL(cat.cat_id, 0) as cat_id'),
            DB::raw('IFNULL(cat.cat_nom, "BORRADOS / OTROS") as cat_nom'),
            DB::raw('SUM(det.cdecan) as total_categoria_cantidad'),
            DB::raw('SUM(det.cdevve) as total_categoria_ventas')
        )
        ->whereBetween('cab.ccafem', [$fec_ini, $fec_fin])
        ->whereNull('cab.ccabaj')
        ->whereIn('cab.tdocod', ['01', '03', '13']);

    if (!empty($suc_id)) { $categorias_totales->where('cab.id_empresa_negocio', $suc_id); }
    if (!empty($ven_id) && $ven_id != 'Todos') { $categorias_totales->where('cab.IdUsuario_ven', $ven_id); }
    if (!empty($cli_id) && $cli_id != 'Todos') { $categorias_totales->where('cab.clicod', $cli_id); }

    $categorias = $categorias_totales->groupBy('cat_id', 'cat_nom')->orderBy('cat_nom')->get();

    // --- 2. CONSULTA DE PRODUCTOS DETALLADOS ---
    $productos_query = DB::table('cpe_detalle as det')
        ->select(
            DB::raw('IFNULL(p.cat_id, 0) as cat_id'),
            'det.IdProducto',
            'det.cdedes as pronom', // Nombre real de la venta
            'det.procod',          // Código real de la venta
            'um.umenom as umecod',
            DB::raw('SUM(det.cdecan) as cantidad_total_producto'),
            DB::raw('SUM(det.cdevve) as total_venta_producto')
        )
        ->join('cpe_cabecera as cab', 'det.IdCpe_cabecera', '=', 'cab.IdCpe_cabecera')
        ->leftJoin('productos as p', 'det.IdProducto', '=', 'p.IdProducto')
        ->leftJoin('unidad_medida as um', 'p.umecod', '=', 'um.umecod')
        ->whereBetween('cab.ccafem', [$fec_ini, $fec_fin])
        ->whereNull('cab.ccabaj')
        ->whereIn('cab.tdocod', ['01', '03', '13']);

    if (!empty($suc_id)) { $productos_query->where('cab.id_empresa_negocio', $suc_id); }
    if (!empty($ven_id) && $ven_id != 'Todos') { $productos_query->where('cab.IdUsuario_ven', $ven_id); }
    if (!empty($cli_id) && $cli_id != 'Todos') { $productos_query->where('cab.clicod', $cli_id); }

    $productos = $productos_query->groupBy('cat_id', 'det.IdProducto', 'pronom', 'det.procod', 'um.umenom')
                                 ->orderBy('pronom')->get();

    // --- 3. AGRUPAR PRODUCTOS POR CATEGORÍA USANDO COLECCIONES ---
    $productos_agrupados = $productos->groupBy('cat_id');

    // --- 4. ARMAR EL REPORTE FINAL (ESTRUCTURA DE ARRAY) ---
    $reporte_agrupado = [];
    foreach ($categorias as $cat) {
        $reporte_agrupado[$cat->cat_id] = [
            'cat_nom' => $cat->cat_nom,
            'total_categoria_cantidad' => $cat->total_categoria_cantidad,
            'total_categoria_ventas' => $cat->total_categoria_ventas,
            'productos' => $productos_agrupados->get($cat->cat_id, collect())
        ];
    }

    $total_general_ventas = $categorias->sum('total_categoria_ventas');
    $total_general_cantidad = $categorias->sum('total_categoria_cantidad');

    $vista = view('empresas.reportes.ventas.formatos.reporte_ventas_por_categoria_detallada', 
        compact('reporte_agrupado', 'fec_ini', 'fec_fin', 'dato_vendedor', 'dato_cliente', 'sucursal', 'total_general_ventas', 'total_general_cantidad')
    )->render();
        }elseif ($tip_rep == '16') {
    // REPORTE 16: TOP 5 MÁS VENDIDOS POR CATEGORÍA (AGRUPADO POR CÓDIGO)
    
    $productos_ventas = DB::table('cpe_detalle as det')
        ->join('cpe_cabecera as cab', 'det.IdCpe_cabecera', '=', 'cab.IdCpe_cabecera')
        ->leftJoin('productos as p', 'det.IdProducto', '=', 'p.IdProducto')
        ->leftJoin('categorias as cat', 'p.cat_id', '=', 'cat.cat_id')
        ->select(
            DB::raw('IFNULL(cat.cat_id, 0) as cat_id'),
            DB::raw('IFNULL(cat.cat_nom, "SIN CATEGORÍA / OTROS") as cat_nom'),
            'det.procod as codigo', // Agrupamos por el CÓDIGO
            DB::raw('MAX(det.cdedes) as producto'), // Tomamos el nombre más completo de los que existan
            DB::raw('SUM(det.cdecan) as cantidad_total'),
            DB::raw('SUM(det.cdevve) as monto_total')
        )
        ->whereBetween('cab.ccafem', [$fec_ini, $fec_fin])
        ->whereNull('cab.ccabaj')
        ->whereIn('cab.tdocod', ['01', '03', '13']);

    if (!empty($suc_id)) { 
        $productos_ventas->where('cab.id_empresa_negocio', $suc_id); 
    }

    // EL TRUCO ESTÁ AQUÍ: Quitamos 'producto' del groupBy para que se junten por 'codigo'
    $productos = $productos_ventas->groupBy('cat_id', 'cat_nom', 'codigo')
        ->get();

    // Agrupamos por categoría y filtramos los TOP 5 de cada una
    $reporte_top_categoria = $productos->groupBy('cat_id')->map(function ($items) {
        return [
            'cat_nom' => $items->first()->cat_nom,
            'productos' => $items->sortByDesc('cantidad_total')->take(5)
        ];
    })->sortBy('cat_nom');

    $vista = view('empresas.reportes.ventas.formatos.reporte_top_por_categoria', 
        compact('reporte_top_categoria', 'fec_ini', 'fec_fin', 'sucursal')
    )->render();
}elseif ($tip_rep == '17') {
    // REPORTE 17: FLUJO DE VENTAS POR HORA (OPTIMIZADO)
    
    $query = DB::table('cpe_cabecera')
        ->select(
            // Extraemos la hora directamente de fecha_hora de la cabecera
            DB::raw('HOUR(fecha_hora) as hora'),
            DB::raw('COUNT(*) as total_pedidos')
        )
        // Filtramos por el rango de fechas que eligió el usuario
        ->whereBetween('ccafem', [$fec_ini, $fec_fin])
        // Validamos que la venta no esté anulada
        ->whereNull('ccabaj')
        // Filtramos por tipos de documento (Boleta, Factura, Nota de venta)
        ->whereIn('tdocod', ['01', '03', '13']);

    // Si seleccionaste una sucursal específica
    if (!empty($suc_id)) { 
        $query->where('id_empresa_negocio', $suc_id); 
    }

    $resultados = $query->groupBy(DB::raw('HOUR(fecha_hora)'))
        ->orderBy('hora', 'ASC')
        ->get();

    // Preparamos los datos para el gráfico (del 00:00 al 23:00)
    $labels = [];
    $data = [];
    $mapaHoras = [];

    foreach ($resultados as $res) {
        $mapaHoras[$res->hora] = $res->total_pedidos;
    }

    for ($i = 0; $i < 24; $i++) {
        $labels[] = str_pad($i, 2, '0', STR_PAD_LEFT) . ":00";
        $data[] = isset($mapaHoras[$i]) ? $mapaHoras[$i] : 0;
    }

    $maxVal = count($data) > 0 ? max($data) : 0;
    $backgroundColors = [];
    $borderColors = [];

    foreach ($data as $valor) {
        if ($valor > 0 && $valor == $maxVal) {
            // Color destacado para la hora pico (Naranja/Dorado)
            $backgroundColors[] = 'rgba(255, 159, 64, 0.8)';
            $borderColors[] = 'rgba(255, 159, 64, 1)';
        } else {
            // Color estándar (Azul suave)
            $backgroundColors[] = 'rgba(54, 162, 235, 0.5)';
            $borderColors[] = 'rgba(54, 162, 235, 1)';
        }
    }

    $vista = view('empresas.reportes.ventas.formatos.reporte_flujo_horas', 
        compact('labels', 'data', 'fec_ini', 'fec_fin', 'sucursal', 'backgroundColors', 'borderColors')
    )->render();
}elseif($tip_rep=='13'){
        // REPORTE 13: MEDIOS DE PAGO
        // Corregido: Se añaden las consultas necesarias
            
        $vent_med_pag = $cons_ventas->obtenerVentasMediosPagos($suc_id,$fec_ini,$fec_fin,$cli_id,$caj_id,$ven_id);
        $res_vent_med_pag = $cons_ventas->obtenerResumenVentasMediosPagos($suc_id,$fec_ini,$fec_fin,$cli_id,$caj_id,$ven_id);
        $total = $vent_med_pag->sum('total');

        $med_pag = DB::tABLE('medios_pagos')->select('id_med_pag','nom_med_pag')->get();
        
        $cont_mp = 9+count($med_pag);

        $vista = view('empresas.reportes.ventas.formatos.reporte_ventas_medios_pagos',compact('fec_ini','fec_fin','sucursal','vent_med_pag','res_vent_med_pag','total','med_pag','cont_mp'))->render();

    }


    // --- 4. RESPUESTA AJAX (Esto queda igual) ---
    if($request->ajax()){
        return response()->json(['vista'=>$vista]);

    }

}
   

    public function generarExcelVentas(Request $request)
{
    // 1. OBTENCIÓN DE FILTROS
    $fec_ini = $request->get('fec_ini');
    $fec_fin = $request->get('fec_fin');
    $ven_id = $request->get('ven_id');
    $cli_id = $request->get('cli_id');
    $suc_id = $request->get('suc_id');
    $caj_id = $request->get('caj_id');
    $tip_rep = $request->get('tip_rep');
    $IdProducto = $request->get('IdProducto');

    // Mover la obtención de la sucursal solo si es realmente necesaria (ejemplo de optimización)
    $sucursal = null;
    if (!empty($suc_id)) {
        $sucursal = DB::table('empresa_negocios')->where('id_empresa_negocio', $suc_id)->first();
    }

    $cons_ventas = new ReportesVentas(); // Se instancia aquí para usarla dentro de los if/else

    // =========================================================================
    // TIPOS DE REPORTE CON OPTIMIZACIÓN CRÍTICA (N+1, loadView -> fromArray)
    // =========================================================================

    if ($tip_rep == '14') { 
    // Usamos leftJoin para no perder ventas de productos eliminados
    $query = DB::table('cpe_cabecera as cab')
        ->join('cpe_detalle as det', 'cab.IdCpe_cabecera', '=', 'det.IdCpe_cabecera')
        ->leftJoin('productos as p', 'det.IdProducto', '=', 'p.IdProducto')
        ->leftJoin('categorias as cat', 'p.cat_id', '=', 'cat.cat_id') 
        ->select(
            // Si no hay categoría, lo agrupamos como "BORRADOS / OTROS"
            DB::raw('IFNULL(cat.cat_nom, "BORRADOS / OTROS") as Categoria'), 
            DB::raw('SUM(det.cdecan) as total_items_vendidos'),
            DB::raw('SUM(det.cdevve) as total_ventas')
        )
        ->whereBetween('cab.ccafem', [$fec_ini, $fec_fin])
        ->whereNull('cab.ccabaj') 
        ->whereIn('cab.tdocod', ['01', '03', '13']); 

    if (!empty($suc_id)) {
        $query->where('cab.id_empresa_negocio', $suc_id);
    }

    $salesByCategory = $query->groupBy('Categoria')
                             ->orderByDesc('total_ventas')
                             ->get();

    $data_for_excel[] = ['CATEGORÍA', 'ITEMS VENDIDOS', 'TOTAL VENTA (S/.)'];
    
    $gran_total_items = 0;
    $gran_total_ventas = 0;

    foreach ($salesByCategory as $sale) {
        $data_for_excel[] = [
            $sale->Categoria,
            (float)$sale->total_items_vendidos,
            (float)$sale->total_ventas,
        ];
        $gran_total_items += $sale->total_items_vendidos;
        $gran_total_ventas += $sale->total_ventas;
    }

    // AGREGAMOS FILA DE TOTAL GENERAL
    $data_for_excel[] = ['TOTAL GENERAL', (float)$gran_total_items, (float)$gran_total_ventas];

    return \Excel::create('Reporte_Ventas_Por_Categoria', function($excel) use ($data_for_excel) {
        $excel->sheet('Ventas por Categoría', function($sheet) use ($data_for_excel) {
            $sheet->fromArray($data_for_excel, null, 'A1', false, false);
            $sheet->cells('A1:C1', function($cells) {
                $cells->setBackground('#3c8dbc')->setFontColor('#ffffff')->setFontWeight('bold');
            });
            // Ponemos en negrita la última fila (el total)
            $sheet->row(count($data_for_excel), function($row) { $row->setFontWeight('bold'); });
        });
    })->export('xlsx');
    }elseif ($tip_rep == '15') { 
        // 1. Obtener Categorías (Basado en el detalle de ventas)
        $resumen_cats = DB::table('cpe_detalle as det')
            ->join('cpe_cabecera as cab', 'det.IdCpe_cabecera', '=', 'cab.IdCpe_cabecera')
            ->leftJoin('productos as p', 'det.IdProducto', '=', 'p.IdProducto')
            ->leftJoin('categorias as cat', 'p.cat_id', '=', 'cat.cat_id')
            ->select(
                DB::raw('IFNULL(cat.cat_id, 0) as cat_id'), 
                DB::raw('IFNULL(cat.cat_nom, "BORRADOS / OTROS") as cat_nom'), 
                DB::raw('SUM(det.cdecan) as total_qty'), 
                DB::raw('SUM(det.cdevve) as total_money'))
            ->whereBetween('cab.ccafem', [$fec_ini, $fec_fin])
            ->whereNull('cab.ccabaj')
            ->whereIn('cab.tdocod', ['01', '03', '13']);

        if (!empty($suc_id)) { $resumen_cats->where('cab.id_empresa_negocio', $suc_id); }
        // (Agregar aquí tus otros filtros de vendedor/cliente si los necesitas)

        $resumen_cats = $resumen_cats->groupBy('cat_id', 'cat_nom')->orderBy('cat_nom')->get();

        // 2. Obtener Productos (Usando cdedes por si el producto ya no existe)
        $productos_q = DB::table('cpe_detalle as det')
            ->select(
                DB::raw('IFNULL(p.cat_id, 0) as cat_id'), 
                'det.procod', 
                'det.cdedes as pronom', 
                'um.umenom',
                DB::raw('SUM(det.cdecan) as cant'), 
                DB::raw('SUM(det.cdevve) as total'))
            ->join('cpe_cabecera as cab', 'det.IdCpe_cabecera', '=', 'cab.IdCpe_cabecera')
            ->leftJoin('productos as p', 'det.IdProducto', '=', 'p.IdProducto')
            ->leftJoin('unidad_medida as um', 'p.umecod', '=', 'um.umecod')
            ->whereBetween('cab.ccafem', [$fec_ini, $fec_fin])
            ->whereNull('cab.ccabaj')
            ->whereIn('cab.tdocod', ['01', '03', '13']);

        if (!empty($suc_id)) { $productos_q->where('cab.id_empresa_negocio', $suc_id); }

        $productos = $productos_q->groupBy('cat_id', 'det.IdProducto', 'det.procod', 'pronom', 'um.umenom')->get()->groupBy('cat_id');

        // 3. Preparar datos para Excel
        $data_for_excel = [];
        $data_for_excel[] = ['REPORTE DETALLADO POR CATEGORÍAS'];
        $data_for_excel[] = ['Desde: '.$fec_ini, 'Hasta: '.$fec_fin];
        $data_for_excel[] = ['']; 

        $total_final_qty = 0;
        $total_final_money = 0;

        foreach ($resumen_cats as $cat) {
            $data_for_excel[] = ['CATEGORÍA:', $cat->cat_nom, '', 'TOTAL QTY:', (float)$cat->total_qty, 'TOTAL VENTA:', (float)$cat->total_money];
            $data_for_excel[] = ['CÓDIGO', 'PRODUCTO', 'U.M.', 'CANTIDAD', 'TOTAL'];

            $total_final_qty += $cat->total_qty;
            $total_final_money += $cat->total_money;

            if(isset($productos[$cat->cat_id])){
                foreach ($productos[$cat->cat_id] as $p) {
                    $data_for_excel[] = [$p->procod, $p->pronom, $p->umenom, (float)$p->cant, (float)$p->total];
                }
            }
            $data_for_excel[] = ['']; 
        }

        // FILA DE CIERRE CON EL TOTAL GENERAL DE TODO EL REPORTE
        $data_for_excel[] = ['TOTAL GENERAL DEL REPORTE', '', '', 'SUMA TOTAL QTY:', (float)$total_final_qty, 'SUMA TOTAL VENTAS:', (float)$total_final_money];

        return \Excel::create('Ventas_Detallado_Categoria', function($excel) use ($data_for_excel) {
            $excel->sheet('Reporte', function($sheet) use ($data_for_excel) {
                $sheet->fromArray($data_for_excel, null, 'A1', false, false);
                $sheet->row(1, function($row) { $row->setFontWeight('bold'); });
                // Negrita a la última fila de totales
                $sheet->row(count($data_for_excel), function($row) { $row->setFontWeight('bold'); });
            });
        })->export('xlsx');
    }elseif ($tip_rep == '17') {
    // REPORTE 17: FLUJO DE VENTAS POR HORA EN EXCEL
    
    // 1. OBTENEMOS LOS DATOS AGRUPADOS POR HORA
    $query = DB::table('cpe_cabecera')
        ->select(
            DB::raw('HOUR(fecha_hora) as hora'),
            DB::raw('COUNT(*) as total_pedidos')
        )
        ->whereBetween('ccafem', [$fec_ini, $fec_fin])
        ->whereNull('ccabaj')
        ->whereIn('tdocod', ['01', '03', '13']);

    if (!empty($suc_id)) { 
        $query->where('id_empresa_negocio', $suc_id); 
    }

    $resultados = $query->groupBy(DB::raw('HOUR(fecha_hora)'))
        ->orderBy('hora', 'ASC')
        ->get();

    // 2. PREPARAMOS EL MAPA DE 24 HORAS (PARA NO DEJAR HUECOS)
    $mapaHoras = [];
    foreach ($resultados as $res) {
        $mapaHoras[$res->hora] = $res->total_pedidos;
    }

    // 3. PREPARAMOS LOS DATOS PARA EL EXCEL
    $data_for_excel = [];
    $data_for_excel[] = ['REPORTE DE FLUJO TRANSACCIONAL POR HORAS'];
    $data_for_excel[] = ['Desde: ' . $fec_ini, 'Hasta: ' . $fec_fin];
    $data_for_excel[] = ['Sucursal:', $sucursal->emp_nom ?? 'Todas'];
    $data_for_excel[] = ['']; // Espacio en blanco

    // Encabezados de la tabla
    $data_for_excel[] = ['HORA DEL DÍA', 'CANTIDAD DE PEDIDOS / VENTAS', 'ESTADO SUGERIDO'];

    $total_general = 0;

    for ($i = 0; $i < 24; $i++) {
        $hora_formateada = str_pad($i, 2, '0', STR_PAD_LEFT) . ":00";
        $cantidad = isset($mapaHoras[$i]) ? (int)$mapaHoras[$i] : 0;
        
        // Un toque extra: sugerencia según la cantidad
        $sugerencia = '';
        if ($cantidad > 15) { // Ejemplo: más de 15 pedidos es "Hora Punta"
            $sugerencia = 'ALTO FLUJO - REFORZAR PERSONAL';
        } elseif ($cantidad > 0 && $cantidad <= 5) {
            $sugerencia = 'BAJO FLUJO - PERSONAL MÍNIMO';
        } elseif ($cantidad == 0) {
            $sugerencia = 'SIN ACTIVIDAD';
        } else {
            $sugerencia = 'FLUJO NORMAL';
        }

        $data_for_excel[] = [
            $hora_formateada,
            $cantidad,
            $sugerencia
        ];

        $total_general += $cantidad;
    }

    $data_for_excel[] = ['']; 
    $data_for_excel[] = ['TOTAL DE PEDIDOS EN EL RANGO', $total_general, ''];

    // 4. GENERACIÓN DEL ARCHIVO (Usando el formato de Laravel-Excel que usas)
    return \Excel::create('Reporte_Flujo_Horas_HolaP', function($excel) use ($data_for_excel) {
        $excel->sheet('Flujo por Horas', function($sheet) use ($data_for_excel) {
            $sheet->fromArray($data_for_excel, null, 'A1', false, false);
            
            // Estilos básicos: Negrita al título y a la fila de totales
            $sheet->row(1, function($row) { $row->setFontWeight('bold'); });
            $sheet->row(5, function($row) { $row->setFontWeight('bold'); $row->setBackground('#DFF0D8'); });
            
            // Ajustar el ancho de las columnas para que se lea bien en Iquitos y en todo el mundo
            $sheet->setColumnFormat([
                'B' => '0' // Formato número sin decimales para los pedidos
            ]);
        });
    })->export('xlsx');
}elseif ($tip_rep == '1' || $tip_rep == '2') {
        // Reporte 1 (General) y Reporte 2 (Detallado) consolidados como LISTADO
        
        // 1. Obtención de datos desde el modelo
        $registros = $cons_ventas->obtenerTodosLosRegistrosDeVenta(
            $suc_id, $fec_ini, $fec_fin, $cli_id, $caj_id, $ven_id, $tip_rep
        ); 

        if ($registros->isEmpty()) {
             return Redirect::back()->with('error', 'No se encontraron registros de ventas.');
        }

        // 2. Mapeamos los datos para verlos DOCUMENTO POR DOCUMENTO
        $listado = $registros->map(function ($item) {
            return [
                'FECHA'        => $item->ccafem,
                'TIPO DOC'     => $item->tdocod,
                'SERIE-NUMERO' => $item->serie . '-' . $item->correlativo,
                'ESTADO'       => $item->ccabaj ? 'ANULADO' : 'VIGENTE',
                'CLIENTE'      => $item->clirazonsocial,
                'RUC/DNI'      => $item->ccandi,
                'SUBTOTAL'     => (float)$item->subtotal,
                'IGV'          => (float)$item->igv,
                'TOTAL'        => (float)$item->total,
                'TIPO PAGO'    => $item->forma_pago,
            ];
        });

        // 3. Preparamos los encabezados
        $encabezados = ['FECHA', 'TIPO DOC', 'SERIE-NÚMERO', 'ESTADO', 'CLIENTE', 'RUC/DNI', 'SUBTOTAL', 'IGV', 'TOTAL', 'TIPO PAGO'];
        $data_for_excel = $listado->prepend($encabezados)->toArray();

        // 4. Calculamos el total de ventas (solo vigentes) para ponerlo al final
        $suma_total = $registros->where('ccabaj', null)->sum('total');

        $nombre_rep = ($tip_rep == '1') ? 'Reporte_Ventas_General' : 'Reporte_Ventas_Detallado';

        // 5. Generación del Excel
        return \Excel::create($nombre_rep, function($excel) use ($data_for_excel, $suma_total) {
            $excel->sheet('Ventas', function($sheet) use ($data_for_excel, $suma_total) {
                
                // Cargar los datos
                $sheet->fromArray($data_for_excel, null, 'A1', false, false);
                
                // Estilos para la cabecera
                $sheet->cells('A1:J1', function($cells) {
                    $cells->setBackground('#3c8dbc');
                    $cells->setFontColor('#ffffff');
                    $cells->setFontWeight('bold');
                    $cells->setAlignment('center');
                });

                // Formato numérico para las columnas de dinero (G=Subtotal, H=Igv, I=Total)
                $sheet->setColumnFormat([
                    'G' => '0.00',
                    'H' => '0.00',
                    'I' => '0.00',
                ]);

                // Agregamos una fila al final con el TOTAL GENERAL
                $sheet->appendRow(['', '', '', '', '', 'TOTAL VIGENTES:', '', '', number_format($suma_total, 2), '']);
                
                // Ponemos en negrita la fila del total (última fila)
                $lastRow = $sheet->getHighestRow();
                $sheet->row($lastRow, function($row) {
                    $row->setFontWeight('bold');
                });

            });
        })->export('xlsx');
    } elseif ($tip_rep == '2') { // REPORTE DE VENTAS DETALLADO (Listado)
            
            $listado = $registros->map(function ($item) {
                return [
                    'FECHA' => $item->ccafem,
                    'TIPO DOC' => $item->tdocod,
                    'SERIE-NUMERO' => $item->serie . '-' . $item->correlativo,
                    'ESTADO' => $item->ccabaj ? 'ANULADO' : 'VIGENTE',
                    'CLIENTE' => $item->clirazonsocial,
                    'RUC/DNI' => $item->ccandi,
                    'TOTAL' => number_format($item->total, 2),
                    'TIPO PAGO' => $item->forma_pago,
                ];
            });

            $encabezados = ['FECHA', 'TIPO DOC', 'SERIE-NÚMERO', 'ESTADO', 'CLIENTE', 'RUC/DNI', 'TOTAL', 'TIPO PAGO'];
            $data_for_excel = $listado->prepend($encabezados)->toArray();

            return \Excel::create('Reporte_Ventas_Detallado', function($excel) use ($data_for_excel) {
                $excel->sheet('Detallado', function($sheet) use ($data_for_excel) {
                    $sheet->fromArray($data_for_excel, null, 'A1', false, false);
                    $sheet->cells('A1:H1', function($cells) {
                        $cells->setBackground('#3c8dbc');
                        $cells->setFontColor('#ffffff');
                        $cells->setFontWeight('bold');
                    });
                });
            })->export('xlsx');
        
    } elseif ($tip_rep == '3') { // REPORTE SUNAT
     
    // ... tu lógica de obtención y mapeo de datos
    $registros = $cons_ventas->obtenerDatosReporteSunat($suc_id, $fec_ini, $fec_fin, $cli_id, $caj_id, $ven_id);
    $factura = $registros->where('tdocod', '01');
    $boleta = $registros->where('tdocod', '03');
    $nota_credito = $registros->where('tdocod', '07');
    $all_records = $factura->merge($boleta)->merge($nota_credito);

    $data = $all_records->map(function ($reg) {
        return [
            'FECHA' => $reg->fecha,
            'TIPO_COMPROBANTE' => $reg->tdocod,
            'SERIE' => $reg->serie,
            'NUMERO' => $reg->numero,
            'TIPO_ID' => $reg->documentoidentidad,
            'DNI/RUC' => $reg->numerodocumento,
            'CLIENTE' => $reg->cliente,
            // ¡IMPORTANTE! Quita el formato number_format aquí
            // para que Excel lo reciba como un número puro.
            //'GRAVADO' => (float) $reg->gravado, // <-- Cascada de tipo
            //'IGV' => (float) $reg->igv,         // <-- Cascada de tipo
            'TOTAL' => (float) $reg->total,     // <-- Cascada de tipo
        ];
    });

    $headers = [
        'FECHA', 'TIPO COMPROBANTE', 'SERIE', 'NÚMERO', 'TIPO ID', 'DNI/RUC', 'CLIENTE', 'TOTAL'
    ];
     
    $data_for_excel = $data->prepend($headers)->toArray();

    // Cálculo de totales para el resumen final (Mantenemos number_format aquí, ya que es la fila de resumen)
    $total_ventas_sunat = $registros->whereIn('tdocod', ['01', '03'])->sum('total');
    $total_notas_creditos = $registros->where('tdocod', '07')->sum('total');
    $igv_ventas = $registros->whereIn('tdocod', ['01', '03'])->sum('igv');
    $igv_notas_creditos = $registros->where('tdocod', '07')->sum('igv');

    return \Excel::create('Reporte_Ventas_SUNAT', function($excel) use ($data_for_excel, $total_ventas_sunat, $total_notas_creditos, $igv_ventas, $igv_notas_creditos) {
        $excel->sheet('Reporte_Ventas_SUNAT', function($sheet) use ($data_for_excel, $total_ventas_sunat, $total_notas_creditos, $igv_ventas, $igv_notas_creditos){
            $sheet->fromArray($data_for_excel, null, 'A1', false, false);
             
            // Estilos para el encabezado
            $sheet->cells('A1:J1', function($cells) {
                $cells->setBackground('#3c8dbc');
                $cells->setFontColor('#ffffff');
                $cells->setFontWeight('bold');
            });
            
            // ⭐️⭐️ CÓDIGO CLAVE PARA EL FORMATO NUMÉRICO ⭐️⭐️
            // Columnas H, I, J son las de valores monetarios (GRAVADO, IGV, TOTAL)
            $sheet->setColumnFormat([
                'H' => '0.00', // Formato de número con dos decimales (ej: 12.34)
                //'I' => '0.00',
                //'J' => '0.00',
                // Opcional: para RUC/DNI, asegurar que se vean como texto para que no pierdan ceros iniciales
                'F' => '@', // Formato de texto
            ]);

            // Agregar el resumen al final (ajustar la columna del total si el resumen tiene el mismo formato)
            $sheet->appendRow(['', '', '', '', '', 'TOTAL VENTAS:', number_format($total_ventas_sunat, 2), number_format($igv_ventas, 2), number_format($total_ventas_sunat, 2)]);
            $sheet->appendRow(['', '', '', '', '', 'TOTAL NOTAS CRÉDITO:', number_format($total_notas_creditos, 2), number_format($igv_notas_creditos, 2), number_format($total_notas_creditos, 2)]);
        });
    })->export('xlsx');

}
    
    // =========================================================================
    // TIPOS DE REPORTE CON LÓGICA EXISTENTE PERO SIN LA REDUNDANCIA INICIAL
    // =========================================================================
    
    // A partir de aquí, las consultas deben ser llamadas solo si el tip_rep lo requiere.
    // Ej: Si tip_rep=4 (Ventas Migrar), solo se llama la función necesaria.
    
    elseif ($tip_rep == '4') {
        $ventas_migrar = $cons_ventas->obtenerRegistroVentasMigrar($suc_id,$fec_ini,$fec_fin,$cli_id,$caj_id,$ven_id);
        // ... (Tu código de exportación con loadView, que es más lento)
        // RECOMENDACIÓN: Refactorizar a fromArray si el reporte es tabular.
        Excel::create('Reporte_Ventas_Migrar', function($excel) use ($fec_ini,$fec_fin,$ventas_migrar,$sucursal) {
            $excel->sheet('Reporte_Ventas_Migrar', function($sheet) use ($fec_ini,$fec_fin,$ventas_migrar,$sucursal){
                $sheet->loadView('empresas.reportes.ventas.formatos.reporte_ventas_migrar',compact('fec_ini','fec_fin','ventas_migrar','sucursal'));
            });
        })->export('xlsx');
        return;
    }
    // ... Repetir esta estructura para los demás reportes (5, 6, 7, 8, 9, 10, 13, 18)
    // llamando SÓLO a las funciones de $cons_ventas que realmente necesiten.
    // Ejemplo para tip_rep=5:
    elseif ($tip_rep == '5') {
        $ventas_res_vend = $cons_ventas->obtenerResumenVentasVendedor($suc_id,$fec_ini,$fec_fin,$cli_id,$caj_id,$ven_id);
        $total = $ventas_res_vend->sum('total');
        $dat_vend = DB::table('users')->where('IdUsuario',$ven_id)->first();

        // RECOMENDACIÓN: Refactorizar a fromArray si es posible
        Excel::create('Reporte_Ventas_Vendedor', function($excel) use ($fec_ini,$fec_fin,$sucursal,$dat_vend,$ventas_res_vend,$total) {
            $excel->sheet('Reporte_Resumen_Ventas_Vendedor', function($sheet) use ($fec_ini,$fec_fin,$sucursal,$dat_vend,$ventas_res_vend,$total){
                $sheet->loadView('empresas.reportes.ventas.formatos.reporte_resumen_ventas_vendedor',compact('fec_ini','fec_fin','sucursal','dat_vend','ventas_res_vend','total'));
            });
        })->export('xlsx');
        return;
    }elseif ($tip_rep == '6') { // REPORTE 6: RESUMEN CLIENTE
        $ventas_res_cli = $cons_ventas->obtenerResumenVentasCliente($suc_id, $fec_ini, $fec_fin, $cli_id, $caj_id, $ven_id);
        $total = $ventas_res_cli->sum('total');
        $dat_cli = DB::table('cliente')->where('clicod', $cli_id)->first();

        return \Excel::create('Reporte_Resumen_Ventas_Cliente', function($excel) use ($fec_ini, $fec_fin, $sucursal, $dat_cli, $ventas_res_cli, $total) {
            $excel->sheet('Resumen Cliente', function($sheet) use ($fec_ini, $fec_fin, $sucursal, $dat_cli, $ventas_res_cli, $total) {
                $sheet->loadView('empresas.reportes.ventas.formatos.reporte_resumen_ventas_cliente', compact('fec_ini', 'fec_fin', 'sucursal', 'dat_cli', 'ventas_res_cli', 'total'));
            });
        })->export('xlsx');
    }

    elseif ($tip_rep == '7') { // REPORTE 7: RESUMEN PRODUCTOS
        $vent_res_prod = $cons_ventas->obtenerResumenVentasProductos($suc_id, $fec_ini, $fec_fin, $cli_id, $caj_id, $ven_id, $IdProducto);
        $total = $vent_res_prod->sum('total');

        return \Excel::create('Reporte_Resumen_Ventas_Productos', function($excel) use ($fec_ini, $fec_fin, $sucursal, $vent_res_prod, $total, $IdProducto) {
            $excel->sheet('Resumen Productos', function($sheet) use ($fec_ini, $fec_fin, $sucursal, $vent_res_prod, $total, $IdProducto) {
                $sheet->loadView('empresas.reportes.ventas.formatos.reporte_resumen_ventas_productos', compact('fec_ini', 'fec_fin', 'sucursal', 'vent_res_prod', 'total', 'IdProducto'));
            });
        })->export('xlsx');
    }

    elseif ($tip_rep == '8') { // REPORTE 8: VENTAS DETALLADAS POR PRODUCTO
    $ventas_det_prod = $cons_ventas->obtenerVentasDetallada($suc_id, $fec_ini, $fec_fin, $cli_id, $caj_id, $ven_id, $IdProducto);
    $notas_cre_det_prod = $cons_ventas->obtenerNotasCreditoDetallada($suc_id, $fec_ini, $fec_fin, $cli_id, $caj_id, $ven_id, $IdProducto);
    $anulaciones_det_prod = $cons_ventas->obtenerAnulacionesDetallada($suc_id, $fec_ini, $fec_fin, $cli_id, $caj_id, $ven_id, $IdProducto);
    
    // Calculamos el Neto real para la variable total_final
    $total_v = $ventas_det_prod->sum('cdevve');
    $total_nc = $notas_cre_det_prod->sum('cdevve');
    $total_final = $total_v - $total_nc; 

    // IMPORTANTE: Cambié "total" por "total_final" dentro del use() y del compact()
    return \Excel::create('Reporte_Ventas_Detallada_Productos', function($excel) use ($fec_ini, $fec_fin, $sucursal, $ventas_det_prod, $notas_cre_det_prod, $anulaciones_det_prod, $total_final, $IdProducto) {
        $excel->sheet('Detallado Productos', function($sheet) use ($fec_ini, $fec_fin, $sucursal, $ventas_det_prod, $notas_cre_det_prod, $anulaciones_det_prod, $total_final, $IdProducto) {
            
            $sheet->loadView('empresas.reportes.ventas.formatos.reporte_ventas_detallada_productos', 
                compact('fec_ini', 'fec_fin', 'sucursal', 'ventas_det_prod', 'notas_cre_det_prod', 'anulaciones_det_prod', 'total_final', 'IdProducto'));
        
        });
    })->export('xlsx');
}

    elseif ($tip_rep == '9') { // REPORTE 9: VENTAS ANULADAS
        $ventas_anuladas = $cons_ventas->obtenerVentasAnuladas($suc_id, $fec_ini, $fec_fin, $cli_id, $caj_id, $ven_id);
        $vent_res_prod = $cons_ventas->obtenerResumenVentasProductos($suc_id, $fec_ini, $fec_fin, $cli_id, $caj_id, $ven_id, $IdProducto);
        $total = $vent_res_prod->sum('total');

        return \Excel::create('Reporte_Ventas_Anuladas', function($excel) use ($fec_ini, $fec_fin, $sucursal, $ventas_anuladas, $total, $IdProducto) {
            $excel->sheet('Ventas Anuladas', function($sheet) use ($fec_ini, $fec_fin, $sucursal, $ventas_anuladas, $total, $IdProducto) {
                $sheet->loadView('empresas.reportes.ventas.formatos.reporte_ventas_anuladas', compact('fec_ini', 'fec_fin', 'sucursal', 'ventas_anuladas', 'total', 'IdProducto'));
            });
        })->export('xlsx');
    }

    elseif ($tip_rep == '13') { // REPORTE 13: MEDIOS DE PAGO
        $vent_med_pag = $cons_ventas->obtenerVentasMediosPagos($suc_id, $fec_ini, $fec_fin, $cli_id, $caj_id, $ven_id);
        $res_vent_med_pag = $cons_ventas->obtenerResumenVentasMediosPagos($suc_id, $fec_ini, $fec_fin, $cli_id, $caj_id, $ven_id);
        $total = $vent_med_pag->sum('total');
        $med_pag = DB::table('medios_pagos')->select('id_med_pag', 'nom_med_pag')->get();
        $cont_mp = 9 + count($med_pag);

        return \Excel::create('Reporte_Ventas_Medios_Pago', function($excel) use ($fec_ini, $fec_fin, $sucursal, $vent_med_pag, $res_vent_med_pag, $total, $med_pag, $cont_mp) {
            $excel->sheet('Medios de Pago', function($sheet) use ($fec_ini, $fec_fin, $sucursal, $vent_med_pag, $res_vent_med_pag, $total, $med_pag, $cont_mp) {
                $sheet->loadView('empresas.reportes.ventas.formatos.reporte_ventas_medios_pagos', compact('fec_ini', 'fec_fin', 'sucursal', 'vent_med_pag', 'res_vent_med_pag', 'total', 'med_pag', 'cont_mp'));
            });
        })->export('xlsx');
    }
}
   

   public function generarPDFVentas(Request $request){

        $fec_ini = $request->get('fec_ini');
        $fec_fin = $request->get('fec_fin');
        $ven_id = $request->get('ven_id');
        $cli_id = $request->get('cli_id');
        $suc_id = $request->get('suc_id');
        $caj_id = $request->get('caj_id');
        $tip_rep = $request->get('tip_rep');
        $IdProducto = $request->get('IdProducto');

        //$IdProducto = $request->get('IdProducto'); // Para otros reportes
        $cli_id = $request->get('cli_id'); // Para otros reportes
        $caj_id = $request->get('caj_id'); // Para otros reportes
        $ven_id = $request->get('ven_id'); // Para otros reportes

        $sucursal = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',$suc_id)->first();
        $empresa = DB::table('empresa')->where('IdEmpresa', Auth::user()->IdEmpresa)->first(); // Asumiendo que necesitas datos de la empresa principal

        if($tip_rep == '14'){ // Ventas por Categoría
    $query = DB::table('cpe_cabecera as cab')
        ->join('cpe_detalle as det', 'cab.IdCpe_cabecera', '=', 'det.IdCpe_cabecera')
        // Cambiamos a leftJoin para no perder datos de productos borrados
        ->leftJoin('productos as p', 'det.IdProducto', '=', 'p.IdProducto')
        ->leftJoin('categorias as cat', 'p.cat_id', '=', 'cat.cat_id')
        ->select(
            // Si no hay categoría, lo marcamos como Borrados
            DB::raw('IFNULL(cat.cat_nom, "SIN CATEGORÍA / BORRADOS") as categoria_nombre'),
            DB::raw('SUM(det.cdecan) as total_items_vendidos_categoria'),
            DB::raw('SUM(det.cdevve) as total_ventas_categoria')
        )
        ->whereBetween('cab.ccafem', [$fec_ini, $fec_fin])
        ->whereNull('cab.ccabaj')
        ->whereIn('cab.tdocod', ['01', '03', '13']);

    if (!empty($suc_id)) {
        $query->where('cab.id_empresa_negocio', $suc_id);
    }

    $salesByCategory = $query->groupBy('categoria_nombre') // Agrupamos por el nombre calculado
                             ->orderByDesc('total_ventas_categoria')
                             ->get();
    
    $pdf = PDF::loadView('empresas.reportes.ventas.formatos.reporte_ventas_por_categoria_pdf', compact('salesByCategory', 'fec_ini', 'fec_fin', 'sucursal', 'empresa'));
    return $pdf->stream('Reporte_Ventas_Por_Categoria_'.date('YmdHis').'.pdf');

} elseif ($tip_rep == '15') {
    // --- 1. RESUMEN DE CATEGORÍAS ---
    $resumen_cats = DB::table('cpe_detalle as det')
        ->join('cpe_cabecera as cab', 'det.IdCpe_cabecera', '=', 'cab.IdCpe_cabecera')
        ->leftJoin('productos as p', 'det.IdProducto', '=', 'p.IdProducto')
        ->leftJoin('categorias as cat', 'p.cat_id', '=', 'cat.cat_id')
        ->select(
            DB::raw('IFNULL(cat.cat_id, 0) as cat_id'), 
            DB::raw('IFNULL(cat.cat_nom, "SIN CATEGORÍA / BORRADOS") as cat_nom'), 
            DB::raw('SUM(det.cdecan) as total_categoria_cantidad'), 
            DB::raw('SUM(det.cdevve) as total_categoria_ventas'))
        ->whereBetween('cab.ccafem', [$fec_ini, $fec_fin])
        ->whereNull('cab.ccabaj')
        ->whereIn('cab.tdocod', ['01', '03', '13']);

    if (!empty($suc_id)) { $resumen_cats->where('cab.id_empresa_negocio', $suc_id); }
    if (!empty($ven_id) && $ven_id != 'Todos') { $resumen_cats->where('cab.IdUsuario_ven', $ven_id); }
    if (!empty($cli_id) && $cli_id != 'Todos') { $resumen_cats->where('cab.clicod', $cli_id); }

    $categorias = $resumen_cats->groupBy('cat_id', 'cat_nom')->orderBy('cat_nom')->get();

    // --- 2. PRODUCTOS DETALLADOS ---
    $productos_q = DB::table('cpe_detalle as det')
        ->select(
            DB::raw('IFNULL(p.cat_id, 0) as cat_id'), 
            'det.IdProducto', 
            'det.cdedes as pronom', // USAMOS LA DESCRIPCIÓN DE LA VENTA
            'det.procod',          // USAMOS EL CÓDIGO DE LA VENTA
            'um.umenom as umecod',
            DB::raw('SUM(det.cdecan) as cantidad_total_producto'), 
            DB::raw('SUM(det.cdevve) as total_venta_producto'))
        ->join('cpe_cabecera as cab', 'det.IdCpe_cabecera', '=', 'cab.IdCpe_cabecera')
        ->leftJoin('productos as p', 'det.IdProducto', '=', 'p.IdProducto')
        ->leftJoin('unidad_medida as um', 'p.umecod', '=', 'um.umecod')
        ->whereBetween('cab.ccafem', [$fec_ini, $fec_fin])
        ->whereNull('cab.ccabaj')
        ->whereIn('cab.tdocod', ['01', '03', '13']);

    if (!empty($suc_id)) { $productos_q->where('cab.id_empresa_negocio', $suc_id); }

    $productos_agrupados = $productos_q->groupBy('cat_id', 'det.IdProducto', 'pronom', 'det.procod', 'um.umenom')
                                       ->get()
                                       ->groupBy('cat_id');

    // --- 3. ARMAR ARRAY PARA LA VISTA ---
    $reporte_agrupado = [];
    foreach ($categorias as $c) {
        $reporte_agrupado[$c->cat_id] = [
            'cat_nom' => $c->cat_nom,
            'total_categoria_cantidad' => $c->total_categoria_cantidad,
            'total_categoria_ventas' => $c->total_categoria_ventas,
            'productos' => $productos_agrupados->get($c->cat_id, collect())
        ];
    }

    $total_general_ventas = $categorias->sum('total_categoria_ventas');
    $total_general_cantidad = $categorias->sum('total_categoria_cantidad');

    // Generar PDF
    $pdf = \PDF::loadView('empresas.reportes.ventas.formatos.reporte_ventas_por_categoria_detallada', 
        compact('reporte_agrupado', 'fec_ini', 'fec_fin', 'sucursal', 'total_general_ventas', 'total_general_cantidad')
    );

    return $pdf->stream('Reporte_Detallado_Categoria.pdf');
}elseif ($tip_rep == '16') {
    // 1. REPETIMOS LA LÓGICA DE PANTALLA PARA TENER LOS DATOS AGRUPADOS POR CÓDIGO
    $query = DB::table('cpe_detalle as det')
        ->join('cpe_cabecera as cab', 'det.IdCpe_cabecera', '=', 'cab.IdCpe_cabecera')
        ->leftJoin('productos as p', 'det.IdProducto', '=', 'p.IdProducto')
        ->leftJoin('categorias as cat', 'p.cat_id', '=', 'cat.cat_id')
        ->select(
            DB::raw('IFNULL(cat.cat_id, 0) as cat_id'),
            DB::raw('IFNULL(cat.cat_nom, "SIN CATEGORÍA / OTROS") as cat_nom'),
            'det.procod as codigo', 
            DB::raw('MAX(det.cdedes) as producto'), // Nombre más completo
            DB::raw('SUM(det.cdecan) as cantidad_total'),
            DB::raw('SUM(det.cdevve) as monto_total')
        )
        ->whereBetween('cab.ccafem', [$fec_ini, $fec_fin])
        ->whereNull('cab.ccabaj')
        ->whereIn('cab.tdocod', ['01', '03', '13']);

    if (!empty($suc_id)) { 
        $query->where('cab.id_empresa_negocio', $suc_id); 
    }

    // Agrupamos por código para que sume las variaciones de nombre (como lo de la cerveza)
    $productos = $query->groupBy('cat_id', 'cat_nom', 'codigo')->get();

    // 2. FILTRAMOS LOS TOP 5 POR CADA CATEGORÍA
    $reporte_top_categoria = $productos->groupBy('cat_id')->map(function ($items) {
        return [
            'cat_nom' => $items->first()->cat_nom,
            'productos' => $items->sortByDesc('cantidad_total')->take(5)
        ];
    })->sortBy('cat_nom');

    // 3. TOTALES GENERALES PARA EL PIE DEL PDF
    $total_general_ventas = $productos->sum('monto_total');
    $total_general_cantidad = $productos->sum('cantidad_total');

    // 4. CARGAMOS LA VISTA (Asegúrate que el archivo .blade.php exista)
    // ELIMINAMOS LA LÍNEA ->setPaper() QUE DABA ERROR
    $pdf = PDF::loadView('empresas.reportes.ventas.formatos.reporte_top_por_categoria', 
        compact('reporte_top_categoria', 'fec_ini', 'fec_fin', 'sucursal', 'total_general_ventas', 'total_general_cantidad')
    );

    return $pdf->stream('Reporte_Top5_Por_Categoria.pdf');
}elseif ($tip_rep == '17') {
    // 1. REPETIMOS LA LÓGICA PARA TENER LOS DATOS
    $query = DB::table('cpe_cabecera')
        ->select(
            DB::raw('HOUR(fecha_hora) as hora'),
            DB::raw('COUNT(*) as total_pedidos')
        )
        ->whereBetween('ccafem', [$fec_ini, $fec_fin])
        ->whereNull('ccabaj')
        ->whereIn('tdocod', ['01', '03', '13']);

    if (!empty($suc_id)) { 
        $query->where('id_empresa_negocio', $suc_id); 
    }

    $resultados = $query->groupBy(DB::raw('HOUR(fecha_hora)'))
        ->orderBy('hora', 'ASC')
        ->get();

    // 2. PREPARAMOS EL MAPA DE 24 HORAS
    $data_reporte = [];
    $mapaHoras = [];
    foreach ($resultados as $res) {
        $mapaHoras[$res->hora] = $res->total_pedidos;
    }

    $maxVal = count($mapaHoras) > 0 ? max($mapaHoras) : 0;

    for ($i = 0; $i < 24; $i++) {
        $cantidad = isset($mapaHoras[$i]) ? $mapaHoras[$i] : 0;
        $data_reporte[] = [
            'hora' => str_pad($i, 2, '0', STR_PAD_LEFT) . ":00",
            'cantidad' => $cantidad,
            'es_pico' => ($cantidad > 0 && $cantidad == $maxVal) ? true : false
        ];
    }

    $total_pedidos = array_sum(array_column($data_reporte, 'cantidad'));

    // 3. CARGAMOS LA VISTA PARA EL PDF
    $pdf = PDF::loadView('empresas.reportes.ventas.formatos.pdf_reporte_flujo_horas', 
        compact('data_reporte', 'fec_ini', 'fec_fin', 'sucursal', 'total_pedidos')
    );

    return $pdf->stream('Reporte_Flujo_Horas_HolaP.pdf');
}

        $cons_ventas = new ReportesVentas();
 
        $factura = $cons_ventas->obtenerFactura($suc_id,$fec_ini,$fec_fin,$cli_id,$caj_id,$ven_id);
        $boleta = $cons_ventas->obtenerBoleta($suc_id,$fec_ini,$fec_fin,$cli_id,$caj_id,$ven_id);
        $nota_venta = $cons_ventas->obtenerNotaVenta($suc_id,$fec_ini,$fec_fin,$cli_id,$caj_id,$ven_id);
        $nota_credito = $cons_ventas->obtenerNotaCredito($suc_id,$fec_ini,$fec_fin,$cli_id,$caj_id,$ven_id);
        $venta_sunat = $cons_ventas->obtenerVentaSunat($suc_id,$fec_ini,$fec_fin,$cli_id,$caj_id,$ven_id);
        $total_contado = $cons_ventas->obtenerTotalContado($suc_id,$fec_ini,$fec_fin,$cli_id,$caj_id,$ven_id);
        $total_credito = $cons_ventas->obtenerTotalCredito($suc_id,$fec_ini,$fec_fin,$cli_id,$caj_id,$ven_id);
        $total_ventas = $cons_ventas->obtenerTotalVentas($suc_id,$fec_ini,$fec_fin,$cli_id,$caj_id,$ven_id);
        $total_ventas_sunat = $cons_ventas->obtenerTotalVentasSunat($suc_id,$fec_ini,$fec_fin,$cli_id,$caj_id,$ven_id);
        $total_ventas_bolfac = $cons_ventas->obtenerTotalVentasBoletasFacturas($suc_id,$fec_ini,$fec_fin,$cli_id,$caj_id,$ven_id);
        $total_notas_ventas = $cons_ventas->obtenerTotalNotasVentas($suc_id,$fec_ini,$fec_fin,$cli_id,$caj_id,$ven_id);
        $total_vales_cons = $cons_ventas->obtenerTotalValesConsumo($suc_id,$fec_ini,$fec_fin,$cli_id,$caj_id,$ven_id);
        $total_notas_creditos = $cons_ventas->obtenerTotalNotasCreditos($suc_id,$fec_ini,$fec_fin,$cli_id,$caj_id,$ven_id);
        $igv_ventas = $cons_ventas->obtenerIGVVentas($suc_id,$fec_ini,$fec_fin,$cli_id,$caj_id,$ven_id);
        $igv_notas_creditos = $cons_ventas->obtenerIGVNotasCreditos($suc_id,$fec_ini,$fec_fin,$cli_id,$caj_id,$ven_id);
        $venta = $cons_ventas->obtenerVenta($suc_id,$fec_ini,$fec_fin,$cli_id,$caj_id,$ven_id);
        $detalle = $cons_ventas->obtenerVentaDetalle($suc_id,$fec_ini,$fec_fin,$cli_id,$caj_id,$ven_id);
        $vent_res_prod = $cons_ventas->obtenerResumenVentasProductos($suc_id,$fec_ini,$fec_fin,$cli_id,$caj_id,$ven_id,$IdProducto);
        $vent_bolfac_res_prod = $cons_ventas->obtenerResumenVentasBoletasFacturasProductos($suc_id,$fec_ini,$fec_fin,$cli_id,$caj_id,$ven_id);
        $not_cre_res_prod = $cons_ventas->obtenerResumenNotasCreditosProductos($suc_id,$fec_ini,$fec_fin,$cli_id,$caj_id,$ven_id);
        $not_ven_res_prod = $cons_ventas->obtenerResumenNotasVentasProductos($suc_id,$fec_ini,$fec_fin,$cli_id,$caj_id,$ven_id);
        $val_con_res_prod = $cons_ventas->obtenerResumenValesConsumoProductos($suc_id,$fec_ini,$fec_fin,$cli_id,$caj_id,$ven_id);
        $ventas_migrar = $cons_ventas->obtenerRegistroVentasMigrar($suc_id,$fec_ini,$fec_fin,$cli_id,$caj_id,$ven_id);
        $ventas_res_vend = $cons_ventas->obtenerResumenVentasVendedor($suc_id,$fec_ini,$fec_fin,$cli_id,$caj_id,$ven_id);
        $ventas_res_cli = $cons_ventas->obtenerResumenVentasCliente($suc_id,$fec_ini,$fec_fin,$cli_id,$caj_id,$ven_id);
        $ventas_det_prod = $cons_ventas->obtenerVentasDetallada($suc_id,$fec_ini,$fec_fin,$cli_id,$caj_id,$ven_id,$IdProducto);
        $notas_cre_det_prod = $cons_ventas->obtenerNotasCreditoDetallada($suc_id,$fec_ini,$fec_fin,$cli_id,$caj_id,$ven_id,$IdProducto);
        $anulaciones_det_prod = $cons_ventas->obtenerAnulacionesDetallada($suc_id,$fec_ini,$fec_fin,$cli_id,$caj_id,$ven_id,$IdProducto);
        $ventas_anuladas = $cons_ventas->obtenerVentasAnuladas($suc_id,$fec_ini,$fec_fin,$cli_id,$caj_id,$ven_id);

        if($tip_rep=='1'){

          $pdf = PDF::loadView('empresas.reportes.ventas.formatos.reporte_ventas',compact('fec_ini','fec_fin','factura','boleta','nota_venta','nota_credito','venta','total_contado','total_credito','total_ventas'));
             return $pdf->stream('document.pdf');

                $headers = array(
                        'Content-Type: application/pdf',
                );


        }elseif($tip_rep=='2'){

          $pdf = PDF::loadView('empresas.reportes.ventas.formatos.reporte_ventas_detallada',compact('fec_ini','fec_fin','factura','boleta','nota_venta','nota_credito','venta','total_contado','total_credito','total_ventas','detalle','vent_res_prod','not_cre_res_prod','total_notas_creditos','total_notas_ventas','not_ven_res_prod','total_vales_cons','val_con_res_prod','vent_bolfac_res_prod','total_ventas_bolfac'));
             return $pdf->stream('document.pdf');

                $headers = array(
                        'Content-Type: application/pdf',
                );

        }elseif($tip_rep=='3'){

          $pdf = PDF::loadView('empresas.reportes.ventas.formatos.Reporte_Ventas_sunat',compact('fec_ini','fec_fin','factura','boleta','nota_credito','total_notas_creditos','total_ventas_sunat','igv_notas_creditos','igv_ventas'));
             return $pdf->stream('document.pdf');

                $headers = array(
                        'Content-Type: application/pdf',
                );

        }elseif($tip_rep=='4'){


            $pdf = PDF::loadView('empresas.reportes.ventas.formatos.reporte_ventas_migrar',compact('fec_ini','fec_fin','factura','boleta','nota_venta','nota_credito','venta','total_contado','total_credito','total_ventas','ventas_migrar','sucursal'));
             return $pdf->stream('document.pdf');

                $headers = array(
                        'Content-Type: application/pdf',
                );


          

         }elseif($tip_rep=='5'){

            $total = $ventas_res_vend->sum('total');
            $dat_vend = DB::tABLE('users')->where('IdUsuario',$ven_id)->first();

            $pdf = PDF::loadView('empresas.reportes.ventas.formatos.reporte_resumen_ventas_vendedor',compact('fec_ini','fec_fin','sucursal','dat_vend','ventas_res_vend','total'));
             return $pdf->stream('document.pdf');

                $headers = array(
                        'Content-Type: application/pdf',
                );


        }elseif($tip_rep=='5'){

            $total = $ventas_res_cli->sum('total');
            $dat_vend = DB::tABLE('cliente')->where('clicod',$cli_id)->first();

            $pdf = PDF::loadView('empresas.reportes.ventas.formatos.reporte_resumen_ventas_cliente',compact('fec_ini','fec_fin','sucursal','dat_cli','ventas_res_cli','total'));
            return $pdf->stream('document.pdf');

                $headers = array(
                        'Content-Type: application/pdf',
                );


        }elseif($tip_rep=='6'){

           return view('empresas.reportes.ventas.buscadores.buscar_ventas_formato_sunat',compact('vendedor','cajero','cliente','sucursal','fec_ini','fec_fin'));

        }elseif($tip_rep=='7'){


            $total = $vent_res_prod->sum('precio');

       
            $pdf = PDF::loadView('empresas.reportes.ventas.formatos.reporte_resumen_ventas_productos',compact('fec_ini','fec_fin','sucursal','vent_res_prod','total','IdProducto'));
            return $pdf->stream('document.pdf');

                $headers = array(
                        'Content-Type: application/pdf',
                );         

        }elseif($tip_rep=='8'){
            // 1. Ejecutamos las consultas para obtener los datos reales
            $ventas_det_prod = $cons_ventas->obtenerVentasDetallada($suc_id, $fec_ini, $fec_fin, $cli_id, $caj_id, $ven_id, $IdProducto);
            $notas_cre_det_prod = $cons_ventas->obtenerNotasCreditoDetallada($suc_id, $fec_ini, $fec_fin, $cli_id, $caj_id, $ven_id, $IdProducto);
            $anulaciones_det_prod = $cons_ventas->obtenerAnulacionesDetallada($suc_id, $fec_ini, $fec_fin, $cli_id, $caj_id, $ven_id, $IdProducto);

            // 2. Calculamos el total final (Ventas menos Notas de Crédito)
            $total_v = $ventas_det_prod->sum('cdevve');
            $total_nc = $notas_cre_det_prod->sum('cdevve');
            $total_final = $total_v - $total_nc; 

            // 3. Cargamos la vista con las variables correctas
            // Eliminamos la línea ->setPaper() que causaba el error
            $pdf = PDF::loadView('empresas.reportes.ventas.formatos.reporte_ventas_detallada_productos', 
                compact('fec_ini', 'fec_fin', 'sucursal', 'ventas_det_prod', 'notas_cre_det_prod', 'anulaciones_det_prod', 'total_final', 'IdProducto')
            );
            
            // 4. Retornamos el stream igual que en tu reporte 7
            return $pdf->stream('Reporte_Ventas_Detallado.pdf');
        }elseif($tip_rep=='9'){


            $pdf = PDF::loadView('empresas.reportes.ventas.formatos.reporte_ventas_anuladas',compact('fec_ini','fec_fin','sucursal','ventas_anuladas','IdProducto','total_contado','total_credito','total_ventas'));
            
            return $pdf->stream('document.pdf');

                $headers = array(
                        'Content-Type: application/pdf',
            );



        }elseif($tip_rep=='10'){

                
            $pdf = PDF::loadView('empresas.reportes.ventas.formatos.reporte_ventas_anuladas_detalladas',compact('fec_ini','fec_fin','sucursal','anulaciones_det_prod','total','IdProducto','total_contado','total_credito','total_ventas'));
            
            return $pdf->stream('document.pdf');

                $headers = array(
                        'Content-Type: application/pdf',
            );
               

          }elseif($tip_rep=='13'){

             
                 
              $vent_med_pag = $cons_ventas->obtenerVentasMediosPagos($suc_id,$fec_ini,$fec_fin,$cli_id,$caj_id,$ven_id);
              $res_vent_med_pag = $cons_ventas->obtenerResumenVentasMediosPagos($suc_id,$fec_ini,$fec_fin,$cli_id,$caj_id,$ven_id);
              $total = $vent_med_pag->sum('total');

              $med_pag = DB::tABLE('medios_pagos')->select('id_med_pag','nom_med_pag')->get();
            
              $cont_mp = 9+count($med_pag);

              ini_set("pcre.backtrack_limit", "50000000000");

                $pdf = PDF::loadView('empresas.reportes.ventas.formatos.reporte_ventas_medios_pagos',compact('fec_ini','fec_fin','sucursal','vent_med_pag','res_vent_med_pag','total','cont_mp','med_pag'));
            
                return $pdf->stream('document.pdf');

                $headers = array(
                    'Content-Type: application/pdf',
                );
                       

             

        }


        if($request->ajax()){
            return response()->json(['vista'=>$vista]);

        }

        
  }

    public function arqueodiario(Request $request){

      return view('empresas.reportes.reporte_arqueo_diario');

    }

  

 public function generararqueodiario(Request $request){
        
         $medios = DB::tABLE('cuentas_cobrar_medios')->get();

     foreach($medios as $med){

        if($med->med_pag_id=='4'){
                 DB::tABLE('cuentas_cobrar_detalle')
                 ->where('numero_recibo',$med->numero_recibo)
                 ->where('cue_cob_det_id',$med->cue_cob_det_id)
                 ->update(['monto_efectivo'=>$med->monto]); 
        }

         if($med->med_pag_id=='5'){
                DB::tABLE('cuentas_cobrar_detalle')
                ->where('numero_recibo',$med->numero_recibo)
                ->where('cue_cob_det_id',$med->cue_cob_det_id)
                ->update(['monto_deposito'=>$med->monto]);
        }


        
       
      }


        $cliente = $request->get('cliente');
        $documento = $request->get('documento');
 
        $fecha = $request->get('fecin');

        $sucursal = DB::tABLE('empresa_negocios')
        ->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
        ->first();

        $empresa = DB::tABLE('empresa')
        ->where('IdEmpresa',$sucursal->IdEmpresa)
        ->first();


        $usuario = DB::tABLE('users')->where('IdUsuario',Auth::user()->IdUsuario)->first();
        
        $registroventascontado = DB::tABLE('cpe_cabecera as cpe_c')->select('fecha_hora','ccacodsun','ccafem','tdodes','cpe_c.serdoc','cpe_c.numdoc','tdides','cpe_c.ccandi','cpe_c.ccanom','monnom','cpe_c.ccaitv','cpe_c.IdEmpresa','cpe_c.tdocod','cpe_c.IdCpe_cabecera','cpe_c.ccanot','cpe_c.ccabaj','cpe_c.codhash','cpe_c.estado','cpe_c.enviado','totalcontado','totalcredito')
        ->join('tipo_documento as tip_d','cpe_c.tdocod','=','tip_d.tdocod')
        ->join('tipo_documento_identidad as tdi','cpe_c.tdicod','=','tdi.tdicod')
        ->join('moneda as mon','cpe_c.moncod','=','mon.moncod')
        ->whereNull('cpe_c.ccabaj')
        ->where('cpe_c.id_empresa_negocio',$sucursal->id_empresa_negocio)
        ->where(function ($query) {
          $query->Where('cpe_c.tdocod','03')
                ->orWhere('cpe_c.tdocod','01')
                ->orWhere('cpe_c.tdocod','13');
              
          })
        ->where('estadopago','CONTADO')
        ->where('ccafem',$fecha)
        ->get();


        $ventascontado = $registroventascontado->sum('ccaitv');
        

        $registroventascredito = DB::tABLE('cpe_cabecera as cpe_c')->select('fecha_hora','ccacodsun','ccafem','tdodes','cpe_c.serdoc','cpe_c.numdoc','tdides','cpe_c.ccandi','cpe_c.ccanom','monnom','cpe_c.ccaitv','cpe_c.IdEmpresa','cpe_c.tdocod','cpe_c.IdCpe_cabecera','cpe_c.ccanot','cpe_c.ccabaj','cpe_c.codhash','cpe_c.estado','cpe_c.enviado','totalcontado','totalcredito')
        ->leftjoin('tipo_documento as tip_d','cpe_c.tdocod','=','tip_d.tdocod')
        ->leftjoin('tipo_documento_identidad as tdi','cpe_c.tdicod','=','tdi.tdicod')
        ->leftjoin('moneda as mon','cpe_c.moncod','=','mon.moncod')
        ->whereNull('cpe_c.ccabaj')
        ->where('cpe_c.id_empresa_negocio',$sucursal->id_empresa_negocio)
        ->where(function ($query) {
          $query->Where('cpe_c.tdocod','03')
                ->orWhere('cpe_c.tdocod','01')
                ->orWhere('cpe_c.tdocod','13');
              
          })
        ->where('estadopago','CREDITO')
        ->where('ccafem',$fecha)
        ->get();
        

        $ventascredito = $registroventascredito->sum('ccaitv');
        
        $cobranzas = DB::tABLE('cuentas_cobrar_detalle')->select('fec_dep','cuentas_cobrar_detalle.abono','comentario','numero_recibo',DB::raw("(SELECT CONCAT(name,apeusu) FROM users WHERE IdUsuario= cuentas_cobrar_detalle.vendedor) as vendedor"),DB::raw("(SELECT clinom FROM cliente WHERE clicod = cuentas_cobrar.clicod) as cliente"),DB::raw("(SELECT clinum FROM cliente WHERE clicod = cuentas_cobrar.clicod) as ruc"),'serdoc','numdoc','cuentas_cobrar.clicod','total_detalle','saldo_detalle','fec_reg','tdocod','ccanom','monto_efectivo','monto_deposito')
            ->join('cuentas_cobrar','cuentas_cobrar.cue_cob_id','cuentas_cobrar_detalle.cue_cob_id')
            ->join('cpe_cabecera','cpe_cabecera.IdCpe_cabecera','cuentas_cobrar.IdCpe_cabecera')
           // ->whereNull('cpe_cabecera.ccabaj')
            ->where('cuentas_cobrar.id_empresa_negocio',$sucursal->id_empresa_negocio)
          
            ->where('fec_reg','=',$fecha)
            ->orderby('cliente','asc')
            ->get();

        
          $totalcobranzas = $cobranzas->sum('monto_efectivo');
           $totalcobranzasdepositos = $cobranzas->sum('monto_deposito');

          $pagos = DB::tABLE('cuentas_pagar_detalle')->select('fec_reg','fec_dep','cuentas_pagar_detalle.abono',DB::raw("(SELECT prov_raz FROM proveedor WHERE prov_id = compras_cabecera.prov_id) as proveedor"),DB::raw("(SELECT prov_ruc FROM proveedor WHERE prov_id = compras_cabecera.prov_id) as ruc"),'com_doc_ser','com_doc_num','compras_cabecera.prov_id','compras_cabecera.tdocod')
            ->join('cuentas_pagar','cuentas_pagar.cue_pag_id','cuentas_pagar_detalle.cue_pag_id')
            ->join('compras_cabecera','compras_cabecera.com_cab_id','cuentas_pagar.com_cab_id')
            ->where('est_compra','Registrado')
            ->where('cuentas_pagar.id_empresa_negocio',$sucursal->id_empresa_negocio)
            ->where('fec_reg','=',$fecha)
            ->orderby('proveedor','asc')
            ->get();

      $totalpagos = $pagos->sum('abono');
   

        $totalgasto = DB::tABLE('gastos_cabecera')
        ->leftjoin('moneda','gastos_cabecera.mon_id','moneda.moncod')
        ->leftjoin('tipo_documento','gastos_cabecera.tdocod','tipo_documento.tdocod')
        ->join('usuario_gastos','usuario_gastos.gast_cab_id','gastos_cabecera.gast_cab_id')
        ->where('tipo_movimiento','GASTO')
        ->where('est_gasto','Registrado')
        ->where('gast_fec',$fecha)
        ->orderby('gastos_cabecera.gast_Cab_id','desc')
        ->sum('total_gast');

         $totalingreso = DB::tABLE('gastos_cabecera')
        ->leftjoin('moneda','gastos_cabecera.mon_id','moneda.moncod')
        ->leftjoin('tipo_documento','gastos_cabecera.tdocod','tipo_documento.tdocod')
        ->join('usuario_gastos','usuario_gastos.gast_cab_id','gastos_cabecera.gast_cab_id')
        ->where('tipo_movimiento','INGRESO')
        ->where('est_gasto','Registrado')
        ->where('gast_fec',$fecha)
        ->orderby('gastos_cabecera.gast_Cab_id','desc')
        ->sum('total_gast');


        $grup_gas = DB::tABLE('gastos_cabecera')->select('tip_gas_nom',DB::RAW('sum(gastos_detalle.total) as total'))
         ->leftjoin('gastos_detalle','gastos_cabecera.gast_cab_id','gastos_detalle.gast_cab_id')
         ->join('usuario_gastos','usuario_gastos.gast_cab_id','gastos_cabecera.gast_cab_id')
        ->leftjoin('tipo_gastos','tipo_gastos.tip_gas_id','gastos_detalle.tip_gas_id')
        ->where('gastos_cabecera.id_empresa_negocio',Auth::user()->id_empresa_negocio)
        ->where('tipo_movimiento','GASTO')
        ->where('est_gasto','Registrado')
        ->where('gast_fec',$fecha)
        ->orderby('gastos_cabecera.gast_Cab_id','desc')
        ->groupby('tipo_gastos.tip_gas_id')
        ->get();

    
        $grup_ing = DB::tABLE('gastos_cabecera')->select('tip_gas_nom',DB::RAW('sum(gastos_detalle.total) as total'))
         ->leftjoin('gastos_detalle','gastos_cabecera.gast_cab_id','gastos_detalle.gast_cab_id')
         ->join('usuario_gastos','usuario_gastos.gast_cab_id','gastos_cabecera.gast_cab_id')
        ->leftjoin('tipo_gastos','tipo_gastos.tip_gas_id','gastos_detalle.tip_gas_id')
        ->where('gastos_cabecera.id_empresa_negocio',Auth::user()->id_empresa_negocio)
        ->where('tipo_movimiento','GASTO')
        ->where('est_gasto','Registrado')
        ->where('gast_fec',$fecha)
        ->orderby('gastos_cabecera.gast_Cab_id','desc')
        ->groupby('tipo_gastos.tip_gas_id')
        ->get();

      
        $compras = DB::tABLE('compras_cabecera')
        ->leftjoin('proveedor','compras_cabecera.prov_id','proveedor.prov_id')
        ->leftjoin('moneda','compras_cabecera.mon_id','moneda.moncod')
        ->leftjoin('tipo_documento','compras_cabecera.tdocod','tipo_documento.tdocod')
        ->where('compras_cabecera.id_empresa_negocio',Auth::user()->id_empresa_negocio)
         ->where('com_fec',$fecha)
        ->where('est_compra','Registrado')
        ->where('compras_cabecera.tdocod','!=','80')
        ->sum('total_com');


        $gastos = DB::tABLE('gastos_cabecera')
        ->join('gastos_detalle','gastos_cabecera.gast_cab_id','gastos_detalle.gast_cab_id')
        ->leftjoin('moneda','gastos_cabecera.mon_id','moneda.moncod')
        ->leftjoin('tipo_gastos','tipo_gastos.tip_gas_id','gastos_detalle.tip_gas_id')
        ->leftjoin('tipo_documento','gastos_cabecera.tdocod','tipo_documento.tdocod')
       
        ->where('gastos_cabecera.id_empresa_negocio',Auth::user()->id_empresa_negocio)
        ->where('tipo_movimiento','GASTO')
        ->where('est_gasto','Registrado')
        ->where('gast_fec',$fecha)
        ->orderby('gastos_cabecera.gast_cab_id','desc')
        ->get();

          $ingresos = DB::tABLE('gastos_cabecera')
        ->leftjoin('gastos_detalle','gastos_cabecera.gast_cab_id','gastos_detalle.gast_cab_id')
        ->leftjoin('moneda','gastos_cabecera.mon_id','moneda.moncod')
        ->leftjoin('tipo_gastos','tipo_gastos.tip_gas_id','gastos_detalle.tip_gas_id')
        ->leftjoin('tipo_documento','gastos_cabecera.tdocod','tipo_documento.tdocod')
        ->where('gastos_cabecera.id_empresa_negocio',Auth::user()->id_empresa_negocio)
        ->where('tipo_movimiento','INGRESO')
        ->where('est_gasto','Registrado')
        ->where('gast_fec',$fecha)
        ->orderby('gastos_cabecera.gast_cab_id','desc')
        ->get();


        $totalgast = $gastos->sum('total');
        $totalingreso = $ingresos->sum('total');

        $saldo = 0;

        $fecha1 = Carbon::parse($fecha)->format('d-m-Y');

        $bus_saldo = DB::tABLE('saldos_arqueo')
        ->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
        ->where('fecha','<',$fecha)
        ->orderby('fecha','desc')
        ->first();

        if(empty($bus_saldo)){
          $saldo = 0;
        }else{
          $saldo = $bus_saldo->saldo;
        }

        $saldo_nuevo = ($saldo+$ventascontado+$totalingreso+$totalcobranzas)-($totalgast+$totalpagos);


        $bus_saldo_act = DB::tABLE('saldos_arqueo')
        ->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
        ->where('fecha',$fecha)
        ->first();

        if(empty($bus_saldo_act)){
          DB::tABLE('saldos_arqueo')
          ->insert(['fecha'=>$fecha,'saldo'=>$saldo_nuevo,'id_empresa_negocio'=>Auth::user()->id_empresa_negocio]);
        }else{
          DB::tABLE('saldos_arqueo')
          ->where('sal_arq_id',$bus_saldo_act->sal_arq_id)
          ->update(['saldo'=>$saldo_nuevo]);
        }
       


        $rutapdf = public_path().'/pdfreportes/';

        $nompdffile = 'Arqueo_Resumen_Diario_'.$fecha.'.pdf';

       if(file_exists($rutapdf.$nompdffile)){

            unlink($rutapdf.$nompdffile);
        }

        $view = \View::make('formatos_reportes.reporte_pdf_arqueo_diario', compact('totalcobranzasdepositos','compras','totalgasto','totalingreso','registroventascontado','registroventascredito','ventascredito','ventascontado','cobranzas','totalcobranzas','usuario','sucursal','empresa','grup_gas','totalgasto','grup_ing','gastos','totalgast','totalingreso','ingresos','fecha','saldo_nuevo','saldo','pagos','totalpagos'));

                  
          $pdf = \App::make('dompdf.wrapper');
          $contenido = $view->render();
          $pdf->loadHTML($view)->setPaper('A4', 'lanscape')->save($rutapdf.$nompdffile);


      
        


       if (file_exists($rutapdf.$nompdffile))
      {
        $headers = array(
              'Content-Type: application/pdf',
            );

        return response()->download($rutapdf.$nompdffile);
      }

      



   

    }





    public function generarreporte(Request $request, $tipo){

      $fecin = now()->modify('first day of this month')->format('Y-m-d');
      $fecfin = now()->modify('last day of this month')->format('Y-m-d');

      $productos = DB::tABLE('productos')->get();
      $clientes = DB::tABLE('cliente')->where('rucemp',Auth::user()->IdEmpresa)->get();
      $proveedores = DB::tABLE('proveedor')->get();

      $prod = $request->get('IdProducto');

      $negocios = DB::tABLE('empresa_negocios')->where('IdEmpresa',Auth::user()->IdEmpresa)->get();
             
      $almacenes = DB::tABLE('almacenes')
      ->where('id_empresa_negocio',$negocios->first()->id_empresa_negocio)->get();
            
      $sucursal = $negocios->first()->id_empresa_negocio;  

       $almacen = $almacenes->first()->id_almacen;  



      $vendedores = DB::tABLE('users')
      ->leftjoin('role_user','role_user.user_IdUsuario','users.IdUsuario')
     // ->where('role_id','5')
       ->where('IdEmpresa',Auth::user()->IdEmpresa)
      ->get();

        $productoslista = DB::tABLE('productos')->where('tipo','=','1')->where('promocion','!=','2')
      ->get();

      return view('empresas.reportes.reportes',compact('prod','almacen','sucursal','negocios','almacenes','productos','vendedores','clientes','fecin','fecfin','tipo','proveedores','productoslista'));

            

    }



    public function buscarreportepdf(Request $request){

      $fecin = $request->get('fecin');
      $fecfin = $request->get('fecfin');
      $opcion = $request->get('opcion');
      $vendedor = $request->get('vendedor');
      $cliente = $request->get('cliente');
      $producto = $request->get('producto');
      $pro = $request->get('IdProducto');
      $almacen = $request->get('almacen');
      $sucursal = $request->get('sucursal');
      $proveedor = $request->get('proveedor');
       $dato_vendedor ="";

     
      //$productos = DB::tABLE('productos')->where('tipo','1')->get();
      $negocios = DB::tABLE('empresa_negocios')->get();

      if(!empty($sucursal)){
        $almacenes = DB::tABLE('almacenes')->where('id_empresa_negocio',$sucursal)->get();
      }else{
        $almacenes = DB::tABLE('almacenes')->where('id_empresa_negocio',$negocios->first()->id_empresa_negocio)->get();
      }

      $dat_ven = DB::tABLE('users')->where('IdUsuario',$request->get('vendedor'))->first();

      $data_sucursal = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',$sucursal)->first();

      $dat_cli = DB::tABLE('cliente')->where('clicod',$request->get('cliente'))->first();

      $empresa = DB::tABLE('empresa')->where('IdEmpresa',$data_sucursal->IdEmpresa)->first();

      $hora_rep = now()->format('Y-m-d H:i:s');
      $dato_vendedor ="";
      $dato_cliente ="";

      switch ($opcion){
            case 1:

                if($vendedor!='Todos'){
                $dato_vendedor = DB::tABLE('users')->where('IdUsuario',$vendedor)->first();
                }

                if($cliente!='Todos'){
                  $dato_cliente = DB::tABLE('cliente')->where('clicod',$cliente)->first();
                }

                $comprobantes = cpe_cabecera::select('cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','det.cdedes as producto','det.cdecan as cantidad','cdepuni as precio','monnom as moneda','det.cdevve as total','cpe_cabecera.ccabaj as baja','det.costo','ccasunrescod','tdides as documentoidentidad','ccandi as numerodocumento','procod','cdedes','cdecan','cdepuni','cdevve','umecod','ccaitv','id_almacen_pro')
                ->leftjoin('cpe_detalle as det','cpe_cabecera.IdCpe_cabecera','det.IdCpe_cabecera')
                ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                 ->where(function ($query) use ($vendedor) {
                      if($vendedor !='Todos'){
                         $query->Where('cpe_cabecera.IdUsuario_ven',$vendedor);
                      }
                    })
                ->where(function ($query) {
                  $query->where('cpe_cabecera.tdocod','01')
                    ->orWhere('cpe_cabecera.tdocod','03')
                    ->orWhere('cpe_cabecera.tdocod','13')
                    ->orWhere('cpe_cabecera.tdocod','14')
                    //->orWhere('cpe_cabecera.tdocod','07')
                    ;
                  }) 
                  ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                ->where(function ($query2) use ($cliente) {
                      if($cliente !='Todos'){
                         $query2->Where('cpe_cabecera.clicod',$cliente);
                      }
                  })
                 ->orderBy('IdCpe_cabecera','desc')->get();




                 $cabecera = DB::tABLE('cpe_cabecera')
             //    ->join('cpe_detalle','cpe_detalle.IdCpe_cabecera','cpe_cabecera.IdCpe_cabecera')
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                 ->where(function ($query) use ($vendedor){
                      if($vendedor !='Todos'){
                         $query->Where('cpe_cabecera.IdUsuario_ven',$vendedor);
                      }
                    })
                ->where(function ($query) {
                  $query->where('cpe_cabecera.tdocod','01')
                    ->orWhere('cpe_cabecera.tdocod','03')
                    ->orWhere('cpe_cabecera.tdocod','13')
                    ->orWhere('cpe_cabecera.tdocod','14')
                   // ->orWhere('cpe_cabecera.tdocod','07')
                    ;
                  }) 
                ->whereNull('ccabaj')
                ->where('ccanot','')
                ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                 ->where(function ($query2) use ($cliente) {
                      if($cliente !='Todos'){
                         $query2->Where('cpe_cabecera.clicod',$cliente);
                      }
                    })
                 ->get();

                 $total = $cabecera->where('ccanot','')->sum('ccaitv');

                 $totalnotas = $cabecera->where('tdocod','7')->sum('ccaitv');

                 $totalefectivo = $cabecera->where('ccanot','')->sum('totalcontado');
                 $totalcredito = $cabecera->where('ccanot','')->sum('totalcredito');
             
                 $nompdffile='REPORTE_VENTAS_'.$data_sucursal->IdEmpresa.'_'.now()->format('Y-m-d').'.pdf'; 


                 $rutapdf = public_path().'/reporte_cuentas_cobrar/';

                 if(file_exists($rutapdf.$nompdffile)){
                  unlink($rutapdf.$nompdffile);
                 }

                $view = \View::make('formatos_reportes.reporte_pdf_ventas', compact('totalnotas','total','dato_vendedor','cabecera','comprobantes','fecin','fecfin','empresa','sucursal','almacen','data_sucursal','dato_cliente','totalefectivo','totalcredito'));

                            
                $pdf = \App::make('dompdf.wrapper');
                $contenido = $view->render();
                $pdf->loadHTML($view)->setPaper('A4', 'lanscape')->save($rutapdf.$nompdffile);


                $headers = array(
                        'Content-Type: application/pdf',
                      );

                return response()->download($rutapdf.$nompdffile);
  
          

            break;

            case 2:


                if($vendedor!='Todos'){
                  $dato_vendedor = DB::tABLE('users')->where('IdUsuario',$vendedor)->first();
                }

                if($cliente!='Todos'){
                  $dato_cliente = DB::tABLE('cliente')->where('clicod',$cliente)->first();
                }

                $comprobantes = cpe_cabecera::select('cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','det.cdedes as producto','det.cdecan as cantidad','cdepuni as precio','monnom as moneda','det.cdevve as total','cpe_cabecera.ccabaj as baja','det.costo','ccasunrescod','tdides as documentoidentidad','ccandi as numerodocumento','procod','cdedes','cdecan','cdepuni','cdevve','umecod','ccaitv','id_almacen_pro')
                ->leftjoin('cpe_detalle as det','cpe_cabecera.IdCpe_cabecera','det.IdCpe_cabecera')
                ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                 ->where(function ($query) use ($vendedor) {
                      if($vendedor !='Todos'){
                         $query->Where('cpe_cabecera.IdUsuario_ven',$vendedor);
                      }
                    })
                ->where(function ($query) {
                  $query->where('cpe_cabecera.tdocod','01')
                    ->orWhere('cpe_cabecera.tdocod','03')
                    ->orWhere('cpe_cabecera.tdocod','13')
                    ->orWhere('cpe_cabecera.tdocod','14')
                    ->orWhere('cpe_cabecera.tdocod','07')
                    ;
                  }) 
                    ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                       ->where(function ($query2) use ($cliente) {
                      if($cliente !='Todos'){
                         $query2->Where('cpe_cabecera.clicod',$cliente);
                      }
                    })
                 ->orderBy('IdCpe_cabecera','desc')->get();




                 $cabecera = DB::tABLE('cpe_cabecera')
             //    ->join('cpe_detalle','cpe_detalle.IdCpe_cabecera','cpe_cabecera.IdCpe_cabecera')
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                 ->where(function ($query) use ($vendedor) {
                      if($vendedor !='Todos'){
                         $query->Where('cpe_cabecera.IdUsuario_ven',$vendedor);
                      }
                    })
                ->where(function ($query) {
                  $query->where('cpe_cabecera.tdocod','01')
                    ->orWhere('cpe_cabecera.tdocod','03')
                    ->orWhere('cpe_cabecera.tdocod','13')
                    ->orWhere('cpe_cabecera.tdocod','14')
                    ->orWhere('cpe_cabecera.tdocod','07')
                    ;
                  }) 
                    ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                })
                   ->where(function ($query2) use ($cliente) {
                      if($cliente !='Todos'){
                         $query2->Where('cpe_cabecera.clicod',$cliente);
                      }
                    })
                 ->get();

                 $total = $cabecera->where('ccanot','')->sum('ccaitv');

                 $totalnotas = $cabecera->where('tdocod','7')->sum('ccaitv');

                 /*---------------------------------------REPORTE VENTAS POR PRODUCTO RESUMEN------------------------------------------------------*/


                $productos = cpe_cabecera::select(DB::RAW('sum(cpe_detalle.cdecan*cpe_det_factor) as cantidad'),'cdedes',DB::RAW('sum(cpe_detalle.cdevve) as precio'),'cpe_detalle.procod','umecod')
                ->leftjoin('cpe_detalle','cpe_detalle.IdCpe_cabecera','cpe_cabecera.IdCpe_cabecera')
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                ->whereNull('cpe_cabecera.ccabaj')
                ->where('cdecan','>','0')
                ->where(function ($query) use ($vendedor) {
                      if($vendedor !='Todos'){
                         $query->Where('cpe_cabecera.IdUsuario_ven',$vendedor);
                      }
                })
                ->where(function ($query) {
                  $query->where('cpe_cabecera.tdocod','01')
                    ->orWhere('cpe_cabecera.tdocod','03')
                    ->orWhere('cpe_cabecera.tdocod','13')
                    ->orWhere('cpe_cabecera.tdocod','14')
                    ->orWhere('cpe_cabecera.tdocod','07')
                    ;
                  })   
                ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                })
                ->where(function ($query2) use ($cliente) {
                    if($cliente !='Todos'){
                         $query2->Where('cpe_cabecera.clicod',$cliente);
                    }
                }) 
                ->orderby('cdedes','desc')
                ->groupby('procod','cdedes')
                ->get();


                     $productosnot = cpe_cabecera::select(DB::RAW('sum(cpe_detalle.cdecan*cpe_det_factor) as cantidad'),'cdedes',DB::RAW('sum(cpe_detalle.cdevve) as precio'),'cpe_detalle.procod','umecod')
                ->leftjoin('cpe_detalle','cpe_detalle.IdCpe_cabecera','cpe_cabecera.IdCpe_cabecera')
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                ->whereNull('cpe_cabecera.ccabaj')
                ->where('cdecan','>','0')
                ->where(function ($query) use ($vendedor) {
                      if($vendedor !='Todos'){
                         $query->Where('cpe_cabecera.IdUsuario_ven',$vendedor);
                      }
                })
                ->where(function ($query) {
                  $query->Where('cpe_cabecera.tdocod','07')
                    ;
                  })   
                ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                })
                ->where(function ($query2) use ($cliente) {
                    if($cliente !='Todos'){
                         $query2->Where('cpe_cabecera.clicod',$cliente);
                      }
                }) 
                ->orderby('cdedes','desc')
                ->groupby('procod')
                ->get();

                $productosnotas = $productosnot->sum('cantidad');

                $totalproductos = $productos->sum('cantidad');

                $totalmontoproductos =0;
               
                foreach ($productos as $pro) {
                   $totalmontoproductos = $totalmontoproductos + ($pro->precio);
                }


                 /*---------------------------------------------------------------------------------------------------------------------------------*/

                 $nompdffile='REPORTE_VENTAS_DETALLADAS_'.$data_sucursal->IdEmpresa.'_'.now()->format('Y-m-d').'.pdf'; 


                 $rutapdf = public_path().'/reporte_cuentas_cobrar/';

                 if(file_exists($rutapdf.$nompdffile)){
                  unlink($rutapdf.$nompdffile);
                 }

                $view = \View::make('formatos_reportes.reporte_pdf_ventas_detalladas', compact('totalmontoproductos','totalnotas','total','cabecera','comprobantes','fecin','fecfin','empresa','sucursal','almacen','productos','productosnotas','data_sucursal','dato_vendedor','dato_cliente'));

                            
                $pdf = \App::make('dompdf.wrapper');
                $contenido = $view->render();
                $pdf->loadHTML($view)->setPaper('A4', 'lanscape')->save($rutapdf.$nompdffile);


                $headers = array(
                        'Content-Type: application/pdf',
                      );

                return response()->download($rutapdf.$nompdffile);
  

            break;

                  case 3:

                
                 $comprobantes = cpe_cabecera::select('ccacodsun','cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','tdides as documentoidentidad','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','monnom as moneda','ccaitv as total','cpe_cabecera.ccabaj as baja','ccaigv as igv','ccatvg as gravado','ccandi as numerodocumento','ccasunrescod','cpe_cabecera.tdocod','cpe_cabecera.ccatexo','cpe_cabecera.icbper')
                    ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                    ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                    ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                    ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                    ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
                    ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                
                ->where(function ($query6) use ($almacen) {
                  if($almacen !=0 && !empty($almacen)){
                      $query6->where('cpe_cabecera.id_almacen',$almacen);
                  }                
                }) 
                    ->where('cpe_cabecera.ccafem','>=',$fecin)
                    ->where('cpe_cabecera.ccafem','<=',$fecfin)
                    ->where(function ($query) {
                    $query->where('cpe_cabecera.tdocod','01')
                      ->orWhere('cpe_cabecera.tdocod','03')
                      ->orWhere('cpe_cabecera.tdocod','07');
                    })    
                   
                     ->orderBy('IdCpe_cabecera','desc')->get();

             

              $gravadosfacturas = $comprobantes->where('tdocod','01')->sum('gravado');
              $gravadosboletas = $comprobantes->where('tdocod','03')->sum('gravado');
              $gravadosnotas = $comprobantes->where('tdocod','07')->sum('gravado');

              $exoneradosfacturas = $comprobantes->where('tdocod','01')->sum('ccatexo');
              $exoneradosboletas = $comprobantes->where('tdocod','03')->sum('ccatexo');
              $exoneradosnotas = $comprobantes->where('tdocod','07')->sum('ccatexo');

              $totalgravados = $comprobantes->sum('gravado');
              $totalexonerados = $comprobantes->sum('ccatexo');
              $totaligv = $comprobantes->where('tdocod','!=','07')->sum('ccaigv');
              $totaligvnotas = $comprobantes->where('tdocod','07')->sum('ccaigv');


              $totalboletas = $comprobantes->where('tdocod','03')->sum('total');
              $totalfacturas = $comprobantes->where('tdocod','01')->sum('total');
               $totalnotas = $comprobantes->where('tdocod','07')->sum('total');
              // FIN NOTAS DE CREDITOS

              $hora_rep = now()->format('Y-m-d H:i:s');

              $boletas =  cpe_cabecera::select('ccacodsun','cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','tdides as documentoidentidad','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','monnom as moneda','ccaitv as total','cpe_cabecera.ccabaj as baja','ccaigv as igv','ccatvg as gravado','ccandi as numerodocumento','ccasunrescod','cpe_cabecera.tdocod','cpe_cabecera.ccatexo','cpe_cabecera.icbper')
                    ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                    ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                    ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                    ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                    ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
                    ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                
                ->where(function ($query6) use ($almacen) {
                  if($almacen !=0 && !empty($almacen)){
                      $query6->where('cpe_cabecera.id_almacen',$almacen);
                  }                
                }) 
                    ->where('cpe_cabecera.ccafem','>=',$fecin)
                    ->where('cpe_cabecera.ccafem','<=',$fecfin)
                    ->where(function ($query) {
                    $query->where('cpe_cabecera.tdocod','03');
                    })    
                   
                     ->orderBy('IdCpe_cabecera','desc')->get();

              $facturas =  cpe_cabecera::select('ccacodsun','cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','tdides as documentoidentidad','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','monnom as moneda','ccaitv as total','cpe_cabecera.ccabaj as baja','ccaigv as igv','ccatvg as gravado','ccandi as numerodocumento','ccasunrescod','cpe_cabecera.tdocod','cpe_cabecera.ccatexo','cpe_cabecera.icbper')
                    ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                    ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                    ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                    ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                    ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
                    ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                
                ->where(function ($query6) use ($almacen) {
                  if($almacen !=0 && !empty($almacen)){
                      $query6->where('cpe_cabecera.id_almacen',$almacen);
                  }                
                }) 
                    ->where('cpe_cabecera.ccafem','>=',$fecin)
                    ->where('cpe_cabecera.ccafem','<=',$fecfin)
                    ->where(function ($query) {
                    $query->where('cpe_cabecera.tdocod','01');
                    })    
                   
                     ->orderBy('IdCpe_cabecera','desc')->get();

              $notas =  cpe_cabecera::select('ccacodsun','cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','tdides as documentoidentidad','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','monnom as moneda','ccaitv as total','cpe_cabecera.ccabaj as baja','ccaigv as igv','ccatvg as gravado','ccandi as numerodocumento','ccasunrescod','cpe_cabecera.tdocod','cpe_cabecera.ccatexo','cpe_cabecera.icbper')
                    ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                    ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                    ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                    ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                    ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
                    ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                
                ->where(function ($query6) use ($almacen) {
                  if($almacen !=0 && !empty($almacen)){
                      $query6->where('cpe_cabecera.id_almacen',$almacen);
                  }                
                }) 
                    ->where('cpe_cabecera.ccafem','>=',$fecin)
                    ->where('cpe_cabecera.ccafem','<=',$fecfin)
                    ->where(function ($query) {
                    $query->where('cpe_cabecera.tdocod','07');
                    })    
                   
                     ->orderBy('IdCpe_cabecera','desc')->get();

             
                    $nompdffile='VENTAS_'.$fecin.'_'.$fecfin.'.pdf'; 


                 $rutapdf = public_path().'/reporte_cuentas_cobrar/';

                 if(file_exists($rutapdf.$nompdffile)){
                  unlink($rutapdf.$nompdffile);
                 }

                 $sucursal = EmpresaNegocios::FindOrFail($request->get('sucursal'));

                 $empresa = Empresa::findOrFail($sucursal->IdEmpresa);

                $view = \View::make('formatos_reportes.reporte_pdf_ventas', compact('hora_rep','fecin','fecfin','comprobantes','gravadosfacturas','gravadosboletas','gravadosnotas','exoneradosnotas','exoneradosboletas','exoneradosfacturas','totalgravados','totalexonerados','totaligv','totalboletas','totalfacturas','totalnotas','notas','boletas','facturas','totaligvnotas','empresa','sucursal'));

                            
                $pdf = \App::make('dompdf.wrapper');
                $contenido = $view->render();
                $pdf->loadHTML($view)->setPaper('A4', 'lanscape')->save($rutapdf.$nompdffile);

               
                 $headers = array(
                    'Content-Type: application/pdf',
                  );

     


           
                 return response()->download($rutapdf.$nompdffile);

              


            break;


            case 60:

                   $comprobantes = cpe_cabecera::select('cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','cdepuni as precio','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','det.cdecan as cantidad','det.cdedes as producto','monnom as moneda','det.cdevve as total','productos.costofijo as costo','cpe_cabecera.ccabaj as baja','ccasunrescod','det.flete')
                ->leftjoin('cpe_detalle as det','cpe_cabecera.IdCpe_cabecera','det.IdCpe_cabecera')
                ->leftjoin('productos','productos.IdProducto','det.IdProducto')
                ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
                ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                
                ->where(function ($query6) use ($almacen) {
                  if($almacen !='Todos'){
                      $query6->where('cpe_cabecera.id_almacen',$almacen);
                  }                
                }) 
                ->where(function ($query1){
          
                    $query1->whereNull('cpe_cabecera.ccabaj')
                          ->orwhere('cpe_cabecera.ccabaj','');
                    
                })
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin) 
                  ->where(function ($query) {
                $query->where('cpe_cabecera.tdocod','01')
                    ->orWhere('cpe_cabecera.tdocod','03')
                    ->orWhere('cpe_cabecera.tdocod','13');
                })                
                ->orderBy('IdCpe_cabecera','desc')->get();


                $totalventas = cpe_cabecera::select('cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','cdepuni as precio','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','det.cdecan as cantidad','det.cdedes as producto','monnom as moneda','det.cdevve as total','productos.costofijo as costo','cpe_cabecera.ccabaj as baja','ccasunrescod')
                ->leftjoin('cpe_detalle as det','cpe_cabecera.IdCpe_cabecera','det.IdCpe_cabecera')
                 ->leftjoin('productos','productos.IdProducto','det.IdProducto')
                ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
               ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                
                ->where(function ($query6) use ($almacen) {
                  if($almacen !='Todos'){
                      $query6->where('cpe_cabecera.id_almacen',$almacen);
                  }                
                }) 
                ->where(function ($query1){
          
                    $query1->whereNull('cpe_cabecera.ccabaj')
                          ->orwhere('cpe_cabecera.ccabaj','');
                    
                })
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin) 
                  ->where(function ($query) {
                $query->where('cpe_cabecera.tdocod','01')
                    ->orWhere('cpe_cabecera.tdocod','03')
                    ->orWhere('cpe_cabecera.tdocod','13');
                })                
                ->sum('cdevve');

                 $totalcostodet = cpe_cabecera::select('cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','cdepuni as precio','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','det.cdecan as cantidad','det.cdedes as producto','monnom as moneda','det.cdevve as total','productos.costo as costo','cpe_cabecera.ccabaj as baja','ccasunrescod','det.flete')
                ->leftjoin('cpe_detalle as det','cpe_cabecera.IdCpe_cabecera','det.IdCpe_cabecera')
                 ->leftjoin('productos','productos.IdProducto','det.IdProducto')
                ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
               ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                
                ->where(function ($query6) use ($almacen) {
                  if($almacen !='Todos'){
                      $query6->where('cpe_cabecera.id_almacen',$almacen);
                  }                
                }) 
                ->where(function ($query1){
          
                    $query1->whereNull('cpe_cabecera.ccabaj')
                          ->orwhere('cpe_cabecera.ccabaj','');
                    
                })
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin) 
                  ->where(function ($query) {
                $query->where('cpe_cabecera.tdocod','01')
                    ->orWhere('cpe_cabecera.tdocod','03')
                    ->orWhere('cpe_cabecera.tdocod','13');
                })                
                ->get();

                $totalcosto =0;
                
                foreach($totalcostodet as $tcosto){
                  $totalcosto = $totalcosto + $tcosto->costo;
                }

                 $nompdffile='RENTABILIDAD_'.$fecin.'_'.$fecfin.'.pdf'; 


                 $rutapdf = public_path().'/reporte_cuentas_cobrar/';

                 if(file_exists($rutapdf.$nompdffile)){
                  unlink($rutapdf.$nompdffile);
                 }

                

                 $empresa = Empresa::findOrFail($data_sucursal->IdEmpresa);

                $view = \View::make('formatos_reportes.reporte_pdf_rentabilidad', compact('hora_rep','totalventas','totalcosto','comprobantes','fecin','fecfin','empresa','sucursal','data_sucursal'));

                            
                $pdf = \App::make('dompdf.wrapper');
                $contenido = $view->render();
                $pdf->loadHTML($view)->setPaper('A4', 'lanscape')->save($rutapdf.$nompdffile);

               
                 $headers = array(
                    'Content-Type: application/pdf',
                  );

           
                 return response()->download($rutapdf.$nompdffile);


            break;

            case 61:

                if($vendedor!='Todos'){
                $dato_vendedor = DB::tABLE('users')->where('IdUsuario',$vendedor)->first();
                }

                if($cliente!='Todos'){
                  $dato_cliente = DB::tABLE('cliente')->where('clicod',$cliente)->first();
                }

                $comprobantes = cpe_cabecera::select('cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','monnom as moneda','cpe_cabecera.ccabaj as baja','ccasunrescod','tdides as documentoidentidad','ccandi as numerodocumento','users.name','users.apeusu',DB::RAW('sum(ccaitv) as total'))
                ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
                ->leftjoin('users','users.IdUsuario','cpe_cabecera.IdUsuario_ven')
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                 ->where(function ($query) use ($vendedor) {
                      if($vendedor !='Todos'){
                         $query->Where('cpe_cabecera.IdUsuario_ven',$vendedor);
                      }
                    })
                ->where(function ($query) {
                  $query->where('cpe_cabecera.tdocod','01')
                    ->orWhere('cpe_cabecera.tdocod','03')
                    ->orWhere('cpe_cabecera.tdocod','13')
                    ->orWhere('cpe_cabecera.tdocod','14')
                    //->orWhere('cpe_cabecera.tdocod','07')
                    ;
                  }) 
                  ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                   ->where(function ($query2) use ($cliente) {
                      if($cliente !='Todos'){
                         $query2->Where('cpe_cabecera.clicod',$cliente);
                      }
                    })
                
               /* ->where(function ($query6) use ($idalmacen) {
                  if($idalmacen !='Todos' && !empty($idalmacen)){
                      $query6->where('det.id_almacen_pro',$idalmacen);
                  }                
                })   */ 
                 ->groupby('IdUsuario_ven')
                 ->orderby('name')->get();



                 $total = $comprobantes->sum('total');

                  $nompdffile='resumen_ventas_vendedor'.$fecin.'_'.$fecfin.'.pdf'; 


                 $rutapdf = public_path().'/reporte_cuentas_cobrar/';

                 if(file_exists($rutapdf.$nompdffile)){
                  unlink($rutapdf.$nompdffile);
                 }

                

                 $empresa = Empresa::findOrFail($data_sucursal->IdEmpresa);

                $view = \View::make('formatos_reportes.reporte_pdf_resumen_ventas_vendedor', compact('hora_rep','total','comprobantes','fecin','fecfin','empresa','sucursal','data_sucursal','dato_vendedor'));

                            
                $pdf = \App::make('dompdf.wrapper');
                $contenido = $view->render();
                $pdf->loadHTML($view)->setPaper('A4', 'lanscape')->save($rutapdf.$nompdffile);

               
                 $headers = array(
                    'Content-Type: application/pdf',
                  );

           
                 return response()->download($rutapdf.$nompdffile);




            break;



             case 4:

                   $comprobantes = cpe_cabecera::select('cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','cdepuni as precio','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','det.cdecan as cantidad','det.cdedes as producto','monnom as moneda','det.cdevve as total','det.costo as costo','cpe_cabecera.ccabaj as baja','ccasunrescod','det.flete')
                ->leftjoin('cpe_detalle as det','cpe_cabecera.IdCpe_cabecera','det.IdCpe_cabecera')
                ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
                ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                
                ->where(function ($query6) use ($almacen) {
                  if($almacen !='Todos'){
                      $query6->where('cpe_cabecera.id_almacen',$almacen);
                  }                
                }) 
                ->where(function ($query1){
          
                    $query1->whereNull('cpe_cabecera.ccabaj')
                          ->orwhere('cpe_cabecera.ccabaj','');
                    
                })
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin) 
                  ->where(function ($query) {
                $query->where('cpe_cabecera.tdocod','01')
                    ->orWhere('cpe_cabecera.tdocod','03')
                    ->orWhere('cpe_cabecera.tdocod','13');
                })                
                ->orderBy('IdCpe_cabecera','desc')->get();


                $totalventas = cpe_cabecera::select('cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','cdepuni as precio','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','det.cdecan as cantidad','det.cdedes as producto','monnom as moneda','det.cdevve as total','det.costo as costo','cpe_cabecera.ccabaj as baja','ccasunrescod')
                ->leftjoin('cpe_detalle as det','cpe_cabecera.IdCpe_cabecera','det.IdCpe_cabecera')
                ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
               ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                
                ->where(function ($query6) use ($almacen) {
                  if($almacen !='Todos'){
                      $query6->where('cpe_cabecera.id_almacen',$almacen);
                  }                
                }) 
                ->where(function ($query1){
          
                    $query1->whereNull('cpe_cabecera.ccabaj')
                          ->orwhere('cpe_cabecera.ccabaj','');
                    
                })
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin) 
                  ->where(function ($query) {
                $query->where('cpe_cabecera.tdocod','01')
                    ->orWhere('cpe_cabecera.tdocod','03')
                    ->orWhere('cpe_cabecera.tdocod','13');
                })                
                ->sum('cdevve');

                 $totalcostodet = cpe_cabecera::select('cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','cdepuni as precio','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','det.cdecan as cantidad','det.cdedes as producto','monnom as moneda','det.cdevve as total','det.costo as costo','cpe_cabecera.ccabaj as baja','ccasunrescod','det.flete')
                ->leftjoin('cpe_detalle as det','cpe_cabecera.IdCpe_cabecera','det.IdCpe_cabecera')
                ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
               ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                
                ->where(function ($query6) use ($almacen) {
                  if($almacen !='Todos'){
                      $query6->where('cpe_cabecera.id_almacen',$almacen);
                  }                
                }) 
                ->where(function ($query1){
          
                    $query1->whereNull('cpe_cabecera.ccabaj')
                          ->orwhere('cpe_cabecera.ccabaj','');
                    
                })
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin) 
                  ->where(function ($query) {
                $query->where('cpe_cabecera.tdocod','01')
                    ->orWhere('cpe_cabecera.tdocod','03')
                    ->orWhere('cpe_cabecera.tdocod','13');
                })                
                ->get();

                $totalcosto =0;
                
                foreach($totalcostodet as $tcosto){
                  $totalcosto = $totalcosto + $tcosto->costo;
                }

                 $nompdffile='RENTABILIDAD_'.$fecin.'_'.$fecfin.'.pdf'; 


                 $rutapdf = public_path().'/reporte_cuentas_cobrar/';

                 if(file_exists($rutapdf.$nompdffile)){
                  unlink($rutapdf.$nompdffile);
                 }

                

                 $empresa = Empresa::findOrFail($data_sucursal->IdEmpresa);

                $view = \View::make('formatos_reportes.reporte_pdf_rentabilidad', compact('hora_rep','totalventas','totalcosto','comprobantes','fecin','fecfin','empresa','sucursal','data_sucursal'));

                            
                $pdf = \App::make('dompdf.wrapper');
                $contenido = $view->render();
                $pdf->loadHTML($view)->setPaper('A4', 'lanscape')->save($rutapdf.$nompdffile);

               
                 $headers = array(
                    'Content-Type: application/pdf',
                  );

           
                 return response()->download($rutapdf.$nompdffile);


            break;

            case 5:



              /*  $registros = DB::tABLE('cpe_detalle')->get();
              foreach ($registros as $reg) {
                DB::tABLE('cpe_detalle')->where('IdCpe_detalle',$reg->IdCpe_detalle)->update(['procod'=>trim($reg->procod)]);
              }*/


              $empresa = DB::tABLE('empresa')->where('IdEmpresa',Auth::user()->IdEmpresa)->first();

             // $sucursal = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first();
             // $datocli = DB::tABLE('cliente')->where('clicod',$clicod)->first();

                $productos = cpe_cabecera::select(DB::RAW('sum(cpe_detalle.cdecan*cpe_det_factor) as cantidad'),'cdedes',DB::RAW('sum(cpe_detalle.cdevve) as precio'),'cpe_detalle.procod','umecod')
                ->leftjoin('cpe_detalle','cpe_detalle.IdCpe_cabecera','cpe_cabecera.IdCpe_cabecera')
 
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                ->whereNull('cpe_cabecera.ccabaj')
                ->where('cdecan','>','0')
                 ->where(function ($query) {
                  $query->Where('cpe_cabecera.tdocod','03')
                        ->orWhere('cpe_cabecera.tdocod','01')
                        ->orWhere('cpe_cabecera.tdocod','13');
                      
                  })
               
                ->where(function ($query5) use ($sucursal) {
                  if($sucursal !='Todos'){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                
                ->where(function ($query6) use ($almacen) {
                  if($almacen !='Todos'){
                      $query6->where('cpe_detalle.id_almacen_pro',$almacen);
                  }                
                }) 
                ->orderby('cdedes','desc')
                ->groupby('procod')
                ->get();

                $totalproductos = $productos->sum('cantidad');

                $total =0;
                foreach ($productos as $pro) {
                   $total = $total + ($pro->precio);
                }

                $nompdffile='resumen_ventas_productos_'.$data_sucursal->IdEmpresa.'_'.now()->format('Y-m-d').'.pdf'; 


                 $rutapdf = public_path().'/reporte_cuentas_cobrar/';

                 if(file_exists($rutapdf.$nompdffile)){
                  unlink($rutapdf.$nompdffile);
                 }

                $view = \View::make('formatos_reportes.reporte_pdf_resumen_ventas_productos', compact('productos','fecin','fecfin','total','totalproductos','total','empresa','sucursal','data_sucursal'));

                            
                $pdf = \App::make('dompdf.wrapper');
                $contenido = $view->render();
                $pdf->loadHTML($view)->setPaper('A4', 'lanscape')->save($rutapdf.$nompdffile);


                $headers = array(
                        'Content-Type: application/pdf',
                      );

                return response()->download($rutapdf.$nompdffile);


                 
              break;

               case 6:


                $comprobantes = cpe_cabecera::select('cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','det.cdedes as producto','det.cdecan as cantidad','cdepuni as precio','monnom as moneda','det.cdevve as total','cpe_cabecera.ccabaj as baja','det.costo','ccasunrescod','tdides as documentoidentidad','ccandi as numerodocumento','IdProducto','cpe_det_factor','des_doc')
                ->leftjoin('cpe_detalle as det','cpe_cabecera.IdCpe_cabecera','det.IdCpe_cabecera')
                ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
                 ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                
                ->where(function ($query6) use ($almacen) {
                  if($almacen !='Todos'){
                      $query6->where('det.id_almacen_pro',$almacen);
                  }                
                }) 
                
                
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
               ->whereNull('cpe_cabecera.ccabaj')
                ->where('cdecan','>','0')
                 ->where(function ($query) {
                  $query->Where('cpe_cabecera.tdocod','03')
                        ->orWhere('cpe_cabecera.tdocod','01')
                        ->orWhere('cpe_cabecera.tdocod','13');
                      
                  })
               
                ->where(function ($query4) use ($pro) {

                if($pro !=0){
                   $query4->where('det.IdProducto',$pro)
                    ->orWhere('det.IdProducto_rel',$pro);
                }
               

                })    
                 ->orderBy('IdCpe_cabecera','desc')->get();

                $total = $comprobantes->sum('total');
   
                $nompdffile='ventas_productos_'.$data_sucursal->IdEmpresa.'_'.now()->format('Y-m-d').'.pdf'; 

                 $rutapdf = public_path().'/reporte_cuentas_cobrar/';

                 if(file_exists($rutapdf.$nompdffile)){
                  unlink($rutapdf.$nompdffile);
                 }

                $empresa = DB::tABLE('empresa')->where('IdEmpresa',$data_sucursal->IdEmpresa)->first();

                $view = \View::make('formatos_reportes.reporte_pdf_ventas_productos', compact('empresa','fecin','fecfin','comprobantes','total','dat_ven','data_sucursal'));

                            
                $pdf = \App::make('dompdf.wrapper');
                $contenido = $view->render();
                $pdf->loadHTML($view)->setPaper('A4', 'lanscape')->save($rutapdf.$nompdffile);


                $headers = array(
                        'Content-Type: application/pdf',
                      );

                return response()->download($rutapdf.$nompdffile);


              


            break;

             case 7:

                $comprobantes = cpe_cabecera::select('ccaitv','cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','monnom as moneda','ccaitv as total','cpe_cabecera.ccabaj as baja','ccasunrescod','tdides as documentoidentidad','ccandi as numerodocumento','cpe_cabecera.tdocod')
                ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
                 ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                
                ->where(function ($query6) use ($almacen) {
                  if($almacen !=0 && !empty($almacen)){
                      $query6->where('cpe_cabecera.id_almacen',$almacen);
                  }                
                }) 
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                ->where(function ($query){
                    $query->Where('cpe_cabecera.tdocod','15');
                })
                ->where(function ($query1) use ($vendedor) {
                      if($vendedor !='Todos'){
                         $query1->Where('cpe_cabecera.IdUsuario_ven',$vendedor);
                      }
                    })
                 ->where(function ($query2) use ($cliente) {
                      if($cliente !='Todos'){
                         $query2->Where('cpe_cabecera.clicod',$cliente);
                      }
                    })
                ->orderBy('IdCpe_cabecera','desc')->get();

            //   dd($comprobantes);

                  $total = $comprobantes->sum('ccaitv');

                $vista = view('empresas.reportes.proformas',compact('dat_cli','fecin','fecfin','comprobantes','total','dat_ven'))->render();

              if($request->ajax()){
                 return response()->json(['vista'=>$vista]);

              }


            break;

             case 8:

                $comprobantes = cpe_cabecera::select('ccaitv','cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','monnom as moneda','ccaitv as total','cpe_cabecera.ccabaj as baja','ccasunrescod','tdides as documentoidentidad','ccandi as numerodocumento','cpe_cabecera.tdocod')
                ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
                 ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                
                ->where(function ($query6) use ($almacen) {
                  if($almacen !=0 && !empty($almacen)){
                      $query6->where('cpe_cabecera.id_almacen',$almacen);
                  }                
                }) 
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                ->where(function ($query){
                    $query->Where('cpe_cabecera.tdocod','16');
                })
                ->where(function ($query1) use ($vendedor) {
                      if($vendedor !='Todos'){
                         $query1->Where('cpe_cabecera.IdUsuario_ven',$vendedor);
                      }
                    })
                 ->where(function ($query2) use ($cliente) {
                      if($cliente !='Todos'){
                         $query2->Where('cpe_cabecera.clicod',$cliente);
                      }
                    })
                ->orderBy('IdCpe_cabecera','desc')->get();

            //   dd($comprobantes);

                  $total = $comprobantes->sum('ccaitv');

                $vista = view('empresas.reportes.pedidos',compact('dat_cli','fecin','fecfin','comprobantes','total','dat_ven'))->render();

              if($request->ajax()){
                 return response()->json(['vista'=>$vista]);

              }


            break;

                case 9:

                
              
            
                 $comprobantes = cpe_cabecera::select(DB::RAW('sum(det.cdevve) as TOTAL'),DB::RAW('sum(det.cdecan*factor) as CANTIDAD'),'productos.pronom',DB::RAW('sum(det.cdevve*(det.comision/100)) as COMISION'))
                ->leftjoin('cpe_detalle as det','cpe_cabecera.IdCpe_cabecera','det.IdCpe_cabecera')
                ->leftjoin('productos','productos.IdProducto','det.IdProducto_rel')
                ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
               ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                
                ->where(function ($query6) use ($almacen) {
                  if($almacen !=0 && !empty($almacen)){
                      $query6->where('cpe_cabecera.id_almacen',$almacen);
                  }                
                }) 
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                ->where(function ($query) use ($vendedor) {
                      if($vendedor !='Todos'){
                         $query->Where('cpe_cabecera.IdUsuario_ven',$vendedor);
                      }
                    })
                   ->where(function ($query2) use ($pro) {
                      if($pro !=0){
                         $query2->Where('IdProducto_rel',$pro);
                      }
                    })
                ->where(function ($query1){
          
                    $query1->whereNull('cpe_cabecera.ccabaj')
                          ->orwhere('cpe_cabecera.ccabaj','');
                    
                })
                ->where(function ($query3) {
                $query3->where('cpe_cabecera.tdocod','01')
                    ->orWhere('cpe_cabecera.tdocod','03')
                    ->orWhere('cpe_cabecera.tdocod','13');
                })   
                ->groupby('IdProducto_rel') 
                 ->orderBy('cpe_cabecera.IdCpe_cabecera','desc')->get();

                 //dd($comprobantes);

                  $total = $comprobantes->sum('COMISION');

                    $vista = view('empresas.reportes.comision_vendedor',compact('fecin','fecfin','comprobantes','total','dat_ven','dat_cli'))->render();


                    if($request->ajax()){
                 return response()->json(['vista'=>$vista]);

              }


            break;

                 case 10:

                $comprobantes = cpe_cabecera::select(DB::RAW('sum(det.cdevve) as TOTAL'),DB::RAW('sum(det.cdecan*factor) as CANTIDAD'),'productos.pronom',DB::RAW('sum(det.cdevve*(det.comision/100)) as COMISION'))
                ->leftjoin('cpe_detalle as det','cpe_cabecera.IdCpe_cabecera','det.IdCpe_cabecera')
                ->leftjoin('productos','productos.IdProducto','det.IdProducto_rel')
                ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
               ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                
                ->where(function ($query6) use ($almacen) {
                  if($almacen !=0 && !empty($almacen)){
                      $query6->where('cpe_cabecera.id_almacen',$almacen);
                  }                
                }) 
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                ->where(function ($query) use ($vendedor) {
                      if($vendedor !='Todos'){
                         $query->Where('cpe_cabecera.IdUsuario_ven',$vendedor);
                      }
                    })
                 ->where(function ($query2) use ($cliente) {
                      if($cliente !='Todos'){
                         $query2->Where('cpe_cabecera.clicod',$cliente);
                      }
                    })
                ->where(function ($query1){
          
                    $query1->whereNull('cpe_cabecera.ccabaj')
                          ->orwhere('cpe_cabecera.ccabaj','');
                    
                })
                ->where(function ($query3) {
                $query3->where('cpe_cabecera.tdocod','01')
                    ->orWhere('cpe_cabecera.tdocod','03')
                    ->orWhere('cpe_cabecera.tdocod','13');
                })   
                ->groupby('IdProducto_rel') 
                 ->orderBy('cpe_cabecera.IdCpe_cabecera','desc')->get();

                 //dd($comprobantes);

                  $total = $comprobantes->sum('COMISION');


                $vista = view('empresas.reportes.comision_vendedor',compact('fecin','fecfin','comprobantes','total','dat_ven','dat_cli'))->render();

                    if($request->ajax()){
                 return response()->json(['vista'=>$vista]);

              }


            break;


                case 11:

                 $comprobantes = DB::tABLE('compras_cabecera')
                  ->leftjoin('proveedor','compras_cabecera.prov_id','proveedor.prov_id')
                  ->join('moneda','compras_cabecera.mon_id','moneda.moncod')
                  ->join('tipo_documento','compras_cabecera.tdocod','tipo_documento.tdocod')
                  
                    ->where('compras_cabecera.com_fec','>=',$fecin)
                    ->where('compras_cabecera.com_fec','<=',$fecfin)
                  ->where('est_compra','Registrado')
                    ->where(function ($query) use ($sucursal){
                      if(!$sucursal !=0 && !empty($sucursal)){
                          $query->where('compras_cabecera.id_empresa_negocio',$sucursal);
                      }
                     
                  })
                  ->where(function ($query1) use ($proveedor){
                      if($proveedor !=0){
                          $query1->where('compras_cabecera.prov_id',$proveedor);
                      }
                      
                  })
                        
                  ->where(function ($query6) use ($almacen) {
                      if($almacen !=0 && !empty($almacen)){
                          $query6->where('compras_cabecera.id_almacen',$almacen);
                      }                
                    }) 
                
                  ->where('compras_cabecera.tdocod','!=','80')
                  ->orderby('com_cab_id','desc')
                  ->get();
              //   dd($comprobantes);

                  $total = $comprobantes->sum('total_com');


                $vista = view('empresas.reportes.compras',compact('fecin','fecfin','comprobantes','total','dat_ven','dat_cli'))->render();

                    if($request->ajax()){
                 return response()->json(['vista'=>$vista]);

              }


            break;

              case 12:

              $comprobantes = DB::tABLE('compras_cabecera')
              ->leftjoin('compras_detalle','compras_detalle.com_cab_id','compras_cabecera.com_cab_id')
                 ->leftjoin('productos','productos.IdProducto','compras_detalle.pro_id')
                  ->leftjoin('proveedor','compras_cabecera.prov_id','proveedor.prov_id')
                  ->leftjoin('unidad_medida','unidad_medida.umecod','compras_detalle.ume_cod')
                  ->join('moneda','compras_cabecera.mon_id','moneda.moncod')
                  ->join('tipo_documento','compras_cabecera.tdocod','tipo_documento.tdocod')
              ->where('est_compra','Registrado')
                ->where('compras_cabecera.com_fec','>=',$fecin)
                ->where('compras_cabecera.com_fec','<=',$fecfin)
                  ->where(function ($query) use ($sucursal){
                      if(!$sucursal !=0 && !empty($sucursal)){
                          $query->where('compras_cabecera.id_empresa_negocio',$sucursal);
                      }
                     
                  })
                  ->where(function ($query1) use ($proveedor){
                      if($proveedor !=0){
                          $query1->where('compras_cabecera.prov_id',$proveedor);
                      }
                      
                  })
                        
                  ->where(function ($query6) use ($almacen) {
                      if($almacen !=0 && !empty($almacen)){
                          $query6->where('compras_cabecera.id_almacen',$almacen);
                      }                
                    }) 
              ->where('compras_cabecera.tdocod','!=','80')
              ->orderby('compras_cabecera.com_cab_id','desc')
              ->get();
                 //dd($comprobantes);

                  $total = $comprobantes->sum('total_com');


                $vista = view('empresas.reportes.compras_detalladas',compact('fecin','fecfin','comprobantes','total','dat_ven','dat_cli'))->render();

                    if($request->ajax()){
                 return response()->json(['vista'=>$vista]);

              }


            break;

             

               case 14:

                $comprobantes = cpe_cabecera::select('ccaitv','cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','monnom as moneda','ccaitv as total','cpe_cabecera.ccabaj as baja','ccasunrescod','tdides as documentoidentidad','ccandi as numerodocumento','cpe_cabecera.tdocod','pedidos.fecha_hora','pedidos.fecha_salida','pedidos.placa',DB::raw("(SELECT CONCAT(name,apeusu) FROM users WHERE IdUsuario= pedidos.IdUsuario) as usuario_registra"),DB::raw("(SELECT CONCAT(name,apeusu) FROM users WHERE IdUsuario= pedidos.IdUsuarioCob) as usuario_cobra"))
                ->join('pedidos','pedidos.ped_id','cpe_cabecera.ped_id')
                ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
                 ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                
                ->where(function ($query6) use ($almacen) {
                  if($almacen !=0 && !empty($almacen)){
                      $query6->where('cpe_cabecera.id_almacen',$almacen);
                  }                
                }) 
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                ->where(function ($query){
                    $query->where('cpe_cabecera.tdocod','01')
                          ->orwhere('cpe_cabecera.tdocod','03')
                          ->orWhere('cpe_cabecera.tdocod','13');
                })
                ->where(function ($query1) use ($vendedor) {
                      if($vendedor !='Todos'){
                         $query1->Where('cpe_cabecera.IdUsuario_ven',$vendedor);
                      }
                    })
                 ->where(function ($query2) use ($cliente) {
                      if($cliente !='Todos'){
                         $query2->Where('cpe_cabecera.clicod',$cliente);
                      }
                    })
                ->orderBy('IdCpe_cabecera','desc')->get();

            //   dd($comprobantes);

                  $total = $comprobantes->sum('ccaitv');

                $vista = view('empresas.reportes.ventas_ingreso_vehiculos',compact('dat_cli','fecin','fecfin','comprobantes','total','dat_ven'))->render();

              if($request->ajax()){
                 return response()->json(['vista'=>$vista]);

              }


            break;

              case 15:

                $comprobantes = cpe_cabecera::select('ccaitv','cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','monnom as moneda','ccaitv as total','cpe_cabecera.ccabaj as baja','ccasunrescod','tdides as documentoidentidad','ccandi as numerodocumento','cpe_cabecera.tdocod','pedidos.fecha_hora','pedidos.fecha_salida','pedidos.placa',DB::raw("(SELECT CONCAT(name,apeusu) FROM users WHERE IdUsuario= pedidos.IdUsuario) as usuario_registra"),DB::raw("(SELECT CONCAT(name,apeusu) FROM users WHERE IdUsuario= pedidos.IdUsuarioCob) as usuario_cobra"))
                ->join('pedidos','pedidos.ped_id','cpe_cabecera.ped_id')
                ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
                 ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                
                ->where(function ($query6) use ($almacen) {
                  if($almacen !=0 && !empty($almacen)){
                      $query6->where('cpe_cabecera.id_almacen',$almacen);
                  }                
                }) 
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                ->where(function ($query){
                    $query->Where('cpe_cabecera.tdocod','50');
                })
                ->where(function ($query1) use ($vendedor) {
                      if($vendedor !='Todos'){
                         $query1->Where('cpe_cabecera.IdUsuario_ven',$vendedor);
                      }
                    })
                 ->where(function ($query2) use ($cliente) {
                      if($cliente !='Todos'){
                         $query2->Where('cpe_cabecera.clicod',$cliente);
                      }
                    })
                ->orderBy('IdCpe_cabecera','desc')->get();

            //   dd($comprobantes);

                  $total = $comprobantes->sum('ccaitv');

                $vista = view('empresas.reportes.ventas_ingreso_vehiculos',compact('dat_cli','fecin','fecfin','comprobantes','total','dat_ven'))->render();

              if($request->ajax()){
                 return response()->json(['vista'=>$vista]);

              }


            break;

               case 16:

                $comprobantes = cpe_cabecera::select('ccaitv','cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','monnom as moneda','ccaitv as total','cpe_cabecera.ccabaj as baja','ccasunrescod','tdides as documentoidentidad','ccandi as numerodocumento','cpe_cabecera.tdocod','pedidos.fecha_hora','pedidos.fecha_salida','pedidos.placa',DB::raw("(SELECT CONCAT(name,apeusu) FROM users WHERE IdUsuario= pedidos.IdUsuario) as usuario_registra"),DB::raw("(SELECT CONCAT(name,apeusu) FROM users WHERE IdUsuario= pedidos.IdUsuarioCob) as usuario_cobra"))
                ->join('pedidos','pedidos.ped_id','cpe_cabecera.ped_id')
                ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
                 ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                
                ->where(function ($query6) use ($almacen) {
                  if($almacen !=0 && !empty($almacen)){
                      $query6->where('cpe_cabecera.id_almacen',$almacen);
                  }                
                }) 
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                ->where(function ($query){
                    $query->where('cpe_cabecera.tdocod','01')
                          ->orwhere('cpe_cabecera.tdocod','03')
                          ->orWhere('cpe_cabecera.tdocod','13')
                           ->orWhere('cpe_cabecera.tdocod','50');
                })
                ->where(function ($query1) use ($vendedor) {
                      if($vendedor !='Todos'){
                         $query1->Where('cpe_cabecera.IdUsuario_ven',$vendedor);
                      }
                    })
                 ->where(function ($query2) use ($cliente) {
                      if($cliente !='Todos'){
                         $query2->Where('cpe_cabecera.clicod',$cliente);
                      }
                    })
                ->orderBy('IdCpe_cabecera','desc')->get();

            //   dd($comprobantes);

                  $total = $comprobantes->sum('ccaitv');

                $vista = view('empresas.reportes.ventas_ingreso_vehiculos',compact('dat_cli','fecin','fecfin','comprobantes','total','dat_ven'))->render();

              if($request->ajax()){
                 return response()->json(['vista'=>$vista]);

              }


            break;

              case 17:

                $comprobantes = cpe_cabecera::select('ccaitv','cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','monnom as moneda','ccaitv as total','cpe_cabecera.ccabaj as baja','ccasunrescod','tdides as documentoidentidad','ccandi as numerodocumento','cpe_cabecera.tdocod','ccatexo','ccatvg','ccatvi')
                ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
                 ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                
                ->where(function ($query6) use ($almacen) {
                  if($almacen !=0 && !empty($almacen)){
                      $query6->where('cpe_cabecera.id_almacen',$almacen);
                  }                
                }) 
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                ->where(function ($query){
                    $query->where('cpe_cabecera.tdocod','01')
                          ->orwhere('cpe_cabecera.tdocod','03')
                          ->orWhere('cpe_cabecera.tdocod','13')
                          ->orWhere('cpe_cabecera.tdocod','07');
                })
                ->whereNull('ccabaj')
                ->orderBy('IdCpe_cabecera','desc')->get();

            //   dd($comprobantes);

                  $total = $comprobantes->sum('ccaitv');

                  $totalexoneradasfacturas = $comprobantes->where('tdocod','01')->sum('ccatexo');
                  $totalgravadasfacturas = $comprobantes->where('tdocod','01')->sum('ccatvg');

                  $totalexoneradasboletas = $comprobantes->where('tdocod','03')->sum('ccatexo');
                  $totalgravadasboletas = $comprobantes->where('tdocod','03')->sum('ccatvg');

                   $totalexoneradasnotas = $comprobantes->where('tdocod','07')->sum('ccatexo');
                  $totalgravadasnotas = $comprobantes->where('tdocod','07')->sum('ccatvg');


                $nompdffile='RESUMEN_VENTAS_'.$fecin.'_'.$fecfin.'.pdf'; 


                 $rutapdf = public_path().'/reporte_cuentas_cobrar/';

                 if(file_exists($rutapdf.$nompdffile)){
                  unlink($rutapdf.$nompdffile);
                 }

                 $sucursal = EmpresaNegocios::FindOrFail($request->get('sucursal'));

                 $empresa = Empresa::findOrFail($sucursal->IdEmpresa);

                $view = \View::make('formatos_reportes.reporte_pdf_resumen_ventas', compact('fecin','fecfin','total','totalexoneradasfacturas','totalexoneradasboletas','totalgravadasfacturas','totalgravadasboletas','totalgravadasnotas','totalexoneradasnotas','empresa','sucursal'));

                            
                $pdf = \App::make('dompdf.wrapper');
                $contenido = $view->render();
                $pdf->loadHTML($view)->setPaper('A4', 'lanscape')->save($rutapdf.$nompdffile);

               
                 $headers = array(
                    'Content-Type: application/pdf',
                  );

     


           
                 return response()->download($rutapdf.$nompdffile);

              


            break;

             case 19:

                if($vendedor!='Todos'){
                $dato_vendedor = DB::tABLE('users')->where('IdUsuario',$vendedor)->first();
                }

                if($cliente!='Todos'){
                  $dato_cliente = DB::tABLE('cliente')->where('clicod',$cliente)->first();
                }

               

                $facturas = cpe_cabecera::select('ccaitv','cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','monnom as moneda','ccaitv as total','cpe_cabecera.ccabaj as baja','ccasunrescod','tdides as documentoidentidad','ccandi as numerodocumento',
                  'cpe_cabecera.tdocod','ccafve as fechaven','cpe_cabecera.tdicod','ccatexo','ccafem_ref','serie_ref','tdocod_ref','num_ref','cod_mov')
                ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
                 ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                
                ->where(function ($query6) use ($almacen) {
                  if($almacen !='Todos'){
                      $query6->where('cpe_cabecera.id_almacen',$almacen);
                  }                
                }) 
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                ->where(function ($query){
                    $query->where('cpe_cabecera.tdocod','01');
                })
                ->where(function ($query1) use ($vendedor) {
                      if($vendedor !='Todos'){
                         $query1->Where('cpe_cabecera.IdUsuario_ven',$vendedor);
                      }
                    })
                 ->where(function ($query2) use ($cliente) {
                      if($cliente !='Todos'){
                         $query2->Where('cpe_cabecera.clicod',$cliente);
                      }
                    })
                ->orderBy('IdCpe_cabecera','desc')->get();

                 $boletas = cpe_cabecera::select('ccaitv','cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','monnom as moneda','ccaitv as total','cpe_cabecera.ccabaj as baja','ccasunrescod','tdides as documentoidentidad','ccandi as numerodocumento',
                  'cpe_cabecera.tdocod','ccafve as fechaven','cpe_cabecera.tdicod','ccatexo','ccafem_ref','serie_ref','tdocod_ref','num_ref','cod_mov')
                ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
                 ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                
                ->where(function ($query6) use ($almacen) {
                  if($almacen !='Todos'){
                      $query6->where('cpe_cabecera.id_almacen',$almacen);
                  }                
                }) 
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                ->where(function ($query){
                    $query->where('cpe_cabecera.tdocod','03');
                })
                ->where(function ($query1) use ($vendedor) {
                      if($vendedor !='Todos'){
                         $query1->Where('cpe_cabecera.IdUsuario_ven',$vendedor);
                      }
                    })
                 ->where(function ($query2) use ($cliente) {
                      if($cliente !='Todos'){
                         $query2->Where('cpe_cabecera.clicod',$cliente);
                      }
                    })
                ->orderBy('IdCpe_cabecera','desc')->get();


                 $notas = cpe_cabecera::select('ccaitv','cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','monnom as moneda','ccaitv as total','cpe_cabecera.ccabaj as baja','ccasunrescod','tdides as documentoidentidad','ccandi as numerodocumento',
                  'cpe_cabecera.tdocod','ccafve as fechaven','cpe_cabecera.tdicod','ccatexo','ccafem_ref','serie_ref','tdocod_ref','num_ref','cod_mov')
                ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
                 ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                
                ->where(function ($query6) use ($almacen) {
                  if($almacen !='Todos'){
                      $query6->where('cpe_cabecera.id_almacen',$almacen);
                  }                
                }) 
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                ->where(function ($query){
                    $query->where('cpe_cabecera.tdocod','07');
                })
                ->where(function ($query1) use ($vendedor) {
                      if($vendedor !='Todos'){
                         $query1->Where('cpe_cabecera.IdUsuario_ven',$vendedor);
                      }
                    })
                 ->where(function ($query2) use ($cliente) {
                      if($cliente !='Todos'){
                         $query2->Where('cpe_cabecera.clicod',$cliente);
                      }
                    })
                ->orderBy('IdCpe_cabecera','desc')->get();

      

                  $totalfacturas = $facturas->sum('ccaitv');
                  $totalboletas = $boletas->sum('ccaitv');
                  $totalnotas = $notas->sum('ccaitv');


                  $totalfacturasexo = $facturas->sum('ccatexo');
                  $totalboletasexo = $boletas->sum('ccatexo');
                  $totalnotasexo = $notas->sum('ccatexo');
                    

                  $totalfacturasinaf = $facturas->sum('ccatvi');
                  $totalboletasinaf = $boletas->sum('ccatvi');
                  $totalnotasinaf = $notas->sum('ccatvi');
                  

                   $totalfacturasigv = $facturas->sum('ccaigv');
                  $totalboletasigv = $boletas->sum('ccaigv');
                  $totalnotasigv = $notas->sum('ccaigv');
                 
                  $totalfacturasicbper = $facturas->sum('icbper');
                  $totalboletasicbper = $boletas->sum('icbper');
                  $totalnotasicbper = $notas->sum('icbper');

                 $nompdffile='resumen_ventas_contador'.$data_sucursal->IdEmpresa.'_'.now()->format('Y-m-d').'.pdf'; 


                 $rutapdf = public_path().'/reporte_cuentas_cobrar/';

                 if(file_exists($rutapdf.$nompdffile)){
                  unlink($rutapdf.$nompdffile);
                 }

                $view = \View::make('formatos_reportes.reporte_pdf_ventas_contador', compact('dato_cliente','fecin','fecfin','facturas','boletas','notas','totalfacturas','totalboletas','totalnotas','totalfacturasexo','totalboletasexo','totalnotasexo','totalfacturasinaf','totalboletasinaf','totalnotasinaf','totalfacturasigv','totalboletasigv','totalnotasigv','totalfacturasicbper','totalboletasicbper','totalnotasicbper','dato_vendedor','vendedor','cliente','empresa','data_sucursal'));
                            
                $pdf = \App::make('dompdf.wrapper');
                $contenido = $view->render();
                $pdf->loadHTML($view)->setPaper('A4', 'Landscape')->save($rutapdf.$nompdffile);


                $headers = array(
                        'Content-Type: application/pdf',
                      );

                return response()->download($rutapdf.$nompdffile);
  
          

            break;

             case 51:

                if($vendedor!='Todos'){
                $dato_vendedor = DB::tABLE('users')->where('IdUsuario',$vendedor)->first();
                }

                if($cliente!='Todos'){
                  $dato_cliente = DB::tABLE('cliente')->where('clicod',$cliente)->first();
                }

                $comprobantes = cpe_cabecera::select('cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','det.cdedes as producto','det.cdecan as cantidad','cdepuni as precio','monnom as moneda','det.cdevve as total','cpe_cabecera.ccabaj as baja','det.costo','ccasunrescod','tdides as documentoidentidad','ccandi as numerodocumento','procod','cdedes','cdecan','cdepuni','cdevve','umecod','ccaitv','id_almacen_pro')
                ->leftjoin('cpe_detalle as det','cpe_cabecera.IdCpe_cabecera','det.IdCpe_cabecera')
                ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                 ->where(function ($query) use ($vendedor) {
                      if($vendedor !='Todos'){
                         $query->Where('cpe_cabecera.IdUsuario_ven',$vendedor);
                      }
                    })
                ->where(function ($query) {
                  $query->where('cpe_cabecera.tdocod','01')
                    ->orWhere('cpe_cabecera.tdocod','03')
                    ->orWhere('cpe_cabecera.tdocod','13')
                    ->orWhere('cpe_cabecera.tdocod','14')
                    //->orWhere('cpe_cabecera.tdocod','07')
                    ;
                  }) 
                  ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                   ->where(function ($query2) use ($cliente) {
                      if($cliente !='Todos'){
                         $query2->Where('cpe_cabecera.clicod',$cliente);
                      }
                    })
                  ->whereNotNull('ccabaj')
                
               /* ->where(function ($query6) use ($idalmacen) {
                  if($idalmacen !='Todos' && !empty($idalmacen)){
                      $query6->where('det.id_almacen_pro',$idalmacen);
                  }                
                })   */ 
                 ->orderBy('IdCpe_cabecera','desc')->get();




                 $cabecera = DB::tABLE('cpe_cabecera')
             //    ->join('cpe_detalle','cpe_detalle.IdCpe_cabecera','cpe_cabecera.IdCpe_cabecera')
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                 ->where(function ($query) use ($vendedor){
                      if($vendedor !='Todos'){
                         $query->Where('cpe_cabecera.IdUsuario_ven',$vendedor);
                      }
                    })
                ->where(function ($query) {
                  $query->where('cpe_cabecera.tdocod','01')
                    ->orWhere('cpe_cabecera.tdocod','03')
                    ->orWhere('cpe_cabecera.tdocod','13')
                    ->orWhere('cpe_cabecera.tdocod','14')
                   // ->orWhere('cpe_cabecera.tdocod','07')
                    ;
                  }) 
                ->whereNotNull('ccabaj')
               // ->where('ccanot','')
                ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                 ->where(function ($query2) use ($cliente) {
                      if($cliente !='Todos'){
                         $query2->Where('cpe_cabecera.clicod',$cliente);
                      }
                    })
                
               /* ->where(function ($query6) use ($idalmacen) {
                  if($idalmacen !='Todos' && !empty($idalmacen)){
                      $query6->where('cpe_detalle.id_almacen_pro',$idalmacen);
                  }                
                }) */
               // ->groupby('cpe_cabecera.IdCpe_cabecera') 
                 ->get();

                 $total = $cabecera->where('ccanot','')->sum('ccaitv');

                 $totalnotas = $cabecera->where('tdocod','7')->sum('ccaitv');

                 $totalefectivo = $cabecera->where('ccanot','')->sum('totalcontado');
                 $totalcredito = $cabecera->where('ccanot','')->sum('totalcredito');
             
                 $nompdffile='resumen_ventas_anuladas_'.$data_sucursal->IdEmpresa.'_'.now()->format('Y-m-d').'.pdf'; 


                 $rutapdf = public_path().'/reporte_cuentas_cobrar/';

                 if(file_exists($rutapdf.$nompdffile)){
                  unlink($rutapdf.$nompdffile);
                 }

                $view = \View::make('formatos_reportes.reporte_pdf_ventas_anuladas', compact('totalnotas','total','dato_vendedor','cabecera','comprobantes','fecin','fecfin','empresa','sucursal','almacen','data_sucursal','dato_cliente','totalefectivo','totalcredito'));

                            
                $pdf = \App::make('dompdf.wrapper');
                $contenido = $view->render();
                $pdf->loadHTML($view)->setPaper('A4', 'lanscape')->save($rutapdf.$nompdffile);


                $headers = array(
                        'Content-Type: application/pdf',
                      );

                return response()->download($rutapdf.$nompdffile);
  
          

            break;

             case 52:

                if($vendedor!='Todos'){
                $dato_vendedor = DB::tABLE('users')->where('IdUsuario',$vendedor)->first();
                }

                if($cliente!='Todos'){
                  $dato_cliente = DB::tABLE('cliente')->where('clicod',$cliente)->first();
                }

                $comprobantes = cpe_cabecera::select('cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','det.cdedes as producto','det.cdecan as cantidad','cdepuni as precio','monnom as moneda','det.cdevve as total','cpe_cabecera.ccabaj as baja','det.costo','ccasunrescod','tdides as documentoidentidad','ccandi as numerodocumento','procod','cdedes','cdecan','cdepuni','cdevve','umecod','ccaitv','id_almacen_pro')
                ->leftjoin('cpe_detalle as det','cpe_cabecera.IdCpe_cabecera','det.IdCpe_cabecera')
                ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                 ->where(function ($query) use ($vendedor) {
                      if($vendedor !='Todos'){
                         $query->Where('cpe_cabecera.IdUsuario_ven',$vendedor);
                      }
                    })
                ->where(function ($query) {
                  $query->where('cpe_cabecera.tdocod','01')
                    ->orWhere('cpe_cabecera.tdocod','03')
                    ->orWhere('cpe_cabecera.tdocod','13')
                    ->orWhere('cpe_cabecera.tdocod','14')
                    //->orWhere('cpe_cabecera.tdocod','07')
                    ;
                  }) 
                  ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                   ->where(function ($query2) use ($cliente) {
                      if($cliente !='Todos'){
                         $query2->Where('cpe_cabecera.clicod',$cliente);
                      }
                    })
                
               /* ->where(function ($query6) use ($idalmacen) {
                  if($idalmacen !='Todos' && !empty($idalmacen)){
                      $query6->where('det.id_almacen_pro',$idalmacen);
                  }                
                })   */ 
                 ->orderBy('IdCpe_cabecera','desc')->get();




                 $cabecera = DB::tABLE('cpe_cabecera')
             //    ->join('cpe_detalle','cpe_detalle.IdCpe_cabecera','cpe_cabecera.IdCpe_cabecera')
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                 ->where(function ($query) use ($vendedor){
                      if($vendedor !='Todos'){
                         $query->Where('cpe_cabecera.IdUsuario_ven',$vendedor);
                      }
                    })
                ->where(function ($query) {
                  $query->where('cpe_cabecera.tdocod','01')
                    ->orWhere('cpe_cabecera.tdocod','03')
                    ->orWhere('cpe_cabecera.tdocod','13')
                    ->orWhere('cpe_cabecera.tdocod','14')
                   // ->orWhere('cpe_cabecera.tdocod','07')
                    ;
                  }) 
                ->whereNull('ccabaj')
                ->where('ccanot','')
                ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                 ->where(function ($query2) use ($cliente) {
                      if($cliente !='Todos'){
                         $query2->Where('cpe_cabecera.clicod',$cliente);
                      }
                    })
                
               /* ->where(function ($query6) use ($idalmacen) {
                  if($idalmacen !='Todos' && !empty($idalmacen)){
                      $query6->where('cpe_detalle.id_almacen_pro',$idalmacen);
                  }                
                }) */
               // ->groupby('cpe_cabecera.IdCpe_cabecera') 
                 ->get();

                 $total = $cabecera->where('ccanot','')->sum('ccaitv');

                 $totalnotas = $cabecera->where('tdocod','7')->sum('ccaitv');

                 $totalefectivo = $cabecera->where('ccanot','')->sum('totalcontado');
                 $totalcredito = $cabecera->where('ccanot','')->sum('totalcredito');
             
                 $nompdffile='resumen_pedidos_anulados_'.$data_sucursal->IdEmpresa.'_'.now()->format('Y-m-d').'.pdf'; 


                 $rutapdf = public_path().'/reporte_cuentas_cobrar/';

                 if(file_exists($rutapdf.$nompdffile)){
                  unlink($rutapdf.$nompdffile);
                 }

                $view = \View::make('formatos_reportes.reporte_pdf_pedidos_anulados', compact('totalnotas','total','dato_vendedor','cabecera','comprobantes','fecin','fecfin','empresa','sucursal','almacen','data_sucursal','dato_cliente','totalefectivo','totalcredito'));

                            
                $pdf = \App::make('dompdf.wrapper');
                $contenido = $view->render();
                $pdf->loadHTML($view)->setPaper('A4', 'lanscape')->save($rutapdf.$nompdffile);


                $headers = array(
                        'Content-Type: application/pdf',
                      );

                return response()->download($rutapdf.$nompdffile);
  
          

            break;


                  case 53:

                 $comprobantes = compras_cabecera::select('total_com','com_cab_id','com_fec as fecha','tdodes as comprobante','com_doc_ser as serie','com_doc_num as numero','prov_raz as cliente','monnom as moneda','total_com as total','tdides as documentoidentidad','prov_ruc as numerodocumento','compras_cabecera.tdocod')
                ->leftjoin('moneda as m','compras_cabecera.mon_id','=','m.moncod')
                ->leftjoin('proveedor as cl','compras_cabecera.prov_id','=','cl.prov_id')
               //->leftjoin('empresa as e','compras_cabecera.IdEmpresa','=','e.IdEmpresa')
                ->leftjoin('tipo_documento as tip_d','compras_cabecera.tdocod','=','tip_d.tdocod')
                ->leftjoin('tipo_documento_identidad as tdi','cl.tdicod','=','tdi.tdicod')
                 ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('compras_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                
                ->where(function ($query6) use ($almacen) {
                  if($almacen !=0 && !empty($almacen)){
                      $query6->where('compras_cabecera.id_almacen',$almacen);
                  }                
                }) 
                ->where('compras_cabecera.com_fec','>=',$fecin)
                ->where('compras_cabecera.com_fec','<=',$fecfin)
                /*->where(function ($query){
                    $query->where('compras_cabecera.tdocod','01')
                          ->orwhere('compras_cabecera.tdocod','03')
                          ->orWhere('compras_cabecera.tdocod','13')
                          ->orWhere('compras_cabecera.tdocod','07');
                })*/
                ->where('est_compra','Registrado')
                ->orderBy('com_cab_id','desc')->get();

            //  dd($comprobantes);

                  $total = $comprobantes->sum('total_com');

                  $totalexoneradasfacturas = $comprobantes->where('tdocod','01')->sum('total_com');
                  $totalgravadasfacturas = $comprobantes->where('tdocod','01')->sum('ccatvg');

                  $totalexoneradasboletas = $comprobantes->where('tdocod','03')->sum('total_com');
                  $totalgravadasboletas = $comprobantes->where('tdocod','03')->sum('ccatvg');

                  $totalexoneradasnotas = $comprobantes->where('tdocod','07')->sum('total_com');
                  $totalgravadasnotas = $comprobantes->where('tdocod','07')->sum('ccatvg');


            

                 $sucursal = EmpresaNegocios::FindOrFail($request->get('sucursal'));

                 $empresa = Empresa::findOrFail($sucursal->IdEmpresa);

                $nompdffile='RESUMEN_COMPRAS_'.$fecin.'_'.$fecfin.'.pdf'; 


                 $rutapdf = public_path().'/reporte_cuentas_cobrar/';

                 if(file_exists($rutapdf.$nompdffile)){
                  unlink($rutapdf.$nompdffile);
                 }

               
                $view = \View::make('formatos_reportes.reporte_pdf_resumen_compras', compact('fecin','fecfin','total','totalexoneradasfacturas','totalexoneradasboletas','totalgravadasfacturas','totalgravadasboletas','totalgravadasnotas','totalexoneradasnotas','empresa','sucursal'));

                            
                $pdf = \App::make('dompdf.wrapper');
                $contenido = $view->render();
                $pdf->loadHTML($view)->setPaper('A4', 'lanscape')->save($rutapdf.$nompdffile);

               
                 $headers = array(
                    'Content-Type: application/pdf',
                  );

     


           
                 return response()->download($rutapdf.$nompdffile);

              


            break;

             case 54:

               $comprobantes = compras_cabecera::select('total_com','compras_cabecera.com_cab_id','com_fec as fecha','tdodes as comprobante','compras_cabecera.com_doc_ser as serie','compras_cabecera.com_doc_num as numero','prov_raz as cliente','monnom as moneda','total_com as total','tdides as documentoidentidad','prov_ruc as numerodocumento',
                  'compras_cabecera.tdocod','com_fec_ven as fechaven','cl.tdicod','total_com as ccatexo')
                ->leftjoin('moneda as m','compras_cabecera.mon_id','=','m.moncod')
                ->leftjoin('proveedor as cl','compras_cabecera.prov_id','=','cl.prov_id')
                ->leftjoin('empresa as e','compras_cabecera.IdEmpresa','=','e.IdEmpresa')
                ->leftjoin('tipo_documento as tip_d','compras_cabecera.tdocod','=','tip_d.tdocod')
                ->leftjoin('tipo_documento_identidad as tdi','tdi.tdicod','=','cl.tdicod')
                 ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('compras_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                
                ->where(function ($query6) use ($almacen) {
                  if($almacen !='Todos'){
                      $query6->where('compras_cabecera.id_almacen',$almacen);
                  }                
                }) 
                ->where('compras_cabecera.com_fec','>=',$fecin)
                ->where('compras_cabecera.com_fec','<=',$fecfin)
                /* ->where(function ($query){
                    $query->where('compras_cabecera.tdocod','01')
                          ->orwhere('compras_cabecera.tdocod','03')
                          ->orWhere('compras_cabecera.tdocod','13');
                })
               ->where(function ($query1) use ($vendedor) {
                      if($vendedor !='Todos'){
                         $query1->Where('compras_cabecera.IdUsuario_ven',$vendedor);
                      }
                    })*/
                 /*->where(function ($query2) use ($cliente) {
                      if($cliente !='Todos'){
                         $query2->Where('compras_cabecera.clicod',$cliente);
                      }
                    })*/
                ->orderBy('com_fec','desc')->get();

      

                  $total = $comprobantes->sum('total_com');
             
                 $nompdffile='resumen_compras_contador'.$data_sucursal->IdEmpresa.'_'.now()->format('Y-m-d').'.pdf'; 


                 $rutapdf = public_path().'/reporte_cuentas_cobrar/';

                 if(file_exists($rutapdf.$nompdffile)){
                  unlink($rutapdf.$nompdffile);
                 }

                $view = \View::make('formatos_reportes.reporte_pdf_compras_contador', compact('dato_cliente','fecin','fecfin','comprobantes','total','dato_vendedor','vendedor','cliente','empresa','data_sucursal'));
                            
                $pdf = \App::make('dompdf.wrapper');
                $contenido = $view->render();
                $pdf->loadHTML($view)->setPaper('A4', 'Landscape')->save($rutapdf.$nompdffile);


                $headers = array(
                        'Content-Type: application/pdf',
                      );

                return response()->download($rutapdf.$nompdffile);
  
          

            break;

               case 55:

              $comprobantes = DB::tABLE('compras_cabecera')->select('compras_detalle.vencimiento','compras_detalle.lote','pronom')
              ->leftjoin('proveedor','compras_cabecera.prov_id','proveedor.prov_id')
              ->leftjoin('compras_detalle','compras_cabecera.com_cab_id','compras_detalle.com_cab_id')
              ->leftjoin('productos','productos.IdProducto','compras_detalle.pro_id')
              ->join('moneda','compras_cabecera.mon_id','moneda.moncod')
              ->join('tipo_documento','compras_cabecera.tdocod','tipo_documento.tdocod')
              
              ->where('est_compra','Registrado')
                ->where('compras_cabecera.com_fec','>=',$fecin)
                ->where('compras_cabecera.com_fec','<=',$fecfin)
                  ->where(function ($query) use ($sucursal){
                      if(!$sucursal !=0 && !empty($sucursal)){
                          $query->where('compras_cabecera.id_empresa_negocio',$sucursal);
                      }
                     
                  })
                  ->where(function ($query1) use ($proveedor){
                      if($proveedor !=0){
                          $query1->where('compras_cabecera.prov_id',$proveedor);
                      }
                      
                  })

                   ->where(function ($query8) use ($pro){
                      if($pro !=0){
                          $query8->where('compras_detalle.IdProducto_rel',$pro);
                      }
                      
                  })
                        
                  ->where(function ($query6) use ($almacen) {
                      if($almacen !=0 && !empty($almacen)){
                          $query6->where('compras_cabecera.id_almacen',$almacen);
                      }                
                    }) 
              ->where('compras_cabecera.tdocod','!=','80')
              ->groupby('pro_id','compras_detalle.vencimiento')
              ->orderby('compras_cabecera.com_cab_id','desc')
              ->get();
                 //dd($comprobantes);

                  $total = $comprobantes->sum('total_com');


                 $nompdffile='resumen_compras_producto_lote'.$data_sucursal->IdEmpresa.'_'.now()->format('Y-m-d').'.pdf'; 


                 $rutapdf = public_path().'/reporte_cuentas_cobrar/';

                 if(file_exists($rutapdf.$nompdffile)){
                  unlink($rutapdf.$nompdffile);
                 }

                $view = \View::make('formatos_reportes.reporte_pdf_compras_productos_lote', compact('fecin','fecfin','comprobantes','total','empresa','data_sucursal'));
                            
                $pdf = \App::make('dompdf.wrapper');
                $contenido = $view->render();
                $pdf->loadHTML($view)->setPaper('A4', 'Portrait')->save($rutapdf.$nompdffile);


                $headers = array(
                        'Content-Type: application/pdf',
                      );

                return response()->download($rutapdf.$nompdffile);
  

              break;



                  case 13:

              $comprobantes = DB::tABLE('compras_cabecera')
              ->leftjoin('proveedor','compras_cabecera.prov_id','proveedor.prov_id')
              ->leftjoin('compras_detalle','compras_cabecera.com_cab_id','compras_detalle.com_cab_id')
              ->leftjoin('productos','productos.IdProducto','compras_detalle.pro_id')
              ->join('moneda','compras_cabecera.mon_id','moneda.moncod')
              ->join('tipo_documento','compras_cabecera.tdocod','tipo_documento.tdocod')
              
              ->where('est_compra','Registrado')
                ->where('compras_cabecera.com_fec','>=',$fecin)
                ->where('compras_cabecera.com_fec','<=',$fecfin)
                  ->where(function ($query) use ($sucursal){
                      if(!$sucursal !=0 && !empty($sucursal)){
                          $query->where('compras_cabecera.id_empresa_negocio',$sucursal);
                      }
                     
                  })
                  ->where(function ($query1) use ($proveedor){
                      if($proveedor !=0){
                          $query1->where('compras_cabecera.prov_id',$proveedor);
                      }
                      
                  })

                   ->where(function ($query8) use ($pro){
                      if($pro !=0){
                          $query8->where('compras_detalle.IdProducto_rel',$pro);
                      }
                      
                  })
                        
                  ->where(function ($query6) use ($almacen) {
                      if($almacen !=0 && !empty($almacen)){
                          $query6->where('compras_cabecera.id_almacen',$almacen);
                      }                
                    }) 
              ->where('compras_cabecera.tdocod','!=','80')
              ->orderby('compras_cabecera.com_cab_id','desc')
              ->get();
                 //dd($comprobantes);

                  $total = $comprobantes->sum('total_com');


                    $nompdffile='resumen_compras_productos'.$data_sucursal->IdEmpresa.'_'.now()->format('Y-m-d').'.pdf'; 


                 $rutapdf = public_path().'/reporte_cuentas_cobrar/';

                 if(file_exists($rutapdf.$nompdffile)){
                  unlink($rutapdf.$nompdffile);
                 }

                $view = \View::make('formatos_reportes.reporte_pdf_compras_productos', compact('fecin','fecfin','comprobantes','total','empresa','data_sucursal'));
                            
                $pdf = \App::make('dompdf.wrapper');
                $contenido = $view->render();
                $pdf->loadHTML($view)->setPaper('A4', 'Portrait')->save($rutapdf.$nompdffile);


                $headers = array(
                        'Content-Type: application/pdf',
                      );

                return response()->download($rutapdf.$nompdffile);


             break;


               //CONSOLIDADO DE VENTAS
              case 80:

                $boletas = cpe_cabecera::select('ccafem as FECHA', 'tdodes as COMPROBANTE','serdoc as SERIE',DB::raw('MIN(numdoc) as INICIO'),DB::raw( 'MAX(numdoc) as FIN'),DB::raw('SUM(ccatvg) as VALOR_VENTA'),DB::raw('SUM(ccaigv) as IGV'),DB::raw('SUM(ccaitv) as TOTAL'))
                ->leftjoin('tipo_documento','tipo_documento.tdocod','cpe_cabecera.tdocod')
                ->whereNull('ccabaj')
                ->where('cpe_cabecera.tdocod','03')
                ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                }) 
                ->where(function ($query6) use ($almacen) {
                  if($almacen !='Todos'){
                      $query6->where('cpe_cabecera.id_almacen',$almacen);
                  }                
                }) 
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                ->groupby('ccafem')
                ->groupby('tdodes')
                ->orderBy('FECHA','asc')
                ->orderBy('INICIO','asc')
                ->get();

                $facturas = cpe_cabecera::select('ccaitv','cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','monnom as moneda','ccaitv as total','cpe_cabecera.ccabaj as baja','ccasunrescod','tdides as documentoidentidad','ccandi as numerodocumento','cpe_cabecera.tdocod','ccatexo','ccatvg','ccatvi')
                ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
                ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                
                ->where(function ($query6) use ($almacen) {
                  if($almacen !=0 && !empty($almacen)){
                      $query6->where('cpe_cabecera.id_almacen',$almacen);
                  }                
                }) 
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                ->where(function ($query){
                    $query->where('cpe_cabecera.tdocod','01');
                })
                ->whereNull('ccabaj')
                ->orderBy('IdCpe_cabecera','desc')->get();

             

                $total_boletas = $boletas->sum('TOTAL');
                $total_facturas = $facturas->sum('total');


                $nompdffile='consolidado_ventas'.$data_sucursal->IdEmpresa.'_'.now()->format('Y-m-d').'.pdf'; 


                $rutapdf = public_path().'/reporte_cuentas_cobrar/';

                 if(file_exists($rutapdf.$nompdffile)){
                  unlink($rutapdf.$nompdffile);
                 }

                $view = \View::make('formatos_reportes.reporte_pdf_consolidado_ventas', compact('fecin','fecfin','facturas','boletas','total_facturas','total_boletas','empresa','data_sucursal'));
                            
                $pdf = \App::make('dompdf.wrapper');
                $contenido = $view->render();
                $pdf->loadHTML($view)->setPaper('A4', 'Portrait')->save($rutapdf.$nompdffile);


                $headers = array(
                        'Content-Type: application/pdf',
                      );

                return response()->download($rutapdf.$nompdffile);





             

         
      }

    }




    public function buscarreporte(Request $request){

      $fecin = $request->get('fecin');
      $fecfin = $request->get('fecfin');
      $opcion = $request->get('opcion');
      $vendedor = $request->get('vendedor');
      $cliente = $request->get('cliente');
      $producto = $request->get('producto');
      $pro = $request->get('IdProducto');
      $almacen = $request->get('almacen');
      $sucursal = $request->get('sucursal');
      $proveedor = $request->get('proveedor');
     
      $data_sucursal = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',$sucursal)->first();

      //$productos = DB::tABLE('productos')->where('tipo','1')->get();
      $negocios = DB::tABLE('empresa_negocios')->get();

      if(!empty($sucursal)){
        $almacenes = DB::tABLE('almacenes')->where('id_empresa_negocio',$sucursal)->get();
      }else{
        $almacenes = DB::tABLE('almacenes')->where('id_empresa_negocio',$negocios->first()->id_empresa_negocio)->get();
      }

      $dat_ven = DB::tABLE('users')->where('IdUsuario',$request->get('vendedor'))->first();

      $dat_cli = DB::tABLE('cliente')->where('clicod',$request->get('cliente'))->first();

       $empresa = DB::tABLE('empresa')->where('IdEmpresa',$data_sucursal->IdEmpresa)->first();

      $hora_rep = now()->format('Y-m-d H:i:s');
      $dato_vendedor ="";
      $dato_cliente ="";

      switch ($opcion){
            case 1:

                if($vendedor!='Todos'){
                $dato_vendedor = DB::tABLE('users')->where('IdUsuario',$vendedor)->first();
                }

                if($cliente!='Todos'){
                  $dato_cliente = DB::tABLE('cliente')->where('clicod',$cliente)->first();
                }

                $comprobantes = cpe_cabecera::select('cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','det.cdedes as producto','det.cdecan as cantidad','cdepuni as precio','monnom as moneda','det.cdevve as total','cpe_cabecera.ccabaj as baja','det.costo','ccasunrescod','tdides as documentoidentidad','ccandi as numerodocumento','procod','cdedes','cdecan','cdepuni','cdevve','umecod','ccaitv','id_almacen_pro')
                ->leftjoin('cpe_detalle as det','cpe_cabecera.IdCpe_cabecera','det.IdCpe_cabecera')
                ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                 ->where(function ($query) use ($vendedor) {
                      if($vendedor !='Todos'){
                         $query->Where('cpe_cabecera.IdUsuario_ven',$vendedor);
                      }
                    })
                ->where(function ($query) {
                  $query->where('cpe_cabecera.tdocod','01')
                    ->orWhere('cpe_cabecera.tdocod','03')
                    ->orWhere('cpe_cabecera.tdocod','13')
                    ->orWhere('cpe_cabecera.tdocod','14')
                    //->orWhere('cpe_cabecera.tdocod','07')
                    ;
                  }) 
                  ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                   ->where(function ($query2) use ($cliente) {
                      if($cliente !='Todos'){
                         $query2->Where('cpe_cabecera.clicod',$cliente);
                      }
                    })
                
               /* ->where(function ($query6) use ($idalmacen) {
                  if($idalmacen !='Todos' && !empty($idalmacen)){
                      $query6->where('det.id_almacen_pro',$idalmacen);
                  }                
                })   */ 
                 ->orderBy('IdCpe_cabecera','desc')->get();




                 $cabecera = DB::tABLE('cpe_cabecera')
             //    ->join('cpe_detalle','cpe_detalle.IdCpe_cabecera','cpe_cabecera.IdCpe_cabecera')
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                 ->where(function ($query) use ($vendedor){
                      if($vendedor !='Todos'){
                         $query->Where('cpe_cabecera.IdUsuario_ven',$vendedor);
                      }
                    })
                ->where(function ($query) {
                  $query->where('cpe_cabecera.tdocod','01')
                    ->orWhere('cpe_cabecera.tdocod','03')
                    ->orWhere('cpe_cabecera.tdocod','13')
                    ->orWhere('cpe_cabecera.tdocod','14')
                   // ->orWhere('cpe_cabecera.tdocod','07')
                    ;
                  }) 
                ->whereNull('ccabaj')
                ->where('ccanot','')
                ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                 ->where(function ($query2) use ($cliente) {
                      if($cliente !='Todos'){
                         $query2->Where('cpe_cabecera.clicod',$cliente);
                      }
                    })
                
               /* ->where(function ($query6) use ($idalmacen) {
                  if($idalmacen !='Todos' && !empty($idalmacen)){
                      $query6->where('cpe_detalle.id_almacen_pro',$idalmacen);
                  }                
                }) */
               // ->groupby('cpe_cabecera.IdCpe_cabecera') 
                 ->get();

                 $total = $cabecera->where('ccanot','')->sum('ccaitv');

                 $totalnotas = $cabecera->where('tdocod','7')->sum('ccaitv');

                 $totalefectivo = $cabecera->where('ccanot','')->sum('totalcontado');

                 $totalcredito = $cabecera->where('ccanot','')->sum('totalcredito');

                $vista = view('empresas.reportes.ventas', compact('totalnotas','total','dato_vendedor','cabecera','comprobantes','fecin','fecfin','empresa','sucursal','almacen','data_sucursal','dato_cliente','totalefectivo','totalcredito'))->render();

              if($request->ajax()){
                 return response()->json(['vista'=>$vista]);

              }


            break;

            case 2:

                 if($vendedor!='Todos'){
                  $dato_vendedor = DB::tABLE('users')->where('IdUsuario',$vendedor)->first();
                }

                if($cliente!='Todos'){
                  $dato_cliente = DB::tABLE('cliente')->where('clicod',$cliente)->first();
                }

                $comprobantes = cpe_cabecera::select('cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','det.cdedes as producto','det.cdecan as cantidad','cdepuni as precio','monnom as moneda','det.cdevve as total','cpe_cabecera.ccabaj as baja','det.costo','ccasunrescod','tdides as documentoidentidad','ccandi as numerodocumento','procod','cdedes','cdecan','cdepuni','cdevve','umecod','ccaitv','id_almacen_pro')
                ->leftjoin('cpe_detalle as det','cpe_cabecera.IdCpe_cabecera','det.IdCpe_cabecera')
                ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                 ->where(function ($query) use ($vendedor) {
                      if($vendedor !='Todos'){
                         $query->Where('cpe_cabecera.IdUsuario_ven',$vendedor);
                      }
                    })
                ->where(function ($query) {
                  $query->where('cpe_cabecera.tdocod','01')
                    ->orWhere('cpe_cabecera.tdocod','03')
                    ->orWhere('cpe_cabecera.tdocod','13')
                    ->orWhere('cpe_cabecera.tdocod','14')
                    ->orWhere('cpe_cabecera.tdocod','07')
                    ;
                  }) 
                    ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                       ->where(function ($query2) use ($cliente) {
                      if($cliente !='Todos'){
                         $query2->Where('cpe_cabecera.clicod',$cliente);
                      }
                    })
                 ->orderBy('IdCpe_cabecera','desc')->get();




                 $cabecera = DB::tABLE('cpe_cabecera')
             //    ->join('cpe_detalle','cpe_detalle.IdCpe_cabecera','cpe_cabecera.IdCpe_cabecera')
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                 ->where(function ($query) use ($vendedor) {
                      if($vendedor !='Todos'){
                         $query->Where('cpe_cabecera.IdUsuario_ven',$vendedor);
                      }
                    })
                ->where(function ($query) {
                  $query->where('cpe_cabecera.tdocod','01')
                    ->orWhere('cpe_cabecera.tdocod','03')
                    ->orWhere('cpe_cabecera.tdocod','13')
                    ->orWhere('cpe_cabecera.tdocod','14')
                    ->orWhere('cpe_cabecera.tdocod','07')
                    ;
                  }) 
                    ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                })
                   ->where(function ($query2) use ($cliente) {
                      if($cliente !='Todos'){
                         $query2->Where('cpe_cabecera.clicod',$cliente);
                      }
                    })
                 ->get();

                 $total = $cabecera->where('ccanot','')->sum('ccaitv');

                 $totalnotas = $cabecera->where('tdocod','7')->sum('ccaitv');

                 /*---------------------------------------REPORTE VENTAS POR PRODUCTO RESUMEN------------------------------------------------------*/


                $productos = cpe_cabecera::select(DB::RAW('sum(cpe_detalle.cdecan*cpe_det_factor) as cantidad'),'cdedes',DB::RAW('sum(cpe_detalle.cdevve) as precio'),'cpe_detalle.procod','umecod')
                ->leftjoin('cpe_detalle','cpe_detalle.IdCpe_cabecera','cpe_cabecera.IdCpe_cabecera')
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                ->whereNull('cpe_cabecera.ccabaj')
                ->where('cdecan','>','0')
                ->where(function ($query) use ($vendedor) {
                      if($vendedor !='Todos'){
                         $query->Where('cpe_cabecera.IdUsuario_ven',$vendedor);
                      }
                })
                ->where(function ($query) {
                  $query->where('cpe_cabecera.tdocod','01')
                    ->orWhere('cpe_cabecera.tdocod','03')
                    ->orWhere('cpe_cabecera.tdocod','13')
                    ->orWhere('cpe_cabecera.tdocod','14')
                    ->orWhere('cpe_cabecera.tdocod','07')
                    ;
                  })   
                ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                })
                ->where(function ($query2) use ($cliente) {
                    if($cliente !='Todos'){
                         $query2->Where('cpe_cabecera.clicod',$cliente);
                    }
                }) 
                ->orderby('cdedes','desc')
                ->groupby('procod','cdedes')
                ->get();


                     $productosnot = cpe_cabecera::select(DB::RAW('sum(cpe_detalle.cdecan*cpe_det_factor) as cantidad'),'cdedes',DB::RAW('sum(cpe_detalle.cdevve) as precio'),'cpe_detalle.procod','umecod')
                ->leftjoin('cpe_detalle','cpe_detalle.IdCpe_cabecera','cpe_cabecera.IdCpe_cabecera')
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                ->whereNull('cpe_cabecera.ccabaj')
                ->where('cdecan','>','0')
                ->where(function ($query) use ($vendedor) {
                      if($vendedor !='Todos'){
                         $query->Where('cpe_cabecera.IdUsuario_ven',$vendedor);
                      }
                })
                ->where(function ($query) {
                  $query->Where('cpe_cabecera.tdocod','07')
                    ;
                  })   
                ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                })
                ->where(function ($query2) use ($cliente) {
                    if($cliente !='Todos'){
                         $query2->Where('cpe_cabecera.clicod',$cliente);
                      }
                }) 
                ->orderby('cdedes','desc')
                ->groupby('procod')
                ->get();

                $productosnotas = $productosnot->sum('cantidad');

                $totalproductos = $productos->sum('cantidad');

                $totalmontoproductos =0;
               
                foreach ($productos as $pro) {
                   $totalmontoproductos = $totalmontoproductos + ($pro->precio);
                }


                 /*---------------------------------------------------------------------------------------------------------------------------------*/

               

              


                $vista = view('empresas.reportes.ventas_detalladas',compact('totalmontoproductos','totalnotas','total','cabecera','comprobantes','fecin','fecfin','empresa','sucursal','almacen','productos','productosnotas','data_sucursal','dato_vendedor','dato_cliente'))->render();

                    if($request->ajax()){
                 return response()->json(['vista'=>$vista]);

              }


            break;

                  case 3:

                  $facturas = cpe_cabecera::select('ccacodsun','cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','tdides as documentoidentidad','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','monnom as moneda','ccaitv as total','cpe_cabecera.ccabaj as baja','ccaigv as igv','ccatvg as gravado','ccandi as numerodocumento','ccasunrescod','cpe_cabecera.tdocod','cpe_cabecera.ccatexo as exonerado','cpe_cabecera.icbper')
                    ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                    ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                    ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                    ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                    ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
                    ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                
                ->where(function ($query6) use ($almacen) {
                  if($almacen !=0 && !empty($almacen)){
                      $query6->where('cpe_cabecera.id_almacen',$almacen);
                  }                
                }) 
                    ->where('cpe_cabecera.ccafem','>=',$fecin)
                    ->where('cpe_cabecera.ccafem','<=',$fecfin)
                    ->where(function ($query) {
                    $query->where('cpe_cabecera.tdocod','01');
                    })    
                ->orderBy('IdCpe_cabecera','desc')->get();


                $boletas = cpe_cabecera::select('ccacodsun','cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','tdides as documentoidentidad','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','monnom as moneda','ccaitv as total','cpe_cabecera.ccabaj as baja','ccaigv as igv','ccatvg as gravado','ccandi as numerodocumento','ccasunrescod','cpe_cabecera.tdocod','cpe_cabecera.ccatexo as exonerado','cpe_cabecera.icbper')
                    ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                    ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                    ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                    ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                    ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
                    ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                
                ->where(function ($query6) use ($almacen) {
                  if($almacen !=0 && !empty($almacen)){
                      $query6->where('cpe_cabecera.id_almacen',$almacen);
                  }                
                }) 
                    ->where('cpe_cabecera.ccafem','>=',$fecin)
                    ->where('cpe_cabecera.ccafem','<=',$fecfin)
                    ->where(function ($query) {
                    $query->Where('cpe_cabecera.tdocod','03');
                    })    
                ->orderBy('IdCpe_cabecera','desc')->get();

                  $notascreditos = cpe_cabecera::select('ccacodsun','cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','tdides as documentoidentidad','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','monnom as moneda','ccaitv as total','cpe_cabecera.ccabaj as baja','ccaigv as igv','ccatvg as gravado','ccandi as numerodocumento','ccasunrescod','cpe_cabecera.tdocod','cpe_cabecera.ccatexo as exonerado','cpe_cabecera.icbper')
                    ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                    ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                    ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                    ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                    ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
                    ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                
                ->where(function ($query6) use ($almacen) {
                  if($almacen !=0 && !empty($almacen)){
                      $query6->where('cpe_cabecera.id_almacen',$almacen);
                  }                
                }) 
                    ->where('cpe_cabecera.ccafem','>=',$fecin)
                    ->where('cpe_cabecera.ccafem','<=',$fecfin)
                    ->where(function ($query) {
                    $query->Where('cpe_cabecera.tdocod','07');
                    })    
                ->orderBy('IdCpe_cabecera','desc')->get();




              $fac_grav = $facturas->sum('gravado');
              $fac_igv= $facturas->sum('igv');
              $fac_exo = $facturas->sum('exonerado');
              $fac_icb= $facturas->sum('icbper');
              $fac_tot = $facturas->sum('total');


              $bol_grav = $boletas->sum('gravado');
              $bol_igv= $boletas->sum('igv');
              $bol_exo = $boletas->sum('exonerado');
              $bol_icb= $boletas->sum('icbper');
              $bol_tot = $boletas->sum('total');

              $notc_grav = $notascreditos->sum('gravado');
              $notc_igv= $notascreditos->sum('igv');
              $notc_exo = $notascreditos->sum('exonerado');
              $notc_icb= $notascreditos->sum('icbper');
              $notc_tot = $notascreditos->sum('total');

    
   
              $hora_rep = now()->format('Y-m-d H:i:s');

                $vista = view('empresas.reportes.ventas_sunat',compact('hora_rep','fecin','fecfin','facturas','boletas','notascreditos','fac_icb','fac_grav','fac_igv','fac_exo','fac_tot','bol_grav','bol_igv','bol_exo','bol_tot','bol_icb','notc_grav','notc_igv','notc_exo','notc_tot','notc_icb'))->render();

                if($request->ajax()){
                  return response()->json(['vista'=>$vista]);
                }


            break;

              case 60:

                   $comprobantes = cpe_cabecera::select('cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','cdepuni as precio','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','det.cdecan as cantidad','det.cdedes as producto','monnom as moneda','det.cdevve as total','productos.costofijo as costo','cpe_cabecera.ccabaj as baja','ccasunrescod','det.flete')
                ->leftjoin('cpe_detalle as det','cpe_cabecera.IdCpe_cabecera','det.IdCpe_cabecera')
                ->leftjoin('productos','productos.IdProducto','det.IdProducto')
                ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
                ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                
                ->where(function ($query6) use ($almacen) {
                  if($almacen !=0 && !empty($almacen)){
                      $query6->where('cpe_cabecera.id_almacen',$almacen);
                  }                
                }) 
                ->where(function ($query1){
          
                    $query1->whereNull('cpe_cabecera.ccabaj')
                          ->orwhere('cpe_cabecera.ccabaj','');
                    
                })
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin) 
                  ->where(function ($query) {
                $query->where('cpe_cabecera.tdocod','01')
                    ->orWhere('cpe_cabecera.tdocod','03')
                    ->orWhere('cpe_cabecera.tdocod','13');
                })                
                ->orderBy('IdCpe_cabecera','desc')->get();


                $totalventas = cpe_cabecera::select('cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','cdepuni as precio','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','det.cdecan as cantidad','det.cdedes as producto','monnom as moneda','det.cdevve as total','productos.costofijo as costo','cpe_cabecera.ccabaj as baja','ccasunrescod')
                ->leftjoin('cpe_detalle as det','cpe_cabecera.IdCpe_cabecera','det.IdCpe_cabecera')
                 ->leftjoin('productos','productos.IdProducto','det.IdProducto')
                ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
               ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                
                ->where(function ($query6) use ($almacen) {
                  if($almacen !=0 && !empty($almacen)){
                      $query6->where('cpe_cabecera.id_almacen',$almacen);
                  }                
                }) 
                ->where(function ($query1){
          
                    $query1->whereNull('cpe_cabecera.ccabaj')
                          ->orwhere('cpe_cabecera.ccabaj','');
                    
                })
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin) 
                  ->where(function ($query) {
                $query->where('cpe_cabecera.tdocod','01')
                    ->orWhere('cpe_cabecera.tdocod','03')
                    ->orWhere('cpe_cabecera.tdocod','13');
                })                
                ->sum('cdevve');

                 $totalcosto = cpe_cabecera::select('cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','cdepuni as precio','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','det.cdecan as cantidad','det.cdedes as producto','monnom as moneda','det.cdevve as total','productos.costofijo as costo','cpe_cabecera.ccabaj as baja','ccasunrescod','det.flete')
                ->leftjoin('cpe_detalle as det','cpe_cabecera.IdCpe_cabecera','det.IdCpe_cabecera')
                 ->leftjoin('productos','productos.IdProducto','det.IdProducto')
                ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
               ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                
                ->where(function ($query6) use ($almacen) {
                  if($almacen !=0 && !empty($almacen)){
                      $query6->where('cpe_cabecera.id_almacen',$almacen);
                  }                
                }) 
                ->where(function ($query1){
          
                    $query1->whereNull('cpe_cabecera.ccabaj')
                          ->orwhere('cpe_cabecera.ccabaj','');
                    
                })
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin) 
                  ->where(function ($query) {
                $query->where('cpe_cabecera.tdocod','01')
                    ->orWhere('cpe_cabecera.tdocod','03')
                    ->orWhere('cpe_cabecera.tdocod','13');
                })                
                ->sum(DB::RAW('det.cdecan*(det.costo+det.flete)'));

               

                $vista = view('empresas.reportes.rentabilidadcostofijo',compact('totalventas','totalcosto','comprobantes','fecin','fecfin'))->render();


                    if($request->ajax()){
                 return response()->json(['vista'=>$vista]);

              }


            break;


            case 61:

                if($vendedor!='Todos'){
                $dato_vendedor = DB::tABLE('users')->where('IdUsuario',$vendedor)->first();
                }

                if($cliente!='Todos'){
                  $dato_cliente = DB::tABLE('cliente')->where('clicod',$cliente)->first();
                }

                $comprobantes = cpe_cabecera::select('cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','monnom as moneda','cpe_cabecera.ccabaj as baja','ccasunrescod','tdides as documentoidentidad','ccandi as numerodocumento','users.name','users.apeusu',DB::RAW('sum(ccaitv) as total'))
                ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
                ->leftjoin('users','users.IdUsuario','cpe_cabecera.IdUsuario_ven')
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                 ->where(function ($query) use ($vendedor) {
                      if($vendedor !='Todos'){
                         $query->Where('cpe_cabecera.IdUsuario_ven',$vendedor);
                      }
                    })
                ->where(function ($query) {
                  $query->where('cpe_cabecera.tdocod','01')
                    ->orWhere('cpe_cabecera.tdocod','03')
                    ->orWhere('cpe_cabecera.tdocod','13')
                    ->orWhere('cpe_cabecera.tdocod','14')
                    //->orWhere('cpe_cabecera.tdocod','07')
                    ;
                  }) 
                  ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                   ->where(function ($query2) use ($cliente) {
                      if($cliente !='Todos'){
                         $query2->Where('cpe_cabecera.clicod',$cliente);
                      }
                    })
                
               /* ->where(function ($query6) use ($idalmacen) {
                  if($idalmacen !='Todos' && !empty($idalmacen)){
                      $query6->where('det.id_almacen_pro',$idalmacen);
                  }                
                })   */ 
                 ->groupby('IdUsuario_ven')
                 ->orderby('name')->get();



                 $total = $comprobantes->sum('total');

                $vista = view('empresas.reportes.resumenventasvendedor', compact('comprobantes','dato_vendedor','fecin','fecfin','empresa','sucursal','almacen','data_sucursal','dato_cliente','total'))->render();

              if($request->ajax()){
                 return response()->json(['vista'=>$vista]);

              }


            break;


             case 4:

                   $comprobantes = cpe_cabecera::select('cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','cdepuni as precio','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','det.cdecan as cantidad','det.cdedes as producto','monnom as moneda','det.cdevve as total','det.costo as costo','cpe_cabecera.ccabaj as baja','ccasunrescod','det.flete')
                ->leftjoin('cpe_detalle as det','cpe_cabecera.IdCpe_cabecera','det.IdCpe_cabecera')
                ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
                ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                
                ->where(function ($query6) use ($almacen) {
                  if($almacen !=0 && !empty($almacen)){
                      $query6->where('cpe_cabecera.id_almacen',$almacen);
                  }                
                }) 
                ->where(function ($query1){
          
                    $query1->whereNull('cpe_cabecera.ccabaj')
                          ->orwhere('cpe_cabecera.ccabaj','');
                    
                })
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin) 
                  ->where(function ($query) {
                $query->where('cpe_cabecera.tdocod','01')
                    ->orWhere('cpe_cabecera.tdocod','03')
                    ->orWhere('cpe_cabecera.tdocod','13');
                })                
                ->orderBy('IdCpe_cabecera','desc')->get();


                $totalventas = cpe_cabecera::select('cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','cdepuni as precio','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','det.cdecan as cantidad','det.cdedes as producto','monnom as moneda','det.cdevve as total','det.costo as costo','cpe_cabecera.ccabaj as baja','ccasunrescod')
                ->leftjoin('cpe_detalle as det','cpe_cabecera.IdCpe_cabecera','det.IdCpe_cabecera')
                ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
               ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                
                ->where(function ($query6) use ($almacen) {
                  if($almacen !=0 && !empty($almacen)){
                      $query6->where('cpe_cabecera.id_almacen',$almacen);
                  }                
                }) 
                ->where(function ($query1){
          
                    $query1->whereNull('cpe_cabecera.ccabaj')
                          ->orwhere('cpe_cabecera.ccabaj','');
                    
                })
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin) 
                  ->where(function ($query) {
                $query->where('cpe_cabecera.tdocod','01')
                    ->orWhere('cpe_cabecera.tdocod','03')
                    ->orWhere('cpe_cabecera.tdocod','13');
                })                
                ->sum('cdevve');

                 $totalcosto = cpe_cabecera::select('cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','cdepuni as precio','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','det.cdecan as cantidad','det.cdedes as producto','monnom as moneda','det.cdevve as total','det.costo as costo','cpe_cabecera.ccabaj as baja','ccasunrescod','det.flete')
                ->leftjoin('cpe_detalle as det','cpe_cabecera.IdCpe_cabecera','det.IdCpe_cabecera')
                ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
               ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                
                ->where(function ($query6) use ($almacen) {
                  if($almacen !=0 && !empty($almacen)){
                      $query6->where('cpe_cabecera.id_almacen',$almacen);
                  }                
                }) 
                ->where(function ($query1){
          
                    $query1->whereNull('cpe_cabecera.ccabaj')
                          ->orwhere('cpe_cabecera.ccabaj','');
                    
                })
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin) 
                  ->where(function ($query) {
                $query->where('cpe_cabecera.tdocod','01')
                    ->orWhere('cpe_cabecera.tdocod','03')
                    ->orWhere('cpe_cabecera.tdocod','13');
                })                
                ->sum(DB::RAW('det.cdecan*(det.costo+det.flete)'));

               

                $vista = view('empresas.reportes.rentabilidad',compact('totalventas','totalcosto','comprobantes','fecin','fecfin'))->render();


                    if($request->ajax()){
                 return response()->json(['vista'=>$vista]);

              }


            break;

            case 5:



              
              /*  $registros = DB::tABLE('cpe_detalle')->get();
              foreach ($registros as $reg) {
                DB::tABLE('cpe_detalle')->where('IdCpe_detalle',$reg->IdCpe_detalle)->update(['procod'=>trim($reg->procod)]);
              }*/


              $empresa = DB::tABLE('empresa')->where('IdEmpresa',Auth::user()->IdEmpresa)->first();

             // $sucursal = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first();
             // $datocli = DB::tABLE('cliente')->where('clicod',$clicod)->first();

                $productos = cpe_cabecera::select(DB::RAW('sum(cpe_detalle.cdecan*cpe_det_factor) as cantidad'),'cdedes',DB::RAW('sum(cpe_detalle.cdevve) as precio'),'cpe_detalle.procod','umecod')
                ->leftjoin('cpe_detalle','cpe_detalle.IdCpe_cabecera','cpe_cabecera.IdCpe_cabecera')
 
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                ->whereNull('cpe_cabecera.ccabaj')
                ->where('cdecan','>','0')
                 ->where(function ($query) {
                  $query->Where('cpe_cabecera.tdocod','03')
                        ->orWhere('cpe_cabecera.tdocod','01')
                        ->orWhere('cpe_cabecera.tdocod','13');
                      
                  })
               
                ->where(function ($query5) use ($sucursal) {
                  if($sucursal !='Todos'){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                
                ->where(function ($query6) use ($almacen) {
                  if($almacen !='Todos'){
                      $query6->where('cpe_detalle.id_almacen_pro',$almacen);
                  }                
                }) 
                ->orderby('cdedes','desc')
                ->groupby('procod')
                ->get();

                $totalproductos = $productos->sum('cantidad');

                $total =0;
                foreach ($productos as $pro) {
                   $total = $total + ($pro->precio);
                }


                $vista = view('empresas.reportes.productos', compact('productos','fecin','fecfin','total','totalproductos','total','empresa','sucursal','data_sucursal'))->render();


              if($request->ajax()){
                 return response()->json(['vista'=>$vista]);

              }

                 
              break;

               case 6:


                $comprobantes = cpe_cabecera::select('cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','det.cdedes as producto','det.cdecan as cantidad','cdepuni as precio','monnom as moneda','det.cdevve as total','cpe_cabecera.ccabaj as baja','det.costo','ccasunrescod','tdides as documentoidentidad','ccandi as numerodocumento','IdProducto','cpe_det_factor')
                ->leftjoin('cpe_detalle as det','cpe_cabecera.IdCpe_cabecera','det.IdCpe_cabecera')
                ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
                 ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                
                ->where(function ($query6) use ($almacen) {
                  if($almacen !='Todos'){
                      $query6->where('det.id_almacen_pro',$almacen);
                  }                
                }) 
                
                
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
               ->whereNull('cpe_cabecera.ccabaj')
                ->where('cdecan','>','0')
                 ->where(function ($query) {
                  $query->Where('cpe_cabecera.tdocod','03')
                        ->orWhere('cpe_cabecera.tdocod','01')
                        ->orWhere('cpe_cabecera.tdocod','13');
                      
                  })
               
                ->where(function ($query4) use ($pro) {

                if($pro !=0){
                   $query4->where('det.IdProducto',$pro)
                    ->orWhere('det.IdProducto_rel',$pro);
                }
               

                })    
                 ->orderBy('IdCpe_cabecera','desc')->get();

                $total = $comprobantes->sum('total');


                $vista = view('empresas.reportes.ventas_productos',compact('fecin','fecfin','comprobantes','total','dat_ven','dat_cli'))->render();

                    if($request->ajax()){
                 return response()->json(['vista'=>$vista]);

              }


            break;

             case 7:

                $comprobantes = cpe_cabecera::select('ccaitv','cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','monnom as moneda','ccaitv as total','cpe_cabecera.ccabaj as baja','ccasunrescod','tdides as documentoidentidad','ccandi as numerodocumento','cpe_cabecera.tdocod')
                ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
                 ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                
                ->where(function ($query6) use ($almacen) {
                  if($almacen !=0 && !empty($almacen)){
                      $query6->where('cpe_cabecera.id_almacen',$almacen);
                  }                
                }) 
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                ->where(function ($query){
                    $query->Where('cpe_cabecera.tdocod','15');
                })
                ->where(function ($query1) use ($vendedor) {
                      if($vendedor !='Todos'){
                         $query1->Where('cpe_cabecera.IdUsuario_ven',$vendedor);
                      }
                    })
                 ->where(function ($query2) use ($cliente) {
                      if($cliente !='Todos'){
                         $query2->Where('cpe_cabecera.clicod',$cliente);
                      }
                    })
                ->orderBy('IdCpe_cabecera','desc')->get();

            //   dd($comprobantes);

                  $total = $comprobantes->sum('ccaitv');

                $vista = view('empresas.reportes.proformas',compact('dat_cli','fecin','fecfin','comprobantes','total','dat_ven'))->render();

              if($request->ajax()){
                 return response()->json(['vista'=>$vista]);

              }


            break;

             case 8:

                $comprobantes = cpe_cabecera::select('ccaitv','cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','monnom as moneda','ccaitv as total','cpe_cabecera.ccabaj as baja','ccasunrescod','tdides as documentoidentidad','ccandi as numerodocumento','cpe_cabecera.tdocod')
                ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
                 ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                
                ->where(function ($query6) use ($almacen) {
                  if($almacen !=0 && !empty($almacen)){
                      $query6->where('cpe_cabecera.id_almacen',$almacen);
                  }                
                }) 
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                ->where(function ($query){
                    $query->Where('cpe_cabecera.tdocod','16');
                })
                ->where(function ($query1) use ($vendedor) {
                      if($vendedor !='Todos'){
                         $query1->Where('cpe_cabecera.IdUsuario_ven',$vendedor);
                      }
                    })
                 ->where(function ($query2) use ($cliente) {
                      if($cliente !='Todos'){
                         $query2->Where('cpe_cabecera.clicod',$cliente);
                      }
                    })
                ->orderBy('IdCpe_cabecera','desc')->get();

            //   dd($comprobantes);

                  $total = $comprobantes->sum('ccaitv');

                $vista = view('empresas.reportes.pedidos',compact('dat_cli','fecin','fecfin','comprobantes','total','dat_ven'))->render();

              if($request->ajax()){
                 return response()->json(['vista'=>$vista]);

              }


            break;

                case 9:

                
              
            
                 $comprobantes = cpe_cabecera::select(DB::RAW('sum(det.cdevve) as TOTAL'),DB::RAW('sum(det.cdecan*factor) as CANTIDAD'),'productos.pronom',DB::RAW('sum(det.cdevve*(det.comision/100)) as COMISION'))
                ->leftjoin('cpe_detalle as det','cpe_cabecera.IdCpe_cabecera','det.IdCpe_cabecera')
                ->leftjoin('productos','productos.IdProducto','det.IdProducto_rel')
                ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
               ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                
                ->where(function ($query6) use ($almacen) {
                  if($almacen !=0 && !empty($almacen)){
                      $query6->where('cpe_cabecera.id_almacen',$almacen);
                  }                
                }) 
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                ->where(function ($query) use ($vendedor) {
                      if($vendedor !='Todos'){
                         $query->Where('cpe_cabecera.IdUsuario_ven',$vendedor);
                      }
                    })
                   ->where(function ($query2) use ($pro) {
                      if($pro !=0){
                         $query2->Where('IdProducto_rel',$pro);
                      }
                    })
                ->where(function ($query1){
          
                    $query1->whereNull('cpe_cabecera.ccabaj')
                          ->orwhere('cpe_cabecera.ccabaj','');
                    
                })
                ->where(function ($query3) {
                $query3->where('cpe_cabecera.tdocod','01')
                    ->orWhere('cpe_cabecera.tdocod','03')
                    ->orWhere('cpe_cabecera.tdocod','13');
                })   
                ->groupby('IdProducto_rel') 
                 ->orderBy('cpe_cabecera.IdCpe_cabecera','desc')->get();

                 //dd($comprobantes);

                  $total = $comprobantes->sum('COMISION');

                    $vista = view('empresas.reportes.comision_vendedor',compact('fecin','fecfin','comprobantes','total','dat_ven','dat_cli'))->render();


                    if($request->ajax()){
                 return response()->json(['vista'=>$vista]);

              }


            break;

                 case 10:

                $comprobantes = cpe_cabecera::select(DB::RAW('sum(det.cdevve) as TOTAL'),DB::RAW('sum(det.cdecan*factor) as CANTIDAD'),'productos.pronom',DB::RAW('sum(det.cdevve*(det.comision/100)) as COMISION'))
                ->leftjoin('cpe_detalle as det','cpe_cabecera.IdCpe_cabecera','det.IdCpe_cabecera')
                ->leftjoin('productos','productos.IdProducto','det.IdProducto_rel')
                ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
               ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                
                ->where(function ($query6) use ($almacen) {
                  if($almacen !=0 && !empty($almacen)){
                      $query6->where('det.id_almacen_pro',$almacen);
                  }                
                }) 
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                ->where(function ($query) use ($vendedor) {
                      if($vendedor !='0'){
                         $query->Where('cpe_cabecera.IdUsuario_ven',$vendedor);
                      }
                    })
             
                ->where(function ($query1){
          
                    $query1->whereNull('cpe_cabecera.ccabaj')
                          ->orwhere('cpe_cabecera.ccabaj','');
                    
                })
                ->where(function ($query3) {
                $query3->where('cpe_cabecera.tdocod','01')
                    ->orWhere('cpe_cabecera.tdocod','03')
                    ->orWhere('cpe_cabecera.tdocod','13');
                })   
                ->groupby('IdProducto_rel') 
                 ->orderBy('cpe_cabecera.IdCpe_cabecera','desc')->get();

                 //dd($comprobantes);

                  $total = $comprobantes->sum('COMISION');


                $vista = view('empresas.reportes.comision_vendedor',compact('fecin','fecfin','comprobantes','total','dat_ven','dat_cli'))->render();

                    if($request->ajax()){
                 return response()->json(['vista'=>$vista]);

              }


            break;


                case 11:

                 $comprobantes = DB::tABLE('compras_cabecera')
                  ->leftjoin('proveedor','compras_cabecera.prov_id','proveedor.prov_id')
                  ->join('moneda','compras_cabecera.mon_id','moneda.moncod')
                  ->join('tipo_documento','compras_cabecera.tdocod','tipo_documento.tdocod')
                  
                    ->where('compras_cabecera.com_fec','>=',$fecin)
                    ->where('compras_cabecera.com_fec','<=',$fecfin)
                  ->where('est_compra','Registrado')
                    ->where(function ($query) use ($sucursal){
                      if(!$sucursal !=0 && !empty($sucursal)){
                          $query->where('compras_cabecera.id_empresa_negocio',$sucursal);
                      }
                     
                  })
                  ->where(function ($query1) use ($proveedor){
                      if($proveedor !=0){
                          $query1->where('compras_cabecera.prov_id',$proveedor);
                      }
                      
                  })
                        
                  ->where(function ($query6) use ($almacen) {
                      if($almacen !=0 && !empty($almacen)){
                          $query6->where('compras_cabecera.id_almacen',$almacen);
                      }                
                    }) 
                
                  ->where('compras_cabecera.tdocod','!=','80')
                  ->orderby('com_cab_id','desc')
                  ->get();
              //   dd($comprobantes);

                  $total = $comprobantes->sum('total_com');


                $vista = view('empresas.reportes.compras',compact('fecin','fecfin','comprobantes','total','dat_ven','dat_cli'))->render();

                    if($request->ajax()){
                 return response()->json(['vista'=>$vista]);

              }


            break;

              case 12:

              $comprobantes = DB::tABLE('compras_cabecera')
              ->leftjoin('compras_detalle','compras_detalle.com_cab_id','compras_cabecera.com_cab_id')
                 ->leftjoin('productos','productos.IdProducto','compras_detalle.pro_id')
                  ->leftjoin('proveedor','compras_cabecera.prov_id','proveedor.prov_id')
                  ->leftjoin('unidad_medida','unidad_medida.umecod','compras_detalle.ume_cod')
                  ->join('moneda','compras_cabecera.mon_id','moneda.moncod')
                  ->join('tipo_documento','compras_cabecera.tdocod','tipo_documento.tdocod')
              ->where('est_compra','Registrado')
                ->where('compras_cabecera.com_fec','>=',$fecin)
                ->where('compras_cabecera.com_fec','<=',$fecfin)
                  ->where(function ($query) use ($sucursal){
                      if(!$sucursal !=0 && !empty($sucursal)){
                          $query->where('compras_cabecera.id_empresa_negocio',$sucursal);
                      }
                     
                  })
                  ->where(function ($query1) use ($proveedor){
                      if($proveedor !=0){
                          $query1->where('compras_cabecera.prov_id',$proveedor);
                      }
                      
                  })
                        
                  ->where(function ($query6) use ($almacen) {
                      if($almacen !=0 && !empty($almacen)){
                          $query6->where('compras_cabecera.id_almacen',$almacen);
                      }                
                    }) 
              ->where('compras_cabecera.tdocod','!=','80')
              ->orderby('compras_cabecera.com_cab_id','desc')
              ->get();
                 //dd($comprobantes);

                  $total = $comprobantes->sum('total_com');


                $vista = view('empresas.reportes.compras_detalladas',compact('fecin','fecfin','comprobantes','total','dat_ven','dat_cli'))->render();

                    if($request->ajax()){
                 return response()->json(['vista'=>$vista]);

              }


            break;

              case 13:

              $comprobantes = DB::tABLE('compras_cabecera')
              ->leftjoin('compras_detalle','compras_detalle.com_cab_id','compras_cabecera.com_cab_id')
                 ->leftjoin('productos','productos.IdProducto','compras_detalle.pro_id')
                  ->leftjoin('proveedor','compras_cabecera.prov_id','proveedor.prov_id')
                  ->leftjoin('unidad_medida','unidad_medida.umecod','compras_detalle.ume_cod')
                  ->join('moneda','compras_cabecera.mon_id','moneda.moncod')
                  ->join('tipo_documento','compras_cabecera.tdocod','tipo_documento.tdocod')
              ->where('est_compra','Registrado')
                ->where('compras_cabecera.com_fec','>=',$fecin)
                ->where('compras_cabecera.com_fec','<=',$fecfin)
                  ->where(function ($query) use ($sucursal){
                      if(!$sucursal !=0 && !empty($sucursal)){
                          $query->where('compras_cabecera.id_empresa_negocio',$sucursal);
                      }
                     
                  })
                  ->where(function ($query1) use ($proveedor){
                      if($proveedor !=0){
                          $query1->where('compras_cabecera.prov_id',$proveedor);
                      }
                      
                  })

                   ->where(function ($query8) use ($pro){
                      if($pro !=0){
                          $query8->where('compras_detalle.IdProducto_rel',$pro);
                      }
                      
                  })
                        
                  ->where(function ($query6) use ($almacen) {
                      if($almacen !=0 && !empty($almacen)){
                          $query6->where('compras_cabecera.id_almacen',$almacen);
                      }                
                    }) 
              ->where('compras_cabecera.tdocod','!=','80')
              ->orderby('compras_cabecera.com_cab_id','desc')
              ->get();
                 //dd($comprobantes);

                  $total = $comprobantes->sum('total_com');


                $vista = view('empresas.reportes.compras_detalladas',compact('fecin','fecfin','comprobantes','total','dat_ven','dat_cli'))->render();

                    if($request->ajax()){
                 return response()->json(['vista'=>$vista]);

              }

              break;

               case 14:

                $comprobantes = cpe_cabecera::select('ccaitv','cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','monnom as moneda','ccaitv as total','cpe_cabecera.ccabaj as baja','ccasunrescod','tdides as documentoidentidad','ccandi as numerodocumento','cpe_cabecera.tdocod','pedidos.fecha_hora','pedidos.fecha_salida','pedidos.placa',DB::raw("(SELECT CONCAT(name,apeusu) FROM users WHERE IdUsuario= pedidos.IdUsuario) as usuario_registra"),DB::raw("(SELECT CONCAT(name,apeusu) FROM users WHERE IdUsuario= pedidos.IdUsuarioCob) as usuario_cobra"))
                ->join('pedidos','pedidos.ped_id','cpe_cabecera.ped_id')
                ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
                 ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                
                ->where(function ($query6) use ($almacen) {
                  if($almacen !=0 && !empty($almacen)){
                      $query6->where('cpe_cabecera.id_almacen',$almacen);
                  }                
                }) 
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                ->where(function ($query){
                    $query->where('cpe_cabecera.tdocod','01')
                          ->orwhere('cpe_cabecera.tdocod','03')
                          ->orWhere('cpe_cabecera.tdocod','13');
                })
                ->where(function ($query1) use ($vendedor) {
                      if($vendedor !='Todos'){
                         $query1->Where('cpe_cabecera.IdUsuario_ven',$vendedor);
                      }
                    })
                 ->where(function ($query2) use ($cliente) {
                      if($cliente !='Todos'){
                         $query2->Where('cpe_cabecera.clicod',$cliente);
                      }
                    })
                ->orderBy('IdCpe_cabecera','desc')->get();

            //   dd($comprobantes);

                  $total = $comprobantes->sum('ccaitv');

                $vista = view('empresas.reportes.ventas_ingreso_vehiculos',compact('dat_cli','fecin','fecfin','comprobantes','total','dat_ven'))->render();

              if($request->ajax()){
                 return response()->json(['vista'=>$vista]);

              }


            break;

              case 15:

                $comprobantes = cpe_cabecera::select('ccaitv','cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','monnom as moneda','ccaitv as total','cpe_cabecera.ccabaj as baja','ccasunrescod','tdides as documentoidentidad','ccandi as numerodocumento','cpe_cabecera.tdocod','pedidos.fecha_hora','pedidos.fecha_salida','pedidos.placa',DB::raw("(SELECT CONCAT(name,apeusu) FROM users WHERE IdUsuario= pedidos.IdUsuario) as usuario_registra"),DB::raw("(SELECT CONCAT(name,apeusu) FROM users WHERE IdUsuario= pedidos.IdUsuarioCob) as usuario_cobra"))
                ->join('pedidos','pedidos.ped_id','cpe_cabecera.ped_id')
                ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
                 ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                
                ->where(function ($query6) use ($almacen) {
                  if($almacen !=0 && !empty($almacen)){
                      $query6->where('cpe_cabecera.id_almacen',$almacen);
                  }                
                }) 
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                ->where(function ($query){
                    $query->Where('cpe_cabecera.tdocod','50');
                })
                ->where(function ($query1) use ($vendedor) {
                      if($vendedor !='Todos'){
                         $query1->Where('cpe_cabecera.IdUsuario_ven',$vendedor);
                      }
                    })
                 ->where(function ($query2) use ($cliente) {
                      if($cliente !='Todos'){
                         $query2->Where('cpe_cabecera.clicod',$cliente);
                      }
                    })
                ->orderBy('IdCpe_cabecera','desc')->get();

            //   dd($comprobantes);

                  $total = $comprobantes->sum('ccaitv');

                $vista = view('empresas.reportes.ventas_ingreso_vehiculos',compact('dat_cli','fecin','fecfin','comprobantes','total','dat_ven'))->render();

              if($request->ajax()){
                 return response()->json(['vista'=>$vista]);

              }


            break;

               case 16:

                $comprobantes = cpe_cabecera::select('ccaitv','cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','monnom as moneda','ccaitv as total','cpe_cabecera.ccabaj as baja','ccasunrescod','tdides as documentoidentidad','ccandi as numerodocumento','cpe_cabecera.tdocod','pedidos.fecha_hora','pedidos.fecha_salida','pedidos.placa',DB::raw("(SELECT CONCAT(name,apeusu) FROM users WHERE IdUsuario= pedidos.IdUsuario) as usuario_registra"),DB::raw("(SELECT CONCAT(name,apeusu) FROM users WHERE IdUsuario= pedidos.IdUsuarioCob) as usuario_cobra"))
                ->join('pedidos','pedidos.ped_id','cpe_cabecera.ped_id')
                ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
                 ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                
                ->where(function ($query6) use ($almacen) {
                  if($almacen !=0 && !empty($almacen)){
                      $query6->where('cpe_cabecera.id_almacen',$almacen);
                  }                
                }) 
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                ->where(function ($query){
                    $query->where('cpe_cabecera.tdocod','01')
                          ->orwhere('cpe_cabecera.tdocod','03')
                          ->orWhere('cpe_cabecera.tdocod','13')
                           ->orWhere('cpe_cabecera.tdocod','50');
                })
                ->where(function ($query1) use ($vendedor) {
                      if($vendedor !='Todos'){
                         $query1->Where('cpe_cabecera.IdUsuario_ven',$vendedor);
                      }
                    })
                 ->where(function ($query2) use ($cliente) {
                      if($cliente !='Todos'){
                         $query2->Where('cpe_cabecera.clicod',$cliente);
                      }
                    })
                ->orderBy('IdCpe_cabecera','desc')->get();

            //   dd($comprobantes);

                  $total = $comprobantes->sum('ccaitv');

                $vista = view('empresas.reportes.ventas_ingreso_vehiculos',compact('dat_cli','fecin','fecfin','comprobantes','total','dat_ven'))->render();

              if($request->ajax()){
                 return response()->json(['vista'=>$vista]);

              }


            break;

            case 17:

              $comprobantes = cpe_cabecera::select('ccaitv','cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','monnom as moneda','ccaitv as total','cpe_cabecera.ccabaj as baja','ccasunrescod','tdides as documentoidentidad','ccandi as numerodocumento','cpe_cabecera.tdocod','ccatexo','ccatvg','ccatvi')
                ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
                 ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                
                ->where(function ($query6) use ($almacen) {
                  if($almacen !=0 && !empty($almacen)){
                      $query6->where('cpe_cabecera.id_almacen',$almacen);
                  }                
                }) 
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                ->where(function ($query){
                    $query->where('cpe_cabecera.tdocod','01')
                          ->orwhere('cpe_cabecera.tdocod','03')
                          ->orWhere('cpe_cabecera.tdocod','13')
                          ->orWhere('cpe_cabecera.tdocod','07');
                })
                ->whereNull('ccabaj')
                ->orderBy('IdCpe_cabecera','desc')->get();

            //   dd($comprobantes);

                  $total = $comprobantes->sum('ccaitv');

                  $totalexoneradasfacturas = $comprobantes->where('tdocod','01')->sum('ccatexo');
                  $totalgravadasfacturas = $comprobantes->where('tdocod','01')->sum('ccatvg');

                  $totalexoneradasboletas = $comprobantes->where('tdocod','03')->sum('ccatexo');
                  $totalgravadasboletas = $comprobantes->where('tdocod','03')->sum('ccatvg');

                   $totalexoneradasnotas = $comprobantes->where('tdocod','07')->sum('ccatexo');
                  $totalgravadasnotas = $comprobantes->where('tdocod','07')->sum('ccatvg');


            

                 $sucursal = EmpresaNegocios::FindOrFail($request->get('sucursal'));

                 $empresa = Empresa::findOrFail($sucursal->IdEmpresa);

                
                    $vista =view('empresas.reportes.ventas_resumen',compact('fecin','fecfin','total','totalexoneradasfacturas','totalexoneradasboletas','totalgravadasfacturas','totalgravadasboletas','totalgravadasnotas','totalexoneradasnotas','empresa','sucursal'))->render();


              if($request->ajax()){
                 return response()->json(['vista'=>$vista]);

              }

              break;
                


              case 19:



                $facturas = cpe_cabecera::select('ccaitv','cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','monnom as moneda','ccaitv as total','cpe_cabecera.ccabaj as baja','ccasunrescod','tdides as documentoidentidad','ccandi as numerodocumento',
                  'cpe_cabecera.tdocod','ccafve as fechaven','cpe_cabecera.tdicod','ccatexo','ccafem_ref','serie_ref','tdocod_ref','num_ref','cod_mov')
                ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
                 ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                
                ->where(function ($query6) use ($almacen) {
                  if($almacen !='Todos'){
                      $query6->where('cpe_cabecera.id_almacen',$almacen);
                  }                
                }) 
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                ->where(function ($query){
                    $query->where('cpe_cabecera.tdocod','01');
                })
                ->where(function ($query1) use ($vendedor) {
                      if($vendedor !='Todos'){
                         $query1->Where('cpe_cabecera.IdUsuario_ven',$vendedor);
                      }
                    })
                 ->where(function ($query2) use ($cliente) {
                      if($cliente !='Todos'){
                         $query2->Where('cpe_cabecera.clicod',$cliente);
                      }
                    })
                ->orderBy('IdCpe_cabecera','desc')->get();

                 $boletas = cpe_cabecera::select('ccaitv','cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','monnom as moneda','ccaitv as total','cpe_cabecera.ccabaj as baja','ccasunrescod','tdides as documentoidentidad','ccandi as numerodocumento',
                  'cpe_cabecera.tdocod','ccafve as fechaven','cpe_cabecera.tdicod','ccatexo','ccafem_ref','serie_ref','tdocod_ref','num_ref','cod_mov')
                ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
                 ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                
                ->where(function ($query6) use ($almacen) {
                  if($almacen !='Todos'){
                      $query6->where('cpe_cabecera.id_almacen',$almacen);
                  }                
                }) 
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                ->where(function ($query){
                    $query->where('cpe_cabecera.tdocod','03');
                })
                ->where(function ($query1) use ($vendedor) {
                      if($vendedor !='Todos'){
                         $query1->Where('cpe_cabecera.IdUsuario_ven',$vendedor);
                      }
                    })
                 ->where(function ($query2) use ($cliente) {
                      if($cliente !='Todos'){
                         $query2->Where('cpe_cabecera.clicod',$cliente);
                      }
                    })
                ->orderBy('IdCpe_cabecera','desc')->get();


                 $notas = cpe_cabecera::select('ccaitv','cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','monnom as moneda','ccaitv as total','cpe_cabecera.ccabaj as baja','ccasunrescod','tdides as documentoidentidad','ccandi as numerodocumento',
                  'cpe_cabecera.tdocod','ccafve as fechaven','cpe_cabecera.tdicod','ccatexo','ccafem_ref','serie_ref','tdocod_ref','num_ref','cod_mov')
                ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
                 ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                
                ->where(function ($query6) use ($almacen) {
                  if($almacen !='Todos'){
                      $query6->where('cpe_cabecera.id_almacen',$almacen);
                  }                
                }) 
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                ->where(function ($query){
                    $query->where('cpe_cabecera.tdocod','07');
                })
                ->where(function ($query1) use ($vendedor) {
                      if($vendedor !='Todos'){
                         $query1->Where('cpe_cabecera.IdUsuario_ven',$vendedor);
                      }
                    })
                 ->where(function ($query2) use ($cliente) {
                      if($cliente !='Todos'){
                         $query2->Where('cpe_cabecera.clicod',$cliente);
                      }
                    })
                ->orderBy('IdCpe_cabecera','desc')->get();

      

                          $totalfacturas = $facturas->sum('ccaitv');
                  $totalboletas = $boletas->sum('ccaitv');
                  $totalnotas = $notas->sum('ccaitv');


                  $totalfacturasexo = $facturas->sum('ccatexo');
                  $totalboletasexo = $boletas->sum('ccatexo');
                  $totalnotasexo = $notas->sum('ccatexo');
                    

                  $totalfacturasinaf = $facturas->sum('ccatvi');
                  $totalboletasinaf = $boletas->sum('ccatvi');
                  $totalnotasinaf = $notas->sum('ccatvi');
                  

                   $totalfacturasigv = $facturas->sum('ccaigv');
                  $totalboletasigv = $boletas->sum('ccaigv');
                  $totalnotasigv = $notas->sum('ccaigv');
                 
                  $totalfacturasicbper = $facturas->sum('icbper');
                  $totalboletasicbper = $boletas->sum('icbper');
                  $totalnotasicbper = $notas->sum('icbper');

                $vista = view('empresas.reportes.ventas_contador',compact('dat_cli','fecin','fecfin','facturas','boletas','notas','totalfacturas','totalboletas','totalfacturasexo','totalboletasexo','totalnotasexo','totalfacturasinaf','totalboletasinaf','totalnotasinaf','totalfacturasigv','totalboletasigv','totalnotasigv','totalfacturasicbper','totalboletasicbper','totalnotasicbper','totalnotas','dat_ven'))->render();

              if($request->ajax()){
                 return response()->json(['vista'=>$vista]);

              }


            break;

              case 50:

                   $tipo = $request->get('promocion');
          $categoria = $request->get('cmbCatId');
          $sucursal = $request->get('sucursal');
          $negocios = DB::tABLE('empresa_negocios')->get();
          $almacen = $request->get('almacen');
          $datosalm ="";

            $rucemp = trim(Auth::user()->IdEmpresa);
            $empresa = Empresa::findOrFail($rucemp);

            $buspro = trim($request->get('buspro'));
            
             $categorias = DB::tABLE('categorias')->get();

 

            $tipos_productos = DB::tABLE('tipos_productos')->get();

            $datos = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',$negocios->first()->id_empresa_negocio)->first();

            if(empty($sucursal)){

               $almacenes = DB::tABLE('almacenes')->where('id_empresa_negocio',$negocios->first()->id_empresa_negocio)->get();

            }else{

               $almacenes = DB::tABLE('almacenes')->where('id_empresa_negocio',$sucursal)->get();
            }



           
                

                $productos = DB::tABLE('productos as p')
                ->select('lote','vencimiento','producto_stock.stock_inicial','producto_stock.stock','p.procod','p.pronom','m.monnom','u.umenom','p.propun','p.proest','producto_stock.IdProducto','mar.mar_nom as marca','p.costo','p.propun','modelo')
                ->leftjoin('moneda as m','p.moncod','=','m.moncod')
         ->leftjoin('marcas as mar','mar.mar_id','=','p.marca')
                ->leftjoin('unidad_medida as u','p.umecod','=','u.umecod')
               // ->leftjoin('producto_empresa','producto_empresa.IdProducto','p.IdProducto')
                ->leftjoin('producto_stock','producto_stock.IdProducto','p.IdProducto')
                ->where(function ($query) use($categoria) {

                  if($categoria!='Todos'){
                    $query->where('cat_id',$categoria);
                  }
                  
                })
                ->where(function ($query1) use($buspro) {

                  if(!empty($buspro)){
                    $query1->where('pronom','like','%'.$buspro.'%')
                            ->orwhere('procod',$buspro);
                  }
                  
                })
                ->where('producto_stock.id_empresa_negocio',$sucursal)
                ->where('tipo','1')
                ->where('id_almacen',$almacen)
                ->groupby('p.IdProducto')
                ->orderby('pronom','asc')
                ->get();

                $datosalm = DB::tABLE('almacenes')->where('id_almacen',$almacen)->first();

                

               

                $vista = view('empresas.reportes.stock_productos',compact('productos','buspro','negocios','categorias','tipos_productos','categoria','tipo','sucursal','datos','almacenes','almacen','datosalm','empresa'))->render();

              if($request->ajax()){
                 return response()->json(['vista'=>$vista]);

              }


            break;

            case 51:

                if($vendedor!='Todos'){
                $dato_vendedor = DB::tABLE('users')->where('IdUsuario',$vendedor)->first();
                }

                if($cliente!='Todos'){
                  $dato_cliente = DB::tABLE('cliente')->where('clicod',$cliente)->first();
                }

                $comprobantes = cpe_cabecera::select('cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','det.cdedes as producto','det.cdecan as cantidad','cdepuni as precio','monnom as moneda','det.cdevve as total','cpe_cabecera.ccabaj as baja','det.costo','ccasunrescod','tdides as documentoidentidad','ccandi as numerodocumento','procod','cdedes','cdecan','cdepuni','cdevve','umecod','ccaitv','id_almacen_pro')
                ->leftjoin('cpe_detalle as det','cpe_cabecera.IdCpe_cabecera','det.IdCpe_cabecera')
                ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                 ->where(function ($query) use ($vendedor) {
                      if($vendedor !='Todos'){
                         $query->Where('cpe_cabecera.IdUsuario_ven',$vendedor);
                      }
                    })
                ->where(function ($query) {
                  $query->where('cpe_cabecera.tdocod','01')
                    ->orWhere('cpe_cabecera.tdocod','03')
                    ->orWhere('cpe_cabecera.tdocod','13')
                    ->orWhere('cpe_cabecera.tdocod','14')
                    //->orWhere('cpe_cabecera.tdocod','07')
                    ;
                  }) 
                  ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                   ->where(function ($query2) use ($cliente) {
                      if($cliente !='Todos'){
                         $query2->Where('cpe_cabecera.clicod',$cliente);
                      }
                    })
                 ->whereNotNull('ccabaj')
                 ->orderBy('IdCpe_cabecera','desc')->get();




                 $cabecera = DB::tABLE('cpe_cabecera')
             //    ->join('cpe_detalle','cpe_detalle.IdCpe_cabecera','cpe_cabecera.IdCpe_cabecera')
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                 ->where(function ($query) use ($vendedor){
                      if($vendedor !='Todos'){
                         $query->Where('cpe_cabecera.IdUsuario_ven',$vendedor);
                      }
                    })
                ->where(function ($query) {
                  $query->where('cpe_cabecera.tdocod','01')
                    ->orWhere('cpe_cabecera.tdocod','03')
                    ->orWhere('cpe_cabecera.tdocod','13')
                    ->orWhere('cpe_cabecera.tdocod','14')
                   // ->orWhere('cpe_cabecera.tdocod','07')
                    ;
                  }) 
 
                ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                 ->where(function ($query2) use ($cliente) {
                      if($cliente !='Todos'){
                         $query2->Where('cpe_cabecera.clicod',$cliente);
                      }
                    })
                ->whereNotNull('ccabaj')
                 ->get();

                 $total = $cabecera->where('ccanot','')->sum('ccaitv');

                 $totalnotas = $cabecera->where('tdocod','7')->sum('ccaitv');

                 $totalefectivo = $cabecera->where('ccanot','')->sum('totalcontado');
                 $totalcredito = $cabecera->where('ccanot','')->sum('totalcredito');

                $vista = view('empresas.reportes.ventas_anuladas', compact('totalnotas','total','dato_vendedor','cabecera','comprobantes','fecin','fecfin','empresa','sucursal','almacen','data_sucursal','dato_cliente','totalefectivo','totalcredito'))->render();

              if($request->ajax()){
                 return response()->json(['vista'=>$vista]);

              }


            break;

            case 52:

                if($vendedor!='Todos'){
                $dato_vendedor = DB::tABLE('users')->where('IdUsuario',$vendedor)->first();
                }

                if($cliente!='Todos'){
                  $dato_cliente = DB::tABLE('cliente')->where('clicod',$cliente)->first();
                }

                $comprobantes = cpe_cabecera::select('cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','det.cdedes as producto','det.cdecan as cantidad','cdepuni as precio','monnom as moneda','det.cdevve as total','cpe_cabecera.ccabaj as baja','det.costo','ccasunrescod','tdides as documentoidentidad','ccandi as numerodocumento','procod','cdedes','cdecan','cdepuni','cdevve','umecod','ccaitv','id_almacen_pro')
                ->leftjoin('cpe_detalle as det','cpe_cabecera.IdCpe_cabecera','det.IdCpe_cabecera')
                ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                 ->where(function ($query) use ($vendedor) {
                      if($vendedor !='Todos'){
                         $query->Where('cpe_cabecera.IdUsuario_ven',$vendedor);
                      }
                    })
                ->where(function ($query) {
                  $query->where('cpe_cabecera.tdocod','16')
                    ;
                  }) 
                  ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                   ->where(function ($query2) use ($cliente) {
                      if($cliente !='Todos'){
                         $query2->Where('cpe_cabecera.clicod',$cliente);
                      }
                    })
                 ->whereNotNull('ccabaj')
                 ->orderBy('IdCpe_cabecera','desc')->get();




                 $cabecera = DB::tABLE('cpe_cabecera')
             //    ->join('cpe_detalle','cpe_detalle.IdCpe_cabecera','cpe_cabecera.IdCpe_cabecera')
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                 ->where(function ($query) use ($vendedor){
                      if($vendedor !='Todos'){
                         $query->Where('cpe_cabecera.IdUsuario_ven',$vendedor);
                      }
                    })
                ->where(function ($query) {
                  $query->where('cpe_cabecera.tdocod','16')
                   // ->orWhere('cpe_cabecera.tdocod','07')
                    ;
                  }) 
                ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                 ->where(function ($query2) use ($cliente) {
                      if($cliente !='Todos'){
                         $query2->Where('cpe_cabecera.clicod',$cliente);
                      }
                    })
                ->whereNotNull('ccabaj')
                 ->get();

                 $total = $cabecera->where('ccanot','')->sum('ccaitv');

                 $totalnotas = $cabecera->where('tdocod','7')->sum('ccaitv');

                 $totalefectivo = $cabecera->where('ccanot','')->sum('totalcontado');
                 $totalcredito = $cabecera->where('ccanot','')->sum('totalcredito');

                $vista = view('empresas.reportes.pedidos_anulados', compact('totalnotas','total','dato_vendedor','cabecera','comprobantes','fecin','fecfin','empresa','sucursal','almacen','data_sucursal','dato_cliente','totalefectivo','totalcredito'))->render();

              if($request->ajax()){
                 return response()->json(['vista'=>$vista]);

              }


            break;



             case 53:

              $comprobantes = compras_cabecera::select('total_com','com_cab_id','com_fec as fecha','tdodes as comprobante','com_doc_ser as serie','com_doc_num as numero','prov_raz as cliente','monnom as moneda','total_com as total','tdides as documentoidentidad','prov_ruc as numerodocumento','compras_cabecera.tdocod')
                ->leftjoin('moneda as m','compras_cabecera.mon_id','=','m.moncod')
                ->leftjoin('proveedor as cl','compras_cabecera.prov_id','=','cl.prov_id')
               //->leftjoin('empresa as e','compras_cabecera.IdEmpresa','=','e.IdEmpresa')
                ->leftjoin('tipo_documento as tip_d','compras_cabecera.tdocod','=','tip_d.tdocod')
                ->leftjoin('tipo_documento_identidad as tdi','cl.tdicod','=','tdi.tdicod')
                 ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('compras_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                
                ->where(function ($query6) use ($almacen) {
                  if($almacen !=0 && !empty($almacen)){
                      $query6->where('compras_cabecera.id_almacen',$almacen);
                  }                
                }) 
                ->where('compras_cabecera.com_fec','>=',$fecin)
                ->where('compras_cabecera.com_fec','<=',$fecfin)
                /*->where(function ($query){
                    $query->where('compras_cabecera.tdocod','01')
                          ->orwhere('compras_cabecera.tdocod','03')
                          ->orWhere('compras_cabecera.tdocod','13')
                          ->orWhere('compras_cabecera.tdocod','07');
                })*/
                ->where('est_compra','Registrado')
                ->orderBy('com_cab_id','desc')->get();

            //  dd($comprobantes);

                  $total = $comprobantes->sum('total_com');

                  $totalexoneradasfacturas = $comprobantes->where('tdocod','01')->sum('total_com');
                  $totalgravadasfacturas = $comprobantes->where('tdocod','01')->sum('ccatvg');

                  $totalexoneradasboletas = $comprobantes->where('tdocod','03')->sum('total_com');
                  $totalgravadasboletas = $comprobantes->where('tdocod','03')->sum('ccatvg');

                  $totalexoneradasnotas = $comprobantes->where('tdocod','07')->sum('total_com');
                  $totalgravadasnotas = $comprobantes->where('tdocod','07')->sum('ccatvg');


            

                 $sucursal = EmpresaNegocios::FindOrFail($request->get('sucursal'));

                 $empresa = Empresa::findOrFail($sucursal->IdEmpresa);

                
                    $vista =view('empresas.reportes.compras_resumen',compact('fecin','fecfin','total','totalexoneradasfacturas','totalexoneradasboletas','totalgravadasfacturas','totalgravadasboletas','totalgravadasnotas','totalexoneradasnotas','empresa','sucursal'))->render();


              if($request->ajax()){
                 return response()->json(['vista'=>$vista]);

              }

              break;
                


              case 54:



                $comprobantes = compras_cabecera::select('cod_mov','total_com','compras_cabecera.com_cab_id','com_fec as fecha','tdodes as comprobante','compras_cabecera.com_doc_ser as serie','compras_cabecera.com_doc_num as numero','prov_raz as cliente','monnom as moneda','total_com as total','tdides as documentoidentidad','prov_ruc as numerodocumento',
                  'compras_cabecera.tdocod','com_fec_ven as fechaven','cl.tdicod','total_com as ccatexo')
                ->leftjoin('moneda as m','compras_cabecera.mon_id','=','m.moncod')
                ->leftjoin('proveedor as cl','compras_cabecera.prov_id','=','cl.prov_id')
                ->leftjoin('empresa as e','compras_cabecera.IdEmpresa','=','e.IdEmpresa')
                ->leftjoin('tipo_documento as tip_d','compras_cabecera.tdocod','=','tip_d.tdocod')
                ->leftjoin('tipo_documento_identidad as tdi','tdi.tdicod','=','cl.tdicod')
                 ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('compras_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                
                ->where(function ($query6) use ($almacen) {
                  if($almacen !='Todos'){
                      $query6->where('compras_cabecera.id_almacen',$almacen);
                  }                
                }) 
                ->where('compras_cabecera.com_fec','>=',$fecin)
                ->where('compras_cabecera.com_fec','<=',$fecfin)
                /* ->where(function ($query){
                    $query->where('compras_cabecera.tdocod','01')
                          ->orwhere('compras_cabecera.tdocod','03')
                          ->orWhere('compras_cabecera.tdocod','13');
                })
               ->where(function ($query1) use ($vendedor) {
                      if($vendedor !='Todos'){
                         $query1->Where('compras_cabecera.IdUsuario_ven',$vendedor);
                      }
                    })*/
                 /*->where(function ($query2) use ($cliente) {
                      if($cliente !='Todos'){
                         $query2->Where('compras_cabecera.clicod',$cliente);
                      }
                    })*/
                ->orderBy('com_fec','desc')->get();

      

                  $total = $comprobantes->sum('total_com');

                $vista = view('empresas.reportes.compras_contador',compact('dat_cli','fecin','fecfin','comprobantes','total','dat_ven'))->render();

              if($request->ajax()){
                 return response()->json(['vista'=>$vista]);

              }


            break;

              case 55:

              $comprobantes = DB::tABLE('compras_cabecera')->select('compras_detalle.vencimiento','compras_detalle.lote','pronom')
              ->leftjoin('proveedor','compras_cabecera.prov_id','proveedor.prov_id')
              ->leftjoin('compras_detalle','compras_cabecera.com_cab_id','compras_detalle.com_cab_id')
              ->leftjoin('productos','productos.IdProducto','compras_detalle.pro_id')
              ->join('moneda','compras_cabecera.mon_id','moneda.moncod')
              ->join('tipo_documento','compras_cabecera.tdocod','tipo_documento.tdocod')
              
              ->where('est_compra','Registrado')
                ->where('compras_cabecera.com_fec','>=',$fecin)
                ->where('compras_cabecera.com_fec','<=',$fecfin)
                  ->where(function ($query) use ($sucursal){
                      if(!$sucursal !=0 && !empty($sucursal)){
                          $query->where('compras_cabecera.id_empresa_negocio',$sucursal);
                      }
                     
                  })
                  ->where(function ($query1) use ($proveedor){
                      if($proveedor !=0){
                          $query1->where('compras_cabecera.prov_id',$proveedor);
                      }
                      
                  })

                   ->where(function ($query8) use ($pro){
                      if($pro !=0){
                          $query8->where('compras_detalle.IdProducto_rel',$pro);
                      }
                      
                  })
                        
                  ->where(function ($query6) use ($almacen) {
                      if($almacen !=0 && !empty($almacen)){
                          $query6->where('compras_cabecera.id_almacen',$almacen);
                      }                
                    }) 
              ->where('compras_cabecera.tdocod','!=','80')
              ->groupby('pro_id','compras_detalle.vencimiento')
              ->orderby('compras_cabecera.com_cab_id','desc')
              ->get();
                 //dd($comprobantes);

                  $total = $comprobantes->sum('total_com');


                $vista = view('empresas.reportes.compras_vencimiento',compact('fecin','fecfin','comprobantes','total','dat_ven','dat_cli'))->render();

                    if($request->ajax()){
                 return response()->json(['vista'=>$vista]);

              }

              break;


              case 17:

              $comprobantes = cpe_cabecera::select('ccaitv','cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','monnom as moneda','ccaitv as total','cpe_cabecera.ccabaj as baja','ccasunrescod','tdides as documentoidentidad','ccandi as numerodocumento','cpe_cabecera.tdocod','ccatexo','ccatvg','ccatvi')
                ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
                 ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                
                ->where(function ($query6) use ($almacen) {
                  if($almacen !=0 && !empty($almacen)){
                      $query6->where('cpe_cabecera.id_almacen',$almacen);
                  }                
                }) 
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                ->where(function ($query){
                    $query->where('cpe_cabecera.tdocod','01')
                          ->orwhere('cpe_cabecera.tdocod','03')
                          ->orWhere('cpe_cabecera.tdocod','13')
                          ->orWhere('cpe_cabecera.tdocod','07');
                })
                ->whereNull('ccabaj')
                ->orderBy('IdCpe_cabecera','desc')->get();

            //   dd($comprobantes);

                  $total = $comprobantes->sum('ccaitv');

                  $totalexoneradasfacturas = $comprobantes->where('tdocod','01')->sum('ccatexo');
                  $totalgravadasfacturas = $comprobantes->where('tdocod','01')->sum('ccatvg');

                  $totalexoneradasboletas = $comprobantes->where('tdocod','03')->sum('ccatexo');
                  $totalgravadasboletas = $comprobantes->where('tdocod','03')->sum('ccatvg');

                   $totalexoneradasnotas = $comprobantes->where('tdocod','07')->sum('ccatexo');
                  $totalgravadasnotas = $comprobantes->where('tdocod','07')->sum('ccatvg');


            

                 $sucursal = EmpresaNegocios::FindOrFail($request->get('sucursal'));

                 $empresa = Empresa::findOrFail($sucursal->IdEmpresa);

                
                    $vista =view('empresas.reportes.ventas_resumen',compact('fecin','fecfin','total','totalexoneradasfacturas','totalexoneradasboletas','totalgravadasfacturas','totalgravadasboletas','totalgravadasnotas','totalexoneradasnotas','empresa','sucursal'))->render();


              if($request->ajax()){
                 return response()->json(['vista'=>$vista]);

              }

              break;
                

              //CONSOLIDADO DE VENTAS
              case 80:

                $boletas = cpe_cabecera::select('ccafem as FECHA', 'tdodes as COMPROBANTE','serdoc as SERIE',DB::raw('MIN(numdoc) as INICIO'),DB::raw( 'MAX(numdoc) as FIN'),DB::raw('SUM(ccatvg) as VALOR_VENTA'),DB::raw('SUM(ccaigv) as IGV'),DB::raw('SUM(ccaitv) as TOTAL'))
                ->leftjoin('tipo_documento','tipo_documento.tdocod','cpe_cabecera.tdocod')
                ->whereNull('ccabaj')
                ->where('cpe_cabecera.tdocod','03')
                ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                }) 
                ->where(function ($query6) use ($almacen) {
                  if($almacen !='Todos'){
                      $query6->where('cpe_cabecera.id_almacen',$almacen);
                  }                
                }) 
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                ->groupby('ccafem')
                ->groupby('tdodes')
                ->orderBy('FECHA','asc')
                ->orderBy('INICIO','asc')
                ->get();

                $facturas = cpe_cabecera::select('ccaitv','cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','monnom as moneda','ccaitv as total','cpe_cabecera.ccabaj as baja','ccasunrescod','tdides as documentoidentidad','ccandi as numerodocumento','cpe_cabecera.tdocod','ccatexo','ccatvg','ccatvi')
                ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
                ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                
                ->where(function ($query6) use ($almacen) {
                  if($almacen !=0 && !empty($almacen)){
                      $query6->where('cpe_cabecera.id_almacen',$almacen);
                  }                
                }) 
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                ->where(function ($query){
                    $query->where('cpe_cabecera.tdocod','01');
                })
                ->whereNull('ccabaj')
                ->orderBy('IdCpe_cabecera','desc')->get();

             

                $total_boletas = $boletas->sum('TOTAL');
                $total_facturas = $facturas->sum('total');

                $vista = view('empresas.reportes.ventas_consolidado',compact('dat_cli','fecin','fecfin','boletas','facturas','total_boletas','total_facturas','dat_ven'))->render();

              if($request->ajax()){
                 return response()->json(['vista'=>$vista]);

              }


            break;







         
      }

    }




    public function imprimirreporteventas(Request $request){

       $clientes = DB::tABLE('cliente')->where('rucemp',Auth::user()->IdEmpresa)->get();
        $clicod = $request->get('clicod');

        $fecin = $request->get('fecin');
        $fecfin = $request->get('fecfin');
        $empresa = Empresa::findOrFail(Auth::user()->IdEmpresa);
        $IdEmpresa = Auth::user()->IdEmpresa;
        $docomp = $request->get('docomp');
        $nomempresa = Empresa::FindOrFail($IdEmpresa);
        $datoempresa = $nomempresa->NomEmpresa;

        $vendedor = $request->get('vendedor');
        
        $vendedores = DB::tABLE('users')
        ->leftjoin('role_user','role_user.user_IdUsuario','users.IdUsuario')
       // ->where('role_id','5')
         ->where('IdEmpresa',Auth::user()->IdEmpresa)
        ->get();

        $dat_ven = DB::tABLE('users')->where('IdUsuario',$request->get('vendedor'))->first();



        $comprobantes = cpe_cabecera::select('cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','monnom as moneda','ccaitv as total','cpe_cabecera.ccabaj as baja','ccasunrescod','tdides as documentoidentidad','ccandi as numerodocumento','cpe_cabecera.tdocod')
                    ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                    ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                    ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                    ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                    ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
                    ->where('cpe_cabecera.id_empresa_negocio',Auth::user()->id_empresa_negocio)
                    ->where('cpe_cabecera.ccafem','>=',$fecin)
                    ->where('cpe_cabecera.ccafem','<=',$fecfin)
                    ->where(function ($query) use ($vendedor) {
                      if($vendedor !='Todos'){
                         $query->Where('cpe_cabecera.IdUsuario_ven',$vendedor);
                      }
                    })
                        ->where(function ($query) {
                $query->where('cpe_cabecera.tdocod','01')
                    ->orWhere('cpe_cabecera.tdocod','03')
                    ->orWhere('cpe_cabecera.tdocod','13')
                    ->orWhere('cpe_cabecera.tdocod','14');
                })    
                    ->where(function ($query1){
                        $query1->whereNull('cpe_cabecera.ccabaj')
                              ->orwhere('cpe_cabecera.ccabaj','');
                    
                    })
                    ->orderBy('IdCpe_cabecera','desc')->get();

          $totalfacturas = $comprobantes->where('tdocod','01')->sum('total');

          $totalboletas =  $comprobantes->where('tdocod','03')->sum('total');

             $total =  $comprobantes->sum('total');

      $impresoras = DB::tABLE('configuracion_impresoras')->where('Id',Auth::user()->terminal)->first();


  
      $connector = new WindowsPrintConnector("smb://".$impresoras->ruta);

     
        $printer = new Printer($connector);
        $printer->setJustification(Printer::JUSTIFY_CENTER);
       

       $printer->text("TIENDAS DEPORTIVAS S.A.C"."\n");
             $printer->text("JR. PROSPERO #643"."\n");
       $printer->text('RUC:'.$empresa->IdEmpresa."\n");
       
 
        
        $printer->setJustification(Printer::JUSTIFY_CENTER);
        $printer->text("VENTAS DEL DIA"."\n");
          $printer->setJustification(Printer::JUSTIFY_LEFT);
        $printer->text("Fecha       Cod. Doc.  Serie  N° Doc.  Total"."\n");
        $printer->text("________________________________________________"."\n");
        foreach ($comprobantes as $comp) {
         
           $printer->text(Carbon::parse($comp->fecha)->format('d-m-Y')."    ".$comp->tdocod."       ".$comp->serie."   ".$comp->numero."    ".$comp->total."\n");
     
           
          
          
        }

        $printer->text("________________________________________________"."\n");
        $printer->text("TOTAL B/V                                ".number_format($totalboletas,'2','.',',')."\n");
        $printer->text("TOTAL F                                  ".number_format($totalfacturas,'2','.',',')."\n"."\n");


        $printer->text("________________________________________________"."\n");
        $printer->text("TOTAL VENTAS                             ".number_format($total,'2','.',',')."\n");
        $printer->feed();
         
     
        $printer->cut();
         
     
        $printer->pulse();

        $printer->close();



}
  
  
    public function imprimirreportes(){

        return view('empresas.reportes.indexventasticket');
    }

  public function kardex(Request $request){

      $almacen = $request->get('almacen');
      $sucursal = $request->get('sucursal');
      $prod = $request->get('IdProducto');
      $docomp= $request->get('docomp');
      $fecin = $request->get('fecin');
      $fecfin = $request->get('fecfin');
  

      $productoslista = DB::tABLE('productos')
      ->where('tipo','=','1')
      ->where('promocion','!=','2')
      ->get();

      $productos = DB::tABLE('productos')
      ->where('tipo','=','1')
      ->get();
  
      $negocios = DB::tABLE('empresa_negocios')->get();
      $almacenes = DB::tABLE('almacenes')->where('id_empresa_negocio',$negocios->first()->id_empresa_negocio)->get();
     
       
    return view('empresas.reportes.kardex',compact('productos','negocios','almacenes','productoslista'));
     

    }


   public function generarkardexpdf(Request $request){

    $negocios = DB::tABLE('empresa_negocios')->get();

    $almacenes = DB::tABLE('almacenes')->where('id_empresa_negocio',$negocios->first()->id_empresa_negocio)->get();

    $almacen = $request->get('almacen');
    $sucursal = $request->get('sucursal');
    $tipo = $request->get('docomp');
    $fecin = $request->get('fecin');
    $fecfin = $request->get('fecfin');
    $IdProducto = $request->get('IdProducto');

    $empresa = Empresa::findOrFail(Auth::user()->IdEmpresa);

    $data_sucursal = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',$sucursal)->first();

   
     
     $productoslista = DB::tABLE('productos')->select('IdProducto','pronom','procod','costo_total','flete','umecod')
    ->where('tipo','=','1')
    ->where('promocion','!=','2')
    //->where('id_empresa_negocio',$sucursal)
    ->orderby('pronom','asc')
    ->get();



      $saldo_anterior =0;


      $productos = DB::tABLE('productos')->select('IdProducto','pronom','procod','costo_total','flete','umecod',DB::raw("(SELECT COUNT(*) FROM movimientos_productos WHERE IdProducto_rel=productos.IdProducto ) as contar"))
      ->where('tipo','=','1')
      ->where(function ($query) use ($IdProducto){
          if($IdProducto!='Todos'){
             $query->where('IdProducto','=',$IdProducto); 
          }      
      }) 
      ->having('contar','>','0')
      ->where('id_empresa_negocio',$sucursal)
      ->orderby('IdProducto','asc')
      ->get();



      $dat_alm = DB::tABLE('almacenes')->where('id_almacen',$almacen)->first();
      $dat_suc = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',$sucursal)->first();
      $dat_emp = DB::tABLE('empresa')->where('IdEmpresa',Auth::user()->IdEmpresa)->first();

      $saldos_actuales = [];
      $saldo_anterior=0;
      $kardex =[];
      $kardex_mov=[];
      $array_productos = [];

      
      

     
          foreach($productos as $p){

               $kardex_mov = [];
               $ingresos = 0;
               $salidas = 0;
               $saldo_anterior=0;

              $buspro = $p->IdProducto;


              $ingresos = DB::tABLE('movimientos_productos')->select('cantidad')
             ->where('mov_tip','I')
             ->WHERE('fecha_mov','<',$fecin)
             ->where('IdProducto_rel',$buspro)
             ->where('id_empresa_negocio',$sucursal)
             ->where(function ($query) use ($almacen) {
                    if($almacen!='Todos'){
                      $query->where('id_almacen','=',$almacen); 
                    }      
                }) 
             ->sum('cantidad');

             $salidas = DB::tABLE('movimientos_productos')->select('cantidad')
             ->where('mov_tip','E')
              ->WHERE('fecha_mov','<',$fecin)
             ->where('IdProducto_rel',$buspro)
             ->where('id_empresa_negocio',$sucursal)
             ->where(function ($query) use ($almacen) {
                    if($almacen!='Todos'){
                      $query->where('id_almacen','=',$almacen); 
                    }      
                }) 
             ->sum('cantidad');

    
              $saldo_anterior = $ingresos-$salidas;

              if($saldo_anterior!=0){
          
                   $kardex_mov[] = array(
                      'fecha'=>$fecin,
                      'tdocod'=>'',
                      'serie'=>'',
                      'numero'=>'',
                      'cliente'=>'SALDO_ANTERIOR',
                      'cantidad'=>$saldo_anterior,
                      'IdProducto'=>$buspro,
                      'IdProducto_rel'=>$buspro,
                      'destino'=>'',
                      'origen'=>'',
                      'descripcion'=>'SALDO ANTERIOR',
                      'cod_tip_ope'=>'16',
                      'mov_tip'=>'I',
                      'tipo'=>'0',
                      'costo'=>'',
                      'stock'=>'0',
                      'mov_lote'=>'',
                      'mov_vencimiento'=>''
                    );
              }


              $movimientos = DB::tABLE('movimientos_productos')
              ->select('mov_vencimiento','mov_lote','cod_tip_ope','tipo','fecha_mov','tdocod','serie','numero','cliente','stock_inicial','cantidad','stock','IdProducto_rel','IdProducto',
              DB::raw("(SELECT descripcion FROM almacenes WHERE id_almacen=movimientos_productos.id_almacen_destino) as destino"),
              DB::raw("(SELECT descripcion FROM almacenes WHERE id_almacen=movimientos_productos.id_almacen) as origen"),
              'descripcion','mov_tip','costo')
              ->where(function ($query) use ($almacen){
              if($almacen!='Todos'){
                  $query->where('id_almacen','=',$almacen); 
                }      
              }) 
               ->where(function ($query) use ($buspro){
                  $query->where('IdProducto','=',$buspro)
                  ->orWhere('IdProducto_rel',$buspro); 
                    
              }) 
              ->where('fecha_mov','>=',$fecin)
              ->where('fecha_mov','<=',$fecfin)
              ->orderby('fecha_mov','asc')
              ->orderby('tipo','asc')
              ->get();  

              
              foreach ($movimientos as $mov) {
                

                      $kardex_mov[] = array(
                      'fecha'=>$mov->fecha_mov,
                      'tdocod'=>$mov->tdocod,
                      'serie'=>$mov->serie,
                      'numero'=>$mov->numero,
                      'cliente'=>$mov->cliente,
                      'cantidad'=>$mov->cantidad,
                      'IdProducto'=>$mov->IdProducto,
                      'IdProducto_rel'=>$mov->IdProducto_rel,
                      'destino'=>$mov->destino,
                      'origen'=>$mov->origen,
                      'descripcion'=>$mov->descripcion,
                      'mov_tip'=>$mov->mov_tip,
                      'costo'=>$mov->costo,
                      'cod_tip_ope'=>$mov->cod_tip_ope,
                      'tipo'=>$mov->tipo,
                      'stock'=>'0',
                      'mov_lote'=>$mov->mov_lote,
                      'mov_vencimiento'=>$mov->mov_vencimiento

                    );
        

              }



           

           

              $array_productos[] = array(
                  'codigo'=> $p->procod,
                  'unidad'=>$p->umecod,
                  'producto'=>$p->pronom,
                  'movimientos' =>array(
                    $kardex_mov
                  )

              );


        }
 
    
    

 // dd($array_productos);

   $id_producto = array_column($kardex_mov, 'IdProducto_rel');
   $fec_mov = array_column($kardex_mov, 'fecha');
   $num_mov  = array_column($kardex_mov, 'tipo');
   $tipo_mov = array_column($kardex_mov, 'mov_tip');

   array_multisort($id_producto, SORT_ASC,$fec_mov, SORT_ASC,$num_mov, SORT_ASC, $tipo_mov, SORT_DESC, $kardex_mov);

     // dd($kardex);

   

      
      if($tipo=='2'){


            ini_set("pcre.backtrack_limit", "5000000");

             
             $pdf = PDF::loadView('formatos_reportes.kardexfisicopdf',compact('dat_alm','data_sucursal','movimientos','productos','negocios','almacenes','sucursal','almacen','data_sucursal','dat_alm','dat_emp','fecin','fecfin','productoslista','saldos_actuales','kardex','IdProducto','empresa','array_productos'));
             return $pdf->stream('document.pdf');

          


                $headers = array(
                        'Content-Type: application/pdf',
                      );



     //   return view('formatos_reportes.kardexfisicopdf',compact('dat_alm','data_sucursal','movimientos','productos','negocios','almacenes','sucursal','almacen','data_sucursal','dat_alm','dat_emp','fecin','fecfin','productoslista','saldos_actuales','kardex','IdProducto','empresa','array_productos'));

        // $view = \View::make('formatos_reportes.kardexfisicopdf',compact('dat_alm','data_sucursal','movimientos','productos','negocios','almacenes','sucursal','almacen','data_sucursal','dat_alm','dat_emp','fecin','fecfin','productoslista','saldos_actuales','kardex','IdProducto','empresa','array_productos'));

           // $nompdffile='KARDEX_FISICO_'.now()->format('Y-m-d').'.pdf'; 

            


      }elseif($tipo=='1'){


        return view('formatos_reportes.kardexvalorizadopdf',compact('dat_alm','data_sucursal','movimientos','productos','negocios','almacenes','sucursal','almacen','data_sucursal','dat_alm','dat_emp','fecin','fecfin','productoslista','saldos_actuales','kardex','IdProducto','empresa','array_productos'));

         // $view = \View::make('formatos_reportes.kardexvalorizadopdf',compact('dat_alm','data_sucursal','movimientos','productos','negocios','almacenes','sucursal','almacen','data_sucursal','dat_alm','dat_emp','fecin','fecfin','productoslista','saldos_actuales','kardex','IdProducto','empresa','array_productos'));

           //$nompdffile='KARDEX_VALORIZADO_'.now()->format('Y-m-d').'.pdf'; 


      }

/*
        $rutapdf = public_path().'/reporte_cuentas_cobrar/';

            if(file_exists($rutapdf.$nompdffile)){
                        unlink($rutapdf.$nompdffile);
            }
                      

      $pdf = \App::make('dompdf.wrapper');
      $contenido = $view->render();
      $pdf->loadHTML($view)->setPaper('A4', 'Landscape')->save($rutapdf.$nompdffile);

     
      $headers = array(
              'Content-Type: application/pdf',
            );

      return response()->download($rutapdf.$nompdffile);
  

*/
   
     



 }

  
   public function generarkardexexcel(Request $request){

  $negocios = DB::tABLE('empresa_negocios')->get();

    $almacenes = DB::tABLE('almacenes')->where('id_empresa_negocio',$negocios->first()->id_empresa_negocio)->get();

    $almacen = $request->get('almacen');
    $sucursal = $request->get('sucursal');
    $tipo = $request->get('docomp');
    $fecin = $request->get('fecin');
    $fecfin = $request->get('fecfin');
    $IdProducto = $request->get('IdProducto');


     
     $productoslista = DB::tABLE('productos')->select('IdProducto','pronom','procod','costo_total','flete','umecod')
    ->where('tipo','=','1')
    ->where('promocion','!=','2')
    //->where('id_empresa_negocio',$sucursal)
    ->orderby('pronom','asc')
    ->get();


      $productos = DB::tABLE('productos')->select('IdProducto','pronom','procod','costo_total','flete','umecod',DB::raw("(SELECT COUNT(*) FROM movimientos_productos WHERE IdProducto_rel=productos.IdProducto ) as contar"))
      ->where('tipo','=','1')
      ->where(function ($query) use ($IdProducto){
          if($IdProducto!='Todos'){
             $query->where('IdProducto','=',$IdProducto); 
          }      
      }) 
      ->having('contar','>','0')
      ->where('id_empresa_negocio',$sucursal)
      ->orderby('IdProducto','asc')
      ->get();


      $dat_alm = DB::tABLE('almacenes')->where('id_almacen',$almacen)->first();
      $dat_suc = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',$sucursal)->first();
      $dat_emp = DB::tABLE('empresa')->where('IdEmpresa',Auth::user()->IdEmpresa)->first();

      $saldos_actuales = [];
      $saldo_anterior=0;
      $kardex =[];
      $kardex_mov=[];
      $array_productos = [];

      
      

     
          foreach($productos as $p){

               $kardex_mov = [];
               $ingresos = 0;
               $salidas = 0;
               $saldo_anterior=0;

              $buspro = $p->IdProducto;


              $ingresos = DB::tABLE('movimientos_productos')->select('cantidad')
             ->where('mov_tip','I')
             ->WHERE('fecha_mov','<',$fecin)
             ->where('IdProducto_rel',$buspro)
             ->where('id_empresa_negocio',$sucursal)
             ->where(function ($query) use ($almacen) {
                    if($almacen!='Todos'){
                      $query->where('id_almacen','=',$almacen); 
                    }      
                }) 
             ->sum('cantidad');

             $salidas = DB::tABLE('movimientos_productos')->select('cantidad')
             ->where('mov_tip','E')
              ->WHERE('fecha_mov','<',$fecin)
             ->where('IdProducto_rel',$buspro)
             ->where('id_empresa_negocio',$sucursal)
             ->where(function ($query) use ($almacen) {
                    if($almacen!='Todos'){
                      $query->where('id_almacen','=',$almacen); 
                    }      
                }) 
             ->sum('cantidad');

    
              $saldo_anterior = $ingresos-$salidas;

              if($saldo_anterior!=0){
          
                   $kardex_mov[] = array(
                      'fecha'=>$fecin,
                      'tdocod'=>'',
                      'serie'=>'',
                      'numero'=>'',
                      'cliente'=>'SALDO_ANTERIOR',
                      'cantidad'=>$saldo_anterior,
                      'IdProducto'=>$buspro,
                      'IdProducto_rel'=>$buspro,
                      'destino'=>'',
                      'origen'=>'',
                      'descripcion'=>'SALDO ANTERIOR',
                      'cod_tip_ope'=>'16',
                      'mov_tip'=>'I',
                      'tipo'=>'0',
                      'costo'=>'',
                      'stock'=>'0',
                      'mov_lote'=>'',
                      'mov_vencimiento'=>''
                    );
              }


              $movimientos = DB::tABLE('movimientos_productos')
              ->select('mov_vencimiento','mov_lote','cod_tip_ope','tipo','fecha_mov','tdocod','serie','numero','cliente','stock_inicial','cantidad','stock','IdProducto_rel','IdProducto',
              DB::raw("(SELECT descripcion FROM almacenes WHERE id_almacen=movimientos_productos.id_almacen_destino) as destino"),
              DB::raw("(SELECT descripcion FROM almacenes WHERE id_almacen=movimientos_productos.id_almacen) as origen"),
              'descripcion','mov_tip','costo')
              ->where(function ($query) use ($almacen){
              if($almacen!='Todos'){
                  $query->where('id_almacen','=',$almacen); 
                }      
              }) 
               ->where(function ($query) use ($buspro){
                  $query->where('IdProducto','=',$buspro)
                  ->orWhere('IdProducto_rel',$buspro); 
                    
              }) 
              ->where('fecha_mov','>=',$fecin)
              ->where('fecha_mov','<=',$fecfin)
              ->orderby('fecha_mov','asc')
              ->orderby('tipo','asc')
              ->get();  

              
              foreach ($movimientos as $mov) {
               
                        $kardex_mov[] = array(
                      'fecha'=>$mov->fecha_mov,
                      'tdocod'=>$mov->tdocod,
                      'serie'=>$mov->serie,
                      'numero'=>$mov->numero,
                      'cliente'=>$mov->cliente,
                      'cantidad'=>$mov->cantidad,
                      'IdProducto'=>$mov->IdProducto,
                      'IdProducto_rel'=>$mov->IdProducto_rel,
                      'destino'=>$mov->destino,
                      'origen'=>$mov->origen,
                      'descripcion'=>$mov->descripcion,
                      'mov_tip'=>$mov->mov_tip,
                      'costo'=>$mov->costo,
                      'cod_tip_ope'=>$mov->cod_tip_ope,
                      'tipo'=>$mov->tipo,
                      'stock'=>'0',
                      'mov_lote'=>$mov->mov_lote,
                      'mov_vencimiento'=>$mov->mov_vencimiento

                        );
              

              }



           

           

              $array_productos[] = array(
                  'codigo'=> $p->procod,
                  'unidad'=>$p->umecod,
                  'producto'=>$p->pronom,
                  'movimientos' =>array(
                    $kardex_mov
                  )

              );


        }
 
    
    

 // dd($array_productos);

   $id_producto = array_column($kardex_mov, 'IdProducto_rel');
   $fec_mov = array_column($kardex_mov, 'fecha');
   $num_mov  = array_column($kardex_mov, 'tipo');
   $tipo_mov = array_column($kardex_mov, 'mov_tip');

   array_multisort($id_producto, SORT_ASC,$fec_mov, SORT_ASC,$num_mov, SORT_ASC, $tipo_mov, SORT_DESC, $kardex_mov);

      if($tipo=='2'){

        Excel::create('kardex_fisico', function($excel) use ($dat_suc,$movimientos,$productos,$negocios,$almacenes,$sucursal,$almacen,$dat_alm,$dat_emp,$fecin,$fecfin,$productoslista,$saldos_actuales,$IdProducto,$array_productos) {

                        $excel->sheet('kardex_fisico', function($sheet) use ($dat_suc,$movimientos,$productos,$negocios,$almacenes,$sucursal,$almacen,$dat_alm,$dat_emp,$fecin,$fecfin,$productoslista,$saldos_actuales,$IdProducto,$array_productos) {

                       
                            
                                  $sheet->loadView('empresas.reportes.kardexfisicoexcel',compact('dat_alm','dat_suc','movimientos','productos','negocios','almacenes','sucursal','almacen','dat_emp','fecin','fecfin','productoslista','saldos_actuales','IdProducto','array_productos'));
                          
                                

                        });

                    })->export('xlsx'); 

  

      }elseif($tipo=='1'){

        Excel::create('kardex_valorizado', function($excel) use ($dat_suc,$movimientos,$productos,$negocios,$almacenes,$sucursal,$almacen,$dat_alm,$dat_emp,$fecin,$fecfin,$productoslista,$saldos_actuales,$IdProducto,$array_productos) {

                        $excel->sheet('kardex_valorizado', function($sheet) use ($dat_suc,$movimientos,$productos,$negocios,$almacenes,$sucursal,$almacen,$dat_alm,$dat_emp,$fecin,$fecfin,$productoslista,$saldos_actuales,$IdProducto,$array_productos) {

                              
                                  $sheet->loadView('empresas.reportes.kardexvalorizadoexcel',compact('dat_alm','dat_suc','movimientos','productos','negocios','almacenes','sucursal','almacen','dat_emp','fecin','fecfin','productoslista','saldos_actuales','IdProducto','array_productos'));
                          
                                

                        });

                    })->export('xlsx'); 

         
      }
     


                        



 }


  public function listar_ajuste(Request $request){

    $negocios = DB::tABLE('empresa_negocios')->get();

    $almacenes = DB::tABLE('almacenes')->where('id_empresa_negocio',$negocios->first()->id_empresa_negocio)->get();

    $almacen = $request->get('almacen');
    $sucursal = $request->get('sucursal');
    $tipo = $request->get('docomp');
    $fecin = $request->get('fecin');
    $fecfin = $request->get('fecfin');
    $IdProducto = $request->get('IdProducto');
    $movimientos ='';

     
    $productos = DB::tABLE('productos')->select('IdProducto','pronom','procod','costo_total','flete','umecod')
    ->where('tipo','=','1')
    ->where('promocion','!=','2')
    //->where('id_empresa_negocio',$sucursal)
    ->orderby('pronom','asc')
    ->get();


    $dat_alm = DB::tABLE('almacenes')->where('id_almacen',$almacen)->first();
    $dat_suc = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',$sucursal)->first();
    $dat_emp = DB::tABLE('empresa')->where('IdEmpresa',Auth::user()->IdEmpresa)->first();

    if(!empty($IdProducto)){

      $movimientos = DB::tABLE('movimientos_productos')
      ->leftjoin('productos','productos.IdProducto','movimientos_productos.IdProducto_rel')
      ->leftjoin('cat_movimiento','cat_movimiento.cat_mov_id','movimientos_productos.mov_tip')
       ->where(function ($query) use ($IdProducto){
          if($IdProducto!='Todos'){
             $query->where('movimientos_productos.IdProducto','=',$IdProducto)
            ->orwhere('movimientos_productos.IdProducto_rel','=',$IdProducto); 
          }
           
             
      }) 
      ->where('movimientos_productos.id_empresa_negocio',$sucursal)
      ->where(function ($query) use ($almacen) {
          if($almacen!='Todos'){
              $query->where('movimientos_productos.id_almacen','=',$almacen); 
          }      
      }) 
      ->where('descripcion','AJUSTE')
      ->where('fecha_mov','>=',$fecin)
      ->where('fecha_mov','<=',$fecfin)
      ->orderby('fecha_mov','desc')
      ->get();

    }


      return view('empresas.reportes.listar_ajuste',compact('dat_alm','dat_suc','movimientos','productos','negocios','almacenes','sucursal','almacen','dat_suc','dat_alm','dat_emp','fecin','fecfin','productos','IdProducto'));
 

 }



 public function buscarkardex(Request $request){

    $negocios = DB::tABLE('empresa_negocios')->get();

    $almacenes = DB::tABLE('almacenes')->where('id_empresa_negocio',$negocios->first()->id_empresa_negocio)->get();

    $almacen = $request->get('almacen');
    $sucursal = $request->get('sucursal');
    $tipo = $request->get('docomp');
    $fecin = $request->get('fecin');
    $fecfin = $request->get('fecfin');
    $IdProducto = $request->get('IdProducto');


     
    $productoslista = DB::tABLE('productos')->select('IdProducto','pronom','procod','costo_total','flete','umecod')
    ->where('tipo','=','1')
    ->where('promocion','!=','2')
    //->where('id_empresa_negocio',$sucursal)
    ->orderby('pronom','asc')
    ->get();



      $saldo_anterior =0;


      $productos = DB::tABLE('productos')->select('IdProducto','pronom','procod','costo_total','flete','umecod',DB::raw("(SELECT COUNT(*) FROM movimientos_productos WHERE IdProducto_rel=productos.IdProducto Limit 1) as contar"))
      ->where('tipo','=','1')
      ->where(function ($query) use ($IdProducto){
          if($IdProducto!='Todos'){
             $query->where('IdProducto','=',$IdProducto); 
          }      
      }) 
      ->having('contar','>','0')
      ->where('id_empresa_negocio',$sucursal)
      ->orderby('IdProducto','asc')
      ->get();

      
    
      $dat_alm = DB::tABLE('almacenes')->where('id_almacen',$almacen)->first();
      $dat_suc = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',$sucursal)->first();
      $dat_emp = DB::tABLE('empresa')->where('IdEmpresa',Auth::user()->IdEmpresa)->first();

      $saldos_actuales = [];
      $saldo_anterior=0;
      $kardex =[];
      $kardex_mov=[];
      $array_productos = [];


     
          foreach($productos as $p){

               $kardex_mov = [];
               $ingresos = 0;
               $salidas = 0;
               $saldo_anterior=0;

              $buspro = $p->IdProducto;


              $ingresos = DB::tABLE('movimientos_productos')->select('cantidad')
             ->where('mov_tip','I')
             ->WHERE('fecha_mov','<',$fecin)
             ->where('IdProducto_rel',$buspro)
             ->where('id_empresa_negocio',$sucursal)
             ->where(function ($query) use ($almacen) {
                    if($almacen!='Todos'){
                      $query->where('id_almacen','=',$almacen); 
                    }      
                }) 
             ->sum('cantidad');

             $salidas = DB::tABLE('movimientos_productos')->select('cantidad')
             ->where('mov_tip','E')
              ->WHERE('fecha_mov','<',$fecin)
             ->where('IdProducto_rel',$buspro)
             ->where('id_empresa_negocio',$sucursal)
             ->where(function ($query) use ($almacen) {
                    if($almacen!='Todos'){
                      $query->where('id_almacen','=',$almacen); 
                    }      
                }) 
             ->sum('cantidad');

    
              $saldo_anterior = $ingresos-$salidas;

              if($saldo_anterior!=0){
          
                   $kardex_mov[] = array(
                      'fecha'=>$fecin,
                      'tdocod'=>'',
                      'serie'=>'',
                      'numero'=>'',
                      'cliente'=>'SALDO_ANTERIOR',
                      'cantidad'=>$saldo_anterior,
                      'IdProducto'=>$buspro,
                      'IdProducto_rel'=>$buspro,
                      'destino'=>'',
                      'origen'=>'',
                      'descripcion'=>'SALDO ANTERIOR',
                      'cod_tip_ope'=>'16',
                      'mov_tip'=>'I',
                      'tipo'=>'0',
                      'costo'=>'',
                      'stock'=>'0',
                      'mov_lote'=>'',
                      'mov_vencimiento'=>''
                    );
              }


              $movimientos = DB::tABLE('movimientos_productos')
              ->select('mov_vencimiento','mov_lote','cod_tip_ope','tipo','fecha_mov','tdocod','serie','numero','cliente','stock_inicial','cantidad','stock','IdProducto_rel','IdProducto','descripcion','mov_tip','costo')
              ->where(function ($query) use ($almacen){
              if($almacen!='Todos'){
                  $query->where('id_almacen','=',$almacen); 
                }      
              }) 
               ->where(function ($query) use ($buspro){
                  $query->where('IdProducto','=',$buspro)
                  ->orWhere('IdProducto_rel',$buspro); 
                    
              }) 
              ->where('fecha_mov','>=',$fecin)
              ->where('fecha_mov','<=',$fecfin)
              ->orderby('fecha_mov','asc')
              ->orderby('tipo','asc')
              ->get();  

            

              foreach ($movimientos as $mov) {
                
                      $kardex_mov[] = array(
                      'fecha'=>$mov->fecha_mov,
                      'tdocod'=>$mov->tdocod,
                      'serie'=>$mov->serie,
                      'numero'=>$mov->numero,
                      'cliente'=>$mov->cliente,
                      'cantidad'=>$mov->cantidad,
                      'IdProducto'=>$mov->IdProducto,
                      'IdProducto_rel'=>$mov->IdProducto_rel,
                      'destino'=>'',
                      'origen'=>'',
                      'descripcion'=>$mov->descripcion,
                      'mov_tip'=>$mov->mov_tip,
                      'costo'=>$mov->costo,
                      'cod_tip_ope'=>$mov->cod_tip_ope,
                      'tipo'=>$mov->tipo,
                      'stock'=>'0',
                      'mov_lote'=>$mov->mov_lote,
                      'mov_vencimiento'=>$mov->mov_vencimiento

                    );
                
              }



           

           

              $array_productos[] = array(
                  'codigo'=> $p->procod,
                  'unidad'=>$p->umecod,
                  'producto'=>$p->pronom,
                  'movimientos' =>array(
                    $kardex_mov
                  )

              );


        }
 
    
     

 //dd($array_productos);

   $id_producto = array_column($kardex_mov, 'IdProducto_rel');
   $fec_mov = array_column($kardex_mov, 'fecha');
   $num_mov  = array_column($kardex_mov, 'tipo');
   $tipo_mov = array_column($kardex_mov, 'mov_tip');

   array_multisort($id_producto, SORT_ASC,$fec_mov, SORT_ASC,$num_mov, SORT_ASC, $tipo_mov, SORT_DESC, $kardex_mov);


/*  foreach($array_productos as  $detalles){

  
      $i=0;
      $stock=0;
      $saldo=0;


    foreach($detalles['movimientos']  as $mov){
     
      $contar = count($mov);

      for($j=0;$j<$contar;$j++){

           if($mov[$j]['mov_tip']=='I'){
              $stock = $saldo+$mov[$j]['cantidad'];
           }elseif($mov[$j]['mov_tip']=='E'){
              $stock = $saldo-$mov[$j]['cantidad'];
           }

     

           $detalles[$mov[$j]['stock']]=$stock;
                            

           
            $saldo = $stock;

      echo $stock.'<br>';
            $i = $i+1;
      
     } 

   }




  }

  dd($array_productos);*/



//dd('listo');

//dd($IdProducto);
//dd($movimientos);
     



     
 

      if($tipo=='2'){
         return view('empresas.reportes.kardexfisico',compact('dat_alm','dat_suc','movimientos','productos','negocios','almacenes','sucursal','almacen','dat_suc','dat_alm','dat_emp','fecin','fecfin','productoslista','saldos_actuales','kardex','IdProducto','array_productos'));
      }elseif($tipo=='1'){
          return view('empresas.reportes.kardexvalorizado',compact('dat_alm','dat_suc','movimientos','productos','negocios','almacenes','sucursal','almacen','dat_suc','dat_alm','dat_emp','fecin','fecfin','productoslista','saldos_actuales','kardex','IdProducto','array_productos'));
      }
     



 }

   


    public function cuentascobrar(Request $request){


          $clientes = DB::tABLE('cliente')->where('rucemp',Auth::user()->IdEmpresa)->get();

          $data_emp = DB::tABLE('empresa')->where('IdEmpresa',Auth::user()->IdEmpresa)->first();

          $data_neg = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first();

           $vendedores = DB::tABLE('users')
            ->join('role_user','role_user.user_IdUsuario','users.IdUsuario')
           // ->where('role_id','5')
             ->where('IdEmpresa',Auth::user()->IdEmpresa)
            ->get();

            $fecin = $request->get('fecin');
            $fecfin = $request->get('fecfin');
            $placa = $request->get('placa');
            $tipdoc = $request->get('tipdoc');
            $numdoc = $request->get('numdoc');
            $clicod = $request->get('clicod');
            $estado = $request->get('estado');
            $tipo = $request->get('tipfec');

            if(empty($tipo)){
                $tipo='0';
            }

            if(empty($fecin)){

                $fecin = now()->modify('first day of this month')->format('Y-m-d');
                $fecfin = now()->modify('last day of this month')->format('Y-m-d');
            }

            $cuentas = DB::tABLE('cuentas_cobrar')
            ->leftjoin('cpe_cabecera','cpe_cabecera.IdCpe_cabecera','cuentas_cobrar.IdCpe_cabecera')
            ->leftjoin('cliente','cliente.clicod','cuentas_cobrar.clicod')
            ->where('cuentas_cobrar.id_empresa_negocio',Auth::user()->id_empresa_negocio)
         /*   ->where(function($query) use($clicod) {
                   
                    
                     $query->where('cpe_cabecera.clicod',$clicod);

                    
                   
             })*/
     /*     ->where(function($query1) use ($fecin, $fecfin,$tipo)
          {
              if($tipo=='0'){
                $query1->where('fec_ven','>=',$fecin)
                      ->where('fec_ven','<=',$fecfin);
              }elseif($tipo=='1'){


                $query1->where('ccafem','>=',$fecin)
                      ->where('ccafem','<=',$fecfin);
              }
            
          })*/
            ->orderby('cuentas_cobrar.cue_cob_id','desc')
            ->get();


   /*       $detalles = DB::tABLE('cuentas_cobrar')
          ->leftjoin('cpe_cabecera','cpe_cabecera.IdCpe_cabecera','cuentas_cobrar.IdCpe_cabecera')
          ->leftjoin('cpe_detalle','cpe_detalle.IdCpe_cabecera','cpe_cabecera.IdCpe_cabecera')
          ->leftjoin('cliente','cliente.clicod','cuentas_cobrar.clicod')
          ->leftjoin('cuentasbancarias','cuentasbancarias.cuen_ban_id','cuentas_cobrar.cuen_ban_id')
          ->leftjoin('tipo_cuentas','tipo_cuentas.tip_cuen_id','cuentasbancarias.tip_cuen_id')
          ->leftjoin('moneda','moneda.moncod','cuentasbancarias.moncod')
          ->leftjoin('bancos','bancos.ban_id','cuentasbancarias.ban_id')
          ->where('cuentas_cobrar.id_empresa_negocio',Auth::user()->id_empresa_negocio)
          ->where(function($query) use($clicod) {
           
              $query->where('cpe_cabecera.clicod',$clicod);

                 
           })
          ->where(function($query1) use ($fecin, $fecfin,$tipo)
          {
              if($tipo=='0'){
                $query1->where('fec_ven','>=',$fecin)
                      ->where('fec_ven','<=',$fecfin);
              }elseif($tipo=='1'){


                $query1->where('ccafem','>=',$fecin)
                      ->where('ccafem','<=',$fecfin);
              }
            
          })
          ->orderby('cuentas_cobrar.cue_cob_id','desc')
          ->get();
*/
      /*  $detallecob = DB::tABLE('cuentas_cobrar_detalle as ccd')
        ->leftjoin('cuentasbancarias','cuentasbancarias.cuen_ban_id','ccd.cuen_ban_id')
        ->leftjoin('tipo_cuentas','tipo_cuentas.tip_cuen_id','cuentasbancarias.tip_cuen_id')
        ->leftjoin('moneda','moneda.moncod','cuentasbancarias.moncod')
        ->leftjoin('bancos','bancos.ban_id','cuentasbancarias.ban_id')
        ->orderby('ccd.cue_cob_det_id','desc')
        ->get();
       */
 
        return view('empresas.reportes.cuentascobrar',compact('vendedores','data_emp','data_neg','clientes','cuentas','fecin','fecfin','clicod','tipo'));
    }

    public function imprimirreporte(Request $request){

        $tipo = $request->get('docomp');
        $fecin = $request->get('fecin');
        $fecfin = $request->get('fecfin');

        $rucemp = Auth::user()->IdEmpresa;
        $empresa = Empresa::findOrFail($rucemp);
        $empresanegocios = EmpresaNegocios::findOrFail(Auth::user()->id_empresa_negocio);

        $totalventa=0;

         switch ($tipo){
            case 1:

                $comprobantes = cpe_cabecera::select('cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','monnom as moneda','ccaitv as total','cpe_cabecera.ccabaj as baja','ccasunrescod','tdides as documentoidentidad','ccandi as numerodocumento','cpe_cabecera.tdocod')
                ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
                ->where('cpe_cabecera.id_empresa_negocio',Auth::user()->id_empresa_negocio)
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                ->where(function ($query){
                    $query->where('tdocod','01')
                          ->orwhere('tdocod','03')
                          ->orWhere('tdocod','13');
                })
                ->where(function ($query1){
          
                    $query1->whereNull('cpe_cabecera.ccabaj')
                          ->orwhere('cpe_cabecera.ccabaj','');
                    
                })
                ->orderBy('IdCpe_cabecera','desc')->get();

                foreach($comprobantes as $comprobante){
  
                    if(!empty($comprobante->baja)){
                        $totalventa = $totalventa + $comprobante->total;
                      
                    }
                }
 
                  $impresoras = DB::tABLE('configuracion_impresoras')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->where('predeterminado','1')->first();
                  
                 try { 

                          $connector = new WindowsPrintConnector("smb://".$impresoras->ruta);

                         
                            $printer = new Printer($connector);
                            $printer->setJustification(Printer::JUSTIFY_CENTER);
                            if(file_exists($empresa->LogEmpresa)){
                                  $logo = EscposImage::load(public_path().'/'.$empresa->LogEmpresa,false);
                                  $printer->bitImage($logo);
                            }
                            $printer->setFont(Printer::FONT_A);
                            $printer->text("\n".$empresa->NomEmpresa."\n");
                            $printer->text($empresanegocios->cabecera."\n");
                         
                            
                            $printer->setJustification(Printer::JUSTIFY_LEFT);
                            $printer->text("COMPROBANTES ".$fecin." - ".$fecfin."\n");
                            $printer->text("________________________________________________"."\n");
                            foreach ($detalle as $det) {
                             
                            $printer->text($comprobantes->tdocod."-".$comprobantes->serie."-".$comprobantes->numero."  ".$comprobantes->ccafem."  ".$comprobantes->cliente." ".$comprobantes->total."\n");
                            
                            }


                            $printer->text("TOTAL: "."                               ".$totalventa."\n");
                           $printer->text("\n");
                        


                            $printer->feed();
                             
                         
                            $printer->cut();
                             
                         
                            $printer->pulse();
                             
                            
                            $printer->close();
                          }catch (\Exception $e) {

                          
                          }

                    return Redirect::to('/imprimirreportes');

            break;

            case 2:

                $productos = cpe_cabecera::select(DB::RAW('sum(cpe_detalle.cdecan) as cantidad'),'cdedes','cdepuni','procod','umecod')
                ->leftjoin('cpe_detalle','cpe_detalle.IdCpe_cabecera','cpe_cabecera.IdCpe_cabecera')
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                ->where('cpe_cabecera.moncod','=','PEN')
                ->where('cpe_cabecera.id_empresa_negocio',Auth::user()->id_empresa_negocio)
                ->whereNull('cpe_cabecera.ccabaj')
                ->orderby('cantidad','desc')
                ->groupby('procod','cdepuni','cdedes','umecod')
                ->get();


                 try { 

                          $connector = new WindowsPrintConnector("smb://".$impresoras->ruta);

                         
                            $printer = new Printer($connector);
                            $printer->setJustification(Printer::JUSTIFY_CENTER);
                            $logo = EscposImage::load('logo_rest.png',false);
                            $printer->bitImage($logo);
                            $printer->setFont(Printer::FONT_A);
                            $printer->text("\n".$empresa->NomEmpresa."\n");
                            $printer->text($empresanegocios->cabecera."\n");
                         
                            
                            $printer->setJustification(Printer::JUSTIFY_LEFT);
                            $printer->text("RANKING PRODUCTOS ".$fecin." - ".$fecfin."\n");
                            $printer->text("CONCEPTO                             CANTIDAD"."\n");
                            $printer->text("________________________________________________"."\n");
                            foreach ($productos as $det) {
                             
                            $printer->text($det->cdedes."                 ".$det->cantidad."\n");
                            
                            }
                           $printer->text("\n");
                        


                            $printer->feed();
                             
                         
                            $printer->cut();
                             
                         
                            $printer->pulse();
                             
                            
                            $printer->close();
                          }catch (\Exception $e) {

                          
                          }


                        return Redirect::to('/imprimirreportes');

            break;


            case 3:

               $productos= DB::tABLE('productos as p')
                ->leftjoin('moneda as m','p.moncod','=','m.moncod')
                ->leftjoin('unidad_medida as u','p.umecod','=','u.umecod')
                ->select('p.stockinicial','p.stock','p.procod','p.pronom','m.monnom','u.umenom','p.provun','p.propun','p.proest','IdProducto','p.costo','p.propun','marca','modelo')
                ->where('p.id_empresa_negocio',Auth::user()->id_empresa_negocio)
                 ->where(function ($query) {
                     $query->where('promocion','0')
                           ->orWhere('promocion','4');
                 })
                ->orderby('stock','asc')
                ->get();


                 try { 

                          $connector = new WindowsPrintConnector("smb://".$impresoras->ruta);

                         
                            $printer = new Printer($connector);
                            $printer->setJustification(Printer::JUSTIFY_CENTER);
                            $logo = EscposImage::load('logo_rest.png',false);
                            $printer->bitImage($logo);
                            $printer->setFont(Printer::FONT_A);
                            $printer->text("\n".$empresa->NomEmpresa."\n");
                            $printer->text($empresanegocios->cabecera."\n");
                         
                            
                            $printer->setJustification(Printer::JUSTIFY_LEFT);
                            $printer->text("STOCK ACTUAL PRODUCTOS"."\n");
                            $printer->text("PRODUCTO                             STOCK"."\n");
                            $printer->text("________________________________________________"."\n");
                            foreach ($productos as $det) {
                             
                            $printer->text($det->pronom."                ".$det->stock."\n");
                            
                            }
                           $printer->text("\n");
                        


                            $printer->feed();
                             
                         
                            $printer->cut();
                             
                         
                            $printer->pulse();
                             
                            
                            $printer->close();
                          }catch (\Exception $e) {

                          
                          }


                    return Redirect::to('/imprimirreportes');

            break;


   

        }
    

    }
    
     public function reportestock(Request $request){


        $fecin = Carbon::now()->startOfMonth()->format('Y-m-d');
        $fecfin = Carbon::now()->endOfMonth()->format('Y-m-d');
        $docomp = $request->get('docomp');
        $clientes = DB::tABLE('cliente')->where('rucemp',Auth::user()->IdEmpresa)->get();
        $clicod = $request->get('clicod');
        $sucursal = $request->get('sucursal');
        $almacen = $request->get('almacen');

        $negocios = DB::tABLE('empresa_negocios')->get();
        if(!empty($sucursal)){
          $almacenes = DB::tABLE('almacenes')->where('id_empresa_negocio',$sucursal)->get();
        }else{
          $almacenes = DB::tABLE('almacenes')->where('id_empresa_negocio',$negocios->first()->id_empresa_negocio)->get();
        }
        return view('empresas.reportes.indexstock',compact('clientes','clicod','sucursal','negocios','fecin','fecfin','docomp','almacenes','almacen'));

    }

    public function reportepantalla(){

        $vendedores = DB::tABLE('users')
        ->leftjoin('role_user','role_user.user_IdUsuario','users.IdUsuario')
        // ->where('role_id','5')
        ->where('IdEmpresa',Auth::user()->IdEmpresa)
        ->get();

        return view('empresas.reportes.index',compact('vendedores'));
    }

    public function reportecompra(request $request){


        $rucemp = trim(Auth::user()->IdEmpresa);
        $negocios = EmpresaNegocios::get();
        $sucursal = $request->get('sucursal');
        $proveedores = DB::tABLE('proveedor')->get();
        $proveedor = $request->get('proveedor');
        $opbtn = $request->get('opbtn');
         $tipo = $request->get('tipo');

        $fecin = $request->get('fecin');
        $fecfin = $request->get('fecfin');

        if(empty($fecin)){

          $fecin = now()->modify('first day of this month')->format('Y-m-d');
          $fecfin = now()->modify('last day of this month')->format('Y-m-d');
          $opbtn ='0';

        }

        $oldfecin = strtotime($fecin);

        $oldfecfin = strtotime($fecfin);

        $mesfecin = date('m',$oldfecin);

        $mesfecfin = date('m',$oldfecfin);

   
        $diferencia = $mesfecfin - $mesfecin;
        
        if($diferencia=='0'){

          $diferencia='1';

        }

      
        if($tipo =='1'){

          $compras = DB::tABLE('compras_cabecera')
              ->leftjoin('proveedor','compras_cabecera.prov_id','proveedor.prov_id')
              ->join('moneda','compras_cabecera.mon_id','moneda.moncod')
              ->join('tipo_documento','compras_cabecera.tdocod','tipo_documento.tdocod')
              
                ->where('compras_cabecera.com_fec','>=',$fecin)
                ->where('compras_cabecera.com_fec','<=',$fecfin)
              ->where('est_compra','Registrado')
                ->where(function ($query) use ($sucursal){
                  if(!$sucursal !=0 && !empty($sucursal)){
                      $query->where('compras_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
              })
              ->where(function ($query1) use ($proveedor){
                  if($proveedor !=0){
                      $query1->where('compras_cabecera.prov_id',$proveedor);
                  }
                  
              })
                    
              ->where(function ($query6) use ($almacen) {
                  if($almacen !=0 && !empty($almacen)){
                      $query6->where('compras_cabecera.id_almacen',$almacen);
                  }                
                }) 
            
              ->where('compras_cabecera.tdocod','!=','80')
              ->orderby('com_cab_id','desc')
              ->get();

        }elseif($tipo=='2'){
              $compras = DB::tABLE('compras_cabecera')
              ->leftjoin('proveedor','compras_cabecera.prov_id','proveedor.prov_id')
              ->leftjoin('compras_detalle','compras_cabecera.com_cab_id','compras_detalle.com_cab_id')
              ->leftjoin('productos','productos.IdProducto','compras_detalle.pro_id')
              ->join('moneda','compras_cabecera.mon_id','moneda.moncod')
              ->join('tipo_documento','compras_cabecera.tdocod','tipo_documento.tdocod')
              
              ->where('est_compra','Registrado')
                ->where('compras_cabecera.com_fec','>=',$fecin)
                ->where('compras_cabecera.com_fec','<=',$fecfin)
                ->where(function ($query) use ($sucursal){
                  if(!is_null($sucursal)){
                      $query->where('compras_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
              })
              ->where(function ($query1) use ($proveedor){
                  if($proveedor !='Todos'){
                      $query1->where('compras_cabecera.prov_id',$proveedor);
                  }
                  
              })
              ->where('compras_cabecera.tdocod','!=','80')
              ->orderby('compras_cabecera.com_cab_id','desc')
              ->get();

        }elseif($tipo=='3'){

             $compras = DB::tABLE('compras_cabecera')->select(DB::RAW('sum(compras_detalle.cantidad) as cantidad'),'pronom','pre_uni','procod','ume_cod')
                ->leftjoin('compras_detalle','compras_detalle.com_cab_id','compras_cabecera.com_cab_id')
                ->leftjoin('productos','productos.IdProducto','compras_detalle.pro_id')
                ->where('compras_cabecera.com_fec','>=',$fecin)
                ->where('compras_cabecera.com_fec','<=',$fecfin)
                ->where('est_compra','Registrado')
                ->where(function ($query) use ($sucursal){
                  if(!is_null($sucursal)){
                      $query->where('compras_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                })
                ->where(function ($query1) use ($proveedor){
                  if($proveedor !='Todos'){
                      $query1->where('compras_cabecera.prov_id',$proveedor);
                  }
                  
                })
                ->orderby('cantidad','desc')
                ->groupby('procod','pre_uni','pronom','ume_cod')
                ->get();

        }else{
            $compras = DB::tABLE('compras_cabecera')
              ->leftjoin('proveedor','compras_cabecera.prov_id','proveedor.prov_id')
              ->join('moneda','compras_cabecera.mon_id','moneda.moncod')
              ->join('tipo_documento','compras_cabecera.tdocod','tipo_documento.tdocod')
              
              ->where('est_compra','Registrado')
                ->where('compras_cabecera.com_fec','>=',$fecin)
                ->where('compras_cabecera.com_fec','<=',$fecfin)
                ->where(function ($query) use ($sucursal){
                  if(!is_null($sucursal)){
                      $query->where('compras_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
              })
             
              ->where('compras_cabecera.tdocod','!=','80')
              ->orderby('com_cab_id','desc')
              ->get();
        }
             


        if($opbtn =='2'){

         
                        Excel::create('Reporte_Compras', function($excel) use ($compras,$negocios,$sucursal,$proveedores,$proveedor,$fecin,$fecfin,$tipo,$diferencia) {

                        $excel->sheet('Comprobantes', function($sheet) use ($compras,$negocios,$sucursal,$proveedores,$proveedor,$fecin,$fecfin,$tipo,$diferencia) {

                            $sheet->setColumnFormat(array(
                                    'A' => 'dd/mm/yy',
                                    'G' => '0.00',
                                    'H' => '0.00',
                                    'I' => '0.00',
                                    'J' => '0.00',

                                    
                                ));
                                
                                if($tipo=='1'){
                                  $sheet->loadView('empresas.reportes.reportecomprasexcel',compact('compras','negocios','sucursal','proveedores','proveedor','fecin','fecfin','diferencia'));
                                }elseif($tipo=='2'){
                                  $sheet->loadView('empresas.reportes.reportecomprasdetalladoexcel',compact('compras','negocios','sucursal','proveedores','proveedor','fecin','fecfin','diferencia'));
                                }elseif($tipo=='3'){
                                  $sheet->loadView('empresas.reportes.reportecompraspromedioexcel',compact('compras','negocios','sucursal','proveedores','proveedor','fecin','fecfin','diferencia'));
                                }

                                

                        });

                    })->export('xlsx'); 

          }elseif($opbtn=='1' || empty($opbtn)){
              

              return view('empresas.reportes.reportecompras',compact('compras','negocios','sucursal','proveedores','proveedor','fecin','fecfin','tipo','diferencia'));

          }
                   

       


      
    }

      public function reportealbergues(request $request){


        $rucemp = trim(Auth::user()->IdEmpresa);
        $negocios = EmpresaNegocios::get();
        $sucursal = $request->get('sucursal');
        $proveedores = DB::tABLE('proveedor')->get();
        $proveedor = $request->get('proveedor');
        $opbtn = $request->get('opbtn');
         $tipo = $request->get('tipo');

        $fecin = $request->get('fecin');
        $fecfin = $request->get('fecfin');

        if(empty($fecin)){

          $fecin = now()->modify('first day of this month')->format('Y-m-d');
          $fecfin = now()->modify('last day of this month')->format('Y-m-d');
          $opbtn ='0';

        }

        $oldfecin = strtotime($fecin);

        $oldfecfin = strtotime($fecfin);

        $mesfecin = date('m',$oldfecin);

        $mesfecfin = date('m',$oldfecfin);

   
        $diferencia = $mesfecfin - $mesfecin;
        
        if($diferencia=='0'){

          $diferencia='1';

        }

      
        if($tipo =='1'){

              $compras = DB::tABLE('pedido_servicio_cab')
              ->leftjoin('productos','productos.IdProducto','pedido_servicio_cab.IdProducto')
              ->join('servicios','servicios.ser_cod','pedido_servicio_cab.ser_cod')
              ->where('ped_ser_fec','>=',$fecin)
              ->where('ped_ser_fec','<=',$fecfin)
              ->where('pedido_servicio_cab.id_empresa_negocio',$sucursal)
              ->orderby('ped_ser_fec','desc')
              ->get();

              $grup_serv= DB::tABLE('pedido_servicio_cab')->select('ped_ser_fec','ser_nom',DB::RAW('sum(total) as cantidad'))
              ->leftjoin('productos','productos.IdProducto','pedido_servicio_cab.IdProducto')
              ->join('servicios','servicios.ser_cod','pedido_servicio_cab.ser_cod')
              ->where('ped_ser_fec','>=',$fecin)
              ->where('ped_ser_fec','<=',$fecfin)
              ->where('pedido_servicio_cab.id_empresa_negocio',$sucursal)
              ->orderby('ped_ser_fec','desc')
              ->groupby('pedido_servicio_cab.ped_ser_fec')
              ->groupby('pedido_servicio_cab.ser_cod')
              ->get();

               $grup_plat= DB::tABLE('pedido_servicio_cab')->select('ped_ser_fec','ser_nom','pronom',DB::RAW('sum(total) as cantidad'))
              ->leftjoin('productos','productos.IdProducto','pedido_servicio_cab.IdProducto')
              ->join('servicios','servicios.ser_cod','pedido_servicio_cab.ser_cod')
              ->where('ped_ser_fec','>=',$fecin)
              ->where('ped_ser_fec','<=',$fecfin)
              ->where('pedido_servicio_cab.id_empresa_negocio',$sucursal)
              ->orderby('ped_ser_fec','desc')
              ->groupby('pedido_servicio_cab.ped_ser_fec')
              ->groupby('pedido_servicio_cab.IdProducto')
              ->get();


           /*  $grup_ins = DB::tABLE('pedido_servicio_cab')->select('ped_ser_fec','ser_nom','productos.pronom','recetas.pronom as insumo',DB::RAW('sum(total) as cantidad'),DB::RAW('sum(total*recetas.rec_cant) as cantins'))
              ->leftjoin('productos as p','productos.IdProducto','pedido_servicio_cab.IdProducto')
              ->leftjoin('recetas','recetas.prod_id','productos.IdProducto')
              ->join('servicios','servicios.ser_cod','pedido_servicio_cab.ser_cod')
              ->where('ped_ser_fec','>=',$fecin)
              ->where('ped_ser_fec','<=',$fecfin)
              ->where('pedido_servicio_cab.id_empresa_negocio',$sucursal)
              ->orderby('ped_ser_fec','desc')
              ->groupby('pedido_servicio_cab.ped_ser_fec')
              ->groupby('pedido_servicio_cab.IdProducto')
              ->groupby('pedido_servicio_cab.prod_id')
              ->get();*/

        }elseif($tipo=='2'){
              $compras = DB::tABLE('compras_cabecera')
              ->leftjoin('proveedor','compras_cabecera.prov_id','proveedor.prov_id')
              ->leftjoin('compras_detalle','compras_cabecera.com_cab_id','compras_detalle.com_cab_id')
              ->leftjoin('productos','productos.IdProducto','compras_detalle.pro_id')
              ->join('moneda','compras_cabecera.mon_id','moneda.moncod')
              ->join('tipo_documento','compras_cabecera.tdocod','tipo_documento.tdocod')
              
              ->where('est_compra','Registrado')
                ->where('compras_cabecera.com_fec','>=',$fecin)
                ->where('compras_cabecera.com_fec','<=',$fecfin)
                ->where(function ($query) use ($sucursal){
                  if(!is_null($sucursal)){
                      $query->where('compras_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
              })
              ->where(function ($query1) use ($proveedor){
                  if($proveedor !='Todos'){
                      $query1->where('compras_cabecera.prov_id',$proveedor);
                  }
                  
              })
              ->where('compras_cabecera.tdocod','!=','80')
              ->orderby('compras_cabecera.com_cab_id','desc')
              ->get();

        }else{
            $compras = DB::tABLE('compras_cabecera')
              ->leftjoin('proveedor','compras_cabecera.prov_id','proveedor.prov_id')
              ->join('moneda','compras_cabecera.mon_id','moneda.moncod')
              ->join('tipo_documento','compras_cabecera.tdocod','tipo_documento.tdocod')
              
              ->where('est_compra','Registrado')
                ->where('compras_cabecera.com_fec','>=',$fecin)
                ->where('compras_cabecera.com_fec','<=',$fecfin)
                ->where(function ($query) use ($sucursal){
                  if(!is_null($sucursal)){
                      $query->where('compras_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
              })
             
              ->where('compras_cabecera.tdocod','!=','80')
              ->orderby('com_cab_id','desc')
              ->get();
        }
             


        if($opbtn =='2'){

         
                        Excel::create('Reporte_Compras', function($excel) use ($compras,$negocios,$sucursal,$proveedores,$proveedor,$fecin,$fecfin,$tipo,$diferencia) {

                        $excel->sheet('Comprobantes', function($sheet) use ($compras,$negocios,$sucursal,$proveedores,$proveedor,$fecin,$fecfin,$tipo,$diferencia) {

                            $sheet->setColumnFormat(array(
                                    'A' => 'dd/mm/yy',
                                    'G' => '0.00',
                                    'H' => '0.00',
                                    'I' => '0.00',
                                    'J' => '0.00',

                                    
                                ));
                                
                                if($tipo=='1'){
                                  $sheet->loadView('empresas.reportes.reportecomprasexcel',compact('compras','negocios','sucursal','proveedores','proveedor','fecin','fecfin','diferencia'));
                                }elseif($tipo=='2'){
                                  $sheet->loadView('empresas.reportes.reportecomprasdetalladoexcel',compact('compras','negocios','sucursal','proveedores','proveedor','fecin','fecfin','diferencia'));
                                }elseif($tipo=='3'){
                                  $sheet->loadView('empresas.reportes.reportecompraspromedioexcel',compact('compras','negocios','sucursal','proveedores','proveedor','fecin','fecfin','diferencia'));
                                }

                                

                        });

                    })->export('xlsx'); 

          }elseif($opbtn=='1' || empty($opbtn)){
              

              return view('empresas.reportes.reportealbergues',compact('compras','negocios','sucursal','proveedores','proveedor','fecin','fecfin','tipo','diferencia','grup_serv','grup_plat','grup_ins'));

          }
                   

       


      
    }



    public function ReporteComprobantes(Request $request)
    {

      
        $clientes = DB::tABLE('cliente')->where('rucemp',Auth::user()->IdEmpresa)->get();
        $clicod = $request->get('clicod');

        $fecin = $request->get('fecin');
        $fecfin = $request->get('fecfin');
        $empresa = Empresa::findOrFail(Auth::user()->IdEmpresa);
        $IdEmpresa = Auth::user()->IdEmpresa;
        $docomp = $request->get('docomp');
        $nomempresa = Empresa::FindOrFail($IdEmpresa);
        $datoempresa = $nomempresa->NomEmpresa;

        $vendedor = $request->get('vendedor');
        
        $vendedores = DB::tABLE('users')
        ->leftjoin('role_user','role_user.user_IdUsuario','users.IdUsuario')
       // ->where('role_id','5')
        ->where('IdEmpresa',Auth::user()->IdEmpresa)
        ->get();

        $dat_ven = DB::tABLE('users')->where('IdUsuario',$request->get('vendedor'))->first();

       
        switch ($docomp){
            case 1:
    
        
        $comprobantes = cpe_cabecera::select('cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','monnom as moneda','ccaitv as total','cpe_cabecera.ccabaj as baja','ccasunrescod','tdides as documentoidentidad','ccandi as numerodocumento')
                    ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                    ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                    ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                    ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                    ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
                    ->where('cpe_cabecera.id_empresa_negocio',Auth::user()->id_empresa_negocio)
                    ->where('cpe_cabecera.ccafem','>=',$fecin)
                    ->where('cpe_cabecera.ccafem','<=',$fecfin)
                    ->where(function ($query) use ($vendedor) {
                      if($vendedor !='Todos'){
                         $query->Where('cpe_cabecera.IdUsuario_ven',$vendedor);
                      }
                    })
                        ->where(function ($query) {
                $query->where('cpe_cabecera.tdocod','01')
                    ->orWhere('cpe_cabecera.tdocod','03')
                    ->orWhere('cpe_cabecera.tdocod','13')
                    ->orWhere('cpe_cabecera.tdocod','07')
                    ->orWhere('cpe_cabecera.tdocod','08')
                    ->orWhere('cpe_cabecera.tdocod','14');
                })    
                        
                    ->where(function ($query1){
                        $query1->whereNull('cpe_cabecera.ccabaj')
                              ->orwhere('cpe_cabecera.ccabaj','');
                    
                    })
                    ->orderBy('IdCpe_cabecera','desc')->get();

              return view('empresas.reportes.indexventas',compact('fecin','fecfin','comprobantes','vendedores','dat_ven'));
        
        break;

        case 2:

                 $comprobantes = cpe_cabecera::select('cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','det.cdedes as producto','det.cdecan as cantidad','cdepuni as precio','monnom as moneda','det.cdevve as total','cpe_cabecera.ccabaj as baja','det.costo','ccasunrescod','tdides as documentoidentidad','ccandi as numerodocumento')
                ->leftjoin('cpe_detalle as det','cpe_cabecera.IdCpe_cabecera','det.IdCpe_cabecera')
                ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
                ->where('cpe_cabecera.id_empresa_negocio',Auth::user()->id_empresa_negocio)
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                 ->where(function ($query) use ($vendedor) {
                      if($vendedor !='Todos'){
                         $query->Where('cpe_cabecera.IdUsuario_ven',$vendedor);
                      }
                    })
                     ->where(function ($query) {
                $query->where('cpe_cabecera.tdocod','01')
                    ->orWhere('cpe_cabecera.tdocod','03')
                    ->orWhere('cpe_cabecera.tdocod','13')
                    ->orWhere('cpe_cabecera.tdocod','14');
                })    
                 ->orderBy('IdCpe_cabecera','desc')->get();

      

                return view('empresas.reportes.indexdetallado',compact('vendedores','fecin','fecfin','comprobantes','dat_ven'));
                 
        break;
        
        case 3:
                 $comprobantes = cpe_cabecera::select('cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','cdepuni as precio','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','det.cdecan as cantidad','det.cdedes as producto','monnom as moneda','det.cdevve as total','det.costo as costo','cpe_cabecera.ccabaj as baja','ccasunrescod')
                ->leftjoin('cpe_detalle as det','cpe_cabecera.IdCpe_cabecera','det.IdCpe_cabecera')
                ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
                ->where('cpe_cabecera.id_empresa_negocio',Auth::user()->id_empresa_negocio)
               
                   ->where(function ($query) use ($vendedor) {
                      if($vendedor !='Todos'){
                         $query->Where('cpe_cabecera.IdUsuario_ven',$vendedor);
                      }
                    })
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin) 
                  ->where(function ($query) {
                $query->where('cpe_cabecera.tdocod','01')
                    ->orWhere('cpe_cabecera.tdocod','03')
                    ->orWhere('cpe_cabecera.tdocod','13')
                    ->orWhere('cpe_cabecera.tdocod','14');
                })                
                ->orderBy('IdCpe_cabecera','desc')->get();

               

                  return view('empresas.reportes.indexutilidad',compact('vendedores','fecin','fecfin','comprobantes','dat_ven'));
                 
        break;


        
        case 4:
       $vendedores = DB::tABLE('users')
        ->leftjoin('role_user','role_user.user_IdUsuario','users.IdUsuario')
        // ->where('role_id','5')
        ->where('IdEmpresa',Auth::user()->IdEmpresa)
        ->get();
    
                 $comprobantes = cpe_cabecera::select('cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','tdides as documentoidentidad','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','monnom as moneda','ccaitv as total','cpe_cabecera.ccabaj as baja','ccaigv as igv','ccatvg as gravado','ccandi as numerodocumento','ccasunrescod','cpe_cabecera.tdocod','cpe_cabecera.ccatexo','cpe_cabecera.icbper')
                    ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                    ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                    ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                    ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                    ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
                    ->where('cpe_cabecera.id_empresa_negocio',Auth::user()->id_empresa_negocio)
                    ->where('cpe_cabecera.ccafem','>=',$fecin)
                    ->where('cpe_cabecera.ccafem','<=',$fecfin)
                       ->where(function ($query) {
            $query->where('cpe_cabecera.tdocod','01')
              ->orWhere('cpe_cabecera.tdocod','03')
              ->orWhere('cpe_cabecera.tdocod','13');
            })    
                     ->orderBy('IdCpe_cabecera','desc')->get();

               
                      return view('empresas.reportes.indexcontador',compact('fecin','fecfin','comprobantes','vendedores'));

        break;

         case 5:
                 $comprobantes = compras_cabecera::select('com_fec as fecha','tdodes as comprobante','com_doc_ser as serie','com_doc_num as numero','prov_raz as cliente','monnom as moneda','total_com as total','est_compra as estado')
                    ->leftjoin('moneda as m','compras_cabecera.mon_id','=','m.moncod')
                    ->leftjoin('proveedor as prov','prov.prov_id','compras_cabecera.prov_id')
                    ->leftjoin('empresa as e','compras_cabecera.IdEmpresa','=','e.IdEmpresa')
                    ->leftjoin('tipo_documento as tip_d','compras_cabecera.tdocod','=','tip_d.tdocod')
                    ->leftjoin('tipo_documento_identidad as tdi','prov.tdicod','=','tdi.tdicod')
                    ->where('compras_cabecera.id_empresa_negocio',Auth::user()->id_empresa_negocio)
                    ->where('com_fec','>=',$fecin)
                    ->where('com_fec','<=',$fecfin)
                    ->orderBy('com_cab_id','desc')->get();

             

                    return view('empresas.reportes.indexcompras',compact('fecin','fecfin','comprobantes'));
        break;


         case 6:
                
                $clicod = $request->get('clicod');
                $clientes = DB::tABLE('cliente')->where('rucemp',Auth::user()->IdEmpresa)->get();

                   
              $almacen = $request->get('almacen');
              $sucursal = $request->get('sucursal');

              $negocios = DB::tABLE('empresa_negocios')->get();
              if(!empty($sucursal)){
                $almacenes = DB::tABLE('almacenes')->where('id_empresa_negocio',$sucursal)->get();
              }else{
                $almacenes = DB::tABLE('almacenes')->where('id_empresa_negocio',$negocios->first()->id_empresa_negocio)->get();
              }

                $datocli = DB::tABLE('cliente')->where('clicod',$clicod)->first();

                $productos = cpe_cabecera::select(DB::RAW('sum(cpe_detalle.cdecan) as cantidad'),'cdedes','cdepuni','procod','umecod')
                ->leftjoin('cpe_detalle','cpe_detalle.IdCpe_cabecera','cpe_cabecera.IdCpe_cabecera')
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                ->whereNull('cpe_cabecera.ccabaj')
                ->where(function ($query) use ($sucursal) {
                    if($sucursal !='0'){
                       $query->Where('cpe_cabecera.id_empresa_negocio',$sucursal);
                    }
                  })
                ->where(function ($query1) use ($clicod) {
                    if($clicod !='Todos'){
                       $query1->Where('cpe_cabecera.clicod',$clicod);
                    }
                  })
                ->where(function ($query2) use ($sucursal) {
                    if(!empty($sucursal)){
                       $query2->Where('cpe_cabecera.id_empresa_negocio',$sucursal);
                    }
                })
                ->where(function ($query3) use ($almacen) {
                    if(!empty($almacen)){
                       $query3->Where('cpe_cabecera.id_almacen',$almacen);
                    }
                })
                ->orderby('cantidad','desc')
                ->groupby('procod','cdepuni','cdedes','umecod')
                ->get();

                return view('empresas.reportes.indexproductos',compact('productos','fecin','fecfin','clicod','clientes','datocli','sucursal','negocios','docomp','almacen','almacenes'));

        break;

        case 7:

              
              $almacen = $request->get('almacen');
              $sucursal = $request->get('sucursal');

              $negocios = DB::tABLE('empresa_negocios')->get();
              if(!empty($sucursal)){
                $almacenes = DB::tABLE('almacenes')->where('id_empresa_negocio',$sucursal)->get();
              }else{
                $almacenes = DB::tABLE('almacenes')->where('id_empresa_negocio',$negocios->first()->id_empresa_negocio)->get();
              }

             if(empty($sucursal)){
                $productos = DB::tABLE('productos')
                ->select('procod','mar_nom as marca','pronom','propun','productos.IdProducto','umecod','promocion','color','imagenproducto','precio2',DB::raw("(SELECT stock FROM producto_stock as pro WHERE productos.IdProducto = pro.IdProducto AND tipo='1' AND id_almacen='".$almacenes->first()->id_almacen."' AND id_empresa_negocio='".$negocios->first()->id_empresa_negocio."') as stock"),'precio',DB::raw("(SELECT count(*) FROM productos as pro WHERE productos.IdProducto = pro.pro_rel AND tipo='2') as cont_pre"))
                ->join('producto_empresa','producto_empresa.IdProducto','productos.IdProducto')
                ->join('producto_stock','producto_stock.IdProducto','producto_empresa.IdProducto')
               ->leftjoin('marcas','marcas.mar_id','productos.marca')
                ->where('tipo','1')
                ->where('producto_empresa.id_empresa_negocio',$negocios->first()->id_empresa_negocio)
                ->where('id_almacen',$almacenes->first()->id_empresa_negocio)
                ->groupby('productos.IdProducto')
                ->orderby('productos.pronom')
                ->orderby('productos.umecod')
                ->get();

             }else{
                $productos = DB::tABLE('productos')
                ->select('costo','procod','mar_nom as marca','pronom','propun','productos.IdProducto','umenom','productos.umecod','promocion','color','imagenproducto','precio2',DB::raw("(SELECT stock FROM producto_stock as pro WHERE productos.IdProducto = pro.IdProducto AND tipo='1' AND id_almacen='".$almacen."' AND id_empresa_negocio='".$sucursal."') as stock"),'precio',DB::raw("(SELECT count(*) FROM productos as pro WHERE productos.IdProducto = pro.pro_rel AND tipo='2') as cont_pre"))
                ->join('producto_empresa','producto_empresa.IdProducto','productos.IdProducto')
                ->join('producto_stock','producto_stock.IdProducto','producto_empresa.IdProducto')
                ->join('unidad_medida','unidad_medida.umecod','productos.umecod')
        ->leftjoin('marcas','marcas.mar_id','productos.marca')
                ->where('tipo','1')
                ->where('producto_empresa.id_empresa_negocio',$sucursal)
                ->where('id_almacen',$almacen)
                ->groupby('productos.IdProducto')
                ->orderby('productos.pronom')
                ->orderby('productos.umecod')
                ->get();

             }
              

                return view('empresas.reportes.indexmostrarstock',compact('sucursal','negocios','almacenes','almacen','productos','fecin','fecfin','clientes','clicod','docomp'));
        break;


     
      }
            
    
    }

    

     public function ExportarStock(Request $request)
    {

        $rucemp = trim(Auth::user()->IdEmpresa); 
    
        $stock= DB::table("productos")
          ->select("productos.*",
                    DB::raw("(SELECT SUM(cantidad) FROM movimientos
                                WHERE movimientos.IdProducto = productos.IdProducto
                                AND mov_tip='I'
                                GROUP BY movimientos.IdProducto) as Ingresos"),
                    DB::raw("(SELECT SUM(cantidad) FROM movimientos
                                WHERE movimientos.IdProducto = productos.IdProducto
                                AND mov_tip='E'
                                GROUP BY movimientos.IdProducto) as Egresos"))->where('IdEmpresa',$rucemp)->get();
            
            
        Excel::create('Stock', function($excel) use($stock) {
            $excel->sheet('Stock', function($sheet) use($stock) {
                $sheet->fromArray($stock);
            });
        })->export('xlsx');

    }


       public function ExportarProductosExcel(Request $request)
    {

          $tipo = $request->get('promocion');
          $categoria = $request->get('cmbCatId');
          $sucursal = $request->get('sucursal');
          $negocios = DB::tABLE('empresa_negocios')->get();
          $almacen = $request->get('almacen');
          $datosalm ="";
          $rucemp = trim(Auth::user()->IdEmpresa);
          $empresa = Empresa::findOrFail($rucemp);
          $buspro = trim($request->get('buspro'));

          $rucemp = trim(Auth::user()->IdEmpresa); 
     
          $productos = DB::tABLE('productos as p')
                ->select('producto_stock.stock_inicial','producto_stock.stock','p.procod','p.pronom','m.monnom','u.umenom','p.propun','p.proest','producto_stock.IdProducto','mar.mar_nom as marca','p.costo','p.propun','modelo','precio','precio2','precio3')
                ->leftjoin('moneda as m','p.moncod','=','m.moncod')
                ->leftjoin('marcas as mar','mar.mar_id','=','p.marca')
                ->leftjoin('unidad_medida as u','p.umecod','=','u.umecod')
                ->leftjoin('producto_empresa','producto_empresa.IdProducto','p.IdProducto')
                ->leftjoin('producto_stock','producto_stock.IdProducto','p.IdProducto')
                ->where(function ($query) use($categoria) {
                  if($categoria!='Todos'){
                    $query->where('cat_id',$categoria);
                  }
                  
                })
                ->where(function ($query1) use($buspro) {
                  if(!empty($buspro)){
                    $query1->where('pronom','like','%'.$buspro.'%')
                            ->orwhere('procod',$buspro);
                  }
                  
                })
                ->where('producto_stock.id_empresa_negocio',$sucursal)
                ->where('tipo','1')
                ->where('id_almacen',$almacen)
                ->groupby('p.IdProducto')
                ->orderby('pronom','asc')
                ->get();

                $productoslista = [];

                foreach($productos as $producto){

                                     $item = array(
                    'CODIGO'=>$producto->procod, 
                    'PRODUCTO'=>$producto->pronom, 
                    'MARCA'=>$producto->marca, 
                    'UNIDAD_MEDIDA'=>$producto->umenom, 
                    'STOCK_INICIAL'=>$producto->stock_inicial, 
                    'STOCK'=>$producto->stock, 
                    'PRECIO_PUBLICO'=>$producto->precio,
                    'PRECIO_MAYOR'=>$producto->precio2,
                    'PRECIO_ESPECIAL'=>$producto->precio3,
                    'COSTO'=>$producto->costo,
                    'VALOR_INVENTARIO'=>number_format($producto->costo*$producto->stock,2,'.','') 
                  );

                   $productoslista[]=$item;

                }
           
        Excel::create('PRODUCTOS', function($excel) use($productoslista) {
        $excel->sheet('PRODUCTOS', function($sheet) use($productoslista) {
    
        $sheet->fromArray($productoslista);
     });
        })->export('xlsx');





    }

    public function ExportarStockProductos(Request $request)
    {

          $tipo = $request->get('promocion');
          $categoria = $request->get('cmbCatId');
          $sucursal = $request->get('sucursal');
          $negocios = DB::tABLE('empresa_negocios')->get();
          $almacen = $request->get('almacen');
          $datosalm ="";
          $rucemp = trim(Auth::user()->IdEmpresa);
          $empresa = Empresa::findOrFail($rucemp);
          $buspro = trim($request->get('buspro'));

          $rucemp = trim(Auth::user()->IdEmpresa); 
     
          $productos = DB::tABLE('productos as p')
                ->select('producto_stock.stock_inicial','producto_stock.stock','p.procod','p.pronom','m.monnom','u.umenom','p.propun','p.proest','producto_stock.IdProducto','mar.mar_nom as marca','p.costo','p.propun','modelo')
                ->leftjoin('moneda as m','p.moncod','=','m.moncod')
                ->leftjoin('marcas as mar','mar.mar_id','=','p.marca')
                ->leftjoin('unidad_medida as u','p.umecod','=','u.umecod')
                ->leftjoin('producto_empresa','producto_empresa.IdProducto','p.IdProducto')
                ->leftjoin('producto_stock','producto_stock.IdProducto','p.IdProducto')
                ->where(function ($query) use($categoria) {
                  if($categoria!='Todos'){
                    $query->where('cat_id',$categoria);
                  }
                  
                })
                ->where(function ($query1) use($buspro) {
                  if(!empty($buspro)){
                    $query1->where('pronom','like','%'.$buspro.'%')
                            ->orwhere('procod',$buspro);
                  }
                  
                })
                ->where('producto_stock.id_empresa_negocio',$sucursal)
                ->where('tipo','1')
                ->where('id_almacen',$almacen)
                ->groupby('p.IdProducto')
                ->orderby('pronom','asc')
                ->get();

                $productoslista = [];

                foreach($productos as $producto){

                   $item = array(
                    'CODIGO'=>$producto->procod, 
                    'PRODUCTO'=>$producto->pronom, 
                    'MARCA'=>$producto->marca, 
                    'UNIDAD_MEDIDA'=>$producto->umenom, 
                    'STOCK_INICIAL'=>$producto->stock_inicial, 
                    'STOCK'=>$producto->stock, 
                    'COSTO'=>$producto->costo,
                    'VALOR_INVENTARIO'=>number_format($producto->costo*$producto->stock,2,'.','') 
                  );


                   $productoslista[]=$item;

                }
           
        Excel::create('Stock', function($excel) use($productoslista) {
        $excel->sheet('Stock', function($sheet) use($productoslista) {
    
        $sheet->fromArray($productoslista);
     });
        })->export('xlsx');





    }

     public function ExportarStockValorizado(Request $request)
    {

          $rucemp = trim(Auth::user()->IdEmpresa); 
     
         $productos= productos::select('productos.procod as CODIGO PRODUCTO','productos.pronom AS PRODUCTO','u.umenom as UNIDAD MEDIDA','productos.stockinicial as STOCK INICIAL','productos.stock as STOCK ACTUAL','productos.propun as PRECIO UNITARIO','productos.costo as COSTO',DB::raw('ROUND((productos.costo * productos.stock),2) as VALOR_INVENTARIO'))
                ->leftjoin('unidad_medida as u','productos.umecod','=','u.umecod')
                ->where('productos.IdEmpresa','=',$rucemp)
                ->where('productos.id_empresa_negocio',Auth::user()->id_empresa_negocio)
                 ->where(function ($query){
                        $query->where('promocion','=','0')
                              ->orWhere('promocion','=','4');
                })
                ->orderby('pronom','asc')
                ->get();
        
                  $total = productos::where('IdEmpresa','=',$rucemp)
                   ->where('productos.id_empresa_negocio',Auth::user()->id_empresa_negocio)
                    ->where(function ($query){
                            $query->where('promocion','=','0')
                                  ->orWhere('promocion','=','4');
                    })
                    ->sum(DB::raw('ROUND((productos.costo * productos.stock),2)'));

            
        Excel::create('Stock Valorizado', function($excel) use($productos,$total) {
        $excel->sheet('Stock Valorizado', function($sheet) use($productos,$total) {
    
        $sheet->fromArray($productos);

         $sheet->appendRow(array(
             'TOTAL VALOR INVENTARIO',$total
        ));


     });
        })->export('xlsx');





    }
   
    public function pdf_reporte_cuentas_cobrar_vendedor(Request $request){

           $vendedor = $request->get('vendedor');

             $clientes = DB::tABLE('cliente')->select('cliente.clicod','clinom','clinum','clidir')
          ->join('cpe_cabecera','cpe_cabecera.clicod','cliente.clicod')
          ->where('IdUsuario_ven',$vendedor)
          ->where('cliente.rucemp',Auth::user()->IdEmpresa)
          ->distinct()
          ->orderby('clinom','asc')
          ->get();

          $data_emp = DB::tABLE('empresa')->where('IdEmpresa',Auth::user()->IdEmpresa)->first();

          $data_neg = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first();

         

          $data_vendedor = DB::tABLE('users')->where('IdUsuario',$vendedor)->first();

           $vendedores = DB::tABLE('users')
            ->join('role_user','role_user.user_IdUsuario','users.IdUsuario')
           //// ->where('role_id','5')
            ->where('IdEmpresa',Auth::user()->IdEmpresa)
            ->get();

            $fecin = $request->get('fecin');
            $fecfin = $request->get('fecfin');
            $placa = $request->get('placa');
            $tipdoc = $request->get('tipdoc');
            $numdoc = $request->get('numdoc');
            $clicod = $request->get('clicod');
            $estado = $request->get('estado');
            $tipo = $request->get('tipfec');

            if(empty($tipo)){
                $tipo='0';
            }

            if(empty($fecin)){

                $fecin = now()->modify('first day of this month')->format('Y-m-d');
                $fecfin = now()->modify('last day of this month')->format('Y-m-d');
            }

            $cuentas = DB::tABLE('cuentas_cobrar')
            ->leftjoin('cpe_cabecera','cpe_cabecera.IdCpe_cabecera','cuentas_cobrar.IdCpe_cabecera')
            ->leftjoin('cliente','cliente.clicod','cuentas_cobrar.clicod')
            ->whereNull('cpe_cabecera.ccabaj')
            ->where('cuentas_cobrar.id_empresa_negocio',Auth::user()->id_empresa_negocio)
            ->where(function($query) use($vendedor) {
                   
                    
                     $query->where('cpe_cabecera.IdUsuario_ven',$vendedor);

                    
                   
             })
     /*     ->where(function($query1) use ($fecin, $fecfin,$tipo)
          {
              if($tipo=='0'){
                $query1->where('fec_ven','>=',$fecin)
                      ->where('fec_ven','<=',$fecfin);
              }elseif($tipo=='1'){


                $query1->where('ccafem','>=',$fecin)
                      ->where('ccafem','<=',$fecfin);
              }
            
          })*/
            ->orderby('cuentas_cobrar.fec_ven','asc')
            ->get();

      $nompdffile='cuentas_cobrar_vendedor_'.now()->format('Y-m-d').'.pdf'; 


       $rutapdf = public_path().'/reporte_cuentas_cobrar/';

       if(file_exists($rutapdf.$nompdffile)){
        unlink($rutapdf.$nompdffile);
       }

      $view = \View::make('formatos_reportes.reporte_pdf_cuentas_cobrar_vendedor', compact('data_vendedor','cuentas','clientes','data_emp','data_neg','vendedores','vendedor'));

                  
      $pdf = \App::make('dompdf.wrapper');
      $contenido = $view->render();
      $pdf->loadHTML($view)->setPaper('A4', 'lanscape')->save($rutapdf.$nompdffile);

     
      $headers = array(
              'Content-Type: application/pdf',
            );

      return response()->download($rutapdf.$nompdffile);
  
 
     //   return view('empresas.reportes.cuentascobrar',compact('date_emp','data_neg','clientes','cuentas','fecin','fecfin','clicod','tipo'));



    }


    public function pdf_reporte_cobranzas_vendedor(Request $request){

           $vendedor = $request->get('vendedor');

             $clientes = DB::tABLE('cliente')->select('cliente.clicod','clinom','clinum','clidir')
          ->join('cuentas_cobrar','cuentas_cobrar.clicod','cliente.clicod')
          ->where(DB::raw("(SELECT count(*) FROM cuentas_cobrar_detalle WHERE cue_cob_id = cuentas_cobrar.cue_cob_id)"),'>','0')
          ->where('cliente.rucemp',Auth::user()->IdEmpresa)
          ->distinct()
          ->orderby('clinom','asc')
          ->get();

          $data_emp = DB::tABLE('empresa')->where('IdEmpresa',Auth::user()->IdEmpresa)->first();

          $data_neg = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first();

         

          $data_vendedor = DB::tABLE('users')->where('IdUsuario',$vendedor)->first();

           $vendedores = DB::tABLE('users')
            ->join('role_user','role_user.user_IdUsuario','users.IdUsuario')
           // ->where('role_id','5')
            ->where('IdEmpresa',Auth::user()->IdEmpresa)
            ->get();

            $fecin = $request->get('fecin');
            $fecfin = $request->get('fecfin');
            $placa = $request->get('placa');
            $tipdoc = $request->get('tipdoc');
            $numdoc = $request->get('numdoc');
            $clicod = $request->get('clicod');
            $estado = $request->get('estado');
            $tipo = $request->get('tipfec');

            if(empty($tipo)){
                $tipo='0';
            }

            if(empty($fecin)){

                $fecin = now()->modify('first day of this month')->format('Y-m-d');
                $fecfin = now()->modify('last day of this month')->format('Y-m-d');
            }

            $cuentas = DB::tABLE('cuentas_cobrar_detalle')->select('fec_dep','cuentas_cobrar_detalle.abono','comentario','numero_recibo',DB::raw("(SELECT CONCAT(name,apeusu) FROM users WHERE IdUsuario= cuentas_cobrar_detalle.vendedor) as vendedor"),DB::raw("(SELECT clinom FROM cliente WHERE clicod = cuentas_cobrar.clicod) as cliente"),DB::raw("(SELECT clinum FROM cliente WHERE clicod = cuentas_cobrar.clicod) as ruc"),'serdoc','numdoc','cuentas_cobrar.clicod','total_detalle','saldo_detalle')
            ->join('cuentas_cobrar','cuentas_cobrar.cue_cob_id','cuentas_cobrar_detalle.cue_cob_id')
            ->join('cpe_cabecera','cpe_cabecera.IdCpe_cabecera','cuentas_cobrar.IdCpe_cabecera')
           // ->whereNull('cpe_cabecera.ccabaj')
            ->where('cuentas_cobrar.id_empresa_negocio',Auth::user()->id_empresa_negocio)
            ->where(function($query) use($vendedor) {
                   
                    
                     $query->where('cuentas_cobrar_detalle.vendedor',$vendedor);

                    
                   
             })
            ->where('fec_dep','>=',$fecin)
            ->where('fec_dep','<=',$fecfin)
     /*     ->where(function($query1) use ($fecin, $fecfin,$tipo)
          {
              if($tipo=='0'){
                $query1->where('fec_ven','>=',$fecin)
                      ->where('fec_ven','<=',$fecfin);
              }elseif($tipo=='1'){


                $query1->where('ccafem','>=',$fecin)
                      ->where('ccafem','<=',$fecfin);
              }
            
          })*/
            ->orderby('cliente','asc')
            ->get();

//dd($cuentas);
      $nompdffile='cuentas_cobrar_vendedor_'.now()->format('Y-m-d').'.pdf'; 


       $rutapdf = public_path().'/reporte_cuentas_cobrar/';

       if(file_exists($rutapdf.$nompdffile)){
        unlink($rutapdf.$nompdffile);
       }

      $view = \View::make('formatos_reportes.reporte_pdf_cobranzas_vendedor', compact('data_vendedor','cuentas','clientes','data_emp','data_neg','vendedores','fecin','fecfin'));

                  
      $pdf = \App::make('dompdf.wrapper');
      $contenido = $view->render();
      $pdf->loadHTML($view)->setPaper('A4', 'lanscape')->save($rutapdf.$nompdffile);


      $headers = array(
              'Content-Type: application/pdf',
            );

      return response()->download($rutapdf.$nompdffile);
  
 
     //   return view('empresas.reportes.cuentascobrar',compact('date_emp','data_neg','clientes','cuentas','fecin','fecfin','clicod','tipo'));



    } 


public function pdf_reporte_cobranzas_clientes(Request $request){

           $clicod = $request->get('clicod');

             $clientes = DB::tABLE('cliente')->select('cliente.clicod','clinom','clinum','clidir')
          ->join('cuentas_cobrar','cuentas_cobrar.clicod','cliente.clicod')
          ->where(DB::raw("(SELECT count(*) FROM cuentas_cobrar_detalle WHERE cue_cob_id = cuentas_cobrar.cue_cob_id)"),'>','0')
          ->where('cliente.rucemp',Auth::user()->IdEmpresa)
          ->distinct()
          ->orderby('clinom','asc')
          ->get();

          $data_emp = DB::tABLE('empresa')->where('IdEmpresa',Auth::user()->IdEmpresa)->first();

          $data_neg = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first();

         

          $data_cliente = DB::tABLE('cliente')->where('clicod',$clicod)->first();

         
            $fecin = $request->get('fecin');
            $fecfin = $request->get('fecfin');
            $placa = $request->get('placa');
            $tipdoc = $request->get('tipdoc');
            $numdoc = $request->get('numdoc');
            $clicod = $request->get('clicod');
            $estado = $request->get('estado');
            $tipo = $request->get('tipfec');

            if(empty($tipo)){
                $tipo='0';
            }

            if(empty($fecin)){

                $fecin = now()->modify('first day of this month')->format('Y-m-d');
                $fecfin = now()->modify('last day of this month')->format('Y-m-d');
            }

            $cuentas = DB::tABLE('cuentas_cobrar_detalle')->select('fec_dep','cuentas_cobrar_detalle.abono','comentario','numero_recibo',DB::raw("(SELECT CONCAT(name,apeusu) FROM users WHERE IdUsuario= cuentas_cobrar_detalle.vendedor) as vendedor"),DB::raw("(SELECT clinom FROM cliente WHERE clicod = cuentas_cobrar.clicod) as cliente"),DB::raw("(SELECT clinum FROM cliente WHERE clicod = cuentas_cobrar.clicod) as ruc"),'serdoc','numdoc','cuentas_cobrar.clicod','total_detalle','saldo_detalle')
            ->join('cuentas_cobrar','cuentas_cobrar.cue_cob_id','cuentas_cobrar_detalle.cue_cob_id')
            ->join('cpe_cabecera','cpe_cabecera.IdCpe_cabecera','cuentas_cobrar.IdCpe_cabecera')
           // ->whereNull('cpe_cabecera.ccabaj')
            ->where('cuentas_cobrar.id_empresa_negocio',Auth::user()->id_empresa_negocio)
            ->where(function($query) use($clicod) {
                   
                    
                     $query->where('cuentas_cobrar.clicod',$clicod);

                    
                   
             })
            ->where('fec_dep','>=',$fecin)
             ->where('fec_dep','<=',$fecfin)
     /*     ->where(function($query1) use ($fecin, $fecfin,$tipo)
          {
              if($tipo=='0'){
                $query1->where('fec_ven','>=',$fecin)
                      ->where('fec_ven','<=',$fecfin);
              }elseif($tipo=='1'){


                $query1->where('ccafem','>=',$fecin)
                      ->where('ccafem','<=',$fecfin);
              }
            
          })*/
            ->orderby('cuentas_cobrar_detalle.cue_cob_det_id','asc')
            ->get();

//dd($cuentas);
      $nompdffile='cobrazas_clientes'.now()->format('Y-m-d').'.pdf'; 


       $rutapdf = public_path().'/reporte_cuentas_cobrar/';

       if(file_exists($rutapdf.$nompdffile)){
        unlink($rutapdf.$nompdffile);
       }

      $view = \View::make('formatos_reportes.reporte_pdf_cobranzas_clientes', compact('data_cliente','cuentas','clientes','data_emp','data_neg'));

                  
      $pdf = \App::make('dompdf.wrapper');
      $contenido = $view->render();
      $pdf->loadHTML($view)->setPaper('A4', 'lanscape')->save($rutapdf.$nompdffile);


      $headers = array(
              'Content-Type: application/pdf',
            );

      return response()->download($rutapdf.$nompdffile);
  
 
     //   return view('empresas.reportes.cuentascobrar',compact('date_emp','data_neg','clientes','cuentas','fecin','fecfin','clicod','tipo'));



    } 
     public function pdf_reporte_cuentas_cobrar_cliente(Request $request){

           $vendedor = $request->get('vendedor');

           $cliente = $request->get('clicod');

           $data_cliente = DB::tABLE('cliente')->where('clicod',$cliente)->first();

          $clientes = DB::tABLE('cliente')
          ->where('clicod',$cliente)
          ->where('cliente.rucemp',Auth::user()->IdEmpresa)
          ->get();

          $data_emp = DB::tABLE('empresa')->where('IdEmpresa',Auth::user()->IdEmpresa)->first();

          $data_neg = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first();

         

          $data_vendedor = DB::tABLE('users')->where('IdUsuario',$vendedor)->first();

           $vendedores = DB::tABLE('users')
            ->join('role_user','role_user.user_IdUsuario','users.IdUsuario')
           // ->where('role_id','5')
             ->where('IdEmpresa',Auth::user()->IdEmpresa)
            ->get();

            $fecin = $request->get('fecin');
            $fecfin = $request->get('fecfin');
            $placa = $request->get('placa');
            $tipdoc = $request->get('tipdoc');
            $numdoc = $request->get('numdoc');
            $clicod = $request->get('clicod');
            $estado = $request->get('estado');
            $tipo = $request->get('tipfec');

            if(empty($tipo)){
                $tipo='0';
            }

            if(empty($fecin)){

                $fecin = now()->modify('first day of this month')->format('Y-m-d');
                $fecfin = now()->modify('last day of this month')->format('Y-m-d');
            }

            $cuentas = DB::tABLE('cuentas_cobrar')
            ->leftjoin('cpe_cabecera','cpe_cabecera.IdCpe_cabecera','cuentas_cobrar.IdCpe_cabecera')
            ->leftjoin('cliente','cliente.clicod','cuentas_cobrar.clicod')
            ->where('cuentas_cobrar.id_empresa_negocio',Auth::user()->id_empresa_negocio)
            ->where(function($query) use($cliente) {
                   
                    
                     $query->where('cpe_cabecera.clicod',$cliente);

                    
                   
             })
     /*     ->where(function($query1) use ($fecin, $fecfin,$tipo)
          {
              if($tipo=='0'){
                $query1->where('fec_ven','>=',$fecin)
                      ->where('fec_ven','<=',$fecfin);
              }elseif($tipo=='1'){


                $query1->where('ccafem','>=',$fecin)
                      ->where('ccafem','<=',$fecfin);
              }
            
          })*/
            ->orderby('cuentas_cobrar.fec_ven','asc')
            ->get();

      $nompdffile='cuentas_cobrar_cliente_'.now()->format('Y-m-d').'.pdf'; 


       $rutapdf = public_path().'/reporte_cuentas_cobrar/';

       if(file_exists($rutapdf.$nompdffile)){
        unlink($rutapdf.$nompdffile);
       }

      $view = \View::make('formatos_reportes.reporte_pdf_cuentas_cobrar_cliente', compact('data_cliente','data_vendedor','cuentas','clientes','data_emp','data_neg','vendedores'));

                  
      $pdf = \App::make('dompdf.wrapper');
      $contenido = $view->render();
      $pdf->loadHTML($view)->setPaper('A4', 'lanscape')->save($rutapdf.$nompdffile);


      $headers = array(
              'Content-Type: application/pdf',
            );

      return response()->download($rutapdf.$nompdffile);
  
 
     //   return view('empresas.reportes.cuentascobrar',compact('date_emp','data_neg','clientes','cuentas','fecin','fecfin','clicod','tipo'));



    }

     public function pdf_reporte_resumen_ventas_productos(Request $request){

            /*  $registros = DB::tABLE('cpe_detalle')->get();
              foreach ($registros as $reg) {
                DB::tABLE('cpe_detalle')->where('IdCpe_detalle',$reg->IdCpe_detalle)->update(['procod'=>trim($reg->procod)]);
              }*/

              $fecin = $request->get('fecin');
              $fecfin = $request->get('fecfin');
              $clicod = $request->get('clicod');
              $idsucursal = $request->get('sucursal');
              $almacen = $request->get('almacen');

              $empresa = DB::tABLE('empresa')->where('IdEmpresa',Auth::user()->IdEmpresa)->first();

              $sucursal = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first();
              $datocli = DB::tABLE('cliente')->where('clicod',$clicod)->first();

                $productos = cpe_cabecera::select(DB::RAW('sum(cpe_detalle.cdecan*cpe_det_factor) as cantidad'),'cdedes',DB::RAW('sum(cpe_detalle.cdevve) as precio'),'cpe_detalle.procod','umecod')
                ->leftjoin('cpe_detalle','cpe_detalle.IdCpe_cabecera','cpe_cabecera.IdCpe_cabecera')
 
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                ->whereNull('cpe_cabecera.ccabaj')
                ->where('cdecan','>','0')
                 ->where(function ($query) {
                  $query->Where('cpe_cabecera.tdocod','03')
                        ->orWhere('cpe_cabecera.tdocod','01')
                        ->orWhere('cpe_cabecera.tdocod','13');
                      
                  })
                ->where(function ($query1) use ($clicod) {
                    if($clicod !=0){
                       $query1->Where('cpe_cabecera.clicod',$clicod);
                    }
                  })
                ->where(function ($query5) use ($idsucursal) {
                  if($idsucursal !=0 && !empty($idsucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$idsucursal);
                  }
                 
                  
                }) 
                
                ->where(function ($query6) use ($almacen) {
                  if($almacen !=0 && !empty($almacen)){
                      $query6->where('cpe_cabecera.id_almacen',$almacen);
                  }                
                }) 
                ->orderby('cdedes','desc')
                ->groupby('procod')
                ->get();

                $totalproductos = $productos->sum('cantidad');

                $total =0;
                foreach ($productos as $pro) {
                   $total = $total + ($pro->precio);
                }

                $nompdffile='resumen_ventas_productos_'.$sucursal->IdEmpresa.'_'.now()->format('Y-m-d').'.pdf'; 


                 $rutapdf = public_path().'/reporte_cuentas_cobrar/';

                 if(file_exists($rutapdf.$nompdffile)){
                  unlink($rutapdf.$nompdffile);
                 }

                $view = \View::make('formatos_reportes.reporte_pdf_resumen_ventas_productos', compact('productos','fecin','fecfin','total','totalproductos','total','empresa','sucursal','idsucursal'));

                            
                $pdf = \App::make('dompdf.wrapper');
                $contenido = $view->render();
                $pdf->loadHTML($view)->setPaper('A4', 'lanscape')->save($rutapdf.$nompdffile);


                $headers = array(
                        'Content-Type: application/pdf',
                      );

                return response()->download($rutapdf.$nompdffile);
  
 

    }

       public function pdf_reporte_ventas_vendedor(Request $request){

                
                $sucursal = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',$request->get('sucursal'))->first();
                $almacen = DB::tABLE('almacenes')->where('id_almacen',$request->get('almacen'))->first();
                $empresa = DB::tABLE('empresa')->where('IdEmpresa',$sucursal->IdEmpresa)->first();

                $fecin = $request->get('fecin');
                $fecfin = $request->get('fecfin');
                $vendedor = $request->get('vendedor');
                $idsucursal = $request->get('sucursal');
                $idalmacen = $request->get('almacen');

                $dato_vendedor = DB::tABLE('users')->where('IdUsuario',$vendedor)->first();

                $comprobantes = cpe_cabecera::select('cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','det.cdedes as producto','det.cdecan as cantidad','cdepuni as precio','monnom as moneda','det.cdevve as total','cpe_cabecera.ccabaj as baja','det.costo','ccasunrescod','tdides as documentoidentidad','ccandi as numerodocumento','procod','cdedes','cdecan','cdepuni','cdevve','umecod','ccaitv','id_almacen_pro')
                ->leftjoin('cpe_detalle as det','cpe_cabecera.IdCpe_cabecera','det.IdCpe_cabecera')
                ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                 ->where(function ($query) use ($vendedor) {
                      if($vendedor !='Todos'){
                         $query->Where('cpe_cabecera.IdUsuario_ven',$vendedor);
                      }
                    })
                ->where(function ($query) {
                  $query->where('cpe_cabecera.tdocod','01')
                    ->orWhere('cpe_cabecera.tdocod','03')
                    ->orWhere('cpe_cabecera.tdocod','13')
                    ->orWhere('cpe_cabecera.tdocod','14')
                    ->orWhere('cpe_cabecera.tdocod','07')
                    ;
                  }) 
                    ->where(function ($query5) use ($idsucursal) {
                  if($idsucursal !=0 && !empty($idsucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$idsucursal);
                  }
                 
                  
                }) 
                
               /* ->where(function ($query6) use ($idalmacen) {
                  if($idalmacen !='Todos' && !empty($idalmacen)){
                      $query6->where('det.id_almacen_pro',$idalmacen);
                  }                
                })   */ 
                 ->orderBy('IdCpe_cabecera','desc')->get();




                 $cabecera = DB::tABLE('cpe_cabecera')
             //    ->join('cpe_detalle','cpe_detalle.IdCpe_cabecera','cpe_cabecera.IdCpe_cabecera')
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                 ->where(function ($query) use ($vendedor) {
                      if($vendedor !='Todos'){
                         $query->Where('cpe_cabecera.IdUsuario_ven',$vendedor);
                      }
                    })
                ->where(function ($query) {
                  $query->where('cpe_cabecera.tdocod','01')
                    ->orWhere('cpe_cabecera.tdocod','03')
                    ->orWhere('cpe_cabecera.tdocod','13')
                    ->orWhere('cpe_cabecera.tdocod','14')
                    ->orWhere('cpe_cabecera.tdocod','07')
                    ;
                  }) 
                    ->where(function ($query5) use ($idsucursal) {
                  if($idsucursal !=0 && !empty($idsucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$idsucursal);
                  }
                 
                  
                }) 
                
               /* ->where(function ($query6) use ($idalmacen) {
                  if($idalmacen !='Todos' && !empty($idalmacen)){
                      $query6->where('cpe_detalle.id_almacen_pro',$idalmacen);
                  }                
                }) */
               // ->groupby('cpe_cabecera.IdCpe_cabecera') 
                 ->get();

                 $total = $cabecera->where('ccanot','')->sum('ccaitv');

                 $totalnotas = $cabecera->where('tdocod','7')->sum('ccaitv');

                 /*---------------------------------------REPORTE VENTAS POR PRODUCTO RESUMEN------------------------------------------------------*/


                $productos = cpe_cabecera::select(DB::RAW('sum(cpe_detalle.cdecan*cpe_det_factor) as cantidad'),'cdedes',DB::RAW('sum(cpe_detalle.cdevve) as precio'),'cpe_detalle.procod','umecod')
                ->leftjoin('cpe_detalle','cpe_detalle.IdCpe_cabecera','cpe_cabecera.IdCpe_cabecera')
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                ->whereNull('cpe_cabecera.ccabaj')
                ->where('cdecan','>','0')
                ->where(function ($query) use ($vendedor) {
                      if($vendedor !='Todos'){
                         $query->Where('cpe_cabecera.IdUsuario_ven',$vendedor);
                      }
                })
                ->where(function ($query) {
                  $query->where('cpe_cabecera.tdocod','01')
                    ->orWhere('cpe_cabecera.tdocod','03')
                    ->orWhere('cpe_cabecera.tdocod','13')
                    ->orWhere('cpe_cabecera.tdocod','14')
                    ->orWhere('cpe_cabecera.tdocod','07')
                    ;
                  })   
                ->where(function ($query5) use ($idsucursal) {
                  if($idsucursal !=0 && !empty($idsucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$idsucursal);
                  }
                 
                  
                }) 
                
               /* ->where(function ($query6) use ($idalmacen) {
                  if($idalmacen !='Todos'){
                      $query6->where('cpe_detalle.id_almacen_pro',$idalmacen);
                  }                
                }) */
                ->orderby('cdedes','desc')
                ->groupby('procod')
                ->get();


                     $productosnot = cpe_cabecera::select(DB::RAW('sum(cpe_detalle.cdecan*cpe_det_factor) as cantidad'),'cdedes',DB::RAW('sum(cpe_detalle.cdevve) as precio'),'cpe_detalle.procod','umecod')
                ->leftjoin('cpe_detalle','cpe_detalle.IdCpe_cabecera','cpe_cabecera.IdCpe_cabecera')
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                ->whereNull('cpe_cabecera.ccabaj')
                ->where('cdecan','>','0')
                ->where(function ($query) use ($vendedor) {
                      if($vendedor !='Todos'){
                         $query->Where('cpe_cabecera.IdUsuario_ven',$vendedor);
                      }
                })
                ->where(function ($query) {
                  $query->Where('cpe_cabecera.tdocod','07')
                    ;
                  })   
                ->where(function ($query5) use ($idsucursal) {
                  if($idsucursal !=0 && !empty($idsucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$idsucursal);
                  }
                 
                  
                }) 
                
              /*  ->where(function ($query6) use ($idalmacen) {
                  if($idalmacen !='Todos' ){
                      $query6->where('cpe_detalle.id_almacen_pro',$idalmacen);
                  }                
                }) */
                ->orderby('cdedes','desc')
                ->groupby('procod')
                ->get();

                $productosnotas = $productosnot->sum('cantidad');

                $totalproductos = $productos->sum('cantidad');

                $totalmontoproductos =0;
               
                foreach ($productos as $pro) {
                   $totalmontoproductos = $totalmontoproductos + ($pro->precio);
                }


                 /*---------------------------------------------------------------------------------------------------------------------------------*/

                 $nompdffile='resumen_ventas_vendedor_productos_'.$sucursal->IdEmpresa.'_'.now()->format('Y-m-d').'.pdf'; 


                 $rutapdf = public_path().'/reporte_cuentas_cobrar/';

                 if(file_exists($rutapdf.$nompdffile)){
                  unlink($rutapdf.$nompdffile);
                 }

                $view = \View::make('formatos_reportes.reporte_pdf_ventas_vendedor_productos', compact('totalmontoproductos','totalnotas','total','dato_vendedor','cabecera','comprobantes','fecin','fecfin','empresa','sucursal','almacen','productos','productosnotas'));

                            
                $pdf = \App::make('dompdf.wrapper');
                $contenido = $view->render();
                $pdf->loadHTML($view)->setPaper('A4', 'lanscape')->save($rutapdf.$nompdffile);


                $headers = array(
                        'Content-Type: application/pdf',
                      );

                return response()->download($rutapdf.$nompdffile);
  
 

    }
    
    public function reporte_inventario(Request $request)
    {
        

          $sucursal = $request->get('sucursal');
          $almacen = $request->get('almacen');
          $fec_ini = $request->get('fec_ini');
          $fec_fin = $request->get('fec_fin');
          $rucemp = trim(Auth::user()->IdEmpresa);
          $empresa = Empresa::findOrFail($rucemp);
          $saldos_actuales = [];


          $negocios = DB::tABLE('empresa_negocios')->get();
          $almacenes = DB::tABLE('almacenes')->where('id_empresa_negocio',$negocios->first()->id_empresa_negocio)->get();

          $dat_neg = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',$sucursal)->first();

          $dat_alm =  DB::tABLE('almacenes')->where('id_almacen',$almacen)->first(); 

          if(empty($fec_ini)){
            $fec_ini = now()->modify('first day of this month')->format('Y-m-d');
            $fec_fin = now()->modify('last day of this month')->format('Y-m-d');
          }


          $productoslista = DB::tABLE('productos')->select('umecin','mar_nom','IdProducto','pronom','procod','costo_total','flete','productos.umecod',DB::raw("(SELECT COUNT(*) FROM movimientos_productos WHERE IdProducto_rel=productos.IdProducto ) as contar"),'lote','vencimiento')
          ->leftjoin('marcas','marcas.mar_id','productos.marca')
            ->leftjoin('unidad_medida','unidad_medida.umecod','productos.umecod')
          ->where('tipo','=','1')
          ->where('promocion','!=','2')
          //->where('id_empresa_negocio',$sucursal)
          ->having('contar','>','0')
          ->orderby('pronom','asc')
          ->get();



          if(!empty($sucursal)){

          foreach($productoslista as $p){

           $stock=0;
           $saldo_anterior=0;
           $ingresos = 0;
           $salidas = 0;
           $ingresos_ant = 0;
           $salidas_ant = 0;

           $ingresos = DB::tABLE('movimientos_productos')->select('cantidad')
           ->where('mov_tip','I')
           //->WHERE('fecha_mov','>=',$fec_ini)
           ->WHERE('fecha_mov','<=',$fec_fin)
           ->where('IdProducto_rel',$p->IdProducto)
           ->where('id_empresa_negocio',$sucursal)
           ->where(function ($query) use ($almacen) {
                  if($almacen!='Todos'){
                    $query->where('id_almacen','=',$almacen); 
                  }      
              }) 
           ->sum('cantidad');

           $salidas = DB::tABLE('movimientos_productos')->select('cantidad')
           ->where('mov_tip','E')
           // ->WHERE('fecha_mov','>=',$fec_ini)
           ->WHERE('fecha_mov','<=',$fec_fin)
           ->where('IdProducto_rel',$p->IdProducto)
           ->where('id_empresa_negocio',$sucursal)
           ->where(function ($query) use ($almacen) {
                  if($almacen!='Todos'){
                    $query->where('id_almacen','=',$almacen); 
                  }      
              }) 
           ->sum('cantidad');

         
          
            $stock = ($ingresos-$salidas);
  
          
         /*  $ingresos_ant = DB::tABLE('movimientos_productos')->select('cantidad')
           ->where('mov_tip','I')
           ->WHERE('fecha_mov','<',$fec_ini)
           ->where('IdProducto_rel',$p->IdProducto)
           ->where('id_empresa_negocio',$sucursal)
           ->where(function ($query) use ($almacen) {
                  if($almacen!='Todos'){
                    $query->where('id_almacen','=',$almacen); 
                  }      
              }) 
           ->sum('cantidad');

           $salidas_ant = DB::tABLE('movimientos_productos')->select('cantidad')
           ->where('mov_tip','E')
            ->WHERE('fecha_mov','<',$fec_ini)
           ->where('IdProducto_rel',$p->IdProducto)
           ->where('id_empresa_negocio',$sucursal)
           ->where(function ($query) use ($almacen) {
                  if($almacen!='Todos'){
                    $query->where('id_almacen','=',$almacen); 
                  }      
              }) 
           ->sum('cantidad');



            $saldo_anterior =  ($ingresos_ant - $salidas_ant);
               
    
            $stock_actual = $stock + $saldo_anterior;
          */
             $agregar_saldo = array('costo_promedio'=>$p->costo_total,'flete'=>$p->flete,'codigo'=>$p->procod,'IdProducto'=>$p->IdProducto,'stock'=>$stock,'producto'=>$p->pronom,'costo_total'=>$p->costo_total,'unidad'=>$p->umecin,'lote'=>$p->lote,'vencimiento'=>$p->vencimiento,'mar_nom'=>$p->mar_nom);

             $saldos_actuales[] = $agregar_saldo;

          }

       }
      
      
            return view('empresas.reportes.buscar_inventario',compact('dat_alm','negocios','sucursal','dat_neg','almacenes','almacen','dat_alm','fec_ini','fec_fin','saldos_actuales'));

    }


     public function reporte_inventario_pdf(Request $request)
    {
        

          $sucursal = $request->get('sucursal');
          $almacen = $request->get('almacen');
          $fec_ini = $request->get('fec_ini');
          $fec_fin = $request->get('fec_fin');
          $rucemp = trim(Auth::user()->IdEmpresa);
           $empresa = Empresa::findOrFail($rucemp);
          $saldos_actuales = [];


          $negocios = DB::tABLE('empresa_negocios')->get();
          $almacenes = DB::tABLE('almacenes')->where('id_empresa_negocio',$negocios->first()->id_empresa_negocio)->get();

          $dat_neg = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',$sucursal)->first();

          $dat_alm =  DB::tABLE('almacenes')->where('id_almacen',$almacen)->first(); 

          if(empty($fec_ini)){
            $fec_ini = now()->modify('first day of this month')->format('Y-m-d');
            $fec_fin = now()->modify('last day of this month')->format('Y-m-d');
          }


           $productoslista = DB::tABLE('productos')->select('umecin','mar_nom','lote','vencimiento','IdProducto','pronom','procod','costo_total','flete','productos.umecod',DB::raw("(SELECT COUNT(*) FROM movimientos_productos WHERE IdProducto_rel=productos.IdProducto ) as contar"))
           ->leftjoin('marcas','marcas.mar_id','productos.marca')
             ->leftjoin('unidad_medida','unidad_medida.umecod','productos.umecod')
          ->where('tipo','=','1')
          ->where('promocion','!=','2')
          //->where('id_empresa_negocio',$sucursal)
          ->having('contar','>','0')
          ->orderby('pronom','asc')
          ->get();

          if(!empty($sucursal)){

          foreach($productoslista as $p){

           $stock=0;
           $saldo_anterior=0;
           $ingresos = 0;
           $salidas = 0;
           $ingresos_ant = 0;
           $salidas_ant = 0;

           $ingresos = DB::tABLE('movimientos_productos')->select('cantidad')
           ->where('mov_tip','I')
           ->WHERE('fecha_mov','>=',$fec_ini)
           ->WHERE('fecha_mov','<=',$fec_fin)
           ->where('IdProducto_rel',$p->IdProducto)
           ->where('id_empresa_negocio',$sucursal)
           ->where(function ($query) use ($almacen) {
                  if($almacen!='Todos'){
                    $query->where('id_almacen','=',$almacen); 
                  }      
              }) 
           ->sum('cantidad');

           $salidas = DB::tABLE('movimientos_productos')->select('cantidad')
           ->where('mov_tip','E')
            ->WHERE('fecha_mov','>=',$fec_ini)
           ->WHERE('fecha_mov','<=',$fec_fin)
           ->where('IdProducto_rel',$p->IdProducto)
           ->where('id_empresa_negocio',$sucursal)
           ->where(function ($query) use ($almacen) {
                  if($almacen!='Todos'){
                    $query->where('id_almacen','=',$almacen); 
                  }      
              }) 
           ->sum('cantidad');

         
          
            $stock = ($ingresos-$salidas);
  
          
           $ingresos_ant = DB::tABLE('movimientos_productos')->select('cantidad')
           ->where('mov_tip','I')
           ->WHERE('fecha_mov','<',$fec_ini)
           ->where('IdProducto_rel',$p->IdProducto)
           ->where('id_empresa_negocio',$sucursal)
           ->where(function ($query) use ($almacen) {
                  if($almacen!='Todos'){
                    $query->where('id_almacen','=',$almacen); 
                  }      
              }) 
           ->sum('cantidad');

           $salidas_ant = DB::tABLE('movimientos_productos')->select('cantidad')
           ->where('mov_tip','E')
            ->WHERE('fecha_mov','<',$fec_ini)
           ->where('IdProducto_rel',$p->IdProducto)
           ->where('id_empresa_negocio',$sucursal)
           ->where(function ($query) use ($almacen) {
                  if($almacen!='Todos'){
                    $query->where('id_almacen','=',$almacen); 
                  }      
              }) 
           ->sum('cantidad');



            $saldo_anterior =  ($ingresos_ant - $salidas_ant);
               
    
            $stock_actual = $stock + $saldo_anterior;

             $agregar_saldo = array('costo_promedio'=>$p->costo_total,'flete'=>$p->flete,'codigo'=>$p->procod,'IdProducto'=>$p->IdProducto,'stock'=>$stock_actual,'producto'=>$p->pronom,'costo_total'=>$p->costo_total,'unidad'=>$p->umecin,'lote'=>$p->lote,'vencimiento'=>$p->vencimiento,'mar_nom'=>$p->mar_nom);

             $saldos_actuales[] = $agregar_saldo;

          }

       }
      
      //  dd($saldos_actuales);
         return view('formatos_reportes.reporte_pdf_inventario', compact('dat_alm','empresa','negocios','sucursal','dat_neg','almacenes','almacen','fec_ini','fec_fin','saldos_actuales'));

        /*  $nompdffile = 'REPORTE_INVENTARIO.pdf';

        $rutapdf = public_path().'/pdfreportes/';

        $view = \View::make('formatos_reportes.reporte_pdf_inventario', compact('dat_alm','empresa','negocios','sucursal','dat_neg','almacenes','almacen','fec_ini','fec_fin','saldos_actuales'));


                  
          $pdf = \App::make('dompdf.wrapper');
          $contenido = $view->render();
          $pdf->loadHTML($view)->setPaper('A4', 'lanscape')->save($rutapdf.$nompdffile);


      
       if (file_exists($rutapdf.$nompdffile))
      {
        $headers = array(
              'Content-Type: application/pdf',
            );

        return response()->download($rutapdf.$nompdffile);

     }*/

    }

    public function reporte_inventario_excel(Request $request)
    {
        


          $sucursal = $request->get('sucursal');
          $almacen = $request->get('almacen');
          $fec_ini = $request->get('fec_ini');
          $fec_fin = $request->get('fec_fin');
          $rucemp = trim(Auth::user()->IdEmpresa);
           $empresa = Empresa::findOrFail($rucemp);
          $saldos_actuales = [];


          $negocios = DB::tABLE('empresa_negocios')->get();
          $almacenes = DB::tABLE('almacenes')->where('id_empresa_negocio',$negocios->first()->id_empresa_negocio)->get();

          $dat_neg = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',$sucursal)->first();

          $dat_alm =  DB::tABLE('almacenes')->where('id_almacen',$almacen)->first(); 

          if(empty($fec_ini)){
            $fec_ini = now()->modify('first day of this month')->format('Y-m-d');
            $fec_fin = now()->modify('last day of this month')->format('Y-m-d');
          }


           $productoslista = DB::tABLE('productos')->select('umecin','mar_nom','IdProducto','pronom','procod','costo_total','flete','productos.umecod',DB::raw("(SELECT COUNT(*) FROM movimientos_productos WHERE IdProducto_rel=productos.IdProducto ) as contar"),'lote','vencimiento')
            ->leftjoin('marcas','marcas.mar_id','productos.marca')
              ->leftjoin('unidad_medida','unidad_medida.umecod','productos.umecod')
          ->where('tipo','=','1')
          ->where('promocion','!=','2')
          //->where('id_empresa_negocio',$sucursal)
          ->having('contar','>','0')
          ->orderby('pronom','asc')
          ->get();

          if(!empty($sucursal)){

          foreach($productoslista as $p){

           $stock=0;
           $saldo_anterior=0;
           $ingresos = 0;
           $salidas = 0;
           $ingresos_ant = 0;
           $salidas_ant = 0;

           $ingresos = DB::tABLE('movimientos_productos')->select('cantidad')
           ->where('mov_tip','I')
           ->WHERE('fecha_mov','>=',$fec_ini)
           ->WHERE('fecha_mov','<=',$fec_fin)
           ->where('IdProducto_rel',$p->IdProducto)
           ->where('id_empresa_negocio',$sucursal)
           ->where(function ($query) use ($almacen) {
                  if($almacen!='Todos'){
                    $query->where('id_almacen','=',$almacen); 
                  }      
              }) 
           ->sum('cantidad');

           $salidas = DB::tABLE('movimientos_productos')->select('cantidad')
           ->where('mov_tip','E')
            ->WHERE('fecha_mov','>=',$fec_ini)
           ->WHERE('fecha_mov','<=',$fec_fin)
           ->where('IdProducto_rel',$p->IdProducto)
           ->where('id_empresa_negocio',$sucursal)
           ->where(function ($query) use ($almacen) {
                  if($almacen!='Todos'){
                    $query->where('id_almacen','=',$almacen); 
                  }      
              }) 
           ->sum('cantidad');

         
          
            $stock = ($ingresos-$salidas);
  
          
           $ingresos_ant = DB::tABLE('movimientos_productos')->select('cantidad')
           ->where('mov_tip','I')
           ->WHERE('fecha_mov','<',$fec_ini)
           ->where('IdProducto_rel',$p->IdProducto)
           ->where('id_empresa_negocio',$sucursal)
           ->where(function ($query) use ($almacen) {
                  if($almacen!='Todos'){
                    $query->where('id_almacen','=',$almacen); 
                  }      
              }) 
           ->sum('cantidad');

           $salidas_ant = DB::tABLE('movimientos_productos')->select('cantidad')
           ->where('mov_tip','E')
            ->WHERE('fecha_mov','<',$fec_ini)
           ->where('IdProducto_rel',$p->IdProducto)
           ->where('id_empresa_negocio',$sucursal)
           ->where(function ($query) use ($almacen) {
                  if($almacen!='Todos'){
                    $query->where('id_almacen','=',$almacen); 
                  }      
              }) 
           ->sum('cantidad');



            $saldo_anterior =  ($ingresos_ant - $salidas_ant);
               
    
            $stock_actual = $stock + $saldo_anterior;

             $agregar_saldo = array('costo_promedio'=>$p->costo_total,'flete'=>$p->flete,'codigo'=>$p->procod,'IdProducto'=>$p->IdProducto,'stock'=>$stock_actual,'producto'=>$p->pronom,'costo_total'=>$p->costo_total,'unidad'=>$p->umecin,'lote'=>$p->lote,'vencimiento'=>$p->vencimiento,'mar_nom'=>$p->mar_nom);

             $saldos_actuales[] = $agregar_saldo;

          }

       }
      

                     Excel::create('Reporte_Inventario', function($excel) use ($dat_alm,$negocios,$sucursal,$dat_neg,$almacenes,$almacen,$fec_ini,$fec_fin,$saldos_actuales) {

                        $excel->sheet('Inventarios', function($sheet) use ($dat_alm,$negocios,$sucursal,$dat_neg,$almacenes,$almacen,$fec_ini,$fec_fin,$saldos_actuales) {


                                  $sheet->loadView('formatos_reportes_excel.inventario',compact('dat_alm','negocios','sucursal','dat_neg','almacenes','almacen','fec_ini','fec_fin','saldos_actuales'));
                              

                                

                        });

                    })->export('xlsx'); 

       
      



    }

    public function reporte_ventas(Request $request){

      $opcion = $request->get('opcion');
      $fecin = $request->get('fecin');
      $fecfin = $request->get('fecfin');
      $sucursal = $request->get('sucursal');
      $data_sucursal = EmpresaNegocios::findOrFail($sucursal);
      $vendedor = $request->get('vendedor');
      $cliente = $request->get('cliente');
      $almacen = $request->get('almacen');
      $dato_vendedor="";
      $dato_cliente="";

      

      $empresa = Auth::user()->IdEmpresa;
        switch ($opcion){
            case 1:
               $comprobantes = cpe_cabecera::select('cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','det.cdedes as producto','det.cdecan as cantidad','cdepuni as precio','monnom as moneda','det.cdevve as total','cpe_cabecera.ccabaj as baja','det.costo','ccasunrescod','tdides as documentoidentidad','ccandi as numerodocumento','procod','cdedes','cdecan','cdepuni','cdevve','umecod','ccaitv','id_almacen_pro')
                ->leftjoin('cpe_detalle as det','cpe_cabecera.IdCpe_cabecera','det.IdCpe_cabecera')
                ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                ->where(function ($query) {
                  $query->where('cpe_cabecera.tdocod','01')
                    ->orWhere('cpe_cabecera.tdocod','03')
                    ->orWhere('cpe_cabecera.tdocod','13')
                    ->orWhere('cpe_cabecera.tdocod','14')
                    //->orWhere('cpe_cabecera.tdocod','07')
                    ;
                  }) 
                  ->where(function ($query5) use ($sucursal) {
        
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
         
                  
                }) 
                 ->orderBy('IdCpe_cabecera','desc')->get();




                 $cabecera = DB::tABLE('cpe_cabecera')
             //    ->join('cpe_detalle','cpe_detalle.IdCpe_cabecera','cpe_cabecera.IdCpe_cabecera')
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
          
                ->where(function ($query) {
                  $query->where('cpe_cabecera.tdocod','01')
                    ->orWhere('cpe_cabecera.tdocod','03')
                    ->orWhere('cpe_cabecera.tdocod','13')
                    ->orWhere('cpe_cabecera.tdocod','14')
                   // ->orWhere('cpe_cabecera.tdocod','07')
                    ;
                  }) 
                ->whereNull('ccabaj')
                ->where('ccanot','')
                ->where(function ($query5) use ($sucursal) {
      
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
        
                }) 
    
                 ->get();


                 $total = $cabecera->where('ccanot','')->sum('ccaitv');

                 $totalnotas = $cabecera->where('tdocod','7')->sum('ccaitv');

                 $totalefectivo = $cabecera->where('ccanot','')->sum('totalcontado');
                 $totalcredito = $cabecera->where('ccanot','')->sum('totalcredito');
                        Excel::create('Reporte_Ventas', function($excel) use ($totalnotas,$total,$cabecera,$comprobantes,$fecin,$fecfin,$empresa,$sucursal,$data_sucursal,$totalefectivo,$totalcredito) {

                        $excel->sheet('Comprobantes', function($sheet) use ($totalnotas,$total,$cabecera,$comprobantes,$fecin,$fecfin,$empresa,$sucursal,$data_sucursal,$totalefectivo,$totalcredito) {


                                  $sheet->loadView('formatos_reportes_excel.ventas',compact('totalnotas','total','dato_vendedor','cabecera','comprobantes','fecin','fecfin','empresa','sucursal','data_sucursal','totalefectivo','totalcredito'));
                              

                                

                        });

                    })->export('xlsx'); 
            break;

              case 2:

                
                 if($vendedor!='Todos'){
                  $dato_vendedor = DB::tABLE('users')->where('IdUsuario',$vendedor)->first();
                }

                if($cliente!='Todos'){
                  $dato_cliente = DB::tABLE('cliente')->where('clicod',$cliente)->first();
                }

                $comprobantes = cpe_cabecera::select('cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','det.cdedes as producto','det.cdecan as cantidad','cdepuni as precio','monnom as moneda','det.cdevve as total','cpe_cabecera.ccabaj as baja','det.costo','ccasunrescod','tdides as documentoidentidad','ccandi as numerodocumento','procod','cdedes','cdecan','cdepuni','cdevve','umecod','ccaitv','id_almacen_pro')
                ->leftjoin('cpe_detalle as det','cpe_cabecera.IdCpe_cabecera','det.IdCpe_cabecera')
                ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                 ->where(function ($query) use ($vendedor) {
                      if($vendedor !='Todos'){
                         $query->Where('cpe_cabecera.IdUsuario_ven',$vendedor);
                      }
                    })
                ->where(function ($query) {
                  $query->where('cpe_cabecera.tdocod','01')
                    ->orWhere('cpe_cabecera.tdocod','03')
                    ->orWhere('cpe_cabecera.tdocod','13')
                    ->orWhere('cpe_cabecera.tdocod','14')
                    ->orWhere('cpe_cabecera.tdocod','07')
                    ;
                  }) 
                    ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                       ->where(function ($query2) use ($cliente) {
                      if($cliente !='Todos'){
                         $query2->Where('cpe_cabecera.clicod',$cliente);
                      }
                    })
                 ->orderBy('IdCpe_cabecera','desc')->get();




                 $cabecera = DB::tABLE('cpe_cabecera')
             //    ->join('cpe_detalle','cpe_detalle.IdCpe_cabecera','cpe_cabecera.IdCpe_cabecera')
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                 ->where(function ($query) use ($vendedor) {
                      if($vendedor !='Todos'){
                         $query->Where('cpe_cabecera.IdUsuario_ven',$vendedor);
                      }
                    })
                ->where(function ($query) {
                  $query->where('cpe_cabecera.tdocod','01')
                    ->orWhere('cpe_cabecera.tdocod','03')
                    ->orWhere('cpe_cabecera.tdocod','13')
                    ->orWhere('cpe_cabecera.tdocod','14')
                    ->orWhere('cpe_cabecera.tdocod','07')
                    ;
                  }) 
                    ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                })
                   ->where(function ($query2) use ($cliente) {
                      if($cliente !='Todos'){
                         $query2->Where('cpe_cabecera.clicod',$cliente);
                      }
                    })
                 ->get();

                 $total = $cabecera->where('ccanot','')->sum('ccaitv');

                 $totalnotas = $cabecera->where('tdocod','7')->sum('ccaitv');

                 /*---------------------------------------REPORTE VENTAS POR PRODUCTO RESUMEN------------------------------------------------------*/


                $productos = cpe_cabecera::select(DB::RAW('sum(cpe_detalle.cdecan*cpe_det_factor) as cantidad'),'cdedes',DB::RAW('sum(cpe_detalle.cdevve) as precio'),'cpe_detalle.procod','umecod')
                ->leftjoin('cpe_detalle','cpe_detalle.IdCpe_cabecera','cpe_cabecera.IdCpe_cabecera')
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                ->whereNull('cpe_cabecera.ccabaj')
                ->where('cdecan','>','0')
                ->where(function ($query) use ($vendedor) {
                      if($vendedor !='Todos'){
                         $query->Where('cpe_cabecera.IdUsuario_ven',$vendedor);
                      }
                })
                ->where(function ($query) {
                  $query->where('cpe_cabecera.tdocod','01')
                    ->orWhere('cpe_cabecera.tdocod','03')
                    ->orWhere('cpe_cabecera.tdocod','13')
                    ->orWhere('cpe_cabecera.tdocod','14')
                    ->orWhere('cpe_cabecera.tdocod','07')
                    ;
                  })   
                ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                })
                ->where(function ($query2) use ($cliente) {
                    if($cliente !='Todos'){
                         $query2->Where('cpe_cabecera.clicod',$cliente);
                    }
                }) 
                ->orderby('cdedes','desc')
                ->groupby('procod','cdedes')
                ->get();


                     $productosnot = cpe_cabecera::select(DB::RAW('sum(cpe_detalle.cdecan*cpe_det_factor) as cantidad'),'cdedes',DB::RAW('sum(cpe_detalle.cdevve) as precio'),'cpe_detalle.procod','umecod')
                ->leftjoin('cpe_detalle','cpe_detalle.IdCpe_cabecera','cpe_cabecera.IdCpe_cabecera')
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                ->whereNull('cpe_cabecera.ccabaj')
                ->where('cdecan','>','0')
                ->where(function ($query) use ($vendedor) {
                      if($vendedor !='Todos'){
                         $query->Where('cpe_cabecera.IdUsuario_ven',$vendedor);
                      }
                })
                ->where(function ($query) {
                  $query->Where('cpe_cabecera.tdocod','07')
                    ;
                  })   
                ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                })
                ->where(function ($query2) use ($cliente) {
                    if($cliente !='Todos'){
                         $query2->Where('cpe_cabecera.clicod',$cliente);
                      }
                }) 
                ->orderby('cdedes','desc')
                ->groupby('procod')
                ->get();

                $productosnotas = $productosnot->sum('cantidad');

                $totalproductos = $productos->sum('cantidad');

                $totalmontoproductos =0;
               
                foreach ($productos as $pro) {
                   $totalmontoproductos = $totalmontoproductos + ($pro->precio);
                }


                 /*---------------------------------------------------------------------------------------------------------------------------------*/

               


                        Excel::create('Reporte_Ventas_Detallado', function($excel) use ($totalmontoproductos,$totalnotas,$total,$cabecera,$comprobantes,$fecin,$fecfin,$empresa,$sucursal,$almacen,$productos,$productosnotas,$data_sucursal,$dato_vendedor,$dato_cliente) {

                        $excel->sheet('Comprobantes', function($sheet) use ($totalmontoproductos,$totalnotas,$total,$cabecera,$comprobantes,$fecin,$fecfin,$empresa,$sucursal,$almacen,$productos,$productosnotas,$data_sucursal,$dato_vendedor,$dato_cliente) {


                                  $sheet->loadView('formatos_reportes_excel.ventas_detalladas',compact('totalmontoproductos','totalnotas','total','cabecera','comprobantes','fecin','fecfin','empresa','sucursal','almacen','productos','productosnotas','data_sucursal','dato_vendedor','dato_cliente'));
                              

                                

                        });

                    })->export('xlsx'); 


             

            case 3:

                 $comprobantes = cpe_cabecera::select('ccacodsun','cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','tdides as documentoidentidad','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','monnom as moneda','ccaitv as total','cpe_cabecera.ccabaj as baja','ccaigv as igv','ccatvg as gravado','ccandi as numerodocumento','ccasunrescod','cpe_cabecera.tdocod','cpe_cabecera.ccatexo','cpe_cabecera.icbper')
                    ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                    ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                    ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                    ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                    ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
                    ->where(function ($query5) use ($sucursal) {
               
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                
                  
                }) 
          
                    ->where('cpe_cabecera.ccafem','>=',$fecin)
                    ->where('cpe_cabecera.ccafem','<=',$fecfin)
                    ->where(function ($query) {
                    $query->where('cpe_cabecera.tdocod','01')
                      ->orWhere('cpe_cabecera.tdocod','03');
                    })    
                   
                     ->orderBy('IdCpe_cabecera','desc')->get();

                $totales = cpe_cabecera::select('ccacodsun','cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','tdides as documentoidentidad','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','monnom as moneda','ccaitv as total','cpe_cabecera.ccabaj as baja','ccaigv as igv','ccatvg as gravado','ccandi as numerodocumento','ccasunrescod','cpe_cabecera.tdocod','cpe_cabecera.ccatexo','cpe_cabecera.icbper')
                    ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                    ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                    ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                    ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                    ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
                    ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
       
                    ->where('cpe_cabecera.ccafem','>=',$fecin)
                    ->where('cpe_cabecera.ccafem','<=',$fecfin)
                    ->where(function ($query) {
                      $query->where('cpe_cabecera.tdocod','01')
                        ->orWhere('cpe_cabecera.tdocod','03');
                      })    
                    
                     ->orderBy('IdCpe_cabecera','desc')->get();


              $gravados = $totales->sum('ccatvg');
              $exoneradas = $totales->sum('ccatexo');
              $totaligv = $totales->sum('cdeigv');
              $totalventas = $totales->sum('total');
    
              //NOTAS DE CREDITOS

                 $totalnc = cpe_cabecera::select('ccacodsun','cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','monnom as moneda','cpe_cabecera.ccabaj as baja','ccasunrescod','tdides as documentoidentidad','ccandi as numerodocumento','ccaigv','ccaitv','ccatexo','ccatvg')
            
                ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
                ->where(function ($query5) use ($sucursal) {
             
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
              
                 
                  
                }) 
                
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                  ->where(function ($query1){
          
                    $query1->whereNull('cpe_cabecera.ccabaj')
                          ->orwhere('cpe_cabecera.ccabaj','');
                    
                })
                ->where(function ($query) {
                   $query->Where('cpe_cabecera.tdocod','07');
                })    
                 ->orderBy('cpe_cabecera.IdCpe_cabecera','desc')->get();

              $gravadosnc = $totalnc->sum('ccatvg');
              $exoneradasnc = $totalnc->sum('ccatexo');
              $totaligvnc = $totalnc->sum('ccaigv');
              $totalnc = $totalnc->sum('ccaitv');

              // FIN NOTAS DE CREDITOS

              $hora_rep = now()->format('Y-m-d H:i:s');

                Excel::create('Reporte_Ventas_SUNAT', function($excel) use ($hora_rep,$fecin,$fecfin,$comprobantes,$gravados,$exoneradas,$totaligv,$totalventas,$gravadosnc,$exoneradasnc,$totaligvnc,$totalnc) {

                        $excel->sheet('Comprobantes', function($sheet) use ($hora_rep,$fecin,$fecfin,$comprobantes,$gravados,$exoneradas,$totaligv,$totalventas,$gravadosnc,$exoneradasnc,$totaligvnc,$totalnc) {


                                  $sheet->loadView('formatos_reportes_excel.ventas_sunat',compact('hora_rep','fecin','fecfin','comprobantes','gravados','exoneradas','totaligv','totalventas','gravadosnc','exoneradasnc','totaligvnc','totalnc'));
                              

                                

                        });

                    })->export('xlsx'); 

              

            break;


            case 51:

       

                $comprobantes = cpe_cabecera::select('cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','det.cdedes as producto','det.cdecan as cantidad','cdepuni as precio','monnom as moneda','det.cdevve as total','cpe_cabecera.ccabaj as baja','det.costo','ccasunrescod','tdides as documentoidentidad','ccandi as numerodocumento','procod','cdedes','cdecan','cdepuni','cdevve','umecod','ccaitv','id_almacen_pro')
                ->leftjoin('cpe_detalle as det','cpe_cabecera.IdCpe_cabecera','det.IdCpe_cabecera')
                ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                ->where(function ($query) {
                  $query->where('cpe_cabecera.tdocod','01')
                    ->orWhere('cpe_cabecera.tdocod','03')
                    ->orWhere('cpe_cabecera.tdocod','13')
                    ->orWhere('cpe_cabecera.tdocod','14')
                    //->orWhere('cpe_cabecera.tdocod','07')
                    ;
                  }) 
                  ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                ->whereNotNull('ccabaj')
                ->orderBy('IdCpe_cabecera','desc')->get();




                $cabecera = DB::tABLE('cpe_cabecera')
             // ->join('cpe_detalle','cpe_detalle.IdCpe_cabecera','cpe_cabecera.IdCpe_cabecera')
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                ->where(function ($query) {
                  $query->where('cpe_cabecera.tdocod','01')
                    ->orWhere('cpe_cabecera.tdocod','03')
                    ->orWhere('cpe_cabecera.tdocod','13')
                    ->orWhere('cpe_cabecera.tdocod','14')
                   // ->orWhere('cpe_cabecera.tdocod','07')
                    ;
                  }) 
 
                ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                ->whereNotNull('ccabaj')
                ->get();

                

                 $totalnotas = $cabecera->where('tdocod','7')->sum('ccaitv');

                 $totalefectivo = $cabecera->where('ccanot','')->sum('totalcontado');
                 $totalcredito = $cabecera->where('ccanot','')->sum('totalcredito');

                  $total = $totalefectivo+$totalcredito;

          
                $hora_rep = now()->format('Y-m-d H:i:s');

                Excel::create('Reporte_Ventas_Anuladas', function($excel) use ($totalnotas,$total,$cabecera,$comprobantes,$fecin,$fecfin,$empresa,$sucursal,$data_sucursal,$totalefectivo,$totalcredito) {

                        $excel->sheet('Comprobantes', function($sheet) use ($totalnotas,$total,$cabecera,$comprobantes,$fecin,$fecfin,$empresa,$sucursal,$data_sucursal,$totalefectivo,$totalcredito) {


                                  $sheet->loadView('formatos_reportes_excel.ventas_anuladas',compact('totalnotas','total','cabecera','comprobantes','fecin','fecfin','empresa','sucursal','data_sucursal','totalefectivo','totalcredito'));
                              

                                

                        });

                    })->export('xlsx'); 




            break;  


                  case 80:

                $boletas = cpe_cabecera::select('ccafem as FECHA', 'tdodes as COMPROBANTE','serdoc as SERIE',DB::raw('MIN(numdoc) as INICIO'),DB::raw( 'MAX(numdoc) as FIN'),DB::raw('SUM(ccatvg) as VALOR_VENTA'),DB::raw('SUM(ccaigv) as IGV'),DB::raw('SUM(ccaitv) as TOTAL'))
                ->leftjoin('tipo_documento','tipo_documento.tdocod','cpe_cabecera.tdocod')
                ->whereNull('ccabaj')
                ->where('cpe_cabecera.tdocod','03')
                ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                }) 
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                ->groupby('ccafem')
                ->groupby('tdodes')
                ->orderBy('FECHA','asc')
                ->orderBy('INICIO','asc')
                ->get();

                $facturas = cpe_cabecera::select('ccaitv','cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','monnom as moneda','ccaitv as total','cpe_cabecera.ccabaj as baja','ccasunrescod','tdides as documentoidentidad','ccandi as numerodocumento','cpe_cabecera.tdocod','ccatexo','ccatvg','ccatvi')
                ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
                ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                ->where(function ($query){
                    $query->where('cpe_cabecera.tdocod','01');
                })
                ->whereNull('ccabaj')
                ->orderBy('IdCpe_cabecera','desc')->get();

             

                $total_boletas = $boletas->sum('TOTAL');
                $total_facturas = $facturas->sum('total');

              
                 $hora_rep = now()->format('Y-m-d H:i:s');

                Excel::create('Reporte_Ventas_Consolidado', function($excel) use ($fecin,$fecfin,$boletas,$facturas,$total_boletas,$total_facturas) {

                        $excel->sheet('Comprobantes', function($sheet) use ($fecin,$fecfin,$boletas,$facturas,$total_boletas,$total_facturas) {


                                  $sheet->loadView('formatos_reportes_excel.ventas_consolidado',compact('fecin','fecfin','boletas','facturas','total_boletas','total_facturas'));
                              

                                

                        });

                    })->export('xlsx'); 


            break;


                    case 19:



              
                $facturas = cpe_cabecera::select('ccaitv','cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','monnom as moneda','ccaitv as total','cpe_cabecera.ccabaj as baja','ccasunrescod','tdides as documentoidentidad','ccandi as numerodocumento',
                  'cpe_cabecera.tdocod','ccafve as fechaven','cpe_cabecera.tdicod','ccatexo','ccafem_ref','serie_ref','tdocod_ref','num_ref','cod_mov')
                ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
                 ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                
                ->where(function ($query6) use ($almacen) {
                  if($almacen !='Todos'){
                      $query6->where('cpe_cabecera.id_almacen',$almacen);
                  }                
                }) 
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                ->where(function ($query){
                    $query->where('cpe_cabecera.tdocod','01');
                })
                ->where(function ($query1) use ($vendedor) {
                      if($vendedor !='Todos'){
                         $query1->Where('cpe_cabecera.IdUsuario_ven',$vendedor);
                      }
                    })
                 ->where(function ($query2) use ($cliente) {
                      if($cliente !='Todos'){
                         $query2->Where('cpe_cabecera.clicod',$cliente);
                      }
                    })
                ->orderBy('IdCpe_cabecera','desc')->get();

                 $boletas = cpe_cabecera::select('ccaitv','cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','monnom as moneda','ccaitv as total','cpe_cabecera.ccabaj as baja','ccasunrescod','tdides as documentoidentidad','ccandi as numerodocumento',
                  'cpe_cabecera.tdocod','ccafve as fechaven','cpe_cabecera.tdicod','ccatexo','ccafem_ref','serie_ref','tdocod_ref','num_ref','cod_mov')
                ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
                 ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                
                ->where(function ($query6) use ($almacen) {
                  if($almacen !='Todos'){
                      $query6->where('cpe_cabecera.id_almacen',$almacen);
                  }                
                }) 
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                ->where(function ($query){
                    $query->where('cpe_cabecera.tdocod','03');
                })
                ->where(function ($query1) use ($vendedor) {
                      if($vendedor !='Todos'){
                         $query1->Where('cpe_cabecera.IdUsuario_ven',$vendedor);
                      }
                    })
                 ->where(function ($query2) use ($cliente) {
                      if($cliente !='Todos'){
                         $query2->Where('cpe_cabecera.clicod',$cliente);
                      }
                    })
                ->orderBy('IdCpe_cabecera','desc')->get();


                 $notas = cpe_cabecera::select('ccaitv','cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','monnom as moneda','ccaitv as total','cpe_cabecera.ccabaj as baja','ccasunrescod','tdides as documentoidentidad','ccandi as numerodocumento',
                  'cpe_cabecera.tdocod','ccafve as fechaven','cpe_cabecera.tdicod','ccatexo','ccafem_ref','serie_ref','tdocod_ref','num_ref','cod_mov')
                ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
                 ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                
                ->where(function ($query6) use ($almacen) {
                  if($almacen !='Todos'){
                      $query6->where('cpe_cabecera.id_almacen',$almacen);
                  }                
                }) 
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                ->where(function ($query){
                    $query->where('cpe_cabecera.tdocod','07');
                })
                ->where(function ($query1) use ($vendedor) {
                      if($vendedor !='Todos'){
                         $query1->Where('cpe_cabecera.IdUsuario_ven',$vendedor);
                      }
                    })
                 ->where(function ($query2) use ($cliente) {
                      if($cliente !='Todos'){
                         $query2->Where('cpe_cabecera.clicod',$cliente);
                      }
                    })
                ->orderBy('IdCpe_cabecera','desc')->get();

      

                          $totalfacturas = $facturas->sum('ccaitv');
                  $totalboletas = $boletas->sum('ccaitv');
                  $totalnotas = $notas->sum('ccaitv');


                  $totalfacturasexo = $facturas->sum('ccatexo');
                  $totalboletasexo = $boletas->sum('ccatexo');
                  $totalnotasexo = $notas->sum('ccatexo');
                    

                  $totalfacturasinaf = $facturas->sum('ccatvi');
                  $totalboletasinaf = $boletas->sum('ccatvi');
                  $totalnotasinaf = $notas->sum('ccatvi');
                  

                   $totalfacturasigv = $facturas->sum('ccaigv');
                  $totalboletasigv = $boletas->sum('ccaigv');
                  $totalnotasigv = $notas->sum('ccaigv');
                 
                  $totalfacturasicbper = $facturas->sum('icbper');
                  $totalboletasicbper = $boletas->sum('icbper');
                  $totalnotasicbper = $notas->sum('icbper');


              
                  $hora_rep = now()->format('Y-m-d H:i:s');

                Excel::create('Reporte_Ventas_Registro_SUNAT', function($excel) use ($fecin,$fecfin,$totalfacturas,$totalboletas,$totalnotas,$totalfacturasexo,$totalboletasexo,$totalnotasexo,$totalfacturasinaf,$totalboletasinaf,$totalnotasinaf,$totalfacturasigv,$totalboletasigv,$totalnotasigv,$totalfacturasicbper,$totalboletasicbper,$totalnotasicbper,$facturas,$boletas,$notas) {

                        $excel->sheet('Comprobantes', function($sheet) use ($fecin,$fecfin,$totalfacturas,$totalboletas,$totalnotas,$totalfacturasexo,$totalboletasexo,$totalnotasexo,$totalfacturasinaf,$totalboletasinaf,$totalnotasinaf,$totalfacturasigv,$totalboletasigv,$totalnotasigv,$totalfacturasicbper,$totalboletasicbper,$totalnotasicbper,$facturas,$boletas,$notas) {


                                  $sheet->loadView('formatos_reportes_excel.ventas_registro_sunat',compact('fecin','fecfin','totalfacturas','totalboletas','totalnotas','totalfacturasexo','totalboletasexo','totalnotasexo','totalfacturasinaf','totalboletasinaf','totalnotasinaf','totalfacturasigv','totalboletasigv','totalnotasigv','totalfacturasicbper','totalboletasicbper','totalnotasicbper','facturas','boletas','notas'));
                              

                                

                        });

                    })->export('xlsx'); 


            break;





        }

      

    }


    pubLic function reporte_compras(Request $request){


          $opcion = $request->get('opcion');
          $fecin = $request->get('fecin');
          $fecfin = $request->get('fecfin');
          $sucursal = $request->get('sucursal');
          $almacen = $request->get('almacen');
          $proveedor = $request->get('proveedor');
          $data_sucursal = EmpresaNegocios::findOrFail($sucursal);

          $empresa = Auth::user()->IdEmpresa;

        switch ($opcion){
           case 1:

                 $comprobantes = DB::tABLE('compras_cabecera')
                  ->leftjoin('proveedor','compras_cabecera.prov_id','proveedor.prov_id')
                  ->join('moneda','compras_cabecera.mon_id','moneda.moncod')
                  ->join('tipo_documento','compras_cabecera.tdocod','tipo_documento.tdocod')
                    ->where('compras_cabecera.com_fec','>=',$fecin)
                    ->where('compras_cabecera.com_fec','<=',$fecfin)
                  ->where('est_compra','Registrado')
                    ->where(function ($query) use ($sucursal){
                      if(!$sucursal !=0 && !empty($sucursal)){
                          $query->where('compras_cabecera.id_empresa_negocio',$sucursal);
                      }
                     
                  })
                  ->where(function ($query1) use ($proveedor){
                      if($proveedor !=0){
                          $query1->where('compras_cabecera.prov_id',$proveedor);
                      }
                      
                  })
                        
                  ->where(function ($query6) use ($almacen) {
                      if($almacen !=0 && !empty($almacen)){
                          $query6->where('compras_cabecera.id_almacen',$almacen);
                      }                
                    }) 
                
                  ->where(function ($query){
                    $query->where('compras_cabecera.tdocod','!=','80');
                })
                  ->orderby('com_cab_id','desc')
                  ->get();
             //   dd($comprobantes);

                  $total = $comprobantes->sum('total_com');


                $vista = view('empresas.reportes.compras',compact('fecin','fecfin','comprobantes','total','dat_ven','dat_cli'))->render();



              if($request->ajax()){
                 return response()->json(['vista'=>$vista]);

              }

            break;

            case 2:

                

              $comprobantes = DB::tABLE('compras_cabecera')
               ->leftjoin('compras_detalle','compras_detalle.com_cab_id','compras_cabecera.com_cab_id')
                 ->leftjoin('productos','productos.IdProducto','compras_detalle.pro_id')
                  ->leftjoin('proveedor','compras_cabecera.prov_id','proveedor.prov_id')
                  ->leftjoin('unidad_medida','unidad_medida.umecod','compras_detalle.ume_cod')
                  ->join('moneda','compras_cabecera.mon_id','moneda.moncod')
                  ->join('tipo_documento','compras_cabecera.tdocod','tipo_documento.tdocod')
              ->where('est_compra','Registrado')
                ->where('compras_cabecera.com_fec','>=',$fecin)
                ->where('compras_cabecera.com_fec','<=',$fecfin)
                  ->where(function ($query) use ($sucursal){
                      if(!$sucursal !=0 && !empty($sucursal)){
                          $query->where('compras_cabecera.id_empresa_negocio',$sucursal);
                      }
                     
                  })
                  ->where(function ($query1) use ($proveedor){
                      if($proveedor !=0){
                          $query1->where('compras_cabecera.prov_id',$proveedor);
                      }
                      
                  })
                        
                  ->where(function ($query6) use ($almacen) {
                      if($almacen !=0 && !empty($almacen)){
                          $query6->where('compras_cabecera.id_almacen',$almacen);
                      }                
                    }) 
               ->where(function ($query){
                    $query->where('compras_cabecera.tdocod','<>','80');
                })
              ->orderby('compras_cabecera.com_cab_id','desc')
              ->get();
                 //dd($comprobantes);

                  $total = $comprobantes->sum('total_com');


                $vista = view('empresas.reportes.compras_detalladas',compact('fecin','fecfin','comprobantes','total','dat_ven','dat_cli'))->render();

                    if($request->ajax()){
                 return response()->json(['vista'=>$vista]);

              }



            break;


            case 3:

              $comprobantes = DB::tABLE('compras_cabecera')
              ->leftjoin('proveedor','compras_cabecera.prov_id','proveedor.prov_id')
              ->join('moneda','compras_cabecera.mon_id','moneda.moncod')
              ->join('tipo_documento','compras_cabecera.tdocod','tipo_documento.tdocod')
              ->where('est_compra','Registrado')
                ->where('compras_cabecera.com_fec','>=',$fecin)
                ->where('compras_cabecera.com_fec','<=',$fecfin)
                  ->where(function ($query) use ($sucursal){
                      if(!empty($sucursal)){
                          $query->where('compras_cabecera.id_empresa_negocio',$sucursal);
                      }
                     
                  })
            
                  ->where(function ($query6) use ($almacen) {
                      if(!empty($almacen)){
                          $query6->where('compras_cabecera.id_almacen',$almacen);
                      }                
                    }) 
               ->where(function ($query){
                    $query->where('compras_cabecera.tdocod','!=','80');
                })
              ->orderby('compras_cabecera.com_cab_id','desc')
              ->get();
                 //dd($comprobantes);

                  $total = $comprobantes->sum('total_com');


                $vista = view('empresas.reportes.compras_contador',compact('fecin','fecfin','comprobantes','total','dat_ven','dat_cli'))->render();

                    if($request->ajax()){
                 return response()->json(['vista'=>$vista]);

              }



            break;


            case 4:

                   $comprobantes = compras_cabecera::select('total_com','com_cab_id','com_fec as fecha','tdodes as comprobante','com_doc_ser as serie','com_doc_num as numero','prov_raz as cliente','monnom as moneda','total_com as total','tdides as documentoidentidad','prov_ruc as numerodocumento','compras_cabecera.tdocod')
                ->leftjoin('moneda as m','compras_cabecera.mon_id','=','m.moncod')
                ->leftjoin('proveedor as cl','compras_cabecera.prov_id','=','cl.prov_id')
               //->leftjoin('empresa as e','compras_cabecera.IdEmpresa','=','e.IdEmpresa')
                ->leftjoin('tipo_documento as tip_d','compras_cabecera.tdocod','=','tip_d.tdocod')
                ->leftjoin('tipo_documento_identidad as tdi','cl.tdicod','=','tdi.tdicod')
                 ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('compras_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                
                ->where(function ($query6) use ($almacen) {
                  if($almacen !=0 && !empty($almacen)){
                      $query6->where('compras_cabecera.id_almacen',$almacen);
                  }                
                }) 
                ->where('compras_cabecera.com_fec','>=',$fecin)
                ->where('compras_cabecera.com_fec','<=',$fecfin)
                ->where(function ($query){
                    $query->where('compras_cabecera.tdocod','01')
                          ->orwhere('compras_cabecera.tdocod','03')
                          ->orWhere('compras_cabecera.tdocod','13')
                          ->orWhere('compras_cabecera.tdocod','07')
                          ->orWhere('compras_cabecera.tdocod','09');
                })
                ->where('est_compra','Registrado')
                ->orderBy('com_cab_id','desc')->get();

            //  dd($comprobantes);

                  $total = $comprobantes->sum('total_com');

                  $totalexoneradasfacturas = $comprobantes->where('tdocod','01')->sum('total_com');
                  $totalgravadasfacturas = $comprobantes->where('tdocod','01')->sum('ccatvg');

                  $totalexoneradasboletas = $comprobantes->where('tdocod','03')->sum('total_com');
                  $totalgravadasboletas = $comprobantes->where('tdocod','03')->sum('ccatvg');

                  $totalexoneradasnotas = $comprobantes->where('tdocod','07')->sum('total_com');
                  $totalgravadasnotas = $comprobantes->where('tdocod','07')->sum('ccatvg');


            

                 $sucursal = EmpresaNegocios::FindOrFail($request->get('sucursal'));

                 $empresa = Empresa::findOrFail($sucursal->IdEmpresa);

                
                    $vista =view('empresas.reportes.compras_resumen',compact('fecin','fecfin','total','totalexoneradasfacturas','totalexoneradasboletas','totalgravadasfacturas','totalgravadasboletas','totalgravadasnotas','totalexoneradasnotas','empresa','sucursal'))->render();


              if($request->ajax()){
                 return response()->json(['vista'=>$vista]);

              }



            break;
          }

    }

    pubLic function reporte_compras_excel(Request $request){

      $opcion = $request->get('opcion');
      $fecin = $request->get('fecin');
      $fecfin = $request->get('fecfin');
      $sucursal = $request->get('sucursal');
      $almacen = $request->get('almacen');
      $data_sucursal = EmpresaNegocios::findOrFail($sucursal);

      $empresa = Empresa::findorFail(Auth::user()->IdEmpresa);

        switch ($opcion){
            case 1:

                 $comprobantes = DB::tABLE('compras_cabecera')
                  ->leftjoin('proveedor','compras_cabecera.prov_id','proveedor.prov_id')
                  ->join('moneda','compras_cabecera.mon_id','moneda.moncod')
                  ->join('tipo_documento','compras_cabecera.tdocod','tipo_documento.tdocod')
                  ->where('compras_cabecera.com_fec','>=',$fecin)
                  ->where('compras_cabecera.com_fec','<=',$fecfin)
                  ->where('est_compra','Registrado')
                    ->where(function ($query) use ($sucursal){
                      if(!empty($sucursal)){
                          $query->where('compras_cabecera.id_empresa_negocio',$sucursal);
                      }
                     
                  })
                     
                  ->where(function ($query6) use ($almacen) {
                      if(!empty($almacen)){
                          $query6->where('compras_cabecera.id_almacen',$almacen);
                      }                
                    }) 
                
                  ->where('compras_cabecera.tdocod','!=','80')
                  ->orderby('com_cab_id','desc')
                  ->get();
             

                  $total = $comprobantes->sum('total_com');


                   Excel::create('COMPRAS', function($excel) use ($data_sucursal,$empresa,$fecin,$fecfin,$total,$comprobantes) {

                        $excel->sheet('COMPRAS', function($sheet) use ($data_sucursal,$empresa,$fecin,$fecfin,$total,$comprobantes) {

                       
                            
                                  $sheet->loadView('formatos_reportes_excel.compras',compact('data_sucursal','empresa','fecin','fecfin','total','comprobantes'));
                          
                                

                        });

                    })->export('xlsx'); 

                

                break;

                      case 2:

                 $comprobantes = DB::tABLE('compras_cabecera')
                 ->leftjoin('compras_detalle','compras_detalle.com_cab_id','compras_cabecera.com_cab_id')
                 ->leftjoin('productos','productos.IdProducto','compras_detalle.pro_id')
                  ->leftjoin('proveedor','compras_cabecera.prov_id','proveedor.prov_id')
                  ->leftjoin('unidad_medida','unidad_medida.umecod','compras_detalle.ume_cod')
                  ->join('moneda','compras_cabecera.mon_id','moneda.moncod')
                  ->join('tipo_documento','compras_cabecera.tdocod','tipo_documento.tdocod')
                  ->where('compras_cabecera.com_fec','>=',$fecin)
                  ->where('compras_cabecera.com_fec','<=',$fecfin)
                  ->where('est_compra','Registrado')
                    ->where(function ($query) use ($sucursal){
                      if(!empty($sucursal)){
                          $query->where('compras_cabecera.id_empresa_negocio',$sucursal);
                      }
                     
                  })
                     
                  ->where(function ($query6) use ($almacen) {
                      if(!empty($almacen)){
                          $query6->where('compras_cabecera.id_almacen',$almacen);
                      }                
                    }) 
                
                  ->where('compras_cabecera.tdocod','!=','80')
                  ->orderby('compras_cabecera.com_cab_id','desc')
                  ->get();
             

                  $total = $comprobantes->sum('total_com');


                   Excel::create('COMPRAS DETALLADO', function($excel) use ($data_sucursal,$empresa,$fecin,$fecfin,$total,$comprobantes) {

                        $excel->sheet('COMPRAS_DETALLADO', function($sheet) use ($data_sucursal,$empresa,$fecin,$fecfin,$total,$comprobantes) {

                       
                            
                                  $sheet->loadView('formatos_reportes_excel.compras_detalladas',compact('data_sucursal','empresa','fecin','fecfin','total','comprobantes'));
                          
                                

                        });

                    })->export('xlsx'); 

  

               
                break;

                  case 3:

                   $comprobantes = DB::tABLE('compras_cabecera')
              ->leftjoin('proveedor','compras_cabecera.prov_id','proveedor.prov_id')
              ->join('moneda','compras_cabecera.mon_id','moneda.moncod')
              ->join('tipo_documento','compras_cabecera.tdocod','tipo_documento.tdocod')
              ->where('est_compra','Registrado')
                ->where('compras_cabecera.com_fec','>=',$fecin)
                ->where('compras_cabecera.com_fec','<=',$fecfin)
                  ->where(function ($query) use ($sucursal){
                      if(!empty($sucursal)){
                          $query->where('compras_cabecera.id_empresa_negocio',$sucursal);
                      }
                     
                  })
            
                  ->where(function ($query6) use ($almacen) {
                      if(!empty($almacen)){
                          $query6->where('compras_cabecera.id_almacen',$almacen);
                      }                
                    }) 
               ->where(function ($query){
                    $query->where('compras_cabecera.tdocod','!=','80');
                })
              ->orderby('compras_cabecera.com_cab_id','desc')
              ->get();
                 //dd($comprobantes);

                  $total = $comprobantes->sum('total_com');



                 Excel::create('REGISTRO COMPRAS SUNAT', function($excel) use ($data_sucursal,$empresa,$fecin,$fecfin,$total,$comprobantes) {

                        $excel->sheet('REGISTRO_COMPRAS_SUNAT', function($sheet) use ($data_sucursal,$empresa,$fecin,$fecfin,$total,$comprobantes) {

                          $sheet->setWidth(array(
                            'A'     =>  1000000000
                        ));

                            
                                  $sheet->loadView('formatos_reportes_excel.compras_contador',compact('fecin','fecfin','comprobantes','total','dat_ven','dat_cli'));
                          
                                

                        });

                    })->export('xlsx'); 



              break;
        };


    }

    pubLic function reporte_compras_pdf(Request $request){
      
      $opcion = $request->get('opcion');
      $fecin = $request->get('fecin');
      $fecfin = $request->get('fecfin');
      $sucursal = $request->get('sucursal');
      $almacen = $request->get('almacen');
      $data_sucursal = EmpresaNegocios::findOrFail($sucursal);

      $empresa = Empresa::findorFail(Auth::user()->IdEmpresa);

        switch ($opcion){
            case 1:

                 $comprobantes = DB::tABLE('compras_cabecera')
                  ->leftjoin('proveedor','compras_cabecera.prov_id','proveedor.prov_id')
                  ->join('moneda','compras_cabecera.mon_id','moneda.moncod')
                  ->join('tipo_documento','compras_cabecera.tdocod','tipo_documento.tdocod')
                  ->where('compras_cabecera.com_fec','>=',$fecin)
                  ->where('compras_cabecera.com_fec','<=',$fecfin)
                  ->where('est_compra','Registrado')
                    ->where(function ($query) use ($sucursal){
                      if(!empty($sucursal)){
                          $query->where('compras_cabecera.id_empresa_negocio',$sucursal);
                      }
                     
                  })
                     
                  ->where(function ($query6) use ($almacen) {
                      if(!empty($almacen)){
                          $query6->where('compras_cabecera.id_almacen',$almacen);
                      }                
                    }) 
                
                  ->where('compras_cabecera.tdocod','!=','80')
                  ->orderby('com_cab_id','desc')
                  ->get();
             

                  $total = $comprobantes->sum('total_com');


                 $rutapdf = public_path().'/pdfreportes/';

                  $nompdffile = 'Reporte_Compras_'.$fecin.'_'.$fecfin.'.pdf';

                 if(file_exists($rutapdf.$nompdffile)){

                      unlink($rutapdf.$nompdffile);
                  }

                
                  $view = \View::make('formatos_reportes.reporte_pdf_compras', compact('data_sucursal','empresa','fecin','fecfin','total','comprobantes'));

                            
                  $pdf = \App::make('dompdf.wrapper');
                  $contenido = $view->render();
                  $pdf->loadHTML($view)->setPaper('A4', 'lanscape')->save($rutapdf.$nompdffile);

                 if (file_exists($rutapdf.$nompdffile)){
                    $headers = array(
                        'Content-Type: application/pdf',
                      );

                  return response()->download($rutapdf.$nompdffile);

                }

                break;

                      case 2:

                 $comprobantes = DB::tABLE('compras_cabecera')
                 ->leftjoin('compras_detalle','compras_detalle.com_cab_id','compras_cabecera.com_cab_id')
                 ->leftjoin('productos','productos.IdProducto','compras_detalle.pro_id')
                  ->leftjoin('proveedor','compras_cabecera.prov_id','proveedor.prov_id')
                  ->leftjoin('unidad_medida','unidad_medida.umecod','compras_detalle.ume_cod')
                  ->join('moneda','compras_cabecera.mon_id','moneda.moncod')
                  ->join('tipo_documento','compras_cabecera.tdocod','tipo_documento.tdocod')
                  ->where('compras_cabecera.com_fec','>=',$fecin)
                  ->where('compras_cabecera.com_fec','<=',$fecfin)
                  ->where('est_compra','Registrado')
                    ->where(function ($query) use ($sucursal){
                      if(!empty($sucursal)){
                          $query->where('compras_cabecera.id_empresa_negocio',$sucursal);
                      }
                     
                  })
                     
                  ->where(function ($query6) use ($almacen) {
                      if(!empty($almacen)){
                          $query6->where('compras_cabecera.id_almacen',$almacen);
                      }                
                    }) 
                
                  ->where('compras_cabecera.tdocod','!=','80')
                  ->orderby('compras_cabecera.com_cab_id','desc')
                  ->get();
             

                  $total = $comprobantes->sum('total_com');


                 $rutapdf = public_path().'/pdfreportes/';

                  $nompdffile = 'Reporte_Compras_'.$fecin.'_'.$fecfin.'.pdf';

                 if(file_exists($rutapdf.$nompdffile)){

                      unlink($rutapdf.$nompdffile);
                  }

                
                  $view = \View::make('formatos_reportes.reporte_pdf_compras_productos', compact('data_sucursal','empresa','fecin','fecfin','total','comprobantes'));

                            
                  $pdf = \App::make('dompdf.wrapper');
                  $contenido = $view->render();
                  $pdf->loadHTML($view)->setPaper('A4', 'lanscape')->save($rutapdf.$nompdffile);

                 if (file_exists($rutapdf.$nompdffile)){
                    $headers = array(
                        'Content-Type: application/pdf',
                      );

                  return response()->download($rutapdf.$nompdffile);

                }

                break;



        };

    }

       public function reportes_salidas(Request $request){

        $fecin = $request->get('fecin');
        $fecfin = $request->get('fecfin');

        $areas = DB::tABLE('areas')->get();

        $area = $request->get('area');
        $negocios = DB::tABLE('empresa_negocios')->get();
        $sucursal = $request->get('sucursal');
        $IdProducto = $request->get('IdProducto');
        $tipo = $request->get('tipo');
        $productos = DB::tABLE('productos')->get();

        if(empty($tipo)){
          $tipo ='1';
        }

        $documento = $request->get('comp');

        $ser = substr($documento,strpos($documento,'-')-4,4);
        $num = substr($documento,strpos($documento,'-')+1,8);

        if(empty($fecin)){

          $fecin = now()->modify('first day of this month')->format('Y-m-d');
          $fecfin = now()->modify('last day of this month')->format('Y-m-d');

        }

  
        $empresa = Empresa::findOrFail(Auth::user()->IdEmpresa);
           
        $IdEmpresa = Auth::user()->IdEmpresa;
    
        $cantidad =0;

       if($tipo=='1'){
          $comprobantes = DB::tABLE('cpe_cabecera as cpe_c')->select('cpe_det_lote','cpe_det_venc','ccacodsun','ccafem','tdodes','cpe_c.serdoc','cpe_c.numdoc','tdides','cpe_c.ccandi','cpe_c.ccanom','monnom','cpe_c.ccaitv','cpe_c.IdEmpresa','cpe_c.tdocod','cpe_c.IdCpe_cabecera','cpe_c.ccanot','cpe_c.ccabaj','cpe_c.codhash','cpe_c.estado','cpe_c.enviado','totalcontado','totalcredito','areas.are_emp_des','name','apeusu','cdedes','cdecan','cpe_c.are_emp_id','cpe_detalle.costo')
          ->leftjoin('cpe_detalle','cpe_detalle.IdCpe_cabecera','cpe_c.IdCpe_cabecera')
          ->join('tipo_documento as tip_d','cpe_c.tdocod','=','tip_d.tdocod')
          ->join('tipo_documento_identidad as tdi','cpe_c.tdicod','=','tdi.tdicod')
          ->join('moneda as mon','cpe_c.moncod','=','mon.moncod')
          ->leftjoin('users','users.IdUsuario','cpe_c.usu_rec')
          ->join('areas','areas.are_emp_id','cpe_c.are_emp_id')
          ->whereNull('cpe_c.ccabaj')
          ->where('cpe_c.ccafem','>=',$fecin)
          ->where('cpe_c.ccafem','<=',$fecfin)
          ->where('cpe_c.id_empresa_negocio',Auth::user()->id_empresa_negocio)
          ->where(function ($query) {
            $query->where('cpe_c.tdocod','81');
            })
          ->where(function ($query) use ($IdProducto) {
            if(!empty($IdProducto)){
              $query->where('IdProducto',$IdProducto);
            }
          })
          ->where(function ($query) use($area) {
            if(!empty($area)){
               $query->where('cpe_c.are_emp_id',$area);
            }
           
          })
          ->orderby('IdCpe_cabecera','asc')
          ->get();


          $cantidad = $comprobantes->sum('cdecan');

        }elseif($tipo=='2'){
           $comprobantes = DB::tABLE('cpe_cabecera')
           ->select(DB::RAW('sum(cpe_detalle.cdecan) as cantidad'),'pronom','cdedes','cdepuni','productos.procod','productos.umecod','areas.are_emp_des','cpe_cabecera.are_emp_id',DB::RAW('sum(cpe_detalle.costo) as costo_total'))
                ->leftjoin('cpe_detalle','cpe_detalle.IdCpe_cabecera','cpe_cabecera.IdCpe_cabecera')
                ->leftjoin('productos','productos.IdProducto','cpe_detalle.IdProducto')
                ->leftjoin('areas','areas.are_emp_id','cpe_cabecera.are_emp_id')
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                ->where('cpe_cabecera.id_empresa_negocio',Auth::user()->id_empresa_negocio)
                ->whereNull('cpe_cabecera.ccabaj')
                ->where(function ($query) use ($IdProducto) {
                  if(!empty($IdProducto)){
                    $query->where('cpe_detalle.IdProducto',$IdProducto);
                  }
                })
                ->where(function ($query) {
                  $query->where('cpe_cabecera.tdocod','81');
                  })
                  ->where(function ($query) use($area) {
                  if(!empty($area)){
                     $query->where('cpe_cabecera.are_emp_id',$area);
                  }
                 
                  })
                ->orderby('cantidad','desc')
                ->groupby('cpe_cabecera.are_emp_id')
                ->groupby('cpe_detalle.IdProducto')
                ->get();

                $cantidad = $comprobantes->sum('cantidad');
              


        }

         return view('empresas.reportes.reportes_salidas_productos',compact('IdProducto','productos','cantidad','negocios','sucursal','comprobantes','fecin','fecfin','documento','areas','area','tipo'));

      

    }



     public function pdf_reportes_salidas(Request $request){


        $fecin = $request->get('fecin');
        $fecfin = $request->get('fecfin');

        $areas = DB::tABLE('areas')->get();

        $area = $request->get('area');
        $negocios = DB::tABLE('empresa_negocios')->get();
        $sucursal = $request->get('sucursal');
        $IdProducto = $request->get('IdProducto');
        $tipo = $request->get('tipo');
        $productos = DB::tABLE('productos')->get();

        if(empty($tipo)){
          $tipo ='1';
        }

        $documento = $request->get('comp');

        $ser = substr($documento,strpos($documento,'-')-4,4);
        $num = substr($documento,strpos($documento,'-')+1,8);

        if(empty($fecin)){

          $fecin = now()->modify('first day of this month')->format('Y-m-d');
          $fecfin = now()->modify('last day of this month')->format('Y-m-d');

        }

  
        $empresa = Empresa::findOrFail(Auth::user()->IdEmpresa);
        
        $data_sucursal = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first();

        $IdEmpresa = Auth::user()->IdEmpresa;
    
        $cantidad =0;

       if($tipo=='1'){
          $comprobantes = DB::tABLE('cpe_cabecera as cpe_c')->select('cpe_det_lote','cpe_det_venc','ccacodsun','ccafem','tdodes','cpe_c.serdoc','cpe_c.numdoc','tdides','cpe_c.ccandi','cpe_c.ccanom','monnom','cpe_c.ccaitv','cpe_c.IdEmpresa','cpe_c.tdocod','cpe_c.IdCpe_cabecera','cpe_c.ccanot','cpe_c.ccabaj','cpe_c.codhash','cpe_c.estado','cpe_c.enviado','totalcontado','totalcredito','areas.are_emp_des','name','apeusu','cdedes','cdecan','cpe_c.are_emp_id','cpe_detalle.costo')
          ->leftjoin('cpe_detalle','cpe_detalle.IdCpe_cabecera','cpe_c.IdCpe_cabecera')
          ->join('tipo_documento as tip_d','cpe_c.tdocod','=','tip_d.tdocod')
          ->join('tipo_documento_identidad as tdi','cpe_c.tdicod','=','tdi.tdicod')
          ->join('moneda as mon','cpe_c.moncod','=','mon.moncod')
          ->leftjoin('users','users.IdUsuario','cpe_c.usu_rec')
          ->join('areas','areas.are_emp_id','cpe_c.are_emp_id')
          ->whereNull('cpe_c.ccabaj')
          ->where('cpe_c.ccafem','>=',$fecin)
          ->where('cpe_c.ccafem','<=',$fecfin)
          ->where('cpe_c.id_empresa_negocio',Auth::user()->id_empresa_negocio)
          ->where(function ($query) {
            $query->where('cpe_c.tdocod','81');
            })
          ->where(function ($query) use ($IdProducto) {
            if(!empty($IdProducto)){
              $query->where('IdProducto',$IdProducto);
            }
          })
          ->where(function ($query) use($area) {
            if(!empty($area)){
               $query->where('cpe_c.are_emp_id',$area);
            }
           
          })
          ->orderby('IdCpe_cabecera','asc')
          ->get();

          $cantidad = $comprobantes->sum('cdecan');

        }elseif($tipo=='2'){
           $comprobantes = DB::tABLE('cpe_cabecera')
           ->select(DB::RAW('sum(cpe_detalle.cdecan) as cantidad'),'pronom','cdedes','cdepuni','productos.procod','productos.umecod','areas.are_emp_des','cpe_cabecera.are_emp_id',DB::RAW('sum(cpe_detalle.costo) as costo_total'))
                ->leftjoin('cpe_detalle','cpe_detalle.IdCpe_cabecera','cpe_cabecera.IdCpe_cabecera')
                ->leftjoin('productos','productos.IdProducto','cpe_detalle.IdProducto')
                ->leftjoin('areas','areas.are_emp_id','cpe_cabecera.are_emp_id')
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                ->where('cpe_cabecera.id_empresa_negocio',Auth::user()->id_empresa_negocio)
                ->whereNull('cpe_cabecera.ccabaj')
                ->where(function ($query) use ($IdProducto) {
                  if(!empty($IdProducto)){
                    $query->where('cpe_detalle.IdProducto',$IdProducto);
                  }
                })
                ->where(function ($query) {
                  $query->where('cpe_cabecera.tdocod','81');
                  })
                  ->where(function ($query) use($area) {
                  if(!empty($area)){
                     $query->where('cpe_cabecera.are_emp_id',$area);
                  }
                 
                  })
                ->orderby('cantidad','desc')
                ->groupby('cpe_cabecera.are_emp_id')
                ->groupby('cpe_detalle.IdProducto')
                ->get();

                $cantidad = $comprobantes->sum('cantidad');


        }


          $rutapdf = public_path().'/pdfreportes/';

        $nompdffile = 'REPORTE_SALIDAS_PRODUCTOS'.'.pdf';

       if(file_exists($rutapdf.$nompdffile)){

            unlink($rutapdf.$nompdffile);
        }

        $empresa = DB::tABLE('empresa')->where('IdEmpresa',Auth::user()->IdEmpresa)->first();
        $view = \View::make('formatos_reportes.reporte_pdf_salida_productos', compact('IdProducto','productos','cantidad','negocios','sucursal','comprobantes','fecin','fecfin','documento','areas','area','tipo','data_sucursal','empresa'));

                  
          $pdf = \App::make('dompdf.wrapper');
          $contenido = $view->render();
          $pdf->loadHTML($view)->setPaper('A4', 'lanscape')->save($rutapdf.$nompdffile);

          if (file_exists($rutapdf.$nompdffile))
          {
            $headers = array(
                  'Content-Type: application/pdf',
                );

            return response()->download($rutapdf.$nompdffile);

          }

       
      

    }




    public function reportes_taller(Request $request){

      $fecin = now()->modify('first day of this month')->format('Y-m-d');
      $fecfin = now()->modify('last day of this month')->format('Y-m-d');

      $clientes = DB::tABLE('cliente')->where('rucemp',Auth::user()->IdEmpresa)->get();


      $prod = $request->get('IdProducto');

      $negocios = DB::tABLE('empresa_negocios')->where('IdEmpresa',Auth::user()->IdEmpresa)->get();
             
      $almacenes = DB::tABLE('almacenes')
      ->where('id_empresa_negocio',$negocios->first()->id_empresa_negocio)->get();
            
      $sucursal = $negocios->first()->id_empresa_negocio;  

       $almacen = $almacenes->first()->id_almacen;  


      return view('empresas.reportes.reportes_taller',compact('almacen','sucursal','negocios','almacenes','clientes','fecin','fecfin'));

            

    }



    public function generar_reportes_taller(Request $request){

        $fecin = $request->get('fecin');
        $fecfin = $request->get('fecfin');

        $opcion = $request->get('opcion');

        $negocios = DB::tABLE('empresa_negocios')->get();
        $sucursal = $request->get('sucursal');
  

        if(empty($fecin)){

          $fecin = now()->modify('first day of this month')->format('Y-m-d');
          $fecfin = now()->modify('last day of this month')->format('Y-m-d');

        }

  
        $empresa = Empresa::findOrFail(Auth::user()->IdEmpresa);
           
        $IdEmpresa = Auth::user()->IdEmpresa;
    

         $ordenes = DB::tABLE('cpe_cabecera as cpe_c')->select('est_ord_nom','fechacot','cpe_c.estadocobrar','ccacodsun','ccafem','tdodes','cpe_c.serdoc','cpe_c.numdoc','tdides','cpe_c.ccandi','cpe_c.ccanom','monnom','cpe_c.ccaitv','cpe_c.tdocod','cpe_c.IdCpe_cabecera','cpe_c.ccanot','cpe_c.ccabaj','cpe_c.codhash','estadopago','estado','referencia','ccadessun','cliente.telefono','mod_nom','mar_nom','pronom','equi_ser','equi_id','observaciones','coordinadores.name as nom_coor','coordinadores.apeusu as ape_coor','tecnicos.name as nom_tec','tecnicos.apeusu as ape_tec','cpe_c.est_ord_cod','supervisor.name as nom_sup','supervisor.apeusu as ape_sup')
         ->leftjoin('estados_ordenes','estados_ordenes.est_ord_cod','cpe_c.est_ord_cod')
          ->leftjoin('cliente','cpe_c.clicod','=','cliente.clicod')
          ->leftjoin('tipo_documento as tip_d','cpe_c.tdocod','=','tip_d.tdocod')
            ->leftjoin('tipo_documento_identidad as tdi','cpe_c.tdicod','=','tdi.tdicod')
            ->leftjoin('moneda as mon','cpe_c.moncod','=','mon.moncod')
            ->leftjoin('productos as pro','cpe_c.equi_id','=','pro.IdProducto')
            ->leftjoin('modelos as mod','mod.mod_id','=','pro.modelo')
            ->leftjoin('marcas as mar','mar.mar_id','=','pro.marca')
            ->leftjoin('users as coordinadores','coordinadores.IdUsuario','cpe_c.coordinador')
            ->leftjoin('users as tecnicos','tecnicos.IdUsuario','cpe_c.tecnico')
            ->leftjoin('users as supervisor','supervisor.IdUsuario','cpe_c.supervisor')
          ->where('cpe_c.id_empresa_negocio',Auth::user()->id_empresa_negocio)
          ->where('cpe_c.est_ord_cod',$opcion)
            ->where('cpe_c.fechacot','>=',$fecin)
            ->where('cpe_c.fechacot','<=',$fecfin)
           ->where('cpe_c.tdocod','70')
           ->orderby('IdCpe_cabecera','desc')
          ->get();


     
             $vista = view('empresas.reportes.reporte_taller_ordenes',compact('ordenes'))->render();

              if($request->ajax()){
                 return response()->json(['vista'=>$vista]);

              }

      

    }


     public function generar_reportes_taller_excel(Request $request){

        $fecin = $request->get('fecin');
        $fecfin = $request->get('fecfin');

        $opcion = $request->get('opcion');

        $negocios = DB::tABLE('empresa_negocios')->get();
        $sucursal = $request->get('sucursal');
  

        if(empty($fecin)){

          $fecin = now()->modify('first day of this month')->format('Y-m-d');
          $fecfin = now()->modify('last day of this month')->format('Y-m-d');

        }

  
        $empresa = Empresa::findOrFail(Auth::user()->IdEmpresa);
           
        $IdEmpresa = Auth::user()->IdEmpresa;
    

         $ordenes = DB::tABLE('cpe_cabecera as cpe_c')->select('est_ord_nom','fechacot','cpe_c.estadocobrar','ccacodsun','ccafem','tdodes','cpe_c.serdoc','cpe_c.numdoc','tdides','cpe_c.ccandi','cpe_c.ccanom','monnom','cpe_c.ccaitv','cpe_c.tdocod','cpe_c.IdCpe_cabecera','cpe_c.ccanot','cpe_c.ccabaj','cpe_c.codhash','estadopago','estado','referencia','ccadessun','cliente.telefono','mod_nom','mar_nom','pronom','equi_ser','equi_id','observaciones','coordinadores.name as nom_coor','coordinadores.apeusu as ape_coor','tecnicos.name as nom_tec','tecnicos.apeusu as ape_tec','cpe_c.est_ord_cod','supervisor.name as nom_sup','supervisor.apeusu as ape_sup')
         ->leftjoin('estados_ordenes','estados_ordenes.est_ord_cod','cpe_c.est_ord_cod')
          ->leftjoin('cliente','cpe_c.clicod','=','cliente.clicod')
          ->leftjoin('tipo_documento as tip_d','cpe_c.tdocod','=','tip_d.tdocod')
            ->leftjoin('tipo_documento_identidad as tdi','cpe_c.tdicod','=','tdi.tdicod')
            ->leftjoin('moneda as mon','cpe_c.moncod','=','mon.moncod')
            ->leftjoin('productos as pro','cpe_c.equi_id','=','pro.IdProducto')
            ->leftjoin('modelos as mod','mod.mod_id','=','pro.modelo')
            ->leftjoin('marcas as mar','mar.mar_id','=','pro.marca')
            ->leftjoin('users as coordinadores','coordinadores.IdUsuario','cpe_c.coordinador')
            ->leftjoin('users as tecnicos','tecnicos.IdUsuario','cpe_c.tecnico')
            ->leftjoin('users as supervisor','supervisor.IdUsuario','cpe_c.supervisor')
          ->where('cpe_c.id_empresa_negocio',Auth::user()->id_empresa_negocio)
          ->where('cpe_c.est_ord_cod',$opcion)
            ->where('cpe_c.fechacot','>=',$fecin)
            ->where('cpe_c.fechacot','<=',$fecfin)
           ->where('cpe_c.tdocod','70')
           ->orderby('IdCpe_cabecera','desc')
          ->get();

                   Excel::create('ordenes', function($excel) use ($ordenes) {

                        $excel->sheet('ordenes', function($sheet) use ($ordenes) {

                       
                            
                                  $sheet->loadView('formatos_reportes_excel.reporte_taller_ordenes',compact('ordenes'));
                          
                                

                        });

                    })->export('xlsx'); 


      

    }



     public function generar_reportes_taller_pdf(Request $request){

        $fecin = $request->get('fecin');
        $fecfin = $request->get('fecfin');

        $opcion = $request->get('opcion');

        $negocios = DB::tABLE('empresa_negocios')->get();
        $sucursal = $request->get('sucursal');
  

        if(empty($fecin)){

          $fecin = now()->modify('first day of this month')->format('Y-m-d');
          $fecfin = now()->modify('last day of this month')->format('Y-m-d');

        }

        $dat_suc = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',$sucursal)->first();
  
        $empresa = Empresa::findOrFail(Auth::user()->IdEmpresa);
           
        $IdEmpresa = Auth::user()->IdEmpresa;
    

         $ordenes = DB::tABLE('cpe_cabecera as cpe_c')->select('est_ord_nom','fechacot','cpe_c.estadocobrar','ccacodsun','ccafem','tdodes','cpe_c.serdoc','cpe_c.numdoc','tdides','cpe_c.ccandi','cpe_c.ccanom','monnom','cpe_c.ccaitv','cpe_c.tdocod','cpe_c.IdCpe_cabecera','cpe_c.ccanot','cpe_c.ccabaj','cpe_c.codhash','estadopago','estado','referencia','ccadessun','cliente.telefono','mod_nom','mar_nom','pronom','equi_ser','equi_id','observaciones','coordinadores.name as nom_coor','coordinadores.apeusu as ape_coor','tecnicos.name as nom_tec','tecnicos.apeusu as ape_tec','cpe_c.est_ord_cod','supervisor.name as nom_sup','supervisor.apeusu as ape_sup')
         ->leftjoin('estados_ordenes','estados_ordenes.est_ord_cod','cpe_c.est_ord_cod')
          ->leftjoin('cliente','cpe_c.clicod','=','cliente.clicod')
          ->leftjoin('tipo_documento as tip_d','cpe_c.tdocod','=','tip_d.tdocod')
            ->leftjoin('tipo_documento_identidad as tdi','cpe_c.tdicod','=','tdi.tdicod')
            ->leftjoin('moneda as mon','cpe_c.moncod','=','mon.moncod')
            ->leftjoin('productos as pro','cpe_c.equi_id','=','pro.IdProducto')
            ->leftjoin('modelos as mod','mod.mod_id','=','pro.modelo')
            ->leftjoin('marcas as mar','mar.mar_id','=','pro.marca')
            ->leftjoin('users as coordinadores','coordinadores.IdUsuario','cpe_c.coordinador')
            ->leftjoin('users as tecnicos','tecnicos.IdUsuario','cpe_c.tecnico')
            ->leftjoin('users as supervisor','supervisor.IdUsuario','cpe_c.supervisor')
          ->where('cpe_c.id_empresa_negocio',Auth::user()->id_empresa_negocio)
          ->where('cpe_c.est_ord_cod',$opcion)
            ->where('cpe_c.fechacot','>=',$fecin)
            ->where('cpe_c.fechacot','<=',$fecfin)
           ->where('cpe_c.tdocod','70')
           ->orderby('IdCpe_cabecera','desc')
          ->get();

          $rutapdf = public_path().'/pdfreportes/';

          $nompdffile = 'REPORTE_ORDENES.pdf';

          if(file_exists($rutapdf.$nompdffile)){
            unlink($rutapdf.$nompdffile);
          }


          $view = \View::make('formatos_reportes.reportes_taller_ordenes', compact('ordenes','dat_suc','empresa'));

                  
          $pdf = \App::make('dompdf.wrapper');
          $contenido = $view->render();
          $pdf->loadHTML($view)->setPaper('A4', 'lanscape')->save($rutapdf.$nompdffile);



          if (file_exists($rutapdf.$nompdffile))
          {
            $headers = array(
              'Content-Type: application/pdf',
            );

            return response()->download($rutapdf.$nompdffile);

          }


      

    }





}
