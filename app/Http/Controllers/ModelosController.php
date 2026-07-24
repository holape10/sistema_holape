<?php

namespace MasterSoft\Http\Controllers;

use Illuminate\Http\Request;
use MasterSoft\Http\Requests;
use MasterSoft\modelos;
use Illuminate\Support\Facades\Auth;
use MasterSoft\Http\Requests\FacturaCreateFormRequest;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Input;
use DB;

class ModelosController extends Controller
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
            $modelos = DB::tABLE('modelos')->paginate(10);
        }else{
             $modelos = DB::tABLE('modelos')->where('mod_nom',$buscar)->paginate(10);
        }
        return view('empresas.modelos.index',compact('modelos','buscar'));
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        return view('empresas.modelos.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $modelos = new modelos;
        
        $modelos->mod_nom = $request->get('mod_nom');
        $modelos->save();
        return Redirect::to('/modelos');
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
      $modelo=modelos::findOrFail($id);
      return view('empresas.modelos.edit',compact('modelo'));
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
      $modelos=modelos::findOrFail($id);
      
      $modelos->mod_nom = $request->get('mod_nom');
      $modelos->update();
      return Redirect::to('/modelos');

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
      $modelos= modelos::findOrFail($id);
      $modelos->delete();

      return Redirect::to('/modelos');
    }
}
