@extends ('layouts.empresas')
@section ('contenido')

    <section class="content">
    

        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                      <div class="box-header with-border" style="background-color:blue;">
        <center><font color="white"><strong>EDITAR EVALUACI&Oacute;N</strong></font></center>
     </div>
                    <div class="box-body">

    {!!Form::model($evaluaciones,['method'=>'PATCH','route'=>['evaluaciones.update',$evaluaciones->eval_id],'files'=>'true'])!!}
    {{Form::token()}}
     <div class="row">
        <div hidden="hidden" class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="eval_id">Código de Categoría</label>
                <input type="text" name="eval_id" value="{{$evaluaciones->eval_id}}" class="form-control">
                
           </div>
        </div>

          <div class="col-lg-3 col-md-3 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="eval_cod">C&oacute;digo</label>
                <input type="text" name="eval_cod" value="{{$evaluaciones->eval_cod}}" class="form-control" placeholder="">
               
           </div>
        </div>

      

        <div class="col-lg-4 col-md-4 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="eval_nom">Familia</label>
                <input type="text" name="eval_nom" value="{{$evaluaciones->eval_nom}}" class="form-control">
              
           </div>
        </div>
   
        
        
    </div>
    <div class="row">
        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
             <div class="form-group form-group-sm">
                <button class="btn btn-primary" type="submit">Guardar</button>
                <a href="/evaluaciones"><button class="btn btn-danger btn-close" type="button">Cancelar</button></a>
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
