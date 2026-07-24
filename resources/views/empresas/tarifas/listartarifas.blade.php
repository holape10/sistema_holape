
    <div class="form-group form-group-sm">
        <label>TARIFAS</label>
        <select name="tarifa" id="tarifa" class="form-control">
            @foreach($tarifas as $tarifa)
    	        <option value="{{$tarifa->id_tarifa}}">{{$tarifa->descripcion}} / S/. {{$tarifa->precio}}</option>
            @endforeach
        </select>
    </div>
