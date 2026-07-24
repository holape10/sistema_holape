<form method="post" action="{{url('buscarstock')}}" enctype="multipart/form-data">
        {{csrf_field()}}
<div class="form-group">
	<div class="input-group">
		<input type="text" class="form-control" name="buspro" placeholder="Nombre o Código del producto" value="{{$buspro}}">
		<input type="hidden" readonly class="form-control" name="busrucemp" placeholder="Buscar..." value="{{Auth::user()->IdEmpresa}}">
		<span class="input-group-btn">
			<button type="submit" class="btn btn-primary">Buscar</button>
		</span>
	</div>
</div>
</form>