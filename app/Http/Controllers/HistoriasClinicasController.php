<?php

namespace MasterSoft\Http\Controllers;
use Illuminate\Http\Request;
use MasterSoft\Http\Requests;
use MasterSoft\historia_clinica;
use MasterSoft\Cliente;
use MasterSoft\atencion_clinica;
use Illuminate\Support\Facades\Auth;
use MasterSoft\Http\Requests\FacturaCreateFormRequest;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Input;
use DB;

class HistoriasClinicasController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {


        
       /*  $his_cli_fec = $request->get('his_cli_fec');
         $his_cli_fec_fin = $request->get('his_cli_fec_fin');

         if(empty($his_cli_fec)){

          $his_cli_fec = now()->modify('first day of this month')->format('Y-m-d');
          $his_cli_fec_fin = now()->modify('last day of this month')->format('Y-m-d');

        }*/

        $documentos_identidad = DB::tABLE('tipo_documento_identidad')->get();
       	$generos = DB::tABLE('sexo')->get();
       	$estados = DB::tABLE('estado_civil')->get();
       	$especialidades = DB::tABLE('especialidad')->get();
       	$doctores = DB::tABLE('users')
        ->join('role_user','role_user.user_IdUsuario','users.IdUsuario')
        ->where('role_id','17')
        ->get();

       	$cli_num_nom = $request->get('cli_num_nom');

        $historias = DB::tABLE('cliente')
        ->leftjoin('historia_clinica','cliente.clicod','historia_clinica.clicod')
        ->leftjoin('sexo','sexo.sex_id','cliente.sex_id')
        ->leftjoin('estado_civil','estado_civil.est_civ_id','cliente.est_civ_id')
        ->leftjoin('tipo_documento_identidad','tipo_documento_identidad.tdicod','cliente.tdicod')
       /* ->where('his_cli_fec','>=',$his_cli_fec)
        ->where('his_cli_fec','<=',$his_cli_fec_fin)*/
        ->where(function ($query) use ($cli_num_nom) {
          if(!empty($cli_num_nom)){
             $query->where('clinom','like','%'.$cli_num_nom.'%')
              ->orWhere('clinum','=',$cli_num_nom);
          }
         
         })
        ->where('his_cli_est','REGISTRADO')
        ->paginate(100);

     


        return view('empresas.historia_clinica.index',compact('especialidades','historias','his_cli_fec','his_cli_fec_fin','cli_num_nom','documentos_identidad','generos','estados','doctores'));

    }

     public function historia_paciente($id)
    {


        
       /*  $his_cli_fec = $request->get('his_cli_fec');
         $his_cli_fec_fin = $request->get('his_cli_fec_fin');

         if(empty($his_cli_fec)){

          $his_cli_fec = now()->modify('first day of this month')->format('Y-m-d');
          $his_cli_fec_fin = now()->modify('last day of this month')->format('Y-m-d');

        }*/

        $documentos_identidad = DB::tABLE('tipo_documento_identidad')->get();
       	$generos = DB::tABLE('sexo')->get();
       	$estados = DB::tABLE('estado_civil')->get();

       	$data_historia = DB::tABLE('historia_clinica')
       	 ->leftjoin('cliente','cliente.clicod','historia_clinica.clicod')
       	 ->where('historia_clinica.his_cli_id',$id)
       	 ->first();
      

        $historias = DB::tABLE('atencion_clinica')
        ->join('historia_clinica','atencion_clinica.his_cli_id','historia_clinica.his_cli_id')
        ->join('cliente','historia_clinica.clicod','cliente.clicod')
        ->leftjoin('sexo','sexo.sex_id','cliente.sex_id')
        ->leftjoin('estado_civil','estado_civil.est_civ_id','cliente.est_civ_id')
        ->join('tipo_documento_identidad','tipo_documento_identidad.tdicod','cliente.tdicod')
        ->join('especialidad','especialidad.esp_id','atencion_clinica.esp_id')
        ->join('users','users.IdUsuario','atencion_clinica.doctor')
        ->where('ate_cli_est','!=','ELIMINADO')

       /* ->where('his_cli_fec','>=',$his_cli_fec)
        ->where('his_cli_fec','<=',$his_cli_fec_fin)*/
        ->where('historia_clinica.his_cli_id',$id)
        ->orderby('ate_cli_est','desc')
        ->orderby('ate_cli_fec','asc')
        ->orderby('ate_cli_hor','asc')
        ->paginate(100);

     


        return view('empresas.historia_clinica.historia_paciente',compact('historias','his_cli_fec','his_cli_fec_fin','cli_num_nom','documentos_identidad','generos','estados','data_historia'));

    }



     public function historia_especialista(Request $request)
    {


        
       /*  $his_cli_fec = $request->get('his_cli_fec');
         $his_cli_fec_fin = $request->get('his_cli_fec_fin');

         if(empty($his_cli_fec)){

          $his_cli_fec = now()->modify('first day of this month')->format('Y-m-d');
          $his_cli_fec_fin = now()->modify('last day of this month')->format('Y-m-d');

        }*/

        $doctor = DB::tABLE('users')->where('IdUsuario',Auth::user()->IdUsuario)->first();

        $documentos_identidad = DB::tABLE('tipo_documento_identidad')->get();
       	$generos = DB::tABLE('sexo')->get();
       	$estados = DB::tABLE('estado_civil')->get();

       	
      

        $historias = DB::tABLE('atencion_clinica')
        ->join('historia_clinica','atencion_clinica.his_cli_id','historia_clinica.his_cli_id')
        ->join('cliente','historia_clinica.clicod','cliente.clicod')
        ->leftjoin('sexo','sexo.sex_id','cliente.sex_id')
        ->leftjoin('estado_civil','estado_civil.est_civ_id','cliente.est_civ_id')
        ->join('tipo_documento_identidad','tipo_documento_identidad.tdicod','cliente.tdicod')
        ->join('especialidad','especialidad.esp_id','atencion_clinica.esp_id')
        ->join('users','users.IdUsuario','atencion_clinica.doctor')
       /* ->where('his_cli_fec','>=',$his_cli_fec)
        ->where('his_cli_fec','<=',$his_cli_fec_fin)*/
        ->where('atencion_clinica.doctor',Auth::user()->IdUsuario)
        ->orderby('ate_cli_est','desc')
        ->orderby('ate_cli_fec','asc')
        ->orderby('ate_cli_hor','asc')

        ->paginate(100);

     


        return view('empresas.historia_clinica.historia_especialista',compact('historias','his_cli_fec','his_cli_fec_fin','cli_num_nom','documentos_identidad','generos','estados','doctor'));

    }


     public function atender_paciente($id)
    {


    
        $documentos_identidad = DB::tABLE('tipo_documento_identidad')->get();
       	$generos = DB::tABLE('sexo')->get();
       	$estados = DB::tABLE('estado_civil')->get();

       	   $historias = DB::tABLE('atencion_clinica')
        ->join('historia_clinica','atencion_clinica.his_cli_id','historia_clinica.his_cli_id')
        ->join('cliente','historia_clinica.clicod','cliente.clicod')
        ->join('sexo','sexo.sex_id','cliente.sex_id')
        ->join('estado_civil','estado_civil.est_civ_id','cliente.est_civ_id')
        ->join('tipo_documento_identidad','tipo_documento_identidad.tdicod','cliente.tdicod')
        ->join('especialidad','especialidad.esp_id','atencion_clinica.esp_id')
        ->join('users','users.IdUsuario','atencion_clinica.doctor')
       /* ->where('his_cli_fec','>=',$his_cli_fec)
        ->where('his_cli_fec','<=',$his_cli_fec_fin)*/
        ->where('ate_cli_id',$id)
        ->first();

       
       	$data_historia = DB::tABLE('historia_clinica')
       	 ->leftjoin('cliente','cliente.clicod','historia_clinica.clicod')
       	 ->where('historia_clinica.his_cli_id',$historias->his_cli_id)
       	 ->first();
      

        return view('empresas.historia_clinica.atencion_paciente',compact('historias','his_cli_fec','his_cli_fec_fin','cli_num_nom','documentos_identidad','generos','estados','data_historia','id'));

    }


        public function editar_atencion($id)
    {


    
        $documentos_identidad = DB::tABLE('tipo_documento_identidad')->get();
       	$generos = DB::tABLE('sexo')->get();
       	$estados = DB::tABLE('estado_civil')->get();

       	   $historias = DB::tABLE('atencion_clinica')
        ->join('historia_clinica','atencion_clinica.his_cli_id','historia_clinica.his_cli_id')
        ->join('cliente','historia_clinica.clicod','cliente.clicod')
        ->join('sexo','sexo.sex_id','cliente.sex_id')
        ->join('estado_civil','estado_civil.est_civ_id','cliente.est_civ_id')
        ->join('tipo_documento_identidad','tipo_documento_identidad.tdicod','cliente.tdicod')
        ->join('especialidad','especialidad.esp_id','atencion_clinica.esp_id')
        ->join('users','users.IdUsuario','atencion_clinica.doctor')
       /* ->where('his_cli_fec','>=',$his_cli_fec)
        ->where('his_cli_fec','<=',$his_cli_fec_fin)*/
        ->where('ate_cli_id',$id)
        ->first();

       
       	$data_historia = DB::tABLE('historia_clinica')
       	 ->leftjoin('cliente','cliente.clicod','historia_clinica.clicod')
       	 ->where('historia_clinica.his_cli_id',$historias->his_cli_id)
       	 ->first();
      

        return view('empresas.historia_clinica.editar_atencion_paciente',compact('historias','his_cli_fec','his_cli_fec_fin','cli_num_nom','documentos_identidad','generos','estados','data_historia','id'));

    }

        public function editar_cita($id)
    {


    
        
        $documentos_identidad = DB::tABLE('tipo_documento_identidad')->get();
       	$generos = DB::tABLE('sexo')->get();
       	$estados = DB::tABLE('estado_civil')->get();
       	$especialidades = DB::tABLE('especialidad')->get();
       	$doctores = DB::tABLE('users')
        ->join('role_user','role_user.user_IdUsuario','users.IdUsuario')
        ->where('role_id','17')
        ->get();


       	   $historias = DB::tABLE('atencion_clinica')
        ->join('historia_clinica','atencion_clinica.his_cli_id','historia_clinica.his_cli_id')
        ->join('cliente','historia_clinica.clicod','cliente.clicod')
        ->leftjoin('sexo','sexo.sex_id','cliente.sex_id')
        ->leftjoin('estado_civil','estado_civil.est_civ_id','cliente.est_civ_id')
        ->join('tipo_documento_identidad','tipo_documento_identidad.tdicod','cliente.tdicod')
        ->leftjoin('especialidad','especialidad.esp_id','atencion_clinica.esp_id')
        ->join('users','users.IdUsuario','atencion_clinica.doctor')
       /* ->where('his_cli_fec','>=',$his_cli_fec)
        ->where('his_cli_fec','<=',$his_cli_fec_fin)*/
        ->where('ate_cli_id',$id)
        ->first();

       
       	$data_historia = DB::tABLE('historia_clinica')
       	 ->leftjoin('cliente','cliente.clicod','historia_clinica.clicod')
       	 ->where('historia_clinica.his_cli_id',$historias->his_cli_id)
       	 ->first();
      

        return view('empresas.historia_clinica.editar_cita',compact('historias','his_cli_fec','his_cli_fec_fin','cli_num_nom','documentos_identidad','generos','estados','data_historia','id','doctores','especialidades'));

    }

    public function registrar_diagnostico(Request $request){

    	$atencion_clinica = atencion_clinica::findOrFail($request->get('id'));
    	$atencion_clinica->alergia =  $request->get('alergia');
    	$atencion_clinica->antecedente = $request->get('antecedente');
    	$atencion_clinica->diagnostico = $request->get('diagnostico');
    	$atencion_clinica->exa_fis = $request->get('exa_fis');
    	$atencion_clinica->fre_car = $request->get('fre_car');
    	$atencion_clinica->fre_res = $request->get('fre_res');
    	$atencion_clinica->int_qui = $request->get('int_qui');
    	$atencion_clinica->mot_con = $request->get('mot_con');
    	$atencion_clinica->talla = $request->get('talla');
    	$atencion_clinica->tratamiento = $request->get('tratamiento');
    	$atencion_clinica->peso = $request->get('peso');
    	$atencion_clinica->pro_cit = $request->get('pro_cit');
    	$atencion_clinica->ate_cli_est = 'ATENDIDO';
    	$atencion_clinica->update();


    	 if($request->ajax()){
             return response()->json(['mensaje'=>'REGISTRADO']);
        }


    }

   


    public function registrar_atencion(Request $request){


    	$cliente = Cliente::UpdateOrCreate(['clinum'=>$request->get('clinum')],['clinom'=>$request->get('clinom'),'clidir'=>$request->get('clidir'),'clicor'=>$request->get('clicor'),'tdicod'=>$request->get('tdicod'),'telefono'=>$request->get('clitel'),'fecha_nacimiento'=>$request->get('fec_nac'),'est_civ_id'=>$request->get('est_civ_id'),'sex_id'=>$request->get('sex_id')]); 

    	$bus_his = historia_clinica::where('clicod',$cliente->clicod)->first();

    	if(!empty($bus_his)){

    		$atencion_clinica = new atencion_clinica;
    		$atencion_clinica->his_cli_id = $bus_his->his_cli_id ;
    		$atencion_clinica->ate_cli_fec = $request->get('ate_cli_fec');
    		$atencion_clinica->ate_cli_hor = $request->get('ate_cli_hor'); 
    		$atencion_clinica->esp_id = $request->get('esp_id');
    		$atencion_clinica->doctor = $request->get('doctor');
    		$atencion_clinica->ate_cli_est = 'PENDIENTE';
    		$atencion_clinica->save();

    	}else{

    		

    		$historia_clinica = new historia_clinica;
    		$historia_clinica->clicod = $cliente->clicod ;
    		$historia_clinica->save();

    		self::generar_numero_historia_clinica($historia_clinica->his_cli_id);

    		$atencion_clinica = new atencion_clinica;
    		$atencion_clinica->his_cli_id = $historia_clinica->his_cli_id ;
    		$atencion_clinica->ate_cli_fec = $request->get('ate_cli_fec');
    		$atencion_clinica->ate_cli_hor = $request->get('ate_cli_hor');
    		$atencion_clinica->esp_id = $request->get('esp_id');
    		$atencion_clinica->doctor = $request->get('doctor');
    		$atencion_clinica->ate_cli_est = 'PENDIENTE';
    		$atencion_clinica->save();

    	}
    	

    	 if($request->ajax()){
             return response()->json(['mensaje'=>'REGISTRADO']);
        }

    	
    }


      public function actualizar_cita(Request $request){


      	$id=$request->get('id');

    	$cliente = Cliente::UpdateOrCreate(['clinum'=>$request->get('clinum')],['clinom'=>$request->get('clinom'),'clidir'=>$request->get('clidir'),'clicor'=>$request->get('clicor'),'tdicod'=>$request->get('tdicod'),'telefono'=>$request->get('clitel'),'fecha_nacimiento'=>$request->get('fec_nac'),'est_civ_id'=>$request->get('est_civ_id'),'sex_id'=>$request->get('sex_id')]); 

   
    		$atencion_clinica = atencion_clinica::findorfail($id);
    		$atencion_clinica->ate_cli_fec = $request->get('ate_cli_fec');
    		$atencion_clinica->ate_cli_hor = $request->get('ate_cli_hor');
    		$atencion_clinica->esp_id = $request->get('esp_id');
    		$atencion_clinica->doctor = $request->get('doctor');
    		$atencion_clinica->ate_cli_est = 'PENDIENTE';
    		$atencion_clinica->update();

    	

    	 if($request->ajax()){
             return response()->json(['mensaje'=>'REGISTRADO']);
        }

    	
    }


     public function generar_numero_historia_clinica($historia){

      	$numero = str_pad($historia,5,"0", STR_PAD_LEFT);

        $gen_cod = DB::tABLE('historia_clinica')->where('his_cli_id',$historia)->update(['his_cli_cod'=>'HC'.$numero]);

        return $gen_cod;
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        
        return view('empresas.aplicativos.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $aplicativos = new aplicativos;
        $aplicativos->IdEmpresa = trim(Auth::user()->IdEmpresa);
        $aplicativos->apli_nom = $request->get('txtApliNom');
        $aplicativos->pago = $request->get('pago');
        $aplicativos->id_empresa_negocio = Auth::user()->id_empresa_negocio;
        $aplicativos->save();
        return Redirect::to('/aplicativos');
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
      
      $aplicativos=aplicativos::findOrFail($id);
      return view('empresas.aplicativos.edit',compact('aplicativos'));
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
      $aplicativos=aplicativos::findOrFail($id);
      $aplicativos->IdEmpresa = trim(Auth::user()->IdEmpresa);
      $aplicativos->apli_nom = $request->get('txt_aplinom');
       $aplicativos->pago = $request->get('pago');
      $aplicativos->update();
      return Redirect::to('/aplicativos');

    }

 

      
    public function eliminar_historia_clinica(Request $request)
    {

      $id = $request->get('id');

      $buscar = historia_clinica::findOrFail($id);
      $buscar->his_cli_est = 'ELIMINADO'; 
      $buscar->update();

      return Redirect::to('/historiaclinica');
    }


      public function eliminar_cita (Request $request)
    {

      $id = $request->get('id');

      $buscar = atencion_clinica::findOrFail($id);
     $buscar->ate_cli_est = 'ELIMINADO'; 
      $buscar->update();

      return Redirect::to('/historia/'.$id);
    }

}
