<?php

namespace MasterSoft\Http\Controllers;
use Illuminate\Http\Request;
use MasterSoft\Http\Requests;
use MasterSoft\configuracion_concar;
use Illuminate\Support\Facades\Auth;
use MasterSoft\Http\Requests\FacturaCreateFormRequest;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Input;
use DB;

class ConfiguracionContableController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
  

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
 

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
  

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
    public function editar()
    {
     
      $configuracion_concar =configuracion_concar::first();
      return view('empresas.configuracion_concar.editar',compact('configuracion_concar'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function actualizar_configuracion_concar(Request $request)
    {

      $bus_config = configuracion_concar::first();

      if(!empty($bus_config)){
      	$configuracion = configuracion_concar::findOrFail($bus_config->id_conf_conc);
      	$configuracion->cod_d_prov_fac = $request->get('txt_cod_d_prov_fac');
        $configuracion->cod_h_prov_fac = $request->get('txt_cod_h_prov_fac');
        $configuracion->cod_d_prov_bol = $request->get('txt_cod_d_prov_bol');
        $configuracion->cod_h_prov_bol = $request->get('txt_cod_h_prov_bol');
      	$configuracion->cod_ven_con = $request->get('txt_cod_ven_con');
        $configuracion->cod_ven_anu = $request->get('txt_cod_ven_anu');
      	$configuracion->cod_d_can = $request->get('txt_cod_d_can');
        $configuracion->cod_h_can_fac = $request->get('txt_cod_h_can_fac');
        $configuracion->cod_h_can_bol = $request->get('txt_cod_h_can_bol');
        $configuracion->provision = $request->get('txt_provision');
        $configuracion->cancelacion = $request->get('txt_cancelacion');
        $configuracion->prov_corre = $request->get('txt_prov_corre');
        $configuracion->canc_corre = $request->get('txt_canc_corre');
      	$configuracion->update();
      }else{
      	$configuracion = new configuracion_concar;
      	$configuracion->cod_d_prov_fac = $request->get('txt_cod_d_prov_fac');
        $configuracion->cod_h_prov_fac = $request->get('txt_cod_h_prov_fac');
        $configuracion->cod_d_prov_bol = $request->get('txt_cod_d_prov_bol');
        $configuracion->cod_h_prov_bol = $request->get('txt_cod_h_prov_bol');
        $configuracion->cod_ven_con = $request->get('txt_cod_ven_con');
        $configuracion->cod_ven_anu = $request->get('txt_cod_ven_anu');
        $configuracion->cod_d_can = $request->get('txt_cod_d_can');
        $configuracion->cod_h_can_fac = $request->get('txt_cod_h_can_fac');
        $configuracion->cod_h_can_bol = $request->get('txt_cod_h_can_bol');
        $configuracion->provision = $request->get('txt_provision');
        $configuracion->cancelacion = $request->get('txt_cancelacion');
        $configuracion->prov_corre = $request->get('txt_prov_corre');
        $configuracion->canc_corre = $request->get('txt_canc_corre');
      	$configuracion->save();
      }
     


      return Redirect::to('/SisFact');

    }

  
}
