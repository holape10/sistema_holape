<?php

namespace MasterSoft;

use Illuminate\Database\Eloquent\Model;

class categorias extends Model
{
    protected $table = 'categorias';
	protected $primaryKey ='cat_id';

	public $timestamps = false;

	protected $fillable = [
		'cat_nom',
		'IdEmpresa'

	];

	protected $guarded = [

	];
}
