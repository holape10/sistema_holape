<?php

namespace MasterSoft\Http\Controllers;
use Illuminate\Http\Request;
use MasterSoft\Http\Requests;
use MasterSoft\aplicativos;
use Illuminate\Support\Facades\Auth;
use MasterSoft\Http\Requests\FacturaCreateFormRequest;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Input;
use DB;

class aplicativosController extends Controller
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
            $aplicativos = DB::tABLE('aplicativos')->where('IdEmpresa',$rucemp)->paginate(10);
        }else{
             $aplicativos = DB::tABLE('aplicativos')->where('apli_nom',$buscar)->where('IdEmpresa',$rucemp)->paginate(10);
        }
        return view('empresas.aplicativos.index',compact('aplicativos','buscar'));
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        
        return view('empresas.aplicativos.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $aplicativos = new aplicativos;
        $aplicativos->IdEmpresa = trim(Auth::user()->IdEmpresa);
        $aplicativos->apli_nom = $request->get('txtApliNom');
        $aplicativos->pago = $request->get('pago');
        $aplicativos->id_empresa_negocio = Auth::user()->id_empresa_negocio;
        $aplicativos->save();
        return Redirect::to('/aplicativos');
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
      
      $aplicativos=aplicativos::findOrFail($id);
      return view('empresas.aplicativos.edit',compact('aplicativos'));
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
      $aplicativos=aplicativos::findOrFail($id);
      $aplicativos->IdEmpresa = trim(Auth::user()->IdEmpresa);
      $aplicativos->apli_nom = $request->get('txt_aplinom');
       $aplicativos->pago = $request->get('pago');
      $aplicativos->update();
      return Redirect::to('/aplicativos');

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
      $aplicativos= aplicativos::findOrFail($id);
      $aplicativos->delete();

      return Redirect::to('/aplicativos');
    }
}
