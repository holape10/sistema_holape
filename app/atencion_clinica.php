<?php

namespace MasterSoft;

use Illuminate\Database\Eloquent\Model;

class atencion_clinica extends Model
{
    protected $table = 'atencion_clinica';
	protected $primaryKey ='ate_cli_id';

	public $timestamps = false;

	protected $fillable = [
		
	];

	protected $guarded = [

	];
}
