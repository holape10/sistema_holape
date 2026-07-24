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

class SireCompras extends Model
{
   protected $table = 'sire_compras';

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
        'ano',
        'numero_inicial',
        'numero_final',
        'tipo_doc_identidad',
        'nro_doc_identidad',
        'cliente',
        'bi_grav_dg',
        'igv_ipm_dg',
        'bi_grav_dgng',
        'igv_ipm_dgng',
        'bi_grav_dng',
        'igv_ipm_dng',
        'valor_adq_ng',
        'isc',
        'icbper',
        'otros_tributos',
        'total_cp',
        'moneda',
        'tipo_cambio',
        'fecha_emision_doc_mod',
        'tipo_cp_mod',
        'serie_cp_mod',
        'com_dam_dsi',
        'nro_cp_mod',
        'clas_bss_sss',
        'id_proy_ope_atrib',
        'porc_part',
        'imb',
        'car_orig',
        'detraccion',
        'tipo_nota',
        'est_comp',
        'incal',
        'clu1',
        'clu2',
        'clu3',
        'clu4',
        'clu5',
        'clu6',
        'clu7',
        'clu8',
        'clu9',
        'clu10',
        'clu11',
        'clu12',
        'clu13',
        'clu14',
        'clu15',
        'clu16',
        'clu17',
        'clu18',
        'clu19',
        'clu20',
        'clu21',
        'clu22',
        'clu23',
        'clu24',
        'clu25',
        'clu26',
        'clu27',
        'clu28',
        'clu29',
        'clu30',
        'clu31',
        'clu32',
        'clu33',
        'clu34',
        'clu35',
        'clu36',
        'clu37',
        'clu38',
        'clu39'
        
   ];

   protected $guarded = [

   ];




}
