<?php

namespace MasterSoft;

use Illuminate\Database\Eloquent\Model;

class placas extends Model
{
    protected $table = 'placas';
    protected $primaryKey = 'plac_id';
    public $timestamps = false;

    protected $fillable = [
    	'plac_prim',
        'plac_secu',
        'clicod'
    ]; 
}
