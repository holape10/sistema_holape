@extends ('layouts.empresas')
@section ('contenido')

	<section class="content">
     

        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                     <div class="box-header" style="background-color:blue;">
                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                        <font color="white"><i class='glyphicon glyphicon-briefcase'></i><strong> CREAR MODELO</strong></font>
                     </div>
                    </div>
                    <div class="box-body">

	{!!Form::open(array('url'=>'modelos','method'=>'POST','autocomplete'=>'off','files'=>'true'))!!}
    {{Form::token()}}
    <div class="row">
        <div class="col-lg-3 col-md-3 col-sm-3 col-xs-3">
            <div class="form-group form-group-sm">
                <label for="mod_nom">Modelo</label>
                <input type="text" name="mod_nom" value="{{old('mod_nom')}}" class="form-control" placeholder="">
                
           </div>
        </div>
    </div>
    <div class="row">
    	<div class="col-lg-3 col-md-3 col-sm-3 col-xs-3">
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
