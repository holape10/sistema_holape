<?php

namespace MasterSoft;

use Illuminate\Database\Eloquent\Model;

class Cliente extends Model
{
	protected $table = 'cliente';
	protected $primaryKey ='clicod';

	public $timestamps = false;

	protected $fillable = [
		'clicod',
		'fecha_consulta',
		'motivo_consulta',
		'exploracion_fisica',
		'diagnostico',
		'tratamiento',


		'tdicod',
		'clinum',
		'clinom',	
		'clidir',
		'clicor',
		'clicor2',
		'clicor3',
		'clicor4',
		'cliest',
		'rucemp',
		'vendedor',
		'telefono',
		'cuenta12',
		'direccion1',
		'direccion2',
		'direccion3',
		'direccion4',
		'direccion5',
		'clicon',
		'clicontel',
		'fecha_nacimiento',
		'sex_id',
		'est_civ_id'
	];

	public function paciente()
    {
        return $this->belongsTo(Cliente::class, 'clicod', 'clicod');
    }

	protected $guarded = [

	];
    //
}
