<?php

namespace MasterSoft\Modelos;

use Illuminate\Database\Eloquent\Model;
use MasterSoft\cpe_cabecera;
use MasterSoft\cpe_detalle;
use MasterSoft\productos;
use DB;


class Almacen extends Model
{
   

	public function buscar_almacen_predeterminado($id_empresa_negocio){

		$almacen = DB::tABLE('almacenes')->where('id_empresa_negocio',$id_empresa_negocio)->where('predeterminado','1')->get();

		return $almacen;
		
	}

	public function buscar_almacenes($id_empresa_negocio){

		$almacen = DB::tABLE('almacenes')->where('id_empresa_negocio',$id_empresa_negocio)->get();

		return $almacen;
		
	}

   public function movimiento_calcular_stock($IdProducto,$id_almacen){

   	  $productos = DB::tABLE('productos')->select('IdProducto')->where('IdProducto',$IdProducto)->get();

	    foreach($productos as $pro){

	       $movimientos = DB::TABLE('movimientos_productos')->select('descripcion','mov_pro_id','mov_tip','cantidad')
	    	->where(function ($query) use ($pro) {
                    $query->where('IdProducto',$pro->IdProducto)
                    ->orWhere('IdProducto_rel',$pro->IdProducto);
            })
	    	//->where('fecha_mov','>=',$fecha)
	    	->where('id_almacen',$id_almacen)
	    	->orderby('fecha_mov','asc')
	    	->orderby('mov_tip','desc')
	    	->orderby('tipo','asc')
	    	->get();

	    	$stock = 0;
	    	$i=0;
	    	
	     	foreach($movimientos as $mov){

	        	if($i==0){

	        			if($mov->descripcion=='STOCK_INICIAL' || $mov->descripcion=='SALDO_ANTERIOR'){

	        				$stock = $mov->cantidad;

	        			}else{
	        				
	        				if($mov->mov_tip=='I'){
                      			$stock = $mov->cantidad;
                  			}else{
                      			$stock = $mov->cantidad*(-1);
                  			}
	        			}
	           				
	            		DB::TABLE('movimientos_productos')->where('mov_pro_id',$mov->mov_pro_id)->update(['stock'=>$stock]);

	        	}else{

	             	if($mov->descripcion=='STOCK_INICIAL' || $mov->descripcion=='SALDO_ANTERIOR'){

                     	$stock = $mov->cantidad;

                	}else{

                    	if($mov->mov_tip=='I'){
                       		$stock =$stock + $mov->cantidad;
                    	}else{
                      		$stock =$stock - $mov->cantidad;
                   		}

                	}

	              	DB::TABLE('movimientos_productos')->where('mov_pro_id',$mov->mov_pro_id)->update(['stock'=>$stock]);
	        	}

	        	$i = $i+1;
	    	}

	    	 DB::tABLE('producto_stock')->where('IdProducto',$pro->IdProducto)->where('id_almacen',$id_almacen)->update(['stock'=>$stock]);

	    }

	    return 'actualizado';

   }

 		
   public function registrar_movimiento_salida($id){

    $cabecera = cpe_cabecera::findOrFail($id);


     if(empty($cabecera->ccabaj) || is_null($cabecera->ccabaj)){
    $detalle = cpe_detalle::where('IdCpe_cabecera',$id)->get();


    foreach($detalle as $det){

        $bus_pro = productos::findOrFail($det->IdProducto);

        if(empty($bus_pro->pro_rel)){
            $id_prod = $bus_pro->IdProducto;
        }else{
            $id_prod = $bus_pro->pro_rel;
        }

        DB::tABLE('movimientos_productos')->insert([
            'IdProducto'=>$det->IdProducto,
            'IdProducto_rel'=>$id_prod,
            'precio'=>$det->cdepuni,
            'cantidad'=>$det->cdecan*$det->cpe_det_factor,
            'costo'=>$det->costo_total,
            'cliente'=>$cabecera->ccanom,
            'IdCpe_cabecera'=>$cabecera->IdCpe_cabecera,
            'serie'=>$cabecera->serdoc,
            'numero'=>$cabecera->numdoc,
            'tdocod'=>$cabecera->tdocod,
            'cod_tip_ope'=>'01',
            'descripcion'=>'VENTA',
            'tipo'=>'3',
            'mov_tip'=>'E',
            'id_empresa_negocio'=>$cabecera->id_empresa_negocio,
            'id_almacen'=>$cabecera->id_almacen,
            'fecha_mov'=>$cabecera->ccafem,
        ]);

    		self::movimiento_calcular_stock($id_prod,$cabecera->id_almacen);



    }
}
    return 'Registrado';


}


public function listar_cierres_almacen($id_almacen,$periodo){


	$registros = DB::tABLE('cierre_mensual')->select('descripcion','periodo','fecha_apertura','fecha_cierre','estado')
	->join('almacenes','almacenes.id_almacen','cierre_mensual.id_almacen')
	->where('almacenes.id_almacen',$id_almacen)->get();
	//dd($periodo.' '.$id_almacen);
//dd($registros);
	return $registros;
}

}
