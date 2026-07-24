<?php

namespace MasterSoft\Http\Controllers;

use Illuminate\Http\Request;
use MasterSoft\Http\Requests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use MasterSoft\bancos;
use DB;

class BancosController extends Controller
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
          $bancos = DB::tABLE('bancos')->where('IdEmpresa',$rucemp)->paginate(10);
      }else{
           $bancos = DB::tABLE('bancos')->where('ban_nom',$buscar)->where('IdEmpresa',$rucemp)->paginate(10);
      }
      return view('empresas.bancos.index',compact('bancos','buscar'));
      }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('empresas.bancos.create');
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
        $banco=new bancos;
        $banco->ban_nom = $request->get('ban_nom') ;
        $banco->IdEmpresa= $rucemp;
        $banco->save();
      return Redirect::to('/bancos');
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
          $banco=bancos::findOrFail($id);
          return view('empresas.bancos.edit',compact('banco'));
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
          $banco= bancos::findOrFail($id);
          $rucemp = trim(Auth::user()->IdEmpresa);
          $banco->ban_nom = $request->get('ban_nom');
          $banco->update();
          return Redirect::to('/bancos');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
     public function destroy($id)
     {
         $banco= bancos::findOrFail($id);
         $banco->delete();

         return Redirect::to('/bancos');
     }
}
