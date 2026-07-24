<?php

namespace MasterSoft;

use Illuminate\Database\Eloquent\Model;

class TurnoAsistencia extends Model
{
    protected $table = 'turnos_asistencia';

    protected $fillable = [
        'codigo', 
        'hora_entrada_1', 
        'hora_salida_1', 
        'hora_entrada_2', 
        'hora_salida_2', 
        'tolerancia_minutos', 
        'descripcion'
    ];

    public $timestamps = false;
}