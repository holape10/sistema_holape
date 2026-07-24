<?php

namespace MasterSoft;

use Illuminate\Database\Eloquent\Model;

class Chofer extends Model
{
    protected $table = 'choferes';

    protected $fillable = [
        'dni', 'nombres_apellidos', 'licencia', 
        'telefono', 'estado', 'id_empresa_negocio'
    ];
}