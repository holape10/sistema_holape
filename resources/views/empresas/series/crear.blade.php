@extends ('layouts.empresas')
@section ('contenido')
	<section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-body">
                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                            <h4><i class='glyphicon glyphicon-user'></i><strong> NUEVA SERIE</strong></h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
   
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-body">
	
	{!!Form::open(array('url'=>'series','method'=>'POST','autocomplete'=>'off','files'=>'true'))!!}
    {{Form::token()}}
    <div class="row">
        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
            <div class="form-group">
                <label for="tdocod">Tipo Documento</label>
                <select class="form-control"  name="tdocod" id="tdocod">
                    @foreach($tipo_doc as $doc)
                        <option value="{{$doc->tdocod}}">{{$doc->tdodes}}</option>
                    @endforeach
                </select>
                @if ($errors->has('tdocod'))
                        <span class="help-block"><strong><font color="red">{{ $errors->first('tdocod') }}</font></strong></span>
                @endif
           </div>
        </div>
    	<div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
    		<div class="form-group">
		       	<label for="serie">Serie</label>
		        <input type="text" name="serie" min="0" value="{{old('serie')}}" class="form-control" placeholder="Número de Serie...">
                  @if ($errors->has('serie'))
                        <span class="help-block"><strong><font color="red">{{ $errors->first('serie') }}</font></strong></span>
                @endif
           </div>
    	</div>
    </div>
    <div class="row">
    	<div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
    		<div class="form-group">
		       	<label for="numcor">Correlativo</label>
		        <input type="number" name="numcor" min="0" value="{{old('numcor')}}" class="form-control" placeholder="Número Correlativo..">
                  @if ($errors->has('numcor'))
                        <span class="help-block"><strong><font color="red">{{ $errors->first('numcor') }}</font></strong></span>
                @endif
           </div>
    	</div>
    
    </div>
  
    <div class="row">
    	<div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
    		 <div class="form-group">
            	<button class="btn btn-primary" type="submit">Guardar</button>
            	<a href="{{route('series.index')}}"><button class="btn btn-danger btn-close" type="button">Cancelar</button></a>
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