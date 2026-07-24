<?php

namespace MasterSoft\Console;

use Illuminate\Support\Facades\DB;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
         \MasterSoft\Console\Commands\DbDump::class,
         \MasterSoft\Console\Commands\CheckTruncateStatus::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // Tarea existente de SUNAT
        $schedule->command('sunat:check-send-comprobantes')->dailyAt('01:00');
        
        // Verificar el estado de truncar cada 3 días a las 11:00 AM (horario laboral)
        $schedule->command('system:check-truncate')
                 ->days([0, 3, 6])  // Día 0 (Domingo), día 3 (Miércoles), día 6 (Sábado)
                 ->at('11:00')      // 11:00 AM - Horario laboral
                 ->withoutOverlapping()
                 ->runInBackground();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }
    
}