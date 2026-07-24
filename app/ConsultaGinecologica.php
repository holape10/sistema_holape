<?php

namespace MasterSoft;

use Illuminate\Database\Eloquent\Model;

class ConsultaGinecologica extends Model
{
    protected $table = 'consultas_ginecologicas';
    
    // ESTO ES LO IMPORTANTE: clicod debe estar aquí para que Laravel permita guardarlo
    protected $fillable = [
        'clicod', 
        'fecha_consulta', 
        'motivo_consulta', 
        'exploracion_fisica', 
        'diagnostico', 
        'tratamiento'
    ];

    public function paciente()
    {
        return $this->belongsTo(Cliente::class, 'clicod', 'clicod');
    }
}