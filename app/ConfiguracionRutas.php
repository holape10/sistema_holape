<?php

namespace MasterSoft;

use Illuminate\Database\Eloquent\Model;

class ConfiguracionRutas extends Model
{

   protected $table = 'configuracion_rutas';

   protected $primaryKey = 'Id';

   public $timestamps = false;

   protected $fillable = [
   		'descripcion',
   		'ruta',
   ];

   protected $guarded = [

   ];

}
