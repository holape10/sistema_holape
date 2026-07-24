<?php

namespace MasterSoft;

use Illuminate\Database\Eloquent\Model;

class cuentasbancarias extends Model
{
    protected $table = 'cuentasbancarias';
    protected $primaryKey = 'cuen_ban_id';
    public $timestamps = false;

    protected $fillable = [
    	'id_empresa_negocio',
		'IdEmpresa',
		'cuen_ban_tip',
		'cuen_ban_num',
		'moncod'
    ]; 
}
