<?php

namespace MasterSoft;

use Illuminate\Database\Eloquent\Model;

class maquinas extends Model
{
    protected $table = 'maquinas';
	protected $primaryKey ='maq_id';

	public $timestamps = false;

	protected $fillable = [
		'maq_id',
		'maq_cod',
		'maq_nom',
		'maq_est'
	];

	protected $guarded = [

	];
}
