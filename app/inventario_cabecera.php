<?php

namespace MasterSoft;

use Illuminate\Database\Eloquent\Model;

class inventario_cabecera extends Model
{
    protected $table = 'inventario_cabecera';
	protected $primaryKey ='inv_cab_id';

	public $timestamps = false;

	protected $fillable = [
		'inv_fec',
		'IdUsuario',
		'id_almacen',
		'id_empresa_negocio'
	];

	protected $guarded = [

	];
}
