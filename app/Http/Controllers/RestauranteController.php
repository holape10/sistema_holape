<?php

namespace MasterSoft\Http\Controllers;

use Illuminate\Http\Request;
use MasterSoft\Http\Requests;
use MasterSoft\Empresa;
use MasterSoft\pedidos;
use MasterSoft\usuario_pedidos;
use MasterSoft\usuario_facturacion;
use MasterSoft\mesas;
use MasterSoft\pedidos_detalle;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Input;
use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\FilePrintConnector;
use Mike42\Escpos\EscposImage;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Mike42\Escpos\PrintConnectors\DummyPrintConnector;
use Greenter\Model\Client\Client;
use Greenter\Model\Company\Company;
use Greenter\Model\Company\Address;
use Greenter\Model\Sale\Invoice;
use Greenter\Model\Sale\SaleDetail;
use Greenter\XMLSecLibs\Sunat\SignedXML;
use Greenter\Xml\Builder\InvoiceBuilder;
use Greenter\Xml\Builder\VoidedBuilder;
use Greenter\Xml\Builder\NoteBuilder;
use Greenter\Xml\Builder\SummaryBuilder;
use Greenter\Model\Sale\Legend;
use Greenter\Ws\Services\SunatEndpoints;
use Greenter\Model\Sale\Note;
use Greenter\Model\Voided\Voided;
use Greenter\Model\Voided\VoidedDetail;
use Greenter\Model\Summary\Summary;
use Greenter\Model\Summary\SummaryDetail;
use Greenter\Model\Summary\SummaryPerception;
use Greenter\Model\Sale\Document;
use Greenter\Ws\Services\SoapClient;
use Greenter\Ws\Services\SummarySender;
use Greenter\Ws\Services\BillSender;
use Greenter\Ws\Services\ExtService;
use Greenter\Model\Sale\FormaPagos\FormaPagoContado;
use Greenter\Ws\Services\ConsultCdrService;
use Greenter\Model\Despatch\Despatch;
use Greenter\Model\Despatch\DespatchDetail;
use Greenter\Model\Despatch\Direction;
use Greenter\Model\Despatch\Shipment;
use Greenter\Model\Despatch\Transportist;
use Greenter\Xml\Builder\DespatchBuilder;
use Greenter\Model\Sale\Charge;
use Greenter\See;
use DOMDocument;
use MasterSoft\resumenes;
use MasterSoft\Mail\FacturacionEmail;
use Illuminate\Support\Facades\Mail;
use MasterSoft\tipos_vehiculos;
use MasterSoft\cuentascobrar;
use MasterSoft\guias_remision;
use MasterSoft\guias_remision_detalle;
use MasterSoft\movimientoscaja;
use MasterSoft\movimientosbancarios;
use MasterSoft\cuentascobrardetalle;
use MasterSoft\Cliente;
use MasterSoft\caja;
use MasterSoft\cpe_cabecera;
use MasterSoft\documento_relacionado;
use MasterSoft\unidad_medida;
use MasterSoft\cpe_detalle;
use MasterSoft\cpe_baja;
use MasterSoft\movimientos;
use MasterSoft\movimientosinsumos;
use MasterSoft\EmpresaNegocios;
use MasterSoft\insumos;
use MasterSoft\Comprobante;
use MasterSoft\cpe_nota_detalle;
use MasterSoft\cpe_nota;
use MasterSoft\User;
use MasterSoft\tipo_documento_identidad;
use MasterSoft\productos;
use MasterSoft\tipocambio;
use MasterSoft\MontoLetras;
use MasterSoft\Http\Requests\FacturaCreateFormRequest;
use QR_Code\Exceptions\InvalidVCardAddressEntryException;
use QR_Code\Exceptions\InvalidVCardPhoneEntryException;
use QR_Code\Types\QR_CalendarEvent;
use QR_Code\Types\QR_EmailMessage;
use QR_Code\Types\QR_meCard;
use QR_Code\Types\QR_Phone;
use QR_Code\Types\QR_Sms;
use QR_Code\Types\QR_Text;
use QR_Code\Types\QR_Url;
use QR_Code\Types\QR_VCard;
use QR_Code\Types\QR_WiFi;
use QR_Code\Types\vCard\Person;
use QR_Code\Types\vCard\Phone;
use DB;
use Hash;
use PDF;
use Config;
use Carbon;
use Jenssegers\Agent\Agent;
use Illuminate\Support\Facades\Log;

class RestauranteController extends Controller
{


    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
   
    public function index(){

        $primer_piso = DB::tABLE('pisos')
        ->where('suc_id',Auth::user()->id_empresa_negocio)
        ->first();

        if(!empty($primer_piso)){
           $mesas = DB::tABLE('mesas')
           ->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
           ->where('mesas.pis_id',$primer_piso->pis_id)
           ->orderby('mesas.mes_nom','asc')
           ->get();    
        }

       $mozos = DB::tABLE('users')
       ->join('role_user','role_user.user_IdUsuario','users.IdUsuario')
       ->where('role_id','8')
       ->get();

       $categorias = DB::tABLE('categorias')->get();

       $pisos = DB::tABLE('pisos')
       ->where('suc_id',Auth::user()->id_empresa_negocio)
       ->get(); 

       $empresa = Empresa::findorFail(Auth::user()->IdEmpresa);
       
       return view('empresas.restaurante.consola',compact('mesas','primer_piso','pisos','mozos','categorias','empresa'));
    }

   public function consola()
   {

    $agent = new Agent(); // Instancia el agente de detección

    $primer_piso = DB::tABLE('pisos')
    ->where('suc_id',Auth::user()->id_empresa_negocio)
    ->first();

       $empresa = Empresa::findorFail(Auth::user()->IdEmpresa);

       $mesas = collect();
    if(!empty($primer_piso)){
       $mesas = DB::tABLE('mesas')
       ->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
       ->where('mesas.pis_id',$primer_piso->pis_id)
       ->where('ind_union','0')
       ->orderby('mesas.mes_nom','asc')
       ->get();    
   }

   $mozos = DB::tABLE('users')
   ->join('role_user','role_user.user_IdUsuario','users.IdUsuario')
   ->where('role_id','8')
   ->get();

   $categorias = DB::tABLE('categorias')->where('visible','1')->get();

   $cat_pred =  DB::tABLE('categorias')->where('predeterminado','1')->first();

   $pisos = DB::tABLE('pisos')
   ->where('suc_id',Auth::user()->id_empresa_negocio)
   ->get();

   if ($agent->isMobile() || $agent->isTablet()) {
            // Si es un dispositivo móvil o tablet, cargar la vista optimizada
            //return view('empresas.restaurante.consolamobil', compact('mesas', 'primer_piso', 'pisos', 'mozos', 'categorias', 'cat_pred', 'empresa'));
            return view('empresas.restaurante.consola', compact('mesas', 'primer_piso', 'pisos', 'mozos', 'categorias', 'cat_pred', 'empresa'));
        } else {
            // Si es un dispositivo de escritorio, cargar la vista original
            return view('empresas.restaurante.consola', compact('mesas', 'primer_piso', 'pisos', 'mozos', 'categorias', 'cat_pred', 'empresa'));
        }
            
}

public function showKioskoCobranza($ped_id)
    {
        $cabecera = DB::table('pedidos')
            ->where('ped_id', $ped_id)
            ->where('ped_est', 'Aperturado')
            ->first();

        if (empty($cabecera)) {
            // Redirige si el pedido no se encuentra o no está aperturado
            return redirect()->route('kiosko.seleccion_servicio')->with('error', 'Pedido no encontrado o ya ha sido cobrado/anulado.');
        }

        $empresa = \MasterSoft\Empresa::findOrFail(Auth::user()->IdEmpresa); // Usar namespace completo
        $negocio = DB::table('empresa_negocios')->where('id_empresa_negocio', Auth::user()->id_empresa_negocio)->first(); // Usar DB::table

        // Cargar los detalles del pedido, incluyendo items_facturado
        $detalle = DB::table('pedidos_detalle')
            ->join('productos', 'productos.IdProducto', '=', 'pedidos_detalle.IdProducto')
            ->where('ped_id', $cabecera->ped_id)
            ->where('estadoitem', 'Ingresado') // Solo ítems no eliminados
            ->select('pedidos_detalle.*', 'productos.pronom', 'productos.procod', 'productos.umecod', 'productos.factor', 'productos.icbper', 'productos.costo', 'productos.promocion', 'productos.pro_rel', 'productos.tigcod')
            ->get();

        // Calcular el total a cobrar de los items no facturados
        $total_pendiente_cobrar = 0;
        $total_icbper_pendiente = 0;
        $icbper_val = $empresa->icbper ?? 0;

        foreach ($detalle as $item) {
            $cantidad_a_cobrar = $item->ped_det_can - $item->item_facturado;
            if ($cantidad_a_cobrar > 0) {
                $total_pendiente_cobrar += ($cantidad_a_cobrar * $item->ped_det_pre);
                if (isset($item->icbper_ind) && $item->icbper_ind == 1) {
                    $total_icbper_pendiente += ($cantidad_a_cobrar * $icbper_val);
                }
            }
        }
        $total_pendiente_cobrar += $total_icbper_pendiente;
        $cabecera->ped_tot = round($total_pendiente_cobrar, 2); // Actualizar ped_tot para mostrar el saldo a cobrar
        $cabecera->icbper_tot = round($total_icbper_pendiente, 2);


        $comprobantes = DB::table('tipo_documento')->where('caja', '1')->get();
        $documentos = DB::table('tipo_documento_identidad')->orderBy('orden', 'asc')->get();
        $estadopagos = DB::table('credito_dias')->get(); // Acceso directo a la tabla
        $mediospagos = DB::table('medios_pagos')->get();

        $dat_pis = null;
        $dat_mes = null;
        if (!empty($cabecera->pis_id)) {
            $dat_pis = DB::table('pisos')->where('pis_id', $cabecera->pis_id)->first();
        }
        if (!empty($cabecera->mes_id)) {
            $dat_mes = DB::table('mesas')->where('mes_id', $cabecera->mes_id)->first();
        }

        return view('empresas.kiosko.kiosko_cobranza', compact(
            'cabecera',
            'detalle',
            'empresa',
            'negocio',
            'comprobantes',
            'documentos',
            'estadopagos',
            'mediospagos',
            'dat_pis',
            'dat_mes'
        ));
    }



    public function indexcomandas(Request $request)
    {
        $negocios = DB::table('empresa_negocios')->get();
        
        $sucursal = $request->get('sucursal');
        if(empty($sucursal)){
            $sucursal = Auth::user()->id_empresa_negocio;
        }

        // Cambiamos 'fec_ini' por 'fecin' para que coincida con la vista
        $fec_ini = $request->get('fecin'); 
        $fec_fin = $request->get('fecfin');

        if(empty($fec_ini)){
            $fec_ini = now()->format('Y-m-d');
        }
        if(empty($fec_fin)){
            $fec_fin = now()->format('Y-m-d');
        }

        // Filtros adicionales
        $ped_tip = $request->get('ped_tip');
        $ped_est = $request->get('ped_est');

        // Resumen de montos (usando los mismos filtros que la tabla)
        $resumen = DB::table('pedidos')
            ->where('ped_fec', '>=', $fec_ini)
            ->where('ped_fec', '<=', $fec_fin)
            ->where('id_empresa_negocio', $sucursal)
            ->when($ped_tip, function ($q) use ($ped_tip) {
                return $q->where('ped_tip', $ped_tip);
            })
            ->select('ped_est', 
                DB::raw('count(*) as total'), 
                DB::raw('sum(ped_tot) as monto')
            )
            ->groupBy('ped_est')
            ->get()
            ->keyBy('ped_est');

        $cant_pendientes  = isset($resumen['Aperturado']) ? $resumen['Aperturado']->total : 0;
        $monto_pendientes = isset($resumen['Aperturado']) ? $resumen['Aperturado']->monto : 0;
        $cant_cobrados    = isset($resumen['Cerrado']) ? $resumen['Cerrado']->total : 0;
        $monto_cobrados   = isset($resumen['Cerrado']) ? $resumen['Cerrado']->monto : 0;

        // Consulta de Comandas
        $comandas = DB::table('pedidos')
            ->leftjoin('mesas','mesas.mes_id','pedidos.mes_id')
            ->where('ped_fec','>=',$fec_ini)
            ->where('ped_fec','<=',$fec_fin)
            ->where('pedidos.id_empresa_negocio',$sucursal)
            ->where(function ($query) use ($ped_tip, $ped_est){
                if(!empty($ped_tip)) $query->where('ped_tip',$ped_tip);
                if(!empty($ped_est)) $query->where('ped_est',$ped_est);
            })
            ->select('pedidos.*', 'mesas.mes_nom') 
            ->orderby('fecha_hora','desc')
            ->get();

        $ids_pedidos = $comandas->pluck('ped_id')->toArray();
        $raw_detalles = DB::table('pedidos_detalle')
            ->whereIn('ped_id', $ids_pedidos)
            ->where('estadoitem', '!=', 'Eliminado')
            ->get();

        $detalles_por_pedido = $raw_detalles->groupBy('ped_id');

        return view('empresas.restaurante.indexcomandas', compact(
            'comandas', 'detalles_por_pedido', 'fec_ini', 'fec_fin',
            'ped_tip', 'ped_est', 'negocios', 'sucursal',
            'cant_pendientes', 'monto_pendientes', 'cant_cobrados', 'monto_cobrados'    
        ));
    }

public function exportarComandasExcel(Request $request)
{
    // 1. Filtros (ajustados a los nombres de tu vista buscarcomandas.blade.php)
    $sucursal = $request->get('sucursal') ?: \Auth::user()->id_empresa_negocio;
    $fec_ini = $request->get('fecin') ?: date('Y-m-d');
    $fec_fin = $request->get('fecfin') ?: date('Y-m-d');
    $ped_tip = $request->get('ped_tip');
    $ped_est = $request->get('ped_est');

    // 2. Consulta idéntica a tu indexcomandas
    $comandas = \DB::table('pedidos')
        ->leftjoin('mesas','mesas.mes_id','pedidos.mes_id')
        ->where('ped_fec','>=',$fec_ini)
        ->where('ped_fec','<=',$fec_fin)
        ->where(function ($query) use ($ped_tip){
            if(!empty($ped_tip)) $query->orwhere('ped_tip',$ped_tip);
        })
        ->where(function ($query) use ($ped_est){
            if(!empty($ped_est)) $query->orwhere('ped_est',$ped_est);
        })
        ->where('pedidos.id_empresa_negocio',$sucursal)
        ->select('pedidos.*', 'mesas.mes_nom') 
        ->orderby('fecha_hora','desc')
        ->get();

    // 3. Generar el contenido del CSV en una variable
    $output = "";
    // Agregar BOM para que Excel reconozca tildes y Ñ
    $output .=  chr(0xEF).chr(0xBB).chr(0xBF);
    
    // Cabecera
    $output .= "ID,Fecha/Hora,Cliente,Mesa,Tipo,Estado,Total\n";

    foreach ($comandas as $c) {
        $output .= implode(",", [
            $c->ped_id,
            $c->fecha_hora,
            str_replace(",", " ", $c->ped_cli_nom), // Evitar que comas rompan el CSV
            $c->mes_nom ?: 'N/A',
            $c->ped_tip,
            $c->ped_est,
            $c->ped_tot
        ]) . "\n";
    }

    //$fileName = 'Reporte_' . $fec_ini . '.csv';
    $fileName = 'Reporte_Comandas_' . $fec_ini . '_al_' . $fec_fin . '.csv';

    return response($output)
        ->header('Content-Type', 'text/csv; charset=utf-8')
        ->header('Content-Disposition', 'attachment; filename="' . $fileName . '"')
        ->header('Pragma', 'no-cache')
        ->header('Expires', '0');
}

    public function indexcaja($codfact='')
    {
        $ventas = 0;
        $efectivo = 0;
        $sum_mp = collect();
        
        $docs_permitidos = ['01', '03', '07', '08', '13'];
        $id_empresa = Auth::user()->id_empresa_negocio;
        $id_usuario = Auth::user()->IdUsuario;

        // NUEVO: Verificar si el usuario es Admin (role_id = 2)
        $es_admin = DB::table('role_user')
            ->where('user_IdUsuario', $id_usuario)
            ->where('role_id', 2)
            ->exists();

        // 1. Obtener datos estructurales (Mesas, Pisos, etc.)
        $primer_piso = DB::table('pisos')->where('suc_id', $id_empresa)->first();
        $mesas = [];
        if(!empty($primer_piso)){
            $mesas = DB::table('mesas')
                ->where('id_empresa_negocio', $id_empresa)
                ->where('mesas.pis_id', $primer_piso->pis_id)
                ->where('ind_union', '0')
                ->orderby('mesas.mes_nom', 'asc')
                ->get();    
        }

        $mozos = DB::table('users')
            ->join('role_user', 'role_user.user_IdUsuario', 'users.IdUsuario')
            ->where('role_id', '8')
            ->get();

        $categorias = DB::table('categorias')->get();
        $pisos = DB::table('pisos')->where('suc_id', $id_empresa)->get();
        $medios = DB::table('medios_pagos')->where('IdEmpresa', Auth::user()->IdEmpresa)->get();
        $datos = DB::table('empresa_negocios')->where('id_empresa_negocio', $id_empresa)->first(); 
        $dat_suc = $datos; // Optimización: usamos la misma variable, no hace doble consulta

        $sucursales = DB::table('users as us')
            ->leftjoin('empresa_negocios as en', 'en.id_empresa_negocio', 'us.id_empresa_negocio')
            ->where('us.IdUsuario', $id_usuario)
            ->get();

        // 2. LÓGICA DE TURNOS Y TOTALES (Aquí está la magia)
        $turno = null;
        $turnos_ids = collect(); // Guardará los IDs de los turnos a sumar

        if ($es_admin) {
            // ADMIN: Obtiene TODOS los turnos aperturados de la empresa
            $turnos_ids = DB::table('turnos')
                ->where('id_empresa_negocio', $id_empresa) // Asegúrate que este campo exista en tu tabla turnos
                ->where('estado', 'Aperturado')
                ->pluck('id_turno');
            
            if ($turnos_ids->isNotEmpty()) {
                // Creamos un objeto "falso" para que la vista no se rompa y sepa que hay caja abierta
                $turno = (object) ['id_turno' => 'global', 'estado' => 'Aperturado'];
            }
        } else {
            // USUARIO NORMAL: Solo su turno
            $turno = DB::table('turnos')
                ->where('id_turno', Auth::user()->id_turno)
                ->where('estado', 'Aperturado')
                ->first();
                
            if ($turno) {
                $turnos_ids = collect([$turno->id_turno]);
            }
        }

        // 3. Calcular totales si hay al menos un turno aperturado
        if ($turnos_ids->isNotEmpty()) {
            // Total Ventas
            $ventas = DB::table('cpe_cabecera')
                ->join('venta_medio_pago', 'venta_medio_pago.IdCpe_cabecera', 'cpe_cabecera.IdCpe_cabecera')
                ->whereNull('cpe_cabecera.ccabaj') // NUEVO: Alias explícito para evitar errores
                ->whereIn('venta_medio_pago.id_turno', $turnos_ids) // NUEVO: Usa el array de IDs
                ->whereIn('cpe_cabecera.tdocod', $docs_permitidos)
                ->sum('venta_medio_pago.monto');

            // Total Efectivo
            $efectivo = DB::table('cpe_cabecera')
                ->join('venta_medio_pago', 'venta_medio_pago.IdCpe_cabecera', 'cpe_cabecera.IdCpe_cabecera')
                ->join('medios_pagos', 'medios_pagos.id_med_pag', 'venta_medio_pago.id_med_pag')
                ->whereNull('cpe_cabecera.ccabaj')
                ->whereIn('venta_medio_pago.id_turno', $turnos_ids)         
                ->where('medios_pagos.predeterminado', '1')
                ->whereIn('cpe_cabecera.tdocod', $docs_permitidos)
                ->sum('venta_medio_pago.monto');

            // Resumen por medio de pago
            $sum_mp = DB::table('cpe_cabecera')
                ->select('medios_pagos.nom_med_pag', DB::RAW('SUM(venta_medio_pago.monto) as monto_total'))
                ->join('venta_medio_pago', 'venta_medio_pago.IdCpe_cabecera', 'cpe_cabecera.IdCpe_cabecera')
                ->join('medios_pagos', 'medios_pagos.id_med_pag', 'venta_medio_pago.id_med_pag')
                ->whereNull('cpe_cabecera.ccabaj')
                ->whereIn('venta_medio_pago.id_turno', $turnos_ids)
                ->whereIn('cpe_cabecera.tdocod', $docs_permitidos)
                ->groupBy('venta_medio_pago.id_med_pag', 'medios_pagos.nom_med_pag') // NUEVO: GroupBy completo para evitar errores SQL estrictos
                ->get();
        }

        // 4. OPTIMIZACIÓN: Cargar todos los pedidos activos de una sola vez (Adiós problema N+1)
        $pedidos_activos = collect();
        if (!empty($mesas)) {
            $mesas_ids = $mesas->pluck('mes_id');
            $pedidos_activos = DB::table('pedidos')
                ->whereIn('mes_id', $mesas_ids)
                ->where('ped_est', 'Aperturado')
                ->get()
                ->keyBy('mes_id'); // Indexa por mes_id para búsqueda instantánea en la vista
        }

        return view('empresas.restaurante.consola_caja', compact(
            'datos', 'sum_mp', 'codfact', 'mesas', 'primer_piso', 'pisos', 
            'mozos', 'categorias', 'turno', 'ventas', 'efectivo', 
            'sucursales', 'medios', 'dat_suc', 'es_admin', 'pedidos_activos' // NUEVO: enviamos estas variables
        ));
    }
    
/*public function indexcaja($codfact='')
{
    $ventas = 0;
    $efectivo = 0;

    // Definimos los documentos permitidos (Factura, Boleta, NC, ND, Ticket)
    $docs_permitidos = ['01', '03', '07', '08', '13'];

    $primer_piso = DB::table('pisos')
        ->where('suc_id', Auth::user()->id_empresa_negocio)
        ->first();

    if(!empty($primer_piso)){
        $mesas = DB::table('mesas')
            ->where('id_empresa_negocio', Auth::user()->id_empresa_negocio)
            ->where('mesas.pis_id', $primer_piso->pis_id)
            ->where('ind_union', '0')
            ->orderby('mesas.mes_nom', 'asc')
            ->get();    
    }

    $mozos = DB::table('users')
        ->join('role_user', 'role_user.user_IdUsuario', 'users.IdUsuario')
        ->where('role_id', '8')
        ->get();

    $categorias = DB::table('categorias')->get();

    $pisos = DB::table('pisos')
        ->where('suc_id', Auth::user()->id_empresa_negocio)
        ->get();

    $turno = DB::table('turnos')
        ->where('id_turno', Auth::user()->id_turno)
        ->where('estado', 'Aperturado')
        ->first();

    $dat_suc = DB::table('empresa_negocios')->where('id_empresa_negocio', Auth::user()->id_empresa_negocio)->first();

    if(!empty($turno)){
        // Total Ventas filtrado
        $ventas = DB::table('cpe_cabecera')
            ->join('venta_medio_pago', 'venta_medio_pago.IdCpe_cabecera', 'cpe_cabecera.IdCpe_cabecera')
            ->whereNull('ccabaj')
            ->where('venta_medio_pago.id_turno', $turno->id_turno)
            ->whereIn('cpe_cabecera.tdocod', $docs_permitidos) // CAMBIO AQUÍ
            ->sum('monto');

        // Total Efectivo filtrado
        $efectivo = DB::table('cpe_cabecera')
            ->join('venta_medio_pago', 'venta_medio_pago.IdCpe_cabecera', 'cpe_cabecera.IdCpe_cabecera')
            ->join('medios_pagos', 'medios_pagos.id_med_pag', 'venta_medio_pago.id_med_pag')
            ->whereNull('ccabaj')
            ->where('venta_medio_pago.id_turno', $turno->id_turno)         
            ->where('medios_pagos.predeterminado', '1')
            ->whereIn('cpe_cabecera.tdocod', $docs_permitidos) // CAMBIO AQUÍ
            ->sum('monto');
    }

    $sucursales = DB::table('users as us')
        ->leftjoin('empresa_negocios as en', 'en.id_empresa_negocio', 'us.id_empresa_negocio')
        ->where('us.IdUsuario', Auth::user()->IdUsuario)
        ->get();

    $sum_mp = "";

    if(!empty($turno->id_turno)){
        // Resumen por medio de pago filtrado
        $sum_mp = DB::table('cpe_cabecera')->select('nom_med_pag', DB::RAW('SUM(monto) as monto_total'))
            ->join('venta_medio_pago', 'venta_medio_pago.IdCpe_cabecera', 'cpe_cabecera.IdCpe_cabecera')
            ->join('medios_pagos', 'medios_pagos.id_med_pag', 'venta_medio_pago.id_med_pag')
            ->whereNull('ccabaj')
            ->where('venta_medio_pago.id_turno', $turno->id_turno)
            ->whereIn('cpe_cabecera.tdocod', $docs_permitidos) // CAMBIO AQUÍ
            ->groupby('venta_medio_pago.id_med_pag')
            ->get();
    }

    $medios = DB::table('medios_pagos')->where('IdEmpresa', Auth::user()->IdEmpresa)->get();

    $datos = DB::table('empresa_negocios')->where('id_empresa_negocio', '=', Auth::user()->id_empresa_negocio)
        ->first(); 

    return view('empresas.restaurante.consola_caja', compact('datos', 'sum_mp', 'codfact', 'mesas', 'primer_piso', 'pisos', 'mozos', 'categorias', 'turno', 'ventas', 'efectivo', 'sucursales', 'medios', 'dat_suc'));
}*/



public function buscar_pedido_mesa(Request $request, $mesa){
    $cabecera = DB::table('pedidos')->where('mes_id',$mesa)->where('ped_est','Aperturado')->first();
    $mozos = DB::table('users')
        ->join('role_user','role_user.user_IdUsuario','users.IdUsuario')
        ->where('role_id','8')
        ->get();
    
    if(!empty($cabecera)){
        // ✅ MODIFICADO: Solo items pendientes de facturar
        $detalle = DB::table('pedidos_detalle')
            ->join('productos','productos.IdProducto','pedidos_detalle.IdProducto')
            ->select('pedidos_detalle.*', 'productos.*',
                DB::raw('(ped_det_can - IFNULL(item_facturado, 0)) as cantidad_pendiente'))
            ->where('ped_id',$cabecera->ped_id)
            ->where('estadoitem','Ingresado')
            ->whereRaw('(ped_det_can - IFNULL(item_facturado, 0)) > 0') // Solo pendientes
            ->get();
        
        $dat_pis = DB::table('pisos')->where('pis_id',$cabecera->pis_id)->first();
        $dat_mes = DB::table('mesas')->where('mes_id',$mesa)->first();
        
        $vista = view('empresas.restaurante.listar_pedido',compact('cabecera','detalle','dat_pis','mozos','dat_mes'))->render();
        
        if($request->ajax()){        
            return response()->json(['vista'=>$vista,'estado'=>'1','ped_id'=>$cabecera->ped_id]);
        }
    }else{
        if($request->ajax()){    
            return response()->json(['estado'=>'0']);
        }
    }
}


public function buscar_pedido_llevar_delivery(Request $request, $ped_id){

    $cabecera = DB::tABLE('pedidos')->where('ped_id',$ped_id)->where('ped_est','Aperturado')->first();

    $mozos = DB::tABLE('users')
    ->join('role_user','role_user.user_IdUsuario','users.IdUsuario')
    ->where('role_id','8')
    ->get();

    if(!empty($cabecera)){

       $detalle = DB::tABLE('pedidos_detalle')
       ->join('productos','productos.IdProducto','pedidos_detalle.IdProducto')
       ->where('ped_id',$cabecera->ped_id)
       ->where('estadoitem','Ingresado')
       ->get();

       $dat_pis = DB::tABLE('pisos')->where('pis_id',$cabecera->pis_id)->first();

          //$dat_mes = DB::tABLE('mesas')->where('mes_id',$mesa)->first();

       $vista = view('empresas.restaurante.listar_pedido',compact('cabecera','detalle','dat_pis','mozos','dat_mes'))->render();

       if($request->ajax()){
        
        return response()->json(['vista'=>$vista,'estado'=>'1','ped_id'=>$cabecera->ped_id,'cabecera'=>$cabecera]);

    }

}else{

  if($request->ajax()){
    
    return response()->json(['estado'=>'0']);

}

}


}



    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */


    public function validarAdminPassword(Request $request)
    {
        $password = $request->input('password');

        // Buscar usuarios con roles de 'admin', 'superadmin' o 'caja'
        // Puedes ajustar los IDs de rol según tu tabla `roles`
        //$allowedRoles = ['admin', 'superadmin', 'caja']; // Ajusta estos roles según tu configuración
        $allowedRoles = ['admin', 'superadmin']; // Ajusta estos roles según tu configuración
        
        $users = User::whereHas('roles', function($query) use ($allowedRoles) {
            $query->whereIn('name', $allowedRoles);
        })->get();

        foreach ($users as $user) {
            if (Hash::check($password, $user->password)) {
                // Contraseña válida para al menos un usuario autorizado
                return response()->json(['success' => true]);
            }
        }

        // Si no se encontró ningún usuario autorizado con esa contraseña
        return response()->json(['success' => false, 'message' => 'Contraseña de administrador incorrecta o usuario no autorizado.']);
    }


    public function store(Request $request)
    {
        $rucemp = trim(Auth::user()->IdEmpresa);
        $empresa = Empresa::findOrFail($rucemp);
        $mesas = $request->get('txtMesaId');
        $fecha = $request->get('fecha');
        $producto = $request->get('proid');
        $cantidad  = $request->get('cant');
        $unidad = $request->get('unid');
        $valuni = $request->get('provun');
        $preuni = $request->get('propun');
        $subtotal = $request->get('subtotal');
        $igv = $request->get('igv');
        $total = $request->get('total');
        $totalitem = $request->get('itemtotal');
        $detalle = $request->get('detalle');
        $ped_tot = $request->get('total_venta');

        $pedido = new pedidos;
        $pedido->mes_id=$mesas;
        $pedido->fecha = $fecha;
        $pedido->total=$total;
        $pedido->subtotal=$subtotal;
        $pedido->est_ped_id ='1';
        $pedido->igv=$igv;
        $pedido->ped_tot = $ped_tot;
        $pedido->ped_tip = 'SALON';
        $pedido->IdEmpresa=$rucemp;
        $pedido->id_empresa_negocio = Auth::user()->id_empresa_negocio;
        $pedido->ped_est='Aperturado';
        $pedido->IdUsuario = Auth::user()->IdUsuario;
        if(!empty($request->get('mozo'))){
            $pedido->mozo = $request->get('mozo');
        }else{
            $pedido->mozo = Auth::user()->IdUsuario;
        }
        $pedido->save();

        $id_ped = $pedido->ped_id;

        $mesa = mesas::findOrFail($mes_id);
        $mesa->mes_est='Ocupado';
        $mesa->update();

        if(!empty($unidad)){
          foreach ($unidad as $index => $unid) {
            // code...
            $subitem =$valuni[$index]*$cantidad[$index];
            $igvitem =$totalitem[$index]-$subitem;

            $ped_det = new pedidos_detalle;
            $ped_det->ped_id=$pedido->ped_id;
            $ped_det->IdProducto=$producto[$index];
            $ped_det->cantidad=$cantidad[$index];
            $ped_det->unidad=$unidad[$index];
            $ped_det->provunitem=$valuni[$index];
            $ped_det->propunitem=$preuni[$index];
            $ped_det->igvitem=$igvitem;
            $ped_det->subtotalitem=$subitem;
            $ped_det->IdEmpresa=$rucemp;
            $ped_det->totalitem=$totalitem[$index];
            $ped_det->detalle = $detalle[$index];
            
            $ped_det->impreso = 'imprimir';
            $ped_det->save();

        }
    }
    

    $usuario_pedidos = new usuario_pedidos;
    $usuario_pedidos->ped_id = $id_ped;
    $usuario_pedidos->id_turno = Auth::user()->id_turno;
    $usuario_pedidos->id_empresa_negocio = Auth::user()->id_empresa_negocio;
    $usuario_pedidos->IdEmpresa = Auth::user()->IdEmpresa;
    $usuario_pedidos->referencia = "Registro";
    $usuario_pedidos->save();

    for($i=1;$i<=$empresa->imp_pedido;$i++){
        self::imprimirpedido($id_ped);
    }

    if($request->ajax()) {
        return response()->json(['mensaje' => 'Registrado correctamente','id_ped'=>$id_ped]);
    }
    
  }

  public function adicionarpedidollevar(Request $request)
  {
    $rucemp = trim(Auth::user()->IdEmpresa);
    $empresa = Empresa::findOrFail($rucemp);
    $mesas = $request->get('txtMesaId');
    $fecha = $request->get('fecha');
    $producto = $request->get('proid');
    $cantidad  = $request->get('cant');
    $unidad = $request->get('unid');
    $valuni = $request->get('provun');
    $preuni = $request->get('propun');
    $subtotal = $request->get('subtotal');
    $igv = $request->get('igv');
    $total = $request->get('total');
    $totalitem = $request->get('itemtotal');
    $ped_id = $request->get('txtPedId');
    $detalle = $request->get('detalle');
    $iddetalle = $request->get('iddetalle');

    
    $pedido = pedidos::findOrFail($ped_id);
    
    $pedido->fecha = $fecha;
    $pedido->total=$total;
    $pedido->subtotal=$subtotal;
    $pedido->igv=$igv;
    $pedido->fecha_hora_modificacion = now()->format('Y-m-d H:i:s');
    $pedido->IdEmpresa=$rucemp;
    $pedido->ped_est='Aperturado';
    $pedido->tipo = $request->get('tipo_pedido');
    
    $pedido->IdUsuarioMod = Auth::user()->IdUsuario;
    $pedido->id_empresa_negocio = Auth::user()->id_empresa_negocio;

    if(!empty($request->get('mozo'))){
      $pedido->mozo = $request->get('mozo');
  }else{
      $pedido->mozo = Auth::user()->IdUsuario;
  }

  $pedido->etiqueta = 'ITEM ADICIONAL';
  $pedido->update();

  $id_ped = $pedido->ped_id;

  
  
  if(!empty($unidad)){

    foreach ($unidad as $index => $unid) {
          // code...
      $subitem =$valuni[$index]*$cantidad[$index];
      $igvitem =$totalitem[$index]-$subitem;

      $buscardetalle = pedidos_detalle::WHERE('ped_det_id',$iddetalle[$index])
      ->where('estadoitem','!=','Eliminado')
      ->count();
      



      if($buscardetalle < 1 ){

        $ped_det = new pedidos_detalle;
        $ped_det->ped_id=$pedido->ped_id;
        $ped_det->IdProducto=$producto[$index];
        $ped_det->cantidad=$cantidad[$index];
        $ped_det->unidad=$unidad[$index];
        $ped_det->provunitem=$valuni[$index];
        $ped_det->propunitem=$preuni[$index];
        $ped_det->igvitem=$igvitem;
        $ped_det->subtotalitem=$subitem;
        $ped_det->IdEmpresa=$rucemp;
        $ped_det->totalitem=$totalitem[$index];
        $ped_det->detalle = $detalle[$index];
        $ped_det->impreso = 'imprimir';
        $ped_det->save();

    }
    

}

}



$usuario_pedidos = new usuario_pedidos;
$usuario_pedidos->ped_id = $id_ped;
$usuario_pedidos->id_turno = Auth::user()->id_turno;
$usuario_pedidos->id_empresa_negocio = Auth::user()->id_empresa_negocio;
$usuario_pedidos->IdEmpresa = Auth::user()->IdEmpresa;
$usuario_pedidos->referencia = "Actualizo";
$usuario_pedidos->save();

for($i=1;$i<=$empresa->imp_pedido;$i++){
  self::imprimirpedidollevar($id_ped);
}



if($request->ajax()) {
    return response()->json(['mensaje' => 'Registrado correctamente','pedido'=>$id_ped]);
}

         // $mesas = DB::tABLE('mesas')->where('IdEmpresa',$rucemp)->get();
       // return Redirect::to('/mesas');
        // return view('empresas.mesas',compact('id_ped','mesas'));
}


public function adicionarpedido(Request $request)
    {
        $rucemp = trim(Auth::user()->IdEmpresa);
        $empresa = Empresa::findOrFail($rucemp);
        $mesas = $request->get('txtMesaId');
        $fecha = $request->get('fecha');
        $producto = $request->get('proid');
        $cantidad  = $request->get('cant');
        $unidad = $request->get('unid');
        $valuni = $request->get('provun');
        $preuni = $request->get('propun');
        $subtotal = $request->get('subtotal');
        $igv = $request->get('igv');
        $total = $request->get('total');
        $totalitem = $request->get('itemtotal');
        // Asegúrate de que 'detalle' y 'item_obs' se reciban como arrays desde el frontend
        // Es crucial que los `name` de los inputs en el frontend sean `name="detalle[]"` y `name="item_obs[]"`
        $detalle_desc = $request->get('detalle'); // Renombrado para evitar conflicto con el objeto $detalle
        $item_obs_arr = $request->get('item_obs');
        $iddetalle = $request->get('iddetalle');
        $ped_id = $request->get('txtPedId');


        $pedido = pedidos::findOrFail($ped_id);
        $pedido->mes_id = $mesas;
        $pedido->fecha = $fecha;
        $pedido->total = $total;
        $pedido->subtotal = $subtotal;
        $pedido->igv = $igv;
        $pedido->IdEmpresa = $rucemp;
        
        $pedido->ped_est = 'Aperturado';
        $pedido->id_empresa_negocio = Auth::user()->id_empresa_negocio;
        $pedido->IdUsuarioMod = Auth::user()->IdUsuario;
        $pedido->fecha_hora_modificacion = now()->format('Y-m-d H:i:s');
        if(!empty($request->get('mozo'))){
            $pedido->mozo = $request->get('mozo');
        }else{
            $pedido->mozo = Auth::user()->IdUsuario;
        }
        $pedido->etiqueta = 'ITEM ADICIONAL';
        $pedido->update();

        $id_ped = $pedido->ped_id;

        $mesa = mesas::findOrFail($mesas);
        $mesa->mes_est = 'Ocupado';
        $mesa->update();

        // Antes de eliminar, necesitamos revertir el stock de los ítems existentes que van a ser "reemplazados"
        // Esta lógica es crucial si la tabla `pedidos_detalle` se vacía y se vuelve a llenar.
        $detalles_antiguos = pedidos_detalle::where('ped_id', $ped_id)->where('estadoitem', 'Ingresado')->get();
        foreach ($detalles_antiguos as $old_det) {
            $this->revertir_stock_por_item($old_det->IdProducto, $old_det->ped_det_can, Auth::user()->id_empresa_negocio);
        }

        // Eliminar todos los detalles existentes para este pedido.
        // CUIDADO: Esto borra permanentemente los registros. Si necesitas un historial,
        // podrías simplemente actualizar los existentes y agregar nuevos,
        // o marcar los eliminados como 'Eliminado' y no borrarlos físicamente.
        // Dado que la lógica original ya lo hace, lo mantenemos.
        pedidos_detalle::where('ped_id', $ped_id)->delete();

        if(is_array($producto) && !empty($producto)){ // Asegurarse de que $producto sea un array válido
            foreach ($producto as $index => $pro_id) { // Iterar sobre $producto para asegurar la sincronización
                $subitem = $valuni[$index] * $cantidad[$index];
                $igvitem = $totalitem[$index] - $subitem;

                $ped_det = new pedidos_detalle;
                $ped_det->ped_id = $pedido->ped_id;
                $ped_det->IdProducto = $pro_id; // Usar $pro_id directamente
                $ped_det->cantidad = $cantidad[$index];
                $ped_det->unidad = $unidad[$index];
                $ped_det->provunitem = $valuni[$index];
                $ped_det->propunitem = $preuni[$index];
                $ped_det->igvitem = $igvitem;
                $ped_det->subtotalitem = $subitem;
                $ped_det->IdEmpresa = $rucemp;
                $ped_det->totalitem = $totalitem[$index];
                // Usar null coalesce operator para evitar errores si el índice no existe
                $ped_det->detalle = $detalle_desc[$index] ?? null;
                $ped_det->item_obs = $item_obs_arr[$index] ?? null;
                $ped_det->impreso = 'imprimir';
                $ped_det->save();

                // Registrar el movimiento de salida para los items recién añadidos/modificados
                $this->registrar_movimiento_salida_comanda_individual($pro_id, $cantidad[$index], Auth::user()->id_empresa_negocio, $pedido->ped_cli_nom, $ped_id, $pedido->ped_fec);
            }
        }
        
        $usuario_pedidos = new usuario_pedidos;
        $usuario_pedidos->ped_id = $id_ped;
        $usuario_pedidos->id_turno = Auth::user()->id_turno;
        $usuario_pedidos->id_empresa_negocio = Auth::user()->id_empresa_negocio;
        $usuario_pedidos->IdEmpresa = Auth::user()->IdEmpresa;
        $usuario_pedidos->referencia = "Actualizo";
        $usuario_pedidos->save();

        for($i=1;$i<=$empresa->imp_pedido;$i++){
            self::imprimirpedidollevar($id_ped);
        }

        if($request->ajax()) {
            return response()->json(['mensaje' => 'Registrado correctamente','id_ped'=>$id_ped]);
        }
    }

    
    public function mostrar_mesas($id_ped,$tipo=0){
        $rucemp = trim(Auth::user()->IdEmpresa);
        
        $primer_piso = DB::tABLE('pisos')->where('suc_id',Auth::user()->id_empresa_negocio)->first();

        if(!empty($primer_piso)){

            if(Auth::user()->hasRole('admin') || Auth::user()->hasRole('caja') ){
                $mesas = DB::tABLE('mesas')
                ->join('pisos','pisos.pis_id','mesas.pis_id')
                ->where('IdEmpresa',$rucemp)
                ->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
                ->where('mesas.pis_id',$primer_piso->pis_id)
                ->orderby('mesas.mes_nom','asc')
                ->get(); 
            }else{

              $mesas = DB::tABLE('mesas')
              ->join('pisos','pisos.pis_id','mesas.pis_id')
              ->where('IdEmpresa',$rucemp)
              ->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
              ->where('mesas.pis_id',$primer_piso->pis_id)
              ->where('IdUsuario',Auth::user()->IdUsuario)
              ->orderby('mesas.mes_nom','asc')
              ->get(); 


          }
          

          
      }

      

      $users = DB::tABLE('users')->get();

      $pisos = DB::tABLE('pisos')
      ->where('suc_id',Auth::user()->id_empresa_negocio)
      ->get();
      
       // return Redirect::to('/mesas');
      return view('empresas.mesas',compact('id_ped','mesas','primer_piso','users','pisos','tipo'));
  }
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request,$id)
    {
      $mesa_id = $request->get('mesa');
      $motivo = $request->get('obser');

      $mesa = mesas::findOrFail($mesa_id);
      $mesa->mes_est ='Libre';
      $mesa->update();
      
      $deleteped = pedidos::findOrFail($id);
      $deleteped->ped_est = 'Eliminado';
      $deleteped->MotElim = $motivo;
      $deleteped->IdUsuarioDel = Auth::user()->IdUsuario;
      $deleteped->update();

      return Redirect::to('/mesas');
  }


  public function imprimirpedido($pedido){

     $IdEmpresa = Auth::user()->IdEmpresa;

     $empresanegocios = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first();

     $cab_pedido = DB::tABLE('pedidos')
     ->where('ped_id',$pedido)
     ->leftjoin('users','users.IdUsuario','pedidos.mozo')
     ->first();

     $mesa= DB::tABLE('mesas')->where('mes_id',$cab_pedido->mes_id)->first();

     
     $impresoras = DB::tABLE('configuracion_impresoras')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->get();



     foreach ($impresoras as $impresora) {
        

        $detalle = DB::tABLE('pedidos_detalle')->where('ped_id',$pedido)
        ->join('productos','pedidos_detalle.IdProducto','productos.IdProducto')
        ->leftjoin('categorias','categorias.cat_id','productos.cat_id')
        ->where('categorias.id_empresa_negocio',Auth::user()->id_empresa_negocio)
        ->where('pedidos_detalle.impreso','imprimir')
        ->where('categorias.impresora',$impresora->Id)
        ->get();

        
        $detallecount = DB::tABLE('pedidos_detalle')->where('ped_id',$pedido)
        ->join('productos','pedidos_detalle.IdProducto','productos.IdProducto')
        ->leftjoin('categorias','categorias.cat_id','productos.cat_id')
        ->where('categorias.id_empresa_negocio',Auth::user()->id_empresa_negocio)
        ->where('pedidos_detalle.impreso','imprimir')
        ->where('categorias.impresora',$impresora->Id)
        ->count();

        if($detallecount >0){
            
            if($impresora->tip_conex_imp=='COMPARTIDO'){
                $connector = new WindowsPrintConnector("smb://".$impresora->ruta);
            }elseif($impresora->tip_conex_imp=='RED'){
                $connector = new NetworkPrintConnector($impresora->ruta,9100);
            }
            
            $printer = new Printer($connector);
            

            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->setFont(Printer::FONT_A);

            $printer->text($cab_pedido->etiqueta."\n");
            $printer->text("Pedido ".$cab_pedido->ped_id.": ". $mesa->mes_nom."\n");
                 //   $printer->text("OUT & PRIDE"."\n");
            if(empty($cab_pedido->etiqueta)){
                $printer->text("Fecha:". $cab_pedido->fecha_hora."\n");
            }else{
                $printer->text("Fecha:". $cab_pedido->fecha_hora_modificacion."\n"."\n");
            }
            
            $printer->text("Mozo:". $cab_pedido->name.' '.$cab_pedido->apeusu."\n"."\n");
            
            $printer->setJustification(Printer::JUSTIFY_LEFT);
            
            
            $printer->setJustification(Printer::JUSTIFY_LEFT);
            $printer->text("CONCEPTO    CANTIDAD       OBS."."\n");
            $printer->text("_________________________________"."\n");
            foreach ($detalle as $det) {
               
             $primeralinea = str_pad(substr($det->pronom,0,17),17," ",STR_PAD_RIGHT);
             $segundalinea = str_pad(substr($det->pronom,18,34),17," ",STR_PAD_RIGHT);
             $printer->text($primeralinea."   ".$det->cantidad."  ".$det->detalle."\n");
             $printer->text($segundalinea."\n");


             $buscardetalle = pedidos_detalle::findOrFail($det->ped_det_id);
             $buscardetalle->impreso ='impreso';
             $buscardetalle->update();
         }
         $printer->text("\n");
         $printer->feed();
         $printer->cut();
         $printer->pulse();
         $printer->close();

     }

     $buscarpedido = DB::tABLE('pedidos')
     ->where('ped_id',$pedido)
     ->update(['etiqueta' => ""]); 

 }

 


}

public function imprimirpedidollevar($pedido){

 $IdEmpresa = Auth::user()->IdEmpresa;
 $cab_pedido = DB::tABLE('pedidos')
 ->where('ped_id',$pedido)
 ->first();

 
 $impresoras = DB::tABLE('configuracion_impresoras')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->get();

 foreach ($impresoras as $impresora) {
    

    $detalle = DB::tABLE('pedidos_detalle')->where('ped_id',$pedido)
    ->join('productos','pedidos_detalle.IdProducto','productos.IdProducto')
    ->leftjoin('categorias','categorias.cat_id','productos.cat_id')
    ->where('categorias.id_empresa_negocio',Auth::user()->id_empresa_negocio)
    ->where('pedidos_detalle.impreso','imprimir')
    ->where('categorias.impresora',$impresora->Id)
    ->get();

    $detallecount = DB::tABLE('pedidos_detalle')->where('ped_id',$pedido)
    ->join('productos','pedidos_detalle.IdProducto','productos.IdProducto')
    ->leftjoin('categorias','categorias.cat_id','productos.cat_id')
    ->where('categorias.id_empresa_negocio',Auth::user()->id_empresa_negocio)
    ->where('pedidos_detalle.impreso','imprimir')
    ->where('categorias.impresora',$impresora->Id)
    ->count();

    if($detallecount >0){
        
        if($impresora->tip_conex_imp=='COMPARTIDO'){
            $connector = new WindowsPrintConnector("smb://".$impresora->ruta);
        }elseif($impresora->tip_conex_imp=='RED'){
            $connector = new NetworkPrintConnector($impresora->ruta,9100);
        }
        
        $printer = new Printer($connector);
        

        $printer->setJustification(Printer::JUSTIFY_CENTER);
        $printer->setFont(Printer::FONT_A);
        $printer->text($cab_pedido->etiqueta."\n");
        $printer->text("Pedido Para Llevar ". $cab_pedido->ped_id."\n");
        if(empty($cab_pedido->etiqueta)){
            $printer->text("Fecha:". $cab_pedido->fecha_hora."\n");
        }else{
            $printer->text("Fecha:". $cab_pedido->fecha_hora_modificacion."\n");
        }
        
               //   $printer->text("OUT & PRIDE"."\n");
        $printer->text("Cliente:". $cab_pedido->cliente."\n");
        $printer->text("Dirección: ".$cab_pedido->direccion."\n");
        
        $printer->setJustification(Printer::JUSTIFY_LEFT);
        
        
        $printer->setJustification(Printer::JUSTIFY_LEFT);
        $printer->text("CONCEPTO                CANTIDAD       OBS."."\n");
        $printer->text("________________________________________________"."\n");
        
        foreach ($detalle as $det) {
           $primeralinea = str_pad(substr($det->pronom,0,17),17," ",STR_PAD_RIGHT);
           $segundalinea = str_pad(substr($det->pronom,18,34),17," ",STR_PAD_RIGHT);
           $printer->text($primeralinea."          ".$det->cantidad."        ".$det->detalle."\n");
           $printer->text($segundalinea."\n");

           $buscardetalle = pedidos_detalle::findOrFail($det->ped_det_id);
           $buscardetalle->impreso ='impreso';
           $buscardetalle->update();
       }

       $printer->text("\n");
       

       $printer->feed();
       
       $printer->cut();
       
       
       $printer->pulse();
       
       $printer->close();

   }

   $buscarpedido = DB::tABLE('pedidos')
   ->where('ped_id',$pedido)
   ->update(['etiqueta' => ""]); 
}
}

public function registrar_comanda(Request $request){

    $bus_emp =  Empresa::findOrFail(Auth::user()->IdEmpresa);
    $icbper_tot = $request->get('icbper_tot');

  $mes_id = $request->get('mes_id');
  $mozo = $request->get('mozo');
  $pis_id = $request->get('pis_id');
  $productos = $request->get('txt_id_producto');
  $precios = $request->get('precios');
  $descripcion = $request->get('descripcion');
  $cantidad = $request->get('txt_cantidad');
  $ped_fec = $request->get('ped_fec');
  $mozo = $request->get('mozo');
  $tipo = $request->get('tipo');
  $ped_tot = $request->get('total_venta');
  $est_ped_id = "";
  $ped_num_doc = $request->get('ped_num_doc');
  $ped_cli_nom = $request->get('ped_cli_nom');
  $item_obs = $request->get('item_obs');
  $pagar = $request->get('pagar');
  $vuelto = $request->get('vuelto');
  $ped_dir = $request->get('ped_dir');
  $tdicod = $request->get('tdicod');
  $ped_obs = $request->get('ped_obs');
  $motorizado = $request->get('motorizado');
  $ped_pag_efe = $request->get('ped_pag_efe');
  $ped_pag_tar = $request->get('ped_pag_tar');
  $ped_tel = $request->get('ped_tel');
  $ped_ref = $request->get('ped_ref');
  $ped_fac = $request->get('ped_fac');
  $icbper_ind = $request->get('txt_icbper');
  
    if(empty($productos)){
        if($request->ajax()){
            return response()->json(['estado'=>'error','mensaje'=>'ELEGIR PRODUCTOS']);
        }
    }

  if($tipo=='1'){
    if(empty($mes_id)){       
      if($request->ajax()){
        return response()->json(['estado'=>'error','mensaje'=>'ELEGIR UNA MESA']);
    }
}

$mesa = mesas::findOrFail($mes_id);
$mesa->mes_est ='Ocupado';
$mesa->update();


if(!empty($mes_id)){
  $tipo_com = 'Salon';
}

}elseif($tipo=='2'){

    $tipo_com = 'Delivery';

    $est_ped_id = '10';

}elseif($tipo=='3'){

    $tipo_com = 'Llevar';

}





$pedidos = new pedidos;
$pedidos->mes_id = $mes_id;
$pedidos->ped_num_doc = $ped_num_doc;
$pedidos->ped_cli_nom = $ped_cli_nom;
$pedidos->ped_dir = $ped_dir;
$pedidos->mozo = $mozo;
$pedidos->motorizado = $motorizado;
$pedidos->ped_pag_efe = $ped_pag_efe;
$pedidos->motorizado = $motorizado;
$pedidos->ped_pag_tar = $ped_pag_tar;
$pedidos->ped_ref = $ped_ref;
$pedidos->ped_tel = $ped_tel;
$pedidos->icbper_val = $bus_emp->icbper;
$pedidos->icbper_tot = $icbper_tot;
$pedidos->ped_fac = $ped_fac;
$pedidos->pagar = $pagar;
$pedidos->vuelto = $vuelto;
$pedidos->ped_tot = $ped_tot;
$pedidos->tdicod = $tdicod;
$pedidos->ped_obs = $ped_obs;
$pedidos->pis_id = $pis_id;
$pedidos->ped_fec = $ped_fec;
$pedidos->est_ped_id= $est_ped_id;
$pedidos->ped_tip = $tipo_com;
$pedidos->IdEmpresa = Auth::user()->IdEmpresa;
$pedidos->id_empresa_negocio = Auth::user()->id_empresa_negocio;

$pedidos->save();

foreach($productos as $index => $pro){

    $detalle = new pedidos_detalle;
    $detalle->ped_det_can = $cantidad[$index];
    $detalle->IdProducto = $pro;
    $detalle->item_obs = $item_obs[$index];
    $detalle->ped_det_pre = $precios[$index];

    //$detalle->tiempo_maximo = $tiempo_maximo * 60;

    $detalle->icbper_ind = $icbper_ind[$index];
    $detalle->descripcion = $request->get('descripcion')[$index];
    $detalle->ped_id = $pedidos->ped_id;
    $detalle->impreso = 'Imprimir';
    $detalle->save();

}


        self::registrar_movimiento_salida_comanda($pedidos->ped_id);

        $data =  self::imprimir_comanda($pedidos->ped_id);

           if($tipo=='2' || $tipo=='3'){

              self::imprimircuenta($pedidos->ped_id);
           }

if($request->ajax()){
    return response()->json(['mensaje'=>'Registrado','ped_id'=>$pedidos->ped_id]);
}

}


 public function imprimircuenta($pedido){

        $cab_pedido = DB::TABLE('pedidos')
           ->where('ped_id',$pedido)
           ->leftjoin('users','users.IdUsuario','pedidos.mozo')
           ->first();
       
       if($cab_pedido->ped_est =='Eliminado' || $cab_pedido->ped_est =='Cerrado'){
       
         if($request->ajax()) {
          return response()->json(['mensaje' => 'Cerrado']);
        }
    
       }


            $IdEmpresa = Auth::user()->IdEmpresa;
                    
                
           $IdEmpresa = Auth::user()->IdEmpresa;
          

           $mesa= DB::TABLE('pedidos')->where('ped_id',$cab_pedido->ped_id)->first();

           $detalle = DB::TABLE('pedidos_detalle')->where('ped_id',$pedido)->where('estadoitem','Ingresado')->join('productos','pedidos_detalle.IdProducto','productos.IdProducto')->get();
      
           DB::TABLE('mesas')->where('mes_id',$cab_pedido->mes_id)->update(['mes_est'=>'Cuenta']);
        try{

          $impresoras = DB::TABLE('configuracion_impresoras')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->where('descripcion','CAJA')->first();

      
      $connector = new WindowsPrintConnector("smb://".$impresoras->ruta);

         

      $printer = new Printer($connector);    

        $printer->setJustification(Printer::JUSTIFY_CENTER);
        $printer->setFont(Printer::FONT_A);
        $printer->setJustification(Printer::JUSTIFY_CENTER);
        $printer->setFont(Printer::FONT_A);          
        $printer->setTextSize(4,4);
        if(isset($mesa)){
           $printer->text($cab_pedido->ped_id."\n");
        }

        $printer->setFont(Printer::FONT_A);          
        $printer->setTextSize(2,2);
        if(isset($mesa)){
           $printer->text($cab_pedido->ped_tip."\n");
        }

        $printer->text("Atencion:". $cab_pedido->name."\n");

        $printer->setFont(Printer::FONT_A);          
        $printer->setTextSize(1,1);
       
     //   $printer->text("OUT & PRIDE"."\n");
        $printer->text("Cliente: ". $cab_pedido->ped_cli_nom."\n");
        $printer->text("Direccion: ". $cab_pedido->ped_dir."\n");
        $printer->text("Telefono: ". $cab_pedido->ped_tel."\n");
         if(!empty($cab_pedido->ped_ref)){
                        $printer->text("Referencia: ".$cab_pedido->ped_ref."\n");
                    }
        $printer->text("Fecha:". $cab_pedido->fecha_hora."\n");
       
      
        
        $printer->setJustification(Printer::JUSTIFY_LEFT);
        $printer->text("Descripcion     Cant.     PU       Total"."\n");
        $printer->text("__________________________________________"."\n");
        foreach ($detalle as $det) {
         
           //$primeralinea = str_pad(substr($det->pronom,0,17),17," ",STR_PAD_RIGHT);
           //$segundalinea = str_pad(substr($det->pronom,18,34),17," ",STR_PAD_RIGHT);
          $printer->text($det->pronom. " + ".$det->detalle."\n");
          $printer->text("                  ".$det->ped_det_can."    ".$det->ped_det_pre."     ".number_format($det->ped_det_pre*$det->ped_det_can,'2','.','')."\n");
           //$printer->text($primeralinea."  ".$det->cantidad."  ".$det->propunitem."  ".$det->totalitem."\n");
           //$printer->text($segundalinea."\n");
        }
        $printer->text("__________________________________________"."\n");
       //$printer->text("\n");
        $printer->setFont(Printer::FONT_A);          
        $printer->setTextSize(2,2);
       $printer->text("TOTAL:". $cab_pedido->ped_tot."\n");
       $printer->text("____________________"."\n");       
        $printer->text("PAGA:". $cab_pedido->pagar."\n");
        $printer->text("VUELTO:". $cab_pedido->vuelto."\n");
        //$printer->text("\n");
       //$printer->text("RUC: _________________________________________"."\n");
       //$printer->text("RAZON SOCIAL: ________________________________"."\n");
      //$printer->text("DIRECCION: ____________________________________"."\n");
        $printer->feed();
        
        $printer->cut();
         
       // $printer->pulse();
       
        $printer->close();


        }catch(\exception $e){

          //dd($e);
           return response()->json(['validar' =>false,'pedido'=>$pedido]);

     

        }
            
          

        }



   public function imprimircuenta1($pedido){

            $IdEmpresa = Auth::user()->IdEmpresa;
                    
                
           $IdEmpresa = Auth::user()->IdEmpresa;
           $cab_pedido = DB::tABLE('pedidos')
             ->leftjoin('users','users.IdUsuario','pedidos.mozo')
           ->where('ped_id',$pedido)
           ->first();

           $mesa= DB::tABLE('mesas')->where('mes_id',$cab_pedido->mes_id)->first();


           $mesa= DB::tABLE('mesas')->where('mes_id',$cab_pedido->mes_id)->first();

            $piso = DB::tABLE('pisos')->where('pis_id',$cab_pedido->pis_id)->first();


           $detalle = DB::tABLE('pedidos_detalle')
           ->where('ped_id',$pedido)
           ->join('productos','pedidos_detalle.IdProducto','productos.IdProducto')
           ->where('estadoitem','Ingresado')
           ->get();

        try{

          $impresoras = DB::tABLE('configuracion_impresoras')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->where('predeterminado','1')->first();

      
      $connector = new WindowsPrintConnector("smb://".$impresoras->ruta);

         

      $printer = new Printer($connector);
    

        $printer->setJustification(Printer::JUSTIFY_CENTER);
        $printer->setFont(Printer::FONT_A);
        if(isset($mesa)){
           $printer->text("Pedido ".$cab_pedido->ped_id.": ".$piso->pis_nom."  ".$mesa->mes_nom."\n");
        }
       
            if(!empty($cab_pedido->mozo)){
                        $printer->text("Mozo:". $cab_pedido->name.' '.$cab_pedido->apeusu."\n"."\n");
                    }
                   

     //   $printer->text("OUT & PRIDE"."\n");
        $printer->text("Fecha:". $cab_pedido->fecha_hora."\n");
       
      
        
        $printer->setJustification(Printer::JUSTIFY_LEFT);
        $printer->text("CONCEPTO  CANTIDAD   PU  IMPORTE"."\n");
        $printer->text("_________________________________"."\n");
        foreach ($detalle as $det) {
         
           $primeralinea = str_pad(substr($det->descripcion,0,17),17," ",STR_PAD_RIGHT);
           $segundalinea = str_pad(substr($det->descripcion,18,34),17," ",STR_PAD_RIGHT);
           $printer->text($primeralinea."  ".$det->ped_det_can."  ".$det->ped_det_pre."  ".number_format($det->ped_det_pre*$det->ped_det_can,'2','.','')."\n");
           $printer->text($segundalinea."\n");
        }
       $printer->text("\n");
       $printer->text("CONSUMO TOTAL:". $cab_pedido->ped_tot."\n");
        $printer->text("\n");
       $printer->text("RUC: ______________________________________________"."\n");
       $printer->text("RAZON SOCIAL: _____________________________________"."\n");
      $printer->text("DIRECCION: _______________________________________"."\n");
        $printer->feed();
        
        $printer->cut();
         
        $printer->pulse();
       
        $printer->close();


        }catch(\exception $e){

            dd($e);
           return response()->json(['validar' =>false,'pedido'=>$pedido]);

     

        }
            
          

        }

public function actualizar_comanda(Request $request){

    $bus_emp = Empresa::findOrFail(Auth::user()->IdEmpresa);
    $icbper_tot = $request->get('icbper_tot');
    $icbper_ind = $request->get('txt_icbper');
    $mes_id = $request->get('mes_id');
    $pis_id = $request->get('pis_id');
    $ped_id = $request->get('ped_id');
    $mozo = $request->get('mozo');
    $productos_request = $request->get('txt_id_producto');
    $precios_request = $request->get('precios');
    $cantidad_request = $request->get('txt_cantidad');
    $item_obs_request = $request->get('item_obs');
    $descripcion_request = $request->get('descripcion');

    $ped_fec = $request->get('ped_fec');
    $ped_tot = $request->get('total_venta');
    $tipo = $request->get('tipo');
    $pagar = $request->get('pagar');
    $vuelto = $request->get('vuelto');

    $ped_num_doc = $request->get('ped_num_doc');
    $ped_cli_nom = $request->get('ped_cli_nom');
    $ped_dir = $request->get('ped_dir');
    $tdicod = $request->get('tdicod');
    $ped_obs = $request->get('ped_obs');
    $motorizado = $request->get('motorizado');

    $ped_pag_efe = $request->get('ped_pag_efe');
    $ped_pag_tar = $request->get('ped_pag_tar');
    $ped_tel = $request->get('ped_tel');
    $ped_ref = $request->get('ped_ref');
    $ped_fac = $request->get('ped_fac');

    if(empty($productos_request)){
        if($request->ajax()){
            return response()->json(['estado'=>'error','mensaje'=>'ELEGIR PRODUCTOS']);
        }
    }

    if($tipo=='1'){
        $des_tip = 'Salon';
    }elseif($tipo=='2'){
        $des_tip = 'Delivery';
    }elseif($tipo=='3'){
        $des_tip = 'Llevar';
    }

    if(!empty($mes_id)){
        $mesa = mesas::findOrFail($mes_id);
        $mesa->mes_est = 'Ocupado';
        $mesa->update();
    }

    $pedidos = pedidos::findOrFail($ped_id);
    $pedidos->mes_id = $mes_id;
    $pedidos->pis_id = $pis_id;
    $pedidos->ped_fec = $ped_fec;
    $pedidos->ped_tot = $ped_tot;
    $pedidos->mozo = $mozo;
    $pedidos->motorizado = $motorizado;
    $pedidos->pagar = $pagar;
    $pedidos->vuelto = $vuelto;
    $pedidos->ped_tip = $des_tip;
    $pedidos->icbper_val = $bus_emp->icbper;
    $pedidos->icbper_tot = $icbper_tot;
    $pedidos->motorizado = $motorizado;
    $pedidos->ped_num_doc = $ped_num_doc;
    $pedidos->ped_cli_nom = $ped_cli_nom;
    $pedidos->ped_dir = $ped_dir;
    $pedidos->ped_pag_efe = $ped_pag_efe;
    $pedidos->ped_pag_tar = $ped_pag_tar;
    $pedidos->ped_ref = $ped_ref;
    $pedidos->ped_tel = $ped_tel;
    $pedidos->ped_fac = $ped_fac;
    $pedidos->tdicod = $tdicod;
    $pedidos->ped_obs = $ped_obs;
    $pedidos->IdEmpresa = Auth::user()->IdEmpresa;
    $pedidos->id_empresa_negocio = Auth::user()->id_empresa_negocio;
    $pedidos->update();

    // ✅ MODIFICADO: Obtener productos con cantidad pendiente
    $existing_products = DB::table('pedidos_detalle')
                            ->select('pedidos_detalle.*',
                                DB::raw('(ped_det_can - IFNULL(item_facturado, 0)) as cantidad_pendiente'))
                            ->where('ped_id', $ped_id)
                            ->where('estadoitem', 'Ingresado')
                            ->whereRaw('(ped_det_can - IFNULL(item_facturado, 0)) > 0')
                            ->get()
                            ->keyBy('IdProducto');

    $productos_a_mantener = [];

    foreach($productos_request as $index => $pro){
        $producto_existe = $existing_products->get($pro);
        
        if($producto_existe){
            // ✅ El producto existe y tiene cantidad pendiente
            $detalle = pedidos_detalle::findOrFail($producto_existe->ped_det_id);
            
            // ✅ Comparar con cantidad_pendiente, NO con ped_det_can total
            $cantidad_pendiente_db = $producto_existe->cantidad_pendiente;
            $cantidad_total_db = $producto_existe->ped_det_can;
            $cantidad_del_request = $cantidad_request[$index];
            
            // ✅ Calcular la diferencia REAL (lo que cambió vs lo pendiente)
            $diferencia_cantidad = $cantidad_del_request - $cantidad_pendiente_db;
            
            if($diferencia_cantidad > 0){
                // Aumentó la cantidad: comandar solo el aumento
                $detalle->ped_det_can = $cantidad_total_db + $diferencia_cantidad; // Sumar al total
                $detalle->impreso = 'imprimir';
                $detalle->mod_cant = $diferencia_cantidad;
                
                Log::info("Item {$pro}: Aumentó {$diferencia_cantidad}. Total en BD: {$detalle->ped_det_can}");
                
                // Registrar movimiento solo del aumento
                $this->registrar_movimiento_salida_comanda_individual(
                    $pro,
                    $diferencia_cantidad,
                    $pedidos->id_empresa_negocio,
                    $pedidos->ped_cli_nom,
                    $ped_id,
                    $pedidos->ped_fec
                );
                
            }elseif($diferencia_cantidad < 0){
                // Disminuyó la cantidad: anular parcialmente
                $detalle->ped_det_can = $cantidad_total_db + $diferencia_cantidad; // Restar del total
                $detalle->impreso = 'impreso';
                $detalle->mod_cant = $diferencia_cantidad;
                
                Log::info("Item {$pro}: Disminuyó " . abs($diferencia_cantidad) . ". Total en BD: {$detalle->ped_det_can}");
                
                // Revertir stock
                $this->revertir_stock_por_item(
                    $pro,
                    abs($diferencia_cantidad),
                    $pedidos->id_empresa_negocio
                );
                
            }else{
                // No cambió cantidad: solo actualizar observaciones
                $detalle->impreso = 'impreso';
                $detalle->mod_cant = 0;
                
                Log::info("Item {$pro}: Sin cambios de cantidad.");
            }
            
            $detalle->ped_det_pre = $precios_request[$index];
            $detalle->item_obs = $item_obs_request[$index] ?? null;
            $detalle->icbper_ind = $icbper_ind[$index] ?? null;
            $detalle->update();
            
            $productos_a_mantener[] = $pro;

        }else{
            // ✅ Producto nuevo: insertar
            $detalle = new pedidos_detalle;
            $detalle->ped_det_can = $cantidad_request[$index];
            $detalle->IdProducto = $pro;
            $detalle->ped_det_pre = $precios_request[$index];
            $detalle->descripcion = $descripcion_request[$index] ?? null;
            $detalle->ped_id = $pedidos->ped_id;
            $detalle->item_obs = $item_obs_request[$index] ?? null;
            $detalle->icbper_ind = $icbper_ind[$index] ?? null;
            $detalle->impreso = 'Imprimir';
            $detalle->item_facturado = 0;
            $detalle->mod_cant = $cantidad_request[$index];
            $detalle->save();

            $productos_a_mantener[] = $pro;
            
            Log::info("Item {$pro}: Nuevo item con cantidad {$cantidad_request[$index]}");
            
            // Registrar movimiento del nuevo item
            $this->registrar_movimiento_salida_comanda_individual(
                $pro,
                $cantidad_request[$index],
                $pedidos->id_empresa_negocio,
                $pedidos->ped_cli_nom,
                $ped_id,
                $pedidos->ped_fec
            );
        }
    }

    // ✅ Eliminar productos que ya no están en el request
    $productos_en_db = $existing_products->keys()->toArray();
    $productos_a_eliminar_de_db = array_diff($productos_en_db, $productos_a_mantener);

    foreach ($productos_a_eliminar_de_db as $pro_id_to_delete) {
        $producto_to_delete = $existing_products->get($pro_id_to_delete);
        
        if ($producto_to_delete) {
            $detalle_to_delete = pedidos_detalle::findOrFail($producto_to_delete->ped_det_id);
            
            // Revertir solo la cantidad pendiente
            $this->revertir_stock_por_item(
                $detalle_to_delete->IdProducto,
                $producto_to_delete->cantidad_pendiente, // ✅ Solo lo pendiente
                $pedidos->id_empresa_negocio
            );
            
            $detalle_to_delete->estadoitem = 'Eliminado';
            $detalle_to_delete->update();
            
            Log::info("Item {$pro_id_to_delete}: Eliminado. Revertidos {$producto_to_delete->cantidad_pendiente} unidades.");
        }
    }

    self::imprimir_comanda($pedidos->ped_id);

    if($request->ajax()){
        return response()->json(['mensaje'=>'Registrado','ped_id'=>$pedidos->ped_id]);
    }
}


       

    private function registrar_movimiento_salida_comanda_individual($producto_id, $cantidad, $id_empresa_negocio, $cliente_nom, $ped_id, $ped_fec) {
        $bus_pro = productos::findOrFail($producto_id);
        $bus_alm = DB::table('almacenes')->where('id_empresa_negocio', $id_empresa_negocio)->where('predeterminado','1')->first();

        if (!$bus_alm) {
            // Considerar registrar este error o lanzar una excepción si no hay almacén predeterminado.
            return; 
        }

        $id_prod = empty($bus_pro->pro_rel) ? $bus_pro->IdProducto : $bus_pro->pro_rel;

        if($bus_pro->promocion == '0'){ // Producto simple
            DB::table('movimientos_productos')->insert([
                'IdProducto' => $producto_id,
                'IdProducto_rel' => $id_prod,
                'precio' => $bus_pro->propun,
                'cantidad' => $cantidad * $bus_pro->factor,
                'costo' => $bus_pro->costo,
                'cliente' => $cliente_nom,
                'IdCpe_cabecera' => null,
                'serie' => null,
                'numero' => null,
                'tdocod' => null,
                'tipo' => '3', // Salida por venta/comanda
                'mov_tip' => 'E', // Egreso
                'id_empresa_negocio' => $id_empresa_negocio,
                'id_almacen' => $bus_alm->id_almacen,
                'fecha_mov' => $ped_fec,
                'descripcion' => 'Salida por Comanda (Pedido ID: ' . $ped_id . ')',
                'mov_lote' => $bus_pro->lote,
                'mov_vencimiento' => $bus_pro->vencimiento,
            ]);

            $stock_prod = DB::table('producto_stock')
                ->where('IdProducto', $id_prod)
                ->where('id_almacen', $bus_alm->id_almacen)
                ->first();

            if ($stock_prod) {
                DB::table('producto_stock')->where('pro_sto_id', $stock_prod->pro_sto_id)->update(['stock' => $stock_prod->stock - ($cantidad * $bus_pro->factor)]);
            }
        } elseif($bus_pro->promocion == '2'){ // Producto con receta (insumos)
            $bus_receta = DB::table('recetas')->where('prod_id', $id_prod)->get();

            if(count($bus_receta) > 0){
                foreach($bus_receta as $rec){
                    DB::table('movimientos_productos')->insert([
                        'IdProducto' => $rec->prod_insu,
                        'IdProducto_rel' => $rec->prod_insu,
                        'precio' => '0',
                        'cantidad' => $cantidad * $rec->rec_cant,
                        'costo' => $rec->ins_costo,
                        'cliente' => $cliente_nom,
                        'IdCpe_cabecera' => null,
                        'serie' => null,
                        'numero' => null,
                        'tdocod' => null,
                        'tipo' => '3',
                        'mov_tip' => 'E',
                        'id_empresa_negocio' => $id_empresa_negocio,
                        'id_almacen' => $bus_alm->id_almacen,
                        'fecha_mov' => $ped_fec,
                        'descripcion' => 'Salida por Receta (Pedido ID: ' . $ped_id . ')',
                    ]);

                    $stock_prod_ins = DB::table('producto_stock')
                        ->where('IdProducto', $rec->prod_insu)
                        ->where('id_almacen', $bus_alm->id_almacen)
                        ->first();

                    if ($stock_prod_ins) {
                        DB::table('producto_stock')->where('pro_sto_id', $stock_prod_ins->pro_sto_id)->update(['stock' => $stock_prod_ins->stock - ($cantidad * $rec->rec_cant)]);
                    }
                }
            }
        } elseif($bus_pro->promocion == '3'){ // Combo
            $bus_combo_items = DB::table('combos')->where('IdProducto_rel', $id_prod)->get();

            foreach($bus_combo_items as $combo_item){
                $prod_combo = productos::findOrFail($combo_item->IdProducto_comb);
                $id_prod_combo = empty($prod_combo->pro_rel) ? $prod_combo->IdProducto : $prod_combo->pro_rel;

                if($prod_combo->promocion == '0'){ // Item del combo es un producto simple
                    DB::table('movimientos_productos')->insert([
                        'IdProducto' => $prod_combo->IdProducto,
                        'IdProducto_rel' => $id_prod_combo,
                        'precio' => $prod_combo->propun,
                        'cantidad' => $cantidad * $combo_item->prod_comb_cant,
                        'costo' => $prod_combo->costo,
                        'cliente' => $cliente_nom,
                        'IdCpe_cabecera' => null,
                        'serie' => null,
                        'numero' => null,
                        'tdocod' => null,
                        'tipo' => '3',
                        'mov_tip' => 'E',
                        'id_empresa_negocio' => $id_empresa_negocio,
                        'id_almacen' => $bus_alm->id_almacen,
                        'fecha_mov' => $ped_fec,
                        'descripcion' => 'Salida por Combo (Pedido ID: ' . $ped_id . ') - Item: ' . $prod_combo->pronom,
                        'mov_lote' => $prod_combo->lote,
                        'mov_vencimiento' => $prod_combo->vencimiento,
                    ]);

                    $stock_prod_combo = DB::table('producto_stock')
                        ->where('IdProducto', $id_prod_combo)
                        ->where('id_almacen', $bus_alm->id_almacen)
                        ->first();

                    if ($stock_prod_combo) {
                        DB::table('producto_stock')->where('pro_sto_id', $stock_prod_combo->pro_sto_id)->update(['stock' => $stock_prod_combo->stock - ($cantidad * $combo_item->prod_comb_cant)]);
                    }
                } elseif($prod_combo->promocion == '2'){ // Item del combo tiene receta
                    $bus_receta_combo_item = DB::table('recetas')->where('prod_id', $id_prod_combo)->get();

                    if(count($bus_receta_combo_item) > 0){
                        foreach($bus_receta_combo_item as $rec_combo_item){
                            DB::table('movimientos_productos')->insert([
                                'IdProducto' => $rec_combo_item->prod_insu,
                                'IdProducto_rel' => $rec_combo_item->prod_insu,
                                'precio' => '0',
                                'cantidad' => $cantidad * $combo_item->prod_comb_cant * $rec_combo_item->rec_cant,
                                'costo' => $rec_combo_item->ins_costo,
                                'cliente' => $cliente_nom,
                                'IdCpe_cabecera' => null,
                                'serie' => null,
                                'numero' => null,
                                'tdocod' => null,
                                'tipo' => '3',
                                'mov_tip' => 'E',
                                'id_empresa_negocio' => $id_empresa_negocio,
                                'id_almacen' => $bus_alm->id_almacen,
                                'fecha_mov' => $ped_fec,
                                'descripcion' => 'Salida por Receta de Combo (Pedido ID: ' . $ped_id . ') - Item: ' . $prod_combo->pronom,
                            ]);

                            $stock_prod_rec_combo = DB::table('producto_stock')
                                ->where('IdProducto', $rec_combo_item->prod_insu)
                                ->where('id_almacen', $bus_alm->id_almacen)
                                ->first();

                            if ($stock_prod_rec_combo) {
                                DB::table('producto_stock')->where('pro_sto_id', $stock_prod_rec_combo->pro_sto_id)->update(['stock' => $stock_prod_rec_combo->stock - ($cantidad * $combo_item->prod_comb_cant * $rec_combo_item->rec_cant)]);
                            }
                        }
                    }
                }
            }
        }
    }




       

    public function eliminar_item(Request $request, $item, $pedido)
    {
        // Obtenemos la cantidad actual del item antes de eliminarlo lógicamente
        $detalle_item = pedidos_detalle::where('ped_id', $pedido)
            ->where('IdProducto', $item)
            ->where('estadoitem', 'Ingresado')
            ->first();

        if ($detalle_item) {
            // Revertimos el stock del item antes de marcarlo como "Eliminado"
            $this->revertir_stock_por_item($detalle_item->IdProducto, $detalle_item->ped_det_can, Auth::user()->id_empresa_negocio);
        }

        pedidos_detalle::where('ped_id', $pedido)
            ->where('IdProducto', $item)
            ->update(['estadoitem' => 'Eliminado']);

        $detalle_restantes = pedidos_detalle::where('ped_id', $pedido)
            ->where('estadoitem', 'Ingresado')
            ->get();

        // Si no quedan ítems activos en el pedido, eliminamos el pedido completo y liberamos la mesa
        if (count($detalle_restantes) === 0) {
            $pedido_obj = pedidos::findOrFail($pedido);
            $mesa_id = $pedido_obj->mes_id; // Guardamos el ID de la mesa antes de eliminar el pedido
            
            $pedido_obj->ped_est = 'Eliminado';
            $pedido_obj->update();

            if (!empty($mesa_id)) {
                $mesa = mesas::findOrFail($mesa_id);
                $mesa->mes_est = 'Libre';
                $mesa->update();

                // Manejar mesas unidas si aplica (si tu lógica de mesas_union está en liberar_mesa, podrías llamarla aquí)
                // self::liberar_mesa($pedido); // Si liberar_mesa maneja lo de las uniones
            }
            
            self::imprimir_item_eliminado($pedido, $item); // Imprime el item eliminado, aunque el pedido se elimina

            return response()->json([
                'mensaje' => 'Último ítem eliminado. Pedido completo eliminado y mesa liberada.',
                'status' => 'success',
                'action' => 'reload_page' // Indica al JS que recargue la página completa
            ]);
        } else {
            // Si quedan ítems, solo actualizamos el total del pedido y recargamos la vista del pedido
            $total = 0;
            foreach ($detalle_restantes as $det) {
                $total = $total + ($det->ped_det_can * $det->ped_det_pre);
            }
            pedidos::where('ped_id', $pedido)->update(['ped_tot' => $total]);
            
            self::imprimir_item_eliminado($pedido, $item); // Imprime el ítem eliminado

            return response()->json([
                'mensaje' => 'Ítem Eliminado',
                'status' => 'success',
                'action' => 'reload_partial', // Indica al JS que recargue solo la parte del pedido
                'ped_id' => $pedido
            ]);
        }
    }

    public function revertir_stock_por_item($producto_id, $cantidad_revertir, $id_empresa_negocio)
    {
        $bus_pro = productos::findOrFail($producto_id);
        $bus_alm = DB::table('almacenes')->where('id_empresa_negocio', $id_empresa_negocio)->where('predeterminado', '1')->first();

        // Asegúrate de que $bus_alm no sea nulo antes de continuar
        if (!$bus_alm) {
            // Manejar el caso en que no se encuentre un almacén predeterminado
            // Podrías lanzar una excepción, registrar un error, o simplemente retornar.
            return;
        }

        $id_prod = empty($bus_pro->pro_rel) ? $bus_pro->IdProducto : $bus_pro->pro_rel;

        if ($bus_pro->promocion == '0') { // Producto simple
            $stock_prod = DB::table('producto_stock')
                ->where('IdProducto', $id_prod)
                ->where('id_almacen', $bus_alm->id_almacen)
                ->first();

            if ($stock_prod) {
                DB::table('producto_stock')->where('pro_sto_id', $stock_prod->pro_sto_id)->update(['stock' => $stock_prod->stock + ($cantidad_revertir * $bus_pro->factor)]);
            }
        } elseif ($bus_pro->promocion == '2') { // Producto con receta (insumos)
            $bus_receta = DB::table('recetas')->where('prod_id', $id_prod)->get();

            if (count($bus_receta) > 0) {
                foreach ($bus_receta as $rec) {
                    // Aquí, en lugar de insertar un movimiento de salida, se debería insertar un movimiento de ENTRADA por reversión
                    DB::table('movimientos_productos')->insert([
                        'IdProducto' => $rec->prod_insu,
                        'IdProducto_rel' => $rec->prod_insu,
                        'precio' => '0', // No aplica precio de venta en una reversión de insumo
                        'cantidad' => $cantidad_revertir * $rec->rec_cant,
                        'costo' => $rec->ins_costo,
                        'cliente' => 'REVERSION_ITEM', // Un cliente o referencia para identificar la reversión
                        'IdCpe_cabecera' => null,
                        'serie' => null,
                        'numero' => null,
                        'tdocod' => null,
                        'tipo' => '4', // Por ejemplo, '4' para reversión o ajuste de entrada
                        'mov_tip' => 'I', // Indica un INGRESO al stock
                        'id_empresa_negocio' => $id_empresa_negocio,
                        'id_almacen' => $bus_alm->id_almacen,
                        'fecha_mov' => now()->toDateString(), // Fecha actual de la reversión
                        'descripcion' => 'Reversión por eliminación de ítem de Comanda (Receta de: ' . $bus_pro->pronom . ')',
                        'mov_lote' => null, // O el lote si se puede rastrear
                        'mov_vencimiento' => null, // O la fecha de vencimiento si se puede rastrear
                    ]);

                    $stock_prod_ins = DB::table('producto_stock')
                        ->where('IdProducto', $rec->prod_insu)
                        ->where('id_almacen', $bus_alm->id_almacen)
                        ->first();

                    if ($stock_prod_ins) {
                        DB::table('producto_stock')->where('pro_sto_id', $stock_prod_ins->pro_sto_id)->update(['stock' => $stock_prod_ins->stock + ($cantidad_revertir * $rec->rec_cant)]);
                    }
                }
            }
        } elseif ($bus_pro->promocion == '3') { // Combo
            $bus_combo_items = DB::table('combos')->where('IdProducto_rel', $id_prod)->get();

            foreach ($bus_combo_items as $combo_item) {
                $prod_combo = productos::findOrFail($combo_item->IdProducto_comb);
                $id_prod_combo = empty($prod_combo->pro_rel) ? $prod_combo->IdProducto : $prod_combo->pro_rel;

                if ($prod_combo->promocion == '0') { // Ítem del combo es un producto simple
                     DB::table('movimientos_productos')->insert([
                        'IdProducto' => $prod_combo->IdProducto,
                        'IdProducto_rel' => $id_prod_combo,
                        'precio' => $prod_combo->propun,
                        'cantidad' => $cantidad_revertir * $combo_item->prod_comb_cant,
                        'costo' => $prod_combo->costo,
                        'cliente' => 'REVERSION_ITEM', // Cliente genérico para reversión
                        'IdCpe_cabecera' => null,
                        'serie' => null,
                        'numero' => null,
                        'tdocod' => null,
                        'tipo' => '4', // Tipo '4' para reversión
                        'mov_tip' => 'I', // Ingreso de stock
                        'id_empresa_negocio' => $id_empresa_negocio,
                        'id_almacen' => $bus_alm->id_almacen,
                        'fecha_mov' => now()->toDateString(), // Fecha actual
                        'descripcion' => 'Reversión por eliminación de ítem de Comanda (Combo: ' . $bus_pro->pronom . ' - Item: ' . $prod_combo->pronom . ')',
                        'mov_lote' => $prod_combo->lote,
                        'mov_vencimiento' => $prod_combo->vencimiento,
                    ]);

                    $stock_prod_combo = DB::table('producto_stock')
                        ->where('IdProducto', $id_prod_combo)
                        ->where('id_almacen', $bus_alm->id_almacen)
                        ->first();

                    if ($stock_prod_combo) {
                        DB::table('producto_stock')->where('pro_sto_id', $stock_prod_combo->pro_sto_id)->update(['stock' => $stock_prod_combo->stock + ($cantidad_revertir * $combo_item->prod_comb_cant)]);
                    }
                } elseif ($prod_combo->promocion == '2') { // Ítem del combo tiene receta
                    $bus_receta_combo_item = DB::table('recetas')->where('prod_id', $id_prod_combo)->get();

                    if (count($bus_receta_combo_item) > 0) {
                        foreach ($bus_receta_combo_item as $rec_combo_item) {
                            DB::table('movimientos_productos')->insert([
                                'IdProducto' => $rec_combo_item->prod_insu,
                                'IdProducto_rel' => $rec_combo_item->prod_insu,
                                'precio' => '0',
                                'cantidad' => $cantidad_revertir * $combo_item->prod_comb_cant * $rec_combo_item->rec_cant,
                                'costo' => $rec_combo_item->ins_costo,
                                'cliente' => 'REVERSION_ITEM', // Cliente genérico para reversión
                                'IdCpe_cabecera' => null,
                                'serie' => null,
                                'numero' => null,
                                'tdocod' => null,
                                'tipo' => '4', // Tipo '4' para reversión
                                'mov_tip' => 'I', // Ingreso de stock
                                'id_empresa_negocio' => $id_empresa_negocio,
                                'id_almacen' => $bus_alm->id_almacen,
                                'fecha_mov' => now()->toDateString(), // Fecha actual
                                'descripcion' => 'Reversión por eliminación de ítem de Comanda (Combo: ' . $bus_pro->pronom . ' - Receta de: ' . $prod_combo->pronom . ')',
                            ]);

                            $stock_prod_rec_combo = DB::table('producto_stock')
                                ->where('IdProducto', $rec_combo_item->prod_insu)
                                ->where('id_almacen', $bus_alm->id_almacen)
                                ->first();

                            if ($stock_prod_rec_combo) {
                                DB::table('producto_stock')->where('pro_sto_id', $stock_prod_rec_combo->pro_sto_id)->update(['stock' => $stock_prod_rec_combo->stock + ($cantidad_revertir * $combo_item->prod_comb_cant * $rec_combo_item->rec_cant)]);
                            }
                        }
                    }
                }
            }
        }
    }

    

public function eliminar_pedido(Request $request,$pedido){

          if(empty($pedido)){ //
            if($request->ajax()){ //
               return response()->json(['estado'=>'error','mensaje'=>'ELEGIR PEDIDO A ELIMINAR']); //
            }

          }
          
          // Revertir el stock de todos los items del pedido antes de eliminarlo
          $detalle_pedido = pedidos_detalle::where('ped_id', $pedido)->where('estadoitem','Ingresado')->get(); //
          foreach ($detalle_pedido as $item) { //
              $this->revertir_stock_por_item($item->IdProducto, $item->ped_det_can, Auth::user()->id_empresa_negocio); //
          }

          $pedido_obj = pedidos::findOrFail($pedido); //
          $pedido_obj->ped_est = 'Eliminado'; //
          $pedido_obj->update(); //

  
          if(!empty($pedido_obj->mes_id)){ //
            $mesa = mesas::findOrFail($pedido_obj->mes_id); //
            $mesa->mes_est = 'Libre'; //
            $mesa->update(); //
          }
         
         self::imprimir_pedido_eliminado($pedido);

          if($request->ajax()){ //
            return response()->json(['mensaje'=>'Pedido Eliminado']); //
          }

        }

public function panel_salon(Request $request){
  
  $primer_piso = DB::tABLE('pisos')
  ->where('suc_id',Auth::user()->id_empresa_negocio)
  ->first();

  $mesas = DB::tABLE('mesas')
  ->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
  ->where('mesas.pis_id',$primer_piso->pis_id)
  ->orderby('mesas.mes_nom','asc')
  ->get(); 

  
  $pisos = DB::tABLE('pisos')
  ->where('suc_id',Auth::user()->id_empresa_negocio)
  ->get();

  $cat_pred =  DB::tABLE('categorias')->where('predeterminado','1')->first();

  $vista = view('empresas.restaurante.panel_salon',compact('primer_piso','mesas','pisos','cat_pred'))->render();

  if($request->ajax()){
    
    return response()->json(['vista'=>$vista]);

}

}


public function entregarpedido($id,Request $request){


  $pedidos = pedidos::findOrFail($id);
  $pedidos->est_ped_id = '5';
  $pedidos->fecha_hora_entrega = now()->format('Y-m-d H:i:s');
  $pedidos->update();

  //if(Auth::User()->hasRole('repartidor') ){
      return Redirect::to('/consola');

  //}else{
   //return Redirect::to('/listos');

//}

}


public function listar_pedidos_delivery(Request $request)
{

    $estado = $request->get('estado');

    if(empty($estado)){
        $estado = 10;
    }

    if(Auth::User()->hasRole('admin') ||   Auth::User()->hasRole('caja')){
        $pedidos = DB::tABLE('pedidos')
        ->leftjoin('cat_estado_pedido as cep','cep.est_ped_id','pedidos.est_ped_id')
        ->leftjoin('users','users.IdUsuario','pedidos.repartidor')
        ->leftjoin('mesas','mesas.mes_id','pedidos.mes_id')
        ->where('pedidos.IdEmpresa',Auth::user()->IdEmpresa)
        ->where('pedidos.est_ped_id',$estado)
        ->where('pedidos.ped_tip','DELIVERY')
        ->orderby('ped_id','desc')
        ->get();
    }elseif(Auth::User()->hasRole('repartidor')){
        $pedidos = DB::tABLE('pedidos')
        ->leftjoin('cat_estado_pedido as cep','cep.est_ped_id','pedidos.est_ped_id')
        ->leftjoin('users','users.IdUsuario','pedidos.repartidor')
        ->leftjoin('mesas','mesas.mes_id','pedidos.mes_id')
        ->where('pedidos.IdEmpresa',Auth::user()->IdEmpresa)
        ->where('pedidos.est_ped_id',$estado)
        ->where('pedidos.ped_tip','DELIVERY')
        ->where('repartidor',Auth::user()->IdUsuario)
        ->orderby('ped_id','desc')
        ->get();

    }


    $detalles = DB::tABLE('pedidos')->select('pronom','pedidos.ped_id','detalle')
    ->leftjoin('pedidos_detalle as tbpd','tbpd.ped_id','pedidos.ped_id')
    ->leftjoin('productos as tbp','tbp.IdProducto','tbpd.IdProducto')
   ->leftjoin('cat_estado_pedido as cep','cep.est_ped_id','pedidos.est_ped_id')
    ->where('pedidos.IdEmpresa',Auth::user()->IdEmpresa)
    //->where('pedidos.est_ped_id','2')
    ->where('pedidos.ped_tip','Delivery')
    ->orderby('ped_id','desc')
    ->get();


    $repartidores = DB::tABLE('users')->join('role_user','users.IdUsuario','role_user.user_IdUsuario')->where('role_id','8')->get();

    $estados = DB::tABLE('cat_estado_pedido')->where('delivery','1')->get();

    return view('empresas.restaurante.pedidosdelivery',compact('pedidos','detalles','repartidores','estados'));
    
}


public function panel_delivery(Request $request){
  
  $documentos = DB::tABLE('tipo_documento_identidad')->orderby('orden','asc')->get();

  $vista = view('empresas.restaurante.panel_delivery',compact('documentos'))->render();

  if($request->ajax()){
    
    return response()->json(['vista'=>$vista]);

}

}

public function panel_llevar(Request $request){

  $documentos = DB::tABLE('tipo_documento_identidad')->orderby('orden','asc')->get();

  $vista = view('empresas.restaurante.panel_llevar',compact('documentos'))->render();

  if($request->ajax()){
    
    return response()->json(['vista'=>$vista]);

}

}

public function cambiar_mesa(Request $request){

  try{

    $mes_id_act = $request->get('mes_id_act');
    $mes_id = $request->get('mesas');
    $ped_id = $request->get('ped_id_act');

    $bus_ant = mesas::findOrFail($mes_id_act);
    

    if($bus_ant->mes_est=='Ocupado' && $mes_id<>$mes_id_act){
     $bus_mes = mesas::findOrFail($mes_id);
     $bus_mes->mes_est = 'Ocupado';
     $bus_mes->update();


     

     $act_ped = pedidos::findOrFail($ped_id);
     $act_ped->mes_id = $mes_id;
     $act_ped->pis_id = $bus_mes->pis_id;
     $act_ped->update();

     $bus_ant->mes_est = 'Libre';
     $bus_ant->update();
 }
 



}catch(\Exception $e){

}

return Redirect::to('/consola');


}

public function buscar_pedidos(Request $request, $tipo){

  if($tipo=='2'){
    $tip_nom = 'Delivery';
}elseif($tipo=='3'){
    $tip_nom = 'LLevar';
}

$pedidos = pedidos::where('ped_tip',$tip_nom)->where('ped_est','Aperturado')->orderby('ped_id','asc')->get();

$vista = view('empresas.restaurante.listar_pedidos_delivery_llevar',compact('pedidos'))->render();

if($request->ajax()){
    
    return response()->json(['vista'=>$vista]);

}

}

public function buscar_pedidos_caja(Request $request, $tipo){

  if($tipo=='2'){
    $tip_nom = 'Delivery';
}elseif($tipo=='3'){
    $tip_nom = 'LLevar';
}

$pedidos = pedidos::where('ped_tip',$tip_nom)->where('ped_est','Aperturado')->orderby('ped_id','asc')->get();

$vista = view('empresas.restaurante.listar_pedidos_delivery_llevar_caja',compact('pedidos'))->render();

if($request->ajax()){
    
    return response()->json(['vista'=>$vista]);

}

}

public function cobrar_mesa($ped_id){
    $cabecera = DB::table('pedidos')->where('ped_id',$ped_id)->where('ped_est','Aperturado')->first();
    $empresa = Empresa::findorFail(Auth::user()->IdEmpresa);
    
    if(empty($cabecera)){
       return Redirect::to('/consolacaja');
    }else{
        $categorias = DB::table('categorias')->where('visible','1')->get();
        $comprobantes = DB::table('tipo_documento')->where('caja','1')->get();
        $documentos = DB::table('tipo_documento_identidad')->orderby('orden','asc')->get();
        $estadopagos = DB::table('credito_dias')->get();
        $mediospagos = DB::table('medios_pagos')->get();
        $mozos = DB::table('users')
            ->join('role_user','role_user.user_IdUsuario','users.IdUsuario')
            ->get();
        
        //  MODIFICADO: Solo mostrar items con cantidad pendiente de facturar
        $detalle = DB::table('pedidos_detalle')
            ->join('productos','productos.IdProducto','pedidos_detalle.IdProducto')
            ->select('pedidos_detalle.*', 'productos.*',
                DB::raw('(ped_det_can - IFNULL(item_facturado, 0)) as cantidad_pendiente'))
            ->where('ped_id',$cabecera->ped_id)
            ->where('estadoitem','Ingresado')
            ->whereRaw('(ped_det_can - IFNULL(item_facturado, 0)) > 0') // Solo pendientes
            ->get();
        
        $dat_pis = DB::table('pisos')->where('pis_id',$cabecera->pis_id)->first();
        $dat_mes = DB::table('mesas')->where('mes_id',$cabecera->mes_id)->first();
        $negocio = DB::table('empresa_negocios')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first();
    }
    
    return view('empresas.restaurante.cobrar_mesa',compact('negocio','cabecera','detalle','dat_pis','mozos','dat_mes','categorias','comprobantes','documentos','estadopagos','mediospagos','empresa'));
}



public function cuentas_separadas($ped_id){
    $cabecera = DB::tABLE('pedidos')->where('ped_id',$ped_id)->where('ped_est','Aperturado')->first();

    if(empty($cabecera)){
        return Redirect::to('/consolacaja');
    } else {
        // ✅ AQUÍ ESTÁ EL CAMBIO: Limpiamos la alerta al entrar a la vista
        DB::table('pedidos')->where('ped_id', $ped_id)->update(['ped_sol_cs' => 0]);

        // Todo lo demás sigue igual...
        $empresa = Empresa::findorFail(Auth::user()->IdEmpresa);
        $categorias = DB::tABLE('categorias')->where('visible','1')->get();
        $comprobantes = DB::tABLE('tipo_documento')->where('caja','1')->get();
        $documentos = DB::tABLE('tipo_documento_identidad')->orderby('orden','asc')->get();
        $estadopagos = DB::tABLE('credito_dias')->get();
        $mediospagos = DB::tABLE('medios_pagos')->get();
        $mozos = DB::tABLE('users')
            ->join('role_user','role_user.user_IdUsuario','users.IdUsuario')
            ->where('role_id','8')
            ->get();

        $detalle = DB::tABLE('pedidos_detalle')
            ->join('productos','productos.IdProducto','pedidos_detalle.IdProducto')
            ->where('ped_id',$cabecera->ped_id)
            ->where('estadoitem','Ingresado')
            ->get();

        $dat_pis = DB::tABLE('pisos')->where('pis_id',$cabecera->pis_id)->first();
        $dat_mes = DB::tABLE('mesas')->where('mes_id',$cabecera->mes_id)->first();
    }

    return view('empresas.restaurante.cuentas_separadas', compact('cabecera','detalle','dat_pis','mozos','dat_mes','categorias','comprobantes','documentos','estadopagos','mediospagos','empresa'));
}


public function imprimirPrecuentaSeparada($pedido_id, $items_cobrados, $total_parcial, $datos_cliente) {
    $cab_pedido = DB::table('pedidos')
        ->where('ped_id', $pedido_id)
        ->leftJoin('users', 'users.IdUsuario', 'pedidos.mozo')
        ->first();

    $impresoras = DB::table('configuracion_impresoras')
        ->where('id_empresa_negocio', Auth::user()->id_empresa_negocio)
        ->where('descripcion', 'CAJA')
        ->first();

    try {
        $connector = new WindowsPrintConnector("smb://" . $impresoras->ruta);
        $printer = new Printer($connector);

        // --- ENCABEZADO ---
        $printer->setJustification(Printer::JUSTIFY_CENTER);
        $printer->setTextSize(2, 2);
        $printer->text("PRE-CUENTA PARCIAL\n"); // Título distintivo
        $printer->setTextSize(1, 1);
        $printer->text("Mesa: " . $cab_pedido->ped_id . " | Tipo: " . $cab_pedido->ped_tip . "\n");
        $printer->text("Cliente: " . $datos_cliente['nombre'] . "\n");
        $printer->text("------------------------------------------\n");

        // --- DETALLE DE LO QUE SE ESTÁ PAGANDO ---
        $printer->setJustification(Printer::JUSTIFY_LEFT);
        $printer->text("Cant.  Producto              Total\n");
        $printer->text("------------------------------------------\n");

        foreach ($items_cobrados as $item) {
            // $item debe contener la descripción, cantidad y precio que viene del formulario
            $printer->text(str_pad($item['cantidad'], 6) . str_pad(substr($item['descripcion'], 0, 20), 22) . number_format($item['total'], 2) . "\n");
        }

        // --- TOTALES ---
        $printer->text("------------------------------------------\n");
        $printer->setJustification(Printer::JUSTIFY_RIGHT);
        $printer->setTextSize(2, 2);
        $printer->text("TOTAL A PAGAR: S/. " . number_format($total_parcial, 2) . "\n");
        
        $printer->setTextSize(1, 1);
        $printer->text("\n*** Gracias por su preferencia ***\n");
        $printer->feed(2);
        $printer->cut();
        $printer->close();

        return true;
    } catch (\Exception $e) {
        return false;
    }
}

public function validar_items_facturados($ped_id,$IdProducto,$cantidad){
    
    $cant_rest = 0;
    $bus_det = DB::tABLE('pedidos_detalle')->where('ped_id',$ped_id)->where('IdProducto',$IdProducto)->first();

    $cant_rest = $bus_det->ped_det_can - $bus_det->item_facturado;

 //dd($cantidad.'  '.$cant_rest);

    if($cantidad<=$cant_rest){
         return response()->json(['estado'=>'success','mensaje'=>'CANTIDAD PERMITIDA','valor'=>'1']);
    }else{
        return response()->json(['estado'=>'error','mensaje'=>'LA CANTIDAD INGRESADA ES MAYOR A LA PERMITIDA','valor'=>'0']);
    }   


}


public function validar_items_facturados_pedido($ped_id,$items,$cantidades){

        $i=0;

        foreach($items as $index => $item){

            $bus_det = DB::tABLE('pedidos_detalle')
            ->where('ped_id',$ped_id)
            ->where('IdProducto',$item)->first();

            $cant_rest = $bus_det->ped_det_can - $bus_det->item_facturado;

            if($cantidades[$index]<=$cant_rest){
                $i = $i+0;
            }else{
                $i=$i+1;
            }
        }

        if($i>0){

            return true;
        }else{
             return false;
        }


}

public function registrarcuentascobrar($venta){

    $venta = DB::tABLE('cpe_cabecera')->where('IdCpe_cabecera',$venta)->first();

    $cuentacobrar = new cuentascobrar;
    $cuentacobrar->IdCpe_cabecera = $venta->IdCpe_cabecera;
    $cuentacobrar->clicod = $venta->clicod;
    $cuentacobrar->fec_ven = $venta->ccafve;
    $cuentacobrar->abono = $venta->totalcontado;
    $cuentacobrar->estado_cob = 'pendiente';
    $cuentacobrar->total = $venta->totalcredito;
    $cuentacobrar->saldo = $venta->totalcredito;
    $cuentacobrar->id_empresa_negocio = Auth::user()->id_empresa_negocio;
    $cuentacobrar->save();

    return $cuentacobrar;
}

public function registrar_cobro_kiosko(Request $request)
    {
        DB::beginTransaction();

        try {
            $mon_icbper = $request->get('icbper_val');
            $mozo = $request->get('mozo');
            $rucemp = trim(Auth::user()->IdEmpresa);
            $empresa = \MasterSoft\Empresa::findOrFail($rucemp); // Usar namespace completo

            $tdocod = $request->get('tdocod');
            $clinum = $request->get('clinum');
            $clinom = $request->get('clinom');
            $clidir = $request->get('clidir');
            $clicor = $request->get('clicor');
            $imprimir = $request->get('imprimir');
            $consumo = $request->get('consumo');

            $mondoc = 'PEN';
            $observaciones = $request->get('observaciones');

            $ped_id = $request->get('ped_id');
            
            $estadopago_id = $request->get('estadopago');
            $fecEmi = $request->get('fecEmi');
            $fecVen = $request->get('fecVen');
            $tdicod = $request->get('tdicod');

            // Acceder a la tabla directamente para credito_dias
            $buscre = DB::table('credito_dias')->where('cre_dia_id', $estadopago_id)->first();
            if (!$buscre) {
                throw new \Exception("Estado de pago no encontrado para ID: {$estadopago_id}");
            }

            // Acceder a la tabla directamente para empresa_negocios
            $sucursal = DB::table('empresa_negocios')->where('id_empresa_negocio', Auth::user()->id_empresa_negocio)->first();
            if (!$sucursal) {
                throw new \Exception("Configuración de negocio no encontrada para el usuario actual.");
            }

            // Acceder a la tabla directamente para almacenes
            $bus_alm = DB::table('almacenes')->where('id_empresa_negocio', Auth::user()->id_empresa_negocio)
                               ->where('predeterminado', '1')
                               ->first();
            if (!$bus_alm) {
                throw new \Exception("Almacén predeterminado no encontrado para el negocio.");
            }

            // Validaciones iniciales (RUC/DNI)
            $cont_carac = strlen($clinum);
            $obt_dig = substr(trim($clinum), 0, 2);

            if ($tdocod == '01') {
                if (!($cont_carac == '11' && ($obt_dig == '10' || $obt_dig == '20' || $obt_dig == '15' || $obt_dig == '17') && $tdicod == '6')) {
                    return response()->json(['estado' => 'error', 'mensaje' => 'TIPO DE DOCUMENTO NO PERMITIDO PARA EMITIR UNA FACTURA']);
                }
            }

            // Uso de namespace completo para Cliente
            $cliente = \MasterSoft\Cliente::updateOrCreate(['clinum' => $clinum], [
                'clinom' => $clinom,
                'clidir' => $clidir,
                'clicor' => $clicor,
                'tdicod' => $tdicod,
                'telefono' => $request->get('clitel'),
                'rucemp' => Auth::user()->IdEmpresa
            ]);

            // Determinar si se están cobrando cuentas separadas
            $is_split_accounts = $request->has('account_totals');

            if ($is_split_accounts) {
                $account_totals = $request->input('account_totals');
                $account_icbper = $request->input('account_icbper');
                $account_items_json = $request->input('account_items');
                $split_payments = $request->input('split_payments');
                
                // Obtener todos los detalles del pedido original para verificar la facturación
                // Uso de namespace completo para pedidos_detalle
                $pedido_original_detalles = \MasterSoft\pedidos_detalle::where('ped_id', $ped_id)
                                                          ->where('estadoitem', 'Ingresado') // Solo ítems activos
                                                          ->get();

                foreach ($account_totals as $accountId => $account_total) {
                    $total_cuenta = (float)$account_total;
                    $icbper_cuenta = (float)$account_icbper[$accountId];
                    $items_cuenta = json_decode($account_items_json[$accountId], true);

                    if ($total_cuenta <= 0 || empty($items_cuenta)) {
                        continue; // Saltar cuentas vacías
                    }

                    // Obtener el siguiente número de comprobante para esta transacción
                    $numcomp_current = 0;
                    $sercomp_current = '';
                    if ($tdocod == '01') {
                        $sucursal_model = \MasterSoft\EmpresaNegocios::findOrFail($sucursal->id_empresa_negocio); // Necesitas el modelo para actualizarlo
                        $sucursal_model->FnuEmpresa = $sucursal_model->FnuEmpresa + 1;
                        $numcomp_current = $sucursal_model->FnuEmpresa;
                        $sercomp_current = $sucursal_model->FseEmpresa;
                        $sucursal_model->save(); // Guardar aquí para cada factura generada
                    } elseif ($tdocod == '03') {
                        $sucursal_model = \MasterSoft\EmpresaNegocios::findOrFail($sucursal->id_empresa_negocio); // Necesitas el modelo para actualizarlo
                        $sucursal_model->BnuEmpresa = $sucursal_model->BnuEmpresa + 1;
                        $numcomp_current = $sucursal_model->BnuEmpresa;
                        $sercomp_current = $sucursal_model->BseEmpresa;
                        $sucursal_model->save(); // Guardar aquí para cada factura generada
                    } elseif ($tdocod == '13') {
                        $sucursal_model = \MasterSoft\EmpresaNegocios::findOrFail($sucursal->id_empresa_negocio); // Necesitas el modelo para actualizarlo
                        $sucursal_model->NumNota = $sucursal_model->NumNota + 1;
                        $numcomp_current = $sucursal_model->NumNota;
                        $sercomp_current = $sucursal_model->SerNota;
                        $sucursal_model->save(); // Guardar aquí para cada factura generada
                    }
                    // ¡IMPORTANTE! Si tienes más tipos de documentos, añádelos aquí.
                    // Y asegúrate de usar $sucursal_model->save() después de actualizar la numeración.
                    // Si $sucursal no es un modelo Eloquent, necesitarás cargarlo como tal.
                    // Por ahora, estoy asumiendo que sí existe el modelo EmpresaNegocios.
                    // Si no, tendrías que usar DB::table()->update() para la numeración.


                    // Uso de namespace completo para cpe_cabecera
                    $cabecera_split = new \MasterSoft\cpe_cabecera;
                    $cabecera_split->tdocod = $tdocod;
                    $cabecera_split->ccafem = $fecEmi;
                    $cabecera_split->topcod = '0101';
                    $cabecera_split->id_almacen = $bus_alm->id_almacen;
                    $cabecera_split->ccafve = ($buscre->cre_dia_tip == 'CONTADO' || $buscre->cre_dia_tip == 'PERSONALIZADO') ? $fecVen : \Carbon\Carbon::parse($fecEmi)->addDays($buscre->cre_dia_fac)->format('Y-m-d');
                    $cabecera_split->ccaobs = $observaciones . " (Cta. Sep. " . ($request->input("account_names")[$accountId] ?? $accountId) . ")"; // Usar account_names
                    $cabecera_split->tdicod = $tdicod;
                    $cabecera_split->ccandi = $clinum;
                    $cabecera_split->ccanom = $clinom;
                    $cabecera_split->ped_id = $ped_id;
                    $cabecera_split->consumo = $consumo;
                    $cabecera_split->moncod = $mondoc;
                    $cabecera_split->tot_icbper = $icbper_cuenta;
                    $cabecera_split->direccion = $clidir;
                    $cabecera_split->telefono_cliente = $request->get('clitel');
                    $cabecera_split->clicorcli = $clicor;
                    $cabecera_split->cre_dia_id = $estadopago_id;
                    $cabecera_split->id_turno = Auth::user()->id_turno;

                    if ($sucursal->tip_igv_pred == '10') {
                        $cabecera_split->ccatvg = $total_cuenta / 1.105;
                        $cabecera_split->ccaigv = $total_cuenta - ($total_cuenta / 1.105);
                    } elseif ($sucursal->tip_igv_pred == '20') {
                        $cabecera_split->ccatexo = $total_cuenta;
                        $cabecera_split->ccaigv = '0.00';
                    } else {
                        $cabecera_split->ccatinaf = $total_cuenta;
                        $cabecera_split->ccaigv = '0.00';
                    }
                    
                    $cabecera_split->ccaitv = $total_cuenta;
                    $cabecera_split->id_empresa_negocio = $sucursal->id_empresa_negocio;
                    $cabecera_split->clicod = $cliente->clicod;
                    
                    // Calcular paga y vuelto para esta cuenta específica
                    $total_pagado_cuenta = 0;
                    if (isset($split_payments[$accountId])) {
                        foreach ($split_payments[$accountId] as $methodId => $amounts) {
                            foreach ($amounts as $amount) {
                                $total_pagado_cuenta += (float)$amount;
                            }
                        }
                    }
                    $cabecera_split->paga = $total_pagado_cuenta;
                    $cabecera_split->vuelto = max(0, $total_pagado_cuenta - $total_cuenta);

                    if ($buscre->cre_dia_tip == 'CONTADO') {
                        $cabecera_split->estadopago = 'CONTADO';
                        $cabecera_split->totalcontado = $total_cuenta;
                        $cabecera_split->totalcredito = '0';
                    } else {
                        $cabecera_split->estadopago = 'CREDITO';
                        $cabecera_split->totalcredito = $total_cuenta;
                        $cabecera_split->totalcontado = '0';
                    }

                    $cabecera_split->IdUsuario = Auth::user()->IdUsuario;
                    $cabecera_split->IdUsuario_ven = $mozo;
                    $cabecera_split->IdEmpresa = Auth::user()->IdEmpresa;
                    $cabecera_split->serdoc = $sercomp_current;
                    $cabecera_split->numdoc = str_pad($numcomp_current, 8, "0", STR_PAD_LEFT);
                    
                    $cabecera_split->save(); // Guardar la cabecera del nuevo comprobante

                    // Guardar medios de pago para este comprobante
                    if (isset($split_payments[$accountId])) {
                        foreach ($split_payments[$accountId] as $methodId => $amounts) {
                            foreach ($amounts as $amount) {
                                DB::table('venta_medio_pago')->insert([
                                    'id_turno' => Auth::user()->id_turno,
                                    'IdCpe_cabecera' => $cabecera_split->IdCpe_cabecera,
                                    'id_med_pag' => $methodId,
                                    'monto' => (float)$amount
                                ]);
                            }
                        }
                    }

                    // Guardar detalles del comprobante para esta cuenta
                    foreach ($items_cuenta as $item_data) {
                        // Uso de namespace completo para productos
                        $product_info = \MasterSoft\productos::findOrFail($item_data['id']);
                        $id_prod_rel = empty($product_info->pro_rel) ? $product_info->IdProducto : $product_info->pro_rel;

                        $precio_uni = $item_data['precio'];
                        $valor_uni = $product_info->tigcod == '10' ? $item_data['precio'] / 1.105 : $item_data['precio'];
                        $valor_subtotal_item = $product_info->tigcod == '10' ? ($item_data['precio'] * $item_data['cantidad']) / 1.105 : ($item_data['precio'] * $item_data['cantidad']);
                        $valor_total_item = $item_data['precio'] * $item_data['cantidad'];
                        $valor_igv_item = $valor_total_item - $valor_subtotal_item;
                        
                        // Ajuste por ICBPER para el detalle
                        $mon_icbper_item_total = ($item_data['icbper'] == 1) ? ($item_data['cantidad'] * $mon_icbper) : 0;
                        $valor_total_item += $mon_icbper_item_total;


                        // Uso de namespace completo para cpe_detalle
                        $detalle_split = new \MasterSoft\cpe_detalle;
                        $detalle_split->IdCpe_cabecera = $cabecera_split->IdCpe_cabecera;
                        $detalle_split->cdecan = $item_data['cantidad'];
                        $detalle_split->cdepuni = $precio_uni;
                        $detalle_split->cdevun = $valor_uni;
                        $detalle_split->cdevve = $valor_total_item;
                        $detalle_split->cdepve = $valor_subtotal_item;
                        $detalle_split->cdeigv = $valor_igv_item;
                        $detalle_split->costo = $product_info->costo;
                        $detalle_split->tigcod = $product_info->tigcod;
                        $detalle_split->umecod = $product_info->umecod;
                        $detalle_split->cpe_det_factor = $product_info->factor;
                        $detalle_split->procod = $product_info->procod;                        
                        $detalle_split->IdProducto = $item_data['id'];
                        $detalle_split->IdProducto_rel = $id_prod_rel;
                        $detalle_split->cdedes = $item_data['nombre'];
                        $detalle_split->mon_icbper_det = $mon_icbper;
                        $detalle_split->icbper = $item_data['icbper'];
                        $detalle_split->id_almacen_pro = $bus_alm->id_almacen;
                        $detalle_split->save();

                        // Actualizar `item_facturado` en `pedidos_detalle` para el pedido original
                        // Uso de namespace completo para pedidos_detalle
                        $original_ped_det = \MasterSoft\pedidos_detalle::where('ped_id', $ped_id)
                                                            ->where('IdProducto', $item_data['id'])
                                                            ->first();
                        if ($original_ped_det) {
                            $original_ped_det->item_facturado += $item_data['cantidad'];
                            $original_ped_det->save();
                        }

                        // Registrar movimiento de salida para cada item facturado
                        $this->registrar_movimiento_salida_comanda_individual(
                            $item_data['id'],
                            $item_data['cantidad'],
                            Auth::user()->id_empresa_negocio,
                            $clinom, // Nombre del cliente de esta factura/boleta
                            $ped_id,
                            $fecEmi
                        );
                    }

                    // Generar QR y XML para cada comprobante de cuenta separada
                    if ($tdocod == '01' || $tdocod == '03') {
                        // Uso de namespace completo para cpe_cabecera
                        $sunat_split = new \MasterSoft\cpe_cabecera;
                        $sunat_split->generar_nuevo_qr($cabecera_split->IdCpe_cabecera);
                        $sunat_split->generar_xml_boleta_factura($cabecera_split->IdCpe_cabecera);
                        if ($empresa->tipo_envio == '1') {
                            $sunat_split->enviar_sunat($cabecera_split->IdCpe_cabecera); 
                        }
                    }

                    // Imprimir cada comprobante si aplica
                    if ($imprimir == '1' && $sucursal->formato == 'TICKET' && ($sucursal->ticket_pantalla ?? '0') == '0') {    
                        for ($i = 1; $i <= ($empresa->imp_venta ?? 1); $i++) {
                            $this->imprimir($cabecera_split->IdCpe_cabecera, $tdocod);
                        }
                    }

                    // Registrar en usuario_facturacion para cada comprobante
                    // Uso de namespace completo para usuario_facturacion
                    $usuario_facturacion_split = new \MasterSoft\usuario_facturacion;
                    $usuario_facturacion_split->IdCpe_cabecera = $cabecera_split->IdCpe_cabecera;
                    $usuario_facturacion_split->id_turno = Auth::user()->id_turno;
                    $usuario_facturacion_split->id_empresa_negocio = $sucursal->id_empresa_negocio;
                    $usuario_facturacion_split->IdEmpresa = Auth::user()->IdEmpresa;
                    $usuario_facturacion_split->referencia = "Cobro Cta. Separada";
                    $usuario_facturacion_split->ped_id = $ped_id; // Asociar al pedido original
                    $usuario_facturacion_split->save();
                }

                // Al finalizar todas las cuentas separadas, verifica si el pedido original está completamente facturado
                // Uso de namespace completo para pedidos_detalle
                $total_items_original_qty = \MasterSoft\pedidos_detalle::where('ped_id', $ped_id)->sum('ped_det_can');
                $total_items_facturados_qty = \MasterSoft\pedidos_detalle::where('ped_id', $ped_id)->sum('item_facturado');
                
                // Si la suma de las cantidades facturadas es igual o mayor a la cantidad original del pedido
                if ($total_items_facturados_qty >= $total_items_original_qty) {
                    $this->liberar_mesa($ped_id); // Liberar mesa si todo está facturado
                }

                DB::commit();
                return response()->json(['estado' => 'success', 'mensaje' => 'Cuentas separadas emitidas exitosamente.']);

            } else {
                // ** Lógica para COBRANZA TOTAL (ya existente, pero ajustada a las variables del request) **
                $total_venta_global = $request->get('total_venta');
                $pagar_global = $request->get('pagar');
                $vuelto_global = $request->get('vuelto');
                $id_med_pag_arr = $request->get('id_med_pag');
                $mon_med_pag_arr = $request->get('mon_med_pag');

                // Uso de namespace completo para pedidos_detalle
                $pedido_original_items_detalle = \MasterSoft\pedidos_detalle::where('ped_id', $ped_id)
                                                                ->where('estadoitem', 'Ingresado')
                                                                ->get();
                $items_a_facturar_total = [];
                foreach ($pedido_original_items_detalle as $item) {
                    $cantidad_a_cobrar_actual = $item->ped_det_can - $item->item_facturado;
                    if ($cantidad_a_cobrar_actual > 0) {
                        $items_a_facturar_total[] = [
                            'id' => $item->IdProducto,
                            'cantidad' => $cantidad_a_cobrar_actual,
                            'precio' => $item->ped_det_pre,
                            'descripcion' => $item->descripcion,
                            'icbper_ind' => $item->icbper_ind,
                            'item_facturado_original' => $item->item_facturado,
                            'ped_det_id' => $item->ped_det_id
                        ];
                    }
                }
                
                if (empty($items_a_facturar_total) && (float)$total_venta_global <= 0) {
                    $this->liberar_mesa($ped_id);
                    DB::commit();
                    return response()->json(['estado' => 'success', 'mensaje' => 'Pedido ya estaba completamente cobrado o no tenía ítems pendientes.']);
                }

                // Generar el comprobante principal
                // Uso de namespace completo para cpe_cabecera
                $cabecera = new \MasterSoft\cpe_cabecera;
                $cabecera->tdocod = $tdocod;
                $cabecera->ccafem = $fecEmi;
                $cabecera->topcod = '0101';
                $cabecera->id_almacen = $bus_alm->id_almacen;
                $cabecera->ccafve = ($buscre->cre_dia_tip == 'CONTADO' || $buscre->cre_dia_tip == 'PERSONALIZADO') ? $fecVen : \Carbon\Carbon::parse($fecEmi)->addDays($buscre->cre_dia_fac)->format('Y-m-d');
                $cabecera->ccaobs = $observaciones;
                $cabecera->tdicod = $tdicod;
                $cabecera->ccandi = $clinum;
                $cabecera->ccanom = $clinom;
                $cabecera->ped_id = $ped_id;
                $cabecera->consumo = $consumo;
                $cabecera->moncod = $mondoc;
                $cabecera->tot_icbper = $request->get('icbper_tot'); // Total ICBPER del request
                $cabecera->direccion = $clidir;
                $cabecera->telefono_cliente = $request->get('clitel');
                $cabecera->clicorcli = $clicor;
                $cabecera->cre_dia_id = $estadopago_id;
                $cabecera->id_turno = Auth::user()->id_turno;

                if ($sucursal->tip_igv_pred == '10') {
                    $cabecera->ccatvg =  $total_venta_global / 1.105;
                    $cabecera->ccaigv =  $total_venta_global - ($total_venta_global / 1.105);
                } else if ($sucursal->tip_igv_pred == '20') {
                    $cabecera->ccatexo =  $total_venta_global;
                    $cabecera->ccaigv = '0.00';
                } else {
                    $cabecera->ccatinaf = $total_venta_global;
                    $cabecera->ccaigv = '0.00';
                }
                
                $cabecera->ccaitv = $total_venta_global;
                $cabecera->id_empresa_negocio = $sucursal->id_empresa_negocio;
                $cabecera->clicod = $cliente->clicod;
                $cabecera->vuelto = $vuelto_global;
                $cabecera->paga = $pagar_global;

                if ($buscre->cre_dia_tip == 'CONTADO') {
                    $cabecera->estadopago = 'CONTADO';
                    $cabecera->totalcontado = $total_venta_global;
                    $cabecera->totalcredito = '0';
                } else {
                    $cabecera->estadopago = 'CREDITO';
                    $cabecera->totalcredito = $total_venta_global;
                    $cabecera->totalcontado = '0';
                }

                $cabecera->IdUsuario = Auth::user()->IdUsuario;
                $cabecera->IdUsuario_ven = $mozo;
                $cabecera->IdEmpresa =  Auth::user()->IdEmpresa;

                // Acceder a la tabla directamente para EmpresaNegocios para actualizar la numeración
                $empresanegocio_update = DB::table('empresa_negocios')->where('id_empresa_negocio', $sucursal->id_empresa_negocio);

                if($tdocod == '01'){
                    $current_num = ($empresanegocio_update->first()->FnuEmpresa ?? 0) + 1;
                    $empresanegocio_update->update(['FnuEmpresa' => $current_num]);
                    $cabecera->serdoc = $sucursal->FseEmpresa;
                    $cabecera->numdoc = str_pad($current_num, 8, "0", STR_PAD_LEFT);
                } elseif($tdocod == '03'){
                    $current_num = ($empresanegocio_update->first()->BnuEmpresa ?? 0) + 1;
                    $empresanegocio_update->update(['BnuEmpresa' => $current_num]);
                    $cabecera->serdoc = $sucursal->BseEmpresa;
                    $cabecera->numdoc = str_pad($current_num, 8, "0", STR_PAD_LEFT);
                } elseif($tdocod == '13'){
                    $current_num = ($empresanegocio_update->first()->NumNota ?? 0) + 1;
                    $empresanegocio_update->update(['NumNota' => $current_num]);
                    $cabecera->serdoc = $sucursal->SerNota;
                    $cabecera->numdoc = str_pad($current_num, 8, "0", STR_PAD_LEFT);
                } 

                $cabecera->save();

                // Guardar medios de pago
                if (!empty($id_med_pag_arr)) {
                    foreach ($id_med_pag_arr as $index_mp => $mp) {
                        DB::table('venta_medio_pago')->insert([
                            'id_turno' => Auth::user()->id_turno,
                            'IdCpe_cabecera' => $cabecera->IdCpe_cabecera,
                            'id_med_pag' => $mp,
                            'monto' => $mon_med_pag_arr[$index_mp]
                        ]);
                    }
                } else {
                    // Si no hay medios de pago explícitos, asume el total va al medio de pago seleccionado
                    DB::table('venta_medio_pago')->insert([
                        'id_turno' => Auth::user()->id_turno,
                        'IdCpe_cabecera' => $cabecera->IdCpe_cabecera,
                        'id_med_pag' => $request->get('med_pag'),
                        'monto' => $total_venta_global
                    ]);
                }

                // Guardar detalles del comprobante (cpe_detalle) y actualizar pedidos_detalle
                foreach ($items_a_facturar_total as $item_data) {
                    // Uso de namespace completo para productos
                    $product_info = \MasterSoft\productos::findOrFail($item_data['id']);
                    $id_prod_rel = empty($product_info->pro_rel) ? $product_info->IdProducto : $product_info->pro_rel;

                    $precio_uni = $item_data['precio'];
                    $valor_uni = $product_info->tigcod == '10' ? $item_data['precio'] / 1.105 : $item_data['precio'];
                    $valor_subtotal_item = $product_info->tigcod == '10' ? ($item_data['precio'] * $item_data['cantidad']) / 1.105 : ($item_data['precio'] * $item_data['cantidad']);
                    $valor_total_item = $item_data['precio'] * $item_data['cantidad'];
                    $valor_igv_item = $valor_total_item - $valor_subtotal_item;
                    
                    // Ajuste por ICBPER para el detalle
                    $mon_icbper_item_total = ($item_data['icbper_ind'] == 1) ? ($item_data['cantidad'] * $mon_icbper) : 0;
                    $valor_total_item += $mon_icbper_item_total;


                    // Uso de namespace completo para cpe_detalle
                    $detalle_cpe = new \MasterSoft\cpe_detalle;
                    $detalle_cpe->IdCpe_cabecera = $cabecera->IdCpe_cabecera;
                    $detalle_cpe->cdecan = $item_data['cantidad'];
                    $detalle_cpe->cdepuni = $precio_uni;
                    $detalle_cpe->cdevun = $valor_uni;
                    $detalle_cpe->cdevve = $valor_total_item;
                    $detalle_cpe->cdepve = $valor_subtotal_item;
                    $detalle_cpe->cdeigv = $valor_igv_item;
                    $detalle_cpe->costo = $product_info->costo;
                    $detalle_cpe->tigcod = $product_info->tigcod;
                    $detalle_cpe->umecod = $product_info->umecod;
                    $detalle_cpe->cpe_det_factor = $product_info->factor;
                    $detalle_cpe->procod = $product_info->procod;
                    $detalle_cpe->IdProducto = $item_data['id'];
                    $detalle_cpe->IdProducto_rel = $id_prod_rel;
                    $detalle_cpe->cdedes = $item_data['descripcion'];
                    $detalle_cpe->mon_icbper_det = $mon_icbper;
                    $detalle_cpe->icbper = $item_data['icbper_ind'];
                    $detalle_cpe->id_almacen_pro = $bus_alm->id_almacen;
                    $detalle_cpe->save();

                    // Actualizar `item_facturado` en `pedidos_detalle`
                    // Uso de namespace completo para pedidos_detalle
                    $original_ped_det = \MasterSoft\pedidos_detalle::find($item_data['ped_det_id']);
                    if ($original_ped_det) {
                        $original_ped_det->item_facturado += $item_data['cantidad'];
                        $original_ped_det->save();
                    }
                }

                $this->registrar_movimiento_salida($cabecera->IdCpe_cabecera); // Registra los movimientos de stock
                $this->liberar_mesa($ped_id); // Libera la mesa

                // Uso de namespace completo para usuario_facturacion
                $usuario_facturacion = new \MasterSoft\usuario_facturacion;
                $usuario_facturacion->IdCpe_cabecera = $cabecera->IdCpe_cabecera;
                $usuario_facturacion->id_turno = Auth::user()->id_turno;
                $usuario_facturacion->id_empresa_negocio = $sucursal->id_empresa_negocio;
                $usuario_facturacion->IdEmpresa = Auth::user()->IdEmpresa;
                $usuario_facturacion->referencia = "Registro";
                $usuario_facturacion->ped_id = $ped_id; // Asociar al pedido original
                $usuario_facturacion->save();

                if ($tdocod == '01' || $tdocod == '03') {
                    // Uso de namespace completo para cpe_cabecera
                    $sunat = new \MasterSoft\cpe_cabecera;
                    $sunat->generar_nuevo_qr($cabecera->IdCpe_cabecera);
                    $sunat->generar_xml_boleta_factura($cabecera->IdCpe_cabecera);
                    if ($empresa->tipo_envio == '1') {
                        $sunat->enviar_sunat($cabecera->IdCpe_cabecera); 
                    }
                }

                if ($imprimir == '1') {
                    if ($sucursal->formato == 'TICKET' && ($sucursal->ticket_pantalla ?? '0') == '0') {    
                        for ($i = 1; $i <= ($empresa->imp_venta ?? 1); $i++) {
                            $this->imprimir($cabecera->IdCpe_cabecera, $tdocod);
                        }
                    }
                }

                DB::commit();
                return response()->json(['estado' => 'success', 'codfact' => $cabecera->IdCpe_cabecera, 'tdocod' => $tdocod, 'mensaje' => 'Comprobante Emitido']);
            }

        } catch (\Exception $e) {
            DB::rollback();
            // Log more detailed information for debugging
            \Log::error("Error en registrar_cobro_kiosko (PHP): " . $e->getMessage() . "\nStack Trace: " . $e->getTraceAsString());
            return response()->json(['estado' => 'error', 'mensaje' => 'Hubo un error al procesar el cobro: ' . $e->getMessage()]);
        }
    }


    
    public function registrar_cobro(Request $request){



    DB::beginTransaction();

    try{

        $tot_icbper = $request->get('icbper_tot');
   
        $mon_icbper = $request->get('icbper_val');
        $mozo = $request->get('mozo');
       $rucemp = trim(Auth::user()->IdEmpresa);
       $empresa = Empresa::findOrFail($rucemp);
       
       $ped_tip = $request->get('ped_tip');
       $pis_id = $request->get('pis_id');
       $mes_id = $request->get('mes_id');
       $tdicod = $request->get('tdicod');
       $clinum = $request->get('clinum');
       $clinom = $request->get('clinom');
       $clidir = $request->get('clidir');
       $clicor = $request->get('clicor');
       $imprimir = $request->get('imprimir');
       $consumo = $request->get('consumo');
       $mozo = $request->get('mozo');

       $mondoc = 'PEN';
       $observaciones = $request->get('observaciones');
       

       $ped_id = $request->get('ped_id');
       $total_venta = $request->get('total_venta');
       $vuelto = $request->get('vuelto');
       $pagar = $request->get('pagar');

       $tdocod = $request->get('tdocod');
       $estadopago = $request->get('estadopago');
       $fecEmi = $request->get('fecEmi');
       $fecVen = $request->get('fecVen');
       

       $id_med_pag = $request->get('id_med_pag');
       $med_pag = $request->get('med_pag');
       $mon_med_pag = $request->get('mon_med_pag');
       
       
       // ✅ AGREGADO: Variables para cuotas
       $mon_cuo = $request->get('mon_cuo');
       $fec_cuo = $request->get('fec_cuo');

       $items = $request->get('txt_id_producto');
       $cantidades = $request->get('txt_cantidad');
       $precios = $request->get('precios');       

       /*if(!empty($id_med_pag)){
        if($tdocod=='13'){
            foreach ($id_med_pag as $idx => $vmp){
                $val_mp = DB::tABLE('medios_pagos')->where('id_med_pag',$vmp)->first();
                
                if($val_mp->predeterminado !='1'){
                    $tdocod='03';
                }
            }
        }
    }else{
        if($tdocod=='13'){
            $val_mp = DB::tABLE('medios_pagos')->where('id_med_pag',$med_pag)->first();

            if($val_mp->predeterminado !='1'){
                $tdocod='03';
            }
        }
    }*/


    $buscre = DB::tABLE('credito_dias')->where('cre_dia_id',$estadopago)->first();

    $sucursal = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first();

    $bus_alm= DB::tABLE('almacenes')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->where('predeterminado','1')->first();

    $cont_carac = strlen($clinum);
    $obt_dig = substr(trim($clinum), 0, 2);

    if($tdocod=='01'){
        if($cont_carac =='11' && ($obt_dig=='10' || $obt_dig=='20' || $obt_dig=='15' || $obt_dig=='17') && $tdicod=='6'){
          
        }else{
          return response()->json(['estado'=>'error','mensaje'=>'TIPO DE DOCUMENTO NO PERMITIDO PARA EMITIR UNA FACTURA']);
      }
  }
  

  $empresanegocio = EmpresaNegocios::findOrFail($sucursal->id_empresa_negocio);

  if($tdocod == '01'){
      $senudoc = DB::tABLE('empresa_negocios')->select('FseEmpresa','FnuEmpresa')->where('id_empresa_negocio',$sucursal->id_empresa_negocio)->first();
      $numcomp =  $senudoc->FnuEmpresa+1;
      $sercomp =  $senudoc->FseEmpresa;
  }elseif ($tdocod =='03') {
      $senudoc = DB::tABLE('empresa_negocios')->select('BseEmpresa','BnuEmpresa')->where('id_empresa_negocio',$sucursal->id_empresa_negocio)->first();
      $numcomp =  $senudoc->BnuEmpresa+1;
      $sercomp =  $senudoc->BseEmpresa;
  }elseif ($tdocod =='13') {
      $senudoc = DB::tABLE('empresa_negocios')->select('SerNota','NumNota')->where('id_empresa_negocio',$sucursal->id_empresa_negocio)->first();
      $numcomp =  $senudoc->NumNota+1;
      $sercomp =  $senudoc->SerNota;
  }elseif ($tdocod =='15') {
      $senudoc = DB::tABLE('empresa_negocios')->select('ProNum','ProSer')->where('id_empresa_negocio',$sucursal->id_empresa_negocio)->first();
      $numcomp =  $senudoc->ProNum+1;
      $sercomp =  $senudoc->ProSer;
  }elseif ($tdocod =='14') {
      $senudoc = DB::tABLE('empresa_negocios')->select('NumVal','SerVal')->where('id_empresa_negocio',$sucursal->id_empresa_negocio)->first();
      $numcomp =  $senudoc->NumVal+1;
      $sercomp =  $senudoc->SerVal;
  }


  $cliente = Cliente::UpdateOrCreate(
    ['clinum' => $clinum],
    [
        'clinom' => $clinom,
        'clidir' => $clidir,
        'clicor' => $clicor,
        'tdicod' => $tdicod,
        'telefono' => $request->get('telefono'), // Corregido el nombre del input
        'fecha_nacimiento' => $request->get('fecha_nacimiento'), // Nuevo campo
        'cuenta12' => $request->get('cuenta12'), // Nuevo campo
        'rucemp' => Auth::user()->IdEmpresa
    ]
);



  $cabecera = new cpe_cabecera;
  $cabecera->cuenta12 = $cliente->cuenta12 ?? '121201';
  $cabecera->tdocod = $tdocod;
  $cabecera->ccafem = $fecEmi;
  $cabecera->topcod = '0101';
  $cabecera->id_almacen = $bus_alm->id_almacen;

  
  if($buscre->cre_dia_tip=='CONTADO' || $buscre->cre_dia_tip=='PERSONALIZADO'){
    $cabecera->ccafve = $request->get('fecVen');
}else{
    $cabecera->ccafve = date('Y-m-d',strtotime($fecEmi."+ ".$buscre->cre_dia_fac." days"));
}

if($buscre->cre_dia_tip=='CONTADO'){
    
    $cabecera->totalcontado = $total_venta;
    $cabecera->totalcredito = '0';

}elseif($buscre->cre_dia_tip=='CREDITO' || $buscre->cre_dia_tip=='PERSONALIZADO'){
    $cabecera->totalcredito = $total_venta;
    $cabecera->totalcontado = '0';

}

$cabecera->ccaobs = $observaciones;
$cabecera->tdicod = $tdicod;
$cabecera->ccandi = $clinum;
$cabecera->ccanom = $clinom;
$cabecera->ped_id = $ped_id;
$cabecera->mozo = $mozo;
$cabecera->ped_tip = $ped_tip;
$cabecera->pis_id = $pis_id;
$cabecera->mes_id = $mes_id;
$cabecera->consumo = $consumo;
$cabecera->moncod = $mondoc;
$cabecera->tot_icbper = $tot_icbper;
$cabecera->direccion = $clidir;
$cabecera->telefono_cliente = $request->get('telefono');
$cabecera->clicorcli = $clicor;
$cabecera->cre_dia_id = $estadopago;
$cabecera->id_turno = Auth::user()->id_turno;

if($sucursal->tip_igv_pred =='10'){
    $cabecera->ccatvg =  $total_venta/1.105;
    $cabecera->ccaigv =  $total_venta-$total_venta/1.105;
}

if($sucursal->tip_igv_pred =='20'){
    $cabecera->ccatexo =  $total_venta;
    $cabecera->ccaigv = '0.00';
}

$cabecera->ccatinaf =  '0.00';
$cabecera->ccaitv = $total_venta;
$cabecera->id_empresa_negocio = $sucursal->id_empresa_negocio;
$cabecera->clicod = $cliente->clicod;
$cabecera->vuelto = $vuelto;
$cabecera->paga = $pagar;

if($buscre->cre_dia_tip=='CONTADO'){
 $cabecera->estadopago = 'CONTADO';
}else{
 $cabecera->estadopago = 'CREDITO';
}


$cabecera->IdUsuario = Auth::user()->IdUsuario;
$cabecera->IdUsuario_ven = $mozo;
$cabecera->IdEmpresa =  Auth::user()->IdEmpresa;

if($tdocod=='01'){
  if( $empresanegocio->FnuEmpresa == $numcomp){
      $modnumcomp = $numcomp+1;
  }else{
      $modnumcomp = $numcomp;
  }

  $empresanegocio->FseEmpresa = $sercomp;
  $empresanegocio->FnuEmpresa = $modnumcomp;
  
  $numdoc = str_pad($modnumcomp,8,"0", STR_PAD_LEFT);
  $cabecera->serdoc= $sercomp;
  $cabecera->numdoc = $numdoc;
  
}elseif($tdocod=='03'){
  if( $empresanegocio->BnuEmpresa == $numcomp){
      $modnumcomp = $numcomp+1;
  }else{
      $modnumcomp = $numcomp;
  }

  $empresanegocio->BseEmpresa = $sercomp;
  $empresanegocio->BnuEmpresa = $modnumcomp;

  $numdoc = str_pad($modnumcomp,8,"0", STR_PAD_LEFT);
  $cabecera->serdoc= $sercomp;
  $cabecera->numdoc = $numdoc;
  
}elseif($tdocod=='13'){
  if( $empresanegocio->NumNota == $numcomp){
      $modnumcomp = $numcomp+1;
  }else{
      $modnumcomp = $numcomp;
  }

  $empresanegocio->SerNota = $sercomp;
  $empresanegocio->NumNota = $modnumcomp;

  $numdoc = str_pad($modnumcomp,8,"0", STR_PAD_LEFT);
  $cabecera->serdoc= $sercomp;
  $cabecera->numdoc = $numdoc;
  
  
}elseif($tdocod=='15'){
  if( $empresanegocio->ProNum == $numcomp){
      $modnumcomp = $numcomp+1;
  }else{
      $modnumcomp = $numcomp;
  }

  $empresanegocio->ProSer = $sercomp;
  $empresanegocio->ProNum = $modnumcomp;

  $numdoc = str_pad($modnumcomp,8,"0", STR_PAD_LEFT);
  $cabecera->serdoc= $sercomp;
  $cabecera->numdoc = $numdoc;
  
  

}elseif($tdocod=='14'){
  if( $empresanegocio->NumVal == $numcomp){
      $modnumcomp = $numcomp+1;
  }else{
      $modnumcomp = $numcomp;
  }

  $empresanegocio->SerVal = $sercomp;
  $empresanegocio->NumVal = $modnumcomp;
  

  $numdoc = str_pad($modnumcomp,8,"0", STR_PAD_LEFT);
  $cabecera->serdoc= $sercomp;
  $cabecera->numdoc = $numdoc;
  
}

$empresanegocio->update();
$cabecera->save();

    // =================================================================
    // INICIO: LÓGICA DE FIDELIZACIÓN (PUNTOS HOLA P)
    // =================================================================
    // Solo damos puntos si no es un cliente "Varios" (00000000)
    if($clinum != '00000000' && strlen(trim($clinum)) >= 8) {
    $saldo_antes = $cliente->puntos ?? 0;
    
    // Obtenemos la tasa de conversión (1 sol = X puntos)
    $regla_base = DB::table('fidelizacion_configs')->where('activo', 1)->first();
    $valor_sol = $regla_base ? $regla_base->valor_sol : 1;
    
    $puntos_ganados = floor($total_venta / $valor_sol);
    $puntos_gastados_total = 0;

    // 1. PROCESAR PREMIOS CANJEADOS EN ESTA VENTA
    if($request->has('premios_canjeados')){
        foreach($request->get('premios_canjeados') as $id_regla){
            $regla_canje = DB::table('fidelizacion_configs')->where('id', $id_regla)->first();
            if($regla_canje){
                $puntos_gastados_total += $regla_canje->puntos_minimos;
                
                // Guardamos el movimiento del premio vinculado a ESTA boleta
                DB::table('puntos_historial')->insert([
                    'cliente_id' => $cliente->clicod,
                    'venta_id' => $cabecera->IdCpe_cabecera,
                    'puntos_ganados' => 0,
                    'puntos_canjeados' => $regla_canje->puntos_minimos,
                    'saldo_antes' => 0, // No es necesario aquí, calcularemos el saldo final global
                    'saldo_despues' => 0, 
                    'motivo' => 'PREMIO: ' . strtoupper($regla_canje->premio),
                    'created_at' => \Carbon\Carbon::now(),
                    'updated_at' => \Carbon\Carbon::now()
                ]);
            }
        }
    }

    // 2. REGISTRAR LOS PUNTOS GANADOS
    if($puntos_ganados > 0){
         DB::table('puntos_historial')->insert([
            'cliente_id' => $cliente->clicod,
            'venta_id' => $cabecera->IdCpe_cabecera,
            'puntos_ganados' => $puntos_ganados,
            'puntos_canjeados' => 0,
            'saldo_antes' => 0,
            'saldo_despues' => 0,
            'motivo' => 'Consumo en Venta',
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now()
        ]);
    }

    // 3. ACTUALIZAR SALDO FINAL DEL CLIENTE
    $saldo_final = $saldo_antes + $puntos_ganados - $puntos_gastados_total;
    DB::table('cliente')
        ->where('clicod', $cliente->clicod)
        ->update(['puntos' => $saldo_final]);
}
    // =================================================================
    // FIN: LÓGICA DE FIDELIZACIÓN
    // =================================================================

// ✅ AGREGADO: Registrar cuotas cuando es CRÉDITO
$codfact = $cabecera->IdCpe_cabecera;

if($buscre->cre_dia_tip=='CREDITO' || $buscre->cre_dia_tip=='PERSONALIZADO' ){

    if(!empty($mon_cuo)){
        $i=0;
        foreach ($mon_cuo as $key => $mc){

            $i=$i+1;

            DB::tABLE('ventas_cuotas')
            ->insert([
                'ven_cuo_num'=>$i,
                'ven_cuo_fec_ven'=>$fec_cuo[$key],
                'ven_cuo_mon'=>$mc,
                'IdCpe_cabecera'=>$codfact
            ]);
        }
    }else{

        DB::tABLE('ventas_cuotas')
            ->insert([
                'ven_cuo_num'=>'1',
                'ven_cuo_fec_ven'=>$cabecera->ccafve,
                'ven_cuo_mon'=>$cabecera->ccaitv,
                'IdCpe_cabecera'=>$codfact
        ]);

    }
}

// ✅ MODIFICADO: Solo registrar medios de pago si es CONTADO
if($buscre->cre_dia_tip=='CONTADO'){
    
    if(!empty($id_med_pag)){
        foreach($id_med_pag as $index_mp =>$mp){

            DB::tABLE('venta_medio_pago')
            ->insert(['id_turno'=>Auth::user()->id_turno,
                'IdCpe_cabecera'=>$cabecera->IdCpe_cabecera,
                'id_med_pag'=>$mp,
                'monto'=>$mon_med_pag[$index_mp]]);
        }
        
    }else{

     
       DB::tABLE('venta_medio_pago')
       ->insert(['id_turno'=>Auth::user()->id_turno,
        'IdCpe_cabecera'=>$cabecera->IdCpe_cabecera,
        'id_med_pag'=>$med_pag,
        'monto'=>$total_venta]);
    }

}


foreach($items as $index => $item){

    $dat_pro = productos::findOrFail($item);

    if(empty($dat_pro->pro_rel)){
        $id_prod = $dat_pro->IdProducto;
    }else{
        $id_prod = $dat_pro->pro_rel;
    }


    if($dat_pro->tigcod =='10'){

      $precio_uni = $precios[$index];
      $valor_uni = ($precios[$index]/1.105);
      

      $valor_subtotal = ($precios[$index]*$cantidades[$index])/1.105;
      $valor_total = $precios[$index]*$cantidades[$index];
      
  }elseif($dat_pro->tigcod=='20'){
    
      $precio_uni = $precios[$index];
      $valor_uni = $precios[$index];
      

      $valor_subtotal = $precios[$index]*$cantidades[$index];
      $valor_total = $precios[$index]*$cantidades[$index];
  }

  

  $valor_igv_total =  $valor_total-$valor_subtotal;
  
  
  $detalle = new cpe_detalle;
  $detalle->IdCpe_cabecera =  $cabecera->IdCpe_cabecera;
  $detalle->cdecan = $cantidades[$index];
  $detalle->cdepuni = $precio_uni;
  $detalle->cdevun = $valor_uni;
  $detalle->cdevve  = $valor_total;
  $detalle->cdepve  = $valor_subtotal;
  $detalle->cdeigv = $valor_igv_total;
  $detalle->costo = $dat_pro->costo;
  $detalle->tigcod = $dat_pro->tigcod;
  $detalle->umecod = $dat_pro->umecod;
  $detalle->cpe_det_factor = $dat_pro->factor;
  $detalle->procod = $dat_pro->procod;
  $detalle->debe = $dat_pro->debe;
  $detalle->haber = $dat_pro->haber;
  $detalle->cta_contable_70 = $this->obtenerCuenta70PorProducto($dat_pro);
  $detalle->IdProducto = $item;  
  $detalle->IdProducto_rel = $id_prod;
  $detalle->cdedes = $request->get('descripcion')[$index];
  $detalle->mon_icbper_det = $mon_icbper;

          //$detalle->pronomobs = $pronomobs[$index];  
  $detalle->icbper = $dat_pro->icbper;
          //$detalle->desc_mon = $cantidades[$index]*$descuento[$index];
  $detalle->id_almacen_pro = $bus_alm->id_almacen;
  $detalle->save();
  



}

self::registrar_movimiento_salida($cabecera->IdCpe_cabecera);

self::liberar_mesa($ped_id);


$usuario_facturacion = new usuario_facturacion;
$usuario_facturacion->IdCpe_cabecera = $cabecera->IdCpe_cabecera;
$usuario_facturacion->id_turno = Auth::user()->id_turno;
$usuario_facturacion->id_empresa_negocio = $sucursal->id_empresa_negocio;
$usuario_facturacion->IdEmpresa = Auth::user()->IdEmpresa;
$usuario_facturacion->referencia = "Registro";
$usuario_facturacion->save();

if($buscre->cre_dia_tip !='CONTADO'){
          self::registrarcuentascobrar($codfact);
        }



if($tdocod =='01' || $tdocod=='03'){
    $sunat = new cpe_cabecera;
    $sunat->generar_nuevo_qr($cabecera->IdCpe_cabecera);

    $sunat->generar_xml_boleta_factura($cabecera->IdCpe_cabecera);

    if($empresa->tipo_envio =='1'){
        
        $sunat->enviar_sunat($cabecera->IdCpe_cabecera); 
    }

    
}

/*if($imprimir=='1'){
    if($sucursal->formato =='TICKET' && $sucursal->ticket_pantalla=='0'){    
        for($i=1;$i<=$empresa->imp_venta;$i++){           
                  self::imprimir($cabecera->IdCpe_cabecera,$tdocod);            
        }
    }
}*/

/*if($imprimir=='1'){
    if($sucursal->formato =='TICKET' && $sucursal->ticket_pantalla=='0'){    
        
        // 1. Verificamos si se usó IZIPAY (Su ID es 2 en la base de datos)
        $pago_con_izipay = false;
        
        // Evaluamos si el pago tiene múltiples medios o es uno simple
        if(!empty($id_med_pag)){
            // Si $id_med_pag es un array, verificamos si el ID 2 está dentro
            if(in_array(2, $id_med_pag)){
                $pago_con_izipay = true;
            }
        } else {
            // Si es un solo medio de pago
            if($med_pag == 2){
                $pago_con_izipay = true;
            }
        }
        
        // 2. Si es IZIPAY forzamos 2 impresiones, sino usamos la configuración habitual de la empresa
        $cantidad_impresiones = ($pago_con_izipay) ? 2 : $empresa->imp_venta;
        
        // 3. Ejecutamos el bucle de impresión con la cantidad ajustada
        for($i=1; $i<=$cantidad_impresiones; $i++){
            self::imprimir($cabecera->IdCpe_cabecera, $tdocod);
        }
    }
}*/

if($imprimir=='1'){
    if($sucursal->formato =='TICKET' && $sucursal->ticket_pantalla=='0'){    
        
        $forzar_dos_copias = false;
        
        // Evaluamos los medios de pago empleados
        if(!empty($id_med_pag)){
            // Si es multipago, verificamos si hay algún medio diferente a 1 (Efectivo)
            foreach($id_med_pag as $mp_id) {
                if($mp_id != 1) {
                    $forzar_dos_copias = true;
                    break; 
                }
            }
        } else {
            // Si es un solo medio de pago y es diferente a 1
            if($med_pag != 1){
                $forzar_dos_copias = true;
            }
        }
        
        // REGLA: Si tiene otros medios de pago -> 2 copias. 
        // Si es netamente ID 1 (Efectivo) -> Forzamos 1 copia en lugar de $empresa->imp_venta
        $cantidad_impresiones = ($forzar_dos_copias) ? 2 : 1;
        
        // Ejecutamos el bucle de impresión con la cantidad ajustada
        for($i=1; $i<=$cantidad_impresiones; $i++){
            self::imprimir($cabecera->IdCpe_cabecera, $tdocod);
        }
    }
}



DB::commit();

return response()->json(['estado'=>'success','codfact' =>$cabecera->IdCpe_cabecera,'tdocod'=>$tdocod,'mensaje'=>'Comprobante Emitido']);

}catch(\Exception $e){
    DB::rollback();
    return response()->json(['estado'=>'error','mensaje'=>$e->getMessage()]);
}


}


    public function registrar_cobro_cs(Request $request){

    DB::beginTransaction();

    try{

        $tot_icbper = $request->get('icbper_tot');
        $mon_icbper = $request->get('icbper_val');

       $rucemp = trim(Auth::user()->IdEmpresa);
       $empresa = Empresa::findOrFail($rucemp);

       $tdicod = $request->get('tdicod');
       $clinum = $request->get('clinum');
       $clinom = $request->get('clinom');
       $clidir = $request->get('clidir');
       $clicor = $request->get('clicor');
       $imprimir = $request->get('imprimir');
       $consumo = $request->get('consumo');

       $mozo = $request->get('mozo');
       $ped_tip = $request->get('ped_tip');
       $pis_id = $request->get('pis_id');
       $mes_id = $request->get('mes_id');

       $mondoc = 'PEN';
       $observaciones = $request->get('observaciones');
       
       $ped_id = $request->get('ped_id');
       $total_venta = $request->get('total_venta');
       $vuelto = $request->get('vuelto');
       $pagar = $request->get('pagar');

       $tdocod = $request->get('tdocod');
       $estadopago = $request->get('estadopago');
       $fecEmi = $request->get('fecEmi');
       $fecVen = $request->get('fecVen');

       $id_med_pag = $request->get('id_med_pag');
       $med_pag = $request->get('med_pag');
       $mon_med_pag = $request->get('mon_med_pag');
       
       // Variables para cuotas
       $mon_cuo = $request->get('mon_cuo');
       $fec_cuo = $request->get('fec_cuo');

       $items = $request->get('txt_id_producto');
       $cantidades = $request->get('txt_cantidad');
       $precios = $request->get('precios');

       $val_item_facturado = self::validar_items_facturados_pedido($ped_id,$items,$cantidades);
       
        if($val_item_facturado == true){
            return response()->json(['estado'=>'error','mensaje'=>'LA CANTIDAD DE LOS PRODUCTOS ES MAYOR A LA CANTIDAD POR FACTURAR']);
        }

    $buscre = DB::tABLE('credito_dias')->where('cre_dia_id',$estadopago)->first();
    $sucursal = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first();
    $bus_alm= DB::tABLE('almacenes')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->where('predeterminado','1')->first();

    $cont_carac = strlen($clinum);
    $obt_dig = substr(trim($clinum), 0, 2);

    if($tdocod=='01'){
        if($cont_carac =='11' && ($obt_dig=='10' || $obt_dig=='20' || $obt_dig=='15' || $obt_dig=='17') && $tdicod=='6'){
          
        }else{
          return response()->json(['estado'=>'error','mensaje'=>'TIPO DE DOCUMENTO NO PERMITIDO PARA EMITIR UNA FACTURA']);
      }
  }
  
  $empresanegocio = EmpresaNegocios::findOrFail($sucursal->id_empresa_negocio);

  if($tdocod == '01'){
      $senudoc = DB::tABLE('empresa_negocios')->select('FseEmpresa','FnuEmpresa')->where('id_empresa_negocio',$sucursal->id_empresa_negocio)->first();
      $numcomp =  $senudoc->FnuEmpresa+1;
      $sercomp =  $senudoc->FseEmpresa;
  }elseif ($tdocod =='03') {
      $senudoc = DB::tABLE('empresa_negocios')->select('BseEmpresa','BnuEmpresa')->where('id_empresa_negocio',$sucursal->id_empresa_negocio)->first();
      $numcomp =  $senudoc->BnuEmpresa+1;
      $sercomp =  $senudoc->BseEmpresa;
  }elseif ($tdocod =='13') {
      $senudoc = DB::tABLE('empresa_negocios')->select('SerNota','NumNota')->where('id_empresa_negocio',$sucursal->id_empresa_negocio)->first();
      $numcomp =  $senudoc->NumNota+1;
      $sercomp =  $senudoc->SerNota;
  }elseif ($tdocod =='15') {
      $senudoc = DB::tABLE('empresa_negocios')->select('ProNum','ProSer')->where('id_empresa_negocio',$sucursal->id_empresa_negocio)->first();
      $numcomp =  $senudoc->ProNum+1;
      $sercomp =  $senudoc->ProSer;
  }elseif ($tdocod =='14') {
      $senudoc = DB::tABLE('empresa_negocios')->select('NumVal','SerVal')->where('id_empresa_negocio',$sucursal->id_empresa_negocio)->first();
      $numcomp =  $senudoc->NumVal+1;
      $sercomp =  $senudoc->SerVal;
  }

  $cliente = Cliente::UpdateOrCreate(
    ['clinum' => $clinum],
    [
        'clinom' => $clinom,
        'clidir' => $clidir,
        'clicor' => $clicor,
        'tdicod' => $tdicod,
        'telefono' => $request->get('telefono'), // Corregido el nombre del input
        'fecha_nacimiento' => $request->get('fecha_nacimiento'), // Nuevo campo
        'cuenta12' => $request->get('cuenta12'), // Nuevo campo
        'rucemp' => Auth::user()->IdEmpresa
    ]
);

  $cabecera = new cpe_cabecera;
  $cabecera->cuenta12 = $cliente->cuenta12 ?? '121201';
  $cabecera->tdocod = $tdocod;
  $cabecera->ccafem = $fecEmi;
  $cabecera->topcod = '0101';
  $cabecera->id_almacen = $bus_alm->id_almacen;
  
  if($buscre->cre_dia_tip=='CONTADO' || $buscre->cre_dia_tip=='PERSONALIZADO'){
    $cabecera->ccafve = $request->get('fecVen');
}else{
    $cabecera->ccafve = date('Y-m-d',strtotime($fecEmi."+ ".$buscre->cre_dia_fac." days"));
}

if($buscre->cre_dia_tip=='CONTADO'){
    $cabecera->totalcontado = $total_venta;
    $cabecera->totalcredito = '0';
}elseif($buscre->cre_dia_tip=='CREDITO' || $buscre->cre_dia_tip=='PERSONALIZADO'){
    $cabecera->totalcredito = $total_venta;
    $cabecera->totalcontado = '0';
}

$cabecera->ccaobs = $observaciones;
$cabecera->tdicod = $tdicod;
$cabecera->ccandi = $clinum;
$cabecera->ccanom = $clinom;
$cabecera->ped_id = $ped_id;
$cabecera->mozo = $mozo;
$cabecera->ped_tip = $ped_tip;
$cabecera->pis_id = $pis_id;
$cabecera->mes_id = $mes_id;
$cabecera->consumo = $consumo;
$cabecera->moncod = $mondoc;
$cabecera->tot_icbper = $tot_icbper;
$cabecera->direccion = $clidir;
$cabecera->telefono_cliente = $request->get('telefono');
$cabecera->clicorcli = $clicor;
$cabecera->cre_dia_id = $estadopago;
$cabecera->id_turno = Auth::user()->id_turno;

if($sucursal->tip_igv_pred =='10'){
    $cabecera->ccatvg =  $total_venta/1.105;
    $cabecera->ccaigv =  $total_venta-$total_venta/1.105;
}

if($sucursal->tip_igv_pred =='20'){
    $cabecera->ccatexo =  $total_venta;
    $cabecera->ccaigv = '0.00';
}

$cabecera->ccatinaf =  '0.00';
$cabecera->ccaitv = $total_venta;
$cabecera->id_empresa_negocio = $sucursal->id_empresa_negocio;
$cabecera->clicod = $cliente->clicod;
$cabecera->vuelto = $vuelto;
$cabecera->paga = $pagar;

if($buscre->cre_dia_tip=='CONTADO'){
 $cabecera->estadopago = 'CONTADO';
}else{
 $cabecera->estadopago = 'CREDITO';
}

$cabecera->IdUsuario = Auth::user()->IdUsuario;
$cabecera->IdUsuario_ven = $mozo; // Agregado también como en el principal
$cabecera->IdEmpresa =  Auth::user()->IdEmpresa;

if($tdocod=='01'){
  if( $empresanegocio->FnuEmpresa == $numcomp){
      $modnumcomp = $numcomp+1;
  }else{
      $modnumcomp = $numcomp;
  }
  $empresanegocio->FseEmpresa = $sercomp;
  $empresanegocio->FnuEmpresa = $modnumcomp;
  
  $numdoc = str_pad($modnumcomp,8,"0", STR_PAD_LEFT);
  $cabecera->serdoc= $sercomp;
  $cabecera->numdoc = $numdoc;
  
}elseif($tdocod=='03'){
  if( $empresanegocio->BnuEmpresa == $numcomp){
      $modnumcomp = $numcomp+1;
  }else{
      $modnumcomp = $numcomp;
  }
  $empresanegocio->BseEmpresa = $sercomp;
  $empresanegocio->BnuEmpresa = $modnumcomp;

  $numdoc = str_pad($modnumcomp,8,"0", STR_PAD_LEFT);
  $cabecera->serdoc= $sercomp;
  $cabecera->numdoc = $numdoc;
  
}elseif($tdocod=='13'){
  if( $empresanegocio->NumNota == $numcomp){
      $modnumcomp = $numcomp+1;
  }else{
      $modnumcomp = $numcomp;
  }
  $empresanegocio->SerNota = $sercomp;
  $empresanegocio->NumNota = $modnumcomp;

  $numdoc = str_pad($modnumcomp,8,"0", STR_PAD_LEFT);
  $cabecera->serdoc= $sercomp;
  $cabecera->numdoc = $numdoc;
  
}elseif($tdocod=='15'){
  if( $empresanegocio->ProNum == $numcomp){
      $modnumcomp = $numcomp+1;
  }else{
      $modnumcomp = $numcomp;
  }
  $empresanegocio->ProSer = $sercomp;
  $empresanegocio->ProNum = $modnumcomp;

  $numdoc = str_pad($modnumcomp,8,"0", STR_PAD_LEFT);
  $cabecera->serdoc= $sercomp;
  $cabecera->numdoc = $numdoc;

}elseif($tdocod=='14'){
  if( $empresanegocio->NumVal == $numcomp){
      $modnumcomp = $numcomp+1;
  }else{
      $modnumcomp = $numcomp;
  }
  $empresanegocio->SerVal = $sercomp;
  $empresanegocio->NumVal = $modnumcomp;
  
  $numdoc = str_pad($modnumcomp,8,"0", STR_PAD_LEFT);
  $cabecera->serdoc= $sercomp;
  $cabecera->numdoc = $numdoc;
}

$empresanegocio->update();
$cabecera->save();

// Imprimir precuenta parcial automática después de guardar la cabecera
self::imprimir_precuenta_parcial_automatico($cabecera->IdCpe_cabecera);

// =================================================================
// INICIO: LÓGICA DE FIDELIZACIÓN (PUNTOS HOLA P) - ADAPTADO A CS
// =================================================================
if($clinum != '00000000' && strlen(trim($clinum)) >= 8) {
    $saldo_antes = $cliente->puntos ?? 0;
    
    // Obtenemos la tasa de conversión
    $regla_base = DB::table('fidelizacion_configs')->where('activo', 1)->first();
    $valor_sol = $regla_base ? $regla_base->valor_sol : 1;
    
    // Se calculan los puntos sobre el total a pagar EN ESTA subcuenta (total_venta)
    $puntos_ganados = floor($total_venta / $valor_sol);
    $puntos_gastados_total = 0;

    // 1. PROCESAR PREMIOS CANJEADOS EN ESTA VENTA
    if($request->has('premios_canjeados')){
        foreach($request->get('premios_canjeados') as $id_regla){
            $regla_canje = DB::table('fidelizacion_configs')->where('id', $id_regla)->first();
            if($regla_canje){
                $puntos_gastados_total += $regla_canje->puntos_minimos;
                
                DB::table('puntos_historial')->insert([
                    'cliente_id' => $cliente->clicod,
                    'venta_id' => $cabecera->IdCpe_cabecera,
                    'puntos_ganados' => 0,
                    'puntos_canjeados' => $regla_canje->puntos_minimos,
                    'saldo_antes' => 0, 
                    'saldo_despues' => 0, 
                    'motivo' => 'PREMIO: ' . strtoupper($regla_canje->premio),
                    'created_at' => \Carbon\Carbon::now(),
                    'updated_at' => \Carbon\Carbon::now()
                ]);
            }
        }
    }

    // 2. REGISTRAR LOS PUNTOS GANADOS
    if($puntos_ganados > 0){
         DB::table('puntos_historial')->insert([
            'cliente_id' => $cliente->clicod,
            'venta_id' => $cabecera->IdCpe_cabecera,
            'puntos_ganados' => $puntos_ganados,
            'puntos_canjeados' => 0,
            'saldo_antes' => 0,
            'saldo_despues' => 0,
            'motivo' => 'Consumo en Venta Parcial',
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now()
        ]);
    }

    // 3. ACTUALIZAR SALDO FINAL DEL CLIENTE
    $saldo_final = $saldo_antes + $puntos_ganados - $puntos_gastados_total;
    DB::table('cliente')
        ->where('clicod', $cliente->clicod)
        ->update(['puntos' => $saldo_final]);
}
// =================================================================
// FIN: LÓGICA DE FIDELIZACIÓN
// =================================================================

// Registrar cuotas cuando es CRÉDITO
$codfact = $cabecera->IdCpe_cabecera;

if($buscre->cre_dia_tip=='CREDITO' || $buscre->cre_dia_tip=='PERSONALIZADO' ){
    if(!empty($mon_cuo)){
        $i=0;
        foreach ($mon_cuo as $key => $mc){
            $i=$i+1;
            DB::tABLE('ventas_cuotas')
            ->insert([
                'ven_cuo_num'=>$i,
                'ven_cuo_fec_ven'=>$fec_cuo[$key],
                'ven_cuo_mon'=>$mc,
                'IdCpe_cabecera'=>$codfact
            ]);
        }
    }else{
        DB::tABLE('ventas_cuotas')
            ->insert([
                'ven_cuo_num'=>'1',
                'ven_cuo_fec_ven'=>$cabecera->ccafve,
                'ven_cuo_mon'=>$cabecera->ccaitv,
                'IdCpe_cabecera'=>$codfact
        ]);
    }
}

// Solo registrar medios de pago si es CONTADO
if($buscre->cre_dia_tip=='CONTADO'){
    if(!empty($id_med_pag)){
        foreach($id_med_pag as $index_mp =>$mp){
            DB::tABLE('venta_medio_pago')
            ->insert(['id_turno'=>Auth::user()->id_turno,
                'IdCpe_cabecera'=>$cabecera->IdCpe_cabecera,
                'id_med_pag'=>$mp,
                'monto'=>$mon_med_pag[$index_mp]]);
        }
    }else{
       DB::tABLE('venta_medio_pago')
       ->insert(['id_turno'=>Auth::user()->id_turno,
        'IdCpe_cabecera'=>$cabecera->IdCpe_cabecera,
        'id_med_pag'=>$med_pag,
        'monto'=>$total_venta]);
    }
}

foreach($items as $index => $item){
    if($cantidades[$index]>0){
        $dat_pro = productos::findOrFail($item);

        if(empty($dat_pro->pro_rel)){
            $id_prod = $dat_pro->IdProducto;
        }else{
            $id_prod = $dat_pro->pro_rel;
        }

        if($dat_pro->tigcod =='10'){
          $precio_uni = $precios[$index];
          $valor_uni = ($precios[$index]/1.105);
          $valor_subtotal = ($precios[$index]*$cantidades[$index])/1.105;
          $valor_total = $precios[$index]*$cantidades[$index];
      
        }elseif($dat_pro->tigcod=='20'){
              $precio_uni = $precios[$index];
              $valor_uni = $precios[$index];
              $valor_subtotal = $precios[$index]*$cantidades[$index];
              $valor_total = $precios[$index]*$cantidades[$index];
        }

        $valor_igv_total =  $valor_total-$valor_subtotal;
          
         $detalle = new cpe_detalle;
         $detalle->IdCpe_cabecera =  $cabecera->IdCpe_cabecera;
         $detalle->cdecan = $cantidades[$index];
         $detalle->cdepuni = $precio_uni;
         $detalle->cdevun = $valor_uni;
         $detalle->cdevve  = $valor_total;
         $detalle->cdepve  = $valor_subtotal;
         $detalle->cdeigv = $valor_igv_total;
         $detalle->costo = $dat_pro->costo;
         $detalle->tigcod = $dat_pro->tigcod;
         $detalle->umecod = $dat_pro->umecod;
         $detalle->cpe_det_factor = $dat_pro->factor;
         $detalle->procod = $dat_pro->procod;
         
         // Agregados los campos debe y haber que vi en tu código original
         $detalle->debe = $dat_pro->debe ?? null;
         $detalle->haber = $dat_pro->haber ?? null;
         $detalle->cta_contable_70 = $this->obtenerCuenta70PorProducto($dat_pro);

         $detalle->IdProducto = $item;
         $detalle->IdProducto_rel = $id_prod;
         $detalle->cdedes = $request->get('descripcion')[$index];
         $detalle->mon_icbper_det = $mon_icbper;
         $detalle->icbper = $dat_pro->icbper;
         $detalle->id_almacen_pro = $bus_alm->id_almacen;
         $detalle->save();
          
         $bus_item_ped = DB::tABLE('pedidos_detalle')->where('ped_id',$ped_id)->where('IdProducto',$item)->first();

         DB::tABLE('pedidos_detalle')
          ->where('ped_det_id',$bus_item_ped->ped_det_id)
          ->update([
            'item_facturado'=>$cantidades[$index]+$bus_item_ped->item_facturado
          ]);
    }
}

$sum_can = DB::tABLE('pedidos_detalle')->where('ped_id',$ped_id)->sum('ped_det_can');
$sum_item_fac = DB::tABLE('pedidos_detalle')->where('ped_id',$ped_id)->sum('item_facturado');

self::registrar_movimiento_salida($cabecera->IdCpe_cabecera);

if($sum_can == $sum_item_fac){
    self::liberar_mesa($ped_id);
}

$es_ultimo_cobro = ($sum_can == $sum_item_fac);
if($es_ultimo_cobro){
    self::liberar_mesa($ped_id);
}

$usuario_facturacion = new usuario_facturacion;
$usuario_facturacion->IdCpe_cabecera = $cabecera->IdCpe_cabecera;
$usuario_facturacion->id_turno = Auth::user()->id_turno;
$usuario_facturacion->id_empresa_negocio = $sucursal->id_empresa_negocio;
$usuario_facturacion->IdEmpresa = Auth::user()->IdEmpresa;
$usuario_facturacion->referencia = "Registro";
$usuario_facturacion->save();

if($buscre->cre_dia_tip !='CONTADO'){
    self::registrarcuentascobrar($codfact);
}

if($tdocod =='01' || $tdocod=='03'){
    $sunat = new cpe_cabecera;
    $sunat->generar_nuevo_qr($cabecera->IdCpe_cabecera);
    $sunat->generar_xml_boleta_factura($cabecera->IdCpe_cabecera);

    if($empresa->tipo_envio =='1'){
        $sunat->enviar_sunat($cabecera->IdCpe_cabecera); 
    }
}

/*if($imprimir=='1'){
    if($sucursal->formato =='TICKET' && $sucursal->ticket_pantalla=='0'){    
        for($i=1;$i<=$empresa->imp_venta;$i++){
             self::imprimir($cabecera->IdCpe_cabecera,$tdocod);
        }
    }
}*/

if($imprimir=='1'){
    if($sucursal->formato =='TICKET' && $sucursal->ticket_pantalla=='0'){    
        
        // 1. Verificamos si se usó un medio de pago diferente a 1 (Efectivo)
        $forzar_dos_copias = false;
        
        // Evaluamos si el pago tiene múltiples medios o es uno simple
        if(!empty($id_med_pag)){
            // Si es un array, recorremos para ver si hay algún ID que NO sea 1
            foreach($id_med_pag as $mp_id) {
                if($mp_id != 1) {
                    $forzar_dos_copias = true;
                    break; // Con encontrar uno diferente a 1, basta
                }
            }
        } else {
            // Si es un solo medio de pago y es diferente a 1
            if($med_pag != 1){
                $forzar_dos_copias = true;
            }
        }
        
        // 2. Si es diferente a 1 forzamos 2 impresiones, sino usamos la configuración habitual de la empresa
        $cantidad_impresiones = ($forzar_dos_copias) ? 2 : $empresa->imp_venta;
        
        // 3. Ejecutamos el bucle de impresión con la cantidad ajustada
        for($i=1; $i<=$cantidad_impresiones; $i++){
            self::imprimir($cabecera->IdCpe_cabecera, $tdocod);
        }
    }
}

DB::commit();

return response()->json(['estado'=>'success','codfact' =>$cabecera->IdCpe_cabecera,'tdocod'=>$tdocod,'mensaje'=>'Comprobante Emitido','pedido'=>$ped_id,'finalizado' => $es_ultimo_cobro]);

}catch(\Exception $e){
    DB::rollback();
    return response()->json(['estado'=>'error','mensaje'=>$e->getMessage()]);
}

}

private function obtenerCuenta70PorProducto($dat_pro)
{
    if(!$dat_pro) {
        return null;
    }

    // Si el producto tiene referencia a otro producto, usamos el producto principal para obtener la configuración
    if(!empty($dat_pro->pro_rel)){
        $producto_rel = productos::find($dat_pro->pro_rel);
        if($producto_rel){
            $dat_pro = $producto_rel;
        }
    }

    if(!empty($dat_pro->tip_pro_id)){
        $tipo = DB::table('tipo_producto')->where('tip_pro_id',$dat_pro->tip_pro_id)->first();
        if($tipo && !empty($tipo->cta_contable_70)){
            return $tipo->cta_contable_70;
        }
    }

    if(!empty($dat_pro->cat_id)){
        $tipo = DB::table('categorias')
            ->join('tipo_producto','categorias.tip_pro_id','=','tipo_producto.tip_pro_id')
            ->where('categorias.cat_id',$dat_pro->cat_id)
            ->select('tipo_producto.cta_contable_70')
            ->first();
        if($tipo && !empty($tipo->cta_contable_70)){
            return $tipo->cta_contable_70;
        }
    }

    return null;
}

public function imprimir_precuenta_parcial_automatico($id_cabecera) {
    // 1. Obtenemos datos con Query Builder
    $cabecera = DB::table('cpe_cabecera')->where('IdCpe_cabecera', $id_cabecera)->first();
    
    // Si por alguna razón no encuentra la cabecera, abortamos para no dar error
    if (!$cabecera) return;

    $detalle = DB::table('cpe_detalle')->where('IdCpe_cabecera', $id_cabecera)->get();
    $sucursal = DB::table('empresa_negocios')->where('id_empresa_negocio', $cabecera->id_empresa_negocio)->first();

    // 2. Buscamos la impresora según el terminal del usuario (Igual que en tu función imprimir)
    $impresoras = DB::table('configuracion_impresoras')->where('Id', Auth::user()->terminal)->first();

    if (!$impresoras) return;

    try {
        // 3. Conectores dinámicos (RED o COMPARTIDO)
        /*if ($impresoras->tip_conex_imp == 'COMPARTIDO') {
            $connector = new \Mike42\Escpos\PrintConnectors\WindowsPrintConnector("smb://" . $impresoras->ruta);
        } elseif ($impresoras->tip_conex_imp == 'RED') {
            $connector = new \Mike42\Escpos\PrintConnectors\NetworkPrintConnector($impresoras->ruta, 9100);
        } else {
            return;
        }*/

        //IMPRESORA VIRTUAL
                $connector = new DummyPrintConnector();
                $printer = new Printer($connector);

        $printer = new \Mike42\Escpos\Printer($connector);

        // --- ENCABEZADO ---
        $printer->setJustification(\Mike42\Escpos\Printer::JUSTIFY_CENTER);
        
        if($sucursal && file_exists($sucursal->logosuc)){
            $logo = \Mike42\Escpos\EscposImage::load(public_path().'/'.$sucursal->logosuc, false);
            $printer->bitImage($logo);
        }

        $printer->setTextSize(2, 2);
        $printer->text("PRE-CUENTA PARCIAL\n");
        $printer->setTextSize(1, 1);
        $printer->text("Pedido: " . $cabecera->ped_id . "\n");
        $printer->text("------------------------------------------\n");

        // --- DATOS DEL CLIENTE ---
        $printer->setJustification(\Mike42\Escpos\Printer::JUSTIFY_LEFT);
        
        // ✅ CORRECCIÓN AQUÍ: Usamos la fecha de emisión o la actual para evitar el error de created_at
        $fecha_impresion = isset($cabecera->ccafem) ? $cabecera->ccafem : date('Y-m-d');
        $printer->text("Fecha:   " . $fecha_impresion . " " . date('H:i') . "\n");
        
        $printer->text("Cliente: " . substr($cabecera->ccanom, 0, 30) . "\n");
        $printer->text("------------------------------------------\n");
        
        // --- DETALLE ---
        $printer->text("Cant.  Descripcion          P.U    Total\n");
        foreach ($detalle as $det) {
            $printer->text(str_pad(number_format($det->cdecan, 0), 6) . 
                           str_pad(substr($det->cdedes, 0, 18), 20) . 
                           str_pad(number_format($det->cdepuni, 2), 7) . 
                           number_format($det->cdevve, 2) . "\n");
        }

        $printer->text("__________________________________________\n");
        $printer->setJustification(\Mike42\Escpos\Printer::JUSTIFY_RIGHT);
        $printer->setTextSize(2, 2);
        $printer->text("TOTAL: S/ " . number_format($cabecera->ccaitv, 2) . "\n");
        
        $printer->setTextSize(1, 1);
        $printer->setJustification(\Mike42\Escpos\Printer::JUSTIFY_CENTER);
        $printer->text("\nSISTEMA DESARROLLADO POR HOLAPE\n");
        
        $printer->feed(3);
        $printer->cut();
        $codigo_raw = $connector->getData();
        $printer->close();

        DB::table('cola_impresion')->insert([
            'contenido'  => base64_encode($codigo_raw), 
            'impresora'  => $impresoras->descripcion, // Usará la de caja ("CPE") o la asignada al terminal
            'estado'     => '0'            
        ]);

    } catch (\Exception $e) {
        \Log::error("Error en Precuenta Parcial: " . $e->getMessage());
    }
}





public function liberar_mesa($ped_id){

    $pedido = pedidos::findOrFail($ped_id);
    $pedido->ped_est = 'Cerrado';
    $pedido->update();

    if(!empty($pedido->mes_id)){

          DB::tABLE('mesas')->where('mes_id',$pedido->mes_id)->update(['mes_est'=>'Libre']);

          $buscar = DB::tABLE('mesas_union')->where('mes_id_act',$pedido->mes_id)->where('mes_uni_est','APERTURADO')->get();

          if(!empty($buscar)){
            foreach($buscar as $bus){
                DB::tABLE('mesas')->where('mes_id',$bus->mes_id)->update(['mes_est'=>'Libre','ind_union'=>'0']);
                DB::tABLE('mesas_union')->where('mes_uni_id',$bus->mes_uni_id)->update(['mes_uni_est'=>'CERRADO']);
            }           
          }
    }
    

    return 'liberado';

    
}

public function registrar_movimiento_salida_comanda($ped_id){
            $pedido = pedidos::findOrFail($ped_id);
            $detalle_pedido = pedidos_detalle::where('ped_id', $ped_id)->get();
            $sucursal = DB::table('empresa_negocios')->where('id_empresa_negocio', Auth::user()->id_empresa_negocio)->first();
            $bus_alm = DB::table('almacenes')->where('id_empresa_negocio', Auth::user()->id_empresa_negocio)->where('predeterminado','1')->first();

            foreach($detalle_pedido as $det){
                $bus_pro = productos::findOrFail($det->IdProducto);

                $id_prod = empty($bus_pro->pro_rel) ? $bus_pro->IdProducto : $bus_pro->pro_rel;

                // Si es un producto simple (promocion = 0)
                if($bus_pro->promocion == '0'){
                    $cantidad_principal = $det->ped_det_can * $bus_pro->factor;
                    $cantidad_equivalente = 0;
                    
                    if(!empty($bus_pro->factor_cons) && $bus_pro->factor_cons > 0){
                        $cantidad_equivalente = $cantidad_principal * $bus_pro->factor_cons;
                    }

                    DB::table('movimientos_productos')->insert([
                        'IdProducto' => $det->IdProducto,
                        'IdProducto_rel' => $id_prod,
                        'precio' => $det->ped_det_pre,
                        'cantidad' => $cantidad_principal,
                        'cantidad_equivalente' => $cantidad_equivalente,
                        'costo' => $bus_pro->costo,
                        'cliente' => $pedido->ped_cli_nom,
                        'IdCpe_cabecera' => null,
                        'serie' => null,
                        'numero' => null,
                        'tdocod' => null,
                        'tipo' => '3',
                        'mov_tip' => 'E',
                        'stock_equivalente' => $cantidad_equivalente,
                        'id_empresa_negocio' => $pedido->id_empresa_negocio,
                        'id_almacen' => $bus_alm->id_almacen,
                        'fecha_mov' => $pedido->ped_fec,
                        'descripcion' => 'Salida por Comanda (Pedido ID: ' . $ped_id . ')',
                        'mov_lote' => $bus_pro->lote,
                        'mov_vencimiento' => $bus_pro->vencimiento,
                    ]);

                    $stock_prod = DB::table('producto_stock')
                        ->where('IdProducto', $id_prod)
                        ->where('id_almacen', $bus_alm->id_almacen)
                        ->first();

                    if ($stock_prod) {
                        $nuevo_stock = $stock_prod->stock - $cantidad_principal;
                        $nuevo_stock_equivalente = $stock_prod->stock_equivalencia - $cantidad_equivalente;
                        
                        DB::table('producto_stock')
                            ->where('pro_sto_id', $stock_prod->pro_sto_id)
                            ->update([
                                'stock' => $nuevo_stock,
                                'stock_equivalencia' => $nuevo_stock_equivalente
                            ]);
                    }
                } 
                // Si es un producto que tiene receta (promocion = 2) - Tragos/Comidas
                elseif($bus_pro->promocion == '2'){
                    $bus_receta = DB::table('recetas')->where('prod_id', $id_prod)->get();

                    if(count($bus_receta) > 0){
                        foreach($bus_receta as $rec){
                            $bus_insumo = productos::findOrFail($rec->prod_insu);
                            
                            $cantidad_equivalente = $det->ped_det_can * $rec->rec_cant;
                            $cantidad_principal = 0;
                            
                            if(!empty($bus_insumo->factor_cons) && $bus_insumo->factor_cons > 0){
                                $cantidad_principal = $cantidad_equivalente / $bus_insumo->factor_cons;
                            }

                            DB::table('movimientos_productos')->insert([
                                'IdProducto' => $rec->prod_insu,
                                'IdProducto_rel' => $rec->prod_insu,
                                'precio' => '0',
                                'cantidad' => $cantidad_principal,
                                'cantidad_equivalente' => $cantidad_equivalente,
                                'costo' => $rec->ins_costo,
                                'cliente' => $pedido->ped_cli_nom,
                                'IdCpe_cabecera' => null,
                                'serie' => null,
                                'numero' => null,
                                'tdocod' => null,
                                'tipo' => '3',
                                'mov_tip' => 'E',
                                'stock_equivalente' => $cantidad_equivalente,
                                'id_empresa_negocio' => $pedido->id_empresa_negocio,
                                'id_almacen' => $bus_alm->id_almacen,
                                'fecha_mov' => $pedido->ped_fec,
                                'descripcion' => 'Salida por Receta (Pedido ID: ' . $ped_id . ')',
                            ]);

                            $stock_prod_ins = DB::table('producto_stock')
                                ->where('IdProducto', $rec->prod_insu)
                                ->where('id_almacen', $bus_alm->id_almacen)
                                ->first();

                            if ($stock_prod_ins) {
                                $nuevo_stock = $stock_prod_ins->stock - $cantidad_principal;
                                $nuevo_stock_equivalente = $stock_prod_ins->stock_equivalencia - $cantidad_equivalente;
                                
                                DB::table('producto_stock')
                                    ->where('pro_sto_id', $stock_prod_ins->pro_sto_id)
                                    ->update([
                                        'stock' => $nuevo_stock,
                                        'stock_equivalencia' => $nuevo_stock_equivalente
                                    ]);
                            }
                        }
                    }
                }
                // Si es un COMBO (promocion = 3) - CORREGIDO
                elseif($bus_pro->promocion == '3'){
                    
                    // CORRECCIÓN: Buscar por IdProducto_rel (el combo principal)
                    $bus_combo = DB::table('combos')->where('IdProducto_rel', $id_prod)->get();

                    if(count($bus_combo) > 0){
                        
                        // Iterar sobre cada ítem del combo
                        foreach($bus_combo as $combo_item){
                            
                            $bus_pro_combo = productos::findOrFail($combo_item->IdProducto_comb);
                            $id_prod_combo = empty($bus_pro_combo->pro_rel) ? $bus_pro_combo->IdProducto : $bus_pro_combo->pro_rel;

                            // Si el ítem del combo es un PRODUCTO SIMPLE (promocion = 0)
                            if($bus_pro_combo->promocion == '0'){
                                
                                $cantidad_principal = ($det->ped_det_can * $combo_item->prod_comb_cant) * $bus_pro_combo->factor;
                                $cantidad_equivalente = 0;
                                
                                if(!empty($bus_pro_combo->factor_cons) && $bus_pro_combo->factor_cons > 0){
                                    $cantidad_equivalente = $cantidad_principal * $bus_pro_combo->factor_cons;
                                }

                                DB::table('movimientos_productos')->insert([
                                    'IdProducto' => $combo_item->IdProducto_comb,
                                    'IdProducto_rel' => $id_prod_combo,
                                    'precio' => $combo_item->prod_comb_prec,
                                    'cantidad' => $cantidad_principal,
                                    'cantidad_equivalente' => $cantidad_equivalente,
                                    'costo' => $combo_item->prod_comb_cost,
                                    'cliente' => $pedido->ped_cli_nom,
                                    'IdCpe_cabecera' => null,
                                    'serie' => null,
                                    'numero' => null,
                                    'tdocod' => null,
                                    'tipo' => '3',
                                    'mov_tip' => 'E',
                                    'stock_equivalente' => $cantidad_equivalente,
                                    'id_empresa_negocio' => $pedido->id_empresa_negocio,
                                    'id_almacen' => $bus_alm->id_almacen,
                                    'fecha_mov' => $pedido->ped_fec,
                                    'descripcion' => 'Salida por Combo: '.$bus_pro->pronom.' - Item: '.$bus_pro_combo->pronom.' (Pedido ID: ' . $ped_id . ')',
                                    'mov_lote' => $bus_pro_combo->lote,
                                    'mov_vencimiento' => $bus_pro_combo->vencimiento,
                                ]);

                                $stock_prod = DB::table('producto_stock')
                                    ->where('IdProducto', $id_prod_combo)
                                    ->where('id_almacen', $bus_alm->id_almacen)
                                    ->first();

                                if ($stock_prod) {
                                    $nuevo_stock = $stock_prod->stock - $cantidad_principal;
                                    $nuevo_stock_equivalente = $stock_prod->stock_equivalencia - $cantidad_equivalente;
                                    
                                    DB::table('producto_stock')
                                        ->where('pro_sto_id', $stock_prod->pro_sto_id)
                                        ->update([
                                            'stock' => $nuevo_stock,
                                            'stock_equivalencia' => $nuevo_stock_equivalente
                                        ]);
                                }

                            } 
                            // Si el ítem del combo es un PRODUCTO PREPARADO (promocion = 2)
                            elseif($bus_pro_combo->promocion == '2'){
                                
                                $bus_receta = DB::table('recetas')->where('prod_id', $id_prod_combo)->get();

                                if(count($bus_receta) > 0){
                                    foreach($bus_receta as $rec){
                                        
                                        $bus_insumo = productos::findOrFail($rec->prod_insu);
                                        
                                        // Calcular cantidad considerando combo y receta
                                        $cantidad_equivalente = ($det->ped_det_can * $combo_item->prod_comb_cant) * $rec->rec_cant;
                                        $cantidad_principal = 0;
                                        
                                        if(!empty($bus_insumo->factor_cons) && $bus_insumo->factor_cons > 0){
                                            $cantidad_principal = $cantidad_equivalente / $bus_insumo->factor_cons;
                                        }

                                        DB::table('movimientos_productos')->insert([
                                            'IdProducto' => $rec->prod_insu,
                                            'IdProducto_rel' => $rec->prod_insu,
                                            'precio' => '0',
                                            'cantidad' => $cantidad_principal,
                                            'cantidad_equivalente' => $cantidad_equivalente,
                                            'costo' => $rec->ins_costo,
                                            'cliente' => $pedido->ped_cli_nom,
                                            'IdCpe_cabecera' => null,
                                            'serie' => null,
                                            'numero' => null,
                                            'tdocod' => null,
                                            'tipo' => '3',
                                            'mov_tip' => 'E',
                                            'stock_equivalente' => $cantidad_equivalente,
                                            'id_empresa_negocio' => $pedido->id_empresa_negocio,
                                            'id_almacen' => $bus_alm->id_almacen,
                                            'fecha_mov' => $pedido->ped_fec,
                                            'descripcion' => 'Salida por Combo: '.$bus_pro->pronom.' - Receta de: '.$bus_pro_combo->pronom.' - Insumo: '.$bus_insumo->pronom.' (Pedido ID: ' . $ped_id . ')',
                                        ]);

                                        $stock_prod_ins = DB::table('producto_stock')
                                            ->where('IdProducto', $rec->prod_insu)
                                            ->where('id_almacen', $bus_alm->id_almacen)
                                            ->first();

                                        if ($stock_prod_ins) {
                                            $nuevo_stock = $stock_prod_ins->stock - $cantidad_principal;
                                            $nuevo_stock_equivalente = $stock_prod_ins->stock_equivalencia - $cantidad_equivalente;
                                            
                                            DB::table('producto_stock')
                                                ->where('pro_sto_id', $stock_prod_ins->pro_sto_id)
                                                ->update([
                                                    'stock' => $nuevo_stock,
                                                    'stock_equivalencia' => $nuevo_stock_equivalente
                                                ]);
                                        }
                                    }
                                }

                            }
                            // Si el ítem del combo es un INSUMO (promocion = 4)
                            elseif($bus_pro_combo->promocion == '4'){
                                
                                $cantidad_principal = ($det->ped_det_can * $combo_item->prod_comb_cant) * $bus_pro_combo->factor;
                                $cantidad_equivalente = 0;
                                
                                if(!empty($bus_pro_combo->factor_cons) && $bus_pro_combo->factor_cons > 0){
                                    $cantidad_equivalente = $cantidad_principal * $bus_pro_combo->factor_cons;
                                }

                                DB::table('movimientos_productos')->insert([
                                    'IdProducto' => $combo_item->IdProducto_comb,
                                    'IdProducto_rel' => $id_prod_combo,
                                    'precio' => $combo_item->prod_comb_prec,
                                    'cantidad' => $cantidad_principal,
                                    'cantidad_equivalente' => $cantidad_equivalente,
                                    'costo' => $combo_item->prod_comb_cost,
                                    'cliente' => $pedido->ped_cli_nom,
                                    'IdCpe_cabecera' => null,
                                    'serie' => null,
                                    'numero' => null,
                                    'tdocod' => null,
                                    'tipo' => '3',
                                    'mov_tip' => 'E',
                                    'stock_equivalente' => $cantidad_equivalente,
                                    'id_empresa_negocio' => $pedido->id_empresa_negocio,
                                    'id_almacen' => $bus_alm->id_almacen,
                                    'fecha_mov' => $pedido->ped_fec,
                                    'descripcion' => 'Salida por Combo: '.$bus_pro->pronom.' - Insumo: '.$bus_pro_combo->pronom.' (Pedido ID: ' . $ped_id . ')',
                                ]);

                                $stock_prod = DB::table('producto_stock')
                                    ->where('IdProducto', $id_prod_combo)
                                    ->where('id_almacen', $bus_alm->id_almacen)
                                    ->first();

                                if ($stock_prod) {
                                    $nuevo_stock = $stock_prod->stock - $cantidad_principal;
                                    $nuevo_stock_equivalente = $stock_prod->stock_equivalencia - $cantidad_equivalente;
                                    
                                    DB::table('producto_stock')
                                        ->where('pro_sto_id', $stock_prod->pro_sto_id)
                                        ->update([
                                            'stock' => $nuevo_stock,
                                            'stock_equivalencia' => $nuevo_stock_equivalente
                                        ]);
                                }

                            }

                        } // Fin foreach combo_item
                    }

                } // Fin promocion = 3

            } // Fin foreach detalle_pedido
        }


        // Nueva función para ajustar stock cuando se actualiza una comanda
        public function ajustar_stock_por_comanda($producto_id, $cantidad_cambio, $id_empresa_negocio){
            $bus_pro = productos::findOrFail($producto_id);
            $bus_alm = DB::table('almacenes')->where('id_empresa_negocio', $id_empresa_negocio)->where('predeterminado','1')->first();

            $id_prod = empty($bus_pro->pro_rel) ? $bus_pro->IdProducto : $bus_pro->pro_rel;

            if($bus_pro->promocion == '0'){
                $stock_prod = DB::table('producto_stock')
                    ->where('IdProducto', $id_prod)
                    ->where('id_almacen', $bus_alm->id_almacen)
                    ->first();

                if ($stock_prod) {
                    DB::table('producto_stock')->where('pro_sto_id', $stock_prod->pro_sto_id)->update(['stock' => $stock_prod->stock - ($cantidad_cambio * $bus_pro->factor)]);
                }
            } 
            elseif($bus_pro->promocion == '2'){
                $bus_receta = DB::table('recetas')->where('prod_id', $id_prod)->get();

                if(count($bus_receta) > 0){
                    foreach($bus_receta as $rec){
                        $stock_prod_ins = DB::table('producto_stock')
                            ->where('IdProducto', $rec->prod_insu)
                            ->where('id_almacen', $bus_alm->id_almacen)
                            ->first();

                        if ($stock_prod_ins) {
                            DB::table('producto_stock')->where('pro_sto_id', $stock_prod_ins->pro_sto_id)->update(['stock' => $stock_prod_ins->stock - ($cantidad_cambio * $rec->rec_cant)]);
                        }
                    }
                }
            } 
            elseif($bus_pro->promocion == '3'){
                $bus_combo_items = DB::table('combos')->where('IdProducto_rel', $id_prod)->get();

                foreach($bus_combo_items as $combo_item){
                    $prod_combo = productos::findOrFail($combo_item->IdProducto_comb);
                    $id_prod_combo = empty($prod_combo->pro_rel) ? $prod_combo->IdProducto : $prod_combo->pro_rel;

                    if($prod_combo->promocion == '0'){
                        $stock_prod_combo = DB::table('producto_stock')
                            ->where('IdProducto', $id_prod_combo)
                            ->where('id_almacen', $bus_alm->id_almacen)
                            ->first();

                        if ($stock_prod_combo) {
                            DB::table('producto_stock')->where('pro_sto_id', $stock_prod_combo->pro_sto_id)->update(['stock' => $stock_prod_combo->stock - ($cantidad_cambio * $combo_item->prod_comb_cant)]);
                        }
                    } 
                    elseif($prod_combo->promocion == '2'){
                        $bus_receta_combo_item = DB::table('recetas')->where('prod_id', $id_prod_combo)->get();

                        if(count($bus_receta_combo_item) > 0){
                            foreach($bus_receta_combo_item as $rec_combo_item){
                                $stock_prod_rec_combo = DB::table('producto_stock')
                                    ->where('IdProducto', $rec_combo_item->prod_insu)
                                    ->where('id_almacen', $bus_alm->id_almacen)
                                    ->first();

                                if ($stock_prod_rec_combo) {
                                    DB::table('producto_stock')->where('pro_sto_id', $stock_prod_rec_combo->pro_sto_id)->update(['stock' => $stock_prod_rec_combo->stock - ($cantidad_cambio * $combo_item->prod_comb_cant * $rec_combo_item->rec_cant)]);
                                }
                            }
                        }
                    }
                }
            }
        }



public function registrar_movimiento_salida($id){

        $cabecera = cpe_cabecera::findOrFail($id);
        $detalle = cpe_detalle::where('IdCpe_cabecera',$id)->get();

        // PRE-CARGAR todos los productos del detalle para reducir consultas
        $productos_ids = $detalle->pluck('IdProducto')->toArray();
        $productos_cache = productos::whereIn('IdProducto', $productos_ids)->get()->keyBy('IdProducto');

        foreach($detalle as $det){

            $bus_pro = $productos_cache[$det->IdProducto];

            if(empty($bus_pro->pro_rel)){
                $id_prod = $bus_pro->IdProducto;
            }else{
                $id_prod = $bus_pro->pro_rel;
            }

            // ========== PROMOCION = 0 (PRODUCTOS SIMPLES) ==========
            if($bus_pro->promocion =='0'){

                $cantidad_principal = $det->cdecan * $det->cpe_det_factor;
                $cantidad_equivalente = 0;
                
                if(!empty($bus_pro->factor_cons) && $bus_pro->factor_cons > 0){
                    $cantidad_equivalente = $cantidad_principal * $bus_pro->factor_cons;
                }

                // 1. Consultar Stock Actual ANTES del movimiento
                $stock_prod = DB::table('producto_stock')
                    ->where('IdProducto', $id_prod)
                    ->where('id_almacen', $cabecera->id_almacen)
                    ->first();

                $stock_actual_base = $stock_prod ? $stock_prod->stock : 0;
                $stock_actual_equiv = $stock_prod ? $stock_prod->stock_equivalencia : 0;

                // 2. Calcular los Nuevos Saldos
                $nuevo_stock_base = $stock_actual_base - $cantidad_principal;
                $nuevo_stock_equiv = $stock_actual_equiv - $cantidad_equivalente;

                // 3. Registrar en el Kardex con los saldos restantes
                DB::table('movimientos_productos')->insert([
                    'IdProducto'=>$det->IdProducto,
                    'IdProducto_rel'=>$id_prod,
                    'precio'=>$det->cdepuni,
                    'cantidad'=>$cantidad_principal,
                    'cantidad_equivalente'=>$cantidad_equivalente,
                    'costo'=>$det->costo,
                    'cliente'=>$cabecera->ccanom,
                    'IdCpe_cabecera'=>$cabecera->IdCpe_cabecera,
                    'serie'=>$cabecera->serdoc,
                    'numero'=>$cabecera->numdoc,
                    'tdocod'=>$cabecera->tdocod,
                    'tipo'=>'3',
                    'mov_tip'=>'E',
                    'stock'=>$nuevo_stock_base, // Guardamos el saldo principal
                    'stock_equivalente'=>$nuevo_stock_equiv, // Guardamos el saldo equivalente
                    'id_empresa_negocio'=>$cabecera->id_empresa_negocio,
                    'id_almacen'=>$cabecera->id_almacen,
                    'fecha_mov'=>$cabecera->ccafem
                ]);

                // 4. Actualizar la tabla de Stock
                if($stock_prod){
                    DB::table('producto_stock')
                        ->where('pro_sto_id', $stock_prod->pro_sto_id)
                        ->update([
                            'stock' => $nuevo_stock_base,
                            'stock_equivalencia' => $nuevo_stock_equiv
                        ]);
                }

            } 
            // ========== PROMOCION = 2 (PREPARADOS CON RECETA) ==========
            elseif($bus_pro->promocion =='2'){

                $bus_receta = DB::table('recetas')->where('prod_id', $id_prod)->get();

                if(count($bus_receta) > 0){
                    
                    $insumos_ids = $bus_receta->pluck('prod_insu')->toArray();
                    $insumos_cache = productos::whereIn('IdProducto', $insumos_ids)->get()->keyBy('IdProducto');
            
                    foreach($bus_receta as $rec){

                        $bus_insumo = $insumos_cache[$rec->prod_insu];
                        
                        $cantidad_equivalente = $det->cdecan * $rec->rec_cant;
                        $cantidad_principal = 0;

                        if(!empty($bus_insumo->factor_cons) && $bus_insumo->factor_cons > 0){
                            $cantidad_principal = $cantidad_equivalente / $bus_insumo->factor_cons;
                        }

                        // 1. Consultar Stock del Insumo
                        $stock_prod_ins = DB::table('producto_stock')
                            ->where('IdProducto', $rec->prod_insu)
                            ->where('id_almacen', $cabecera->id_almacen)
                            ->first();

                        $stock_actual_base = $stock_prod_ins ? $stock_prod_ins->stock : 0;
                        $stock_actual_equiv = $stock_prod_ins ? $stock_prod_ins->stock_equivalencia : 0;

                        $nuevo_stock_base = $stock_actual_base - $cantidad_principal;
                        $nuevo_stock_equiv = $stock_actual_equiv - $cantidad_equivalente;

                        // 2. Registrar en el Kardex
                        DB::table('movimientos_productos')->insert([
                            'IdProducto'=>$rec->prod_insu,
                            'IdProducto_rel'=>$rec->prod_insu,
                            'precio'=>'0',
                            'cantidad'=>$cantidad_principal,
                            'cantidad_equivalente'=>$cantidad_equivalente,
                            'costo'=>$rec->ins_costo,
                            'cliente'=>$cabecera->ccanom,
                            'IdCpe_cabecera'=>$cabecera->IdCpe_cabecera,
                            'serie'=>$cabecera->serdoc,
                            'numero'=>$cabecera->numdoc,
                            'tdocod'=>$cabecera->tdocod,
                            'tipo'=>'3',
                            'mov_tip'=>'E',
                            'stock'=>$nuevo_stock_base,
                            'stock_equivalente'=>$nuevo_stock_equiv,
                            'id_empresa_negocio'=>$cabecera->id_empresa_negocio,
                            'id_almacen'=>$cabecera->id_almacen,
                            'fecha_mov'=>$cabecera->ccafem,
                        ]);

                        // 3. Actualizar la tabla de Stock
                        if($stock_prod_ins){
                            DB::table('producto_stock')
                                ->where('pro_sto_id', $stock_prod_ins->pro_sto_id)
                                ->update([
                                    'stock' => $nuevo_stock_base,
                                    'stock_equivalencia' => $nuevo_stock_equiv
                                ]);
                        }
                    }
                }

            } 
            // ========== PROMOCION = 3 (COMBOS) ==========
            elseif($bus_pro->promocion =='3'){

                $bus_combo = DB::table('combos')->where('IdProducto_comb', $id_prod)->get();

                if(count($bus_combo) > 0){

                    $combo_productos_ids = $bus_combo->pluck('IdProducto_rel')->toArray();
                    $combo_productos_cache = productos::whereIn('IdProducto', $combo_productos_ids)->get()->keyBy('IdProducto');

                    foreach($bus_combo as $combo_item){

                        $bus_pro_item = $combo_productos_cache[$combo_item->IdProducto_rel];

                        if(empty($bus_pro_item->pro_rel)){
                            $id_prod_item = $bus_pro_item->IdProducto;
                        }else{
                            $id_prod_item = $bus_pro_item->pro_rel;
                        }

                        // ===== CASO 1 Y 3: PRODUCTO SIMPLE (0) O INSUMO (4) =====
                        if($bus_pro_item->promocion =='0' || $bus_pro_item->promocion =='4'){

                            $cantidad_principal = ($det->cdecan * $combo_item->prod_comb_cant) * $bus_pro_item->factor;
                            $cantidad_equivalente = 0;
                            
                            if(!empty($bus_pro_item->factor_cons) && $bus_pro_item->factor_cons > 0){
                                $cantidad_equivalente = $cantidad_principal * $bus_pro_item->factor_cons;
                            }

                            $stock_prod = DB::table('producto_stock')
                                ->where('IdProducto', $id_prod_item)
                                ->where('id_almacen', $cabecera->id_almacen)
                                ->first();

                            $stock_actual_base = $stock_prod ? $stock_prod->stock : 0;
                            $stock_actual_equiv = $stock_prod ? $stock_prod->stock_equivalencia : 0;

                            $nuevo_stock_base = $stock_actual_base - $cantidad_principal;
                            $nuevo_stock_equiv = $stock_actual_equiv - $cantidad_equivalente;

                            DB::table('movimientos_productos')->insert([
                                'IdProducto'=>$combo_item->IdProducto_rel,
                                'IdProducto_rel'=>$id_prod_item,
                                'precio'=>$combo_item->prod_comb_prec,
                                'cantidad'=>$cantidad_principal,
                                'cantidad_equivalente'=>$cantidad_equivalente,
                                'costo'=>$combo_item->prod_comb_cost,
                                'cliente'=>$cabecera->ccanom,
                                'IdCpe_cabecera'=>$cabecera->IdCpe_cabecera,
                                'serie'=>$cabecera->serdoc,
                                'numero'=>$cabecera->numdoc,
                                'tdocod'=>$cabecera->tdocod,
                                'tipo'=>'3',
                                'mov_tip'=>'E',
                                'stock'=>$nuevo_stock_base,
                                'stock_equivalente'=>$nuevo_stock_equiv,
                                'id_empresa_negocio'=>$cabecera->id_empresa_negocio,
                                'id_almacen'=>$cabecera->id_almacen,
                                'fecha_mov'=>$cabecera->ccafem,
                                'descripcion'=>'Combo',
                            ]);

                            if($stock_prod){
                                DB::table('producto_stock')
                                    ->where('pro_sto_id', $stock_prod->pro_sto_id)
                                    ->update([
                                        'stock' => $nuevo_stock_base,
                                        'stock_equivalencia' => $nuevo_stock_equiv
                                    ]);
                            }

                        } 
                        // ===== CASO 2: PRODUCTO PREPARADO (2) DENTRO DEL COMBO =====
                        elseif($bus_pro_item->promocion =='2'){

                            $bus_receta = DB::table('recetas')->where('prod_id', $id_prod_item)->get();

                            if(count($bus_receta) > 0){
                                
                                $receta_insumos_ids = $bus_receta->pluck('prod_insu')->toArray();
                                $receta_insumos_cache = productos::whereIn('IdProducto', $receta_insumos_ids)->get()->keyBy('IdProducto');
                        
                                foreach($bus_receta as $rec){

                                    $bus_insumo = $receta_insumos_cache[$rec->prod_insu];
                                    
                                    $cantidad_equivalente = ($det->cdecan * $combo_item->prod_comb_cant) * $rec->rec_cant;
                                    $cantidad_principal = 0;

                                    if(!empty($bus_insumo->factor_cons) && $bus_insumo->factor_cons > 0){
                                        $cantidad_principal = $cantidad_equivalente / $bus_insumo->factor_cons;
                                    }

                                    $stock_prod_ins = DB::table('producto_stock')
                                        ->where('IdProducto', $rec->prod_insu)
                                        ->where('id_almacen', $cabecera->id_almacen)
                                        ->first();

                                    $stock_actual_base = $stock_prod_ins ? $stock_prod_ins->stock : 0;
                                    $stock_actual_equiv = $stock_prod_ins ? $stock_prod_ins->stock_equivalencia : 0;

                                    $nuevo_stock_base = $stock_actual_base - $cantidad_principal;
                                    $nuevo_stock_equiv = $stock_actual_equiv - $cantidad_equivalente;

                                    DB::table('movimientos_productos')->insert([
                                        'IdProducto'=>$rec->prod_insu,
                                        'IdProducto_rel'=>$rec->prod_insu,
                                        'precio'=>'0',
                                        'cantidad'=>$cantidad_principal,
                                        'cantidad_equivalente'=>$cantidad_equivalente,
                                        'costo'=>$rec->ins_costo,
                                        'cliente'=>$cabecera->ccanom,
                                        'IdCpe_cabecera'=>$cabecera->IdCpe_cabecera,
                                        'serie'=>$cabecera->serdoc,
                                        'numero'=>$cabecera->numdoc,
                                        'tdocod'=>$cabecera->tdocod,
                                        'tipo'=>'3',
                                        'mov_tip'=>'E',
                                        'stock'=>$nuevo_stock_base,
                                        'stock_equivalente'=>$nuevo_stock_equiv,
                                        'id_empresa_negocio'=>$cabecera->id_empresa_negocio,
                                        'id_almacen'=>$cabecera->id_almacen,
                                        'fecha_mov'=>$cabecera->ccafem,
                                        'descripcion'=>'Combo (Receta)',
                                    ]);

                                    if($stock_prod_ins){
                                        DB::table('producto_stock')
                                            ->where('pro_sto_id', $stock_prod_ins->pro_sto_id)
                                            ->update([
                                                'stock' => $nuevo_stock_base,
                                                'stock_equivalencia' => $nuevo_stock_equiv
                                            ]);
                                    }
                                }
                            }
                        }
                    } 
                } 
            } 
        } 
    }



public function imprimir($cpe,$tipdoc){
    
    $rucemp = Auth::user()->IdEmpresa;

    $empresa = Empresa::findOrFail($rucemp);

    $empresanegocios = EmpresaNegocios::findOrFail(Auth::user()->id_empresa_negocio);

    $nomdoc = DB::tABLE('tipo_documento')->where('tdocod',$tipdoc)->first();
    
    $pedido = "";
    
    if($tipdoc == '01' || $tipdoc == '03' || $tipdoc == '13' || $tipdoc == '07' || $tipdoc == '99'){
        $cabecera = DB::tABLE('cpe_cabecera as cab')
        ->leftjoin('cliente as cli','cab.ccandi','=','cli.clinum')
        ->leftjoin('tipo_documento as tdo','cab.tdocod','=','tdo.tdocod')
        ->leftjoin('tipo_documento_identidad as tdi','cab.tdicod','=','tdi.tdicod')
        ->leftjoin('moneda as mon','cab.moncod','=','mon.moncod')
        ->leftjoin('tipo_operacion as top','cab.topcod','=','top.topcod')
        ->where('IdCpe_cabecera','=',$cpe)
        ->first();

        $bus_vendedor = DB::tABLE('cpe_cabecera')->where('IdCpe_cabecera',$cpe)->first();
        
        $cuotas = DB::tABLE('ventas_cuotas')->where('IdCpe_cabecera',$cpe)->get();
        
        $totalletras= MontoLetras::convertir(number_format($cabecera->ccaitv,'2','.',''),$cabecera->monnom,'Centimos');
        
        $detalle=DB::tABLE('cpe_detalle as det')
        ->leftjoin('unidad_medida as umed','det.umecod','=','umed.umecod')
        ->where('IdCpe_cabecera','=',$cpe)->get();

        $sucursal = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first();

        if(!empty($cabecera->ped_id)){
            $pedido = DB::tABLE('pedidos')->select('pedidos.mes_id','mesas.mes_nom','motorizado','pedidos.mozo','users.name','users.apeusu')
            ->leftjoin('users','users.IdUsuario','pedidos.mozo')
            ->leftjoin('mesas','mesas.mes_id','pedidos.mes_id')
            ->where('ped_id',$cabecera->ped_id)->first();
        }
    }

    $impresoras = DB::tABLE('configuracion_impresoras')->where('Id',Auth::user()->terminal)->first();

    $medios = DB::tABLE('venta_medio_pago')
    ->join('medios_pagos','medios_pagos.id_med_pag','venta_medio_pago.id_med_pag')
    ->where('IdCpe_cabecera',$cpe)
    ->get();



    try{ 


      //IMPRESORA REAL
     /*if($impresoras->tip_conex_imp=='COMPARTIDO'){
        $connector = new WindowsPrintConnector("smb://".$impresoras->ruta);
    }elseif($impresoras->tip_conex_imp=='RED'){
        $connector = new NetworkPrintConnector($impresoras->ruta,9100);
    }*/

    //IMPRESORA VIRTUAL
    $connector = new DummyPrintConnector();
    $printer = new Printer($connector);


    
    $printer = new Printer($connector);
    $printer->setJustification(Printer::JUSTIFY_CENTER);
    
    if($cabecera->tdocod !='99'){
        if(file_exists($empresanegocios->logosuc)){
          $logo = EscposImage::load(public_path().'/'.$empresanegocios->logosuc,false);
          $printer->bitImage($logo);
      }

            //NOMBRE COMERCIAL

      $printer->setFont(Printer::FONT_B);
      $printer->setTextSize(1,1);
      //$printer->text($empresanegocios->nombre_comercial."\n");

             //NOMBRE EMPRESA
      $printer->text($empresa->NomEmpresa."\n");
            //$printer->text($empresanegocios->NomEmpresa."\n");

      $printer->setFont(Printer::FONT_A);
      $printer->setTextSize(1,1);

            //DIRECCION DE LA EMPRESA
      $printer->text($empresanegocios->direccion."\n");

            //UBIGEO DEL CLIENTE DEPARTAMENTO-PROVINCIA-DISTRITO
      $printer->text($empresanegocios->departamento."-".$empresanegocios->provincia."-".$empresanegocios->distrito."\n"."\n");

            //TELEFONO DEL CLIENTE
      if(!empty($empresanegocios->telefono)){
       $printer->text($empresanegocios->telefono."\n"."\n");   
   }
   
            //RUC DE LA EMPRESA
   $printer->text('RUC:'.$empresa->IdEmpresa."\n");
}

$printer->setFont(Printer::FONT_A);
      $printer->setTextSize(1,1); 

        //NOMBRE DEL TIPO DE COMPROBANTE
$printer->text($nomdoc->tdodes."\n");

        //SERIE Y NUMERO DE COMPROBANTE (serdoc=serie  y numdoc=numero)
$printer->text($cabecera->serdoc."-".$cabecera->numdoc."\n"."\n");

        //NOMBRE DEL VENDEDOR
        //$printer->text("Vendedor: ".$bus_vendedor->vendedor."\n"."\n");

        //DATOS DEL CLIENTE
$printer->setJustification(Printer::JUSTIFY_LEFT);
$printer->text("Fecha:       ".$cabecera->fecha_hora."\n");
$printer->text("RUC/DNI:     ".$cabecera->clinum."\n");
$printer->text("Cliente:     ".$cabecera->ccanom."\n");
$printer->text("Dirección:   ".$cabecera->direccion."\n");
if(!empty($pedido->mes_nom)){
    $printer->text("Mesa:        ".$pedido->mes_nom."\n");  
}

if(!empty($pedido->name)){
    $printer->text("Mozo:  ". $pedido->name.' '.$pedido->apeusu."\n");
}

$printer->text("Condición de Pago:   ".$cabecera->estadopago."\n"."\n");

        //DETALLE DE LOS PRODUCTOS QUE SE VENDEN
$printer->setJustification(Printer::JUSTIFY_LEFT);
        //$printer->text("CODIGO    DESCRIPCION "."\n");
        //$printer->text("CANTIDAD    UNIDAD    PRECIO    TOTAL"."\n");
$printer->text("________________________________________"."\n");

$printer->text("Descripción  Cant.    UDM.   P.U   Total"."\n");
$printer->text("________________________________________"."\n");
if($cabecera->consumo=='1'){
    $printer->text("POR CONSUMO "."\n");
    $printer->text("1"."    "."NIU"."     ".$cabecera->ccaitv."    ".$cabecera->ccaitv."\n\n");

}else{
    foreach ($detalle as $det){
        if($det->cdevve > 0){
            $printer->text($det->cdedes."\n");
            $printer->text("           ".$det->cdecan."  ".$det->umecod."   ".$det->cdepuni."    ".$det->cdevve."\n");
        }else{
            $printer->text($det->cdedes."\n");
        } 
    }
}

        //$printer->text("\n");
$printer->text("________________________________________"."\n");

        //MEDIOS DE PAGO
foreach ($medios as $m) {
    $printer->text($m->nom_med_pag." ".$cabecera->simbolo."                        ".$m->monto."\n");
}

if($cabecera->tdocod !='99'){  
            //$printer->text("SUBTOTAL: ".$cabecera->simbolo."                       ".$cabecera->ccatvg."\n");
            //$printer->text("OP. GRAVADA: ".$cabecera->simbolo."                    ".$cabecera->ccatvg."\n");

            //$printer->text("OP. EXONERADA: ".$cabecera->simbolo."                  ".$cabecera->ccatexo."\n");

            // $printer->text("OP. INAFECTA: ".$cabecera->simbolo."         "."0.00"."\n");

            //$printer->text("IGV 18%: ".$cabecera->simbolo."                        ".$cabecera->ccaigv."\n");

            //$printer->text("ICBPER: ".$cabecera->simbolo."                         ".$cabecera->icbper."\n");
}
//$printer->text("ICBPER: ".$cabecera->simbolo."                         ".$cabecera->icbper."\n");
$printer->text("TOTAL: ".$cabecera->simbolo."                          ".$cabecera->ccaitv."\n"."\n");
if ($cabecera->paga!=0) {
    $printer->text("PAGA CON: ".$cabecera->simbolo."                       ".$cabecera->paga."\n");
}

if ($cabecera->vuelto!=0) {
    $printer->text("VUELTO: ".$cabecera->simbolo."                         ".$cabecera->vuelto."\n"."\n");
}

$printer->setFont(Printer::FONT_B);
$printer->setTextSize(1,1);

$printer->text("Son: ".$totalletras." ".$cabecera->monnom."\n");
$printer->setJustification(Printer::JUSTIFY_CENTER);
$printer->text("REPRESENTACIÓN IMPRESA DE LA ".$cabecera->tdodes."\n");

if($cabecera->tdocod=='01' || $cabecera->tdocod=='03'){
    $printer->setJustification(Printer::JUSTIFY_CENTER);
    $logo1 = EscposImage::load(public_path().'/qr/QR-'.$rucemp.'-'.$cabecera->tdocod.'-'.$cabecera->serdoc.'-'.$cabecera->numdoc.'.png',false);
    $printer->bitImage($logo1);
}
if($cabecera->tdocod !='99'){  
    $printer->text("BIENES TRANSFERIDOS EN LA AMAZONIA PARA SER CONSUMIDOS EN LA MISMA. SERVICIOS PRESTADOS EN LA AMAZONIA"."\n");
    $printer->text("SISTEMA DESARROLLADO POR HOLAPE - 928 396 147"."\n");
    $printer->setJustification(Printer::JUSTIFY_CENTER);
                //$printer->text("\n".$empresanegocios->pie."\n");
}

$printer->setFont(Printer::FONT_B);
$printer->setTextSize(2,2);
// =================================================================
// INICIO: IMPRESIÓN DE PUNTOS EN TICKET
// =================================================================
/*if($cabecera->clinum != '00000000'){
    
    // 1. Buscar si en ESTA boleta se entregaron premios
    $canjes_en_venta = DB::table('puntos_historial')
                        ->where('venta_id', $cpe)
                        ->where('puntos_canjeados', '>', 0)
                        ->get();

    if(count($canjes_en_venta) > 0){
        $printer->text("\n");
        $printer->text("------------------------------\n");
        $printer->setJustification(Printer::JUSTIFY_CENTER);
        $printer->text("** FELICIDADES, HAS CANJEADO **\n");
        foreach($canjes_en_venta as $canje){
            // Imprime "CANJE DE PREMIO: MOCHILA"
            $printer->text(strtoupper($canje->motivo) . "\n"); 
        }
        $printer->text("------------------------------\n");
    }
    
    // 2. Imprimir el saldo restante
    $cliente_puntos = DB::table('cliente')->where('clinum', $cabecera->clinum)->first();
    if($cliente_puntos){
        $printer->text("\n");
        $printer->setJustification(Printer::JUSTIFY_CENTER);
        $printer->text("TIENES " . $cliente_puntos->puntos . " PUNTOS ACUMULADOS\n");
        $printer->text("¡Sigue juntando en HOLAPE y gana más!\n");
        $printer->text("------------------------------\n");
    }
}*/
// =================================================================
// FIN: IMPRESIÓN DE PUNTOS
// =================================================================

$printer->feed();


$printer->cut();


$printer->pulse();

//IMPRESORA VIRTUAL
$codigo_raw = $connector->getData();

        
          $printer->close();

          //IMPRESORA VIRTUAL

          DB::table('cola_impresion')->insert([
            'contenido' => base64_encode($codigo_raw),
            'impresora' => $impresoras->descripcion, // "CPE"
            'estado'    => '0'            
        ]);

      }catch (\Exception $e) {
         
          dd($e);

      }
  }


 
  
      public function imprimir_comanda($pedido){

           $IdEmpresa = Auth::user()->IdEmpresa;

           $empresanegocios = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first();

           $cab_pedido = DB::tABLE('pedidos')
           ->where('ped_id',$pedido)
           ->leftjoin('users','users.IdUsuario','pedidos.mozo')
           ->first();

           $mesa = DB::tABLE('mesas')->where('mes_id',$cab_pedido->mes_id)->first();

           $impresoras = DB::tABLE('configuracion_impresoras')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->get();

 
           foreach ($impresoras as $impresora) {
                
   

                $detalle = DB::tABLE('pedidos_detalle')->where('ped_id',$pedido)
               ->join('productos','pedidos_detalle.IdProducto','productos.IdProducto')
               ->leftjoin('categorias','categorias.cat_id','productos.cat_id')
               ->where('categorias.id_empresa_negocio',Auth::user()->id_empresa_negocio)
               ->where('pedidos_detalle.impreso','imprimir')
               ->where('categorias.impresora',$impresora->Id)
               ->get();

            
            try{

                 if(count($detalle) >0){


                    if($impresora->tip_conex_imp=='COMPARTIDO'){
                        $connector = new WindowsPrintConnector("smb://".$impresora->ruta);
                    }elseif($impresora->tip_conex_imp=='RED'){
                        $connector = new NetworkPrintConnector($impresora->ruta,9100);
                    }
                    
                    $printer = new Printer($connector);
                    //$printer->setTextSize(2,1);
                    $printer->setTextSize(1,1);

                    $printer->setJustification(Printer::JUSTIFY_CENTER);
                    $printer->setFont(Printer::FONT_B);
                    $printer->setTextSize(2,2);

                    $printer->text($cab_pedido->etiqueta."\n");
                    $printer->text($cab_pedido->ped_id."\n");

                    //$printer->text("Tipo ".$cab_pedido->ped_tip."\n");
                    $printer->text($cab_pedido->ped_tip."\n");

                    $printer->setFont(Printer::FONT_B);
                    $printer->setTextSize(3,3);

                    if(!empty($cab_pedido->mes_id)){
                        $printer->text($mesa->mes_nom."\n");
                    }

                    $printer->setFont(Printer::FONT_B);
                    $printer->setTextSize(1,1);

                    if(!empty($cab_pedido->ped_cli_nom)){
                        $printer->text("Cliente: ".$cab_pedido->ped_cli_nom."\n");
                    }

                    if(!empty($cab_pedido->ped_dir)){
                        $printer->text("Direccion: ".$cab_pedido->ped_dir."\n");
                    }

                    if(!empty($cab_pedido->ped_tel)){
                        $printer->text("Telefono: ".$cab_pedido->ped_tel."\n");
                    }

                    if(!empty($cab_pedido->ped_ref)){
                        $printer->text("Referencia: ".$cab_pedido->ped_ref."\n");
                    }

                    if(empty($cab_pedido->etiqueta)){
                        $printer->text("Fecha:". $cab_pedido->fecha_hora."\n");
                    }else{
                        $printer->text("Fecha:". $cab_pedido->fecha_hora_modificacion."\n"."\n");
                    }
                    
                    if(!empty($cab_pedido->mozo)){
                        $printer->text("Mozo:". $cab_pedido->name.' '.$cab_pedido->apeusu."\n"."\n");
                    }
                   
                   
                    $printer->setJustification(Printer::JUSTIFY_LEFT);
                    
                    
                    $printer->setJustification(Printer::JUSTIFY_LEFT);
                    $printer->text("CONCEPTO    CANTIDAD       OBS."."\n");
                    $printer->text("__________________________"."\n");

                    $printer->setFont(Printer::FONT_B);
                    $printer->setTextSize(2,2);
                  
                    foreach ($detalle as $det) {
                     
                       $primeralinea = ($det->descripcion);
                       //$segundalinea = str_pad(substr($det->descripcion,18,34),17," ",STR_PAD_RIGHT);
                       if($det->mod_cant > 0){
                         $printer->text($det->mod_cant."  ".$primeralinea."   ".$det->detalle."  ".$det->item_obs."\n");

                         //$printer->text($primeralinea."   ".$det->mod_cant."  ".$det->detalle."  ".$det->item_obs."\n");
                        
                       }else{

                        $printer->text($det->ped_det_can."  ".$primeralinea."   ".$det->detalle."  ".$det->item_obs."\n");
                          //$printer->text($primeralinea."   ".$det->ped_det_can."  ".$det->detalle."  ".$det->item_obs."\n");   
                       }
                      
                       //$printer->text($segundalinea."\n");

                        

                       $buscardetalle = pedidos_detalle::findOrFail($det->ped_det_id);
                       $buscardetalle->impreso ='impreso';
                       $buscardetalle->mod_cant ='0';
                       $buscardetalle->update();
                    }
                    
                    $printer->text("\n");
                    $printer->feed();
                    $printer->cut();
                    $printer->pulse();
                    $printer->close();

                  }

                 $buscarpedido = DB::tABLE('pedidos')
                ->where('ped_id',$pedido)
                ->update(['etiqueta' => ""]); 

             
             }catch(\Exception $e){
                dd($e);
            }
          
             

              }

           


    }


    public function imprimir_comanda_total($pedido){

           $IdEmpresa = Auth::user()->IdEmpresa;

           $empresanegocios = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first();

           $cab_pedido = DB::tABLE('pedidos')
           ->where('ped_id',$pedido)
           ->leftjoin('users','users.IdUsuario','pedidos.mozo')
           ->first();

           $mesa = DB::tABLE('mesas')->where('mes_id',$cab_pedido->mes_id)->first();

           $impresoras = DB::tABLE('configuracion_impresoras')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->get();


           foreach ($impresoras as $impresora) {
                

                $detalle = DB::tABLE('pedidos_detalle')->where('ped_id',$pedido)
               ->join('productos','pedidos_detalle.IdProducto','productos.IdProducto')
               ->leftjoin('categorias','categorias.cat_id','productos.cat_id')
               ->where('categorias.id_empresa_negocio',Auth::user()->id_empresa_negocio)
               ->where('categorias.impresora',$impresora->Id)
               ->get();

       
                try{

                      if(count($detalle) >0){



                          if($impresora->tip_conex_imp=='COMPARTIDO'){
                        $connector = new WindowsPrintConnector("smb://".$impresora->ruta);
                    }elseif($impresora->tip_conex_imp=='RED'){
                        $connector = new NetworkPrintConnector($impresora->ruta,9100);
                    }
                    
                    $printer = new Printer($connector);
                    $printer->setTextSize(2,1);

                    $printer->setJustification(Printer::JUSTIFY_CENTER);
                    $printer->setFont(Printer::FONT_A);

                    $printer->text($cab_pedido->etiqueta."\n");
                    $printer->text("Pedido ".$cab_pedido->ped_id."\n");

                    $printer->text("Tipo ".$cab_pedido->ped_tip."\n");

                    if(!empty($cab_pedido->mes_id)){
                        $printer->text($mesa->mes_nom."\n");
                    }

                    if(!empty($cab_pedido->ped_cli_nom)){
                        $printer->text("Cliente: ".$cab_pedido->ped_cli_nom."\n");
                    }

                    if(!empty($cab_pedido->ped_dir)){
                        $printer->text("Direccion: ".$cab_pedido->ped_dir."\n");
                    }

                    if(!empty($cab_pedido->ped_tel)){
                        $printer->text("Telefono: ".$cab_pedido->ped_tel."\n");
                    }

                    if(!empty($cab_pedido->ped_ref)){
                        $printer->text("Referencia: ".$cab_pedido->ped_ref."\n");
                    }

                    if(empty($cab_pedido->etiqueta)){
                        $printer->text("Fecha:". $cab_pedido->fecha_hora."\n");
                    }else{
                        $printer->text("Fecha:". $cab_pedido->fecha_hora_modificacion."\n"."\n");
                    }
                    
                    if(!empty($cab_pedido->mozo)){
                        $printer->text("Mozo:". $cab_pedido->name.' '.$cab_pedido->apeusu."\n"."\n");
                    }
                   
                   
                    $printer->setJustification(Printer::JUSTIFY_LEFT);
                    
                    
                    $printer->setJustification(Printer::JUSTIFY_LEFT);
                    $printer->text("CONCEPTO    CANTIDAD       OBS."."\n");
                    $printer->text("_________________________________"."\n");
                    foreach ($detalle as $det) {
                     
                       $primeralinea = str_pad(substr($det->pronom,0,17),17," ",STR_PAD_RIGHT);
                       $segundalinea = str_pad(substr($det->pronom,18,34),17," ",STR_PAD_RIGHT);
                       $printer->text($primeralinea."   ".$det->ped_det_can."  ".$det->detalle."\n");
                       $printer->text($segundalinea."\n");


                       $buscardetalle = pedidos_detalle::findOrFail($det->ped_det_id);
                       $buscardetalle->update();
                    }

                    $printer->text("\n");
                    $printer->feed();
                    $printer->cut();
                    $printer->pulse();
                    $printer->close();

                  }

                 $buscarpedido = DB::tABLE('pedidos')
                ->where('ped_id',$pedido)
                ->update(['etiqueta' => ""]); 

                 }catch(\Exception $e){
            
                 }
            

              }



    }


public function imprimir_pedido_eliminado($pedido){

 $IdEmpresa = Auth::user()->IdEmpresa;

 $empresanegocios = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first();

 $cab_pedido = DB::tABLE('pedidos')
 ->where('ped_id',$pedido)
 ->leftjoin('users','users.IdUsuario','pedidos.mozo')
 ->first();

 $mesa = DB::tABLE('mesas')->where('mes_id',$cab_pedido->mes_id)->first();

 $impresoras = DB::tABLE('configuracion_impresoras')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->get();


 foreach ($impresoras as $impresora) {
    

    $detalle = DB::tABLE('pedidos_detalle')->where('ped_id',$pedido)
    ->join('productos','pedidos_detalle.IdProducto','productos.IdProducto')
    ->leftjoin('categorias','categorias.cat_id','productos.cat_id')
    ->where('categorias.id_empresa_negocio',Auth::user()->id_empresa_negocio)
    ->where('categorias.impresora',$impresora->Id)
    ->get();

    
    try{

       if(count($detalle) >0){

        if($impresora->tip_conex_imp=='COMPARTIDO'){
            $connector = new WindowsPrintConnector("smb://".$impresora->ruta);
        }elseif($impresora->tip_conex_imp=='RED'){
            $connector = new NetworkPrintConnector($impresora->ruta,9100);
        }
        
        $printer = new Printer($connector);
        

        $printer->setJustification(Printer::JUSTIFY_CENTER);

        $printer -> setTextSize(1, 2);
        $printer->text("PEDIDO ELIMINADO"."\n");

        
        $printer -> setTextSize(1, 1);
        $printer->setFont(Printer::FONT_B);
        $printer -> setTextSize(2, 2);

        $printer->text($cab_pedido->etiqueta."\n");
        $printer->text("Pedido ".$cab_pedido->ped_id."\n");

        $printer->text("Tipo ".$cab_pedido->ped_tip."\n");

        if(!empty($cab_pedido->mes_id)){
            $printer->text($mesa->mes_nom."\n");
        }

        $printer->setFont(Printer::FONT_B);
        $printer -> setTextSize(1, 1);

        if(!empty($cab_pedido->ped_cli_nom)){
            $printer->text("Cliente: ".$cab_pedido->ped_cli_nom."\n");
        }

        if(!empty($cab_pedido->ped_dir)){
            $printer->text("Direccion: ".$cab_pedido->ped_dir."\n");
        }

        if(!empty($cab_pedido->ped_tel)){
            $printer->text("Telefono: ".$cab_pedido->ped_tel."\n");
        }

        if(!empty($cab_pedido->ped_ref)){
            $printer->text("Referencia: ".$cab_pedido->ped_ref."\n");
        }

        if(empty($cab_pedido->etiqueta)){
            $printer->text("Fecha:". $cab_pedido->fecha_hora."\n");
        }else{
            $printer->text("Fecha:". $cab_pedido->fecha_hora_modificacion."\n"."\n");
        }
        
        if(!empty($cab_pedido->mozo)){
            $printer->text("Mozo:". $cab_pedido->name.' '.$cab_pedido->apeusu."\n"."\n");
        }
        
        
        $printer->setJustification(Printer::JUSTIFY_LEFT);
        
        
        $printer->setJustification(Printer::JUSTIFY_LEFT);
        $printer->text("CONCEPTO    CANTIDAD       OBS."."\n");
        $printer->text("_________________________________"."\n");

        $printer->setFont(Printer::FONT_B);
        $printer -> setTextSize(2, 2);
        foreach ($detalle as $det) {
           
         $primeralinea = ($det->pronom);
         //$segundalinea = str_pad(substr($det->pronom,18,34),17," ",STR_PAD_RIGHT);
         $printer->text($det->ped_det_can."  ".$primeralinea."   ".$det->detalle."\n");
         //$printer->text($segundalinea."\n");


         $buscardetalle = pedidos_detalle::findOrFail($det->ped_det_id);
         $buscardetalle->update();
     }

     $printer->text("\n");
     $printer->feed();
     $printer->cut();
     $printer->pulse();
     $printer->close();

 }

 $buscarpedido = DB::tABLE('pedidos')
 ->where('ped_id',$pedido)
 ->update(['etiqueta' => ""]); 

}catch(\Exception $e){

}


}




}



public function imprimir_item_eliminado($pedido,$item){

 $IdEmpresa = Auth::user()->IdEmpresa;

 $empresanegocios = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first();

 $cab_pedido = DB::tABLE('pedidos')
 ->where('ped_id',$pedido)
 ->leftjoin('users','users.IdUsuario','pedidos.mozo')
 ->first();

 $mesa = DB::tABLE('mesas')->where('mes_id',$cab_pedido->mes_id)->first();

 $impresoras = DB::tABLE('configuracion_impresoras')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->get();


 foreach ($impresoras as $impresora) {
    

    $detalle = DB::tABLE('pedidos_detalle')
    ->where('ped_id',$pedido)
    ->where('pedidos_detalle.IdProducto',$item)
    ->join('productos','pedidos_detalle.IdProducto','productos.IdProducto')
    ->leftjoin('categorias','categorias.cat_id','productos.cat_id')
    ->where('categorias.id_empresa_negocio',Auth::user()->id_empresa_negocio)
    ->where('categorias.impresora',$impresora->Id)
    ->get();

    
    try{

       if(count($detalle) >0){



        if($impresora->tip_conex_imp=='COMPARTIDO'){
            $connector = new WindowsPrintConnector("smb://".$impresora->ruta);
        }elseif($impresora->tip_conex_imp=='RED'){
            $connector = new NetworkPrintConnector($impresora->ruta,9100);
        }
        
        $printer = new Printer($connector);
        

        $printer->setJustification(Printer::JUSTIFY_CENTER);

        $printer -> setTextSize(1, 2);
        $printer->text("ITEM ELIMINADO"."\n");

        
        $printer->setFont(Printer::FONT_B);
        $printer -> setTextSize(2, 2);

        $printer->text($cab_pedido->etiqueta."\n");
        $printer->text("Pedido ".$cab_pedido->ped_id."\n");

        $printer->text("Tipo ".$cab_pedido->ped_tip."\n");

        if(!empty($cab_pedido->mes_id)){
            $printer->text($mesa->mes_nom."\n");
        }

        $printer->setFont(Printer::FONT_B);
        $printer -> setTextSize(1, 1);

        if(!empty($cab_pedido->ped_cli_nom)){
            $printer->text("Cliente: ".$cab_pedido->ped_cli_nom."\n");
        }

        if(!empty($cab_pedido->ped_dir)){
            $printer->text("Direccion: ".$cab_pedido->ped_dir."\n");
        }

        if(!empty($cab_pedido->ped_tel)){
            $printer->text("Telefono: ".$cab_pedido->ped_tel."\n");
        }

        if(!empty($cab_pedido->ped_ref)){
            $printer->text("Referencia: ".$cab_pedido->ped_ref."\n");
        }

        if(empty($cab_pedido->etiqueta)){
            $printer->text("Fecha:". $cab_pedido->fecha_hora."\n");
        }else{
            $printer->text("Fecha:". $cab_pedido->fecha_hora_modificacion."\n"."\n");
        }
        
        if(!empty($cab_pedido->mozo)){
            $printer->text("Mozo:". $cab_pedido->name.' '.$cab_pedido->apeusu."\n"."\n");
        }
        
        
        $printer->setJustification(Printer::JUSTIFY_LEFT);
        
        
        $printer->setJustification(Printer::JUSTIFY_LEFT);
        $printer->text("CONCEPTO    CANTIDAD       OBS."."\n");
        $printer->text("_________________________________"."\n");
        $printer->setFont(Printer::FONT_B);
        $printer -> setTextSize(2, 2);
        foreach ($detalle as $det) {
           
         $primeralinea = ($det->pronom);
         //$segundalinea = str_pad(substr($det->pronom,18,34),17," ",STR_PAD_RIGHT);
         $printer->text($det->ped_det_can."  ".$primeralinea."   ".$det->detalle."\n");
         //$printer->text($segundalinea."\n");


         $buscardetalle = pedidos_detalle::findOrFail($det->ped_det_id);
         $buscardetalle->update();
     }

     $printer->text("\n");
     $printer->feed();
     $printer->cut();
     $printer->pulse();
     $printer->close();

 }

 $buscarpedido = DB::tABLE('pedidos')
 ->where('ped_id',$pedido)
 ->update(['etiqueta' => ""]); 
 
}catch(\Exception $e){

}


}




}


function sanear_string($string)
{
 
    $string = trim($string);
 
    $string = str_replace(
        array('á', 'à', 'ä', 'â', 'ª', 'Á', 'À', 'Â', 'Ä'),
        array('a', 'a', 'a', 'a', 'a', 'A', 'A', 'A', 'A'),
        $string
    );
 
    $string = str_replace(
        array('é', 'è', 'ë', 'ê', 'É', 'È', 'Ê', 'Ë'),
        array('e', 'e', 'e', 'e', 'E', 'E', 'E', 'E'),
        $string
    );
 
    $string = str_replace(
        array('í', 'ì', 'ï', 'î', 'Í', 'Ì', 'Ï', 'Î'),
        array('i', 'i', 'i', 'i', 'I', 'I', 'I', 'I'),
        $string
    );
 
    $string = str_replace(
        array('ó', 'ò', 'ö', 'ô', 'Ó', 'Ò', 'Ö', 'Ô'),
        array('o', 'o', 'o', 'o', 'O', 'O', 'O', 'O'),
        $string
    );
 
    $string = str_replace(
        array('ú', 'ù', 'ü', 'û', 'Ú', 'Ù', 'Û', 'Ü'),
        array('u', 'u', 'u', 'u', 'U', 'U', 'U', 'U'),
        $string
    );
 
    $string = str_replace(
        array('ñ', 'Ñ', 'ç', 'Ç'),
        array('n', 'N', 'c', 'C',),
        $string
    );
 
    //Esta parte se encarga de eliminar cualquier caracter extraño
   
 
 
    return $string;
}
 



}
