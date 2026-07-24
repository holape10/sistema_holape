@extends ('layouts.empresas')
@section ('contenido')
<script>
$(document).ready(function()
{  
        
        $("#txt_provun").on('keyup',function(){
            var numdoc = $('#txt_provun').val();
            $("#txt_propun").val((numdoc*1.18).toFixed(3));
        })

          $("#txt_propun").on('keyup',function(){
            var numdoc = $('#txt_propun').val();
            $("#txt_provun").val((numdoc/1.1055).toFixed(3));
        })



       
});
</script>
	<section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-body">
                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                        <h4><i class='glyphicon glyphicon-briefcase'></i><strong> Nuevo Tipo Gastos</strong></h4>
                     </div>
                    </div>
                </div>
            </div>
        </div>
   
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-body">

	{!!Form::open(array('url'=>'gastos','method'=>'POST','autocomplete'=>'off','files'=>'true'))!!}
    {{Form::token()}}
    <div class="row">
        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
            <div class="form-group">
                <label for="txt_codgasto">Código de gastos</label>
                <input type="text" name="txt_codgasto" value="{{old('txt_codgasto')}}" class="form-control" placeholder="">
                  @if ($errors->has('txt_codgasto'))
                        <span class="help-block"><strong><font color="red">{{ $errors->first('txt_codgasto') }}</font></strong></span>
                @endif
           </div>
        </div>
          <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
            <div class="form-group">
                <label for="txt_descgasto">Descripcion</label>
                <input type="text" name="txt_descgasto" value="{{old('txt_descgasto')}}" class="form-control" placeholder="">
                  @if ($errors->has('txt_descgasto'))
                        <span class="help-block"><strong><font color="red">{{ $errors->first('txt_descgasto') }}</font></strong></span>
                @endif
           </div>
        </div>
         
        </div>
       
     
    <div class="row">
       
    </div>
    <div class="row">
    	<div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
    		<div class="form-group">
            	<input type="hidden" readonly name="txt_rucemp" class="form-control" value="{{Auth::user()->IdEmpresa}}">
            </div>
        </div>
    </div>

    <div class="row">
    	<div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
    		 <div class="form-group">
            	<button class="btn btn-primary" type="submit">Guardar</button>
            	<a href="{{config('global.ruta')}}/gastoss"><button class="btn btn-danger btn-close" type="button">Cancelar</button></a>
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