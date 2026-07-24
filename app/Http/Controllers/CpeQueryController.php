<?php

namespace MasterSoft\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use PDF;

class CpeQueryController extends Controller
{
    // 1. Mostrar el formulario de consulta pública
    public function index()
    {
        $empresa = DB::table('empresa')->first();
        return view('empresas.cpe.index', compact('empresa'));
    }

    // 2. Procesar la búsqueda pública y consultar a ApiPeru
    public function search(Request $request)
    {
        $this->validate($request, [
            'tipo_documento' => 'required',
            'serie'          => 'required|string|max:4',
            'numero'         => 'required|integer',
            'fecha'          => 'required|date',
            'total'          => 'required|numeric',
        ]);

        $empresa = DB::table('empresa')->first();
        if (!$empresa) {
            return redirect()->back()->withErrors(['error' => 'Configuración de empresa no encontrada.'])->withInput();
        }

        $comprobante = DB::table('cpe_cabecera')
            ->where('tdocod', $request->input('tipo_documento'))
            ->where('serdoc', strtoupper($request->input('serie')))
            ->where('numdoc', $request->input('numero'))
            ->where('ccafem', $request->input('fecha'))
            ->where('ccaitv', number_format($request->input('total'), 2, '.', ''))
            ->first();

        if (!$comprobante) {
            return redirect()->back()->withErrors(['error' => 'No se encontró ningún comprobante con los datos ingresados.'])->withInput();
        }

        $detalles = DB::table('cpe_detalle')->where('IdCpe_cabecera', $comprobante->IdCpe_cabecera)->get();

        // Consulta a APIPERU
        $token = 'c7c656604942b0a6df5fa225835e99eb7376cf841d38781b91f651f72e03cc09';
        $url = "https://apiperu.dev/api/cpe";

        $params = json_encode([
            "ruc_emisor"            => $empresa->IdEmpresa,
            "codigo_tipo_documento" => $request->input('tipo_documento'),
            "serie_documento"       => strtoupper($request->input('serie')),
            "numero_documento"      => $request->input('numero'),
            "fecha_de_emision"      => $request->input('fecha'),
            "total"                 => number_format($request->input('total'), 2, '.', '')
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

        $sunatStatus = 'No verificado';
        if (!$err) {
            $resultado = json_decode($response);
            if ($resultado && isset($resultado->success) && $resultado->success) {
                $sunatStatus = isset($resultado->data->comprobante_estado_descripcion) ? $resultado->data->comprobante_estado_descripcion : 'Aceptado';
            } else {
                $sunatStatus = isset($resultado->message) ? $resultado->message : 'No encontrado en SUNAT';
            }
        } else {
            $sunatStatus = 'Error de conexión con el validador';
        }

        // CORRECCIÓN: Armamos el nombre SIN rellenar ceros para el XML, basándonos en los datos reales de la BD
        $fileNameBase = $empresa->IdEmpresa . '-' . $comprobante->tdocod . '-' . $comprobante->serdoc . '-' . $comprobante->numdoc;
        
        $xmlFile = "xml/" . $fileNameBase . ".xml";
        $cdrFile = "cdr/R-" . $fileNameBase . ".zip";

        // Verificamos si los archivos físicos existen en public/xml/ y public/cdr/
        $links = [
            'pdf' => route('cpe.download.pdf', $comprobante->IdCpe_cabecera),
            'xml' => File::exists(public_path($xmlFile)) ? asset($xmlFile) : null,
            'cdr' => File::exists(public_path($cdrFile)) ? asset($cdrFile) : null,
        ];

        return view('empresas.cpe.index', compact('empresa', 'comprobante', 'detalles', 'links', 'sunatStatus'));
    }

    // 3. Renderizar y descargar el PDF
    public function downloadPdf($id)
    {
        $cabpdf = DB::table('cpe_cabecera')->where('IdCpe_cabecera', $id)->first();
        if (!$cabpdf) {
            abort(404, 'Comprobante no encontrado');
        }

        $detpdf = DB::table('cpe_detalle')->where('IdCpe_cabecera', $id)->get(); 
        $empresa = DB::table('empresa')->first();

        $sucursal = DB::table('empresa_negocios')
            ->where('id_empresa_negocio', $cabpdf->id_empresa_negocio)
            ->first();

        if (!$sucursal) {
            $sucursal = DB::table('empresa_negocios')->first();
        }

        $tipoDocDescripcion = 'COMPROBANTE ELECTRÓNICO';
        if ($cabpdf->tdocod == '01') {
            $tipoDocDescripcion = 'FACTURA ELECTRÓNICA';
        } elseif ($cabpdf->tdocod == '03') {
            $tipoDocDescripcion = 'BOLETA DE VENTA ELECTRÓNICA';
        }

        $totalletras = $this->convertirNumeroALetras($cabpdf->ccaitv);

        $numeroFormateado = str_pad($cabpdf->numdoc, 8, "0", STR_PAD_LEFT);
        $textoQr = $empresa->IdEmpresa . '|' . $cabpdf->tdocod . '|' . $cabpdf->serdoc . '|' . $numeroFormateado . '|' . $cabpdf->ccaigv . '|' . $cabpdf->ccaitv . '|' . $cabpdf->ccafem . '|';

        $logoFinal = null;
        if ($sucursal && !empty($sucursal->logosuc)) {
            $logoFinal = str_replace('public/', '', $sucursal->logosuc);
        }

        $pdf = \PDF::loadView('empresas.cpe.A4', compact(
            'cabpdf', 
            'detpdf', 
            'empresa', 
            'sucursal', 
            'tipoDocDescripcion',
            'totalletras', 
            'textoQr',
            'logoFinal'
        ));
        
        $name = "R-" . $empresa->IdEmpresa . "-" . $cabpdf->tdocod . "-" . $cabpdf->serdoc . "-" . $numeroFormateado . ".pdf";
        
        return $pdf->download($name);
    }

    private function convertirNumeroALetras($numero) {
        $arrNumero = explode('.', number_format($numero, 2, '.', ''));
        $entero = (int)$arrNumero[0];
        $centimos = $arrNumero[1];

        $unidades = ['', 'UN', 'DOS', 'TRES', 'CUATRO', 'CINCO', 'SEIS', 'SIETE', 'OCHO', 'NUEVE', 'DIEZ', 'ONCE', 'DOCE', 'TRECE', 'CATORCE', 'QUINCE'];
        $decenas = ['', '', '', 'TREINTA', 'CUARENTA', 'CINCUENTA', 'SESENTA', 'SETENTA', 'OCHENTA', 'NOVENTA'];
        $centenas = ['', 'CIENTO', 'DOSCIENTOS', 'TRESCIENTOS', 'CUATROCIENTOS', 'QUINIENTOS', 'SEISCIENTOS', 'SETECIENTOS', 'OCHOCIENTOS', 'NOVECIENTOS'];

        if ($entero === 0) return "CERO CON " . $centimos . "/100 SOLES";
        if ($entero === 100) return "CIEN CON " . $centimos . "/100 SOLES";

        $letras = '';

        if ($entero >= 100) {
            $letras .= $centenas[floor($entero / 100)] . ' ';
            $entero %= 100;
        }

        if ($entero > 0) {
            if ($entero < 16) {
                $letras .= $unidades[$entero] . ' ';
            } else {
                $dec = floor($entero / 10);
                $uni = $entero % 10;
                if ($dec == 1) $letras .= "DIECI" . $unidades[$uni] . ' ';
                elseif ($dec == 2) $letras .= ($uni == 0) ? "VEINTE " : "VEINTI" . $unidades[$uni] . ' ';
                else {
                    $letras .= $decenas[$dec];
                    if ($uni > 0) $letras .= " Y " . $unidades[$uni];
                    $letras .= ' ';
                }
            }
        }

        return trim($letras) . " CON " . $centimos . "/100 SOLES";
    }
}