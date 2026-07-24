<?php

namespace MasterSoft\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
     public function __construct()
    {
        $this->middleware('auth');
    }
    public function index(Request $request)
    {	
       // $request->user()->authorizeRoles(['user', 'admin']);

    	if(Auth::user()->hasRole('admin')){
                //return redirect('/inicio');
            return view('empresas.dashboard.dashboard_v2');
        }

        if(Auth::user()->hasRole('mozo')){
                return redirect('/seleccion');
        }

        if(Auth::user()->hasRole('superadmin')){
            return redirect('/puntoventadirecta');
        }
      

         if(Auth::user()->hasRole('caja')){
            return redirect('/consolacaja');
        }

           if(Auth::user()->hasRole('contador')){
            return redirect('/reportes/1');
        }

      /*  if(Auth::user()->hasRole('recepcion')){
            return redirect('/ordeneselectro');
        }

          if(Auth::user()->hasRole('supervisor')){
            return redirect('/ordenessuper');
        }

         if(Auth::user()->hasRole('coordinador')){
            return redirect('/ordenestec');
        }
        
           if(Auth::user()->hasRole('tecnico')){
            return redirect('/atencionot');
        }

            if(Auth::user()->hasRole('calidad')){
            return redirect('/atencioncc');
        }

            if(Auth::user()->hasRole('sistemas')){
            return redirect('/ordeneselectro');
        }*/



       if(Auth::user()->hasRole('recepcion')){
            return redirect('/ordenescompu');
        }

          if(Auth::user()->hasRole('supervisor')){
            return redirect('/ordenesteccompu');
        }

         if(Auth::user()->hasRole('coordinador')){
            return redirect('/ordenestec');
        }
        
           if(Auth::user()->hasRole('tecnico')){
            return redirect('/atencioncompu');
        }

            if(Auth::user()->hasRole('calidad')){
            return redirect('/atencioncc');
        }

            if(Auth::user()->hasRole('sistemas')){
            return redirect('/ordeneselectro');
        }


    }
    
}
