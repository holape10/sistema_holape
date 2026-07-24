<?php

namespace MasterSoft;

use Illuminate\Database\Eloquent\Model;

class FidelizacionConfig extends Model
{
    protected $table = 'fidelizacion_configs';

    // Agrega esta línea para permitir el guardado masivo
    protected $fillable = [
        'descripcion', 
        'valor_sol', 
        'puntos_minimos', 
        'premio', 
        'fecha_vencimiento',
        'activo'
    ];
}
