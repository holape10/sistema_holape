{!! Form::open(array('url'=>'/subcategorias','method'=>'GET','autocomplete'=>'off','role'=>'search'))!!}
<div class="form-group form-group-sm">
	<div class="input-group">
		<input type="text" class="form-control" name="buscar"  value="{{$buscar}}">
		<span class="input-group-btn">
			<button type="submit" class="btn btn-primary btn-sm">Buscar</button>
		</span>
	</div>
</div>
{{Form::close()}}