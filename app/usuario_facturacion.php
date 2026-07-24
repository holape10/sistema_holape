<?php

namespace MasterSoft;

use Illuminate\Database\Eloquent\Model;

class usuario_facturacion extends Model
{
    protected $table = 'usuario_facturacion';
	protected $primaryKey ='Id';

	public $timestamps = false;

	protected $fillable = [
		'id_turno',
		'IdCpe_cabecera',
		'id_empresa_negocio',
		'IdEmpresa',
		'referencia'

	];

	protected $guarded = [

	];
}
