<?php

namespace MasterSoft;

use Illuminate\Database\Eloquent\Model;

class orden_pedido_detalle extends Model
{
    protected $table = 'orden_pedido_detalle';

   protected $primaryKey = 'IdOPD';

  // protected  $keyType = 'string';
   
   public $timestamps = false;

   protected $fillable = [
   		'IdOP',
   		'codigo',
   		'descripcion',
   		'cantidad'
   ];

   protected $guarded = [

   ];
}
