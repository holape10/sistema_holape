<?php

namespace MasterSoft\Http\Controllers;

use Illuminate\Http\Request;
use MasterSoft\Brazalete;
//use MasterSoft\VisitaSauna; // Asumiendo que creaste este modelo
use MasterSoft\Cliente;     // Tu modelo de cliente actual
use DB;

class SaunaController extends Controller
{
    public function index()
    {
        return view('empresas.sauna.recepcion');
    }

    public function procesarEscaneoRFID(Request $request)
    {

        $request->validate([
            'codigo_rfid' => 'required_without:numero_casillero',
            'numero_casillero' => 'required_without:codigo_rfid'
        ]);
        $brazalete = null;

        if ($request->filled('numero_casillero')) {
            $numero = $request->input('numero_casillero');
            
            // Buscamos coincidencia exacta o aproximada en tu columna 'numero_casillero'
            // Ej: Si el usuario escribe "1", buscará "Casillero 01" o "1"
            $brazalete = \MasterSoft\Brazalete::where('numero_casillero', $numero)
                ->orWhere('numero_casillero', 'LIKE', '%' . $numero)
                ->first();

            if (!$brazalete) {
                return redirect()->back()->with('error', 'No se encontró ningún casillero con el número: ' . $numero);
            }
        } 
        // ACCIÓN B: Si es el escaneo normal del lector RFID
        else {
            $brazalete = \MasterSoft\Brazalete::where('codigo_rfid', $request->input('codigo_rfid'))->first();

            if (!$brazalete) {
                return redirect()->back()->with('error', 'El código RFID escaneado no está registrado.');
            }
        }
        
        $codigoRfid = $request->input('codigo_rfid');

        $brazalete = Brazalete::where('codigo_rfid', $codigoRfid)->first();

        if (!$brazalete) {
            return redirect()->back()->with('error', 'El código RFID ('. $codigoRfid .') no está registrado.');
        }

        // Si el brazalete está DISPONIBLE, vamos a hacer el Check-In
        if ($brazalete->estado == 'disponible') {
            // Jalamos los clientes de tu tabla actual para el buscador/select
            // Ajusta 'cliente' y 'clicod' si tuvieras otros campos como el nombre (ej: clinom)
            $clientes = DB::table('cliente')->select('clicod', 'clinum', 'clinom')->limit(100)->get(); 
            return view('empresas.sauna.asignar_cliente', compact('brazalete', 'clientes'));
        } 
        
        // Si el brazalete ya está EN USO, mostraremos su cuenta (Esto lo haremos en el siguiente paso)
        if ($brazalete->estado == 'en_uso') {
            $visitaActiva = DB::table('visitas_sauna')
                ->join('cliente', 'visitas_sauna.cliente_id', '=', 'cliente.clicod')
                ->where('visitas_sauna.brazalete_id', $brazalete->id)
                ->where('visitas_sauna.estado', 'abierta')
                ->select('visitas_sauna.*', 'cliente.clinom', 'cliente.clinum')
                ->first();

            if (!$visitaActiva) {
                return redirect()->back()->with('error', 'La pulsera dice En Uso, pero no hay ninguna visita abierta en el sistema.');
            }

            $horaIngreso = new \DateTime($visitaActiva->fecha_ingreso);
            $horaActual = new \DateTime(date('Y-m-d H:i:s'));
            $diferencia = $horaIngreso->diff($horaActual);
            $tiempoTranscurrido = $diferencia->format('%h horas y %i minutos');

            // AQUÍ CORREGIMOS EL NOMBRE DE LA COLUMNA A 'pronom'
            $consumosActuales = DB::table('consumos_sauna')
                ->join('productos', 'consumos_sauna.producto_id', '=', 'productos.IdProducto')
                ->where('consumos_sauna.visita_sauna_id', $visitaActiva->id)
                ->select('consumos_sauna.*', 'productos.pronom') 
                ->get();

            // AQUÍ CORREGIMOS A 'pronom' y 'propun'
            $productosVenta = DB::table('productos')
                ->select('IdProducto', 'pronom', 'propun')
                ->limit(100) // Opcional, para no saturar el select si tienes miles
                ->get();

            return redirect()->route('sauna.cuenta_activa', $brazalete->id);
        }
    }

    public function verCuentaActiva($id_brazalete)
    {
        $brazalete = \MasterSoft\Brazalete::findOrFail($id_brazalete);

        $visitaActiva = DB::table('visitas_sauna')
            ->join('cliente', 'visitas_sauna.cliente_id', '=', 'cliente.clicod')
            ->where('visitas_sauna.brazalete_id', $brazalete->id)
            ->where('visitas_sauna.estado', 'abierta')
            ->select('visitas_sauna.*', 'cliente.clinom', 'cliente.clinum')
            ->first();

        if (!$visitaActiva) {
            return redirect()->route('sauna.recepcion')->with('error', 'No hay visita abierta.');
        }

        $horaIngreso = new \DateTime($visitaActiva->fecha_ingreso);
        $horaActual = new \DateTime(date('Y-m-d H:i:s'));
        $tiempoTranscurrido = $horaIngreso->diff($horaActual)->format('%h horas y %i min');

        // JALAMOS DE TU TABLA REAL: pedidos_detalle
        $consumosActuales = DB::table('pedidos_detalle')
            ->where('ped_id', $visitaActiva->pedido_id) // Usamos el ID del pedido maestro
            ->get();

        $productosVenta = DB::table('productos')
            ->select('IdProducto', 'pronom', 'propun', 'codigo_barra')
            ->where('pronom', '!=', '')
            ->get();

        return view('empresas.sauna.cuenta_activa', compact('brazalete', 'visitaActiva', 'tiempoTranscurrido', 'consumosActuales', 'productosVenta'));
    }

    public function autocompleteCliente(Request $request, $cliente)
    {
        try {
            $field = $request->input('field', 'clinum'); 
            $results = [];

            // 1. Búsqueda por NOMBRE si contiene letras
            if (!is_numeric($cliente)) {
                $busqueda = DB::table('cliente')
                             ->where('clinom', 'LIKE', '%' . $cliente . '%')
                             ->take(10)
                             ->get();

                foreach ($busqueda as $cli) {
                    $results[] = [
                        'clicod' => $cli->clicod,
                        'num' => $cli->clinum,
                        'nom' => $cli->clinom,
                        'dir' => $cli->clidir ?? '',
                        'tdicod' => $cli->tdicod ?? '1',
                        'label' => $cli->clinom . ' (' . $cli->clinum . ')',
                        'es_nuevo' => false
                    ];
                }
            } 
            
            // 2. Búsqueda por DNI / RUC local
            if (is_numeric($cliente) || empty($results)) {
                $busquedaLocal = DB::table('cliente')
                             ->where('clinum', 'LIKE', $cliente . '%')
                             ->take(5)
                             ->get();

                foreach ($busquedaLocal as $cli) {
                    $results[] = [
                        'clicod' => $cli->clicod,
                        'num' => $cli->clinum,
                        'nom' => $cli->clinom,
                        'dir' => $cli->clidir ?? '',
                        'tdicod' => $cli->tdicod ?? '1',
                        'label' => $cli->clinom . ' (' . $cli->clinum . ')',
                        'es_nuevo' => false
                    ];
                }

                // 3. SI NO SE ENCONTRÓ LOCALMENTE, LLAMAMOS A TU API PERÚ
                if (empty($results) && is_numeric($cliente)) {
                    if (strlen($cliente) === 8) {
                        $leer_respuesta = $this->consultardni($cliente);
                        if (isset($leer_respuesta['data'])) {
                            $results[] = [
                                'clicod' => null,
                                'num' => $leer_respuesta['data']['numero'],
                                'nom' => $leer_respuesta['data']['nombres'] . ' ' . $leer_respuesta['data']['apellido_paterno'] . ' ' . $leer_respuesta['data']['apellido_materno'],
                                'dir' => '',
                                'tdicod' => '1', // DNI
                                'label' => '(API DNI) ' . $leer_respuesta['data']['nombres'] . ' ' . $leer_respuesta['data']['apellido_paterno'],
                                'es_nuevo' => true
                            ];
                        }
                    } elseif (strlen($cliente) === 11) {
                        $leer_respuesta = $this->consultaruc($cliente);
                        if (isset($leer_respuesta['data'])) {
                            $results[] = [
                                'clicod' => null,
                                'num' => $leer_respuesta['data']['ruc'],
                                'nom' => $leer_respuesta['data']['nombre_o_razon_social'],
                                'dir' => $leer_respuesta['data']['direccion_completa'] ?? '',
                                'tdicod' => '6', // RUC
                                'label' => '(API RUC) ' . $leer_respuesta['data']['nombre_o_razon_social'],
                                'es_nuevo' => true
                            ];
                        }
                    }
                }
            }

            return response()->json($results);

        } catch (\Exception $e) {
            Log::error('Error en autocomplete Sauna: ' . $e->getMessage());
            return response()->json([]);
        }
    }

    public function agregarConsumo(Request $request)
    {
        $request->validate(['visita_sauna_id' => 'required', 'cantidad' => 'required|numeric|min:1']);
        
        $visita = DB::table('visitas_sauna')->where('id', $request->input('visita_sauna_id'))->first();
        $producto = null;

        if ($request->filled('codigo_barra')) {
            $producto = DB::table('productos')->where('codigo_barra', $request->input('codigo_barra'))->first();
        } elseif ($request->filled('producto_id')) {
            $producto = DB::table('productos')->where('IdProducto', $request->input('producto_id'))->first();
        }

        if (!$producto || !$visita) {
            return redirect()->back()->with('error', 'Producto no encontrado o código incorrecto.');
        }

        $cantidad = $request->input('cantidad');
        $idEmpresa = \Auth::user()->IdEmpresa ?? '20610257705';

        // GUARDAMOS EN TU TABLA pedidos_detalle
        DB::table('pedidos_detalle')->insert([
            'ped_id' => $visita->pedido_id,
            'IdProducto' => $producto->IdProducto,
            'IdEmpresa' => $idEmpresa,
            'detalle' => $producto->pronom,
            'nombre' => $producto->pronom,
            'cantidad' => $cantidad,
            'precio' => $producto->propun,
            'estadoitem' => 'Ingresado',
            'impreso' => 'impreso',
            'created_at' => date('Y-m-d H:i:s')
        ]);

        return redirect()->back()->with('success', "¡Se cargó {$cantidad}x {$producto->pronom} al casillero!");
    }

    // Guardar nuevo cliente desde la vista de asignación de forma veloz
    public function registrarClienteRapido(Request $request)
    {
        $request->validate([
            'clinum' => 'required',
            'clinom' => 'required'
        ]);

        try {
            $rucemp = Auth::user()->IdEmpresa ?? '1';

            // Insertamos directamente en tu tabla actual 'cliente'
            // NOTA: Si tu columna 'clicod' no es AUTO_INCREMENT, calcula aquí el correlativo.
            $clicod = DB::table('cliente')->insertGetId([
                'clinum' => $request->input('clinum'),
                'clinom' => $request->input('clinom'),
                'clidir' => $request->input('clidir') ?? '',
                'tdicod' => $request->input('tdicod') ?? '1',
                'rucemp' => $rucemp,
                // Agrega campos obligatorios adicionales que use tu tabla si da error
            ]);

            return response()->json([
                'success' => true,
                'clicod' => $clicod,
                'clinom' => $request->input('clinom')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar cliente: ' . $e->getMessage()
            ], 500);
        }
    }

    // --- TUS MÉTODOS PRIVADOS DE CONSULTA API ---
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
        if ($err) { throw new \Exception("Error cURL RUC: " . $err); } 
        return json_decode($response, true);
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
        if ($err) { throw new \Exception("Error cURL DNI: " . $err); }
        return json_decode($response, true);
    }

    // Método para registrar el ingreso en la Base de Datos
    public function guardarCheckIn(Request $request)
    {
        $request->validate(['brazalete_id' => 'required', 'cliente_id' => 'required']);

        DB::beginTransaction();
        try {
            $cliente = DB::table('cliente')->where('clicod', $request->input('cliente_id'))->first();
            $idEmpresa = \Auth::user()->IdEmpresa ?? '20610257705';

            // CREAMOS EL PEDIDO MASTER EN TU TABLA ORIGINAL 'pedidos'
            $ped_id = DB::table('pedidos')->insertGetId([
                'pagado' => 0,
                'ped_tip' => 'Sauna',
                'ped_fec' => date('Y-m-d'),
                'IdEmpresa' => $idEmpresa,
                'ped_est' => 'Aperturado',
                'ped_nom' => $cliente->clinom,
                'created_at' => date('Y-m-d H:i:s')
            ]);

            DB::table('visitas_sauna')->insert([
                'cliente_id' => $request->input('cliente_id'),
                'brazalete_id' => $request->input('brazalete_id'),
                'pedido_id' => $ped_id, // ENLAZAMOS EL PEDIDO AL CASILLERO
                'fecha_ingreso' => date('Y-m-d H:i:s'),
                'estado' => 'abierta',
                'created_at' => date('Y-m-d H:i:s')
            ]);

            \MasterSoft\Brazalete::where('id', $request->input('brazalete_id'))->update(['estado' => 'en_uso']);

            DB::commit();
            return redirect()->route('sauna.recepcion')->with('success', '¡Ingreso registrado y Pedido Aperturado!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('sauna.recepcion')->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function editarBrazalete($id)
    {
        $brazalete = Brazalete::findOrFail($id);
        return view('empresas.sauna.brazaletes_edit', compact('brazalete'));
    }

    // Procesa la actualización en la Base de Datos
    public function actualizarBrazalete(Request $request, $id)
    {
        $request->validate([
            'numero_casillero' => 'required',
            // Validamos que el RFID sea único, pero ignorando el ID del brazalete actual
            'codigo_rfid' => 'required|unique:brazaletes,codigo_rfid,' . $id
        ]);

        $brazalete = Brazalete::findOrFail($id);
        $brazalete->update([
            'numero_casillero' => $request->input('numero_casillero'),
            'codigo_rfid' => $request->input('codigo_rfid'),
            'estado' => $request->input('estado') // Añadimos poder cambiar estado (ej. Mantenimiento)
        ]);

        return redirect()->route('sauna.brazaletes.index')->with('success', '¡Brazalete/Casillero actualizado con éxito!');
    }

    public function listarBrazaletes()
    {
        $brazaletes = Brazalete::orderBy('numero_casillero', 'asc')->get();
        return view('empresas.sauna.brazaletes_index', compact('brazaletes'));
    }

    // Muestra el formulario para registrar uno nuevo
    public function crearBrazalete()
    {
        return view('empresas.sauna.brazaletes_create');
    }

    // Guarda el nuevo casillero/brazalete en la BD
    public function guardarBrazalete(Request $request)
    {
        $request->validate([
            'numero_casillero' => 'required',
            'codigo_rfid' => 'required|unique:brazaletes,codigo_rfid'
        ]);

        Brazalete::create([
            'numero_casillero' => $request->input('numero_casillero'),
            'codigo_rfid' => $request->input('codigo_rfid'),
            'estado' => 'disponible'
        ]);

        return redirect()->route('sauna.brazaletes.index')->with('success', '¡Brazalete/Casillero registrado con éxito!');
    }
}