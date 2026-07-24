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

class SireVentas extends Model
{
   protected $table = 'sire_ventas';

   protected $primaryKey = 'Id';

   public $timestamps = false;

   protected $fillable = [
        
        'ruc',
        'razon_social',
        'periodo',
        'car_sunat',
        'fecha_emision',
        'fecha_vencimiento',
        'tipo_doc',
        'serie',
        'numero_inicial',
        'numero_final',
        'tipo_doc_identidad',
        'nro_doc_identidad',
        'cliente',
        'valor_facturacion',
        'valor_gravada',
        'descuento_BI',
        'igv_ipm',
        'dscto_igv_ipm',
        'mto_exonerado',
        'mto_inafecto',
        'isc',
        'bi_grav_ivap',
        'ivap',
        'icbper',
        'otros_tributos',
        'total_cp',
        'moneda',
        'tipo_cambio',
        'fecha_emision_doc_mod',
        'tipo_cp_mod',
        'serie_cp_mod',
        'nro_cp_mod',
        'id_proy_ope_atrib',
        'tipo_nota',
        'est_comp',
        'valor_fob_emb',
        'valor_op_grat',
        'tipo_operacion',
        'dam_cp',
        'clu'
        

   ];

   protected $guarded = [

   ];




}
