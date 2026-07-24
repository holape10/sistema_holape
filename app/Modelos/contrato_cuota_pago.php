<?php

namespace MasterSoft\Modelos;

use Illuminate\Database\Eloquent\Model;

class contrato_cuota_pago extends Model
{
    protected $table = 'contrato_cuota_pago';
	protected $primaryKey ='cont_cuot_pag_id';

	public $timestamps = false;

	protected $fillable = [
		
	];

	protected $guarded = [

	];
}
