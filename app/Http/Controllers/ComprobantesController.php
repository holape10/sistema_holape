<?php

namespace MasterSoft\Http\Controllers;
use Illuminate\Http\Request;
use MasterSoft\Http\Requests;
use Peru\Http\ContextClient;
use Peru\Jne\DniFactory;
use Peru\Sunat\RucFactory;
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

   public function monitoreo(Request $request)
    {
        $id_empresa_negocio = Auth::user()->id_empresa_negocio;

        // 1. Iniciamos la consulta base uniendo con usuarios y mesas
        $query = DB::table('cola_impresion as c')
            ->select(
                'c.id',
                'c.created_at',
                'c.impresora',
                'c.estado',
                'c.mesa as mesa_original', // Guardamos el valor original por si es 'Llevar' o 'Delivery'
                'c.usuario as usuario_original',
                'u.name as usuario_nombre',
                'u.apeusu as usuario_apellido',
                'm.mes_nom as mesa_nombre'
            )
            ->leftJoin('users as u', function($join) {
                $join->on(DB::raw('CAST(c.usuario AS UNSIGNED)'), '=', 'u.IdUsuario')
                     ->whereRaw('c.usuario REGEXP "^[0-9]+$"'); // Evita errores si hay datos antiguos de texto plano
            })
            ->leftJoin('mesas as m', function($join) {
                $join->on(DB::raw('CAST(c.mesa AS UNSIGNED)'), '=', 'm.mes_id')
                     ->whereRaw('c.mesa REGEXP "^[0-9]+$"'); // Evita errores si almacena 'Llevar' o 'Delivery'
            });

        // 2. Aplicamos Filtro de Búsqueda por Usuario (Busca en ID o Nombre/Apellido si viene texto)
        if ($request->filled('buscar_usuario')) {
            $buscarUsuario = $request->input('buscar_usuario');
            $query->where(function($q) use ($buscarUsuario) {
                $q->where('c.usuario', $buscarUsuario)
                  ->orWhere('u.name', 'LIKE', "%{$buscarUsuario}%")
                  ->orWhere('u.apeusu', 'LIKE', "%{$buscarUsuario}%");
            });
        }

        // 3. Aplicamos Filtro de Búsqueda por Mesa / Servicio (Busca en ID, Nombre de mesa o Tipo de Servicio)
        if ($request->filled('buscar_mesa')) {
            $buscarMesa = $request->input('buscar_mesa');
            $query->where(function($q) use ($buscarMesa) {
                $q->where('c.mesa', $buscarMesa)
                  ->orWhere('m.mes_nom', 'LIKE', "%{$buscarMesa}%");
            });
        }

        // 4. Obtenemos los últimos 100 registros filtrados
        $tickets = $query->orderBy('c.id', 'desc')
            ->limit(100)
            ->get();

        return view('empresas.comprobantes.monitoreo', compact('tickets'));
    }

    public function reimprimir($id)
    {
        try {
            // Buscamos el ticket y cambiamos su estado a 'pendiente'
            $actualizado = DB::table('cola_impresion')
                ->where('id', $id)
                ->update([
                    'estado' => 'pendiente',
                    'updated_at' => \Carbon\Carbon::now()
                ]);

            if ($actualizado) {
                return response()->json(['success' => true, 'message' => '¡Ticket enviado a la cola nuevamente!']);
            }

            return response()->json(['success' => false, 'message' => 'No se pudo encontrar el ticket.']);

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
        }
    }

    public function eliminarVarios(Request $request)
    {
        try {
            $ids = $request->input('ids');

            // Validamos que sea un array y no esté vacío
            if (!is_array($ids) || empty($ids)) {
                return response()->json([
                    'success' => false, 
                    'message' => 'No se seleccionaron registros para eliminar.'
                ]);
            }

            // Eliminamos todos los IDs seleccionados
            $eliminados = DB::table('cola_impresion')
                ->whereIn('id', $ids)
                ->delete();

            if ($eliminados > 0) {
                return response()->json([
                    'success' => true, 
                    'message' => '¡Se eliminaron ' . $eliminados . ' ticket(s) correctamente!'
                ]);
            }

            return response()->json([
                'success' => false, 
                'message' => 'No se pudo eliminar los registros o ya no existen.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false, 
                'message' => 'Error al eliminar: ' . $e->getMessage()
            ]);
        }
    }



    public function __construct()
    {
        $this->middleware('auth')->except(['autocomplete','autocomplete1']);;
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
    

      

        $comprobantes = DB::tABLE('cpe_cabecera as cpe_c')->select('ccacodsun','ccafem','tdodes','cpe_c.serdoc','cpe_c.numdoc','tdides','cpe_c.ccandi','cpe_c.ccanom','monnom','simbolo','cpe_c.ccaitv','cpe_c.IdEmpresa','cpe_c.tdocod','cpe_c.IdCpe_cabecera','cpe_c.ccanot','cpe_c.ccabaj','cpe_c.estado','cpe_c.enviado','totalcontado','totalcredito','est_equ_nom','est_equ_col','ut.name as nom_tec','ut.apeusu as ape_tec','marca','modelo','cpe_c.est_equ_id','fecha_hora','fallas')
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
    

      

        $comprobantes = DB::tABLE('cpe_cabecera as cpe_c')->select('ccacodsun','ccafem','tdodes','cpe_c.serdoc','cpe_c.numdoc','tdides','cpe_c.ccandi','cpe_c.ccanom','monnom','simbolo','cpe_c.ccaitv','cpe_c.IdEmpresa','cpe_c.tdocod','cpe_c.IdCpe_cabecera','cpe_c.ccanot','cpe_c.ccabaj','cpe_c.estado','cpe_c.enviado','totalcontado','totalcredito','est_equ_nom','est_equ_col','ut.name as nom_tec','ut.apeusu as ape_tec','marca','modelo','cpe_c.est_equ_id','fecha_hora','fallas','mensaje_estado','cliente.telefono')
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
    

      

        $comprobantes = DB::tABLE('cpe_cabecera as cpe_c')->select('ccacodsun','ccafem','tdodes','cpe_c.serdoc','cpe_c.numdoc','tdides','cpe_c.ccandi','cpe_c.ccanom','monnom','simbolo','cpe_c.ccaitv','cpe_c.IdEmpresa','cpe_c.tdocod','cpe_c.IdCpe_cabecera','cpe_c.ccanot','cpe_c.ccabaj','cpe_c.estado','cpe_c.enviado','totalcontado','totalcredito','est_equ_nom','est_equ_col','ut.name as nom_tec','ut.apeusu as ape_tec','marca','modelo','cpe_c.est_equ_id','fecha_hora','fallas')
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
    
        $comprobantes = DB::tABLE('cpe_cabecera as cpe_c')->select('ccacodsun','ccafem','tdodes','cpe_c.serdoc','cpe_c.numdoc','tdides','cpe_c.ccandi','cpe_c.ccanom','monnom','simbolo','cpe_c.ccaitv','cpe_c.IdEmpresa','cpe_c.tdocod','cpe_c.IdCpe_cabecera','cpe_c.ccanot','cpe_c.ccabaj','cpe_c.estado','cpe_c.enviado','totalcontado','totalcredito','est_equ_nom','est_equ_col','ut.name as nom_tec','ut.apeusu as ape_tec','marca','modelo','cpe_c.est_equ_id','fecha_hora','fallas')
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
        
        // 1. Cambiamos $documento por $documentoBusqueda
        $documentoBusqueda = $request->get('comp');

        $negocios = EmpresaNegocios::get();
        $sucursal = $request->get('sucursal');

        if(empty($sucursal)){
          $sucursal = $negocios->first()->id_empresa_negocio;
        }
        
        // 2. Usamos la nueva variable para sacar serie y número
        $ser = substr($documentoBusqueda,strpos($documentoBusqueda,'-')-4,4);
        $num = substr($documentoBusqueda,strpos($documentoBusqueda,'-')+1,8);

        if(empty($fecin)){
          $fecin = now()->modify('first day of this month')->format('Y-m-d');
          $fecfin = now()->modify('last day of this month')->format('Y-m-d');
        }

        $empresa = Empresa::findOrFail(Auth::user()->IdEmpresa);
            
        $comprobantes = DB::tABLE('cpe_cabecera as cpe_c')->select('ccacodsun','ccafem','tdodes','cpe_c.serdoc','cpe_c.numdoc','tdides','cpe_c.ccandi','cpe_c.ccanom','monnom','cpe_c.ccaitv','cpe_c.IdEmpresa','cpe_c.tdocod','cpe_c.IdCpe_cabecera','cpe_c.ccanot','cpe_c.ccabaj','cpe_c.estado','cpe_c.enviado','totalcontado','totalcredito')
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
         // 3. Actualizamos el 'use' y el 'if' con la nueva variable
         ->where(function ($query2) use ($documentoBusqueda,$ser,$num){
            if(!empty($documentoBusqueda)){
                $query2->where('serdoc',$ser)
                    ->Where('numdoc',$num);
            }
        })
        ->orderby('IdCpe_cabecera','desc')
        ->paginate(100);
   
        // 4. Pasamos 'documentoBusqueda' al compact en vez de 'documento'
        return view('empresas.comprobantes.indexcotizaciones',compact('comprobantes','fecin','fecfin','razsoc','documentoBusqueda','negocios','sucursal'));
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
    
        $cantidad =0;
     
          $comprobantes = DB::tABLE('cpe_cabecera as cpe_c')->select('ccacodsun','ccafem','tdodes','cpe_c.serdoc','cpe_c.numdoc','tdides','cpe_c.ccandi','cpe_c.ccanom','monnom','cpe_c.ccaitv','cpe_c.IdEmpresa','cpe_c.tdocod','cpe_c.IdCpe_cabecera','cpe_c.ccanot','cpe_c.ccabaj','cpe_c.estado','cpe_c.enviado','totalcontado','totalcredito','areas.are_emp_des','name','apeusu')
         // ->leftjoin('cpe_detalle','cpe_detalle.IdCpe_cabecera','cpe_c.IdCpe_cabecera')
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
          ->get();

          $cantidad = $comprobantes->sum('cdecan');
         
   
          return view('empresas.comprobantes.indexsalidas',compact('cantidad','negocios','sucursal','comprobantes','fecin','fecfin','documento','areas','area','tipo'));


        

        
         
    }


     public function detalle_salidas($venta){

    $cabecera = DB::tABLE('cpe_cabecera')
    ->join('tipo_documento','tipo_documento.tdocod','cpe_cabecera.tdocod')
    ->where('IdCpe_cabecera',$venta)->first();

    $detalle = DB::tABLE('cpe_cabecera')
    ->join('cpe_detalle','cpe_detalle.IdCpe_cabecera','cpe_cabecera.IdCpe_cabecera')
    ->where('cpe_cabecera.IdCpe_cabecera',$venta)
    ->get();

    return view('empresas.comprobantes.detallesalidas',compact('cabecera','detalle'));

 }

   public function editar_salidas_productos($id){

    $cabecera = DB::tABLE('cpe_cabecera as cpe_c')->select('ccacodsun','ccafem','tdodes','cpe_c.serdoc','cpe_c.numdoc','tdides','cpe_c.ccandi','cpe_c.ccanom','monnom','cpe_c.ccaitv','cpe_c.IdEmpresa','cpe_c.tdocod','cpe_c.IdCpe_cabecera','cpe_c.ccanot','cpe_c.ccabaj','cpe_c.estado','cpe_c.enviado','totalcontado','totalcredito','areas.are_emp_des','name','apeusu','cpe_c.are_emp_id','usu_rec')
          ->join('tipo_documento as tip_d','cpe_c.tdocod','=','tip_d.tdocod')
          ->join('tipo_documento_identidad as tdi','cpe_c.tdicod','=','tdi.tdicod')
          ->join('moneda as mon','cpe_c.moncod','=','mon.moncod')
          ->leftjoin('users','users.IdUsuario','cpe_c.usu_rec')
          ->leftjoin('areas','areas.are_emp_id','cpe_c.are_emp_id')
          ->where('cpe_c.id_empresa_negocio',Auth::user()->id_empresa_negocio)
          ->where('cpe_c.tdocod','81')
           ->where('cpe_c.IdCpe_cabecera',$id)
          ->first();

      $detalle = DB::tABLE('cpe_detalle')->where('IdCpe_cabecera',$id)->get();

        $datos = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first(); 

        $creditos = DB::tABLE('credito_dias')->get();

        $documentos = DB::tABLE('tipo_documento_identidad')->where('tdiest','=','Activo')->orderby('tdicod')->get();

        $rucemp = trim(Auth::user()->IdEmpresa);

            $senudoc = DB::tABLE('empresa_negocios')
                    ->select('serieguia','numeroguia')
                    ->where('IdEmpresa','=',$rucemp)
                    ->where('id_empresa_negocio','=',Auth::user()->id_empresa_negocio)
                    ->first(); 

          $motivos = DB::tABLE('motivo_traslado')
        ->orderBy('motivo','asc')->get();

        $modalidades = DB::tABLE('modalidad_traslado')
        ->orderBy('modalidad','asc')->get();

        $mediospagos = DB::tABLE('medios_pagos')->get();

         // consultar tipos de documentos de identidad
        $docidentidad = DB::tABLE('tipo_documento_identidad')->where('tdiest','=','Activo')->orderBy('tdicod','asc')->get();

        //consultar tipo de documento 
        $doccomprobante = DB::tABLE('tipo_documento')->where('tdoest','=','Activo')->orderBy('tdocod','asc')->get();

         // consultar tipos de monedas
        $monedas = DB::tABLE('moneda')->where('monest','=','Activo')->orderby('moncod','asc')->get();

        $categorias = DB::tABLE('categorias')
      //  ->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
        ->orderby('cat_nom','asc')
        ->get();

        $clientes = DB::tABLE('cliente')->get();

        $comprobante = DB::tABLE('tipo_documento')->get();

        $tipodocumento = DB::tABLE('tipo_documento_identidad')->get();

        $igv = DB::tABLE('tipo_igv')->where('tigest','=','Activo')
        ->orderBy('tigcod','asc')->get();

        $unidades = DB::tABLE('unidad_medida')
        ->where('umeest','=','Activo')
        ->orderBy('umecod','asc')->get();

        $mozos = DB::tABLE('users')
        ->join('role_user','role_user.user_IdUsuario','users.IdUsuario')
        ->where('role_id','5')
        ->get();

        $mediospagos = DB::tABLE('medios_pagos')->get();

          $sucursal = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first();

        $almacen = DB::tABLE('almacenes')
        ->where('predeterminado','1')
        ->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
        ->first();


      $productos = DB::tABLE('productos')
        ->select('procod','marca','pronom','propun','productos.IdProducto','umecod','promocion','color','imagenproducto','precio2',DB::raw("(SELECT stock FROM producto_stock as pro WHERE productos.IdProducto = pro.IdProducto AND tipo='1' AND id_almacen='".$almacen->id_almacen."' AND id_empresa_negocio='".Auth::user()->id_empresa_negocio."') as stock"),'precio',DB::raw("(SELECT count(*) FROM productos as pro WHERE productos.IdProducto = pro.pro_rel AND tipo='2') as cont_pre"))
        ->join('producto_empresa','producto_empresa.IdProducto','productos.IdProducto')
        ->join('producto_stock','producto_stock.IdProducto','producto_empresa.IdProducto')
      
        ->where('tipo','1')
        ->where('producto_empresa.id_empresa_negocio',Auth::user()->id_empresa_negocio)
        ->where('id_almacen',$almacen->id_almacen)
        ->groupby('productos.IdProducto')
        ->orderby('productos.pronom')
        ->orderby('productos.umecod')
        ->get();

        $colaboradores = DB::tABLE('users')->get();
        $areas = DB::tABLE('areas')->get();

      return view('empresas.puntosventas.editarsalidasproductos',compact('categorias','comprobante','tipodocumento','igv','unidades','unidades','mozos','creditos','mediospagos','clientes','documentos','datos','monedas','senudoc','motivos','modalidades','docidentidad','doccomprobante','monedas','productos','colaboradores','areas','detalle','cabecera'));

  }


    public function indexAutoconsumos(Request $request)
    {
        $razsoc = $request->get('cliente');
        $fecin = $request->get('fecin');
        $fecfin = $request->get('fecfin');

        $negocios = DB::table('empresa_negocios')->get();
        $sucursal = $request->get('sucursal');

        if (!empty($sucursal)) {
            $dat_suc = DB::table('empresa_negocios')->where('id_empresa_negocio', $sucursal)->first();
        } else {
            $dat_suc = DB::table('empresa_negocios')->where('id_empresa_negocio', $negocios->first()->id_empresa_negocio)->first();
        }

        //$documento = $request->get('comp');
        $documentoBusqueda = $request->get('comp');

        $ser = substr($documentoBusqueda, strpos($documentoBusqueda, '-') - 4, 4);
        $num = substr($documentoBusqueda, strpos($documentoBusqueda, '-') + 1, 8);

        if (!empty($documentoBusqueda)) {
            // Intenta buscar el guion para separar serie y numero
            $posGuion = strpos($documentoBusqueda, '-');
            if ($posGuion !== false) {
                // Si hay un guion, se asume formato SERIE-NUMERO
                $ser = substr($documentoBusqueda, 0, $posGuion);
                $num = substr($documentoBusqueda, $posGuion + 1);
            } else {
                // Si no hay guion, se asume que es solo el NUMERO
                $num = $documentoBusqueda;
            }
        }

        if (empty($fecin)) {
            $fecin = now()->modify('first day of this month')->format('Y-m-d');
            $fecfin = now()->modify('last day of this month')->format('Y-m-d');
        }

        $empresa = Empresa::findOrFail(Auth::user()->IdEmpresa);
        $IdEmpresa = Auth::user()->IdEmpresa;

        $comprobantes = DB::table('cpe_cabecera as cpe_c')
            ->select('cliente.telefono', 'ccadessun', 'cpe_c.tdocod', 'ccacodsun', 'ccafem', 'cpe_c.fecha_hora', 'tdodes', 'cpe_c.serdoc', 'cpe_c.numdoc', 'tdides', 'cpe_c.ccandi', 'cpe_c.ccanom', 'monnom', 'cpe_c.ccaitv', 'cpe_c.IdEmpresa', 'cpe_c.IdCpe_cabecera', 'cpe_c.ccanot', 'cpe_c.ccabaj',  'cpe_c.estado', 'cpe_c.enviado', 'totalcontado', 'totalcredito', 'clicorcli', 'cliente.clicor','mesa.mes_nom', 'usua.name', 'usua.apeusu', 'des_doc', DB::raw('(select numdoc from cpe_cabecera where IdCpe_cabecera=cpe_c.ped_ref) as pedido'))
            ->leftjoin('cliente', 'cliente.clicod', 'cpe_c.clicod')
            ->leftjoin('pedidos as pide', 'pide.ped_id', 'cpe_c.ped_id')
            ->leftjoin('users as usua', 'usua.IdUsuario', 'pide.mozo')
            ->leftjoin('mesas as mesa', 'mesa.mes_id', 'pide.mes_id')
            ->join('tipo_documento as tip_d', 'cpe_c.tdocod', '=', 'tip_d.tdocod')
            ->join('tipo_documento_identidad as tdi', 'cpe_c.tdicod', '=', 'tdi.tdicod')
            ->join('moneda as mon', 'cpe_c.moncod', '=', 'mon.moncod')
            ->where('cpe_c.ccafem', '>=', $fecin)
            ->where('cpe_c.ccafem', '<=', $fecfin)
            ->where('cpe_c.id_empresa_negocio', Auth::user()->id_empresa_negocio)
            ->where('cpe_c.tdocod', '99') // FILTRO CLAVE: tdocod = 99 (Autoconsumo)
            ->where(function ($query1) use ($razsoc) {
                if (!empty($razsoc)) {
                    $query1->where('cpe_c.ccanom', 'like', '%' . $razsoc . '%')
                        ->orWhere('cpe_c.ccandi', '=', $razsoc);
                }
            })
            ->where(function ($query2) use ($documentoBusqueda, $ser, $num) {
                if (!empty($documentoBusqueda)) {
                    $query2->where('serdoc', $ser)
                        ->orWhere('numdoc', $num);
                }
            })

            ->where(function ($query2) use ($documentoBusqueda, $ser, $num){
            if(!empty($documentoBusqueda)){
                if (!is_null($ser) && !is_null($num)) {
                    // Si se detectó formato SERIE-NUMERO
                    $query2->where('cpe_c.serdoc', 'like', $ser . '%') // Busca por la serie ingresada
                           ->where('cpe_c.numdoc', 'like', $num . '%'); // Y por el número
                } elseif (!is_null($num)) {
                    // Si solo se detectó NUMERO
                    $query2->where('cpe_c.numdoc', 'like', $num . '%'); // Busca solo por el número
                }
            }
        })
            ->orderby('IdCpe_cabecera', 'desc')
            ->paginate(100);

            return view('empresas.comprobantes.indexautoconsumos', compact('negocios', 'sucursal', 'comprobantes', 'fecin', 'fecfin', 'razsoc', 'documentoBusqueda', 'dat_suc', 'rutaBusqueda'));

        //return view('empresas.comprobantes.indexautoconsumos', compact('negocios', 'sucursal', 'comprobantes', 'fecin', 'fecfin', 'razsoc', 'documento', 'dat_suc'));
    }



    public function index(Request $request)
    {

        
         
        $razsoc = $request->get('cliente');
        $fecin = $request->get('fecin');
        $fecfin = $request->get('fecfin');

        $negocios = DB::tABLE('empresa_negocios')->get();
        $sucursal = $request->get('sucursal');

        if(!empty($sucursal)){
          $dat_suc = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',$sucursal)->first();

        }else{
           $dat_suc = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',$negocios->first()->id_empresa_negocio)->first();
        }


        //$documento = $request->get('comp');
        $documentoBusqueda = $request->get('comp'); 

        //$ser = substr($documento,strpos($documento,'-')-4,4);
        //$num = substr($documento,strpos($documento,'-')+1,8);
        $ser = null;
        $num = null;

        if (!empty($documentoBusqueda)) {
            // Intenta buscar el guion para separar serie y numero
            $posGuion = strpos($documentoBusqueda, '-');
            if ($posGuion !== false) {
                // Si hay un guion, se asume formato SERIE-NUMERO
                $ser = substr($documentoBusqueda, 0, $posGuion);
                $num = substr($documentoBusqueda, $posGuion + 1);
            } else {
                // Si no hay guion, se asume que es solo el NUMERO
                $num = $documentoBusqueda;
            }
        }

        if(empty($fecin)){

          $fecin = now()->modify('first day of this month')->format('Y-m-d');
          $fecfin = now()->modify('last day of this month')->format('Y-m-d');

        }
  
        $empresa = Empresa::findOrFail(Auth::user()->IdEmpresa);
           
        $IdEmpresa = Auth::user()->IdEmpresa;

      
        $comprobantes = DB::tABLE('cpe_cabecera as cpe_c')->select('cliente.telefono','ccadessun','cpe_c.tdocod','ccacodsun','ccafem','cpe_c.fecha_hora','tdodes','cpe_c.serdoc','cpe_c.numdoc','tdides','cpe_c.ccandi','cpe_c.ccanom','monnom','cpe_c.ccaitv','cpe_c.IdEmpresa','cpe_c.tdocod','cpe_c.IdCpe_cabecera','cpe_c.ccanot','cpe_c.ccabaj','cpe_c.estado','cpe_c.enviado','totalcontado','totalcredito','clicorcli','cliente.clicor','mesa.mes_nom','usua.name','usua.apeusu','des_doc',DB::RAW('(select numdoc from cpe_cabecera where IdCpe_cabecera=cpe_c.ped_ref) as pedido'))
        ->leftjoin('cliente','cliente.clicod','cpe_c.clicod')
        ->leftjoin('pedidos as pide','pide.ped_id','cpe_c.ped_id')
        ->leftjoin('users as usua','usua.IdUsuario','pide.mozo')
        ->leftjoin('mesas as mesa','mesa.mes_id','pide.mes_id')
        ->join('tipo_documento as tip_d','cpe_c.tdocod','=','tip_d.tdocod')
        ->join('tipo_documento_identidad as tdi','cpe_c.tdicod','=','tdi.tdicod')
        //->join('mesas as mesa','pedidos.mes_id','=','mesa.mes_id')
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
            if(!empty($razsoc)){
                $query1->where('cpe_c.ccanom','like','%'.$razsoc.'%')
                    ->orWhere('cpe_c.ccandi','=',$razsoc);
            }
        })
        ->where(function ($query2) use ($documentoBusqueda, $ser, $num){
            if(!empty($documentoBusqueda)){
                if (!is_null($ser) && !is_null($num)) {
                    // Si se detectó formato SERIE-NUMERO
                    $query2->where('cpe_c.serdoc', 'like', $ser . '%') // Busca por la serie ingresada
                           ->where('cpe_c.numdoc', 'like', $num . '%'); // Y por el número
                } elseif (!is_null($num)) {
                    // Si solo se detectó NUMERO
                    $query2->where('cpe_c.numdoc', 'like', $num . '%'); // Busca solo por el número
                }
            }
        })
        ->orderby('IdCpe_cabecera','desc')
        ->paginate(100);
   
          return view('empresas.comprobantes.index',compact('negocios','sucursal','comprobantes','fecin','fecfin','razsoc','documentoBusqueda','dat_suc'));
         
    }


      public function lista_nota_credito(Request $request)
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
    

        $comprobantes = DB::tABLE('cpe_cabecera as cpe_c')->select('cliente.telefono','ccadessun','cpe_c.tdocod','ccacodsun','ccafem','tdodes','cpe_c.serdoc','cpe_c.numdoc','tdides','cpe_c.ccandi','cpe_c.ccanom','monnom','cpe_c.ccaitv','cpe_c.IdEmpresa','cpe_c.tdocod','cpe_c.IdCpe_cabecera','cpe_c.ccanot','cpe_c.ccabaj','cpe_c.estado','cpe_c.enviado','totalcontado','totalcredito','clicorcli','cliente.clicor','des_doc')
        ->leftjoin('cliente','cliente.clicod','cpe_c.clicod')
        ->join('tipo_documento as tip_d','cpe_c.tdocod','=','tip_d.tdocod')
        ->join('tipo_documento_identidad as tdi','cpe_c.tdicod','=','tdi.tdicod')
        ->join('moneda as mon','cpe_c.moncod','=','mon.moncod')
        ->where('cpe_c.ccafem','>=',$fecin)
        ->where('cpe_c.ccafem','<=',$fecfin)
        ->where('cpe_c.id_empresa_negocio',Auth::user()->id_empresa_negocio)
        ->where(function ($query) {
          $query->where('cpe_c.tdocod','07');
          })
          ->where(function ($query1) use ($razsoc){
            if(!empty($razsoc)){
                $query1->where('cpe_c.ccanom','like','%'.$razsoc.'%')
                    ->orWhere('cpe_c.ccandi','=',$razsoc);
            }
        })
        ->where(function ($query2) use ($documento,$ser,$num){
            if(!empty($documento)){
                $query2->where('serdoc',$ser)
                       ->orWhere('numdoc',$num);
            }
        })
        ->orderby('IdCpe_cabecera','desc')
        ->paginate(100);
   
          return view('empresas.comprobantes.lista_nota_credito',compact('negocios','sucursal','comprobantes','fecin','fecfin','razsoc','documento'));

    }


     public function indexpedidos(Request $request)
    {
        $razsoc = $request->get('cliente');
        $estado = $request->get('estado');
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

                $comprobantes = DB::tABLE('cpe_cabecera as cpe_c')->select('facturado','ccacodsun','ccafem','tdodes','cpe_c.serdoc','cpe_c.numdoc','tdides','cpe_c.ccandi','cpe_c.ccanom','monnom','cpe_c.ccaitv','cpe_c.IdEmpresa','cpe_c.tdocod','cpe_c.IdCpe_cabecera','cpe_c.ccanot','cpe_c.ccabaj','cpe_c.estado','cpe_c.enviado','totalcontado','totalcredito','clicorcli','cliente.clicor','facturado')
        ->join('cliente','cliente.clicod','cpe_c.clicod')
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
            if(!empty($razsoc)){
                $query1->where('cpe_c.ccanom','like','%'.$razsoc.'%')
                    ->orWhere('cpe_c.ccandi','=',$razsoc);
            }
        })
         ->where(function ($query1) use ($estado){
            if($estado!='Todos'){
                $query1->where('cpe_c.facturado',$estado);
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

 public function autocompleteprov(Request $request,$search){     

    try{

      $rucemp = trim(Auth::user()->IdEmpresa);
      $ruc = Proveedor::where('prov_ruc','=',$search)->take(10)->get();
      $results = array();
 
      if(count($ruc)==0){
 
         if(strlen($search)<='8'){ 
               $leer_respuesta = self::consultardni($search);
                $results[] = ['value'=>$leer_respuesta['data']['numero'],'nom'=>$leer_respuesta['data']['nombres'].' '.$leer_respuesta['data']['apellido_paterno'].' '.$leer_respuesta['data']['apellido_materno'],'dir'=>'--','tdicod'=>'1']; 
         }else{         
              $leer_respuesta = self::consultaruc($search); 
              $results[] = ['value'=>$leer_respuesta['data']['ruc'],'nom'=>$leer_respuesta['data']['nombre_o_razon_social'],'dir'=>$leer_respuesta['data']['direccion_completa'],'tdicod'=>'6','ubigeo'=>'']; 
         }        
       }else{ 
         foreach($ruc as $cli => $search){
            $numnom=$search->clinum;          
           $results[] = ['value'=>$numnom,'nom'=>$search->clinom,'dir'=>$search->clidir,'tdicod'=>$search->tdicod,'clicod'=>$search->clicod,'cor'=>$search->clicor,'tel'=>$search->telefono,'fecha_nacimiento'=>$search->fecha_nacimiento,'cuenta12'=>$search->cuenta12,'sex_id'=>$search->sex_id,'est_civ_id'=>$search->est_civ_id,'ubigeo'=>''];
         }        
       }

        return response()->json($results);

    }catch(\Exception $e){
       return response()->json(['error' => 'NO SE ENCONTRÓ EL RUC']);       
    }     
    

    }

       public function autocomplete($cliente){
    try {
        // Buscamos al cliente seleccionando explícitamente los campos necesarios
        $ruc = Cliente::where('clinum', '=', $cliente)
              ->select('clicod', 'clinum', 'clinom', 'clidir', 'clicor', 'telefono', 'fecha_nacimiento', 'cuenta12', 'sex_id', 'est_civ_id', 'tdicod')
              ->take(10)
              ->get();

        $results = array();

        if(count($ruc) == 0){ 
            if(strlen($cliente) <= 8){ 
                $leer_respuesta = self::consultardni($cliente);
                $results[] = ['value'=>$leer_respuesta['data']['numero'],'nom'=>$leer_respuesta['data']['nombres'].' '.$leer_respuesta['data']['apellido_paterno'].' '.$leer_respuesta['data']['apellido_materno'],'dir'=>'--','tdicod'=>'1']; 
            } else {          
                $leer_respuesta = self::consultaruc($cliente); 
                $results[] = ['value'=>$leer_respuesta['data']['ruc'],'nom'=>$leer_respuesta['data']['nombre_o_razon_social'],'dir'=>$leer_respuesta['data']['direccion_completa'],'tdicod'=>'6','ubigeo'=>'']; 
            }        
        } else { 
            // CAMBIO AQUÍ: Usamos '$item' en el bucle para no confundir con el parámetro '$cliente'
            foreach($ruc as $item){
                $results[] = [
                    'value'            => $item->clinum,
                    'nom'              => $item->clinom,
                    'dir'              => $item->clidir,
                    'tdicod'           => $item->tdicod,
                    'clicod'           => $item->clicod,
                    'cor'              => $item->clicor,
                    'tel'              => $item->telefono,
                    // Aseguramos que el nombre de la clave sea 'fecnac' o 'fecha_nacimiento' según lo que tu JS espera                    
                    'fecnac'           => $item->fecha_nacimiento, // Enviamos ambos por seguridad
                    'fecha_nacimiento' => $item->fecha_nacimiento, 
                    'cuenta12'         => $item->cuenta12,
                    'sex_id'           => $item->sex_id,
                    'est_civ_id'       => $item->est_civ_id,
                    'ubigeo'           => ''
                ];
            }        
        }

        return response()->json($results);

    } catch(\Exception $e){
        return response()->json(['error' => 'Error en la búsqueda']);
    }
}



     public function autocomplete1($cliente){
    // $search = $request->term;


    try{        
 
      //  $rucemp = trim(Auth::user()->IdEmpresa); 
       $ruc = Cliente::where('clinum','=',$cliente)
       //->where('rucemp','=',$rucemp)
     //  ->where('cliest','=','Activo')
       ->take(10)->get();
       $results = array();
  
       if(count($ruc)==0){
 
         if(strlen($cliente)<='8'){ 
               $leer_respuesta = self::consultardni($cliente);
                $results[] = ['value'=>$leer_respuesta['data']['numero'],'nom'=>$leer_respuesta['data']['nombres'].' '.$leer_respuesta['data']['apellido_paterno'].' '.$leer_respuesta['data']['apellido_materno'],'dir'=>'--','tdicod'=>'1']; 
         }else{         
              $leer_respuesta = self::consultaruc($cliente); 
              $results[] = ['value'=>$leer_respuesta['data']['ruc'],'nom'=>$leer_respuesta['data']['nombre_o_razon_social'],'dir'=>$leer_respuesta['data']['direccion_completa'],'tdicod'=>'6','ubigeo'=>'']; 
         }        
       }else{ 
         foreach($ruc as $cli => $cliente){
            $numnom=$cliente->clinum;          
           $results[] = ['value'=>$numnom,'nom'=>$cliente->clinom,'dir'=>$cliente->clidir,'tdicod'=>$cliente->tdicod,'clicod'=>$cliente->clicod,'cor'=>$cliente->clicor,'tel'=>$cliente->telefono,'fecha_nacimiento'=>$cliente->fecha_nacimiento,'cuenta12'=>$cliente->cuenta12,'sex_id'=>$cliente->sex_id,'est_civ_id'=>$cliente->est_civ_id,'ubigeo'=>''];
         }        
       }
 
         return response()->json($results);
 
     }catch(\Exception $e){
        dd($e);
        return response()->json(['error' => 'NO SE ENCONTRÓ']);
        
     }

    

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

  
  

   

    //NOTA DE CREDITO Y DÉBITO

    public function tiponotacd($tdocod=0,$idcabecera=0,$ncdcod){

      $rucemp = Auth::user()->IdEmpresa;

      $cabecera = DB::tABLE('cpe_cabecera as cab')
      ->leftjoin('cliente as cli','cab.clicod','=','cli.clicod')
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
        

        }elseif($tdocod=='03'){

          $senuncd = DB::tABLE('empresa_negocios')->select('BcseEmpresa','BcnuEmpresa')->where('id_empresa_negocio',$cabecera->id_empresa_negocio)->first();

          
        }

        if($ncdcod=='07'){
            $nota = DB::tABLE('tipo_nota_credito')->get();
        }elseif($ncdcod=='08'){
            $nota = DB::tABLE('tipo_nota_debito')->get();
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


    public function ingresarpanel($idempresa,$idnegocio){

      $user =Auth::user()->IdUsuario;
      $regemp = User::findOrFail($user);
      $regemp->IdEmpresa = $idempresa;
      $regemp->id_empresa_negocio = $idnegocio;
      $regemp->update();

      return Redirect::to('/SisFact');

    }





     public function consultaruc($ruc){
        
    
       
          $params = json_encode(['ruc' => $ruc]);
          $curl = curl_init();
          curl_setopt_array($curl, array(
              CURLOPT_URL => "https://apiperu.dev/api/ruc",
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_CUSTOMREQUEST => "POST",
              CURLOPT_SSL_VERIFYPEER => false,
              CURLOPT_POSTFIELDS => $params,        
              CURLOPT_HTTPHEADER => [
                  'Accept: application/json',
                  'Content-Type: application/json',
                  'Authorization: Bearer c7c656604942b0a6df5fa225835e99eb7376cf841d38781b91f651f72e03cc09'
              ],        
          ));
          $response = curl_exec($curl);
          $err = curl_error($curl);
          curl_close($curl);

          if ($err) {
            return $err;

          } else {

            $leer_respuesta = json_decode($response, true);

            return  $leer_respuesta;
          }
             
                  
    }

    public function consultardni($dni){

          $params = json_encode(['dni' => $dni]);
          $curl = curl_init();
          curl_setopt_array($curl, array(
              CURLOPT_URL => "https://apiperu.dev/api/dni",
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_CUSTOMREQUEST => "POST",
              CURLOPT_SSL_VERIFYPEER => false,
              CURLOPT_POSTFIELDS => $params,        
              CURLOPT_HTTPHEADER => [
                  'Accept: application/json',
                  'Content-Type: application/json',
                  'Authorization: Bearer c7c656604942b0a6df5fa225835e99eb7376cf841d38781b91f651f72e03cc09'
              ],        
          ));
          $response = curl_exec($curl);
          $err = curl_error($curl);
          curl_close($curl);

          if ($err) {
            return $err;

          } else {

            $leer_respuesta = json_decode($response, true);
          
            return  $leer_respuesta;
          }


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

  public function revisarComprobantes1033(Request $request)
    {
        try {
            // Se puede agregar lógica para filtrar por los IDs seleccionados
            // Si el botón "Revisar" es para todos los de la lista visible, o si pasas IDs.
            // Por simplicidad, asumiremos que actualiza todos los de la empresa actual
            // que tienen 1033. Si necesitas seleccionar por ID, ajusta aquí.

            // Puedes obtener los IDs seleccionados del formulario si usas checkboxes
            $selectedIds = $request->input('selected_comprobantes_ids'); // Asumiendo que envías un array de IDs

            // --- Lógica para el código 1033 (ya existente) ---
            $query1033 = cpe_cabecera::where('ccacodsun', '1033')
                                 ->where('id_empresa_negocio', Auth::user()->id_empresa_negocio);

            if (!empty($selectedIds)) {
                $query1033->whereIn('IdCpe_cabecera', $selectedIds);
            }

            $updatedCount1033 = $query1033->update([
                'ccacodsun' => '0',
                'ccadessun' => 'Actualizado manualmente: Comprobante ya registrado en SUNAT (código 1033).',
                'enviado' => '1'
            ]);

            // --- Nueva lógica para el código 1032 ---
            $query1032 = cpe_cabecera::where('ccacodsun', '1032')
                                 ->where('id_empresa_negocio', Auth::user()->id_empresa_negocio);

            if (!empty($selectedIds)) {
                $query1032->whereIn('IdCpe_cabecera', $selectedIds);
            }
            
            $currentDate = now()->format('Y-m-d'); // Obtiene la fecha actual en formato YYYY-MM-DD

            $updatedCount1032 = $query1032->update([
                'ccabaj' => $currentDate, // Fecha actual
                'ccaitv' => 0, // Cambiar a 0
                'motivo_baja' => 'ENVIO FUERA DE FECHA O ERROR DE COMPROBANTE', // Texto específico
                'est_sunat' => 'ANULADO' // Cambiar a 'ANULADO'
            ]);

            \Log::info("Se actualizaron {$updatedCount1033} comprobantes con ccacodsun='1033' a aceptado por SUNAT.");
            \Log::info("Se actualizaron {$updatedCount1032} comprobantes con ccacodsun='1032' con datos de baja.");

            return response()->json([
                'success' => true,
                'message' => "Se actualizaron {$updatedCount1033} comprobantes a estado 'Aceptado por SUNAT' y {$updatedCount1032} comprobantes con estado 'Anulado' (1032)."
            ]);

        } catch (\Exception $e) {
            \Log::error('Error al revisar y actualizar comprobantes con código 1033 o 1032: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Ocurrió un error al intentar revisar los comprobantes.'
            ], 500);
        }
    }

       public function enviar_comprobante(Request $request){


        $corcli = $request->get('txt_correo');
        
        $cpe_cabecera = new cpe_cabecera;
                
              if(!empty($corcli)){                 
                 $cpe_cabecera->enviar_comprobante_correo($request->get('comprobante'),$corcli);
              }


 

          return Redirect::to('/SisFact');
      }
     
   

  public function salidasproductos($tdocod=0,$cpe=0)
    { 

        $datos = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first(); 

        $creditos = DB::tABLE('credito_dias')->get();

        $documentos = DB::tABLE('tipo_documento_identidad')->where('tdiest','=','Activo')->orderby('tdicod')->get();

        $rucemp = trim(Auth::user()->IdEmpresa);

            $senudoc = DB::tABLE('empresa_negocios')
                    ->select('serieguia','numeroguia')
                    ->where('IdEmpresa','=',$rucemp)
                    ->where('id_empresa_negocio','=',Auth::user()->id_empresa_negocio)
                    ->first(); 

          $motivos = DB::tABLE('motivo_traslado')
        ->orderBy('motivo','asc')->get();

        $modalidades = DB::tABLE('modalidad_traslado')
        ->orderBy('modalidad','asc')->get();

        $mediospagos = DB::tABLE('medios_pagos')->get();

         // consultar tipos de documentos de identidad
        $docidentidad = DB::tABLE('tipo_documento_identidad')->where('tdiest','=','Activo')->orderBy('tdicod','asc')->get();

        //consultar tipo de documento 
        $doccomprobante = DB::tABLE('tipo_documento')->where('tdoest','=','Activo')->orderBy('tdocod','asc')->get();

         // consultar tipos de monedas
        $monedas = DB::tABLE('moneda')->where('monest','=','Activo')->orderby('moncod','asc')->get();

        $categorias = DB::tABLE('categorias')
      //  ->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
        ->orderby('cat_nom','asc')
        ->get();

        $clientes = DB::tABLE('cliente')->get();

        $comprobante = DB::tABLE('tipo_documento')->get();

        $tipodocumento = DB::tABLE('tipo_documento_identidad')->get();

        $igv = DB::tABLE('tipo_igv')->where('tigest','=','Activo')
        ->orderBy('tigcod','asc')->get();

        $unidades = DB::tABLE('unidad_medida')
        ->where('umeest','=','Activo')
        ->orderBy('umecod','asc')->get();

        $mozos = DB::tABLE('users')
        ->join('role_user','role_user.user_IdUsuario','users.IdUsuario')
        ->where('role_id','5')
        ->get();

        $mediospagos = DB::tABLE('medios_pagos')->get();

          $sucursal = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first();

        $almacen = DB::tABLE('almacenes')
        ->where('predeterminado','1')
        ->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
        ->first();


      $productos = DB::tABLE('productos')
        ->select('procod','marca','pronom','propun','productos.IdProducto','umecod','promocion','color','imagenproducto','precio2',DB::raw("(SELECT stock FROM producto_stock as pro WHERE productos.IdProducto = pro.IdProducto AND tipo='1' AND id_almacen='".$almacen->id_almacen."' AND id_empresa_negocio='".Auth::user()->id_empresa_negocio."') as stock"),'precio',DB::raw("(SELECT count(*) FROM productos as pro WHERE productos.IdProducto = pro.pro_rel AND tipo='2') as cont_pre"))
        ->join('producto_empresa','producto_empresa.IdProducto','productos.IdProducto')
        ->join('producto_stock','producto_stock.IdProducto','producto_empresa.IdProducto')
      
        ->where('tipo','1')
        ->where('producto_empresa.id_empresa_negocio',Auth::user()->id_empresa_negocio)
        ->where('id_almacen',$almacen->id_almacen)
        ->groupby('productos.IdProducto')
        ->orderby('productos.pronom')
        ->orderby('productos.umecod')
        ->get();

        $colaboradores = DB::tABLE('users')->get();
        $areas = DB::tABLE('areas')->get();

        return view('empresas.puntosventas.salidasproductos',compact('categorias','comprobante','tipodocumento','igv','unidades','unidades','tdocod','cpe','mozos','creditos','mediospagos','clientes','documentos','datos','monedas','senudoc','motivos','modalidades','docidentidad','doccomprobante','monedas','productos','colaboradores','areas'));
    }

    
  

}
