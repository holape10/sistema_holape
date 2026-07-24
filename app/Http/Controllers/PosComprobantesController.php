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
use MasterSoft\User;
use MasterSoft\tipo_documento_identidad;
use MasterSoft\productos;
use MasterSoft\tipocambio;
use MasterSoft\MontoLetras;
use Illuminate\Support\Facades\Auth;
use MasterSoft\Http\Requests\FacturaCreateFormRequest;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Input;
use DomDocument;
use SoapClient;
use SoapHeader;
use SoapVar;
use ZipArchive;
use stdClass;
use SimpleXMLElement;
use Greenter\XMLSecLibs\Sunat\SignedXml;
use Greenter\XMLSecLibs\Certificate\X509Certificate;
use Greenter\XMLSecLibs\Certificate\X509ContentType;
use Artisaninweb\SoapWrapper\SoapWrapper;
use App\Soap\Request\GetConversionAmount;
use App\Soap\Response\GetConversionAmountResponse;
use PDF;
use DB;

class POSComprobantesController extends Controller
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

    public function index(Request $request)
    {

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
            $empresa = Empresa::findOrFail(Auth::user()->IdEmpresa);
            $doccomprobante = DB::tABLE('tipo_documento')->where('tdoest','=','Activo')->orderBy('tdocod','asc')->pluck('tdodes', 'tdocod');

          
            
            $IdEmpresa = Auth::user()->IdEmpresa;
            $ser = substr($comp,strpos($comp,'-')-4,4);
            $num = substr($comp,strpos($comp,'-')+1,8);

           if(empty($razsoc) && empty($respse) && $tipdoc==0 && empty($fecin) && empty($fecfin) && empty($serdoc) && empty($numdoc) && empty($comp)){

            $compcabecera = DB::tABLE('cpe_cabecera as cpe_c')->select('ccafem','tdodes','cpe_c.serdoc','cpe_c.numdoc','tdides','cpe_c.ccandi','cpe_c.ccanom','monnom','cpe_c.ccaitv','cpe_c.IdEmpresa','cpe_c.tdocod','cpe_c.IdCpe_cabecera','cpe_c.ccanot','cpe_c.ccabaj','cpe_c.codhash','cpe_c.ccasunrescod')
               ->join('tipo_documento as tip_d','cpe_c.tdocod','=','tip_d.tdocod')
               ->join('tipo_documento_identidad as tdi','cpe_c.tdicod','=','tdi.tdicod')
               ->join('moneda as mon','cpe_c.moncod','=','mon.moncod')
               ->where('cpe_c.IdEmpresa','=',$IdEmpresa)
               ->where('cpe_c.ccanom','like','%'.$razsoc.'%')
               ->where('cpe_c.ccafem','>=',$fechaini)
               ->where('cpe_c.ccafem','<=',$fechafin)
               ->orwhere('cpe_c.IdEmpresa','=',$IdEmpresa)
               ->where('cpe_c.ccandi','like','%'.$razsoc.'%')
               ->where('cpe_c.ccafem','>=',$fechaini)
               ->where('cpe_c.ccafem','<=',$fechafin);

               $comprobantes = DB::tABLE('cpe_nota as cpe_n')
               ->select('cpe_n.ccafem','tdodes','cpe_n.serdoc','cpe_n.numdoc','tdides','cpe_c.ccandi','cpe_c.ccanom','monnom','cpe_n.ccaitv','cpe_c.IdEmpresa','cpe_n.tdocod','cpe_n.IdCpe_nota','cpe_c.ccanot','cpe_n.ccabaj','cpe_n.codhash','cpe_n.ccasunrescod')
               ->join('cpe_cabecera as cpe_c','cpe_n.IdCpe_cabecera','=','cpe_c.IdCpe_cabecera')
               ->join('tipo_documento as tip_d','cpe_n.tdocod','=','tip_d.tdocod')
               ->join('tipo_documento_identidad as tdi','cpe_c.tdicod','=','tdi.tdicod')
               ->join('moneda as mon','cpe_c.moncod','=','mon.moncod')
               ->where('cpe_c.IdEmpresa','=',$IdEmpresa)
               ->where('cpe_c.ccanom','like','%'.$razsoc.'%')
               ->where('cpe_n.ccafem','>=',$fechaini)
               ->where('cpe_n.ccafem','<=',$fechafin)
               ->orwhere('cpe_c.IdEmpresa','=',$IdEmpresa)
               ->where('cpe_c.ccandi','like','%'.$razsoc.'%')
               ->where('cpe_n.ccafem','>=',$fechaini)
               ->where('cpe_n.ccafem','<=',$fechafin)
               ->union($compcabecera)
                ->orderby('ccafem','desc')
               ->orderby('IdCpe_nota','desc')
               ->get();
              

           }elseif(!empty($comp)){
    

               $compcabecera = DB::tABLE('cpe_cabecera as cpe_c')->select('ccafem','tdodes','cpe_c.serdoc','cpe_c.numdoc','tdides','cpe_c.ccandi','cpe_c.ccanom','monnom','cpe_c.ccaitv','cpe_c.IdEmpresa','cpe_c.tdocod','cpe_c.IdCpe_cabecera','cpe_c.ccanot','cpe_c.ccabaj','cpe_c.codhash','cpe_c.ccasunrescod')
               ->join('tipo_documento as tip_d','cpe_c.tdocod','=','tip_d.tdocod')
               ->join('tipo_documento_identidad as tdi','cpe_c.tdicod','=','tdi.tdicod')
               ->join('moneda as mon','cpe_c.moncod','=','mon.moncod')
               ->where('cpe_c.IdEmpresa','=',$IdEmpresa)
               ->where('serdoc','=',$ser)
               ->where('numdoc','=',$num);

               $comprobantes = DB::tABLE('cpe_nota as cpe_n')
               ->select('cpe_n.ccafem','tdodes','cpe_n.serdoc','cpe_n.numdoc','tdides','cpe_c.ccandi','cpe_c.ccanom','monnom','cpe_n.ccaitv','cpe_c.IdEmpresa','cpe_n.tdocod','cpe_n.IdCpe_nota','cpe_c.ccanot','cpe_c.ccabaj','cpe_n.codhash','cpe_n.ccasunrescod')
                ->join('cpe_cabecera as cpe_c','cpe_n.IdCpe_cabecera','=','cpe_c.IdCpe_cabecera')
                ->join('tipo_documento as tip_d','cpe_n.tdocod','=','tip_d.tdocod')
               ->join('tipo_documento_identidad as tdi','cpe_c.tdicod','=','tdi.tdicod')
               ->join('moneda as mon','cpe_c.moncod','=','mon.moncod')
               ->where('cpe_c.IdEmpresa','=',$IdEmpresa)
               ->where('cpe_n.serdoc','=',$ser)
               ->where('cpe_n.numdoc','=',$num)
               ->union($compcabecera)
                ->orderby('ccafem','desc')
               ->orderby('IdCpe_nota','desc')
               ->get();
              


           }elseif($respse==1  && $tipdoc==0  && empty($serdoc) && empty($numdoc)){
           

              $compcabecera = DB::tABLE('cpe_cabecera as cpe_c')->select('ccafem','tdodes','cpe_c.serdoc','cpe_c.numdoc','tdides','cpe_c.ccandi','cpe_c.ccanom','monnom','cpe_c.ccaitv','cpe_c.IdEmpresa','cpe_c.tdocod','cpe_c.IdCpe_cabecera','cpe_c.ccanot','cpe_c.ccabaj','cpe_c.codhash','cpe_c.ccasunrescod')
              ->join('tipo_documento as tip_d','cpe_c.tdocod','=','tip_d.tdocod')
              ->join('tipo_documento_identidad as tdi','cpe_c.tdicod','=','tdi.tdicod')
              ->join('moneda as mon','cpe_c.moncod','=','mon.moncod')
              ->where('cpe_c.IdEmpresa','=',$IdEmpresa)
              ->where('cpe_c.ccanom','like','%'.$razsoc.'%')
              ->where('cpe_c.ccafem','>=',$fecin)
              ->where('cpe_c.ccafem','<=',$fecfin)
              ->orwhere('cpe_c.IdEmpresa','=',$IdEmpresa)
              ->where('cpe_c.ccandi','like','%'.$razsoc.'%') 
              ->where('cpe_c.ccafem','>=',$fecin)
              ->where('cpe_c.ccafem','<=',$fecfin);

              $comprobantes = DB::tABLE('cpe_nota as cpe_n')
               ->select('cpe_n.ccafem','tdodes','cpe_n.serdoc','cpe_n.numdoc','tdides','cpe_c.ccandi','cpe_c.ccanom','monnom','cpe_n.ccaitv','cpe_c.IdEmpresa','cpe_n.tdocod','cpe_n.IdCpe_nota','cpe_c.ccanot','cpe_c.ccabaj','cpe_n.codhash','cpe_n.ccasunrescod')
                ->join('cpe_cabecera as cpe_c','cpe_n.IdCpe_cabecera','=','cpe_c.IdCpe_cabecera')
                ->join('tipo_documento as tip_d','cpe_n.tdocod','=','tip_d.tdocod')
               ->join('tipo_documento_identidad as tdi','cpe_c.tdicod','=','tdi.tdicod')
               ->join('moneda as mon','cpe_c.moncod','=','mon.moncod')
               ->where('cpe_c.IdEmpresa','=',$IdEmpresa)
              ->where('cpe_c.ccanom','like','%'.$razsoc.'%')
              ->where('cpe_n.ccafem','>=',$fecin)
              ->where('cpe_n.ccafem','<=',$fecfin)
              ->orwhere('cpe_c.IdEmpresa','=',$IdEmpresa)
              ->where('cpe_c.ccandi','like','%'.$razsoc.'%') 
              ->where('cpe_n.ccafem','>=',$fecin)
              ->where('cpe_n.ccafem','<=',$fecfin)
              ->union($compcabecera)
               ->orderby('ccafem','desc')
               ->orderby('IdCpe_nota','desc')
              ->get();
              

           }elseif (empty($razsoc) && $respse==1  && empty($serdoc) && empty($numdoc)) {
         

              $compcabecera = DB::tABLE('cpe_cabecera as cpe_c')->select('ccafem','tdodes','cpe_c.serdoc','cpe_c.numdoc','tdides','cpe_c.ccandi','cpe_c.ccanom','monnom','cpe_c.ccaitv','cpe_c.IdEmpresa','cpe_c.tdocod','cpe_c.IdCpe_cabecera','cpe_c.ccanot','cpe_c.ccabaj','cpe_c.codhash','cpe_c.ccasunrescod')
              ->join('tipo_documento as tip_d','cpe_c.tdocod','=','tip_d.tdocod')
              ->join('tipo_documento_identidad as tdi','cpe_c.tdicod','=','tdi.tdicod')
              ->join('moneda as mon','cpe_c.moncod','=','mon.moncod')
             ->where('cpe_c.IdEmpresa','=',$IdEmpresa)
              ->where('cpe_c.tdocod','=',$tipdoc)
              ->where('cpe_c.ccafem','>=',$fecin)
              ->where('cpe_c.ccafem','<=',$fecfin);

              $comprobantes = DB::tABLE('cpe_nota as cpe_n')
              ->select('cpe_n.ccafem','tdodes','cpe_n.serdoc','cpe_n.numdoc','tdides','cpe_c.ccandi','cpe_c.ccanom','monnom','cpe_n.ccaitv','cpe_c.IdEmpresa','cpe_n.tdocod','cpe_n.IdCpe_nota','cpe_c.ccanot','cpe_c.ccabaj','cpe_n.codhash','cpe_n.ccasunrescod')
              ->join('cpe_cabecera as cpe_c','cpe_n.IdCpe_cabecera','=','cpe_c.IdCpe_cabecera')
              ->join('tipo_documento as tip_d','cpe_n.tdocod','=','tip_d.tdocod')
              ->join('tipo_documento_identidad as tdi','cpe_c.tdicod','=','tdi.tdicod')
              ->join('moneda as mon','cpe_c.moncod','=','mon.moncod')
              ->where('cpe_c.IdEmpresa','=',$IdEmpresa)
              ->where('cpe_n.tdocod','=',$tipdoc)
              ->where('cpe_n.ccafem','>=',$fecin)
              ->where('cpe_n.ccafem','<=',$fecfin)
              ->union($compcabecera)
               ->orderby('ccafem','desc')
               ->orderby('IdCpe_nota','desc')
              ->get();
              
           }elseif (empty($razsoc) && $tipdoc==0 && empty($serdoc) && empty($numdoc)) {
              if($respse=='2'){
                 $compcabecera = DB::tABLE('cpe_cabecera as cpe_c')->select('ccafem','tdodes','cpe_c.serdoc','cpe_c.numdoc','tdides','cpe_c.ccandi','cpe_c.ccanom','monnom','cpe_c.ccaitv','cpe_c.IdEmpresa','cpe_c.tdocod','cpe_c.IdCpe_cabecera','cpe_c.ccanot','cpe_c.ccabaj','cpe_c.codhash','cpe_c.ccasunrescod')
                ->join('tipo_documento as tip_d','cpe_c.tdocod','=','tip_d.tdocod')
                ->join('tipo_documento_identidad as tdi','cpe_c.tdicod','=','tdi.tdicod')
                ->join('moneda as mon','cpe_c.moncod','=','mon.moncod')
                ->where('cpe_c.IdEmpresa','=',$IdEmpresa)
                ->where('cpe_c.ccasunrescod','=',NULL)
                ->where('cpe_c.ccafem','>=',$fecin)
                ->where('cpe_c.ccafem','<=',$fecfin);

                 $comprobantes = DB::tABLE('cpe_nota as cpe_n')
                ->select('cpe_n.ccafem','tdodes','cpe_n.serdoc','cpe_n.numdoc','tdides','cpe_c.ccandi','cpe_c.ccanom','monnom','cpe_n.ccaitv','cpe_c.IdEmpresa','cpe_n.tdocod','cpe_n.IdCpe_nota','cpe_c.ccanot','cpe_c.ccabaj','cpe_n.codhash','cpe_n.ccasunrescod')
                ->join('cpe_cabecera as cpe_c','cpe_n.IdCpe_cabecera','=','cpe_c.IdCpe_cabecera')
                ->join('tipo_documento as tip_d','cpe_n.tdocod','=','tip_d.tdocod')
                ->join('tipo_documento_identidad as tdi','cpe_c.tdicod','=','tdi.tdicod')
                ->join('moneda as mon','cpe_c.moncod','=','mon.moncod')
                ->where('cpe_c.IdEmpresa','=',$IdEmpresa)
                ->where('cpe_n.ccasunrescod','=',NULL)
                ->where('cpe_n.ccafem','>=',$fecin)
                ->where('cpe_n.ccafem','<=',$fecfin)
                ->union($compcabecera)
                ->orderby('ccafem','desc')
                ->orderby('IdCpe_nota','desc')
                ->get();
              }else{
                $compcabecera = DB::tABLE('cpe_cabecera as cpe_c')->select('ccafem','tdodes','cpe_c.serdoc','cpe_c.numdoc','tdides','cpe_c.ccandi','cpe_c.ccanom','monnom','cpe_c.ccaitv','cpe_c.IdEmpresa','cpe_c.tdocod','cpe_c.IdCpe_cabecera','cpe_c.ccanot','cpe_c.ccabaj','cpe_c.codhash','cpe_c.ccasunrescod')
                ->join('tipo_documento as tip_d','cpe_c.tdocod','=','tip_d.tdocod')
                ->join('tipo_documento_identidad as tdi','cpe_c.tdicod','=','tdi.tdicod')
                ->join('moneda as mon','cpe_c.moncod','=','mon.moncod')
                ->where('cpe_c.IdEmpresa','=',$IdEmpresa)
                ->where('cpe_c.ccasunrescod','=',$respse)
                ->where('cpe_c.ccafem','>=',$fecin)
                ->where('cpe_c.ccafem','<=',$fecfin);

                 $comprobantes = DB::tABLE('cpe_nota as cpe_n')
                ->select('cpe_n.ccafem','tdodes','cpe_n.serdoc','cpe_n.numdoc','tdides','cpe_c.ccandi','cpe_c.ccanom','monnom','cpe_n.ccaitv','cpe_c.IdEmpresa','cpe_n.tdocod','cpe_n.IdCpe_nota','cpe_c.ccanot','cpe_c.ccabaj','cpe_n.codhash','cpe_n.ccasunrescod')
                ->join('cpe_cabecera as cpe_c','cpe_n.IdCpe_cabecera','=','cpe_c.IdCpe_cabecera')
                ->join('tipo_documento as tip_d','cpe_n.tdocod','=','tip_d.tdocod')
                ->join('tipo_documento_identidad as tdi','cpe_c.tdicod','=','tdi.tdicod')
                ->join('moneda as mon','cpe_c.moncod','=','mon.moncod')
                ->where('cpe_c.IdEmpresa','=',$IdEmpresa)
                ->where('cpe_n.ccasunrescod','=',$respse)
                ->where('cpe_n.ccafem','>=',$fecin)
                ->where('cpe_n.ccafem','<=',$fecfin)
                ->union($compcabecera)
                ->orderby('ccafem','desc')
                ->orderby('IdCpe_nota','desc')
                ->get();
              }
       
            }elseif (empty($serdoc) && empty($numdoc) && $respse!=1 && $tipdoc!=0) {
              if($respse=='2'){
                 $compcabecera = DB::tABLE('cpe_cabecera as cpe_c')->select('ccafem','tdodes','cpe_c.serdoc','cpe_c.numdoc','tdides','cpe_c.ccandi','cpe_c.ccanom','monnom','cpe_c.ccaitv','cpe_c.IdEmpresa','cpe_c.tdocod','cpe_c.IdCpe_cabecera','cpe_c.ccanot','cpe_c.ccabaj','cpe_c.codhash','cpe_c.ccasunrescod')
                ->join('tipo_documento as tip_d','cpe_c.tdocod','=','tip_d.tdocod')
                ->join('tipo_documento_identidad as tdi','cpe_c.tdicod','=','tdi.tdicod')
                ->join('moneda as mon','cpe_c.moncod','=','mon.moncod')
                ->where('cpe_c.ccanom','like','%'.$razsoc.'%')
                ->where('cpe_c.IdEmpresa','=',$IdEmpresa)
                ->where('cpe_c.tdocod','=',$tipdoc)
                ->where('cpe_c.ccasunrescod','=',NULL)
                ->where('cpe_c.ccafem','>=',$fecin)
                ->where('cpe_c.ccafem','<=',$fecfin)
                ->orwhere('ccandi','like','%'.$razsoc.'%')
                ->where('cpe_c.IdEmpresa','=',$IdEmpresa)
                ->where('cpe_c.tdocod','=',$tipdoc)
                ->where('cpe_c.ccasunrescod','=',NULL)
                ->where('cpe_c.ccafem','>=',$fecin)
                ->where('cpe_c.ccafem','<=',$fecfin);

                 $comprobantes = DB::tABLE('cpe_nota as cpe_n')
                ->select('cpe_n.ccafem','tdodes','cpe_n.serdoc','cpe_n.numdoc','tdides','cpe_c.ccandi','cpe_c.ccanom','monnom','cpe_n.ccaitv','cpe_c.IdEmpresa','cpe_n.tdocod','cpe_n.IdCpe_nota','cpe_c.ccanot','cpe_c.ccabaj','cpe_n.codhash','cpe_n.ccasunrescod')
                ->join('cpe_cabecera as cpe_c','cpe_n.IdCpe_cabecera','=','cpe_c.IdCpe_cabecera')
                ->join('tipo_documento as tip_d','cpe_n.tdocod','=','tip_d.tdocod')
                ->join('tipo_documento_identidad as tdi','cpe_c.tdicod','=','tdi.tdicod')
                ->join('moneda as mon','cpe_c.moncod','=','mon.moncod')
                ->where('cpe_c.ccanom','like','%'.$razsoc.'%')
                ->where('cpe_c.IdEmpresa','=',$IdEmpresa)
                ->where('cpe_n.ccasunrescod','=',NULL)
                ->where('cpe_n.tdocod','=',$tipdoc)
                ->where('cpe_n.ccafem','>=',$fecin)
                ->where('cpe_n.ccafem','<=',$fecfin)
                ->orwhere('ccandi','like','%'.$razsoc.'%')
                ->where('cpe_c.IdEmpresa','=',$IdEmpresa)
                ->where('cpe_n.ccasunrescod','=',NULL)
                ->where('cpe_n.tdocod','=',$tipdoc)
                ->where('cpe_n.ccafem','>=',$fecin)
                ->where('cpe_n.ccafem','<=',$fecfin)
                ->union($compcabecera)
                ->orderby('ccafem','desc')
                ->orderby('IdCpe_nota','desc')
                ->get();
              }else{
                $compcabecera = DB::tABLE('cpe_cabecera as cpe_c')->select('ccafem','tdodes','cpe_c.serdoc','cpe_c.numdoc','tdides','cpe_c.ccandi','cpe_c.ccanom','monnom','cpe_c.ccaitv','cpe_c.IdEmpresa','cpe_c.tdocod','cpe_c.IdCpe_cabecera','cpe_c.ccanot','cpe_c.ccabaj','cpe_c.codhash','cpe_c.ccasunrescod')
                ->join('tipo_documento as tip_d','cpe_c.tdocod','=','tip_d.tdocod')
                ->join('tipo_documento_identidad as tdi','cpe_c.tdicod','=','tdi.tdicod')
                ->join('moneda as mon','cpe_c.moncod','=','mon.moncod')
                ->where('cpe_c.ccanom','like','%'.$razsoc.'%')
                ->where('cpe_c.IdEmpresa','=',$IdEmpresa)
                ->where('cpe_c.tdocod','=',$tipdoc)
                ->where('cpe_c.ccasunrescod','=',$respse)
                ->where('cpe_c.ccafem','>=',$fecin)
                ->where('cpe_c.ccafem','<=',$fecfin)
                ->orwhere('ccandi','like','%'.$razsoc.'%')
                ->where('cpe_c.IdEmpresa','=',$IdEmpresa)
                ->where('cpe_c.tdocod','=',$tipdoc)
                ->where('cpe_c.ccasunrescod','=',$respse)
                ->where('cpe_c.ccafem','>=',$fecin)
                ->where('cpe_c.ccafem','<=',$fecfin);

                 $comprobantes = DB::tABLE('cpe_nota as cpe_n')
                ->select('cpe_n.ccafem','tdodes','cpe_n.serdoc','cpe_n.numdoc','tdides','cpe_c.ccandi','cpe_c.ccanom','monnom','cpe_n.ccaitv','cpe_c.IdEmpresa','cpe_n.tdocod','cpe_n.IdCpe_nota','cpe_c.ccanot','cpe_c.ccabaj','cpe_n.codhash','cpe_n.ccasunrescod')
                ->join('cpe_cabecera as cpe_c','cpe_n.IdCpe_cabecera','=','cpe_c.IdCpe_cabecera')
                ->join('tipo_documento as tip_d','cpe_n.tdocod','=','tip_d.tdocod')
                ->join('tipo_documento_identidad as tdi','cpe_c.tdicod','=','tdi.tdicod')
                ->join('moneda as mon','cpe_c.moncod','=','mon.moncod')
                ->where('cpe_c.ccanom','like','%'.$razsoc.'%')
                ->where('cpe_c.IdEmpresa','=',$IdEmpresa)
                ->where('cpe_n.ccasunrescod','=',$respse)
                ->where('cpe_n.tdocod','=',$tipdoc)
                ->where('cpe_n.ccafem','>=',$fecin)
                ->where('cpe_n.ccafem','<=',$fecfin)
                ->orwhere('ccandi','like','%'.$razsoc.'%')
                ->where('cpe_c.IdEmpresa','=',$IdEmpresa)
                ->where('cpe_n.ccasunrescod','=',$respse)
                ->where('cpe_n.tdocod','=',$tipdoc)
                ->where('cpe_n.ccafem','>=',$fecin)
                ->where('cpe_n.ccafem','<=',$fecfin)
                ->union($compcabecera)
                ->orderby('ccafem','desc')
                ->orderby('IdCpe_nota','desc')
                ->get();
              }
           }elseif (empty($razsoc)) {
              
              if($respse=='2'){
                $compcabecera = DB::tABLE('cpe_cabecera as cpe_c')->select('ccafem','tdodes','cpe_c.serdoc','cpe_c.numdoc','tdides','cpe_c.ccandi','cpe_c.ccanom','monnom','cpe_c.ccaitv','cpe_c.IdEmpresa','cpe_c.tdocod','cpe_c.IdCpe_cabecera','cpe_c.ccanot','cpe_c.ccabaj','cpe_c.codhash','cpe_c.ccasunrescod')
                ->join('tipo_documento as tip_d','cpe_c.tdocod','=','tip_d.tdocod')
                ->join('tipo_documento_identidad as tdi','cpe_c.tdicod','=','tdi.tdicod')
                ->join('moneda as mon','cpe_c.moncod','=','mon.moncod')
                ->where('cpe_c.IdEmpresa','=',$IdEmpresa)
                ->where('cpe_c.ccasunrescod','=',NULL)
                ->where('cpe_c.tdocod','=',$tipdoc)
                ->where('cpe_c.ccafem','>=',$fecin)
                ->where('cpe_c.ccafem','<=',$fecfin);

                 $comprobantes = DB::tABLE('cpe_nota as cpe_n')
                ->select('cpe_n.ccafem','tdodes','cpe_n.serdoc','cpe_n.numdoc','tdides','cpe_c.ccandi','cpe_c.ccanom','monnom','cpe_n.ccaitv','cpe_c.IdEmpresa','cpe_n.tdocod','cpe_n.IdCpe_nota','cpe_c.ccanot','cpe_c.ccabaj','cpe_n.codhash','cpe_n.ccasunrescod')
                ->join('cpe_cabecera as cpe_c','cpe_n.IdCpe_cabecera','=','cpe_c.IdCpe_cabecera')
                ->join('tipo_documento as tip_d','cpe_n.tdocod','=','tip_d.tdocod')
                ->join('tipo_documento_identidad as tdi','cpe_c.tdicod','=','tdi.tdicod')
                ->join('moneda as mon','cpe_c.moncod','=','mon.moncod')
                ->where('cpe_c.IdEmpresa','=',$IdEmpresa)
                ->where('cpe_n.ccasunrescod','=',NULL)
                ->where('cpe_n.tdocod','=',$tipdoc)
                ->where('cpe_n.ccafem','>=',$fecin)
                ->where('cpe_n.ccafem','<=',$fecfin)
                ->union($compcabecera)
                 ->orderby('ccafem','desc')
               ->orderby('IdCpe_nota','desc')
                ->get();
              }else{
                $compcabecera = DB::tABLE('cpe_cabecera as cpe_c')->select('ccafem','tdodes','cpe_c.serdoc','cpe_c.numdoc','tdides','cpe_c.ccandi','cpe_c.ccanom','monnom','cpe_c.ccaitv','cpe_c.IdEmpresa','cpe_c.tdocod','cpe_c.IdCpe_cabecera','cpe_c.ccanot','cpe_c.ccabaj','cpe_c.codhash','cpe_c.ccasunrescod')
                ->join('tipo_documento as tip_d','cpe_c.tdocod','=','tip_d.tdocod')
                ->join('tipo_documento_identidad as tdi','cpe_c.tdicod','=','tdi.tdicod')
                ->join('moneda as mon','cpe_c.moncod','=','mon.moncod')
                ->where('cpe_c.IdEmpresa','=',$IdEmpresa)
                ->where('cpe_c.ccasunrescod','=',$respse)
                ->where('cpe_c.tdocod','=',$tipdoc)
                ->where('cpe_c.ccafem','>=',$fecin)
                ->where('cpe_c.ccafem','<=',$fecfin);

                 $comprobantes = DB::tABLE('cpe_nota as cpe_n')
                ->select('cpe_n.ccafem','tdodes','cpe_n.serdoc','cpe_n.numdoc','tdides','cpe_c.ccandi','cpe_c.ccanom','monnom','cpe_n.ccaitv','cpe_c.IdEmpresa','cpe_n.tdocod','cpe_n.IdCpe_nota','cpe_c.ccanot','cpe_c.ccabaj','cpe_n.codhash','cpe_n.ccasunrescod')
                ->join('cpe_cabecera as cpe_c','cpe_n.IdCpe_cabecera','=','cpe_c.IdCpe_cabecera')
                ->join('tipo_documento as tip_d','cpe_n.tdocod','=','tip_d.tdocod')
                ->join('tipo_documento_identidad as tdi','cpe_c.tdicod','=','tdi.tdicod')
                ->join('moneda as mon','cpe_c.moncod','=','mon.moncod')
                ->where('cpe_c.IdEmpresa','=',$IdEmpresa)
                ->where('cpe_n.ccasunrescod','=',$respse)
                ->where('cpe_n.tdocod','=',$tipdoc)
                ->where('cpe_n.ccafem','>=',$fecin)
                ->where('cpe_n.ccafem','<=',$fecfin)
                ->union($compcabecera)
                 ->orderby('ccafem','desc')
               ->orderby('IdCpe_nota','desc')
                ->get();
              }
                
          
           }elseif ($tipdoc==0) {
           
              if($respse=='2'){
                $compcabecera = DB::tABLE('cpe_cabecera as cpe_c')->select('ccafem','tdodes','cpe_c.serdoc','cpe_c.numdoc','tdides','cpe_c.ccandi','cpe_c.ccanom','monnom','cpe_c.ccaitv','cpe_c.IdEmpresa','cpe_c.tdocod','cpe_c.IdCpe_cabecera','cpe_c.ccanot','cpe_c.ccabaj','cpe_c.codhash','cpe_c.ccasunrescod')
              ->join('tipo_documento as tip_d','cpe_c.tdocod','=','tip_d.tdocod')
              ->join('tipo_documento_identidad as tdi','cpe_c.tdicod','=','tdi.tdicod')
              ->join('moneda as mon','cpe_c.moncod','=','mon.moncod')
              ->where('cpe_c.IdEmpresa','=',$IdEmpresa)
              ->where('cpe_c.ccasunrescod','=',NULL)
              ->where('cpe_c.ccanom','like','%'.$razsoc.'%')
              ->where('cpe_c.ccafem','>=',$fecin)
              ->where('cpe_c.ccafem','<=',$fecfin);

              $comprobantes = DB::tABLE('cpe_nota as cpe_n')
              ->select('cpe_n.ccafem','tdodes','cpe_n.serdoc','cpe_n.numdoc','tdides','cpe_c.ccandi','cpe_c.ccanom','monnom','cpe_n.ccaitv','cpe_c.IdEmpresa','cpe_n.tdocod','cpe_n.IdCpe_nota','cpe_c.ccanot','cpe_c.ccabaj','cpe_n.codhash','cpe_n.ccasunrescod')
              ->join('cpe_cabecera as cpe_c','cpe_n.IdCpe_cabecera','=','cpe_c.IdCpe_cabecera')
              ->join('tipo_documento as tip_d','cpe_n.tdocod','=','tip_d.tdocod')
              ->join('tipo_documento_identidad as tdi','cpe_c.tdicod','=','tdi.tdicod')
              ->join('moneda as mon','cpe_c.moncod','=','mon.moncod')
              ->where('cpe_c.IdEmpresa','=',$IdEmpresa)
              ->where('cpe_n.ccasunrescod','=',NULL)
              ->where('cpe_c.ccanom','like','%'.$razsoc.'%')
              ->where('cpe_n.ccafem','>=',$fecin)
              ->where('cpe_n.ccafem','<=',$fecfin)
              ->union($compcabecera)
               ->orderby('ccafem','desc')
               ->orderby('IdCpe_nota','desc')
              ->get();
              }else{
                $compcabecera = DB::tABLE('cpe_cabecera as cpe_c')->select('ccafem','tdodes','cpe_c.serdoc','cpe_c.numdoc','tdides','cpe_c.ccandi','cpe_c.ccanom','monnom','cpe_c.ccaitv','cpe_c.IdEmpresa','cpe_c.tdocod','cpe_c.IdCpe_cabecera','cpe_c.ccanot','cpe_c.ccabaj','cpe_c.codhash','cpe_c.ccasunrescod')
              ->join('tipo_documento as tip_d','cpe_c.tdocod','=','tip_d.tdocod')
              ->join('tipo_documento_identidad as tdi','cpe_c.tdicod','=','tdi.tdicod')
              ->join('moneda as mon','cpe_c.moncod','=','mon.moncod')
              ->where('cpe_c.IdEmpresa','=',$IdEmpresa)
              ->where('cpe_c.ccasunrescod','=',$respse)
              ->where('cpe_c.ccanom','like','%'.$razsoc.'%')
              ->where('cpe_c.ccafem','>=',$fecin)
              ->where('cpe_c.ccafem','<=',$fecfin)
              ->orwhere('cpe_c.IdEmpresa','=',$IdEmpresa)
              ->where('cpe_c.ccasunrescod','=',$respse)
              ->where('cpe_c.ccandi','like','%'.$razsoc.'%')
              ->where('cpe_c.ccafem','>=',$fecin)
              ->where('cpe_c.ccafem','<=',$fecfin);

              $comprobantes = DB::tABLE('cpe_nota as cpe_n')
              ->select('cpe_n.ccafem','tdodes','cpe_n.serdoc','cpe_n.numdoc','tdides','cpe_c.ccandi','cpe_c.ccanom','monnom','cpe_n.ccaitv','cpe_c.IdEmpresa','cpe_n.tdocod','cpe_n.IdCpe_nota','cpe_c.ccanot','cpe_c.ccabaj','cpe_n.codhash','cpe_n.ccasunrescod')
              ->join('cpe_cabecera as cpe_c','cpe_n.IdCpe_cabecera','=','cpe_c.IdCpe_cabecera')
              ->join('tipo_documento as tip_d','cpe_n.tdocod','=','tip_d.tdocod')
              ->join('tipo_documento_identidad as tdi','cpe_c.tdicod','=','tdi.tdicod')
              ->join('moneda as mon','cpe_c.moncod','=','mon.moncod')
              ->where('cpe_c.IdEmpresa','=',$IdEmpresa)
              ->where('cpe_n.ccasunrescod','=',$respse)
              ->where('cpe_c.ccanom','like','%'.$razsoc.'%')
              ->where('cpe_n.ccafem','>=',$fecin)
              ->where('cpe_n.ccafem','<=',$fecfin)
              ->orwhere('cpe_c.IdEmpresa','=',$IdEmpresa)
              ->where('cpe_n.ccasunrescod','=',$respse)
              ->where('cpe_c.ccandi','like','%'.$razsoc.'%')
              ->where('cpe_n.ccafem','>=',$fecin)
              ->where('cpe_n.ccafem','<=',$fecfin)
              ->union($compcabecera)
               ->orderby('ccafem','desc')
               ->orderby('IdCpe_nota','desc')
              ->get();  
              }
              
            
           }elseif ($respse==1) {
         
              $compcabecera = DB::tABLE('cpe_cabecera as cpe_c')->select('ccafem','tdodes','cpe_c.serdoc','cpe_c.numdoc','tdides','cpe_c.ccandi','cpe_c.ccanom','monnom','cpe_c.ccaitv','cpe_c.IdEmpresa','cpe_c.tdocod','cpe_c.IdCpe_cabecera','cpe_c.ccanot','cpe_c.ccabaj','cpe_c.codhash','cpe_c.ccasunrescod')
              ->join('tipo_documento as tip_d','cpe_c.tdocod','=','tip_d.tdocod')
              ->join('tipo_documento_identidad as tdi','cpe_c.tdicod','=','tdi.tdicod')
              ->join('moneda as mon','cpe_c.moncod','=','mon.moncod')
              ->where('cpe_c.IdEmpresa','=',$IdEmpresa)
              ->where('cpe_c.tdocod','=',$tipdoc)
              ->where('cpe_c.ccanom','like','%'.$razsoc.'%')
              ->where('cpe_c.ccafem','>=',$fecin)
              ->where('cpe_c.ccafem','<=',$fecfin)
              ->orwhere('cpe_c.IdEmpresa','=',$IdEmpresa)
              ->where('cpe_c.tdocod','=',$tipdoc)
              ->where('cpe_c.ccandi','like','%'.$razsoc.'%') 
              ->where('cpe_c.ccafem','>=',$fecin)
              ->where('cpe_c.ccafem','<=',$fecfin);

              $comprobantes = DB::tABLE('cpe_nota as cpe_n')
              ->select('cpe_n.ccafem','tdodes','cpe_n.serdoc','cpe_n.numdoc','tdides','cpe_c.ccandi','cpe_c.ccanom','monnom','cpe_n.ccaitv','cpe_c.IdEmpresa','cpe_n.tdocod','cpe_n.IdCpe_nota','cpe_c.ccanot','cpe_c.ccabaj','cpe_n.codhash','cpe_n.ccasunrescod')
              ->join('cpe_cabecera as cpe_c','cpe_n.IdCpe_cabecera','=','cpe_c.IdCpe_cabecera')
              ->join('tipo_documento as tip_d','cpe_n.tdocod','=','tip_d.tdocod')
              ->join('tipo_documento_identidad as tdi','cpe_c.tdicod','=','tdi.tdicod')
              ->join('moneda as mon','cpe_c.moncod','=','mon.moncod')
              ->where('cpe_c.IdEmpresa','=',$IdEmpresa)
              ->where('cpe_n.tdocod','=',$tipdoc)
              ->where('cpe_c.ccanom','like','%'.$razsoc.'%')
              ->where('cpe_n.ccafem','>=',$fecin)
              ->where('cpe_n.ccafem','<=',$fecfin)
              ->orwhere('cpe_c.IdEmpresa','=',$IdEmpresa)
              ->where('cpe_n.tdocod','=',$tipdoc)
              ->where('cpe_c.ccandi','like','%'.$razsoc.'%') 
              ->where('cpe_n.ccafem','>=',$fecin)
              ->where('cpe_n.ccafem','<=',$fecfin)
              ->union($compcabecera)
              ->orderby('ccafem','desc')
              ->orderby('IdCpe_nota','desc')
              ->get();
            }
        
            session(['nomLogo' => $empresa->LogEmpresa]);
            session(['NomEmpresa' => $empresa->NomEmpresa]);
              
          
            return view('empresas.comprobantes.index',['comprobantes'=>$comprobantes,'empresa'=>$empresa,'doccomprobante'=>$doccomprobante]);

        
         
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($tdocod,$cpe=0)
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
        $rucemp = trim(Auth::user()->IdEmpresa);

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
            return view('empresas.comprobantes.nuevafactura',compact('igv','monedas','unidades','operaciones','docidentidad','clientes','fecha','senudoc','tdocod','productos','doccomprobante','cpe'));
        }elseif($tdocod=='03'){
             return view('empresas.comprobantes.nuevaboleta',compact('igv','monedas','unidades','operaciones','docidentidad','clientes','fecha','senudoc','tdocod','productos','doccomprobante','cpe'));
        }elseif($tdocod=='07'){
             return view('empresas.comprobantes.nuevanota',compact('igv','monedas','unidades','operaciones','docidentidad','clientes','fecha','tdocod','productos','doccomprobante','nota','ncdcod','cpe'));
        }elseif($tdocod=='08'){
             return view('empresas.comprobantes.nuevanota',compact('igv','monedas','unidades','operaciones','docidentidad','clientes','fecha','tdocod','productos','doccomprobante','nota','ncdcod','cpe'));    
        }   
    }

    public function crearboleta($tdocod,$cpe=0)
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
        $rucemp = trim(Auth::user()->IdEmpresa);

        // consultar los clientes que le pertenece a la empresa
        $clientes= DB::tABLE('cliente')->where('rucemp','=',$rucemp)->where('cliest','=','Activo')
        ->orderby('clinom','asc')->get();

        //consultar productos que le pertenece a la empresa
        $productos= DB::tABLE('productos')->where('IdEmpresa','=',$rucemp)->where('proest','=','Activo')
        ->orderby('pronom','asc')->get();

        // consultar tipos de monedas
        $monedas = DB::tABLE('moneda')->where('monest','=','Activo')->orderby('moncod','asc')->get();

        // consultar la serie y numero de factura

         $senudoc = DB::tABLE('empresa')->select('BseEmpresa','BnuEmpresa')->where('IdEmpresa','=',$rucemp)->first(); 
      

        $fecha = now()->format('m/d/Y');
        //return $senudoc;
             return view('empresas.comprobantes.boletadetalle',compact('igv','monedas','unidades','operaciones','docidentidad','clientes','fecha','senudoc','tdocod','productos','doccomprobante','cpe'));
        
       
    }
    
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

      // Guardar en variables los datos que se enviaron a través del formulario
        $tdicod = $request->get('tdicod');
        $cliruc = $request->get('clinum');
        $numcomp= $request->get('numdoc');
        $sercomp= $request->get('serdoc');
        $topcod = $request->get('topcod');
        $clinom = $request->get('clinom');
        $fecemi = $request->get('fecEmi');
        $fecven = $request->get('fecVen');
        $clidir = $request->get('clidir');
        $clicor = $request->get('clicor');
        $mondoc = $request->get('mondoc');
        $descglb = $request->get('totdesc');
        $exon = $request->get('exon');
        $inaf = $request->get('inaf');
        $grav = $request->get('grav');
        $igv = $request->get('igv');
        $isc = $request->get('isc');
        $grat = $request->get('grat');
        $otrosc = $request->get('otrosc');
        $otros = $request->get('otros');
        $total = $request->get('total');
        $tdocod = $request->get('txt_tdocod');
        $obser = $request->get('obser');
        $tipcambio = $request->get('camdoc');
        $cantidades = $request->get('cant');
        $unidades = $request->get('unid');
        $codpro = $request->get('codpro');
        $detpro = $request->get('detpro');
        $vunit = $request->get('vunit');
        $vigv = $request->get('vigv');
        $tigv = $request->get('tigv');
        $vsub = $request->get('vsub');
        $vtot = $request->get('vtot');
        $rucemp = trim(Auth::user()->IdEmpresa);
        $numdoc = str_pad($request->get('numdoc'),8,"0", STR_PAD_LEFT);
        $exp=$request->get('exp');
        $puni = $request->get('preuni');

        $moneda = DB::tABLE('moneda')->where('moncod','=',$mondoc)->first();


      //Registrar el tipo de cambio enviado a través del formulario si no existe.
        if($mondoc !='1'){
          $camdoc = $request->get('camdoc');
          $tipcam = tipocambio::FirstOrCreate(['FecTipCambio'=>$fecemi],['CamCompra'=>$camdoc]);
        }else{
          $camdoc=0;
        }
        
      //Registrar el cliente enviado a través del formulario si no existe
        $cliente = Cliente::FirstOrCreate(['clinum'=>$cliruc],['clinom'=>$clinom,'clidir'=>$clidir,'clicor'=>$clicor,'rucemp'=>$rucemp,'tdicod'=>$tdicod]);
        
      //Crear un objeto de la cabecera del comprobante para registrar los datos de la cabecera
        $cabecera = new cpe_cabecera;
        $cabecera->tdocod = $request->get('tdocod');
        $cabecera->topcod = $request->get('topcod');
        $cabecera->ccafem = $request->get('fecEmi');
        $cabecera->ccafve = $request->get('fecVen');
        $cabecera->ccaobs = $request->get('obser');
        //$cabecera->ccacde = $request->get();
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
        $cabecera->numdoc = $numdoc;
        $cabecera->codunique = Auth::user()->IdEmpresa.''.$request->get('tdocod').''.$request->get('serdoc').''.$request->get('numdoc');
        $cabecera->IdUsuario = Auth::user()->IdUsuario;
        $cabecera->IdEmpresa = Auth::user()->IdEmpresa;
        //$cabecera->save();


        $empresa = Empresa::findOrFail($rucemp);
        if($tdocod=='01'){
          if( $empresa->FnuEmpresa == $numcomp){
              $modnumcomp = $numcomp+1;
          }else{
              $modnumcomp = $numcomp;
          }

          $empresa->FseEmpresa = $sercomp;
          $empresa->FnuEmpresa = $modnumcomp;
          $empresa->update();

          $numdoc = str_pad($modnumcomp,8,"0", STR_PAD_LEFT);
          $cabecera->serdoc= $sercomp;
          $cabecera->numdoc = $numdoc;
          $cabecera->save();
        }elseif($tdocod=='03'){
          if( $empresa->FnuEmpresa == $numcomp){
              $modnumcomp = $numcomp+1;
          }else{
              $modnumcomp = $numcomp;
          }

          $empresa->BseEmpresa = $sercomp;
          $empresa->BnuEmpresa = $modnumcomp;
          $empresa->update();

          $numdoc = str_pad($modnumcomp,8,"0", STR_PAD_LEFT);
          $cabecera->serdoc= $sercomp;
          $cabecera->numdoc = $numdoc;
          $cabecera->save();
        }
        //Ruta donde se guardarán los archivos cab y det.
        $raiz = '/opt/fs/'.$cabecera->IdEmpresa.'/sunat_archivos/sfs/DATA/';
        
     
         
        //Registrar los documentos relacionados del comprobante
        $docrel = $request->get('tdr');
        $docrser = $request->get('tdrser');
        $docrnum = $request->get('tdrnum');

        if(!empty($docrel) && !empty($docrser) && !empty($docrnum)){
          foreach( $docrel as $index => $tdr ) 
          {
            if(!empty($docrel[$index]) && !empty($docrser[$index]) && !empty($docrnum[$index])){
               $docrenum = str_pad($docrnum[$index],8,"0", STR_PAD_LEFT);
               $drsernum = "$docrser[$index]-$docrnum[$index]";
               
               $guia = DB::table("documento_relacionado")->where('tdocod','=',$docrel[$index])->where('dorser','=',$docrser[$index])->where('dornum','=',$docrenum)->first();

               if($guia==''){

                    $docurela[] = array( "tipoDocRelacionado"=> $docrel[$index],
                    "numeroDocRelacionado"=> $drsernum
                    );
               } 
            }
          }

        }else{
          $docurela[] = array( "tipoDocRelacionado"=> "",
                    "numeroDocRelacionado"=> ""
                    );
        }
   
        $totalletras= MontoLetras::convertir(number_format($cabecera->ccaitv,'2','.',''),$moneda->monnom,'Centimos');

        $cabfile =  $cabecera->IdEmpresa.'-'.$cabecera->tdocod.'-'.$cabecera->serdoc.'-'.$numdoc.'.json'; 

        $datemp = DB::table("empresa")->where("IdEmpresa","=",$rucemp)->first();

        if($tdocod=='01'){
           $nomcomp = "factura";
        }elseif($tdocod=='03'){
           $nomcomp = "boleta";
        }

    $xml = new DomDocument('1.0', 'ISO-8859-1');
    $xml->standalone         = false;
    $xml->preserveWhiteSpace    = false;
    $Invoice = $xml->createElement('Invoice');
    $Invoice = $xml->appendChild($Invoice);
    // Set the attributes.
    $Invoice->setAttribute('xmlns', 'urn:oasis:names:specification:ubl:schema:xsd:Invoice-2');
    $Invoice->setAttribute('xmlns:cac', 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2');
    $Invoice->setAttribute('xmlns:cbc', 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
    $Invoice->setAttribute('xmlns:ccts', "urn:un:unece:uncefact:documentation:2");
    $Invoice->setAttribute('xmlns:ds', "http://www.w3.org/2000/09/xmldsig#");
    $Invoice->setAttribute('xmlns:ext', "urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2");
    $Invoice->setAttribute('xmlns:qdt', "urn:oasis:names:specification:ubl:schema:xsd:QualifiedDatatypes-2");
    $Invoice->setAttribute('xmlns:sac', "urn:sunat:names:specification:ubl:peru:schema:xsd:SunatAggregateComponents-1");
    $Invoice->setAttribute('xmlns:udt', "urn:un:unece:uncefact:data:specification:UnqualifiedDataTypesSchemaModule:2");
 

    $UBLExtension = $xml->createElement('ext:UBLExtensions');
    $UBLExtension = $Invoice->appendChild($UBLExtension);
    $ext = $xml->createElement('ext:UBLExtension');
    $ext = $UBLExtension->appendChild($ext);
    $contents = $xml->createElement('ext:ExtensionContent');
    $contents = $ext->appendChild($contents);
    $sac = $xml->createElement('sac:AdditionalInformation');
    $sac = $contents->appendChild($sac);
    $monetary = $xml->createElement('sac:AdditionalMonetaryTotal');
    $monetary = $sac->appendChild($monetary);

    $cbc = $xml->createElement('cbc:ID', '2005'); 
    $cbc = $monetary->appendChild($cbc);

    $cbc = $xml->createElement('cbc:PayableAmount', $descglb); 
    $cbc = $monetary->appendChild($cbc); 
    $cbc->setAttribute('currencyID',  $mondoc);
    
    // el 1001 total velor venta - operaciones gravadas
    $monetary = $xml->createElement('sac:AdditionalMonetaryTotal'); 
    $monetary = $sac->appendChild($monetary);
    $cbc = $xml->createElement('cbc:ID', '1001'); 
    $cbc = $monetary->appendChild($cbc);
    $cbc = $xml->createElement('cbc:PayableAmount', $grav); 
    $cbc = $monetary->appendChild($cbc); $cbc->setAttribute('currencyID',  $mondoc);
    
    // el 1002 total valor venta - operaciones inafectas
    $monetary = $xml->createElement('sac:AdditionalMonetaryTotal'); 
    $monetary = $sac->appendChild($monetary);
    $cbc = $xml->createElement('cbc:ID', '1002'); 
    $cbc = $monetary->appendChild($cbc);
    $cbc = $xml->createElement('cbc:PayableAmount', $inaf); 
    $cbc = $monetary->appendChild($cbc); $cbc->setAttribute('currencyID',  $mondoc);
    
    // el 1003 total valor venta - operaciones exoneradas
    $monetary = $xml->createElement('sac:AdditionalMonetaryTotal'); 
    $monetary = $sac->appendChild($monetary);
    $cbc = $xml->createElement('cbc:ID', '1003');
    $cbc = $monetary->appendChild($cbc);
    $cbc = $xml->createElement('cbc:PayableAmount', $exon); 
    $cbc = $monetary->appendChild($cbc); $cbc->setAttribute('currencyID',  $mondoc);

    // el 1000 total valor venta - operaciones exportadas
    $monetary = $xml->createElement('sac:AdditionalMonetaryTotal'); 
    $monetary = $sac->appendChild($monetary);
    $cbc = $xml->createElement('cbc:ID', '1000');
    $cbc = $monetary->appendChild($cbc);
    $cbc = $xml->createElement('cbc:PayableAmount', $exp); 
    $cbc = $monetary->appendChild($cbc); $cbc->setAttribute('currencyID',  $mondoc);

    // el 1004 total valor venta - operaciones gratuitas
    $monetary = $xml->createElement('sac:AdditionalMonetaryTotal'); 
    $monetary = $sac->appendChild($monetary);
    $cbc = $xml->createElement('cbc:ID', '1004');
    $cbc = $monetary->appendChild($cbc);
    $cbc = $xml->createElement('cbc:PayableAmount', $grat); 
    $cbc = $monetary->appendChild($cbc); $cbc->setAttribute('currencyID',  $mondoc);
    
    //31.- leyendas - esta en el catalogo 15 sunat
    $aditional = $xml->createElement('sac:AdditionalProperty'); 
    $aditional = $sac->appendChild($aditional);
    $cbc = $xml->createElement('cbc:ID', '1000'); 
    $cbc = $aditional->appendChild($cbc);
    $cbc = $xml->createElement('cbc:Value', $totalletras); 
    $cbc = $aditional->appendChild($cbc);
               
    $cbc = $xml->createElement('cbc:ProfileID'); 
    $cbc = $invoice->appendChild($cbc);
    $cbc->setAttribute('schemeName','SUNAT:Identificador de Tipo de Operación');
    $cbc->setAttribute('schemeAgencyName','PE:SUNAT');
    $cbc->setAttribute('schemeURI','urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo17');
   
    
      // 2.- Firma electronica
    $ext = $xml->createElement('ext:UBLExtension'); 
    $ext = $UBLExtension->appendChild($ext);
    $contents = $xml->createElement('ext:ExtensionContent', ' '); 
    $contents = $ext->appendChild($contents);


    // 36. Version del UBL
    $cbc = $xml->createElement('cbc:UBLVersionID', '2.1'); 
    $cbc = $Invoice->appendChild($cbc);
    // 37.- Version de la estructura del documento
    $cbc = $xml->createElement('cbc:CustomizationID', '2.0'); 
    $cbc = $Invoice->appendChild($cbc);
    // 8.- Numeracion , conformada por serie y numero correlativo B001-00012926
    $cbc = $xml->createElement('cbc:ID', $sercomp.'-'.$numdoc); 
    $cbc = $Invoice->appendChild($cbc);
    // 1.- Fecha de emision 2017-04-13
    $cbc = $xml->createElement('cbc:IssueDate', $fecemi); 
    $cbc = $Invoice->appendChild($cbc);
    $cbc = $xml->createElement('cbc:IssueTime','14:12:12'); 
    $cbc = $Invoice->appendChild($cbc);
    $cbc = $xml->createElement('cbc:DueDate', $fecemi); 
    $cbc = $Invoice->appendChild($cbc);
    // 7.- Tipo de Documento 01 Factura 03 Boleta 07 Nota credito - catalogo numero 06
    $cbc = $xml->createElement('cbc:InvoiceTypeCode', $tdocod); 
    $cbc = $Invoice->appendChild($cbc);
    $cbc->setAttribute('listAgencyName','PE:SUNAT');
    $cbc->setAttribute('listName','SUNAT:Identificador de Tipo de Documento');
    $cbc->setAttribute('listURI','urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo01');
    // 28.- Tipo de moneda en la cual se emite la factura electronica
    $cbc = $xml->createElement('cbc:DocumentCurrencyCode',  $mondoc); 
    $cbc = $Invoice->appendChild($cbc);
    $cbc->setAttribute('listID','ISO 4217 Alpha');
    $cbc->setAttribute('listName','Currency');
    $cbc->setAttribute('listAgencyName','United Nations Economic Commission for Europe');

    // 2.- Parte de la firma electronica. esto es de quien creo la firma electronica
    $cac_signature = $xml->createElement('cac:Signature'); 
    $cac = $Invoice->appendChild($cac_signature);

    $cbc = $xml->createElement('cbc:ID',$rucemp); 
    $cbc = $cac_signature->appendChild($cbc);
  
    $cac_signatory = $xml->createElement('cac:SignatoryParty');
    $cac_signatory = $cac_signature->appendChild($cac_signatory);
    $cac = $xml->createElement('cac:PartyIdentification'); 
    $cac = $cac_signatory->appendChild($cac);
    $cbc = $xml->createElement('cbc:ID', $rucemp); 
    $cbc = $cac->appendChild($cbc);
    $cac = $xml->createElement('cac:PartyName');
    $cac = $cac_signatory->appendChild($cac);

    $cbc = $xml->createElement('cbc:Name', $empresa->NomEmpresa); 
    $cbc = $cac->appendChild($cbc);
  
    $cac_digital = $xml->createElement('cac:DigitalSignatureAttachment'); 
    $cac_digital = $cac_signature->appendChild($cac_digital);
    $cac = $xml->createElement('cac:ExternalReference'); 
    $cac = $cac_digital->appendChild($cac);
    $cbc = $xml->createElement('cbc:URI', $rucemp); 
    $cbc = $cac->appendChild($cbc); 


    // 3.- Apellidos y nombres, denominacion o razon social (DATOS DEL PROVEEDOR)
    // 4.- Nombre Comercial
    // 5.- Domicilio fiscal
    // 6.- Numero RUC
    $cac_accounting = $xml->createElement('cac:AccountingSupplierParty'); 
    $cac_accounting = $Invoice->appendChild($cac_accounting);
    $cac_party = $xml->createElement('cac:Party'); 
    $cac_party = $cac_accounting->appendChild($cac_party);
    $cac = $xml->createElement('cac:PartyName'); 
    $cac = $cac_party->appendChild($cac);
    $cbc = $xml->createElement('cbc:Name', $empresa->NomEmpresa); 
    $cbc = $cac->appendChild($cbc);

    $PartyTaxScheme = $xml->createElement('cac:PartyTaxScheme'); 
    $PartyTaxScheme = $cac_party->appendChild($PartyTaxScheme);
    $cbc = $xml->createElement('cbc:RegistrationName', $empresa->NomEmpresa); 
    $cbc = $cac->appendChild($cbc);
    $cbc = $xml->createElement('CompanyID',$rucemp); 
    $cbc = $cac->appendChild($cbc);
    $cbc->setAttribute('schemeID','6');
    $cbc->setAttribute('schemeName','"SUNAT:Identificador de Documento de Identidad');
    $cbc->setAttribute('schemeAgencyName','PE:SUNAT');
    $cbc->setAttribute('schemeURI','urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo06');

    $TaxScheme = $xml->createElement('cac:TaxScheme'); 
    $TaxScheme = $PartyTaxScheme->appendChild($TaxScheme);
    $cbc = $xml->createElement('cbc:ID'); 
    $cbc = $TaxScheme->appendChild($cbc);

    $RegistrationAddress = $xml->createElement('cac:RegistrationAddress'); 
    $RegistrationAddress = $PartyTaxScheme->appendChild($RegistrationAddress);
    $cbc = $xml->createElement('cbc:AddressTypeCode','0000'); 
    $cbc = $RegistrationAddress->appendChild($cbc);


    $cac_accounting = $xml->createElement('cac:AccountingCustomerParty'); 
    $cac_accounting = $Invoice->appendChild($cac_accounting);
    $cac_party = $xml->createElement('cac:Party'); 
    $cac_party = $cac_accounting->appendChild($cac_party);

    $PartyTaxScheme = $xml->createElement('cac:PartyTaxScheme'); 
    $PartyTaxScheme = $cac_party->appendChild($PartyTaxScheme);
    $cbc = $xml->createElement('cbc:RegistrationName', $empresa->NomEmpresa); 
    $cbc = $cac->appendChild($cbc);
    $cbc = $xml->createElement('CompanyID',$rucemp); 
    $cbc = $cac->appendChild($cbc);
    $cbc->setAttribute('schemeID',$tdicod);
    $cbc->setAttribute('schemeName','"SUNAT:Identificador de Documento de Identidad');
    $cbc->setAttribute('schemeAgencyName','PE:SUNAT');
    $cbc->setAttribute('schemeURI','urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo06');

    $TaxScheme = $xml->createElement('cac:TaxScheme'); 
    $TaxScheme = $PartyTaxScheme->appendChild($TaxScheme);
    $cbc = $xml->createElement('cbc:ID'); 
    $cbc = $TaxScheme->appendChild($cbc);

   





    $cac_accounting = $xml->createElement('cac:AccountingCustomerParty'); 
    $cac_accounting = $Invoice->appendChild($cac_accounting);
    $cbc = $xml->createElement('cbc:CustomerAssignedAccountID',$cliruc); 
    $cbc = $cac_accounting->appendChild($cbc);
    $cbc = $xml->createElement('cbc:AdditionalAccountID','6'); 
    $cbc = $cac_accounting->appendChild($cbc);
    $cac_party = $xml->createElement('cac:Party'); 
    $cac_party = $cac_accounting->appendChild($cac_party);
    $legal = $xml->createElement('cac:PartyLegalEntity'); 
    $legal = $cac_party->appendChild($legal);
    $cbc = $xml->createElement('cbc:RegistrationName', $clinom); 
    $cbc = $legal->appendChild($cbc);
    // no tiene numero o no esta registrado
    $seller = $xml->createElement('cac:SellerSupplierParty'); 
    $seller = $Invoice->appendChild($seller);
    $cac_party = $xml->createElement('cac:Party'); 
    $cac_party = $seller->appendChild($cac_party);
    $address = $xml->createElement('cac:PostalAddress');
    $address = $cac_party->appendChild($address);
    $cbc = $xml->createElement('cbc:AddressTypeCode', '0'); 
    $cbc = $address->appendChild($cbc);
   
    // 22.- Sumatoria IGV
    // 23.- Sumatoria ISC
    // 24.- Sumatoria otros tributos
    $taxtotal = $xml->createElement('cac:TaxTotal'); 
    $taxtotal = $Invoice->appendChild($taxtotal);
    $cbc = $xml->createElement('cbc:TaxAmount', $igv); 
    $cbc = $taxtotal->appendChild($cbc); 
    $cbc->setAttribute('currencyID',  $mondoc);
    $taxtsubtotal = $xml->createElement('cac:TaxSubtotal'); 
    $taxtsubtotal = $taxtotal->appendChild($taxtsubtotal);
    $cbc = $xml->createElement('cbc:TaxAmount', $igv); 
    $cbc = $taxtsubtotal->appendChild($cbc); 
    $cbc->setAttribute('currencyID',  $mondoc);
    $taxtcategory = $xml->createElement('cac:TaxCategory'); 
    $taxtcategory = $taxtsubtotal->appendChild($taxtcategory);
    $taxscheme = $xml->createElement('cac:TaxScheme'); 
    $taxscheme = $taxtcategory->appendChild($taxscheme);
    $cbc = $xml->createElement('cbc:ID', '1000'); 
    $cbc = $taxscheme->appendChild($cbc);
    $cbc = $xml->createElement('cbc:Name', 'IGV'); 
    $cbc = $taxscheme->appendChild($cbc);
    $cbc = $xml->createElement('cbc:TaxTypeCode', 'VAT'); 
    $cbc = $taxscheme->appendChild($cbc);

    // 25.- Sumatoria otros cargos
    $legal = $xml->createElement('cac:LegalMonetaryTotal'); 
    $legal = $Invoice->appendChild($legal);
    $cbc = $xml->createElement('cbc:AllowanceTotalAmount',$descglb); 
    $cbc = $legal->appendChild($cbc); 
    $cbc->setAttribute('currencyID', $mondoc);
    $cbc = $xml->createElement('cbc:ChargeTotalAmount',$otrosc); 
    $cbc = $legal->appendChild($cbc); 
    $cbc->setAttribute('currencyID',  $mondoc);
    $cbc = $xml->createElement('cbc:PayableAmount',$total); 
    $cbc = $legal->appendChild($cbc); 
    $cbc->setAttribute('currencyID',$mondoc);
   
    // detalle de la factura

      $i=0;
        foreach( $unidades as $index => $ume ) {
            $i=$i+1;
            //$imod=str_pad($i,3,"0", STR_PAD_LEFT);
            
            $detalle = new cpe_detalle;
            $detalle->IdCpe_cabecera =  $cabecera->IdCpe_cabecera; 
            $detalle->umecod = $ume;
            $detalle->cdecan = $cantidades[$index];
            //$pos = strpos($codpro[$index],'|');
            //$codproducto = substr($codpro[$index], 0, $pos);
            $codproducto = $codpro[$index];
            $detalle->procod = $codproducto;
            $detalle->cdepsu = $codproducto;
            $detalle->cdedes = $detpro[$index];
            $detalle->cdevun = $vunit[$index];
            $detalle->cdeigv = $vigv[$index];
            $detalle->tigcod = $tigv[$index];
            $detalle->cdepve = $vsub[$index];
            $detalle->cdevve = $vtot[$index];
            $detalle->cdepuni = $puni[$index];
            
            
            if($detalle->tigcod=='10'){
              $tiptri='1000'; //IGV Impuesto Generarl a las ventas
            }elseif($detalle->tigcod=='20'){
               $tiptri='9997'; //exonerado
            }elseif($detalle->tigcod=='30'){
              $tiptri ='9998'; //inafecto
            }elseif($detalle->tigcod=='17'){
              $tiptri ='1016'; //Impuesto a la venta de arroz pilado
            }elseif($detalle->tigcod=='40'){
              $tiptri ='9995'; //exportación
            }else{
              $tiptri='9996'; //Gratutito
            }

            $codumecin = unidad_medida::findOrFail($ume);
         
            $detalle->tiptri = $tiptri;
            $detalle->save();
            
            $InvoiceLine = $xml->createElement('cac:InvoiceLine'); 
            $InvoiceLine = $Invoice->appendChild($InvoiceLine);
            $cbc = $xml->createElement('cbc:ID', $i); 
            $cbc = $InvoiceLine->appendChild($cbc);
            $cbc = $xml->createElement('cbc:InvoicedQuantity',$detalle->cdecan); 
            $cbc = $InvoiceLine->appendChild($cbc); 
            $cbc->setAttribute('unitCode', $codumecin->umecin);
            $cbc = $xml->createElement('cbc:LineExtensionAmount',$detalle->cdevun); 
            $cbc = $InvoiceLine->appendChild($cbc); 
            $cbc->setAttribute('currencyID',  $mondoc);
            $pricing = $xml->createElement('cac:PricingReference'); 
            $pricing = $InvoiceLine->appendChild($pricing);
            $cac = $xml->createElement('cac:AlternativeConditionPrice'); 
            $cac = $pricing->appendChild($cac);
            $cbc = $xml->createElement('cbc:PriceAmount',$detalle->cdepuni); 
            $cbc = $cac->appendChild($cbc); $cbc->setAttribute('currencyID', $mondoc);
            $cbc = $xml->createElement('cbc:PriceTypeCode','01'); 
            $cbc = $cac->appendChild($cbc);
           
            //descuento por item
            $allowance = $xml->createElement('cac:AllowanceCharge'); 
            $allowance = $InvoiceLine->appendChild($allowance);
            $cbc = $xml->createElement('cbc:ChargeIndicator','false'); 
            $cbc = $allowance->appendChild($cbc);
            $cbc = $xml->createElement('cbc:Amount','0.00'); 
            $cbc = $allowance->appendChild($cbc); 
            $cbc->setAttribute('currencyID', $mondoc);


            $taxtotal = $xml->createElement('cac:TaxTotal'); 
            $taxtotal = $InvoiceLine->appendChild($taxtotal);
            $cbc = $xml->createElement('cbc:TaxAmount',$detalle->cdeigv); 
            $cbc = $taxtotal->appendChild($cbc); 
            $cbc->setAttribute('currencyID',$mondoc);
            $taxtsubtotal = $xml->createElement('cac:TaxSubtotal'); 
            $taxtsubtotal = $taxtotal->appendChild($taxtsubtotal);
            $cbc = $xml->createElement('cbc:TaxableAmount',$detalle->cdeigv); 
            $cbc = $taxtsubtotal->appendChild($cbc); 
            $cbc->setAttribute('currencyID',$mondoc);
            $cbc = $xml->createElement('cbc:TaxAmount',$detalle->cdeigv); 
            $cbc = $taxtsubtotal->appendChild($cbc); 
            $cbc->setAttribute('currencyID',$mondoc);
            $taxtcategory = $xml->createElement('cac:TaxCategory'); 
            $taxtcategory = $taxtsubtotal->appendChild($taxtcategory);
            $cbc = $xml->createElement('cbc:TaxExemptionReasonCode', $detalle->tigcod); 
            $cbc = $taxtcategory->appendChild($cbc);
            $taxscheme = $xml->createElement('cac:TaxScheme'); 
            $taxscheme = $taxtcategory->appendChild($taxscheme);
            $cbc = $xml->createElement('cbc:ID',$tiptri); 
            $cbc = $taxscheme->appendChild($cbc);
            $cbc = $xml->createElement('cbc:Name','IGV'); 
            $cbc = $taxscheme->appendChild($cbc);
            $cbc = $xml->createElement('cbc:TaxTypeCode','VAT'); 
            $cbc = $taxscheme->appendChild($cbc);

            $item = $xml->createElement('cac:Item'); 
            $item = $InvoiceLine->appendChild($item);
            $cbc = $xml->createElement('cbc:Description',$detalle->cdedes); 
            $cbc = $item->appendChild($cbc);
           
            $sellers = $xml->createElement('cac:SellersItemIdentification'); 
            $sellers = $item->appendChild($sellers);
            $cbc = $xml->createElement('cbc:ID',$detalle->procod); 
            $cbc = $sellers->appendChild($cbc);
            
          
            $price = $xml->createElement('cac:Price'); 
            $price = $InvoiceLine->appendChild($price);
            $cbc = $xml->createElement('cbc:PriceAmount', $detalle->cdepve); 
            $cbc = $price->appendChild($cbc); $cbc->setAttribute('currencyID',$mondoc);
                
        }
        
        $xml->formatOutput = true;
        $strings_xml = $xml->saveXML();
        

        $xml->save($rucemp.'-'.$tdocod.'-'.$sercomp.'-'.$numdoc.'.xml');

        $codfact = $cabecera->IdCpe_cabecera; 

        $nomfilexml = $rucemp.'-'.$tdocod.'-'.$sercomp.'-'.$numdoc;
        $xmlPath = $nomfilexml.'.xml' ;
       
        $pfx = file_get_contents('NUEVOcEtWQ1RLRldiaVBnejIzNA==.pfx');
        $password = 'tAkqaV3EDLQSgXPQ';

        $certificate = new X509Certificate($pfx, $password);
        $pem = $certificate->export(X509ContentType::PEM);
            
        file_put_contents('certificado_20505641770.pem', $pem);

        $certPath = 'certificadoestudiocontable.pem'; 

        // Convertir pfx to pem 


        $xmlDocument = new DOMDocument();
        $xmlDocument->load($xmlPath);
        $xmlTool = new SignedXml();
        $xmlTool->setCertificateFromFile($certPath);
        $xmlTool->sign($xmlDocument);
        $content = $xmlDocument->saveXML();
    
      
        $arch = fopen ($xmlPath, "w+");
        fwrite($arch,"");
        fclose($arch);

        file_put_contents($xmlPath, $content);

        $zipname = $nomfilexml.".zip";
        $zip = new ZipArchive;
        $zip->open($zipname, ZipArchive::CREATE);
        $zip->addFile($xmlPath,$xmlPath);
        $zip->close();

        $contenido = file_get_contents($zipname);
        $contentFile = base64_encode($contenido);

        $usu = $empresa->wsusuario;
        $pass = $empresa->wscontrasena;

        $wsdl = "http://calidad.escondatagate.net/wsValidator/ol-ti-itcpe/billService.wsdl";

        $xmlstring = '<?xml version="1.0" encoding="UTF-8"?><SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:math="http://exslt.org/math">

        <SOAP-ENV:Header>

            <Security xmlns="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">

                <UsernameToken>

                    <Username>'.$empresa->IdEmpresa.$usu.'</Username>
                    <Password>'.$pass.'</Password>
                </UsernameToken>


            </Security>

        </SOAP-ENV:Header>

        <SOAP-ENV:Body xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd" xmlns:ns2="http://service.sunat.gob.pe" wsu:Id="47e96d2b-4153-44a5-90a7-5b03b46aaf25">

            <ns2:sendBill xmlns:ns2="http://service.sunat.gob.pe">

                <fileName>'.$zipname.'</fileName> 
                <contentFile>' . base64_encode(file_get_contents($zipname)). '</contentFile>
       </ns2:sendBill>

        </SOAP-ENV:Body>

    </SOAP-ENV:Envelope>';


 
  $result = self::soapCall($wsdl, $callFunction = "sendBill", $xmlstring);

  $xmlstring = '<?xml version="1.0" encoding="UTF-8"?><SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:math="http://exslt.org/math">

        <SOAP-ENV:Header>

            <Security xmlns="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">

                <UsernameToken>

                    <Username>'.$empresa->IdEmpresa.$usu.'</Username>
                    <Password>'.$pass.'</Password>
                </UsernameToken>
            </Security>

        </SOAP-ENV:Header>

        <SOAP-ENV:Body xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd" xmlns:ns2="http://service.sunat.gob.pe" wsu:Id="47e96d2b-4153-44a5-90a7-5b03b46aaf25">

            <ns2:getStatusCdr xmlns:ns2="http://service.sunat.gob.pe">
                      <rucComprobante>'.$rucemp.'</rucComprobante>
                     <tipoComprobante>'.$tdocod.'</tipoComprobante>
                     <serieComprobante>'.$sernum.'</serieComprobante>
                     <numeroComprobante>'.$numdoc.'</numeroComprobante>
       </ns2:sendBill>

        </SOAP-ENV:Body>

    </SOAP-ENV:Envelope>';
 
      $result = self::soapCall($wsdl, $callFunction = "getStatusCdr", $xmlstring);



   if($tdocod=='03'){

      return Redirect::to('/SisFact/create/03/'.$codfact)->with('success','registrado');
    }elseif($tdocod=='01'){
        return Redirect::to('/SisFact/create/01/'.$codfact)->with('success','registrado');
    }
}

 
    public function facturapdf($codfact,$doccod,$idcabecera)
    {

      $rucemp = trim(Auth::user()->IdEmpresa);
      $rutpdfile = public_path().'/'.$rucemp.'/';
     // $file= $rutpdfile.$codfact.'.pdf';
      //$file= $codfact.'.pdf';
      $file= $rutpdfile.$codfact.'.pdf';

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
         return Redirect::to('/SisFact');
      }
    }


    public function buscarcomprobante(Request $request){
      $search = $request->term;

      $ser = substr($search,strpos($search,'-')-4,4);
      $num = substr($search,strpos($search,'-')+1,8);

      $rucemp = trim(Auth::user()->IdEmpresa);
      $comprobante = DB::tABLE('cpe_cabecera as cab')
      ->join('cliente as cli','cab.ccandi','=','cli.clinum')
      ->join('tipo_documento as tdo','cab.tdocod','=','tdo.tdocod')
      ->join('tipo_documento_identidad as tdi','cab.tdicod','=','tdi.tdicod')
      ->join('moneda as mon','cab.moncod','=','mon.moncod')
      ->join('tipo_operacion as top','cab.topcod','=','top.topcod')
      ->where('serdoc','=',$ser)
      ->where('numdoc','=',$num)
      ->where('IdEmpresa','=',$rucemp)
      ->where('ccasunrescod','=','0')
      ->where('ccabaj','=',NULL)
      //->orwhere(DB::raw('substr(respse,1,2)'),'=','04')
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

      $rucemp = trim(Auth::user()->IdEmpresa);

      $compcabecera = DB::tABLE('cpe_cabecera as cpe_c')->select('serdoc','numdoc')
      ->where('serdoc','=',$ser)
      ->where('numdoc','=',$num)
      ->where('IdEmpresa','=',$rucemp);

      $comprobante = DB::tABLE('cpe_nota as cpe_n')->select('cpe_n.serdoc','cpe_n.numdoc')
       ->join('cpe_cabecera as cpe_c','cpe_n.IdCpe_cabecera','=','cpe_c.IdCpe_cabecera')
      ->where('cpe_n.serdoc','=',$ser)
      ->where('cpe_n.numdoc','=',$num)
      ->where('cpe_c.IdEmpresa','=',$rucemp)
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

      $rucemp = trim(Auth::user()->IdEmpresa);
      $comprobante = DB::tABLE('cpe_cabecera as cab')
      ->join('cliente as cli','cab.ccandi','=','cli.clinum')
      ->join('tipo_documento as tdo','cab.tdocod','=','tdo.tdocod')
      ->join('tipo_documento_identidad as tdi','cab.tdicod','=','tdi.tdicod')
      ->join('moneda as mon','cab.moncod','=','mon.moncod')
      ->join('tipo_operacion as top','cab.topcod','=','top.topcod')
      ->where('serdoc','=',$ser)
      ->where('numdoc','=',$num)
      ->where('IdEmpresa','=',$rucemp)
      ->where('ccasunrescod','=','0' )
      ->where('ccabaj','=',NULL)
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
      $rucemp = trim(Auth::user()->IdEmpresa);
      
      //$ruc = Cliente::where('clinum','like','%'.$search.'%')->where('cliest','=','Activo')->where('rucemp','=',$rucemp)->orwhere('clinom','like','%'.$search.'%')->where('rucemp','=',$rucemp)->where('cliest','=','Activo')->take(10)->get();
      $ruc = Cliente::where('clinum','like','%'.$search.'%')->where('rucemp','=',$rucemp)->where('cliest','=','Activo')->take(10)->get();
      $results = array();
      $results1 = array();

     if(!isset($ruc)){
        foreach($ruc as $cli => $cliente){
           $numnom=$cliente->clinum;
          //$numnom=$resultado['ruc'].'|'.$resultado['razonSocial'];
          //$results[] = ['value'=>$numnom,'nom'=>$resultado['razonSocial'],'dir'=>$resultado['direccion']];
          $results[] = ['value'=>$numnom,'nom'=>$cliente->clinom,'dir'=>$cliente->clidir,'tdicod'=>$cliente->tdicod];
        }

      }else{

          if(empty($results)){
            $resultado = self::consultaruc($search);
              if($resultado['status']!= '0'){
                  
                $numnom=$resultado['ruc'];
                $results1[] = ['value'=>$numnom,'nom'=>$resultado['razonSocial'],'dir'=>$resultado['direccion'],'tdicod'=>'6'];
              }
          }

          if(empty($results1)){
            $results = array();
          }elseif(!empty($results1)){
             $results = $results1;
          }
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
       $rucemp = trim(Auth::user()->IdEmpresa);
      //$productos= DB::tABLE('productos')->where('procod', 'like','%'.$search.'%')->where('IdEmpresa','=',$rucemp)->where('proest','=','Activo')
       $productos= DB::tABLE('productos')->where('procod', 'like','%'.$search.'%')->where('IdEmpresa','=',$rucemp)->where('proest','=','Activo')
        ->orderby('pronom','asc')->get();

      $results = array();
      foreach($productos as $pro){
        $codnom = $pro->procod;
        $results[] = ['value'=>$codnom,'pronom'=>$pro->pronom,'provun'=>$pro->provun,'umecod'=>$pro->umecod,'propun'=>$pro->propun];
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

  
  

 public function registrarnota(Request $request){

        $rucemp = trim(Auth::user()->IdEmpresa);

        $empresa = Empresa::findOrFail($rucemp);
        $serdoc = $request->get('serdoc');
        $numdoc = str_pad($request->get('numdoc'),8,"0", STR_PAD_LEFT);
        //$sercomp = $request->get('serdoc');
        $numcomp = $request->get('numdoc');
        $serdocmod = $request->get('serdocmod');
        $numdocmod = str_pad($request->get('numdocmod'),8,"0", STR_PAD_LEFT);
        $tdicod= $request->get('tdicod');
        $tipdoc = $request->get('tdo_cod');
        $tipnot = $request->get('tipnot');
        $tipmon = $request->get('tipmon');
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
        $exp=$request->get('exp');
        $puni = $request->get('preuni');
        $descglb = $request->get('desc');
        $tdomod = $request->get('tdomod');
        $nota = $serdoc.'-'.$numdoc;


        $docmod = DB::tABLE('cpe_cabecera')->select('IdCpe_cabecera')->where('IdEmpresa','=',$rucemp)->where('serdoc','=',$serdocmod)->where('numdoc','=',$numdocmod)->first();

        $IdCpe_cabecera=$docmod->IdCpe_cabecera;

        $cabecera = new cpe_nota;
        $cabecera->tdocod = $tdocod;
        $cabecera->ccafem = $fecemi;
        $cabecera->ccaobs = $motivo;
        $cabecera->serdoc = $serdoc;
        $cabecera->numdoc = $numdoc;
        $cabecera->tipcambio = $tipcambio;
        $cabecera->tipnot = $tipnot;
        $cabecera->IdUsuario = Auth::user()->IdUsuario;
        $cabecera->ccacar = $request->get('otrosc');
        $cabecera->ccatvg = $request->get('grav');
        $cabecera->ccanom = $clinom;
        $cabecera->ccandi = $clinum;
        $cabecera->ccatvgr = $request->get('grat');
        $cabecera->ccatvi = $request->get('inaf');
        $cabecera->ccatexp= $request->get('exp');
        $cabecera->ccatve = $request->get('exon');
        $cabecera->ccaigv = $request->get('igv');
        $cabecera->ccaisc = $request->get('isc');
        $cabecera->ccaotr = $request->get('otros');
        $cabecera->ccaitv = $request->get('total');
        $cabecera->serdoc= $request->get('serdoc');
        $cabecera->ccatexp= $request->get('exp');
        $cabecera->moncod = $tipmon;
        $cabecera->IdEmpresa = $rucemp;
        $cabecera->IdCpe_cabecera = $IdCpe_cabecera;
        $cabecera->IdUsuario = Auth::user()->IdUsuario;
        $cabecera->save();

       
        $cabfactura = cpe_cabecera::findOrFail($IdCpe_cabecera);
        $cabfactura->ccanot = $nota;
        $cabfactura->update();
        
        $totalletras= MontoLetras::convertir(number_format($cabecera->ccaitv,'2','.',','), $monnom,'CENTIMOS');

      

        if ($tdocod =='07') {
            if($tipdoc =='01'){
                $empresa = Empresa::findOrFail($rucemp);

                if( $empresa->FcnuEmpresa == $numcomp){
                    $modnumdoc= $numcomp+1;
                }else{
                    $modnumdoc = $numcomp;
                }

                $empresa->FcseEmpresa = $serdoc;
                $empresa->FcnuEmpresa = $modnumdoc;
                $empresa->update();

                $numdoc = str_pad($modnumdoc,8,"0", STR_PAD_LEFT);
                $cabecera->serdoc= $serdoc;
                $cabecera->numdoc = $numdoc;
                $cabecera->save();

            }elseif($tipdoc=='03'){
                $empresa = Empresa::findOrFail($rucemp);
                
                if( $empresa->BcnuEmpresa == $numcomp){
                    $modnumdoc= $numcomp+1;
                }else{
                    $modnumdoc = $numcomp;
                }

                $empresa->BcseEmpresa = $serdoc;
                $empresa->BcnuEmpresa = $modnumdoc;
                $empresa->update();

                $numdoc = str_pad($modnumdoc,8,"0", STR_PAD_LEFT);
                $cabecera->serdoc= $serdoc;
                $cabecera->numdoc = $numdoc;
                $cabecera->save();

            }
        }elseif ($tdocod =='08') {
            if($tipdoc =='01'){
                $empresa = Empresa::findOrFail($rucemp);
                if( $empresa->FdnuEmpresa == $numcomp){
                    $modnumdoc= $numcomp+1;
                }else{
                    $modnumdoc = $numcomp;
                }

                $empresa->FdseEmpresa = $serdoc;
                $empresa->FdnuEmpresa = $modnumdoc;
                $empresa->update();

                $numdoc = str_pad($modnumdoc,8,"0", STR_PAD_LEFT);
                $cabecera->serdoc= $serdoc;
                $cabecera->numdoc = $numdoc;
                $cabecera->save();

            }elseif($tipdoc=='03'){
                $empresa = Empresa::findOrFail($rucemp);
              
                if( $empresa->BdnuEmpresa == $numcomp){
                    $modnumdoc= $numcomp+1;
                }else{
                    $modnumdoc = $numcomp;
                }

                $empresa->BdseEmpresa = $serdoc;
                $empresa->BdnuEmpresa = $modnumdoc;
                $empresa->update();

                $numdoc = str_pad($modnumdoc,8,"0", STR_PAD_LEFT);
                $cabecera->serdoc= $serdoc;
                $cabecera->numdoc = $numdoc;
                $cabecera->save();
            }
        }
      

       if ($tdocod =='07') {
            $xml = new DomDocument('1.0', 'UTF-8');
            $xml->preserveWhiteSpace = false;
            $CreditNote = $xml->createElement('CreditNote'); 
            $CreditNote = $xml->appendChild($CreditNote);
            $CreditNote->setAttribute('xmlns',"urn:oasis:names:specification:ubl:schema:xsd:CreditNote-2");
            $CreditNote->setAttribute('xmlns:cac',"urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2");
            $CreditNote->setAttribute('xmlns:cbc',"urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2");
            $CreditNote->setAttribute('xmlns:ccts',"urn:un:unece:uncefact:documentation:2");
            $CreditNote->setAttribute('xmlns:ds',"http://www.w3.org/2000/09/xmldsig#");
            $CreditNote->setAttribute('xmlns:ext',"urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2");
            $CreditNote->setAttribute('xmlns:qdt',"urn:oasis:names:specification:ubl:schema:xsd:QualifiedDatatypes-2");
            $CreditNote->setAttribute('xmlns:sac',"urn:sunat:names:specification:ubl:peru:schema:xsd:SunatAggregateComponents-1");
            $CreditNote->setAttribute('xmlns:udt',"urn:un:unece:uncefact:data:specification:UnqualifiedDataTypesSchemaModule:2");
            $CreditNote->setAttribute('xmlns:xsi',"http://www.w3.org/2001/XMLSchema-instance");

            $UBLExtension = $xml->createElement('ext:UBLExtensions'); 
            $UBLExtension = $CreditNote->appendChild($UBLExtension);
            $ext = $xml->createElement('ext:UBLExtension'); 
            $ext = $UBLExtension->appendChild($ext);
            $contents = $xml->createElement('ext:ExtensionContent'); 
            $contents = $ext->appendChild($contents);
            $sac = $xml->createElement('sac:AdditionalInformation'); 
            $sac = $contents->appendChild($sac);
                           
            // el 2005 es Total descuentos
            $monetary = $xml->createElement('sac:AdditionalMonetaryTotal'); 
            $monetary = $sac->appendChild($monetary);
            
            $cbc = $xml->createElement('cbc:ID', '2005'); 
            $cbc = $monetary->appendChild($cbc);
            $cbc = $xml->createElement('cbc:PayableAmount','0.00'); 
            $cbc = $monetary->appendChild($cbc); 
            $cbc->setAttribute('currencyID', $mondoc);
            // El 1001 total velor venta - operaciones gravadas1
            $monetary = $xml->createElement('sac:AdditionalMonetaryTotal'); 
            $monetary = $sac->appendChild($monetary);
            $cbc = $xml->createElement('cbc:ID', '1001'); 
            $cbc = $monetary->appendChild($cbc);
            $cbc = $xml->createElement('cbc:PayableAmount', $grav);
            $cbc = $monetary->appendChild($cbc); 
            $cbc->setAttribute('currencyID', $mondoc);
            
            // el 1002 total valor venta - operaciones inafectas
            $monetary = $xml->createElement('sac:AdditionalMonetaryTotal'); 
            $monetary = $sac->appendChild($monetary);
            $cbc = $xml->createElement('cbc:ID', '1002'); 
            $cbc = $monetary->appendChild($cbc);
            $cbc = $xml->createElement('cbc:PayableAmount', $inaf); 
            $cbc = $monetary->appendChild($cbc); 
            $cbc->setAttribute('currencyID', $mondoc);
            // el 1003 total valor venta - operaciones exoneradas
            $monetary = $xml->createElement('sac:AdditionalMonetaryTotal'); 
            $monetary = $sac->appendChild($monetary);
            $cbc = $xml->createElement('cbc:ID', '1003'); 
            $cbc = $monetary->appendChild($cbc);
            $cbc = $xml->createElement('cbc:PayableAmount', $exon); 
            $cbc = $monetary->appendChild($cbc); 
            $cbc->setAttribute('currencyID', $mondoc);
            
            // 2.- Firma electronica
            $ext = $xml->createElement('ext:UBLExtension'); 
            $ext = $UBLExtension->appendChild($ext);
            $contents = $xml->createElement('ext:ExtensionContent', ' '); 
            $contents = $ext->appendChild($contents);
            // 36. Version del UBL
            $cbc = $xml->createElement('cbc:UBLVersionID', '2.0'); 
            $cbc = $CreditNote->appendChild($cbc);
            // 37.- Version de la estructura del documento
            $cbc = $xml->createElement('cbc:CustomizationID', '1.0'); 
            $cbc = $CreditNote->appendChild($cbc);
            // 8.- Numeracion , conformada por serie y numero correlativo B001-00012926
            $cbc = $xml->createElement('cbc:ID', $serdoc.'-'.$numdoc); 
            $cbc = $CreditNote->appendChild($cbc);
            // 1.- Fecha de emision 2017-04-13
            $cbc = $xml->createElement('cbc:IssueDate', $fecemi); 
            $cbc = $CreditNote->appendChild($cbc);
            // 28.- Tipo de moneda en la cual se emite la factura electronica $c19
            $cbc = $xml->createElement('cbc:DocumentCurrencyCode',$mondoc); 
            $cbc = $CreditNote->appendChild($cbc);
            $cac = $xml->createElement('cac:DiscrepancyResponse'); 
            $cac = $CreditNote->appendChild($cac);
            $cbc = $xml->createElement('cbc:ReferenceID',$serdocmod.'-'.$numdocmod); 
            $cbc = $cac->appendChild($cbc);
            $cbc = $xml->createElement('cbc:ResponseCode',$tipnot); 
            $cbc = $cac->appendChild($cbc);
            $cbc = $xml->createElement('cbc:Description', $motivo); 
            $cbc = $cac->appendChild($cbc);

            $BillingReference = $xml->createElement('cac:BillingReference'); 
            $BillingReference = $CreditNote->appendChild($BillingReference);
            $cac = $xml->createElement('cac:InvoiceDocumentReference'); 
            $cac = $BillingReference->appendChild($cac);
            $cbc = $xml->createElement('cbc:ID',$serdocmod.'-'.$numdocmod); 
            $cbc = $cac->appendChild($cbc);
            $cbc = $xml->createElement('cbc:DocumentTypeCode',$tipdoc); 
            $cbc = $cac->appendChild($cbc);

            // 2.- Parte de la firma electronica. esto es de quien creo la firma electronica
            $cac_signature = $xml->createElement('cac:Signature'); 
            $cac_signature = $CreditNote->appendChild($cac_signature);
            $cbc = $xml->createElement('cbc:ID',$empresa->IdEmpresa); 
            $cbc = $cac_signature->appendChild($cbc);
            $cac_signatory = $xml->createElement('cac:SignatoryParty'); 
            $cac_signatory = $cac_signature->appendChild($cac_signatory);
            $cac = $xml->createElement('cac:PartyIdentification'); 
            $cac = $cac_signatory->appendChild($cac);
            $cbc = $xml->createElement('cbc:ID',$empresa->IdEmpresa); 
            $cbc = $cac->appendChild($cbc);
            $cac = $xml->createElement('cac:PartyName'); 
            $cac = $cac_signatory->appendChild($cac);
            $cbc = $xml->createElement('cbc:Name',$empresa->NomEmpresa); 
            $cbc = $cac->appendChild($cbc);
            $cac_digital = $xml->createElement('cac:DigitalSignatureAttachment'); 
            $cac_digital = $cac_signature->appendChild($cac_digital);
            $cac = $xml->createElement('cac:ExternalReference'); 
            $cac = $cac_digital->appendChild($cac);
            $cbc = $xml->createElement('cbc:URI',$empresa->IdEmpresa); 
            $cbc = $cac->appendChild($cbc);
            // DATOS EMISOR
           $cac_accounting = $xml->createElement('cac:AccountingSupplierParty'); 
            $cac_accounting = $CreditNote->appendChild($cac_accounting);
            $cbc = $xml->createElement('cbc:CustomerAssignedAccountID', $rucemp); 
            $cbc = $cac_accounting->appendChild($cbc);
            $cbc = $xml->createElement('cbc:AdditionalAccountID', '6'); 
            $cbc = $cac_accounting->appendChild($cbc);
            $cac_party = $xml->createElement('cac:Party'); 
            $cac_party = $cac_accounting->appendChild($cac_party);
            $cac = $xml->createElement('cac:PartyName'); 
            $cac = $cac_party->appendChild($cac);
            $cbc = $xml->createElement('cbc:Name', $empresa->NomEmpresa); 
            $cbc = $cac->appendChild($cbc);
            $address = $xml->createElement('cac:PostalAddress'); 
            $address = $cac_party->appendChild($address);
            $cbc = $xml->createElement('cbc:ID',''); 
            $cbc = $address->appendChild($cbc);
            $cbc = $xml->createElement('cbc:StreetName', $empresa->DirEmpresa); 
            $cbc = $address->appendChild($cbc);
            $country = $xml->createElement('cac:Country'); 
            $country = $address->appendChild($country);
            $cbc = $xml->createElement('cbc:IdentificationCode', 'PER'); 
            $cbc = $country->appendChild($cbc);
            $legal = $xml->createElement('cac:PartyLegalEntity'); 
            $legal = $cac_party->appendChild($legal);
            $cbc = $xml->createElement('cbc:RegistrationName', $empresa->NomEmpresa); 
            $cbc = $legal->appendChild($cbc);

            //DATOS CLIENTE
            $cac_accounting = $xml->createElement('cac:AccountingCustomerParty'); 
            $cac_accounting = $CreditNote->appendChild($cac_accounting);
            $cbc = $xml->createElement('cbc:CustomerAssignedAccountID', $clinum); 
            $cbc = $cac_accounting->appendChild($cbc);
            $cbc = $xml->createElement('cbc:AdditionalAccountID',$tdicod); 
            $cbc = $cac_accounting->appendChild($cbc);
            $cac_party = $xml->createElement('cac:Party'); 
            $cac_party = $cac_accounting->appendChild($cac_party);
            // nombre o razon zocial
            $legal = $xml->createElement('cac:PartyLegalEntity'); 
            $legal = $cac_party->appendChild($legal);
            $cbc = $xml->createElement('cbc:RegistrationName',$clinom); 
            $cbc = $legal->appendChild($cbc);
            
            // Sumatoria IGV
            $taxtotal = $xml->createElement('cac:TaxTotal'); 
            $taxtotal = $CreditNote->appendChild($taxtotal);
            $cbc = $xml->createElement('cbc:TaxAmount', $igv); 
            $cbc = $taxtotal->appendChild($cbc); 
            $cbc->setAttribute('currencyID', $mondoc);
            $taxtsubtotal = $xml->createElement('cac:TaxSubtotal'); 
            $taxtsubtotal = $taxtotal->appendChild($taxtsubtotal);
            $cbc = $xml->createElement('cbc:TaxAmount', $igv); 
            $cbc = $taxtsubtotal->appendChild($cbc); 
            $cbc->setAttribute('currencyID', $mondoc);
            $taxtcategory = $xml->createElement('cac:TaxCategory'); 
            $taxtcategory = $taxtsubtotal->appendChild($taxtcategory);
            $taxscheme = $xml->createElement('cac:TaxScheme'); 
            $taxscheme = $taxtcategory->appendChild($taxscheme);
            $cbc = $xml->createElement('cbc:ID', '1000'); 
            $cbc = $taxscheme->appendChild($cbc);
            $cbc = $xml->createElement('cbc:Name', 'IGV'); 
            $cbc = $taxscheme->appendChild($cbc);
            $cbc = $xml->createElement('cbc:TaxTypeCode', 'VAT'); 
            $cbc = $taxscheme->appendChild($cbc);
            // Importe total de venta
            $legal = $xml->createElement('cac:LegalMonetaryTotal'); 
            $legal = $CreditNote->appendChild($legal);
            $cbc = $xml->createElement('cbc:PayableAmount', $total); 
            $cbc = $legal->appendChild($cbc); 
            $cbc->setAttribute('currencyID', $mondoc);

        }elseif($tdocod =='08'){

            $xml = new DomDocument('1.0','UTF-8');
            $xml->standalone         = false;
            $xml->preserveWhiteSpace = false;
            $DebitNote = $xml->createElement('DebitNote'); 
            $DebitNote = $xml->appendChild($DebitNote);
            $DebitNote->setAttribute('xmlns',"urn:oasis:names:specification:ubl:schema:xsd:DebitNote-2");
            $DebitNote->setAttribute('xmlns:cac',"urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2");
            $DebitNote->setAttribute('xmlns:cbc',"urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2");
            $DebitNote->setAttribute('xmlns:ccts',"urn:un:unece:uncefact:documentation:2");
            $DebitNote->setAttribute('xmlns:ds',"http://www.w3.org/2000/09/xmldsig#");
            $DebitNote->setAttribute('xmlns:ext',"urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2");
            $DebitNote->setAttribute('xmlns:qdt',"urn:oasis:names:specification:ubl:schema:xsd:QualifiedDatatypes-2");
            $DebitNote->setAttribute('xmlns:sac',"urn:sunat:names:specification:ubl:peru:schema:xsd:SunatAggregateComponents-1");
            $DebitNote->setAttribute('xmlns:udt',"urn:un:unece:uncefact:data:specification:UnqualifiedDataTypesSchemaModule:2");
            $DebitNote->setAttribute('xmlns:xsi',"http://www.w3.org/2001/XMLSchema-instance");

            $UBLExtension = $xml->createElement('ext:UBLExtensions'); 
            $UBLExtension = $DebitNote->appendChild($UBLExtension);
            $ext = $xml->createElement('ext:UBLExtension'); 
            $ext = $UBLExtension->appendChild($ext);
            $contents = $xml->createElement('ext:ExtensionContent'); 
            $contents = $ext->appendChild($contents);
            $sac = $xml->createElement('sac:AdditionalInformation'); 
            $sac = $contents->appendChild($sac);
                           
           
            // El 1001 total velor venta - operaciones gravadas1
            $monetary = $xml->createElement('sac:AdditionalMonetaryTotal'); 
            $monetary = $sac->appendChild($monetary);
            $cbc = $xml->createElement('cbc:ID', '1001'); 
            $cbc = $monetary->appendChild($cbc);
            $cbc = $xml->createElement('cbc:PayableAmount', $grav);
            $cbc = $monetary->appendChild($cbc); 
            $cbc->setAttribute('currencyID', $mondoc);
            
            // el 1002 total valor venta - operaciones inafectas
            $monetary = $xml->createElement('sac:AdditionalMonetaryTotal'); 
            $monetary = $sac->appendChild($monetary);
            $cbc = $xml->createElement('cbc:ID', '1002'); 
            $cbc = $monetary->appendChild($cbc);
            $cbc = $xml->createElement('cbc:PayableAmount', $inaf); 
            $cbc = $monetary->appendChild($cbc); 
            $cbc->setAttribute('currencyID', $mondoc);
            // el 1003 total valor venta - operaciones exoneradas
            $monetary = $xml->createElement('sac:AdditionalMonetaryTotal'); 
            $monetary = $sac->appendChild($monetary);
            $cbc = $xml->createElement('cbc:ID', '1003'); 
            $cbc = $monetary->appendChild($cbc);
            $cbc = $xml->createElement('cbc:PayableAmount', $exon); 
            $cbc = $monetary->appendChild($cbc); 
            $cbc->setAttribute('currencyID', $mondoc);
            
            // 2.- Firma electronica
            $ext = $xml->createElement('ext:UBLExtension'); 
            $ext = $UBLExtension->appendChild($ext);
            $contents = $xml->createElement('ext:ExtensionContent',''); 
            $contents = $ext->appendChild($contents);

            // 36. Version del UBL
            $cbc = $xml->createElement('cbc:UBLVersionID', '2.0'); 
            $cbc = $DebitNote->appendChild($cbc);
            // 37.- Version de la estructura del documento
            $cbc = $xml->createElement('cbc:CustomizationID', '1.0'); 
            $cbc = $DebitNote->appendChild($cbc);
            // 8.- Numeracion , conformada por serie y numero correlativo B001-00012926
            $cbc = $xml->createElement('cbc:ID',$serdoc.'-'.$numdoc); 
            $cbc = $DebitNote->appendChild($cbc);
            // 1.- Fecha de emision 2017-04-13
            $cbc = $xml->createElement('cbc:IssueDate',$fecemi); 
            $cbc = $DebitNote->appendChild($cbc);

            $cbc_IssueDate = $xml->createElement('cbc:IssueTime','11:30:20'); 
            $cbc_IssueDate = $DebitNote->appendChild($cbc_IssueDate);
            // 28.- Tipo de moneda en la cual se emite la factura electronica $c19
            $cbc = $xml->createElement('cbc:DocumentCurrencyCode',$mondoc); 
            $cbc = $DebitNote->appendChild($cbc);
            $cac = $xml->createElement('cac:DiscrepancyResponse'); 
            $cac = $DebitNote->appendChild($cac);
            $cbc = $xml->createElement('cbc:ReferenceID',$serdocmod.'-'.$numdocmod); 
            $cbc = $cac->appendChild($cbc);
            $cbc = $xml->createElement('cbc:ResponseCode',$tipnot); 
            $cbc = $cac->appendChild($cbc);
            $cbc = $xml->createElement('cbc:Description', $motivo); 
            $cbc = $cac->appendChild($cbc);

            $BillingReference = $xml->createElement('cac:BillingReference'); 
            $BillingReference = $DebitNote->appendChild($BillingReference);
            $cac = $xml->createElement('cac:InvoiceDocumentReference'); 
            $cac = $BillingReference->appendChild($cac);
            $cbc = $xml->createElement('cbc:ID',$serdocmod.'-'.$numdocmod); 
            $cbc = $cac->appendChild($cbc);
            $cbc = $xml->createElement('cbc:DocumentTypeCode',$tipdoc); 
            $cbc = $cac->appendChild($cbc);

            // 2.- Parte de la firma electronica. esto es de quien creo la firma electronica
            $cac_signature = $xml->createElement('cac:Signature'); 
            $cac_signature = $DebitNote->appendChild($cac_signature);
            $cbc = $xml->createElement('cbc:ID',$empresa->IdEmpresa); 
            $cbc = $cac_signature->appendChild($cbc);
            $cac_signatory = $xml->createElement('cac:SignatoryParty'); 
            $cac_signatory = $cac_signature->appendChild($cac_signatory);
            $cac = $xml->createElement('cac:PartyIdentification'); 
            $cac = $cac_signatory->appendChild($cac);
            $cbc = $xml->createElement('cbc:ID',$empresa->IdEmpresa); 
            $cbc = $cac->appendChild($cbc);
            $cac = $xml->createElement('cac:PartyName'); 
            $cac = $cac_signatory->appendChild($cac);
            $cbc = $xml->createElement('cbc:Name',$empresa->NomEmpresa); 
            $cbc = $cac->appendChild($cbc);
            $cac_digital = $xml->createElement('cac:DigitalSignatureAttachment'); 
            $cac_digital = $cac_signature->appendChild($cac_digital);
            $cac = $xml->createElement('cac:ExternalReference'); 
            $cac = $cac_digital->appendChild($cac);
            $cbc = $xml->createElement('cbc:URI',$empresa->IdEmpresa); 
            $cbc = $cac->appendChild($cbc);
            // DATOS EMISOR
            $cac_accounting = $xml->createElement('cac:AccountingSupplierParty'); 
            $cac_accounting = $DebitNote->appendChild($cac_accounting);
            $cbc = $xml->createElement('cbc:CustomerAssignedAccountID',$rucemp); 
            $cbc = $cac_accounting->appendChild($cbc);
            $cbc = $xml->createElement('cbc:AdditionalAccountID','6'); 
            $cbc = $cac_accounting->appendChild($cbc);
            $cac_party = $xml->createElement('cac:Party'); 
            $cac_party = $cac_accounting->appendChild($cac_party);
            $address = $xml->createElement('cac:PostalAddress'); 
            $address = $cac_party->appendChild($address);
            $cbc = $xml->createElement('cbc:AddressTypeCode','0015'); 
            $cbc = $address->appendChild($cbc);
           // $cbc = $xml->createElement('cbc:StreetName', $empresa->DirEmpresa); 
           // $cbc = $address->appendChild($cbc);
           // $country = $xml->createElement('cac:Country'); 
           // $country = $address->appendChild($country);
           // $cbc = $xml->createElement('cbc:IdentificationCode', 'PER'); 
           // $cbc = $country->appendChild($cbc);
            $legal = $xml->createElement('cac:PartyLegalEntity'); 
            $legal = $cac_party->appendChild($legal);
            $cbc = $xml->createElement('cbc:RegistrationName',$empresa->NomEmpresa); 
            $cbc = $legal->appendChild($cbc);

            //DATOS CLIENTE
            $cac_accounting = $xml->createElement('cac:AccountingCustomerParty'); 
            $cac_accounting = $DebitNote->appendChild($cac_accounting);
            $cbc = $xml->createElement('cbc:CustomerAssignedAccountID',$clinum); 
            $cbc = $cac_accounting->appendChild($cbc);
            $cbc = $xml->createElement('cbc:AdditionalAccountID',$tdicod); 
            $cbc = $cac_accounting->appendChild($cbc);
            $cac_party = $xml->createElement('cac:Party'); 
            $cac_party = $cac_accounting->appendChild($cac_party);
            // nombre o razon zocial
            $legal = $xml->createElement('cac:PartyLegalEntity'); 
            $legal = $cac_party->appendChild($legal);
            $cbc = $xml->createElement('cbc:RegistrationName',$clinom); 
            $cbc = $legal->appendChild($cbc);

            $cac_RequestedMonetaryTotal = $xml->createElement('cac:RequestedMonetaryTotal'); 
            $cac_RequestedMonetaryTotal = $DebitNote->appendChild($cac_RequestedMonetaryTotal);
            $cbc = $xml->createElement('cbc:PayableAmount',$total); 
            $cbc = $cac_RequestedMonetaryTotal->appendChild($cbc);
            $cbc->setAttribute('currencyID', $mondoc);
            
            // Sumatoria IGV
         /*   $taxtotal = $xml->createElement('cac:TaxTotal'); 
            $taxtotal = $DebitNote->appendChild($taxtotal);
            $cbc = $xml->createElement('cbc:TaxAmount', $igv); 
            $cbc = $taxtotal->appendChild($cbc); 
            $cbc->setAttribute('currencyID', $mondoc);
            $taxtsubtotal = $xml->createElement('cac:TaxSubtotal'); 
            $taxtsubtotal = $taxtotal->appendChild($taxtsubtotal);
            $cbc = $xml->createElement('cbc:TaxAmount', $igv); 
            $cbc = $taxtsubtotal->appendChild($cbc); 
            $cbc->setAttribute('currencyID', $mondoc);
            $taxtcategory = $xml->createElement('cac:TaxCategory'); 
            $taxtcategory = $taxtsubtotal->appendChild($taxtcategory);
            $taxscheme = $xml->createElement('cac:TaxScheme'); 
            $taxscheme = $taxtcategory->appendChild($taxscheme);
            $cbc = $xml->createElement('cbc:ID', '1000'); 
            $cbc = $taxscheme->appendChild($cbc);
            $cbc = $xml->createElement('cbc:Name', 'IGV'); 
            $cbc = $taxscheme->appendChild($cbc);
            $cbc = $xml->createElement('cbc:TaxTypeCode', 'VAT'); 
            $cbc = $taxscheme->appendChild($cbc);
            // Importe total de venta
            $legal = $xml->createElement('cac:LegalMonetaryTotal'); 
            $legal = $DebitNote->appendChild($legal);
            $cbc = $xml->createElement('cbc:PayableAmount', $total); 
            $cbc = $legal->appendChild($cbc); $cbc->setAttribute('currencyID', $mondoc);*/
        }


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

           
        $docrel = $request->get('tdr');
        $docrser = $request->get('tdrser');
        $docrnum = $request->get('tdrnum');
        $docurela[] =array(
                          "tipoDocRelacionado"=>$tipdoc,
                          "numeroDocRelacionado"=> $serdocmod.'-'.$numdocmod,
                          "codigoMotivo"=> $tipnot,
                          "descripcionMotivo"=> $motivo
                        );
        if(!empty($docrel) && !empty($docrser) && !empty($docrnum)){
          foreach( $docrel as $index => $tdr ) 
          {
            if(!empty($docrel[$index]) && !empty($docrser[$index]) && !empty($docrnum[$index])){
               $docrenum = str_pad($docrnum[$index],8,"0", STR_PAD_LEFT);
               $drsernum = "$docrser[$index]-$docrnum[$index]";
               
               $guia = DB::table("documento_relacionado")->where('tdocod','=',$docrel[$index])->where('dorser','=',$docrser[$index])->where('dornum','=',$docrenum)->first();

               if($guia==''){

                    $docurela[] = array( "tipoDocRelacionado"=> $docrel[$index],
                    "numeroDocRelacionado"=> $drsernum
                    );
               } 
            }
          }

        }else{
          $docurela[] = '';
        }
    
        $newdocurela=array_filter($docurela);

        $i=0;
        foreach( $unidades as $index => $ume ) {
            $i=$i+1;
            $imod=str_pad($i,3,"0", STR_PAD_LEFT);
            $detalle = new cpe_nota_detalle;
            $detalle->IdCpe_nota =  $cabecera->IdCpe_nota; 
            $dpro = $detpro[$index];
            $detalle->cdedes = $dpro;
              
              
            if(($tdocod=='07' && ($tipnot=='04' || $tipnot=='08' || $tipnot=='10'  )) || ($tdocod=='08' && ($tipnot=='01' || $tipnot=='03' ))){
              $pos = strpos($codpro[$index],'|');
              $codproducto = substr($codpro[$index], 0, $pos);
            }else{
              $codproducto = $codpro[$index];
            }
             
              $detalle->umecod = $ume;
              $detalle->cdecan = $cantidades[$index];
              $detalle->procod = $codproducto;
              $detalle->cdepsu = $codproducto;
              $detalle->cdevun = $vunit[$index];
              $detalle->cdeigv = $vigv[$index];
              $detalle->tigcod = $tigv[$index];
              $detalle->cdepve = $vsub[$index];
              $detalle->cdevve = $vtot[$index];
              $detalle->cdepuni = $puni[$index];
            
            if($detalle->tigcod=='10'){
              $tiptri='1000'; //IGV Impuesto Generarl a las ventas
            }elseif($detalle->tigcod=='20'){
               $tiptri='9997'; //exonerado
            }elseif($detalle->tigcod=='30'){
              $tiptri ='9998'; //inafecto
            }elseif($detalle->tigcod=='17'){
              $tiptri ='1016'; //Impuesto a la venta de arroz pilado
            }elseif($detalle->tigcod=='40'){
              $tiptri ='9995'; //exportación
            }else{
              $tiptri='9996'; //Gratutito
            }

  
            //Guardar en una variable los items en el archivo .det
            $codumecin = unidad_medida::findOrFail($ume); 
            $detalle->tiptri = $tiptri;
            $detalle->save();

      if ($tdocod =='07') {
            $CreditNoteLine = $xml->createElement('cac:CreditNoteLine'); 
            $CreditNoteLine = $CreditNote->appendChild($CreditNoteLine);
            $cbc = $xml->createElement('cbc:ID', $i); 
            $cbc = $CreditNoteLine->appendChild($cbc);
            $cbc = $xml->createElement('cbc:CreditedQuantity', $detalle->cdecan); 
            $cbc = $CreditNoteLine->appendChild($cbc); 
            $cbc->setAttribute('unitCode', "NIU"); // cantidad x item:  1
            $cbc = $xml->createElement('cbc:LineExtensionAmount',$detalle->cdevun); 
            $cbc = $CreditNoteLine->appendChild($cbc); 
            $cbc->setAttribute('currencyID', $mondoc);
            
            // precio unitario del producto con igv
            $pricing = $xml->createElement('cac:PricingReference'); 
            $pricing = $CreditNoteLine->appendChild($pricing);
            $cac = $xml->createElement('cac:AlternativeConditionPrice'); 
            $cac = $pricing->appendChild($cac);
            
            // precio unitario con igv
            $cbc = $xml->createElement('cbc:PriceAmount',$detalle->cdepuni); 
            $cbc = $cac->appendChild($cbc); $cbc->setAttribute('currencyID', $mondoc);
            
            // 01 con igv, 02 operaciones no onerosas
            $cbc = $xml->createElement('cbc:PriceTypeCode', '01'); 
            $cbc = $cac->appendChild($cbc);
            // igv del total del producto aplicado ya el descuento *0.18
            $taxtotal = $xml->createElement('cac:TaxTotal'); 
            $taxtotal = $CreditNoteLine->appendChild($taxtotal);
            $cbc = $xml->createElement('cbc:TaxAmount', $detalle->cdeigv); 
            $cbc = $taxtotal->appendChild($cbc); $cbc->setAttribute('currencyID', $mondoc);
            $taxtsubtotal = $xml->createElement('cac:TaxSubtotal'); 
            $taxtsubtotal = $taxtotal->appendChild($taxtsubtotal);
            $cbc = $xml->createElement('cbc:TaxAmount',$detalle->cdeigv); 
            $cbc = $taxtsubtotal->appendChild($cbc); $cbc->setAttribute('currencyID', $mondoc);
            $taxtcategory = $xml->createElement('cac:TaxCategory'); 
            $taxtcategory = $taxtsubtotal->appendChild($taxtcategory);
            $cbc = $xml->createElement('cbc:TaxExemptionReasonCode',$detalle->tigcod); 
            $cbc = $taxtcategory->appendChild($cbc);
            $taxscheme = $xml->createElement('cac:TaxScheme'); 
            $taxscheme = $taxtcategory->appendChild($taxscheme);
            $cbc = $xml->createElement('cbc:ID',$tiptri); 
            $cbc = $taxscheme->appendChild($cbc);
            $cbc = $xml->createElement('cbc:Name', 'IGV'); 
            $cbc = $taxscheme->appendChild($cbc);
            $cbc = $xml->createElement('cbc:TaxTypeCode', 'VAT'); 
            $cbc = $taxscheme->appendChild($cbc);
            $item1 = $xml->createElement('cac:Item'); 
            $item1 = $CreditNoteLine->appendChild($item1);
            $cbc = $xml->createElement('cbc:Description',$detalle->cdedes); 
            $cbc = $item1->appendChild($cbc);
            $sellers = $xml->createElement('cac:SellersItemIdentification'); 
            $sellers = $item1->appendChild($sellers);
            $cbc = $xml->createElement('cbc:ID',$detalle->procod); 
            $cbc = $sellers->appendChild($cbc);
            
            // precio sin igv ejm 83.05
            $price = $xml->createElement('cac:Price'); 
            $price = $CreditNoteLine->appendChild($price);
            $cbc = $xml->createElement('cbc:PriceAmount', $detalle->cdepve); 
            $cbc = $price->appendChild($cbc); $cbc->setAttribute('currencyID',$mondoc);

      }elseif($tdocod =='08'){ 

            $DebitNoteLine = $xml->createElement('cac:DebitNoteLine'); 
            $DebitNoteLine = $DebitNote->appendChild($DebitNoteLine);
            $cbc = $xml->createElement('cbc:ID', $i); 
            $cbc = $DebitNoteLine->appendChild($cbc);
            $cbc = $xml->createElement('cbc:DebitedQuantity',$detalle->cdecan); 
            $cbc = $DebitNoteLine->appendChild($cbc); 
            $cbc->setAttribute('unitCode', "NIU"); // cantidad x item:  1
            $cbc = $xml->createElement('cbc:LineExtensionAmount',$detalle->cdevun); 
            $cbc = $DebitNoteLine->appendChild($cbc); 
            $cbc->setAttribute('currencyID', $mondoc);
            
            // precio unitario del producto con igv
            $pricing = $xml->createElement('cac:PricingReference'); 
            $pricing = $DebitNoteLine->appendChild($pricing);
            $cac = $xml->createElement('cac:AlternativeConditionPrice'); 
            $cac = $pricing->appendChild($cac);
            
            // precio unitario con igv
            $cbc = $xml->createElement('cbc:PriceAmount',$detalle->cdepuni); 
            $cbc = $cac->appendChild($cbc); $cbc->setAttribute('currencyID',$mondoc);
            
            // 01 con igv, 02 operaciones no onerosas
            $cbc = $xml->createElement('cbc:PriceTypeCode','01'); 
            $cbc = $cac->appendChild($cbc);
            // igv del total del producto aplicado ya el descuento *0.18
            $taxtotal = $xml->createElement('cac:TaxTotal'); 
            $taxtotal = $DebitNoteLine->appendChild($taxtotal);
            $cbc = $xml->createElement('cbc:TaxAmount', $detalle->cdeigv); 
            $cbc = $taxtotal->appendChild($cbc); $cbc->setAttribute('currencyID',$mondoc);
            $taxtsubtotal = $xml->createElement('cac:TaxSubtotal'); 
            $taxtsubtotal = $taxtotal->appendChild($taxtsubtotal);
            $cbc = $xml->createElement('cbc:TaxAmount',$detalle->cdeigv); 
            $cbc = $taxtsubtotal->appendChild($cbc); $cbc->setAttribute('currencyID',$mondoc);
            $taxtcategory = $xml->createElement('cac:TaxCategory'); 
            $taxtcategory = $taxtsubtotal->appendChild($taxtcategory);
            $cbc = $xml->createElement('cbc:TaxExemptionReasonCode',$detalle->tigcod); 
            $cbc = $taxtcategory->appendChild($cbc);
            $taxscheme = $xml->createElement('cac:TaxScheme'); 
            $taxscheme = $taxtcategory->appendChild($taxscheme);
            $cbc = $xml->createElement('cbc:ID',$tiptri); 
            $cbc = $taxscheme->appendChild($cbc);
            $cbc = $xml->createElement('cbc:Name','IGV'); 
            $cbc = $taxscheme->appendChild($cbc);
            $cbc = $xml->createElement('cbc:TaxTypeCode','VAT'); 
            $cbc = $taxscheme->appendChild($cbc);
            $item1 = $xml->createElement('cac:Item'); 
            $item1 = $DebitNoteLine->appendChild($item1);
            $cbc = $xml->createElement('cbc:Description',$detalle->cdedes); 
            $cbc = $item1->appendChild($cbc);
            $sellers = $xml->createElement('cac:SellersItemIdentification'); 
            $sellers = $item1->appendChild($sellers);
            $cbc = $xml->createElement('cbc:ID',$detalle->procod); 
            $cbc = $sellers->appendChild($cbc);
            
            // precio sin igv ejm 83.05
            $price = $xml->createElement('cac:Price'); 
            $price = $DebitNoteLine->appendChild($price);
            $cbc = $xml->createElement('cbc:PriceAmount',$detalle->cdepve); 
            $cbc = $price->appendChild($cbc); 
            $cbc->setAttribute('currencyID',$mondoc);
      }

          }


        $xml->formatOutput = true;
        $strings_xml = $xml->saveXML();
        
      

        $xml->save($rucemp.'-'.$tdocod.'-'.$serdoc.'-'.$numdoc.'.xml');

        $codfact = $cabecera->IdCpe_cabecera; 

        $nomfilexml = $rucemp.'-'.$tdocod.'-'.$serdoc.'-'.$numdoc;
        $xmlPath = $nomfilexml.'.xml' ;
       

        $certPath = 'certificadoestudiocontable.pem'; // Convertir pfx to pem 


        $xmlDocument = new DOMDocument();
        $xmlDocument->load($xmlPath);
        $xmlTool = new SignedXml();
        $xmlTool->setCertificateFromFile($certPath);
        $xmlTool->sign($xmlDocument);
        $content = $xmlDocument->saveXML();
        


        $arch = fopen ($xmlPath, "w+");
        fwrite($arch,"");
        fclose($arch);

        file_put_contents($xmlPath,$content);
 
  
        $zipname = $nomfilexml.".zip";
        $zip = new ZipArchive;
        $zip->open($zipname, ZipArchive::CREATE);
        $zip->addFile($xmlPath,$xmlPath);
        $zip->close();

        $contenido = file_get_contents($zipname);
        $contentFile = base64_encode($contenido);

        $usu = $empresa->wsusuario;
        $pass = $empresa->wscontrasena;

           $xmlstring = '<?xml version="1.0" encoding="UTF-8"?><SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:math="http://exslt.org/math">
    <SOAP-ENV:Header>
        <Security xmlns="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">
            <UsernameToken>
                <Username>'.$empresa->IdEmpresa.$usu.'</Username>
                <Password>'.$pass.'</Password>
            </UsernameToken>
        </Security>
    </SOAP-ENV:Header>
    <SOAP-ENV:Body xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd" xmlns:ns2="http://service.sunat.gob.pe" wsu:Id="47e96d2b-4153-44a5-90a7-5b03b46aaf25">
        <ns2:sendBill xmlns:ns2="http://service.sunat.gob.pe">
                <fileName>'.$zipname.'.zip</fileName>
                <contentFile>'.base64_encode(file_get_contents($zipname)).'</contentFile>
    </ns2:sendBill>
    </SOAP-ENV:Body>
</SOAP-ENV:Envelope>';



      $wsdl = "http://calidad.escondatagate.net/wsValidator/ol-ti-itcpe/billService.wsdl";

      $result = self::soapCall($wsdl, $callFunction = "sendBill", $xmlstring);

    /*      //Monto total en letras
         $totalletras= MontoLetras::convertir(number_format($cabecera->ccaitv,'2','.',','), $monnom,'CENTIMOS');
 
        //Guardar en una variable el nombre del archivo cab
        $cabfile =  $rucemp.'-'.$cabecera->tdocod.'-'.$cabecera->serdoc.'-'.$numdoc.'.json'; 

        //consultar datos de la empresa emisora
        $datemp = DB::table("empresa")->where("IdEmpresa","=",$rucemp)->first();
        //Crear el archivo cab e insertar el contenido
        //$archivo = fopen($raiz.$cabfile, "a");
       
        $data_json = json_encode($data,JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        $archivo = fopen($cabfile, "a");
        fputs($archivo,$data_json);
        fclose($archivo);

        $leer_respuesta = self::webserviceonline($data_json,$cabfile);
      
        $codfact = $cabecera->IdCpe_nota;
        $hash= DB::tABLE('cpe_nota')->select('codhash')->where('IdCpe_nota','=',$codfact)->first();

      
        $detpdf= DB::tABLE('cpe_nota_detalle as d')->join('cpe_nota as n','n.IdCpe_nota','=','d.IdCpe_nota')->where('n.IdCpe_nota','=',$codfact)->get();

        $nompdffile =  $rucemp.'-'.$cabecera->tdocod.'-'.$cabecera->serdoc.'-'.$numdoc.'.pdf'; 
        $rutpdfile = public_path().'/'.$rucemp.'/';
  if (file_exists($rutpdfile))
    {
        if($tdocod=='07'){
          $view = \View::make('empresas.comprobantes.'.$rucemp.'.notacreditopdf', compact('serdoc','numdoc','clinom','clinum','fecemi','clidir','monnom','hash','motivo','detpdf','empresa','cabecera','desnota','serdocmod','numdocmod','tdomod'));
        }elseif($tdocod=='08'){
          $view = \View::make('empresas.comprobantes.'.$rucemp.'.notadebitopdf', compact('serdoc','numdoc','clinom','clinum','fecemi','clidir','monnom','hash','motivo','detpdf','empresa','cabecera','desnota','serdocmod','numdocmod','tdomod'));
        }

        $pdf = \App::make('dompdf.wrapper');
        $contenido = $view->render();
        $pdf->loadHTML($view)->setPaper('A4', 'lanscape')->save($rutpdfile.$nompdffile);
        //$pdf->loadHTML($view)->setPaper('A4', 'lanscape')->save($nompdffile);

    }


    //if($leer_respuesta['responseCode']=='0'){
      $cabfactura = cpe_nota::findOrFail($codfact);
      $cabfactura->ccasunrescod = $leer_respuesta['responseCode'];
      $cabfactura->ccasunnot = $leer_respuesta['responseContent'];
      $cabfactura->update();
    //} 
    */
       // return Redirect::to('/SisFact')->with('success',$leer_respuesta['responseContent'].'-'.$leer_respuesta['responseCode']);

      return Redirect::to('/SisFact');
    }



    //NOTA DE CREDITO Y DÉBITO

    public function tiponotacd($tdocod=0,$idcabecera=0,$ncdcod){
      $rucemp = Auth::user()->IdEmpresa;
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
      $rucemp = trim(Auth::user()->IdEmpresa);

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

      $rucemp = Auth::user()->IdEmpresa;
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
      $rucemp = trim(Auth::user()->IdEmpresa);

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
        if($ncdcod=='07'){
            if($tipnot=='04' || $tipnot=='08' || $tipnot=='10'){
                
              return view('empresas.comprobantes.emitirnota2',['cabecera'=>$cabecera,'detalle'=>$detalle,'senuncd'=>$senuncd,'igv'=>$igv,'monedas'=>$monedas,'unidades'=>$unidades,'operaciones'=>$operaciones,'docidentidad'=>$docidentidad,'clientes'=>$clientes,'tdocod'=>$tdocod,'ncdcod'=>$ncdcod,'productos'=>$productos,'doccomprobante'=>$doccomprobante,'nota'=>$nota]);
            }else{
                return view('empresas.comprobantes.emitirnota',['cabecera'=>$cabecera,'detalle'=>$detalle,'senuncd'=>$senuncd,'igv'=>$igv,'monedas'=>$monedas,'unidades'=>$unidades,'operaciones'=>$operaciones,'docidentidad'=>$docidentidad,'clientes'=>$clientes,'tdocod'=>$tdocod,'ncdcod'=>$ncdcod,'productos'=>$productos,'doccomprobante'=>$doccomprobante,'nota'=>$nota]);
            }
        }elseif($ncdcod=='08'){
            if($tipnot=='01' || $tipnot=='03'){
                
              return view('empresas.comprobantes.emitirnota',['cabecera'=>$cabecera,'detalle'=>$detalle,'senuncd'=>$senuncd,'igv'=>$igv,'monedas'=>$monedas,'unidades'=>$unidades,'operaciones'=>$operaciones,'docidentidad'=>$docidentidad,'clientes'=>$clientes,'tdocod'=>$tdocod,'ncdcod'=>$ncdcod,'productos'=>$productos,'doccomprobante'=>$doccomprobante,'nota'=>$nota]);
            }else{
                return view('empresas.comprobantes.emitirnota',['cabecera'=>$cabecera,'detalle'=>$detalle,'senuncd'=>$senuncd,'igv'=>$igv,'monedas'=>$monedas,'unidades'=>$unidades,'operaciones'=>$operaciones,'docidentidad'=>$docidentidad,'clientes'=>$clientes,'tdocod'=>$tdocod,'ncdcod'=>$ncdcod,'productos'=>$productos,'doccomprobante'=>$doccomprobante,'nota'=>$nota]);
            }
        }
    }

    //Comunicación de baja desde el menú Comprobantes
    public function bajacomprobante(){
      $rucemp = Auth::user()->IdEmpresa;
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

    //Comunicación de baja desde el listado de comprobantes
    public function formbajacomprobante($serdoc,$numdoc,$tdocod,$fecemi,$tdodes){
      $rucemp = Auth::user()->IdEmpresa;
      $fecact = date('Y-m-d');
      $inicomp = substr($serdoc,0, 1);

      if($inicomp =='B'){
        $numbaj = DB::tABLE('empresa')->select('IdEmpresa','RcnuEmpresa','FecRc')->where('IdEmpresa','=',$rucemp)->first(); 
        if($numbaj->FecRc==$fecact){
          $cor=$numbaj->RcnuEmpresa+1;
        }else{
          $cabcomp = empresa::findOrFail($numbaj->IdEmpresa);
          $cabcomp->FecRc = $fecact;
          $cabcomp->BanuEmpresa = 0;
          $cabcomp->update();
          $cor = $cabcomp->BanuEmpresa+1;
        }
      }elseif($inicomp == 'F'){
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
      }
      

      if($tdocod=="01" || $tdocod=="03"){
        $comp = DB::tABLE('cpe_cabecera as cab')
        ->join('moneda as mon','cab.moncod','=','mon.moncod')
        ->where('serdoc','=',$serdoc)
        ->where('numdoc','=',$numdoc)
        ->where('IdEmpresa','=',$rucemp)->first();
      }elseif($tdocod=="07" || $tdocod=="08"){
        $comp = DB::tABLE('cpe_nota as nota')
        ->join('moneda as mon','nota.moncod','=','mon.moncod')
        ->where('serdoc','=',$serdoc)
        ->where('numdoc','=',$numdoc)
        ->where('IdEmpresa','=',$rucemp)->first();
      }

      $sernumdoc = $serdoc.'-'.$numdoc;
      return view('empresas.comprobantes.emitirbaja',['cor'=>$cor,'sernumdoc'=>$sernumdoc,'tdodes'=>$tdodes,'tdocod'=>$tdocod,'fecemi'=>$fecemi,'monnom'=>$comp->monnom,'ccaitv'=>$comp->ccaitv]);
    }


    public function registrarbajacomprobante(Request $request){
      $rucemp = Auth::user()->IdEmpresa;
      $serdocbaja = $request->get('serdocbaja');
      $fecbaj = $request->get('fecbaj'); //Fecha de generación del documento de baja
      $numbaj = $request->get('numbaj');
      $numbajmod = str_pad($numbaj,3,"0", STR_PAD_LEFT);
      $obser = $request->get('obser');
      $fecemi = $request->get('fecemi'); //fecha de emisión del documento que se dará de baja
      $tdomod = $request->get('tdomod');
      $tdocod = $request->get('tdo_cod');
      $inicomp = substr($serdocbaja,0, 1);
      $empresa = Empresa::findOrFail($rucemp);

     
      if($inicomp =='B'){
       
        
        
        $nomfilexml = $rucemp.'-RC'.'-'.str_replace("-", "", $fecbaj).'-'.$numbaj;
        $numeracion = 'RC'.'-'.str_replace("-", "", $fecbaj).'-'.$numbaj;
        $xmlPath = $nomfilexml.'.xml' ;

        $docbaja = new cpe_baja;
        $sernumbaja =$serdocbaja; 
        $ser = substr($sernumbaja,strpos($sernumbaja,'-')-4,4);
        $num = substr($sernumbaja,strpos($sernumbaja,'-')+1,8);
        $numdoc = str_pad($num,8,"0", STR_PAD_LEFT);

       

        $docbaja->cbanum =  $ser.'-'.$numdoc;
        $docbaja->cbacor =  $numbaj; 
        $docbaja->cbamot =  $obser; 
        $docbaja->cbdfco =  $fecbaj; 
        $docbaja->cbafec =  $fecemi; 
        $docbaja->tdocod =  $tdocod; 
        $docbaja->IdEmpresa =  $rucemp; 

        if($tdocod=='03'){
          $cabecera= DB::tABLE('cpe_cabecera')->where('serdoc','=',$ser)->where('numdoc','=',$numdoc)->where('tdocod','=',$tdocod)->first();
          $docbaja->IdCpe_cabecera = $cabecera->IdCpe_cabecera; 
          $cabcomp = cpe_cabecera::findOrFail($cabecera->IdCpe_cabecera);
          $cabcomp->ccabaj = str_replace("-", "", $fecbaj).'-'.$numbajmod;
          $cabcomp->update();

          //$detalle= DB::tABLE('cpe_detalle')->where('IdCpe_cabecera','=',$cabecera->IdCpe_cabecera)->get();

        }elseif($tdocod=='07' || $tdocod=='08'){
          $cabecera= DB::tABLE('cpe_nota')->select('IdCpe_nota','serdoc','numdoc')->where('serdoc','=',$ser)->where('numdoc','=',$numdoc)->where('tdocod','=',$tdocod)->first();
          $docbaja->IdCpe_cabecera = $cabecera->IdCpe_nota; 
          $cabcomp = cpe_nota::findOrFail($cabecera->IdCpe_nota);
          $cabcomp->ccabaj = str_replace("-", "", $fecbaj).'-'.$numbajmod;
          $cabcomp->update();

          //$detalle= DB::tABLE('cpe_nota_detalle')->where('IdCpe_nota','=',$cabecera->IdCpe_nota)->get();

        }

         $cliente = DB::tABLE('cliente')->where('clinum',$cabecera->ccandi)->where('rucemp',$rucemp)->first();

        $compbaj = $cabecera->serdoc.'-'.str_pad($cabecera->numdoc,8,"0", STR_PAD_LEFT);
        

          $datemp = DB::table("empresa")->where("IdEmpresa","=",$rucemp)->first();

         
          
           /* $xml = new DomDocument('1.0', 'ISO-8859-1');
            $xml->standalone         = false;
            $xml->preserveWhiteSpace = false;
            $Invoice = $xml->createElement('p:SummaryDocuments'); 
            $Invoice = $xml->appendChild($Invoice);
            $Invoice->setAttribute('xmlns:p',"urn:sunat:names:specification:ubl:peru:schema:xsd:SummaryDocuments-1" );
            $Invoice->setAttribute('xmlns:cbc',"urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2");
            $Invoice->setAttribute('xmlns:cac',"urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2");
            $Invoice->setAttribute('xmlns:ext',"urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2");
            $Invoice->setAttribute('xmlns:sac',"urn:sunat:names:specification:ubl:peru:schema:xsd:SunatAggregateComponents-1");
  
          //$Invoice->setAttribute('xsi:schemaLocation','urn:sunat:names:specification:ubl:peru:schema:xsd:InvoiceSummary-1 D:\UBL_SUNAT\SUNAT_xml_20110112\20110112\xsd\maindoc\UBLPE-InvoiceSummary-1.0.xsd');
            $UBLExtension = $xml->createElement('ext:UBLExtensions'); 
            $UBLExtension = $Invoice->appendChild($UBLExtension);
            $ext = $xml->createElement('ext:UBLExtension'); 
            $ext = $UBLExtension->appendChild($ext);
            $contents = $xml->createElement('ext:ExtensionContent'); 
            $contents = $ext->appendChild($contents);
            $cbc = $xml->createElement('cbc:UBLVersionID', '2.0'); 
            $cbc = $Invoice->appendChild($cbc);
            $cbc = $xml->createElement('cbc:CustomizationID', '1.1'); 
            $cbc = $Invoice->appendChild($cbc);
            $cbc = $xml->createElement('cbc:ID',$numeracion); 
            $cbc = $Invoice->appendChild($cbc);
            $cbc = $xml->createElement('cbc:ReferenceDate',$fecbaj); 
            $cbc = $Invoice->appendChild($cbc);
            $cbc = $xml->createElement('cbc:IssueDate',$fecbaj); 
            $cbc = $Invoice->appendChild($cbc);
            $cbc = $xml->createElement('cbc:Note','CONSOLIDADO DE BOLETAS DE VENTA'); 
            $cbc = $Invoice->appendChild($cbc);

            // signature
                $cac_signature = $xml->createElement('cac:Signature'); 
                $cac = $Invoice->appendChild($cac_signature);
                $cbc = $xml->createElement('cbc:ID',$numeracion); 
                $cbc = $cac_signature->appendChild($cbc);
                $cac_signatory = $xml->createElement('cac:SignatoryParty');
                $cac_signatory = $cac_signature->appendChild($cac_signatory);
                $cac = $xml->createElement('cac:PartyIdentification'); 
                $cac = $cac_signatory->appendChild($cac);
                $cbc = $xml->createElement('cbc:ID', $empresa->IdEmpresa); 
                $cbc = $cac->appendChild($cbc);
                $cac = $xml->createElement('cac:PartyName');
                $cac = $cac_signatory->appendChild($cac);
                $cbc = $xml->createElement('cbc:Name', $empresa->NomEmpresa); 
                $cbc = $cac->appendChild($cbc);
                $cac_digital = $xml->createElement('cac:DigitalSignatureAttachment'); 
                $cac_digital = $cac_signature->appendChild($cac_digital);
                $cac = $xml->createElement('cac:ExternalReference'); 
                $cac = $cac_digital->appendChild($cac);
                $cbc = $xml->createElement('cbc:URI',$numeracion); 
                $cbc = $cac->appendChild($cbc); 

          // Datos Surmotriz
                $cac_accounting = $xml->createElement('cac:AccountingSupplierParty'); 
                $cac_accounting = $Invoice->appendChild($cac_accounting);
                $cbc = $xml->createElement('cbc:CustomerAssignedAccountID',$empresa->IdEmpresa); 
                $cbc = $cac_accounting->appendChild($cbc);
                $cbc = $xml->createElement('cbc:AdditionalAccountID',$empresa->TipDoc); 
                $cbc = $cac_accounting->appendChild($cbc);
                $cac_party = $xml->createElement('cac:Party'); 
                $cac_party = $cac_accounting->appendChild($cac_party);
                $legal = $xml->createElement('cac:PartyLegalEntity'); 
                $legal = $cac_party->appendChild($legal);
                $cbc = $xml->createElement('cbc:RegistrationName',$empresa->NomEmpresa); 
                $cbc = $legal->appendChild($cbc); */


                $xml = new DomDocument('1.0', 'UTF-8');
                $xml->standalone         = false;
                $xml->preserveWhiteSpace = false;
                $Summary = $xml->appendChild($xml->createElement('p:SummaryDocuments'));
                $Summary->setAttribute('xmlns:p', 'urn:sunat:names:specification:ubl:peru:schema:xsd:SummaryDocuments-1');
                $Summary->setAttribute('xmlns:ext', 'urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2');
                $Summary->setAttribute('xmlns:cbc', 'urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
                $Summary->setAttribute('xmlns:cac', 'urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2');
                $Summary->setAttribute('xmlns:sac', 'urn:sunat:names:specification:ubl:peru:schema:xsd:SunatAggregateComponents-1');
                $UBLExtensions = $Summary->appendChild($xml->createElement('ext:UBLExtensions'));
                $UBLExtension = $UBLExtensions->appendChild($xml->createElement('ext:UBLExtension'));
                $ExtensionContent = $UBLExtension->appendChild($xml->createElement('ext:ExtensionContent'));
                $UBLVersionID = $Summary->appendChild($xml->createElement('cbc:UBLVersionID','2.0'));
                $CustomizationID = $Summary->appendChild($xml->createElement('cbc:CustomizationID','1.1'));
                $ID = $Summary->appendChild($xml->createElement('cbc:ID',$numeracion));
                $ReferenceDate = $Summary->appendChild($xml->createElement('cbc:ReferenceDate',$fecbaj));
                $IssueDate = $Summary->appendChild($xml->createElement('cbc:IssueDate',$fecbaj));
                $Signature = $Summary->appendChild($xml->createElement('cac:Signature'));
                $ID = $Signature->appendChild($xml->createElement('cbc:ID','S'.$numeracion));
                $SignatoryParty = $Signature->appendChild($xml->createElement('cac:SignatoryParty'));
                $PartyIdentification = $SignatoryParty->appendChild($xml->createElement('cac:PartyIdentification'));
                $ID = $PartyIdentification->appendChild($xml->createElement('cbc:ID',$empresa->IdEmpresa));
                $PartyName = $SignatoryParty->appendChild($xml->createElement('cac:PartyName'));
                $Name = $PartyName->appendChild($xml->createElement('cbc:Name',$empresa->NomEmpresa));
                $DigitalSignatureAttachment = $Signature->appendChild($xml->createElement('cac:DigitalSignatureAttachment'));
                $ExternalReference = $DigitalSignatureAttachment->appendChild($xml->createElement('cac:ExternalReference'));
                $URI = $ExternalReference->appendChild($xml->createElement('cbc:URI','#S'.$numeracion));
                $AccountingSupplierParty = $Summary->appendChild($xml->createElement('cac:AccountingSupplierParty'));
                $CustomerAssignedAccountID = $AccountingSupplierParty->appendChild($xml->createElement('cbc:CustomerAssignedAccountID',$empresa->IdEmpresa));
                $AdditionalAccountID = $AccountingSupplierParty->appendChild($xml->createElement('cbc:AdditionalAccountID',$empresa->TipDoc));
                $Party = $AccountingSupplierParty->appendChild($xml->createElement('cac:Party'));
                $PartyLegalEntity = $Party->appendChild($xml->createElement('cac:PartyLegalEntity'));
                $PartyLegalEntity = $PartyLegalEntity->appendChild($xml->createElement('cbc:RegistrationName',$empresa->NomEmpresa));



                $SummaryDocumentsLine = $Summary->appendChild($xml->createElement('sac:SummaryDocumentsLine'));
                $LineID = $SummaryDocumentsLine->appendChild($xml->createElement('cbc:LineID','1'));
                $DocumentTypeCode = $SummaryDocumentsLine->appendChild($xml->createElement('cbc:DocumentTypeCode',$tdocod));
                $ID = $SummaryDocumentsLine->appendChild($xml->createElement('cbc:ID',$ser.'-'.$numdoc));
                $AccountingCustomerParty = $SummaryDocumentsLine->appendChild($xml->createElement('cac:AccountingCustomerParty'));
                $CustomerAssignedAccountID = $AccountingCustomerParty->appendChild($xml->createElement('cbc:CustomerAssignedAccountID',$cliente->clinum));
                $AdditionalAccountID = $AccountingCustomerParty->appendChild($xml->createElement('cbc:AdditionalAccountID',$cliente->tdicod));
                
               /* if($cbc_DocumentTypeCode=='07'){ // Nota Credito
                    $BillingReference = $SummaryDocumentsLine->appendChild($xml->createElement('cac:BillingReference'));
                        $InvoiceDocumentReference = $BillingReference->appendChild($xml->createElement('cac:InvoiceDocumentReference'));
                            $ID = $InvoiceDocumentReference->appendChild($xml->createElement('cbc:ID',$serienumero_ref));
                            $DocumentTypeCode = $InvoiceDocumentReference->appendChild($xml->createElement('cbc:DocumentTypeCode','03'));
                }*/

                $Status = $SummaryDocumentsLine->appendChild($xml->createElement('cac:Status'));
                $ConditionCode = $Status->appendChild($xml->createElement('cbc:ConditionCode','3'));
                $TotalAmount = $SummaryDocumentsLine->appendChild($xml->createElement('sac:TotalAmount',$cabecera->ccaitv)); 
                $TotalAmount->setAttribute('currencyID', 'PEN');

                //if($gravadas!='0.00'){
                    $BillingPayment = $SummaryDocumentsLine->appendChild($xml->createElement('sac:BillingPayment'));
                    $PaidAmount = $BillingPayment->appendChild($xml->createElement('cbc:PaidAmount',$cabecera->ccatvg)); 
                    $PaidAmount->setAttribute('currencyID', 'PEN');
                    $InstructionID = $BillingPayment->appendChild($xml->createElement('cbc:InstructionID','01'));

                    $BillingPayment = $SummaryDocumentsLine->appendChild($xml->createElement('sac:BillingPayment'));
                    $PaidAmount = $BillingPayment->appendChild($xml->createElement('cbc:PaidAmount',$cabecera->ccatve)); 
                    $PaidAmount->setAttribute('currencyID', 'PEN');
                    $InstructionID = $BillingPayment->appendChild($xml->createElement('cbc:InstructionID','02'));

                    $BillingPayment = $SummaryDocumentsLine->appendChild($xml->createElement('sac:BillingPayment'));
                    $PaidAmount = $BillingPayment->appendChild($xml->createElement('cbc:PaidAmount',$cabecera->ccatvi)); 
                    $PaidAmount->setAttribute('currencyID', 'PEN');
                    $InstructionID = $BillingPayment->appendChild($xml->createElement('cbc:InstructionID','03'));
               // }
                
                $otrostri = $SummaryDocumentsLine->appendChild($xml->createElement('cac:AllowanceCharge'));
                $charge = $otrostri->appendChild($xml->createElement('cbc:ChargeIndicator','true')); 
                $otrostriamount = $otrostri->appendChild($xml->createElement('cbc:Amount','0.00')); 
                $otrostriamount->setAttribute('currencyID', 'PEN');

                $TaxTotal = $SummaryDocumentsLine->appendChild($xml->createElement('cac:TaxTotal'));
                $TaxAmount = $TaxTotal->appendChild($xml->createElement('cbc:TaxAmount',$cabecera->ccaigv)); 
                $TaxAmount->setAttribute('currencyID', 'PEN');
                
                $TaxSubtotal = $TaxTotal->appendChild($xml->createElement('cac:TaxSubtotal'));
                $TaxAmount = $TaxSubtotal->appendChild($xml->createElement('cbc:TaxAmount',$cabecera->ccaigv)); 
                $TaxAmount->setAttribute('currencyID', 'PEN');
                
                $TaxCategory = $TaxSubtotal->appendChild($xml->createElement('cac:TaxCategory'));
                $TaxScheme = $TaxCategory->appendChild($xml->createElement('cac:TaxScheme'));
                $ID = $TaxScheme->appendChild($xml->createElement('cbc:ID','1000'));
                $Name = $TaxScheme->appendChild($xml->createElement('cbc:Name','IGV'));
                $TaxTypeCode = $TaxScheme->appendChild($xml->createElement('cbc:TaxTypeCode','VAT'));

                

          



    /*
                  $SummaryDocumentsLine = $xml->createElement('sac:SummaryDocumentsLine'); 
                  $SummaryDocumentsLine = $Invoice->appendChild($SummaryDocumentsLine);
                  $cbc = $xml->createElement('cbc:LineID','1'); 
                  $cbc = $SummaryDocumentsLine->appendChild($cbc);
                  $cbc = $xml->createElement('cbc:DocumentTypeCode',$tdocod); 
                  $cbc = $SummaryDocumentsLine->appendChild($cbc);
                  $cbc = $xml->createElement('cbc:ID',$ser.'-'.$numdoc); 
                  $cbc = $SummaryDocumentsLine->appendChild($cbc);
            
                  $AccountingCustomerParty = $xml->createElement('cac:AccountingCustomerParty'); 
                  $AccountingCustomerParty  = $SummaryDocumentsLine->appendChild($AccountingCustomerParty);
                  $cbc = $xml->createElement('cbc:CustomerAssignedAccountID',$cliente->clinum); 
                  $cbc = $AccountingCustomerParty->appendChild($cbc);
                //  $cbc = $xml->createElement('cbc:AdditionalAccountID',$cliente->tdicod); 
                //  $cbc = $AccountingCustomerParty->appendChild($cbc);

                  $status = $xml->createElement('cac:status'); 
                  $status  = $SummaryDocumentsLine->appendChild($status);
                  $cbc = $xml->createElement('cbc:ConditionCode','3'); 
                  $cbc = $status->appendChild($cbc);

                  $sac = $xml->createElement('sac:TotalAmount',$cabecera->ccaitv); 
                  $sac = $SummaryDocumentsLine->appendChild($sac);
                  $sac->setAttribute('currencyID','PEN');
         
           
            // Total ISC
          /*  $TaxTotal = $xml->createElement('cac:TaxTotal'); 
            $TaxTotal =  $SummaryDocumentsLine->appendChild($TaxTotal);
            $cbc = $xml->createElement('cbc:TaxAmount','0.00'); 
            $cbc = $TaxTotal->appendChild($cbc); $cbc->setAttribute('currencyID',"PEN");
            $cac = $xml->createElement('cac:TaxSubtotal'); 
            $cac = $TaxTotal->appendChild($cac);
            $cbc = $xml->createElement('cbc:TaxAmount','0.00'); 
            $cbc = $cac->appendChild($cbc); $cbc->setAttribute('currencyID',"PEN");
            $TaxCategory = $xml->createElement('cac:TaxCategory'); 
            $TaxCategory = $cac->appendChild($TaxCategory);
            $TaxScheme = $xml->createElement('cac:TaxScheme'); 
            $TaxScheme = $TaxCategory->appendChild($TaxScheme);
            $cbc = $xml->createElement('cbc:ID','2000'); 
            $cbc = $TaxScheme->appendChild($cbc);
            $cbc = $xml->createElement('cbc:Name','ISC'); 
            $cbc = $TaxScheme->appendChild($cbc);
            $cbc = $xml->createElement('cbc:TaxTypeCode','EXC'); 
            $cbc = $TaxScheme->appendChild($cbc);*/
            
            // Total IGV
       /*     $TaxTotal = $xml->createElement('cac:TaxTotal'); 
            $TaxTotal =  $SummaryDocumentsLine->appendChild($TaxTotal);
                $cbc = $xml->createElement('cbc:TaxAmount', $cabecera->ccaigv); 
                $cbc = $TaxTotal->appendChild($cbc); 
                $cbc->setAttribute('currencyID',"PEN");

                $cac = $xml->createElement('cac:TaxSubtotal'); 
                $cac = $TaxTotal->appendChild($cac);
                $cbc = $xml->createElement('cbc:TaxAmount', $cabecera->ccaigv); 
                $cbc = $cac->appendChild($cbc); $cbc->setAttribute('currencyID',"PEN");
                $TaxCategory = $xml->createElement('cac:TaxCategory'); 
                $TaxCategory = $cac->appendChild($TaxCategory);
                $TaxScheme = $xml->createElement('cac:TaxScheme'); 
                $TaxScheme = $TaxCategory->appendChild($TaxScheme);
                $cbc = $xml->createElement('cbc:ID','1000'); 
                $cbc = $TaxScheme->appendChild($cbc);
                $cbc = $xml->createElement('cbc:Name','IGV'); 
                $cbc = $TaxScheme->appendChild($cbc);
                $cbc = $xml->createElement('cbc:TaxTypeCode','VAT'); 
                $cbc = $TaxScheme->appendChild($cbc);
*/
            // Total Otros tributos
           /* $TaxTotal = $xml->createElement('cac:TaxTotal'); 
            $TaxTotal =  $SummaryDocumentsLine->appendChild($TaxTotal);
            $cbc = $xml->createElement('cbc:TaxAmount','0.00'); 
            $cbc = $TaxTotal->appendChild($cbc); 
            $cbc->setAttribute('currencyID',"PEN");
            $cac = $xml->createElement('cac:TaxSubtotal'); 
            $cac = $TaxTotal->appendChild($cac);
            $cbc = $xml->createElement('cbc:TaxAmount','0.00'); 
            $cbc = $cac->appendChild($cbc); 
            $cbc->setAttribute('currencyID',"PEN");
            $TaxCategory = $xml->createElement('cac:TaxCategory'); 
            $TaxCategory = $cac->appendChild($TaxCategory);
            $TaxScheme = $xml->createElement('cac:TaxScheme'); 
            $TaxScheme = $TaxCategory->appendChild($TaxScheme);
            $cbc = $xml->createElement('cbc:ID','9999'); 
            $cbc = $TaxScheme->appendChild($cbc);
            $cbc = $xml->createElement('cbc:Name','OTROS'); 
            $cbc = $TaxScheme->appendChild($cbc);
            $cbc = $xml->createElement('cbc:TaxTypeCode','OTH'); 
            $cbc = $TaxScheme->appendChild($cbc);*/
            
            $docbaja->TipoBaja = "RC";
            $docbaja->save();
            $cabcomp->update();
            $empresa = Empresa::findOrFail($rucemp);
            $empresa->RcnuEmpresa = $numbaj;
            $empresa->update();


      }elseif($inicomp == 'F'){
        $nomfilexml = $rucemp.'-RA'.'-'.str_replace("-", "", $fecbaj).'-'.$numbaj;
        $numeracion = 'RA'.'-'.str_replace("-", "", $fecbaj).'-'.$numbaj;
        $xmlPath = $nomfilexml.'.xml' ;

        $docbaja = new cpe_baja;
        $sernumbaja =$serdocbaja; 
        $ser = substr($sernumbaja,strpos($sernumbaja,'-')-4,4);
        $num = substr($sernumbaja,strpos($sernumbaja,'-')+1,8);
        $numdoc = str_pad($num,8,"0", STR_PAD_LEFT);

        $docbaja->cbanum =  $ser.'-'.$numdoc;
        $docbaja->cbacor =  $numbaj; 
        $docbaja->cbamot =  $obser; 
        $docbaja->cbdfco =  $fecbaj; 
        $docbaja->cbafec =  $fecemi; 
        $docbaja->tdocod =  $tdocod; 
        $docbaja->IdEmpresa =  $rucemp; 

        if($tdocod=='01'){
          $cabecera= DB::tABLE('cpe_cabecera')->select('IdCpe_cabecera','serdoc','numdoc')->where('serdoc','=',$ser)->where('numdoc','=',$numdoc)->first();
          $docbaja->IdCpe_cabecera = $cabecera->IdCpe_cabecera; 
          $cabcomp = cpe_cabecera::findOrFail($cabecera->IdCpe_cabecera);
          $cabcomp->ccabaj = str_replace("-", "", $fecbaj).'-'.$numbajmod;
          $cabcomp->update();
        }elseif($tdocod=='07' || $tdocod=='08'){
          $cabecera= DB::tABLE('cpe_nota')->select('IdCpe_nota','serdoc','numdoc')->where('serdoc','=',$ser)->where('numdoc','=',$numdoc)->first();
          $docbaja->IdCpe_cabecera = $cabecera->IdCpe_nota; 
          $cabcomp = cpe_nota::findOrFail($cabecera->IdCpe_nota);
          $cabcomp->ccabaj = str_replace("-", "", $fecbaj).'-'.$numbajmod;
          $cabcomp->update();
        }

          // INICIO JSON

       
          $datemp = DB::table("empresa")->where("IdEmpresa","=",$rucemp)->first();

          
          $docbaja->TipoBaja = "RA";
          $docbaja->save();
        
          $empresa = Empresa::findOrFail($rucemp);
          $empresa->BanuEmpresa = $numbaj;
          $empresa->update();

                $xml = new DomDocument('1.0', 'ISO-8859-1'); 
                $xml->standalone = false; 
                $xml->preserveWhiteSpace = false;
                $Invoice = $xml->createElement('VoidedDocuments'); 
                $Invoice = $xml->appendChild($Invoice);
                $Invoice->setAttribute('xmlns',"urn:sunat:names:specification:ubl:peru:schema:xsd:VoidedDocuments-1");
                $Invoice->setAttribute('xmlns:cac',"urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2");
                $Invoice->setAttribute('xmlns:cbc',"urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2");
                $Invoice->setAttribute('xmlns:ds',"http://www.w3.org/2000/09/xmldsig#");
                $Invoice->setAttribute('xmlns:ext',"urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2");
                $Invoice->setAttribute('xmlns:sac',"urn:sunat:names:specification:ubl:peru:schema:xsd:SunatAggregateComponents-1");
                $Invoice->setAttribute('xmlns:xsi',"http://www.w3.org/2001/XMLSchema-instance");
                $UBLExtension = $xml->createElement('ext:UBLExtensions'); 
                $UBLExtension = $Invoice->appendChild($UBLExtension);
                $ext = $xml->createElement('ext:UBLExtension'); 
                $ext = $UBLExtension->appendChild($ext);
                $contents = $xml->createElement('ext:ExtensionContent'); 
                $contents = $ext->appendChild($contents);
                $cbc = $xml->createElement('cbc:UBLVersionID', '2.0'); 
                $cbc = $Invoice->appendChild($cbc);
                $cbc = $xml->createElement('cbc:CustomizationID', '1.0'); 
                $cbc = $Invoice->appendChild($cbc);
                $cbc = $xml->createElement('cbc:ID','RA'.'-'.str_replace("-", "", $fecbaj).'-'.$numbaj); 
                $cbc = $Invoice->appendChild($cbc);
                $cbc = $xml->createElement('cbc:ReferenceDate',$fecbaj); 
                $cbc = $Invoice->appendChild($cbc);
                $cbc = $xml->createElement('cbc:IssueDate',$fecbaj); 
                $cbc = $Invoice->appendChild($cbc);
               
                // signature
                $cac_signature = $xml->createElement('cac:Signature'); 
                $cac = $Invoice->appendChild($cac_signature);

                $cbc = $xml->createElement('cbc:ID',$empresa->IdEmpresa); 
                $cbc = $cac_signature->appendChild($cbc);
                
                $cac_signatory = $xml->createElement('cac:SignatoryParty');
                $cac_signatory = $cac_signature->appendChild($cac_signatory);
                $cac = $xml->createElement('cac:PartyIdentification'); 
                $cac = $cac_signatory->appendChild($cac);
                $cbc = $xml->createElement('cbc:ID', $empresa->IdEmpresa); 
                $cbc = $cac->appendChild($cbc);
                $cac = $xml->createElement('cac:PartyName');
                $cac = $cac_signatory->appendChild($cac);

                $cbc = $xml->createElement('cbc:Name', $empresa->NomEmpresa); 
                $cbc = $cac->appendChild($cbc);
                
                $cac_digital = $xml->createElement('cac:DigitalSignatureAttachment'); 
                $cac_digital = $cac_signature->appendChild($cac_digital);
                $cac = $xml->createElement('cac:ExternalReference'); 
                $cac = $cac_digital->appendChild($cac);
                $cbc = $xml->createElement('cbc:URI', $empresa->IdEmpresa); 
                $cbc = $cac->appendChild($cbc); 


                $cac_accounting = $xml->createElement('cac:AccountingSupplierParty'); 
                $cac_accounting = $Invoice->appendChild($cac_accounting);
                $cbc = $xml->createElement('cbc:CustomerAssignedAccountID', $empresa->IdEmpresa); 
                $cbc = $cac_accounting->appendChild($cbc);
                $cbc = $xml->createElement('cbc:AdditionalAccountID', $empresa->TipDoc); 
                $cbc = $cac_accounting->appendChild($cbc);
                $cac_party = $xml->createElement('cac:Party'); 
                $cac_party = $cac_accounting->appendChild($cac_party);
                $cac = $xml->createElement('cac:PartyName'); 
                $cac = $cac_party->appendChild($cac);
                $cbc = $xml->createElement('cbc:Name',$empresa->NomEmpresa); 
                $cbc = $cac->appendChild($cbc);
                $legal = $xml->createElement('cac:PartyLegalEntity'); 
                $legal = $cac_party->appendChild($legal);
                $cbc = $xml->createElement('cbc:RegistrationName',$empresa->NomEmpresa); 
                $cbc = $legal->appendChild($cbc);
                $VoidedDocumentsLine = $xml->createElement('sac:VoidedDocumentsLine'); 
                $VoidedDocumentsLine = $Invoice->appendChild($VoidedDocumentsLine);

                $cbc = $xml->createElement('cbc:LineID','1'); 
                $cbc = $VoidedDocumentsLine->appendChild($cbc);
                $cbc = $xml->createElement('cbc:DocumentTypeCode',$tdocod); 
                $cbc = $VoidedDocumentsLine->appendChild($cbc);
                $sac = $xml->createElement('sac:DocumentSerialID',$ser); 
                $sac = $VoidedDocumentsLine->appendChild($sac);
                $sac = $xml->createElement('sac:DocumentNumberID',$numdoc); 
                $sac = $VoidedDocumentsLine->appendChild($sac);
                $sac = $xml->createElement('sac:VoidReasonDescription',$obser); 
                $sac = $VoidedDocumentsLine->appendChild($sac);

        }

       
        $xml->formatOutput = true;
        $strings_xml = $xml->saveXML();

        $xml->save($xmlPath);
        
        $certPath = 'certificadoestudiocontable.pem'; // Convertir pfx to pem 


        $xmlDocument = new DOMDocument();
        $xmlDocument->load($xmlPath);
        $xmlTool = new SignedXml();
        $xmlTool->setCertificateFromFile($certPath);
        $xmlTool->sign($xmlDocument);
        $content = $xmlDocument->saveXML();
 
        $arch = fopen ($xmlPath, "w+");
        fwrite($arch,"");
        fclose($arch);

        file_put_contents($xmlPath, $content);

        $zipname = $nomfilexml.".zip";
        $zip = new ZipArchive;
        $zip->open($zipname, ZipArchive::CREATE);
        $zip->addFile($xmlPath);
        $zip->close();

        $contenido = file_get_contents($zipname);
        $contentFile = base64_encode($contenido);

        $usu = $empresa->wsusuario;
        $pass = $empresa->wscontrasena;

           $xmlstring = '<?xml version="1.0" encoding="UTF-8"?><SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:math="http://exslt.org/math">
    <SOAP-ENV:Header>
        <Security xmlns="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-secext-1.0.xsd">
            <UsernameToken>
                <Username>'.$empresa->IdEmpresa.$usu.'</Username>
                <Password>'.$pass.'</Password>
            </UsernameToken>
        </Security>
    </SOAP-ENV:Header>
    <SOAP-ENV:Body xmlns:wsu="http://docs.oasis-open.org/wss/2004/01/oasis-200401-wss-wssecurity-utility-1.0.xsd" xmlns:ns2="http://service.sunat.gob.pe" wsu:Id="47e96d2b-4153-44a5-90a7-5b03b46aaf25">
        <ns2:sendSummary xmlns:ns2="http://service.sunat.gob.pe">
                <fileName>'.$zipname.'.zip</fileName>
                <contentFile>'.base64_encode(file_get_contents($zipname)).'</contentFile>
    </ns2:sendSummary>
    </SOAP-ENV:Body>
</SOAP-ENV:Envelope>';



      $wsdl = "http://calidad.escondatagate.net/wsValidator/ol-ti-itcpe/billService.wsdl";

      $result = self::soapCall($wsdl, $callFunction = "sendBill", $xmlstring);
      

    
    /*   $cabfactura = cpe_baja::findOrFail($docbaja->IdCpe_baja);
        $cabfactura->respcod = $leer_respuesta['responseCode'];
        $cabfactura->respCont = $leer_respuesta['responseContent'];
        $cabfactura->ticket = $leer_respuesta['ticket'];

        $estado_ticket = self::wsestadoticket( $leer_respuesta['ticket']);
        
        $cabfactura->codigo = $estado_ticket['codigo'];
        $cabfactura->mensaje = $estado_ticket['mensaje'];
        $cabfactura->estado = $estado_ticket['statusCode'];
        $cabfactura->codresp = $estado_ticket['responseCode'];
        $cabfactura->mensresp= $estado_ticket['responseMessage'];
        $cabfactura->update();
     */

      return Redirect::to('/SisFact')->with('success');
     
    }

    public function listarnotas($idcabecera){

      $rucemp =Auth::user()->IdEmpresa;
      $notas = DB::tABLE('cpe_nota as n')->select('n.ccafem','n.serdoc','n.numdoc','tdodes','c.ccandi','c.ccanom','mn.monnom','n.ccaitv','n.tdocod','c.IdEmpresa','n.IdCpe_nota','n.tdocod','n.codhash','n.ccasunrescod','n.ccabaj')
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

    public function ingresarpanel($idempresa){

      $user =Auth::user()->IdUsuario;
      $regemp = User::findOrFail($user);
      $regemp->IdEmpresa = $idempresa;
      $regemp->update();

      return Redirect::to('/SisFact');

    }

    public function webserviceonline($data_json,$cabfile){

      $dataent = array (

          "customer"=>array(
            "username"=> "20422559711mertra02",
            "password"=> "Mertra2018*"
          ),

          "fileName"=>$cabfile,
          "fileContent"=>base64_encode($data_json)
      );

      $ent = json_encode($dataent,JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

      //return $ent;

      $ruta = "http://calidad.escondatagate.net/wsParser/rest/parserWS";
  
      $ch = curl_init();

          // Establecer URL y otras opciones apropiadas
          curl_setopt($ch, CURLOPT_URL, $ruta);
          curl_setopt($ch, CURLOPT_HTTPHEADER, array(
              'Content-Type: application/json',
            )
          );
         curl_setopt($ch, CURLOPT_POST, 1);
         curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
         curl_setopt($ch, CURLOPT_POSTFIELDS,$ent);
         curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
          // Capturar la URL y pasarla al navegador
          $respuesta = curl_exec($ch);
          $leer_respuesta = json_decode($respuesta, true);
         
          // Cerrar el recurso cURL y liberar recursos del sistema
          curl_close($ch);
           return $leer_respuesta;
        }

    public function webservicepdf($tipdoc,$serdoc,$numdoc){

      $rucemp = trim(Auth::user()->IdEmpresa);
      $dataent = array (

          "user"=>array(
            "username"=> "20422559711mertra02",
            "password"=> "Mertra2018*"
          ),

          "codCPE"=>$tipdoc,
          "numSerieCPE"=>$serdoc,
          "numCPE"=>$numdoc
      );

      $ent = json_encode($dataent,JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

      //return $ent;

      $ruta = "http://calidad.escondatagate.net/wsBackend/clients/getPdfURL";
  
      $ch = curl_init();

          // Establecer URL y otras opciones apropiadas
          curl_setopt($ch, CURLOPT_URL, $ruta);
          curl_setopt($ch, CURLOPT_HTTPHEADER, array(
              'Content-Type: application/json',
            )
          );
         curl_setopt($ch, CURLOPT_POST, 1);
         curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
         curl_setopt($ch, CURLOPT_POSTFIELDS,$ent);
         curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
          // Capturar la URL y pasarla al navegador
          $respuesta = curl_exec($ch);
          $leer_respuesta = json_decode($respuesta, true);
         
          // Cerrar el recurso cURL y liberar recursos del sistema
          curl_close($ch);
          $archivo = $rucemp.'-'.$tipdoc.'-'.$serdoc.'-'.$numdoc.'.pdf';
          $tempArchivo = tempnam(sys_get_temp_dir(), $archivo);
          
          if(!empty($leer_respuesta['pdfURL'])){
            copy($leer_respuesta['pdfURL'], $tempArchivo);
            return response()->download($tempArchivo, $archivo);  
          }else{
            return Redirect::to('/SisFact');
          }
        
         // return response()->download($leer_respuesta['pdfURL']);
                
        }

      public function wsestadoticket($ticket){

      $rucemp = trim(Auth::user()->IdEmpresa);
      $dataent = array (

          "user"=>array(
            "username"=> "20422559711mertra02",
            "password"=> "Mertra2018*"
          ),

          "ticket"=>$ticket
      );

      $ent = json_encode($dataent,JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

      //return $ent;

      $ruta = "http://calidad.escondatagate.net/wsBackend/clients/getStatus";
  
      $ch = curl_init();

          // Establecer URL y otras opciones apropiadas
          curl_setopt($ch, CURLOPT_URL, $ruta);
          curl_setopt($ch, CURLOPT_HTTPHEADER, array(
              'Content-Type: application/json',
            )
          );
         curl_setopt($ch, CURLOPT_POST, 1);
         curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
         curl_setopt($ch, CURLOPT_POSTFIELDS,$ent);
         curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
          // Capturar la URL y pasarla al navegador
          $respuesta = curl_exec($ch);
          $leer_respuesta = json_decode($respuesta, true);
         
          // Cerrar el recurso cURL y liberar recursos del sistema
          curl_close($ch);
      
          return $leer_respuesta;
         // return response()->download($leer_respuesta['pdfURL']);
                
        }
            public function consultaruc($ruc){

      $rucemp = trim(Auth::user()->IdEmpresa);
      $dataent = array (


          "ruc"=>"20600316738"
      );

      $ent = json_encode($dataent,JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

      //return $ent;

      $ruta = "http://services.wijoata.com/consultar-ruc/api/ruc/".$ruc;
  
      $ch = curl_init();

          // Establecer URL y otras opciones apropiadas
          curl_setopt($ch, CURLOPT_URL, $ruta);
          curl_setopt($ch, CURLOPT_HTTPHEADER, array(
              'Content-Type: application/json',
            )
          );
         curl_setopt($ch, CURLOPT_POST, 1);
         curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
         curl_setopt($ch, CURLOPT_POSTFIELDS,$ent);
         curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
          // Capturar la URL y pasarla al navegador
          $respuesta = curl_exec($ch);
          $leer_respuesta = json_decode($respuesta, true);
         
          // Cerrar el recurso cURL y liberar recursos del sistema
          curl_close($ch);
      
          return $leer_respuesta;
         // return response()->download($leer_respuesta['pdfURL']);
                
        }



    function soapCall($wsdlURL, $callFunction = "", $XMLString){
    $client = new feedSoap($wsdlURL, array('trace' => true));
    $reply  = $client->SoapClientCall($XMLString);
    //echo "REQUEST:\n" . $client->__getFunctions() . "\n";
    $client->__call("$callFunction", array(), array());
    //$request = prettyXml($client->__getLastRequest());
    //echo highlight_string($request, true) . "<br/>\n";
    return $client->__getLastResponse();
    //print_r($client);
}

}
 

class feedSoap extends SoapClient{
    public $XMLStr = "";
    public function setXMLStr($value){
        $this->XMLStr = $value;
    }
    public function getXMLStr(){
        return $this->XMLStr;
    }
    public function __doRequest($request, $location, $action, $version, $one_way = 0){
        $request = $this->XMLStr;
        $dom = new DOMDocument('1.0');
        try{
            $dom->loadXML($request);
        } catch (DOMException $e) {
            die($e->code);
        }
        $request = $dom->saveXML();
        //Solicitud
        return parent::__doRequest($request, $location, $action, $version, $one_way = 0);
    }
    public function SoapClientCall($SOAPXML){
        return $this->setXMLStr($SOAPXML);
    }
}