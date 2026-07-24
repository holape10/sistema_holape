<?php

namespace MasterSoft\Modelos;

use Illuminate\Database\Eloquent\Model;

class contrato_cabecera extends Model
{
    protected $table = 'contrato_cabecera';
	protected $primaryKey ='cont_cab_id';

	public $timestamps = false;

	protected $fillable = [
		
	];

	protected $guarded = [

	];
}
