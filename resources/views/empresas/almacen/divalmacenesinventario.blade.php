<script type="text/javascript">
   $("#almacen").change(function() {
                            
                    $("#btnCategorias").click();

                  });

	
</script>
   

<div class="form-group form-group-sm">
                              <label>Almacenes</label>
                              <select class="form-control" name="alm_nue_inv" id="alm_nue_inv">
                              
                                @foreach($almacenes as $almacen)
                                   <option value="{{$almacen->id_almacen}}">{{$almacen->descripcion}}</option>
                                @endforeach
                              </select>
                            </div>