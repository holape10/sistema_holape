@extends ('layouts.empresas')
@section ('contenido')

	<section class="content">
   
   
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                        <div class="box-header with-border" style="background:blue;">
      <font size="2" color="white"><strong><center>DATOS DEL T&Eacute;CNICO</center></strong></font>
    </div>
                    <div class="box-body">
	
	{!!Form::open(array('url'=>'tecnicos','method'=>'POST','autocomplete'=>'off','files'=>'true','id'=>'formcli'))!!}
    {{Form::token()}}
    <div class="row">
      
    	<div class="col-lg-2 col-md-2 col-sm-2 col-xs-12">
    		<div class="form-group form-group-sm">
		       	   <label for="tecnum">Num. Doc</label><img style="display:none;" width="50px" height="50px" src="/img/load.gif" name="imgload" id="imgload">
                    <input type="text"  name="tecnum" id="tecnum" value="{{old('tecnum')}}" onKeypress="if(event.keyCode == 13) buscarcliente();" placeholder="" class="form-control" >
                  
           </div>
    	</div>
    
    	<div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
    		<div class="form-group form-group-sm">
		       	<label for="tecnom">Nombre T&eacute;cnico</label>
		        <input type="text" name="tecnom" id="tecnom" value="{{old('tecnom')}}" class="form-control" >
          
           </div>
    	</div>
        <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
            <div class="form-group form-group-sm">
                <label for="tecdir">Dirección</label>
                <input type="text" name="tecdir" id="tecdir" value="{{old('tecdir')}}" class="form-control" >
          
           </div>
        </div>
 
        <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
            <div class="form-group form-group-sm">
                <label for="teccor">Correo Electrónico</label>
                <input type="text" name="teccor" id="teccor" value="{{old('teccor')}}" class="form-control" >
               
           </div>
        </div>
           <div class="col-lg-4 col-md-4 col-sm-12 col-xs-12">
        <div class="form-group form-group-sm">
          <label>Tel&eacute;fono</label>
          <input name="tectel" id="tectel" value="--" class="form-control">

        </div>
      </div>
    	   
      </div>
         <div class="row">
        <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
             <div class="form-group form-group-sm">
                <button class="btn btn-primary" type="submit">Guardar</button>
                <a href="{{config('global.ruta')}}/tecnicos"><button class="btn btn-danger btn-close" type="button">Cancelar</button></a>
            </div>
        </div>
    </div>

    </div>
 
</div>
       </div>
            </div>
       </div>
   </section>
	{!!Form::close()!!}		
@endsection