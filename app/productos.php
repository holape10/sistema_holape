<?php

namespace MasterSoft;

use Illuminate\Database\Eloquent\Model;

class productos extends Model
{
  protected $table = 'productos';
	protected $primaryKey ='IdProducto';
	//protected  $keyType = 'string';
	public $timestamps = false;

	protected $fillable = [
		'proest',
		'moncod',
		'umecod',
		'pronom',
		'stock',
		'procod',
		'IdEmpresa',
		'propun',
		'propun1',
		'propun2',
		'propun3',
		'margen2',
		'margen3',
		'margen4',
		'margen',
		'provun',
		'cat_id',
		'propuncom',
		'provuncom',
		'IdDistribuidor',
		'cod_producto_sunat',
		'requiere_lote_vencimiento',
		'comprobante',
		'promocion',		
		'ranking',
		'id_empresa_negocio',
		'stockinicial',
		'imagenproducto'
	];

	protected $guarded = [

		/*'requiere_lote_vencimiento' => 'boolean', 
        
        'propun' => 'float',
        'propun1' => 'float',
        'propun2' => 'float',
        'costo' => 'float',
        'peso' => 'float',
        'flete' => 'float',
        'costo_total' => 'float',
        'stock_min' => 'float',
        'icbper' => 'integer',
        'stock_migrar' => 'float',
        'propuncom' => 'float',
        'comision' => 'float',
        'costofijo' => 'float',
        'stock' => 'float',
        'stockinicial' => 'float',
        'dias_garantia' => 'integer',
        'mon_icbper' => 'float',
        'factor_cons' => 'float',
        'vencimiento' => 'date', */


	];
    //
}
