<?php

namespace MasterSoft;

use Illuminate\Database\Eloquent\Model;

class tipo_equipo extends Model
{
    protected $table = 'tipo_equipo';
	protected $primaryKey = 'id_tip_equi';

	public $timestamps = false;

	protected $fillable = [
		'nom_tip_equi',
	];

	protected $guarded = [

	];
}
