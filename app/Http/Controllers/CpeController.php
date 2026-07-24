<?php

namespace MasterSoft\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CpeController extends Controller
{
    // Muestra el formulario
    public function index()
    {
        return view('empresas.consultascpe.consulta_cpe');
    }

    // Procesa la consulta hacia apiperu.dev
    public function consultar(Request $request)
    {
        $token = 'c7c656604942b0a6df5fa225835e99eb7376cf841d38781b91f651f72e03cc09'; // Reemplaza con tu token real
        $url = "https://apiperu.dev/api/cpe";

        $params = json_encode([
            "ruc_emisor"            => $request->ruc_emisor,
            "codigo_tipo_documento" => $request->tipo_documento,
            "serie_documento"       => $request->serie,
            "numero_documento"      => $request->numero,
            "fecha_de_emision"      => $request->fecha,
            "total"                 => $request->total
        ]);

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
            return back()->with('error', 'Error en la conexión: ' . $err);
        }

        $resultado = json_decode($response);

        return view('empresas.consultascpe.consulta_cpe', compact('resultado'));
    }

    public function consultarVenta($id)
    {
        $venta = DB::table('cpe_cabecera')->where('IdCpe_cabecera', $id)->first();

        if (!$venta) {
            return back()->with('info', 'No se encontró el comprobante.');
        }

        $token = trim('c7c656604942b0a6df5fa225835e99eb7376cf841d38781b91f651f72e03cc09');
        $url = "https://apiperu.dev/api/cpe";

        $params = json_encode([
            "ruc_emisor"            => $venta->IdEmpresa, // Tu RUC
            "codigo_tipo_documento" => $venta->tdocod,
            "serie_documento"       => $venta->serdoc,
            "numero_documento"      => $venta->numdoc,
            "fecha_de_emision"      => $venta->ccafem,
            "total"                 => number_format($venta->ccaitv, 2, '.', '')
        ]);

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
            return back()->with('info', 'Error de conexión con la API: ' . $err);
        }

        $resultado = json_decode($response);

        if ($resultado && $resultado->success) {
            $estado = strtoupper($resultado->data->comprobante_estado_descripcion);
            return back()->with('success', "Respuesta de SUNAT para {$venta->serdoc}-{$venta->numdoc}: " . $estado);
        } else {
            return back()->with('info', "Error API: " . ($resultado->message ?? 'Respuesta inválida'));
        }
    }

    public function consultaMultipleV2(Request $request)
    {
        $token = 'c7c656604942b0a6df5fa225835e99eb7376cf841d38781b91f651f72e03cc09'; // Reemplaza con tu token
        $url = "https://apiperu.dev/api/validacion-multiple-cpe-v2";

        // 1. Capturamos las fechas del formulario. Si no hay, por defecto usamos la fecha de hoy.
        $fecha_inicio = $request->input('fecha_inicio', date('Y-m-d'));
        $fecha_fin = $request->input('fecha_fin', date('Y-m-d'));

        // 2. Filtramos la base de datos por esas fechas
        $ventas = DB::table('cpe_cabecera')
                    ->whereBetween('ccafem', [$fecha_inicio, $fecha_fin])
                    ->orderBy('ccafem', 'desc')
                    ->limit(100) // Máximo recomendado por la API V2
                    ->get();

        $listaComprobantes = [];
        $resultado = null;

        // Si encontramos ventas en esas fechas, armamos el array
        if ($ventas->count() > 0) {
            foreach ($ventas as $v) {
                $listaComprobantes[] = "{$v->IdEmpresa}|{$v->tdocod}|{$v->serdoc}|{$v->numdoc}|{$v->ccafem}|" . number_format($v->ccaitv, 2, '.', '');
            }

            // Preparamos el envío a la API
            $params = json_encode([
                "ruc_empresa"   => "",
                "sol_usuario"   => "",
                "clave_usuario" => "",
                "comprobantes"  => $listaComprobantes
            ]);

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

            if (!$err) {
                $resultado = json_decode($response);
            }
        }

        // Retornamos las variables a la vista, incluyendo las fechas para que el filtro no se borre
        return view('empresas.consultascpe.consulta_multiple_v2', compact('resultado', 'fecha_inicio', 'fecha_fin', 'ventas'));
    }
}