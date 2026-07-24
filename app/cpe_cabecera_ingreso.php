<?php

namespace MasterSoft;

use Illuminate\Database\Eloquent\Model;

class cpe_cabecera_ingreso extends Model
{
   protected $table = 'cpe_cabecera_ingreso';

   protected $primaryKey = 'IdCpe_cabecera_ingreso';

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
        'IdUsuario',
        'codhash',
        'ccaestbu',
        'ccaestse',
        'respse',
        'codunique',
        'detraccion',
        'mesa_id',
        'id_empresa_negocio'
   ];

   protected $guarded = [

   ];
}
