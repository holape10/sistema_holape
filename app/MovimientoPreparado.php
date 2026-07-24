<?php

namespace MasterSoft;

use Illuminate\Database\Eloquent\Model;

class MovimientoPreparado extends Model
{
    protected $table = 'movimientos_preparados';
    
    protected $fillable = [
        'producto_id', 
        'pedido_id', 
        'usuario_id',
        'tipo_movimiento', 
        'cantidad', 
        'stock_resultante', 
        'observacion', 
        'fecha_proceso'
    ];

    public function producto()
    {
        // Enlazamos con el primary key correcto de tu tabla productos
        return $this->belongsTo('App\Producto', 'producto_id', 'IdProducto');
    }
}