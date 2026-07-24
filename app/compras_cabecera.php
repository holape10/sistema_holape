<?php

namespace MasterSoft;

use Illuminate\Database\Eloquent\Model;

class compras_cabecera extends Model
{
	protected $table = 'compras_cabecera';
	protected $primaryKey ='com_cab_id';

	public $timestamps = false;

	protected $fillable = [
		'com_doc_ser',
		'com_doc_num',
		'com_fec',	
		'com_fec_ven',
		'mon_cod',
		'prov_id',
		'tip_cam',
		'tot_igv',
		'tot_grav',
		'tot_grat',
		'tot_exon',
		'tot_inaf',
		'tot_desc_por',
		'tot_desc',
		'tot_otr_car',
		'tot_exp',
		'tot_otr_tri',
		'total_com',
		'comp_obs',
		'IdEmpresa',
		'tdocod',
		'id_empresa_negocio'
	];

	protected $guarded = [

	];
    //
}
