<?php

namespace MasterSoft\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use MasterSoft\gastos_cabecera;
use MasterSoft\gastos_detalle;
use MasterSoft\usuario_gastos;
use MasterSoft\Proveedor;
use MasterSoft\movimientos;
use MasterSoft\User;
use MasterSoft\productos;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Input;
use DB;

class RecursosHumanosController extends Controller
{
   /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

     public function __construct()
    {
        $this->middleware('auth');
    }


    public function index($codgast=0,request $request)
    {   
        $tipo = $request->get('tipo');

        $negocios = DB::tABLE('empresa_negocios')->get();

        $sucursal = $request->get('sucursal');

        $colaborador = $request->get('personal');

        $personal = DB::tABLE('users')->get();

        $rucemp = trim(Auth::user()->IdEmpresa);

        if(Auth::user()->hasRole('admin')){

            $gastos = DB::tABLE('gastos_cabecera')
            ->leftjoin('moneda','gastos_cabecera.mon_id','moneda.moncod')
            ->leftjoin('tipo_documento','gastos_cabecera.tdocod','tipo_documento.tdocod')
            ->leftjoin('usuario_gastos','usuario_gastos.gast_cab_id','gastos_cabecera.gast_cab_id')
            ->leftjoin('turnos','turnos.id_turno','usuario_gastos.id_turno')
            ->join('users','users.IdUsuario','gastos_cabecera.personal')
            ->where('gastos_cabecera.id_empresa_negocio',$sucursal)
            ->Where(function($query) use ($colaborador){
                    if($colaborador !=0){
                        $query->where('gastos_cabecera.personal',$colaborador);
                    }
                    
            })
            ->orderby('gastos_cabecera.gast_Cab_id','desc')->get();

        }else{

            $gastos = DB::tABLE('gastos_cabecera')
            ->leftjoin('proveedor','gastos_cabecera.prov_id','proveedor.prov_id')
            ->leftjoin('moneda','gastos_cabecera.mon_id','moneda.moncod')
            ->leftjoin('tipo_documento','gastos_cabecera.tdocod','tipo_documento.tdocod')
            ->leftjoin('usuario_gastos','usuario_gastos.gast_cab_id','gastos_cabecera.gast_cab_id')
            ->leftjoin('turnos','turnos.id_turno','usuario_gastos.id_turno')
            ->join('users','users.IdUsuario','gastos_cabecera.personal')
            ->where('gastos_cabecera.id_empresa_negocio',$sucursal)
          
            ->orderby('gastos_cabecera.gast_Cab_id','desc')->get();

        }
     

        return view('recursoshumanos.pagospersonal.index',compact('personal','gastos','codgast','negocios','sucursal','tipo','colaborador'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        $igv = DB::tABLE('tipo_igv')->where('tigest','=','Activo')
        ->orderBy('tigcod','asc')->get();

        // consultar unidades de medida
        $unidades = DB::tABLE('unidad_medida')->where('umeest','=','Activo')
        ->orderBy('umecod','asc')->get();


        // consultar tipos de documentos de identidad
        $docidentidad = DB::tABLE('tipo_documento_identidad')->where('tdiest','=','Activo')->orderBy('tdicod','asc')->get();

        //consultar tipo de documento
        $doccomprobante = DB::tABLE('tipo_documento')->where('rrhh','1')->orderBy('tdocod','asc')->get();

        // obtener el ruc de la empresa en la cual se logueo
        $rucemp = trim(Auth::user()->IdEmpresa);


        // consultar tipos de monedas
        $monedas = DB::tABLE('moneda')->where('monest','=','Activo')->orderby('moncod','asc')->get();

        // consultar la serie y numero de factura

        $fecha = now()->format('m/d/Y');

        $negocios = DB::tABLE('empresa_negocios')->get();

            $gastos = DB::tABLE('tipo_gastos')->get();

        return view('recursoshumanos.pagospersonal.nuevagasto',compact('negocios','igv','monedas','unidades','docidentidad','fecha','doccomprobante','cpe','codgast','gastos'));

    }

       public function gastospersonal()
    {

        $igv = DB::tABLE('tipo_igv')->where('tigest','=','Activo')
        ->orderBy('tigcod','asc')->get();

        // consultar unidades de medida
        $unidades = DB::tABLE('unidad_medida')->where('umeest','=','Activo')
        ->orderBy('umecod','asc')->get();

            $gastos = DB::tABLE('tipo_gastos')->get();


        // consultar tipos de documentos de identidad
        $docidentidad = DB::tABLE('tipo_documento_identidad')->where('tdiest','=','Activo')->orderBy('tdicod','asc')->get();

        //consultar tipo de documento
        $doccomprobante = DB::tABLE('tipo_documento')->where('rrhh','1')->orderBy('tdocod','asc')->get();

        // obtener el ruc de la empresa en la cual se logueo
        $rucemp = trim(Auth::user()->IdEmpresa);


        // consultar tipos de monedas
        $monedas = DB::tABLE('moneda')->where('monest','=','Activo')->orderby('moncod','asc')->get();

        // consultar la serie y numero de factura

         $negocios = DB::tABLE('empresa_negocios')->get();

        $fecha = now()->format('m/d/Y');

        $personal = DB::tABLE('users')->get();

        return view('recursoshumanos.pagospersonal.gasto',compact('gastos','igv','monedas','unidades','docidentidad','fecha','doccomprobante','cpe','negocios','personal'));

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $rucemp = trim(Auth::user()->IdEmpresa);

        $sucursal = $request->get('sucursal');

        $turno = '0';

        if(empty($sucursal)){
            $sucursal = Auth::user()->id_empresa_negocio;
            $turno = Auth::user()->id_turno;
        }

        $tdicod = $request->get('tdocod');
        $personal = $request->get('personal');

        $gastos = new gastos_cabecera;
        $gastos->gast_doc_ser = $request->get('serdoc');
        $gastos->gast_doc_num = $request->get('numdoc');
        $gastos->gast_fec = $request->get('fecreg');
        $gastos->gast_fec_pag = $request->get('fecpag');
        $gastos->total_gast = $request->get('total_gasto');
        $gastos->tdocod = $request->get('tdocod');
        $gastos->estado_pago = 'PENDIENTE';
        $gastos->id_empresa_negocio = $sucursal;
        $gastos->IdUsuario = Auth::user()->IdUsuario;
        $gastos->personal = $personal;
        $gastos->save();

        $usuario_gastos = new usuario_gastos;
        $usuario_gastos->gast_cab_id = $gastos->gast_cab_id;
        $usuario_gastos->id_turno = $turno;
        $usuario_gastos->id_empresa_negocio = $sucursal;
        $usuario_gastos->referencia = 'GASTO';
        $usuario_gastos->save();

        $codgast= $gastos->gast_cab_id;


        $detpro = $request->get('detpro');
        $preuni = $request->get('preuni');
        $tip_gas = $request->get('tip_gas');

    foreach( $tip_gas as $index => $tg ) {

   
        $gastos_det = new gastos_detalle;
        $gastos_det->pre_uni = $preuni[$index];
        $gastos_det->det_gasto = $detpro[$index];
        $gastos_det->total= $preuni[$index];
        $gastos_det->gast_cab_id= $gastos->gast_cab_id;
        $gastos_det->IdEmpresa= $rucemp;
        $gastos_det->tip_gas_id = $tg;
        $gastos_det->save();

    
    }

    return response()->json(['error'=>'error','mensaje'=>'Registrado']);



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
    public function destroy(Request $request,$id)
    {
        $gastos = gastos_cabecera::findOrFail($id);
        $gastos->est_gasto = 'Eliminado';
        $gastos->usu_elimino = Auth::user()->IdUsuario;
        $gastos->update();


        if(Auth::user()->hasRole('admin') || Auth::user()->hasRole('superadmin')){
     
             return Redirect::to('/gastospersonal');
        
        }else{
             return Redirect::to('/caja');
        }
       
    }

    public function detallegastos($id){
        $rucemp = trim(Auth::user()->IdEmpresa);

        $gasto = DB::tABLE('gastos_detalle as cd')
        ->where('gast_cab_id',$id)->where('cd.IdEmpresa',$rucemp)->get();


        return view('recursoshumanos.pagospersonal.detalles',compact('gasto'));
    }

}
