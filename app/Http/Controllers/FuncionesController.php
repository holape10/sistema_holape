<?php

namespace MasterSoft\Http\Controllers;

use Illuminate\Http\Request;
use MasterSoft\Http\Requests;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Input;
use DB;

class FuncionesController extends Controller
{

	public function __construct()
    {
       
    }

   	public function consultarempresas(Request $request){
      $search = $request->email;
      $roluser = DB::tABLE('users as u')->select('r.name')
      ->join('role_user as ru','u.IdUsuario','=','ru.user_IdUsuario')
      ->join('roles as r','ru.role_id','=','r.id')
      ->where('email','=',$search)->first();

       $results = array();


      if($roluser==NULL){
      	   $results[] = ['id'=>'','text'=>''];
      }elseif($roluser->name=='admin'){
      	   $results[] = ['id'=>'','text'=>''];
      }elseif($roluser->name=='user'){
      		$empresas = DB::tABLE('users as u')->select('u.IdEmpresa')
          ->join('empresa as e','u.IdEmpresa','=','e.IdEmpresa')
          ->where('EstEmpresa','=','Activo')
      		->where('email','=',$search)
          ->get();
      		foreach($empresas as $index => $emp){
        		$results[] = ['id'=>$emp->IdEmpresa,'text'=>$emp->IdEmpresa];
      		}
      } 

      return response()->json($results);
    }


   	

}
