 
            <div class="form-group form-group-sm">
                <label for="cmbCatId">Categorias</label>
                <select class="form-control"  name="cmbCatId" id="cmbCatId">
                		<option value="0">Todos</option>

                    @foreach($categorias as $cat)
                        <option value="{{$cat->cat_id}}">{{$cat->cat_nom}}</option>
                    @endforeach
                </select>
               
           </div>
      