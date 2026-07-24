<?php

namespace MasterSoft\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CheckTruncateStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'system:check-truncate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verifica el estado de truncar desde el servidor remoto';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try {
            // Obtener RUC del sistema actual desde .env
            $rucCliente = env('RUC_CLIENTE');
            
            if (empty($rucCliente)) {
                $this->error('ERROR: RUC_CLIENTE no configurado en .env');
                Log::error('RUC_CLIENTE no configurado en .env');
                return;
            }
            
            $this->info("Verificando estado para RUC: {$rucCliente}");
            
            // Conectar a la base de datos remota y obtener el estado de truncar
            // NOTA: Tu tabla usa 'razon_social' en lugar de 'nombre_o_razon_social'
            $cliente = DB::connection('remote_license_db')
                ->table('clientes')
                ->select('truncar', 'estado', 'razon_social')
                ->where('ruc', $rucCliente)
                ->first();
            
            if (!$cliente) {
                $this->error('ERROR: Cliente no encontrado en la base de datos remota');
                Log::error("Cliente con RUC {$rucCliente} no encontrado en base remota");
                
                // Si no existe el cliente, mantener el último estado conocido
                return;
            }
            
            $this->info("Cliente encontrado: {$cliente->razon_social}");
            $this->info("Estado: {$cliente->estado}");
            $this->info("Truncar: {$cliente->truncar}");
            
            // Verificar el estado de truncar
            if ($cliente->truncar == 1) {
                Cache::put('system_truncated', true, now()->addDays(7));
                $this->error('⛔ SISTEMA BLOQUEADO - El campo truncar está en 1');
                Log::warning('Sistema bloqueado remotamente', [
                    'ruc' => $rucCliente,
                    'cliente' => $cliente->razon_social,
                    'truncar' => $cliente->truncar
                ]);
            } else {
                Cache::put('system_truncated', false, now()->addDays(7));
                $this->info('✅ SISTEMA ACTIVO - Funcionamiento normal');
                Log::info('Sistema verificado - Estado normal', [
                    'ruc' => $rucCliente,
                    'cliente' => $cliente->razon_social
                ]);
            }
            
            // Guardar timestamp de última verificación
            Cache::put('last_truncate_check', now()->format('Y-m-d H:i:s'), now()->addDays(7));
            $this->info("Última verificación: " . now()->format('Y-m-d H:i:s'));
            
        } catch (\Exception $e) {
            $this->error('ERROR en la verificación: ' . $e->getMessage());
            Log::error('Error al verificar estado truncar', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // En caso de error de conexión, mantener el último estado conocido
            // No cambiar el cache para evitar bloqueos o desbloqueos accidentales
            $this->warn('Se mantiene el último estado conocido debido al error');
        }
    }
}