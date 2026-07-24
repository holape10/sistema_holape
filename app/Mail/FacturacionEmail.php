<?php

namespace MasterSoft\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\Queue\ShouldQueue;

class FacturacionEmail extends Mailable
{
     use Queueable, SerializesModels;
     
    /**
     * The demo object instance.
     *
     * @var Demo
     */
    public $demo;

     public $destino4;
     public $destino5;
   // private $ruta_comp;
 
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($demo,$destino4,$destino5,$correo)
    {
       
        //$rucemp = trim(Auth::user()->IdEmpresa);
        $this->demo = $demo;
        $this->destino4 = $destino4;
        $this->destino5 = $destino5;
        $this->correo = $correo;

        //$this->ruta_comp = $rutpdfile ='/opt/data/comprobantes/'.$rucemp.'/pdf/';
    }
 
    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {

       $email = $this->from($this->correo)
                    ->view('mails.facturacion');
         

                if(!empty($this->destino4)){
                  $email->attach($this->destino4);  
                }

                  if(!empty($this->destino5)){
                  $email->attach($this->destino5);  
                }
              

        return $email;
              
    }
}
