<?php

namespace MasterSoft;

use Illuminate\Database\Eloquent\Model;

class presentaciones extends Model
{
   protected $table = 'presentaciones';

   protected $primaryKey = 'IdPresentacion';

   public $timestamps = false;

   protected $fillable = [
   		'Presentacion',
   		'Descripcion',
   		'IdEmpresa',
         'id_empresa_negocio'
   ];

   protected $guarded = [

   ];
}
