<?php

namespace MasterSoft\Http\Controllers;

use Illuminate\Http\Request;
use MasterSoft\productos;
use MasterSoft\Http\Requests\ProductoCreateFormRequest;
use MasterSoft\Http\Requests\ProductoUpdateFormRequest;
use MasterSoft\Http\Requests;
use MasterSoft\movimientos;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Auth;
use DB;
class PromocionesController extends Controller
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

    public function index(Request $request)
    {
         if($request){
            $rucemp = trim(Auth::user()->IdEmpresa);
            $buspro = trim($request->get('buspro'));
            if(empty($rucemp) && empty($buspro)){
                $productos= DB::tABLE('productos as p')
                ->join('moneda as m','p.moncod','=','m.moncod')
                ->join('unidad_medida as u','p.umecod','=','u.umecod')
                ->select('p.procod','p.pronom','m.monnom','u.umenom','p.provun','p.propun','p.propuncom','p.proest','IdProducto','stock')
                ->where('IdEmpresa','=',$rucemp)
                ->where('promocion','1')
                ->orderby('pronom','asc')
                ->paginate(7);
            } else{
                $productos = DB::tABLE('productos as p')
                ->join('moneda as m','p.moncod','=','m.moncod')
                ->join('unidad_medida as u','p.umecod','=','u.umecod')
                ->select('p.procod','p.pronom','m.monnom','u.umenom','p.provun','p.propun','p.propuncom','p.proest','IdProducto','stock')
                ->where('IdEmpresa','=',$rucemp)
                ->where('pronom','like', '%'.$buspro.'%')
                ->where('promocion','1')
                ->orwhere('IdEmpresa','=',$rucemp)
                ->where('procod','like','%'.$buspro.'%')
                ->where('promocion','1')
                ->orderby('pronom','asc')
                ->paginate(7);
            }

            return view('empresas.promociones.index',['productos'=>$productos,'buspro'=>$buspro]);
         }

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $rucemp = trim(Auth::user()->IdEmpresa);
        $categorias = DB::tABLE('categorias')->WHERE('IdEmpresa',$rucemp)->get();
        $unidades = DB::tABLE('unidad_medida')->where('umeest','=','Activo')
        ->orderBy('umecod','asc')->get();

        $monedas = DB::tABLE('moneda')->where('monest','=','Activo')->orderby('moncod','asc')->get();

        $productos = DB::tABLE('productos')->where('promocion','0')->WHERE('IdEmpresa',$rucemp)->get();
         return view('empresas.promociones.create',compact('unidades','categorias','monedas','productos'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ProductoCreateFormRequest $request)
    {

        $fecha = now()->format('Y-m-d');
        $productos = new productos;
        $productos->IdEmpresa= trim(Auth::user()->IdEmpresa);
        $productos->procod = $request->get('txt_procod');
        $productos->pronom = $request->get('txt_pronom');
        $productos->umecod = $request->get('txt_umecod');
        $productos->moncod = $request->get('txt_moncod');
        $productos->provun = $request->get('txt_provun');
        $productos->promocion = '1';

        if(!empty($request->get('cmbProd1')) && !empty($request->get('txt_cant_prod1'))){
            $productos->producto1 = $request->get('cmbProd1');
            $productos->cantidad1 = $request->get('txt_cant_prod1');
        }
      
        if(!empty($request->get('cmbProd2')) && !empty($request->get('txt_cant_prod2'))){
            $productos->producto2 = $request->get('cmbProd2');
            $productos->cantidad2 = $request->get('txt_cant_prod2');
        }
      
        if(!empty($request->get('cmbProd3')) && !empty($request->get('txt_cant_prod3'))){
            $productos->producto3 = $request->get('cmbProd3');
            $productos->cantidad3 = $request->get('txt_cant_prod3');
        }
      
        if(!empty($request->get('cmbProd4')) && !empty($request->get('txt_cant_prod4'))){
            $productos->producto4 = $request->get('cmbProd4');
            $productos->cantidad4 = $request->get('txt_cant_prod4');
        }
        
        $productos->propun = $request->get('txt_propun');
        $productos->cat_id = $request->get('cmbCatId');
        $productos->stock = '0';
        $productos->proest = "Activo";
        $productos->save();
       


         return Redirect::to('/productos');


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
    public function edit($id)
    {
        $rucemp = trim(Auth::user()->IdEmpresa);
        $productos = productos::findOrFail($id);
        $unidades = DB::tABLE('unidad_medida')->where('umeest','=','Activo')
        ->orderBy('umecod','asc')->get();
        $categorias = DB::tABLE('categorias')->WHERE('IdEmpresa',$rucemp)->get();
         $producto = DB::tABLE('productos')->WHERE('IdEmpresa',$rucemp)->get();
        $monedas = DB::tABLE('moneda')->where('monest','=','Activo')->orderby('moncod','asc')->get();
        return view('empresas.promociones.edit',compact('producto','productos','unidades','categorias','monedas'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $productos= productos::findOrFail($id);
        $productos->procod = $request->get('txt_procod');
        $productos->pronom = $request->get('txt_pronom');
        $productos->umecod = $request->get('txt_umecod');
        $productos->moncod = $request->get('txt_moncod');
        $productos->provun = $request->get('txt_provun');
        $productos->promocion = $request->get('txt_promocion');

        $productos->producto1 = $request->get('cmbProd1');
        $productos->cantidad1 = $request->get('txt_cant_prod1');

        $productos->producto2 = $request->get('cmbProd2');
        $productos->cantidad2 = $request->get('txt_cant_prod2');

        $productos->producto3 = $request->get('cmbProd3');
        $productos->cantidad3 = $request->get('txt_cant_prod3');

        $productos->producto4 = $request->get('cmbProd4');
        $productos->cantidad4 = $request->get('txt_cant_prod4');

        $productos->propun = $request->get('txt_propun');
        $productos->cat_id = $request->get('cmbCatId');
        $productos->stock = '0';
        $productos->proest = $request->get('txt_proest');

        $productos->update();
        return Redirect::to('/productos');
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

        return Redirect::to('/productos');
    }

    public function consultarmenu(Request $request, $cat_id){
        $rucemp = trim(Auth::user()->IdEmpresa);
        $productos = DB::tABLE('productos')->select('procod','pronom','propun','provun','IdProducto','umecod')->where('cat_id',$cat_id)->where('IdEmpresa',$rucemp)->get();
        $unidades = DB::tABLE('unidad_medida')->where('umeest','=','Activo')
        ->orderBy('umecod','asc')->get();

        $vista = view('empresas.promociones.menu',compact('productos','unidades'))->render();

        if($request->ajax()){
         return response()->json(['vista'=>$vista]);

        }
    }

    public function consultarcategorias(Request $request){
        $rucemp = trim(Auth::user()->IdEmpresa);
        $categorias = DB::tABLE('categorias')->select('cat_id','cat_nom')->where('IdEmpresa',$rucemp)->orderby('cat_nom','asc')->get();

        $vista = view('empresas.promociones.listacategorias',compact('categorias'))->render();

        if($request->ajax()){
         return response()->json(['vista'=>$vista]);

        }
    }
}
