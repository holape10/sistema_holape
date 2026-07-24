<?php

namespace MasterSoft\Http\Controllers\ValetParking;

use MasterSoft\Http\Controllers\Controller;
use Illuminate\Http\Request;
use MasterSoft\Http\Requests;
use MasterSoft\tarifas;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Input;
use DB;

class TarifasController extends Controller
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
            $tarifas = DB::tABLE('tarifas')
            ->select('id_tarifa','nom_uni_tie','tarifas.descripcion as descripcion','tv.descripcion as nomvehiculo','precio','preciosinigv','tolerancia')
            ->leftjoin('unidad_tiempo as ut','ut.id_uni_tie','tarifas.id_uni_tie')
            ->leftjoin('tipos_vehiculos as tv','tv.id_tipo_vehiculo','tarifas.id_tipo_vehiculo')
            ->where('tarifas.id_empresa_negocio',Auth::user()->id_empresa_negocio)
            ->paginate(10);

        }else{

             $tarifas = DB::tABLE('tarifas')->select('id_tarifa','nom_uni_tie','tarifas.descripcion as descripcion','tv.descripcion.descripcion as nomvehiculo','precio','preciosinigv','tolerancia') 
             ->leftjoin('unidad_tiempo as ut','ut.id_uni_tie','tarifas.id_uni_tie')
             ->leftjoin('tipos_vehiculos as tv','tv.id_tipo_vehiculo','tarifas.id_tipo_vehiculo')
             ->where('descripcion',$buscar)
             ->where('tarifas.id_empresa_negocio',Auth::user()->id_empresa_negocio)
             ->paginate(10);
        }

        return view('valetparking.tarifas.index',compact('tarifas','buscar'));
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        $vehiculos = DB::tABLE('tipos_vehiculos')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->get();
        $unitiempo = DB::tABLE('unidad_tiempo')->get();

        return view('valetparking.tarifas.create',compact('vehiculos','unitiempo'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $tarifas = new tarifas;
        $tarifas->IdEmpresa = trim(Auth::user()->IdEmpresa);
        $tarifas->descripcion = $request->get('txtdescripcion');

        if($request->get('chkIgv') ==true){

            $tarifas->precio = $request->get('txtprecio');
            $tarifas->preciosinigv = $request->get('txtprecio')/1.1055;
            $tarifas->incluyeigv ='1';
        }else{

            $tarifas->precio = $request->get('txtprecio')*1.18;
            $tarifas->preciosinigv = $request->get('txtprecio'); 
            $tarifas->incluyeigv ='0'; 
        }

       
        $tarifas->tolerancia = $request->get('txttolerancia');
        $tarifas->id_uni_tie = $request->get('cmbUniTie');
        $tarifas->id_tipo_vehiculo = $request->get('cmbVehiculos');
        $tarifas->id_empresa_negocio = Auth::user()->id_empresa_negocio;
        $tarifas->save();
        return Redirect::to('/tarifas');
    }

    public function BuscarTarifas(Request $request, $tipo){
        $tarifas = DB::tABLE('tarifas')
        ->where('id_tipo_vehiculo',$tipo)
        ->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
        ->get();
        
        $vista = view('valetparking.tarifas.listartarifas',compact('tarifas'))->render();

        if($request->ajax()){
         return response()->json(['vista'=>$vista]);
        }
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
      
      $tarifas=tarifas::findOrFail($id);
       $unitiempo = DB::tABLE('unidad_tiempo')->get();
      $vehiculos = DB::tABLE('tipos_vehiculos')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->get();
        

      return view('valetparking.tarifas.edit',compact('tarifas','vehiculos','unitiempo'));
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
        
        $tarifas=tarifas::findOrFail($id);
        $tarifas->IdEmpresa = trim(Auth::user()->IdEmpresa);
        $tarifas->descripcion = $request->get('txtdescripcion');
        $tarifas->id_uni_tie = $request->get('cmbUniTie');
        $tarifas->tolerancia = $request->get('txttolerancia');
        $tarifas->id_tipo_vehiculo = $request->get('cmbVehiculos');
        $tarifas->id_empresa_negocio = Auth::user()->id_empresa_negocio;

          if(!is_null($request->get('chkIgv'))){
            $tarifas->precio = $request->get('txtprecio');
            $tarifas->preciosinigv = $request->get('txtprecio')/1.1055;
            $tarifas->incluyeigv ='1';
        }else{
            $tarifas->precio = $request->get('txtprecio')*1.18;
            $tarifas->preciosinigv = $request->get('txtprecio'); 
            $tarifas->incluyeigv ='0'; 
        }

        $tarifas->update();

      return Redirect::to('/tarifas');

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function eliminartarifa(Request $request)
    {
      $tarifas= tarifas::findOrFail($request->get('id'));
      $tarifas->delete();

      return Redirect::to('/tarifas');
    }
}
