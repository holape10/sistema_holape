<?php

namespace MasterSoft;

use Illuminate\Database\Eloquent\Model;

class OrdenServicio extends Model
{
    protected $table = 'ordenes_servicio';
    
    protected $fillable = [
        'codigo', 'cliente_documento', 'cliente_nombre', 'origen', 
        'destino', 'descripcion_carga', 'peso', 'bultos', 'precio', 'estado'
    ];

    public function guia()
    {
        return $this->hasOne(GuiaRemision::class, 'orden_servicio_id');
    }
}