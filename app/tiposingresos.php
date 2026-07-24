<?php

namespace MasterSoft;

use Illuminate\Database\Eloquent\Model;

class tiposingresos extends Model
{
  protected $table = 'tipos_ingresos';
	protected $primaryKey ='Id';
	//protected  $keyType = 'string';
	public $timestamps = false;

	protected $fillable = [
		'codingreso',
		'descingreso',
	
	];

	protected $guarded = [

	];
    //
}