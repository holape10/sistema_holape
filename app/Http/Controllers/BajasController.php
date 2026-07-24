<?php

namespace MasterSoft\Http\Controllers;
use Illuminate\Http\Request;
use MasterSoft\Http\Requests;
use MasterSoft\TipoIGV;
use MasterSoft\Empresa;
use MasterSoft\Cliente;
use MasterSoft\cpe_cabecera;
use MasterSoft\documento_relacionado;
use MasterSoft\unidad_medida;
use MasterSoft\cpe_detalle;
use MasterSoft\cpe_baja;
use MasterSoft\Comprobante;
use MasterSoft\cpe_nota_detalle;
use MasterSoft\cpe_nota;
use MasterSoft\tipo_documento_identidad;
use MasterSoft\productos;
use MasterSoft\tipocambio;
use Illuminate\Support\Facades\Auth;
use MasterSoft\Http\Requests\FacturaCreateFormRequest;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Input;

use PDF;
use DB;


class BajasController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
            $fechaini = now()->modify('first day of this month');
            $fechafin = now()->modify('last day of this month');
            $razsoc = $request->get('searchText');
            $respse = $request->get('tiper');
           // $tipdoc = $request->get('docomp');
            $fecin = $request->get('fecin');
            $fecfin = $request->get('fecfin');
            $serdoc=$request->get('serdoc');
            $compbaja=$request->get('compbaja');
            $numdoc = $request->get('numdoc');
          
            $rucemp = Auth::user()->IdEmpresa;
            $ser = substr($compbaja,strpos($compbaja,'-')-4,4);
            $num = substr($compbaja,strpos($compbaja,'-')+1,8);

             $empresa = Empresa::findOrFail($rucemp);

           if(empty($razsoc) && empty($respse)  && empty($fecin) && empty($fecfin) && empty($serdoc) && empty($numdoc) && empty($comp)){

               $bajas = DB::tABLE('cpe_baja as b')->select('b.ccaenlace','b.IdCpe_cabecera','b.IdEmpresa','b.cbacor','b.cbdfco','tdodes','b.cbafec','b.cbanum','b.cbamot','b.ccasunrescod','b.ccasuntick','serdoc','ccanom','ccandi','TipoBaja')
                ->join('tipo_documento as td','b.tdocod','=','td.tdocod')
                ->join('cpe_cabecera as c','b.IdCpe_cabecera','=','c.IdCpe_cabecera')
                ->where('c.IdEmpresa','=',$rucemp)
                ->orderby('IdCpe_baja','desc')
                ->paginate(10);
            }elseif(!empty($compbaja)){

                $bajas = DB::tABLE('cpe_baja as b')->select('b.ccaenlace','b.IdCpe_cabecera','b.IdEmpresa','b.cbacor','b.cbdfco','tdodes','b.cbafec','b.cbanum','b.cbamot','b.ccasunrescod','b.ccasuntick','serdoc','ccanom','ccandi','TipoBaja')
                ->join('tipo_documento as td','b.tdocod','=','td.tdocod')
                ->join('cpe_cabecera as c','b.IdCpe_cabecera','=','c.IdCpe_cabecera')
                ->where('c.IdEmpresa','=',$rucemp)
                ->where('serdoc','=',$ser)
                ->where('numdoc','=',$num)
                ->orderby('IdCpe_baja','desc')
                ->paginate(10);

            }elseif (empty($razsoc) && $respse==1) {
                  $bajas = DB::tABLE('cpe_baja as b')->select('b.ccaenlace','b.IdCpe_cabecera','b.IdEmpresa','b.cbacor','b.cbdfco','tdodes','b.cbafec','b.cbanum','b.cbamot','b.ccasunrescod','b.ccasuntick','serdoc','ccanom','ccandi','TipoBaja')
                ->join('tipo_documento as td','b.tdocod','=','td.tdocod')
                ->join('cpe_cabecera as c','b.IdCpe_cabecera','=','c.IdCpe_cabecera')
                ->where('c.IdEmpresa','=',$rucemp)
                ->where('b.cbdfco','>=',$fecin)
                ->where('b.cbdfco','<=',$fecfin)
                ->orderby('IdCpe_baja','desc')
                ->paginate(10);

           }elseif($respse==1){
           
               $bajas = DB::tABLE('cpe_baja as b')->select('b.ccaenlace','b.IdCpe_cabecera','b.IdEmpresa','b.cbacor','b.cbdfco','tdodes','b.cbafec','b.cbanum','b.cbamot','b.ccasunrescod','b.ccasuntick','serdoc','ccanom','ccandi','TipoBaja')
                ->join('tipo_documento as td','b.tdocod','=','td.tdocod')
                ->join('cpe_cabecera as c','b.IdCpe_cabecera','=','c.IdCpe_cabecera')
                ->where('c.IdEmpresa','=',$rucemp)
                ->where('ccanom','like','%'.$razsoc.'%')
                ->where('b.cbdfco','>=',$fecin)
                ->where('b.cbdfco','<=',$fecfin)
                ->orwhere('c.IdEmpresa','=',$rucemp)
                ->where('ccandi','like','%'.$razsoc.'%') 
                ->where('b.cbdfco','>=',$fecin)
                ->where('b.cbdfco','<=',$fecfin)
                ->orderby('IdCpe_baja','desc')
                ->paginate(10);


           }elseif (empty($razsoc)) {
                if($respse==2){
                    $bajas = DB::tABLE('cpe_baja as b')->select('b.ccaenlace','b.IdCpe_cabecera','b.IdEmpresa','b.cbacor','b.cbdfco','tdodes','b.cbafec','b.cbanum','b.cbamot','b.ccasunrescod','b.ccasuntick','serdoc','ccanom','ccandi','TipoBaja')
                    ->join('tipo_documento as td','b.tdocod','=','td.tdocod')
                    ->join('cpe_cabecera as c','b.IdCpe_cabecera','=','c.IdCpe_cabecera')
                    ->where('c.IdEmpresa','=',$rucemp)
                    ->where('b.ccasunrescod','b.ccasuntick','=',NULL)
                    ->where('b.cbdfco','>=',$fecin)
                    ->where('b.cbdfco','<=',$fecfin)
                    ->orderby('IdCpe_baja','desc')
                    ->paginate(10);

                }else{
                    $bajas = DB::tABLE('cpe_baja as b')->select('b.ccaenlace','b.IdCpe_cabecera','b.IdEmpresa','b.cbacor','b.cbdfco','tdodes','b.cbafec','b.cbanum','b.cbamot','b.ccasunrescod','b.ccasuntick','serdoc','ccanom','ccandi','TipoBaja')
                    ->join('tipo_documento as td','b.tdocod','=','td.tdocod')
                    ->join('cpe_cabecera as c','b.IdCpe_cabecera','=','c.IdCpe_cabecera')
                    ->where('c.IdEmpresa','=',$rucemp)
                    ->where('b.ccasunrescod','b.ccasuntick','=',$respse)
                    ->where('b.cbdfco','>=',$fecin)
                    ->where('b.cbdfco','<=',$fecfin)
                    ->orderby('IdCpe_baja','desc')
                    ->paginate(10);
                }
       

           }else{
                if($respse==2){
                    $bajas = DB::tABLE('cpe_baja as b')->select('b.ccaenlace','b.IdCpe_cabecera','b.IdEmpresa','b.cbacor','b.cbdfco','tdodes','b.cbafec','b.cbanum','b.cbamot','b.ccasunrescod','b.ccasuntick','serdoc','ccanom','ccandi','TipoBaja')
                    ->join('tipo_documento as td','b.tdocod','=','td.tdocod')
                    ->join('cpe_cabecera as c','b.IdCpe_cabecera','=','c.IdCpe_cabecera')
                    ->where('c.IdEmpresa','=',$rucemp)
                    ->where('ccanom','like','%'.$razsoc.'%')
                    ->where('b.ccasunrescod','b.ccasuntick','=',NULL)
                    ->where('b.cbdfco','>=',$fecin)
                    ->where('b.cbdfco','<=',$fecfin)
                    ->orwhere('c.IdEmpresa','=',$rucemp)
                    ->where('ccandi','like','%'.$razsoc.'%')
                    ->where('b.ccasunrescod','b.ccasuntick','=',NULL) 
                    ->where('b.cbdfco','>=',$fecin)
                    ->where('b.cbdfco','<=',$fecfin)
                    ->orderby('IdCpe_baja','desc')
                    ->paginate(10);
                }else{
                 $bajas = DB::tABLE('cpe_baja as b')->select('b.ccaenlace','b.IdCpe_cabecera','b.IdEmpresa','b.cbacor','b.cbdfco','tdodes','b.cbafec','b.cbanum','b.cbamot','b.ccasunrescod','b.ccasuntick','serdoc','ccanom','ccandi','TipoBaja')
                    ->join('tipo_documento as td','b.tdocod','=','td.tdocod')
                    ->join('cpe_cabecera as c','b.IdCpe_cabecera','=','c.IdCpe_cabecera')
                    ->where('c.IdEmpresa','=',$rucemp)
                    ->where('ccanom','like','%'.$razsoc.'%')
                    ->where('b.ccasunrescod','b.ccasuntick','=',$respse)
                    ->where('b.cbdfco','>=',$fecin)
                    ->where('b.cbdfco','<=',$fecfin)
                    ->orwhere('c.IdEmpresa','=',$rucemp)
                    ->where('ccandi','like','%'.$razsoc.'%')
                    ->where('b.ccasunrescod','b.ccasuntick','=',$respse) 
                    ->where('b.cbdfco','>=',$fecin)
                    ->where('b.cbdfco','<=',$fecfin)
                    ->orderby('IdCpe_baja','desc')
                    ->paginate(10);   
                } 
                    
              
            }
          
            return view('empresas.comprobantes.listarbajas',['bajas'=>$bajas,'empresa'=>$empresa]);

        
    }

     public function listarbajas($idcabecera,$tdocod){

      $rucemp =Auth::user()->IdEmpresa;
     
     if($tdocod=='1' || $tdocod=='2'){
        $bajas = DB::tABLE('cpe_baja as b')->select('b.ccaenlace','b.IdCpe_cabecera','b.IdEmpresa','b.cbacor','b.cbdfco','tdodes','b.cbafec','b.cbanum','b.cbamot','b.ccasunrescod','b.ccasuntick','serdoc','ccanom','ccandi','TipoBaja')
      ->join('tipo_documento as td','b.tdocod','=','td.tdocod')
      ->join('cpe_cabecera as c','b.IdCpe_cabecera','=','c.IdCpe_cabecera')
      ->where('b.IdCpe_cabecera','=',$idcabecera)
      ->where('b.tdocod','=',$tdocod)
      ->paginate(10);
     
        $empresa = Empresa::findOrFail($rucemp);

      $sndocmod = DB::tABLE('cpe_cabecera')->select('serdoc','numdoc')->where('IdCpe_cabecera','=',$idcabecera)->first();


     }elseif($tdocod=='3' || $tdocod=='4'){
         $bajas = DB::tABLE('cpe_baja as b')->select('b.ccaenlace','b.IdCpe_cabecera','b.IdEmpresa','b.cbacor','b.cbdfco','tdodes','b.cbafec','b.cbanum','b.cbamot','b.ccasunrescod','b.ccasuntick','serdoc','ccanom','ccandi','TipoBaja')
          ->join('tipo_documento as td','b.tdocod','=','td.tdocod')
          ->join('cpe_nota as c','b.IdCpe_cabecera','=','c.IdCpe_nota')
          ->where('b.IdCpe_cabecera','=',$idcabecera)
          ->where('b.tdocod','=',$tdocod)
          ->paginate(10);
     
     $empresa = Empresa::findOrFail($rucemp);

      $sndocmod = DB::tABLE('cpe_nota')->select('serdoc','numdoc')->where('IdCpe_nota','=',$idcabecera)->first();

     }
   
  
        return view('empresas.comprobantes.listarbajas',['bajas'=>$bajas,'sndocmod'=>$sndocmod,'empresa'=>$empresa]);

    }

    public function buscardocumentosbajas(Request $request){
      $search = $request->term;

      $ser = substr($search,strpos($search,'-')-4,4);
      $num = substr($search,strpos($search,'-')+1,8);

      $rucemp = trim(Auth::user()->IdEmpresa);

      $comprobante = DB::tABLE('cpe_cabecera as cpe_c')->select('serdoc','numdoc')
      ->where('serdoc','=',$ser)
      ->where('numdoc','=',$num)
      ->where('IdEmpresa','=',$rucemp)
      ->where('ccabaj','<>',NULL)->take(1)->get();

      $results = array();

      foreach($comprobante as $c => $comp){
        $sernum=$comp->serdoc.'-'.$comp->numdoc;
        $results[] = ['value'=>$sernum];
      }
      return response()->json($results);
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
}
