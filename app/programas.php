<?php

namespace MasterSoft;

use Illuminate\Database\Eloquent\Model;

class programas extends Model
{
    protected $table = 'programas';
	protected $primaryKey ='prog_id';

	public $timestamps = false;

	protected $fillable = [
		'prog_cod',
		'prog_nom'
	];

	protected $guarded = [

	];
}
