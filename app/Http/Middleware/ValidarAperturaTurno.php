<?php

namespace MasterSoft\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;

class ValidarAperturaTurno
{



    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        
        if(Auth::user()->turno =='Cerrado'){


         if(Auth::user()->hasRole('caja') || Auth::user()->hasRole('admin')){
           return Redirect::to('/caja')->with('danger','SE REQUIERE APERTURAR TURNO'); 
         }
          


        }else{
        	 return $next($request);
        }

     
    }
}
