<?php

namespace MasterSoft\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CheckSystemTruncate
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
{
    // Permitir la ruta de verificación sin bloquear
    if ($request->is('verificar-sistema')) {
        return $next($request);
    }

    // Verificar el estado actual del cache
    $truncated = \Cache::get('system_truncated', false);
//    \Log::info('CheckSystemTruncate middleware - Valor de system_truncated: ' . ($truncated ? 'true' : 'false'));
    
    if ($truncated) {
        // Cerrar sesión del usuario si está autenticado
        if (\Auth::check()) {
            \Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }
        
        // Mostrar vista de sistema bloqueado con botón de verificación
        return response()->view('errors.system-blocked', [
            'message' => 'El sistema se encuentra temporalmente deshabilitado.',
            'contact' => 'Verifique su estado de pago con soporte técnico.'
        ], 503);
    }

    return $next($request);
}
    
    /**
     * Verificar estado de truncar directamente desde el VPS
     * Este método será llamado desde el controlador o la vista
     */
    public static function checkTruncateStatusNow()
{
    try {
        $rucCliente = env('RUC_CLIENTE');
        if (empty($rucCliente)) {
            \Log::error('RUC_CLIENTE no configurado en .env');
            return [
                'success' => false,
                'blocked' => true,
                'message' => 'Configuración incorrecta. Comuníquese con soporte.'
            ];
        }

        // Conectar a la base de datos remota
        $cliente = \DB::connection('remote_license_db')
            ->table('clientes')
            ->select('truncar', 'estado', 'razon_social')
            ->where('ruc', $rucCliente)
            ->first();

        if (!$cliente) {
            \Log::error("Cliente con RUC {$rucCliente} no encontrado");
            return [
                'success' => false,
                'blocked' => true,
                'message' => 'Cliente no encontrado en el servidor de licencias.'
            ];
        }

        // Actualizar el cache según el estado
        if ($cliente->truncar == 1) {
            \Cache::put('system_truncated', true, now()->addDays(7));
            return [
                'success' => true,
                'blocked' => true,
                'message' => 'El sistema sigue bloqueado por falta de pago. Si ya realizó el pago, espere unos minutos y vuelva a verificar.'
            ];
        } else {
            // >>>> DESBLOQUEAR <<<<
            \Cache::put('system_truncated', false, now()->addDays(7));
            return [
                'success' => true,
                'blocked' => false,
                'message' => '¡El sistema ha sido desbloqueado! Puede continuar usando el sistema normalmente.'
            ];
        }

    } catch (\Exception $e) {
        \Log::error('Error al verificar truncar: ' . $e->getMessage());
        return [
            'success' => false,
            'blocked' => true,
            'message' => 'Error de conexión con el servidor de licencias. Intente nuevamente.'
        ];
    }
}
}