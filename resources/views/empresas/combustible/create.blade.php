@extends ('layouts.empresas')
@section ('contenido')

    <section class="content">
    
   
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-header" style="background-color:blue;">
                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                            <font color="white"><strong><center>Registrar Nivel Combustible</center></strong></font>
                        </div>
                    </div>
                <div class="box-body">

    {!!Form::open(array('url'=>'combustible','method'=>'POST','autocomplete'=>'off','files'=>'true'))!!}
    {{Form::token()}}
    <div class="row">
        <div class="col-lg-4 col-md-4 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="comb_nom">Combustible</label>
                <input type="text" name="comb_nom" value="{{old('comb_nom')}}" class="form-control" placeholder="">
               
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