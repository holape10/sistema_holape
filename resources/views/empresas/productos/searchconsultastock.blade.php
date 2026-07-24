  {!!Form::open(array('url'=>'/consultastock','autocomplete'=>'off','method'=>'POST','id'=>'frmReporte','role'=>'form','files'=>'true'))!!}
  {{Form::token()}}
  <div class="row">
  	

  	<div hidden="hidden" class="col-lg-2">
  		<div class="form-group form-group-sm">
  			<label class="control-label">Tipo Reporte</label>
  			<select name="opcion" class="form-control">
  				<option value="500">STOCK PRODUCTOS</option>
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
  							<button type="button" id="btnBuscar" class="btn btn-primary btn-sm">Buscar</button>
  			</div>
  			
    
  		</div>
  	</div>
  </div>
  {{Form::close()}}