<?php

namespace MasterSoft\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;

class MotivoMermaController extends Controller
{
    public function index()
    {
        $motivos = DB::table('motivos_merma')->orderBy('id', 'desc')->get();
        return view('empresas.mermas.motivos', compact('motivos'));
    }

    public function store(Request $request)
    {
        DB::table('motivos_merma')->insert([
            'descripcion' => strtoupper($request->get('descripcion')),
            'estado' => $request->get('estado', 1)
        ]);
        return Redirect::to('/motivos-merma')->with('success', 'Motivo creado correctamente.');
    }

    public function update(Request $request)
    {
        DB::table('motivos_merma')
            ->where('id', $request->get('id'))
            ->update([
                'descripcion' => strtoupper($request->get('descripcion')),
                'estado' => $request->get('estado')
            ]);
        return Redirect::to('/motivos-merma')->with('success', 'Motivo actualizado correctamente.');
    }

    public function destroy($id)
    {
        // Validación de seguridad: No eliminar si ya hay mermas con este motivo
        $uso = DB::table('mermas')->where('id_motivo', $id)->count();
        if($uso > 0){
            return Redirect::to('/motivos-merma')->with('info', 'No se puede eliminar porque existen mermas registradas con este motivo.');
        }

        DB::table('motivos_merma')->where('id', $id)->delete();
        return Redirect::to('/motivos-merma')->with('success', 'Motivo eliminado correctamente.');
    }
}