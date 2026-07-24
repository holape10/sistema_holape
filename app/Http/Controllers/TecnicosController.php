<?php

namespace MasterSoft\Http\Controllers;

use Illuminate\Http\Request;
use MasterSoft\Http\Requests;
use MasterSoft\tecnicos;
use MasterSoft\User;
use MasterSoft\tipo_documento_identidad;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Input;
use MasterSoft\Http\Requests\ClienteFormRequest;
use MasterSoft\Http\Requests\ClienteUpdateFormRequest;
use Illuminate\Support\Facades\Auth;
use DB;


class TecnicosController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
     public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $ruc = trim(Auth::user()->IdEmpresa);
        if($request){
            $rucemp = trim($request->get('busrucemp'));
            $buscli = trim($request->get('buscli'));
            if(empty($rucemp) && empty($buscli)){
                $tecnicos= DB::tABLE('tecnicos')->where('rucemp','=',$ruc)
                ->orderby('tecnom','asc')
                ->paginate(7);
            } else{
            $tecnicos= DB::tABLE('tecnicos')
            ->where('rucemp','=',$rucemp)
            ->where('tecnom','like', '%'.$buscli.'%')
            ->where('rucemp','=',$ruc)
            ->orwhere('rucemp','=',$rucemp)
            ->where('tecnum','like','%'.$buscli.'%')
            ->where('rucemp','=',$ruc)
            ->orderby('tecnom','asc')
            ->paginate(7);
            }
            
            return view('empresas.tecnicos.index',compact('tecnicos','buscli'));
         }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $documentos = DB::tABLE('tipo_documento_identidad')->where('tdiest','=','Activo')->orderby('tdicod')->get();
        return view('empresas.tecnicos.create',compact('documentos'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $tecnico = new tecnicos;
        $tecnico->rucemp = trim(Auth::user()->IdEmpresa);
        
        $tecnico->tecnum = $request->get('tecnum');
        $tecnico->tecdir = $request->get('tecdir');
        $tecnico->tecnom = $request->get('tecnom');
        $tecnico->teccor = $request->get('teccor');
        $tecnico->tectel = $request->get('tectel');
        
        $tecnico->save();
        return Redirect::to('/tecnicos');
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
        $tecnicos= tecnicos::findOrFail($id);
        $documentos=DB::tABLE('tipo_documento_identidad')->where('tdiest','=','Activo')->get();
        return view('empresas.tecnicos.edit',compact('tecnicos','documentos'));
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
        $tecnico=  tecnicos::findOrFail($id);
        $tecnico->rucemp = trim(Auth::user()->IdEmpresa);
        
        $tecnico->tecnum = $request->get('tecnum');
        $tecnico->tecdir = $request->get('tecdir');
        $tecnico->tecnom = $request->get('tecnom');
        $tecnico->teccor = $request->get('teccor');
        $tecnico->tectel = $request->get('tectel');
        
        
        $tecnico->update();
        return Redirect::to('/tecnicos');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $tecnicos=  tecnicos::findOrFail($id);
        $tecnicos->delete();

        return Redirect::to('/tecnicos');
    }

}
