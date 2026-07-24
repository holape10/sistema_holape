<?php

namespace MasterSoft;

use Illuminate\Database\Eloquent\Model;

class mesas extends Model
{
  protected $table = 'mesas';
  protected $primaryKey ='mes_id';

public $timestamps = false;

protected $fillable = [
  'mes_nom',
  'IdEmpresa',
  'mes_est',
  'id_empresa_negocio'

];
}
