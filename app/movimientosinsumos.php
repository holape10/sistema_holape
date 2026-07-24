<?php

namespace MasterSoft;

use Illuminate\Database\Eloquent\Model;

class movimientosinsumos extends Model
{
	protected $table = 'movimientosinsumos';
	protected $primaryKey ='mov_id_insumo';

	public $timestamps = false;

	protected $fillable = [
	'mov_id_insumo',
	'IdInsumo',
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
	'IdUsuario',
	'prov_id',
	'observacion',
	'fec_vencimiento',
	'IdUsuario',
	'IdCpe_cabecera',
	'totalfactura',
	'preciounitario',
	'IdDistribuidor',
	'medida',
	'totalmedida',
	'id_empresa_negocio'
		
	];

	protected $guarded = [

	];
    //
}
