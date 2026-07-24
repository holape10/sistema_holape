@extends ('layouts.empresas')
@section ('contenido')
	<section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-body">
                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                            <h4><i class='glyphicon glyphicon-user'></i><strong> NUEVO TIPO DE CAMBIO</strong></h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
   
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-body">
	
	{!!Form::open(array('url'=>'tipocambio','method'=>'POST','autocomplete'=>'off','files'=>'true'))!!}
    {{Form::token()}}
    <div class="row">
    	<div class="col-lg-2 col-md-2 col-sm-2 col-xs-6">
    		<div class="form-group">
		       	<label for="fecha">Fecha</label>
                {!!Form::date('fecha',Carbon::now()->format('Y-m-d'),['class'=>'form-control input-sm','id'=>'fecha']);!!}
                  @if ($errors->has('fecha'))
                        <span class="help-block"><strong><font color="red">{{ $errors->first('Fecha') }}</font></strong></span>
                @endif
           </div>
    	</div>
        <div class="col-lg-3 col-md-3 col-sm-3 col-xs-6">
            <div class="form-group">
                <label for="tccompra">TC Compra</label>
                <input type="text" name="tccompra" min="1" value="{{old('tccompra')}}" class="form-control" placeholder="Tipo de Cambio Compra..">
                  @if ($errors->has('tccompra'))
                        <span class="help-block"><strong><font color="red">{{ $errors->first('tccompra') }}</font></strong></span>
                @endif
           </div>
        </div>
        <div class="col-lg-3 col-md-3 col-sm-3 col-xs-6">
            <div class="form-group">
                <label for="tcventa">TC Venta</label>
                <input type="text" name="tcventa" min="1" value="{{old('tcventa')}}" class="form-control" placeholder="Tipo de Cambio Venta..">
                  @if ($errors->has('tcventa'))
                        <span class="help-block"><strong><font color="red">{{ $errors->first('tcventa') }}</font></strong></span>
                @endif
           </div>
        </div>
    
    </div>
    
    <div class="row">
    	<div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
    		 <div class="form-group">
            	<button class="btn btn-primary" type="submit">Guardar</button>
            	<a href="{{route('tipocambio.index')}}"><button class="btn btn-danger btn-close" type="button">Cancelar</button></a>
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