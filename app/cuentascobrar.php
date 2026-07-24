<?php

namespace MasterSoft;

use Illuminate\Database\Eloquent\Model;

class cuentascobrar extends Model
{
    protected $table = 'cuentas_cobrar';
    protected $primaryKey = 'cue_cob_id';
    public $timestamps = false;

    protected $fillable = [
		'IdCpe_cabecera',
		'clicod',
		'fec_pago',
		'abono',
		'saldo',
		'cuen_ban_id',
		'num_oper',
		'fecha_deposito',
		'id_empresa_negocio'
    ]; 
}
