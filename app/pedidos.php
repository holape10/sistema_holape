<?php

namespace MasterSoft;

use Illuminate\Database\Eloquent\Model;

class pedidos extends Model
{
    protected $table = 'pedidos';

   protected $primaryKey = 'ped_id';

  // protected  $keyType = 'string';

   public $timestamps = false;

   protected $fillable = [
   	'mes_id',
      'cliente',
      'fecha',
      'fecha_hora',
   	'IdEmpresa',
      'total',
      'subtotal',
      'igv',
      'MotElim',
      'IdUsuario',
      'IdUsuarioMod',
      'IdUsuarioDel',
      'IdUsuarioCob',
      'direccion',
      'id_empresa_negocio'
   ];

   protected $guarded = [

   ];
}
