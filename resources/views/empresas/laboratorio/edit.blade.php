@extends ('layouts.empresas')
@section ('contenido')

    <section class="content">
    

        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                      <div class="box-header with-border" style="background-color:blue;">
        <center><font color="white"><strong>EDITAR LABORATORIO</strong></font></center>
     </div>
                    <div class="box-body">

    {!!Form::model($laboratorio,['method'=>'PATCH','route'=>['laboratorio.update',$laboratorio->lab_id],'files'=>'true'])!!}
    {{Form::token()}}
     <div class="row">
        <div  class="col-lg-2 col-md-2 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="lab_cod">Código</label>
                <input type="text" name="lab_cod" value="{{$laboratorio->lab_cod}}" class="form-control">
                
           </div>
        </div>


      

        <div class="col-lg-4 col-md-4 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="lab_nom">Laboratorio</label>
                <input type="text" name="lab_nom" value="{{$laboratorio->lab_nom}}" class="form-control" placeholder="">
              
           </div>
        </div>
    
      
    </div>
    <div class="row">
        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
             <div class="form-group form-group-sm">
                <button class="btn btn-primary" type="submit">Guardar</button>
                <a href="/areas"><button class="btn btn-danger btn-close" type="button">Cancelar</button></a>
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
