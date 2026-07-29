<?php

namespace MasterSoft\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class OcrController extends Controller
{
    public function index()
    {
        return view('empresas.ocr.index');
    }

    public function process(Request $request)
    {
        $request->validate([
            'document' => 'required|file|mimes:jpeg,png,jpg,pdf|max:10240',
        ]);

        try {
            $file = $request->file('document');
            $mimeType = $file->getClientMimeType();
            $base64Data = base64_encode(file_get_contents($file->getRealPath()));

            $apiKey = env('GEMINI_API_KEY');
            if (!$apiKey) {
                return response()->json([
                    'success' => false,
                    'message' => 'GEMINI_API_KEY no configurada en el .env'
                ], 500);
            }

            $prompt = "Extrae los datos principales de este comprobante o factura en formato JSON estricto. "
                . "No incluyas formateo markdown ni triple comillas. "
                . "Campos requeridos: ruc_emisor, razon_social, tipo_documento, serie_numero, fecha_emision, "
                . "monto_op_gravada, monto_igv, monto_total, items (un array de objetos con: descripcion, cantidad, precio_unitario, importe).";

            $client = new Client();
            
            // ENDPOINT ESTABLE (v1)
            // Cambia gemini-2.0-flash por gemini-1.5-flash-8b
            $endpoint = "https://generativelanguage.googleapis.com/v1/models/gemini-1.5-flash-8b:generateContent?key={$apiKey}";

            $response = $client->post($endpoint, [
                'headers' => [
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt],
                                [
                                    'inline_data' => [
                                        'mime_type' => $mimeType,
                                        'data'      => $base64Data
                                    ]
                                ]
                            ]
                        ]
                    ],
                    'generationConfig' => [
                        'response_mime_type' => 'application/json'
                    ]
                ],
                'timeout' => 30
            ]);

            $body = json_decode($response->getBody()->getContents(), true);
            $rawText = $body['candidates'][0]['content']['parts'][0]['text'] ?? '{}';
            
            $extractedData = json_decode($rawText, true);

            return response()->json([
                'success' => true,
                'data'    => $extractedData
            ]);

        } catch (GuzzleException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al comunicarse con la API de IA: ' . $e->getMessage()
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de procesamiento: ' . $e->getMessage()
            ], 500);
        }
    }
}