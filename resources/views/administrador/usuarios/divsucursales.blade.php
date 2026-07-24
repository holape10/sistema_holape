<div class="form-group">
              <label>SUCURSAL</label>
                <select name="idnegocio" id="idnegocio" class="form-control">
                  @foreach ($negocios as $negocio)
                    <option value="{{$negocio->id_empresa_negocio}}">{{$negocio->tipo_negocio}}</option>
                  @endforeach
                </select>
              </div>