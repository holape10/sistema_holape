@extends ('layouts.empresas')
@section ('contenido')

    <section class="content">
    

        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                      <div class="box-header with-border" style="background-color:blue;">
        <center><font color="white"><strong>EDITAR PROCESO</strong></font></center>
     </div>
                    <div class="box-body">

    {!!Form::model($procesos,['method'=>'PATCH','route'=>['procesos.update',$procesos->proc_id],'files'=>'true'])!!}
    {{Form::token()}}
     <div class="row">
        <div  class="col-lg-2 col-md-2 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="proc_cod">Código</label>
                <input type="text" name="proc_cod" value="{{$procesos->proc_cod}}" class="form-control">
                
           </div>
        </div>


      

        <div class="col-lg-4 col-md-4 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="proc_nom">Proceso</label>
                <input type="text" name="proc_nom" value="{{$procesos->proc_nom}}" class="form-control" placeholder="">
              
           </div>
        </div>
    
      
    </div>
    <div class="row">
        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
             <div class="form-group form-group-sm">
                <button class="btn btn-primary" type="submit">Guardar</button>
                <a href="/procesos"><button class="btn btn-danger btn-close" type="button">Cancelar</button></a>
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
