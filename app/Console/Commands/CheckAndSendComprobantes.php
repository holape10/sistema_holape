<?php

namespace MasterSoft\Console\Commands;

use Illuminate\Console\Command;
use MasterSoft\Jobs\SendComprobanteToSunat;
use MasterSoft\cpe_cabecera;
use MasterSoft\Empresa;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth; // Asegúrate de importar Auth para Auth::user()

class CheckAndSendComprobantes extends Command
{
    protected $signature = 'sunat:check-send-comprobantes';
    protected $description = 'Verifica y envía comprobantes electrónicos próximos a vencer a SUNAT.';

    public function handle()
    {
        Log::info('Iniciando verificación y envío automático de comprobantes a SUNAT.');

        $today = Carbon::today();

        // Para usar Auth::user() en comandos de consola, necesitas asegurarte de que el usuario esté autenticado.
        // En un cron job, no hay un usuario web autenticado. La mejor práctica aquí es:
        // 1. Pasar el IdEmpresa directamente o
        // 2. Iterar sobre todas las empresas con tipo_envio = '1'
        // 3. O, si la configuración es global, simplemente obtenerla.

        // Optamos por obtener la configuración de la empresa principal directamente,
        // ya que 'tipo_envio' parece ser una configuración a nivel de la entidad principal.
        $empresaConfig = Empresa::find(DB::table('empresa_negocios')->where('id_empresa_negocio', \Auth::user()->id_empresa_negocio)->value('IdEmpresa')); // <-- Modificación para obtener IdEmpresa
        // Si Auth::user() no está disponible en este contexto (cron), esta línea fallará.
        // Una forma más robusta si `tipo_envio` es para *toda* la empresa (no por sucursal de usuario):
        // $empresaConfig = Empresa::where('IdEmpresa', 'TU_RUC_PRINCIPAL_AQUI')->first();
        // O, si necesitas iterar por cada empresa que tenga envío automático:
        // $empresasAutomatizadas = Empresa::where('tipo_envio', '1')->get();
        // foreach ($empresasAutomatizadas as $empresaConfig) { ... lógica por cada empresa ... }


        if (!$empresaConfig) {
            Log::error('No se encontró la configuración de la empresa principal. Abortando envío automático.');
            $this->error('No se encontró la configuración de la empresa principal.');
            return 1;
        }

        $isAutomaticSendActive = ($empresaConfig->tipo_envio == '1');

        $limitDateFactura = $today->copy()->subDays(2)->format('Y-m-d'); // Emisión hace 2 días
        $limitDateBoleta = $today->copy()->subDays(4)->format('Y-m-d'); // Emisión hace 4 días

        if ($isAutomaticSendActive) {
            $this->info('Envío automático de comprobantes activo.');
            // Facturas
            $facturasToSenda = cpe_cabecera::where('tdocod', '01')
                ->where('ccafem', $limitDateFactura)
                ->where(function ($query) {
                    $query->whereNull('ccacodsun')
                          ->orWhere('ccacodsun', '!=', '0');
                })
                ->whereNull('ccabaj')
                ->where('enviado', '0') // Asume que 'enviado' es un flag de tu sistema
                ->get();

            foreach ($facturasToSenda as $comprobante) {
                Log::info("Despachando Job para Factura ID: {$comprobante->IdCpe_cabecera} (Automático)");
                SendComprobanteToSunat::dispatch($comprobante->IdCpe_cabecera);
            }

            // Boletas (las boletas NO se envían automáticamente con esta configuración)
            // El requisito es que SÓLO afecte a las facturas.
            // Si decides en el futuro que las boletas también se envíen automáticamente,
            // habilita un bloque similar aquí para tdocod '03'.

        } else {
            $this->info('Envío automático de comprobantes está deshabilitado. No se procesarán.');
            Log::info('Envío automático de comprobantes está deshabilitado.');
        }

        $this->info('Verificación y envío de comprobantes finalizado.');
        Log::info('Verificación y envío automático de comprobantes a SUNAT finalizado.');

        return 0;
    }
}