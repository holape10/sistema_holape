<?php

namespace MasterSoft;

use Illuminate\Database\Eloquent\Model;

class turnos extends Model
{
    protected $table = 'turnos';
	protected $primaryKey ='id_turno';

	public $timestamps = false;

	protected $fillable = [
		'turno',
		'IdUsuario',
		'apertura',
		'cierre',
		'IdEmpresa',
		'id_empresa_negocio',
		'estado'

	];

	protected $guarded = [

	];
}
