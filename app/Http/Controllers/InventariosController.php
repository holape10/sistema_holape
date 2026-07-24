<?php

namespace MasterSoft\Http\Controllers;

use Illuminate\Http\Request;
use MasterSoft\productos;
use MasterSoft\movimientos;
use MasterSoft\presentaciones;
use MasterSoft\inventario_cabecera;
use MasterSoft\recetas;
use MasterSoft\combos;
use MasterSoft\marcas;
use MasterSoft\Proveedor;
use MasterSoft\EmpresaNegocios;
use MasterSoft\modelos\Almacen;
use MasterSoft\Http\Requests\ProductoCreateFormRequest;
use MasterSoft\Http\Requests\ProductoUpdateFormRequest;
use MasterSoft\Http\Requests;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Auth;
use MasterSoft\Empresa;
use DB;
use Excel;

class InventariosController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

      public function __construct()
    {
        $this->middleware('auth');
    }


     



     public function inventarios(Request $request)
    {   
   

          $sucursal = $request->get('sucursal');
          $almacen = $request->get('almacen');
          $fec_ini = $request->get('fec_ini');
          $fec_fin = $request->get('fec_fin');
          $rucemp = trim(Auth::user()->IdEmpresa);
       

          $negocios = DB::tABLE('empresa_negocios')->get();
          $almacenes = DB::tABLE('almacenes')->where('id_empresa_negocio',$negocios->first()->id_empresa_negocio)->get();

          $dat_neg = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',$sucursal)->first();
          $dat_alm = DB::tABLE('almacenes')->where('id_almacen',$almacen)->first();

          if(empty($fec_ini)){
            $fec_ini = now()->modify('first day of this month')->format('Y-m-d');
            $fec_fin = now()->modify('last day of this month')->format('Y-m-d');
          }

        
            
      
            $inventarios = DB::tABLE('inventario_cabecera')
              	->leftjoin('users','users.IdUsuario','inventario_cabecera.IdUsuario')
                ->leftjoin('almacenes','almacenes.id_almacen','inventario_cabecera.id_almacen')
                ->leftjoin('empresa_negocios','empresa_negocios.id_empresa_negocio','almacenes.id_empresa_negocio')
		        ->where(function ($query) use ($sucursal) {
			        if(!empty($sucursal) || $sucursal!='Todos'){
			            $query->Where('inventario_cabecera.id_empresa_negocio','=',$sucursal);
			        }
		        })
		        ->where(function ($query) use ($almacen) {
			        if(!empty($almacen) || $almacen!='Todos'){
			            $query->Where('inventario_cabecera.id_almacen','=',$almacen);
			        }
		        })
                ->where('inv_fec','>=',$fec_ini)
                ->where('inv_fec','<=',$fec_fin)
                ->orderby('inv_fec','desc')
                ->get();

        
        

            return view('empresas.inventarios.index',compact('dat_alm','negocios','sucursal','dat_neg','almacenes','almacen','dat_alm','fec_ini','fec_fin','inventarios'));
      

    }

    public function nuevoinventario(Request $request){

    	  $fecha = $request->get('fecha_nue_inv');
    	  $sucursal = $request->get('suc_nue_inv');
    	  $almacen = $request->get('alm_nue_inv');

          $dat_suc = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',$sucursal)->first();
          $dat_alm = DB::tABLE('almacenes')->where('id_almacen',$almacen)->first();

          $inventario = new inventario_cabecera;
          $inventario->inv_fec = $fecha;
          $inventario->id_empresa_negocio = $sucursal;
          $inventario->id_almacen = $almacen;
          $inventario->IdUsuario = Auth::user()->IdUsuario;
          $inventario->save();

          $inv_cab_id = $inventario->inv_cab_id;

          return Redirect::to('/ingresarinventario/'.$inv_cab_id);

         

    }

    public function ingresar_inventario(Request $request,$inv_cab_id){

    	  $inventario = inventario_cabecera::findOrFail($inv_cab_id);

    	  $fecha = $inventario->inv_fec;
    	  $sucursal = $inventario->id_empresa_negocio;
    	  $almacen = $inventario->id_almacen;

          $dat_suc = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',$sucursal)->first();
          $dat_alm = DB::tABLE('almacenes')->where('id_almacen',$almacen)->first();

          $detalle = DB::tABLE('inventario_detalle')
          ->join('productos','productos.IdProducto','inventario_detalle.IdProducto')
          ->where('inv_cab_id',$inv_cab_id)
          ->get();
         
    	 return view('empresas.inventarios.nuevoinventario',compact('fecha','sucursal','almacen','dat_suc','dat_alm','inv_cab_id','detalle'));

    }


  


    public function inventario_registrar_producto(Request $request,$inv_cab_id,$id_producto,$inv_can){

      $bus_pro = DB::tABLE('productos')->where('IdProducto',$id_producto)->first();

      $inventario = DB::tABLE('inventario_cabecera')->where('inv_cab_id',$inv_cab_id)->first();

      if(empty($bus_pro->pro_rel)){
        $id = $bus_pro->IdProducto;
      }else{
        $id = $bus_pro->pro_rel;
      }


      $bus_pro_det = DB::tABLE('inventario_detalle')
      ->where('inv_cab_id',$inv_cab_id)
      ->where('IdProducto',$id_producto)
      ->first();

      if(empty($bus_pro_det)){
        DB::tABLE('inventario_detalle')
          ->insert(['inv_cab_id'=>$inv_cab_id,
            'IdProducto'=>$id_producto,
            'inv_can'=>$inv_can
            //'inv_costo'=>$costo[$key]
          ]);
      }else{
        DB::tABLE('inventario_detalle')
        ->where('inv_det_id',$bus_pro_det->inv_det_id)
          ->update(['inv_cab_id'=>$inv_cab_id,
            'IdProducto'=>$id_producto,
            'inv_can'=>$inv_can
            //'inv_costo'=>$costo[$key]
          ]);
      }
       
        // ========== CÁLCULO DE STOCK CON EQUIVALENCIA ==========
        
        // Stock en unidad principal
        $stock_principal = $inv_can * $bus_pro->factor;
        
        // Stock en unidad equivalente
        $stock_equivalencia = 0;
        if(!empty($bus_pro->factor_cons) && $bus_pro->factor_cons > 0){
            $stock_equivalencia = $stock_principal * $bus_pro->factor_cons;
        }

        // ========== ACTUALIZAR PRODUCTO_STOCK ==========
        DB::tABLE('producto_stock')
        ->where('IdProducto',$id)
        ->where('id_almacen',$inventario->id_almacen)
        ->update([
            'stock' => $stock_principal,
            'stock_equivalencia' => $stock_equivalencia
        ]);

        // ========== REGISTRAR EN MOVIMIENTOS_PRODUCTOS ==========
        DB::tABLE('movimientos_productos')->where('inv_cab_id',$inv_cab_id)->where('IdProducto',$id_producto)->delete();

         DB::tABLE('movimientos_productos')->insert([

                    'IdProducto'=>$id_producto,
                    'precio'=>'0',
                    'cantidad'=>$stock_principal,
                    'cantidad_equivalente'=>$stock_equivalencia,  // ← NUEVO
                    'costo'=>$bus_pro->costo,
                    'descripcion'=>'STOCK_INICIAL',
                    'cod_tip_ope'=>'16',
                    'mov_cab_id'=>'',
                    'stock'=>'',
                    'stock_equivalente'=>$stock_equivalencia,     // ← NUEVO
                    'IdProducto_rel'=>$id,
                    'IdCpe_cabecera'=>'',
                    'com_cab_id'=>'',
                    'stock_inicial'=>'',
                    'serie'=>'',
                    'numero'=>'',
                    'tdocod'=>'',
                    'tipo'=>'1',
                    'mov_tip'=>'I',
                    'inv_cab_id'=>$inv_cab_id,
                    'id_empresa_negocio'=>$inventario->id_empresa_negocio,
                    'id_almacen'=>$inventario->id_almacen,
                    'fecha_mov'=>$inventario->inv_fec,
                   

            ]); 

          $mov_cal_stock = new Almacen();
          $mov_cal_stock->movimiento_calcular_stock($id,$inventario->id_almacen);
    
      if($request->ajax()){
         return response()->json(['estado'=>'Producto Registrado en el Inventario']);

       }

    }


	public function inventario_eliminar_producto(Request $request,$inv_cab_id,$id_producto,$inv_can){


  		$bus_pro = DB::tABLE('productos')->where('IdProducto',$id_producto)->first();

  		$inventario = DB::tABLE('inventario_cabecera')->where('inv_cab_id',$inv_cab_id)->first();

  		if(empty($bus_pro->pro_rel)){
  			$id = $bus_pro->IdProducto;
  		}else{
  			$id = $bus_pro->pro_rel;
  		}


  		$bus_pro_det = DB::tABLE('inventario_detalle')
  		->where('inv_cab_id',$inv_cab_id)
  		->where('IdProducto',$id_producto)
  		->delete();

       
        DB::tABLE('producto_stock')
        ->where('IdProducto',$id)
        ->where('id_almacen',$inventario->id_almacen)
        ->update(['stock'=>'0']);

        DB::tABLE('movimientos_productos')->where('inv_cab_id',$inv_cab_id)->where('IdProducto',$id_producto)->delete();

        $mov_cal_stock = new Almacen();
        $mov_cal_stock->movimiento_calcular_stock($id,now());
    
    
      if($request->ajax()){
         return response()->json(['estado'=>'Producto Eliminado del Inventario - Stock Actual ="0"']);

       }

    }

  

//-----------------------------------------------------------------------------------------------//



    public function actualizarinventario(Request $request){

      $sucursal = $request->get('suc_id');
      $almacen = $request->get('alm_id');
      $fecha = $request->get('inv_fec');
      $productos = $request->get('id');
      $stock = $request->get('cant');
      $costo = $request->get('costo');
      $inv_cab_id = $request->get('inv_cab_id');

      DB::tABLE('inventario_detalle')->where('inv_cab_id',$inv_cab_id)->delete();
  


      foreach ($productos as $key => $pro) {
        
        $buscar_producto = DB::tABLE('productos')
        ->where('IdProducto',$pro)
        ->first();

        if(empty($buscar_producto->pro_rel)){

          $id_pro = $buscar_producto->IdProducto;

        }else{

           $id_pro = $buscar_producto->pro_rel;

        }

        $bus_sto = DB::tABLE('producto_stock')
        ->where('id_empresa_negocio',$sucursal)
        ->where('id_almacen',$almacen)
        ->where('IdProducto',$id_pro)
        ->first();


        if(!empty($bus_sto)){

          DB::tABLE('producto_stock')
          ->where('id_empresa_negocio',$sucursal)
          ->where('id_almacen',$almacen)
          ->where('IdProducto',$id_pro)
          ->update([
            'stock'=>$stock[$key],
            'stock_inicial'=>$stock[$key]

          ]);

        }else{

          DB::tABLE('producto_stock')
          ->insert(['stock'=>$stock[$key],
                    'stock_inicial'=>$stock[$key],
                    'id_empresa_negocio'=>$sucursal,
                    'id_almacen'=>$almacen,
                    'IdProducto'=>$id_pro
          ]);

        }
        
      


        DB::tABLE('inventario_detalle')
        ->insert(['inv_cab_id'=>$inv_cab_id,
          'IdProducto'=>$id_pro,
          'inv_can'=>$stock[$key],
          'inv_costo'=>$costo[$key]
        ]);


      }

      return Redirect::to('/inventarios');

    }
    
     public function editarinventario($id){

        $cabecera = DB::tABLE('inventario_cabecera')->where('inv_cab_id',$id)->first();

         $negocios = DB::tABLE('empresa_negocios')->where('IdEmpresa',Auth::user()->IdEmpresa)->get();

         $sucursal = $cabecera->id_empresa_negocio;

          $almacenes = DB::tABLE('almacenes')->where('id_empresa_negocio',$sucursal)->get();

          $almacen = $cabecera->id_almacen;

          $fecha = $cabecera->inv_fec;

          $tipo ='2';

        $productoslista = DB::tABLE('inventario_detalle')
        ->join('productos','productos.IdProducto','inventario_detalle.IdProducto')
        ->where('inv_cab_id',$id)
        ->get();

          return view('empresas.productos.inventarioseditar',compact('buspro','negocios','categorias','tipos_productos','categoria','tipo','sucursal','datos','almacenes','almacen','datosalm','fecha','unidades','productoslista','id'));


    }



}
