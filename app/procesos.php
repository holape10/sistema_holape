<?php

namespace MasterSoft;

use Illuminate\Database\Eloquent\Model;

class procesos extends Model
{
    protected $table = 'procesos';
	protected $primaryKey ='proc_id';

	public $timestamps = false;

	protected $fillable = [
		'proc_id',
		'proc_cod',
		'proc_nom',
		'proc_est'
	];

	protected $guarded = [

	];
}
