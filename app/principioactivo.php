<?php

namespace MasterSoft;

use Illuminate\Database\Eloquent\Model;

class principioactivo extends Model
{
    protected $table = 'principioactivo';
	protected $primaryKey ='pri_act_id';

	public $timestamps = false;

	protected $fillable = [
		'pri_act_nom'
	];

	protected $guarded = [

	];
}
