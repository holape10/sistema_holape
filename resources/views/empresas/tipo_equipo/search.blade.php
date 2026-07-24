{!! Form::open(array('url'=>'/tipoequipo','method'=>'GET','autocomplete'=>'off','role'=>'search'))!!}
<div class="form-group form-group-sm">
	<div class="input-group">
		<input type="text" class="form-control" name="buscar" placeholder="" value="{{$buscar}}">
		<span class="input-group-btn">
			<button type="submit" class="btn btn-sm btn-primary">Buscar</button>
		</span>
	</div>
</div>
{{Form::close()}}
