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

class GastosController extends Controller
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

        $fecin = $request->get('fecin');
        $fecfin = $request->get('fecfin');

        if(empty($fecin)){

          $fecin = now()->modify('first day of this month')->format('Y-m-d');
          $fecfin = now()->modify('last day of this month')->format('Y-m-d');

        }
        

        $negocios = DB::tABLE('empresa_negocios')->get();

        $sucursal = $request->get('sucursal');

        $rucemp = trim(Auth::user()->IdEmpresa);

        if(Auth::user()->hasRole('admin')){

            $gastos = DB::tABLE('gastos_cabecera')
            ->leftjoin('proveedor','gastos_cabecera.prov_id','proveedor.prov_id')
            ->leftjoin('moneda','gastos_cabecera.mon_id','moneda.moncod')
            ->leftjoin('tipo_documento','gastos_cabecera.tdocod','tipo_documento.tdocod')
            ->leftjoin('usuario_gastos','usuario_gastos.gast_cab_id','gastos_cabecera.gast_cab_id')
            ->leftjoin('turnos','turnos.id_turno','usuario_gastos.id_turno')
            ->leftjoin('users','users.IdUsuario','gastos_cabecera.IdUsuario')
            ->where('gastos_cabecera.id_empresa_negocio',$sucursal)
            ->where('est_gasto','!=','Eliminado')
            ->Where(function($query) use ($tipo){
                    if($tipo !='0'){
                        $query->where('tipo_movimiento',$tipo);
                    }
                    
            })
             ->where('gast_fec','>=',$fecin)
            ->where('gast_fec','<=',$fecfin)
            ->orderby('gastos_cabecera.gast_Cab_id','desc')->get();

        }else{

            $gastos = DB::tABLE('gastos_cabecera')
            ->leftjoin('proveedor','gastos_cabecera.prov_id','proveedor.prov_id')
            ->leftjoin('moneda','gastos_cabecera.mon_id','moneda.moncod')
            ->leftjoin('tipo_documento','gastos_cabecera.tdocod','tipo_documento.tdocod')
            ->leftjoin('usuario_gastos','usuario_gastos.gast_cab_id','gastos_cabecera.gast_cab_id')
            ->leftjoin('turnos','turnos.id_turno','usuario_gastos.id_turno')
            ->leftjoin('users','users.IdUsuario','gastos_cabecera.IdUsuario')
            ->where('gastos_cabecera.id_empresa_negocio',$sucursal)
            ->where('est_gasto','!=','Eliminado')
            ->where('gast_fec','>=',$fecin)
            ->where('gast_fec','<=',$fecfin)
            ->Where(function($query) use ($tipo){
                    if($tipo !='0'){
                        $query->where('tipo_movimiento',$tipo);
                    }
                    
            })
                ->Where(function($query) use ($tipo){
                    
                     $query->where('gastos_cabecera.IdUsuario',Auth::user()->IdUsuario)
                     ->orwhere('colaborador',Auth::user()->IdUsuario);
                                        
            })
            ->orderby('gastos_cabecera.gast_Cab_id','desc')->get();

        }
     

        return view('empresas.gastos.index',compact('gastos','codgast','negocios','sucursal','tipo'));
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
        $doccomprobante = DB::tABLE('tipo_documento')->where('tdoest','=','Activo')->orderBy('tdocod','asc')->get();

        // obtener el ruc de la empresa en la cual se logueo
        $rucemp = trim(Auth::user()->IdEmpresa);


        // consultar tipos de monedas
        $monedas = DB::tABLE('moneda')->where('monest','=','Activo')->orderby('moncod','asc')->get();

        // consultar la serie y numero de factura

        $fecha = now()->format('m/d/Y');

        $negocios = DB::tABLE('empresa_negocios')->get();

            $gastos = DB::tABLE('tipo_gastos')->get();

        return view('empresas.gastos.nuevagasto',compact('negocios','igv','monedas','unidades','docidentidad','fecha','doccomprobante','codgast','gastos'));

    }

       public function gastoproductos()
    {

        $igv = DB::tABLE('tipo_igv')->where('tigest','=','Activo')
        ->orderBy('tigcod','asc')->get();

        // consultar unidades de medida
        $unidades = DB::tABLE('unidad_medida')->where('umeest','=','Activo')
        ->orderBy('umecod','asc')->get();

            $gastos = DB::tABLE('tipo_gastos')->orderby('tip_gas_nom','asc')->get();


        // consultar tipos de documentos de identidad
        $docidentidad = DB::tABLE('tipo_documento_identidad')->where('tdiest','=','Activo')->orderBy('tdicod','asc')->get();

        //consultar tipo de documento
        $doccomprobante = DB::tABLE('tipo_documento')->where('tdoest','=','Activo')->orderBy('tdocod','asc')->get();

        // obtener el ruc de la empresa en la cual se logueo
        $rucemp = trim(Auth::user()->IdEmpresa);


        // consultar tipos de monedas
        $monedas = DB::tABLE('moneda')->where('monest','=','Activo')->orderby('moncod','asc')->get();

        // consultar la serie y numero de factura

         $negocios = DB::tABLE('empresa_negocios')->get();

        $fecha = now()->format('m/d/Y');

             /*$colaboradores = DB::tABLE('users')
        ->join('role_user','role_user.user_IdUsuario','users.IdUsuario')
        ->where('role_id','4')
        ->where('turno','Aperturado')
        ->get();*/
        $colaboradores = DB::tABLE('users')
        ->orderby('name','asc')
        ->get(); // <--- ¡Esto ya trae a TODOS los usuarios!


        return view('empresas.gastos.gasto',compact('gastos','igv','monedas','unidades','docidentidad','fecha','doccomprobante','negocios','colaboradores'));

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
    $colaborador_seleccionado = $request->get('colaborador'); // El ID del usuario seleccionado en el SELECT (puede ser '0')

    // 1. Determinar el TURNO: Debe ser el turno aperturado del USUARIO LOGUEADO
    $bus_tur = DB::tABLE('turnos')->where('IdUsuario', Auth::user()->IdUsuario)
                                  ->where('estado', 'Aperturado')
                                  ->first();

    if (empty($bus_tur)) {
        // Si el USUARIO LOGUEADO no tiene un turno abierto, NO puede registrar el gasto.
        return response()->json(['estado'=>'error','mensaje'=>'El usuario logueado no tiene un turno aperturado. Debe aperturar caja.']);
    }
    
    // 2. Asignar el turno encontrado (del usuario logueado).
    $turno = $bus_tur->id_turno;

    // 3. Determinar el ID del colaborador a quien se le ASIGNA el gasto (puede ser '0' si no se seleccionó).
    // Si no se selecciona nadie, se guarda '0' o el valor que envíe el select por defecto.
    $id_colaborador_gasto = (empty($colaborador_seleccionado) || $colaborador_seleccionado == '0') ? '0' : $colaborador_seleccionado;

    // --- Resto de variables de la petición ---
    $tdicod = $request->get('cmbTdi');
    $movimiento = $request->get('cmbmovimiento');
    $prov_num = $request->get('txtProvNum');
    $prov_raz = $request->get('txtProvRaz');
    $prov_con = $request->get('txtProvCon');
    $prov_num_con = $request->get('txtProvNumCon');
    $prov_dir = $request->get('txtProvDir');
    $prov_cor = $request->get('txtProvCor');
    $moncod = $request->get('mondoc');

    // --- Guardado de Cabecera (gastos_cabecera) ---
    $gastos = new gastos_cabecera;
    $gastos->gast_doc_ser = $request->get('serdoc');
    $gastos->gast_doc_num = $request->get('numdoc');
    $gastos->gast_fec = $request->get('fecEmi');
    $gastos->gast_fec_ven = $request->get('fecVen');
    $gastos->mon_id = $request->get('mondoc');

    
    $proveedor = Proveedor::FirstOrCreate(['prov_ruc'=>$prov_num,'IdEmpresa'=>$rucemp],['prov_raz'=>$prov_raz,'tdicod'=>$tdicod,'prov_con'=>$prov_con,'prov_num_con'=>$prov_num_con,'prov_cor'=>$prov_cor,'prov_dir'=>$prov_dir,'IdEmpresa'=>$rucemp]);
    
    $gastos->prov_id = $proveedor->prov_id;
    $gastos->tip_cam = $request->get('camdoc');
    $gastos->tot_igv = $request->get('igv');
    $gastos->tot_grav = $request->get('grav');
    $gastos->tot_grat = $request->get('grat');
    $gastos->tot_exon = $request->get('exon');
    $gastos->tot_inaf = $request->get('inaf');
    $gastos->tot_desc_por = $request->get('desc');
    $gastos->tot_desc = $request->get('totdesc');
    $gastos->tot_otr_car = $request->get('otrosc');
    $gastos->tot_exp = $request->get('otros');
    $gastos->tot_otr_tri = $request->get('exp');
    $gastos->total_gast = $request->get('total_gasto');
    $gastos->gast_obs = $request->get('obser');
    $gastos->tdocod = $request->get('cmbTdo');
    $gastos->id_empresa_negocio = $sucursal;
    $gastos->tipo_movimiento = $movimiento;
    $gastos->IdUsuario = Auth::user()->IdUsuario; // Usuario que REGISTRA el gasto (el administrador logueado)
    $gastos->colaborador = $id_colaborador_gasto; // Usuario al que se ASIGNA el gasto (del select o '0')
    $gastos->save();

    // --- Guardado de Usuario Gasto (usuario_gastos) ---
    $usuario_gastos = new usuario_gastos;
    $usuario_gastos->gast_cab_id = $gastos->gast_cab_id;
    $usuario_gastos->id_turno = $turno; // Turno del usuario LOGUEADO
    $usuario_gastos->id_empresa_negocio = $sucursal;
    $usuario_gastos->IdUsuario = Auth::user()->IdUsuario;
    $usuario_gastos->referencia = $movimiento;
    $usuario_gastos->save();

    $codgast= $gastos->gast_cab_id;

    $cantidades = $request->get('cant');
    $unidades = $request->get('unid');
    $codpro = $request->get('codpro');
    $detpro = $request->get('detpro');
    $vunit = $request->get('vunit');
    $preuni = $request->get('preuni');
    $vigv = $request->get('vigv');
    $tigv = $request->get('tigv');
    $vsub = $request->get('vsub');
    $vtot = $request->get('vtot');
    $premin = $request->get('prevenmin');
    $premay = $request->get('prevenmay');
    $pro_id = $request->get('pro_id');
    $tip_gas = $request->get('tip_gas');
    
    foreach( $preuni as $index => $puni ) {

   
        $gastos_det = new gastos_detalle;
     
        $gastos_det->pre_uni = $puni;
        $gastos_det->det_gasto = $detpro[$index];
        $gastos_det->total= $puni;
        $gastos_det->gast_cab_id= $gastos->gast_cab_id;
        $gastos_det->IdEmpresa= $rucemp;
        $gastos_det->tip_gas_id = $tip_gas[$index];
        $gastos_det->save();

        $movimiento = new movimientos;
        $movimiento->mov_fec = $gastos->gast_fec; 
        $movimiento->mov_tip = 'I';
        $movimiento->mov_mot = 'gasto';
        $movimiento->cantidad = '1';
        $movimiento->IdEmpresa = $rucemp;
       // $movimiento->IdProducto = $IdProducto->IdProducto;
        $movimiento->save();

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
        
        $gasto_cab = DB::tABLE('gastos_cabecera')->where('gast_cab_id',$id)
        ->leftjoin('proveedor','proveedor.prov_id','gastos_cabecera.prov_id')->first();

        $gasto_det = DB::tABLE('gastos_detalle')->where('gast_cab_id',$id)->get();

        $igv = DB::tABLE('tipo_igv')->where('tigest','=','Activo')
        ->orderBy('tigcod','asc')->get();

        // consultar unidades de medida
        $unidades = DB::tABLE('unidad_medida')->where('umeest','=','Activo')
        ->orderBy('umecod','asc')->get();

            $gastos = DB::tABLE('tipo_gastos')->orderby('tip_gas_nom','asc')->get();


        // consultar tipos de documentos de identidad
        $docidentidad = DB::tABLE('tipo_documento_identidad')->where('tdiest','=','Activo')->orderBy('tdicod','asc')->get();

        //consultar tipo de documento
        $doccomprobante = DB::tABLE('tipo_documento')->where('tdoest','=','Activo')->orderBy('tdocod','asc')->get();

        // obtener el ruc de la empresa en la cual se logueo
        $rucemp = trim(Auth::user()->IdEmpresa);


        // consultar tipos de monedas
        $monedas = DB::tABLE('moneda')->where('monest','=','Activo')->orderby('moncod','asc')->get();

        // consultar la serie y numero de factura

         $negocios = DB::tABLE('empresa_negocios')->get();

        $fecha = now()->format('m/d/Y');

             $colaboradores = DB::tABLE('users')
        ->join('role_user','role_user.user_IdUsuario','users.IdUsuario')
        ->where('role_id','4')
        ->where('turno','Aperturado')
        ->get();


        return view('empresas.gastos.editar_gasto',compact('gastos','igv','monedas','unidades','docidentidad','fecha','doccomprobante','negocios','colaboradores','gasto_cab','gasto_det'));


    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function actualizar_gasto(Request $request)
    {
        

        $id = $request->get('id');

        $rucemp = trim(Auth::user()->IdEmpresa);

        $sucursal = $request->get('sucursal');
        $colaborador = $request->get('colaborador');

        if(Auth::user()->hasRole('admin')){

             if(!empty($colaborador)){

                    $bus_tur = DB::tABLE('turnos')->where('IdUsuario',$colaborador)->where('estado','Aperturado')->first();
                    $turno = $bus_tur->id_turno;

                    return response()->json(['estado'=>'error','mensaje'=>'El Cajero seleccionado ya cerró turno']);

                }else{
                     $turno=Auth::user()->id_turno;
                }

           
        }else{

            $turno = Auth::user()->id_turno;
        }
       
       
   
        $tdicod = $request->get('cmbTdi');
        $movimiento = $request->get('cmbmovimiento');
        $prov_num = $request->get('txtProvNum');
        $prov_raz = $request->get('txtProvRaz');
        $prov_con = $request->get('txtProvCon');
        $prov_num_con = $request->get('txtProvNumCon');
        $prov_dir = $request->get('txtProvDir');
        $prov_cor = $request->get('txtProvCor');
        $moncod = $request->get('mondoc');
        $colaborador = $request->get('colaborador');

        $gastos = gastos_cabecera::findOrFail($id);
        $gastos->gast_doc_ser = $request->get('serdoc');
        $gastos->gast_doc_num = $request->get('numdoc');
        $gastos->gast_fec = $request->get('fecEmi');
        $gastos->gast_fec_ven = $request->get('fecVen');
        $gastos->mon_id = $request->get('mondoc');

    
        $proveedor = Proveedor::FirstOrCreate(['prov_ruc'=>$prov_num,'IdEmpresa'=>$rucemp],['prov_raz'=>$prov_raz,'tdicod'=>$tdicod,'prov_con'=>$prov_con,'prov_num_con'=>$prov_num_con,'prov_cor'=>$prov_cor,'prov_dir'=>$prov_dir,'IdEmpresa'=>$rucemp]);
    
        $gastos->prov_id = $proveedor->prov_id;
        $gastos->tip_cam = $request->get('camdoc');
        $gastos->tot_igv = $request->get('igv');
        $gastos->tot_grav = $request->get('grav');
        $gastos->tot_grat = $request->get('grat');
        $gastos->tot_exon = $request->get('exon');
        $gastos->tot_inaf = $request->get('inaf');
        $gastos->tot_desc_por = $request->get('desc');
        $gastos->tot_desc = $request->get('totdesc');
        $gastos->tot_otr_car = $request->get('otrosc');
        $gastos->tot_exp = $request->get('otros');
        $gastos->tot_otr_tri = $request->get('exp');
        $gastos->total_gast = $request->get('total_gasto');
        $gastos->gast_obs = $request->get('obser');
        $gastos->tdocod = $request->get('cmbTdo');
        $gastos->id_empresa_negocio = $sucursal;
        $gastos->tipo_movimiento = $movimiento;
        $gastos->IdUsuario = Auth::user()->IdUsuario;
        $gastos->colaborador = $colaborador;
        $gastos->update();

        DB::tABLE('usuario_gastos')->where('gast_cab_id',$id)->delete();

        $usuario_gastos = new usuario_gastos;
        $usuario_gastos->gast_cab_id = $gastos->gast_cab_id;
        $usuario_gastos->id_turno = $turno;
        $usuario_gastos->id_empresa_negocio = $sucursal;
        $usuario_gastos->IdUsuario = Auth::user()->IdUsuario;
        $usuario_gastos->referencia = $movimiento;
        $usuario_gastos->save();

        $codgast= $gastos->gast_cab_id;

        $cantidades = $request->get('cant');
        $unidades = $request->get('unid');
        $codpro = $request->get('codpro');
        $detpro = $request->get('detpro');
        $vunit = $request->get('vunit');
        $preuni = $request->get('preuni');
        $vigv = $request->get('vigv');
        $tigv = $request->get('tigv');
        $vsub = $request->get('vsub');
        $vtot = $request->get('vtot');
        $premin = $request->get('prevenmin');
        $premay = $request->get('prevenmay');
        $pro_id = $request->get('pro_id');
        $tip_gas = $request->get('tip_gas');

         DB::tABLE('gastos_detalle')->where('gast_cab_id',$id)->delete();

        foreach( $preuni as $index => $puni ) {

       
            $gastos_det = new gastos_detalle;
         
            $gastos_det->pre_uni = $puni;
            $gastos_det->det_gasto = $detpro[$index];
            $gastos_det->total= $puni;
            $gastos_det->gast_cab_id= $gastos->gast_cab_id;
            $gastos_det->IdEmpresa= $rucemp;
            $gastos_det->tip_gas_id = $tip_gas[$index];
            $gastos_det->save();

         

        }

        return response()->json(['estado'=>'success','mensaje'=>'ACTUALIZADO']);

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $gastos = gastos_cabecera::findOrFail($id);
        $gastos->est_gasto = 'Eliminado';
        $gastos->usu_elimino = Auth::user()->IdUsuario;
        $gastos->update();

        if(Auth::user()->hasRole('admin') || Auth::user()->hasRole('superadmin')){
             return Redirect::to('/gastos');
        }else{
             return Redirect::to('/caja');
        }
       
    }

    public function detallegastos($id){
        $rucemp = trim(Auth::user()->IdEmpresa);

        $gasto = DB::tABLE('gastos_detalle as cd')
        ->where('gast_cab_id',$id)->where('cd.IdEmpresa',$rucemp)->get();


        return view('empresas.gastos.detalles',compact('gasto'));
    }

}
