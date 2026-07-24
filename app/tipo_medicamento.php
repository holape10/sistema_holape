<?php

namespace MasterSoft;

use Illuminate\Database\Eloquent\Model;

class tipo_medicamento extends Model
{
    protected $table = 'tipo_medicamento';

    protected $primaryKey = 'id_tip_med';

    public $timestamps = false;

    protected $fillable = [
    	'descripcion',
    	'IdEmpresa',
    ];

    protected $guarded = [

    ];
 }