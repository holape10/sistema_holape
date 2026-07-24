@extends ('layouts.empresas')
@section ('contenido')

	<section class="content">
       
        <div class="row">
            <div class="col-xs-12">
                <div class="box">
                      <div class="box-header" style="background-color:blue;">
                        <font color="white"><center><strong>NUEVA MESA</strong></center></font>
                       
                    </div>
                    <div class="box-body">

	{!!Form::open(array('url'=>'mesa','method'=>'POST','autocomplete'=>'off','files'=>'true'))!!}
    {{Form::token()}}
    <div class="row">
        <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
            <div class="form-group form-group-sm">
                <label for="txtMesNom">Nombre de Mesa</label>
                <input type="text" name="txtMesNom" value="{{old('txtMesNom')}}" class="form-control" placeholder="">
                  @if ($errors->has('txtMesNom'))
                        <span class="help-block"><strong><font color="red">{{ $errors->first('txtMesNom') }}</font></strong></span>
                @endif
           </div>
        </div>
         <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
            <div class="form-group form-group-sm">
                <label for="pis_id">Piso</label>
                <select name="pis_id" class="form-control">
                    @foreach($pisos as $piso)
                    <option value="{{$piso->pis_id}}">{{$piso->pis_nom}}</option>
                    @endforeach
                </select>
           </div>
        </div>
         <div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
            <div class="form-group form-group-sm">
                <label for="pis_id">Mozos</label>
                <select name="usuario" class="form-control">
                    <option></option>
                    @foreach($usuarios as $usuario)
                    <option value="{{$usuario->IdUsuario}}">{{$usuario->name}} {{$usuario->apeusu}}</option>
                    @endforeach
                </select>
           </div>
        </div>
    </div>
    <div class="row">
    	<div class="col-lg-3 col-md-3 col-sm-12 col-xs-12">
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
