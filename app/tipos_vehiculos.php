<?php

namespace MasterSoft;

use Illuminate\Database\Eloquent\Model;

class tipos_vehiculos extends Model
{
    protected $table = 'tipos_vehiculos';
	protected $primaryKey ='id_tipo_vehiculo';

	public $timestamps = false;

	protected $fillable = [
		'descripcion',
		'IdEmpresa',
		'id_empresa_negocio'

	];

	protected $guarded = [

	];
}
