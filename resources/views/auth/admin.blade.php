@extends('layouts.app')

@section('content')
<style>
    .card-header-laravel{
        background:transparent;
        border:transparent;
    }
    .card-laravel{
       border:transparent;
        width: 50%;
    }
    

</style>
<div class="container">
     <div class="row justify-content-center">
    <div class="card card-laravel">   
            <div class="card-header card-header-laravel"> 
                <center><img class="img-responsive img-fluid" width="400px" height="108px" src=""></center>
            </div>
            <div class="card-body">
                <form class="form-horizontal" autocomplete="off"  method="POST" action="{{ route('login') }}">
                {{ csrf_field() }}
                    <div class="row justify-content-md-center">
                        <div class="col-lg-10 col-sm-10 col-xs-10 mb-3 ">
                            <div class="input-group {{ $errors->has('email') ? ' has-error' : '' }}" >
                              <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fa fa-user"></i></span>
                              </div>
                              <input type="text" name="email" id="email" value="{{ old('email') }}" class="form-control" placeholder="Usuario">
                            </div>
                            <span class="help-block">
                            @if ($errors->has('email'))
                            <strong>{{ $errors->first('email') }}</strong>
                              @endif
                            </span>
                  
                        </div>
                   
                        <div class="col-lg-10 col-sm-10 col-xs-10 mb-3">
                            <div class="input-group {{ $errors->has('password') ? ' has-error' : '' }}">
                              <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fa fa-lock"></i></span>
                              </div>
                              <input type="password" name="password" value="{{ old('password') }}" id="password" class="form-control" placeholder="Contraseña">
                            </div>
                            <span class="help-block">
                            @if ($errors->has('password'))
                            <strong>{{ $errors->first('password') }}</strong>
                              @endif
                        </span>
                  
                        </div>
                        <div class="col-lg-10 col-sm-10 col-xs-10">
                            <button id="" style="color:#fff;background-color:#1F618D; border-color:#1F618D;" class="btn btn-lg btn-default btn-block" type="submit">INGRESAR</button>
                        </div>
                        <div class="col-lg-10 col-sm-10 col-xs-10" align="right">
                            <a href="{{ route('password.request') }}">Recuperar Contraseña</a>
                        </div>
                    </div>
                     <input  type="hidden" class="form-control input-lg" name="rol" value="administrador" >
                </form>
            </div>
        </div>
    </div>

</div>
@endsection
