<?php

namespace MasterSoft;

use Illuminate\Database\Eloquent\Model;

class movimientos extends Model
{
	protected $table = 'movimientos';
	protected $primaryKey ='mov_id';

	public $timestamps = false;

	protected $fillable = [
	'mov_id',
	'IdProducto',
	'mov_fec',
	'cantidad',
	'mov_tip',
	'mov_mot',
	'IdEmpresa',
	'unidad',
	'propuncom',
	'provuncom',
	'propunmin',
	'propunmay',
	'comprobante',
	'serie',
	'numero',
	'codpro',
	'descripcion',
	'id_empresa_negocio'
		
	];

	protected $guarded = [

	];
    //
}
