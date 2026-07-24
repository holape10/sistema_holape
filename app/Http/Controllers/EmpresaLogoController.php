<?php

namespace MasterSoft\Http\Controllers;

use Illuminate\Http\Request;
use MasterSoft\Http\Requests;
use MasterSoft\Empresa;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Input;
use MasterSoft\Http\Requests\EmpresaFormRequest;

use DB;



class EmpresaLogoController extends Controller
{
  
    public function showLogo($id)
    {
         try {
             return view('auth.login', ['empresa'=>Empresa::findOrFail($id)]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return view('auth/mensaje');
        }
    }
}


