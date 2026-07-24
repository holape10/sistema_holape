<?php

namespace MasterSoft\Http\Controllers;

use Illuminate\Http\Request;
use MasterSoft\Http\Requests;
use MasterSoft\programas;
use Illuminate\Support\Facades\Auth;
use MasterSoft\Http\Requests\FacturaCreateFormRequest;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Input;
use DB;

class programasController extends Controller
{
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function asignarplatos($id){

        $platos = DB::tABLE('productos')
          ->where('tipo','1')
        ->where('promocion','2')
        ->get();

        $progplat = DB::tABLE('programas_preparados')
        ->join('productos','productos.IdProducto','programas_preparados.IdProducto')
        ->where('programas_preparados.prog_id',$id)

        ->get();

        return view('empresas.programas.asignarplato',compact('platos','progplat','id'));
    }

    public function registrarplato(Request $request){

        $platos = $request->get('plat_id');
        $id = $request->get('prog_id');

          DB::tABLE('programas_preparados')->where('prog_id',$id)->delete();

        foreach ($platos as $i => $pl) {
            
            DB::tABLE('programas_preparados')->insert(['prog_id'=>$id,'IdProducto'=>$pl]);

        }

        return Redirect::to('/programas');
    }

    public function index(Request $request)
    {
        
      if($request){
        $buscar = $request->get('buscar');

        if(empty($buscar)){
            $programas = DB::tABLE('programas')->paginate(100);
        }else{  
             $programas = DB::tABLE('programas')->where('prog_nom',$buscar)->paginate(100);
        }
        return view('empresas.programas.index',compact('programas','buscar'));
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        return view('empresas.programas.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $programas = new programas;
        $programas->prog_cod = $request->get('prog_cod');
        $programas->prog_nom = $request->get('prog_nom');
        $programas->save();

        return Redirect::to('/programas');
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
      $programas=programas::findOrFail($id);
      return view('empresas.programas.edit',compact('programas'));
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
      $programas=programas::findOrFail($id);
      $programas->prog_cod = $request->get('prog_cod');
      $programas->prog_nom = $request->get('prog_nom');
      $programas->update();
      return Redirect::to('/programas');

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
      $programas= programas::findOrFail($id);
      $programas->delete();

      return Redirect::to('/programas');
    }
}
