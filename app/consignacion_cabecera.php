<?php

namespace MasterSoft;

use Illuminate\Database\Eloquent\Model;

class consignacion_cabecera extends Model
{
   protected $table = 'consignacion_cabecera';

   protected $primaryKey = 'id_consignacion_cabecera';

   public $timestamps = false;

   protected $fillable = [
   		'tdocod',
   		'topcod',
        'ccafem',
        'ccafve',
   		'ccacde',
        'tdicod',
        'ccandi',
        'ccanom',
        'moncod',
        'ccades',
        'ccacar',
        'ccatde',
        'ccatvg',
        'ccatvi',
        'ccatve',
        'ccaigv',
        'ccaisc',
        'ccaotr',
        'ccaitv',
        'direccion',
        'IdUsuario',
        'codhash',
        'ccaestbu',
        'ccaestse',
        'respse',
        'codunique',
        'detraccion',
        'mesa_id',
        'ped_id',
        'fecha_hora',
        'direccion',
        'external_id',
        'tipo_venta',
        'id_empresa_negocio'
   ];

   protected $guarded = [

   ];
}
