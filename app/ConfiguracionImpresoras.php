<?php

namespace MasterSoft;

use Illuminate\Database\Eloquent\Model;

class ConfiguracionImpresoras extends Model
{
    protected $table = 'configuracion_impresoras';

   protected $primaryKey = 'Id';

   public $timestamps = false;

   protected $fillable = [
   		'descripcion',
   		'ruta',
   		'IdEmpresa',
   		'id_empresa_negocio'
   ];

   protected $guarded = [

   ];
}
