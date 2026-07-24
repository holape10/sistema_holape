<?php

namespace MasterSoft\Http\Controllers;

use Illuminate\Http\Request;
use MasterSoft\Http\Requests;
use MasterSoft\tipogastos;
use Illuminate\Support\Facades\Auth;
use MasterSoft\Http\Requests\FacturaCreateFormRequest;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Input;
use DB;

class TipoGastosController extends Controller
{
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        
      if($request){
        $buscar = $request->get('buscar');

        if(empty($buscar)){
            $tipogastos = DB::tABLE('tipo_gastos')->paginate(10);
        }else{  
             $tipogastos = DB::tABLE('tipo_gastos')->where('tip_gas_nom',$buscar)->paginate(10);
        }
        return view('empresas.tipogastos.index',compact('tipogastos','buscar'));
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        return view('empresas.tipogastos.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $tipogastos = new tipogastos;
        $tipogastos->tip_gas_cod = $request->get('tip_gas_cod');
        $tipogastos->tip_gas_nom = $request->get('tip_gas_nom');
        $tipogastos->save();

        return Redirect::to('/tipogastos');
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
      $tipogastos= tipogastos::findOrFail($id);
      return view('empresas.tipogastos.edit',compact('tipogastos'));
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
      $tipogastos= tipogastos::findOrFail($id);
      $tipogastos->tip_gas_cod = $request->get('tip_gas_cod');
      $tipogastos->tip_gas_nom = $request->get('tip_gas_nom');
      $tipogastos->update();
      return Redirect::to('/tipogastos');

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
      $tipogastos=  tipogastos::findOrFail($id);
      $tipogastos->delete();

      return Redirect::to('/tipogastos');
    }
}
