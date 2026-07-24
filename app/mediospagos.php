<?php

namespace MasterSoft;

use Illuminate\Database\Eloquent\Model;

class mediospagos extends Model
{
    protected $table = 'medios_pagos';
	protected $primaryKey ='id_med_pag';

	public $timestamps = false;

	protected $fillable = [
		'nom_med_pag',
		'IdEmpresa'
	];

	protected $guarded = [

	];
}
