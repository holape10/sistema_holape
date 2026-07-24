<?php

namespace MasterSoft\Http\Controllers;
use Illuminate\Http\Request;
use MasterSoft\Http\Requests;
use Peru\Http\ContextClient;
use Peru\Jne\{Dni, DniParser};
use Peru\Sunat\{HtmlParser, Ruc, RucParser};
use Greenter\Model\Client\Client;
use Greenter\Model\Company\Company;
use Greenter\Model\Company\Address;
use Greenter\Model\Sale\Invoice;
use Greenter\Model\Sale\SaleDetail;
use Greenter\Model\Sale\Legend;
use Greenter\Ws\Services\SunatEndpoints;
use Greenter\See;
use Greenter\Model\Voided\Voided;
use Greenter\Model\Voided\VoidedDetail;
use Greenter\Model\Summary\Summary;
use Greenter\Model\Summary\SummaryDetail;
use Greenter\Model\Summary\SummaryPerception;
use Greenter\Model\Sale\Document;
use MasterSoft\Mail\FacturacionEmail;
use Greenter\Model\Sale\Note;
use Illuminate\Support\Facades\Mail;
use DOMDocument;
use MasterSoft\TipoIGV;
use MasterSoft\Empresa;
use MasterSoft\Cliente;
use MasterSoft\Proveedor;
use MasterSoft\presentaciones;
use MasterSoft\cpe_cabecera;
use MasterSoft\documento_relacionado;
use MasterSoft\unidad_medida;
use MasterSoft\movimientos;
use MasterSoft\cpe_detalle;
use MasterSoft\cpe_baja;
use MasterSoft\Comprobante;
use MasterSoft\EmpresaNegocios;
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
use MasterSoft\guias_remision;
use MasterSoft\guias_remision_detalle;
use QR_Code\Exceptions\InvalidVCardAddressEntryException;
use QR_Code\Exceptions\InvalidVCardPhoneEntryException;
use QR_Code\Types\QR_CalendarEvent;
use QR_Code\Types\QR_EmailMessage;
use QR_Code\Types\QR_meCard;
use QR_Code\Types\QR_Phone;
use QR_Code\Types\QR_Sms;
use QR_Code\Types\QR_Text;
use QR_Code\Types\QR_Url;
use QR_Code\Types\QR_VCard;
use QR_Code\Types\QR_WiFi;
use QR_Code\Types\vCard\Person;
use QR_Code\Types\vCard\Phone;
use \Peru\Sunat\RucFactory;
use Peru\Jne\DniFactory;
use DB;
use Hash;
use PDF;
use Config;

class ComprobantesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */


     public function configuracion(){
    
      $rucemp = trim(Auth::user()->IdEmpresa);
      $empresa = Empresa::findOrFail($rucemp);
      $usuario = $rucemp.$empresa->wsusuario;
      $contrasena = $empresa->claveSunat;

      $rutacertificado = public_path().'/certificados/'.$rucemp.'.pem';

      $see = new See();

      if($empresa->produccion=='1'){
          $see->setService(SunatEndpoints::FE_PRODUCCION);
      }else{
          $see->setService(SunatEndpoints::FE_BETA);
      }
 
      if(file_exists($rutacertificado)){
         $see->setCertificate(file_get_contents($rutacertificado));
       }else{
          $see->setCertificate(file_get_contents(public_path().'/certificados/prueba.pem'));
       }
     
      $see->setCredentials($usuario,$contrasena);

      return $see;

    }



    public function __construct()
    {
        $this->middleware('auth')->except(['autocomplete']);;
    }


    public function historialordenes(Request $request)
    {
      
         $clientes = DB::tABLE('cliente')->get();
        $razsoc = $request->get('cliente');
        $fecin = $request->get('fecin');
        $fecfin = $request->get('fecfin');
        $estados = DB::tABLE('estado_equipo')->get();

        if(empty($fecin)){

          $fecin = now()->modify('first day of this month')->format('Y-m-d');
          $fecfin = now()->modify('last day of this month')->format('Y-m-d');

        }

        $serdoc=$request->get('serdoc');
        $comp=$request->get('comp');
     
        $empresa = Empresa::findOrFail(Auth::user()->IdEmpresa);
           
        $IdEmpresa = Auth::user()->IdEmpresa;
    

      

        $comprobantes = DB::tABLE('cpe_cabecera as cpe_c')->select('ccacodsun','ccafem','tdodes','cpe_c.serdoc','cpe_c.numdoc','tdides','cpe_c.ccandi','cpe_c.ccanom','monnom','simbolo','cpe_c.ccaitv','cpe_c.IdEmpresa','cpe_c.tdocod','cpe_c.IdCpe_cabecera','cpe_c.ccanot','cpe_c.ccabaj','cpe_c.codhash','cpe_c.estado','cpe_c.enviado','totalcontado','totalcredito','est_equ_nom','est_equ_col','ut.name as nom_tec','ut.apeusu as ape_tec','marca','modelo','cpe_c.est_equ_id','fecha_hora','fallas')
        ->join('tipo_documento as tip_d','cpe_c.tdocod','=','tip_d.tdocod')
        ->join('estado_equipo as eq','eq.est_equ_id','cpe_c.est_equ_id')
        ->join('tipo_documento_identidad as tdi','cpe_c.tdicod','=','tdi.tdicod')
        ->join('moneda as mon','cpe_c.moncod','=','mon.moncod')
        ->join('users as ut','ut.IdUsuario','cpe_c.tecnico')
        ->where('cpe_c.tdocod','=','80')
        ->where('cpe_c.IdEmpresa','=',$IdEmpresa)
        ->where('cpe_c.ccafem','>=',$fecin)
        ->where('cpe_c.ccafem','<=',$fecfin)
        ->where('cpe_c.id_empresa_negocio',Auth::user()->id_empresa_negocio)
           ->where(function ($query) use ($razsoc) {
          if(!empty($razsoc)){
             $query->Where('cpe_c.clicod','=',$razsoc);
           
          }
            })
        ->orderby('IdCpe_cabecera','desc')
        ->paginate(100);
              

        return view('empresas.comprobantes.historialordenes',compact('comprobantes','fecin','fecfin','estados','clientes'));

          
         
    }

      public function indexordenes(Request $request)
    {
        
          $clientes = DB::tABLE('cliente')->get();

        $razsoc = $request->get('cliente');
        $fecin = $request->get('fecin');
        $fecfin = $request->get('fecfin');
        $estados = DB::tABLE('estado_equipo')->get();

        if(empty($fecin)){

          $fecin = now()->modify('first day of this month')->format('Y-m-d');
          $fecfin = now()->modify('last day of this month')->format('Y-m-d');

        }

        $serdoc=$request->get('serdoc');
        $comp=$request->get('comp');
     
        $empresa = Empresa::findOrFail(Auth::user()->IdEmpresa);
           
        $IdEmpresa = Auth::user()->IdEmpresa;
    

      

        $comprobantes = DB::tABLE('cpe_cabecera as cpe_c')->select('ccacodsun','ccafem','tdodes','cpe_c.serdoc','cpe_c.numdoc','tdides','cpe_c.ccandi','cpe_c.ccanom','monnom','simbolo','cpe_c.ccaitv','cpe_c.IdEmpresa','cpe_c.tdocod','cpe_c.IdCpe_cabecera','cpe_c.ccanot','cpe_c.ccabaj','cpe_c.codhash','cpe_c.estado','cpe_c.enviado','totalcontado','totalcredito','est_equ_nom','est_equ_col','ut.name as nom_tec','ut.apeusu as ape_tec','marca','modelo','cpe_c.est_equ_id','fecha_hora','fallas','mensaje_estado','cliente.telefono')
        ->join('tipo_documento as tip_d','cpe_c.tdocod','=','tip_d.tdocod')
        ->join('cliente','cliente.clicod','cpe_c.clicod')
        ->join('estado_equipo as eq','eq.est_equ_id','cpe_c.est_equ_id')
        ->join('tipo_documento_identidad as tdi','cpe_c.tdicod','=','tdi.tdicod')
        ->join('moneda as mon','cpe_c.moncod','=','mon.moncod')
        ->join('users as ut','ut.IdUsuario','cpe_c.tecnico')
        ->where('cpe_c.tdocod','=','80')
        ->where('cpe_c.IdEmpresa','=',$IdEmpresa)
        ->where('cpe_c.ccafem','>=',$fecin)
        ->where('cpe_c.ccafem','<=',$fecfin)
        ->where('cpe_c.id_empresa_negocio',Auth::user()->id_empresa_negocio)
        ->where(function ($query) use ($razsoc) {
          if(!empty($razsoc)){
             $query->Where('cpe_c.clicod','=',$razsoc);
           
          }
            })
        ->orderby('IdCpe_cabecera','desc')
        ->paginate(100);
              

        return view('empresas.comprobantes.indexordenes',compact('comprobantes','fecin','fecfin','estados','clientes'));

          
         
    }

      public function ordenesclientes(Request $request)
    {
      
        $razsoc = $request->get('cliente');
        $fecin = $request->get('fecin');
        $fecfin = $request->get('fecfin');
        $estados = DB::tABLE('estado_equipo')->get();

        $clientes = DB::tABLE('cliente')->get();

        if(empty($fecin)){

          $fecin = now()->modify('first day of this month')->format('Y-m-d');
          $fecfin = now()->modify('last day of this month')->format('Y-m-d');

        }

        $serdoc=$request->get('serdoc');
        $comp=$request->get('comp');
     
        $empresa = Empresa::findOrFail(Auth::user()->IdEmpresa);
           
        $IdEmpresa = Auth::user()->IdEmpresa;
    

      

        $comprobantes = DB::tABLE('cpe_cabecera as cpe_c')->select('ccacodsun','ccafem','tdodes','cpe_c.serdoc','cpe_c.numdoc','tdides','cpe_c.ccandi','cpe_c.ccanom','monnom','simbolo','cpe_c.ccaitv','cpe_c.IdEmpresa','cpe_c.tdocod','cpe_c.IdCpe_cabecera','cpe_c.ccanot','cpe_c.ccabaj','cpe_c.codhash','cpe_c.estado','cpe_c.enviado','totalcontado','totalcredito','est_equ_nom','est_equ_col','ut.name as nom_tec','ut.apeusu as ape_tec','marca','modelo','cpe_c.est_equ_id','fecha_hora','fallas')
        ->join('tipo_documento as tip_d','cpe_c.tdocod','=','tip_d.tdocod')
        ->join('estado_equipo as eq','eq.est_equ_id','cpe_c.est_equ_id')
        ->join('tipo_documento_identidad as tdi','cpe_c.tdicod','=','tdi.tdicod')
        ->join('moneda as mon','cpe_c.moncod','=','mon.moncod')
        ->join('users as ut','ut.IdUsuario','cpe_c.tecnico')
        ->where('cpe_c.tdocod','=','80')
        ->where('cpe_c.IdEmpresa','=',$IdEmpresa)
        ->where('cpe_c.ccafem','>=',$fecin)
        ->where('cpe_c.ccafem','<=',$fecfin)
        ->where('cpe_c.id_empresa_negocio',Auth::user()->id_empresa_negocio)
          ->where(function ($query) use ($razsoc) {
          if(!empty($razsoc)){
             $query->Where('cpe_c.clicod','=',$razsoc);
           
          }
            })        ->orderby('IdCpe_cabecera','desc')
        ->paginate(100);
              

        return view('empresas.comprobantes.ordenesclientes',compact('comprobantes','fecin','fecfin','estados','clientes'));

          
         
    }

    public function equiposreparacion(Request $request)
    {
      
        $razsoc = $request->get('cliente');
        $fecin = $request->get('fecin');
        $fecfin = $request->get('fecfin');
        $estados = DB::tABLE('estado_equipo')->get();

        $clientes = DB::tABLE('cliente')->get();

        if(empty($fecin)){

          $fecin = now()->modify('first day of this month')->format('Y-m-d');
          $fecfin = now()->modify('last day of this month')->format('Y-m-d');

        }

        $serdoc=$request->get('serdoc');
        $comp=$request->get('comp');
     
        $empresa = Empresa::findOrFail(Auth::user()->IdEmpresa);
           
        $IdEmpresa = Auth::user()->IdEmpresa;
    
        $comprobantes = DB::tABLE('cpe_cabecera as cpe_c')->select('ccacodsun','ccafem','tdodes','cpe_c.serdoc','cpe_c.numdoc','tdides','cpe_c.ccandi','cpe_c.ccanom','monnom','simbolo','cpe_c.ccaitv','cpe_c.IdEmpresa','cpe_c.tdocod','cpe_c.IdCpe_cabecera','cpe_c.ccanot','cpe_c.ccabaj','cpe_c.codhash','cpe_c.estado','cpe_c.enviado','totalcontado','totalcredito','est_equ_nom','est_equ_col','ut.name as nom_tec','ut.apeusu as ape_tec','marca','modelo','cpe_c.est_equ_id','fecha_hora','fallas')
        ->join('tipo_documento as tip_d','cpe_c.tdocod','=','tip_d.tdocod')
        ->join('estado_equipo as eq','eq.est_equ_id','cpe_c.est_equ_id')
        ->join('tipo_documento_identidad as tdi','cpe_c.tdicod','=','tdi.tdicod')
        ->join('moneda as mon','cpe_c.moncod','=','mon.moncod')
        ->join('users as ut','ut.IdUsuario','cpe_c.tecnico')
        ->where('cpe_c.tdocod','=','80')
        ->where('cpe_c.IdEmpresa','=',$IdEmpresa)
        ->where('cpe_c.ccafem','>=',$fecin)
        ->where('cpe_c.ccafem','<=',$fecfin)
        ->where('cpe_c.id_empresa_negocio',Auth::user()->id_empresa_negocio)
        ->where('cpe_c.est_equ_id','1')
          ->where(function ($query) use ($razsoc) {
          if(!empty($razsoc)){
             $query->Where('cpe_c.clicod','=',$razsoc);
           
          }
            })       
        ->orderby('IdCpe_cabecera','desc')
        ->paginate(100);
              

        return view('empresas.comprobantes.equiposreparacion',compact('comprobantes','fecin','fecfin','estados','clientes'));

          
         
    }

    public function cotizaciones(Request $request)
    {
        $razsoc = $request->get('cliente');
        $fecin = $request->get('fecin');
        $fecfin = $request->get('fecfin');
        $documento = $request->get('comp');

          $negocios = EmpresaNegocios::get();
        $sucursal = $request->get('sucursal');

        if(empty($sucursal)){
          $sucursal = $negocios->first()->id_empresa_negocio;
        }
        
        $ser = substr($documento,strpos($documento,'-')-4,4);
        $num = substr($documento,strpos($documento,'-')+1,8);


        if(empty($fecin)){

          $fecin = now()->modify('first day of this month')->format('Y-m-d');
          $fecfin = now()->modify('last day of this month')->format('Y-m-d');

        }

        
        $empresa = Empresa::findOrFail(Auth::user()->IdEmpresa);
           
        $comprobantes = DB::tABLE('cpe_cabecera as cpe_c')->select('ccacodsun','ccafem','tdodes','cpe_c.serdoc','cpe_c.numdoc','tdides','cpe_c.ccandi','cpe_c.ccanom','monnom','cpe_c.ccaitv','cpe_c.IdEmpresa','cpe_c.tdocod','cpe_c.IdCpe_cabecera','cpe_c.ccanot','cpe_c.ccabaj','cpe_c.codhash','cpe_c.estado','cpe_c.enviado','totalcontado','totalcredito')
        ->leftjoin('tipo_documento as tip_d','cpe_c.tdocod','=','tip_d.tdocod')
        ->leftjoin('tipo_documento_identidad as tdi','cpe_c.tdicod','=','tdi.tdicod')
        ->leftjoin('moneda as mon','cpe_c.moncod','=','mon.moncod')
     
        ->where('cpe_c.ccafem','>=',$fecin)
        ->where('cpe_c.ccafem','<=',$fecfin)
        ->where('cpe_c.id_empresa_negocio',$sucursal)
        ->where('cpe_c.tdocod','15')
        ->where(function ($query) use ($razsoc) {
          if(!empty($razsoc)){
             $query->where('cpe_c.ccanom','like','%'.$razsoc.'%')
              ->orWhere('cpe_c.ccandi','=',$razsoc);
          }
         
          })
         ->where(function ($query2) use ($documento,$ser,$num){
            if(!empty($documento)){
                $query2->where('serdoc',$ser)
                    ->Where('numdoc',$num);
            }
        })
        ->orderby('IdCpe_cabecera','desc')
        ->paginate(100);
              

              
   
            return view('empresas.comprobantes.indexcotizaciones',compact('comprobantes','fecin','fecfin','razsoc','documento','negocios','sucursal'));
          
         

        
         
    }



    public function indexsalidas(Request $request)
    {
       
        $fecin = $request->get('fecin');
        $fecfin = $request->get('fecfin');

        $areas = DB::tABLE('areas')->get();

        $area = $request->get('area');
        $negocios = DB::tABLE('empresa_negocios')->get();
        $sucursal = $request->get('sucursal');

        $tipo = $request->get('tipo');

        if(empty($tipo)){
          $tipo ='1';
        }

        $documento = $request->get('comp');

        $ser = substr($documento,strpos($documento,'-')-4,4);
        $num = substr($documento,strpos($documento,'-')+1,8);

        if(empty($fecin)){

          $fecin = now()->modify('first day of this month')->format('Y-m-d');
          $fecfin = now()->modify('last day of this month')->format('Y-m-d');

        }

  
        $empresa = Empresa::findOrFail(Auth::user()->IdEmpresa);
           
        $IdEmpresa = Auth::user()->IdEmpresa;
    

        if($tipo=='1'){
          $comprobantes = DB::tABLE('cpe_cabecera as cpe_c')->select('ccacodsun','ccafem','tdodes','cpe_c.serdoc','cpe_c.numdoc','tdides','cpe_c.ccandi','cpe_c.ccanom','monnom','cpe_c.ccaitv','cpe_c.IdEmpresa','cpe_c.tdocod','cpe_c.IdCpe_cabecera','cpe_c.ccanot','cpe_c.ccabaj','cpe_c.codhash','cpe_c.estado','cpe_c.enviado','totalcontado','totalcredito','are_emp_des','name','apeusu','cdedes','cdecan')
          ->leftjoin('cpe_detalle','cpe_detalle.IdCpe_cabecera','cpe_c.IdCpe_cabecera')
          ->join('tipo_documento as tip_d','cpe_c.tdocod','=','tip_d.tdocod')
          ->join('tipo_documento_identidad as tdi','cpe_c.tdicod','=','tdi.tdicod')
          ->join('moneda as mon','cpe_c.moncod','=','mon.moncod')
             ->leftjoin('users','users.IdUsuario','cpe_c.usu_rec')
          ->leftjoin('areas','areas.are_emp_id','cpe_c.are_emp_id')
          ->where('cpe_c.ccafem','>=',$fecin)
          ->where('cpe_c.ccafem','<=',$fecfin)
          ->where('cpe_c.id_empresa_negocio',Auth::user()->id_empresa_negocio)
          ->where(function ($query) {
            $query->where('cpe_c.tdocod','81');
            })
            ->where(function ($query) use($area) {
            if(!empty($area)){
               $query->where('cpe_c.are_emp_id',$area);
            }
           
            })
          ->orderby('IdCpe_cabecera','desc')
          ->paginate(10000);
        }elseif($tipo=='2'){
           $comprobantes = DB::tABLE('cpe_cabecera')
           ->select(DB::RAW('sum(cpe_detalle.cdecan) as cantidad'),'pronom','cdedes','cdepuni','productos.procod','productos.umecod','are_emp_des')
                ->leftjoin('cpe_detalle','cpe_detalle.IdCpe_cabecera','cpe_cabecera.IdCpe_cabecera')
                ->leftjoin('productos','productos.IdProducto','cpe_detalle.IdProducto')
                ->leftjoin('areas','areas.are_emp_id','cpe_cabecera.are_emp_id')
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                ->where('cpe_cabecera.id_empresa_negocio',Auth::user()->id_empresa_negocio)
                ->whereNull('cpe_cabecera.ccabaj')
                ->where(function ($query) {
                  $query->where('cpe_cabecera.tdocod','81');
                  })
                  ->where(function ($query) use($area) {
                  if(!empty($area)){
                     $query->where('cpe_cabecera.are_emp_id',$area);
                  }
                 
                  })
                ->orderby('cantidad','desc')
                ->groupby('cpe_detalle.IdProducto')
              ->paginate(10000);

        }
      
              

    
              
   
          return view('empresas.comprobantes.indexsalidas',compact('negocios','sucursal','comprobantes','fecin','fecfin','documento','areas','area','tipo'));


        

        
         
    }



    public function index(Request $request)
    {


      






        $razsoc = $request->get('cliente');
        $fecin = $request->get('fecin');
        $fecfin = $request->get('fecfin');

         $negocios = DB::tABLE('empresa_negocios')->get();
      $sucursal = $request->get('sucursal');


        $documento = $request->get('comp');

        $ser = substr($documento,strpos($documento,'-')-4,4);
        $num = substr($documento,strpos($documento,'-')+1,8);

        if(empty($fecin)){

          $fecin = now()->modify('first day of this month')->format('Y-m-d');
          $fecfin = now()->modify('last day of this month')->format('Y-m-d');

        }

  
        $empresa = Empresa::findOrFail(Auth::user()->IdEmpresa);
           
        $IdEmpresa = Auth::user()->IdEmpresa;
    

      

        $comprobantes = DB::tABLE('cpe_cabecera as cpe_c')->select('ccacodsun','ccafem','tdodes','cpe_c.serdoc','cpe_c.numdoc','tdides','cpe_c.ccandi','cpe_c.ccanom','monnom','cpe_c.ccaitv','cpe_c.IdEmpresa','cpe_c.tdocod','cpe_c.IdCpe_cabecera','cpe_c.ccanot','cpe_c.ccabaj','cpe_c.codhash','cpe_c.estado','cpe_c.enviado','totalcontado','totalcredito')
        ->join('tipo_documento as tip_d','cpe_c.tdocod','=','tip_d.tdocod')
        ->join('tipo_documento_identidad as tdi','cpe_c.tdicod','=','tdi.tdicod')
        ->join('moneda as mon','cpe_c.moncod','=','mon.moncod')
        ->where('cpe_c.ccafem','>=',$fecin)
        ->where('cpe_c.ccafem','<=',$fecfin)
        ->where('cpe_c.id_empresa_negocio',Auth::user()->id_empresa_negocio)
        ->where(function ($query) {
          $query->where('cpe_c.tdocod','01')
              ->orWhere('cpe_c.tdocod','03')
              ->orWhere('cpe_c.tdocod','13')
              ->orWhere('cpe_c.tdocod','14')
              ->orWhere('cpe_c.tdocod','07')
              ->orWhere('cpe_c.tdocod','08');
          })
          ->where(function ($query1) use ($razsoc){
            if(!empty($razsocs)){
                $query1->where('cpe_c.ccanom','like','%'.$razsoc.'%')
                    ->orWhere('cpe_c.ccandi','=',$razsoc);
            }
        })
        ->where(function ($query2) use ($documento,$ser,$num){
            if(!empty($documento)){
                $query2->where('serdoc',$ser)
                    ->Where('numdoc',$num);
            }
        })
        ->orderby('IdCpe_cabecera','desc')
        ->paginate(100);
              

    
              
   
          return view('empresas.comprobantes.index',compact('negocios','sucursal','comprobantes','fecin','fecfin','razsoc','documento'));


        

        
         
    }

     public function indexpedidos(Request $request)
    {
        $razsoc = $request->get('cliente');
        $fecin = $request->get('fecin');
        $fecfin = $request->get('fecfin');

         $negocios = DB::tABLE('empresa_negocios')->get();
      $sucursal = $request->get('sucursal');


        $documento = $request->get('comp');

        $ser = substr($documento,strpos($documento,'-')-4,4);
        $num = substr($documento,strpos($documento,'-')+1,8);

        if(empty($fecin)){

          $fecin = now()->modify('first day of this month')->format('Y-m-d');
          $fecfin = now()->modify('last day of this month')->format('Y-m-d');

        }

  
        $empresa = Empresa::findOrFail(Auth::user()->IdEmpresa);
           
        $IdEmpresa = Auth::user()->IdEmpresa;
    

      

        $comprobantes = DB::tABLE('cpe_cabecera as cpe_c')->select('ccacodsun','ccafem','tdodes','cpe_c.serdoc','cpe_c.numdoc','tdides','cpe_c.ccandi','cpe_c.ccanom','monnom','cpe_c.ccaitv','cpe_c.IdEmpresa','cpe_c.tdocod','cpe_c.IdCpe_cabecera','cpe_c.ccanot','cpe_c.ccabaj','cpe_c.codhash','cpe_c.estado','cpe_c.enviado','totalcontado','totalcredito')
        ->join('tipo_documento as tip_d','cpe_c.tdocod','=','tip_d.tdocod')
        ->join('tipo_documento_identidad as tdi','cpe_c.tdicod','=','tdi.tdicod')
        ->join('moneda as mon','cpe_c.moncod','=','mon.moncod')
        ->where('cpe_c.ccafem','>=',$fecin)
        ->where('cpe_c.ccafem','<=',$fecfin)
        ->where('cpe_c.id_empresa_negocio',Auth::user()->id_empresa_negocio)
        ->where(function ($query) {
          $query->where('cpe_c.tdocod','16');
          })
          ->where(function ($query1) use ($razsoc){
            if(!empty($razsocs)){
                $query1->where('cpe_c.ccanom','like','%'.$razsoc.'%')
                    ->orWhere('cpe_c.ccandi','=',$razsoc);
            }
        })
        ->where(function ($query2) use ($documento,$ser,$num){
            if(!empty($documento)){
                $query2->where('serdoc',$ser)
                    ->Where('numdoc',$num);
            }
        })
        ->orderby('IdCpe_cabecera','desc')
        ->paginate(100);
              

    
              
   
          return view('empresas.comprobantes.indexpedidos',compact('negocios','sucursal','comprobantes','fecin','fecfin','razsoc','documento'));


        

        
         
    }




  public function facturapdf($codfact,$doccod,$idcabecera,$archivo)
    {

      $rucemp = trim(Auth::user()->IdEmpresa);
      $rutpdfile = public_path().'/pdf/';
      $rutxmlfile = '/opt/data/comprobantes/'.$rucemp.'/xml/';
      $rutcdrfile = '/opt/data/comprobantes/'.$rucemp.'/cdr/';
     // $file= $rutpdfile.$codfact.'.pdf';
      //$file= $codfact.'.pdf';
      $file= $codfact.'.pdf';
      $xml= $codfact.'.xml';
      $cdr= 'R-'.$codfact.'.zip';

    if($archivo =='pdf'){

       if (file_exists($file))
      {
        $headers = array(
              'Content-Type: application/pdf',
            );

        return response()->download($file);
      }

    }elseif($archivo =='xml'){
        if (file_exists($xml))
      {
        $headers = array(
              'Content-Type: application/xml',
            );

        return response()->download($xml);
      }

    }elseif($archivo =='cdr'){

      if (file_exists($cdr))
      {
        $headers = array(
              'Content-Type: application/zip',
            );

        return response()->download($cdr);
      }

    }
     

     
      if($doccod=='07' || $doccod=='08'){
        return Redirect::to('/listarnotas/'.$idcabecera);
      }elseif($doccod=='0'){
        return Redirect::to('/listarbajas/'.$idcabecera);
      }else{
         return Redirect::to('/SisFact');
      }
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
      ->join('cliente as cli','cab.clicod','=','cli.clicod')
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


    public function autocompletenom(Request $request){
      $search = $request->term;
    
    
      $ruc = Cliente::where('clinom','like','%'.$search.'%')->where('rucemp',$rucemp)->where('cliest','=','Activo')->take(10)->get();
      $results = array();
      

        foreach($ruc as $cli => $cliente){
           $numnom=$cliente->clinom;
         
          $results[] = ['value'=>$numnom,'clinum'=>$cliente->clinum,'dir'=>$cliente->clidir,'tdicod'=>$cliente->tdicod,'clicod'=>$cliente->clicod,'cor'=>$cliente->clicor,'telefono'=>$cliente->telefono];
        }



      return response()->json($results);
    }

    public function autocompletenomprov(Request $request){
      $search = $request->term;
      $rucemp = trim(Auth::user()->IdEmpresa);
    
      $ruc = Proveedor::where('prov_raz','like','%'.$search.'%')->where('IdEmpresa',$rucemp)->take(10)->get();
      $results = array();
      

        foreach($ruc as $cli => $cliente){
           $numnom=$cliente->prov_raz;
         
          $results[] = ['value'=>$numnom,'clinum'=>$cliente->prov_ruc,'dir'=>$cliente->prov_dir,'tdicod'=>$cliente->tdicod,'clicod'=>$cliente->prov_id,'cor'=>$cliente->prov_cor,'telefono'=>$cliente->prov_num_con];
        }



      return response()->json($results);
    }

 public function autocompleteprov($cliente){
     // $search = $request->term;

    try{
       $rucemp = trim(Auth::user()->IdEmpresa);
      

      $ruc = Proveedor::where('prov_ruc',$cliente)->take(10)->get();
      $results = array();
      
     
      if($ruc->isEmpty()){

        if(strlen($cliente)=='8'){

             $leer_respuesta = self::consultardni($cliente);
                $results[] = ['value'=>$leer_respuesta->dni,'nom'=>$leer_respuesta->nombres.' '.$leer_respuesta->apellidoPaterno.' '.$leer_respuesta->apellidoMaterno,'dir'=>'--','tdicod'=>'1'];

        }else{

           $leer_respuesta = self::consultaruc($cliente);
              $results[] = ['value'=>$leer_respuesta->ruc,'nom'=>$leer_respuesta->razonSocial,'dir'=>$leer_respuesta->direccion,'tdicod'=>'6'];

        }

        
      }else{

        foreach($ruc as $cli => $cliente){
           $numnom=$cliente->clinum;
         
          $results[] = ['value'=>$numnom,'nom'=>$cliente->prov_raz,'dir'=>$cliente->prov_dir,'tdicod'=>$cliente->tdicod,'clicod'=>$cliente->prov_id,'cor'=>$cliente->prov_cor,'tel'=>$cliente->prov_num_con];
        }
       
      }
    }catch(\Exception $e){

       $leer_respuesta = self::consultaruc($cliente);
              $results[] = ['value'=>$leer_respuesta->ruc,'nom'=>$leer_respuesta->razonSocial,'dir'=>$leer_respuesta->direccion,'tdicod'=>'6'];
       
    }
     

   



      return response()->json($results);
    }

    public function autocomplete($cliente){
     // $search = $request->term;

    try{
       $rucemp = trim(Auth::user()->IdEmpresa);
      

      $ruc = Cliente::where('clinum','=',$cliente)->where('rucemp','=',$rucemp)->where('cliest','=','Activo')->take(10)->get();
      $results = array();
      
 
      if($ruc->isEmpty()){

        if(strlen($cliente)=='8'){

             $leer_respuesta = self::consultardni($cliente);
                $results[] = ['value'=>$leer_respuesta->dni,'nom'=>$leer_respuesta->nombres.' '.$leer_respuesta->apellidoPaterno.' '.$leer_respuesta->apellidoMaterno,'dir'=>'--','tdicod'=>'1'];

        }else{

           $leer_respuesta = self::consultaruc($cliente);
              $results[] = ['value'=>$leer_respuesta->ruc,'nom'=>$leer_respuesta->razonSocial,'dir'=>$leer_respuesta->direccion,'tdicod'=>'6'];

        }

        
      }else{

        foreach($ruc as $cli => $cliente){
           $numnom=$cliente->clinum;
         
          $results[] = ['value'=>$numnom,'nom'=>$cliente->clinom,'dir'=>$cliente->clidir,'tdicod'=>$cliente->tdicod,'clicod'=>$cliente->clicod,'cor'=>$cliente->clicor,'tel'=>$cliente->telefono,'fecnac'=>$cliente->clifecnac];
        }
       
      }
    }catch(\Exception $e){

       $leer_respuesta = self::consultaruc($cliente);
              $results[] = ['value'=>$leer_respuesta->ruc,'nom'=>$leer_respuesta->razonSocial,'dir'=>$leer_respuesta->direccion,'tdicod'=>'6'];
       
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
        $sercomp = $request->get('serdoc');
        $numcomp = $request->get('numdoc');
        $tdicod= $request->get('tdicod');
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
        $descglb = $request->get('totdesc');
        $numdocmod = str_pad($request->get('numdocmod'),8,"0", STR_PAD_LEFT);

      $moneda = DB::tABLE('moneda')->where('moncod','=',$mondoc)->first();


        //DATOS DOCUMENTO RELACIONADO

        $tdomod = $request->get('tdomod');
       // $tipnc = $request->get('tipnc');
       
  
        $docmod = DB::tABLE('cpe_cabecera')->select('IdCpe_cabecera','id_empresa_negocio')->where('serdoc','=',$serdocmod)->where('numdoc','=',$numdocmod)->first();
  
        $dat_cli = DB::tABLE('cliente')->where('clinum',$clinum)->where('rucemp',$rucemp)->first();


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
        $cabecera->ccandi = $clinum;
        $cabecera->ccanom = $clinom;
        $cabecera->clicod = $dat_cli->clicod;
        $cabecera->tipcambio = $tipcambio;
        $cabecera->tipnot = $tipnot;
        $cabecera->moncod = $request->get('tipmon');
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
    $cabecera->ccades= $descglb;
        $cabecera->IdEmpresa = $rucemp;
        $cabecera->IdCpe_cabecera = $IdCpe_cabecera;
        $cabecera->IdUsuario = Auth::user()->IdUsuario;

        $nota = $serdoc.'-'.$numdoc;
        $cabfactura = cpe_cabecera::findOrFail($IdCpe_cabecera);
        $cabfactura->ccanot = $nota;
       
        $cabfactura->update();
        


        if ($tdocod =='07') {
            if($tipdoc =='01'){
                $empresanegocio = EmpresaNegocios::findOrFail($docmod->id_empresa_negocio);

                if( $empresa->FcnuEmpresa == $numcomp){
                    $modnumdoc= $numcomp+1;
                }else{
                    $modnumdoc = $numcomp;
                }

                $empresa->FcseEmpresa = $serdoc;
                $empresa->FcnuEmpresa = $modnumdoc;
                //$empresa->update();

                $numdoc = str_pad($modnumdoc,8,"0", STR_PAD_LEFT);
                $cabecera->serdoc= $serdoc;
                $cabecera->numdoc = $numdoc;
                

            }elseif($tipdoc=='03'){
                $empresanegocio = EmpresaNegocios::findOrFail($docmod->id_empresa_negocio);
                
                if( $empresa->BcnuEmpresa == $numcomp){
                    $modnumdoc= $numcomp+1;
                }else{
                    $modnumdoc = $numcomp;
                }

                $empresa->BcseEmpresa = $serdoc;
                $empresa->BcnuEmpresa = $modnumdoc;
                //$empresa->update();

                $numdoc = str_pad($modnumdoc,8,"0", STR_PAD_LEFT);
                $cabecera->serdoc= $serdoc;
                $cabecera->numdoc = $numdoc;
                

            }
        }elseif ($tdocod =='08') {
            if($tipdoc =='01'){
                $empresanegocio = EmpresaNegocios::findOrFail($docmod->id_empresa_negocio);
                if( $empresa->FdnuEmpresa == $numcomp){
                    $modnumdoc= $numcomp+1;
                }else{
                    $modnumdoc = $numcomp;
                }

                $empresa->FdseEmpresa = $serdoc;
                $empresa->FdnuEmpresa = $modnumdoc;
                //$empresa->update();

                $numdoc = str_pad($modnumdoc,8,"0", STR_PAD_LEFT);
                $cabecera->serdoc= $serdoc;
                $cabecera->numdoc = $numdoc;
              
            }elseif($tipdoc=='03'){
                $empresanegocio = EmpresaNegocios::findOrFail($docmod->id_empresa_negocio);
              
                if( $empresa->BdnuEmpresa == $numcomp){
                    $modnumdoc= $numcomp+1;
                }else{
                    $modnumdoc = $numcomp;
                }

                $empresa->BdseEmpresa = $serdoc;
                $empresa->BdnuEmpresa = $modnumdoc;
                //$empresa->update();

                $numdoc = str_pad($modnumdoc,8,"0", STR_PAD_LEFT);
                $cabecera->serdoc= $serdoc;
                $cabecera->numdoc = $numdoc;
               
            }
        }
        
          $empresanegocio->update();
          $cabecera->save();
          $codfact = $cabecera->IdCpe_nota; 
         
        
                 
       //Registrar el detalle de la factura
        $cantidades = $request->get('cant');
        $unidades = $request->get('unid');
        $codpro = $request->get('codpro');
        $detpro = $request->get('detpro');
        $vunit = $request->get('vunit');
        $puni = $request->get('preuni');
        $vigv = $request->get('vigv');
        $tigv = $request->get('tigv');
        $vsub = $request->get('vsub');
        $vtot = $request->get('vtot');


           foreach( $unidades as $index => $ume ) {
            $detalle = new cpe_nota_detalle;
            $detalle->IdCpe_nota =  $cabecera->IdCpe_nota; 
            $dpro = $detpro[$index];
            $detalle->cdedes = $dpro;
            
              $codproducto = $codpro[$index];
              $detalle->umecod = $ume;
              $detalle->cdecan = $cantidades[$index];
              $detalle->procod = $codproducto;
              $detalle->cdepsu = $codproducto;
              $detalle->cdevun = $vunit[$index];
              $detalle->cdepuni = $puni[$index];
              $detalle->cdeigv = $vigv[$index];
              $detalle->tigcod = $tigv[$index];
              $detalle->cdepve = $vsub[$index];
              $detalle->cdevve = $vtot[$index];
              $detalle->save();

       
          }
 
       
            $leer_respuesta = self::generarnota($codfact);
       
            self::generarnotapdf($codfact);
   


          return Redirect::to('/SisFact')->with('success');

   
    }


    public function generarnotapdf($nota){

      $cabpdf = DB::tABLE('cpe_nota')
      ->select('cpe_cabecera.id_empresa_negocio','cpe_nota.serdoc','cpe_nota.numdoc','cpe_nota.ccanom','cpe_nota.ccandi','cpe_nota.IdEmpresa','cpe_nota.tdocod','cpe_cabecera.tdicod','tdodes','cpe_nota.ccafem','cpe_nota.moncod','cpe_nota.ccatvg','cpe_nota.ccaitv','cpe_nota.ccatve','cpe_nota.ccatvi','cpe_nota.ccaigv','cpe_cabecera.serdoc as sermod','cpe_cabecera.numdoc as nummod','cpe_nota.ccaobs','direccion','tipo_documento_identidad.tdides','cpe_nota.ccatexo')
      ->join('cpe_cabecera','cpe_cabecera.IdCpe_cabecera','cpe_nota.IdCpe_cabecera')
      ->join('tipo_documento','tipo_documento.tdocod','cpe_nota.tdocod')
      ->join('tipo_documento_identidad','tipo_documento_identidad.tdicod','cpe_cabecera.tdicod')
      ->where('IdCpe_nota',$nota)
      ->first();



      $numdoc = str_pad($cabpdf->numdoc,8,"0", STR_PAD_LEFT);

      $nompdffile=$cabpdf->IdEmpresa.'-'.$cabpdf->tdocod.'-'.$cabpdf->serdoc.'-'.$numdoc.'.pdf'; 


      $rutpdfile = public_path().'/pdf/';

      $empresa = Empresa::findOrFail(Auth::user()->IdEmpresa);

      $logo = public_path()."/imagenes/logos/".$empresa->LogEmpresa;

      $qrfile =  'QR-'.$empresa->IdEmpresa.'-'.$cabpdf->tdocod.'-'.$cabpdf->serdoc.'-'.$numdoc.'.png'; 

      $imgqr = "/qr/".$qrfile;

      $sucursal = DB::tABLE('empresa_negocios')
      ->leftjoin('formatos_comprobantes','formatos_comprobantes.cod_for_com','empresa_negocios.cod_for_com')
      ->where('id_empresa_negocio',$cabpdf->id_empresa_negocio)->first();


      $detpdf = DB::tABLE('cpe_nota_detalle')
      ->where('IdCpe_nota',$nota)
      ->get();

       $view = \View::make('empresas.comprobantes.general.'.$sucursal->descripcion, compact('cabpdf','detpdf','empresa','sucursal','logo','imgqr'));

        $pdf = \App::make('dompdf.wrapper');
        $contenido = $view->render();
        $pdf->loadHTML($view)->setPaper('A4', 'lanscape')->save($rutpdfile.$nompdffile);

        return 'realizado';

    }



     public function generarnota($codfact){

    $cabecera = DB::tABLE('cpe_nota')->where('IdCpe_nota',$codfact)
    ->leftjoin('cliente','cliente.clicod','cpe_nota.clicod')->first();

    $comprobante = DB::tABLE('cpe_cabecera')->where('IdCpe_cabecera',$cabecera->IdCpe_cabecera)->first();
    $detalles = DB::tABLE('cpe_nota_detalle')->where('IdCpe_nota',$codfact)->get();
    $sucursal = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',$comprobante->id_empresa_negocio)->first();
    $moneda = DB::tABLE('moneda')->where('moncod',$cabecera->moncod)->first();
    $empresa = DB::tABLE('empresa')->where('IdEmpresa',$sucursal->IdEmpresa)->first();

   

      $numdoc = str_pad($cabecera->numdoc,8,"0", STR_PAD_LEFT);

     $qrfile =  'QR-'.$cabecera->IdEmpresa.'-'.$cabecera->tdocod.'-'.$cabecera->serdoc.'-'.$numdoc.'.png'; 
     $rutacdr = public_path().'/cdr/';
     $rutaxml = public_path().'/xml/';
      $rutapdf = public_path().'/pdf/';

       $filexml =  $cabecera->IdEmpresa.'-'.$cabecera->tdocod.'-'.$cabecera->serdoc.'-'.$numdoc.'.xml';
          $filecdrzip =  'R-'.$cabecera->IdEmpresa.'-'.$cabecera->tdocod.'-'.$cabecera->serdoc.'-'.$numdoc.'.zip';



    $see = self::configuracion();
    
    $clidireccion = new Address();
    $clidireccion->setDireccion($cabecera->clidir);

    // Cliente
    $client = new Client();
    $client->setTipoDoc($cabecera->tdicod)
        ->setNumDoc($cabecera->ccandi)
        ->setRznSocial($cabecera->ccanom)
        ->setAddress($clidireccion);

    
    // Emisor
    $address = new Address();
    $address->setUbigueo($sucursal->ubigeo)
    ->setDepartamento($sucursal->departamento)
    ->setProvincia($sucursal->provincia)
    ->setDistrito($sucursal->distrito)
    ->setUrbanizacion('-')
    ->setDireccion($sucursal->direccion);

    $company = new Company();
    $company->setRuc($empresa->IdEmpresa)
        ->setRazonSocial($empresa->NomEmpresa)
        ->setNombreComercial('')
        ->setAddress($address);


        $invoice = (new Note())
        ->setUblVersion('2.1')
        //->setTipoOperacion($cabecera->topcod) // Catalog. 51
        ->setTipoDoc($cabecera->tdocod)
        ->setSerie($cabecera->serdoc)
        ->setCorrelativo($cabecera->numdoc)
        ->setFechaEmision(new \DateTime($cabecera->ccafem))
        ->setTipDocAfectado($comprobante->tdocod) // Tipo Doc: Factura
        ->setNumDocfectado($comprobante->serdoc.'-'.$comprobante->numdoc) // Factura: Serie-Correlativo
        ->setCodMotivo($cabecera->tipnot) // Catalogo. 09
        ->setDesMotivo($cabecera->ccaobs)
        ->setTipoMoneda($cabecera->moncod)
        ->setClient($client)
        ->setMtoOperGravadas($cabecera->ccatvg)
        ->setMtoIGV($cabecera->ccaigv)
        ->setTotalImpuestos($cabecera->ccaigv)
       // ->setValorVenta($cabecera->ccatvg)
      //  ->setSubTotal($cabecera->ccaitv)
        ->setMtoImpVenta($cabecera->ccaitv)
        ->setCompany($company);
     //  }

    

  
    $items = [];
    foreach ($detalles as $detalle) {

    
      $item = (new SaleDetail())
        ->setCodProducto($detalle->procod)
        ->setUnidad($detalle->umecod)
        ->setCantidad($detalle->cdecan)
        ->setDescripcion($detalle->cdedes)
        ->setMtoBaseIgv($detalle->cdepve)
        ->setPorcentajeIgv(18.00) // 18%
        ->setIgv($detalle->cdeigv)
        ->setTipAfeIgv($detalle->tigcod)
        ->setTotalImpuestos($detalle->cdeigv)
        ->setMtoValorVenta($detalle->cdepve)
        ->setMtoValorUnitario($detalle->cdevun)
        ->setMtoPrecioUnitario($detalle->cdepuni);

     $items[] = $item;
    }



    $totalletras= MontoLetras::convertir(number_format($cabecera->ccaitv,'2','.',''),$moneda->monnom,'Centimos');

 

       $legend = (new Legend())
        ->setCode('1000')
        ->setValue($totalletras);

       $invoice->setDetails($items)
            ->setLegends([$legend]);




    $result = $see->send($invoice);

 
    // Guardar XML
    file_put_contents($rutaxml.$filexml,
    $see->getFactory()->getLastXml());

    if (!$result->isSuccess()) {
        $error = $result->getError();
        $actualizar = cpe_nota::findOrFail($codfact);
        $actualizar->ccasunrescod = $error->getCode();
        $actualizar->ccadessun = $error->getMessage();
        $actualizar->update();

        return "error";
    }

    $resultado = $result->getCdrResponse();
    // Guardar CDR
    file_put_contents($rutacdr.'R-'.$filecdrzip, $result->getCdrZip());

    $actualizar = cpe_nota::findOrFail($codfact);
    $actualizar->ccasunrescod = $resultado->getCode();
    $actualizar->ccadessun = $resultado->getDescription();

    $xml = file_get_contents($rutaxml.$filexml);
              
    $params = [
      $actualizar->IdEmpresa,
      $actualizar->tdocod,
      $actualizar->serdoc,
      $actualizar->numdoc,
      number_format($actualizar->ccaigv, 2, '.', ''),
      number_format($actualizar->ccaitv, 2, '.', ''),
      $actualizar->ccafem,
      $actualizar->tdicod,
      $actualizar->ccandi,
    ];

    $content = implode('|', $params).'|';

    $DOM = new DOMDocument('1.0', 'utf-8');
    $DOM->loadXML($xml);
    $hash = $DOM->getElementsByTagName('DigestValue')->item(0)->nodeValue;
    $actualizar->codhash = $hash;
    $actualizar->ccaqr = $content;
    $actualizar->update();

    self::generar_qr($qrfile,$actualizar->ccaqr);
   

    return "success";

  }


 

    public function generar_qr($qrfile,$contenido){

      $rucemp = trim(Auth::user()->IdEmpresa);
      $ruta = public_path().'/qr/';
      $file = $ruta.$qrfile;
      
      return \QRCode::text($contenido)->setMargin(1)->setSize(7)->setOutFile($file)->png();

    }
  


    //NOTA DE CREDITO Y DÉBITO

    public function tiponotacd($tdocod=0,$idcabecera=0,$ncdcod){
      $rucemp = Auth::user()->IdEmpresa;
    //  $cabecera=DB::tABLE('cpe_cabecera')->where('IdCpe_cabecera','=',$idcabecera)->where('IdEmpresa','=',$rucemp)->first();

       $cabecera = DB::tABLE('cpe_cabecera as cab')
      ->join('cliente as cli','cab.clicod','=','cli.clicod')
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
      $productos= DB::tABLE('productos')->where('IdEmpresa','=',$rucemp)->where('id_empresa_negocio','=',Auth::user()->id_empresa_negocio)
      ->orderby('pronom','asc')->get();

      // consultar tipos de monedas
      $monedas = DB::tABLE('moneda')->where('monest','=','Activo')->orderby('moncod','asc')->get();


 

  
        if($tdocod =='01'){
          $senuncd = DB::tABLE('empresa_negocios')->select('FcseEmpresa','FcnuEmpresa')->where('id_empresa_negocio',$cabecera->id_empresa_negocio)->first();
          $nota = DB::tABLE('tipo_nota_credito')->get();
        }elseif($tdocod=='03'){
          $senuncd = DB::tABLE('empresa_negocios')->select('BcseEmpresa','BcnuEmpresa')->where('id_empresa_negocio',$cabecera->id_empresa_negocio)->first();
          $nota = DB::tABLE('tipo_nota_credito')->get();
        }
    


      return view('empresas.comprobantes.tiponota',compact('cabecera','senuncd','igv','monedas','unidades','operaciones','docidentidad','clientes','tdocod','ncdcod','productos','doccomprobante','nota','detalle'));
         

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
     public function formbajacomprobante($comprobante){
     
      $rucemp = Auth::user()->IdEmpresa;
      $fecact = date('Y-m-d');
    

      $cabecera = DB::tABLE('cpe_cabecera')
      ->join('moneda','cpe_cabecera.moncod','moneda.moncod')
      ->join('tipo_documento','tipo_documento.tdocod','cpe_cabecera.tdocod')
      ->where('IdCpe_cabecera',$comprobante)
      ->first();

      return view('empresas.comprobantes.emitirbaja',compact('cabecera'));

     
    }


     public function registraranulacion(Request $request,$comp,$motivo){
      


      $rucemp = Auth::user()->IdEmpresa;
      $empresa = Empresa::findOrFail($rucemp);
      

      $fecbaj = now()->format('Y-m-d');

      $cabecera = cpe_cabecera::findOrFail($comp);
      $cabecera->motivo_baja = $motivo;
      $cabecera->ccabaj = $fecbaj;
      $cabecera->ccasunrescod='7';
      $cabecera->ccatvg ='0.00';
      $cabecera->ccaigv ='0.00';
      $cabecera->ccaitv ='0.00';
      $cabecera->estado = 'ANULADO';
      $cabecera->update();
      
      $almacen = DB::tABLE('almacenes')->where('id_empresa_negocio',$cabecera->id_empresa_negocio)->where('predeterminado','1')->first();

      $detalle = DB::tABLE('cpe_detalle')->where('IdCpe_cabecera',$comp)->get();

      if($cabecera->tdocod=='01' || $cabecera->tdocod=='03' || $cabecera->tdocod=='13'){

        foreach ($detalle as $key => $det) {
            
          if(!empty($det->IdProducto)){

            $producto = Productos::findOrFail($det->IdProducto);
            
            if(empty($producto->pro_rel)){

              $id_prod = $producto->IdProducto;

            }else{
          
              $id_prod = $producto->pro_rel;

            }

            $stockprod = DB::tABLE('producto_stock')
            ->where('IdProducto',$id_prod)
            ->where('id_empresa_negocio',$cabecera->id_empresa_negocio)
            ->where('id_almacen',$almacen->id_almacen)
            ->first();

            if(empty($stockprod)){
              
              $stock = 0+($det->cdecan*$producto->factor);
              $stockprod_act = DB::tABLE('producto_stock')
                  ->insert([
                    'stock'=>$stock,'IdProducto'=>$id_prod,
                    'id_empresa_negocio'=>$cabecera->id_empresa_negocio,
                    'id_almacen'=>$almacen->id_almacen]
                  );

            }else{

                $stockprod_act = DB::tABLE('producto_stock')
                ->where('pro_sto_id',$stockprod->pro_sto_id)
                ->update(['stock'=>$stockprod->stock+($det->cdecan*$producto->factor)]);

                $stock = $stockprod->stock+($det->cdecan*$producto->factor);
            }

            $movimiento = new movimientos;
            $movimiento->mov_fec = $fecbaj; 
            $movimiento->mov_tip = 'E';
            $movimiento->mov_mot = 'ANULACION';
            $movimiento->cantidad = $det->cdecan;
            $movimiento->unidad = $det->umecod;
            $movimiento->comprobante = $cabecera->serdoc.'-'.$cabecera->numdoc;
            $movimiento->IdEmpresa = $rucemp;
            $movimiento->id_empresa_negocio = $cabecera->id_empresa_negocio;
            $movimiento->IdProducto = $producto->IdProducto;
            $movimiento->observacion = "Venta desde Punto de Venta";
            $movimiento->IdUsuario = Auth::user()->IdUsuario;
            $movimiento->IdCpe_cabecera = $cabecera->IdCpe_cabecera;
            $movimiento->stockmov = $stock;
            $movimiento->save();


          }
          
          
        } 
      }
     

      $baja = new cpe_baja;
      $baja->IdCpe_cabecera = $comp;
      $baja->cbamot = $motivo;
      $baja->cbdfco =  $fecbaj; 
      $baja->id_empresa_negocio = Auth::user()->id_empresa_negocio;
      $baja->IdEmpresa =  $rucemp; 
      $baja->save();

      if($cabecera->tdocod=='01'){
       
         $leer_respuesta = self::generarcomunicacionbaja($comp);
      
      }elseif($cabecera->tdocod=='03'){
   
          $leer_respuesta = self::generarresumen($comp);
      }  
      
      if($cabecera->tdocod =='80'){

          if($request->ajax()) {
          return response()->json(['mensaje' => 'orden']);
        }
   
      }elseif($cabecera->tdocod =='15'){
          if($request->ajax()) {
          return response()->json(['mensaje' => 'cotizacion']);
        }
  
      }else{

          if($request->ajax()) {
          return response()->json(['mensaje' => 'cpe']);
        }

      }
     
      

  
    }
    

        

    public function listarnotas($idcabecera){

      $rucemp =Auth::user()->IdEmpresa;
      $notas = DB::tABLE('cpe_nota as n')->select('n.ccaenlace','n.ccafem','n.serdoc','n.numdoc','tdodes','c.ccandi','c.ccanom','mn.monnom','n.ccaitv','n.tdocod','c.IdEmpresa','n.IdCpe_nota','n.tdocod','n.codhash','n.ccasunrescod','n.ccabaj')
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

    public function ingresarpanel($idempresa,$idnegocio){

      $user =Auth::user()->IdUsuario;
      $regemp = User::findOrFail($user);
      $regemp->IdEmpresa = $idempresa;
      $regemp->id_empresa_negocio = $idnegocio;
      $regemp->update();

      return Redirect::to('/SisFact');

    }



   
   


    public function consultaruc($ruc){
        

        $factory = new RucFactory();
        $cs = $factory->create();

        $company = $cs->get($ruc);
        if (!$company) {
            echo 'Not found';
            return;
        }
;

        return $company;

             
                  
    }

    public function consultardni($dni){

        $factory = new DniFactory();
        $cs = $factory->create();

        $person = $cs->get($dni);
        if (!$person) {
            echo 'Not found';
            return;
        }

        return $person;

    }
     
   



      
      //FACTURACION ELECTRONICA

    public function generarcomprobante($codfact){

        $cabecera = DB::tABLE('cpe_cabecera')->where('IdCpe_cabecera',$codfact)->first();
        $detalles = DB::tABLE('cpe_detalle')->where('IdCpe_cabecera',$codfact)->get();
        $sucursal = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first();
        $moneda = DB::tABLE('moneda')->where('moncod',$cabecera->moncod)->first();
        $empresa = DB::tABLE('empresa')->where('IdEmpresa',Auth::user()->IdEmpresa)->first();

        $see = self::configuracion();
        
        $clidireccion = new Address();
        $clidireccion->setDireccion($cabecera->clidir);

        // Cliente
        $client = new Client();
        $client->setTipoDoc($cabecera->tdicod)
            ->setNumDoc($cabecera->ccandi)
            ->setRznSocial($cabecera->ccanom)
            ->setAddress($clidireccion);

        // Emisor
         $address = new Address();
        $address->setUbigueo($empresa->ubigeo)
        ->setDepartamento($empresa->departamento)
        ->setProvincia($empresa->provincia)
        ->setDistrito($empresa->distrito)
        ->setUrbanizacion('-')
        ->setDireccion($empresa->DirEmpresa);

        $company = new Company();
        $company->setRuc($empresa->IdEmpresa)
            ->setRazonSocial($empresa->NomEmpresa)
            ->setNombreComercial('')
            ->setAddress($address);

        // Venta
        $invoice = (new Invoice())
            ->setUblVersion('2.1')
            ->setTipoOperacion($cabecera->topcod) // Catalog. 51
            ->setTipoDoc($cabecera->tdocod)
            ->setSerie($cabecera->serdoc)
            ->setCorrelativo($cabecera->numdoc)
            ->setFechaEmision(new \DateTime($cabecera->ccafem))
            ->setTipoMoneda($cabecera->moncod)
            ->setClient($client)
            ->setMtoOperExoneradas($cabecera->ccatve)
            ->setMtoOperGravadas($cabecera->ccatvg)
            ->setMtoIGV($cabecera->ccaigv)
            ->setTotalImpuestos($cabecera->ccaigv)
            ->setValorVenta($cabecera->ccatvg)
            ->setSubTotal($cabecera->ccaitv)
            ->setMtoImpVenta($cabecera->ccaitv)
            ->setCompany($company);

      
        $items = [];
        foreach ($detalles as $detalle) {

        
          $item = (new SaleDetail())
            ->setCodProducto($detalle->procod)
            ->setUnidad($detalle->umecod)
            ->setCantidad($detalle->cdecan)
            ->setDescripcion($detalle->cdedes)
            ->setMtoBaseIgv($detalle->cdepve)
            ->setPorcentajeIgv(18.00) // 18%
            ->setIgv($detalle->cdeigv)
            ->setTipAfeIgv($detalle->tigcod)
            ->setTotalImpuestos($detalle->cdeigv)
            ->setMtoValorVenta($detalle->cdepve)
            ->setMtoValorUnitario($detalle->cdevun)
            ->setMtoPrecioUnitario($detalle->cdepuni);

         $items[] = $item;
        }



        $totalletras= MontoLetras::convertir(number_format($cabecera->ccaitv,'2','.',''),$moneda->monnom,'Centimos');

        $legend = (new Legend())
            ->setCode('1000')
            ->setValue($totalletras);

        
        $invoice->setDetails($items)
                ->setLegends([$legend]);

        $result = $see->send($invoice);

        // Guardar XML
        file_put_contents($invoice->getName().'.xml',
        $see->getFactory()->getLastXml());

        if (!$result->isSuccess()) {
            $error = $result->getError();
            $actualizar = cpe_cabecera::findOrFail($codfact);
            $actualizar->ccasunrescod = $error->getCode();
            $actualizar->ccadessun = $error->getMessage();
            $actualizar->update();

            return "error";
        }

        $resultado = $result->getCdrResponse();
        // Guardar CDR
        file_put_contents('R-'.$invoice->getName().'.zip', $result->getCdrZip());

        $actualizar = cpe_cabecera::findOrFail($codfact);
        $actualizar->ccasunrescod = $resultado->getCode();
        $actualizar->ccadessun = $resultado->getDescription();

        $xml = file_get_contents($invoice->getName().'.xml');
                  
        $params = [
          $actualizar->IdEmpresa,
          $actualizar->tdocod,
          $actualizar->serdoc,
          $actualizar->numdoc,
          number_format($actualizar->ccaigv, 2, '.', ''),
          number_format($actualizar->ccaitv, 2, '.', ''),
          $actualizar->ccafem,
          $actualizar->tdicod,
          $actualizar->ccandi,
        ];

        $content = implode('|', $params).'|';

        $DOM = new DOMDocument('1.0', 'utf-8');
        $DOM->loadXML($xml);
        $hash = $DOM->getElementsByTagName('DigestValue')->item(0)->nodeValue;
        $actualizar->codhash = $hash;
        $actualizar->ccaqr = $content;
        $actualizar->update();

       

        return "success";

      }    

      

  

   public function generarresumen($codfact){

    $cabecera = DB::tABLE('cpe_cabecera')->where('IdCpe_cabecera',$codfact)->first();
    $baja = DB::tABLE('cpe_baja')->where('IdCpe_cabecera',$codfact)->first();
    $sucursal = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first();
    $moneda = DB::tABLE('moneda')->where('moncod',$cabecera->moncod)->first();
    $empresa = DB::tABLE('empresa')->where('IdEmpresa',Auth::user()->IdEmpresa)->first();

    $ResnuEmpresa = $empresa->ResnuEmpresa+1;

    $actualizarnum = Empresa::findOrFail(Auth::user()->IdEmpresa);
    $actualizarnum->ResnuEmpresa = $ResnuEmpresa;
    $actualizarnum->update();

    $see = self::configuracion();
    
    // Emisor
    $address = new Address();
    $address->setUbigueo($sucursal->ubigeo)
    ->setDepartamento($sucursal->departamento)
    ->setProvincia($sucursal->provincia)
    ->setDistrito($sucursal->distrito)
    ->setUrbanizacion('-')
    ->setDireccion($sucursal->direccion);

    $company = new Company();
    $company->setRuc($empresa->IdEmpresa)
            ->setRazonSocial($empresa->NomEmpresa)
            ->setNombreComercial('')
            ->setAddress($address);

    $detail = new SummaryDetail();
    $detail->setTipoDoc($cabecera->tdocod)
            ->setSerieNro($cabecera->serdoc.'-'.$cabecera->numdoc)
            ->setEstado('3')
            ->setClienteTipo($cabecera->tdicod)
            ->setClienteNro($cabecera->ccandi)
            ->setTotal($cabecera->ccaitv)
            ->setMtoOperGravadas($cabecera->ccatvg)
            ->setMtoOperInafectas(0.00)
            ->setMtoOperExoneradas($cabecera->ccatve)
            ->setMtoOperExportacion(0.00)
            ->setMtoOtrosCargos(0.00)
            ->setMtoIGV($cabecera->ccaigv);

    $sum = new Summary();
    $sum->setFecGeneracion(new \DateTime($baja->cbdfco))
        ->setFecResumen(new \DateTime($baja->cbdfco))
        ->setCorrelativo($ResnuEmpresa)
        ->setCompany($company)
        ->setDetails([$detail]);

    
    $result = $see->send($sum);

    file_put_contents($sum->getName().'.xml',
    $see->getFactory()->getLastXml());

    
    if (!$result->isSuccess()) {
        $error = $result->getError();
        $actualizar = cpe_cabecera::findOrFail($codfact);
        $actualizar->ccasunrescod = $error->getCode();
        $actualizar->ccadessun = $error->getMessage();
        $actualizar->update();

        return "error";
    }


    $ticket = $result->getTicket();
   // echo 'Ticket :<strong>' . $ticket .'</strong>';

   

    $res = $see->getStatus($ticket);
 
    $baja = DB::tABLE('cpe_baja')->where('IdCpe_cabecera',$codfact)->first();
    $actualizarbaja = cpe_baja::findOrFail($baja->IdCpe_baja);
    $actualizarbaja->ccasuntick = $ticket;
    $actualizarbaja->ccasunrescod = $res->getCode();
    $actualizarbaja->update();

      if($res->getCode()=='0'){
      $cabecera = cpe_cabecera::findOrFail($codfact);
      $cabecera->ccasunrescod = '8';
      $cabecera->update();
    }

    if (!$res->isSuccess()) {
  
         $error = $res->getError();
    
        $actualizarbaja = cpe_baja::findOrFail($baja->IdCpe_baja);
        $actualizarbaja->ccasunrescod = $error->getCode();
        $actualizarbaja->ccadessun = $error->getMessage();
        $actualizarbaja->update();

        return;
    }

  
    $resultado = $res->getCdrResponse();

    file_put_contents($sum->getName().'.zip', $res->getCdrZip());


    return "success";

  }


  public function consultarticketbaja($codfact){

        $see = self::configuracion();

    $baja = DB::tABLE('cpe_baja')->where('IdCpe_cabecera',$codfact)->first();


    $res = $see->getStatus($baja->ccasuntick);
  

    $actualizarbaja = cpe_baja::findOrFail($baja->IdCpe_baja);
    $actualizarbaja->ccasunrescod = $res->getCode();
    $actualizarbaja->update();

    if($res->getCode()=='0'){
      $cabecera = cpe_cabecera::findOrFail($codfact);
      $cabecera->ccasunrescod = '8';
      $cabecera->update();
    }

    if (!$res->isSuccess()) {
  
        $error = $res->getError();
    
        $actualizarbaja = cpe_baja::findOrFail($baja->IdCpe_baja);
        $actualizarbaja->ccasunrescod = $error->getCode();
        $actualizarbaja->ccadessun = $error->getMessage();
        $actualizarbaja->update();

        return Redirect::to('/SisFact');
    }

  
    $resultado = $res->getCdrResponse();

    file_put_contents($resultado->getId().'.zip', $res->getCdrZip());


     return Redirect::to('/SisFact');


  }
     
   
   public function generarresumendiario($fechacomprobantes,$tipo){

    $fechageneracion = now()->format('Y-m-d');

    $contar = DB::tABLE('cpe_cabecera')->where('tdocod','03')->where('ccafem',$fechacomprobantes)->count();

    if($contar >0){

        if($tipo=='1'){

      $cabeceras = DB::tABLE('cpe_cabecera')
      ->where('tdocod','03')
      ->where('ccafem','=',$fechacomprobantes)
      ->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
      ->get();


    }elseif($tipo=='3'){


      $cabeceras = DB::tABLE('cpe_cabecera')->where('tdocod','03')
      ->where('ccafem','=',$fechacomprobantes)
      ->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
      ->where('ccabaj','!=','')
      ->where('tdocod','03')
      ->get();

    }

    $sucursal = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first();

    $empresa = DB::tABLE('empresa')->where('IdEmpresa',Auth::user()->IdEmpresa)->first();

    $ResnuEmpresa = $sucursal->ResnuEmpresa +1;

    $actualizarnum = EmpresaNegocios::findOrFail(Auth::user()->id_empresa_negocio);
    $actualizarnum->ResnuEmpresa = $ResnuEmpresa;
    $actualizarnum->update();

    $resumen = new resumenes;
    $resumen->res_fec = $fechageneracion;
    $resumen->res_fec_gen = $fechacomprobantes;

    $see = self::configuracion();
    
    // Emisor
    $address = new Address();
    $address->setUbigueo($sucursal->ubigeo)
    ->setDepartamento($sucursal->departamento)
    ->setProvincia($sucursal->provincia)
    ->setDistrito($sucursal->distrito)
    ->setUrbanizacion('-')
    ->setDireccion($sucursal->direccion);

    $company = new Company();
    $company->setRuc($empresa->IdEmpresa)
            ->setRazonSocial($empresa->NomEmpresa)
            ->setNombreComercial('')
            ->setAddress($address);

    $comprobantes = [];
    foreach ($cabeceras as $cabecera) {
        
        $detail = new SummaryDetail();
        $detail->setTipoDoc($cabecera->tdocod)
            ->setSerieNro($cabecera->serdoc.'-'.$cabecera->numdoc)
            ->setEstado($tipo)
            ->setClienteTipo($cabecera->tdicod)
            ->setClienteNro($cabecera->ccandi)
            ->setTotal($cabecera->ccaitv)
            ->setMtoOperGravadas($cabecera->ccatvg)
            ->setMtoOperInafectas(0.00)
            ->setMtoOperExoneradas($cabecera->ccatve)
            ->setMtoOperExportacion($cabecera->ccatexp)
            ->setMtoOtrosCargos(0.00)
            ->setMtoIGV($cabecera->ccaigv);

        $comprobantes[] = $detail;
    }
 

    $sum = new Summary();
    $sum->setFecGeneracion(new \DateTime($fechacomprobantes))
        ->setFecResumen(new \DateTime($fechageneracion))
        ->setCorrelativo($ResnuEmpresa)
        ->setCompany($company)
        ->setDetails($comprobantes);

    $result = $see->send($sum);

    file_put_contents($sum->getName().'.xml',
    $see->getFactory()->getLastXml());



    if (!$result->isSuccess()) {
        
        $error = $result->getError();
        $resumen->error_code = $error->getCode();
        $resumen->error = $error->getMessage();
       
  
    }


    $ticket = $result->getTicket();
   // echo 'Ticket :<strong>' . $ticket .'</strong>';

    $resumen->res_ticket = $ticket;

   



    $resumen->id_empresa_negocio = Auth::user()->id_empresa_negocio;
    $resumen->res_tip =$tipo;

 

 //consultarticket
    $res = $see->getStatus($ticket);
  


    if (!$res->isSuccess()) {
  
        $error = $res->getError();
        $resumen->error_code_ticket = $error->getCode();
        $resumen->error_ticket=$error->getMessage();

    
    }

   
    $resultado = $res->getCdrResponse();


    if($res->getCode()=='0'){
        
      $resumen->res_est = $resultado->getDescription();
      $resumen->res_cod_est =$resultado->getCode();

    }

    file_put_contents($sum->getName().'.zip', $res->getCdrZip());

    $resumen->save();

    foreach ($cabeceras as $comp) {
      
      $cpe = cpe_cabecera::findOrFail($comp->IdCpe_cabecera);
      if($tipo=='1'){
        $cpe->ticket = $ticket;
        
        if($res->getCode()=='0'){
          $cpe->ccasunrescod = $resultado->getCode();
        }else{
          $cpe->ccasunrescod = $error->getCode();
        }

      }elseif($tipo=='3'){
        $cpe->ticketbaja = $ticket;

         if($res->getCode()=='0'){
            $cpe->ccasunrescod = '8';
          }else{
            $cpe->ccasunrescod = $error->getCode();
          }

      }
      
     
      $cpe->update();
    }

   
    return "success";
    }else{

       return Redirect::to('/SisFact');
    }

  }

  public function consultarticket($ticket,$tipo){

    $see = self::configuracion();

   
    $res = $see->getStatus($ticket);
   


    if (!$res->isSuccess()) {
  
        $error = $res->getError();
        $resumenes = resumenes::where('res_ticket',$ticket)
        ->update(['error_ticket'=>$error->getMessage(),'error_code_ticket'=>$error->getCode()]);

    }

  
    $resultado = $res->getCdrResponse();


    if($res->getCode()=='0'){
      
        $resumenes = resumenes::where('res_ticket',$ticket)
      ->update(['res_est'=>$resultado->getDescription(),'res_cod_est'=>$resultado->getCode()]);

    }


    file_put_contents($resultado->getId().'.zip', $res->getCdrZip());

    if($tipo=='1'){
        $buscarcomp = DB::tABLE('cpe_cabecera')->where('ticket',$ticket)->where('tdocod','03')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->get();
      
      foreach ($buscarcomp as $comp) {
          
          $cpe = cpe_cabecera::findOrFail($comp->IdCpe_cabecera);
          if($res->getCode()=='0'){
            $cpe->ccasunrescod = $resultado->getCode();
          }else{
            $cpe->ccasunrescod = $error->getCode();
          }
          $cpe->update();
        }

    }elseif($tipo =='3'){
      $buscarcomp = DB::tABLE('cpe_cabecera')->where('ticketbaja',$ticket)->where('tdocod','03')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->get();

        foreach ($buscarcomp as $comp) {
          
          $cpe = cpe_cabecera::findOrFail($comp->IdCpe_cabecera);
          if($res->getCode()=='0'){
            $cpe->ccasunrescod = '8';
          }else{
            $cpe->ccasunrescod = $error->getCode();
          }
          $cpe->update();
        }

    }
  
     return Redirect::to('/SisFact');


  }

       public function enviar_comprobante(Request $request){

        $rucemp = trim(Auth::user()->IdEmpresa);
        $empresa = Empresa::findOrFail($rucemp);
        
        $correo_envio =env('MAIL_USERNAME',$empresa->correo_envio);
        $contrasena_envio = env('MAIL_PASSWORD', $empresa->contrasena_envio);

        $cabpdf = DB::tABLE('cpe_cabecera')->where('IdCpe_cabecera',$request->get('comprobante'))->first();

        $sucursal = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',$cabpdf->id_empresa_negocio)->first();

        $corcli = $request->get('txt_correo');
                
     ///   self::generarpdfgeneral($request->get('comprobante'));

        $cliente = DB::tABLE('cliente')->where('clinum',$cabpdf->ccandi)->where('rucemp',$rucemp)->first();
      
    
        $ruta_pdf = public_path().'/pdf/';
        $ruta_xml = public_path().'/xml/';

        //$numdoc = str_pad($cabpdf->numdoc,8,"0", STR_PAD_LEFT);

        $numdoc = $cabpdf->numdoc;

        $nompdffile=$rucemp.'-'.$cabpdf->tdocod.'-'.$cabpdf->serdoc.'-'.$numdoc.'.pdf'; 
        $nomxmlfile=$rucemp.'-'.$cabpdf->tdocod.'-'.$cabpdf->serdoc.'-'.$numdoc.'.xml'; 


        $destino4 = "";
        $destino5 = "";

        Config::set('mail.username', $empresa->correo_envio);
        Config::set('mail.password', $empresa->contrasena_envio);

         if(file_exists($ruta_pdf.$nompdffile)){
            $destino4=$ruta_pdf.$nompdffile;
         
          }

          if(file_exists($ruta_xml.$nomxmlfile)){
           
            $destino5=$ruta_xml.$nomxmlfile;
          }
          

                $objDemo = new \stdClass();
              
                $objDemo->tipo_comprobante = 'Comprobante Electrónica';
                $objDemo->sender = $empresa->correo_envio;
                $objDemo->receiver = $cliente->clinom;
                $objDemo->invoicepdf = $destino4;
                $objDemo->invoicexml = $destino5;
                $objDemo->empresa = $empresa->NomEmpresa;

                
                if(!empty($corcli)){
                  try{
                     Mail::to($corcli)->send(new FacturacionEmail($objDemo,$destino4,$destino5,$empresa->correo_envio));
                  }catch(\Exception $e){
                    //dd($e);
                  }
                 
                }
           
 

          return Redirect::to('/SisFact');
      }
     
     public function generarpdfgeneral($venta){


      $rucemp =Auth::user()->IdEmpresa;
      $rutapdf = public_path().'/pdf/';

      self::generarqr($venta);

      $empresa = Empresa::findOrFail($rucemp);

      $sucursal = DB::tABLE('empresa_negocios')
      ->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first();
     

      $cabpdf = DB::tABLE('cpe_cabecera')
      ->leftjoin('moneda as mon','cpe_cabecera.moncod','=','mon.moncod')
      ->leftjoin('tipo_documento','tipo_documento.tdocod','cpe_cabecera.tdocod')
      ->where('IdCpe_cabecera',$venta)
      ->first();


      $detpdf = DB::tABLE('cpe_detalle')
      ->leftjoin('productos','productos.IdProducto','cpe_detalle.IdProducto')
      ->where('IdCpe_cabecera',$venta)->get();

      $cliente= DB::tABLE('cliente as cli')
      ->leftjoin('cpe_cabecera as c','c.clicod','=','cli.clicod')
      ->where('IdCpe_cabecera','=',$venta)
      ->where('cli.clicod','=',$cabpdf->clicod)
      ->first();
                  
      $nompdffile=$rucemp.'-'.$cabpdf->tdocod.'-'.$cabpdf->serdoc.'-'.str_pad($cabpdf->numdoc,8,"0", STR_PAD_LEFT).'.pdf'; 

      $moneda = DB::tABLE('moneda')->where('moncod','=',$cabpdf->moncod)->first();


      $totalletras= MontoLetras::convertir(number_format($cabpdf->ccaitv,'2','.',''),$moneda->monnom,'Centimos');

      $numdoc = str_pad($cabpdf->numdoc,8,"0", STR_PAD_LEFT);
        
      $qrfile =  'QR-'.$rucemp.'-'.$cabpdf->tdocod.'-'.$cabpdf->serdoc.'-'.$numdoc.'.png'; 

      $imgqr = $qrfile;

      
      $view = \View::make('empresas.comprobantes.general.comprobante', compact('cabpdf','detpdf','cliente','totalletras','empresa','imgqr','sucursal','qrfile'));

                  
      $pdf = \App::make('dompdf.wrapper');
      $contenido = $view->render();
      $pdf->loadHTML($view)->setPaper('A4', 'lanscape')->save($rutapdf.$nompdffile);

    
  
        return 'realizado';
    }

  public function generarqr($comprobante){


      $cabecera = DB::tABLE('cpe_cabecera')->where('IdCpe_cabecera',$comprobante)->first();


      $numdoc = str_pad($cabecera->numdoc,8,"0", STR_PAD_LEFT);

      $qrfile =  'QR-'.$cabecera->IdEmpresa.'-'.$cabecera->tdocod.'-'.$cabecera->serdoc.'-'.$numdoc.'.png'; 

    
      $rucemp = trim(Auth::user()->IdEmpresa);
      $ruta = public_path().'/';
      $file = $ruta.$qrfile;
      
      return \QRCode::text($cabecera->ccaqr)->setMargin(1)->setSize(7)->setOutFile($file)->png();

    }


    public function enviar_ose($archivo){

        $urlService = 'https://ose-demo.nubefact.com/ol-ti-itcpe/billService';
        $soap = new SoapClient();
        $soap->setService($urlService);
        $soap->setCredentials('20541158902necosis','adm11700');
        $sender = new BillSender();
        $sender->setClient($soap);

        $xml = file_get_contents(public_path().'/xml/'.$archivo.'.xml');
        $result = $sender->send($archivo, $xml);

       
        if (!$result->isSuccess()) {
            // Error en la conexion con el servicio de SUNAT
            var_dump($result->getError());

            echo 'sdasd';
            return;
        }

        $cdr = $result->getCdrResponse();

        dd($cdr);
        file_put_contents(public_path().'/cdr/'.'R-'.$archivo.'zip', $result->getCdrZip());

        // Verificar CDR (Factura aceptada o rechazada)
        $code = (int)$cdr->getCode();

        
        if ($code === 0) {
            echo 'ESTADO: ACEPTADA'.PHP_EOL;
            if (count($cdr->getNotes()) > 0) {
                echo 'INCLUYE OBSERVACIONES:'.PHP_EOL;
                // Mostrar observaciones
                foreach ($cdr->getNotes() as $obs) {
                    echo 'OBS: '.$obs.PHP_EOL;
                }
            }

        } else if ($code >= 2000 && $code <= 3999) {
            echo 'ESTADO: RECHAZADA'.PHP_EOL;

        } else {
            /* Esto no debería darse, pero si ocurre, es un CDR inválido que debería tratarse como un error-excepción. */
            /*code: 0100 a 1999 */
            echo 'Excepción';
        }

        echo $cdr->getDescription().PHP_EOL;

      }

}
