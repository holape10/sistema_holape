<?php

namespace MasterSoft;

use Illuminate\Database\Eloquent\Model;

class conceptosbancarios extends Model
{
    protected $table = 'conceptosbancarios';
    protected $primaryKey = 'concepto_id';
    public $timestamps = false;

    protected $fillable = [
    	'concepto_nom',
        'IdEmpresa',
        'id_empresa_negocio'
    ]; 
}
