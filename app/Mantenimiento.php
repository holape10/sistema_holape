<?php

namespace MasterSoft;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Mantenimiento extends Model
{
    use SoftDeletes;

    protected $table = 'mantenimientos';

    protected $fillable = [
        'vehiculo_id', 
        'tipo_mantenimiento', 
        'descripcion', 
        'costo', 
        'kilometraje_actual',
        'fecha_mantenimiento'
    ];

    protected $dates = ['deleted_at'];

    // Relación
    public function vehiculo()
    {
        return $this->belongsTo(Vehiculo::class, 'vehiculo_id', 'id');
    }
}