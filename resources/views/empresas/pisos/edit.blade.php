@extends ('layouts.empresas')
@section ('contenido')

	<section class="content">
  
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-header" style="background-color:blue;">
                        <font color="white"><center><strong>EDITAR PISO</strong></center></font>
                      
                    </div>
                    <div class="box-body">

    {!!Form::model($piso,['method'=>'PATCH','route'=>['pisos.update',$piso->pis_id,$piso->emp_id],'files'=>'true'])!!}
    {{Form::token()}}
     <div class="row">
        <div hidden="hidden" class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="pis_id">Código</label>
                <input type="text" name="pis_id" value="{{$piso->pis_id}}" class="form-control">
                  @if ($errors->has('pis_id'))
                        <span class="help-block"><strong><font color="red">{{ $errors->first('pis_id') }}</font></strong></span>
                @endif
           </div>
        </div>

        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="pis_nom">Piso</label>
                <input type="text" name="pis_nom" value="{{$piso->pis_nom}}" class="form-control" >
                  @if ($errors->has('pis_nom'))
                        <span class="help-block"><strong><font color="red">{{ $errors->first('pis_nom') }}</font></strong></span>
                @endif
           </div>
        </div>

    </div>

    <div class="row">
        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
             <div class="form-group form-group-sm">
                <button class="btn btn-primary" type="submit">Guardar</button>
                <a href="{{config('global.ruta')}}/mesas"><button class="btn btn-danger btn-close" type="button">Cancelar</button></a>
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
