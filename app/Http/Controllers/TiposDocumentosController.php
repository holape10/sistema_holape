<?php

namespace MasterSoft\Http\Controllers;

use Illuminate\Http\Request;
use MasterSoft\Http\Requests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use MasterSoft\tiposdocumentos;
use DB;

class tiposdocumentosController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
      $rucemp = trim(Auth::user()->IdEmpresa);
    if($request){
      $buscar = $request->get('buscar');

      if(empty($buscar)){
          $documentos = DB::tABLE('tiposdocumentos')->where('IdEmpresa',$rucemp)->paginate(10);
      }else{
           $documentos = DB::tABLE('tiposdocumentos')->where('doc_nom',$buscar)->where('IdEmpresa',$rucemp)->paginate(10);
      }
      return view('empresas.tiposdocumentos.index',compact('documentos','buscar'));
      }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('empresas.tiposdocumentos.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $rucemp = trim(Auth::user()->IdEmpresa);
        $documento=new tiposdocumentos;
        $documento->doc_nom = $request->get('doc_nom') ;
        $documento->IdEmpresa= $rucemp;
        $documento->save();
      return Redirect::to('/tiposdocumentos');
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

          $documento=tiposdocumentos::findOrFail($id);
 
          return view('empresas.tiposdocumentos.edit',compact('documento'));
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
          $documento= tiposdocumentos::findOrFail($id);
          $rucemp = trim(Auth::user()->IdEmpresa);
          $documento->doc_nom = $request->get('doc_nom');
          $documento->update();
          return Redirect::to('/tiposdocumentos');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
     public function destroy($id)
     {
         $documento= tiposdocumentos::findOrFail($id);
         $documento->delete();

         return Redirect::to('/tiposdocumentos');
     }
}
