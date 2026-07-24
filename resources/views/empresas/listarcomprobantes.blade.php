@extends('layouts.consulta')

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
<div class="container" background="img/banner.jpg">
     <div class="row justify-content-center">
    <div class="card card-laravel">   
            <div class="card-header card-header-laravel"> 
                <center><img class="img-responsive img-fluid" width="400px" height="108px" src="img/eiglogo.png"></center>
            </div>
            <div class="card-body">
                 {!!Form::open(array('url'=>'/consultar','autocomplete'=>'off','method'=>'POST','id'=>'formcons','role'=>'form','files'=>'true'))!!}
                 {{Form::token()}}
                    <div class="row justify-content-md-center">

                        <div class="col-lg-10 col-sm-10 col-xs-10 mb-3 ">
                          <label><strong>Empresa Emisora</strong></label>
                            <div class="input-group {{ $errors->has('cmbRucEmp') ? ' has-error' : '' }}" >
                              <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fa fa-user"></i></span>
                              </div>
                              <select name="cmbRucEmp" class="form-control">  
                                <option></option>
                              </select>
                            
                            </div>
                            <span class="help-block">
                            @if ($errors->has('cmbRucEmp'))
                            <strong>{{ $errors->first('cmbRucEmp') }}</strong>
                              @endif
                            </span>
                  
                        </div>
                        <div class="col-lg-10 col-sm-10 col-xs-10  mb-3 ">
                           <label><strong>Serie - Número</strong></label>
                            <div class="input-group  {{ $errors->has('txtSerNum') ? ' has-error' : '' }}">
                              <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fa fa-bars"></i></span>
                              </div>

                          <input type="text" name="txtSerNum" class="form-control" placeholder="Serie-Número">
                      
                            </div>
                            <span class="help-block">
                            @if ($errors->has('txtSerNum'))
                            <strong>{{ $errors->first('txtSerNum') }}</strong>
                              @endif
                        </span>
                  
                        </div>
                        <div class="col-lg-10 col-sm-10 col-xs-10  mb-3 ">
                          <label><strong>Fecha Emisión</strong></label>
                            <div class="input-group  {{ $errors->has('fecEmi') ? ' has-error' : '' }}">

                              
                              <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fa fa-bars"></i></span>
                              </div>

                           <input type="date" id="fecEmi" name="fecEmi" value="{{Carbon::now()->format('Y-m-d')}}" class="form-control">

                      
                            </div>
                            <span class="help-block">
                            @if ($errors->has('fecemi'))
                            <strong>{{ $errors->first('fecemi') }}</strong>
                              @endif
                        </span>
                  
                        </div>
                        <div class="col-lg-10 col-sm-10 col-xs-10  mb-3 ">
                          <label><strong>Monto del Comprobante</strong></label>
                            <div class="input-group  {{ $errors->has('txtMon') ? ' has-error' : '' }}">
                              <div class="input-group-prepend">
                                <span class="input-group-text"><i class="fa fa-bars"></i></span>
                              </div>
                          <input type="text" name="txtMon" class="form-control" placeholder="Monto">
                      
                            </div>
                            <span class="help-block">
                            @if ($errors->has('txtMon'))
                            <strong>{{ $errors->first('txtMon') }}</strong>
                              @endif
                        </span>
                  
                        </div>
                        
                        <div class="col-lg-10 col-sm-10 col-xs-10">
                            <button class="btn btn-lg btn-primary btn-block" type="submit">Consultar</button>
                        </div>

                    </div>
                    
                {!!Form::close()!!}     
            </div>
        </div>
    </div>

</div>
@endsection
