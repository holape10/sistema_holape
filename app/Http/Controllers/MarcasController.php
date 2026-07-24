<?php

namespace MasterSoft\Http\Controllers;

use Illuminate\Http\Request;
use MasterSoft\Http\Requests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use MasterSoft\marcas;
use DB;

class MarcasController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
      $IdEmpresa = trim(Auth::user()->IdEmpresa);
    if($request){
      $buscar = $request->get('buscar');

      if(empty($buscar)){
          $marcas = DB::tABLE('marcas')
          
          
          ->paginate(10);
      }else{
           $marcas = DB::tABLE('marcas')
           ->where('mar_nom',$buscar)
           
           ->paginate(10);
      }
      return view('empresas.marcas.index',compact('marcas','buscar'));
      }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('empresas.marcas.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $IdEmpresa = trim(Auth::user()->IdEmpresa);
        $marca=new marcas;
        $marca->mar_nom = $request->get('mar_nom') ;
        $marca->id_empresa_negocio = Auth::user()->id_empresa_negocio;
   
        $marca->save();
      return Redirect::to('/marcas');
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
          $marcas=marcas::findOrFail($id);
          return view('empresas.marcas.edit',compact('marcas'));
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
          $marca= marcas::findOrFail($id);
          $IdEmpresa = trim(Auth::user()->IdEmpresa);
          $marca->mar_nom = $request->get('mar_nom');
          $marca->id_empresa_negocio = Auth::user()->id_empresa_negocio;
          $marca->update();
          return Redirect::to('/marcas');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
     public function destroy($id)
     {
         $marca= marcas::findOrFail($id);
         $marca->delete();

         return Redirect::to('/marcas');
     }
}
