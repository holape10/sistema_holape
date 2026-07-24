@extends ('layouts.empresas')
@section ('contenido')
	<section class="content">
       
   
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                      <div class="box-header with-border" style="background-color:blue;">
        <center><font color="white"><strong>REGISTRAR CLIENTE</strong></font></center>
     </div>
                    <div class="box-body">
	
	{!!Form::open(array('url'=>'clientes','method'=>'POST','autocomplete'=>'off','files'=>'true'))!!}
    {{Form::token()}}
    <div class="row">
        <div class="col-lg-2 col-md-2 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="tdicod">Tipo Documento Ident.</label>
                <select class="form-control"  name="tdicod" id="tdicod">
                    <option></option>
                    @foreach($documentos as $doc)
                        <option value="{{$doc->tdicod}}">{{$doc->tdides}}</option>
                    @endforeach
                </select>
               
           </div>
        </div>
    	<div class="col-lg-2 col-md-2 col-sm-6 col-xs-6">
    		<div class="form-group form-group-sm">
		       	<label for="clinum">Número Documento Ident.</label>
		        <input type="text" name="clinum" value="{{old('clinum')}}" class="form-control" placeholder="Número de Documento de Identidad...">
                 
                 
           </div>
    	</div>
  
    	<div class="col-lg-5 col-md-5 col-sm-6 col-xs-6">
    		<div class="form-group form-group-sm">
		       	<label for="clinom">Razón Social</label>
		        <input type="text" name="clinom" value="{{old('clinom')}}" class="form-control" placeholder="Nombre de Cliente ó Razón Social...">
               
           </div>
    	</div>
        <div class="col-lg-4 col-md-4 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="clidir">Dirección Principal</label>
                <input type="text" name="clidir" value="{{old('clidir')}}" class="form-control" placeholder="Dirección...">
                
           </div>
        </div>
        <div class="col-lg-4 col-md-4 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="clidir">Dirección 1</label>
                <input type="text" name="clidir1" value="{{old('clidir1')}}" class="form-control" placeholder="Dirección...">
                
           </div>
        </div>
        <div class="col-lg-4 col-md-4 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="clidir">Dirección 2</label>
                <input type="text" name="clidir2" value="{{old('clidir2')}}" class="form-control" placeholder="Dirección...">
                
           </div>
        </div>
        <div class="col-lg-4 col-md-4 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="clidir">Dirección 3</label>
                <input type="text" name="clidir3" value="{{old('clidir3')}}" class="form-control" placeholder="Dirección...">
                
           </div>
        </div>
        <div class="col-lg-4 col-md-4 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="clidir">Dirección 4</label>
                <input type="text" name="clidir4" value="{{old('clidir4')}}" class="form-control" placeholder="Dirección...">
                
           </div>
        </div>
        <div class="col-lg-4 col-md-4 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="clidir">Dirección 5</label>
                <input type="text" name="clidir5" value="{{old('clidir5')}}" class="form-control" placeholder="Dirección...">
                
           </div>
        </div>
         <div class="col-lg-3 col-md-3 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="clitel">Tel&eacute;fono</label>
                <input type="text" name="clitel" value="{{old('telefono')}}" class="form-control" placeholder="Teléfono...">
                
           </div>
        </div>
         <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
            <div class="form-group form-group-sm">
                <label for="vendedores">Vendedores</label>

                    <select name="vendedores"  class="form-control">  
                    <option></option>  
                        @foreach($vendedores as $vendedor)
                          
                             <option value="{{$vendedor->IdUsuario}}">{{$vendedor->name}} {{$vendedor->apeusu}}</option>
                            
                        @endforeach 
                    </select>                         
            
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="clicor">Correo Electrónico</label>
                <input type="text" name="clicor" value="{{old('clicor')}}" class="form-control" placeholder="Correo Eléctronico...">
                 
           </div>
        </div>
         <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="clicor">Correo Electrónico 2</label>
                <input type="text" name="clicor2" value="{{old('clicor2')}}" class="form-control" placeholder="Correo Eléctronico...">
                 
           </div>
        </div>
         <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="clicor">Correo Electrónico 3</label>
                <input type="text" name="clicor3" value="{{old('clicor3')}}" class="form-control" placeholder="Correo Eléctronico...">
                 
           </div>
        </div>
         <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="clicor">Correo Electrónico 4</label>
                <input type="text" name="clicor4" value="{{old('clicor4')}}" class="form-control" placeholder="Correo Eléctronico...">
                 
           </div>
        </div>
          <div hidden="hidden"  class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="clifecna">Fecha Nacimiento</label>
                <input type="date" name="clifecnac" class="form-control" value="{{old('clifecnac')}}">
                 
           </div>
        </div>
    	
    </div>
    <div class="row">
    	<div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
    		 <div class="form-group form-group-sm">
            	<button class="btn btn-primary" type="submit">Guardar</button>
            	<a href="{{config('global.ruta')}}/clientes"><button class="btn btn-danger btn-close" type="button">Cancelar</button></a>
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