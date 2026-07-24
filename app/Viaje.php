<?php

namespace MasterSoft;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Viaje extends Model
{
    use SoftDeletes;

    protected $table = 'viajes';

    protected $fillable = [
        'vehiculo_id', 
        'origen', 
        'destino', 
        'fecha_salida', 
        'estado',
        'costo_estimado'
    ];

    protected $dates = ['deleted_at', 'fecha_salida'];

    // Relación: Un viaje pertenece a un vehículo
    public function vehiculo()
    {
        return $this->belongsTo(Vehiculo::class, 'vehiculo_id', 'id');
    }
}