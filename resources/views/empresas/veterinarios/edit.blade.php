@extends ('layouts.empresas')
@section ('contenido')
	
<section class="content">
   
   
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                      <div class="box-header with-border" style="background-color:blue;">
        <center><font color="white"><strong>EDITAR CLIENTE</strong></font></center>
     </div>
                    <div class="box-body">

    {!!Form::model($clientes,['method'=>'PATCH','route'=>['clientes.update',$clientes->clicod,$clientes->rucemp],'files'=>'true'])!!}
    {{Form::token()}}
    <div class="row">
        <div class="col-lg-2 col-md-2 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="tdicod">Tipo Documento Ident.</label>
                <select class="form-control"  name="tdicod" id="tdicod">
                    <option></option>
                    @foreach($documentos as $doc)
                         @if($doc->tdicod==$clientes->tdicod)
                            <option value="{{$doc->tdicod}}" selected>{{$doc->tdides}}</option>
                        @else
                            <option value="{{$doc->tdicod}}">{{$doc->tdides}}</option>
                        @endif 
                    @endforeach
                </select>
               
           </div>
        </div>
    	<div  class="col-lg-2 col-md-2 col-sm-6 col-xs-6">
    		<div class="form-group form-group-sm">
		       	<label for="clinum">Número Documento Ident.</label>
		        <input type="text" name="clinum" readonly="readonly" value="{{$clientes->clinum}}" class="form-control" placeholder="Número de Documento de Identidad...">
               
           </div>
    	</div>
    
    	<div class="col-lg-5 col-md-5 col-sm-6 col-xs-6">
    		<div class="form-group form-group-sm">
		       	<label for="clinom">Razón Social</label>
		        <input type="text" name="clinom" value="{{$clientes->clinom}}" class="form-control" placeholder="Nombre de Cliente ó Razón Social...">
                 
           </div>
    	</div>
        <div class="col-lg-4 col-md-4 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="clidir">Dirección Principal</label>
                <input type="text" name="clidir" value="{{$clientes->clidir}}" class="form-control" placeholder="Dirección...">
                  
           </div>
        </div>
         <div class="col-lg-4 col-md-4 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="clidir">Dirección 1</label>
                <input type="text" name="clidir1" value="{{$clientes->direccion1}}" class="form-control" placeholder="Dirección...">
                
           </div>
        </div>
        <div class="col-lg-4 col-md-4 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="clidir">Dirección 2</label>
                <input type="text" name="clidir2" value="{{$clientes->direccion2}}" class="form-control" placeholder="Dirección...">
                
           </div>
        </div>
        <div class="col-lg-4 col-md-4 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="clidir">Dirección 3</label>
                <input type="text" name="clidir3" value="{{$clientes->direccion3}}" class="form-control" placeholder="Dirección...">
                
           </div>
        </div>
        <div class="col-lg-4 col-md-4 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="clidir">Dirección 4</label>
                <input type="text" name="clidir4" value="{{$clientes->direccion4}}" class="form-control" placeholder="Dirección...">
                
           </div>
        </div>
        <div class="col-lg-4 col-md-4 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="clidir">Dirección 5</label>
                <input type="text" name="clidir5" value="{{$clientes->direccion5}}" class="form-control" placeholder="Dirección...">
                
           </div>
        </div>
         <div class="col-lg-3 col-md-3 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="clitel">Tel&eacute;fono</label>
                <input type="text" name="clitel" value="{{$clientes->telefono}}" class="form-control" placeholder="Dirección...">
                  
           </div>
        </div>
         <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
            <div class="form-group form-group-sm">
                <label for="vendedores">Vendedores</label>
                    <select name="vendedores"  class="form-control">  
                    <option></option>  
                        @foreach($vendedores as $vendedor)
                            @if($vendedor->IdUsuario == $clientes->vendedor)
                            <option selected="selected" value="{{$vendedor->IdUsuario}}">{{$vendedor->name}} {{$vendedor->apeusu}}</option>
                            @else
                             <option value="{{$vendedor->IdUsuario}}">{{$vendedor->name}} {{$vendedor->apeusu}}</option>
                            @endif
                        @endforeach 
                    </select>                         
            
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-3 col-md-3 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="clicor">Correo Electrónico</label>
                <input type="text" name="clicor" value="{{$clientes->clicor}}" class="form-control" placeholder="Correo Eléctronico...">
                  @if ($errors->has('clicor'))
                        <span class="help-block"><strong><font color="red">{{ $errors->first('clicor') }}</font></strong></span>
                @endif
           </div>
        </div>

        <div class="col-lg-3 col-md-3 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="clicor">Correo Electrónico 2</label>
                <input type="text" name="clicor2" value="{{$clientes->clicor2}}" class="form-control" placeholder="Correo Eléctronico...">
               
           </div>
        </div>

        <div class="col-lg-3 col-md-3 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="clicor">Correo Electrónico 3</label>
                <input type="text" name="clicor3" value="{{$clientes->clicor3}}" class="form-control" placeholder="Correo Eléctronico...">
                 
           </div>
        </div>

        <div class="col-lg-3 col-md-3 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="clicor4">Correo Electrónico 4</label>
                <input type="text" name="clicor4" value="{{$clientes->clicor4}}" class="form-control" placeholder="Correo Eléctronico...">
                 
           </div>
        </div>
        <div hidden="hidden" class="col-lg-3 col-md-3 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="clifecna">Fecha Nacimiento</label>
                <input type="date" name="clifecnac" class="form-control" value="{{$clientes->clifecnac}}">
                 
           </div>
        </div>
        <div class="col-lg-2 col-md-2 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="cliest">Estado</label>
                    <select name="cliest"  class="form-control">    
                    @if($clientes->cliest=='Activo')
                        <option value="{{$clientes->cliest}}" selected>{{$clientes->cliest}}</option>
                        <option value="Inactivo">Inactivo</option>
                    @else
                        <option value="{{$clientes->cliest}}" >{{$clientes->cliest}}</option>
                        <option value="Activo">Activo</option>
                    @endif                                     
                </select>
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