<?php

namespace MasterSoft\Http\Controllers;

use Illuminate\Http\Request;
use MasterSoft\tiposgastos;
use MasterSoft\Http\Requests;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Auth;
use DB;

class TiposGastosController extends Controller
{
     
      /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

      public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
         if($request){
            $rucemp = trim(Auth::user()->IdEmpresa);
            $buspro = trim($request->get('buspro'));
            if(empty($rucemp) && empty($buspro)){
                $gastos= DB::tABLE('gastos as g')
                ->where('g.IdEmpresa','=',$rucemp)
                ->paginate(100);
            } else{
                $gastos = DB::tABLE('tipos_gastos as g')
                
                ->where('g.IdEmpresa','=',$rucemp)
                ->where('descgasto','like', '%'.$buspro.'%')
                ->orwhere('g.IdEmpresa','=',$rucemp)
                ->where('codgasto','=',$buspro)
                ->paginate(100);
            }

            return view('empresas.gastos.index',['gastos'=>$gastos,'buspro'=>$buspro]);
         }

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        
        return view('empresas.gastos.create');

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $gastos = new tiposgastos;
        $gastos->IdEmpresa= trim(Auth::user()->IdEmpresa);
        $gastos->codgasto = $request->get('txt_codgasto');
        $gastos->descgasto = $request->get('txt_descgasto');
        //$gastos->esta = "Activo";
        $gastos->save();
        return Redirect::to('/gastos');
    }


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
        $rucemp = trim(Auth::user()->IdEmpresa);
        $gastos = tiposgastoS::findOrFail($id);
       
        return view('empresas.gastos.edit',compact('gastos'));
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
        $gasto= tiposgastos::findOrFail($id);
        $gasto->IdEmpresa= trim(Auth::user()->IdEmpresa);
        $gasto->codgasto = $request->get('txt_codgasto');
        $gasto->descgasto = $request->get('txt_descgasto');
        $gasto->update();
        return Redirect::to('/gastos');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $gasto= tiposgastos::findOrFail($id);
        $gasto->delete();

        return Redirect::to('/gastoS');
    }

   


}
