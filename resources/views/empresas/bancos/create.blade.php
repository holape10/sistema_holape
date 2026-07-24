@extends ('layouts.empresas')
@section ('contenido')

	<section class="content">
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-body">
                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                        <h4><i class='glyphicon glyphicon-briefcase'></i><strong> CREAR BANCO</strong></h4>
                     </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                    <div class="box-body">

	{!!Form::open(array('url'=>'bancos','method'=>'POST','autocomplete'=>'off','files'=>'true'))!!}
    {{Form::token()}}
    <div class="row">
        <div class="col-lg-3 col-md-3 col-sm-3 col-xs-3">
            <div class="form-group form-group-sm">
                <label for="ban_nom">Banco</label>
                <input type="text" name="ban_nom" value="{{old('ban_nom')}}" class="form-control" placeholder="">
                
           </div>
        </div>
    </div>
    <div class="row">
    	<div class="col-lg-3 col-md-3 col-sm-3 col-xs-3">
    		 <div class="form-group form-group-sm">
            	<button class="btn btn-primary" type="submit">Guardar</button>
            	<a href="/bancos"><button class="btn btn-danger btn-close" type="button">Cancelar</button></a>
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
