<?php

namespace MasterSoft;

use Illuminate\Database\Eloquent\Model;

class AsientoDetalle extends Model
{
    public $timestamps = false; // Basado en tu estructura, los detalles no suelen llevar timestamps
    protected $table = 'asiento_detalles';
    
    protected $fillable = [
        'asiento_id', 'plan_contable_id', 'debe', 'haber', 'referencia_id', 'referencia_tipo'
    ];

    public function cuenta()
    {
        return $this->belongsTo(PlanContable::class, 'plan_contable_id');
    }
}