@extends ('layouts.empresas')
@section ('contenido')

    <section class="content">
     
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                      <div class="box-header with-border" style="background-color:blue;">
        <center><font color="white"><strong>REGISTRAR EVALUACI&Oacute;N</strong></font></center>
     </div>
                    <div class="box-body">

    {!!Form::open(array('url'=>'evaluaciones','method'=>'POST','autocomplete'=>'off','files'=>'true'))!!}
    {{Form::token()}}
    <div class="row">
          <div class="col-lg-3 col-md-3 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="eval_cod">C&oacute;digo</label>
                <input type="text" name="eval_cod" value="{{old('eval_cod')}}" class="form-control" placeholder="">
               
           </div>
        </div>
        <div class="col-lg-4 col-md-4 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="eval_nom">Evaluaci&oacute;n</label>
                <input type="text" name="eval_nom" value="{{old('eval_nom')}}" class="form-control" placeholder="">
               
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