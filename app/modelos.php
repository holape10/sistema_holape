<?php

namespace MasterSoft;

use Illuminate\Database\Eloquent\Model;

class modelos extends Model
{
    protected $table = 'modelos';
	protected $primaryKey ='mod_id';

	public $timestamps = false;

	protected $fillable = [
		'mod_nom',
	
	];

	protected $guarded = [

	];
}
