@extends ('layouts.empresas')
@section ('contenido')
<script>
$(document).ready(function()
{

        $("#txt_provun").on('change',function(){
            var numdoc = $('#txt_provun').val();
            $("#txt_propun").val((numdoc*1.18).toFixed(3));
        })

          $("#txt_propun").on('change',function(){
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
                        <h4><i class='glyphicon glyphicon-user'></i><strong> EDITAR APLICATIVO</strong></h4>
                      </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-body">

    {!!Form::model($aplicativos,['method'=>'PATCH','route'=>['aplicativos.update',$aplicativos->apli_id],'files'=>'true'])!!}
    {{Form::token()}}
     <div class="row">
        <div hidden="hidden" class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="txt_procod">Aplicativo</label>
                <input type="text" name="txt_procod" value="{{$aplicativos->apli_id}}" class="form-control" >
                  @if ($errors->has('txt_'))
                        <span class="help-block"><strong><font color="red">{{ $errors->first('txt_procod') }}</font></strong></span>
                @endif
           </div>
        </div>


      

        <div class="col-lg-4 col-md-4 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="txt_aplinom">Aplicativo</label>
                <input type="text" name="txt_aplinom" value="{{$aplicativos->apli_nom}}" class="form-control" >
                  @if ($errors->has('txt_aplinom'))
                        <span class="help-block"><strong><font color="red">{{ $errors->first('txt_aplinom') }}</font></strong></span>
                @endif
           </div>
        </div>
     
    </div>
    <div class="row">
        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
             <div class="form-group form-group-sm">
                <button class="btn btn-primary" type="submit">Guardar</button>
                <a href="{{config('global.ruta')}}/categorias"><button class="btn btn-danger btn-close" type="button">Cancelar</button></a>
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
