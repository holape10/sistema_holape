<?php

namespace MasterSoft\Modelos;

use Illuminate\Database\Eloquent\Model;
use MasterSoft\compras_cabecera;
use MasterSoft\compras_detalle;
use DB;


class ReportesCompras extends Model
{
    
	private $fec_ini;
	private $fec_fin;
	private $prov_id;
	private $suc_id;
	private $id_almacen;
    
   
    //OBTIENE VENTAS TOTALES - BOLETAS - FACTURAS - NOTAS DE VENTAS

    public function obtenerCompra($suc_id,$fec_ini,$fec_fin,$prov_id,$id_almacen){

    	$registros = DB::tABLE('compras_cabecera')
        ->leftjoin('proveedor','compras_cabecera.prov_id','proveedor.prov_id')
        ->join('moneda','compras_cabecera.mon_id','moneda.moncod')
        ->join('tipo_documento','compras_cabecera.tdocod','tipo_documento.tdocod')
        ->where('compras_cabecera.com_fec','>=',$fec_ini)
        ->where('compras_cabecera.com_fec','<=',$fec_fin)
        ->where('est_compra','Registrado')
        ->where(function ($query) use ($suc_id){
            if(!empty($suc_id)){
                $query->where('compras_cabecera.id_empresa_negocio',$suc_id);
            }    
        })
        ->where(function ($query1) use ($prov_id){
            if(!empty($prov_id)){
                 $query1->where('compras_cabecera.prov_id',$prov_id);
            }       
        })              
        ->where(function ($query6) use ($id_almacen) {
            if(!empty($id_almacen)){
                $query6->where('compras_cabecera.id_almacen',$id_almacen);
            }                
        })       
        ->where(function ($query) {
             $query->where('compras_cabecera.tdocod','01')
                    ->orWhere('compras_cabecera.tdocod','03')
                    ->orWhere('compras_cabecera.tdocod','13')
                    ->orWhere('compras_cabecera.tdocod','09')
                    ->orWhere('compras_cabecera.tdocod','15')
                    ->orWhere('compras_cabecera.tdocod','89')
                    ->orWhere('compras_cabecera.tdocod','99');
        }) 
        ->orderby('com_cab_id','desc')
        ->get();


        return $registros;

    }


    public function obtenerCompraDetalle($suc_id,$fec_ini,$fec_fin,$id_almacen,$prov_id){

            $registros = DB::tABLE('compras_cabecera')
            ->leftjoin('compras_detalle','compras_detalle.com_cab_id','compras_cabecera.com_cab_id')
            ->leftjoin('productos','productos.IdProducto','compras_detalle.pro_id')
            ->leftjoin('proveedor','compras_cabecera.prov_id','proveedor.prov_id')
            ->leftjoin('unidad_medida','unidad_medida.umecod','compras_detalle.ume_cod')
            ->join('moneda','compras_cabecera.mon_id','moneda.moncod')
            ->join('tipo_documento','compras_cabecera.tdocod','tipo_documento.tdocod')
            ->where('est_compra','Registrado')
            ->where('compras_cabecera.com_fec','>=',$fec_ini)
            ->where('compras_cabecera.com_fec','<=',$fec_fin)
            ->where(function ($query) use ($suc_id){
                if(!empty($suc_id)){
                    $query->where('compras_cabecera.id_empresa_negocio',$suc_id);
                }    
            })
            ->where(function ($query1) use ($prov_id){
                if(!empty($prov_id)){
                    $query1->where('compras_cabecera.prov_id',$prov_id);
                }       
            })              
            ->where(function ($query6) use ($id_almacen) {
                if(!empty($id_almacen)){
                    $query6->where('compras_cabecera.id_almacen',$id_almacen);
                }                
            })       
            ->where(function ($query) {
             $query->where('compras_cabecera.tdocod','01')
                    ->orWhere('compras_cabecera.tdocod','03')
                    ->orWhere('compras_cabecera.tdocod','13')
                    ->orWhere('compras_cabecera.tdocod','09')
                    ->orWhere('compras_cabecera.tdocod','15')
                    ->orWhere('compras_cabecera.tdocod','89')
                    ->orWhere('compras_cabecera.tdocod','99');
            }) 
            ->orderby('compras_cabecera.com_cab_id','desc')
            ->get();

            return $registros;

    }


      public function obtenerTotalContado($suc_id,$fec_ini,$fec_fin,$id_almacen,$prov_id){

        
         $registros = DB::tABLE('compras_cabecera')
            ->leftjoin('compras_detalle','compras_detalle.com_cab_id','compras_cabecera.com_cab_id')
            ->leftjoin('productos','productos.IdProducto','compras_detalle.pro_id')
            ->leftjoin('proveedor','compras_cabecera.prov_id','proveedor.prov_id')
            ->leftjoin('unidad_medida','unidad_medida.umecod','compras_detalle.ume_cod')
            ->join('moneda','compras_cabecera.mon_id','moneda.moncod')
            ->join('tipo_documento','compras_cabecera.tdocod','tipo_documento.tdocod')
            ->where('est_compra','Registrado')
            ->where('compras_cabecera.com_fec','>=',$fec_ini)
            ->where('compras_cabecera.com_fec','<=',$fec_fin)
            ->where(function ($query) use ($suc_id){
                if(!empty($suc_id)){
                    $query->where('compras_cabecera.id_empresa_negocio',$suc_id);
                }    
            })
            ->where(function ($query1) use ($prov_id){
                if(!empty($prov_id)){
                    $query1->where('compras_cabecera.prov_id',$prov_id);
                }       
            })              
            ->where(function ($query6) use ($id_almacen) {
                if(!empty($id_almacen)){
                    $query6->where('compras_cabecera.id_almacen',$id_almacen);
                }                
            })       
            ->where(function ($query) {
             $query->where('compras_cabecera.tdocod','01')
                    ->orWhere('compras_cabecera.tdocod','03')
                    ->orWhere('compras_cabecera.tdocod','13')
                    ->orWhere('compras_cabecera.tdocod','09')
                    ->orWhere('compras_cabecera.tdocod','15')
                    ->orWhere('compras_cabecera.tdocod','89')
                    ->orWhere('compras_cabecera.tdocod','99');
            }) 
            ->sum('tot_con');
       

        
        return $registros;

    }


    public function obtenerTotalCredito($suc_id,$fec_ini,$fec_fin,$id_almacen,$prov_id){

        $registros = DB::tABLE('compras_cabecera')
        ->leftjoin('compras_detalle','compras_detalle.com_cab_id','compras_cabecera.com_cab_id')
        ->leftjoin('productos','productos.IdProducto','compras_detalle.pro_id')
        ->leftjoin('proveedor','compras_cabecera.prov_id','proveedor.prov_id')
        ->leftjoin('unidad_medida','unidad_medida.umecod','compras_detalle.ume_cod')
        ->join('moneda','compras_cabecera.mon_id','moneda.moncod')
        ->join('tipo_documento','compras_cabecera.tdocod','tipo_documento.tdocod')
        ->where('est_compra','Registrado')
        ->where('compras_cabecera.com_fec','>=',$fec_ini)
        ->where('compras_cabecera.com_fec','<=',$fec_fin)
        ->where(function ($query) use ($suc_id){
            if(!empty($suc_id)){
                $query->where('compras_cabecera.id_empresa_negocio',$suc_id);
            }    
        })
        ->where(function ($query1) use ($prov_id){
            if(!empty($prov_id)){
                $query1->where('compras_cabecera.prov_id',$prov_id);
            }       
        })              
        ->where(function ($query6) use ($id_almacen) {
            if(!empty($id_almacen)){
                $query6->where('compras_cabecera.id_almacen',$id_almacen);
            }                
        })       
        ->where(function ($query) {
            $query->where('compras_cabecera.tdocod','01')
                ->orWhere('compras_cabecera.tdocod','03')
                ->orWhere('compras_cabecera.tdocod','13')
                ->orWhere('compras_cabecera.tdocod','09')
                ->orWhere('compras_cabecera.tdocod','15')
                ->orWhere('compras_cabecera.tdocod','89')
                ->orWhere('compras_cabecera.tdocod','99');
        }) 
        ->sum('tot_cre');

    }


    public function obtenerTotalCompras($suc_id,$fec_ini,$fec_fin,$id_almacen,$prov_id){

        $registros = DB::tABLE('compras_cabecera')
        ->leftjoin('compras_detalle','compras_detalle.com_cab_id','compras_cabecera.com_cab_id')
        ->leftjoin('productos','productos.IdProducto','compras_detalle.pro_id')
        ->leftjoin('proveedor','compras_cabecera.prov_id','proveedor.prov_id')
        ->leftjoin('unidad_medida','unidad_medida.umecod','compras_detalle.ume_cod')
        ->join('moneda','compras_cabecera.mon_id','moneda.moncod')
        ->join('tipo_documento','compras_cabecera.tdocod','tipo_documento.tdocod')
        ->where('est_compra','Registrado')
        ->where('compras_cabecera.com_fec','>=',$fec_ini)
        ->where('compras_cabecera.com_fec','<=',$fec_fin)
        ->where(function ($query) use ($suc_id){
            if(!empty($suc_id)){
                $query->where('compras_cabecera.id_empresa_negocio',$suc_id);
            }    
        })
        ->where(function ($query1) use ($prov_id){
            if(!empty($prov_id)){
                $query1->where('compras_cabecera.prov_id',$prov_id);
            }       
        })              
        ->where(function ($query6) use ($id_almacen) {
            if(!empty($id_almacen)){
                $query6->where('compras_cabecera.id_almacen',$id_almacen);
            }                
        })       
        ->where(function ($query) {
            $query->where('compras_cabecera.tdocod','01')
                ->orWhere('compras_cabecera.tdocod','03')
                ->orWhere('compras_cabecera.tdocod','13')
                ->orWhere('compras_cabecera.tdocod','09')
                ->orWhere('compras_cabecera.tdocod','15')
                ->orWhere('compras_cabecera.tdocod','89');
        }) 
        ->sum('total_com');

        
        return $registros;

    }

  public function obtenerTotalComprasBoletasFacturas($suc_id,$fec_ini,$fec_fin,$id_almacen,$prov_id){

        $registros = DB::tABLE('compras_cabecera')
        ->leftjoin('proveedor','compras_cabecera.prov_id','proveedor.prov_id')
        ->join('moneda','compras_cabecera.mon_id','moneda.moncod')
        ->join('tipo_documento','compras_cabecera.tdocod','tipo_documento.tdocod')
        ->where('compras_cabecera.com_fec','>=',$fec_ini)
        ->where('compras_cabecera.com_fec','<=',$fec_fin)
        ->where('est_compra','Registrado')
        ->where(function ($query) use ($suc_id){
            if(!empty($suc_id)){
                $query->where('compras_cabecera.id_empresa_negocio',$suc_id);
            }    
        })
        ->where(function ($query1) use ($prov_id){
            if(!empty($prov_id)){
                 $query1->where('compras_cabecera.prov_id',$prov_id);
            }       
        })              
        ->where(function ($query6) use ($id_almacen) {
            if(!empty($id_almacen)){
                $query6->where('compras_cabecera.id_almacen',$id_almacen);
            }                
        })       
        ->where(function ($query) {
             $query->where('compras_cabecera.tdocod','01')
                    ->orWhere('compras_cabecera.tdocod','03');
        }) 
        ->sum('total_com');

        return $registros;

    }


     public function obtenerTotalOtrasCompras($suc_id,$fec_ini,$fec_fin,$id_almacen,$prov_id){


        $registros = DB::tABLE('compras_cabecera')
        ->leftjoin('proveedor','compras_cabecera.prov_id','proveedor.prov_id')
        ->join('moneda','compras_cabecera.mon_id','moneda.moncod')
        ->join('tipo_documento','compras_cabecera.tdocod','tipo_documento.tdocod')
        ->where('compras_cabecera.com_fec','>=',$fec_ini)
        ->where('compras_cabecera.com_fec','<=',$fec_fin)
        ->where('est_compra','Registrado')
        ->where(function ($query) use ($suc_id){
            if(!empty($suc_id)){
                $query->where('compras_cabecera.id_empresa_negocio',$suc_id);
            }    
        })
        ->where(function ($query1) use ($prov_id){
            if(!empty($prov_id)){
                 $query1->where('compras_cabecera.prov_id',$prov_id);
            }       
        })              
        ->where(function ($query6) use ($id_almacen) {
            if(!empty($id_almacen)){
                $query6->where('compras_cabecera.id_almacen',$id_almacen);
            }                
        })       
        ->where(function ($query) {
             $query->Where('compras_cabecera.tdocod','13')
                    ->orWhere('compras_cabecera.tdocod','09')
                    ->orWhere('compras_cabecera.tdocod','15')
                    ->orWhere('compras_cabecera.tdocod','89');
        }) 
        ->sum('total_com');

        return $registros;

    }

    public function obtenerValesCompras($suc_id,$fec_ini,$fec_fin,$id_almacen,$prov_id){


        $registros = DB::tABLE('compras_cabecera')
        ->leftjoin('proveedor','compras_cabecera.prov_id','proveedor.prov_id')
        ->join('moneda','compras_cabecera.mon_id','moneda.moncod')
        ->join('tipo_documento','compras_cabecera.tdocod','tipo_documento.tdocod')
        ->where('compras_cabecera.com_fec','>=',$fec_ini)
        ->where('compras_cabecera.com_fec','<=',$fec_fin)
        ->where('est_compra','Registrado')
        ->where(function ($query) use ($suc_id){
            if(!empty($suc_id)){
                $query->where('compras_cabecera.id_empresa_negocio',$suc_id);
            }    
        })
        ->where(function ($query1) use ($prov_id){
            if(!empty($prov_id)){
                 $query1->where('compras_cabecera.prov_id',$prov_id);
            }       
        })              
        ->where(function ($query6) use ($id_almacen) {
            if(!empty($id_almacen)){
                $query6->where('compras_cabecera.id_almacen',$id_almacen);
            }                
        })       
        ->where(function ($query) {
             $query->Where('compras_cabecera.tdocod','99');
        }) 
        ->orderby('com_cab_id','desc')
        ->get();

        return $registros;

    }


      public function obtenerTotalValesCompras($suc_id,$fec_ini,$fec_fin,$id_almacen,$prov_id){

        
        $registros = DB::tABLE('compras_cabecera')
        ->leftjoin('proveedor','compras_cabecera.prov_id','proveedor.prov_id')
        ->join('moneda','compras_cabecera.mon_id','moneda.moncod')
        ->join('tipo_documento','compras_cabecera.tdocod','tipo_documento.tdocod')
        ->where('compras_cabecera.com_fec','>=',$fec_ini)
        ->where('compras_cabecera.com_fec','<=',$fec_fin)
        ->where('est_compra','Registrado')
        ->where(function ($query) use ($suc_id){
            if(!empty($suc_id)){
                $query->where('compras_cabecera.id_empresa_negocio',$suc_id);
            }    
        })
        ->where(function ($query1) use ($prov_id){
            if(!empty($prov_id)){
                 $query1->where('compras_cabecera.prov_id',$prov_id);
            }       
        })              
        ->where(function ($query6) use ($id_almacen) {
            if(!empty($id_almacen)){
                $query6->where('compras_cabecera.id_almacen',$id_almacen);
            }                
        })       
        ->where(function ($query) {
             $query->Where('compras_cabecera.tdocod','99');
        }) 
        ->sum('total_com');

        return $registros;

    }



      public function obtenerIGVCompras($suc_id,$fec_ini,$fec_fin,$id_almacen,$prov_id){

        $registros = DB::tABLE('compras_cabecera')
        ->leftjoin('proveedor','compras_cabecera.prov_id','proveedor.prov_id')
        ->join('moneda','compras_cabecera.mon_id','moneda.moncod')
        ->join('tipo_documento','compras_cabecera.tdocod','tipo_documento.tdocod')
        ->where('compras_cabecera.com_fec','>=',$fec_ini)
        ->where('compras_cabecera.com_fec','<=',$fec_fin)
        ->where('est_compra','Registrado')
        ->where(function ($query) use ($suc_id){
            if(!empty($suc_id)){
                $query->where('compras_cabecera.id_empresa_negocio',$suc_id);
            }    
        })
        ->where(function ($query1) use ($prov_id){
            if(!empty($prov_id)){
                 $query1->where('compras_cabecera.prov_id',$prov_id);
            }       
        })              
        ->where(function ($query6) use ($id_almacen) {
            if(!empty($id_almacen)){
                $query6->where('compras_cabecera.id_almacen',$id_almacen);
            }                
        })       
        ->where(function ($query) {
             $query->where('compras_cabecera.tdocod','01')
                    ->orWhere('compras_cabecera.tdocod','03');
        }) 
        ->sum('igv_com');

        
        return $registros;

    }

    public function obtenerIGVNotasCreditos($suc_id,$fec_ini,$fec_fin,$id_almacen,$prov_id){

         $registros = DB::tABLE('compras_cabecera')
        ->leftjoin('proveedor','compras_cabecera.prov_id','proveedor.prov_id')
        ->join('moneda','compras_cabecera.mon_id','moneda.moncod')
        ->join('tipo_documento','compras_cabecera.tdocod','tipo_documento.tdocod')
        ->where('compras_cabecera.com_fec','>=',$fec_ini)
        ->where('compras_cabecera.com_fec','<=',$fec_fin)
        ->where('est_compra','Registrado')
        ->where(function ($query) use ($suc_id){
            if(!empty($suc_id)){
                $query->where('compras_cabecera.id_empresa_negocio',$suc_id);
            }    
        })
        ->where(function ($query1) use ($prov_id){
            if(!empty($prov_id)){
                 $query1->where('compras_cabecera.prov_id',$prov_id);
            }       
        })              
        ->where(function ($query6) use ($id_almacen) {
            if(!empty($id_almacen)){
                $query6->where('compras_cabecera.id_almacen',$id_almacen);
            }                
        })       
        ->where(function ($query) {
             $query->where('compras_cabecera.tdocod','07');
        }) 
        ->sum('igv_com');


        return $registros;

    }



     public function obtenerTotalNotasCreditos($suc_id,$fec_ini,$fec_fin,$id_almacen,$prov_id){

        $registros = DB::tABLE('compras_cabecera')
        ->leftjoin('proveedor','compras_cabecera.prov_id','proveedor.prov_id')
        ->join('moneda','compras_cabecera.mon_id','moneda.moncod')
        ->join('tipo_documento','compras_cabecera.tdocod','tipo_documento.tdocod')
        ->where('compras_cabecera.com_fec','>=',$fec_ini)
        ->where('compras_cabecera.com_fec','<=',$fec_fin)
        ->where('est_compra','Registrado')
        ->where(function ($query) use ($suc_id){
            if(!empty($suc_id)){
                $query->where('compras_cabecera.id_empresa_negocio',$suc_id);
            }    
        })
        ->where(function ($query1) use ($prov_id){
            if(!empty($prov_id)){
                 $query1->where('compras_cabecera.prov_id',$prov_id);
            }       
        })              
        ->where(function ($query6) use ($id_almacen) {
            if(!empty($id_almacen)){
                $query6->where('compras_cabecera.id_almacen',$id_almacen);
            }                
        })       
        ->where(function ($query) {
             $query->where('compras_cabecera.tdocod','07');
        }) 
        ->sum('total_com');

        
        return $registros;

    }


  
    //VENTAS - FACTURAS
    public function obtenerFactura($suc_id,$fec_ini,$fec_fin,$id_almacen,$prov_id){

    	
    	$registros = DB::tABLE('compras_cabecera')
        ->leftjoin('proveedor','compras_cabecera.prov_id','proveedor.prov_id')
        ->join('moneda','compras_cabecera.mon_id','moneda.moncod')
        ->join('tipo_documento','compras_cabecera.tdocod','tipo_documento.tdocod')
        ->where('compras_cabecera.com_fec','>=',$fec_ini)
        ->where('compras_cabecera.com_fec','<=',$fec_fin)
        ->where('est_compra','Registrado')
        ->where(function ($query) use ($suc_id){
            if(!empty($suc_id)){
                $query->where('compras_cabecera.id_empresa_negocio',$suc_id);
            }    
        })
        ->where(function ($query1) use ($prov_id){
            if(!empty($prov_id)){
                 $query1->where('compras_cabecera.prov_id',$prov_id);
            }       
        })              
        ->where(function ($query6) use ($id_almacen) {
            if(!empty($id_almacen)){
                $query6->where('compras_cabecera.id_almacen',$id_almacen);
            }                
        })       
        ->where(function ($query) {
             $query->where('compras_cabecera.tdocod','01');
        }) 
        ->orderby('com_cab_id','desc')
        ->get();

        return $registros;

    }


    //VENTAS - BOLETAS
    public function obtenerBoleta($suc_id,$fec_ini,$fec_fin,$id_almacen,$prov_id){

        $registros = DB::tABLE('compras_cabecera')
        ->leftjoin('proveedor','compras_cabecera.prov_id','proveedor.prov_id')
        ->join('moneda','compras_cabecera.mon_id','moneda.moncod')
        ->join('tipo_documento','compras_cabecera.tdocod','tipo_documento.tdocod')
        ->where('compras_cabecera.com_fec','>=',$fec_ini)
        ->where('compras_cabecera.com_fec','<=',$fec_fin)
        ->where('est_compra','Registrado')
        ->where(function ($query) use ($suc_id){
            if(!empty($suc_id)){
                $query->where('compras_cabecera.id_empresa_negocio',$suc_id);
            }    
        })
        ->where(function ($query1) use ($prov_id){
            if(!empty($prov_id)){
                 $query1->where('compras_cabecera.prov_id',$prov_id);
            }       
        })              
        ->where(function ($query6) use ($id_almacen) {
            if(!empty($id_almacen)){
                $query6->where('compras_cabecera.id_almacen',$id_almacen);
            }                
        })       
        ->where(function ($query) {
             $query->where('compras_cabecera.tdocod','03');
        }) 
        ->orderby('com_cab_id','desc')
        ->get();

        return $registros;

    }

    //OBTENER NOTAS DE VENTAS
    public function obtenerOtrasCompras($suc_id,$fec_ini,$fec_fin,$id_almacen,$prov_id){

    	  $registros = DB::tABLE('compras_cabecera')
        ->leftjoin('proveedor','compras_cabecera.prov_id','proveedor.prov_id')
        ->join('moneda','compras_cabecera.mon_id','moneda.moncod')
        ->join('tipo_documento','compras_cabecera.tdocod','tipo_documento.tdocod')
        ->where('compras_cabecera.com_fec','>=',$fec_ini)
        ->where('compras_cabecera.com_fec','<=',$fec_fin)
        ->where('est_compra','Registrado')
        ->where(function ($query) use ($suc_id){
            if(!empty($suc_id)){
                $query->where('compras_cabecera.id_empresa_negocio',$suc_id);
            }    
        })
        ->where(function ($query1) use ($prov_id){
            if(!empty($prov_id)){
                 $query1->where('compras_cabecera.prov_id',$prov_id);
            }       
        })              
        ->where(function ($query6) use ($id_almacen) {
            if(!empty($id_almacen)){
                $query6->where('compras_cabecera.id_almacen',$id_almacen);
            }                
        })       
        ->where(function ($query) {
             $query->Where('compras_cabecera.tdocod','13')
                    ->orWhere('compras_cabecera.tdocod','09')
                    ->orWhere('compras_cabecera.tdocod','15')
                    ->orWhere('compras_cabecera.tdocod','89');
        }) 
        ->orderby('com_cab_id','desc')
        ->get();

        return $registros;

    }


    //OBTENER NOTAS DE CRÉDITOS
    public function obtenerNotaCredito($suc_id,$fec_ini,$fec_fin,$id_almacen,$prov_id){

    	$registros = DB::tABLE('compras_cabecera')
        ->leftjoin('proveedor','compras_cabecera.prov_id','proveedor.prov_id')
        ->join('moneda','compras_cabecera.mon_id','moneda.moncod')
        ->join('tipo_documento','compras_cabecera.tdocod','tipo_documento.tdocod')
        ->where('compras_cabecera.com_fec','>=',$fec_ini)
        ->where('compras_cabecera.com_fec','<=',$fec_fin)
        ->where('est_compra','Registrado')
        ->where(function ($query) use ($suc_id){
            if(!empty($suc_id)){
                $query->where('compras_cabecera.id_empresa_negocio',$suc_id);
            }    
        })
        ->where(function ($query1) use ($prov_id){
            if(!empty($prov_id)){
                 $query1->where('compras_cabecera.prov_id',$prov_id);
            }       
        })              
        ->where(function ($query6) use ($id_almacen) {
            if(!empty($id_almacen)){
                $query6->where('compras_cabecera.id_almacen',$id_almacen);
            }                
        })       
        ->where(function ($query) {
             $query->Where('compras_cabecera.tdocod','07');
        }) 
        ->orderby('com_cab_id','desc')
        ->get();

        return $registros;
        
    }


    public function obtenerResumenComprasProductos($suc_id,$fec_ini,$fec_fin,$id_almacen,$prov_id){

            $registros = DB::tABLE('compras_cabecera')->select(DB::RAW('sum(compras_detalle.cantidad*com_det_factor) as cantidad'),'pronom',DB::RAW('sum(compras_detalle.total) as precio'),'procod','ume_cod')
            ->leftjoin('compras_detalle','compras_detalle.com_cab_id','compras_cabecera.com_cab_id')
            ->leftjoin('productos','productos.IdProducto','compras_detalle.pro_id')
            ->leftjoin('proveedor','compras_cabecera.prov_id','proveedor.prov_id')
            ->leftjoin('unidad_medida','unidad_medida.umecod','compras_detalle.ume_cod')
            ->join('moneda','compras_cabecera.mon_id','moneda.moncod')
            ->join('tipo_documento','compras_cabecera.tdocod','tipo_documento.tdocod')
            ->where('est_compra','Registrado')
            ->where('compras_cabecera.com_fec','>=',$fec_ini)
            ->where('compras_cabecera.com_fec','<=',$fec_fin)
            ->where(function ($query) use ($suc_id){
                if(!empty($suc_id)){
                    $query->where('compras_cabecera.id_empresa_negocio',$suc_id);
                }    
            })
            ->where(function ($query1) use ($prov_id){
                if(!empty($prov_id)){
                    $query1->where('compras_cabecera.prov_id',$prov_id);
                }       
            })              
            ->where(function ($query6) use ($id_almacen) {
                if(!empty($id_almacen)){
                    $query6->where('compras_cabecera.id_almacen',$id_almacen);
                }                
            })       
            ->where(function ($query) {
             $query->Where('compras_cabecera.tdocod','13')
                    ->orWhere('compras_cabecera.tdocod','09')
                    ->orWhere('compras_cabecera.tdocod','15')
                    ->orWhere('compras_cabecera.tdocod','89');
            }) 
            ->orderby('compras_cabecera.com_cab_id','desc')
            ->get();

            return $registros;

    }

        public function obtenerResumenComprasBoletasFacturasProductos($suc_id,$fec_ini,$fec_fin,$id_almacen,$prov_id){

            $registros = DB::tABLE('compras_cabecera')->select(DB::RAW('sum(compras_detalle.cantidad*com_det_factor) as cantidad'),'pronom',DB::RAW('sum(compras_detalle.total) as precio'),'procod','ume_cod')
            ->leftjoin('compras_detalle','compras_detalle.com_cab_id','compras_cabecera.com_cab_id')
            ->leftjoin('productos','productos.IdProducto','compras_detalle.pro_id')
            ->leftjoin('proveedor','compras_cabecera.prov_id','proveedor.prov_id')
            ->leftjoin('unidad_medida','unidad_medida.umecod','compras_detalle.ume_cod')
            ->join('moneda','compras_cabecera.mon_id','moneda.moncod')
            ->join('tipo_documento','compras_cabecera.tdocod','tipo_documento.tdocod')
            ->where('est_compra','Registrado')
            ->where('compras_cabecera.com_fec','>=',$fec_ini)
            ->where('compras_cabecera.com_fec','<=',$fec_fin)
            ->where(function ($query) use ($suc_id){
                if(!empty($suc_id)){
                    $query->where('compras_cabecera.id_empresa_negocio',$suc_id);
                }    
            })
            ->where(function ($query1) use ($prov_id){
                if(!empty($prov_id)){
                    $query1->where('compras_cabecera.prov_id',$prov_id);
                }       
            })              
            ->where(function ($query6) use ($id_almacen) {
                if(!empty($id_almacen)){
                    $query6->where('compras_cabecera.id_almacen',$id_almacen);
                }                
            })       
            ->where(function ($query) {
             $query->where('compras_cabecera.tdocod','01')
                    ->orWhere('compras_cabecera.tdocod','03');
            }) 
            ->orderby('compras_cabecera.com_cab_id','desc')
            ->get();

            return $registros;

    }




    public function obtenerResumenNotasCreditosProductos($suc_id,$fec_ini,$fec_fin,$id_almacen,$prov_id){

                    $registros = DB::tABLE('compras_cabecera')->select(DB::RAW('sum(compras_detalle.cantidad*com_det_factor) as cantidad'),'pronom',DB::RAW('sum(compras_detalle.total) as precio'),'procod','ume_cod')
            ->leftjoin('compras_detalle','compras_detalle.com_cab_id','compras_cabecera.com_cab_id')
            ->leftjoin('productos','productos.IdProducto','compras_detalle.pro_id')
            ->leftjoin('proveedor','compras_cabecera.prov_id','proveedor.prov_id')
            ->leftjoin('unidad_medida','unidad_medida.umecod','compras_detalle.ume_cod')
            ->join('moneda','compras_cabecera.mon_id','moneda.moncod')
            ->join('tipo_documento','compras_cabecera.tdocod','tipo_documento.tdocod')
            ->where('est_compra','Registrado')
            ->where('compras_cabecera.com_fec','>=',$fec_ini)
            ->where('compras_cabecera.com_fec','<=',$fec_fin)
            ->where(function ($query) use ($suc_id){
                if(!empty($suc_id)){
                    $query->where('compras_cabecera.id_empresa_negocio',$suc_id);
                }    
            })
            ->where(function ($query1) use ($prov_id){
                if(!empty($prov_id)){
                    $query1->where('compras_cabecera.prov_id',$prov_id);
                }       
            })              
            ->where(function ($query6) use ($id_almacen) {
                if(!empty($id_almacen)){
                    $query6->where('compras_cabecera.id_almacen',$id_almacen);
                }                
            })       
            ->where(function ($query) {
             $query->where('compras_cabecera.tdocod','07');
            }) 
            ->orderby('compras_cabecera.com_cab_id','desc')
            ->get();


            return $registros;

    }

     public function obtenerResumenOtrasComprasProductos($suc_id,$fec_ini,$fec_fin,$id_almacen,$prov_id){

            $registros = DB::tABLE('compras_cabecera')->select(DB::RAW('sum(compras_detalle.cantidad*com_det_factor) as cantidad'),'pronom',DB::RAW('sum(compras_detalle.total) as precio'),'procod','ume_cod')
            ->leftjoin('compras_detalle','compras_detalle.com_cab_id','compras_cabecera.com_cab_id')
            ->leftjoin('productos','productos.IdProducto','compras_detalle.pro_id')
            ->leftjoin('proveedor','compras_cabecera.prov_id','proveedor.prov_id')
            ->leftjoin('unidad_medida','unidad_medida.umecod','compras_detalle.ume_cod')
            ->join('moneda','compras_cabecera.mon_id','moneda.moncod')
            ->join('tipo_documento','compras_cabecera.tdocod','tipo_documento.tdocod')
            ->where('est_compra','Registrado')
            ->where('compras_cabecera.com_fec','>=',$fec_ini)
            ->where('compras_cabecera.com_fec','<=',$fec_fin)
            ->where(function ($query) use ($suc_id){
                if(!empty($suc_id)){
                    $query->where('compras_cabecera.id_empresa_negocio',$suc_id);
                }    
            })
            ->where(function ($query1) use ($prov_id){
                if(!empty($prov_id)){
                    $query1->where('compras_cabecera.prov_id',$prov_id);
                }       
            })              
            ->where(function ($query6) use ($id_almacen) {
                if(!empty($id_almacen)){
                    $query6->where('compras_cabecera.id_almacen',$id_almacen);
                }                
            })       
            ->where(function ($query) {
             $query->where('compras_cabecera.tdocod','01')
                    ->orWhere('compras_cabecera.tdocod','03')
                    ->orWhere('compras_cabecera.tdocod','13')
                    ->orWhere('compras_cabecera.tdocod','09')
                    ->orWhere('compras_cabecera.tdocod','15')
                    ->orWhere('compras_cabecera.tdocod','89');
            }) 
            ->orderby('compras_cabecera.com_cab_id','desc')
            ->get();


            return $registros;

    }

     public function obtenerResumenValesComprasProductos($suc_id,$fec_ini,$fec_fin,$id_almacen,$prov_id){

            $registros = DB::tABLE('compras_cabecera')->select(DB::RAW('sum(compras_detalle.cantidad*com_det_factor) as cantidad'),'pronom',DB::RAW('sum(compras_detalle.total) as precio'),'procod','ume_cod')
            ->leftjoin('compras_detalle','compras_detalle.com_cab_id','compras_cabecera.com_cab_id')
            ->leftjoin('productos','productos.IdProducto','compras_detalle.pro_id')
            ->leftjoin('proveedor','compras_cabecera.prov_id','proveedor.prov_id')
            ->leftjoin('unidad_medida','unidad_medida.umecod','compras_detalle.ume_cod')
            ->join('moneda','compras_cabecera.mon_id','moneda.moncod')
            ->join('tipo_documento','compras_cabecera.tdocod','tipo_documento.tdocod')
            ->where('est_compra','Registrado')
            ->where('compras_cabecera.com_fec','>=',$fec_ini)
            ->where('compras_cabecera.com_fec','<=',$fec_fin)
            ->where(function ($query) use ($suc_id){
                if(!empty($suc_id)){
                    $query->where('compras_cabecera.id_empresa_negocio',$suc_id);
                }    
            })
            ->where(function ($query1) use ($prov_id){
                if(!empty($prov_id)){
                    $query1->where('compras_cabecera.prov_id',$prov_id);
                }       
            })              
            ->where(function ($query6) use ($id_almacen) {
                if(!empty($id_almacen)){
                    $query6->where('compras_cabecera.id_almacen',$id_almacen);
                }                
            })       
            ->where(function ($query) {
                $query->Where('compras_cabecera.tdocod','99');
            }) 
            ->orderby('compras_cabecera.com_cab_id','desc')
            ->get();


            return $registros;

    }


    public function obtenerResumenComprasProveedor($suc_id,$fec_ini,$fec_fin,$id_almacen,$prov_id){

   
        $registros = DB::tABLE('compras_cabecera')->select('compras_cabecera.com_cab_id','proveedor.prov_raz','proveedor.prov_ruc',DB::RAW('sum(total_com) as total'))
        ->leftjoin('proveedor','compras_cabecera.prov_id','proveedor.prov_id')
        ->join('moneda','compras_cabecera.mon_id','moneda.moncod')
        ->join('tipo_documento','compras_cabecera.tdocod','tipo_documento.tdocod')
        ->where('compras_cabecera.com_fec','>=',$fec_ini)
        ->where('compras_cabecera.com_fec','<=',$fec_fin)
        ->where('est_compra','Registrado')
        ->where(function ($query) use ($suc_id){
            if(!empty($suc_id)){
                $query->where('compras_cabecera.id_empresa_negocio',$suc_id);
            }    
        })
        ->where(function ($query1) use ($prov_id){
            if(!empty($prov_id)){
                 $query1->where('compras_cabecera.prov_id',$prov_id);
            }       
        })              
        ->where(function ($query6) use ($id_almacen) {
            if(!empty($id_almacen)){
                $query6->where('compras_cabecera.id_almacen',$id_almacen);
            }                
        })       
        ->where(function ($query) {
             $query->where('compras_cabecera.tdocod','01');
        }) 
        ->orderby('com_cab_id','desc')
        ->groupby('compras_cabecera.prov_id')
        ->orderby('prov_raz')
        ->get();

     
        return $registros;

    }

   

}
