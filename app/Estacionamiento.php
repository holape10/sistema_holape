<?php

namespace MasterSoft;

use Illuminate\Database\Eloquent\Model;

class Estacionamiento extends Model
{
    protected $table = 'estacionamientos';
    public $timestamps = false; // No usaremos created_at/updated_at de Laravel
    protected $fillable = ['placa', 'codigo_barras', 'hora_ingreso', 'hora_salida', 'monto_total', 'estado'];
}