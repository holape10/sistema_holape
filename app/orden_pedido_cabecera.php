<?php

namespace MasterSoft;

use Illuminate\Database\Eloquent\Model;

class orden_pedido_cabecera extends Model
{
    protected $table = 'orden_pedido_cabecera';

   protected $primaryKey = 'IdOP';

  // protected  $keyType = 'string';
   
   public $timestamps = false;

   protected $fillable = [
   		'orden_pedido',
   		'estado',
      'id_empresa_negocio'
   ];

   protected $guarded = [

   ];
}
