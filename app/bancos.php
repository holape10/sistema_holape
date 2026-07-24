<?php

namespace MasterSoft;

use Illuminate\Database\Eloquent\Model;

class bancos extends Model
{
    protected $table = 'bancos';
    protected $primaryKey = 'IdBanco';
    public $timestamps = false;

    protected $fillable = [
    	'IdCuenta',
    	'NomCuenta',
    	'NumCuenta',
    	'MonCuenta',
    	'SalInicial',
    	'FecCuenta',
    	'descripcion',
    	'usu_reg',
    	'usu_upd',
        'estado'
    ]; 
}
