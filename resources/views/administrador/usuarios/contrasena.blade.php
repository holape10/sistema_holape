@if(Auth::user()->hasRole('admin'))
@extends('layouts.empresas')
@section('contenido')
<section class="content">
  
    <div class="row">
            <div class="col-lg-12">
                <div class="box">
                     <div class="box-header" style="background:blue;">
                        <font size="3" color="white"><center><strong>CAMBIAR CONTRASEÑA</strong></center></font>
                     
                    </div>
                    <div class="box-body">
    {!!Form::open(array('url'=>'administrador/cambiar/contrasena','method'=>'POST','autocomplete'=>'off','files'=>'false'))!!}
    {{Form::token()}}
        <div class="row">
            <input readonly type="hidden" name="idUsuario" value="{{$usuario->IdUsuario}}" class="form-control">
    
        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="nomUsuario">Nombres</label>
                <input readonly type="text" name="nomUsuario" value="{{$usuario->name}}" class="form-control">
           </div>
        </div>
        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="apeUsuario">Apellidos</label>
                <input readonly type="text" name="apeUsuario" value="{{$usuario->apeusu}}" class="form-control">
           </div>
        </div>
        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="email">Correo</label>
                <input readonly type="text" name="email" value="{{$usuario->email}}" class="form-control">
           </div>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm{{ $errors->has('password') ? ' has-error' : '' }}">
                <label for="password">Contraseña Acceso</label><font color="red">*</font>
                <input id="password" type="password" class="form-control" name="password"  placeholder="">
                    @if ($errors->has('password'))
                        <span class="help-block"><strong><font color="red">{{ $errors->first('password') }}</font></strong></span>
                    @endif
            </div>
        </div>

        
            <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
                <div class="form-group form-group-sm">
                    <label for="password-confirm">Confirmar contraseña Acceso</label><font color="red">*</font>
                    <input id="" class="form-control" type="password" class="form-control" name="password_confirmation" placeholder=" " require>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="passwordadmin">Contraseña Supervisor</label><font color="red">*</font>
                <input id="passwordadmin" type="password" class="form-control" name="passwordadmin" placeholder="">
            </div>
        </div>

        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="password-confirm-admin">Confirmar contraseña Supervisor</label><font color="red">*</font>
                <input id="" class="form-control" type="password" class="form-control" name="password_confirmation-admin" placeholder="" >
            </div>
        </div>
      
    </div>
    <div class="row">
        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
             <div class="form-group form-group-sm">
                <button class="btn btn-primary" type="submit">Guardar</button>
                <a href="/administrador/usuarios"><button type="button" class="btn btn-danger">Cancelar</button></a>
            </div>
        </div>
    </div>

    {!!Form::close()!!} 
</div>
    </div>
    </div>
    </div>
    </section>
@endsection
@endif