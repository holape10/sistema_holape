<?php

namespace MasterSoft;

use Illuminate\Database\Eloquent\Model;

class cpe_detalle_ingreso extends Model
{
   protected $table = 'cpe_detalle_ingreso';

   protected $primaryKey = 'IdCpe_detalle_ingreso';

   public $timestamps = false;

   protected $fillable = [
   		'IdCpe_cabecera_ingreso',
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
