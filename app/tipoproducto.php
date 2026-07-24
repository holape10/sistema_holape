<?php

namespace MasterSoft;

use Illuminate\Database\Eloquent\Model;

class tipoproducto extends Model
{
    protected $table = 'tipo_producto';
	protected $primaryKey ='tip_pro_id';

	public $timestamps = false;

	protected $fillable = [
		'tip_pro_nom',
		'subcat_id',
		'id_empresa_negocio'

	];

	protected $guarded = [

	];
}
