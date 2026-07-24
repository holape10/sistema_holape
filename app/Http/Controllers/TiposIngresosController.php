<?php

namespace MasterSoft\Http\Controllers;

use Illuminate\Http\Request;
use MasterSoft\tiposingresos;
use MasterSoft\Http\Requests;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Auth;
use DB;

class TiposIngresosController extends Controller
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
                $ingresos= DB::tABLE('ingresos as g')
                ->where('g.IdEmpresa','=',$rucemp)
                ->paginate(100);
            } else{
                $ingresos = DB::tABLE('tipos_ingresos as g')
                
                ->where('g.IdEmpresa','=',$rucemp)
                ->where('descingreso','like', '%'.$buspro.'%')
                ->orwhere('g.IdEmpresa','=',$rucemp)
                ->where('codingreso','=',$buspro)
                ->paginate(100);
            }

            return view('empresas.ingresos.index',['ingresos'=>$ingresos,'buspro'=>$buspro]);
         }

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        
        return view('empresas.ingresos.create');

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $ingresos = new tiposingresos;
        $ingresos->IdEmpresa= trim(Auth::user()->IdEmpresa);
        $ingresos->codingreso = $request->get('txt_codingreso');
        $ingresos->descingreso = $request->get('txt_descingreso');
        //$ingresos->esta = "Activo";
        $ingresos->save();
        return Redirect::to('/ingresos');
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
        $rucemp = trim(Auth::user()->IdEmpresa);
        $ingresos = tiposingresos::findOrFail($id);
       
        return view('empresas.ingresos.edit',compact('ingresos'));
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
        $ingresos= tiposingresos::findOrFail($id);
        $ingresos->IdEmpresa= trim(Auth::user()->IdEmpresa);
        $ingresos->codingreso = $request->get('txt_codingreso');
        $ingresos->descingreso = $request->get('txt_descingreso');
        $ingresos->update();
        return Redirect::to('/ingresos');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $ingresos= tiposingresos::findOrFail($id);
        $ingresos->delete();

        return Redirect::to('/ingresos');
    }

   


}
