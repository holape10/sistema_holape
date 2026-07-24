 
            <div class="form-group form-group-sm">
                <label for="subcat_id">Subfamilia</label>
                <select class="form-control"  name="subcat_id" id="subcat_id">
                		<option value="0">Todos</option>

                    @foreach($subcategorias as $subcat)
                        <option value="{{$subcat->subcat_id}}">{{$subcat->subcat_nom}}</option>
                    @endforeach
                </select>
               
           </div>
      