<?php

namespace MasterSoft;

use Illuminate\Database\Eloquent\Model;

class EmpresaNegocio extends Model
{
    protected $table = 'empresa_negocios';
    protected $primaryKey = 'id_empresa_negocio';
    public $timestamps = false;

    // Relación opcional si quieres jalar los datos de la empresa principal
    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'IdEmpresa', 'IdEmpresa');
    }
}