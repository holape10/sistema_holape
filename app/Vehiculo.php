<?php

namespace MasterSoft;

use Illuminate\Database\Eloquent\Model;

class Vehiculo extends Model
{
    protected $table = 'vehiculos';

    protected $fillable = [
        'placa', 'marca', 'modelo', 'carga_util_kg', 
        'inscripcion_mtc', 'estado', 'id_empresa_negocio'
    ];
}