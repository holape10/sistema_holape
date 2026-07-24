<?php

namespace MasterSoft\Http\Controllers;

use Illuminate\Http\Request;
use MasterSoft\Http\Requests;
use MasterSoft\Empresa;
use MasterSoft\User;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Input;
use MasterSoft\Http\Requests\EmpresaFormRequest;
use Illuminate\Support\Facades\Auth;

use DB;

class UsuarioEmpresaController extends Controller
{
     public function __construct()
    {
        $this->middleware('guest');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    

     public function selectAjax(Request $request)
    {
    	if($request->ajax()){
    		$listarEmpresas=DB::tABLE('empresa as e')
            ->join('users u','e.IdEmpresa','=','u.IdEmpresa')
            ->select('u.IdEmpresa','e.NomEmpresa')
            ->where('u.email','=',$id)
            ->orderBy('NomEmpresa','asc')  
            ->get();
    		$data = view('ajax-select',compact('empresa'))->render();
    		return response()->json(['options'=>$data]);
    	}
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(EmpresaFormRequest $request)
    {
       
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
       
    }

    public function showLogo($id)
    {
        
    }
    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(EmpresaFormRequest $request, $id)
    {
       
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

      public function destroy($id)
    {
       
    }

}
