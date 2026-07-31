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

            $apiKey = config('services.gemini.api_key');
            if (!$apiKey) {
                return response()->json([
                    'success' => false,
                    'message' => 'GEMINI_API_KEY no configurada en .env'
                ], 500);
            }

            $prompt = "Eres un experto en extracción de datos de facturas. Extrae SOLO estos campos en JSON estricto: ruc_emisor, razon_social, tipo_documento, serie_numero, fecha_emision, monto_op_gravada, monto_igv, monto_total, items (array con: descripcion, cantidad, precio_unitario, importe). Si no encuentras un campo, déjalo vacío.";

            $client = new Client();
            
            // ✅ Gemini 2.0 Flash - Gratis y rápido
            $endpoint = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent";

            $response = $client->post($endpoint, [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'x-goog-api-key' => $apiKey,
                ],
                'json' => [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt],
                                [
                                    'inline_data' => [
                                        'mime_type' => $mimeType,
                                        'data' => $base64Data
                                    ]
                                ]
                            ]
                        ]
                    ],
                    'generationConfig' => [
                        'response_mime_type' => 'application/json'
                    ]
                ],
                'timeout' => 45
            ]);

            $body = json_decode($response->getBody()->getContents(), true);
            $rawText = $body['candidates'][0]['content']['parts'][0]['text'] ?? '{}';
            $extractedData = json_decode($rawText, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('JSON inválido: ' . json_last_error_msg());
            }

            return response()->json([
                'success' => true,
                'data' => $extractedData
            ]);

        } catch (GuzzleException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error API Gemini: ' . $e->getMessage()
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
}