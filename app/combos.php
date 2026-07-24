<?php

namespace MasterSoft;

use Illuminate\Database\Eloquent\Model;

class combos extends Model
{
  protected $table = 'combos';
	protected $primaryKey ='comb_id';
	
	public $timestamps = false;

	protected $fillable = [
		'IdProducto',
		'prod_nom_comb',
		'prod_comb_cant'
	];

	protected $guarded = [

	];
    //
}
