<?php

namespace MasterSoft;

use Illuminate\Database\Eloquent\Model;

class Empresa extends Model
{

   protected $table = 'empresa';

   protected $primaryKey = 'IdEmpresa';

   protected  $keyType = 'string';
   
   public $timestamps = false;

   protected $fillable = [
   		'NomEmpresa',
         'NomComercial',
   		'LogEmpresa',
         'DirEmpresa',
   		'EstEmpresa',
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
         'formato',
         'mod_almacen',
         'fecbaja',
         'wsurl',
         'wsusuario',
         'wscontrasena'
   ];

   protected $guarded = [

   ];
}
