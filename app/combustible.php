<?php

namespace MasterSoft;

use Illuminate\Database\Eloquent\Model;

class combustible extends Model
{
    protected $table = 'combustible';
	protected $primaryKey ='comb_id';

	public $timestamps = false;

	protected $fillable = [
		'comb_nom'

	];

	protected $guarded = [

	];
}
