
<div class="form-group form-group-sm">
	<label>Almacenes</label>
	<select class="form-control" name="id_almacen" id="id_almacen">
		@foreach($almacen as $almacen)
		<option value="{{$almacen->id_almacen}}">{{$almacen->descripcion}}</option>
		@endforeach
	</select>
</div>