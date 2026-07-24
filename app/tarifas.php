<?php

namespace MasterSoft;

use Illuminate\Database\Eloquent\Model;

class tarifas extends Model
{
    protected $table = 'tarifas';
	protected $primaryKey ='id_tarifa';

	public $timestamps = false;

	protected $fillable = [
		'id_tarifa',
		'descripcion',
		'precio',
		'tolerancia',
		'IdEmpresa',
		'id_empresa_negocio'

	];

	protected $guarded = [

	];
}
