<?php

namespace MasterSoft;

use Illuminate\Database\Eloquent\Model;

class Series extends Model
{
    protected $table = "fe_series";

    protected $primaryKey = "IdSerie";

    public $timestamps = false;

    protected $fillable = [
    	'IdEmpresa',
    	'Tipo_Documento',
    	'Numero_Serie',
    	'Num_Correlativo',
    	'Estado'

    ];
}
