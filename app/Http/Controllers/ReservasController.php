<?php

namespace MasterSoft\Http\Controllers;

use Illuminate\Http\Request;
use MasterSoft\pisos;
use MasterSoft\mesas;
use MasterSoft\categorias;
use MasterSoft\productos;
use MasterSoft\Cliente;
use DB;

class ReservasController extends Controller
{

    public function index(Request $request)
    {
        // Obtener reservas, filtrando por fecha si el usuario lo desea
        $fecha = $request->input('fecha', date('Y-m-d'));
        
        $reservas = DB::table('reservas')
            ->join('cliente', 'reservas.clicod', '=', 'cliente.clicod')
            ->join('mesas', 'reservas.mes_id', '=', 'mesas.mes_id')
            ->join('pisos', 'reservas.pis_id', '=', 'pisos.pis_id')
            ->where('fecha_reserva', $fecha)
            ->orderBy('hora_inicio', 'asc')
            ->get();

        return view('empresas.reservas.index', compact('reservas', 'fecha'));
    }

    public function create()
    {
        // Traemos los pisos
        $pisos = pisos::all(); 
        
        // Categorías visibles
        $categorias = categorias::where('visible', '1')->get();
        
        // Productos activos excluyendo los combos (promocion = 4)
        $productos = productos::where(function($query) {
            $query->where('promocion', '!=', '4')
                  ->orWhereNull('promocion');
        })->get();

        return view('empresas.reservas.create', compact('pisos', 'categorias', 'productos'));
    }

    // --- FUNCIONES AJAX ---

    // Obtener mesas según el piso seleccionado
    public function getMesasPorPiso($pis_id)
    {
        $mesas = mesas::where('pis_id', $pis_id)->get();
        return response()->json($mesas);
    }

    // Buscar cliente por su documento (DNI/RUC)
    public function buscarCliente(Request $request)
    {
        $term = $request->input('term');

        if (empty($term)) {
            return response()->json([]);
        }

        // Buscamos coincidencias aproximadas por Nombre O por Número de Documento
        $clientes = DB::table('cliente')
            ->where(function($query) use ($term) {
                $query->where('clinum', 'LIKE', '%' . $term . '%')
                      ->orWhere('clinom', 'LIKE', '%' . $term . '%');
            })
            ->limit(10) // Evita saturar la memoria si hay miles de clientes
            ->get(['clicod', 'clinum', 'clinom']); // Solo jalamos lo necesario

        return response()->json($clientes);
    }

    // 1. Guardar el cliente desde el Modal
    public function storeClienteAjax(Request $request)
    {
        // Determinamos el tdicod (Tipo de documento: 1=DNI, 6=RUC)
        $tdicod = strlen($request->documento) == 11 ? '6' : '1';

        // Obtenemos el RUC dinámico de la sesión del usuario logueado
        // Si tu sistema usa otra variable en la sesión, cámbiala por su nombre aquí
        $rucEmpresa = session('IdEmpresa') ?? (auth()->user() ? auth()->user()->IdEmpresa : '20610257705');

        $cliente = new Cliente();
        $cliente->tdicod = $tdicod;
        $cliente->clinum = $request->documento;
        $cliente->clinom = strtoupper($request->nombre);
        $cliente->clidir = $request->direccion ?? '--';
        $cliente->rucemp = $rucEmpresa; // Asignación dinámica para el multi-tenant
        $cliente->save();

        return response()->json([
            'success' => true, 
            'cliente' => $cliente
        ]);
    }

    // 2. Ruta unificada para buscar en la API externa
    public function buscarApiExterna($documento)
    {
        try {
            if (strlen($documento) == 8) {
                $respuesta = $this->consultardni($documento);
            } elseif (strlen($documento) == 11) {
                $respuesta = $this->consultaruc($documento);
            } else {
                return response()->json(['success' => false, 'message' => 'Documento inválido']);
            }

            return response()->json($respuesta);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    // 3. Tus funciones privadas de API Perú
    private function consultaruc($ruc)
    {
        $params = json_encode(['ruc' => $ruc]);
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://apiperu.dev/api/ruc",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_POSTFIELDS => $params,
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
                'Content-Type: application/json',
                'Authorization: Bearer c7c656604942b0a6df5fa225835e99eb7376cf841d38781b91f651f72e03cc09'
            ],
        ));
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) { throw new \Exception("Error cURL: " . $err); } 
        return json_decode($response, true);
    }

    public function store(Request $request)
    {
        // Validar datos básicos
        $request->validate([
            'clicod' => 'required',
            'pis_id' => 'required',
            'mes_id' => 'required',
            'fecha_reserva' => 'required|date',
            'hora_inicio' => 'required',
            'hora_fin' => 'required',
            'productos' => 'required|array'
        ]);

        // OBTENCIÓN DINÁMICA: Buscamos el piso seleccionado para saber a qué empresa pertenece
        $piso = DB::table('pisos')->where('pis_id', $request->pis_id)->first();
        $idEmpresa = $piso ? $piso->emp_id : null; // Jalamos el RUC real de la base de datos

        if (!$idEmpresa) {
            return back()->with('error', 'No se pudo determinar la empresa asignada a esta zona.');
        }

        DB::beginTransaction();
        try {
            // 1. Guardar Cabecera de Reserva con el RUC dinámico
            $reservaId = DB::table('reservas')->insertGetId([
                'IdEmpresa' => $idEmpresa, // ¡Listo! Funciona para cualquier cliente que implementes
                'clicod' => $request->clicod,
                'pis_id' => $request->pis_id,
                'mes_id' => $request->mes_id,
                'fecha_reserva' => $request->fecha_reserva,
                'hora_inicio' => $request->hora_inicio,
                'hora_fin' => $request->hora_fin,
                'cantidad_personas' => $request->cantidad_personas,
                'total' => $request->total_reserva,
                'observacion' => $request->observacion_general,
                'estado' => 'Confirmada',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 2. Guardar Detalles de Productos
            $detalles = [];
            foreach ($request->productos as $prod) {
                $subtotal = $prod['cantidad'] * $prod['precio'];
                $detalles[] = [
                    'res_id' => $reservaId,
                    'IdProducto' => $prod['id'],
                    'cantidad' => $prod['cantidad'],
                    'precio_unitario' => $prod['precio'],
                    'subtotal' => $subtotal,
                    'observacion_producto' => $prod['observacion'] ?? null
                ];
            }
            
            DB::table('reserva_detalle')->insert($detalles);

            DB::commit();

            // Redirigir a la ruta que muestra el ticket para imprimir
            return redirect()->route('reservas.ticket', $reservaId);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al guardar la reserva: ' . $e->getMessage());
        }
    }

    public function imprimirTicket($id)
    {
        $reserva = DB::table('reservas')
            ->join('cliente', 'reservas.clicod', '=', 'cliente.clicod')
            ->join('mesas', 'reservas.mes_id', '=', 'mesas.mes_id')
            ->join('pisos', 'reservas.pis_id', '=', 'pisos.pis_id')
            ->join('empresa', 'reservas.IdEmpresa', '=', 'empresa.IdEmpresa')
            ->where('res_id', $id)
            ->first();

        $detalles = DB::table('reserva_detalle')
            ->join('productos', 'reserva_detalle.IdProducto', '=', 'productos.IdProducto')
            ->where('res_id', $id)
            ->get();

        return view('empresas.reservas.ticket', compact('reserva', 'detalles'));
    }

    private function consultardni($dni)
    {
        $params = json_encode(['dni' => $dni]);
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://apiperu.dev/api/dni",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_POSTFIELDS => $params,
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
                'Content-Type: application/json',
                'Authorization: Bearer c7c656604942b0a6df5fa225835e99eb7376cf841d38781b91f651f72e03cc09'
            ],
        ));
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) { throw new \Exception("Error cURL: " . $err); } 
        return json_decode($response, true);
    }
}