<?php

namespace MasterSoft;

use Illuminate\Database\Eloquent\Model;

class cpe_nota_detalle extends Model
{
   protected $table = 'cpe_nota_detalle';

   protected $primaryKey = 'IdCpe_nota_detalle';

   public $timestamps = false;

   protected $fillable = [
   		'IdCpe_nota',
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
   ];

   protected $guarded = [

   ];
}
