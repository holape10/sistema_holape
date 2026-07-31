<?php

namespace MasterSoft;

use Illuminate\Database\Eloquent\Model;

class Contribuyente extends Model
{
    protected $table = 'contribuyentes';
    protected $primaryKey = 'ruc';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false;

    protected $fillable = [
        'ruc',
        'razon_social',
        'estado',
        'condicion',
        'ubigeo',
        'tipo_via',
        'nombre_via',
        'codigo_zona',
        'tipo_zona',
        'numero',
        'interior',
        'lote',
        'departamento',
        'manzana',
        'kilometro'
    ];
}