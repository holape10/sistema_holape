<?php

namespace MasterSoft\Http\Controllers;

use Illuminate\Http\Request;
use MasterSoft\Http\Requests;
use MasterSoft\laboratorio;
use MasterSoft\User;
use MasterSoft\tipo_documento_identidad;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Auth;
use DB;

class LaboratorioController extends Controller
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
          $buscar = trim($request->get('lab_nom'));
          if(empty($buscar)){
              $laboratorios= DB::tABLE('laboratorio')->where('IdEmpresa','=',$ruc)
              ->orderby('lab_nom','asc')
              ->paginate(10);
          } else{
          $laboratorios= DB::tABLE('laboratorio')
          ->where('IdEmpresa','=',$ruc)
          ->where(function ($query) use ($buscar) {
                $query->where('lab_nom','like', '%'.$buscar.'%')
                      ->orwhere('lab_cod','=',$buscar);
          })
          ->orderby('lab_nom','asc')
          ->paginate(10);
          }

          return view('empresas.laboratorio.index',compact('laboratorios','buscar'));
       }
  }

  /**
   * Show the form for creating a new resource.
   *
   * @return \Illuminate\Http\Response
   */

   public function create()
   {
    

       return view('empresas.laboratorio.create');
   }

   /**
    * Store a newly created resource in storage.
    *
    * @param  \Illuminate\Http\Request  $request
    * @return \Illuminate\Http\Response
    */

    public function store(Request $request)
    {
        $laborario = new laboratorio;
        $laborario->IdEmpresa = trim(Auth::user()->IdEmpresa);
        $laborario->lab_nom = $request->get('lab_nom');
        $laborario->lab_cod = $request->get('lab_cod');
        $laborario->save();

        return Redirect::to('/laboratorio');
    }

    public function edit($id)
    {
        $laboratorio=laboratorio::findOrFail($id);

    
        return view('empresas.laboratorio.edit',['laboratorio'=>$laboratorio]);

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $ids
     * @return \Illuminate\Http\Response
     */

     public function update(Request $request, $id)
     {
         $laborario= laboratorio::findOrFail($id);
         $laborario->lab_cod = $request->get('lab_cod');
         $laborario->lab_nom = $request->get('lab_nom');
         $laborario->update();

         return Redirect::to('/laboratorio');
     }

     public function destroy($id)
     {
         $laborario= laboratorio::findOrFail($id);
         $laborario->delete();

         return Redirect::to('/laboratorio');
     }

     

    
}
