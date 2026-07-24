<?php

namespace MasterSoft;

use Illuminate\Database\Eloquent\Model;

class cpe_detalle_gasto extends Model
{
   protected $table = 'cpe_detalle_gasto';

   protected $primaryKey = 'IdCpe_detalle_gasto';

   public $timestamps = false;

   protected $fillable = [
   		'IdCpe_cabecera_gasto',
   		'umecod',
        'cdecan',
        'procod',
   		'cdepsu',
        'cdedes',
        'cdevun',
        'cdedec',
        'cdeigv',
        'tigcod',
        'cdeisc',
        'cdetis',
        'cdepve',
        'cdevve',
        'descuento'
   ];

   protected $guarded = [

   ];
}
