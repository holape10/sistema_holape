  {!!Form::open(array('url'=>'exportarstockproductos','autocomplete'=>'off','method'=>'POST','id'=>'frmReporte','role'=>'form','files'=>'true'))!!}
  {{Form::token()}}
  <div class="row">
  	<div class="col-lg-2">
  		<div class="form-group form-group-sm">
  			<label>Empresas</label>
  			<select name="sucursal" id="sucursal" class="form-control">
  				@foreach($negocios as $negocio)
	  				@if($negocio->id_empresa_negocio == $sucursal)
	  				<option selected="selected" value="{{$negocio->id_empresa_negocio}}">{{$negocio->IdEmpresa}} - {{$negocio->tipo_negocio}}</option>
	  				@else
	  				<option value="{{$negocio->id_empresa_negocio}}">{{$negocio->IdEmpresa}} - {{$negocio->tipo_negocio}}</option>
	  				@endif

  				@endforeach
  			</select>
  		</div>
  	</div>

  	<div hidden="hidden" class="col-lg-2">
  		<div class="form-group form-group-sm">
  			<label class="control-label">Tipo Reporte</label>
  			<select name="opcion" class="form-control">
  				<option value="50">STOCK PRODUCTOS</option>
  			</select>
  		</div>
  	</div>

  	<div class="col-lg-2" id="divalmacen">
  		<div class="form-group form-group-sm">
  			<label>Almacenes</label>
  			<select name="almacen" id="almacen" class="form-control">
  				@foreach($almacenes as $alm)
	  				@if($alm->id_almacen == $almacen)
	  				<option selected="selected" value="{{$alm->id_almacen}}">{{$alm->descripcion}}</option>
	  				@else
	  				<option value="{{$alm->id_almacen}}">{{$alm->descripcion}}</option>
	  				@endif
  				@endforeach
  			</select>
  		</div>
  	</div>

  	<div id="catinsu" class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
  		<div class="form-group form-group-sm">
  			<label for="cmbCatId">Categorias</label>
  			<select class="form-control"  name="cmbCatId" id="cmbCatId">
  				<option value="Todos">Todos</option>
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

  		<div id="catinsu" class="col-lg-2 col-md-2 col-sm-12 col-xs-12">
  		<div class="form-group form-group-sm">
  			<label for="estado">Estado</label>
  			<select class="form-control"  name="estado" id="estado">
  				<option value="Todos">Todos</option>
  				<option value="cs">Con Stock</option>
  				<option value="ne">Negativos</option>
  				<option value="se">Sin Stock</option>
  				
  			</select>
  		</div>
  	</div>       

  	<div class="col-lg-4 col-md-4 col-sm-4 col-xs-4">
  		<div class="form-group form-group-sm">
  			<label>Descripci&oacute;n</label>
  			<input type="text" class="form-control" name="buspro" id="buspro" placeholder="Nombre o Código del producto" value="{{$buspro}}">
  		</div>
  	</div>
  </div>

  <div class="row">
  	<div class="col-lg-12">
  		<div class="btn-toolbar" role="toolbar" aria-label="...">
  			<div class="btn-group">
  				<button type="button" id="btnBuscar" class="btn btn-primary btn-sm"><i class="fa fa-search"></i> Buscar</button>
  			</div>
  			<div class="btn-group" >
        <button type="button" id="btnImprimirTermica" class="btn btn-info btn-sm">
            <i class="fa fa-print"></i> Imprimir Stock (80mm)
        </button>
    </div>
  			<div class="btn-group">
  				<button type="button" id="btnPDF" dir="/exportarstockpdf" target="_blank"  class=" btn btn-warning btn-sm"><i class="fa fa-file-pdf-o"></i> Exportar Stock PDF</button>
  			</div>
        <div class="btn-group">
    <button type="button" id="btnExcel" dir="/exportarstockexcel" class="btn btn-success btn-sm"><i class="fa fa-file-excel-o"></i> Exportar Stock Excel</button>
    </div>
    <div class="btn-group">
    <button type="button" id="btnTicketVPS" dir="/exportarstockticket" class="btn btn-default btn-sm" style="background-color: #e2e3e5;">
        <i class="fa fa-print"></i> Ticket Pantalla
    </button>
</div>
  
    
  		</div>
  	</div>
  </div>
  {{Form::close()}}