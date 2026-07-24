<?php

namespace MasterSoft;

use Illuminate\Database\Eloquent\Model;

class fallas extends Model
{
    protected $table = 'fallas';
	protected $primaryKey ='fall_id';

	public $timestamps = false;

	protected $fillable = [
		'fall _nom',
		'IdEmpresa',
		'id_empresa_negocio'

	];

	protected $guarded = [

	];
}
