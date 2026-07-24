{!! Form::open(array('url'=>'/negocios','method'=>'GET','autocomplete'=>'off','role'=>'buscar'))!!}
<div class="form-group">
	<div class="input-group">
		<input type="text" class="form-control input-sm" name="buscar"  value="{{$buscar}}">
		<span class="input-group-btn">
			<button type="submit" class="btn btn-primary btn-sm">Buscar</button>
		</span>
	</div>
</div>
{{Form::close()}}