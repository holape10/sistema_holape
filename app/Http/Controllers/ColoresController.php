<?php

namespace MasterSoft\Http\Controllers;

use Illuminate\Http\Request;
use MasterSoft\Http\Requests;
use MasterSoft\Colores;
use Illuminate\Support\Facades\Auth;
use MasterSoft\Http\Requests\FacturaCreateFormRequest;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Input;
use DB;

class ColoresController extends Controller
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
            $colores = DB::tABLE('colores')->where('IdEmpresa',$rucemp)->paginate(10);
        }else{
             $colores = DB::tABLE('colores')->where('descripcion',$buscar)->where('IdEmpresa',$rucemp)->paginate(10);
        }
        return view('empresas.colores.index',compact('colores','buscar'));
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        return view('empresas.colores.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $colores = new Colores;
        $colores->IdEmpresa = trim(Auth::user()->IdEmpresa);
        $colores->descripcion = $request->get('txtcolor');
        $colores->save();
        return Redirect::to('/colores');
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
      $colores=Colores::findOrFail($id);
      return view('empresas.colores.edit',compact('colores'));
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
      $colores=Colores::findOrFail($id);
      $colores->IdEmpresa = trim(Auth::user()->IdEmpresa);
      $colores->descripcion = $request->get('txt_color');
      $colores->update();
      return Redirect::to('/colores');

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
      $colores= Colores::findOrFail($id);
      $colores->delete();

      return Redirect::to('/colores');
    }
}
