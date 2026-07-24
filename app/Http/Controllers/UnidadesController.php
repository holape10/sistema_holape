<?php

namespace MasterSoft\Http\Controllers;
use Illuminate\Http\Request;
use MasterSoft\Http\Requests;
use MasterSoft\unidad_medida;
use Illuminate\Support\Facades\Auth;
use MasterSoft\Http\Requests\FacturaCreateFormRequest;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Input;
use DB;

class UnidadesController extends Controller
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
            $unidades = DB::tABLE('unidad_medida')->where('IdEmpresa',$rucemp)->paginate(10);
        }else{
             $unidades = DB::tABLE('unidad_medida')->where('umenom',$buscar)->where('IdEmpresa',$rucemp)->paginate(10);
        }
        return view('empresas.unidades.index',compact('unidades','buscar'));
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        return view('empresas.unidades.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $unidades = new unidad_medida
        ;
        $unidades->IdEmpresa = trim(Auth::user()->IdEmpresa);
        $unidades->umenom = $request->get('txtUniMed');
        $unidades->umecin = $request->get('txtCodUniMed');
        $unidades->umecod = $request->get('txtCodUniMed');
        $unidades->save();
        return Redirect::to('/unidades');
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
      $unidades=unidad_medida::findOrFail($id);
      return view('empresas.unidades.edit',compact('unidades'));
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
      $unidades=unidades::findOrFail($id);
      $unidades->IdEmpresa = trim(Auth::user()->IdEmpresa);
      $unidades->umenom = $request->get('txtUniMed');
        $unidades->umecin = $request->get('txtCodUniMed');
        $unidades->umecod = $request->get('txtCodUniMed');
      $unidades->update();
      return Redirect::to('/unidades');

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
      $unidades= unidad_medida::findOrFail($id);
      $unidades->delete();

      return Redirect::to('/unidades');
    }
}
