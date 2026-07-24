<?php

namespace MasterSoft;

use Illuminate\Database\Eloquent\Model;

class EmpresaNegocios extends Model
{
    protected $table = 'empresa_negocios';

   protected  $primaryKey = 'id_empresa_negocio';

   public $timestamps = false;

   protected $fillable = [
   		'tipo_negocio',
   		'nombre_comercial',
         'estado',
   		'direccion',
   		'telefono',
   		'correo',
   		'web',
   		'IdEmpresa',
         'FseEmpresa',
         'FnuEmpresa',
         'BseEmpresa',
         'BnuEmpresa',
         'BanuEmpresa',
         'FcnuEmpresa',
         'FdnuEmpresa',
         'BcnuEmpresa',
         'BdnuEmpresa',
         'SerNota',
         'NumNota',
         'codigofiscal'
   ];

   protected $guarded = [

   ];
}
