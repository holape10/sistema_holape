@extends('layouts.app')
@section('content')

@php

  $sucursales= DB::TABLE('empresa_negocios')->get();
  $terminales = DB::tABLE('configuracion_impresoras')->get();
@endphp
     <div class="row justify-content-center">
    <div class="card card-laravel" style="background:transparent;">   
            <div class="card-body" >
                <form class="form-horizontal" autocomplete="off"  method="POST" action="/login">
                {{ csrf_field() }}
                    <div class="row justify-content-md-center">
                        <div class="col-lg-10 col-sm-10 col-xs-12 mb-3 ">
                            <div class="input-group {{ $errors->has('email') ? ' has-error' : '' }}" >
                              <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fa fa-user"></i></span>
                              </div>
                              <input type="text" name="email"  style="background:#F2F4F4 ;" id="email" value="{{ old('email') }}" class="form-control" placeholder="Usuario">
                            </div>
                            <span class="help-block">
                         
                            </span>
                  
                        </div>
                            <div class="col-lg-10 col-sm-10 col-xs-12  mb-3 ">
                            <div class="input-group  {{ $errors->has('IdEmpresa') ? ' has-error' : '' }}">
                              <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fa fa-bars"></i></span>
                              </div>

                                
                          

                              <select class="form-control" name="sucursal" style="background:#F2F4F4 ;">
                                 @foreach($sucursales as $sucursal)
                                 <option value="{{$sucursal->id_empresa_negocio}}">{{$sucursal->tipo_negocio}}</option>
                                 @endforeach
                              </select>
                            </div>
                       
                        </div>

                       

                        <div class="col-lg-10 col-sm-10 col-xs-12 mb-3">
                            <div class="input-group {{ $errors->has('password') ? ' has-error' : '' }}">
                              <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fa fa-lock"></i></span>
                              </div>
                              <input type="password" style="background:#F2F4F4 ;" name="password" value="{{ old('password') }}" id="password" class="form-control" placeholder="Contraseña">
                            </div>
                            <span class="help-block">
                           
                        </span>
                  
                        </div>

                        <div class="col-lg-10 col-sm-10 col-xs-10  mb-3 ">
                            
                            <div class="input-group">
                              <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fa fa-bars"></i></span>
                              </div>

                              <select class="form-control" name="terminal" style="background:#F2F4F4 ;">
                              
                                 @foreach($terminales as $terminal)
                                 <option value="{{$terminal->Id}}">{{$terminal->descripcion}}</option>
                                 @endforeach
                              </select>
                            </div>
                       
                        </div>

                        <div class="col-lg-10 col-sm-10 col-xs-12">
                            <button id="" style="color:#fff;background-color:#1F618D; border-color:#1F618D;" class="btn btn-lg btn-default btn-block" type="submit">INGRESAR</button>
                        </div>
                        <div class="col-lg-10 col-sm-10 col-xs-10" align="right">
                          <!--  <a href="{{ route('password.request') }}">Recuperar Contraseña</a>-->
                        </div>
                    </div>
                   
                </form>
            </div>
        </div>
</div>

@endsection
