 
            <div class="form-group form-group-sm">
                <label for="tip_pro_id">Tipo de Producto</label>
                <select class="form-control"  name="tip_pro_id" id="tip_pro_id">
                		<option value="0">Todos</option>

                    @foreach($tipos as $tp)
                        <option value="{{$tp->tip_pro_id}}">{{$tp->tip_pro_nom}}</option>
                    @endforeach
                </select>
               
           </div>
      