@extends ('layouts.empresas')
@section ('contenido')

    <section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-body">
                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                        <h4><i class='glyphicon glyphicon-briefcase'></i><strong> NUEVO TIPO CAJA</strong></h4>
                     </div>
                    </div>
                </div>
            </div>
        </div>
   
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-body">

    {!!Form::open(array('url'=>'tiposcaja','method'=>'POST','autocomplete'=>'off','files'=>'true'))!!}
    {{Form::token()}}
    <div class="row">

        <div class="col-lg-4 col-md-4 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="tip_caj_id">C&oacute;digo</label>
                <input type="text" name="tip_caj_id" value="{{old('tip_caj_id')}}" class="form-control" placeholder="">
            
           </div>
        </div>
    

        <div class="col-lg-4 col-md-4 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="tip_caj_nom">Tipo Caja</label>
                <input type="text" name="tip_caj_nom" value="{{old('tip_caj_nom')}}" class="form-control" placeholder="">
            
           </div>
        </div>
    
              <div class="col-lg-4 col-md-4 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="movimiento">Movimiento</label>
                <select name="movimiento" class="form-control">
                    <option value="ENTRADA">ENTRADA</option>
                      <option value="SALIDA">SALIDA</option>
                </select>
            
           </div>
        </div>

        
        </div>
    <div class="row">
        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
             <div class="form-group form-group-sm">
                <button class="btn btn-primary" type="submit">Guardar</button>
                <a href="/tiposcaja"><button class="btn btn-danger btn-close" type="button">Cancelar</button></a>
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