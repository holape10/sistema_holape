@extends ('layouts.empresas')
@section ('contenido')

	<section class="content">
        

        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-body" style="background-color:blue;">
                   
                        <font color="white"><center><strong> Registrar Tipo de Equipo</strong></center></font>
              
                    </div>
                    <div class="box-body">

	{!!Form::open(array('url'=>'tipoequipo','method'=>'POST','autocomplete'=>'off','files'=>'true'))!!}
    {{Form::token()}}
    <div class="row">
        <div class="col-lg-3 col-md-3 col-sm-3 col-xs-3">
            <div class="form-group form-group-sm">
                <label for="nom_tip_equi">Tipo de Equipo</label>
                <input type="text" name="nom_tip_equi" value="{{old('nom_tip_equi')}}" class="form-control" placeholder="">
                
           </div>
        </div>
    </div>
    <div class="row">
    	<div class="col-lg-3 col-md-3 col-sm-3 col-xs-3">
    		 <div class="form-group form-group-sm">
            	<button class="btn btn-primary" type="submit">Guardar</button>
            	<a href="/tipoequipo"><button class="btn btn-danger btn-close" type="button">Cancelar</button></a>
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
