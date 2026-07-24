{!! Form::open(array('url'=>'/caja','method'=>'GET','autocomplete'=>'off','role'=>'search'))!!}
<label>SELECCIONAR CAJERO</label>
<div class="form-group form-group-sm">
	<div class="input-group">
		<select name="usuario" class="form-control">
			<option value=""></option>
			@foreach($usuarios as $usuario)
			<option value="{{$usuario->IdUsuario}}">{{$usuario->name}} {{$usuario->apeusu}}</option>
			@endforeach
		</select>
		<span class="input-group-btn">
			<button type="submit" class="btn btn-sm btn-primary">Buscar</button>
		</span>

	</div>
	 
</div>
{{Form::close()}}