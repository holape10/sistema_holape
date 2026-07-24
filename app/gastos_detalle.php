<?php

namespace MasterSoft;

use Illuminate\Database\Eloquent\Model;

class gastos_detalle extends Model
{
	protected $table = 'gastos_detalle';
	protected $primaryKey ='gast_det_id';

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
		'gast_cab_id',
		'pre_ven_min',
		'pre_ven_may',
		'tip_igv',
		'det_gasto',
		
	];

	protected $guarded = [

	];
    //
}
