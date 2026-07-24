<?php

namespace MasterSoft\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use MasterSoft\tipocambio;
use Illuminate\Support\Facades\Redirect;
use DB;

class TipoCambioController extends Controller
{
     /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        
        $IdEmpresa = Auth::user()->IdEmpresa;
        $list_tc = DB::tABLE('tipocambio')->get();

        return view('empresas.tipocambio.index',['list_tc'=>$list_tc]);
  
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $tipo_doc = DB::tABLE('tipo_documento')->get();
        return view('empresas.tipocambio.crear',['tipo_doc'=>$tipo_doc]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $IdEmpresa = Auth::user()->IdEmpresa;
        $tipocambio = new tipocambio();
        $tipocambio->IdEmpresa = $IdEmpresa;
        $tipocambio->FecTipCambio = $request->get('fecha');
        $tipocambio->CamCompra = $request->get('tccompra');
        $tipocambio->CamVenta = $request->get('tcventa');
        $tipocambio->save();
        return Redirect::to('/tipocambio');
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
        $IdEmpresa = Auth::user()->IdEmpresa;
        $tipocambio = tipocambio::findOrFail($id);
     
        return view('empresas.tipocambio.editar',['tipocambio'=>$tipocambio]);
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
        $tipocambio= tipocambio::findOrFail($id);
        $tipocambio->CamVenta = $request->get('tccompra');
        $tipocambio->CamCompra = $request->get('tcventa');
        $tipocambio->update();
        return Redirect::to('/tipocambio');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($idserie)
    {
        $est_serie = DB::tABLE('fe_series')->select('Estado')->where('IdSerie','=',$idserie)->first();

        if($est_serie->Estado=='1'){
            $estNuevo = '0';
        }else{
            $estNuevo = '1';
        }

        $serie =Series::findOrFail($idserie);
        $serie->Estado = $estNuevo;
        $serie->update();

        return Redirect::to('/tipocambio');
    }
}
