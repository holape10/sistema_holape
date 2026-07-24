<?php

namespace MasterSoft;

use Illuminate\Database\Eloquent\Model;

class areas extends Model
{
    protected $table = 'areas';
	protected $primaryKey ='are_emp_id';

	public $timestamps = false;

	protected $fillable = [
		'are_emp_cod',
		'are_emp_des'
	];

	protected $guarded = [

	];
}
