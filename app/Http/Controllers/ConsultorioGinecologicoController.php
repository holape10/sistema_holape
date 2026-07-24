<?php

namespace MasterSoft\Http\Controllers;

use Illuminate\Http\Request;
use MasterSoft\Cliente;
use MasterSoft\ConsultaGinecologica;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ConsultorioGinecologicoController extends Controller
{
    public function index(Request $request)
    {
        $buscar = $request->get('buscar');

        // Usamos el modelo ConsultaGinecologica pero hacemos JOIN con el modelo Cliente
        $consultas = ConsultaGinecologica::with('paciente')
            ->whereHas('paciente', function($q) use ($buscar) {
                if($buscar) {
                    $q->where('clinom', 'LIKE', '%' . $buscar . '%')
                      ->orWhere('clinum', 'LIKE', '%' . $buscar . '%');
                }
            })
            ->orderBy('fecha_consulta', 'desc')
            ->paginate(100);

        return view('empresas.Consultorio.index', compact('consultas', 'buscar'));
    }

    public function create()
    {
        return view('empresas.Consultorio.create');
    }

    /*public function imprimirReporte($id)
    {
        // Buscamos la consulta específica con los datos de su paciente
        $consulta = ConsultaGinecologica::with('paciente')->findOrFail($id);
        
        return view('empresas.Consultorio.reporte', compact('consulta'));
    }*/
    public function imprimirReporte($id)
    {
        $consulta = ConsultaGinecologica::with('paciente')->findOrFail($id);
        $idEmpresa = Auth::user()->IdEmpresa;

        // Jalamos los datos dinámicos de tu configuración de negocio
        $negocio = \MasterSoft\EmpresaNegocios::where('IdEmpresa', $idEmpresa)->first();
        $empresa = \MasterSoft\Empresa::find($idEmpresa);

        return view('empresas.Consultorio.reporte', compact('consulta', 'negocio', 'empresa'));
    }

    /*public function store(Request $request)
    {
        $clicod = $request->input('clicod');

        if (empty($clicod)) {
            $nuevoCliente = new Cliente();
            $nuevoCliente->clinum = $request->input('clinum');
            $nuevoCliente->clinom = strtoupper($request->input('clinom'));
            $nuevoCliente->clidir = $request->input('clidir', '--');
            $nuevoCliente->tdicod = strlen($request->input('clinum')) == 11 ? '6' : '1';
            $nuevoCliente->rucemp = Auth::user()->IdEmpresa ?? '00000000000'; // RUC de tu empresa
            $nuevoCliente->save();

            // Obtenemos el ID autoincremental recién creado
            $clicod = $nuevoCliente->clicod;
        }

        // Guardamos la consulta médica vinculada al clicod
        ConsultaGinecologica::create([
            'clicod' => $clicod,
            'fecha_consulta' => now(),
            'motivo_consulta' => $request->input('motivo_consulta'),
            'exploracion_fisica' => $request->input('exploracion_fisica'),
            'diagnostico' => $request->input('diagnostico'),
            'tratamiento' => $request->input('tratamiento'),
        ]);

        return redirect()->route('consultorio.index')
                         ->with('success', 'Consulta registrada y paciente vinculado exitosamente.');
    }*/

    public function store(Request $request)
    {
        $clicod = $request->input('clicod');

        if (empty($clicod)) {
            $nuevoCliente = new Cliente();
            $nuevoCliente->clinum = $request->input('clinum');
            $nuevoCliente->clinom = strtoupper($request->input('clinom'));
            $nuevoCliente->clidir = $request->input('clidir', '--');
            $nuevoCliente->tdicod = strlen($request->input('clinum')) == 11 ? '6' : '1';
            $nuevoCliente->rucemp = Auth::user()->IdEmpresa ?? '00000000000';
            $nuevoCliente->save();

            $clicod = $nuevoCliente->clicod;
        }

        // Guardamos la consulta y la capturamos en una variable para sacar su ID
        $consulta = ConsultaGinecologica::create([
            'clicod' => $clicod,
            'fecha_consulta' => now(),
            'motivo_consulta' => $request->input('motivo_consulta'),
            'exploracion_fisica' => $request->input('exploracion_fisica'),
            'diagnostico' => $request->input('diagnostico'),
            'tratamiento' => $request->input('tratamiento'),
        ]);

        // Guardamos en la sesión el ID para disparar el script de impresión en el index
        return redirect()->route('consultorio.index')
                         ->with('success', 'Consulta registrada exitosamente.')
                         ->with('abrir_reporte_id', $consulta->id);
    }

    public function show($id)
    {
        // En este caso, el $id será el clicod del paciente.
        // Buscamos los datos del paciente
        $paciente = Cliente::where('clicod', $id)->firstOrFail();

        // Buscamos todas sus consultas, de la más reciente a la más antigua
        $historial = ConsultaGinecologica::where('clicod', $id)
                                         ->orderBy('fecha_consulta', 'desc')
                                         ->get();

        return view('empresas.Consultorio.show', compact('paciente', 'historial'));
    }

    // Tu función de autocompletado integrada aquí
    public function autocompleteClient(Request $request)
    {
        $query = $request->input('query');
        $field = $request->input('field'); // 'clinum' or 'clinom'
        $IdEmpresa = Auth::user()->IdEmpresa; // Asumiendo que esta es la ID principal del RUC de tu empresa.

        // Primero, intentar buscar en la base de datos local
        $clientes_locales = Cliente::where('rucemp', $IdEmpresa)
                                    ->where(function($q) use ($query, $field) {
                                        if ($field === 'clinum') {
                                            $q->where('clinum', 'like', $query . '%');
                                        } else { // field is 'clinom'
                                            $q->where('clinom', 'like', '%' . $query . '%');
                                        }
                                    })
                                    ->limit(10)
                                    ->get(['clicod', 'clinum', 'clinom', 'clidir', 'tdicod', 'clicontel']); // Cambié telefono a clicontel según tu modelo

        $results = [];

        // Añadir clientes locales a los resultados
        foreach ($clientes_locales as $cli) {
            $results[] = [
                'label' => "{$cli->clinom} ({$cli->clinum})", 
                'value' => ($field === 'clinum') ? $cli->clinum : $cli->clinom,
                'nom' => $cli->clinom,
                'num' => $cli->clinum, 
                'dir' => $cli->clidir,
                'tdicod' => $cli->tdicod,
                'clicod' => $cli->clicod,
                'tel' => $cli->clicontel
            ];
        }

        // Si la búsqueda es por número y no se encontraron suficientes resultados localmente, intentar con la API
        if ($field === 'clinum' && count($results) < 10) { 
            $api_token = 'c7c656604942b0a6df5fa225835e99eb7376cf841d38781b91f651f72e03cc09'; 
                                                                                                            
            $tipo_documento_api = '';
            $numero_documento_api = '';

            if (strlen($query) == 11 && is_numeric($query)) {
                $tipo_documento_api = 'ruc';
                $numero_documento_api = $query;
            } elseif (strlen($query) == 8 && is_numeric($query)) {
                $tipo_documento_api = 'dni';
                $numero_documento_api = $query;
            }
            
            if (!empty($tipo_documento_api)) {
                try {
                    $params = json_encode([$tipo_documento_api => $numero_documento_api]);
                    $curl = curl_init();
                    curl_setopt_array($curl, array(
                        CURLOPT_URL => "https://apiperu.dev/api/{$tipo_documento_api}",
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_CUSTOMREQUEST => "POST",
                        CURLOPT_SSL_VERIFYPEER => false, 
                        CURLOPT_POSTFIELDS => $params,     
                        CURLOPT_HTTPHEADER => [
                            'Accept: application/json',
                            'Content-Type: application/json',
                            'Authorization: Bearer ' . $api_token
                        ],     
                    ));
                    $response = curl_exec($curl);
                    $err = curl_error($curl);
                    curl_close($curl);

                    if ($err) {
                        Log::error("Error de cURL al consultar API: " . $err);
                    } else {
                        $api_data = json_decode($response, true);

                        if (!empty($api_data) && isset($api_data['data'])) { 
                            $data_from_api = $api_data['data']; 

                            $numero_documento = $data_from_api['numero'] ?? $data_from_api['ruc'] ?? $query;
                            $nombre_razon_social = $data_from_api['nombres'] ?? $data_from_api['nombre_o_razon_social'] ?? 'N/A';
                            if (isset($data_from_api['apellido_paterno'])) $nombre_razon_social .= ' ' . $data_from_api['apellido_paterno'];
                            if (isset($data_from_api['apellido_materno'])) $nombre_razon_social .= ' ' . $data_from_api['apellido_materno'];
                            $direccion = $data_from_api['direccion_completa'] ?? $data_from_api['direccion'] ?? '--';
                            $tipo_doc = (strlen($numero_documento) == 11) ? '6' : '1'; 

                            $already_in_results = false;
                            foreach ($results as $res) {
                                if ($res['num'] === $numero_documento) {
                                    $already_in_results = true;
                                    break;
                                }
                            }

                            if (!$already_in_results) {
                                $results[] = [
                                    'label' => "(API) {$nombre_razon_social} ({$numero_documento})",
                                    'value' => ($field === 'clinum') ? $numero_documento : $nombre_razon_social,
                                    'nom' => $nombre_razon_social,
                                    'num' => $numero_documento,
                                    'dir' => $direccion,
                                    'tdicod' => $tipo_doc,
                                    'clicod' => null,
                                    'tel' => null
                                ];
                            }
                        }
                    }
                } catch (\Exception $e) {
                    Log::error("Error al consultar API: " . $e->getMessage());
                }
            }
        }
        
        return response()->json($results);
    }
}