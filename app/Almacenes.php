<?php

namespace MasterSoft;

use Illuminate\Database\Eloquent\Model;

class Almacenes extends Model
{
    protected $table = 'almacenes';
    protected $primaryKey = 'id_almacen';
    public $timestamps = false;

    protected $fillable = [
    	'descripcion',
    	'id_empresa_negocio',
    	'id_empresa'
    ]; 
}
