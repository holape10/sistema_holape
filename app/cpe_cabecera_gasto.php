<?php

namespace MasterSoft;

use Illuminate\Database\Eloquent\Model;

class cpe_cabecera_gasto extends Model
{
   protected $table = 'cpe_cabecera_gasto';

   protected $primaryKey = 'IdCpe_cabecera_gasto';

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
        'mot_elim',
        'estado',
        'tipo',
        'id_empresa_negocio'
   ];

   protected $guarded = [

   ];
}
