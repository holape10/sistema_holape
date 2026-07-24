<?php

namespace MasterSoft\Http\Controllers;

use Illuminate\Http\Request;
use MasterSoft\productos;
use MasterSoft\SerieProducto;
use MasterSoft\presentaciones;
use MasterSoft\movimientos;
use MasterSoft\Http\Requests\ProductoCreateFormRequest;
use MasterSoft\Http\Requests\ProductoUpdateFormRequest;
use MasterSoft\Http\Requests;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Auth;
use DB;

class SerieProductoController extends Controller
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
            $rucemp = trim(Auth::user()->IdEmpresa);
        if($request){
            $rucemp = trim(Auth::user()->IdEmpresa);
            $buspro = trim($request->get('busserie'));
            if(empty($rucemp) && empty($buspro)){
                $series= DB::tABLE('presentaciones as p')
                ->orderby('presentacion','asc')->where('IdEmpresa','=',$rucemp)->get();
               // ->paginate(7);

            } else{
                $series= DB::tABLE('presentaciones as p')
                ->orderby('presentacion','asc')->where('IdEmpresa','=',$rucemp)->get();
                //->paginate(7);
            }

            return view('empresas.productos.serieindex',['series'=>$series,'buspro'=>$buspro]);
         }
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
        $presentaciones= presentaciones::findOrFail($id);
        $presentaciones->delete();

        return Redirect::to('/serieproducto');
    }
}
