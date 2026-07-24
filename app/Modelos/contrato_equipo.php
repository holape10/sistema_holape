<?php

namespace MasterSoft\Modelos;

use Illuminate\Database\Eloquent\Model;

class contrato_equipo extends Model
{
    protected $table = 'contrato_equipo';
	protected $primaryKey ='cont_equi_id';

	public $timestamps = false;

	protected $fillable = [
		
	];

	protected $guarded = [

	];
}
