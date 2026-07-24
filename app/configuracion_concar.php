<?php

namespace MasterSoft;

use Illuminate\Database\Eloquent\Model;

class configuracion_concar extends Model
{
    protected $table = 'configuracion_concar';
	protected $primaryKey ='id_conf_conc';

	public $timestamps = false;

	protected $fillable = [
		

	];

	protected $guarded = [

	];
}
