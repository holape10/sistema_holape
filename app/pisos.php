<?php

namespace MasterSoft;

use Illuminate\Database\Eloquent\Model;

class pisos extends Model
{
	protected $table = 'pisos';
	protected $primaryKey ='pis_id';

	public $timestamps = false;

	protected $fillable = [
		'pis_nom',
		'emp_id',
		'suc_id'
	];

	protected $guarded = [

	];
    //
}
