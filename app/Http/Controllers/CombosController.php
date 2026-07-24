<?php

namespace MasterSoft\Http\Controllers;

use Illuminate\Http\Request;
use MasterSoft\productos;
use MasterSoft\movimientos;
use MasterSoft\presentaciones;
use MasterSoft\recetas;
use MasterSoft\combos;
use MasterSoft\marcas;
use MasterSoft\Proveedor;
use MasterSoft\EmpresaNegocios;
use MasterSoft\Http\Requests\ProductoCreateFormRequest;
use MasterSoft\Http\Requests\ProductoUpdateFormRequest;
use MasterSoft\Http\Requests;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Auth;
use MasterSoft\Empresa;
use DB;
use Excel;
use PDF;

class CombosController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

      public function __construct()
    {
        $this->middleware('auth')->except(['consultastockproductos']);
    }


         public function historialproducto($id){

        $detalle = DB::tABLE('cpe_cabecera')
        ->leftjoin('cpe_detalle','cpe_detalle.IdCpe_cabecera','cpe_cabecera.IdCpe_cabecera')
        ->leftjoin('cliente','cliente.clicod','cpe_cabecera.clicod')
        ->leftjoin('productos','productos.IdProducto','cpe_detalle.IdProducto')
        ->leftjoin('unidad_medida','unidad_medida.umecod','productos.umecod')
          ->leftjoin('marcas','marcas.mar_id','productos.marca')
        ->where('cpe_detalle.IdProducto',$id)->paginate(1000);

        $producto = DB::tABLE('productos')->where('IdProducto',$id)->first();

        return  view('empresas.combos.historial',compact('detalle','producto'));

     }


    public function generarcodigo(){

        $rucemp = trim(Auth::user()->IdEmpresa);
        $rutabarcode = '/opt/data/comprobantes/'.$rucemp.'/barras/';

        $d = new DNS1D();
        $d1 = new DNS2D();
        $d->setStorPath($rutabarcode);
        echo $d->getBarcodePNGPath("6971636851325", "EAN13");

        $archivo = fopen($rutabarcode.'6971636851325.png', "a");
        fputs($archivo,$d->getBarcodePNGPath("6971636851325", "EAN13"));
        fclose($archivo);

    }


    public function actualizarpro(Request $request, $tipo){
       
       if($tipo=='venta'){
         $vista = view('empresas.combos.actualizarpro')->render();
       }elseif($tipo=='compra'){
        $vista = view('empresas.combos.actualizarprocompra')->render();
       }elseif($tipo=='servicio'){
        $vista = view('empresas.combos.actualizarproservicio')->render();
       }
        

        if($request->ajax()){
         return response()->json(['vista'=>$vista]);

        }
    }


    public function index(Request $request)
    {



        $rucemp = trim(Auth::user()->IdEmpresa);
        $buspro = trim($request->get('buspro'));
        $tipo = $request->get('promocion');
        $categoria = $request->get('cmbCatId');
        $subcategoria = $request->get('subcat_id');
        $tip_pro_id = $request->get('tip_pro_id');

        $categorias = DB::tABLE('categorias')->get();
        $subcategorias = DB::tABLE('subcategorias')->get();
        $tipos = DB::tABLE('tipo_producto')->get();

        $sucursales = DB::tABLE('empresa_negocios')->get();
        $sucursal = $request->get('sucursal');

        if(empty($sucursal)){
          $sucursal = $sucursales->first()->id_empresa_negocio;
        }
        $tipos_productos = DB::tABLE('tipos_productos')->get();
           
        if(is_null($categoria)){
        

        $productos = DB::tABLE('productos as p')
        ->select('lote','vencimiento','ma.mar_nom','p.procod','p.pronom','m.monnom','u.umenom','p.propun','p.proest','p.IdProducto','p.propun','modelo','cat_nom','imagenproducto','promocion','subcat_nom','tip_pro_nom','precio','codigo_barra')
        ->leftjoin('moneda as m','p.moncod','=','m.moncod') 
         ->leftjoin('marcas as ma','ma.mar_id','=','p.marca') 
        ->leftjoin('categorias as cat','cat.cat_id','=','p.cat_id')
        ->leftjoin('subcategorias as subcat','subcat.subcat_id','=','p.subcat_id')
        ->leftjoin('tipo_producto as tp','tp.tip_pro_id','=','p.tip_pro_id')
        ->leftjoin('unidad_medida as u','p.umecod','=','u.umecod')
        ->leftjoin('producto_codigo as pc','pc.IdProducto','p.IdProducto')
        ->leftjoin('producto_empresa','producto_empresa.IdProducto','p.IdProducto')
        ->where('producto_empresa.id_empresa_negocio',$sucursal)
        ->where('p.promocion','3')
        ->groupby('p.IdProducto')
        ->orderby('cat_nom','asc')
        ->orderby('pronom','asc')
        ->paginate(150);

      }else{


         $productos = DB::tABLE('productos as p')
        ->select('lote','vencimiento','ma.mar_nom','p.procod','p.pronom','m.monnom','u.umenom','p.propun','p.proest','p.IdProducto','p.propun','modelo','cat_nom','imagenproducto','promocion','subcat_nom','tip_pro_nom','precio','codigo_barra')
        ->leftjoin('moneda as m','p.moncod','=','m.moncod') 
        ->leftjoin('categorias as cat','cat.cat_id','=','p.cat_id')
              ->leftjoin('marcas as ma','ma.mar_id','=','p.marca') 
        ->leftjoin('subcategorias as subcat','subcat.subcat_id','=','p.subcat_id')
        ->leftjoin('tipo_producto as tp','tp.tip_pro_id','=','p.tip_pro_id')
        ->leftjoin('unidad_medida as u','p.umecod','=','u.umecod')
        ->leftjoin('producto_codigo as pc','pc.IdProducto','p.IdProducto')
        ->leftjoin('producto_empresa','producto_empresa.IdProducto','p.IdProducto')
        ->where(function ($query) use ($buspro) {
          if(!empty($buspro)){
             $query->Where('pronom','like','%'.$buspro.'%')
             ->orWhere('codigo_barra',$buspro)
             ->orWhere('procod',$buspro);
          }
        })
        ->where(function ($query1) use ($categoria) {
          if($categoria !='0'){
             $query1->Where('p.cat_id',$categoria);
          }
        })
        ->where(function ($query2) use ($subcategoria) {
          if($subcategoria !='0'){
             $query2->Where('p.subcat_id',$subcategoria);
          }
        })
         ->where(function ($query3) use ($tip_pro_id) {
          if($tip_pro_id !='0'){
             $query3->Where('p.tip_pro_id',$tip_pro_id);
          }
        })
        ->where('producto_empresa.id_empresa_negocio',$sucursal)
        ->where('p.promocion','3')
        ->groupby('p.IdProducto')
        ->orderby('cat_nom','asc')
        ->orderby('pronom','asc')
        ->paginate(150);
      }

      $data_suc = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',$sucursal)->first();
       
        return view('empresas.combos.index',compact('productos','buspro','categorias','tipos_productos','tipos','subcategorias','subcategoria','tip_pro_id','categoria','sucursales','sucursal','data_suc'));
         

    }

       public function asignarreceta($prod_id){

      $insumos = DB::tABLE('productos')
      ->where('promocion','4')
       ->where('tipo','1')
      //->where('productos.IdEmpresa',Auth::user()->IdEmpresa)
      ->leftjoin('unidad_medida as cum','cum.umecod','productos.umecod')
      ->get();

      $receta = DB::tABLE('recetas as tbc')
      ->join('productos as tbp','tbp.IdProducto','tbc.prod_insu')
      ->leftjoin('unidad_medida as cum','cum.umecod','tbp.umecod')
      ->where('tbc.prod_id',$prod_id)
      ->get();

      $producto = DB::tABLE('productos')->where('IdProducto',$prod_id)->first();

      
      return view('empresas.combos.recetas',compact('insumos','prod_id','producto','receta'));

    }

     public function registrarreceta(Request $request){

      $insumo = $request->get('prod_ins_id');
      $cantidad = $request->get('cantidad');
      $prod_id =$request->get('producto');

      recetas::where('prod_id',$prod_id)->delete();

      if(!empty($insumo)){
         foreach ($insumo as $index => $insu) {
     
             $receta = new recetas;
             $receta->prod_insu = $insu;
             $receta->prod_id = $prod_id;
             $receta->rec_cant = $cantidad[$index];
             $receta->save();
     
      }
     
      }

       return Redirect::to('/productos');
     
    }

    public function asignarcombo($prod_id){

      $insumos = DB::tABLE('productos')
       ->where(function ($query){
                $query->where('promocion','=','0')
                      ->orWhere('promocion','=','2');
        })
      ->where('productos.IdEmpresa',Auth::user()->IdEmpresa)
      ->join('unidad_medida as cum','cum.umecod','productos.umecod')
      ->get();

      $combos = DB::tABLE('combos as tbc')
      ->join('productos as tbp','tbp.IdProducto','tbc.prod_combo')
      ->join('unidad_medida as cum','cum.umecod','tbp.umecod')
      ->where('tbc.prod_id',$prod_id)
      ->get();

      $producto = DB::tABLE('productos')->where('IdProducto',$prod_id)->first();

      return view('empresas.combos.combos',compact('insumos','prod_id','producto','combos'));

    }

     public function registrarcombo(Request $request){

      $prodcombo = $request->get('prod_comb_id');
      $cantidad = $request->get('cantidad');
      $prod_id =$request->get('producto');

      combos::where('prod_id',$prod_id)->delete();

      $suma = 0;
      if(!empty($prodcombo)){
         foreach ($prodcombo as $index => $insu) {
     
             $combo = new combos;
             $combo->prod_combo = $insu;
             $combo->prod_id = $prod_id;
             $combo->comb_cant = $cantidad[$index];
             $combo->save();

             $buscarprecio = DB::tABLE('productos')->where('IdProducto',$insu)->first();

             $suma = $suma + $cantidad[$index]*$buscarprecio->costo;
     
      }

        $actualizarcosto = productos::findOrFail($prod_id);
        $actualizarcosto->costo = $request->get('totalcosto');
        if($request->get('preciofinal')=='0'){
           $actualizarcosto->propun =  $request->get('preciosugerido');
        }else{
           $actualizarcosto->propun = $request->get('preciofinal');
        }
       
        $actualizarcosto->update();

      }

       return Redirect::to('/productos');
     
    }

    public function actualizar(){

      $empresas = DB::tABLE('empresa_negocios')->get();

      $productos = DB::tABLE('productos')->get();

      foreach ($empresas as $emp) {
        
        foreach ($productos as $pro) {
            

            $buspro = DB::tABLE('producto_empresa')
            ->where('id_empresa_negocio',$emp->id_empresa_negocio)
            ->where('IdProducto',$pro->IdProducto)
            ->first();

       
            if(empty($bus_pro)){

               DB::tABLE('producto_empresa')
               ->insert(['IdProducto'=>$pro->IdProducto,
                'id_empresa_negocio'=>$emp->id_empresa_negocio,
                'precio'=>$pro->propun,
                'precio3'=>$pro->propun2,
                'precio2'=>$pro->propun1
              ]); 
            }

           
        }
      }

      return Redirect::to('/actualizarproductostock');

    }

    public function actualizar_producto_stock(){

      $empresas = DB::tABLE('empresa_negocios')->get();

    

      $productos = DB::tABLE('productos')->where('tipo','1')->get();

      foreach ($empresas as $emp) {
        $almacenes = DB::tABLE('almacenes')->where('id_empresa_negocio',$emp->id_empresa_negocio)->get();


        if(count($almacenes)>'0'){
          foreach ($almacenes as $alm){

              foreach ($productos as $pro) {
             

                   DB::tABLE('producto_stock')->insert([
				   'IdProducto'=>$pro->IdProducto,
				   'id_empresa_negocio'=>$emp->id_empresa_negocio,
				   'id_almacen'=>$alm->id_almacen,
				   'stock'=>$pro->stock_migrar,
            'stock_inicial'=>$pro->stock_migrar
				   
				   ]); 
               
              }
            }
           

        }
       


      }

       return Redirect::to('/utilitarios');

    }

  public function actualizarproductostock(){

      $empresas = DB::tABLE('empresa_negocios')->where('id_empresa_negocio','3')->get();

      $productos = DB::tABLE('productos')->get();

      foreach ($empresas as $emp) {
        

        foreach ($productos as $pro) {
            

            $buspro = DB::tABLE('producto_empresa')
            ->where('id_empresa_negocio',$emp->id_empresa_negocio)
            ->where('IdProducto',$pro->IdProducto)
            ->first();

       

            if(empty($bus_pro)){

               DB::tABLE('producto_empresa')->insert(['IdProducto'=>$pro->IdProducto,'id_empresa_negocio'=>$emp->id_empresa_negocio]); 
            }

           
        }
      }

    }


    public function actualizarstock(Request $request){

      $sucursal = $request->get('suc_id');
      $almacen = $request->get('alm_id');
      $fecha = $request->get('inv_fec');
      $productos = $request->get('id');
      $stock = $request->get('cant');
      $costo = $request->get('costo');

      $cabecera = DB::tABLE('inventario_cabecera')
      ->insert(['inv_fec'=>$fecha,
                'IdUsuario'=>Auth::user()->IdUsuario,
                'id_empresa_negocio'=>$sucursal,
                'id_almacen'=>$almacen
              ]);

      $inv_cab_id = DB::getPdo()->lastInsertId();


      foreach ($productos as $key => $pro) {
        
        $bus_sto = DB::tABLE('producto_stock')
        ->where('id_empresa_negocio',$sucursal)
        ->where('id_almacen',$almacen)
        ->where('IdProducto',$pro)
        ->first();


        if(!empty($bus_sto)){

          DB::tABLE('producto_stock')
          ->where('id_empresa_negocio',$sucursal)
          ->where('id_almacen',$almacen)
          ->where('IdProducto',$pro)
          ->update(['stock'=>$stock[$key]]);

        }else{

          DB::tABLE('producto_stock')
          ->insert(['stock'=>$stock[$key],
                    'id_empresa_negocio'=>$sucursal,
                    'id_almacen'=>$almacen,
                    'IdProducto'=>$pro
          ]);

        }
        
      


        DB::tABLE('inventario_detalle')
        ->insert(['inv_cab_id'=>$inv_cab_id,
          'IdProducto'=>$pro,
          'inv_can'=>$stock[$key],
          'inv_costo'=>$costo[$key]
        ]);


      }

      return Redirect::to('/inventarios');

    }

    


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function crear_combo()
    {
        $rucemp = trim(Auth::user()->IdEmpresa);

        $marcas = DB::tABLE('marcas')->get();

        $modelos = DB::tABLE('modelos')->get();

        $laboratorios = DB::tABLE('laboratorio')->get();

        $unidades = DB::tABLE('unidad_medida')
        ->orderBy('umecod','asc')->get();

        $monedas = DB::tABLE('moneda')->where('monest','=','Activo')->orderby('moncod','asc')->get();

        $proveedores = DB::tABLE('proveedor')->WHERE('id_empresa_negocio',Auth::user()->id_empresa_negocio)->get();

        $tipoigv = DB::tABLE('tipo_igv')->get();

         $categorias = DB::tABLE('categorias')->get();
        $subcategorias = DB::tABLE('subcategorias')->get();
        $tipos = DB::tABLE('tipo_producto')->get();

        $programas = DB::tABLE('programas')->get();

        $servicios = DB::tABLE('servicios')->get();

        $sucursal = DB::tABLE('empresa_negocios')->WHERE('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first();

        $list_prod = DB::tABLE('productos')
        ->where(function ($query){
            $query->where('promocion','0')
        			->orWhere('promocion','2');
        })
        ->where('tipo','1')
        ->get();

        return view('empresas.combos.create',compact('modelos','programas','unidades','categorias','monedas','proveedores','tipoigv','subcategorias','tipos','servicios','marcas','laboratorios','sucursal','list_prod'));

    }


      public function RegistrarPresentacion(Request $request){
       $rucemp = trim(Auth::user()->IdEmpresa); 
        $presentacion = new presentaciones;
        $presentacion->Presentacion = $request->get('presentacion');
        $presentacion->Descripcion = $request->get('descripcion');
        $presentacion->IdEmpresa =  $rucemp;
        $presentacion->save();

        return Redirect::to('/productos');
    }



     public function busquedaproductocomanda(Request $request, $valor){
        $rucemp = trim(Auth::user()->IdEmpresa);
        $productos = DB::tABLE('productos')->select('procod','pronom','propun','provun','IdProducto','umecod','stock','promocion','color','stock')
        ->where(function ($query) use($valor){
                $query->where('procod','=',$valor)
                      ->orWhere('pronom', 'like','%'.$valor.'%');
        })
        ->where(function ($query){
                $query->where('promocion','1')
                ->orWhere('promocion','0')
                      ->orWhere('promocion','2');
        })
        ->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
        ->get();

        $unidades = DB::tABLE('unidad_medida')->where('umeest','=','Activo')
        ->orderBy('umecod','asc')->get();

        $vista = view('empresas.combos.menucomanda',compact('productos','unidades'))->render();

        if($request->ajax()){
         return response()->json(['vista'=>$vista]);

        }
    }

    public function generarean13($digits){
        //first change digits to a string so that we can access individual numbers
       
        $digits =(string)$digits;
        // 1. Add the values of the digits in the even-numbered positions: 2, 4, 6, etc.
        $even_sum = $digits{1} + $digits{3} + $digits{5} + $digits{7} + $digits{9} + $digits{11};
        // 2. Multiply this result by 3.
        $even_sum_three = $even_sum * 3;
        // 3. Add the values of the digits in the odd-numbered positions: 1, 3, 5, etc.
        $odd_sum = $digits{0} + $digits{2} + $digits{4} + $digits{6} + $digits{8} + $digits{10};
        // 4. Sum the results of steps 2 and 3.
        $total_sum = $even_sum_three + $odd_sum;
        // 5. The check character is the smallest number which, when added to the result in step 4,  produces a multiple of 10.
        $next_ten = (ceil($total_sum/10))*10;
        $check_digit = $next_ten - $total_sum;
        return $digits . $check_digit;
    }



    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
   public function registrar_combo(Request $request)
    {

     
        $empresas = DB::tABLE('empresa_negocios')->get();
        $dat_emp = Empresa::findOrFail(Auth::user()->IdEmpresa);
        $tipo_codigo = $request->get('tipo_codigo');
        $codigo = $request->get('txt_procod');
        $codigo_bar = $request->get('codigo_barra');


        if($tipo_codigo=='1'){

            $codigoean13 = self::generarean13($codigo_bar);
        }else{
            $codigoean13 = $codigo_bar;
        }

      
        $nombreprod = trim($request->get('txt_pronom'));

       

        $validar = DB::tABLE('productos')
        ->where('procod',$codigo)
        ->where('tipo','1')
        ->get();

        if(empty($nombreprod)){

          if($request->ajax()){
              return response()->json(['estado'=>'error','mensaje'=>'INGRESAR LA DESCRIPCION DEL PRODUCTO']);
            }

        }


        if(count($validar)>0){

          if($request->ajax()){
              return response()->json(['estado'=>'error','mensaje'=>'EL CODIGO DE PRODUCTO EXISTE']);
            }

        }

        $codigos = $request->get('codigobarra');
        $presentacion = $request->get('presentacion');
         $codigo_barra_pre = $request->get('codigo_barra_pre');
        $descripcion = $request->get('descripcion');
        $costo = $request->get('txt_costo');
        $costo_pre = $request->get('costo');
        $flete = $request->get('txt_flete');
        $costo_total = $request->get('costo_total');
        $peso = $request->get('txt_peso');
        $precio = $request->get('precio');
        $precio2 = $request->get('precio2');
        $precio3 = $request->get('precio3');
        $factor = $request->get('factor');
        $prog_id = $request->get('prog_id');


        $productos = new productos;
        $productos->IdEmpresa= trim(Auth::user()->IdEmpresa);
        if(!empty($request->get('txt_procod'))){
            $productos->procod = $request->get('txt_procod');
        }
        $productos->pronom = $request->get('txt_pronom');
        $productos->marca = $request->get('marca');
        $productos->modelo = $request->get('modelo');
        $productos->dias_garantia = $request->get('dias_garantia');;
        $productos->tipo_codigo = $request->get('tipo_codigo');
        $productos->talla = $request->get('talla');
        $productos->codigo_barra = $codigoean13;
        $productos->color = $request->get('color');
        $productos->umecod = $request->get('txt_umecod');
        $productos->moncod = $request->get('txt_moncod');
        $productos->costofijo = $request->get('txt_costofijo');
        $productos->lote = $request->get('lote');
        $productos->vencimiento = $request->get('fechavencimiento');
        $productos->propun = $request->get('txt_propun');
        $productos->propun1 = $request->get('txt_propun2');
        $productos->propun2 = $request->get('txt_propun3');
        $productos->stock_min = $request->get('stock_min');
		$productos->factor = $request->get('factor_pro');
        $productos->lab_id = $request->get('lab_id');
        if($request->get('icbper')=='1'){
             $productos->mon_icbper = $dat_emp->icbper;
        }
        $productos->icbper = $request->get('icbper');
        $productos->prog_id = $prog_id;
        $productos->comision = $request->get('comision');
        $productos->ubicacion = $request->get('ubicacion');
        $productos->promocion = $request->get('promocion');
        $productos->costo = $costo;
        $productos->costo_total = $costo_total;
        $productos->peso = $peso;
        $productos->flete = $flete;
        $productos->tipo ='1';
        if($request->get('promocion')=='2'){
          $productos->ser_cod = $request->get('tip_pre');
        }
        
        $productos->tigcod = $request->get('tigcod');
        $productos->id_empresa_negocio = Auth::user()->id_empresa_negocio;
        $productos->cat_id = $request->get('cmbCatId');
        $productos->subcat_id = $request->get('subcat_id');
        $productos->tip_pro_id = $request->get('tip_pro_id');
        $productos->proest = "Activo";
        if(Input::hasFile('imagen')){
            $file=Input::file('imagen');
            $file->move(public_path().'/imagenes/productos/',$file->getClientOriginalName());
            $productos->imagenproducto=$file->getClientOriginalName();
        }
        $productos->save();
        
		
		

					
        //registrar en todas las empresas

        foreach($empresas as $emp) {

          $bus_pro = DB::tABLE('producto_empresa')
          ->where('id_empresa_negocio',$emp->id_empresa_negocio)
          ->where('IdProducto',$productos->IdProducto)
          ->first();

          if(empty($bus_pro)){
            DB::tABLE('producto_empresa')
            ->insert(['id_empresa_negocio'=>$emp->id_empresa_negocio,
                      'IdProducto'=>$productos->IdProducto,
                      'precio'=>$request->get('txt_propun'),
                      'precio3'=>$request->get('txt_propun3'),
                      'precio2'=>$request->get('txt_propun2')
            ]);
          }

          $almacenes = DB::tABLE('almacenes')
          ->where('id_empresa_negocio',$emp->id_empresa_negocio)
          ->get();

          if(count($almacenes)>'0'){
            foreach ($almacenes as $alm){

                  $bus_pro_stock = DB::tABLE('producto_stock')
                  ->where('id_empresa_negocio',$emp->id_empresa_negocio)
                  ->where('id_almacen',$alm->id_almacen)
                  ->where('IdProducto',$productos->IdProducto)
                  ->first();

                  if(empty($bus_pro_stock)){

                     DB::tABLE('producto_stock')->insert(['IdProducto'=>$productos->IdProducto,'id_empresa_negocio'=>$emp->id_empresa_negocio,'id_almacen'=>$alm->id_almacen]); 
                  } 
              }
            }

        }

          
        

        if(empty($request->get('txt_procod'))){

            $buscarproducto = productos::findOrFail($productos->IdProducto);

          

            if($request->get('promocion')=='0'){
                  $correlativo = DB::tABLE('empresa_negocios')->select('corr_prod')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first();
                  $numero = $correlativo->corr_prod+1;
                  DB::tABLE('empresa_negocios')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->update(['corr_prod'=>$numero]);
                $buscarproducto->procod = 'PROD'.str_pad($numero,4,"0", STR_PAD_LEFT);
            }elseif($request->get('promocion')=='2'){
                  $correlativo = DB::tABLE('empresa_negocios')->select('corr_prep')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first();
                   $numero = $correlativo->corr_prep+1;
                    DB::tABLE('empresa_negocios')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->update(['corr_prep'=>$numero]);
                $buscarproducto->procod = 'PREP'.str_pad($numero,4,"0", STR_PAD_LEFT);
            }elseif($request->get('promocion')=='4'){
                  $correlativo = DB::tABLE('empresa_negocios')->select('corr_insu')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first();
                   $numero = $correlativo->corr_insu+1;
                    DB::tABLE('empresa_negocios')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->update(['corr_insu'=>$numero]);
               $buscarproducto->procod = 'INSU'.str_pad($numero,4,"0", STR_PAD_LEFT);
            }

           
            $buscarproducto->update();

            $cod_pro = $buscarproducto->procod;

        }else{

            $cod_pro = $productos->procod;
        }

		
        if(!empty($presentacion)){
          foreach ($presentacion as $i => $pre) {

             if(!empty(trim($descripcion[$i]))){

                    $objpresentacion = new productos;
                    //$objpresentacion->IdEmpresa= trim(Auth::user()->IdEmpresa);
                    $objpresentacion->procod = $cod_pro;
                    $objpresentacion->codigo_barra = $codigo_barra_pre[$i];
                    $objpresentacion->pronom = $descripcion[$i];
                    $objpresentacion->marca =  $productos->marca;
                    $objpresentacion->modelo = $productos->modelo;
                    $objpresentacion->color =  $productos->color;
                    $objpresentacion->umecod = $pre;
                    $objpresentacion->moncod = $productos->moncod;
                    $objpresentacion->lab_id = $productos->lab_id;
                    $objpresentacion->propun = $precio[$i];
                    $objpresentacion->icbper = $productos->icbper;
                    $objpresentacion->promocion = $productos->promocion;
                    $objpresentacion->tipo ='2';
                
                    $objpresentacion->costo = $costo_pre[$i];
                    $objpresentacion->factor = $factor[$i];
                    $objpresentacion->tigcod = $productos->tigcod;
                    $objpresentacion->id_empresa_negocio = Auth::user()->id_empresa_negocio;
                    $objpresentacion->cat_id = $productos->cat_id;
                    $objpresentacion->proest = "Activo";
                    $objpresentacion->pro_rel = $productos->IdProducto;
                    $objpresentacion->save();

                    foreach($empresas as $emp) {
                      $bus_pro = DB::tABLE('producto_empresa')
                      ->where('id_empresa_negocio',$emp->id_empresa_negocio)
                      ->where('IdProducto',$objpresentacion->IdProducto)
                      ->first();

                      if(empty($bus_pro)){
                        DB::tABLE('producto_empresa')->insert(['id_empresa_negocio'=>$emp->id_empresa_negocio,'IdProducto'=>$objpresentacion->IdProducto,'precio'=>$precio[$i],'precio2'=>$precio2[$i]]);
                      }
                      
                      
                    }

               
             }
          }
        }

        $IdProducto_rel = $request->get('IdProducto_rel');
        $prod_comb_cant = $request->get('prod_comb_cant');
        $prod_comb_cost = $request->get('prod_comb_cost');
        $prod_comb_prec = $request->get('prod_comb_prec');
        

        if(!empty($IdProducto_rel)){
          foreach ($IdProducto_rel as $i => $ipr) {
             $combo = new combos;
             $combo->IdProducto_rel = $ipr;
             $combo->IdProducto_comb = $productos->IdProducto;
             $combo->prod_comb_cant = $prod_comb_cant[$i];
             $combo->prod_comb_prec = $prod_comb_prec[$i]; 
             $combo->prod_comb_cost = $prod_comb_cost[$i];
             $combo->save();
          }
        }


        foreach ($codigos as $index => $codbar) {
          
          DB::tABLE('producto_codigo')->insert(['IdProducto'=>$productos->IdProducto,'cod_bar'=>$codbar]);

        }

        if($request->ajax()){
              return response()->json(['mensaje'=>'PRODUCTO REGISTRADO']);
        }

      //return Redirect::to('/productos');
    }

    public function buscarcategorias($producto,Request $request){

        if($producto=='4'){
              $categorias = DB::tABLE('categorias')->where('tipo',$producto)->get();
        }else{
              $categorias = DB::tABLE('categorias')
              ->where('tipo',NULL)
              ->orWhere('tipo','')
              ->get();  
        }
      

        $vista = view('empresas.combos.divcategorias',compact('categorias'))->render();

        if($request->ajax()){
          return response()->json(['vista'=>$vista]);

        }

    }

     public function buscarsubcategorias($producto,Request $request){
 
        $subcategorias = DB::tABLE('subcategorias')->where('cat_id',$producto)->get();
        
        $vista = view('empresas.combos.divsubcategorias',compact('subcategorias'))->render();

        if($request->ajax()){
          return response()->json(['vista'=>$vista]);

        }

    }

    public function buscartipos($producto,Request $request){
 
        $tipos = DB::tABLE('tipo_producto')->where('subcat_id',$producto)->get();
        
        $vista = view('empresas.combos.divtipos',compact('tipos'))->render();

        if($request->ajax()){
          return response()->json(['vista'=>$vista]);

        }

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function editar_combo($id,$sucursal)
    {
        $rucemp = trim(Auth::user()->IdEmpresa);

        $marcas = DB::tABLE('marcas')->get();

         $programas = DB::tABLE('programas')->get();

           $modelos = DB::tABLE('modelos')->get();


        $productos = productos::where('productos.IdProducto',$id)

        ->leftjoin('producto_empresa','producto_empresa.IdProducto','productos.IdProducto')
        ->where('producto_empresa.id_empresa_negocio',$sucursal)
        ->first();

        $laboratorios = DB::tABLE('laboratorio')->get();

        $unidades = DB::tABLE('unidad_medida')
        ->orderBy('umecod','asc')->get();

        $tipoigv = DB::tABLE('tipo_igv')->get();

        $codigos = DB::tABLE('producto_codigo')
        ->where('IdProducto',$id)
        ->get();

        $presentaciones = DB::tABLE('productos')
        ->leftjoin('producto_empresa','producto_empresa.IdProducto','productos.IdProducto')
        ->where('pro_rel',$id)
        ->where('producto_empresa.id_empresa_negocio',$sucursal)
        ->get();
         
        $categorias = DB::tABLE('categorias')->get();
        $subcategorias = DB::tABLE('subcategorias')->get();
        $tipos = DB::tABLE('tipo_producto')->get();

         $servicios = DB::tABLE('servicios')->get();

        $monedas = DB::tABLE('moneda')->where('monest','=','Activo')->orderby('moncod','asc')->get();

         $list_prod = DB::tABLE('productos')
        ->where(function ($query){
            $query->where('promocion','0')
        			->orWhere('promocion','2');
        })
        ->where('tipo','1')
        ->get();

        $list_prod_comb = DB::tABLE('combos')
        ->where('IdProducto_comb',$id)
        ->join('productos','productos.IdProducto','combos.IdProducto_rel')->get();

        return view('empresas.combos.edit',compact('modelos','productos','unidades','categorias','monedas','tipoigv','codigos','presentaciones','subcategorias','tipos','sucursal','servicios','marcas','programas','laboratorios','list_prod','list_prod_comb'));
    }

    public function presentaciones($id){

       $rucemp = trim(Auth::user()->IdEmpresa);

        $productos = productos::findOrFail($id);

        $unidades = DB::tABLE('unidad_medida')
        ->orderBy('umecod','asc')->get();

        $tipoigv = DB::tABLE('tipo_igv')->get();
         
        $categorias = DB::tABLE('categorias')->get();
        $monedas = DB::tABLE('moneda')->where('monest','=','Activo')->orderby('moncod','asc')->get();

        $codigos = DB::tABLE('producto_codigo')
        ->where('IdProducto',$id)
        ->get();

        $presentaciones = DB::tABLE('productos')->where('pro_rel',$id)->get();
        
        return view('empresas.combos.presentaciones',compact('productos','unidades','categorias','monedas','tipoigv','codigos','presentaciones','id'));

    }


    public function actualizarpresentaciones($id,$idprod,$presentacion,$descripcion,$costo,$precio,$factor,$sucursal,$precio2,$codigo_barra_pre)
    {

        $empresas = DB::tABLE('empresa_negocios')->get();

        $producto = productos::findOrFail($id);
 
        $registros= DB::tABLE('productos')->where('pro_rel',$id)->where('tipo','2')->get();


        if(count($registros)>'0'){
          
          foreach ($registros as $reg) {
              
              if(!empty($idprod)){
                   if(!in_array($reg->IdProducto,$idprod)){

                      DB::tABLE('productos')->where('IdProducto',$reg->IdProducto)->delete();
     
                  }  

              }else{

                  DB::tABLE('productos')->where('pro_rel',$id)->delete();
              }  
          }
        }


      if(!empty($presentacion)){
            foreach ($presentacion as $i => $pre) {
               if(!empty($descripcion) && !empty($costo) && !empty($precio) ){

                      $buscar = DB::tABLE('productos')->where('IdProducto',$idprod[$i])->first();

                    
                      if(empty($buscar)){

                          $objpresentacion = new productos;
                          $objpresentacion->procod = $producto->procod;
                          $objpresentacion->pronom = $descripcion[$i];
                          $objpresentacion->codigo_barra = $codigo_barra_pre[$i];
                          $objpresentacion->marca =  $producto->marca;
                          $objpresentacion->modelo = $producto->modelo;
                          $objpresentacion->color =  $producto->color;
                          $objpresentacion->umecod = $pre;
                          $objpresentacion->moncod = $producto->moncod;
                          $objpresentacion->propun = $precio[$i];
                          $objpresentacion->icbper = $producto->icbper;
                          $objpresentacion->promocion = $producto->promocion;
                          $objpresentacion->lab_id = $producto->lab_id;
                          $objpresentacion->tipo ='2';
                          $objpresentacion->costo = $costo[$i];
                          $objpresentacion->factor = $factor[$i];
                          $objpresentacion->tigcod = $producto->tigcod;
                          $objpresentacion->id_empresa_negocio = $sucursal;
                          $objpresentacion->cat_id = $producto->cat_id;
                          $objpresentacion->proest = "Activo";
                          $objpresentacion->pro_rel = $id;
                          $objpresentacion->save();


                          foreach($empresas as $emp) {

                                  $bus_pro_emp = DB::tABLE('producto_empresa')
                                  ->where('id_empresa_negocio',$emp->id_empresa_negocio)
                                  ->where('IdProducto',$objpresentacion->IdProducto)
                                  ->first();

                                  if(empty($bus_pro_emp)){
                                    DB::tABLE('producto_empresa')
                                    ->insert(['id_empresa_negocio'=>$emp->id_empresa_negocio,
                                              'IdProducto'=>$objpresentacion->IdProducto,
                                              'precio'=>$precio[$i],
                                              'precio2'=>$precio2[$i]
                                    ]);
                                  }
      
                          }

                   

                          DB::tABLE('producto_empresa')
                          ->where('IdProducto',$objpresentacion->IdProducto)
                          ->where('id_empresa_negocio',$sucursal)
                          ->update(['precio'=>$precio[$i],
                          'precio2'=>$precio2[$i]]
                          );
                            
                       


                               


                      }else{

                        $objpresentacion = productos::findOrFail($idprod[$i]);
                          $objpresentacion->procod = $producto->procod;
                          $objpresentacion->codigo_barra = $codigo_barra_pre[$i];
                          $objpresentacion->pronom = $descripcion[$i];
                          $objpresentacion->marca =  $producto->marca;
                          $objpresentacion->modelo = $producto->modelo;
                          $objpresentacion->color =  $producto->color;
                          $objpresentacion->umecod = $pre;
                          $objpresentacion->moncod = $producto->moncod;
                          $objpresentacion->propun = $precio[$i];
                          $objpresentacion->icbper = $producto->icbper;
                          $objpresentacion->promocion = $producto->promocion;
                          $objpresentacion->lab_id = $producto->lab_id;
                          $objpresentacion->tipo ='2';
                          $objpresentacion->costo = $costo[$i];
                          $objpresentacion->factor = $factor[$i];
                          $objpresentacion->tigcod = $producto->tigcod;
                          $objpresentacion->id_empresa_negocio = $sucursal;
                          $objpresentacion->cat_id = $producto->cat_id;
                          $objpresentacion->proest = "Activo";
                          $objpresentacion->pro_rel = $id;
                          $objpresentacion->update();

                          
                          foreach($empresas as $emp) {

                                  $bus_pro_emp = DB::tABLE('producto_empresa')
                                  ->where('id_empresa_negocio',$emp->id_empresa_negocio)
                                  ->where('IdProducto',$objpresentacion->IdProducto)
                                  ->first();

                                  if(empty($bus_pro_emp)){
                                    DB::tABLE('producto_empresa')
                                    ->insert(['id_empresa_negocio'=>$emp->id_empresa_negocio,
                                              'IdProducto'=>$objpresentacion->IdProducto,
                                              'precio'=>$precio[$i],
                                              'precio2'=>$precio2[$i]
                                    ]);
                                  }
      
                          }

                         
                          DB::tABLE('producto_empresa')->where('IdProducto',$objpresentacion->IdProducto)->where('id_empresa_negocio',$sucursal)
                          ->update(['precio'=>$precio[$i],'precio2'=>$precio2[$i]]);
              
                        

                      }
                    
                 
               }
            }
          }

                
        


  
        return 'listo';
          
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function actualizar_combo(Request $request)
    {

        $dat_emp = Empresa::findOrFail(Auth::user()->IdEmpresa);

        $id = $request->get('idprod');

        $empresas = DB::tABLE('empresa_negocios')->get();

        $codigos = $request->get('codigobarra');
        $codigo_bar = $request->get('codigo_barra');

        $tipo_codigo = $request->get('tipo_codigo');



        if(strlen($codigo_bar)=='12' && $tipo_codigo=='1'){
            $codigoean13 = self::generarean13($codigo_bar);
        }else{
            $codigoean13 = trim($codigo_bar);
        }

        $sucursal = $request->get('sucursal');
        $prog_id = $request->get('prog_id');

        $costo = $request->get('txt_costo');
        $costo_pre = $request->get('costo');
        $flete = $request->get('txt_flete');
        $costo_total = $request->get('costo_total');
        $peso = $request->get('txt_peso');

        $productos= productos::findOrFail($id);
        $productos->codigo_barra = $codigoean13;
 
       // $codigoean13 = self::generarean13($request->get('txt_procod'));
        $productos->procod = $request->get('txt_procod');
        $productos->pronom = $request->get('txt_pronom');
        $productos->marca = $request->get('marca');
        $productos->costo = $costo;
        $productos->peso = $peso;
        $productos->flete = $flete;
        $productos->costo_total = $costo_total;
        $productos->costofijo = $request->get('txt_costofijo');

        $productos->lab_id = $request->get('lab_id');
         $productos->tipo_codigo = $request->get('tipo_codigo');
       // $productos->color = $request->get('color');
        $productos->cat_id = $request->get('cmbCatId');
        $productos->subcat_id = $request->get('subcat_id');
        $productos->prog_id = $request->get('prog_id');
        $productos->tip_pro_id = $request->get('tip_pro_id');
        $productos->modelo = $request->get('modelo');
        $productos->dias_garantia = $request->get('dias_garantia');
        $productos->talla = $request->get('talla');
                $productos->umecod = $request->get('txt_umecod');
        $productos->moncod = $request->get('txt_moncod');
     

        $productos->comision = $request->get('comision');
        $productos->lote = $request->get('lote');
         $productos->factor = $request->get('factor_pro');
        $productos->vencimiento = $request->get('fechavencimiento');
        $productos->stock_min = $request->get('stock_min');
        $productos->ubicacion = $request->get('ubicacion');
        $productos->tigcod = $request->get('tigcod');
        if($request->get('promocion')=='2'){
          $productos->ser_cod = $request->get('tip_pre');
        }
        $productos->propun = $request->get('txt_propun');
        $productos->propun1 = $request->get('txt_propun2');
        $productos->propun2 = $request->get('txt_propun3');
         if($request->get('icbper')=='1'){
             $productos->mon_icbper = $dat_emp->icbper;
        }
        $productos->icbper = $request->get('icbper');
        $productos->promocion = $request->get('promocion');
        $productos->proest = $request->get('txt_proest');
        $productos->id_empresa_negocio = Auth::user()->id_empresa_negocio;
         if(Input::hasFile('imagen')){
            $file=Input::file('imagen');
            $file->move(public_path().'/imagenes/productos/',$file->getClientOriginalName());
            $productos->imagenproducto=$file->getClientOriginalName();
        }

        $productos->update();
  
 
        $actpre = DB::tABLE('producto_empresa')->where('id_empresa_negocio',$sucursal)->where('IdProducto',$id)
        ->update(['precio'=>$request->get('txt_propun'),'precio2'=>$request->get('txt_propun2'),'precio3'=>$request->get('txt_propun3')]);
        
     
        $IdProducto_rel = $request->get('IdProducto_rel');
        $prod_comb_cant = $request->get('prod_comb_cant');
        $prod_comb_cost = $request->get('prod_comb_cost');
        $prod_comb_prec = $request->get('prod_comb_prec');
        
        DB::tABLE('combos')->where('IdProducto_comb',$productos->IdProducto)->delete();

        if(!empty($IdProducto_rel)){
          foreach ($IdProducto_rel as $i => $ipr) {
             $combo = new combos;
             $combo->IdProducto_rel = $ipr;
             $combo->IdProducto_comb = $productos->IdProducto;
             $combo->prod_comb_cant = $prod_comb_cant[$i];
             $combo->prod_comb_prec = $prod_comb_prec[$i]; 
             $combo->prod_comb_cost = $prod_comb_cost[$i];
             $combo->save();
          }
        }


        return Redirect::to('/combos');
          
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $productos= productos::findOrFail($id);
        $productos->delete();

        DB::tABLE('combos')->where('IdProducto_comb',$id)->delete();

        return Redirect::to('/combos');
    }

    public function precios(){

      $productos = DB::tABLE('productos')->get();

      foreach ($productos as $pro) {
        DB::tABLE('producto_empresa')->where('IdProducto',$pro->IdProducto)->update(['precio'=>$pro->propun]);
      }
    }

  //----------------------------------------CONSULTAS PARA PUNTO DE VENTA-------------------------------------------->

   public function consultarmenu(Request $request, $cat_id){
         
        $rucemp = trim(Auth::user()->IdEmpresa);

        $sucursal = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first();

        $almacen = DB::tABLE('almacenes')
        ->where('predeterminado','1')
        ->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
        ->first();



  
            $productos = DB::tABLE('productos')
            ->select('lab_nom','productos.lab_id','pro_rel','costo','mar_nom','procod','pronom','propun','productos.IdProducto','umecod','promocion','color','imagenproducto','precio2','precio3',DB::raw("(SELECT stock FROM producto_stock as pro WHERE productos.IdProducto = pro.IdProducto AND id_almacen='".$almacen->id_almacen."' AND pro.id_empresa_negocio='".$almacen->id_empresa_negocio."') as stock"),'precio',DB::raw("(SELECT count(*) FROM productos as pro WHERE pro.pro_rel =  productos.IdProducto) as cont_pre"))
            ->join('producto_empresa','producto_empresa.IdProducto','productos.IdProducto')
            ->leftjoin('laboratorio','laboratorio.lab_id','productos.lab_id')
          //  ->join('producto_stock','producto_stock.IdProducto','producto_empresa.IdProducto')
          ->leftjoin('marcas','marcas.mar_id','productos.marca')
        ->where(function ($query) use($search){
                $query->where('pronom','like','%'.$search.'%')
          ->orwhere('procod',$search);
          })
            ->where('tipo','1')
            ->where('promocion','!=','2')
           // ->where('producto_empresa.id_empresa_negocio',Auth::user()->id_empresa_negocio)
           // ->where('id_almacen',$almacen->id_almacen)
            ->groupby('productos.IdProducto')
            ->orderby('productos.pronom')
            ->orderby('productos.umecod')
            ->get();



      $contar = count($productos);


        $unidades = DB::tABLE('unidad_medida')->where('umeest','=','Activo')
        ->orderBy('umecod','asc')->get();

        $vista = view('empresas.combos.menu',compact('productos','unidades','sucursal'))->render();

        if($request->ajax()){
         return response()->json(['vista'=>$vista]);

        }
    }


    public function consultarproductosservicio(Request $request, $tipo,$prog_id){
         
        $rucemp = trim(Auth::user()->IdEmpresa);

        $sucursal = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first();

        $almacen = DB::tABLE('almacenes')
        ->where('predeterminado','1')
        ->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
        ->first();

        $productos = DB::tABLE('productos')
        ->join('programas_preparados','productos.IdProducto','programas_preparados.IdProducto')
        //->leftjoin('pedido_servicio_cab',)
        ->select('productos.IdProducto','pronom')
        ->where('promocion','2')
      //  ->where('ser_cod',$tipo)
        ->where('programas_preparados.prog_id',$prog_id)
        ->get();


      //  dd($productos);
        $unidades = DB::tABLE('unidad_medida')->where('umeest','=','Activo')
        ->orderBy('umecod','asc')->get();

        $vista = view('empresas.puntosventas.divdetalleservicio',compact('productos'))->render();

        if($request->ajax()){
         return response()->json(['vista'=>$vista]);

        }
    }

 
    public function consultarproductoscompra(Request $request){


      $search = $request->search; 

      $id_almacen = $request->almacen;
       
      $rucemp = trim(Auth::user()->IdEmpresa);

       $bus_alm = DB::tABLE('almacenes')
        ->where('id_almacen',$id_almacen)

        ->first();


       $bus_suc = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',$bus_alm->id_empresa_negocio)->first();

  
            $productos = DB::tABLE('productos')
            ->select('costo_total','lab_nom','productos.lab_id','pro_rel','costo','mar_nom','procod','pronom','propun','productos.IdProducto','umecod','promocion','color','imagenproducto','precio2','precio3',DB::raw("(SELECT stock FROM producto_stock as pro WHERE productos.IdProducto = pro.IdProducto AND id_almacen='".$bus_alm->id_almacen."' AND pro.id_empresa_negocio='".$bus_alm->id_empresa_negocio."') as stock"),'precio',DB::raw("(SELECT count(*) FROM productos as pro WHERE pro.pro_rel =  productos.IdProducto) as cont_pre"))
            ->join('producto_empresa','producto_empresa.IdProducto','productos.IdProducto')
              ->leftjoin('laboratorio','laboratorio.lab_id','productos.lab_id')
          //  ->join('producto_stock','producto_stock.IdProducto','producto_empresa.IdProducto')
          ->leftjoin('marcas','marcas.mar_id','productos.marca')
        ->where(function ($query) use($search){
                $query->where('pronom','like','%'.$search.'%')
          ->orwhere('procod',$search);
          })
            ->where('tipo','1')
            ->where('promocion','!=','2')
           // ->where('producto_empresa.id_empresa_negocio',Auth::user()->id_empresa_negocio)
           // ->where('id_almacen',$almacen->id_almacen)
            ->groupby('productos.IdProducto')
            ->orderby('productos.pronom')
            ->orderby('productos.umecod')
            ->get();



      $contar = count($productos);

      $results = array();
    foreach($productos as $pro){


        $codnom = $pro->procod;
		 
	

			 $results[] = ['id'=>$pro->IdProducto,'producto'=>$pro->pronom.' '.$pro->mar_nom,'text'=>'COD:'.$pro->procod.' | '.$pro->pronom.' '.$pro->mar_nom.' | Precio: '.$pro->precio .' | STOCK: '.$pro->stock,'textcompra'=>'COD:'.$pro->procod.' | '.$pro->pronom.' '.$pro->mar_nom.' | Costo: '.$pro->costo_total .' | STOCK: '.$pro->stock,'propun'=>$pro->precio,'contar'=>$pro->cont_pre,'sub_costo'=>$pro->costo,'costo'=>$pro->costo_total,'codigo'=>$pro->procod,'unidad'=>$pro->umecod,'pro_rel'=>$pro->pro_rel];
		



  

   

		
		   
       
	  }
     
       return response()->json($results);


        /* $vista = view('empresas.puntosventas.productos',compact('productos'))->render();

             if($request->ajax()){
         return response()->json(['vista'=>$vista]);

        }*/



    }




       public function busquedaproducto(Request $request, $valor){

        $rucemp = trim(Auth::user()->IdEmpresa);

          $sucursal = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first();

        $almacen = DB::tABLE('almacenes')
        ->where('predeterminado','1')
        ->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
        ->first();

        $productos = DB::tABLE('productos')
        ->select('productos.procod','productos.pronom','productos.propun','productos.IdProducto','productos.umecod','productos.promocion','productos.color','productos.imagenproducto','precio','precio2',DB::raw("(SELECT stock FROM producto_stock as pro WHERE productos.IdProducto = pro.IdProducto AND tipo='1' AND id_almacen='".$almacen->id_almacen."' AND id_empresa_negocio='".Auth::user()->id_empresa_negocio."') as stock"),DB::raw("(SELECT count(*) FROM productos as pro WHERE productos.IdProducto = pro.pro_rel AND tipo='2') as cont_pre"),'stock')
        ->leftjoin('producto_codigo','producto_codigo.IdProducto','productos.IdProducto')
        ->join('producto_empresa','producto_empresa.IdProducto','productos.IdProducto')
        ->join('producto_stock','producto_stock.IdProducto','producto_empresa.IdProducto')
        
       
        ->where(function ($query) use($valor){
                $query->where('cod_bar','=',$valor)
                      ->orWhere('productos.pronom', 'like','%'.$valor.'%')
                      ->orWhere('productos.procod',$valor);
        })
        ->where('tipo','1')
        ->where('producto_empresa.id_empresa_negocio',Auth::user()->id_empresa_negocio)
        ->where('id_almacen',$almacen->id_almacen)
        ->groupby('productos.IdProducto')
        ->orderby('productos.pronom')
        ->orderby('productos.umecod')
        ->get();

      //  dd(Auth::user()->id_empresa_negocio);
        
        $unidades = DB::tABLE('unidad_medida')->where('umeest','=','Activo')
        ->orderBy('umecod','asc')->get();

        $vista = view('empresas.combos.menu',compact('productos','unidades','sucursal'))->render();

    
        if($request->ajax()){
         return response()->json(['vista'=>$vista]);

        }
    }

     public function presentacionesproducto(Request $request, $id){
         
        $rucemp = trim(Auth::user()->IdEmpresa);
        
        $almacen = DB::tABLE('almacenes')
        ->where('predeterminado','1')
        ->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
        ->first();

      

        $productos = DB::tABLE('productos')
        ->select('acom','cat_sig','lab_nom','productos.lab_id','factor','precio3','productos.procod','productos.pronom','productos.propun','productos.IdProducto','productos.umecod','productos.promocion','productos.color','productos.imagenproducto','precio','precio2',DB::raw("(SELECT stock FROM producto_stock as pro WHERE pro.IdProducto ='".$id."' and pro.id_almacen='".$almacen->id_almacen."'  and pro.id_empresa_negocio='".Auth::user()->id_empresa_negocio."') as stock"),DB::raw("(SELECT umenom FROM unidad_medida as um WHERE um.umecod=productos.umecod) as umenom"))
        ->leftjoin('producto_empresa','producto_empresa.IdProducto','productos.IdProducto')
          ->leftjoin('laboratorio','laboratorio.lab_id','productos.lab_id')
        ->where(function ($query) use ($id) {
             
                    $query->where('productos.pro_rel',$id)
					->orwhere('productos.IdProducto',$id);


                })
        ->where('producto_empresa.id_empresa_negocio',Auth::user()->id_empresa_negocio)
        // ->where('tipo','2')
        ->groupby('productos.IdProducto')
        ->orderby('productos.pronom')
        ->orderby('productos.umecod')
        ->get();

      

        $unidades = DB::tABLE('unidad_medida')->where('umeest','=','Activo')
        ->orderBy('umecod','asc')->get();

        $vista = view('empresas.puntosventas.divproductos',compact('productos','unidades'))->render();

        if($request->ajax()){
         return response()->json(['vista'=>$vista]);

        }
    }

//-----------------------------------------------------------------------------------------------//
  

//------------------------------------------consultar productos compra --------------------------//


    

     public function presentacionesproductocompra(Request $request, $id, $sucursal,$almacen){
         
        $rucemp = trim(Auth::user()->IdEmpresa);

        $productos = DB::tABLE('productos')
         ->select('lab_nom','productos.lab_id','factor','productos.procod','productos.pronom','productos.propun','productos.IdProducto','productos.umecod','productos.promocion','productos.color','productos.imagenproducto','precio','precio2',DB::raw("(SELECT stock FROM producto_stock as pro WHERE pro.IdProducto ='".$id."' and pro.id_almacen='".$almacen."' and pro.id_empresa_negocio='".$sucursal."') as stock"),'costo')
        ->join('producto_empresa','producto_empresa.IdProducto','productos.IdProducto')
          ->leftjoin('laboratorio','laboratorio.lab_id','productos.lab_id')
        ->where(function ($query) use ($id) {
             
                          $query->where('productos.pro_rel',$id)
          ->orwhere('productos.IdProducto',$id);


                })
        ->where('producto_empresa.id_empresa_negocio',$sucursal)
     
        ->groupby('productos.IdProducto')
        ->orderby('productos.pronom')
        ->orderby('productos.umecod')
        ->get();

   

        $unidades = DB::tABLE('unidad_medida')->where('umeest','=','Activo')
        ->orderBy('umecod','asc')->get();

        $vista = view('empresas.compras.divproductos',compact('productos','unidades'))->render();

        if($request->ajax()){
         return response()->json(['vista'=>$vista]);

        }
    }


     public function consultarmenucompra(Request $request, $cat_id,$sucursal,$almacen){
         
        $rucemp = trim(Auth::user()->IdEmpresa);

       

        $productos = DB::tABLE('productos')
        ->select('procod','pronom','propun','productos.IdProducto','umecod','promocion','color','imagenproducto','precio2',DB::raw("(SELECT stock FROM producto_stock as pro WHERE productos.IdProducto = pro.IdProducto AND tipo='1' AND id_almacen='".$almacen."' AND id_empresa_negocio='".$sucursal."') as stock"),'precio',DB::raw("(SELECT count(*) FROM productos as pro WHERE productos.IdProducto = pro.pro_rel AND tipo='2') as cont_pre"),'costo')
        ->join('producto_empresa','producto_empresa.IdProducto','productos.IdProducto')
        ->join('producto_stock','producto_stock.IdProducto','producto_empresa.IdProducto')
        ->where('cat_id',$cat_id)
        ->where('tipo','1')
        ->where('producto_empresa.id_empresa_negocio',$sucursal)
        ->where('id_almacen',$almacen)
        ->groupby('productos.IdProducto')
        ->orderby('productos.pronom')
        ->orderby('productos.umecod')
        ->get();


        $unidades = DB::tABLE('unidad_medida')->where('umeest','=','Activo')
        ->orderBy('umecod','asc')->get();

        $vista = view('empresas.combos.menucompra',compact('productos','unidades'))->render();

        if($request->ajax()){
         return response()->json(['vista'=>$vista]);

        }
    }

    public function busquedaproductocompra(Request $request, $valor,$sucursal,$almacen){

        $rucemp = trim(Auth::user()->IdEmpresa);

         $productos = DB::tABLE('productos')
        ->select('procod','pronom','propun','productos.IdProducto','umecod','promocion','color','imagenproducto','precio2',DB::raw("(SELECT stock FROM producto_stock as pro WHERE productos.IdProducto = pro.IdProducto AND tipo='1' AND id_almacen='".$almacen."' AND id_empresa_negocio='".$sucursal."') as stock"),'precio',DB::raw("(SELECT count(*) FROM productos as pro WHERE productos.IdProducto = pro.pro_rel AND tipo='2') as cont_pre"),'costo')
        ->leftjoin('producto_codigo','producto_codigo.IdProducto','productos.IdProducto')
        ->leftjoin('producto_stock','producto_stock.IdProducto','productos.IdProducto')
        ->leftjoin('producto_empresa','producto_empresa.IdProducto','productos.IdProducto')
        ->where(function ($query) use($valor){
                $query->where('cod_bar','=',$valor)
                      ->orWhere('productos.pronom', 'like','%'.$valor.'%')
                      ->orWhere('productos.procod',$valor);
        })
        ->where('producto_empresa.id_empresa_negocio',$sucursal)
        ->where('id_almacen',$almacen)
        ->groupby('productos.IdProducto')
        ->orderby('productos.pronom')
        ->orderby('productos.umecod')
        ->get();

        $unidades = DB::tABLE('unidad_medida')->where('umeest','=','Activo')
        ->orderBy('umecod','asc')->get();

        $vista = view('empresas.combos.menucompra',compact('productos','unidades'))->render();

        if($request->ajax()){
         return response()->json(['vista'=>$vista]);

        }
    }

        public function consultarproductos(Request $request){


      $search = $request->search; 

      $id_almacen = $request->almacen;
       
      $rucemp = trim(Auth::user()->IdEmpresa);

       $bus_alm = DB::tABLE('almacenes')
        ->where('id_almacen',$id_almacen)

        ->first();


       $bus_suc = DB::tABLE('empresa_negocios')
       ->where('id_empresa_negocio',$bus_alm->id_empresa_negocio)
       ->first();

  
            $productos = DB::tABLE('productos')
            ->select('costo_total','lab_nom','productos.lab_id','pro_rel','costo','mar_nom','procod','pronom','propun','productos.IdProducto','umecod','promocion','color','imagenproducto','precio2','precio3',DB::raw("(SELECT stock FROM producto_stock as pro WHERE productos.IdProducto = pro.IdProducto AND id_almacen='".$bus_alm->id_almacen."' AND pro.id_empresa_negocio='".$bus_alm->id_empresa_negocio."') as stock"),'precio',DB::raw("(SELECT count(*) FROM productos as pro WHERE pro.pro_rel =  productos.IdProducto) as cont_pre"),'icbper','mon_icbper')
            ->join('producto_empresa','producto_empresa.IdProducto','productos.IdProducto')
            ->leftjoin('laboratorio','laboratorio.lab_id','productos.lab_id')
          //  ->join('producto_stock','producto_stock.IdProducto','producto_empresa.IdProducto')
          ->leftjoin('marcas','marcas.mar_id','productos.marca')
        ->where(function ($query) use($search){
                $query->where('pronom','like','%'.$search.'%')
          ->orwhere('procod',$search);
          })
            ->where('tipo','1')
            ->where('promocion','!=','2')
           // ->where('producto_empresa.id_empresa_negocio',Auth::user()->id_empresa_negocio)
           // ->where('id_almacen',$almacen->id_almacen)
            ->groupby('productos.IdProducto')
            ->orderby('productos.pronom')
            ->orderby('productos.umecod')
            ->get();



      $contar = count($productos);

      $results = array();
    foreach($productos as $pro){


        $codnom = $pro->procod;
     
    if(Auth::User()->hasRole('admin') || Auth::user()->hasRole('superadmin')){


       $results[] = ['id'=>$pro->IdProducto,'producto'=>$pro->pronom.' '.$pro->mar_nom,'text'=>'COD:'.$pro->procod.' | '.$pro->pronom.' '.$pro->mar_nom.' | Precio: '.$pro->precio .' | STOCK: '.$pro->stock,'textcompra'=>'COD:'.$pro->procod.' | '.$pro->pronom.' '.$pro->mar_nom.' | Costo: '.$pro->costo_total .' | STOCK: '.$pro->stock,'propun'=>$pro->precio,'contar'=>$pro->cont_pre,'costo'=>$pro->costo_total,'codigo'=>$pro->procod,'unidad'=>$pro->umecod,'pro_rel'=>$pro->pro_rel,'id_almacen'=>$id_almacen,"icbper"=>$pro->icbper,"mon_icbper"=>$pro->mon_icbper];
    
      if($pro->precio2 > 0){
         $results[] = ['id'=>$pro->IdProducto,'producto'=>$pro->pronom.' '.$pro->mar_nom,'text'=>'COD:'.$pro->procod.' | '.$pro->pronom.' '.$pro->mar_nom.' | Precio Mayor: '.$pro->precio2 .' | STOCK: '.$pro->stock,'textcompra'=>'COD:'.$pro->procod.' | '.$pro->pronom.' '.$pro->mar_nom.' | Costo: '.$pro->costo_total .' | STOCK: '.$pro->stock,'propun'=>$pro->precio2,'contar'=>$pro->cont_pre,'costo'=>$pro->costo_total,'codigo'=>$pro->procod,'unidad'=>$pro->umecod,'pro_rel'=>$pro->pro_rel,'id_almacen'=>$id_almacen,"icbper"=>$pro->icbper,"mon_icbper"=>$pro->mon_icbper];
      
      }
      
      if($pro->precio3 > 0){
         $results[] = ['id'=>$pro->IdProducto,'producto'=>$pro->pronom.' '.$pro->mar_nom,'text'=>'COD:'.$pro->procod.' | '.$pro->pronom.' '.$pro->mar_nom.' | Precio Especial: '.$pro->precio3 .' | STOCK: '.$pro->stock,'textcompra'=>'COD:'.$pro->procod.' | '.$pro->pronom.' '.$pro->mar_nom.' | Costo: '.$pro->costo_total .' | STOCK: '.$pro->stock,'propun'=>$pro->precio3,'contar'=>$pro->cont_pre,'costo'=>$pro->costo_total,'codigo'=>$pro->procod,'unidad'=>$pro->umecod,'pro_rel'=>$pro->pro_rel,'id_almacen'=>$id_almacen,"icbper"=>$pro->icbper,"mon_icbper"=>$pro->mon_icbper];
      
      }


    }
    

    

      if(Auth::User()->hasRole('vendedor') || Auth::User()->hasRole('caja') || Auth::User()->hasRole('tecnico')){
        
        if($bus_suc->ven_sin_sto =='0'){  

          if($pro->stock > 0){

             $results[] = ['id'=>$pro->IdProducto,'producto'=>$pro->pronom.' '.$pro->mar_nom,'text'=>'COD:'.$pro->procod.' | '.$pro->pronom.' '.$pro->mar_nom.' | Precio: '.$pro->precio .' | STOCK: '.$pro->stock,'textcompra'=>'COD:'.$pro->procod.' | '.$pro->pronom.' '.$pro->mar_nom.' | Costo: '.$pro->costo_total .' | STOCK: '.$pro->stock,'propun'=>$pro->precio,'contar'=>$pro->cont_pre,'costo'=>$pro->costo_total,'codigo'=>$pro->procod,'unidad'=>$pro->umecod,'pro_rel'=>$pro->pro_rel,'id_almacen'=>$id_almacen];
          
          if($pro->precio2 > 0){
             $results[] = ['id'=>$pro->IdProducto,'producto'=>$pro->pronom.' '.$pro->mar_nom,'text'=>'COD:'.$pro->procod.' | '.$pro->pronom.' '.$pro->mar_nom.' | Precio Mayor: '.$pro->precio2 .' | STOCK: '.$pro->stock,'textcompra'=>'COD:'.$pro->procod.' | '.$pro->pronom.' '.$pro->mar_nom.' | Costo: '.$pro->costo_total .' | STOCK: '.$pro->stock,'propun'=>$pro->precio2,'contar'=>$pro->cont_pre,'costo'=>$pro->costo_total,'codigo'=>$pro->procod,'unidad'=>$pro->umecod,'pro_rel'=>$pro->pro_rel,'id_almacen'=>$id_almacen];
          
          }

          }
          
         
          
         }else{
             $results[] = ['id'=>$pro->IdProducto,'producto'=>$pro->pronom.' '.$pro->mar_nom,'text'=>'COD:'.$pro->procod.' | '.$pro->pronom.' '.$pro->mar_nom.' | Precio: '.$pro->precio .' | STOCK: '.$pro->stock,'textcompra'=>'COD:'.$pro->procod.' | '.$pro->pronom.' '.$pro->mar_nom.' | Costo: '.$pro->costo_total .' | STOCK: '.$pro->stock,'propun'=>$pro->precio,'contar'=>$pro->cont_pre,'costo'=>$pro->costo_total,'codigo'=>$pro->procod,'unidad'=>$pro->umecod,'pro_rel'=>$pro->pro_rel,'id_almacen'=>$id_almacen];
          
          if($pro->precio2 > 0){
             $results[] = ['id'=>$pro->IdProducto,'producto'=>$pro->pronom.' '.$pro->mar_nom,'text'=>'COD:'.$pro->procod.' | '.$pro->pronom.' '.$pro->mar_nom.' | Precio Mayor: '.$pro->precio2 .' | STOCK: '.$pro->stock,'textcompra'=>'COD:'.$pro->procod.' | '.$pro->pronom.' '.$pro->mar_nom.' | Costo: '.$pro->costo_total .' | STOCK: '.$pro->stock,'propun'=>$pro->precio2,'contar'=>$pro->cont_pre,'costo'=>$pro->costo_total,'codigo'=>$pro->procod,'unidad'=>$pro->umecod,'pro_rel'=>$pro->pro_rel,'id_almacen'=>$id_almacen];
          
          }
         }


        } 


   

    
       
       
    }
     

     
       return response()->json($results);


        /* $vista = view('empresas.puntosventas.productos',compact('productos'))->render();

             if($request->ajax()){
         return response()->json(['vista'=>$vista]);

        }*/



    }



            public function consultarservicios(Request $request){


      $search = $request->search; 

      $id_almacen = $request->almacen;
       
      $rucemp = trim(Auth::user()->IdEmpresa);

       $bus_alm = DB::tABLE('almacenes')
        ->where('id_almacen',$id_almacen)

        ->first();


       $bus_suc = DB::tABLE('empresa_negocios')
       ->where('id_empresa_negocio',$bus_alm->id_empresa_negocio)
       ->first();

  
            $productos = DB::tABLE('productos')
            ->select('costo_total','lab_nom','productos.lab_id','pro_rel','costo','mar_nom','procod','pronom','propun','productos.IdProducto','umecod','promocion','color','imagenproducto','precio2','precio3',DB::raw("(SELECT stock FROM producto_stock as pro WHERE productos.IdProducto = pro.IdProducto AND id_almacen='".$bus_alm->id_almacen."' AND pro.id_empresa_negocio='".$bus_alm->id_empresa_negocio."') as stock"),'precio',DB::raw("(SELECT count(*) FROM productos as pro WHERE pro.pro_rel =  productos.IdProducto) as cont_pre"))
            ->join('producto_empresa','producto_empresa.IdProducto','productos.IdProducto')
            ->leftjoin('laboratorio','laboratorio.lab_id','productos.lab_id')
          //  ->join('producto_stock','producto_stock.IdProducto','producto_empresa.IdProducto')
          ->leftjoin('marcas','marcas.mar_id','productos.marca')
        ->where(function ($query) use($search){
                $query->where('pronom','like','%'.$search.'%')
          ->orwhere('procod',$search);
          })
            ->where('tipo','1')
            ->where('promocion','=','6')
           // ->where('producto_empresa.id_empresa_negocio',Auth::user()->id_empresa_negocio)
           // ->where('id_almacen',$almacen->id_almacen)
            ->groupby('productos.IdProducto')
            ->orderby('productos.pronom')
            ->orderby('productos.umecod')
            ->get();



      $contar = count($productos);

      $results = array();
    foreach($productos as $pro){


        $codnom = $pro->procod;
     
    if(Auth::User()->hasRole('admin') || Auth::user()->hasRole('superadmin')){


       $results[] = ['id'=>$pro->IdProducto,'producto'=>$pro->pronom.' '.$pro->mar_nom,'text'=>'COD:'.$pro->procod.' | '.$pro->pronom.' '.$pro->mar_nom.' | Precio: '.$pro->precio .' | STOCK: '.$pro->stock,'textcompra'=>'COD:'.$pro->procod.' | '.$pro->pronom.' '.$pro->mar_nom.' | Costo: '.$pro->costo_total .' | STOCK: '.$pro->stock,'propun'=>$pro->precio,'contar'=>$pro->cont_pre,'costo'=>$pro->costo_total,'codigo'=>$pro->procod,'unidad'=>$pro->umecod,'pro_rel'=>$pro->pro_rel,"id_almacen"=>$id_almacen];
    
      if($pro->precio2 > 0){
         $results[] = ['id'=>$pro->IdProducto,'producto'=>$pro->pronom.' '.$pro->mar_nom,'text'=>'COD:'.$pro->procod.' | '.$pro->pronom.' '.$pro->mar_nom.' | Precio Mayor: '.$pro->precio2 .' | STOCK: '.$pro->stock,'textcompra'=>'COD:'.$pro->procod.' | '.$pro->pronom.' '.$pro->mar_nom.' | Costo: '.$pro->costo_total .' | STOCK: '.$pro->stock,'propun'=>$pro->precio2,'contar'=>$pro->cont_pre,'costo'=>$pro->costo_total,'codigo'=>$pro->procod,'unidad'=>$pro->umecod,'pro_rel'=>$pro->pro_rel,"id_almacen"=>$id_almacen];
      
      }
      
      if($pro->precio3 > 0){
         $results[] = ['id'=>$pro->IdProducto,'producto'=>$pro->pronom.' '.$pro->mar_nom,'text'=>'COD:'.$pro->procod.' | '.$pro->pronom.' '.$pro->mar_nom.' | Precio Especial: '.$pro->precio3 .' | STOCK: '.$pro->stock,'textcompra'=>'COD:'.$pro->procod.' | '.$pro->pronom.' '.$pro->mar_nom.' | Costo: '.$pro->costo_total .' | STOCK: '.$pro->stock,'propun'=>$pro->precio3,'contar'=>$pro->cont_pre,'costo'=>$pro->costo_total,'codigo'=>$pro->procod,'unidad'=>$pro->umecod,'pro_rel'=>$pro->pro_rel,"id_almacen"=>$id_almacen];
      
      }


    }
    

    

      if(Auth::User()->hasRole('vendedor') || Auth::User()->hasRole('caja') || Auth::User()->hasRole('tecnico')){
        
        if($bus_suc->ven_sin_sto =='0'){  

          if($pro->stock > 0){

             $results[] = ['id'=>$pro->IdProducto,'producto'=>$pro->pronom.' '.$pro->mar_nom,'text'=>'COD:'.$pro->procod.' | '.$pro->pronom.' '.$pro->mar_nom.' | Precio: '.$pro->precio .' | STOCK: '.$pro->stock,'textcompra'=>'COD:'.$pro->procod.' | '.$pro->pronom.' '.$pro->mar_nom.' | Costo: '.$pro->costo_total .' | STOCK: '.$pro->stock,'propun'=>$pro->precio,'contar'=>$pro->cont_pre,'costo'=>$pro->costo_total,'codigo'=>$pro->procod,'unidad'=>$pro->umecod,'pro_rel'=>$pro->pro_rel,"id_almacen"=>$id_almacen];
          
          if($pro->precio2 > 0){
             $results[] = ['id'=>$pro->IdProducto,'producto'=>$pro->pronom.' '.$pro->mar_nom,'text'=>'COD:'.$pro->procod.' | '.$pro->pronom.' '.$pro->mar_nom.' | Precio Mayor: '.$pro->precio2 .' | STOCK: '.$pro->stock,'textcompra'=>'COD:'.$pro->procod.' | '.$pro->pronom.' '.$pro->mar_nom.' | Costo: '.$pro->costo_total .' | STOCK: '.$pro->stock,'propun'=>$pro->precio2,'contar'=>$pro->cont_pre,'costo'=>$pro->costo_total,'codigo'=>$pro->procod,'unidad'=>$pro->umecod,'pro_rel'=>$pro->pro_rel,"id_almacen"=>$id_almacen];
          
          }

          }
          
         
          
         }else{
             $results[] = ['id'=>$pro->IdProducto,'producto'=>$pro->pronom.' '.$pro->mar_nom,'text'=>'COD:'.$pro->procod.' | '.$pro->pronom.' '.$pro->mar_nom.' | Precio: '.$pro->precio .' | STOCK: '.$pro->stock,'textcompra'=>'COD:'.$pro->procod.' | '.$pro->pronom.' '.$pro->mar_nom.' | Costo: '.$pro->costo_total .' | STOCK: '.$pro->stock,'propun'=>$pro->precio,'contar'=>$pro->cont_pre,'costo'=>$pro->costo_total,'codigo'=>$pro->procod,'unidad'=>$pro->umecod,'pro_rel'=>$pro->pro_rel,"id_almacen"=>$id_almacen];
          
          if($pro->precio2 > 0){
             $results[] = ['id'=>$pro->IdProducto,'producto'=>$pro->pronom.' '.$pro->mar_nom,'text'=>'COD:'.$pro->procod.' | '.$pro->pronom.' '.$pro->mar_nom.' | Precio Mayor: '.$pro->precio2 .' | STOCK: '.$pro->stock,'textcompra'=>'COD:'.$pro->procod.' | '.$pro->pronom.' '.$pro->mar_nom.' | Costo: '.$pro->costo_total .' | STOCK: '.$pro->stock,'propun'=>$pro->precio2,'contar'=>$pro->cont_pre,'costo'=>$pro->costo_total,'codigo'=>$pro->procod,'unidad'=>$pro->umecod,'pro_rel'=>$pro->pro_rel,"id_almacen"=>$id_almacen];
          
          }
         }


        } 


   

    
       
       
    }
     
       return response()->json($results);


        /* $vista = view('empresas.puntosventas.productos',compact('productos'))->render();

             if($request->ajax()){
         return response()->json(['vista'=>$vista]);

        }*/



    }



       public function consultarrepuestos(Request $request){


      $search = $request->search; 

      $id_almacen = $request->almacen;
       
      $rucemp = trim(Auth::user()->IdEmpresa);

       $bus_alm = DB::tABLE('almacenes')
        ->where('id_almacen',$id_almacen)

        ->first();


       $bus_suc = DB::tABLE('empresa_negocios')
       ->where('id_empresa_negocio',$bus_alm->id_empresa_negocio)
       ->first();

  
            $productos = DB::tABLE('productos')
            ->select('costo_total','lab_nom','productos.lab_id','pro_rel','costo','mar_nom','procod','pronom','propun','productos.IdProducto','umecod','promocion','color','imagenproducto','precio2','precio3',DB::raw("(SELECT stock FROM producto_stock as pro WHERE productos.IdProducto = pro.IdProducto AND id_almacen='".$bus_alm->id_almacen."' AND pro.id_empresa_negocio='".$bus_alm->id_empresa_negocio."') as stock"),'precio',DB::raw("(SELECT count(*) FROM productos as pro WHERE pro.pro_rel =  productos.IdProducto) as cont_pre"))
            ->join('producto_empresa','producto_empresa.IdProducto','productos.IdProducto')
            ->leftjoin('laboratorio','laboratorio.lab_id','productos.lab_id')
          //  ->join('producto_stock','producto_stock.IdProducto','producto_empresa.IdProducto')
          ->leftjoin('marcas','marcas.mar_id','productos.marca')
        ->where(function ($query) use($search){
                $query->where('pronom','like','%'.$search.'%')
          ->orwhere('procod',$search);
          })
            ->where('tipo','1')
            ->where('promocion','=','7')
           // ->where('producto_empresa.id_empresa_negocio',Auth::user()->id_empresa_negocio)
           // ->where('id_almacen',$almacen->id_almacen)
            ->groupby('productos.IdProducto')
            ->orderby('productos.pronom')
            ->orderby('productos.umecod')
            ->get();



      $contar = count($productos);

      $results = array();
    foreach($productos as $pro){


        $codnom = $pro->procod;
     
    if(Auth::User()->hasRole('admin') || Auth::user()->hasRole('superadmin') || Auth::user()->hasRole('supervisor') || Auth::user()->hasRole('recepcion')){


       $results[] = ['id'=>$pro->IdProducto,'producto'=>$pro->pronom.' '.$pro->mar_nom,'text'=>'COD:'.$pro->procod.' | '.$pro->pronom.' '.$pro->mar_nom.' | Precio: '.$pro->precio .' | STOCK: '.$pro->stock,'textcompra'=>'COD:'.$pro->procod.' | '.$pro->pronom.' '.$pro->mar_nom.' | Costo: '.$pro->costo_total .' | STOCK: '.$pro->stock,'propun'=>$pro->precio,'contar'=>$pro->cont_pre,'costo'=>$pro->costo_total,'codigo'=>$pro->procod,'unidad'=>$pro->umecod,'pro_rel'=>$pro->pro_rel,"id_almacen"=>$id_almacen];
    
      if($pro->precio2 > 0){
         $results[] = ['id'=>$pro->IdProducto,'producto'=>$pro->pronom.' '.$pro->mar_nom,'text'=>'COD:'.$pro->procod.' | '.$pro->pronom.' '.$pro->mar_nom.' | Precio Mayor: '.$pro->precio2 .' | STOCK: '.$pro->stock,'textcompra'=>'COD:'.$pro->procod.' | '.$pro->pronom.' '.$pro->mar_nom.' | Costo: '.$pro->costo_total .' | STOCK: '.$pro->stock,'propun'=>$pro->precio2,'contar'=>$pro->cont_pre,'costo'=>$pro->costo_total,'codigo'=>$pro->procod,'unidad'=>$pro->umecod,'pro_rel'=>$pro->pro_rel,"id_almacen"=>$id_almacen];
      
      }
      
      if($pro->precio3 > 0){
         $results[] = ['id'=>$pro->IdProducto,'producto'=>$pro->pronom.' '.$pro->mar_nom,'text'=>'COD:'.$pro->procod.' | '.$pro->pronom.' '.$pro->mar_nom.' | Precio Especial: '.$pro->precio3 .' | STOCK: '.$pro->stock,'textcompra'=>'COD:'.$pro->procod.' | '.$pro->pronom.' '.$pro->mar_nom.' | Costo: '.$pro->costo_total .' | STOCK: '.$pro->stock,'propun'=>$pro->precio3,'contar'=>$pro->cont_pre,'costo'=>$pro->costo_total,'codigo'=>$pro->procod,'unidad'=>$pro->umecod,'pro_rel'=>$pro->pro_rel,"id_almacen"=>$id_almacen];
      
      }


    }
    

    

      if(Auth::User()->hasRole('vendedor') || Auth::User()->hasRole('caja') || Auth::User()->hasRole('tecnico')){
        
        if($bus_suc->ven_sin_sto =='0'){  

          if($pro->stock > 0){

             $results[] = ['id'=>$pro->IdProducto,'producto'=>$pro->pronom.' '.$pro->mar_nom,'text'=>'COD:'.$pro->procod.' | '.$pro->pronom.' '.$pro->mar_nom.' | Precio: '.$pro->precio .' | STOCK: '.$pro->stock,'textcompra'=>'COD:'.$pro->procod.' | '.$pro->pronom.' '.$pro->mar_nom.' | Costo: '.$pro->costo_total .' | STOCK: '.$pro->stock,'propun'=>$pro->precio,'contar'=>$pro->cont_pre,'costo'=>$pro->costo_total,'codigo'=>$pro->procod,'unidad'=>$pro->umecod,'pro_rel'=>$pro->pro_rel,"id_almacen"=>$id_almacen];
          
          if($pro->precio2 > 0){
             $results[] = ['id'=>$pro->IdProducto,'producto'=>$pro->pronom.' '.$pro->mar_nom,'text'=>'COD:'.$pro->procod.' | '.$pro->pronom.' '.$pro->mar_nom.' | Precio Mayor: '.$pro->precio2 .' | STOCK: '.$pro->stock,'textcompra'=>'COD:'.$pro->procod.' | '.$pro->pronom.' '.$pro->mar_nom.' | Costo: '.$pro->costo_total .' | STOCK: '.$pro->stock,'propun'=>$pro->precio2,'contar'=>$pro->cont_pre,'costo'=>$pro->costo_total,'codigo'=>$pro->procod,'unidad'=>$pro->umecod,'pro_rel'=>$pro->pro_rel,"id_almacen"=>$id_almacen];
          
          }

          }
          
         
          
         }else{
             $results[] = ['id'=>$pro->IdProducto,'producto'=>$pro->pronom.' '.$pro->mar_nom,'text'=>'COD:'.$pro->procod.' | '.$pro->pronom.' '.$pro->mar_nom.' | Precio: '.$pro->precio .' | STOCK: '.$pro->stock,'textcompra'=>'COD:'.$pro->procod.' | '.$pro->pronom.' '.$pro->mar_nom.' | Costo: '.$pro->costo_total .' | STOCK: '.$pro->stock,'propun'=>$pro->precio,'contar'=>$pro->cont_pre,'costo'=>$pro->costo_total,'codigo'=>$pro->procod,'unidad'=>$pro->umecod,'pro_rel'=>$pro->pro_rel,"id_almacen"=>$id_almacen];
          
          if($pro->precio2 > 0){
             $results[] = ['id'=>$pro->IdProducto,'producto'=>$pro->pronom.' '.$pro->mar_nom,'text'=>'COD:'.$pro->procod.' | '.$pro->pronom.' '.$pro->mar_nom.' | Precio Mayor: '.$pro->precio2 .' | STOCK: '.$pro->stock,'textcompra'=>'COD:'.$pro->procod.' | '.$pro->pronom.' '.$pro->mar_nom.' | Costo: '.$pro->costo_total .' | STOCK: '.$pro->stock,'propun'=>$pro->precio2,'contar'=>$pro->cont_pre,'costo'=>$pro->costo_total,'codigo'=>$pro->procod,'unidad'=>$pro->umecod,'pro_rel'=>$pro->pro_rel,"id_almacen"=>$id_almacen];
          
          }
         }


        } 


   

    
       
       
    }
     
       return response()->json($results);


        /* $vista = view('empresas.puntosventas.productos',compact('productos'))->render();

             if($request->ajax()){
         return response()->json(['vista'=>$vista]);

        }*/



    }



        public function consultarproductosbarra(Request $request){


      $search = $request->value; 

         $bus_suc = DB::tABLE('empresa_negocios')
       ->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
       ->first();

        $bus_alm = DB::tABLE('almacenes')
         ->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
        ->where('predeterminado','1')

        ->first();


      $id_almacen =  $bus_alm->id_almacen;
       
      $rucemp = trim(Auth::user()->IdEmpresa);

  
            $productos = DB::tABLE('productos')
            ->select('costo_total','lab_nom','productos.lab_id','pro_rel','costo','mar_nom','procod','pronom','propun','productos.IdProducto','umecod','promocion','color','imagenproducto','precio2','precio3',DB::raw("(SELECT stock FROM producto_stock as pro WHERE productos.IdProducto = pro.IdProducto AND id_almacen='".$bus_alm->id_almacen."' AND pro.id_empresa_negocio='".$bus_alm->id_empresa_negocio."') as stock"),'precio',DB::raw("(SELECT count(*) FROM productos as pro WHERE pro.pro_rel =  productos.IdProducto) as cont_pre"),'icbper','mon_icbper')
            ->join('producto_empresa','producto_empresa.IdProducto','productos.IdProducto')
            ->leftjoin('laboratorio','laboratorio.lab_id','productos.lab_id')
          //  ->join('producto_stock','producto_stock.IdProducto','producto_empresa.IdProducto')
          ->leftjoin('marcas','marcas.mar_id','productos.marca')
        ->where(function ($query) use($search){
                $query->where('codigo_barra',$search);
          })
            //->where('tipo','1')
            ->where('promocion','!=','2')
           // ->where('producto_empresa.id_empresa_negocio',Auth::user()->id_empresa_negocio)
           // ->where('id_almacen',$almacen->id_almacen)
            ->groupby('productos.IdProducto')
            ->orderby('productos.pronom')
            ->orderby('productos.umecod')
            ->get();


      $contar = count($productos);

      $results = array();


    foreach($productos as $pro){


        $codnom = $pro->procod;
     
    if(Auth::User()->hasRole('admin') || Auth::user()->hasRole('superadmin')){


       $results[] = ['id'=>$pro->IdProducto,'producto'=>$pro->pronom.' '.$pro->mar_nom,'text'=>'COD:'.$pro->procod.' | '.$pro->pronom.' '.$pro->mar_nom.' | Precio: '.$pro->precio .' | STOCK: '.$pro->stock,'textcompra'=>'COD:'.$pro->procod.' | '.$pro->pronom.' '.$pro->mar_nom.' | Costo: '.$pro->costo_total .' | STOCK: '.$pro->stock,'propun'=>$pro->precio,'contar'=>$pro->cont_pre,'costo'=>$pro->costo_total,'codigo'=>$pro->procod,'unidad'=>$pro->umecod,'pro_rel'=>$pro->pro_rel,"id_almacen"=>$id_almacen,"icbper"=>$pro->icbper,"mon_icbper"=>$pro->mon_icbper];
    
      if($pro->precio2 > 0){
         $results[] = ['id'=>$pro->IdProducto,'producto'=>$pro->pronom.' '.$pro->mar_nom,'text'=>'COD:'.$pro->procod.' | '.$pro->pronom.' '.$pro->mar_nom.' | Precio Mayor: '.$pro->precio2 .' | STOCK: '.$pro->stock,'textcompra'=>'COD:'.$pro->procod.' | '.$pro->pronom.' '.$pro->mar_nom.' | Costo: '.$pro->costo_total .' | STOCK: '.$pro->stock,'propun'=>$pro->precio2,'contar'=>$pro->cont_pre,'costo'=>$pro->costo_total,'codigo'=>$pro->procod,'unidad'=>$pro->umecod,'pro_rel'=>$pro->pro_rel,"id_almacen"=>$id_almacen,"icbper"=>$pro->icbper,"mon_icbper"=>$pro->mon_icbper];
      
      }
      
      if($pro->precio3 > 0){
         $results[] = ['id'=>$pro->IdProducto,'producto'=>$pro->pronom.' '.$pro->mar_nom,'text'=>'COD:'.$pro->procod.' | '.$pro->pronom.' '.$pro->mar_nom.' | Precio Especial: '.$pro->precio3 .' | STOCK: '.$pro->stock,'textcompra'=>'COD:'.$pro->procod.' | '.$pro->pronom.' '.$pro->mar_nom.' | Costo: '.$pro->costo_total .' | STOCK: '.$pro->stock,'propun'=>$pro->precio3,'contar'=>$pro->cont_pre,'costo'=>$pro->costo_total,'codigo'=>$pro->procod,'unidad'=>$pro->umecod,'pro_rel'=>$pro->pro_rel,"id_almacen"=>$id_almacen,"icbper"=>$pro->icbper,"mon_icbper"=>$pro->mon_icbper];
      
      }


    }
    



      if(Auth::User()->hasRole('vendedor') || Auth::User()->hasRole('caja')){
        
        if($bus_suc->ven_sin_sto =='0'){  

          if($pro->stock > 0){

             $results[] = ['id'=>$pro->IdProducto,'producto'=>$pro->pronom.' '.$pro->mar_nom,'text'=>'COD:'.$pro->procod.' | '.$pro->pronom.' '.$pro->mar_nom.' | Precio: '.$pro->precio .' | STOCK: '.$pro->stock,'textcompra'=>'COD:'.$pro->procod.' | '.$pro->pronom.' '.$pro->mar_nom.' | Costo: '.$pro->costo_total .' | STOCK: '.$pro->stock,'propun'=>$pro->precio,'contar'=>$pro->cont_pre,'costo'=>$pro->costo_total,'codigo'=>$pro->procod,'unidad'=>$pro->umecod,'pro_rel'=>$pro->pro_rel,"id_almacen"=>$id_almacen,"icbper"=>$pro->icbper,"mon_icbper"=>$pro->mon_icbper];
          
          if($pro->precio2 > 0){
             $results[] = ['id'=>$pro->IdProducto,'producto'=>$pro->pronom.' '.$pro->mar_nom,'text'=>'COD:'.$pro->procod.' | '.$pro->pronom.' '.$pro->mar_nom.' | Precio Mayor: '.$pro->precio2 .' | STOCK: '.$pro->stock,'textcompra'=>'COD:'.$pro->procod.' | '.$pro->pronom.' '.$pro->mar_nom.' | Costo: '.$pro->costo_total .' | STOCK: '.$pro->stock,'propun'=>$pro->precio2,'contar'=>$pro->cont_pre,'costo'=>$pro->costo_total,'codigo'=>$pro->procod,'unidad'=>$pro->umecod,'pro_rel'=>$pro->pro_rel,"id_almacen"=>$id_almacen,"icbper"=>$pro->icbper,"mon_icbper"=>$pro->mon_icbper];
          
          }

          }
          
         
          
         }else{

            
             $results[] = ['id'=>$pro->IdProducto,'producto'=>$pro->pronom.' '.$pro->mar_nom,'text'=>'COD:'.$pro->procod.' | '.$pro->pronom.' '.$pro->mar_nom.' | Precio: '.$pro->precio .' | STOCK: '.$pro->stock,'textcompra'=>'COD:'.$pro->procod.' | '.$pro->pronom.' '.$pro->mar_nom.' | Costo: '.$pro->costo_total .' | STOCK: '.$pro->stock,'propun'=>$pro->precio,'contar'=>$pro->cont_pre,'costo'=>$pro->costo_total,'codigo'=>$pro->procod,'unidad'=>$pro->umecod,'pro_rel'=>$pro->pro_rel,"id_almacen"=>$id_almacen,"icbper"=>$pro->icbper,"mon_icbper"=>$pro->mon_icbper];
          
          if($pro->precio2 > 0){
             $results[] = ['id'=>$pro->IdProducto,'producto'=>$pro->pronom.' '.$pro->mar_nom,'text'=>'COD:'.$pro->procod.' | '.$pro->pronom.' '.$pro->mar_nom.' | Precio Mayor: '.$pro->precio2 .' | STOCK: '.$pro->stock,'textcompra'=>'COD:'.$pro->procod.' | '.$pro->pronom.' '.$pro->mar_nom.' | Costo: '.$pro->costo_total .' | STOCK: '.$pro->stock,'propun'=>$pro->precio2,'contar'=>$pro->cont_pre,'costo'=>$pro->costo_total,'codigo'=>$pro->procod,'unidad'=>$pro->umecod,'pro_rel'=>$pro->pro_rel,"id_almacen"=>$id_almacen,"icbper"=>$pro->icbper,"mon_icbper"=>$pro->mon_icbper];
          
          }
         }


        } 


   

    
       
       
    }
     
       return response()->json($results);


        /* $vista = view('empresas.puntosventas.productos',compact('productos'))->render();

             if($request->ajax()){
         return response()->json(['vista'=>$vista]);

        }*/



    }




//---------------------------------------fin consultar productos compra-----------------------------//


//------------------------------------------consultar productos inventarios --------------------------//


    public function presentacionesproductoinventario(Request $request, $id, $sucursal,$almacen){
         
        $rucemp = trim(Auth::user()->IdEmpresa);

        $productos = DB::tABLE('productos')
         ->select('umecod','productos.procod','productos.pronom','productos.propun','productos.IdProducto','productos.umecod','productos.promocion','productos.color','productos.imagenproducto','precio','precio2',DB::raw("(SELECT stock FROM producto_stock as pro WHERE pro.IdProducto ='".$id."' and pro.id_almacen='".$almacen."' and pro.id_empresa_negocio='".$sucursal."') as stock"),'costo')
        ->join('producto_empresa','producto_empresa.IdProducto','productos.IdProducto')
        ->where(function ($query) use ($id) {
             
                    $query->where('productos.pro_rel',$id);


                })
        ->where('producto_empresa.id_empresa_negocio',$sucursal)
          ->where('tipo','2')
        ->groupby('productos.IdProducto')
        ->orderby('productos.pronom')
        ->orderby('productos.umecod')
        ->get();

   

        $unidades = DB::tABLE('unidad_medida')->where('umeest','=','Activo')
        ->orderBy('umecod','asc')->get();

        $vista = view('empresas.compras.divproductos',compact('productos','unidades'))->render();

        if($request->ajax()){
         return response()->json(['vista'=>$vista]);

        }
    }


     public function consultarmenuinventario(Request $request, $cat_id,$sucursal,$almacen){
         
        $rucemp = trim(Auth::user()->IdEmpresa);

       

        $productos = DB::tABLE('productos')
        ->select('procod','pronom','propun','productos.IdProducto','umecod','promocion','color','imagenproducto','precio2',DB::raw("(SELECT stock FROM producto_stock as pro WHERE productos.IdProducto = pro.IdProducto AND tipo='1' AND id_almacen='".$almacen."' AND id_empresa_negocio='".$sucursal."') as stock"),'precio',DB::raw("(SELECT count(*) FROM productos as pro WHERE productos.IdProducto = pro.pro_rel AND tipo='2') as cont_pre"),'costo')
        ->join('producto_empresa','producto_empresa.IdProducto','productos.IdProducto')
        ->join('producto_stock','producto_stock.IdProducto','producto_empresa.IdProducto')
        ->where('cat_id',$cat_id)
        ->where('tipo','1')
        ->where('producto_empresa.id_empresa_negocio',$sucursal)
        ->where('id_almacen',$almacen)
        ->groupby('productos.IdProducto')
        ->orderby('productos.pronom')
        ->orderby('productos.umecod')
        ->get();


        $unidades = DB::tABLE('unidad_medida')->where('umeest','=','Activo')
        ->orderBy('umecod','asc')->get();

        $vista = view('empresas.combos.menuinventario',compact('productos','unidades'))->render();

        if($request->ajax()){
         return response()->json(['vista'=>$vista]);

        }
    }

    public function busquedaproductoinventario(Request $request, $valor,$sucursal,$almacen){

        $rucemp = trim(Auth::user()->IdEmpresa);

         $productos = DB::tABLE('productos')
        ->select('procod','pronom','propun','productos.IdProducto','umecod','promocion','color','imagenproducto','precio2',DB::raw("(SELECT stock FROM producto_stock as pro WHERE productos.IdProducto = pro.IdProducto AND tipo='1' AND id_almacen='".$almacen."' AND id_empresa_negocio='".$sucursal."') as stock"),'precio',DB::raw("(SELECT count(*) FROM productos as pro WHERE productos.IdProducto = pro.pro_rel AND tipo='2') as cont_pre"),'costo')
        ->leftjoin('producto_codigo','producto_codigo.IdProducto','productos.IdProducto')
        ->leftjoin('producto_stock','producto_stock.IdProducto','productos.IdProducto')
        ->leftjoin('producto_empresa','producto_empresa.IdProducto','productos.IdProducto')
        ->where(function ($query) use($valor){
                $query->where('cod_bar','=',$valor)
                      ->orWhere('productos.pronom', 'like','%'.$valor.'%')
                      ->orWhere('productos.procod',$valor);
        })
        ->where('producto_empresa.id_empresa_negocio',$sucursal)
        ->where('id_almacen',$almacen)
        ->groupby('productos.IdProducto')
        ->orderby('productos.pronom')
        ->orderby('productos.umecod')
        ->get();

        $unidades = DB::tABLE('unidad_medida')->where('umeest','=','Activo')
        ->orderBy('umecod','asc')->get();

        $vista = view('empresas.combos.menuinventario',compact('productos','unidades'))->render();

        if($request->ajax()){
         return response()->json(['vista'=>$vista]);

        }
    }

     public function consultarproductosinventario(Request $request,$sucursal,$almacen){

      $search = $request->get('value');       
      $rucemp = trim(Auth::user()->IdEmpresa);


        $productos = DB::tABLE('productos')
        ->select('procod','pronom','propun','productos.IdProducto','umecod','promocion','color','imagenproducto','precio2',DB::raw("(SELECT stock FROM producto_stock as pro WHERE productos.IdProducto = pro.IdProducto AND tipo='1' AND id_almacen='".$almacen."' AND id_empresa_negocio='".$sucursal."') as stock"),'precio',DB::raw("(SELECT count(*) FROM productos as pro WHERE productos.IdProducto = pro.pro_rel AND tipo='2') as cont_pre"),'costo')
        ->join('producto_codigo','producto_codigo.IdProducto','productos.IdProducto')
        ->join('producto_empresa','producto_empresa.IdProducto','productos.IdProducto')
        ->join('producto_stock','producto_stock.IdProducto','producto_empresa.IdProducto')
        ->where(function ($query) use($search){
            $query->where('cod_bar','=',$search);
         })
        ->where('tipo','1')
        ->where('producto_empresa.id_empresa_negocio',$sucursal)
        ->where('id_almacen',$almacen)
        ->groupby('productos.IdProducto')
        ->orderby('productos.pronom')
        ->orderby('productos.umecod')
        ->get();

 
      $contar = count($productos);

      $results = array();
      foreach($productos as $pro){
        $codnom = $pro->procod;
        $results[] = ['success'=>'true','value'=>$codnom,'pronom'=>$pro->pronom,'propun'=>$pro->propun,'umecod'=>$pro->umecod,'stock'=>$pro->stock,'imagenproducto'=>$pro->imagenproducto,'proid'=>$pro->IdProducto,'contar'=>$contar,'costo'=>$pro->costo];
      }
     
       return response()->json($results);

    }



//---------------------------------------fin consultar inventarios-----------------------------//
    
    public function consultarmenualm(Request $request, $cat_id, $almacen,$sucursal){
         
       $rucemp = trim(Auth::user()->IdEmpresa);

      

        $productos = DB::tABLE('productos')
        ->select('procod','pronom','propun','productos.IdProducto','umecod','promocion','color','imagenproducto','precio2',DB::raw("(SELECT stock FROM producto_stock as pro WHERE productos.IdProducto = pro.IdProducto AND tipo='1' AND id_almacen='".$almacen."' AND id_empresa_negocio='".$sucursal."') as stock"),'precio',DB::raw("(SELECT count(*) FROM productos as pro WHERE productos.IdProducto = pro.pro_rel AND tipo='2') as cont_pre"),'costo')
        ->join('producto_empresa','producto_empresa.IdProducto','productos.IdProducto')
        ->join('producto_stock','producto_stock.IdProducto','producto_empresa.IdProducto')
        ->where('cat_id',$cat_id)
        ->where('tipo','1')
        ->where('producto_empresa.id_empresa_negocio',$sucursal)
        ->where('id_almacen',$almacen)
        ->groupby('productos.IdProducto')
        ->orderby('productos.pronom')
        ->orderby('productos.umecod')
        ->get();



        $unidades = DB::tABLE('unidad_medida')->where('umeest','=','Activo')
        ->orderBy('umecod','asc')->get();

        $vista = view('empresas.combos.menualm',compact('productos','unidades'))->render();

        if($request->ajax()){
         return response()->json(['vista'=>$vista]);

        }
    }


     public function consultarproductosalm(Request $request,$sucursal,$almacen){

      $search = $request->get('value');  
    

      $rucemp = trim(Auth::user()->IdEmpresa);

       $productos = DB::tABLE('productos')
        ->select('procod','pronom','propun','productos.IdProducto','umecod','promocion','color','imagenproducto','precio2',DB::raw("(SELECT stock FROM producto_stock as pro WHERE productos.IdProducto = pro.IdProducto AND tipo='1' AND id_almacen='".$almacen."' AND id_empresa_negocio='".$sucursal."') as stock"),'precio',DB::raw("(SELECT count(*) FROM productos as pro WHERE productos.IdProducto = pro.pro_rel AND tipo='2') as cont_pre"),'costo')
        ->join('producto_codigo','producto_codigo.IdProducto','productos.IdProducto')
        ->join('producto_empresa','producto_empresa.IdProducto','productos.IdProducto')
        ->join('producto_stock','producto_stock.IdProducto','producto_empresa.IdProducto')
        ->where(function ($query) use($search){
            $query->where('cod_bar','=',$search);
         })
        ->where('tipo','1')
        ->where('producto_empresa.id_empresa_negocio',$sucursal)
        ->where('id_almacen',$almacen)
        ->groupby('productos.IdProducto')
        ->orderby('productos.pronom')
        ->orderby('productos.umecod')
        ->get();


      $contar= count($productos);

      $results = array();
      foreach($productos as $pro){
        $codnom = $pro->procod;
        $results[] = ['success'=>'true','value'=>$codnom,'pronom'=>$pro->pronom,'propun'=>$pro->propun,'umecod'=>$pro->umecod,'stock'=>$pro->stock,'imagenproducto'=>$pro->imagenproducto,'proid'=>$pro->IdProducto,'contar'=>$contar,'costo'=>$pro->costo];
      }
     
       return response()->json($results);

    }

    

   

     public function busquedaproductoalm(Request $request, $valor, $almacen,$sucursal){

       $rucemp = trim(Auth::user()->IdEmpresa);

          $productos = DB::tABLE('productos')
        ->select('procod','pronom','propun','productos.IdProducto','umecod','promocion','color','imagenproducto','precio2',DB::raw("(SELECT stock FROM producto_stock as pro WHERE productos.IdProducto = pro.IdProducto AND tipo='1' AND id_almacen='".$almacen."' AND id_empresa_negocio='".$sucursal."') as stock"),'precio',DB::raw("(SELECT count(*) FROM productos as pro WHERE productos.IdProducto = pro.pro_rel AND tipo='2') as cont_pre"),'costo')
        ->leftjoin('producto_codigo','producto_codigo.IdProducto','productos.IdProducto')
        ->leftjoin('producto_stock','producto_stock.IdProducto','productos.IdProducto')
        ->leftjoin('producto_empresa','producto_empresa.IdProducto','productos.IdProducto')
        ->where(function ($query) use($valor){
                $query->where('cod_bar','=',$valor)
                      ->orWhere('productos.pronom', 'like','%'.$valor.'%')
                      ->orWhere('productos.procod',$valor);
        })
        ->where('producto_empresa.id_empresa_negocio',$sucursal)
        ->where('id_almacen',$almacen)
        ->groupby('productos.IdProducto')
        ->orderby('productos.pronom')
        ->orderby('productos.umecod')
        ->get();



        $unidades = DB::tABLE('unidad_medida')->where('umeest','=','Activo')
        ->orderBy('umecod','asc')->get();

        $vista = view('empresas.combos.menualm',compact('productos','unidades'))->render();

        if($request->ajax()){
         return response()->json(['vista'=>$vista]);

        }
    }


     public function consultarmenucomanda(Request $request, $cat_id){
        $rucemp = trim(Auth::user()->IdEmpresa);

        $sucursal = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first();

        $almacen = DB::tABLE('almacenes')
        ->where('predeterminado','1')
        ->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
        ->first();



  
            $productos = DB::tABLE('productos')
            ->select('lab_nom','productos.lab_id','pro_rel','costo','mar_nom','procod','pronom','propun','productos.IdProducto','umecod','promocion','color','imagenproducto','precio2','precio3',DB::raw("(SELECT stock FROM producto_stock as pro WHERE productos.IdProducto = pro.IdProducto AND id_almacen='".$almacen->id_almacen."' AND pro.id_empresa_negocio='".$almacen->id_empresa_negocio."') as stock"),'precio',DB::raw("(SELECT count(*) FROM productos as pro WHERE pro.pro_rel =  productos.IdProducto) as cont_pre"))
            ->join('producto_empresa','producto_empresa.IdProducto','productos.IdProducto')
            ->leftjoin('laboratorio','laboratorio.lab_id','productos.lab_id')
          //  ->join('producto_stock','producto_stock.IdProducto','producto_empresa.IdProducto')
          ->leftjoin('marcas','marcas.mar_id','productos.marca')
        ->where(function ($query) use($cat_id){
                $query->where('cat_id',$cat_id);
          })
            ->where('tipo','1')
            ->where('promocion','!=','2')
           // ->where('producto_empresa.id_empresa_negocio',Auth::user()->id_empresa_negocio)
           // ->where('id_almacen',$almacen->id_almacen)
            ->groupby('productos.IdProducto')
            ->orderby('productos.pronom')
            ->orderby('productos.umecod')
            ->get();



      $contar = count($productos);


        $unidades = DB::tABLE('unidad_medida')->where('umeest','=','Activo')
        ->orderBy('umecod','asc')->get();

        $vista = view('empresas.combos.menucomanda',compact('productos','unidades'))->render();

        if($request->ajax()){
         return response()->json(['vista'=>$vista]);

        }
    }

    
     public function consultarmenucobrar(Request $request, $cat_id){
        $rucemp = trim(Auth::user()->IdEmpresa);
        $productos = DB::tABLE('productos')
        ->select('procod','pronom','propun','IdProducto','umecod','promocion','color','imagenproducto')
        ->where('cat_id',$cat_id)->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
        ->where(function ($query){
                $query->where('promocion','1')
                ->orWhere('promocion','0')
                      ->orWhere('promocion','2');
        })
        ->get();

        $unidades = DB::tABLE('unidad_medida')->where('umeest','=','Activo')
        ->orderBy('umecod','asc')->get();

        $vista = view('empresas.combos.menucobrar',compact('productos','unidades'))->render();

        if($request->ajax()){
         return response()->json(['vista'=>$vista]);

        }
    }


   
    public function stockproductos(Request $request)
    {   
   
          $tipo = $request->get('promocion');
          $categoria = $request->get('cmbCatId');
          $sucursal = $request->get('sucursal');
          $negocios = DB::tABLE('empresa_negocios')->get();
          $almacen = $request->get('almacen');
          $datosalm ="";

            $rucemp = trim(Auth::user()->IdEmpresa);
            $buspro = trim($request->get('buspro'));
            
            if(empty($sucursal)){

              $productos = DB::tABLE('productos as p')
                ->select('lote','vencimiento','producto_stock.stock_inicial','producto_stock.stock','p.procod','p.pronom','m.monnom','u.umenom','p.propun','p.proest','producto_stock.IdProducto','p.costo','p.propun','mar.mar_nom as marca','modelo')
                ->leftjoin('moneda as m','p.moncod','=','m.moncod')
         ->leftjoin('marcas as mar','mar.mar_id','=','p.marca')
                ->leftjoin('unidad_medida as u','p.umecod','=','u.umecod')
                ->leftjoin('producto_empresa','producto_empresa.IdProducto','p.IdProducto')
                ->leftjoin('producto_stock','producto_stock.IdProducto','p.IdProducto')
                ->where('producto_stock.id_empresa_negocio',$negocios->first()->id_empresa_negocio)
                ->where('id_almacen','0')
                ->where('tipo','1')
                ->groupby('p.IdProducto')
                ->orderby('stock','asc')
                ->paginate(100);
         

            }else{

           

                $productos = DB::tABLE('productos as p')
                ->select('lote','vencimiento','producto_stock.stock_inicial','producto_stock.stock','p.procod','p.pronom','m.monnom','u.umenom','p.propun','p.proest','producto_stock.IdProducto','mar.mar_nom as marca','p.costo','p.propun','modelo')
                ->leftjoin('moneda as m','p.moncod','=','m.moncod')
         ->leftjoin('marcas as mar','mar.mar_id','=','p.marca')
                ->leftjoin('unidad_medida as u','p.umecod','=','u.umecod')
                ->leftjoin('producto_empresa','producto_empresa.IdProducto','p.IdProducto')
                ->leftjoin('producto_stock','producto_stock.IdProducto','p.IdProducto')
                ->where(function ($query) use($categoria) {

                  if($categoria!='Todos'){
                    $query->where('cat_id',$categoria);
                  }
                  
                })
                ->where(function ($query1) use($buspro) {

                  if(!empty($buspro)){
                    $query1->where('pronom','like','%'.$buspro.'%')
                            ->orwhere('procod',$buspro);
                  }
                  
                })
                ->where('producto_stock.id_empresa_negocio',$sucursal)
                ->where('tipo','1')
                ->where('id_almacen',$almacen)
                ->groupby('p.IdProducto')
                ->orderby('stock','asc')
                ->paginate(100);

                $datosalm = DB::tABLE('almacenes')->where('id_almacen',$almacen)->first();

            }
                

            $categorias = DB::tABLE('categorias')->get();

            $buspro="";

            $tipos_productos = DB::tABLE('tipos_productos')->get();

            $datos = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',$sucursal)->first();

            if(empty($sucursal)){

               $almacenes = DB::tABLE('almacenes')->where('id_empresa_negocio',$negocios->first()->id_empresa_negocio)->get();

            }else{

               $almacenes = DB::tABLE('almacenes')->where('id_empresa_negocio',$sucursal)->get();
            }

           

            return view('empresas.combos.stockproducto',compact('productos','buspro','negocios','categorias','tipos_productos','categoria','tipo','sucursal','datos','almacenes','almacen','datosalm'));
      

    }

    public function consultastockproductos(Request $request)
    {   
   
          $tipo = $request->get('promocion');
          $categoria = $request->get('cmbCatId');

          $buspro = trim($request->get('buspro'));
            
            if(empty($sucursal)){

              $productos = DB::tABLE('productos as p')
                ->select('lote','vencimiento','producto_stock.stock_inicial','producto_stock.stock','p.procod','p.pronom','m.monnom','u.umenom','p.propun','p.proest','producto_stock.IdProducto','p.costo','p.propun','mar.mar_nom as marca','modelo')
                ->leftjoin('moneda as m','p.moncod','=','m.moncod')
         ->leftjoin('marcas as mar','mar.mar_id','=','p.marca')
                ->leftjoin('unidad_medida as u','p.umecod','=','u.umecod')
                ->leftjoin('producto_empresa','producto_empresa.IdProducto','p.IdProducto')
                ->leftjoin('producto_stock','producto_stock.IdProducto','p.IdProducto')
               
                ->where('id_almacen','0')
                ->where('tipo','1')
                ->groupby('p.IdProducto')
                ->orderby('stock','asc')
                ->paginate(100);
         

            }else{

           

                $productos = DB::tABLE('productos as p')
                ->select('lote','vencimiento','producto_stock.stock_inicial','producto_stock.stock','p.procod','p.pronom','m.monnom','u.umenom','p.propun','p.proest','producto_stock.IdProducto','mar.mar_nom as marca','p.costo','p.propun','modelo')
                ->leftjoin('moneda as m','p.moncod','=','m.moncod')
         ->leftjoin('marcas as mar','mar.mar_id','=','p.marca')
                ->leftjoin('unidad_medida as u','p.umecod','=','u.umecod')
                ->leftjoin('producto_empresa','producto_empresa.IdProducto','p.IdProducto')
                ->leftjoin('producto_stock','producto_stock.IdProducto','p.IdProducto')
                ->where(function ($query) use($categoria) {

                  if($categoria!='Todos'){
                    $query->where('cat_id',$categoria);
                  }
                  
                })
                ->where(function ($query1) use($buspro) {

                  if(!empty($buspro)){
                    $query1->where('pronom','like','%'.$buspro.'%')
                            ->orwhere('procod',$buspro);
                  }
                  
                })
                
                ->where('tipo','1')
              
                ->groupby('p.IdProducto')
                ->orderby('stock','asc')
                ->paginate(100);

              
            }
                

            $categorias = DB::tABLE('categorias')->get();

            $buspro="";

            $tipos_productos = DB::tABLE('tipos_productos')->get();

           
          
           

            return view('empresas.combos.consultastockproducto',compact('productos','buspro','categorias','tipos_productos','categoria','tipo'));
      

    }


    public function exportarstockproductos(Request $request){

          $tipo = $request->get('promocion');
          $categoria = $request->get('cmbCatId');
          $sucursal = $request->get('sucursal');
          $negocios = DB::tABLE('empresa_negocios')->get();
          $almacen = $request->get('almacen');
          $datosalm ="";
          $buspro="";


            $rucemp = trim(Auth::user()->IdEmpresa);
            $empresa = Empresa::findOrFail($rucemp);

            $buspro = trim($request->get('buspro'));
            
             $categorias = DB::tABLE('categorias')->get();

           
            $tipos_productos = DB::tABLE('tipos_productos')->get();

            $datos = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',$negocios->first()->id_empresa_negocio)->first();

            if(empty($sucursal)){

               $almacenes = DB::tABLE('almacenes')->where('id_empresa_negocio',$negocios->first()->id_empresa_negocio)->get();

            }else{

               $almacenes = DB::tABLE('almacenes')->where('id_empresa_negocio',$sucursal)->get();
            }





                $productos = DB::tABLE('productos as p')
                ->select('producto_stock.stock_inicial','producto_stock.stock','p.procod','p.pronom','m.monnom','u.umenom','p.proest','producto_stock.IdProducto','mar.mar_nom as marca','modelo')
                ->leftjoin('moneda as m','p.moncod','=','m.moncod')
                ->leftjoin('marcas as mar','mar.mar_id','=','p.marca')
                ->leftjoin('unidad_medida as u','p.umecod','=','u.umecod')
                ->leftjoin('producto_stock','producto_stock.IdProducto','p.IdProducto')
                ->where(function ($query) use($categoria) {

                  if($categoria!='Todos'){
                    $query->where('cat_id',$categoria);
                  }
                  
                })
                ->where(function ($query1) use($buspro) {

                  if(!empty($buspro)){
                    $query1->where('pronom','like','%'.$buspro.'%')
                            ->orwhere('procod',$buspro);
                  }
                  
                })
                ->where('producto_stock.id_empresa_negocio',$sucursal)
                ->where('tipo','1')
                 ->where('promocion','!=','2')
                ->where('id_almacen',$almacen)
                ->groupby('p.IdProducto')
                ->orderby('pronom','asc')
                ->get();

                $datosalm = DB::tABLE('almacenes')->where('id_almacen',$almacen)->first();

                

           
             $nompdffile='STOCK_PRODUCTOS_'.$datos->IdEmpresa.'.pdf'; 

           

              $rutapdf = public_path().'/reporte_cuentas_cobrar/';

                 if(file_exists($rutapdf.$nompdffile)){
                  unlink($rutapdf.$nompdffile);
                 }

                 ini_set("pcre.backtrack_limit", "5000000");

             
             $pdf = PDF::loadView('formatos_reportes.reporte_pdf_stock_productos',compact('productos','buspro','negocios','categorias','tipos_productos','categoria','tipo','sucursal','datos','almacenes','almacen','datosalm','empresa'));
             return $pdf->stream('document.pdf');

          


                $headers = array(
                        'Content-Type: application/pdf',
                      );



              //  return response()->download($rutapdf.$nompdffile);
  





    }


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

          return view('empresas.combos.inventarioseditar',compact('buspro','negocios','categorias','tipos_productos','categoria','tipo','sucursal','datos','almacenes','almacen','datosalm','fecha','unidades','productoslista','id'));


    }


     public function inventarios(Request $request,$sucursal=0,$almacen=0,$tipo=0)
    {   
   

          $categoria = $request->get('cmbCatId');
         // $sucursal = $request->get('sucursal');
          $negocios = DB::tABLE('empresa_negocios')->get();
         // $almacen = $request->get('almacen');
          $datosalm ="";


          $fecha = $request->get('fecha');

          if(empty($fecha)){
            $fecha = now()->format('Y-m-d');
          }

            $rucemp = trim(Auth::user()->IdEmpresa);
            $buspro = trim($request->get('buspro'));
            
            if($sucursal!='0'){

            
              if($tipo=='1'){

                $productos = DB::tABLE('inventario_cabecera')
                ->leftjoin('users','users.IdUsuario','inventario_cabecera.IdUsuario')
                ->leftjoin('almacenes','almacenes.id_almacen','inventario_cabecera.id_almacen')
                ->where('inventario_cabecera.id_empresa_negocio',$sucursal)
                ->where('inv_fec',$fecha)
                ->where('inventario_cabecera.id_almacen',$almacen)
                ->get();

                $datosalm = DB::tABLE('almacenes')->where('id_almacen',$almacen)->first();

              }elseif($tipo=='2'){

                $productos = DB::tABLE('productos as p')
                ->select('p.procod','p.pronom','m.monnom','u.umenom','p.propun','p.proest','producto_empresa.IdProducto','p.costo','p.propun','marca','modelo')
                ->leftjoin('moneda as m','p.moncod','=','m.moncod')
                ->leftjoin('unidad_medida as u','p.umecod','=','u.umecod')
                ->leftjoin('producto_empresa','producto_empresa.IdProducto','p.IdProducto')
               // ->leftjoin('producto_stock','producto_stock.IdProducto','p.IdProducto')
                ->where('producto_empresa.id_empresa_negocio',$sucursal)
            
              //  ->where('id_almacen',$almacen)
                ->groupby('p.IdProducto')
                ->orderby('pronom','asc')
                ->get();

                $datosalm = DB::tABLE('almacenes')->where('id_almacen',$almacen)->first();

              }
                

            }

           

            $categorias = DB::tABLE('categorias')->get();

            $buspro="";

            $tipos_productos = DB::tABLE('tipos_productos')->get();

            $datos = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',$sucursal)->first();

            if(empty($sucursal)){

               $almacenes = DB::tABLE('almacenes')->where('id_empresa_negocio',$negocios->first()->id_empresa_negocio)->get();

            }else{

               $almacenes = DB::tABLE('almacenes')->where('id_empresa_negocio',$sucursal)->get();
            }

        

            return view('empresas.combos.inventarios',compact('buspro','negocios','categorias','tipos_productos','categoria','tipo','sucursal','datos','almacenes','almacen','datosalm','fecha','productos'));
      

    }

      public function BuscarProducto(Request $request){

        $buspro = $request->get('value');       
        $emp_id = trim(Auth::user()->IdEmpresa);

        $productos= DB::tABLE('productos')
        ->join('unidad_medida as cum','cum.umecod','productos.umecod')
        ->where('IdProducto',$buspro)
        ->where('productos.IdEmpresa',$emp_id)
        ->get();


        $results = array();
        
        foreach($productos as $pro){
          $results[] = ['success'=>'true','value'=>$pro->pronom,'prod_id'=>$pro->IdProducto,'prod_cost'=>$pro->costo,'prod_pun'=>$pro->propun,'prod_stock'=>$pro->stock,'unidad'=>$pro->umenom,'costo'=>$pro->costo_total,'precio'=>$pro->propun];
        }
     
       return response()->json($results);
    }



    public function ajustar_stock($id_almacen,$IdProducto){

        $productos = productos::findOrFail($IdProducto);
        $almacen = DB::tABLE('almacenes')->where('id_almacen',$id_almacen)->first();
        $sucursal = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',$almacen->id_empresa_negocio)->first();

        return view('empresas.combos.ajustar_stock',compact('productos','almacen','sucursal'));

    }
  
     public function registrar_ajustar_stock(Request $request)
    {   

            $sucursal = $request->get('suc_id');
            $almacen = $request->get('alm_id');
            $cant = $request->get('cantidad');
      
            $IdProducto = $request->get('IdProducto');

             
            $bus_stock = DB::tABLE('producto_stock')->where('IdProducto',$IdProducto)->where('id_almacen',$almacen)->first();

            $cantidad = $cant - $bus_stock->stock;

            

              if($cantidad<0){

                 $cantidad = $cantidad*(-1);
                 $mov_tipo = 'E';
                 $tipo = '2';

                $tdocod = '84';

                $senudoc = DB::tABLE('empresa_negocios')->select('AESer','AENum')->where('id_empresa_negocio',$sucursal)->first();
                $numdoc =  $senudoc->AENum+1;
                $sercomp =  $senudoc->AESer;


                $emp_nego = EmpresaNegocios::findOrFail($sucursal);
                $emp_nego->AENum = $numdoc;
                $emp_nego->update();


    
              }else{

                $cantidad = $cantidad;
                $mov_tipo = 'I';
                $tipo = '3';

                $tdocod = '83';
                $senudoc = DB::tABLE('empresa_negocios')->select('AISer','AINum')->where('id_empresa_negocio',$sucursal)->first();
                $numdoc =  $senudoc->AINum+1;
                $sercomp =  $senudoc->AISer;


                $emp_nego = EmpresaNegocios::findOrFail($sucursal);
                $emp_nego->AINum = $numdoc;
                $emp_nego->update();




              }


             DB::tABLE('producto_stock')
             ->where('IdProducto',$IdProducto)
             ->update(['stock'=>$cant,
              'stock_inicial'=>$cant]);



               DB::tABLE('movimientos_productos')->insert([
                    'IdProducto'=>$IdProducto,
                    'precio'=>'',
                    'cantidad'=>$cantidad,
                    'costo'=>'',
                    'mov_cab_id'=>'',
                    'stock'=>'',
                    'IdProducto_rel'=>$IdProducto,
                    'IdCpe_cabecera'=>'',
                    'com_cab_id'=>'',
                    'stock_inicial'=>'',
                    'serie'=>$sercomp,
                    'numero'=>$numdoc,
                    'tdocod'=>$tdocod,
                    'tipo'=>$tipo,
                    'mov_tip'=>$mov_tipo,
                    'descripcion'=>'AJUSTE',
                    'id_empresa_negocio'=>$sucursal,
                    'id_almacen'=>$almacen,
                    'fecha_mov'=>now()->format('Y-m-d'),
            ]);




        return Redirect::to('/stockproductos');


    }


    public function consultarcategorias(Request $request){
        $rucemp = trim(Auth::user()->IdEmpresa);
        $categorias = DB::tABLE('categorias')
        ->select('cat_id','cat_nom','color')
        ->orderby('cat_nom','asc')
        ->get();

        $vista = view('empresas.combos.listacategorias',compact('categorias'))->render();

        if($request->ajax()){
         return response()->json(['vista'=>$vista]);

        }
    }
    

    

   
    public function costeo(request $request,$id,$suc){

        $rucemp = trim(Auth::user()->IdEmpresa);

        
        $bus_pro = DB::tABLE('productos')->where('IdProducto',$id)->first();

        if(empty($bus_pro->pro_rel)){
           $id = $bus_pro->IdProducto;
        }else{
           $id = $bus_pro->pro_rel;
        }

        $productos = DB::tABLE('productos')
        ->select('procod','pronom','productos.IdProducto','umecod','promocion','color','imagenproducto','precio','precio2','costo')
        ->leftjoin('producto_empresa','producto_empresa.IdProducto','productos.IdProducto')
        ->where('producto_empresa.id_empresa_negocio',$suc)
        ->where(function ($query) use ($id) {
             
                    $query->where('productos.IdProducto',$id)
                          ->orwhere('productos.pro_rel',$id);


                })
         ->groupby('IdProducto')
         ->orderby('productos.pronom')
         ->orderby('productos.umecod')
          ->get();


     

        $vista = view('empresas.compras.divcosteo',compact('productos','suc'))->render();

        if($request->ajax()){
         return response()->json(['vista'=>$vista]);

        }
    }

    
    public function actualizarprecios(Request $request){

       $productos = $request->get('idpro');
       $sucursal = $request->get('sucursal');
       $precios = $request->get('precio');
       $precios2 = $request->get('precio2');
       $costnue = $request->get('costnue');
       
       foreach ($productos as $i => $pro) {
          
          if(!empty($precios[$i]) || $precios[$i]=='0'){

             DB::tABLE('producto_empresa')
            ->where('IdProducto',$pro)
            ->where('id_empresa_negocio',$sucursal)
            ->update(['precio'=>$precios[$i]]);

          }

          if(!empty($precios2[$i]) || $precios2[$i]=='0'){

            DB::tABLE('producto_empresa')
            ->where('IdProducto',$pro)
            ->where('id_empresa_negocio',$sucursal)
            ->update(['precio2'=>$precios2[$i]]);

          }

//          if(!empty($costact[$i]) || $costact[$i]=='0'){

 //           DB::tABLE('productos')
 //           ->where('IdProducto',$pro)
 //           ->update(['costo'=>$precios2[$i]]);

  //        }


          if(!empty($costnue[$i]) || $costnue[$i]=='0'){

            DB::tABLE('productos')
            ->where('IdProducto',$pro)
            ->update(['costo'=>$costnue[$i]]);

          }
         
       }

        if($request->ajax()){
         return response()->json(['mensaje'=>'Precios Modificados']);

        }
    }

    public function exportar_productos_inventario(Request $request, $sucursal, $alm){

       
    
              $negocio = DB::tABLE('empresa_negocios')
              ->where('id_empresa_negocio',$sucursal)
              ->first();

              $almacen = DB::tABLE('almacenes')->where('id_almacen',$alm)->first();

              $productos = DB::tABLE('productos')
              ->join('producto_stock','producto_stock.IdProducto','productos.IdProducto')
              ->join('producto_empresa','producto_empresa.IdProducto','productos.IdProducto')
              ->where('producto_stock.id_empresa_negocio',$sucursal)
              ->where('producto_stock.id_almacen',$alm)
              ->get();



    

         
                        Excel::create('INVENTARIO_PRODUCTOS', function($excel) use ($productos,$negocio,$almacen) {

                        $excel->sheet('Comprobantes', function($sheet) use ($productos,$negocio,$almacen) {

                            /*$sheet->setColumnFormat(array(
                                    'A' => 'dd/mm/yy',
                                    'G' => '0.00',
                                    'H' => '0.00',
                                    'I' => '0.00',
                                    'J' => '0.00',

                                    
                                ));*/
                                
                                $sheet->loadView('empresas.reportes.reporte_productos_inventarios',compact('productos','negocio','almacen'));

                        });

                    })->export('xlsx'); 
    
        }

   public function  importar_inventario(Request $request)
    {


      try{
        if(empty($request->get('almacenimport'))){
        $almacen = $request->get('almacen');
      }else{
         $almacen = $request->get('almacenimport');
      }
       

        

       $sucursal = $request->get('sucursalimport');
       $fecha = $request->get('fechaimport');

      $cabecera = DB::tABLE('inventario_cabecera')
      ->insert(['inv_fec'=>$fecha,
                'IdUsuario'=>Auth::user()->IdUsuario,
                'id_empresa_negocio'=>$sucursal,
                'id_almacen'=>$almacen
              ]);

      $inv_cab_id = DB::getPdo()->lastInsertId();

        \Excel::load(Input::file('archivo'), function($reader) use($sucursal,$almacen,$inv_cab_id)  {

            $excel = $reader->get()->toArray();;

            // iteracción
           //  $reader->each(function($row)  use($producto,$rucemp) {

            foreach ($excel as $key => $value) {

              
                $buscarproducto = DB::tABLE('productos')->where('IdProducto',$value['id'])
                ->first();

                if(!empty($buscarproducto)){

                  if(empty($buscarproducto->pro_rel)){
                    $id = $value['id'];
                    $stock = $value['cantidad']*$buscarproducto->factor;
                  }else{
                       $id = $buscarproducto->pro_rel;
                   $stock = $value['cantidad']*$buscarproducto->factor;
             
                  }
               
                
                    
                    $buscar_stock = DB::tABLE('producto_stock')
                    ->where('IdProducto',$id)
                    ->where('producto_stock.id_empresa_negocio',$sucursal)
                    ->where('producto_stock.id_almacen',$almacen)
                    ->update(['producto_stock.stock'=>$stock,'producto_stock.stock_inicial'=>$value['stock_inicial']]);

                    DB::tABLE('inventario_detalle')
                    ->insert(['inv_cab_id'=>$inv_cab_id,
                      'IdProducto'=>$id,
                      'inv_sto_ini'=>$value['stock_inicial'],
                      'inv_can'=>$stock,
                      'inv_costo'=>$value['costo']
                    ]);
                    }
                   

                }
        
        
        });



      }catch(\Exception $e){
          dd($e);
      }
      

           
        return Redirect::to('/inventarios');      
    }




 public function stock_inicial(){

      $productos = DB::tABLE('productos')->where('tipo','1')->get();

      $almacen = DB::tABLE('almacenes')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->where('predeterminado','1')->first();

      foreach ($productos as $key => $pro) {

                DB::tABLE('movimientos_productos')->insert([
                        'IdProducto'=>$pro->IdProducto,
                        'precio'=>$pro->propun,
                        'cantidad'=>$pro->stock_migrar,
                        'costo'=>$pro->costo,
                        'mov_cab_id'=>'',
                        'stock'=>$pro->stock_migrar,
                        'IdProducto_rel'=>$pro->IdProducto,
                        'IdCpe_cabecera'=>'',
                        'com_cab_id'=>'',
                        'stock_inicial'=>$pro->stock_migrar,
                        'serie'=>'',
                        'numero'=>'',
                        'tdocod'=>'',
                        'tipo'=>'I',
                        'descripcion'=>'STOCK INICIAL',
                        'id_empresa_negocio'=>Auth::user()->id_empresa_negocio,
                        'id_almacen'=>$almacen->id_almacen,
                        'fecha_mov'=>'2021-07-01',
                    ]);


      }


           

 }


 public function calcular_stock(){


      $ventas = DB::tABLE('cpe_detalle')
      ->join('cpe_cabecera','cpe_cabecera.IdCpe_cabecera','cpe_detalle.IdCpe_cabecera')
      ->where(function ($query) {
          $query->where('tdocod','01')
              ->orWhere('tdocod','03')
              ->orWhere('tdocod','13')
              ->orWhere('tdocod','14');
          })
      ->where('cpe_cabecera.id_empresa_negocio',Auth::user()->id_empresa_negocio)
      ->orderby('ccafem','asc')
      ->get();


      $compras = DB::tABLE('compras_cabecera')
      ->join('compras_detalle','compras_detalle.com_cab_id','compras_cabecera.com_cab_id')
      ->leftjoin('proveedor','proveedor.prov_id','compras_cabecera.prov_id')
      ->where(function ($query) {
          $query->where('tdocod','01')
              ->orWhere('tdocod','03')
              ->orWhere('tdocod','13')
              ->orWhere('tdocod','14');
          })
      ->where('compras_cabecera.id_empresa_negocio',Auth::user()->id_empresa_negocio)
      ->orderby('com_fec','asc')
      ->get();



      foreach ($compras as $comp) {

                DB::tABLE('movimientos_productos')->insert([

                        'IdProducto'=>$comp->pro_id,
                        'precio'=>'',
                        'cliente'=>$comp->prov_raz,
                        'cantidad'=>$comp->cantidad,
                        'costo'=>$comp->pre_uni,
                        'mov_cab_id'=>'',
                        'stock'=>$comp->com_det_stock,
                        'IdProducto_rel'=>$comp->IdProducto_rel,
                        'IdCpe_cabecera'=>'',
                        'com_cab_id'=>$comp->com_cab_id,
                        'stock_inicial'=>$comp->com_det_stock_inicial,
                        'serie'=>$comp->com_doc_ser,
                        'numero'=>$comp->com_doc_num,
                        'tdocod'=>$comp->tdocod,
                        'tipo'=>'2',
                        'descripcion'=>'COMPRAS',
                        'id_empresa_negocio'=>Auth::user()->id_empresa_negocio,
                        'id_almacen'=>$comp->id_almacen,
                        'fecha_mov'=>$comp->com_fec_ing,

                    ]);

      }

      foreach ($ventas as $ven) {
        
        try{
               DB::tABLE('movimientos_productos')->insert([
                                'IdProducto'=>$ven->IdProducto,
                                'precio'=>$ven->cdepuni,
                                'cliente'=>$ven->ccanom,
                                'cantidad'=>$ven->cdecan,
                                'costo'=>$ven->costo,
                                'mov_cab_id'=>'',
                                'stock'=>$ven->cpe_det_stock,
                                'IdProducto_rel'=>$ven->IdProducto_rel,
                                'IdCpe_cabecera'=>$ven->IdCpe_cabecera,
                                'com_cab_id'=>'',
                                'stock_inicial'=>$ven->cpe_det_stock_inicial,
                                'serie'=>$ven->serdoc,
                                'numero'=>$ven->numdoc,
                                'tdocod'=>$ven->tdocod,
                                'tipo'=>'3',
                                'descripcion'=>'VENTAS',
                                'id_empresa_negocio'=>Auth::user()->id_empresa_negocio,
                                'id_almacen'=>$ven->id_almacen,
                                'fecha_mov'=>$ven->ccafem,


                           ]);
        }catch(\Exception $e){


        }
      
        
        }


      }



      /*---------------------------------------------RESTAURANTE------------------------------------------------------*/
         public function buscarcarta(Request $request, $id=0,$cat=0){
     
            $productos = DB::tABLE('productos')
            ->join('producto_empresa','producto_empresa.IdProducto','productos.IdProducto')
            ->where(function ($query) use($id){
                if($id<>'0'){
                  $query->where('procod','=',$id)
                        ->orWhere('pronom', 'like','%'.$id.'%'); 
                }
            })
            ->where(function ($query) use($cat){
                if($cat<>'0'){
                  $query->where('cat_id','=',$cat);
                }
            })

            ->where('promocion','!=','4')
            ->orderby('pronom','asc')
            ->where('producto_empresa.id_empresa_negocio',Auth::user()->id_empresa_negocio)
            ->get();

            $data_cat = DB::tABLE('categorias')->where('cat_id',$cat)->first();

            $vista = view('empresas.combos.items_productos',compact('productos','data_cat'))->render();

            if($request->ajax()){
             return response()->json(['vista'=>$vista]);

            }
        }

        public function buscarcartallevar(Request $request, $id=0,$cat=0){
     
               $productos = DB::tABLE('productos')
            ->join('producto_empresa','producto_empresa.IdProducto','productos.IdProducto')
            ->where(function ($query) use($id){
                if($id<>'0'){
                  $query->where('procod','=',$id)
                        ->orWhere('pronom', 'like','%'.$id.'%'); 
                }
            })
            ->where(function ($query) use($cat){
                if($cat<>'0'){
                  $query->where('cat_id','=',$cat);
                }
            })

            ->where('promocion','!=','4')
            ->orderby('pronom','asc')
            ->where('producto_empresa.id_empresa_negocio',Auth::user()->id_empresa_negocio)
            ->get();

            $data_cat = DB::tABLE('categorias')->where('cat_id',$cat)->first();

            $vista = view('empresas.combos.items_productos',compact('productos','data_cat'))->render();

            if($request->ajax()){
             return response()->json(['vista'=>$vista]);

            }
        }




      /*--------------------------------------------------------------------------------------------------------------*/



      public function  importar_ajuste(Request $request)
    {

        try{

        \Excel::load(Input::file('archivo'), function($reader) {

            $excel = $reader->get()->toArray();

          
            foreach ($excel as $key => $value) {

              
               $buscar_almacen = DB::tABLE('almacenes')
               ->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
               ->where('predeterminado','1')->first();


               $cant = $value['stock'];
               $codigo = $value['codigo'];

               $bus_pro = productos::where('procod',$codigo)->where('tipo','1')->first();

               $bus_stock = DB::tABLE('producto_stock')->where('IdProducto',$bus_pro->IdProducto)->where('id_almacen',$buscar_almacen->id_almacen)->first();

               $cantidad = $cant - $bus_stock->stock;

            

              if($cantidad<0){

                 $cantidad = $cantidad*(-1);
                 $mov_tipo = 'E';
                 $tipo = '2';

                $tdocod = '84';

                $senudoc = DB::tABLE('empresa_negocios')->select('AESer','AENum')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first();
                $numdoc =  $senudoc->AENum+1;
                $sercomp =  $senudoc->AESer;


                $emp_nego = EmpresaNegocios::findOrFail(Auth::user()->id_empresa_negocio);
                $emp_nego->AENum = $numdoc;
                $emp_nego->update();


    
              }else{

                $cantidad = $cantidad;
                $mov_tipo = 'I';
                $tipo = '3';

                $tdocod = '83';
                $senudoc = DB::tABLE('empresa_negocios')->select('AISer','AINum')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first();
                $numdoc =  $senudoc->AINum+1;
                $sercomp =  $senudoc->AISer;


                $emp_nego = EmpresaNegocios::findOrFail(Auth::user()->id_empresa_negocio);
                $emp_nego->AINum = $numdoc;
                $emp_nego->update();




              }


             DB::tABLE('producto_stock')
             ->where('IdProducto',$bus_pro->IdProducto)
             ->update(['stock'=>$cant,
              'stock_inicial'=>$cant]);



               DB::tABLE('movimientos_productos')->insert([
                    'IdProducto'=>$bus_pro->IdProducto,
                    'precio'=>'',
                    'cantidad'=>$cantidad,
                    'costo'=>'',
                    'mov_cab_id'=>'',
                    'stock'=>'',
                    'IdProducto_rel'=>$bus_pro->IdProducto,
                    'IdCpe_cabecera'=>'',
                    'com_cab_id'=>'',
                    'stock_inicial'=>'',
                    'serie'=>$sercomp,
                    'numero'=>$numdoc,
                    'tdocod'=>$tdocod,
                    'tipo'=>$tipo,
                    'mov_tip'=>$mov_tipo,
                    'descripcion'=>'AJUSTE',
                    'id_empresa_negocio'=>Auth::user()->id_empresa_negocio,
                    'id_almacen'=>$buscar_almacen->id_almacen,
                    'fecha_mov'=>now()->format('Y-m-d'),
            ]);





            }
        
        
        });

      }catch(\Exception $e){
       dd($e);
      }
      

           
        return Redirect::to('/productos');      
    }




        public function consultar_catalogo(Request $request){


      $search = $request->get('bus_cata'); 

        $bus_suc = DB::tABLE('empresa_negocios')
       ->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
       ->first();

       $bus_alm = DB::tABLE('almacenes')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->where('predeterminado','1')->first();
      $id_almacen = $bus_alm->id_almacen;
       
      $rucemp = trim(Auth::user()->IdEmpresa);

       $bus_alm = DB::tABLE('almacenes')
        ->where('id_almacen',$id_almacen)

        ->first();


   
  
            $catalogo = DB::tABLE('productos')
            ->select('imagenproducto','costo_total','lab_nom','productos.lab_id','pro_rel','costo','mar_nom','procod','pronom','propun','productos.IdProducto','umecod','promocion','color','imagenproducto','precio2','precio3',DB::raw("(SELECT stock FROM producto_stock as pro WHERE productos.IdProducto = pro.IdProducto AND id_almacen='".$bus_alm->id_almacen."' AND pro.id_empresa_negocio='".$bus_alm->id_empresa_negocio."') as stock"),'precio',DB::raw("(SELECT count(*) FROM productos as pro WHERE pro.pro_rel =  productos.IdProducto) as cont_pre"),'icbper','mon_icbper')
            ->join('producto_empresa','producto_empresa.IdProducto','productos.IdProducto')
            ->leftjoin('laboratorio','laboratorio.lab_id','productos.lab_id')
          //  ->join('producto_stock','producto_stock.IdProducto','producto_empresa.IdProducto')
          ->leftjoin('marcas','marcas.mar_id','productos.marca')
        ->where(function ($query) use($search){
                $query->where('pronom','like','%'.$search.'%')
          ->orwhere('codigo_barra',$search)
          ->orwhere('procod',$search);
          })
           
            ->where('promocion','!=','2')
           // ->where('producto_empresa.id_empresa_negocio',Auth::user()->id_empresa_negocio)
           // ->where('id_almacen',$almacen->id_almacen)
            ->groupby('productos.IdProducto')
            ->orderby('productos.pronom')
            ->orderby('productos.umecod')
            ->get();



     

        $vista = view('empresas.puntosventas.div_catalogo',compact('catalogo'))->render();

             if($request->ajax()){
         return response()->json(['vista'=>$vista]);

        }



    }


}
