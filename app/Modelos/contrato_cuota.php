<?php

namespace MasterSoft\Modelos;

use Illuminate\Database\Eloquent\Model;

class contrato_cuota extends Model
{
    protected $table = 'contrato_cuota';
	protected $primaryKey ='cont_cuot_id';

	public $timestamps = false;

	protected $fillable = [
		
	];

	protected $guarded = [

	];
}
