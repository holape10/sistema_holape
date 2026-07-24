<?php

namespace MasterSoft;

use Illuminate\Database\Eloquent\Model;

class Asiento extends Model
{
    protected $table = 'asientos';
    
    protected $fillable = [
        'fecha', 'glosa', 'tipo_asiento', 'moneda', 'tipo_cambio'
    ];

    // Relación con sus detalles
    public function detalles()
    {
        return $this->hasMany(AsientoDetalle::class, 'asiento_id');
    }
}