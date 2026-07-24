<?php

namespace MasterSoft;

use Illuminate\Database\Eloquent\Model;

class Paciente extends Model
{
    protected $table = 'pacientes';
    
    protected $fillable = [
        'nombre_completo', 
        'dni', 
        'fecha_nacimiento', 
        'telefono', 
        'antecedentes'
    ];

    // Relación: Un paciente tiene muchas consultas
    public function consultas()
    {
        return $this->hasMany(ConsultaGinecologica::class, 'paciente_id');
    }
}