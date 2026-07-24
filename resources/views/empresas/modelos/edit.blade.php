@extends ('layouts.empresas')
@section ('contenido')

	<section class="content">
 
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                     <div class="box-header" style="background-color:BLUE;">
                         <div class="col-lg-8 col-md-8 col-sm-8 col-xs-8">
                            <font color="white"><center><strong> EDITAR MODELO</strong></center></font>
                          </div>
                    </div>
                    <div class="box-body">

    {!!Form::model($modelo,['method'=>'PATCH','route'=>['modelos.update',$modelo->mod_id],'files'=>'true'])!!}
    {{Form::token()}}
     <div class="row">
        <div hidden="hidden" class="col-lg-3 col-md-3 col-sm-3 col-xs-3">
            <div class="form-group form-group-sm">
                <label for="mod_id">Código</label>
                <input type="text" name="mod_id" value="{{$modelo->mod_id}}" class="form-control" >
                
           </div>
        </div>

        <div class="col-lg-3 col-md-3 col-sm-3 col-xs-3">
            <div class="form-group form-group-sm">
                <label for="mod_nom">Modelo</label>
                <input type="text" name="mod_nom" value="{{$modelo->mod_nom}}" class="form-control">
               
           </div>
        </div>

			

    </div>

    <div class="row">
        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
             <div class="form-group form-group-sm">
                <button class="btn btn-primary" type="submit">Guardar</button>
                <a href="/modelos"><button class="btn btn-danger btn-close" type="button">Cancelar</button></a>
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
