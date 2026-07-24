<?php

namespace MasterSoft\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use DB;
use Carbon;

class ValidarTipoCambio
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
     	
     	$fec = now()->format('Y-m-d');

       	$buscar_cambio = DB::TABLE('tipocambio')
       	->where('FecTipCambio',$fec)
	    ->where('CamVenta','>','0')
	    ->where('CamCompra','>','0')
	    ->first();

	    if(empty($buscar_cambio)){

	    	try{

	       		$resultado = file_get_contents("https://www.sunat.gob.pe/a/txt/tipoCambio.txt");

	       		$dato = explode("|", $resultado);

		        $fecha = $dato[0];
		        $compra = $dato[1];
		        $venta = $dato[2];

		       // dd($fecha);
		        $fech_nueva = Carbon::createFromFormat('d/m/Y', $fecha)->format('Y-m-d');

		        $buscar = DB::TABLE('tipocambio')->where('FecTipCambio',$fech_nueva)
		        ->where('CamVenta','>','0')
		        ->where('CamCompra','>','0')
		        ->first();


		        if(empty($buscar)){
		           DB::TABLE('tipoCambio')->insert(['FecTipCambio'=>$fech_nueva,'CamVenta'=>$venta,'CamCompra'=>$compra]);
		        }

		        $buscar_nuevo = DB::TABLE('tipocambio')
		        ->where('FecTipCambio',$fech_nueva)
		        ->where('CamVenta','>','0')
		        ->where('CamCompra','>','0')
		        ->first();

		        if(empty($buscar_nuevo)){

		    
		          return Redirect::to('/tipocambio');

		        }else{

		        	 return $next($request);
		        }
       

	       	}catch(\Exception $e){

	       		 return $next($request);

	       	}

       	}else{

       		 return $next($request);

       	}
       
	      

     
    }
}


        
