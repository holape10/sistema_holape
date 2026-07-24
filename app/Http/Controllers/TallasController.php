<?php

namespace MasterSoft\Http\Controllers;

use Illuminate\Http\Request;
use MasterSoft\Http\Requests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use MasterSoft\tallas;
use DB;

class TallasController extends Controller
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
          $tallas = DB::tABLE('tallas')
          ->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
          ->where('IdEmpresa',$IdEmpresa)
          ->paginate(10);
      }else{
           $tallas = DB::tABLE('tallas')
           ->where('tal_nom',$buscar)
           ->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
           ->where('IdEmpresa',$IdEmpresa)->paginate(10);
      }
      return view('empresas.tallas.index',compact('tallas','buscar'));
      }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('empresas.tallas.create');
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
        $talla=new tallas;
        $talla->tal_nom = $request->get('tal_nom') ;
        $talla->id_empresa_negocio = Auth::user()->id_empresa_negocio;
        $talla->IdEmpresa= $IdEmpresa;
        $talla->save();
      return Redirect::to('/tallas');
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
          $talla=tallas::findOrFail($id);
          return view('empresas.tallas.edit',compact('talla'));
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
          $talla= tallas::findOrFail($id);
          $IdEmpresa = trim(Auth::user()->IdEmpresa);
          $talla->tal_nom = $request->get('tal_nom');
          $talla->id_empresa_negocio = Auth::user()->id_empresa_negocio;
          $talla->update();
          return Redirect::to('/tallas');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
     public function destroy($id)
     {
         $talla= tallas::findOrFail($id);
         $talla->delete();

         return Redirect::to('/tallas');
     }
}
