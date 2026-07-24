<?php

namespace MasterSoft;

use Illuminate\Database\Eloquent\Model;

class Colores extends Model
{
    protected $table = 'colores';
	protected $primaryKey ='id_color';

	public $timestamps = false;

	protected $fillable = [
		'descripcion',
		'IdEmpresa'
	];

	protected $guarded = [

	];
}
