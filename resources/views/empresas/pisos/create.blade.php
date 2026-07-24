@extends ('layouts.empresas')
@section ('contenido')

	<section class="content">
      
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-header" style="background-color:blue;">
                        <font color="white"><center><strong>NUEVO PISO</strong></center></font>
                       
                    </div>
                    <div class="box-body">

	{!!Form::open(array('url'=>'pisos','method'=>'POST','autocomplete'=>'off','files'=>'true'))!!}
    {{Form::token()}}
    <div class="row">
        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
            <div class="form-group form-group-sm">
                <label for="pis_nom">Piso</label>
                <input type="text" name="pis_nom" value="{{old('pis_nom')}}" class="form-control" placeholder="">
             
           </div>
        </div>
    </div>
    <div class="row">
    	<div class="col-lg-6 col-md-6 col-sm-6 col-xs-6">
    		 <div class="form-group form-group-sm">
            	<button class="btn btn-primary" type="submit">Guardar</button>
            	<a href="/categorias"><button class="btn btn-danger btn-close" type="button">Cancelar</button></a>
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
