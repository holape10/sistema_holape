<?php

namespace MasterSoft;

use Illuminate\Database\Eloquent\Model;

class tipo_igv extends Model
{
    protected $table ='tipo_igv';
    protected $primaryKey ='tigcod';
    public $timestamps = false;

    protected $fillable = [
    	'tigdes',
    	'tigpor',
    	'tigest'
    ];
}
