<?php

namespace MasterSoft\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TruncateColaImpresion extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'system:truncate-cola-impresion';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Trunca la tabla cola_impresion diariamente a las 5 AM';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try {
            $totalAntes = DB::table('cola_impresion')->count();

            DB::table('cola_impresion')->truncate();

            $this->info("cola_impresion truncada correctamente. Registros eliminados: {$totalAntes}");
            Log::info('cola_impresion truncada por scheduler', [
                'registros_eliminados' => $totalAntes,
                'fecha' => now()->format('Y-m-d H:i:s'),
            ]);
        } catch (\Exception $e) {
            $this->error('ERROR al truncar cola_impresion: ' . $e->getMessage());
            Log::error('Error al truncar cola_impresion', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}