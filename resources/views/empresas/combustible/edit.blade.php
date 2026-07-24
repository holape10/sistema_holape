@extends ('layouts.empresas')
@section ('contenido')

    <section class="content">

        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                      <div class="box-header" style="background-color:blue;">
                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                            <font color="white"><strong><center>Editar Nivel Combustible</center></strong></font>
                        </div>
                    </div>
                    <div class="box-body">

    {!!Form::model($combustible,['method'=>'PATCH','route'=>['combustible.update',$combustible->comb_id],'files'=>'true'])!!}
    {{Form::token()}}
     <div class="row">
        <div hidden="hidden" class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="comb_id">Código</label>
                <input type="text" name="comb_id" value="{{$combustible->comb_id}}" class="form-control" placeholder="">
              
           </div>
        </div>


      

        <div class="col-lg-4 col-md-4 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="comb_nom">Nombre de Categoría</label>
                <input type="text" name="comb_nom" value="{{$combustible->comb_nom}}" class="form-control" placeholder="">
                
           </div>
        </div>
      
    </div>
    <div class="row">
        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
             <div class="form-group form-group-sm">
                <button class="btn btn-primary" type="submit">Guardar</button>
                <a href="/combustible"><button class="btn btn-danger btn-close" type="button">Cancelar</button></a>
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
