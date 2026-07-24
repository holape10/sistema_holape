<?php

namespace MasterSoft\Http\Controllers;


use Illuminate\Http\Request;
use MasterSoft\Http\Requests;
use MasterSoft\TipoIGV;
use MasterSoft\Empresa;
use MasterSoft\Cliente;
use MasterSoft\cpe_cabecera;
use MasterSoft\documento_relacionado;
use MasterSoft\unidad_medida;
use MasterSoft\cpe_detalle;
use MasterSoft\cpe_baja;
use MasterSoft\Comprobante;
use MasterSoft\cpe_nota_detalle;
use MasterSoft\cpe_nota;
use MasterSoft\tipo_documento_identidad;
use MasterSoft\productos;
use MasterSoft\tipocambio;
use Illuminate\Support\Facades\Auth;
use MasterSoft\Http\Requests\FacturaCreateFormRequest;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Input;


use PDF;
use DB;

class PanelClientesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request,$idempresa)
    {
            session(['rucemp' => $idempresa]);
            $fechaini = now()->modify('first day of this month');
            $fechafin = now()->modify('last day of this month');
            $razsoc = $request->get('searchText');
            $respse = $request->get('tiper');
            $tipdoc = $request->get('docomp');
            $fecin = $request->get('fecin');
            $fecfin = $request->get('fecfin');
            $serdoc=$request->get('serdoc');
            $comp=$request->get('comp');
            $numdoc = $request->get('numdoc');
            $empresa = Empresa::findOrFail(session('rucemp'));
            $doccomprobante = DB::tABLE('tipo_documento')->where('tdoest','=','Activo')->orderBy('tdocod','asc')->pluck('tdodes', 'tdocod');

            $ser = substr($comp,strpos($comp,'-')-4,4);
            $num = substr($comp,strpos($comp,'-')+1,8);

           if(empty($razsoc) && $respse==0 && $tipdoc==0 && empty($fecin) && empty($fecfin) && empty($serdoc) && empty($numdoc) && empty($comp)){

            $compcabecera = DB::tABLE('cpe_cabecera as cpe_c')->select('ccafem','tdodes','cpe_c.serdoc','cpe_c.numdoc','tdides','cpe_c.ccandi','cpe_c.ccanom','monnom','cpe_c.ccaitv','cpe_c.IdEmpresa','cpe_c.tdocod','cpe_c.IdCpe_cabecera','cpe_c.ccanot','cpe_c.ccabaj','cpe_c.respse','cpe_c.codhash')
               ->join('tipo_documento as tip_d','cpe_c.tdocod','=','tip_d.tdocod')
               ->join('tipo_documento_identidad as tdi','cpe_c.tdicod','=','tdi.tdicod')
               ->join('moneda as mon','cpe_c.moncod','=','mon.moncod')
               ->where('cpe_c.IdEmpresa','=',session('rucemp'))
               ->where('ccanom','like','%'.$razsoc.'%')
               ->where('cpe_c.ccafem','>=',$fechaini)
               ->where('cpe_c.ccafem','<=',$fechafin)
               ->orwhere('cpe_c.IdEmpresa','=',session('rucemp'))
               ->where('ccandi','like','%'.$razsoc.'%')
               ->where('cpe_c.ccafem','>=',$fechaini)
               ->where('cpe_c.ccafem','<=',$fechafin);

               $comprobantes = DB::tABLE('cpe_nota as cpe_n')
               ->select('cpe_n.ccafem','tdodes','cpe_n.serdoc','cpe_n.numdoc','tdides','cpe_c.ccandi','cpe_c.ccanom','monnom','cpe_n.ccaitv','cpe_c.IdEmpresa','cpe_n.tdocod','cpe_n.IdCpe_nota','cpe_c.ccanot','cpe_c.ccabaj','cpe_n.respse','cpe_n.codhash')
               ->join('cpe_cabecera as cpe_c','cpe_n.IdCpe_cabecera','=','cpe_c.IdCpe_cabecera')
               ->join('tipo_documento as tip_d','cpe_n.tdocod','=','tip_d.tdocod')
               ->join('tipo_documento_identidad as tdi','cpe_c.tdicod','=','tdi.tdicod')
               ->join('moneda as mon','cpe_c.moncod','=','mon.moncod')
               ->where('cpe_c.IdEmpresa','=',session('rucemp'))
               ->where('ccanom','like','%'.$razsoc.'%')
               ->where('cpe_n.ccafem','>=',$fechaini)
               ->where('cpe_n.ccafem','<=',$fechafin)
               ->orwhere('cpe_c.IdEmpresa','=',session('rucemp'))
               ->where('ccandi','like','%'.$razsoc.'%')
               ->where('cpe_n.ccafem','>=',$fechaini)
               ->where('cpe_n.ccafem','<=',$fechafin)
               ->union($compcabecera)
                ->orderby('ccafem','desc')
               ->orderby('IdCpe_nota','desc')
               ->get();
              

           }elseif(!empty($comp)){
    

               $compcabecera = DB::tABLE('cpe_cabecera as cpe_c')->select('ccafem','tdodes','cpe_c.serdoc','cpe_c.numdoc','tdides','cpe_c.ccandi','cpe_c.ccanom','monnom','cpe_c.ccaitv','cpe_c.IdEmpresa','cpe_c.tdocod','cpe_c.IdCpe_cabecera','cpe_c.ccanot','cpe_c.ccabaj','cpe_c.respse','cpe_c.codhash')
               ->join('tipo_documento as tip_d','cpe_c.tdocod','=','tip_d.tdocod')
               ->join('tipo_documento_identidad as tdi','cpe_c.tdicod','=','tdi.tdicod')
               ->join('moneda as mon','cpe_c.moncod','=','mon.moncod')
               ->where('cpe_c.IdEmpresa','=',session('rucemp'))
               ->where('serdoc','=',$ser)
               ->where('numdoc','=',$num);

               $comprobantes = DB::tABLE('cpe_nota as cpe_n')
               ->select('cpe_n.ccafem','tdodes','cpe_n.serdoc','cpe_n.numdoc','tdides','cpe_c.ccandi','cpe_c.ccanom','monnom','cpe_n.ccaitv','cpe_c.IdEmpresa','cpe_n.tdocod','cpe_n.IdCpe_nota','cpe_c.ccanot','cpe_c.ccabaj','cpe_n.respse','cpe_n.codhash')
                ->join('cpe_cabecera as cpe_c','cpe_n.IdCpe_cabecera','=','cpe_c.IdCpe_cabecera')
                ->join('tipo_documento as tip_d','cpe_n.tdocod','=','tip_d.tdocod')
               ->join('tipo_documento_identidad as tdi','cpe_c.tdicod','=','tdi.tdicod')
               ->join('moneda as mon','cpe_c.moncod','=','mon.moncod')
               ->where('cpe_c.IdEmpresa','=',session('rucemp'))
               ->where('cpe_n.serdoc','=',$ser)
               ->where('cpe_n.numdoc','=',$num)
               ->union($compcabecera)
                ->orderby('ccafem','desc')
               ->orderby('IdCpe_nota','desc')
               ->get();
              


           }elseif($respse==0  && $tipdoc==0  && empty($serdoc) && empty($numdoc)){
           

              $compcabecera = DB::tABLE('cpe_cabecera as cpe_c')->select('ccafem','tdodes','cpe_c.serdoc','cpe_c.numdoc','tdides','cpe_c.ccandi','cpe_c.ccanom','monnom','cpe_c.ccaitv','cpe_c.IdEmpresa','cpe_c.tdocod','cpe_c.IdCpe_cabecera','cpe_c.ccanot','cpe_c.ccabaj','cpe_c.respse','cpe_c.codhash')
              ->join('tipo_documento as tip_d','cpe_c.tdocod','=','tip_d.tdocod')
              ->join('tipo_documento_identidad as tdi','cpe_c.tdicod','=','tdi.tdicod')
              ->join('moneda as mon','cpe_c.moncod','=','mon.moncod')
              ->where('cpe_c.IdEmpresa','=',session('rucemp'))
              ->where('ccanom','like','%'.$razsoc.'%')
              ->where('cpe_c.ccafem','>=',$fecin)
              ->where('cpe_c.ccafem','<=',$fecfin)
              ->orwhere('cpe_c.IdEmpresa','=',session('rucemp'))
              ->where('ccandi','like','%'.$razsoc.'%') 
              ->where('cpe_c.ccafem','>=',$fecin)
              ->where('cpe_c.ccafem','<=',$fecfin);

              $comprobantes = DB::tABLE('cpe_nota as cpe_n')
               ->select('cpe_n.ccafem','tdodes','cpe_n.serdoc','cpe_n.numdoc','tdides','cpe_c.ccandi','cpe_c.ccanom','monnom','cpe_n.ccaitv','cpe_c.IdEmpresa','cpe_n.tdocod','cpe_n.IdCpe_nota','cpe_c.ccanot','cpe_c.ccabaj','cpe_n.respse','cpe_n.codhash')
                ->join('cpe_cabecera as cpe_c','cpe_n.IdCpe_cabecera','=','cpe_c.IdCpe_cabecera')
                ->join('tipo_documento as tip_d','cpe_n.tdocod','=','tip_d.tdocod')
               ->join('tipo_documento_identidad as tdi','cpe_c.tdicod','=','tdi.tdicod')
               ->join('moneda as mon','cpe_c.moncod','=','mon.moncod')
               ->where('cpe_c.IdEmpresa','=',session('rucemp'))
              ->where('ccanom','like','%'.$razsoc.'%')
              ->where('cpe_n.ccafem','>=',$fecin)
              ->where('cpe_n.ccafem','<=',$fecfin)
              ->orwhere('cpe_c.IdEmpresa','=',session('rucemp'))
              ->where('ccandi','like','%'.$razsoc.'%') 
              ->where('cpe_n.ccafem','>=',$fecin)
              ->where('cpe_n.ccafem','<=',$fecfin)
              ->union($compcabecera)
               ->orderby('ccafem','desc')
               ->orderby('IdCpe_nota','desc')
              ->get();
              

           }elseif (empty($razsoc) && $respse==0  && empty($serdoc) && empty($numdoc)) {
         

              $compcabecera = DB::tABLE('cpe_cabecera as cpe_c')->select('ccafem','tdodes','cpe_c.serdoc','cpe_c.numdoc','tdides','cpe_c.ccandi','cpe_c.ccanom','monnom','cpe_c.ccaitv','cpe_c.IdEmpresa','cpe_c.tdocod','cpe_c.IdCpe_cabecera','cpe_c.ccanot','cpe_c.ccabaj','cpe_c.respse','cpe_c.codhash')
              ->join('tipo_documento as tip_d','cpe_c.tdocod','=','tip_d.tdocod')
              ->join('tipo_documento_identidad as tdi','cpe_c.tdicod','=','tdi.tdicod')
              ->join('moneda as mon','cpe_c.moncod','=','mon.moncod')
             ->where('cpe_c.IdEmpresa','=',session('rucemp'))
              ->where('cpe_c.tdocod','=',$tipdoc)
              ->where('cpe_c.ccafem','>=',$fecin)
              ->where('cpe_c.ccafem','<=',$fecfin);

              $comprobantes = DB::tABLE('cpe_nota as cpe_n')
              ->select('cpe_n.ccafem','tdodes','cpe_n.serdoc','cpe_n.numdoc','tdides','cpe_c.ccandi','cpe_c.ccanom','monnom','cpe_n.ccaitv','cpe_c.IdEmpresa','cpe_n.tdocod','cpe_n.IdCpe_nota','cpe_c.ccanot','cpe_c.ccabaj','cpe_n.respse','cpe_n.codhash')
              ->join('cpe_cabecera as cpe_c','cpe_n.IdCpe_cabecera','=','cpe_c.IdCpe_cabecera')
              ->join('tipo_documento as tip_d','cpe_n.tdocod','=','tip_d.tdocod')
              ->join('tipo_documento_identidad as tdi','cpe_c.tdicod','=','tdi.tdicod')
              ->join('moneda as mon','cpe_c.moncod','=','mon.moncod')
              ->where('cpe_c.IdEmpresa','=',session('rucemp'))
              ->where('cpe_n.tdocod','=',$tipdoc)
              ->where('cpe_n.ccafem','>=',$fecin)
              ->where('cpe_n.ccafem','<=',$fecfin)
              ->union($compcabecera)
               ->orderby('ccafem','desc')
               ->orderby('IdCpe_nota','desc')
              ->get();
              
           }elseif (empty($razsoc) && $tipdoc==0 && empty($serdoc) && empty($numdoc)) {
           
              if($respse=='01' || $respse=='02' || $respse=='03' || $respse=='04' || $respse=='05' || $respse=='06' || $respse=='10'){
              $compcabecera = DB::tABLE('cpe_cabecera as cpe_c')->select('ccafem','tdodes','cpe_c.serdoc','cpe_c.numdoc','tdides','cpe_c.ccandi','cpe_c.ccanom','monnom','cpe_c.ccaitv','cpe_c.IdEmpresa','cpe_c.tdocod','cpe_c.IdCpe_cabecera','cpe_c.ccanot','cpe_c.ccabaj','cpe_c.respse','cpe_c.codhash')
                ->join('tipo_documento as tip_d','cpe_c.tdocod','=','tip_d.tdocod')
                ->join('tipo_documento_identidad as tdi','cpe_c.tdicod','=','tdi.tdicod')
                ->join('moneda as mon','cpe_c.moncod','=','mon.moncod')
                ->where('cpe_c.IdEmpresa','=',session('rucemp'))
                ->where(DB::raw('substr(respse,1,2)'),'=',$respse)
                ->where('cpe_c.ccafem','>=',$fecin)
                ->where('cpe_c.ccafem','<=',$fecfin);

                 $comprobantes = DB::tABLE('cpe_nota as cpe_n')
                ->select('cpe_n.ccafem','tdodes','cpe_n.serdoc','cpe_n.numdoc','tdides','cpe_c.ccandi','cpe_c.ccanom','monnom','cpe_n.ccaitv','cpe_c.IdEmpresa','cpe_n.tdocod','cpe_n.IdCpe_nota','cpe_c.ccanot','cpe_c.ccabaj','cpe_n.respse','cpe_n.codhash')
                ->join('cpe_cabecera as cpe_c','cpe_n.IdCpe_cabecera','=','cpe_c.IdCpe_cabecera')
                ->join('tipo_documento as tip_d','cpe_n.tdocod','=','tip_d.tdocod')
                ->join('tipo_documento_identidad as tdi','cpe_c.tdicod','=','tdi.tdicod')
                ->join('moneda as mon','cpe_c.moncod','=','mon.moncod')
                ->where('cpe_c.IdEmpresa','=',session('rucemp'))
                ->where(DB::raw('substr(cpe_n.respse,1,2)'),'=',$respse)
                ->where('cpe_n.ccafem','>=',$fecin)
                ->where('cpe_n.ccafem','<=',$fecfin)
                ->union($compcabecera)
                 ->orderby('ccafem','desc')
               ->orderby('IdCpe_nota','desc')
                ->get();
              }else{
                $compcabecera = DB::tABLE('cpe_cabecera as cpe_c')->select('ccafem','tdodes','cpe_c.serdoc','cpe_c.numdoc','tdides','cpe_c.ccandi','cpe_c.ccanom','monnom','cpe_c.ccaitv','cpe_c.IdEmpresa','cpe_c.tdocod','cpe_c.IdCpe_cabecera','cpe_c.ccanot','cpe_c.ccabaj','cpe_c.respse','cpe_c.codhash')
                ->join('tipo_documento as tip_d','cpe_c.tdocod','=','tip_d.tdocod')
                ->join('tipo_documento_identidad as tdi','cpe_c.tdicod','=','tdi.tdicod')
                ->join('moneda as mon','cpe_c.moncod','=','mon.moncod')
                ->where('cpe_c.IdEmpresa','=',session('rucemp'))
                ->where(DB::raw('substr(respse,1,3)'),'>',100)
                ->where('cpe_c.ccafem','>=',$fecin)
                ->where('cpe_c.ccafem','<=',$fecfin);

                 $comprobantes = DB::tABLE('cpe_nota as cpe_n')
                ->select('cpe_n.ccafem','tdodes','cpe_n.serdoc','cpe_n.numdoc','tdides','cpe_c.ccandi','cpe_c.ccanom','monnom','cpe_n.ccaitv','cpe_c.IdEmpresa','cpe_n.tdocod','cpe_n.IdCpe_nota','cpe_c.ccanot','cpe_c.ccabaj','cpe_n.respse','cpe_n.codhash')
                ->join('cpe_cabecera as cpe_c','cpe_n.IdCpe_cabecera','=','cpe_c.IdCpe_cabecera')
                ->join('tipo_documento as tip_d','cpe_n.tdocod','=','tip_d.tdocod')
                ->join('tipo_documento_identidad as tdi','cpe_c.tdicod','=','tdi.tdicod')
                ->join('moneda as mon','cpe_c.moncod','=','mon.moncod')
                ->where('cpe_c.IdEmpresa','=',session('rucemp'))
                ->where(DB::raw('substr(cpe_n.respse,1,3)'),'>',100)
                ->where('cpe_n.ccafem','>=',$fecin)
                ->where('cpe_n.ccafem','<=',$fecfin)
                ->union($compcabecera)
                 ->orderby('ccafem','desc')
               ->orderby('IdCpe_nota','desc')
                ->get();
              }
             

             

           }elseif (empty($razsoc)) {
             if($respse=='01' || $respse=='02' || $respse=='03' || $respse=='04' || $respse=='05' || $respse=='06' || $respse=='10'){
              
                $compcabecera = DB::tABLE('cpe_cabecera as cpe_c')->select('ccafem','tdodes','cpe_c.serdoc','cpe_c.numdoc','tdides','cpe_c.ccandi','cpe_c.ccanom','monnom','cpe_c.ccaitv','cpe_c.IdEmpresa','cpe_c.tdocod','cpe_c.IdCpe_cabecera','cpe_c.ccanot','cpe_c.ccabaj','cpe_c.respse','cpe_c.codhash')
                ->join('tipo_documento as tip_d','cpe_c.tdocod','=','tip_d.tdocod')
                ->join('tipo_documento_identidad as tdi','cpe_c.tdicod','=','tdi.tdicod')
                ->join('moneda as mon','cpe_c.moncod','=','mon.moncod')
                ->where('cpe_c.IdEmpresa','=',session('rucemp'))
                ->where(DB::raw('substr(respse,1,2)'),'=',$respse)
                ->where('cpe_c.tdocod','=',$tipdoc)
                ->where('cpe_c.ccafem','>=',$fecin)
                ->where('cpe_c.ccafem','<=',$fecfin);

                 $comprobantes = DB::tABLE('cpe_nota as cpe_n')
                ->select('cpe_n.ccafem','tdodes','cpe_n.serdoc','cpe_n.numdoc','tdides','cpe_c.ccandi','cpe_c.ccanom','monnom','cpe_n.ccaitv','cpe_c.IdEmpresa','cpe_n.tdocod','cpe_n.IdCpe_nota','cpe_c.ccanot','cpe_c.ccabaj','cpe_n.respse','cpe_n.codhash')
                ->join('cpe_cabecera as cpe_c','cpe_n.IdCpe_cabecera','=','cpe_c.IdCpe_cabecera')
                ->join('tipo_documento as tip_d','cpe_n.tdocod','=','tip_d.tdocod')
                ->join('tipo_documento_identidad as tdi','cpe_c.tdicod','=','tdi.tdicod')
                ->join('moneda as mon','cpe_c.moncod','=','mon.moncod')
                ->where('cpe_c.IdEmpresa','=',session('rucemp'))
                ->where(DB::raw('substr(cpe_n.respse,1,2)'),'=',$respse)
                ->where('cpe_n.tdocod','=',$tipdoc)
                ->where('cpe_n.ccafem','>=',$fecin)
                ->where('cpe_n.ccafem','<=',$fecfin)
                ->union($compcabecera)
                 ->orderby('ccafem','desc')
               ->orderby('IdCpe_nota','desc')
                ->get();

              }else{
                $compcabecera = DB::tABLE('cpe_cabecera as cpe_c')->select('ccafem','tdodes','cpe_c.serdoc','cpe_c.numdoc','tdides','cpe_c.ccandi','cpe_c.ccanom','monnom','cpe_c.ccaitv','cpe_c.IdEmpresa','cpe_c.tdocod','cpe_c.IdCpe_cabecera','cpe_c.ccanot','cpe_c.ccabaj','cpe_c.respse','cpe_c.codhash')
                ->join('tipo_documento as tip_d','cpe_c.tdocod','=','tip_d.tdocod')
                ->join('tipo_documento_identidad as tdi','cpe_c.tdicod','=','tdi.tdicod')
                ->join('moneda as mon','cpe_c.moncod','=','mon.moncod')
                ->where('cpe_c.IdEmpresa','=',session('rucemp'))
                ->where(DB::raw('substr(respse,1,3)'),'>',100)
                ->where('cpe_c.tdocod','=',$tipdoc)
                ->where('cpe_c.ccafem','>=',$fecin)
                ->where('cpe_c.ccafem','<=',$fecfin);

                 $comprobantes = DB::tABLE('cpe_nota as cpe_n')
                ->select('cpe_n.ccafem','tdodes','cpe_n.serdoc','cpe_n.numdoc','tdides','cpe_c.ccandi','cpe_c.ccanom','monnom','cpe_n.ccaitv','cpe_c.IdEmpresa','cpe_n.tdocod','cpe_n.IdCpe_nota','cpe_c.ccanot','cpe_c.ccabaj','cpe_n.respse','cpe_n.codhash')
                ->join('cpe_cabecera as cpe_c','cpe_n.IdCpe_cabecera','=','cpe_c.IdCpe_cabecera')
                ->join('tipo_documento as tip_d','cpe_n.tdocod','=','tip_d.tdocod')
                ->join('tipo_documento_identidad as tdi','cpe_c.tdicod','=','tdi.tdicod')
                ->join('moneda as mon','cpe_c.moncod','=','mon.moncod')
                ->where('cpe_c.IdEmpresa','=',session('rucemp'))
                ->where(DB::raw('substr(cpe_n.respse,1,3)'),'>',100)
                ->where('cpe_n.tdocod','=',$tipdoc)
                ->where('cpe_n.ccafem','>=',$fecin)
                ->where('cpe_n.ccafem','<=',$fecfin)
                ->union($compcabecera)
                 ->orderby('ccafem','desc')
               ->orderby('IdCpe_nota','desc')
                ->get();

              }

             

           }elseif ($tipdoc==0) {
           
              if($respse=='01' || $respse=='02' || $respse=='03' || $respse=='04' || $respse=='05' || $respse=='06' || $respse=='10'){
                $compcabecera = DB::tABLE('cpe_cabecera as cpe_c')->select('ccafem','tdodes','cpe_c.serdoc','cpe_c.numdoc','tdides','cpe_c.ccandi','cpe_c.ccanom','monnom','cpe_c.ccaitv','cpe_c.IdEmpresa','cpe_c.tdocod','cpe_c.IdCpe_cabecera','cpe_c.ccanot','cpe_c.ccabaj','cpe_c.respse','cpe_c.codhash')
              ->join('tipo_documento as tip_d','cpe_c.tdocod','=','tip_d.tdocod')
              ->join('tipo_documento_identidad as tdi','cpe_c.tdicod','=','tdi.tdicod')
              ->join('moneda as mon','cpe_c.moncod','=','mon.moncod')
              ->where('cpe_c.IdEmpresa','=',session('rucemp'))
              ->where(DB::raw('substr(respse,1,2)'),'=',$respse)
              ->where('ccanom','like','%'.$razsoc.'%')
              ->where('cpe_c.ccafem','>',$fecin)
              ->where('cpe_c.ccafem','<',$fecfin)
              ->orwhere('cpe_c.IdEmpresa','=',session('rucemp'))
              ->where(DB::raw('substr(respse,1,2)'),'=',$respse)
              ->where('ccandi','like','%'.$razsoc.'%') 
              ->where('cpe_c.ccafem','>=',$fecin)
              ->where('cpe_c.ccafem','<=',$fecfin);

              $comprobantes = DB::tABLE('cpe_nota as cpe_n')
              ->select('cpe_n.ccafem','tdodes','cpe_n.serdoc','cpe_n.numdoc','tdides','cpe_c.ccandi','cpe_c.ccanom','monnom','cpe_n.ccaitv','cpe_c.IdEmpresa','cpe_n.tdocod','cpe_n.IdCpe_nota','cpe_c.ccanot','cpe_c.ccabaj','cpe_n.respse','cpe_n.codhash')
              ->join('cpe_cabecera as cpe_c','cpe_n.IdCpe_cabecera','=','cpe_c.IdCpe_cabecera')
              ->join('tipo_documento as tip_d','cpe_n.tdocod','=','tip_d.tdocod')
              ->join('tipo_documento_identidad as tdi','cpe_c.tdicod','=','tdi.tdicod')
              ->join('moneda as mon','cpe_c.moncod','=','mon.moncod')
              ->where('cpe_c.IdEmpresa','=',session('rucemp'))
              ->where(DB::raw('substr(respse,1,2)'),'=',$respse)
              ->where('ccanom','like','%'.$razsoc.'%')
              ->where('cpe_n.ccafem','>',$fecin)
              ->where('cpe_n.ccafem','<',$fecfin)
              ->orwhere('cpe_c.IdEmpresa','=',session('rucemp'))
              ->where(DB::raw('substr(cpe_n.respse,1,2)'),'=',$respse)
              ->where('ccandi','like','%'.$razsoc.'%') 
              ->where('cpe_n.ccafem','>=',$fecin)
              ->where('cpe_n.ccafem','<=',$fecfin)
              ->union($compcabecera)
               ->orderby('ccafem','desc')
               ->orderby('IdCpe_nota','desc')
              ->get();
            }else{
               $compcabecera = DB::tABLE('cpe_cabecera as cpe_c')->select('ccafem','tdodes','cpe_c.serdoc','cpe_c.numdoc','tdides','cpe_c.ccandi','cpe_c.ccanom','monnom','cpe_c.ccaitv','cpe_c.IdEmpresa','cpe_c.tdocod','cpe_c.IdCpe_cabecera','cpe_c.ccanot','cpe_c.ccabaj','cpe_c.respse','cpe_c.codhash')
              ->join('tipo_documento as tip_d','cpe_c.tdocod','=','tip_d.tdocod')
              ->join('tipo_documento_identidad as tdi','cpe_c.tdicod','=','tdi.tdicod')
              ->join('moneda as mon','cpe_c.moncod','=','mon.moncod')
              ->where('cpe_c.IdEmpresa','=',session('rucemp'))
              ->where(DB::raw('substr(respse,1,2)'),'>',100)
              ->where('ccanom','like','%'.$razsoc.'%')
              ->where('cpe_c.ccafem','>',$fecin)
              ->where('cpe_c.ccafem','<',$fecfin)
              ->orwhere('cpe_c.IdEmpresa','=',session('rucemp'))
              ->where(DB::raw('substr(respse,1,3)'),'>',100)
              ->where('ccandi','like','%'.$razsoc.'%') 
              ->where('cpe_c.ccafem','>=',$fecin)
              ->where('cpe_c.ccafem','<=',$fecfin);

              $comprobantes = DB::tABLE('cpe_nota as cpe_n')
              ->select('cpe_n.ccafem','tdodes','cpe_n.serdoc','cpe_n.numdoc','tdides','cpe_c.ccandi','cpe_c.ccanom','monnom','cpe_n.ccaitv','cpe_c.IdEmpresa','cpe_n.tdocod','cpe_n.IdCpe_nota','cpe_c.ccanot','cpe_c.ccabaj','cpe_n.respse','cpe_n.codhash')
              ->join('cpe_cabecera as cpe_c','cpe_n.IdCpe_cabecera','=','cpe_c.IdCpe_cabecera')
              ->join('tipo_documento as tip_d','cpe_n.tdocod','=','tip_d.tdocod')
              ->join('tipo_documento_identidad as tdi','cpe_c.tdicod','=','tdi.tdicod')
              ->join('moneda as mon','cpe_c.moncod','=','mon.moncod')
              ->where('cpe_c.IdEmpresa','=',session('rucemp'))
              ->where(DB::raw('substr(respse,1,3)'),'>',100)
              ->where('ccanom','like','%'.$razsoc.'%')
              ->where('cpe_n.ccafem','>',$fecin)
              ->where('cpe_n.ccafem','<',$fecfin)
              ->orwhere('cpe_c.IdEmpresa','=',session('rucemp'))
              ->where(DB::raw('substr(cpe_n.respse,1,3)'),'>',100)
              ->where('ccandi','like','%'.$razsoc.'%') 
              ->where('cpe_n.ccafem','>=',$fecin)
              ->where('cpe_n.ccafem','<=',$fecfin)
              ->union($compcabecera)
               ->orderby('ccafem','desc')
               ->orderby('IdCpe_nota','desc')
              ->get();
            }

           }elseif ($respse==0) {
         
              $compcabecera = DB::tABLE('cpe_cabecera as cpe_c')->select('ccafem','tdodes','cpe_c.serdoc','cpe_c.numdoc','tdides','cpe_c.ccandi','cpe_c.ccanom','monnom','cpe_c.ccaitv','cpe_c.IdEmpresa','cpe_c.tdocod','cpe_c.IdCpe_cabecera','cpe_c.ccanot','cpe_c.ccabaj','cpe_c.respse','cpe_c.codhash')
              ->join('tipo_documento as tip_d','cpe_c.tdocod','=','tip_d.tdocod')
              ->join('tipo_documento_identidad as tdi','cpe_c.tdicod','=','tdi.tdicod')
              ->join('moneda as mon','cpe_c.moncod','=','mon.moncod')
              ->where('cpe_c.IdEmpresa','=',session('rucemp'))
              ->where('cpe_c.tdocod','=',$tipdoc)
              ->where('ccanom','like','%'.$razsoc.'%')
              ->where('cpe_c.ccafem','>',$fecin)
              ->where('cpe_c.ccafem','<',$fecfin)
              ->orwhere('cpe_c.IdEmpresa','=',session('rucemp'))
              ->where('cpe_c.tdocod','=',$tipdoc)
              ->where('ccandi','like','%'.$razsoc.'%') 
              ->where('cpe_c.ccafem','>=',$fecin)
              ->where('cpe_c.ccafem','<=',$fecfin);

              $comprobantes = DB::tABLE('cpe_nota as cpe_n')
              ->select('cpe_n.ccafem','tdodes','cpe_n.serdoc','cpe_n.numdoc','tdides','cpe_c.ccandi','cpe_c.ccanom','monnom','cpe_n.ccaitv','cpe_c.IdEmpresa','cpe_n.tdocod','cpe_n.IdCpe_nota','cpe_c.ccanot','cpe_c.ccabaj','cpe_n.respse','cpe_n.codhash')
              ->join('cpe_cabecera as cpe_c','cpe_n.IdCpe_cabecera','=','cpe_c.IdCpe_cabecera')
              ->join('tipo_documento as tip_d','cpe_n.tdocod','=','tip_d.tdocod')
              ->join('tipo_documento_identidad as tdi','cpe_c.tdicod','=','tdi.tdicod')
              ->join('moneda as mon','cpe_c.moncod','=','mon.moncod')
              ->where('cpe_c.IdEmpresa','=',session('rucemp'))
              ->where('cpe_n.tdocod','=',$tipdoc)
              ->where('ccanom','like','%'.$razsoc.'%')
              ->where('cpe_n.ccafem','>',$fecin)
              ->where('cpe_n.ccafem','<',$fecfin)
              ->orwhere('cpe_c.IdEmpresa','=',session('rucemp'))
              ->where('cpe_n.tdocod','=',$tipdoc)
              ->where('ccandi','like','%'.$razsoc.'%') 
              ->where('cpe_n.ccafem','>=',$fecin)
              ->where('cpe_n.ccafem','<=',$fecfin)
              ->union($compcabecera)
              ->orderby('ccafem','desc')
              ->orderby('IdCpe_nota','desc')
              ->get();

            }
        
            session(['nomLogo' => $empresa->LogEmpresa]);
            session(['NomEmpresa' => $empresa->NomEmpresa]);
              
          
            return view('administrador.panelclientes.comprobantes.index',['comprobantes'=>$comprobantes,'empresa'=>$empresa,'doccomprobante'=>$doccomprobante]);

        
         
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($tdocod)
    {

        // consultar tipos de  IGV
        $ncdcod= $tdocod;
        $igv = DB::tABLE('tipo_igv')->where('tigest','=','Activo')
        ->orderBy('tigcod','asc')->get();

        // consultar unidades de medida
        $unidades = DB::tABLE('unidad_medida')->where('umeest','=','Activo')
        ->orderBy('umecod','asc')->get();

        // consultar tipo de operaciones
        $operaciones = DB::tABLE('tipo_operacion')->where('topest','=','Activo')
        ->orderBy('topcod','asc')->get();

        // consultar tipos de documentos de identidad
        $docidentidad = DB::tABLE('tipo_documento_identidad')->where('tdiest','=','Activo')->orderBy('tdicod','asc')->get();

        //consultar tipo de documento 
        $doccomprobante = DB::tABLE('tipo_documento')->where('tdoest','=','Activo')->orderBy('tdocod','asc')->get();

        // obtener el ruc de la empresa en la cual se logueo
        $rucemp = trim($rucemp);

        // consultar los clientes que le pertenece a la empresa
        $clientes= DB::tABLE('cliente')->where('rucemp','=',$rucemp)->where('cliest','=','Activo')
        ->orderby('clinom','asc')->get();

        //consultar productos que le pertenece a la empresa
        $productos= DB::tABLE('productos')->where('IdEmpresa','=',$rucemp)->where('proest','=','Activo')
        ->orderby('pronom','asc')->get();

        // consultar tipos de monedas
        $monedas = DB::tABLE('moneda')->where('monest','=','Activo')->orderby('moncod','asc')->get();

        // consultar la serie y numero de factura

        if($tdocod == '01'){
          $senudoc = DB::tABLE('empresa')->select('FseEmpresa','FnuEmpresa')->where('IdEmpresa','=',$rucemp)->first(); 
        }elseif ($tdocod =='03') {
         $senudoc = DB::tABLE('empresa')->select('BseEmpresa','BnuEmpresa')->where('IdEmpresa','=',$rucemp)->first(); 
        }elseif ($tdocod =='07') {
         $nota = DB::tABLE('tipo_nota_credito')->where('ncest','=','Activo')
        ->orderBy('nccod','asc')->get();
        }elseif ($tdocod =='08') {
         $nota = DB::tABLE('tipo_nota_debito')->where('ndest','=','Activo')
        ->orderBy('ndcod','asc')->get(); 
        }
        

        $fecha = now()->format('m/d/Y');
        //return $senudoc;
        if($tdocod=='01'){
            return view('empresas.comprobantes.nuevafactura',['igv'=>$igv,'monedas'=>$monedas,'unidades'=>$unidades,'operaciones'=>$operaciones,'docidentidad'=>$docidentidad,'clientes'=>$clientes,'fecha'=>$fecha,'senudoc'=>$senudoc,'tdocod'=>$tdocod,'productos'=>$productos,'doccomprobante'=>$doccomprobante]);
        }elseif($tdocod=='03'){
             return view('empresas.comprobantes.nuevaboleta',['igv'=>$igv,'monedas'=>$monedas,'unidades'=>$unidades,'operaciones'=>$operaciones,'docidentidad'=>$docidentidad,'clientes'=>$clientes,'fecha'=>$fecha,'senudoc'=>$senudoc,'tdocod'=>$tdocod,'productos'=>$productos,'doccomprobante'=>$doccomprobante]);
          }elseif($tdocod=='07'){
             return view('empresas.comprobantes.nuevanotacredito',['igv'=>$igv,'monedas'=>$monedas,'unidades'=>$unidades,'operaciones'=>$operaciones,'docidentidad'=>$docidentidad,'clientes'=>$clientes,'fecha'=>$fecha,'tdocod'=>$tdocod,'productos'=>$productos,'doccomprobante'=>$doccomprobante,'nota'=>$nota,'ncdcod'=> $ncdcod]);
           }elseif($tdocod=='08'){
             return view('empresas.comprobantes.nuevanotadebito',['igv'=>$igv,'monedas'=>$monedas,'unidades'=>$unidades,'operaciones'=>$operaciones,'docidentidad'=>$docidentidad,'clientes'=>$clientes,'fecha'=>$fecha,'tdocod'=>$tdocod,'productos'=>$productos,'doccomprobante'=>$doccomprobante,'nota'=>$nota,'ncdcod'=> $ncdcod]);
          }
      
       
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

       //Registrar si el cliente ingresado en la factura no existe
   
        $tdicod = $request->get('tdicod');
        $cliruc = $request->get('clinum');
        $clinom = $request->get('clinom');
        $fecemi = $request->get('fecEmi');
        $clidir = $request->get('clidir');
        $clicor = $request->get('clicor');
        $mondoc = $request->get('mondoc');
        if($mondoc !='PEN'){
          $camdoc = $request->get('camdoc');
          $tipcam = tipocambio::FirstOrCreate(['FecTipCambio'=>$fecemi],['CamCompra'=>$camdoc]);
        }else{
          $camdoc=0;
        }
        $rucemp = trim($rucemp);
        $cliente = Cliente::FirstOrCreate(['clinum'=>$cliruc],['clinom'=>$clinom,'clidir'=>$clidir,'clicor'=>$clicor,'rucemp'=>$rucemp,'tdicod'=>$tdicod]);
        
      //Registrar la cabecera de la factura
        $tdocod = $request->get('txt_tdocod');
        $cabecera = new cpe_cabecera;
        $cabecera->tdocod = $request->get('tdocod');
        $cabecera->topcod = $request->get('topcod');
        $cabecera->ccafem = $request->get('fecEmi');
        $cabecera->ccafve = $request->get('fecVen');
        $cabecera->ccaobs = $request->get('obser');

       // $cabecera->ccacde = $request->get();
        $cabecera->tdicod = $request->get('tdicod');
        $cabecera->ccandi = $request->get('clinum');
        $cabecera->ccanom = $request->get('clinom');
        $cabecera->moncod = $request->get('mondoc');
        $cabecera->tipcambio = $camdoc;
        $cabecera->ccacar = $request->get('otrosc');
        $cabecera->ccatde = $request->get('desc');
        $cabecera->ccatvg = $request->get('grav');
        $cabecera->ccatvgr = $request->get('grat');
        $cabecera->ccatvi = $request->get('inaf');
        $cabecera->ccatve = $request->get('exon');
        $cabecera->ccaigv = $request->get('igv');
        $cabecera->ccaisc = $request->get('isc');
        $cabecera->ccaotr = $request->get('otros');
        $cabecera->ccaitv = $request->get('total');
        $cabecera->serdoc= $request->get('serdoc');
        

        $numdoc = str_pad($request->get('numdoc'),8,"0", STR_PAD_LEFT);
        $cabecera->numdoc = $numdoc;

        $cabecera->codunique = $rucemp.''.$request->get('tdocod').''.$request->get('serdoc').''.$request->get('numdoc');
        $cabecera->IdUsuario = Auth::user()->IdUsuario;
        $cabecera->IdEmpresa = $rucemp;
        $cabecera->save();

         //Actualizar la serie y/o el nÃºmero del documento a emitir
        if($tdocod=='01'){
          $empresa = Empresa::findOrFail($rucemp);
          $empresa->FseEmpresa = $request->get('serdoc');
          $empresa->FnuEmpresa = $request->get('numdoc');
          $empresa->update();
        }elseif($tdocod=='03'){
          $empresa = Empresa::findOrFail($rucemp);
          $empresa->BseEmpresa = $request->get('serdoc');
          $empresa->BnuEmpresa = $request->get('numdoc');
          $empresa->update();
        }
         


        $cab = $cabecera->tdocod.'|'.$cabecera->ccafem.'|'.'0'.'|'.$cabecera->tdicod.'|'.$cabecera->ccandi.'|'.$cabecera->ccanom.'|'.$cabecera->moncod.'|'.$cabecera->ccatde.'|'.$cabecera->ccacar.'|'.$cabecera->ccatde.'|'.$cabecera->ccatvg.'|'.$cabecera->ccatvi.'|'. $cabecera->ccatve.'|'.$cabecera->ccaigv.'|0.00|'.$cabecera->ccaotr.'|'.$cabecera->ccaitv;
        
        //Ruta donde se guardarán los archivos cab y det.
        $raiz = '/opt/fs/'.$cabecera->IdEmpresa.'/sunat_archivos/sfs/DATA/';
        

       //Registrar el detalle de la factura
        $cantidades = $request->get('cant');
        $unidades = $request->get('unid');
        $codpro = $request->get('codpro');
        $detpro = $request->get('detpro');
        $vunit = $request->get('vunit');
        $vigv = $request->get('vigv');
        $tigv = $request->get('tigv');
        $vsub = $request->get('vsub');
        $vtot = $request->get('vtot');

        
        foreach( $unidades as $index => $ume ) {
            $detalle = new cpe_detalle;
            $detalle->IdCpe_cabecera =  $cabecera->IdCpe_cabecera; 
            $detalle->umecod = $ume;
            $detalle->cdecan = $cantidades[$index];
            $pos = strpos($codpro[$index],'|');
            $codproducto = substr($codpro[$index], 0, $pos);
            $detalle->procod = $codproducto;
            $detalle->cdepsu = $codproducto;
            $detalle->cdedes = $detpro[$index];
            $detalle->cdevun = $vunit[$index];
            $detalle->cdeigv = $vigv[$index];
            $detalle->tigcod = $tigv[$index];
            $detalle->cdepve = $vsub[$index];
            $detalle->cdevve = $vtot[$index];
            $detalle->save();

            //Guardar en una variable los items en el archivo .det
            $codumecin = unidad_medida::findOrFail($ume);
            $det = $codumecin->umecin.'|'.$detalle->cdecan.'|'.$detalle->procod.'|'.$detalle->cdepsu.'|'.$detalle->cdedes.'|'.$detalle->cdevun.'|0.00|'.$detalle->cdeigv = $vigv[$index].'|'.$detalle->tigcod.'|'.'0.00'.'|'.'01'.'|'.$detalle->cdepve.'|'.$detalle->cdevve."\n";
            $detfile = $cabecera->IdEmpresa.'-'.$cabecera->tdocod.'-'.$cabecera->serdoc.'-'.$numdoc.'.DET'; 
        
            //Crear e insertar los items al archivo det
            //$archdet = fopen($raiz.$detfile, "a");
            $archdet = fopen($detfile, "a");
            fputs($archdet,$det);
            fclose($archdet);
        }

            //registrar documentos relacionados
        $docrel = $request->get('tdr');
        $docrser = $request->get('tdrser');
        $docrnum = $request->get('tdrnum');

        if(!empty($docrel) && !empty($docrser) && !empty($docrnum)){
          foreach( $docrel as $index => $tdr ) 
          {
            if(!empty($docrel[$index]) && !empty($docrser[$index]) && !empty($docrnum[$index])){
               $docrenum = str_pad($docrnum[$index],8,"0", STR_PAD_LEFT);
               
               $guia = DB::table("documento_relacionado")->where('tdocod','=',$docrel[$index])->where('dorser','=',$docrser[$index])->where('dornum','=',$docrenum)->first();

               if($guia==''){
                   $docrelacionado = new documento_relacionado;
                   $docrelacionado->IdCpe_cabecera = $cabecera->IdCpe_cabecera;
                   $docrelacionado->tdocod= $docrel[$index];
                   $docrelacionado->dorser = $docrser[$index];
                   $docrelacionado->dornum = $docrenum;
                   $docrelacionado->save();

                   if($docrelacionado->tdocod=='09' || $docrelacionado->tdocod=='31' ){
                        $rel = '1'.'|'.$docrelacionado->tdocod.'|'.$docrelacionado->dorser.'-'.$docrelacionado->dornum.'|'.$cabecera->tdocod.'|'.$cabecera->numdoc.'|'.$cabecera->ccaitv."\n";
                   }

                  $relfile = $cabecera->IdEmpresa.'-'.$cabecera->tdocod.'-'.$cabecera->serdoc.'-'.$numdoc.'.REL';
                  //$archrel = fopen($raiz.$relfile, "a");
                  $archrel = fopen($relfile, "a");
                  fputs($archrel,$rel);
                  fclose($archrel);
               } 
        
            }
           
          }

        }

        //Guardar en una variable el nombre del archivo cab
        $cabfile =  $cabecera->IdEmpresa.'-'.$cabecera->tdocod.'-'.$cabecera->serdoc.'-'.$numdoc.'.CAB'; 
  
        //Segun el tipo de documento se crearÃ¡ el contenido del archivo cab
            $cab = $cabecera->tdocod.'|'.$cabecera->ccafem.'|'.'0'.'|'.$cabecera->tdicod.'|'.$cabecera->ccandi.'|'.$cabecera->ccanom.'|'.$cabecera->moncod.'|'.$cabecera->ccatde.'|'.$cabecera->ccacar.'|'.$cabecera->ccatde.'|'.$cabecera->ccatvg.'|'.$cabecera->ccatvi.'|'. $cabecera->ccatve.'|'.$cabecera->ccaigv.'|0.00|'.$cabecera->ccaotr.'|'.$cabecera->ccaitv;

        //Crear el archivo cab e insertar el contenido
        //$archivo = fopen($raiz.$cabfile, "a");
        $archivo = fopen($cabfile, "a");
        fputs($archivo,$cab);
        fclose($archivo);
        

       if(!empty($clicor)){
          $correo = '01|'.$clicor;
          $corfile = $cabecera->IdEmpresa.'-'.$cabecera->tdocod.'-'.$cabecera->serdoc.'-'.$numdoc.'.COR'; 
          //$archcor = fopen($raiz.$corfile, "a");
          $archcor = fopen($corfile, "a");
          fputs($archcor,$correo);
          fclose($archcor); 
        }

        
        $webservice = self::webservice($cabfile,$empresa->puerto,$cabecera->IdCpe_cabecera,$tdocod,session('rucemp'));
      
        $codfact = $cabecera->IdCpe_cabecera; 

        $cabpdf= DB::tABLE('cpe_cabecera as c')->join('moneda as m','c.moncod','=','m.moncod')->join('empresa as e','c.IdEmpresa','=','e.IdEmpresa')->where('IdCpe_cabecera','=',$codfact)->where('c.IdEmpresa','=',$rucemp)->first();
         
       if($cabpdf->codhash!='' || $cabpdf->respse=='05'){

          $cliente= DB::tABLE('cliente as cli')->join('cpe_cabecera as c','c.ccandi','=','cli.clinum')->where('IdCpe_cabecera','=',$codfact)->where('cli.rucemp','=',$rucemp)->where('cli.clinum','=',$cliruc)->first();

          $detpdf= DB::tABLE('cpe_detalle as d')->join('cpe_cabecera as c','d.IdCpe_cabecera','=','c.IdCpe_cabecera')->where('c.IdCpe_cabecera','=',$codfact)->get();
          

          $nompdffile =  $cabpdf->IdEmpresa.'-'.$cabpdf->tdocod.'-'.$cabpdf->serdoc.'-'.$numdoc.'.pdf'; 
          $rutpdfile = '/opt/fs/'.$rucemp.'/sunat_archivos/sfs/REPO/ ';

          if($tdocod=='01'){
           $view = \View::make('empresas.comprobantes.'.$rucemp.'.facturapdf', compact('cabpdf','detpdf','cliente'));
          }elseif($tdocod=='03'){
            $view = \View::make('empresas.comprobantes.'.$rucemp.'.boletapdf', compact('cabpdf','detpdf','cliente')); 
          }

          $pdf = \App::make('dompdf.wrapper');
          $contenido = $view->render();
          //$pdf->loadHTML($view)->setPaper('A4', 'lanscape')->save($rutpdfile.$nompdffile);
          $pdf->loadHTML($view)->setPaper('A4', 'lanscape')->save($nompdffile);
       }
    
      // llamar al webservice build and send
    
     
     return $webservice;
        
    }

    /**
   );  * Display the specified resource.
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



    public function facturapdf($codfact,$doccod,$idcabecera)
    {

      $rucemp = trim($rucemp);
      $rutpdfile = '/opt/fs/'.$rucemp.'/sunat_archivos/sfs/REPO/ ';
     // $file= $rutpdfile.$codfact.'.pdf';
      $file= $codfact.'.pdf';
      //$file= public_path().'/'.$codfact.'.pdf';

      if (file_exists($file))
      {
        $headers = array(
              'Content-Type: application/pdf',
            );

        return response()->download($file);
      }

      if($doccod=='07' || $doccod=='08'){
        return Redirect::to('/listarnotas/'.$idcabecera);
      }elseif($doccod=='0'){
        return Redirect::to('/listarbajas/'.$idcabecera);
      }else{
         return Redirect::to('/MasterSoft');
      }
    }


    public function buscarcomprobante(Request $request){
      $search = $request->term;

      $ser = substr($search,strpos($search,'-')-4,4);
      $num = substr($search,strpos($search,'-')+1,8);

      $rucemp = trim($rucemp);
      $comprobante = DB::tABLE('cpe_cabecera as cab')
      ->join('cliente as cli','cab.ccandi','=','cli.clinum')
      ->join('tipo_documento as tdo','cab.tdocod','=','tdo.tdocod')
      ->join('tipo_documento_identidad as tdi','cab.tdicod','=','tdi.tdicod')
      ->join('moneda as mon','cab.moncod','=','mon.moncod')
      ->join('tipo_operacion as top','cab.topcod','=','top.topcod')
      ->where('serdoc','=',$ser)
      ->where('numdoc','=',$num)
      ->where('IdEmpresa','=',$rucemp)
      ->where(DB::raw('substr(respse,1,2)'),'=','03')
      ->orwhere(DB::raw('substr(respse,1,2)'),'=','04')
      ->take(10)->get();

      $results = array();

      foreach($comprobante as $c => $comp){
        $sernum=$comp->serdoc.'-'.$comp->numdoc;
        $results[] = ['value'=>$sernum,'serdoc'=>$comp->serdoc,'numdoc'=>$comp->numdoc,'clinum'=>$comp->ccandi,'clinom'=>$comp->ccanom,'clidir'=>$comp->clidir,'clicor'=>$comp->clicor,'tdomod'=>$comp->tdodes,'tdides'=>$comp->tdides,'monnom'=>$comp->monnom,'tipcambio'=>$comp->tipcambio,'topdes'=>$comp->topdes,'tdicod'=>$comp->tdicod,'tdocod'=>$comp->tdocod,'moncod'=>$comp->moncod,'fecemi'=>$comp->ccafem,'idcabecera'=>$comp->IdCpe_cabecera];
      }
      return response()->json($results);
    }


     public function buscarcomprobantelista(Request $request){
      $search = $request->term;

      $ser = substr($search,strpos($search,'-')-4,4);
      $num = substr($search,strpos($search,'-')+1,8);

      $rucemp = trim($rucemp);

      $compcabecera = DB::tABLE('cpe_cabecera as cpe_c')->select('serdoc','numdoc')
      ->where('serdoc','=',$ser)
      ->where('numdoc','=',$num)
      ->where('IdEmpresa','=',$rucemp);

      $comprobante = DB::tABLE('cpe_nota as cpe_n')->select('cpe_n.serdoc','cpe_n.numdoc')
       ->join('cpe_cabecera as cpe_c','cpe_n.IdCpe_cabecera','=','cpe_c.IdCpe_cabecera')
      ->where('cpe_n.serdoc','=',$ser)
      ->where('cpe_n.numdoc','=',$num)
      ->where('IdEmpresa','=',$rucemp)
      ->union($compcabecera)
      ->get();

      $results = array();

      foreach($comprobante as $c => $comp){
        $sernum=$comp->serdoc.'-'.$comp->numdoc;
        $results[] = ['value'=>$sernum];
      }
      return response()->json($results);
    }


     public function buscarcomprobantebaja(Request $request){
      $search = $request->term;

      $ser = substr($search,strpos($search,'-')-4,4);
      $num = substr($search,strpos($search,'-')+1,8);

      $rucemp = trim($rucemp);
      $comprobante = DB::tABLE('cpe_cabecera as cab')
      ->join('cliente as cli','cab.ccandi','=','cli.clinum')
      ->join('tipo_documento as tdo','cab.tdocod','=','tdo.tdocod')
      ->join('tipo_documento_identidad as tdi','cab.tdicod','=','tdi.tdicod')
      ->join('moneda as mon','cab.moncod','=','mon.moncod')
      ->join('tipo_operacion as top','cab.topcod','=','top.topcod')
      ->where('serdoc','=',$ser)
      ->where('numdoc','=',$num)
      ->where('IdEmpresa','=',$rucemp)
      ->where(DB::raw('substr(respse,1,2)'),'=','03' )
      ->where('ccabaj','=','')
      ->orwhere('serdoc','=',$ser)
      ->where('numdoc','=',$num)
      ->where('IdEmpresa','=',$rucemp)
      ->where(DB::raw('substr(respse,1,2)'),'=','04')
      ->where('ccabaj','=','')
      ->take(1)->get();

      $results = array();

      foreach($comprobante as $c => $comp){
        $sernum=$comp->serdoc.'-'.$comp->numdoc;
        $results[] = ['value'=>$sernum,'serdoc'=>$comp->serdoc,'numdoc'=>$comp->numdoc,'tdomod'=>$comp->tdodes,'tdocod'=>$comp->tdocod,'fecemi'=>$comp->ccafem,'idcabecera'=>$comp->IdCpe_cabecera,'monnom'=>$comp->monnom,'ccaitv'=>$comp->ccaitv];
      }
      return response()->json($results);
    }


    public function autocomplete(Request $request){
      $search = $request->term;
      $rucemp = trim($rucemp);
      $ruc = Cliente::where('clinum','like','%'.$search.'%')->where('cliest','=','Activo')->where('rucemp','=',$rucemp)->orwhere('clinom','like','%'.$search.'%')->where('rucemp','=',$rucemp)->where('cliest','=','Activo')->take(10)->get();
      $results = array();

      foreach($ruc as $cli => $cliente){
        $numnom=$cliente->clinum.'|'.$cliente->clinom;
        $results[] = ['value'=>$numnom,'num'=>$cliente->clinum,'nom'=>$cliente->clinom,'dir'=>$cliente->clidir,'cor'=>$cliente->clicor,'tdicod'=>$cliente->tdicod];
      }
      return response()->json($results);
    }

    public function consultarcambio(Request $request){
      $search = $request->term;
      $cambio = DB::tABLE('tipocambio')->where('FecTipCambio','=',$search)->take(10)->get();
      $results = array();

      foreach($cambio as $tc => $tcam){
        $results[] = ['value'=>$tcam->FecTipCambio,'cam'=>$tcam->CamCompra];
      }
       return response()->json($results);
    }

    public function consultartipcambio(Request $request){
         $search = $request->fecemi;
         $cambio = DB::tABLE('tipocambio')->where('FecTipCambio','=',$search)->first();

         $res = $cambio->CamCompra;
       
      return $res;
    }


    public function consultarproducto(Request $request){
      $search = $request->term;       
       $rucemp = trim($rucemp);
      $productos= DB::tABLE('productos')->where('pronom', 'like','%'.$search.'%')->where('IdEmpresa','=',$rucemp)->where('proest','=','Activo')
        ->orderby('pronom','asc')->get();

      $results = array();
      foreach($productos as $pro){
        $codnom = $pro->procod.'|'.$pro->pronom;
        $results[] = ['value'=>$codnom,'pronom'=>$pro->pronom,'provun'=>$pro->provun,'umecod'=>$pro->umecod];
      }

      return response()->json($results);
    }


    public function consultartdi(Request $request){
      
      $search = $request->term;       
      
      $docidentidad = DB::tABLE('tipo_documento_identidad')->where('tdides', 'like','%'.$search.'%')->where('tdides','like','%'.$search.'%')->where('tdiest','=','Activo')->get();

       $results = array();
      foreach($docidentidad as $tdi){
        $results[] = ['id'=>$tdi->tdicod,'text'=>$tdi->tdides];
      }

      return response()->json($results);

    }

    public function verificarcomprobante(Request $request){
      $codunique = $request->get('codunique');

      $comprobante= DB::tABLE('cpe_cabecera')->where('codunique','=',$codunique)->first();
     
     if($comprobante!= ''){
        $respuesta = 'false';
     }else {
        $respuesta = 'true';
      }
      return $respuesta;
    }

   public function webservice ($cabfile,$puerto,$idcabecera,$tdocod,$rucemp){

        $data = array
        (
          "filename" => $cabfile,
        );

        $databuild_string = json_encode($data);
        $chbuild = curl_init("http://localhost:".$puerto."/FacturadorSunat/api/adapter/build/");
        curl_setopt($chbuild, CURLOPT_HEADER, true);
        curl_setopt($chbuild, CURLOPT_HTTPHEADER, array(
            'Accept: application/json',
            'Content-Type: application/json')
        );

        curl_setopt($chbuild, CURLOPT_POST, true);
        curl_setopt($chbuild, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($chbuild, CURLOPT_POSTFIELDS, $databuild_string);
        $results = curl_exec($chbuild);
        curl_close($chbuild);

        $results = substr($results,strpos($results,'{'),strpos($results,'}')-strpos($results,'{')+1);
        $respbuild = json_decode($results, true);
       
        if($tdocod=='01' || $tdocod=='03'){
          if(isset($respbuild['estado'],$respbuild['firma'])){
            $compbuild = cpe_cabecera::findOrFail($idcabecera);
            $compbuild->ccaestbu = $respbuild['estado'];
            $compbuild->codhash = $respbuild['firma'];
            $compbuild->respse = '02';
            $compbuild->update();
          }else{
             if($respbuild['message']=="" && $respbuild['code']=="" ){
              $compbuild = cpe_cabecera::findOrFail($idcabecera);
              $compbuild->respse = '01';
              $compbuild->update();
            }else{
              $compbuild = cpe_cabecera::findOrFail($idcabecera);
              $compbuild->ccaestbu = $respbuild['message'];
              $compbuild->respse = $respbuild['code'];
              $compbuild->update();
            }
          }
        }elseif($tdocod=='07' || $tdocod=='08'){
          if(isset($respbuild['estado'],$respbuild['firma'])){
            $compbuild = cpe_nota::findOrFail($idcabecera);
            $compbuild->ccaestbu = $respbuild['estado'];
            $compbuild->respse = $respbuild['firma'];
            $compbuild->respse = '02';
            $compbuild->update();
          }else{
            if($respbuild['message']=="" && $respbuild['code']=="" ){
              $compbuild = cpe_nota::findOrFail($idcabecera);
              $compbuild->respse = '01';
              $compbuild->update();
            }else{
              $compbuild = cpe_nota::findOrFail($idcabecera);
              $compbuild->ccaestbu = $respbuild['message'];
              $compbuild->respse = $respbuild['code'];
              $compbuild->update();
            }
          
          }
        }else{
          $fecbaj = $tdocod;
          $numcor = $idcabecera;
          if(isset($respbuild['estado'],$respbuild['firma'])){
            cpe_baja::where('cbacor',$numcor)
            ->where('cbdfco',$fecbaj)
            ->update(['cbaestbu'=>$respbuild['estado'],'codhash'=>$respbuild['firma'],'respse'=>'02']);
          }else{
            if($respbuild['message']=="" && $respbuild['code']=="" ){
                cpe_baja::where('cbacor',$numcor)
                ->where('cbdfco',$fecbaj)
                ->update(['respse'=>'01']);
            }else{
               cpe_baja::where('cbacor',$numcor)
               ->where('cbdfco',$fecbaj)
               ->update(['cbaestbu'=>$respbuild['message'],'respse'=>$respbuild['code']]);
            }
           
          }
        }
       

       
       //Enviar parÃ¡metro a webservice send

        $datasend_string = json_encode($data);
        $chsend = curl_init("http://localhost:".$puerto."/FacturadorSunat/api/adapter/send/");
        curl_setopt($chsend, CURLOPT_HEADER, true);
        curl_setopt($chsend, CURLOPT_HTTPHEADER, array(
            'Accept: application/json',
            'Content-Type: application/json')
        );

        curl_setopt($chsend, CURLOPT_POST, true);
        curl_setopt($chsend, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($chsend, CURLOPT_POSTFIELDS, $datasend_string);
        $results = curl_exec($chsend);
        curl_close($chsend);
        
        $results = substr($results,strpos($results,'{'),strpos($results,'}')-strpos($results,'{')+1);
        $respsend = json_decode($results, true);


        if($tdocod=='01' || $tdocod=='03'){
          if(isset($respsend['estado'],$respsend['descripcion'])){
            $compsend = cpe_cabecera::findOrFail($idcabecera);
            $compsend->ccaestse = $respsend['estado'];
            $compsend->respse = $respsend['descripcion'];
            $compsend->update();
          }else{
            if($respsend['message']=="" && $respsend['code']==""){
               $compsend = cpe_cabecera::findOrFail($idcabecera);
               if($compsend->codhash!=""){
                 $compsend->respse = '02';
               }else{
                $compsend->respse = $respsend['code'];
               }
               $compsend->update();
            }else{
               $compsend = cpe_cabecera::findOrFail($idcabecera);
               $compsend->ccaestse = $respsend['message'];
               $compsend->respse = $respsend['code'];
               $compsend->update();
            }
           
          }

        }elseif($tdocod=='07' || $tdocod=='08'){
          if(isset($respsend['estado'],$respsend['descripcion'])){
            $compsend = cpe_nota::findOrFail($idcabecera);
            $compsend->ccaestse = $respsend['estado'];
            $compsend->respse = $respsend['descripcion'];
            $compsend->update();
          }else{
            if($respsend['message']=="" && $respsend['code']==""){
               $compsend = cpe_nota::findOrFail($idcabecera);
               if($compsend->codhash!=""){
                 $compsend->respse = '02';
               }else{
                $compsend->respse = $respsend['code'];
               }
               $compsend->update();
            }else{
               $compsend = cpe_nota::findOrFail($idcabecera);
               $compsend->ccaestse = $respsend['message'];
               $compsend->respse = $respsend['code'];
               $compsend->update();
            }
           
          }
        }else{
          if(isset($respsend['estado'],$respsend['descripcion'])){
            cpe_baja::where('cbacor','=',$idcabecera)->update(['cbaestse'=>$respsend['estado'],'respse'=>$respsend['descripcion']]);
          }else{
            if($respsend['message']=="" && $respsend['code']==""){
               cpe_baja::where('cbacor','=',$idcabecera)->where('codhash','<>','')->update(['respse'=>'02']);
            }else{
              cpe_baja::where('cbacor','=',$idcabecera)->update(['cbaestse'=>$respsend['message'],'respse'=>$respsend['code']]);
            }
          }

        }

        return Redirect::to('/PanelClientes/'.$rucemp);
   }

   public function webservicesend ($cabfile,$puerto,$idcabecera,$tdocod,$rucemp){

    
      $data = array
        (
          "filename" => $cabfile,
        );
       
       //Enviar parÃ¡metro a webservice send

        $datasend_string = json_encode($data);
        $chsend = curl_init("http://localhost:".$puerto."/FacturadorSunat/api/adapter/send/");
        curl_setopt($chsend, CURLOPT_HEADER, true);
        curl_setopt($chsend, CURLOPT_HTTPHEADER, array(
            'Accept: application/json',
            'Content-Type: application/json')
        );

        curl_setopt($chsend, CURLOPT_POST, true);
        curl_setopt($chsend, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($chsend, CURLOPT_POSTFIELDS, $datasend_string);
        $results = curl_exec($chsend);
        curl_close($chsend);
        
        $results = substr($results,strpos($results,'{'),strpos($results,'}')-strpos($results,'{')+1);
        $respsend = json_decode($results, true);

         if($tdocod=='01' || $tdocod=='03'){
          if(isset($respsend['estado'],$respsend['descripcion'])){
            $compsend = cpe_cabecera::findOrFail($idcabecera);
            $compsend->ccaestse = $respsend['estado'];
            $compsend->respse = $respsend['descripcion'];
            $compsend->update();
          }else{
            if($respsend['message']=="" && $respsend['code']==""){
               $compsend = cpe_cabecera::findOrFail($idcabecera);
               if($compsend->codhash!=""){
                 $compsend->respse = '02';
               }else{
                $compsend->respse = $respsend['code'];
               }
               $compsend->update();
            }else{
               $compsend = cpe_cabecera::findOrFail($idcabecera);
               $compsend->ccaestse = $respsend['message'];
               $compsend->respse = $respsend['code'];
               $compsend->update();
            }
           
          }

        }elseif($tdocod=='07' || $tdocod=='08'){
          if(isset($respsend['estado'],$respsend['descripcion'])){
            $compsend = cpe_nota::findOrFail($idcabecera);
            $compsend->ccaestse = $respsend['estado'];
            $compsend->respse = $respsend['descripcion'];
            $compsend->update();
          }else{
            if($respsend['message']=="" && $respsend['code']==""){
               $compsend = cpe_nota::findOrFail($idcabecera);
               if($compsend->codhash!=""){
                 $compsend->respse = '02';
               }else{
                $compsend->respse = $respsend['code'];
               }
               $compsend->update();
            }else{
               $compsend = cpe_nota::findOrFail($idcabecera);
               $compsend->ccaestse = $respsend['message'];
               $compsend->respse = $respsend['code'];
               $compsend->update();
            }
           
          }
        }else{
          if(isset($respsend['estado'],$respsend['descripcion'])){
            cpe_baja::where('cbacor','=',$idcabecera)->update(['cbaestse'=>$respsend['estado'],'respse'=>$respsend['descripcion']]);
          }else{
            if($respsend['message']=="" && $respsend['code']==""){
               cpe_baja::where('cbacor','=',$idcabecera)->where('codhash','<>','')->update(['cbaestse'=>$respsend['message'],'respse'=>'02']);
            }else{
              cpe_baja::where('cbacor','=',$idcabecera)->update(['cbaestse'=>$respsend['message'],'respse'=>$respsend['code']]);
            }
          }

        }


      
         return Redirect::to('/PanelClientes/'.$rucemp);
   }

  public function registrarnota(Request $request){

         $rucemp = trim($rucemp);
  
        $serdoc = $request->get('serdoc');
        $numdoc = str_pad($request->get('numdoc'),8,"0", STR_PAD_LEFT);
        $tdicod= $request->get('tdi_cod');
        $tipdoc = $request->get('tdo_cod');
        $tipnot = $request->get('tipnot');
        $desnota = $request->get('desnota');
        $motivo = $request->get('obser');
        $clinom = $request->get('clinom');
        $clinum = $request->get('clinum');
        $clidir = $request->get('clidir');
        $clicor = $request->get('clicor');
        $tipcambio = $request->get('camdoc');
        $fecemi = $request->get('fecEmi');
        $mondoc = $request->get('tipmon');
        $monnom = $request->get('mondoc');
        $otrosc = $request->get('otrosc');
        $grav = $request->get('grav');
        $grat = $request->get('grat');
        $inaf = $request->get('inaf');
        $exon = $request->get('exon');
        $igv = $request->get('igv');
        $isc = $request->get('isc');
        $otros = $request->get('otros');
        $total = $request->get('total');
        $tdocod = $request->get('txt_tdocod');
        $serdocmod = $request->get('serdocmod');
        $numdocmod = str_pad($request->get('numdocmod'),8,"0", STR_PAD_LEFT);

        //DATOS DOCUMENTO RELACIONADO

      $tdomod = $request->get('tdomod');
       // $tipnc = $request->get('tipnc');
       
  
        $docmod = DB::tABLE('cpe_cabecera')->select('IdCpe_cabecera')->where('IdEmpresa','=',$rucemp)->where('serdoc','=',$serdocmod)->where('numdoc','=',$numdocmod)->first();

        $IdCpe_cabecera=$docmod->IdCpe_cabecera;
        //-----FIN DATOS DOCUMENTOS RELACIONADOS       
        
        //Registrar la cabecera de la factura

        $cabecera = new cpe_nota;
        $cabecera->tdocod = $tdocod;
        //$cabecera->tdocod = $tdocod;
        $cabecera->ccafem = $fecemi;
        $cabecera->ccaobs = $motivo;
        $cabecera->serdoc = $serdoc;
        $cabecera->numdoc = $numdoc;
        $cabecera->tipcambio = $tipcambio;
        $cabecera->tipnot = $tipnot;
       // $cabecera->IdEmpresa = $rucemp;
        $cabecera->IdUsuario = Auth::user()->IdUsuario;
        $cabecera->ccacar = $request->get('otrosc');
        $cabecera->ccatvg = $request->get('grav');
        $cabecera->ccatvgr = $request->get('grat');
        $cabecera->ccatvi = $request->get('inaf');
        $cabecera->ccatve = $request->get('exon');
        $cabecera->ccaigv = $request->get('igv');
        $cabecera->ccaisc = $request->get('isc');
        $cabecera->ccaotr = $request->get('otros');
        $cabecera->ccaitv = $request->get('total');
        $cabecera->serdoc= $request->get('serdoc');
        $cabecera->IdCpe_cabecera = $IdCpe_cabecera;
        $cabecera->IdUsuario = Auth::user()->IdUsuario;

        
        $cabecera->save();

        $nota = $serdoc.'-'.$numdoc;
        $cabfactura = cpe_cabecera::findOrFail($IdCpe_cabecera);
        $cabfactura->ccanot = $nota;
        $cabfactura->update();

         //Actualizar la serie y/o el nÃºmero del documento a emitir


        if ($tdocod =='07') {
            if($tipdoc =='01'){
                $empresa = Empresa::findOrFail($rucemp);
                $empresa->FcseEmpresa = $request->get('serdoc');
                $empresa->FcnuEmpresa = $request->get('numdoc');
                $empresa->update();
            }elseif($tipdoc=='03'){
                $empresa = Empresa::findOrFail($rucemp);
                $empresa->BcseEmpresa = $request->get('serdoc');
                $empresa->BcnuEmpresa = $request->get('numdoc');
                $empresa->update();
            }
            if($tipnot =='03'){
                 $detpromod = $request->get('detpromod');
            }

        }elseif ($tdocod =='08') {
            if($tipdoc =='01'){
                $empresa = Empresa::findOrFail($rucemp);
                $empresa->FdseEmpresa = $request->get('serdoc');
                $empresa->FdnuEmpresa = $request->get('numdoc');
                $empresa->update();
            }elseif($tipdoc=='03'){
                $empresa = Empresa::findOrFail($rucemp);
                $empresa->BdseEmpresa = $request->get('serdoc');
                $empresa->BdnuEmpresa = $request->get('numdoc');
                $empresa->update();
            }
        }
        

       
        
        //Ruta donde se guardarán los archivos cab y det.
        $raiz = '/opt/fs/'.$rucemp.'/sunat_archivos/sfs/DATA/';
        

       //Registrar el detalle de la factura
        $cantidades = $request->get('cant');
        $unidades = $request->get('unid');
        $codpro = $request->get('codpro');

      
        $detpro = $request->get('detpro');
        
        
        $vunit = $request->get('vunit');
        $vigv = $request->get('vigv');
        $tigv = $request->get('tigv');
        $vsub = $request->get('vsub');
        $vtot = $request->get('vtot');


        if(($tdocod=='07' && $tipnot=='04') || ($tdocod=='08' && $tipnot=='01') || ($tdocod=='08' && $tipnot=='03') ){
            $detalle = new cpe_nota_detalle;
            $detalle->IdCpe_nota =  $cabecera->IdCpe_nota; 
            $dpro = $detpro;
            $detalle->cdedes = $request->get("detn");
        
            $detalle->cdecan = '0';
            $detalle->umecod = 'UNI';
            $detalle->cdevun = $request->get("vunitn");
            $detalle->cdeigv = $igv;
            $detalle->cdepve = $request->get("vunitn");
            $detalle->cdevve = $total;
            $detalle->tigcod = '10';
            $detalle->procod = '0';
            $detalle->cdepsu = '0';
            
            $detalle->save();

            //Guardar en una variable los items en el archivo .det
            $det = '0'.'|'.$detalle->cdecan.'|'.$detalle->procod.'|'.$detalle->cdepsu.'|'.$detalle->cdedes.'|'.$detalle->cdevun.'|0.00|'.$detalle->cdeigv.'|'.$detalle->tigcod.'|'.'0.00'.'|'.'01'.'|'.$detalle->cdepve.'|'.$detalle->cdevve."\n";
            $detfile = $rucemp.'-'.$cabecera->tdocod.'-'.$cabecera->serdoc.'-'.$numdoc.'.DET'; 
        
            //Crear e insertar los items al archivo det
            //$archdet = fopen($raiz.$detfile, "a");
            $archdet = fopen($detfile, "a");
            fputs($archdet,$det);
            fclose($archdet);

        }else{
           foreach( $unidades as $index => $ume ) {
            $detalle = new cpe_nota_detalle;
            $detalle->IdCpe_nota =  $cabecera->IdCpe_nota; 
            $dpro = $detpro[$index];

            if($tdocod=='07' && $tipnot=='03'){
              $dpromod = $detpromod[$index];
              $detalle->cdedes = 'Dice: '.$dpro.' Debe decir: '.$dpromod;
            }else{
              $detalle->cdedes = $dpro;
            }


              $codproducto = $codpro[$index];
              $detalle->umecod = $ume;
              $detalle->cdecan = $cantidades[$index];
              $detalle->procod = $codproducto;
              $detalle->cdepsu = $codproducto;
              $detalle->cdevun = $vunit[$index];
              $detalle->cdeigv = $vigv[$index];
              $detalle->tigcod = $tigv[$index];
              $detalle->cdepve = $vsub[$index];
              $detalle->cdevve = $vtot[$index];
            
              $detalle->save();

            //Guardar en una variable los items en el archivo .det
           $codumecin = unidad_medida::findOrFail($ume);
            $det = $codumecin->umecin.'|'.$detalle->cdecan.'|'.$detalle->procod.'|'.$detalle->cdepsu.'|'.$detalle->cdedes.'|'.$detalle->cdevun.'|0.00|'.$detalle->cdeigv.'|'.$detalle->tigcod.'|'.'0.00'.'|'.'01'.'|'.$detalle->cdepve.'|'.$detalle->cdevve."\n";
            $detfile = $rucemp.'-'.$cabecera->tdocod.'-'.$cabecera->serdoc.'-'.$numdoc.'.DET'; 
        
            //Crear e insertar los items al archivo det
            //$archdet = fopen($raiz.$detfile, "a");
            $archdet = fopen($detfile, "a");
            fputs($archdet,$det);
            fclose($archdet);
          }
        }
       
        //Guardar en una variable el nombre del archivo cab
           $cabfile =  $rucemp.'-'.$cabecera->tdocod.'-'.$cabecera->serdoc.'-'.$numdoc.'.NOT'; 

        //Segun el tipo de documento se crearÃ¡ el contenido del archivo cab
        $cab = $fecemi.'|'.$tipnot.'|'.$motivo.'|'. $cabfactura->tdocod.'|'.$serdocmod.'-'.$numdocmod.'|'.$cabfactura->tdicod.'|'.$clinum.'|'.$clinom.'|'.$mondoc.'|'.$otrosc.'|'.$grav.'|'. $inaf.'|'.$exon.'|'.$igv.'|'.$isc.'|'.$otros.'|'.$total;
    

        //Crear el archivo cab e insertar el contenido
        //$archivo = fopen($raiz.$cabfile, "a");
        $archivo = fopen($cabfile, "a");
        fputs($archivo,$cab);
        fclose($archivo);
       
     

      //Crear Factura PDF guardarla para posterior consultas
    $codfact = $cabecera->IdCpe_nota;
  
    $hash= DB::tABLE('cpe_nota')->select('codhash','respse')->where('IdCpe_nota','=',$codfact)->first();

    $webservice = self::webservice($cabfile,$empresa->puerto,$codfact,$tdocod,session('rucemp'));  

   if($hash->codhash!=''|| $hash->respse=='05'){ 
      
      $detpdf= DB::tABLE('cpe_nota_detalle as d')->join('cpe_nota as n','n.IdCpe_nota','=','d.IdCpe_nota')->where('n.IdCpe_nota','=',$codfact)->get();

      $nompdffile =  $rucemp.'-'.$cabecera->tdocod.'-'.$cabecera->serdoc.'-'.$numdoc.'.pdf'; 
      $rutpdfile = '/opt/fs/'.$rucemp.'/sunat_archivos/sfs/REPO/ ';
      if($tdocod=='07'){
        $view = \View::make('empresas.comprobantes.'.$rucemp.'.notacreditopdf', compact('serdoc','numdoc','clinom','clinum','fecemi','clidir','monnom','hash','motivo','detpdf','empresa','cabecera','desnota','serdocmod','numdocmod','tdomod'));
      }elseif($tdocod=='08'){
        $view = \View::make('empresas.comprobantes.'.$rucemp.'.notadebitopdf', compact('serdoc','numdoc','clinom','clinum','fecemi','clidir','monnom','hash','motivo','detpdf','empresa','cabecera','desnota','serdocmod','numdocmod','tdomod'));
      }

      $pdf = \App::make('dompdf.wrapper');
      $contenido = $view->render();
      //$pdf->loadHTML($view)->setPaper('A4', 'lanscape')->save($rutpdfile.$nompdffile);
      $pdf->loadHTML($view)->setPaper('A4', 'lanscape')->save($nompdffile);

    }
     
     return $webservice;

    }



    //NOTA DE CREDITO Y DÉBITO

    public function tiponotacd($tdocod,$idcabecera,$ncdcod){
      $rucemp = $rucemp;
    //  $cabecera=DB::tABLE('cpe_cabecera')->where('IdCpe_cabecera','=',$idcabecera)->where('IdEmpresa','=',$rucemp)->first();

       $cabecera = DB::tABLE('cpe_cabecera as cab')
      ->join('cliente as cli','cab.ccandi','=','cli.clinum')
      ->join('tipo_documento as tdo','cab.tdocod','=','tdo.tdocod')
      ->join('tipo_documento_identidad as tdi','cab.tdicod','=','tdi.tdicod')
      ->join('moneda as mon','cab.moncod','=','mon.moncod')
      ->join('tipo_operacion as top','cab.topcod','=','top.topcod')
      ->where('IdCpe_cabecera','=',$idcabecera)->where('IdEmpresa','=',$rucemp)
      ->first();

     // $detalle=DB::tABLE('cpe_detalle as det')->join('unidad_medida as umed','det.umecod','=','umed.umecod')->where('IdCpe_cabecera','=',$idcabecera)->get();

      $igv = DB::tABLE('tipo_igv')->where('tigest','=','Activo')
        ->orderBy('tigcod','asc')->get();

      

      // consultar unidades de medida
      $unidades = DB::tABLE('unidad_medida')->where('umeest','=','Activo')
      ->orderBy('umecod','asc')->get();

      // consultar tipo de operaciones
      $operaciones = DB::tABLE('tipo_operacion')->where('topest','=','Activo')
      ->orderBy('topcod','asc')->get();

      // consultar tipos de documentos de identidad
      $docidentidad = DB::tABLE('tipo_documento_identidad')->where('tdiest','=','Activo')->orderBy('tdicod','asc')->get();

      //consultar tipo de documento 
      $doccomprobante = DB::tABLE('tipo_documento')->where('tdoest','=','Activo')->orderBy('tdocod','asc')->get();

      // obtener el ruc de la empresa en la cual se logueo
      $rucemp = trim($rucemp);

      // consultar los clientes que le pertenece a la empresa
      $clientes= DB::tABLE('cliente')->where('rucemp','=',$rucemp)->where('cliest','=','Activo')
      ->orderby('clinom','asc')->get();

      //consultar productos que le pertenece a la empresa
      $productos= DB::tABLE('productos')->where('IdEmpresa','=',$rucemp)->where('proest','=','Activo')
      ->orderby('pronom','asc')->get();

      // consultar tipos de monedas
      $monedas = DB::tABLE('moneda')->where('monest','=','Activo')->orderby('moncod','asc')->get();


  /*    if ($ncdcod =='07') {
        $senuncd = DB::tABLE('empresa')->select('CseEmpresa','CnuEmpresa')->where('IdEmpresa','=',$rucemp)->first(); 
        $nota = DB::tABLE('tipo_nota_credito')->where('ncest','=','Activo')
        ->orderBy('nccod','asc')->get();


      }elseif ($ncdcod =='08') {
        $senuncd = DB::tABLE('empresa')->select('DseEmpresa','DnuEmpresa')->where('IdEmpresa','=',$rucemp)->first(); 
         $nota = DB::tABLE('tipo_nota_debito')->where('ndest','=','Activo')
        ->orderBy('ndcod','asc')->get();
      }*/

      if ($ncdcod =='07') {
        if($tdocod =='01'){
          $senuncd = DB::tABLE('empresa')->select('FcseEmpresa','FcnuEmpresa')->where('IdEmpresa','=',$rucemp)->first();
          $nota = DB::tABLE('tipo_nota_credito')->where('ncest','=','Activo')->get();
        }elseif($tdocod=='03'){
          $senuncd = DB::tABLE('empresa')->select('BcseEmpresa','BcnuEmpresa')->where('IdEmpresa','=',$rucemp)->first();
          $nota = DB::tABLE('tipo_nota_credito')->where('ncest','=','Activo')->get();
        }
      }elseif ($ncdcod =='08') {
          if($tdocod =='01'){
          $senuncd = DB::tABLE('empresa')->select('FdseEmpresa','FdnuEmpresa')->where('IdEmpresa','=',$rucemp)->first();
          $nota = DB::tABLE('tipo_nota_debito')->where('ndest','=','Activo')->get();
        }elseif($tdocod=='03'){
          $senuncd = DB::tABLE('empresa')->select('BdseEmpresa','BdnuEmpresa')->where('IdEmpresa','=',$rucemp)->first();
          $nota = DB::tABLE('tipo_nota_debito')->where('ndest','=','Activo')->get();
        }
      }
        

      return view('empresas.comprobantes.tiponota',['cabecera'=>$cabecera,'senuncd'=>$senuncd,'igv'=>$igv,'monedas'=>$monedas,'unidades'=>$unidades,'operaciones'=>$operaciones,'docidentidad'=>$docidentidad,'clientes'=>$clientes,'tdocod'=>$tdocod,'ncdcod'=>$ncdcod,'productos'=>$productos,'doccomprobante'=>$doccomprobante,'nota'=>$nota]);
         

    }

    public function emitirnota(Request $request){

     $idcabecera = $request->get('idcabecera');
     $tdocod = $request->get('tdo_cod');
     $ncdcod = $request->get('txt_tdocod');
     $tipnot = $request->get('tipnot');
     // datos $tdocod,$idcabecera,$ncdcod,$tipncd

      $rucemp = $rucemp;
    //  $cabecera=DB::tABLE('cpe_cabecera')->where('IdCpe_cabecera','=',$idcabecera)->where('IdEmpresa','=',$rucemp)->first();

       $cabecera = DB::tABLE('cpe_cabecera as cab')
      ->join('cliente as cli','cab.ccandi','=','cli.clinum')
      ->join('tipo_documento as tdo','cab.tdocod','=','tdo.tdocod')
      ->join('tipo_documento_identidad as tdi','cab.tdicod','=','tdi.tdicod')
      ->join('moneda as mon','cab.moncod','=','mon.moncod')
      ->join('tipo_operacion as top','cab.topcod','=','top.topcod')
      ->where('IdCpe_cabecera','=',$idcabecera)->where('IdEmpresa','=',$rucemp)
      ->first();

      $detalle=DB::tABLE('cpe_detalle as det')->join('unidad_medida as umed','det.umecod','=','umed.umecod')->join('tipo_igv as ti','det.tigcod','=','ti.tigcod')->where('IdCpe_cabecera','=',$idcabecera)->get();

      $igv = DB::tABLE('tipo_igv')->where('tigest','=','Activo')
        ->orderBy('tigcod','asc')->get();



      // consultar unidades de medida
      $unidades = DB::tABLE('unidad_medida')->where('umeest','=','Activo')
      ->orderBy('umecod','asc')->get();

      // consultar tipo de operaciones
      $operaciones = DB::tABLE('tipo_operacion')->where('topest','=','Activo')
      ->orderBy('topcod','asc')->get();

      // consultar tipos de documentos de identidad
      $docidentidad = DB::tABLE('tipo_documento_identidad')->where('tdiest','=','Activo')->orderBy('tdicod','asc')->get();

      //consultar tipo de documento 
      $doccomprobante = DB::tABLE('tipo_documento')->where('tdoest','=','Activo')->orderBy('tdocod','asc')->get();

      // obtener el ruc de la empresa en la cual se logueo
      $rucemp = trim($rucemp);

      // consultar los clientes que le pertenece a la empresa
      $clientes= DB::tABLE('cliente')->where('rucemp','=',$rucemp)->where('cliest','=','Activo')
      ->orderby('clinom','asc')->get();

      //consultar productos que le pertenece a la empresa
      $productos= DB::tABLE('productos')->where('IdEmpresa','=',$rucemp)->where('proest','=','Activo')
      ->orderby('pronom','asc')->get();

      // consultar tipos de monedas
      $monedas = DB::tABLE('moneda')->where('monest','=','Activo')->orderby('moncod','asc')->get();


      if ($ncdcod =='07') {
        if($tdocod =='01'){
          $senuncd = DB::tABLE('empresa')->select('FcseEmpresa','FcnuEmpresa')->where('IdEmpresa','=',$rucemp)->first();
          $nota = DB::tABLE('tipo_nota_credito')->where('nccod','=',$tipnot)->first();
        }elseif($tdocod=='03'){
          $senuncd = DB::tABLE('empresa')->select('BcseEmpresa','BcnuEmpresa')->where('IdEmpresa','=',$rucemp)->first();
          $nota = DB::tABLE('tipo_nota_credito')->where('nccod','=',$tipnot)->first();
        }
      }elseif ($ncdcod =='08') {
          if($tdocod =='01'){
          $senuncd = DB::tABLE('empresa')->select('FdseEmpresa','FdnuEmpresa')->where('IdEmpresa','=',$rucemp)->first();
          $nota = DB::tABLE('tipo_nota_debito')->where('ndcod','=',$tipnot)->first();
        }elseif($tdocod=='03'){
          $senuncd = DB::tABLE('empresa')->select('BdseEmpresa','BdnuEmpresa')->where('IdEmpresa','=',$rucemp)->first();
          $nota = DB::tABLE('tipo_nota_debito')->where('ndcod','=',$tipnot)->first();
        }
      }
        


         return view('empresas.comprobantes.emitirnota',['cabecera'=>$cabecera,'detalle'=>$detalle,'senuncd'=>$senuncd,'igv'=>$igv,'monedas'=>$monedas,'unidades'=>$unidades,'operaciones'=>$operaciones,'docidentidad'=>$docidentidad,'clientes'=>$clientes,'tdocod'=>$tdocod,'ncdcod'=>$ncdcod,'productos'=>$productos,'doccomprobante'=>$doccomprobante,'nota'=>$nota]);
    }

    public function bajacomprobante(){
      $rucemp = $rucemp;
      $fecact = date('Y-m-d');
      $numbaj = DB::tABLE('empresa')->select('IdEmpresa','BanuEmpresa','fecbaja')->where('IdEmpresa','=',$rucemp)->first(); 
      if($numbaj->fecbaja==$fecact){
        $cor=$numbaj->BanuEmpresa+1;
      }else{
        $cabcomp = empresa::findOrFail($numbaj->IdEmpresa);
        $cabcomp->fecbaja = $fecact;
        $cabcomp->BanuEmpresa = 0;
        $cabcomp->update();
        $cor = $cabcomp->BanuEmpresa+1;
      }

       return view('empresas.comprobantes.comunicacionbaja',['cor'=>$cor]);
     
    }

    public function formbajacomprobante($serdoc,$numdoc,$tdocod,$fecemi,$tdodes){
      $rucemp = $rucemp;
      $fecact = date('Y-m-d');
      $numbaj = DB::tABLE('empresa')->select('IdEmpresa','BanuEmpresa','fecbaja')->where('IdEmpresa','=',$rucemp)->first(); 
      if($numbaj->fecbaja==$fecact){
        $cor=$numbaj->BanuEmpresa+1;
      }else{
        $cabcomp = empresa::findOrFail($numbaj->IdEmpresa);
        $cabcomp->fecbaja = $fecact;
        $cabcomp->BanuEmpresa = 0;
        $cabcomp->update();
        $cor = $cabcomp->BanuEmpresa+1;
      }

      $comp = DB::tABLE('cpe_cabecera as cab')
      ->join('moneda as mon','cab.moncod','=','mon.moncod')
      ->where('serdoc','=',$serdoc)
      ->where('numdoc','=',$numdoc)
      ->where('IdEmpresa','=',$rucemp)->first();

      $sernumdoc = $serdoc.'-'.$numdoc;
      return view('empresas.comprobantes.emitirbaja',['cor'=>$cor,'sernumdoc'=>$sernumdoc,'tdodes'=>$tdodes,'tdocod'=>$tdocod,'fecemi'=>$fecemi,'monnom'=>$comp->monnom,'ccaitv'=>$comp->ccaitv]);
    }


    public function registrarbajacomprobante(Request $request){
      $rucemp = $rucemp;
      $serdocbaja = $request->get('serdocbaja');
      $fecbaj = $request->get('fecbaj');
      $numbaj = $request->get('numbaj');
      $numbajmod = str_pad($numbaj,3,"0", STR_PAD_LEFT);

      $obser = $request->get('obser');
      $fecemi = $request->get('fecemi');
      $tdomod = $request->get('tdomod');
      $tdocod = $request->get('tdo_cod');

      $cabfile =  $rucemp.'-RA'.'-'.str_replace("-", "", $fecbaj).'-'.$numbajmod.'.CBA';
      $nompdffile =  $rucemp.'-'.str_replace("-", "", $fecbaj).'-'.$numbajmod.'.pdf'; 
      foreach( $serdocbaja as $index => $ser ) {
        $docbaja = new cpe_baja;
        $sernumbaja =$serdocbaja[$index]; 
        $ser = substr($sernumbaja,strpos($sernumbaja,'-')-4,4);
        $num = substr($sernumbaja,strpos($sernumbaja,'-')+1,8);
        $numdoc = str_pad($num,8,"0", STR_PAD_LEFT);

        $docbaja->cbanum =  $ser.'-'.$numdoc;
        $docbaja->cbacor =  $numbaj; 
        $docbaja->cbamot =  $obser[$index]; 
        $docbaja->cbdfco =  $fecbaj; 
        $docbaja->cbafec =  $fecemi[$index]; 
        $docbaja->tdocod =  $tdocod[$index]; 
        $docbaja->IdEmpresa =  $rucemp; 
        $cabecera= DB::tABLE('cpe_cabecera')->select('IdCpe_cabecera','serdoc','numdoc')->where('serdoc','=',$ser)->where('numdoc','=',$numdoc)->first();
        $docbaja->IdCpe_cabecera = $cabecera->IdCpe_cabecera; 
        $docbaja->save();

        $cabcomp = cpe_cabecera::findOrFail($cabecera->IdCpe_cabecera);
        $cabcomp->ccabaj = str_replace("-", "", $fecbaj).'-'.$numbajmod;
        $cabcomp->update();
  
        $detbaj =  $docbaja->cbafec.'|'.$docbaja->cbdfco.'|'.$docbaja->tdocod.'|'. $docbaja->cbanum.'|'.$docbaja->cbamot."\n";

       $raiz = '/opt/fs/'.$rucemp.'/sunat_archivos/sfs/DATA/';

            //Crear e insertar los items al archivo det
            //$archdet = fopen($raiz.$cabfile, "a");
            $archdet = fopen($cabfile, "a");
            fputs($archdet,$detbaj);
            fclose($archdet);

        }
          
        $empresa = Empresa::findOrFail($rucemp);
        $empresa->BanuEmpresa = $numbaj;
        $empresa->update();

        
        //Crear Comunicación de baja PDF guardarla para posterior consultas
        $corbaja = $docbaja->cbacor;

        $webservice = self::webservice($cabfile,$empresa->puerto,$corbaja,$fecbaj,session('rucemp')); 
        
            $dobaja= DB::tABLE('cpe_baja as b')
            ->join('cpe_cabecera as c','b.IdCpe_cabecera','=','c.IdCpe_cabecera')
            ->join('moneda as m','c.moncod','=','m.moncod')
            ->join('tipo_documento as tp','b.tdocod','=','tp.tdocod')
            ->where('cbdfco','=',$fecbaj)
            ->where('cbacor','=',$corbaja)->where('b.IdEmpresa','=',$rucemp)->get();
   

            $rutpdfile = '/opt/fs/'.$rucemp.'/sunat_archivos/sfs/REPO/ ';
            $view = \View::make('empresas.comprobantes.'.$rucemp.'.comunicacionbajapdf', compact('dobaja','fecbaj','numbaj','empresa'));

            $pdf = \App::make('dompdf.wrapper');
            $contenido = $view->render();
            //$pdf->loadHTML($view)->setPaper('A4', 'lanscape')->save($rutpdfile.$nompdffile);
            $pdf->loadHTML($view)->setPaper('A4', 'lanscape')->save($nompdffile);

        // llamar al webservice build and send
        

      return $webservice;
    }

    public function listarnotas($idcabecera){

      $rucemp =$rucemp;
      $notas = DB::tABLE('cpe_nota as n')->select('n.ccafem','n.serdoc','n.numdoc','tdodes','ccandi','ccanom','mn.monnom','n.ccaitv','n.respse','n.tdocod','c.IdEmpresa','n.IdCpe_nota','n.tdocod','n.codhash')
      ->join('tipo_documento as td','n.tdocod','=','td.tdocod')
      ->join('cpe_cabecera as c','n.IdCpe_cabecera','=','c.IdCpe_cabecera')
      ->join('moneda as mn','c.moncod','=','mn.moncod')
      ->where('n.IdCpe_cabecera','=',$idcabecera)
      ->where('c.IdEmpresa','=',$rucemp)
      ->orderby('n.IdCpe_nota','desc')
      ->paginate(10);
       $empresa = Empresa::findOrFail($rucemp);

       $sndocmod = DB::tABLE('cpe_cabecera')->select('serdoc','numdoc')->where('IdCpe_cabecera','=',$idcabecera)->first();

        return view('empresas.comprobantes.listarnotas',['notas'=>$notas,'empresa'=>$empresa,'sndocmod'=>$sndocmod]);

    }

}
