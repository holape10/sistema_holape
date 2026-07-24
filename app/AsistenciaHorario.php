<?php

namespace MasterSoft;

use Illuminate\Database\Eloquent\Model;

class AsistenciaHorario extends Model
{
    protected $table = 'asistencia_horarios';

    protected $fillable = [
        'emp_id', 
        'turno_id', 
        'fecha'
    ];

    public $timestamps = false;

    // Relación para traer la información del turno fácilmente
    public function turno()
    {
        return $this->belongsTo(TurnoAsistencia::class, 'turno_id');
    }
}