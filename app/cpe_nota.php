<?php

namespace MasterSoft;

use Illuminate\Database\Eloquent\Model;

class cpe_nota extends Model
{
   protected $table = 'cpe_nota';

   protected $primaryKey = 'IdCpe_nota';

   public $timestamps = false;

   protected $fillable = [
   		'ccacar',
		'ccaestbu',
		'ccaestse',
		'ccaigv',
		'ccaisc',
		'ccaitv',
		'ccaobs',
		'ccaotr',
		'ccatve',
		'ccatvg',
		'ccatvgr',
		'ccatvi',
		'cnofem',
		'codhash',
		'IdCpe_cabecera',
		'IdEmpresa',
		'IdUsuario',
		'numdoc',
		'respse',
		'serdoc',
		'tdocod',
		'tdocod1',
		'tipcambio',
		'cnofem',
		'id_empresa_negocio'

   ];

   protected $guarded = [

   ];
}
