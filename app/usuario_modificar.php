<?php

namespace MasterSoft;

use Illuminate\Database\Eloquent\Model;

class usuario_modificar extends Model
{
    protected $table = 'usuario_modificar';
	protected $primaryKey ='Id';

	public $timestamps = false;

	protected $fillable = [
		'ped_id',
		'id_usu_mod',
		'id_usu_aut',
		'dni',
		'nombre',
		'descripcion',
		'placa',
		'id_tipo_vehiculo',
		'id_tarifa',
		'dnimod',
		'nombremod',
		'descripcionmod',
		'placamod',
		'id_tipo_vehiculomod',
		'id_tarifamod'

	];

	protected $guarded = [

	];
}
