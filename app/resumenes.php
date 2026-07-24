<?php

namespace MasterSoft;

use Illuminate\Database\Eloquent\Model;

class resumenes extends Model
{
    protected $table = 'resumenes';
    protected $primaryKey = 'res_id';
    public $timestamps = false;

    protected $fillable = [
    	'res_fec',
        'res_fec_gen',
        'res_ticket',
        'res_est',
        'res_cod_est',
        'id_empresa_negocio',
        'res_tip'
    ]; 
}
