<?php

namespace MasterSoft;

use Illuminate\Database\Eloquent\Model;

class TipoDocumentoIdentidad extends Model
{
    protected $table = 'tipo_documento_identidad';

    protected $primaryKey = 'IdDocumento';

    protected $timestamps = false;

    protected $fillable [
    	'CodDocumento',
    	'NomDocumento',
    	'EstDocumento'
    ];

    protected $guarded [

    ];
}
