<?php

namespace MasterSoft;

use Illuminate\Database\Eloquent\Model;

class SerieProducto extends Model
{
    
    protected $table = "serie_producto";

    protected $primaryKey = "IdSerPro";

    public $timestamps = false;

    protected $fillable = [
    	'IdEmpresa',
    	'IdProducto',
    	'Serie'

    ];
}
