<?php

namespace MasterSoft\Http\Controllers;
use Illuminate\Http\Request;
use MasterSoft\Http\Requests;
use MasterSoft\categorias;
use Illuminate\Support\Facades\Auth;
use MasterSoft\Http\Requests\FacturaCreateFormRequest;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Input;
use DB;

class CategoriasController extends Controller
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

            $query = DB::table('categorias')
                ->select('categorias.predeterminado','cat_id','categorias.cat_nom',
                    'impresora1.descripcion AS descripcion1', 
                    'impresora2.descripcion AS descripcion2', 
                    'impresora3.descripcion AS descripcion3',
                    'tipo_producto.tip_pro_nom' // Traemos el nombre del tipo
                )
                ->leftjoin('configuracion_impresoras AS impresora1','impresora1.Id','categorias.impresora')
                ->leftjoin('configuracion_impresoras AS impresora2','impresora2.Id','categorias.impresora2')
                ->leftjoin('configuracion_impresoras AS impresora3','impresora3.Id','categorias.impresora3')
                // NUEVO JOIN: Enlazamos con el tipo de producto
                ->leftjoin('tipo_producto','tipo_producto.tip_pro_id','categorias.tip_pro_id');

            if(empty($buscar)){
                $categorias = $query->paginate(100);
            }else{
                 $categorias = $query->where('categorias.cat_nom', 'LIKE', '%'.$buscar.'%')->paginate(100);
            }
            return view('empresas.categorias.index',compact('categorias','buscar'));
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $impresoras = DB::table('configuracion_impresoras')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->get();
        
        // NUEVO: Consultamos los tipos de producto para enviarlos al <select> de la vista
        $tipos = DB::table('tipo_producto')->get();
        
        return view('empresas.categorias.create',compact('impresoras', 'tipos'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Obtenemos el ID del negocio una sola vez
        $id_empresa_negocio = trim(Auth::user()->id_empresa_negocio);

        $categorias = new categorias;
        $categorias->IdEmpresa = trim(Auth::user()->IdEmpresa);
        $categorias->color = $request->get('color');
        $categorias->visible = $request->get('visible');
        $categorias->cat_acom = $request->get('cat_acom');
        $categorias->id_empresa_negocio = $id_empresa_negocio;
        $categorias->cat_nom = $request->get('txtCatNom');
        $categorias->tipo = $request->get('insumo');
        $categorias->impresora = $request->get('impresoras');
        $categorias->impresora2 = $request->get('impresora2');
        $categorias->impresora3 = $request->get('impresora3');
        $categorias->tip_pro_id = $request->get('tip_pro_id');
        $categorias->save();

        // ✅ LIMPIEZA DE CACHÉ AUTOMÁTICA
        cache()->forget("categorias_empresa_{$id_empresa_negocio}");

        return Redirect::to('/categorias');
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
        $impresoras = DB::table('configuracion_impresoras')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->get();
        $categorias = categorias::findOrFail($id);
        
        // NUEVO: Consultamos los tipos de producto para enviarlos a la vista de edición
        $tipos = DB::table('tipo_producto')->get();
        
        return view('empresas.categorias.edit',compact('categorias','impresoras', 'tipos'));
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
        // Obtenemos el ID del negocio una sola vez
        $id_empresa_negocio = trim(Auth::user()->id_empresa_negocio);

        $categorias = categorias::findOrFail($id);
        $categorias->IdEmpresa = trim(Auth::user()->IdEmpresa);
        $categorias->visible = $request->get('visible');
        $categorias->cat_acom = $request->get('cat_acom');
        $categorias->id_empresa_negocio = $id_empresa_negocio;
        $categorias->color = $request->get('color');
        $categorias->cat_nom = $request->get('txt_catnom');
        $categorias->tipo = $request->get('insumo');
        $categorias->impresora = $request->get('impresoras');
        $categorias->impresora2 = $request->get('impresora2');
        $categorias->impresora3 = $request->get('impresora3');
        
        // NUEVO: Actualizamos el id del tipo de producto (Abuelo)
        $categorias->tip_pro_id = $request->get('tip_pro_id');
        
        $categorias->update();

        // ✅ LIMPIEZA DE CACHÉ AUTOMÁTICA
        cache()->forget("categorias_empresa_{$id_empresa_negocio}");

        return Redirect::to('/categorias');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $categorias = categorias::findOrFail($id);
        
        // Obtenemos el ID del negocio antes de borrar el registro
        $id_empresa_negocio = $categorias->id_empresa_negocio;
        
        $categorias->delete();

        // ✅ LIMPIEZA DE CACHÉ AUTOMÁTICA al eliminar
        cache()->forget("categorias_empresa_{$id_empresa_negocio}");
          
        return Redirect::to('/categorias');
    }

    public function categoria_predeterminada($cat){

        Categorias::where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->update(['predeterminado'=>0]);

        $predeterminada = Categorias::FindOrFail($cat);
        $predeterminada->predeterminado = '1';
        $predeterminada->update();

    }
}
