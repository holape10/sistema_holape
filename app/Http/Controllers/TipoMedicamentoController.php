<?php

namespace MasterSoft\Http\Controllers;
use Illuminate\Http\Request;
use MasterSoft\Http\Requests;
use MasterSoft\tipo_medicamento;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Input;
use DB;

class TipoMedicamentoController extends Controller
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
            $tipo_medicamento = DB::tABLE('tipo_medicamento')->where('IdEmpresa',$rucemp)->paginate(10);
        }else{
             $tipo_medicamento = DB::tABLE('tipo_medicamento')->where('descripcion',$buscar)->where('IdEmpresa',$rucemp)->paginate(10);
        }
        return view('empresas.tiposmedicamentos.index',compact('tipo_medicamento','buscar'));
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        return view('empresas.tiposmedicamentos.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $tipo_medicamento = new tipo_medicamento;
        $tipo_medicamento->IdEmpresa = trim(Auth::user()->IdEmpresa);
        $tipo_medicamento->descripcion = $request->get('txtTipMed');
        $tipo_medicamento->save();
        
        return Redirect::to('/tiposmedicamentos');
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
      $tipo_medicamento=tipo_medicamento::findOrFail($id);
      return view('empresas.tiposmedicamentos.edit',compact('tipo_medicamento'));
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
      $tipo_medicamento=tipo_medicamento::findOrFail($id);
      $tipo_medicamento->IdEmpresa = trim(Auth::user()->IdEmpresa);
      $tipo_medicamento->descripcion = $request->get('txtTipMed');
      $tipo_medicamento->update();
      return Redirect::to('/tiposmedicamentos');

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
      $tipo_medicamento= tipo_medicamento::findOrFail($id);
      $tipo_medicamento->delete();

      return Redirect::to('/tiposmedicamentos');
    }
}
