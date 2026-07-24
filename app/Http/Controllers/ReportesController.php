<?php

namespace MasterSoft\Http\Controllers;

use Illuminate\Http\Request;
use MasterSoft\Http\Requests;
use MasterSoft\TipoIGV;
use MasterSoft\ReportesVentas;
use MasterSoft\Empresa;
use MasterSoft\EmpresaNegocios;
use MasterSoft\Cliente;
use MasterSoft\cpe_cabecera;
use MasterSoft\documento_relacionado;
use MasterSoft\unidad_medida;
use MasterSoft\cpe_detalle;
use MasterSoft\cpe_baja;
use MasterSoft\Comprobante;
use MasterSoft\compras_cabecera;
use MasterSoft\cpe_nota_detalle;
use MasterSoft\gastos_cabecera;
use MasterSoft\cpe_nota;
use MasterSoft\User;
use MasterSoft\tipo_documento_identidad;
use MasterSoft\tipo_documento;
use MasterSoft\productos;
use MasterSoft\tipocambio;
use MasterSoft\MontoLetras;
use Illuminate\Support\Facades\Auth;
use MasterSoft\Http\Requests\FacturaCreateFormRequest;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Input;
use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\FilePrintConnector;
use Mike42\Escpos\EscposImage;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;

use DB;
use Carbon;
use Excel;
use PDF;

class ReportesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
     
    public function __construct()
    {
        
        $this->middleware('auth')->except(['consulta_stock_productos']);
    }

    public function arqueodiario(Request $request){

      return view('empresas.reportes.reporte_arqueo_diario');

    }

    public function reporte_pagos_personal(Request $request){

      $fecin = $request->get('fecin');
      $fecfin = $request->get('fecfin');
      $colaborador = $request->get('personal');
     $pagos ="";

      $personal = DB::tABLE('users')->get();

      $dato_colaborador = DB::tABLE('users')->where('IdUsuario',$colaborador)->first();

      if(!empty($fecin)){
         $pagos = DB::tABLE('gastos_cabecera')
      ->join('gastos_detalle','gastos_cabecera.gast_cab_id','gastos_detalle.gast_cab_id')
      ->where('colaborador',$colaborador)
      ->where('gast_fec','>=',$fecin)
      ->where('gast_fec','<=',$fecfin)
      ->get();

      }
     
      return view('empresas.reportes.reporte_pagos_personal',compact('fecin','fecfin','pagos','personal','dato_colaborador'));

    }

    public function exportarclientes()
{
    // Traemos TODOS los clientes de la tabla sin filtros de empresa
    // Seleccionamos solo las columnas que necesitas para el reporte
    $clientes = Cliente::select('clinum', 'clinom', 'clidir', 'tdicod')->get();

    // Nombre del archivo con la fecha de hoy
    $filename = "lista_completa_clientes_" . date('d-m-Y') . ".csv";

    // Configuramos las cabeceras para la descarga
    $headers = [
        "Content-type"        => "text/csv; charset=UTF-8",
        "Content-Disposition" => "attachment; filename=$filename",
        "Pragma"              => "no-cache",
        "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
        "Expires"             => "0"
    ];

    $callback = function() use ($clientes) {
        $file = fopen('php://output', 'w');
        
        // BOM para que Excel en Windows reconozca Ñ y tildes correctamente
        fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

        // Escribimos los títulos de las columnas
        fputcsv($file, ['DNI/RUC', 'RAZON SOCIAL', 'DIRECCION', 'TIPO DOC']);

        // Recorremos todos los clientes y los metemos al archivo
        foreach ($clientes as $cli) {
            fputcsv($file, [
                $cli->clinum,
                $cli->clinom,
                $cli->clidir,
                $cli->tdicod
            ]);
        }
        fclose($file);
    };

    // Retornamos el archivo como una descarga directa
    return response()->stream($callback, 200, $headers);
}

    public function pdf_reporte_pagos_personal(Request $request){

      $fecin = $request->get('fecin');
      $fecfin = $request->get('fecfin');
      $colaborador = $request->get('personal');

      $personal = DB::tABLE('users')->get();

      $empresa = Empresa::findOrFail(Auth::user()->IdEmpresa);

      $sucursal = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first();

      $datos_colaborador = DB::tABLE('users')->where('IdUsuario',$colaborador)->first();
      
      $pagos = DB::tABLE('gastos_cabecera')
      ->join('gastos_detalle','gastos_cabecera.gast_cab_id','gastos_detalle.gast_cab_id')
      ->where('colaborador',$colaborador)
      ->where('gast_fec','>=',$fecin)
      ->where('gast_fec','<=',$fecfin)
      ->where('tipo_movimiento','GASTO')
      ->where(function ($query) {
                    $query->where('tip_gas_id','26')
                      ->orWhere('tip_gas_id','7');
      })            
      ->get();

      
      $totalgas = $pagos->sum('total');

       $rutapdf = public_path().'/pdfreportes/';

        $nompdffile = 'Pagos_Personal_'.$datos_colaborador->name.'_'.$datos_colaborador->apeusu.'.pdf';

       if(file_exists($rutapdf.$nompdffile)){

            unlink($rutapdf.$nompdffile);
        }

        $empresa = DB::tABLE('empresa')->where('IdEmpresa',Auth::user()->IdEmpresa)->first();
        $view = \View::make('formatos_reportes.reporte_pdf_pagos_personal', compact('sucursal','empresa','pagos','datos_colaborador','personal','fecin','fecfin','colaborador','totalgas'));

                  
          $pdf = \App::make('dompdf.wrapper');
          $contenido = $view->render();
          $pdf->loadHTML($view)->setPaper('A4', 'lanscape')->save($rutapdf.$nompdffile);


      
        


       if (file_exists($rutapdf.$nompdffile))
      {
        $headers = array(
              'Content-Type: application/pdf',
            );

        return response()->download($rutapdf.$nompdffile);

     }

    }
  public function generararqueodiario(Request $request){
        
         $medios = DB::tABLE('cuentas_cobrar_medios')->get();

     foreach($medios as $med){

        if($med->med_pag_id=='4'){
                 DB::tABLE('cuentas_cobrar_detalle')
                 ->where('numero_recibo',$med->numero_recibo)
                 ->where('cue_cob_det_id',$med->cue_cob_det_id)
                 ->update(['monto_efectivo'=>$med->monto]); 
        }

         if($med->med_pag_id=='5'){
                DB::tABLE('cuentas_cobrar_detalle')
                ->where('numero_recibo',$med->numero_recibo)
                ->where('cue_cob_det_id',$med->cue_cob_det_id)
                ->update(['monto_deposito'=>$med->monto]);
        }


        
       
      }


        $cliente = $request->get('cliente');
        $documento = $request->get('documento');
 
        $fecha = $request->get('fecin');

        $sucursal = DB::tABLE('empresa_negocios')
        ->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
        ->first();

        $empresa = DB::tABLE('empresa')
        ->where('IdEmpresa',$sucursal->IdEmpresa)
        ->first();


        $usuario = DB::tABLE('users')->where('IdUsuario',Auth::user()->IdUsuario)->first();
        
        $registroventascontado = DB::table('cpe_cabecera as cpe_c')->select('fecha_hora','ccacodsun','ccafem','tdodes','cpe_c.serdoc','cpe_c.numdoc','tdides','cpe_c.ccandi','cpe_c.ccanom','monnom','cpe_c.ccaitv','cpe_c.IdEmpresa','cpe_c.tdocod','cpe_c.IdCpe_cabecera','cpe_c.ccanot','cpe_c.ccabaj','cpe_c.codhash','cpe_c.estado','cpe_c.enviado','totalcontado','totalcredito')
        ->join('tipo_documento as tip_d','cpe_c.tdocod','=','tip_d.tdocod')
        ->join('tipo_documento_identidad as tdi','cpe_c.tdicod','=','tdi.tdicod')
        ->join('moneda as mon','cpe_c.moncod','=','mon.moncod')
        ->whereNull('cpe_c.ccabaj')
        ->where('cpe_c.id_empresa_negocio',$sucursal->id_empresa_negocio)
        ->where(function ($query) {
          $query->Where('cpe_c.tdocod','03')
                ->orWhere('cpe_c.tdocod','01')
                ->orWhere('cpe_c.tdocod','13');
              
          })
        ->where('estadopago','CONTADO')
        ->where('ccafem',$fecha)
        ->get();


        $ventascontado = $registroventascontado->sum('ccaitv');

        $registronotascontado = DB::table('cpe_cabecera as cpe_c')->select('fecha_hora','ccacodsun','ccafem','tdodes','cpe_c.serdoc','cpe_c.numdoc','tdides','cpe_c.ccandi','cpe_c.ccanom','monnom','cpe_c.ccaitv','cpe_c.IdEmpresa','cpe_c.tdocod','cpe_c.IdCpe_cabecera','cpe_c.ccanot','cpe_c.ccabaj','cpe_c.codhash','cpe_c.estado','cpe_c.enviado','totalcontado','totalcredito')
        ->join('tipo_documento as tip_d','cpe_c.tdocod','=','tip_d.tdocod')
        ->join('tipo_documento_identidad as tdi','cpe_c.tdicod','=','tdi.tdicod')
        ->join('moneda as mon','cpe_c.moncod','=','mon.moncod')
        ->whereNull('cpe_c.ccabaj')
        ->where('cpe_c.id_empresa_negocio',$sucursal->id_empresa_negocio)
        ->where('cpe_c.tdocod','13')
        ->where('estadopago','CONTADO')
        ->where('ccafem',$fecha)
        ->get();

        

        $notascontado = $registronotascontado->sum('ccaitv');

         $registroconsumo = DB::table('cpe_cabecera as cpe_c')->select('fecha_hora','ccacodsun','ccafem','tdodes','cpe_c.serdoc','cpe_c.numdoc','tdides','cpe_c.ccandi','cpe_c.ccanom','monnom','cpe_c.ccaitv','cpe_c.IdEmpresa','cpe_c.tdocod','cpe_c.IdCpe_cabecera','cpe_c.ccanot','cpe_c.ccabaj','cpe_c.codhash','cpe_c.estado','cpe_c.enviado','totalcontado','totalcredito')
        ->join('tipo_documento as tip_d','cpe_c.tdocod','=','tip_d.tdocod')
        ->join('tipo_documento_identidad as tdi','cpe_c.tdicod','=','tdi.tdicod')
        ->join('moneda as mon','cpe_c.moncod','=','mon.moncod')
        ->whereNull('cpe_c.ccabaj')
        ->where('cpe_c.id_empresa_negocio',$sucursal->id_empresa_negocio)
        ->where('cpe_c.tdocod','99')
        ->where('estadopago','CONTADO')
        ->where('ccafem',$fecha)
        ->get();

        

        $consumo = $registroconsumo->sum('ccaitv');


        $registronotascredito = DB::table('cpe_cabecera as cpe_c')->select('fecha_hora','ccacodsun','ccafem','tdodes','cpe_c.serdoc','cpe_c.numdoc','tdides','cpe_c.ccandi','cpe_c.ccanom','monnom','cpe_c.ccaitv','cpe_c.IdEmpresa','cpe_c.tdocod','cpe_c.IdCpe_cabecera','cpe_c.ccanot','cpe_c.ccabaj','cpe_c.codhash','cpe_c.estado','cpe_c.enviado','totalcontado','totalcredito')
        ->join('tipo_documento as tip_d','cpe_c.tdocod','=','tip_d.tdocod')
        ->join('tipo_documento_identidad as tdi','cpe_c.tdicod','=','tdi.tdicod')
        ->join('moneda as mon','cpe_c.moncod','=','mon.moncod')
        ->whereNull('cpe_c.ccabaj')
        ->where('cpe_c.id_empresa_negocio',$sucursal->id_empresa_negocio)
        ->Where('cpe_c.tdocod','13')
        ->where('estadopago','CREDITO')
        ->where('ccafem',$fecha)
        ->get();


        $notascredito = $registronotascredito->sum('ccaitv');



        $registroventascontadof = DB::table('cpe_cabecera as cpe_c')->select('fecha_hora','ccacodsun','ccafem','tdodes','cpe_c.serdoc','cpe_c.numdoc','tdides','cpe_c.ccandi','cpe_c.ccanom','monnom','cpe_c.ccaitv','cpe_c.IdEmpresa','cpe_c.tdocod','cpe_c.IdCpe_cabecera','cpe_c.ccanot','cpe_c.ccabaj','cpe_c.codhash','cpe_c.estado','cpe_c.enviado','totalcontado','totalcredito')
        ->join('tipo_documento as tip_d','cpe_c.tdocod','=','tip_d.tdocod')
        ->join('tipo_documento_identidad as tdi','cpe_c.tdicod','=','tdi.tdicod')
        ->join('moneda as mon','cpe_c.moncod','=','mon.moncod')
        ->whereNull('cpe_c.ccabaj')
        ->where('cpe_c.id_empresa_negocio',$sucursal->id_empresa_negocio)
        ->where(function ($query) {
          $query->Where('cpe_c.tdocod','01');   
          })
        ->where('estadopago','CONTADO')
        ->where('ccafem',$fecha)
        ->get();


        $ventasfactura = $registroventascontadof->sum('ccaitv');

        $registroventascontadob = DB::table('cpe_cabecera as cpe_c')->select('fecha_hora','ccacodsun','ccafem','tdodes','cpe_c.serdoc','cpe_c.numdoc','tdides','cpe_c.ccandi','cpe_c.ccanom','monnom','cpe_c.ccaitv','cpe_c.IdEmpresa','cpe_c.tdocod','cpe_c.IdCpe_cabecera','cpe_c.ccanot','cpe_c.ccabaj','cpe_c.codhash','cpe_c.estado','cpe_c.enviado','totalcontado','totalcredito')
        ->join('tipo_documento as tip_d','cpe_c.tdocod','=','tip_d.tdocod')
        ->join('tipo_documento_identidad as tdi','cpe_c.tdicod','=','tdi.tdicod')
        ->join('moneda as mon','cpe_c.moncod','=','mon.moncod')
        ->whereNull('cpe_c.ccabaj')
        ->where('cpe_c.id_empresa_negocio',$sucursal->id_empresa_negocio)
        ->where(function ($query) {
          $query->Where('cpe_c.tdocod','03');              
          })
        ->where('estadopago','CONTADO')
        ->where('ccafem',$fecha)
        ->get();


        $ventasboleta = $registroventascontadob->sum('ccaitv');

        $registroventascontadofc = DB::table('cpe_cabecera as cpe_c')->select('fecha_hora','ccacodsun','ccafem','tdodes','cpe_c.serdoc','cpe_c.numdoc','tdides','cpe_c.ccandi','cpe_c.ccanom','monnom','cpe_c.ccaitv','cpe_c.IdEmpresa','cpe_c.tdocod','cpe_c.IdCpe_cabecera','cpe_c.ccanot','cpe_c.ccabaj','cpe_c.codhash','cpe_c.estado','cpe_c.enviado','totalcontado','totalcredito')
        ->join('tipo_documento as tip_d','cpe_c.tdocod','=','tip_d.tdocod')
        ->join('tipo_documento_identidad as tdi','cpe_c.tdicod','=','tdi.tdicod')
        ->join('moneda as mon','cpe_c.moncod','=','mon.moncod')
        ->whereNull('cpe_c.ccabaj')
        ->where('cpe_c.id_empresa_negocio',$sucursal->id_empresa_negocio)
        ->where(function ($query) {
          $query->Where('cpe_c.tdocod','01');   
          })
        ->where('estadopago','CREDITO')
        ->where('ccafem',$fecha)
        ->get();


        $ventasfacturac = $registroventascontadofc->sum('ccaitv');

        $registroventascontadobc = DB::table('cpe_cabecera as cpe_c')->select('fecha_hora','ccacodsun','ccafem','tdodes','cpe_c.serdoc','cpe_c.numdoc','tdides','cpe_c.ccandi','cpe_c.ccanom','monnom','cpe_c.ccaitv','cpe_c.IdEmpresa','cpe_c.tdocod','cpe_c.IdCpe_cabecera','cpe_c.ccanot','cpe_c.ccabaj','cpe_c.codhash','cpe_c.estado','cpe_c.enviado','totalcontado','totalcredito')
        ->join('tipo_documento as tip_d','cpe_c.tdocod','=','tip_d.tdocod')
        ->join('tipo_documento_identidad as tdi','cpe_c.tdicod','=','tdi.tdicod')
        ->join('moneda as mon','cpe_c.moncod','=','mon.moncod')
        ->whereNull('cpe_c.ccabaj')
        ->where('cpe_c.id_empresa_negocio',$sucursal->id_empresa_negocio)
        ->where(function ($query) {
          $query->Where('cpe_c.tdocod','03');              
          })
        ->where('estadopago','CREDITO')
        ->where('ccafem',$fecha)
        ->get();


        $ventasboletac = $registroventascontadobc->sum('ccaitv');
        

        $registroventascredito = DB::table('cpe_cabecera as cpe_c')->select('fecha_hora','ccacodsun','ccafem','tdodes','cpe_c.serdoc','cpe_c.numdoc','tdides','cpe_c.ccandi','cpe_c.ccanom','monnom','cpe_c.ccaitv','cpe_c.IdEmpresa','cpe_c.tdocod','cpe_c.IdCpe_cabecera','cpe_c.ccanot','cpe_c.ccabaj','cpe_c.codhash','cpe_c.estado','cpe_c.enviado','totalcontado','totalcredito')
        ->leftjoin('tipo_documento as tip_d','cpe_c.tdocod','=','tip_d.tdocod')
        ->leftjoin('tipo_documento_identidad as tdi','cpe_c.tdicod','=','tdi.tdicod')
        ->leftjoin('moneda as mon','cpe_c.moncod','=','mon.moncod')
        ->whereNull('cpe_c.ccabaj')
        ->where('cpe_c.id_empresa_negocio',$sucursal->id_empresa_negocio)
        ->where(function ($query) {
          $query->Where('cpe_c.tdocod','03')
                ->orWhere('cpe_c.tdocod','01')
                ->orWhere('cpe_c.tdocod','13');
              
          })
        ->where('estadopago','CREDITO')
        ->where('ccafem',$fecha)
        ->get();
        

        $ventascredito = $registroventascredito->sum('ccaitv');
        
        $cobranzas = DB::TABLE('cuentas_cobrar_detalle')->select('fec_dep','cuentas_cobrar_detalle.abono','comentario','numero_recibo',DB::raw("(SELECT CONCAT(name,apeusu) FROM users WHERE IdUsuario= cuentas_cobrar_detalle.vendedor) as vendedor"),DB::raw("(SELECT clinom FROM cliente WHERE clicod = cuentas_cobrar.clicod) as cliente"),DB::raw("(SELECT clinum FROM cliente WHERE clicod = cuentas_cobrar.clicod) as ruc"),'serdoc','numdoc','cuentas_cobrar.clicod','total_detalle','saldo_detalle','fec_reg','tdocod','ccanom','monto_efectivo','monto_deposito')
            ->join('cuentas_cobrar','cuentas_cobrar.cue_cob_id','cuentas_cobrar_detalle.cue_cob_id')
            ->join('cpe_cabecera','cpe_cabecera.IdCpe_cabecera','cuentas_cobrar.IdCpe_cabecera')
           // ->whereNull('cpe_cabecera.ccabaj')
            ->where('cuentas_cobrar.id_empresa_negocio',$sucursal->id_empresa_negocio)
          
            ->where('fec_reg','=',$fecha)
            ->orderby('cliente','asc')
            ->get();

        
          $totalcobranzas = $cobranzas->sum('abono');
           $totalcobranzasdepositos = $cobranzas->sum('monto_deposito');

          $pagos = DB::TABLE('cuentas_pagar_detalle')->select('fec_reg','fec_dep','cuentas_pagar_detalle.abono',DB::raw("(SELECT prov_raz FROM proveedor WHERE prov_id = compras_cabecera.prov_id) as proveedor"),DB::raw("(SELECT prov_ruc FROM proveedor WHERE prov_id = compras_cabecera.prov_id) as ruc"),'com_doc_ser','com_doc_num','compras_cabecera.prov_id','compras_cabecera.tdocod')
            ->join('cuentas_pagar','cuentas_pagar.cue_pag_id','cuentas_pagar_detalle.cue_pag_id')
            ->join('compras_cabecera','compras_cabecera.com_cab_id','cuentas_pagar.com_cab_id')
            ->where('est_compra','Registrado')
            ->where('cuentas_pagar.id_empresa_negocio',$sucursal->id_empresa_negocio)
            ->where('fec_reg','=',$fecha)
            ->orderby('proveedor','asc')
            ->get();

      $totalpagos = $pagos->sum('abono');
   

        $totalgasto = DB::TABLE('gastos_cabecera')
        ->leftjoin('moneda','gastos_cabecera.mon_id','moneda.moncod')
        ->leftjoin('tipo_documento','gastos_cabecera.tdocod','tipo_documento.tdocod')
        ->join('usuario_gastos','usuario_gastos.gast_cab_id','gastos_cabecera.gast_cab_id')
        ->where('tipo_movimiento','GASTO')
        ->where('est_gasto','Registrado')
        ->where('gast_fec',$fecha)
        ->orderby('gastos_cabecera.gast_Cab_id','desc')
        ->sum('total_gast');

         $totalingreso = DB::TABLE('gastos_cabecera')
        ->leftjoin('moneda','gastos_cabecera.mon_id','moneda.moncod')
        ->leftjoin('tipo_documento','gastos_cabecera.tdocod','tipo_documento.tdocod')
        ->join('usuario_gastos','usuario_gastos.gast_cab_id','gastos_cabecera.gast_cab_id')
        ->where('tipo_movimiento','INGRESO')
        ->where('est_gasto','Registrado')
        ->where('gast_fec',$fecha)
        ->orderby('gastos_cabecera.gast_Cab_id','desc')
        ->sum('total_gast');


        $grup_gas = DB::TABLE('gastos_cabecera')->select('tip_gas_nom',DB::RAW('sum(gastos_detalle.total) as total'))
         ->leftjoin('gastos_detalle','gastos_cabecera.gast_cab_id','gastos_detalle.gast_cab_id')
         ->join('usuario_gastos','usuario_gastos.gast_cab_id','gastos_cabecera.gast_cab_id')
        ->leftjoin('tipo_gastos','tipo_gastos.tip_gas_id','gastos_detalle.tip_gas_id')
        ->where('gastos_cabecera.id_empresa_negocio',Auth::user()->id_empresa_negocio)
        ->where('tipo_movimiento','GASTO')
        ->where('est_gasto','Registrado')
        ->where('gast_fec',$fecha)
        ->orderby('gastos_cabecera.gast_Cab_id','desc')
        ->groupby('tipo_gastos.tip_gas_id')
        ->get();

    
        $grup_ing = DB::TABLE('gastos_cabecera')->select('tip_gas_nom',DB::RAW('sum(gastos_detalle.total) as total'))
         ->leftjoin('gastos_detalle','gastos_cabecera.gast_cab_id','gastos_detalle.gast_cab_id')
         ->join('usuario_gastos','usuario_gastos.gast_cab_id','gastos_cabecera.gast_cab_id')
        ->leftjoin('tipo_gastos','tipo_gastos.tip_gas_id','gastos_detalle.tip_gas_id')
        ->where('gastos_cabecera.id_empresa_negocio',Auth::user()->id_empresa_negocio)
        ->where('tipo_movimiento','GASTO')
        ->where('est_gasto','Registrado')
        ->where('gast_fec',$fecha)
        ->orderby('gastos_cabecera.gast_Cab_id','desc')
        ->groupby('tipo_gastos.tip_gas_id')
        ->get();

      
        $compras = DB::TABLE('compras_cabecera')
        ->leftjoin('proveedor','compras_cabecera.prov_id','proveedor.prov_id')
        ->leftjoin('moneda','compras_cabecera.mon_id','moneda.moncod')
        ->leftjoin('tipo_documento','compras_cabecera.tdocod','tipo_documento.tdocod')
        ->where('compras_cabecera.id_empresa_negocio',Auth::user()->id_empresa_negocio)
         ->where('com_fec',$fecha)
        ->where('est_compra','Registrado')
        ->where('compras_cabecera.tdocod','!=','80')
        ->sum('total_com');


        $gastos = DB::TABLE('gastos_cabecera')
        ->join('gastos_detalle','gastos_cabecera.gast_cab_id','gastos_detalle.gast_cab_id')
        ->leftjoin('moneda','gastos_cabecera.mon_id','moneda.moncod')
        ->leftjoin('tipo_gastos','tipo_gastos.tip_gas_id','gastos_detalle.tip_gas_id')
        ->leftjoin('tipo_documento','gastos_cabecera.tdocod','tipo_documento.tdocod')
       
        ->where('gastos_cabecera.id_empresa_negocio',Auth::user()->id_empresa_negocio)
        ->where('tipo_movimiento','GASTO')
        ->where('est_gasto','Registrado')
        ->where('gast_fec',$fecha)
        ->orderby('gastos_cabecera.gast_cab_id','desc')
        ->get();

          $ingresos = DB::TABLE('gastos_cabecera')
        ->leftjoin('gastos_detalle','gastos_cabecera.gast_cab_id','gastos_detalle.gast_cab_id')
        ->leftjoin('moneda','gastos_cabecera.mon_id','moneda.moncod')
        ->leftjoin('tipo_gastos','tipo_gastos.tip_gas_id','gastos_detalle.tip_gas_id')
        ->leftjoin('tipo_documento','gastos_cabecera.tdocod','tipo_documento.tdocod')
        ->where('gastos_cabecera.id_empresa_negocio',Auth::user()->id_empresa_negocio)
        ->where('tipo_movimiento','INGRESO')
        ->where('est_gasto','Registrado')
        ->where('gast_fec',$fecha)
        ->orderby('gastos_cabecera.gast_cab_id','desc')
        ->get();


        $totalgast = $gastos->sum('total');
        $totalingreso = $ingresos->sum('total');

        $saldo = 0;

        $fecha1 = Carbon::parse($fecha)->format('d-m-Y');

        $bus_saldo = DB::tABLE('saldos_arqueo')
        ->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
        ->where('fecha','<',$fecha)
        ->orderby('fecha','desc')
        ->first();

        if(empty($bus_saldo)){
          $saldo = 0;
        }else{
          $saldo = $bus_saldo->saldo;
        }

        $saldo_nuevo = ($saldo+$ventascontado+$totalingreso+$totalcobranzas)-($totalgast+$totalpagos);


        $bus_saldo_act = DB::tABLE('saldos_arqueo')
        ->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
        ->where('fecha',$fecha)
        ->first();

        if(empty($bus_saldo_act)){
          DB::tABLE('saldos_arqueo')
          ->insert(['fecha'=>$fecha,'saldo'=>$saldo_nuevo,'id_empresa_negocio'=>Auth::user()->id_empresa_negocio]);
        }else{
          DB::tABLE('saldos_arqueo')
          ->where('sal_arq_id',$bus_saldo_act->sal_arq_id)
          ->update(['saldo'=>$saldo_nuevo]);
        }
       


        $rutapdf = public_path().'/pdfreportes/';

        $nompdffile = 'Arqueo_Resumen_Diario_'.$fecha.'.pdf';

       if(file_exists($rutapdf.$nompdffile)){

            unlink($rutapdf.$nompdffile);
        }

        $view = \View::make('formatos_reportes.reporte_pdf_arqueo_diario', compact('totalcobranzasdepositos','compras','totalgasto','totalingreso','registroventascontado','registroventascredito','ventascredito','ventascontado','cobranzas','totalcobranzas','usuario','sucursal','empresa','grup_gas','totalgasto','grup_ing','gastos','totalgast','totalingreso','ingresos','fecha','saldo_nuevo','saldo','pagos','totalpagos','registroventascontadof','registroventascontadob','ventasfactura','ventasboleta','registroventascontadofc','registroventascontadobc','ventasfacturac','ventasboletac','registronotascontado','registronotascredito','notascontado','notascredito','registroconsumo','consumo'));

                  
          $pdf = \App::make('dompdf.wrapper');
          $contenido = $view->render();
          $pdf->loadHTML($view)->setPaper('A4', 'lanscape')->save($rutapdf.$nompdffile);


      
        


       if (file_exists($rutapdf.$nompdffile))
      {
        $headers = array(
              'Content-Type: application/pdf',
            );

        return response()->download($rutapdf.$nompdffile);
      }

      



   

    }






    public function generarreporte(Request $request, $tipo){

      $fecin = now()->modify('first day of this month')->format('Y-m-d');
      $fecfin = now()->modify('last day of this month')->format('Y-m-d');

      $productos = DB::tABLE('productos')->get();
      $clientes = DB::tABLE('cliente')->where('rucemp',Auth::user()->IdEmpresa)->get();
      $proveedores = DB::tABLE('proveedor')->get();

      $prod = $request->get('IdProducto');

      $negocios = DB::tABLE('empresa_negocios')->where('IdEmpresa',Auth::user()->IdEmpresa)->get();
             
      $almacenes = DB::tABLE('almacenes')
      ->where('id_empresa_negocio',$negocios->first()->id_empresa_negocio)->get();
            
      $sucursal = $negocios->first()->id_empresa_negocio;  

       $almacen = $almacenes->first()->id_almacen;  



      $vendedores = DB::tABLE('users')
      ->leftjoin('role_user','role_user.user_IdUsuario','users.IdUsuario')
     // ->where('role_id','5')
       ->where('IdEmpresa',Auth::user()->IdEmpresa)
      ->get();

        $productoslista = DB::tABLE('productos')->where('tipo','=','1')->where('promocion','!=','2')
      ->get();

      return view('empresas.reportes.reportes',compact('prod','almacen','sucursal','negocios','almacenes','productos','vendedores','clientes','fecin','fecfin','tipo','proveedores','productoslista'));

            

    }



    public function buscarreportepdf(Request $request){

      $fecin = $request->get('fecin');
      $fecfin = $request->get('fecfin');
      $opcion = $request->get('opcion');
      $vendedor = $request->get('vendedor');
      $cliente = $request->get('cliente');
      $producto = $request->get('producto');
      $pro = $request->get('IdProducto');
      $almacen = $request->get('almacen');
      $sucursal = $request->get('sucursal');
      $proveedor = $request->get('proveedor');
       $dato_vendedor ="";

     
      //$productos = DB::tABLE('productos')->where('tipo','1')->get();
      $negocios = DB::tABLE('empresa_negocios')->get();

      if(!empty($sucursal)){
        $almacenes = DB::tABLE('almacenes')->where('id_empresa_negocio',$sucursal)->get();
      }else{
        $almacenes = DB::tABLE('almacenes')->where('id_empresa_negocio',$negocios->first()->id_empresa_negocio)->get();
      }

      $dat_ven = DB::tABLE('users')->where('IdUsuario',$request->get('vendedor'))->first();

      $data_sucursal = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',$sucursal)->first();

      $dat_cli = DB::tABLE('cliente')->where('clicod',$request->get('cliente'))->first();

      $empresa = DB::tABLE('empresa')->where('IdEmpresa',$data_sucursal->IdEmpresa)->first();

      $hora_rep = now()->format('Y-m-d H:i:s');
      $dato_vendedor ="";
      $dato_cliente ="";

      switch ($opcion){
            case 1:

                if($vendedor!='Todos'){
                $dato_vendedor = DB::tABLE('users')->where('IdUsuario',$vendedor)->first();
                }

                if($cliente!='Todos'){
                  $dato_cliente = DB::tABLE('cliente')->where('clicod',$cliente)->first();
                }

                $comprobantes = cpe_cabecera::select('cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','det.cdedes as producto','det.cdecan as cantidad','cdepuni as precio','monnom as moneda','det.cdevve as total','cpe_cabecera.ccabaj as baja','det.costo','ccasunrescod','tdides as documentoidentidad','ccandi as numerodocumento','procod','cdedes','cdecan','cdepuni','cdevve','umecod','ccaitv','id_almacen_pro')
                ->leftjoin('cpe_detalle as det','cpe_cabecera.IdCpe_cabecera','det.IdCpe_cabecera')
                ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                 ->where(function ($query) use ($vendedor) {
                      if($vendedor !='Todos'){
                         $query->Where('cpe_cabecera.IdUsuario_ven',$vendedor);
                      }
                    })
                ->where(function ($query) {
                  $query->where('cpe_cabecera.tdocod','01')
                    ->orWhere('cpe_cabecera.tdocod','03')
                    ->orWhere('cpe_cabecera.tdocod','13')
                    ->orWhere('cpe_cabecera.tdocod','14')
                    //->orWhere('cpe_cabecera.tdocod','07')
                    ;
                  }) 
                  ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                ->where(function ($query2) use ($cliente) {
                      if($cliente !='Todos'){
                         $query2->Where('cpe_cabecera.clicod',$cliente);
                      }
                  })
                 ->orderBy('IdCpe_cabecera','desc')->get();




                 $cabecera = DB::tABLE('cpe_cabecera')
             //    ->join('cpe_detalle','cpe_detalle.IdCpe_cabecera','cpe_cabecera.IdCpe_cabecera')
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                 ->where(function ($query) use ($vendedor){
                      if($vendedor !='Todos'){
                         $query->Where('cpe_cabecera.IdUsuario_ven',$vendedor);
                      }
                    })
                ->where(function ($query) {
                  $query->where('cpe_cabecera.tdocod','01')
                    ->orWhere('cpe_cabecera.tdocod','03')
                    ->orWhere('cpe_cabecera.tdocod','13')
                    ->orWhere('cpe_cabecera.tdocod','14')
                   // ->orWhere('cpe_cabecera.tdocod','07')
                    ;
                  }) 
                ->whereNull('ccabaj')
                ->where('ccanot','')
                ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                 ->where(function ($query2) use ($cliente) {
                      if($cliente !='Todos'){
                         $query2->Where('cpe_cabecera.clicod',$cliente);
                      }
                    })
                 ->get();

                 $total = $cabecera->where('ccanot','')->sum('ccaitv');

                 $totalnotas = $cabecera->where('tdocod','7')->sum('ccaitv');

                 $totalefectivo = $cabecera->where('ccanot','')->sum('totalcontado');
                 $totalcredito = $cabecera->where('ccanot','')->sum('totalcredito');
             
                 $nompdffile='REPORTE_VENTAS_'.$data_sucursal->IdEmpresa.'_'.now()->format('Y-m-d').'.pdf'; 


                 $rutapdf = public_path().'/reporte_cuentas_cobrar/';

                 if(file_exists($rutapdf.$nompdffile)){
                  unlink($rutapdf.$nompdffile);
                 }

                $view = \View::make('formatos_reportes.reporte_pdf_ventas', compact('totalnotas','total','dato_vendedor','cabecera','comprobantes','fecin','fecfin','empresa','sucursal','almacen','data_sucursal','dato_cliente','totalefectivo','totalcredito'));

                            
                $pdf = \App::make('dompdf.wrapper');
                $contenido = $view->render();
                $pdf->loadHTML($view)->setPaper('A4', 'lanscape')->save($rutapdf.$nompdffile);


                $headers = array(
                        'Content-Type: application/pdf',
                      );

                return response()->download($rutapdf.$nompdffile);
  
          

            break;

            case 2:


                if($vendedor!='Todos'){
                  $dato_vendedor = DB::tABLE('users')->where('IdUsuario',$vendedor)->first();
                }

                if($cliente!='Todos'){
                  $dato_cliente = DB::tABLE('cliente')->where('clicod',$cliente)->first();
                }

                $comprobantes = cpe_cabecera::select('cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','det.cdedes as producto','det.cdecan as cantidad','cdepuni as precio','monnom as moneda','det.cdevve as total','cpe_cabecera.ccabaj as baja','det.costo','ccasunrescod','tdides as documentoidentidad','ccandi as numerodocumento','procod','cdedes','cdecan','cdepuni','cdevve','umecod','ccaitv','id_almacen_pro')
                ->leftjoin('cpe_detalle as det','cpe_cabecera.IdCpe_cabecera','det.IdCpe_cabecera')
                ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                 ->where(function ($query) use ($vendedor) {
                      if($vendedor !='Todos'){
                         $query->Where('cpe_cabecera.IdUsuario_ven',$vendedor);
                      }
                    })
                ->where(function ($query) {
                  $query->where('cpe_cabecera.tdocod','01')
                    ->orWhere('cpe_cabecera.tdocod','03')
                    ->orWhere('cpe_cabecera.tdocod','13')
                    ->orWhere('cpe_cabecera.tdocod','14')
                    ->orWhere('cpe_cabecera.tdocod','07')
                    ;
                  }) 
                    ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                       ->where(function ($query2) use ($cliente) {
                      if($cliente !='Todos'){
                         $query2->Where('cpe_cabecera.clicod',$cliente);
                      }
                    })
                 ->orderBy('IdCpe_cabecera','desc')->get();




                 $cabecera = DB::tABLE('cpe_cabecera')
             //    ->join('cpe_detalle','cpe_detalle.IdCpe_cabecera','cpe_cabecera.IdCpe_cabecera')
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                 ->where(function ($query) use ($vendedor) {
                      if($vendedor !='Todos'){
                         $query->Where('cpe_cabecera.IdUsuario_ven',$vendedor);
                      }
                    })
                ->where(function ($query) {
                  $query->where('cpe_cabecera.tdocod','01')
                    ->orWhere('cpe_cabecera.tdocod','03')
                    ->orWhere('cpe_cabecera.tdocod','13')
                    ->orWhere('cpe_cabecera.tdocod','14')
                    ->orWhere('cpe_cabecera.tdocod','07')
                    ;
                  }) 
                    ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                })
                   ->where(function ($query2) use ($cliente) {
                      if($cliente !='Todos'){
                         $query2->Where('cpe_cabecera.clicod',$cliente);
                      }
                    })
                 ->get();

                 $total = $cabecera->where('ccanot','')->sum('ccaitv');

                 $totalnotas = $cabecera->where('tdocod','7')->sum('ccaitv');

                 /*---------------------------------------REPORTE VENTAS POR PRODUCTO RESUMEN------------------------------------------------------*/


                $productos = cpe_cabecera::select(DB::RAW('sum(cpe_detalle.cdecan*cpe_det_factor) as cantidad'),'cdedes',DB::RAW('sum(cpe_detalle.cdevve) as precio'),'cpe_detalle.procod','umecod')
                ->leftjoin('cpe_detalle','cpe_detalle.IdCpe_cabecera','cpe_cabecera.IdCpe_cabecera')
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                ->whereNull('cpe_cabecera.ccabaj')
                ->where('cdecan','>','0')
                ->where(function ($query) use ($vendedor) {
                      if($vendedor !='Todos'){
                         $query->Where('cpe_cabecera.IdUsuario_ven',$vendedor);
                      }
                })
                ->where(function ($query) {
                  $query->where('cpe_cabecera.tdocod','01')
                    ->orWhere('cpe_cabecera.tdocod','03')
                    ->orWhere('cpe_cabecera.tdocod','13')
                    ->orWhere('cpe_cabecera.tdocod','14')
                    ->orWhere('cpe_cabecera.tdocod','07')
                    ;
                  })   
                ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                })
                ->where(function ($query2) use ($cliente) {
                    if($cliente !='Todos'){
                         $query2->Where('cpe_cabecera.clicod',$cliente);
                    }
                }) 
                ->orderby('cdedes','desc')
                ->groupby('procod','cdedes')
                ->get();


                     $productosnot = cpe_cabecera::select(DB::RAW('sum(cpe_detalle.cdecan*cpe_det_factor) as cantidad'),'cdedes',DB::RAW('sum(cpe_detalle.cdevve) as precio'),'cpe_detalle.procod','umecod')
                ->leftjoin('cpe_detalle','cpe_detalle.IdCpe_cabecera','cpe_cabecera.IdCpe_cabecera')
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                ->whereNull('cpe_cabecera.ccabaj')
                ->where('cdecan','>','0')
                ->where(function ($query) use ($vendedor) {
                      if($vendedor !='Todos'){
                         $query->Where('cpe_cabecera.IdUsuario_ven',$vendedor);
                      }
                })
                ->where(function ($query) {
                  $query->Where('cpe_cabecera.tdocod','07')
                    ;
                  })   
                ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                })
                ->where(function ($query2) use ($cliente) {
                    if($cliente !='Todos'){
                         $query2->Where('cpe_cabecera.clicod',$cliente);
                      }
                }) 
                ->orderby('cdedes','desc')
                ->groupby('procod')
                ->get();

                $productosnotas = $productosnot->sum('cantidad');

                $totalproductos = $productos->sum('cantidad');

                $totalmontoproductos =0;
               
                foreach ($productos as $pro) {
                   $totalmontoproductos = $totalmontoproductos + ($pro->precio);
                }


                 /*---------------------------------------------------------------------------------------------------------------------------------*/

                 $nompdffile='REPORTE_VENTAS_DETALLADAS_'.$data_sucursal->IdEmpresa.'_'.now()->format('Y-m-d').'.pdf'; 


                 $rutapdf = public_path().'/reporte_cuentas_cobrar/';

                 if(file_exists($rutapdf.$nompdffile)){
                  unlink($rutapdf.$nompdffile);
                 }

                $view = \View::make('formatos_reportes.reporte_pdf_ventas_detalladas', compact('totalmontoproductos','totalnotas','total','cabecera','comprobantes','fecin','fecfin','empresa','sucursal','almacen','productos','productosnotas','data_sucursal','dato_vendedor','dato_cliente'));

                            
                $pdf = \App::make('dompdf.wrapper');
                $contenido = $view->render();
                $pdf->loadHTML($view)->setPaper('A4', 'lanscape')->save($rutapdf.$nompdffile);


                $headers = array(
                        'Content-Type: application/pdf',
                      );

                return response()->download($rutapdf.$nompdffile);
  

            break;

                  case 3:

                
                 $comprobantes = cpe_cabecera::select('ccacodsun','cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','tdides as documentoidentidad','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','monnom as moneda','ccaitv as total','cpe_cabecera.ccabaj as baja','ccaigv as igv','ccatvg as gravado','ccandi as numerodocumento','ccasunrescod','cpe_cabecera.tdocod','cpe_cabecera.ccatexo','cpe_cabecera.icbper')
                    ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                    ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                    ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                    ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                    ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
                    ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                
                ->where(function ($query6) use ($almacen) {
                  if($almacen !=0 && !empty($almacen)){
                      $query6->where('cpe_cabecera.id_almacen',$almacen);
                  }                
                }) 
                    ->where('cpe_cabecera.ccafem','>=',$fecin)
                    ->where('cpe_cabecera.ccafem','<=',$fecfin)
                    ->where(function ($query) {
                    $query->where('cpe_cabecera.tdocod','01')
                      ->orWhere('cpe_cabecera.tdocod','03')
                      ->orWhere('cpe_cabecera.tdocod','07');
                    })    
                   
                     ->orderBy('IdCpe_cabecera','desc')->get();

             

              $gravadosfacturas = $comprobantes->where('tdocod','01')->sum('gravado');
              $gravadosboletas = $comprobantes->where('tdocod','03')->sum('gravado');
              $gravadosnotas = $comprobantes->where('tdocod','07')->sum('gravado');

              $exoneradosfacturas = $comprobantes->where('tdocod','01')->sum('ccatexo');
              $exoneradosboletas = $comprobantes->where('tdocod','03')->sum('ccatexo');
              $exoneradosnotas = $comprobantes->where('tdocod','07')->sum('ccatexo');

              $totalgravados = $comprobantes->sum('gravado');
              $totalexonerados = $comprobantes->sum('ccatexo');
              $totaligv = $comprobantes->where('tdocod','!=','07')->sum('ccaigv');
              $totaligvnotas = $comprobantes->where('tdocod','07')->sum('ccaigv');


              $totalboletas = $comprobantes->where('tdocod','03')->sum('total');
              $totalfacturas = $comprobantes->where('tdocod','01')->sum('total');
               $totalnotas = $comprobantes->where('tdocod','07')->sum('total');
              // FIN NOTAS DE CREDITOS

              $hora_rep = now()->format('Y-m-d H:i:s');

              $boletas =  cpe_cabecera::select('ccacodsun','cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','tdides as documentoidentidad','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','monnom as moneda','ccaitv as total','cpe_cabecera.ccabaj as baja','ccaigv as igv','ccatvg as gravado','ccandi as numerodocumento','ccasunrescod','cpe_cabecera.tdocod','cpe_cabecera.ccatexo','cpe_cabecera.icbper')
                    ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                    ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                    ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                    ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                    ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
                    ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                
                ->where(function ($query6) use ($almacen) {
                  if($almacen !=0 && !empty($almacen)){
                      $query6->where('cpe_cabecera.id_almacen',$almacen);
                  }                
                }) 
                    ->where('cpe_cabecera.ccafem','>=',$fecin)
                    ->where('cpe_cabecera.ccafem','<=',$fecfin)
                    ->where(function ($query) {
                    $query->where('cpe_cabecera.tdocod','03');
                    })    
                   
                     ->orderBy('IdCpe_cabecera','desc')->get();

              $facturas =  cpe_cabecera::select('ccacodsun','cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','tdides as documentoidentidad','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','monnom as moneda','ccaitv as total','cpe_cabecera.ccabaj as baja','ccaigv as igv','ccatvg as gravado','ccandi as numerodocumento','ccasunrescod','cpe_cabecera.tdocod','cpe_cabecera.ccatexo','cpe_cabecera.icbper')
                    ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                    ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                    ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                    ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                    ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
                    ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                
                ->where(function ($query6) use ($almacen) {
                  if($almacen !=0 && !empty($almacen)){
                      $query6->where('cpe_cabecera.id_almacen',$almacen);
                  }                
                }) 
                    ->where('cpe_cabecera.ccafem','>=',$fecin)
                    ->where('cpe_cabecera.ccafem','<=',$fecfin)
                    ->where(function ($query) {
                    $query->where('cpe_cabecera.tdocod','01');
                    })    
                   
                     ->orderBy('IdCpe_cabecera','desc')->get();

              $notas =  cpe_cabecera::select('ccacodsun','cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','tdides as documentoidentidad','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','monnom as moneda','ccaitv as total','cpe_cabecera.ccabaj as baja','ccaigv as igv','ccatvg as gravado','ccandi as numerodocumento','ccasunrescod','cpe_cabecera.tdocod','cpe_cabecera.ccatexo','cpe_cabecera.icbper')
                    ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                    ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                    ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                    ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                    ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
                    ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                
                ->where(function ($query6) use ($almacen) {
                  if($almacen !=0 && !empty($almacen)){
                      $query6->where('cpe_cabecera.id_almacen',$almacen);
                  }                
                }) 
                    ->where('cpe_cabecera.ccafem','>=',$fecin)
                    ->where('cpe_cabecera.ccafem','<=',$fecfin)
                    ->where(function ($query) {
                    $query->where('cpe_cabecera.tdocod','07');
                    })    
                   
                     ->orderBy('IdCpe_cabecera','desc')->get();

             
                    $nompdffile='VENTAS_'.$fecin.'_'.$fecfin.'.pdf'; 


                 $rutapdf = public_path().'/reporte_cuentas_cobrar/';

                 if(file_exists($rutapdf.$nompdffile)){
                  unlink($rutapdf.$nompdffile);
                 }

                 $sucursal = EmpresaNegocios::FindOrFail($request->get('sucursal'));

                 $empresa = Empresa::findOrFail($sucursal->IdEmpresa);

                $view = \View::make('formatos_reportes.reporte_pdf_ventas', compact('hora_rep','fecin','fecfin','comprobantes','gravadosfacturas','gravadosboletas','gravadosnotas','exoneradosnotas','exoneradosboletas','exoneradosfacturas','totalgravados','totalexonerados','totaligv','totalboletas','totalfacturas','totalnotas','notas','boletas','facturas','totaligvnotas','empresa','sucursal'));

                            
                $pdf = \App::make('dompdf.wrapper');
                $contenido = $view->render();
                $pdf->loadHTML($view)->setPaper('A4', 'lanscape')->save($rutapdf.$nompdffile);

               
                 $headers = array(
                    'Content-Type: application/pdf',
                  );

     


           
                 return response()->download($rutapdf.$nompdffile);

              


            break;


            case 60:

                   $comprobantes = cpe_cabecera::select('cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','cdepuni as precio','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','det.cdecan as cantidad','det.cdedes as producto','monnom as moneda','det.cdevve as total','productos.costofijo as costo','cpe_cabecera.ccabaj as baja','ccasunrescod','det.flete')
                ->leftjoin('cpe_detalle as det','cpe_cabecera.IdCpe_cabecera','det.IdCpe_cabecera')
                ->leftjoin('productos','productos.IdProducto','det.IdProducto')
                ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
                ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                
                ->where(function ($query6) use ($almacen) {
                  if($almacen !='Todos'){
                      $query6->where('cpe_cabecera.id_almacen',$almacen);
                  }                
                }) 
                ->where(function ($query1){
          
                    $query1->whereNull('cpe_cabecera.ccabaj')
                          ->orwhere('cpe_cabecera.ccabaj','');
                    
                })
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin) 
                  ->where(function ($query) {
                $query->where('cpe_cabecera.tdocod','01')
                    ->orWhere('cpe_cabecera.tdocod','03')
                    ->orWhere('cpe_cabecera.tdocod','13');
                })                
                ->orderBy('IdCpe_cabecera','desc')->get();


                $totalventas = cpe_cabecera::select('cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','cdepuni as precio','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','det.cdecan as cantidad','det.cdedes as producto','monnom as moneda','det.cdevve as total','productos.costofijo as costo','cpe_cabecera.ccabaj as baja','ccasunrescod')
                ->leftjoin('cpe_detalle as det','cpe_cabecera.IdCpe_cabecera','det.IdCpe_cabecera')
                 ->leftjoin('productos','productos.IdProducto','det.IdProducto')
                ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
               ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                
                ->where(function ($query6) use ($almacen) {
                  if($almacen !='Todos'){
                      $query6->where('cpe_cabecera.id_almacen',$almacen);
                  }                
                }) 
                ->where(function ($query1){
          
                    $query1->whereNull('cpe_cabecera.ccabaj')
                          ->orwhere('cpe_cabecera.ccabaj','');
                    
                })
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin) 
                  ->where(function ($query) {
                $query->where('cpe_cabecera.tdocod','01')
                    ->orWhere('cpe_cabecera.tdocod','03')
                    ->orWhere('cpe_cabecera.tdocod','13');
                })                
                ->sum('cdevve');

                 $totalcostodet = cpe_cabecera::select('cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','cdepuni as precio','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','det.cdecan as cantidad','det.cdedes as producto','monnom as moneda','det.cdevve as total','productos.costo as costo','cpe_cabecera.ccabaj as baja','ccasunrescod','det.flete')
                ->leftjoin('cpe_detalle as det','cpe_cabecera.IdCpe_cabecera','det.IdCpe_cabecera')
                 ->leftjoin('productos','productos.IdProducto','det.IdProducto')
                ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
               ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                
                ->where(function ($query6) use ($almacen) {
                  if($almacen !='Todos'){
                      $query6->where('cpe_cabecera.id_almacen',$almacen);
                  }                
                }) 
                ->where(function ($query1){
          
                    $query1->whereNull('cpe_cabecera.ccabaj')
                          ->orwhere('cpe_cabecera.ccabaj','');
                    
                })
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin) 
                  ->where(function ($query) {
                $query->where('cpe_cabecera.tdocod','01')
                    ->orWhere('cpe_cabecera.tdocod','03')
                    ->orWhere('cpe_cabecera.tdocod','13');
                })                
                ->get();

                $totalcosto =0;
                
                foreach($totalcostodet as $tcosto){
                  $totalcosto = $totalcosto + $tcosto->costo;
                }

                 $nompdffile='RENTABILIDAD_'.$fecin.'_'.$fecfin.'.pdf'; 


                 $rutapdf = public_path().'/reporte_cuentas_cobrar/';

                 if(file_exists($rutapdf.$nompdffile)){
                  unlink($rutapdf.$nompdffile);
                 }

                

                 $empresa = Empresa::findOrFail($data_sucursal->IdEmpresa);

                $view = \View::make('formatos_reportes.reporte_pdf_rentabilidad', compact('hora_rep','totalventas','totalcosto','comprobantes','fecin','fecfin','empresa','sucursal','data_sucursal'));

                            
                $pdf = \App::make('dompdf.wrapper');
                $contenido = $view->render();
                $pdf->loadHTML($view)->setPaper('A4', 'lanscape')->save($rutapdf.$nompdffile);

               
                 $headers = array(
                    'Content-Type: application/pdf',
                  );

           
                 return response()->download($rutapdf.$nompdffile);


            break;

            case 61:

                if($vendedor!='Todos'){
                $dato_vendedor = DB::tABLE('users')->where('IdUsuario',$vendedor)->first();
                }

                if($cliente!='Todos'){
                  $dato_cliente = DB::tABLE('cliente')->where('clicod',$cliente)->first();
                }

                $comprobantes = cpe_cabecera::select('cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','monnom as moneda','cpe_cabecera.ccabaj as baja','ccasunrescod','tdides as documentoidentidad','ccandi as numerodocumento','users.name','users.apeusu',DB::RAW('sum(ccaitv) as total'))
                ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
                ->leftjoin('users','users.IdUsuario','cpe_cabecera.IdUsuario_ven')
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                 ->where(function ($query) use ($vendedor) {
                      if($vendedor !='Todos'){
                         $query->Where('cpe_cabecera.IdUsuario_ven',$vendedor);
                      }
                    })
                ->where(function ($query) {
                  $query->where('cpe_cabecera.tdocod','01')
                    ->orWhere('cpe_cabecera.tdocod','03')
                    ->orWhere('cpe_cabecera.tdocod','13')
                    ->orWhere('cpe_cabecera.tdocod','14')
                    //->orWhere('cpe_cabecera.tdocod','07')
                    ;
                  }) 
                  ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                   ->where(function ($query2) use ($cliente) {
                      if($cliente !='Todos'){
                         $query2->Where('cpe_cabecera.clicod',$cliente);
                      }
                    })
                
               /* ->where(function ($query6) use ($idalmacen) {
                  if($idalmacen !='Todos' && !empty($idalmacen)){
                      $query6->where('det.id_almacen_pro',$idalmacen);
                  }                
                })   */ 
                 ->groupby('IdUsuario_ven')
                 ->orderby('name')->get();



                 $total = $comprobantes->sum('total');

                  $nompdffile='resumen_ventas_vendedor'.$fecin.'_'.$fecfin.'.pdf'; 


                 $rutapdf = public_path().'/reporte_cuentas_cobrar/';

                 if(file_exists($rutapdf.$nompdffile)){
                  unlink($rutapdf.$nompdffile);
                 }

                

                 $empresa = Empresa::findOrFail($data_sucursal->IdEmpresa);

                $view = \View::make('formatos_reportes.reporte_pdf_resumen_ventas_vendedor', compact('hora_rep','total','comprobantes','fecin','fecfin','empresa','sucursal','data_sucursal','dato_vendedor'));

                            
                $pdf = \App::make('dompdf.wrapper');
                $contenido = $view->render();
                $pdf->loadHTML($view)->setPaper('A4', 'lanscape')->save($rutapdf.$nompdffile);

               
                 $headers = array(
                    'Content-Type: application/pdf',
                  );

           
                 return response()->download($rutapdf.$nompdffile);




            break;



             case 4:

                   $comprobantes = cpe_cabecera::select('cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','cdepuni as precio','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','det.cdecan as cantidad','det.cdedes as producto','monnom as moneda','det.cdevve as total','det.costo as costo','cpe_cabecera.ccabaj as baja','ccasunrescod','det.flete')
                ->leftjoin('cpe_detalle as det','cpe_cabecera.IdCpe_cabecera','det.IdCpe_cabecera')
                ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
                ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                
                ->where(function ($query6) use ($almacen) {
                  if($almacen !='Todos'){
                      $query6->where('cpe_cabecera.id_almacen',$almacen);
                  }                
                }) 
                ->where(function ($query1){
          
                    $query1->whereNull('cpe_cabecera.ccabaj')
                          ->orwhere('cpe_cabecera.ccabaj','');
                    
                })
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin) 
                  ->where(function ($query) {
                $query->where('cpe_cabecera.tdocod','01')
                    ->orWhere('cpe_cabecera.tdocod','03')
                    ->orWhere('cpe_cabecera.tdocod','13');
                })                
                ->orderBy('IdCpe_cabecera','desc')->get();


                $totalventas = cpe_cabecera::select('cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','cdepuni as precio','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','det.cdecan as cantidad','det.cdedes as producto','monnom as moneda','det.cdevve as total','det.costo as costo','cpe_cabecera.ccabaj as baja','ccasunrescod')
                ->leftjoin('cpe_detalle as det','cpe_cabecera.IdCpe_cabecera','det.IdCpe_cabecera')
                ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
               ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                
                ->where(function ($query6) use ($almacen) {
                  if($almacen !='Todos'){
                      $query6->where('cpe_cabecera.id_almacen',$almacen);
                  }                
                }) 
                ->where(function ($query1){
          
                    $query1->whereNull('cpe_cabecera.ccabaj')
                          ->orwhere('cpe_cabecera.ccabaj','');
                    
                })
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin) 
                  ->where(function ($query) {
                $query->where('cpe_cabecera.tdocod','01')
                    ->orWhere('cpe_cabecera.tdocod','03')
                    ->orWhere('cpe_cabecera.tdocod','13');
                })                
                ->sum('cdevve');

                 $totalcostodet = cpe_cabecera::select('cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','cdepuni as precio','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','det.cdecan as cantidad','det.cdedes as producto','monnom as moneda','det.cdevve as total','det.costo as costo','cpe_cabecera.ccabaj as baja','ccasunrescod','det.flete')
                ->leftjoin('cpe_detalle as det','cpe_cabecera.IdCpe_cabecera','det.IdCpe_cabecera')
                ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
               ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                
                ->where(function ($query6) use ($almacen) {
                  if($almacen !='Todos'){
                      $query6->where('cpe_cabecera.id_almacen',$almacen);
                  }                
                }) 
                ->where(function ($query1){
          
                    $query1->whereNull('cpe_cabecera.ccabaj')
                          ->orwhere('cpe_cabecera.ccabaj','');
                    
                })
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin) 
                  ->where(function ($query) {
                $query->where('cpe_cabecera.tdocod','01')
                    ->orWhere('cpe_cabecera.tdocod','03')
                    ->orWhere('cpe_cabecera.tdocod','13');
                })                
                ->get();

                $totalcosto =0;
                
                foreach($totalcostodet as $tcosto){
                  $totalcosto = $totalcosto + $tcosto->costo;
                }

                 $nompdffile='RENTABILIDAD_'.$fecin.'_'.$fecfin.'.pdf'; 


                 $rutapdf = public_path().'/reporte_cuentas_cobrar/';

                 if(file_exists($rutapdf.$nompdffile)){
                  unlink($rutapdf.$nompdffile);
                 }

                

                 $empresa = Empresa::findOrFail($data_sucursal->IdEmpresa);

                $view = \View::make('formatos_reportes.reporte_pdf_rentabilidad', compact('hora_rep','totalventas','totalcosto','comprobantes','fecin','fecfin','empresa','sucursal','data_sucursal'));

                            
                $pdf = \App::make('dompdf.wrapper');
                $contenido = $view->render();
                $pdf->loadHTML($view)->setPaper('A4', 'lanscape')->save($rutapdf.$nompdffile);

               
                 $headers = array(
                    'Content-Type: application/pdf',
                  );

           
                 return response()->download($rutapdf.$nompdffile);


            break;

            case 5:



              /*  $registros = DB::tABLE('cpe_detalle')->get();
              foreach ($registros as $reg) {
                DB::tABLE('cpe_detalle')->where('IdCpe_detalle',$reg->IdCpe_detalle)->update(['procod'=>trim($reg->procod)]);
              }*/


              $empresa = DB::tABLE('empresa')->where('IdEmpresa',Auth::user()->IdEmpresa)->first();

             // $sucursal = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first();
             // $datocli = DB::tABLE('cliente')->where('clicod',$clicod)->first();

                $productos = cpe_cabecera::select(DB::RAW('sum(cpe_detalle.cdecan*cpe_det_factor) as cantidad'),'cdedes',DB::RAW('sum(cpe_detalle.cdevve) as precio'),'cpe_detalle.procod','umecod')
                ->leftjoin('cpe_detalle','cpe_detalle.IdCpe_cabecera','cpe_cabecera.IdCpe_cabecera')
 
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                ->whereNull('cpe_cabecera.ccabaj')
                ->where('cdecan','>','0')
                 ->where(function ($query) {
                  $query->Where('cpe_cabecera.tdocod','03')
                        ->orWhere('cpe_cabecera.tdocod','01')
                        ->orWhere('cpe_cabecera.tdocod','13');
                      
                  })
               
                ->where(function ($query5) use ($sucursal) {
                  if($sucursal !='Todos'){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                
                ->where(function ($query6) use ($almacen) {
                  if($almacen !='Todos'){
                      $query6->where('cpe_detalle.id_almacen_pro',$almacen);
                  }                
                }) 
                ->orderby('cdedes','desc')
                ->groupby('procod')
                ->get();

                $totalproductos = $productos->sum('cantidad');

                $total =0;
                foreach ($productos as $pro) {
                   $total = $total + ($pro->precio);
                }

                $nompdffile='resumen_ventas_productos_'.$data_sucursal->IdEmpresa.'_'.now()->format('Y-m-d').'.pdf'; 


                 $rutapdf = public_path().'/reporte_cuentas_cobrar/';

                 if(file_exists($rutapdf.$nompdffile)){
                  unlink($rutapdf.$nompdffile);
                 }

                $view = \View::make('formatos_reportes.reporte_pdf_resumen_ventas_productos', compact('productos','fecin','fecfin','total','totalproductos','total','empresa','sucursal','data_sucursal'));

                            
                $pdf = \App::make('dompdf.wrapper');
                $contenido = $view->render();
                $pdf->loadHTML($view)->setPaper('A4', 'lanscape')->save($rutapdf.$nompdffile);


                $headers = array(
                        'Content-Type: application/pdf',
                      );

                return response()->download($rutapdf.$nompdffile);


                 
              break;

               case 6:


                $comprobantes = cpe_cabecera::select('cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','det.cdedes as producto','det.cdecan as cantidad','cdepuni as precio','monnom as moneda','det.cdevve as total','cpe_cabecera.ccabaj as baja','det.costo','ccasunrescod','tdides as documentoidentidad','ccandi as numerodocumento','IdProducto','cpe_det_factor','des_doc')
                ->leftjoin('cpe_detalle as det','cpe_cabecera.IdCpe_cabecera','det.IdCpe_cabecera')
                ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
                 ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                
                ->where(function ($query6) use ($almacen) {
                  if($almacen !='Todos'){
                      $query6->where('det.id_almacen_pro',$almacen);
                  }                
                }) 
                
                
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
               ->whereNull('cpe_cabecera.ccabaj')
                ->where('cdecan','>','0')
                 ->where(function ($query) {
                  $query->Where('cpe_cabecera.tdocod','03')
                        ->orWhere('cpe_cabecera.tdocod','01')
                        ->orWhere('cpe_cabecera.tdocod','13');
                      
                  })
               
                ->where(function ($query4) use ($pro) {

                if($pro !=0){
                   $query4->where('det.IdProducto',$pro)
                    ->orWhere('det.IdProducto_rel',$pro);
                }
               

                })    
                 ->orderBy('IdCpe_cabecera','desc')->get();

                $total = $comprobantes->sum('total');
   
                $nompdffile='ventas_productos_'.$data_sucursal->IdEmpresa.'_'.now()->format('Y-m-d').'.pdf'; 

                 $rutapdf = public_path().'/reporte_cuentas_cobrar/';

                 if(file_exists($rutapdf.$nompdffile)){
                  unlink($rutapdf.$nompdffile);
                 }

                $empresa = DB::tABLE('empresa')->where('IdEmpresa',$data_sucursal->IdEmpresa)->first();

                $view = \View::make('formatos_reportes.reporte_pdf_ventas_productos', compact('empresa','fecin','fecfin','comprobantes','total','dat_ven','data_sucursal'));

                            
                $pdf = \App::make('dompdf.wrapper');
                $contenido = $view->render();
                $pdf->loadHTML($view)->setPaper('A4', 'lanscape')->save($rutapdf.$nompdffile);


                $headers = array(
                        'Content-Type: application/pdf',
                      );

                return response()->download($rutapdf.$nompdffile);


              


            break;

             case 7:

                $comprobantes = cpe_cabecera::select('ccaitv','cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','monnom as moneda','ccaitv as total','cpe_cabecera.ccabaj as baja','ccasunrescod','tdides as documentoidentidad','ccandi as numerodocumento','cpe_cabecera.tdocod')
                ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
                 ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                
                ->where(function ($query6) use ($almacen) {
                  if($almacen !=0 && !empty($almacen)){
                      $query6->where('cpe_cabecera.id_almacen',$almacen);
                  }                
                }) 
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                ->where(function ($query){
                    $query->Where('cpe_cabecera.tdocod','15');
                })
                ->where(function ($query1) use ($vendedor) {
                      if($vendedor !='Todos'){
                         $query1->Where('cpe_cabecera.IdUsuario_ven',$vendedor);
                      }
                    })
                 ->where(function ($query2) use ($cliente) {
                      if($cliente !='Todos'){
                         $query2->Where('cpe_cabecera.clicod',$cliente);
                      }
                    })
                ->orderBy('IdCpe_cabecera','desc')->get();

            //   dd($comprobantes);

                  $total = $comprobantes->sum('ccaitv');

                $vista = view('empresas.reportes.proformas',compact('dat_cli','fecin','fecfin','comprobantes','total','dat_ven'))->render();

              if($request->ajax()){
                 return response()->json(['vista'=>$vista]);

              }


            break;

             case 8:

                $comprobantes = cpe_cabecera::select('ccaitv','cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','monnom as moneda','ccaitv as total','cpe_cabecera.ccabaj as baja','ccasunrescod','tdides as documentoidentidad','ccandi as numerodocumento','cpe_cabecera.tdocod')
                ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
                 ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                
                ->where(function ($query6) use ($almacen) {
                  if($almacen !=0 && !empty($almacen)){
                      $query6->where('cpe_cabecera.id_almacen',$almacen);
                  }                
                }) 
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                ->where(function ($query){
                    $query->Where('cpe_cabecera.tdocod','16');
                })
                ->where(function ($query1) use ($vendedor) {
                      if($vendedor !='Todos'){
                         $query1->Where('cpe_cabecera.IdUsuario_ven',$vendedor);
                      }
                    })
                 ->where(function ($query2) use ($cliente) {
                      if($cliente !='Todos'){
                         $query2->Where('cpe_cabecera.clicod',$cliente);
                      }
                    })
                ->orderBy('IdCpe_cabecera','desc')->get();

            //   dd($comprobantes);

                  $total = $comprobantes->sum('ccaitv');

                $vista = view('empresas.reportes.pedidos',compact('dat_cli','fecin','fecfin','comprobantes','total','dat_ven'))->render();

              if($request->ajax()){
                 return response()->json(['vista'=>$vista]);

              }


            break;

                case 9:

                
              
            
                 $comprobantes = cpe_cabecera::select(DB::RAW('sum(det.cdevve) as TOTAL'),DB::RAW('sum(det.cdecan*factor) as CANTIDAD'),'productos.pronom',DB::RAW('sum(det.cdevve*(det.comision/100)) as COMISION'))
                ->leftjoin('cpe_detalle as det','cpe_cabecera.IdCpe_cabecera','det.IdCpe_cabecera')
                ->leftjoin('productos','productos.IdProducto','det.IdProducto_rel')
                ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
               ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                
                ->where(function ($query6) use ($almacen) {
                  if($almacen !=0 && !empty($almacen)){
                      $query6->where('cpe_cabecera.id_almacen',$almacen);
                  }                
                }) 
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                ->where(function ($query) use ($vendedor) {
                      if($vendedor !='Todos'){
                         $query->Where('cpe_cabecera.IdUsuario_ven',$vendedor);
                      }
                    })
                   ->where(function ($query2) use ($pro) {
                      if($pro !=0){
                         $query2->Where('IdProducto_rel',$pro);
                      }
                    })
                ->where(function ($query1){
          
                    $query1->whereNull('cpe_cabecera.ccabaj')
                          ->orwhere('cpe_cabecera.ccabaj','');
                    
                })
                ->where(function ($query3) {
                $query3->where('cpe_cabecera.tdocod','01')
                    ->orWhere('cpe_cabecera.tdocod','03')
                    ->orWhere('cpe_cabecera.tdocod','13');
                })   
                ->groupby('IdProducto_rel') 
                 ->orderBy('cpe_cabecera.IdCpe_cabecera','desc')->get();

                 //dd($comprobantes);

                  $total = $comprobantes->sum('COMISION');

                    $vista = view('empresas.reportes.comision_vendedor',compact('fecin','fecfin','comprobantes','total','dat_ven','dat_cli'))->render();


                    if($request->ajax()){
                 return response()->json(['vista'=>$vista]);

              }


            break;

                 case 10:

                $comprobantes = cpe_cabecera::select(DB::RAW('sum(det.cdevve) as TOTAL'),DB::RAW('sum(det.cdecan*factor) as CANTIDAD'),'productos.pronom',DB::RAW('sum(det.cdevve*(det.comision/100)) as COMISION'))
                ->leftjoin('cpe_detalle as det','cpe_cabecera.IdCpe_cabecera','det.IdCpe_cabecera')
                ->leftjoin('productos','productos.IdProducto','det.IdProducto_rel')
                ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
               ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                
                ->where(function ($query6) use ($almacen) {
                  if($almacen !=0 && !empty($almacen)){
                      $query6->where('cpe_cabecera.id_almacen',$almacen);
                  }                
                }) 
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                ->where(function ($query) use ($vendedor) {
                      if($vendedor !='Todos'){
                         $query->Where('cpe_cabecera.IdUsuario_ven',$vendedor);
                      }
                    })
                 ->where(function ($query2) use ($cliente) {
                      if($cliente !='Todos'){
                         $query2->Where('cpe_cabecera.clicod',$cliente);
                      }
                    })
                ->where(function ($query1){
          
                    $query1->whereNull('cpe_cabecera.ccabaj')
                          ->orwhere('cpe_cabecera.ccabaj','');
                    
                })
                ->where(function ($query3) {
                $query3->where('cpe_cabecera.tdocod','01')
                    ->orWhere('cpe_cabecera.tdocod','03')
                    ->orWhere('cpe_cabecera.tdocod','13');
                })   
                ->groupby('IdProducto_rel') 
                 ->orderBy('cpe_cabecera.IdCpe_cabecera','desc')->get();

                 //dd($comprobantes);

                  $total = $comprobantes->sum('COMISION');


                $vista = view('empresas.reportes.comision_vendedor',compact('fecin','fecfin','comprobantes','total','dat_ven','dat_cli'))->render();

                    if($request->ajax()){
                 return response()->json(['vista'=>$vista]);

              }


            break;


                case 11:

                 $comprobantes = DB::tABLE('compras_cabecera')
                  ->leftjoin('proveedor','compras_cabecera.prov_id','proveedor.prov_id')
                  ->join('moneda','compras_cabecera.mon_id','moneda.moncod')
                  ->join('tipo_documento','compras_cabecera.tdocod','tipo_documento.tdocod')
                  
                    ->where('compras_cabecera.com_fec','>=',$fecin)
                    ->where('compras_cabecera.com_fec','<=',$fecfin)
                  ->where('est_compra','Registrado')
                    ->where(function ($query) use ($sucursal){
                      if(!$sucursal !=0 && !empty($sucursal)){
                          $query->where('compras_cabecera.id_empresa_negocio',$sucursal);
                      }
                     
                  })
                  ->where(function ($query1) use ($proveedor){
                      if($proveedor !=0){
                          $query1->where('compras_cabecera.prov_id',$proveedor);
                      }
                      
                  })
                        
                  ->where(function ($query6) use ($almacen) {
                      if($almacen !=0 && !empty($almacen)){
                          $query6->where('compras_cabecera.id_almacen',$almacen);
                      }                
                    }) 
                
                  ->where('compras_cabecera.tdocod','!=','80')
                  ->orderby('com_cab_id','desc')
                  ->get();
              //   dd($comprobantes);

                  $total = $comprobantes->sum('total_com');


                $vista = view('empresas.reportes.compras',compact('fecin','fecfin','comprobantes','total','dat_ven','dat_cli'))->render();

                    if($request->ajax()){
                 return response()->json(['vista'=>$vista]);

              }


            break;

              case 12:

              $comprobantes = DB::tABLE('compras_cabecera')
              ->leftjoin('compras_detalle','compras_detalle.com_cab_id','compras_cabecera.com_cab_id')
                 ->leftjoin('productos','productos.IdProducto','compras_detalle.pro_id')
                  ->leftjoin('proveedor','compras_cabecera.prov_id','proveedor.prov_id')
                  ->leftjoin('unidad_medida','unidad_medida.umecod','compras_detalle.ume_cod')
                  ->join('moneda','compras_cabecera.mon_id','moneda.moncod')
                  ->join('tipo_documento','compras_cabecera.tdocod','tipo_documento.tdocod')
              ->where('est_compra','Registrado')
                ->where('compras_cabecera.com_fec','>=',$fecin)
                ->where('compras_cabecera.com_fec','<=',$fecfin)
                  ->where(function ($query) use ($sucursal){
                      if(!$sucursal !=0 && !empty($sucursal)){
                          $query->where('compras_cabecera.id_empresa_negocio',$sucursal);
                      }
                     
                  })
                  ->where(function ($query1) use ($proveedor){
                      if($proveedor !=0){
                          $query1->where('compras_cabecera.prov_id',$proveedor);
                      }
                      
                  })
                        
                  ->where(function ($query6) use ($almacen) {
                      if($almacen !=0 && !empty($almacen)){
                          $query6->where('compras_cabecera.id_almacen',$almacen);
                      }                
                    }) 
              ->where('compras_cabecera.tdocod','!=','80')
              ->orderby('compras_cabecera.com_cab_id','desc')
              ->get();
                 //dd($comprobantes);

                  $total = $comprobantes->sum('total_com');


                $vista = view('empresas.reportes.compras_detalladas',compact('fecin','fecfin','comprobantes','total','dat_ven','dat_cli'))->render();

                    if($request->ajax()){
                 return response()->json(['vista'=>$vista]);

              }


            break;

             

               case 14:

                $comprobantes = cpe_cabecera::select('ccaitv','cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','monnom as moneda','ccaitv as total','cpe_cabecera.ccabaj as baja','ccasunrescod','tdides as documentoidentidad','ccandi as numerodocumento','cpe_cabecera.tdocod','pedidos.fecha_hora','pedidos.fecha_salida','pedidos.placa',DB::raw("(SELECT CONCAT(name,apeusu) FROM users WHERE IdUsuario= pedidos.IdUsuario) as usuario_registra"),DB::raw("(SELECT CONCAT(name,apeusu) FROM users WHERE IdUsuario= pedidos.IdUsuarioCob) as usuario_cobra"))
                ->join('pedidos','pedidos.ped_id','cpe_cabecera.ped_id')
                ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
                 ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                
                ->where(function ($query6) use ($almacen) {
                  if($almacen !=0 && !empty($almacen)){
                      $query6->where('cpe_cabecera.id_almacen',$almacen);
                  }                
                }) 
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                ->where(function ($query){
                    $query->where('cpe_cabecera.tdocod','01')
                          ->orwhere('cpe_cabecera.tdocod','03')
                          ->orWhere('cpe_cabecera.tdocod','13');
                })
                ->where(function ($query1) use ($vendedor) {
                      if($vendedor !='Todos'){
                         $query1->Where('cpe_cabecera.IdUsuario_ven',$vendedor);
                      }
                    })
                 ->where(function ($query2) use ($cliente) {
                      if($cliente !='Todos'){
                         $query2->Where('cpe_cabecera.clicod',$cliente);
                      }
                    })
                ->orderBy('IdCpe_cabecera','desc')->get();

            //   dd($comprobantes);

                  $total = $comprobantes->sum('ccaitv');

                  

                $vista = view('empresas.reportes.ventas_ingreso_vehiculos',compact('dat_cli','fecin','fecfin','comprobantes','total','dat_ven'))->render();

              if($request->ajax()){
                 return response()->json(['vista'=>$vista]);

              }


            break;

              case 15:

                $comprobantes = cpe_cabecera::select('ccaitv','cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','monnom as moneda','ccaitv as total','cpe_cabecera.ccabaj as baja','ccasunrescod','tdides as documentoidentidad','ccandi as numerodocumento','cpe_cabecera.tdocod','pedidos.fecha_hora','pedidos.fecha_salida','pedidos.placa',DB::raw("(SELECT CONCAT(name,apeusu) FROM users WHERE IdUsuario= pedidos.IdUsuario) as usuario_registra"),DB::raw("(SELECT CONCAT(name,apeusu) FROM users WHERE IdUsuario= pedidos.IdUsuarioCob) as usuario_cobra"))
                ->join('pedidos','pedidos.ped_id','cpe_cabecera.ped_id')
                ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
                 ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                
                ->where(function ($query6) use ($almacen) {
                  if($almacen !=0 && !empty($almacen)){
                      $query6->where('cpe_cabecera.id_almacen',$almacen);
                  }                
                }) 
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                ->where(function ($query){
                    $query->Where('cpe_cabecera.tdocod','50');
                })
                ->where(function ($query1) use ($vendedor) {
                      if($vendedor !='Todos'){
                         $query1->Where('cpe_cabecera.IdUsuario_ven',$vendedor);
                      }
                    })
                 ->where(function ($query2) use ($cliente) {
                      if($cliente !='Todos'){
                         $query2->Where('cpe_cabecera.clicod',$cliente);
                      }
                    })
                ->orderBy('IdCpe_cabecera','desc')->get();

            //   dd($comprobantes);

                  $total = $comprobantes->sum('ccaitv');

                $vista = view('empresas.reportes.ventas_ingreso_vehiculos',compact('dat_cli','fecin','fecfin','comprobantes','total','dat_ven'))->render();

              if($request->ajax()){
                 return response()->json(['vista'=>$vista]);

              }


            break;

               case 16:

                $comprobantes = cpe_cabecera::select('ccaitv','cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','monnom as moneda','ccaitv as total','cpe_cabecera.ccabaj as baja','ccasunrescod','tdides as documentoidentidad','ccandi as numerodocumento','cpe_cabecera.tdocod','pedidos.fecha_hora','pedidos.fecha_salida','pedidos.placa',DB::raw("(SELECT CONCAT(name,apeusu) FROM users WHERE IdUsuario= pedidos.IdUsuario) as usuario_registra"),DB::raw("(SELECT CONCAT(name,apeusu) FROM users WHERE IdUsuario= pedidos.IdUsuarioCob) as usuario_cobra"))
                ->join('pedidos','pedidos.ped_id','cpe_cabecera.ped_id')
                ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
                 ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                
                ->where(function ($query6) use ($almacen) {
                  if($almacen !=0 && !empty($almacen)){
                      $query6->where('cpe_cabecera.id_almacen',$almacen);
                  }                
                }) 
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                ->where(function ($query){
                    $query->where('cpe_cabecera.tdocod','01')
                          ->orwhere('cpe_cabecera.tdocod','03')
                          ->orWhere('cpe_cabecera.tdocod','13')
                           ->orWhere('cpe_cabecera.tdocod','50');
                })
                ->where(function ($query1) use ($vendedor) {
                      if($vendedor !='Todos'){
                         $query1->Where('cpe_cabecera.IdUsuario_ven',$vendedor);
                      }
                    })
                 ->where(function ($query2) use ($cliente) {
                      if($cliente !='Todos'){
                         $query2->Where('cpe_cabecera.clicod',$cliente);
                      }
                    })
                ->orderBy('IdCpe_cabecera','desc')->get();

            //   dd($comprobantes);

                  $total = $comprobantes->sum('ccaitv');

                $vista = view('empresas.reportes.ventas_ingreso_vehiculos',compact('dat_cli','fecin','fecfin','comprobantes','total','dat_ven'))->render();

              if($request->ajax()){
                 return response()->json(['vista'=>$vista]);

              }


            break;

              case 17:

                $comprobantes = cpe_cabecera::select('ccaitv','cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','monnom as moneda','ccaitv as total','cpe_cabecera.ccabaj as baja','ccasunrescod','tdides as documentoidentidad','ccandi as numerodocumento','cpe_cabecera.tdocod','ccatexo','ccatvg','ccatvi')
                ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
                 ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                
                ->where(function ($query6) use ($almacen) {
                  if($almacen !=0 && !empty($almacen)){
                      $query6->where('cpe_cabecera.id_almacen',$almacen);
                  }                
                }) 
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                ->where(function ($query){
                    $query->where('cpe_cabecera.tdocod','01')
                          ->orwhere('cpe_cabecera.tdocod','03')
                          ->orWhere('cpe_cabecera.tdocod','13')
                          ->orWhere('cpe_cabecera.tdocod','07');
                })
                ->whereNull('ccabaj')
                ->orderBy('IdCpe_cabecera','desc')->get();

            //   dd($comprobantes);

                  $total = $comprobantes->sum('ccaitv');

                  $totalexoneradasfacturas = $comprobantes->where('tdocod','01')->sum('ccatexo');
                  $totalgravadasfacturas = $comprobantes->where('tdocod','01')->sum('ccatvg');

                  $totalexoneradasboletas = $comprobantes->where('tdocod','03')->sum('ccatexo');
                  $totalgravadasboletas = $comprobantes->where('tdocod','03')->sum('ccatvg');

                   $totalexoneradasnotas = $comprobantes->where('tdocod','07')->sum('ccatexo');
                  $totalgravadasnotas = $comprobantes->where('tdocod','07')->sum('ccatvg');


                $nompdffile='RESUMEN_VENTAS_'.$fecin.'_'.$fecfin.'.pdf'; 


                 $rutapdf = public_path().'/reporte_cuentas_cobrar/';

                 if(file_exists($rutapdf.$nompdffile)){
                  unlink($rutapdf.$nompdffile);
                 }

                 $sucursal = EmpresaNegocios::FindOrFail($request->get('sucursal'));

                 $empresa = Empresa::findOrFail($sucursal->IdEmpresa);

                $view = \View::make('formatos_reportes.reporte_pdf_resumen_ventas', compact('fecin','fecfin','total','totalexoneradasfacturas','totalexoneradasboletas','totalgravadasfacturas','totalgravadasboletas','totalgravadasnotas','totalexoneradasnotas','empresa','sucursal'));

                            
                $pdf = \App::make('dompdf.wrapper');
                $contenido = $view->render();
                $pdf->loadHTML($view)->setPaper('A4', 'lanscape')->save($rutapdf.$nompdffile);

               
                 $headers = array(
                    'Content-Type: application/pdf',
                  );

     


           
                 return response()->download($rutapdf.$nompdffile);

              


            break;

             case 19:

                if($vendedor!='Todos'){
                $dato_vendedor = DB::tABLE('users')->where('IdUsuario',$vendedor)->first();
                }

                if($cliente!='Todos'){
                  $dato_cliente = DB::tABLE('cliente')->where('clicod',$cliente)->first();
                }

               

                $facturas = cpe_cabecera::select('ccaitv','cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','monnom as moneda','ccaitv as total','cpe_cabecera.ccabaj as baja','ccasunrescod','tdides as documentoidentidad','ccandi as numerodocumento',
                  'cpe_cabecera.tdocod','ccafve as fechaven','cpe_cabecera.tdicod','ccatexo','ccafem_ref','serie_ref','tdocod_ref','num_ref','cod_mov')
                ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
                 ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                
                ->where(function ($query6) use ($almacen) {
                  if($almacen !='Todos'){
                      $query6->where('cpe_cabecera.id_almacen',$almacen);
                  }                
                }) 
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                ->where(function ($query){
                    $query->where('cpe_cabecera.tdocod','01');
                })
                ->where(function ($query1) use ($vendedor) {
                      if($vendedor !='Todos'){
                         $query1->Where('cpe_cabecera.IdUsuario_ven',$vendedor);
                      }
                    })
                 ->where(function ($query2) use ($cliente) {
                      if($cliente !='Todos'){
                         $query2->Where('cpe_cabecera.clicod',$cliente);
                      }
                    })
                ->orderBy('IdCpe_cabecera','desc')->get();

                 $boletas = cpe_cabecera::select('ccaitv','cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','monnom as moneda','ccaitv as total','cpe_cabecera.ccabaj as baja','ccasunrescod','tdides as documentoidentidad','ccandi as numerodocumento',
                  'cpe_cabecera.tdocod','ccafve as fechaven','cpe_cabecera.tdicod','ccatexo','ccafem_ref','serie_ref','tdocod_ref','num_ref','cod_mov')
                ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
                 ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                
                ->where(function ($query6) use ($almacen) {
                  if($almacen !='Todos'){
                      $query6->where('cpe_cabecera.id_almacen',$almacen);
                  }                
                }) 
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                ->where(function ($query){
                    $query->where('cpe_cabecera.tdocod','03');
                })
                ->where(function ($query1) use ($vendedor) {
                      if($vendedor !='Todos'){
                         $query1->Where('cpe_cabecera.IdUsuario_ven',$vendedor);
                      }
                    })
                 ->where(function ($query2) use ($cliente) {
                      if($cliente !='Todos'){
                         $query2->Where('cpe_cabecera.clicod',$cliente);
                      }
                    })
                ->orderBy('IdCpe_cabecera','desc')->get();


                 $notas = cpe_cabecera::select('ccaitv','cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','monnom as moneda','ccaitv as total','cpe_cabecera.ccabaj as baja','ccasunrescod','tdides as documentoidentidad','ccandi as numerodocumento',
                  'cpe_cabecera.tdocod','ccafve as fechaven','cpe_cabecera.tdicod','ccatexo','ccafem_ref','serie_ref','tdocod_ref','num_ref','cod_mov')
                ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
                 ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                
                ->where(function ($query6) use ($almacen) {
                  if($almacen !='Todos'){
                      $query6->where('cpe_cabecera.id_almacen',$almacen);
                  }                
                }) 
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                ->where(function ($query){
                    $query->where('cpe_cabecera.tdocod','07');
                })
                ->where(function ($query1) use ($vendedor) {
                      if($vendedor !='Todos'){
                         $query1->Where('cpe_cabecera.IdUsuario_ven',$vendedor);
                      }
                    })
                 ->where(function ($query2) use ($cliente) {
                      if($cliente !='Todos'){
                         $query2->Where('cpe_cabecera.clicod',$cliente);
                      }
                    })
                ->orderBy('IdCpe_cabecera','desc')->get();

      

                  $totalfacturas = $facturas->sum('ccaitv');
                  $totalboletas = $boletas->sum('ccaitv');
                  $totalnotas = $notas->sum('ccaitv');


                  $totalfacturasexo = $facturas->sum('ccatexo');
                  $totalboletasexo = $boletas->sum('ccatexo');
                  $totalnotasexo = $notas->sum('ccatexo');
                    

                  $totalfacturasinaf = $facturas->sum('ccatvi');
                  $totalboletasinaf = $boletas->sum('ccatvi');
                  $totalnotasinaf = $notas->sum('ccatvi');
                  

                   $totalfacturasigv = $facturas->sum('ccaigv');
                  $totalboletasigv = $boletas->sum('ccaigv');
                  $totalnotasigv = $notas->sum('ccaigv');
                 
                  $totalfacturasicbper = $facturas->sum('icbper');
                  $totalboletasicbper = $boletas->sum('icbper');
                  $totalnotasicbper = $notas->sum('icbper');

                 $nompdffile='resumen_ventas_contador'.$data_sucursal->IdEmpresa.'_'.now()->format('Y-m-d').'.pdf'; 


                 $rutapdf = public_path().'/reporte_cuentas_cobrar/';

                 if(file_exists($rutapdf.$nompdffile)){
                  unlink($rutapdf.$nompdffile);
                 }

                $view = \View::make('formatos_reportes.reporte_pdf_ventas_contador', compact('dato_cliente','fecin','fecfin','facturas','boletas','notas','totalfacturas','totalboletas','totalnotas','totalfacturasexo','totalboletasexo','totalnotasexo','totalfacturasinaf','totalboletasinaf','totalnotasinaf','totalfacturasigv','totalboletasigv','totalnotasigv','totalfacturasicbper','totalboletasicbper','totalnotasicbper','dato_vendedor','vendedor','cliente','empresa','data_sucursal'));
                            
                $pdf = \App::make('dompdf.wrapper');
                $contenido = $view->render();
                $pdf->loadHTML($view)->setPaper('A4', 'Landscape')->save($rutapdf.$nompdffile);


                $headers = array(
                        'Content-Type: application/pdf',
                      );

                return response()->download($rutapdf.$nompdffile);
  
          

            break;

             case 51:

                if($vendedor!='Todos'){
                $dato_vendedor = DB::tABLE('users')->where('IdUsuario',$vendedor)->first();
                }

                if($cliente!='Todos'){
                  $dato_cliente = DB::tABLE('cliente')->where('clicod',$cliente)->first();
                }

                $comprobantes = cpe_cabecera::select('cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','det.cdedes as producto','det.cdecan as cantidad','cdepuni as precio','monnom as moneda','det.cdevve as total','cpe_cabecera.ccabaj as baja','det.costo','ccasunrescod','tdides as documentoidentidad','ccandi as numerodocumento','procod','cdedes','cdecan','cdepuni','cdevve','umecod','ccaitv','id_almacen_pro')
                ->leftjoin('cpe_detalle as det','cpe_cabecera.IdCpe_cabecera','det.IdCpe_cabecera')
                ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                 ->where(function ($query) use ($vendedor) {
                      if($vendedor !='Todos'){
                         $query->Where('cpe_cabecera.IdUsuario_ven',$vendedor);
                      }
                    })
                ->where(function ($query) {
                  $query->where('cpe_cabecera.tdocod','01')
                    ->orWhere('cpe_cabecera.tdocod','03')
                    ->orWhere('cpe_cabecera.tdocod','13')
                    ->orWhere('cpe_cabecera.tdocod','14')
                    //->orWhere('cpe_cabecera.tdocod','07')
                    ;
                  }) 
                  ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                   ->where(function ($query2) use ($cliente) {
                      if($cliente !='Todos'){
                         $query2->Where('cpe_cabecera.clicod',$cliente);
                      }
                    })
                  ->whereNotNull('ccabaj')
                
               /* ->where(function ($query6) use ($idalmacen) {
                  if($idalmacen !='Todos' && !empty($idalmacen)){
                      $query6->where('det.id_almacen_pro',$idalmacen);
                  }                
                })   */ 
                 ->orderBy('IdCpe_cabecera','desc')->get();




                 $cabecera = DB::tABLE('cpe_cabecera')
             //    ->join('cpe_detalle','cpe_detalle.IdCpe_cabecera','cpe_cabecera.IdCpe_cabecera')
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                 ->where(function ($query) use ($vendedor){
                      if($vendedor !='Todos'){
                         $query->Where('cpe_cabecera.IdUsuario_ven',$vendedor);
                      }
                    })
                ->where(function ($query) {
                  $query->where('cpe_cabecera.tdocod','01')
                    ->orWhere('cpe_cabecera.tdocod','03')
                    ->orWhere('cpe_cabecera.tdocod','13')
                    ->orWhere('cpe_cabecera.tdocod','14')
                   // ->orWhere('cpe_cabecera.tdocod','07')
                    ;
                  }) 
                ->whereNotNull('ccabaj')
               // ->where('ccanot','')
                ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                 ->where(function ($query2) use ($cliente) {
                      if($cliente !='Todos'){
                         $query2->Where('cpe_cabecera.clicod',$cliente);
                      }
                    })
                
               /* ->where(function ($query6) use ($idalmacen) {
                  if($idalmacen !='Todos' && !empty($idalmacen)){
                      $query6->where('cpe_detalle.id_almacen_pro',$idalmacen);
                  }                
                }) */
               // ->groupby('cpe_cabecera.IdCpe_cabecera') 
                 ->get();

                 $total = $cabecera->where('ccanot','')->sum('ccaitv');

                 $totalnotas = $cabecera->where('tdocod','7')->sum('ccaitv');

                 $totalefectivo = $cabecera->where('ccanot','')->sum('totalcontado');
                 $totalcredito = $cabecera->where('ccanot','')->sum('totalcredito');
             
                 $nompdffile='resumen_ventas_anuladas_'.$data_sucursal->IdEmpresa.'_'.now()->format('Y-m-d').'.pdf'; 


                 $rutapdf = public_path().'/reporte_cuentas_cobrar/';

                 if(file_exists($rutapdf.$nompdffile)){
                  unlink($rutapdf.$nompdffile);
                 }

                $view = \View::make('formatos_reportes.reporte_pdf_ventas_anuladas', compact('totalnotas','total','dato_vendedor','cabecera','comprobantes','fecin','fecfin','empresa','sucursal','almacen','data_sucursal','dato_cliente','totalefectivo','totalcredito'));

                            
                $pdf = \App::make('dompdf.wrapper');
                $contenido = $view->render();
                $pdf->loadHTML($view)->setPaper('A4', 'lanscape')->save($rutapdf.$nompdffile);


                $headers = array(
                        'Content-Type: application/pdf',
                      );

                return response()->download($rutapdf.$nompdffile);
  
          

            break;

             case 52:

                if($vendedor!='Todos'){
                $dato_vendedor = DB::tABLE('users')->where('IdUsuario',$vendedor)->first();
                }

                if($cliente!='Todos'){
                  $dato_cliente = DB::tABLE('cliente')->where('clicod',$cliente)->first();
                }

                $comprobantes = cpe_cabecera::select('cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','det.cdedes as producto','det.cdecan as cantidad','cdepuni as precio','monnom as moneda','det.cdevve as total','cpe_cabecera.ccabaj as baja','det.costo','ccasunrescod','tdides as documentoidentidad','ccandi as numerodocumento','procod','cdedes','cdecan','cdepuni','cdevve','umecod','ccaitv','id_almacen_pro')
                ->leftjoin('cpe_detalle as det','cpe_cabecera.IdCpe_cabecera','det.IdCpe_cabecera')
                ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                 ->where(function ($query) use ($vendedor) {
                      if($vendedor !='Todos'){
                         $query->Where('cpe_cabecera.IdUsuario_ven',$vendedor);
                      }
                    })
                ->where(function ($query) {
                  $query->where('cpe_cabecera.tdocod','01')
                    ->orWhere('cpe_cabecera.tdocod','03')
                    ->orWhere('cpe_cabecera.tdocod','13')
                    ->orWhere('cpe_cabecera.tdocod','14')
                    //->orWhere('cpe_cabecera.tdocod','07')
                    ;
                  }) 
                  ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                   ->where(function ($query2) use ($cliente) {
                      if($cliente !='Todos'){
                         $query2->Where('cpe_cabecera.clicod',$cliente);
                      }
                    })
                
               /* ->where(function ($query6) use ($idalmacen) {
                  if($idalmacen !='Todos' && !empty($idalmacen)){
                      $query6->where('det.id_almacen_pro',$idalmacen);
                  }                
                })   */ 
                 ->orderBy('IdCpe_cabecera','desc')->get();




                 $cabecera = DB::tABLE('cpe_cabecera')
             //    ->join('cpe_detalle','cpe_detalle.IdCpe_cabecera','cpe_cabecera.IdCpe_cabecera')
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                 ->where(function ($query) use ($vendedor){
                      if($vendedor !='Todos'){
                         $query->Where('cpe_cabecera.IdUsuario_ven',$vendedor);
                      }
                    })
                ->where(function ($query) {
                  $query->where('cpe_cabecera.tdocod','01')
                    ->orWhere('cpe_cabecera.tdocod','03')
                    ->orWhere('cpe_cabecera.tdocod','13')
                    ->orWhere('cpe_cabecera.tdocod','14')
                   // ->orWhere('cpe_cabecera.tdocod','07')
                    ;
                  }) 
                ->whereNull('ccabaj')
                ->where('ccanot','')
                ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                 ->where(function ($query2) use ($cliente) {
                      if($cliente !='Todos'){
                         $query2->Where('cpe_cabecera.clicod',$cliente);
                      }
                    })
                
               /* ->where(function ($query6) use ($idalmacen) {
                  if($idalmacen !='Todos' && !empty($idalmacen)){
                      $query6->where('cpe_detalle.id_almacen_pro',$idalmacen);
                  }                
                }) */
               // ->groupby('cpe_cabecera.IdCpe_cabecera') 
                 ->get();

                 $total = $cabecera->where('ccanot','')->sum('ccaitv');

                 $totalnotas = $cabecera->where('tdocod','7')->sum('ccaitv');

                 $totalefectivo = $cabecera->where('ccanot','')->sum('totalcontado');
                 $totalcredito = $cabecera->where('ccanot','')->sum('totalcredito');
             
                 $nompdffile='resumen_pedidos_anulados_'.$data_sucursal->IdEmpresa.'_'.now()->format('Y-m-d').'.pdf'; 


                 $rutapdf = public_path().'/reporte_cuentas_cobrar/';

                 if(file_exists($rutapdf.$nompdffile)){
                  unlink($rutapdf.$nompdffile);
                 }

                $view = \View::make('formatos_reportes.reporte_pdf_pedidos_anulados', compact('totalnotas','total','dato_vendedor','cabecera','comprobantes','fecin','fecfin','empresa','sucursal','almacen','data_sucursal','dato_cliente','totalefectivo','totalcredito'));

                            
                $pdf = \App::make('dompdf.wrapper');
                $contenido = $view->render();
                $pdf->loadHTML($view)->setPaper('A4', 'lanscape')->save($rutapdf.$nompdffile);


                $headers = array(
                        'Content-Type: application/pdf',
                      );

                return response()->download($rutapdf.$nompdffile);
  
          

            break;


                  case 53:

                 $comprobantes = compras_cabecera::select('total_com','com_cab_id','com_fec as fecha','tdodes as comprobante','com_doc_ser as serie','com_doc_num as numero','prov_raz as cliente','monnom as moneda','total_com as total','tdides as documentoidentidad','prov_ruc as numerodocumento','compras_cabecera.tdocod')
                ->leftjoin('moneda as m','compras_cabecera.mon_id','=','m.moncod')
                ->leftjoin('proveedor as cl','compras_cabecera.prov_id','=','cl.prov_id')
               //->leftjoin('empresa as e','compras_cabecera.IdEmpresa','=','e.IdEmpresa')
                ->leftjoin('tipo_documento as tip_d','compras_cabecera.tdocod','=','tip_d.tdocod')
                ->leftjoin('tipo_documento_identidad as tdi','cl.tdicod','=','tdi.tdicod')
                 ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('compras_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                
                ->where(function ($query6) use ($almacen) {
                  if($almacen !=0 && !empty($almacen)){
                      $query6->where('compras_cabecera.id_almacen',$almacen);
                  }                
                }) 
                ->where('compras_cabecera.com_fec','>=',$fecin)
                ->where('compras_cabecera.com_fec','<=',$fecfin)
                /*->where(function ($query){
                    $query->where('compras_cabecera.tdocod','01')
                          ->orwhere('compras_cabecera.tdocod','03')
                          ->orWhere('compras_cabecera.tdocod','13')
                          ->orWhere('compras_cabecera.tdocod','07');
                })*/
                ->where('est_compra','Registrado')
                ->orderBy('com_cab_id','desc')->get();

            //  dd($comprobantes);

                  $total = $comprobantes->sum('total_com');

                  $totalexoneradasfacturas = $comprobantes->where('tdocod','01')->sum('total_com');
                  $totalgravadasfacturas = $comprobantes->where('tdocod','01')->sum('ccatvg');

                  $totalexoneradasboletas = $comprobantes->where('tdocod','03')->sum('total_com');
                  $totalgravadasboletas = $comprobantes->where('tdocod','03')->sum('ccatvg');

                  $totalexoneradasnotas = $comprobantes->where('tdocod','07')->sum('total_com');
                  $totalgravadasnotas = $comprobantes->where('tdocod','07')->sum('ccatvg');


            

                 $sucursal = EmpresaNegocios::FindOrFail($request->get('sucursal'));

                 $empresa = Empresa::findOrFail($sucursal->IdEmpresa);

                $nompdffile='RESUMEN_COMPRAS_'.$fecin.'_'.$fecfin.'.pdf'; 


                 $rutapdf = public_path().'/reporte_cuentas_cobrar/';

                 if(file_exists($rutapdf.$nompdffile)){
                  unlink($rutapdf.$nompdffile);
                 }

               
                $view = \View::make('formatos_reportes.reporte_pdf_resumen_compras', compact('fecin','fecfin','total','totalexoneradasfacturas','totalexoneradasboletas','totalgravadasfacturas','totalgravadasboletas','totalgravadasnotas','totalexoneradasnotas','empresa','sucursal'));

                            
                $pdf = \App::make('dompdf.wrapper');
                $contenido = $view->render();
                $pdf->loadHTML($view)->setPaper('A4', 'lanscape')->save($rutapdf.$nompdffile);

               
                 $headers = array(
                    'Content-Type: application/pdf',
                  );

     


           
                 return response()->download($rutapdf.$nompdffile);

              


            break;

             case 54:

               $comprobantes = compras_cabecera::select('total_com','compras_cabecera.com_cab_id','com_fec as fecha','tdodes as comprobante','compras_cabecera.com_doc_ser as serie','compras_cabecera.com_doc_num as numero','prov_raz as cliente','monnom as moneda','total_com as total','tdides as documentoidentidad','prov_ruc as numerodocumento',
                  'compras_cabecera.tdocod','com_fec_ven as fechaven','cl.tdicod','total_com as ccatexo')
                ->leftjoin('moneda as m','compras_cabecera.mon_id','=','m.moncod')
                ->leftjoin('proveedor as cl','compras_cabecera.prov_id','=','cl.prov_id')
                ->leftjoin('empresa as e','compras_cabecera.IdEmpresa','=','e.IdEmpresa')
                ->leftjoin('tipo_documento as tip_d','compras_cabecera.tdocod','=','tip_d.tdocod')
                ->leftjoin('tipo_documento_identidad as tdi','tdi.tdicod','=','cl.tdicod')
                 ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('compras_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                
                ->where(function ($query6) use ($almacen) {
                  if($almacen !='Todos'){
                      $query6->where('compras_cabecera.id_almacen',$almacen);
                  }                
                }) 
                ->where('compras_cabecera.com_fec','>=',$fecin)
                ->where('compras_cabecera.com_fec','<=',$fecfin)
                /* ->where(function ($query){
                    $query->where('compras_cabecera.tdocod','01')
                          ->orwhere('compras_cabecera.tdocod','03')
                          ->orWhere('compras_cabecera.tdocod','13');
                })
               ->where(function ($query1) use ($vendedor) {
                      if($vendedor !='Todos'){
                         $query1->Where('compras_cabecera.IdUsuario_ven',$vendedor);
                      }
                    })*/
                 /*->where(function ($query2) use ($cliente) {
                      if($cliente !='Todos'){
                         $query2->Where('compras_cabecera.clicod',$cliente);
                      }
                    })*/
                ->orderBy('com_fec','desc')->get();

      

                  $total = $comprobantes->sum('total_com');
             
                 $nompdffile='resumen_compras_contador'.$data_sucursal->IdEmpresa.'_'.now()->format('Y-m-d').'.pdf'; 


                 $rutapdf = public_path().'/reporte_cuentas_cobrar/';

                 if(file_exists($rutapdf.$nompdffile)){
                  unlink($rutapdf.$nompdffile);
                 }

                $view = \View::make('formatos_reportes.reporte_pdf_compras_contador', compact('dato_cliente','fecin','fecfin','comprobantes','total','dato_vendedor','vendedor','cliente','empresa','data_sucursal'));
                            
                $pdf = \App::make('dompdf.wrapper');
                $contenido = $view->render();
                $pdf->loadHTML($view)->setPaper('A4', 'Landscape')->save($rutapdf.$nompdffile);


                $headers = array(
                        'Content-Type: application/pdf',
                      );

                return response()->download($rutapdf.$nompdffile);
  
          

            break;

               case 55:

              $comprobantes = DB::tABLE('compras_cabecera')->select('compras_detalle.vencimiento','compras_detalle.lote','pronom')
              ->leftjoin('proveedor','compras_cabecera.prov_id','proveedor.prov_id')
              ->leftjoin('compras_detalle','compras_cabecera.com_cab_id','compras_detalle.com_cab_id')
              ->leftjoin('productos','productos.IdProducto','compras_detalle.pro_id')
              ->join('moneda','compras_cabecera.mon_id','moneda.moncod')
              ->join('tipo_documento','compras_cabecera.tdocod','tipo_documento.tdocod')
              
              ->where('est_compra','Registrado')
                ->where('compras_cabecera.com_fec','>=',$fecin)
                ->where('compras_cabecera.com_fec','<=',$fecfin)
                  ->where(function ($query) use ($sucursal){
                      if(!$sucursal !=0 && !empty($sucursal)){
                          $query->where('compras_cabecera.id_empresa_negocio',$sucursal);
                      }
                     
                  })
                  ->where(function ($query1) use ($proveedor){
                      if($proveedor !=0){
                          $query1->where('compras_cabecera.prov_id',$proveedor);
                      }
                      
                  })

                   ->where(function ($query8) use ($pro){
                      if($pro !=0){
                          $query8->where('compras_detalle.IdProducto_rel',$pro);
                      }
                      
                  })
                        
                  ->where(function ($query6) use ($almacen) {
                      if($almacen !=0 && !empty($almacen)){
                          $query6->where('compras_cabecera.id_almacen',$almacen);
                      }                
                    }) 
              ->where('compras_cabecera.tdocod','!=','80')
              ->groupby('pro_id','compras_detalle.vencimiento')
              ->orderby('compras_cabecera.com_cab_id','desc')
              ->get();
                 //dd($comprobantes);

                  $total = $comprobantes->sum('total_com');


                 $nompdffile='resumen_compras_producto_lote'.$data_sucursal->IdEmpresa.'_'.now()->format('Y-m-d').'.pdf'; 


                 $rutapdf = public_path().'/reporte_cuentas_cobrar/';

                 if(file_exists($rutapdf.$nompdffile)){
                  unlink($rutapdf.$nompdffile);
                 }

                $view = \View::make('formatos_reportes.reporte_pdf_compras_productos_lote', compact('fecin','fecfin','comprobantes','total','empresa','data_sucursal'));
                            
                $pdf = \App::make('dompdf.wrapper');
                $contenido = $view->render();
                $pdf->loadHTML($view)->setPaper('A4', 'Portrait')->save($rutapdf.$nompdffile);


                $headers = array(
                        'Content-Type: application/pdf',
                      );

                return response()->download($rutapdf.$nompdffile);
  

              break;



                  case 13:

              $comprobantes = DB::tABLE('compras_cabecera')
              ->leftjoin('proveedor','compras_cabecera.prov_id','proveedor.prov_id')
              ->leftjoin('compras_detalle','compras_cabecera.com_cab_id','compras_detalle.com_cab_id')
              ->leftjoin('productos','productos.IdProducto','compras_detalle.pro_id')
              ->join('moneda','compras_cabecera.mon_id','moneda.moncod')
              ->join('tipo_documento','compras_cabecera.tdocod','tipo_documento.tdocod')
              
              ->where('est_compra','Registrado')
                ->where('compras_cabecera.com_fec','>=',$fecin)
                ->where('compras_cabecera.com_fec','<=',$fecfin)
                  ->where(function ($query) use ($sucursal){
                      if(!$sucursal !=0 && !empty($sucursal)){
                          $query->where('compras_cabecera.id_empresa_negocio',$sucursal);
                      }
                     
                  })
                  ->where(function ($query1) use ($proveedor){
                      if($proveedor !=0){
                          $query1->where('compras_cabecera.prov_id',$proveedor);
                      }
                      
                  })

                   ->where(function ($query8) use ($pro){
                      if($pro !=0){
                          $query8->where('compras_detalle.IdProducto_rel',$pro);
                      }
                      
                  })
                        
                  ->where(function ($query6) use ($almacen) {
                      if($almacen !=0 && !empty($almacen)){
                          $query6->where('compras_cabecera.id_almacen',$almacen);
                      }                
                    }) 
              ->where('compras_cabecera.tdocod','!=','80')
              ->orderby('compras_cabecera.com_cab_id','desc')
              ->get();
                 //dd($comprobantes);

                  $total = $comprobantes->sum('total_com');


                    $nompdffile='resumen_compras_productos'.$data_sucursal->IdEmpresa.'_'.now()->format('Y-m-d').'.pdf'; 


                 $rutapdf = public_path().'/reporte_cuentas_cobrar/';

                 if(file_exists($rutapdf.$nompdffile)){
                  unlink($rutapdf.$nompdffile);
                 }

                $view = \View::make('formatos_reportes.reporte_pdf_compras_productos', compact('fecin','fecfin','comprobantes','total','empresa','data_sucursal'));
                            
                $pdf = \App::make('dompdf.wrapper');
                $contenido = $view->render();
                $pdf->loadHTML($view)->setPaper('A4', 'Portrait')->save($rutapdf.$nompdffile);


                $headers = array(
                        'Content-Type: application/pdf',
                      );

                return response()->download($rutapdf.$nompdffile);


             break;


               //CONSOLIDADO DE VENTAS
              case 80:

                $boletas = cpe_cabecera::select('ccafem as FECHA', 'tdodes as COMPROBANTE','serdoc as SERIE',DB::raw('MIN(numdoc) as INICIO'),DB::raw( 'MAX(numdoc) as FIN'),DB::raw('SUM(ccatvg) as VALOR_VENTA'),DB::raw('SUM(ccaigv) as IGV'),DB::raw('SUM(ccaitv) as TOTAL'))
                ->leftjoin('tipo_documento','tipo_documento.tdocod','cpe_cabecera.tdocod')
                ->whereNull('ccabaj')
                ->where('cpe_cabecera.tdocod','03')
                ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                }) 
                ->where(function ($query6) use ($almacen) {
                  if($almacen !='Todos'){
                      $query6->where('cpe_cabecera.id_almacen',$almacen);
                  }                
                }) 
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                ->groupby('ccafem')
                ->groupby('tdodes')
                ->orderBy('FECHA','asc')
                ->orderBy('INICIO','asc')
                ->get();

                $facturas = cpe_cabecera::select('ccaitv','cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','monnom as moneda','ccaitv as total','cpe_cabecera.ccabaj as baja','ccasunrescod','tdides as documentoidentidad','ccandi as numerodocumento','cpe_cabecera.tdocod','ccatexo','ccatvg','ccatvi')
                ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
                ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                
                ->where(function ($query6) use ($almacen) {
                  if($almacen !=0 && !empty($almacen)){
                      $query6->where('cpe_cabecera.id_almacen',$almacen);
                  }                
                }) 
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                ->where(function ($query){
                    $query->where('cpe_cabecera.tdocod','01');
                })
                ->whereNull('ccabaj')
                ->orderBy('IdCpe_cabecera','desc')->get();

             

                $total_boletas = $boletas->sum('TOTAL');
                $total_facturas = $facturas->sum('total');


                $nompdffile='consolidado_ventas'.$data_sucursal->IdEmpresa.'_'.now()->format('Y-m-d').'.pdf'; 


                $rutapdf = public_path().'/reporte_cuentas_cobrar/';

                 if(file_exists($rutapdf.$nompdffile)){
                  unlink($rutapdf.$nompdffile);
                 }

                $view = \View::make('formatos_reportes.reporte_pdf_consolidado_ventas', compact('fecin','fecfin','facturas','boletas','total_facturas','total_boletas','empresa','data_sucursal'));
                            
                $pdf = \App::make('dompdf.wrapper');
                $contenido = $view->render();
                $pdf->loadHTML($view)->setPaper('A4', 'Portrait')->save($rutapdf.$nompdffile);


                $headers = array(
                        'Content-Type: application/pdf',
                      );

                return response()->download($rutapdf.$nompdffile);





             

         
      }

    }



    public function consulta_stock_productos(Request $request){


                   $tipo = $request->get('promocion');
          $categoria = $request->get('cmbCatId');
          $sucursal = $request->get('sucursal');
          $negocios = DB::tABLE('empresa_negocios')->get();
          $almacen = $request->get('almacen');
          $datosalm ="";

            $buspro = trim($request->get('buspro'));
            
             $categorias = DB::tABLE('categorias')->get();

 

            $tipos_productos = DB::tABLE('tipos_productos')->get();

          

                $productos = DB::tABLE('productos as p')
                ->select('producto_stock.lote','producto_stock.vencimiento','producto_stock.stock_inicial','producto_stock.stock','p.procod','p.pronom','m.monnom','u.umenom','p.propun','p.proest','producto_stock.IdProducto','mar.mar_nom as marca','p.costo','p.propun','modelo')
                ->leftjoin('moneda as m','p.moncod','=','m.moncod')
                ->leftjoin('marcas as mar','mar.mar_id','=','p.marca')
                ->leftjoin('unidad_medida as u','p.umecod','=','u.umecod')
               // ->leftjoin('producto_empresa','producto_empresa.IdProducto','p.IdProducto')
                ->leftjoin('producto_stock','producto_stock.IdProducto','p.IdProducto')
               
                ->where(function ($query1) use($buspro) {

                  if(!empty($buspro)){
                    $query1->where('pronom','like','%'.$buspro.'%')
                            ->orwhere('procod',$buspro);
                  }
                  
                })
               
                ->where('tipo','1')
         
                ->groupby('p.IdProducto')
                ->orderby('pronom','asc')
                ->get();


                $vista = view('empresas.reportes.consulta_stock_productos',compact('productos','buspro','categorias','tipos_productos','categoria','tipo'))->render();

              if($request->ajax()){
                 return response()->json(['vista'=>$vista]);

              }



    }



    public function buscarreporte(Request $request){

      $fecin = $request->get('fecin');
      $fecfin = $request->get('fecfin');
      $opcion = $request->get('opcion');
      $vendedor = $request->get('vendedor');
      $cliente = $request->get('cliente');
      $producto = $request->get('producto');
      $pro = $request->get('IdProducto');
      $almacen = $request->get('almacen');
      $sucursal = $request->get('sucursal');
      $proveedor = $request->get('proveedor');
     
      $data_sucursal = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',$sucursal)->first();

      //$productos = DB::tABLE('productos')->where('tipo','1')->get();
      $negocios = DB::tABLE('empresa_negocios')->get();

      if(!empty($sucursal)){
        $almacenes = DB::tABLE('almacenes')->where('id_empresa_negocio',$sucursal)->get();
      }else{
        $almacenes = DB::tABLE('almacenes')->where('id_empresa_negocio',$negocios->first()->id_empresa_negocio)->get();
      }

      $dat_ven = DB::tABLE('users')->where('IdUsuario',$request->get('vendedor'))->first();

      $dat_cli = DB::tABLE('cliente')->where('clicod',$request->get('cliente'))->first();

       $empresa = DB::tABLE('empresa')->where('IdEmpresa',$data_sucursal->IdEmpresa)->first();

      $hora_rep = now()->format('Y-m-d H:i:s');
      $dato_vendedor ="";
      $dato_cliente ="";

      $opcion = $request->input('opcion');

      $accion = $request->input('accion', 'buscar');

      switch ($opcion){
            case 1:

                if($vendedor!='Todos'){
                $dato_vendedor = DB::tABLE('users')->where('IdUsuario',$vendedor)->first();
                }

                if($cliente!='Todos'){
                  $dato_cliente = DB::tABLE('cliente')->where('clicod',$cliente)->first();
                }

                $comprobantes = cpe_cabecera::select('cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','det.cdedes as producto','det.cdecan as cantidad','cdepuni as precio','monnom as moneda','det.cdevve as total','cpe_cabecera.ccabaj as baja','det.costo','ccasunrescod','tdides as documentoidentidad','ccandi as numerodocumento','procod','cdedes','cdecan','cdepuni','cdevve','umecod','ccaitv','id_almacen_pro')
                ->leftjoin('cpe_detalle as det','cpe_cabecera.IdCpe_cabecera','det.IdCpe_cabecera')
                ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                 ->where(function ($query) use ($vendedor) {
                      if($vendedor !='Todos'){
                         $query->Where('cpe_cabecera.IdUsuario_ven',$vendedor);
                      }
                    })
                ->where(function ($query) {
                  $query->where('cpe_cabecera.tdocod','01')
                    ->orWhere('cpe_cabecera.tdocod','03')
                    ->orWhere('cpe_cabecera.tdocod','13')
                    ->orWhere('cpe_cabecera.tdocod','14')
                    //->orWhere('cpe_cabecera.tdocod','07')
                    ;
                  }) 
                  ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                   ->where(function ($query2) use ($cliente) {
                      if($cliente !='Todos'){
                         $query2->Where('cpe_cabecera.clicod',$cliente);
                      }
                    })
                    ->whereNull('ccabaj')
                 ->orderBy('IdCpe_cabecera','desc')->get();




                 $cabecera = DB::tABLE('cpe_cabecera')
             //    ->join('cpe_detalle','cpe_detalle.IdCpe_cabecera','cpe_cabecera.IdCpe_cabecera')
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                 ->where(function ($query) use ($vendedor){
                      if($vendedor !='Todos'){
                         $query->Where('cpe_cabecera.IdUsuario_ven',$vendedor);
                      }
                    })
                ->where(function ($query) {
                  $query->where('cpe_cabecera.tdocod','01')
                    ->orWhere('cpe_cabecera.tdocod','03')
                    ->orWhere('cpe_cabecera.tdocod','13')
                    ->orWhere('cpe_cabecera.tdocod','14')
                   // ->orWhere('cpe_cabecera.tdocod','07')
                    ;
                  }) 
                ->whereNull('ccabaj')
                ->where('ccanot','')
                ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                 ->where(function ($query2) use ($cliente) {
                      if($cliente !='Todos'){
                         $query2->Where('cpe_cabecera.clicod',$cliente);
                      }
                    })
                
               /* ->where(function ($query6) use ($idalmacen) {
                  if($idalmacen !='Todos' && !empty($idalmacen)){
                      $query6->where('cpe_detalle.id_almacen_pro',$idalmacen);
                  }                
                }) */
               // ->groupby('cpe_cabecera.IdCpe_cabecera') 
                 ->get();

                 $total = $cabecera->where('ccanot','')->sum('ccaitv');

                 $totalnotas = $cabecera->where('tdocod','7')->sum('ccaitv');

                 $totalefectivo = $cabecera->where('ccanot','')->sum('totalcontado');

                 $totalcredito = $cabecera->where('ccanot','')->sum('totalcredito');

                $vista = view('empresas.reportes.ventas', compact('totalnotas','total','dato_vendedor','cabecera','comprobantes','fecin','fecfin','empresa','sucursal','almacen','data_sucursal','dato_cliente','totalefectivo','totalcredito','cliente','vendedor'))->render();

              if($request->ajax()){
                 return response()->json(['vista'=>$vista]);

              }


            break;

            case 2:

                 if($vendedor!='Todos'){
                  $dato_vendedor = DB::tABLE('users')->where('IdUsuario',$vendedor)->first();
                }

                if($cliente!='Todos'){
                  $dato_cliente = DB::tABLE('cliente')->where('clicod',$cliente)->first();
                }

                $comprobantes = cpe_cabecera::select('cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','det.cdedes as producto','det.cdecan as cantidad','cdepuni as precio','monnom as moneda','det.cdevve as total','cpe_cabecera.ccabaj as baja','det.costo','ccasunrescod','tdides as documentoidentidad','ccandi as numerodocumento','procod','cdedes','cdecan','cdepuni','cdevve','umecod','ccaitv','id_almacen_pro')
                ->leftjoin('cpe_detalle as det','cpe_cabecera.IdCpe_cabecera','det.IdCpe_cabecera')
                ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                 ->whereNull('ccabaj')
                 ->where(function ($query) use ($vendedor) {
                      if($vendedor !='Todos'){
                         $query->Where('cpe_cabecera.IdUsuario_ven',$vendedor);
                      }
                    })
                ->where(function ($query) {
                  $query->where('cpe_cabecera.tdocod','01')
                    ->orWhere('cpe_cabecera.tdocod','03')
                    ->orWhere('cpe_cabecera.tdocod','13')
                    ->orWhere('cpe_cabecera.tdocod','14')
                    ->orWhere('cpe_cabecera.tdocod','07')
                    ;
                  }) 
                    ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                       ->where(function ($query2) use ($cliente) {
                      if($cliente !='Todos'){
                         $query2->Where('cpe_cabecera.clicod',$cliente);
                      }
                    })
                 ->orderBy('IdCpe_cabecera','desc')->get();




                 $cabecera = DB::tABLE('cpe_cabecera')
             //    ->join('cpe_detalle','cpe_detalle.IdCpe_cabecera','cpe_cabecera.IdCpe_cabecera')
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                 ->where(function ($query) use ($vendedor) {
                      if($vendedor !='Todos'){
                         $query->Where('cpe_cabecera.IdUsuario_ven',$vendedor);
                      }
                    })
                  ->whereNull('ccabaj')
                ->where(function ($query) {
                  $query->where('cpe_cabecera.tdocod','01')
                    ->orWhere('cpe_cabecera.tdocod','03')
                    ->orWhere('cpe_cabecera.tdocod','13')
                    ->orWhere('cpe_cabecera.tdocod','14')
                    ->orWhere('cpe_cabecera.tdocod','07')
                    ;
                  }) 
                    ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                })
                   ->where(function ($query2) use ($cliente) {
                      if($cliente !='Todos'){
                         $query2->Where('cpe_cabecera.clicod',$cliente);
                      }
                    })
                 ->get();

                 $total = $cabecera->where('ccanot','')->sum('ccaitv');

                 $totalnotas = $cabecera->where('tdocod','7')->sum('ccaitv');

                 /*---------------------------------------REPORTE VENTAS POR PRODUCTO RESUMEN------------------------------------------------------*/


                $productos = cpe_cabecera::select(DB::RAW('sum(cpe_detalle.cdecan*cpe_det_factor) as cantidad'),'cdedes',DB::RAW('sum(cpe_detalle.cdevve) as precio'),'cpe_detalle.procod','umecod')
                ->leftjoin('cpe_detalle','cpe_detalle.IdCpe_cabecera','cpe_cabecera.IdCpe_cabecera')
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                ->whereNull('cpe_cabecera.ccabaj')
                ->where('cdecan','>','0')
                 ->whereNull('ccabaj')
                ->where(function ($query) use ($vendedor) {
                      if($vendedor !='Todos'){
                         $query->Where('cpe_cabecera.IdUsuario_ven',$vendedor);
                      }
                })
                ->where(function ($query) {
                  $query->where('cpe_cabecera.tdocod','01')
                    ->orWhere('cpe_cabecera.tdocod','03')
                    ->orWhere('cpe_cabecera.tdocod','13')
                    ->orWhere('cpe_cabecera.tdocod','14')
                    ->orWhere('cpe_cabecera.tdocod','07')
                    ;
                  })   
                ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                })
                ->where(function ($query2) use ($cliente) {
                    if($cliente !='Todos'){
                         $query2->Where('cpe_cabecera.clicod',$cliente);
                    }
                }) 
                ->orderby('cdedes','desc')
                ->groupby('procod','cdedes')
                ->get();


                     $productosnot = cpe_cabecera::select(DB::RAW('sum(cpe_detalle.cdecan*cpe_det_factor) as cantidad'),'cdedes',DB::RAW('sum(cpe_detalle.cdevve) as precio'),'cpe_detalle.procod','umecod')
                ->leftjoin('cpe_detalle','cpe_detalle.IdCpe_cabecera','cpe_cabecera.IdCpe_cabecera')
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                ->whereNull('cpe_cabecera.ccabaj')
                ->where('cdecan','>','0')
                ->where(function ($query) use ($vendedor) {
                      if($vendedor !='Todos'){
                         $query->Where('cpe_cabecera.IdUsuario_ven',$vendedor);
                      }
                })
                ->where(function ($query) {
                  $query->Where('cpe_cabecera.tdocod','07')
                    ;
                  })   
                ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                })
                ->where(function ($query2) use ($cliente) {
                    if($cliente !='Todos'){
                         $query2->Where('cpe_cabecera.clicod',$cliente);
                      }
                }) 
                ->orderby('cdedes','desc')
                ->groupby('procod')
                ->get();

                $productosnotas = $productosnot->sum('cantidad');

                $totalproductos = $productos->sum('cantidad');

                $totalmontoproductos =0;
               
                foreach ($productos as $pro) {
                   $totalmontoproductos = $totalmontoproductos + ($pro->precio);
                }


                 /*---------------------------------------------------------------------------------------------------------------------------------*/

               

              


                $vista = view('empresas.reportes.ventas_detalladas',compact('totalmontoproductos','totalnotas','total','cabecera','comprobantes','fecin','fecfin','empresa','sucursal','almacen','productos','productosnotas','data_sucursal','dato_vendedor','dato_cliente','cliente','vendedor'))->render();

                    if($request->ajax()){
                 return response()->json(['vista'=>$vista]);

              }


            break;

                  case 3:

                  $facturas = cpe_cabecera::select('ccacodsun','cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','tdides as documentoidentidad','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','monnom as moneda','ccaitv as total','cpe_cabecera.ccabaj as baja','ccaigv as igv','ccatvg as gravado','ccandi as numerodocumento','ccasunrescod','cpe_cabecera.tdocod','cpe_cabecera.ccatexo as exonerado','cpe_cabecera.icbper')
                    ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                    ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                    ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                    ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                    ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
                    ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                
                ->where(function ($query6) use ($almacen) {
                  if($almacen !=0 && !empty($almacen)){
                      $query6->where('cpe_cabecera.id_almacen',$almacen);
                  }                
                }) 
                    ->where('cpe_cabecera.ccafem','>=',$fecin)
                    ->where('cpe_cabecera.ccafem','<=',$fecfin)
                    ->where(function ($query) {
                    $query->where('cpe_cabecera.tdocod','01');
                    })    
                ->orderBy('IdCpe_cabecera','desc')->get();


                $boletas = cpe_cabecera::select('ccacodsun','cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','tdides as documentoidentidad','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','monnom as moneda','ccaitv as total','cpe_cabecera.ccabaj as baja','ccaigv as igv','ccatvg as gravado','ccandi as numerodocumento','ccasunrescod','cpe_cabecera.tdocod','cpe_cabecera.ccatexo as exonerado','cpe_cabecera.icbper')
                    ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                    ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                    ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                    ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                    ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
                    ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                
                ->where(function ($query6) use ($almacen) {
                  if($almacen !=0 && !empty($almacen)){
                      $query6->where('cpe_cabecera.id_almacen',$almacen);
                  }                
                }) 
                    ->where('cpe_cabecera.ccafem','>=',$fecin)
                    ->where('cpe_cabecera.ccafem','<=',$fecfin)
                    ->where(function ($query) {
                    $query->Where('cpe_cabecera.tdocod','03');
                    })    
                ->orderBy('IdCpe_cabecera','desc')->get();

                  $notascreditos = cpe_cabecera::select('ccacodsun','cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','tdides as documentoidentidad','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','monnom as moneda','ccaitv as total','cpe_cabecera.ccabaj as baja','ccaigv as igv','ccatvg as gravado','ccandi as numerodocumento','ccasunrescod','cpe_cabecera.tdocod','cpe_cabecera.ccatexo as exonerado','cpe_cabecera.icbper')
                    ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                    ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                    ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                    ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                    ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
                    ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                
                ->where(function ($query6) use ($almacen) {
                  if($almacen !=0 && !empty($almacen)){
                      $query6->where('cpe_cabecera.id_almacen',$almacen);
                  }                
                }) 
                    ->where('cpe_cabecera.ccafem','>=',$fecin)
                    ->where('cpe_cabecera.ccafem','<=',$fecfin)
                    ->where(function ($query) {
                    $query->Where('cpe_cabecera.tdocod','07');
                    })    
                ->orderBy('IdCpe_cabecera','desc')->get();




              $fac_grav = $facturas->sum('gravado');
              $fac_igv= $facturas->sum('igv');
              $fac_exo = $facturas->sum('exonerado');
              $fac_icb= $facturas->sum('icbper');
              $fac_tot = $facturas->sum('total');


              $bol_grav = $boletas->sum('gravado');
              $bol_igv= $boletas->sum('igv');
              $bol_exo = $boletas->sum('exonerado');
              $bol_icb= $boletas->sum('icbper');
              $bol_tot = $boletas->sum('total');

              $notc_grav = $notascreditos->sum('gravado');
              $notc_igv= $notascreditos->sum('igv');
              $notc_exo = $notascreditos->sum('exonerado');
              $notc_icb= $notascreditos->sum('icbper');
              $notc_tot = $notascreditos->sum('total');

    
   
              $hora_rep = now()->format('Y-m-d H:i:s');

                $vista = view('empresas.reportes.ventas_sunat',compact('hora_rep','fecin','fecfin','facturas','boletas','notascreditos','fac_icb','fac_grav','fac_igv','fac_exo','fac_tot','bol_grav','bol_igv','bol_exo','bol_tot','bol_icb','notc_grav','notc_igv','notc_exo','notc_tot','notc_icb'))->render();

                if($request->ajax()){
                  return response()->json(['vista'=>$vista]);
                }


            break;

              case 60:

                   $comprobantes = cpe_cabecera::select('cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','cdepuni as precio','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','det.cdecan as cantidad','det.cdedes as producto','monnom as moneda','det.cdevve as total','productos.costofijo as costo','cpe_cabecera.ccabaj as baja','ccasunrescod','det.flete')
                ->leftjoin('cpe_detalle as det','cpe_cabecera.IdCpe_cabecera','det.IdCpe_cabecera')
                ->leftjoin('productos','productos.IdProducto','det.IdProducto')
                ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
                ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                
                ->where(function ($query6) use ($almacen) {
                  if($almacen !=0 && !empty($almacen)){
                      $query6->where('cpe_cabecera.id_almacen',$almacen);
                  }                
                }) 
                ->where(function ($query1){
          
                    $query1->whereNull('cpe_cabecera.ccabaj')
                          ->orwhere('cpe_cabecera.ccabaj','');
                    
                })
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin) 
                  ->where(function ($query) {
                $query->where('cpe_cabecera.tdocod','01')
                    ->orWhere('cpe_cabecera.tdocod','03')
                    ->orWhere('cpe_cabecera.tdocod','13');
                })                
                ->orderBy('IdCpe_cabecera','desc')->get();


                $totalventas = cpe_cabecera::select('cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','cdepuni as precio','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','det.cdecan as cantidad','det.cdedes as producto','monnom as moneda','det.cdevve as total','productos.costofijo as costo','cpe_cabecera.ccabaj as baja','ccasunrescod')
                ->leftjoin('cpe_detalle as det','cpe_cabecera.IdCpe_cabecera','det.IdCpe_cabecera')
                 ->leftjoin('productos','productos.IdProducto','det.IdProducto')
                ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
               ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                
                ->where(function ($query6) use ($almacen) {
                  if($almacen !=0 && !empty($almacen)){
                      $query6->where('cpe_cabecera.id_almacen',$almacen);
                  }                
                }) 
                ->where(function ($query1){
          
                    $query1->whereNull('cpe_cabecera.ccabaj')
                          ->orwhere('cpe_cabecera.ccabaj','');
                    
                })
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin) 
                  ->where(function ($query) {
                $query->where('cpe_cabecera.tdocod','01')
                    ->orWhere('cpe_cabecera.tdocod','03')
                    ->orWhere('cpe_cabecera.tdocod','13');
                })                
                ->sum('cdevve');

                 $totalcosto = cpe_cabecera::select('cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','cdepuni as precio','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','det.cdecan as cantidad','det.cdedes as producto','monnom as moneda','det.cdevve as total','productos.costofijo as costo','cpe_cabecera.ccabaj as baja','ccasunrescod','det.flete')
                ->leftjoin('cpe_detalle as det','cpe_cabecera.IdCpe_cabecera','det.IdCpe_cabecera')
                 ->leftjoin('productos','productos.IdProducto','det.IdProducto')
                ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
               ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                
                ->where(function ($query6) use ($almacen) {
                  if($almacen !=0 && !empty($almacen)){
                      $query6->where('cpe_cabecera.id_almacen',$almacen);
                  }                
                }) 
                ->where(function ($query1){
          
                    $query1->whereNull('cpe_cabecera.ccabaj')
                          ->orwhere('cpe_cabecera.ccabaj','');
                    
                })
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin) 
                  ->where(function ($query) {
                $query->where('cpe_cabecera.tdocod','01')
                    ->orWhere('cpe_cabecera.tdocod','03')
                    ->orWhere('cpe_cabecera.tdocod','13');
                })                
                ->sum(DB::RAW('det.cdecan*(det.costo+det.flete)'));

               

                $vista = view('empresas.reportes.rentabilidadcostofijo',compact('totalventas','totalcosto','comprobantes','fecin','fecfin'))->render();


                    if($request->ajax()){
                 return response()->json(['vista'=>$vista]);

              }


            break;


            case 61:

                if($vendedor!='Todos'){
                $dato_vendedor = DB::tABLE('users')->where('IdUsuario',$vendedor)->first();
                }

                if($cliente!='Todos'){
                  $dato_cliente = DB::tABLE('cliente')->where('clicod',$cliente)->first();
                }

                $comprobantes = cpe_cabecera::select('cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','monnom as moneda','cpe_cabecera.ccabaj as baja','ccasunrescod','tdides as documentoidentidad','ccandi as numerodocumento','users.name','users.apeusu',DB::RAW('sum(ccaitv) as total'))
                ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
                ->leftjoin('users','users.IdUsuario','cpe_cabecera.IdUsuario_ven')
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                 ->where(function ($query) use ($vendedor) {
                      if($vendedor !='Todos'){
                         $query->Where('cpe_cabecera.IdUsuario_ven',$vendedor);
                      }
                    })
                ->where(function ($query) {
                  $query->where('cpe_cabecera.tdocod','01')
                    ->orWhere('cpe_cabecera.tdocod','03')
                    ->orWhere('cpe_cabecera.tdocod','13')
                    ->orWhere('cpe_cabecera.tdocod','14')
                    //->orWhere('cpe_cabecera.tdocod','07')
                    ;
                  }) 
                  ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                   ->where(function ($query2) use ($cliente) {
                      if($cliente !='Todos'){
                         $query2->Where('cpe_cabecera.clicod',$cliente);
                      }
                    })
                
               /* ->where(function ($query6) use ($idalmacen) {
                  if($idalmacen !='Todos' && !empty($idalmacen)){
                      $query6->where('det.id_almacen_pro',$idalmacen);
                  }                
                })   */ 
                 ->groupby('IdUsuario_ven')
                 ->orderby('name')->get();



                 $total = $comprobantes->sum('total');

                $vista = view('empresas.reportes.resumenventasvendedor', compact('comprobantes','dato_vendedor','fecin','fecfin','empresa','sucursal','almacen','data_sucursal','dato_cliente','total'))->render();

              if($request->ajax()){
                 return response()->json(['vista'=>$vista]);

              }


            break;


             case 4:

                   $comprobantes = cpe_cabecera::select('cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','cdepuni as precio','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','det.cdecan as cantidad','det.cdedes as producto','monnom as moneda','det.cdevve as total','det.costo as costo','cpe_cabecera.ccabaj as baja','ccasunrescod','det.flete')
                ->leftjoin('cpe_detalle as det','cpe_cabecera.IdCpe_cabecera','det.IdCpe_cabecera')
                ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
                ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                
                ->where(function ($query6) use ($almacen) {
                  if($almacen !=0 && !empty($almacen)){
                      $query6->where('cpe_cabecera.id_almacen',$almacen);
                  }                
                }) 
                ->where(function ($query1){
          
                    $query1->whereNull('cpe_cabecera.ccabaj')
                          ->orwhere('cpe_cabecera.ccabaj','');
                    
                })
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin) 
                  ->where(function ($query) {
                $query->where('cpe_cabecera.tdocod','01')
                    ->orWhere('cpe_cabecera.tdocod','03')
                    ->orWhere('cpe_cabecera.tdocod','13');
                })                
                ->orderBy('IdCpe_cabecera','desc')->get();


                $totalventas = cpe_cabecera::select('cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','cdepuni as precio','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','det.cdecan as cantidad','det.cdedes as producto','monnom as moneda','det.cdevve as total','det.costo as costo','cpe_cabecera.ccabaj as baja','ccasunrescod')
                ->leftjoin('cpe_detalle as det','cpe_cabecera.IdCpe_cabecera','det.IdCpe_cabecera')
                ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
               ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                
                ->where(function ($query6) use ($almacen) {
                  if($almacen !=0 && !empty($almacen)){
                      $query6->where('cpe_cabecera.id_almacen',$almacen);
                  }                
                }) 
                ->where(function ($query1){
          
                    $query1->whereNull('cpe_cabecera.ccabaj')
                          ->orwhere('cpe_cabecera.ccabaj','');
                    
                })
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin) 
                  ->where(function ($query) {
                $query->where('cpe_cabecera.tdocod','01')
                    ->orWhere('cpe_cabecera.tdocod','03')
                    ->orWhere('cpe_cabecera.tdocod','13');
                })                
                ->sum('cdevve');

                 $totalcosto = cpe_cabecera::select('cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','cdepuni as precio','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','det.cdecan as cantidad','det.cdedes as producto','monnom as moneda','det.cdevve as total','det.costo as costo','cpe_cabecera.ccabaj as baja','ccasunrescod','det.flete')
                ->leftjoin('cpe_detalle as det','cpe_cabecera.IdCpe_cabecera','det.IdCpe_cabecera')
                ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
               ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                
                ->where(function ($query6) use ($almacen) {
                  if($almacen !=0 && !empty($almacen)){
                      $query6->where('cpe_cabecera.id_almacen',$almacen);
                  }                
                }) 
                ->where(function ($query1){
          
                    $query1->whereNull('cpe_cabecera.ccabaj')
                          ->orwhere('cpe_cabecera.ccabaj','');
                    
                })
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin) 
                  ->where(function ($query) {
                $query->where('cpe_cabecera.tdocod','01')
                    ->orWhere('cpe_cabecera.tdocod','03')
                    ->orWhere('cpe_cabecera.tdocod','13');
                })                
                ->sum(DB::RAW('det.cdecan*(det.costo+det.flete)'));

               

                $vista = view('empresas.reportes.rentabilidad',compact('totalventas','totalcosto','comprobantes','fecin','fecfin'))->render();


                    if($request->ajax()){
                 return response()->json(['vista'=>$vista]);

              }


            break;

            case 5:



              
              /*  $registros = DB::tABLE('cpe_detalle')->get();
              foreach ($registros as $reg) {
                DB::tABLE('cpe_detalle')->where('IdCpe_detalle',$reg->IdCpe_detalle)->update(['procod'=>trim($reg->procod)]);
              }*/


              $empresa = DB::tABLE('empresa')->where('IdEmpresa',Auth::user()->IdEmpresa)->first();

             // $sucursal = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first();
             // $datocli = DB::tABLE('cliente')->where('clicod',$clicod)->first();

                $productos = cpe_cabecera::select(DB::RAW('sum(cpe_detalle.cdecan*cpe_det_factor) as cantidad'),'cdedes',DB::RAW('sum(cpe_detalle.cdevve) as precio'),'cpe_detalle.procod','umecod')
                ->leftjoin('cpe_detalle','cpe_detalle.IdCpe_cabecera','cpe_cabecera.IdCpe_cabecera')
 
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                ->whereNull('cpe_cabecera.ccabaj')
                ->where('cdecan','>','0')
                 ->where(function ($query) {
                  $query->Where('cpe_cabecera.tdocod','03')
                        ->orWhere('cpe_cabecera.tdocod','01')
                        ->orWhere('cpe_cabecera.tdocod','13');
                      
                  })
               
                ->where(function ($query5) use ($sucursal) {
                  if($sucursal !='Todos'){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                
                ->where(function ($query6) use ($almacen) {
                  if($almacen !='Todos'){
                      $query6->where('cpe_detalle.id_almacen_pro',$almacen);
                  }                
                }) 
                ->orderby('cdedes','desc')
                ->groupby('procod')
                ->get();

                $totalproductos = $productos->sum('cantidad');

                $total =0;
                foreach ($productos as $pro) {
                   $total = $total + ($pro->precio);
                }


                $vista = view('empresas.reportes.productos', compact('productos','fecin','fecfin','total','totalproductos','total','empresa','sucursal','data_sucursal'))->render();


              if($request->ajax()){
                 return response()->json(['vista'=>$vista]);

              }

                 
              break;

               case 6:


                $comprobantes = cpe_cabecera::select('cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','det.cdedes as producto','det.cdecan as cantidad','cdepuni as precio','monnom as moneda','det.cdevve as total','cpe_cabecera.ccabaj as baja','det.costo','ccasunrescod','tdides as documentoidentidad','ccandi as numerodocumento','IdProducto','cpe_det_factor')
                ->leftjoin('cpe_detalle as det','cpe_cabecera.IdCpe_cabecera','det.IdCpe_cabecera')
                ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
                 ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                
                ->where(function ($query6) use ($almacen) {
                  if($almacen !='Todos'){
                      $query6->where('det.id_almacen_pro',$almacen);
                  }                
                }) 
                
                
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
               ->whereNull('cpe_cabecera.ccabaj')
                ->where('cdecan','>','0')
                 ->where(function ($query) {
                  $query->Where('cpe_cabecera.tdocod','03')
                        ->orWhere('cpe_cabecera.tdocod','01')
                        ->orWhere('cpe_cabecera.tdocod','13');
                      
                  })
               
                ->where(function ($query4) use ($pro) {

                if($pro !=0){
                   $query4->where('det.IdProducto',$pro)
                    ->orWhere('det.IdProducto_rel',$pro);
                }
               

                })    
                 ->orderBy('IdCpe_cabecera','desc')->get();

                $total = $comprobantes->sum('total');


                $vista = view('empresas.reportes.ventas_productos',compact('fecin','fecfin','comprobantes','total','dat_ven','dat_cli'))->render();

                    if($request->ajax()){
                 return response()->json(['vista'=>$vista]);

              }


            break;

             case 7:

                $comprobantes = cpe_cabecera::select('ccaitv','cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','monnom as moneda','ccaitv as total','cpe_cabecera.ccabaj as baja','ccasunrescod','tdides as documentoidentidad','ccandi as numerodocumento','cpe_cabecera.tdocod')
                ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
                 ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                
                ->where(function ($query6) use ($almacen) {
                  if($almacen !=0 && !empty($almacen)){
                      $query6->where('cpe_cabecera.id_almacen',$almacen);
                  }                
                }) 
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                ->where(function ($query){
                    $query->Where('cpe_cabecera.tdocod','15');
                })
                ->where(function ($query1) use ($vendedor) {
                      if($vendedor !='Todos'){
                         $query1->Where('cpe_cabecera.IdUsuario_ven',$vendedor);
                      }
                    })
                 ->where(function ($query2) use ($cliente) {
                      if($cliente !='Todos'){
                         $query2->Where('cpe_cabecera.clicod',$cliente);
                      }
                    })
                ->orderBy('IdCpe_cabecera','desc')->get();

            //   dd($comprobantes);

                  $total = $comprobantes->sum('ccaitv');

                $vista = view('empresas.reportes.proformas',compact('dat_cli','fecin','fecfin','comprobantes','total','dat_ven'))->render();

              if($request->ajax()){
                 return response()->json(['vista'=>$vista]);

              }


            break;

             case 8:

                $comprobantes = cpe_cabecera::select('ccaitv','cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','monnom as moneda','ccaitv as total','cpe_cabecera.ccabaj as baja','ccasunrescod','tdides as documentoidentidad','ccandi as numerodocumento','cpe_cabecera.tdocod')
                ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
                 ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                
                ->where(function ($query6) use ($almacen) {
                  if($almacen !=0 && !empty($almacen)){
                      $query6->where('cpe_cabecera.id_almacen',$almacen);
                  }                
                }) 
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                ->where(function ($query){
                    $query->Where('cpe_cabecera.tdocod','16');
                })
                ->where(function ($query1) use ($vendedor) {
                      if($vendedor !='Todos'){
                         $query1->Where('cpe_cabecera.IdUsuario_ven',$vendedor);
                      }
                    })
                 ->where(function ($query2) use ($cliente) {
                      if($cliente !='Todos'){
                         $query2->Where('cpe_cabecera.clicod',$cliente);
                      }
                    })
                ->orderBy('IdCpe_cabecera','desc')->get();

            //   dd($comprobantes);

                  $total = $comprobantes->sum('ccaitv');

                $vista = view('empresas.reportes.pedidos',compact('dat_cli','fecin','fecfin','comprobantes','total','dat_ven'))->render();

              if($request->ajax()){
                 return response()->json(['vista'=>$vista]);

              }


            break;

                case 9:

                
              
            
                 $comprobantes = cpe_cabecera::select(DB::RAW('sum(det.cdevve) as TOTAL'),DB::RAW('sum(det.cdecan*factor) as CANTIDAD'),'productos.pronom',DB::RAW('sum(det.cdevve*(det.comision/100)) as COMISION'))
                ->leftjoin('cpe_detalle as det','cpe_cabecera.IdCpe_cabecera','det.IdCpe_cabecera')
                ->leftjoin('productos','productos.IdProducto','det.IdProducto_rel')
                ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
               ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                
                ->where(function ($query6) use ($almacen) {
                  if($almacen !=0 && !empty($almacen)){
                      $query6->where('cpe_cabecera.id_almacen',$almacen);
                  }                
                }) 
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                ->where(function ($query) use ($vendedor) {
                      if($vendedor !='Todos'){
                         $query->Where('cpe_cabecera.IdUsuario_ven',$vendedor);
                      }
                    })
                   ->where(function ($query2) use ($pro) {
                      if($pro !=0){
                         $query2->Where('IdProducto_rel',$pro);
                      }
                    })
                ->where(function ($query1){
          
                    $query1->whereNull('cpe_cabecera.ccabaj')
                          ->orwhere('cpe_cabecera.ccabaj','');
                    
                })
                ->where(function ($query3) {
                $query3->where('cpe_cabecera.tdocod','01')
                    ->orWhere('cpe_cabecera.tdocod','03')
                    ->orWhere('cpe_cabecera.tdocod','13');
                })   
                ->groupby('IdProducto_rel') 
                 ->orderBy('cpe_cabecera.IdCpe_cabecera','desc')->get();

                 //dd($comprobantes);

                  $total = $comprobantes->sum('COMISION');

                    $vista = view('empresas.reportes.comision_vendedor',compact('fecin','fecfin','comprobantes','total','dat_ven','dat_cli'))->render();


                    if($request->ajax()){
                 return response()->json(['vista'=>$vista]);

              }


            break;

                 case 10:

                $comprobantes = cpe_cabecera::select(DB::RAW('sum(det.cdevve) as TOTAL'),DB::RAW('sum(det.cdecan*factor) as CANTIDAD'),'productos.pronom',DB::RAW('sum(det.cdevve*(det.comision/100)) as COMISION'))
                ->leftjoin('cpe_detalle as det','cpe_cabecera.IdCpe_cabecera','det.IdCpe_cabecera')
                ->leftjoin('productos','productos.IdProducto','det.IdProducto_rel')
                ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
               ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                
                ->where(function ($query6) use ($almacen) {
                  if($almacen !=0 && !empty($almacen)){
                      $query6->where('det.id_almacen_pro',$almacen);
                  }                
                }) 
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                ->where(function ($query) use ($vendedor) {
                      if($vendedor !='0'){
                         $query->Where('cpe_cabecera.IdUsuario_ven',$vendedor);
                      }
                    })
             
                ->where(function ($query1){
          
                    $query1->whereNull('cpe_cabecera.ccabaj')
                          ->orwhere('cpe_cabecera.ccabaj','');
                    
                })
                ->where(function ($query3) {
                $query3->where('cpe_cabecera.tdocod','01')
                    ->orWhere('cpe_cabecera.tdocod','03')
                    ->orWhere('cpe_cabecera.tdocod','13');
                })   
                ->groupby('IdProducto_rel') 
                 ->orderBy('cpe_cabecera.IdCpe_cabecera','desc')->get();

                 //dd($comprobantes);

                  $total = $comprobantes->sum('COMISION');


                $vista = view('empresas.reportes.comision_vendedor',compact('fecin','fecfin','comprobantes','total','dat_ven','dat_cli'))->render();

                    if($request->ajax()){
                 return response()->json(['vista'=>$vista]);

              }


            break;


                case 11:

                 $comprobantes = DB::tABLE('compras_cabecera')
                  ->leftjoin('proveedor','compras_cabecera.prov_id','proveedor.prov_id')
                  ->join('moneda','compras_cabecera.mon_id','moneda.moncod')
                  ->join('tipo_documento','compras_cabecera.tdocod','tipo_documento.tdocod')
                  
                    ->where('compras_cabecera.com_fec','>=',$fecin)
                    ->where('compras_cabecera.com_fec','<=',$fecfin)
                  ->where('est_compra','Registrado')
                    ->where(function ($query) use ($sucursal){
                      if(!$sucursal !=0 && !empty($sucursal)){
                          $query->where('compras_cabecera.id_empresa_negocio',$sucursal);
                      }
                     
                  })
                  ->where(function ($query1) use ($proveedor){
                      if($proveedor !=0){
                          $query1->where('compras_cabecera.prov_id',$proveedor);
                      }
                      
                  })
                        
                  ->where(function ($query6) use ($almacen) {
                      if($almacen !=0 && !empty($almacen)){
                          $query6->where('compras_cabecera.id_almacen',$almacen);
                      }                
                    }) 
                
                  ->where('compras_cabecera.tdocod','!=','80')
                  ->orderby('com_cab_id','desc')
                  ->get();
              //   dd($comprobantes);

                  $total = $comprobantes->sum('total_com');


                $vista = view('empresas.reportes.compras',compact('fecin','fecfin','comprobantes','total','dat_ven','dat_cli'))->render();

                    if($request->ajax()){
                 return response()->json(['vista'=>$vista]);

              }


            break;

              case 12:

              $comprobantes = DB::tABLE('compras_cabecera')
              ->leftjoin('compras_detalle','compras_detalle.com_cab_id','compras_cabecera.com_cab_id')
                 ->leftjoin('productos','productos.IdProducto','compras_detalle.pro_id')
                  ->leftjoin('proveedor','compras_cabecera.prov_id','proveedor.prov_id')
                  ->leftjoin('unidad_medida','unidad_medida.umecod','compras_detalle.ume_cod')
                  ->join('moneda','compras_cabecera.mon_id','moneda.moncod')
                  ->join('tipo_documento','compras_cabecera.tdocod','tipo_documento.tdocod')
              ->where('est_compra','Registrado')
                ->where('compras_cabecera.com_fec','>=',$fecin)
                ->where('compras_cabecera.com_fec','<=',$fecfin)
                  ->where(function ($query) use ($sucursal){
                      if(!$sucursal !=0 && !empty($sucursal)){
                          $query->where('compras_cabecera.id_empresa_negocio',$sucursal);
                      }
                     
                  })
                  ->where(function ($query1) use ($proveedor){
                      if($proveedor !=0){
                          $query1->where('compras_cabecera.prov_id',$proveedor);
                      }
                      
                  })
                        
                  ->where(function ($query6) use ($almacen) {
                      if($almacen !=0 && !empty($almacen)){
                          $query6->where('compras_cabecera.id_almacen',$almacen);
                      }                
                    }) 
              ->where('compras_cabecera.tdocod','!=','80')
              ->orderby('compras_cabecera.com_cab_id','desc')
              ->get();
                 //dd($comprobantes);

                  $total = $comprobantes->sum('total_com');


                $vista = view('empresas.reportes.compras_detalladas',compact('fecin','fecfin','comprobantes','total','dat_ven','dat_cli'))->render();

                    if($request->ajax()){
                 return response()->json(['vista'=>$vista]);

              }


            break;

              case 13:

              $comprobantes = DB::tABLE('compras_cabecera')
              ->leftjoin('compras_detalle','compras_detalle.com_cab_id','compras_cabecera.com_cab_id')
                 ->leftjoin('productos','productos.IdProducto','compras_detalle.pro_id')
                  ->leftjoin('proveedor','compras_cabecera.prov_id','proveedor.prov_id')
                  ->leftjoin('unidad_medida','unidad_medida.umecod','compras_detalle.ume_cod')
                  ->join('moneda','compras_cabecera.mon_id','moneda.moncod')
                  ->join('tipo_documento','compras_cabecera.tdocod','tipo_documento.tdocod')
              ->where('est_compra','Registrado')
                ->where('compras_cabecera.com_fec','>=',$fecin)
                ->where('compras_cabecera.com_fec','<=',$fecfin)
                  ->where(function ($query) use ($sucursal){
                      if(!$sucursal !=0 && !empty($sucursal)){
                          $query->where('compras_cabecera.id_empresa_negocio',$sucursal);
                      }
                     
                  })
                  ->where(function ($query1) use ($proveedor){
                      if($proveedor !=0){
                          $query1->where('compras_cabecera.prov_id',$proveedor);
                      }
                      
                  })

                   ->where(function ($query8) use ($pro){
                      if($pro !=0){
                          $query8->where('compras_detalle.IdProducto_rel',$pro);
                      }
                      
                  })
                        
                  ->where(function ($query6) use ($almacen) {
                      if($almacen !=0 && !empty($almacen)){
                          $query6->where('compras_cabecera.id_almacen',$almacen);
                      }                
                    }) 
              ->where('compras_cabecera.tdocod','!=','80')
              ->orderby('compras_cabecera.com_cab_id','desc')
              ->get();
                 //dd($comprobantes);

                  $total = $comprobantes->sum('total_com');


                $vista = view('empresas.reportes.compras_detalladas',compact('fecin','fecfin','comprobantes','total','dat_ven','dat_cli'))->render();

                    if($request->ajax()){
                 return response()->json(['vista'=>$vista]);

              }

              break;

               case 14:

                $comprobantes = cpe_cabecera::select('ccaitv','cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','monnom as moneda','ccaitv as total','cpe_cabecera.ccabaj as baja','ccasunrescod','tdides as documentoidentidad','ccandi as numerodocumento','cpe_cabecera.tdocod','pedidos.fecha_hora','pedidos.fecha_salida','pedidos.placa',DB::raw("(SELECT CONCAT(name,apeusu) FROM users WHERE IdUsuario= pedidos.IdUsuario) as usuario_registra"),DB::raw("(SELECT CONCAT(name,apeusu) FROM users WHERE IdUsuario= pedidos.IdUsuarioCob) as usuario_cobra"))
                ->join('pedidos','pedidos.ped_id','cpe_cabecera.ped_id')
                ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
                 ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                
                ->where(function ($query6) use ($almacen) {
                  if($almacen !=0 && !empty($almacen)){
                      $query6->where('cpe_cabecera.id_almacen',$almacen);
                  }                
                }) 
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                ->where(function ($query){
                    $query->where('cpe_cabecera.tdocod','01')
                          ->orwhere('cpe_cabecera.tdocod','03')
                          ->orWhere('cpe_cabecera.tdocod','13');
                })
                ->where(function ($query1) use ($vendedor) {
                      if($vendedor !='Todos'){
                         $query1->Where('cpe_cabecera.IdUsuario_ven',$vendedor);
                      }
                    })
                 ->where(function ($query2) use ($cliente) {
                      if($cliente !='Todos'){
                         $query2->Where('cpe_cabecera.clicod',$cliente);
                      }
                    })
                ->orderBy('IdCpe_cabecera','desc')->get();

            //   dd($comprobantes);

                  $total = $comprobantes->sum('ccaitv');

                $vista = view('empresas.reportes.ventas_ingreso_vehiculos',compact('dat_cli','fecin','fecfin','comprobantes','total','dat_ven'))->render();

              if($request->ajax()){
                 return response()->json(['vista'=>$vista]);

              }


            break;

              case 15:

                $comprobantes = cpe_cabecera::select('ccaitv','cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','monnom as moneda','ccaitv as total','cpe_cabecera.ccabaj as baja','ccasunrescod','tdides as documentoidentidad','ccandi as numerodocumento','cpe_cabecera.tdocod','pedidos.fecha_hora','pedidos.fecha_salida','pedidos.placa',DB::raw("(SELECT CONCAT(name,apeusu) FROM users WHERE IdUsuario= pedidos.IdUsuario) as usuario_registra"),DB::raw("(SELECT CONCAT(name,apeusu) FROM users WHERE IdUsuario= pedidos.IdUsuarioCob) as usuario_cobra"))
                ->join('pedidos','pedidos.ped_id','cpe_cabecera.ped_id')
                ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
                 ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                
                ->where(function ($query6) use ($almacen) {
                  if($almacen !=0 && !empty($almacen)){
                      $query6->where('cpe_cabecera.id_almacen',$almacen);
                  }                
                }) 
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                ->where(function ($query){
                    $query->Where('cpe_cabecera.tdocod','50');
                })
                ->where(function ($query1) use ($vendedor) {
                      if($vendedor !='Todos'){
                         $query1->Where('cpe_cabecera.IdUsuario_ven',$vendedor);
                      }
                    })
                 ->where(function ($query2) use ($cliente) {
                      if($cliente !='Todos'){
                         $query2->Where('cpe_cabecera.clicod',$cliente);
                      }
                    })
                ->orderBy('IdCpe_cabecera','desc')->get();

            //   dd($comprobantes);

                  $total = $comprobantes->sum('ccaitv');

                $vista = view('empresas.reportes.ventas_ingreso_vehiculos',compact('dat_cli','fecin','fecfin','comprobantes','total','dat_ven'))->render();

              if($request->ajax()){
                 return response()->json(['vista'=>$vista]);

              }


            break;

               case 16:

                $comprobantes = cpe_cabecera::select('ccaitv','cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','monnom as moneda','ccaitv as total','cpe_cabecera.ccabaj as baja','ccasunrescod','tdides as documentoidentidad','ccandi as numerodocumento','cpe_cabecera.tdocod','pedidos.fecha_hora','pedidos.fecha_salida','pedidos.placa',DB::raw("(SELECT CONCAT(name,apeusu) FROM users WHERE IdUsuario= pedidos.IdUsuario) as usuario_registra"),DB::raw("(SELECT CONCAT(name,apeusu) FROM users WHERE IdUsuario= pedidos.IdUsuarioCob) as usuario_cobra"))
                ->join('pedidos','pedidos.ped_id','cpe_cabecera.ped_id')
                ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
                 ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                
                ->where(function ($query6) use ($almacen) {
                  if($almacen !=0 && !empty($almacen)){
                      $query6->where('cpe_cabecera.id_almacen',$almacen);
                  }                
                }) 
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                ->where(function ($query){
                    $query->where('cpe_cabecera.tdocod','01')
                          ->orwhere('cpe_cabecera.tdocod','03')
                          ->orWhere('cpe_cabecera.tdocod','13')
                           ->orWhere('cpe_cabecera.tdocod','50');
                })
                ->where(function ($query1) use ($vendedor) {
                      if($vendedor !='Todos'){
                         $query1->Where('cpe_cabecera.IdUsuario_ven',$vendedor);
                      }
                    })
                 ->where(function ($query2) use ($cliente) {
                      if($cliente !='Todos'){
                         $query2->Where('cpe_cabecera.clicod',$cliente);
                      }
                    })
                ->orderBy('IdCpe_cabecera','desc')->get();

            //   dd($comprobantes);

                  $total = $comprobantes->sum('ccaitv');

                $vista = view('empresas.reportes.ventas_ingreso_vehiculos',compact('dat_cli','fecin','fecfin','comprobantes','total','dat_ven'))->render();

              if($request->ajax()){
                 return response()->json(['vista'=>$vista]);

              }


            break;

            case 17:

              $comprobantes = cpe_cabecera::select('ccaitv','cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','monnom as moneda','ccaitv as total','cpe_cabecera.ccabaj as baja','ccasunrescod','tdides as documentoidentidad','ccandi as numerodocumento','cpe_cabecera.tdocod','ccatexo','ccatvg','ccatvi')
                ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
                 ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                
                ->where(function ($query6) use ($almacen) {
                  if($almacen !=0 && !empty($almacen)){
                      $query6->where('cpe_cabecera.id_almacen',$almacen);
                  }                
                }) 
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                ->where(function ($query){
                    $query->where('cpe_cabecera.tdocod','01')
                          ->orwhere('cpe_cabecera.tdocod','03')
                          ->orWhere('cpe_cabecera.tdocod','13')
                          ->orWhere('cpe_cabecera.tdocod','07');
                })
                ->whereNull('ccabaj')
                ->orderBy('IdCpe_cabecera','desc')->get();

            //   dd($comprobantes);

                  $total = $comprobantes->sum('ccaitv');

                  $totalexoneradasfacturas = $comprobantes->where('tdocod','01')->sum('ccatexo');
                  $totalgravadasfacturas = $comprobantes->where('tdocod','01')->sum('ccatvg');

                  $totalexoneradasboletas = $comprobantes->where('tdocod','03')->sum('ccatexo');
                  $totalgravadasboletas = $comprobantes->where('tdocod','03')->sum('ccatvg');

                   $totalexoneradasnotas = $comprobantes->where('tdocod','07')->sum('ccatexo');
                  $totalgravadasnotas = $comprobantes->where('tdocod','07')->sum('ccatvg');


            

                 $sucursal = EmpresaNegocios::FindOrFail($request->get('sucursal'));

                 $empresa = Empresa::findOrFail($sucursal->IdEmpresa);

                
                    $vista =view('empresas.reportes.ventas_resumen',compact('fecin','fecfin','total','totalexoneradasfacturas','totalexoneradasboletas','totalgravadasfacturas','totalgravadasboletas','totalgravadasnotas','totalexoneradasnotas','empresa','sucursal'))->render();


              if($request->ajax()){
                 return response()->json(['vista'=>$vista]);

              }

              break;
                


              case 19:



                $facturas = cpe_cabecera::select('ccaitv','cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','monnom as moneda','ccaitv as total','cpe_cabecera.ccabaj as baja','ccasunrescod','tdides as documentoidentidad','ccandi as numerodocumento',
                  'cpe_cabecera.tdocod','ccafve as fechaven','cpe_cabecera.tdicod','ccatexo','ccafem_ref','serie_ref','tdocod_ref','num_ref','cod_mov')
                ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
                 ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                
                ->where(function ($query6) use ($almacen) {
                  if($almacen !='Todos'){
                      $query6->where('cpe_cabecera.id_almacen',$almacen);
                  }                
                }) 
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                ->where(function ($query){
                    $query->where('cpe_cabecera.tdocod','01');
                })
                ->where(function ($query1) use ($vendedor) {
                      if($vendedor !='Todos'){
                         $query1->Where('cpe_cabecera.IdUsuario_ven',$vendedor);
                      }
                    })
                 ->where(function ($query2) use ($cliente) {
                      if($cliente !='Todos'){
                         $query2->Where('cpe_cabecera.clicod',$cliente);
                      }
                    })
                ->orderBy('IdCpe_cabecera','desc')->get();

                 $boletas = cpe_cabecera::select('ccaitv','cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','monnom as moneda','ccaitv as total','cpe_cabecera.ccabaj as baja','ccasunrescod','tdides as documentoidentidad','ccandi as numerodocumento',
                  'cpe_cabecera.tdocod','ccafve as fechaven','cpe_cabecera.tdicod','ccatexo','ccafem_ref','serie_ref','tdocod_ref','num_ref','cod_mov')
                ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
                 ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                
                ->where(function ($query6) use ($almacen) {
                  if($almacen !='Todos'){
                      $query6->where('cpe_cabecera.id_almacen',$almacen);
                  }                
                }) 
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                ->where(function ($query){
                    $query->where('cpe_cabecera.tdocod','03');
                })
                ->where(function ($query1) use ($vendedor) {
                      if($vendedor !='Todos'){
                         $query1->Where('cpe_cabecera.IdUsuario_ven',$vendedor);
                      }
                    })
                 ->where(function ($query2) use ($cliente) {
                      if($cliente !='Todos'){
                         $query2->Where('cpe_cabecera.clicod',$cliente);
                      }
                    })
                ->orderBy('IdCpe_cabecera','desc')->get();


                 $notas = cpe_cabecera::select('ccaitv','cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','monnom as moneda','ccaitv as total','cpe_cabecera.ccabaj as baja','ccasunrescod','tdides as documentoidentidad','ccandi as numerodocumento',
                  'cpe_cabecera.tdocod','ccafve as fechaven','cpe_cabecera.tdicod','ccatexo','ccafem_ref','serie_ref','tdocod_ref','num_ref','cod_mov')
                ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
                 ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                
                ->where(function ($query6) use ($almacen) {
                  if($almacen !='Todos'){
                      $query6->where('cpe_cabecera.id_almacen',$almacen);
                  }                
                }) 
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                ->where(function ($query){
                    $query->where('cpe_cabecera.tdocod','07');
                })
                ->where(function ($query1) use ($vendedor) {
                      if($vendedor !='Todos'){
                         $query1->Where('cpe_cabecera.IdUsuario_ven',$vendedor);
                      }
                    })
                 ->where(function ($query2) use ($cliente) {
                      if($cliente !='Todos'){
                         $query2->Where('cpe_cabecera.clicod',$cliente);
                      }
                    })
                ->orderBy('IdCpe_cabecera','desc')->get();

      

                          $totalfacturas = $facturas->sum('ccaitv');
                  $totalboletas = $boletas->sum('ccaitv');
                  $totalnotas = $notas->sum('ccaitv');


                  $totalfacturasexo = $facturas->sum('ccatexo');
                  $totalboletasexo = $boletas->sum('ccatexo');
                  $totalnotasexo = $notas->sum('ccatexo');
                    

                  $totalfacturasinaf = $facturas->sum('ccatvi');
                  $totalboletasinaf = $boletas->sum('ccatvi');
                  $totalnotasinaf = $notas->sum('ccatvi');
                  

                   $totalfacturasigv = $facturas->sum('ccaigv');
                  $totalboletasigv = $boletas->sum('ccaigv');
                  $totalnotasigv = $notas->sum('ccaigv');
                 
                  $totalfacturasicbper = $facturas->sum('icbper');
                  $totalboletasicbper = $boletas->sum('icbper');
                  $totalnotasicbper = $notas->sum('icbper');

                $vista = view('empresas.reportes.ventas_contador',compact('dat_cli','fecin','fecfin','facturas','boletas','notas','totalfacturas','totalboletas','totalfacturasexo','totalboletasexo','totalnotasexo','totalfacturasinaf','totalboletasinaf','totalnotasinaf','totalfacturasigv','totalboletasigv','totalnotasigv','totalfacturasicbper','totalboletasicbper','totalnotasicbper','totalnotas','dat_ven'))->render();

              if($request->ajax()){
                 return response()->json(['vista'=>$vista]);

              }


            break;

              case 50:

            // *** 1. OBTENER PARÁMETRO DE ACCIÓN (BUSCAR O IMPRIMIR) ***
            $accion = $request->input('accion', 'buscar');

            // *** 2. OBTENCIÓN DE FILTROS Y DATOS COMPLEMENTARIOS (TU LÓGICA EXISTENTE) ***
            $tipo = $request->get('promocion');
            $estado = $request->get('estado');
            $categoria = $request->get('cmbCatId');
            $sucursal = $request->get('sucursal');
            $negocios = DB::table('empresa_negocios')->get();
            $almacen = $request->get('almacen');
            $datosalm ="";

            $rucemp = trim(Auth::user()->IdEmpresa);
            $empresa = Empresa::findOrFail($rucemp);

            $buspro = trim($request->get('buspro'));
            
            $categorias = DB::table('categorias')->get();

            $tipos_productos = DB::table('tipos_productos')->get();

            $datos = DB::table('empresa_negocios')->where('id_empresa_negocio',$negocios->first()->id_empresa_negocio)->first();

            if(empty($sucursal)){
                $almacenes = DB::table('almacenes')->where('id_empresa_negocio',$negocios->first()->id_empresa_negocio)->get();
            }else{
                $almacenes = DB::table('almacenes')->where('id_empresa_negocio',$sucursal)->get();
            }
            
            // *** 3. LÓGICA DE OBTENCIÓN DE DATOS DEL STOCK (EXISTENTE - Se ejecuta siempre) ***
            $productos = DB::table('productos as p')
            ->select('producto_stock.lote','producto_stock.vencimiento','producto_stock.stock_inicial','producto_stock.stock','producto_stock.stock_equivalencia','p.procod','p.pronom','m.monnom','u.umenom','p.propun','p.proest','producto_stock.IdProducto','mar.mar_nom as marca','p.costo','cat.cat_nom as categoria','p.propun',
                DB::raw("(SELECT precio FROM producto_empresa  WHERE producto_empresa.IdProducto=p.IdProducto and id_empresa_negocio='".$sucursal."') as precio"),
                DB::raw("(SELECT precio2 FROM producto_empresa  WHERE producto_empresa.IdProducto=p.IdProducto and id_empresa_negocio='".$sucursal."') as precio2"),
                DB::raw("(SELECT precio3 FROM producto_empresa  WHERE producto_empresa.IdProducto=p.IdProducto and id_empresa_negocio='".$sucursal."') as precio3")
             )
            ->leftjoin('moneda as m','p.moncod','=','m.moncod')
             ->leftjoin('marcas as mar','mar.mar_id','=','p.marca')
            ->leftjoin('unidad_medida as u','p.umecod','=','u.umecod')
            ->leftjoin('producto_stock','producto_stock.IdProducto','p.IdProducto')
            ->leftjoin('categorias as cat', 'p.cat_id', '=', 'cat.cat_id')
            ->where(function ($query1) use($buspro) {
              if(!empty($buspro)){
                $query1->where('p.pronom','like','%'.$buspro.'%')
                       ->orwhere('p.procod',$buspro);
              }
            })
            ->where(function ($query1) use($estado) {
              if($estado=='cs'){
                $query1->where('producto_stock.stock','>','0');
              }elseif($estado=='se'){
                  $query1->where('producto_stock.stock','=','0');
              }elseif($estado=='ne'){
                  $query1->where('producto_stock.stock','<','0');
              }
            })
            ->where(function ($queryCat) use ($categoria) {
                if ($categoria != 'Todos' && !empty($categoria)) {
                    $queryCat->where('p.cat_id', $categoria); 
                }
            })
            ->where('producto_stock.id_empresa_negocio',$sucursal)
            ->where('p.tipo','1') // Se especificó 'p.'
            ->where(function ($query) {
              $query->where('p.promocion','0') // Se especificó 'p.'
                    ->orWhere('p.promocion','4');
            })
            ->where('producto_stock.id_almacen',$almacen) // Se especificó 'producto_stock.'
            ->groupby('p.IdProducto')
            ->orderby('p.pronom','asc') // Se especificó 'p.'
            ->get();

            $datosalm = DB::table('almacenes')->where('id_almacen',$almacen)->first();

            // --------------------------------------------------------------------
            // 4. LÓGICA CONDICIONAL: IMPRIMIR TÉRMICA o MOSTRAR VISTA
            // --------------------------------------------------------------------

            if ($accion == 'imprimir_termica') {
                
                // --- NUEVA LÓGICA DE IMPRESIÓN TÉRMICA ---
                try {
                    
                    // 4.1. OBTENER LA CONFIGURACIÓN DE LA IMPRESORA
                    $id_empresa_negocio = $sucursal; 

                    $impresora_caja = DB::table('configuracion_impresoras')
                                        ->where('id_empresa_negocio', $id_empresa_negocio)
                                        ->where('predeterminado', '1')
                                        ->first();

                    if (!$impresora_caja) {
                        throw new \Exception("No se encontró impresora predeterminada para el negocio.");
                    }
                    
                    // 4.2. CREAR EL CONNECTOR CON LA CORRECCIÓN DE SMB (CLAVE)
                    if ($impresora_caja->tip_conex_imp == 'COMPARTIDO') {
                        // Se usa smb:// para rutas con barra diagonal
                        $ruta_smb = "smb://" . $impresora_caja->ruta;
                        $connector = new WindowsPrintConnector($ruta_smb); 
                        
                    } elseif ($impresora_caja->tip_conex_imp == 'RED') {
                        $connector = new NetworkPrintConnector($impresora_caja->ruta, 9100);
                    } else {
                         throw new \Exception("Tipo de conexión de impresora no soportado: " . $impresora_caja->tip_conex_imp);
                    }
                    
                    $printer = new Printer($connector);
                    
                    // 4.3. CONTENIDO DE IMPRESIÓN (42 caracteres de ancho total)
                    $printer->setJustification(Printer::JUSTIFY_CENTER);
                    $printer->setTextSize(2, 2); 
                    $printer->text("REPORTE DE STOCK\n");
                    $printer->setTextSize(1, 1); 
                    $printer->text(str_repeat("-", 42) . "\n");
                    $printer->text("ALMACEN: " . ($datosalm->nom_almacen ?? 'N/A') . "\n");
                    $printer->text("FECHA: " . date('d/m/Y H:i:s') . "\n");
                    // Opcional: Imprimir categoría filtrada si se requiere en el ticket
                    $printer->text("CATEGORIA: " . ($categoria == 'Todos' ? 'TODAS' : ($productos->first()->categoria ?? 'N/A')) . "\n");
                    $printer->text(str_repeat("-", 42) . "\n");
                    
                    $printer->setJustification(Printer::JUSTIFY_LEFT);
                    
                    // Cabeceras de columna: Producto(25) | Stock(8) | Eq.(9) = 42
                    $printer->text(str_pad("PRODUCTO", 25) . str_pad("STOCK", 8, " ", STR_PAD_LEFT) . str_pad("EQ.", 9, " ", STR_PAD_LEFT) . "\n");
                    $printer->text(str_repeat("-", 42) . "\n");

                    // Datos de productos (Con alineación por str_pad)
                    foreach ($productos as $producto) {
                        // 1. Nombre: Recortar y alinear a la izquierda (25 caracteres)
                        $nombre = substr($producto->pronom, 0, 25);
                        $nombre_alineado = str_pad($nombre, 25, " "); 

                        // 2. Stock Actual: Alinear a la derecha (8 caracteres)
                        $stock = str_pad($producto->stock, 8, " ", STR_PAD_LEFT);

                        // 3. Stock Equivalencia: Alinear a la derecha (9 caracteres)
                        $equivalencia = str_pad($producto->stock_equivalencia, 9, " ", STR_PAD_LEFT);
                        
                        // 4. Imprimir la línea
                        $linea = $nombre_alineado . $stock . $equivalencia . "\n";
                        $printer->text($linea);
                    }
                    
                    // Pie de página
                    $printer->text(str_repeat("-", 42) . "\n");
                    $printer->setJustification(Printer::JUSTIFY_CENTER);
                    $printer->text("Fin del Reporte\n");
                    $printer->feed(4); 
                    $printer->cut(); 
                    
                    $printer->close();
                    
                    // Retorna éxito para la petición AJAX
                    return response()->json(['success' => true]);
                    
                } catch (\Exception $e) {
                    \Log::error("Error de Impresión Térmica (Stock): " . $e->getMessage());
                    // Retorna error para la petición AJAX
                    return response()->json(['success' => false, 'message' => 'Fallo la conexión con la impresora. Detalle: ' . $e->getMessage()], 500);
                }
                
            } else {
                // --- LÓGICA DE VISTA (EXISTENTE - Se ejecuta con btnBuscar) ---

                $vista = view('empresas.reportes.stock_productos',compact('productos','buspro','negocios','categorias','tipos_productos','categoria','tipo','sucursal','datos','almacenes','almacen','datosalm','empresa'))->render();

                if($request->ajax()){
                    return response()->json(['vista'=>$vista]);
                }
            }
            break;

            case 51:

                if($vendedor!='Todos'){
                $dato_vendedor = DB::tABLE('users')->where('IdUsuario',$vendedor)->first();
                }

                if($cliente!='Todos'){
                  $dato_cliente = DB::tABLE('cliente')->where('clicod',$cliente)->first();
                }

                $comprobantes = cpe_cabecera::select('cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','det.cdedes as producto','det.cdecan as cantidad','cdepuni as precio','monnom as moneda','det.cdevve as total','cpe_cabecera.ccabaj as baja','det.costo','ccasunrescod','tdides as documentoidentidad','ccandi as numerodocumento','procod','cdedes','cdecan','cdepuni','cdevve','umecod','ccaitv','id_almacen_pro')
                ->leftjoin('cpe_detalle as det','cpe_cabecera.IdCpe_cabecera','det.IdCpe_cabecera')
                ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                 ->where(function ($query) use ($vendedor) {
                      if($vendedor !='Todos'){
                         $query->Where('cpe_cabecera.IdUsuario_ven',$vendedor);
                      }
                    })
                ->where(function ($query) {
                  $query->where('cpe_cabecera.tdocod','01')
                    ->orWhere('cpe_cabecera.tdocod','03')
                    ->orWhere('cpe_cabecera.tdocod','13')
                    ->orWhere('cpe_cabecera.tdocod','14')
                    //->orWhere('cpe_cabecera.tdocod','07')
                    ;
                  }) 
                  ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                   ->where(function ($query2) use ($cliente) {
                      if($cliente !='Todos'){
                         $query2->Where('cpe_cabecera.clicod',$cliente);
                      }
                    })
                 ->whereNotNull('ccabaj')
                 ->orderBy('IdCpe_cabecera','desc')->get();




                 $cabecera = DB::tABLE('cpe_cabecera')
             //    ->join('cpe_detalle','cpe_detalle.IdCpe_cabecera','cpe_cabecera.IdCpe_cabecera')
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                 ->where(function ($query) use ($vendedor){
                      if($vendedor !='Todos'){
                         $query->Where('cpe_cabecera.IdUsuario_ven',$vendedor);
                      }
                    })
                ->where(function ($query) {
                  $query->where('cpe_cabecera.tdocod','01')
                    ->orWhere('cpe_cabecera.tdocod','03')
                    ->orWhere('cpe_cabecera.tdocod','13')
                    ->orWhere('cpe_cabecera.tdocod','14')
                   // ->orWhere('cpe_cabecera.tdocod','07')
                    ;
                  }) 
 
                ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                 ->where(function ($query2) use ($cliente) {
                      if($cliente !='Todos'){
                         $query2->Where('cpe_cabecera.clicod',$cliente);
                      }
                    })
                ->whereNotNull('ccabaj')
                 ->get();

                 $total = $cabecera->where('ccanot','')->sum('ccaitv');

                 $totalnotas = $cabecera->where('tdocod','7')->sum('ccaitv');

                 $totalefectivo = $cabecera->where('ccanot','')->sum('totalcontado');
                 $totalcredito = $cabecera->where('ccanot','')->sum('totalcredito');

                $vista = view('empresas.reportes.ventas_anuladas', compact('totalnotas','total','dato_vendedor','cabecera','comprobantes','fecin','fecfin','empresa','sucursal','almacen','data_sucursal','dato_cliente','totalefectivo','totalcredito'))->render();

              if($request->ajax()){
                 return response()->json(['vista'=>$vista]);

              }


            break;

            case 52:

                if($vendedor!='Todos'){
                $dato_vendedor = DB::tABLE('users')->where('IdUsuario',$vendedor)->first();
                }

                if($cliente!='Todos'){
                  $dato_cliente = DB::tABLE('cliente')->where('clicod',$cliente)->first();
                }

                $comprobantes = cpe_cabecera::select('cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','det.cdedes as producto','det.cdecan as cantidad','cdepuni as precio','monnom as moneda','det.cdevve as total','cpe_cabecera.ccabaj as baja','det.costo','ccasunrescod','tdides as documentoidentidad','ccandi as numerodocumento','procod','cdedes','cdecan','cdepuni','cdevve','umecod','ccaitv','id_almacen_pro')
                ->leftjoin('cpe_detalle as det','cpe_cabecera.IdCpe_cabecera','det.IdCpe_cabecera')
                ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                 ->where(function ($query) use ($vendedor) {
                      if($vendedor !='Todos'){
                         $query->Where('cpe_cabecera.IdUsuario_ven',$vendedor);
                      }
                    })
                ->where(function ($query) {
                  $query->where('cpe_cabecera.tdocod','16')
                    ;
                  }) 
                  ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                   ->where(function ($query2) use ($cliente) {
                      if($cliente !='Todos'){
                         $query2->Where('cpe_cabecera.clicod',$cliente);
                      }
                    })
                 ->whereNotNull('ccabaj')
                 ->orderBy('IdCpe_cabecera','desc')->get();




                 $cabecera = DB::tABLE('cpe_cabecera')
             //    ->join('cpe_detalle','cpe_detalle.IdCpe_cabecera','cpe_cabecera.IdCpe_cabecera')
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                 ->where(function ($query) use ($vendedor){
                      if($vendedor !='Todos'){
                         $query->Where('cpe_cabecera.IdUsuario_ven',$vendedor);
                      }
                    })
                ->where(function ($query) {
                  $query->where('cpe_cabecera.tdocod','16')
                   // ->orWhere('cpe_cabecera.tdocod','07')
                    ;
                  }) 
                ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                 ->where(function ($query2) use ($cliente) {
                      if($cliente !='Todos'){
                         $query2->Where('cpe_cabecera.clicod',$cliente);
                      }
                    })
                ->whereNotNull('ccabaj')
                 ->get();

                 $total = $cabecera->where('ccanot','')->sum('ccaitv');

                 $totalnotas = $cabecera->where('tdocod','7')->sum('ccaitv');

                 $totalefectivo = $cabecera->where('ccanot','')->sum('totalcontado');
                 $totalcredito = $cabecera->where('ccanot','')->sum('totalcredito');

                $vista = view('empresas.reportes.pedidos_anulados', compact('totalnotas','total','dato_vendedor','cabecera','comprobantes','fecin','fecfin','empresa','sucursal','almacen','data_sucursal','dato_cliente','totalefectivo','totalcredito'))->render();

              if($request->ajax()){
                 return response()->json(['vista'=>$vista]);

              }


            break;



             case 53:

              $comprobantes = compras_cabecera::select('total_com','com_cab_id','com_fec as fecha','tdodes as comprobante','com_doc_ser as serie','com_doc_num as numero','prov_raz as cliente','monnom as moneda','total_com as total','tdides as documentoidentidad','prov_ruc as numerodocumento','compras_cabecera.tdocod')
                ->leftjoin('moneda as m','compras_cabecera.mon_id','=','m.moncod')
                ->leftjoin('proveedor as cl','compras_cabecera.prov_id','=','cl.prov_id')
               //->leftjoin('empresa as e','compras_cabecera.IdEmpresa','=','e.IdEmpresa')
                ->leftjoin('tipo_documento as tip_d','compras_cabecera.tdocod','=','tip_d.tdocod')
                ->leftjoin('tipo_documento_identidad as tdi','cl.tdicod','=','tdi.tdicod')
                 ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('compras_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                
                ->where(function ($query6) use ($almacen) {
                  if($almacen !=0 && !empty($almacen)){
                      $query6->where('compras_cabecera.id_almacen',$almacen);
                  }                
                }) 
                ->where('compras_cabecera.com_fec','>=',$fecin)
                ->where('compras_cabecera.com_fec','<=',$fecfin)
                /*->where(function ($query){
                    $query->where('compras_cabecera.tdocod','01')
                          ->orwhere('compras_cabecera.tdocod','03')
                          ->orWhere('compras_cabecera.tdocod','13')
                          ->orWhere('compras_cabecera.tdocod','07');
                })*/
                ->where('est_compra','Registrado')
                ->orderBy('com_cab_id','desc')->get();

            //  dd($comprobantes);

                  $total = $comprobantes->sum('total_com');

                  $totalexoneradasfacturas = $comprobantes->where('tdocod','01')->sum('total_com');
                  $totalgravadasfacturas = $comprobantes->where('tdocod','01')->sum('ccatvg');

                  $totalexoneradasboletas = $comprobantes->where('tdocod','03')->sum('total_com');
                  $totalgravadasboletas = $comprobantes->where('tdocod','03')->sum('ccatvg');

                  $totalexoneradasnotas = $comprobantes->where('tdocod','07')->sum('total_com');
                  $totalgravadasnotas = $comprobantes->where('tdocod','07')->sum('ccatvg');


            

                 $sucursal = EmpresaNegocios::FindOrFail($request->get('sucursal'));

                 $empresa = Empresa::findOrFail($sucursal->IdEmpresa);

                
                    $vista =view('empresas.reportes.compras_resumen',compact('fecin','fecfin','total','totalexoneradasfacturas','totalexoneradasboletas','totalgravadasfacturas','totalgravadasboletas','totalgravadasnotas','totalexoneradasnotas','empresa','sucursal'))->render();


              if($request->ajax()){
                 return response()->json(['vista'=>$vista]);

              }

              break;
                


              case 54:



                $comprobantes = compras_cabecera::select('cod_mov','total_com','compras_cabecera.com_cab_id','com_fec as fecha','tdodes as comprobante','compras_cabecera.com_doc_ser as serie','compras_cabecera.com_doc_num as numero','prov_raz as cliente','monnom as moneda','total_com as total','tdides as documentoidentidad','prov_ruc as numerodocumento',
                  'compras_cabecera.tdocod','com_fec_ven as fechaven','cl.tdicod','total_com as ccatexo')
                ->leftjoin('moneda as m','compras_cabecera.mon_id','=','m.moncod')
                ->leftjoin('proveedor as cl','compras_cabecera.prov_id','=','cl.prov_id')
                ->leftjoin('empresa as e','compras_cabecera.IdEmpresa','=','e.IdEmpresa')
                ->leftjoin('tipo_documento as tip_d','compras_cabecera.tdocod','=','tip_d.tdocod')
                ->leftjoin('tipo_documento_identidad as tdi','tdi.tdicod','=','cl.tdicod')
                 ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('compras_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                
                ->where(function ($query6) use ($almacen) {
                  if($almacen !='Todos'){
                      $query6->where('compras_cabecera.id_almacen',$almacen);
                  }                
                }) 
                ->where('compras_cabecera.com_fec','>=',$fecin)
                ->where('compras_cabecera.com_fec','<=',$fecfin)
                /* ->where(function ($query){
                    $query->where('compras_cabecera.tdocod','01')
                          ->orwhere('compras_cabecera.tdocod','03')
                          ->orWhere('compras_cabecera.tdocod','13');
                })
               ->where(function ($query1) use ($vendedor) {
                      if($vendedor !='Todos'){
                         $query1->Where('compras_cabecera.IdUsuario_ven',$vendedor);
                      }
                    })*/
                 /*->where(function ($query2) use ($cliente) {
                      if($cliente !='Todos'){
                         $query2->Where('compras_cabecera.clicod',$cliente);
                      }
                    })*/
                ->orderBy('com_fec','desc')->get();

      

                  $total = $comprobantes->sum('total_com');

                $vista = view('empresas.reportes.compras_contador',compact('dat_cli','fecin','fecfin','comprobantes','total','dat_ven'))->render();

              if($request->ajax()){
                 return response()->json(['vista'=>$vista]);

              }


            break;

              case 55:

              $comprobantes = DB::tABLE('compras_cabecera')->select('compras_detalle.vencimiento','compras_detalle.lote','pronom')
              ->leftjoin('proveedor','compras_cabecera.prov_id','proveedor.prov_id')
              ->leftjoin('compras_detalle','compras_cabecera.com_cab_id','compras_detalle.com_cab_id')
              ->leftjoin('productos','productos.IdProducto','compras_detalle.pro_id')
              ->join('moneda','compras_cabecera.mon_id','moneda.moncod')
              ->join('tipo_documento','compras_cabecera.tdocod','tipo_documento.tdocod')
              
              ->where('est_compra','Registrado')
                ->where('compras_cabecera.com_fec','>=',$fecin)
                ->where('compras_cabecera.com_fec','<=',$fecfin)
                  ->where(function ($query) use ($sucursal){
                      if(!$sucursal !=0 && !empty($sucursal)){
                          $query->where('compras_cabecera.id_empresa_negocio',$sucursal);
                      }
                     
                  })
                  ->where(function ($query1) use ($proveedor){
                      if($proveedor !=0){
                          $query1->where('compras_cabecera.prov_id',$proveedor);
                      }
                      
                  })

                   ->where(function ($query8) use ($pro){
                      if($pro !=0){
                          $query8->where('compras_detalle.IdProducto_rel',$pro);
                      }
                      
                  })
                        
                  ->where(function ($query6) use ($almacen) {
                      if($almacen !=0 && !empty($almacen)){
                          $query6->where('compras_cabecera.id_almacen',$almacen);
                      }                
                    }) 
              ->where('compras_cabecera.tdocod','!=','80')
              ->groupby('pro_id','compras_detalle.vencimiento')
              ->orderby('compras_cabecera.com_cab_id','desc')
              ->get();
                 //dd($comprobantes);

                  $total = $comprobantes->sum('total_com');


                $vista = view('empresas.reportes.compras_vencimiento',compact('fecin','fecfin','comprobantes','total','dat_ven','dat_cli'))->render();

                    if($request->ajax()){
                 return response()->json(['vista'=>$vista]);

              }

              break;


              case 17:

              $comprobantes = cpe_cabecera::select('ccaitv','cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','monnom as moneda','ccaitv as total','cpe_cabecera.ccabaj as baja','ccasunrescod','tdides as documentoidentidad','ccandi as numerodocumento','cpe_cabecera.tdocod','ccatexo','ccatvg','ccatvi')
                ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
                 ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                
                ->where(function ($query6) use ($almacen) {
                  if($almacen !=0 && !empty($almacen)){
                      $query6->where('cpe_cabecera.id_almacen',$almacen);
                  }                
                }) 
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                ->where(function ($query){
                    $query->where('cpe_cabecera.tdocod','01')
                          ->orwhere('cpe_cabecera.tdocod','03')
                          ->orWhere('cpe_cabecera.tdocod','13')
                          ->orWhere('cpe_cabecera.tdocod','07');
                })
                ->whereNull('ccabaj')
                ->orderBy('IdCpe_cabecera','desc')->get();

            //   dd($comprobantes);

                  $total = $comprobantes->sum('ccaitv');

                  $totalexoneradasfacturas = $comprobantes->where('tdocod','01')->sum('ccatexo');
                  $totalgravadasfacturas = $comprobantes->where('tdocod','01')->sum('ccatvg');

                  $totalexoneradasboletas = $comprobantes->where('tdocod','03')->sum('ccatexo');
                  $totalgravadasboletas = $comprobantes->where('tdocod','03')->sum('ccatvg');

                   $totalexoneradasnotas = $comprobantes->where('tdocod','07')->sum('ccatexo');
                  $totalgravadasnotas = $comprobantes->where('tdocod','07')->sum('ccatvg');


            

                 $sucursal = EmpresaNegocios::FindOrFail($request->get('sucursal'));

                 $empresa = Empresa::findOrFail($sucursal->IdEmpresa);

                
                    $vista =view('empresas.reportes.ventas_resumen',compact('fecin','fecfin','total','totalexoneradasfacturas','totalexoneradasboletas','totalgravadasfacturas','totalgravadasboletas','totalgravadasnotas','totalexoneradasnotas','empresa','sucursal'))->render();


              if($request->ajax()){
                 return response()->json(['vista'=>$vista]);

              }

              break;
                

              //CONSOLIDADO DE VENTAS
              case 80:

                $boletas = cpe_cabecera::select('ccafem as FECHA', 'tdodes as COMPROBANTE','serdoc as SERIE',DB::raw('MIN(numdoc) as INICIO'),DB::raw( 'MAX(numdoc) as FIN'),DB::raw('SUM(ccatvg) as VALOR_VENTA'),DB::raw('SUM(ccaigv) as IGV'),DB::raw('SUM(ccaitv) as TOTAL'))
                ->leftjoin('tipo_documento','tipo_documento.tdocod','cpe_cabecera.tdocod')
                ->whereNull('ccabaj')
                ->where('cpe_cabecera.tdocod','03')
                ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                }) 
                ->where(function ($query6) use ($almacen) {
                  if($almacen !='Todos'){
                      $query6->where('cpe_cabecera.id_almacen',$almacen);
                  }                
                }) 
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                ->groupby('ccafem')
                ->groupby('tdodes')
                ->orderBy('FECHA','asc')
                ->orderBy('INICIO','asc')
                ->get();

                $facturas = cpe_cabecera::select('ccaitv','cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','monnom as moneda','ccaitv as total','cpe_cabecera.ccabaj as baja','ccasunrescod','tdides as documentoidentidad','ccandi as numerodocumento','cpe_cabecera.tdocod','ccatexo','ccatvg','ccatvi')
                ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
                ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                
                ->where(function ($query6) use ($almacen) {
                  if($almacen !=0 && !empty($almacen)){
                      $query6->where('cpe_cabecera.id_almacen',$almacen);
                  }                
                }) 
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                ->where(function ($query){
                    $query->where('cpe_cabecera.tdocod','01');
                })
                ->whereNull('ccabaj')
                ->orderBy('IdCpe_cabecera','desc')->get();

             

                $total_boletas = $boletas->sum('TOTAL');
                $total_facturas = $facturas->sum('total');

                $vista = view('empresas.reportes.ventas_consolidado',compact('dat_cli','fecin','fecfin','boletas','facturas','total_boletas','total_facturas','dat_ven'))->render();

              if($request->ajax()){
                 return response()->json(['vista'=>$vista]);

              }


            break;







         
      }

    }




    public function imprimirreporteventas(Request $request){

       $clientes = DB::tABLE('cliente')->where('rucemp',Auth::user()->IdEmpresa)->get();
        $clicod = $request->get('clicod');

        $fecin = $request->get('fecin');
        $fecfin = $request->get('fecfin');
        $empresa = Empresa::findOrFail(Auth::user()->IdEmpresa);
        $IdEmpresa = Auth::user()->IdEmpresa;
        $docomp = $request->get('docomp');
        $nomempresa = Empresa::FindOrFail($IdEmpresa);
        $datoempresa = $nomempresa->NomEmpresa;

        $vendedor = $request->get('vendedor');
        
        $vendedores = DB::tABLE('users')
        ->leftjoin('role_user','role_user.user_IdUsuario','users.IdUsuario')
       // ->where('role_id','5')
         ->where('IdEmpresa',Auth::user()->IdEmpresa)
        ->get();

        $dat_ven = DB::tABLE('users')->where('IdUsuario',$request->get('vendedor'))->first();



        $comprobantes = cpe_cabecera::select('cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','monnom as moneda','ccaitv as total','cpe_cabecera.ccabaj as baja','ccasunrescod','tdides as documentoidentidad','ccandi as numerodocumento','cpe_cabecera.tdocod')
                    ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                    ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                    ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                    ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                    ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
                    ->where('cpe_cabecera.id_empresa_negocio',Auth::user()->id_empresa_negocio)
                    ->where('cpe_cabecera.ccafem','>=',$fecin)
                    ->where('cpe_cabecera.ccafem','<=',$fecfin)
                    ->where(function ($query) use ($vendedor) {
                      if($vendedor !='Todos'){
                         $query->Where('cpe_cabecera.IdUsuario_ven',$vendedor);
                      }
                    })
                        ->where(function ($query) {
                $query->where('cpe_cabecera.tdocod','01')
                    ->orWhere('cpe_cabecera.tdocod','03')
                    ->orWhere('cpe_cabecera.tdocod','13')
                    ->orWhere('cpe_cabecera.tdocod','14');
                })    
                    ->where(function ($query1){
                        $query1->whereNull('cpe_cabecera.ccabaj')
                              ->orwhere('cpe_cabecera.ccabaj','');
                    
                    })
                    ->orderBy('IdCpe_cabecera','desc')->get();

          $totalfacturas = $comprobantes->where('tdocod','01')->sum('total');

          $totalboletas =  $comprobantes->where('tdocod','03')->sum('total');

             $total =  $comprobantes->sum('total');

      $impresoras = DB::tABLE('configuracion_impresoras')->where('Id',Auth::user()->terminal)->first();


  
      $connector = new WindowsPrintConnector("smb://".$impresoras->ruta);

     
        $printer = new Printer($connector);
        $printer->setJustification(Printer::JUSTIFY_CENTER);
       

       $printer->text("TIENDAS DEPORTIVAS S.A.C"."\n");
             $printer->text("JR. PROSPERO #643"."\n");
       $printer->text('RUC:'.$empresa->IdEmpresa."\n");
       
 
        
        $printer->setJustification(Printer::JUSTIFY_CENTER);
        $printer->text("VENTAS DEL DIA"."\n");
          $printer->setJustification(Printer::JUSTIFY_LEFT);
        $printer->text("Fecha       Cod. Doc.  Serie  N° Doc.  Total"."\n");
        $printer->text("________________________________________________"."\n");
        foreach ($comprobantes as $comp) {
         
           $printer->text(Carbon::parse($comp->fecha)->format('d-m-Y')."    ".$comp->tdocod."       ".$comp->serie."   ".$comp->numero."    ".$comp->total."\n");
     
           
          
          
        }

        $printer->text("________________________________________________"."\n");
        $printer->text("TOTAL B/V                                ".number_format($totalboletas,'2','.',',')."\n");
        $printer->text("TOTAL F                                  ".number_format($totalfacturas,'2','.',',')."\n"."\n");


        $printer->text("________________________________________________"."\n");
        $printer->text("TOTAL VENTAS                             ".number_format($total,'2','.',',')."\n");
        $printer->feed();
         
     
        $printer->cut();
         
     
        $printer->pulse();

        $printer->close();



}
  
  
    public function imprimirreportes(){

        return view('empresas.reportes.indexventasticket');
    }

  public function kardex(Request $request){

      $almacen = $request->get('almacen');
      $sucursal = $request->get('sucursal');
      $prod = $request->get('IdProducto');
      $docomp= $request->get('docomp');
      $fecin = $request->get('fecin');
      $fecfin = $request->get('fecfin');
  

      $productoslista = DB::tABLE('productos')
      ->where('tipo','=','1')
      ->where('promocion','!=','2')
      ->get();

      $productos = DB::tABLE('productos')
      ->where('tipo','=','1')
      ->get();
  
      $negocios = DB::tABLE('empresa_negocios')->get();
      $almacenes = DB::tABLE('almacenes')->where('id_empresa_negocio',$negocios->first()->id_empresa_negocio)->get();
     
       
    return view('empresas.reportes.kardex',compact('productos','negocios','almacenes','productoslista'));
     

    }


   public function generarkardexpdf(Request $request){

    
     $negocios = DB::tABLE('empresa_negocios')->get();

    $almacenes = DB::tABLE('almacenes')->where('id_empresa_negocio',$negocios->first()->id_empresa_negocio)->get();

    $almacen = $request->get('almacen');
    $sucursal = $request->get('sucursal');
    $tipo = $request->get('docomp');
    $fecin = $request->get('fecin');
    $fecfin = $request->get('fecfin');
    $IdProducto = $request->get('IdProducto');


     
    $productoslista = DB::tABLE('productos')->select('IdProducto','pronom','procod','costo_total','flete','umecod')
    ->where('tipo','=','1')
    ->where('promocion','!=','2')
    //->where('id_empresa_negocio',$sucursal)
    ->orderby('pronom','asc')
    ->get();



      $saldo_anterior =0;


     
      $productos = DB::tABLE('productos')->select('IdProducto','pronom','procod','costo_total','flete','umecod',DB::raw("(SELECT COUNT(mov_pro_id) FROM movimientos_productos WHERE IdProducto=productos.IdProducto or IdProducto_rel=productos.IdProducto ) as contar"))
      ->where('tipo','=','1')
      ->where(function ($query) use ($IdProducto){
          if($IdProducto!='Todos'){
             $query->where('IdProducto','=',$IdProducto); 
          }      
      }) 
     ->having('contar','>','0')
      ->orderby('IdProducto','asc')
      ->get();
   
      $dat_alm = DB::tABLE('almacenes')->where('id_almacen',$almacen)->first();
      $dat_suc = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',$sucursal)->first();
      $dat_emp = DB::tABLE('empresa')->where('IdEmpresa',Auth::user()->IdEmpresa)->first();




      
      if($tipo=='2'){


            ini_set("pcre.backtrack_limit", "5000000");

             
             $pdf = PDF::loadView('formatos_reportes.kardexfisicopdf',compact('dat_alm','data_sucursal','movimientos','productos','negocios','almacenes','sucursal','almacen','dat_suc','dat_alm','dat_emp','fecin','fecfin','productoslista','saldos_actuales','kardex','IdProducto','dat_emp','array_productos'));
             return $pdf->stream('document.pdf');

          


                $headers = array(
                        'Content-Type: application/pdf',
                      );


  


      }elseif($tipo=='1'){


        return view('formatos_reportes.kardexvalorizadopdf',compact('dat_alm','data_sucursal','movimientos','productos','negocios','almacenes','sucursal','almacen','data_sucursal','dat_alm','dat_emp','fecin','fecfin','productoslista','saldos_actuales','kardex','IdProducto','empresa','array_productos'));

      

      }


     



 }

  
   public function generarkardexexcel(Request $request){

  
     $negocios = DB::tABLE('empresa_negocios')->get();

    $almacenes = DB::tABLE('almacenes')->where('id_empresa_negocio',$negocios->first()->id_empresa_negocio)->get();

    $almacen = $request->get('almacen');
    $sucursal = $request->get('sucursal');
    $tipo = $request->get('docomp');
    $fecin = $request->get('fecin');
    $fecfin = $request->get('fecfin');
    $IdProducto = $request->get('IdProducto');


     
    $productoslista = DB::tABLE('productos')->select('IdProducto','pronom','procod','costo_total','flete','umecod')
    ->where('tipo','=','1')
    ->where('promocion','!=','2')
    //->where('id_empresa_negocio',$sucursal)
    ->orderby('pronom','asc')
    ->get();



      $saldo_anterior =0;


      $productos = DB::tABLE('productos')->select('IdProducto','pronom','procod','costo_total','flete','umecod',DB::raw("(SELECT COUNT(mov_pro_id) FROM movimientos_productos WHERE IdProducto=productos.IdProducto or IdProducto_rel=productos.IdProducto ) as contar"))
      ->where('tipo','=','1')
      ->where(function ($query) use ($IdProducto){
          if($IdProducto!='Todos'){
             $query->where('IdProducto','=',$IdProducto); 
          }      
      }) 
     ->having('contar','>','0')
      ->orderby('IdProducto','asc')
      ->get();
   
      $dat_alm = DB::tABLE('almacenes')->where('id_almacen',$almacen)->first();
      $dat_suc = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',$sucursal)->first();
      $dat_emp = DB::tABLE('empresa')->where('IdEmpresa',Auth::user()->IdEmpresa)->first();


   

      if($tipo=='2'){

        Excel::create('kardex_fisico', function($excel) use ($productos,$dat_alm,$negocios,$almacenes,$sucursal,$almacen,$dat_suc,$dat_emp,$fecin,$fecfin,$productoslista,$IdProducto) {

                        $excel->sheet('kardex_fisico', function($sheet) use ($productos,$dat_alm,$negocios,$almacenes,$sucursal,$almacen,$dat_suc,$dat_emp,$fecin,$fecfin,$productoslista,$IdProducto) {

                       
                            
                                  $sheet->loadView('formatos_reportes_excel.kardex_fisico_excel',compact('productos','dat_alm','negocios','almacenes','sucursal','almacen','dat_suc','dat_emp','fecin','fecfin','productoslista','IdProducto'));
                          
                                

                        });

                    })->export('xlsx'); 

  

      }elseif($tipo=='1'){

        Excel::create('kardex_valorizado', function($excel) use ($dat_suc,$movimientos,$productos,$negocios,$almacenes,$sucursal,$almacen,$dat_alm,$dat_emp,$fecin,$fecfin,$productoslista,$saldos_actuales,$IdProducto,$array_productos) {

                        $excel->sheet('kardex_valorizado', function($sheet) use ($dat_suc,$movimientos,$productos,$negocios,$almacenes,$sucursal,$almacen,$dat_alm,$dat_emp,$fecin,$fecfin,$productoslista,$saldos_actuales,$IdProducto,$array_productos) {

                              
                                  $sheet->loadView('empresas.reportes.kardexvalorizadoexcel',compact('dat_alm','dat_suc','movimientos','productos','negocios','almacenes','sucursal','almacen','dat_emp','fecin','fecfin','productoslista','saldos_actuales','IdProducto','array_productos'));
                          
                                

                        });

                    })->export('xlsx'); 

         
      }
     


                        



 }


  public function listar_ajuste(Request $request){

    $negocios = DB::tABLE('empresa_negocios')->get();

    $almacenes = DB::tABLE('almacenes')->where('id_empresa_negocio',$negocios->first()->id_empresa_negocio)->get();

    $almacen = $request->get('almacen');
    $sucursal = $request->get('sucursal');
    $tipo = $request->get('docomp');
    $fecin = $request->get('fecin');
    $fecfin = $request->get('fecfin');
    $IdProducto = $request->get('IdProducto');
    $movimientos ='';

     
    $productos = DB::tABLE('productos')->select('IdProducto','pronom','procod','costo_total','flete','umecod')
    ->where('tipo','=','1')
    ->where('promocion','!=','2')
    //->where('id_empresa_negocio',$sucursal)
    ->orderby('pronom','asc')
    ->get();


    $dat_alm = DB::tABLE('almacenes')->where('id_almacen',$almacen)->first();
    $dat_suc = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',$sucursal)->first();
    $dat_emp = DB::tABLE('empresa')->where('IdEmpresa',Auth::user()->IdEmpresa)->first();

    if(!empty($IdProducto)){

      $movimientos = DB::tABLE('movimientos_productos')
      ->leftjoin('productos','productos.IdProducto','movimientos_productos.IdProducto_rel')
      ->leftjoin('cat_movimiento','cat_movimiento.cat_mov_id','movimientos_productos.mov_tip')
       ->where(function ($query) use ($IdProducto){
          if($IdProducto!='Todos'){
             $query->where('movimientos_productos.IdProducto','=',$IdProducto)
            ->orwhere('movimientos_productos.IdProducto_rel','=',$IdProducto); 
          }
           
             
      }) 
      ->where('movimientos_productos.id_empresa_negocio',$sucursal)
      ->where(function ($query) use ($almacen) {
          if($almacen!='Todos'){
              $query->where('movimientos_productos.id_almacen','=',$almacen); 
          }      
      }) 
      ->where('descripcion','AJUSTE')
      ->where('fecha_mov','>=',$fecin)
      ->where('fecha_mov','<=',$fecfin)
      ->orderby('fecha_mov','desc')
      ->get();

    }


      return view('empresas.reportes.listar_ajuste',compact('dat_alm','dat_suc','movimientos','productos','negocios','almacenes','sucursal','almacen','dat_suc','dat_alm','dat_emp','fecin','fecfin','productos','IdProducto'));
 

 }



 public function buscarkardex(Request $request){

    $negocios = DB::tABLE('empresa_negocios')->get();

    $almacenes = DB::tABLE('almacenes')->where('id_empresa_negocio',$negocios->first()->id_empresa_negocio)->get();

    $almacen = $request->get('almacen');
    $sucursal = $request->get('sucursal');
    $tipo = $request->get('docomp');
    $fecin = $request->get('fecin');
    $fecfin = $request->get('fecfin');
    $IdProducto = $request->get('IdProducto');


     
    $productoslista = DB::tABLE('productos')->select('IdProducto','pronom','procod','costo_total','flete','umecod')
    ->where('tipo','=','1')
    ->where('promocion','!=','2')
    //->where('id_empresa_negocio',$sucursal)
    ->orderby('pronom','asc')
    ->get();



      $saldo_anterior =0;




      $productos = DB::tABLE('productos')->select('IdProducto','pronom','procod','costo_total','flete','umecod',DB::raw("(SELECT COUNT(mov_pro_id) FROM movimientos_productos WHERE IdProducto=productos.IdProducto or IdProducto_rel=productos.IdProducto ) as contar"))
      ->where('tipo','=','1')
      ->where(function ($query) use ($IdProducto){
          if($IdProducto!='Todos'){
             $query->where('IdProducto','=',$IdProducto); 
          }      
      }) 
     ->having('contar','>','0')
      ->orderby('IdProducto','asc')
      ->get();
      
      $dat_alm = DB::tABLE('almacenes')->where('id_almacen',$almacen)->first();
      $dat_suc = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',$sucursal)->first();
      $dat_emp = DB::tABLE('empresa')->where('IdEmpresa',Auth::user()->IdEmpresa)->first();


    if($tipo=='2'){

         return view('empresas.reportes.kardexfisico1',compact('productos','dat_alm','dat_suc','negocios','almacenes','sucursal','almacen','dat_suc','dat_alm','dat_emp','fecin','fecfin','productoslista','IdProducto','fecin','fecfin'));
         
      }elseif($tipo=='1'){
          return view('empresas.reportes.kardexvalorizado',compact('dat_alm','dat_suc','productos','negocios','almacenes','sucursal','almacen','dat_suc','dat_alm','dat_emp','fecin','fecfin','productoslista','saldos_actuales','kardex','IdProducto','array_productos','fecin','fecfin'));
      }

     
 



 }

   


    public function cuentascobrar(Request $request){


          $clientes = DB::tABLE('cliente')->where('rucemp',Auth::user()->IdEmpresa)->get();

          $data_emp = DB::tABLE('empresa')->where('IdEmpresa',Auth::user()->IdEmpresa)->first();

          $data_neg = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first();

           $vendedores = DB::tABLE('users')
            ->join('role_user','role_user.user_IdUsuario','users.IdUsuario')
           // ->where('role_id','5')
             ->where('IdEmpresa',Auth::user()->IdEmpresa)
            ->get();

            $fecin = $request->get('fecin');
            $fecfin = $request->get('fecfin');
            $placa = $request->get('placa');
            $tipdoc = $request->get('tipdoc');
            $numdoc = $request->get('numdoc');
            $clicod = $request->get('clicod');
            $estado = $request->get('estado');
            $tipo = $request->get('tipfec');

            if(empty($tipo)){
                $tipo='0';
            }

            if(empty($fecin)){

                $fecin = now()->modify('first day of this month')->format('Y-m-d');
                $fecfin = now()->modify('last day of this month')->format('Y-m-d');
            }

            $cuentas = DB::tABLE('cuentas_cobrar')
            ->leftjoin('cpe_cabecera','cpe_cabecera.IdCpe_cabecera','cuentas_cobrar.IdCpe_cabecera')
            ->leftjoin('cliente','cliente.clicod','cuentas_cobrar.clicod')
            ->where('cuentas_cobrar.id_empresa_negocio',Auth::user()->id_empresa_negocio)
         /*   ->where(function($query) use($clicod) {
                   
                    
                     $query->where('cpe_cabecera.clicod',$clicod);

                    
                   
             })*/
     /*     ->where(function($query1) use ($fecin, $fecfin,$tipo)
          {
              if($tipo=='0'){
                $query1->where('fec_ven','>=',$fecin)
                      ->where('fec_ven','<=',$fecfin);
              }elseif($tipo=='1'){


                $query1->where('ccafem','>=',$fecin)
                      ->where('ccafem','<=',$fecfin);
              }
            
          })*/
            ->orderby('cuentas_cobrar.cue_cob_id','desc')
            ->get();


   /*       $detalles = DB::tABLE('cuentas_cobrar')
          ->leftjoin('cpe_cabecera','cpe_cabecera.IdCpe_cabecera','cuentas_cobrar.IdCpe_cabecera')
          ->leftjoin('cpe_detalle','cpe_detalle.IdCpe_cabecera','cpe_cabecera.IdCpe_cabecera')
          ->leftjoin('cliente','cliente.clicod','cuentas_cobrar.clicod')
          ->leftjoin('cuentasbancarias','cuentasbancarias.cuen_ban_id','cuentas_cobrar.cuen_ban_id')
          ->leftjoin('tipo_cuentas','tipo_cuentas.tip_cuen_id','cuentasbancarias.tip_cuen_id')
          ->leftjoin('moneda','moneda.moncod','cuentasbancarias.moncod')
          ->leftjoin('bancos','bancos.ban_id','cuentasbancarias.ban_id')
          ->where('cuentas_cobrar.id_empresa_negocio',Auth::user()->id_empresa_negocio)
          ->where(function($query) use($clicod) {
           
              $query->where('cpe_cabecera.clicod',$clicod);

                 
           })
          ->where(function($query1) use ($fecin, $fecfin,$tipo)
          {
              if($tipo=='0'){
                $query1->where('fec_ven','>=',$fecin)
                      ->where('fec_ven','<=',$fecfin);
              }elseif($tipo=='1'){


                $query1->where('ccafem','>=',$fecin)
                      ->where('ccafem','<=',$fecfin);
              }
            
          })
          ->orderby('cuentas_cobrar.cue_cob_id','desc')
          ->get();
*/
      /*  $detallecob = DB::tABLE('cuentas_cobrar_detalle as ccd')
        ->leftjoin('cuentasbancarias','cuentasbancarias.cuen_ban_id','ccd.cuen_ban_id')
        ->leftjoin('tipo_cuentas','tipo_cuentas.tip_cuen_id','cuentasbancarias.tip_cuen_id')
        ->leftjoin('moneda','moneda.moncod','cuentasbancarias.moncod')
        ->leftjoin('bancos','bancos.ban_id','cuentasbancarias.ban_id')
        ->orderby('ccd.cue_cob_det_id','desc')
        ->get();
       */
 
        return view('empresas.reportes.cuentascobrar',compact('vendedores','data_emp','data_neg','clientes','cuentas','fecin','fecfin','clicod','tipo'));
    }

    public function imprimirreporte(Request $request){

        $tipo = $request->get('docomp');
        $fecin = $request->get('fecin');
        $fecfin = $request->get('fecfin');

        $rucemp = Auth::user()->IdEmpresa;
        $empresa = Empresa::findOrFail($rucemp);
        $empresanegocios = EmpresaNegocios::findOrFail(Auth::user()->id_empresa_negocio);

        $totalventa=0;

         switch ($tipo){
            case 1:

                $comprobantes = cpe_cabecera::select('cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','monnom as moneda','ccaitv as total','cpe_cabecera.ccabaj as baja','ccasunrescod','tdides as documentoidentidad','ccandi as numerodocumento','cpe_cabecera.tdocod')
                ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
                ->where('cpe_cabecera.id_empresa_negocio',Auth::user()->id_empresa_negocio)
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                ->where(function ($query){
                    $query->where('tdocod','01')
                          ->orwhere('tdocod','03')
                          ->orWhere('tdocod','13');
                })
                ->where(function ($query1){
          
                    $query1->whereNull('cpe_cabecera.ccabaj')
                          ->orwhere('cpe_cabecera.ccabaj','');
                    
                })
                ->orderBy('IdCpe_cabecera','desc')->get();

                foreach($comprobantes as $comprobante){
  
                    if(!empty($comprobante->baja)){
                        $totalventa = $totalventa + $comprobante->total;
                      
                    }
                }
 
                  $impresoras = DB::tABLE('configuracion_impresoras')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->where('predeterminado','1')->first();
                  
                 try { 

                          $connector = new WindowsPrintConnector("smb://".$impresoras->ruta);

                         
                            $printer = new Printer($connector);
                            $printer->setJustification(Printer::JUSTIFY_CENTER);
                            if(file_exists($empresa->LogEmpresa)){
                                  $logo = EscposImage::load(public_path().'/'.$empresa->LogEmpresa,false);
                                  $printer->bitImage($logo);
                            }
                            $printer->setFont(Printer::FONT_A);
                            $printer->text("\n".$empresa->NomEmpresa."\n");
                            $printer->text($empresanegocios->cabecera."\n");
                         
                            
                            $printer->setJustification(Printer::JUSTIFY_LEFT);
                            $printer->text("COMPROBANTES ".$fecin." - ".$fecfin."\n");
                            $printer->text("________________________________________________"."\n");
                            foreach ($detalle as $det) {
                             
                            $printer->text($comprobantes->tdocod."-".$comprobantes->serie."-".$comprobantes->numero."  ".$comprobantes->ccafem."  ".$comprobantes->cliente." ".$comprobantes->total."\n");
                            
                            }


                            $printer->text("TOTAL: "."                               ".$totalventa."\n");
                           $printer->text("\n");
                        


                            $printer->feed();
                             
                         
                            $printer->cut();
                             
                         
                            $printer->pulse();
                             
                            
                            $printer->close();
                          }catch (\Exception $e) {

                          
                          }

                    return Redirect::to('/imprimirreportes');

            break;

            case 2:

                $productos = cpe_cabecera::select(DB::RAW('sum(cpe_detalle.cdecan) as cantidad'),'cdedes','cdepuni','procod','umecod')
                ->leftjoin('cpe_detalle','cpe_detalle.IdCpe_cabecera','cpe_cabecera.IdCpe_cabecera')
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                ->where('cpe_cabecera.moncod','=','PEN')
                ->where('cpe_cabecera.id_empresa_negocio',Auth::user()->id_empresa_negocio)
                ->whereNull('cpe_cabecera.ccabaj')
                ->orderby('cantidad','desc')
                ->groupby('procod','cdepuni','cdedes','umecod')
                ->get();


                 try { 

                          $connector = new WindowsPrintConnector("smb://".$impresoras->ruta);

                         
                            $printer = new Printer($connector);
                            $printer->setJustification(Printer::JUSTIFY_CENTER);
                            $logo = EscposImage::load('logo_rest.png',false);
                            $printer->bitImage($logo);
                            $printer->setFont(Printer::FONT_A);
                            $printer->text("\n".$empresa->NomEmpresa."\n");
                            $printer->text($empresanegocios->cabecera."\n");
                         
                            
                            $printer->setJustification(Printer::JUSTIFY_LEFT);
                            $printer->text("RANKING PRODUCTOS ".$fecin." - ".$fecfin."\n");
                            $printer->text("CONCEPTO                             CANTIDAD"."\n");
                            $printer->text("________________________________________________"."\n");
                            foreach ($productos as $det) {
                             
                            $printer->text($det->cdedes."                 ".$det->cantidad."\n");
                            
                            }
                           $printer->text("\n");
                        


                            $printer->feed();
                             
                         
                            $printer->cut();
                             
                         
                            $printer->pulse();
                             
                            
                            $printer->close();
                          }catch (\Exception $e) {

                          
                          }


                        return Redirect::to('/imprimirreportes');

            break;


            case 3:

               $productos= DB::tABLE('productos as p')
                ->leftjoin('moneda as m','p.moncod','=','m.moncod')
                ->leftjoin('unidad_medida as u','p.umecod','=','u.umecod')
                ->select('p.stockinicial','p.stock','p.procod','p.pronom','m.monnom','u.umenom','p.provun','p.propun','p.proest','IdProducto','p.costo','p.propun','marca','modelo')
                ->where('p.id_empresa_negocio',Auth::user()->id_empresa_negocio)
                 ->where(function ($query) {
                     $query->where('promocion','0')
                           ->orWhere('promocion','4');
                 })
                ->orderby('stock','asc')
                ->get();


                 try { 

                          $connector = new WindowsPrintConnector("smb://".$impresoras->ruta);

                         
                            $printer = new Printer($connector);
                            $printer->setJustification(Printer::JUSTIFY_CENTER);
                            $logo = EscposImage::load('logo_rest.png',false);
                            $printer->bitImage($logo);
                            $printer->setFont(Printer::FONT_A);
                            $printer->text("\n".$empresa->NomEmpresa."\n");
                            $printer->text($empresanegocios->cabecera."\n");
                         
                            
                            $printer->setJustification(Printer::JUSTIFY_LEFT);
                            $printer->text("STOCK ACTUAL PRODUCTOS"."\n");
                            $printer->text("PRODUCTO                             STOCK"."\n");
                            $printer->text("________________________________________________"."\n");
                            foreach ($productos as $det) {
                             
                            $printer->text($det->pronom."                ".$det->stock."\n");
                            
                            }
                           $printer->text("\n");
                        


                            $printer->feed();
                             
                         
                            $printer->cut();
                             
                         
                            $printer->pulse();
                             
                            
                            $printer->close();
                          }catch (\Exception $e) {

                          
                          }


                    return Redirect::to('/imprimirreportes');

            break;


   

        }
    

    }
    
     public function reportestock(Request $request){


        $fecin = Carbon::now()->startOfMonth()->format('Y-m-d');
        $fecfin = Carbon::now()->endOfMonth()->format('Y-m-d');
        $docomp = $request->get('docomp');
        $clientes = DB::tABLE('cliente')->where('rucemp',Auth::user()->IdEmpresa)->get();
        $clicod = $request->get('clicod');
        $sucursal = $request->get('sucursal');
        $almacen = $request->get('almacen');

        $negocios = DB::tABLE('empresa_negocios')->get();
        if(!empty($sucursal)){
          $almacenes = DB::tABLE('almacenes')->where('id_empresa_negocio',$sucursal)->get();
        }else{
          $almacenes = DB::tABLE('almacenes')->where('id_empresa_negocio',$negocios->first()->id_empresa_negocio)->get();
        }
        return view('empresas.reportes.indexstock',compact('clientes','clicod','sucursal','negocios','fecin','fecfin','docomp','almacenes','almacen'));

    }

    public function reportepantalla(){

        $vendedores = DB::tABLE('users')
        ->leftjoin('role_user','role_user.user_IdUsuario','users.IdUsuario')
        // ->where('role_id','5')
        ->where('IdEmpresa',Auth::user()->IdEmpresa)
        ->get();

        return view('empresas.reportes.index',compact('vendedores'));
    }

    public function reportecompra(request $request){


        $rucemp = trim(Auth::user()->IdEmpresa);
        $negocios = EmpresaNegocios::get();
        $sucursal = $request->get('sucursal');
        $proveedores = DB::tABLE('proveedor')->get();
        $proveedor = $request->get('proveedor');
        $opbtn = $request->get('opbtn');
         $tipo = $request->get('tipo');

        $fecin = $request->get('fecin');
        $fecfin = $request->get('fecfin');

        if(empty($fecin)){

          $fecin = now()->modify('first day of this month')->format('Y-m-d');
          $fecfin = now()->modify('last day of this month')->format('Y-m-d');
          $opbtn ='0';

        }

        $oldfecin = strtotime($fecin);

        $oldfecfin = strtotime($fecfin);

        $mesfecin = date('m',$oldfecin);

        $mesfecfin = date('m',$oldfecfin);

   
        $diferencia = $mesfecfin - $mesfecin;
        
        if($diferencia=='0'){

          $diferencia='1';

        }

      
        if($tipo =='1'){

          $compras = DB::tABLE('compras_cabecera')
              ->leftjoin('proveedor','compras_cabecera.prov_id','proveedor.prov_id')
              ->join('moneda','compras_cabecera.mon_id','moneda.moncod')
              ->join('tipo_documento','compras_cabecera.tdocod','tipo_documento.tdocod')
              
                ->where('compras_cabecera.com_fec','>=',$fecin)
                ->where('compras_cabecera.com_fec','<=',$fecfin)
              ->where('est_compra','Registrado')
                ->where(function ($query) use ($sucursal){
                  if(!$sucursal !=0 && !empty($sucursal)){
                      $query->where('compras_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
              })
              ->where(function ($query1) use ($proveedor){
                  if($proveedor !=0){
                      $query1->where('compras_cabecera.prov_id',$proveedor);
                  }
                  
              })
                    
              ->where(function ($query6) use ($almacen) {
                  if($almacen !=0 && !empty($almacen)){
                      $query6->where('compras_cabecera.id_almacen',$almacen);
                  }                
                }) 
            
              ->where('compras_cabecera.tdocod','!=','80')
              ->orderby('com_cab_id','desc')
              ->get();

        }elseif($tipo=='2'){
              $compras = DB::tABLE('compras_cabecera')
              ->leftjoin('proveedor','compras_cabecera.prov_id','proveedor.prov_id')
              ->leftjoin('compras_detalle','compras_cabecera.com_cab_id','compras_detalle.com_cab_id')
              ->leftjoin('productos','productos.IdProducto','compras_detalle.pro_id')
              ->join('moneda','compras_cabecera.mon_id','moneda.moncod')
              ->join('tipo_documento','compras_cabecera.tdocod','tipo_documento.tdocod')
              
              ->where('est_compra','Registrado')
                ->where('compras_cabecera.com_fec','>=',$fecin)
                ->where('compras_cabecera.com_fec','<=',$fecfin)
                ->where(function ($query) use ($sucursal){
                  if(!is_null($sucursal)){
                      $query->where('compras_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
              })
              ->where(function ($query1) use ($proveedor){
                  if($proveedor !='Todos'){
                      $query1->where('compras_cabecera.prov_id',$proveedor);
                  }
                  
              })
              ->where('compras_cabecera.tdocod','!=','80')
              ->orderby('compras_cabecera.com_cab_id','desc')
              ->get();

        }elseif($tipo=='3'){

             $compras = DB::tABLE('compras_cabecera')->select(DB::RAW('sum(compras_detalle.cantidad) as cantidad'),'pronom','pre_uni','procod','ume_cod')
                ->leftjoin('compras_detalle','compras_detalle.com_cab_id','compras_cabecera.com_cab_id')
                ->leftjoin('productos','productos.IdProducto','compras_detalle.pro_id')
                ->where('compras_cabecera.com_fec','>=',$fecin)
                ->where('compras_cabecera.com_fec','<=',$fecfin)
                ->where('est_compra','Registrado')
                ->where(function ($query) use ($sucursal){
                  if(!is_null($sucursal)){
                      $query->where('compras_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                })
                ->where(function ($query1) use ($proveedor){
                  if($proveedor !='Todos'){
                      $query1->where('compras_cabecera.prov_id',$proveedor);
                  }
                  
                })
                ->orderby('cantidad','desc')
                ->groupby('procod','pre_uni','pronom','ume_cod')
                ->get();

        }else{
            $compras = DB::tABLE('compras_cabecera')
              ->leftjoin('proveedor','compras_cabecera.prov_id','proveedor.prov_id')
              ->join('moneda','compras_cabecera.mon_id','moneda.moncod')
              ->join('tipo_documento','compras_cabecera.tdocod','tipo_documento.tdocod')
              
              ->where('est_compra','Registrado')
                ->where('compras_cabecera.com_fec','>=',$fecin)
                ->where('compras_cabecera.com_fec','<=',$fecfin)
                ->where(function ($query) use ($sucursal){
                  if(!is_null($sucursal)){
                      $query->where('compras_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
              })
             
              ->where('compras_cabecera.tdocod','!=','80')
              ->orderby('com_cab_id','desc')
              ->get();
        }
             


        if($opbtn =='2'){

         
                        Excel::create('Reporte_Compras', function($excel) use ($compras,$negocios,$sucursal,$proveedores,$proveedor,$fecin,$fecfin,$tipo,$diferencia) {

                        $excel->sheet('Comprobantes', function($sheet) use ($compras,$negocios,$sucursal,$proveedores,$proveedor,$fecin,$fecfin,$tipo,$diferencia) {

                            $sheet->setColumnFormat(array(
                                    'A' => 'dd/mm/yy',
                                    'G' => '0.00',
                                    'H' => '0.00',
                                    'I' => '0.00',
                                    'J' => '0.00',

                                    
                                ));
                                
                                if($tipo=='1'){
                                  $sheet->loadView('empresas.reportes.reportecomprasexcel',compact('compras','negocios','sucursal','proveedores','proveedor','fecin','fecfin','diferencia'));
                                }elseif($tipo=='2'){
                                  $sheet->loadView('empresas.reportes.reportecomprasdetalladoexcel',compact('compras','negocios','sucursal','proveedores','proveedor','fecin','fecfin','diferencia'));
                                }elseif($tipo=='3'){
                                  $sheet->loadView('empresas.reportes.reportecompraspromedioexcel',compact('compras','negocios','sucursal','proveedores','proveedor','fecin','fecfin','diferencia'));
                                }

                                

                        });

                    })->export('xlsx'); 

          }elseif($opbtn=='1' || empty($opbtn)){
              

              return view('empresas.reportes.reportecompras',compact('compras','negocios','sucursal','proveedores','proveedor','fecin','fecfin','tipo','diferencia'));

          }
                   

       


      
    }

      public function reportealbergues(request $request){


        $rucemp = trim(Auth::user()->IdEmpresa);
        $negocios = EmpresaNegocios::get();
        $sucursal = $request->get('sucursal');
        $proveedores = DB::tABLE('proveedor')->get();
        $proveedor = $request->get('proveedor');
        $opbtn = $request->get('opbtn');
         $tipo = $request->get('tipo');

        $fecin = $request->get('fecin');
        $fecfin = $request->get('fecfin');

        if(empty($fecin)){

          $fecin = now()->modify('first day of this month')->format('Y-m-d');
          $fecfin = now()->modify('last day of this month')->format('Y-m-d');
          $opbtn ='0';

        }

        $oldfecin = strtotime($fecin);

        $oldfecfin = strtotime($fecfin);

        $mesfecin = date('m',$oldfecin);

        $mesfecfin = date('m',$oldfecfin);

   
        $diferencia = $mesfecfin - $mesfecin;
        
        if($diferencia=='0'){

          $diferencia='1';

        }

      
        if($tipo =='1'){

              $compras = DB::tABLE('pedido_servicio_cab')
              ->leftjoin('productos','productos.IdProducto','pedido_servicio_cab.IdProducto')
              ->join('servicios','servicios.ser_cod','pedido_servicio_cab.ser_cod')
              ->where('ped_ser_fec','>=',$fecin)
              ->where('ped_ser_fec','<=',$fecfin)
              ->where('pedido_servicio_cab.id_empresa_negocio',$sucursal)
              ->orderby('ped_ser_fec','desc')
              ->get();

              $grup_serv= DB::tABLE('pedido_servicio_cab')->select('ped_ser_fec','ser_nom',DB::RAW('sum(total) as cantidad'))
              ->leftjoin('productos','productos.IdProducto','pedido_servicio_cab.IdProducto')
              ->join('servicios','servicios.ser_cod','pedido_servicio_cab.ser_cod')
              ->where('ped_ser_fec','>=',$fecin)
              ->where('ped_ser_fec','<=',$fecfin)
              ->where('pedido_servicio_cab.id_empresa_negocio',$sucursal)
              ->orderby('ped_ser_fec','desc')
              ->groupby('pedido_servicio_cab.ped_ser_fec')
              ->groupby('pedido_servicio_cab.ser_cod')
              ->get();

               $grup_plat= DB::tABLE('pedido_servicio_cab')->select('ped_ser_fec','ser_nom','pronom',DB::RAW('sum(total) as cantidad'))
              ->leftjoin('productos','productos.IdProducto','pedido_servicio_cab.IdProducto')
              ->join('servicios','servicios.ser_cod','pedido_servicio_cab.ser_cod')
              ->where('ped_ser_fec','>=',$fecin)
              ->where('ped_ser_fec','<=',$fecfin)
              ->where('pedido_servicio_cab.id_empresa_negocio',$sucursal)
              ->orderby('ped_ser_fec','desc')
              ->groupby('pedido_servicio_cab.ped_ser_fec')
              ->groupby('pedido_servicio_cab.IdProducto')
              ->get();


           /*  $grup_ins = DB::tABLE('pedido_servicio_cab')->select('ped_ser_fec','ser_nom','productos.pronom','recetas.pronom as insumo',DB::RAW('sum(total) as cantidad'),DB::RAW('sum(total*recetas.rec_cant) as cantins'))
              ->leftjoin('productos as p','productos.IdProducto','pedido_servicio_cab.IdProducto')
              ->leftjoin('recetas','recetas.prod_id','productos.IdProducto')
              ->join('servicios','servicios.ser_cod','pedido_servicio_cab.ser_cod')
              ->where('ped_ser_fec','>=',$fecin)
              ->where('ped_ser_fec','<=',$fecfin)
              ->where('pedido_servicio_cab.id_empresa_negocio',$sucursal)
              ->orderby('ped_ser_fec','desc')
              ->groupby('pedido_servicio_cab.ped_ser_fec')
              ->groupby('pedido_servicio_cab.IdProducto')
              ->groupby('pedido_servicio_cab.prod_id')
              ->get();*/

        }elseif($tipo=='2'){
              $compras = DB::tABLE('compras_cabecera')
              ->leftjoin('proveedor','compras_cabecera.prov_id','proveedor.prov_id')
              ->leftjoin('compras_detalle','compras_cabecera.com_cab_id','compras_detalle.com_cab_id')
              ->leftjoin('productos','productos.IdProducto','compras_detalle.pro_id')
              ->join('moneda','compras_cabecera.mon_id','moneda.moncod')
              ->join('tipo_documento','compras_cabecera.tdocod','tipo_documento.tdocod')
              
              ->where('est_compra','Registrado')
                ->where('compras_cabecera.com_fec','>=',$fecin)
                ->where('compras_cabecera.com_fec','<=',$fecfin)
                ->where(function ($query) use ($sucursal){
                  if(!is_null($sucursal)){
                      $query->where('compras_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
              })
              ->where(function ($query1) use ($proveedor){
                  if($proveedor !='Todos'){
                      $query1->where('compras_cabecera.prov_id',$proveedor);
                  }
                  
              })
              ->where('compras_cabecera.tdocod','!=','80')
              ->orderby('compras_cabecera.com_cab_id','desc')
              ->get();

        }else{
            $compras = DB::tABLE('compras_cabecera')
              ->leftjoin('proveedor','compras_cabecera.prov_id','proveedor.prov_id')
              ->join('moneda','compras_cabecera.mon_id','moneda.moncod')
              ->join('tipo_documento','compras_cabecera.tdocod','tipo_documento.tdocod')
              
              ->where('est_compra','Registrado')
                ->where('compras_cabecera.com_fec','>=',$fecin)
                ->where('compras_cabecera.com_fec','<=',$fecfin)
                ->where(function ($query) use ($sucursal){
                  if(!is_null($sucursal)){
                      $query->where('compras_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
              })
             
              ->where('compras_cabecera.tdocod','!=','80')
              ->orderby('com_cab_id','desc')
              ->get();
        }
             


        if($opbtn =='2'){

         
                        Excel::create('Reporte_Compras', function($excel) use ($compras,$negocios,$sucursal,$proveedores,$proveedor,$fecin,$fecfin,$tipo,$diferencia) {

                        $excel->sheet('Comprobantes', function($sheet) use ($compras,$negocios,$sucursal,$proveedores,$proveedor,$fecin,$fecfin,$tipo,$diferencia) {

                            $sheet->setColumnFormat(array(
                                    'A' => 'dd/mm/yy',
                                    'G' => '0.00',
                                    'H' => '0.00',
                                    'I' => '0.00',
                                    'J' => '0.00',

                                    
                                ));
                                
                                if($tipo=='1'){
                                  $sheet->loadView('empresas.reportes.reportecomprasexcel',compact('compras','negocios','sucursal','proveedores','proveedor','fecin','fecfin','diferencia'));
                                }elseif($tipo=='2'){
                                  $sheet->loadView('empresas.reportes.reportecomprasdetalladoexcel',compact('compras','negocios','sucursal','proveedores','proveedor','fecin','fecfin','diferencia'));
                                }elseif($tipo=='3'){
                                  $sheet->loadView('empresas.reportes.reportecompraspromedioexcel',compact('compras','negocios','sucursal','proveedores','proveedor','fecin','fecfin','diferencia'));
                                }

                                

                        });

                    })->export('xlsx'); 

          }elseif($opbtn=='1' || empty($opbtn)){
              

              return view('empresas.reportes.reportealbergues',compact('compras','negocios','sucursal','proveedores','proveedor','fecin','fecfin','tipo','diferencia','grup_serv','grup_plat','grup_ins'));

          }
                   

       


      
    }



    public function ReporteComprobantes(Request $request)
    {

      
        $clientes = DB::tABLE('cliente')->where('rucemp',Auth::user()->IdEmpresa)->get();
        $clicod = $request->get('clicod');

        $fecin = $request->get('fecin');
        $fecfin = $request->get('fecfin');
        $empresa = Empresa::findOrFail(Auth::user()->IdEmpresa);
        $IdEmpresa = Auth::user()->IdEmpresa;
        $docomp = $request->get('docomp');
        $nomempresa = Empresa::FindOrFail($IdEmpresa);
        $datoempresa = $nomempresa->NomEmpresa;

        $vendedor = $request->get('vendedor');
        
        $vendedores = DB::tABLE('users')
        ->leftjoin('role_user','role_user.user_IdUsuario','users.IdUsuario')
       // ->where('role_id','5')
        ->where('IdEmpresa',Auth::user()->IdEmpresa)
        ->get();

        $dat_ven = DB::tABLE('users')->where('IdUsuario',$request->get('vendedor'))->first();

       
        switch ($docomp){
            case 1:
    
        
        $comprobantes = cpe_cabecera::select('cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','monnom as moneda','ccaitv as total','cpe_cabecera.ccabaj as baja','ccasunrescod','tdides as documentoidentidad','ccandi as numerodocumento')
                    ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                    ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                    ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                    ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                    ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
                    ->where('cpe_cabecera.id_empresa_negocio',Auth::user()->id_empresa_negocio)
                    ->where('cpe_cabecera.ccafem','>=',$fecin)
                    ->where('cpe_cabecera.ccafem','<=',$fecfin)
                    ->where(function ($query) use ($vendedor) {
                      if($vendedor !='Todos'){
                         $query->Where('cpe_cabecera.IdUsuario_ven',$vendedor);
                      }
                    })
                        ->where(function ($query) {
                $query->where('cpe_cabecera.tdocod','01')
                    ->orWhere('cpe_cabecera.tdocod','03')
                    ->orWhere('cpe_cabecera.tdocod','13')
                    ->orWhere('cpe_cabecera.tdocod','07')
                    ->orWhere('cpe_cabecera.tdocod','08')
                    ->orWhere('cpe_cabecera.tdocod','14');
                })    
                        
                    ->where(function ($query1){
                        $query1->whereNull('cpe_cabecera.ccabaj')
                              ->orwhere('cpe_cabecera.ccabaj','');
                    
                    })
                    ->orderBy('IdCpe_cabecera','desc')->get();

              return view('empresas.reportes.indexventas',compact('fecin','fecfin','comprobantes','vendedores','dat_ven'));
        
        break;

        case 2:

                 $comprobantes = cpe_cabecera::select('cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','det.cdedes as producto','det.cdecan as cantidad','cdepuni as precio','monnom as moneda','det.cdevve as total','cpe_cabecera.ccabaj as baja','det.costo','ccasunrescod','tdides as documentoidentidad','ccandi as numerodocumento')
                ->leftjoin('cpe_detalle as det','cpe_cabecera.IdCpe_cabecera','det.IdCpe_cabecera')
                ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
                ->where('cpe_cabecera.id_empresa_negocio',Auth::user()->id_empresa_negocio)
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                 ->where(function ($query) use ($vendedor) {
                      if($vendedor !='Todos'){
                         $query->Where('cpe_cabecera.IdUsuario_ven',$vendedor);
                      }
                    })
                     ->where(function ($query) {
                $query->where('cpe_cabecera.tdocod','01')
                    ->orWhere('cpe_cabecera.tdocod','03')
                    ->orWhere('cpe_cabecera.tdocod','13')
                    ->orWhere('cpe_cabecera.tdocod','14');
                })    
                 ->orderBy('IdCpe_cabecera','desc')->get();

      

                return view('empresas.reportes.indexdetallado',compact('vendedores','fecin','fecfin','comprobantes','dat_ven'));
                 
        break;
        
        case 3:
                 $comprobantes = cpe_cabecera::select('cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','cdepuni as precio','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','det.cdecan as cantidad','det.cdedes as producto','monnom as moneda','det.cdevve as total','det.costo as costo','cpe_cabecera.ccabaj as baja','ccasunrescod')
                ->leftjoin('cpe_detalle as det','cpe_cabecera.IdCpe_cabecera','det.IdCpe_cabecera')
                ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
                ->where('cpe_cabecera.id_empresa_negocio',Auth::user()->id_empresa_negocio)
               
                   ->where(function ($query) use ($vendedor) {
                      if($vendedor !='Todos'){
                         $query->Where('cpe_cabecera.IdUsuario_ven',$vendedor);
                      }
                    })
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin) 
                  ->where(function ($query) {
                $query->where('cpe_cabecera.tdocod','01')
                    ->orWhere('cpe_cabecera.tdocod','03')
                    ->orWhere('cpe_cabecera.tdocod','13')
                    ->orWhere('cpe_cabecera.tdocod','14');
                })                
                ->orderBy('IdCpe_cabecera','desc')->get();

               

                  return view('empresas.reportes.indexutilidad',compact('vendedores','fecin','fecfin','comprobantes','dat_ven'));
                 
        break;


        
        case 4:
       $vendedores = DB::tABLE('users')
        ->leftjoin('role_user','role_user.user_IdUsuario','users.IdUsuario')
        // ->where('role_id','5')
        ->where('IdEmpresa',Auth::user()->IdEmpresa)
        ->get();
    
                 $comprobantes = cpe_cabecera::select('cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','tdides as documentoidentidad','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','monnom as moneda','ccaitv as total','cpe_cabecera.ccabaj as baja','ccaigv as igv','ccatvg as gravado','ccandi as numerodocumento','ccasunrescod','cpe_cabecera.tdocod','cpe_cabecera.ccatexo','cpe_cabecera.icbper')
                    ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                    ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                    ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                    ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                    ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
                    ->where('cpe_cabecera.id_empresa_negocio',Auth::user()->id_empresa_negocio)
                    ->where('cpe_cabecera.ccafem','>=',$fecin)
                    ->where('cpe_cabecera.ccafem','<=',$fecfin)
                       ->where(function ($query) {
            $query->where('cpe_cabecera.tdocod','01')
              ->orWhere('cpe_cabecera.tdocod','03')
              ->orWhere('cpe_cabecera.tdocod','13');
            })    
                     ->orderBy('IdCpe_cabecera','desc')->get();

               
                      return view('empresas.reportes.indexcontador',compact('fecin','fecfin','comprobantes','vendedores'));

        break;

         case 5:
                 $comprobantes = compras_cabecera::select('com_fec as fecha','tdodes as comprobante','com_doc_ser as serie','com_doc_num as numero','prov_raz as cliente','monnom as moneda','total_com as total','est_compra as estado')
                    ->leftjoin('moneda as m','compras_cabecera.mon_id','=','m.moncod')
                    ->leftjoin('proveedor as prov','prov.prov_id','compras_cabecera.prov_id')
                    ->leftjoin('empresa as e','compras_cabecera.IdEmpresa','=','e.IdEmpresa')
                    ->leftjoin('tipo_documento as tip_d','compras_cabecera.tdocod','=','tip_d.tdocod')
                    ->leftjoin('tipo_documento_identidad as tdi','prov.tdicod','=','tdi.tdicod')
                    ->where('compras_cabecera.id_empresa_negocio',Auth::user()->id_empresa_negocio)
                    ->where('com_fec','>=',$fecin)
                    ->where('com_fec','<=',$fecfin)
                    ->orderBy('com_cab_id','desc')->get();

             

                    return view('empresas.reportes.indexcompras',compact('fecin','fecfin','comprobantes'));
        break;


         case 6:
                
                $clicod = $request->get('clicod');
                $clientes = DB::tABLE('cliente')->where('rucemp',Auth::user()->IdEmpresa)->get();

                   
              $almacen = $request->get('almacen');
              $sucursal = $request->get('sucursal');

              $negocios = DB::tABLE('empresa_negocios')->get();
              if(!empty($sucursal)){
                $almacenes = DB::tABLE('almacenes')->where('id_empresa_negocio',$sucursal)->get();
              }else{
                $almacenes = DB::tABLE('almacenes')->where('id_empresa_negocio',$negocios->first()->id_empresa_negocio)->get();
              }

                $datocli = DB::tABLE('cliente')->where('clicod',$clicod)->first();

                $productos = cpe_cabecera::select(DB::RAW('sum(cpe_detalle.cdecan) as cantidad'),'cdedes','cdepuni','procod','umecod')
                ->leftjoin('cpe_detalle','cpe_detalle.IdCpe_cabecera','cpe_cabecera.IdCpe_cabecera')
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                ->whereNull('cpe_cabecera.ccabaj')
                ->where(function ($query) use ($sucursal) {
                    if($sucursal !='0'){
                       $query->Where('cpe_cabecera.id_empresa_negocio',$sucursal);
                    }
                  })
                ->where(function ($query1) use ($clicod) {
                    if($clicod !='Todos'){
                       $query1->Where('cpe_cabecera.clicod',$clicod);
                    }
                  })
                ->where(function ($query2) use ($sucursal) {
                    if(!empty($sucursal)){
                       $query2->Where('cpe_cabecera.id_empresa_negocio',$sucursal);
                    }
                })
                ->where(function ($query3) use ($almacen) {
                    if(!empty($almacen)){
                       $query3->Where('cpe_cabecera.id_almacen',$almacen);
                    }
                })
                ->orderby('cantidad','desc')
                ->groupby('procod','cdepuni','cdedes','umecod')
                ->get();

                return view('empresas.reportes.indexproductos',compact('productos','fecin','fecfin','clicod','clientes','datocli','sucursal','negocios','docomp','almacen','almacenes'));

        break;

        case 7:

              
              $almacen = $request->get('almacen');
              $sucursal = $request->get('sucursal');

              $negocios = DB::tABLE('empresa_negocios')->get();
              if(!empty($sucursal)){
                $almacenes = DB::tABLE('almacenes')->where('id_empresa_negocio',$sucursal)->get();
              }else{
                $almacenes = DB::tABLE('almacenes')->where('id_empresa_negocio',$negocios->first()->id_empresa_negocio)->get();
              }

             if(empty($sucursal)){
                $productos = DB::tABLE('productos')
                ->select('procod','mar_nom as marca','pronom','propun','productos.IdProducto','umecod','promocion','color','imagenproducto','precio2',DB::raw("(SELECT stock FROM producto_stock as pro WHERE productos.IdProducto = pro.IdProducto AND tipo='1' AND id_almacen='".$almacenes->first()->id_almacen."' AND id_empresa_negocio='".$negocios->first()->id_empresa_negocio."') as stock"),'precio',DB::raw("(SELECT count(*) FROM productos as pro WHERE productos.IdProducto = pro.pro_rel AND tipo='2') as cont_pre"))
                ->join('producto_empresa','producto_empresa.IdProducto','productos.IdProducto')
                ->join('producto_stock','producto_stock.IdProducto','producto_empresa.IdProducto')
               ->leftjoin('marcas','marcas.mar_id','productos.marca')
                ->where('tipo','1')
                ->where('producto_empresa.id_empresa_negocio',$negocios->first()->id_empresa_negocio)
                ->where('id_almacen',$almacenes->first()->id_empresa_negocio)
                ->groupby('productos.IdProducto')
                ->orderby('productos.pronom')
                ->orderby('productos.umecod')
                ->get();

             }else{
                $productos = DB::tABLE('productos')
                ->select('costo','procod','mar_nom as marca','pronom','propun','productos.IdProducto','umenom','productos.umecod','promocion','color','imagenproducto','precio2',DB::raw("(SELECT stock FROM producto_stock as pro WHERE productos.IdProducto = pro.IdProducto AND tipo='1' AND id_almacen='".$almacen."' AND id_empresa_negocio='".$sucursal."') as stock"),'precio',DB::raw("(SELECT count(*) FROM productos as pro WHERE productos.IdProducto = pro.pro_rel AND tipo='2') as cont_pre"))
                ->join('producto_empresa','producto_empresa.IdProducto','productos.IdProducto')
                ->join('producto_stock','producto_stock.IdProducto','producto_empresa.IdProducto')
                ->join('unidad_medida','unidad_medida.umecod','productos.umecod')
        ->leftjoin('marcas','marcas.mar_id','productos.marca')
                ->where('tipo','1')
                ->where('producto_empresa.id_empresa_negocio',$sucursal)
                ->where('id_almacen',$almacen)
                ->groupby('productos.IdProducto')
                ->orderby('productos.pronom')
                ->orderby('productos.umecod')
                ->get();

             }
              

                return view('empresas.reportes.indexmostrarstock',compact('sucursal','negocios','almacenes','almacen','productos','fecin','fecfin','clientes','clicod','docomp'));
        break;


     
      }
            
    
    }

    

     public function ExportarStock(Request $request)
    {

        $rucemp = trim(Auth::user()->IdEmpresa); 
    
        $stock= DB::table("productos")
          ->select("productos.*",
                    DB::raw("(SELECT SUM(cantidad) FROM movimientos
                                WHERE movimientos.IdProducto = productos.IdProducto
                                AND mov_tip='I'
                                GROUP BY movimientos.IdProducto) as Ingresos"),
                    DB::raw("(SELECT SUM(cantidad) FROM movimientos
                                WHERE movimientos.IdProducto = productos.IdProducto
                                AND mov_tip='E'
                                GROUP BY movimientos.IdProducto) as Egresos"))->where('IdEmpresa',$rucemp)->get();
            
            
        Excel::create('Stock', function($excel) use($stock) {
            $excel->sheet('Stock', function($sheet) use($stock) {
                $sheet->fromArray($stock);
            });
        })->export('xlsx');

    }


       public function ExportarProductosExcel(Request $request)
    {

          $tipo = $request->get('promocion');
          $categoria = $request->get('cmbCatId');
          $sucursal = $request->get('sucursal');
          $negocios = DB::tABLE('empresa_negocios')->get();
          $almacen = $request->get('almacen');
          $datosalm ="";
          $rucemp = trim(Auth::user()->IdEmpresa);
          $empresa = Empresa::findOrFail($rucemp);
          $buspro = trim($request->get('buspro'));

          $rucemp = trim(Auth::user()->IdEmpresa); 
     
          $productos = DB::tABLE('productos as p')
                ->select('producto_stock.stock_inicial','producto_stock.stock','p.procod','p.pronom','m.monnom','u.umenom','p.propun','p.proest','producto_stock.IdProducto','mar.mar_nom as marca','p.costo','p.propun','modelo','precio','precio2','precio3')
                ->leftjoin('moneda as m','p.moncod','=','m.moncod')
                ->leftjoin('marcas as mar','mar.mar_id','=','p.marca')
                ->leftjoin('unidad_medida as u','p.umecod','=','u.umecod')
                ->leftjoin('producto_empresa','producto_empresa.IdProducto','p.IdProducto')
                ->leftjoin('producto_stock','producto_stock.IdProducto','p.IdProducto')
                ->where(function ($query) use($categoria) {
                  if($categoria!='Todos'){
                    $query->where('cat_id',$categoria);
                  }
                  
                })
                ->where(function ($query1) use($buspro) {
                  if(!empty($buspro)){
                    $query1->where('pronom','like','%'.$buspro.'%')
                            ->orwhere('procod',$buspro);
                  }
                  
                })
                ->where('producto_stock.id_empresa_negocio',$sucursal)
                ->where('tipo','1')
                ->where('id_almacen',$almacen)
                ->groupby('p.IdProducto')
                ->orderby('pronom','asc')
                ->get();

                $productoslista = [];

                foreach($productos as $producto){

                                     $item = array(
                    'CODIGO'=>$producto->procod, 
                    'PRODUCTO'=>$producto->pronom, 
                    'MARCA'=>$producto->marca, 
                    'UNIDAD_MEDIDA'=>$producto->umenom, 
                    'STOCK_INICIAL'=>$producto->stock_inicial, 
                    'STOCK'=>$producto->stock, 
                    'PRECIO_PUBLICO'=>$producto->precio,
                    'PRECIO_MAYOR'=>$producto->precio2,
                    'PRECIO_ESPECIAL'=>$producto->precio3,
                    'COSTO'=>$producto->costo,
                    'VALOR_INVENTARIO'=>number_format($producto->costo*$producto->stock,2,'.','') 
                  );

                   $productoslista[]=$item;

                }
           
        Excel::create('PRODUCTOS', function($excel) use($productoslista) {
        $excel->sheet('PRODUCTOS', function($sheet) use($productoslista) {
    
        $sheet->fromArray($productoslista);
     });
        })->export('xlsx');





    }

    public function ExportarStockProductos(Request $request)
    {
        // 1. OBTENCIÓN DE FILTROS NECESARIOS
        $tipo = $request->get('promocion');
        $estado = $request->get('estado');
        $categoria = $request->get('cmbCatId'); // Recibimos la categoría
        $sucursal = $request->get('sucursal');
        $almacen = $request->get('almacen');
        $buspro = trim($request->get('buspro'));
        
        $rucemp = trim(Auth::user()->IdEmpresa);
        $empresa = \MasterSoft\Empresa::findOrFail($rucemp);
        
        // 2. LÓGICA DE OBTENCIÓN DE DATOS DEL STOCK
        $productos = DB::table('productos as p')
        ->select(
            'producto_stock.lote','producto_stock.vencimiento','producto_stock.stock_inicial','producto_stock.stock','producto_stock.stock_equivalencia','p.procod','p.pronom','m.monnom','u.umenom','p.propun','p.proest','producto_stock.IdProducto','mar.mar_nom as marca','p.costo','p.propun',
            'cat.cat_nom as categoria', // <--- NUEVO CAMPO: Obtenemos el nombre de la categoría
            DB::raw("(SELECT precio FROM producto_empresa  WHERE producto_empresa.IdProducto=p.IdProducto and id_empresa_negocio='".$sucursal."') as precio"),
            DB::raw("(SELECT precio2 FROM producto_empresa  WHERE producto_empresa.IdProducto=p.IdProducto and id_empresa_negocio='".$sucursal."') as precio2"),
            DB::raw("(SELECT precio3 FROM producto_empresa  WHERE producto_empresa.IdProducto=p.IdProducto and id_empresa_negocio='".$sucursal."') as precio3")
         )
        ->leftjoin('moneda as m','p.moncod','=','m.moncod')
        ->leftjoin('marcas as mar','mar.mar_id','=','p.marca')
        ->leftjoin('unidad_medida as u','p.umecod','=','u.umecod')
        ->leftjoin('producto_stock','producto_stock.IdProducto','p.IdProducto')
        ->leftjoin('categorias as cat', 'p.cat_id', '=', 'cat.cat_id') // <--- NUEVO JOIN a categorias
        ->where(function ($query1) use($buspro) {
            if(!empty($buspro)){
                $query1->where('p.pronom','like','%'.$buspro.'%')
                       ->orwhere('p.procod',$buspro);
            }
        })
        ->where(function ($query1) use($estado) {
            if($estado=='cs'){
                $query1->where('producto_stock.stock','>','0');
            }elseif($estado=='se'){
                $query1->where('producto_stock.stock','=','0');
            }elseif($estado=='ne'){
                $query1->where('producto_stock.stock','<','0');
            }
        })
        // ---> NUEVO: FILTRO DE CATEGORÍA <---
        ->where(function ($queryCat) use ($categoria) {
            if ($categoria != 'Todos' && !empty($categoria)) {
                $queryCat->where('p.cat_id', $categoria);
            }
        })
        ->where('producto_stock.id_empresa_negocio',$sucursal)
        ->where('p.tipo','1') // Se especificó 'p.'
        ->where(function ($query) { 
            $query->where('p.promocion','0') // Se especificó 'p.'
                  ->orWhere('p.promocion','4');
        })
        ->where('producto_stock.id_almacen',$almacen) // Se especificó 'producto_stock.'
        ->groupby('p.IdProducto')
        ->orderby('p.pronom','asc') // Se especificó 'p.'
        ->get();

        $productoslista = [];

        // 3. TRANSFORMACIÓN DE DATOS PARA EXCEL
        foreach($productos as $producto){
            $item = array(
                'CODIGO'=>$producto->procod, 
                'CATEGORIA'=>$producto->categoria, // <--- NUEVA COLUMNA EN EXCEL
                'PRODUCTO'=>$producto->pronom, 
                'MARCA'=>$producto->marca, 
                'UNIDAD_MEDIDA'=>$producto->umenom, 
                'LOTE'=>$producto->lote, 
                'VENCIMIENTO'=>$producto->vencimiento,
                'STOCK_INICIAL'=>$producto->stock_inicial, 
                'STOCK_PRINCIPAL'=>$producto->stock, 
                'STOCK_EQUIVALENCIA'=>$producto->stock_equivalencia,
                'PRECIO_1'=>$producto->precio, 
                'PRECIO_2'=>$producto->precio2, 
                'PRECIO_3'=>$producto->precio3,
                'COSTO'=>$producto->costo,
                'VALOR_INVENTARIO'=>number_format($producto->costo*$producto->stock,2,'.','') 
            );
            $productoslista[]=$item;
        }

        // 4. GENERACIÓN Y DESCARGA DEL EXCEL
        Excel::create('Stock', function($excel) use($productoslista) {
            $excel->sheet('Stock', function($sheet) use($productoslista) {
                $sheet->fromArray($productoslista);
            });
        })->export('xlsx');

        die(); 
    }

    public function ExportarStockPdf(Request $request)
    {
        // 1. OBTENCIÓN DE FILTROS NECESARIOS
        $tipo = $request->get('promocion');
        $estado = $request->get('estado'); 
        $categoria = $request->get('cmbCatId');
        $sucursal = $request->get('sucursal');
        $almacen = $request->get('almacen');
        $buspro = trim($request->get('buspro'));

        // 2. OBTENCIÓN DE DATOS COMPLEMENTARIOS
        $rucemp = trim(Auth::user()->IdEmpresa);
        $empresa = \MasterSoft\Empresa::findOrFail($rucemp);

        $categorias = DB::table('categorias')->get();
        $tipos_productos = DB::table('tipos_productos')->get();
        $negocios = DB::table('empresa_negocios')->get();
        $datos = DB::table('empresa_negocios')->where('id_empresa_negocio',$negocios->first()->id_empresa_negocio)->first();

        if(empty($sucursal)){
            $almacenes = DB::table('almacenes')->where('id_empresa_negocio',$negocios->first()->id_empresa_negocio)->get();
        }else{
            $almacenes = DB::table('almacenes')->where('id_empresa_negocio',$sucursal)->get();
        }
        
        // 3. LÓGICA DE OBTENCIÓN DE DATOS DEL STOCK
        $productos = DB::table('productos as p')
        ->select(
            'producto_stock.lote','producto_stock.vencimiento','producto_stock.stock_inicial','producto_stock.stock','producto_stock.stock_equivalencia','p.procod','p.pronom','m.monnom','u.umenom','p.propun','p.proest','producto_stock.IdProducto','mar.mar_nom as marca','p.costo','p.propun',
            'cat.cat_nom as categoria', 
            DB::raw("(SELECT precio FROM producto_empresa  WHERE producto_empresa.IdProducto=p.IdProducto and id_empresa_negocio='".$sucursal."') as precio"),
            DB::raw("(SELECT precio2 FROM producto_empresa  WHERE producto_empresa.IdProducto=p.IdProducto and id_empresa_negocio='".$sucursal."') as precio2"),
            DB::raw("(SELECT precio3 FROM producto_empresa  WHERE producto_empresa.IdProducto=p.IdProducto and id_empresa_negocio='".$sucursal."') as precio3")
         )
        ->leftjoin('moneda as m','p.moncod','=','m.moncod')
        ->leftjoin('marcas as mar','mar.mar_id','=','p.marca')
        ->leftjoin('unidad_medida as u','p.umecod','=','u.umecod')
        ->leftjoin('producto_stock','producto_stock.IdProducto','p.IdProducto')
        ->leftjoin('categorias as cat', 'p.cat_id', '=', 'cat.cat_id') 
        ->where(function ($query1) use($buspro) {
            if(!empty($buspro)){
                $query1->where('p.pronom','like','%'.$buspro.'%')
                       ->orwhere('p.procod',$buspro);
            }
        })
        ->where(function ($query1) use($estado) {
            if($estado=='cs'){
                $query1->where('producto_stock.stock','>','0');
            }elseif($estado=='se'){
                $query1->where('producto_stock.stock','=','0');
            }elseif($estado=='ne'){
                $query1->where('producto_stock.stock','<','0');
            }
        })
        ->where(function ($queryCat) use ($categoria) {
            if ($categoria != 'Todos' && !empty($categoria)) {
                $queryCat->where('p.cat_id', $categoria); 
            }
        })
        ->where('producto_stock.id_empresa_negocio',$sucursal)
        ->where('p.tipo','1') 
        ->where(function ($query) { 
            $query->where('p.promocion','0') 
                  ->orWhere('p.promocion','4');
        })
        ->where('producto_stock.id_almacen',$almacen) 
        ->groupby('p.IdProducto')
        ->orderby('p.pronom','asc') 
        ->get();

        $datosalm = DB::table('almacenes')->where('id_almacen',$almacen)->first();
        
        // 4. GENERACIÓN DEL PDF
        $nompdffile='STOCK_PRODUCTOS_'.$datos->IdEmpresa.'.pdf';  
        $rutapdf = public_path().'/reporte_cuentas_cobrar/';

        if(file_exists($rutapdf.$nompdffile)){
            unlink($rutapdf.$nompdffile);
        }
        ini_set("pcre.backtrack_limit", "5000000");

        $pdf = \PDF::loadView('formatos_reportes.reporte_pdf_stock_productos',compact('productos','buspro','negocios','categorias','tipos_productos','categoria','tipo','sucursal','datos','almacenes','almacen','datosalm','empresa'));
        
        return $pdf->stream('document.pdf');
    }

    public function ImprimirStockTicketVista(Request $request)
    {
        $tipo = $request->get('promocion');
        $estado = $request->get('estado'); 
        $categoria = $request->get('cmbCatId');
        $sucursal = $request->get('sucursal');
        $almacen = $request->get('almacen');
        $buspro = trim($request->get('buspro'));

        $rucemp = trim(Auth::user()->IdEmpresa);
        $empresa = \MasterSoft\Empresa::findOrFail($rucemp);

        // LÓGICA DE STOCK 
        $productos = DB::table('productos as p')
        ->select(
            'producto_stock.stock', 'producto_stock.stock_equivalencia', 'p.procod', 'p.pronom',
            'cat.cat_nom as categoria' // <--- AÑADIDO ESTE CAMPO AQUÍ
         )
        ->leftjoin('producto_stock','producto_stock.IdProducto','p.IdProducto')
        ->leftjoin('categorias as cat', 'p.cat_id', '=', 'cat.cat_id') 
        ->where(function ($query1) use($buspro) {
            if(!empty($buspro)){
                $query1->where('p.pronom','like','%'.$buspro.'%')
                       ->orwhere('p.procod',$buspro);
            }
        })
        ->where(function ($query1) use($estado) {
            if($estado=='cs'){
                $query1->where('producto_stock.stock','>','0');
            }elseif($estado=='se'){
                $query1->where('producto_stock.stock','=','0');
            }elseif($estado=='ne'){
                $query1->where('producto_stock.stock','<','0');
            }
        })
        ->where(function ($queryCat) use ($categoria) {
            if ($categoria != 'Todos' && !empty($categoria)) {
                $queryCat->where('p.cat_id', $categoria); 
            }
        })
        ->where('producto_stock.id_empresa_negocio',$sucursal)
        ->where('p.tipo','1') 
        ->where(function ($query) { 
            $query->where('p.promocion','0') 
                  ->orWhere('p.promocion','4');
        })
        ->where('producto_stock.id_almacen',$almacen) 
        ->groupby('p.IdProducto')
        ->orderby('p.pronom','asc') 
        ->get();

        $datosalm = DB::table('almacenes')->where('id_almacen',$almacen)->first();

        return view('formatos_reportes.ticket_stock_vps', compact('productos', 'empresa', 'datosalm'));
    }

     public function ExportarStockValorizado(Request $request)
    {

          $rucemp = trim(Auth::user()->IdEmpresa); 
     
         $productos= productos::select('productos.procod as CODIGO PRODUCTO','productos.pronom AS PRODUCTO','u.umenom as UNIDAD MEDIDA','productos.stockinicial as STOCK INICIAL','productos.stock as STOCK ACTUAL','productos.propun as PRECIO UNITARIO','productos.costo as COSTO',DB::raw('ROUND((productos.costo * productos.stock),2) as VALOR_INVENTARIO'))
                ->leftjoin('unidad_medida as u','productos.umecod','=','u.umecod')
                ->where('productos.IdEmpresa','=',$rucemp)
                ->where('productos.id_empresa_negocio',Auth::user()->id_empresa_negocio)
                 ->where(function ($query){
                        $query->where('promocion','=','0')
                              ->orWhere('promocion','=','4');
                })
                ->orderby('pronom','asc')
                ->get();
        
                  $total = productos::where('IdEmpresa','=',$rucemp)
                   ->where('productos.id_empresa_negocio',Auth::user()->id_empresa_negocio)
                    ->where(function ($query){
                            $query->where('promocion','=','0')
                                  ->orWhere('promocion','=','4');
                    })
                    ->sum(DB::raw('ROUND((productos.costo * productos.stock),2)'));

            
        Excel::create('Stock Valorizado', function($excel) use($productos,$total) {
        $excel->sheet('Stock Valorizado', function($sheet) use($productos,$total) {
    
        $sheet->fromArray($productos);

         $sheet->appendRow(array(
             'TOTAL VALOR INVENTARIO',$total
        ));


     });
        })->export('xlsx');





    }
   
    public function pdf_reporte_cuentas_cobrar_vendedor(Request $request){

           $vendedor = $request->get('vendedor');

             $clientes = DB::tABLE('cliente')->select('cliente.clicod','clinom','clinum','clidir')
          ->join('cpe_cabecera','cpe_cabecera.clicod','cliente.clicod')
          ->where('IdUsuario_ven',$vendedor)
          ->where('cliente.rucemp',Auth::user()->IdEmpresa)
          ->distinct()
          ->orderby('clinom','asc')
          ->get();

          $data_emp = DB::tABLE('empresa')->where('IdEmpresa',Auth::user()->IdEmpresa)->first();

          $data_neg = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first();

         

          $data_vendedor = DB::tABLE('users')->where('IdUsuario',$vendedor)->first();

           $vendedores = DB::tABLE('users')
            ->join('role_user','role_user.user_IdUsuario','users.IdUsuario')
           //// ->where('role_id','5')
            ->where('IdEmpresa',Auth::user()->IdEmpresa)
            ->get();

            $fecin = $request->get('fecin');
            $fecfin = $request->get('fecfin');
            $placa = $request->get('placa');
            $tipdoc = $request->get('tipdoc');
            $numdoc = $request->get('numdoc');
            $clicod = $request->get('clicod');
            $estado = $request->get('estado');
            $tipo = $request->get('tipfec');

            if(empty($tipo)){
                $tipo='0';
            }

            if(empty($fecin)){

                $fecin = now()->modify('first day of this month')->format('Y-m-d');
                $fecfin = now()->modify('last day of this month')->format('Y-m-d');
            }

            $cuentas = DB::tABLE('cuentas_cobrar')
            ->leftjoin('cpe_cabecera','cpe_cabecera.IdCpe_cabecera','cuentas_cobrar.IdCpe_cabecera')
            ->leftjoin('cliente','cliente.clicod','cuentas_cobrar.clicod')
            ->whereNull('cpe_cabecera.ccabaj')
            ->where('cuentas_cobrar.id_empresa_negocio',Auth::user()->id_empresa_negocio)
            ->where(function($query) use($vendedor) {
                   
                    
                     $query->where('cpe_cabecera.IdUsuario_ven',$vendedor);

                    
                   
             })
     /*     ->where(function($query1) use ($fecin, $fecfin,$tipo)
          {
              if($tipo=='0'){
                $query1->where('fec_ven','>=',$fecin)
                      ->where('fec_ven','<=',$fecfin);
              }elseif($tipo=='1'){


                $query1->where('ccafem','>=',$fecin)
                      ->where('ccafem','<=',$fecfin);
              }
            
          })*/
            ->orderby('cuentas_cobrar.fec_ven','asc')
            ->get();

      $nompdffile='cuentas_cobrar_vendedor_'.now()->format('Y-m-d').'.pdf'; 


       $rutapdf = public_path().'/reporte_cuentas_cobrar/';

       if(file_exists($rutapdf.$nompdffile)){
        unlink($rutapdf.$nompdffile);
       }

      $view = \View::make('formatos_reportes.reporte_pdf_cuentas_cobrar_vendedor', compact('data_vendedor','cuentas','clientes','data_emp','data_neg','vendedores','vendedor'));

                  
      $pdf = \App::make('dompdf.wrapper');
      $contenido = $view->render();
      $pdf->loadHTML($view)->setPaper('A4', 'lanscape')->save($rutapdf.$nompdffile);

     
      $headers = array(
              'Content-Type: application/pdf',
            );

      return response()->download($rutapdf.$nompdffile);
  
 
     //   return view('empresas.reportes.cuentascobrar',compact('date_emp','data_neg','clientes','cuentas','fecin','fecfin','clicod','tipo'));



    }


    public function pdf_reporte_cobranzas_vendedor(Request $request){

           $vendedor = $request->get('vendedor');

             $clientes = DB::tABLE('cliente')->select('cliente.clicod','clinom','clinum','clidir')
          ->join('cuentas_cobrar','cuentas_cobrar.clicod','cliente.clicod')
          ->where(DB::raw("(SELECT count(*) FROM cuentas_cobrar_detalle WHERE cue_cob_id = cuentas_cobrar.cue_cob_id)"),'>','0')
          ->where('cliente.rucemp',Auth::user()->IdEmpresa)
          ->distinct()
          ->orderby('clinom','asc')
          ->get();

          $data_emp = DB::tABLE('empresa')->where('IdEmpresa',Auth::user()->IdEmpresa)->first();

          $data_neg = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first();

         

          $data_vendedor = DB::tABLE('users')->where('IdUsuario',$vendedor)->first();

           $vendedores = DB::tABLE('users')
            ->join('role_user','role_user.user_IdUsuario','users.IdUsuario')
           // ->where('role_id','5')
            ->where('IdEmpresa',Auth::user()->IdEmpresa)
            ->get();

            $fecin = $request->get('fecin');
            $fecfin = $request->get('fecfin');
            $placa = $request->get('placa');
            $tipdoc = $request->get('tipdoc');
            $numdoc = $request->get('numdoc');
            $clicod = $request->get('clicod');
            $estado = $request->get('estado');
            $tipo = $request->get('tipfec');

            if(empty($tipo)){
                $tipo='0';
            }

            if(empty($fecin)){

                $fecin = now()->modify('first day of this month')->format('Y-m-d');
                $fecfin = now()->modify('last day of this month')->format('Y-m-d');
            }

            $cuentas = DB::tABLE('cuentas_cobrar_detalle')->select('fec_dep','cuentas_cobrar_detalle.abono','comentario','numero_recibo',DB::raw("(SELECT CONCAT(name,apeusu) FROM users WHERE IdUsuario= cuentas_cobrar_detalle.vendedor) as vendedor"),DB::raw("(SELECT clinom FROM cliente WHERE clicod = cuentas_cobrar.clicod) as cliente"),DB::raw("(SELECT clinum FROM cliente WHERE clicod = cuentas_cobrar.clicod) as ruc"),'serdoc','numdoc','cuentas_cobrar.clicod','total_detalle','saldo_detalle')
            ->join('cuentas_cobrar','cuentas_cobrar.cue_cob_id','cuentas_cobrar_detalle.cue_cob_id')
            ->join('cpe_cabecera','cpe_cabecera.IdCpe_cabecera','cuentas_cobrar.IdCpe_cabecera')
           // ->whereNull('cpe_cabecera.ccabaj')
            ->where('cuentas_cobrar.id_empresa_negocio',Auth::user()->id_empresa_negocio)
            ->where(function($query) use($vendedor) {
                   
                    
                     $query->where('cuentas_cobrar_detalle.vendedor',$vendedor);

                    
                   
             })
            ->where('fec_dep','>=',$fecin)
            ->where('fec_dep','<=',$fecfin)
     /*     ->where(function($query1) use ($fecin, $fecfin,$tipo)
          {
              if($tipo=='0'){
                $query1->where('fec_ven','>=',$fecin)
                      ->where('fec_ven','<=',$fecfin);
              }elseif($tipo=='1'){


                $query1->where('ccafem','>=',$fecin)
                      ->where('ccafem','<=',$fecfin);
              }
            
          })*/
            ->orderby('cliente','asc')
            ->get();

//dd($cuentas);
      $nompdffile='cuentas_cobrar_vendedor_'.now()->format('Y-m-d').'.pdf'; 


       $rutapdf = public_path().'/reporte_cuentas_cobrar/';

       if(file_exists($rutapdf.$nompdffile)){
        unlink($rutapdf.$nompdffile);
       }

      $view = \View::make('formatos_reportes.reporte_pdf_cobranzas_vendedor', compact('data_vendedor','cuentas','clientes','data_emp','data_neg','vendedores','fecin','fecfin'));

                  
      $pdf = \App::make('dompdf.wrapper');
      $contenido = $view->render();
      $pdf->loadHTML($view)->setPaper('A4', 'lanscape')->save($rutapdf.$nompdffile);


      $headers = array(
              'Content-Type: application/pdf',
            );

      return response()->download($rutapdf.$nompdffile);
  
 
     //   return view('empresas.reportes.cuentascobrar',compact('date_emp','data_neg','clientes','cuentas','fecin','fecfin','clicod','tipo'));



    } 


public function pdf_reporte_cobranzas_clientes(Request $request){

           $clicod = $request->get('clicod');

             $clientes = DB::tABLE('cliente')->select('cliente.clicod','clinom','clinum','clidir')
          ->join('cuentas_cobrar','cuentas_cobrar.clicod','cliente.clicod')
          ->where(DB::raw("(SELECT count(*) FROM cuentas_cobrar_detalle WHERE cue_cob_id = cuentas_cobrar.cue_cob_id)"),'>','0')
          ->where('cliente.rucemp',Auth::user()->IdEmpresa)
          ->distinct()
          ->orderby('clinom','asc')
          ->get();

          $data_emp = DB::tABLE('empresa')->where('IdEmpresa',Auth::user()->IdEmpresa)->first();

          $data_neg = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first();

         

          $data_cliente = DB::tABLE('cliente')->where('clicod',$clicod)->first();

         
            $fecin = $request->get('fecin');
            $fecfin = $request->get('fecfin');
            $placa = $request->get('placa');
            $tipdoc = $request->get('tipdoc');
            $numdoc = $request->get('numdoc');
            $clicod = $request->get('clicod');
            $estado = $request->get('estado');
            $tipo = $request->get('tipfec');

            if(empty($tipo)){
                $tipo='0';
            }

            if(empty($fecin)){

                $fecin = now()->modify('first day of this month')->format('Y-m-d');
                $fecfin = now()->modify('last day of this month')->format('Y-m-d');
            }

            $cuentas = DB::tABLE('cuentas_cobrar_detalle')->select('fec_dep','cuentas_cobrar_detalle.abono','comentario','numero_recibo',DB::raw("(SELECT CONCAT(name,apeusu) FROM users WHERE IdUsuario= cuentas_cobrar_detalle.vendedor) as vendedor"),DB::raw("(SELECT clinom FROM cliente WHERE clicod = cuentas_cobrar.clicod) as cliente"),DB::raw("(SELECT clinum FROM cliente WHERE clicod = cuentas_cobrar.clicod) as ruc"),'serdoc','numdoc','cuentas_cobrar.clicod','total_detalle','saldo_detalle')
            ->join('cuentas_cobrar','cuentas_cobrar.cue_cob_id','cuentas_cobrar_detalle.cue_cob_id')
            ->join('cpe_cabecera','cpe_cabecera.IdCpe_cabecera','cuentas_cobrar.IdCpe_cabecera')
           // ->whereNull('cpe_cabecera.ccabaj')
            ->where('cuentas_cobrar.id_empresa_negocio',Auth::user()->id_empresa_negocio)
            ->where(function($query) use($clicod) {
                   
                    
                     $query->where('cuentas_cobrar.clicod',$clicod);

                    
                   
             })
            ->where('fec_dep','>=',$fecin)
             ->where('fec_dep','<=',$fecfin)
     /*     ->where(function($query1) use ($fecin, $fecfin,$tipo)
          {
              if($tipo=='0'){
                $query1->where('fec_ven','>=',$fecin)
                      ->where('fec_ven','<=',$fecfin);
              }elseif($tipo=='1'){


                $query1->where('ccafem','>=',$fecin)
                      ->where('ccafem','<=',$fecfin);
              }
            
          })*/
            ->orderby('cuentas_cobrar_detalle.cue_cob_det_id','asc')
            ->get();

//dd($cuentas);
      $nompdffile='cobrazas_clientes'.now()->format('Y-m-d').'.pdf'; 


       $rutapdf = public_path().'/reporte_cuentas_cobrar/';

       if(file_exists($rutapdf.$nompdffile)){
        unlink($rutapdf.$nompdffile);
       }

      $view = \View::make('formatos_reportes.reporte_pdf_cobranzas_clientes', compact('data_cliente','cuentas','clientes','data_emp','data_neg'));

                  
      $pdf = \App::make('dompdf.wrapper');
      $contenido = $view->render();
      $pdf->loadHTML($view)->setPaper('A4', 'lanscape')->save($rutapdf.$nompdffile);


      $headers = array(
              'Content-Type: application/pdf',
            );

      return response()->download($rutapdf.$nompdffile);
  
 
     //   return view('empresas.reportes.cuentascobrar',compact('date_emp','data_neg','clientes','cuentas','fecin','fecfin','clicod','tipo'));



    } 
     public function pdf_reporte_cuentas_cobrar_cliente(Request $request){

           $vendedor = $request->get('vendedor');

           $cliente = $request->get('clicod');

           $data_cliente = DB::tABLE('cliente')->where('clicod',$cliente)->first();

          $clientes = DB::tABLE('cliente')
          ->where('clicod',$cliente)
          ->where('cliente.rucemp',Auth::user()->IdEmpresa)
          ->get();

          $data_emp = DB::tABLE('empresa')->where('IdEmpresa',Auth::user()->IdEmpresa)->first();

          $data_neg = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first();

         

          $data_vendedor = DB::tABLE('users')->where('IdUsuario',$vendedor)->first();

           $vendedores = DB::tABLE('users')
            ->join('role_user','role_user.user_IdUsuario','users.IdUsuario')
           // ->where('role_id','5')
             ->where('IdEmpresa',Auth::user()->IdEmpresa)
            ->get();

            $fecin = $request->get('fecin');
            $fecfin = $request->get('fecfin');
            $placa = $request->get('placa');
            $tipdoc = $request->get('tipdoc');
            $numdoc = $request->get('numdoc');
            $clicod = $request->get('clicod');
            $estado = $request->get('estado');
            $tipo = $request->get('tipfec');

            if(empty($tipo)){
                $tipo='0';
            }

            if(empty($fecin)){

                $fecin = now()->modify('first day of this month')->format('Y-m-d');
                $fecfin = now()->modify('last day of this month')->format('Y-m-d');
            }

            $cuentas = DB::tABLE('cuentas_cobrar')
            ->leftjoin('cpe_cabecera','cpe_cabecera.IdCpe_cabecera','cuentas_cobrar.IdCpe_cabecera')
            ->leftjoin('cliente','cliente.clicod','cuentas_cobrar.clicod')
            ->where('cuentas_cobrar.id_empresa_negocio',Auth::user()->id_empresa_negocio)
            ->where(function($query) use($cliente) {
                   
                    
                     $query->where('cpe_cabecera.clicod',$cliente);

                    
                   
             })
     /*     ->where(function($query1) use ($fecin, $fecfin,$tipo)
          {
              if($tipo=='0'){
                $query1->where('fec_ven','>=',$fecin)
                      ->where('fec_ven','<=',$fecfin);
              }elseif($tipo=='1'){


                $query1->where('ccafem','>=',$fecin)
                      ->where('ccafem','<=',$fecfin);
              }
            
          })*/
            ->orderby('cuentas_cobrar.fec_ven','asc')
            ->get();

      $nompdffile='cuentas_cobrar_cliente_'.now()->format('Y-m-d').'.pdf'; 


       $rutapdf = public_path().'/reporte_cuentas_cobrar/';

       if(file_exists($rutapdf.$nompdffile)){
        unlink($rutapdf.$nompdffile);
       }

      $view = \View::make('formatos_reportes.reporte_pdf_cuentas_cobrar_cliente', compact('data_cliente','data_vendedor','cuentas','clientes','data_emp','data_neg','vendedores'));

                  
      $pdf = \App::make('dompdf.wrapper');
      $contenido = $view->render();
      $pdf->loadHTML($view)->setPaper('A4', 'lanscape')->save($rutapdf.$nompdffile);


      $headers = array(
              'Content-Type: application/pdf',
            );

      return response()->download($rutapdf.$nompdffile);
  
 
     //   return view('empresas.reportes.cuentascobrar',compact('date_emp','data_neg','clientes','cuentas','fecin','fecfin','clicod','tipo'));



    }

     public function pdf_reporte_resumen_ventas_productos(Request $request){

            /*  $registros = DB::tABLE('cpe_detalle')->get();
              foreach ($registros as $reg) {
                DB::tABLE('cpe_detalle')->where('IdCpe_detalle',$reg->IdCpe_detalle)->update(['procod'=>trim($reg->procod)]);
              }*/

              $fecin = $request->get('fecin');
              $fecfin = $request->get('fecfin');
              $clicod = $request->get('clicod');
              $idsucursal = $request->get('sucursal');
              $almacen = $request->get('almacen');

              $empresa = DB::tABLE('empresa')->where('IdEmpresa',Auth::user()->IdEmpresa)->first();

              $sucursal = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first();
              $datocli = DB::tABLE('cliente')->where('clicod',$clicod)->first();

                $productos = cpe_cabecera::select(DB::RAW('sum(cpe_detalle.cdecan*cpe_det_factor) as cantidad'),'cdedes',DB::RAW('sum(cpe_detalle.cdevve) as precio'),'cpe_detalle.procod','umecod')
                ->leftjoin('cpe_detalle','cpe_detalle.IdCpe_cabecera','cpe_cabecera.IdCpe_cabecera')
 
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                ->whereNull('cpe_cabecera.ccabaj')
                ->where('cdecan','>','0')
                 ->where(function ($query) {
                  $query->Where('cpe_cabecera.tdocod','03')
                        ->orWhere('cpe_cabecera.tdocod','01')
                        ->orWhere('cpe_cabecera.tdocod','13');
                      
                  })
                ->where(function ($query1) use ($clicod) {
                    if($clicod !=0){
                       $query1->Where('cpe_cabecera.clicod',$clicod);
                    }
                  })
                ->where(function ($query5) use ($idsucursal) {
                  if($idsucursal !=0 && !empty($idsucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$idsucursal);
                  }
                 
                  
                }) 
                
                ->where(function ($query6) use ($almacen) {
                  if($almacen !=0 && !empty($almacen)){
                      $query6->where('cpe_cabecera.id_almacen',$almacen);
                  }                
                }) 
                ->orderby('cdedes','desc')
                ->groupby('procod')
                ->get();

                $totalproductos = $productos->sum('cantidad');

                $total =0;
                foreach ($productos as $pro) {
                   $total = $total + ($pro->precio);
                }

                $nompdffile='resumen_ventas_productos_'.$sucursal->IdEmpresa.'_'.now()->format('Y-m-d').'.pdf'; 


                 $rutapdf = public_path().'/reporte_cuentas_cobrar/';

                 if(file_exists($rutapdf.$nompdffile)){
                  unlink($rutapdf.$nompdffile);
                 }

                $view = \View::make('formatos_reportes.reporte_pdf_resumen_ventas_productos', compact('productos','fecin','fecfin','total','totalproductos','total','empresa','sucursal','idsucursal'));

                            
                $pdf = \App::make('dompdf.wrapper');
                $contenido = $view->render();
                $pdf->loadHTML($view)->setPaper('A4', 'lanscape')->save($rutapdf.$nompdffile);


                $headers = array(
                        'Content-Type: application/pdf',
                      );

                return response()->download($rutapdf.$nompdffile);
  
 

    }

       public function pdf_reporte_ventas_vendedor(Request $request){

                
                $sucursal = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',$request->get('sucursal'))->first();
                $almacen = DB::tABLE('almacenes')->where('id_almacen',$request->get('almacen'))->first();
                $empresa = DB::tABLE('empresa')->where('IdEmpresa',$sucursal->IdEmpresa)->first();

                $fecin = $request->get('fecin');
                $fecfin = $request->get('fecfin');
                $vendedor = $request->get('vendedor');
                $idsucursal = $request->get('sucursal');
                $idalmacen = $request->get('almacen');

                $dato_vendedor = DB::tABLE('users')->where('IdUsuario',$vendedor)->first();

                $comprobantes = cpe_cabecera::select('cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','det.cdedes as producto','det.cdecan as cantidad','cdepuni as precio','monnom as moneda','det.cdevve as total','cpe_cabecera.ccabaj as baja','det.costo','ccasunrescod','tdides as documentoidentidad','ccandi as numerodocumento','procod','cdedes','cdecan','cdepuni','cdevve','umecod','ccaitv','id_almacen_pro')
                ->leftjoin('cpe_detalle as det','cpe_cabecera.IdCpe_cabecera','det.IdCpe_cabecera')
                ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                 ->where(function ($query) use ($vendedor) {
                      if($vendedor !='Todos'){
                         $query->Where('cpe_cabecera.IdUsuario_ven',$vendedor);
                      }
                    })
                ->where(function ($query) {
                  $query->where('cpe_cabecera.tdocod','01')
                    ->orWhere('cpe_cabecera.tdocod','03')
                    ->orWhere('cpe_cabecera.tdocod','13')
                    ->orWhere('cpe_cabecera.tdocod','14')
                    ->orWhere('cpe_cabecera.tdocod','07')
                    ;
                  }) 
                    ->where(function ($query5) use ($idsucursal) {
                  if($idsucursal !=0 && !empty($idsucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$idsucursal);
                  }
                 
                  
                }) 
                
               /* ->where(function ($query6) use ($idalmacen) {
                  if($idalmacen !='Todos' && !empty($idalmacen)){
                      $query6->where('det.id_almacen_pro',$idalmacen);
                  }                
                })   */ 
                 ->orderBy('IdCpe_cabecera','desc')->get();




                 $cabecera = DB::tABLE('cpe_cabecera')
             //    ->join('cpe_detalle','cpe_detalle.IdCpe_cabecera','cpe_cabecera.IdCpe_cabecera')
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                 ->where(function ($query) use ($vendedor) {
                      if($vendedor !='Todos'){
                         $query->Where('cpe_cabecera.IdUsuario_ven',$vendedor);
                      }
                    })
                ->where(function ($query) {
                  $query->where('cpe_cabecera.tdocod','01')
                    ->orWhere('cpe_cabecera.tdocod','03')
                    ->orWhere('cpe_cabecera.tdocod','13')
                    ->orWhere('cpe_cabecera.tdocod','14')
                    ->orWhere('cpe_cabecera.tdocod','07')
                    ;
                  }) 
                    ->where(function ($query5) use ($idsucursal) {
                  if($idsucursal !=0 && !empty($idsucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$idsucursal);
                  }
                 
                  
                }) 
                
               /* ->where(function ($query6) use ($idalmacen) {
                  if($idalmacen !='Todos' && !empty($idalmacen)){
                      $query6->where('cpe_detalle.id_almacen_pro',$idalmacen);
                  }                
                }) */
               // ->groupby('cpe_cabecera.IdCpe_cabecera') 
                 ->get();

                 $total = $cabecera->where('ccanot','')->sum('ccaitv');

                 $totalnotas = $cabecera->where('tdocod','7')->sum('ccaitv');

                 /*---------------------------------------REPORTE VENTAS POR PRODUCTO RESUMEN------------------------------------------------------*/


                $productos = cpe_cabecera::select(DB::RAW('sum(cpe_detalle.cdecan*cpe_det_factor) as cantidad'),'cdedes',DB::RAW('sum(cpe_detalle.cdevve) as precio'),'cpe_detalle.procod','umecod')
                ->leftjoin('cpe_detalle','cpe_detalle.IdCpe_cabecera','cpe_cabecera.IdCpe_cabecera')
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                ->whereNull('cpe_cabecera.ccabaj')
                ->where('cdecan','>','0')
                ->where(function ($query) use ($vendedor) {
                      if($vendedor !='Todos'){
                         $query->Where('cpe_cabecera.IdUsuario_ven',$vendedor);
                      }
                })
                ->where(function ($query) {
                  $query->where('cpe_cabecera.tdocod','01')
                    ->orWhere('cpe_cabecera.tdocod','03')
                    ->orWhere('cpe_cabecera.tdocod','13')
                    ->orWhere('cpe_cabecera.tdocod','14')
                    ->orWhere('cpe_cabecera.tdocod','07')
                    ;
                  })   
                ->where(function ($query5) use ($idsucursal) {
                  if($idsucursal !=0 && !empty($idsucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$idsucursal);
                  }
                 
                  
                }) 
                
               /* ->where(function ($query6) use ($idalmacen) {
                  if($idalmacen !='Todos'){
                      $query6->where('cpe_detalle.id_almacen_pro',$idalmacen);
                  }                
                }) */
                ->orderby('cdedes','desc')
                ->groupby('procod')
                ->get();


                     $productosnot = cpe_cabecera::select(DB::RAW('sum(cpe_detalle.cdecan*cpe_det_factor) as cantidad'),'cdedes',DB::RAW('sum(cpe_detalle.cdevve) as precio'),'cpe_detalle.procod','umecod')
                ->leftjoin('cpe_detalle','cpe_detalle.IdCpe_cabecera','cpe_cabecera.IdCpe_cabecera')
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                ->whereNull('cpe_cabecera.ccabaj')
                ->where('cdecan','>','0')
                ->where(function ($query) use ($vendedor) {
                      if($vendedor !='Todos'){
                         $query->Where('cpe_cabecera.IdUsuario_ven',$vendedor);
                      }
                })
                ->where(function ($query) {
                  $query->Where('cpe_cabecera.tdocod','07')
                    ;
                  })   
                ->where(function ($query5) use ($idsucursal) {
                  if($idsucursal !=0 && !empty($idsucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$idsucursal);
                  }
                 
                  
                }) 
                
              /*  ->where(function ($query6) use ($idalmacen) {
                  if($idalmacen !='Todos' ){
                      $query6->where('cpe_detalle.id_almacen_pro',$idalmacen);
                  }                
                }) */
                ->orderby('cdedes','desc')
                ->groupby('procod')
                ->get();

                $productosnotas = $productosnot->sum('cantidad');

                $totalproductos = $productos->sum('cantidad');

                $totalmontoproductos =0;
               
                foreach ($productos as $pro) {
                   $totalmontoproductos = $totalmontoproductos + ($pro->precio);
                }


                 /*---------------------------------------------------------------------------------------------------------------------------------*/

                 $nompdffile='resumen_ventas_vendedor_productos_'.$sucursal->IdEmpresa.'_'.now()->format('Y-m-d').'.pdf'; 


                 $rutapdf = public_path().'/reporte_cuentas_cobrar/';

                 if(file_exists($rutapdf.$nompdffile)){
                  unlink($rutapdf.$nompdffile);
                 }

                $view = \View::make('formatos_reportes.reporte_pdf_ventas_vendedor_productos', compact('totalmontoproductos','totalnotas','total','dato_vendedor','cabecera','comprobantes','fecin','fecfin','empresa','sucursal','almacen','productos','productosnotas'));

                            
                $pdf = \App::make('dompdf.wrapper');
                $contenido = $view->render();
                $pdf->loadHTML($view)->setPaper('A4', 'lanscape')->save($rutapdf.$nompdffile);


                $headers = array(
                        'Content-Type: application/pdf',
                      );

                return response()->download($rutapdf.$nompdffile);
  
 

    }
    
    public function reporte_inventario(Request $request)
    {
        

          $sucursal = $request->get('sucursal');
          $almacen = $request->get('almacen');
          $fec_ini = $request->get('fec_ini');
          $fec_fin = $request->get('fec_fin');
          $periodo = $request->get('periodo');
          $rucemp = trim(Auth::user()->IdEmpresa);
          $empresa = Empresa::findOrFail($rucemp);
          $saldos_actuales = [];


          $negocios = DB::tABLE('empresa_negocios')->get();
          $almacenes = DB::tABLE('almacenes')->where('id_empresa_negocio',$negocios->first()->id_empresa_negocio)->get();

          $dat_neg = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',$sucursal)->first();

          $dat_alm =  DB::tABLE('almacenes')->where('id_almacen',$almacen)->first(); 

          if(empty($fec_ini)){
            $fec_ini = now()->modify('first day of this month')->format('Y-m-d');
            $fec_fin = now()->modify('last day of this month')->format('Y-m-d');
          }



          if(!empty($sucursal)){

                $fec_ini = Carbon::parse($periodo)->firstOfMonth()->startOfDay()->format('Y-m-d');
                $fec_fin = Carbon::parse($periodo)->lastOfMonth()->endOfDay()->format('Y-m-d');
                $fec_sig = Carbon::parse($periodo)->addMonth()->startOfDay()->format('Y-m-d');

                 $inventario= DB::table("productos")
                  ->select("productos.*",

                            DB::raw("(SELECT SUM(cantidad) FROM movimientos_productos
                                        WHERE movimientos_productos.IdProducto_rel = productos.IdProducto
                                        AND mov_tip='I' and movimientos_productos.id_almacen='".$almacen."'
                                        and movimientos_productos.fecha_mov>='".$fec_ini."'
                                         and movimientos_productos.fecha_mov<='".$fec_fin."'
                                        GROUP BY movimientos_productos.IdProducto_rel) as Ingresos"),

                            DB::raw("(SELECT SUM(cantidad) FROM movimientos_productos
                                        WHERE movimientos_productos.IdProducto_rel = productos.IdProducto
                                        AND mov_tip='E' and movimientos_productos.id_almacen='".$almacen."'
                                         and movimientos_productos.fecha_mov>='".$fec_ini."'
                                         and movimientos_productos.fecha_mov<='".$fec_fin."'
                                        GROUP BY movimientos_productos.IdProducto_rel) as Egresos"))
                  //->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
                      // ->having('contar','>','0')
                  ->where('productos.tipo','1')
                  ->get();


                  }

      
      
            return view('empresas.reportes.buscar_inventario',compact('periodo','dat_alm','negocios','sucursal','dat_neg','almacenes','almacen','dat_alm','fec_ini','fec_fin','inventario'));

    }


     public function reporte_inventario_pdf(Request $request)
    {
        
          $sucursal = $request->get('sucursal');
          $almacen = $request->get('almacen');
          $fec_ini = $request->get('fec_ini');
          $fec_fin = $request->get('fec_fin');
          $periodo = $request->get('periodo');
          $rucemp = trim(Auth::user()->IdEmpresa);
          $empresa = Empresa::findOrFail($rucemp);
          $saldos_actuales = [];

          $dat_neg = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',$sucursal)->first();

        

          $dat_alm =  DB::tABLE('almacenes')->where('id_almacen',$almacen)->first(); 
          
          $fec_ini = Carbon::parse($periodo)->firstOfMonth()->startOfDay()->format('Y-m-d');
          $fec_fin = Carbon::parse($periodo)->lastOfMonth()->endOfDay()->format('Y-m-d');
          $fec_sig = Carbon::parse($periodo)->addMonth()->startOfDay()->format('Y-m-d');



          $inventario= DB::table("productos")
          ->select("productos.*",


                    DB::raw("(SELECT SUM(cantidad) FROM movimientos_productos
                                WHERE movimientos_productos.IdProducto_rel = productos.IdProducto
                                AND mov_tip='I' and movimientos_productos.id_almacen='".$almacen."'
                                and movimientos_productos.fecha_mov>='".$fec_ini."'
                                 and movimientos_productos.fecha_mov<='".$fec_fin."'
                                GROUP BY movimientos_productos.IdProducto_rel) as Ingresos"),

                    DB::raw("(SELECT SUM(cantidad) FROM movimientos_productos
                                WHERE movimientos_productos.IdProducto_rel = productos.IdProducto
                                AND mov_tip='E' and movimientos_productos.id_almacen='".$almacen."'
                                 and movimientos_productos.fecha_mov>='".$fec_ini."'
                                 and movimientos_productos.fecha_mov<='".$fec_fin."'
                                GROUP BY movimientos_productos.IdProducto_rel) as Egresos"))
          //->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
              // ->having('contar','>','0')
          ->where('productos.tipo','1')
          ->get();


 

       
      
      //  dd($saldos_actuales);
         return view('formatos_reportes.reporte_pdf_inventario', compact('dat_alm','dat_neg','fec_ini','fec_fin','inventario','periodo','empresa'));

        /*  $nompdffile = 'REPORTE_INVENTARIO.pdf';

        $rutapdf = public_path().'/pdfreportes/';

        $view = \View::make('formatos_reportes.reporte_pdf_inventario', compact('dat_alm','empresa','negocios','sucursal','dat_neg','almacenes','almacen','fec_ini','fec_fin','saldos_actuales'));


                  
          $pdf = \App::make('dompdf.wrapper');
          $contenido = $view->render();
          $pdf->loadHTML($view)->setPaper('A4', 'lanscape')->save($rutapdf.$nompdffile);


      
       if (file_exists($rutapdf.$nompdffile))
      {
        $headers = array(
              'Content-Type: application/pdf',
            );

        return response()->download($rutapdf.$nompdffile);

     }*/

    }

    public function reporte_inventario_excel(Request $request)
    {
        

          $sucursal = $request->get('sucursal');
          $almacen = $request->get('almacen');
          $fec_ini = $request->get('fec_ini');
          $fec_fin = $request->get('fec_fin');
          $rucemp = trim(Auth::user()->IdEmpresa);
          $empresa = Empresa::findOrFail($rucemp);
          $saldos_actuales = [];

           $periodo = $request->get('periodo');

          $fec_ini = Carbon::parse($periodo)->firstOfMonth()->startOfDay()->format('Y-m-d');
          $fec_fin = Carbon::parse($periodo)->lastOfMonth()->endOfDay()->format('Y-m-d');
          $fec_sig = Carbon::parse($periodo)->addMonth()->startOfDay()->format('Y-m-d');


          $dat_neg = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',$sucursal)->first();

        

          $dat_alm =  DB::tABLE('almacenes')->where('id_almacen',$almacen)->first(); 
  

          $inventario= DB::table("productos")
          ->select("productos.*",
                    DB::raw("(SELECT SUM(cantidad) FROM movimientos_productos
                                WHERE movimientos_productos.IdProducto_rel = productos.IdProducto
                                AND mov_tip='I' and movimientos_productos.id_almacen='".$almacen."'
                                and movimientos_productos.fecha_mov>='".$fec_ini."'
                                 and movimientos_productos.fecha_mov<='".$fec_fin."'
                                GROUP BY movimientos_productos.IdProducto_rel) as Ingresos"),

                    DB::raw("(SELECT SUM(cantidad) FROM movimientos_productos
                                WHERE movimientos_productos.IdProducto_rel = productos.IdProducto
                                AND mov_tip='E' and movimientos_productos.id_almacen='".$almacen."'
                                 and movimientos_productos.fecha_mov>='".$fec_ini."'
                                 and movimientos_productos.fecha_mov<='".$fec_fin."'
                                GROUP BY movimientos_productos.IdProducto_rel) as Egresos"))
          //->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
              // ->having('contar','>','0')
          ->where('productos.tipo','1')
          ->get();


 
                     Excel::create('Reporte_Inventario', function($excel) use ($dat_alm,$dat_neg,$fec_ini,$fec_fin,$inventario) {

                        $excel->sheet('Inventarios', function($sheet) use ($dat_alm,$dat_neg,$fec_ini,$fec_fin,$inventario) {


                                  $sheet->loadView('formatos_reportes_excel.inventario',compact('periodo','dat_alm','dat_neg','fec_ini','fec_fin','inventario'));
                              

                                

                        });

                    })->export('xlsx'); 

       
      



    }

    public function reporte_ventas(Request $request){

      $opcion = $request->get('opcion');
      $fecin = $request->get('fecin');
      $fecfin = $request->get('fecfin');
      $sucursal = $request->get('sucursal');
      $data_sucursal = EmpresaNegocios::findOrFail($sucursal);
      $vendedor = $request->get('vendedor');
      $cliente = $request->get('cliente');
      $almacen = $request->get('almacen');
      $dato_vendedor="";
      $dato_cliente="";

      

      $empresa = Auth::user()->IdEmpresa;
        switch ($opcion){
            case 1:
               $comprobantes = cpe_cabecera::select('cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','det.cdedes as producto','det.cdecan as cantidad','cdepuni as precio','monnom as moneda','det.cdevve as total','cpe_cabecera.ccabaj as baja','det.costo','ccasunrescod','tdides as documentoidentidad','ccandi as numerodocumento','procod','cdedes','cdecan','cdepuni','cdevve','umecod','ccaitv','id_almacen_pro')
                ->leftjoin('cpe_detalle as det','cpe_cabecera.IdCpe_cabecera','det.IdCpe_cabecera')
                ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                ->where(function ($query) {
                  $query->where('cpe_cabecera.tdocod','01')
                    ->orWhere('cpe_cabecera.tdocod','03')
                    ->orWhere('cpe_cabecera.tdocod','13')
                    ->orWhere('cpe_cabecera.tdocod','14')
                    //->orWhere('cpe_cabecera.tdocod','07')
                    ;
                  }) 
                  ->where(function ($query5) use ($sucursal) {
        
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
         
                  
                }) 
                 ->orderBy('IdCpe_cabecera','desc')->get();




                 $cabecera = DB::tABLE('cpe_cabecera')
             //    ->join('cpe_detalle','cpe_detalle.IdCpe_cabecera','cpe_cabecera.IdCpe_cabecera')
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
          
                ->where(function ($query) {
                  $query->where('cpe_cabecera.tdocod','01')
                    ->orWhere('cpe_cabecera.tdocod','03')
                    ->orWhere('cpe_cabecera.tdocod','13')
                    ->orWhere('cpe_cabecera.tdocod','14')
                   // ->orWhere('cpe_cabecera.tdocod','07')
                    ;
                  }) 
                ->whereNull('ccabaj')
                ->where('ccanot','')
                ->where(function ($query5) use ($sucursal) {
      
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
        
                }) 
    
                 ->get();


                 $total = $cabecera->where('ccanot','')->sum('ccaitv');

                 $totalnotas = $cabecera->where('tdocod','7')->sum('ccaitv');

                 $totalefectivo = $cabecera->where('ccanot','')->sum('totalcontado');
                 $totalcredito = $cabecera->where('ccanot','')->sum('totalcredito');
                        Excel::create('Reporte_Ventas', function($excel) use ($totalnotas,$total,$cabecera,$comprobantes,$fecin,$fecfin,$empresa,$sucursal,$data_sucursal,$totalefectivo,$totalcredito) {

                        $excel->sheet('Comprobantes', function($sheet) use ($totalnotas,$total,$cabecera,$comprobantes,$fecin,$fecfin,$empresa,$sucursal,$data_sucursal,$totalefectivo,$totalcredito) {


                                  $sheet->loadView('formatos_reportes_excel.ventas',compact('totalnotas','total','dato_vendedor','cabecera','comprobantes','fecin','fecfin','empresa','sucursal','data_sucursal','totalefectivo','totalcredito'));
                              

                                

                        });

                    })->export('xlsx'); 
            break;

              case 2:

                
                 if($vendedor!='Todos'){
                  $dato_vendedor = DB::tABLE('users')->where('IdUsuario',$vendedor)->first();
                }

                if($cliente!='Todos'){
                  $dato_cliente = DB::tABLE('cliente')->where('clicod',$cliente)->first();
                }

                $comprobantes = cpe_cabecera::select('cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','det.cdedes as producto','det.cdecan as cantidad','cdepuni as precio','monnom as moneda','det.cdevve as total','cpe_cabecera.ccabaj as baja','det.costo','ccasunrescod','tdides as documentoidentidad','ccandi as numerodocumento','procod','cdedes','cdecan','cdepuni','cdevve','umecod','ccaitv','id_almacen_pro')
                ->leftjoin('cpe_detalle as det','cpe_cabecera.IdCpe_cabecera','det.IdCpe_cabecera')
                ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                 ->where(function ($query) use ($vendedor) {
                      if($vendedor !='Todos'){
                         $query->Where('cpe_cabecera.IdUsuario_ven',$vendedor);
                      }
                    })
                ->where(function ($query) {
                  $query->where('cpe_cabecera.tdocod','01')
                    ->orWhere('cpe_cabecera.tdocod','03')
                    ->orWhere('cpe_cabecera.tdocod','13')
                    ->orWhere('cpe_cabecera.tdocod','14')
                    ->orWhere('cpe_cabecera.tdocod','07')
                    ;
                  }) 
                    ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                       ->where(function ($query2) use ($cliente) {
                      if($cliente !='Todos'){
                         $query2->Where('cpe_cabecera.clicod',$cliente);
                      }
                    })
                 ->orderBy('IdCpe_cabecera','desc')->get();




                 $cabecera = DB::tABLE('cpe_cabecera')
             //    ->join('cpe_detalle','cpe_detalle.IdCpe_cabecera','cpe_cabecera.IdCpe_cabecera')
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                 ->where(function ($query) use ($vendedor) {
                      if($vendedor !='Todos'){
                         $query->Where('cpe_cabecera.IdUsuario_ven',$vendedor);
                      }
                    })
                ->where(function ($query) {
                  $query->where('cpe_cabecera.tdocod','01')
                    ->orWhere('cpe_cabecera.tdocod','03')
                    ->orWhere('cpe_cabecera.tdocod','13')
                    ->orWhere('cpe_cabecera.tdocod','14')
                    ->orWhere('cpe_cabecera.tdocod','07')
                    ;
                  }) 
                    ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                })
                   ->where(function ($query2) use ($cliente) {
                      if($cliente !='Todos'){
                         $query2->Where('cpe_cabecera.clicod',$cliente);
                      }
                    })
                 ->get();

                 $total = $cabecera->where('ccanot','')->sum('ccaitv');

                 $totalnotas = $cabecera->where('tdocod','7')->sum('ccaitv');

                 /*---------------------------------------REPORTE VENTAS POR PRODUCTO RESUMEN------------------------------------------------------*/


                $productos = cpe_cabecera::select(DB::RAW('sum(cpe_detalle.cdecan*cpe_det_factor) as cantidad'),'cdedes',DB::RAW('sum(cpe_detalle.cdevve) as precio'),'cpe_detalle.procod','umecod')
                ->leftjoin('cpe_detalle','cpe_detalle.IdCpe_cabecera','cpe_cabecera.IdCpe_cabecera')
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                ->whereNull('cpe_cabecera.ccabaj')
                ->where('cdecan','>','0')
                ->where(function ($query) use ($vendedor) {
                      if($vendedor !='Todos'){
                         $query->Where('cpe_cabecera.IdUsuario_ven',$vendedor);
                      }
                })
                ->where(function ($query) {
                  $query->where('cpe_cabecera.tdocod','01')
                    ->orWhere('cpe_cabecera.tdocod','03')
                    ->orWhere('cpe_cabecera.tdocod','13')
                    ->orWhere('cpe_cabecera.tdocod','14')
                    ->orWhere('cpe_cabecera.tdocod','07')
                    ;
                  })   
                ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                })
                ->where(function ($query2) use ($cliente) {
                    if($cliente !='Todos'){
                         $query2->Where('cpe_cabecera.clicod',$cliente);
                    }
                }) 
                ->orderby('cdedes','desc')
                ->groupby('procod','cdedes')
                ->get();


                     $productosnot = cpe_cabecera::select(DB::RAW('sum(cpe_detalle.cdecan*cpe_det_factor) as cantidad'),'cdedes',DB::RAW('sum(cpe_detalle.cdevve) as precio'),'cpe_detalle.procod','umecod')
                ->leftjoin('cpe_detalle','cpe_detalle.IdCpe_cabecera','cpe_cabecera.IdCpe_cabecera')
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                ->whereNull('cpe_cabecera.ccabaj')
                ->where('cdecan','>','0')
                ->where(function ($query) use ($vendedor) {
                      if($vendedor !='Todos'){
                         $query->Where('cpe_cabecera.IdUsuario_ven',$vendedor);
                      }
                })
                ->where(function ($query) {
                  $query->Where('cpe_cabecera.tdocod','07')
                    ;
                  })   
                ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                })
                ->where(function ($query2) use ($cliente) {
                    if($cliente !='Todos'){
                         $query2->Where('cpe_cabecera.clicod',$cliente);
                      }
                }) 
                ->orderby('cdedes','desc')
                ->groupby('procod')
                ->get();

                $productosnotas = $productosnot->sum('cantidad');

                $totalproductos = $productos->sum('cantidad');

                $totalmontoproductos =0;
               
                foreach ($productos as $pro) {
                   $totalmontoproductos = $totalmontoproductos + ($pro->precio);
                }


                 /*---------------------------------------------------------------------------------------------------------------------------------*/

               


                        Excel::create('Reporte_Ventas_Detallado', function($excel) use ($totalmontoproductos,$totalnotas,$total,$cabecera,$comprobantes,$fecin,$fecfin,$empresa,$sucursal,$almacen,$productos,$productosnotas,$data_sucursal,$dato_vendedor,$dato_cliente) {

                        $excel->sheet('Comprobantes', function($sheet) use ($totalmontoproductos,$totalnotas,$total,$cabecera,$comprobantes,$fecin,$fecfin,$empresa,$sucursal,$almacen,$productos,$productosnotas,$data_sucursal,$dato_vendedor,$dato_cliente) {


                                  $sheet->loadView('formatos_reportes_excel.ventas_detalladas',compact('totalmontoproductos','totalnotas','total','cabecera','comprobantes','fecin','fecfin','empresa','sucursal','almacen','productos','productosnotas','data_sucursal','dato_vendedor','dato_cliente'));
                              

                                

                        });

                    })->export('xlsx'); 


             

            case 3:

                 $comprobantes = cpe_cabecera::select('ccacodsun','cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','tdides as documentoidentidad','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','monnom as moneda','ccaitv as total','cpe_cabecera.ccabaj as baja','ccaigv as igv','ccatvg as gravado','ccandi as numerodocumento','ccasunrescod','cpe_cabecera.tdocod','cpe_cabecera.ccatexo','cpe_cabecera.icbper')
                    ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                    ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                    ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                    ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                    ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
                    ->where(function ($query5) use ($sucursal) {
               
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                
                  
                }) 
          
                    ->where('cpe_cabecera.ccafem','>=',$fecin)
                    ->where('cpe_cabecera.ccafem','<=',$fecfin)
                    ->where(function ($query) {
                    $query->where('cpe_cabecera.tdocod','01')
                      ->orWhere('cpe_cabecera.tdocod','03');
                    })    
                   
                     ->orderBy('IdCpe_cabecera','desc')->get();

                $totales = cpe_cabecera::select('ccacodsun','cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','tdides as documentoidentidad','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','monnom as moneda','ccaitv as total','cpe_cabecera.ccabaj as baja','ccaigv as igv','ccatvg as gravado','ccandi as numerodocumento','ccasunrescod','cpe_cabecera.tdocod','cpe_cabecera.ccatexo','cpe_cabecera.icbper')
                    ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                    ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                    ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                    ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                    ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
                    ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
       
                    ->where('cpe_cabecera.ccafem','>=',$fecin)
                    ->where('cpe_cabecera.ccafem','<=',$fecfin)
                    ->where(function ($query) {
                      $query->where('cpe_cabecera.tdocod','01')
                        ->orWhere('cpe_cabecera.tdocod','03');
                      })    
                    
                     ->orderBy('IdCpe_cabecera','desc')->get();


              $gravados = $totales->sum('ccatvg');
              $exoneradas = $totales->sum('ccatexo');
              $totaligv = $totales->sum('cdeigv');
              $totalventas = $totales->sum('total');
    
              //NOTAS DE CREDITOS

                 $totalnc = cpe_cabecera::select('ccacodsun','cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','monnom as moneda','cpe_cabecera.ccabaj as baja','ccasunrescod','tdides as documentoidentidad','ccandi as numerodocumento','ccaigv','ccaitv','ccatexo','ccatvg')
            
                ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
                ->where(function ($query5) use ($sucursal) {
             
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
              
                 
                  
                }) 
                
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                  ->where(function ($query1){
          
                    $query1->whereNull('cpe_cabecera.ccabaj')
                          ->orwhere('cpe_cabecera.ccabaj','');
                    
                })
                ->where(function ($query) {
                   $query->Where('cpe_cabecera.tdocod','07');
                })    
                 ->orderBy('cpe_cabecera.IdCpe_cabecera','desc')->get();

              $gravadosnc = $totalnc->sum('ccatvg');
              $exoneradasnc = $totalnc->sum('ccatexo');
              $totaligvnc = $totalnc->sum('ccaigv');
              $totalnc = $totalnc->sum('ccaitv');

              // FIN NOTAS DE CREDITOS

              $hora_rep = now()->format('Y-m-d H:i:s');

                Excel::create('Reporte_Ventas_SUNAT', function($excel) use ($hora_rep,$fecin,$fecfin,$comprobantes,$gravados,$exoneradas,$totaligv,$totalventas,$gravadosnc,$exoneradasnc,$totaligvnc,$totalnc) {

                        $excel->sheet('Comprobantes', function($sheet) use ($hora_rep,$fecin,$fecfin,$comprobantes,$gravados,$exoneradas,$totaligv,$totalventas,$gravadosnc,$exoneradasnc,$totaligvnc,$totalnc) {


                                  $sheet->loadView('formatos_reportes_excel.ventas_sunat',compact('hora_rep','fecin','fecfin','comprobantes','gravados','exoneradas','totaligv','totalventas','gravadosnc','exoneradasnc','totaligvnc','totalnc'));
                              

                                

                        });

                    })->export('xlsx'); 

              

            break;


            case 51:

       

                $comprobantes = cpe_cabecera::select('cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','det.cdedes as producto','det.cdecan as cantidad','cdepuni as precio','monnom as moneda','det.cdevve as total','cpe_cabecera.ccabaj as baja','det.costo','ccasunrescod','tdides as documentoidentidad','ccandi as numerodocumento','procod','cdedes','cdecan','cdepuni','cdevve','umecod','ccaitv','id_almacen_pro')
                ->leftjoin('cpe_detalle as det','cpe_cabecera.IdCpe_cabecera','det.IdCpe_cabecera')
                ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                ->where(function ($query) {
                  $query->where('cpe_cabecera.tdocod','01')
                    ->orWhere('cpe_cabecera.tdocod','03')
                    ->orWhere('cpe_cabecera.tdocod','13')
                    ->orWhere('cpe_cabecera.tdocod','14')
                    //->orWhere('cpe_cabecera.tdocod','07')
                    ;
                  }) 
                  ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                ->whereNotNull('ccabaj')
                ->orderBy('IdCpe_cabecera','desc')->get();




                $cabecera = DB::tABLE('cpe_cabecera')
             // ->join('cpe_detalle','cpe_detalle.IdCpe_cabecera','cpe_cabecera.IdCpe_cabecera')
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                ->where(function ($query) {
                  $query->where('cpe_cabecera.tdocod','01')
                    ->orWhere('cpe_cabecera.tdocod','03')
                    ->orWhere('cpe_cabecera.tdocod','13')
                    ->orWhere('cpe_cabecera.tdocod','14')
                   // ->orWhere('cpe_cabecera.tdocod','07')
                    ;
                  }) 
 
                ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                ->whereNotNull('ccabaj')
                ->get();

                

                 $totalnotas = $cabecera->where('tdocod','7')->sum('ccaitv');

                 $totalefectivo = $cabecera->where('ccanot','')->sum('totalcontado');
                 $totalcredito = $cabecera->where('ccanot','')->sum('totalcredito');

                  $total = $totalefectivo+$totalcredito;

          
                $hora_rep = now()->format('Y-m-d H:i:s');

                Excel::create('Reporte_Ventas_Anuladas', function($excel) use ($totalnotas,$total,$cabecera,$comprobantes,$fecin,$fecfin,$empresa,$sucursal,$data_sucursal,$totalefectivo,$totalcredito) {

                        $excel->sheet('Comprobantes', function($sheet) use ($totalnotas,$total,$cabecera,$comprobantes,$fecin,$fecfin,$empresa,$sucursal,$data_sucursal,$totalefectivo,$totalcredito) {


                                  $sheet->loadView('formatos_reportes_excel.ventas_anuladas',compact('totalnotas','total','cabecera','comprobantes','fecin','fecfin','empresa','sucursal','data_sucursal','totalefectivo','totalcredito'));
                              

                                

                        });

                    })->export('xlsx'); 




            break;  


                  case 80:

                $boletas = cpe_cabecera::select('ccafem as FECHA', 'tdodes as COMPROBANTE','serdoc as SERIE',DB::raw('MIN(numdoc) as INICIO'),DB::raw( 'MAX(numdoc) as FIN'),DB::raw('SUM(ccatvg) as VALOR_VENTA'),DB::raw('SUM(ccaigv) as IGV'),DB::raw('SUM(ccaitv) as TOTAL'))
                ->leftjoin('tipo_documento','tipo_documento.tdocod','cpe_cabecera.tdocod')
                ->whereNull('ccabaj')
                ->where('cpe_cabecera.tdocod','03')
                ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                }) 
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                ->groupby('ccafem')
                ->groupby('tdodes')
                ->orderBy('FECHA','asc')
                ->orderBy('INICIO','asc')
                ->get();

                $facturas = cpe_cabecera::select('ccaitv','cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','monnom as moneda','ccaitv as total','cpe_cabecera.ccabaj as baja','ccasunrescod','tdides as documentoidentidad','ccandi as numerodocumento','cpe_cabecera.tdocod','ccatexo','ccatvg','ccatvi')
                ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
                ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                ->where(function ($query){
                    $query->where('cpe_cabecera.tdocod','01');
                })
                ->whereNull('ccabaj')
                ->orderBy('IdCpe_cabecera','desc')->get();

             

                $total_boletas = $boletas->sum('TOTAL');
                $total_facturas = $facturas->sum('total');

              
                 $hora_rep = now()->format('Y-m-d H:i:s');

                Excel::create('Reporte_Ventas_Consolidado', function($excel) use ($fecin,$fecfin,$boletas,$facturas,$total_boletas,$total_facturas) {

                        $excel->sheet('Comprobantes', function($sheet) use ($fecin,$fecfin,$boletas,$facturas,$total_boletas,$total_facturas) {


                                  $sheet->loadView('formatos_reportes_excel.ventas_consolidado',compact('fecin','fecfin','boletas','facturas','total_boletas','total_facturas'));
                              

                                

                        });

                    })->export('xlsx'); 


            break;


                    case 19:



              
                $facturas = cpe_cabecera::select('ccaitv','cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','monnom as moneda','ccaitv as total','cpe_cabecera.ccabaj as baja','ccasunrescod','tdides as documentoidentidad','ccandi as numerodocumento',
                  'cpe_cabecera.tdocod','ccafve as fechaven','cpe_cabecera.tdicod','ccatexo','ccafem_ref','serie_ref','tdocod_ref','num_ref','cod_mov')
                ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
                 ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                
                ->where(function ($query6) use ($almacen) {
                  if($almacen !='Todos'){
                      $query6->where('cpe_cabecera.id_almacen',$almacen);
                  }                
                }) 
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                ->where(function ($query){
                    $query->where('cpe_cabecera.tdocod','01');
                })
                ->where(function ($query1) use ($vendedor) {
                      if($vendedor !='Todos'){
                         $query1->Where('cpe_cabecera.IdUsuario_ven',$vendedor);
                      }
                    })
                 ->where(function ($query2) use ($cliente) {
                      if($cliente !='Todos'){
                         $query2->Where('cpe_cabecera.clicod',$cliente);
                      }
                    })
                ->orderBy('IdCpe_cabecera','desc')->get();

                 $boletas = cpe_cabecera::select('ccaitv','cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','monnom as moneda','ccaitv as total','cpe_cabecera.ccabaj as baja','ccasunrescod','tdides as documentoidentidad','ccandi as numerodocumento',
                  'cpe_cabecera.tdocod','ccafve as fechaven','cpe_cabecera.tdicod','ccatexo','ccafem_ref','serie_ref','tdocod_ref','num_ref','cod_mov')
                ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
                 ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                
                ->where(function ($query6) use ($almacen) {
                  if($almacen !='Todos'){
                      $query6->where('cpe_cabecera.id_almacen',$almacen);
                  }                
                }) 
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                ->where(function ($query){
                    $query->where('cpe_cabecera.tdocod','03');
                })
                ->where(function ($query1) use ($vendedor) {
                      if($vendedor !='Todos'){
                         $query1->Where('cpe_cabecera.IdUsuario_ven',$vendedor);
                      }
                    })
                 ->where(function ($query2) use ($cliente) {
                      if($cliente !='Todos'){
                         $query2->Where('cpe_cabecera.clicod',$cliente);
                      }
                    })
                ->orderBy('IdCpe_cabecera','desc')->get();


                 $notas = cpe_cabecera::select('ccaitv','cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','monnom as moneda','ccaitv as total','cpe_cabecera.ccabaj as baja','ccasunrescod','tdides as documentoidentidad','ccandi as numerodocumento',
                  'cpe_cabecera.tdocod','ccafve as fechaven','cpe_cabecera.tdicod','ccatexo','ccafem_ref','serie_ref','tdocod_ref','num_ref','cod_mov')
                ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
                 ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('cpe_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                
                ->where(function ($query6) use ($almacen) {
                  if($almacen !='Todos'){
                      $query6->where('cpe_cabecera.id_almacen',$almacen);
                  }                
                }) 
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                ->where(function ($query){
                    $query->where('cpe_cabecera.tdocod','07');
                })
                ->where(function ($query1) use ($vendedor) {
                      if($vendedor !='Todos'){
                         $query1->Where('cpe_cabecera.IdUsuario_ven',$vendedor);
                      }
                    })
                 ->where(function ($query2) use ($cliente) {
                      if($cliente !='Todos'){
                         $query2->Where('cpe_cabecera.clicod',$cliente);
                      }
                    })
                ->orderBy('IdCpe_cabecera','desc')->get();

      

                          $totalfacturas = $facturas->sum('ccaitv');
                  $totalboletas = $boletas->sum('ccaitv');
                  $totalnotas = $notas->sum('ccaitv');


                  $totalfacturasexo = $facturas->sum('ccatexo');
                  $totalboletasexo = $boletas->sum('ccatexo');
                  $totalnotasexo = $notas->sum('ccatexo');
                    

                  $totalfacturasinaf = $facturas->sum('ccatvi');
                  $totalboletasinaf = $boletas->sum('ccatvi');
                  $totalnotasinaf = $notas->sum('ccatvi');
                  

                   $totalfacturasigv = $facturas->sum('ccaigv');
                  $totalboletasigv = $boletas->sum('ccaigv');
                  $totalnotasigv = $notas->sum('ccaigv');
                 
                  $totalfacturasicbper = $facturas->sum('icbper');
                  $totalboletasicbper = $boletas->sum('icbper');
                  $totalnotasicbper = $notas->sum('icbper');


              
                  $hora_rep = now()->format('Y-m-d H:i:s');

                Excel::create('Reporte_Ventas_Registro_SUNAT', function($excel) use ($fecin,$fecfin,$totalfacturas,$totalboletas,$totalnotas,$totalfacturasexo,$totalboletasexo,$totalnotasexo,$totalfacturasinaf,$totalboletasinaf,$totalnotasinaf,$totalfacturasigv,$totalboletasigv,$totalnotasigv,$totalfacturasicbper,$totalboletasicbper,$totalnotasicbper,$facturas,$boletas,$notas) {

                        $excel->sheet('Comprobantes', function($sheet) use ($fecin,$fecfin,$totalfacturas,$totalboletas,$totalnotas,$totalfacturasexo,$totalboletasexo,$totalnotasexo,$totalfacturasinaf,$totalboletasinaf,$totalnotasinaf,$totalfacturasigv,$totalboletasigv,$totalnotasigv,$totalfacturasicbper,$totalboletasicbper,$totalnotasicbper,$facturas,$boletas,$notas) {


                                  $sheet->loadView('formatos_reportes_excel.ventas_registro_sunat',compact('fecin','fecfin','totalfacturas','totalboletas','totalnotas','totalfacturasexo','totalboletasexo','totalnotasexo','totalfacturasinaf','totalboletasinaf','totalnotasinaf','totalfacturasigv','totalboletasigv','totalnotasigv','totalfacturasicbper','totalboletasicbper','totalnotasicbper','facturas','boletas','notas'));
                              

                                

                        });

                    })->export('xlsx'); 


            break;





        }

      

    }


    pubLic function reporte_compras(Request $request){


          $opcion = $request->get('opcion');
          $fecin = $request->get('fecin');
          $fecfin = $request->get('fecfin');
          $sucursal = $request->get('sucursal');
          $almacen = $request->get('almacen');
          $proveedor = $request->get('proveedor');
          $data_sucursal = EmpresaNegocios::findOrFail($sucursal);

          $empresa = Auth::user()->IdEmpresa;

        switch ($opcion){
           case 1:

                 $comprobantes = DB::tABLE('compras_cabecera')
                  ->leftjoin('proveedor','compras_cabecera.prov_id','proveedor.prov_id')
                  ->join('moneda','compras_cabecera.mon_id','moneda.moncod')
                  ->join('tipo_documento','compras_cabecera.tdocod','tipo_documento.tdocod')
                    ->where('compras_cabecera.com_fec','>=',$fecin)
                    ->where('compras_cabecera.com_fec','<=',$fecfin)
                  ->where('est_compra','Registrado')
                    ->where(function ($query) use ($sucursal){
                      if(!$sucursal !=0 && !empty($sucursal)){
                          $query->where('compras_cabecera.id_empresa_negocio',$sucursal);
                      }
                     
                  })
                  ->where(function ($query1) use ($proveedor){
                      if($proveedor !=0){
                          $query1->where('compras_cabecera.prov_id',$proveedor);
                      }
                      
                  })
                        
                  ->where(function ($query6) use ($almacen) {
                      if($almacen !=0 && !empty($almacen)){
                          $query6->where('compras_cabecera.id_almacen',$almacen);
                      }                
                    }) 
                
                  ->where(function ($query){
                    $query->where('compras_cabecera.tdocod','!=','80');
                })
                  ->orderby('com_cab_id','desc')
                  ->get();
             //   dd($comprobantes);

                  $total = $comprobantes->sum('total_com');


                $vista = view('empresas.reportes.compras',compact('fecin','fecfin','comprobantes','total','dat_ven','dat_cli'))->render();



              if($request->ajax()){
                 return response()->json(['vista'=>$vista]);

              }

            break;

            case 2:

                

              $comprobantes = DB::tABLE('compras_cabecera')
               ->leftjoin('compras_detalle','compras_detalle.com_cab_id','compras_cabecera.com_cab_id')
                 ->leftjoin('productos','productos.IdProducto','compras_detalle.pro_id')
                  ->leftjoin('proveedor','compras_cabecera.prov_id','proveedor.prov_id')
                  ->leftjoin('unidad_medida','unidad_medida.umecod','compras_detalle.ume_cod')
                  ->join('moneda','compras_cabecera.mon_id','moneda.moncod')
                  ->join('tipo_documento','compras_cabecera.tdocod','tipo_documento.tdocod')
              ->where('est_compra','Registrado')
                ->where('compras_cabecera.com_fec','>=',$fecin)
                ->where('compras_cabecera.com_fec','<=',$fecfin)
                  ->where(function ($query) use ($sucursal){
                      if(!$sucursal !=0 && !empty($sucursal)){
                          $query->where('compras_cabecera.id_empresa_negocio',$sucursal);
                      }
                     
                  })
                  ->where(function ($query1) use ($proveedor){
                      if($proveedor !=0){
                          $query1->where('compras_cabecera.prov_id',$proveedor);
                      }
                      
                  })
                        
                  ->where(function ($query6) use ($almacen) {
                      if($almacen !=0 && !empty($almacen)){
                          $query6->where('compras_cabecera.id_almacen',$almacen);
                      }                
                    }) 
               ->where(function ($query){
                    $query->where('compras_cabecera.tdocod','<>','80');
                })
              ->orderby('compras_cabecera.com_cab_id','desc')
              ->get();
                 //dd($comprobantes);

                  $total = $comprobantes->sum('total_com');


                $vista = view('empresas.reportes.compras_detalladas',compact('fecin','fecfin','comprobantes','total','dat_ven','dat_cli'))->render();

                    if($request->ajax()){
                 return response()->json(['vista'=>$vista]);

              }



            break;


            case 3:

              $comprobantes = DB::tABLE('compras_cabecera')
              ->leftjoin('proveedor','compras_cabecera.prov_id','proveedor.prov_id')
              ->join('moneda','compras_cabecera.mon_id','moneda.moncod')
              ->join('tipo_documento','compras_cabecera.tdocod','tipo_documento.tdocod')
              ->where('est_compra','Registrado')
                ->where('compras_cabecera.com_fec','>=',$fecin)
                ->where('compras_cabecera.com_fec','<=',$fecfin)
                  ->where(function ($query) use ($sucursal){
                      if(!empty($sucursal)){
                          $query->where('compras_cabecera.id_empresa_negocio',$sucursal);
                      }
                     
                  })
            
                  ->where(function ($query6) use ($almacen) {
                      if(!empty($almacen)){
                          $query6->where('compras_cabecera.id_almacen',$almacen);
                      }                
                    }) 
               ->where(function ($query){
                    $query->where('compras_cabecera.tdocod','!=','80');
                })
              ->orderby('compras_cabecera.com_cab_id','desc')
              ->get();
                 //dd($comprobantes);

                  $total = $comprobantes->sum('total_com');


                $vista = view('empresas.reportes.compras_contador',compact('fecin','fecfin','comprobantes','total','dat_ven','dat_cli'))->render();

                    if($request->ajax()){
                 return response()->json(['vista'=>$vista]);

              }



            break;


            case 4:

                   $comprobantes = compras_cabecera::select('total_com','com_cab_id','com_fec as fecha','tdodes as comprobante','com_doc_ser as serie','com_doc_num as numero','prov_raz as cliente','monnom as moneda','total_com as total','tdides as documentoidentidad','prov_ruc as numerodocumento','compras_cabecera.tdocod')
                ->leftjoin('moneda as m','compras_cabecera.mon_id','=','m.moncod')
                ->leftjoin('proveedor as cl','compras_cabecera.prov_id','=','cl.prov_id')
               //->leftjoin('empresa as e','compras_cabecera.IdEmpresa','=','e.IdEmpresa')
                ->leftjoin('tipo_documento as tip_d','compras_cabecera.tdocod','=','tip_d.tdocod')
                ->leftjoin('tipo_documento_identidad as tdi','cl.tdicod','=','tdi.tdicod')
                 ->where(function ($query5) use ($sucursal) {
                  if($sucursal !=0 && !empty($sucursal)){
                     $query5->where('compras_cabecera.id_empresa_negocio',$sucursal);
                  }
                 
                  
                }) 
                
                ->where(function ($query6) use ($almacen) {
                  if($almacen !=0 && !empty($almacen)){
                      $query6->where('compras_cabecera.id_almacen',$almacen);
                  }                
                }) 
                ->where('compras_cabecera.com_fec','>=',$fecin)
                ->where('compras_cabecera.com_fec','<=',$fecfin)
                ->where(function ($query){
                    $query->where('compras_cabecera.tdocod','01')
                          ->orwhere('compras_cabecera.tdocod','03')
                          ->orWhere('compras_cabecera.tdocod','13')
                          ->orWhere('compras_cabecera.tdocod','07')
                          ->orWhere('compras_cabecera.tdocod','09');
                })
                ->where('est_compra','Registrado')
                ->orderBy('com_cab_id','desc')->get();

            //  dd($comprobantes);

                  $total = $comprobantes->sum('total_com');

                  $totalexoneradasfacturas = $comprobantes->where('tdocod','01')->sum('total_com');
                  $totalgravadasfacturas = $comprobantes->where('tdocod','01')->sum('ccatvg');

                  $totalexoneradasboletas = $comprobantes->where('tdocod','03')->sum('total_com');
                  $totalgravadasboletas = $comprobantes->where('tdocod','03')->sum('ccatvg');

                  $totalexoneradasnotas = $comprobantes->where('tdocod','07')->sum('total_com');
                  $totalgravadasnotas = $comprobantes->where('tdocod','07')->sum('ccatvg');


            

                 $sucursal = EmpresaNegocios::FindOrFail($request->get('sucursal'));

                 $empresa = Empresa::findOrFail($sucursal->IdEmpresa);

                
                    $vista =view('empresas.reportes.compras_resumen',compact('fecin','fecfin','total','totalexoneradasfacturas','totalexoneradasboletas','totalgravadasfacturas','totalgravadasboletas','totalgravadasnotas','totalexoneradasnotas','empresa','sucursal'))->render();


              if($request->ajax()){
                 return response()->json(['vista'=>$vista]);

              }



            break;
          }

    }

   pubLic function reporte_compras_excel(Request $request){

      $opcion = $request->get('opcion');
      $fecin = $request->get('fecin');
      $fecfin = $request->get('fecfin');
      $sucursal = $request->get('sucursal');
      $almacen = $request->get('almacen');
      $data_sucursal = EmpresaNegocios::findOrFail($sucursal);

      $empresa = Empresa::findorFail(Auth::user()->IdEmpresa);

        switch ($opcion){
            case 11:

                 $comprobantes = DB::tABLE('compras_cabecera')
                  ->leftjoin('proveedor','compras_cabecera.prov_id','proveedor.prov_id')
                  ->join('moneda','compras_cabecera.mon_id','moneda.moncod')
                  ->join('tipo_documento','compras_cabecera.tdocod','tipo_documento.tdocod')
                  ->where('compras_cabecera.com_fec','>=',$fecin)
                  ->where('compras_cabecera.com_fec','<=',$fecfin)
                  ->where('est_compra','Registrado')
                    ->where(function ($query) use ($sucursal){
                      if(!empty($sucursal)){
                          $query->where('compras_cabecera.id_empresa_negocio',$sucursal);
                      }
                     
                  })
                     
                  ->where(function ($query6) use ($almacen) {
                      if(!empty($almacen)){
                          $query6->where('compras_cabecera.id_almacen',$almacen);
                      }                
                    }) 
                
                  ->where('compras_cabecera.tdocod','!=','80')
                  ->orderby('com_cab_id','desc')
                  ->get();
             

                  $total = $comprobantes->sum('total_com');


                   Excel::create('COMPRAS', function($excel) use ($data_sucursal,$empresa,$fecin,$fecfin,$total,$comprobantes) {

                        $excel->sheet('COMPRAS', function($sheet) use ($data_sucursal,$empresa,$fecin,$fecfin,$total,$comprobantes) {

                       
                            
                                  $sheet->loadView('formatos_reportes_excel.compras',compact('data_sucursal','empresa','fecin','fecfin','total','comprobantes'));
                          
                                

                        });

                    })->export('xlsx'); 

                

                break;

                      case 12:

                 $comprobantes = DB::tABLE('compras_cabecera')
                 ->leftjoin('compras_detalle','compras_detalle.com_cab_id','compras_cabecera.com_cab_id')
                 ->leftjoin('productos','productos.IdProducto','compras_detalle.pro_id')
                  ->leftjoin('proveedor','compras_cabecera.prov_id','proveedor.prov_id')
                  ->leftjoin('unidad_medida','unidad_medida.umecod','compras_detalle.ume_cod')
                  ->join('moneda','compras_cabecera.mon_id','moneda.moncod')
                  ->join('tipo_documento','compras_cabecera.tdocod','tipo_documento.tdocod')
                  ->where('compras_cabecera.com_fec','>=',$fecin)
                  ->where('compras_cabecera.com_fec','<=',$fecfin)
                  ->where('est_compra','Registrado')
                    ->where(function ($query) use ($sucursal){
                      if(!empty($sucursal)){
                          $query->where('compras_cabecera.id_empresa_negocio',$sucursal);
                      }
                     
                  })
                     
                  ->where(function ($query6) use ($almacen) {
                      if(!empty($almacen)){
                          $query6->where('compras_cabecera.id_almacen',$almacen);
                      }                
                    }) 
                
                  ->where('compras_cabecera.tdocod','!=','80')
                  ->orderby('compras_cabecera.com_cab_id','desc')
                  ->get();
             

                  $total = $comprobantes->sum('total_com');


                   Excel::create('COMPRAS DETALLADO', function($excel) use ($data_sucursal,$empresa,$fecin,$fecfin,$total,$comprobantes) {

                        $excel->sheet('COMPRAS_DETALLADO', function($sheet) use ($data_sucursal,$empresa,$fecin,$fecfin,$total,$comprobantes) {

                       
                            
                                  $sheet->loadView('formatos_reportes_excel.compras_detalladas',compact('data_sucursal','empresa','fecin','fecfin','total','comprobantes'));
                          
                                

                        });

                    })->export('xlsx'); 

  

               
                break;

                  case 3:

                   $comprobantes = DB::tABLE('compras_cabecera')
              ->leftjoin('proveedor','compras_cabecera.prov_id','proveedor.prov_id')
              ->join('moneda','compras_cabecera.mon_id','moneda.moncod')
              ->join('tipo_documento','compras_cabecera.tdocod','tipo_documento.tdocod')
              ->where('est_compra','Registrado')
                ->where('compras_cabecera.com_fec','>=',$fecin)
                ->where('compras_cabecera.com_fec','<=',$fecfin)
                  ->where(function ($query) use ($sucursal){
                      if(!empty($sucursal)){
                          $query->where('compras_cabecera.id_empresa_negocio',$sucursal);
                      }
                     
                  })
            
                  ->where(function ($query6) use ($almacen) {
                      if(!empty($almacen)){
                          $query6->where('compras_cabecera.id_almacen',$almacen);
                      }                
                    }) 
               ->where(function ($query){
                    $query->where('compras_cabecera.tdocod','!=','80');
                })
              ->orderby('compras_cabecera.com_cab_id','desc')
              ->get();
                 //dd($comprobantes);

                  $total = $comprobantes->sum('total_com');



                 Excel::create('REGISTRO COMPRAS SUNAT', function($excel) use ($data_sucursal,$empresa,$fecin,$fecfin,$total,$comprobantes) {

                        $excel->sheet('REGISTRO_COMPRAS_SUNAT', function($sheet) use ($data_sucursal,$empresa,$fecin,$fecfin,$total,$comprobantes) {

                          $sheet->setWidth(array(
                            'A'     =>  1000000000
                        ));

                            
                                  $sheet->loadView('formatos_reportes_excel.compras_contador',compact('fecin','fecfin','comprobantes','total','dat_ven','dat_cli'));
                          
                                

                        });

                    })->export('xlsx'); 



              break;
        };


    }


    pubLic function reporte_compras_pdf(Request $request){
      
      $opcion = $request->get('opcion');
      $fecin = $request->get('fecin');
      $fecfin = $request->get('fecfin');
      $sucursal = $request->get('sucursal');
      $almacen = $request->get('almacen');
      $data_sucursal = EmpresaNegocios::findOrFail($sucursal);

      $empresa = Empresa::findorFail(Auth::user()->IdEmpresa);

        switch ($opcion){
            case 1:

                 $comprobantes = DB::tABLE('compras_cabecera')
                  ->leftjoin('proveedor','compras_cabecera.prov_id','proveedor.prov_id')
                  ->join('moneda','compras_cabecera.mon_id','moneda.moncod')
                  ->join('tipo_documento','compras_cabecera.tdocod','tipo_documento.tdocod')
                  ->where('compras_cabecera.com_fec','>=',$fecin)
                  ->where('compras_cabecera.com_fec','<=',$fecfin)
                  ->where('est_compra','Registrado')
                    ->where(function ($query) use ($sucursal){
                      if(!empty($sucursal)){
                          $query->where('compras_cabecera.id_empresa_negocio',$sucursal);
                      }
                     
                  })
                     
                  ->where(function ($query6) use ($almacen) {
                      if(!empty($almacen)){
                          $query6->where('compras_cabecera.id_almacen',$almacen);
                      }                
                    }) 
                
                  ->where('compras_cabecera.tdocod','!=','80')
                  ->orderby('com_cab_id','desc')
                  ->get();
             

                  $total = $comprobantes->sum('total_com');


                 $rutapdf = public_path().'/pdfreportes/';

                  $nompdffile = 'Reporte_Compras_'.$fecin.'_'.$fecfin.'.pdf';

                 if(file_exists($rutapdf.$nompdffile)){

                      unlink($rutapdf.$nompdffile);
                  }

                
                  $view = \View::make('formatos_reportes.reporte_pdf_compras', compact('data_sucursal','empresa','fecin','fecfin','total','comprobantes'));

                            
                  $pdf = \App::make('dompdf.wrapper');
                  $contenido = $view->render();
                  $pdf->loadHTML($view)->setPaper('A4', 'lanscape')->save($rutapdf.$nompdffile);

                 if (file_exists($rutapdf.$nompdffile)){
                    $headers = array(
                        'Content-Type: application/pdf',
                      );

                  return response()->download($rutapdf.$nompdffile);

                }

                break;

                      case 2:

                 $comprobantes = DB::tABLE('compras_cabecera')
                 ->leftjoin('compras_detalle','compras_detalle.com_cab_id','compras_cabecera.com_cab_id')
                 ->leftjoin('productos','productos.IdProducto','compras_detalle.pro_id')
                  ->leftjoin('proveedor','compras_cabecera.prov_id','proveedor.prov_id')
                  ->leftjoin('unidad_medida','unidad_medida.umecod','compras_detalle.ume_cod')
                  ->join('moneda','compras_cabecera.mon_id','moneda.moncod')
                  ->join('tipo_documento','compras_cabecera.tdocod','tipo_documento.tdocod')
                  ->where('compras_cabecera.com_fec','>=',$fecin)
                  ->where('compras_cabecera.com_fec','<=',$fecfin)
                  ->where('est_compra','Registrado')
                    ->where(function ($query) use ($sucursal){
                      if(!empty($sucursal)){
                          $query->where('compras_cabecera.id_empresa_negocio',$sucursal);
                      }
                     
                  })
                     
                  ->where(function ($query6) use ($almacen) {
                      if(!empty($almacen)){
                          $query6->where('compras_cabecera.id_almacen',$almacen);
                      }                
                    }) 
                
                  ->where('compras_cabecera.tdocod','!=','80')
                  ->orderby('compras_cabecera.com_cab_id','desc')
                  ->get();
             

                  $total = $comprobantes->sum('total_com');


                 $rutapdf = public_path().'/pdfreportes/';

                  $nompdffile = 'Reporte_Compras_'.$fecin.'_'.$fecfin.'.pdf';

                 if(file_exists($rutapdf.$nompdffile)){

                      unlink($rutapdf.$nompdffile);
                  }

                
                  $view = \View::make('formatos_reportes.reporte_pdf_compras_productos', compact('data_sucursal','empresa','fecin','fecfin','total','comprobantes'));

                            
                  $pdf = \App::make('dompdf.wrapper');
                  $contenido = $view->render();
                  $pdf->loadHTML($view)->setPaper('A4', 'lanscape')->save($rutapdf.$nompdffile);

                 if (file_exists($rutapdf.$nompdffile)){
                    $headers = array(
                        'Content-Type: application/pdf',
                      );

                  return response()->download($rutapdf.$nompdffile);

                }

                break;



        };

    }

       public function reportes_salidas(Request $request){

        $fecin = $request->get('fecin');
        $fecfin = $request->get('fecfin');

        $areas = DB::tABLE('areas')->get();

        $area = $request->get('area');
        $negocios = DB::tABLE('empresa_negocios')->get();
        $sucursal = $request->get('sucursal');
        $IdProducto = $request->get('IdProducto');
        $tipo = $request->get('tipo');
        $productos = DB::tABLE('productos')->get();

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

       if($tipo=='1'){
          $comprobantes = DB::tABLE('cpe_cabecera as cpe_c')->select('cpe_det_lote','cpe_det_venc','ccacodsun','ccafem','tdodes','cpe_c.serdoc','cpe_c.numdoc','tdides','cpe_c.ccandi','cpe_c.ccanom','monnom','cpe_c.ccaitv','cpe_c.IdEmpresa','cpe_c.tdocod','cpe_c.IdCpe_cabecera','cpe_c.ccanot','cpe_c.ccabaj','cpe_c.codhash','cpe_c.estado','cpe_c.enviado','totalcontado','totalcredito','areas.are_emp_des','name','apeusu','cdedes','cdecan','cpe_c.are_emp_id','cpe_detalle.costo')
          ->leftjoin('cpe_detalle','cpe_detalle.IdCpe_cabecera','cpe_c.IdCpe_cabecera')
          ->join('tipo_documento as tip_d','cpe_c.tdocod','=','tip_d.tdocod')
          ->join('tipo_documento_identidad as tdi','cpe_c.tdicod','=','tdi.tdicod')
          ->join('moneda as mon','cpe_c.moncod','=','mon.moncod')
          ->leftjoin('users','users.IdUsuario','cpe_c.usu_rec')
          ->join('areas','areas.are_emp_id','cpe_c.are_emp_id')
          ->whereNull('cpe_c.ccabaj')
          ->where('cpe_c.ccafem','>=',$fecin)
          ->where('cpe_c.ccafem','<=',$fecfin)
          ->where('cpe_c.id_empresa_negocio',Auth::user()->id_empresa_negocio)
          ->where(function ($query) {
            $query->where('cpe_c.tdocod','81');
            })
          ->where(function ($query) use ($IdProducto) {
            if(!empty($IdProducto)){
              $query->where('IdProducto',$IdProducto);
            }
          })
          ->where(function ($query) use($area) {
            if(!empty($area)){
               $query->where('cpe_c.are_emp_id',$area);
            }
           
          })
          ->orderby('IdCpe_cabecera','asc')
          ->get();


          $cantidad = $comprobantes->sum('cdecan');

        }elseif($tipo=='2'){
           $comprobantes = DB::tABLE('cpe_cabecera')
           ->select(DB::RAW('sum(cpe_detalle.cdecan) as cantidad'),'pronom','cdedes','cdepuni','productos.procod','productos.umecod','areas.are_emp_des','cpe_cabecera.are_emp_id',DB::RAW('sum(cpe_detalle.costo) as costo_total'))
                ->leftjoin('cpe_detalle','cpe_detalle.IdCpe_cabecera','cpe_cabecera.IdCpe_cabecera')
                ->leftjoin('productos','productos.IdProducto','cpe_detalle.IdProducto')
                ->leftjoin('areas','areas.are_emp_id','cpe_cabecera.are_emp_id')
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                ->where('cpe_cabecera.id_empresa_negocio',Auth::user()->id_empresa_negocio)
                ->whereNull('cpe_cabecera.ccabaj')
                ->where(function ($query) use ($IdProducto) {
                  if(!empty($IdProducto)){
                    $query->where('cpe_detalle.IdProducto',$IdProducto);
                  }
                })
                ->where(function ($query) {
                  $query->where('cpe_cabecera.tdocod','81');
                  })
                  ->where(function ($query) use($area) {
                  if(!empty($area)){
                     $query->where('cpe_cabecera.are_emp_id',$area);
                  }
                 
                  })
                ->orderby('cantidad','desc')
                ->groupby('cpe_cabecera.are_emp_id')
                ->groupby('cpe_detalle.IdProducto')
                ->get();

                $cantidad = $comprobantes->sum('cantidad');
              


        }

         return view('empresas.reportes.reportes_salidas_productos',compact('IdProducto','productos','cantidad','negocios','sucursal','comprobantes','fecin','fecfin','documento','areas','area','tipo'));

      

    }



     public function pdf_reportes_salidas(Request $request){


        $fecin = $request->get('fecin');
        $fecfin = $request->get('fecfin');

        $areas = DB::tABLE('areas')->get();

        $area = $request->get('area');
        $negocios = DB::tABLE('empresa_negocios')->get();
        $sucursal = $request->get('sucursal');
        $IdProducto = $request->get('IdProducto');
        $tipo = $request->get('tipo');
        $productos = DB::tABLE('productos')->get();

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
        
        $data_sucursal = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first();

        $IdEmpresa = Auth::user()->IdEmpresa;
    
        $cantidad =0;

       if($tipo=='1'){
          $comprobantes = DB::tABLE('cpe_cabecera as cpe_c')->select('cpe_det_lote','cpe_det_venc','ccacodsun','ccafem','tdodes','cpe_c.serdoc','cpe_c.numdoc','tdides','cpe_c.ccandi','cpe_c.ccanom','monnom','cpe_c.ccaitv','cpe_c.IdEmpresa','cpe_c.tdocod','cpe_c.IdCpe_cabecera','cpe_c.ccanot','cpe_c.ccabaj','cpe_c.codhash','cpe_c.estado','cpe_c.enviado','totalcontado','totalcredito','areas.are_emp_des','name','apeusu','cdedes','cdecan','cpe_c.are_emp_id','cpe_detalle.costo')
          ->leftjoin('cpe_detalle','cpe_detalle.IdCpe_cabecera','cpe_c.IdCpe_cabecera')
          ->join('tipo_documento as tip_d','cpe_c.tdocod','=','tip_d.tdocod')
          ->join('tipo_documento_identidad as tdi','cpe_c.tdicod','=','tdi.tdicod')
          ->join('moneda as mon','cpe_c.moncod','=','mon.moncod')
          ->leftjoin('users','users.IdUsuario','cpe_c.usu_rec')
          ->join('areas','areas.are_emp_id','cpe_c.are_emp_id')
          ->whereNull('cpe_c.ccabaj')
          ->where('cpe_c.ccafem','>=',$fecin)
          ->where('cpe_c.ccafem','<=',$fecfin)
          ->where('cpe_c.id_empresa_negocio',Auth::user()->id_empresa_negocio)
          ->where(function ($query) {
            $query->where('cpe_c.tdocod','81');
            })
          ->where(function ($query) use ($IdProducto) {
            if(!empty($IdProducto)){
              $query->where('IdProducto',$IdProducto);
            }
          })
          ->where(function ($query) use($area) {
            if(!empty($area)){
               $query->where('cpe_c.are_emp_id',$area);
            }
           
          })
          ->orderby('IdCpe_cabecera','asc')
          ->get();

          $cantidad = $comprobantes->sum('cdecan');

        }elseif($tipo=='2'){
           $comprobantes = DB::tABLE('cpe_cabecera')
           ->select(DB::RAW('sum(cpe_detalle.cdecan) as cantidad'),'pronom','cdedes','cdepuni','productos.procod','productos.umecod','areas.are_emp_des','cpe_cabecera.are_emp_id',DB::RAW('sum(cpe_detalle.costo) as costo_total'))
                ->leftjoin('cpe_detalle','cpe_detalle.IdCpe_cabecera','cpe_cabecera.IdCpe_cabecera')
                ->leftjoin('productos','productos.IdProducto','cpe_detalle.IdProducto')
                ->leftjoin('areas','areas.are_emp_id','cpe_cabecera.are_emp_id')
                ->where('cpe_cabecera.ccafem','>=',$fecin)
                ->where('cpe_cabecera.ccafem','<=',$fecfin)
                ->where('cpe_cabecera.id_empresa_negocio',Auth::user()->id_empresa_negocio)
                ->whereNull('cpe_cabecera.ccabaj')
                ->where(function ($query) use ($IdProducto) {
                  if(!empty($IdProducto)){
                    $query->where('cpe_detalle.IdProducto',$IdProducto);
                  }
                })
                ->where(function ($query) {
                  $query->where('cpe_cabecera.tdocod','81');
                  })
                  ->where(function ($query) use($area) {
                  if(!empty($area)){
                     $query->where('cpe_cabecera.are_emp_id',$area);
                  }
                 
                  })
                ->orderby('cantidad','desc')
                ->groupby('cpe_cabecera.are_emp_id')
                ->groupby('cpe_detalle.IdProducto')
                ->get();

                $cantidad = $comprobantes->sum('cantidad');


        }


          $rutapdf = public_path().'/pdfreportes/';

        $nompdffile = 'REPORTE_SALIDAS_PRODUCTOS'.'.pdf';

       if(file_exists($rutapdf.$nompdffile)){

            unlink($rutapdf.$nompdffile);
        }

        $empresa = DB::tABLE('empresa')->where('IdEmpresa',Auth::user()->IdEmpresa)->first();
        $view = \View::make('formatos_reportes.reporte_pdf_salida_productos', compact('IdProducto','productos','cantidad','negocios','sucursal','comprobantes','fecin','fecfin','documento','areas','area','tipo','data_sucursal','empresa'));

                  
          $pdf = \App::make('dompdf.wrapper');
          $contenido = $view->render();
          $pdf->loadHTML($view)->setPaper('A4', 'lanscape')->save($rutapdf.$nompdffile);

          if (file_exists($rutapdf.$nompdffile))
          {
            $headers = array(
                  'Content-Type: application/pdf',
                );

            return response()->download($rutapdf.$nompdffile);

          }

       
      

    }




    public function reportes_taller(Request $request){

      $fecin = now()->modify('first day of this month')->format('Y-m-d');
      $fecfin = now()->modify('last day of this month')->format('Y-m-d');

      $clientes = DB::tABLE('cliente')->where('rucemp',Auth::user()->IdEmpresa)->get();


      $prod = $request->get('IdProducto');

      $negocios = DB::tABLE('empresa_negocios')->where('IdEmpresa',Auth::user()->IdEmpresa)->get();
             
      $almacenes = DB::tABLE('almacenes')
      ->where('id_empresa_negocio',$negocios->first()->id_empresa_negocio)->get();
            
      $sucursal = $negocios->first()->id_empresa_negocio;  

       $almacen = $almacenes->first()->id_almacen;  


      return view('empresas.reportes.reportes_taller',compact('almacen','sucursal','negocios','almacenes','clientes','fecin','fecfin'));

            

    }



    public function generar_reportes_taller(Request $request){

        $fecin = $request->get('fecin');
        $fecfin = $request->get('fecfin');

        $opcion = $request->get('opcion');

        $negocios = DB::tABLE('empresa_negocios')->get();
        $sucursal = $request->get('sucursal');
  

        if(empty($fecin)){

          $fecin = now()->modify('first day of this month')->format('Y-m-d');
          $fecfin = now()->modify('last day of this month')->format('Y-m-d');

        }

  
        $empresa = Empresa::findOrFail(Auth::user()->IdEmpresa);
           
        $IdEmpresa = Auth::user()->IdEmpresa;
    

         $ordenes = DB::tABLE('cpe_cabecera as cpe_c')->select('est_ord_nom','fechacot','cpe_c.estadocobrar','ccacodsun','ccafem','tdodes','cpe_c.serdoc','cpe_c.numdoc','tdides','cpe_c.ccandi','cpe_c.ccanom','monnom','cpe_c.ccaitv','cpe_c.tdocod','cpe_c.IdCpe_cabecera','cpe_c.ccanot','cpe_c.ccabaj','cpe_c.codhash','estadopago','estado','referencia','ccadessun','cliente.telefono','mod_nom','mar_nom','pronom','equi_ser','equi_id','observaciones','coordinadores.name as nom_coor','coordinadores.apeusu as ape_coor','tecnicos.name as nom_tec','tecnicos.apeusu as ape_tec','cpe_c.est_ord_cod','supervisor.name as nom_sup','supervisor.apeusu as ape_sup')
         ->leftjoin('estados_ordenes','estados_ordenes.est_ord_cod','cpe_c.est_ord_cod')
          ->leftjoin('cliente','cpe_c.clicod','=','cliente.clicod')
          ->leftjoin('tipo_documento as tip_d','cpe_c.tdocod','=','tip_d.tdocod')
            ->leftjoin('tipo_documento_identidad as tdi','cpe_c.tdicod','=','tdi.tdicod')
            ->leftjoin('moneda as mon','cpe_c.moncod','=','mon.moncod')
            ->leftjoin('productos as pro','cpe_c.equi_id','=','pro.IdProducto')
            ->leftjoin('modelos as mod','mod.mod_id','=','pro.modelo')
            ->leftjoin('marcas as mar','mar.mar_id','=','pro.marca')
            ->leftjoin('users as coordinadores','coordinadores.IdUsuario','cpe_c.coordinador')
            ->leftjoin('users as tecnicos','tecnicos.IdUsuario','cpe_c.tecnico')
            ->leftjoin('users as supervisor','supervisor.IdUsuario','cpe_c.supervisor')
          ->where('cpe_c.id_empresa_negocio',Auth::user()->id_empresa_negocio)
          ->where('cpe_c.est_ord_cod',$opcion)
            ->where('cpe_c.fechacot','>=',$fecin)
            ->where('cpe_c.fechacot','<=',$fecfin)
           ->where('cpe_c.tdocod','70')
           ->orderby('IdCpe_cabecera','desc')
          ->get();


     
             $vista = view('empresas.reportes.reporte_taller_ordenes',compact('ordenes'))->render();

              if($request->ajax()){
                 return response()->json(['vista'=>$vista]);

              }

      

    }


     public function generar_reportes_taller_excel(Request $request){

        $fecin = $request->get('fecin');
        $fecfin = $request->get('fecfin');

        $opcion = $request->get('opcion');

        $negocios = DB::tABLE('empresa_negocios')->get();
        $sucursal = $request->get('sucursal');
  

        if(empty($fecin)){

          $fecin = now()->modify('first day of this month')->format('Y-m-d');
          $fecfin = now()->modify('last day of this month')->format('Y-m-d');

        }

  
        $empresa = Empresa::findOrFail(Auth::user()->IdEmpresa);
           
        $IdEmpresa = Auth::user()->IdEmpresa;
    

         $ordenes = DB::tABLE('cpe_cabecera as cpe_c')->select('est_ord_nom','fechacot','cpe_c.estadocobrar','ccacodsun','ccafem','tdodes','cpe_c.serdoc','cpe_c.numdoc','tdides','cpe_c.ccandi','cpe_c.ccanom','monnom','cpe_c.ccaitv','cpe_c.tdocod','cpe_c.IdCpe_cabecera','cpe_c.ccanot','cpe_c.ccabaj','cpe_c.codhash','estadopago','estado','referencia','ccadessun','cliente.telefono','mod_nom','mar_nom','pronom','equi_ser','equi_id','observaciones','coordinadores.name as nom_coor','coordinadores.apeusu as ape_coor','tecnicos.name as nom_tec','tecnicos.apeusu as ape_tec','cpe_c.est_ord_cod','supervisor.name as nom_sup','supervisor.apeusu as ape_sup')
         ->leftjoin('estados_ordenes','estados_ordenes.est_ord_cod','cpe_c.est_ord_cod')
          ->leftjoin('cliente','cpe_c.clicod','=','cliente.clicod')
          ->leftjoin('tipo_documento as tip_d','cpe_c.tdocod','=','tip_d.tdocod')
            ->leftjoin('tipo_documento_identidad as tdi','cpe_c.tdicod','=','tdi.tdicod')
            ->leftjoin('moneda as mon','cpe_c.moncod','=','mon.moncod')
            ->leftjoin('productos as pro','cpe_c.equi_id','=','pro.IdProducto')
            ->leftjoin('modelos as mod','mod.mod_id','=','pro.modelo')
            ->leftjoin('marcas as mar','mar.mar_id','=','pro.marca')
            ->leftjoin('users as coordinadores','coordinadores.IdUsuario','cpe_c.coordinador')
            ->leftjoin('users as tecnicos','tecnicos.IdUsuario','cpe_c.tecnico')
            ->leftjoin('users as supervisor','supervisor.IdUsuario','cpe_c.supervisor')
          ->where('cpe_c.id_empresa_negocio',Auth::user()->id_empresa_negocio)
          ->where('cpe_c.est_ord_cod',$opcion)
            ->where('cpe_c.fechacot','>=',$fecin)
            ->where('cpe_c.fechacot','<=',$fecfin)
           ->where('cpe_c.tdocod','70')
           ->orderby('IdCpe_cabecera','desc')
          ->get();

                   Excel::create('ordenes', function($excel) use ($ordenes) {

                        $excel->sheet('ordenes', function($sheet) use ($ordenes) {

                       
                            
                                  $sheet->loadView('formatos_reportes_excel.reporte_taller_ordenes',compact('ordenes'));
                          
                                

                        });

                    })->export('xlsx'); 


      

    }



     public function generar_reportes_taller_pdf(Request $request){

        $fecin = $request->get('fecin');
        $fecfin = $request->get('fecfin');

        $opcion = $request->get('opcion');

        $negocios = DB::tABLE('empresa_negocios')->get();
        $sucursal = $request->get('sucursal');
  

        if(empty($fecin)){

          $fecin = now()->modify('first day of this month')->format('Y-m-d');
          $fecfin = now()->modify('last day of this month')->format('Y-m-d');

        }

        $dat_suc = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',$sucursal)->first();
  
        $empresa = Empresa::findOrFail(Auth::user()->IdEmpresa);
           
        $IdEmpresa = Auth::user()->IdEmpresa;
    

         $ordenes = DB::tABLE('cpe_cabecera as cpe_c')->select('est_ord_nom','fechacot','cpe_c.estadocobrar','ccacodsun','ccafem','tdodes','cpe_c.serdoc','cpe_c.numdoc','tdides','cpe_c.ccandi','cpe_c.ccanom','monnom','cpe_c.ccaitv','cpe_c.tdocod','cpe_c.IdCpe_cabecera','cpe_c.ccanot','cpe_c.ccabaj','cpe_c.codhash','estadopago','estado','referencia','ccadessun','cliente.telefono','mod_nom','mar_nom','pronom','equi_ser','equi_id','observaciones','coordinadores.name as nom_coor','coordinadores.apeusu as ape_coor','tecnicos.name as nom_tec','tecnicos.apeusu as ape_tec','cpe_c.est_ord_cod','supervisor.name as nom_sup','supervisor.apeusu as ape_sup')
         ->leftjoin('estados_ordenes','estados_ordenes.est_ord_cod','cpe_c.est_ord_cod')
          ->leftjoin('cliente','cpe_c.clicod','=','cliente.clicod')
          ->leftjoin('tipo_documento as tip_d','cpe_c.tdocod','=','tip_d.tdocod')
            ->leftjoin('tipo_documento_identidad as tdi','cpe_c.tdicod','=','tdi.tdicod')
            ->leftjoin('moneda as mon','cpe_c.moncod','=','mon.moncod')
            ->leftjoin('productos as pro','cpe_c.equi_id','=','pro.IdProducto')
            ->leftjoin('modelos as mod','mod.mod_id','=','pro.modelo')
            ->leftjoin('marcas as mar','mar.mar_id','=','pro.marca')
            ->leftjoin('users as coordinadores','coordinadores.IdUsuario','cpe_c.coordinador')
            ->leftjoin('users as tecnicos','tecnicos.IdUsuario','cpe_c.tecnico')
            ->leftjoin('users as supervisor','supervisor.IdUsuario','cpe_c.supervisor')
          ->where('cpe_c.id_empresa_negocio',Auth::user()->id_empresa_negocio)
          ->where('cpe_c.est_ord_cod',$opcion)
            ->where('cpe_c.fechacot','>=',$fecin)
            ->where('cpe_c.fechacot','<=',$fecfin)
           ->where('cpe_c.tdocod','70')
           ->orderby('IdCpe_cabecera','desc')
          ->get();

          $rutapdf = public_path().'/pdfreportes/';

          $nompdffile = 'REPORTE_ORDENES.pdf';

          if(file_exists($rutapdf.$nompdffile)){
            unlink($rutapdf.$nompdffile);
          }


          $view = \View::make('formatos_reportes.reportes_taller_ordenes', compact('ordenes','dat_suc','empresa'));

                  
          $pdf = \App::make('dompdf.wrapper');
          $contenido = $view->render();
          $pdf->loadHTML($view)->setPaper('A4', 'lanscape')->save($rutapdf.$nompdffile);



          if (file_exists($rutapdf.$nompdffile))
          {
            $headers = array(
              'Content-Type: application/pdf',
            );

            return response()->download($rutapdf.$nompdffile);

          }


      

    }


 public function generar_arqueodiario_excel(Request $request){
        
         $medios = DB::tABLE('cuentas_cobrar_medios')->get();

     foreach($medios as $med){

        if($med->med_pag_id=='4'){
                 DB::tABLE('cuentas_cobrar_detalle')
                 ->where('numero_recibo',$med->numero_recibo)
                 ->where('cue_cob_det_id',$med->cue_cob_det_id)
                 ->update(['monto_efectivo'=>$med->monto]); 
        }

         if($med->med_pag_id=='5'){
                DB::tABLE('cuentas_cobrar_detalle')
                ->where('numero_recibo',$med->numero_recibo)
                ->where('cue_cob_det_id',$med->cue_cob_det_id)
                ->update(['monto_deposito'=>$med->monto]);
        }


        
       
      }


        $cliente = $request->get('cliente');
        $documento = $request->get('documento');
 
        $fecha = $request->get('fecin');

        $sucursal = DB::tABLE('empresa_negocios')
        ->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
        ->first();

        $empresa = DB::tABLE('empresa')
        ->where('IdEmpresa',$sucursal->IdEmpresa)
        ->first();


        $usuario = DB::tABLE('users')->where('IdUsuario',Auth::user()->IdUsuario)->first();
        
        $registroventascontado = DB::table('cpe_cabecera as cpe_c')->select('fecha_hora','ccacodsun','ccafem','tdodes','cpe_c.serdoc','cpe_c.numdoc','tdides','cpe_c.ccandi','cpe_c.ccanom','monnom','cpe_c.ccaitv','cpe_c.IdEmpresa','cpe_c.tdocod','cpe_c.IdCpe_cabecera','cpe_c.ccanot','cpe_c.ccabaj','cpe_c.codhash','cpe_c.estado','cpe_c.enviado','totalcontado','totalcredito')
        ->join('tipo_documento as tip_d','cpe_c.tdocod','=','tip_d.tdocod')
        ->join('tipo_documento_identidad as tdi','cpe_c.tdicod','=','tdi.tdicod')
        ->join('moneda as mon','cpe_c.moncod','=','mon.moncod')
        ->whereNull('cpe_c.ccabaj')
        ->where('cpe_c.id_empresa_negocio',$sucursal->id_empresa_negocio)
        ->where(function ($query) {
          $query->Where('cpe_c.tdocod','03')
                ->orWhere('cpe_c.tdocod','01')
                ->orWhere('cpe_c.tdocod','13');
              
          })
        ->where('estadopago','CONTADO')
        ->where('ccafem',$fecha)
        ->get();


        $ventascontado = $registroventascontado->sum('ccaitv');

        $registronotascontado = DB::table('cpe_cabecera as cpe_c')->select('fecha_hora','ccacodsun','ccafem','tdodes','cpe_c.serdoc','cpe_c.numdoc','tdides','cpe_c.ccandi','cpe_c.ccanom','monnom','cpe_c.ccaitv','cpe_c.IdEmpresa','cpe_c.tdocod','cpe_c.IdCpe_cabecera','cpe_c.ccanot','cpe_c.ccabaj','cpe_c.codhash','cpe_c.estado','cpe_c.enviado','totalcontado','totalcredito')
        ->join('tipo_documento as tip_d','cpe_c.tdocod','=','tip_d.tdocod')
        ->join('tipo_documento_identidad as tdi','cpe_c.tdicod','=','tdi.tdicod')
        ->join('moneda as mon','cpe_c.moncod','=','mon.moncod')
        ->whereNull('cpe_c.ccabaj')
        ->where('cpe_c.id_empresa_negocio',$sucursal->id_empresa_negocio)
        ->where('cpe_c.tdocod','13')
        ->where('estadopago','CONTADO')
        ->where('ccafem',$fecha)
        ->get();

        

        $notascontado = $registronotascontado->sum('ccaitv');

         $registroconsumo = DB::table('cpe_cabecera as cpe_c')->select('fecha_hora','ccacodsun','ccafem','tdodes','cpe_c.serdoc','cpe_c.numdoc','tdides','cpe_c.ccandi','cpe_c.ccanom','monnom','cpe_c.ccaitv','cpe_c.IdEmpresa','cpe_c.tdocod','cpe_c.IdCpe_cabecera','cpe_c.ccanot','cpe_c.ccabaj','cpe_c.codhash','cpe_c.estado','cpe_c.enviado','totalcontado','totalcredito')
        ->join('tipo_documento as tip_d','cpe_c.tdocod','=','tip_d.tdocod')
        ->join('tipo_documento_identidad as tdi','cpe_c.tdicod','=','tdi.tdicod')
        ->join('moneda as mon','cpe_c.moncod','=','mon.moncod')
        ->whereNull('cpe_c.ccabaj')
        ->where('cpe_c.id_empresa_negocio',$sucursal->id_empresa_negocio)
        ->where('cpe_c.tdocod','99')
        ->where('estadopago','CONTADO')
        ->where('ccafem',$fecha)
        ->get();

        

        $consumo = $registroconsumo->sum('ccaitv');


        $registronotascredito = DB::table('cpe_cabecera as cpe_c')->select('fecha_hora','ccacodsun','ccafem','tdodes','cpe_c.serdoc','cpe_c.numdoc','tdides','cpe_c.ccandi','cpe_c.ccanom','monnom','cpe_c.ccaitv','cpe_c.IdEmpresa','cpe_c.tdocod','cpe_c.IdCpe_cabecera','cpe_c.ccanot','cpe_c.ccabaj','cpe_c.codhash','cpe_c.estado','cpe_c.enviado','totalcontado','totalcredito')
        ->join('tipo_documento as tip_d','cpe_c.tdocod','=','tip_d.tdocod')
        ->join('tipo_documento_identidad as tdi','cpe_c.tdicod','=','tdi.tdicod')
        ->join('moneda as mon','cpe_c.moncod','=','mon.moncod')
        ->whereNull('cpe_c.ccabaj')
        ->where('cpe_c.id_empresa_negocio',$sucursal->id_empresa_negocio)
        ->Where('cpe_c.tdocod','13')
        ->where('estadopago','CREDITO')
        ->where('ccafem',$fecha)
        ->get();


        $notascredito = $registronotascredito->sum('ccaitv');



        $registroventascontadof = DB::table('cpe_cabecera as cpe_c')->select('fecha_hora','ccacodsun','ccafem','tdodes','cpe_c.serdoc','cpe_c.numdoc','tdides','cpe_c.ccandi','cpe_c.ccanom','monnom','cpe_c.ccaitv','cpe_c.IdEmpresa','cpe_c.tdocod','cpe_c.IdCpe_cabecera','cpe_c.ccanot','cpe_c.ccabaj','cpe_c.codhash','cpe_c.estado','cpe_c.enviado','totalcontado','totalcredito')
        ->join('tipo_documento as tip_d','cpe_c.tdocod','=','tip_d.tdocod')
        ->join('tipo_documento_identidad as tdi','cpe_c.tdicod','=','tdi.tdicod')
        ->join('moneda as mon','cpe_c.moncod','=','mon.moncod')
        ->whereNull('cpe_c.ccabaj')
        ->where('cpe_c.id_empresa_negocio',$sucursal->id_empresa_negocio)
        ->where(function ($query) {
          $query->Where('cpe_c.tdocod','01');   
          })
        ->where('estadopago','CONTADO')
        ->where('ccafem',$fecha)
        ->get();


        $ventasfactura = $registroventascontadof->sum('ccaitv');

        $registroventascontadob = DB::table('cpe_cabecera as cpe_c')->select('fecha_hora','ccacodsun','ccafem','tdodes','cpe_c.serdoc','cpe_c.numdoc','tdides','cpe_c.ccandi','cpe_c.ccanom','monnom','cpe_c.ccaitv','cpe_c.IdEmpresa','cpe_c.tdocod','cpe_c.IdCpe_cabecera','cpe_c.ccanot','cpe_c.ccabaj','cpe_c.codhash','cpe_c.estado','cpe_c.enviado','totalcontado','totalcredito')
        ->join('tipo_documento as tip_d','cpe_c.tdocod','=','tip_d.tdocod')
        ->join('tipo_documento_identidad as tdi','cpe_c.tdicod','=','tdi.tdicod')
        ->join('moneda as mon','cpe_c.moncod','=','mon.moncod')
        ->whereNull('cpe_c.ccabaj')
        ->where('cpe_c.id_empresa_negocio',$sucursal->id_empresa_negocio)
        ->where(function ($query) {
          $query->Where('cpe_c.tdocod','03');              
          })
        ->where('estadopago','CONTADO')
        ->where('ccafem',$fecha)
        ->get();


        $ventasboleta = $registroventascontadob->sum('ccaitv');

        $registroventascontadofc = DB::table('cpe_cabecera as cpe_c')->select('fecha_hora','ccacodsun','ccafem','tdodes','cpe_c.serdoc','cpe_c.numdoc','tdides','cpe_c.ccandi','cpe_c.ccanom','monnom','cpe_c.ccaitv','cpe_c.IdEmpresa','cpe_c.tdocod','cpe_c.IdCpe_cabecera','cpe_c.ccanot','cpe_c.ccabaj','cpe_c.codhash','cpe_c.estado','cpe_c.enviado','totalcontado','totalcredito')
        ->join('tipo_documento as tip_d','cpe_c.tdocod','=','tip_d.tdocod')
        ->join('tipo_documento_identidad as tdi','cpe_c.tdicod','=','tdi.tdicod')
        ->join('moneda as mon','cpe_c.moncod','=','mon.moncod')
        ->whereNull('cpe_c.ccabaj')
        ->where('cpe_c.id_empresa_negocio',$sucursal->id_empresa_negocio)
        ->where(function ($query) {
          $query->Where('cpe_c.tdocod','01');   
          })
        ->where('estadopago','CREDITO')
        ->where('ccafem',$fecha)
        ->get();


        $ventasfacturac = $registroventascontadofc->sum('ccaitv');

        $registroventascontadobc = DB::table('cpe_cabecera as cpe_c')->select('fecha_hora','ccacodsun','ccafem','tdodes','cpe_c.serdoc','cpe_c.numdoc','tdides','cpe_c.ccandi','cpe_c.ccanom','monnom','cpe_c.ccaitv','cpe_c.IdEmpresa','cpe_c.tdocod','cpe_c.IdCpe_cabecera','cpe_c.ccanot','cpe_c.ccabaj','cpe_c.codhash','cpe_c.estado','cpe_c.enviado','totalcontado','totalcredito')
        ->join('tipo_documento as tip_d','cpe_c.tdocod','=','tip_d.tdocod')
        ->join('tipo_documento_identidad as tdi','cpe_c.tdicod','=','tdi.tdicod')
        ->join('moneda as mon','cpe_c.moncod','=','mon.moncod')
        ->whereNull('cpe_c.ccabaj')
        ->where('cpe_c.id_empresa_negocio',$sucursal->id_empresa_negocio)
        ->where(function ($query) {
          $query->Where('cpe_c.tdocod','03');              
          })
        ->where('estadopago','CREDITO')
        ->where('ccafem',$fecha)
        ->get();


        $ventasboletac = $registroventascontadobc->sum('ccaitv');
        

        $registroventascredito = DB::table('cpe_cabecera as cpe_c')->select('fecha_hora','ccacodsun','ccafem','tdodes','cpe_c.serdoc','cpe_c.numdoc','tdides','cpe_c.ccandi','cpe_c.ccanom','monnom','cpe_c.ccaitv','cpe_c.IdEmpresa','cpe_c.tdocod','cpe_c.IdCpe_cabecera','cpe_c.ccanot','cpe_c.ccabaj','cpe_c.codhash','cpe_c.estado','cpe_c.enviado','totalcontado','totalcredito')
        ->leftjoin('tipo_documento as tip_d','cpe_c.tdocod','=','tip_d.tdocod')
        ->leftjoin('tipo_documento_identidad as tdi','cpe_c.tdicod','=','tdi.tdicod')
        ->leftjoin('moneda as mon','cpe_c.moncod','=','mon.moncod')
        ->whereNull('cpe_c.ccabaj')
        ->where('cpe_c.id_empresa_negocio',$sucursal->id_empresa_negocio)
        ->where(function ($query) {
          $query->Where('cpe_c.tdocod','03')
                ->orWhere('cpe_c.tdocod','01')
                ->orWhere('cpe_c.tdocod','13');
              
          })
        ->where('estadopago','CREDITO')
        ->where('ccafem',$fecha)
        ->get();
        

        $ventascredito = $registroventascredito->sum('ccaitv');
        
        $cobranzas = DB::TABLE('cuentas_cobrar_detalle')->select('fec_dep','cuentas_cobrar_detalle.abono','comentario','numero_recibo',DB::raw("(SELECT CONCAT(name,apeusu) FROM users WHERE IdUsuario= cuentas_cobrar_detalle.vendedor) as vendedor"),DB::raw("(SELECT clinom FROM cliente WHERE clicod = cuentas_cobrar.clicod) as cliente"),DB::raw("(SELECT clinum FROM cliente WHERE clicod = cuentas_cobrar.clicod) as ruc"),'serdoc','numdoc','cuentas_cobrar.clicod','total_detalle','saldo_detalle','fec_reg','tdocod','ccanom','monto_efectivo','monto_deposito')
            ->join('cuentas_cobrar','cuentas_cobrar.cue_cob_id','cuentas_cobrar_detalle.cue_cob_id')
            ->join('cpe_cabecera','cpe_cabecera.IdCpe_cabecera','cuentas_cobrar.IdCpe_cabecera')
           // ->whereNull('cpe_cabecera.ccabaj')
            ->where('cuentas_cobrar.id_empresa_negocio',$sucursal->id_empresa_negocio)
          
            ->where('fec_reg','=',$fecha)
            ->orderby('cliente','asc')
            ->get();

        
          $totalcobranzas = $cobranzas->sum('abono');
           $totalcobranzasdepositos = $cobranzas->sum('monto_deposito');

          $pagos = DB::TABLE('cuentas_pagar_detalle')->select('fec_reg','fec_dep','cuentas_pagar_detalle.abono',DB::raw("(SELECT prov_raz FROM proveedor WHERE prov_id = compras_cabecera.prov_id) as proveedor"),DB::raw("(SELECT prov_ruc FROM proveedor WHERE prov_id = compras_cabecera.prov_id) as ruc"),'com_doc_ser','com_doc_num','compras_cabecera.prov_id','compras_cabecera.tdocod')
            ->join('cuentas_pagar','cuentas_pagar.cue_pag_id','cuentas_pagar_detalle.cue_pag_id')
            ->join('compras_cabecera','compras_cabecera.com_cab_id','cuentas_pagar.com_cab_id')
            ->where('est_compra','Registrado')
            ->where('cuentas_pagar.id_empresa_negocio',$sucursal->id_empresa_negocio)
            ->where('fec_reg','=',$fecha)
            ->orderby('proveedor','asc')
            ->get();

      $totalpagos = $pagos->sum('abono');
   

        $totalgasto = DB::TABLE('gastos_cabecera')
        ->leftjoin('moneda','gastos_cabecera.mon_id','moneda.moncod')
        ->leftjoin('tipo_documento','gastos_cabecera.tdocod','tipo_documento.tdocod')
        ->join('usuario_gastos','usuario_gastos.gast_cab_id','gastos_cabecera.gast_cab_id')
        ->where('tipo_movimiento','GASTO')
        ->where('est_gasto','Registrado')
        ->where('gast_fec',$fecha)
        ->orderby('gastos_cabecera.gast_Cab_id','desc')
        ->sum('total_gast');

         $totalingreso = DB::TABLE('gastos_cabecera')
        ->leftjoin('moneda','gastos_cabecera.mon_id','moneda.moncod')
        ->leftjoin('tipo_documento','gastos_cabecera.tdocod','tipo_documento.tdocod')
        ->join('usuario_gastos','usuario_gastos.gast_cab_id','gastos_cabecera.gast_cab_id')
        ->where('tipo_movimiento','INGRESO')
        ->where('est_gasto','Registrado')
        ->where('gast_fec',$fecha)
        ->orderby('gastos_cabecera.gast_Cab_id','desc')
        ->sum('total_gast');


        $grup_gas = DB::TABLE('gastos_cabecera')->select('tip_gas_nom',DB::RAW('sum(gastos_detalle.total) as total'))
         ->leftjoin('gastos_detalle','gastos_cabecera.gast_cab_id','gastos_detalle.gast_cab_id')
         ->join('usuario_gastos','usuario_gastos.gast_cab_id','gastos_cabecera.gast_cab_id')
        ->leftjoin('tipo_gastos','tipo_gastos.tip_gas_id','gastos_detalle.tip_gas_id')
        ->where('gastos_cabecera.id_empresa_negocio',Auth::user()->id_empresa_negocio)
        ->where('tipo_movimiento','GASTO')
        ->where('est_gasto','Registrado')
        ->where('gast_fec',$fecha)
        ->orderby('gastos_cabecera.gast_Cab_id','desc')
        ->groupby('tipo_gastos.tip_gas_id')
        ->get();

    
        $grup_ing = DB::TABLE('gastos_cabecera')->select('tip_gas_nom',DB::RAW('sum(gastos_detalle.total) as total'))
         ->leftjoin('gastos_detalle','gastos_cabecera.gast_cab_id','gastos_detalle.gast_cab_id')
         ->join('usuario_gastos','usuario_gastos.gast_cab_id','gastos_cabecera.gast_cab_id')
        ->leftjoin('tipo_gastos','tipo_gastos.tip_gas_id','gastos_detalle.tip_gas_id')
        ->where('gastos_cabecera.id_empresa_negocio',Auth::user()->id_empresa_negocio)
        ->where('tipo_movimiento','GASTO')
        ->where('est_gasto','Registrado')
        ->where('gast_fec',$fecha)
        ->orderby('gastos_cabecera.gast_Cab_id','desc')
        ->groupby('tipo_gastos.tip_gas_id')
        ->get();

      
        $compras = DB::TABLE('compras_cabecera')
        ->leftjoin('proveedor','compras_cabecera.prov_id','proveedor.prov_id')
        ->leftjoin('moneda','compras_cabecera.mon_id','moneda.moncod')
        ->leftjoin('tipo_documento','compras_cabecera.tdocod','tipo_documento.tdocod')
        ->where('compras_cabecera.id_empresa_negocio',Auth::user()->id_empresa_negocio)
         ->where('com_fec',$fecha)
        ->where('est_compra','Registrado')
        ->where('compras_cabecera.tdocod','!=','80')
        ->sum('total_com');


        $gastos = DB::TABLE('gastos_cabecera')
        ->join('gastos_detalle','gastos_cabecera.gast_cab_id','gastos_detalle.gast_cab_id')
        ->leftjoin('moneda','gastos_cabecera.mon_id','moneda.moncod')
        ->leftjoin('tipo_gastos','tipo_gastos.tip_gas_id','gastos_detalle.tip_gas_id')
        ->leftjoin('tipo_documento','gastos_cabecera.tdocod','tipo_documento.tdocod')
       
        ->where('gastos_cabecera.id_empresa_negocio',Auth::user()->id_empresa_negocio)
        ->where('tipo_movimiento','GASTO')
        ->where('est_gasto','Registrado')
        ->where('gast_fec',$fecha)
        ->orderby('gastos_cabecera.gast_cab_id','desc')
        ->get();

          $ingresos = DB::TABLE('gastos_cabecera')
        ->leftjoin('gastos_detalle','gastos_cabecera.gast_cab_id','gastos_detalle.gast_cab_id')
        ->leftjoin('moneda','gastos_cabecera.mon_id','moneda.moncod')
        ->leftjoin('tipo_gastos','tipo_gastos.tip_gas_id','gastos_detalle.tip_gas_id')
        ->leftjoin('tipo_documento','gastos_cabecera.tdocod','tipo_documento.tdocod')
        ->where('gastos_cabecera.id_empresa_negocio',Auth::user()->id_empresa_negocio)
        ->where('tipo_movimiento','INGRESO')
        ->where('est_gasto','Registrado')
        ->where('gast_fec',$fecha)
        ->orderby('gastos_cabecera.gast_cab_id','desc')
        ->get();


        $totalgast = $gastos->sum('total');
        $totalingreso = $ingresos->sum('total');

        $saldo = 0;

        $fecha1 = Carbon::parse($fecha)->format('d-m-Y');

        $bus_saldo = DB::tABLE('saldos_arqueo')
        ->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
        ->where('fecha','<',$fecha)
        ->orderby('fecha','desc')
        ->first();

        if(empty($bus_saldo)){
          $saldo = 0;
        }else{
          $saldo = $bus_saldo->saldo;
        }

        $saldo_nuevo = ($saldo+$ventascontado+$totalingreso+$totalcobranzas)-($totalgast+$totalpagos);


        $bus_saldo_act = DB::tABLE('saldos_arqueo')
        ->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
        ->where('fecha',$fecha)
        ->first();

        if(empty($bus_saldo_act)){
          DB::tABLE('saldos_arqueo')
          ->insert(['fecha'=>$fecha,'saldo'=>$saldo_nuevo,'id_empresa_negocio'=>Auth::user()->id_empresa_negocio]);
        }else{
          DB::tABLE('saldos_arqueo')
          ->where('sal_arq_id',$bus_saldo_act->sal_arq_id)
          ->update(['saldo'=>$saldo_nuevo]);
        }
       


        $rutapdf = public_path().'/pdfreportes/';

        $nompdffile = 'Arqueo_Resumen_Diario_'.$fecha.'.pdf';

       if(file_exists($rutapdf.$nompdffile)){

            unlink($rutapdf.$nompdffile);
        }


          Excel::create('ARQUEO_DIARIO', function($excel) use ($totalcobranzasdepositos,$compras,$totalgasto,$totalingreso,$registroventascontado,$registroventascredito,$ventascredito,$ventascontado,$cobranzas,$totalcobranzas,$usuario,$sucursal,$empresa,$grup_gas,$grup_ing,$gastos,$totalgast,$ingresos,$fecha,$saldo_nuevo,$saldo,$pagos,$totalpagos,$registroventascontadof,$registroventascontadob,$ventasfactura,$ventasboleta,$registroventascontadofc,$registroventascontadobc,$ventasfacturac,$ventasboletac,$registronotascontado,$registronotascredito,$notascontado,$notascredito,$registroconsumo,$consumo) {

                        $excel->sheet('kardex_fisico', function($sheet) use ($totalcobranzasdepositos,$compras,$totalgasto,$totalingreso,$registroventascontado,$registroventascredito,$ventascredito,$ventascontado,$cobranzas,$totalcobranzas,$usuario,$sucursal,$empresa,$grup_gas,$grup_ing,$gastos,$totalgast,$ingresos,$fecha,$saldo_nuevo,$saldo,$pagos,$totalpagos,$registroventascontadof,$registroventascontadob,$ventasfactura,$ventasboleta,$registroventascontadofc,$registroventascontadobc,$ventasfacturac,$ventasboletac,$registronotascontado,$registronotascredito,$notascontado,$notascredito,$registroconsumo,$consumo) {

                       
                            
                                  $sheet->loadView('formatos_reportes_excel.arqueo_diario',compact('totalcobranzasdepositos','compras','totalgasto','totalingreso','registroventascontado','registroventascredito','ventascredito','ventascontado','cobranzas','totalcobranzas','usuario','sucursal','empresa','grup_gas','totalgasto','grup_ing','gastos','totalgast','totalingreso','ingresos','fecha','saldo_nuevo','saldo','pagos','totalpagos','registroventascontadof','registroventascontadob','ventasfactura','ventasboleta','registroventascontadofc','registroventascontadobc','ventasfacturac','ventasboletac','registronotascontado','registronotascredito','notascontado','notascredito','registroconsumo','consumo'));
                          
                                

                        });

                    })->export('xlsx'); 



      

       if (file_exists($rutapdf.$nompdffile))
      {
        $headers = array(
              'Content-Type: application/pdf',
            );

        return response()->download($rutapdf.$nompdffile);
      }

      



   

    }





public function generar_arqueodiarioresumen_excel(Request $request){
        
         $medios = DB::tABLE('cuentas_cobrar_medios')->get();

     foreach($medios as $med){

        if($med->med_pag_id=='4'){
                 DB::tABLE('cuentas_cobrar_detalle')
                 ->where('numero_recibo',$med->numero_recibo)
                 ->where('cue_cob_det_id',$med->cue_cob_det_id)
                 ->update(['monto_efectivo'=>$med->monto]); 
        }

         if($med->med_pag_id=='5'){
                DB::tABLE('cuentas_cobrar_detalle')
                ->where('numero_recibo',$med->numero_recibo)
                ->where('cue_cob_det_id',$med->cue_cob_det_id)
                ->update(['monto_deposito'=>$med->monto]);
        }


        
       
      }


        $cliente = $request->get('cliente');
        $documento = $request->get('documento');
 
        $fecha = $request->get('fecin');

        $sucursal = DB::tABLE('empresa_negocios')
        ->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
        ->first();

        $empresa = DB::tABLE('empresa')
        ->where('IdEmpresa',$sucursal->IdEmpresa)
        ->first();


        $usuario = DB::tABLE('users')->where('IdUsuario',Auth::user()->IdUsuario)->first();
        
        $registroventascontado = DB::table('cpe_cabecera as cpe_c')->select('fecha_hora','ccacodsun','ccafem','tdodes','cpe_c.serdoc','cpe_c.numdoc','tdides','cpe_c.ccandi','cpe_c.ccanom','monnom','cpe_c.ccaitv','cpe_c.IdEmpresa','cpe_c.tdocod','cpe_c.IdCpe_cabecera','cpe_c.ccanot','cpe_c.ccabaj','cpe_c.codhash','cpe_c.estado','cpe_c.enviado','totalcontado','totalcredito')
        ->join('tipo_documento as tip_d','cpe_c.tdocod','=','tip_d.tdocod')
        ->join('tipo_documento_identidad as tdi','cpe_c.tdicod','=','tdi.tdicod')
        ->join('moneda as mon','cpe_c.moncod','=','mon.moncod')
        ->whereNull('cpe_c.ccabaj')
        ->where('cpe_c.id_empresa_negocio',$sucursal->id_empresa_negocio)
        ->where(function ($query) {
          $query->Where('cpe_c.tdocod','03')
                ->orWhere('cpe_c.tdocod','01')
                ->orWhere('cpe_c.tdocod','13');
              
          })
        ->where('estadopago','CONTADO')
        ->where('ccafem',$fecha)
        ->get();


        $ventascontado = $registroventascontado->sum('ccaitv');

        $registronotascontado = DB::table('cpe_cabecera as cpe_c')->select('fecha_hora','ccacodsun','ccafem','tdodes','cpe_c.serdoc','cpe_c.numdoc','tdides','cpe_c.ccandi','cpe_c.ccanom','monnom','cpe_c.ccaitv','cpe_c.IdEmpresa','cpe_c.tdocod','cpe_c.IdCpe_cabecera','cpe_c.ccanot','cpe_c.ccabaj','cpe_c.codhash','cpe_c.estado','cpe_c.enviado','totalcontado','totalcredito')
        ->join('tipo_documento as tip_d','cpe_c.tdocod','=','tip_d.tdocod')
        ->join('tipo_documento_identidad as tdi','cpe_c.tdicod','=','tdi.tdicod')
        ->join('moneda as mon','cpe_c.moncod','=','mon.moncod')
        ->whereNull('cpe_c.ccabaj')
        ->where('cpe_c.id_empresa_negocio',$sucursal->id_empresa_negocio)
        ->where('cpe_c.tdocod','13')
        ->where('estadopago','CONTADO')
        ->where('ccafem',$fecha)
        ->get();

        

        $notascontado = $registronotascontado->sum('ccaitv');

         $registroconsumo = DB::table('cpe_cabecera as cpe_c')->select('fecha_hora','ccacodsun','ccafem','tdodes','cpe_c.serdoc','cpe_c.numdoc','tdides','cpe_c.ccandi','cpe_c.ccanom','monnom','cpe_c.ccaitv','cpe_c.IdEmpresa','cpe_c.tdocod','cpe_c.IdCpe_cabecera','cpe_c.ccanot','cpe_c.ccabaj','cpe_c.codhash','cpe_c.estado','cpe_c.enviado','totalcontado','totalcredito')
        ->join('tipo_documento as tip_d','cpe_c.tdocod','=','tip_d.tdocod')
        ->join('tipo_documento_identidad as tdi','cpe_c.tdicod','=','tdi.tdicod')
        ->join('moneda as mon','cpe_c.moncod','=','mon.moncod')
        ->whereNull('cpe_c.ccabaj')
        ->where('cpe_c.id_empresa_negocio',$sucursal->id_empresa_negocio)
        ->where('cpe_c.tdocod','99')
        ->where('estadopago','CONTADO')
        ->where('ccafem',$fecha)
        ->get();

        

        $consumo = $registroconsumo->sum('ccaitv');


        $registronotascredito = DB::table('cpe_cabecera as cpe_c')->select('fecha_hora','ccacodsun','ccafem','tdodes','cpe_c.serdoc','cpe_c.numdoc','tdides','cpe_c.ccandi','cpe_c.ccanom','monnom','cpe_c.ccaitv','cpe_c.IdEmpresa','cpe_c.tdocod','cpe_c.IdCpe_cabecera','cpe_c.ccanot','cpe_c.ccabaj','cpe_c.codhash','cpe_c.estado','cpe_c.enviado','totalcontado','totalcredito')
        ->join('tipo_documento as tip_d','cpe_c.tdocod','=','tip_d.tdocod')
        ->join('tipo_documento_identidad as tdi','cpe_c.tdicod','=','tdi.tdicod')
        ->join('moneda as mon','cpe_c.moncod','=','mon.moncod')
        ->whereNull('cpe_c.ccabaj')
        ->where('cpe_c.id_empresa_negocio',$sucursal->id_empresa_negocio)
        ->Where('cpe_c.tdocod','13')
        ->where('estadopago','CREDITO')
        ->where('ccafem',$fecha)
        ->get();


        $notascredito = $registronotascredito->sum('ccaitv');



        $registroventascontadof = DB::table('cpe_cabecera as cpe_c')->select('fecha_hora','ccacodsun','ccafem','tdodes','cpe_c.serdoc','cpe_c.numdoc','tdides','cpe_c.ccandi','cpe_c.ccanom','monnom','cpe_c.ccaitv','cpe_c.IdEmpresa','cpe_c.tdocod','cpe_c.IdCpe_cabecera','cpe_c.ccanot','cpe_c.ccabaj','cpe_c.codhash','cpe_c.estado','cpe_c.enviado','totalcontado','totalcredito')
        ->join('tipo_documento as tip_d','cpe_c.tdocod','=','tip_d.tdocod')
        ->join('tipo_documento_identidad as tdi','cpe_c.tdicod','=','tdi.tdicod')
        ->join('moneda as mon','cpe_c.moncod','=','mon.moncod')
        ->whereNull('cpe_c.ccabaj')
        ->where('cpe_c.id_empresa_negocio',$sucursal->id_empresa_negocio)
        ->where(function ($query) {
          $query->Where('cpe_c.tdocod','01');   
          })
        ->where('estadopago','CONTADO')
        ->where('ccafem',$fecha)
        ->get();


        $ventasfactura = $registroventascontadof->sum('ccaitv');

        $registroventascontadob = DB::table('cpe_cabecera as cpe_c')->select('fecha_hora','ccacodsun','ccafem','tdodes','cpe_c.serdoc','cpe_c.numdoc','tdides','cpe_c.ccandi','cpe_c.ccanom','monnom','cpe_c.ccaitv','cpe_c.IdEmpresa','cpe_c.tdocod','cpe_c.IdCpe_cabecera','cpe_c.ccanot','cpe_c.ccabaj','cpe_c.codhash','cpe_c.estado','cpe_c.enviado','totalcontado','totalcredito')
        ->join('tipo_documento as tip_d','cpe_c.tdocod','=','tip_d.tdocod')
        ->join('tipo_documento_identidad as tdi','cpe_c.tdicod','=','tdi.tdicod')
        ->join('moneda as mon','cpe_c.moncod','=','mon.moncod')
        ->whereNull('cpe_c.ccabaj')
        ->where('cpe_c.id_empresa_negocio',$sucursal->id_empresa_negocio)
        ->where(function ($query) {
          $query->Where('cpe_c.tdocod','03');              
          })
        ->where('estadopago','CONTADO')
        ->where('ccafem',$fecha)
        ->get();


        $ventasboleta = $registroventascontadob->sum('ccaitv');

        $registroventascontadofc = DB::table('cpe_cabecera as cpe_c')->select('fecha_hora','ccacodsun','ccafem','tdodes','cpe_c.serdoc','cpe_c.numdoc','tdides','cpe_c.ccandi','cpe_c.ccanom','monnom','cpe_c.ccaitv','cpe_c.IdEmpresa','cpe_c.tdocod','cpe_c.IdCpe_cabecera','cpe_c.ccanot','cpe_c.ccabaj','cpe_c.codhash','cpe_c.estado','cpe_c.enviado','totalcontado','totalcredito')
        ->join('tipo_documento as tip_d','cpe_c.tdocod','=','tip_d.tdocod')
        ->join('tipo_documento_identidad as tdi','cpe_c.tdicod','=','tdi.tdicod')
        ->join('moneda as mon','cpe_c.moncod','=','mon.moncod')
        ->whereNull('cpe_c.ccabaj')
        ->where('cpe_c.id_empresa_negocio',$sucursal->id_empresa_negocio)
        ->where(function ($query) {
          $query->Where('cpe_c.tdocod','01');   
          })
        ->where('estadopago','CREDITO')
        ->where('ccafem',$fecha)
        ->get();


        $ventasfacturac = $registroventascontadofc->sum('ccaitv');

        $registroventascontadobc = DB::table('cpe_cabecera as cpe_c')->select('fecha_hora','ccacodsun','ccafem','tdodes','cpe_c.serdoc','cpe_c.numdoc','tdides','cpe_c.ccandi','cpe_c.ccanom','monnom','cpe_c.ccaitv','cpe_c.IdEmpresa','cpe_c.tdocod','cpe_c.IdCpe_cabecera','cpe_c.ccanot','cpe_c.ccabaj','cpe_c.codhash','cpe_c.estado','cpe_c.enviado','totalcontado','totalcredito')
        ->join('tipo_documento as tip_d','cpe_c.tdocod','=','tip_d.tdocod')
        ->join('tipo_documento_identidad as tdi','cpe_c.tdicod','=','tdi.tdicod')
        ->join('moneda as mon','cpe_c.moncod','=','mon.moncod')
        ->whereNull('cpe_c.ccabaj')
        ->where('cpe_c.id_empresa_negocio',$sucursal->id_empresa_negocio)
        ->where(function ($query) {
          $query->Where('cpe_c.tdocod','03');              
          })
        ->where('estadopago','CREDITO')
        ->where('ccafem',$fecha)
        ->get();


        $ventasboletac = $registroventascontadobc->sum('ccaitv');
        

        $registroventascredito = DB::table('cpe_cabecera as cpe_c')->select('fecha_hora','ccacodsun','ccafem','tdodes','cpe_c.serdoc','cpe_c.numdoc','tdides','cpe_c.ccandi','cpe_c.ccanom','monnom','cpe_c.ccaitv','cpe_c.IdEmpresa','cpe_c.tdocod','cpe_c.IdCpe_cabecera','cpe_c.ccanot','cpe_c.ccabaj','cpe_c.codhash','cpe_c.estado','cpe_c.enviado','totalcontado','totalcredito')
        ->leftjoin('tipo_documento as tip_d','cpe_c.tdocod','=','tip_d.tdocod')
        ->leftjoin('tipo_documento_identidad as tdi','cpe_c.tdicod','=','tdi.tdicod')
        ->leftjoin('moneda as mon','cpe_c.moncod','=','mon.moncod')
        ->whereNull('cpe_c.ccabaj')
        ->where('cpe_c.id_empresa_negocio',$sucursal->id_empresa_negocio)
        ->where(function ($query) {
          $query->Where('cpe_c.tdocod','03')
                ->orWhere('cpe_c.tdocod','01')
                ->orWhere('cpe_c.tdocod','13');
              
          })
        ->where('estadopago','CREDITO')
        ->where('ccafem',$fecha)
        ->get();
        

        $ventascredito = $registroventascredito->sum('ccaitv');
        
        $cobranzas = DB::TABLE('cuentas_cobrar_detalle')->select('fec_dep','cuentas_cobrar_detalle.abono','comentario','numero_recibo',DB::raw("(SELECT CONCAT(name,apeusu) FROM users WHERE IdUsuario= cuentas_cobrar_detalle.vendedor) as vendedor"),DB::raw("(SELECT clinom FROM cliente WHERE clicod = cuentas_cobrar.clicod) as cliente"),DB::raw("(SELECT clinum FROM cliente WHERE clicod = cuentas_cobrar.clicod) as ruc"),'serdoc','numdoc','cuentas_cobrar.clicod','total_detalle','saldo_detalle','fec_reg','tdocod','ccanom','monto_efectivo','monto_deposito')
            ->join('cuentas_cobrar','cuentas_cobrar.cue_cob_id','cuentas_cobrar_detalle.cue_cob_id')
            ->join('cpe_cabecera','cpe_cabecera.IdCpe_cabecera','cuentas_cobrar.IdCpe_cabecera')
           // ->whereNull('cpe_cabecera.ccabaj')
            ->where('cuentas_cobrar.id_empresa_negocio',$sucursal->id_empresa_negocio)
          
            ->where('fec_reg','=',$fecha)
            ->orderby('cliente','asc')
            ->get();

        
          $totalcobranzas = $cobranzas->sum('abono');
           $totalcobranzasdepositos = $cobranzas->sum('monto_deposito');

          $pagos = DB::TABLE('cuentas_pagar_detalle')->select('fec_reg','fec_dep','cuentas_pagar_detalle.abono',DB::raw("(SELECT prov_raz FROM proveedor WHERE prov_id = compras_cabecera.prov_id) as proveedor"),DB::raw("(SELECT prov_ruc FROM proveedor WHERE prov_id = compras_cabecera.prov_id) as ruc"),'com_doc_ser','com_doc_num','compras_cabecera.prov_id','compras_cabecera.tdocod')
            ->join('cuentas_pagar','cuentas_pagar.cue_pag_id','cuentas_pagar_detalle.cue_pag_id')
            ->join('compras_cabecera','compras_cabecera.com_cab_id','cuentas_pagar.com_cab_id')
            ->where('est_compra','Registrado')
            ->where('cuentas_pagar.id_empresa_negocio',$sucursal->id_empresa_negocio)
            ->where('fec_reg','=',$fecha)
            ->orderby('proveedor','asc')
            ->get();

      $totalpagos = $pagos->sum('abono');
   

        $totalgasto = DB::TABLE('gastos_cabecera')
        ->leftjoin('moneda','gastos_cabecera.mon_id','moneda.moncod')
        ->leftjoin('tipo_documento','gastos_cabecera.tdocod','tipo_documento.tdocod')
        ->join('usuario_gastos','usuario_gastos.gast_cab_id','gastos_cabecera.gast_cab_id')
        ->where('tipo_movimiento','GASTO')
        ->where('est_gasto','Registrado')
        ->where('gast_fec',$fecha)
        ->orderby('gastos_cabecera.gast_Cab_id','desc')
        ->sum('total_gast');

         $totalingreso = DB::TABLE('gastos_cabecera')
        ->leftjoin('moneda','gastos_cabecera.mon_id','moneda.moncod')
        ->leftjoin('tipo_documento','gastos_cabecera.tdocod','tipo_documento.tdocod')
        ->join('usuario_gastos','usuario_gastos.gast_cab_id','gastos_cabecera.gast_cab_id')
        ->where('tipo_movimiento','INGRESO')
        ->where('est_gasto','Registrado')
        ->where('gast_fec',$fecha)
        ->orderby('gastos_cabecera.gast_Cab_id','desc')
        ->sum('total_gast');


        $grup_gas = DB::TABLE('gastos_cabecera')->select('tip_gas_nom',DB::RAW('sum(gastos_detalle.total) as total'))
         ->leftjoin('gastos_detalle','gastos_cabecera.gast_cab_id','gastos_detalle.gast_cab_id')
         ->join('usuario_gastos','usuario_gastos.gast_cab_id','gastos_cabecera.gast_cab_id')
        ->leftjoin('tipo_gastos','tipo_gastos.tip_gas_id','gastos_detalle.tip_gas_id')
        ->where('gastos_cabecera.id_empresa_negocio',Auth::user()->id_empresa_negocio)
        ->where('tipo_movimiento','GASTO')
        ->where('est_gasto','Registrado')
        ->where('gast_fec',$fecha)
        ->orderby('gastos_cabecera.gast_Cab_id','desc')
        ->groupby('tipo_gastos.tip_gas_id')
        ->get();

    
        $grup_ing = DB::TABLE('gastos_cabecera')->select('tip_gas_nom',DB::RAW('sum(gastos_detalle.total) as total'))
         ->leftjoin('gastos_detalle','gastos_cabecera.gast_cab_id','gastos_detalle.gast_cab_id')
         ->join('usuario_gastos','usuario_gastos.gast_cab_id','gastos_cabecera.gast_cab_id')
        ->leftjoin('tipo_gastos','tipo_gastos.tip_gas_id','gastos_detalle.tip_gas_id')
        ->where('gastos_cabecera.id_empresa_negocio',Auth::user()->id_empresa_negocio)
        ->where('tipo_movimiento','GASTO')
        ->where('est_gasto','Registrado')
        ->where('gast_fec',$fecha)
        ->orderby('gastos_cabecera.gast_Cab_id','desc')
        ->groupby('tipo_gastos.tip_gas_id')
        ->get();

      
        $compras = DB::TABLE('compras_cabecera')
        ->leftjoin('proveedor','compras_cabecera.prov_id','proveedor.prov_id')
        ->leftjoin('moneda','compras_cabecera.mon_id','moneda.moncod')
        ->leftjoin('tipo_documento','compras_cabecera.tdocod','tipo_documento.tdocod')
        ->where('compras_cabecera.id_empresa_negocio',Auth::user()->id_empresa_negocio)
         ->where('com_fec',$fecha)
        ->where('est_compra','Registrado')
        ->where('compras_cabecera.tdocod','!=','80')
        ->sum('total_com');


        $gastos = DB::TABLE('gastos_cabecera')
        ->join('gastos_detalle','gastos_cabecera.gast_cab_id','gastos_detalle.gast_cab_id')
        ->leftjoin('moneda','gastos_cabecera.mon_id','moneda.moncod')
        ->leftjoin('tipo_gastos','tipo_gastos.tip_gas_id','gastos_detalle.tip_gas_id')
        ->leftjoin('tipo_documento','gastos_cabecera.tdocod','tipo_documento.tdocod')
       
        ->where('gastos_cabecera.id_empresa_negocio',Auth::user()->id_empresa_negocio)
        ->where('tipo_movimiento','GASTO')
        ->where('est_gasto','Registrado')
        ->where('gast_fec',$fecha)
        ->orderby('gastos_cabecera.gast_cab_id','desc')
        ->get();

          $ingresos = DB::TABLE('gastos_cabecera')
        ->leftjoin('gastos_detalle','gastos_cabecera.gast_cab_id','gastos_detalle.gast_cab_id')
        ->leftjoin('moneda','gastos_cabecera.mon_id','moneda.moncod')
        ->leftjoin('tipo_gastos','tipo_gastos.tip_gas_id','gastos_detalle.tip_gas_id')
        ->leftjoin('tipo_documento','gastos_cabecera.tdocod','tipo_documento.tdocod')
        ->where('gastos_cabecera.id_empresa_negocio',Auth::user()->id_empresa_negocio)
        ->where('tipo_movimiento','INGRESO')
        ->where('est_gasto','Registrado')
        ->where('gast_fec',$fecha)
        ->orderby('gastos_cabecera.gast_cab_id','desc')
        ->get();


        $totalgast = $gastos->sum('total');
        $totalingreso = $ingresos->sum('total');

        $saldo = 0;

        $fecha1 = Carbon::parse($fecha)->format('d-m-Y');

        $bus_saldo = DB::tABLE('saldos_arqueo')
        ->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
        ->where('fecha','<',$fecha)
        ->orderby('fecha','desc')
        ->first();

        if(empty($bus_saldo)){
          $saldo = 0;
        }else{
          $saldo = $bus_saldo->saldo;
        }

        $saldo_nuevo = ($saldo+$ventascontado+$totalingreso+$totalcobranzas)-($totalgast+$totalpagos);


        $bus_saldo_act = DB::tABLE('saldos_arqueo')
        ->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
        ->where('fecha',$fecha)
        ->first();

        if(empty($bus_saldo_act)){
          DB::tABLE('saldos_arqueo')
          ->insert(['fecha'=>$fecha,'saldo'=>$saldo_nuevo,'id_empresa_negocio'=>Auth::user()->id_empresa_negocio]);
        }else{
          DB::tABLE('saldos_arqueo')
          ->where('sal_arq_id',$bus_saldo_act->sal_arq_id)
          ->update(['saldo'=>$saldo_nuevo]);
        }
       


        $rutapdf = public_path().'/pdfreportes/';

        $nompdffile = 'Arqueo_Resumen_Diario_'.$fecha.'.pdf';

       if(file_exists($rutapdf.$nompdffile)){

            unlink($rutapdf.$nompdffile);
        }


          Excel::create('ARQUEO_DIARIO', function($excel) use ($totalcobranzasdepositos,$compras,$totalgasto,$totalingreso,$registroventascontado,$registroventascredito,$ventascredito,$ventascontado,$cobranzas,$totalcobranzas,$usuario,$sucursal,$empresa,$grup_gas,$grup_ing,$gastos,$totalgast,$ingresos,$fecha,$saldo_nuevo,$saldo,$pagos,$totalpagos,$registroventascontadof,$registroventascontadob,$ventasfactura,$ventasboleta,$registroventascontadofc,$registroventascontadobc,$ventasfacturac,$ventasboletac,$registronotascontado,$registronotascredito,$notascontado,$notascredito,$registroconsumo,$consumo) {

                        $excel->sheet('kardex_fisico', function($sheet) use ($totalcobranzasdepositos,$compras,$totalgasto,$totalingreso,$registroventascontado,$registroventascredito,$ventascredito,$ventascontado,$cobranzas,$totalcobranzas,$usuario,$sucursal,$empresa,$grup_gas,$grup_ing,$gastos,$totalgast,$ingresos,$fecha,$saldo_nuevo,$saldo,$pagos,$totalpagos,$registroventascontadof,$registroventascontadob,$ventasfactura,$ventasboleta,$registroventascontadofc,$registroventascontadobc,$ventasfacturac,$ventasboletac,$registronotascontado,$registronotascredito,$notascontado,$notascredito,$registroconsumo,$consumo) {

                       
                            
                                  $sheet->loadView('formatos_reportes_excel.arqueo_diario_resumen',compact('totalcobranzasdepositos','compras','totalgasto','totalingreso','registroventascontado','registroventascredito','ventascredito','ventascontado','cobranzas','totalcobranzas','usuario','sucursal','empresa','grup_gas','totalgasto','grup_ing','gastos','totalgast','totalingreso','ingresos','fecha','saldo_nuevo','saldo','pagos','totalpagos','registroventascontadof','registroventascontadob','ventasfactura','ventasboleta','registroventascontadofc','registroventascontadobc','ventasfacturac','ventasboletac','registronotascontado','registronotascredito','notascontado','notascredito','registroconsumo','consumo'));
                          
                                

                        });

                    })->export('xlsx'); 



     

        


       if (file_exists($rutapdf.$nompdffile))
      {
        $headers = array(
              'Content-Type: application/pdf',
            );

        return response()->download($rutapdf.$nompdffile);
      }

      



   

    }






   public function generararqueodiarioresumen(Request $request){
        
         $medios = DB::tABLE('cuentas_cobrar_medios')->get();

     foreach($medios as $med){

        if($med->med_pag_id=='4'){
                 DB::tABLE('cuentas_cobrar_detalle')
                 ->where('numero_recibo',$med->numero_recibo)
                 ->where('cue_cob_det_id',$med->cue_cob_det_id)
                 ->update(['monto_efectivo'=>$med->monto]); 
        }

         if($med->med_pag_id=='5'){
                DB::tABLE('cuentas_cobrar_detalle')
                ->where('numero_recibo',$med->numero_recibo)
                ->where('cue_cob_det_id',$med->cue_cob_det_id)
                ->update(['monto_deposito'=>$med->monto]);
        }


        
       
      }


        $cliente = $request->get('cliente');
        $documento = $request->get('documento');
 
        $fecha = $request->get('fecin');

        $sucursal = DB::tABLE('empresa_negocios')
        ->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
        ->first();

        $empresa = DB::tABLE('empresa')
        ->where('IdEmpresa',$sucursal->IdEmpresa)
        ->first();


        $usuario = DB::tABLE('users')->where('IdUsuario',Auth::user()->IdUsuario)->first();
        
        $registroventascontado = DB::table('cpe_cabecera as cpe_c')->select('fecha_hora','ccacodsun','ccafem','tdodes','cpe_c.serdoc','cpe_c.numdoc','tdides','cpe_c.ccandi','cpe_c.ccanom','monnom','cpe_c.ccaitv','cpe_c.IdEmpresa','cpe_c.tdocod','cpe_c.IdCpe_cabecera','cpe_c.ccanot','cpe_c.ccabaj','cpe_c.codhash','cpe_c.estado','cpe_c.enviado','totalcontado','totalcredito')
        ->join('tipo_documento as tip_d','cpe_c.tdocod','=','tip_d.tdocod')
        ->join('tipo_documento_identidad as tdi','cpe_c.tdicod','=','tdi.tdicod')
        ->join('moneda as mon','cpe_c.moncod','=','mon.moncod')
        ->whereNull('cpe_c.ccabaj')
        ->where('cpe_c.id_empresa_negocio',$sucursal->id_empresa_negocio)
        ->where(function ($query) {
          $query->Where('cpe_c.tdocod','03')
                ->orWhere('cpe_c.tdocod','01')
                ->orWhere('cpe_c.tdocod','13');
              
          })
        ->where('estadopago','CONTADO')
        ->where('ccafem',$fecha)
        ->get();


        $ventascontado = $registroventascontado->sum('ccaitv');

        $registronotascontado = DB::table('cpe_cabecera as cpe_c')->select('fecha_hora','ccacodsun','ccafem','tdodes','cpe_c.serdoc','cpe_c.numdoc','tdides','cpe_c.ccandi','cpe_c.ccanom','monnom','cpe_c.ccaitv','cpe_c.IdEmpresa','cpe_c.tdocod','cpe_c.IdCpe_cabecera','cpe_c.ccanot','cpe_c.ccabaj','cpe_c.codhash','cpe_c.estado','cpe_c.enviado','totalcontado','totalcredito')
        ->join('tipo_documento as tip_d','cpe_c.tdocod','=','tip_d.tdocod')
        ->join('tipo_documento_identidad as tdi','cpe_c.tdicod','=','tdi.tdicod')
        ->join('moneda as mon','cpe_c.moncod','=','mon.moncod')
        ->whereNull('cpe_c.ccabaj')
        ->where('cpe_c.id_empresa_negocio',$sucursal->id_empresa_negocio)
        ->where('cpe_c.tdocod','13')
        ->where('estadopago','CONTADO')
        ->where('ccafem',$fecha)
        ->get();

        

        $notascontado = $registronotascontado->sum('ccaitv');

         $registroconsumo = DB::table('cpe_cabecera as cpe_c')->select('fecha_hora','ccacodsun','ccafem','tdodes','cpe_c.serdoc','cpe_c.numdoc','tdides','cpe_c.ccandi','cpe_c.ccanom','monnom','cpe_c.ccaitv','cpe_c.IdEmpresa','cpe_c.tdocod','cpe_c.IdCpe_cabecera','cpe_c.ccanot','cpe_c.ccabaj','cpe_c.codhash','cpe_c.estado','cpe_c.enviado','totalcontado','totalcredito')
        ->join('tipo_documento as tip_d','cpe_c.tdocod','=','tip_d.tdocod')
        ->join('tipo_documento_identidad as tdi','cpe_c.tdicod','=','tdi.tdicod')
        ->join('moneda as mon','cpe_c.moncod','=','mon.moncod')
        ->whereNull('cpe_c.ccabaj')
        ->where('cpe_c.id_empresa_negocio',$sucursal->id_empresa_negocio)
        ->where('cpe_c.tdocod','99')
        ->where('estadopago','CONTADO')
        ->where('ccafem',$fecha)
        ->get();

        

        $consumo = $registroconsumo->sum('ccaitv');


        $registronotascredito = DB::table('cpe_cabecera as cpe_c')->select('fecha_hora','ccacodsun','ccafem','tdodes','cpe_c.serdoc','cpe_c.numdoc','tdides','cpe_c.ccandi','cpe_c.ccanom','monnom','cpe_c.ccaitv','cpe_c.IdEmpresa','cpe_c.tdocod','cpe_c.IdCpe_cabecera','cpe_c.ccanot','cpe_c.ccabaj','cpe_c.codhash','cpe_c.estado','cpe_c.enviado','totalcontado','totalcredito')
        ->join('tipo_documento as tip_d','cpe_c.tdocod','=','tip_d.tdocod')
        ->join('tipo_documento_identidad as tdi','cpe_c.tdicod','=','tdi.tdicod')
        ->join('moneda as mon','cpe_c.moncod','=','mon.moncod')
        ->whereNull('cpe_c.ccabaj')
        ->where('cpe_c.id_empresa_negocio',$sucursal->id_empresa_negocio)
        ->Where('cpe_c.tdocod','13')
        ->where('estadopago','CREDITO')
        ->where('ccafem',$fecha)
        ->get();


        $notascredito = $registronotascredito->sum('ccaitv');



        $registroventascontadof = DB::table('cpe_cabecera as cpe_c')->select('fecha_hora','ccacodsun','ccafem','tdodes','cpe_c.serdoc','cpe_c.numdoc','tdides','cpe_c.ccandi','cpe_c.ccanom','monnom','cpe_c.ccaitv','cpe_c.IdEmpresa','cpe_c.tdocod','cpe_c.IdCpe_cabecera','cpe_c.ccanot','cpe_c.ccabaj','cpe_c.codhash','cpe_c.estado','cpe_c.enviado','totalcontado','totalcredito')
        ->join('tipo_documento as tip_d','cpe_c.tdocod','=','tip_d.tdocod')
        ->join('tipo_documento_identidad as tdi','cpe_c.tdicod','=','tdi.tdicod')
        ->join('moneda as mon','cpe_c.moncod','=','mon.moncod')
        ->whereNull('cpe_c.ccabaj')
        ->where('cpe_c.id_empresa_negocio',$sucursal->id_empresa_negocio)
        ->where(function ($query) {
          $query->Where('cpe_c.tdocod','01');   
          })
        ->where('estadopago','CONTADO')
        ->where('ccafem',$fecha)
        ->get();


        $ventasfactura = $registroventascontadof->sum('ccaitv');

        $registroventascontadob = DB::table('cpe_cabecera as cpe_c')->select('fecha_hora','ccacodsun','ccafem','tdodes','cpe_c.serdoc','cpe_c.numdoc','tdides','cpe_c.ccandi','cpe_c.ccanom','monnom','cpe_c.ccaitv','cpe_c.IdEmpresa','cpe_c.tdocod','cpe_c.IdCpe_cabecera','cpe_c.ccanot','cpe_c.ccabaj','cpe_c.codhash','cpe_c.estado','cpe_c.enviado','totalcontado','totalcredito')
        ->join('tipo_documento as tip_d','cpe_c.tdocod','=','tip_d.tdocod')
        ->join('tipo_documento_identidad as tdi','cpe_c.tdicod','=','tdi.tdicod')
        ->join('moneda as mon','cpe_c.moncod','=','mon.moncod')
        ->whereNull('cpe_c.ccabaj')
        ->where('cpe_c.id_empresa_negocio',$sucursal->id_empresa_negocio)
        ->where(function ($query) {
          $query->Where('cpe_c.tdocod','03');              
          })
        ->where('estadopago','CONTADO')
        ->where('ccafem',$fecha)
        ->get();


        $ventasboleta = $registroventascontadob->sum('ccaitv');

        $registroventascontadofc = DB::table('cpe_cabecera as cpe_c')->select('fecha_hora','ccacodsun','ccafem','tdodes','cpe_c.serdoc','cpe_c.numdoc','tdides','cpe_c.ccandi','cpe_c.ccanom','monnom','cpe_c.ccaitv','cpe_c.IdEmpresa','cpe_c.tdocod','cpe_c.IdCpe_cabecera','cpe_c.ccanot','cpe_c.ccabaj','cpe_c.codhash','cpe_c.estado','cpe_c.enviado','totalcontado','totalcredito')
        ->join('tipo_documento as tip_d','cpe_c.tdocod','=','tip_d.tdocod')
        ->join('tipo_documento_identidad as tdi','cpe_c.tdicod','=','tdi.tdicod')
        ->join('moneda as mon','cpe_c.moncod','=','mon.moncod')
        ->whereNull('cpe_c.ccabaj')
        ->where('cpe_c.id_empresa_negocio',$sucursal->id_empresa_negocio)
        ->where(function ($query) {
          $query->Where('cpe_c.tdocod','01');   
          })
        ->where('estadopago','CREDITO')
        ->where('ccafem',$fecha)
        ->get();


        $ventasfacturac = $registroventascontadofc->sum('ccaitv');

        $registroventascontadobc = DB::table('cpe_cabecera as cpe_c')->select('fecha_hora','ccacodsun','ccafem','tdodes','cpe_c.serdoc','cpe_c.numdoc','tdides','cpe_c.ccandi','cpe_c.ccanom','monnom','cpe_c.ccaitv','cpe_c.IdEmpresa','cpe_c.tdocod','cpe_c.IdCpe_cabecera','cpe_c.ccanot','cpe_c.ccabaj','cpe_c.codhash','cpe_c.estado','cpe_c.enviado','totalcontado','totalcredito')
        ->join('tipo_documento as tip_d','cpe_c.tdocod','=','tip_d.tdocod')
        ->join('tipo_documento_identidad as tdi','cpe_c.tdicod','=','tdi.tdicod')
        ->join('moneda as mon','cpe_c.moncod','=','mon.moncod')
        ->whereNull('cpe_c.ccabaj')
        ->where('cpe_c.id_empresa_negocio',$sucursal->id_empresa_negocio)
        ->where(function ($query) {
          $query->Where('cpe_c.tdocod','03');              
          })
        ->where('estadopago','CREDITO')
        ->where('ccafem',$fecha)
        ->get();


        $ventasboletac = $registroventascontadobc->sum('ccaitv');
        

        $registroventascredito = DB::table('cpe_cabecera as cpe_c')->select('fecha_hora','ccacodsun','ccafem','tdodes','cpe_c.serdoc','cpe_c.numdoc','tdides','cpe_c.ccandi','cpe_c.ccanom','monnom','cpe_c.ccaitv','cpe_c.IdEmpresa','cpe_c.tdocod','cpe_c.IdCpe_cabecera','cpe_c.ccanot','cpe_c.ccabaj','cpe_c.codhash','cpe_c.estado','cpe_c.enviado','totalcontado','totalcredito')
        ->leftjoin('tipo_documento as tip_d','cpe_c.tdocod','=','tip_d.tdocod')
        ->leftjoin('tipo_documento_identidad as tdi','cpe_c.tdicod','=','tdi.tdicod')
        ->leftjoin('moneda as mon','cpe_c.moncod','=','mon.moncod')
        ->whereNull('cpe_c.ccabaj')
        ->where('cpe_c.id_empresa_negocio',$sucursal->id_empresa_negocio)
        ->where(function ($query) {
          $query->Where('cpe_c.tdocod','03')
                ->orWhere('cpe_c.tdocod','01')
                ->orWhere('cpe_c.tdocod','13');
              
          })
        ->where('estadopago','CREDITO')
        ->where('ccafem',$fecha)
        ->get();
        

        $ventascredito = $registroventascredito->sum('ccaitv');
        
        $cobranzas = DB::TABLE('cuentas_cobrar_detalle')->select('fec_dep','cuentas_cobrar_detalle.abono','comentario','numero_recibo',DB::raw("(SELECT CONCAT(name,apeusu) FROM users WHERE IdUsuario= cuentas_cobrar_detalle.vendedor) as vendedor"),DB::raw("(SELECT clinom FROM cliente WHERE clicod = cuentas_cobrar.clicod) as cliente"),DB::raw("(SELECT clinum FROM cliente WHERE clicod = cuentas_cobrar.clicod) as ruc"),'serdoc','numdoc','cuentas_cobrar.clicod','total_detalle','saldo_detalle','fec_reg','tdocod','ccanom','monto_efectivo','monto_deposito')
            ->join('cuentas_cobrar','cuentas_cobrar.cue_cob_id','cuentas_cobrar_detalle.cue_cob_id')
            ->join('cpe_cabecera','cpe_cabecera.IdCpe_cabecera','cuentas_cobrar.IdCpe_cabecera')
           // ->whereNull('cpe_cabecera.ccabaj')
            ->where('cuentas_cobrar.id_empresa_negocio',$sucursal->id_empresa_negocio)
          
            ->where('fec_reg','=',$fecha)
            ->orderby('cliente','asc')
            ->get();

        
          $totalcobranzas = $cobranzas->sum('abono');
           $totalcobranzasdepositos = $cobranzas->sum('monto_deposito');

          $pagos = DB::TABLE('cuentas_pagar_detalle')->select('fec_reg','fec_dep','cuentas_pagar_detalle.abono',DB::raw("(SELECT prov_raz FROM proveedor WHERE prov_id = compras_cabecera.prov_id) as proveedor"),DB::raw("(SELECT prov_ruc FROM proveedor WHERE prov_id = compras_cabecera.prov_id) as ruc"),'com_doc_ser','com_doc_num','compras_cabecera.prov_id','compras_cabecera.tdocod')
            ->join('cuentas_pagar','cuentas_pagar.cue_pag_id','cuentas_pagar_detalle.cue_pag_id')
            ->join('compras_cabecera','compras_cabecera.com_cab_id','cuentas_pagar.com_cab_id')
            ->where('est_compra','Registrado')
            ->where('cuentas_pagar.id_empresa_negocio',$sucursal->id_empresa_negocio)
            ->where('fec_reg','=',$fecha)
            ->orderby('proveedor','asc')
            ->get();

      $totalpagos = $pagos->sum('abono');
   

        $totalgasto = DB::TABLE('gastos_cabecera')
        ->leftjoin('moneda','gastos_cabecera.mon_id','moneda.moncod')
        ->leftjoin('tipo_documento','gastos_cabecera.tdocod','tipo_documento.tdocod')
        ->join('usuario_gastos','usuario_gastos.gast_cab_id','gastos_cabecera.gast_cab_id')
        ->where('tipo_movimiento','GASTO')
        ->where('est_gasto','Registrado')
        ->where('gast_fec',$fecha)
        ->orderby('gastos_cabecera.gast_Cab_id','desc')
        ->sum('total_gast');

         $totalingreso = DB::TABLE('gastos_cabecera')
        ->leftjoin('moneda','gastos_cabecera.mon_id','moneda.moncod')
        ->leftjoin('tipo_documento','gastos_cabecera.tdocod','tipo_documento.tdocod')
        ->join('usuario_gastos','usuario_gastos.gast_cab_id','gastos_cabecera.gast_cab_id')
        ->where('tipo_movimiento','INGRESO')
        ->where('est_gasto','Registrado')
        ->where('gast_fec',$fecha)
        ->orderby('gastos_cabecera.gast_Cab_id','desc')
        ->sum('total_gast');


        $grup_gas = DB::TABLE('gastos_cabecera')->select('tip_gas_nom',DB::RAW('sum(gastos_detalle.total) as total'))
         ->leftjoin('gastos_detalle','gastos_cabecera.gast_cab_id','gastos_detalle.gast_cab_id')
         ->join('usuario_gastos','usuario_gastos.gast_cab_id','gastos_cabecera.gast_cab_id')
        ->leftjoin('tipo_gastos','tipo_gastos.tip_gas_id','gastos_detalle.tip_gas_id')
        ->where('gastos_cabecera.id_empresa_negocio',Auth::user()->id_empresa_negocio)
        ->where('tipo_movimiento','GASTO')
        ->where('est_gasto','Registrado')
        ->where('gast_fec',$fecha)
        ->orderby('gastos_cabecera.gast_Cab_id','desc')
        ->groupby('tipo_gastos.tip_gas_id')
        ->get();

    
        $grup_ing = DB::TABLE('gastos_cabecera')->select('tip_gas_nom',DB::RAW('sum(gastos_detalle.total) as total'))
         ->leftjoin('gastos_detalle','gastos_cabecera.gast_cab_id','gastos_detalle.gast_cab_id')
         ->join('usuario_gastos','usuario_gastos.gast_cab_id','gastos_cabecera.gast_cab_id')
        ->leftjoin('tipo_gastos','tipo_gastos.tip_gas_id','gastos_detalle.tip_gas_id')
        ->where('gastos_cabecera.id_empresa_negocio',Auth::user()->id_empresa_negocio)
        ->where('tipo_movimiento','GASTO')
        ->where('est_gasto','Registrado')
        ->where('gast_fec',$fecha)
        ->orderby('gastos_cabecera.gast_Cab_id','desc')
        ->groupby('tipo_gastos.tip_gas_id')
        ->get();

      
        $compras = DB::TABLE('compras_cabecera')
        ->leftjoin('proveedor','compras_cabecera.prov_id','proveedor.prov_id')
        ->leftjoin('moneda','compras_cabecera.mon_id','moneda.moncod')
        ->leftjoin('tipo_documento','compras_cabecera.tdocod','tipo_documento.tdocod')
        ->where('compras_cabecera.id_empresa_negocio',Auth::user()->id_empresa_negocio)
         ->where('com_fec',$fecha)
        ->where('est_compra','Registrado')
        ->where('compras_cabecera.tdocod','!=','80')
        ->sum('total_com');


        $gastos = DB::TABLE('gastos_cabecera')
        ->join('gastos_detalle','gastos_cabecera.gast_cab_id','gastos_detalle.gast_cab_id')
        ->leftjoin('moneda','gastos_cabecera.mon_id','moneda.moncod')
        ->leftjoin('tipo_gastos','tipo_gastos.tip_gas_id','gastos_detalle.tip_gas_id')
        ->leftjoin('tipo_documento','gastos_cabecera.tdocod','tipo_documento.tdocod')
       
        ->where('gastos_cabecera.id_empresa_negocio',Auth::user()->id_empresa_negocio)
        ->where('tipo_movimiento','GASTO')
        ->where('est_gasto','Registrado')
        ->where('gast_fec',$fecha)
        ->orderby('gastos_cabecera.gast_cab_id','desc')
        ->get();

          $ingresos = DB::TABLE('gastos_cabecera')
        ->leftjoin('gastos_detalle','gastos_cabecera.gast_cab_id','gastos_detalle.gast_cab_id')
        ->leftjoin('moneda','gastos_cabecera.mon_id','moneda.moncod')
        ->leftjoin('tipo_gastos','tipo_gastos.tip_gas_id','gastos_detalle.tip_gas_id')
        ->leftjoin('tipo_documento','gastos_cabecera.tdocod','tipo_documento.tdocod')
        ->where('gastos_cabecera.id_empresa_negocio',Auth::user()->id_empresa_negocio)
        ->where('tipo_movimiento','INGRESO')
        ->where('est_gasto','Registrado')
        ->where('gast_fec',$fecha)
        ->orderby('gastos_cabecera.gast_cab_id','desc')
        ->get();


        $totalgast = $gastos->sum('total');
        $totalingreso = $ingresos->sum('total');

        $saldo = 0;

        $fecha1 = Carbon::parse($fecha)->format('d-m-Y');

        $bus_saldo = DB::tABLE('saldos_arqueo')
        ->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
        ->where('fecha','<',$fecha)
        ->orderby('fecha','desc')
        ->first();

        if(empty($bus_saldo)){
          $saldo = 0;
        }else{
          $saldo = $bus_saldo->saldo;
        }

        $saldo_nuevo = ($saldo+$ventascontado+$totalingreso+$totalcobranzas)-($totalgast+$totalpagos);


        $bus_saldo_act = DB::tABLE('saldos_arqueo')
        ->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
        ->where('fecha',$fecha)
        ->first();

        if(empty($bus_saldo_act)){
          DB::tABLE('saldos_arqueo')
          ->insert(['fecha'=>$fecha,'saldo'=>$saldo_nuevo,'id_empresa_negocio'=>Auth::user()->id_empresa_negocio]);
        }else{
          DB::tABLE('saldos_arqueo')
          ->where('sal_arq_id',$bus_saldo_act->sal_arq_id)
          ->update(['saldo'=>$saldo_nuevo]);
        }
       


        $rutapdf = public_path().'/pdfreportes/';

        $nompdffile = 'Arqueo_Resumen_Diario_'.$fecha.'.pdf';

       if(file_exists($rutapdf.$nompdffile)){

            unlink($rutapdf.$nompdffile);
        }

        $view = \View::make('formatos_reportes.reporte_pdf_arqueo_diario_resumen', compact('totalcobranzasdepositos','compras','totalgasto','totalingreso','registroventascontado','registroventascredito','ventascredito','ventascontado','cobranzas','totalcobranzas','usuario','sucursal','empresa','grup_gas','totalgasto','grup_ing','gastos','totalgast','totalingreso','ingresos','fecha','saldo_nuevo','saldo','pagos','totalpagos','registroventascontadof','registroventascontadob','ventasfactura','ventasboleta','registroventascontadofc','registroventascontadobc','ventasfacturac','ventasboletac','registronotascontado','registronotascredito','notascontado','notascredito','registroconsumo','consumo'));

                  
          $pdf = \App::make('dompdf.wrapper');
          $contenido = $view->render();
          $pdf->loadHTML($view)->setPaper('A4', 'lanscape')->save($rutapdf.$nompdffile);


      
        


       if (file_exists($rutapdf.$nompdffile))
      {
        $headers = array(
              'Content-Type: application/pdf',
            );

        return response()->download($rutapdf.$nompdffile);
      }

      



   

    }


    
}
