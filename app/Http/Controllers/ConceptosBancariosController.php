<?php

namespace MasterSoft\Http\Controllers;

use Illuminate\Http\Request;
use MasterSoft\Http\Requests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use MasterSoft\conceptosbancarios;
use DB;

class ConceptosBancariosController extends Controller
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
          $conceptos = DB::tABLE('conceptosbancarios')->where('IdEmpresa',$rucemp)->paginate(10);
      }else{
           $conceptos = DB::tABLE('conceptosbancarios')->where('concepto_nom',$buscar)->where('IdEmpresa',$rucemp)->paginate(10);
      }
      return view('empresas.conceptosbancarios.index',compact('conceptos','buscar'));
      }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('empresas.conceptosbancarios.create');
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
        $concepto=new conceptosbancarios;
        $concepto->concepto_nom = $request->get('concepto_nom') ;
        $concepto->IdEmpresa= $rucemp;
        $concepto->save();
      return Redirect::to('/conceptosbancarios');
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
          $concepto=conceptosbancarios::findOrFail($id);
          return view('empresas.conceptosbancarios.edit',compact('concepto'));
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
          $concepto= conceptosbancarios::findOrFail($id);
          $rucemp = trim(Auth::user()->IdEmpresa);
          $concepto->concepto_nom = $request->get('concepto_nom');
          $concepto->update();
          return Redirect::to('/conceptosbancarios');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
     public function destroy($id)
     {
         $concepto= conceptosbancarios::findOrFail($id);
         $concepto->delete();

         return Redirect::to('/conceptosbancarios');
     }
}
