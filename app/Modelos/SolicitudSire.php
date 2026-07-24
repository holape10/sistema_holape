<?php

namespace MasterSoft\Modelos;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use DB;
use DOMDocument;
use MasterSoft\cpe_cabecera;
use MasterSoft\cpe_detalle;
use MasterSoft\EmpresaNegocios;
use MasterSoft\Cliente;
use Config;
use MasterSoft\Mail\FacturacionEmail;
use Illuminate\Support\Facades\Mail;
use MasterSoft\Modelos\contrato_cabecera;
use MasterSoft\Modelos\contrato_equipo;
use MasterSoft\Modelos\contrato_detalle;
use MasterSoft\Modelos\contrato_cuota;

class SolicitudSire extends Model
{
   protected $table = 'solicitud_sire';

   protected $primaryKey = 'solsire_id';

   public $timestamps = false;

   protected $fillable = [
        

   ];

   protected $guarded = [

   ];




}
