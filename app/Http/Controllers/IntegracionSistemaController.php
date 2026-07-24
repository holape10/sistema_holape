<?php

namespace MasterSoft\Http\Controllers;

use Illuminate\Http\Request;
use Greenter\Model\Client\Client;
use Greenter\Model\Company\Company;
use Greenter\Model\Company\Address;
use Greenter\Model\Sale\Invoice;
use Greenter\Model\Sale\SaleDetail;
use Greenter\XMLSecLibs\Sunat\SignedXML;
use Greenter\XML\Builder\InvoiceBuilder;
use Greenter\Model\Sale\Legend;
use Greenter\Ws\Services\SunatEndpoints;
use Greenter\See;
use Greenter\Model\Voided\Voided;
use Greenter\Model\Voided\VoidedDetail;
use Greenter\Model\Summary\Summary;
use Greenter\Model\Summary\SummaryDetail;
use Greenter\Model\Summary\SummaryPerception;
use Greenter\Model\Sale\Document;
use MasterSoft\resumenes;
use DOMDocument;
use MasterSoft\Mail\FacturacionEmail;
use Illuminate\Support\Facades\Mail;
use MasterSoft\Http\Requests;
use MasterSoft\cuentascobrar;
use MasterSoft\movimientoscaja;
use MasterSoft\Empresa;
use MasterSoft\movimientosbancarios;
use MasterSoft\cuentascobrardetalle;
use MasterSoft\pedidos;
use MasterSoft\pedidos_detalle;
use MasterSoft\TipoIGV;
use MasterSoft\usuario_pedidos;
use MasterSoft\usuario_facturacion;
use MasterSoft\Cliente;
use MasterSoft\caja;
use MasterSoft\cpe_cabecera;
use MasterSoft\documento_relacionado;
use MasterSoft\unidad_medida;
use MasterSoft\cpe_detalle;
use MasterSoft\cpe_baja;
use MasterSoft\mesas;
use MasterSoft\movimientos;
use MasterSoft\movimientosinsumos;
use MasterSoft\EmpresaNegocios;
use MasterSoft\insumos;
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
use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\FilePrintConnector;
use Mike42\Escpos\EscposImage;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
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
use XBase\Record\FoxproRecord;
use XBase\TableEditor;
use XBase\TableReader;
use Carbon\Carbon;

class IntegracionSistemaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function registrar_ventas_dbf(Request $request,$fecin,$fecfin){

     /* echo phpinfo();

      exit();*/
	  
		$validar = DB::tABLE('cpe_cabecera')
		->where(function ($query) {
          $query->where('cpe_cabecera.tdocod','01')
              ->orWhere('cpe_cabecera.tdocod','03')
              ->orWhere('cpe_cabecera.tdocod','07')
              ->orWhere('cpe_cabecera.tdocod','08')
             ;
          })
		->where('migrar','1')
        ->where('ccafem','>=',$fecin)
        ->where('ccafem','<=',$fecfin)
		->get();
		
		$cantidad = DB::tABLE('cpe_cabecera')
		->where(function ($query) {
          $query->where('cpe_cabecera.tdocod','01')
              ->orWhere('cpe_cabecera.tdocod','03')
             ;
          })
        ->where('ccafem','>=',$fecin)
        ->where('ccafem','<=',$fecfin)
		->get();
		
		
		
		$dateini = strtotime($fecin);
		$datefin = strtotime($fecfin);
		
    $dini = date('dmY',$dateini);
		$dfin =  date('dmY',$datefin);
		
		
	//	dd($dini);
		
	/*if(count($validar)=='0' && count($cantidad)>'0'){*/
		
    $codigos_movimientos = DB::tABLE('cpe_cabecera')
     ->where(function ($query) {
          $query->where('cpe_cabecera.tdocod','01')
              ->orWhere('cpe_cabecera.tdocod','03')
               ->orWhere('cpe_cabecera.tdocod','07')
              ->orWhere('cpe_cabecera.tdocod','08')
             ;
          })
        ->whereNull('cod_mov')
        ->where('ccafem','>=',$fecin)
        ->where('ccafem','<=',$fecfin)
      
        ->get();


    foreach ($codigos_movimientos as $codigos) {
         
         if(empty($codigos->cod_mov) || is_null($codigos->cod_mov)){

                  self::generar_codigo_movimiento($codigos->IdCpe_cabecera);
              }

    }

		$cabecera = DB::tABLE('cpe_cabecera')
        ->join('tipo_documento','tipo_documento.tdocod','cpe_cabecera.tdocod')
        ->join('moneda','moneda.moncod','cpe_cabecera.moncod')
        ->join('tipo_documento_identidad','tipo_documento_identidad.tdicod','cpe_cabecera.tdicod')
        ->leftjoin('credito_dias','credito_dias.cre_dia_id','cpe_cabecera.cre_dia_id')
		 ->where(function ($query) {
          $query->where('cpe_cabecera.tdocod','01')
              ->orWhere('cpe_cabecera.tdocod','03')
               ->orWhere('cpe_cabecera.tdocod','07')
              ->orWhere('cpe_cabecera.tdocod','08')
             ;
          })
		//->where('migrar','0')
        ->where('ccafem','>=',$fecin)
        ->where('ccafem','<=',$fecfin)
      
        ->get();
	
		   
		  
     

        $archivo_cab_ant = public_path()."/corporacion/cabecera.DBF";
		    $archivo_cab = public_path()."/corporacion/cabecera_".$dini."_".$dfin.".DBF";
		
        $archivo_det_ant = public_path()."/corporacion/detalle.DBF";
		    $archivo_det = public_path()."/corporacion/detalle_".$dini."_".$dfin.".DBF";
		
		copy($archivo_cab_ant,$archivo_cab);
		copy($archivo_det_ant,$archivo_det);
		
       $db_cab = dbase_open($archivo_cab, 2);

       $db_det = dbase_open($archivo_det, 2);
       
		if($db_cab){
			$registros = dbase_numrecords($db_cab);
			
			for($i=1;$i<=$registros;$i++){
				
				dbase_delete_record($db_cab,$i);
				
			}
			
			dbase_pack($db_cab);
		}
		
		if($db_det){
			$registrosdet = dbase_numrecords($db_det);
			
			for($j=1;$j<=$registrosdet;$j++){
				
				dbase_delete_record($db_det,$j);
				
			}
			dbase_pack($db_det);
		}
		
	
	$tip_mot = "";
  $tip_not ="";
  $ser_doc_c = "";
  $num_doc_c = "";
  $tip_doc_c = "";
  $fec_doc_c = "";
  

        if($db_cab){

        foreach ($cabecera as $cab){

  
              
              $almacen = DB::tABLE('almacenes')->where('predeterminado','1')->where('id_empresa_negocio',$cab->id_empresa_negocio)->first();
			
              if($cab->estadopago=='CONTADO'){ 
                       $montocredito =  $cab->ccaitv;
              }else{
                       $montocredito= '';
              } 

        			$codigo = $almacen->codigo;
        				
        			$dateEmi = date_create($cab->ccafem);
        			$dateVen = date_create($cab->ccafve);
        			
        			$fechaEmi = date_format($dateEmi,"Ymd");
        			$fechaVen = date_format($dateEmi,"Ymd");
        			
          				 
              if(is_null($cab->ccabaj)){
          				$anulado ='0';
          		}else{
          				$anulado ='1';
          		}
          		
              if($cab->tdocod=='07' || $cab->tdocod=='08' ){

              $referencia = DB::tABLE('cpe_cabecera')
              ->where('IdCpe_cabecera',$cab->IdCpe_cabecera_ref)
              ->first();

              if(!empty($referencia)){

                $ser_doc_c = $referencia->serdoc;
                $num_doc_c = $referencia->numdoc;
                $tip_doc_c = $referencia->tdocod;
                
                $dateEmiRef = date_create($referencia->ccafem);
                $fec_doc_c = date_format($dateEmiRef,"Ymd");
              }

            }

               if($cab->tdocod=='07'){
                    $bus_mot = DB::tABLE('tipo_nota_credito')->where('nccod',$cab->tipnot)->first();
                    $tip_mot = $bus_mot->ncdes;

                }elseif($cab->tdocod=='08'){
                    $bus_mot = DB::tABLE('tipo_nota_debito')->where('ndcod',$cab->tipnot)->first();
                    $tip_mot = $bus_mot->nddes;
                }
            


          dbase_add_record($db_cab, 
            array(
			        '',//num_mes
              $cab->cod_mov, 
              '',//id
              $cab->tdocod,
              $cab->serdoc, 
              str_pad($cab->numdoc,7,"0", STR_PAD_LEFT), 
			        '',
			        $cab->ccandi,
			        $cab->ccanom,
              $cab->topcod,
			        $cab->des_doc, 	
			        $fechaEmi,   
              $fechaEmi, 			  
              $codigo,//alm    
              '',//fpago   
              $cab->cre_dia_fac,    
              $fechaVen,      
              $montocredito,  
              '',//categ   
              '',//vendedor    
              $cab->ccaitv,  
              '',//pordes  
              '',//descto  
              '',//tneto   
              '',//igv 
              '',//flete  
              '',//otros   
			    $cab->ccaitv, 
              $cab->tipcambio,  
              '',//guia    
              '',//ctacte  
              '',//flag_igv    
              '',//flag    
              $anulado,
              '',//usuario 
              '',//fcontrol
              '',//ws  
              '',//lipre   
              $cab->simbolo,  
              '',//proforma  
			  '',//orden
              '',//flagcosto 
              '',//flagstock  
              '',//letra 
              '',//numero 
              '',//importe 
              '',//embarcado   
              '',//de 
              '',//destino 
              '',//notifir   
              '',//consignado  
              '',//fcon    
              '',//cont    
              '',//caja    
              '',//turno   
              '',//anticipo1
              '',//anticipo2   
              '',//anticipo3   
              '',//totalanti   
              '',//ctacargo--    
              '',//ctaabono--  
              $fec_doc_c,//fecha_c 
              $tip_doc_c,//tip_doc_c   
              $ser_doc_c,//ser_doc_c   
              $num_doc_c,//num_doc_c   
              $cab->tdocod, 
              $cab->tdicod,  
              '',//exporta
              $cab->ccatvg, 
              $cab->ccatexo,   
              $cab->ccatvi,    
              '',//isc 
              '',//merca   
              '',//servi   
              '',//prote   
              '',//alq
              '',//otr
              '',//fcoddoc 
              '',//fserie  
              '',//fnumero 
			         '',//ctamerca
			        '',//ctaservi    
              '',//ctaprote    
              '',//cataalq  
              '',//ctaotr  			  
              '',//gfcon   
              $cab->tipnot,//gnumero 
              $tip_mot,//gtranspo    
              '',//gdirec  
              '',//gplaca  
              '',//gchofer 
              '',//gsalida 
              '',//gdestino    
              '',//gdirec1 
              '',//gdirec2 
              '',//gbrevete    
              '',//distri 
              '',//gprovin 
              '',//gdepar  
              '',//gruc    
              '',//gcodmoti    
              '',//gmotivo 
              $cab->icbper
            )
        );   

          DB::tABLE('cpe_cabecera')->where('IdCpe_cabecera',$cab->IdCpe_cabecera)->update(['migrar'=>'1']);

        }
    }


    // DETALLE
    dbase_close($db_cab);
    


    if($db_det){
        foreach ($cabecera as $cab){
            
            $detalles = DB::tABLE('cpe_detalle')
            ->select('IdCpe_detalle','cpe_detalle.IdCpe_cabecera','cod_mov','cpe_cabecera.id_empresa_negocio','serdoc','numdoc','cpe_detalle.umecod','cpe_detalle.procod','cdedes','cdecan','cdepuni','cdepve','cpe_detalle.costo','desc_mon')
            ->join('cpe_cabecera','cpe_cabecera.IdCpe_cabecera','cpe_detalle.IdCpe_cabecera')
            ->leftjoin('productos','productos.IdProducto','cpe_detalle.IdProducto')
            ->where('cpe_detalle.IdCpe_cabecera',$cab->IdCpe_cabecera)
            ->where('ccafem','>=',$fecin)
            ->where('ccafem','<=',$fecfin)
           //->where('migrar_det','0')
            ->get();

            foreach ($detalles as $det) {
                
				$movimiento = DB::tABLE('cpe_cabecera')->where('IdCpe_cabecera',$det->IdCpe_cabecera)->first();
				
                dbase_add_record($db_det, 
                    array(
                        $movimiento->cod_mov, 
                        $det->id_empresa_negocio, 
                        $movimiento->cod_mov, 
                        $det->serdoc.'-'.$det->numdoc,   
                        '',//ubica   
                        $det->procod,//cunico 
                        $det->cdedes, 
                        $det->umecod,  
                        '',//dunida 
                        '',//factor  
                        $det->cdecan,   
                        $det->cdepuni-$det->desc_mon,  
                        '',//pdscto 
                        '',//vdscto  
                        '',//descto    
                        '',//descto_v    
                        '',//vflete  
                        '',//vigv    
                        '',//votros  
                        '',//vcosto  
                        $det->cdepve,   
                        '',//porincre    
                        '',//cost_prom  
                        $det->costo, 
                        '',//flag    
                        '',//flagdeta    
                        '',//usuario 
                        '',//fcontrol    
                        '',//ws  
                        '',//comision    
                        '',//comisionso  
                        '',//descto_1    
                        '',//descto_2   
                        $codigo,//almacen 
                        '',//canti_m3    
                        '',//guia   
                        '',//marca   
                        '',//modelo  
                        '',//motor   
                        '',//chasis  
                        '',//color   
                        '',//ano 
                        '',//producto    
                        '',//talla   
                        '',//fecha  
                        '',//ibolsas

                    )
                ); 

                 DB::tABLE('cpe_detalle')->where('IdCpe_detalle',$det->IdCpe_detalle)->update(['migrar_det'=>'1']);  

            }
        }
    }

    dbase_close($db_det);
	
    
	if($request->ajax()) {

          return response()->json(['mensaje' => 'MIGRADO']);

        }
	/*}
	
	elseif(count($cantidad)=='0'){
		
		if($request->ajax()) {

          return response()->json(['mensaje' => 'NO EXISTEN REGISTROS PARA MIGRAR ']);

        }
		
	}else{
		if($request->ajax()) {

          return response()->json(['mensaje' => 'EXISTEN REGISTROS MIGRADOS EN EL RANGO DE FECHA. ELEGIR OTRO RANGO DE FECHA ']);

        }
	}
        */
        
        

    }


       public function registrar_compras_dbf(Request $request,$fecin=0,$fecfin=0){

 
  

    

       $archivo_cab = public_path()."/corporacion/almaingr.DBF";
      $archivo_det =  public_path()."/corporacion/almainde.DBF";

   /*   $dbh = dbase_open($archivo_cab, 0)

          or die("¡Error! No se pudo abrir el archivo de base de datos dbase '$ruta_db'.");

        // Obtener la información de columna
        $info_columna = dbase_get_header_info($dbh);

        // Mostrar la información
        foreach ($info_columna as $key => $value) {
          echo  "'',".$value['name']."<br>";

        }*/
  

        $cabecera =  DB::tABLE('compras_cabecera')
        ->leftjoin('proveedor','compras_cabecera.prov_id','proveedor.prov_id')
        ->join('moneda','compras_cabecera.mon_id','moneda.moncod')
        ->join('tipo_documento','compras_cabecera.tdocod','tipo_documento.tdocod')
        ->where('tipocompra','Producto')
        ->where('est_compra','Registrado')
        ->where('com_fec','>=',$fecin)
        ->where('com_fec','<=',$fecfin)
        ->where('compras_cabecera.tdocod','!=','80')
        ->orderby('com_cab_id','desc')
        ->get();


      $db_cab = dbase_open($archivo_cab, 2);

      $db_det = dbase_open($archivo_det, 2);

      if($db_cab){
      $registros = dbase_numrecords($db_cab);
      
      for($i=1;$i<=$registros;$i++){
        
        dbase_delete_record($db_cab,$i);
        
      }
      
      dbase_pack($db_cab);
    }
    
    if($db_det){
      $registrosdet = dbase_numrecords($db_det);
      
      for($j=1;$j<=$registrosdet;$j++){
        
        dbase_delete_record($db_det,$j);
        
      }
      dbase_pack($db_det);
    }
    


        if($db_cab){

        foreach ($cabecera as $cab){
            
            $bus_alma = DB::tABLE('compras_cabecera')->where('com_cab_id',$cab->com_cab_id)->first();


              $dateEmi = date_create($cab->com_fec);
              $dateVen = date_create($cab->com_fec_ven);
              $dateIng = date_create($cab->com_fec_ing);
              
              $fechaEmi = date_format($dateEmi,"Ymd");
              $fechaVen = date_format($dateEmi,"Ymd");
              $fechaIng = date_format($dateIng,"Ymd");
              
                   
          


          if($cab->est_compra=='Eliminado'){
            $anulado = '1';
          }else{
            $anulado='0';
          }

			$almacen = DB::tABLE('almacenes')->where('predeterminado','1')
      ->where('id_empresa_negocio',$bus_alma->id_empresa_negocio)
      ->first();
			
			$codigo = $almacen->codigo;

          dbase_add_record($db_cab, 
            array(
                    $cab->com_doc_num,//NUMDOC
                    $cab->com_doc_ser,//SERIEDOC
                    $cab->prov_ruc,//RUC_DNI
                    '',//ID
                    '',//NORDEN
                    $cab->cod_mov,//CODMOV
                    '',//SER9
                    $almacen->codigo,//ALMA
                    '02',//COD_OPERA
                    $cab->tdocod,//CODDOC
                    '',//DESDOC
                    $fechaEmi,//FECHADOC
                    $fechaIng,//FECHAING
                    '',//FEPAGO
                    '',//IMPOPAGO
                    '',//FLETE
                    ($cab->total_com-$cab->igv_com),//TBRUTO
                    '',//GRAVADA
                    '',//DESCTO
                    '',//IGVR
                    $cab->igv_com,//IGV
                    '',//FINANCIA
                    '',//OTROS
                    '',//TNETO
                    $cab->total_com,//TOTAL
                    '',//TOTANTI
                    '',//TC
                    '',//TCPAGO
                    $cab->simbolo,//MONEDA
                    '',//NOTA
                    $anulado,//ANULADA
                    '',//IGV_
                    '',//FLAG
                    '',//USUARIO
                    '',//FCONTROL
                    '',//WS
                    '',//FPAGO
                    '',//GUIA
                    '',//MES
                    '',//ANO
                    '',//DIAS
                    $fechaVen,//FECVENCI
                    '',//PESO
                    '',//TRANSPORTE
                    '',//PROCEDENCI
                    '',//BULTOS
                    '',//LETRA
                    '',//PDI
                    '',//IES
                    '',//RENTA
                    '',//RETEN
                    '',//FLAGSTOCK
                    '',//FLAGCOSTO
                    '',//CANJEGUIA
                    '',//CODCIUDAD
                    '',//PERCEPCION
                    '',//AREA
                    '',//NOMAREA
                    '',//FCON
                    '',//CONT
                    '',//CTACARGO
                    '',//CTAABONO
                    '',//REGCOMPRA
                    '',//SER1
                    '',//SER2
                    '',//SER3
                    '',//SER4
                    '',//SER5
                    '',//SER6
                    '',//SER7
                    '',//SER8
                    '',//NUM1
                    '',//FE1
                    '',//IMP1
                    '',//NUM2
                    '',//NUM3
                    '',//NUM4
                    '',//NUM5
                    '',//NUM6
                    '',//NUM7
                    '',//NUM8
                    '',//NUM9
                    '',//IMP2
                    '',//IMP3
                    '',//IMP4
                    '',//IMP5
                    '',//IMP6
                    '',//IMP7
                    '',//IMP8
                    '',//IMP9
                    '',//FE2
                    '',//FE3
                    '',//FE4
                    '',//FE5
                    '',//FE6
                    '',//FE7
                    '',//FE8
                    '',//FE9
                    '',//FCODDOC
                    '',//FECHANC
                    '',//FSERIE
                    '',//FNUMERO
                    '',//MPAGO
                    '',//BANCO
                    '',//NCHEQUE
                    '',//CTACANCE
                    '',//DIFEREN
                    '',//FCONPAGO
                    '',//ONG
                    '',//OGYEYONG
                    '',//NG
                    '',//OGYE
                    '',//DUA
                    '',//TIPO_2
                    '',//ISC
                    '',//BASE_RT
                    '',//IGV_1
                    '',//IGV_2
                    '',//IGV_3
                    '',//FE_DPR
                    '',//CP_DPR
                    '',//IM_DPR
                    '',//DE_DPR
            )
        );   

        }
    }


    // DETALLE
    dbase_close($db_cab);
    
 

    if($db_det){
        foreach ($cabecera as $cab){
            

            $detalle= DB::tABLE('compras_detalle as cd')
            ->join('compras_cabecera','compras_cabecera.com_cab_id','cd.com_cab_id')
            ->join('unidad_medida as um','um.umecod','cd.ume_cod')
            ->join('productos as p','p.IdProducto','cd.pro_id')
            ->where('cd.com_cab_id',$cab->com_cab_id)
            ->where('com_fec','>=',$fecin)
            ->where('com_fec','<=',$fecfin)
            ->get();

            foreach ($detalle as $det) {
                
                dbase_add_record($db_det, 
                    array(
                     '',//id 
                     '',//cia 
                     $det->cod_mov,//codmov  
                     $det->procod,//cunico  
                     $det->pronom,//descrip 
                     $det->ume_cod,//cunida  
                     '',//dunida  
                     '',//stock 
                     $det->com_det_factor,//factor  
                     $det->cantidad,//canti 
                     '',//Canti_m 
                     $det->pre_uni,//puni  
                     '',//puni_m  
                     '',//descto_p  
                     '',//descto_v  
                     '',//igv_p 
                     '',//igv_v 
                     '',//flete_p 
                     '',//flete_v 
                     $det->total,//total 
                     '',//pcosto_a  
                     '',//margenp_a 
                     '',//margenv_a 
                     '',//pventa_a  
                     '',//pcosto_n  
                     '',//margenp_n 
                     '',//margenv_n 
                     '',//reintegro 
                     '',//costo1  
                     '',//kilo  
                     '',//flete 
                     '',//costo2  
                     '',//margen  
                     '',//pventa  
                     '',//costo_prome 
                     '',//ap  
                     '',//flag  
                     '',//usuario 
                     '',//fcontrol  
                     '',//ws  
                     $codigo,//almacen 
                     '',//producto  
                     '',//marca 
                     '',//fvenci  
                     '',//merma

                    )
                );   

            }
        }
    }

    dbase_close($db_det);
        
        if($request->ajax()) {

          return response()->json(['mensaje' => 'MIGRADO']);

        }

    }




    public function registrar_clientes_proveedores_dbf(Request $request){

      
      $clientes = DB::tABLE('cliente')->get();

      $proveedores = DB::tABLE('proveedor')->get();

      $archivo_cli_prov = "D:\SIGCO\Base01/acrecli.DBF";

      $db_cli_prov = dbase_open($archivo_cli_prov, 2);


      if($db_cli_prov){
        foreach ($clientes as $cli) {
              dbase_add_record($db_cli_prov, 
                  array(
                      $cli->clicod,//id
                      '',//cia 
                      '',//prov 
                      '1',//clie 
                      '',//plla 
                      '',//categ
                      $cli->clicod,//codigo  
                      $cli->clinum,//num_doc 
                      $cli->clinom,//nom_razo  
                      '',//nomcorto  
                      $cli->clidir,//direc 
                      '',//localida  
                      '',//postal  
                      $cli->telefono,//telefs  
                      '',//fax 
                      $cli->clicor,//email 
                      '',//doc_id  
                      '',//repres  
                      '',//cargo 
                      '',//doc_repr  
                      '',//num_repr  
                      '',//bco_sol 
                      '',//cta_sol 
                      '',//esctasol  
                      '',//bco_dol 
                      '',//cta_dol 
                      '',//esctadol  
                      '',//diasgrac  
                      '',//limcred 
                      '',//s_d 
                      '',//condpag 
                      '',//observac  
                      '',//lipre 
                      '',//fec_ini 
                      '',//usuario 
                      '',//fcontrol  
                      '',//ws  
                      '',//op  
                      '',//cuentacli 
                      '',//cuentavta 
                      '',//cuentapro 
                      '',//zona  
                      '',//ctaantip  
                      '',//ctaantic  
                      '',//moneda  
                      '',//tipo  
                      '',//co  
                      '',//ci  
                      '',//ce  
                      '',//paterno 
                      '',//materno 
                      '',//nombres
                      '',

                    )
                  );
          }
      
        foreach ($proveedores as $prov) {
              dbase_add_record($db_cli_prov, 
                  array(
                      $prov->prov_id,//id
                      '',//cia 
                      '1',//prov 
                      '',//clie 
                      '',//plla 
                      '',//categ
                      $prov->prov_id,//codigo  
                      $prov->prov_ruc,//num_doc 
                      $prov->prov_raz,//nom_razo  
                      '',//nomcorto  
                      $prov->prov_dir,//direc 
                      '',//localida  
                      '',//postal  
                      $prov->prov_num_con,//telefs  
                      '',//fax 
                      $prov->prov_cor,//email 
                      '',//doc_id  
                      '',//repres  
                      '',//cargo 
                      '',//doc_repr  
                      '',//num_repr  
                      '',//bco_sol 
                      '',//cta_sol 
                      '',//esctasol  
                      '',//bco_dol 
                      '',//cta_dol 
                      '',//esctadol  
                      '',//diasgrac  
                      '',//limcred 
                      '',//s_d 
                      '',//condpag 
                      '',//observac  
                      '',//lipre 
                      '',//fec_ini 
                      '',//usuario 
                      '',//fcontrol  
                      '',//ws  
                      '',//op  
                      '',//cuentacli 
                      '',//cuentavta 
                      '',//cuentapro 
                      '',//zona  
                      '',//ctaantip  
                      '',//ctaantic  
                      '',//moneda  
                      '',//tipo  
                      '',//co  
                      '',//ci  
                      '',//ce  
                      '',//paterno 
                      '',//materno 
                      '',//nombres
                      '',//
                      
                    )
                  );
          }
        }

        dbase_close($db_cli_prov);
        
        if($request->ajax()) {

          return response()->json(['mensaje' => 'MIGRADO']);

        }
    }

    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
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

     public function extraer_ventas_dbf(Request $request){

    
       
       
        $archivo_cab = public_path()."/corporacion/importar/cabecera.dbf";
      
        $archivo_det = public_path()."/corporacion/importar/detalle.dbf";

       $db_cab = dbase_open($archivo_cab, 0);
    
    
        $almacen = DB::tABLE('almacenes')
        ->where('predeterminado','1')
              ->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
              ->first();

          $id = [];

        if($db_cab){
            $numero_registros = dbase_numrecords($db_cab);
            for($i=1;$i<=$numero_registros; $i++){
                $fila = dbase_get_record_with_names($db_cab, $i);
                   $validar = DB::tABLE('cpe_cabecera')
                   ->where('serdoc',$fila['SERIEDOC'])
                   ->where('numdoc',$fila['NUM_DOC'])
                   ->where('numdoc',$fila['NUM_DOC'])
                   ->where('tdocod',$fila['COD_DOC'])
                   ->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)
                   ->get();

            if(count($validar)==0){
                 
                  

                 if(empty(trim($fila['NOTA']))){
                    $clinom = 'Venta al Portador';
                  }else{
                    $clinom = $fila['NOTA'];
                  }


                  if(empty(trim($fila['RUC']))){
                    $clinum = '00000001';
                  }else{
                    $clinum = $fila['RUC'];
                  }


                  if(empty(trim($fila['TIPO_2']))){
                    $tdicod='1';
                  }else{
                    $tdicod = $fila['TIPO_2'];
                  }


                  if($clinum =='000000001' || $clinum =='00000000'){
                         $cliente = Cliente::FirstOrCreate(['clinum'=>$clinum,'clinom'=>$clinom,'rucemp'=>Auth::user()->IdEmpresa
                      ],
                      ['clinom'=>$clinom,
                        'clidir'=>'--',
                        'rucemp'=>Auth::user()->IdEmpresa,
                        'tdicod'=>$tdicod
                      ]);
                       
                      }else{
                         $cliente = Cliente::UpdateOrCreate(['clinum'=>$clinum,'rucemp'=>Auth::user()->IdEmpresa],
                        ['clinom'=>$clinom,
                        'clidir'=>'--',
                        'rucemp'=>Auth::user()->IdEmpresa,
                        'tdicod'=>$tdicod
                      ]);
                      }
               

                      $cabecera = new cpe_cabecera;
                      $cabecera->cod_mov = $fila['CODMOV'];
                      $cabecera->tdocod = $fila['COD_DOC'];
                      $cabecera->topcod = '0101';
                      $cabecera->ccafem = $fila['FECHA_DOC'];
                      $cabecera->id_almacen = $almacen->id_almacen;
                      $cabecera->ccafve = $fila['FECVENCI'];

                      
                      $cabecera->totalcontado = $fila['IMPOPAGO'];
                      $cabecera->IdUsuario_ven = $fila['VENDEDOR'];
                      
                      $cabecera->totalcredito = '0';
                      
                     
                      
                      $cabecera->tdicod = $tdicod;
                      $cabecera->ccandi = $fila['RUC'];
                      $cabecera->ccanom = $fila['NOTA'];
                      $cabecera->direccion = '--';
                      $cabecera->clicod = $cliente->clicod;
                  
                      if($fila['MONEDA']=='S/'){
                        $cabecera->moncod =='PEN';
                      }elseif ($fila['MONEDA']=='$') {
                        $cabecera->moncod =='USD';
                      }

                      if($fila['ANULADA']=='1'){
                          $cabecera->ccabaj = $fila['ANULADA'];
                      }else{
                          $cabecera->ccabaj = null;
                      }

                      $cabecera->tipcambio = $fila['TC'];;
                      $cabecera->ccatinaf =  '0.00';
                      $cabecera->ccatexo = $fila['EXONERADA'];
                      $cabecera->ccatvg = $fila['GRAVADA'];
                      $cabecera->ccaigv = $fila['IGV'];
                      $cabecera->icbper = $fila['IBOLSAS'];
                      $cabecera->ccaitv = $fila['TOTAL'];
                      $cabecera->id_empresa_negocio = $almacen->id_empresa_negocio;
                      $cabecera->IdUsuario = Auth::user()->IdUsuario;
                      $cabecera->IdEmpresa = Auth::user()->IdEmpresa;
                      $cabecera->serdoc= $fila['SERIEDOC'];
                      $cabecera->numdoc = $fila['NUM_DOC'];
                      $cabecera->tipnot= $fila['GCODMOTI'];
                      $cabecera->ccaobs= $fila['GMOTIVO'];
                      $cabecera->serie_ref= trim($fila['SER_DOC_C']);
                      $cabecera->num_ref = trim(ltrim($fila['NUM_DOC_C'],'0'));
                      $cabecera->tdocod_ref = trim($fila['TIP_DOC_C']);
                      $cabecera->save();

                      $id[] = $fila['CODMOV'];





            }
        }

      }

 
    dbase_close($db_cab);



                                $db_det = dbase_open($archivo_det, 0);
                                if ($db_det) {
                                    $numero_registros1 = dbase_numrecords($db_det);
                                    for ($j = 1; $j <= $numero_registros1; $j++) {
                                        $fila1 = dbase_get_record_with_names($db_det, $j);

                                        if(in_array($fila1['CODMOV'],$id)){

                                             $buscar = DB::tABLE('cpe_cabecera')
                                        ->where('cod_mov',$fila1['CODMOV'])
                                        ->first();

                                       
                                           if(!empty($buscar)){
                                            
                                            try{

                                            $unidad = DB::tABLE('unidad_medida')->where('umecin',$fila1['CUNIDA'])->first();

                                              $detalle = new cpe_detalle;
                                              $detalle->IdCpe_cabecera =  $buscar->IdCpe_cabecera;
                                              $detalle->procod = $fila1['CUNICO'];


                                              $detalle->umecod = $unidad->umecod;
                                              $detalle->cdecan = $fila1['CANTI'];
                                              $detalle->cdepsu = "";
                                              $detalle->cdedes = $fila1['DESCRIP'];
                                              $valorsubtotal = $fila1['TOTAL'];
                                              $valortotal = $fila1['TOTAL'];
                                              $valorigvtotal =  $valortotal-$valorsubtotal;
                                              $detalle->cdepuni = $fila1['PRECIO'];
                                              $detalle->cdevun =  $fila1['PRECIO'];
                                              $detalle->cdevve = $valortotal;
                                              $detalle->cdepve = $valorsubtotal;
                                              $detalle->cdeigv = $valorigvtotal;
                                              $detalle->tigcod='20';
                                              $detalle->fecha_venta = $buscar->ccafem;
                                              $detalle->save();
                                            }catch(\Exception $e){

                                                $detalle = new cpe_detalle;
                                              $detalle->IdCpe_cabecera =  $buscar->IdCpe_cabecera;
                                              $detalle->procod = $fila1['CUNICO'];
                                              $detalle->umecod = 'NIU';
                                              $detalle->cdecan = $fila1['CANTI'];
                                              $detalle->cdepsu = "";
                                              $detalle->cdedes = $fila1['DESCRIP'];
                                              $valorsubtotal = $fila1['TOTAL'];
                                              $valortotal = $fila1['TOTAL'];
                                              $valorigvtotal =  $valortotal-$valorsubtotal;
                                              $detalle->cdepuni = $fila1['PRECIO'];
                                              $detalle->cdevun =  $fila1['PRECIO'];
                                              $detalle->cdevve = $valortotal;
                                              $detalle->cdepve = $valorsubtotal;
                                              $detalle->cdeigv = $valorigvtotal;
                                              $detalle->tigcod='20';
                                              $detalle->fecha_venta = $buscar->ccafem;
                                              $detalle->save();


                                            }

                                           
                                        }

                                       
                                        }
                                       
                                     
                                    }

                           
                              }

                            dbase_close($db_det);


  
         if($request->ajax()) {

          return response()->json(['mensaje' => 'MIGRADO']);

        }
 

    }

    public function generar_codigo_movimiento($IdCpe_cabecera){

        $bus_cpe = DB::tABLE('cpe_cabecera')->where('IdCpe_cabecera',$IdCpe_cabecera)->First();

        $bus_suc = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',$bus_cpe->id_empresa_negocio)->first();

        $gen_cod = DB::tABLE('cpe_cabecera')->where('IdCpe_cabecera',$IdCpe_cabecera)->update(['cod_mov'=>'MOV'.$bus_suc->cod_suc.$IdCpe_cabecera]);

        return $gen_cod;
    }


    public function ventas_concar_dbf(Request $request){


        $fecin = $request->get('fecin');
        $fecfin = $request->get('fecfin');
        
        $conf_concar = DB::tABLE('configuracion_concar')->first();

        $sucursal = DB::tABLE('empresa_negocios')->where('id_empresa_negocio',Auth::user()->id_empresa_negocio)->first();

        $dateini = strtotime($fecin);
        $datefin = strtotime($fecfin);
        
        $dini = date('dmY',$dateini);
        $dfin =  date('dmY',$datefin);
        

        $cabecera = DB::tABLE('cpe_cabecera')
        ->join('tipo_documento','tipo_documento.tdocod','cpe_cabecera.tdocod')
        ->join('moneda','moneda.moncod','cpe_cabecera.moncod')
        ->join('tipo_documento_identidad','tipo_documento_identidad.tdicod','cpe_cabecera.tdicod')
        ->leftjoin('credito_dias','credito_dias.cre_dia_id','cpe_cabecera.cre_dia_id')
         ->where(function ($query) {
          $query->where('cpe_cabecera.tdocod','01')
              ->orWhere('cpe_cabecera.tdocod','03');
          })
        //->where('migrar','0')
        ->where('ccafem','>=',$fecin)
        ->where('ccafem','<=',$fecfin)
        ->get();

    
    
           
          $ano_per =  date_format(date_create($fecin),"y");
     

        $archivo_cab_ant = public_path()."/CONCAR/CC00EEAA_C.DBF";
            $archivo_cab = public_path()."/CONCAR/CC".$sucursal->cod_suc.$ano_per.".DBF";
        
        $archivo_det_ant = public_path()."/CONCAR/CD00EEAA_D.DBF";
            $archivo_det = public_path()."/CONCAR/CD".$sucursal->cod_suc.$ano_per.".DBF";
        
        copy($archivo_cab_ant,$archivo_cab);
        copy($archivo_det_ant,$archivo_det);
        
       $db_cab = dbase_open($archivo_cab, 2);

       $db_det = dbase_open($archivo_det, 2);
       
        if($db_cab){
            $registros = dbase_numrecords($db_cab);
            
            for($i=1;$i<=$registros;$i++){
                
                dbase_delete_record($db_cab,$i);
                
            }
            
            dbase_pack($db_cab);
        }
        
        if($db_det){
            $registrosdet = dbase_numrecords($db_det);
            
            for($j=1;$j<=$registrosdet;$j++){
                
                dbase_delete_record($db_det,$j);
                
            }
            dbase_pack($db_det);
        }
        


        if($db_cab){


        $i=$conf_concar->prov_corre;

        foreach ($cabecera as $cab){

            $i = $i+1;

            $mes =  date_format(date_create($cab->ccafem),"m");
            $ano =  date_format(date_create($cab->ccafem),"y");
            $dia =  date_format(date_create($cab->ccafem),"d");

            if($cab->moncod=='PEN'){
                $moncod ='MN';
            }else{
                $moncod ='ME';
            }

              $almacen = DB::tABLE('almacenes')->where('predeterminado','1')->where('id_empresa_negocio',$cab->id_empresa_negocio)->first();
            
              if($cab->estadopago=='CONTADO'){ 
                       $montocredito =  $cab->ccaitv;
              }else{
                       $montocredito= '';
              } 

                    $codigo = $almacen->codigo;
                        
                    $dateEmi = date_create($cab->ccafem);
                    $dateVen = date_create($cab->ccafve);
                    
                    $fechaEmi = date_format($dateEmi,"Ymd");
                    $fechaVen = date_format($dateEmi,"Ymd");
                    
                         
              if(is_null($cab->ccabaj)){
                        $anulado ='0';
                }else{
                        $anulado ='1';
                }
                
            

              

          dbase_add_record($db_cab, 
            array(
              $conf_concar->provision,//num_mes
              $mes.str_pad($i,4,"0", STR_PAD_LEFT), 
              $ano.$mes.$dia,//id
              $moncod,
              'F', 
              number_format($cab->tipcambio,'5','.',''),
              $cab->ccanom.' '.$cab->serdoc.'-'.str_pad($cab->numdoc,7,"0", STR_PAD_LEFT),
              $cab->ccaitv,
              'V',  
              'S',   
              $fechaEmi,              
              Carbon::parse($cab->fecha_hora)->format('H:i'), 
              '',
              '',    
              '',      
              '',  
              '', 
              '',   
              $fechaEmi,   
              '', 
              'S', 
              $cab->serdoc,  
              $fechaEmi,
              '', 
              ''
            )
        );   


      
      
        }


        $j=$conf_concar->canc_corre;
        foreach ($cabecera as $cab){

            $j = $j+1;

            $mes =  date_format(date_create($cab->ccafem),"m");
            $ano =  date_format(date_create($cab->ccafem),"y");
            $dia =  date_format(date_create($cab->ccafem),"d");

            if($cab->moncod=='PEN'){
                $moncod ='MN';
            }else{
                $moncod ='ME';
            }

              $almacen = DB::tABLE('almacenes')->where('predeterminado','1')->where('id_empresa_negocio',$cab->id_empresa_negocio)->first();
            
              if($cab->estadopago=='CONTADO'){ 
                       $montocredito =  $cab->ccaitv;
              }else{
                       $montocredito= '';
              } 

                    $codigo = $almacen->codigo;
                        
                    $dateEmi = date_create($cab->ccafem);
                    $dateVen = date_create($cab->ccafve);
                    
                    $fechaEmi = date_format($dateEmi,"Ymd");
                    $fechaVen = date_format($dateEmi,"Ymd");
                    
                         
              if(is_null($cab->ccabaj)){
                        $anulado ='0';
                }else{
                        $anulado ='1';
                }
                
        

        dbase_add_record($db_cab, 
            array(
              $conf_concar->cancelacion,//num_mes
                $mes.str_pad($j,4,"0", STR_PAD_LEFT), 
              $ano.$mes.$dia,//id
              $moncod,
              'F', 
              number_format($cab->tipcambio,'5','.',''),
              $cab->ccanom.' '.$cab->serdoc.'-'.str_pad($cab->numdoc,7,"0", STR_PAD_LEFT),
              $cab->ccaitv,
              'V',  
              'S',   
              $fechaEmi,              
              Carbon::parse($cab->fecha_hora)->format('H:i'), 
              '',
              '',    
              '',      
              '',  
              '', 
              '',   
              $fechaEmi,   
              '', 
              'S', 
              $cab->serdoc,  
              $fechaEmi,
              '', 
              ''
            )
        );   

      
        }


    }


    // DETALLE
    dbase_close($db_cab);
    

     


       if($db_det){


        $i=$conf_concar->prov_corre;
        
        foreach ($cabecera as $cab){


            if($cab->tipcambio>'0'){
                $ven_dol = $cab->ccaitv/$cab->tipcambio;
            }else{
                $ven_dol = 0;
            }
            

         

            if($cab->tdocod=='03'){
                $cod_d_provi = $conf_concar->cod_d_prov_bol;
                $cod_h_provi = $conf_concar->cod_h_prov_bol;

                if(is_null($cab->ccabaj)){
                     $cod_comp = $conf_concar->cod_ven_con;
                }else{
                     $cod_comp = $conf_concar->cod_ven_anu;
                }
               
            }elseif($cab->tdocod=='01'){
                $cod_d_provi = $conf_concar->cod_d_prov_fac;
                $cod_h_provi = $conf_concar->cod_h_prov_fac;
                
                if(is_null($cab->ccabaj)){
                     $cod_comp = $conf_concar->cod_ven_con;
                }else{
                     $cod_comp = $conf_concar->cod_ven_anu;
                }
               
               
            }

            $i = $i+1;

            $mes =  date_format(date_create($cab->ccafem),"m");
            $ano =  date_format(date_create($cab->ccafem),"y");
            $dia =  date_format(date_create($cab->ccafem),"d");

            if($cab->moncod=='PEN'){
                $moncod ='MN';
            }else{
                $moncod ='ME';
            }

              $almacen = DB::tABLE('almacenes')->where('predeterminado','1')->where('id_empresa_negocio',$cab->id_empresa_negocio)->first();
            
              if($cab->estadopago=='CONTADO'){ 
                       $montocredito =  $cab->ccaitv;
              }else{
                       $montocredito= '';
              } 

                    $codigo = $almacen->codigo;
                        
                    $dateEmi = date_create($cab->ccafem);
                    $dateVen = date_create($cab->ccafve);
                    
                    $fechaEmi = date_format($dateEmi,"Ymd");
                    $fechaVen = date_format($dateEmi,"Ymd");
                    
                         
              if(is_null($cab->ccabaj)){
                        $anulado ='0';
                }else{
                        $anulado ='1';
                }
                
            

              

          dbase_add_record($db_det, 
            array(
              $conf_concar->provision,//num_mes
              $mes.str_pad($i,4,"0", STR_PAD_LEFT), 
              str_pad('1',4,"0", STR_PAD_LEFT),
              $ano.$mes.$dia,//id
              $cod_d_provi, 
              $cod_comp,
              '', 
              $moncod,
              'D',
              $cab->ccaitv,
              $cab->des_doc,
              $cab->serdoc.'-'.str_pad($cab->numdoc,7,"0", STR_PAD_LEFT),
              $ano.$mes.$dia,//id
              '',
              '',  
              'S',   
              $fechaEmi,              
              $cab->ccanom.' '.$cab->serdoc.'-'.str_pad($cab->numdoc,7,"0", STR_PAD_LEFT),
              $ven_dol,
              $cab->ccaitv,   
              '',
              $fechaEmi,   
              $fechaEmi, 
              '',
              '',
              '',
              '', 
              '',
              '',
              '',
              '',
              '',
              '',
              '',
              '',
              '',
              '',  
              '',   
              '',              
              '', 
              '',
              '',    
              '',      
              '',  
              '', 
              '',   
              '',   
              '', 
              '', 
              ''
            
            )
        );   




          dbase_add_record($db_det, 
            array(
              $conf_concar->provision,//num_mes
              $mes.str_pad($i,4,"0", STR_PAD_LEFT), 
              str_pad('2',4,"0", STR_PAD_LEFT),
              $ano.$mes.$dia,//id
              $cod_h_provi,
              $cod_comp,
              '', 
              $moncod,
              'H',
              $cab->ccaitv,
              $cab->des_doc,
              $cab->serdoc.'-'.str_pad($cab->numdoc,7,"0", STR_PAD_LEFT),
              $ano.$mes.$dia,//id
              '',
              '',  
              'S',   
              $fechaEmi,              
              $cab->ccanom.' '.$cab->serdoc.'-'.str_pad($cab->numdoc,7,"0", STR_PAD_LEFT),
              $ven_dol,
              $cab->ccaitv,   
              '',
              $fechaEmi,   
              $fechaEmi, 
              '',
              '',
              '',
              '', 
              '',
              '',
              '',
              '',
              '',
              '',
              '',
              '',
              '',
              '',  
              '',   
              '',              
              '', 
              '',
              '',    
              '',      
              '',  
              '', 
              '',   
              '',   
              '', 
              '', 
              ''
            )
        ); 

    }



    $j=$conf_concar->canc_corre;

    foreach ($cabecera as $cab){


            if($cab->tipcambio>'0'){
                $ven_dol = $cab->ccaitv/$cab->tipcambio;
            }else{
                $ven_dol = 0;
            }
            


           
            if($cab->tdocod=='03'){
            
                $cod_h_can = $conf_concar->cod_h_can_bol;
                $cod_comp = $conf_concar->cod_ven_con;

                if(is_null($cab->ccabaj)){
                     $cod_comp = $conf_concar->cod_ven_con;
                }else{
                     $cod_comp = $conf_concar->cod_ven_anu;
                }
               


            }elseif($cab->tdocod=='01'){

                $cod_h_can = $conf_concar->cod_h_can_fac;
                $cod_comp = $cab->ccandi;

                if(is_null($cab->ccabaj)){
                     $cod_comp = $conf_concar->cod_ven_con;
                }else{
                     $cod_comp = $conf_concar->cod_ven_anu;
                }
               
            }


            $j = $j+1;

            $mes =  date_format(date_create($cab->ccafem),"m");
            $ano =  date_format(date_create($cab->ccafem),"y");
            $dia =  date_format(date_create($cab->ccafem),"d");

            if($cab->moncod=='PEN'){
                $moncod ='MN';
            }else{
                $moncod ='ME';
            }

            $almacen = DB::tABLE('almacenes')->where('predeterminado','1')->where('id_empresa_negocio',$cab->id_empresa_negocio)->first();
            
            if($cab->estadopago=='CONTADO'){ 
                       $montocredito =  $cab->ccaitv;
            }else{
                     $montocredito= '';
            } 

            $codigo = $almacen->codigo;
                        
            $dateEmi = date_create($cab->ccafem);
            $dateVen = date_create($cab->ccafve);
                    
            $fechaEmi = date_format($dateEmi,"Ymd");
            $fechaVen = date_format($dateEmi,"Ymd");
                    
                         
            if(is_null($cab->ccabaj)){
                $anulado ='0';
            }else{
                $anulado ='1';
            }
                

          dbase_add_record($db_det, 
            array(
              $conf_concar->cancelacion,//num_mes
              $mes.str_pad($j,4,"0", STR_PAD_LEFT), 
              str_pad('1',4,"0", STR_PAD_LEFT),
              $ano.$mes.$dia,//id
              $conf_concar->cod_d_can, 
              $cod_comp,
              '', 
              $moncod,
              'D',
              $cab->ccaitv,
              $cab->des_doc,
              $cab->serdoc.'-'.str_pad($cab->numdoc,7,"0", STR_PAD_LEFT),
              $ano.$mes.$dia,//id
              '',
              '',  
              'S',   
              $fechaEmi,              
              $cab->ccanom.' '.$cab->serdoc.'-'.str_pad($cab->numdoc,7,"0", STR_PAD_LEFT),
              $ven_dol,
              $cab->ccaitv,   
              '',
              $fechaEmi,   
              $fechaEmi, 
              '',
              '',
              '',
              '', 
              '',
              '',
              '',
              '',
              '',
              '',
              '',
              '',
              '',
              '',  
              '',   
              '',              
              '', 
              '',
              '',    
              '',      
              '',  
              '', 
              '',   
              '',   
              '', 
              '', 
              ''
            
            )
        );   


        dbase_add_record($db_det, 
            array(
              $conf_concar->cancelacion,//num_mes
                $mes.str_pad($j,4,"0", STR_PAD_LEFT), 
              str_pad('2',4,"0", STR_PAD_LEFT),
              $ano.$mes.$dia,//id
              $cod_h_can,
              $cod_comp,
              '', 
              $moncod,
              'H',
              $cab->ccaitv,
              $cab->des_doc,
              $cab->serdoc.'-'.str_pad($cab->numdoc,7,"0", STR_PAD_LEFT),
              $ano.$mes.$dia,//id
              '',
              '',  
              'S',   
              $fechaEmi,              
              $cab->ccanom.' '.$cab->serdoc.'-'.str_pad($cab->numdoc,7,"0", STR_PAD_LEFT),
              $ven_dol,
              $cab->ccaitv,   
              '',
              $fechaEmi,   
              $fechaEmi, 
              '',
              '',
              '',
              '', 
              '',
              '',
              '',
              '',
              '',
              '',
              '',
              '',
              '',
              '',  
              '',   
              '',              
              '', 
              '',
              '',    
              '',      
              '',  
              '', 
              '',   
              '',   
              '', 
              '', 
              ''
            )
        );   


        }

    }


    // DETALLE
    dbase_close($db_det);
    

    if($request->ajax()) {

          return response()->json(['mensaje' => 'MIGRADO CONCAR']);

        }



    }
}
