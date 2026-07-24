<?php

namespace MasterSoft\Http\Controllers;

use Illuminate\Http\Request;
use MasterSoft\Vehiculo;
use MasterSoft\Chofer;
use DB;
use Exception;

class TransporteController extends Controller
{
    // 1. Vista principal con pestañas (Tabs)
    public function index()
    {
        // Capturamos la empresa en sesión para filtrar
        $id_empresa = session('id_empresa_negocio') ?? 1; 

        $vehiculos = Vehiculo::where('id_empresa_negocio', $id_empresa)->orderBy('id', 'desc')->get();
        $choferes = Chofer::where('id_empresa_negocio', $id_empresa)->orderBy('id', 'desc')->get();

        return view('empresas.transportes.unidades.index', compact('vehiculos', 'choferes'));
    }

    // 2. Guardar Vehículo
    public function storeVehiculo(Request $request)
    {
        $request->validate([
            'placa' => 'required'
        ]);

        $id_empresa = session('id_empresa_negocio') ?? 1;

        // Validar que la placa no esté duplicada en la misma empresa
        $existe = Vehiculo::where('placa', strtoupper($request->placa))
                            ->where('id_empresa_negocio', $id_empresa)
                            ->first();

        if ($existe) {
            return back()->withErrors(['error' => 'La placa ya se encuentra registrada.']);
        }

        Vehiculo::create([
            'placa'              => strtoupper($request->placa),
            'marca'              => strtoupper($request->marca),
            'modelo'             => strtoupper($request->modelo),
            'carga_util_kg'      => $request->carga_util_kg ?? 0,
            'inscripcion_mtc'    => strtoupper($request->inscripcion_mtc),
            'estado'             => 'ACTIVO',
            'id_empresa_negocio' => $id_empresa
        ]);

        return back()->with('success', 'Vehículo registrado correctamente.');
    }

    // 3. Guardar Chofer
    public function storeChofer(Request $request)
    {
        $request->validate([
            'dni'      => 'required',
            'licencia' => 'required'
        ]);

        $id_empresa = session('id_empresa_negocio') ?? 1;

        // Validar que el DNI no esté duplicado en la misma empresa
        $existe = Chofer::where('dni', $request->dni)
                        ->where('id_empresa_negocio', $id_empresa)
                        ->first();

        if ($existe) {
            return back()->withErrors(['error' => 'El DNI de este chofer ya está registrado.']);
        }

        Chofer::create([
            'dni'                => $request->dni,
            'nombres_apellidos'  => strtoupper($request->nombres_apellidos),
            'licencia'           => strtoupper($request->licencia),
            'telefono'           => $request->telefono,
            'estado'             => 'ACTIVO',
            'id_empresa_negocio' => $id_empresa
        ]);

        return back()->with('success', 'Chofer registrado correctamente.');
    }

    // 4. Tu API integrada para buscar DNI o RUC (Consultas rápidas desde la vista)
    public function consultarDocumento(Request $request) 
    {
        $documento = trim($request->get('documento'));
        $token = 'c7c656604942b0a6df5fa225835e99eb7376cf841d38781b91f651f72e03cc09'; 

        if (strlen($documento) === 8) {
            $params = json_encode(['dni' => $documento]);
            $url = "https://apiperu.dev/api/dni";
        } elseif (strlen($documento) === 11) {
            $params = json_encode(['ruc' => $documento]);
            $url = "https://apiperu.dev/api/ruc";
        } else {
            return response()->json(['error' => 'El documento debe tener 8 (DNI) u 11 dígitos (RUC).']);
        }

        $curl = curl_init();
        curl_setopt_array($curl, [
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
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) { 
            return response()->json(['error' => 'Error de conexión: ' . $err]); 
        }

        $data = json_decode($response, true);

        if(isset($data['success']) && $data['success'] == true) {
            if (strlen($documento) === 8) {
                return response()->json([
                    'nom' => $data['data']['nombre_completo']
                ]);
            } else {
                $ubigeo_sunat = $data['data']['ubigeo_sunat'] ?? '000000';
                $ubigeo_cod = is_array($ubigeo_sunat) ? $ubigeo_sunat[0] : $ubigeo_sunat;
                
                $ubigeo_bd = \DB::table('cat_ubigeo')->where('ubi_cod', $ubigeo_cod)->first();
                $ubigeo_des = $ubigeo_bd ? trim($ubigeo_bd->ubi_des) : 'Autocompletado';

                return response()->json([
                    'nom' => $data['data']['nombre_o_razon_social'],
                    'dir' => $data['data']['direccion_completa'],
                    'ubigeo' => $ubigeo_cod,
                    'ubigeo_des' => $ubigeo_des
                ]);
            }
        } else {
            return response()->json(['error' => 'Documento no encontrado en SUNAT/RENIEC.']);
        }
    }
}