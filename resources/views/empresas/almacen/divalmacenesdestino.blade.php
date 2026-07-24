
   

<div class="form-group form-group-sm">
                              <label>Almacenes</label>
                              <select class="form-control" name="des_alm" id="des_alm">
                            
                                @foreach($almacenes as $almacen)
                                   <option value="{{$almacen->id_almacen}}">{{$almacen->descripcion}}</option>
                                @endforeach
                              </select>
                            </div>