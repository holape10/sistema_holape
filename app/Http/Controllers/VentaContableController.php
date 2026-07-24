<?php

namespace MasterSoft\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use MasterSoft\Asiento;
use MasterSoft\AsientoDetalle;
use MasterSoft\PlanContable;

class VentaContableController extends Controller
{
    public function index()
    {
        $ventas = DB::table('cpe_cabecera')
            ->orderBy('IdCpe_cabecera', 'desc')
            ->limit(100)
            ->get();

        // Extraemos los IDs de las ventas mostradas en pantalla
        $ventasIds = $ventas->pluck('IdCpe_cabecera')->toArray();

        // Buscamos cuáles de esos IDs ya existen en la tabla de detalles del diario
        $centralizadas = AsientoDetalle::whereIn('referencia_id', $ventasIds)
            ->where('referencia_tipo', 'cpe_cabecera')
            ->pluck('referencia_id')
            ->toArray();

        // Pasamos la variable $centralizadas a la vista para ocultar los botones
        return view('empresas.contabilidad.ventas.index', compact('ventas', 'centralizadas'));
    }

    // Botón Individual
    public function generarAsientoVenta($id)
    {
        $resultado = $this->procesarAsientoIndividual($id);
        
        if ($resultado['status']) {
            return back()->with('success', $resultado['mensaje']);
        }
        return back()->with('error', $resultado['mensaje']);
    }

    // Botón Masivo
    public function centralizarMasivo()
    {
        // Traemos las últimas 100 ventas (puedes ajustar esto luego para filtrar por mes/fecha)
        $ventas = DB::table('cpe_cabecera')->orderBy('IdCpe_cabecera', 'desc')->limit(100)->get();
        
        $procesados = 0;
        $omitidos = 0;

        foreach ($ventas as $venta) {
            $resultado = $this->procesarAsientoIndividual($venta->IdCpe_cabecera);
            if ($resultado['status']) {
                $procesados++;
            } else {
                $omitidos++;
            }
        }

        return back()->with('success', "Proceso Masivo Terminado: $procesados asientos generados. $omitidos omitidos (ya existían o faltaban datos).");
    }

    // Lógica Centralizada (Usada por el botón masivo y el individual)
    private function procesarAsientoIndividual($id)
    {
        $venta = DB::table('cpe_cabecera')->where('IdCpe_cabecera', $id)->first();
        if (!$venta) return ['status' => false, 'mensaje' => 'Venta no encontrada.'];

        $existeAsiento = AsientoDetalle::where('referencia_id', $id)
            ->where('referencia_tipo', 'cpe_cabecera')
            ->exists();

        if ($existeAsiento) return ['status' => false, 'mensaje' => 'Ya cuenta con asiento.'];

        $detalles = DB::table('cpe_detalle')->where('IdCpe_cabecera', $id)->get();
        if ($detalles->isEmpty()) return ['status' => false, 'mensaje' => 'Comprobante sin detalles.'];

        $codigo12 = $venta->cuenta_12 ?? '121201'; 
        $codigo40 = $venta->cuenta_igv ?? '40111'; 

        $cuenta12 = PlanContable::where('codigo', $codigo12)->first();
        $cuenta40 = PlanContable::where('codigo', $codigo40)->first();

        if (!$cuenta12) return ['status' => false, 'mensaje' => "Cuenta 12 ($codigo12) no existe."];

        DB::transaction(function () use ($venta, $id, $detalles, $cuenta12, $cuenta40) {
            $serieNum = ($venta->serdoc ?? 'N/O') . "-" . ($venta->numdoc ?? '000');
            
            $asiento = Asiento::create([
                'fecha'        => $venta->ccafem ?? date('Y-m-d'),
                'glosa'        => "Venta CPE: " . $serieNum . " - " . ($venta->ccanom ?? 'Portador'),
                'tipo_asiento' => 'ventas',
                'moneda'       => $venta->moncod ?? 'PEN',
                'tipo_cambio'  => 1.0000 
            ]);

            $totalCobrar = floatval($venta->ccaitv ?? 0.00);
            AsientoDetalle::create([
                'asiento_id'       => $asiento->id,
                'plan_contable_id' => $cuenta12->id,
                'debe'             => $totalCobrar,
                'haber'            => 0.00,
                'referencia_id'    => $id,
                'referencia_tipo'  => 'cpe_cabecera'
            ]);

            $igvTotal = floatval($venta->ccaigv ?? 0.00);
            if ($igvTotal > 0 && $cuenta40) {
                AsientoDetalle::create([
                    'asiento_id'       => $asiento->id,
                    'plan_contable_id' => $cuenta40->id,
                    'debe'             => 0.00,
                    'haber'            => $igvTotal,
                    'referencia_id'    => $id,
                    'referencia_tipo'  => 'cpe_cabecera'
                ]);
            }

            $totalesPorCuenta70 = [];
            foreach ($detalles as $det) {
                $cta70 = $det->cta_contable_70 ?? '70111'; 
                $subtotalLinea = floatval($det->cdevun) * floatval($det->cdecan);

                if (!isset($totalesPorCuenta70[$cta70])) {
                    $totalesPorCuenta70[$cta70] = 0;
                }
                $totalesPorCuenta70[$cta70] += $subtotalLinea;
            }

            foreach ($totalesPorCuenta70 as $codigo70 => $montoSubtotal) {
                $cuenta70 = PlanContable::where('codigo', $codigo70)->first();
                if ($cuenta70) {
                    AsientoDetalle::create([
                        'asiento_id'       => $asiento->id,
                        'plan_contable_id' => $cuenta70->id,
                        'debe'             => 0.00,
                        'haber'            => round($montoSubtotal, 2),
                        'referencia_id'    => $id,
                        'referencia_tipo'  => 'cpe_cabecera'
                    ]);
                }
            }
        });

        return ['status' => true, 'mensaje' => 'Generado correctamente.'];
    }

    public function generarTxtSunat(Request $request)
    {
        $mes = $request->get('mes', date('m'));
        $anio = $request->get('anio', date('Y'));

        // 1. Buscamos el RUC de la primera empresa que encuentre en la tabla o 
        // podrías filtrar por el usuario logueado. Por ahora, tomamos el RUC de la 
        // tabla empresa_negocios de forma dinámica.
        $empresaData = DB::table('empresa_negocios')
                        ->select('IdEmpresa')
                        ->first();
        
        $rucEmpresa = $empresaData ? $empresaData->IdEmpresa : '00000000000';

        // 2. Nombre de archivo oficial con el RUC dinámico
        $filename = "LE" . $rucEmpresa . $anio . $mes . "0000000000101111.txt";

        // 3. Consulta de ventas filtrada
        $ventas = DB::table('cpe_cabecera')
            ->whereMonth('ccafem', $mes)
            ->whereYear('ccafem', $anio)
            ->get();

        $txt = "";
        foreach ($ventas as $v) {
            $periodo = $anio . $mes . "00";
            $cuo = "V" . str_pad($v->IdCpe_cabecera, 8, '0', STR_PAD_LEFT);
            $fec = date('d/m/Y', strtotime($v->ccafem));
            
            // Validar si el RUC del cliente es válido (11 dígitos para RUC, menos es DNI)
            $nroDoc = $v->ccandi ?? '00000000';
            $tipoDoc = (strlen($nroDoc) == 11) ? '6' : '1';
            
            // Estructura de línea (El uso de | es el estándar para SIRE)
            $linea = $periodo . "|" . $cuo . "|M1|" . $fec . "||" . $v->tdocod . "|" . $v->serdoc . "|" . $v->numdoc . "||" . $tipoDoc . "|" . $nroDoc . "|" . $v->ccanom . "|" . $v->ccatvg . "|0.00|" . $v->ccaigv . "|0.00|0.00|0.00|0.00|0.00|" . $v->ccaitv . "|PEN|1.000|||||||||1|";
            
            $txt .= $linea . "\r\n";
        }

        return response($txt, 200, [
            'Content-Type' => 'text/plain',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"'
        ]);
    }
}