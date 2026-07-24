<?php

namespace MasterSoft;

use Illuminate\Database\Eloquent\Model;

class cuentascobrardetalle extends Model
{
    protected $table = 'cuentas_cobrar_detalle';
    protected $primaryKey = 'cue_cob_det_id';
    public $timestamps = false;

    protected $fillable = [
		'cue_cob_id',
		'fec_dep',
		'abono',
		'cuen_ban_id',
		'num_oper',
		'comentario',
		'fec_reg'
    ]; 
}
