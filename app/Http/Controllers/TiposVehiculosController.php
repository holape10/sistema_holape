<?php

namespace MasterSoft\Http\Controllers;
use Illuminate\Http\Request;
use MasterSoft\Http\Requests;
use MasterSoft\tipos_vehiculos;
use MasterSoft\cliente;
use Illuminate\Support\Facades\Auth;
use MasterSoft\Http\Requests\FacturaCreateFormRequest;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Input;
use DB;

class TiposVehiculosController extends Controller
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
            $vehiculos = DB::tABLE('tipos_vehiculos')->where('IdEmpresa',$rucemp)->paginate(10);
        }else{
             $vehiculos = DB::tABLE('tipos_vehiculos')->where('placa','like','%'.$buscar.'%')->where('IdEmpresa',$rucemp)->paginate(10);
        }
        return view('empresas.tiposvehiculos.index',compact('vehiculos','buscar'));
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        $documentos = DB::tABLE('tipo_documento_identidad')->get();
        $combustible = DB::tABLE('combustible')->get();
        $marcas = DB::tABLE('marcas')->get();
        $modelos = DB::tABLE('modelos')->get();
        $tecnicos = DB::tABLE('tecnicos')->get();
        $clientes = DB::tABLE('cliente')->get();
        $condiciones = DB::tABLE('condicionespago')->get();
        
        return view('empresas.tiposvehiculos.create',compact('documentos','marcas','modelos','combustible','tecnicos','clientes','marcas','modelos'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {   

        $cliente = new Cliente;
        $cliente->rucemp = trim(Auth::user()->IdEmpresa);
        $cliente->tdicod = $request->get('tdicod');
        $cliente->clinum = $request->get('clinum');
        $cliente->clidir = $request->get('clidir');
        $cliente->clinom = $request->get('clinom');
        $cliente->clicor = $request->get('clicor');
        $cliente->clitel = $request->get('clitel');
        $cliente->clicon = $request->get('clicon');
        $cliente->clicontel = $request->get('clicontel');
        $cliente->tar_id = $request->get('tarifa');
        $cliente->save();


        $vehiculos = new tipos_vehiculos;
        $vehiculos->IdEmpresa = trim(Auth::user()->IdEmpresa);
        $vehiculos->mar_id = $request->get('marca');
        $vehiculos->mod_id = $request->get('modelo');
        $vehiculos->comb_id = $request->get('combustible');
        $vehiculos->clicod = $cliente->clicod;
        $vehiculos->kilometros = $request->get('kilometros');
        $vehiculos->observaciones = $request->get('observaciones');
        $vehiculos->placa = $request->get('placa');
        $vehiculos->kilometros = $request->get('kilometros');
        $vehiculos->cilindrada = $request->get('cilindrada'); 
        $vehiculos->fecinspeccion = $request->get('fecinspeccion');
        $vehiculos->bastidor = $request->get('chasis');
        $vehiculos->fecrevision = $request->get('fecrevision');
        $vehiculos->fecsoat = $request->get('fecsoat');
         $vehiculos->color = $request->get('color');
         $vehiculos->ano = $request->get('ano');
        $vehiculos->id_empresa_negocio = Auth::user()->id_empresa_negocio;
        $vehiculos->save();

        return Redirect::to('/tiposvehiculos');
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
      

        $documentos = DB::tABLE('tipo_documento_identidad')->get();
        $combustible = DB::tABLE('combustible')->get();
        $marcas = DB::tABLE('marcas')->get();
        $modelos = DB::tABLE('modelos')->get();
        $tecnicos = DB::tABLE('tecnicos')->get();
        $clientes = DB::tABLE('cliente')->get();
        $condiciones = DB::tABLE('condicionespago')->get();
        

      $vehiculos=tipos_vehiculos::where('id_tipo_vehiculo',$id)
                 ->leftjoin('cliente','cliente.clicod','tipos_vehiculos.clicod')
                 ->leftjoin('tipo_documento_identidad','tipo_documento_identidad.tdicod','cliente.tdicod')
                 ->leftjoin('marcas','marcas.mar_id','tipos_vehiculos.mar_id')
                 ->leftjoin('modelos','modelos.mod_id','tipos_vehiculos.mod_id')
                 ->leftjoin('combustible','combustible.comb_id','tipos_vehiculos.comb_id')->first();

      return view('empresas.tiposvehiculos.edit',compact('vehiculos','documentos','combustible','marcas','modelos','tecnicos','clientes','condiciones'));
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
    

        $buscar =  DB::tABLE('cliente')->where('clinum',$request->get('clinum'))->count();


        if($buscar=='0'){

            $cliente = new Cliente;
            $cliente->rucemp = trim(Auth::user()->IdEmpresa);
            $cliente->tdicod = $request->get('tdicod');
            $cliente->clinum = $request->get('clinum');
            $cliente->clidir = $request->get('clidir');
            $cliente->clinom = $request->get('clinom');
            $cliente->clicor = $request->get('clicor');
            $cliente->clitel = $request->get('clitel');
            $cliente->clicon = $request->get('clicon');
            $cliente->clicontel = $request->get('clicontel');
            $cliente->tar_id = $request->get('tarifa');
            $cliente->save();

        }else{
            $cliente = Cliente::findOrFail($request->get('clicod'));
            $cliente->rucemp = trim(Auth::user()->IdEmpresa);
            $cliente->tdicod = $request->get('tdicod');
            $cliente->clinum = $request->get('clinum');
            $cliente->clidir = $request->get('clidir');
            $cliente->clinom = $request->get('clinom');
            $cliente->clicor = $request->get('clicor');
            $cliente->clitel = $request->get('clitel');
            $cliente->clicon = $request->get('clicon');
            $cliente->clicontel = $request->get('clicontel');
            $cliente->tar_id = $request->get('tarifa');
            $cliente->update();
        }
       


        $vehiculos = tipos_vehiculos::findOrFail($id);
        $vehiculos->IdEmpresa = trim(Auth::user()->IdEmpresa);
        $vehiculos->mar_id = $request->get('marca');
        $vehiculos->mod_id = $request->get('modelo');
        $vehiculos->comb_id = $request->get('combustible');
        $vehiculos->clicod = $cliente->clicod;
        $vehiculos->kilometros = $request->get('kilometros');
        $vehiculos->observaciones = $request->get('observaciones');
        $vehiculos->placa = $request->get('placa');
        $vehiculos->kilometros = $request->get('kilometros');
        $vehiculos->cilindrada = $request->get('cilindrada'); 
        $vehiculos->fecinspeccion = $request->get('fecinspeccion');
        $vehiculos->bastidor = $request->get('chasis');
        $vehiculos->fecrevision = $request->get('fecrevision');
        $vehiculos->fecsoat = $request->get('fecsoat');
        $vehiculos->color = $request->get('color');
        $vehiculos->ano = $request->get('ano');
        $vehiculos->id_empresa_negocio = Auth::user()->id_empresa_negocio;
        $vehiculos->update();
      return Redirect::to('/tiposvehiculos');

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
      $vehiculos= tipos_vehiculos::findOrFail($id);
      $vehiculos->delete();

      return Redirect::to('/tiposvehiculos');
    }
}
