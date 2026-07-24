<?php

namespace MasterSoft\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use MasterSoft\Series;
use Illuminate\Support\Facades\Redirect;
use DB;

class SeriesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        
        $IdEmpresa = Auth::user()->IdEmpresa;
        $list_series = DB::tABLE('fe_series as ser')->join('tipo_documento as tip_d','ser.tipo_documento','=','tip_d.tdocod')->where('IdEmpresa','=',$IdEmpresa)->get();

        return view('empresas.series.index',['list_series'=>$list_series]);
  
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $tipo_doc = DB::tABLE('tipo_documento')->get();
        return view('empresas.series.crear',['tipo_doc'=>$tipo_doc]);
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
        $serie = new Series();
        $serie->IdEmpresa = $IdEmpresa;
        $serie->Tipo_Documento = $request->get('tdocod');
        $serie->Numero_Serie = $request->get('serie');
        $serie->Num_Correlativo = $request->get('numcor');
        $serie->save();
        return Redirect::to('/series');
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
        $serie = Series::findOrFail($id);
        $tipo_doc = DB::tABLE('tipo_documento')->get();
     
        return view('empresas.series.editar',['serie'=>$serie,'tipo_doc'=>$tipo_doc]);
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
        $serie= Series::findOrFail($id);
        $serie->Num_Correlativo = $request->get('numcor');
        $serie->update();
        return Redirect::to('/series');
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

        return Redirect::to('/series');
    }
}
