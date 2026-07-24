
@extends('layouts.empresas')
@section('contenido')
 <section class="content">
     <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-body">
                     <div class="col-lg-8 col-md-8 col-sm-8 col-xs-8">
                        <h4><i class=''></i><strong> CAMBIAR CONTRASEÑA</strong></h4>
                 </div>
                    </div>
                </div>
            </div>
        </div>

    <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-body">
	{!!Form::open(array('url'=>'proveedor/cambiar/contrasena','method'=>'POST','autocomplete'=>'off','files'=>'false'))!!}
    {{Form::token()}}
    <div class="row">
		        <input readonly type="hidden" name="idUsuario" value="{{$usuario->IdUsuario}}" class="form-control">

    	</div>

    	<div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
    		<div class="form-group">
		       	<label for="nomUsuario">Nombres</label>
		        <input readonly type="text" name="nomUsuario" value="{{$usuario->name}}" class="form-control">
           </div>
    	</div>
    	<div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
    		<div class="form-group">
		       	<label for="apeUsuario">Apellidos</label>
		        <input readonly type="text" name="apeUsuario" value="{{$usuario->ApeUsuario}}" class="form-control">
           </div>
    	</div>
    	<div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
    		<div class="form-group">
                <label for="email">Correo</label>
                <input readonly type="text" name="email" value="{{$usuario->email}}" class="form-control">
           </div>
    	</div>
    	<div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
            <div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">
                <label for="password">Contraseña</label><font color="red">*</font>
                <input id="password" type="password" class="form-control" name="password" required placeholder="Contraseña...">
                    @if ($errors->has('password'))
                        <span class="help-block"><strong><font color="red">{{ $errors->first('password') }}</font></strong></span>
                    @endif
            </div>
        </div>

        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
            <div class="form-group">
                <label for="password-confirm">Confirmar contraseña</label><font color="red">*</font>
                <input id="" class="form-control" type="password" class="form-control" name="password_confirmation" placeholder="Confirmar Contraseña..." require>
            </div>
        </div>
    </div>
    <div class="row">
    	<div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
    		 <div class="form-group">
            	<button class="btn btn-primary" type="submit">Guardar</button>
            	<a href="{{config('global.ruta')}}/SisFact"><button type="button" class="btn btn-danger">Cancelar</button></a>
            </div>
    	</div>
    </div>
</div>
</div>
</div>
</section>
	{!!Form::close()!!}
@endsection
