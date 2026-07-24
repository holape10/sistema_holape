<?php

namespace MasterSoft\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use DB;


class validateAuthenticated
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
     	




    	$empresa = DB::tABLE('empresa')->first();
        /*	$contenido = file_get_contents(public_path().'/js/empresa/empresa.txt');

        	
	    	if($contenido == $empresa->IdEmpresa){
	    		
	    		 return $next($request);
	    		
	    	}else{
	    		DB::TABLE('empresa')
				->delete();

	    	}*/
	       	
	    	
	       	
		        return $next($request);
		
     
    }
}


        
