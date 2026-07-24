<?php

namespace MasterSoft;

use Illuminate\Database\Eloquent\Model;

class tipogastos extends Model
{
    protected $table = 'tipo_gastos';
	protected $primaryKey ='tip_gas_id';

	public $timestamps = false;

	protected $fillable = [
		'tip_gas_id',
		'tip_gas_nom'
	];

	protected $guarded = [

	];
}
