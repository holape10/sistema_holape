<?php

namespace MasterSoft;

use Illuminate\Database\Eloquent\Model;

class compras_detalle extends Model
{
	protected $table = 'compras_detalle';
	protected $primaryKey ='com_det_id';

	public $timestamps = false;

	protected $fillable = [
		'pro_id',
		'val_uni',
		'pre_uni',
		'igv',
		'subtotal',
		'total',
		'cantidad',
		'ume_cod',
		'com_cab_id',
		'pre_ven_min',
		'pre_ven_may',
		'tip_igv',
		
	];

	protected $guarded = [

	];
    //
}
