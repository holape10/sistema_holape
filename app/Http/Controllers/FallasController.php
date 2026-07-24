<?php

namespace MasterSoft\Http\Controllers;
use Illuminate\Http\Request;
use MasterSoft\Http\Requests;
use MasterSoft\fallas;
use Illuminate\Support\Facades\Auth;
use MasterSoft\Http\Requests\FacturaCreateFormRequest;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Input;
use DB;

class FallasController extends Controller
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
            $fallas = DB::tABLE('fallas')->paginate(10);
        }else{
             $fallas = DB::tABLE('fallas')->where('fall_nom',$buscar)->paginate(10);
        }

        
        return view('empresas.fallas.index',compact('fallas','buscar'));
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
        return view('empresas.fallas.create',compact('impresoras'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $fallas = new fallas;
        $fallas->IdEmpresa = trim(Auth::user()->IdEmpresa);
        $fallas->fall_cod = $request->get('fall_cod');
         $fallas->id_empresa_negocio = trim(Auth::user()->id_empresa_negocio);
        $fallas->fall_nom = $request->get('fall_nom');
      
        $fallas->save();
        return Redirect::to('/fallas');
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
      
      $fallas=fallas::findOrFail($id);
      return view('empresas.fallas.edit',compact('fallas'));
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
      $fallas=fallas::findOrFail($id);
      $fallas->IdEmpresa = trim(Auth::user()->IdEmpresa);
      $fallas->fall_cod = $request->get('fall_cod');
      $fallas->id_empresa_negocio = trim(Auth::user()->id_empresa_negocio);
      $fallas->fall_nom = $request->get('fall_nom');
      $fallas->update();
      return Redirect::to('/fallas');

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
      $fallas= fallas::findOrFail($id);
      $fallas->delete();

      return Redirect::to('/fallas');
    }
}
