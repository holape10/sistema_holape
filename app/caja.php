<?php

namespace MasterSoft;

use Illuminate\Database\Eloquent\Model;

class caja extends Model
{
    protected $table = 'caja';
	protected $primaryKey ='Id';

	public $timestamps = false;

	protected $fillable = [
		'fecha',
		'IdEmpresa',
		'estado',
		'usuario',

	];

	protected $guarded = [

	];
}
