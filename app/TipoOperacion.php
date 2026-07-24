<?php

namespace MasterSoft;

use Illuminate\Database\Eloquent\Model;

class TipoOperacion extends Model
{
    protected $table = 'tipo_operacion';

    protected $primaryKey = 'IdTipoOperacion';

    protected $timestamps = false;

    protected $fillable [

    	'CodTipoOperacion',
    	'TipoOperacion',
    	'EstTipoOperacion'

    ];

    protected $guarded [

    ];
}
