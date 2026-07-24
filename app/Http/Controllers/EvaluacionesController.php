<?php

namespace MasterSoft\Http\Controllers;
use Illuminate\Http\Request;
use MasterSoft\Http\Requests;
use MasterSoft\evaluaciones;
use Illuminate\Support\Facades\Auth;
use MasterSoft\Http\Requests\FacturaCreateFormRequest;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Input;
use DB;

class EvaluacionesController extends Controller
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
            $evaluaciones = DB::tABLE('evaluaciones')->paginate(10);
        }else{
             $evaluaciones = DB::tABLE('evaluaciones')->where('eval_nom',$buscar)->paginate(10);
        }

        
        return view('empresas.evaluaciones.index',compact('evaluaciones','buscar'));
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        
        return view('empresas.evaluaciones.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $evaluaciones = new evaluaciones;
        $evaluaciones->IdEmpresa = trim(Auth::user()->IdEmpresa);
        $evaluaciones->eval_cod = $request->get('eval_cod');
        $evaluaciones->id_empresa_negocio = trim(Auth::user()->id_empresa_negocio);
        $evaluaciones->eval_nom = $request->get('eval_nom');
        $evaluaciones->save();
        return Redirect::to('/evaluaciones');
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
    
      $evaluaciones=evaluaciones::findOrFail($id);
      return view('empresas.evaluaciones.edit',compact('evaluaciones'));
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
      $evaluaciones=evaluaciones::findOrFail($id);
      $evaluaciones->IdEmpresa = trim(Auth::user()->IdEmpresa);
      $evaluaciones->eval_cod = $request->get('eval_cod');
      $evaluaciones->id_empresa_negocio = trim(Auth::user()->id_empresa_negocio);
      $evaluaciones->eval_nom = $request->get('eval_nom');

      $evaluaciones->update();
      return Redirect::to('/evaluaciones');

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
      $evaluaciones= evaluaciones::findOrFail($id);
      $evaluaciones->delete();

      return Redirect::to('/evaluaciones');
    }
}
