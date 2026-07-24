<?php

namespace MasterSoft;

use Illuminate\Database\Eloquent\Model;

class usuario_gastos extends Model
{
    protected $table = 'usuario_gastos';
	protected $primaryKey ='Id';

	public $timestamps = false;

	protected $fillable = [
		'id_turno',
		'gast_cab_id',
		'id_empresa_negocio',
		'IdEmpresa',
		'referencia'

	];

	protected $guarded = [

	];
}
