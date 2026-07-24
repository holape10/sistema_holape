@extends ('layouts.empresas')
@section ('contenido')

	<section class="content">
    <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-body">
                     <div class="col-lg-8 col-md-8 col-sm-8 col-xs-8">
                        <h4><i class='glyphicon glyphicon-user'></i><strong> EDITAR PRINCIPIO ACTIVO</strong></h4>
                      </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-body">

    {!!Form::model($principioactivo,['method'=>'PATCH','route'=>['principioactivo.update',$principioactivo->pri_act_id],'files'=>'true'])!!}
    {{Form::token()}}
     <div class="row">
      

        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
            <div class="form-group">
                <label for="pri_act_nom">Principio Activo</label>
                <input type="text" name="pri_act_nom" value="{{$principioactivo->pri_act_nom}}" class="form-control" placeholder="">
              
           </div>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
             <div class="form-group">
                <button class="btn btn-primary" type="submit">Guardar</button>
                <a href="/principioactivo"><button class="btn btn-danger btn-close" type="button">Cancelar</button></a>
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
