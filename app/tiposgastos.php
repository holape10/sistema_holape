<?php

namespace MasterSoft;

use Illuminate\Database\Eloquent\Model;

class tiposgastos extends Model
{
  protected $table = 'tipos_gastos';
	protected $primaryKey ='Id';
	//protected  $keyType = 'string';
	public $timestamps = false;

	protected $fillable = [
		'codgasto',
		'descgasto',
		'estgasto',
		'IdEmpresa'
	
	];

	protected $guarded = [

	];
    //
}