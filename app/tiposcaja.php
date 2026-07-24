<?php

namespace MasterSoft;

use Illuminate\Database\Eloquent\Model;

class tiposcaja extends Model
{
    protected $table = 'tiposcaja';
    
	protected $primaryKey ='tip_caj_id';

	protected  $keyType = 'string';

	public $timestamps = false;

	protected $fillable = [
		'tip_caj_nom',
		'IdEmpresa',
		'id_empresa_negocio'

	];

	protected $guarded = [

	];
}
