<?php

namespace MasterSoft;

use Illuminate\Database\Eloquent\Model;

class usuario_pedidos extends Model
{
    protected $table = 'usuario_pedidos';
	protected $primaryKey ='Id';

	public $timestamps = false;

	protected $fillable = [
		'id_turno',
		'ped_id',
		'id_empresa_negocio',
		'IdEmpresa',
		'referencia',
		'fecha'

	];

	protected $guarded = [

	];
}
