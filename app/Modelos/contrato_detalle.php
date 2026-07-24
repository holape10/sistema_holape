<?php

namespace MasterSoft\Modelos;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use DB;
use Greenter\Model\Client\Client;
use Greenter\Model\Company\Company;
use Greenter\Model\Company\Address;
use Greenter\Model\Sale\Invoice;
use Greenter\Model\Sale\SaleDetail;
use Greenter\Model\Sale\Cuota;
use Greenter\XMLSecLibs\Sunat\SignedXML;
use Greenter\Xml\Builder\InvoiceBuilder;
use Greenter\Xml\Builder\VoidedBuilder;
use Greenter\Xml\Builder\NoteBuilder;
use Greenter\Xml\Builder\SummaryBuilder;
use Greenter\Model\Sale\Legend;
use Greenter\Ws\Services\SunatEndpoints;
use Greenter\Model\Sale\Note;
use Greenter\Model\Voided\Voided;
use Greenter\Model\Voided\VoidedDetail;
use Greenter\Model\Summary\Summary;
use Greenter\Model\Summary\SummaryDetail;
use Greenter\Model\Summary\SummaryPerception;
use Greenter\Model\Sale\Document;
use Greenter\Ws\Services\SoapClient;
use Greenter\Ws\Services\SummarySender;
use Greenter\Ws\Services\BillSender;
use Greenter\Ws\Services\ExtService;
use Greenter\Model\Sale\FormaPagos\FormaPagoContado;
use Greenter\Model\Sale\FormaPagos\FormaPagoCredito;
use Greenter\Ws\Services\ConsultCdrService;
use Greenter\Model\Despatch\Despatch;
use Greenter\Model\Despatch\DespatchDetail;
use Greenter\Model\Despatch\Direction;
use Greenter\Model\Despatch\Shipment;
use Greenter\Model\Despatch\Transportist;
use Greenter\Xml\Builder\DespatchBuilder;
use Greenter\Model\Sale\Charge;
use Greenter\See;
use DOMDocument;
use MasterSoft\cpe_cabecera;
use MasterSoft\cpe_detalle;
use MasterSoft\Cliente;
use Config;
use MasterSoft\Mail\FacturacionEmail;
use Illuminate\Support\Facades\Mail;
use MasterSoft\Modelos\contrato_cabecera;
use MasterSoft\Modelos\contrato_equipo;
use MasterSoft\Modelos\contrato_cuota;

class contrato_detalle extends Model
{
    
   protected $table = 'contrato_detalle';

   protected $primaryKey = 'cont_det_id';

   public $timestamps = false;

   protected $fillable = [
        
   ];

   protected $guarded = [

   ];


 




}
