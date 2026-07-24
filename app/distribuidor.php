<?php

namespace MasterSoft;

use Illuminate\Database\Eloquent\Model;

class distribuidor extends Model
{
	protected $table = 'distribuidor';
	protected $primaryKey ='IdDistribuidor';
	
  	// protected  $keyType = 'string';


	public $timestamps = false;

	protected $fillable = [
		'NomDistribuidor',
		'EstDistribuidor',
		'RucDistribuidor',
		'tdicod',
		'ContDistribuidor',
		'ContNumDistribuidor',
		'CorDistribuidor',
		'DirDistribuidor',
		'IdEmpresa'
	];

	protected $guarded = [

	];
    //
}
