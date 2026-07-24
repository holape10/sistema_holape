<?php

namespace MasterSoft;

use Illuminate\Database\Eloquent\Model;

class empleado extends Model
{
	protected $table = 'empleado';
	protected $primaryKey ='emp_id';

	public $timestamps = false;

	protected $fillable = [
		'emp_nom',
        'emp_ape_mat',
        'emp_ape_pat',
        'emp_dir',
        'emp_cor',
        'emp_tel',
        'emp_cel',
        'emp_sex',
        'emp_est',
        'emp_num_doc',
        'tdicod',
        'id_empresa_negocio'

	];

	protected $guarded = [

	];
    //
}
