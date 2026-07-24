<?php

namespace MasterSoft;

use Illuminate\Database\Eloquent\Model;

class cpe_baja extends Model
{
    
   protected $table = 'cpe_baja';

   protected $primaryKey = 'IdCpe_baja';

   public $timestamps = false;

   protected  $keyType = 'string';
   
   protected $fillable = [
      'cbacor',
   	'cbacod',
		'cbamot',
		'cbdfco',
		'cbafec',
		'cbanum',
		'tdocod',
      'cbaestse',
      'respse',
      'cbaestbu',
      'codhash',
      'TipoBaja',
      'external_id',
      'id_empresa_negocio'

   ];

   protected $guarded = [

   ];
}
