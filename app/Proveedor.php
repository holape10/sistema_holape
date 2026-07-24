<?php

namespace MasterSoft;

use Illuminate\Database\Eloquent\Model;

class Proveedor extends Model
{
	protected $table = 'proveedor';
	protected $primaryKey ='prov_id';

	public $timestamps = false;

	protected $fillable = [
		'prov_ruc',
		'prov_raz',
		'tdicod',
		'prov_con',
		'prov_num_con',
		'prov_cor',
	  'prov_dir',
		'IdEmpresa',
	];

	protected $guarded = [

	];
    //
}
