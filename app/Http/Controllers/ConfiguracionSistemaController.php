<?php

namespace MasterSoft\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Input;
use MasterSoft\ConfiguracionImpresoras;
use MasterSoft\ConfiguracionRutas;
use MasterSoft\Empresa;
use DB;


class ConfiguracionSistemaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function listarrutas()
    {
        $rutas = ConfiguracionRutas::paginate(10);
        return view('administrador.configuracionsistema.listarrutassistema',compact('rutas'));
        
    }

    public function crearrutas()
    {
        return view('administrador.configuracionsistema.crearrutassistema');
    }

     public function editarrutas($id)
    {
        $ruta = Configuracionrutas::FindOrFail($id);
        return view('administrador.configuracionsistema.editarrutasistema',compact('ruta'));
    }

    public function impresorapredeterminada($impresora){

        ConfiguracionImpresoras::where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
        ->update(['predeterminado'=>0]);

        $predeterminada = ConfiguracionImpresoras::FindOrFail($impresora);
        $predeterminada->predeterminado = '1';
        $predeterminada->update();

    }

    public function actualizarruta(Request $request)
    {
        $ruta = Configuracionrutas::FindOrFail($request->get('Id'));
        $ruta->descripcion = $request->get('descripcion');
        $ruta->ruta = $request->get('ruta');
        $ruta->update();

        return Redirect::to('/rutas/listarrutas');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function registrarruta(Request $request)
    {
        $ruta = new Configuracionrutas;
        $ruta->descripcion = $request->get('descripcion');
        $ruta->ruta = $request->get('ruta');
        $ruta->save();

        return Redirect::to('/rutas/listarrutas');
    }


    public function eliminarruta(Request $request){

        Configuracionrutas::where('Id',$request->get('Id'))->delete();

       return Redirect::to('/rutas/listarrutas');
    }

  
    public function listarimpresoras($id_empresa_negocio)
    {
        $impresoras = ConfiguracionImpresoras::where('id_empresa_negocio',$id_empresa_negocio)->paginate(10);
        return view('administrador.configuracionsistema.listarimpresoras',compact('impresoras','id_empresa_negocio'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function crearimpresoras($id_empresa_negocio)
    {
        return view('administrador.configuracionsistema.crearimpresora',compact('id_empresa_negocio'));
    }

     public function editarimpresoras($id,$id_empresa_negocio)
    {
        $impresora = ConfiguracionImpresoras::FindOrFail($id);
        return view('administrador.configuracionsistema.editarimpresora',compact('impresora','id_empresa_negocio'));
    }

    public function actualizarimpresora(Request $request)
    {
        $impresora = ConfiguracionImpresoras::FindOrFail($request->get('Id'));
        $impresora->descripcion = $request->get('impresora');
        $impresora->ruta = $request->get('ruta');
        $impresora->update();

        return Redirect::to('/impresoras/listarimpresoras/'.$request->get('id_empresa_negocio'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function registrarimpresora(Request $request)
    {
        $impresora = new ConfiguracionImpresoras;
        $impresora->descripcion = $request->get('impresora');
        $impresora->ruta = $request->get('ruta');
        $impresora->id_empresa_negocio = $request->get('id_empresa_negocio');
        $impresora->save();

        return Redirect::to('/impresoras/listarimpresoras/'.$request->get('id_empresa_negocio'));
    }


    public function eliminarimpresora(Request $request){

        ConfiguracionImpresoras::where('Id',$request->get('Id'))->delete();

       return Redirect::to('/impresoras/listarimpresoras/'.$request->get('id_empresa_negocio'));
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
        //
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
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
