<?php

namespace MasterSoft;

use Illuminate\Database\Eloquent\Model;

class tiposdocumentos extends Model
{
    protected $table = 'tiposdocumentos';
    protected $primaryKey = 'doc_id';
    public $timestamps = false;

    protected $fillable = [
    	'doc_nom',
        'IdEmpresa',
        'id_empresa_negocio'
    ]; 
}
