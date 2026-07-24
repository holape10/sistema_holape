@extends ('layouts.empresas')
@section ('contenido')

	<section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                     <div class="box-header" style="background-color:blue;">
                        <font color="white"><center><strong> Editar Tipo de Equipo</strong></center></font>
                    </div>
                    <div class="box-body">

    {!!Form::model($tipo_equipo,['method'=>'PATCH','route'=>['tipoequipo.update',$tipo_equipo->id_tip_equi],'files'=>'true'])!!}
    {{Form::token()}}
     <div class="row">
        <div hidden="hidden" class="col-lg-3 col-md-3 col-sm-3 col-xs-3">
            <div class="form-group form-group-sm">
                <label for="id_tip_equi">Código</label>
                <input type="text" name="id_tip_equi" value="{{$tipo_equipo->id_tip_equi}}" class="form-control" >
                
           </div>
        </div>

        <div class="col-lg-3 col-md-3 col-sm-3 col-xs-3">
            <div class="form-group form-group-sm">
                <label for="nom_tip_equi">Modelo</label>
                <input type="text" name="nom_tip_equi" value="{{$tipo_equipo->nom_tip_equi}}" class="form-control">
               
           </div>
        </div>

			

    </div>

    <div class="row">
        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
             <div class="form-group form-group-sm">
                <button class="btn btn-primary" type="submit">Guardar</button>
                <a href="/tipoequipo"><button class="btn btn-danger btn-close" type="button">Cancelar</button></a>
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
