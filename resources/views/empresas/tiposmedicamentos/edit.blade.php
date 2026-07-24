@extends ('layouts.empresas')
@section ('contenido')

	<section class="content">
    <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-body">
                     <div class="col-lg-8 col-md-8 col-sm-8 col-xs-8">
                        <h4><i class='glyphicon glyphicon-user'></i><strong> EDITAR TIPO DE MEDICAMENTO</strong></h4>
                      </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-body">

    {!!Form::model($tipo_medicamento,['method'=>'PATCH','route'=>['tiposmedicamentos.update',$tipo_medicamento->id_tip_med],'files'=>'true'])!!}
    {{Form::token()}}
     <div class="row">
      

        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
            <div class="form-group">
                <label for="txtTipMed">Nombre de Categoría</label>
                <input type="text" name="txtTipMed" value="{{$tipo_medicamento->descripcion}}" class="form-control" placeholder="">
                  @if ($errors->has('txtTipMed'))
                        <span class="help-block"><strong><font color="red">{{ $errors->first('txtTipMed') }}</font></strong></span>
                @endif
           </div>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
             <div class="form-group">
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
