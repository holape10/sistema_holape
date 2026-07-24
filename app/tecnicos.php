<?php

namespace MasterSoft;

use Illuminate\Database\Eloquent\Model;

class tecnicos extends Model
{
	protected $table = 'tecnicos';
	protected $primaryKey ='tec_id';

	public $timestamps = false;

	protected $fillable = [
		'tdicod',
		'tecnum',
		'tecnom',	
		'tecdir',
		'tectel',
		'teccor',
		'tecest',
		'rucemp'
	];

	protected $guarded = [

	];
    //
}
