<?php

namespace MasterSoft\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use MasterSoft\Asiento;
use MasterSoft\AsientoDetalle;
use MasterSoft\PlanContable;

class CompraContableController extends Controller
{
    public function index()
    {
        $compras = DB::table('compras_cabecera')
            ->orderBy('com_cab_id', 'desc')
            ->limit(100)
            ->get();

        return view('empresas.contabilidad.compras.index', compact('compras'));
    }

    public function generarAsientoCompra($id)
    {
        $compra = DB::table('compras_cabecera')->where('com_cab_id', $id)->first();

        if (!$compra) {
            return back()->with('error', 'La compra especificada no existe.');
        }

        $existeAsiento = AsientoDetalle::where('referencia_id', $id)
            ->where('referencia_tipo', 'compras_cabecera')
            ->exists();

        if ($existeAsiento) {
            return back()->with('error', 'Esta compra ya tiene un asiento contable asociado.');
        }

        // Cuentas básicas de compras en el plan peruano (6011 Mercaderías, 4011 IGV, 4212 Emitidas)
        $cuenta60 = PlanContable::where('codigo', '6011')->first(); 
        $cuenta40 = PlanContable::where('codigo', '4011')->first(); 
        $cuenta42 = PlanContable::where('codigo', '4212')->first(); 

        if (!$cuenta60 || !$cuenta40 || !$cuenta42) {
            return back()->with('error', 'Por favor crea las cuentas 6011, 4011 y 4212 en tu Plan Contable.');
        }

        DB::transaction(function () use ($compra, $id, $cuenta60, $cuenta40, $cuenta42) {
            
            $asiento = Asiento::create([
                'fecha'        => $compra->com_fec ?? date('Y-m-d'),
                'glosa'        => "Por la compra según doc: " . ($compra->com_doc_ser ?? '') . " Proveedor: " . ($compra->prov_num ?? ''),
                'tipo_asiento' => 'compras',
                'moneda'       => $compra->mon_id ?? 'PEN',
                'tipo_cambio'  => $compra->tip_cam ?? 1.0000
            ]);

            $total    = floatval($compra->total_com ?? 0.00);
            // Si tu base de datos no guarda el igv directo, lo calculamos de manera estándar (Total / 1.18)
            $subtotal = round($total / 1.18, 2);
            $igv      = round($total - $subtotal, 2);

            // 1. DEBE: Cuenta 6011 (Base imponible de la compra)
            AsientoDetalle::create([
                'asiento_id'       => $asiento->id,
                'plan_contable_id' => $cuenta60->id,
                'debe'             => $subtotal,
                'haber'            => 0.00,
                'referencia_id'    => $id,
                'referencia_tipo'  => 'compras_cabecera'
            ]);

            // 2. DEBE: Cuenta 4011 (IGV de la compra)
            if ($igv > 0) {
                AsientoDetalle::create([
                    'asiento_id'       => $asiento->id,
                    'plan_contable_id' => $cuenta40->id,
                    'debe'             => $igv,
                    'haber'            => 0.00,
                    'referencia_id'    => $id,
                    'referencia_tipo'  => 'compras_cabecera'
                ]);
            }

            // 3. HABER: Cuenta 4212 (Total por pagar al Proveedor)
            AsientoDetalle::create([
                'asiento_id'       => $asiento->id,
                'plan_contable_id' => $cuenta42->id,
                'debe'             => 0.00,
                'haber'            => $total,
                'referencia_id'    => $id,
                'referencia_tipo'  => 'compras_cabecera'
            ]);
        });

        return redirect()->route('compras.index')->with('success', 'Asiento de compra generado con éxito.');
    }
}