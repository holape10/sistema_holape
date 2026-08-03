<?php

namespace MasterSoft\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SunatController extends Controller
{
    /**
     * Consulta de RUC en el Padrón Reducido SUNAT
     * Compatible con PHP 7.2 y Laravel 5.6
     *
     * @param string $ruc
     * @return \Illuminate\Http\JsonResponse
     */

    public function consultarDni($dni)
    {
        if (!preg_match('/^\d{8}$/', $dni)) {
            return response()->json([
                'success' => false,
                'message' => 'DNI inválido. Debe tener 8 dígitos numéricos'
            ], 400);
        }

        // Buscar RUC que empiece con 10 + DNI (ignora el dígito verificador)
        $contribuyente = DB::table('contribuyentes')
            ->where('ruc', 'LIKE', '10' . $dni . '%')  // ✅ CAMBIO AQUÍ
            ->join('cat_ubigeo', 'contribuyentes.ubigeo', '=', 'cat_ubigeo.ubi_cod', 'left')
            ->select(
                'contribuyentes.ruc',
                'contribuyentes.razon_social',
                'contribuyentes.estado',
                'contribuyentes.condicion',
                'contribuyentes.ubigeo',
                'contribuyentes.tipo_via',
                'contribuyentes.nombre_via',
                'contribuyentes.numero',
                'contribuyentes.interior',
                'contribuyentes.codigo_zona',
                'contribuyentes.tipo_zona',
                'contribuyentes.lote',
                'cat_ubigeo.departamento',
                'cat_ubigeo.provincia',
                'cat_ubigeo.distrito'
            )
            ->first();

        if (!$contribuyente) {
            return response()->json([
                'success' => false,
                'message' => 'DNI no encontrado en nuestra base de datos'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'dni' => $dni,
                'ruc' => $contribuyente->ruc,
                'nombres' => $contribuyente->razon_social,
                'estado' => $contribuyente->estado,
                'condicion' => $contribuyente->condicion,
                'ubigeo' => $contribuyente->ubigeo,
                'direccion' => $this->formatearDireccion($contribuyente),
                'departamento' => $contribuyente->departamento,
                'provincia' => $contribuyente->provincia,
                'distrito' => $contribuyente->distrito
            ]
        ]);
    }

    // Función auxiliar para formatear dirección (si ya la tienes, la reutilizas)
    private function formatearDireccion($data)
    {
        $partes = [];
        
        if (!empty($data->tipo_via) && !empty($data->nombre_via)) {
            $partes[] = $data->tipo_via . ' ' . $data->nombre_via;
        }
        
        if (!empty($data->numero)) {
            $partes[] = 'Nro ' . $data->numero;
        }
        
        if (!empty($data->interior)) {
            $partes[] = 'Int ' . $data->interior;
        }
        
        if (!empty($data->tipo_zona) && !empty($data->codigo_zona)) {
            $partes[] = $data->tipo_zona . ' ' . $data->codigo_zona;
        }
        
        if (!empty($data->lote)) {
            $partes[] = 'Lote ' . $data->lote;
        }

        return implode(' ', $partes);
    }

    public function consultar($ruc)
    {
        // 1. Validar que tenga 11 dígitos y empiece con prefijos válidos de Perú
        if (!preg_match('/^(10|15|16|17|20)\d{9}$/', $ruc)) {
            return response()->json([
                'success' => false,
                'message' => 'El número de RUC es inválido (debe tener 11 dígitos)'
            ], 400);
        }

        // 2. Consulta optimizada uniendo con cat_ubigeo
        $contribuyente = DB::table('contribuyentes as c')
            ->leftJoin('cat_ubigeo as u', 'c.ubigeo', '=', 'u.ubi_cod')
            ->where('c.ruc', $ruc)
            ->select(
                'c.ruc',
                'c.razon_social',
                'c.estado',
                'c.condicion',
                'c.ubigeo',
                'u.departamento',
                'u.provincia',
                'u.distrito',
                DB::raw("TRIM(CONCAT_WS(' ', 
                    NULLIF(c.tipo_via, '-'), 
                    NULLIF(c.nombre_via, '-'), 
                    IF(c.numero != '-' AND c.numero != '', CONCAT('NRO. ', c.numero), ''),
                    IF(c.interior != '-' AND c.interior != '', CONCAT('INT. ', c.interior), ''),
                    NULLIF(c.codigo_zona, '-'), 
                    NULLIF(c.tipo_zona, '-'),
                    IF(u.departamento IS NOT NULL, CONCAT(u.departamento, ' - ', u.provincia, ' - ', u.distrito), '')
                )) as direccion")
            )
            ->first();

        // 3. Respuesta si no existe
        if (!$contribuyente) {
            return response()->json([
                'success' => false,
                'message' => 'RUC no encontrado en la base de datos'
            ], 404);
        }

        // 4. Respuesta exitosa
        return response()->json([
            'success' => true,
            'data'    => $contribuyente
        ], 200);
    }
}