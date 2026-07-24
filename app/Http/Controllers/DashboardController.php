<?php

namespace MasterSoft\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use DB;
use MasterSoft\logdata;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {   
        $negocios = DB::tABLE('empresa_negocios')->where('IdEmpresa',Auth::user()->IdEmpresa)->get();

        $fec_ini = $request->get('fec_ini');
        $fec_fin = $request->geT('fec_fin');

        if(empty($fec_ini)){

          $fec_ini = now()->modify('first day of this month')->format('Y-m-d');
          $fec_fin = now()->modify('last day of this month')->format('Y-m-d');

        }

        $tot_not_vent = DB::tABLE('cpe_cabecera')->where('tdocod','13')->whereNull('ccabaj')
         ->where('ccafem','>=',$fec_ini)
        ->where('ccafem','<=',$fec_fin)->sum('ccaitv');

        $tot_fac_vent = DB::tABLE('cpe_cabecera')->where('tdocod','01')->whereNull('ccabaj')->where('ccafem','>=',$fec_ini)
        ->where('ccafem','<=',$fec_fin)->sum('ccaitv');

        $tot_bol_vent = DB::tABLE('cpe_cabecera')->where('tdocod','03')->whereNull('ccabaj')->where('ccafem','>=',$fec_ini)
        ->where('ccafem','<=',$fec_fin)->sum('ccaitv');

        $tot_costo = DB::table('cpe_cabecera')
            ->leftjoin('cpe_detalle','cpe_cabecera.IdCpe_cabecera','cpe_detalle.IdCpe_cabecera')
            ->whereIn('cpe_cabecera.tdocod', ['01', '03', '13']) // Filtro de ventas válidas
            ->where('ccafem','>=',$fec_ini)
            ->where('ccafem','<=',$fec_fin)
            ->whereNull('ccabaj') // Descartar anulados
            ->sum(DB::raw('cpe_detalle.costo * cpe_detalle.cdecan'));

        $top_cli = DB::table('cpe_cabecera')
         ->select('ccanom','ccandi','clicod',DB::raw('sum(ccaitv) as total'),DB::raw('count(*) as transacciones'))
         ->whereIn('tdocod', ['01', '03', '13']) // <-- Agrega esto
         ->whereNull('ccabaj')                   // <-- Y esto para descartar anulados
         ->where('ccafem','>=',$fec_ini)
         ->where('ccafem','<=',$fec_fin)
         ->orderBy('total','desc')
         ->groupBy('clicod')
         ->take(100)
         ->get();

        $top_pro = DB::tABLE('cpe_cabecera')
        ->leftjoin('cpe_detalle','cpe_detalle.IdCpe_cabecera','cpe_cabecera.IdCpe_cabecera')
        ->leftjoin('productos','productos.IdProducto','cpe_detalle.IdProducto')
        ->select('productos.IdProducto','pronom','codigo_barra',DB::RAW('sum(cdevve) as total'),DB::RAW('count(cdecan) as movimientos'))
        ->whereNotNull('cpe_detalle.IdProducto')
        ->whereNull('cpe_cabecera.ccabaj')
        ->where('ccafem','>=',$fec_ini)
        ->where('ccafem','<=',$fec_fin)
        ->orderby('movimientos','desc')
        ->groupby('IdProducto')
        ->take(100)
        ->get();
        
        $prod_stock = DB::tABLE('producto_stock')->select('pronom','producto_stock.stock')
        ->join('productos','productos.IdProducto','producto_stock.IdProducto')
        ->where('producto_stock.stock','<=','50')
        ->orderby('producto_stock.stock','asc')
        ->get();

        $vent_men = DB::tABLE('cpe_cabecera')
         ->select(DB::RAW('sum(ccaitv) as total'),DB::raw('YEAR(ccafem) year, MONTH(ccafem) month'))
         ->where(function ($query) {
          $query->where('tdocod','01')
              ->orWhere('tdocod','03')
              ->orWhere('tdocod','13');
          })
         ->whereNull('ccabaj')
          ->where('ccafem','>=',$fec_ini)
          ->where('ccafem','<=',$fec_fin)
         ->groupby('year','month')
         ->get();

         $vent_dia = DB::table('cpe_cabecera')
    ->select(
        DB::raw('DATE(ccafem) as dia'),
        DB::raw('SUM(ccaitv) as total')
    )
    ->where(function ($query) {
        $query->where('tdocod','01')
              ->orWhere('tdocod','03')
              ->orWhere('tdocod','13');
    })
    ->whereNull('ccabaj')
    ->where('ccafem','>=', $fec_ini)
    ->where('ccafem','<=', $fec_fin)
    ->groupBy(DB::raw('DATE(ccafem)'))
    ->orderBy(DB::raw('DATE(ccafem)'))
    ->get();

           $meses = DB::tABLE('cpe_cabecera')
         ->select(DB::raw('MONTH(ccafem) month'))
         ->where(function ($query) {
          $query->where('tdocod','01')
              ->orWhere('tdocod','03')
              ->orWhere('tdocod','13');
          })
         ->whereNull('ccabaj')
          ->where('ccafem','>=',$fec_ini)
        ->where('ccafem','<=',$fec_fin)
         ->groupby('month')
         ->get();

         $expiringProducts = DB::table('compras_detalle as cd')
            ->select(
                'cd.pro_id',
                'p.pronom',
                'cd.lote',
                'cd.vencimiento',
                'cd.cantidad'
            )
            ->join('productos as p', 'p.IdProducto', '=', 'cd.pro_id')
            ->whereNotNull('cd.vencimiento')
            ->where('cd.vencimiento', '>=', Carbon::now()->toDateString())
            ->where('cd.vencimiento', '<=', Carbon::now()->addMonths(1)->toDateString())
            ->orderBy('cd.vencimiento', 'asc')
            ->get();

        foreach ($expiringProducts as $product) {
            $daysUntilExpiration = Carbon::now()->diffInDays($product->vencimiento, false);

            if ($daysUntilExpiration <= 0) {
                $product->expiration_status = 'Vencido';
                $product->status_color = 'danger';
            } elseif ($daysUntilExpiration <= 15) {
                $product->expiration_status = 'Por Vencer Urgente';
                $product->status_color = 'warning';
            } elseif ($daysUntilExpiration <= 30) {
                $product->expiration_status = 'Por Vencer';
                $product->status_color = 'info';
            } else {
                $product->expiration_status = 'Pronto a Vencer';
                $product->status_color = 'success';
            }
        }

        // AQUÍ ESTÁ EL CAMBIO - Agregar 'vent_dia' al compact
        return view('empresas.dashboard.dashboard',compact(
            'tot_not_vent',
            'tot_fac_vent',
            'tot_bol_vent',
            'tot_costo',
            'top_cli',
            'top_pro',
            'prod_stock',
            'vent_men',
            'vent_dia',  // <- ESTA LÍNEA ES LA QUE FALTABA
            'meses',
            'fec_ini',
            'fec_fin',
            'negocios',
            'expiringProducts'
        ));
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

    public function Documentos(Request $request, $tdocod_filter)
{
    $rucemp = trim(Auth::user()->IdEmpresa);
    
    // 1. Determinar título y tipos de documento a filtrar
    $title = '';
    $document_types = [];
    
    switch ($tdocod_filter) {
        case '13':
            $title = 'Detalle de Notas de Venta';
            $document_types = ['13'];
            break;
        case '01':
            $title = 'Detalle de Facturas';
            $document_types = ['01'];
            break;
        case '03':
            $title = 'Detalle de Boletas';
            $document_types = ['03'];
            break;
        case 'ALL':
            $title = 'Detalle de Total Ventas';
            $document_types = ['13', '01', '03']; // Asumiendo que Total Ventas incluye estos 3
            break;
        default:
            $title = 'Documentos de Venta';
            $document_types = ['13', '01', '03'];
            break;
    }

    // 2. Aplicar filtros de fecha que vienen del dashboard
    $fec_ini = $request->get('fec_ini');
    $fec_fin = $request->get('fec_fin');
    
    if (empty($fec_ini)) {
      $fec_ini = now()->modify('first day of this month')->format('Y-m-d');
      $fec_fin = now()->modify('last day of this month')->format('Y-m-d');
    }

    // 3. Construcción de la consulta
    $documents = DB::tABLE('cpe_cabecera')
        ->select(
            'ccafem', // Fecha
            'tdocod', // Tipo
            'serdoc', // Serie
            'numdoc', // Número
            'ccandi', // DNI/RUC Cliente
            'ccanom', // Nombre Cliente
            'ccaitv', // Total
            // Campos RAW para la concatenación que pediste
            DB::raw("CONCAT(serdoc, '-', numdoc) AS serie_numero"),
            DB::raw("CONCAT(ccandi, ' - ', ccanom) AS cliente_completo")
        )
        ->whereIn('tdocod', $document_types)
        ->whereNull('ccabaj') // Excluir documentos anulados
        ->where('ccafem', '>=', $fec_ini)
        ->where('ccafem', '<=', $fec_fin)
        // Puedes agregar un filtro de empresa si la tabla cpe_cabecera lo requiere
        // ->where('IdEmpresa', $rucemp) 
        ->orderBy('ccafem', 'desc')
        ->get();

        $total_suma = $documents->sum('ccaitv'); // <--- ¡Esta es la clave!

    return view('empresas.dashboard.documentos', compact('documents', 'title', 'fec_ini', 'fec_fin','total_suma'));
}

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
