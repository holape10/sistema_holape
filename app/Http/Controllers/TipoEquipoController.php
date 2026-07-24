<?php

namespace MasterSoft\Http\Controllers;

use Illuminate\Http\Request;
use MasterSoft\Http\Requests;
use MasterSoft\tipo_equipo;
use Illuminate\Support\Facades\Auth;
use MasterSoft\Http\Requests\FacturaCreateFormRequest;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Input;
use DB;

class TipoEquipoController extends Controller
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
            $tipo_equipo = DB::tABLE('tipo_equipo')->paginate(10);
        }else{
             $tipo_equipo = DB::tABLE('tipo_equipo')->where('nom_tip_equi',$buscar)->paginate(10);
        }
        return view('empresas.tipo_equipo.index',compact('tipo_equipo','buscar'));
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        return view('empresas.tipo_equipo.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $tipo_equipo = new tipo_equipo;
        
        $tipo_equipo->nom_tip_equi = $request->get('nom_tip_equi');
        $tipo_equipo->save();
        return Redirect::to('/tipoequipo');
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
      $tipo_equipo = tipo_equipo::findOrFail($id);
      return view('empresas.tipo_equipo.edit',compact('tipo_equipo'));
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
      $tipo_equipo=tipo_equipo::findOrFail($id);
      
      $tipo_equipo->nom_tip_equi = $request->get('nom_tip_equi');
      $tipo_equipo->update();

      return Redirect::to('/tipoequipo');

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
      $tipo_equipo= tipo_equipo::findOrFail($id);
      $tipo_equipo->delete();

      return Redirect::to('/tipoequipo');
    }
}
