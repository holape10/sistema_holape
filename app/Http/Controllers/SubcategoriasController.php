<?php

namespace MasterSoft\Http\Controllers;
use Illuminate\Http\Request;
use MasterSoft\Http\Requests;
use MasterSoft\subcategorias;
use Illuminate\Support\Facades\Auth;
use MasterSoft\Http\Requests\FacturaCreateFormRequest;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Input;
use DB;

class SubcategoriasController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
{
    $rucemp = trim(Auth::user()->IdEmpresa);
    if($request){
        $buscar = $request->get('buscar');

        // Creamos la consulta base con los dos joins
        $query = DB::table('subcategorias')
            ->join('categorias','categorias.cat_id','subcategorias.cat_id')
            ->leftjoin('tipo_producto','tipo_producto.tip_pro_id','categorias.tip_pro_id')
            ->select(
                'subcategorias.*', 
                'categorias.cat_nom', 
                'tipo_producto.tip_pro_nom' // Traemos el nombre del Abuelo
            );

        if(empty($buscar)){
            $subcategorias = $query->paginate(100);
        }else{
            $subcategorias = $query->where('subcat_nom', 'LIKE', '%'.$buscar.'%')
                                   ->paginate(100);
        }

        return view('empresas.subcategorias.index', compact('subcategorias', 'buscar'));
    }
}

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        $categorias = DB::tABLE('categorias')->get();
        $impresoras = DB::tABLE('configuracion_impresoras')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->get();
        return view('empresas.subcategorias.create',compact('impresoras','categorias'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $subcategorias = new subcategorias;
        $subcategorias->color = $request->get('color');
        $subcategorias->id_empresa_negocio = trim(Auth::user()->id_empresa_negocio);
        $subcategorias->subcat_nom = $request->get('subcat_nom');
        $subcategorias->cat_id = $request->get('cat_id');
        //$subcategorias->impresora = $request->get('impresoras');
        $subcategorias->save();

        return Redirect::to('/subcategorias');
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

      $categorias = DB::tABLE('categorias')->get();
      $impresoras = DB::tABLE('configuracion_impresoras')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->get();
      $subcategorias= subcategorias::findOrFail($id);
      return view('empresas.subcategorias.edit',compact('subcategorias','impresoras','categorias'));
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
      $subcategorias= subcategorias::findOrFail($id);
      $subcategorias->id_empresa_negocio = trim(Auth::user()->id_empresa_negocio);
      $subcategorias->color = $request->get('color');
      $subcategorias->subcat_nom = $request->get('subcat_nom');
      $subcategorias->cat_id = $request->get('cat_id');
      //$subcategorias->impresora = $request->get('impresoras');
      $subcategorias->update();

      return Redirect::to('/subcategorias');

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
      $subcategorias= subcategorias::findOrFail($id);
      $subcategorias->delete();

      return Redirect::to('/subcategorias');
    }
}
