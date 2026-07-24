<?php

namespace MasterSoft;

use Illuminate\Database\Eloquent\Model;

class cuentaspagar extends Model
{
    protected $table = 'cuentas_pagar';
    protected $primaryKey = 'cue_pag_id';
    public $timestamps = false;

    protected $fillable = [
		'com_cab_id',
		'fec_pago',
		'abono',
		'saldo',
		'cuen_ban_id',
		'num_oper',
		'fecha_deposito',
		'id_empresa_negocio'
    ]; 
}
