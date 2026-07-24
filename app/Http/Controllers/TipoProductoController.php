<?php

namespace MasterSoft\Http\Controllers;
use Illuminate\Http\Request;
use MasterSoft\Http\Requests;
use MasterSoft\tipoproducto;
use Illuminate\Support\Facades\Auth;
use MasterSoft\Http\Requests\FacturaCreateFormRequest;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Input;
use DB;

class TipoProductoController extends Controller
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

            if(empty($buscar)){
                // Quitamos los leftjoin de categorias y subcategorias
                $tipos = DB::tABLE('tipo_producto')
                ->paginate(100);
            }else{
                 $tipos = DB::tABLE('tipo_producto')
                 ->where('tip_pro_nom', 'LIKE', '%'.$buscar.'%')
                 ->paginate(100);
            }
            return view('empresas.tipoproducto.index',compact('tipos','buscar'));
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $impresoras = DB::tABLE('configuracion_impresoras')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->get();
        // Ya no mandamos subcategorias a la vista
        return view('empresas.tipoproducto.create',compact('impresoras'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $tipos = new tipoproducto;
        $tipos->IdEmpresa = trim(Auth::user()->IdEmpresa);
        //$tipos->color = $request->get('color');
        $tipos->id_empresa_negocio = trim(Auth::user()->id_empresa_negocio);
        $tipos->tip_pro_nom = $request->get('tip_pro_nom');
        $tipos->cta_contable_70 = $request->get('cta_contable_70');
        $tipos->cta_contable_12 = $request->get('cta_contable_12');
        //$tipos->subcat_id = $request->get('subcat_id');
 
        $tipos->save();

        return Redirect::to('/tipoproducto');
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
        $impresoras = DB::tABLE('configuracion_impresoras')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->get();
        $tipos = tipoproducto::findOrFail($id);
        // Quitamos la consulta de subcategorias
        return view('empresas.tipoproducto.edit',compact('tipos','impresoras'));
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
        $tipos = tipoproducto::findOrFail($id);
        // Descomenta las lineas de empresa si las necesitas
        $tipos->tip_pro_nom = $request->get('tip_pro_nom');
        $tipos->cta_contable_70 = $request->get('cta_contable_70');
        $tipos->cta_contable_12 = $request->get('cta_contable_12');
        
        // ¡ESTA LÍNEA SE BORRA O SE COMENTA! Ya no depende de subcat_id
        // $tipos->subcat_id = $request->get('subcat_id'); 
        
        $tipos->update();
        return Redirect::to('/tipoproducto');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
      $tipos= tipoproducto::findOrFail($id);
      $tipos->delete();

      return Redirect::to('/tipoproducto');
    }
}
