{!! Form::open(array(
    'url' => (isset($es_insumos) && $es_insumos) ? '/insumos' : '/productos',
    'method' => 'GET',
    'autocomplete' => 'off',
    'role' => 'search',
    'id' => 'formproducto'
)) !!}

<div class="row">
    <div class="col-lg-2 col-md-3 col-sm-6 col-xs-12">
        <div class="form-group form-group-sm">
            <label for="sucursal">Sucursal</label>
            <select class="form-control" name="sucursal" id="sucursal" onchange="this.form.submit()">
                @foreach($sucursales as $suc)
                    <option value="{{$suc->id_empresa_negocio}}" {{ $suc->id_empresa_negocio == $sucursal ? 'selected' : '' }}>
                        {{$suc->IdEmpresa}} - {{$suc->tipo_negocio}}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="col-lg-2 col-md-3 col-sm-6 col-xs-12">
        <div class="form-group form-group-sm">
            <label for="promocion">Tipo Prod.</label>
            <select class="form-control" name="promocion" id="promocion" onchange="this.form.submit()">
                <option value="">-- Todos --</option>
                <option value="0" {{ request('promocion') === '0' ? 'selected' : '' }}>Producto</option>
                <option value="2" {{ request('promocion') == '2' ? 'selected' : '' }}>Preparado</option>
                <option value="4" {{ request('promocion') == '4' ? 'selected' : '' }}>Insumo</option>
                <option value="1" {{ request('promocion') == '1' || request('promocion') == '6' ? 'selected' : '' }}>Combo</option>
            </select>
        </div>
    </div>

    <div class="col-lg-2 col-md-3 col-sm-6 col-xs-12">
        <div class="form-group form-group-sm">
            <label for="tip_pro_id">Linea</label>
            <select class="form-control" name="tip_pro_id" id="tip_pro_id" onchange="this.form.submit()">
                <option value="0">-- Todos --</option>
                @foreach($tipos as $tp)
                    <option value="{{$tp->tip_pro_id}}" {{ $tp->tip_pro_id == $tip_pro_id ? 'selected' : '' }}>
                        {{$tp->tip_pro_nom}}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="col-lg-2 col-md-3 col-sm-6 col-xs-12">
        <div class="form-group form-group-sm">
            <label for="cmbCatId">Familia</label>
            <select class="form-control" name="cmbCatId" id="cmbCatId" onchange="this.form.submit()">
                <option value="0">-- Todos --</option>
                @foreach($categorias as $cat)
                    <option value="{{$cat->cat_id}}" {{ $cat->cat_id == $categoria ? 'selected' : '' }}>
                        {{$cat->cat_nom}}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="col-lg-2 col-md-3 col-sm-6 col-xs-12">
        <div class="form-group form-group-sm">
            <label for="subcat_id">Subfamilia</label>
            <select class="form-control" name="subcat_id" id="subcat_id" onchange="this.form.submit()">
                <option value="0">-- Todos --</option>
                @foreach($subcategorias as $subcat)
                    <option value="{{$subcat->subcat_id}}" {{ $subcat->subcat_id == $subcategoria ? 'selected' : '' }}>
                        {{$subcat->subcat_nom}}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="col-lg-2 col-md-9 col-sm-12 col-xs-12"> 
        <div class="form-group form-group-sm">
            <label>Buscar Producto</label>
            <div class="input-group">
                <input type="text" class="form-control" name="buspro" placeholder="Nombre/Cód." value="{{$buspro}}">
                <span class="input-group-btn">
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fa fa-search"></i>
                    </button>
                </span>
            </div>
        </div>
    </div>

    @if(Auth::User()->hasRole('admin') || Auth::User()->hasRole('superadmin'))
    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" hidden="hidden">
        <div class="form-group form-group-sm">
            <label style="cursor: pointer; font-weight: bold; color: #d35400;">
                <input type="checkbox" id="checkMitadPrecioGlobal" style="transform: scale(1.5); margin-right: 8px;"> 
                Activar Descuento del 50% menos en TODOS los Productos
            </label>
        </div>
    </div>
    @endif 

</div>

{{Form::close()}}