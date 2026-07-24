<?php

namespace MasterSoft;

use Illuminate\Database\Eloquent\Model;

class subcategorias extends Model
{
    protected $table = 'subcategorias';
	protected $primaryKey ='subcat_id';

	public $timestamps = false;

	protected $fillable = [
		'subcat_nom',
		'cat_id',
		'id_empresa_negocio'

	];

	protected $guarded = [

	];
}
