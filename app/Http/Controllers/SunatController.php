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