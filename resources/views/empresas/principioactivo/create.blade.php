@extends ('layouts.empresas')
@section ('contenido')

	<section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-body">
                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                        <h4><i class='glyphicon glyphicon-briefcase'></i><strong> NUEVO PRINCIPIO ACTIVO</strong></h4>
                     </div>
                    </div>
                </div>
            </div>
        </div>
   
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-body">

	{!!Form::open(array('url'=>'principioactivo','method'=>'POST','autocomplete'=>'off','files'=>'true'))!!}
    {{Form::token()}}
    <div class="row">
        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
            <div class="form-group">
                <label for="pri_act_nom">PRINCIPIO ACTIVO</label>
                <input type="text" name="pri_act_nom" value="{{old('pri_act_nom')}}" class="form-control" placeholder="">
               
           </div>
        </div>
    </div>
    <div class="row">
    	<div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
    		 <div class="form-group">
            	<button class="btn btn-primary" type="submit">Guardar</button>
            	<a href="/principioactivo"><button class="btn btn-danger btn-close" type="button">Cancelar</button></a>
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