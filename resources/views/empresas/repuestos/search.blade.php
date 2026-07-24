{!! Form::open(array('url'=>'/repuestos','method'=>'GET','autocomplete'=>'off','role'=>'search','id'=>'formproducto'))!!}
<!--<div class="col-lg-2">
	<div class="form-group form-group-sm">
		<label>Tipo Producto</label>
		<select name="promocion" id="promocion" class="form-control">
			<option value="Todos">Todos</option>
			@foreach($tipos_productos as $tip)
			<option value="{{$tip->tip_prod_cod}}">{{$tip->tip_prod_nom}}</option>
			@endforeach
		</select>
	</div>
</div>-->
<div class="row">
 <div  class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
            <div class="form-group form-group-sm">
                <label for="sucursal">Sucursal</label>
                <select class="form-control"  name="sucursal" id="sucursal">
        
                    @foreach($sucursales as $suc)
                        @if($suc->id_empresa_negocio == $sucursal)
                            <option selected="selected" value="{{$suc->id_empresa_negocio}}">{{$suc->IdEmpresa}} - {{$suc->tipo_negocio}}</option>
                        @else
                            <option value="{{$suc->id_empresa_negocio}}">{{$suc->IdEmpresa}} - {{$suc->tipo_negocio}}</option>
                        @endif
                        
                    @endforeach
                </select>
               
           </div>
        </div>
 <div id="catinsu" class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
            <div class="form-group form-group-sm">
                <label for="cmbCatId">Familia</label>
                <select class="form-control"  name="cmbCatId" id="cmbCatId">
                	<option value="0">Todos</option>
                    @foreach($categorias as $cat)
                     	@if($cat->cat_id == $categoria)
                     		<option selected="selected" value="{{$cat->cat_id}}">{{$cat->cat_nom}}</option>
                     	@else
                     		<option value="{{$cat->cat_id}}">{{$cat->cat_nom}}</option>
                     	@endif
                        
                    @endforeach
                </select>
               
           </div>
        </div>
        <div id="subcat" class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
            <div class="form-group form-group-sm">
                <label for="subcat_id">Subamilia</label>
                <select class="form-control"  name="subcat_id" id="subcat_id">
                		<option value="0">Todos</option>

                    @foreach($subcategorias as $subcat)
                    	@if($subcat->subcat_id == $subcategoria) 
                    		<option selected="selected" value="{{$subcat->subcat_id}}">{{$subcat->subcat_nom}}</option>
                    	@else
                    		<option value="{{$subcat->subcat_id}}">{{$subcat->subcat_nom}}</option>
                    	@endif
                       
                    @endforeach
                </select>
               
           </div>
        </div>
        <div id="subcat" class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
            <div class="form-group form-group-sm">
                <label for="tip_pro_id">Tipo Producto</label>
                <select class="form-control"  name="tip_pro_id" id="tip_pro_id">
                	<option value="0">Todos</option>

                    @foreach($tipos as $tp)
                    	@if($tp->tip_pro_id == $tip_pro_id)
                    		<option selected="selected" value="{{$tp->tip_pro_id}}">{{$tp->tip_pro_nom}}</option>
                    	@else
                    		<option value="{{$tp->tip_pro_id}}">{{$tp->tip_pro_nom}}</option>
                    	@endif
                        
                    @endforeach
                </select>
               
           </div>
        </div>
<div class="col-lg-4"> 
<div class="form-group form-group-sm">
	<label>Descripci&oacute;n</label>
	<div class="input-group">
		
		<input type="text" class="form-control" name="buspro" placeholder="Nombre o Código del producto" value="{{$buspro}}">
	
		<span class="input-group-btn">
			<button type="submit" class="btn btn-sm btn-primary">Buscar</button>
		</span>
	</div>
</div>
</div>
</div>


{{Form::close()}}