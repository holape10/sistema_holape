<?php

namespace MasterSoft;

use Illuminate\Database\Eloquent\Model;

class historia_clinica extends Model
{
    protected $table = 'historia_clinica';
	protected $primaryKey ='his_cli_id';

	public $timestamps = false;

	protected $fillable = [


	];

	protected $guarded = [

	];
}
