<?php

namespace MasterSoft;

use Illuminate\Database\Eloquent\Model;

class laboratorio extends Model
{
    protected $table = 'laboratorio';
	protected $primaryKey ='lab_id';

	public $timestamps = false;

	protected $fillable = [
		'lab_cod',
		'lab_nom'
	];

	protected $guarded = [

	];
}
