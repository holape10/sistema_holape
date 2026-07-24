<?php

namespace MasterSoft\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Excel;
use Carbon\Carbon;

class ConcarController extends Controller
{
    /**
     * Muestra la interfaz de exportación.
     */
    public function index()
    {
        // Traemos las empresas uniendo ambas tablas. 
        // Eliminamos filtros estrictos de estado para asegurar que listen correctamente.
        $empresas = DB::table('empresa_negocios')
            ->join('empresa', 'empresa_negocios.IdEmpresa', '=', 'empresa.IdEmpresa')
            ->select(
                'empresa_negocios.id_empresa_negocio',
                'empresa_negocios.cod_suc', // Tu campo para el código CONCAR (Ej: 0004)
                'empresa_negocios.nombre_comercial',
                'empresa.IdEmpresa as ruc',
                'empresa.NomEmpresa'
            )
            ->get();

        return view('empresas.concar.export', compact('empresas'));
    }


    public function exportarVentas(Request $request)
    {
        // Validación para Laravel 5.6
        $request->validate([
            'fecha_inicio'       => 'required|date',
            'fecha_fin'          => 'required|date',
            'sub_ventas'         => 'required',
            'id_empresa_negocio' => 'required',
            'ejercicio'          => 'required'
        ]);

        // Buscar el negocio seleccionado para obtener su 'cod_suc' (Ej: 0004)
        $negocio = DB::table('empresa_negocios')
            ->where('id_empresa_negocio', $request->id_empresa_negocio)
            ->first();

        if (!$negocio || empty($negocio->cod_suc)) {
            return back()->with('error', 'El negocio seleccionado no tiene configurado un código de sucursal (cod_suc) para CONCAR.');
        }

        // =========================================================================
        // TABLA TEMPORAL INTERMEDIA DEL CONCAR
        // =========================================================================
        $tablaTemporal = "CTMOVI40"; 
        // =========================================================================

        // Filtrar las ventas de tu BD MySQL usando ccafem
        $ventas = DB::table('cpe_cabecera')
                    ->join('cliente', 'cpe_cabecera.clicod', '=', 'cliente.clicod')
                    ->select(
                        'cpe_cabecera.*', 
                        'cliente.clinum as cliente_ruc_dni'
                    )
                    ->whereBetween('cpe_cabecera.ccafem', [$request->fecha_inicio, $request->fecha_fin])
                    ->where('cpe_cabecera.id_empresa_negocio', $request->id_empresa_negocio)
                    ->get();

        if ($ventas->isEmpty()) {
            return back()->with('error', 'No se encontraron comprobantes para esta sucursal en el rango de fechas seleccionado.');
        }

        try {
            DB::connection('sqlsrv_concar')->beginTransaction();

            // Iniciamos un contador secuencial simple para agrupar los comprobantes del lote temporal
            $contadorAsiento = 1;

            foreach ($ventas as $index => $v) {
                
                $fecha_emision = $v->ccafem; 
                $tipoDoc       = ($v->tdocod == '01') ? 'FT' : 'BV';
                $numDoc        = $v->serdoc . '-' . $v->numdoc;
                
                $total_venta   = round($v->ccaitv ?? 0, 2); 
                $total_igv     = round($v->ccaigv ?? 0, 2); 

                $fechaConcar = Carbon::parse($fecha_emision)->format('ymd');
                $fechaHoy    = Carbon::now()->format('ymd');
                
                $mesVenta       = Carbon::parse($fecha_emision)->format('m');
                // Formato de comprobante temporal: Mes + Correlativo (Ej: 050001)
                $nroComprobante = str_pad($mesVenta . str_pad($contadorAsiento, 4, '0', STR_PAD_LEFT), 6, '0', STR_PAD_LEFT);
                $glosa          = substr("VENTA " . $numDoc, 0, 30); 

                $cta_12 = !empty($v->cuenta12) ? $v->cuenta12 : '121201'; 
                $cta_40 = '401111';

                $secuencia_detalle = 1; 

                // -----------------------------------------------------------------
                // LÍNEA 1: INSERTAR LA CUENTA 12 (DEBE) EN CTMOVI40
                // -----------------------------------------------------------------
                DB::connection('sqlsrv_concar')->table($tablaTemporal)->insert([
                    'SUBDIA'   => $request->sub_ventas,
                    'COMPRO'   => $nroComprobante,
                    'SECUE'    => str_pad($secuencia_detalle++, 4, '0', STR_PAD_LEFT),
                    'FECHCOM'  => $fechaConcar,
                    'CUENTA'   => $cta_12,
                    'CODANE'   => $v->cliente_ruc_dni,
                    'CODMON'   => 'MN',
                    'DH'       => 'D', 
                    'IMPORT'   => $total_venta,
                    'TIPDOC'   => $tipoDoc,
                    'NUMDOC'   => $numDoc,
                    'FECDOC'   => $fechaConcar,
                    'GLOSA'    => $glosa,
                    'USER'     => 'HOLAP',
                    'DATE'     => $fechaHoy,
                    'ACTUALIZ' => 'N' // Indica al CONCAR que es un asiento nuevo pendiente de validar
                ]);

                // -----------------------------------------------------------------
                // LÍNEA 2: INSERTAR LA CUENTA 40 (HABER - IGV) EN CTMOVI40
                // -----------------------------------------------------------------
                if ($total_igv > 0) {
                    DB::connection('sqlsrv_concar')->table($tablaTemporal)->insert([
                        'SUBDIA'   => $request->sub_ventas,
                        'COMPRO'   => $nroComprobante,
                        'SECUE'    => str_pad($secuencia_detalle++, 4, '0', STR_PAD_LEFT),
                        'FECHCOM'  => $fechaConcar,
                        'CUENTA'   => $cta_40,
                        'CODANE'   => '', 
                        'CODMON'   => 'MN',
                        'DH'       => 'H', 
                        'IMPORT'   => $total_igv,
                        'TIPDOC'   => $tipoDoc,
                        'NUMDOC'   => $numDoc,
                        'FECDOC'   => $fechaConcar,
                        'GLOSA'    => $glosa,
                        'USER'     => 'HOLAP',
                        'DATE'     => $fechaHoy,
                        'ACTUALIZ' => 'N'
                    ]);
                }

                // -----------------------------------------------------------------
                // LÍNEAS 3+: INSERTAR LAS CUENTAS 70 AGRUPADAS (HABER) EN CTMOVI40
                // -----------------------------------------------------------------
                $detalles_productos = DB::table('cpe_detalle')
                    ->select(
                        'cta_contable_70', 
                        DB::raw('SUM(cdevve) as subtotal_cuenta') 
                    )
                    ->where('IdCpe_cabecera', $v->IdCpe_cabecera)
                    ->groupBy('cta_contable_70')
                    ->get();

                foreach ($detalles_productos as $det) {
                    $cta_70 = !empty($det->cta_contable_70) ? $det->cta_contable_70 : '7032111';

                    DB::connection('sqlsrv_concar')->table($tablaTemporal)->insert([
                        'SUBDIA'   => $request->sub_ventas,
                        'COMPRO'   => $nroComprobante,
                        'SECUE'    => str_pad($secuencia_detalle++, 4, '0', STR_PAD_LEFT),
                        'FECHCOM'  => $fechaConcar,
                        'CUENTA'   => $cta_70,
                        'CODANE'   => '', 
                        'CODMON'   => 'MN',
                        'DH'       => 'H', 
                        'IMPORT'   => round($det->subtotal_cuenta, 2),
                        'TIPDOC'   => $tipoDoc,
                        'NUMDOC'   => $numDoc,
                        'FECDOC'   => $fechaConcar,
                        'GLOSA'    => $glosa,
                        'USER'     => 'HOLAP',
                        'DATE'     => $fechaHoy,
                        'ACTUALIZ' => 'N'
                    ]);
                }

                $contadorAsiento++;
            }

            DB::connection('sqlsrv_concar')->commit();
            return back()->with('success', '¡Ventas enviadas con éxito a la tabla de importación CTMOVI40! Listo para que el contador ejecute la validación.');

        } catch (\Exception $e) {
            DB::connection('sqlsrv_concar')->rollBack();
            return back()->with('error', 'Error en la tabla intermedia del Concar: ' . $e->getMessage());
        }
    }

    public function exportarExcel(Request $request)
    {
        $request->validate([
            'fecha_inicio'       => 'required|date',
            'fecha_fin'          => 'required|date',
            'sub_ventas'         => 'required',
            'id_empresa_negocio' => 'required',
            'correlativo_inicial'=> 'required|integer|min:1'
        ]);

        $ventas = DB::table('cpe_cabecera')
                    ->join('cliente', 'cpe_cabecera.clicod', '=', 'cliente.clicod')
                    ->select('cpe_cabecera.*', 'cliente.clinum as cliente_ruc_dni')
                    ->whereBetween('cpe_cabecera.ccafem', [$request->fecha_inicio, $request->fecha_fin])
                    ->where('cpe_cabecera.id_empresa_negocio', $request->id_empresa_negocio)
                    ->get();

        if ($ventas->isEmpty()) {
            return back()->with('error', 'No se encontraron comprobantes en este rango de fechas para generar el Excel.');
        }

        // Armamos el arreglo completo
        $filas = [];
        
        // Fila 1: Cabeceras EXACTAS del archivo de soporte (41 columnas)
        $filas[] = [
            'Campo', 'Sub Diario', 'Número de Comprobante', 'Fecha de Comprobante', 'Código de Moneda', 
            'Glosa Principal', 'Tipo de Cambio', 'Tipo de Conversión', 'Flag de Conversión de Moneda', 
            'Fecha Tipo de Cambio', 'Cuenta Contable', 'Código de Anexo', 'Código de Centro de Costo', 
            'Debe / Haber', 'Importe Original', 'Importe en Dólares', 'Importe en Soles', 
            'Tipo de Documento', 'Número de Documento', 'Fecha de Documento', 'Fecha de Vencimiento', 
            'Código de Area', 'Glosa Detalle', 'Código de Anexo Auxiliar', 'Medio de Pago', 
            'Tipo de Documento de Referencia', 'Número de Documento Referencia', 'Fecha Documento Referencia', 
            'Nro Máq. Registradora Tipo Doc. Ref.', 'Base Imponible Documento Referencia', 'IGV Documento Provisión', 
            'Tipo Referencia en estado MQ', 'Número Serie Caja Registradora', 'Fecha de Operación', 
            'Tipo de Tasa', 'Tasa Detracción/Percepción', 'Importe Base Detracción/Percepción Dólares', 
            'Importe Base Detracción/Percepción Soles', "Tipo Cambio para 'F'", 'Importe de IGV sin derecho crédito fiscal',
            'Tasa IGV' // <- COLUMNA 41 OBLIGATORIA
        ];

        $contadorAsiento = (int) $request->correlativo_inicial;

        foreach ($ventas as $v) {
            $fecha_emision = Carbon::parse($v->ccafem)->format('d/m/Y'); 
            $tipoDoc       = ($v->tdocod == '01') ? 'FT' : (($v->tdocod == '03') ? 'BV' : 'NA');
            $numDoc        = $v->serdoc . '-' . $v->numdoc;
            
            $total_venta   = round($v->ccaitv ?? 0, 2); 
            $total_igv     = round($v->ccaigv ?? 0, 2); 

            $mesVenta       = Carbon::parse($v->ccafem)->format('m');
            
            $subDiarioText  = str_pad($request->sub_ventas, 2, '0', STR_PAD_LEFT); 
            $nroComprobante = str_pad($mesVenta . str_pad($contadorAsiento, 4, '0', STR_PAD_LEFT), 6, '0', STR_PAD_LEFT); 
            $glosa          = substr("VENTAS " . $numDoc, 0, 30); 

            $cta_12 = !empty($v->cuenta12) ? $v->cuenta12 : '121201'; 
            $cta_40 = '401111';

            // Ahora el molde tiene 41 columnas
            $filaBase = array_fill(0, 41, '');
            $filaBase[0]  = ''; // Columna A (Campo) queda vacía en los datos
            $filaBase[1]  = $subDiarioText;
            $filaBase[2]  = $nroComprobante;
            $filaBase[3]  = $fecha_emision;
            $filaBase[4]  = 'MN';
            $filaBase[5]  = $glosa;
            $filaBase[6]  = '0'; 
            $filaBase[7]  = 'V'; 
            $filaBase[8]  = 'S'; 
            $filaBase[9]  = $fecha_emision;
            $filaBase[15] = '0'; 
            $filaBase[17] = $tipoDoc;
            $filaBase[18] = $numDoc;
            $filaBase[19] = $fecha_emision;
            $filaBase[20] = $fecha_emision; 
            $filaBase[22] = $glosa; 
            $filaBase[29] = '0';
            $filaBase[30] = '0';

            // DEBE (Cuenta 12)
            $fila1 = $filaBase;
            $fila1[10] = $cta_12;
            $fila1[11] = $v->cliente_ruc_dni;
            $fila1[13] = 'D';
            $fila1[14] = $total_venta; 
            $fila1[16] = $total_venta; 
            $filas[] = $fila1;

            // HABER (Cuenta 40 - IGV)
            if ($total_igv > 0) {
                $fila2 = $filaBase;
                $fila2[10] = $cta_40;
                $fila2[13] = 'H';
                $fila2[14] = $total_igv;
                $fila2[16] = $total_igv;
                $filas[] = $fila2;
            }

            // HABER (Cuentas 70)
            $detalles_productos = DB::table('cpe_detalle')
                ->select('cta_contable_70', DB::raw('SUM(cdevve) as subtotal_cuenta'))
                ->where('IdCpe_cabecera', $v->IdCpe_cabecera)
                ->groupBy('cta_contable_70')
                ->get();

            foreach ($detalles_productos as $det) {
                $cta_70 = !empty($det->cta_contable_70) ? $det->cta_contable_70 : '7032111';
                $fila3 = $filaBase;
                $fila3[10] = $cta_70;
                $fila3[13] = 'H';
                $fila3[14] = round($det->subtotal_cuenta, 2);
                $fila3[16] = round($det->subtotal_cuenta, 2);
                $filas[] = $fila3;
            }

            $contadorAsiento++;
        }

        $nombreArchivo = "Reporte_Ventas_CONCAR_" . date('YmdHis');

        return Excel::create($nombreArchivo, function($excel) use ($filas) {
            $excel->sheet('Ventas', function($sheet) use ($filas) {
                
                // 1. Forzar formato Texto (@) en B y C para conservar los ceros
                $sheet->setColumnFormat([
                    'B' => '@',
                    'C' => '@'
                ]);

                // 2. Insertamos el array directamente en A1 (ahora que tiene la columna "Campo" todo cuadra)
                $sheet->fromArray($filas, null, 'A1', false, false);

                // 3. Estilo plano para que no se corten los textos (De la A hasta la AO = 41 columnas)
                $columnasConcar = [
                    'A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z',
                    'AA','AB','AC','AD','AE','AF','AG','AH','AI','AJ','AK','AL','AM','AN','AO'
                ];
                
                foreach ($columnasConcar as $col) {
                    $sheet->getStyle($col)->getAlignment()->setWrapText(false);
                }
            });
        })->download('xlsx');
    }

    public function exportarCobranzasExcel(Request $request)
    {
        $request->validate([
            'fecha_inicio'       => 'required|date',
            'fecha_fin'          => 'required|date',
            'sub_cobranzas'      => 'required', // Sub Diario 03
            'id_empresa_negocio' => 'required',
            'correlativo_inicial'=> 'required|integer|min:1'
        ]);

        // 1. Traemos todas las ventas ACTIVAS cruzadas con sus métodos de pago
        $pagos = DB::table('cpe_cabecera')
                    ->join('cliente', 'cpe_cabecera.clicod', '=', 'cliente.clicod')
                    ->join('venta_medio_pago', 'cpe_cabecera.IdCpe_cabecera', '=', 'venta_medio_pago.IdCpe_cabecera')
                    ->join('medios_pagos', 'venta_medio_pago.id_med_pag', '=', 'medios_pagos.id_med_pag')
                    ->select(
                        'cpe_cabecera.ccafem', 'cpe_cabecera.tdocod', 'cpe_cabecera.serdoc', 
                        'cpe_cabecera.numdoc', 'cpe_cabecera.cuenta12',
                        'cliente.clinum as cliente_ruc_dni',
                        'venta_medio_pago.monto', 'venta_medio_pago.id_med_pag'
                    )
                    ->whereBetween('cpe_cabecera.ccafem', [$request->fecha_inicio, $request->fecha_fin])
                    ->where('cpe_cabecera.id_empresa_negocio', $request->id_empresa_negocio)
                    ->whereNull('cpe_cabecera.ccabaj') // EXIGENCIA: Solo comprobantes activos
                    ->get();

        if ($pagos->isEmpty()) {
            return back()->with('error', 'No se encontraron pagos en este rango de fechas para generar el Excel de Cobranzas.');
        }

        // 2. AGRUPAMOS POR DÍA (La magia contable para el asiento diario)
        $pagosPorDia = $pagos->groupBy('ccafem');

        $filas = [];
        // Cabeceras exactas de 41 columnas
        $filas[] = [
            'Campo', 'Sub Diario', 'Número de Comprobante', 'Fecha de Comprobante', 'Código de Moneda', 
            'Glosa Principal', 'Tipo de Cambio', 'Tipo de Conversión', 'Flag de Conversión de Moneda', 
            'Fecha Tipo de Cambio', 'Cuenta Contable', 'Código de Anexo', 'Código de Centro de Costo', 
            'Debe / Haber', 'Importe Original', 'Importe en Dólares', 'Importe en Soles', 
            'Tipo de Documento', 'Número de Documento', 'Fecha de Documento', 'Fecha de Vencimiento', 
            'Código de Area', 'Glosa Detalle', 'Código de Anexo Auxiliar', 'Medio de Pago', 
            'Tipo de Documento de Referencia', 'Número de Documento Referencia', 'Fecha Documento Referencia', 
            'Nro Máq. Registradora Tipo Doc. Ref.', 'Base Imponible Documento Referencia', 'IGV Documento Provisión', 
            'Tipo Referencia en estado MQ', 'Número Serie Caja Registradora', 'Fecha de Operación', 
            'Tipo de Tasa', 'Tasa Detracción/Percepción', 'Importe Base Detracción/Percepción Dólares', 
            'Importe Base Detracción/Percepción Soles', "Tipo Cambio para 'F'", 'Importe de IGV sin derecho crédito fiscal',
            'Tasa IGV'
        ];

        $contadorAsiento = (int) $request->correlativo_inicial;

        // 3. PROCESAMOS DÍA POR DÍA
        foreach ($pagosPorDia as $fecha => $pagosDelDia) {
            
            $fecha_operacion = Carbon::parse($fecha)->format('d/m/Y');
            $mesVenta        = Carbon::parse($fecha)->format('m');
            
            $subDiarioText   = str_pad($request->sub_cobranzas, 2, '0', STR_PAD_LEFT); 
            $nroComprobante  = str_pad($mesVenta . str_pad($contadorAsiento, 4, '0', STR_PAD_LEFT), 6, '0', STR_PAD_LEFT); 
            $glosa_diaria    = substr("COBRANZAS DEL DIA " . $fecha_operacion, 0, 30); 

            $total_efectivo = 0;
            $total_izipay   = 0;

            // Separamos la plata: Efectivo vs Izipay/Otros
            foreach ($pagosDelDia as $p) {
                if ($p->id_med_pag == 1) { // <-- ASUMIENDO QUE ID 1 ES EFECTIVO
                    $total_efectivo += $p->monto;
                } else { // <-- CUALQUIER OTRO ID (Visa, Yape, Plin) VA A IZIPAY
                    $total_izipay += $p->monto;
                }
            }

            // Molde base de 41 columnas
            $filaBase = array_fill(0, 41, '');
            $filaBase[0]  = ''; // Campo
            $filaBase[1]  = $subDiarioText;
            $filaBase[2]  = $nroComprobante;
            $filaBase[3]  = $fecha_operacion;
            $filaBase[4]  = 'MN';
            $filaBase[5]  = $glosa_diaria;
            $filaBase[6]  = '0'; 
            $filaBase[7]  = 'V'; 
            $filaBase[8]  = 'S'; 
            $filaBase[9]  = $fecha_operacion;
            $filaBase[15] = '0'; 
            $filaBase[29] = '0';
            $filaBase[30] = '0';

            // ---------------------------------------------------------
            // LÍNEA 1: DEBE - EFECTIVO (101101)
            // ---------------------------------------------------------
            if ($total_efectivo > 0) {
                $filaEfe = $filaBase;
                $filaEfe[10] = '101101';
                $filaEfe[11] = ''; // Efectivo no suele requerir anexo
                $filaEfe[13] = 'D';
                $filaEfe[14] = round($total_efectivo, 2);
                $filaEfe[16] = round($total_efectivo, 2);
                $filaEfe[17] = '00'; // Tipo documento varios (opcional)
                $filaEfe[22] = $glosa_diaria;
                $filas[] = $filaEfe;
            }

            // ---------------------------------------------------------
            // LÍNEA 2: DEBE - IZIPAY (103101)
            // ---------------------------------------------------------
            if ($total_izipay > 0) {
                $filaIzi = $filaBase;
                $filaIzi[10] = '103101';
                $filaIzi[11] = ''; // Si el contador exige RUC de IZIPAY, ponlo aquí
                $filaIzi[13] = 'D';
                $filaIzi[14] = round($total_izipay, 2);
                $filaIzi[16] = round($total_izipay, 2);
                $filaIzi[17] = '00';
                $filaIzi[22] = $glosa_diaria;
                $filas[] = $filaIzi;
            }

            // ---------------------------------------------------------
            // LÍNEAS 3: HABER - CUENTAS POR COBRAR (121201) POR CADA FACTURA
            // ---------------------------------------------------------
            foreach ($pagosDelDia as $p) {
                $tipoDoc = ($p->tdocod == '01') ? 'FT' : (($p->tdocod == '03') ? 'BV' : 'NA');
                $numDoc  = $p->serdoc . '-' . $p->numdoc;

                $filaHaber = $filaBase;
                $filaHaber[10] = !empty($p->cuenta12) ? $p->cuenta12 : '121201';
                $filaHaber[11] = $p->cliente_ruc_dni; // RUC OBLIGATORIO PARA LA 12
                $filaHaber[13] = 'H';
                $filaHaber[14] = round($p->monto, 2);
                $filaHaber[16] = round($p->monto, 2);
                $filaHaber[17] = $tipoDoc;
                $filaHaber[18] = $numDoc;
                $filaHaber[19] = $fecha_operacion;
                $filaHaber[20] = $fecha_operacion; 
                $filaHaber[22] = substr("COBRO " . $numDoc, 0, 30);
                
                $filas[] = $filaHaber;
            }

            // Pasamos al siguiente día (Nuevo asiento)
            $contadorAsiento++;
        }

        $nombreArchivo = "Reporte_Cobranzas_CONCAR_" . date('YmdHis');

        return Excel::create($nombreArchivo, function($excel) use ($filas) {
            $excel->sheet('Cobranzas', function($sheet) use ($filas) {
                
                $sheet->setColumnFormat([
                    'B' => '@',
                    'C' => '@'
                ]);

                $sheet->fromArray($filas, null, 'A1', false, false);

                $columnasConcar = [
                    'A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z',
                    'AA','AB','AC','AD','AE','AF','AG','AH','AI','AJ','AK','AL','AM','AN','AO'
                ];
                
                foreach ($columnasConcar as $col) {
                    $sheet->getStyle($col)->getAlignment()->setWrapText(false);
                }
            });
        })->download('xlsx');
    }

    /*public function exportarExcel(Request $request)
    {
        $request->validate([
            'fecha_inicio'       => 'required|date',
            'fecha_fin'          => 'required|date',
            'sub_ventas'         => 'required',
            'id_empresa_negocio' => 'required',
            'correlativo_inicial'=> 'required|integer|min:1'
        ]);

        $ventas = DB::table('cpe_cabecera')
                    ->join('cliente', 'cpe_cabecera.clicod', '=', 'cliente.clicod')
                    ->select('cpe_cabecera.*', 'cliente.clinum as cliente_ruc_dni')
                    ->whereBetween('cpe_cabecera.ccafem', [$request->fecha_inicio, $request->fecha_fin])
                    ->where('cpe_cabecera.id_empresa_negocio', $request->id_empresa_negocio)
                    ->get();

        if ($ventas->isEmpty()) {
            return back()->with('error', 'No se encontraron comprobantes en este rango de fechas para generar el Excel.');
        }

        // Armamos el arreglo completo con TODA la información
        $filas = [];
        
        // Fila 1: Cabeceras (Con espacio en la primera y CON TILDES exactas para el validador del CONCAR)
        $filas[] = [' ', 'Sub Diario', 'Número de Comprobante', 'Fecha de Comprobante', 'Código de Moneda', 
            'Glosa Principal', 'Tipo de Cambio', 'Tipo de Conversión', 'Flag de Conversión de Moneda', 
            'Fecha Tipo de Cambio', 'Cuenta Contable', 'Código de Anexo', 'Código de Centro de Costo', 
            'Debe / Haber', 'Importe Original', 'Importe en Dólares', 'Importe en Soles', 
            'Tipo de Documento', 'Número de Documento', 'Fecha de Documento', 'Fecha de Vencimiento', 
            'Código de Area', 'Glosa Detalle', 'Código de Anexo Auxiliar', 'Medio de Pago', 
            'Tipo de Documento de Referencia', 'Número de Documento Referencia', 'Fecha Documento Referencia', 
            'Nro Máq. Registradora Tipo Doc. Ref.', 'Base Imponible Documento Referencia', 'IGV Documento Provisión', 
            'Tipo Referencia en estado MQ', 'Número Serie Caja Registradora', 'Fecha de Operación', 
            'Tipo de Tasa', 'Tasa Detracción/Percepción', 'Importe Base Detracción/Percepción Dólares', 
            'Importe Base Detracción/Percepción Soles', "Tipo Cambio para 'F'", 'Importe de IGV sin derecho crédito fiscal'
        ];

        $contadorAsiento = (int) $request->correlativo_inicial;

        foreach ($ventas as $v) {
            $fecha_emision = Carbon::parse($v->ccafem)->format('d/m/Y'); 
            $tipoDoc       = ($v->tdocod == '01') ? 'FT' : (($v->tdocod == '03') ? 'BV' : 'NA');
            $numDoc        = $v->serdoc . '-' . $v->numdoc;
            
            $total_venta   = round($v->ccaitv ?? 0, 2); 
            $total_igv     = round($v->ccaigv ?? 0, 2); 

            $mesVenta       = Carbon::parse($v->ccafem)->format('m');
            
            $subDiarioText  = str_pad($request->sub_ventas, 2, '0', STR_PAD_LEFT); 
            $nroComprobante = str_pad($mesVenta . str_pad($contadorAsiento, 4, '0', STR_PAD_LEFT), 6, '0', STR_PAD_LEFT); 
            $glosa          = substr("VENTAS " . $numDoc, 0, 30); 

            $cta_12 = !empty($v->cuenta12) ? $v->cuenta12 : '121201'; 
            $cta_40 = '401111';

            $filaBase = array_fill(0, 40, '');
            $filaBase[1]  = $subDiarioText;
            $filaBase[2]  = $nroComprobante;
            $filaBase[3]  = $fecha_emision;
            $filaBase[4]  = 'MN';
            $filaBase[5]  = $glosa;
            $filaBase[6]  = '0'; 
            $filaBase[7]  = 'V'; 
            $filaBase[8]  = 'S'; 
            $filaBase[9]  = $fecha_emision;
            $filaBase[15] = '0'; 
            $filaBase[17] = $tipoDoc;
            $filaBase[18] = $numDoc;
            $filaBase[19] = $fecha_emision;
            $filaBase[20] = $fecha_emision; 
            $filaBase[22] = $glosa; 
            $filaBase[29] = '0';
            $filaBase[30] = '0';

            // DEBE (Cuenta 12)
            $fila1 = $filaBase;
            $fila1[10] = $cta_12;
            $fila1[11] = $v->cliente_ruc_dni;
            $fila1[13] = 'D';
            $fila1[14] = $total_venta; 
            $fila1[16] = $total_venta; 
            $filas[] = $fila1;

            // HABER (Cuenta 40 - IGV)
            if ($total_igv > 0) {
                $fila2 = $filaBase;
                $fila2[10] = $cta_40;
                $fila2[13] = 'H';
                $fila2[14] = $total_igv;
                $fila2[16] = $total_igv;
                $filas[] = $fila2;
            }

            // HABER (Cuentas 70)
            $detalles_productos = DB::table('cpe_detalle')
                ->select('cta_contable_70', DB::raw('SUM(cdevve) as subtotal_cuenta'))
                ->where('IdCpe_cabecera', $v->IdCpe_cabecera)
                ->groupBy('cta_contable_70')
                ->get();

            foreach ($detalles_productos as $det) {
                $cta_70 = !empty($det->cta_contable_70) ? $det->cta_contable_70 : '7032111';
                $fila3 = $filaBase;
                $fila3[10] = $cta_70;
                $fila3[13] = 'H';
                $fila3[14] = round($det->subtotal_cuenta, 2);
                $fila3[16] = round($det->subtotal_cuenta, 2);
                $filas[] = $fila3;
            }

            $contadorAsiento++;
        }

        $nombreArchivo = "Reporte_Ventas_CONCAR_" . date('YmdHis');

        // Exportar directamente a .xlsx usando tu componente Excel
        return Excel::create($nombreArchivo, function($excel) use ($filas) {
            $excel->sheet('Ventas', function($sheet) use ($filas) {
                
                // Forzar formato Texto (@) para que Excel no borre los ceros de las columnas B y C
                $sheet->setColumnFormat([
                    'B' => '@',
                    'C' => '@'
                ]);

                // Insertar el array directamente (false, false evita que modifique cabeceras automáticas)
                $sheet->fromArray($filas, null, 'A1', false, false);
            });
        })->download('xlsx');
    }*/
}