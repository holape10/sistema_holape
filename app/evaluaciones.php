<?php

namespace MasterSoft;

use Illuminate\Database\Eloquent\Model;

class evaluaciones extends Model
{
    protected $table = 'evaluaciones';
	protected $primaryKey ='eval_id';

	public $timestamps = false;

	protected $fillable = [
		'eval _nom',
		'IdEmpresa',
		'id_empresa_negocio'

	];

	protected $guarded = [

	];
}
