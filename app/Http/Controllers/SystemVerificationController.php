<?php

namespace MasterSoft\Http\Controllers;

use Illuminate\Http\Request;
use MasterSoft\Http\Middleware\CheckSystemTruncate;

class SystemVerificationController extends Controller
{
    /**
     * Verificar el estado del sistema desde la vista de bloqueo
     */
    public function verify(Request $request)
    {
        try {
            // Llamar al método de verificación del middleware
            $result = CheckSystemTruncate::checkTruncateStatusNow();
            
            return response()->json([
                'success' => true,
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al verificar el estado del sistema: ' . $e->getMessage()
            ], 500);
        }
    }
}