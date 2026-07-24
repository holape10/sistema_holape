<?php

namespace MasterSoft\Http\Controllers;
use Illuminate\Http\Request;
use MasterSoft\Http\Requests;
use MasterSoft\principioactivo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Input;
use DB;

class PrincipioActivoController extends Controller
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
            $principioactivo = DB::tABLE('principioactivo')->where('IdEmpresa',$rucemp)->paginate(10);
        }else{
             $principioactivo = DB::tABLE('principioactivo')->where('pri_act_nom',$buscar)->where('IdEmpresa',$rucemp)->paginate(10);
        }
        return view('empresas.principioactivo.index',compact('principioactivo','buscar'));
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        return view('empresas.principioactivo.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $principioactivo = new principioactivo;
        $principioactivo->IdEmpresa = trim(Auth::user()->IdEmpresa);
        $principioactivo->pri_act_nom = $request->get('pri_act_nom');
        $principioactivo->save();
        return Redirect::to('/principioactivo');
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
      $principioactivo=principioactivo::findOrFail($id);
      return view('empresas.principioactivo.edit',compact('principioactivo'));
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
      $principioactivo=principioactivo::findOrFail($id);
      $principioactivo->IdEmpresa = trim(Auth::user()->IdEmpresa);
      $principioactivo->pri_act_nom = $request->get('pri_act_nom');
      $principioactivo->update();
      return Redirect::to('/principioactivo');

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
      $principioactivo= principioactivo::findOrFail($id);
      $principioactivo->delete();

      return Redirect::to('/principioactivo');
    }
}
