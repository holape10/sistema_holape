<?php

namespace MasterSoft\Modelos;

use Illuminate\Database\Eloquent\Model;
use MasterSoft\cpe_cabecera;
use DB;


class ReportesVentas extends Model
{
    
    private $fec_ini;
    private $fec_fin;
    private $ven_id;
    private $caj_id;
    private $suc_id;
    private $cli_id;
    
   
    //OBTIENE VENTAS TOTALES - BOLETAS - FACTURAS - NOTAS DE VENTAS

    public function obtenerVenta($suc_id,$fec_ini,$fec_fin,$cli_id,$caj_id,$ven_id){

        

        $registros = cpe_cabecera::select('ccacodsun','cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','tdides as documentoidentidad','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','monnom as moneda','ccaitv as total','cpe_cabecera.ccabaj as baja','ccaigv as igv','ccatvg as gravado','ccandi as numerodocumento','ccasunrescod','cpe_cabecera.tdocod as tipo_comprobante','cpe_cabecera.ccatexo as exonerado','cpe_cabecera.tot_icbper','totalcontado as contado','totalcredito as credito','totalcontado as contado','totalcredito as credito','cpe_cabecera.tdicod')
        ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
        ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
        ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
        ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
        ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
        ->whereNull('ccabaj')
        ->where(function ($query) use ($suc_id) {
            if(!empty($suc_id)){
                $query->where('cpe_cabecera.id_empresa_negocio',$suc_id);
            }
        }) 
        ->where(function ($query) use ($ven_id) {
            if(!empty($ven_id)){
                $query->where('cpe_cabecera.IdUsuario_ven',$ven_id);
            }
        }) 
        ->where(function ($query) use ($caj_id) {
            if(!empty($caj_id)){
                $query->where('cpe_cabecera.IdUsuario',$caj_id);
            }
        }) 
        ->where(function ($query) use ($cli_id) {
            if(!empty($cli_id)){
                $query->where('cpe_cabecera.clicod',$cli_id);
            }
        }) 
        ->where('cpe_cabecera.ccafem','>=',$fec_ini)
        ->where('cpe_cabecera.ccafem','<=',$fec_fin)
        ->where(function ($query) {
             $query->where('cpe_cabecera.tdocod','01')
                    ->orWhere('cpe_cabecera.tdocod','03')
                    ->orWhere('cpe_cabecera.tdocod','13')
                    ->orWhere('cpe_cabecera.tdocod','14')
                    ->orWhere('cpe_cabecera.tdocod','07')
                    ->orWhere('cpe_cabecera.tdocod','14')
                    ->orWhere('cpe_cabecera.tdocod','99');
        }) 
        ->orderBy('IdCpe_cabecera','desc')->get();


        return $registros;

    }


    public function obtenerVentaDetalle($suc_id,$fec_ini,$fec_fin,$cli_id,$caj_id,$ven_id){

        

        $registros = cpe_cabecera::select('ccacodsun','cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','tdides as documentoidentidad','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','monnom as moneda','ccaitv as total','cpe_cabecera.ccabaj as baja','ccaigv as igv','ccatvg as gravado','ccandi as numerodocumento','ccasunrescod','cpe_cabecera.tdocod as tipo_comprobante','cpe_cabecera.ccatexo as exonerado','cpe_cabecera.tot_icbper','totalcontado as contado','totalcredito as credito','totalcontado as contado','totalcredito as credito','cdedes as producto','cdecan as cantidad','cdepuni as precio_unitario','cdevve as total_item','procod as codigo','umecod as unidad_medida')
        ->leftjoin('cpe_detalle','cpe_detalle.IdCpe_cabecera','cpe_cabecera.IdCpe_cabecera')
        ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
        ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
        ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
        ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
        ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
        ->whereNull('ccabaj')
        ->where(function ($query) use ($suc_id) {
            if(!empty($suc_id)){
                $query->where('cpe_cabecera.id_empresa_negocio',$suc_id);
            }
        }) 
        ->where(function ($query) use ($ven_id) {
            if(!empty($ven_id)){
                $query->where('cpe_cabecera.IdUsuario_ven',$ven_id);
            }
        }) 
        ->where(function ($query) use ($caj_id) {
            if(!empty($caj_id)){
                $query->where('cpe_cabecera.IdUsuario',$caj_id);
            }
        }) 
        ->where(function ($query) use ($cli_id) {
            if(!empty($cli_id)){
                $query->where('cpe_cabecera.clicod',$cli_id);
            }
        }) 
        ->where('cpe_cabecera.ccafem','>=',$fec_ini)
        ->where('cpe_cabecera.ccafem','<=',$fec_fin)
        ->where(function ($query) {
            $query->where('cpe_cabecera.tdocod','01')
                    ->orWhere('cpe_cabecera.tdocod','03')
                    ->orWhere('cpe_cabecera.tdocod','13')
                    ->orWhere('cpe_cabecera.tdocod','14')
                    ->orWhere('cpe_cabecera.tdocod','07')
                    ->orWhere('cpe_cabecera.tdocod','99');
        }) 
        ->orderBy('IdCpe_cabecera','desc')->get();


        return $registros;

    }




    public function obtenerRentabilidad($suc_id,$fec_ini,$fec_fin,$cli_id,$caj_id,$ven_id){
 
        $registros = cpe_cabecera::select('ccacodsun','cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','tdides as documentoidentidad','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','monnom as moneda','ccaitv as total','cpe_cabecera.ccabaj as baja','ccaigv as igv','ccatvg as gravado','ccandi as numerodocumento','ccasunrescod','cpe_cabecera.tdocod as tipo_comprobante','cpe_cabecera.ccatexo as exonerado','cpe_cabecera.tot_icbper','totalcontado as contado','totalcredito as credito','totalcontado as contado','totalcredito as credito','cdedes as producto','cdecan as cantidad','cdepuni as precio_unitario','cdevve as total_item','procod as codigo','umecod as unidad_medida','cpe_detalle.costo','cpe_detalle.costo_promedio')
        ->leftjoin('cpe_detalle','cpe_detalle.IdCpe_cabecera','cpe_cabecera.IdCpe_cabecera')
        ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
        ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
        ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
        ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
        ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
        ->whereNull('ccabaj')
        ->where(function ($query) use ($suc_id) {
            if(!empty($suc_id)){
                $query->where('cpe_cabecera.id_empresa_negocio',$suc_id);
            }
        }) 
        ->where(function ($query) use ($ven_id) {
            if(!empty($ven_id)){
                $query->where('cpe_cabecera.IdUsuario_ven',$ven_id);
            }
        }) 
        ->where(function ($query) use ($caj_id) {
            if(!empty($caj_id)){
                $query->where('cpe_cabecera.IdUsuario',$caj_id);
            }
        }) 
        ->where(function ($query) use ($cli_id) {
            if(!empty($cli_id)){
                $query->where('cpe_cabecera.clicod',$cli_id);
            }
        }) 
        ->where('cpe_cabecera.ccafem','>=',$fec_ini)
        ->where('cpe_cabecera.ccafem','<=',$fec_fin)
        ->where(function ($query) {
            $query->where('cpe_cabecera.tdocod','01')
                    ->orWhere('cpe_cabecera.tdocod','03')
                    ->orWhere('cpe_cabecera.tdocod','13')
                    ->orWhere('cpe_cabecera.tdocod','14')
                    ->orWhere('cpe_cabecera.tdocod','07')
                    ->orWhere('cpe_cabecera.tdocod','99');
        }) 
        ->orderBy('IdCpe_cabecera','desc')->get();


        return $registros;

    }


    public function obtenerTodosLosRegistrosDeVenta($suc_id, $fec_ini, $fec_fin, $cli_id, $caj_id, $ven_id, $tip_rep)
    {
        $query = \DB::table('cpe_cabecera as cab')
                    ->select(
                        'cab.IdCpe_cabecera',
                        'cab.ccafem',           // Fecha
                        'cab.tdocod',           // Tipo documento
                        'cab.serdoc as serie',  // El campo real es serdoc
                        'cab.ccandi',           // Documento (DNI/RUC)
                        'cab.numdoc as correlativo', // El campo real es numdoc
                        'cab.ccabaj',           // Para saber si es anulado
                        'cab.ccanom as clirazonsocial', // El campo real es ccanom
                        'cab.ccandi as clidoc', // Usamos ccandi también para clidoc
                        'cab.ccaitv as total',  // El campo real es ccaitv
                        'cab.ccaigv as igv',    // El campo real es ccaigv
                        'cab.ccatvg as subtotal', // El campo real es ccatvg (gravado)
                        // Lógica para determinar forma de pago basada en tus otros métodos
                        \DB::raw("(CASE WHEN cab.totalcontado > 0 THEN 'CONTADO' ELSE 'CRÉDITO' END) as forma_pago")
                    )
                    ->whereBetween('cab.ccafem', [$fec_ini, $fec_fin]);

        // Filtros condicionales corregidos
        if (!empty($suc_id)) {
            $query->where('cab.id_empresa_negocio', $suc_id);
        }
        if (!empty($cli_id) && $cli_id != 'Todos') {
            $query->where('cab.clicod', $cli_id);
        }
        if (!empty($ven_id) && $ven_id != 'Todos') {
            $query->where('cab.IdUsuario_ven', $ven_id);
        }
        if (!empty($caj_id) && $caj_id != 'Todos') {
            $query->where('cab.IdUsuario', $caj_id);
        }

        // Opcional: Filtramos solo los tipos de documentos que te interesan para estos reportes
        $query->whereIn('cab.tdocod', ['01', '03', '07', '13', '14', '99']);

        return $query->get();
    }



      public function obtenerTotalContado($suc_id,$fec_ini,$fec_fin,$cli_id,$caj_id,$ven_id){

        

        $registros = cpe_cabecera::select('ccacodsun','cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','tdides as documentoidentidad','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','monnom as moneda','ccaitv as total','cpe_cabecera.ccabaj as baja','ccaigv as igv','ccatvg as gravado','ccandi as numerodocumento','ccasunrescod','cpe_cabecera.tdocod','cpe_cabecera.ccatexo as exonerado','cpe_cabecera.tot_icbper','totalcontado as contado','totalcredito as credito','cpe_cabecera.tdicod')
        ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
        ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
        ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
        ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
        ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
        ->whereNull('ccabaj')
        ->where(function ($query) use ($suc_id) {
            if(!empty($suc_id)){
                $query->where('cpe_cabecera.id_empresa_negocio',$suc_id);
            }
        }) 
        ->where(function ($query) use ($ven_id) {
            if(!empty($ven_id)){
                $query->where('cpe_cabecera.IdUsuario_ven',$ven_id);
            }
        }) 
        ->where(function ($query) use ($caj_id) {
            if(!empty($caj_id)){
                $query->where('cpe_cabecera.IdUsuario',$caj_id);
            }
        }) 
        ->where(function ($query) use ($cli_id) {
            if(!empty($cli_id)){
                $query->where('cpe_cabecera.clicod',$cli_id);
            }
        }) 
        ->where('cpe_cabecera.ccafem','>=',$fec_ini)
        ->where('cpe_cabecera.ccafem','<=',$fec_fin)
        ->where(function ($query) {
             $query->where('cpe_cabecera.tdocod','01')
                    ->orWhere('cpe_cabecera.tdocod','03')
                    ->orWhere('cpe_cabecera.tdocod','13')
                    ->orWhere('cpe_cabecera.tdocod','14');
        }) 
        ->sum('totalcontado');

        
        return $registros;

    }


    public function obtenerTotalCredito($suc_id,$fec_ini,$fec_fin,$cli_id,$caj_id,$ven_id){

        

        $registros = cpe_cabecera::select('ccacodsun','cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','tdides as documentoidentidad','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','monnom as moneda','ccaitv as total','cpe_cabecera.ccabaj as baja','ccaigv as igv','ccatvg as gravado','ccandi as numerodocumento','ccasunrescod','cpe_cabecera.tdocod','cpe_cabecera.ccatexo as exonerado','cpe_cabecera.tot_icbper')
        ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod','totalcontado as contado','totalcredito as credito','cpe_cabecera.tdicod')
        ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
        ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
        ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
        ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
        ->whereNull('ccabaj')
        ->where(function ($query) use ($suc_id) {
            if(!empty($suc_id)){
                $query->where('cpe_cabecera.id_empresa_negocio',$suc_id);
            }
        }) 
        ->where(function ($query) use ($ven_id) {
            if(!empty($ven_id)){
                $query->where('cpe_cabecera.IdUsuario_ven',$ven_id);
            }
        }) 
        ->where(function ($query) use ($caj_id) {
            if(!empty($caj_id)){
                $query->where('cpe_cabecera.IdUsuario',$caj_id);
            }
        }) 
        ->where(function ($query) use ($cli_id) {
            if(!empty($cli_id)){
                $query->where('cpe_cabecera.clicod',$cli_id);
            }
        }) 
        ->where('cpe_cabecera.ccafem','>=',$fec_ini)
        ->where('cpe_cabecera.ccafem','<=',$fec_fin)
        ->where(function ($query) {
             $query->where('cpe_cabecera.tdocod','01')
                    ->orWhere('cpe_cabecera.tdocod','03')
                    ->orWhere('cpe_cabecera.tdocod','13')
                    ->orWhere('cpe_cabecera.tdocod','14');
        }) 
        ->sum('totalcredito');

        
        return $registros;

    }


       public function obtenerTotalVentas($suc_id,$fec_ini,$fec_fin,$cli_id,$caj_id,$ven_id){

        

        $registros = cpe_cabecera::select('ccacodsun','cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','tdides as documentoidentidad','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','monnom as moneda','ccaitv as total','cpe_cabecera.ccabaj as baja','ccaigv as igv','ccatvg as gravado','ccandi as numerodocumento','ccasunrescod','cpe_cabecera.tdocod','cpe_cabecera.ccatexo as exonerado','cpe_cabecera.tot_icbper')
        ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod','totalcontado as contado','totalcredito as credito','cpe_cabecera.tdicod')
        ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
        ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
        ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
        ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
        ->whereNull('ccabaj')
        ->where(function ($query) use ($suc_id) {
            if(!empty($suc_id)){
                $query->where('cpe_cabecera.id_empresa_negocio',$suc_id);
            }
        }) 
        ->where(function ($query) use ($ven_id) {
            if(!empty($ven_id)){
                $query->where('cpe_cabecera.IdUsuario_ven',$ven_id);
            }
        }) 
        ->where(function ($query) use ($caj_id) {
            if(!empty($caj_id)){
                $query->where('cpe_cabecera.IdUsuario',$caj_id);
            }
        }) 
        ->where(function ($query) use ($cli_id) {
            if(!empty($cli_id)){
                $query->where('cpe_cabecera.clicod',$cli_id);
            }
        }) 
        ->where('cpe_cabecera.ccafem','>=',$fec_ini)
        ->where('cpe_cabecera.ccafem','<=',$fec_fin)
        ->where(function ($query) {
             $query->where('cpe_cabecera.tdocod','01')
                    ->orWhere('cpe_cabecera.tdocod','03')
                    ->orWhere('cpe_cabecera.tdocod','13')
                    ->orWhere('cpe_cabecera.tdocod','14');
        }) 
        ->sum('ccaitv');

        
        return $registros;

    }

    public function obtenerDatosReporteSunat($suc_id, $fec_ini, $fec_fin, $cli_id, $caj_id, $ven_id){

        $base_query = cpe_cabecera::select(
            'cpe_cabecera.ccandi',
            'cpe_cabecera.tdicod',
            'cpe_cabecera.tdocod',
            'cpe_cabecera.serdoc',
            'cpe_cabecera.numdoc',
            'cpe_cabecera.ccaitv as total',
            'cpe_cabecera.ccaigv as igv',
            'cpe_cabecera.ccafem as fecha',
            'cpe_cabecera.ccatvg as gravado',
            'cpe_cabecera.ccatexo as exonerado',
            'cpe_cabecera.tot_icbper',
            'cpe_cabecera.ccacodsun',
            'cpe_cabecera.ccandi as numerodocumento',
            'cpe_cabecera.tdicod as documentoidentidad',
            'cpe_cabecera.serdoc as serie',
            'cpe_cabecera.numdoc as numero',
            'cpe_cabecera.ccanom as cliente',
            'cpe_cabecera.IdCpe_cabecera' // Necesario si lo usas para vincular detalles
            // AÑADE AQUÍ CUALQUIER OTRA COLUMNA QUE NECESITES PARA MOSTRAR EN LA VISTA (ej. nombre de moneda, cliente, etc.)
        )
        // Asegúrate de incluir los JOINs que ya tenías en tus funciones individuales:
        ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
        ->leftjoin('tipo_documento as td','cpe_cabecera.tdocod','=','td.tdocod')
        ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
        ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')

        // Filtros obligatorios por rango de fechas y documentos válidos
        ->where('cpe_cabecera.ccafem','>=',$fec_ini)
        ->where('cpe_cabecera.ccafem','<=',$fec_fin)
        ->whereNull('ccabaj') // Solo documentos que no están dados de baja
        ->whereIn('cpe_cabecera.tdocod', ['01', '03', '07']) // Factura, Boleta, Nota Crédito

        // Aplicación de filtros opcionales (Sucursal, Vendedor, Cliente, Caja)
        ->where(function ($query) use ($suc_id) {
            if(!empty($suc_id)){
                $query->where('cpe_cabecera.id_empresa_negocio',$suc_id);
            }
        })
        ->where(function ($query) use ($ven_id) {
            if(!empty($ven_id)){
                $query->where('cpe_cabecera.IdUsuario_ven',$ven_id);
            }
        })
        ->where(function ($query) use ($caj_id) {
            if(!empty($caj_id)){
                $query->where('cpe_cabecera.IdUsuario',$caj_id);
            }
        })
        ->where(function ($query) use ($cli_id) {
            if(!empty($cli_id)){
                $query->where('cpe_cabecera.clicod',$cli_id);
            }
        })
        ->orderBy('cpe_cabecera.ccafem', 'asc') // Opcional: ordenar para la vista
        ->get(); // Ejecuta la consulta UNA SOLA VEZ

        return $base_query;
    }

     public function obtenerTotalVentasSunat($suc_id,$fec_ini,$fec_fin,$cli_id,$caj_id,$ven_id){

        $registros = cpe_cabecera::select('ccacodsun','cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','tdides as documentoidentidad','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','monnom as moneda','ccaitv as total','cpe_cabecera.ccabaj as baja','ccaigv as igv','ccatvg as gravado','ccandi as numerodocumento','ccasunrescod','cpe_cabecera.tdocod','cpe_cabecera.ccatexo as exonerado','cpe_cabecera.tot_icbper')
        ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod','totalcontado as contado','totalcredito as credito','cpe_cabecera.tdicod')
        ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
        ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
        ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
        ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
        ->whereNull('ccabaj')
        ->where(function ($query) use ($suc_id) {
            if(!empty($suc_id)){
                $query->where('cpe_cabecera.id_empresa_negocio',$suc_id);
            }
        }) 
        ->where(function ($query) use ($ven_id) {
            if(!empty($ven_id)){
                $query->where('cpe_cabecera.IdUsuario_ven',$ven_id);
            }
        }) 
        ->where(function ($query) use ($caj_id) {
            if(!empty($caj_id)){
                $query->where('cpe_cabecera.IdUsuario',$caj_id);
            }
        }) 
        ->where(function ($query) use ($cli_id) {
            if(!empty($cli_id)){
                $query->where('cpe_cabecera.clicod',$cli_id);
            }
        }) 
        ->where('cpe_cabecera.ccafem','>=',$fec_ini)
        ->where('cpe_cabecera.ccafem','<=',$fec_fin)
        ->where(function ($query) {
             $query->where('cpe_cabecera.tdocod','01')
                    ->orWhere('cpe_cabecera.tdocod','03');
        }) 
        ->sum('ccaitv');

        
        return $registros;

    }


  public function obtenerTotalVentasBoletasFacturas($suc_id,$fec_ini,$fec_fin,$cli_id,$caj_id,$ven_id){

        

        $registros = cpe_cabecera::select('ccacodsun','cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','tdides as documentoidentidad','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','monnom as moneda','ccaitv as total','cpe_cabecera.ccabaj as baja','ccaigv as igv','ccatvg as gravado','ccandi as numerodocumento','ccasunrescod','cpe_cabecera.tdocod','cpe_cabecera.ccatexo as exonerado','cpe_cabecera.tot_icbper')
        ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod','totalcontado as contado','totalcredito as credito','cpe_cabecera.tdicod')
        ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
        ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
        ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
        ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
        ->whereNull('ccabaj')
        ->where(function ($query) use ($suc_id) {
            if(!empty($suc_id)){
                $query->where('cpe_cabecera.id_empresa_negocio',$suc_id);
            }
        }) 
        ->where(function ($query) use ($ven_id) {
            if(!empty($ven_id)){
                $query->where('cpe_cabecera.IdUsuario_ven',$ven_id);
            }
        }) 
        ->where(function ($query) use ($caj_id) {
            if(!empty($caj_id)){
                $query->where('cpe_cabecera.IdUsuario',$caj_id);
            }
        }) 
        ->where(function ($query) use ($cli_id) {
            if(!empty($cli_id)){
                $query->where('cpe_cabecera.clicod',$cli_id);
            }
        }) 
        ->where('cpe_cabecera.ccafem','>=',$fec_ini)
        ->where('cpe_cabecera.ccafem','<=',$fec_fin)
        ->where(function ($query) {
             $query->where('cpe_cabecera.tdocod','01')
                    ->orWhere('cpe_cabecera.tdocod','03');
        }) 
        ->sum('ccaitv');

        
        return $registros;

    }


       public function obtenerTotalNotasVentas($suc_id,$fec_ini,$fec_fin,$cli_id,$caj_id,$ven_id){

        

        $registros = cpe_cabecera::select('ccacodsun','cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','tdides as documentoidentidad','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','monnom as moneda','ccaitv as total','cpe_cabecera.ccabaj as baja','ccaigv as igv','ccatvg as gravado','ccandi as numerodocumento','ccasunrescod','cpe_cabecera.tdocod','cpe_cabecera.ccatexo as exonerado','cpe_cabecera.tot_icbper')
        ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod','totalcontado as contado','totalcredito as credito','cpe_cabecera.tdicod')
        ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
        ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
        ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
        ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
        ->whereNull('ccabaj')
        ->where(function ($query) use ($suc_id) {
            if(!empty($suc_id)){
                $query->where('cpe_cabecera.id_empresa_negocio',$suc_id);
            }
        }) 
        ->where(function ($query) use ($ven_id) {
            if(!empty($ven_id)){
                $query->where('cpe_cabecera.IdUsuario_ven',$ven_id);
            }
        }) 
        ->where(function ($query) use ($caj_id) {
            if(!empty($caj_id)){
                $query->where('cpe_cabecera.IdUsuario',$caj_id);
            }
        }) 
        ->where(function ($query) use ($cli_id) {
            if(!empty($cli_id)){
                $query->where('cpe_cabecera.clicod',$cli_id);
            }
        }) 
        ->where('cpe_cabecera.ccafem','>=',$fec_ini)
        ->where('cpe_cabecera.ccafem','<=',$fec_fin)
        ->where(function ($query) {
             $query->Where('cpe_cabecera.tdocod','13');
        }) 
        ->sum('ccaitv');

        
        return $registros;

    }


      public function obtenerTotalValesConsumo($suc_id,$fec_ini,$fec_fin,$cli_id,$caj_id,$ven_id){

        

        $registros = cpe_cabecera::select('ccacodsun','cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','tdides as documentoidentidad','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','monnom as moneda','ccaitv as total','cpe_cabecera.ccabaj as baja','ccaigv as igv','ccatvg as gravado','ccandi as numerodocumento','ccasunrescod','cpe_cabecera.tdocod','cpe_cabecera.ccatexo as exonerado','cpe_cabecera.tot_icbper')
        ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod','totalcontado as contado','totalcredito as credito','cpe_cabecera.tdicod')
        ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
        ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
        ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
        ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
        ->whereNull('ccabaj')
        ->where(function ($query) use ($suc_id) {
            if(!empty($suc_id)){
                $query->where('cpe_cabecera.id_empresa_negocio',$suc_id);
            }
        }) 
        ->where(function ($query) use ($ven_id) {
            if(!empty($ven_id)){
                $query->where('cpe_cabecera.IdUsuario_ven',$ven_id);
            }
        }) 
        ->where(function ($query) use ($caj_id) {
            if(!empty($caj_id)){
                $query->where('cpe_cabecera.IdUsuario',$caj_id);
            }
        }) 
        ->where(function ($query) use ($cli_id) {
            if(!empty($cli_id)){
                $query->where('cpe_cabecera.clicod',$cli_id);
            }
        }) 
        ->where('cpe_cabecera.ccafem','>=',$fec_ini)
        ->where('cpe_cabecera.ccafem','<=',$fec_fin)
        ->where(function ($query) {
             $query->Where('cpe_cabecera.tdocod','99');
        }) 
        ->sum('ccaitv');

        
        return $registros;

    }



      public function obtenerIGVVentas($suc_id,$fec_ini,$fec_fin,$cli_id,$caj_id,$ven_id){

        

        $registros = cpe_cabecera::select('ccaigv')
        ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod','totalcontado as contado','totalcredito as credito','cpe_cabecera.tdicod')
        ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
        ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
        ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
        ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
        ->whereNull('ccabaj')
        ->where(function ($query) use ($suc_id) {
            if(!empty($suc_id)){
                $query->where('cpe_cabecera.id_empresa_negocio',$suc_id);
            }
        }) 
        ->where(function ($query) use ($ven_id) {
            if(!empty($ven_id)){
                $query->where('cpe_cabecera.IdUsuario_ven',$ven_id);
            }
        }) 
        ->where(function ($query) use ($caj_id) {
            if(!empty($caj_id)){
                $query->where('cpe_cabecera.IdUsuario',$caj_id);
            }
        }) 
        ->where(function ($query) use ($cli_id) {
            if(!empty($cli_id)){
                $query->where('cpe_cabecera.clicod',$cli_id);
            }
        }) 
        ->where('cpe_cabecera.ccafem','>=',$fec_ini)
        ->where('cpe_cabecera.ccafem','<=',$fec_fin)
        ->where(function ($query) {
             $query->where('cpe_cabecera.tdocod','01')
                    ->orWhere('cpe_cabecera.tdocod','03')
                    ->orWhere('cpe_cabecera.tdocod','13')
                    ->orWhere('cpe_cabecera.tdocod','14');
        }) 
        ->sum('ccaigv');

        
        return $registros;

    }

    public function obtenerIGVNotasCreditos($suc_id,$fec_ini,$fec_fin,$cli_id,$caj_id,$ven_id){

        

        $registros = cpe_cabecera::select('ccaigv')
        ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod','totalcontado as contado','totalcredito as credito','cpe_cabecera.tdicod')
        ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
        ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
        ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
        ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
        ->whereNull('ccabaj')
        ->where(function ($query) use ($suc_id) {
            if(!empty($suc_id)){
                $query->where('cpe_cabecera.id_empresa_negocio',$suc_id);
            }
        }) 
        ->where(function ($query) use ($ven_id) {
            if(!empty($ven_id)){
                $query->where('cpe_cabecera.IdUsuario_ven',$ven_id);
            }
        }) 
        ->where(function ($query) use ($caj_id) {
            if(!empty($caj_id)){
                $query->where('cpe_cabecera.IdUsuario',$caj_id);
            }
        }) 
        ->where(function ($query) use ($cli_id) {
            if(!empty($cli_id)){
                $query->where('cpe_cabecera.clicod',$cli_id);
            }
        }) 
        ->where('cpe_cabecera.ccafem','>=',$fec_ini)
        ->where('cpe_cabecera.ccafem','<=',$fec_fin)
        ->where(function ($query) {
             $query->where('cpe_cabecera.tdocod','07');
        }) 
        ->sum('ccaigv');

        
        return $registros;

    }



     public function obtenerTotalNotasCreditos($suc_id,$fec_ini,$fec_fin,$cli_id,$caj_id,$ven_id){

        

        $registros = cpe_cabecera::select('ccacodsun','cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','tdides as documentoidentidad','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','monnom as moneda','ccaitv as total','cpe_cabecera.ccabaj as baja','ccaigv as igv','ccatvg as gravado','ccandi as numerodocumento','ccasunrescod','cpe_cabecera.tdocod','cpe_cabecera.ccatexo as exonerado','cpe_cabecera.tot_icbper')
        ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod','totalcontado as contado','totalcredito as credito','cpe_cabecera.tdicod')
        ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
        ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
        ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
        ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
        ->whereNull('ccabaj')
        ->where(function ($query) use ($suc_id) {
            if(!empty($suc_id)){
                $query->where('cpe_cabecera.id_empresa_negocio',$suc_id);
            }
        }) 
        ->where(function ($query) use ($ven_id) {
            if(!empty($ven_id)){
                $query->where('cpe_cabecera.IdUsuario_ven',$ven_id);
            }
        }) 
        ->where(function ($query) use ($caj_id) {
            if(!empty($caj_id)){
                $query->where('cpe_cabecera.IdUsuario',$caj_id);
            }
        }) 
        ->where(function ($query) use ($cli_id) {
            if(!empty($cli_id)){
                $query->where('cpe_cabecera.clicod',$cli_id);
            }
        }) 
        ->where('cpe_cabecera.ccafem','>=',$fec_ini)
        ->where('cpe_cabecera.ccafem','<=',$fec_fin)
        ->where(function ($query) {
             $query->where('cpe_cabecera.tdocod','07');
        }) 
        ->sum('ccaitv');

        
        return $registros;

    }


    //OBTIENE VENTAS - BOLETAS - FACTURAS
    public function obtenerVentaSunat($suc_id,$fec_ini,$fec_fin,$cli_id,$caj_id,$ven_id){

        $registros = cpe_cabecera::select('ccacodsun','cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','tdides as documentoidentidad','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','monnom as moneda','ccaitv as total','cpe_cabecera.ccabaj as baja','ccaigv as igv','ccatvg as gravado','ccandi as numerodocumento','ccasunrescod','cpe_cabecera.tdocod','cpe_cabecera.ccatexo as exonerado','cpe_cabecera.tot_icbper','totalcontado as contado','totalcredito as credito','cpe_cabecera.tdicod')
        ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
        ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
        ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
        ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
        ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')         
         ->where(function ($query) use ($suc_id) {
            if(!empty($suc_id)){
                $query->where('cpe_cabecera.id_empresa_negocio',$suc_id);
            }
        }) 
        ->where(function ($query) use ($ven_id) {
            if(!empty($ven_id)){
                $query->where('cpe_cabecera.IdUsuario_ven',$ven_id);
            }
        }) 
        ->where(function ($query) use ($caj_id) {
            if(!empty($caj_id)){
                $query->where('cpe_cabecera.IdUsuario',$caj_id);
            }
        }) 
        ->where(function ($query) use ($cli_id) {
            if(!empty($cli_id)){
                $query->where('cpe_cabecera.clicod',$cli_id);
            }
        }) 
        ->where('cpe_cabecera.ccafem','>=',$fec_ini)
        ->where('cpe_cabecera.ccafem','<=',$fec_fin)
        ->where(function ($query) {
            $query->where('cpe_cabecera.tdocod','01')
            ->where('cpe_cabecera.tdocod','03');
        })    
        ->orderBy('IdCpe_cabecera','desc')->get();

        return $registros;

    }


    //VENTAS - FACTURAS
    public function obtenerFactura($suc_id,$fec_ini,$fec_fin,$cli_id,$caj_id,$ven_id){

        
        $registros = cpe_cabecera::select('ccacodsun','cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','tdides as documentoidentidad','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','monnom as moneda','ccaitv as total','cpe_cabecera.ccabaj as baja','ccaigv as igv','ccatvg as gravado','ccandi as numerodocumento','ccasunrescod','cpe_cabecera.tdocod','cpe_cabecera.ccatexo as exonerado','cpe_cabecera.tot_icbper','totalcontado as contado','totalcredito as credito','cpe_cabecera.tdicod')
        ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
        ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
        ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
        ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
        ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')        
        ->where(function ($query) use ($suc_id) {
            if(!empty($suc_id)){
                $query->where('cpe_cabecera.id_empresa_negocio',$suc_id);
            }
        }) 
        ->where(function ($query) use ($ven_id) {
            if(!empty($ven_id)){
                $query->where('cpe_cabecera.IdUsuario_ven',$ven_id);
            }
        }) 
        ->where(function ($query) use ($caj_id) {
            if(!empty($caj_id)){
                $query->where('cpe_cabecera.IdUsuario',$caj_id);
            }
        }) 
        ->where(function ($query) use ($cli_id) {
            if(!empty($cli_id)){
                $query->where('cpe_cabecera.clicod',$cli_id);
            }
        }) 
        ->where('cpe_cabecera.ccafem','>=',$fec_ini)
        ->where('cpe_cabecera.ccafem','<=',$fec_fin)
        ->where(function ($query) {
            $query->where('cpe_cabecera.tdocod','01');
        })    
        ->orderBy('IdCpe_cabecera','desc')->get();

        return $registros;

    }


    //VENTAS - BOLETAS
    public function obtenerBoleta($suc_id,$fec_ini,$fec_fin,$cli_id,$caj_id,$ven_id){

        $registros = cpe_cabecera::select('ccacodsun','cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','tdides as documentoidentidad','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','monnom as moneda','ccaitv as total','cpe_cabecera.ccabaj as baja','ccaigv as igv','ccatvg as gravado','ccandi as numerodocumento','ccasunrescod','cpe_cabecera.tdocod','cpe_cabecera.ccatexo as exonerado','cpe_cabecera.tot_icbper','totalcontado as contado','totalcredito as credito','cpe_cabecera.tdicod')
        ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
        ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
        ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
        ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
        ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
        ->where(function ($query) use ($suc_id) {
            if(!empty($suc_id)){
                $query->where('cpe_cabecera.id_empresa_negocio',$suc_id);
            }
        }) 
        ->where(function ($query) use ($ven_id) {
            if(!empty($ven_id)){
                $query->where('cpe_cabecera.IdUsuario_ven',$ven_id);
            }
        }) 
        ->where(function ($query) use ($caj_id) {
            if(!empty($caj_id)){
                $query->where('cpe_cabecera.IdUsuario',$caj_id);
            }
        }) 
        ->where(function ($query) use ($cli_id) {
            if(!empty($cli_id)){
                $query->where('cpe_cabecera.clicod',$cli_id);
            }
        }) 
        ->where('cpe_cabecera.ccafem','>=',$fec_ini)
        ->where('cpe_cabecera.ccafem','<=',$fec_fin)
        ->where(function ($query) {
            $query->where('cpe_cabecera.tdocod','03');
        })    
        ->orderBy('IdCpe_cabecera','desc')->get();

        return $registros;

    }

    //OBTENER NOTAS DE VENTAS
    public function obtenerNotaVenta($suc_id,$fec_ini,$fec_fin,$cli_id,$caj_id,$ven_id){

        $registros = cpe_cabecera::select('ccacodsun','cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','tdides as documentoidentidad','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','monnom as moneda','ccaitv as total','cpe_cabecera.ccabaj as baja','ccaigv as igv','ccatvg as gravado','ccandi as numerodocumento','ccasunrescod','cpe_cabecera.tdocod','cpe_cabecera.ccatexo as exonerado','cpe_cabecera.tot_icbper','totalcontado as contado','totalcredito as credito','cpe_cabecera.tdicod')
        ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
        ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
        ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
        ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
        ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
        ->whereNull('ccabaj')
         ->where(function ($query) use ($suc_id) {
            if(!empty($suc_id)){
                $query->where('cpe_cabecera.id_empresa_negocio',$suc_id);
            }
        }) 
        ->where(function ($query) use ($ven_id) {
            if(!empty($ven_id)){
                $query->where('cpe_cabecera.IdUsuario_ven',$ven_id);
            }
        }) 
        ->where(function ($query) use ($caj_id) {
            if(!empty($caj_id)){
                $query->where('cpe_cabecera.IdUsuario',$caj_id);
            }
        }) 
        ->where(function ($query) use ($cli_id) {
            if(!empty($cli_id)){
                $query->where('cpe_cabecera.clicod',$cli_id);
            }
        }) 
        ->where('cpe_cabecera.ccafem','>=',$fec_ini)
        ->where('cpe_cabecera.ccafem','<=',$fec_fin)
        ->where(function ($query) {
            $query->where('cpe_cabecera.tdocod','13');
        })    
        ->orderBy('IdCpe_cabecera','desc')->get();

        return $registros;

    }


    //OBTENER NOTAS DE CRÉDITOS
    public function obtenerNotaCredito($suc_id,$fec_ini,$fec_fin,$cli_id,$caj_id,$ven_id){

        $registros = cpe_cabecera::select('ccacodsun','cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','tdides as documentoidentidad','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','monnom as moneda','ccaitv as total','cpe_cabecera.ccabaj as baja','ccaigv as igv','ccatvg as gravado','ccandi as numerodocumento','ccasunrescod','cpe_cabecera.tdocod','cpe_cabecera.ccatexo as exonerado','cpe_cabecera.tot_icbper','totalcontado as contado','totalcredito as credito','cpe_cabecera.tdicod')
        ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
        ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
        ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
        ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
        ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
        ->where(function ($query) use ($suc_id) {
            if(!empty($suc_id)){
                $query->where('cpe_cabecera.id_empresa_negocio',$suc_id);
            }
        }) 
        ->where(function ($query) use ($ven_id) {
            if(!empty($ven_id)){
                $query->where('cpe_cabecera.IdUsuario_ven',$ven_id);
            }
        }) 
        ->where(function ($query) use ($caj_id) {
            if(!empty($caj_id)){
                $query->where('cpe_cabecera.IdUsuario',$caj_id);
            }
        }) 
        ->where(function ($query) use ($cli_id) {
            if(!empty($cli_id)){
                $query->where('cpe_cabecera.clicod',$cli_id);
            }
        }) 
        ->where('cpe_cabecera.ccafem','>=',$fec_ini)
        ->where('cpe_cabecera.ccafem','<=',$fec_fin)
        ->where(function ($query) {
            $query->where('cpe_cabecera.tdocod','07');
        })    
        ->orderBy('IdCpe_cabecera','desc')->get();

        return $registros;
        
    }


    public function obtenerResumenVentasProductos($suc_id,$fec_ini,$fec_fin,$cli_id,$caj_id,$ven_id,$IdProducto){

          $registros = cpe_cabecera::select('cpe_cabecera.ccafem as dia','cpe_detalle.procod as codigo','cdedes as producto',DB::RAW('sum(cpe_detalle.cdecan) as cantidad'),DB::RAW('AVG(cpe_detalle.cdepuni) as precio'),DB::RAW('sum(cpe_detalle.cdevve) as total'))
                ->leftjoin('cpe_detalle','cpe_detalle.IdCpe_cabecera','cpe_cabecera.IdCpe_cabecera')
                ->where('cpe_cabecera.ccafem','>=',$fec_ini)
                ->where('cpe_cabecera.ccafem','<=',$fec_fin)
                ->whereNull('cpe_cabecera.ccabaj')
                ->where('cdecan','>','0')
                ->where(function ($query) {
                    $query->Where('cpe_cabecera.tdocod','03')
                        ->orWhere('cpe_cabecera.tdocod','01')
                        ->orWhere('cpe_cabecera.tdocod','13');
   
                })
                ->whereNull('ccabaj')
                ->where(function ($query) use ($suc_id) {
                        if(!empty($suc_id)){
                            $query->where('cpe_cabecera.id_empresa_negocio',$suc_id);
                        }
                 }) 
                 ->where(function ($query) use ($IdProducto) {
                        if(!empty($IdProducto)){
                            $query->where('cpe_detalle.IdProducto',$IdProducto);
                        }
                 }) 
                ->where(function ($query) use ($ven_id) {
                        if(!empty($ven_id)){
                            $query->where('cpe_cabecera.IdUsuario_ven',$ven_id);
                        }
                }) 
                ->where(function ($query) use ($caj_id) {
                        if(!empty($caj_id)){
                            $query->where('cpe_cabecera.IdUsuario',$caj_id);
                        }
                }) 
                ->where(function ($query) use ($cli_id) {
                        if(!empty($cli_id)){
                            $query->where('cpe_cabecera.clicod',$cli_id);
                        }
                 }) 
                ->where('cpe_cabecera.ccafem','>=',$fec_ini)
                ->where('cpe_cabecera.ccafem','<=',$fec_fin)   
                ->groupby('cpe_cabecera.ccafem','cpe_detalle.procod','cdedes')
                ->orderby('cpe_cabecera.ccafem','asc')
                ->orderby('cpe_detalle.procod','asc')
                ->get();


            return $registros;

    }

    /*public function obtenerResumenVentasProductos($suc_id,$fec_ini,$fec_fin,$cli_id,$caj_id,$ven_id,$IdProducto){

          $registros = cpe_cabecera::select(DB::RAW('sum(cpe_detalle.cdecan*cpe_det_factor) as cantidad'),'cdedes',DB::RAW('sum(cpe_detalle.cdevve) as precio'),'cpe_detalle.procod','umecod')
                ->leftjoin('cpe_detalle','cpe_detalle.IdCpe_cabecera','cpe_cabecera.IdCpe_cabecera')
                ->where('cpe_cabecera.ccafem','>=',$fec_ini)
                ->where('cpe_cabecera.ccafem','<=',$fec_fin)
                ->whereNull('cpe_cabecera.ccabaj')
                ->where('cdecan','>','0')
                ->where(function ($query) {
                    $query->Where('cpe_cabecera.tdocod','03')
                        ->orWhere('cpe_cabecera.tdocod','01')
                        ->orWhere('cpe_cabecera.tdocod','13');
   
                })
                ->whereNull('ccabaj')
                ->where(function ($query) use ($suc_id) {
                        if(!empty($suc_id)){
                            $query->where('cpe_cabecera.id_empresa_negocio',$suc_id);
                        }
                 }) 
                 ->where(function ($query) use ($IdProducto) {
                        if(!empty($IdProducto)){
                            $query->where('cpe_detalle.IdProducto',$IdProducto);
                        }
                 }) 
                ->where(function ($query) use ($ven_id) {
                        if(!empty($ven_id)){
                            $query->where('cpe_cabecera.IdUsuario_ven',$ven_id);
                        }
                }) 
                ->where(function ($query) use ($caj_id) {
                        if(!empty($caj_id)){
                            $query->where('cpe_cabecera.IdUsuario',$caj_id);
                        }
                }) 
                ->where(function ($query) use ($cli_id) {
                        if(!empty($cli_id)){
                            $query->where('cpe_cabecera.clicod',$cli_id);
                        }
                 }) 
                ->where('cpe_cabecera.ccafem','>=',$fec_ini)
                ->where('cpe_cabecera.ccafem','<=',$fec_fin)   
                ->orderby('cdedes','desc')
                ->groupby('procod')
                ->get();


            return $registros;

    }*/

        public function obtenerResumenVentasBoletasFacturasProductos($suc_id,$fec_ini,$fec_fin,$cli_id,$caj_id,$ven_id){

          $registros = cpe_cabecera::select(DB::RAW('sum(cpe_detalle.cdecan*cpe_det_factor) as cantidad'),'cdedes',DB::RAW('sum(cpe_detalle.cdevve) as precio'),'cpe_detalle.procod','umecod')
                ->leftjoin('cpe_detalle','cpe_detalle.IdCpe_cabecera','cpe_cabecera.IdCpe_cabecera')
                ->where('cpe_cabecera.ccafem','>=',$fec_ini)
                ->where('cpe_cabecera.ccafem','<=',$fec_fin)
                ->whereNull('cpe_cabecera.ccabaj')
                ->where('cdecan','>','0')
                ->where(function ($query) {
                    $query->Where('cpe_cabecera.tdocod','03')
                        ->orWhere('cpe_cabecera.tdocod','01');
   
                })
                ->whereNull('ccabaj')
                ->where(function ($query) use ($suc_id) {
                        if(!empty($suc_id)){
                            $query->where('cpe_cabecera.id_empresa_negocio',$suc_id);
                        }
                 }) 
                ->where(function ($query) use ($ven_id) {
                        if(!empty($ven_id)){
                            $query->where('cpe_cabecera.IdUsuario_ven',$ven_id);
                        }
                }) 
                ->where(function ($query) use ($caj_id) {
                        if(!empty($caj_id)){
                            $query->where('cpe_cabecera.IdUsuario',$caj_id);
                        }
                }) 
                ->where(function ($query) use ($cli_id) {
                        if(!empty($cli_id)){
                            $query->where('cpe_cabecera.clicod',$cli_id);
                        }
                 }) 
                ->where('cpe_cabecera.ccafem','>=',$fec_ini)
                ->where('cpe_cabecera.ccafem','<=',$fec_fin)   
                ->orderby('cdedes','desc')
                ->groupby('procod')
                ->get();

            return $registros;

    }



    public function obtenerResumenNotasProductos($suc_id,$fec_ini,$fec_fin,$cli_id,$caj_id,$ven_id,$IdProducto){

          $registros = cpe_cabecera::select(DB::RAW('sum(cpe_detalle.cdecan*cpe_det_factor) as cantidad'),'cdedes',DB::RAW('sum(cpe_detalle.cdevve) as precio'),'cpe_detalle.procod','umecod')
                ->leftjoin('cpe_detalle','cpe_detalle.IdCpe_cabecera','cpe_cabecera.IdCpe_cabecera')
                ->where('cpe_cabecera.ccafem','>=',$fec_ini)
                ->where('cpe_cabecera.ccafem','<=',$fec_fin)
                ->whereNull('cpe_cabecera.ccabaj')
                ->where('cdecan','>','0')
                ->where(function ($query) {
                    $query->Where('cpe_cabecera.tdocod','13')
                        ->orWhere('cpe_cabecera.tdocod','14');
   
                })
                ->whereNull('ccabaj')
                ->where(function ($query) use ($suc_id) {
                        if(!empty($suc_id)){
                            $query->where('cpe_cabecera.id_empresa_negocio',$suc_id);
                        }
                 }) 
                 ->where(function ($query) use ($IdProducto) {
                        if(!empty($IdProducto)){
                            $query->where('cpe_detalle.IdProducto',$IdProducto);
                        }
                 }) 
                ->where(function ($query) use ($ven_id) {
                        if(!empty($ven_id)){
                            $query->where('cpe_cabecera.IdUsuario_ven',$ven_id);
                        }
                }) 
                ->where(function ($query) use ($caj_id) {
                        if(!empty($caj_id)){
                            $query->where('cpe_cabecera.IdUsuario',$caj_id);
                        }
                }) 
                ->where(function ($query) use ($cli_id) {
                        if(!empty($cli_id)){
                            $query->where('cpe_cabecera.clicod',$cli_id);
                        }
                 }) 
                ->where('cpe_cabecera.ccafem','>=',$fec_ini)
                ->where('cpe_cabecera.ccafem','<=',$fec_fin)   
                ->orderby('cdedes','desc')
                ->groupby('procod')
                ->get();


            return $registros;

    }



  /*  public function obtenerResumenNotasCreditosProductos($suc_id,$fec_ini,$fec_fin,$cli_id,$caj_id,$ven_id,$IdProducto){

          $registros = cpe_cabecera::select(DB::RAW('sum(cpe_detalle.cdecan*cpe_det_factor) as cantidad'),'cdedes',DB::RAW('sum(cpe_detalle.cdevve) as precio'),'cpe_detalle.procod','umecod')
                ->leftjoin('cpe_detalle','cpe_detalle.IdCpe_cabecera','cpe_cabecera.IdCpe_cabecera')
                ->where('cpe_cabecera.ccafem','>=',$fec_ini)
                ->where('cpe_cabecera.ccafem','<=',$fec_fin)
                ->whereNull('cpe_cabecera.ccabaj')
                ->where('cdecan','>','0')
                ->where(function ($query) {
                    $query->Where('cpe_cabecera.tdocod','07');
   
                })
                ->whereNull('ccabaj')
                ->where(function ($query) use ($suc_id) {
                        if(!empty($suc_id)){
                            $query->where('cpe_cabecera.id_empresa_negocio',$suc_id);
                        }
                 }) 
                 ->where(function ($query) use ($IdProducto) {
                        if(!empty($IdProducto)){
                            $query->where('cpe_detalle.IdProducto',$IdProducto);
                        }
                 }) 
                ->where(function ($query) use ($ven_id) {
                        if(!empty($ven_id)){
                            $query->where('cpe_cabecera.IdUsuario_ven',$ven_id);
                        }
                }) 
                ->where(function ($query) use ($caj_id) {
                        if(!empty($caj_id)){
                            $query->where('cpe_cabecera.IdUsuario',$caj_id);
                        }
                }) 
                ->where(function ($query) use ($cli_id) {
                        if(!empty($cli_id)){
                            $query->where('cpe_cabecera.clicod',$cli_id);
                        }
                 }) 
                ->where('cpe_cabecera.ccafem','>=',$fec_ini)
                ->where('cpe_cabecera.ccafem','<=',$fec_fin)   
                ->orderby('cdedes','desc')
                ->groupby('procod')
                ->get();


            return $registros;

    }
*/


    public function obtenerResumenNotasCreditosProductos($suc_id,$fec_ini,$fec_fin,$cli_id,$caj_id,$ven_id){

          $registros = cpe_cabecera::select(DB::RAW('sum(cpe_detalle.cdecan*cpe_det_factor) as cantidad'),'cdedes',DB::RAW('sum(cpe_detalle.cdevve) as precio'),'cpe_detalle.procod','umecod')
                ->leftjoin('cpe_detalle','cpe_detalle.IdCpe_cabecera','cpe_cabecera.IdCpe_cabecera')
                ->where('cpe_cabecera.ccafem','>=',$fec_ini)
                ->where('cpe_cabecera.ccafem','<=',$fec_fin)
                ->whereNull('cpe_cabecera.ccabaj')
                ->where('cdecan','>','0')
                ->where(function ($query) {
                    $query->Where('cpe_cabecera.tdocod','07');
   
                })
                ->whereNull('ccabaj')
                ->where(function ($query) use ($suc_id) {
                        if(!empty($suc_id)){
                            $query->where('cpe_cabecera.id_empresa_negocio',$suc_id);
                        }
                 }) 
                ->where(function ($query) use ($ven_id) {
                        if(!empty($ven_id)){
                            $query->where('cpe_cabecera.IdUsuario_ven',$ven_id);
                        }
                }) 
                ->where(function ($query) use ($caj_id) {
                        if(!empty($caj_id)){
                            $query->where('cpe_cabecera.IdUsuario',$caj_id);
                        }
                }) 
                ->where(function ($query) use ($cli_id) {
                        if(!empty($cli_id)){
                            $query->where('cpe_cabecera.clicod',$cli_id);
                        }
                 }) 
                ->where('cpe_cabecera.ccafem','>=',$fec_ini)
                ->where('cpe_cabecera.ccafem','<=',$fec_fin)   
                ->orderby('cdedes','desc')
                ->groupby('procod')
                ->get();

            return $registros;

    }

     public function obtenerResumenNotasVentasProductos($suc_id,$fec_ini,$fec_fin,$cli_id,$caj_id,$ven_id){

          $registros = cpe_cabecera::select(DB::RAW('sum(cpe_detalle.cdecan*cpe_det_factor) as cantidad'),'cdedes',DB::RAW('sum(cpe_detalle.cdevve) as precio'),'cpe_detalle.procod','umecod')
                ->leftjoin('cpe_detalle','cpe_detalle.IdCpe_cabecera','cpe_cabecera.IdCpe_cabecera')
                ->where('cpe_cabecera.ccafem','>=',$fec_ini)
                ->where('cpe_cabecera.ccafem','<=',$fec_fin)
                ->whereNull('cpe_cabecera.ccabaj')
                ->where('cdecan','>','0')
                ->where(function ($query) {
                    $query->Where('cpe_cabecera.tdocod','13');
   
                })
                ->whereNull('ccabaj')
                ->where(function ($query) use ($suc_id) {
                        if(!empty($suc_id)){
                            $query->where('cpe_cabecera.id_empresa_negocio',$suc_id);
                        }
                 }) 
                ->where(function ($query) use ($ven_id) {
                        if(!empty($ven_id)){
                            $query->where('cpe_cabecera.IdUsuario_ven',$ven_id);
                        }
                }) 
                ->where(function ($query) use ($caj_id) {
                        if(!empty($caj_id)){
                            $query->where('cpe_cabecera.IdUsuario',$caj_id);
                        }
                }) 
                ->where(function ($query) use ($cli_id) {
                        if(!empty($cli_id)){
                            $query->where('cpe_cabecera.clicod',$cli_id);
                        }
                 }) 
                ->where('cpe_cabecera.ccafem','>=',$fec_ini)
                ->where('cpe_cabecera.ccafem','<=',$fec_fin)   
                ->orderby('cdedes','desc')
                ->groupby('procod')
                ->get();

            return $registros;

    }

     public function obtenerResumenValesConsumoProductos($suc_id,$fec_ini,$fec_fin,$cli_id,$caj_id,$ven_id){

          $registros = cpe_cabecera::select(DB::RAW('sum(cpe_detalle.cdecan*cpe_det_factor) as cantidad'),'cdedes',DB::RAW('sum(cpe_detalle.cdevve) as precio'),'cpe_detalle.procod','umecod')
                ->leftjoin('cpe_detalle','cpe_detalle.IdCpe_cabecera','cpe_cabecera.IdCpe_cabecera')
                ->where('cpe_cabecera.ccafem','>=',$fec_ini)
                ->where('cpe_cabecera.ccafem','<=',$fec_fin)
                ->whereNull('cpe_cabecera.ccabaj')
                ->where('cdecan','>','0')
                ->where(function ($query) {
                    $query->Where('cpe_cabecera.tdocod','99');
   
                })
                ->whereNull('ccabaj')
                ->where(function ($query) use ($suc_id) {
                        if(!empty($suc_id)){
                            $query->where('cpe_cabecera.id_empresa_negocio',$suc_id);
                        }
                 }) 
                ->where(function ($query) use ($ven_id) {
                        if(!empty($ven_id)){
                            $query->where('cpe_cabecera.IdUsuario_ven',$ven_id);
                        }
                }) 
                ->where(function ($query) use ($caj_id) {
                        if(!empty($caj_id)){
                            $query->where('cpe_cabecera.IdUsuario',$caj_id);
                        }
                }) 
                ->where(function ($query) use ($cli_id) {
                        if(!empty($cli_id)){
                            $query->where('cpe_cabecera.clicod',$cli_id);
                        }
                 }) 
                ->where('cpe_cabecera.ccafem','>=',$fec_ini)
                ->where('cpe_cabecera.ccafem','<=',$fec_fin)   
                ->orderby('cdedes','desc')
                ->groupby('procod')
                ->get();

            return $registros;

    }


    public function obtenerRegistroVentasMigrar($suc_id,$fec_ini,$fec_fin,$cli_id,$caj_id,$ven_id){

      

        $registros = cpe_cabecera::select('ccacodsun','cpe_cabecera.IdCpe_cabecera','ccafem','cpe_cabecera.serdoc','cpe_cabecera.numdoc','cpe_cabecera.ccanom','monnom','ccaitv','cpe_cabecera.ccabaj','ccaigv','ccatvg','ccandi','ccasunrescod','cpe_cabecera.tdocod','cpe_cabecera.tdicod','cpe_cabecera.ccatexo','cpe_cabecera.tot_icbper','totalcontado','totalcredito','cdedes','cdecan','cdepuni','cdevve','cdepve','procod','umecod','estadopago','ccafve','tigcod','cdevun','cpe_det_factor','valor_unitario','precio_ref','icbper_det','cdeigv','mon_icbper_det','cpe_detalle.icbper as icbper_1','tipcambio','cpe_cabecera.direccion','costo','cpe_cabecera.cod_mov','cpe_cabecera.moncod','serie_ref','num_ref','tdocod_ref','fecha_ref','cod_suc')
        ->leftjoin('cpe_detalle','cpe_detalle.IdCpe_cabecera','cpe_cabecera.IdCpe_cabecera')
          ->leftjoin('empresa_negocios','empresa_negocios.id_empresa_negocio','cpe_cabecera.id_empresa_negocio')
        ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
        ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
        ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
        ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
        ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
        ->where(function ($query) use ($suc_id) {
            if(!empty($suc_id)){
                $query->where('cpe_cabecera.id_empresa_negocio',$suc_id);
            }
        }) 
        ->where(function ($query) use ($ven_id) {
            if(!empty($ven_id)){
                $query->where('cpe_cabecera.IdUsuario_ven',$ven_id);
            }
        }) 
        ->where(function ($query) use ($caj_id) {
            if(!empty($caj_id)){
                $query->where('cpe_cabecera.IdUsuario',$caj_id);
            }
        }) 
        ->where(function ($query) use ($cli_id) {
            if(!empty($cli_id)){
                $query->where('cpe_cabecera.clicod',$cli_id);
            }
        }) 
        ->where('cpe_cabecera.ccafem','>=',$fec_ini)
        ->where('cpe_cabecera.ccafem','<=',$fec_fin)
        ->where(function ($query) {
            $query->where('cpe_cabecera.tdocod','01')
                    ->orWhere('cpe_cabecera.tdocod','03')
                    ->orWhere('cpe_cabecera.tdocod','13')
                    ->orWhere('cpe_cabecera.tdocod','14')
                    ->orWhere('cpe_cabecera.tdocod','07')
                    ->orWhere('cpe_cabecera.tdocod','08')
                    ->orWhere('cpe_cabecera.tdocod','99');
        }) 
        ->orderBy('IdCpe_cabecera','desc')->get();


            return $registros;

    }

    public function obtenerResumenVentasVendedor($suc_id,$fec_ini,$fec_fin,$cli_id,$caj_id,$ven_id){


                $registros = cpe_cabecera::select('cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','monnom as moneda','cpe_cabecera.ccabaj as baja','ccasunrescod','tdides as documentoidentidad','ccandi as numerodocumento','users.name','users.apeusu',DB::RAW('sum(ccaitv) as total'))
                ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
                ->leftjoin('users','users.IdUsuario','cpe_cabecera.IdUsuario_ven')
                ->where('cpe_cabecera.ccafem','>=',$fec_ini)
                ->where('cpe_cabecera.ccafem','<=',$fec_fin)
                ->where(function ($query) {
                    $query->where('cpe_cabecera.tdocod','01')
                        ->orWhere('cpe_cabecera.tdocod','03')
                        ->orWhere('cpe_cabecera.tdocod','13')
                        ->orWhere('cpe_cabecera.tdocod','14');
                }) 
                ->where(function ($query) use ($suc_id){
                    if(!empty($suc_id)){
                        $query->where('cpe_cabecera.id_empresa_negocio',$suc_id);
                    }
                }) 
                ->where(function ($query) use ($ven_id){
                    if(!empty($ven_id)){
                        $query->where('cpe_cabecera.IdUsuario_ven',$ven_id);
                    }
                }) 
                ->where(function ($query) use ($caj_id){
                    if(!empty($caj_id)){
                        $query->where('cpe_cabecera.IdUsuario',$caj_id);
                    }
                }) 
                ->where(function ($query) use ($cli_id){
                    if(!empty($cli_id)){
                        $query->where('cpe_cabecera.clicod',$cli_id);
                    }
                }) 
                ->groupby('IdUsuario_ven')
                ->orderby('name')->get();


                 return $registros;

    }

     public function obtenerResumenVentasCliente($suc_id,$fec_ini,$fec_fin,$cli_id,$caj_id,$ven_id){


                $registros = cpe_cabecera::select('cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','monnom as moneda','cpe_cabecera.ccabaj as baja','ccasunrescod','tdides as documentoidentidad','ccandi as numerodocumento','users.name','users.apeusu',DB::RAW('sum(ccaitv) as total'))
                ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
                ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
                ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
                ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
                ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
                ->leftjoin('users','users.IdUsuario','cpe_cabecera.IdUsuario_ven')
                ->where('cpe_cabecera.ccafem','>=',$fec_ini)
                ->where('cpe_cabecera.ccafem','<=',$fec_fin)
                ->where(function ($query) {
                    $query->where('cpe_cabecera.tdocod','01')
                        ->orWhere('cpe_cabecera.tdocod','03')
                        ->orWhere('cpe_cabecera.tdocod','13')
                        ->orWhere('cpe_cabecera.tdocod','14');
                }) 
                ->where(function ($query) use ($suc_id){
                    if(!empty($suc_id)){
                        $query->where('cpe_cabecera.id_empresa_negocio',$suc_id);
                    }
                }) 
                ->where(function ($query) use ($ven_id){
                    if(!empty($ven_id)){
                        $query->where('cpe_cabecera.IdUsuario_ven',$ven_id);
                    }
                }) 
                ->where(function ($query) use ($caj_id){
                    if(!empty($caj_id)){
                        $query->where('cpe_cabecera.IdUsuario',$caj_id);
                    }
                }) 
                ->where(function ($query) use ($cli_id){
                    if(!empty($cli_id)){
                        $query->where('cpe_cabecera.clicod',$cli_id);
                    }
                }) 
                ->groupby('IdUsuario_ven')
                ->orderby('name')->get();


                 return $registros;

    }

    public function obtenerVentasDetallada($suc_id,$fec_ini,$fec_fin,$cli_id,$caj_id,$ven_id,$IdProducto){

           $registros = cpe_cabecera::select('ccafem','serdoc','numdoc','ccandi','ccanom','cpe_det_factor','cdecan as cantidad','cdedes','cdevve','cdepuni','cpe_detalle.procod','umecod','moncod')
                ->leftjoin('cpe_detalle','cpe_detalle.IdCpe_cabecera','cpe_cabecera.IdCpe_cabecera')
                ->where('cpe_cabecera.ccafem','>=',$fec_ini)
                ->where('cpe_cabecera.ccafem','<=',$fec_fin)
                ->whereNull('cpe_cabecera.ccabaj')
                ->where('cdecan','>','0')
                ->where(function ($query) {
                    $query->Where('cpe_cabecera.tdocod','03')
                        ->orWhere('cpe_cabecera.tdocod','01')
                        ->orWhere('cpe_cabecera.tdocod','13')
                        ->orWhere('cpe_cabecera.tdocod','14');
   
                })
                ->whereNull('ccabaj')
                ->where(function ($query) use ($suc_id) {
                        if(!empty($suc_id)){
                            $query->where('cpe_cabecera.id_empresa_negocio',$suc_id);
                        }
                 }) 
                 ->where(function ($query) use ($IdProducto) {
                        if(!empty($IdProducto)){
                            $query->where('cpe_detalle.IdProducto',$IdProducto);
                        }
                 }) 
                ->where(function ($query) use ($ven_id) {
                        if(!empty($ven_id)){
                            $query->where('cpe_cabecera.IdUsuario_ven',$ven_id);
                        }
                }) 
                ->where(function ($query) use ($caj_id) {
                        if(!empty($caj_id)){
                            $query->where('cpe_cabecera.IdUsuario',$caj_id);
                        }
                }) 
                ->where(function ($query) use ($cli_id) {
                        if(!empty($cli_id)){
                            $query->where('cpe_cabecera.clicod',$cli_id);
                        }
                 }) 
                ->where('cpe_cabecera.ccafem','>=',$fec_ini)
                ->where('cpe_cabecera.ccafem','<=',$fec_fin)   
                ->orderby('ccafem','asc')
                //->orderby('cdedes','desc')
                
                ->get();

                return $registros;


    }

    public function obtenerNotasCreditoDetallada($suc_id,$fec_ini,$fec_fin,$cli_id,$caj_id,$ven_id,$IdProducto){

           $registros = cpe_cabecera::select('ccafem','serdoc','numdoc','ccandi','ccanom','cpe_det_factor','cdecan as cantidad','cdedes','cdevve','cdepuni','cpe_detalle.procod','umecod','moncod')
                ->leftjoin('cpe_detalle','cpe_detalle.IdCpe_cabecera','cpe_cabecera.IdCpe_cabecera')
                ->where('cpe_cabecera.ccafem','>=',$fec_ini)
                ->where('cpe_cabecera.ccafem','<=',$fec_fin)
                ->whereNull('cpe_cabecera.ccabaj')
                ->where('cdecan','>','0')
                ->where(function ($query) {
                    $query->Where('cpe_cabecera.tdocod','07');
   
                })
                ->whereNull('ccabaj')
                ->where(function ($query) use ($suc_id) {
                        if(!empty($suc_id)){
                            $query->where('cpe_cabecera.id_empresa_negocio',$suc_id);
                        }
                 }) 
                 ->where(function ($query) use ($IdProducto) {
                        if(!empty($IdProducto)){
                            $query->where('cpe_detalle.IdProducto',$IdProducto);
                        }
                 }) 
                ->where(function ($query) use ($ven_id) {
                        if(!empty($ven_id)){
                            $query->where('cpe_cabecera.IdUsuario_ven',$ven_id);
                        }
                }) 
                ->where(function ($query) use ($caj_id) {
                        if(!empty($caj_id)){
                            $query->where('cpe_cabecera.IdUsuario',$caj_id);
                        }
                }) 
                ->where(function ($query) use ($cli_id) {
                        if(!empty($cli_id)){
                            $query->where('cpe_cabecera.clicod',$cli_id);
                        }
                 }) 
                ->where('cpe_cabecera.ccafem','>=',$fec_ini)
                ->where('cpe_cabecera.ccafem','<=',$fec_fin)   
                //->orderby('cdedes','desc')
                ->orderby('ccafem','asc')
                ->get();

                 return $registros;

                 
    }

     public function obtenerAnulacionesDetallada($suc_id,$fec_ini,$fec_fin,$cli_id,$caj_id,$ven_id,$IdProducto){

           $registros = cpe_cabecera::select('ccafem','serdoc','numdoc','ccandi','ccanom','cpe_det_factor','cdecan as cantidad','cdedes','cdevve','cdepuni','cpe_detalle.procod','umecod','moncod')
                ->leftjoin('cpe_detalle','cpe_detalle.IdCpe_cabecera','cpe_cabecera.IdCpe_cabecera')
                ->where('cpe_cabecera.ccafem','>=',$fec_ini)
                ->where('cpe_cabecera.ccafem','<=',$fec_fin)
                ->where('cdecan','>','0')
                ->where(function ($query) {
                    $query->Where('cpe_cabecera.tdocod','03')
                         ->orWhere('cpe_cabecera.tdocod','01')
                         ->orWhere('cpe_cabecera.tdocod','13')
                         ->orWhere('cpe_cabecera.tdocod','14');
   
                })
                ->whereNotNull('ccabaj')
                ->where(function ($query) use ($suc_id) {
                        if(!empty($suc_id)){
                            $query->where('cpe_cabecera.id_empresa_negocio',$suc_id);
                        }
                 }) 
                ->where(function ($query) use ($IdProducto) {
                        if(!empty($IdProducto)){
                            $query->where('cpe_detalle.IdProducto',$IdProducto);
                        }
                 }) 
                ->where(function ($query) use ($ven_id) {
                        if(!empty($ven_id)){
                            $query->where('cpe_cabecera.IdUsuario_ven',$ven_id);
                        }
                }) 
                ->where(function ($query) use ($caj_id) {
                        if(!empty($caj_id)){
                            $query->where('cpe_cabecera.IdUsuario',$caj_id);
                        }
                }) 
                ->where(function ($query) use ($cli_id) {
                        if(!empty($cli_id)){
                            $query->where('cpe_cabecera.clicod',$cli_id);
                        }
                 }) 
                ->where('cpe_cabecera.ccafem','>=',$fec_ini)
                ->where('cpe_cabecera.ccafem','<=',$fec_fin)   
                //->orderby('cdedes','desc')
                ->orderby('ccafem','asc')
                ->get();

                 return $registros;

                 
    }


     public function obtenerVentasAnuladas($suc_id,$fec_ini,$fec_fin,$cli_id,$caj_id,$ven_id){

        $registros = cpe_cabecera::select('ccacodsun','cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','tdides as documentoidentidad','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','monnom as moneda','ccaitv as total','cpe_cabecera.ccabaj as baja','ccaigv as igv','ccatvg as gravado','ccandi as numerodocumento','ccasunrescod','cpe_cabecera.tdocod as tipo_comprobante','cpe_cabecera.ccatexo as exonerado','cpe_cabecera.tot_icbper','totalcontado as contado','totalcredito as credito','totalcontado as contado','totalcredito as credito','cpe_cabecera.tdicod')
        ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
        ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
        ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
        ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
        ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
        ->whereNotNull('ccabaj')
        ->where(function ($query) use ($suc_id) {
            if(!empty($suc_id)){
                $query->where('cpe_cabecera.id_empresa_negocio',$suc_id);
            }
        }) 
        ->where(function ($query) use ($ven_id) {
            if(!empty($ven_id)){
                $query->where('cpe_cabecera.IdUsuario_ven',$ven_id);
            }
        }) 
        ->where(function ($query) use ($caj_id) {
            if(!empty($caj_id)){
                $query->where('cpe_cabecera.IdUsuario',$caj_id);
            }
        }) 
        ->where(function ($query) use ($cli_id) {
            if(!empty($cli_id)){
                $query->where('cpe_cabecera.clicod',$cli_id);
            }
        }) 
        ->where('cpe_cabecera.ccafem','>=',$fec_ini)
        ->where('cpe_cabecera.ccafem','<=',$fec_fin)
        ->where(function ($query) {
             $query->where('cpe_cabecera.tdocod','01')
                    ->orWhere('cpe_cabecera.tdocod','03')
                    ->orWhere('cpe_cabecera.tdocod','13')
                    ->orWhere('cpe_cabecera.tdocod','14')
                    ->orWhere('cpe_cabecera.tdocod','14')
                    ->orWhere('cpe_cabecera.tdocod','99');
        }) 
        ->orderBy('ccafem','desc')->get();


        return $registros;

    }

    public function obtenerRegistrosContable($suc_id,$fec_ini,$fec_fin,$cli_id,$caj_id,$ven_id){

       
       
      
         $registros = cpe_cabecera::select('tip_d_ref.des_doc as des_doc_ref','IdCpe_cabecera_ref','tdocod_ref','serie_ref','num_ref','ccafem_ref','tip_d.des_doc','tipcambio','ccacodsun','cpe_cabecera.IdCpe_cabecera','ccafem','tip_d.tdodes as comprobante','tdides as documentoidentidad','cpe_cabecera.serdoc','cpe_cabecera.numdoc','cpe_cabecera.ccanom as cliente','monnom as moneda','ccaitv','cpe_cabecera.ccabaj','ccaigv as igv','ccatvg as gravado','ccandi as numerodocumento','ccasunrescod','cpe_cabecera.tdocod','cpe_cabecera.ccatexo as exonerado','cpe_cabecera.tot_icbper','totalcontado as contado','totalcredito as credito','cpe_cabecera.tdicod','ccabaj','m.moncod','ccafve')

        ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
        ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
        ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
        ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
        ->leftjoin('tipo_documento as tip_d_ref','cpe_cabecera.tdocod_ref','=','tip_d_ref.tdocod')
        ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
         ->where(function ($query) use ($suc_id) {
            if(!empty($suc_id)){
                $query->where('cpe_cabecera.id_empresa_negocio',$suc_id);
            }
        }) 

        ->where('cpe_cabecera.ccafem','>=',$fec_ini)
        ->where('cpe_cabecera.ccafem','<=',$fec_fin)
        ->where(function ($query) {
            $query->where('cpe_cabecera.tdocod','01')
            ->orwhere('cpe_cabecera.tdocod','03')
            ->orwhere('cpe_cabecera.tdocod','07')
            ->orwhere('cpe_cabecera.tdocod','08');
        })
        ->orderBy('cpe_cabecera.tdocod','asc')
        ->orderBy('cpe_cabecera.serdoc','asc')
        ->orderBy('cpe_cabecera.numdoc','asc')
        ->get();    
        //->orderBy('IdCpe_cabecera','desc')->get();
      
        return $registros;

    }


    public function obtenerVentasMediosPagos($suc_id,$fec_ini,$fec_fin,$cli_id,$caj_id,$ven_id){

        $registros = cpe_cabecera::select('ccacodsun','cpe_cabecera.IdCpe_cabecera','ccafem as fecha','tdodes as comprobante','tdides as documentoidentidad','cpe_cabecera.serdoc as serie','cpe_cabecera.numdoc as numero','cpe_cabecera.ccanom as cliente','monnom as moneda','ccaitv as total','cpe_cabecera.ccabaj as baja','ccaigv as igv','ccatvg as gravado','ccandi as numerodocumento','ccasunrescod','cpe_cabecera.tdocod as tipo_comprobante','cpe_cabecera.ccatexo as exonerado','cpe_cabecera.tot_icbper','totalcontado as contado','totalcredito as credito','totalcontado as contado','totalcredito as credito','cpe_cabecera.tdicod','monto','nom_med_pag')
        ->leftjoin('venta_medio_pago','venta_medio_pago.IdCpe_cabecera','cpe_cabecera.IdCpe_cabecera')
        ->leftjoin('medios_pagos','medios_pagos.id_med_pag','venta_medio_pago.id_med_pag')
        ->leftjoin('moneda as m','cpe_cabecera.moncod','=','m.moncod')
        ->leftjoin('cliente as cl','cpe_cabecera.clicod','=','cl.clicod')
        ->leftjoin('empresa as e','cpe_cabecera.IdEmpresa','=','e.IdEmpresa')
        ->leftjoin('tipo_documento as tip_d','cpe_cabecera.tdocod','=','tip_d.tdocod')
        ->leftjoin('tipo_documento_identidad as tdi','cpe_cabecera.tdicod','=','tdi.tdicod')
           ->whereNull('ccabaj')
        ->where(function ($query) use ($suc_id) {
            if(!empty($suc_id)){
                $query->where('cpe_cabecera.id_empresa_negocio',$suc_id);
            }
        }) 
        ->where(function ($query) use ($ven_id) {
            if(!empty($ven_id)){
                $query->where('cpe_cabecera.IdUsuario_ven',$ven_id);
            }
        }) 
        ->where(function ($query) use ($caj_id) {
            if(!empty($caj_id)){
                $query->where('cpe_cabecera.IdUsuario',$caj_id);
            }
        }) 
        ->where(function ($query) use ($cli_id) {
            if(!empty($cli_id)){
                $query->where('cpe_cabecera.clicod',$cli_id);
            }
        }) 
        ->where('cpe_cabecera.ccafem','>=',$fec_ini)
        ->where('cpe_cabecera.ccafem','<=',$fec_fin)
        ->where(function ($query) {
             $query->where('cpe_cabecera.tdocod','01')
                    ->orWhere('cpe_cabecera.tdocod','03')
                    ->orWhere('cpe_cabecera.tdocod','13')
                    ->orWhere('cpe_cabecera.tdocod','14')
                    ->orWhere('cpe_cabecera.tdocod','14')
                    ->orWhere('cpe_cabecera.tdocod','99');
        }) 
        ->orderBy('ccafem','desc')
        ->orderBy('serdoc','asc')
        ->orderBy('numdoc','desc')
        ->get();


        return $registros;

    }

     public function obtenerResumenVentasMediosPagos($suc_id,$fec_ini,$fec_fin,$cli_id,$caj_id,$ven_id){

        $registros = cpe_cabecera::select('nom_med_pag',DB::RAW('sum(monto) as tot_med_pag'))
        ->leftjoin('venta_medio_pago','venta_medio_pago.IdCpe_cabecera','cpe_cabecera.IdCpe_cabecera')
        ->leftjoin('medios_pagos','medios_pagos.id_med_pag','venta_medio_pago.id_med_pag')
        ->where(function ($query) use ($suc_id) {
            if(!empty($suc_id)){
                $query->where('cpe_cabecera.id_empresa_negocio',$suc_id);
            }
        }) 
        ->where(function ($query) use ($ven_id) {
            if(!empty($ven_id)){
                $query->where('cpe_cabecera.IdUsuario_ven',$ven_id);
            }
        }) 
        ->where(function ($query) use ($caj_id) {
            if(!empty($caj_id)){
                $query->where('cpe_cabecera.IdUsuario',$caj_id);
            }
        }) 
        ->where(function ($query) use ($cli_id) {
            if(!empty($cli_id)){
                $query->where('cpe_cabecera.clicod',$cli_id);
            }
        }) 
        ->whereNull('ccabaj')
        ->where('cpe_cabecera.ccafem','>=',$fec_ini)
        ->where('cpe_cabecera.ccafem','<=',$fec_fin)
        ->where(function ($query) {
             $query->where('cpe_cabecera.tdocod','01')
                    ->orWhere('cpe_cabecera.tdocod','03')
                    ->orWhere('cpe_cabecera.tdocod','13')
                    ->orWhere('cpe_cabecera.tdocod','14')
                    ->orWhere('cpe_cabecera.tdocod','14')
                    ->orWhere('cpe_cabecera.tdocod','99');
        }) 
        ->groupby('venta_medio_pago.id_med_pag')
        ->orderBy('ccafem','desc')
        ->orderBy('serdoc','asc')
        ->orderBy('numdoc','desc')
        ->get();


        return $registros;

    }




}
