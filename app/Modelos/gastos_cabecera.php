<?php

namespace MasterSoft\Modelos;

use Illuminate\Database\Eloquent\Model;

class gastos_cabecera extends Model
{
	protected $table = 'gastos_cabecera';
	protected $primaryKey ='gast_cab_id';

	public $timestamps = false;

	protected $fillable = [
		'gast_doc_ser',
		'gast_doc_num',
		'gast_fec',	
		'gast_fec_ven',
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
		'est_gasto',
		'usu_elimino',
		'tipo_movimiento',
		'local',
		'id_empresa_negocio'
	];

	protected $guarded = [

	];
    //
}
