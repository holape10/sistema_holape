<?php

namespace MasterSoft\Http\Controllers;
use Illuminate\Http\Request;
//use Illuminate\Support\Facades\Artisan;
use MasterSoft\Http\Middleware\CheckSystemTruncate;
use MasterSoft\Http\Requests;
use MasterSoft\turnos;
use MasterSoft\caja;
use MasterSoft\User;
use MasterSoft\Empresa;
use MasterSoft\usuario_facturacion;
use Illuminate\Support\Facades\Auth;
use MasterSoft\Http\Requests\FacturaCreateFormRequest;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Input;
use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\FilePrintConnector;
use Mike42\Escpos\EscposImage;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Carbon\Carbon;
use DB;
use Excel;

class TurnosController extends Controller
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

    public function index(Request $request)
    {
        $rucemp = trim(Auth::user()->IdEmpresa);
        $usuarios = User::all();
        $buscar = $request->get('usuario');

        $medios = DB::tABLE('medios_pagos')->get();

        if($request){
           

            if(empty($buscar)){

                $turnos = DB::tABLE('turnos')->select('en.tipo_negocio','apeusu','name','email','apertura','cierre','turnos.estado','turnos.id_turno','monto','montocierre')
                ->leftjoin('empresa_negocios as en','en.id_empresa_negocio','turnos.id_empresa_negocio')
                ->leftjoin('users as u','u.IdUsuario','turnos.IdUsuario')
                ->where('turnos.id_empresa_negocio',Auth::user()->id_empresa_negocio)
                ->where('turnos.IdUsuario',Auth::user()->IdUsuario)
                ->orderby('turnos.id_turno','desc')
                ->paginate(100);

                 $datosusuario = DB::tABLE('users')->where('IdUsuario',Auth::user()->IdUsuario)->first();


            }else{

                 $turnos = DB::tABLE('turnos')->select('en.tipo_negocio','apeusu','name','email','apertura','cierre','turnos.estado','turnos.id_turno','monto','montocierre')
                 ->leftjoin('empresa_negocios as en','en.id_empresa_negocio','turnos.id_empresa_negocio')
                 ->leftjoin('users as u','u.IdUsuario','turnos.IdUsuario')
                 ->where('turnos.id_empresa_negocio',Auth::user()->id_empresa_negocio)
                 ->where('turnos.IdUsuario',$buscar)
                 ->orderby('turnos.id_turno','desc')
                 ->paginate(100);

                 $datosusuario = DB::tABLE('users')->where('IdUsuario',$buscar)->first();

            }

            $sucursales = DB::tABLE('users as us')
            ->leftjoin('empresa_negocios as en','en.id_empresa_negocio','us.id_empresa_negocio')
            ->where('us.IdUsuario',Auth::user()->IdUsuario)
            ->get();
            
           
            /*$almacenes = DB::tABLE('usuario_almacen as ua')
            ->leftjoin('almacenes as al','al.id_almacen','ua.id_almacen')
            ->leftjoin('empresa_negocios as en','en.id_empresa_negocio','al.id_empresa_negocio')
            ->where('ua.IdUsuario',Auth::user()->IdUsuario)
            ->where('al.id_empresa_negocio',$sucursales->first()->id_empresa_negocio)->get();*/

            
             return view('empresas.turnos.caja',compact('turnos','buscar','usuarios','datosusuario','sucursales','medios'));
            
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        return view('empresas.turnos.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $turnos = new turnos;
        $turnos->id_empresa_negocio = $request->get('cmbSucursal');
        $turnos->turno = $request->get('txtturno');
        $turnos->IdUsuario = $request->get('txtturno');
        $turnos->estado = 'Aperturado';
        $turnos->save();

        return Redirect::to('/turnos');
    }

    public function AperturarTurno(Request $request){

        $turnos = new turnos;
        $turnos->id_empresa_negocio = $request->get('cmbSucursal');
        $turnos->IdUsuario = Auth::user()->IdUsuario;
        $turnos->estado = 'Aperturado';
        $turnos->monto = $request->get('txtMonto');
        $turnos->save();

        $usuario = User::findOrFail(Auth::user()->IdUsuario);
        $usuario->turno = 'Aperturado';
        $usuario->id_turno = $turnos->id_turno;
        
        $usuario->update();

        return Redirect::to('/seleccion')->with('success','TURNO APERTURADO');
    }
    

    public function cuadrecaja($id){

        $medios = DB::tABLE('venta_medio_pago as vmp')
        ->select('nom_med_pag',DB::raw('SUM(monto) as monto'))
        ->join('medios_pagos as mp','mp.id_med_pag','vmp.id_med_pag')
        ->where('id_turno',$id)
        ->groupby('vmp.id_med_pag')
        ->get();

        $comprobantes = DB::tABLE('venta_medio_pago as vmp')
        ->select('tdodes',DB::raw('SUM(monto) as monto'))
        ->join('cpe_cabecera as cpe','cpe.IdCpe_cabecera','vmp.IdCpe_cabecera')
        ->join('tipo_documento as td','td.tdocod','cpe.tdocod')
        ->where('id_turno',$id)
        ->groupby('cpe.tdocod')
        ->get();
        return '';

    }


   
public function CerrarTurno(Request $request)
{
    $opcion = $request->get('opcion');
    // Definimos los documentos permitidos para que el cálculo sea exacto
    $docs_permitidos = ['01', '03', '07', '08', '13'];
    
    $bus_ocup = DB::table('pedidos')->where('ped_est', 'Aperturado')->count();

    if ($opcion != '1') { // Cierre de turno regular

        $turno = Auth::user()->id_turno;
        $datos = DB::table('turnos')->where('id_turno', $turno)->first();
        $medio_pago = $request->get('txtMedPago');
        $medio_monto = $request->get('txtMonto');

        $medios = DB::table('venta_medio_pago as vmp')
            ->select('nom_med_pag', DB::raw('SUM(monto) as monto'))
            ->join('medios_pagos as mp', 'mp.id_med_pag', 'vmp.id_med_pag')
            ->join('cpe_cabecera as cpe', 'cpe.IdCpe_cabecera', 'vmp.IdCpe_cabecera')
            ->where('vmp.id_turno', $turno)
            ->whereNull('ccabaj')
            ->whereIn('cpe.tdocod', $docs_permitidos)
            ->groupBy('vmp.id_med_pag')
            ->get();

        $total_cierre = 0;

        if ($medio_pago && is_array($medio_pago)) {
            foreach ($medio_pago as $index => $mp) {
                DB::table('turno_medio_pago')->insert(['id_turno' => $turno, 'id_med_pag' => $mp, 'monto' => $medio_monto[$index]]);
            }
        }

        $comprobantes = DB::table('venta_medio_pago as vmp')
            ->select('tdodes', DB::raw('SUM(monto) as monto'))
            ->join('cpe_cabecera as cpe', 'cpe.IdCpe_cabecera', 'vmp.IdCpe_cabecera')
            ->join('tipo_documento as td', 'td.tdocod', 'cpe.tdocod')
            ->where('vmp.id_turno', $turno)
            ->whereNull('ccabaj')
            ->whereIn('cpe.tdocod', $docs_permitidos)
            ->groupBy('cpe.tdocod')
            ->get();

        $gastos = DB::table('gastos_cabecera as gc')
            ->join('usuario_gastos as tur', 'gc.gast_cab_id', 'tur.gast_cab_id')
            ->where('id_turno', $turno)
            ->where('est_gasto', 'Registrado')
            ->where('referencia', 'GASTO')
            ->sum('total_gast');

        $ingresos = DB::table('gastos_cabecera as gc')
            ->join('usuario_gastos as tur', 'gc.gast_cab_id', 'tur.gast_cab_id')
            ->where('id_turno', $turno)
            ->where('est_gasto', 'Registrado')
            ->where('referencia', 'INGRESO')
            ->sum('total_gast');

        foreach ($medios as $me) {
            $total_cierre = $total_cierre + $me->monto;
        }

        $usuario = User::findOrFail(Auth::user()->IdUsuario);
        $usuario->turno = 'Cerrado';
        $usuario->id_turno = "0";
        $usuario->update();

        DB::table('turnos')
            ->where('id_turno', $turno)
            ->update([
                'estado' => 'Cerrado',
                'montocierre' => ($datos->monto + $total_cierre + $ingresos) - $gastos,
                'cieisl1dis1' => $request->get('cieisl1dis1'),
                'cieisl1dis2' => $request->get('cieisl1dis2'),
                'cieisl2dis1' => $request->get('cieisl2dis1'),
                'cieisl2dis2' => $request->get('cieisl2dis2'),
                'cant_m_10_centimos' => $request->get('cant_m_10_centimos', 0),
                'cant_m_20_centimos' => $request->get('cant_m_20_centimos', 0),
                'cant_m_50_centimos' => $request->get('cant_m_50_centimos', 0),
                'cant_m_1_sol'       => $request->get('cant_m_1_sol', 0),
                'cant_m_2_soles'     => $request->get('cant_m_2_soles', 0),
                'cant_m_5_soles'     => $request->get('cant_m_5_soles', 0),
                'cant_c_10_soles'    => $request->get('cant_c_10_soles', 0),
                'cant_c_20_soles'    => $request->get('cant_c_20_soles', 0),
                'cant_c_50_soles'    => $request->get('cant_c_50_soles', 0),
                'cant_c_100_soles'   => $request->get('cant_c_100_soles', 0),
                'cant_c_200_soles'   => $request->get('cant_c_200_soles', 0),
            ]);

        try {
            \Log::info('Pedidos desvinculados de cpe_cabecera y tablas pedidos/pedidos_detalle truncadas para el turno ID: ' . $turno);
            \Log::info('Estado de las mesas actualizado a "Libre" para la empresa ID: ' . Auth::user()->id_empresa_negocio);
        } catch (\Exception $e) {
            \Log::error('Error al procesar tablas de pedidos o mesas al cerrar turno: ' . $e->getMessage());
            return Redirect::to('/consolacaja')->with('danger', 'Error al reiniciar pedidos o mesas: ' . $e->getMessage());
        }

        $verificacion = \MasterSoft\Http\Middleware\CheckSystemTruncate::checkTruncateStatusNow();
        $ruta_redirect = (isset($verificacion['blocked']) && $verificacion['blocked']) ? '/' : '/consolacaja';
        $mensaje = (isset($verificacion['blocked']) && $verificacion['blocked']) ? 'TURNO CERRADO Y SISTEMA BLOQUEADO' : 'TURNO CERRADO';

        session()->flash('danger', $mensaje);

        $denominaciones = [
            'cant_m_10_centimos' => $request->get('cant_m_10_centimos', 0),
            'cant_m_20_centimos' => $request->get('cant_m_20_centimos', 0),
            'cant_m_50_centimos' => $request->get('cant_m_50_centimos', 0),
            'cant_m_1_sol'       => $request->get('cant_m_1_sol', 0),
            'cant_m_2_soles'     => $request->get('cant_m_2_soles', 0),
            'cant_m_5_soles'     => $request->get('cant_m_5_soles', 0),
            'cant_c_10_soles'    => $request->get('cant_c_10_soles', 0),
            'cant_c_20_soles'    => $request->get('cant_c_20_soles', 0),
            'cant_c_50_soles'    => $request->get('cant_c_50_soles', 0),
            'cant_c_100_soles'   => $request->get('cant_c_100_soles', 0),
            'cant_c_200_soles'   => $request->get('cant_c_200_soles', 0),
        ];

        // ACA CONSULTAMOS LOS DATOS FALTANTES QUE CAUSABAN EL ERROR
        $datos = DB::table('turnos')->where('id_turno', $turno)->first();
        $sucursal = DB::table('empresa_negocios')->where('id_empresa_negocio', $datos->id_empresa_negocio)->first();
        $empresa = Empresa::findOrFail($sucursal->IdEmpresa);
        $cajero = DB::table('turnos as t')->join('users as u', 'u.IdUsuario', 't.IdUsuario')->where('t.id_turno', $turno)->first();

        // ENVIAMOS TODO COMPLETO A LA VISTA
        return view('formatos_comprobantes.denominaciones', compact('denominaciones', 'turno', 'ruta_redirect', 'empresa', 'cajero', 'datos'));

    } else { // Modificar turno ya cerrado

        $turno = $txtturno = $request->get('txtTurno');
        $datos = DB::table('turnos')->where('id_turno', $turno)->first();
        $medio_pago = $request->get('txtMedPago');
        $medio_monto = $request->get('txtMonto');

        DB::table('turno_medio_pago')->where('id_turno', $turno)->delete();

        $total_cierre = 0;
        if ($medio_pago && is_array($medio_pago)) {
            foreach ($medio_pago as $index => $mp) {
                DB::table('turno_medio_pago')->insert(['id_turno' => $turno, 'id_med_pag' => $mp, 'monto' => $medio_monto[$index]]);
            }
        }

        $medios = DB::table('venta_medio_pago as vmp')
            ->select('nom_med_pag', DB::raw('SUM(monto) as monto'))
            ->join('medios_pagos as mp', 'mp.id_med_pag', 'vmp.id_med_pag')
            ->join('cpe_cabecera as cpe', 'cpe.IdCpe_cabecera', 'vmp.IdCpe_cabecera')
            ->where('vmp.id_turno', $turno)
            ->whereNull('ccabaj')
            ->whereIn('cpe.tdocod', $docs_permitidos)
            ->groupBy('vmp.id_med_pag')
            ->get();

        $gastos = DB::table('gastos_cabecera as gc')
            ->join('usuario_gastos as tur', 'gc.gast_cab_id', 'tur.gast_cab_id')
            ->where('id_turno', $turno)
            ->where('est_gasto', 'Registrado')
            ->where('referencia', 'GASTO')
            ->sum('total_gast');

        $ingresos = DB::table('gastos_cabecera as gc')
            ->join('usuario_gastos as tur', 'gc.gast_cab_id', 'tur.gast_cab_id')
            ->where('id_turno', $turno)
            ->where('est_gasto', 'Registrado')
            ->where('referencia', 'INGRESO')
            ->sum('total_gast');

        foreach ($medios as $me) {
            $total_cierre = $total_cierre + $me->monto;
        }

        $usuario = User::findOrFail(Auth::user()->IdUsuario);
        $usuario->turno = 'Cerrado';
        $usuario->id_turno = "0";
        $usuario->update();

        DB::table('turnos')
            ->where('id_turno', $turno)
            ->update([
                'estado' => 'Cerrado',
                'montocierre' => ($datos->monto + $total_cierre + $ingresos) - $gastos,
                'cant_m_10_centimos' => $request->get('cant_m_10_centimos', 0),
                'cant_m_20_centimos' => $request->get('cant_m_20_centimos', 0),
                'cant_m_50_centimos' => $request->get('cant_m_50_centimos', 0),
                'cant_m_1_sol'       => $request->get('cant_m_1_sol', 0),
                'cant_m_2_soles'     => $request->get('cant_m_2_soles', 0),
                'cant_m_5_soles'     => $request->get('cant_m_5_soles', 0),
                'cant_c_10_soles'    => $request->get('cant_c_10_soles', 0),
                'cant_c_20_soles'    => $request->get('cant_c_20_soles', 0),
                'cant_c_50_soles'    => $request->get('cant_c_50_soles', 0),
                'cant_c_100_soles'   => $request->get('cant_c_100_soles', 0),
                'cant_c_200_soles'   => $request->get('cant_c_200_soles', 0),
            ]);

        $verificacion = \MasterSoft\Http\Middleware\CheckSystemTruncate::checkTruncateStatusNow();
        $ruta_redirect = (isset($verificacion['blocked']) && $verificacion['blocked']) ? '/' : '/consolacaja';
        $mensaje = (isset($verificacion['blocked']) && $verificacion['blocked']) ? 'TURNO CERRADO Y SISTEMA BLOQUEADO' : 'TURNO CERRADO';

        session()->flash('danger', $mensaje);

        $denominaciones = [
            'cant_m_10_centimos' => $request->get('cant_m_10_centimos', 0),
            'cant_m_20_centimos' => $request->get('cant_m_20_centimos', 0),
            'cant_m_50_centimos' => $request->get('cant_m_50_centimos', 0),
            'cant_m_1_sol'       => $request->get('cant_m_1_sol', 0),
            'cant_m_2_soles'     => $request->get('cant_m_2_soles', 0),
            'cant_m_5_soles'     => $request->get('cant_m_5_soles', 0),
            'cant_c_10_soles'    => $request->get('cant_c_10_soles', 0),
            'cant_c_20_soles'    => $request->get('cant_c_20_soles', 0),
            'cant_c_50_soles'    => $request->get('cant_c_50_soles', 0),
            'cant_c_100_soles'   => $request->get('cant_c_100_soles', 0),
            'cant_c_200_soles'   => $request->get('cant_c_200_soles', 0),
        ];

        // ACA CONSULTAMOS LOS DATOS FALTANTES QUE CAUSABAN EL ERROR
        $datos = DB::table('turnos')->where('id_turno', $turno)->first();
        $sucursal = DB::table('empresa_negocios')->where('id_empresa_negocio', $datos->id_empresa_negocio)->first();
        $empresa = Empresa::findOrFail($sucursal->IdEmpresa);
        $cajero = DB::table('turnos as t')->join('users as u', 'u.IdUsuario', 't.IdUsuario')->where('t.id_turno', $turno)->first();

        // ENVIAMOS TODO COMPLETO A LA VISTA
        return view('formatos_comprobantes.denominaciones', compact('denominaciones', 'turno', 'ruta_redirect', 'empresa', 'cajero', 'datos'));
    }
}

    public function reporteVentasDia($id_turno)
    {
        // 1. Obtener los datos del turno para obtener el usuario y el rango de fechas
        $turno = turnos::find($id_turno);

        if (!$turno) {
            abort(404, 'Turno no encontrado.');
        }

        $idUsuario = $turno->IdUsuario;
        $fechaInicioTurno = Carbon::parse($turno->apertura);
        
        $fechaFinTurno = $turno->cierre;
        if (!$fechaFinTurno) {
            $fechaFinTurno = Carbon::now();
        } else {
            $fechaFinTurno = Carbon::parse($fechaFinTurno);
        }

        // Obtener datos de la empresa
        $sucursal = DB::table('empresa_negocios')->where('id_empresa_negocio', $turno->id_empresa_negocio)->first();
        $empresa = Empresa::find($sucursal->IdEmpresa);

        // Obtener el nombre del cajero
        $cajero = User::where('IdUsuario', $turno->IdUsuario)->first();

        // 2. OBTENER DATOS DE VENTAS
        $ventas = DB::table('cpe_cabecera')
                      ->select(
                          'cpe_cabecera.fecha_hora',
                          'cpe_cabecera.serdoc',
                          'cpe_cabecera.numdoc',
                          'cpe_cabecera.ccaitv',
                          'cpe_cabecera.estadopago',
                          'tipo_documento.tdodes as tipo_documento',
                          'cliente.clinom as nombre_cliente'
                      )
                      ->join('tipo_documento', 'tipo_documento.tdocod', '=', 'cpe_cabecera.tdocod')
                      ->leftJoin('cliente', 'cliente.clicod', '=', 'cpe_cabecera.clicod')
                      ->where('cpe_cabecera.IdUsuario_ven', $idUsuario)
                      ->whereBetween('cpe_cabecera.fecha_hora', [$fechaInicioTurno, $fechaFinTurno])
                      //->where('cpe_cabecera.cre_dia_id', '!=', 2)
                      ->where('cpe_cabecera.tdocod', '!=', '85')
                      ->where('cpe_cabecera.tdocod', '!=', '86')
                      ->orderBy('cpe_cabecera.estadopago', 'asc') // Ordenar por fecha
                      ->get();

        // Calcular totales de ventas
        $totalVentas = $ventas->sum('ccaitv');
        $cantidadVentas = $ventas->count();
        $totalContado = $ventas->where('estadopago', 'CONTADO')->sum('ccaitv');
        $cantidadContado = $ventas->where('estadopago', 'CONTADO')->count();
        $totalCredito = $ventas->where('estadopago', 'CREDITO')->sum('ccaitv');
        $cantidadCredito = $ventas->where('estadopago', 'CREDITO')->count();

        // 3. OBTENER DATOS DE COBRANZAS
        $cobranzas = DB::table('cuentas_cobrar_detalle')
                         ->select(
                             'cuentas_cobrar_detalle.fecha_hora',
                             'cuentas_cobrar_detalle.abono',
                             //'cuentas_cobrar_detalle.observacion', // Si existe este campo y es útil
                             'cuentas_cobrar.cue_cob_id',
                             'cpe_cabecera.tdocod',
                             'cpe_cabecera.serdoc',
                             'cpe_cabecera.numdoc',
                             'cliente.clinom as nombre_cliente'
                         )
                         ->leftJoin('cuentas_cobrar', 'cuentas_cobrar.cue_cob_id', '=', 'cuentas_cobrar_detalle.cue_cob_id')
                         ->leftJoin('cliente', 'cliente.clicod', '=', 'cuentas_cobrar.clicod')
                         ->leftJoin('cpe_cabecera', 'cpe_cabecera.IdCpe_cabecera', '=', 'cuentas_cobrar.IdCpe_cabecera')
                         ->where('cuentas_cobrar_detalle.vendedor', $idUsuario)
                         ->whereBetween('cuentas_cobrar_detalle.fecha_hora', [$fechaInicioTurno, $fechaFinTurno])
                         //->orderBy('gastos_cabecera.tipo_movimiento', 'asc') // Ordenar por fecha
                         ->get();

        // Calcular totales de cobranzas
        $totalCobranzas = $cobranzas->sum('abono');
        $cantidadCobranzas = $cobranzas->count();

        // 4. OBTENER DATOS DE GASTOS E INGRESOS POR MOVIMIENTO
        // Aquí obtendremos todos los movimientos de gasto/ingreso registrados para el turno
        $movimientosCaja = DB::table('gastos_cabecera')
                               ->select(
                                   'gastos_cabecera.gast_fec',
                                   'gastos_cabecera.tipo_movimiento', // 'GASTO' o 'INGRESO'
                                   'gastos_detalle.det_gasto',   // Detalle del gasto/ingreso
                                   'gastos_detalle.pre_uni',     // Total unitario (si aplica, o lo que quieres como monto por item)
                                   'gastos_cabecera.total_gast',
                                   'gastos_detalle.pre_uni' // Total del gasto/ingreso
                                   //'tipo_gastos.tip_gas_nom'     // Nombre del tipo de gasto/ingreso
                               )
                               ->join('usuario_gastos', 'usuario_gastos.gast_cab_id', '=', 'gastos_cabecera.gast_cab_id')
                               ->leftJoin('gastos_detalle', 'gastos_cabecera.gast_cab_id', '=', 'gastos_detalle.gast_cab_id') // LEFT JOIN para que traiga la cabecera incluso sin detalle
                               ->leftJoin('tipo_gastos', 'tipo_gastos.tip_gas_id', '=', 'gastos_detalle.tip_gas_id')
                               ->where('usuario_gastos.id_turno', $id_turno) // Filtrar por el turno actual
                               ->where('gastos_cabecera.est_gasto', 'Registrado') // Solo movimientos registrados
                               //->whereBetween('gastos_cabecera.gast_fec', [$fechaInicioTurno, $fechaFinTurno]) // Rango de fechas del turno
                               ->orderBy('gastos_cabecera.tipo_movimiento', 'asc') // Ordenar por fecha
                               ->get();

        // Filtrar y calcular totales separados para GASTOS e INGRESOS DE CAJA
        $gastosCaja = $movimientosCaja->where('tipo_movimiento', 'GASTO');
        $totalGastosCaja = $gastosCaja->sum('pre_uni'); // Suma del total de la cabecera
        $cantidadGastosCaja = $gastosCaja->count();

        $ingresosCaja = $movimientosCaja->where('tipo_movimiento', 'INGRESO');
        $totalIngresosCaja = $ingresosCaja->sum('pre_uni'); // Suma del total de la cabecera
        $cantidadIngresosCaja = $ingresosCaja->count();

        // 5. Preparar TODOS los datos para la vista
        $data = [
            'turno' => $turno,
            'ventas' => $ventas,
            'totalVentas' => $totalVentas,
            'cantidadVentas' => $cantidadVentas,
            'totalContado' => $totalContado,
            'cantidadContado' => $cantidadContado,
            'totalCredito' => $totalCredito,
            'cantidadCredito' => $cantidadCredito,
            // Datos de cobranzas
            'cobranzas' => $cobranzas,
            'totalCobranzas' => $totalCobranzas,
            'cantidadCobranzas' => $cantidadCobranzas,
            // Nuevos datos de movimientos de caja (gastos/ingresos)
            'movimientosCaja' => $movimientosCaja, // Pasamos todos los movimientos
            'gastosCaja' => $gastosCaja, // Y los filtrados por si se necesitan
            'totalGastosCaja' => $totalGastosCaja,
            'cantidadGastosCaja' => $cantidadGastosCaja,
            'ingresosCaja' => $ingresosCaja,
            'totalIngresosCaja' => $totalIngresosCaja,
            'cantidadIngresosCaja' => $cantidadIngresosCaja,
            // Datos de la empresa y cajero (comunes a todos los reportes)
            'empresa_nombre' => $empresa->NomEmpresa ?? 'N/A',
            'empresa_ruc' => $empresa->IdEmpresa ?? 'N/A',
            'empresa_direccion' => $sucursal->direccion ?? 'N/A',
            'empresa_telefono' => $empresa->tel_emp ?? '',
            'fecha_impresion' => Carbon::now()->format('d/m/Y H:i:s'),
            'cajero_nombre' => ($cajero->name ?? '') . ' ' . ($cajero->apeusu ?? ''),
        ];

        // 6. Devolver la vista
        return view('formatos_comprobantes.ventas_dia', $data);
    }

    public function imprimirCajaCategoriasProductos($id)
    {
        $turno = $id;

        $datos = DB::table('turnos')->where('id_turno', $turno)->first();
        $sucursal = DB::table('empresa_negocios')->where('id_empresa_negocio', $datos->id_empresa_negocio)->first();
        $empresa = Empresa::findOrFail($sucursal->IdEmpresa);
        $cajero = DB::table('turnos as t')->join('users as u', 'u.IdUsuario', 't.IdUsuario')->where('t.id_turno', $turno)->first();

        // Obtener ventas agrupadas por categoría y producto con las correcciones de totales y categorías nulas
        $ventas_agrupadas = DB::table('cpe_cabecera as cc')
            ->select(
                DB::raw('COALESCE(c.cat_nom, "SIN CATEGORÍA") as cat_nom'), // Asegura que los productos sin categoría aparezcan
                'p_base.pronom',
                DB::raw('SUM(cd.cdecan * p_base.factor) as CANTIDAD'), // Mantiene el cálculo de cantidad base
                'p_base.propun as PRECIO_UNITARIO', 
                DB::raw('SUM(cd.cdevve) as TOTAL'), // CORRECCIÓN CLAVE: Suma el valor real facturado en caja
                DB::raw('COALESCE(c.cat_id, 0) as cat_id') // Asigna un ID 0 a los que no tienen categoría para poder agruparlos
            )
            ->join('cpe_detalle as cd', 'cd.IdCpe_cabecera', '=', 'cc.IdCpe_cabecera')
            ->join('productos as p_base', function($join) {
                $join->on('p_base.IdProducto', '=', DB::raw('COALESCE(cd.IdProducto_rel, cd.IdProducto)'));
            })
            ->leftJoin('categorias as c', 'c.cat_id', '=', 'p_base.cat_id') // CORRECCIÓN CLAVE: Cambiado a leftJoin
            ->where(function ($query) {
                $query->whereIn('cc.tdocod', ['01', '03', '13']); // Factura, Boleta, Nota de Venta
            })
            ->where('cc.id_turno', $turno)
            ->whereNull('cc.ccabaj')
            ->where('cc.tdocod', '!=', '99') // Excluir autoconsumo
            ->groupBy('c.cat_id', 'c.cat_nom', 'p_base.IdProducto', 'p_base.pronom', 'p_base.propun', 'p_base.factor') 
            ->orderBy(DB::raw('COALESCE(c.cat_nom, "SIN CATEGORÍA")')) // Ordena tomando en cuenta la validación
            ->orderBy('p_base.pronom')
            ->get();

        $ventas_por_categoria = [];
        $gran_total_ventas = 0;

        foreach ($ventas_agrupadas as $venta) {
            if (!isset($ventas_por_categoria[$venta->cat_id])) {
                $ventas_por_categoria[$venta->cat_id] = [
                    'nombre_categoria' => $venta->cat_nom,
                    'productos' => []
                ];
            }
            $ventas_por_categoria[$venta->cat_id]['productos'][] = $venta;
            $gran_total_ventas += $venta->TOTAL;
        }

        return view('formatos_comprobantes.ticket_cajaturno_categorias_productos', compact(
            'datos',
            'empresa',
            'sucursal',
            'cajero',
            'ventas_por_categoria',
            'gran_total_ventas'
        ));
    }




    public function imprimirturnoproductos($id){

        $turno = $id;

        $datos = DB::table('turnos')->where('id_turno',$turno)->first();

        $sucursal = DB::table('empresa_negocios')->where('id_empresa_negocio',$datos->id_empresa_negocio)->first();

        $empresa = Empresa::findOrFail($sucursal->IdEmpresa);

        $cajero = DB::table('turnos as t')->join('users as u','u.IdUsuario','t.IdUsuario')->where('t.id_turno',$turno)->first();

        $medios = DB::table('venta_medio_pago as vmp')
        ->select('vmp.id_med_pag','nom_med_pag',DB::raw('SUM(monto) as monto'))
        ->join('medios_pagos as mp','mp.id_med_pag','vmp.id_med_pag')
        ->leftjoin('cpe_cabecera as cpe','cpe.IdCpe_cabecera','vmp.IdCpe_cabecera')
        ->where('vmp.id_turno',$turno)
        ->whereNull('ccabaj')
        ->where('cpe.tdocod', '!=', '99') // Descomentar si quieres excluir autoconsumo de medios de pago en este reporte
        ->groupby('vmp.id_med_pag')
        ->get();

        $medios_reg =  DB::table('turno_medio_pago as vmp')
        ->select('vmp.id_med_pag','nom_med_pag',DB::raw('SUM(monto) as monto'))
        ->join('medios_pagos as mp','mp.id_med_pag','vmp.id_med_pag')
        ->where('vmp.id_turno',$turno)
        ->groupby('vmp.id_med_pag')
        ->get();

        $comprobantes = DB::table('venta_medio_pago as vmp')
        ->select('tdodes',DB::raw('SUM(monto) as monto'),DB::raw('count(*) as cantidad'))
        ->join('cpe_cabecera as cpe','cpe.IdCpe_cabecera','vmp.IdCpe_cabecera')
        ->join('tipo_documento as td','td.tdocod','cpe.tdocod')
        ->where('vmp.id_turno',$turno)
        ->whereNull('ccabaj')
        ->where('cpe.tdocod', '!=', '99') // Descomentar si quieres excluir autoconsumo de comprobantes de venta en este reporte
        ->groupby('cpe.tdocod')
        ->get();

        $ven_prod = DB::table('cpe_cabecera as cc')
            ->select(
                DB::raw('cd.IdProducto as IdProducto'),
                DB::raw('p.pronom as pronom'),
                // Mantener IdProducto_rel para identificar presentaciones
                DB::raw('cd.IdProducto_rel as IdProducto_rel'), 
                DB::raw('SUM(cd.cdevve) as TOTAL'),
                DB::raw('SUM(cd.cdecan) as CANTIDAD')
            )
            ->join('cpe_detalle as cd', 'cd.IdCpe_cabecera', '=', 'cc.IdCpe_cabecera')
            // Unir a productos para obtener el nombre del producto realmente vendido
            ->join('productos as p', 'p.IdProducto', '=', 'cd.IdProducto') 
            ->where(function ($query) {
                $query->whereIn('cc.tdocod', ['01', '03', '13']); // Factura, Boleta, Nota de Venta
            })
            ->where('cc.id_turno', $id)
            ->whereNull('cc.ccabaj')
            ->where('cc.tdocod', '!=', '99') // Excluir autoconsumo
            // Agrupar por el producto específico vendido y su relación
            ->groupBy('cd.IdProducto', 'p.pronom', 'cd.IdProducto_rel') 
            // Ordenar por el padre (IdProducto_rel) para que las presentaciones aparezcan debajo del principal
            ->orderBy('cd.IdProducto_rel', 'asc') 
            ->orderBy('cd.IdProducto', 'asc')
            ->get();
        

        // ****************************************************************************************************
        // CORRECCIÓN CLAVE PARA ven_ins:
        // Asegúrate de que los insumos se sumen correctamente y se excluya el autoconsumo.
        // ****************************************************************************************************
        $ven_ins = DB::table('cpe_cabecera as cc')
            ->select(
                DB::raw('SUM(cd.cdecan * r.rec_cant) as TOT_INS'), // Cantidad vendida del producto final * cantidad de insumo en receta
                'p_ins.pronom as pronom' // Nombre del insumo
            )
            ->join('cpe_detalle as cd', 'cd.IdCpe_cabecera', '=', 'cc.IdCpe_cabecera')
            // Unir a la tabla de recetas usando el IdProducto de cpe_detalle (que es el producto final)
            ->join('recetas as r', 'r.prod_id', '=', 'cd.IdProducto')
            // Unir a productos para obtener el nombre del insumo
            ->join('productos as p_ins', 'p_ins.IdProducto', '=', 'r.prod_insu')
            ->where(function ($query) {
                $query->whereIn('cc.tdocod', ['01', '03', '13']); // Factura, Boleta, Nota de Venta
            })
            ->where('cc.id_turno', $id)
            ->whereNull('cc.ccabaj')
            ->where('cc.tdocod', '!=', '99') // Excluir autoconsumo
            ->groupBy('p_ins.pronom') // Agrupar por el nombre del insumo
            ->get();


        // Resto de consultas (cantidadboletas, cantidadfacturas, cantidadnotas, maymennotas, etc.):
        // Mantienen las exclusiones de tdocod=99 y las inicializaciones de arrays vacíos.

        $gastos = DB::table('gastos_cabecera as gc')
        ->join('usuario_gastos as tur','gc.gast_cab_id','tur.gast_cab_id')
        ->where('id_turno',$turno)
        ->where('est_gasto','Registrado')
        ->where('referencia','GASTO')
        ->sum('total_gast');


        $ingresos = DB::table('gastos_cabecera as gc')
        ->join('usuario_gastos as tur','gc.gast_cab_id','tur.gast_cab_id')
        ->where('est_gasto','Registrado')
        ->where('referencia','INGRESO')
        ->sum('total_gast');

        // Las siguientes consultas también deben inicializar los arrays si es posible que no tengan resultados
        $cantidadboletas = DB::table('cpe_cabecera as cp')
        ->join('usuario_facturacion as uf','uf.IdCpe_cabecera','cp.IdCpe_cabecera')
        ->where('uf.id_turno',$turno)
        ->where('tdocod','03')
        ->whereNull('ccabaj')
        ->where('cp.tdocod', '!=', '99') // Excluir autoconsumo
        ->count();

        $cantidadfacturas = DB::table('cpe_cabecera as cp')
        ->join('usuario_facturacion as uf','uf.IdCpe_cabecera','cp.IdCpe_cabecera')
        ->where('uf.id_turno',$turno)
        ->where('tdocod','01')
        ->whereNull('ccabaj')
        ->where('cp.tdocod', '!=', '99') // Excluir autoconsumo
        ->count();

        $cantidadnotas = DB::table('cpe_cabecera as cp')
        ->join('usuario_facturacion as uf','uf.IdCpe_cabecera','cp.IdCpe_cabecera')
        ->where('uf.id_turno',$turno)
        ->where('tdocod','13')
        ->whereNull('ccabaj')
        ->where('cp.tdocod', '!=', '99') // Excluir autoconsumo
        ->count();


        $maymennotas = DB::table('cpe_cabecera as cp')
        ->join('usuario_facturacion as uf','uf.IdCpe_cabecera','cp.IdCpe_cabecera')
        ->where('uf.id_turno',$turno)
        ->where('tdocod','13')
        ->whereNull('ccabaj')
        ->where('cp.tdocod', '!=', '99') // Excluir autoconsumo
        ->get();

        $maymenboletas = DB::table('cpe_cabecera as cp')
        ->join('usuario_facturacion as uf','uf.IdCpe_cabecera','cp.IdCpe_cabecera')
        ->where('uf.id_turno',$turno)
        ->where('tdocod','03')
        ->whereNull('ccabaj')
        ->where('cp.tdocod', '!=', '99') // Excluir autoconsumo
        ->get();

        $maymenfacturas = DB::table('cpe_cabecera as cp')
        ->join('usuario_facturacion as uf','uf.IdCpe_cabecera','cp.IdCpe_cabecera')
        ->where('uf.id_turno',$turno)
        ->where('tdocod','01')
        ->whereNull('ccabaj')
        ->where('cp.tdocod', '!=', '99') // Excluir autoconsumo
        ->get();


        $mennot = "";
        $maynot = "";
        $menbol = "";
        $maybol = "";
        $menfac = "";
        $mayfac = "";

        $arraynot = []; // Inicializar array
        foreach ($maymennotas as $not) {
            $arraynot[] = $not->serdoc.'-'.$not->numdoc;
        }

        $arraybol = []; // Inicializar array
        foreach ($maymenboletas as $bol) {
            $arraybol[] = $bol->serdoc.'-'.$bol->numdoc;
        }

        $arrayfac = []; // Inicializar array
        foreach ($maymenfacturas as $fac) {
            $arrayfac[] = $fac->serdoc.'-'.$fac->numdoc;
        }

        if(!empty($arraynot)){ // Usar !empty() para verificar si el array no está vacío
            $mennot = min($arraynot);
            $maynot = max($arraynot);
        }

        if(!empty($arraybol)){ // Usar !empty()
            $menbol = min($arraybol);
            $maybol = max($arraybol);
        }

        if(!empty($arrayfac)){ // Usar !empty()
            $menfac = min($arrayfac);
            $mayfac = max($arrayfac);
        }

        

        return view('formatos_comprobantes.ticket_cajaturno_productos',compact('datos','comprobantes','empresa','sucursal','gastos','ingresos','cajero','cantidadnotas','cantidadfacturas','cantidadboletas','maymennotas','maymenfacturas','maymenboletas','menbol','maybol','mennot','maynot','menfac','mayfac','medios_reg','medios','ven_prod','ven_ins'));

    }

    public function imprimirDenominaciones($turno)
    {
        $datos = DB::table('turnos')->where('id_turno', $turno)->first();
        if (!$datos) {
            abort(404, 'Turno no encontrado.');
        }

        $sucursal = DB::table('empresa_negocios')->where('id_empresa_negocio', $datos->id_empresa_negocio)->first();
        $empresa = Empresa::findOrFail($sucursal->IdEmpresa);
        $cajero = DB::table('turnos as t')
            ->join('users as u', 'u.IdUsuario', 't.IdUsuario')
            ->where('t.id_turno', $turno)
            ->first();

        $denominaciones = [
            'cant_m_10_centimos' => $datos->cant_m_10_centimos,
            'cant_m_20_centimos' => $datos->cant_m_20_centimos,
            'cant_m_50_centimos' => $datos->cant_m_50_centimos,
            'cant_m_1_sol'       => $datos->cant_m_1_sol,
            'cant_m_2_soles'     => $datos->cant_m_2_soles,
            'cant_m_5_soles'     => $datos->cant_m_5_soles,
            'cant_c_10_soles'    => $datos->cant_c_10_soles,
            'cant_c_20_soles'    => $datos->cant_c_20_soles,
            'cant_c_50_soles'    => $datos->cant_c_50_soles,
            'cant_c_100_soles'   => $datos->cant_c_100_soles,
            'cant_c_200_soles'   => $datos->cant_c_200_soles,
        ];

        $ruta_redirect = '/caja';
        return view('formatos_comprobantes.denominaciones', compact('denominaciones', 'turno', 'ruta_redirect', 'empresa', 'cajero', 'datos'));
    }

    public function imprimirAutoconsumoTurno($id)
    {
        $turno = $id;

        $datos = DB::table('turnos')->where('id_turno', $turno)->first();
        $sucursal = DB::table('empresa_negocios')->where('id_empresa_negocio', $datos->id_empresa_negocio)->first();
        $empresa = Empresa::findOrFail($sucursal->IdEmpresa);
        $cajero = DB::table('turnos as t')->join('users as u', 'u.IdUsuario', 't.IdUsuario')->where('t.id_turno', $turno)->first();

        // Obtener los autoconsumos para el turno específico
        $autoconsumos = DB::table('cpe_cabecera as cc')
            ->select('cc.tdocod', 'td.tdodes', 'cc.serdoc', 'cc.numdoc', 'cc.ccaitv', 'cc.ccandi', 'cc.ccanom', 'cc.fecha_hora')
            ->join('tipo_documento as td', 'cc.tdocod', '=', 'td.tdocod')
            ->where('cc.id_turno', $turno)
            ->where('cc.tdocod', '99') // Filtro clave para autoconsumo
            ->whereNull('cc.ccabaj') // Asegurarse de que no estén anulados
            ->orderBy('cc.fecha_hora', 'asc')
            ->get();

        // Sumar el total de autoconsumos
        $totalAutoconsumos = $autoconsumos->sum('ccaitv');

        // Puedes pasar más datos si los necesitas en el reporte,
        // por ejemplo, detalles de productos de autoconsumo si los tuvieras relacionados.
        // Por ahora, nos enfocamos en el total y los documentos.


        $ven_prod_autoconsumo = DB::table('cpe_cabecera as cc')
            ->select(
                DB::raw('SUM(cd.cdecan) as CANTIDAD'),
                DB::raw('p_base.pronom as pronom'),
                DB::raw('p_base.propun as precio_unitario') // <-- Añadir esto
            )
            ->join('cpe_detalle as cd', 'cd.IdCpe_cabecera', '=', 'cc.IdCpe_cabecera')
            ->leftJoin('productos as p_base', function($join) {
                $join->on('p_base.IdProducto', '=', DB::raw('COALESCE(cd.IdProducto_rel, cd.IdProducto)'));
            })
            ->where('cc.id_turno', $turno)
            ->where('cc.tdocod', '99')
            ->whereNull('cc.ccabaj')
            ->groupBy('p_base.pronom', 'p_base.propun') // <-- Agrupar también por precio_unitario
            ->get();

        return view('formatos_comprobantes.ticket_cajaturno_autoconsumo', compact(
            'datos',
            'empresa',
            'sucursal',
            'cajero',
            'autoconsumos',
            'totalAutoconsumos',
            'ven_prod_autoconsumo'
        ));
    }

    
    public function imprimirturno($id){

    $turno = $id;
    // Definimos los documentos permitidos para todo el reporte
    $docs_permitidos = ['01', '03', '07', '08', '13'];

    $datos = DB::table('turnos')->where('id_turno', $turno)->first();
    $sucursal = DB::table('empresa_negocios')->where('id_empresa_negocio', $datos->id_empresa_negocio)->first();
    $empresa = Empresa::findOrFail($sucursal->IdEmpresa);
    $cajero = DB::table('turnos as t')->join('users as u', 'u.IdUsuario', 't.IdUsuario')->where('t.id_turno', $turno)->first();

    // 1. MEDIOS DE PAGO (Solo documentos permitidos)
    $medios = DB::table('venta_medio_pago as vmp')
        ->select('vmp.id_med_pag', 'nom_med_pag', DB::raw('SUM(monto) as monto'))
        ->join('medios_pagos as mp', 'mp.id_med_pag', 'vmp.id_med_pag')
        ->leftjoin('cpe_cabecera as cpe', 'cpe.IdCpe_cabecera', 'vmp.IdCpe_cabecera')
        ->where('vmp.id_turno', $turno)
        ->whereNull('ccabaj')
        ->whereIn('cpe.tdocod', $docs_permitidos) // FILTRO GLOBAL
        ->groupby('vmp.id_med_pag')
        ->get();

    $medios_reg = DB::table('turno_medio_pago as vmp')
        ->select('vmp.id_med_pag', 'nom_med_pag', DB::raw('SUM(monto) as monto'))
        ->join('medios_pagos as mp', 'mp.id_med_pag', 'vmp.id_med_pag')
        ->where('vmp.id_turno', $turno)
        ->groupby('vmp.id_med_pag')
        ->get();

    // 2. RESUMEN POR TIPO DE COMPROBANTE (Solo permitidos)
    $comprobantes = DB::table('venta_medio_pago as vmp')
        ->select('tdodes', DB::raw('SUM(monto) as monto'), DB::raw('count(*) as cantidad'))
        ->join('cpe_cabecera as cpe', 'cpe.IdCpe_cabecera', 'vmp.IdCpe_cabecera')
        ->join('tipo_documento as td', 'td.tdocod', 'cpe.tdocod')
        ->where('vmp.id_turno', $turno)
        ->whereNull('ccabaj')
        ->whereIn('cpe.tdocod', $docs_permitidos) // FILTRO GLOBAL
        ->groupby('cpe.tdocod')
        ->get();

    // 3. VENTAS POR PRODUCTO (Solo permitidos)
    $ven_prod = DB::table('cpe_cabecera')
        ->select(DB::RAW('sum(cpe_detalle.cdevve) as TOTAL'), DB::RAW('sum(cpe_detalle.cdecan*factor) as CANTIDAD'), 'productos.pronom')
        ->join('cpe_detalle', 'cpe_detalle.IdCpe_cabecera', 'cpe_cabecera.IdCpe_cabecera')
        ->leftjoin('productos', 'productos.IdProducto', 'cpe_detalle.IdProducto_rel')
        ->whereIn('cpe_cabecera.tdocod', $docs_permitidos) // Simplificado con whereIn
        ->where('id_turno', $id)
        ->whereNull('ccabaj')
        ->groupby('IdProducto_rel')
        ->get();

    // GASTOS E INGRESOS (Se mantienen igual ya que dependen del turno)
    $gastos = DB::table('gastos_cabecera as gc')
        ->join('usuario_gastos as tur', 'gc.gast_cab_id', 'tur.gast_cab_id')
        ->where('id_turno', $turno)
        ->where('est_gasto', 'Registrado')
        ->where('referencia', 'GASTO')
        ->sum('total_gast');

    $ingresos = DB::table('gastos_cabecera as gc')
        ->join('usuario_gastos as tur', 'gc.gast_cab_id', 'tur.gast_cab_id')
        ->where('id_turno', $turno)
        ->where('est_gasto', 'Registrado')
        ->where('referencia', 'INGRESO')
        ->sum('total_gast');

    // 4. CANTIDADES ESPECÍFICAS (Filtros directos)
    $cantidadboletas = DB::table('cpe_cabecera as cp')
        ->join('usuario_facturacion as uf', 'uf.IdCpe_cabecera', 'cp.IdCpe_cabecera')
        ->where('uf.id_turno', $turno)
        ->where('tdocod', '03')
        ->whereNull('ccabaj')
        ->count();

    $cantidadfacturas = DB::table('cpe_cabecera as cp')
        ->join('usuario_facturacion as uf', 'uf.IdCpe_cabecera', 'cp.IdCpe_cabecera')
        ->where('uf.id_turno', $turno)
        ->where('tdocod', '01')
        ->whereNull('ccabaj')
        ->count();

    $cantidadnotas = DB::table('cpe_cabecera as cp')
        ->join('usuario_facturacion as uf', 'uf.IdCpe_cabecera', 'cp.IdCpe_cabecera')
        ->where('uf.id_turno', $turno)
        ->where('tdocod', '13')
        ->whereNull('ccabaj')
        ->count();

    // 5. RANGOS (MAYOR Y MENOR)
    $maymennotas = DB::table('cpe_cabecera as cp')->join('usuario_facturacion as uf', 'uf.IdCpe_cabecera', 'cp.IdCpe_cabecera')
        ->where('uf.id_turno', $turno)->where('tdocod', '13')->whereNull('ccabaj')->get();

    $maymenboletas = DB::table('cpe_cabecera as cp')->join('usuario_facturacion as uf', 'uf.IdCpe_cabecera', 'cp.IdCpe_cabecera')
        ->where('uf.id_turno', $turno)->where('tdocod', '03')->whereNull('ccabaj')->get();

    $maymenfacturas = DB::table('cpe_cabecera as cp')->join('usuario_facturacion as uf', 'uf.IdCpe_cabecera', 'cp.IdCpe_cabecera')
        ->where('uf.id_turno', $turno)->where('tdocod', '01')->whereNull('ccabaj')->get();

    // Lógica de rangos (Min/Max)
    $mennot = $maynot = $menbol = $maybol = $menfac = $mayfac = "";

    $arraynot = []; foreach ($maymennotas as $not) { $arraynot[] = $not->serdoc.'-'.$not->numdoc; }
    $arraybol = []; foreach ($maymenboletas as $bol) { $arraybol[] = $bol->serdoc.'-'.$bol->numdoc; }
    $arrayfac = []; foreach ($maymenfacturas as $fac) { $arrayfac[] = $fac->serdoc.'-'.$fac->numdoc; }

    if(!empty($arraynot)){ $mennot = min($arraynot); $maynot = max($arraynot); }
    if(!empty($arraybol)){ $menbol = min($arraybol); $maybol = max($arraybol); }
    if(!empty($arrayfac)){ $menfac = min($arrayfac); $mayfac = max($arrayfac); }

    $efectivo = DB::table('cpe_cabecera as cp')
        ->join('usuario_facturacion as uf', 'uf.IdCpe_cabecera', 'cp.IdCpe_cabecera')
        ->where('uf.id_turno', $turno)
        ->whereNull('cp.ccabaj')
        ->whereIn('cp.tdocod', $docs_permitidos)
        ->sum('efectivo');

    $visa = DB::table('cpe_cabecera as cp')
        ->join('usuario_facturacion as uf', 'uf.IdCpe_cabecera', 'cp.IdCpe_cabecera')
        ->where('uf.id_turno', $turno)
        ->whereNull('cp.ccabaj')
        ->whereIn('cp.tdocod', $docs_permitidos)
        ->sum('visa');

    $mastercard = DB::table('cpe_cabecera as cp')
        ->join('usuario_facturacion as uf', 'uf.IdCpe_cabecera', 'cp.IdCpe_cabecera')
        ->where('uf.id_turno', $turno)
        ->whereNull('cp.ccabaj')
        ->whereIn('cp.tdocod', $docs_permitidos)
        ->sum('mastercard');

    $transferencia = DB::table('cpe_cabecera as cp')
        ->join('usuario_facturacion as uf', 'uf.IdCpe_cabecera', 'cp.IdCpe_cabecera')
        ->where('uf.id_turno', $turno)
        ->whereNull('cp.ccabaj')
        ->whereIn('cp.tdocod', $docs_permitidos)
        ->sum('transferencia');

    $cantidadcredito = DB::table('cpe_cabecera as cp')
        ->join('usuario_facturacion as uf', 'uf.IdCpe_cabecera', 'cp.IdCpe_cabecera')
        ->where('uf.id_turno', $turno)
        ->where('cp.estadopago', 'CREDITO')
        ->whereNull('cp.ccabaj')
        ->whereIn('cp.tdocod', $docs_permitidos)
        ->count();

    $totalcredito = DB::table('cpe_cabecera as cp')
        ->join('usuario_facturacion as uf', 'uf.IdCpe_cabecera', 'cp.IdCpe_cabecera')
        ->where('uf.id_turno', $turno)
        ->where('cp.estadopago', 'CREDITO')
        ->whereNull('cp.ccabaj')
        ->whereIn('cp.tdocod', $docs_permitidos)
        ->sum('totalcredito');

    return view('formatos_comprobantes.ticket_cajaturno', compact(
        'datos', 'comprobantes', 'empresa', 'sucursal', 'gastos', 'ingresos', 'cajero', 
        'cantidadnotas', 'cantidadfacturas', 'cantidadboletas', 'turno', 
        'menbol', 'maybol', 'mennot', 'maynot', 'menfac', 'mayfac', 
        'medios_reg', 'medios', 'ven_prod',
        'efectivo', 'visa', 'mastercard', 'transferencia',
        'cantidadcredito', 'totalcredito'
    ));
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
      $turnos=turnos::findOrFail($id);
      return view('empresas.turnos.edit',compact('turnos'));
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
      $turnos=turnos::findOrFail($id);
      $turnos->id_empresa_negocio = Auth::user()->id_empresa_negocio;
      $turnos->turno = $request->get('txtturno');
      $turnos->usuario = Auth::user()->IdUsuario;
      $turnos->update();
      return Redirect::to('/turnos');

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
       $turnos=turnos::findOrFail($id);
     
      $turnos->estado = 'Cerrado';
      $turnos->update();
      return Redirect::to('/turnos');
    }

    public function movimientosturno($turno){

        $datos = DB::tABLE('turnos')->where('id_turno',$turno)->first();

        $usuario = DB::tABLE('users')->where('IdUsuario',$datos->IdUsuario)->first();

        $gastos = DB::tABLE('gastos_cabecera')
        ->leftjoin('gastos_detalle','gastos_cabecera.gast_cab_id','gastos_detalle.gast_cab_id')
        ->leftjoin('moneda','gastos_cabecera.mon_id','moneda.moncod')
        ->leftjoin('tipo_gastos','tipo_gastos.tip_gas_id','gastos_detalle.tip_gas_id')
        ->leftjoin('tipo_documento','gastos_cabecera.tdocod','tipo_documento.tdocod')
        ->join('usuario_gastos','usuario_gastos.gast_cab_id','gastos_cabecera.gast_cab_id')
        ->where('gastos_cabecera.id_empresa_negocio',$datos->id_empresa_negocio)
        ->where('tipo_movimiento','GASTO')
        ->where('est_gasto','Registrado')
        ->where('id_turno',$turno)
        ->orderby('gastos_cabecera.gast_Cab_id','desc')
        ->get();

        $grup_gas = DB::tABLE('gastos_cabecera')->select('tip_gas_nom',DB::RAW('sum(gastos_detalle.total) as total'))
         ->leftjoin('gastos_detalle','gastos_cabecera.gast_cab_id','gastos_detalle.gast_cab_id')
         ->join('usuario_gastos','usuario_gastos.gast_cab_id','gastos_cabecera.gast_cab_id')
        ->leftjoin('tipo_gastos','tipo_gastos.tip_gas_id','gastos_detalle.tip_gas_id')
        ->where('gastos_cabecera.id_empresa_negocio',$datos->id_empresa_negocio)
        ->where('tipo_movimiento','GASTO')
        ->where('est_gasto','Registrado')
        ->where('id_turno',$turno)
        ->orderby('gastos_cabecera.gast_Cab_id','desc')
        ->groupby('tipo_gastos.tip_gas_id')
        ->get();

   
        $gastoseliminados = DB::tABLE('gastos_cabecera')
        ->leftjoin('gastos_detalle','gastos_cabecera.gast_cab_id','gastos_detalle.gast_cab_id')
        ->leftjoin('moneda','gastos_cabecera.mon_id','moneda.moncod')
        ->leftjoin('tipo_gastos','tipo_gastos.tip_gas_id','gastos_detalle.tip_gas_id')
        ->leftjoin('tipo_documento','gastos_cabecera.tdocod','tipo_documento.tdocod')
        ->leftjoin('usuario_gastos','usuario_gastos.gast_cab_id','gastos_cabecera.gast_cab_id')
        ->where('gastos_cabecera.id_empresa_negocio',$datos->id_empresa_negocio)
        ->where('tipo_movimiento','GASTO')
        ->where('est_gasto','Eliminado')
        ->where('id_turno',$turno)
        ->orderby('gastos_cabecera.gast_Cab_id','desc')
        ->get();

        $grup_gas_eli = DB::tABLE('gastos_cabecera')->select('tip_gas_nom',DB::RAW('sum(gastos_detalle.total) as total'))
        ->leftjoin('gastos_detalle','gastos_cabecera.gast_cab_id','gastos_detalle.gast_cab_id')
        ->join('usuario_gastos','usuario_gastos.gast_cab_id','gastos_cabecera.gast_cab_id')
        ->where('gastos_cabecera.id_empresa_negocio',$datos->id_empresa_negocio)
        ->leftjoin('tipo_gastos','tipo_gastos.tip_gas_id','gastos_detalle.tip_gas_id')
        ->where('tipo_movimiento','GASTO')
        ->where('est_gasto','Eliminado')
        ->where('id_turno',$turno)
        ->orderby('gastos_cabecera.gast_Cab_id','desc')
        ->groupby('tipo_gastos.tip_gas_id')
        ->get();

        $ingresos = DB::tABLE('gastos_cabecera')
        ->leftjoin('gastos_detalle','gastos_cabecera.gast_cab_id','gastos_detalle.gast_cab_id')
        ->leftjoin('moneda','gastos_cabecera.mon_id','moneda.moncod')
        ->leftjoin('tipo_gastos','tipo_gastos.tip_gas_id','gastos_detalle.tip_gas_id')
        ->leftjoin('tipo_documento','gastos_cabecera.tdocod','tipo_documento.tdocod')

        ->join('usuario_gastos','usuario_gastos.gast_cab_id','gastos_cabecera.gast_cab_id')

        ->where('gastos_cabecera.id_empresa_negocio',$datos->id_empresa_negocio)
        ->where('tipo_movimiento','INGRESO')
        ->where('est_gasto','Registrado')
        ->where('id_turno',$turno)
        ->orderby('gastos_cabecera.gast_Cab_id','desc')
        ->get();

          $grup_ing =DB::tABLE('gastos_cabecera')->select('tip_gas_nom',DB::RAW('sum(gastos_detalle.total) as total'))
        ->leftjoin('gastos_detalle','gastos_cabecera.gast_cab_id','gastos_detalle.gast_cab_id')
        ->join('usuario_gastos','usuario_gastos.gast_cab_id','gastos_cabecera.gast_cab_id')
        ->leftjoin('tipo_gastos','tipo_gastos.tip_gas_id','gastos_detalle.tip_gas_id')
        ->where('gastos_cabecera.id_empresa_negocio',$datos->id_empresa_negocio)
        ->where('tipo_movimiento','INGRESO')
        ->where('est_gasto','Registrado')
        ->where('id_turno',$turno)
        ->orderby('gastos_cabecera.gast_Cab_id','desc')
        ->groupby('tipo_gastos.tip_gas_id')
        ->get();


         $ingresoseliminados = DB::tABLE('gastos_cabecera')
        ->leftjoin('gastos_detalle','gastos_cabecera.gast_cab_id','gastos_detalle.gast_cab_id')
        ->leftjoin('moneda','gastos_cabecera.mon_id','moneda.moncod')
        ->leftjoin('tipo_gastos','tipo_gastos.tip_gas_id','gastos_detalle.tip_gas_id')
        ->leftjoin('tipo_documento','gastos_cabecera.tdocod','tipo_documento.tdocod')
        ->leftjoin('usuario_gastos','usuario_gastos.gast_cab_id','gastos_cabecera.gast_cab_id')
        ->where('gastos_cabecera.id_empresa_negocio',Auth::user()->id_empresa_negocio)
        ->where('tipo_movimiento','INGRESO')
        ->where('est_gasto','Eliminado')
        ->where('id_turno',$turno)
        ->orderby('gastos_cabecera.gast_Cab_id','desc')
        ->get();


         $grup_ing_eli = DB::tABLE('gastos_cabecera')
        ->leftjoin('gastos_detalle','gastos_cabecera.gast_cab_id','gastos_detalle.gast_cab_id')
        ->join('usuario_gastos','usuario_gastos.gast_cab_id','gastos_cabecera.gast_cab_id')
        ->leftjoin('tipo_gastos','tipo_gastos.tip_gas_id','gastos_detalle.tip_gas_id')
        ->where('gastos_cabecera.id_empresa_negocio',Auth::user()->id_empresa_negocio)
        ->where('tipo_movimiento','INGRESO')
        ->where('est_gasto','Eliminado')
        ->where('id_turno',$turno)
        ->orderby('gastos_cabecera.gast_Cab_id','desc')
        ->groupby('tipo_gastos.tip_gas_id')
        ->get();

        $totalgas = 0;
        $totaling = 0;
        $totalgaseli =0;
        $totalingeli =0;

        foreach ($gastos as $gas) {
            $totalgas = $totalgas + $gas->total;
        }

        foreach ($ingresos as $ing) {
            
            $totaling = $totaling + $ing->total;

        }

        foreach ($gastoseliminados as $gaseli) {

            $totalgaseli = $totalgaseli + $gaseli->total;
        }

        foreach ($ingresoseliminados as $ingeli) {
            
            $totalingeli = $totalingeli + $ingeli->total;
        }

        return view('empresas.turnos.movimientosturno',compact('gastos','gastoseliminados','ingresoseliminados','ingresos','totalgas','totaling','totalgaseli','totalingeli','datos','usuario','grup_ing','grup_gas','grup_gas_eli','grup_ing_eli'));

    }

    public function ventasturno(Request $request,$turno){

        $cliente = $request->get('cliente');
        $documento = $request->get('documento');

        $ser = substr($documento,strpos($documento,'-')-4,4);
        $num = substr($documento,strpos($documento,'-')+1,8);


        $datos = DB::tABLE('turnos')->where('id_turno',$turno)->first();

        $usuario = DB::tABLE('users')->where('IdUsuario',$datos->IdUsuario)->first();
        
        
        $gastos = DB::tABLE('gastos_cabecera')
        ->leftjoin('gastos_detalle','gastos_cabecera.gast_cab_id','gastos_detalle.gast_cab_id')
        ->leftjoin('moneda','gastos_cabecera.mon_id','moneda.moncod')
        ->leftjoin('tipo_gastos','tipo_gastos.tip_gas_id','gastos_detalle.tip_gas_id')
        ->leftjoin('tipo_documento','gastos_cabecera.tdocod','tipo_documento.tdocod')
        ->join('usuario_gastos','usuario_gastos.gast_cab_id','gastos_cabecera.gast_cab_id')
        ->where('gastos_cabecera.id_empresa_negocio',$datos->id_empresa_negocio)
        ->where('tipo_movimiento','GASTO')
        ->where('est_gasto','Registrado')
        ->where('id_turno',$turno)
        ->orderby('gastos_cabecera.gast_Cab_id','desc')
        ->get();

        $grup_gas = DB::tABLE('gastos_cabecera')->select('tip_gas_nom',DB::RAW('sum(gastos_detalle.total) as total'))
         ->leftjoin('gastos_detalle','gastos_cabecera.gast_cab_id','gastos_detalle.gast_cab_id')
         ->join('usuario_gastos','usuario_gastos.gast_cab_id','gastos_cabecera.gast_cab_id')
        ->leftjoin('tipo_gastos','tipo_gastos.tip_gas_id','gastos_detalle.tip_gas_id')
        ->where('gastos_cabecera.id_empresa_negocio',$datos->id_empresa_negocio)
        ->where('tipo_movimiento','GASTO')
        ->where('est_gasto','Registrado')
        ->where('id_turno',$turno)
        ->orderby('gastos_cabecera.gast_Cab_id','desc')
        ->groupby('tipo_gastos.tip_gas_id')
        ->get();

   
        $gastoseliminados = DB::tABLE('gastos_cabecera')
        ->leftjoin('gastos_detalle','gastos_cabecera.gast_cab_id','gastos_detalle.gast_cab_id')
        ->leftjoin('moneda','gastos_cabecera.mon_id','moneda.moncod')
        ->leftjoin('tipo_gastos','tipo_gastos.tip_gas_id','gastos_detalle.tip_gas_id')
        ->leftjoin('tipo_documento','gastos_cabecera.tdocod','tipo_documento.tdocod')
        ->leftjoin('usuario_gastos','usuario_gastos.gast_cab_id','gastos_cabecera.gast_cab_id')
        ->where('gastos_cabecera.id_empresa_negocio',$datos->id_empresa_negocio)
        ->where('tipo_movimiento','GASTO')
        ->where('est_gasto','Eliminado')
        ->where('id_turno',$turno)
        ->orderby('gastos_cabecera.gast_Cab_id','desc')
        ->get();

        $grup_gas_eli = DB::tABLE('gastos_cabecera')->select('tip_gas_nom',DB::RAW('sum(gastos_detalle.total) as total'))
        ->leftjoin('gastos_detalle','gastos_cabecera.gast_cab_id','gastos_detalle.gast_cab_id')
        ->join('usuario_gastos','usuario_gastos.gast_cab_id','gastos_cabecera.gast_cab_id')
        ->where('gastos_cabecera.id_empresa_negocio',$datos->id_empresa_negocio)
        ->leftjoin('tipo_gastos','tipo_gastos.tip_gas_id','gastos_detalle.tip_gas_id')
        ->where('tipo_movimiento','GASTO')
        ->where('est_gasto','Eliminado')
        ->where('id_turno',$turno)
        ->orderby('gastos_cabecera.gast_Cab_id','desc')
        ->groupby('tipo_gastos.tip_gas_id')
        ->get();

        $ingresos = DB::tABLE('gastos_cabecera')
        ->leftjoin('gastos_detalle','gastos_cabecera.gast_cab_id','gastos_detalle.gast_cab_id')
        ->leftjoin('moneda','gastos_cabecera.mon_id','moneda.moncod')
        ->leftjoin('tipo_gastos','tipo_gastos.tip_gas_id','gastos_detalle.tip_gas_id')
        ->leftjoin('tipo_documento','gastos_cabecera.tdocod','tipo_documento.tdocod')

        ->join('usuario_gastos','usuario_gastos.gast_cab_id','gastos_cabecera.gast_cab_id')

        ->where('gastos_cabecera.id_empresa_negocio',$datos->id_empresa_negocio)
        ->where('tipo_movimiento','INGRESO')
        ->where('est_gasto','Registrado')
        ->where('id_turno',$turno)
        ->orderby('gastos_cabecera.gast_Cab_id','desc')
        ->get();

          $grup_ing =DB::tABLE('gastos_cabecera')->select('tip_gas_nom',DB::RAW('sum(gastos_detalle.total) as total'))
        ->leftjoin('gastos_detalle','gastos_cabecera.gast_cab_id','gastos_detalle.gast_cab_id')
        ->join('usuario_gastos','usuario_gastos.gast_cab_id','gastos_cabecera.gast_cab_id')
        ->leftjoin('tipo_gastos','tipo_gastos.tip_gas_id','gastos_detalle.tip_gas_id')
        ->where('gastos_cabecera.id_empresa_negocio',$datos->id_empresa_negocio)
        ->where('tipo_movimiento','INGRESO')
        ->where('est_gasto','Registrado')
        ->where('id_turno',$turno)
        ->orderby('gastos_cabecera.gast_Cab_id','desc')
        ->groupby('tipo_gastos.tip_gas_id')
        ->get();


         $ingresoseliminados = DB::tABLE('gastos_cabecera')
        ->leftjoin('gastos_detalle','gastos_cabecera.gast_cab_id','gastos_detalle.gast_cab_id')
        ->leftjoin('moneda','gastos_cabecera.mon_id','moneda.moncod')
        ->leftjoin('tipo_gastos','tipo_gastos.tip_gas_id','gastos_detalle.tip_gas_id')
        ->leftjoin('tipo_documento','gastos_cabecera.tdocod','tipo_documento.tdocod')
        ->leftjoin('usuario_gastos','usuario_gastos.gast_cab_id','gastos_cabecera.gast_cab_id')
        ->where('gastos_cabecera.id_empresa_negocio',Auth::user()->id_empresa_negocio)
        ->where('tipo_movimiento','INGRESO')
        ->where('est_gasto','Eliminado')
        ->where('id_turno',$turno)
        ->orderby('gastos_cabecera.gast_Cab_id','desc')
        ->get();


         $grup_ing_eli = DB::tABLE('gastos_cabecera')
        ->leftjoin('gastos_detalle','gastos_cabecera.gast_cab_id','gastos_detalle.gast_cab_id')
        ->join('usuario_gastos','usuario_gastos.gast_cab_id','gastos_cabecera.gast_cab_id')
        ->leftjoin('tipo_gastos','tipo_gastos.tip_gas_id','gastos_detalle.tip_gas_id')
        ->where('gastos_cabecera.id_empresa_negocio',Auth::user()->id_empresa_negocio)
        ->where('tipo_movimiento','INGRESO')
        ->where('est_gasto','Eliminado')
        ->where('id_turno',$turno)
        ->orderby('gastos_cabecera.gast_Cab_id','desc')
        ->groupby('tipo_gastos.tip_gas_id')
        ->get();
        
          $compras = DB::tABLE('compras_cabecera')
        ->leftjoin('proveedor','compras_cabecera.prov_id','proveedor.prov_id')
        ->leftjoin('moneda','compras_cabecera.mon_id','moneda.moncod')
        ->leftjoin('tipo_documento','compras_cabecera.tdocod','tipo_documento.tdocod')
         ->where('id_turno',$turno)
        ->where('est_compra','Registrado')
        ->where('compras_cabecera.tdocod','!=','80')
        ->sum('total_com');

      

          $efectivo = DB::tABLE('cpe_cabecera')
          ->join('venta_medio_pago','venta_medio_pago.IdCpe_cabecera','cpe_cabecera.IdCpe_cabecera')
          ->join('medios_pagos','medios_pagos.id_med_pag','venta_medio_pago.id_med_pag')
          ->whereNull('ccabaj')
          ->where('venta_medio_pago.id_turno',$turno)
          ->where('medios_pagos.predeterminado','1')
          ->sum('monto');


          $otros_medios = DB::tABLE('cpe_cabecera')
          ->join('venta_medio_pago','venta_medio_pago.IdCpe_cabecera','cpe_cabecera.IdCpe_cabecera')
          ->join('medios_pagos','medios_pagos.id_med_pag','venta_medio_pago.id_med_pag')
          ->whereNull('ccabaj')
          ->where('venta_medio_pago.id_turno',$turno)
          ->where('medios_pagos.predeterminado','!=','1')
          ->sum('monto');

            $sum_mp ="";


          $sum_mp = DB::tABLE('cpe_cabecera')->select('nom_med_pag',DB::RAW('SUM(monto) as monto_total'))
          ->join('venta_medio_pago','venta_medio_pago.IdCpe_cabecera','cpe_cabecera.IdCpe_cabecera')
          ->join('medios_pagos','medios_pagos.id_med_pag','venta_medio_pago.id_med_pag')
          ->whereNull('ccabaj')
          ->where('venta_medio_pago.id_turno',$turno)
          ->groupby('venta_medio_pago.id_med_pag')
          ->get();
  
    

            
        $totalgas = 0;
        $totaling = 0;
        $totalgaseli =0;
        $totalingeli =0;

        foreach ($gastos as $gas) {
            $totalgas = $totalgas + $gas->total;
        }

        foreach ($ingresos as $ing) {
            
            $totaling = $totaling + $ing->total;

        }

        foreach ($gastoseliminados as $gaseli) {

            $totalgaseli = $totalgaseli + $gaseli->total;
        }

        foreach ($ingresoseliminados as $ingeli) {
            
            $totalingeli = $totalingeli + $ingeli->total;
        }
        
        $comprobantes = DB::tABLE('cpe_cabecera as cpe_c')->select('cpe_c.ped_id','ccacodsun','ccafem','tdodes','cpe_c.serdoc','cpe_c.numdoc','tdides','cpe_c.ccandi','cpe_c.ccanom','monnom','cpe_c.ccaitv','cpe_c.IdEmpresa','cpe_c.tdocod','cpe_c.IdCpe_cabecera','cpe_c.ccanot','cpe_c.ccabaj','cpe_c.codhash','cpe_c.estado','cpe_c.enviado','totalcontado','totalcredito','clicor','clicor2','clicor3','clicor4','clicorcli','clicorcli2','clicorcli3','clicorcli4','ped_tot')
        ->leftjoin('pedidos','pedidos.ped_id','cpe_c.ped_id')
        ->join('tipo_documento as tip_d','cpe_c.tdocod','=','tip_d.tdocod')
        ->leftjoin('cliente','cliente.clicod','cpe_c.clicod')
        ->join('tipo_documento_identidad as tdi','cpe_c.tdicod','=','tdi.tdicod')
        ->join('moneda as mon','cpe_c.moncod','=','mon.moncod')
        ->join('usuario_facturacion','usuario_facturacion.IdCpe_cabecera','cpe_c.IdCpe_cabecera')
        ->whereNull('cpe_c.ccabaj')
        ->where('usuario_facturacion.id_empresa_negocio',$datos->id_empresa_negocio)
        ->where(function ($query) {
          $query->where('cpe_c.tdocod','01')
              ->orWhere('cpe_c.tdocod','03')
              ->orWhere('cpe_c.tdocod','13');
          })
        ->where(function ($query1) use ($cliente){
            if(!empty($clientes)){
                $query1->where('cpe_c.ccanom','like','%'.$cliente.'%')
                    ->orWhere('cpe_c.ccandi','=',$cliente);
            }
        })
        ->where(function ($query2) use ($documento,$ser,$num){
            if(!empty($documento)){
                $query2->where('serdoc',$ser)
                    ->Where('numdoc',$num);
            }
        })
        ->where('usuario_facturacion.id_turno',$turno)
        ->orderby('cpe_c.ped_id','asc')
        ->paginate(10000);
        

        $total = DB::tABLE('cpe_cabecera as cpe_c')->select('ccacodsun','ccafem','tdodes','cpe_c.serdoc','cpe_c.numdoc','tdides','cpe_c.ccandi','cpe_c.ccanom','monnom','cpe_c.ccaitv','cpe_c.IdEmpresa','cpe_c.tdocod','cpe_c.IdCpe_cabecera','cpe_c.ccanot','cpe_c.ccabaj','cpe_c.codhash','cpe_c.estado','cpe_c.enviado','totalcontado','totalcredito')
        ->join('tipo_documento as tip_d','cpe_c.tdocod','=','tip_d.tdocod')
        ->join('tipo_documento_identidad as tdi','cpe_c.tdicod','=','tdi.tdicod')
        ->join('moneda as mon','cpe_c.moncod','=','mon.moncod')
        ->join('usuario_facturacion','usuario_facturacion.IdCpe_cabecera','cpe_c.IdCpe_cabecera')
        ->whereNull('cpe_c.ccabaj')
       ->where('usuario_facturacion.id_empresa_negocio',$datos->id_empresa_negocio)
        ->where(function ($query) {
          $query->where('cpe_c.tdocod','01')
              ->orWhere('cpe_c.tdocod','03')
              ->orWhere('cpe_c.tdocod','13');
          })
        ->where(function ($query1) use ($cliente){
            if(!empty($clientes)){
                $query1->where('cpe_c.ccanom','like','%'.$cliente.'%')
                    ->orWhere('cpe_c.ccandi','=',$cliente);
            }
        })
        ->where(function ($query2) use ($documento,$ser,$num){
            if(!empty($documento)){
                $query2->where('serdoc',$ser)
                    ->Where('numdoc',$num);
            }
        })
        ->where('estadopago','contado')
        ->where('usuario_facturacion.id_turno',$turno)
        ->sum('ccaitv');
        
        
        $credito = DB::tABLE('cpe_cabecera as cpe_c')->select('ccacodsun','ccafem','tdodes','cpe_c.serdoc','cpe_c.numdoc','tdides','cpe_c.ccandi','cpe_c.ccanom','monnom','cpe_c.ccaitv','cpe_c.IdEmpresa','cpe_c.tdocod','cpe_c.IdCpe_cabecera','cpe_c.ccanot','cpe_c.ccabaj','cpe_c.codhash','cpe_c.estado','cpe_c.enviado','totalcontado','totalcredito')
        ->join('tipo_documento as tip_d','cpe_c.tdocod','=','tip_d.tdocod')
        ->join('tipo_documento_identidad as tdi','cpe_c.tdicod','=','tdi.tdicod')
        ->join('moneda as mon','cpe_c.moncod','=','mon.moncod')
        ->join('usuario_facturacion','usuario_facturacion.IdCpe_cabecera','cpe_c.IdCpe_cabecera')
        ->whereNull('cpe_c.ccabaj')
        ->where('usuario_facturacion.id_empresa_negocio',$datos->id_empresa_negocio)
        ->where(function ($query) {
          $query->where('cpe_c.tdocod','01')
              ->orWhere('cpe_c.tdocod','03')
              ->orWhere('cpe_c.tdocod','13');
          })
        ->where(function ($query1) use ($cliente){
            if(!empty($clientes)){
                $query1->where('cpe_c.ccanom','like','%'.$cliente.'%')
                    ->orWhere('cpe_c.ccandi','=',$cliente);
            }
        })
        ->where('estadopago','credito')
        ->where(function ($query2) use ($documento,$ser,$num){
            if(!empty($documento)){
                $query2->where('serdoc',$ser)
                    ->Where('numdoc',$num);
            }
        })
        ->where('usuario_facturacion.id_turno',$turno)
        ->sum('ccaitv');
                    
  
                 return view('empresas.turnos.ventasturno',compact('efectivo','sum_mp','compras','totalgas','totaling','total','comprobantes','turno','cliente','documento','datos','usuario','documento','credito','otros_medios'));
  


 

    }


  public function ventasturnoexcel(Request $request,$turno){

        $cliente = $request->get('cliente');
        $documento = $request->get('documento');

        $ser = substr($documento,strpos($documento,'-')-4,4);
        $num = substr($documento,strpos($documento,'-')+1,8);


        $datos = DB::tABLE('turnos')->where('id_turno',$turno)->first();

        $usuario = DB::tABLE('users')->where('IdUsuario',$datos->IdUsuario)->first();
        
        
        $gastos = DB::tABLE('gastos_cabecera')
        ->leftjoin('gastos_detalle','gastos_cabecera.gast_cab_id','gastos_detalle.gast_cab_id')
        ->leftjoin('moneda','gastos_cabecera.mon_id','moneda.moncod')
        ->leftjoin('tipo_gastos','tipo_gastos.tip_gas_id','gastos_detalle.tip_gas_id')
        ->leftjoin('tipo_documento','gastos_cabecera.tdocod','tipo_documento.tdocod')
        ->join('usuario_gastos','usuario_gastos.gast_cab_id','gastos_cabecera.gast_cab_id')
        ->where('gastos_cabecera.id_empresa_negocio',$datos->id_empresa_negocio)
        ->where('tipo_movimiento','GASTO')
        ->where('est_gasto','Registrado')
        ->where('id_turno',$turno)
        ->orderby('gastos_cabecera.gast_Cab_id','desc')
        ->get();

        $grup_gas = DB::tABLE('gastos_cabecera')->select('tip_gas_nom',DB::RAW('sum(gastos_detalle.total) as total'))
         ->leftjoin('gastos_detalle','gastos_cabecera.gast_cab_id','gastos_detalle.gast_cab_id')
         ->join('usuario_gastos','usuario_gastos.gast_cab_id','gastos_cabecera.gast_cab_id')
        ->leftjoin('tipo_gastos','tipo_gastos.tip_gas_id','gastos_detalle.tip_gas_id')
        ->where('gastos_cabecera.id_empresa_negocio',$datos->id_empresa_negocio)
        ->where('tipo_movimiento','GASTO')
        ->where('est_gasto','Registrado')
        ->where('id_turno',$turno)
        ->orderby('gastos_cabecera.gast_Cab_id','desc')
        ->groupby('tipo_gastos.tip_gas_id')
        ->get();

   
        $gastoseliminados = DB::tABLE('gastos_cabecera')
        ->leftjoin('gastos_detalle','gastos_cabecera.gast_cab_id','gastos_detalle.gast_cab_id')
        ->leftjoin('moneda','gastos_cabecera.mon_id','moneda.moncod')
        ->leftjoin('tipo_gastos','tipo_gastos.tip_gas_id','gastos_detalle.tip_gas_id')
        ->leftjoin('tipo_documento','gastos_cabecera.tdocod','tipo_documento.tdocod')
        ->leftjoin('usuario_gastos','usuario_gastos.gast_cab_id','gastos_cabecera.gast_cab_id')
        ->where('gastos_cabecera.id_empresa_negocio',$datos->id_empresa_negocio)
        ->where('tipo_movimiento','GASTO')
        ->where('est_gasto','Eliminado')
        ->where('id_turno',$turno)
        ->orderby('gastos_cabecera.gast_Cab_id','desc')
        ->get();

        $grup_gas_eli = DB::tABLE('gastos_cabecera')->select('tip_gas_nom',DB::RAW('sum(gastos_detalle.total) as total'))
        ->leftjoin('gastos_detalle','gastos_cabecera.gast_cab_id','gastos_detalle.gast_cab_id')
        ->join('usuario_gastos','usuario_gastos.gast_cab_id','gastos_cabecera.gast_cab_id')
        ->where('gastos_cabecera.id_empresa_negocio',$datos->id_empresa_negocio)
        ->leftjoin('tipo_gastos','tipo_gastos.tip_gas_id','gastos_detalle.tip_gas_id')
        ->where('tipo_movimiento','GASTO')
        ->where('est_gasto','Eliminado')
        ->where('id_turno',$turno)
        ->orderby('gastos_cabecera.gast_Cab_id','desc')
        ->groupby('tipo_gastos.tip_gas_id')
        ->get();

        $ingresos = DB::tABLE('gastos_cabecera')
        ->leftjoin('gastos_detalle','gastos_cabecera.gast_cab_id','gastos_detalle.gast_cab_id')
        ->leftjoin('moneda','gastos_cabecera.mon_id','moneda.moncod')
        ->leftjoin('tipo_gastos','tipo_gastos.tip_gas_id','gastos_detalle.tip_gas_id')
        ->leftjoin('tipo_documento','gastos_cabecera.tdocod','tipo_documento.tdocod')

        ->join('usuario_gastos','usuario_gastos.gast_cab_id','gastos_cabecera.gast_cab_id')

        ->where('gastos_cabecera.id_empresa_negocio',$datos->id_empresa_negocio)
        ->where('tipo_movimiento','INGRESO')
        ->where('est_gasto','Registrado')
        ->where('id_turno',$turno)
        ->orderby('gastos_cabecera.gast_Cab_id','desc')
        ->get();

          $grup_ing =DB::tABLE('gastos_cabecera')->select('tip_gas_nom',DB::RAW('sum(gastos_detalle.total) as total'))
        ->leftjoin('gastos_detalle','gastos_cabecera.gast_cab_id','gastos_detalle.gast_cab_id')
        ->join('usuario_gastos','usuario_gastos.gast_cab_id','gastos_cabecera.gast_cab_id')
        ->leftjoin('tipo_gastos','tipo_gastos.tip_gas_id','gastos_detalle.tip_gas_id')
        ->where('gastos_cabecera.id_empresa_negocio',$datos->id_empresa_negocio)
        ->where('tipo_movimiento','INGRESO')
        ->where('est_gasto','Registrado')
        ->where('id_turno',$turno)
        ->orderby('gastos_cabecera.gast_Cab_id','desc')
        ->groupby('tipo_gastos.tip_gas_id')
        ->get();


         $ingresoseliminados = DB::tABLE('gastos_cabecera')
        ->leftjoin('gastos_detalle','gastos_cabecera.gast_cab_id','gastos_detalle.gast_cab_id')
        ->leftjoin('moneda','gastos_cabecera.mon_id','moneda.moncod')
        ->leftjoin('tipo_gastos','tipo_gastos.tip_gas_id','gastos_detalle.tip_gas_id')
        ->leftjoin('tipo_documento','gastos_cabecera.tdocod','tipo_documento.tdocod')
        ->leftjoin('usuario_gastos','usuario_gastos.gast_cab_id','gastos_cabecera.gast_cab_id')
        ->where('gastos_cabecera.id_empresa_negocio',Auth::user()->id_empresa_negocio)
        ->where('tipo_movimiento','INGRESO')
        ->where('est_gasto','Eliminado')
        ->where('id_turno',$turno)
        ->orderby('gastos_cabecera.gast_Cab_id','desc')
        ->get();


         $grup_ing_eli = DB::tABLE('gastos_cabecera')
        ->leftjoin('gastos_detalle','gastos_cabecera.gast_cab_id','gastos_detalle.gast_cab_id')
        ->join('usuario_gastos','usuario_gastos.gast_cab_id','gastos_cabecera.gast_cab_id')
        ->leftjoin('tipo_gastos','tipo_gastos.tip_gas_id','gastos_detalle.tip_gas_id')
        ->where('gastos_cabecera.id_empresa_negocio',Auth::user()->id_empresa_negocio)
        ->where('tipo_movimiento','INGRESO')
        ->where('est_gasto','Eliminado')
        ->where('id_turno',$turno)
        ->orderby('gastos_cabecera.gast_Cab_id','desc')
        ->groupby('tipo_gastos.tip_gas_id')
        ->get();
        
          $compras = DB::tABLE('compras_cabecera')
        ->leftjoin('proveedor','compras_cabecera.prov_id','proveedor.prov_id')
        ->leftjoin('moneda','compras_cabecera.mon_id','moneda.moncod')
        ->leftjoin('tipo_documento','compras_cabecera.tdocod','tipo_documento.tdocod')
         ->where('id_turno',$turno)
        ->where('est_compra','Registrado')
        ->where('compras_cabecera.tdocod','!=','80')
        ->sum('total_com');

      

          $efectivo = DB::tABLE('cpe_cabecera')
          ->join('venta_medio_pago','venta_medio_pago.IdCpe_cabecera','cpe_cabecera.IdCpe_cabecera')
          ->join('medios_pagos','medios_pagos.id_med_pag','venta_medio_pago.id_med_pag')
          ->whereNull('ccabaj')
          ->where('venta_medio_pago.id_turno',$turno)
          ->where('medios_pagos.predeterminado','1')
          ->sum('monto');


          $otros_medios = DB::tABLE('cpe_cabecera')
          ->join('venta_medio_pago','venta_medio_pago.IdCpe_cabecera','cpe_cabecera.IdCpe_cabecera')
          ->join('medios_pagos','medios_pagos.id_med_pag','venta_medio_pago.id_med_pag')
          ->whereNull('ccabaj')
          ->where('venta_medio_pago.id_turno',$turno)
          ->where('medios_pagos.predeterminado','!=','1')
          ->sum('monto');

            $sum_mp ="";


          $sum_mp = DB::tABLE('cpe_cabecera')->select('nom_med_pag',DB::RAW('SUM(monto) as monto_total'))
          ->join('venta_medio_pago','venta_medio_pago.IdCpe_cabecera','cpe_cabecera.IdCpe_cabecera')
          ->join('medios_pagos','medios_pagos.id_med_pag','venta_medio_pago.id_med_pag')
          ->whereNull('ccabaj')
          ->where('venta_medio_pago.id_turno',$turno)
          ->groupby('venta_medio_pago.id_med_pag')
          ->get();
  
    

            
        $totalgas = 0;
        $totaling = 0;
        $totalgaseli =0;
        $totalingeli =0;

        foreach ($gastos as $gas) {
            $totalgas = $totalgas + $gas->total;
        }

        foreach ($ingresos as $ing) {
            
            $totaling = $totaling + $ing->total;

        }

        foreach ($gastoseliminados as $gaseli) {

            $totalgaseli = $totalgaseli + $gaseli->total;
        }

        foreach ($ingresoseliminados as $ingeli) {
            
            $totalingeli = $totalingeli + $ingeli->total;
        }
        
        $comprobantes = DB::tABLE('cpe_cabecera as cpe_c')->select('cpe_c.ped_id','ccacodsun','ccafem','tdodes','cpe_c.serdoc','cpe_c.numdoc','tdides','cpe_c.ccandi','cpe_c.ccanom','monnom','cpe_c.ccaitv','cpe_c.IdEmpresa','cpe_c.tdocod','cpe_c.IdCpe_cabecera','cpe_c.ccanot','cpe_c.ccabaj','cpe_c.codhash','cpe_c.estado','cpe_c.enviado','totalcontado','totalcredito','clicor','clicor2','clicor3','clicor4','clicorcli','clicorcli2','clicorcli3','clicorcli4')
        ->join('tipo_documento as tip_d','cpe_c.tdocod','=','tip_d.tdocod')
        ->leftjoin('cliente','cliente.clicod','cpe_c.clicod')
        ->join('tipo_documento_identidad as tdi','cpe_c.tdicod','=','tdi.tdicod')
        ->join('moneda as mon','cpe_c.moncod','=','mon.moncod')
        ->join('usuario_facturacion','usuario_facturacion.IdCpe_cabecera','cpe_c.IdCpe_cabecera')
        ->whereNull('cpe_c.ccabaj')
        ->where('usuario_facturacion.id_empresa_negocio',$datos->id_empresa_negocio)
        ->where(function ($query) {
          $query->where('cpe_c.tdocod','01')
              ->orWhere('cpe_c.tdocod','03')
              ->orWhere('cpe_c.tdocod','13');
          })
        ->where(function ($query1) use ($cliente){
            if(!empty($clientes)){
                $query1->where('cpe_c.ccanom','like','%'.$cliente.'%')
                    ->orWhere('cpe_c.ccandi','=',$cliente);
            }
        })
        ->where(function ($query2) use ($documento,$ser,$num){
            if(!empty($documento)){
                $query2->where('serdoc',$ser)
                    ->Where('numdoc',$num);
            }
        })
        ->where('usuario_facturacion.id_turno',$turno)
        ->orderby('cpe_c.ped_id','asc')
        ->get();
        

        $total = DB::tABLE('cpe_cabecera as cpe_c')->select('ccacodsun','ccafem','tdodes','cpe_c.serdoc','cpe_c.numdoc','tdides','cpe_c.ccandi','cpe_c.ccanom','monnom','cpe_c.ccaitv','cpe_c.IdEmpresa','cpe_c.tdocod','cpe_c.IdCpe_cabecera','cpe_c.ccanot','cpe_c.ccabaj','cpe_c.codhash','cpe_c.estado','cpe_c.enviado','totalcontado','totalcredito')
        ->join('tipo_documento as tip_d','cpe_c.tdocod','=','tip_d.tdocod')
        ->join('tipo_documento_identidad as tdi','cpe_c.tdicod','=','tdi.tdicod')
        ->join('moneda as mon','cpe_c.moncod','=','mon.moncod')
        ->join('usuario_facturacion','usuario_facturacion.IdCpe_cabecera','cpe_c.IdCpe_cabecera')
        ->whereNull('cpe_c.ccabaj')
       ->where('usuario_facturacion.id_empresa_negocio',$datos->id_empresa_negocio)
        ->where(function ($query) {
          $query->where('cpe_c.tdocod','01')
              ->orWhere('cpe_c.tdocod','03')
              ->orWhere('cpe_c.tdocod','13');
          })
        ->where(function ($query1) use ($cliente){
            if(!empty($clientes)){
                $query1->where('cpe_c.ccanom','like','%'.$cliente.'%')
                    ->orWhere('cpe_c.ccandi','=',$cliente);
            }
        })
        ->where(function ($query2) use ($documento,$ser,$num){
            if(!empty($documento)){
                $query2->where('serdoc',$ser)
                    ->Where('numdoc',$num);
            }
        })
        ->where('estadopago','contado')
        ->where('usuario_facturacion.id_turno',$turno)
        ->sum('ccaitv');
        
        
        $credito = DB::tABLE('cpe_cabecera as cpe_c')->select('ccacodsun','ccafem','tdodes','cpe_c.serdoc','cpe_c.numdoc','tdides','cpe_c.ccandi','cpe_c.ccanom','monnom','cpe_c.ccaitv','cpe_c.IdEmpresa','cpe_c.tdocod','cpe_c.IdCpe_cabecera','cpe_c.ccanot','cpe_c.ccabaj','cpe_c.codhash','cpe_c.estado','cpe_c.enviado','totalcontado','totalcredito')
        ->join('tipo_documento as tip_d','cpe_c.tdocod','=','tip_d.tdocod')
        ->join('tipo_documento_identidad as tdi','cpe_c.tdicod','=','tdi.tdicod')
        ->join('moneda as mon','cpe_c.moncod','=','mon.moncod')
        ->join('usuario_facturacion','usuario_facturacion.IdCpe_cabecera','cpe_c.IdCpe_cabecera')
        ->whereNull('cpe_c.ccabaj')
        ->where('usuario_facturacion.id_empresa_negocio',$datos->id_empresa_negocio)
        ->where(function ($query) {
          $query->where('cpe_c.tdocod','01')
              ->orWhere('cpe_c.tdocod','03')
              ->orWhere('cpe_c.tdocod','13');
          })
        ->where(function ($query1) use ($cliente){
            if(!empty($clientes)){
                $query1->where('cpe_c.ccanom','like','%'.$cliente.'%')
                    ->orWhere('cpe_c.ccandi','=',$cliente);
            }
        })
        ->where('estadopago','credito')
        ->where(function ($query2) use ($documento,$ser,$num){
            if(!empty($documento)){
                $query2->where('serdoc',$ser)
                    ->Where('numdoc',$num);
            }
        })
        ->where('usuario_facturacion.id_turno',$turno)
        ->sum('ccaitv');
                    
    
     Excel::create('reporte_ventas_turno', function($excel) use ($efectivo,$sum_mp,$compras,$totalgas,$totaling,$total,$comprobantes,$turno,$cliente,$datos,$usuario,$documento,$credito,$otros_medios) {

                        $excel->sheet('reporte_ventas_turno', function($sheet) use ($efectivo,$sum_mp,$compras,$totalgas,$totaling,$total,$comprobantes,$turno,$cliente,$datos,$usuario,$documento,$credito,$otros_medios) {

                              
                                  $sheet->loadView('formatos_reportes_excel.ventasturno',compact('efectivo','sum_mp','compras','totalgas','totaling','total','comprobantes','turno','cliente','datos','usuario','documento','credito','otros_medios'));
                          
                                

                        });

                    })->export('xlsx'); 


    }

      public function arqueoresumen(Request $request,$turno,$tipo=0){

        $cliente = $request->get('cliente');
        $documento = $request->get('documento');

        $total_boletas_efectivo=0;
        $total_facturas_efectivo=0;
        $total_notas_efectivo = 0;
    
        $datos = DB::tABLE('turnos')->where('id_turno',$turno)->first();
        $sucursal = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',$datos->id_empresa_negocio)->first();
        $empresa = DB::tABLE('empresa')->where('IdEmpresa',$sucursal->IdEmpresa)->first();


        $usuario = DB::tABLE('users')->where('IdUsuario',$datos->IdUsuario)->first();
        
        $consul_med_pag = DB::tABLE('medios_pagos')->where('predeterminado','1')->first();

        $ventasefectivoboleta = DB::tABLE('cpe_cabecera')
         ->join('venta_medio_pago','venta_medio_pago.IdCpe_cabecera','cpe_cabecera.IdCpe_cabecera')
        ->join('medios_pagos','venta_medio_pago.id_med_pag','medios_pagos.id_med_pag')
        ->whereNull('cpe_cabecera.ccabaj')
        ->where('venta_medio_pago.id_med_pag',$consul_med_pag->id_med_pag)
        ->where('cpe_cabecera.id_empresa_negocio',$datos->id_empresa_negocio)
        ->where(function ($query) {
          $query->Where('cpe_cabecera.tdocod','03');
              
          })
        ->where('estadopago','CONTADO')
        ->where('venta_medio_pago.id_turno',$turno)
        ->sum('monto');
        

         $ventasefectivofactura = DB::tABLE('cpe_cabecera')
        ->join('venta_medio_pago','venta_medio_pago.IdCpe_cabecera','cpe_cabecera.IdCpe_cabecera')
        ->join('medios_pagos','venta_medio_pago.id_med_pag','medios_pagos.id_med_pag')
        ->whereNull('cpe_cabecera.ccabaj')
       ->where('venta_medio_pago.id_med_pag',$consul_med_pag->id_med_pag)
         
        ->where('cpe_cabecera.id_empresa_negocio',$datos->id_empresa_negocio)
        ->where(function ($query) {
          $query->Where('cpe_cabecera.tdocod','01');
              
          })
    
        ->where('estadopago','contado')
        ->where('venta_medio_pago.id_turno',$turno)
        ->sum('monto');

        $ventasefectivonota = DB::tABLE('cpe_cabecera')
        ->join('venta_medio_pago','venta_medio_pago.IdCpe_cabecera','cpe_cabecera.IdCpe_cabecera')
        ->join('medios_pagos','venta_medio_pago.id_med_pag','medios_pagos.id_med_pag')
        ->whereNull('cpe_cabecera.ccabaj')
        ->where('venta_medio_pago.id_med_pag',$consul_med_pag->id_med_pag)
        ->where('cpe_cabecera.id_empresa_negocio',$datos->id_empresa_negocio)
        ->where(function ($query) {
          $query->Where('cpe_cabecera.tdocod','13');
              
          })
        ->where('estadopago','contado')
        ->where('venta_medio_pago.id_turno',$turno)
        ->sum('monto');
        

        $totalingreso = DB::tABLE('cpe_cabecera')
        ->join('venta_medio_pago','venta_medio_pago.IdCpe_cabecera','cpe_cabecera.IdCpe_cabecera')
        ->join('medios_pagos','venta_medio_pago.id_med_pag','medios_pagos.id_med_pag')
         ->where('venta_medio_pago.id_med_pag',$consul_med_pag->id_med_pag)
         ->where(function ($query) {
          $query->Where('cpe_cabecera.tdocod','03')
          ->orWhere('cpe_cabecera.tdocod','01')
          ->orWhere('cpe_cabecera.tdocod','13');
              
          })
    
        ->where('venta_medio_pago.id_turno',$turno)
        ->whereNull('ccabaj')
        ->sum('monto');


        $ventas_medios_pagos = DB::tABLE('cpe_cabecera')->select('cpe_cabecera.tdocod','tdodes','nom_med_pag','medios_pagos.id_med_pag',DB::RAW('sum(monto) as monto'))
        ->join('venta_medio_pago','venta_medio_pago.IdCpe_cabecera','cpe_cabecera.IdCpe_cabecera')
        ->join('medios_pagos','venta_medio_pago.id_med_pag','medios_pagos.id_med_pag')
        ->join('tipo_documento','tipo_documento.tdocod','cpe_cabecera.tdocod')
         ->where(function ($query) {
          $query->Where('cpe_cabecera.tdocod','03')
          ->orWhere('cpe_cabecera.tdocod','01')
          ->orWhere('cpe_cabecera.tdocod','13');
              
          })
        ->where('venta_medio_pago.id_turno',$turno)
        ->whereNull('ccabaj')
        ->groupby('tdocod')
        ->groupby('id_med_pag')
         ->get();

          $total_ventas_medios_pagos = DB::tABLE('cpe_cabecera')
          ->select('tipo_medio','nom_med_pag','medios_pagos.id_med_pag',DB::RAW('sum(monto) as monto'))
        ->join('venta_medio_pago','venta_medio_pago.IdCpe_cabecera','cpe_cabecera.IdCpe_cabecera')
        ->join('medios_pagos','venta_medio_pago.id_med_pag','medios_pagos.id_med_pag')
       
         ->where(function ($query) {
          $query->Where('cpe_cabecera.tdocod','03')
          ->orWhere('cpe_cabecera.tdocod','01')
          ->orWhere('cpe_cabecera.tdocod','13');
              
          })
        ->where('venta_medio_pago.id_turno',$turno)
        ->whereNull('ccabaj')
        ->groupby('id_med_pag')
         ->get();

         $medios_pagos = DB::tABLE('medios_pagos')->get();

         $sum_ven_med_pag = $ventas_medios_pagos->sum('monto');

        $totalgasto = DB::tABLE('gastos_cabecera')
        ->leftjoin('moneda','gastos_cabecera.mon_id','moneda.moncod')
        ->leftjoin('tipo_documento','gastos_cabecera.tdocod','tipo_documento.tdocod')
        ->join('usuario_gastos','usuario_gastos.gast_cab_id','gastos_cabecera.gast_cab_id')
        ->where('tipo_movimiento','GASTO')
        ->where('est_gasto','Registrado')
        ->where('id_turno',$turno)
        ->orderby('gastos_cabecera.gast_Cab_id','desc')
        ->sum('total_gast');

        $grup_gas = DB::tABLE('gastos_cabecera')->select('tip_gas_nom',DB::RAW('sum(gastos_detalle.total) as total'))
         ->leftjoin('gastos_detalle','gastos_cabecera.gast_cab_id','gastos_detalle.gast_cab_id')
         ->join('usuario_gastos','usuario_gastos.gast_cab_id','gastos_cabecera.gast_cab_id')
        ->leftjoin('tipo_gastos','tipo_gastos.tip_gas_id','gastos_detalle.tip_gas_id')
        ->where('gastos_cabecera.id_empresa_negocio',$datos->id_empresa_negocio)
        ->where('tipo_movimiento','GASTO')
        ->where('est_gasto','Registrado')
        ->where('id_turno',$turno)
        ->orderby('gastos_cabecera.gast_Cab_id','desc')
        ->groupby('tipo_gastos.tip_gas_id')
        ->get();

  
      
        $compras = DB::tABLE('compras_cabecera')
        ->leftjoin('proveedor','compras_cabecera.prov_id','proveedor.prov_id')
        ->leftjoin('moneda','compras_cabecera.mon_id','moneda.moncod')
        ->leftjoin('tipo_documento','compras_cabecera.tdocod','tipo_documento.tdocod')
         ->where('id_turno',$turno)
        ->where('est_compra','Registrado')
        ->where('compras_cabecera.tdocod','!=','80')
        ->sum('total_com');

          $ven_prod = DB::tABLE('cpe_cabecera')
          ->select('cpe_detalle.IdProducto',DB::RAW('sum(cpe_detalle.cdevve) as TOTAL'),DB::RAW('sum(cpe_detalle.cdecan*factor) as CANTIDAD'),'productos.pronom')
        ->join('cpe_detalle','cpe_detalle.IdCpe_cabecera','cpe_cabecera.IdCpe_cabecera')
         ->join('productos','productos.IdProducto','cpe_detalle.IdProducto')
          ->where(function ($query) {
          $query->where('cpe_cabecera.tdocod','01')
              ->orWhere('cpe_cabecera.tdocod','03')
              ->orWhere('cpe_cabecera.tdocod','13');
          })
          ->where('id_turno',$turno)
          ->whereNull('ccabaj')
           ->groupby('cpe_detalle.IdProducto')->get();


        $rutapdf = public_path().'/pdfreportes/';

        $nompdffile = 'Arqueo_Resumen_'.$datos->id_turno.'.pdf';

       if(file_exists($rutapdf.$nompdffile)){

            unlink($rutapdf.$nompdffile);
        }

        $view = \View::make('formatos_reportes.reporte_pdf_arqueo_resumen', compact('total_ventas_medios_pagos','compras','totalgasto','totalingreso','ventasefectivonota','ventasefectivofactura','ventasefectivoboleta','datos','usuario','sucursal','empresa','grup_gas','ventas_medios_pagos','sum_ven_med_pag','medios_pagos','ven_prod'));

                  
          $pdf = \App::make('dompdf.wrapper');
          $contenido = $view->render();
          $pdf->loadHTML($view)->setPaper('A4', 'lanscape')->save($rutapdf.$nompdffile);


      
        if($tipo=='1'){

                $impresoras = DB::tABLE('configuracion_impresoras')->where('Id',Auth::user()->terminal)->first();


  
      $connector = new WindowsPrintConnector("smb://".$impresoras->ruta);

     
        $printer = new Printer($connector);
        $printer->setJustification(Printer::JUSTIFY_CENTER);
       

       $printer->text($empresa->NomEmpresa."\n");
             $printer->text($sucursal->direccion."\n");
       $printer->text('RUC:'.$empresa->IdEmpresa."\n");
       
 
        
        $printer->setJustification(Printer::JUSTIFY_CENTER);
        $printer->text("ARQUEO DE CAJA - RESUMEN DIARIO"."\n");
        $printer->setJustification(Printer::JUSTIFY_LEFT);
        $printer->text("USUARIO"."      ".$usuario->name.' '.$usuario->apeusu."\n");
        $printer->text("APERTURA"."     ".$datos->apertura."\n");
        $printer->text("CIERRE"."       ".$datos->cierre."\n");
        $printer->text("INGRESOS EFECTIVO"."\n");
        $printer->text("________________________________________________"."\n");
        $printer->text("DESCRIPCION                               TOTAL"."\n");
        $printer->text(str_pad("FONDO CAJA",40," ", STR_PAD_RIGHT)." ".number_format($datos->monto,'2','.',',')."\n");
        $printer->text(str_pad("VENTAS BOLETAS ",40," ", STR_PAD_RIGHT)." ".number_format($ventasefectivoboleta,'2','.',',')."\n");
        $printer->text(str_pad("VENTAS FACTURAS ",40," ", STR_PAD_RIGHT)." ".number_format($ventasefectivofactura,'2','.',',')."\n");
        $printer->text(str_pad("VENTAS NOTAS ",40," ", STR_PAD_RIGHT)." ".number_format($ventasefectivonota,'2','.',',')."\n");
        $printer->text("________________________________________________"."\n");
        $printer->text("TOTAL INGRESOS                           ".number_format($totalingreso+$datos->monto,'2','.',',')."\n"."\n");
      
        $printer->text("EGRESOS"."\n");
        $printer->text("________________________________________________"."\n");
        foreach($grup_gas as $gg){
        $printer->text(str_pad($gg->tip_gas_nom,40," ", STR_PAD_RIGHT)." ".number_format($gg->total,'2','.',',')."\n");
        }
        $printer->text("COMPRAS                                  ".number_format($compras,'2','.',',')."\n");
        $printer->text("________________________________________________"."\n");
        $printer->text("TOTAL GASTOS                             ".number_format($totalgasto,'2','.',',')."\n"."\n");
       
        $printer->text("RESUMEN"."\n");
        $printer->text("________________________________________________"."\n");
        $printer->text("DESCRIPCION                              TOTAL"."\n");
        $printer->text("TOTAL INGRESO                            ".number_format($totalingreso+$datos->monto,'2','.',',')."\n");
        $printer->text("TOTAL EGRESO                             ".number_format($totalgasto+$compras,'2','.',',')."\n");
        $printer->text("________________________________________________"."\n");
        $printer->text("SALDO                                    ".number_format(($totalingreso+$datos->monto)-($totalgasto+$compras),'2','.',',')."\n");

        
        $printer->text("\n"."\n"."VENTAS POR MEDIO DE PAGO"."\n");
        $printer->text("________________________________________________"."\n");
        $printer->text("DESCRIPCION                              TOTAL"."\n");
        foreach($total_ventas_medios_pagos as $tvm){
            $printer->text(str_pad($tvm->nom_med_pag,40," ", STR_PAD_RIGHT)." ".number_format($tvm->monto,'2','.',',')."\n");
                            
        }
                                    
    

        $printer->feed();
         
     
        $printer->cut();
         
     
        $printer->pulse();

        $printer->close();


        }else{


       if (file_exists($rutapdf.$nompdffile))
      {
        $headers = array(
              'Content-Type: application/pdf',
            );

        return response()->download($rutapdf.$nompdffile);
      }

        }



    }

    public function arqueodetallado(Request $request,$turno,$tipo=0){

        $cliente = $request->get('cliente');
        $documento = $request->get('documento');


        $datos = DB::tABLE('turnos')->where('id_turno',$turno)->first();
        
        $sucursal = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',$datos->id_empresa_negocio)->first();
        
        $empresa = DB::tABLE('empresa')->where('IdEmpresa',$sucursal->IdEmpresa)->first();

        $usuario = DB::tABLE('users')->where('IdUsuario',$datos->IdUsuario)->first();
        
        
        $gastos = DB::tABLE('gastos_cabecera')
        ->leftjoin('gastos_detalle','gastos_cabecera.gast_cab_id','gastos_detalle.gast_cab_id')
        ->leftjoin('moneda','gastos_cabecera.mon_id','moneda.moncod')
        ->leftjoin('tipo_gastos','tipo_gastos.tip_gas_id','gastos_detalle.tip_gas_id')
        ->leftjoin('tipo_documento','gastos_cabecera.tdocod','tipo_documento.tdocod')
        ->join('usuario_gastos','usuario_gastos.gast_cab_id','gastos_cabecera.gast_cab_id')
        ->where('gastos_cabecera.id_empresa_negocio',$datos->id_empresa_negocio)
        ->where('tipo_movimiento','GASTO')
        ->where('est_gasto','Registrado')
        ->where('id_turno',$turno)
        ->orderby('gastos_cabecera.gast_cab_id','desc')
        ->get();

           $total_gastos = DB::tABLE('gastos_cabecera')
        ->leftjoin('gastos_detalle','gastos_cabecera.gast_cab_id','gastos_detalle.gast_cab_id')
        ->leftjoin('moneda','gastos_cabecera.mon_id','moneda.moncod')
        ->leftjoin('tipo_gastos','tipo_gastos.tip_gas_id','gastos_detalle.tip_gas_id')
        ->leftjoin('tipo_documento','gastos_cabecera.tdocod','tipo_documento.tdocod')
        ->join('usuario_gastos','usuario_gastos.gast_cab_id','gastos_cabecera.gast_cab_id')
        ->where('gastos_cabecera.id_empresa_negocio',$datos->id_empresa_negocio)
        ->where('tipo_movimiento','GASTO')
        ->where('est_gasto','Registrado')
        ->where('id_turno',$turno)
        ->sum('total');

        $grup_gas = DB::tABLE('gastos_cabecera')->select('tip_gas_nom',DB::RAW('sum(gastos_detalle.total) as total'))
         ->leftjoin('gastos_detalle','gastos_cabecera.gast_cab_id','gastos_detalle.gast_cab_id')
         ->join('usuario_gastos','usuario_gastos.gast_cab_id','gastos_cabecera.gast_cab_id')
        ->leftjoin('tipo_gastos','tipo_gastos.tip_gas_id','gastos_detalle.tip_gas_id')
        ->where('gastos_cabecera.id_empresa_negocio',$datos->id_empresa_negocio)
        ->where('tipo_movimiento','GASTO')
        ->where('est_gasto','Registrado')
        ->where('id_turno',$turno)
        ->orderby('gastos_cabecera.gast_Cab_id','desc')
        ->groupby('tipo_gastos.tip_gas_id')
        ->get();

   


        $ingresos = DB::tABLE('gastos_cabecera')
        ->leftjoin('gastos_detalle','gastos_cabecera.gast_cab_id','gastos_detalle.gast_cab_id')
        ->leftjoin('moneda','gastos_cabecera.mon_id','moneda.moncod')
        ->leftjoin('tipo_gastos','tipo_gastos.tip_gas_id','gastos_detalle.tip_gas_id')
        ->leftjoin('tipo_documento','gastos_cabecera.tdocod','tipo_documento.tdocod')
        ->join('usuario_gastos','usuario_gastos.gast_cab_id','gastos_cabecera.gast_cab_id')
        ->where('tipo_movimiento','INGRESO')
        ->where('est_gasto','Registrado')
        ->where('id_turno',$turno)
        ->orderby('gastos_cabecera.gast_Cab_id','desc')
        ->get();

          $grup_ing =DB::tABLE('gastos_cabecera')->select('tip_gas_nom',DB::RAW('sum(gastos_detalle.total) as total'))
        ->leftjoin('gastos_detalle','gastos_cabecera.gast_cab_id','gastos_detalle.gast_cab_id')
        ->join('usuario_gastos','usuario_gastos.gast_cab_id','gastos_cabecera.gast_cab_id')
        ->leftjoin('tipo_gastos','tipo_gastos.tip_gas_id','gastos_detalle.tip_gas_id')
        ->where('gastos_cabecera.id_empresa_negocio',$datos->id_empresa_negocio)
        ->where('tipo_movimiento','INGRESO')
        ->where('est_gasto','Registrado')
        ->where('id_turno',$turno)
        ->orderby('gastos_cabecera.gast_Cab_id','desc')
        ->groupby('tipo_gastos.tip_gas_id')
        ->get();


        

         
        
      
          $compras = DB::tABLE('compras_cabecera')
          ->leftjoin('compras_detalle','compras_detalle.com_cab_id','compras_cabecera.com_cab_id')
            ->leftjoin('productos','productos.IdProducto','compras_detalle.pro_id')
        ->leftjoin('proveedor','compras_cabecera.prov_id','proveedor.prov_id')
        ->leftjoin('moneda','compras_cabecera.mon_id','moneda.moncod')
        ->leftjoin('tipo_documento','compras_cabecera.tdocod','tipo_documento.tdocod')
         ->where('id_turno',$turno)
        ->where('est_compra','Registrado')
        ->where('compras_cabecera.tdocod','!=','80')
        ->get();


          $totalcompras = DB::tABLE('compras_cabecera')
        ->leftjoin('proveedor','compras_cabecera.prov_id','proveedor.prov_id')
        ->leftjoin('moneda','compras_cabecera.mon_id','moneda.moncod')
        ->leftjoin('tipo_documento','compras_cabecera.tdocod','tipo_documento.tdocod')
         ->where('id_turno',$turno)
        ->where('est_compra','Registrado')
        ->where('compras_cabecera.tdocod','!=','80')
        ->sum('total_com');

            
        $totalgas = 0;
        $totaling = 0;
        $totalgaseli =0;
        $totalingeli =0;

        foreach ($gastos as $gas) {
            $totalgas = $totalgas + $gas->total;
        }

        foreach ($ingresos as $ing) {
            
            $totaling = $totaling + $ing->total;

        }

    
          $med_pag = DB::tABLE('medios_pagos')->where('predeterminado','1')->first();

        $comprobantes = DB::tABLE('cpe_cabecera as cpe_c')->select('fecha_hora','ccacodsun','ccafem','tdodes','cpe_c.serdoc','cpe_c.numdoc','tdides','cpe_c.ccandi','cpe_c.ccanom','monnom','ccaitv','cpe_c.IdEmpresa','cpe_c.tdocod','cpe_c.IdCpe_cabecera','cpe_c.ccanot','cpe_c.ccabaj','cpe_c.codhash','cpe_c.estado','cpe_c.enviado','totalcontado','totalcredito')
        ->join('tipo_documento as tip_d','cpe_c.tdocod','=','tip_d.tdocod')
        ->join('tipo_documento_identidad as tdi','cpe_c.tdicod','=','tdi.tdicod')
        ->join('moneda as mon','cpe_c.moncod','=','mon.moncod')
        ->join('usuario_facturacion','usuario_facturacion.IdCpe_cabecera','cpe_c.IdCpe_cabecera')
       // ->leftjoin('venta_medio_pago','venta_medio_pago.IdCpe_cabecera','cpe_c.IdCpe_cabecera')
        ->whereNull('cpe_c.ccabaj')
        ->where('usuario_facturacion.id_empresa_negocio',$datos->id_empresa_negocio)
        ->where(function ($query) {
          $query->where('cpe_c.tdocod','01')
              ->orWhere('cpe_c.tdocod','03')
              ->orWhere('cpe_c.tdocod','13');
          })
        ->where('usuario_facturacion.id_turno',$turno)
        ->orderby('IdCpe_cabecera','desc')
        ->get();
        
       // dd($comprobantes);

        $total = DB::tABLE('cpe_cabecera as cpe_c')->select('ccacodsun','ccafem','tdodes','cpe_c.serdoc','cpe_c.numdoc','tdides','cpe_c.ccandi','cpe_c.ccanom','monnom','ccaitv','cpe_c.IdEmpresa','cpe_c.tdocod','cpe_c.IdCpe_cabecera','cpe_c.ccanot','cpe_c.ccabaj','cpe_c.codhash','cpe_c.estado','cpe_c.enviado','totalcontado','totalcredito')
        ->join('tipo_documento as tip_d','cpe_c.tdocod','=','tip_d.tdocod')
        ->join('tipo_documento_identidad as tdi','cpe_c.tdicod','=','tdi.tdicod')
        ->join('moneda as mon','cpe_c.moncod','=','mon.moncod')
         ->join('usuario_facturacion','usuario_facturacion.IdCpe_cabecera','cpe_c.IdCpe_cabecera')
        ->whereNull('cpe_c.ccabaj')
         
        ->where('usuario_facturacion.id_empresa_negocio',$datos->id_empresa_negocio)
        ->where(function ($query) {
          $query->where('cpe_c.tdocod','01')
              ->orWhere('cpe_c.tdocod','03')
              ->orWhere('cpe_c.tdocod','13');
          })
    
        ->where('estadopago','contado')
        ->where('usuario_facturacion.id_turno',$turno)
        ->sum('ccaitv');
        
      

        $credito = DB::tABLE('cpe_cabecera as cpe_c')->select('fecha_hora','ccacodsun','ccafem','tdodes','cpe_c.serdoc','cpe_c.numdoc','tdides','cpe_c.ccandi','cpe_c.ccanom','monnom','cpe_c.ccaitv','cpe_c.IdEmpresa','cpe_c.tdocod','cpe_c.IdCpe_cabecera','cpe_c.ccanot','cpe_c.ccabaj','cpe_c.codhash','cpe_c.estado','cpe_c.enviado','totalcontado','totalcredito')
        ->join('tipo_documento as tip_d','cpe_c.tdocod','=','tip_d.tdocod')
        ->join('tipo_documento_identidad as tdi','cpe_c.tdicod','=','tdi.tdicod')
        ->join('moneda as mon','cpe_c.moncod','=','mon.moncod')
      ->join('usuario_facturacion','usuario_facturacion.IdCpe_cabecera','cpe_c.IdCpe_cabecera')
        ->whereNull('cpe_c.ccabaj')
        ->where('usuario_facturacion.id_empresa_negocio',$datos->id_empresa_negocio)
        ->where(function ($query) {
          $query->where('cpe_c.tdocod','01')
              ->orWhere('cpe_c.tdocod','03')
              ->orWhere('cpe_c.tdocod','13');
          })
        ->where(function ($query1) use ($cliente){
            if(!empty($clientes)){
                $query1->where('cpe_c.ccanom','like','%'.$cliente.'%')
                    ->orWhere('cpe_c.ccandi','=',$cliente);
            }
        })
        ->where('estadopago','credito')
        
        ->where('usuario_facturacion.id_turno',$turno)
        ->sum('ccaitv');
                    

        $rutapdf = public_path().'/pdfreportes/';

        $nompdffile = 'Arqueo_Detallado_'.$datos->id_turno.'.pdf';

       if(file_exists($rutapdf.$nompdffile)){

            unlink($rutapdf.$nompdffile);
        }

        $view = \View::make('formatos_reportes.reporte_pdf_arqueo_detallado', compact('compras','totalgas','totaling','total','comprobantes','turno','cliente','documento','datos','usuario','documento','credito','empresa','sucursal','gastos','totalcompras'));


                  
          $pdf = \App::make('dompdf.wrapper');
          $contenido = $view->render();
          $pdf->loadHTML($view)->setPaper('A4', 'lanscape')->save($rutapdf.$nompdffile);


      
        if($tipo=='1'){

               $impresoras = DB::tABLE('configuracion_impresoras')->where('Id',Auth::user()->terminal)->first();


  
                  $connector = new WindowsPrintConnector("smb://".$impresoras->ruta);

                 
                    $printer = new Printer($connector);
                    $printer->setJustification(Printer::JUSTIFY_CENTER);
                   

                   $printer->text($empresa->NomEmpresa."\n");
             $printer->text($sucursal->direccion."\n");
                   $printer->text('RUC:'.$empresa->IdEmpresa."\n");
                   
             
                    
                    $printer->setJustification(Printer::JUSTIFY_CENTER);
                    $printer->text("ARQUEO DE CAJA DIARIO - DETALLADO"."\n");
                    $printer->setJustification(Printer::JUSTIFY_LEFT);
                    $printer->text("USUARIO"."                  ".$usuario->name.' '.$usuario->apeusu."\n");
                    $printer->text("APERTURA"."                 ".$datos->apertura."\n");
                    $printer->text("CIERRE"."                   ".$datos->cierre."\n");
                    $printer->text("Fecha    Cod.  Serie  N° Doc. Descrip.   Total"."\n");
                    $printer->text("________________________________________________"."\n"."\n");
                    
                    $printer->text("CAJA CHICA"."\n");
                    $printer->text("________________________________________________"."\n");
                    $printer->text(date('d/m/Y',strtotime($datos->apertura))."      "."FONDO DE CAJA"."           ".number_format($datos->monto,'2','.',',')."\n");
                    $printer->text(date('d/m/Y',strtotime($datos->apertura))."      "."INGRESOS EFECTIVO"."         ".number_format($totaling,'2','.',',')."\n");
                    $printer->text("________________________________________________"."\n");
                    $printer->text("TOTAL INGRESOS CAJA                     ".number_format($datos->monto+$totaling,'2','.',',')."\n"."\n"."\n");
                   


                    $printer->text("VENTAS"."\n");
                    $printer->text("_______________________________________________"."\n");
                    foreach($comprobantes as $comp){
                    $printer->text($comp->fecha_hora."  ".$comp->tdocod."  ".$comp->serdoc."    ".$comp->numdoc."   ".number_format($comp->ccaitv,'2','.',',')."\n");
                    }
                    $printer->text("_______________________________________________"."\n");
                    $printer->text("TOTAL VENTAS                             ".number_format($total,'2','.',',')."\n"."\n"."\n");


                    $printer->text("GASTOS"."\n");
                    $printer->text("________________________________________________"."\n");
                   foreach($gastos as $gast){
                    $printer->text($gast->gast_fec."   ".str_pad($gast->tdocod,2," ", STR_PAD_RIGHT)."  ".str_pad($gast->gast_doc_ser,4," ", STR_PAD_RIGHT)." ".str_pad($gast->gast_doc_num,4," ", STR_PAD_RIGHT)."  ".str_pad($gast->det_gasto,10," ", STR_PAD_RIGHT)."  ".number_format($gast->total,'2','.',',')."\n");
                    }
                    $printer->text("_______________________________________________"."\n");
                    $printer->text("TOTAL GASTOS                            ".number_format($totalgas,'2','.',',')."\n"."\n"."\n");



/*                    $printer->text("COMPRAS"."\n");
                    $printer->text("_______________________________________________"."\n");
                    foreach($compras as $compra){
                    $printer->text($compra->com_fec."  ".$compra->tdocod."  ".$compra->com_doc_ser."    ".$compra->com_doc_num."  ".$compra->pronom."  ".number_format($compra->total_com,'2','.',',')."\n");
                    }
                    $printer->text("________________________________________________"."\n");
                    $printer->text("TOTAL COMPRAS                            ".number_format($totalcompras,'2','.',',')."\n"."\n"."\n");    
*/


                    $printer->text("RESUMEN"."\n");
                    $printer->text("________________________________________________"."\n");
                    $printer->text("DESCRIPCION                              TOTAL"."\n");
                    $printer->text("TOTAL INGRESO                            ".number_format($totaling+$total+$datos->monto,'2','.',',')."\n");
                    //$printer->text("TOTAL EGRESO                             ".number_format($totalgas+$totalcompras,'2','.',',')."\n");
                    $printer->text("TOTAL EGRESO                             ".number_format($totalgas+$totalcompras,'2','.',',')."\n");
                    $printer->text("________________________________________________"."\n");
                   // $printer->text("SALDO                                    ".number_format(($totaling+$total+$datos->monto)-($totalgas+$totalcompras),'2','.',',')."\n");
                    $printer->text("SALDO                                    ".number_format(($totaling+$total+$datos->monto),'2','.',',')."\n");
                





                    $printer->feed();
                     
                 
                    $printer->cut();
                     
                 
                    $printer->pulse();

                    $printer->close();



        }else{


       if (file_exists($rutapdf.$nompdffile))
      {
        $headers = array(
              'Content-Type: application/pdf',
            );

        return response()->download($rutapdf.$nompdffile);
      }
      
        }



    }


   
}
