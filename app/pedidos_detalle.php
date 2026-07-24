<?php

namespace MasterSoft;

use Illuminate\Database\Eloquent\Model;

class pedidos_detalle extends Model
{
   protected $table = 'pedidos_detalle';

   protected $primaryKey = 'ped_det_id';

  // protected  $keyType = 'string';

   public $timestamps = false;

   protected $fillable = [
   		'ped_id',
   		'IdProducto',
   		'propounitem',
   		'provunitem',
      'igvitem',
      'subtotalitem',
      'totalitem',
      'cantidad',
      'unidad',
      'IdEmpresa',
      'detalle'

   ];

   protected $guarded = [

   ];
}
