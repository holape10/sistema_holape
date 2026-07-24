<?php

namespace MasterSoft;

use Illuminate\Database\Eloquent\Model;

class tipocambio extends Model
{
     protected $table = 'tipocambio';

    protected $primaryKey = 'IdTipCambio';

    public $timestamps = false;

    protected $fillable = [
        'IdEmpresa',
    	'FecTipCambio',
    	'CamVenta',
    	'CamCompra',
    ];

    protected $guarded = [

    ];
}
