<?php

namespace MasterSoft\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class ComprobanteMail extends Mailable
{
    use Queueable, SerializesModels;

    public $comprobante;
    public $cliente;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($comprobante, $cliente)
    {
        $this->comprobante = $comprobante;
        $this->cliente = $cliente;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Tu Comprobante de Pago - DEVSOFT by HolaPe')
                    ->view('emails.comprobante') // Crea esta vista en resources/views/emails/comprobante.blade.php
                    ->attachFromStorage($this->comprobante->ruta_pdf, [
                        'as' => 'Comprobante_' . $this->comprobante->serie . '-' . $this->comprobante->numero . '.pdf',
                        'mime' => 'application/pdf',
                    ]);
    }
}
