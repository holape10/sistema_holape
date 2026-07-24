@extends ('layouts.empresas')
@section ('contenido')

    <section class="content">
    

        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                      <div class="box-header with-border" style="background-color:blue;">
        <center><font color="white"><strong>EDITAR MAQUINA</strong></font></center>
     </div>
                    <div class="box-body">

    {!!Form::model($maquinas,['method'=>'PATCH','route'=>['maquinas.update',$maquinas->maq_id],'files'=>'true'])!!}
    {{Form::token()}}
     <div class="row">
        <div  class="col-lg-2 col-md-2 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="maq_cod">Código</label>
                <input type="text" name="maq_cod" value="{{$maquinas->maq_cod}}" class="form-control">
                
           </div>
        </div>


      

        <div class="col-lg-4 col-md-4 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="maq_nom">Máquina</label>
                <input type="text" name="maq_nom" value="{{$maquinas->maq_nom}}" class="form-control" placeholder="">
              
           </div>
        </div>
    
      
    </div>
    <div class="row">
        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
             <div class="form-group form-group-sm">
                <button class="btn btn-primary" type="submit">Guardar</button>
                <a href="/maquinas"><button class="btn btn-danger btn-close" type="button">Cancelar</button></a>
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
