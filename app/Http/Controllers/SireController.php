<?php

namespace MasterSoft\Http\Controllers;

use Illuminate\Http\Request;
use MasterSoft\Http\Requests;
use MasterSoft\Modelos\ReportesVentas;
use MasterSoft\TipoIGV;
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
use MasterSoft\Modelos\SireCompras;
use MasterSoft\Modelos\SireVentas;
use MasterSoft\Modelos\SolicitudSire;
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
use Carbon\Carbon;
use Excel;
use PDF;

class SireController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function buscarRegistrosVentasCompras(Request $request){
        $sucursal = DB::table('empresa_negocios')->get();
        $fec_ini = Carbon::now()->startOfMonth()->format('Y-m-d');
        $fec_fin = Carbon::now()->endOfMonth()->format('Y-m-d');

        return view('empresas.reportes.sire.buscadores.buscar_registro_ventas_compras',compact('sucursal','fec_ini','fec_fin'));
    }

    public function consultarSireSunat(Request $request){
        $sucursal = DB::table('empresa_negocios')->get();
        $fec_ini = Carbon::now()->startOfMonth()->format('Y-m-d');
        $fec_fin = Carbon::now()->endOfMonth()->format('Y-m-d');

        $solicitudes = DB::table('solicitud_sire')->orderby('solsire_id','desc')->get();

        return view('empresas.reportes.sire.buscadores.buscar_consulta_sunat',compact('sucursal','fec_ini','fec_fin','solicitudes'));
    }

    public function generarSire(Request $request){
        
        $fecha = $request->get('mes_ano');
        $ano = Carbon::parse($fecha)->format('Y');
        $mes = Carbon::parse($fecha)->format('m');

        $fec_ini = Carbon::parse($fecha)->startOfMonth()->format('Y-m-d');
        $fec_fin = Carbon::parse($fecha)->endOfMonth()->format('Y-m-d');

        $suc_id = $request->get('suc_id');
        $tip_rep = $request->get('tip_rep');
        $cod_oport = $request->get('cod_oport');

        $sucursal = DB::table('empresa_negocios')->where('id_empresa_negocio',$suc_id)->first();
        $empresa = Empresa::findOrFail($sucursal->IdEmpresa);

        $cons_ventas = new ReportesVentas();
        $comprobantes = $cons_ventas->obtenerComprobantesSunat($suc_id,$fec_ini,$fec_fin);

        $nom_arch_txt = 'LE'.$empresa->IdEmpresa.$ano.$mes.'00140400'.$cod_oport.'1112';
        $path_txt = public_path().'/sire/'.$nom_arch_txt.'.txt';
  
        if(file_exists($path_txt)){
            unlink($path_txt);
        }

        $archivo = fopen($path_txt, 'w');

        foreach($comprobantes as $cabecera){

            $fec_ven = !empty($cabecera->ccafem_ref) ? Carbon::parse($cabecera->ccafem_ref)->format('d/m/Y') : '';
          
            $params = [
                $empresa->IdEmpresa,
                $empresa->NomEmpresa,
                $ano.$mes,
                $empresa->IdEmpresa.$cabecera->tdocod.$cabecera->serdoc.$cabecera->numdoc,
                Carbon::parse($cabecera->fecha)->format('d/m/Y'),
                Carbon::parse($cabecera->ccafve)->format('d/m/Y'),
                $cabecera->tdocod,
                $cabecera->serie,
                $cabecera->numero,
                '',//numero final rango
                $cabecera->tdicod,
                $cabecera->numerodocumento,
                $cabecera->cliente,
                '',//exportacion
                $cabecera->gravado,
                '',//DESCUENTO BASE
                $cabecera->igv,
                '',//DESCUENTO IGV
                $cabecera->exonerado,
                '0.00',//inafecto
                '',//ISC
                '',//VASE IVAP
                '',//IVAP
                '0.00',//ICBPER
                '',//OTROS
                $cabecera->total,
                $cabecera->moncod,
                $cabecera->tipcambio,
                $fec_ven,
                $cabecera->tdocod_ref,
                $cabecera->serie_ref,
                $cabecera->num_ref,
                ''//Identificación Contrato
            ];

            $content = implode('|', $params).'|';
            fwrite($archivo, $content . PHP_EOL );
        }
        
        fclose($archivo);

        // Creamos un instancia de la clase ZipArchive
        $zip = new \ZipArchive();
        $zip_path = public_path().'/sire/'.$nom_arch_txt.'.zip';

        if ($zip->open($zip_path, \ZipArchive::CREATE) === TRUE) {
            $zip->addFile($path_txt, $nom_arch_txt.'.txt');
            $zip->close();
        }

        header("Content-type: application/octet-stream");
        header("Content-disposition: attachment; filename=".$nom_arch_txt.".zip");
        readfile($zip_path);
        
        // Limpiamos los archivos generados tras descargar
        unlink($zip_path);
        unlink($path_txt);

        return Redirect::to('/sire');   
    }

    public function obtenerToken($ruc){
        $empresa = DB::table('empresa')->where('IdEmpresa', $ruc)->first();
        
        if(!$empresa || empty($empresa->client_id) || empty($empresa->client_secret)){
            \Log::error("Faltan credenciales API SUNAT para el RUC: " . $ruc);
            return null;
        }

        $client_id = $empresa->client_id;
        $client_secret = $empresa->client_secret;
        $username = $empresa->IdEmpresa . $empresa->wsusuario;
        $password = $empresa->claveSunat;

        $url = 'https://api-seguridad.sunat.gob.pe/v1/clientessol/'.$client_id.'/oauth2/token/';
        
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_POSTFIELDS => http_build_query([
                'grant_type' => 'password',
                'scope' => 'https://api-sire.sunat.gob.pe',
                'client_id' => $client_id,
                'client_secret' => $client_secret,
                'username' => $username,
                'password' => $password
            ]),
            CURLOPT_HTTPHEADER => array('Content-Type: application/x-www-form-urlencoded'),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
            \Log::error("Error cURL en obtenerToken: " . $err);
            return null;
        }

        $respuesta = json_decode($response, true);
        return $respuesta['access_token'] ?? null;
    }

    public function obtenerPeriodos(){
        $token = self::obtenerToken(Auth::user()->IdEmpresa);

        if(!$token){
            return response()->json(['error' => 'No se pudo obtener el token de SUNAT'], 500);
        }

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api-sire.sunat.gob.pe/v1/contribuyente/migeigv/libros/rvierce/padron/web/omisos/140000/periodos',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Accept: application/json',
                'Authorization: Bearer ' . $token
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        
        echo $response;
    }

    public function descargarPropuesta(Request $request){
        $fecha = $request->get('mes_ano');
        $tipo = $request->get('tip_rep');
        $ruc = $request->get('suc_id');

        $ano = Carbon::parse($fecha)->format('Y');
        $mes = Carbon::parse($fecha)->format('m');
        $periodo = $ano.$mes;

        if($tipo=='1'){
            $url = 'https://api-sire.sunat.gob.pe/v1/contribuyente/migeigv/libros/rvie/propuesta/web/propuesta/'.$periodo.'/exportapropuesta?codTipoArchivo=1';
        }elseif($tipo=='2'){
            $url = 'https://api-sire.sunat.gob.pe/v1/contribuyente/migeigv/libros/rce/propuesta/web/propuesta/'.$periodo.'/exportacioncomprobantepropuesta?codTipoArchivo=1&codOrigenEnvio=1';
        }

        $token = self::obtenerToken($ruc);

        if(!$token){
            return back()->with('error', 'No se pudo obtener el token de SUNAT. Verifique sus credenciales API.');
        }

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Accept: application/json',
                'Authorization: Bearer '.$token
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);

        $respuesta = json_decode($response, true);

        if(isset($respuesta['numTicket'])){
            $solicitud = new SolicitudSire;
            $solicitud->periodo = $periodo;
            $solicitud->numTicket = $respuesta['numTicket'];
            $solicitud->tipArch = '1';
            $solicitud->tipo = $tipo;
            $solicitud->IdEmpresa = Auth::user()->IdEmpresa;
            $solicitud->save();
        }
        
        return Redirect::to('/sire/sunat');
    }

    public function consultarTicket($solsire_id){
        $solicitud = DB::table('solicitud_sire')->where('solsire_id',$solsire_id)->first();
        $token = self::obtenerToken(Auth::user()->IdEmpresa);

        $url = 'https://api-sire.sunat.gob.pe/v1/contribuyente/migeigv/libros/rvierce/gestionprocesosmasivos/web/masivo/consultaestadotickets?perIni='.$solicitud->periodo.'&perFin='.$solicitud->periodo.'&page=1&perPage=20&numTicket='.$solicitud->numTicket;

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'Authorization: Bearer '.$token
            ),
        ));

        $response = curl_exec($curl);
        $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        
        $respuesta = json_decode($response, true);

        if (!isset($respuesta['registros'][0]['archivoReporte'])) {
            \Log::error('Error al consultar ticket de SUNAT. Ticket ID: ' . $solicitud->numTicket . ' HTTP Code: ' . $http_code . ' Response: ' . $response);
            return Redirect::to('/sire/sunat')->with('error', 'Ticket en proceso o error de SUNAT.');
        }
        
        if(!is_null($respuesta['registros'][0]['archivoReporte'])){
            $nomArchivoReporte = $respuesta['registros'][0]['archivoReporte'][0]['nomArchivoReporte'];
            $nomArchivoContenido = $respuesta['registros'][0]['archivoReporte'][0]['nomArchivoContenido'];

            DB::table('solicitud_sire')->where('solsire_id',$solsire_id)->update([
                'archReporte' => $nomArchivoReporte,
                'archContenido' => $nomArchivoContenido,
                'tipArch' => '1'
            ]);

            $archivo = self::descargarArchivo($token, $nomArchivoReporte);

            $zip = new \ZipArchive;
            if($zip->open(public_path().'/sire/zip/'.$archivo) === TRUE) {
                $zip->extractTo(public_path().'/sire/excel/');
                $zip->close();
            }
        
            if(file_exists(public_path().'/sire/excel/'.$nomArchivoContenido)){
                self::cargar_registros($solicitud->solsire_id);
                return Redirect::to('/sire/sunat');
            } else {
                return Redirect::to('/sire/sunat');
            }
        }
    }

    public function descargarArchivo($token, $archivoReporte){
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api-sire.sunat.gob.pe/v1/contribuyente/migeigv/libros/rvierce/gestionprocesosmasivos/web/masivo/archivoreporte?nomArchivoReporte='.$archivoReporte.'&codTipoArchivoReporte=01&codLibro=140000',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Accept: application/json',
                'Authorization: Bearer '.$token
            ),
        ));

        $response = curl_exec($curl);
        $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($http_code != 200) {
            \Log::error('Error al descargar archivo de SUNAT. HTTP Code: ' . $http_code . ' Response: ' . $response);
            return null;
        }

        file_put_contents(public_path().'/sire/zip/'.$archivoReporte, $response);
        return $archivoReporte;
    }

    public function descargarZip($id){
        $solicitud = DB::table('solicitud_sire')->where('solsire_id',$id)->first();

        if(file_exists(public_path().'/sire/zip/'.$solicitud->archReporte)){
            return response()->download(public_path().'/sire/zip/'.$solicitud->archReporte);
        }else{
            return Redirect::to('/sire/sunat');
        }
    }

    public function descargarCsv($id){
        $solicitud = DB::table('solicitud_sire')->where('solsire_id',$id)->first();

        if(file_exists(public_path().'/sire/excel/'.$solicitud->archContenido)){
            return response()->download(public_path().'/sire/excel/'.$solicitud->archContenido);
        }else{
            return Redirect::to('/sire/sunat');
        }
    }

    public function cargar_registros($id)
    {
        $solicitud = DB::table('solicitud_sire')->where('solsire_id',$id)->first();
        $ruta_archivo = public_path().'/sire/excel/'.$solicitud->archContenido;
        
        if (!file_exists($ruta_archivo)) {
            return 'no_existe';
        }

        if($solicitud->tipo=='1'){
            DB::table('sire_ventas')->where('solsire_id',$id)->delete();
        }elseif($solicitud->tipo=='2'){
            DB::table('sire_compras')->where('solsire_id',$id)->delete();
        }

        if (($archivo = fopen($ruta_archivo, "r")) !== FALSE) {
            $i = 0;
            while (($cells = fgetcsv($archivo, 0, ",")) !== FALSE) {
                $i++;
                if($i >= 2){
                    if($solicitud->tipo == '1'){
                        $ventas = new SireVentas;
                        $ventas->ruc = $cells[0] ?? '';
                        $ventas->razon_social = $cells[12] ?? ''; // Corregido el índice duplicado
                        $ventas->periodo = $cells[2] ?? '';
                        $ventas->car_sunat = $cells[3] ?? '';
                        $ventas->fecha_emision = $cells[4] ?? '';
                        $ventas->fecha_vencimiento = $cells[5] ?? '';
                        $ventas->tipo_doc = $cells[6] ?? '';
                        $ventas->serie = $cells[7] ?? '';
                        $ventas->numero_inicial = $cells[8] ?? '';
                        $ventas->numero_final = $cells[9] ?? '';
                        $ventas->tipo_doc_identidad = $cells[10] ?? '';
                        $ventas->nro_doc_identidad = $cells[11] ?? '';
                        $ventas->cliente = $cells[12] ?? '';
                        $ventas->valor_facturacion = $cells[13] ?? '0.00';
                        $ventas->valor_gravada = $cells[14] ?? '0.00';
                        $ventas->descuento_BI = $cells[15] ?? '0.00';
                        $ventas->igv_ipm = $cells[16] ?? '0.00';
                        $ventas->dscto_igv_ipm = $cells[17] ?? '0.00';
                        $ventas->mto_exonerado = $cells[18] ?? '0.00';
                        $ventas->mto_inafecto = $cells[19] ?? '0.00';
                        $ventas->isc = $cells[20] ?? '0.00';
                        $ventas->bi_grav_ivap = $cells[21] ?? '0.00';
                        $ventas->ivap = $cells[22] ?? '0.00';
                        $ventas->icbper = $cells[23] ?? '0.00';
                        $ventas->otros_tributos = $cells[24] ?? '0.00';
                        $ventas->total_cp = $cells[25] ?? '0.00';
                        $ventas->moneda = $cells[26] ?? '';
                        $ventas->tipo_cambio = $cells[27] ?? '0.00';
                        $ventas->fecha_emision_doc_mod = $cells[28] ?? '';
                        $ventas->tipo_cp_mod = $cells[29] ?? '';
                        $ventas->serie_cp_mod = $cells[30] ?? '';
                        $ventas->nro_cp_mod = $cells[31] ?? '';
                        $ventas->id_proy_ope_atrib = $cells[32] ?? '';
                        $ventas->tipo_nota = $cells[33] ?? '';
                        $ventas->est_comp = $cells[34] ?? '';
                        $ventas->valor_fob_emb = $cells[35] ?? '0.00';
                        $ventas->valor_op_grat = $cells[36] ?? '0.00';
                        $ventas->tipo_operacion = $cells[37] ?? '';
                        $ventas->dam_cp = $cells[38] ?? '';
                        $ventas->clu = $cells[39] ?? '';
                        $ventas->solsire_id = $id;
                        $ventas->save();

                    } elseif($solicitud->tipo == '2'){
                        $compras = new SireCompras;
                        $compras->ruc = $cells[0] ?? '';
                        $compras->razon_social = $cells[1] ?? '';
                        $compras->periodo = $cells[2] ?? '';
                        $compras->car_sunat = $cells[3] ?? '';
                        $compras->fecha_emision = $cells[4] ?? '';
                        $compras->fecha_vencimiento = $cells[5] ?? '';
                        $compras->tipo_doc = $cells[6] ?? '';
                        $compras->serie = $cells[7] ?? '';
                        $compras->ano = $cells[8] ?? '';
                        $compras->numero_inicial = $cells[9] ?? '';
                        $compras->numero_final = $cells[10] ?? '';
                        $compras->tipo_doc_identidad = $cells[11] ?? '';
                        $compras->nro_doc_identidad = $cells[12] ?? '';
                        $compras->cliente = $cells[13] ?? '';
                        $compras->bi_grav_dg = $cells[14] ?? '0.00';
                        $compras->igv_ipm_dg = $cells[15] ?? '0.00';
                        $compras->bi_grav_dgng = $cells[16] ?? '0.00';
                        $compras->igv_ipm_dgng = $cells[17] ?? '0.00';
                        $compras->bi_grav_dng = $cells[18] ?? '0.00';
                        $compras->igv_ipm_dng = $cells[19] ?? '0.00';
                        $compras->valor_adq_ng = $cells[20] ?? '0.00';
                        $compras->isc = $cells[21] ?? '0.00';
                        $compras->icbper = $cells[22] ?? '0.00';
                        $compras->otros_tributos = $cells[23] ?? '0.00';
                        $compras->total_cp = $cells[24] ?? '0.00';
                        $compras->moneda = $cells[25] ?? '';
                        $compras->tipo_cambio = $cells[26] ?? '0.00';
                        $compras->fecha_emision_doc_mod = $cells[27] ?? '';
                        $compras->tipo_cp_mod = $cells[28] ?? '';
                        $compras->serie_cp_mod = $cells[29] ?? '';
                        $compras->com_dam_dsi = $cells[30] ?? '';
                        $compras->nro_cp_mod = $cells[31] ?? '';
                        $compras->clas_bss_sss = $cells[32] ?? '';
                        $compras->id_proy_ope_atrib = $cells[33] ?? '';
                        $compras->porc_part = $cells[34] ?? '0.00';
                        $compras->imb = $cells[35] ?? '0.00';
                        $compras->car_orig = $cells[36] ?? '';
                        $compras->detraccion = $cells[37] ?? '';
                        $compras->tipo_nota = $cells[38] ?? '';
                        $compras->est_comp = $cells[39] ?? '';
                        $compras->incal = $cells[40] ?? '';
                        $compras->clu1 = $cells[41] ?? '';
                        $compras->clu2 = $cells[42] ?? '';
                        $compras->clu3 = $cells[43] ?? '';
                        $compras->clu4 = $cells[44] ?? '';
                        $compras->clu5 = $cells[45] ?? '';
                        $compras->clu6 = $cells[46] ?? '';
                        $compras->clu7 = $cells[47] ?? '';
                        $compras->clu8 = $cells[48] ?? '';
                        $compras->clu9 = $cells[49] ?? '';
                        $compras->clu10 = $cells[50] ?? '';
                        $compras->clu11 = $cells[51] ?? '';
                        $compras->clu12 = $cells[52] ?? '';
                        $compras->clu13 = $cells[53] ?? '';
                        $compras->clu14 = $cells[54] ?? '';
                        $compras->clu15 = $cells[55] ?? '';
                        $compras->clu16 = $cells[56] ?? '';
                        $compras->clu17 = $cells[57] ?? '';
                        $compras->clu18 = $cells[58] ?? '';
                        $compras->clu19 = $cells[59] ?? '';
                        $compras->clu20 = $cells[60] ?? '';
                        $compras->clu21 = $cells[61] ?? '';
                        $compras->clu22 = $cells[62] ?? '';
                        $compras->clu23 = $cells[63] ?? '';
                        $compras->clu24 = $cells[64] ?? '';
                        $compras->clu25 = $cells[65] ?? '';
                        $compras->clu26 = $cells[66] ?? '';
                        $compras->clu27 = $cells[67] ?? '';
                        $compras->clu28 = $cells[68] ?? '';
                        $compras->clu29 = $cells[69] ?? '';
                        $compras->clu30 = $cells[70] ?? '';
                        $compras->clu31 = $cells[71] ?? '';
                        $compras->clu32 = $cells[72] ?? '';
                        $compras->clu33 = $cells[73] ?? '';
                        $compras->clu34 = $cells[74] ?? '';
                        $compras->clu35 = $cells[75] ?? '';
                        $compras->clu36 = $cells[76] ?? '';
                        $compras->clu37 = $cells[77] ?? '';
                        $compras->clu38 = $cells[78] ?? '';
                        $compras->clu39 = $cells[79] ?? '';
                        $compras->solsire_id = $id;
                        $compras->save();
                    }
                }
            }
            fclose($archivo);
        }
        return 'cargado';
    }

    public function ver_registros($id)
    {
        // Simplificado: Llama a la función principal para no repetir código
        self::cargar_registros($id);
        return Redirect::to('/sire/sunat');
    }

    public function generar_ventas_excel($id){
        $ventas = SireVentas::where('solsire_id',$id)->get();
        
        if(count($ventas)>0){
            $solicitud = SolicitudSire::findOrFail($id);
            $data_emp = Empresa::findOrFail($solicitud->IdEmpresa);
            
            Excel::create('Sire_Ventas', function($excel) use ($ventas,$data_emp) {
                $excel->sheet('Sire_Ventas', function($sheet) use ($ventas,$data_emp) {
                    $sheet->loadView('empresas.reportes.sire.formatos.sire_ventas',compact('ventas','data_emp'));
                });
            })->export('xlsx'); 

        }else{
            return Redirect::to('/sire/sunat');
        }
    }

    public function generar_compras_excel($id){
        $compras = SireCompras::where('solsire_id',$id)->get();

        if(count($compras)>0){
            $solicitud = SolicitudSire::findOrFail($id);
            $data_emp = Empresa::findOrFail($solicitud->IdEmpresa);
        
            Excel::create('Sire_Compras', function($excel) use ($compras,$data_emp) {
                $excel->sheet('Sire_Compras', function($sheet) use ($compras,$data_emp) {
                    $sheet->loadView('empresas.reportes.sire.formatos.sire_compras',compact('compras','data_emp'));
                });
            })->export('xlsx'); 

        }else{
            return Redirect::to('/sire/sunat');
        }
    }

    public function generar_ventas_concar($id){
        $ventas = SireVentas::where('solsire_id',$id)->get();
        $configuracion = DB::table('configuracion_concar')->first();

        if(count($ventas)>0){
            $solicitud = SolicitudSire::findOrFail($id);
            $data_emp = Empresa::findOrFail($solicitud->IdEmpresa);
            
            Excel::create('Sire_Ventas', function($excel) use ($ventas,$data_emp,$configuracion) {
                $excel->sheet('Sire_Ventas', function($sheet) use ($ventas,$data_emp,$configuracion) {
                    $sheet->loadView('empresas.reportes.sire.formatos.sire_concar_ventas',compact('ventas','data_emp','configuracion'));
                });
            })->export('xlsx'); 

        }else{
            return Redirect::to('/sire/sunat');
        }
    }

    public function generar_compras_concar($id){
        $compras = SireCompras::where('solsire_id',$id)->get();
        $configuracion = DB::table('configuracion_concar')->first();
        
        if(count($compras)>0){
            $solicitud = SolicitudSire::findOrFail($id);
            $data_emp = Empresa::findOrFail($solicitud->IdEmpresa);
        
            Excel::create('Sire_Compras', function($excel) use ($compras,$data_emp,$configuracion) {
                $excel->sheet('Sire_Compras', function($sheet) use ($compras,$data_emp,$configuracion) {
                    $sheet->loadView('empresas.reportes.sire.formatos.sire_concar_compras',compact('compras','data_emp','configuracion'));
                });
            })->export('xlsx'); 

        }else{
            return Redirect::to('/sire/sunat');
        }
    }
}