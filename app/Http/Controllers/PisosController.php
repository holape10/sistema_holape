<?php

namespace MasterSoft\Http\Controllers;

use Illuminate\Http\Request;
use MasterSoft\Http\Requests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use MasterSoft\pisos;
use DB;

class PisosController extends Controller
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
          $pisos = DB::tABLE('pisos')
          ->where('suc_id',Auth::user()->id_empresa_negocio)
          ->where('emp_id',$rucemp)
          ->paginate(10);
      }else{
           $pisos = DB::tABLE('pisos')
           ->where('pis_nom',$buscar)
           ->where('suc_id',Auth::user()->id_empresa_negocio)
           ->where('emp_id',$rucemp)->paginate(10);
      }
      return view('empresas.pisos.index',compact('pisos','buscar'));
      }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('empresas.pisos.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $rucemp = trim(Auth::user()->IdEmpresa);
        $piso=new pisos;
        $piso->pis_nom = $request->get('pis_nom') ;
        $piso->suc_id = Auth::user()->id_empresa_negocio;
        $piso->emp_id= $rucemp;
        $piso->save();
      return Redirect::to('/pisos');
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
          $piso=pisos::findOrFail($id);
          return view('empresas.pisos.edit',compact('piso'));
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
          $piso= pisos::findOrFail($id);
          $rucemp = trim(Auth::user()->IdEmpresa);
          $piso->pis_nom = $request->get('pis_nom');
          $piso->suc_id = Auth::user()->id_empresa_negocio;
          $piso->update();
          return Redirect::to('/pisos');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
     public function destroy($id)
     {
         $piso= pisos::findOrFail($id);
         $piso->delete();

         return Redirect::to('/pisos');
     }
}
