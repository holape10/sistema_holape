<?php

namespace MasterSoft\Http\Controllers;
use Illuminate\Http\Request;
use MasterSoft\Http\Requests;
use MasterSoft\tiposcaja;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Input;
use DB;

class TiposCajaController extends Controller
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
            $cajas = DB::tABLE('tiposcaja')->paginate(10);
        }else{
             $cajas = DB::tABLE('tiposcaja')->where('tip_caj_nom',$buscar)->paginate(10);
        }
        return view('empresas.tiposcaja.index',compact('cajas','buscar'));
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

      
        return view('empresas.tiposcaja.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $cajas = new tiposcaja;
        $cajas->IdEmpresa = trim(Auth::user()->IdEmpresa);
        $cajas->tip_caj_id = $request->get('tip_caj_id');
        $cajas->tip_caj_nom = $request->get('tip_caj_nom');
        $cajas->tipo = $request->get('movimiento');
        $cajas->id_empresa_negocio = Auth::user()->id_empresa_negocio;
        $cajas->save();
        return Redirect::to('/tiposcaja');
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
  
      $cajas=tiposcaja::findOrFail($id);
      return view('empresas.tiposcaja.edit',compact('cajas'));
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
      $cajas=tiposcaja::findOrFail($id);
      $cajas->IdEmpresa = trim(Auth::user()->IdEmpresa);
      $cajas->tip_caj_id = $request->get('tip_caj_id');
      $cajas->tip_caj_nom = $request->get('tip_caj_nom');
      $cajas->tipo = $request->get('movimiento');
      $cajas->update();
      return Redirect::to('/tiposcaja');

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
      $cajas= tiposcaja::findOrFail($id);
      $cajas->delete();

      return Redirect::to('/tiposcaja');
    }
}
