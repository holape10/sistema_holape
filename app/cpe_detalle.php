<?php

namespace MasterSoft;

use Illuminate\Database\Eloquent\Model;

class cpe_detalle extends Model
{
   protected $table = 'cpe_detalle';

   protected $primaryKey = 'IdCpe_detalle';

   public $timestamps = false;

   protected $fillable = [
   		'IdCpe_cabecera',
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
        'fecha_venta',
        'costo',
        'numero_recibo',
   ];

   protected $guarded = [

   ];
}
