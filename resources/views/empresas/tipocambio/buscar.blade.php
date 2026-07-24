{!! Form::open(array('url'=>'/series','method'=>'GET','autocomplete'=>'off','role'=>'search'))!!}
<div class="form-group">
	<div class="input-group">
		<input type="text" class="form-control" name="buscli" placeholder="Razón Social ó RUC" value="">
		<input type="hidden" readonly class="form-control" name="busrucemp" placeholder="Buscar..." value="{{Auth::user()->IdEmpresa}}">
		<span class="input-group-btn">
			<button type="submit" class="btn btn-primary">Buscar</button>
		</span>
	</div>
</div>
{{Form::close()}}