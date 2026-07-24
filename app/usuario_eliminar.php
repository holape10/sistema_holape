<?php

namespace MasterSoft;

use Illuminate\Database\Eloquent\Model;

class usuario_eliminar extends Model
{
    protected $table = 'usuario_eliminar';
	protected $primaryKey ='Id';

	public $timestamps = false;

	protected $fillable = [
		'ped_id',
		'id_usu_elim',
		'id_usu_aut',
		'motivo'

	];

	protected $guarded = [

	];
}
