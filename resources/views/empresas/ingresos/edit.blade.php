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
                     <div class="col-lg-8 col-md-8 col-sm-8 col-xs-8">
                        <h4><i class='glyphicon glyphicon-briefcase'></i><strong> EDITAR TIPO DE GASTO</strong></h4>
                      </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-body">


    {!!Form::model($gastos,['method'=>'PATCH','route'=>['gastos.update',$gastos->Id],'files'=>'true'])!!}
    {{Form::token()}}
     <div class="row">
        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
            <div class="form-group">
                <label for="txt_codgasto">Código de Tipo de Gasto</label>
                <input type="text" name="txt_codgasto" value="{{$gastos->codgasto}}" class="form-control" placeholder="">
                  @if ($errors->has('txt_codgasto'))
                        <span class="help-block"><strong><font color="red">{{ $errors->first('txt_codgasto') }}</font></strong></span>
                @endif
           </div>
        </div>

        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
            <div class="form-group">
                <label for="txt_descgasto">Descripcion</label>
                <input type="text" name="txt_descgasto" value="{{$gastos->descgasto}}" class="form-control" placeholder="">
                  @if ($errors->has('txt_descgasto'))
                        <span class="help-block"><strong><font color="red">{{ $errors->first('txt_descgasto') }}</font></strong></span>
                @endif
           </div>
        </div>

        
    </div>
   
     
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
                <a href="{{config('global.ruta')}}/gastos"><button class="btn btn-danger btn-close" type="button">Cancelar</button></a>
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
