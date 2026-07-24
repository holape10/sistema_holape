<?php

namespace MasterSoft;

use Illuminate\Database\Eloquent\Model;

class movimientosbancarios extends Model
{
	protected $table = 'movimientosbancarios';
	protected $primaryKey ='mov_ban_id';

	public $timestamps = false;

	protected $fillable = [
		'mov_tip',
		'concepto_id',
		'mov_com',
		'doc_id',
		'mov_num_doc',
		'ban_id',
		'mov_num_oper',
		'importe',
		'estado',
		'mov_fecha',
		'clicod',
		'saldo',
		'IdEmpresa',
		'id_empresa_negocio'
		
	];

	protected $guarded = [

	];
    //
}
