<?php

namespace MasterSoft;

use Illuminate\Database\Eloquent\Model;

class recetas extends Model
{
  protected $table = 'recetas';
	protected $primaryKey ='rec_id';
	
	public $timestamps = false;

	protected $fillable = [
		'prod_id',
		'prod_ins',
		'rec_cant'
	];

	protected $guarded = [

	];
    //
}
