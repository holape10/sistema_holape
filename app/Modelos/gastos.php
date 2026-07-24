<?php

namespace MasterSoft\Modelos;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Model;
use MasterSoft\Http\Requests;
use MasterSoft\Modelos\gastos_cabecera;
use MasterSoft\Modelos\gastos_detalle;
use MasterSoft\Empresa;
use MasterSoft\EmpresaNegocios;
use MasterSoft\Cliente;
use MasterSoft\unidad_medida;
use MasterSoft\cpe_detalle;
use MasterSoft\cpe_baja;
use MasterSoft\Comprobante;
use MasterSoft\compras_cabecera;
use MasterSoft\User;
use MasterSoft\tipo_documento_identidad;
use MasterSoft\tipo_documento;
use MasterSoft\productos;
use MasterSoft\tipocambio;
use MasterSoft\MontoLetras;
use Illuminate\Support\Facades\Auth;
use MasterSoft\Http\Requests\FacturaCreateFormRequest;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Input;
use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\FilePrintConnector;
use Mike42\Escpos\EscposImage;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use DB;
use Carbon;
use Excel;
use PDF;

class Gastos extends Model
{
        
	private $fec_ini;
	private $fec_fin;
	private $suc_id;

    
    public function obtenerGastos($suc_id,$fec_ini,$fec_fin){

    	$gastos = gastos_cabecera::select('gast_fec_ven','gast_fec','gast_doc_ser','gast_doc_num','tdodes','monnom','prov_raz','total_gast','gast_obs','prov_ruc')
    	->leftjoin('tipo_documento','tipo_documento.tdocod','gastos_cabecera.tdocod')
    	->leftjoin('moneda','moneda.moncod','gastos_cabecera.mon_id')
    	->leftjoin('proveedor','proveedor.prov_id','gastos_cabecera.prov_id')
    	->where('tipo_movimiento','GASTO')
            ->where(function ($query) use ($fec_ini,$fec_fin){
            
                $query->where('gast_fec','>=',$fec_ini)
                ->where('gast_fec','<=',$fec_fin);
            
        })
    	->where('est_gasto','Registrado')
    	->get();

    	return $gastos;
    }

    public function obtenerGastosEliminados($suc_id,$fec_ini,$fec_fin){

    	$gastos = gastos_cabecera::select('gast_fec_ven','gast_fec','gast_doc_ser','gast_doc_num','tdodes','monnom','prov_raz','total_gast','gast_obs','prov_ruc')
    	->leftjoin('tipo_documento','tipo_documento.tdocod','gastos_cabecera.tdocod')
    	->leftjoin('moneda','moneda.moncod','gastos_cabecera.mon_id')
    	->leftjoin('proveedor','proveedor.prov_id','gastos_cabecera.prov_id')
        ->where(function ($query) use ($fec_ini,$fec_fin){
            
                $query->where('gast_fec','>=',$fec_ini)
                ->where('gast_fec','<=',$fec_fin);
            
        })
    	->where('tipo_movimiento','GASTO')
    	->where('est_gasto','Elimiinado')
    	->get();

    	return $gastos;

    }

    public function obtenerGastosDetallado($suc_id,$fec_ini,$fec_fin){
    	
    	$gastos = gastos_cabecera::select('gast_fec_ven','gast_fec','gast_doc_ser','gast_doc_num','tdodes','monnom','prov_raz','total_gast','gast_obs','pre_uni','total','det_gasto','tip_gas_nom','prov_ruc')
    	->leftjoin('gastos_detalle','gastos_detalle.gast_cab_id','gastos_cabecera.gast_cab_id')
    	->leftjoin('tipo_gastos','tipo_gastos.tip_gas_id','gastos_detalle.tip_gas_id')
    	->leftjoin('tipo_documento','tipo_documento.tdocod','gastos_cabecera.tdocod')
    	->leftjoin('moneda','moneda.moncod','gastos_cabecera.mon_id')
    	->leftjoin('proveedor','proveedor.prov_id','gastos_cabecera.prov_id')
    	->where('tipo_movimiento','GASTO')
    	->where('est_gasto','Registrado')
              ->where(function ($query) use ($fec_ini,$fec_fin){
            
                $query->where('gast_fec','>=',$fec_ini)
                ->where('gast_fec','<=',$fec_fin);
            
        })
    	->get();

    	return $gastos;

    }

    public function obtenerGastosDetalladoEliminados($suc_id,$fec_ini,$fec_fin){

    	$gastos = gastos_cabecera::select('gast_fec_ven','gast_fec','gast_doc_ser','gast_doc_num','tdodes','monnom','prov_raz','total_gast','gast_obs','pre_uni','total','det_gasto','tip_gas_nom','prov_ruc')
    	->leftjoin('gastos_detalle','gastos_detalle.gast_cab_id','gastos_cabecera.gast_cab_id')
    		->leftjoin('tipo_gastos','tipo_gastos.tip_gas_id','gastos_detalle.tip_gas_id')
    	->leftjoin('tipo_documento','tipo_documento.tdocod','gastos_cabecera.tdocod')
    	->leftjoin('moneda','moneda.moncod','gastos_cabecera.mon_id')
    	->leftjoin('proveedor','proveedor.prov_id','gastos_cabecera.prov_id')
    	->where('tipo_movimiento','GASTO')
    	->where('est_gasto','Elimiinado')
             ->where(function ($query) use ($fec_ini,$fec_fin){
            
                $query->where('gast_fec','>=',$fec_ini)
                ->where('gast_fec','<=',$fec_fin);
            
        })
    	->get();

    	return $gastos;
    }
 	


}
