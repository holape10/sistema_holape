<?php

namespace MasterSoft;

use Illuminate\Database\Eloquent\Model;

class marcas extends Model
{
	protected $table = 'marcas';
	protected $primaryKey ='mar_id';

	public $timestamps = false;

	protected $fillable = [
		'mar_nom',
		
	];

	protected $guarded = [

	];
    //
}
