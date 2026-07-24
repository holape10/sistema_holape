<?php

namespace MasterSoft;

use Illuminate\Database\Eloquent\Model;

class PlanContable extends Model
{
    protected $table = 'plan_contable';
    
    protected $fillable = [
        'codigo', 'nombre', 'tipo', 'nivel', 'acepta_movimiento'
    ];
}