<?php

namespace MasterSoft;

use Illuminate\Database\Eloquent\Model;

class EmpresasFac extends Model
{
   
   protected $table = 'empresa';

   protected  $primaryKey = 'IdEmpresa';
   protected  $keyType = 'string';
   public $timestamps = false;

   protected $fillable = [
   		'NomEmpresa',
   		'LogEmpresa',
         'DirEmpresa',
   		'EstEmpresa'
   ];

   protected $guarded = [

   ];
}
