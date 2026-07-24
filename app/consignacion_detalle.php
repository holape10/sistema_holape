<?php

namespace MasterSoft;

use Illuminate\Database\Eloquent\Model;

class consignacion_detalle extends Model
{
   protected $table = 'consignacion_detalle';

   protected $primaryKey = 'id_consignacion_detalle';

   public $timestamps = false;

   protected $fillable = [
   		'id_consignacion_cabecera',
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
        'costo'
   ];

   protected $guarded = [

   ];
}
