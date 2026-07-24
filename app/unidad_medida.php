<?php

namespace MasterSoft;

use Illuminate\Database\Eloquent\Model;

class unidad_medida extends Model
{
    protected $table = "unidad_medida";
  
    protected $primaryKey = "umecod";

    public $timestamps = false;

    protected $fillable = [
    	'umenom',
    	'umecin',
    	'umest',
    ];
}
