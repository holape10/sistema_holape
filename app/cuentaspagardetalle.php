<?php

namespace MasterSoft;

use Illuminate\Database\Eloquent\Model;

class cuentaspagardetalle extends Model
{
    protected $table = 'cuentas_pagar_detalle';
    protected $primaryKey = 'cue_pag_det_id';
    public $timestamps = false;

    protected $fillable = [
		'cue_pag_id',
		'fec_dep',
		'abono',
		'cuen_ban_id',
		'num_oper',
		'comentario',
		'fec_reg'
    ]; 
}
