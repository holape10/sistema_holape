<?php
namespace MasterSoft\Http\Controllers;
 
use MasterSoft\Http\Controllers\Controller;
use MasterSoft\Mail\FacturacionEmail;
use Illuminate\Support\Facades\Mail;
 
class MailController extends Controller
{
    public function send()
    {
        $objDemo = new \stdClass();
        $objDemo->demo_one = 'Demo One Value';
        $objDemo->demo_two = 'Demo Two Value';
        $objDemo->sender = 'huancayoimportaciones@gmail.com';
        $objDemo->receiver = 'jbarreto.sistemas@gmail.com';
 
        Mail::to("huancayoimportaciones@gmail.com")->send(new FacturacionEmail($objDemo));
    }
}