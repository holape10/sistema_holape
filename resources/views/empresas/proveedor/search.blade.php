{!! Form::open(array('url'=>'/proveedor','method'=>'GET','autocomplete'=>'off','role'=>'search'))!!}
<div class="form-group">
	<div class="input-group">
		<input type="text" class="form-control" name="txtProv" placeholder="Razón Social ó RUC" value="{{$prov}}">
		<span class="input-group-btn">
			<button type="submit" class="btn btn-primary">Buscar</button>
		</span>
	</div>
</div>
{{Form::close()}}
