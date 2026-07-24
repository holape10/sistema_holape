<?php

namespace MasterSoft;

use Illuminate\Database\Eloquent\Model;

class guias_remision_detalle extends Model
{
   protected $table = 'guias_remision_detalle';

   protected $primaryKey = 'IdCpe_guia_detalle';

   public $timestamps = false;

   protected $fillable = [
   		  'IdProducto',
        'procod',
        'pronom',
        'cantidad',
        'peso',
        'umecod',
        'IdCpe_guia'
   ];

   protected $guarded = [

   ];
}
