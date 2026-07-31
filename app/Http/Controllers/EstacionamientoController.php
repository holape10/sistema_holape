<?php

namespace MasterSoft\Http\Controllers;

use Illuminate\Http\Request;
use MasterSoft\Estacionamiento; 
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

// Modelos necesarios para facturar
use MasterSoft\cpe_cabecera;
use MasterSoft\cpe_detalle;
use MasterSoft\EmpresaNegocios;
use MasterSoft\Empresa;
use MasterSoft\Cliente;
use MasterSoft\usuario_facturacion;

class EstacionamientoController extends Controller
{
    public function index() {
        $activos = \MasterSoft\Estacionamiento::where('estado', 0)
                    ->orderBy('hora_ingreso', 'desc')
                    ->get();
        return view('empresas.estacionamientos.vista', compact('activos'));
    }

    public function obtenerActivos() {
        $activos = \MasterSoft\Estacionamiento::where('estado', 0)
                    ->orderBy('hora_ingreso', 'desc')
                    ->get();               
        return response()->json($activos);
    }

    public function reportePorPunto(Request $request) {
        $fecha_inicio = $request->get('fecha_inicio', date('Y-m-d'));
        $fecha_fin = $request->get('fecha_fin', date('Y-m-d'));

        // Consulta potente: Agrupa y suma por punto de atención
        $reporte = DB::table('estacionamientos')
                    ->select('id_punto_atencion', 
                             DB::raw('SUM(monto_total) as total_recaudado'), 
                             DB::raw('COUNT(*) as total_tickets'))
                    ->where('estado', 1)
                    ->whereBetween('hora_salida', [$fecha_inicio . ' 00:00:00', $fecha_fin . ' 23:59:59'])
                    ->groupBy('id_punto_atencion')
                    ->get();

        // Sumatoria total para el KPI de arriba
        $total_general = $reporte->sum('total_recaudado');
        $total_tickets = $reporte->sum('total_tickets');

        return view('empresas.estacionamientos.reporte', compact('reporte', 'total_general', 'total_tickets', 'fecha_inicio', 'fecha_fin'));
    }

    // API PERU: Consultar DNI o RUC
   public function consultarDocumento(Request $request) {
        $documento = trim($request->get('documento'));

        if (strlen($documento) === 8) {
            // ==========================================
            // 1. CONSULTA DNI (Usando apiperu.dev)
            // ==========================================
            $token = 'c7c656604942b0a6df5fa225835e99eb7376cf841d38781b91f651f72e03cc09'; 
            $params = json_encode(['dni' => $documento]);
            $url = "https://apiperu.dev/api/dni";

            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_POSTFIELDS => $params,
                CURLOPT_HTTPHEADER => [
                    'Accept: application/json',
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $token
                ],
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);

            if ($err) { 
                return response()->json(['error' => 'Error de conexión DNI: ' . $err]); 
            }
            
            return response()->json(json_decode($response, true));

        } elseif (strlen($documento) === 11) {
            // ==========================================
            // 2. CONSULTA RUC (Usando consultas.holape.app)
            // ==========================================
            $url = "https://consultas.holape.app/api/v1/ruc/" . $documento;

            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_HTTPHEADER => [
                    'Accept: application/json',
                ],
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);

            if ($err) { 
                return response()->json(['error' => 'Error de conexión RUC propio: ' . $err]); 
            }
            
            return response()->json(json_decode($response, true));

        } else {
            return response()->json(['error' => 'El documento debe tener 8 dígitos (DNI) o 11 dígitos (RUC).']);
        }
    }

    // Registrar Ingreso
    public function store(Request $request) {
        $placa = strtoupper(trim($request->placa));

        $existe = \MasterSoft\Estacionamiento::where('placa', $placa)->where('estado', 0)->exists();

        if ($existe) {
            return response()->json([
                'success' => false, 
                'message' => 'El vehículo con placa ' . $placa . ' ya se encuentra dentro.'
            ], 422);
        }

        $codigo = 'TK-' . time();
        $est = \MasterSoft\Estacionamiento::create([
            'placa' => $placa,
            'codigo_barras' => $codigo,
            'hora_ingreso' => now(),
            'estado' => 0
        ]);
        
        return response()->json(['success' => true, 'ticket' => $est]);
    }

    // Cargar Vista de Cobro
    public function cobrarTicket($codigo) {
        $ticket = \MasterSoft\Estacionamiento::where('codigo_barras', $codigo)->first();
        if(!$ticket) {
            abort(404, 'Ticket no encontrado.');
        }

        $ingreso = \Carbon\Carbon::parse($ticket->hora_ingreso);
        $tiempo = $ingreso->diffInMinutes(now()); 
        if($tiempo == 0) { $tiempo = 1; } 

        $tarifas = DB::table('tarifas_estacionamiento')->where('activo', 1)->get();
        
        $comprobantes = DB::table('tipo_documento')->where('ventas', '1')->where('tdoest', 'Activo')->get();
        $estadopagos = DB::table('credito_dias')->get();
        $documentos = DB::table('tipo_documento_identidad')->get();
        $mediospagos = DB::table('medios_pagos')->get();
        $negocio = DB::table('empresa_negocios')->where('id_empresa_negocio', Auth::user()->id_empresa_negocio)->first();

        return view('empresas.estacionamientos.cobrar', compact('ticket', 'tiempo', 'comprobantes', 'estadopagos', 'documentos', 'mediospagos', 'negocio','tarifas'));
    }

    // SIMPLIFICADO: Registrar Cobro Exclusivo para Estacionamiento Libre
    public function registrar_cobro(Request $request) {
        DB::beginTransaction();
        try {
            $rucemp = trim(Auth::user()->IdEmpresa);
            $empresa = Empresa::findOrFail($rucemp);
            $sucursal = DB::table('empresa_negocios')->where('id_empresa_negocio', Auth::user()->id_empresa_negocio)->first();
            $bus_alm = DB::table('almacenes')->where('id_empresa_negocio', Auth::user()->id_empresa_negocio)->where('predeterminado', '1')->first();
            $empresanegocio = EmpresaNegocios::findOrFail($sucursal->id_empresa_negocio);

            $tdocod = $request->get('tdocod');
            $tdicod = $request->get('tdicod');
            $clinum = $request->get('clinum');
            $clinom = $request->get('clinom');
            $clidir = $request->get('clidir');
            $total_venta = $request->get('total_venta');
            $fecEmi = $request->get('fecEmi');
            $ticket_id = $request->get('ticket_id'); 
            $tarifa_id = $request->get('tarifa_id'); // Capturamos el ID de la tarifa
            
            // Validar Factura
            $cont_carac = strlen($clinum);
            $obt_dig = substr(trim($clinum), 0, 2);
            if($tdocod == '01'){
                if(!($cont_carac == '11' && ($obt_dig == '10' || $obt_dig == '20' || $obt_dig == '15' || $obt_dig == '17') && $tdicod == '6')){
                    return response()->json(['estado'=>'error','mensaje'=>'TIPO DE DOCUMENTO NO PERMITIDO PARA EMITIR UNA FACTURA']);
                }
            }

            // Obtener Correlativo
            if($tdocod == '01'){
                $numcomp = $empresanegocio->FnuEmpresa + 1;
                $sercomp = $empresanegocio->FseEmpresa;
                $empresanegocio->FnuEmpresa = $numcomp;
            } elseif ($tdocod == '03') {
                $numcomp = $empresanegocio->BnuEmpresa + 1;
                $sercomp = $empresanegocio->BseEmpresa;
                $empresanegocio->BnuEmpresa = $numcomp;
            } else {
                $numcomp = $empresanegocio->NumNota + 1;
                $sercomp = $empresanegocio->SerNota;
                $empresanegocio->NumNota = $numcomp;
            }

            // Crear/Actualizar Cliente
            $cliente = Cliente::UpdateOrCreate(
                ['clinum' => $clinum],
                [
                    'clinom' => $clinom,
                    'clidir' => $clidir,
                    'tdicod' => $tdicod,
                    'rucemp' => Auth::user()->IdEmpresa
                ]
            );

            // Cabecera del CPE
            $cabecera = new cpe_cabecera;
            $cabecera->tdocod = $tdocod;
            $cabecera->serdoc = $sercomp;
            $cabecera->numdoc = str_pad($numcomp, 8, "0", STR_PAD_LEFT);
            $cabecera->ccafem = $fecEmi;
            $cabecera->ccafve = $fecEmi; 
            $cabecera->topcod = '0101';
            $cabecera->id_almacen = $bus_alm->id_almacen;
            $cabecera->totalcontado = $total_venta;
            $cabecera->totalcredito = '0';
            $cabecera->tdicod = $tdicod;
            $cabecera->ccandi = $clinum;
            $cabecera->ccanom = $clinom;
            $cabecera->direccion = $clidir;
            $cabecera->moncod = 'PEN';
            $cabecera->estadopago = 'CONTADO';
            $cabecera->id_turno = Auth::user()->id_turno;
            $cabecera->IdUsuario = Auth::user()->IdUsuario;
            $cabecera->IdEmpresa = Auth::user()->IdEmpresa;
            $cabecera->id_empresa_negocio = $sucursal->id_empresa_negocio;
            $cabecera->clicod = $cliente->clicod;
            $cabecera->vuelto = $request->get('vuelto');
            $cabecera->paga = $request->get('pagar');

            // Cálculos de IGV en la Cabecera
            if($sucursal->tip_igv_pred == '10'){
                $cabecera->ccatvg = round($total_venta / 1.18, 2); 
                $cabecera->ccaigv = round($total_venta - ($total_venta / 1.18), 2);
            } else {
                $cabecera->ccatexo = $total_venta;
                $cabecera->ccaigv = '0.00';
            }
            $cabecera->ccaitv = $total_venta;

            $empresanegocio->update();
            $cabecera->save();

            // Medio de Pago
            DB::table('venta_medio_pago')->insert([
                'id_turno' => Auth::user()->id_turno,
                'IdCpe_cabecera' => $cabecera->IdCpe_cabecera,
                'id_med_pag' => $request->get('med_pag'),
                'monto' => $total_venta
            ]);

            // ==========================================================
            // DETALLE DE FACTURACIÓN: Guardando el nombre de la tarifa
            // ==========================================================
            // Buscamos el nombre de la tarifa seleccionada
            $info_tarifa = DB::table('tarifas_estacionamiento')->where('id', $tarifa_id)->first();
            $nombre_procod = $info_tarifa ? strtoupper($info_tarifa->nombre) : 'ESTACIONAMIENTO';

            $tipo_igv = ($sucursal->tip_igv_pred == '10') ? '10' : '20'; 
            $valor_uni = ($tipo_igv == '10') ? ($total_venta / 1.18) : $total_venta;
            $valor_igv_total = $total_venta - $valor_uni;

            $detalle = new cpe_detalle;
            $detalle->IdCpe_cabecera = $cabecera->IdCpe_cabecera;
            $detalle->cdecan = 1;
            $detalle->cdepuni = $total_venta;
            $detalle->cdevun = $valor_uni;
            $detalle->cdevve  = $total_venta;
            $detalle->cdepve  = $valor_uni;
            $detalle->cdeigv = $valor_igv_total;
            $detalle->tigcod = $tipo_igv;
            $detalle->umecod = 'ZZ'; 
            $detalle->procod = $nombre_procod; // Aquí guardamos el nombre de la tarifa[cite: 2]
            $detalle->cdedes = $request->get('descripcion')[0];
            $detalle->id_almacen_pro = $bus_alm->id_almacen;
            $detalle->save();
            // ==========================================================

            // Registrar en Caja
            $usuario_facturacion = new usuario_facturacion;
            $usuario_facturacion->IdCpe_cabecera = $cabecera->IdCpe_cabecera;
            $usuario_facturacion->id_turno = Auth::user()->id_turno;
            $usuario_facturacion->id_empresa_negocio = $sucursal->id_empresa_negocio;
            $usuario_facturacion->IdEmpresa = Auth::user()->IdEmpresa;
            $usuario_facturacion->referencia = "Registro Estacionamiento";
            $usuario_facturacion->save();

            // Enviar a SUNAT (Generación XML)
            if($tdocod == '01' || $tdocod == '03'){
                $sunat = new cpe_cabecera;
                $sunat->generar_nuevo_qr($cabecera->IdCpe_cabecera);
                $sunat->generar_xml_boleta_factura($cabecera->IdCpe_cabecera);

                if($empresa->tipo_envio == '1'){
                    $sunat->enviar_sunat($cabecera->IdCpe_cabecera); 
                }
            }

            // Actualizar Estado a FINALIZADO (1)
            if ($ticket_id) {
                \MasterSoft\Estacionamiento::where('id', $ticket_id)->update(['estado' => 1]);
            }

            DB::commit();

            return response()->json([
                'estado' => 'success',
                'codfact' => $cabecera->IdCpe_cabecera,
                'serie_correlativo' => $cabecera->serdoc . '-' . $cabecera->numdoc,
                'mensaje' => 'Comprobante Emitido'
            ]);

        } catch(\Exception $e) {
            DB::rollback();
            return response()->json(['estado' => 'error', 'mensaje' => $e->getMessage()]);
        }
    }

    public function registrarSalida(Request $request, $codigo) {
        $est = \MasterSoft\Estacionamiento::where('codigo_barras', $codigo)->where('estado', 0)->first();
        
        if(!$est) {
            return response()->json(['success' => false, 'message' => 'Ticket no encontrado o ya procesado.']);
        }

        $tarifa = DB::table('tarifas_estacionamiento')->where('activo', 1)->first();

        if(!$tarifa) {
            return response()->json(['success' => false, 'message' => 'No hay tarifas activas configuradas.']);
        }

        $minutos = \Carbon\Carbon::parse($est->hora_ingreso)->diffInMinutes(now());
        if($minutos == 0) $minutos = 1;
        $total = $this->calcularCostoEstacionamiento($minutos, $tarifa);

        $est->update([
            'hora_salida' => now(), 
            'monto_total' => round($total, 2), 
            'tarifa_id' => $tarifa->id
        ]);

        return response()->json([
            'success' => true, 
            'placa'   => $est->placa,      
            'tiempo'  => $minutos,         
            'total'   => round($total, 2),
            'mensaje' => 'Cálculo realizado con tarifa: ' . $tarifa->nombre
        ]);
    }

    public function indexTarifas() {
        $tarifas = DB::table('tarifas_estacionamiento')->get();
        return view('empresas.estacionamientos.tarifas', compact('tarifas'));
    }

    public function guardarTarifa(Request $request) {
        DB::table('tarifas_estacionamiento')->insert([
            'nombre' => $request->nombre,
            'precio_primera_hora' => $request->precio_primera_hora,
            'precio_hora_adicional' => $request->precio_hora_adicional,
            'descuento_progresivo' => $request->descuento_progresivo,
            'tipo' => 'progresiva',
            'activo' => 1
        ]);
        return redirect()->back()->with('success', 'Tarifa registrada con éxito.');
    }

    // Adaptado para procesar valores NULL de la base de datos (Ej: Tarifa FLAT)
    public function calcularCostoEstacionamiento($minutos, $tarifa) {
        $horas = ceil($minutos / 60);
        $total = 0;
        
        // Si el descuento viene NULL en la base de datos, lo tratamos como 0
        $descuento = $tarifa->descuento_progresivo ? ($tarifa->descuento_progresivo / 100) : 0;

        if ($horas <= 1) {
            $total = $tarifa->precio_primera_hora;
        } else {
            $total = $tarifa->precio_primera_hora;
            $precio_con_desc = $tarifa->precio_hora_adicional * (1 - $descuento);
            $total += (($horas - 1) * $precio_con_desc);
        }
        return $total;
    }
}