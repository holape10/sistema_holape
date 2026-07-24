<?php

namespace MasterSoft;

use Illuminate\Database\Eloquent\Model;

class Comprobante extends Model
{
    protected $table = 'Comprobante';
    protected $primaryKey = 'IdComprobante';
    public $timestamps = false;

    protected $fillable = [
    	'',
    	'',
    	''
    ];
}
