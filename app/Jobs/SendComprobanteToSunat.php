<?php

namespace MasterSoft\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use MasterSoft\cpe_cabecera;
use MasterSoft\Http\Controllers\ComprobantesController;
use DB;
use Illuminate\Support\Facades\Log;

class SendComprobanteToSunat implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $comprobanteId;

    public function __construct(int $comprobanteId)
    {
        $this->comprobanteId = $comprobanteId;
    }

    public function handle()
    {
        try {
            $comprobante = cpe_cabecera::find($this->comprobanteId);

            if (!$comprobante) {
                Log::warning("Job: Comprobante ID {$this->comprobanteId} no encontrado para envío a SUNAT.");
                return;
            }

            $controller = new ComprobantesController();

            // Asegúrate de que enviarComprobante en ComprobantesController maneje la lógica de Greenter
            // y devuelva un objeto con isSuccess() y getError() para el manejo de errores.
            // Si enviarComprobante realiza redirecciones, necesitarás que performSunatSend (como se explicó)
            // contenga la lógica Greenter y lo invoques aquí.
            $result = $controller->enviarComprobante($comprobante->IdCpe_cabecera, $comprobante->tdocod); // Asumo que el segundo parámetro es el tipo de documento.

            if ($result && $result->isSuccess()) {
                Log::info("Job: Comprobante ID {$this->comprobanteId} enviado a SUNAT exitosamente.");
            } else {
                $error = $result ? $result->getError() : new \Exception("Error desconocido o resultado nulo del envío Greenter.");
                Log::error("Job: Error al enviar comprobante ID {$this->comprobanteId} a SUNAT. Code: " . ($error instanceof \Exception ? $error->getCode() : 'N/A') . ", Message: " . $error->getMessage());
            }

        } catch (\Exception $e) {
            Log::error("Job: Fallo inesperado en SendComprobanteToSunat para ID {$this->comprobanteId}: " . $e->getMessage() . " en " . $e->getFile() . " linea " . $e->getLine());
        }
    }
}